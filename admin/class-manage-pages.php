<?php
/**
 * Manage pages admin screen.
 *
 * @package    NoahEditControl
 * @author     Justin Tadlock <justintadlock@gmail.com>
 */

/**
 * Manage pages class.
 *
 * @since  1.1.0
 * @access public
 */
final class NEC_Manage_Pages {

	/**
	 * Sets up the needed actions.
	 *
	 * @since  1.1.0
	 * @access public
	 * @return void
	 */
	private function __construct() {

		add_action( 'load-edit.php', array( $this, 'load' ) );
	}

	/**
	 * Runs on the page load. Checks if we're viewing the page post type and adds
	 * the appropriate actions/filters for the page.
	 *
	 * @since  1.1.0
	 * @access public
	 * @return void
	 */
	public function load() {

		$screen = get_current_screen();

		// Bail if not on the pages screen.
		if ( empty( $screen->post_type ) || 'page' !== $screen->post_type )
			return;

		// Filter the `request` vars.
		add_filter( 'request', array( $this, 'request' ) );
	}

	/**
	 * Filter on the `request` hook to change what posts are loaded.
	 *
	 * @since  1.1.0
	 * @access public
	 * @param  array  $vars
	 * @return array
	 */
	public function request( $vars ) {

		$new_vars = array();

		// Only run if the current user cannot edit others' pages.
		if ( ! current_user_can( 'edit_others_pages' ) ) {

			// This is a bit of a janky way to handle this feature.  What we're doing
			// is loading all post IDs written by the current user.  Then, we're loading
			// all post IDs the user can contribute to.  Doing one or the other is easy.
			// Doing them both without separate DB queries is harder.  There's probably
			// a better hook available.  I just don't know what it is.  So, we have this.
			// It's only for one page in the admin, so it won't be loaded a lot.

			// Get user's authored posts.
			$author = new WP_Query(
				array(
					'post_type' => 'page',
					'author'    => get_current_user_id(),
					'fields'    => 'ids'
				)
			);

			// Get posts the user can contribute to.
			$meta = new WP_Query(
				array(
					'post_type' => 'page',
					'meta_query' => array(
						array(
							'key' => 'contributors',
							'value' => get_current_user_id(),
							'type' => 'NUMERIC'
						)
					),
					'fields' => 'ids'
				)
			);

			// Pass the post IDs along to core.
			$new_vars['post__in'] = array_merge( (array) $author->posts, (array) $meta->posts );
		}

		// Return the vars, merging with the new ones.
		return array_merge( $vars, $new_vars );
	}

	/**
	 * Returns the instance.
	 *
	 * @since  1.1.0
	 * @access public
	 * @return object
	 */
	public static function get_instance() {

		static $instance = null;

		if ( is_null( $instance ) )
			$instance = new self;

		return $instance;
	}
}

NEC_Manage_Pages::get_instance();
