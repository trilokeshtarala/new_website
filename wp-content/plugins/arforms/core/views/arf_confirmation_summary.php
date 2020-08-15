<?php
global $arf_confirmation_summary;
$arf_confirmation_summary = new arf_submit_confirmation_summary();

class arf_submit_confirmation_summary{

	function __construct(){

		add_action('arf_option_before_submit_conditional_logic', array($this,'arf_submit_confirmation_summary_options'), 12,2 );

		add_filter('arf_save_form_options_outside', array($this,'arf_save_confirmation_summary'), 10,2);

		//add_filter('getsubmitbutton', array($this,'arf_display_summary_button'),10,2);

		add_filter('arf_add_submit_btn_attributes_outside',array($this,'arf_add_submit_btn_attributes_function'),10,2);

		add_filter('arf_additional_form_content_outside',array($this,'arf_add_confirmation_summary_box_outside'),10,5);

		//add_filter('arf_check_for_running_total_field',array($this,'arf_add_confirmation_action_from_outside'),12,5);

		add_filter('arf_additional_form_content_outside',array($this,'arf_add_confirmation_script_from_outside'),100,5);

		add_action('init',array($this,'arf_print_confirmation_summary'),1);

	}

	function arf_submit_confirmation_summary_options($id,$values){
		
		global $armainhelper, $arformcontroller;

		if( !isset($values['arf_confirmation_summary_display']) || (isset($values['arf_confirmation_summary_display']) && $values['arf_confirmation_summary_display'] == '')){
			$values['arf_confirmation_summary_display'] = 'before';
		}
		?>
		<div class="arf_confirmation_summary_container">
			<div class="arf_confirmation_summary_inner_container">
				
				<div class="arf_confirmation_summary_enable">
					<div class="arf_popup_checkbox_wrapper" style="margin-top:5px;">
						<div class="arf_custom_checkbox_div" style="margin-top: 4px;">
							<div class="arf_custom_checkbox_wrapper">
								<input type="checkbox" class="arf_enable_confirmation_summary" name="options[arf_confirmation_summary]" id="arf_confirmation_summary" value="1" <?php isset($values['arf_confirmation_summary']) ? checked($values['arf_confirmation_summary'],1) : ''; ?> />
								<svg width="18px" height="18px">
		                        	<?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
		                        	<?php echo ARF_CUSTOM_CHECKED_ICON; ?>
		                        </svg>
							</div>
							<span>
								<label for="arf_confirmation_summary" style="margin-left:4px;"><?php echo addslashes(esc_html__('Show confirmation (Summary)','ARForms')); ?></label>
							</span>
						</div>
					</div>
				</div>

				<?php
					$arf_enable_confirmation_summary = (isset($values['arf_confirmation_summary']) && $values['arf_confirmation_summary'] == 1) ? '' : 'display:none;';
				?>

				<div class="arf_confirmation_summary_inner_block arfmarginl15" style="<?php echo $arf_enable_confirmation_summary; ?>">
					<div class="arf_confirmation_summary_input_wrapper" style="margin-bottom:10px;">
						<label class="arf_dropdown_autoresponder_label" style="margin-bottom:0px;"> <?php echo addslashes(esc_html__('Display summary','ARForms') ); ?>:</label>
						<div class="arf_radio_wrapper" style="padding-top:8px;">
							<div class="arf_custom_radio_div">
								<div class="arf_custom_radio_wrapper">
									<input type="radio" class="arf_custom_radio arf_confirmation_summary_display_control" name="options[arf_confirmation_summary_display]" id="arf_confirmation_summary_before" value="before" <?php isset($values['arf_confirmation_summary_display']) ? checked($values['arf_confirmation_summary_display'], 'before') : ''; ?> />
									<svg width="18px" height="18px">
										<?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                    	<?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
									</svg>
								</div>
							</div>
							<span>
								<label for="arf_confirmation_summary_before"><?php echo addslashes(esc_html__('Before submitting form','ARForms')); ?></label>
							</span>
						</div>
						<div class="arf_radio_wrapper" style="padding-top:8px;">
							<div class="arf_custom_radio_div">
								<div class="arf_custom_radio_wrapper">
									<input type="radio" class="arf_custom_radio arf_confirmation_summary_display_control" name="options[arf_confirmation_summary_display]" id="arf_confirmation_summary_after" value="after" <?php isset($values['arf_confirmation_summary_display']) ? checked($values['arf_confirmation_summary_display'], 'after') : ''; ?> />
									<svg width="18px" height="18px">
										<?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                    	<?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
									</svg>
								</div>
							</div>
							<span>
								<label for="arf_confirmation_summary_after"><?php echo addslashes(esc_html__('After submitting form','ARForms')); ?></label>
							</span>
						</div>
					</div>
					<?php
						$display_edit_button = "";
						$display_close_button = "display:none;";
						$display_print_button = "display:none;";
						if( isset($values['arf_confirmation_summary_display']) && $values['arf_confirmation_summary_display'] == 'after' ){
							$display_close_button = "";
							$display_edit_button = "display:none;";
							if( isset($values['arf_confirmation_summary_allow_print']) && $values['arf_confirmation_summary_allow_print'] == 1 ){
								$display_print_button = "";
							}
						}
						$display_submit_action_note = "display:none;";
						if( isset($values['success_action']) && $values['success_action'] != 'message' ){
							$display_submit_action_note = "";
						}
					?>
					<div class="arf_confirmation_summary_input_wrapper" id="arf_confirmation_summary_note" style="<?php echo $display_close_button; ?>">
						<ul>
							<li id="arf_display_note_on_success_message" style="<?php echo $display_submit_action_note; ?>"><?php echo addslashes(esc_html__("You have to select",'ARForms')).' "'.addslashes(esc_html__('Display a Message','ARForms')).'" '.addslashes(esc_html__('settings from','ARForms')).' "'.addslashes(esc_html__('Form submission action','ARForms')).'" '.addslashes(esc_html__('displayed above in order to work','ARForms')).' "'.addslashes(esc_html__('display confirmation summary after form submission','ARForms')).'"'; ?>.</li>
							<li><?php echo addslashes(esc_html__('Confirmation summary after form submission only works with Ajax Submission.','ARForms')) ?></li>
						</ul>
					</div>
					<div class="arf_confirmation_summary_input_wrapper" id="arf_confirmation_summary_allow_print" style="<?php echo $display_close_button; ?>">
						<label class="arf_dropdown_autoresponder_label"><?php echo addslashes(esc_html__('Allow user to Print Summary','ARForms')); ?></label>
						<div class="arf_custom_checkbox_div" style="margin-top: 7px;">
							<div class="arf_custom_checkbox_wrapper">
								<input type="checkbox" name="options[arf_confirmation_summary_allow_print]" id="arf_confirmation_summary_allow_print_input" <?php (isset($values['arf_confirmation_summary_allow_print'])) ? checked($values['arf_confirmation_summary_allow_print'],1) : ''; ?> value="1" />
								<svg width="18px" height="18px">
									<?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
									<?php echo ARF_CUSTOM_CHECKED_ICON; ?>
								</svg>
							</div>
							<span>
								<label for="arf_confirmation_summary_allow_print_input"><?php esc_html_e('Yes','ARForms'); ?></label>
							</span>
						</div>
					</div>
					<div class="arf_confirmation_summary_input_wrapper" id="arf_confirmation_summary_confirm_button_wrapper" style="<?php echo $display_edit_button; ?>">
						<label for="arf_confirmation_summary_button_text" class="arf_dropdown_autoresponder_label"><?php echo addslashes(esc_html__('Confirmation Button Label','ARForms')); ?>:</label>
						<input type="text" id="arf_confirmation_summary_button_text" class="arf_large_input_box arf_confirmation_summary_input_box" name="options[arf_confirmation_summary_button_text]" value="<?php echo isset($values['arf_confirmation_summary_button_text']) ? $values['arf_confirmation_summary_button_text'] : addslashes(esc_html__('Confirm','ARForms')); ?>" />
						<span class="arferrmessage" id="arf_confirmation_summary_button_text_error"><?php echo addslashes(esc_html__('This field cannot be blank','ARForms')); ?></span>
					</div>
					
					<div class="arf_confirmation_summary_input_wrapper" id="arf_confirmation_summary_edit_button_wrapper" style="<?php echo $display_edit_button; ?>">
						<label for="arf_confirmation_summary_edit_button_text" class="arf_dropdown_autoresponder_label"><?php echo addslashes(esc_html__('Edit Button Label','ARForms')); ?>:</label>
						<input type="text" id="arf_confirmation_summary_edit_button_text" class="arf_large_input_box arf_confirmation_summary_input_box" name="options[arf_confirmation_summary_edit_button_text]" value="<?php echo isset($values['arf_confirmation_summary_edit_button_text']) ? $values['arf_confirmation_summary_edit_button_text'] : addslashes(esc_html__('Edit','ARForms')); ?>" />
						<span class="arferrmessage" id="arf_confirmation_summary_edit_button_text_error"><?php echo addslashes(esc_html__('This field cannot be blank','ARForms')); ?></span>
					</div>
					
					<div class="arf_confirmation_summary_input_wrapper" id="arf_confirmation_summary_print_button_wrapper" style="<?php echo $display_print_button; ?>">
						<label for="arf_confirmation_summary_print_button_text" class="arf_dropdown_autoresponder_label"><?php echo addslashes(esc_html__('Print Button Label','ARForms') ); ?>:</label>
						<input type="text" id="arf_confirmation_summary_print_button_text" class="arf_large_input_box arf_confirmation_summary_input_box" name="options[arf_confirmation_summary_print_button_text]" value="<?php echo isset($values['arf_confirmation_summary_print_button_text']) ? $values['arf_confirmation_summary_print_button_text'] : addslashes(esc_html__('Print','ARForms')); ?>" />
					</div>
					
					<div class="arf_confirmation_summary_input_wrapper" id="arf_confirmation_summary_close_button_wrapper" style="<?php echo $display_close_button; ?>">
						<label for="arf_confirmation_summary_close_button_text" class="arf_dropdown_autoresponder_label"><?php echo addslashes(esc_html__('Close Button Label','ARForms') ); ?>:</label>
						<input type="text" id="arf_confirmation_summary_close_button_text" class="arf_large_input_box arf_confirmation_summary_input_box" name="options[arf_confirmation_summary_close_button_text]" value="<?php echo isset($values['arf_confirmation_summary_close_button_text']) ? $values['arf_confirmation_summary_close_button_text'] : addslashes(esc_html__('Close','ARForms')); ?>" />
						<span class="arferrmessage" id="arf_confirmation_summary_close_button_text_error"><?php echo addslashes(esc_html__("This field cannot be blank",'ARForms')); ?></span>
					</div>
				</div>
			</div>
		</div>
		<?php		

	}

