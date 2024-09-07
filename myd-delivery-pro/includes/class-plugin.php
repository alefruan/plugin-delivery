<?php

namespace MydPro\Includes;

use MydPro\Includes\Store_Data;
use MydPro\Includes\Admin\Settings;
use MydPro\Includes\Admin\Custom_Posts;
use MydPro\Includes\Admin\Admin_Page;
use MydPro\Includes\License\License;
use MydPro\Includes\Plugin_Update\Plugin_Update;
use MydPro\Includes\Custom_Fields\Myd_Custom_Fields;
use MydPro\Includes\Custom_Fields\Register_Custom_Fields;
use MydPro\Includes\Ajax\Update_Cart;
use MydPro\Includes\Ajax\Create_Draft_Order;
use MydPro\Includes\Ajax\Place_Payment;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin main class
 *
 * @since 1.9.6
 */
final class Plugin {

	/**
	 * Store data
	 *
	 * @since 1.9.6
	 *
	 * TODO: change to protected and create method to get
	 */
	public $store_data;

	/**
	 * License
	 *
	 * @since 1.9.6
	 *
	 * TODO: change to protected and create method to get
	 */
	public $license;

	/**
	 * License
	 *
	 * @since 1.9.6
	 */
	protected $admin_settings;

	/**
	 * Custom Posts
	 *
	 * @since 1.9.6
	 */
	protected $custom_posts;

	/**
	 * Admin menu pages
	 */
	protected $admin_menu_pages;

	/**
	 * Instance
	 *
	 * @since 1.9.4
	 *
	 * @access private
	 * @static
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.9.4
	 *
	 * @access public
	 * @static
	 *
	 * @return Plugin An instance of the class.
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Disable class cloning and throw an error on object clone.
	 *
	 * @access public
	 * @since 1.9.6
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Something went wrong.', 'myd-delivery-pro' ), '1.0' );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * @access public
	 * @since 1.9.6
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Something went wrong.', 'myd-delivery-pro' ), '1.0' );
	}

	/**
	 * Construct class
	 *
	 * @since 1.2
	 * @return void
	 */
	private function __construct() {
		do_action( 'myd_delivery_pro_init' );
		add_action( 'plugins_loaded', [ $this, 'init' ] );
		register_activation_hook( MYD_PLUGIN_MAIN_FILE, [ $this, 'activation' ] );
		register_deactivation_hook( MYD_PLUGIN_MAIN_FILE, [ $this, 'deactivation' ] );
	}

	/**
	 * Init plugin
	 *
	 * @since 1.9.4
	 */
	public function init() {
		load_plugin_textdomain( 'myd-delivery-pro', false, MYD_PLUGIN_DIRNAME . '/languages' );

		/**
		 * Check and solve plugin path name
		 */
		$this->check_plugin_path();

		/**
		 * Check if old version of plugin is active
		 */
		if ( $this->plugin_is_active( 'my-delivey-wordpress/my-delivey-wordpress.php' ) || $this->plugin_is_active( 'my-delivery-wordpress/my-delivery-wordpress.php' ) ) {

			$error_message = sprintf(
				esc_html__( '%1$s requires MyDelivery WordPress (our old version) to be deactivated.', 'myd-delivery-pro' ),
				'<strong>MyD Delivery Pro</strong>'
			);

			add_action( 'admin_notices', function( $message ) use ( $error_message ) {
					printf( '<div class="notice notice-error"><p>%1$s</p></div>', $error_message );
				}
			);
			return;
		}

		/**
		 * Required files (load classes)
		 */
		$this->set_required_files();
		$this->init_update_files();

		new Update_Cart();
		new Create_Draft_Order();
		new Place_Payment();

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frondend_scripts' ] );

		$this->license = new License();

		if ( is_admin() ) {
			$this->admin_settings = new Settings();
			add_action( 'admin_init', [ $this->admin_settings, 'register_settings' ] );

			$this->admin_menu_pages = new Admin_Page();
			add_action( 'admin_menu', [ $this->admin_menu_pages, 'add_admin_pages' ] );
		}

		$this->custom_posts = new Custom_Posts();
		add_action( 'init', [ $this->custom_posts, 'register_custom_posts' ] );

		Store_Data::set_store_data();
		$this->store_data = Store_Data::get_store_data();

		/**
		 * Plugin update checker
		 */
		$plugin_update = new Plugin_Update();
		add_filter( 'plugins_api', array( $plugin_update, 'info' ), 20, 3 );
		add_filter( 'pre_set_site_transient_update_plugins', array( $plugin_update, 'update' ) );

		/**
		 * TODO: Move to license class
		 */
		add_action( 'in_plugin_update_message-myd-delivery-pro/myd-delivery-pro.php', [ $this, 'update_notice_invalid_license' ], 10, 2 );

		if ( is_admin() ) {
			new Myd_Custom_Fields( Register_Custom_Fields::get_registered_fields() );
		}
	}

