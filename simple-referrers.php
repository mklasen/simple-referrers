<?php
/*
 * Plugin Name:       Simple Referrers
 * Plugin URI:        https://mklasen.com
 * Description:       Can it get more simple then this?
 * Version:           1.0
 * Author:            Marinus Klasen
 * Author URI:        https://mklasen.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       simple-referrers
*/

add_action( 'init', function () {
	if ( ! is_admin() && ! current_user_can( 'manage_options' ) ) {
		if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			$ignored_strings = apply_filters( 'simple_referrers_customize_ignored_strings', array( get_site_url() ) );
			$index           = true;

			foreach ( $ignored_strings as $string ) {
				if ( strpos( $_SERVER['HTTP_REFERER'], $string ) !== false ) {
					$index = false;
				}
			}

			if ( $index === true ) {
				global $wp;
				$target = home_url( $wp->request );

				$referrer = parse_url( $_SERVER['HTTP_REFERER'] );

				$referrers   = ! empty( get_option( 'simple_referrers' ) ) ? get_option( 'simple_referrers' ) : array();
				$referrers[] = array(
					'url'         => $_SERVER['HTTP_REFERER'],
					'time'        => time(),
					'domain'      => $referrer['host'],
					'domain_link' => $referrer['scheme'] . '://' . $referrer['host'],
					'target'      => $target,
				);
				update_option( 'simple_referrers', $referrers );
			}
		}
	}
} );

add_action( 'admin_menu', function () {
	//update_option('simple_referrers', array());
	add_submenu_page(
		'options-general.php',
		'Simple Referrers',
		'Simple Referrers',
		'manage_options',
		'simple-referrers',
		function () {
			echo '<div class="wrap">';
			$referrers = ! empty( get_option( 'simple_referrers' ) ) ? get_option( 'simple_referrers' ) : array();

			$referrers = apply_filters( 'simple_referrers_customize_output', $referrers );

			$unique_values = array();

			echo '<table style="background: white; width:100%;" cellpadding="10px" border="1">';
			echo '<tr>';
			echo '<th style="text-align: left;" colspan="5">';
			echo 'Simple Referrers';
			echo '</th>';
			echo '</tr>';
			echo '<tr>';
			echo '<td><strong>Date</strong></td>';
			echo '<td><strong>Referrer</strong></td>';
			echo '<td><strong>Target</strong></td>';
			echo '<td><strong>Date & Time</strong></td>';
			echo '<td><strong>Domain</strong></td>';
			echo '</tr>';
			if ( ! empty( $referrers ) ) {
				foreach ( array_reverse( $referrers ) as $ref ) {

					if ( in_array( $ref['url'], $unique_values ) ) {
						continue;
					}

					$unique_values[] = $ref['url'];

					$ref['url']         = esc_url( $ref['url'] );
					$ref['target']      = esc_url( $ref['target'] );
					$ref['time']        = esc_attr( $ref['time'] );
					$ref['domain_link'] = esc_url( $ref['domain_link'] );

					if ( ! empty( $ref['url'] ) ) {
						
						echo '<tr>';
						echo '<td>';
						echo '<i>' . date_i18n( 'd-m', $ref['time'] ) . '</i>';
						echo '</td>';
						echo '<td>';
						echo '<a href="' . $ref['url'] . '">' . ( strlen( $ref['url'] ) > 50 ? substr( $ref['url'], 0, 50 ) . '...' : $ref['url'] ) . '</a>';
						echo '</td>';
						echo '<td>';
						echo '<a href="' . $ref['target'] . '">' . $ref['target'] . '</a>';
						echo '</td>';
						echo '<td>';
						echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $ref['time'] );
						echo '</td>';
						echo '<td>';
						echo '<a href="' . $ref['domain_link'] . '">' . $ref['domain'] . '</a>';
						echo '</td>';
						echo '</tr>';
					}
				}
			} else {
				echo '<tr>';
				echo '<td colspan="4">';
				echo 'No referrers found.';
				echo '</td>';
				echo '</tr>';
			}
			echo '</table>';

			echo '</div>';
		}
	);
} );

