<?php
    defined('ABSPATH') or die('Suspicious activities detected!');
    
    class Woo_Set_Event_Date_Backend{

    	public function __construct(){
			// Register the Tab
    		add_filter('woocommerce_product_data_tabs', array($this, 'wsed_product_data_tab'), 100);	
		
			// Add the Tab
    		add_filter('woocommerce_product_data_panels', array($this,'wsed_dates_fields'));
    		
    		// Save Fields
    		add_action('woocommerce_process_product_meta', array($this, 'wsed_dates_save'));	
			
    		// Load small styling
    		add_action('admin_footer', array( $this, 'wsed_product_style'));
    	}
    
    	public function wsed_product_data_tab($original_tabs) {
			$new_tab['wsed-woo-event-date'] = array(
				'label' => __('Schedule', 'woo-set-event-date'),
				'target' => 'schedule_product_data',
			);

			$tabs = array_slice($original_tabs, 0, 1, true);
			$tabs = array_merge($tabs, $new_tab);
			$tabs = array_merge($tabs, array_slice($original_tabs, 1, null, true));

			return $tabs;
    	}
    
    	public function wsed_dates_fields(){
        	global $post;
        	
        	echo '<div id="schedule_product_data" class="panel woocommerce_options_panel wsed_options">';
        		
				woocommerce_wp_text_input(
					array( 
						'id'          => 'wsed_event_date_1', 
						'label'       => sprintf(__('Date %s', 'woo-set-event-date'), 1), 
						'placeholder' => 'Format: YYYY/MM/DD',
						'desc_tip'    => 'true',
						'description' => __('Set a date that can be selected by the user.', 'woo-set-event-date') 
					)
				);
				
				woocommerce_wp_text_input(
					array( 
						'id'          => 'wsed_event_display_1', 
						'label'       => sprintf(__('Display of date %s', 'woo-set-event-date'), 1), 
						'placeholder' => '',
						'desc_tip'    => 'true',
						'description' => __('Set a date to show next to the product. If empty, the date set above will be displayed.', 'woo-set-event-date') 
					)
				);
			
				woocommerce_wp_text_input(
					array( 
						'id'          => 'wsed_event_spots_1', 
						'label'       => sprintf(__('Number of spots for the date %s', 'woo-set-event-date'), 1), 
						'placeholder' => '0',
						'desc_tip'    => 'true',
						'description' => __('Set the number of spots to be available for this date.', 'woo-set-event-date'),
						'type'        => 'number'
					)
				);

				for($i=2; $i<10; $i++){
					if(!empty(get_post_meta( $post->ID, 'wsed_event_date_'.$i, true )) && get_post_meta( $post->ID, 'wsed_event_date_'.$i, true ) != ""){
						woocommerce_wp_text_input(
							array( 
								'id'          => 'wsed_event_date_'.$i, 
								'label'       => sprintf(__('Date %s', 'woo-set-event-date'), $i), 
								'placeholder' => __('Format: YYYY/MM/DD', 'woo-set-event-date'),
								'desc_tip'    => 'true',
								'description' => __('Set a date that can be selected by the user.', 'woo-set-event-date') 
							)
						);

						woocommerce_wp_text_input(
							array( 
								'id'          => 'wsed_event_display_'.$i, 
								'label'       => sprintf(__('Display of date %s', 'woo-set-event-date'), $i), 
								'placeholder' => '',
								'desc_tip'    => 'true',
								'description' => __('Set a date to show next to the product. If empty, the date set above will be displayed.', 'woo-set-event-date') 
							)
						);

						woocommerce_wp_text_input(
							array( 
								'id'          => 'wsed_event_spots_'.$i, 
								'label'       => sprintf(__('Number of spots for the date %s', 'woo-set-event-date'), $i), 
								'placeholder' => '20',
								'desc_tip'    => 'true',
								'description' => __('Set the number of spots to be available for this date.', 'woo-set-event-date'),
								'type'        => 'number'
							)
						);
					}else{
						break;
					}
				}

		
				echo "<div id='additional_dates'></div>";
			
				echo "<div id='add_additional_date' onclick='add_additional_date()' style='width: fit-content;padding: 10px 20px;background-color: #c9c9c9;color: #000;border-radius: 20px;margin-left: 20px;margin-bottom: 20px;cursor:pointer;'>" . __('Add new date','woo-set-event-date') . "</div>";
				echo "
					<script>
						var  i = $i;
						var date_label_text = '" . __('Date %s', 'woo-set-event-date') . "';
						var display_label_text = '" . __('Display of date %s', 'woo-set-event-date') . "';
						var spots_label_text = '" . __('Number of spots for the date %s', 'woo-set-event-date') . "';
						
						function add_additional_date(){
							if(i<10){
								var box = document.getElementById('additional_dates');

								var row_date = document.createElement('p');
								row_date.className = 'form-field';

								var label_date = document.createElement('label');
								label_date.innerHTML = date_label_text.replace('%s',i);
								label_date.htmlFor = 'field_additional_date_date_'+i;

								row_date.appendChild(label_date);

								var new_option_date = document.createElement('input');
									new_option_date.type= 'text';
									new_option_date.className = 'wsed-additional-date-field';
									new_option_date.name='wsed_event_date_'+i;
									new_option_date.setAttribute('id', 'field_additional_date_date_'+i);
									new_option_date.setAttribute('placeholder', '" . __('Format: YYYY/MM/DD', 'woo-set-event-date') . "');

								row_date.appendChild(new_option_date);

								box.appendChild(row_date);

								var row_display = document.createElement('p');
								row_display.className = 'form-field';

								var label_display = document.createElement('label');
								label_display.innerHTML = display_label_text.replace('%s',i);
								label_display.htmlFor = 'field_additional_date_display_'+i;

								row_display.appendChild(label_display);

								var new_option_display = document.createElement('input');
									new_option_display.type= 'text';
									new_option_display.className = 'wsed-additional-date-display-field';
									new_option_display.name='wsed_event_display_'+i;
									new_option_display.setAttribute('id', 'field_additional_date_display_'+i);

								row_display.appendChild(new_option_display);

								box.appendChild(row_display);

								var row_spots = document.createElement('p');
								row_spots.className = 'form-field';

								var label_spots = document.createElement('label');
								label_spots.innerHTML = spots_label_text.replace('%s',i);
								label_spots.htmlFor = 'field_additional_date_spots_'+i;

								row_spots.appendChild(label_spots);

								var new_option_spots = document.createElement('input');
									new_option_spots.type= 'text';
									new_option_spots.className = 'wsed-additional-date-spots-field';
									new_option_spots.name='wsed_event_spots_'+i;
									new_option_spots.setAttribute('id', 'field_additional_date_spots_'+i);
									new_option_spots.setAttribute('placeholder', '20');

								row_spots.appendChild(new_option_spots);

								box.appendChild(row_spots);
								i++;
							}
						}
					</script>
				";
			
        
        	echo '</div>';
        }

		// Saving event date options on WooCommerce single product general section.
		public static function wsed_dates_save($post_id){	
			$isDate = 0;
			for($i = 1; $i<10; $i++){
				if(isset($_POST['wsed_event_date_'.$i])){
					update_post_meta($post_id, 'wsed_event_date_'.$i,  sanitize_text_field($_POST['wsed_event_date_'.$i]));	
					update_post_meta($post_id, 'wsed_event_display_'.$i,  sanitize_text_field($_POST['wsed_event_display_'.$i]));	
					update_post_meta($post_id, 'wsed_event_spots_'.$i,  sanitize_text_field($_POST['wsed_event_spots_'.$i]));	

					$isDate = 1;
				}else{
					break;
				}
			}
			update_post_meta($post_id, 'wsed_event_is_date',  $isDate);	
		}
		
    	public function wsed_product_style(){
    		echo '<style>';
				echo '#woocommerce-product-data ul.wc-tabs li a::before {content: "\f508";}';
    		echo '</style>';
    	}
    }
    $wsed_backend = new Woo_Set_Event_Date_Backend();
?>
