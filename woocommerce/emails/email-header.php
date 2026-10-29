<?php
/**
 * Email Header — Altijara branded shell.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-header.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package Altijara
 * @version 7.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$c     = function_exists( 'altijara_mail_colors' ) ? altijara_mail_colors() : array(
	'brand'  => '#1897B8',
	'ink'    => '#262A33',
	'muted'  => '#6e7787',
	'salmon' => '#FFF1E5',
	'card'   => '#FFF7EF',
	'white'  => '#ffffff',
);
$site  = function_exists( 'altijara_mail_site_name' ) ? altijara_mail_site_name() : wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
$logo  = function_exists( 'altijara_mail_logo_url' ) ? altijara_mail_logo_url() : '';
$dir   = is_rtl() ? 'rtl' : 'ltr';
$align = is_rtl() ? 'right' : 'left';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo esc_attr( $dir ); ?>">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
	<meta content="width=device-width, initial-scale=1.0" name="viewport">
	<title><?php echo esc_html( $site ); ?></title>
	<style type="text/css">
		<?php wc_get_template( 'emails/email-styles.php' ); ?>
	</style>
</head>
<body <?php echo is_rtl() ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0" style="margin:0;padding:0;background:<?php echo esc_attr( $c['salmon'] ); ?>;font-family:Tahoma,Arial,Helvetica,sans-serif;">
	<table width="100%" id="outer_wrapper" role="presentation" cellpadding="0" cellspacing="0" border="0" style="background:<?php echo esc_attr( $c['salmon'] ); ?>;padding:32px 12px;">
		<tr>
			<td align="center">
				<table id="inner_wrapper" width="100%" role="presentation" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background:<?php echo esc_attr( $c['white'] ); ?>;border-radius:8px;overflow:hidden;">
					<tr>
						<td id="template_header" style="background:<?php echo esc_attr( $c['card'] ); ?>;padding:28px 24px 20px;text-align:center;border-bottom:3px solid <?php echo esc_attr( $c['brand'] ); ?>;">
							<?php if ( $logo ) : ?>
								<img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $site ); ?>" width="160" style="display:block;max-width:160px;height:auto;margin:0 auto;border:0;" />
							<?php else : ?>
								<span style="font-size:22px;font-weight:700;color:<?php echo esc_attr( $c['ink'] ); ?>;"><?php echo esc_html( $site ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td id="body_content" style="padding:28px 28px 8px;text-align:<?php echo esc_attr( $align ); ?>;color:<?php echo esc_attr( $c['ink'] ); ?>;font-size:15px;line-height:1.7;">
							<?php if ( ! empty( $email_heading ) ) : ?>
								<h1 style="margin:0 0 16px;font-size:20px;line-height:1.4;color:<?php echo esc_attr( $c['ink'] ); ?>;font-weight:700;"><?php echo esc_html( $email_heading ); ?></h1>
							<?php endif; ?>
