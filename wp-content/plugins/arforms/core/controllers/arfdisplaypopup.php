<?php

class ARForms_Popup{

	function __construct(){

		add_filter('the_content',array($this,'arf_form_popup_function'));

		add_action('admin_enqueue_scripts',array($this,'enqueue_chosen_style_and_script'));

		add_action('wp_ajax_save_popup_data', array($this, 'save_popup_data'));

		add_action('wp_ajax_arf_update_popup_state', array($this, 'arf_update_popup_state'));

		add_action('wp_ajax_arf_delete_popup_list', array($this, 'arf_delete_popup_list'));

		add_action('wp_ajax_arf_duplicate_popup', array($this, 'arf_duplicate_popup'));

		add_action('wp_ajax_arf_edit_popup_form', array($this, 'arf_edit_popup_form'));

		add_action('wp_ajax_arf_bulk_delete_popup', array($this, 'arf_bulk_delete_popup'));

		add_action('wp_head', array($this, 'add_popup_shotcode_in_404_page'));

	}

	function add_popup_shotcode_in_404_page(){
		global $wp_query;
		if( $wp_query->is_404 ){
			global $wpdb, $MdlDb;
			$popup_ids = array();
			$popup_ids = $wpdb->get_results( $wpdb->prepare('SELECT * FROM ' . $MdlDb->form_popup . ' WHERE status=%d', 1 ), ARRAY_A );

			if( count( $popup_ids ) <= 0 ){
				return false;
			}

			$i=0;
			$exclude_posts = array();
			$exclude_pages = array();		

			foreach ( $popup_ids as $key => $popup ) {

				$popup_opt = json_decode($popup['popup_option']);
				foreach ( $popup_opt->arf_popup_post_exclude as $exclude_id ) {
					array_push( $exclude_posts, $exclude_id );	
				}	
				foreach ( $popup_opt->arf_popup_page_exclude as $exclude_id ) {
					array_push( $exclude_pages, $exclude_id );	
				}
				$popup['popup_option'] = ( array ) $popup_opt;
				$form_id = $popup['form_id'];
				
				if ( 0==$popup['popup_option']['arf_hide_popup_in_404'] && 1==$popup['status'] && 1==$popup['popup_option']['arf_display_popup_in_posts'] ) {

					$form_shortcode = "";
					
					$post_link_type = $popup['popup_option']['arf_popup_post_link_type'];
					$post_link_caption = $popup['popup_option']['arf_popup_post_caption'];
					$post_click_type = $popup['popup_option']['arf_popup_post_click_type'];
					$post_overlay = $popup['popup_option']['arf_popup_post_overlay'];
					$post_overlay_color = $popup['popup_option']['arf_popup_post_model_bgcolor'];
					$post_show_close = $popup['popup_option']['arf_popup_post_show_close'];
					$post_model_height = $popup['popup_option']['arf_popup_post_model_height'];
					$post_model_width = $popup['popup_option']['arf_popup_post_model_width'];
					$post_link_position = $popup['popup_option']['arf_popup_post_link_position'];
					$post_link_position_fly = isset($popup['popup_option']['arf_popup_post_link_position_fly']) ? $popup['popup_option']['arf_popup_post_link_position_fly'] : 'top';
					$post_link_button_angle = $popup['popup_option']['arf_popup_post_button_angle'];
					$post_show_full_screen = $popup['popup_option']['arf_popup_post_show_full_screen'];
					$post_model_effect = $popup['popup_option']['arf_popup_post_model_effect'];
					$post_inact_time = $popup['popup_option']['arf_popup_post_inactive_time'];

					$post_open_scroll = $popup['popup_option']['arf_popup_post_open_scroll'];
					$post_open_delay = $popup['popup_option']['arf_popup_post_open_delay'];
					$post_model_btn_bgcolor = $popup['popup_option']['arf_popup_post_modal_btn_bg_color'];
					$post_model_btn_txt_color = $popup['popup_option']['arf_popup_post_modal_btn_txt_color'];

					switch($post_link_type){
						case 'onclick':
							if( $post_click_type == 'sticky' ){
								$form_shortcode = "[ARForms_popup id=".$form_id." desc='".$post_link_caption."' type='sticky' position='".$post_link_position."' height='".$post_model_height."' width='".$post_model_width."' bgcolor='".$post_model_btn_bgcolor."' txtcolor='".$post_model_btn_txt_color."']";
							} else if( $post_click_type == 'fly' ){
								$form_shortcode = "[ARForms_popup id=".$form_id." desc='".$post_link_caption."' type='fly' position='".$post_link_position_fly."' height='".$post_model_height."' width='".$post_model_width."' bgcolor='".$post_model_btn_bgcolor."' txtcolor='".$post_model_btn_txt_color."' angle='".$post_link_button_angle."' ]";
							}
						break;
						case 'onload':
							$form_shortcode = "[ARForms_popup id=" . $form_id . " type='" . $post_link_type . "' width='".$post_model_width."' modaleffect='".$post_model_effect."' is_fullscreen='".$post_show_full_screen."' overlay='".$post_overlay."' is_close_link='".$post_show_close."' modal_bgcolor='".$post_overlay_color."' ]";
						break;
						case 'scroll':
							$form_shortcode = "[ARForms_popup id=" . $form_id . " type='" . $post_link_type . "' width='".$post_model_width."' modaleffect='".$post_model_effect."' is_fullscreen='".$post_show_full_screen."' on_scroll='".$post_open_scroll."' overlay='".$post_overlay."' is_close_link='".$post_show_close."' modal_bgcolor='".$post_overlay_color."' ]";
						break;
						case 'timer':
							$form_shortcode = "[ARForms_popup id=". $form_id ." on_delay='".$post_open_delay."' type='".$post_link_type."' width='".$post_model_width."' modaleffect='".$post_model_effect."' is_fullscreen='".$post_show_full_screen."' overlay='".$post_overlay."' is_close_link='".$post_show_close."' modal_bgcolor='".$post_overlay_color."']";
						break;
						case 'on_exit':
							$form_shortcode = "[ARForms_popup id=". $form_id ." type='".$post_link_type."' width='".$post_model_width."' modaleffect='".$post_model_effect."' is_fullscreen='".$post_show_full_screen."' is_close_link='".$post_show_close."' modal_bgcolor='".$post_overlay_color."' ]";
						break;
						case 'on_idle':
							$form_shortcode = "[ARForms_popup id=". $form_id ." type='".$post_link_type."' width='".$post_model_width."' modaleffect='".$post_model_effect."' is_fullscreen='".$post_show_full_screen."' inactive_min='".$post_inact_time."' overlay='".$post_overlay."' is_close_link='".$post_show_close."' modal_bgcolor='".$post_overlay_color."']";
						break;
					}
					
					echo do_shortcode($form_shortcode);

				} else if ( 0==$popup['popup_option']['arf_hide_popup_in_404'] && 1==$popup['status'] && 1==$popup['popup_option']['arf_display_popup_in_pages'] ) {


					$page_link_type = $popup['popup_option']['arf_popup_page_link_type'];
					$page_link_caption = $popup['popup_option']['arf_popup_page_caption'];
					$page_click_type = $popup['popup_option']['arf_popup_page_click_type'];
					$page_overlay = $popup['popup_option']['arf_popup_page_overlay'];
					$page_overlay_color = $popup['popup_option']['arf_popup_page_model_bgcolor'];
					$page_show_close = $popup['popup_option']['arf_popup_page_show_close'];
					$page_model_height = $popup['popup_option']['arf_popup_page_model_height'];
					$page_model_width = $popup['popup_option']['arf_popup_page_model_width'];
					$page_link_position = $popup['popup_option']['arf_popup_page_link_position'];
					$page_link_position_fly = $popup['popup_option']['arf_popup_page_link_position_fly'];
					$page_link_button_angle = $popup['popup_option']['arf_popup_page_button_angle'];

					$page_show_full_screen = $popup['popup_option']['arf_popup_page_show_full_screen'];
					$page_model_effect = $popup['popup_option']['arf_popup_page_model_effect'];
					$page_inact_time = $popup['popup_option']['arf_popup_page_inactive_time'];

					$page_open_scroll = $popup['popup_option']['arf_popup_page_open_scroll'];
					$page_open_delay = $popup['popup_option']['arf_popup_page_open_delay'];
					$page_model_btn_bgcolor = $popup['popup_option']['arf_popup_page_modal_btn_bg_color'];
					$page_model_btn_txt_color = $popup['popup_option']['arf_popup_page_modal_btn_txt_color'];

					switch($page_link_type){
						case 'onclick':
							if( $page_click_type == 'sticky' ){
								$form_shortcode = "[ARForms_popup id=".$form_id." desc='".$page_link_caption."' type='sticky' position='".$page_link_position."' height='".$page_model_height."' width='".$page_model_width."' bgcolor='".$page_model_btn_bgcolor."' txtcolor='".$page_model_btn_txt_color."']";
							} else if( $page_click_type == 'fly' ){
								$form_shortcode = "[ARForms_popup id=".$form_id." desc='".$page_link_caption."' type='fly' position='".$page_link_position_fly."' height='".$page_model_height."' width='".$page_model_width."' bgcolor='".$page_model_btn_bgcolor."' txtcolor='".$page_model_btn_txt_color."' angle='".$page_link_button_angle."' ]";
							}
						break;
						case 'onload':
							$form_shortcode = "[ARForms_popup id=" . $form_id . " type='" . $page_link_type . "' width='".$page_model_width."' modaleffect='".$page_model_effect."' is_fullscreen='".$page_show_full_screen."' overlay='".$page_overlay."' is_close_link='".$page_show_close."' modal_bgcolor='".$page_overlay_color."' ]";
						break;
						case 'scroll':
							$form_shortcode = "[ARForms_popup id=" . $form_id . " type='" . $page_link_type . "' width='".$page_model_width."' modaleffect='".$page_model_effect."' is_fullscreen='".$page_show_full_screen."' on_scroll='".$page_open_scroll."' overlay='".$page_overlay."' is_close_link='".$page_show_close."' modal_bgcolor='".$page_overlay_color."' ]";
						break;
						case 'timer':
							$form_shortcode = "[ARForms_popup id=". $form_id ." on_delay='".$page_open_delay."' type='".$page_link_type."' width='".$page_model_width."' modaleffect='".$page_model_effect."' is_fullscreen='".$page_show_full_screen."' overlay='".$page_overlay."' is_close_link='".$page_show_close."' modal_bgcolor='".$page_overlay_color."']";
						break;
						case 'on_exit':
							$form_shortcode = "[ARForms_popup id=". $form_id ." type='".$page_link_type."' width='".$page_model_width."' modaleffect='".$page_model_effect."' is_fullscreen='".$page_show_full_screen."' is_close_link='".$page_show_close."' modal_bgcolor='".$page_overlay_color."' ]";
						break;
						case 'on_idle':
							$form_shortcode = "[ARForms_popup id=". $form_id ." type='".$page_link_type."' width='".$page_model_width."' modaleffect='".$page_model_effect."' is_fullscreen='".$page_show_full_screen."' inactive_min='".$page_inact_time."' overlay='".$page_overlay."' is_close_link='".$page_show_close."' modal_bgcolor='".$page_overlay_color."']";
						break;
					}

					echo do_shortcode($form_shortcode);

				}	
				$i++;
			}
		}
	}