	function arf_save_confirmation_summary($options,$values){

		$options['arf_confirmation_summary'] = isset($values['options']['arf_confirmation_summary']) ?  $values['options']['arf_confirmation_summary'] : '';

		$options['arf_confirmation_summary_button_text'] = isset($values['options']['arf_confirmation_summary_button_text']) ? $values['options']['arf_confirmation_summary_button_text'] : '';

		$options['arf_confirmation_summary_edit_button_text'] = isset($values['options']['arf_confirmation_summary_edit_button_text']) ? $values['options']['arf_confirmation_summary_edit_button_text'] : '';

		$options['arf_confirmation_summary_close_button_text'] = isset($values['options']['arf_confirmation_summary_close_button_text']) ? $values['options']['arf_confirmation_summary_close_button_text'] : '';

		$options['arf_confirmation_summary_display'] = isset($values['options']['arf_confirmation_summary_display']) ? $values['options']['arf_confirmation_summary_display'] : 'before';

		$options['arf_confirmation_summary_allow_print'] = isset($values['options']['arf_confirmation_summary_allow_print']) ? $values['options']['arf_confirmation_summary_allow_print'] : '';

		$options['arf_confirmation_summary_print_button_text'] = isset($values['options']['arf_confirmation_summary_print_button_text']) ? $values['options']['arf_confirmation_summary_print_button_text'] : '';

		return $options;
	}

