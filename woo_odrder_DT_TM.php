function start_session_ads($key, $value) {
	if(session_id() == '')
     session_start(); 

		$_SESSION[$key] = $value;

}
start_session_ads();

add_action(‘wp_logout’, ‘end_session’);
add_action(‘wp_login’, ‘end_session’);

function end_session() {
	session_destroy ();
}
add_action( 'wp_head', 'woocommerce_order_received_tag_manager' );

function woocommerce_order_received_tag_manager() {

	if ( is_checkout() && !empty( is_wc_endpoint_url('order-received') ) ) {

		$order_id = empty($_GET[ 'order' ]) ? ($GLOBALS[ 'wp' ]->query_vars[ 'order-received' ] ? $GLOBALS[ 'wp' ]->query_vars[ 'order-received' ] : 0) : absint($_GET[ 'order' ]);
        $order_id_filtered = apply_filters('woocommerce_thankyou_order_id', $order_id);
        if ('' != $order_id_filtered) {
            $order_id = $order_id_filtered;
        }

        $order = new WC_Order($order_id);

        $dataLayer['transactionId']		= (string) $order->get_order_number();
        $dataLayer['transactionTotal']	= $order->get_total();

        if ((int)$_SESSION['transactionId'] != (int)$dataLayer['transactionId']) {

		    echo "<script>dataLayer = [{'transactionId': '".$dataLayer['transactionId']."','transactionTotal': '".$dataLayer['transactionTotal']."','transactionProducts': [";
	 		
	 		foreach ($order->get_items() as $item) {

	 			$product     = $item->get_product();
	            $product_id  = $product->get_id();
	            $product_sku = $product->get_sku();

	            $product_categories = get_the_terms($product_id, 'product_cat');

	            if ((is_array($product_categories)) && (count($product_categories) > 0)) {
	                $product_cat = array_pop($product_categories);
	                $product_cat = $product_cat->name;
	            } else {
	                $product_cat = '';
	            }

	            $productId = $product_sku ? $product_sku : $product_id;

	            $product_price = $order->get_item_total($item);

	            $product_data  = [
	                'name'     => htmlentities($item['name'], ENT_QUOTES, 'utf-8'),
	                'sku'      => (string) $product_sku ? $product_sku : $product_id,
	                'category' => htmlentities($product_cat, ENT_QUOTES, 'utf-8'),
	                'price'    => (int) $product_price,
	                'quantity' => (int) $item['qty']
	            ];

		    	echo "{'sku': '".$product_data['sku']."','name': '".$product_data['name']."','category': '".$product_data['category']."','price': '".$product_data['price']."','quantity': '".$product_data['quantity']."'}";

	 		}

			echo "]}];</script>";

			start_session_ads('transactionId', $dataLayer['transactionId']);

		}

	}

}
