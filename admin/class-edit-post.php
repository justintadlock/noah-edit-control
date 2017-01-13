<?php
/**
 * Handles limiting the user category on the edit post screen.
 *
 * @package   NoahEditControl
 * @author    Justin Tadlock <justin@justintadlock.com>
 */

/**
 * Edit post class.
 *
 * @since  1.0.0
 * @access public
 */
final class NEC_Edit_Post {

	/**
	 * Stores the current post ID.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    int
	 */
	public $post_id = 0;

	/**
	 * Sets up the appropriate actions.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @return void
	 */
	protected function __construct() {

		add_action( 'load-post.php',     array( $this, 'load' ) );
		add_action( 'load-post-new.php', array( $this, 'load' ) );
	}

	/**
	 * Fires on the page load hook to add actions specifically for the post and
	 * new post screens.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function load() {

		$screen = get_current_screen();

		// Bail if not editing a blog post.
		if ( ! isset( $screen->post_type ) && 'post' !== $screen->post_type )
			return;

		// Add/Remove filters for `get_terms_args`.
		add_action( 'add_meta_boxes',   array( $this, 'add_filters' ),     0, 2 );
		add_action( 'dbx_post_sidebar', array( $this, 'remove_filters' ), 95    );

		// Filter the default post category.
		add_filter( 'option_default_category', array( $this, 'default_category' ) );
	}

	/**
	 * Adds the filter on `get_terms_args` if viewing the `post` post type.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string  $post_type
	 * @param  object  $post
	 * @return void
	 */
	public function add_filters( $post_type, $post ) {

		if ( 'post' === $post_type ) {
			add_filter( 'get_terms_args', array( $this, 'get_terms_args' ), 10, 2 );

			$this->post_id = $post->ID;
		}
	}

	/**
	 * Removes the filter on `get_terms_args`.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  object  $post
	 * @return void
	 */
	public function remove_filters( $post ) {

		if ( 'post' === $post->post_type )
			remove_filter( 'get_terms_args', array( $this, 'get_terms_args' ) );
	}

	/**
	 * Filter on `get_terms_args`.  This makes sure that only the user's allowed
	 * categories appear in the categories post meta box.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  array  $args
	 * @param  array  $taxonomies
	 * @return array
	 */
	public function get_terms_args( $args, $taxonomies ) {

		// Make sure we're only doing this if the `category` taxonomy is what's
		// being called for.
		if ( 1 === count( $taxonomies ) && 'category' === $taxonomies[0] ) {

			$user_id = get_post( $this->post_id )->post_author;

			$cat_ids = nec_get_user_categories( absint( $user_id ) );

			if ( $cat_ids )
				$args['include'] = $cat_ids;
		}

		return $args;
	}

	/**
	 * Filters the default post category.  WP will assign a default category
	 * if no category is given to the post.  However, since users are limited
	 * to specific categories, we want to make sure that they have permission
	 * to post in the default category.  If not, we'll select the first category
	 * from their list of allowed categories.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  int    $cat_id
	 * @return int
	 */
	public function default_category( $cat_id ) {

		// If we don't have a post ID yet, see if the global `$post` is set and use its ID.
		if ( ! $this->post_id ) {
			global $post;

			if ( is_object( $post ) )
				$this->post_id = $post->ID;
		}

		// If we still don't have a post ID, bail.
		if ( ! $this->post_id )
			return $cat_id;

		$user_id = get_post( $this->post_id )->post_author;

		$cat_ids = nec_get_user_categories( absint( $user_id ) );

		return is_array( $cat_ids ) && ! in_array( $cat_id, $cat_ids ) ? array_shift( $cat_ids ) : $cat_id;
	}

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
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

NEC_Edit_Post::get_instance();
