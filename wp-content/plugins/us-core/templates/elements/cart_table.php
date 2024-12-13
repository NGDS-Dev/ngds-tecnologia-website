<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Output Cart Table
 * Note: All classes and key elements from WooCommerce are retained
 *
 * @var boolean $thumbnail - Show product Thumbnail
 * @var string|int $thumbnail_width - Thumbnail Width
 * @var string $qty_btn_size - Size of Quantity control
 * @var string $removing_link - Show removing link
 * @var string $qty_btn_style - Quantity Buttons Style
 * @var boolean $table_head - Show table labels
 * @var boolean $price_before_qty - Show price before quantity
 * @var string $subtotal_size - Size of subtotal price
 *
 * @see https://github.com/woocommerce/woocommerce/blob/5.8.0/templates/cart/cart.php
 */

if ( ! class_exists( 'woocommerce' ) ) {
	return;
} elseif ( ! usb_is_post_preview() ) {
	if ( function_exists( 'is_cart' ) AND ! is_cart() ) {
		return;
	}

	if ( is_null( WC()->cart ) ) {
		return;
	}

	// Check cart items are valid.
	do_action( 'woocommerce_check_cart_items' );

	// Calc totals.
	WC()->cart->calculate_totals();

	if ( ! is_null( WC()->cart ) AND WC()->cart->is_empty() ) {
		wc_get_template( 'cart/cart-empty.php' );
		return;
	}
}

$_atts['class'] = 'w-cart-table woocommerce-cart-form';
$_atts['class'] .= ' table-lines_' . $table_lines;
$_atts['class'] .= ' qty-btn-style_' . $qty_btn_style;
$_atts['class'] .= ' removing-link_' . $removing_link;
$_atts['class'] .= isset( $classes ) ? $classes : '';

$_atts['action'] = wc_get_cart_url();
$_atts['method'] = 'post';
$_atts['style'] = '';

// Set sizes if set
if ( $thumbnail_width ) {
	$_atts['style'] .= sprintf( '--thumbnail-width:%s;', $thumbnail_width );
}
if ( $qty_btn_size ) {
	$_atts['style'] .= sprintf( '--qty-btn-size:%s;', $qty_btn_size );
}
if ( $subtotal_size ) {
	$_atts['style'] .= sprintf( '--subtotal-size:%s;', $subtotal_size );
}

if ( $valign_middle ) {
	$_atts['class'] .= ' valign_middle';
}

if ( ! empty( $el_id ) ) {
	$_atts['id'] = $el_id;
}

// The control buttons to quantity
$qty_btn_minus = '<input type="button" value="-" class="minus" disabled>';
$qty_btn_plus = '<input type="button" value="+" class="plus">';

