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
		if ( is_admin() && ! wp_doing_ajax() ) {
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
	 * @param bool  $true       Default is true.
	 * @param array $hook_extra Array of data from hook.
	 * @param array $result     Array of data for installation.
	 *
	 * @return bool
	 */
	public function fix_plugin_containing_directory( $true, $hook_extra, $result ) {
		if ( ! isset( $hook_extra['slug'] ) ) {
			return $true;
		}

		$from = untrailingslashit( $result['destination'] );
		$to   = trailingslashit( $result['local_destination'] ) . $hook_extra['slug'];

		if ( trailingslashit( strtolower( $from ) ) !== trailingslashit( strtolower( $to ) ) ) {
			$true = move_dir( $from, $to, true );
		}

		return $true;
	}
}

add_action( 'admin_init', array( new WP_Plugin_Dependencies_2(), 'start' ), 5 );
