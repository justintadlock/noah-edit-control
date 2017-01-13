<?php
/**
 * Handles the user category checklist on the edit user screen.
 *
 * @package   NoahEditControl
 * @author    Justin Tadlock <justin@justintadlock.com>
 */

/**
 * Edit user class.
 *
 * @since  1.0.0
 * @access public
 */
final class NEC_Edit_User {

	/**
	 * Sets up the appropriate actions.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @return void
	 */
	protected function __construct() {

		// Load on the user edit screen.
		add_action( 'load-profile.php',   array( $this, 'load' ) );
		add_action( 'load-user-edit.php', array( $this, 'load' ) );

		// Save when the user profile/options are updated.
		add_action( 'personal_options_update',  array( $this, 'save' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save' ) );
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

		// If the current user cannot manage user categories, bail.
		if ( ! current_user_can( 'manage_user_categories' ) )
			return;

		// Add profile fields.
		add_action( 'show_user_profile', array( $this, 'fields' ) );
		add_action( 'edit_user_profile', array( $this, 'fields' ) );
	}

	/**
	 * Displays the user profile fields.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  object  $user
	 * @return void
	 */
	public function fields( $user ) {

		// Only show on the user's profile if they can publish/edit posts.
		if ( ! user_can( $user->ID, 'publish_posts' ) && ! user_can( $user->ID, 'edit_posts' ) )
			return; ?>

		<h3><?php esc_html_e( 'Post Categories', 'noah-edit-control' ); ?></h3>

		<table class="form-table">

			<tr>
				<th><?php esc_html_e( 'Select Categories', 'noah-edit-control' ); ?></th>

				<td>
					<?php wp_nonce_field( 'nec_user_checklist', 'nec_user_checklist_nonce' ); ?>
					<p class="description"><?php esc_html_e( 'Select the categories that the user is limited to posting in.', 'noah-edit-control' ); ?></p>
					<br />
					<div class="categorydiv">
						<div class="wp-tab-panel">
							<ul class="categorychecklist">
								<?php $this->checklist( $user ); ?>
							</ul>
						</div>
					</div>
				</td>
			</tr>

		</table>
	<?php }

	/**
	 * Displays the user category checklist.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  object  $user
	 * @return void
	 */
	public function checklist( $user ) {

		// Get the categories the user is assigned to.
		$user_cats = nec_get_user_categories( $user->ID );

		// Get all existing categories.
		$categories = (array) get_terms( 'category', array( 'get' => 'all' ) );

		// Create a new category checklist object.
		$walker = new Walker_Category_Checklist;

		// Get the HTML for the category checklist.
		$checklist = $walker->walk(
			$categories, 0,
			array(
				'taxonomy'      => 'category',
				'selected_cats' => $user_cats ? $user_cats : false
			)
		);

		// We need to change the `name` attribute b/c core doesn't allow us to input this.
		// Use `nec_user_category`.
		echo preg_replace(
			'/name=([\'"])post_category\[\]([\'"])/i',
			'name=$1nec_user_category[]$2',
			$checklist
		);
	}

	/**
	 * Saves the user meta.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  int     $user_id
	 * @return void
	 */
	public function save( $user_id ) {

		// Only show on the user's profile if they can publish/edit posts.
		if ( ! user_can( $user_id, 'publish_posts' ) && ! user_can( $user_id, 'edit_posts' ) )
			return;

		// Permissions.
		$verify_nonce = isset ( $_POST['nec_user_checklist_nonce'] ) ? wp_verify_nonce( $_POST['nec_user_checklist_nonce'], 'nec_user_checklist' ) : false;
		$can_manage   = current_user_can( 'manage_user_categories' );
		$can_edit     = current_user_can( 'edit_user', $user_id );

		// If permissions don't check out, bail.
		if ( ! $verify_nonce || ! $can_manage || ! $can_edit )
			return;

		// Get the current categories.
		$current_categories = nec_get_user_categories( $user_id );

		// Get the new categories.
		$new_categories = isset( $_POST['nec_user_category'] ) ? $_POST['nec_user_category'] : '';

		// If we have an array of new categories, set the categories.
		if ( is_array( $new_categories ) )
			nec_set_user_categories( $user_id, array_map( 'absint', $new_categories ) );

		// Else, if we have current categories but no new categories, delete them all.
		elseif ( ! empty( $current_categories ) )
			nec_delete_user_categories( $user_id );
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

NEC_Edit_User::get_instance();
