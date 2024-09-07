<?php

use MydPro\Includes\Store_Data;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<?php if ( $orders->have_posts() ) : ?>
	<?php $currency_simbol = Store_Data::get_store_data( 'currency_simbol' ); ?>
	<?php while ( $orders->have_posts() ) : ?>
		<?php $orders->the_post(); ?>
		<?php $postid = get_the_ID(); ?>
		<?php $date = get_post_meta( $postid, 'order_date', true ); ?>
		<?php $date = gmdate( 'd/m - H:i', strtotime( $date ) ); ?>
		<?php $status = get_post_meta( $postid, 'order_status', true ); ?>
		<?php $coupon = get_post_meta( $postid, 'order_coupon', true ); ?>
		<?php $change = get_post_meta( $postid, 'order_change', true ); ?>
		<?php $payment_type = get_post_meta( $postid, 'order_payment_type', true ); ?>
		<?php $payment_type = $payment_type === 'upon-delivery' ? __( 'Upon Delivery', 'myd-delivery-pro' ) : __( 'Payment Integration', 'myd-delivery-pro' ); ?>
		<?php $payment_status = get_post_meta( $postid, 'order_payment_status', true ); ?>
		<?php $payment_status_mapped = array(
			'waiting' => __( 'Waiting', 'myd-delivery-pro' ),
			'paid' => __( 'Pago', 'myd-delivery-pro' ),
			'failed' => __( 'Falhou', 'myd-delivery-pro' ),
		); ?>
		<?php $payment_status = $payment_status_mapped[ $payment_status ] ?? ''; ?>

		<div class="fdm-orders-full-items" id="content-<?php echo esc_attr( $postid ); ?>">
			<div>
				<div class="fdm-orders-items-order">
					<div class="fdm-order-list-items">
						<div class="fdm-order-list-items-type">
							<?php echo esc_html( get_post_meta( $postid, 'order_ship_method', true ) ); ?>
						</div>

						<div class="fdm-order-list-items-order-number">
							<?php esc_html_e( 'Order', 'myd-delivery-pro' ); ?> <?php echo esc_html( get_the_title( $postid ) ); ?>
						</div>

						<div class="fdm-order-list-items-date">
							<?php echo esc_html( $date ); ?>
						</div>

						<hr class="fdm-divider">

						<?php if ( ! empty( get_post_meta( $postid, 'order_ship_method', true ) ) ) : ?>
							<?php $table = get_post_meta( $postid, 'order_table', true ); ?>
							<?php $address = get_post_meta( $postid, 'order_address', true ); ?>

							<?php if ( ! empty( $table ) ) : ?>
								<div class="fdm-order-list-items-customer-name">
									<?php echo esc_html( get_post_meta( $postid, 'order_customer_name', true ) ); ?>
								</div>

								<div class="fdm-order-list-items-customer">
									<?php echo esc_html( get_post_meta( $postid, 'customer_phone', true ) ); ?>
								</div>

								<div class="fdm-order-list-items-customer">
									<?php echo esc_html__( 'Table', 'myd-delivery-pro' ) . ' ' . esc_html( get_post_meta( $postid, 'order_table', true ) ); ?>
								</div>;
							<?php endif; ?>

							<?php if ( ! empty( $address ) ) : ?>
								<div class="fdm-order-list-items-customer-name">
									<?php echo esc_html( get_post_meta( $postid, 'order_customer_name', true ) ); ?>
								</div>

								<div class="fdm-order-list-items-customer">
									<?php echo esc_html( get_post_meta( $postid, 'customer_phone', true ) ); ?>
								</div>

								<div class="fdm-order-list-items-customer">
									<?php echo esc_html( get_post_meta( $postid, 'order_address', true ) ) . ', ' . esc_html( get_post_meta( $postid, 'order_address_number', true ) ) . ' | ' . esc_html( get_post_meta( $postid, 'order_address_comp', true ) ); ?>
								</div>

								<div class="fdm-order-list-items-customer">
									<?php echo esc_html( get_post_meta( $postid, 'order_neighborhood', true ) ) . ' | ' . esc_html( get_post_meta( $postid, 'order_zipcode', true ) ); ?>
								</div>
							<?php endif; ?>

							<?php if ( empty( $address ) && empty( $table ) ) : ?>
								<div class="fdm-order-list-items-customer-name">
									<?php echo esc_html( get_post_meta( $postid, 'order_customer_name', true ) ); ?>
								</div>
								<div class="fdm-order-list-items-customer">
									<?php echo esc_html( get_post_meta( $postid, 'customer_phone', true ) ); ?>
								</div>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</div>

				<div class="fdm-orders-items-products">
					<div class="fdm-order-list-items">
						<?php $items = get_post_meta( $postid, 'myd_order_items', true ); ?>

						<?php if ( ! empty( $items ) ) : ?>
							<?php foreach ( $items as $value ) : ?>
								<div class="fdm-products-order-loop">
									<div class="fdm-order-list-items-product"><?php echo esc_html( $value['product_name'] ); ?></div>

									<?php if ( $value['product_extras'] !== '' ) : ?>
										<div class="fdm-order-list-items-product-extra"><?php echo esc_html( trim( $value['product_extras'] ) ); ?></div>
									<?php endif; ?>

									<?php if ( ! empty( $value['product_note'] ) ) : ?>
										<div class="fdm-order-list-items-customer"><?php echo esc_html__( 'Note', 'myd-delivery-pro' ) . ' ' . esc_html( $value['product_note'] ); ?></div>
									<?php endif; ?>

									<div class="fdm-order-list-items-product-extra"><?php echo esc_html( Store_Data::get_store_data( 'currency_simbol' ) . ' ' . $value['product_price'] ); ?></div>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>

						<hr class="fdm-divider">

						<div class="fdm-order-list-items-customer">
							<?php esc_html_e( 'Delivery', 'myd-delivery-pro' ); ?>:
							<?php echo esc_html( $currency_simbol ); ?> <?php echo esc_html( get_post_meta( $postid, 'order_delivery_price', true ) ); ?>
						</div>

						<?php if ( ! empty( $coupon ) ) : ?>
							<div class="fdm-order-list-items-customer">
								<?php esc_html_e( 'Coupon code', 'myd-delivery-pro' ); ?>:
								<?php echo esc_html( $coupon ); ?>
							</div>
						<?php endif; ?>

						<div class="fdm-order-list-items-customer-name">
							<?php esc_html_e( 'Total', 'myd-delivery-pro'); ?>:
							<?php echo esc_html( $currency_simbol ); ?> <?php echo esc_html( get_post_meta( $postid, 'order_total', true ) ); ?>
						</div>

						<div class="fdm-order-list-items-customer">
							<?php esc_html_e( 'Payment Type', 'myd-delivery-pro' ); ?>:
							<?php echo esc_html( $payment_type ); ?>
						</div>

						<div class="fdm-order-list-items-customer">
							<?php esc_html_e( 'Payment Method', 'myd-delivery-pro' ); ?>:
							<?php echo esc_html( get_post_meta( $postid, 'order_payment_method', true ) ); ?>
						</div>

						<div class="fdm-order-list-items-customer">
							<?php esc_html_e( 'Payment Status', 'myd-delivery-pro' ); ?>:
							<?php echo esc_html( $payment_status ); ?>
						</div>

						<?php if ( ! empty( $change ) ) : ?>
							<div class="fdm-order-list-items-customer">
								<?php esc_html_e( 'Change for', 'myd-delivery-pro' ); ?>:
								<?php echo esc_html( $change ); ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	<?php endwhile; ?>
	<?php \wp_reset_postdata(); ?>
<?php endif; ?>
