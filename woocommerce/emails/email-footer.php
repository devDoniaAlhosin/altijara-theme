<?php
/**
 * Email Footer — Altijara branded shell.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package Altijara
 * @version 7.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$c     = function_exists( 'altijara_mail_colors' ) ? altijara_mail_colors() : array(
	'brand' => '#1897B8',
	'ink'   => '#262A33',
	'muted' => '#6e7787',
	'white' => '#ffffff',
);
$site  = function_exists( 'altijara_mail_site_name' ) ? altijara_mail_site_name() : wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
$home  = home_url( '/' );
$year  = gmdate( 'Y' );
$align = is_rtl() ? 'right' : 'left';

$footer_text = get_option( 'woocommerce_email_footer_text', '' );
$footer_text = apply_filters( 'woocommerce_email_footer_text', $footer_text );
?>
							<?php if ( $footer_text ) : ?>
								<div id="credit" style="margin-top:24px;font-size:12px;line-height:1.6;color:<?php echo esc_attr( $c['muted'] ); ?>;text-align:<?php echo esc_attr( $align ); ?>;">
									<?php echo wp_kses_post( wpautop( wptexturize( $footer_text ) ) ); ?>
								</div>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td id="template_footer" style="background:<?php echo esc_attr( $c['ink'] ); ?>;padding:18px 24px;text-align:center;">
							<p style="margin:0;font-size:12px;line-height:1.5;color:#c5cad3;">
								<a href="<?php echo esc_url( $home ); ?>" style="color:<?php echo esc_attr( $c['brand'] ); ?>;text-decoration:none;"><?php echo esc_html( $site ); ?></a><br />
								&copy; <?php echo esc_html( $year ); ?>
							</p>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>
