<?php

require( LAFFP_PLUGIN_DIR . 'admin/includes/class-laffp-options-page.php' );
require( LAFFP_PLUGIN_DIR . 'admin/includes/meta-boxes/class-laffp-posts-order-meta-box.php' );
require( LAFFP_PLUGIN_DIR . 'admin/includes/meta-boxes/class-laffp-submit-meta-box.php' );
require( LAFFP_PLUGIN_DIR . 'admin/includes/meta-boxes/class-laffp-target-post-type-meta-box.php' );
require( LAFFP_PLUGIN_DIR . 'admin/includes/meta-boxes/class-laffp-feature-meta-box.php' );

add_action( 'admin_menu', 'laffp_register_option_page' );
add_action( 'admin_menu', 'laffp_register_meta_boxes' );
add_action( 'admin_init', 'laffp_upgrade' );
add_action( 'admin_enqueue_scripts', 'laffp_admin_enqueue_scripts' );
add_filter( 'default_hidden_meta_boxes', 'laffp_default_hidden_meta_boxes', 10, 2 );
add_filter( 'post_row_actions','laffp_remove_quick_edit', 10, 2 );

/**
 * Register plugin option page.
 *
 * @since 0.1.0
 *
 * @return void
 */
function laffp_register_option_page() {
	global $laffp_option;

	$option_page = new Laffp_Options_Page( $laffp_option );
	$option_page->register();
}

/**
 * Register meta boxes.
 *
 * @since 0.1.0
 *
 * @return void
 */
function laffp_register_meta_boxes() {
	$posts_order_meta_box = new Laffp_Posts_Order_Meta_Box();
	$posts_order_meta_box->register();

	$taret_post_type_meta_box = new Laffp_Target_Post_Type_Meta_Box();
	$taret_post_type_meta_box->register();

	remove_meta_box( 'submitdiv', LAFFP_POST_TYPE, 'side' );
	$submit_meta_box = new Laffp_Submit_Meta_Box();
	$submit_meta_box->register();

	$group_args = array(
		'post_type' => LAFFP_POST_TYPE,
		'posts_per_page' => -1
	);
	$groups = get_posts( $group_args );
	foreach ( $groups as $group ) {
		$feature_meta_box = new Laffp_Feature_Meta_Box( $group );
		$feature_meta_box->register();
	}
}

/**
 * Load scripts.
 *
 * @since 0.1.0
 *
 * @return void
 */
function laffp_admin_enqueue_scripts( $hook_suffix ) {
	$is_post_page = in_array( $hook_suffix, array( 'post-new.php', 'post.php' ) );

	if ( $is_post_page && get_post_type() === LAFFP_POST_TYPE ) {
		wp_register_script(
			'laffp-touch-punch',
			plugins_url( 'js/jquery.ui.touch-punch.min.js', dirname( __FILE__ ) ),
			array( 'jquery', 'jquery-ui-sortable'),
			true,
			false
		);
		wp_enqueue_script( 'laffp-touch-punch' );

		wp_register_script(
			'laffp-admin',
			plugins_url( 'js/admin.js', dirname( __FILE__ ) ),
			array( 'jquery', 'jquery-ui-sortable'),
			true,
			false
		);
		wp_enqueue_script( 'laffp-admin' );

		$data = array(
			'field' => ''
		);
		wp_localize_script( 'laffp-admin', 'LAFFP', $data );

		// CSS
		wp_register_style(
			'laffp-admin',
			plugins_url( 'css/admin.css', dirname( __FILE__ ) ),
			array(),
			false,
			'all'
		);
		wp_enqueue_style( 'laffp-admin' );
	}
}

/**
 * Show slug meta box by default.
 *
 * @since 0.1.0
 *
 * @return void
 */
function laffp_default_hidden_meta_boxes( $hidden, $screen ) {
	if ( $screen->post_type === LAFFP_POST_TYPE ) {
		return array();
	}
	return $hidden;
}

/**
 * Hide quick edit link.
 *
 * @since 0.1.0
 *
 * @return void
 */
function laffp_remove_quick_edit( $actions ) {
	if ( get_post_type() === LAFFP_POST_TYPE ) {
		unset( $actions['inline hide-if-no-js'] );
	}
	return $actions;
}
