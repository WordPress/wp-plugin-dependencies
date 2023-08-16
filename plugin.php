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
 * Description: Parses 'Requires Plugins' header and information about dependencies.
 * Author: Andy Fragen, Colin Stewart, Paul Biron
 * Version: 2.0.1
 * License: MIT
 * Network: true
 * Requires at least: 6.0
 * Requires PHP: 7.0
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
if ( version_compare( get_bloginfo( 'version' ), '6.4-beta1', '>=' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	deactivate_plugins( __FILE__ );
}

require_once __DIR__ . '/wp-admin/includes/class-wp-plugin-dependencies.php';
require_once __DIR__ . '/wp-admin/includes/plugin-install.php';

// Override WP_Plugin_Install_List_Table with our own.
require_once __DIR__ . '/wp-admin/includes/class-pd-install-list-table.php';
add_filter(
	'wp_list_table_class_name',
	static function( $class_name ) {
		if ( 'WP_Plugin_Install_List_Table' === $class_name ) {
			$class_name = __NAMESPACE__ . '\PD_Install_List_Table';
		}
		return $class_name;
	}
);
