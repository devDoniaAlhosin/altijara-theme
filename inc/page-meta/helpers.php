<?php
/**
 * Small front-end helpers for page meta values.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tel href helper (strip spaces).
 *
 * @param string $phone Phone display string.
 * @return string
 */
function altijara_phone_href( $phone ) {
	$digits = preg_replace( '/[^\d+]/', '', (string) $phone );
	return $digits ? 'tel:' . $digits : '#';
}

/**
 * Whether page meta includes a form plugin shortcode.
 *
 * @param array<string, mixed> $meta Page meta.
 * @return bool
 */
function altijara_page_has_form_shortcode( $meta ) {
	return is_array( $meta ) && '' !== trim( (string) ( $meta['form_shortcode'] ?? '' ) );
}

/**
 * Print a form shortcode inside the page form styles.
 *
 * Not tied to a specific Quform form id — any shortcode works.
 *
 * @param string $shortcode Shortcode, for example [quform id="1"].
 * @param bool   $wrap      Wrap the output in the shortcode form class.
 */
function altijara_render_page_form_shortcode( $shortcode, $wrap = true ) {
	$shortcode = trim( (string) $shortcode );
	if ( '' === $shortcode ) {
		return;
	}
	if ( $wrap ) {
		echo '<div id="altijara-qf" class="static-form static-form--shortcode">';
	}
	echo do_shortcode( $shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode HTML
	if ( $wrap ) {
		echo '</div>';
	}
}

/**
 * Whether Quform layout compat assets should load.
 *
 * Loads on Contact / Advertise templates (where shortcodes are used) and on
 * any singular page that has a form_shortcode in page meta. Not form-id specific.
 *
 * @return bool
 */
function altijara_needs_quform_compat() {
	if ( is_admin() ) {
		return false;
	}
	if ( is_page_template( 'page-contact.php' ) || is_page_template( 'page-advertise.php' ) ) {
		return true;
	}
	if ( ! is_singular() || ! function_exists( 'altijara_get_page_meta' ) ) {
		return false;
	}
	return altijara_page_has_form_shortcode( altijara_get_page_meta( get_the_ID() ) );
}

/**
 * Late CSS that beats Quform's per-form #id theme rules (any form id).
 * JS only strips Quform float/pixel inline styles — layout is CSS-only.
 */
function altijara_quform_compat_footer() {
	if ( ! altijara_needs_quform_compat() ) {
		return;
	}
	?>
	<style id="altijara-quform-compat">
		.static-form.static-form--shortcode {
			display: flow-root !important;
			isolation: isolate !important;
			width: 100% !important;
			max-width: 100% !important;
			box-sizing: border-box !important;
		}
		.static-form.static-form--shortcode .quform,
		.static-form.static-form--shortcode .quform-form,
		.static-form.static-form--shortcode .quform-form-inner,
		.static-form.static-form--shortcode .quform-element-page,
		.static-form.static-form--shortcode form {
			float: none !important;
			width: 100% !important;
			max-width: 100% !important;
			margin: 0 !important;
			padding: 0 !important;
			box-sizing: border-box !important;
		}
		/* Stack rows — do NOT 2-col the parent (that nested with row 2-col = "grid of 4"). */
		.static-form.static-form--shortcode .quform-elements,
		.static-form.static-form--shortcode .quform-child-elements {
			display: flex !important;
			flex-direction: column !important;
			gap: 10px !important;
			float: none !important;
			width: 100% !important;
			max-width: 100% !important;
			margin: 0 !important;
			padding: 0 !important;
			box-sizing: border-box !important;
		}
		.static-form.static-form--shortcode .quform-child-elements:not(:has(> .quform-element-row)):not(:has(> .quform-element-group)) {
			display: grid !important;
			grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) !important;
			gap: 10px !important;
			align-items: start !important;
		}
		.static-form.static-form--shortcode .quform-element,
		.static-form.static-form--shortcode .quform-element-column,
		.static-form.static-form--shortcode .quform-spacer,
		.static-form.static-form--shortcode .quform-inner,
		.static-form.static-form--shortcode .quform-element-row,
		.static-form.static-form--shortcode .quform-element-group,
		.static-form.static-form--shortcode .quform-error-wrap,
		.static-form.static-form--shortcode .quform-description,
		.static-form.static-form--shortcode [class*="quform-column-"],
		.static-form.static-form--shortcode [class*="quform-size-"] {
			float: none !important;
			clear: none !important;
			position: static !important;
			left: auto !important;
			right: auto !important;
			top: auto !important;
			bottom: auto !important;
			height: auto !important;
			margin: 0 !important;
			box-sizing: border-box !important;
		}
		.static-form.static-form--shortcode .quform-error-wrap:empty,
		.static-form.static-form--shortcode .quform-description:empty {
			display: none !important;
			height: 0 !important;
			min-height: 0 !important;
			padding: 0 !important;
		}
		#altijara-qf.static-form--shortcode .quform-child-elements > .quform-element-row,
		#altijara-qf.static-form--shortcode .quform-child-elements > .quform-element-group,
		#altijara-qf.static-form--shortcode .quform-element-row.quform-2-columns,
		#altijara-qf.static-form--shortcode .quform-element-row.quform-3-columns,
		#altijara-qf.static-form--shortcode .quform-element-row[class*="quform-2-columns"],
		#altijara-qf.static-form--shortcode .quform-element-row[class*="quform-3-columns"],
		.static-form.static-form--shortcode .quform-child-elements > .quform-element-row,
		.static-form.static-form--shortcode .quform-child-elements > .quform-element-group,
		.static-form.static-form--shortcode .quform-element-row.quform-2-columns,
		.static-form.static-form--shortcode .quform-element-row.quform-3-columns,
		.static-form.static-form--shortcode .quform-element-row[class*="quform-2-columns"],
		.static-form.static-form--shortcode .quform-element-row[class*="quform-3-columns"] {
			display: flex !important;
			flex-direction: row !important;
			flex-wrap: nowrap !important;
			align-items: stretch !important;
			gap: 10px !important;
			float: none !important;
			clear: none !important;
			width: 100% !important;
			max-width: 100% !important;
			min-width: 0 !important;
			margin: 0 !important;
			padding: 0 !important;
			box-sizing: border-box !important;
		}
		#altijara-qf.static-form--shortcode .quform-element-row > .quform-element-column,
		#altijara-qf.static-form--shortcode .quform-element-group > .quform-element-column,
		#altijara-qf.static-form--shortcode .quform-element-row > [class*="quform-column-"],
		#altijara-qf.static-form--shortcode .quform-element-row > [class*="quform-size-"],
		.static-form.static-form--shortcode .quform-element-row > .quform-element-column,
		.static-form.static-form--shortcode .quform-element-group > .quform-element-column {
			display: block !important;
			flex: 1 1 0 !important;
			float: none !important;
			clear: none !important;
			width: 100% !important;
			max-width: none !important;
			min-width: 0 !important;
			margin: 0 !important;
			padding: 0 !important;
			box-sizing: border-box !important;
		}
		/* 2-up fields: fill column so outer edges match full-width rows below */
		#altijara-qf.static-form--shortcode .quform-element-row > .quform-element-column .quform-element,
		#altijara-qf.static-form--shortcode .quform-element-row > .quform-element-column .quform-spacer,
		#altijara-qf.static-form--shortcode .quform-element-row > .quform-element-column .quform-inner,
		#altijara-qf.static-form--shortcode .quform-element-row > .quform-element-column .quform-input,
		#altijara-qf.static-form--shortcode .quform-element-row > .quform-element-column [class*="quform-size-"],
		#altijara-qf.static-form--shortcode .quform-element-row > .quform-element-column [class*="quform-field-width"],
		.static-form.static-form--shortcode .quform-element-row > .quform-element-column .quform-element,
		.static-form.static-form--shortcode .quform-element-row > .quform-element-column .quform-spacer,
		.static-form.static-form--shortcode .quform-element-row > .quform-element-column .quform-inner,
		.static-form.static-form--shortcode .quform-element-row > .quform-element-column .quform-input,
		.static-form.static-form--shortcode .quform-element-row > .quform-element-column [class*="quform-size-"],
		.static-form.static-form--shortcode .quform-element-row > .quform-element-column [class*="quform-field-width"] {
			width: 100% !important;
			max-width: 100% !important;
			margin-left: 0 !important;
			margin-right: 0 !important;
			margin-inline: 0 !important;
			padding-left: 0 !important;
			padding-right: 0 !important;
			padding-inline: 0 !important;
			box-sizing: border-box !important;
		}
		#altijara-qf.static-form--shortcode .quform-element-row > .quform-element-column .quform-label,
		#altijara-qf.static-form--shortcode .quform-element-row > .quform-element-column .quform-field,
		#altijara-qf.static-form--shortcode .quform-element-row > .quform-element-column input,
		#altijara-qf.static-form--shortcode .quform-element-row > .quform-element-column select,
		#altijara-qf.static-form--shortcode .quform-element-row > .quform-element-column textarea,
		.static-form.static-form--shortcode .quform-element-row > .quform-element-column .quform-field,
		.static-form.static-form--shortcode .quform-element-row > .quform-element-column input,
		.static-form.static-form--shortcode .quform-element-row > .quform-element-column select,
		.static-form.static-form--shortcode .quform-element-row > .quform-element-column textarea {
			width: 100% !important;
			max-width: 100% !important;
			margin-left: 0 !important;
			margin-right: 0 !important;
			margin-inline: 0 !important;
			box-sizing: border-box !important;
		}
		#altijara-qf.static-form--shortcode .quform-label,
		#altijara-qf.static-form--shortcode .quform-label-text {
			display: block !important;
			float: none !important;
			width: 100% !important;
			max-width: 100% !important;
			text-align: start !important;
			margin: 0 0 5px !important;
			padding: 0 !important;
		}
		/* Buttons: shrink-wrap columns (must beat #altijara-qf equal-width rules above) */
		#altijara-qf.static-form--shortcode .quform-element-row:has(.quform-element-submit),
		#altijara-qf.static-form--shortcode .quform-element-row:has(.quform-element-html .static-btn),
		#altijara-qf.static-form--shortcode .quform-element-row:has(.quform-element-html a),
		#altijara-qf.static-form--shortcode [class*="quform-2-columns"]:has(.quform-element-submit),
		#altijara-qf.static-form--shortcode [class*="quform-2-columns"]:has(.quform-element-html a),
		.static-form.static-form--shortcode .quform-element-row:has(.quform-element-submit),
		.static-form.static-form--shortcode .quform-element-row:has(.quform-element-html a),
		.static-form.static-form--shortcode [class*="quform-2-columns"]:has(.quform-element-submit),
		.static-form.static-form--shortcode [class*="quform-2-columns"]:has(.quform-element-html a) {
			display: flex !important;
			flex-direction: row !important;
			flex-wrap: wrap !important;
			align-items: center !important;
			justify-content: flex-start !important;
			gap: 8px !important;
		}
		#altijara-qf.static-form--shortcode .quform-element-row:has(.quform-element-submit) > .quform-element-column,
		#altijara-qf.static-form--shortcode .quform-element-row:has(.quform-element-html .static-btn) > .quform-element-column,
		#altijara-qf.static-form--shortcode .quform-element-row:has(.quform-element-html a) > .quform-element-column,
		#altijara-qf.static-form--shortcode [class*="quform-2-columns"]:has(.quform-element-submit) > .quform-element-column,
		#altijara-qf.static-form--shortcode [class*="quform-2-columns"]:has(.quform-element-html a) > .quform-element-column,
		.static-form.static-form--shortcode .quform-element-row:has(.quform-element-submit) > .quform-element-column,
		.static-form.static-form--shortcode .quform-element-row:has(.quform-element-html a) > .quform-element-column {
			flex: 0 0 auto !important;
			flex-grow: 0 !important;
			flex-shrink: 0 !important;
			flex-basis: auto !important;
			width: auto !important;
			max-width: max-content !important;
			min-width: 0 !important;
			text-align: start !important;
		}
		#altijara-qf.static-form--shortcode .quform-element-submit,
		#altijara-qf.static-form--shortcode .quform-element-html,
		#altijara-qf.static-form--shortcode .quform-buttons,
		#altijara-qf.static-form--shortcode .quform-submit-inner,
		.static-form.static-form--shortcode .quform-element-submit,
		.static-form.static-form--shortcode .quform-element-html,
		.static-form.static-form--shortcode .quform-buttons,
		.static-form.static-form--shortcode .quform-submit-inner {
			display: flex !important;
			flex-wrap: wrap !important;
			align-items: center !important;
			justify-content: flex-start !important;
			gap: 8px !important;
			width: auto !important;
			max-width: 100% !important;
			margin: 0 !important;
			padding: 0 !important;
			float: none !important;
			clear: none !important;
			text-align: start !important;
		}
		.static-form.static-form--shortcode input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]):not([type="hidden"]):not([type="file"]):not([type="image"]),
		.static-form.static-form--shortcode textarea,
		.static-form.static-form--shortcode select,
		.static-form.static-form--shortcode .quform-field {
			display: block !important;
			float: none !important;
			width: 100% !important;
			max-width: 100% !important;
			box-sizing: border-box !important;
		}
		.static-form.static-form--shortcode .quform-element-honeypot,
		.static-form.static-form--shortcode .quform-hidden,
		.static-form.static-form--shortcode .quform-edit-form {
			display: none !important;
		}
		@media (max-width: 768px) {
			.static-form.static-form--shortcode .quform-child-elements:not(:has(> .quform-element-row)):not(:has(> .quform-element-group)) {
				grid-template-columns: 1fr !important;
			}
			.static-form.static-form--shortcode .quform-child-elements > .quform-element-row:not(:has(.quform-element-submit)):not(:has(.quform-element-html a)),
			.static-form.static-form--shortcode .quform-child-elements > .quform-element-group,
			.static-form.static-form--shortcode .quform-element-row.quform-2-columns:not(:has(.quform-element-submit)):not(:has(.quform-element-html a)),
			.static-form.static-form--shortcode .quform-element-row[class*="quform-2-columns"]:not(:has(.quform-element-submit)):not(:has(.quform-element-html a)) {
				flex-direction: column !important;
			}
			#altijara-qf.static-form--shortcode .quform-element-row:not(:has(.quform-element-submit)):not(:has(.quform-element-html a)) > .quform-element-column,
			.static-form.static-form--shortcode .quform-element-row:not(:has(.quform-element-submit)):not(:has(.quform-element-html a)) > .quform-element-column {
				flex: 1 1 auto !important;
				width: 100% !important;
				max-width: 100% !important;
			}
			#altijara-qf.static-form--shortcode .quform-element-row:has(.quform-element-submit),
			#altijara-qf.static-form--shortcode .quform-element-row:has(.quform-element-html a),
			.static-form.static-form--shortcode .quform-element-row:has(.quform-element-submit),
			.static-form.static-form--shortcode .quform-element-row:has(.quform-element-html a) {
				flex-direction: row !important;
				flex-wrap: wrap !important;
				justify-content: flex-start !important;
				gap: 8px !important;
			}
			#altijara-qf.static-form--shortcode .quform-element-row:has(.quform-element-submit) > .quform-element-column,
			#altijara-qf.static-form--shortcode .quform-element-row:has(.quform-element-html a) > .quform-element-column,
			.static-form.static-form--shortcode .quform-element-row:has(.quform-element-submit) > .quform-element-column,
			.static-form.static-form--shortcode .quform-element-row:has(.quform-element-html a) > .quform-element-column {
				flex: 0 0 auto !important;
				width: auto !important;
				max-width: max-content !important;
			}
		}
	</style>
	<?php
}
add_action( 'wp_footer', 'altijara_quform_compat_footer', 9999 );