	function arf_form_popup_function($content){

		global $wpdb,$arfform,$post, $MdlDb;

		$popup_type = "";
		if( 'post'==$post->post_type ){
			$popup_type = "post";
		}
		if( 'page'==$post->post_type ){
			$popup_type = "page";
		}
		
		$popup_ids = array();
		if(''==$popup_type){
			return $content;
		} else{
			$popup_ids = $wpdb->get_results($wpdb->prepare('SELECT * FROM '.$MdlDb->form_popup.' WHERE status=%d', 1), ARRAY_A);
			
		}
		
		if(count($popup_ids)<=0){
			return $content;
		}


		$i=0;
		$exclude_posts = array();
		$exclude_pages = array();		

		foreach ($popup_ids as $key => $popup) {

			$popup_opt = json_decode($popup['popup_option']);
			foreach ($popup_opt->arf_popup_post_exclude as $exclude_id) {
				array_push($exclude_posts, $exclude_id);	
			}	
			foreach ($popup_opt->arf_popup_page_exclude as $exclude_id) {
				array_push($exclude_pages, $exclude_id);	
			}
			$popup['popup_option'] = (array) $popup_opt;
			$form_id = $popup['form_id'];
			
			if ( ( (is_search() && 0==$popup['popup_option']['arf_hide_popup_in_search_result']) || (is_front_page() && 0==$popup['popup_option']['arf_hide_popup_in_front']) || (is_archive() && 0==$popup['popup_option']['arf_hide_popup_in_archive']) || 'post'==$post->post_type ) && 1==$popup['status'] && 1==$popup['popup_option']['arf_display_popup_in_posts'] && !in_array($post->ID,$exclude_posts) ) {

				$form_shortcode = "";
				
				$post_link_type = $popup['popup_option']['arf_popup_post_link_type'];
				$post_link_caption = $popup['popup_option']['arf_popup_post_caption'];
				$post_click_type = $popup['popup_option']['arf_popup_post_click_type'];
				$post_overlay = $popup['popup_option']['arf_popup_post_overlay'];
				$post_overlay_color = $popup['popup_option']['arf_popup_post_model_bgcolor'];
				$post_show_close = $popup['popup_option']['arf_popup_post_show_close'];
				$post_model_height = $popup['popup_option']['arf_popup_post_model_height'];
				$post_model_width = $popup['popup_option']['arf_popup_post_model_width'];
				$post_link_position = $popup['popup_option']['arf_popup_post_link_position'];
				$post_link_position_fly = isset($popup['popup_option']['arf_popup_post_link_position_fly']) ? $popup['popup_option']['arf_popup_post_link_position_fly'] : 'top';
				$post_link_button_angle = $popup['popup_option']['arf_popup_post_button_angle'];
				$post_show_full_screen = $popup['popup_option']['arf_popup_post_show_full_screen'];
				$post_model_effect = $popup['popup_option']['arf_popup_post_model_effect'];
				$post_inact_time = $popup['popup_option']['arf_popup_post_inactive_time'];

				$post_open_scroll = $popup['popup_option']['arf_popup_post_open_scroll'];
				$post_open_delay = $popup['popup_option']['arf_popup_post_open_delay'];
				$post_model_btn_bgcolor = $popup['popup_option']['arf_popup_post_modal_btn_bg_color'];
				$post_model_btn_txt_color = $popup['popup_option']['arf_popup_post_modal_btn_txt_color'];

				switch($post_link_type){
					case 'onclick':
						if( $post_click_type == 'sticky' ){
							$form_shortcode = "[ARForms_popup id=".$form_id." desc='".$post_link_caption."' type='sticky' position='".$post_link_position."' height='".$post_model_height."' width='".$post_model_width."' bgcolor='".$post_model_btn_bgcolor."' txtcolor='".$post_model_btn_txt_color."']";
						} else if( $post_click_type == 'fly' ){
							$form_shortcode = "[ARForms_popup id=".$form_id." desc='".$post_link_caption."' type='fly' position='".$post_link_position_fly."' height='".$post_model_height."' width='".$post_model_width."' bgcolor='".$post_model_btn_bgcolor."' txtcolor='".$post_model_btn_txt_color."' angle='".$post_link_button_angle."' ]";
						}
					break;
					case 'onload':
						$form_shortcode = "[ARForms_popup id=" . $form_id . " type='" . $post_link_type . "' width='".$post_model_width."' modaleffect='".$post_model_effect."' is_fullscreen='".$post_show_full_screen."' overlay='".$post_overlay."' is_close_link='".$post_show_close."' modal_bgcolor='".$post_overlay_color."' ]";
					break;
					case 'scroll':
						$form_shortcode = "[ARForms_popup id=" . $form_id . " type='" . $post_link_type . "' width='".$post_model_width."' modaleffect='".$post_model_effect."' is_fullscreen='".$post_show_full_screen."' on_scroll='".$post_open_scroll."' overlay='".$post_overlay."' is_close_link='".$post_show_close."' modal_bgcolor='".$post_overlay_color."' ]";
					break;
					case 'timer':
						$form_shortcode = "[ARForms_popup id=". $form_id ." on_delay='".$post_open_delay."' type='".$post_link_type."' width='".$post_model_width."' modaleffect='".$post_model_effect."' is_fullscreen='".$post_show_full_screen."' overlay='".$post_overlay."' is_close_link='".$post_show_close."' modal_bgcolor='".$post_overlay_color."']";
					break;
					case 'on_exit':
						$form_shortcode = "[ARForms_popup id=". $form_id ." type='".$post_link_type."' width='".$post_model_width."' modaleffect='".$post_model_effect."' is_fullscreen='".$post_show_full_screen."' is_close_link='".$post_show_close."' modal_bgcolor='".$post_overlay_color."' ]";
					break;
					case 'on_idle':
						$form_shortcode = "[ARForms_popup id=". $form_id ." type='".$post_link_type."' width='".$post_model_width."' modaleffect='".$post_model_effect."' is_fullscreen='".$post_show_full_screen."' inactive_min='".$post_inact_time."' overlay='".$post_overlay."' is_close_link='".$post_show_close."' modal_bgcolor='".$post_overlay_color."']";
					break;
				}
				
				if(is_search()){
					echo do_shortcode($form_shortcode);
				}

				$content .= $form_shortcode;

			} else if ( ( (is_search() && 0==$popup['popup_option']['arf_hide_popup_in_search_result']) || (is_front_page() && 0==$popup['popup_option']['arf_hide_popup_in_front']) || (is_archive() && 0==$popup['popup_option']['arf_hide_popup_in_archive']) || 'page'==$post->post_type ) && 1==$popup['status'] && 1==$popup['popup_option']['arf_display_popup_in_pages'] && !in_array($post->ID,$exclude_pages) ) {


				$page_link_type = $popup['popup_option']['arf_popup_page_link_type'];
				$page_link_caption = $popup['popup_option']['arf_popup_page_caption'];
				$page_click_type = $popup['popup_option']['arf_popup_page_click_type'];
				$page_overlay = $popup['popup_option']['arf_popup_page_overlay'];
				$page_overlay_color = $popup['popup_option']['arf_popup_page_model_bgcolor'];
				$page_show_close = $popup['popup_option']['arf_popup_page_show_close'];
				$page_model_height = $popup['popup_option']['arf_popup_page_model_height'];
				$page_model_width = $popup['popup_option']['arf_popup_page_model_width'];
				$page_link_position = $popup['popup_option']['arf_popup_page_link_position'];
				$page_link_position_fly = $popup['popup_option']['arf_popup_page_link_position_fly'];
				$page_link_button_angle = $popup['popup_option']['arf_popup_page_button_angle'];

				$page_show_full_screen = $popup['popup_option']['arf_popup_page_show_full_screen'];
				$page_model_effect = $popup['popup_option']['arf_popup_page_model_effect'];
				$page_inact_time = $popup['popup_option']['arf_popup_page_inactive_time'];

				$page_open_scroll = $popup['popup_option']['arf_popup_page_open_scroll'];
				$page_open_delay = $popup['popup_option']['arf_popup_page_open_delay'];
				$page_model_btn_bgcolor = $popup['popup_option']['arf_popup_page_modal_btn_bg_color'];
				$page_model_btn_txt_color = $popup['popup_option']['arf_popup_page_modal_btn_txt_color'];

				switch($page_link_type){
					case 'onclick':
						if( $page_click_type == 'sticky' ){
							$form_shortcode = "[ARForms_popup id=".$form_id." desc='".$page_link_caption."' type='sticky' position='".$page_link_position."' height='".$page_model_height."' width='".$page_model_width."' bgcolor='".$page_model_btn_bgcolor."' txtcolor='".$page_model_btn_txt_color."']";
						} else if( $page_click_type == 'fly' ){
							$form_shortcode = "[ARForms_popup id=".$form_id." desc='".$page_link_caption."' type='fly' position='".$page_link_position_fly."' height='".$page_model_height."' width='".$page_model_width."' bgcolor='".$page_model_btn_bgcolor."' txtcolor='".$page_model_btn_txt_color."' angle='".$page_link_button_angle."' ]";
						}
					break;
					case 'onload':
						$form_shortcode = "[ARForms_popup id=" . $form_id . " type='" . $page_link_type . "' width='".$page_model_width."' modaleffect='".$page_model_effect."' is_fullscreen='".$page_show_full_screen."' overlay='".$page_overlay."' is_close_link='".$page_show_close."' modal_bgcolor='".$page_overlay_color."' ]";
					break;
					case 'scroll':
						$form_shortcode = "[ARForms_popup id=" . $form_id . " type='" . $page_link_type . "' width='".$page_model_width."' modaleffect='".$page_model_effect."' is_fullscreen='".$page_show_full_screen."' on_scroll='".$page_open_scroll."' overlay='".$page_overlay."' is_close_link='".$page_show_close."' modal_bgcolor='".$page_overlay_color."' ]";
					break;
					case 'timer':
						$form_shortcode = "[ARForms_popup id=". $form_id ." on_delay='".$page_open_delay."' type='".$page_link_type."' width='".$page_model_width."' modaleffect='".$page_model_effect."' is_fullscreen='".$page_show_full_screen."' overlay='".$page_overlay."' is_close_link='".$page_show_close."' modal_bgcolor='".$page_overlay_color."']";
					break;
					case 'on_exit':
						$form_shortcode = "[ARForms_popup id=". $form_id ." type='".$page_link_type."' width='".$page_model_width."' modaleffect='".$page_model_effect."' is_fullscreen='".$page_show_full_screen."' is_close_link='".$page_show_close."' modal_bgcolor='".$page_overlay_color."' ]";
					break;
					case 'on_idle':
						$form_shortcode = "[ARForms_popup id=". $form_id ." type='".$page_link_type."' width='".$page_model_width."' modaleffect='".$page_model_effect."' is_fullscreen='".$page_show_full_screen."' inactive_min='".$page_inact_time."' overlay='".$page_overlay."' is_close_link='".$page_show_close."' modal_bgcolor='".$page_overlay_color."']";
					break;
				}

				if(is_search()){
					echo do_shortcode($form_shortcode);
				}

				$content .= $form_shortcode;
			}	
			$i++;
		}
		

		return $content;
	}

