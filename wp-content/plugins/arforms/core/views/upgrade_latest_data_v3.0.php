<?php

global $wpdb, $db_record, $MdlDb, $armainhelper, $arfieldhelper, $arsettingcontroller, $arfsettings, $arffield, $arfrecordmeta, $arfform, $style_settings, $maincontroller;

if( !function_exists('is_plugin_active') ){
	require_once(ABSPATH.'/wp-admin/includes/plugin.php');
}

/* Taking Backup of Form Table */
$wpdb->query("CREATE TABLE `".$wpdb->prefix."arf_forms_backup` LIKE `".$MdlDb->forms."`");
$wpdb->query("INSERT `".$wpdb->prefix."arf_forms_backup` SELECT * FROM `".$MdlDb->forms."`");

/* Taking Backup of Field Table */
$wpdb->query("CREATE TABLE `".$wpdb->prefix."arf_fields_backup` LIKE `".$MdlDb->fields."`");
$wpdb->query("INSERT `".$wpdb->prefix."arf_fields_backup` SELECT * FROM `".$MdlDb->fields."`");

/* Taking Backup of maincss folder */
$wp_upload_dir = wp_upload_dir();
$source_dir = $wp_upload_dir['basedir'].'/arforms/maincss';
$destination_dir = $wp_upload_dir['basedir'].'/arforms/maincss_backup';
if( !is_dir($destination_dir) ){
	wp_mkdir_p($destination_dir);
}

$directory = opendir($source_dir);
while(($file = readdir($directory)) != false ){
	if( $file != '' && file_exists($source_dir.'/'.$file) ){
		copy($source_dir.'/'.$file, $destination_dir.'/'.$file);
	}
}


$addon_array = array(
	'arformsauthorizenet' => array(
		$wpdb->prefix.'arf_authorizenet_forms',
		$wpdb->prefix.'arf_authorizenet_order'
	),
	'arformsmymail' => array(
		$wpdb->prefix.'arf_my_mail_forms'
	),
	'arformspaypal' => array(
		$wpdb->prefix.'arf_paypal_forms',
		$wpdb->prefix.'arf_paypal_order'
	),
	'arformspaypalpro' => array(
		$wpdb->prefix.'arf_paypalpro_forms',
		$wpdb->prefix.'arf_paypalpro_order'
	),
	'arformsstripe' => array(
		$wpdb->prefix.'arf_stripe_forms',
		$wpdb->prefix.'arf_stripe_order'
	),
	'arformsusersignup' => array(
		$wpdb->prefix.'arf_user_registration_forms'
	),
);


$wpdb->query("DROP TABLE IF EXISTS `".$wpdb->prefix."arf_ref_forms`");

$wpdb->query("ALTER TABLE `".$MdlDb->fields."` DROP `ref_field_id`");

$allTemplates = $wpdb->get_results( $wpdb->prepare( "SELECT id,status FROM `".$MdlDb->forms."` WHERE id < %d AND status != %s", 8, 'published' ) );

if( !empty($allTemplates) && count($allTemplates) > 0 ){
	foreach( $allTemplates as $counter => $template ){
		$template_id = $template->id;
		$template_status = $template->status;
		/* Update template status to publish if it's not */
		if( $template_status != 'published' ){
			$updateStatus = array('status' => 'published');
			$updateWhere = array('id' => $template_id);

			$wpdb->update($MdlDb->forms,$updateStatus,$updateWhere);
		}
	}
}


$first_form_id = $wpdb->get_row( $wpdb->prepare( "SELECT MIN(id) as frm_id FROM `".$MdlDb->forms."` WHERE id > %d ",7 ) );


$form_ids_for_addon = array();
$form_ids_for_field = array();
$form_ids_for_entry = array();

if( $first_form_id->frm_id < 100 ){
	$next_form_id = 100;

	/* Get all faulty forms and Loop through them */
	$allForms = $wpdb->get_results( $wpdb->prepare( "SELECT id,status FROM `".$MdlDb->forms."` WHERE id > %d AND status = %s AND is_template = %d ", 7, 'published', 0 ) );
	foreach( $allForms as $form_counter => $formObj ){

		$form_id = $formObj->id;
		$form_status = $formObj->status;

		$update_form_id = array('id' => $next_form_id);
		$where_form_id = array('id' => $form_id);

		/* Do further process only if form id updated successfully */
		if( false !== $wpdb->update( $MdlDb->forms,$update_form_id,$where_form_id ) ){

			$form_ids_for_field[$form_id] = $next_form_id;
			$form_ids_for_addon[$form_id] = $next_form_id;
			$form_ids_for_entry[$form_id] = $next_form_id;

			$next_form_id++;
		}
	}

	/* Loop through all fields and update the form id */
	$old_form_id = $new_form_id = 0;
	if( count($form_ids_for_field) > 0 ){
		foreach( $form_ids_for_field as $old_form_id => $new_form_id ){
			$updated_form_id = array('form_id' => $new_form_id);
			$where_form_id = array('form_id' => $old_form_id);
			$wpdb->update($MdlDb->fields,$updated_form_id,$where_form_id);
		}
	}

	/* Loop through all entries and update the form id */
	$old_form_id = $new_form_id = 0;
	if( count($form_ids_for_entry) > 0 ){
		foreach( $form_ids_for_entry as $old_form_id => $new_form_id ){
			$updated_form_id = array('form_id' => $new_form_id );
			$where_form_id = array('form_id' => $old_form_id);
			$wpdb->update($MdlDb->entry,$updated_form_id,$where_form_id);
		}
	}

	/* Loop through all add on tables */
	if( count($form_ids_for_addon) > 0 ){
		/* Check if any add-on is installed and then update the form id*/
		foreach( $addon_array as $addon => $addon_tables ){
			$current_addon = $addon.'/'.$addon.'.php';
			/* Check if add-on is active or check if add on file is exists */
			if( is_plugin_active( $current_addon ) || file_exists( WP_PLUGIN_DIR.'/'.$current_addon ) ){
				foreach( $addon_tables as $addon_table){
					foreach($form_ids_for_addon as $old_form_id => $new_form_id){
						$update_form_id = array('form_id' => $new_form_id);
						$where_form_id = array('form_id' => $old_form_id);
						$wdpb->update($addon_table,$update_form_id,$where_form_id);
					}
				}
			}
		}
	}

	/* Set Auto Increment Id for Main Table */
	$wpdb->query( "ALTER TABLE `".$MdlDb->forms."` AUTO_INCREMENT = ".$next_form_id );
}



$import_preset_value = $wp_upload_dir['basedir'] . '/arforms/import_preset_value/';
if (!is_dir($import_preset_value)) {
    wp_mkdir_p($import_preset_value);
}

$destination_url = $wp_upload_dir['baseurl'] . '/arforms';
$destination_path = $wp_upload_dir['basedir'] . '/arforms';

$source_path = FORMPATH . '/images/smiley';

$source_image1 = '1.png';
$source_image2 = '2.png';
$source_image3 = '3.png';
$source_image4 = '4.png';
$source_image5 = '5.png';

$dest_image1 = 'arf_smiley_image_1.png';
$dest_image2 = 'arf_smiley_image_2.png';
$dest_image3 = 'arf_smiley_image_3.png';
$dest_image4 = 'arf_smiley_image_4.png';
$dest_image5 = 'arf_smiley_image_5.png';

$smiley_images = array(
    $source_image1 => $dest_image1,
    $source_image2 => $dest_image2,
    $source_image3 => $dest_image3,
    $source_image4 => $dest_image4,
    $source_image5 => $dest_image5
);

foreach ($smiley_images as $source => $destination) {
    copy($source_path . '/' . $source, $destination_path . '/' . $destination);
}

$wpdb->query("ALTER TABLE `" . $MdlDb->ar . "`  ADD `madmimi` TEXT NOT NULL");

$wpdb->query("ALTER TABLE `". $MdlDb->autoresponder."`  ADD `madmimi` VARCHAR(255) NOT NULL");

$wpdb->query("ALTER TABLE " . $MdlDb->fields . " ADD `enable_running_total` longtext NOT NULL AFTER `conditional_logic`");

$wpdb->query("ALTER TABLE " . $MdlDb->forms . " ADD `temp_fields` LONGTEXT NULL");

$wpdb->query("ALTER TABLE " . $MdlDb->forms . " ADD `arf_mapped_addon` LONGTEXT NULL");

$wpdb->query("INSERT INTO `" . $MdlDb->autoresponder. "` (responder_id) VALUES (10)");

$global_options = get_option('arf_options');
$global_options->arfmainformloadjscss = arf_sanitize_value(0, 'integer');
$global_options->arf_load_js_css = array();
$global_options->arf_email_format = arf_sanitize_value('html');
$global_options->decimal_separator = '.';

update_option('arf_options', $global_options);
set_transient('arf_options', $global_options);


$ar_types = maybe_unserialize(get_option('arf_ar_type'));
$ar_types['madmimi_type'] = arf_sanitize_value(1, 'integer');
$ar_types = maybe_serialize($ar_types);
update_option('arf_ar_type', $ar_types);

update_option('arf_form_entry_separator', arf_sanitize_value('arf_comma'));

$field_data = file_get_contents(VIEWS_PATH . '/arf_editor_data.json');

$field_data_obj = json_decode($field_data,true);

$field_data_obj = $field_data_obj['field_data'];


