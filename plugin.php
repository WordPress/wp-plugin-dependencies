<?php
/**
 * Plugin Dependencies
 *
 * @author  Andy Fragen
 * @license MIT
 * @link    https://github.com/WordPress/wp-plugin-dependencies
 * @package wp-plugin-dependencies
 */

/**
 * Plugin Name: Plugin Dependencies
 * Plugin URI:  https://wordpress.org/plugins/wp-plugin-dependencies
 * Description: Parses 'Requires Plugins' header, add plugin install dependencies tab, and information about dependencies.
 * Author: Andy Fragen, Colin Stewart
 * Version: 1.7.1
 * License: MIT
 * Network: true
 * Requires at least: 6.0
 * Requires PHP: 5.6
 * GitHub Plugin URI: https://github.com/WordPress/wp-plugin-dependencies
 * Primary Branch: trunk
 */

namespace WP_Plugin_Dependencies;

/*
 * Exit if called directly.
 * PHP version check and exit.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Deactivate plugin when committed to core.
if ( version_compare( get_bloginfo( 'version' ), '6.2-RC1', '>=' ) ) {
	deactivate_plugins( __FILE__ );
}

// Load the Composer autoloader.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
} else {
	deactivate_plugins( __FILE__ );

	wp_die(
		wp_kses_post(
			__( 'Plugin Dependencies is missing required composer dependencies.', 'wp-plugin-dependencies' )
		)
	);
}

// Add the sites with REST endpoints that return plugins_api() data when passed `slug` query arg.
add_filter(
	'plugin_dependency_endpoints',
	function () {
		return array(
			'https://git-updater.com/wp-json/git-updater/v1/plugins-api/',
			'https://pub.thefragens.com/gravityforms.json',
		);
	}
);

/**
 * Class Init
 */
class Init {

	/**
	 * Initialize, load filters, and get started.
	 *
	 * @return void
	 */
	public function __construct() {
		require_once __DIR__ . '/wp-admin/includes/class-wp-plugin-dependencies.php';

		add_filter( 'install_plugins_tabs', array( $this, 'add_install_tab' ), 10, 1 );
		add_filter( 'install_plugins_table_api_args_dependencies', array( $this, 'add_install_dependency_args' ), 10, 1 );

		add_action( 'install_plugins_dependencies', 'display_plugins_table' );
		add_action( 'admin_init', array( 'WP_Plugin_Dependencies', 'init' ) );
		add_action(
			'install_plugins_table_header',
			function() {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$tab = isset( $_GET['tab'] ) ? sanitize_title_with_dashes( wp_unslash( $_GET['tab'] ) ) : '';
				if ( 'dependencies' === $tab ) {
					echo '<p>' . esc_html__( 'These suggestions are based on dependencies required by installed plugins.' ) . '</p>';
				}
			}
		);
	}

	/**
	 * Add 'Dependencies' tab to 'Plugin > Add New'.
	 *
	 * @param array $tabs Array of plugin install tabs.
	 *
	 * @return array
	 */
	public function add_install_tab( $tabs ) {
		$tabs['dependencies'] = _x( 'Dependencies', 'Plugin Installer' );

		return $tabs;
	}

	/**
	 * Add args to plugins_api().
	 *
	 * @param array $args Array of arguments to plugins_api().
	 *
	 * @return array
	 */
	public function add_install_dependency_args( $args ) {
		$args = array(
			'page'     => 1,
			'per_page' => 36,
			'locale'   => get_user_locale(),
			'browse'   => 'dependencies',
		);

		return $args;
	}
}

new Init();