	function save_popup_data(){
		$response_data = array();
		if(isset($_POST['action']) && 'save_popup_data'==$_POST['action']){
			global $wpdb, $MdlDb;
			
			if(isset($_POST['data']) && !empty($_POST['data'])){
				
				$new_values['form_id'] = 0;
				$new_values['popup_type'] = "";
				$new_values['status'] = 0;
				$new_values['created_date'] = current_time('mysql', 1);
				$opt_arr = array();
				$popup_opt = array();
				foreach ($_POST['data'] as $key => $value) {
					$key_name = str_replace("options[", "", $value['name']);
					$key_name = str_replace("]", "", $key_name);
					if('arf_popup_selected_form'==$key_name){
						$new_values['form_id'] = $value['value'];
					}
					
					$opt_arr[$key_name] = $value['value'];
				}
				
				$popup_opt['arf_popup_selected_form'] = isset($opt_arr['arf_popup_selected_form']) ? arf_sanitize_value($opt_arr['arf_popup_selected_form'], 'integer') : 0;
				$popup_opt['arf_hide_popup_in_archive'] = isset($opt_arr['arf_hide_popup_in_archive']) ? arf_sanitize_value($opt_arr['arf_hide_popup_in_archive'], 'integer') : 0;
				$popup_opt['arf_hide_popup_in_404'] = isset($opt_arr['arf_hide_popup_in_404']) ? arf_sanitize_value($opt_arr['arf_hide_popup_in_404'], 'integer') : 0;
				$popup_opt['arf_hide_popup_in_front'] = isset($opt_arr['arf_hide_popup_in_front']) ? arf_sanitize_value($opt_arr['arf_hide_popup_in_front'], 'integer') : 0;
				$popup_opt['arf_hide_popup_in_search_result'] = isset($opt_arr['arf_hide_popup_in_search_result']) ? arf_sanitize_value($opt_arr['arf_hide_popup_in_search_result'], 'integer') : 0;
				


				/*setting for popup*/
				$popup_opt['arf_display_popup_in_posts'] = isset($opt_arr['arf_display_popup_in_posts']) ? arf_sanitize_value($opt_arr['arf_display_popup_in_posts'], 'integer') : 0;
				$popup_opt['arf_popup_post_link_type'] = isset($opt_arr['arf_popup_post_link_type']) ? arf_sanitize_value($opt_arr['arf_popup_post_link_type']) : 'onclick';
				$popup_opt['arf_popup_post_caption'] = isset($opt_arr['arf_popup_post_caption']) ? arf_sanitize_value($opt_arr['arf_popup_post_caption']) : 'Click here to open form';
				$popup_opt['arf_popup_post_click_type'] = isset($opt_arr['arf_popup_post_click_type']) ? arf_sanitize_value($opt_arr['arf_popup_post_click_type']) : 'sticky';
				$popup_opt['arf_popup_post_overlay'] = isset($opt_arr['arf_popup_post_overlay']) ? arf_sanitize_value($opt_arr['arf_popup_post_overlay']) : '0.6';
				$popup_opt['arf_popup_post_model_bgcolor'] = isset($opt_arr['arf_popup_post_model_bgcolor']) ? arf_sanitize_value($opt_arr['arf_popup_post_model_bgcolor']) : '#000000';
				$popup_opt['arf_popup_post_show_close'] = isset($opt_arr['arf_popup_post_show_close']) ? arf_sanitize_value($opt_arr['arf_popup_post_show_close']) : 'yes';
				$popup_opt['arf_popup_post_model_height'] = isset($opt_arr['arf_popup_post_model_height']) ? arf_sanitize_value($opt_arr['arf_popup_post_model_height']) : 'auto';
				$popup_opt['arf_popup_post_model_width'] = isset($opt_arr['arf_popup_post_model_width']) ? arf_sanitize_value($opt_arr['arf_popup_post_model_width'], 'integer') : 800;
				$popup_opt['arf_popup_post_link_position'] = isset($opt_arr['arf_popup_post_link_position']) ? arf_sanitize_value($opt_arr['arf_popup_post_link_position']) : 'top';
				$popup_opt['arf_popup_post_link_position_fly'] = isset($opt_arr['arf_popup_post_link_position_fly']) ? arf_sanitize_value($opt_arr['arf_popup_post_link_position_fly']) : 'left';
				$popup_opt['arf_popup_post_button_angle'] = isset($opt_arr['arf_popup_post_button_angle']) ? arf_sanitize_value($opt_arr['arf_popup_post_button_angle'], 'integer') : 0;
				$popup_opt['arf_popup_post_show_full_screen'] = isset($opt_arr['arf_popup_post_show_full_screen']) ? arf_sanitize_value($opt_arr['arf_popup_post_show_full_screen']) : 'no';
				$popup_opt['arf_popup_post_model_effect'] = isset($opt_arr['arf_popup_post_model_effect']) ? arf_sanitize_value($opt_arr['arf_popup_post_model_effect']) : 'fade_in';
				$popup_opt['arf_popup_post_inactive_time'] = isset($opt_arr['arf_popup_post_inactive_time']) ? arf_sanitize_value($opt_arr['arf_popup_post_inactive_time'], 'integer') : 1;
				$popup_opt['arf_popup_post_open_scroll'] = isset($opt_arr['arf_popup_post_open_scroll']) ? arf_sanitize_value($opt_arr['arf_popup_post_open_scroll'], 'integer') : 10;
				$popup_opt['arf_popup_post_open_delay'] = isset($opt_arr['arf_popup_post_open_delay']) ? arf_sanitize_value($opt_arr['arf_popup_post_open_delay'], 'integer') : 0;
				$popup_opt['arf_popup_post_modal_btn_bg_color'] = isset($opt_arr['arf_popup_post_modal_btn_bg_color']) ? arf_sanitize_value($opt_arr['arf_popup_post_modal_btn_bg_color']) : '#808080';
				$popup_opt['arf_popup_post_modal_btn_txt_color'] = isset($opt_arr['arf_popup_post_modal_btn_txt_color']) ? arf_sanitize_value($opt_arr['arf_popup_post_modal_btn_txt_color']) : '#FFFFFF';
				$popup_opt['arf_popup_post_exclude'] = isset($opt_arr['arf_popup_post_exclude']) ? explode(',',$opt_arr['arf_popup_post_exclude']) : array();




				/* page settings */
				$popup_opt['arf_display_popup_in_pages'] = isset($opt_arr['arf_display_popup_in_pages']) ? arf_sanitize_value($opt_arr['arf_display_popup_in_pages'], 'integer') : 0;
				$popup_opt['arf_popup_page_link_type'] = isset($opt_arr['arf_popup_page_link_type']) ? arf_sanitize_value($opt_arr['arf_popup_page_link_type']) : 'onclick';
				$popup_opt['arf_popup_page_caption'] = isset($opt_arr['arf_popup_page_caption']) ? arf_sanitize_value($opt_arr['arf_popup_page_caption']) : 'Click here to open form';
				$popup_opt['arf_popup_page_click_type'] = isset($opt_arr['arf_popup_page_click_type']) ? arf_sanitize_value($opt_arr['arf_popup_page_click_type']) : 'sticky';
				$popup_opt['arf_popup_page_overlay'] = isset($opt_arr['arf_popup_page_overlay']) ? arf_sanitize_value($opt_arr['arf_popup_page_overlay']) : '0.6';
				$popup_opt['arf_popup_page_model_bgcolor'] = isset($opt_arr['arf_popup_page_model_bgcolor']) ? arf_sanitize_value($opt_arr['arf_popup_page_model_bgcolor']) : '#000000';
				$popup_opt['arf_popup_page_show_close'] = isset($opt_arr['arf_popup_page_show_close']) ? arf_sanitize_value($opt_arr['arf_popup_page_show_close']) : 'yes';
				$popup_opt['arf_popup_page_model_height'] = isset($opt_arr['arf_popup_page_model_height']) ? arf_sanitize_value($opt_arr['arf_popup_page_model_height']) : 'auto';
				$popup_opt['arf_popup_page_model_width'] = isset($opt_arr['arf_popup_page_model_width']) ? arf_sanitize_value($opt_arr['arf_popup_page_model_width'], 'integer') : 800;
				$popup_opt['arf_popup_page_link_position'] = isset($opt_arr['arf_popup_page_link_position']) ? arf_sanitize_value($opt_arr['arf_popup_page_link_position']) : 'top';
				$popup_opt['arf_popup_page_link_position_fly'] = isset($opt_arr['arf_popup_page_link_position_fly']) ? arf_sanitize_value($opt_arr['arf_popup_page_link_position_fly']) : 'left';
				$popup_opt['arf_popup_page_button_angle'] = isset($opt_arr['arf_popup_page_button_angle']) ? arf_sanitize_value($opt_arr['arf_popup_page_button_angle'], 'integer') : 0;
				$popup_opt['arf_popup_page_show_full_screen'] = isset($opt_arr['arf_popup_page_show_full_screen']) ? arf_sanitize_value($opt_arr['arf_popup_page_show_full_screen']) : 'no';
				$popup_opt['arf_popup_page_model_effect'] = isset($opt_arr['arf_popup_page_model_effect']) ? arf_sanitize_value($opt_arr['arf_popup_page_model_effect']) : 'fade_in';
				$popup_opt['arf_popup_page_inactive_time'] = isset($opt_arr['arf_popup_page_inactive_time']) ? arf_sanitize_value($opt_arr['arf_popup_page_inactive_time'], 'integer') : 1;
				$popup_opt['arf_popup_page_open_scroll'] = isset($opt_arr['arf_popup_page_open_scroll']) ? arf_sanitize_value($opt_arr['arf_popup_page_open_scroll'], 'integer') : 10;
				$popup_opt['arf_popup_page_open_delay'] = isset($opt_arr['arf_popup_page_open_delay']) ? arf_sanitize_value($opt_arr['arf_popup_page_open_delay'], 'integer') : 0;
				$popup_opt['arf_popup_page_modal_btn_bg_color'] = isset($opt_arr['arf_popup_page_modal_btn_bg_color']) ? arf_sanitize_value($opt_arr['arf_popup_page_modal_btn_bg_color']) : '#808080';
				$popup_opt['arf_popup_page_modal_btn_txt_color'] = isset($opt_arr['arf_popup_page_modal_btn_txt_color']) ? arf_sanitize_value($opt_arr['arf_popup_page_modal_btn_txt_color']) : '#FFFFFF';
				$popup_opt['arf_popup_page_exclude'] = isset($opt_arr['arf_popup_page_exclude']) ? explode(',',$opt_arr['arf_popup_page_exclude']) : array();
				/*echo "<br>form_id : ".$form_id;
				echo "<br>type : ".$type;
				echo "<br>state : ".$state;*/
				$new_values['popup_option'] = json_encode($popup_opt);

				if((isset($popup_opt['arf_display_popup_in_posts']) && 1==$popup_opt['arf_display_popup_in_posts']) && (isset($popup_opt['arf_display_popup_in_pages']) && 1==$popup_opt['arf_display_popup_in_pages'])){
					$new_values['popup_type'] = "post,page";
					$new_values['status'] = 1;
				} else if(isset($popup_opt['arf_display_popup_in_posts']) && 1==$popup_opt['arf_display_popup_in_posts']){
					$new_values['popup_type'] = "post";
					$new_values['status'] = 1;
				} else if(isset($popup_opt['arf_display_popup_in_pages']) && 1==$popup_opt['arf_display_popup_in_pages']){
					$new_values['popup_type'] = "page";
					$new_values['status'] = 1;
				}

				$is_success = 0;
				$message = "";
				
				if((isset($_POST['popup_action']) && 'update'==$_POST['popup_action']) && (isset($_POST['popup_id']) && 0!=$_POST['popup_id'])){
					$wpdb->update($MdlDb->form_popup,
						array(
							'form_id' => $new_values['form_id'],
							'popup_type' => $new_values['popup_type'],
							'popup_option' => $new_values['popup_option'],
							'status' => $new_values['status'],
							'created_date' => $new_values['created_date']
						),
						array('popup_id' => $_POST['popup_id']),
						array('%d', '%s', '%s', '%d', '%s'),
						array('%d')
					);
					$is_success = 1;
					$message = "Popup updated successfully.";
				} else{
					$query_results = $wpdb->insert($MdlDb->form_popup, $new_values);
					if($wpdb->insert_id){
						$is_success = 1;
						$message = "Popup created successfully.";
					}
				}
				

        		if(1==$is_success){
        			$response_data['state'] = 'success';
        			$response_data['message'] = esc_html__($message, 'ARForms');
        			$popup_data = $this->arf_load_popup_list();
        			$response_data['popup_data'] = $popup_data['data'];
        			$response_data['popup_list_count'] = $popup_data['count'];
        			echo json_encode($response_data);
        			die;
        		} else{
        			$response_data['state'] = 'error';
        			$response_data['message'] = esc_html__('There is something worng while save options.', 'ARForms');
        			echo json_encode($response_data);
        			die;
        		}
			}
		} 
	}

