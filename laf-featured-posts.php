<?php
/**
 * Plugin Name: Laf Featured Posts
 * Version: 0.2.1
 * Description: Featured posts plugin & widget.
 * Author: LafCreate
 * Author URI: http://www.lafcreate.com
 * Plugin URI: http://www.lafcreate.com
 * Text Domain: laf-featured-posts
 * Domain Path: /languages
 * @package Laffp
 */
/*
Copyright (C) 2016 LafCreate (email: info at lafcreate.com)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Prevent directly access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$laffp_header = get_file_data(
	__FILE__,
 	array(
		'version' => 'Version',
		'text_domain' => 'Text Domain',
		'domain_path' => 'Domain Path'
	)
);

define( 'LAFFP_VERSION', $laffp_header[ 'version' ] );
define( 'LAFFP_TEXT_DOMAIN', $laffp_header[ 'text_domain' ] );
define( 'LAFFP_LANG_DIR', dirname( plugin_basename( __FILE__ ) ) . $laffp_header[ 'domain_path' ] );
define( 'LAFFP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LAFFP_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'LAFFP_POST_TYPE', 'laffp_group' );

unset( $laffp_header );

require( LAFFP_PLUGIN_DIR . 'includes/class-laffp-option.php' );
require( LAFFP_PLUGIN_DIR . 'includes/class-laffp-capability.php' );
require( LAFFP_PLUGIN_DIR . 'includes/widgets/class-laffp-widget-posts.php' );
require( LAFFP_PLUGIN_DIR . 'includes/functions.php' );

global $laffp_option;
$laffp_option = new Laffp_Option();
$laffp_option->load_option();

add_action( 'plugins_loaded', 'laffp_load_textdomain' );
add_action( 'init', 'laffp_register_post_types' );
add_action( 'widgets_init', 'laffp_register_widget' );
add_filter( 'map_meta_cap', 'laffp_map_meta_cap', 10, 4 );
add_action( 'switch_blog', 'laffp_switch_option', 10, 2 );

if ( is_admin() ) {
	require( LAFFP_PLUGIN_DIR . 'admin/admin.php' );
}

register_activation_hook( __FILE__, 'laffp_install' );
register_deactivation_hook( __FILE__, 'laffp_deactivate' );

/**
 * Loads the plugin language files.
 *
 * @since 0.1.0
 *
 * @return bool True when textdomain is successfully loaded, false otherwise.
 */
function laffp_load_textdomain() {
	return load_plugin_textdomain( LAFFP_TEXT_DOMAIN, false, LAFFP_LANG_DIR );
}

/**
 * Register custom post type.
 *
 * @since 0.1.0
 *
 * @return void
 */
function laffp_register_post_types() {
	$labels = array(
		'name' => _x( 'Featured Posts', 'post type general name', LAFFP_TEXT_DOMAIN ),
		'singular_name' => _x( 'Group', 'post type singular name', LAFFP_TEXT_DOMAIN ),
		'add_new' => _x( 'Add New', 'post', LAFFP_TEXT_DOMAIN ),
		'add_new_item' => __( 'Add New Group', LAFFP_TEXT_DOMAIN ),
		'edit_item' => __( 'Edit Group', LAFFP_TEXT_DOMAIN ),
		'new_item' => __( 'New Group', LAFFP_TEXT_DOMAIN ),
		'view_item' => __( 'View Group', LAFFP_TEXT_DOMAIN ),
		'search_items' => __( 'Search Group', LAFFP_TEXT_DOMAIN ),
		'not_found' => __( 'No groups found.', LAFFP_TEXT_DOMAIN ),
		'not_found_in_trash' => __( 'No groups found in Trash.', LAFFP_TEXT_DOMAIN ),
		'parent_item_colon' => null,
		'all_items' => __( 'All Groups', LAFFP_TEXT_DOMAIN ),
		'featured_image' => __( 'Featured Image', LAFFP_TEXT_DOMAIN ),
		'set_featured_image' => __( 'Set featured image', LAFFP_TEXT_DOMAIN ),
		'remove_featured_image' => __( 'Remove featured image', LAFFP_TEXT_DOMAIN ),
		'use_featured_image' => __( 'Use as featured image', LAFFP_TEXT_DOMAIN )
	);
	$args = array(
		'label' => apply_filters( 'laffp_post_type_label', __( 'Featured Posts', LAFFP_TEXT_DOMAIN )  ),
		'labels' => $labels,
		'description' => '',
		'public' => false,
		'hierarchical' => false,
		//'exclude_from_search' => null,
		//'publicly_queryable' => null,
		'show_ui' => true,
		//'show_in_nav_menus' => null,
		//'show_in_menu' => false,
		//'show_in_admin_bar' => false,
		//'menu_position' => null,
		'menu_icon' => null,
		'capability_type' => LAFFP_POST_TYPE,
		//'capabilities' => array(),
		'map_meta_cap' => true,
		'supports' => array( 'title' ),
		'register_meta_box_cb' => null,
		'taxonomies' => array(),
		'has_archive' => false,
		'rewrite' => false,
		'query_var' => false,
		'can_export' => false,
		'show_in_rest' => false
	);
	register_post_type( LAFFP_POST_TYPE, $args );
}