if( file_exists( WP_PLUGIN_DIR.'/arformsignature/arformsignature.php' ) ){
	if( file_exists( WP_PLUGIN_DIR.'/arformsignature/core/arf_signature_field_data.json' )){
		$signature_field_data = file_get_contents(WP_PLUGIN_DIR.'/arformsignature/core/arf_signature_field_data.json');
		$signature_field_data = json_decode($signature_field_data,true);
		$field_data_obj['signature'] = $signature_field_data['field_data']['signature'];
	} else {
		$field_data_obj['signature'] = array(
			'required' => 0,
			'required_indicator' => '*',
			'name' => addslashes(esc_html__('Signature','ARForms')),
			'blank' => addslashes(esc_html__('This field cannot be blank.', 'ARForms')),
			'description' => '',
			'classes' => 'arf_1',
			'inner_class' => 'arf_1col',
			'image_height' => 150,
			'image_width' => 300,
			'type' => 'signature',
			'key' => '{arf_unique_key}'
		);
	}
}


if( file_exists( WP_PLUGIN_DIR.'/arformsdigitalproduct/arformsdigitalproduct.php' ) ){
	if( file_exists( WP_PLUGIN_DIR.'/arformsdigitalproduct/core/views/arf_download_field_data.json') ){
		$digital_product_data = file_get_contents(WP_PLUGIN_DIR.'/arformsdigitalproduct/core/views/arf_download_field_data.json');
		$digital_product_data = json_decode($digital_product_data,true);
		$field_data_obj['arf_product'] = $digital_product_data['field_data']['arf_product'];
	} else {
		$field_data_obj['arf_product'] = array(
			'name' => addslashes(esc_html__('Product','ARForms')),
			'classes' => 'arf_1',
			'inner_class' => 'arf_1col',
			'attach' => 0,
			'arf_product_exp_time_val' => '24',
			'arf_download_show_link' => '',
			'arf_download_link_content' => addslashes(esc_html__('Please download your product by clicking below link','ARForms')).' [ARF_download_link product_id="0" desc="'.addslashes(esc_html__('Download','ARForms')).'" type="link"]',
			'type' => 'arf_product',
			'key' => '{arf_unique_key}'
		);
	}
}

if( !isset($field_data_obj['captcha']) ){
	$field_data_obj['captcha'] = array(
		"invalid"=> addslashes(esc_html__("The reCAPTCHA was not entered correctly","ARForms")),
        "name"=> "Untitled",
        "description"=> "",
        "is_recaptcha"=> "recaptcha",
        "default_value" => "",
        "classes"=> "arf_1",
        "inner_class"=> "arf_1col",
        "key" => "{arf_unique_key}",
        "css_outer_wrapper"=> "",
        "css_label"=> "",
        "css_input_element"=> "",
        "css_description"=> "",
        "type" => "captcha"
	);
}

$fields_with_default_val = array('text','textarea','number','phone','tel');
$fields_without_default_val = array('email','date','url','image','password');

$arf_forms = $wpdb->get_results( $wpdb->prepare( "SELECT id,options,form_css FROM `".$MdlDb->forms."` WHERE status = %s", 'published' ) );