	function enqueue_chosen_style_and_script(){
		global $arf_jscss_version;
		if(isset($_REQUEST['page']) && 'ARForms-popups'==$_REQUEST['page']){
			wp_enqueue_script('arf-jscolor-js',ARFURL.'/js/jscolor.js', array(), $arf_jscss_version);
			wp_enqueue_style('arf-fontawesome-css', ARFURL . '/css/font-awesome.min.css', array(), $arf_jscss_version);	

		}

		if(isset($_REQUEST['page']) && 'ARForms-popups'==$_REQUEST['page']){
			$popup_internal_style = '
			@import " '.ARFURL.'/datatables/media/css/demo_page.css";
		    @import " '.ARFURL.'/datatables/media/css/demo_table_jui.css";
		    @import " '.ARFURL.'/datatables/media/css/jquery-ui-1.8.4.custom.css";
		    @import " '.ARFURL.'/datatables/media/css/ColVis.css";
			';

		    $popup_internal_script = '
		    var __POPUP_SAVE_BTN_LABEL = "'. esc_html__("Save", "ARForms") .'";
		    var __BLANK_POPUP_FORM_ERROR_LABEL = "'. esc_html__('Please select a form.', 'ARForms') .'";
		    var __POST_BLANK_SELECT_ERROR = "'. esc_html__("Please select post popup", "ARForms") .'";
		    var __PAGE_BLANK_SELECT_ERROR = "'. esc_html__("Please select page popup", "ARForms") .'";
		    __ARF_LOADER_ICON = \''. ARF_LOADER_ICON .'\';
			';

			wp_add_inline_style('arforms_v3.0', $popup_internal_style);
			wp_add_inline_script('arforms_admin_v3.0', $popup_internal_script);	
		}
		
	}

	