// Remove unnecessary actions before callin
remove_action( 'woocommerce_before_cart', 'woocommerce_output_all_notices', 10 );
do_action( 'woocommerce_before_cart' );
?>
<form<?php echo us_implode_atts( $_atts ) ?>>
	<?php do_action( 'woocommerce_before_cart_table' ); ?>

	<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
		<?php if ( $table_head ): ?>
			<thead>
			<tr>
				<th class="product-name"<?php if ( $thumbnail ) {
					echo ' colspan="2"';
				} ?>><?php echo esc_html( us_translate( 'Product', 'woocommerce' ) ); ?></th>
				<?php if ( $price_before_qty ): ?>
					<th class="product-price"><?php echo esc_html( us_translate( 'Price', 'woocommerce' ) ); ?></th>
				<?php endif; ?>
				<th class="product-quantity"><?php echo esc_html( us_translate( 'Quantity', 'woocommerce' ) ); ?></th>
				<th class="product-subtotal"><?php echo esc_html( us_translate( 'Subtotal', 'woocommerce' ) ); ?></th>
				<?php if ( $removing_link === 'after_subtotal' ): ?>
					<th class="product-remove"></th>
				<?php endif; ?>
			</tr>
			</thead>
		<?php endif; ?>
		<tbody>
		<?php do_action( 'woocommerce_before_cart_contents' ); ?>

		<?php
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			if (
				$_product
				AND $_product->exists()
				AND $cart_item['quantity'] > 0
				AND apply_filters( 'woocommerce_cart_item_visible', TRUE, $cart_item, $cart_item_key )
			) {
				$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
				
				// The remove product link
				$item_remove_link = sprintf(
					'<a href="%s" class="remove" aria-label="%s" title="%s" data-product_id="%s" data-product_sku="%s"><span>%s</span></a>',
					esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
					esc_html( us_translate( 'Remove this item', 'woocommerce' ) ),
					esc_html( us_translate( 'Remove this item', 'woocommerce' ) ),
					esc_attr( $product_id ),
					esc_attr( $_product->get_sku() ),
					esc_html( us_translate( 'Remove', 'woocommerce' ) )
				);
				$item_remove_link = apply_filters( 'woocommerce_cart_item_remove_link', $item_remove_link, $cart_item_key );
				?>
				<tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

					<?php if ( $thumbnail ): ?>
						<td class="product-thumbnail">
							<?php
							$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
							if ( ! $product_permalink ) {
								echo $thumbnail;
							} else {
								printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail );
							}
							?>
						</td>
					<?php endif; ?>

					<td class="product-name" data-title="<?php echo esc_attr( us_translate( 'Product', 'woocommerce' ) ); ?>">
						<?php
						if ( ! $product_permalink ) {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;' );
						} else {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
						}

						do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );

						// Meta data.
						echo wc_get_formatted_cart_item_data( $cart_item );

						// Backorder notification.
						if (
							$_product->backorders_require_notification()
							AND $_product->is_on_backorder( $cart_item['quantity'] )
						) {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html( us_translate( 'Available on backorder', 'woocommerce' ) ) . '</p>', $product_id ) );
						}
						?>
					</td>
					<?php if ( $price_before_qty ): ?>
						<td class="product-price" data-title="<?php echo esc_attr( us_translate( 'Price', 'woocommerce' ) ); ?>">
							<?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); ?>
						</td>
					<?php endif; ?>

					<td class="product-quantity" data-title="<?php echo esc_attr( us_translate( 'Quantity', 'woocommerce' ) ); ?>">
						<?php
						if ( $_product->is_sold_individually() ) {
							$product_qty = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
						} else {
							$product_qty = woocommerce_quantity_input(
								array(
									'input_name' => "cart[{$cart_item_key}][qty]",
									'input_value' => $cart_item['quantity'],
									'max_value' => $_product->get_max_purchase_quantity(),
									'min_value' => '0',
									'product_name' => $_product->get_name(),
								),
								$_product,
								/* echo */ FALSE
							);

							// Enable the Minus button if quanity is more than 1
							if ( $cart_item['quantity'] > 1 ) {
								$qty_btn_minus = str_replace( ' disabled', '', $qty_btn_minus );
							}

							// Add control buttons to quantity
							$product_qty = preg_replace( '/(.*)(<input\s+[^>]+>)(.*)/s', "$1" . $qty_btn_minus . "$2" . $qty_btn_plus . "$3", $product_qty, /* limit */ 1 );
						}
						?>
						<?php echo apply_filters( 'woocommerce_cart_item_quantity', $product_qty, $cart_item_key, $cart_item ); ?>
						
						<?php if ( $removing_link === 'below_qty' ): ?>
							<span class="product-remove"><?php echo $item_remove_link ?></span>
						<?php endif; ?>
					</td>

					<td class="product-subtotal" data-title="<?php echo esc_attr( us_translate( 'Subtotal', 'woocommerce' ) ); ?>">
						<?php
						echo apply_filters(
							'woocommerce_cart_item_subtotal',
							WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ),
							$cart_item,
							$cart_item_key
						);
						?>
					</td>
					<?php if ( $removing_link === 'after_subtotal' ): ?>
						<td class="product-remove"><?php echo $item_remove_link ?></td>
					<?php endif; ?>
				</tr>
				<?php
			}
		}
		?>

		<?php if ( usb_is_post_preview() AND WC()->cart->is_empty() ): ?>
			<?php for( $i = 1; $i <= 2; $i++ ) { ?>
			<!-- Begin placeholder cart item -->
			<tr class="woocommerce-cart-form__cart-item cart_item">
				<?php if ( $thumbnail ): ?>
					<td class="product-thumbnail">
						<?php echo us_get_img_placeholder(); ?>
					</td>
				<?php endif; ?>
				<td class="product-name">
					<a href="return false"><?php echo esc_html( us_translate( 'Product name', 'woocommerce' ) ) . ' ' . $i; ?></a>
				</td>
				<?php if ( $price_before_qty ): ?>
					<td class="product-price">
						<span class="woocommerce-Price-amount amount">
							<bdi>
								<span class="woocommerce-Price-currencySymbol">$</span>100.00
							</bdi>
						</span>
					</td>
				<?php endif; ?>
				<td class="product-quantity">
					<div class="quantity">
						<?php echo $qty_btn_minus ?>
						<input type="number" class="input-text qty text" value="1">
						<?php echo $qty_btn_plus ?>
					</div>
					<?php if ( $removing_link === 'below_qty' ): ?>
						<span class="product-remove">
							<a href="return false" class="remove"><span><?php echo esc_html( us_translate( 'Remove', 'woocommerce' ) ) ?></span></a>
						</span>
					<?php endif; ?>
				</td>
				<td class="product-subtotal">
					<span class="woocommerce-Price-amount amount">
						<bdi>
							<span class="woocommerce-Price-currencySymbol">$</span>100.00
						</bdi>
					</span>
				</td>
				<?php if ( $removing_link === 'after_subtotal' ): ?>
					<td class="product-remove">
						<a href="return false" class="remove"><span><?php echo esc_html( us_translate( 'Remove', 'woocommerce' ) ) ?></span></a>
					</td>
				<?php endif; ?>
			</tr>
			<!-- End placeholder cart item -->
			<?php } ?>
		<?php endif; ?>

		<?php do_action( 'woocommerce_cart_contents' ); ?>
		<?php do_action( 'woocommerce_cart_actions' ); ?>
		<?php do_action( 'woocommerce_after_cart_contents' ); ?>

		</tbody>
	</table>
	<?php do_action( 'woocommerce_after_cart_table' ); ?>
	<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>

	<?php // Hidden group of elements for the ability to run standard cart handlers ?>
	<input type="hidden" name="coupon_code" id="coupon_code">
	<input type="hidden" name="us_cart_quantity">
	<button type="submit" name="apply_coupon" value="<?php echo esc_attr( us_translate( 'Apply coupon', 'woocommerce' ) ); ?>" style="display:none"></button>
	<button type="submit" name="update_cart" value="<?php echo esc_attr( us_translate( 'Update cart', 'woocommerce' ) ); ?>" style="display:none"></button>

</form>

<?php // NOTE: Implement the output of the commented out hooks! ?>
<?php // do_action( 'woocommerce_before_cart_collaterals' ); ?>
<!-- <div class="cart-collaterals">
	<?php
		// remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cart_totals', 10 );
		// do_action( 'woocommerce_cart_collaterals' );
	?>
</div>-->
<?php // do_action( 'woocommerce_after_cart' ); ?>
