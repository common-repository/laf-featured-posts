<?php
/**
 * Plugin functions.
 */

/**
 * Retrieve featured posts.
 *
 * @since 0.1.0
 *
 * @return array List of featured posts.
 */
function laffp_get_posts( $group_slug, $args = array() ) {
	global $laffp_option;

	$meta_key = 'laffp_order_' . (string) $group_slug;
	$meta_query = array(
		array(
			'key' => $meta_key,
			'compare' => 'EXISTS'
		)
	);
	$defaults = array(
		'post_type' => 'any',
		'post_status' => 'publish',
		'orderby' => 'meta_value_num',
		'meta_key' => $meta_key,
		'posts_per_page' => $laffp_option->get_option( 'posts_per_page', 10 ),
		'order' => 'ASC'
	);
	$args = wp_parse_args( $args, $defaults );
	return get_posts( $args );
}
