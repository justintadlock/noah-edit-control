<?php
/**
 * Handles the author avatars meta box.
 *
 * @package   AvatarsMetaBox
 * @version   1.0.0
 * @author    Justin Tadlock <justin@justintadlock.com>
 * @copyright Copyright (c) 2015, Justin Tadlock
 * @link      http://themehybrid.com/plugins/avatars-meta-box
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/**
 * Meta box class.
 *
 * @since  1.0.0
 * @access public
 */
final class NEC_Meta_Box_Avatars {

	/**
	 * Post types to show the meta box on.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    array
	 */
	public $post_types = array( 'page' );

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

		if ( ! isset( $screen->post_type ) || ! in_array( $screen->post_type, $this->post_types ) )
			return;

		// Add custom meta boxes.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		// Enqueue scripts/styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Loads scripts and styles.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function enqueue() {

		wp_enqueue_style( 'noah-edit-control' );
	}

	/**
	 * Adds the meta box.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function add_meta_boxes( $post_type ) {

		$pt_object = get_post_type_object( $post_type );

		// Add our custom meta box.
		add_meta_box( 'nec-page-contributors', sprintf( esc_html__( '%s Contributors', 'noah-edit-control' ), $pt_object->labels->singular_name ), array( $this, 'meta_box' ), $post_type, 'normal', 'default' );
	}

	/**
	 * Outputs the meta box HTML.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  object  $post
	 * @return void
	 */
	public function meta_box( $post ) {

		$contributors = get_post_meta( $post->ID, 'contributors', false );

		// Set up the main arguments for `get_users()`.
		$args = array( 'role__in' => $this->get_roles( $post->post_type ) );

		// Get the users allowed to be post author.
		$users = get_users( $args ); ?>

		<div class="nec-avatars">

		<?php foreach ( $users as $user ) : ?>

			<label>
				<input type="checkbox" value="<?php echo esc_attr( $user->ID ); ?>" name="nec_contributors" <?php checked( in_array( $user->ID, $contributors ) ); ?> />

				<span class="screen-reader-text"><?php echo esc_html( $user->display_name ); ?></span>

				<?php echo get_avatar( $user->ID, 70 ); ?>
			</label>

		<?php endforeach; ?>

		</div><!-- .nec-avatars -->
	<?php }

	public function save( $post_id ) {

		// Get the current contributors.
		$current_roles = nec_get_post_contributors( $post_id );

		// Get the new contributors.
		$new_contributors = isset( $_POST['nec_contributors'] ) ? $_POST['nec_contributors'] : '';

		// If we have an array of new contributors, set the contributors.
		if ( is_array( $new_contributors ) )
			nec_set_post_contributors( $post_id, array_map( 'absint', $new_contributors ) );

		// Else, if we have current contributors but no new contributors, delete them all.
		elseif ( ! empty( $current_contributors ) )
			nec_delete_post_contributors( $post_id );

	}

	/**
	 * Returns an array of user roles that are allowed to edit, publish, or create
	 * posts of the given post type.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string  $post_type
	 * @global object  $wp_roles
	 * @return array
	 */
	public function get_roles( $post_type ) {
		global $wp_roles;

		$roles = array();
		$type  = get_post_type_object( $post_type );

		// Get the post type object caps.
		$caps = array( $type->cap->edit_posts, $type->cap->publish_posts, $type->cap->create_posts );
		$caps = array_unique( $caps );

		// Loop through the available roles.
		foreach ( $wp_roles->roles as $name => $role ) {

			foreach ( $caps as $cap ) {

				// If the role is granted the cap, add it.
				if ( isset( $role['capabilities'][ $cap ] ) && true === $role['capabilities'][ $cap ] ) {
					$roles[] = $name;
					break;
				}
			}
		}

		return $roles;
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

NEC_Meta_Box_Avatars::get_instance();
