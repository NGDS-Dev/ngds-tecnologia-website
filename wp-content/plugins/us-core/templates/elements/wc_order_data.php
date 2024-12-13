<?php defined( 'ABSPATH' ) or die( 'This script cannot be accessed directly.' );

/**
 * Output Order details
 * It is required the functionality of WooCommerce
 */

if ( ! class_exists( 'woocommerce' ) ) {
	return;
}

// Get the order data
$order_id = us_wc_get_order_id();
$order = wc_get_order( $order_id );

// Do not ouptput on the frontend if there is no order data
if ( ! $order AND ! usb_is_post_preview() ) {
	return;
}

$_atts['class'] = 'w-shop-order woocommerce-order';
$_atts['class'] .= ' type_' . $type;
$_atts['class'] .= isset( $classes ) ? $classes : '';

if ( $type == 'number' ) {
	$_atts['class'] .= ' style_' . $number_style;
}

if ( ! empty( $el_id ) ) {
	$_atts['id'] = $el_id;
}

?>
<div<?php echo us_implode_atts( $_atts ) ?>>
	<?php
	if ( $type == 'number' ) {

		// Dummy data for Live Builder
		if ( usb_is_post_preview() ) {
			$number = '0001';
			$date = wp_date( 'F j, Y' );
			$email = 'test@test.com';
			$total = '$99.00';
			$payment_method = strip_tags( us_translate( 'Cash on delivery', 'woocommerce' ) );
		} else {
			$number = $order->get_order_number();
			$date = wc_format_datetime( $order->get_date_created() );
			$email = $order->get_billing_email();
			$total = $order->get_formatted_order_total();
			$payment_method = wp_kses_post( $order->get_payment_method_title() );

			// Add the default WooCommerce action
			do_action( 'woocommerce_before_thankyou', $order->get_id() );
		} ?>

		<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">
			<li class="woocommerce-order-overview__order order">
				<?php echo esc_html( us_translate( 'Order number:', 'woocommerce' ) ); ?>
				<strong><?php echo $number; ?></strong>
			</li>
			<li class="woocommerce-order-overview__date date">
				<?php echo esc_html( us_translate( 'Date:', 'woocommerce' ) ); ?>
				<strong><?php echo $date; ?></strong>
			</li>
			<?php if ( usb_is_post_preview() OR ( is_user_logged_in() AND $order->get_user_id() === get_current_user_id() AND $order->get_billing_email() ) ) : ?>
				<li class="woocommerce-order-overview__email email">
					<?php echo esc_html( us_translate( 'Email:', 'woocommerce' ) ); ?>
					<strong><?php echo $email; ?></strong>
				</li>
			<?php endif; ?>
			<li class="woocommerce-order-overview__total total">
				<?php echo esc_html( us_translate( 'Total:', 'woocommerce' ) ); ?>
				<strong><?php echo $total; ?></strong>
			</li>
			<?php if ( usb_is_post_preview() OR $order->get_payment_method_title() ) : ?>
				<li class="woocommerce-order-overview__payment-method method">
					<?php echo esc_html( us_translate( 'Payment method:', 'woocommerce' ) ); ?>
					<strong><?php echo $payment_method; ?></strong>
				</li>
			<?php endif; ?>
		</ul>
		<?php
		// Payment Method Instructions
	} elseif ( $type == 'payment-instructions' ) {

		// Dummy data for Live Builder
		if ( usb_is_post_preview() ) {
			echo '<p>';
			echo esc_html( us_translate( 'Pay with cash upon delivery.', 'woocommerce' ) );
			echo '</p>';
		} else {
			do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() );
		}

		// Order Details
	} elseif ( $type == 'details' ) {

		// Dummy data for Live Biulder
		if ( usb_is_post_preview() ) {
			?>
			<section class="woocommerce-order-details">
				<h2 class="woocommerce-order-details__title"><?php echo esc_html( us_translate( 'Order details', 'woocommerce' ) ) ?></h2>
				<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
					<tbody>
						<tr class="woocommerce-table__line-item order_item">
							<td class="woocommerce-table__product-name product-name">
								<a href="http://impreza.local/product/dry-body-oil/"><?php echo esc_html( us_translate( 'Product / Variation title', 'woocommerce' ) ) ?></a> <strong class="product-quantity">×&nbsp;1</strong>
							</td>
							<td class="woocommerce-table__product-total product-total">
								<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>15.00</bdi></span>
							</td>
						</tr>
						<tr class="woocommerce-table__line-item order_item">
							<td class="woocommerce-table__product-name product-name">
								<a href="http://impreza.local/product/refreshing-cleanser-gel/"><?php echo esc_html( us_translate( 'Product / Variation title', 'woocommerce' ) ) ?></a> <strong class="product-quantity">×&nbsp;2</strong>
							</td>
							<td class="woocommerce-table__product-total product-total">
								<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>32.00</bdi></span>
							</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<th scope="row"><?php echo esc_html( us_translate( 'Subtotal:', 'woocommerce' ) ) ?></th>
							<td><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">$</span>47.00</span></td>
						</tr>
						<tr>
							<th scope="row"><?php echo esc_html( us_translate( 'Payment method:', 'woocommerce' ) ) ?></th>
							<td><?php echo esc_html( us_translate( 'Cash on delivery', 'woocommerce' ) ) ?></td>
						</tr>
						<tr>
							<th scope="row"><?php echo esc_html( us_translate( 'Total:', 'woocommerce' ) ) ?></th>
							<td><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">$</span>47.00</span></td>
						</tr>
					</tfoot>
				</table>
			</section>
			<section class="woocommerce-customer-details">
				<h2 class="woocommerce-column__title"><?php echo esc_html( us_translate( 'Billing address', 'woocommerce' ) ) ?></h2>
				<address>
					Sherlock Holmes<br>221b, Baker Street<br>London
					<p class="woocommerce-customer-details--phone">12345678</p>
					<p class="woocommerce-customer-details--email">test@test.com</p>
				</address>
			</section>
			<?php
			// Default WooCommerce template
		} else {
			woocommerce_order_details_table( $order_id );
		}

		// WooCommerce Default Actions for plugins compatibility
	} elseif ( $type == 'default_actions' ) {

		remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );
		do_action( 'woocommerce_thankyou', $order_id );
	}
	?>
</div>