	/**
	 * Load required files
	 *
	 * @since 1.2
	 * @return void
	 */
	public function set_required_files() {
		if ( is_admin() ) {
			include_once MYD_PLUGIN_PATH . 'includes/admin/class-admin-page.php';
			include_once MYD_PLUGIN_PATH . 'includes/admin/abstract-class-admin-settings.php';
			include_once MYD_PLUGIN_PATH . 'includes/admin/class-settings.php';
			include_once MYD_PLUGIN_PATH . 'includes/class-reports.php';
		}

		include_once MYD_PLUGIN_PATH . 'includes/legacy/class-legacy-repeater.php';
		include_once MYD_PLUGIN_PATH . 'includes/custom-fields/class-register-custom-fields.php';
		include_once MYD_PLUGIN_PATH . 'includes/custom-fields/class-label.php';
		include_once MYD_PLUGIN_PATH . 'includes/custom-fields/class-custom-fields.php';
		include_once MYD_PLUGIN_PATH . 'includes/class-store-data.php';
		include_once MYD_PLUGIN_PATH . 'includes/admin/class-custom-posts.php';
		include_once MYD_PLUGIN_PATH . 'includes/fdm-products-list.php';
		include_once MYD_PLUGIN_PATH . 'includes/myd-manage-cpt-columns.php';
		include_once MYD_PLUGIN_PATH . 'includes/class-orders-front-panel.php';
		include_once MYD_PLUGIN_PATH . 'includes/fdm-track-order.php';
		include_once MYD_PLUGIN_PATH . 'includes/api.php';
		include_once MYD_PLUGIN_PATH . 'includes/api/sse/class-order-status-tracking.php';
		include_once MYD_PLUGIN_PATH . 'includes/api/order/class-get-order.php';
		include_once MYD_PLUGIN_PATH . 'includes/set-custom-styles.php';
		include_once MYD_PLUGIN_PATH . 'includes/class-legacy.php';
		include_once MYD_PLUGIN_PATH . 'includes/class-store-orders.php';
		include_once MYD_PLUGIN_PATH . 'includes/class-store-formatting.php';
		include_once MYD_PLUGIN_PATH . 'includes/license/abstract-class-license-api.php';
		include_once MYD_PLUGIN_PATH . 'includes/license/interface-license-action.php';
		include_once MYD_PLUGIN_PATH . 'includes/license/class-license-manage-data.php';
		include_once MYD_PLUGIN_PATH . 'includes/license/class-license.php';
		include_once MYD_PLUGIN_PATH . 'includes/license/class-license-activate.php';
		include_once MYD_PLUGIN_PATH . 'includes/license/class-license-deactivate.php';
		include_once MYD_PLUGIN_PATH . 'includes/plugin-update/class-plugin-update.php';
		include_once MYD_PLUGIN_PATH . 'includes/class-currency.php';
		include_once MYD_PLUGIN_PATH . 'includes/l10n/class-countries.php';
		include_once MYD_PLUGIN_PATH . 'includes/l10n/class-country.php';
		include_once MYD_PLUGIN_PATH . 'includes/ajax/class-update-cart.php';
		include_once MYD_PLUGIN_PATH . 'includes/ajax/class-create-draft-order.php';
		include_once MYD_PLUGIN_PATH . 'includes/ajax/class-place-payment.php';
		include_once MYD_PLUGIN_PATH . 'includes/class-cart.php';
		include_once MYD_PLUGIN_PATH . 'includes/class-create-draft-order.php';
		include_once MYD_PLUGIN_PATH . 'includes/repositories/class-coupon-repository.php';
		include_once MYD_PLUGIN_PATH . 'includes/class-coupon.php';
		include_once MYD_PLUGIN_PATH . '/includes/class-create-draft-order.php';
		include_once MYD_PLUGIN_PATH . '/includes/class-custom-message-whatsapp.php';
	}

