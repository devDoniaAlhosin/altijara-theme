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
 * Edit links for fixed utility pages (Tools admin card only — not shown in the page editor).
 *
 * @param int $current_id Current page ID.
 * @return array<int, array{slug:string,label:string,edit:string,view:string,current:bool}>
 */
function altijara_page_meta_utility_nav_items( $current_id = 0 ) {
	$current_id = absint( $current_id );
	$labels     = altijara_page_schema_labels();
	$items      = array();
	foreach ( array( 'about', 'contact', 'advertise', 'subscribe', 'login' ) as $slug ) {
		$page = function_exists( 'altijara_get_utility_page' ) ? altijara_get_utility_page( $slug ) : null;
		if ( ! $page instanceof WP_Post ) {
			continue;
		}
		$items[] = array(
			'slug'    => $slug,
			'label'   => isset( $labels[ $slug ] ) ? $labels[ $slug ] : $slug,
			'edit'    => get_edit_post_link( $page->ID, 'raw' ),
			'view'    => get_permalink( $page ),
			'current' => ( (int) $page->ID === $current_id ),
		);
	}
	return $items;
}

/**
 * Agent field keys shared by Contact + Advertise.
 *
 * @return string[]
 */
function altijara_page_meta_agent_field_keys() {
	return array(
		'agent_kicker',
		'agent_title',
		'agent_intro',
		'agent_name',
		'agent_role',
		'agent_phone',
		'agent_mobile',
		'agent_email',
	);
}

/**
 * Contact-page agent values for syncing into Advertise.
 *
 * @return array<string, string>
 */
function altijara_page_meta_contact_agent_snapshot() {
	$page = function_exists( 'altijara_get_utility_page' ) ? altijara_get_utility_page( 'contact' ) : null;
	if ( ! $page instanceof WP_Post || ! function_exists( 'altijara_get_page_meta' ) ) {
		return array();
	}
	$meta = altijara_get_page_meta( $page->ID );
	$out  = array();
	foreach ( altijara_page_meta_agent_field_keys() as $key ) {
		$out[ $key ] = isset( $meta[ $key ] ) ? (string) $meta[ $key ] : '';
	}
	return $out;
}

/**
 * Jump links for accordion sections.
 *
 * @param array<int, array{id:string,label:string}> $sections Sections.
 */
function altijara_page_meta_render_toc( array $sections ) {
	if ( ! $sections ) {
		return;
	}
	echo '<nav class="altijara-pm-toc" aria-label="' . esc_attr__( 'Page sections', 'altijara' ) . '">';
	echo '<span class="altijara-pm-toc__label">' . esc_html__( 'Jump to', 'altijara' ) . '</span>';
	echo '<ul class="altijara-pm-toc__list">';
	foreach ( $sections as $row ) {
		$id    = isset( $row['id'] ) ? sanitize_key( $row['id'] ) : '';
		$label = isset( $row['label'] ) ? (string) $row['label'] : '';
		if ( ! $id || ! $label ) {
			continue;
		}
		echo '<li><a href="#altijara-pm-sec-' . esc_attr( $id ) . '" data-section="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</a></li>';
	}
	echo '</ul></nav>';
}

/**
 * Shared agent fields for Contact / Advertise.
 *
 * @param array<string, mixed> $meta  Page meta.
 * @param string               $title Section title.
 * @param bool                 $sync  Show “Copy from Contact”.
 */
