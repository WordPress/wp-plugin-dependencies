<?php
/**
 * Overriding WP_Plugin_Install_List_Table::display_rows()
 *
 * @package WordPress
 * @subpackage Administration
 */

namespace WP_Plugin_Dependencies;

require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-plugin-install-list-table.php';

/**
 * OverrideWP_Plugin_Install_List_Table::display_rows().
 * phpcs:disable
 */
class PD_Install_List_Table extends \WP_Plugin_Install_List_Table {

    /**
     * Display rows of plugin install table.
     *
     * @return void
     */
	public function display_rows() {
	 $plugins_allowedtags = array(
		'a'       => array(
			'href'   => array(),
			'title'  => array(),
			'target' => array(),
		),
		'abbr'    => array( 'title' => array() ),
		'acronym' => array( 'title' => array() ),
		'code'    => array(),
		'pre'     => array(),
		'em'      => array(),
		'strong'  => array(),
		'ul'      => array(),
		'ol'      => array(),
		'li'      => array(),
		'p'       => array(),
		'br'      => array(),
	);

	$plugins_group_titles = array(
		'Performance' => _x( 'Performance', 'Plugin installer group title' ),
		'Social'      => _x( 'Social', 'Plugin installer group title' ),
		'Tools'       => _x( 'Tools', 'Plugin installer group title' ),
	);

	$group = null;

	foreach ( (array) $this->items as $plugin ) {
		if ( is_object( $plugin ) ) {
			$plugin = (array) $plugin;
		}

		// Display the group heading if there is one.
		if ( isset( $plugin['group'] ) && $plugin['group'] !== $group ) {
			if ( isset( $this->groups[ $plugin['group'] ] ) ) {
				$group_name = $this->groups[ $plugin['group'] ];
				if ( isset( $plugins_group_titles[ $group_name ] ) ) {
					$group_name = $plugins_group_titles[ $group_name ];
				}
			} else {
				$group_name = $plugin['group'];
			}

			// Starting a new group, close off the divs of the last one.
			if ( ! empty( $group ) ) {
				echo '</div></div>';
			}

			echo '<div class="plugin-group"><h3>' . esc_html( $group_name ) . '</h3>';
			// Needs an extra wrapping div for nth-child selectors to work.
			echo '<div class="plugin-items">';

			$group = $plugin['group'];
		}

		$title = wp_kses( $plugin['name'], $plugins_allowedtags );

		// Remove any HTML from the description.
		$description = strip_tags( $plugin['short_description'] );

		$description .= $this->get_dependencies_notice( $plugin );

		/**
		 * Filters the plugin card description on the Add Plugins screen.
		 *
		 * @since 6.0.0
		 *
		 * @param string $description Plugin card description.
		 * @param array  $plugin      An array of plugin data. See {@see plugins_api()}
		 *                            for the list of possible values.
		 */
		$description = apply_filters( 'plugin_install_description', $description, $plugin );

		$version = wp_kses( $plugin['version'], $plugins_allowedtags );

		$name = strip_tags( $title . ' ' . $version );

		$author = wp_kses( $plugin['author'], $plugins_allowedtags );
		if ( ! empty( $author ) ) {
			/* translators: %s: Plugin author. */
			$author = ' <cite>' . sprintf( __( 'By %s' ), $author ) . '</cite>';
		}

		$requires_php = isset( $plugin['requires_php'] ) ? $plugin['requires_php'] : null;
		$requires_wp  = isset( $plugin['requires'] ) ? $plugin['requires'] : null;

		$compatible_php = is_php_version_compatible( $requires_php );
		$compatible_wp  = is_wp_version_compatible( $requires_wp );
		$tested_wp      = ( empty( $plugin['tested'] ) || version_compare( get_bloginfo( 'version' ), $plugin['tested'], '<=' ) );

		$action_links = array();

		$action_links[] = wp_get_plugin_action_button( $name, $plugin, $compatible_php, $compatible_wp );

		$details_link = self_admin_url(
			'plugin-install.php?tab=plugin-information&amp;plugin=' . $plugin['slug'] .
			'&amp;TB_iframe=true&amp;width=600&amp;height=550'
		);

		$action_links[] = sprintf(
			'<a href="%s" class="thickbox open-plugin-details-modal" aria-label="%s" data-title="%s">%s</a>',
			esc_url( $details_link ),
			/* translators: %s: Plugin name and version. */
			esc_attr( sprintf( __( 'More information about %s' ), $name ) ),
			esc_attr( $name ),
			__( 'More Details' )
		);

		if ( ! empty( $plugin['icons']['svg'] ) ) {
			$plugin_icon_url = $plugin['icons']['svg'];
		} elseif ( ! empty( $plugin['icons']['2x'] ) ) {
			$plugin_icon_url = $plugin['icons']['2x'];
		} elseif ( ! empty( $plugin['icons']['1x'] ) ) {
			$plugin_icon_url = $plugin['icons']['1x'];
		} else {
			$plugin_icon_url = $plugin['icons']['default'];
		}

		/**
		 * Filters the install action links for a plugin.
		 *
		 * @since 2.7.0
		 *
		 * @param string[] $action_links An array of plugin action links.
		 *                               Defaults are links to Details and Install Now.
		 * @param array    $plugin       An array of plugin data. See {@see plugins_api()}
		 *                               for the list of possible values.
		 */
		$action_links = apply_filters( 'plugin_install_action_links', $action_links, $plugin );

		$last_updated_timestamp = strtotime( $plugin['last_updated'] );
		?>
	<div class="plugin-card plugin-card-<?php echo sanitize_html_class( $plugin['slug'] ); ?>">
		<?php
		if ( ! $compatible_php || ! $compatible_wp ) {
			echo '<div class="notice inline notice-error notice-alt"><p>';
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
			echo '</p></div>';
		}
		?>
		<div class="plugin-card-top">
			<div class="name column-name">
				<h3>
					<a href="<?php echo esc_url( $details_link ); ?>" class="thickbox open-plugin-details-modal">
					<?php echo $title; ?>
					<img src="<?php echo esc_url( $plugin_icon_url ); ?>" class="plugin-icon" alt="" />
					</a>
				</h3>
			</div>
			<div class="action-links">
				<?php
				if ( $action_links ) {
					echo '<ul class="plugin-action-buttons"><li>' . implode( '</li><li>', $action_links ) . '</li></ul>';
				}
				?>
			</div>
			<div class="desc column-description">
				<p><?php echo $description; ?></p>
				<p class="authors"><?php echo $author; ?></p>
			</div>
		</div>
		<div class="plugin-card-bottom">
			<div class="vers column-rating">
				<?php
				wp_star_rating(
					array(
						'rating' => $plugin['rating'],
						'type'   => 'percent',
						'number' => $plugin['num_ratings'],
					)
				);
				?>
				<span class="num-ratings" aria-hidden="true">(<?php echo number_format_i18n( $plugin['num_ratings'] ); ?>)</span>
			</div>
			<div class="column-updated">
				<strong><?php _e( 'Last Updated:' ); ?></strong>
				<?php
					/* translators: %s: Human-readable time difference. */
					printf( __( '%s ago' ), human_time_diff( $last_updated_timestamp ) );
				?>
			</div>
			<div class="column-downloaded">
				<?php
				if ( $plugin['active_installs'] >= 1000000 ) {
					$active_installs_millions = floor( $plugin['active_installs'] / 1000000 );
					$active_installs_text     = sprintf(
						/* translators: %s: Number of millions. */
						_nx( '%s+ Million', '%s+ Million', $active_installs_millions, 'Active plugin installations' ),
						number_format_i18n( $active_installs_millions )
					);
				} elseif ( 0 === $plugin['active_installs'] ) {
					$active_installs_text = _x( 'Less Than 10', 'Active plugin installations' );
				} else {
					$active_installs_text = number_format_i18n( $plugin['active_installs'] ) . '+';
				}
				/* translators: %s: Number of installations. */
				printf( __( '%s Active Installations' ), $active_installs_text );
				?>
			</div>
			<div class="column-compatibility">
				<?php
				if ( ! $tested_wp ) {
					echo '<span class="compatibility-untested">' . __( 'Untested with your version of WordPress' ) . '</span>';
				} elseif ( ! $compatible_wp ) {
					echo '<span class="compatibility-incompatible">' . __( '<strong>Incompatible</strong> with your version of WordPress' ) . '</span>';
				} else {
					echo '<span class="compatibility-compatible">' . __( '<strong>Compatible</strong> with your version of WordPress' ) . '</span>';
				}
				?>
			</div>
		</div>
	</div>
		<?php
	}

	// Close off the group divs of the last one.
	if ( ! empty( $group ) ) {
		echo '</div></div>';
	}
}

