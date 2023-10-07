<?php
/**
 * WordPress Plugin Administration API: WP_Plugin_Dependencies class
 *
 * Replace in WP_Plugin_Install_List_Table::display_rows()
 * $action_links[] = wp_get_plugin_action_button( $name, $plugin, $compatible_php, $compatible_wp );
 *
 * @package WordPress
 * @subpackage Administration
 * @since 6.5.0
 */

/**
 * Gets the markup for the plugin install action button.
 *
 * @since 6.5.0
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
 * @return string $button The markup for the dependency row button.
 * phpcs:disable
 */
function wp_get_plugin_action_button( $name, $data, $compatible_php, $compatible_wp ) {
	$button           = '';
	$data             = (object) $data;
	$status           = install_plugin_install_status( $data );
	$requires_plugins = $data->requires_plugins ?? array();

	// Determine the status of plugin dependencies.
	$installed_plugins                   = get_plugins();
	$active_plugins                      = get_option( 'active_plugins' );
	$plugin_dependencies_count           = count( $requires_plugins );
	$installed_plugin_dependencies_count = 0;
	$active_plugin_dependencies_count    = 0;
	foreach ( $requires_plugins as $dependency ) {
		foreach ( array_keys( $installed_plugins ) as $installed_plugin_file ) {
			if ( str_contains( $installed_plugin_file, '/' ) && explode( '/', $installed_plugin_file )[0] === $dependency ) {
				++$installed_plugin_dependencies_count;
			}
		}

		foreach ( $active_plugins as $active_plugin_file ) {
			if ( str_contains( $active_plugin_file, '/' ) && explode( '/', $active_plugin_file )[0] === $dependency ) {
				++$active_plugin_dependencies_count;
			}
		}
	}
	$all_plugin_dependencies_installed = $installed_plugin_dependencies_count === $plugin_dependencies_count;
	$all_plugin_dependencies_active    = $active_plugin_dependencies_count === $plugin_dependencies_count;

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
					if ( $compatible_php && $compatible_wp && $all_plugin_dependencies_installed && ! empty( $data->download_link ) ) {
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
							'<button type="button" class="install-now button button-disabled" disabled="disabled">%s</button>',
							_x( 'Install Now', 'plugin' )
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
							_x( 'Update Now', 'plugin' )
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
					if ( $compatible_php && $compatible_wp && $all_plugin_dependencies_active ) {
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
							is_network_admin() ? _x( 'Network Activate %s', 'plugin' ) : _x( 'Activate', 'plugin' )
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

/**
 * Displays plugin information in dialog box form.
 *
 * ONLY PART IS FOR CORE MERGE. See "// START CORE MERGE".
 *
 * @global string $tab
 */
function pd_install_plugin_information() {
	global $tab;

	if ( empty( $_REQUEST['plugin'] ) ) {
		return;
	}

	$api = plugins_api(
		'plugin_information',
		array(
			'slug' => wp_unslash( $_REQUEST['plugin'] ),
		)
	);

	if ( is_wp_error( $api ) ) {
		wp_die( $api );
	}

	$plugins_allowedtags = array(
		'a'          => array(
			'href'   => array(),
			'title'  => array(),
			'target' => array(),
		),
		'abbr'       => array( 'title' => array() ),
		'acronym'    => array( 'title' => array() ),
		'code'       => array(),
		'pre'        => array(),
		'em'         => array(),
		'strong'     => array(),
		'div'        => array( 'class' => array() ),
		'span'       => array( 'class' => array() ),
		'p'          => array(),
		'br'         => array(),
		'ul'         => array(),
		'ol'         => array(),
		'li'         => array(),
		'h1'         => array(),
		'h2'         => array(),
		'h3'         => array(),
		'h4'         => array(),
		'h5'         => array(),
		'h6'         => array(),
		'img'        => array(
			'src'   => array(),
			'class' => array(),
			'alt'   => array(),
		),
		'blockquote' => array( 'cite' => true ),
	);

	$plugins_section_titles = array(
		'description'  => _x( 'Description', 'Plugin installer section title' ),
		'installation' => _x( 'Installation', 'Plugin installer section title' ),
		'faq'          => _x( 'FAQ', 'Plugin installer section title' ),
		'screenshots'  => _x( 'Screenshots', 'Plugin installer section title' ),
		'changelog'    => _x( 'Changelog', 'Plugin installer section title' ),
		'reviews'      => _x( 'Reviews', 'Plugin installer section title' ),
		'other_notes'  => _x( 'Other Notes', 'Plugin installer section title' ),
	);

	// Sanitize HTML.
	foreach ( (array) $api->sections as $section_name => $content ) {
		$api->sections[ $section_name ] = wp_kses( $content, $plugins_allowedtags );
	}

	foreach ( array( 'version', 'author', 'requires', 'tested', 'homepage', 'downloaded', 'slug' ) as $key ) {
		if ( isset( $api->$key ) ) {
			$api->$key = wp_kses( $api->$key, $plugins_allowedtags );
		}
	}

	$_tab = esc_attr( $tab );

	// Default to the Description tab, Do not translate, API returns English.
	$section = isset( $_REQUEST['section'] ) ? wp_unslash( $_REQUEST['section'] ) : 'description';
	if ( empty( $section ) || ! isset( $api->sections[ $section ] ) ) {
		$section_titles = array_keys( (array) $api->sections );
		$section        = reset( $section_titles );
	}

	iframe_header( __( 'Plugin Installation' ) );

	$_with_banner = '';

	if ( ! empty( $api->banners ) && ( ! empty( $api->banners['low'] ) || ! empty( $api->banners['high'] ) ) ) {
		$_with_banner = 'with-banner';
		$low          = empty( $api->banners['low'] ) ? $api->banners['high'] : $api->banners['low'];
		$high         = empty( $api->banners['high'] ) ? $api->banners['low'] : $api->banners['high'];
		?>
		<style type="text/css">
			#plugin-information-title.with-banner {
				background-image: url( <?php echo esc_url( $low ); ?> );
			}
			@media only screen and ( -webkit-min-device-pixel-ratio: 1.5 ) {
				#plugin-information-title.with-banner {
					background-image: url( <?php echo esc_url( $high ); ?> );
				}
			}
		</style>
		<?php
	}

	echo '<div id="plugin-information-scrollable">';
	echo "<div id='{$_tab}-title' class='{$_with_banner}'><div class='vignette'></div><h2>{$api->name}</h2></div>";
	echo "<div id='{$_tab}-tabs' class='{$_with_banner}'>\n";

	foreach ( (array) $api->sections as $section_name => $content ) {
		if ( 'reviews' === $section_name && ( empty( $api->ratings ) || 0 === array_sum( (array) $api->ratings ) ) ) {
			continue;
		}

		if ( isset( $plugins_section_titles[ $section_name ] ) ) {
			$title = $plugins_section_titles[ $section_name ];
		} else {
			$title = ucwords( str_replace( '_', ' ', $section_name ) );
		}

		$class       = ( $section_name === $section ) ? ' class="current"' : '';
		$href        = add_query_arg(
			array(
				'tab'     => $tab,
				'section' => $section_name,
			)
		);
		$href        = esc_url( $href );
		$san_section = esc_attr( $section_name );
		echo "\t<a name='$san_section' href='$href' $class>$title</a>\n";
	}

	echo "</div>\n";

	?>
<div id="<?php echo $_tab; ?>-content" class='<?php echo $_with_banner; ?>'>
	<div class="fyi">
		<ul>
			<?php if ( ! empty( $api->version ) ) { ?>
				<li><strong><?php _e( 'Version:' ); ?></strong> <?php echo $api->version; ?></li>
			<?php } if ( ! empty( $api->author ) ) { ?>
				<li><strong><?php _e( 'Author:' ); ?></strong> <?php echo links_add_target( $api->author, '_blank' ); ?></li>
			<?php } if ( ! empty( $api->last_updated ) ) { ?>
				<li><strong><?php _e( 'Last Updated:' ); ?></strong>
					<?php
					/* translators: %s: Human-readable time difference. */
					printf( __( '%s ago' ), human_time_diff( strtotime( $api->last_updated ) ) );
					?>
				</li>
			<?php } if ( ! empty( $api->requires ) ) { ?>
				<li>
					<strong><?php _e( 'Requires WordPress Version:' ); ?></strong>
					<?php
					/* translators: %s: Version number. */
					printf( __( '%s or higher' ), $api->requires );
					?>
				</li>
			<?php } if ( ! empty( $api->tested ) ) { ?>
				<li><strong><?php _e( 'Compatible up to:' ); ?></strong> <?php echo $api->tested; ?></li>
			<?php } if ( ! empty( $api->requires_php ) ) { ?>
				<li>
					<strong><?php _e( 'Requires PHP Version:' ); ?></strong>
					<?php
					/* translators: %s: Version number. */
					printf( __( '%s or higher' ), $api->requires_php );
					?>
				</li>
			<?php } if ( isset( $api->active_installs ) ) { ?>
				<li><strong><?php _e( 'Active Installations:' ); ?></strong>
				<?php
				if ( $api->active_installs >= 1000000 ) {
					$active_installs_millions = floor( $api->active_installs / 1000000 );
					printf(
						/* translators: %s: Number of millions. */
						_nx( '%s+ Million', '%s+ Million', $active_installs_millions, 'Active plugin installations' ),
						number_format_i18n( $active_installs_millions )
					);
				} elseif ( $api->active_installs < 10 ) {
					_ex( 'Less Than 10', 'Active plugin installations' );
				} else {
					echo number_format_i18n( $api->active_installs ) . '+';
				}
				?>
				</li>
			<?php } if ( ! empty( $api->slug ) && empty( $api->external ) ) { ?>
				<li><a target="_blank" href="<?php echo esc_url( __( 'https://wordpress.org/plugins/' ) . $api->slug ); ?>/"><?php _e( 'WordPress.org Plugin Page &#187;' ); ?></a></li>
			<?php } if ( ! empty( $api->homepage ) ) { ?>
				<li><a target="_blank" href="<?php echo esc_url( $api->homepage ); ?>"><?php _e( 'Plugin Homepage &#187;' ); ?></a></li>
			<?php } if ( ! empty( $api->donate_link ) && empty( $api->contributors ) ) { ?>
				<li><a target="_blank" href="<?php echo esc_url( $api->donate_link ); ?>"><?php _e( 'Donate to this plugin &#187;' ); ?></a></li>
			<?php } ?>
		</ul>
		<?php if ( ! empty( $api->rating ) ) { ?>
			<h3><?php _e( 'Average Rating' ); ?></h3>
			<?php
			wp_star_rating(
				array(
					'rating' => $api->rating,
					'type'   => 'percent',
					'number' => $api->num_ratings,
				)
			);
			?>
			<p aria-hidden="true" class="fyi-description">
				<?php
				printf(
					/* translators: %s: Number of ratings. */
					_n( '(based on %s rating)', '(based on %s ratings)', $api->num_ratings ),
					number_format_i18n( $api->num_ratings )
				);
				?>
			</p>
			<?php
		}

		if ( ! empty( $api->ratings ) && array_sum( (array) $api->ratings ) > 0 ) {
			?>
			<h3><?php _e( 'Reviews' ); ?></h3>
			<p class="fyi-description"><?php _e( 'Read all reviews on WordPress.org or write your own!' ); ?></p>
			<?php
			foreach ( $api->ratings as $key => $ratecount ) {
				// Avoid div-by-zero.
				$_rating    = $api->num_ratings ? ( $ratecount / $api->num_ratings ) : 0;
				$aria_label = esc_attr(
					sprintf(
						/* translators: 1: Number of stars (used to determine singular/plural), 2: Number of reviews. */
						_n(
							'Reviews with %1$d star: %2$s. Opens in a new tab.',
							'Reviews with %1$d stars: %2$s. Opens in a new tab.',
							$key
						),
						$key,
						number_format_i18n( $ratecount )
					)
				);
				?>
				<div class="counter-container">
						<span class="counter-label">
							<?php
							printf(
								'<a href="%s" target="_blank" aria-label="%s">%s</a>',
								"https://wordpress.org/support/plugin/{$api->slug}/reviews/?filter={$key}",
								$aria_label,
								/* translators: %s: Number of stars. */
								sprintf( _n( '%d star', '%d stars', $key ), $key )
							);
							?>
						</span>
						<span class="counter-back">
							<span class="counter-bar" style="width: <?php echo 92 * $_rating; ?>px;"></span>
						</span>
					<span class="counter-count" aria-hidden="true"><?php echo number_format_i18n( $ratecount ); ?></span>
				</div>
				<?php
			}
		}
		if ( ! empty( $api->contributors ) ) {
			?>
			<h3><?php _e( 'Contributors' ); ?></h3>
			<ul class="contributors">
				<?php
				foreach ( (array) $api->contributors as $contrib_username => $contrib_details ) {
					$contrib_name = $contrib_details['display_name'];
					if ( ! $contrib_name ) {
						$contrib_name = $contrib_username;
					}
					$contrib_name = esc_html( $contrib_name );

					$contrib_profile = esc_url( $contrib_details['profile'] );
					$contrib_avatar  = esc_url( add_query_arg( 's', '36', $contrib_details['avatar'] ) );

					echo "<li><a href='{$contrib_profile}' target='_blank'><img src='{$contrib_avatar}' width='18' height='18' alt='' />{$contrib_name}</a></li>";
				}
				?>
			</ul>
					<?php if ( ! empty( $api->donate_link ) ) { ?>
				<a target="_blank" href="<?php echo esc_url( $api->donate_link ); ?>"><?php _e( 'Donate to this plugin &#187;' ); ?></a>
			<?php } ?>
				<?php } ?>
	</div>
	<div id="section-holder">
	<?php
	$requires_php = isset( $api->requires_php ) ? $api->requires_php : null;
	$requires_wp  = isset( $api->requires ) ? $api->requires : null;

	$compatible_php = is_php_version_compatible( $requires_php );
	$compatible_wp  = is_wp_version_compatible( $requires_wp );
	$tested_wp      = ( empty( $api->tested ) || version_compare( get_bloginfo( 'version' ), $api->tested, '<=' ) );

	if ( ! $compatible_php ) {
		echo '<div class="notice notice-error notice-alt"><p>';
		_e( '<strong>Error:</strong> This plugin <strong>requires a newer version of PHP</strong>.' );
		if ( current_user_can( 'update_php' ) ) {
			printf(
				/* translators: %s: URL to Update PHP page. */
				' ' . __( '<a href="%s" target="_blank">Click here to learn more about updating PHP</a>.' ),
				esc_url( wp_get_update_php_url() )
			);

			wp_update_php_annotation( '</p><p><em>', '</em>' );
		} else {
			echo '</p>';
		}
		echo '</div>';
	}

	if ( ! $tested_wp ) {
		echo '<div class="notice notice-warning notice-alt"><p>';
		_e( '<strong>Warning:</strong> This plugin <strong>has not been tested</strong> with your current version of WordPress.' );
		echo '</p></div>';
	} elseif ( ! $compatible_wp ) {
		echo '<div class="notice notice-error notice-alt"><p>';
		_e( '<strong>Error:</strong> This plugin <strong>requires a newer version of WordPress</strong>.' );
		if ( current_user_can( 'update_core' ) ) {
			printf(
				/* translators: %s: URL to WordPress Updates screen. */
				' ' . __( '<a href="%s" target="_parent">Click here to update WordPress</a>.' ),
				esc_url( self_admin_url( 'update-core.php' ) )
			);
		}
		echo '</p></div>';
	}

	foreach ( (array) $api->sections as $section_name => $content ) {
		$content = links_add_base_url( $content, 'https://wordpress.org/plugins/' . $api->slug . '/' );
		$content = links_add_target( $content, '_blank' );

		$san_section = esc_attr( $section_name );

		$display = ( $section_name === $section ) ? 'block' : 'none';

		echo "\t<div id='section-{$san_section}' class='section' style='display: {$display};'>\n";
		echo $content;
		echo "\t</div>\n";
	}
	echo "</div>\n";
	echo "</div>\n";
	echo "</div>\n"; // #plugin-information-scrollable
	echo "<div id='$tab-footer'>\n";
	if ( ! empty( $api->download_link ) && ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) ) ) {
		// TODO: START CORE MERGE.
		$button = wp_get_plugin_action_button( $api->name, $api, $compatible_php, $compatible_wp );
		$button = str_replace( 'class="', 'class="right ', $button );

		if ( ! str_contains( $button, __( 'Activate' ) ) ) {
			$button = str_replace( 'class="', 'id="plugin_install_from_iframe" class="', $button );
		}

		echo wp_kses_post( $button );
		// TODO: END CORE MERGE.
	}
	echo "</div>\n";

	iframe_footer();
	exit;
}

remove_action( 'install_plugins_pre_plugin-information', 'install_plugin_information' );
add_action( 'install_plugins_pre_plugin-information', 'pd_install_plugin_information' );
