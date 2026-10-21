<?php
/**
 * Template Name: اشترك
 * Subscribe page — match reference: social + fields + plans inside one card.
 *
 * @package Altijara
 */
get_header();

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
?>
<main class="container static-shell subscribe-shell">
	<section class="static-page static-page--subscribe">
		<header class="static-page-header">
			<h1 class="static-title"><?php echo esc_html( $title ); ?></h1>
			<p class="static-lead"><?php echo esc_html( $lead ); ?></p>
		</header>

		<div class="static-panel static-auth-panel subscribe-form-panel">
			<?php if ( $oauth_err ) : ?>
				<div class="static-success is-visible" role="alert" style="background:rgba(185,28,28,.08);border-color:rgba(185,28,28,.35);color:#7f1d1d;">
					<?php esc_html_e( 'تعذر إكمال التسجيل الاجتماعي. تحقق من إعدادات Google/Facebook في خيارات القالب.', 'altijara' ); ?>
				</div>
			<?php endif; ?>

			<form class="static-form" id="subscribeForm" action="#" method="post">
				<?php
				if ( function_exists( 'altijara_render_social_login_buttons' ) ) {
					altijara_render_social_login_buttons( 'subscribe' );
				}
				?>

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

				<?php if ( $plans ) : ?>
					<fieldset class="subscribe-plans">
						<legend class="subscribe-plans-legend"><?php esc_html_e( 'اختر الباقة', 'altijara' ); ?></legend>
						<input type="hidden" name="plan" id="subscribePlanValue" value="<?php echo esc_attr( ! empty( $plans[0]['value'] ) ? sanitize_key( $plans[0]['value'] ) : 'plan_0' ); ?>">
						<div class="static-plan-cards" role="radiogroup" aria-label="<?php esc_attr_e( 'اختر الباقة', 'altijara' ); ?>">
							<?php foreach ( $plans as $i => $plan ) :
								$val = ! empty( $plan['value'] ) ? sanitize_key( $plan['value'] ) : 'plan_' . $i;
								$id  = 'subscribe_plan_' . $val;
								?>
								<label class="static-plan-option<?php echo 0 === $i ? ' is-selected' : ''; ?>" for="<?php echo esc_attr( $id ); ?>">
									<input
										type="radio"
										id="<?php echo esc_attr( $id ); ?>"
										name="plan_preview"
										value="<?php echo esc_attr( $val ); ?>"
										class="subscribe-tier-input"
										<?php checked( 0 === $i ); ?>
									>
									<span class="plan-body">
										<strong class="plan-title"><?php echo esc_html( $plan['title'] ?? '' ); ?></strong>
										<span class="plan-text"><?php echo esc_html( $plan['text'] ?? '' ); ?></span>
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

				<div class="static-form-actions">
					<button type="submit" class="static-btn static-btn-primary auth-submit"><?php esc_html_e( 'تأكيد الاشتراك', 'altijara' ); ?></button>
				</div>

				<div class="static-success" id="subscribeSuccess" role="status" data-default="<?php echo esc_attr( $m['success_message'] ?? __( 'تم تأكيد اشتراكك — راقب بريدك قريباً.', 'altijara' ) ); ?>"></div>
				<p class="static-form-note"><?php esc_html_e( 'لديك حساب؟', 'altijara' ); ?> <a href="<?php echo esc_url( $login ); ?>"><?php esc_html_e( 'تسجيل الدخول', 'altijara' ); ?></a></p>
			</form>
		</div>
	</section>
</main>
<script>
(function () {
  var form = document.getElementById('subscribeForm');
  if (!form) return;
  var planHidden = document.getElementById('subscribePlanValue');
  document.querySelectorAll('.subscribe-tier-input').forEach(function (input) {
    input.addEventListener('change', function () {
      document.querySelectorAll('.static-plan-option').forEach(function (el) {
        el.classList.toggle('is-selected', el.contains(input) && input.checked);
      });
      if (planHidden) planHidden.value = input.value;
    });
  });
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var note = document.getElementById('subscribeSuccess');
    if (note) {
      note.textContent = note.getAttribute('data-default') || '';
      note.classList.add('is-visible');
    }
    form.reset();
    var first = form.querySelector('.subscribe-tier-input');
    if (first) {
      first.checked = true;
      first.dispatchEvent(new Event('change'));
    }
  });
})();
</script>
<?php
get_footer();
