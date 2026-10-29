<?php
/**
 * Shared HTML email shell for theme + WooCommerce.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default + saved emails options.
 *
 * @return array<string, mixed>
 */
function altijara_get_emails_settings() {
	$defs = function_exists( 'altijara_default_options' ) ? altijara_default_options() : array();
	$defs = isset( $defs['emails'] ) && is_array( $defs['emails'] ) ? $defs['emails'] : array();
	$saved = function_exists( 'altijara_get_option' ) ? altijara_get_option( 'emails', array() ) : array();
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	$merged = array_merge( $defs, $saved );
	if ( ! empty( $GLOBALS['altijara_emails_settings_override'] ) && is_array( $GLOBALS['altijara_emails_settings_override'] ) ) {
		return array_merge( $merged, $GLOBALS['altijara_emails_settings_override'] );
	}
	return $merged;
}

/**
 * Run a callback with temporary emails settings overrides (admin preview).
 *
 * @param array<string, mixed> $override Settings to merge on top of saved options.
 * @param callable             $callback Callback.
 * @return mixed
 */
function altijara_mail_with_settings_override( array $override, $callback ) {
	$prev = isset( $GLOBALS['altijara_emails_settings_override'] ) ? $GLOBALS['altijara_emails_settings_override'] : null;
	$GLOBALS['altijara_emails_settings_override'] = $override;
	try {
		return call_user_func( $callback );
	} finally {
		if ( null === $prev ) {
			unset( $GLOBALS['altijara_emails_settings_override'] );
		} else {
			$GLOBALS['altijara_emails_settings_override'] = $prev;
		}
	}
}

/**
 * Build a full HTML preview for a transactional email type.
 *
 * @param string               $type     reset|verify.
 * @param array<string, mixed> $override Optional unsaved field values.
 * @return array{type:string,subject:string,html:string}
 */
function altijara_mail_build_preview( $type, array $override = array() ) {
	$type = in_array( $type, array( 'reset', 'verify' ), true ) ? $type : 'reset';

	return altijara_mail_with_settings_override(
		$override,
		static function () use ( $type ) {
			$emails  = altijara_get_emails_settings();
			$sample  = add_query_arg( 'altijara_mail_preview', '1', home_url( '/' ) );
			if ( 'verify' === $type ) {
				$subject = (string) ( $emails['verify_subject'] ?? '' );
				$title   = (string) ( $emails['verify_heading'] ?? '' );
				$intro   = (string) ( $emails['verify_intro'] ?? '' );
				$cta     = (string) ( $emails['verify_cta'] ?? '' );
			} else {
				$subject = (string) ( $emails['reset_subject'] ?? '' );
				$title   = (string) ( $emails['reset_heading'] ?? '' );
				$intro   = (string) ( $emails['reset_intro'] ?? '' );
				$cta     = (string) ( $emails['reset_cta'] ?? '' );
			}
			$body  = '<p style="margin:0;">' . esc_html( $intro ) . '</p>';
			$body .= altijara_mail_cta_html( $sample, $cta );
			return array(
				'type'    => $type,
				'subject' => $subject,
				'html'    => altijara_mail_wrap_html( $title, $body ),
			);
		}
	);
}

/**
 * AJAX: live email preview for Theme Options → Emails.
 */
