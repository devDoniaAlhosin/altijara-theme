<?php
/**
 * Email Styles — Altijara brand tokens for WooCommerce emails.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package Altijara
 * @version 9.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$c = function_exists( 'altijara_mail_colors' ) ? altijara_mail_colors() : array(
	'brand'  => '#1897B8',
	'hover'  => '#12748e',
	'ink'    => '#262A33',
	'muted'  => '#6e7787',
	'salmon' => '#FFF1E5',
	'card'   => '#FFF7EF',
	'white'  => '#ffffff',
);

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
?>
body {
	background-color: <?php echo $c['salmon']; ?>;
	padding: 0;
	text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
	font-family: Tahoma, Arial, Helvetica, sans-serif;
}

#outer_wrapper {
	background-color: <?php echo $c['salmon']; ?>;
}

#inner_wrapper {
	background-color: <?php echo $c['white']; ?>;
	border-radius: 8px;
	max-width: 600px;
}

#template_header {
	background-color: <?php echo $c['card']; ?>;
	border-bottom: 3px solid <?php echo $c['brand']; ?>;
	color: <?php echo $c['ink']; ?>;
	font-weight: bold;
	line-height: 1.4;
	vertical-align: middle;
}

#body_content {
	background-color: <?php echo $c['white']; ?>;
	color: <?php echo $c['ink']; ?>;
	font-size: 15px;
	line-height: 1.7;
}

#body_content table td {
	padding: 8px 0;
	color: <?php echo $c['ink']; ?>;
}

#body_content table td td {
	padding: 8px;
}

#body_content table td th {
	padding: 8px;
}

#body_content td ul.wc-item-meta {
	font-size: 13px;
	margin: 8px 0 0;
	padding: 0;
	list-style: none;
}

#body_content td ul.wc-item-meta li {
	margin: 0;
	padding: 0;
}

#body_content td ul.wc-item-meta li p {
	margin: 0;
	line-height: 1.5;
}

#body_content p {
	margin: 0 0 14px;
}

#body_content_inner {
	color: <?php echo $c['ink']; ?>;
	font-size: 15px;
	line-height: 1.7;
	text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

.td {
	color: <?php echo $c['ink']; ?>;
	border: 1px solid #e8e4de;
	vertical-align: middle;
}

.address {
	padding: 12px;
	color: <?php echo $c['ink']; ?>;
	border: 1px solid #e8e4de;
	border-radius: 4px;
}

.text {
	color: <?php echo $c['ink']; ?>;
	font-size: 15px;
	line-height: 1.7;
}

.link {
	color: <?php echo $c['brand']; ?>;
}

#header_wrapper {
	padding: 0;
	display: block;
}

h1 {
	color: <?php echo $c['ink']; ?>;
	font-size: 20px;
	font-weight: 700;
	line-height: 1.4;
	margin: 0 0 16px;
	text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

h2 {
	color: <?php echo $c['ink']; ?>;
	display: block;
	font-size: 17px;
	font-weight: 700;
	line-height: 1.4;
	margin: 0 0 12px;
	text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

h3 {
	color: <?php echo $c['ink']; ?>;
	display: block;
	font-size: 15px;
	font-weight: 700;
	line-height: 1.4;
	margin: 16px 0 8px;
	text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

a {
	color: <?php echo $c['brand']; ?>;
	font-weight: normal;
	text-decoration: underline;
}

img {
	border: none;
	display: inline-block;
	font-size: 14px;
	font-weight: bold;
	height: auto;
	outline: none;
	text-decoration: none;
	text-transform: capitalize;
	vertical-align: middle;
	max-width: 100%;
}

#template_footer {
	background: <?php echo $c['ink']; ?>;
	color: #c5cad3;
}

#credit {
	border: 0;
	color: #c5cad3;
	font-size: 12px;
	line-height: 1.5;
	text-align: center;
	padding: 18px 24px;
}

#credit a {
	color: <?php echo $c['brand']; ?>;
	text-decoration: none;
}

.order_item td,
.order_item th {
	border-color: #e8e4de;
}
<?php
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
