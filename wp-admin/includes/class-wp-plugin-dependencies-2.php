<?php
/**
 * WordPress Plugin Administration API: WP_Plugin_Dependencies class
 *
 * @package WordPress
 * @subpackage Administration
 * @since 6.3.0
 */

/**
 * Child class for installing plugin dependencies.
 *
 * It is designed to add plugin dependencies as designated
 * to a new view in the plugins install page.
 */
class WP_Plugin_Dependencies_2 {

	/**
	 * Holds associative array of slug|endpoint, if present.
	 *
	 * @var array
	 */
	protected $api_endpoints = array();

	/**
	 * Holds $args from `plugins_api_result` hook.
	 *
	 * @var stdClass
	 */
	private $args;

	/**
	 * Initialize, load filters, and get started.
	 *
	 * @return void
	 */
	public function start() {
		if ( is_admin() ) {
			add_filter( 'plugins_api_result', array( $this, 'add_plugin_card_dependencies' ), 10, 3 );
			add_filter( 'upgrader_post_install', array( $this, 'fix_plugin_containing_directory' ), 10, 3 );
			add_filter( 'wp_plugin_dependencies_slug', array( $this, 'split_slug' ), 10, 1 );
		}
	}

	/**
	 * Split slug into slug and endpoint.
	 *
	 * @param string $slug Slug.
	 *
	 * @return string
	 */
	public function split_slug( $slug ) {
		if ( ! str_contains( $slug, '|' ) || str_starts_with( $slug, '|' ) || str_ends_with( $slug, '|' ) ) {
			return $slug;
		}

		$original_slug = $slug;

		list( $slug, $endpoint ) = explode( '|', $slug );

		$slug     = trim( $slug );
		$endpoint = trim( $endpoint );

		if ( '' === $slug || '' === $endpoint ) {
			return $original_slug;
		}

		if ( ! isset( $this->api_endpoints[ $slug ] ) ) {
			$this->api_endpoints[ $slug ] = $endpoint;
		}

		return $slug;
	}

	/**
	 * Filter `plugins_api_result` for adding plugin dependencies.
	 *
	 * @param stdClass $response Response from `plugins_api()`.
	 * @param string   $action   Action type.
	 * @param stdClass $args     Array of data from hook.
	 *
	 * @return void|WP_Error
	 */
	public function add_plugin_card_dependencies( $response, $action, $args ) {
		$rest_endpoints = $this->api_endpoints;
		$this->args     = $args;

		// TODO: no need for Reflection in when in core, use $this->parse_plugin_headers.
		$wp_plugin_dependencies = new WP_Plugin_Dependencies();
		$parse_headers          = new ReflectionMethod( $wp_plugin_dependencies, 'parse_plugin_headers' );
		$parse_headers->setAccessible( true );
		$plugin_headers = $parse_headers->invoke( $wp_plugin_dependencies );

		if ( is_wp_error( $response )
			|| ( property_exists( $args, 'slug' ) && array_key_exists( $args->slug, $this->api_endpoints ) )
		) {
			/**
			 * Filter the REST enpoints used for lookup of plugins API data.
			 *
			 * @param array
			 */
			$rest_endpoints = array_merge( $rest_endpoints, apply_filters( 'plugin_dependency_endpoints', $rest_endpoints ) );

			foreach ( $rest_endpoints as $endpoint ) {
				// Endpoint must contain correct slug somewhere in URI.
				if ( ! str_contains( $endpoint, $args->slug ) ) {
					continue;
				}

				// Get local JSON endpoint.
				if ( str_ends_with( $endpoint, 'json' ) ) {
					foreach ( $plugin_headers as $plugin_file => $requires ) {
						if ( str_contains( $requires['RequiresPlugins'], $endpoint ) ) {
							$endpoint = plugin_dir_url( $plugin_file ) . $endpoint;
							break;
						}
					}
				}
				$response = wp_remote_get( $endpoint );

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
			add_filter( 'upgrader_package_options', array( $this, 'upgrader_package_options' ), 10, 1 );
		}

		return (object) $response;
	}

	/**
	 * Add slug to hook_extra.
	 *
	 * @see WP_Upgrader::run() for $options details.
	 *
	 * @param array $options Array of options.
	 *
	 * @return array
	 */
	public function upgrader_package_options( $options ) {
		$options['hook_extra']['slug'] = $this->args->slug;
		remove_filter( 'upgrader_package_options', array( $this, 'upgrader_package_options' ), 10 );

		return $options;
	}

	/**
	 * Filter `upgrader_post_install` for plugin dependencies.
	 *
	 * For correct renaming of downloaded plugin directory,
	 * some downloads may not be formatted correctly.
	 *
	 * @global WP_Filesystem_Base $wp_filesystem WordPress filesystem subclass.
	 *
	 * @param bool  $true       Default is true.
	 * @param array $hook_extra Array of data from hook.
	 * @param array $result     Array of data for installation.
	 *
	 * @return bool
	 */
	public function fix_plugin_containing_directory( $true, $hook_extra, $result ) {
		global $wp_filesystem;

		if ( ! isset( $hook_extra['slug'] ) ) {
			return $true;
		}

		$from = untrailingslashit( $result['destination'] );
		$to   = trailingslashit( $result['local_destination'] ) . $hook_extra['slug'];

		if ( trailingslashit( strtolower( $from ) ) !== trailingslashit( strtolower( $to ) ) ) {
			// TODO: remove function_exists for commit.
			if ( function_exists( 'move_dir' ) ) {
				$true = move_dir( $from, $to, true );
			} elseif ( ! rename( $from, $to ) ) {
				$wp_filesystem->mkdir( $to );
				$true = copy_dir( $from, $to, array( basename( $to ) ) );
				$wp_filesystem->delete( $from, true );
			}
		}

		return $true;
	}
}

( new WP_Plugin_Dependencies_2() )->start();