	/**
	 * Returns a notice containing a list of dependencies required by the plugin.
	 *
	 * @since 6.5.0
	 *
	 * @param array  $plugin_data An array of plugin data. See {@see plugins_api()}
	 *                            for the list of possible values.
	 * @return string A notice containing a list of dependencies required by the plugin,
	 *                or an empty string if none is required.
	 */
	protected function get_dependencies_notice( $plugin_data ) {
		if ( empty( $plugin_data['requires_plugins'] ) ) {
			return '';
		}

		$no_name_markup  = '<div class="plugin-dependency"><span class="plugin-dependency-name">%s</span></div>';
		$has_name_markup = '<div class="plugin-dependency"><span class="plugin-dependency-name">%s</span> %s</div>';

		$dependencies_list = '';
		foreach ( $plugin_data['requires_plugins'] as $dependency ) {
			$dependency_data = \WP_Plugin_Dependencies::get_dependency_data( $dependency );

			if (
				false !== $dependency_data &&
				! empty( $dependency_data['name'] ) &&
				! empty( $dependency_data['slug'] ) &&
				! empty( $dependency_data['version'] )
			) {
				$more_details_link  = $this->get_more_details_link( $dependency_data['name'], $dependency_data['slug'] );
				$dependencies_list .= sprintf( $has_name_markup, esc_html( $dependency_data['name'] ), $more_details_link );
				continue;
			}

			$result = plugins_api( 'plugin_information', array( 'slug' => $dependency ) );

			if ( ! empty( $result->name ) ) {
				$more_details_link  = $this->get_more_details_link( $result->name, $result->slug );
				$dependencies_list .= sprintf( $has_name_markup, esc_html( $result->name ), $more_details_link );
				continue;
			}

			$dependencies_list .= sprintf( $no_name_markup, esc_html( $dependency ) );
		}

		$dependencies_notice = sprintf(
			'<div class="plugin-dependencies"><p class="plugin-dependencies-explainer-text">%s</p> %s</div>',
			'<strong>' . __( 'Additional plugins are required' ) . '</strong>',
			$dependencies_list
		);

		return $dependencies_notice;
	}

	/**
	 * Creates a 'More details' link for the plugin.
	 *
	 * @since 6.5.0
	 *
	 * @param string $name The plugin's name.
	 * @param string $slug The plugin's slug.
	 * @return string The 'More details' link for the plugin.
	 */
	protected function get_more_details_link( $name, $slug ) {
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

		$more_details_link = sprintf(
			'<a href="%1$s" class="more-details-link thickbox open-plugin-details-modal" aria-label="%2$s" data-title="%3$s">%4$s</a>',
			esc_url( $url ),
			/* translators: %s: Plugin name. */
			sprintf( __( 'More information about %s' ), esc_html( $name ) ),
			esc_attr( $name ),
			__( 'More Details' )
		);

		return $more_details_link;
	}
}