foreach( $arf_forms as $arf_form ){

	$form_id = $arf_form->id;

	$new_form_css = $new_form_options = array();

	$form_options = maybe_unserialize($arf_form->options);
	$form_css = maybe_unserialize($arf_form->form_css);

	$new_form_options = $form_options;
	$new_form_css = $form_css;

	$total_page_breaks = 0;
	$page_break = array();
	$is_font_awesome = false;
    $is_tooltip = false;
    $is_input_mask = false;
    $normal_color_picker = false;
    $advanced_color_picker = false;
    $animate_number = false;
    $arf_page_break_survey = false;
    $arf_page_break_wizard = false;
    $html_running_total_field_array = array();
    $google_captcha_loaded = false;
    $is_imagecontrol_field = false;
    $resize_width_array = array();

	$i = 1;
	$define_field_order = 0;
	$temp_fields = array();
	$section_counter = 0;
	$need_to_increment = 1;

	$new_field_order = array();

	/* Setting up new form options */
	$new_form_options['arf_form_set_cookie'] = 0;

	$new_form_options['arf_form_hide_after_submit'] = 0;

	$new_form_options['arf_pre_dup_check'] = 0;
	$new_form_options['arf_pre_dup_check_type']  = 'ip_address';
	$new_form_options['arf_pre_dup_msg'] = addslashes(esc_html__('You have already submitted this form before. You are not allowed to submit this form again.','ARForms'));

	$new_form_options['arf_restrict_form_entries'] = 0;
	$new_form_options['restrict_action'] = 'max_entries';
	
	$new_form_options['arf_restrict_max_entries'] = 50;
    $new_form_options['arf_restrict_entries_before_specific_date'] = date('Y/m/d');
    $new_form_options['arf_restrict_entries_after_specific_date'] = date('Y/m/d');
    $new_form_options['arf_restrict_entries_start_date'] = date('Y/m/d');
    $new_form_options['arf_restrict_entries_end_date'] = date('Y/m/d');
    $new_form_options['arf_res_msg'] = addslashes(esc_html__('Maximum entry limit is reached.','ARForms'));

    $new_form_options['conditional_subscription'] = 0;
    
    $new_form_options['arf_condition_on_subscription_rules'] = array(
    	1 => array(
    		'id' => 1,
    		'field_id' => '',
    		'field_type' => '',
    		'operator' => 'is',
    		'value' => ''
    	)
    );

    $new_form_options['arf_conditional_redirect_enable'] = 0;

    $new_form_options['arf_conditional_redirect_rules'] = array(
    	1 => array(
    		'id' => 1,
    		'field_id' => '',
    		'field_type' => '',
    		'operator' => 'is',
    		'value' => '',
    		'redirect_url' => ''
    	)
    );

    $new_form_options['arf_conditional_enable_mail'] = 0;

    $new_form_options['arf_conditional_mail_rules'] = array(
    	1 => array(
    		'id_mail' => 1,
    		'field_id_mail' => '',
    		'field_type_mail' => '',
    		'operator_mail' => '',
    		'value_mail' => '',
    		'send_mail_field' => ''
    	)
    );

    $new_form_options['arf_conditional_logic_rules'] = array(
    	0 => array(
    		'id' => 0,
    		'logical_operator' => 'and',
    		'condition' => array(
    			0 => array(
    				'condition_id' => 0,
    				'field_id' => '',
    				'field_type' => '',
    				'operator' => 'is',
    				'value' => ''
    			)
    		),
    		'result' => array(
    			0 => array(
    				'result_id' => 0,
    				'field_id' => '',
    				'field_type' => '',
    				'action' => '',
    				'value' => ''
    			)
    		)
    	)
    );

    $new_form_options['define_template'] = 0;

	/* Update existing form options if needed */
	$new_form_options['arf_form_other_css'] = isset($new_form_options['arf_form_other_css']) ? $new_form_options['arf_form_other_css'] : '';

	$new_form_custom_css = $new_form_options['arf_form_other_css'];

	if( isset($new_form_options['arf_form_outer_wrapper']) && $new_form_options['arf_form_outer_wrapper'] != '' ){
		$new_form_custom_css .= ".arf_form_outer_wrapper.ar_main_div_{$form_id},.popup-form-{$form_id} .arfmodal{";
		$new_form_custom_css .= $new_form_options['arf_form_outer_wrapper'];
		$new_form_custom_css .= "}";
	} 
	if( isset($new_form_options['arf_form_inner_wrapper']) && $new_form_options['arf_form_inner_wrapper'] != '' ){
		$new_form_custom_css .= ".arf_form_outer_wrapper.ar_main_div_{$form_id} .arf_fieldset,.popup-form-{$form_id} .arfmodal{";
		$new_form_custom_css .= $new_form_options['arf_form_inner_wrapper'];
		$new_form_custom_css .= "}";
	}
	if( isset($new_form_options['arf_form_title']) && $new_form_options['arf_form_title'] != '' ){
		$new_form_custom_css .= ".arf_form_outer_wrapper.ar_main_div_{$form_id} .arf_fieldset .formtitle_style{";
		$new_form_custom_css .= $new_form_options['arf_form_title'];
		$new_form_custom_css .= "}";
	}
	if( isset($new_form_options['arf_form_description']) && $new_form_options['arf_form_description'] != '' ){
		$new_form_custom_css .= ".arf_form_outer_wrapper.ar_main_div_{$form_id} .arf_fieldset .formdescription_style{";
		$new_form_custom_css .= $new_form_options['arf_form_description'];
		$new_form_custom_css .= "}";
	}
	if( isset($new_form_options['arf_form_element_wrapper']) && $new_form_options['arf_form_element_wrapper'] != '' ){
		$new_form_custom_css .= ".arf_form_outer_wrapper.ar_main_div_{$form_id} .arf_fieldset .arfformfield{";
		$new_form_custom_css .= $new_form_options['arf_form_element_wrapper'];
		$new_form_custom_css .= "}";
	}
	if( isset($new_form_options['arf_form_element_label']) && $new_form_options['arf_form_element_label'] != '' ){
		$new_form_custom_css .= ".arf_form_outer_wrapper.ar_main_div_{$form_id} .arf_fieldset .arfformfield label.arf_main_label{";
		$new_form_custom_css .= $new_form_options['arf_form_element_label'];
		$new_form_custom_css .= "}";
	}
	if( isset($new_form_options['arf_form_submit_button']) && $new_form_options['arf_form_submit_button'] != '' ){
		$new_form_custom_css .= ".arf_form_outer_wrapper.ar_main_div_{$form_id} .arf_fieldset .arfsubmitbutton button.arf_submit_btn{";
		$new_form_custom_css .= $new_form_options['arf_form_submit_button'];
		$new_form_custom_css .= "}";
	}
	if( isset($new_form_options['arf_form_success_message']) && $new_form_options['arf_form_success_message'] != '' ){
		$new_form_custom_css .= ".arf_form_outer_wrapper.ar_main_div_{$form_id} #arf_message_success{";
		$new_form_custom_css .= $new_form_options['arf_form_success_message'];
		$new_form_custom_css .= "}";
	}
	if( isset($new_form_options['arf_form_elements']) && $new_form_options['arf_form_elements'] != '' ){
		$new_form_custom_css .= ".ar_main_div_{$form_id} .arf_fieldset .controls{";
		$new_form_custom_css .= $new_form_options['arf_form_element_wrapper'];
		$new_form_custom_css .= "}";
	}
	if( isset($new_form_options['arf_submit_outer_wrapper']) && $new_form_options['arf_submit_outer_wrapper'] != '' ){
		$new_form_custom_css .= ".arf_form_outer_wrapper.ar_main_div_{$form_id} .arf_fieldset div.arfsubmitbutton{";
		$new_form_custom_css .= $new_form_options['arf_submit_outer_wrapper'];
		$new_form_custom_css .= "}";
	}
	if( isset($new_form_options['arf_form_next_button']) && $new_form_options['arf_form_next_button'] != '' ){
		$new_form_custom_css .= ".arf_form_outer_wrapper.ar_main_div_{$form_id} .arf_fieldset div.arfsubmitbutton .next_btn{";
		$new_form_custom_css .= $new_form_options['arf_form_next_button'];
		$new_form_custom_css .= "}";
	}
	if( isset($new_form_options['arf_form_previous_button']) && $new_form_options['arf_form_previous_button'] != '' ){
		$new_form_custom_css .= ".arf_form_outer_wrapper.ar_main_div_{$form_id} .arf_fieldset div.arfsubmitbutton .previous_btn{";
		$new_form_custom_css .= $new_form_options['arf_form_previous_button'];
		$new_form_custom_css .= "}";
	}
	if( isset($new_form_options['arf_form_error_message']) && $new_form_options['arf_form_error_message'] != '' ){
		$new_form_custom_css .= ".arf_form_outer_wrapper.ar_main_div_{$form_id} .control-group.arf_error .help-block,.arf_form_outer_wrapper.ar_main_div_{$form_id} .control-group.arf_error .popover,.arf_form_outer_wrapper.ar_main_div_{$form_id} .control-group.arf_warning .help-block,.arf_form_outer_wrapper.ar_main_div_{$form_id} .control-group.arf_warning .help-inline,.arf_form_outer_wrapper.ar_main_div_{$form_id} .control-group.arf_warning .help-label,.arf_form_outer_wrapper.ar_main_div_{$form_id} .control-group.arf_warning .control-label,.arf_form_outer_wrapper.ar_main_div_{$form_id} .control-group.arf_warning .popover{";
		$new_form_custom_css .= $new_form_options['arf_form_error_message'];
		$new_form_custom_css .= "}";
	}
	if( isset($new_form_options['arf_form_page_break']) && $new_form_options['arf_form_page_break'] != ''){
		$new_form_custom_css .= ".arf_form_outer_wrapper.ar_main_div_{$form_id} .arf_fieldset .arfformfield{";
		$new_form_custom_css .= $new_form_options['arf_form_page_break'];
		$new_form_custom_css .= "}";
	}
	if( isset($new_form_options['arf_form_fly_sticky']) && $new_form_options['arf_form_fly_sticky'] != '' ){
		$new_form_custom_css .= "#arf-popup-form-{$form_id} .arf_fly_sticky_btn{";
		$new_form_custom_css .= $new_form_options['arf_form_fly_sticky'];
		$new_form_custom_css .= "}";
	}
	if( isset($new_form_options['arf_form_modal_css']) && $new_form_options['arf_form_modal_css'] != '' ){
		$new_form_custom_css .= "#popup-form-{$form_id}.arfmodal{";
		$new_form_custom_css .= $new_form_options['arf_form_modal_css'];
		$new_form_custom_css .= "}";
	}
	if( isset($new_form_options['arf_form_link_css']) && $new_form_options['arf_form_link_css'] != '' ){
		$new_form_custom_css .= ".arform_modal_link_{$form_id}{";
		$new_form_custom_css .= $new_form_options['arf_form_link_css'];
		$new_form_custom_css .= "}";
	}
	if( isset($new_form_options['arf_form_button_css']) && $new_form_options['arf_form_button_css'] != '' ){
		$new_form_custom_css .= ".arform_modal_button_{$form_id}{";
		$new_form_custom_css .= $new_form_options['arf_form_button_css'];
		$new_form_custom_css .= "}";
	}
	if( isset($new_form_options['arf_form_link_hover_css']) && $new_form_options['arf_form_link_hover_css'] != '' ){
		$new_form_custom_css .= ".arform_modal_link_{$form_id}:hover{";
		$new_form_custom_css .= $new_form_options['arf_form_link_hover_css'];
		$new_form_custom_css .= "}";
	}
	if( isset($new_form_options['arf_form_button_hover_css']) && $new_form_options['arf_form_button_hover_css'] != '' ){
		$new_form_custom_css .= ".arform_modal_button_{$form_id}:hover{";
		$new_form_custom_css .= $new_form_options['arf_form_button_hover_css'];
		$new_form_custom_css .= "}";
	}

    unset($new_form_options['arf_form_outer_wrapper']);
    unset($new_form_options['arf_form_inner_wrapper']);
    unset($new_form_options['arf_form_title']);
    unset($new_form_options['arf_form_description']);
    unset($new_form_options['arf_form_element_wrapper']);
    unset($new_form_options['arf_form_element_label']);
    unset($new_form_options['arf_form_submit_button']);
    unset($new_form_options['arf_form_success_message']);
    unset($new_form_options['arf_form_elements']);
    unset($new_form_options['arf_submit_outer_wrapper']);
    unset($new_form_options['arf_form_next_button']);
    unset($new_form_options['arf_form_previous_button']);
    unset($new_form_options['arf_form_error_message']);
    unset($new_form_options['arf_form_page_break']);
    unset($new_form_options['arf_form_fly_sticky']);
    unset($new_form_options['arf_form_modal_css']);
    unset($new_form_options['arf_form_link_css']);
    unset($new_form_options['arf_form_button_css']);
    unset($new_form_options['arf_form_link_hover_css']);
    unset($new_form_options['arf_form_button_hover_css']);


	/* Adding new options for form styling  */
	$new_form_css['arf_tooltip_bg_color'] = '#000000';
	$new_form_css['arf_tooltip_font_color'] = '#ffffff';
	$new_form_css['arf_tooltip_width'] = '';
	$new_form_css['arf_tooltip_position'] = '';

	/* Update existing options for form styling */
	$new_form_css['arfcheckradiostyle'] = 'default';

	$checkbox_radio_color = $form_css['arfcheckradiocolor'];
	if( !in_array($form_css['arfcheckradiostyle'],array('polaris','futurico','none') ) ){
    	if( $checkbox_radio_color == 'default'){
    		$new_form_css['checked_checkbox_icon_color'] = '#333333';
    		$new_form_css['checked_radio_icon_color'] = '#333333';
    	} else if( $checkbox_radio_color == 'aero' ){
    		$new_form_css['checked_checkbox_icon_color'] = '#9CC2CB';
    		$new_form_css['checked_radio_icon_color'] = '#9CC2CB';
    	} else if( $checkbox_radio_color == 'blue' ){
    		$new_form_css['checked_checkbox_icon_color'] = '#077BDD';
    		$new_form_css['checked_radio_icon_color'] = '#077BDD';
    	} else if( $checkbox_radio_color == 'green' ){
    		$new_form_css['checked_checkbox_icon_color'] = '#1ABC9C';
    		$new_form_css['checked_radio_icon_color'] = '#1ABC9C';
    	} else if( $checkbox_radio_color == 'grey' ){
    		$new_form_css['checked_checkbox_icon_color'] = '#95A5A6';
    		$new_form_css['checked_radio_icon_color'] = '#95A5A6';
    	} else if( $checkbox_radio_color == 'orange' ){
    		$new_form_css['checked_checkbox_icon_color'] = '#F39C12';
    		$new_form_css['checked_radio_icon_color'] = '#F39C12';
    	} else if( $checkbox_radio_color == 'pink' ){
    		$new_form_css['checked_checkbox_icon_color'] = '#AF7AC5';
    		$new_form_css['checked_radio_icon_color'] = '#AF7AC5';
    	} else if( $checkbox_radio_color == 'purple' ){
    		$new_form_css['checked_checkbox_icon_color'] = '#9588B2';
    		$new_form_css['checked_radio_icon_color'] = '#9588B2';
    	} else if( $checkbox_radio_color == 'red' ){
    		$new_form_css['checked_checkbox_icon_color'] = '#EC7063';
    		$new_form_css['checked_radio_icon_color'] = '#EC7063';
    	} else if( $checkbox_radio_color == 'yellow' ){
    		$new_form_css['checked_checkbox_icon_color'] = '#EC7063';
    		$new_form_css['checked_radio_icon_color'] = '#F1C40F';
    	} else {
    		$new_form_css['checked_checkbox_icon_color'] = '#23b7e5';
    		$new_form_css['checked_radio_icon_color'] = '#23b7e5';
    	}
	} else {
		$new_form_css['checked_checkbox_icon_color'] = '#23b7e5';
		$new_form_css['checked_radio_icon_color'] = '#23b7e5';
	}

	$new_form_css['arfmainform_color_skin'] = 'custom';
	$new_form_css['arfinputstyle'] = 'standard';

	$new_form_css['arfmainbasecolor'] = $new_form_css['arfborderactivecolorsetting'];

	$new_form_css['arflikebtncolor'] = "";
	$new_form_css['arfdislikebtncolor'] = "";
	
	$new_form_css['arfstarratingcolor'] = "";
	
	$new_form_css['arfsliderselectioncolor'] = "";
	$new_form_css['arfslidertrackcolor'] = "";

	$new_form_css['arfcommonfont'] = isset($new_form_css['arftitlefontfamily']) ? $new_form_css['arftitlefontfamily'] : 'Helvetica';
	
	$form_title_font_size = $form_css['form_title_font_size'];
	$common_field_size = ( $form_title_font_size <= 24 ) ? 1 : 3;
	$common_field_size = ( $form_title_font_size <= 26 && $form_title_font_size > 24 ) ? 2 : 3;
	$common_field_size = ( $form_title_font_size <= 28 && $form_title_font_size > 26 ) ? 3 : 3;
	$common_field_size = ( $form_title_font_size <= 30 && $form_title_font_size > 28 ) ? 4 : 3;
	$common_field_size = ( $form_title_font_size <= 32 && $form_title_font_size > 30 ) ? 5 : 3;
	$common_field_size = ( $form_title_font_size <= 34 && $form_title_font_size > 32 ) ? 6 : 3;
	$common_field_size = ( $form_title_font_size <= 36 && $form_title_font_size > 34 && $form_css['field_font_size'] == 26 ) ? 7 : 3;
	$common_field_size = ( $form_title_font_size <= 36 && $form_title_font_size > 34 && $form_css['field_font_size'] == 28 ) ? 8 : 3;
	$common_field_size = ( $form_title_font_size <= 38 && $form_title_font_size > 36 ) ? 9 : 3;
	$common_field_size = ( $form_title_font_size <= 40 && $form_title_font_size > 38 ) ? 10 : 3;

	$new_form_css['arfmainfieldcommonsize'] = $common_field_size;

	$new_form_css['arfdatepickerbgcolorsetting'] = '#27c24c';
	$new_form_css['arfdatepickertextcolorsetting'] = '#000000';

	if ($new_form_css['arfcalthemecss'] == 'default_theme' || $new_form_css['arfcalthemecss'] == '') {
        $new_form_css['arfdatepickertextcolorsetting'] = '#1c1b1b';
        $new_form_css['arfdatepickerbgcolorsetting'] = '#ffffff';
    }
    if ($new_form_css['arfcalthemecss'] == '1') {
        $new_form_css['arfdatepickertextcolorsetting'] = '#46484d';
        $new_form_css['arfdatepickerbgcolorsetting'] = '#66aaff';
    } else if ($new_form_css['arfcalthemecss'] == '2') {
        $new_form_css['arfdatepickertextcolorsetting'] = '#46484d';
        $new_form_css['arfdatepickerbgcolorsetting'] = '#8dd96b';
    } else if ($new_form_css['arfcalthemecss'] == '3') {
        $new_form_css['arfdatepickertextcolorsetting'] = '#1c1b1b';
        $new_form_css['arfdatepickerbgcolorsetting'] = '#ffffff';
    } else if ($new_form_css['arfcalthemecss'] == '4') {
        $new_form_css['arfdatepickertextcolorsetting'] = '#ffffff';
        $new_form_css['arfdatepickerbgcolorsetting'] = '#1c1b1b';
    } else if ($new_form_css['arfcalthemecss'] == '5') {
        $new_form_css['arfdatepickertextcolorsetting'] = '#46484d';
        $new_form_css['arfdatepickerbgcolorsetting'] = '#ff8605';
    } else if ($new_form_css['arfcalthemecss'] == '6') {
        $new_form_css['arfdatepickertextcolorsetting'] = '#46484d';
        $new_form_css['arfdatepickerbgcolorsetting'] = '#756ef5';
    } else if ($new_form_css['arfcalthemecss'] == '7') {
        $new_form_css['arfdatepickertextcolorsetting'] = '#46484d';
        $new_form_css['arfdatepickerbgcolorsetting'] = '#ff3939';
    } else if ($new_form_css['arfcalthemecss'] == '8') {
        $new_form_css['arfdatepickertextcolorsetting'] = '#46484d';
        $new_form_css['arfdatepickerbgcolorsetting'] = '#27c24c';
    } else if ($new_form_css['arfcalthemecss'] == '9') {
        $new_form_css['arfdatepickertextcolorsetting'] = '#46484d';
        $new_form_css['arfdatepickerbgcolorsetting'] = '#3f51b5';
    } else if ($new_form_css['arfcalthemecss'] == '10') {
        $new_form_css['arfdatepickertextcolorsetting'] = '#46484d';
        $new_form_css['arfdatepickerbgcolorsetting'] = '#fd2571';
    } else if ($new_form_css['arfcalthemecss'] == '11') {
        $new_form_css['arfdatepickertextcolorsetting'] = '#46484d';
        $new_form_css['arfdatepickerbgcolorsetting'] = '#25fdc3';
    } else if ($new_form_css['arfcalthemecss'] == '12') {
        $new_form_css['arfdatepickertextcolorsetting'] = '#46484d';
        $new_form_css['arfdatepickerbgcolorsetting'] = '#fdcb04';
    } else if ($new_form_css['arfcalthemecss'] == '13') {
        $new_form_css['arfdatepickertextcolorsetting'] = '#ffffff';
        $new_form_css['arfdatepickerbgcolorsetting'] = '#7b75f4';
    } else if ($new_form_css['arfcalthemecss'] == '14') {
        $new_form_css['arfdatepickertextcolorsetting'] = '#ffffff';
        $new_form_css['arfdatepickerbgcolorsetting'] = '#27c24c';
    } else if ($new_form_css['arfcalthemecss'] == '15') {
        $new_form_css['arfdatepickertextcolorsetting'] = '#ffffff';
        $new_form_css['arfdatepickerbgcolorsetting'] = '#5792fd';
    } else if ($new_form_css['arfcalthemecss'] == '16') {
        $new_form_css['arfdatepickertextcolorsetting'] = '#ffffff';
        $new_form_css['arfdatepickerbgcolorsetting'] = '#23b7e5';
    } else if ($new_form_css['arfcalthemecss'] == '17') {
        $new_form_css['arfdatepickertextcolorsetting'] = '#ffffff';
        $new_form_css['arfdatepickerbgcolorsetting'] = '#fd2571';
    } else if ($new_form_css['arfcalthemecss'] == '18') {
        $new_form_css['arfdatepickertextcolorsetting'] = '#ffffff';
        $new_form_css['arfdatepickerbgcolorsetting'] = '#ff8605';
    } else if ($new_form_css['arfcalthemecss'] == '19') {
        $new_form_css['arfdatepickertextcolorsetting'] = '#ffffff';
        $new_form_css['arfdatepickerbgcolorsetting'] = '#ff3939';
    } else if ($new_form_css['arfcalthemecss'] == '20') {
        $new_form_css['arfdatepickertextcolorsetting'] = '#ffffff';
        $new_form_css['arfdatepickerbgcolorsetting'] = '#ad1457';
    } else if ($new_form_css['arfcalthemecss'] == '21') {
        $new_form_css['arfdatepickertextcolorsetting'] = '#ffffff';
        $new_form_css['arfdatepickerbgcolorsetting'] = '#3f51b5';
    } else if ($new_form_css['arfcalthemecss'] == '22') {
        $new_form_css['arfdatepickertextcolorsetting'] = '#1c1b1b';
        $new_form_css['arfdatepickerbgcolorsetting'] = '#f5f5f5';
    } else if ($new_form_css['arfcalthemecss'] == '23') {
        $new_form_css['arfdatepickertextcolorsetting'] = '#1c1b1b';
        $new_form_css['arfdatepickerbgcolorsetting'] = '#ffffff';
    } else if ($new_form_css['arfcalthemecss'] == '24') {
        $new_form_css['arfdatepickertextcolorsetting'] = '#ffffff';
        $new_form_css['arfdatepickerbgcolorsetting'] = '#1c1b1b';
    }

    $new_form_css['arfvalidationbgcolorsetting'] = '#ed4040';
    $new_form_css['arfvalidationtextcolorsetting'] = '#ffffff';
    $error_styling_color = array();
    
    $new_form_css['arferrorstyle'] = isset($new_form_css['arferrorstyle']) ? $new_form_css['arferrorstyle'] : 'normal';

    if( $new_form_css['arferrorstyle'] == 'advance' ){
    	if( isset($new_form_css['arferrorstylecolor']) ){
    		$error_styling_color = explode('|',$new_form_css['arferrorstylecolor']);
    	} else {
    		$error_styling_color = array('#ed4040','#ffffff','#ed4040');
    	}
    } else {
    	if( isset($new_form_css['arferrorstylecolor2'])){
    		$error_styling_color = explode('|',$new_form_css['arferrorstylecolor2']);
    	} else {
    		$error_styling_color = array('#ed4040','#ffffff','#ed4040');
    	}
    }

    
    if( $new_form_css['arferrorstyle'] == 'advance' ){
    	$new_form_css['arfvalidationbgcolorsetting'] = $error_styling_color[0];
    	$new_form_css['arfvalidationtextcolorsetting'] = $error_styling_color[1];
    } else {
    	$new_form_css['arfvalidationbgcolorsetting'] = $error_styling_color[2];
    	$new_form_css['arfvalidationtextcolorsetting'] = $error_styling_color[2];
    }

    $new_form_css['arfformerrorbgcolorsetting'] = '#FDECED';
    $new_form_css['arfformerrorbordercolorsetting'] = '#F9CFD1';
    $new_form_css['arfformerrortextcolorsetting'] = '#ED4040';

	/* Remove unnecessary options from form_css */
	unset($new_form_css['arfcalthemename']);
	unset($new_form_css['arfcalthemecss']);


    /* Update for submit button alignment options */
    if(isset($new_form_css['arfsubmitalignsetting']) && $new_form_css['arfsubmitalignsetting'] == 'fixed') {
        $new_form_css['arfsubmitalignsetting'] = (isset($new_form_css['form_align']) && $new_form_css['form_align'] != '') ? $new_form_css['form_align'] : 'left';
    } else {
        $new_form_css['arfsubmitalignsetting'] = 'center';
    }


	/*Get all fields of current form*/
	$arf_fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `".$MdlDb->fields."` WHERE form_id = %d ORDER BY field_order ASC", $form_id ) );

	$arf_loaded_field = array();

	$password_field = 0;
	$conf_pass_field = 0;

	$total_fields = count($arf_fields);

	$arf_column_class = array();

	$new_conditional_logic = array();
	$cl = 0;
	$rt_field_array = array();

	$custom_captcha_field_id = array();

	$file_upload_control = 0;

	$file_upload_text_color = $file_upload_bg_color = "";

	$rating_control = $slider_control = $like_control = 0;

	$temp_field_array = array();

	foreach($arf_fields as $arf_field){

		$field_id = $arf_field->id;

		$field_type = $arf_field->type;

		if( $field_type == 'slider' ){
			$field_type = arf_sanitize_value('arfslider');
			$wpdb->update( $MdlDb->fields, array('field_type'=> $field_type), array('id' => $field_id) );
		}
		
		array_push($arf_loaded_field, $field_type);
		array_push($rt_field_array,$field_id);

		$field_description = $arf_field->description;
		$field_default_value = $arf_field->default_value;

		$f_options = maybe_unserialize($arf_field->options); // for checkbox, radio and dropdown

		$field_options = maybe_unserialize($arf_field->field_options);

		if( $field_type == 'file' && $file_upload_control == 0 ){
			/*$form_css['arfuploadbtntxtcolorsetting'] = $field_options['upload_font_color'];
			$form_css['arfuploadbtnbgcolorsetting'] = $field_options['upload_btn_color'];*/
			$file_upload_control++;
		}

		/* delete captcha field if it's not set to google captcha */
		if( $field_type == 'captcha' ){
			array_push( $custom_captcha_field_id, $field_id );
		}

		$field_conditional_logic = maybe_unserialize($arf_field->conditional_logic);

		$new_field_options = array();

		$temp_field_array[$field_id]['options'] = json_encode($field_options);

		/* Setting new field options */
		foreach($field_data_obj[$field_type] as $key => $current_field_data ){

            if( $key == 'placeholdertext' ){
                $new_field_options[$key] = isset($field_options['placehodertext']) ? $field_options['placehodertext'] : '';
            } else if( $key == 'default_value' ){
				$new_field_options[$key] = $field_default_value;
			} else if( $key == 'key' ){
				$new_field_options[$key] = $arf_field->field_key;
			} else if( $key == 'name' ){
				$new_field_options[$key] = $arf_field->name;
			} else if( $key == 'description' ){
				$new_field_options[$key] = $field_description;	
			} else if( $key == 'type' ){
				$new_field_options[$key] = $field_type;
			} else if( $key == 'required' ){
				$new_field_options[$key] = $arf_field->required;
			} else if( $key == 'inner_class' ) {

                if($i == 1 || ($define_field_order == $arf_field->field_order)) {
                    $define_field_order = ($arf_field->field_order == 0) ? ($arf_field->field_order + 1) : $arf_field->field_order;
                }
                if($field_type !='imagecontrol' && $field_type != 'hidden' ){

                    if (isset($field_options['classes']) && $field_options['classes'] != 'arf_2' && isset($arf_column_class['two']) && $arf_column_class['two'] == '1') {

                            $need_to_increment = 1;
                            
                            $key_blank = "arf_2col|".$define_field_order;

                            $new_field_order[$key_blank] = $define_field_order;
                            
                            $resize_width_array[$define_field_order] = '49.582';
                            
                            $define_field_order = $define_field_order + 1;

                            $arf_column_class['two'] = '';
                            $arf_column_class['three'] = '';

                            unset($arf_column_class['two']);
                            unset($arf_column_class['three']);

                    } else if (isset($field_options['classes']) && $field_options['classes'] != 'arf_3' && isset($arf_column_class['three']) && $arf_column_class['three'] != '2' && $arf_column_class['three'] == '1') {
                        
                        $need_to_increment = 2;                                                
                        
                        $key_blank = "arf_23col|".$define_field_order;
                        
                        $new_field_order[$key_blank] = $define_field_order;
                        
                        $resize_width_array[$define_field_order] = '33.054';
                        
                        $define_field_order = $define_field_order + 1;
                        
                        $key_blank = "arf_3col|".$define_field_order;
                        
                        $new_field_order[$key_blank] = $define_field_order;
                        
                        $resize_width_array[$define_field_order] = '33.054';
                        
                        $define_field_order = $define_field_order + 2;

                        $arf_column_class['two'] = '';
                        $arf_column_class['three'] = '';

                        unset($arf_column_class['two']);
                        unset($arf_column_class['three']);
                    } else if (isset($field_options['classes']) && $field_options['classes'] != 'arf_3' && isset($arf_column_class['three']) && $arf_column_class['three'] == '2') {
                        
                        $need_to_increment = 1;
                        
                        $key_blank = "arf_3col|".$define_field_order;
                        
                        $new_field_order[$key_blank] = $define_field_order;
                        
                        $resize_width_array[$define_field_order] = '33.054';
                        
                        $define_field_order = $define_field_order + 1;
                        $arf_column_class['two'] = '';
                        $arf_column_class['three'] = '';

                        unset($arf_column_class['two']);
                        unset($arf_column_class['three']);                                               
                    }
                    if($field_options['classes'] == 'arf_1') {
                        
                        $new_field_options[$key] = 'arf_1col';
                        
                        if ($field_type != 'hidden') {
                            $resize_width_array[$define_field_order] = '100.00';
                        }
                        $arf_column_class['two'] = '';
                        $arf_column_class['three'] = '';

                        unset($arf_column_class['two']);
                        unset($arf_column_class['three']);
                    } else if($field_options['classes'] == 'arf_2') {
                        if (isset($field_options['classes']) && $field_options['classes'] == 'arf_2' && empty($arf_column_class['two'])) {
                            $arf_column_class['two'] = '1';

                            $new_field_options[$key] = 'arf21colclass';
                            
                            if ($field_type != 'hidden') {
                                $resize_width_array[$define_field_order] = '49.791';
                            }
                            
                            $arf_column_class['three'] = '';
                            
                            unset($arf_column_class['three']);

                        } else if (isset($field_options['classes']) && $field_options['classes'] == 'arf_2' && isset($arf_column_class['two']) && $arf_column_class['two'] == '1') {                   
                            
                            $new_field_options[$key] = 'arf_2col';
                            
                            if ($field_type != 'hidden') {
                                $resize_width_array[$define_field_order] = '49.582';
                            }
                            
                            $arf_column_class['two'] = '';
                            
                            $arf_column_class['three'] = '';
                            
                            unset($arf_column_class['three']);
                            
                            unset($arf_column_class['two']);
                        }
                    } else if($field_options['classes'] == 'arf_3') {
                        if (isset($field_options['classes']) && $field_options['classes'] == 'arf_3' && empty($arf_column_class['three'])) {
                            
                            $new_field_options[$key] = 'arf31colclass';
                            
                            $arf_column_class['three'] = '1';
                            
                            if ($field_type != 'hidden') {
                                $resize_width_array[$define_field_order] = '33.054';
                            }                                                    
                            
                            $arf_column_class['two'] = '';
                            
                            unset($arf_column_class['two']);
                        } else if (isset($field_options['classes']) && $field_options['classes'] == 'arf_3' && isset($arf_column_class['three']) && $arf_column_class['three'] == '1') {
                                
                                $arf_column_class['three'] = '2';
                                
                                $new_field_options[$key] = 'arf_23col';
                                
                                if ($field_type != 'hidden') {
                                    $resize_width_array[$define_field_order] = '33.054';
                                }
                                
                                $arf_column_class['two'] = '';
                                
                                unset($arf_column_class['two']);
                        } else if (isset($field_options['classes']) && $field_options['classes'] == 'arf_3' && isset($arf_column_class['three']) && $arf_column_class['three'] == '2') {
                            
                            $new_field_options[$key] = 'arf_3col';
                            
                            if ($field_type != 'hidden') {
                                $resize_width_array[$define_field_order] = '33.054';
                            }
                            
                            $arf_column_class['three'] = '';
                            
                            $arf_column_class['two'] = '';
                            
                            unset($arf_column_class['two']);
                            
                            unset($arf_column_class['three']);
                        }
                    } else {
                        $arf_column_class = array();

                    }
                } else {
                    $new_field_options[$key] = 'arf_1col';
                    if ($field_type != 'hidden') {
                        $resize_width_array[$define_field_order] = '100.00';
                    }
                }
			} else {
				$new_field_options[$key] = isset($field_options[$key]) ? $field_options[$key] : '';
			}
		}

		if( in_array($field_type, $fields_with_default_val) ){

			if( $field_default_value != '' && isset($field_options['default_blank']) && $field_options['default_blank'] == 1 ){
				$new_field_options['default_value'] = $field_default_value;
				$new_field_options['placeholdertext'] = '';
			} else if( $field_default_value != '' && isset($field_options['default_blank']) && $field_options['default_blank'] == 0 ){
				$new_field_options['default_value'] = '';
				$new_field_options['placeholdertext'] = $field_default_value;
			}

        } else if( in_array($field_type, $fields_without_default_val) ){
            if( $field_default_value != '' ){
            	$new_field_options['placeholdertext'] = $field_default_value;
            	$new_field_options['default_value'] = '';
            }
        }

		if( isset($field_options['css_outer_wrapper']) && $field_options['css_outer_wrapper'] != '' ){
			$new_form_custom_css .= ".ar_main_div_{$form_id} #arf_field_{$field_id}_container{";
			$new_form_custom_css .= $field_options['css_outer_wrapper'];
			$new_form_custom_css .= "}";
		}

		if( isset($field_options['css_label']) && $field_options['css_label'] != '' ){
			$new_form_custom_css .= ".ar_main_div_{$form_id} #arf_field_{$field_id} label.arf_main_label{";
			$new_form_custom_css .= $field_options['css_label'];
			$new_form_custom_css .= "}";
		}

		if( isset($field_options['css_input_element']) && $field_options['css_input_element'] != '' ){
			$new_form_custom_css .= ".ar_main_div_{$form_id}  #arf_field_{$field_id}_container .controls input{";
			$new_form_custom_css .= $field_options['css_input_element'];
			$new_form_custom_css .= "}";	
		}

		if( isset($field_options['css_description']) && $field_options['css_description'] != '' ){
			$new_form_custom_css .= ".ar_main_div_{$form_id}  #arf_field_{$field_id}_container .arf_field_description{";
			$new_form_custom_css .= $field_options['css_description'];
			$new_form_custom_css .= "}";
		}

		if( isset($field_options['css_add_icon']) && $field_options['css_add_icon'] != '' ){
			$new_form_custom_css .= ".ar_main_div_{$form_id}  #arf_field_{$field_id}_container .arf_prefix_suffix_wrapper";
			$new_form_custom_css .= $field_options['css_add_icon'];
			$new_form_custom_css .= "}";
		}

		if( $field_type != 'hidden' ){
			$new_field_order[$field_id] = $define_field_order;
		}

		if( $field_type == 'html' ){
			$new_field_options['description'] = $field_description;
		} else if( $field_type == 'scale' ){
			$star_rating_color = "#FFF10D";
			if($field_options['star_color'] == 'yellow') {
                $star_rating_color = '#ffab00';
            } else if($field_options['star_color'] == 'red') {
                $star_rating_color = '#da3610';
            } else if($field_options['star_color'] == 'orange') {
                $star_rating_color = '#ff7029';
            } else if($field_options['star_color'] == 'blue') {
                $star_rating_color = '#00bfdd';
            } else if($field_options['star_color'] == 'green') {
                $star_rating_color = '#8dca35';
            } else if($field_options['star_color'] == 'black') {
                $star_rating_color = '#000000';
            } else {
                $star_rating_color ='#FFF10D';
            }

            if( $rating_control == 0 ){
            	$new_form_css['arfstarratingcolor'] = $star_rating_color;
            }

            if( $field_options['star_size'] == "big" ){
                $new_field_options['star_size'] = '30';
            } else if($field_options['star_size'] == 'small') {
                $new_field_options['star_size'] = '18';
            }
            $rating_control++;
		} else if( $field_type == 'like' ){

			if( $like_control == 0 ){
				$new_form_css['arflikebtncolor'] = $field_options["like_bg_color"];
				$new_form_css['arfdislikebtncolor'] = $field_options["dislike_bg_color"];
			}
			unset($new_field_options['like_bg_color']);
			unset($new_field_options['dislike_bg_color']);
			$like_control++;

		} else if( $field_type == 'slider' || $field_type == 'arfslider'){

			if( $slider_control == 0 ){
				$new_form_css['arfsliderselectioncolor'] = $field_options['slider_bg_color'];
				$new_form_css['arfslidertrackcolor'] = $field_options['slider_bg_color2'];
			}
			unset($new_field_options['slider_bg_color']);
			unset($new_field_options['slider_bg_color2']);
			$slider_control++;

		} else if( $field_type == 'file' ){
			
			$new_field_options['max_fileuploading_size'] = 'auto';
            $new_field_options['arf_is_multiple_file'] = 0;
            $new_field_options['arf_draggable'] = 0;
            $new_field_options['arf_dragable_label'] = addslashes(esc_html__('Drop files here or click to select', 'ARForms'));
            $new_field_options['invalid_file_size'] = addslashes(esc_html__('Invalid File Size', 'ARForms'));

		} else if( $field_type == 'text' ){
			
			$new_field_options['single_custom_validation'] = 'custom_validation_none';
            $new_field_options['arf_is_regular_expression'] = 0;
            $new_field_options['arf_regular_expression'] = '';
            $new_field_options['arf_regular_expression_msg'] = addslashes(esc_html__('Entered value is invalid', 'ARForms'));

		} else if( $field_type == 'date' ){
			
			$new_field_options['selectdefaultdate'] = '';
            $new_field_options['currentdefaultdate'] = 0;
            
            /* Updating entry values for this field */
            $field_meta_vlaue = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `".$MdlDb->entry_metas."` WHERE field_id=%d ORDER BY id", $field_id ) );
            $total_meta_value = count($field_meta_vlaue);

            if( $total_meta_value > 0 ){
            	foreach($field_meta_vlaue as $meta_key => $meta_field ){
            		$formate = $new_form_options['date_format'];
                    $entry_value = $meta_field->entry_value;

                    $entry_value = date('Y-m-d H:i:s', strtotime($entry_value));
                    
                    if($entry_value == '1970-01-01 00:00:00'){
                        $explode_date = explode('/',$meta_field->entry_value);
                        if(count($explode_date) >= 3){
                            $entry_value = $explode_date[1].'/'.$explode_date[0].'/'.$explode_date[2];
                            $entry_value = date('Y-m-d H:i:s', strtotime($entry_value));
                        }
                    }

                    $wpdb->update($MdlDb->entry_metas, array('entry_value' => $entry_value), array('id' => $meta_field->id));
            	}
            }

		} else if( $field_type == 'email' || $field_type == 'password' ){
			if( $field_type == 'password' && $conf_pass_field > 0 ){
    			/* Disable confirm password field if form has already one confirm password field */
				$new_field_options['confirm_password'] = 0;
			}

			if( isset($field_options['confirm_'.$field_type]) && $field_options['confirm_'.$field_type] == 1 ){

                if($field_options['classes'] == 'arf_1'){
                    $confirm_field_classes = $new_field_options['confirm_'.$field_type.'_classes'] = 'arf_1';
                    $confirm_field_inner_classes = $new_field_options['confirm_'.$field_type.'_inner_classes'] = 'arf_1col';
                    
                    $arf_column_class['two'] = '';
                    $arf_column_class['three'] = '';
                    unset($arf_column_class['two']);
                    unset($arf_column_class['three']);

                } else if($field_options['classes'] == 'arf_2' and isset($arf_column_class['two']) and $arf_column_class['two'] == '1'){
                    $confirm_field_classes = $new_field_options['confirm_'.$field_type.'_classes'] = 'arf_2';
                    $confirm_field_inner_classes = $new_field_options['confirm_'.$field_type.'_inner_classes'] = 'arf_2col';

                    $arf_column_class['two'] = '';
                    $arf_column_class['three'] = '';
                    unset($arf_column_class['three']);
                    unset($arf_column_class['two']);

                } else if($field_options['classes'] == 'arf_2' and !isset($arf_column_class['two'])){
                    $confirm_field_classes = $new_field_options['confirm_'.$field_type.'_classes'] = 'arf_2';
                    $confirm_field_inner_classes = $new_field_options['confirm_'.$field_type.'_inner_classes'] = 'arf21colclass';
                    
                    $arf_column_class['two'] = '1';
                    $arf_column_class['three'] = '';
                    unset($arf_column_class['three']);
                } else if (isset($field_options['classes']) and $field_options['classes'] == 'arf_3' and isset($arf_column_class['three']) and $arf_column_class['three'] == '1') {
                    
                    $confirm_field_classes = $new_field_options['confirm_'.$field_type.'_classes'] = 'arf_3';
                    $confirm_field_inner_classes = $new_field_options['confirm_'.$field_type.'_inner_classes'] = 'arf_23col';

                    $arf_column_class['three'] = '2';
                    $arf_column_class['two'] = '';
                    unset($arf_column_class['two']);

                } else if (isset($field_options['classes']) and $field_options['classes'] == 'arf_3' and isset($arf_column_class['three']) and $arf_column_class['three'] == '2') {
                    $confirm_field_classes = $new_field_options['confirm_'.$field_type.'_classes'] = 'arf_3';
                    $confirm_field_inner_classes = $new_field_options['confirm_'.$field_type.'_inner_classes'] = 'arf_3col';
                    $arf_column_class['three'] = '';
                    $arf_column_class['two'] = '';
                    unset($arf_column_class['two']);
                    unset($arf_column_class['three']);
                } else if (isset($field_options['classes']) and $field_options['classes'] == 'arf_3' and !isset($arf_column_class['three'])) {
                    $confirm_field_classes = $new_field_options['confirm_'.$field_type.'_classes'] = 'arf_3';
                    $confirm_field_inner_classes = $new_field_options['confirm_'.$field_type.'_inner_classes'] = 'arf31colclass';
                    $arf_column_class['three'] = '1';
                    $arf_column_class['two'] = '';
                    unset($arf_column_class['two']);                                            
                }

                $need_to_increment = 1;
                $define_field_order = $define_field_order + 1;
                $key_blank = $field_id."_confirm";
                $new_field_order[$key_blank] = $define_field_order;
                $temp_fields['confirm_'.$field_type.'_'.$field_id]['key'] = $armainhelper->get_unique_key('', $MdlDb->fields, 'field_key');
                $temp_fields['confirm_'.$field_type.'_'.$field_id]['order'] = $define_field_order;
                $temp_fields['confirm_'.$field_type.'_'.$field_id]['parent_field_id'] = $field_id;
                $temp_fields['confirm_'.$field_type.'_'.$field_id]['confirm_inner_class'] = $confirm_field_inner_classes;

				if( $field_type == 'password' ){
					$conf_pass_field++; // increase counter for password
				}
			}
		} else if( $field_type == 'divider' ){
			if($section_counter == 0){
                $new_form_css['arfsectiontitlefamily'] = $field_options['arf_divider_font'];
                $new_form_css['arfsectiontitlefontsizesetting'] = $field_options['arf_divider_font_size'];
                if($new_field_options['arf_divider_font_style'] == 'bold' || $new_field_options['arf_divider_font_style'] == 'italic'){
                    $new_form_css['arfsectiontitleweightsetting'] = $field_options['arf_divider_font_style'];                                            
                } else {
                    $new_form_css['arfsectiontitleweightsetting'] = '';
                }
                $new_form_css['arf_divider_inherit_bg'] = (isset($field_options['arf_divider_inherit_bg']) && $field_options['arf_divider_inherit_bg'] == '') ? 1 : 0;
                $new_form_css['arfformsectionbackgroundcolor'] = $field_options['arf_divider_bg_color'];
            }
            $section_counter++;
		}

        /* Check page break control and increase counter for it */
        if( $field_type == 'break' ){
        	$total_page_breaks++;
            $page_break[] = $field_id;

            if ($field_options['page_break_type'] == 'survey') {
                $arf_page_break_survey = true;
            }

            if ($field_options['page_break_type'] == 'wizard') {
                $arf_page_break_wizard = true;
            }
        }

        /* set flag to load font awesome css */
        if( (isset($field_options['enable_arf_prefix']) && $field_options['enable_arf_prefix'] == 1) || (isset($field_options['enable_arf_suffix']) && $field_options['enable_arf_suffix'] ) || $new_form_css['arfcheckradiostyle'] == 'custom' || $field_type == 'smiley' || $field_type == 'scale' ){
        	$is_font_awesome = true;
        }

        /* set flag to load input mask library */
        if ($field_type == 'phone' && ( $field_options['phone_validation'] != 'international') ) {
            $is_input_mask = true;
        }

        /* set flag to load either simple colorpicker or advanced colorpicker library */
        if ($field_type == 'colorpicker') {
            if ($field_options['colorpicker_type'] == 'basic') {
                $normal_color_picker = true;
            }
            if ($field_options['colorpicker_type'] == 'advanced') {
                $advanced_color_picker = true;
            }
        }

        /* set flag to load animate number js */
        if( $field_type == 'html' && isset($field_options['enable_total']) && $field_options['enable_total'] == 1){
        	$animate_number = true;
        	$html_running_total_field_array[] = $field_id;
        }

        /* set flag to check form has google captcha */
        if( $field_type == 'captcha' && $field_options['is_recaptcha'] == 'recaptcha') {
        	$google_captcha_loaded = true;
        }

        /* set flag to check form has imagecontrol field or not */
        if ($field_type == 'imagecontrol') {
            $is_imagecontrol_field = true;
        }

        /* Conditional Logic */
        if( isset($field_conditional_logic['enable']) && $field_conditional_logic['enable'] == 1 ){
        	
        	if( !isset($new_conditional_logic[$cl]) ){
        		$new_conditional_logic[$cl] = array();
        	}
        	$new_conditional_logic[$cl]['id'] = $cl;

        	$new_conditional_logic[$cl]['logical_operator'] = $field_conditional_logic['if_cond'];

        	$rules = $field_conditional_logic['rules'];
        	$cn = 0;
    		$new_conditional_logic[$cl]['condition'] = array();
        	foreach( $rules as $k => $condition ){

        		$new_conditional_logic[$cl]['condition'][$cn] = array();

        		$new_conditional_logic[$cl]['condition'][$cn]['condition_id'] = $cn;
        		$new_conditional_logic[$cl]['condition'][$cn]['field_id'] = $condition['field_id'];
        		$new_conditional_logic[$cl]['condition'][$cn]['field_type'] = $condition['field_type'];
        		$new_conditional_logic[$cl]['condition'][$cn]['operator'] = $condition['operator'];
        		$new_conditional_logic[$cl]['condition'][$cn]['value'] = $condition['value'];

        		$wpdb->update($MdlDb->fields,array('conditional_logic' => 1),array('id' => $condition['field_id']));

        		$cn++;
        	}

        	$new_conditional_logic[$cl]['result'] = array();

        	$new_conditional_logic[$cl]['result'][0] = array();

        	$new_conditional_logic[$cl]['result'][0]['result_id'] = 0;
        	$new_conditional_logic[$cl]['result'][0]['field_id'] = $field_id;
        	$new_conditional_logic[$cl]['result'][0]['field_type'] = $field_type;
        	$new_conditional_logic[$cl]['result'][0]['action'] = $field_conditional_logic['display'];
        	$new_conditional_logic[$cl]['result'][0]['value'] = '';

        	$cl++;
        }
        /* Conditional Logic End */

        /* Running Total */
        if( $field_type == 'html' && isset($field_options['enable_total']) && $field_options['enable_total'] == 1 ){
        	$html_content = $field_description;
        	$formula_pattern = "/\<arftotal\>(.*?)\<\/arftotal\>/is";
        	if( preg_match($formula_pattern,$html_content,$matches) ){
        		if( isset($matches[0]) && $matches[0] != '' && !empty($matches[0]) ){

        			$formula_content = $matches[0];

        			$ids_pattern = "/\[(.*?)\:(\d+)(|\.(\d+))\]/";

        			preg_match_all($ids_pattern,$formula_content,$match_ids);

        			if( isset($match_ids[2]) && is_array($match_ids[2]) && !empty($match_ids[2]) ){
        				foreach($match_ids[2] as $matched_id ){
        					$fid = $matched_id;
        					$get_cl_fields = $wpdb->get_row( $wpdb->prepare("SELECT `enable_running_total` FROM `".$MdlDb->fields."` WHERE id = %d", $fid) );
        					if( empty($get_cl_fields->enable_running_total) || $get_cl_fields->enable_running_total == '' ){
        						$wpdb->update($MdlDb->fields,array('enable_running_total'=>$field_id),array('id' => $fid) );
        					} else {
        						$running_total_fields = explode(',',$get_cl_fields->enable_running_total);
        						if( !in_array($field_id, $running_total_fields) ){
        							array_push($running_total_fields,$field_id);
        							$wpdb->update( $MdlDb->fields, array( 'enable_running_total'=>implode( ',', $running_total_fields) ), array('id' => $fid) );
        						}
        					}
        				}
        			}
        		}
        	}
        }
        /* Running Total End */
        if( $field_type != 'hidden' ){
            $define_field_order++;
        }

        if($i === $total_fields) {
            if (isset($new_field_options['classes']) and $new_field_options['classes'] == 'arf_2' and isset($arf_column_class['two']) and $arf_column_class['two'] == '1') { 
                $need_to_increment = 1;
                $key_blank = "arf_2col|".$define_field_order;
                $new_field_order[$key_blank] = $define_field_order;
                $resize_width_array[$define_field_order] = '49.582';
                $define_field_order = $define_field_order + 1;
            } else if (isset($new_field_options['classes']) and $new_field_options['classes'] == 'arf_3' and isset($arf_column_class['three']) and $arf_column_class['three'] != '2' and $arf_column_class['three'] == '1') {
                $need_to_increment = 2;
                $key_blank = "arf_23col|".$define_field_order;
                $new_field_order[$key_blank] = $define_field_order;
                $resize_width_array[$define_field_order] = '33.054';
                $define_field_order = $define_field_order + 1;
                $key_blank = "arf_3col|".$define_field_order;
                $new_field_order[$key_blank] = $define_field_order;
                $resize_width_array[$define_field_order] = '33.054';
                $define_field_order = $define_field_order + 2;
            }
            else if (isset($new_field_options['classes']) and $new_field_options['classes'] == 'arf_3' and isset($arf_column_class['three']) and $arf_column_class['three'] == '2') {
                $need_to_increment = 1;
                $key_blank = "arf_3col|".$define_field_order;
                $new_field_order[$key_blank] = $define_field_order;
                $resize_width_array[$define_field_order] = '33.054';
                $define_field_order = $define_field_order + 1;
            }
        }
        $i++;
        $temp_field_array[$field_id]['new_options'] = json_encode($new_field_options);
        if( $arf_field->options != "" ){
        	//$new_field_opt = json_encode($f_options);
        	$new_field_opt = maybe_unserialize($arf_field->options);
        	$new_field_opt = json_encode($new_field_opt);
        	$wpdb->update($MdlDb->fields,array('options' => $new_field_opt), array('id' => $field_id) );
        }
        $wpdb->update( $MdlDb->fields, array('field_options' => json_encode($new_field_options)), array('id' => $field_id) );
	} /* Ending of field loop */

	update_option('arf_field_options_'.$form_id,json_encode($temp_field_array));

	if( !empty($custom_captcha_field_id) && count($custom_captcha_field_id) > 0 ){
		foreach( $new_field_order as $order_key => $order_value ){
			if( in_array($order_key,$custom_captcha_field_id) ){
				$fopt = $wpdb->get_row( $wpdb->prepare("SELECT `field_options` FROM `".$MdlDb->fields."` WHERE id = %d",$order_key) );
				$fopt = json_decode( $fopt->field_options );
				$new_order_value = $fopt->inner_class.'|'.$order_value;
				unset($new_field_order[$order_key]);
				$new_field_order[$new_order_value] = $order_value;
				$wpdb->delete( $MdlDb->fields,array('id' => $order_key) );
			}
		}

	}

	$new_form_options['arf_form_other_css'] = $new_form_custom_css;

	$new_form_options['arf_loaded_field'] = $arf_loaded_field;

	$new_form_options['arf_conditional_logic_rules'] = $new_conditional_logic;

	$new_form_options['arf_field_resize_width'] = isset($resize_width_array) ? json_encode($resize_width_array) : array();
	$new_form_options['arf_field_order'] = isset($new_field_order) ? json_encode($new_field_order) : array();

	$new_form_options['total_page_break'] = $total_page_breaks;
    $new_form_options['page_break_field'] = $page_break;
    $new_form_options['font_awesome_loaded'] = $is_font_awesome;
    $new_form_options['tooltip_loaded'] = $is_tooltip;
    $new_form_options['arf_input_mask'] = $is_input_mask;
    $new_form_options['arf_normal_colorpicker'] = $normal_color_picker;
    $new_form_options['arf_advance_colorpicker'] = $advanced_color_picker;
    $new_form_options['arf_number_animation'] = $animate_number;
    $new_form_options['arf_page_break_survey'] = $arf_page_break_survey;
    $new_form_options['arf_page_break_wizard'] = $arf_page_break_wizard;
    $new_form_options['html_running_total_field_array'] = $html_running_total_field_array;
    $new_form_options['google_captcha_loaded'] = $google_captcha_loaded;
    $new_form_options['is_imagecontrol_field'] = $is_imagecontrol_field;

    $submit_conditional_logic = isset($new_form_options['submit_conditional_logic']) ? $new_form_options['submit_conditional_logic'] : array();

    if( isset($submit_conditional_logic['rules']) && count($submit_conditional_logic['rules']) > 0 ){
        foreach( $submit_conditional_logic['rules'] as $skey => $srule ){
            $submit_field_id = $srule['field_id'];
            $wpdb->update($MdlDb->fields,array('conditional_logic'=>1),array('id' => $submit_field_id));
        }
    }

    /* rewrite form css */
    if( count($new_form_css) > 0 ){
    	$new_values = array();
        foreach ($new_form_css as $k => $v) {
            $new_values[$k] = $v;
            if( preg_match("/auto/",$new_values[$k]) ){
                $new_values[$k] = str_replace("px","",$new_values[$k]);
            }
        }

        update_option('arf_form_css_'.$form_id,json_encode( $new_values) );

        $saving = true;
        $use_saved = true;
        $arfssl = (is_ssl()) ? 1 : 0;

        $filename = FORMPATH . '/core/css_create_main.php';

        $temp_css_file = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
        $temp_css_file .= "\n";
        ob_start();
        include $filename;
        $temp_css_file .= ob_get_contents();
        ob_end_clean();
        $temp_css_file .= "\n " . $warn;
        $wp_upload_dir = wp_upload_dir();
        $dest_dir = $wp_upload_dir['basedir'] . '/arforms/maincss/';
        $css_file_new = $dest_dir . 'maincss_' . $form_id. '.css';

        WP_Filesystem();
        global $wp_filesystem;
        $wp_filesystem->put_contents($css_file_new, $temp_css_file, 0777);

        $filename1 = FORMPATH . '/core/css_create_materialize.php';
        $temp_css_file1 = $warn1 = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
        $temp_css_file1 .= "\n";
        ob_start();
        include $filename1;
        $temp_css_file1 .= ob_get_contents();
        ob_end_clean();
        $temp_css_file1 .= "\n " . $warn1;
        $wp_upload_dir = wp_upload_dir();
        $dest_dir = $wp_upload_dir['basedir'] . '/arforms/maincss/';
        $css_file_new1 = $dest_dir . 'maincss_materialize_' . $form_id. '.css';

        WP_Filesystem();
        global $wp_filesystem;
        $wp_filesystem->put_contents($css_file_new1, $temp_css_file1, 0777);
    }

    $args = array(
    	'options' => maybe_serialize($new_form_options),
    	'form_css' => maybe_serialize($new_form_css),
    	'temp_fields' => maybe_serialize($temp_fields)
    );

    $where = array(
    	'id' => $form_id
    );

    $wpdb->update($MdlDb->forms, $args, $where );
} /* Ending of Form's loop */


