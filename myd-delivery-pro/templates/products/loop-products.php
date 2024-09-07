<?php

use MydPro\Includes\Store_Data;
use MydPro\Includes\Myd_Store_Formatting;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$postid = get_the_ID();
$image_id = get_post_meta( $postid, 'product_image', true );
$image_url = wp_get_attachment_image_url( $image_id, 'large' );
$box_shadow = get_option( 'myd-products-list-boxshadow' );
$product_price = get_post_meta( $postid, 'product_price', true );
$product_price = empty( $product_price ) ? 0 : $product_price;
$button_text = apply_filters( 'myd-product-loop-button-text', '+' );
$currency_simbol = Store_Data::get_store_data( 'currency_simbol' );
$is_available = get_post_meta( $postid, 'product_available', true );
$disabled_class = $is_available === 'not-available' ? 'myd-product-disabled' : '';
$price_label = get_post_meta( $postid, 'product_price_label', true );
$hide_price_class = $price_label === 'hide' ? 'myd-product-item__price--hide' : '';

?>
<article class="myd-product-item <?php echo esc_attr( $box_shadow ); ?> <?php echo esc_attr( $disabled_class ); ?>" itemscope itemtype="http://schema.org/Product" data-id="<?php echo esc_attr( $postid ); ?>">
	<?php if ( $is_available === 'not-available' ) : ?>
		<span class="myd-product-item__not-available"><?php esc_html_e( 'Not available', 'myd-delivery-pro' ); ?></span>
		<div class="myd-product-item__not-available-overlay"></div>
	<?php endif; ?>
	<div class="myd-product-item__content">
		<h3 class="myd-product-item__title" itemprop="name"><?php echo esc_html( get_the_title() ); ?></h3>
		<p class="myd-product-item__desc" itemprop="description"><?php echo esc_html( get_post_meta( $postid, 'product_description', true ) ); ?></p>

		<div class="myd-product-item__actions">
			<span class="myd-product-item__price <?php echo esc_attr( $hide_price_class ); ?>" itemprop="price">
				<?php if ( $price_label === 'show' || $price_label === '' ) : ?>
					<?php echo esc_html( $currency_simbol . ' ' . Myd_Store_Formatting::format_price( get_post_meta( $postid, 'product_price', true ) ) ); ?>
				<?php endif; ?>

				<?php if ( $price_label === 'from' ) : ?>
					<?php echo esc_html__( 'From', 'myd-delivery-pro' ); ?> <?php echo esc_html( $currency_simbol . ' ' . Myd_Store_Formatting::format_price( get_post_meta( $postid, 'product_price', true ) ) ); ?>
				<?php endif; ?>

				<?php if ( $price_label === 'consult' ) : ?>
					<?php echo esc_html__( 'By Consult', 'myd-delivery-pro' ); ?>
				<?php endif; ?>
			</span>
		</div>
	</div>

	<div class="myd-product-item__img" data-image="<?php echo esc_attr( $image_url ); ?>">
		<?php echo wp_get_attachment_image( $image_id, 'medium', false, [ 'class' => 'myd-product-item-img attachment-medium size-medium', 'alt' => 'MyD Delivery Product Image' ] ); ?>
	</div>
</article>

<hr class="myd-product-item__divider">