function altijara_page_meta_render_agent_section( array $meta, $title, $sync = false ) {
	altijara_page_meta_section_open( 'agent', $title );
	if ( $sync ) {
		echo '<div class="altijara-pm-agent-sync">';
		echo '<p class="description">' . esc_html__( 'Keep the same sales contact on Contact and Advertise, or edit separately below.', 'altijara' ) . '</p>';
		echo '<button type="button" class="button altijara-pm-sync-agent">' . esc_html__( 'Copy agent from Contact page', 'altijara' ) . '</button>';
		echo '<span class="altijara-pm-sync-status" aria-live="polite"></span>';
		echo '</div>';
	}
	altijara_page_meta_grid_open();
	altijara_page_meta_field_text( 'agent_kicker', __( 'Kicker', 'altijara' ), $meta['agent_kicker'] ?? '', false, 'text', __( 'Small label above the card title.', 'altijara' ) );
	altijara_page_meta_field_text( 'agent_title', __( 'Title', 'altijara' ), $meta['agent_title'] ?? '' );
	altijara_page_meta_grid_close();
	altijara_page_meta_field_text( 'agent_intro', __( 'Intro', 'altijara' ), $meta['agent_intro'] ?? '', true );
	altijara_page_meta_grid_open();
	altijara_page_meta_field_text( 'agent_name', __( 'Person name', 'altijara' ), $meta['agent_name'] ?? '' );
	altijara_page_meta_field_text( 'agent_role', __( 'Role', 'altijara' ), $meta['agent_role'] ?? '' );
	altijara_page_meta_field_text( 'agent_phone', __( 'Phone', 'altijara' ), $meta['agent_phone'] ?? '', false, 'tel' );
	altijara_page_meta_field_text( 'agent_mobile', __( 'Mobile', 'altijara' ), $meta['agent_mobile'] ?? '', false, 'tel' );
	altijara_page_meta_field_text( 'agent_email', __( 'Email', 'altijara' ), $meta['agent_email'] ?? '', false, 'email', __( 'Shown on the card; used when contact forms are wired to email.', 'altijara' ) );
	altijara_page_meta_grid_close();
	altijara_page_meta_section_close();
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
			altijara_page_meta_render_toc(
				array(
					array( 'id' => 'intro', 'label' => __( 'Intro', 'altijara' ) ),
					array( 'id' => 'highlight', 'label' => __( 'Highlight', 'altijara' ) ),
					array( 'id' => 'offers', 'label' => __( 'Offers', 'altijara' ) ),
					array( 'id' => 'mission', 'label' => __( 'Mission', 'altijara' ) ),
					array( 'id' => 'distribution', 'label' => __( 'Distribution', 'altijara' ) ),
					array( 'id' => 'hq', 'label' => __( 'HQ', 'altijara' ) ),
				)
			);
			altijara_page_meta_section_open( 'intro', __( 'Intro & lead', 'altijara' ), true );
			altijara_page_meta_field_text( 'lead', __( 'Lead paragraph', 'altijara' ), $meta['lead'] ?? '', true, 'text', __( 'Shown under the page heading. Keep it to 2–3 short sentences.', 'altijara' ) );
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
			altijara_page_meta_field_text( 'hq_contact_label', __( 'Contact button label', 'altijara' ), $meta['hq_contact_label'] ?? '', false, 'text', __( 'Links to the Contact utility page.', 'altijara' ) );
			altijara_page_meta_field_text( 'hq_advertise_label', __( 'Advertise button label', 'altijara' ), $meta['hq_advertise_label'] ?? '', false, 'text', __( 'Links to the Advertise utility page.', 'altijara' ) );
			altijara_page_meta_grid_close();
			altijara_page_meta_section_close();
			break;

		case 'contact':
			altijara_page_meta_render_toc(
				array(
					array( 'id' => 'intro', 'label' => __( 'Intro', 'altijara' ) ),
					array( 'id' => 'chips', 'label' => __( 'Chips', 'altijara' ) ),
					array( 'id' => 'form', 'label' => __( 'Form', 'altijara' ) ),
					array( 'id' => 'agent', 'label' => __( 'Agent', 'altijara' ) ),
				)
			);
			altijara_page_meta_section_open( 'intro', __( 'Intro & lead', 'altijara' ), true );
			altijara_page_meta_field_text( 'lead', __( 'Lead paragraph', 'altijara' ), $meta['lead'] ?? '', true, 'text', __( 'Shown under the page heading.', 'altijara' ) );
			altijara_page_meta_section_close();

			altijara_page_meta_section_open( 'chips', __( 'Contact chips', 'altijara' ) );
			echo '<p class="description" style="margin-top:0;">' . esc_html__( 'Quick contact hints shown near the form (email, name, organization).', 'altijara' ) . '</p>';
			altijara_page_meta_grid_open();
			altijara_page_meta_field_text( 'chip_email', __( 'Email chip', 'altijara' ), $meta['chip_email'] ?? '', false, 'email' );
			altijara_page_meta_field_text( 'chip_name', __( 'Name chip', 'altijara' ), $meta['chip_name'] ?? '' );
			altijara_page_meta_field_text( 'chip_org', __( 'Organization chip', 'altijara' ), $meta['chip_org'] ?? '' );
			altijara_page_meta_grid_close();
			altijara_page_meta_section_close();

			altijara_page_meta_section_open( 'form', __( 'Form', 'altijara' ) );
			altijara_page_meta_grid_open();
			altijara_page_meta_field_text( 'form_title', __( 'Form title', 'altijara' ), $meta['form_title'] ?? '' );
			altijara_page_meta_field_text( 'form_shortcode', __( 'Form shortcode', 'altijara' ), $meta['form_shortcode'] ?? '', false, 'text', __( 'Paste the Quform shortcode, for example [quform id="1"]. Leave empty to keep the built-in form. The page style is applied to that form.', 'altijara' ) );
			altijara_page_meta_field_text( 'success_message', __( 'Success message', 'altijara' ), $meta['success_message'] ?? '', false, 'text', __( 'Shown after the visitor submits the form.', 'altijara' ) );
			altijara_page_meta_grid_close();
			altijara_page_meta_section_close();

			altijara_page_meta_render_agent_section( $meta, __( 'Ad agent sidebar', 'altijara' ), false );
			break;

		case 'advertise':
			altijara_page_meta_render_toc(
				array(
					array( 'id' => 'intro', 'label' => __( 'Intro', 'altijara' ) ),
					array( 'id' => 'agent', 'label' => __( 'Agent', 'altijara' ) ),
					array( 'id' => 'inventory', 'label' => __( 'Inventory', 'altijara' ) ),
					array( 'id' => 'form', 'label' => __( 'Form', 'altijara' ) ),
				)
			);
			altijara_page_meta_section_open( 'intro', __( 'Intro & lead', 'altijara' ), true );
			altijara_page_meta_field_text( 'lead', __( 'Lead paragraph', 'altijara' ), $meta['lead'] ?? '', true, 'text', __( 'Top intro under the Advertise heading.', 'altijara' ) );
			altijara_page_meta_section_close();

			altijara_page_meta_render_agent_section( $meta, __( 'Exclusive agent card', 'altijara' ), true );

			altijara_page_meta_section_open( 'inventory', __( 'Ad inventory', 'altijara' ) );
			echo '<p class="description" style="margin-top:0;">' . esc_html__( 'Each card is one ad format. Variant controls the mock preview size (Leader vs Medium).', 'altijara' ) . '</p>';
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
			altijara_page_meta_field_text( 'form_shortcode', __( 'Form shortcode', 'altijara' ), $meta['form_shortcode'] ?? '', false, 'text', __( 'Paste the Quform shortcode, for example [quform id="1"]. Leave empty to keep the built-in form. The page style is applied to that form.', 'altijara' ) );
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
 * Home 2 metabox — full layout builder.
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