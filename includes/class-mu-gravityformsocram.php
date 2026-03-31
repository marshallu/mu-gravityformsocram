<?php
/**
 * Main plugin class.
 *
 * @package MU_GRAVITYFORMSOCRAM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main MU_GRAVITYFORMSOCRAM class.
 */
class MU_GRAVITYFORMSOCRAM {

	/**
	 * Single instance of the class.
	 *
	 * @var MU_GRAVITYFORMSOCRAM
	 */
	private static $instance = null;

	/**
	 * Returns the single instance of MU_GRAVITYFORMSOCRAM.
	 *
	 * @return MU_GRAVITYFORMSOCRAM
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Register hooks.
	 */
	private function init_hooks() {
		add_action( 'gform_loaded', array( $this, 'load_feed_addon' ), 5 );
	}

	/**
	 * Bootstraps the GFFeedAddOn on gform_loaded.
	 * Calls GFForms::include_feed_addon_framework() then registers the add-on.
	 */
	public function load_feed_addon() {
		if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
			return;
		}
		GFForms::include_feed_addon_framework();
		require_once MU_GRAVITYFORMSOCRAM_PLUGIN_DIR . 'includes/class-mu-gravityformsocram-feed.php';
		GFAddOn::register( 'MU_GravityFormsOcram_Feed' );
	}
}
