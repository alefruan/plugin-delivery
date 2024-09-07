<?php

namespace MydPro\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * TODO: refactor the class
 */
class Myd_Orders_Front_Panel {
	/**
	 * Queried orders object
	 *
	 * @var object
	 */
	protected $orders_object;

	/**
	 * Default args
	 *
	 * @var array
	 */
	protected $default_args = [
		'post_type' => 'mydelivery-orders',
		'posts_per_page' => 30,
		'no_found_rows' => true,
		'meta_query' => [
			'relation' => 'OR',
			[
				'key'     => 'order_status',
				'value'   => 'new',
				'compare' => '=',
			],
			[
				'key'     => 'order_status',
				'value'   => 'confirmed',
				'compare' => '=',
			],
			[
				'key'     => 'order_status',
				'value'   => 'in-delivery',
				'compare' => '=',
			],
			[
				'key'     => 'order_status',
				'value'   => 'done',
				'compare' => '=',
			],
			[
				'key'     => 'order_status',
				'value'   => 'waiting',
				'compare' => '=',
			],
		]
	];

	/**
	 * Construct the class
	 */
	public function __construct () {
		add_shortcode( 'mydelivery-orders', [ $this, 'show_orders_list'] );
		add_action( 'wp_ajax_reload_orders', [ $this, 'ajax_reload_orders'] );
		add_action( 'wp_ajax_nopriv_reload_orders', [ $this, 'ajax_reload_orders'] );
		add_action( 'wp_ajax_update_orders', [ $this, 'update_orders'] );
		add_action( 'wp_ajax_nopriv_update_orders', [ $this, 'update_orders'] );
		add_action( 'wp_ajax_print_orders', [ $this, 'ajax_print_order'] );
		add_action( 'wp_ajax_nopriv_print_orders', [ $this, 'ajax_print_order'] );
	}

	/**
	 * Output template panel
	 *
	 * TODO: move to new class
	 *
	 * @return void
	 * @since 1.9.5
	 */
	public function show_orders_list () {
		if( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
			\wp_enqueue_script( 'myd-orders-panel' );
			\wp_enqueue_script( 'myd-order-list-ajax' );
			\wp_enqueue_style( 'myd-order-panel-frontend' );
			\wp_enqueue_script( 'plugin_pdf' );
			\wp_enqueue_style( 'plugin_pdf_css' );

			/**
			 * Query orders
			 */
			$orders = new Myd_Store_Orders( $this->default_args );
			$orders = $orders->get_orders_object();
			$this->orders_object = $orders;

			/**
			 * Include templates
			 */
			ob_start();
			include MYD_PLUGIN_PATH . 'templates/order/panel.php';
			return ob_get_clean();
		} else {
			return '<div class="fdm-not-logged">' . __( 'Sorry, you dont have access to this page.', 'myd-delivery-pro' ) . '</div>';
		}
	}

	/**
	 * Loop orders list
	 *
	 * TODO: move to new class
	 *
	 * @return void
	 * @since 1.9.5
	 */
	public function loop_orders_list () {
		$orders = $this->orders_object;

		ob_start();
		include MYD_PLUGIN_PATH . 'templates/order/order-list.php';
		return ob_get_clean();
	}

	/**
	 * Orders content
	 *
	 * TODO: move to new class
	 *
	 * @return void
	 * @since 1.9.5
	 */
	public function loop_orders_full () {
		$orders = $this->orders_object;

		ob_start();
		include MYD_PLUGIN_PATH . 'templates/order/order-content.php';
		return ob_get_clean();
	}

	/**
	 * Orders print
	 *
	 * TODO: move to new class
	 *
	 * @return void
	 * @since 1.9.5
	 */
	public function loop_print_order () {
		$orders = $this->orders_object;

		ob_start();
		include MYD_PLUGIN_PATH . 'templates/order/print.php';
		return ob_get_clean();
	}

	/**
	 * Count orders
	 *
	 * @return void
	 */
	public function count_orders() {
		$orders = $this->query_orders();
		$orders = $orders->get_posts();

		return count( $orders );
	}

	/**
	 * Ajax class items
	 *
	 * @return void
	 */
	public function ajax_reload_orders() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'myd-order-notification' ) ) {
			echo wp_json_encode(
				array(
					'error' => 'Security validation failed.',
				)
			);
			exit;
		}

		$order_id = sanitize_text_field( $_REQUEST['id'] );
		$order_action = sanitize_text_field( $_REQUEST['order_action'] );
		update_post_meta( $order_id, 'order_status', $order_action );
		if ( empty( $this->orders_object ) ) {
			/**
			 * Query orders
			 */
			$orders = new Myd_Store_Orders( $this->default_args );
			$orders = $orders->get_orders_object();
			$this->orders_object = $orders;
		}

		echo wp_json_encode( array(
			'loop' => $this->loop_orders_list(),
			'full' => $this->loop_orders_full(),
		));

		exit;
	}

	/**
	 * Ajax to reload order after update (new order)
	 *
	 * @return void
	 */
	public function update_orders() {
		$nonce = $_REQUEST['nonce'] ?? null;
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'myd-order-notification' ) ) {
			die( esc_html__( 'Ops! Security check failed.', 'my-delivey-wordpress' ) );
		} else {
			if ( empty( $this->orders_object ) ) {
				/**
				 * Query orders
				 */
				$orders = new Myd_Store_Orders( $this->default_args );
				$orders = $orders->get_orders_object();
				$this->orders_object = $orders;
			}

			echo wp_json_encode( array(
				'loop' => $this->loop_orders_list(),
				'full' => $this->loop_orders_full(),
				'print' => $this->loop_print_order(),
			));

			exit;
		}
	}
}

new Myd_Orders_Front_Panel();