	function arf_display_summary_button($submit,$form){
		
		if( !isset($form) || empty($form) ){
			return $submit;
		}

		$display_summary_buttons = ( isset($form->options['arf_confirmation_summary']) && $form->options['arf_confirmation_summary'] == 1 ) ? true : false;

		$display_summary_on = ( isset($form->options['arf_confirmation_summary_display']) && $form->options['arf_confirmation_summary_display'] != '' ) ? $form->options['arf_confirmation_summary_display'] : 'before';

		if( $display_summary_on == 'after'){
			return $submit;
		}

		if( !$display_summary_buttons ){
			return $submit;
		}

		$submit = isset($form->options['arf_confirmation_summary_button_text']) ? $form->options['arf_confirmation_summary_button_text'] : esc_html__('Confirm','ARForms');

		return $submit;
	}

	function arf_add_submit_btn_attributes_function( $submit_content, $form){

		if( !isset($form) || empty($form) ){
			return $submit_content;
		}

		$display_summary_buttons = ( isset($form->options['arf_confirmation_summary']) && $form->options['arf_confirmation_summary'] == 1 ) ? true : false;

		if( !$display_summary_buttons ){
			return $submit_content;
		}

		$display_summary_on = ( isset($form->options['arf_confirmation_summary_display']) ) ? $form->options['arf_confirmation_summary_display'] : 'before';

		$submit_content .= ' data-arf-confirm="true" data-arf-display-confirmation="'.$display_summary_on.'"';

		return $submit_content;		

	}

