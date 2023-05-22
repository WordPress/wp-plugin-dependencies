<?php
/**
 * WordPress Plugin Administration API: WP_Plugin_Dependencies class
 *
 * Replace in WP_Plugin_Install_List_Table::display_rows()
 * $action_links[] = wp_get_plugin_action_button( $name, $plugin, $compatible_php, $compatible_wp );
 *
 * @package WordPress
 * @subpackage Administration
 * @since 6.3.0
 */

/**
 * Gets the markup for the plugin install action button.
 *
 * @param string       $name           Plugin name.
 * @param array|object $data           {
 *     An array or object of plugin data. Can be retrieved from the API.
 *
 *     @type string   $slug             The plugin slug.
 *     @type string[] $requires_plugins An array of plugin dependency slugs.
 *     @type string   $version          The plugin's version string. Used when getting the install status.
 * }
 * @param bool         $compatible_php   The result of a PHP compatibility check.
 * @param bool         $compatible_wp    The result of a WP compatibility check.
 *
 * @return string $button The markup for the dependency row button.
 */
function wp_get_plugin_action_button( $name, $data, $compatible_php, $compatible_wp ) {
	$button                = '';
	$data                  = (object) $data;
	$status                = install_plugin_install_status( $data );
	$requires_plugins      = isset( $data->requires_plugins ) ? $data->requires_plugins : array();
	$plugin_dependency_met = true;

	// Check if plugin dependency is installed and active.
	foreach ( $requires_plugins as $dependency ) {
		$plugin_dependency_met = false;
		$active_plugins        = get_option( 'active_plugins' );
		foreach ( $active_plugins as $plugin_file ) {
			if ( str_contains( $plugin_file, '/' ) && explode( '/', $plugin_file )[0] === $dependency ) {
				$plugin_dependency_met = true;
				break;
			}
		}
	}

	sprintf(
		'<a class="install-now button" data-slug="%s" href="%s" aria-label="%s" data-name="%s">%s</a>',
		esc_attr( $data->slug ),
		esc_url( $status['url'] ),
		/* translators: %s: Plugin name and version. */
		esc_attr( sprintf( _x( 'Install %s now', 'plugin' ), $name ) ),
		esc_attr( $name ),
		__( 'Install Now' )
	);

	if ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) ) {
		switch ( $status['status'] ) {
			case 'install':
				if ( $status['url'] ) {
					if ( $compatible_php && $compatible_wp && $plugin_dependency_met ) {
						$button = sprintf(
							'<a class="install-now button" data-slug="%s" href="%s" aria-label="%s" data-name="%s">%s</a>',
							esc_attr( $data->slug ),
							esc_url( $status['url'] ),
							/* translators: %s: Plugin name and version. */
							esc_attr( sprintf( _x( 'Install %s now', 'plugin' ), $name ) ),
							esc_attr( $name ),
							__( 'Install Now' )
						);
					} else {
						$button = sprintf(
							'<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
							_x( 'Cannot Install', 'plugin' )
						);
					}
				}
				break;

			case 'update_available':
				if ( $status['url'] ) {
					if ( $compatible_php && $compatible_wp ) {
						$button = sprintf(
							'<a class="update-now button aria-button-if-js" data-plugin="%s" data-slug="%s" href="%s" aria-label="%s" data-name="%s">%s</a>',
							esc_attr( $status['file'] ),
							esc_attr( $data->slug ),
							esc_url( $status['url'] ),
							/* translators: %s: Plugin name and version. */
							esc_attr( sprintf( _x( 'Update %s now', 'plugin' ), $name ) ),
							esc_attr( $name ),
							__( 'Update Now' )
						);
					} else {
						$button = sprintf(
							'<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
							_x( 'Cannot Update', 'plugin' )
						);
					}
				}
				break;

			case 'latest_installed':
			case 'newer_installed':
				if ( is_plugin_active( $status['file'] ) ) {
					$button = sprintf(
						'<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
						_x( 'Active', 'plugin' )
					);
				} elseif ( current_user_can( 'activate_plugin', $status['file'] ) ) {
					if ( $compatible_php && $compatible_wp ) {
						$button_text = __( 'Activate' );
						/* translators: %s: Plugin name. */
						$button_label = _x( 'Activate %s', 'plugin' );
						$activate_url = add_query_arg(
							array(
								'_wpnonce' => wp_create_nonce( 'activate-plugin_' . $status['file'] ),
								'action'   => 'activate',
								'plugin'   => $status['file'],
							),
							network_admin_url( 'plugins.php' )
						);

						if ( is_network_admin() ) {
							$button_text = __( 'Network Activate' );
							/* translators: %s: Plugin name. */
							$button_label = _x( 'Network Activate %s', 'plugin' );
							$activate_url = add_query_arg( array( 'networkwide' => 1 ), $activate_url );
						}

						$button = sprintf(
							'<a href="%1$s" class="button button-primary activate-now" aria-label="%2$s">%3$s</a>',
							esc_url( $activate_url ),
							esc_attr( sprintf( $button_label, $name ) ),
							$button_text
						);
					} else {
						$button = sprintf(
							'<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
							_x( 'Cannot Activate', 'plugin' )
						);
					}
				} else {
					$button = sprintf(
						'<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
						_x( 'Installed', 'plugin' )
					);
				}
				break;
		}

		return $button;
	}
}