	function arf_load_popup_list(){
		global $MdlDb, $wpdb;
		$response_arr = array();
		$grid_columns = array(
            'input' => '',
            'form_id' => 'Form ID',
            'form_title' => 'Form Title',
            'popup_type' => 'Popup Type',
            'status' => 'Status',
            'created_date' => 'Create Date',
            'action' => 'Action'
        );

		$popup_result = $wpdb->get_results("SELECT a.popup_id,a.form_id,a.popup_type,a.popup_option,a.status,a.created_date,b.name FROM ".$MdlDb->form_popup." a
		INNER JOIN ".$MdlDb->forms." b ON a.form_id=b.id");

		$data = "";
		$count = count($popup_result);
		$i=0;
		if($count > 0){
			foreach ($popup_result as $key => $popup_data) {
				$j=0;
				$data .= "<tr data-form-id='".$popup_data->popup_id."'>";
				foreach ($grid_columns as $key => $tmp_data) {
					switch ($key) {
						case 'input':
							$data .= "<td class='box'><div class='arf_custom_checkbox_div'><div class='arf_custom_checkbox_wrapper'><input id='cb-item-action-{$popup_data->popup_id} class='chkstanard' type='checkbox' value='{$popup_data->popup_id}' name='item-action[]'>
	                                <svg width='18px' height='18px'>
	                                " . ARF_CUSTOM_UNCHECKED_ICON . "
	                                " . ARF_CUSTOM_CHECKED_ICON . "
	                                </svg>
	                            </div>
	                        </div>
	                        <label for='cb-item-action-{$popup_data->popup_id}'><span></span></label></td>";
	                        $j++;
							break;

                       	case 'form_id':
	                        $data .= "<td class='id_column'>" . $popup_data->form_id . "</td>";
	                        $j++;
	                        break;

                       case 'form_title':
	                        $data .= "<td class='popup_type_column'>" . $popup_data->name . "</td>";
	                        $j++;
	                        break;

	                    case 'popup_type':
	                        $data .= "<td class='popup_type_column'>" . $popup_data->popup_type . "</td>";
	                        $j++;
	                        break;

	                    case 'status':
	                    	$checked = (isset($popup_data->status) && 1==$popup_data->status) ? " checked='checked' " : "";
	                    	$switch = '<span class="arf_js_switch_wrapper">
	                            <input type="checkbox" class="js-switch" name="arf_enable_popup_form_switch" id="arf_enable_popup_form_switch" data-id="'.$popup_data->popup_id.'" value="'.$popup_data->status.'" '.$checked.'>
	                            <span class="arf_js_switch"></span>
	                        </span>';
	                        $data .= "<td class='state_column'>" . $switch . "</td>";
	                        $j++;
	                        break;

						case 'created_date':
							$wp_format_date = get_option('date_format');
	                        if ($wp_format_date == 'F j, Y' || $wp_format_date == 'm/d/Y') {
	                            $date_format_new = 'M d, Y';
	                        } else if ($wp_format_date == 'd/m/Y') {
	                            $date_format_new = 'd M, Y';
	                        } else if ($wp_format_date == 'Y/m/d') {
	                            $date_format_new = 'Y, M d';
	                        } else {
	                            $date_format_new = 'M d, Y';
	                        }
	                        $data .= "<td>" . date($date_format_new, strtotime($popup_data->created_date)) . "</td>";
	                        $j++;
	                        break;

	                    case 'action':
	                        $div = "<div class='arf-row-actions arf_popup_action_row'>";
	                        if (current_user_can('arfeditforms')) {
	                            $edit_link = "?page=ARForms&arfaction=edit&id={$popup_data->popup_id}";
	                            $div .= "<div class='arfformicondiv arfformpopupicondiv arfhelptip' title='" . addslashes(esc_html__('Edit Popup options', 'ARForms')) . "'><a onclick='arf_edit_popup_form(".$popup_data->popup_id.")'><svg width='30px' height='30px' viewBox='-5 -4 30 30' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill='#ffffff' d='M17.469,7.115v10.484c0,1.25-1.014,2.264-2.264,2.264H3.75c-1.25,0-2.262-1.014-2.262-2.264V5.082  c0-1.25,1.012-2.264,2.262-2.264h9.518l-2.264,2.001H3.489v13.042h11.979V9.379L17.469,7.115z M15.532,2.451l-0.801,0.8l2.4,2.401  l0.801-0.8L15.532,2.451z M17.131,0.85l-0.799,0.801l2.4,2.4l0.801-0.801L17.131,0.85z M6.731,11.254l2.4,2.4l7.201-7.202  l-2.4-2.401L6.731,11.254z M5.952,14.431h2.264l-2.264-2.264V14.431z' /></svg></a></div>";

	                            $duplicate_link = "?page=ARForms&arfaction=duplicate&id={$popup_data->popup_id}";

	                            $div .= "<div class='arfformicondiv arfformpopupicondiv arfhelptip' title='" . addslashes(esc_html__('Duplicate Popup', 'ARForms')) . "'><a onclick='arf_duplicate_popup(".$popup_data->popup_id.");'><svg width='30px' height='30px' viewBox='-5 -5 30 30' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#ffffff' d='M16.501,15.946V2.85H5.498v-2h11.991v0.025h1.012v15.07H16.501z   M15.489,19.81h-14V3.894h14V19.81z M13.497,5.909H3.481v11.979h10.016V5.909z'/></svg></a></div>
	                            ";
	                            
	                        }

	                        $analytics_link = admin_url('admin.php?page=ARForms-entries&tabview=analytics&form='.$popup_data->form_id);
	                        $div .= "<div class='arfformicondiv arfformpopupicondiv arfhelptip' title='". addslashes(esc_html__('Analytics','ARForms'))."'><a href='".$analytics_link."'><svg width='30px' height='30px' viewBox='-9 -8 45 45' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill='#ffffff' d='M22.232,26.339V14.245h4.003v12.094H22.232z M15.237,7.345h4.003v18.994h-4.003 V7.345z M8.243,0.239h4.003v26.099H8.243V0.239z M1.248,10.159h4.004v16.128H1.248V10.159z' /></svg></a></div>";


	                            $delete_link = "?page=ARForms&arfaction=destroy&id={$popup_data->popup_id}";
	                            $id = $popup_data->popup_id;
	                            $div .= "<div class='arfformicondiv arfformpopupicondiv arfhelptip arfdeleteform_div_" . $id . "' title='" . addslashes(esc_html__('Delete', 'ARForms')) . "' ><a  id='delete_pop' data-delete_content='arf_delete_popup_list' data-toggle='arfmodal' data-id='" . $id . "' style='cursor:pointer'><svg width='30px' height='30px' viewBox='-5 -5 32 32' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#ffffff' d='M18.435,4.857L18.413,19.87L3.398,19.88L3.394,4.857H1.489V2.929  h1.601h3.394V0.85h8.921v2.079h3.336h1.601l0,0v1.928H18.435z M15.231,4.857H6.597H5.425l0.012,13.018h10.945l0.005-13.018H15.231z   M11.4,6.845h2.029v9.065H11.4V6.845z M8.399,6.845h2.03v9.065h-2.03V6.845z' /></svg></a></div>";

	                        $data .= "<td class='arf_action_cell'>" . $div . "</td>";
	                        $j++;
	                        break;
					}
				}
				$data .= "</tr>";
				$i++;
			}	
		}
		
		$response_arr['data'] = $data;
		$response_arr['count'] = $count;
		return $response_arr;
	}

	function arf_update_popup_state(){
		global $wpdb, $MdlDb;
		if(isset($_POST['popup_id']) && 0!=$_POST['popup_id']){
			$wpdb->update($MdlDb->form_popup,
				array('status' => $_POST['status']),
				array('popup_id' => $_POST['popup_id']),
				array( '%d' ), 
				array( '%d' )
			);
			$message='';
			if(0==$_POST['status']){
				$message = "Popup is now disable.";
			} else{
				$message = "Popup is now enable.";
			}
			$response_arr['state'] = "success";
			$response_arr['message'] = esc_html__($message,'ARForms');	
		} else{
			$message='Something goes wrong wile update Popup status.';
			$response_arr['state'] = "error";
			$response_arr['message'] = esc_html__($message,'ARForms');	
		}
		
		echo json_encode($response_arr);
		die;
	}

	function arf_delete_popup_list(){
		global $wpdb, $MdlDb;
		$response_arr=array();
		if(isset($_POST['popup_id']) && 0!=$_POST['popup_id']) {
			
			$wpdb->delete($MdlDb->form_popup,
				array('popup_id'=>$_POST['popup_id'])
			);
			$response_arr['state'] = "success";
			$response_arr['message'] = esc_html__('Popup is deleted successfully.', 'ARForms');
			
		} else {
			$response_arr['state'] = "error";
			$response_arr['message'] = esc_html__('Something goes wrong while delete Popup.', 'ARForms');
		}

		echo json_encode($response_arr);
		die;
	}

	function arf_duplicate_popup(){
		
		$response_arr = array();
		if(isset($_POST['id'])){
			
			global $wpdb, $MdlDb;
			$wpdb->query($wpdb->prepare("INSERT INTO ".$MdlDb->form_popup."(form_id, popup_type, popup_option, status, created_date) SELECT form_id, popup_type, popup_option, status, created_date FROM ".$MdlDb->form_popup." WHERE popup_id=%d", $_POST['id']));

			if($wpdb->insert_id){
				$response_arr['state'] = "success";
				$response_arr['message'] = esc_html__('Popup copied successfully.', 'ARForms');
				$popup_data = $this->arf_load_popup_list();
				$response_arr['popup_data'] = $popup_data['data'];
				$response_arr['popup_list_count'] = $popup_data['count'];
			} else{
				$response_arr['state'] = "error";
				$response_arr['message'] = esc_html__('Something goes wrong while delete Popup.', 'ARForms');	
			}
		} else{
			
			$response_arr['state'] = "error";
			$response_arr['message'] = esc_html__('Something goes wrong while delete Popup.', 'ARForms');
		}
		echo json_encode($response_arr);
		die;
	}


	function arf_edit_popup_form(){
		
		$response_arr = array();
		if(isset($_POST['id'])){
			global $wpdb, $MdlDb;
			$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$MdlDb->form_popup." WHERE popup_id=%d",$_POST['id']), ARRAY_A);
			$response_arr['state'] = "success";
			$response_arr['popup_data'] = $result;
		} else {
			$response_arr['state'] = "error";
			$response_arr['message'] = esc_html__('Popup does not found for the requested ID.','ARForms');
		}
		echo json_encode($response_arr);
		die;
	}

	function arf_bulk_delete_popup(){
		$response_arr = array();
		if(isset($_POST['data']) && count($_POST['data']) > 0){
			global $wpdb, $MdlDb;
			$ids = implode(",", $_POST['data']);
			$res = $wpdb->query("DELETE FROM ".$MdlDb->form_popup." WHERE popup_id IN(".$ids.")");
			$response_arr['state'] = "success";
			$response_arr['message'] = esc_html__('Popup deleted successfully.', 'ARForms');
		} else{
			$response_arr['state'] = "error";
			$response_arr['message'] = esc_html__('Please select one or more record to perform action.', 'ARForms');
		}
		echo json_encode($response_arr);
		die;
	}
}

global $arforms_popup;
$arforms_popup = new ARForms_Popup();