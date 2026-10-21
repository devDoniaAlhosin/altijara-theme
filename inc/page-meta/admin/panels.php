<?php
/**
 * Page meta box panel (schema switch UI).
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta box UI.
 *
 * @param WP_Post $post Post.
 */
function altijara_render_page_meta_box( $post ) {
	$schema = altijara_get_page_schema_key( $post->ID );
	wp_nonce_field( 'altijara_save_page_meta', 'altijara_page_meta_nonce' );

	if ( ! $schema ) {
		echo '<div class="altijara-pm-empty">';
		echo '<p><strong>' . esc_html__( 'No Altijara template assigned', 'altijara' ) . '</strong></p>';
		echo '<p class="description">' . esc_html__( 'Open Page Attributes → Template and choose Home 2, About, Contact, Advertise, Subscribe, or Login. Then you can edit content here.', 'altijara' ) . '</p>';
		echo '</div>';
		return;
	}

	$labels = altijara_page_schema_labels();
	$label  = isset( $labels[ $schema ] ) ? $labels[ $schema ] : $schema;
	$meta   = altijara_get_page_meta( $post->ID );
	$view   = get_permalink( $post );

	echo '<input type="hidden" name="altijara_page_schema" value="' . esc_attr( $schema ) . '">';
	echo '<div class="altijara-page-meta" data-schema="' . esc_attr( $schema ) . '">';

	echo '<div class="altijara-pm-toolbar">';
	echo '<div class="altijara-pm-toolbar__main">';
	echo '<span class="altijara-pm-badge">' . esc_html( $label ) . '</span>';
	echo '<p class="altijara-pm-toolbar__help">' . esc_html__( 'Layout is fixed by the template. Edit copy and lists below. The WordPress title above is for menus/browser — separate from the lead text.', 'altijara' ) . '</p>';
	echo '</div>';
	if ( $view ) {
		echo '<a class="button altijara-pm-view" href="' . esc_url( $view ) . '" target="_blank" rel="noopener">' . esc_html__( 'View page', 'altijara' ) . '</a>';
	}
	echo '</div>';

	switch ( $schema ) {
		case 'home':
			altijara_render_home2_page_meta_panel( $meta );
			break;

		case 'about':
			altijara_page_meta_section_open( 'intro', __( 'Intro & lead', 'altijara' ), true );
			altijara_page_meta_field_text( 'lead', __( 'Lead paragraph', 'altijara' ), $meta['lead'] ?? '', true, 'text', __( 'Shown under the page heading on the front end.', 'altijara' ) );
			altijara_page_meta_section_close();

			altijara_page_meta_section_open( 'highlight', __( 'Highlight card', 'altijara' ) );
			altijara_page_meta_grid_open();
			altijara_page_meta_field_text( 'highlight_kicker', __( 'Kicker', 'altijara' ), $meta['highlight_kicker'] ?? '' );
			altijara_page_meta_field_text( 'highlight_title', __( 'Title', 'altijara' ), $meta['highlight_title'] ?? '' );
			altijara_page_meta_grid_close();
			altijara_page_meta_field_text( 'highlight_intro', __( 'Intro', 'altijara' ), $meta['highlight_intro'] ?? '', true );
			altijara_page_meta_repeater(
				'stats',
				__( 'Highlight stats', 'altijara' ),
				$meta['stats'] ?? array(),
				array(
					'value' => __( 'Value', 'altijara' ),
					'label' => __( 'Label', 'altijara' ),
				)
			);
			altijara_page_meta_section_close();

			altijara_page_meta_section_open( 'offers', __( 'What we offer', 'altijara' ) );
			altijara_page_meta_field_text( 'offers_title', __( 'Offers section title', 'altijara' ), $meta['offers_title'] ?? '' );
			altijara_page_meta_repeater(
				'offers',
				__( 'Offer items', 'altijara' ),
				$meta['offers'] ?? array(),
				array(
					'title' => __( 'Title', 'altijara' ),
					'text'  => __( 'Description', 'altijara' ),
				)
			);
			altijara_page_meta_section_close();

			altijara_page_meta_section_open( 'mission', __( 'Mission', 'altijara' ) );
			altijara_page_meta_field_text( 'mission', __( 'Mission paragraph', 'altijara' ), $meta['mission'] ?? '', true );
			altijara_page_meta_section_close();

			altijara_page_meta_section_open( 'distribution', __( 'Distribution', 'altijara' ) );
			altijara_page_meta_field_text( 'dist_intro', __( 'Intro', 'altijara' ), $meta['dist_intro'] ?? '', true );
			altijara_page_meta_repeater(
				'dist_items',
				__( 'Distribution list', 'altijara' ),
				$meta['dist_items'] ?? array(),
				array(
					'text' => __( 'Item', 'altijara' ),
				)
			);
			altijara_page_meta_section_close();

			altijara_page_meta_section_open( 'hq', __( 'HQ card', 'altijara' ) );
			altijara_page_meta_grid_open();
			altijara_page_meta_field_text( 'hq_kicker', __( 'Kicker', 'altijara' ), $meta['hq_kicker'] ?? '' );
			altijara_page_meta_field_text( 'hq_title', __( 'Title', 'altijara' ), $meta['hq_title'] ?? '' );
			altijara_page_meta_grid_close();
			altijara_page_meta_field_text( 'hq_address', __( 'Address', 'altijara' ), $meta['hq_address'] ?? '', true );
			altijara_page_meta_grid_open();
			altijara_page_meta_field_text( 'hq_phone_label', __( 'Phone label', 'altijara' ), $meta['hq_phone_label'] ?? '' );
			altijara_page_meta_field_text( 'hq_phone', __( 'Phone number', 'altijara' ), $meta['hq_phone'] ?? '', false, 'tel' );
			altijara_page_meta_field_text( 'hq_po_label', __( 'PO box label', 'altijara' ), $meta['hq_po_label'] ?? '' );
			altijara_page_meta_field_text( 'hq_po_value', __( 'PO box value', 'altijara' ), $meta['hq_po_value'] ?? '' );
			altijara_page_meta_field_text( 'hq_contact_label', __( 'Contact button label', 'altijara' ), $meta['hq_contact_label'] ?? '' );
			altijara_page_meta_field_text( 'hq_advertise_label', __( 'Advertise button label', 'altijara' ), $meta['hq_advertise_label'] ?? '' );
			altijara_page_meta_grid_close();
			altijara_page_meta_section_close();
			break;

		case 'contact':
			altijara_page_meta_section_open( 'intro', __( 'Intro & lead', 'altijara' ), true );
			altijara_page_meta_field_text( 'lead', __( 'Lead paragraph', 'altijara' ), $meta['lead'] ?? '', true, 'text', __( 'Shown under the page heading.', 'altijara' ) );
			altijara_page_meta_section_close();

			altijara_page_meta_section_open( 'chips', __( 'Contact chips', 'altijara' ) );
			altijara_page_meta_grid_open();
			altijara_page_meta_field_text( 'chip_email', __( 'Email chip', 'altijara' ), $meta['chip_email'] ?? '', false, 'email' );
			altijara_page_meta_field_text( 'chip_name', __( 'Name chip', 'altijara' ), $meta['chip_name'] ?? '' );
			altijara_page_meta_field_text( 'chip_org', __( 'Organization chip', 'altijara' ), $meta['chip_org'] ?? '' );
			altijara_page_meta_grid_close();
			altijara_page_meta_section_close();

			altijara_page_meta_section_open( 'form', __( 'Form', 'altijara' ) );
			altijara_page_meta_grid_open();
			altijara_page_meta_field_text( 'form_title', __( 'Form title', 'altijara' ), $meta['form_title'] ?? '' );
			altijara_page_meta_field_text( 'success_message', __( 'Success message', 'altijara' ), $meta['success_message'] ?? '' );
			altijara_page_meta_grid_close();
			altijara_page_meta_section_close();

			altijara_page_meta_section_open( 'routing', __( 'Department routing emails', 'altijara' ) );
			altijara_page_meta_grid_open();
			altijara_page_meta_field_text( 'route_editorial', __( 'Editorial', 'altijara' ), $meta['route_editorial'] ?? '', false, 'email' );
			altijara_page_meta_field_text( 'route_ads', __( 'Advertising', 'altijara' ), $meta['route_ads'] ?? '', false, 'email' );
			altijara_page_meta_field_text( 'route_subscribe', __( 'Subscriptions', 'altijara' ), $meta['route_subscribe'] ?? '', false, 'email' );
			altijara_page_meta_field_text( 'route_tech', __( 'Technical', 'altijara' ), $meta['route_tech'] ?? '', false, 'email' );
			altijara_page_meta_grid_close();
			altijara_page_meta_section_close();

			altijara_page_meta_section_open( 'agent', __( 'Ad agent sidebar', 'altijara' ) );
			altijara_page_meta_grid_open();
			altijara_page_meta_field_text( 'agent_kicker', __( 'Kicker', 'altijara' ), $meta['agent_kicker'] ?? '' );
			altijara_page_meta_field_text( 'agent_title', __( 'Title', 'altijara' ), $meta['agent_title'] ?? '' );
			altijara_page_meta_grid_close();
			altijara_page_meta_field_text( 'agent_intro', __( 'Intro', 'altijara' ), $meta['agent_intro'] ?? '', true );
			altijara_page_meta_grid_open();
			altijara_page_meta_field_text( 'agent_name', __( 'Person name', 'altijara' ), $meta['agent_name'] ?? '' );
			altijara_page_meta_field_text( 'agent_role', __( 'Role', 'altijara' ), $meta['agent_role'] ?? '' );
			altijara_page_meta_field_text( 'agent_phone', __( 'Phone', 'altijara' ), $meta['agent_phone'] ?? '', false, 'tel' );
			altijara_page_meta_field_text( 'agent_mobile', __( 'Mobile', 'altijara' ), $meta['agent_mobile'] ?? '', false, 'tel' );
			altijara_page_meta_field_text( 'agent_email', __( 'Email', 'altijara' ), $meta['agent_email'] ?? '', false, 'email' );
			altijara_page_meta_grid_close();
			altijara_page_meta_section_close();
			break;

		case 'advertise':
			altijara_page_meta_section_open( 'intro', __( 'Intro & lead', 'altijara' ), true );
			altijara_page_meta_field_text( 'lead', __( 'Lead paragraph', 'altijara' ), $meta['lead'] ?? '', true );
			altijara_page_meta_section_close();

			altijara_page_meta_section_open( 'agent', __( 'Exclusive agent card', 'altijara' ) );
			altijara_page_meta_grid_open();
			altijara_page_meta_field_text( 'agent_kicker', __( 'Kicker', 'altijara' ), $meta['agent_kicker'] ?? '' );
			altijara_page_meta_field_text( 'agent_title', __( 'Title', 'altijara' ), $meta['agent_title'] ?? '' );
			altijara_page_meta_grid_close();
			altijara_page_meta_field_text( 'agent_intro', __( 'Intro', 'altijara' ), $meta['agent_intro'] ?? '', true );
			altijara_page_meta_grid_open();
			altijara_page_meta_field_text( 'agent_name', __( 'Person name', 'altijara' ), $meta['agent_name'] ?? '' );
			altijara_page_meta_field_text( 'agent_role', __( 'Role', 'altijara' ), $meta['agent_role'] ?? '' );
			altijara_page_meta_field_text( 'agent_phone', __( 'Phone', 'altijara' ), $meta['agent_phone'] ?? '', false, 'tel' );
			altijara_page_meta_field_text( 'agent_mobile', __( 'Mobile', 'altijara' ), $meta['agent_mobile'] ?? '', false, 'tel' );
			altijara_page_meta_field_text( 'agent_email', __( 'Email', 'altijara' ), $meta['agent_email'] ?? '', false, 'email' );
			altijara_page_meta_grid_close();
			altijara_page_meta_section_close();

			altijara_page_meta_section_open( 'inventory', __( 'Ad inventory', 'altijara' ) );
			altijara_page_meta_repeater(
				'ad_specs',
				__( 'Ad inventory cards', 'altijara' ),
				$meta['ad_specs'] ?? array(),
				array(
					'title'      => __( 'Title', 'altijara' ),
					'subtitle'   => __( 'Subtitle', 'altijara' ),
					'size'       => __( 'Size line', 'altijara' ),
					'variant'    => __( 'Variant', 'altijara' ),
					'points'     => __( 'Bullet points (one per line)', 'altijara' ),
					'price'      => __( 'Price', 'altijara' ),
					'price_note' => __( 'Price note', 'altijara' ),
				),
				array( 'points' ),
				array(
					'variant' => array(
						'leader' => __( 'Leader', 'altijara' ),
						'medium' => __( 'Medium', 'altijara' ),
					),
				)
			);
			altijara_page_meta_section_close();

			altijara_page_meta_section_open( 'form', __( 'Lead form & sidebar', 'altijara' ) );
			altijara_page_meta_field_text( 'form_title', __( 'Form title', 'altijara' ), $meta['form_title'] ?? '' );
			altijara_page_meta_field_text( 'form_lead', __( 'Form lead', 'altijara' ), $meta['form_lead'] ?? '', true );
			altijara_page_meta_field_text( 'success_message', __( 'Success message', 'altijara' ), $meta['success_message'] ?? '' );
			altijara_page_meta_field_text( 'why_title', __( 'Why advertise — title', 'altijara' ), $meta['why_title'] ?? '' );
			altijara_page_meta_field_text( 'why_text', __( 'Why advertise — text', 'altijara' ), $meta['why_text'] ?? '', true );
			altijara_page_meta_media_field(
				'media_kit_url',
				__( 'Media kit PDF', 'altijara' ),
				$meta['media_kit_url'] ?? '',
				__( 'Upload or paste a PDF / file URL for the media kit download button.', 'altijara' )
			);
			altijara_page_meta_field_text( 'guidelines', __( 'Sponsored content guidelines', 'altijara' ), $meta['guidelines'] ?? '', true );
			altijara_page_meta_section_close();
			break;

		case 'subscribe':
			altijara_page_meta_section_open( 'intro', __( 'Intro & messages', 'altijara' ), true );
			altijara_page_meta_field_text( 'lead', __( 'Lead paragraph', 'altijara' ), $meta['lead'] ?? '', true );
			altijara_page_meta_field_text( 'success_message', __( 'Success message', 'altijara' ), $meta['success_message'] ?? '' );
			altijara_page_meta_field_text( 'consent_text', __( 'Consent checkbox text', 'altijara' ), $meta['consent_text'] ?? '', true );
			altijara_page_meta_section_close();

			altijara_page_meta_section_open( 'plans', __( 'Subscription tiers', 'altijara' ) );
			altijara_page_meta_repeater(
				'plans',
				__( 'Plans', 'altijara' ),
				$meta['plans'] ?? array(),
				array(
					'value' => __( 'Plan type', 'altijara' ),
					'title' => __( 'Title', 'altijara' ),
					'text'  => __( 'Description', 'altijara' ),
				),
				array( 'text' ),
				array(
					'value' => array(
						'newsletter' => __( 'Newsletter', 'altijara' ),
						'digital'    => __( 'Digital', 'altijara' ),
						'full'       => __( 'Full', 'altijara' ),
					),
				)
			);
			altijara_page_meta_section_close();
			break;

		case 'login':
			altijara_page_meta_section_open( 'copy', __( 'Login copy', 'altijara' ), true );
			altijara_page_meta_field_text( 'lead', __( 'Lead paragraph', 'altijara' ), $meta['lead'] ?? '', true );
			altijara_page_meta_field_text( 'success_message', __( 'Success message', 'altijara' ), $meta['success_message'] ?? '' );
			altijara_page_meta_grid_open();
			altijara_page_meta_field_text( 'forgot_label', __( 'Forgot password label', 'altijara' ), $meta['forgot_label'] ?? '' );
			altijara_page_meta_field_text( 'subscribe_link_label', __( 'Subscribe link label', 'altijara' ), $meta['subscribe_link_label'] ?? '' );
			altijara_page_meta_grid_close();
			altijara_page_meta_field_text( 'subscribe_note', __( 'No account note', 'altijara' ), $meta['subscribe_note'] ?? '' );
			altijara_page_meta_section_close();
			break;
	}

	echo '</div>';
}

