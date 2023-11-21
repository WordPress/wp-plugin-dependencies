<?php
/**
 * Overriding WP_Plugins_List_Table::single_row()
 *
 * @package WordPress
 * @subpackage Administration
 */

namespace WP_Plugin_Dependencies;

require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-plugins-list-table.php';

/**
 * Override WP_Plugins_List_Table::single_row().
 * phpcs:disable
 */
class PD_List_Table extends \WP_Plugins_List_Table {

	/**
	 * @global string $status
	 * @global int $page
	 * @global string $s
	 * @global array $totals
	 *
	 * @param array $item
	 */
	public function single_row( $item ) {
		global $status, $page, $s, $totals;
		static $plugin_id_attrs = array();

		list( $plugin_file, $plugin_data ) = $item;

		$plugin_slug    = isset( $plugin_data['slug'] ) ? $plugin_data['slug'] : sanitize_title( $plugin_data['Name'] );
		$plugin_id_attr = $plugin_slug;

		// Ensure the ID attribute is unique.
		$suffix = 2;
		while ( in_array( $plugin_id_attr, $plugin_id_attrs, true ) ) {
			$plugin_id_attr = "$plugin_slug-$suffix";
			$suffix++;
		}

		$plugin_id_attrs[] = $plugin_id_attr;

		$context = $status;
		$screen  = $this->screen;

		// Pre-order.
		$actions = array(
			'deactivate' => '',
			'activate'   => '',
			'details'    => '',
			'delete'     => '',
		);

		// Do not restrict by default.
		$restrict_network_active = false;
		$restrict_network_only   = false;

		$requires_php = isset( $plugin_data['RequiresPHP'] ) ? $plugin_data['RequiresPHP'] : null;
		$requires_wp  = isset( $plugin_data['RequiresWP'] ) ? $plugin_data['RequiresWP'] : null;

		$compatible_php = is_php_version_compatible( $requires_php );
		$compatible_wp  = is_wp_version_compatible( $requires_wp );

		$has_active_dependents  = \WP_Plugin_Dependencies::has_active_dependents( $plugin_file );
		$has_unmet_dependencies = \WP_Plugin_Dependencies::has_unmet_dependencies( $plugin_file );

		if ( 'mustuse' === $context ) {
			$is_active = true;
		} elseif ( 'dropins' === $context ) {
			$dropins     = _get_dropins();
			$plugin_name = $plugin_file;

			if ( $plugin_file !== $plugin_data['Name'] ) {
				$plugin_name .= '<br />' . $plugin_data['Name'];
			}

			if ( true === ( $dropins[ $plugin_file ][1] ) ) { // Doesn't require a constant.
				$is_active   = true;
				$description = '<p><strong>' . $dropins[ $plugin_file ][0] . '</strong></p>';
			} elseif ( defined( $dropins[ $plugin_file ][1] ) && constant( $dropins[ $plugin_file ][1] ) ) { // Constant is true.
				$is_active   = true;
				$description = '<p><strong>' . $dropins[ $plugin_file ][0] . '</strong></p>';
			} else {
				$is_active   = false;
				$description = '<p><strong>' . $dropins[ $plugin_file ][0] . ' <span class="error-message">' . __( 'Inactive:' ) . '</span></strong> ' .
					sprintf(
						/* translators: 1: Drop-in constant name, 2: wp-config.php */
						__( 'Requires %1$s in %2$s file.' ),
						"<code>define('" . $dropins[ $plugin_file ][1] . "', true);</code>",
						'<code>wp-config.php</code>'
					) . '</p>';
			}

			if ( $plugin_data['Description'] ) {
				$description .= '<p>' . $plugin_data['Description'] . '</p>';
			}
		} else {
			if ( $screen->in_admin( 'network' ) ) {
				$is_active = is_plugin_active_for_network( $plugin_file );
			} else {
				$is_active               = is_plugin_active( $plugin_file );
				$restrict_network_active = ( is_multisite() && is_plugin_active_for_network( $plugin_file ) );
				$restrict_network_only   = ( is_multisite() && is_network_only_plugin( $plugin_file ) && ! $is_active );
			}

			if ( $screen->in_admin( 'network' ) ) {
				if ( $is_active && false === $has_active_dependents ) {
					if ( current_user_can( 'manage_network_plugins' ) ) {
						$actions['deactivate'] = sprintf(
							'<a href="%s" id="deactivate-%s" aria-label="%s">%s</a>',
							wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=' . urlencode( $plugin_file ) . '&amp;plugin_status=' . $context . '&amp;paged=' . $page . '&amp;s=' . $s, 'deactivate-plugin_' . $plugin_file ),
							esc_attr( $plugin_id_attr ),
							/* translators: %s: Plugin name. */
							esc_attr( sprintf( _x( 'Network Deactivate %s', 'plugin' ), $plugin_data['Name'] ) ),
							__( 'Network Deactivate' )
						);
					}
				} else {
					if ( current_user_can( 'manage_network_plugins' ) ) {
						if ( $compatible_php && $compatible_wp ) {
							if ( $has_unmet_dependencies ) {
								$actions['activate']  = __( 'Network Activate' );
								$actions['activate'] .= '<span class="screen-reader-text">' . __( 'Cannot activate due to unmet dependencies' ) . '</span>';
							} else {
								$actions['activate'] = sprintf(
									'<a href="%s" id="activate-%s" class="edit" aria-label="%s">%s</a>',
									wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . urlencode( $plugin_file ) . '&amp;plugin_status=' . $context . '&amp;paged=' . $page . '&amp;s=' . $s, 'activate-plugin_' . $plugin_file ),
									esc_attr( $plugin_id_attr ),
									/* translators: %s: Plugin name. */
									esc_attr( sprintf( _x( 'Network Activate %s', 'plugin' ), $plugin_data['Name'] ) ),
									__( 'Network Activate' )
								);
							}
						} else {
							$actions['activate'] = sprintf(
								'<span>%s</span>',
								_x( 'Cannot Activate', 'plugin' )
							);
						}
					}

					if ( current_user_can( 'delete_plugins' ) && false === $has_active_dependents && ! is_plugin_active( $plugin_file ) ) {
						$actions['delete'] = sprintf(
							'<a href="%s" id="delete-%s" class="delete" aria-label="%s">%s</a>',
							wp_nonce_url( 'plugins.php?action=delete-selected&amp;checked[]=' . urlencode( $plugin_file ) . '&amp;plugin_status=' . $context . '&amp;paged=' . $page . '&amp;s=' . $s, 'bulk-plugins' ),
							esc_attr( $plugin_id_attr ),
							/* translators: %s: Plugin name. */
							esc_attr( sprintf( _x( 'Delete %s', 'plugin' ), $plugin_data['Name'] ) ),
							__( 'Delete' )
						);
					}
				}
			} else {
				if ( $restrict_network_active ) {
					$actions = array(
						'network_active' => __( 'Network Active' ),
					);
				} elseif ( $restrict_network_only ) {
					$actions = array(
						'network_only' => __( 'Network Only' ),
					);
				} elseif ( $is_active ) {
					if ( current_user_can( 'deactivate_plugin', $plugin_file ) && false === $has_active_dependents ) {
						$actions['deactivate'] = sprintf(
							'<a href="%s" id="deactivate-%s" aria-label="%s">%s</a>',
							wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=' . urlencode( $plugin_file ) . '&amp;plugin_status=' . $context . '&amp;paged=' . $page . '&amp;s=' . $s, 'deactivate-plugin_' . $plugin_file ),
							esc_attr( $plugin_id_attr ),
							/* translators: %s: Plugin name. */
							esc_attr( sprintf( _x( 'Deactivate %s', 'plugin' ), $plugin_data['Name'] ) ),
							__( 'Deactivate' )
						);
					}

					if ( current_user_can( 'resume_plugin', $plugin_file ) && is_plugin_paused( $plugin_file ) ) {
						$actions['resume'] = sprintf(
							'<a href="%s" id="resume-%s" class="resume-link" aria-label="%s">%s</a>',
							wp_nonce_url( 'plugins.php?action=resume&amp;plugin=' . urlencode( $plugin_file ) . '&amp;plugin_status=' . $context . '&amp;paged=' . $page . '&amp;s=' . $s, 'resume-plugin_' . $plugin_file ),
							esc_attr( $plugin_id_attr ),
							/* translators: %s: Plugin name. */
							esc_attr( sprintf( _x( 'Resume %s', 'plugin' ), $plugin_data['Name'] ) ),
							__( 'Resume' )
						);
					}
				} else {
					if ( current_user_can( 'activate_plugin', $plugin_file ) ) {
						if ( $compatible_php && $compatible_wp ) {
							if ( $has_unmet_dependencies ) {
								$actions['activate']  = __( 'Activate' );
								$actions['activate'] .= '<span class="screen-reader-text">' . __( 'Cannot activate due to unmet dependencies' ) . '</span>';
							} else {
								$actions['activate'] = sprintf(
									'<a href="%s" id="activate-%s" class="edit" aria-label="%s">%s</a>',
									wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . urlencode( $plugin_file ) . '&amp;plugin_status=' . $context . '&amp;paged=' . $page . '&amp;s=' . $s, 'activate-plugin_' . $plugin_file ),
									esc_attr( $plugin_id_attr ),
									/* translators: %s: Plugin name. */
									esc_attr( sprintf( _x( 'Activate %s', 'plugin' ), $plugin_data['Name'] ) ),
									__( 'Activate' )
								);
							}
						} else {
							$actions['activate'] = sprintf(
								'<span>%s</span>',
								_x( 'Cannot Activate', 'plugin' )
							);
						}
					}

					if ( ! is_multisite() && current_user_can( 'delete_plugins' ) && false === $has_active_dependents ) {
						$actions['delete'] = sprintf(
							'<a href="%s" id="delete-%s" class="delete" aria-label="%s">%s</a>',
							wp_nonce_url( 'plugins.php?action=delete-selected&amp;checked[]=' . urlencode( $plugin_file ) . '&amp;plugin_status=' . $context . '&amp;paged=' . $page . '&amp;s=' . $s, 'bulk-plugins' ),
							esc_attr( $plugin_id_attr ),
							/* translators: %s: Plugin name. */
							esc_attr( sprintf( _x( 'Delete %s', 'plugin' ), $plugin_data['Name'] ) ),
							__( 'Delete' )
						);
					}
				} // End if $is_active.
			} // End if $screen->in_admin( 'network' ).
		} // End if $context.

		$actions = array_filter( $actions );

		if ( $screen->in_admin( 'network' ) ) {

			/**
			 * Filters the action links displayed for each plugin in the Network Admin Plugins list table.
			 *
			 * @since 3.1.0
			 *
			 * @param string[] $actions     An array of plugin action links. By default this can include
			 *                              'activate', 'deactivate', and 'delete'.
			 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
			 * @param array    $plugin_data An array of plugin data. See get_plugin_data()
			 *                              and the {@see 'plugin_row_meta'} filter for the list
			 *                              of possible values.
			 * @param string   $context     The plugin context. By default this can include 'all',
			 *                              'active', 'inactive', 'recently_activated', 'upgrade',
			 *                              'mustuse', 'dropins', and 'search'.
			 */
			$actions = apply_filters( 'network_admin_plugin_action_links', $actions, $plugin_file, $plugin_data, $context );

			/**
			 * Filters the list of action links displayed for a specific plugin in the Network Admin Plugins list table.
			 *
			 * The dynamic portion of the hook name, `$plugin_file`, refers to the path
			 * to the plugin file, relative to the plugins directory.
			 *
			 * @since 3.1.0
			 *
			 * @param string[] $actions     An array of plugin action links. By default this can include
			 *                              'activate', 'deactivate', and 'delete'.
			 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
			 * @param array    $plugin_data An array of plugin data. See get_plugin_data()
			 *                              and the {@see 'plugin_row_meta'} filter for the list
			 *                              of possible values.
			 * @param string   $context     The plugin context. By default this can include 'all',
			 *                              'active', 'inactive', 'recently_activated', 'upgrade',
			 *                              'mustuse', 'dropins', and 'search'.
			 */
			$actions = apply_filters( "network_admin_plugin_action_links_{$plugin_file}", $actions, $plugin_file, $plugin_data, $context );

		} else {

			/**
			 * Filters the action links displayed for each plugin in the Plugins list table.
			 *
			 * @since 2.5.0
			 * @since 2.6.0 The `$context` parameter was added.
			 * @since 4.9.0 The 'Edit' link was removed from the list of action links.
			 *
			 * @param string[] $actions     An array of plugin action links. By default this can include
			 *                              'activate', 'deactivate', and 'delete'. With Multisite active
			 *                              this can also include 'network_active' and 'network_only' items.
			 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
			 * @param array    $plugin_data An array of plugin data. See get_plugin_data()
			 *                              and the {@see 'plugin_row_meta'} filter for the list
			 *                              of possible values.
			 * @param string   $context     The plugin context. By default this can include 'all',
			 *                              'active', 'inactive', 'recently_activated', 'upgrade',
			 *                              'mustuse', 'dropins', and 'search'.
			 */
			$actions = apply_filters( 'plugin_action_links', $actions, $plugin_file, $plugin_data, $context );

			/**
			 * Filters the list of action links displayed for a specific plugin in the Plugins list table.
			 *
			 * The dynamic portion of the hook name, `$plugin_file`, refers to the path
			 * to the plugin file, relative to the plugins directory.
			 *
			 * @since 2.7.0
			 * @since 4.9.0 The 'Edit' link was removed from the list of action links.
			 *
			 * @param string[] $actions     An array of plugin action links. By default this can include
			 *                              'activate', 'deactivate', and 'delete'. With Multisite active
			 *                              this can also include 'network_active' and 'network_only' items.
			 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
			 * @param array    $plugin_data An array of plugin data. See get_plugin_data()
			 *                              and the {@see 'plugin_row_meta'} filter for the list
			 *                              of possible values.
			 * @param string   $context     The plugin context. By default this can include 'all',
			 *                              'active', 'inactive', 'recently_activated', 'upgrade',
			 *                              'mustuse', 'dropins', and 'search'.
			 */
			$actions = apply_filters( "plugin_action_links_{$plugin_file}", $actions, $plugin_file, $plugin_data, $context );

		}

		$class       = $is_active ? 'active' : 'inactive';
		$checkbox_id = 'checkbox_' . md5( $plugin_file );
		$disabled    = '';

		if ( \WP_Plugin_Dependencies::has_active_dependents( $plugin_file ) || \WP_Plugin_Dependencies::has_unmet_dependencies( $plugin_file ) ) {
			$disabled = 'disabled';
		}

		if ( $restrict_network_active || $restrict_network_only || in_array( $status, array( 'mustuse', 'dropins' ), true ) || ! $compatible_php ) {
			$checkbox = '';
		} else {
			$checkbox = sprintf(
				'<label class="label-covers-full-cell" for="%1$s"><span class="screen-reader-text">%2$s</span></label>' .
				'<input type="checkbox" name="checked[]" value="%3$s" id="%1$s" ' . $disabled . '/>',
				$checkbox_id,
				/* translators: Hidden accessibility text. %s: Plugin name. */
				sprintf( __( 'Select %s' ), $plugin_data['Name'] ),
				esc_attr( $plugin_file )
			);
		}

		if ( 'dropins' !== $context ) {
			$description = '<p>' . ( $plugin_data['Description'] ? $plugin_data['Description'] : '&nbsp;' ) . '</p>';
			$plugin_name = $plugin_data['Name'];
		}

		if ( ! empty( $totals['upgrade'] ) && ! empty( $plugin_data['update'] )
			|| ! $compatible_php || ! $compatible_wp
		) {
			$class .= ' update';
		}

		$paused = ! $screen->in_admin( 'network' ) && is_plugin_paused( $plugin_file );

		if ( $paused ) {
			$class .= ' paused';
		}

		if ( is_uninstallable_plugin( $plugin_file ) ) {
			$class .= ' is-uninstallable';
		}

		printf(
			'<tr class="%s" data-slug="%s" data-plugin="%s">',
			esc_attr( $class ),
			esc_attr( $plugin_slug ),
			esc_attr( $plugin_file )
		);

		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		$auto_updates = (array) get_site_option( 'auto_update_plugins', array() );

		foreach ( $columns as $column_name => $column_display_name ) {
			$extra_classes = '';
			if ( in_array( $column_name, $hidden, true ) ) {
				$extra_classes = ' hidden';
			}

			switch ( $column_name ) {
				case 'cb':
					echo "<th scope='row' class='check-column'>$checkbox</th>";
					break;
				case 'name':
					echo "<td class='plugin-title column-primary'><strong>$plugin_name</strong>";
					echo $this->row_actions( $actions, true );
					echo '</td>';
					break;
				case 'description':
					$classes = 'column-description desc';

					echo "<td class='$classes{$extra_classes}'>
						<div class='plugin-description'>$description</div>
						<div class='$class second plugin-version-author-uri'>";

					$plugin_meta = array();
					if ( ! empty( $plugin_data['Version'] ) ) {
						/* translators: %s: Plugin version number. */
						$plugin_meta[] = sprintf( __( 'Version %s' ), $plugin_data['Version'] );
					}
					if ( ! empty( $plugin_data['Author'] ) ) {
						$author = $plugin_data['Author'];
						if ( ! empty( $plugin_data['AuthorURI'] ) ) {
							$author = '<a href="' . $plugin_data['AuthorURI'] . '">' . $plugin_data['Author'] . '</a>';
						}
						/* translators: %s: Plugin author name. */
						$plugin_meta[] = sprintf( __( 'By %s' ), $author );
					}

					// Details link using API info, if available.
					if ( isset( $plugin_data['slug'] ) && current_user_can( 'install_plugins' ) ) {
						$plugin_meta[] = sprintf(
							'<a href="%s" class="thickbox open-plugin-details-modal" aria-label="%s" data-title="%s">%s</a>',
							esc_url(
								network_admin_url(
									'plugin-install.php?tab=plugin-information&plugin=' . $plugin_data['slug'] .
									'&TB_iframe=true&width=600&height=550'
								)
							),
							/* translators: %s: Plugin name. */
							esc_attr( sprintf( __( 'More information about %s' ), $plugin_name ) ),
							esc_attr( $plugin_name ),
							__( 'View details' )
						);
					} elseif ( ! empty( $plugin_data['PluginURI'] ) ) {
						/* translators: %s: Plugin name. */
						$aria_label = sprintf( __( 'Visit plugin site for %s' ), $plugin_name );

						$plugin_meta[] = sprintf(
							'<a href="%s" aria-label="%s">%s</a>',
							esc_url( $plugin_data['PluginURI'] ),
							esc_attr( $aria_label ),
							__( 'Visit plugin site' )
						);
					}

					/**
					 * Filters the array of row meta for each plugin in the Plugins list table.
					 *
					 * @since 2.8.0
					 *
					 * @param string[] $plugin_meta An array of the plugin's metadata, including
					 *                              the version, author, author URI, and plugin URI.
					 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
					 * @param array    $plugin_data {
					 *     An array of plugin data.
					 *
					 *     @type string   $id               Plugin ID, e.g. `w.org/plugins/[plugin-name]`.
					 *     @type string   $slug             Plugin slug.
					 *     @type string   $plugin           Plugin basename.
					 *     @type string   $new_version      New plugin version.
					 *     @type string   $url              Plugin URL.
					 *     @type string   $package          Plugin update package URL.
					 *     @type string[] $icons            An array of plugin icon URLs.
					 *     @type string[] $banners          An array of plugin banner URLs.
					 *     @type string[] $banners_rtl      An array of plugin RTL banner URLs.
					 *     @type string   $requires         The version of WordPress which the plugin requires.
					 *     @type string   $tested           The version of WordPress the plugin is tested against.
					 *     @type string   $requires_php     The version of PHP which the plugin requires.
					 *     @type string   $upgrade_notice   The upgrade notice for the new plugin version.
					 *     @type bool     $update-supported Whether the plugin supports updates.
					 *     @type string   $Name             The human-readable name of the plugin.
					 *     @type string   $PluginURI        Plugin URI.
					 *     @type string   $Version          Plugin version.
					 *     @type string   $Description      Plugin description.
					 *     @type string   $Author           Plugin author.
					 *     @type string   $AuthorURI        Plugin author URI.
					 *     @type string   $TextDomain       Plugin textdomain.
					 *     @type string   $DomainPath       Relative path to the plugin's .mo file(s).
					 *     @type bool     $Network          Whether the plugin can only be activated network-wide.
					 *     @type string   $RequiresWP       The version of WordPress which the plugin requires.
					 *     @type string   $RequiresPHP      The version of PHP which the plugin requires.
					 *     @type string   $UpdateURI        ID of the plugin for update purposes, should be a URI.
					 *     @type string   $Title            The human-readable title of the plugin.
					 *     @type string   $AuthorName       Plugin author's name.
					 *     @type bool     $update           Whether there's an available update. Default null.
					 * }
					 * @param string   $status      Status filter currently applied to the plugin list. Possible
					 *                              values are: 'all', 'active', 'inactive', 'recently_activated',
					 *                              'upgrade', 'mustuse', 'dropins', 'search', 'paused',
					 *                              'auto-update-enabled', 'auto-update-disabled'.
					 */
					$plugin_meta = apply_filters( 'plugin_row_meta', $plugin_meta, $plugin_file, $plugin_data, $status );

					echo implode( ' | ', $plugin_meta );

					echo '</div>';

					if ( \WP_Plugin_Dependencies::has_dependents( $plugin_file ) ) {
						$this->add_dependents_to_dependency_plugin_row( $plugin_file );
					}

					if ( \WP_Plugin_Dependencies::has_dependencies( $plugin_file ) ) {
						$this->add_dependencies_to_dependent_plugin_row( $plugin_file );
					}

					/**
					 * Fires after plugin row meta.
					 *
					 * @since 6.5.0
					 *
					 * @param string $plugin_file Refer to {@see 'plugin_row_meta'} filter.
					 * @param array  $plugin_data Refer to {@see 'plugin_row_meta'} filter.
					 */
					do_action( 'after_plugin_row_meta', $plugin_file, $plugin_data );

					if ( $paused ) {
						$notice_text = __( 'This plugin failed to load properly and is paused during recovery mode.' );

						printf( '<p><span class="dashicons dashicons-warning"></span> <strong>%s</strong></p>', $notice_text );

						$error = wp_get_plugin_error( $plugin_file );

						if ( false !== $error ) {
							printf( '<div class="error-display"><p>%s</p></div>', wp_get_extension_error_description( $error ) );
						}
					}

					echo '</td>';
					break;
				case 'auto-updates':
					if ( ! $this->show_autoupdates || in_array( $status, array( 'mustuse', 'dropins' ), true ) ) {
						break;
					}

					echo "<td class='column-auto-updates{$extra_classes}'>";

					$html = array();

					if ( isset( $plugin_data['auto-update-forced'] ) ) {
						if ( $plugin_data['auto-update-forced'] ) {
							// Forced on.
							$text = __( 'Auto-updates enabled' );
						} else {
							$text = __( 'Auto-updates disabled' );
						}
						$action     = 'unavailable';
						$time_class = ' hidden';
					} elseif ( empty( $plugin_data['update-supported'] ) ) {
						$text       = '';
						$action     = 'unavailable';
						$time_class = ' hidden';
					} elseif ( in_array( $plugin_file, $auto_updates, true ) ) {
						$text       = __( 'Disable auto-updates' );
						$action     = 'disable';
						$time_class = '';
					} else {
						$text       = __( 'Enable auto-updates' );
						$action     = 'enable';
						$time_class = ' hidden';
					}

					$query_args = array(
						'action'        => "{$action}-auto-update",
						'plugin'        => $plugin_file,
						'paged'         => $page,
						'plugin_status' => $status,
					);

					$url = add_query_arg( $query_args, 'plugins.php' );

					if ( 'unavailable' === $action ) {
						$html[] = '<span class="label">' . $text . '</span>';
					} else {
						$html[] = sprintf(
							'<a href="%s" class="toggle-auto-update aria-button-if-js" data-wp-action="%s">',
							wp_nonce_url( $url, 'updates' ),
							$action
						);

						$html[] = '<span class="dashicons dashicons-update spin hidden" aria-hidden="true"></span>';
						$html[] = '<span class="label">' . $text . '</span>';
						$html[] = '</a>';
					}

					if ( ! empty( $plugin_data['update'] ) ) {
						$html[] = sprintf(
							'<div class="auto-update-time%s">%s</div>',
							$time_class,
							wp_get_auto_update_message()
						);
					}

					$html = implode( '', $html );

					/**
					 * Filters the HTML of the auto-updates setting for each plugin in the Plugins list table.
					 *
					 * @since 5.5.0
					 *
					 * @param string $html        The HTML of the plugin's auto-update column content,
					 *                            including toggle auto-update action links and
					 *                            time to next update.
					 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
					 * @param array  $plugin_data An array of plugin data. See get_plugin_data()
					 *                            and the {@see 'plugin_row_meta'} filter for the list
					 *                            of possible values.
					 */
					echo apply_filters( 'plugin_auto_update_setting_html', $html, $plugin_file, $plugin_data );

					echo '<div class="notice notice-error notice-alt inline hidden"><p></p></div>';
					echo '</td>';

					break;
				default:
					$classes = "$column_name column-$column_name $class";

					echo "<td class='$classes{$extra_classes}'>";

					/**
					 * Fires inside each custom column of the Plugins list table.
					 *
					 * @since 3.1.0
					 *
					 * @param string $column_name Name of the column.
					 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
					 * @param array  $plugin_data An array of plugin data. See get_plugin_data()
					 *                            and the {@see 'plugin_row_meta'} filter for the list
					 *                            of possible values.
					 */
					do_action( 'manage_plugins_custom_column', $column_name, $plugin_file, $plugin_data );

					echo '</td>';
			}
		}

		echo '</tr>';

		if ( ! $compatible_php || ! $compatible_wp ) {
			printf(
				'<tr class="plugin-update-tr">' .
				'<td colspan="%s" class="plugin-update colspanchange">' .
				'<div class="update-message notice inline notice-error notice-alt"><p>',
				esc_attr( $this->get_column_count() )
			);

			if ( ! $compatible_php && ! $compatible_wp ) {
				_e( 'This plugin does not work with your versions of WordPress and PHP.' );
				if ( current_user_can( 'update_core' ) && current_user_can( 'update_php' ) ) {
					printf(
						/* translators: 1: URL to WordPress Updates screen, 2: URL to Update PHP page. */
						' ' . __( '<a href="%1$s">Please update WordPress</a>, and then <a href="%2$s">learn more about updating PHP</a>.' ),
						self_admin_url( 'update-core.php' ),
						esc_url( wp_get_update_php_url() )
					);
					wp_update_php_annotation( '</p><p><em>', '</em>' );
				} elseif ( current_user_can( 'update_core' ) ) {
					printf(
						/* translators: %s: URL to WordPress Updates screen. */
						' ' . __( '<a href="%s">Please update WordPress</a>.' ),
						self_admin_url( 'update-core.php' )
					);
				} elseif ( current_user_can( 'update_php' ) ) {
					printf(
						/* translators: %s: URL to Update PHP page. */
						' ' . __( '<a href="%s">Learn more about updating PHP</a>.' ),
						esc_url( wp_get_update_php_url() )
					);
					wp_update_php_annotation( '</p><p><em>', '</em>' );
				}
			} elseif ( ! $compatible_wp ) {
				_e( 'This plugin does not work with your version of WordPress.' );
				if ( current_user_can( 'update_core' ) ) {
					printf(
						/* translators: %s: URL to WordPress Updates screen. */
						' ' . __( '<a href="%s">Please update WordPress</a>.' ),
						self_admin_url( 'update-core.php' )
					);
				}
			} elseif ( ! $compatible_php ) {
				_e( 'This plugin does not work with your version of PHP.' );
				if ( current_user_can( 'update_php' ) ) {
					printf(
						/* translators: %s: URL to Update PHP page. */
						' ' . __( '<a href="%s">Learn more about updating PHP</a>.' ),
						esc_url( wp_get_update_php_url() )
					);
					wp_update_php_annotation( '</p><p><em>', '</em>' );
				}
			}

			echo '</p></div></td></tr>';
		}

		/**
		 * Fires after each row in the Plugins list table.
		 *
		 * @since 2.3.0
		 * @since 5.5.0 Added 'auto-update-enabled' and 'auto-update-disabled'
		 *              to possible values for `$status`.
		 *
		 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
		 * @param array  $plugin_data An array of plugin data. See get_plugin_data()
		 *                            and the {@see 'plugin_row_meta'} filter for the list
		 *                            of possible values.
		 * @param string $status      Status filter currently applied to the plugin list.
		 *                            Possible values are: 'all', 'active', 'inactive',
		 *                            'recently_activated', 'upgrade', 'mustuse', 'dropins',
		 *                            'search', 'paused', 'auto-update-enabled', 'auto-update-disabled'.
		 */
		do_action( 'after_plugin_row', $plugin_file, $plugin_data, $status );

		/**
		 * Fires after each specific row in the Plugins list table.
		 *
		 * The dynamic portion of the hook name, `$plugin_file`, refers to the path
		 * to the plugin file, relative to the plugins directory.
		 *
		 * @since 2.7.0
		 * @since 5.5.0 Added 'auto-update-enabled' and 'auto-update-disabled'
		 *              to possible values for `$status`.
		 *
		 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
		 * @param array  $plugin_data An array of plugin data. See get_plugin_data()
		 *                            and the {@see 'plugin_row_meta'} filter for the list
		 *                            of possible values.
		 * @param string $status      Status filter currently applied to the plugin list.
		 *                            Possible values are: 'all', 'active', 'inactive',
		 *                            'recently_activated', 'upgrade', 'mustuse', 'dropins',
		 *                            'search', 'paused', 'auto-update-enabled', 'auto-update-disabled'.
		 */
		do_action( "after_plugin_row_{$plugin_file}", $plugin_file, $plugin_data, $status );
	}

		/**
	 * Prints a list of other plugins that depend on the plugin.
	 *
	 * @since 6.5.0
	 *
	 * @param string $dependency The dependency's filepath, relative to the plugins directory.
	 */
	protected function add_dependents_to_dependency_plugin_row( $dependency ) {
		$dependent_names = \WP_Plugin_Dependencies::get_dependent_names( $dependency );

		if ( empty( $dependent_names ) ) {
			return;
		}

		printf(
			'<div class="required-by"><strong>%1$s</strong> %2$s</div>',
			__( 'Required by:' ),
			esc_html( implode( ' | ', $dependent_names ) )
		);
	}

	/**
	 * Prints a list of other plugins that the plugin depends on.
	 *
	 * @since 6.5.0
	 *
	 * @param string $dependent The dependent plugin's filepath, relative to the plugins directory.
	 */
	protected function add_dependencies_to_dependent_plugin_row( $dependent ) {
		$dependency_names = \WP_Plugin_Dependencies::get_dependency_names( $dependent );

		if ( array() === $dependency_names ) {
			return;
		}

		$links = array();
		foreach ( $dependency_names as $slug => $name ) {
			$links[] = $this->get_dependency_view_details_link( $name, $slug );
		}

		printf(
			'<div class="requires"><strong>%1$s</strong> %2$s</div>',
			__( 'Requires:' ),
			implode( ' | ', $links )
		);
	}

	/**
	 * Returns a 'View details' like link for a dependency.
	 *
	 * @since 6.5.0
	 *
	 * @param string $name The dependency's name.
	 * @param string $slug The dependency's slug.
	 * @return string A 'View details' link for the dependency.
	 */
	protected function get_dependency_view_details_link( $name, $slug ) {
		$dependency_data = \WP_Plugin_Dependencies::get_dependency_data( $slug );

		if ( false === $dependency_data
			|| $name === $slug
			|| $name !== $dependency_data['name']
			|| empty( $dependency_data['version'] )
		) {
			return $name;
		}

		return $this->get_view_details_link( $name, $slug );
	}

	/**
	 * Returns a 'View details' link for the plugin.
	 *
	 * @since 6.5.0
	 *
	 * @param string $name The plugin's name.
	 * @param string $slug The plugin's slug.
	 * @return string A 'View details' link for the plugin.
	 */
	protected function get_view_details_link( $name, $slug ) {
		$url = add_query_arg(
			array(
				'tab'       => 'plugin-information',
				'plugin'    => $slug,
				'TB_iframe' => 'true',
				'width'     => '600',
				'height'    => '550',
			),
			network_admin_url( 'plugin-install.php' )
		);

		$name_attr = esc_attr( $name );
		return sprintf(
			"<a href='%s' class='thickbox open-plugin-details-modal' aria-label='%s' data-title='%s'>%s</a>",
			esc_url( $url ),
			/* translators: %s: Plugin name. */
			sprintf( __( 'More information about %s' ), $name_attr ),
			$name_attr,
			esc_html( $name )
		);
	}
}
