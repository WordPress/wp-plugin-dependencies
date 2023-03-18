<?php
/**
 * Add Plugin Dependency via REST API.
 *
 * @package Plugin_Dependency_API
 *
 * An example plugins_api(), response https://git-updater.com/wp-json/git-updater/v1/plugins-api/?slug=git-updater
 */

/*
 * Exit if called directly.
 * PHP version check and exit.
 */
if ( ! defined( 'WPINC' ) ) {
	return;
}

if ( ! class_exists( 'Plugin_Dependency_API' ) ) {
	/**
	 * Class for integration of non-dot org plugin dependencies and WP core.
	 */
	class Plugin_Dependency_API {

		/**
		 * Load hooks, let's get going.
		 *
		 * @return void
		 */
		public function load_hooks() {
			add_filter( 'plugins_api_result', [ $this, 'plugins_api_result' ], 10, 3 );
			add_filter( 'upgrader_post_install', [ $this, 'upgrader_post_install' ], 10, 3 );
		}

		/**
		 * Filter `plugins_api_result` for adding plugin dependencies.
		 *
		 * @param \stdClass $response Response from `plugins_api()`.
		 * @param string    $action   Action type.
		 * @param \stdClass $args     Array of data from hook.
		 *
		 * @return void|\WP_Error
		 */
		public function plugins_api_result( $response, $action, $args ) {
			$rest_endpoints = [];
			if ( is_wp_error( $response ) ) {
				/**
				 * Filter the REST enpoints used for lookup of plugins API data.
				 *
				 * @param array
				 */
				$rest_endpoints = array_merge( $rest_endpoints, apply_filters( 'plugin_dependency_endpoints', $rest_endpoints ) );

				foreach ( $rest_endpoints as $endpoint ) {
					// Allow endpoint to return JSON file but ensure returning for correct slug.
					$parsed_endpoint = ltrim( parse_url( $endpoint, PHP_URL_PATH ), '/' );
					if ( false !== strpos( $parsed_endpoint, '.json' )
						&& false === strpos( $parsed_endpoint, $args->slug )
					) {
						continue;
					}

					$url      = add_query_arg( 'slug', $args->slug, untrailingslashit( $endpoint ) );
					$response = wp_remote_get( $url );

					// Convert response to associative array.
					$response = json_decode( wp_remote_retrieve_body( $response ), true );
					if ( null === $response || isset( $response['error'] ) || isset( $response['code'] ) ) {
						$message  = isset( $response['error'] ) ? $response['error'] : '';
						$response = new WP_Error( 'error', 'Error retrieving plugin data.', $message );
					}
					if ( ! is_wp_error( $response ) ) {
						break;
					}
				}

				// Add slug to hook_extra.
				add_filter(
					'upgrader_package_options',
					function ( $options ) use ( $args ) {
						$options['hook_extra']['slug'] = $args->slug;

						return $options;
					},
					10,
					1
				);
			}

			return (object) $response;
		}

		/**
		 * Filter `upgrader_post_install` for plugin dependencies.
		 *
		 * For correct renaming of downloaded plugin directory,
		 * some downloads may not be formatted correctly.
		 *
		 * @param bool  $true       Default is true.
		 * @param array $hook_extra Array of data from hook.
		 * @param array $result     Array of data for installation.
		 *
		 * @return bool
		 */
		public function upgrader_post_install( $true, $hook_extra, $result ) {
			global $wp_filesystem;

			if ( ! isset( $hook_extra['slug'] ) ) {
				return $true;
			}

			$from = untrailingslashit( $result['destination'] );
			$to   = trailingslashit( $result['local_destination'] ) . $hook_extra['slug'];

			if ( trailingslashit( strtolower( $from ) ) !== trailingslashit( strtolower( $to ) ) ) {
				if ( function_exists( 'move_dir' ) ) {
					$true = move_dir( $from, $to, true );
				} elseif ( ! rename( $from, $to ) ) {
					$wp_filesystem->mkdir( $to );
					$true = copy_dir( $from, $to, [ basename( $to ) ] );
					$wp_filesystem->delete( $from, true );
				}
			}

			return $true;
		}
	}
}

( new Plugin_Dependency_API() )->load_hooks();
