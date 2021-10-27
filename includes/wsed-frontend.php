<?php
	defined('ABSPATH') or die('Suspicious activities detected!');

	class Woo_Set_Event_Date_Frontend{

		public function __construct(){
			// Display event dates before Add To Cart button
			add_action('woocommerce_before_add_to_cart_button', array($this, 'wsed_before_add_to_cart_button'), 10);
			
			// Change Add To Cart button on shop page
			add_filter('woocommerce_loop_add_to_cart_link', array($this, 'wsed_loop_add_to_cart_link'), 10, 2);
			
			// Product sorting
			add_filter('woocommerce_get_catalog_ordering_args', array($this, 'wsed_get_catalog_ordering_args'));
			
			// Add metadata to item for cart
			add_filter('woocommerce_add_cart_item_data', array($this, 'wsed_add_cart_item_data'), 10, 3);
			
			// User place order
			add_filter('woocommerce_checkout_create_order', array($this, 'wsed_checkout_create_order'), 10, 2);
			
			// Display event date on cart page
			add_filter('woocommerce_get_item_data', array($this, 'wsed_get_item_data'), 10, 2);
			
			// Display event date on checkout page
			add_action('woocommerce_checkout_create_order_line_item', array($this, 'wsed_checkout_create_order_line_item'), 10, 4);
			
			// Add wsed data to order item
			//add_action('woocommerce_new_order_item', array($this, 'wsed_new_order_item'), 10, 3);
			
			// Set product max. quantity
			add_filter('woocommerce_quantity_input_args', array($this, 'wsed_quantity_input_args'), 10, 2);
			
			// Load styles
			add_action('wp_footer', array($this, 'wsed_style'));
		}

		function wsed_before_add_to_cart_button(){
			global $product;

			$product_id = $product->get_id();	    	
			$isDate = false;
			$displayTitle = true;

			$date = isset($_POST['wsed_date_holder']) ? $_POST['wsed_date_holder'] : get_post_meta($product_id, 'wsed_event_date_1', true);
			$date_id = isset($_POST['wsed_date_id_holder']) ? $_POST['wsed_date_id_holder'] : 1;

			for($i=1; $i<10; $i++){
				if(!empty(get_post_meta($product_id, 'wsed_event_date_' . $i, true)) && get_post_meta($product_id, 'wsed_event_date_' . $i, true) != ""){

					if($displayTitle)
						echo "<p class='wsed_event_date_title'>" . __('Choose a date', 'woo-set-event-date') . ":</p>";

					$wsed_event_date = get_post_meta($product_id, 'wsed_event_date_' . $i, true); 
					$wsed_event_display = get_post_meta($product_id, 'wsed_event_display_' . $i, true); 
					$wsed_event_spots =get_post_meta($product_id, 'wsed_event_spots_' . $i, true); 

					$class = ($date == $wsed_event_date ? "active" : "");

					echo '<div class="wsed_event_date_holder"><div class="wsed_event_date ' . $class . '" data-id="' . $i . '" data-value="' . $wsed_event_date . '" onclick="changeDate(this);">' . ($wsed_event_display != "" ? $wsed_event_display : $wsed_event_date) . '</div>';

					echo '<div class="wsed_event_spots" >' . __('Available spots', 'woo-set-event-date') .": " . $wsed_event_spots . '</div></div>';

					$isDate = true;
					$displayTitle = false;
				}else{
					break;
				}
			}

			if(!$isDate){
				echo '<p class="wsed_event_spots">' . __('No dates available','woo-set-event-date') . '</p>';
				add_filter( 'woocommerce_is_purchasable', '__return_false');
			}else{
				add_filter( 'woocommerce_is_purchasable', '__return_true');
			}

			echo "<script>
					function changeDate(dateElement){
						var date = dateElement.dataset.value;
						var date_id = dateElement.dataset.id;

						document.getElementById('wsed_date_holder').value = date;
						document.getElementById('wsed_date_id_holder').value = date_id;

						var elements = document.getElementsByClassName('wsed_event_date');
						for (var i = 0; i < elements.length; i++) {
							elements[i].classList.remove('active');
						}
						dateElement.classList.add('active');
					}
				</script>";
			
			echo "<input type='hidden' id='wsed_date_holder' name='wsed_date_holder' value='" . $date . "'>";
			echo "<input type='hidden' id='wsed_date_id_holder' name='wsed_date_id_holder' value='" . $date_id . "'>";
		}

		function wsed_loop_add_to_cart_link($button, $product){
			global $product;
			global $wpdb;

			$product_id = $product->get_id();

			$isDate = false;
			$nowDate = time();
			for($i=1; $i<10; $i++){
				if(get_post_meta($product_id, 'wsed_event_date_' . $i, true) != "" && get_post_meta($product_id, 'wsed_event_spots_' . $i, true) != ""){

					$wsed_event_spots = get_post_meta($product_id, 'wsed_event_spots_' . $i, true); 
					$date = strtotime(get_post_meta($product_id, 'wsed_event_date_' . $i, true));

					if($date != "" && $nowDate <= $date && $wsed_event_spots > 0){
						$isDate = true;
					}else{
						update_post_meta($product_id, 'wsed_event_date_' . $i, "");
						update_post_meta($product_id, 'wsed_event_spots_' . $i, "");
					}
				}else{
					break;
				}
			}

			if($isDate){
				return '<a class="button product_type_simple add_to_cart_button ajax_add_to_cart" href="' . $product->get_permalink() . '">' . __('Show dates', 'woo-set-event-date') . '</a>';
			}else{
				return '<a class="button product_type_simple add_to_cart_button ajax_add_to_cart" href="' . $product->get_permalink() . '">' . __('No dates available', 'woo-set-event-date') . '</a>';
				update_post_meta($product_id, 'wsed_event_is_date',  0);
			}

			return $button;
		}

		function wsed_get_catalog_ordering_args($sort_args){	
			global $wp_query;
			$cat_name = $wp_query->get_queried_object()->slug;
			$sort_args['orderby'] = 'meta_value';
			$sort_args['order'] = 'desc';
			$sort_args['meta_key'] = 'wsed_event_is_date';
			return $sort_args;
		}

		function wsed_add_cart_item_data($cart_item, $product_id){
			$event_date = filter_input(INPUT_POST, 'wsed_date_holder');
			$event_date_id = filter_input(INPUT_POST, 'wsed_date_id_holder');
			
			if(isset($event_date)){
				$cart_item['wsed_date'] = $event_date;
				$cart_item['wsed_date_id'] = $event_date_id ;
			}

			return $cart_item;
		}

		function wsed_checkout_create_order($order){

			$order_items = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));

			foreach($order_items as $item_id => $item){
				$product = $item->get_product();
				$product_id = $product->get_id();
				$quantity = $item->get_quantity();

				for($i=1; $i<10; $i++){ 
					if($item->get_meta(__('Date', 'woo-set-event-date')) == get_post_meta($product_id, 'wsed_event_date_' . $i, true) || $item->get_meta(__('Date', 'woo-set-event-date')) == get_post_meta($product_id, 'wsed_event_display_' . $i, true)){
						$wsed_event_spots = get_post_meta($product_id, 'wsed_event_spots_' . $i, true); 
						$wsed_event_spots -= $quantity;
						if($wsed_event_spots <= 0){
							update_post_meta($product_id, 'wsed_event_spots_' . $i, 0);
							update_post_meta($product_id, 'wsed_event_date_' . $i, "");
						}else{
							update_post_meta($product_id, 'wsed_event_spots_' . $i, $wsed_event_spots);
						}
						break;
					}
				}
			}
			return $order;
		}

		function wsed_get_item_data($item_data, $cart_item_data){
			$product_id = $cart_item_data['product_id'];

			if($cart_item_data['wsed_date'] != ""){
				$item_data[] = array(
					'key' => __('Date', 'woo-set-event-date'),
					'value' => (get_post_meta($product_id, 'wsed_event_display_' . $cart_item_data['wsed_date_id'], true) != "" ? get_post_meta($product_id, 'wsed_event_display_' . $cart_item_data['wsed_date_id'], true) : $cart_item_data['wsed_date']) 
				);
			}
			return $item_data;
		}

		function wsed_checkout_create_order_line_item($item, $cart_item_key, $values, $order){
			$product_id = $item['product_id'];

			if($values['wsed_date'] != ""){
				$item->add_meta_data(
					__('Date', 'woo-set-event-date'),
					(get_post_meta($product_id, 'wsed_event_display_' . $values['wsed_date_id'], true) != "" ? get_post_meta($product_id, 'wsed_event_display_' . $values['wsed_date_id'], true) : $values['wsed_date']),
					true
				);
			}
		}
		
		function wsed_quantity_input_args($args, $product) {
			$product_id = $product->get_id();

			$spots = "";
			for($i=1; $i<10; $i++){
				if((isset($_POST['wsed_date_holder']) ? $_POST['wsed_date_holder'] : get_post_meta($product_id, 'wsed_event_date_1', true)) == get_post_meta($product_id, 'wsed_event_date_' . $i, true)){
					$spots = get_post_meta($product_id, 'wsed_event_spots_' . $i, true);
				}
			}

			$args['max_value'] = $spots;
			$args['min_value'] = 1;
			$args['step'] = 1;

			if (is_singular('product'))
				$args['input_value'] = 1;

			return $args;
		}

		public function wsed_style(){
			echo "<style type='text/css'>";
				echo ".wsed_event_date_holder{margin-bottom:10px;}";
				echo '.wsed_event_date{font-style: italic;font-weight: 700;font-size: 100%;border-radius: 20px!important;padding: 5px!important;text-align: center;width: 40%;color: #fff;background-color: rgba(198,140,83,0.5)!important;text-decoration:none!important;margin-bottom:5px;cursor:pointer;}';
				echo '.wsed_event_date.active{background-color: rgba(198,140,83,1)!important;}';
				echo '.wsed_event_date:hover{background-color: rgba(198,140,83,1)!important;}';
				echo '.wsed_event_spots{margin-bottom:20px!important;padding:0!important;}';
			echo '</style>';
		}
	}
	$wsed_frontend = new Woo_Set_Event_Date_Frontend();
?>
