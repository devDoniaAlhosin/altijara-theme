<?php
/**
 * subscribe page content.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pid   = get_the_ID();
$m     = altijara_get_page_meta( $pid );
$title = get_the_title() ? get_the_title() : __( 'اشترك', 'altijara' );
$lead  = ! empty( $m['lead'] )
	? $m['lead']
	: __( 'أبرز التقارير والحوارات وأعداد المجلة — مباشرة إلى بريدك.', 'altijara' );
$plans = ! empty( $m['plans'] ) && is_array( $m['plans'] ) ? $m['plans'] : array();
if ( empty( $plans ) && function_exists( 'altijara_page_meta_defaults' ) ) {
	$defs  = altijara_page_meta_defaults( 'subscribe' );
	$plans = ! empty( $defs['plans'] ) ? $defs['plans'] : array();
}
$consent = ! empty( $m['consent_text'] )
	? $m['consent_text']
	: __( 'أوافق على استلام الرسائل من مجلة التجارة ويمكنني إلغاء الاشتراك في أي وقت.', 'altijara' );
$urls  = function_exists( 'altijara_theme_urls' ) ? altijara_theme_urls() : array();
$login = $urls['login'] ?? home_url( '/login/' );
$oauth_err = isset( $_GET['oauth_error'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$notice    = isset( $_GET['auth_notice'] ) ? sanitize_key( wp_unslash( $_GET['auth_notice'] ) ) : ''; // phpcs:ignore
$msg       = $notice && function_exists( 'altijara_auth_notice_message' ) ? altijara_auth_notice_message( $notice ) : '';
$is_err    = $msg && ! in_array( $notice, array( 'subscribed', 'created' ), true );

$payments_on = function_exists( 'altijara_paid_plans_available' ) && altijara_paid_plans_available();
$tiers_meta  = function_exists( 'altijara_subscription_tiers' ) ? altijara_subscription_tiers() : array();
$logged_in   = is_user_logged_in();
?>
<main class="container static-shell subscribe-shell">
	<section class="static-page static-page--subscribe">
		<header class="static-page-header">
			<h1 class="static-title"><?php echo esc_html( $title ); ?></h1>
			<p class="static-lead"><?php echo esc_html( $lead ); ?></p>
		</header>

		<div class="static-panel static-auth-panel subscribe-form-panel">
			<?php if ( $oauth_err ) : ?>
				<div class="static-success is-visible auth-notice auth-notice--error" role="alert">
					<?php esc_html_e( 'تعذر إكمال التسجيل الاجتماعي. تحقق من إعدادات Google أو Facebook أو Apple في خيارات القالب.', 'altijara' ); ?>
				</div>
			<?php endif; ?>
			<?php if ( $msg ) : ?>
				<div class="static-success is-visible auth-notice<?php echo $is_err ? ' auth-notice--error' : ''; ?>" role="alert">
					<?php echo esc_html( $msg ); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! $payments_on ) : ?>
				<p class="subscribe-payments-note" role="status">
					<?php esc_html_e( 'الباقات المدفوعة غير متاحة حالياً. يمكنك الاشتراك في النشرة المجانية الآن.', 'altijara' ); ?>
				</p>
			<?php endif; ?>

			<form class="static-form" id="subscribeForm" action="<?php echo esc_url( get_permalink() ); ?>" method="post">
				<?php wp_nonce_field( 'altijara_subscribe', 'altijara_auth_nonce' ); ?>
				<input type="hidden" name="altijara_subscribe" value="1">

				<?php
				if ( ! $logged_in && function_exists( 'altijara_render_social_login_buttons' ) ) {
					altijara_render_social_login_buttons( 'subscribe' );
				}
				?>

				<?php if ( ! $logged_in ) : ?>
					<div class="static-field">
						<label for="sName"><?php esc_html_e( 'الاسم', 'altijara' ); ?></label>
						<input id="sName" name="name" type="text" required autocomplete="name" placeholder="<?php esc_attr_e( 'الاسم الكامل', 'altijara' ); ?>">
					</div>
					<div class="static-field">
						<label for="sEmail"><?php esc_html_e( 'البريد الإلكتروني', 'altijara' ); ?></label>
						<input id="sEmail" name="email" type="email" required autocomplete="email" placeholder="name@example.com">
					</div>
					<div class="static-field">
						<label for="sPassword"><?php esc_html_e( 'كلمة المرور', 'altijara' ); ?></label>
						<input id="sPassword" name="password" type="password" required autocomplete="new-password" placeholder="********" minlength="8">
					</div>
				<?php else : ?>
					<p class="subscribe-logged-as">
						<?php
						printf(
							/* translators: %s: display name */
							esc_html__( 'مسجّل كـ %s', 'altijara' ),
							esc_html( wp_get_current_user()->display_name )
						);
						?>
					</p>
				<?php endif; ?>

				<?php if ( $plans ) : ?>
					<fieldset class="subscribe-plans">
						<legend class="subscribe-plans-legend"><?php esc_html_e( 'اختر الباقة', 'altijara' ); ?></legend>
						<?php
						$default_plan = 'newsletter';
						foreach ( $plans as $p ) {
							$pv = ! empty( $p['value'] ) ? sanitize_key( $p['value'] ) : '';
							$is_paid_plan = ! empty( $tiers_meta[ $pv ]['paid'] );
							if ( ! $is_paid_plan || $payments_on ) {
								$default_plan = $pv ? $pv : $default_plan;
								break;
							}
						}
						?>
						<input type="hidden" name="plan" id="subscribePlanValue" value="<?php echo esc_attr( $default_plan ); ?>">
						<div class="static-plan-cards" role="radiogroup" aria-label="<?php esc_attr_e( 'اختر الباقة', 'altijara' ); ?>">
							<?php
							$first_enabled = true;
							foreach ( $plans as $i => $plan ) :
								$val = ! empty( $plan['value'] ) ? sanitize_key( $plan['value'] ) : 'plan_' . $i;
								$id  = 'subscribe_plan_' . $val;
								$is_paid = ! empty( $tiers_meta[ $val ]['paid'] );
								$disabled = $is_paid && ! $payments_on;
								$selected = ( ! $disabled && $first_enabled );
								if ( $selected ) {
									$first_enabled = false;
								}
								?>
								<label class="static-plan-option<?php echo $selected ? ' is-selected' : ''; ?><?php echo $disabled ? ' is-disabled' : ''; ?>" for="<?php echo esc_attr( $id ); ?>">
									<input
										type="radio"
										id="<?php echo esc_attr( $id ); ?>"
										name="plan_preview"
										value="<?php echo esc_attr( $val ); ?>"
										class="subscribe-tier-input"
										data-paid="<?php echo $is_paid ? '1' : '0'; ?>"
										<?php checked( $selected ); ?>
										<?php disabled( $disabled ); ?>
									>
									<span class="plan-body">
										<strong class="plan-title"><?php echo esc_html( $plan['title'] ?? '' ); ?></strong>
										<span class="plan-text"><?php echo esc_html( $plan['text'] ?? '' ); ?></span>
										<?php if ( $disabled ) : ?>
											<span class="plan-soon"><?php esc_html_e( 'قريباً', 'altijara' ); ?></span>
										<?php endif; ?>
									</span>
								</label>
							<?php endforeach; ?>
						</div>
					</fieldset>
				<?php endif; ?>

				<label class="static-check">
					<input type="checkbox" name="consent" required>
					<span><?php echo esc_html( $consent ); ?></span>
				</label>

				<?php
				if ( function_exists( 'altijara_turnstile_widget_html' ) ) {
					altijara_turnstile_widget_html();
				}
				?>

				<div class="static-form-actions">
					<button type="submit" class="static-btn static-btn-primary auth-submit" id="subscribeSubmitBtn">
						<?php echo $payments_on ? esc_html__( 'تأكيد الاشتراك', 'altijara' ) : esc_html__( 'اشترك في النشرة', 'altijara' ); ?>
					</button>
				</div>

				<?php if ( ! $logged_in ) : ?>
					<p class="static-form-note"><?php esc_html_e( 'لديك حساب؟', 'altijara' ); ?> <a href="<?php echo esc_url( $login ); ?>"><?php esc_html_e( 'تسجيل الدخول', 'altijara' ); ?></a></p>
				<?php endif; ?>
			</form>
		</div>
	</section>
</main>
<script>
(function () {
  var form = document.getElementById('subscribeForm');
  if (!form) return;
  var planHidden = document.getElementById('subscribePlanValue');
  var btn = document.getElementById('subscribeSubmitBtn');
  document.querySelectorAll('.subscribe-tier-input').forEach(function (input) {
    input.addEventListener('change', function () {
      if (input.disabled) return;
      document.querySelectorAll('.static-plan-option').forEach(function (el) {
        el.classList.toggle('is-selected', el.contains(input) && input.checked);
      });
      if (planHidden) planHidden.value = input.value;
      if (btn) {
        btn.textContent = input.getAttribute('data-paid') === '1'
          ? <?php echo wp_json_encode( __( 'المتابعة للدفع', 'altijara' ) ); ?>
          : <?php echo wp_json_encode( __( 'تأكيد الاشتراك', 'altijara' ) ); ?>;
      }
    });
  });
})();
</script>
<?php