<?php if ( $is_available !== 'not-available' ) : ?>
	<div
		class="fdm-popup-product-init myd-hide-element"
		id="popup-<?php echo \esc_attr( $postid ); ?>"
	>
		<div class="myd-product-popup__wrapper">
			<div class="myd-product-popup__image-container">
				<span class=fdm-popup-close-btn>
					<svg width="22px" height="22px" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg">
						<path fill="#000000" d="M104.704 338.752a64 64 0 0 1 90.496 0l316.8 316.8 316.8-316.8a64 64 0 0 1 90.496 90.496L557.248 791.296a64 64 0 0 1-90.496 0L104.704 429.248a64 64 0 0 1 0-90.496z"/>
					</svg>
				</span>

				<div class="myd-product-popup__img" data-image="<?php echo esc_attr( $image_url ); ?>">
					<?php echo \wp_get_attachment_image( $image_id, 'medium', false, [ 'class' => 'myd-product-popup-img attachment-medium size-medium', 'alt' => 'MyD Delivery Product Image' ] ); ?>
				</div>
			</div>

			<div class="fdm-popup-product-content">
				<h3 class="myd-product-popup__title">
					<?php echo \esc_html( get_the_title() ); ?>
				</h3>
				<p class="myd-product-popup__description">
					<?php echo \esc_html( get_post_meta( $postid, 'product_description', true ) ); ?>
				</p>

				<p class="myd-product-popup__price">
					<?php echo esc_html( $currency_simbol . ' ' . Myd_Store_Formatting::format_price( get_post_meta( $postid, 'product_price', true ) ) ); ?>
				</p>

				<div class="myd-product-popup-extras">
					<div class="fdm-product-add-extras">
						<?php echo $this->format_product_extra( $postid ); ?>
					</div>

					<input type="text" id="myd-product-note-<?php echo esc_attr( $postid ); ?>" placeholder="<?php echo esc_html__( 'any special requests?', 'myd-delivery-pro' ); ?>" class="myd-product-popup__note">
				</div>
			</div>

			<div class="fdm-popup-product-action">
				<div class="fdm-popup-product-content-qty">
					<div class="fdm-click-minus">-</div>
					<input type="number" class="fdm-popup-input-text fmd-item-qty" value="1" min="1" pattern="\d*">
					<div class="fdm-click-plus">+</div>
				</div>

				<div class="fdm-popup-product-content-add-cart">
					<a
						class="fdm-add-to-cart-popup"
						id="<?php echo esc_attr( $postid ); ?>"
						data-name="<?php echo esc_attr( get_the_title() ); ?>"
						data-price="<?php echo Myd_Store_Formatting::format_price( $product_price ); ?>"
						data-image="<?php echo esc_attr( $image_url ); ?>"
						data-text="<?php esc_attr_e( 'Add to bag', 'myd-delivery-pro' ); ?>"
					>
						<span class="myd-add-to-cart-button__text">
							<?php esc_html_e( 'Add to bag', 'myd-delivery-pro' ); ?>
						</span>

						<span class="myd-add-to-cart-button__icon">
							<svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path fill-rule="evenodd" clip-rule="evenodd" d="M12.0001 2.75C10.7574 2.75 9.75006 3.75736 9.75006 5V5.25447C10.1676 5.24999 10.6183 5.25 11.1053 5.25H12.8948C13.3819 5.25 13.8326 5.24999 14.2501 5.25447V5C14.2501 3.75736 13.2427 2.75 12.0001 2.75ZM15.7501 5.30694V5C15.7501 2.92893 14.0711 1.25 12.0001 1.25C9.929 1.25 8.25006 2.92893 8.25006 5V5.30694C8.11506 5.31679 7.98479 5.32834 7.85904 5.34189C6.98068 5.43657 6.24614 5.63489 5.59385 6.08197C5.3695 6.23574 5.15877 6.40849 4.96399 6.59833C4.39766 7.15027 4.05914 7.83166 3.79405 8.67439C3.53667 9.49258 3.32867 10.5327 3.06729 11.8396L3.04822 11.935C2.67158 13.8181 2.37478 15.302 2.28954 16.484C2.20244 17.6916 2.32415 18.7075 2.89619 19.588C3.08705 19.8817 3.30982 20.1534 3.56044 20.3982C4.31157 21.1318 5.28392 21.4504 6.48518 21.6018C7.66087 21.75 9.17418 21.75 11.0946 21.75H12.9055C14.826 21.75 16.3393 21.75 17.5149 21.6018C18.7162 21.4504 19.6886 21.1318 20.4397 20.3982C20.6903 20.1534 20.9131 19.8817 21.1039 19.588C21.676 18.7075 21.7977 17.6916 21.7106 16.484C21.6254 15.3021 21.3286 13.8182 20.9519 11.9351L20.9328 11.8396C20.6715 10.5327 20.4635 9.49259 20.2061 8.67439C19.941 7.83166 19.6025 7.15027 19.0361 6.59833C18.8414 6.40849 18.6306 6.23574 18.4063 6.08197C17.754 5.63489 17.0194 5.43657 16.1411 5.34189C16.0153 5.32834 15.8851 5.31679 15.7501 5.30694ZM8.01978 6.83326C7.27307 6.91374 6.81176 7.06572 6.44188 7.31924C6.28838 7.42445 6.1442 7.54265 6.01093 7.67254C5.68979 7.98552 5.45028 8.40807 5.22492 9.12449C4.99463 9.85661 4.80147 10.8172 4.52967 12.1762C4.14013 14.1239 3.8633 15.5153 3.78565 16.5919C3.70906 17.6538 3.83838 18.2849 4.15401 18.7707C4.2846 18.9717 4.43702 19.1576 4.60849 19.3251C5.02293 19.7298 5.61646 19.9804 6.67278 20.1136C7.74368 20.2486 9.1623 20.25 11.1486 20.25H12.8515C14.8378 20.25 16.2564 20.2486 17.3273 20.1136C18.3837 19.9804 18.9772 19.7298 19.3916 19.3251C19.5631 19.1576 19.7155 18.9717 19.8461 18.7707C20.1617 18.2849 20.2911 17.6538 20.2145 16.5919C20.1368 15.5153 19.86 14.1239 19.4705 12.1762C19.1987 10.8173 19.0055 9.85661 18.7752 9.12449C18.5498 8.40807 18.3103 7.98552 17.9892 7.67254C17.8559 7.54265 17.7118 7.42445 17.5582 7.31924C17.1884 7.06572 16.7271 6.91374 15.9803 6.83326C15.2173 6.75101 14.2374 6.75 12.8515 6.75H11.1486C9.76271 6.75 8.78285 6.75101 8.01978 6.83326ZM8.92103 14.2929C9.31156 14.1548 9.74006 14.3595 9.87809 14.7501C10.1873 15.625 11.0218 16.25 12.0003 16.25C12.9787 16.25 13.8132 15.625 14.1224 14.7501C14.2605 14.3595 14.6889 14.1548 15.0795 14.2929C15.47 14.4309 15.6747 14.8594 15.5367 15.2499C15.0222 16.7054 13.6342 17.75 12.0003 17.75C10.3663 17.75 8.97827 16.7054 8.46383 15.2499C8.3258 14.8594 8.53049 14.4309 8.92103 14.2929Z" fill="#fff"/>
							</svg>
						</span>
					</a>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>