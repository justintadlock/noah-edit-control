<?php

// Add custom caps to Members cap groups.
add_action( 'members_register_cap_groups', 'nec_register_cap_groups' );

// Filter the page post type arguments.
add_filter( 'register_post_type_args', 'nec_post_type_args', 10, 2 );

// Custom capability mapping.
add_filter( 'map_meta_cap', 'nec_map_meta_cap', 10, 4 );

function nec_register_cap_groups() {

	$group = members_get_cap_group( 'type-page' );

	if ( is_object( $group ) )
		$group->caps[] = 'manage_page_contributors';
}

/**
 * Adds the `create_pages` capability for the `page` post type.  This is used
 * more as a "meta/primitive" hybrid capability.  We'll remap this with a filter on
 * `map_meta_cap`.
 *
 * @since  1.0.0
 * @access public
 * @param  array   $args
 * @param  string  $type
 * @return array
 */
function nec_post_type_args( $args, $type ) {

	if ( 'page' === $type )
		$args['capabilities']['create_posts'] = 'create_pages';

	return $args;
}

/**
 * Custom capability mapping.
 *
 * @since  1.0.0
 * @access public
 * @param  array   $caps
 * @param  string  $cap
 * @param  int     $user_id
 * @param  array   $args
 * @return array
 */
function nec_map_meta_cap( $caps, $cap, $user_id, $args ) {

	// If editing a post, we need to check if the user can "contribute" to the post.
	// If so, we need to allow that user to edit by setting the required caps to
	// simple the post type's `edit_posts` cap.
	if ( 'edit_post' === $cap || 'edit_page' === $cap ) {

		$post_id = isset( $args[0] ) ? $args[0] : false;

		if ( $post_id ) {

			$post_type = get_post_type_object( get_post_type( $post_id ) );

			$contribs = nec_get_post_contributors( $post_id );

			if ( is_array( $contribs ) && in_array( $user_id, $contribs ) )
				$caps = array( $post_type->cap->edit_posts );
		}
	}

	// We're mapping a custom `create_pages` cap to either `edit_pages`, depending on the
	// specific circumstance.  On the admin side, we must wait until the `admin_menu` hook
	// has fired because core WP will give a access error if not.  This is a core bug.
	else if ( 'create_pages' === $cap && is_admin() && ! did_action( 'admin_menu' ) ) {

		$caps = array( 'edit_pages' );
	}

	// This is a bit of a hacky method to make this work, but core WP really doesn't like
	// users editing posts if they don't have the `edit_others_posts` cap.  So, we're
	// going to bypass this check from `_wp_translate_postdata()` just on the post screen.
	// See `wp-admin/includes/post.php` for more details.
	//
	// @link https://core.trac.wordpress.org/ticket/36056
	// @link https://core.trac.wordpress.org/ticket/30452
	// @link https://core.trac.wordpress.org/ticket/33453
	//
	// Note that we also need to do a `function_exists()` check here b/c `map_meta_cap()` is called
	// when `get_current_screen()` may not be available.
	else if ( is_admin() && did_action( 'admin_menu' ) && 'post' === get_current_screen()->base ) {

		$post_type = get_post_type_object( get_current_screen()->post_type );

		if ( $post_type->cap->edit_others_posts == $cap ) {
			global $post;

			$contribs = nec_get_post_contributors( $post->ID );

			if ( is_array( $contribs ) && in_array( $user_id, $contribs ) )
				$caps = array( $post_type->cap->edit_posts );
		}
	}

	return $caps;
}