function altijara_ajax_mail_preview() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
	}
	check_ajax_referer( 'altijara_mail_preview', 'nonce' );

	$type = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : 'reset';
	$raw  = isset( $_POST['emails'] ) ? wp_unslash( $_POST['emails'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if ( ! is_array( $raw ) ) {
		$raw = array();
	}
	$settings = function_exists( 'altijara_sanitize_emails_input' )
		? altijara_sanitize_emails_input( $raw )
		: $raw;

	wp_send_json_success( altijara_mail_build_preview( $type, $settings ) );
}
add_action( 'wp_ajax_altijara_mail_preview', 'altijara_ajax_mail_preview' );

/**
 * Sanitize Theme Options → Emails POST.
 *
 * @param array<string, mixed> $raw Raw input.
 * @return array<string, mixed>
 */
function altijara_sanitize_emails_input( $raw ) {
	$defs = function_exists( 'altijara_default_options' ) ? altijara_default_options() : array();
	$defs = isset( $defs['emails'] ) && is_array( $defs['emails'] ) ? $defs['emails'] : array();
	$raw  = is_array( $raw ) ? $raw : array();

	$text = static function ( $key ) use ( $raw, $defs ) {
		if ( ! isset( $raw[ $key ] ) ) {
			return isset( $defs[ $key ] ) ? (string) $defs[ $key ] : '';
		}
		return sanitize_text_field( (string) $raw[ $key ] );
	};
	$area = static function ( $key ) use ( $raw, $defs ) {
		if ( ! isset( $raw[ $key ] ) ) {
			return isset( $defs[ $key ] ) ? (string) $defs[ $key ] : '';
		}
		return sanitize_textarea_field( (string) $raw[ $key ] );
	};

	$out = array(
		'logo_url'       => isset( $raw['logo_url'] ) ? esc_url_raw( (string) $raw['logo_url'] ) : '',
		'from_name'      => $text( 'from_name' ),
		'footer_text'    => $text( 'footer_text' ),
		'show_raw_link'  => ! empty( $raw['show_raw_link'] ),
		'reset_subject'  => $text( 'reset_subject' ),
		'reset_heading'  => $text( 'reset_heading' ),
		'reset_intro'    => $area( 'reset_intro' ),
		'reset_cta'      => $text( 'reset_cta' ),
		'verify_subject' => $text( 'verify_subject' ),
		'verify_heading' => $text( 'verify_heading' ),
		'verify_intro'   => $area( 'verify_intro' ),
		'verify_cta'     => $text( 'verify_cta' ),
	);

	foreach ( array( 'reset_subject', 'reset_heading', 'reset_intro', 'reset_cta', 'verify_subject', 'verify_heading', 'verify_intro', 'verify_cta' ) as $required ) {
		if ( '' === trim( (string) $out[ $required ] ) && isset( $defs[ $required ] ) ) {
			$out[ $required ] = (string) $defs[ $required ];
		}
	}

	return $out;
}

/**
 * Brand colors for email (matches design defaults).
 *
 * @return array{brand:string,hover:string,ink:string,muted:string,salmon:string,card:string,white:string}
 */
function altijara_mail_colors() {
	$defaults = function_exists( 'altijara_default_design' ) ? altijara_default_design() : array();
	$saved    = function_exists( 'altijara_get_option' ) ? altijara_get_option( 'design', array() ) : array();
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	$d = array_merge( $defaults, $saved );
	return array(
		'brand'  => ! empty( $d['accent_brand'] ) ? $d['accent_brand'] : '#1897B8',
		'hover'  => ! empty( $d['accent_hover'] ) ? $d['accent_hover'] : '#12748e',
		'ink'    => ! empty( $d['ink_black'] ) ? $d['ink_black'] : '#262A33',
		'muted'  => ! empty( $d['ink_muted'] ) ? $d['ink_muted'] : '#6e7787',
		'salmon' => ! empty( $d['bg_salmon'] ) ? $d['bg_salmon'] : '#FFF1E5',
		'card'   => ! empty( $d['bg_card'] ) ? $d['bg_card'] : '#FFF7EF',
		'white'  => '#ffffff',
	);
}

/**
 * Site display name for emails.
 *
 * @return string
 */
function altijara_mail_site_name() {
	$emails = altijara_get_emails_settings();
	if ( ! empty( $emails['from_name'] ) ) {
		return (string) $emails['from_name'];
	}
	return wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
}

/**
 * Logo URL for email header.
 *
 * Order: Emails logo → Theme Options header logo → Customizer custom_logo.
 *
 * @return string
 */
function altijara_mail_logo_url() {
	$emails = altijara_get_emails_settings();
	if ( ! empty( $emails['logo_url'] ) ) {
		return (string) $emails['logo_url'];
	}

	$header = function_exists( 'altijara_get_option' ) ? altijara_get_option( 'header', array() ) : array();
	if ( is_array( $header ) && ! empty( $header['logo_url'] ) ) {
		return (string) $header['logo_url'];
	}

	if ( function_exists( 'altijara_default_options' ) ) {
		$defs = altijara_default_options();
		if ( ! empty( $defs['header']['logo_url'] ) ) {
			return (string) $defs['header']['logo_url'];
		}
	}

	$logo_id = (int) get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		$url = wp_get_attachment_image_url( $logo_id, 'medium' );
		if ( $url ) {
			return $url;
		}
	}
	return '';
}

/**
 * Build a CTA button HTML snippet (button only by default).
 *
 * @param string $url   Link URL.
 * @param string $label Button label.
 * @return string
 */
function altijara_mail_cta_html( $url, $label ) {
	$c      = altijara_mail_colors();
	$emails = altijara_get_emails_settings();
	$html   = sprintf(
		'<p style="margin:28px 0 0;text-align:center;"><a href="%1$s" style="display:inline-block;padding:14px 32px;background:%2$s;color:%3$s;text-decoration:none;font-weight:700;font-size:15px;border-radius:6px;line-height:1.3;">%4$s</a></p>',
		esc_url( $url ),
		esc_attr( $c['brand'] ),
		esc_attr( $c['white'] ),
		esc_html( $label )
	);
	if ( ! empty( $emails['show_raw_link'] ) ) {
		$html .= sprintf(
			'<p style="margin:12px 0 0;text-align:center;font-size:12px;line-height:1.5;color:%2$s;word-break:break-all;"><a href="%1$s" style="color:%3$s;text-decoration:underline;">%1$s</a></p>',
			esc_url( $url ),
			esc_attr( $c['muted'] ),
			esc_attr( $c['brand'] )
		);
	}
	return $html;
}

/**
 * Wrap body HTML in the Altijara email document.
 *
 * @param string $title     Heading shown under the brand bar.
 * @param string $body_html Inner HTML (already escaped by caller where needed).
 * @return string Full HTML email.
 */
