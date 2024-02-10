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
 * Version: 3.0.4
 * License: MIT
 * Network: true
 * Requires at least: 6.4
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
if ( version_compare( get_bloginfo( 'version' ), '6.5-beta1', '>=' )
	|| \class_exists( 'WP_Plugin_Dependencies' )
) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	deactivate_plugins( __FILE__ );
	return;
}

require_once __DIR__ . '/src/wp-includes/class-wp-plugin-dependencies.php';
\WP_Plugin_Dependencies::initialize();

// Let's get started.
add_action(
	'plugins_loaded',
	function () {
		require_once __DIR__ . '/src/Init_Plugin.php';
		Init_Plugin::init();
	}
);
