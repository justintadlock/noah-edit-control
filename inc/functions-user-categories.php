<?php
/**
 * User categories functions.
 *
 * @package   NoahEditControl
 * @author    Justin Tadlock <justin@justintadlock.com>
 */

/**
 * Returns an array of the contributors for a given post.
 *
 * @since  1.0.0
 * @access public
 * @param  int    $user_id
 * @return array
 */
function nec_get_user_categories( $user_id ) {

	return get_user_meta( $user_id, 'categories', false );
}

/**
 * Adds a single contributor to a post's access contributors.
 *
 * @since  1.0.0
 * @access public
 * @param  int        $user_id
 * @param  string     $contributor
 * @return int|false
 */
function nec_add_user_category( $user_id, $contributor ) {

	return add_user_meta( $user_id, 'categories', $contributor, false );
}

/**
 * Removes a single contributor from a post's access contributors.
 *
 * @since  1.0.0
 * @access public
 * @param  int        $user_id
 * @param  string     $contributor
 * @return bool
 */
function nec_remove_user_category( $user_id, $contributor ) {

	return delete_user_meta( $user_id, 'categories', $contributor );
}

/**
 * Sets a post's access contributors given an array of contributors.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @param  array   $contributors
 * @global object  $wp_contributors
 * @return void
 */
function nec_set_user_categories( $user_id, $contributors ) {

	// Get the current contributors.
	$current_contributors = nec_get_user_categories( $user_id );

	foreach ( $current_contributors as $contributor ) {

		// If the WP contributor is one of the current contributors but not a new contributor, remove it.
		if ( ! in_array( $contributor, $contributors ) )
			nec_remove_user_category( $user_id, $contributor );
	}

	// Loop through new contributors.
	foreach ( $contributors as $contributor ) {

		// If new contributor is not already one of the current contributors, add it.
		if ( ! in_array( $contributor, $current_contributors ) )
			nec_add_user_category( $user_id, $contributor );
	}
}

/**
 * Deletes all of a post's access contributors.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $user_id
 * @return bool
 */
function nec_delete_user_categories( $user_id ) {

	return delete_user_meta( $user_id, 'categories' );
}