	/**
	 * Enqueu admin styles/scripts
	 *
	 * @since 1.2
	 * @return void
	 */
	public function enqueue_admin_scripts() {
		wp_register_script( 'myd-admin-scritps', MYD_PLUGN_URL . 'assets/js/admin/admin-scripts.min.js', [], MYD_CURRENT_VERSION, true );
		wp_enqueue_script( 'myd-admin-scritps' );

		wp_register_script( 'myd-admin-cf-media-library', MYD_PLUGN_URL . 'assets/js/admin/custom-fields/media-library.min.js', [], MYD_CURRENT_VERSION, true );
		wp_register_script( 'myd-admin-cf-repeater', MYD_PLUGN_URL . 'assets/js/admin/custom-fields/repeater.min.js', [], MYD_CURRENT_VERSION, true );

		wp_register_style( 'myd-admin-style', MYD_PLUGN_URL . 'assets/css/admin/admin-style.min.css', [], MYD_CURRENT_VERSION );
		wp_enqueue_style( 'myd-admin-style' );

		wp_register_script( 'myd-chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), MYD_CURRENT_VERSION, true );
	}

	/**
	 * Enqueue front end styles/scripts
	 *
	 * @since 1.2
	 * @return void
	 */
	public function enqueue_frondend_scripts() {
		wp_register_script( 'plugin_pdf', 'https://printjs-4de6.kxcdn.com/print.min.js', array(), MYD_CURRENT_VERSION, true );
		wp_register_style( 'plugin_pdf_css', 'https://printjs-4de6.kxcdn.com/print.min.css', array(), MYD_CURRENT_VERSION, true );

		wp_register_script( 'myd-create-order', MYD_PLUGN_URL . 'assets/js/order.min.js', array(), MYD_CURRENT_VERSION, true );
		wp_localize_script(
			'myd-create-order',
			'ajax_object',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'order_nonce' => wp_create_nonce( 'myd-create-order' ),
			)
		);

		wp_register_style( 'myd-delivery-frontend', MYD_PLUGN_URL . 'assets/css/delivery-frontend.min.css', array(), MYD_CURRENT_VERSION );
		wp_register_style( 'myd-order-panel-frontend', MYD_PLUGN_URL . 'assets/css/order-panel-frontend.min.css', array(), MYD_CURRENT_VERSION );
		wp_register_style( 'myd-track-order-frontend', MYD_PLUGN_URL . 'assets/css/track-order-frontend.min.css', array(), MYD_CURRENT_VERSION );

		/**
		 * Orders Panel
		 * TODO: refactor Jquery and merge scripts
		 */
		wp_register_script( 'myd-orders-panel', MYD_PLUGN_URL . 'assets/js/orders-panel/frontend.min.js', array(), MYD_CURRENT_VERSION, true );
		wp_localize_script(
			'myd-orders-panel',
			'order_ajax_object',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'myd-order-notification' ),
				'domain' => esc_attr( home_url() ),
			)
		);

		wp_register_script( 'myd-order-list-ajax', MYD_PLUGN_URL . 'assets/js/order-list-ajax.min.js', array( 'jquery' ), MYD_CURRENT_VERSION, true );
		/**
		 * END Orders Panel
		 */
	}

	/**
	 * Fix plugin path name error
	 *
	 * Solve problem caused in old version ipdate
	 *
	 * @since 1.9.4
	 */
	public function check_plugin_path() {
		if ( is_admin() ) {

			$current_path = MYD_PLUGIN_PATH;

			if ( strpos( $current_path, 'my-delivey-wordpress' ) !== false ) {

				if ( ! function_exists( 'deactivate_plugins' ) ) {
					require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				}

				$deactive = deactivate_plugins( 'my-delivey-wordpress/myd-delivery-pro.php' );
				if ( is_wp_error( $deactive ) ) {
					esc_html_e( 'Error to deactive, contato MyD Delivery support.', 'myd-delivery-pro' );
					return;
				}

				$new_path = str_replace( 'my-delivey-wordpress', 'myd-delivery-pro', $current_path );
				rename( $current_path, $new_path );

				wp_safe_redirect( site_url( '/wp-admin/plugins.php' ) );
				exit;
			}
		}
	}

	/**
	 * Update notice
	 *
	 * @since 1.9.4
	 * @return void
	 */
	public function update_notice_invalid_license( $plugin_data, $new_data ) {

		if ( empty( $new_data->package ) ) {
			printf(
				'<br><span><strong>%1s</strong> %2s.</span>',
				esc_html__( 'Important:', 'myd-delivery-pro' ),
				esc_html__( 'Update is not available because your license is invalid', 'myd-delivery-pro' )
			);
		}
	}

	/**
	 * Load update files
	 *
	 * @since 1.2
	 * @return void
	 */
	public function init_update_files() {
		$license = get_option( 'fdm-license' );
		$domain = site_url();
		$url_to_check = 'https://eduardovillao.me/evcode-checks/?action=get_metadata&slug=myd-delivery-pro&license=' . $license . '&domain=' . $domain . '';
		//MYD_PLUGIN_MAIN_FILE
		//'myd-delivery-pro'
	}

	/**
	 * Check if plugin is activated
	 *
	 * @since 1.9.4
	 * @return boolean
	 * @param string $plugin
	 */
	public function plugin_is_active( $plugin ) {
		return function_exists( 'is_plugin_active' ) ? is_plugin_active( $plugin ) : in_array( $plugin, (array) get_option( 'active_plugins', array() ), true );
	}

	/**
	 * Activation hook
	 *
	 * @since 1.9.6
	 * @return void
	 */
	public function activation() {

		flush_rewrite_rules();
	}

	/**
	 * Deactivation hook
	 *
	 * @since 1.9.6
	 * @return void
	 */
	public function deactivation() {

		flush_rewrite_rules();
	}
}