	function arf_add_confirmation_summary_box_outside($arf_form, $form, $form_data_id,$arfbrowser_name,$browser_info){


		if( !isset($form) || empty($form) ){
			return $arf_form;
		}

		$display_summary_buttons = ( isset($form->options['arf_confirmation_summary']) && $form->options['arf_confirmation_summary'] == 1 ) ? true : false;

		$summary_display_position = ( isset($form->options['arf_confirmation_summary_display']) && $form->options['arf_confirmation_summary_display'] != '' ) ? $form->options['arf_confirmation_summary_display'] : 'before';

		if( !$display_summary_buttons ){
			return $arf_form;
		}

		$submit = $form->options['submit_value'];

		$inputStyle = $form->form_css['arfinputstyle'];

		$wrapper_class = 'arf_materialize_form';
		if( $inputStyle == 'standard' ){
			$wrapper_class = 'arf_standard_form';
		} else if( $inputStyle == 'rounded' ){
			$wrapper_class = 'arf_rounded_form';
		}

		$arf_form .= "<div class='arf_confirmation_summary_wrapper {$wrapper_class}' data-confirmation-display='{$summary_display_position}' id='arf_confirmation_summary_wrapper_{$form_data_id}' style='display:none;' >";

		$arf_form .= "<input type='hidden' id='arf_submit_form_after_confirm_{$form_data_id}'  value='false' />";

		$arf_form .= "<div class='arftitlecontainer'>";

		$arf_form .= "<div class='formtitle_style'>";

			$arf_form .= html_entity_decode(stripslashes($form->name));

		$arf_form .= "</div>";

		$arf_form .= "</div>";

		$arf_form .= "<div class='arf_confirmation_summary_inner_wrapper'>";

		$arf_form .= "</div>";

		$submit_height = ($form->form_css['arfsubmitbuttonheightsetting'] == '') ? '35' : $form->form_css['arfsubmitbuttonheightsetting'];
        $padding_loading_tmp = $submit_height - 24;
        $padding_loading = $padding_loading_tmp / 2;

		$submitbtnclass = '';

		$sbmt_class = "";
        if( $inputStyle == 'material' ){
            $sbmt_class = "btn btn-flat";
        }

        $submit_btn_content = "<div class='arfsubmitbutton arf_confirmation_summary_submit_wrapper'>";

        $arf_modify_button_content = isset($form->options['arf_confirmation_summary_edit_button_text']) ? $form->options['arf_confirmation_summary_edit_button_text'] : addslashes(esc_html__('Modify','ARForms'));

        $arf_close_button_content = isset($form->options['arf_confirmation_summary_close_button_text']) ? $form->options['arf_confirmation_summary_close_button_text'] : addslashes(esc_html__('Close','ARForms'));

        $arf_print_button_content = isset($form->options['arf_confirmation_summary_print_button_text']) ? $form->options['arf_confirmation_summary_print_button_text'] : addslashes(esc_html__('Print','ARForms'));
        
        $arf_confirm_button_content = isset($form->options['arf_confirmation_summary_button_text']) ? $form->options['arf_confirmation_summary_button_text'] : addslashes(esc_html__('Confirm','ARForms'));

        $arf_allow_print = isset($form->options['arf_confirmation_summary_allow_print']) ? $form->options['arf_confirmation_summary_allow_print'] : false;
        
        if( $summary_display_position == 'before' ){
        	$submit_btn_content .= "<input type='button' class='previous_btn arf_modify_button' data-form-id='{$form->id}' data-form-unique-id='{$form_data_id}' value='{$arf_modify_button_content}' />";
        } else {
        	$submit_btn_content .= "<input type='button' class='previous_btn arf_modify_button' data-form-id='{$form->id}' data-form-unique-id='{$form_data_id}' value='{$arf_close_button_content}' />";        	
        }

        if( $summary_display_position == 'before' ){
			$submit_btn_content .= '<button class="arf_submit_btn '.$sbmt_class.' btn-info arf_submit_after_confirm arfstyle-button ' . $submitbtnclass .' '.$arfbrowser_name.'"  id="arf_submit_btn_' . $form_data_id . '_confirm" name="arf_submit_btn_' . $form_data_id . '" data-style="zoom-in" >';

			$submit_btn_content .= '<span class="arfsubmitloader"></span><span class="arfstyle-label">' . esc_attr($arf_confirm_button_content) . '</span>';

			if (( $browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '9' ) || $browser_info['name'] == 'Opera') {
	            $padding_loading = isset($padding_loading) ? $padding_loading : '';
	            $submit_btn_content .= '<span class="arf_ie_image" style="display:none;">';
	            $submit_btn_content .= '<img src="' . ARFURL . '/images/submit_btn_image.gif" style="width:24px; box-shadow:none;-webkit-box-shadow:none;-o-box-shadow:none;-moz-box-shadow:none; vertical-align:middle; height:24px; padding-top:' . $padding_loading . 'px;"/>';
	            $submit_btn_content .= '</span>';
	        }
	        
	        $submit_btn_content .= '</button>';
        } else {

        	if( $arf_allow_print ){
        		$submit_btn_content .= "<button type='button' class='arf_submit_btn {$sbmt_class} arf_print_summary' data-home-url='".home_url()."' data-form-unique-id='{$form_data_id}'>";
        		$submit_btn_content .= $arf_print_button_content;
        		$submit_btn_content .= "</button>";
        	}

        }

        $submit_btn_content .= '</div>';

        $arf_form .= $submit_btn_content;

		$arf_form .= "</div>";

		return $arf_form;
	}

