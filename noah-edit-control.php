<?php
/**
 * Plugin Name: Noah - Edit Control
 * Plugin URI:  http://themehybrid.com
 * Description: Custom plugin to control who can edit posts and pages.
 * Version:     1.0.0-dev
 * Author:      Justin Tadlock
 * Author URI:  http://themehybrid.com
 * Text Domain: noah-edit-control
 * Domain Path: /languages
 */

/**
 * Singleton class that sets up and initializes the plugin.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
final class Noah_Edit_Control {

	/**
	 * Directory path to the plugin folder.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $dir = '';

	/**
	 * Directory URI to the plugin folder.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $uri = '';

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return object
	 */
	public static function get_instance() {

		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new self;
			$instance->setup();
			$instance->includes();
			$instance->setup_actions();
		}

		return $instance;
	}

	/**
	 * Constructor method.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	private function __construct() {}

	/**
	 * Initial plugin setup.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	private function setup() {

		$this->dir = trailingslashit( plugin_dir_path( __FILE__ ) );
		$this->uri = trailingslashit( plugin_dir_url(  __FILE__ ) );
	}

	/**
	 * Loads include and admin files for the plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	private function includes() {

		require_once( $this->dir . 'inc/functions-contributors.php' );
		require_once( $this->dir . 'inc/functions-filters.php'      );

		if ( is_admin() )
			require_once( $this->dir . 'admin/class-meta-box-avatars.php' );
	}

	/**
	 * Sets up initial actions.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	private function setup_actions() {

		// Register activation hook.
		register_activation_hook( __FILE__, array( $this, 'activation' ) );

		// Internationalize the text strings used.
		add_action( 'plugins_loaded', array( $this, 'i18n' ), 2 );

		// Registers scripts and styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_register_scripts' ) );
	}

	/**
	 * Register scripts and styles.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function admin_register_scripts() {

		wp_register_style( 'noah-edit-control', $this->uri . 'css/noah-edit-control.css' );
	}

	/**
	 * Loads the translation files.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function i18n() {

	//	load_plugin_textdomain( 'avatars-meta-box', false, trailingslashit( dirname( plugin_basename( __FILE__ ) ) ) . 'languages' );
	}

	/**
	 * Method that runs only when the plugin is activated.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function activation() {

		$roles = array( 'administrator', 'editor' );

		foreach ( $roles as $r ) {

			$role = get_role( $r );

			if ( $role ) {
				$role->add_cap( 'create_pages' );
				$role->add_cap( 'manage_page_contributors' );
			}
		}
	}
}

/**
 * Gets the instance of the main plugin class.
 *
 * @since  1.0.0
 * @access public
 * @return object
 */
function noah_edit_control() {
	return Noah_Edit_Control::get_instance();
}

// Let's do this thang!
noah_edit_control();