$wpdb->query("ALTER TABLE `" . $MdlDb->fields . "` DROP COLUMN `description`");
$wpdb->query("ALTER TABLE `" . $MdlDb->fields . "` DROP COLUMN `default_value`");

$wpdb->query("ALTER TABLE `".$MdlDb->fields."` CHANGE `conditional_logic` `conditional_logic` TINYINT(1) NULL DEFAULT '0'");

$addon_array = array(
    '0' => array('name'=>'arformsauthorizenet','table_name'=>$wpdb->prefix.'arf_authorizenet_forms','attr'=> 'form_id','update_value'=> 'authorize.net'),
    '1' => array('name'=>'arformsmymail','table_name'=>$wpdb->prefix.'arf_my_mail_forms','attr'=> 'form_id','update_value'=> 'mailster'),
    '2' => array('name'=>'arformspaypal','table_name'=>$wpdb->prefix.'arf_paypal_forms','attr'=> 'form_id','update_value'=> 'paypal'),
    '3' => array('name'=>'arformspaypalpro','table_name'=>$wpdb->prefix.'arf_paypalpro_forms','attr'=> 'form_id','update_value'=> 'paypal_pro'),
    '4' => array('name'=>'arformspostcreator','table_name'=>$wpdb->prefix.'arf_postcreator_forms','attr'=> 'form_id','update_value'=> 'postcreator'),
    '5' => array('name'=>'arformsstripe','table_name'=>$wpdb->prefix.'arf_stripe_forms','attr'=> 'form_id','update_value'=> 'stripe'),
    '6' => array('name'=>'arformsusersignup','table_name'=> $wpdb->prefix.'arf_user_registration_forms','attr'=> 'form_id','update_value'=> 'userregistration'),
);
//$addon_array = array('arformsauthorizenet','arformsmymail','arformspaypal','arformspaypalpro','arformspostcreator','arformsstripe','arformsusersignup');
foreach ($addon_array as $key => $value) {
    $addon_plugin = $value['name'].'/'.$value['name'].'.php';
    if (is_plugin_active($addon_plugin)) {
        $res = $wpdb->get_results('SELECT '.$value['attr'].' FROM ' . $value['table_name'], ARRAY_A);       
        if($res > 0) {
            foreach ($res as $key => $value_form) {
                $form_id = $value_form[$value['attr']];
                $form_data = $arfform->getOne($form_id);
                $form_options = maybe_unserialize($form_data->arf_mapped_addon);
                if(isset($form_options['arf_mapped_addon']) && !empty($form_options['arf_mapped_addon'])) {
                    if(!in_array($value['update_value'],$form_options['arf_mapped_addon']))
                    {
                        array_push($form_options['arf_mapped_addon'],$value['update_value']);
                    }
                } else {
                	$form_options = array();
                    $form_options['arf_mapped_addon'] = array($value['update_value']);
                }                                
                $wpdb->update($MdlDb->forms,array('arf_mapped_addon'=>maybe_serialize($form_options)), array('id' =>$form_id));
                
            }
        }
    }
}