function laffp_register_widget() {
	register_widget( 'Laffp_Widget_Posts' );
}

function laffp_switch_option() {
	global $laffp_option;
	$laffp_option->load_option();
}

function laffp_map_meta_cap( $caps, $cap, $user_id, $args ) {
	return Laffp_Capability::map_meta_cap( $caps, $cap, $user_id, $args );
}

/**
 * Install.
 *
 * @since 0.1.0
 *
 * @return void
 */
function laffp_install( $network_wide = false ) {

	laffp_load_textdomain();

	if ( is_multisite() && $network_wide ) {
		$blogs = wp_get_sites();
		foreach ( $blogs as $blog ) {
			switch_to_blog( $blog[ 'blog_id' ] );
			laffp_process_install();
			restore_current_blog();
		}
	} else {
		laffp_process_install();
	}
}

function laffp_process_install() {
	laffp_register_post_types();
	flush_rewrite_rules( false );
	laffp_upgrade();
	laffp_add_caps();
	laffp_create_initial_post();
}

/**
 * Upgade.
 *
 * @since 0.1.0
 *
 * @return void
 */
function laffp_upgrade() {
	global $laffp_option;

	$current_version = $laffp_option->get_option( 'version' );
	if ( $current_version === LAFFP_VERSION ) {
		return;
	}

	$laffp_option->update_option( 'version', LAFFP_VERSION );
}

/**
 * Add caps to roles.
 *
 * @since 0.1.0
 *
 * @return void
 */
function laffp_add_caps() {
	$post_type = get_post_type_object( LAFFP_POST_TYPE );
	if ( $post_type ) {
		$caps = (array) $post_type->cap;
		$roles = array( 'administrator', 'editor' );
		foreach ( $roles as $role_name ) {
			$role = get_role( $role_name );
			if ( $role ) {
				foreach ( $caps as $cap ) {
					$role->add_cap( $cap );
				}
			}
		}
	}
}

/**
 * Create initial post.
 *
 * @since 0.1.0
 *
 * @return void
 */
function laffp_create_initial_post() {
	$args = array(
		'post_type' => LAFFP_POST_TYPE,
		'post_status' => 'any'
	);

	if ( get_posts( $args ) ) {
		return;
	}

	$post_id = wp_insert_post( array(
		'post_type' => LAFFP_POST_TYPE,
		'post_status' => 'publish',
		'post_title' => __( 'Featured Posts', LAFFP_TEXT_DOMAIN ),
		'post_name' => 'featured-posts',
		'post_content' => ''
	));

	if ( $post_id ) {
		update_post_meta( $post_id, 'laffp_post_types', array( 'post' ) );
	}
}

/**
 * Deactivation.
 *
 * @since 0.1.0
 *
 * @return void
 */
function laffp_deactivate() {
	$caps = array();
	$post_type = get_post_type_object( LAFFP_POST_TYPE );
	if ( $post_type ) {
		$caps = (array) $post_type->cap;
	}
	add_option( 'laffp_caps', $caps, '', 'no' );
}
