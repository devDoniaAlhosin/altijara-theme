<?php
/**
 * Category / taxonomy label helpers.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether a category term is WordPress’s default “Uncategorized”.
 *
 * @param WP_Term|object $term Category term.
 * @return bool
 */
function altijara_is_uncategorized_term( $term ) {
	if ( ! $term || is_wp_error( $term ) ) {
		return false;
	}
	$default_id = (int) get_option( 'default_category' );
	if ( $default_id && (int) $term->term_id === $default_id ) {
		return true;
	}
	return 'uncategorized' === (string) $term->slug;
}

/**
 * Primary category label for a post.
 *
 * Skips Uncategorized when the post also has another category.
 *
 * @param int $post_id Post ID.
 */
function altijara_get_primary_category_name( $post_id ) {
	$terms = get_the_category( $post_id );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return '';
	}
	foreach ( $terms as $term ) {
		if ( ! altijara_is_uncategorized_term( $term ) ) {
			return $term->name;
		}
	}
	return $terms[0]->name;
}

/**
 * Whether a post is filed in a category or one of its children.
 *
 * @param int $post_id Post ID.
 * @param int $term_id Category term ID.
 * @return bool
 */
function altijara_post_in_category_tree( $post_id, $term_id ) {
	$post_id = absint( $post_id );
	$term_id = absint( $term_id );
	if ( ! $post_id || ! $term_id ) {
		return false;
	}
	$ids = array_map( 'intval', wp_get_post_categories( $post_id ) );
	if ( in_array( $term_id, $ids, true ) ) {
		return true;
	}
	foreach ( $ids as $cid ) {
		if ( term_is_ancestor_of( $term_id, $cid, 'category' ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Category label for a card in a listing.
 *
 * On a category archive, or inside a section that queries one category, a post
 * filed in that category shows that category — not whichever term comes first.
 *
 * @param int $post_id           Post ID.
 * @param int $preferred_term_id Listing category. 0 uses the current archive.
 * @return string
 */
function altijara_get_listing_category_name( $post_id, $preferred_term_id = 0 ) {
	$post_id   = absint( $post_id );
	$preferred = absint( $preferred_term_id );
	if ( ! $preferred ) {
		$ctx = function_exists( 'altijara_get_layout_context' ) ? altijara_get_layout_context() : array();
		if ( ! empty( $ctx['category'] ) ) {
			$preferred = absint( $ctx['category'] );
		} elseif ( is_category() ) {
			$queried = get_queried_object();
			if ( $queried instanceof WP_Term && 'category' === $queried->taxonomy ) {
				$preferred = (int) $queried->term_id;
			}
		}
	}
	// Prefer the listing / archive / admin-selected category over any other
	// term the post also belongs to — but never Uncategorized when another exists.
	if ( $preferred ) {
		$term = get_term( $preferred, 'category' );
		if ( $term && ! is_wp_error( $term ) && ! altijara_is_uncategorized_term( $term ) ) {
			return $term->name;
		}
	}
	return altijara_get_primary_category_name( $post_id );
}

/**
 * Card label for a homepage or archive section.
 *
 * @param int                  $post_id     Post ID.
 * @param array<string, mixed> $config      Section config.
 * @param string               $section_key Section id.
 * @return string
 */
function altijara_section_card_category_name( $post_id, $config = array(), $section_key = '' ) {
	$preferred = 0;
	if ( function_exists( 'altijara_resolve_section_category_id' ) ) {
		$preferred = (int) altijara_resolve_section_category_id( is_array( $config ) ? $config : array(), (string) $section_key );
	}
	// When admin picked a section category, always show that name — not another
	// category the post also belongs to (Uncategorized still yields to a real term).
	if ( $preferred ) {
		$term = get_term( $preferred, 'category' );
		if ( $term && ! is_wp_error( $term ) && ! altijara_is_uncategorized_term( $term ) ) {
			return $term->name;
		}
	}
	return altijara_get_listing_category_name( $post_id, $preferred );
}
