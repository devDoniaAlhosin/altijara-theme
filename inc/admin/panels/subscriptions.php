<?php
/**
 * Settings panel: subscriptions.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render subscriptions settings panel.
 *
 * @param array<string, mixed> $ctx Panel context.
 */
function altijara_render_settings_panel_subscriptions( array $ctx ) {
	$opts    = $ctx['opts'];
	$defs    = $ctx['defs'];
	$header  = $ctx['header'];
	$footer  = $ctx['footer'];
	$article = $ctx['article'];
	$social  = $ctx['social'];
	$map     = $ctx['map'];

				$sub_opts = is_array( $opts['subscriptions'] ?? null ) ? $opts['subscriptions'] : array();
				$wc_on    = function_exists( 'altijara_payments_available' ) && altijara_payments_available();
				$products = array();
				if ( $wc_on && function_exists( 'wc_get_products' ) ) {
					$products = wc_get_products(
						array(
							'limit'  => 100,
							'status' => 'publish',
							'return' => 'objects',
						)
					);
				}
				?>
				<div class="altijara-admin-block">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'Subscriptions', 'altijara' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Master switch for magazine subscriptions on the site. When off: hide the header Subscribe button, footer/menu Subscribe links, login/register Subscribe CTAs, My Account plan card and Orders tab; paid checkout stays unavailable. Turn on when you are ready to sell plans.', 'altijara' ); ?></p>
					<table class="form-table">
						<tr>
							<th><?php esc_html_e( 'Enable subscriptions', 'altijara' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="altijara_subscriptions[enabled]" value="1" <?php checked( ! empty( $sub_opts['enabled'] ) ); ?>>
									<?php esc_html_e( 'Show Subscribe CTAs (header, footer, login, register), My Account plan/Orders, and paid checkout when mapped.', 'altijara' ); ?>
								</label>
							</td>
						</tr>
					</table>
				</div>
				<div class="altijara-admin-block">
					<h2 class="altijara-admin-block__title"><?php esc_html_e( 'WooCommerce payments', 'altijara' ); ?></h2>
					<?php if ( ! $wc_on ) : ?>
						<div class="notice notice-warning inline"><p>
							<?php esc_html_e( 'WooCommerce is not active (or checkout page missing). Paid plans stay hidden on the subscribe page. Free newsletter still works.', 'altijara' ); ?>
						</p></div>
					<?php else : ?>
						<p class="description"><?php esc_html_e( 'Map each paid tier to a WooCommerce product (keep products hidden from the shop catalog). Checkout uses whatever gateways you enable in WooCommerce.', 'altijara' ); ?></p>
					<?php endif; ?>
					<table class="form-table">
						<tr>
							<th><label for="product_digital"><?php esc_html_e( 'Digital tier product', 'altijara' ); ?></label></th>
							<td>
								<select name="altijara_subscriptions[product_digital]" id="product_digital" <?php disabled( ! $wc_on ); ?>>
									<option value="0"><?php esc_html_e( '— Not mapped —', 'altijara' ); ?></option>
									<?php foreach ( $products as $product ) : ?>
										<option value="<?php echo esc_attr( $product->get_id() ); ?>" <?php selected( (int) ( $sub_opts['product_digital'] ?? 0 ), $product->get_id() ); ?>>
											<?php echo esc_html( $product->get_name() . ' (#' . $product->get_id() . ')' ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<th><label for="product_full"><?php esc_html_e( 'Full / print tier product', 'altijara' ); ?></label></th>
							<td>
								<select name="altijara_subscriptions[product_full]" id="product_full" <?php disabled( ! $wc_on ); ?>>
									<option value="0"><?php esc_html_e( '— Not mapped —', 'altijara' ); ?></option>
									<?php foreach ( $products as $product ) : ?>
										<option value="<?php echo esc_attr( $product->get_id() ); ?>" <?php selected( (int) ( $sub_opts['product_full'] ?? 0 ), $product->get_id() ); ?>>
											<?php echo esc_html( $product->get_name() . ' (#' . $product->get_id() . ')' ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<th><label for="grace_days"><?php esc_html_e( 'Grace days after expiry', 'altijara' ); ?></label></th>
							<td>
								<input type="number" min="0" max="30" class="small-text" id="grace_days" name="altijara_subscriptions[grace_days]" value="<?php echo esc_attr( (int) ( $sub_opts['grace_days'] ?? 3 ) ); ?>">
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Paid coming soon', 'altijara' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="altijara_subscriptions[paid_coming_soon]" value="1" <?php checked( ! empty( $sub_opts['paid_coming_soon'] ) ); ?>>
									<?php esc_html_e( 'Hide paid checkout even when Woo is active (show “coming soon” on paid tiers).', 'altijara' ); ?>
								</label>
							</td>
						</tr>
					</table>
				</div>
				<div class="altijara-panel-actions">
					<?php submit_button( __( 'Save Subscriptions', 'altijara' ), 'primary', 'submit', false ); ?>
				</div>

<?php
}
