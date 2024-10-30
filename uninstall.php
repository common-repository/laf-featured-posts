<?php
/**
 * Uninstall Laf Featured Posts.
 *
 * @package Lafrec
 *
 * @since 0.1.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

function laffp_uninstall() {
	global $wpdb;

	if ( is_multisite() ) {
		$blogs = wp_get_sites();
		foreach ( $blogs as $blog ) {
			switch_to_blog( $blog[ 'blog_id' ] );
			laffp_process_uninstall();
			restore_current_blog();
		}
	} else {
		laffp_process_uninstall();
	}
}

function laffp_process_uninstall() {
	global $wpdb, $wp_roles;

	/**
	 * Delete caps
	 */
	if ( class_exists( 'WP_Roles' ) ) {
		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}
	}

	$caps = get_option( 'laffp_caps', array() );
	if ( $caps && is_object( $wp_roles ) ) {
		foreach ( $wp_roles->roles as $role_name => $role ) {
			foreach ( $caps as $cap ) {
				$wp_roles->remove_cap( $role_name, $cap );
			}
		}
	}
	delete_option( 'laffp_caps' );

	/**
	 * Delete Custom Posts
	 */
	$args = array(
		'post_type' => 'laffp_group',
		'posts_per_page' => -1,
		'post_status' => 'any'
	);
	$groups = get_posts( $args );
	$order_meta_keys = array();
	if ( $groups ) {
		foreach ( $groups as $group ) {
			$order_meta_keys[] = 'laffp_order_' . $group->post_name;

			// `wp_delete_post` is delete everything that is tied to the post
			wp_delete_post( $group->ID, true );
		}
	}

	/**
	 * Delete Posts Meta
	 */
	// Posts order
	if ( $order_meta_keys ) {
		foreach ( $order_meta_keys as $meta_key ) {
			$where = array( 'meta_key' => $meta_key );
			$format = array( '%s' );
			$wpdb->delete( $wpdb->postmeta, $where, $format );
		}
	}
	// Target post types
	$where = array( 'meta_key' => 'laffp_post_types' );
	$format = array( '%s' );
	$wpdb->delete( $wpdb->postmeta, $where, $format );

	/**
	 * Delete Option
	 */
	delete_option( 'laffp_option' );
}

laffp_uninstall();