function altijara_mail_wrap_html( $title, $body_html ) {
	$c         = altijara_mail_colors();
	$site      = altijara_mail_site_name();
	$blog_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$logo      = altijara_mail_logo_url();
	$emails    = altijara_get_emails_settings();
	$footer    = ! empty( $emails['footer_text'] ) ? (string) $emails['footer_text'] : $blog_name;
	$home      = home_url( '/' );
	$dir       = is_rtl() ? 'rtl' : 'ltr';
	$align     = is_rtl() ? 'right' : 'left';
	$year      = gmdate( 'Y' );
	$title     = $title ? $title : $blog_name;
	$brand_h   = $logo
		? sprintf(
			'<a href="%1$s" style="display:inline-block;text-decoration:none;border:0;"><img src="%2$s" alt="%3$s" width="180" style="display:block;max-width:180px;height:auto;margin:0 auto;border:0;" /></a>',
			esc_url( $home ),
			esc_url( $logo ),
			esc_attr( $blog_name )
		)
		: sprintf(
			'<a href="%1$s" style="font-size:24px;font-weight:700;color:%2$s;letter-spacing:0.02em;text-decoration:none;">%3$s</a>',
			esc_url( $home ),
			esc_attr( $c['ink'] ),
			esc_html( $blog_name )
		);

	return '<!DOCTYPE html>
<html lang="' . esc_attr( get_bloginfo( 'language' ) ) . '" dir="' . esc_attr( $dir ) . '">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>' . esc_html( $title ) . '</title>
</head>
<body style="margin:0;padding:0;background:' . esc_attr( $c['salmon'] ) . ';font-family:Tahoma,Arial,Helvetica,sans-serif;-webkit-text-size-adjust:100%;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:' . esc_attr( $c['salmon'] ) . ';padding:36px 14px;">
<tr><td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background:' . esc_attr( $c['white'] ) . ';border-radius:12px;overflow:hidden;box-shadow:0 8px 28px rgba(38,42,51,0.08);">
<tr><td style="background:' . esc_attr( $c['card'] ) . ';padding:32px 28px 24px;text-align:center;border-bottom:4px solid ' . esc_attr( $c['brand'] ) . ';">
' . $brand_h . '
<p style="margin:14px 0 0;font-size:12px;letter-spacing:0.04em;color:' . esc_attr( $c['muted'] ) . ';">' . esc_html( $blog_name ) . '</p>
</td></tr>
<tr><td style="padding:32px 28px 12px;text-align:' . esc_attr( $align ) . ';">
<h1 style="margin:0 0 18px;font-size:22px;line-height:1.35;color:' . esc_attr( $c['ink'] ) . ';font-weight:700;">' . esc_html( $title ) . '</h1>
<div style="font-size:15px;line-height:1.75;color:' . esc_attr( $c['ink'] ) . ';">' . $body_html . '</div>
</td></tr>
<tr><td style="padding:8px 28px 28px;text-align:' . esc_attr( $align ) . ';">
<p style="margin:20px 0 0;font-size:12px;line-height:1.6;color:' . esc_attr( $c['muted'] ) . ';">' . esc_html( $footer ) . '</p>
</td></tr>
<tr><td style="background:' . esc_attr( $c['ink'] ) . ';padding:20px 24px;text-align:center;">
<p style="margin:0;font-size:12px;line-height:1.55;color:#c5cad3;"><a href="' . esc_url( $home ) . '" style="color:' . esc_attr( $c['brand'] ) . ';text-decoration:none;font-weight:700;">' . esc_html( $blog_name ) . '</a><br />&copy; ' . esc_html( $year ) . ' ' . esc_html( $site ) . '</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>';
}

/**
 * Force HTML content-type for the next wp_mail call.
 *
 * @return string
 */
function altijara_mail_content_type_html() {
	return 'text/html';
}

/**
 * Optional From name from Theme Options → Emails.
 *
 * @param string $from_name Default from name.
 * @return string
 */
function altijara_mail_filter_from_name( $from_name ) {
	$emails = altijara_get_emails_settings();
	if ( ! empty( $emails['from_name'] ) ) {
		return (string) $emails['from_name'];
	}
	return $from_name;
}

/**
 * Send an HTML email using the Altijara shell.
 *
 * @param string $to        Recipient.
 * @param string $subject   Subject line.
 * @param string $title     Inner heading.
 * @param string $body_html Body HTML.
 * @return bool
 */
function altijara_mail_send_html( $to, $subject, $title, $body_html ) {
	if ( ! is_email( $to ) ) {
		return false;
	}
	$html = altijara_mail_wrap_html( $title, $body_html );
	add_filter( 'wp_mail_content_type', 'altijara_mail_content_type_html' );
	add_filter( 'wp_mail_from_name', 'altijara_mail_filter_from_name' );
	$sent = wp_mail( $to, $subject, $html, array( 'Content-Type: text/html; charset=UTF-8' ) );
	remove_filter( 'wp_mail_from_name', 'altijara_mail_filter_from_name' );
	remove_filter( 'wp_mail_content_type', 'altijara_mail_content_type_html' );
	return (bool) $sent;
}