/**
 * Order-by choices for Home 2 category source.
 *
 * @return array<string, string>
 */
function altijara_home_meta_orderby_choices() {
	return array(
		'date'          => __( 'Latest published', 'altijara' ),
		'modified'      => __( 'Last modified', 'altijara' ),
		'title'         => __( 'Title', 'altijara' ),
		'comment_count' => __( 'Most commented', 'altijara' ),
		'rand'          => __( 'Random', 'altijara' ),
	);
}

/**
 * Render AJAX post picker (shared chip UI).
 *
 * @param string               $name_attr Input name for hidden IDs.
 * @param string               $ids_csv   Comma-separated IDs.
 * @param array<string, mixed> $args      post_type, label, placeholder.
 */
function altijara_render_admin_post_picker( $name_attr, $ids_csv, $args = array() ) {
	$post_type   = isset( $args['post_type'] ) ? (string) $args['post_type'] : 'post';
	$label       = isset( $args['label'] ) ? (string) $args['label'] : __( 'Select posts', 'altijara' );
	$placeholder = isset( $args['placeholder'] ) ? (string) $args['placeholder'] : __( 'Search by title or ID…', 'altijara' );
	$ids         = array_values(
		array_filter(
			array_map(
				'absint',
				preg_split( '/[\s,]+/', (string) $ids_csv ) ?: array()
			)
		)
	);
	$titles = function_exists( 'altijara_get_post_titles_map' ) ? altijara_get_post_titles_map( $ids ) : array();
	?>
	<div class="altijara-post-picker" data-post-type="<?php echo esc_attr( $post_type ); ?>">
		<label><strong><?php echo esc_html( $label ); ?></strong></label>
		<input type="hidden" class="altijara-picker-ids" name="<?php echo esc_attr( $name_attr ); ?>" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>">
		<input type="search" class="altijara-picker-search regular-text" placeholder="<?php echo esc_attr( $placeholder ); ?>" autocomplete="off">
		<ul class="altijara-picker-results" hidden></ul>
		<ul class="altijara-picker-selected">
			<?php foreach ( $ids as $pid ) : ?>
				<li data-id="<?php echo esc_attr( (string) $pid ); ?>">
					<span><?php echo esc_html( isset( $titles[ $pid ] ) ? $titles[ $pid ] . ' (#' . $pid . ')' : '#' . $pid ); ?></span>
					<button type="button" class="button-link-delete altijara-picker-remove">&times;</button>
				</li>
			<?php endforeach; ?>
		</ul>
		<p class="description"><?php esc_html_e( 'Search by name or type a post ID. Selection order = display order.', 'altijara' ); ?></p>
	</div>
	<?php
}

/**
 * Home 2 metabox — full JNews-style layout builder.
 *
 * @param array<string, mixed> $meta Page meta.
 */
function altijara_render_home2_page_meta_panel( array $meta ) {
	$page_id = altijara_admin_editing_post_id();
	$pack    = function_exists( 'altijara_get_home2_layout' )
		? altijara_get_home2_layout( $page_id )
		: ( function_exists( 'altijara_layout_preset_home' ) ? altijara_layout_preset_home() : array( 'sections' => array(), 'order' => array() ) );

	echo '<input type="hidden" name="altijara_page[layout_submitted]" value="1">';
	echo '<p class="description" style="margin-top:0;">' . esc_html__( 'Add, reorder, and configure homepage widgets. Page template must be Home 2 and set as Front Page.', 'altijara' ) . '</p>';

	if ( function_exists( 'altijara_render_layout_builder' ) ) {
		altijara_render_layout_builder(
			array(
				'builder_id'   => 'home2_page',
				'field_prefix' => 'altijara_page[layout_sections]',
				'order_field'  => 'altijara_page[layout_order]',
				'sections'     => $pack['sections'],
				'order'        => $pack['order'],
				'surface'      => 'home',
				'lock_hero'    => true,
			)
		);
	}
}