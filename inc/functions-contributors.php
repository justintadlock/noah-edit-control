<?php
/**
 * Post/page contributors functions.
 *
 * @package   NoahEditControl
 * @author    Justin Tadlock <justin@justintadlock.com>
 */

/**
 * Returns an array of the contributors for a given post.
 *
 * @since  1.0.0
 * @access public
 * @param  int    $post_id
 * @return array
 */
function nec_get_post_contributors( $post_id ) {

	return get_post_meta( $post_id, 'contributors', false );
}

/**
 * Adds a single contributor to a post's access contributors.
 *
 * @since  1.0.0
 * @access public
 * @param  int        $post_id
 * @param  string     $contributor
 * @return int|false
 */
function nec_add_post_contributor( $post_id, $contributor ) {

	return add_post_meta( $post_id, 'contributors', $contributor, false );
}

/**
 * Removes a single contributor from a post's access contributors.
 *
 * @since  1.0.0
 * @access public
 * @param  int        $post_id
 * @param  string     $contributor
 * @return bool
 */
function nec_remove_post_contributor( $post_id, $contributor ) {

	return delete_post_meta( $post_id, 'contributors', $contributor );
}

/**
 * Sets a post's access contributors given an array of contributors.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $post_id
 * @param  array   $contributors
 * @global object  $wp_contributors
 * @return void
 */
function nec_set_post_contributors( $post_id, $contributors ) {

	// Get the current contributors.
	$current_contributors = nec_get_post_contributors( $post_id );

	foreach ( $current_contributors as $contributor ) {

		// If the WP contributor is one of the current contributors but not a new contributor, remove it.
		if ( ! in_array( $contributor, $contributors ) )
			nec_remove_post_contributor( $post_id, $contributor );
	}

	// Loop through new contributors.
	foreach ( $contributors as $contributor ) {

		// If new contributor is not already one of the current contributors, add it.
		if ( ! in_array( $contributor, $current_contributors ) )
			nec_add_post_contributor( $post_id, $contributor );
	}
}

/**
 * Deletes all of a post's access contributors.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $post_id
 * @return bool
 */
function nec_delete_post_contributors( $post_id ) {

	return delete_post_meta( $post_id, 'contributors' );
}
