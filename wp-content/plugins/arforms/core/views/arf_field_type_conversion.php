<?php

class arf_file_type_conversion{

	function __construct(){

		add_action('arf_editor_general_options_menu',array($this,'arf_add_general_option_menu'));

		add_action('arf_add_extra_editor_script',array($this,'arf_display_field_conversion_option'));

		add_action('arf_add_modal_in_editor',array($this,'arf_add_field_conversion_modal'),10);

		add_action('arf_display_additional_css_in_editor',array($this,'arf_field_converter_model_style'));
	}

	function arf_add_general_option_menu(){
		$show_convert_field_menu = "display:none;";
		if( isset($_GET['arfaction']) && $_GET['arfaction'] == 'edit' ){
			$show_convert_field_menu = "";
		}
		echo '<li class="arf_editor_top_dropdown_option" id="arf_field_type_converter" style="'.$show_convert_field_menu.'">'.addslashes(esc_html__('Convert Field Type', 'ARForms')).'</li>';

	}

	function arf_display_field_conversion_option(){
	?>
		arf_add_action('arf_after_save_form_first_time','arf_display_field_conversion_option_after_save');
		function arf_display_field_conversion_option_after_save(){
			jQuery("#arf_field_type_converter").show();
		}

		jQuery(document).on('click','#arf_field_type_converter',function(){
			jQuery("#arf_field_type_converter_model").addClass('arfactive');
			jQuery("#arf_field_type_converter_model").parent().addClass('arfactive');
		});

		function arf_field_type_conversion_array(){

			var arf_supported_field_types;
			arf_supported_field_types = '<?php global $arfieldhelper; echo json_encode($this->arf_migrate_field_type()); ?>';
			return arf_supported_field_types;

		}

		arf_add_action('arf_set_field_type_for_outside_options','arf_set_field_type_option_for_field_type_converter');

		function arf_set_field_type_option_for_field_type_converter(){
			var params = arguments[0];
			var id = params[0];
			var field_type = params[1];

			var supported_field_type = jQuery.parseJSON(arf_field_type_conversion_array());
			
			if( id == 'field_type_converter'){

				jQuery("#arf_current_field_type").val(field_type);

				jQuery('.arf_ar_dropdown_wrapper_note_current_type').css('display','none');
				jQuery('ul[data-id="field_type_to_convert"]').find('li').removeAttr('data-field-in-condition');
				jQuery('ul[data-id="field_type_to_convert"]').find('li').removeClass('arfhidden').addClass('arfvisible');
				
				if( typeof field_type != 'undefined' && field_type != '' ){
					var supported_field_type = jQuery.parseJSON(arf_field_type_conversion_array());

					jQuery('.arf_current_field_type').html(supported_field_type[field_type]);
					jQuery('.arf_ar_dropdown_wrapper_note_current_type').css('display','block');

					jQuery('ul[data-id="field_type_to_convert"]').find('li[data-type="'+field_type+'"]').removeClass('arfvisible').addClass('arfhidden');

					if( jQuery("#field_type_to_convert").val() == field_type ){
						var label = jQuery('ul[data-id="field_type_to_convert"]').find('li:first-child').attr('data-label');
						jQuery("#field_type_to_convert").val('');
						jQuery('dl[data-name="field_type_to_convert"]').find('dt span').html(label);
					}
				} else {
					jQuery('.arf_current_field_type').html('');
				}
			}

			var old_field_type = jQuery('#arf_current_field_type').val();
			var new_field_type = jQuery("#field_type_to_convert").val();


			var confirm_options_and_value_message = '';
			var field_values_remove_message = '<?php echo '<li>'; ?> '+ supported_field_type[old_field_type] +' <?php echo esc_html__('Field values will be lost once converted to','ARForms'); ?> '+supported_field_type[new_field_type]+' <?php echo esc_html__('type','ARForms').'</li>'; ?>';
			var confirm_options_value = '';
			var confirm_field_remove  = '';
			
			var conversion_message = '<?php echo '<li>'.esc_html__('You are converting','ARForms'); ?> '+supported_field_type[old_field_type]+' <?php echo esc_html__('type to','ARForms'); ?> ' + supported_field_type[new_field_type] + ' <?php echo addslashes(esc_html__('type, field options will be different from','ARForms')) ?> '+ supported_field_type[old_field_type] +' <?php echo esc_html__('to','ARForms'); ?> '+ supported_field_type[new_field_type] +'. <?php echo addslashes(esc_html__('Please do needful','ARForms')).'</li>'; ?>';

			var other_config_notice = '<?php echo '<li style="height:3px;">&nbsp;<li><li>'.addslashes(esc_html__('Field type changing also may affect email notification section, conditional rule section, payment gateways configuration and other add-ons configuration. So it is highly recommend to verify all these settings after changing field type','ARForms')).'</li>' ?>';

			confirm_options_and_value_message = conversion_message  + other_config_notice;
			field_values_remove_message += conversion_message + other_config_notice;
			confirm_options_value = conversion_message + other_config_notice;
			
			if( old_field_type == 'checkbox' ){
				if( new_field_type != 'radio' && new_field_type != 'select' && new_field_type != 'arf_autocomplete' ){
					jQuery('.arf_ar_dropdown_wrapper_note_changing_type').html(field_values_remove_message);
				} else {
					jQuery('.arf_ar_dropdown_wrapper_note_changing_type').html(confirm_options_and_value_message);
				}
			} else if( old_field_type == 'radio' ){
				if( new_field_type != 'checkbox' && new_field_type != 'select' && new_field_type != 'arf_autocomplete' ){
					jQuery('.arf_ar_dropdown_wrapper_note_changing_type').html(field_values_remove_message);
				} else {
					jQuery('.arf_ar_dropdown_wrapper_note_changing_type').html(confirm_options_and_value_message);
				}
			} else if( old_field_type == 'select' ){
				if( new_field_type != 'radio' && new_field_type != 'checkbox' && new_field_type != 'arf_autocomplete' ){
					jQuery('.arf_ar_dropdown_wrapper_note_changing_type').html(field_values_remove_message);
				} else {
					jQuery('.arf_ar_dropdown_wrapper_note_changing_type').html(confirm_options_and_value_message);
				}
			} else if( old_field_type == 'arf_autocomplete' ){
				if( new_field_type != 'radio' && new_field_type != 'select' && new_field_type != 'checkbox' ){
					jQuery('.arf_ar_dropdown_wrapper_note_changing_type').html(field_values_remove_message);
				} else {
					jQuery('.arf_ar_dropdown_wrapper_note_changing_type').html(confirm_options_and_value_message);
				}
			} else if( old_field_type == 'email' || old_field_type == 'password' ) {
				jQuery('.arf_ar_dropdown_wrapper_note_changing_type').html(confirm_options_value+confirm_field_remove);
			} else {
				jQuery('.arf_ar_dropdown_wrapper_note_changing_type').html(confirm_options_value);
			}
			if( old_field_type != '' && new_field_type != '' ){
				jQuery('.arf_ar_dropdown_wrapper_note_changing_type').show();
			} else {
				jQuery('.arf_ar_dropdown_wrapper_note_changing_type').hide();
			}
		}

		jQuery(document).on('click','.arf_popup_close_button_field_converter',function(){


			var current_field_type = jQuery("#arf_current_field_type").val();
			var field_type_change_to = jQuery("#field_type_to_convert").val();

			if( current_field_type == '' ){
				return false;
			} else if( field_type_change_to == '' ){
				return false;
			}

			jQuery("#arf_field_converter_loader").show();
			
			var field_id = jQuery("#field_type_converter").val();

			var current_field_data = arf_retrieve_field_data(field_id);

			var json_object = arfSkinJson();
			var field_data = json_object.field_data;

			var changing_field_data = field_data[field_type_change_to];

			var input_style = jQuery("#arfmainforminputstyle").val();
		    if (input_style == 'material') {
		        var json_field_data = arf_parse_json(convert_new_materialize_field_array_json());
		    } else {
		        var json_field_data = arf_parse_json(convert_new_field_array_json());
		    }
		    var json_newfield_data = json_field_data[field_type_change_to];

		    var new_field_json_data = {};

		    for( var key in changing_field_data ){
		    	if( typeof current_field_data[key] != 'undefined' ){
		    		if( key != 'type' ){
		    			new_field_json_data[key] = current_field_data[key];
		    		} else {
		    			new_field_json_data[key] = changing_field_data[key];
		    		}
		    	} else {
		    		new_field_json_data[key] = changing_field_data[key];
		    	}
			}

			var form_id = jQuery('#id').val();
			var arf_unique_key = new_field_json_data.key;
			var arf_editor_index_row = jQuery("#arfmainfieldid_"+field_id).parents('.arf_inner_wrapper_sortable').attr('data-id').replace('arf_editor_main_row_','');

			json_newfield_data = json_newfield_data.replace(/\{arf_field_id\}/gi, field_id);
		    json_newfield_data = json_newfield_data.replace(/\{arf_form_id\}/gi, form_id);
		    json_newfield_data = json_newfield_data.replace(/\{arf_unique_key\}/gi, arf_unique_key);
		    json_newfield_data = json_newfield_data.replace(/\{arf_editor_index_row\}/gi, arf_editor_index_row);

		    if( jQuery("#arfmainfieldid_"+field_id).parents('.arf_inner_wrapper_sortable').hasClass('single_column_wrapper') ){
				jQuery("#arfmainfieldid_"+field_id).parents('.arf_inner_wrapper_sortable').replaceWith( jQuery(json_newfield_data) );
			} else {
				var inner_class = jQuery("#arfmainfieldid_"+field_id).attr('inner_class');
				var style = jQuery("#arfmainfieldid_"+field_id).attr('style');
				var data_width = jQuery("#arfmainfieldid_"+field_id).attr('data-width');
				
				var new_control = jQuery(json_newfield_data).find('.sortable_inner_wrapper');

				new_control.attr('inner_class',inner_class);
				new_control.attr('style',style);
				new_control.attr('data-width',data_width);
				jQuery("#arfmainfieldid_"+field_id).replaceWith( new_control );
			}

			var new_field_data = JSON.stringify(new_field_json_data);

			jQuery("#arf_field_data_"+field_id).val(new_field_data).trigger('change');

			jQuery("#field_type_converter").val('');
			jQuery("#arf_current_field_type").val('');
			jQuery("#field_type_to_convert").val('');

			jQuery(".arf_current_field_type").html('');

			jQuery('ul[data-id="field_type_converter"]').find('li[data-value="'+field_id+'"]').attr('data-type',new_field_json_data.type);

			var new_field_type = new_field_json_data.type;

			arf_load_bootstrap_js_css(new_field_type,field_id);

			arfshowfieldoptions(field_id,new_field_type);

			jQuery(".arf_field_option_model_cloned.arfactive").css('visibility','hidden');
			jQuery(".arf_field_values_model.arfactive").css('visibility','hidden');

			if( new_field_type == 'checkbox' || new_field_type == 'radio' || new_field_type == 'select' || new_field_type == 'arf_autocomplete' ){
				jQuery("#arf_edit_value_option_button[data-field-id='"+field_id+"']").trigger('click');
			}

			setTimeout(function(){
				
				var label = jQuery('ul[data-id="field_type_converter"]').find('li:first-child').attr('data-label');
				jQuery('dl[data-name="field_type_converter"]').find('dt span').html(label);

				var label = jQuery('ul[data-id="field_type_to_convert"]').find('li:first-child').attr('data-label');
				jQuery('dl[data-name="field_type_to_convert"]').find('dt span').html(label);

				jQuery(".arf_ar_dropdown_wrapper_note_current_type").hide();

				jQuery('.arf_popup_container.arfactive').removeClass('arfactive');
				jQuery('.arf_modal_overlay.arfactive').removeClass('arfactive');
				jQuery("#arf_field_converter_loader").hide();
				jQuery(".arf_field_option_model_cloned.arfactive").css('visibility','visible');
				jQuery(".arf_field_values_model.arfactive").css('visibility','visible');
				jQuery('.arf_ar_dropdown_wrapper_note_changing_type').hide();
				jQuery(".arf_field_option_submit_button[data-field_id='"+field_id+"']").trigger('click');
				if( new_field_type == 'checkbox' || new_field_type == 'radio' || new_field_type == 'select' || new_field_type == 'arf_autocomplete' ){
					jQuery(".arf_field_values_submit_button[data-field-id='"+field_id+"']").trigger('click');
				}

				if( current_field_data.type == 'email' && current_field_data.confirm_email == 1 ){

					if( jQuery("#arfmainfieldid_"+field_id+"_confirm").parent().hasClass('single_column_wrapper') ){
						jQuery("#arfmainfieldid_"+field_id+"_confirm").parent().remove();
					} else {
						jQuery(".arf_confirm_field#arf_field_"+field_id+"_confirm:not(.sortable_inner_wrapper)").remove();
					}

				} else if ( current_field_data.type == 'password' && current_field_data.confirm_password == 1  ){
					if( jQuery("#arfmainfieldid_"+field_id+"_confirm").parent().hasClass('single_column_wrapper') ){
						jQuery("#arfmainfieldid_"+field_id+"_confirm").parent().remove();
					} else {
						jQuery(".sortable_inner_wrapper#arf_field_"+field_id+"_confirm").remove();
					}
				}
				setTimeout(function(){
					removeBlankElm();
				},500);
			},500);
		});

		arf_add_action('arf_update_name_dropdown_outside','arf_update_type_conversion_list');

		function arf_update_type_conversion_list(){
			var params = arguments[0][0];
			var field_id = params[0];
			var field_type = params[1];
			var field_name = params[2];

			var supported_field_type = jQuery.parseJSON(arf_field_type_conversion_array());		

			var name_field_dropdown = document.getElementsByClassName('arf_change_type_conversion_dropdown');
			var total_field_dropdown = name_field_dropdown.length;
			for( var i = 0; i < total_field_dropdown; i++ ){
				var $that1 = name_field_dropdown[i];

				if( typeof supported_field_type[field_type] != 'undefined' ){
					if ($that1.querySelector('li[data-value="' + field_id + '"]') != null) {
	                    $that1.querySelector('li[data-value="' + field_id + '"]').innerHTML = field_name;
	                    $that1.querySelector('li[data-value="' + field_id + '"]').setAttribute("data-label", field_name);
	                    $that1.querySelector('li[data-value="' + field_id + '"]').setAttribute('data-type', field_type);
	                } else {
	                    var field_opt = document.createElement('li');
	                    field_opt.setAttribute('class','arf_selectbox_option');
	                    field_opt.setAttribute('data-type',field_type);
	                    field_opt.setAttribute('data-value',field_id);
	                    field_opt.setAttribute('data-label',field_name);
	                    field_opt.appendChild(document.createTextNode(field_name));
	                    $that1.appendChild(field_opt);
	                }
	            }
			}
		}

		arf_add_action('arf_delete_name_dropdown_outside','arf_delete_type_conversion_list');

		function arf_delete_type_conversion_list(){
			var params = arguments[0][0];
			var field_id = params[0];
			var f_id = params[1];
		}

	<?php
	}

