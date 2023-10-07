<?php
/**
 * Tests for the WP_Plugin_Dependencies::add_dependency_plugin_row_hooks() method.
 *
 * @package WP_Plugin_Dependencies
 */

require_once __DIR__ . '/base.php';

/**
 * @group admin
 * @group plugins
 *
 * @covers WP_Plugin_Dependencies::add_dependency_plugin_row_hooks
 */
class Tests_Admin_WPPluginDependencies_AddDependencyPluginRowHooks extends WP_PluginDependencies_UnitTestCase {

	/**
	 * Tests that the expected callback methods are hooked.
	 *
	 * @dataProvider data_should_hook_callback
	 *
	 * @param string       $hook     The hook name.
	 * @param string|array $callback The callback.
	 */
	public function test_should_hook_callback( $hook, $callback ) {
		$this->call_method( 'add_dependency_plugin_row_hooks', 'plugin1/plugin1.php' );
		$this->assertIsInt( has_filter( $hook, $callback ) );
	}

	/**
	 * Data provider.
	 *
	 * @return array[]
	 */
	public function data_should_hook_callback() {
		return array(
			'after_plugin_row_meta'                        => array(
				'hook'     => 'after_plugin_row_meta',
				'callback' => array(
					'WP_Plugin_Dependencies',
					'add_dependents_to_dependency_plugin_row',
				),
			),
			'plugin_row_hide_checkbox_plugin1/plugin1.php' => array(
				'hook'     => 'plugin_row_hide_checkbox_plugin1/plugin1.php',
				'callback' => '__return_true',
			),
			'plugin_action_links_plugin1/plugin1.php'      => array(
				'hook'     => 'plugin_action_links_plugin1/plugin1.php',
				'callback' => array(
					'WP_Plugin_Dependencies',
					'unset_dependency_action_links',
				),
			),
			'network_admin_plugin_action_links_plugin1/plugin1.php' => array(
				'hook'     => 'network_admin_plugin_action_links_plugin1/plugin1.php',
				'callback' => array(
					'WP_Plugin_Dependencies',
					'unset_dependency_action_links',
				),
			),
		);
	}
}