	function arf_add_confirmation_action_from_outside($arf_on_change_function,$field,$data_unique_id,$form,$res_data){
		global $arf_form_all_footer_js,$trigger_fields_on_load;
		if( !isset($form) || empty($form) ){
			return $arf_on_change_function;
		}

		$form_options = maybe_unserialize($form->options);

		$display_summary_buttons = ( isset($form_options['arf_confirmation_summary']) && $form_options['arf_confirmation_summary'] == 1 ) ? true : false;

		if( !$display_summary_buttons ){
			return $arf_on_change_function;
		}

		$exclude_for_summary = array('hidden','break','file', 'arf_product','arf_signature','imagecontrol','captcha','confirm_email','password','confirm_password');

		$exclude_for_summary = apply_filters('arf_exclude_field_for_confirmation_summary',$exclude_for_summary,$field);

		$onchange_fields = array('checkbox', 'radio', 'scale', 'select', 'arfslider', 'arf_smiley','like', 'date','time','colorpicker','arf_switch');
		if( !isset($trigger_fields_on_load) ){
			$trigger_fields_on_load = array();
			$trigger_fields_on_load[$form->id] = array();
		}
		$arf_on_change_function = trim($arf_on_change_function);
		if( $arf_on_change_function == '' ){
			if( !in_array($field['type'],$exclude_for_summary) ){
				if( in_array($field['type'],$onchange_fields) ){
					$arf_on_change_function .= " onchange='clearTimeout(__arf_confirm_handle); __arf_confirm_handle = setTimeout(function(){arf_add_field_to_summary(\"".$data_unique_id."\",\"{$field['id']}\",\"{$field['type']}\");},100);'";
				} else {
					$arf_on_change_function .= " onkeyup='clearTimeout(__arf_confirm_handle); __arf_confirm_handle = setTimeout(function(){arf_add_field_to_summary(\"".$data_unique_id."\",\"{$field['id']}\",\"{$field['type']}\");},100);'";
				}
			}
		} else {
			$arf_on_change_function = substr($arf_on_change_function,0,-1);
			
			if( !in_array($field['type'],$exclude_for_summary) ){
				$arf_on_change_function .= " clearTimeout(__arf_confirm_handle); __arf_confirm_handle = setTimeout(function(){arf_add_field_to_summary(\"".$data_unique_id."\",\"{$field['id']}\",\"{$field['type']}\");},100);'";
			}

		}
		if( !in_array($field['type'],$exclude_for_summary) ){
			$trigger_fields_on_load[$form->id][] = "arf_add_field_to_summary(\"".$data_unique_id."\",\"{$field['id']}\",\"{$field['type']}\");";
		}

		return $arf_on_change_function;
	}

