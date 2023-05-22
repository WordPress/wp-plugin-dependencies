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
 * Author: Andy Fragen, Colin Stewart, Paul Biron
 * Version: 1.12.0
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

// TODO: update with correct version.
if ( version_compare( get_bloginfo( 'version' ), '6.3-alpha-99999', '>=' ) ) {
	define( 'WP_PLUGIN_DEPENDENCIES1_COMMITTED', true );
} else {
	define( 'WP_PLUGIN_DEPENDENCIES1_COMMITTED', false );
}

// TODO: update with correct version.
if ( version_compare( get_bloginfo( 'version' ), '6.3-beta-1', '>=' ) ) {
	define( 'WP_PLUGIN_DEPENDENCIES2_COMMITTED', true );
} else {
	define( 'WP_PLUGIN_DEPENDENCIES2_COMMITTED', false );
}

// Deactivate plugin when committed to core.
if ( WP_PLUGIN_DEPENDENCIES2_COMMITTED ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	deactivate_plugins( __FILE__ );
}

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
		require_once __DIR__ . '/wp-admin/includes/plugin-install.php';
		require_once __DIR__ . '/wp-admin/includes/class-wp-plugin-dependencies-2.php';

		// Override WP_Plugin_Install_List_Table with our own.
		require_once __DIR__ . '/wp-admin/includes/class-pd-install-list-table.php';
		add_filter(
			'wp_list_table_class_name',
			function( $class_name ) {
				if ( 'WP_Plugin_Install_List_Table' === $class_name ) {
					$class_name = __NAMESPACE__ . '\PD_Install_List_Table';
				}

				return $class_name;
			}
		);

		if ( ! WP_PLUGIN_DEPENDENCIES1_COMMITTED ) {
			require_once __DIR__ . '/wp-admin/includes/class-wp-plugin-dependencies.php';

			add_filter( 'install_plugins_tabs', array( $this, 'add_install_tab' ), 10, 1 );
			add_filter( 'install_plugins_table_api_args_dependencies', array( $this, 'add_install_dependency_args' ), 10, 1 );

			add_action( 'install_plugins_dependencies', 'display_plugins_table' );
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