/* Delete Old Templates and Install New */
$arf_update_templates = true;
$wpdb->query( $wpdb->prepare("DELETE FROM `".$MdlDb->forms."` WHERE id < %d",8) );

$wpdb->query( $wpdb->prepare("DELETE FROM `".$MdlDb->fields."` WHERE form_id < %d", 8));

$arfsettings = get_transient('arf_options');

if (!is_object($arfsettings)) {
    if ($arfsettings) {
        $arfsettings = maybe_unserialize(maybe_serialize($arfsettings));
    } else {
        $arfsettings = get_option('arf_options');

        if (!is_object($arfsettings)) {
            if ($arfsettings){
                $arfsettings = maybe_unserialize(maybe_serialize($arfsettings));
            } else {
                $arfsettings = new arsettingmodel();
            }
            update_option('arf_options', $arfsettings);
            set_transient('arf_options', $arfsettings);
        }
    }
}

$arfsettings->set_default_options();

$style_settings = get_transient('arfa_options');
if (!is_object($style_settings)) {
    if ($style_settings) {
        $style_settings = maybe_unserialize(maybe_serialize($style_settings));
    } else {
        $style_settings = get_option('arfa_options');
        if (!is_object($style_settings)) {
            if ($style_settings)
                $style_settings = maybe_unserialize(maybe_serialize($style_settings));
            else
                $style_settings = new arstylemodel();
            update_option('arfa_options', $style_settings);
            set_transient('arfa_options', $style_settings);
        }
    }
}
$style_settings = get_option('arfa_options');
if (!is_object($style_settings)) {
    if ($style_settings)
        $style_settings = maybe_unserialize(maybe_serialize($style_settings));
    else
        $style_settings = new arstylemodel();
    update_option('arfa_options', $style_settings);
}

$style_settings->set_default_options();
$style_settings->store();

include(MODELS_PATH."/artemplate.php");