	function arf_add_confirmation_script_from_outside($arf_form, $form, $form_data_id,$arfbrowser_name,$browser_info){
		global $trigger_fields_on_load;

		if( isset($trigger_fields_on_load[$form->id]) && count($trigger_fields_on_load[$form->id]) > 0 ){
			$arf_form .= "<script type='text/javascript' data-cfasync='false'>jQuery(document).ready(function(){";
			
			foreach( $trigger_fields_on_load[$form->id] as $k => $val ){
				$arf_form .= "setTimeout(function(){
					".$val."
				},1000);";
			}

			$arf_form .= "});</script>";
		}

		return $arf_form;
	}

	function arf_print_confirmation_summary(){
		if( isset($_REQUEST['arf_action']) && $_REQUEST['arf_action'] == 'arf_print_summary'){
			?>
			<html>
				<head>
					<!-- <script type="text/javascript" src="< ?php echo ARFURL.'/js/html2canvas.js'; ?>"></script> -->
					<script type="text/javascript">
						function arf_cf_print_data(){
							var summary_wrapper_id = '<?php echo 'arf_confirmation_summary_wrapper_'.$_REQUEST['data-id']; ?>';
							var doc = window.opener.document.getElementById(summary_wrapper_id).parentNode;
							
							if( doc == null ){
								window.close();
							}

							/*html2canvas(doc,{
								onrendered: function (canvas) {
									var img = canvas.toDataURL("image/png");
									var container = document.createElement('container');
									container.setAttribute('class','arf_confirmation_summary_table');

									var imageSrc = document.createElement('img');
									imageSrc.src = img;									

									container.appendChild(imageSrc);

									
								}
							});*/

							var image_fields = doc.querySelectorAll('.arf_image_field');
							var image_fields_len = image_fields.length;

							for( var fi = 0; fi < image_fields_len; fi++ ){
								var cur_img_field = image_fields[fi].innerHTML;

								if( image_fields[fi].style.left != '' ){
									var image_field_style = "left:"+image_fields[fi].style.left+';';
								} else if( image_fields[fi].style.right != '') {
									image_field_style += "right:"+image_fields[fi].style.right+';';
								}
								
								if( image_fields[fi].style.top != '' ){
									image_field_style += "top:"+image_fields[fi].style.top+';';
								} else if( image_fields[fi].style.bottom != '' ){
									image_field_style += "bottom:"+image_fields[fi].style.bottom+';';									
								}

								var imagefield_container = document.createElement('div');								
								imagefield_container.setAttribute('class','arf_imagefield_container');
								imagefield_container.setAttribute('style' ,image_field_style);

								imagefield_container.innerHTML = cur_img_field;
								document.getElementById('arf_cnf_summary_page').appendChild(imagefield_container);
							}

							var title = doc.querySelector('.formtitle_style').innerHTML;

							var container = document.createElement('div');
							container.setAttribute('class','arf_confirmation_summary_title');
							container.innerHTML = title;

							var title_tag = document.createElement('title');
							title_tag.innerHTML = 'ARForms | '+ title;

							document.getElementsByTagName('head')[0].appendChild(title_tag);

							document.getElementById('arf_cnf_summary_page').appendChild(container);

							var table_wrapper = document.createElement('table');
							table_wrapper.setAttribute('class','arf_confirmation_summary_table');
							table_wrapper.setAttribute('cellspacing','0');
							table_wrapper.setAttribute('cellpadding','0');
							table_wrapper.setAttribute('border','0');

							var rows = doc.querySelectorAll('.arf_confirmation_summary_row_wrapper');

							var total_rows = rows.length;

							if( total_rows > 0 ){
								for(var tr = 0; tr < total_rows; tr++ ){

									var current_tr = rows[tr];

									var tr_container = document.createElement('tr');
									var tr_classList = ( parseInt(tr) % 2 == 0 ) ? 'arf_confirmation_summary_row arf_confirmation_summary_even_row' : 'arf_confirmation_summary_row arf_confirmation_summary_odd_row';
									tr_container.setAttribute('class',tr_classList);

									if( current_tr.querySelector('.arf_confirmation_summary_label_full_width') != null ){

										var td_center = document.createElement('td');
										td_center.setAttribute('colspan','2');
										current_tr.setAttribute('rowspan','2');
										td_center.innerHTML = current_tr.querySelector('.arf_confirmation_summary_label_full_width').innerHTML;
										tr_container.appendChild(td_center);

									} else {
										var td_left = document.createElement('td');
										td_left.innerHTML = current_tr.querySelector('.arf_confirmation_summary_label').innerHTML;
										tr_container.appendChild(td_left);

										var td_right = document.createElement('td');
										td_right.innerHTML = current_tr.querySelector('.arf_confirmation_summary_input').innerHTML;
										tr_container.appendChild(td_right);
									}

									table_wrapper.appendChild(tr_container);
								}
							}

							document.getElementById('arf_cnf_summary_page').appendChild(table_wrapper);
							window.print();
						}
					</script>
					<style type="text/css">
			            body {
			                margin: 0 auto;
			                padding: 0;
			                font: 12pt "Tahoma";
			            }
			            * {
			                box-sizing: border-box;
			                -webkit-box-sizing: border-box;
			                -o-box-sizing: border-box;
			                -moz-box-sizing: border-box;
			            }
			            .arf_cnf_summary_page {
						    width: 21cm;
			                min-height: 25.7cm;
			                padding:1cm 0;
			                margin: 1cm auto;
			                border-radius: 5px;
			                background: white;
			                position: relative;
						}
						.arf_imagefield_container{
							position: absolute;
						}
						.arf_confirmation_summary_title {
						    float: left;
						    width: 100%;
						    font-size: 24px;
						    text-align: center;
						    padding: 10px;
						}
						.arf_confirmation_summary_table{
							float:left;
							width:100%;
							padding:30px 0px;
							display: block;
						}
						tr.arf_confirmation_summary_row {
						    float: left;
						    width: 100%;
						    font-size:14px;
						    border:1px solid #ccc;
						    border-bottom:none;
						}
						tr.arf_confirmation_summary_row:last-child{
							border-bottom:1px solid #ccc;
						}
						tr.arf_confirmation_summary_row td{
							vertical-align: middle;
						    width: 60%;
						    min-height: 40px;
						    padding: 12px;
						}
						tr.arf_confirmation_summary_row td:first-child {
						    width: 400px;
						    font-weight: bold;
						    border-right:1px solid #ccc;
						}

						tr.arf_confirmation_summary_row td[colspan="2"] {
						    border: none !important;
						    width: 100% !important;
						}
						@page {
						    size: A4;
						    margin:0;
						}
						@media print {
						    .page {
						        margin: 0;
						        border: initial;
						        border-radius: initial;
						        width: initial;
						        min-height: initial;
						        box-shadow: initial;
						        background: initial;
						        page-break-after: always;
						    }
						}
			        </style>
				</head>
				<body onload="arf_cf_print_data()">
					<div class="arf_cnf_summary_page" id="arf_cnf_summary_page">
					</div>
				</body>
			</html>
			<?php
			die;
		}
	}

}