	function arf_field_converter_model_style(){
	?>
		<style type="text/css">
			#arf_field_type_converter_model{
				height: 60%;
		        min-height: 60%;
		        max-height: 60%;
		        width: 50%;
		        max-width: 50%;
			}
			.arf_field_converter_option_container{
				min-height: 75%;
			    max-height: 80%;
			    overflow-y: auto;
			    overflow-x: hidden;
			    padding-left:10px;
			}
			.arf_field_type_conversion_container{
				float: left;
			    width: 100%;
			    min-height: 155px;
			    height: auto;
			    margin-bottom: 10px;
			    text-align: left;
			}
			.arf_field_type_conversion_container .arf_ar_dropdown_wrapper{
				float:left;
				width:100%;
				margin-bottom:10px;
			}
			.arf_field_type_conversion_container .arf_ar_dropdown_wrapper label.arf_dropdown_autoresponder_label{
				float: left;
			    height: 30px;
			    vertical-align: middle;
			    width: 150px;
			    margin-right: 10px;
			    text-align: right;
			    line-height: 32px;
			}
			.arf_field_type_conversion_container .arf_ar_dropdown_wrapper dl.arf_selectbox{
				float:left;
			}
			.arf_ar_dropdown_wrapper_note_current_type,
			.arf_ar_dropdown_wrapper_note_changing_type{
			    float: left;
			    width: 100%;
			    font-family: Asap-regular;
			    height: 28px;
			    margin-bottom: 5px;
			    font-size:15px;
			}
			.arf_ar_dropdown_wrapper_note_changing_type{
			    padding-left: 150px;
			    display: none;
				font-style: italic;
				color:#ff0000;
				height: auto;
			}
			.arf_current_field_type{
				font-family: Asap-Medium;
			    height: 30px;
			    display: inline-block;
			    line-height: 32px;
			}
			.arf_popup_close_button_field_converter {
			    font-family: Asap-Medium;
			    outline: none;
			    float: right;
			    background: #4786ff;
			    border: none;
			    border-radius: 85px;
			    -webkit-border-radius: 85px;
			    -moz-border-radius: 85px;
			    -o-border-radius: 85px;
			    width: 85px;
			    text-align: center;
			    color: #ffffff;
			    font-size: 14px;
			    cursor: pointer;
			    height: 33px;
			    padding-bottom: 3px;
			    outline: none;
			}
			#arf_field_converter_loader{
				float: right;
			    right: 10px;
			    position: relative;
			}
			.arf_field_type_conversion_container .arf_feature_recommendation_note{
				float:left;
				width:100%;
				margin:0 0 20px 0;
				padding:0 20px;
			}
			@media all and (min-width:1600px) and (max-width:1899px){
				#arf_field_type_converter_model{
					height: 50%;
			        min-height: 50%;
			        max-height: 50%;
			        width: 40%;
			        max-width: 40%;
				}
			}
			@media all and (min-width:1900px){
				#arf_field_type_converter_model{
					height: 50%;
			        min-height: 50%;
			        max-height: 50%;
			        width: 40%;
			        max-width: 40%;
				}
			}
		</style>
	<?php
	}

	function arf_add_field_conversion_modal($values){
		global $arfieldhelper;
	?>
		<div class="arf_modal_overlay">
			<div id="arf_field_type_converter_model" class="arf_popup_container arf_popup_container_field_typle_converter_model">
				
				<div class="arf_popup_container_header">
					<?php echo esc_html__('Convert Field Type','ARForms'); ?>
					<div class="arfpopupclosebutton arfmodalclosebutton" data-dismiss="arfmodal" data-id="arf_optin_popup_button">
	                    <svg width="30px" height="30px" viewBox="1 0 20 20"><g id="preview"><path fill-rule="evenodd" clip-rule="evenodd" fill="#262944" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
	                </div>
				</div>

				<div class="arf_popup_content_container arf_field_converter_option_container">
					<div class="arf_field_type_conversion_container">
						<p class="arf_feature_recommendation_note">
							<?php echo '<strong>'.addslashes(esc_html__('Note','ARForms')).':</strong> '.addslashes(esc_html__('This feature is only recommended when you have big amount of entries in the form and you want to change the particular field type without losing the entry data for that field.','ARForms')); ?>
						</p>
						<div style="margin-left: 25px;float: left;width:100%;display: block;">
							<div class="arf_ar_dropdown_wrapper">
								<label class="arf_dropdown_autoresponder_label"> <?php echo esc_html__('Select Field To Convert','ARForms'); ?> </label> 
								<input type="hidden" id="arf_current_field_type" />
								<input type="hidden" id="field_type_converter" />
								<dl class="arf_selectbox" data-name="field_type_converter" data-id="field_type_converter" style="width:200px;">
									<dt>
										<span><?php echo esc_html__('Select Field','ARForms'); ?></span>
										<input style="display:none;width:128px;" class="arf_autocomplete" type="text" autocomplete="off" />
										<i class="arfa arfa-caret-down arfa-lg"></i>
									</dt>
									<dd>
										<ul class="arf_change_type_conversion_dropdown" style="display: none;max-height: 180px;" data-id="field_type_converter" >
											<li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?></li>
											<?php
												$supported_field_types = $this->arf_migrate_field_type();

												if( isset($values['fields']) && count($values['fields']) > 0 ){
													foreach( $values['fields'] as $k => $fields ){
														if( array_key_exists($fields['type'],$supported_field_types) ){
															echo "<li class='arf_selectbox_option' data-label='".$arfieldhelper->arf_execute_function($fields["name"],'strip_tags')."' data-value='{$fields['id']}' data-type='{$fields['type']}'>".$arfieldhelper->arf_execute_function($fields["name"],'strip_tags')." </li>";
														}
													}
												}
											?>
										</ul>
									</dd>
								</dl>
							</div>

							<div class="arf_ar_dropdown_wrapper">
								<label class="arf_dropdown_autoresponder_label"><?php echo esc_html__('Current Field Type','ARForms'); ?>:</label>
								<span class="arf_current_field_type"></span>
							</div>

							<div class="arf_ar_dropdown_wrapper">
								<input type="hidden" id="field_type_to_convert"  />
								<label class="arf_dropdown_autoresponder_label"> <?php echo esc_html__('Convert To Field Type','ARForms'); ?> </label> 
								<dl class="arf_selectbox" data-name="field_type_to_convert" data-id="field_type_to_convert" style="width:200px;">
									<dt>
										<span><?php echo esc_html__('Select Field Type','ARForms'); ?></span>
										<input style="display:none;width:128px;" class="arf_autocomplete" type="text" autocomplete="off" />
										<i class="arfa arfa-caret-down arfa-lg"></i>
									</dt>
									<dd>
										<ul style="display: none;max-height: 180px;" data-id="field_type_to_convert">
											<li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?></li>
											<?php
												$all_fields_type = $this->arf_migrate_field_type();

												foreach( $all_fields_type as $type => $label ){
													echo "<li class='arf_selectbox_option' data-value='{$type}' data-label='{$label}' data-type='{$type}'>{$label}</li>";
												}
											?>
										</ul>
									</dd>
								</dl>
							</div>

							<ul class="arf_ar_dropdown_wrapper_note_changing_type">
							</ul>
						</div>
					</div>
				</div>

				<div class="arf_popup_container_footer">
					<button type="button" class="arf_popup_close_button_field_converter" data-id="arf_optin_popup_button"><?php echo esc_html__('Confirm',"ARForms"); ?></button>
					<div class="arf_imageloader" id="arf_field_converter_loader"></div>
				</div>

			</div>
		</div>
	<?php
	}

	function arf_migrate_field_type(){

        $field_types = array(
            'text' => esc_html__('Single Line Text', 'ARForms'),
            'textarea' => esc_html__('Multiline Text', 'ARForms'),
            'checkbox' => esc_html__('Checkbox','ARForms'),
            'radio' => esc_html__('Radio Buttons','ARForms'),
            'select' => esc_html__('Dropdown','ARForms'),
            'email' => esc_html__('Email','ARForms'),
            'number' => esc_html__('Number','ARForms'),
            'phone' => esc_html__('Phone','ARForms'),
            'url' => esc_html__('Website/URL','ARForms'),
            'password' => esc_html__('Password','ARForms'),
            'scale' => esc_html__('Star Rating','ARForms'),
            'arfslider' => esc_html__('Slider','ARForms'),
            'colorpicker' => esc_html__('Colorpicker','ARForms'),
            'arf_smiley' => esc_html__('Smiley','ARForms'),
            'arf_autocomplete' => esc_html__('Autocomplete','ARForms')
        );

        $field_types = apply_filters('arf_migrate_field_type_from_outside',$field_types);

        return $field_types;
    }

}

global $arf_file_type_conversion;
$arf_file_type_conversion = new arf_file_type_conversion();