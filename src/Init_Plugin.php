<?php
/**
 * Plugin Dependencies
 *
 * @author  Andy Fragen
 * @license MIT
 * @link    https://github.com/WordPress/wp-plugin-dependencies
 * @package wp-plugin-dependencies
 */

namespace WP_Plugin_Dependencies;

/**
 * Class Init_Plugin
 */
class Init_Plugin {

	/**
	 * Initialize plugin.
	 *
	 * @return void
	 */
	public static function init() {

		// Add new function to wp-admin/includes/plugin-install.php.
		require_once __DIR__ . '/wp-admin/includes/plugin-install.php';
		// Override WP_Plugin_Install_List_Table with our own.
		require_once __DIR__ . '/wp-admin/includes/class-pd-install-list-table.php';
		// Override WP_Plugins_List_Table with our own.
		require_once __DIR__ . '/wp-admin/includes/class-pd-list-table.php';

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( __NAMESPACE__ . '\Init_Plugin', 'enqueue_styles' ) );
			add_action( 'admin_enqueue_scripts', array( __NAMESPACE__ . '\Init_Plugin', 'enqueue_scripts' ) );
			add_action( 'wp_ajax_check_plugin_dependencies', array( 'WP_Plugin_Dependencies', 'check_plugin_dependencies_during_ajax' ) );

			global $pagenow;
			if ( in_array( $pagenow, array( 'plugins.php', 'plugin-install.php' ), true ) ) {
				add_action( 'admin_notices', array( 'WP_Plugin_Dependencies', 'display_admin_notice_for_unmet_dependencies' ) );
				add_action( 'admin_notices', array( 'WP_Plugin_Dependencies', 'display_admin_notice_for_deactivated_dependents' ) );
				add_action( 'admin_notices', array( 'WP_Plugin_Dependencies', 'display_admin_notice_for_circular_dependencies' ) );
			}

			add_filter(
				'wp_list_table_class_name',
				static function ( $class_name ) {
					if ( 'WP_Plugin_Install_List_Table' === $class_name ) {
						$class_name = __NAMESPACE__ . '\PD_Install_List_Table';
					} elseif ( 'WP_Plugins_List_Table' === $class_name ) {
						$class_name = __NAMESPACE__ . '\PD_List_Table';
					}

					return $class_name;
				}
			);
		}
	}

	/**
	 * Enqueues styles for plugin dependencies on the "Add New" plugins screen.
	 *
	 * @global string $wp_version The WordPress version string.
	 * @global string $pagenow    The filename of the current screen.
	 *
	 * @return void
	 */
	public static function enqueue_styles() {
		global $wp_version, $pagenow;

		wp_enqueue_style(
			'wp-plugin-dependencies',
			plugins_url( 'src/wp-admin/css/wp-plugin-dependencies.css', 'wp-plugin-dependencies/plugin.php' ),
			array(),
			$wp_version
		);
	}

	/**
	 * Enqueues scripts for plugin dependencies on the "Add New" plugins screen.
	 *
	 * @global string $wp_version The WordPress version string.
	 * @global string $pagenow    The filename of the current screen.
	 *
	 * @return void
	 */
	public static function enqueue_scripts() {
		global $wp_version, $pagenow;

		if ( 'plugin-install.php' === $pagenow ) {
			wp_enqueue_script(
				'wp-plugin-dependencies-updates',
				plugins_url( 'src/wp-admin/js/updates.js', 'wp-plugin-dependencies/plugin.php' ),
				array( 'updates' ),
				$wp_version,
				true
			);
		}
	}
}
