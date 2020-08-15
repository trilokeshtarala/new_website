<?php
global $wpdb, $arf_memory_limit, $memory_limit, $arfversion, $aresponder, $responder_fname, $responder_lname, $responder_email, $mailchimpkey, $mailchimpid, $infusionsoftkey, $aweberkey, $aweberid, $getresponsekey, $getresponseid, $gvokey, $gvoid, $ebizackey, $ebizacid, $style_settings, $arfsettings, $arformhelper, $arrecordcontroller, $armainhelper, $arformcontroller, $arfieldhelper, $maincontroller, $arfadvanceerrcolor, $MdlDb, $arffield, $arfform, $arfajaxurl;

if (isset($arf_memory_limit) && isset($memory_limit) && ($arf_memory_limit * 1024 * 1024) > $memory_limit) {
    @ini_set("memory_limit", $arf_memory_limit . 'M');
} 

/* arf_dev_flag Temp CSS for Query Monitor */
echo "<style type='text/css'>.qm-js#qm{position:relative;z-index:999;}.notice.arf-notice-update-warning{display:none !important;}</style>";

$id = (isset($_REQUEST['id']) && $_REQUEST['id'] != '' ) ? $_REQUEST['id'] : 0;

if ($action == 'duplicate' || $action == 'edit') {
    $record = $wpdb->get_row($wpdb->prepare("SELECT * FROM `" . $MdlDb->forms . "` WHERE id = %d", $id));
}

if($action == 'edit'){
    if(empty($record)){
        echo '<script type="text/javascript">window.location.href = "' . admin_url('admin.php?page=ARForms') . '";</script>';
    }
}

if (isset($record) && $record->is_template && $_REQUEST['arfaction'] != 'duplicate') {
    wp_die(addslashes(esc_html__('That template cannot be edited', 'ARForms')));
}
if( !isset($record) ){
    $record = new stdClass();
}

$values = array();
$values['fields'] = array();
$arf_all_fields = array();
$record_arr = (array)$record;

if (!empty($record_arr)) {
    $values['id'] = $form_id = $record->id;
    $values['form_key'] = $record->form_key;
    $values['description'] = $record->description;
    $values['name'] = $record->name;
    $values['form_name'] = $record->name;
    $all_fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM `" . $MdlDb->fields . "` WHERE form_id = %d ORDER BY ID ASC", $form_id));
}

$field_list = array();
$include_fields = array();
$exclude = array('divider', 'captcha', 'break');
$all_hidden_fields = array();
$responder_list_option = "";
if (!empty($all_fields)) {
    foreach ($all_fields as $key => $field_) {
        if( !in_array($field_->id,$exclude) && $field_->type == 'hidden') {
            $all_hidden_fields[] = $field_;
            $include_fields[] = $field_->id;
            continue;
        }
        foreach ($field_ as $k => $field_val) {
            if ($k == 'type' && !in_array($field_val, $exclude)) {
                $include_fields[] = $field_->id;
            }
            if ($k == 'options') {
                $arf_all_fields[$key][$k] = json_decode($field_val, true);
                if (json_last_error() != JSON_ERROR_NONE) {
                    $arf_all_fields[$key][$k] = maybe_unserialize($field_val);
                }
            } else if ($k == 'field_options') {
                $field_opts = json_decode($field_val, true);
                if (json_last_error() != JSON_ERROR_NONE) {
                    $field_opts = maybe_unserialize($field_val);
                }
                if( isset($field_opts) && is_array($field_opts) ){ /* arf_dev_flag-3.0 - please check this condition for import/export */
                    foreach ($field_opts as $ki => $val_) {
                        $arf_all_fields[$key][$ki] = $val_;
                    }
                }
            } else {
                $arf_all_fields[$key][$k] = $field_val;
            }
        }
    }
    foreach ($all_fields as $key => $field_) {
        foreach ($field_ as $k => $field_val) {
            if (in_array($field_->id, $include_fields)) {
                if (!isset($field_list[$key])) {
                    $field_list[$key] = new stdClass();
                }
                if ($k == 'options') {
                    $fOpt = json_decode($field_val, true);
                    if (json_last_error() != JSON_ERROR_NONE) {
                        $fOpt = maybe_unserialize($field_val);
                    }
                    $field_list[$key]->$k = $fOpt;
                } else if ($k == 'field_options') {
                    $field_opts = json_decode($field_val, true);
                    if (json_last_error() != JSON_ERROR_NONE) {
                        $field_opts = maybe_unserialize($field_val);
                    }
                    $field_list[$key]->$k = $field_opts;
                } else {
                    $field_list[$key]->$k = $field_val;
                }
            }
        }
    }
    $values['fields'] = $arf_all_fields;
}

$field_data = file_get_contents(VIEWS_PATH . '/arf_editor_data.json');

$field_data_obj = json_decode($field_data);
$form_opts = isset($record->options) ? maybe_unserialize($record->options) : array();
$form_opts = $arformcontroller->arf_html_entity_decode($form_opts);



if (is_array($form_opts) && !empty($form_opts) ) {

    foreach ($form_opts as $opt => $value) {

        if (in_array($opt, array('email_to', 'reply_to', 'reply_to_name','admin_cc_email','admin_bcc_email'))) {

            $values['notification'][0][$opt] = $armainhelper->get_param('notification[0][' . $opt . ']', $value);
            
        }

        $values[$opt] = $armainhelper->get_param($opt, $value);
    }
}


$form_defaults = $arformhelper->get_default_opts();

foreach ($form_defaults as $opt => $default) {


    if (!isset($values[$opt]) or $values[$opt] == '') {
        if ($opt == 'notification') {
            $values[$opt] = ($_POST and isset($_POST[$opt])) ? $_POST[$opt] : $default;
            foreach ($default as $o => $d) {
                if ($o == 'email_to') {
                    $d = '';
                }
                $values[$opt][0][$o] = ($_POST and isset($_POST[$opt][0][$o])) ? $_POST[$opt][0][$o] : $d;
                unset($o);
                unset($d);
            }
        } else {
            $values[$opt] = ($_POST and isset($_POST['options'][$opt])) ? $_POST['options'][$opt] : $default;
        }
    }

    unset($opt);
    unset($defaut);
}
$responder_fname = isset($record->autoresponder_fname) ? $record->autoresponder_fname : '';
$responder_lname = isset($record->autoresponder_lname) ? $record->autoresponder_lname : '';
$responder_email = isset($record->autoresponder_email) ? $record->autoresponder_email : '';

$arffield_selection = $arfieldhelper->field_selection();

$display = apply_filters('arfdisplayfieldoptions', array('label_position' => true));



wp_enqueue_script('sack');
$key = isset($record->form_key) ? $record->form_key : '';

$form_temp_key = '';
if (!isset($record->form_key)) {
    global $armainhelper;
    $possible_letters = '23456789bcdfghjkmnpqrstvwxyz';
    $random_dots = 0;
    $random_lines = 20;

    $form_temp_key = '';
    $i = 0;
    while ($i < 8) {
        $form_temp_key .= substr($possible_letters, mt_rand(0, strlen($possible_letters) - 1), 1);
        $i++;
    }
}

$pre_link = (isset($record->form_key)) ? $arformhelper->get_direct_link($record->form_key) : $arformhelper->get_direct_link($form_temp_key);

$wp_format_date = get_option('date_format');


$data = "";

$data = isset($record) ? $record : '';

$data = $arformcontroller->arfObjtoArray($data);
$aweber_arr = "";
$aweber_arr = isset($data['form_css']) ? $data['form_css'] : '';

$values_nw = isset($data['options']) ? maybe_unserialize($data['options']) : array();

$arr = maybe_unserialize($aweber_arr);

$newarr = array();
if (isset($arr) && !empty($arr) && is_array($arr)) {
    foreach ($arr as $k => $v) {
        $newarr[$k] = $v;
    }
}
$arfinputstyle_template = (isset($_GET['templete_style']) && $_GET['templete_style'] !='') ? $_GET['templete_style'] : ((isset($newarr['arfinputstyle']) && $newarr['arfinputstyle'] !='') ? $newarr['arfinputstyle'] : 'material');


$skinJsonFile = file_get_contents(VIEWS_PATH . '/arf_editor_data.json');

$skinJson = json_decode(stripslashes($skinJsonFile));

$skinJson = apply_filters('arf_form_fields_outside', $skinJson,$arfinputstyle_template);

if (empty($newarr)) {
    $default_data_varible = 'default_data_'.$arfinputstyle_template;
    
    $custom_css_data = $arformcontroller->arfObjtoArray($skinJson->$default_data_varible);
    foreach ($custom_css_data as $k => $v) {
        $newarr[$k] = $v;
    }
}
$newarr['arfinputstyle']  = (isset($_GET['templete_style']) && $_GET['templete_style'] !='') ? $_GET['templete_style'] : ((isset($newarr['arfinputstyle']) && $newarr['arfinputstyle'] !='') ? $newarr['arfinputstyle'] : 'material');


if(isset($_REQUEST['arf_rtl_switch_mode']) && $_REQUEST['arf_rtl_switch_mode']=="yes" ) {
    $newarr['arfformtitlealign'] = "right";
    $newarr['form_align'] = "right";
    $newarr['arfdescalighsetting'] = 'right';
    $newarr['align'] = "right";
    $newarr['text_direction'] = '0';
    $newarr['arfsubmitalignsetting'] = "right";
}


$values_nw['display_title_form'] = isset($values_nw['display_title_form']) ? $values_nw['display_title_form'] : (isset($newarr['display_title_form']) ? $newarr['display_title_form']  : 1);


$active_skin = (isset($newarr['arfmainform_color_skin']) && $newarr['arfmainform_color_skin'] != '') ? $newarr['arfmainform_color_skin'] : 'cyan';

foreach ($newarr as $k => $v) {
    if (strpos($v, '#') === FALSE) {
        if (( preg_match('/color/', $k) or in_array($k, array('arferrorbgsetting', 'arferrorbordersetting', 'arferrortextsetting')) ) && !in_array($k, array('arfcheckradiocolor'))) {
            $newarr[$k] = '#' . $v;
        } else {
            $newarr[$k] = $v;
        }
    }
}



    /* Form Section */
    
    $skinJson->skins->custom->form->title = (isset($newarr['arfmainformtitlecolorsetting']) && $newarr['arfmainformtitlecolorsetting'] != '') ? esc_attr($newarr['arfmainformtitlecolorsetting']) : $skinJson->skins->cyan->form->title;

    $skinJson->skins->custom->form->description = (isset($newarr['arfmainformtitlecolorsetting']) && $newarr['arfmainformtitlecolorsetting'] != '') ? esc_attr($newarr['arfmainformtitlecolorsetting']) : $skinJson->skins->cyan->form->description;

    $skinJson->skins->custom->form->border = (isset($newarr['arfmainfieldsetcolor']) && $newarr['arfmainfieldsetcolor'] != "") ? esc_attr($newarr['arfmainfieldsetcolor']) : $skinJson->skins->cyan->form->border;

    $skinJson->skins->custom->form->background = (isset($newarr['arfmainformbgcolorsetting']) && $newarr['arfmainformbgcolorsetting'] != '' ) ? esc_attr($newarr['arfmainformbgcolorsetting']) : $skinJson->skins->cyan->form->background;

    $skinJson->skins->custom->form->shadow = (isset($newarr['arfmainformbordershadowcolorsetting']) && $newarr['arfmainformbordershadowcolorsetting'] != '') ? esc_attr($newarr['arfmainformbordershadowcolorsetting']) : $skinJson->skins->cyan->form->shadow;

    $skinJson->skins->custom->form->section_background = (isset($newarr['arfformsectionbackgroundcolor']) && $newarr['arfformsectionbackgroundcolor'] != '') ? esc_attr($newarr['arfformsectionbackgroundcolor']) : $skinJson->skins->cyan->form->section_background;


    /* Tooltip Section */

    $skinJson->skins->custom->tooltip->background = ( isset($newarr['arf_tooltip_bg_color']) && $newarr['arf_tooltip_bg_color'] != "" ) ? esc_attr($newarr['arf_tooltip_bg_color']) : $skinJson->skins->cyan->tooltip->background;

    $skinJson->skins->custom->tooltip->text = ( isset($newarr['arf_tooltip_font_color']) && $newarr['arf_tooltip_font_color'] != "" ) ? esc_attr($newarr['arf_tooltip_font_color']) : $skinJson->skins->cyan->tooltip->text;

    /* Page Break Section */

    $skinJson->skins->custom->pagebreak->active_tab = (isset($newarr['bg_color_pg_break']) && $newarr['bg_color_pg_break']) ? esc_attr($newarr['bg_color_pg_break']) : $skinJson->skins->cyan->pagebreak->active_tab;

    $skinJson->skins->custom->pagebreak->inactive_tab = (isset($newarr['bg_inavtive_color_pg_break']) && $newarr['bg_inavtive_color_pg_break'] != '' ) ? esc_attr($newarr['bg_inavtive_color_pg_break']) : $skinJson->skins->cyan->pagebreak->inactive_tab;
    
    $skinJson->skins->custom->pagebreak->text = ( isset($newarr['text_color_pg_break']) && $newarr['text_color_pg_break'] != '' ) ? esc_attr($newarr['text_color_pg_break']) : $skinJson->skins->cyan->pagebreak->text;

    /* Survey Section */

    $skinJson->skins->custom->survey->bar_color = ( isset($newarr['bar_color_survey']) && $newarr['bar_color_survey'] != '' ) ? esc_attr($newarr['bar_color_survey']) : $skinJson->skins->cyan->survey->bar_color;
    
    $skinJson->skins->custom->survey->background = ( isset($newarr['bg_color_survey']) && $newarr['bg_color_survey'] != '' ) ? esc_attr($newarr['bg_color_survey']) : $skinJson->skins->cyan->survey->background;
    
    $skinJson->skins->custom->survey->text = ( isset($newarr['text_color_survey']) && $newarr['text_color_survey'] != '' ) ? esc_attr($newarr['text_color_survey']) : $skinJson->skins->cyan->survey->text;

    /* Label Section */

    $skinJson->skins->custom->label->text = (isset($newarr['label_color']) && $newarr['label_color'] != '' ) ? esc_attr($newarr['label_color']) : $skinJson->skins->cyan->label->text;

    $skinJson->skins->custom->label->description = (isset($newarr['label_color']) && $newarr['label_color'] != '' ) ? esc_attr($newarr['label_color']) : $skinJson->skins->cyan->label->text;

    /* Input Section */

    $skinJson->skins->custom->input->main = (isset($newarr['arfmainbasecolor']) && $newarr['arfmainbasecolor'] != "" ) ? esc_attr($newarr['arfmainbasecolor']) : $skinJson->skins->cyan->input->main;   

    
    $skinJson->skins->custom->input->text = ( isset($newarr['text_color']) && $newarr['text_color'] != '' ) ? esc_attr($newarr['text_color']) : $skinJson->skins->cyan->input->text;

    $skinJson->skins->custom->input->background = (isset($newarr['bg_color']) && $newarr['bg_color'] != '') ? esc_attr($newarr['bg_color']) : $skinJson->skins->cyan->input->background;

    $skinJson->skins->custom->input->background_active = ( isset($newarr['arfbgactivecolorsetting']) && $newarr['arfbgactivecolorsetting'] != '' ) ? esc_attr($newarr['arfbgactivecolorsetting']) : $skinJson->skins->cyan->input->background_active;

    $skinJson->skins->custom->input->background_error = ( isset($newarr['arferrorbgcolorsetting']) && $newarr['arferrorbgcolorsetting'] != '' ) ? esc_attr($newarr['arferrorbgcolorsetting']) : $skinJson->skins->cyan->input->background_error;
    
    $skinJson->skins->custom->input->border = ( isset($newarr['border_color']) && $newarr['border_color'] != '' ) ? esc_attr($newarr['border_color']) : $skinJson->skins->cyan->input->border;
    
    $skinJson->skins->custom->input->border_active = (isset($newarr['arfborderactivecolorsetting']) && $newarr['arfborderactivecolorsetting'] != '' ) ? esc_attr($newarr['arfborderactivecolorsetting']) : $skinJson->skins->cyan->input->border_active;
    
    $skinJson->skins->custom->input->border_error = (isset($newarr['arferrorbordercolorsetting']) && $newarr['arferrorbordercolorsetting'] != '' ) ? esc_attr($newarr['arferrorbordercolorsetting']) : $skinJson->skins->cyan->input->border_error;


    $skinJson->skins->custom->input->prefix_suffix_background = ( isset($newarr['prefix_suffix_bg_color']) && $newarr['prefix_suffix_bg_color'] != '' ) ? esc_attr($newarr['prefix_suffix_bg_color']) : $skinJson->skins->cyan->input->prefix_suffix_background;

    $skinJson->skins->custom->input->prefix_suffix_icon_color = (isset($newarr['prefix_suffix_icon_color']) && $newarr['prefix_suffix_icon_color'] != '' ) ? esc_attr($newarr['prefix_suffix_icon_color']) : $skinJson->skins->cyan->input->prefix_suffix_icon_color;   

    $skinJson->skins->custom->input->checkbox_icon_color = ( isset($newarr['checked_checkbox_icon_color']) && $newarr['checked_checkbox_icon_color'] != '' ) ? esc_attr($newarr['checked_checkbox_icon_color']) : $skinJson->skins->cyan->input->checkbox_icon_color;
    
    $skinJson->skins->custom->input->radio_icon_color = ( isset($newarr['checked_radio_icon_color']) && $newarr['checked_radio_icon_color'] != '' ) ? esc_attr($newarr['checked_radio_icon_color']) : $skinJson->skins->cyan->input->radio_icon_color;

    $skinJson->skins->custom->input->like_button = ( isset($newarr['arflikebtncolor']) && $newarr['arflikebtncolor'] != "" ) ? esc_attr($newarr['arflikebtncolor']) : $skinJson->skins->cyan->input->like_button;

    $skinJson->skins->custom->input->dislike_button = ( isset($newarr['arfdislikebtncolor']) && $newarr['arfdislikebtncolor'] != "" ) ? esc_attr($newarr['arfdislikebtncolor']) : $skinJson->skins->cyan->input->dislike_button;

    $skinJson->skins->custom->input->rating_color = ( isset($newarr['arfstarratingcolor']) && $newarr['arfstarratingcolor'] != "" ) ? esc_attr($newarr['arfstarratingcolor']) : $skinJson->skins->cyan->input->rating_color;

    $skinJson->skins->custom->input->slider_selection_color = ( isset($newarr['arfsliderselectioncolor']) && $newarr['arfsliderselectioncolor'] != "" ) ? esc_attr($newarr['arfsliderselectioncolor']) : $skinJson->skins->cyan->input->slider_selection_color;

    $skinJson->skins->custom->input->slider_track_color = ( isset($newarr['arfslidertrackcolor']) && $newarr['arfslidertrackcolor'] != "" ) ? esc_attr($newarr['arfslidertrackcolor']) : $skinJson->skins->cyan->input->slider_track_color;

    /* Submit Section */
    
    $skinJson->skins->custom->submit->text = (isset($newarr['arfsubmittextcolorsetting']) && $newarr['arfsubmittextcolorsetting'] != '' ) ? esc_attr($newarr['arfsubmittextcolorsetting']) : $skinJson->skins->cyan->submit->text;
    
    $skinJson->skins->custom->submit->background = (isset($newarr['submit_bg_color']) && $newarr['submit_bg_color'] != '' ) ? esc_attr($newarr['submit_bg_color']) : $skinJson->skins->cyan->submit->background;
    
    $skinJson->skins->custom->submit->background_hover = (isset($newarr['arfsubmitbuttonbgcolorhoversetting']) && $newarr['arfsubmitbuttonbgcolorhoversetting'] != '' ) ? esc_attr($newarr['arfsubmitbuttonbgcolorhoversetting']) : $skinJson->skins->cyan->submit->background_hover;
    
    $skinJson->skins->custom->submit->border = isset($newarr['arfsubmitbordercolorsetting']) ? esc_attr($newarr['arfsubmitbordercolorsetting']) : $skinJson->skins->cyan->submit->border;
    
    $skinJson->skins->custom->submit->shadow = ( isset($newarr['arfsubmitshadowcolorsetting']) && $newarr['arfsubmitshadowcolorsetting'] != '' ) ? esc_attr($newarr['arfsubmitshadowcolorsetting']) : $skinJson->skins->cyan->submit->shadow;
    
    /* Success Message Section */

    $skinJson->skins->custom->success_msg->background = ( isset($newarr['arfsucessbgcolorsetting']) && $newarr['arfsucessbgcolorsetting'] != '' ) ? esc_attr($newarr['arfsucessbgcolorsetting']) : $skinJson->skins->cyan->success_msg->background;
    
    $skinJson->skins->custom->success_msg->border = (isset($newarr['arfsucessbordercolorsetting']) && $newarr['arfsucessbordercolorsetting'] != '') ? esc_attr($newarr['arfsucessbordercolorsetting']) : $skinJson->skins->cyan->success_msg->border;
    
    $skinJson->skins->custom->success_msg->text = ( isset($newarr['arfsucesstextcolorsetting']) && $newarr['arfsucesstextcolorsetting'] != '' ) ? esc_attr($newarr['arfsucesstextcolorsetting']) : $skinJson->skins->cyan->success_msg->text;

    /* Success Message Material Section */

    $skinJson->skins->custom->success_msg_material->background = ( isset($newarr['arfsucessbgcolorsetting']) && $newarr['arfsucessbgcolorsetting'] != '' ) ? esc_attr($newarr['arfsucessbgcolorsetting']) : $skinJson->skins->cyan->success_msg_material->background;
    
    $skinJson->skins->custom->success_msg_material->border = (isset($newarr['arfsucessbordercolorsetting']) && $newarr['arfsucessbordercolorsetting'] != '') ? esc_attr($newarr['arfsucessbordercolorsetting']) : $skinJson->skins->cyan->success_msg_material->border;
    
    $skinJson->skins->custom->success_msg_material->text = ( isset($newarr['arfsucesstextcolorsetting']) && $newarr['arfsucesstextcolorsetting'] != '' ) ? esc_attr($newarr['arfsucesstextcolorsetting']) : $skinJson->skins->cyan->success_msg_material->text;

    /* Error Message Section */

    $skinJson->skins->custom->error_msg->background = (isset($newarr['arfformerrorbgcolorsettings']) && $newarr['arfformerrorbgcolorsettings'] != '' ) ? esc_attr($newarr['arfformerrorbgcolorsettings']) : $skinJson->skins->custom->error_msg->background;

    $skinJson->skins->custom->error_msg->text = (isset($newarr['arfformerrortextcolorsettings']) && $newarr['arfformerrortextcolorsettings'] != '' ) ? esc_attr($newarr['arfformerrortextcolorsettings']) : $skinJson->skins->custom->error_msg->text;

    $skinJson->skins->custom->error_msg->border = (isset($newarr['arfformerrorbordercolorsettings']) && $newarr['arfformerrorbordercolorsettings'] != '' ) ? esc_attr($newarr['arfformerrorbordercolorsettings']) : $skinJson->skins->custom->error_msg->border;

    /* Error Message Material Section */

    $skinJson->skins->custom->error_msg_material->background = (isset($newarr['arfformerrorbgcolorsettings']) && $newarr['arfformerrorbgcolorsettings'] != '' ) ? esc_attr($newarr['arfformerrorbgcolorsettings']) : $skinJson->skins->custom->error_msg_material->background;

    $skinJson->skins->custom->error_msg_material->text = (isset($newarr['arfformerrortextcolorsettings']) && $newarr['arfformerrortextcolorsettings'] != '' ) ? esc_attr($newarr['arfformerrortextcolorsettings']) : $skinJson->skins->custom->error_msg_material->text;

    $skinJson->skins->custom->error_msg_material->border = (isset($newarr['arfformerrorbordercolorsettings']) && $newarr['arfformerrorbordercolorsettings'] != '' ) ? esc_attr($newarr['arfformerrorbordercolorsettings']) : $skinJson->skins->custom->error_msg_material->border;

    
    /* Validation Message */
    
    $skinJson->skins->custom->validation_msg->background = ( isset($newarr['arfvalidationbgcolorsetting']) && $newarr['arfvalidationbgcolorsetting'] != '' ) ? esc_attr($newarr['arfvalidationbgcolorsetting']) : (($active_skin != 'custom') ? $skinJson->skins->cyan->validation_msg->background : '');
    
    $skinJson->skins->custom->validation_msg->text = ( isset($newarr['arfvalidationtextcolorsetting']) && $newarr['arfvalidationtextcolorsetting'] != '' ) ? esc_attr($newarr['arfvalidationtextcolorsetting']) : (($active_skin != 'custom') ? $skinJson->skins->cyan->validation_msg->text : '');

    /* DateTime Picker Section */
    
    $skinJson->skins->custom->datepicker->background = ( isset($newarr['arfdatepickerbgcolorsetting']) && $newarr['arfdatepickerbgcolorsetting'] != '' ) ? esc_attr($newarr['arfdatepickerbgcolorsetting']) : $skinJson->skins->cyan->datepicker->background;
    
    $skinJson->skins->custom->datepicker->text = ( isset($newarr['arfdatepickertextcolorsetting']) && $newarr['arfdatepickertextcolorsetting'] != '' ) ? esc_attr($newarr['arfdatepickertextcolorsetting']) : $skinJson->skins->cyan->datepicker->text;

    /* Upload Button Section */
   
    $skinJson->skins->custom->uploadbutton->text = ( isset($newarr['arfuploadbtntxtcolorsetting']) && $newarr['arfuploadbtntxtcolorsetting'] != '' ) ? esc_attr($newarr['arfuploadbtntxtcolorsetting']) : $skinJson->skins->cyan->uploadbutton->text;

    $skinJson->skins->custom->uploadbutton->background = ( isset($newarr['arfuploadbtnbgcolorsetting']) && $newarr['arfuploadbtnbgcolorsetting'] != '' ) ? esc_attr($newarr['arfuploadbtnbgcolorsetting']) : $skinJson->skins->cyan->uploadbutton->background;

$browser_info = $arrecordcontroller->getBrowser($_SERVER['HTTP_USER_AGENT']);

wp_register_script('filedrag-js', ARFURL . '/js/filedrag/filedrag.js', array(), $arfversion);
$armainhelper->load_scripts(array('filedrag-js'));
global $arformcontroller,$get_googlefonts_data;
$get_googlefonts_data = $arformcontroller->get_arf_google_fonts();
$google_font_array = array_chunk($get_googlefonts_data, 150);

foreach ($google_font_array as $key => $font_values) {
    $google_fonts_string = implode('|', $font_values);
    $google_font_url_one = '';
    if (is_ssl()) {
        $google_font_url_one = "https://fonts.googleapis.com/css?family=" . $google_fonts_string;
    } else {
        $google_font_url_one = "http://fonts.googleapis.com/css?family=" . $google_fonts_string;
    }

    echo '<link rel = "stylesheet" type = "text/css" href = "' . $google_font_url_one . '" />';
}
function arf_font_li_listing() {
    global $get_googlefonts_data;
    ?>
    <ol class="arp_selectbox_group_label"><?php echo addslashes(esc_html__('Default Fonts', 'ARForms')); ?></ol>
    <li class="arf_selectbox_option" data-value="Arial" data-label="Arial">Arial</li>
    <li class="arf_selectbox_option" data-value="Helvetica" data-label="Helvetica">Helvetica</li>
    <li class="arf_selectbox_option" data-value="sans-serif" data-label="sans-serif">sans-serif</li>
    <li class="arf_selectbox_option" data-value="Lucida Grande" data-label="Lucida Grande">Lucida Grande</li>
    <li class="arf_selectbox_option" data-value="Lucida Sans Unicode" data-label="Lucida Sans Unicode">Lucida Sans Unicode</li>
    <li class="arf_selectbox_option" data-value="Tahoma" data-label="Tahoma">Tahoma</li>
    <li class="arf_selectbox_option" data-value="Times New Roman" data-label="Times New Roman">Times New Roman</li>
    <li class="arf_selectbox_option" data-value="Courier New" data-label="Courier New">Courier New</li>
    <li class="arf_selectbox_option" data-value="Verdana" data-label="Verdana">Verdana</li>
    <li class="arf_selectbox_option" data-value="Geneva" data-label="Geneva">Geneva</li>
    <li class="arf_selectbox_option" data-value="Courier" data-label="Courier">Courier</li>
    <li class="arf_selectbox_option" data-value="Monospace" data-label="Monospace">Monospace</li>
    <li class="arf_selectbox_option" data-value="Times" data-label="Times">Times</li>
    <ol class="arp_selectbox_group_label"><?php echo addslashes(esc_html__('Google Fonts', 'ARForms')); ?></ol>
    <?php
    if (count($get_googlefonts_data) > 0) {
        foreach ($get_googlefonts_data as $goglefontsfamily) {
            echo "<li class='arf_selectbox_option' data-value='" . $goglefontsfamily . "' data-label='" . $goglefontsfamily . "'>" . $goglefontsfamily . "</li>";
        }
    }
}

$display = apply_filters('arfdisplayfieldoptions', array('label_position' => true));
$arfaction = $_REQUEST['arfaction'];

if ($arfaction == 'duplicate') {
    if ($id < 100) {
        $template_id = 1;
    } else {
        $template_id = 0;
    }
}

$arf_template_id = isset($template_id) ? $template_id : 0;

$res = maybe_unserialize(get_option('arf_ar_type'));
if (empty($autoresponder_all_data_query)) {
    $autoresponder_all_data_query = $wpdb->get_results("SELECT * FROM " . $MdlDb->autoresponder, 'ARRAY_A');
}
$res1 = $autoresponder_all_data_query[2];
$res2 = $autoresponder_all_data_query[0];
$res3 = $autoresponder_all_data_query[3];
$res4 = $autoresponder_all_data_query[4];
$res5 = $autoresponder_all_data_query[5];
$res6 = $autoresponder_all_data_query[7];
$res7 = $autoresponder_all_data_query[8];
$res14 = $autoresponder_all_data_query[9];

$ar_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->ar . " WHERE frm_id = %d ORDER BY id DESC", $id), 'ARRAY_A');

$aweber_arr = maybe_unserialize(isset($ar_data[0]['aweber']) ? $ar_data[0]['aweber'] : '' );

$mailchimp_arr = maybe_unserialize(isset($ar_data[0]['mailchimp']) ? $ar_data[0]['mailchimp'] : '' );
$madmimi_arr = maybe_unserialize(isset($ar_data[0]['madmimi']) ? $ar_data[0]['madmimi'] : '' );
$getresponse_arr = maybe_unserialize(isset($ar_data[0]['getresponse']) ? $ar_data[0]['getresponse'] : '' );
$gvo_arr = maybe_unserialize(isset($ar_data[0]['gvo']) ? $ar_data[0]['gvo'] : '' );
$ebizac_arr = maybe_unserialize(isset($ar_data[0]['ebizac']) ? $ar_data[0]['ebizac'] : '' );
$icontact_arr = maybe_unserialize(isset($ar_data[0]['icontact']) ? $ar_data[0]['icontact'] : '' );
$constant_contact_arr = maybe_unserialize(isset($ar_data[0]['constant_contact']) ? $ar_data[0]['constant_contact'] : '' );
$ar_data[0]['enable_ar'] = isset($ar_data[0]['enable_ar']) ? $ar_data[0]['enable_ar'] : '';
$global_enable_ar = maybe_unserialize(isset($ar_data[0]['enable_ar']) ? $ar_data[0]['enable_ar'] : '' );

$current_active_ar = '';

if (isset($mailchimp_arr['enable']) && $mailchimp_arr['enable'] == 1) {
    $current_active_ar = 'mailchimp';
} else if (isset($aweber_arr['enable']) && $aweber_arr['enable'] == 1) {
    $current_active_ar = 'aweber';
} else if (isset($icontact_arr['enable']) && $icontact_arr['enable'] == 1) {
    $current_active_ar = 'icontact';
} else if (isset($constant_contact_arr['enable']) && $constant_contact_arr['enable'] == 1) {
    $current_active_ar = 'constant_contact';
} else if (isset($getresponse_arr['enable']) && $getresponse_arr['enable'] == 1) {
    $current_active_ar = 'getresponse';
} else if (isset($gvo_arr['enable']) && $gvo_arr['enable'] == 1) {
    $current_active_ar = 'gvo';
} else if (isset($ebizac_arr['enable']) && $ebizac_arr['enable'] == 1) {
    $current_active_ar = 'ebizac';
} else if (isset($madmimi_arr['enable']) && $madmimi_arr['enable'] == 1) {
    $current_active_ar = 'madmimi';
} else {
    $current_active_ar = 'mailchimp';
}

$current_active_ar = apply_filters('arf_current_autoresponse_set_outside', $current_active_ar, $ar_data);

$setvaltolic = 0;
global $arformsplugin;
$setvaltolic = $arformcontroller->$arformsplugin();

$wp_upload_dir = wp_upload_dir();
$upload_main_url = $wp_upload_dir['baseurl'] . '/arforms/maincss';
if (is_ssl()) {
    $fid = str_replace("http://", "https://", $upload_main_url . '/maincss_' . $id . '.css');
} else {
    $fid = $upload_main_url . '/maincss_' . $id . '.css';
}

if ($newarr['arfinputstyle'] == 'material') {
    if( $id > 0 ){
        if (is_ssl()) {
            $fid_m = str_replace("http://", "https://", $upload_main_url . '/maincss_materialize_' . $id . '.css');
        } else {
            $fid_m = $upload_main_url . '/maincss_materialize_' . $id . '.css';
        }
        wp_enqueue_style('arf-main-style-editor-materialize', $fid_m, array(), $arfversion);
    }
    wp_enqueue_style('arf_materialize_style', ARFURL . '/materialize/materialize.css', array(), $arfversion);
    //wp_enqueue_script('arf_materialize_script', ARFURL . '/materialize/materialize.js', array(), $arfversion);
}

if ($id > 0 && ($arfaction != 'duplicate' && $arfaction != 'new')) {
    $define_template = isset($values_nw['define_template']) ? $values_nw['define_template'] : 0;
    if( $newarr['arfinputstyle'] == 'standard'  || $newarr['arfinputstyle'] == 'rounded' ){
        wp_enqueue_style('arf-main-style-editor', $fid, array(), $arfversion);
    }
} else if ($id == 0 || $arfaction == 'duplicate') {

    $arf_form_style = "<style type='text/css' class='added_new_style_css'>";
    if ($arf_template_id == 1) {
        $define_template = $id;
    } else {
        $define_template = $id;
    }

    $id = rand();

    $form_id = $id;
    $saving = true;
    $use_saved = true;
    $new_values = array();
    
    foreach ($newarr as $key => $value) {
        $new_values[$key] = $value;
    }

    $arfssl = false;
    if( is_ssl() ){
        $arfssl = true;
    }

    if ($new_values['arfinputstyle'] == 'standard' || $new_values['arfinputstyle'] == 'rounded') {
        if ($arfaction == 'new') {
            $filename = FORMPATH . '/core/css_create_main.php';
            ob_start();
            include $filename;
            $css = ob_get_contents();
            $css = str_replace('##', '#', $css);
            $arf_form_style .= $css;
            ob_end_clean();
        } else if ($arfaction == 'duplicate') {

            if( $record->is_template ){
                $form_css = maybe_unserialize($record->form_css);
                $input_style = isset($form_css['arfinputstyle']) ? $form_css['arfinputstyle'] : 'material';
                if( $input_style == 'material') {
                    if($new_values['arfinputstyle'] == 'rounded'){
                        $new_values['border_radius'] = 50;
                    } else {
                        $new_values['border_radius'] = 4;
                    }
                    $new_values['arffieldinnermarginssetting_1'] = 7;
                    $new_values['arffieldinnermarginssetting_2'] = 10;
                    $new_values['arfcheckradiostyle'] = 'default';
                    $new_values['arfsubmitborderwidthsetting'] = '0';
                    $new_values['arfsubmitbuttonstyle'] = 'flat';
                    $new_values['arfmainfield_opacity'] = 0;
                    $new_values['arffieldinnermarginssetting'] = '7px 10px 7px 10px';
                }
            }

            $filename = FORMPATH . '/core/css_create_main.php';

            ob_start();
            include $filename;
            $css = ob_get_contents();
            $css = str_replace('##', '#', $css);
            $arf_form_style .= $css;
            ob_end_clean();
        }
    } else if ($new_values['arfinputstyle'] == 'material') {

        if( $arfaction == 'duplicate' && isset($record) && isset($record->is_template) && $record->is_template ){
            $form_css = maybe_unserialize($record->form_css);
            $input_style = isset($form_css['arfinputstyle']) ? $form_css['arfinputstyle'] : 'material';
            if( $input_style != 'material') {
                $new_values['arffieldinnermarginssetting_1'] = 0;
                $new_values['arffieldinnermarginssetting_2'] = 0;
                $new_values['border_radius'] = 0;
                $new_values['arfcheckradiostyle'] = 'material';
                $new_values['arfsubmitborderwidthsetting'] = '2';
                $new_values['arfsubmitbuttonstyle'] = 'border';
                $new_values['arfmainfield_opacity'] = 1;
                $new_values['arffieldinnermarginssetting'] = '0px 0px 0px 0px';
            }
        }

        $filename = FORMPATH . '/core/css_create_materialize.php';

        ob_start();

        include $filename;

        $css = ob_get_contents();

        $css = str_replace('##', '#', $css);

        $arf_form_style .= $css;

        ob_end_clean();

        wp_enqueue_style('arf_editor_material_css', ARFURL . '/materialize/materialize.css', array(), $arfversion);
        //wp_enqueue_script('arf_editor_material_js', ARFURL . '/materialize/materialize.js', array(), $arfversion);
    }
    $arf_form_style .= "</style>";
    echo $arf_form_style;
}

$prepg_temp = addslashes(esc_html__("Previous", "ARForms"));
$next_temp = addslashes(esc_html__("Next", "ARForms"));
$default_selected_tmp = addslashes(esc_html__("wizard", "ARForms"));

if (isset($values['fields'])) {
    foreach ($values['fields'] as $field) {

        if ($field["type"] == "break") {
            $prepg_temp = esc_attr($field["pre_page_title"]);
            $next_temp = esc_attr($field["next_page_title"]);
            $default_selected_tmp = esc_attr($field['page_break_type']);
            break;
        }
    }
}
$form_options = isset($record->options) ? maybe_unserialize($record->options) : array();

$arf_field_order = (isset($form_options['arf_field_order']) && $form_options['arf_field_order'] != '') ? $form_options['arf_field_order'] : '';
$arf_field_resize_width = (isset($form_options['arf_field_resize_width']) && $form_options['arf_field_resize_width'] != '') ? $form_options['arf_field_resize_width'] : '';
if( $arf_field_order != '' ){
    $arf_field_order = json_decode( $arf_field_order, true );
    $arf_field_order = json_encode(array_filter($arf_field_order));
}

if( $arf_field_resize_width != '' ){
    $arf_field_resize_width = json_decode( $arf_field_resize_width, true );
    $arf_field_resize_width = json_encode(array_filter($arf_field_resize_width));
}

$wp_upload_dir = wp_upload_dir();
if (is_ssl()) {
    $upload_css_url = str_replace("http://", "https://", $wp_upload_dir['baseurl'] . '/arforms/');
} else {
    $upload_css_url = $wp_upload_dir['baseurl'] . '/arforms/';
}
$form_opts['arf_form_other_css'] = (isset($form_opts['arf_form_other_css']) && $form_opts['arf_form_other_css']!='') ? $arformcontroller->br2nl($form_opts['arf_form_other_css']) : '';

?>
<script type="text/javascript">
    function arfSkinJson() {
        var skinJson;
        skinJson = <?php echo json_encode($skinJson); ?>;
        return skinJson;
    }
</script>
<style type="text/css" id="arf_form_other_css_<?php echo $id;?>">
    <?php 
    if( isset($form_opts['arf_form_other_css']) ){
        if($arfaction == 'new' || $arfaction == 'duplicate' ){
            echo $temp_arf_form_other_css = preg_replace('/(-|_)('.$define_template.')/', '${1}'.$id, $form_opts['arf_form_other_css'], -1, $count);            
            $form_opts['arf_form_other_css'] = $temp_arf_form_other_css;
        } else {
            echo $form_opts['arf_form_other_css'];
        }
    }
?>
</style>
<?php do_action('arf_display_additional_css_in_editor'); ?>
<inpu typte="hidden" id="arf_db_json_object" value='<?php echo json_encode($skinJson->skins->custom); ?>' />
<style type="text/css" id='arf_form_<?php echo $id; ?>'>
<?php
$custom_css_array_form = array(
    'arf_form_outer_wrapper' => '.arf_form_outer_wrapper|.arfmodal',
    'arf_form_inner_wrapper' => '.arf_fieldset|.arfmodal',
    'arf_form_title' => '.formtitle_style',
    'arf_form_description' => 'div.formdescription_style',
    'arf_form_element_wrapper' => '.arfformfield',
    'arf_form_element_label' => 'label.arf_main_label',
    'arf_form_elements' => '.controls',
    'arf_submit_outer_wrapper' => 'div.arfsubmitbutton',
    'arf_form_submit_button' => '.arfsubmitbutton button.arf_submit_btn',
    'arf_form_next_button' => 'div.arfsubmitbutton .next_btn',
    'arf_form_previous_button' => 'div.arfsubmitbutton .previous_btn',
    'arf_form_success_message' => '#arf_message_success',
    'arf_form_error_message' => '.control-group.arf_error .help-block|.control-group.arf_warning .help-block|.control-group.arf_warning .help-inline|.control-group.arf_warning .control-label|.control-group.arf_error .popover|.control-group.arf_warning .popover',
    'arf_form_page_break' => '.page_break_nav',
);

foreach ($custom_css_array_form as $custom_css_block_form => $custom_css_classes_form) {

    if (isset($form->options[$custom_css_block_form]) and $form->options[$custom_css_block_form] != '') {

        $form->options[$custom_css_block_form] = $arformcontroller->br2nl($form->options[$custom_css_block_form]);

        if ($custom_css_block_form == 'arf_form_outer_wrapper') {
            $arf_form_outer_wrapper_array = explode('|', $custom_css_classes_form);

            foreach ($arf_form_outer_wrapper_array as $arf_form_outer_wrapper1) {
                if ($arf_form_outer_wrapper1 == '.arf_form_outer_wrapper')
                    echo '.ar_main_div_' . $form->id . '.arf_form_outer_wrapper { ' . $form->options[$custom_css_block_form] . ' } ';
                if ($arf_form_outer_wrapper1 == '.arfmodal')
                    echo '#popup-form-' . $form->id . '.arfmodal{ ' . $form->options[$custom_css_block_form] . ' } ';
            }
        }
        else if ($custom_css_block_form == 'arf_form_inner_wrapper') {
            $arf_form_inner_wrapper_array = explode('|', $custom_css_classes_form);
            foreach ($arf_form_inner_wrapper_array as $arf_form_inner_wrapper1) {
                if ($arf_form_inner_wrapper1 == '.arf_fieldset')
                    echo '.ar_main_div_' . $form->id . ' ' . $arf_form_inner_wrapper1 . ' { ' . $form->options[$custom_css_block_form] . ' } ';
                if ($arf_form_inner_wrapper1 == '.arfmodal')
                    echo '.arfmodal .arfmodal-body .ar_main_div_' . $form->id . ' .arf_fieldset { ' . $form->options[$custom_css_block_form] . ' } ';
            }
        }
        else if ($custom_css_block_form == 'arf_form_error_message') {
            $arf_form_error_message_array = explode('|', $custom_css_classes_form);

            foreach ($arf_form_error_message_array as $arf_form_error_message1) {
                echo '.ar_main_div_' . $form->id . ' ' . $arf_form_error_message1 . ' { ' . $form->options[$custom_css_block_form] . ' } ';
            }
        } else {
            echo '.ar_main_div_' . $form->id . ' ' . $custom_css_classes_form . ' { ' . $form->options[$custom_css_block_form] . ' } ';
        }
    }
}
$arfdefine_date_formate_array = $arformcontroller->arfreturndateformate();
?>
</style>

<div class="arf_editor_wrapper">
    <div id="arfsaveformloader"><?php echo ARF_LOADER_ICON; ?></div>
    <input type="hidden" id="arf_control_labels" value="" data-field-id="" />
    <input type="hidden" id="arf_reset_styling" value="false" />
    <input type="hidden" id="arf_copying_fields" value="false" />
    <input type="hidden" id="arf_single_column_field_ids" value="" />
    <div id="arf_hidden_fields_html" style="display:none !important;height:0px !important;width:0px !important;visibility: hidden !important;"></div>
    <input type="hidden" name="arfwpversion" id="arfwpversion" value="<?php echo $GLOBALS['wp_version']; ?>" />
    <input type="hidden" name="arfchange_field" id="arfchange_field" />
    <input type="hidden" name="arfdateformate" id="arfdateformate" data-wp-formate = "<?php echo $arfdefine_date_formate_array['arfwp_dateformate'];?>"  data-js-formate = "<?php echo $arfdefine_date_formate_array['arfjs_dateformate'];?>" />
    <input type="hidden" name="arfgettemplate_style" id="arfgettemplate_style" value="<?php echo (isset($_GET['templete_style']) && $_GET['templete_style'] !='') ? $_GET['templete_style'] : '';?>" />

    <form action="" method="POST" id="arf_current_form_export" name="arf_current_form_export">
        <input type="hidden" name="s_action" value="opt_export_form">
        <input type="hidden" name="opt_export" value="">
        <input type="hidden" name="export_button" value="export_button">
        <input type="hidden" name="is_single_form" value="1">
        <input type="hidden" name="frm_add_form_id_name" id="frm_add_form_id_name" value="<?php echo $form_id; ?>">
    </form>

    <form name="arf_form" id="frm_main_form" method="post" onSubmit='return arfmainformedit(0);'>
        <input type="hidden" name="arfmainformurl" data-id="arfmainformurl" value="<?php echo ARFURL; ?>" />   
        <input type="hidden" name="arfmainformversion" id="arfmainformversion" value="<?php echo $arfversion; ?>" />
        <input type="hidden" name="arfuploadurl" id="arfuploadurl" value="<?php echo $upload_css_url; ?>"/>
        <input type="hidden" name="arfaction" id="arfaction" value="<?php echo $_GET['arfaction']; ?>" />
        <input type="hidden" name="arfajaxurl" id="arfajaxurl" class="arf_ajax_url" value="<?php echo $arfajaxurl; ?>" />
        <input type="hidden" name="arffiledragurl" data-id="arffiledragurl" value="<?php echo ARF_FILEDRAG_SCRIPT_URL; ?>" />

        <input type="hidden" name="prev_arfaction" value="<?php $_GET["arfaction"]; ?>" />

        <input type="hidden" name="frm_autoresponder_no" id="frm_autoresponder_no" value="" />


        <input type="hidden" name="id" id="id" value="<?php echo $id; ?>" />
        <input type="hidden" name="define_template" id="define_template" value="<?php echo isset($define_template) ? $define_template : 0; ?>" />
        <input type="hidden" id="arf_isformchange" name="arf_isformchange" data-value="1" value="1" />

        <input type="hidden" id="page_break_first_pre_btn_txt" value="<?php echo esc_attr($prepg_temp); ?>" />

        <input type="hidden" id="page_break_first_next_btn_txt" value="<?php echo esc_attr($next_temp); ?>" />

        <input type="hidden" id="page_break_first_select" value="<?php echo esc_attr($default_selected_tmp); ?>" />
        <input type ="hidden" id="changed_style_attr" value="" />

        <input type ="hidden" id="default_style_attr" value='<?php echo json_encode($newarr);?>' />

        <input type="hidden" id="arf_field_order" name="arf_field_order" value='<?php echo $arf_field_order; ?>' data-db-field-order='<?php echo ($_GET['arfaction']== 'edit') ? $arf_field_order : ''; ?>' />
        <input type="hidden" id="arf_field_resize_width" name="arf_field_resize_width" value='<?php echo $arf_field_resize_width; ?>' data-db-field-resize='<?php echo ($_GET['arfaction']== 'edit') ? $arf_field_resize_width : ''; ?>' />
        <input type="hidden" id="arf_input_radius" name="arf_input_radius" value='<?php echo $newarr['border_radius']; ?>' />
        <?php $browser_info = $arrecordcontroller->getBrowser($_SERVER['HTTP_USER_AGENT']); ?>
        <input type="hidden" data-id="arf_browser_name" value="<?php echo $browser_info['name']; ?>" />
        <div class="arf_editor_header_belt">
            <div class="arf_editor_header_inner_belt">
                <div class="arf_editor_top_menu_wrapper">
                    <ul class="arf_editor_top_menu">
                        <li class="arf_editor_top_menu_item" id="mail_notification">
                            <span class="arf_editor_top_menu_item_icon">
                                <svg viewBox="0 -4 32 32">
                                <g id="email"><path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M27.321,22.868H3.661c-1.199,0-2.172-0.973-2.172-2.172V3.053c0-1.2,0.973-2.203,2.172-2.203h23.66c1.199,0,2.171,1.003,2.171,2.203v17.643C29.492,21.895,28.52,22.868,27.321,22.868zM27.501,20.894V3.69l-12.28,9.268v0.008l-0.005-0.004l-0.005,0.004v-0.008L3.484,3.676v17.218H27.501z M24.994,2.844H5.95l9.267,7.377L24.994,2.844z"/></g>
                                </svg>
                            </span>
                            <label class="arf_editor_top_menu_label">
                                <?php echo addslashes(esc_html__('Email Notifications', 'ARForms')); ?>
                            </label>
                        </li>
                        <li class="arf_editor_top_menu_item" id="conditional_law">
                            <span class="arf_editor_top_menu_item_icon">
                                <svg viewBox="0 -5 32 32">
                                <g id="conditional_law"><path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M1.489,22.819V20.85H23.5v1.969H1.489z M10.213,13.263l2.246,2.246l5.246-5.246l1.392,1.392l-5.246,5.246l0.013,0.013l-1.392,1.392l-0.013-0.013l-0.013,0.013l-1.392-1.392l0.013-0.013l-2.246-2.246L10.213,13.263z M1.489,5.85H23.5v1.969H1.489V5.85z M1.489,0.85H23.5v1.969H1.489V0.85z"/></g>
                                </svg>
                            </span>
                            <label class="arf_editor_top_menu_label">
                                <?php echo addslashes(esc_html__('Conditional Rule', 'ARForms')); ?>
                            </label>
                        </li>
                        <li class="arf_editor_top_menu_item" id="submit_action">
                            <span class="arf_editor_top_menu_item_icon">
                                <svg viewBox="0 -5 32 32">
                                <g id="submit_action"><path fill="none" stroke="#ffffff" fill-rule="evenodd" clip-rule="evenodd" stroke-width="1.7" d="M23.362,0.85v10.293c0,3.138-2.544,5.683-5.683,5.683h-7.33v3.283l-8.86-6.007l8.86-6.319v4.05h6.686c0.738,0,1.336-0.598,1.336-1.336V0.85H23.362z"/></g>
                                </svg>
                            </span>
                            <label class="arf_editor_top_menu_label">
                                <?php echo addslashes(esc_html__('Submit Action', 'ARForms')); ?>
                            </label>
                        </li>
                        <li class="arf_editor_top_menu_item" id="email_marketers">
                            <span class="arf_editor_top_menu_item_icon">
                                <svg viewBox="0 -3 32 32">
                                <g id="email_marketers"><path  stroke="#ffffff" fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" stroke-width="0.5" d="M23.287,23.217c-0.409,0.46-0.84,0.866-0.932,0.934c-0.092,0.068-0.568,0.417-1.088,0.745c-0.387,0.244-0.789,0.468-1.204,0.669c-5.41,2.64-11.02,1.559-12.981-4.493c-0.291-0.896-0.125-1.162-0.658-1.273c-0.998-0.209-2.2-0.696-2.647-1.711c-0.528-1.2-0.571-2.338-0.003-3.193c0.341-0.513,0.323-0.929-0.217-1.223c-3.604-1.958-1.974-5.485,0.918-8.376c2.536-2.537,6.438-5.428,9.759-3.627c0.54,0.293,1.352,0.39,1.911,0.135c0.513-0.235,1.032-0.436,1.555-0.597c1.414-0.435,4.297-0.813,4.985,1.057c0.509,1.382,0.654,3.366-0.127,4.745c-0.305,0.536-0.203,1.047,0.103,1.582c0.589,1.031,0.529,2.774,0.514,3.681c-0.019,1.043,0.299,1.927,0.67,2.809c0.239,0.568,0.521,1.013,0.623,1.038c0.069,0.017,0.119,0.054,0.134,0.119c0.048,0.209,0.081,0.413,0.101,0.613c0.035,0.341,0.105,0.926,0.164,1.311c0.034,0.226,0.056,0.459,0.061,0.704C24.961,20.623,24.314,22.061,23.287,23.217z M20.125,23.994c0.614,0.016,1.48-0.411,1.869-0.889c0.415-0.511,0.764-1.068,1.024-1.661c0.249-0.564-0.004-0.708-0.534-0.397c-2.286,1.34-5.727,1.179-7.432-0.95c-0.385-0.481-0.52-0.737-0.421-0.483c0.099,0.254,0.036,0.629-0.172,0.854c-0.209,0.224-0.23,0.61-0.025,0.843s0.537,0.25,0.72,0.055c0.184-0.194,0.351-0.326,0.374-0.297c0.022,0.029-0.106,0.204-0.29,0.39c-0.185,0.187-0.205,0.459-0.038,0.6c0.167,0.141,0.444,0.108,0.614-0.062c0.168-0.172,0.486-0.141,0.723,0.049c0.238,0.191,0.322,0.453,0.176,0.605c-0.147,0.152,0.136,0.512,0.666,0.732c0.529,0.22,1.025,0.291,1.082,0.233s0.167-0.068,0.246-0.024c0.081,0.044,0.116,0.11,0.077,0.149c-0.038,0.04,0.417,0.193,1.03,0.237C19.917,23.986,20.022,23.991,20.125,23.994zM22.358,20.167c-0.141,0.143-0.28,0.285-0.421,0.426l-0.128,0.126c-0.071,0.07,0.188-0.045,0.493-0.354C22.61,20.056,22.59,19.931,22.358,20.167z M4.795,16.74c0.122,0.274,0.447,0.299,0.684,0.079c0.236-0.221,0.504-0.19,0.634,0.05c0.131,0.24,0.098,0.572-0.105,0.76c-0.204,0.188-0.032,0.718,0.482,1.056c0.459,0.302,0.945,0.495,1.389,0.515c0.079,0.003,0.241,0.035,0.264,0.136c0.045,0.203,0.097,0.41,0.153,0.621c0.093,0.34,0.354,0.451,0.569,0.251c0.216-0.199,0.446-0.339,0.516-0.313c0.068,0.026,0.149,0.136,0.185,0.247c0.034,0.111-0.144,0.408-0.397,0.664c-0.253,0.255-0.292,0.935-0.03,1.493c0.027,0.059,0.056,0.117,0.084,0.174c0.271,0.553,0.725,0.794,0.944,0.574c0.221-0.22,0.544-0.116,0.752,0.215c0.209,0.332,0.251,0.745,0.064,0.946c-0.188,0.201-0.233,0.475-0.096,0.604c0.083,0.079,0.168,0.154,0.257,0.224c0.052,0.041,0.105,0.081,0.159,0.118c0.09,0.062,0.296-0.027,0.459-0.199s0.299-0.306,0.306-0.299c0.007,0.006-0.122,0.147-0.288,0.315c-0.165,0.168-0.152,0.408,0.038,0.524c0.189,0.117,0.468,0.078,0.614-0.07c0.146-0.147,0.485-0.114,0.777,0.044c0.291,0.157,0.45,0.352,0.34,0.467c-0.111,0.116,0.28,0.348,0.892,0.41c1.708,0.172,3.512-0.274,5.061-1.156c0.534-0.305,0.435-0.575-0.179-0.621c-4.634-0.335-10.049-4.076-6.684-8.961c0.198-0.287-1.173-1.688-1.188-2.397c-0.038-1.685,0.779-2.368,2.145-3.229c0.763-0.481,1.711-0.692,2.656-0.677c0.613,0.011,1.134,0.093,1.171,0.056c0.038-0.036,0.095-0.077,0.126-0.092c0.023-0.01,0.021,0.003,0.005,0.029c-0.016,0.023,0.005,0.007,0.052-0.031c0.037-0.028,0.071-0.051,0.092-0.061c0.037-0.015,0.1-0.025,0.14-0.024c0.04,0.002,0.002,0.072-0.085,0.154c-0.086,0.083-0.107,0.162-0.047,0.175c0.061,0.014,0.214-0.074,0.351-0.192c0.137-0.12-0.172-0.489-0.76-0.67c-0.111-0.035-0.225-0.064-0.338-0.09c-0.6-0.133-1.115-0.09-1.13-0.078c-0.014,0.013-0.509,0.147-1.072,0.394c-0.395,0.173-0.784,0.379-1.166,0.612c-0.524,0.321-0.615,0.336-0.234-0.018c0.38-0.354,0.217-0.474-0.328-0.189c-2.063,1.079-3.949,3.012-5.192,4.528c-0.098,0.12-0.251,0.198-0.421,0.239c-0.263,0.064-0.495,0.026-0.505,0.036c-0.011,0.01-0.342,0.127-0.646,0.396c-0.305,0.27-0.69,0.857-1.028,1.174C4.896,15.969,4.673,16.466,4.795,16.74z M13.062,2.367c-0.99-0.478-2.052-0.443-3.087-0.101C9.392,2.458,8.606,3.06,8.177,3.502C7.292,4.417,6.353,5.387,5.34,6.434C4.709,7.081,4.212,7.589,3.828,7.983c-0.43,0.44-0.777,0.788-0.772,0.779c0.004-0.009,0.352-0.376,0.779-0.82c1.123-1.165,2.877-2.98,4.211-4.366c0.427-0.444,0.737-0.784,0.691-0.761C8.693,2.838,8.302,3.211,7.869,3.648C6.887,4.636,5.564,5.986,4.004,7.587c-0.429,0.441-0.64,0.513-0.437,0.18c0.204-0.333,0.054-0.217-0.28,0.301C2.964,8.567,2.731,9.077,2.669,9.577c-0.172,1.4,0.531,2.441,1.545,3.169c0.499,0.359,1.162,0.104,1.445-0.444c1.648-3.197,4.321-6.447,7.404-8.688C13.562,3.254,13.617,2.634,13.062,2.367z M18.808,1.454c-0.61,0.061-1.088,0.308-1.111,0.332c-0.022,0.023-0.054,0.037-0.069,0.032c-0.015-0.006-0.082,0.015-0.15,0.047c-0.039,0.019-0.079,0.039-0.12,0.061c-0.28,0.148-0.556,0.303-0.829,0.464c-0.451,0.266-0.877,0.668-1.068,0.775c-0.192,0.106-0.638,0.338-0.969,0.573c-0.2,0.142-0.398,0.287-0.59,0.44c-0.455,0.361-0.897,0.735-1.33,1.116c-1.043,1.074-2.101,2.163-3.173,3.271C8.11,10.15,7.034,11.902,6.26,13.861c-0.003,0.01-0.01,0.018-0.017,0.026C6.234,13.9,6.183,14,6.086,14.062c-0.048,0.031-0.108,0.063-0.185,0.094c-0.021,0.009-0.041,0.017-0.063,0.026c-0.012,0.005-0.02,0.008-0.031,0.013c-0.526,0.196-0.864,0.478-1.054,0.809c-0.304,0.536,0.189,0.728,0.624,0.291c0.177-0.178,0.349-0.351,0.516-0.52c0.435-0.438,0.596-0.87,0.594-1.065c-0.002-0.09,0.04-0.196,0.14-0.316c1.955-2.384,5.12-5.258,8.391-5.892c0.262-0.051,0.546-0.09,0.808-0.122c0.448-0.055,0.915-0.111,1.044-0.113c0.149-0.002,0.23,0.022,0.194,0.055c-0.052,0.048,0.407,0.131,0.994,0.315c0.15,0.048,0.301,0.102,0.449,0.162c0.57,0.232,1.245,0.367,1.585,0.232c0.341-0.134,1.063-0.489,1.348-1.037C22.479,4.995,21.533,1.183,18.808,1.454z M22.605,15.494c-0.452-0.864-0.868-1.535-0.877-2.836c-0.006-1.052,0.049-2.333-0.383-3.319c-0.349-0.798-0.817-0.735-1.315-0.426c-0.522,0.325-0.952,0.779-1.067,0.877c-0.114,0.099-0.315,0.316-0.519,0.43c-0.171,0.096-0.359,0.171-0.383,0.179c-0.087,0.027-0.176,0.045-0.267,0.056c-0.08,0.009-0.205,0.028-0.322,0.021c-0.178-0.01-0.719-0.381-1.319-0.51c-1.802-0.385-2.773,0.865-2.898,2.311c-0.053,0.615,0.316,0.868,0.621,0.568c0.307-0.3,0.551-0.494,0.548-0.433c-0.003,0.062-0.241,0.338-0.535,0.618c-0.293,0.28-0.447,0.892-0.221,1.313c0.137,0.254,0.306,0.49,0.509,0.695c0.079,0.08,0.044,0.151-0.017,0.23c-0.031,0.039-0.06,0.079-0.086,0.118c-0.046,0.066,0.154-0.096,0.449-0.365c0.295-0.268,0.56-0.451,0.595-0.411c0.035,0.041-0.285,0.43-0.714,0.873c-0.057,0.057-0.113,0.114-0.17,0.173c-0.43,0.441-0.993,1.259-1.083,1.87c-0.057,0.385-0.056,0.765-0.005,1.137c0.084,0.611,0.494,0.871,0.741,0.621c0.247-0.251,0.442-0.471,0.433-0.492c-0.006-0.012-0.012-0.025-0.017-0.038c-0.101-0.292,0.885-0.485,1.035-0.49c1.515-0.053,3.036-0.205,4.515-0.551c0.968-0.329,1.938-0.657,2.883-1.05c0.021-0.009,0.087-0.039,0.17-0.078C22.999,16.541,22.89,16.04,22.605,15.494z M22.397,17.352c-0.464,0.17-1.026,0.484-1.252,0.716c-0.225,0.231-0.757,0.452-1.188,0.48c-0.432,0.029-0.712-0.03-0.625-0.118c0.086-0.088-0.146-0.093-0.522-0.022c-0.376,0.071-0.921,0.36-1.216,0.659c-0.296,0.3-0.548,0.497-0.564,0.44c-0.017-0.056,0.146-0.288,0.362-0.516c0.215-0.229-0.021-0.353-0.531-0.297c-0.509,0.058-0.714,0.55-0.311,1.016c0.013,0.013,0.024,0.026,0.036,0.041c0.41,0.46,0.825,0.719,0.813,0.698c-0.013-0.021,0.179-0.24,0.424-0.489c0.246-0.25,0.545-0.223,0.704,0.037c0.158,0.26,0.2,0.57,0.057,0.718c-0.144,0.149,0.178,0.46,0.752,0.543c0.344,0.05,0.696,0.056,1.046,0.013c0.189-0.023,0.369-0.059,0.539-0.107c0.292-0.081,0.458-0.225,0.389-0.271c-0.068-0.046,0.225-0.442,0.653-0.884c0.142-0.146,0.282-0.292,0.425-0.438c0.428-0.442,0.875-1.183,0.893-1.667C23.297,17.419,22.862,17.184,22.397,17.352z M20.224,13.986c-0.675,0.086-0.916-0.718-0.896-1.272c0.018-0.495,0.16-1.292,0.775-1.37c0.698-0.09,0.877,0.721,0.896,1.272C20.982,13.111,20.84,13.907,20.224,13.986z M20.25,11.584c-0.436-0.287-0.567,0.841-0.56,1.032c0.012,0.35,0.059,0.913,0.388,1.13c0.443,0.293,0.554-0.848,0.56-1.032C20.626,12.364,20.58,11.802,20.25,11.584z M16.527,15.25c-0.631,0.081-0.824-0.869-0.808-1.313c0.02-0.579,0.198-1.278,0.86-1.364c0.639-0.081,0.794,0.85,0.81,1.314C17.369,14.465,17.19,15.165,16.527,15.25z M16.64,12.832c-0.478-0.316-0.571,1.04-0.555,1.2c0.033,0.307,0.098,0.771,0.382,0.959c0.434,0.287,0.549-0.828,0.56-1.038C17.014,13.603,16.966,13.047,16.64,12.832z M19.212,7.655c-0.071,0.071-0.145,0.131-0.162,0.134c-0.018,0.003,0.031-0.057,0.109-0.134c0.077-0.077,0.15-0.137,0.161-0.133C19.333,7.524,19.284,7.584,19.212,7.655z M16.305,3.161c-0.017,0.008-0.294,0.19-0.611,0.416s-0.256,0.101,0.13-0.292c0.385-0.393,0.762-0.69,0.84-0.659c0.077,0.03,0.035,0.163-0.094,0.291C16.442,3.044,16.324,3.153,16.305,3.161z M8.963,13.61c-0.011,0.014-0.023,0.021-0.03,0.017c-0.009-0.005-0.005-0.019,0.015-0.032C8.967,13.582,8.977,13.594,8.963,13.61z"/></g></svg>
                            </span>
                            <label class="arf_editor_top_menu_label">
                                <?php echo addslashes(esc_html__('Opt-ins', 'ARForms')); ?>
                            </label>
                        </li>
                        <li class="arf_editor_top_menu_item arf_editor_top_menu_dropdown">
                            <span class="arf_editor_top_menu_item_icon">
                                <svg viewBox="0 -3 32 32">
                                <g id="general_options"><path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M12.501,20.85v2.002H7.474V20.85H1.489v-2h5.985v-2.002h5.027v2.002h16.953v2H12.501z M18.473,14.853v-2.002H1.489v-2h16.984V8.849H23.5v2.002h5.954v2H23.5v2.002H18.473z M12.501,6.854H7.474V4.852H1.489v-2h5.985V0.85h5.027v2.002h16.953v2H12.501V6.854z"/></g></svg>
                            </span>
                            <label class="arf_editor_top_menu_label">
                                <?php echo addslashes(esc_html__('Other Options', 'ARForms')); ?>
                                <span class="arf_editor_top_menu_item_icon_drop_icon">
                                    <svg viewBox="1 1 12 10" width="12px" height="10px">
                                        <g id="arf_top_menu_arrow">
                                            <path fill-rule="evenodd" clip-rule="evenodd" fill="#FFFFFF" d="M13.041,3.751L7.733,9.03c-0.169,0.167-0.39,0.251-0.611,0.251
                                                c-0.221,0-0.442-0.084-0.611-0.251L1.203,3.751C0.897,3.447,0.882,2.979,1.13,2.644C0.882,2.307,0.897,1.839,1.203,1.536
                                                c0.338-0.336,0.885-0.336,1.223,0l4.696,4.67l4.697-4.67c0.337-0.335,0.885-0.335,1.222,0c0.307,0.304,0.32,0.771,0.072,1.108
                                                C13.361,2.98,13.347,3.447,13.041,3.751z"/>
                                        </g>
                                    </svg>
                                </span>
                            </label>
                            <div class="arf_editor_top_dropdown_submenu_container">
                                <ul class="arf_editor_top_dropdown">
                                    <li class="arf_editor_top_dropdown_option" id="general_options"><?php echo addslashes(esc_html__('General Options', 'ARForms')); ?></li>
                                    <li class="arf_editor_top_dropdown_option" id="arf_hidden_fields_options"><?php echo esc_html__('Hidden Input Fields', 'ARForms'); ?></li>
                                    <li class="arf_editor_top_dropdown_option" id="arf_tracking_code"><?php echo addslashes(esc_html__('Submit Tracking Script', 'ARForms')); ?></li>
                                    <li class="arf_editor_top_dropdown_option <?php echo ($_GET['arfaction']=='new' || $_GET['arfaction']=='duplicate') ? 'arf_export_form_editor_note':''; ?>" id="arf_export_current_form_link"><?php echo addslashes(esc_html__('Export Form', 'ARForms')); ?></li>
                                    <?php do_action('arf_editor_general_options_menu'); ?>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="arf_editor_top_menu_button_wrapper">
                    <div class="arf_editor_shortcode_wrapper">
                        <div class="arf_editor_shortcode_icon_wrapper arfbelttooltip" id="arf_shortcodes_info" data-title="<?php echo addslashes(esc_html__('Shortcodes', 'ARForms')); ?>"></div>
                        <div class="arf_editor_form_shortcode_list_popup">
                            <div class="arf_editor_form_shortcode_list_content">
                                <?php
                                    $arf_saved_form_shortcode = "display:none;";
                                    $arf_unsaved_form_shortcode = "";
                                    if(isset($_GET['arfaction']) && $_GET['arfaction']== 'edit'){
                                        $arf_saved_form_shortcode = "";
                                        $arf_unsaved_form_shortcode = "display:none;";
                                    }
                                    $shortcode_form_id = (isset($_GET['arfaction']) && $_GET['arfaction'] == 'edit') ? $form_id : '{arf_form_id}';
                                ?>
                                <ul id="arf_editor_saved_form_shortcodes" class="arf_editor_form_shortcode_list" style="<?php echo $arf_saved_form_shortcode; ?>">
                                    <li class="arf_editor_form_shortcode_header"><span><?php echo addslashes(esc_html__("Shortcodes", "ARForms"));?></span></li>
                                    <li class="arf_editor_form_shortcode">
                                        <span class="arf_shortcode_label"><?php echo addslashes(esc_html__("Embed Inline Form", "ARForms"));?></span>
                                        <span class="arf_shortcode_content">[ARForms id=<?php echo $shortcode_form_id; ?>]</span>
                                    </li>
                                    <li class="arf_editor_form_shortcode">
                                        <span class="arf_shortcode_label"><?php echo addslashes(esc_html__("Embed Popup Form", "ARForms"));?></span>
                                        <span class="arf_shortcode_content">[ARForms_popup id=<?php echo $shortcode_form_id; ?> desc='Click here to open Form' type='link' width='800' modaleffect='fade_in' is_fullscreen='no' overlay='0.6' is_close_link='yes' modal_bgcolor='#000000']</span>
                                    </li>
                                    <li class="arf_editor_form_shortcode">
                                        <span class="arf_shortcode_label"><?php echo addslashes(esc_html__("PHP Function", "ARForms"));?></span>
                                        <span class="arf_shortcode_content">&lt;?php global $maincontroller; echo $maincontroller->get_form_shortcode(array('id'=>'<?php echo $shortcode_form_id; ?>')); ?&gt;</span>
                                        <span class="arf_shortcode_reference_link_container"><a href="<?php echo ARFURL; ?>/documentation/index.html#shortcodes" target="_blank" class="arf_shortcode_reference_link"><?php echo esc_html__("More Info.", "ARForms"); ?></a></span>
                                    </li>
                                </ul>

                                <ul id="arf_editor_unsaved_form_shortcodes" class="arf_editor_form_shortcode_list" style="<?php echo $arf_unsaved_form_shortcode; ?>">
                                    <li class="arf_editor_form_shortcode_header"><span><?php echo addslashes(esc_html__("Shortcodes", "ARForms"));?></span></li>
                                    <li class="arf_editor_form_shortcode">
                                        <span class="arf_shortcode_content"><?php echo addslashes(esc_html__("Please save form to generate shortcode.", "ARForms")); ?></span>
                                    </li>
                                </ul>

                            </div>
                        </div>
                    </div>
                    <button type="submit" name="arf_save" class="arf_top_menu_save_button rounded_button btn_green">
                        <?php echo addslashes(esc_html__('Save', 'ARForms')); ?>                        
                    </button>
                    <button type="button" name="arf_preview" class="arf_top_menu_preview_button arfbelttooltip" data-url="<?php echo ($action == 'new') ? $pre_link . '&form_id=' . $id : $pre_link; ?>" data-default-url="<?php echo ($action == 'new') ? $pre_link : '';?>" onclick="arfgetformpreview();" data-title="<?php echo addslashes(esc_html__('Preview', 'ARForms')); ?>" >
                        <span class="arf_top_menu_preview_button_icon">
                            <svg viewBox="0 0 30 30" width="40px" height="35px">
                            <g id="preview"><path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M12.993,15.23c-7.191,0-11.504-7.234-11.504-7.234S5.801,0.85,12.993,0.85c7.189,0,11.504,7.19,11.504,7.19S20.182,15.23,12.993,15.23z M12.993,2.827c-5.703,0-8.799,5.214-8.799,5.214s3.096,5.213,8.799,5.213c5.701,0,8.797-5.213,8.797-5.213S18.694,2.827,12.993,2.827zM12.993,11.572c-1.951,0-3.531-1.581-3.531-3.531s1.58-3.531,3.531-3.531c1.949,0,3.531,1.581,3.531,3.531S14.942,11.572,12.993,11.572z"/></g>
                            </svg>
                        </span>
                    </button>
                    <button type="button" name="arf_reset" class="arf_top_menu_reset_button arfbelttooltip" data-title="<?php echo addslashes(esc_html__('Reset Style', 'ARForms')); ?>" onclick="reset_style_functionality();" >
                        <span class="arf_top_menu_reset_button_icon">
                            <svg viewBox="-4 -1 30 30" width="40px" height="35px">
                            <g id="preview"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#ffffff" d="M16.07,0.293c-0.26-0.107-0.482-0.063-0.666,0.134l-2.037,1.827c-0.679-0.641-1.455-1.138-2.328-1.49  c-0.872-0.352-1.775-0.528-2.708-0.528c-0.99,0-1.937,0.194-2.838,0.581C4.591,1.204,3.814,1.724,3.16,2.378  C2.506,3.032,1.986,3.81,1.598,4.711c-0.387,0.901-0.58,1.847-0.58,2.837s0.193,1.937,0.58,2.838  c0.388,0.901,0.908,1.679,1.562,2.332c0.654,0.654,1.432,1.175,2.333,1.562c0.901,0.388,1.848,0.581,2.838,0.581  c1.092,0,2.13-0.23,3.113-0.69s1.821-1.109,2.514-1.947c0.051-0.063,0.075-0.135,0.071-0.214c-0.003-0.079-0.033-0.145-0.091-0.195  L12.634,10.5c-0.07-0.058-0.149-0.086-0.238-0.086c-0.102,0.013-0.175,0.051-0.219,0.114c-0.464,0.604-1.031,1.069-1.705,1.4  c-0.672,0.33-1.387,0.494-2.142,0.494c-0.66,0-1.29-0.128-1.89-0.386c-0.601-0.257-1.119-0.604-1.558-1.042  c-0.438-0.438-0.785-0.957-1.042-1.557s-0.386-1.23-0.386-1.891c0-0.659,0.129-1.29,0.386-1.89s0.604-1.119,1.042-1.557  C5.322,3.664,5.84,3.316,6.441,3.059c0.6-0.257,1.229-0.386,1.89-0.386c1.275,0,2.384,0.436,3.323,1.305L9.882,6.062  c-0.196,0.19-0.24,0.41-0.133,0.657C9.858,6.973,10.044,7.1,10.311,7.1h5.521c0.165,0,0.308-0.061,0.429-0.181  c0.12-0.121,0.181-0.264,0.181-0.429V0.855C16.442,0.589,16.318,0.401,16.07,0.293z"></path></g>
                            </svg>
                        </span>
                    </button>
                    <button type="button" name="arf_cancel" class="arf_top_menu_cancel_button arfbelttooltip" onClick="window.location = '<?php echo admin_url('admin.php?page=ARForms'); ?>'" data-title="<?php echo addslashes(esc_html__('Cancel', 'ARForms')); ?>">
                        <span class="arf_top_menu_cancel_button_icon">
                            <svg viewBox="-5 -1 30 30" width="45px" height="45px">
                            <g id="preview"><path fill-rule="evenodd" clip-rule="evenodd" fill="#ffffff" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g>
                            </svg>
                        </span>
                    </button>
                </div>
            </div>
        </div>
        <div class="arf_editor_header_shortcode_belt">
            <div class="arf_editor_header_form_title"></div>
            <div class="arf_editor_header_form_width">
                <div class="arf_editor_form_width_wrapper">
                    <span class="arf_editor_form_width_label"><?php echo addslashes(esc_html__('Width', 'ARForms')); ?></span>
                    <span class="arfform_width_header_span" >

                        <input id="arf_editor_form_width_unit" name="arf_editor_form_width_unit" value="<?php echo $newarr['form_width_unit']; ?>" type="hidden" />
                        <dl class="arf_selectbox" data-name="arf_editor_form_width_unit" data-id="arf_editor_form_width_unit" style="width:40px;">
                            <dt style="border:none;text-align:left;"><span><?php echo $newarr['form_width_unit']; ?></span>
                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                            <dd>
                                <ul style="display: none;margin-top:-3px !important;" data-id="arf_editor_form_width_unit">
                                    <li class="arf_selectbox_option" data-value="px" data-label="px"><?php echo addslashes(esc_html__('px', 'ARForms')); ?></li>
                                    <li class="arf_selectbox_option" data-value="%" data-label="%"><?php echo addslashes(esc_html__('%', 'ARForms')); ?></li>
                                </ul>
                            </dd>
                        </dl>
                    </span>
                    <span class="arf_editor_form_width_input_wrapper">
                        <input type="text" name="arf_editor_form_width" id="arf_editor_form_width" class="arf_editor_form_width_input" value="<?php echo esc_attr($newarr['arfmainformwidth']) ?>" />
                    </span>
                    <div class="arf_display_form_id_editor <?php echo ($arfaction == 'edit') ? '' : 'arf_save_form_id_note' ?>">(Form ID: <?php echo ($arfaction == 'edit') ? $form_id : '{arf_form_id}'; ?>)</div>
                </div>
            </div>           
        </div>

        <div class="arf_form_editor_wrapper">
            <div class="arf_form_element_wrapper">
                <div class="arf_form_element_header"><?php echo addslashes(esc_html__('Form Elements', 'ARForms')); ?></div>
                <ul class="arf_form_elements_container">
                    <?php
                    $advancedFields = $arfieldhelper->pro_field_selection();
                    $allFields = array_merge($arffield_selection, $advancedFields);
                    foreach ($allFields as $key => $value) {
                        $icon = $value['icon'];
                        ?>
                        <li class="arf_form_element_item frmbutton frm_t<?php echo $key ?>" id="<?php echo $key; ?>" data-field-id="<?php echo $id; ?>" data-type="<?php echo $key; ?>">
                            <div class="arf_form_element_item_inner_container">
                                <span class="arf_form_element_item_icon">
                                    <?php echo $icon; ?>
                                </span>
                                <label class="arf_form_element_item_text"><?php echo $value['label']; ?></label>
                            </div>
                        </li>
                        <?php
                    }
                    do_action('arfafterbasicfieldlisting', $id, $values);
                    ?>
                </ul>
        <div class="arf_form_element_resize"></div>
        <?php
            $svg_style = "";
            $viewBox = "0 -6 30 30";
            if( is_rtl() ){
                $svg_style = "position:relative;left:15px;transform:rotateY(180deg);-webkit-transform:rotateY(180deg);-o-transform:rotateY(180deg);-moz-transform:rotateY(180deg);-ms-transform:rotateY(180deg);";
                $viewBox = "-13 -6 30 30";
            }
        ?>
        <button type="button" class="arf_hide_form_element_wrapper"><svg viewBox="<?php echo $viewBox; ?>" width="25px" height="45px" style="<?php echo $svg_style ; ?>"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4E5462" d="M3.845,6.872l4.816,4.908l-1.634,1.604L0.615,6.849L0.625,6.84  L0.617,6.832L7.152,0.42l1.603,1.634L3.845,6.872z"/></svg></button> 
            </div>
            <?php echo str_replace('id="{arf_id}"','id="arfeditor_loader" style="display:block;" ',ARF_LOADER_ICON)?>
            <div class="arf_form_editor_content" style="display:none;">
                <div class="arf_form_editor_inner_container" id="maineditcontentview">
                    <?php require(VIEWS_PATH . '/edit_form.php'); ?>
                </div>
            </div>
            <div class="arf_form_styling_tools">
                <ul class="arf_form_style_tabs">
                    <li class="arf_form_style_tab_item active" data-id="arf_form_styling_tools"><?php echo addslashes(esc_html__('Style Options', 'ARForms')); ?></li>
                    <li class="arf_form_style_tab_item" data-id="arf_form_custom_css"><?php echo addslashes(esc_html__('Custom CSS', 'ARForms')); ?></li>
                </ul>
                <input type="hidden" name="arf_styling_height" id="arf_styling_height"/>
                <input type="hidden" name="arf_styling_content_height" id="arf_styling_content_height"/>
                <div class="arf_form_style_tab_container active" id="arf_form_styling_tools">
                    <input type="hidden" name="arfmf" value="<?php echo $id; ?>" id="arfmainformid" />
                    <div class="arf_form_style_tab_accordion">
                        <div class="arf_form_accordion_tabs">
                            <dl class="arf_accordion_tab_color_options active">
                                <dd>
                                    <a href="javascript:void(0)" data-target="arf_accordion_tab_color_options"><?php echo addslashes(esc_html__('Basic Styling Options', 'ARForms')); ?></a>
                                    <div class="arf_accordion_container active">
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class='arf_accordion_outer_title'><?php echo addslashes(esc_html__('Select Theme', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row">
                                            <div class='arf_accordion_inner_title arf_width_50'><?php echo esc_html__('Input Style', 'ARForms'); ?></div>
                                            <div class="arf_accordion_content_container arf_width_50 arf_right">
                                                <div class="arf_dropdown_wrapper" style="margin-right: 5px;">

                                                    <?php

                                                    $inputStyle = array();

                                                    $newarr['arfinputstyle'] = (isset($newarr['arfinputstyle']) && $newarr['arfinputstyle'] != '' ) ? $newarr['arfinputstyle'] : 'material';
                                                    $inputStyle = array(
                                                        'standard' => addslashes(esc_html__('Standard Style', 'ARForms')),
                                                        'rounded' => addslashes(esc_html__('Rounded Style', 'ARForms')),
                                                        'material' => addslashes(esc_html__('Material Style', 'ARForms'))
                                                    );
                                                    ?>
                                                    <input type="hidden" name="arfinpst" value="<?php echo $newarr['arfinputstyle']; ?>" id="arfmainforminputstyle" />
                                                    <dl class="arf_selectbox" data-name="arfinpst" data-id="arfmainforminputstyle">
                                                        <dt style="width:140px;">
                                                        <span style="float:left;"><?php echo $inputStyle[$newarr['arfinputstyle']]; ?></span>
                                                        <i class="arfa arfa-caret-down arfa-lg"></i>
                                                        </dt>
                                                        <dd>
                                                            <ul style="display:none;" data-id="arfmainforminputstyle">
                                                                <?php
                                                                foreach ($inputStyle as $style => $value) {
                                                                    ?>
                                                                    <li class="arf_selectbox_option" data-value="<?php echo $style; ?>" data-label="<?php echo htmlentities($value); ?>"><?php echo $value; ?></li>
                                                                    <?php
                                                                }
                                                                ?>
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class='arf_accordion_outer_title'><?php echo addslashes(esc_html__('Color Scheme', 'ARForms')); ?><div class="arf_imageloader arf_form_style_color_scheme_loader" id="arf_color_scheme_loader"></div></div>
                                        </div>
                                        <div class="arf_accordion_container_row" style="height: auto;">
                                            <div class='arf_accordion_inner_title arf_custom_color_title'><?php echo addslashes(esc_html__('Choose Color', 'ARForms')) ?></div>
                                            <div class="arf_accordion_content_container arf_custom_color_div arf_right" style="margin-right: -4px;">
                                                <input type="hidden" name="arfmcs" data-db-skin="<?php echo $active_skin; ?>" id="arf_color_skin" value="<?php echo $active_skin; ?>" data-default-skin="<?php echo $active_skin; ?>" />
                                                <?php
                                                if (isset($skinJson->skins) && !empty($skinJson->skins)) {
                                                    foreach ($skinJson->skins as $skin => $val) {
                                                        if( $skin == 'custom' ){
                                                            continue;
                                                        }
                                                        ?>
                                                        <div class="arf_skin_container <?php echo ($active_skin == $skin) ? 'active_skin' : ''; ?>" data-skin="<?php echo $skin; ?>" style="background:<?php echo $val->main; ?>;" id="arf_skin_<?php echo $skin; ?>">
                                                        </div>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </div>
                                            <div class="arf_customize_color_div arf_right" style="width:100%;">
                                                <div class="arf_customize_color_inner_label_div">
                                                    <div class='arf_accordion_inner_title arf_label_custom_color' style="width: 90%;"><?php echo addslashes(esc_html__('Custom Color', 'ARForms')); ?></div>
                                                </div>
                                                <div class="arf_customize_color_inner_control_div">
                                                    <?php $custom_bg_color = (isset($newarr['arfmainbasecolor']) && $newarr['arfmainbasecolor'] != "" ) ? esc_attr($newarr['arfmainbasecolor']) : $skinJson->skins->$active_skin->main ?>
                                                    <div class="arf_skin_container <?php echo ($active_skin == 'custom') ? 'active_skin' : ''; ?>" data-skin="custom" style="background:<?php echo $custom_bg_color; ?>;margin-top: 11px;margin-right: 11px;margin-left: -5px;"></div>
                                                    <div class="arf_custom_color">
                                                        <div class="arf_custom_color_icon">
                                                            <svg viewBox="-6 -10 35 35">
                                                            <g id="paint_brush"><path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M15.948,7.303L15.875,7.23l0.049-0.049l-2.459-2.459l3.944-3.872l2.313,0.024v2.654L15.948,7.303z M12.631,6.545c0.058,0.039,0.111,0.081,0.167,0.122c0.036,0.005,0.066,0.011,0.066,0.011c0.022,0.008,0.034,0.023,0.056,0.032l1.643,1.643c0.58,5.877-7.619,6.453-7.619,6.453c-5.389,0.366-5.455-1.907-5.455-1.907c3.559,1.164,6.985-5.223,6.985-5.223C11.001,4.915,12.631,6.545,12.631,6.545z"/></g>
                                                            </svg>
                                                        </div>
                                                        <div class="arf_custom_color_label" style="width: 70px;"><?php echo addslashes(esc_html__('Custom', 'ARForms')); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class='arf_accordion_outer_title'><?php echo addslashes(esc_html__('Font Options', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row">
                                            <div class='arf_accordion_inner_title arf_width_50'><?php echo addslashes(esc_html__('Font Family', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_width_50 arf_right">
                                                <div class="arf_dropdown_wrapper" style="margin-right: 5px;">
                                                    <input id="arfcommonfontfamily" name="arfcommonfont" value="<?php echo isset($newarr['arfcommonfont']) ? $newarr['arfcommonfont'] : 'Helvetica'; ?>" type="hidden">
                                                    <dl class="arf_selectbox" data-name="arfcommonfont" data-id="arfcommonfontfamily">
                                                        <dt style="width:140px;"><span><?php echo isset($newarr['arfcommonfont']) ? $newarr['arfcommonfont'] : 'Helvetica'; ?></span>
                                                        <input value="<?php echo isset($newarr['arfcommonfont']) ? $newarr['arfcommonfont'] : 'Helvetica'; ?>" style="display:none;" class="arf_autocomplete" type="text" autocomplete="off">
                                                        <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                                        <dd>
                                                            <ul style="display: none;" data-id="arfcommonfontfamily">
                                                                <?php arf_font_li_listing(); ?>
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_accordion_container_row_input_size" >
                                            <div class='arf_accordion_inner_title arfwidth40'><?php echo esc_html__('Input Field size', 'ARForms'); ?></div>
                                            <div class="arf_accordion_content_container arfwidth60" style="margin-left: -5px;">
                                                <div class="arf_slider_wrapper">
                                                    <input id="arfmainfieldcommonsize_exs" class="arf_slider" data-slider-id='arfmainfieldcommonsize_exsSlider' type="text" data-slider-min="1" data-slider-max="10" data-slider-step="1" data-slider-value="<?php echo isset($newarr['arfmainfieldcommonsize']) ? esc_attr($newarr['arfmainfieldcommonsize']) : '3' ?>" />
                                                    <div class="arf_slider_unit_data">
                                                        <div style="float:left;margin-left: -7px;"><?php echo addslashes(esc_html__('1', 'ARForms')); ?></div>
                                                        <div style="float:right;margin-right:-15px;"><?php echo addslashes(esc_html__('10', 'ARForms')); ?></div>
                                                    </div>

                                                    <input type="hidden" name="arfmainfieldcommonsize" style="width:100px;" class="txtxbox_widget"  id="arfmainfieldcommonsize" value="<?php echo isset($newarr['arfmainfieldcommonsize']) ? esc_attr($newarr['arfmainfieldcommonsize']) : '3' ?>" size="4" />
                                                </div>


                                            </div>
                                            <div class="arf_right arfmarginright">
                                                <div class="arf_custom_font" style="margin-top: 20px;">
                                                    <div class="arf_custom_font_icon">
                                                        <svg viewBox="-10 -10 35 35">
                                                        <g id="paint_brush">
                                                        <path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M7.423,14.117c1.076,0,2.093,0.022,3.052,0.068v-0.82c-0.942-0.078-1.457-0.146-1.542-0.205  c-0.124-0.092-0.203-0.354-0.235-0.787s-0.049-1.601-0.049-3.504l0.059-6.568c0-0.299,0.013-0.472,0.039-0.518  C8.772,1.744,8.85,1.725,8.981,1.725c1.549,0,2.584,0.043,3.105,0.128c0.162,0.026,0.267,0.076,0.313,0.148  c0.059,0.092,0.117,0.687,0.176,1.784h0.811c0.052-1.201,0.14-2.249,0.264-3.145l-0.107-0.156c-2.396,0.098-4.561,0.146-6.494,0.146  c-1.94,0-3.936-0.049-5.986-0.146L0.954,0.563c0.078,0.901,0.11,1.976,0.098,3.223h0.84c0.085-1.062,0.141-1.633,0.166-1.714  C2.083,1.99,2.121,1.933,2.17,1.9c0.049-0.032,0.262-0.065,0.641-0.098c0.652-0.052,1.433-0.078,2.34-0.078  c0.443,0,0.674,0.024,0.69,0.073c0.016,0.049,0.024,1.364,0.024,3.947c0,1.313-0.01,2.602-0.029,3.863  c-0.033,1.776-0.072,2.804-0.117,3.084c-0.039,0.201-0.098,0.34-0.176,0.414c-0.078,0.075-0.212,0.129-0.4,0.161  c-0.404,0.065-0.791,0.098-1.162,0.098v0.82C4.861,14.14,6.008,14.117,7.423,14.117L7.423,14.117z"/>
                                                        </svg>
                                                    </div>

                                                    <div class="arf_custom_font_label"><?php echo addslashes(esc_html__('Advanced font options', 'ARForms')); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class='arf_accordion_outer_title'><?php echo addslashes(esc_html__('Form Width Settings', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Form Width', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container" style="<?php echo is_rtl() ? "width:50%;" : "width:58%;"; ?>" >
                                                <div class="arf_dropdown_wrapper">
                                                    <input id="arffu" name="arffu" value="<?php echo $newarr['form_width_unit']; ?>" type="hidden" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id}.arf_form_outer_wrapper~|~arf_form_width_unit","material":".ar_main_div_{arf_form_id}.arf_form_outer_wrapper~|~arf_form_width_unit"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_outer_wrapper">
                                                    <dl class="arf_selectbox" data-name="arffu" data-id="arffu" style="width:50px;">
                                                        <dt><span><?php echo $newarr['form_width_unit']; ?></span>
                                                        <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                                        <dd>
                                                            <ul style="display: none;" data-id="arffu">
                                                                <li class="arf_selectbox_option" data-value="<?php echo addslashes(esc_html__('px', 'ARForms')); ?>" data-label="<?php echo addslashes(esc_html__('px', 'ARForms')); ?>"><?php echo addslashes(esc_html__('px', 'ARForms')); ?></li>
                                                                <li class="arf_selectbox_option" data-value="<?php echo addslashes(esc_html__('%', 'ARForms')); ?>" data-label="<?php echo addslashes(esc_html__('%', 'ARForms')); ?>"><?php echo addslashes(esc_html__('%', 'ARForms')); ?></li>
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                </div>
                                                <input type="text" name="arffw" class="arf_small_width_txtbox arfcolor" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id}.arf_form_outer_wrapper~|~max-width{arf_form_width_unit}","material":".ar_main_div_{arf_form_id}.arf_form_outer_wrapper~|~max-width{arf_form_width_unit}"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_outer_wrapper" value="<?php echo esc_attr($newarr['arfmainformwidth']) ?>" id="arf_form_width"/>
                                            </div>

                                        </div>
                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class='arf_accordion_outer_title'><?php echo addslashes(esc_html__('Validation Message Style', 'ARForms')); ?></div>
                                            <div class="arf_accordion_container_row arf_half_width">
                                                <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Type', 'ARForms')); ?></div>
                                                <div class="arf_accordion_content_container arf_align_right arf_right">                                                    
                                                    <div class="arf_toggle_button_group arf_two_button_group">
                                                        <?php $newarr['arferrorstyle'] = isset($newarr['arferrorstyle']) ? $newarr['arferrorstyle'] : 'normal'; ?>
                                                        <label class="arf_toggle_btn <?php echo ($newarr['arferrorstyle'] == 'normal') ? 'arf_success' : ''; ?>"><input type="radio" name="arfest" class="visuallyhidden" id="arfest1" value="normal" <?Php checked($newarr['arferrorstyle'], 'normal'); ?> /><?php echo addslashes(esc_html__('Standard', 'ARForms')); ?></label>
                                                        <label class="arf_toggle_btn <?php echo ($newarr['arferrorstyle'] == 'advance') ? 'arf_success' : ''; ?>"><input type="radio" name="arfest" class="visuallyhidden" id="arfest2" value="advance" <?Php checked($newarr['arferrorstyle'], 'advance'); ?> /><?php echo addslashes(esc_html__('Modern', 'ARForms')); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="arf_accordion_container_row arf_half_width" id="arf_validation_message_style_position" style="<?php echo ($newarr['arferrorstyle'] == 'normal') ? 'display: none;' : '';?>">
                                                <div class="arf_accordion_inner_title" ><?php echo addslashes(esc_html__('Position', 'ARForms')); ?></div>
                                                <div class="arf_accordion_content_container">                                                    
                                                    <div class="arf_toggle_button_group arf_four_button_group">
                                                        <?php $newarr['arferrorstyleposition'] = isset($newarr['arferrorstyleposition']) ? $newarr['arferrorstyleposition'] : 'right'; ?>
                                                        <label class="arf_toggle_btn <?php echo ($newarr['arferrorstyleposition'] == 'right') ? 'arf_success' : ''; ?>"><input type="radio" name="arfestbc" class="visuallyhidden" data-id="arfestbc2" value="right" <?Php checked($newarr['arferrorstyleposition'], 'right'); ?> /><?php echo addslashes(esc_html__('Right', 'ARForms')); ?></label>
                                                        <label class="arf_toggle_btn <?php echo ($newarr['arferrorstyleposition'] == 'left') ? 'arf_success' : ''; ?>"><input type="radio" name="arfestbc" class="visuallyhidden" data-id="arfestbc2" value="left" <?Php checked($newarr['arferrorstyleposition'], 'left'); ?> /><?php echo addslashes(esc_html__('Left', 'ARForms')); ?></label>
                                                        <label class="arf_toggle_btn <?php echo ($newarr['arferrorstyleposition'] == 'bottom') ? 'arf_success' : ''; ?>"><input type="radio" name="arfestbc" class="visuallyhidden" data-id="arfestbc2" value="bottom" <?Php checked($newarr['arferrorstyleposition'], 'bottom'); ?> /><?php echo addslashes(esc_html__('Bottom', 'ARForms')); ?></label>
                                                        <label class="arf_toggle_btn <?php echo ($newarr['arferrorstyleposition'] == 'top' ) ? 'arf_success' : ''; ?>"><input type='radio' name='arfestbc' class='visuallyhidden' id='arfestbc1' value='top' <?php checked($newarr['arferrorstyleposition'], 'top'); ?> /><?php echo addslashes(esc_html__('Top', 'ARForms')); ?></label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="arf_accordion_container_row arf_half_width" id="arf_standard_validation_message_style_position" style="<?php echo ($newarr['arferrorstyle'] == 'advance') ? 'display: none;' : '';?>">
                                                <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Position', 'ARForms')); ?></div>
                                                <div class="arf_accordion_content_container">
                                                    <div class="arf_toggle_button_group arf_four_button_group">

                                                        <?php $newarr['arfstandarderrposition'] = isset($newarr['arfstandarderrposition']) ? $newarr['arfstandarderrposition'] : 'relative'; ?>

                                                        <label class="arf_toggle_btn <?php echo ($newarr['arfstandarderrposition'] == 'absolute') ? 'arf_success' : ''; ?>">
                                                            <input type="radio" name="arfstndrerr" class="visuallyhidden" data-id="arfstndrerr2" value="absolute" <?Php checked($newarr['arfstandarderrposition'], 'absolute'); ?> /><?php echo addslashes(esc_html__('Absolute', 'ARForms')); ?>
                                                        </label>

                                                        <label class="arf_toggle_btn <?php echo ($newarr['arfstandarderrposition'] == 'relative') ? 'arf_success' : ''; ?>">
                                                            <input type="radio" name="arfstndrerr" class="visuallyhidden" data-id="arfstndrerr2" value="relative" <?Php checked($newarr['arfstandarderrposition'], 'relative'); ?> /><?php echo addslashes(esc_html__('Relative', 'ARForms')); ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </dd>
                            </dl>

                            <dl class="arf_accordion_tab_form_settings">
                                <dd>
                                    <a href="javascript:void(0)" data-target="arf_accordion_tab_form_settings"><?php echo addslashes(esc_html__('Advanced Form Options', 'ARForms')); ?></a>
                                    <div class="arf_accordion_container">
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Form Title options', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text arf_width_50"><?php echo addslashes(esc_html__('Display Title and Description', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right arf_width_50">
                                                <div class="arf_float_right arfmarginright4">
                                                    <label class="arf_js_switch_label">
                                                        <span><?php echo addslashes(esc_html__('No', 'ARForms')); ?>&nbsp;</span>
                                                    </label>
                                                    <span class="arf_js_switch_wrapper">
                                                        <input type="checkbox" class="js-switch" name="options[display_title_form]" id="display_title_form" <?php echo (isset($values_nw['display_title_form']) && $values_nw['display_title_form'] == '1') ? 'checked="checked"' : ''; ?> onchange="change_form_title();" value="<?php echo isset($values_nw['display_title_form']) ? $values_nw['display_title_form'] : ''; ?>" />
                                                        <span class="arf_js_switch"></span>
                                                    </span>
                                                    <label class="arf_js_switch_label">
                                                        <span>&nbsp;<?php echo addslashes(esc_html__('Yes', 'ARForms')); ?></span>
                                                    </label>
                                                </div>
                                            </div>
                                            <input type="hidden" id="temp_display_title_form" value="1" />
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text"><?php echo addslashes(esc_html__('Title Alignment', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right arf_right">                                               
                                                <div class="arf_toggle_button_group arf_three_button_group">
                                                    <?php

                                                    $newarr['arfformtitlealign'] = isset($newarr['arfformtitlealign']) ? $newarr['arfformtitlealign'] : 'center';
                                                    ?>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['arfformtitlealign'] == 'right') ? 'arf_success' : ''; ?>"><input  class="visuallyhidden" type="radio" name="arffta" value="right" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~text-align||.ar_main_div_{arf_form_id} .arf_fieldset .formdescription_style~|~text-align","material":".ar_main_div_{arf_form_id}  .arf_fieldset .formtitle_style~|~text-align||.ar_main_div_{arf_form_id} .arf_fieldset .formdescription_style~|~text-align"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_text_align" <?php checked($newarr['arfformtitlealign'], 'right') ?> /><?php echo addslashes(esc_html__('Right', 'ARForms')); ?></label>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['arfformtitlealign'] == 'center') ? 'arf_success' : ''; ?>"><input  class="visuallyhidden" type="radio" name="arffta" value="center" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~text-align||.ar_main_div_{arf_form_id} .arf_fieldset .formdescription_style~|~text-align","material":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~text-align||.ar_main_div_{arf_form_id} .arf_fieldset .formdescription_style~|~text-align"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_text_align" <?php checked($newarr['arfformtitlealign'], 'center') ?> /><?php echo addslashes(esc_html__('Center', 'ARForms')); ?></label>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['arfformtitlealign'] == 'left') ? 'arf_success' : ''; ?>"><input  class="visuallyhidden" type="radio" name="arffta" value="left" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~text-align||.ar_main_div_{arf_form_id} .arf_fieldset .formdescription_style~|~text-align","material":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~text-align||.ar_main_div_{arf_form_id} .arf_fieldset .formdescription_style~|~text-align"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_text_align" <?php checked($newarr['arfformtitlealign'], 'left') ?> /><?php echo addslashes(esc_html__('Left', 'ARForms')); ?></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text arf_form_padding"><?php echo addslashes(esc_html__('Margin', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_center arf_form_container arf_right arfformmarginvals">
                                                <span class="arfpxspan arfformarginvalpx">px</span>
                                                <div class="arf_form_margin_box_wrapper"><input type="text" name="arfformtitlepaddingsetting_1" id="arfformtitlepaddingsetting_1" value="<?php echo esc_attr($newarr['arfmainformtitlepaddingsetting_1']); ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .allfields .arftitlediv~|~margin-top||.ar_main_div_{arf_form_id} .arftitlecontainer~|~margin-top","material":".ar_main_div_{arf_form_id} .allfields .arftitlediv~|~margin-top"}' class="arf_form_margin_box" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_margin" /><br /><span class="arf_px arf_font_size arfformmarginleft"><?php echo addslashes(esc_html__('Top', 'ARForms')); ?></span></div>
                                                <div class="arf_form_margin_box_wrapper"><input type="text" name="arfformtitlepaddingsetting_2" id="arfformtitlepaddingsetting_2" value="<?php echo esc_attr($newarr['arfmainformtitlepaddingsetting_2']); ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .allfields .arftitlediv~|~margin-right||.ar_main_div_{arf_form_id} .arftitlecontainer~|~margin-right","material":".ar_main_div_{arf_form_id} .allfields .arftitlediv~|~margin-right"}' class="arf_form_margin_box" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_margin" /><br /><span class="arf_px arf_font_size arfformmarginleft"><?php echo addslashes(esc_html__('Right', 'ARForms')); ?></span></div>
                                                <div class="arf_form_margin_box_wrapper"><input type="text" name="arfformtitlepaddingsetting_3" id="arfformtitlepaddingsetting_3" value="<?php echo esc_attr($newarr['arfmainformtitlepaddingsetting_3']); ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .allfields .arftitlediv~|~margin-bottom||.ar_main_div_{arf_form_id} .arftitlecontainer~|~margin-bottom","material":".ar_main_div_{arf_form_id} .allfields .arftitlediv~|~margin-bottom"}' class="arf_form_margin_box" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_margin" /><br /><span class="arf_px arf_font_size arfformmarginleft"><?php echo addslashes(esc_html__('Bottom', 'ARForms')); ?></span></div>
                                                <div class="arf_form_margin_box_wrapper"><input type="text" name="arfformtitlepaddingsetting_4" id="arfformtitlepaddingsetting_4" value="<?php echo esc_attr($newarr['arfmainformtitlepaddingsetting_4']); ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .allfields .arftitlediv~|~margin-left||.ar_main_div_{arf_form_id} .arftitlecontainer~|~margin-left","material":".ar_main_div_{arf_form_id} .allfields .arftitlediv~|~margin-left"}' class="arf_form_margin_box" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_margin" /><br /><span class="arf_px arf_font_size arfformmarginleft"><?php echo addslashes(esc_html__('Left', 'ARForms')); ?></span></div>
                                            </div>
                                            <?php
                                            $arfformtitlepaddingsetting_value = '';

                                            if (esc_attr($newarr['arfmainformtitlepaddingsetting_1']) != '') {
                                                $arfformtitlepaddingsetting_value .= $newarr['arfmainformtitlepaddingsetting_1'] . 'px ';
                                            } else {
                                                $arfformtitlepaddingsetting_value .= '0px ';
                                            }
                                            if (esc_attr($newarr['arfmainformtitlepaddingsetting_2']) != '') {
                                                $arfformtitlepaddingsetting_value .= $newarr['arfmainformtitlepaddingsetting_2'] . 'px ';
                                            } else {
                                                $arfformtitlepaddingsetting_value .= '0px ';
                                            }
                                            if (esc_attr($newarr['arfmainformtitlepaddingsetting_3']) != '') {
                                                $arfformtitlepaddingsetting_value .= $newarr['arfmainformtitlepaddingsetting_3'] . 'px ';
                                            } else {
                                                $arfformtitlepaddingsetting_value .= '0px ';
                                            }
                                            if (esc_attr($newarr['arfmainformtitlepaddingsetting_4']) != '') {
                                                $arfformtitlepaddingsetting_value .= $newarr['arfmainformtitlepaddingsetting_4'] . 'px';
                                            } else {
                                                $arfformtitlepaddingsetting_value .= '0px';
                                            }
                                            ?>
                                            <input type="hidden" name="arfftps" style="width:100px;" id="arfformtitlepaddingsetting" class="txtxbox_widget" value="<?php echo $arfformtitlepaddingsetting_value; ?>" />
                                        </div>
                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class='arf_accordion_outer_title'><?php echo addslashes(esc_html__('Form Settings', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_width_50"><?php echo addslashes(esc_html__('Form Alignment', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right arf_width_50 arfhieght35">
                                                <div class="arf_toggle_button_group arf_three_button_group" style="margin-right:5px;">
                                                    <?php $newarr['form_align'] = isset($newarr['form_align']) ? $newarr['form_align'] : 'center'; 
                                                        
                                                    ?>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['form_align'] == 'right') ? 'arf_success' : ''; ?>"><input type="radio" name="arffa" class="visuallyhidden" data-id="arfestbc2" value="right" <?Php checked($newarr['form_align'], 'right'); ?> data-arfstyle="true" data-arfstyledata='{"standard":".arf_form.ar_main_div_{arf_form_id}~|~text-align||.arf_form.ar_main_div_{arf_form_id} form~|~text-align||.arf_form.ar_main_div_{arf_form_id} .unsortable_inner_wrapper.edit_field_type_divider label.arf_main_label~|~text-align","material":".arf_form.ar_main_div_{arf_form_id}~|~text-align||.arf_form.ar_main_div_{arf_form_id} form~|~text-align||.arf_form.ar_main_div_{arf_form_id} .unsortable_inner_wrapper.edit_field_type_divider label.arf_main_label~|~text-align"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_align"
                                                    /><?php echo addslashes(esc_html__('Right', 'ARForms')); ?></label>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['form_align'] == 'center') ? 'arf_success' : ''; ?>"><input type="radio" name="arffa" class="visuallyhidden" data-id="arfestbc2" value="center" <?Php checked($newarr['form_align'], 'center'); ?> data-arfstyle="true" data-arfstyledata='{"standard":".arf_form.ar_main_div_{arf_form_id}~|~text-align||.arf_form.ar_main_div_{arf_form_id} form~|~text-align||.arf_form.ar_main_div_{arf_form_id} .unsortable_inner_wrapper.edit_field_type_divider label.arf_main_label~|~text-align","material":".arf_form.ar_main_div_{arf_form_id}~|~text-align||.arf_form.ar_main_div_{arf_form_id} form~|~text-align||.arf_form.ar_main_div_{arf_form_id} .unsortable_inner_wrapper.edit_field_type_divider label.arf_main_label~|~text-align"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_align"/><?php echo addslashes(esc_html__('Center', 'ARForms')); ?></label>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['form_align'] == 'left') ? 'arf_success' : ''; ?>"><input type="radio" name="arffa" class="visuallyhidden" data-id="arfestbc2" value="left" <?Php checked($newarr['form_align'], 'left'); ?> data-arfstyle="true" data-arfstyledata='{"standard":".arf_form.ar_main_div_{arf_form_id}~|~text-align||.arf_form.ar_main_div_{arf_form_id} form~|~text-align||.arf_form.ar_main_div_{arf_form_id} .unsortable_inner_wrapper.edit_field_type_divider label.arf_main_label~|~text-align","material":".arf_form.ar_main_div_{arf_form_id}~|~text-align||.arf_form.ar_main_div_{arf_form_id} form~|~text-align||.arf_form.ar_main_div_{arf_form_id} .unsortable_inner_wrapper.edit_field_type_divider label.arf_main_label~|~text-align"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_align"/><?php echo addslashes(esc_html__('Left', 'ARForms')); ?></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width" style="height: auto;">
                                            <div class="arf_accordion_inner_title arf_two_row_text "><?php echo addslashes(esc_html__('Background Image', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right">
                                                <div class="arf_imageloader arf_form_style_file_upload_loader" id="ajax_form_loader"></div>
                                                <div id="form_bg_img_div" <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '9') { ?> class="iframe_original_btn" data-id="arfmfbi" style="margin-right:5px; position: relative; overflow: hidden; cursor:pointer; max-width:140px; height:27px; background: #1BBAE1; font-weight:bold; <?php if ($newarr['arfmainform_bg_img'] == '') { ?> background:#1BBAE1;padding: 7px 10px 0 10px;font-size:13px;border-radius:3px;-webkit-border-radius:3px;-o-border-radius:3px;-moz-border-radius:3px;color:#FFFFFF;border:1px solid #CCCCCC;display: inline-block; <?php } ?>" <?php } else { ?> style="margin-left:0px;" <?php } ?>  >
                                                    <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '9' && $newarr['arfmainform_bg_img'] == '') { ?><span class="arf_form_style_file_upload_icon">
                                                        <svg width="16" height="18" viewBox="0 0 18 20" fill="#ffffff"><path xmlns="http://www.w3.org/2000/svg" d="M15.906,18.599h-1h-12h-1h-1v-7h2v5h12v-5h2v7H15.906z M13.157,7.279L9.906,4.028v8.571c0,0.552-0.448,1-1,1c-0.553,0-1-0.448-1-1v-8.54l-3.22,3.22c-0.403,0.403-1.058,0.403-1.46,0 c-0.403-0.403-0.403-1.057,0-1.46l4.932-4.932c0.211-0.211,0.488-0.306,0.764-0.296c0.275-0.01,0.553,0.085,0.764,0.296 l4.932,4.932c0.403,0.403,0.403,1.057,0,1.46S13.561,7.682,13.157,7.279z"/></svg></span><?php } ?>
                                                    <input type="hidden" name="arfmfbi" onclick="clear_file_submit();" value="<?php echo esc_attr($newarr['arfmainform_bg_img']) ?>" data-id="arfmainform_bg_img" />
                                                    <?php
                                                    if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '9') {
                                                        if ($newarr['arfmainform_bg_img'] != '') {
                                                            ?>
                                                            <img src="<?php echo $newarr['arfmainform_bg_img']; ?>" height="35" width="35" style="margin-left:5px;border:1px solid #D5E3FF !important;" />&nbsp;<span onclick="delete_image('form_image');" style="width:35px;height: 35px;display:inline-block;cursor: pointer;"><svg width="23px" height="27px" viewBox="0 0 30 30"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4786FF" d="M19.002,4.351l0.007,16.986L3.997,21.348L3.992,4.351H1.016V2.38  h1.858h4.131V0.357h8.986V2.38h4.146h1.859l0,0v1.971H19.002z M16.268,4.351H6.745H5.993l0.006,15.003h10.997L17,4.351H16.268z   M12.01,7.346h1.988v9.999H12.01V7.346z M9.013,7.346h1.989v9.999H9.013V7.346z"/></svg></span>
                                                            </span>
                                                            
                                                        <?php } else { ?>

                                                            <input type="text" class="original" name="form_bg_img" id="field_arfmfbi" data-form-id="" data-file-valid="true" style="position: absolute; cursor: pointer; top: 0px; width: 160px; height: 59px; left: -999px; z-index: 100; opacity: 0; filter:alpha(opacity=0);" />

                                                            <input type="hidden" id="type_arfmfbi" name="type_arfmfbi" value="1" >
                                                            <input type="hidden" value="jpg, jpeg, jpe, gif, png, bmp, tif, tiff, ico" id="file_types_arfmfbi" name="field_types_arfmfbi" />
                                                            <input type="hidden" name="imagename_form" id="imagename_form" value="" />
                                                            <input type="hidden" name="arfmfbi" onclick="clear_file_submit();" value="" data-id="arfmainform_bg_img" />

                                                            <?php
                                                        }
                                                        echo '<div id="arfmfbi_iframe_div"><iframe style="display:none;" id="arfmfbi_iframe" src="' . ARFURL . '/core/views/iframe.php" ></iframe></div>';
                                                    } else {
                                                        ?>
                                                        <?php if ($newarr['arfmainform_bg_img'] != '') { ?>
                                                            <img src="<?php echo $newarr['arfmainform_bg_img']; ?>" height="35" width="35" style="margin-left:5px;border:1px solid #D5E3FF !important;" />&nbsp;<span onclick="delete_image('form_image');" style="width:35px;height: 35px;display:inline-block;cursor: pointer;"><svg width="23px" height="27px" viewBox="0 0 30 30"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4786FF" d="M19.002,4.351l0.007,16.986L3.997,21.348L3.992,4.351H1.016V2.38  h1.858h4.131V0.357h8.986V2.38h4.146h1.859l0,0v1.971H19.002z M16.268,4.351H6.745H5.993l0.006,15.003h10.997L17,4.351H16.268z   M12.01,7.346h1.988v9.999H12.01V7.346z M9.013,7.346h1.989v9.999H9.013V7.346z"/></svg></span>
                                                        <?php } else { ?>

                                                            <div class="arfajaxfileupload" style="position: relative; overflow: hidden; cursor: pointer;">
                                                                <div class="arf_form_style_file_upload_icon">
                                                                    <svg width="16" height="18" viewBox="0 0 18 20" fill="#ffffff"><path xmlns="http://www.w3.org/2000/svg" d="M15.906,18.599h-1h-12h-1h-1v-7h2v5h12v-5h2v7H15.906z M13.157,7.279L9.906,4.028v8.571c0,0.552-0.448,1-1,1c-0.553,0-1-0.448-1-1v-8.54l-3.22,3.22c-0.403,0.403-1.058,0.403-1.46,0 c-0.403-0.403-0.403-1.057,0-1.46l4.932-4.932c0.211-0.211,0.488-0.306,0.764-0.296c0.275-0.01,0.553,0.085,0.764,0.296 l4.932,4.932c0.403,0.403,0.403,1.057,0,1.46S13.561,7.682,13.157,7.279z"/></svg>
                                                                </div>
                                                                <input type="file" name="form_bg_img" id="form_bg_img" data-val="form_bg" class="original" style="position: absolute; cursor: pointer; top: 0px; padding:0; margin:0; height:100%; width:100%; right:0; z-index: 100; opacity: 0; filter:alpha(opacity=0);" />
                                                            </div>


                                                            <input type="hidden" name="imagename_form" id="imagename_form" value="" />
                                                            <input type="hidden" name="arfmfbi" onclick="clear_file_submit();" value="" data-id="arfmainform_bg_img" />

                                                        <?php } ?>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>

                                        <?php
                                            //$newarr['arf_bg_position_y'] == "px";
                                            $arf_bg_position_style_x="";
                                            $arf_bg_position_height_style_x="";
                                            $arf_bg_position_style_y="";
                                            $arf_bg_position_height_style_y="";

                                            if((isset($newarr['arf_bg_position_x']) && $newarr['arf_bg_position_x']=='px') && (isset($newarr['arf_bg_position_input_x']) && $newarr['arf_bg_position_input_x']!='')){
                                                $arf_bg_position_style_x = "display: block;";
                                                $arf_bg_position_height_style_x = "arf_bg_position_active_height";
                                            } else{
                                                $arf_bg_position_style_x = "display: none;";
                                                $arf_bg_position_height_style_x = "arf_bg_position_inactive_height";
                                            }

                                            if((isset($newarr['arf_bg_position_y']) && $newarr['arf_bg_position_y']=='px') && (isset($newarr['arf_bg_position_input_y']) && $newarr['arf_bg_position_input_y']!='')){
                                                $arf_bg_position_style_y = "display: block;";
                                                $arf_bg_position_height_style_y = "arf_bg_position_active_height";
                                            } else{
                                                $arf_bg_position_style_y = "display: none;";
                                                $arf_bg_position_height_style_y = "arf_bg_position_inactive_height";
                                            }
                                        ?>

                                        <div class="arf_accordion_container_row arf_half_width <?php echo $arf_bg_position_height_style_x; ?>" style="margin-bottom: 30px;">
                                            <div style="width: 31% !important;" class="arf_accordion_inner_title arf_form_padding arf_two_row_text"><?php echo addslashes(esc_html__('Background Image Position-X', 'ARForms')); ?></div>
                                            <div class="arf_form_bg_position_container">
                                                <?php
                                                    $bg_position_selected_x=""; 
                                                    if(isset($newarr['arf_bg_position_x']) && $newarr['arf_bg_position_x']!=''){
                                                            $bg_position_selected_x = $newarr['arf_bg_position_x'];
                                                    } else{ 
                                                        $bg_position_selected_x = "left";
                                                    }
                                                ?>
                                                <div class="arf_dropdown_wrapper" style="width: 100%;">
                                                    <input id="arf_bg_position_x" name="arf_bg_position_x" value="<?php echo $bg_position_selected_x; ?>" type="hidden" onchange="update_form_bg_position(this, 'x', 'arf_form_bg_position_input_div_x', 'arf_fieldset_<?php echo $id; ?>');">
                                                    <dl class="arf_selectbox" data-name="arf_bg_position_x" data-id="arf_bg_position_x" style="width:100%;">
                                                        <dt><span><?php echo $bg_position_selected_x; ?></span>
                                                        <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                                        <dd>
                                                            <ul style="display: block;" data-id="arf_bg_position_x">
                                                                <li class="arf_selectbox_option" data-value="center" data-label="center">center</li>
                                                                <li class="arf_selectbox_option" data-value="left" data-label="left">left</li>
                                                                <li class="arf_selectbox_option" data-value="right" data-label="right">right</li>
                                                                <li class="arf_selectbox_option" data-value="px" data-label="px">px</li>
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                    <span class="arf_px arf_font_size" style="margin-left: 10px;position: relative;"><?php echo addslashes(esc_html__('X-axis', 'ARForms')); ?></span>
                                                </div>  
                                                  
                                            </div>

                                            <div class="arf_form_bg_position_input_container">
                                                <div class="arf_form_bg_position_input_div" id="arf_form_bg_position_input_div_x" style="margin-left:14px; margin-right: -5px;<?php echo $arf_bg_position_style_x; ?>">
                                                    <input type="text" name="arf_bg_position_input_x" id="arf_form_bg_position_input_x" value="<?php echo (isset($newarr['arf_bg_position_input_x']) && $newarr['arf_bg_position_input_x']!='') ? esc_attr($newarr['arf_bg_position_input_x']) : '' ; ?>" class="arf_form_bg_position_input" onfocusout="set_form_bg_position(this, 'x', 'arf_fieldset_<?php echo $id; ?>')">    
                                                    <span class="arf_px arf_font_size" style="margin-left: 10px;"><?php echo addslashes(esc_html__('X-axis', 'ARForms')); ?></span>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="arf_accordion_container_row arf_half_width <?php echo $arf_bg_position_height_style_y; ?>" style="margin-bottom: 30px;">
                                            <div style="width: 31% !important;" class="arf_accordion_inner_title arf_form_padding arf_two_row_text"><?php echo addslashes(esc_html__('Background Image Position-Y', 'ARForms')); ?></div>

                                            <div class="arf_form_bg_position_container">
                                            <?php
                                                $bg_position_selected_y=""; 
                                                if(isset($newarr['arf_bg_position_y']) && $newarr['arf_bg_position_y']!=''){
                                                        $bg_position_selected_y = $newarr['arf_bg_position_y'];
                                                } else{ 
                                                    $bg_position_selected_y = "top";
                                                }
                                            ?>
                                                <div class="arf_dropdown_wrapper" style="width: 100%;">
                                                    <input id="arf_bg_position_y" name="arf_bg_position_y" value="<?php echo $bg_position_selected_y; ?>" type="hidden" onchange="update_form_bg_position(this, 'y', 'arf_form_bg_position_input_div_y', 'arf_fieldset_<?php echo $id; ?>');">
                                                    <dl class="arf_selectbox" data-name="arf_bg_position_y" data-id="arf_bg_position_y" style="width:100%;">
                                                        <dt><span><?php echo $bg_position_selected_y; ?></span> 
                                                        <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                                        <dd>
                                                            <ul style="display: block;" data-id="arf_bg_position_y">
                                                                <li class="arf_selectbox_option" data-value="center" data-label="center">center</li>
                                                                <li class="arf_selectbox_option" data-value="top" data-label="top">top</li>
                                                                <li class="arf_selectbox_option" data-value="bottom" data-label="bottom">bottom</li>
                                                                <li class="arf_selectbox_option" data-value="px" data-label="px">px</li>
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                    <span class="arf_px arf_font_size" style="margin-left: 10px;position: relative;"><?php echo addslashes(esc_html__('Y-axis', 'ARForms')); ?></span>
                                                </div>
                                            </div>
                                            
                                            <div class="arf_form_bg_position_input_container">
                                                <div class="arf_form_bg_position_input_div" id="arf_form_bg_position_input_div_y" style="margin-left: 14px;margin-right: -5px;<?php echo $arf_bg_position_style_y; ?>">
                                                    <input type="text" name="arf_bg_position_input_y" id="arf_form_bg_position_input_y" value="<?php echo (isset($newarr['arf_bg_position_input_y'])&&$newarr['arf_bg_position_input_y']!='') ? esc_attr($newarr['arf_bg_position_input_y']) : '' ; ?>" class="arf_form_bg_position_input" onfocusout="set_form_bg_position(this, 'y', 'arf_fieldset_<?php echo $id; ?>')">    
                                                    <span class="arf_px arf_font_size" style="margin-left: 10px;"><?php echo addslashes(esc_html__('Y-axis', 'ARForms')); ?></span>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_form_padding arf_two_row_text"><?php echo addslashes(esc_html__('Form Padding', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_center arf_form_container">
                                                <div class="arf_form_padding_box_wrapper"><input type="text" name="arfmainfieldsetpadding_1" id="arfmainfieldsetpadding_1" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset~|~padding-top","material":".ar_main_div_{arf_form_id} .arf_fieldset~|~padding-top"}' value="<?php echo esc_attr($newarr['arfmainfieldsetpadding_1']); ?>" class="arf_form_padding_box" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_padding" /><br /><span class="arf_px arf_font_size" style="margin-left:-10px;"><?php echo addslashes(esc_html__('Top', 'ARForms')); ?></span></div>
                                                <div class="arf_form_padding_box_wrapper"><input type="text" name="arfmainfieldsetpadding_2" id="arfmainfieldsetpadding_2" value="<?php echo esc_attr($newarr['arfmainfieldsetpadding_2']); ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset~|~padding-right","material":".ar_main_div_{arf_form_id} .arf_fieldset~|~padding-right"}' class="arf_form_padding_box" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_padding" /><br /><span class="arf_px arf_font_size" style="margin-left:-10px;"><?php echo addslashes(esc_html__('Right', 'ARForms')); ?></span></div>
                                                <div class="arf_form_padding_box_wrapper"><input type="text" name="arfmainfieldsetpadding_3" id="arfmainfieldsetpadding_3" value="<?php echo esc_attr($newarr['arfmainfieldsetpadding_3']); ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset~|~padding-bottom","material":".ar_main_div_{arf_form_id} .arf_fieldset~|~padding-bottom"}' class="arf_form_padding_box" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_padding" /><br /><span class="arf_px arf_font_size" style="margin-left:-10px;"><?php echo addslashes(esc_html__('Bottom', 'ARForms')); ?></span></div>
                                                <div class="arf_form_padding_box_wrapper"><input type="text" name="arfmainfieldsetpadding_4" id="arfmainfieldsetpadding_4" value="<?php echo esc_attr($newarr['arfmainfieldsetpadding_4']); ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset~|~padding-left||.ar_main_div_{arf_form_id} .arf_inner_wrapper_sortable.arfmainformfield.ui-sortable-helper~|~left","material":".ar_main_div_{arf_form_id} .arf_fieldset~|~padding-left||.ar_main_div_{arf_form_id} .arf_inner_wrapper_sortable.arfmainformfield.ui-sortable-helper~|~left"}' class="arf_form_padding_box" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_padding"data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_padding" /><br /><span class="arf_px arf_font_size" style="margin-left:-10px;"><?php echo addslashes(esc_html__('Left', 'ARForms')); ?></span></div>
                                                <?php
                                                $arfmainfieldsetpadding_value = '';

                                                if (esc_attr($newarr['arfmainfieldsetpadding_1']) != '') {
                                                    $arfmainfieldsetpadding_value .= $newarr['arfmainfieldsetpadding_1'] . 'px ';
                                                } else {
                                                    $arfmainfieldsetpadding_value .= '0px ';
                                                }
                                                if (esc_attr($newarr['arfmainfieldsetpadding_2']) != '') {
                                                    $arfmainfieldsetpadding_value .= $newarr['arfmainfieldsetpadding_2'] . 'px ';
                                                } else {
                                                    $arfmainfieldsetpadding_value .= '0px ';
                                                }
                                                if (esc_attr($newarr['arfmainfieldsetpadding_3']) != '') {
                                                    $arfmainfieldsetpadding_value .= $newarr['arfmainfieldsetpadding_3'] . 'px ';
                                                } else {
                                                    $arfmainfieldsetpadding_value .= '0px ';
                                                }
                                                if (esc_attr($newarr['arfmainfieldsetpadding_4']) != '') {
                                                    $arfmainfieldsetpadding_value .= $newarr['arfmainfieldsetpadding_4'] . 'px';
                                                } else {
                                                    $arfmainfieldsetpadding_value .= '0px';
                                                }
                                                ?>
                                                <input type="hidden" name="arfmfsp" style="width:160px;" id="arfmainfieldsetpadding" class="txtxbox_widget arf_float_right" value="<?php echo $arfmainfieldsetpadding_value; ?>" size="4" />
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_form_padding arf_two_row_text"><?php echo addslashes(esc_html__('Section Padding', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_center arf_form_container">
                                                <div class="arf_section_padding_box_wrapper"><input type="text" name="arfsectionpaddingsetting_1" id="arfsectionpaddingsetting_1" onchange="arf_change_field_padding('arfsectionpaddingsetting');" value="<?php echo esc_attr($newarr['arfsectionpaddingsetting_1']); ?>" class="arf_section_padding_box" /><br /><span class="arf_px arf_font_size" style="margin-left:-10px;"><?php echo addslashes(esc_html__('Top', 'ARForms')); ?></span></div>
                                                <div class="arf_section_padding_box_wrapper"><input type="text" name="arfsectionpaddingsetting_2" id="arfsectionpaddingsetting_2" value="<?php echo esc_attr($newarr['arfsectionpaddingsetting_2']); ?>" onchange="arf_change_field_padding('arfsectionpaddingsetting');" class="arf_section_padding_box"/><br /><span class="arf_px arf_font_size" style="margin-left:-10px;"><?php echo addslashes(esc_html__('Right', 'ARForms')); ?></span></div>
                                                <div class="arf_section_padding_box_wrapper"><input type="text" name="arfsectionpaddingsetting_3" id="arfsectionpaddingsetting_3" value="<?php echo esc_attr($newarr['arfsectionpaddingsetting_3']); ?>" onchange="arf_change_field_padding('arfsectionpaddingsetting');" class="arf_section_padding_box"/><br /><span class="arf_px arf_font_size" style="margin-left:-10px;"><?php echo addslashes(esc_html__('Bottom', 'ARForms')); ?></span></div>
                                                <div class="arf_section_padding_box_wrapper"><input type="text" name="arfsectionpaddingsetting_4" id="arfsectionpaddingsetting_4" value="<?php echo esc_attr($newarr['arfsectionpaddingsetting_4']); ?>" onchange="arf_change_field_padding('arfsectionpaddingsetting');" class="arf_section_padding_box" /><br /><span class="arf_px arf_font_size" style="margin-left:-10px;"><?php echo addslashes(esc_html__('Left', 'ARForms')); ?></span></div>
                                                <?php
                                                $arfsectionpaddingsetting_value = '';

                                                if (esc_attr($newarr['arfsectionpaddingsetting_1']) != '')
                                                    $arfsectionpaddingsetting_value .= $newarr['arfsectionpaddingsetting_1'] . 'px ';
                                                else
                                                    $arfsectionpaddingsetting_value .= '15px ';

                                                if (esc_attr($newarr['arfsectionpaddingsetting_2']) != '')
                                                    $arfsectionpaddingsetting_value .= $newarr['arfsectionpaddingsetting_2'] . 'px ';
                                                else
                                                    $arfsectionpaddingsetting_value .= '15px ';

                                                if (esc_attr($newarr['arfsectionpaddingsetting_3']) != '')
                                                    $arfsectionpaddingsetting_value .= $newarr['arfsectionpaddingsetting_3'] . 'px ';
                                                else
                                                    $arfsectionpaddingsetting_value .= '15px ';

                                                if (esc_attr($newarr['arfsectionpaddingsetting_4']) != '')
                                                    $arfsectionpaddingsetting_value .= $newarr['arfsectionpaddingsetting_4'] . 'px';
                                                else
                                                    $arfsectionpaddingsetting_value .= '15px';
                                                ?>
                                                <input type="hidden" name="arfscps" style="width:100px;" id="arfsectionpaddingsetting" class="txtxbox_widget" value="<?php echo $arfsectionpaddingsetting_value; ?>" />
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Form Border', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Border Type', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right arf_right">
                                                <div class="arf_toggle_button_group arf_two_button_group" style="margin-right:5px;">
                                                    <?php $newarr['form_border_shadow'] = isset($newarr['form_border_shadow']) ? $newarr['form_border_shadow'] : 'shadow'; ?>
                                                    <label class="arf_flat_border_btn arf_toggle_btn <?php echo ($newarr['form_border_shadow'] == 'flat') ? 'arf_success' : ''; ?>" style="padding:7px 20px;"><input type="radio" name="arffbs" class="visuallyhidden" value="flat" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset~|~box-shadow-none","material":".ar_main_div_{arf_form_id} .arf_fieldset~|~box-shadow-none"}'  id="arfmainformbordershadow2" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_border_type" <?php checked($newarr['form_border_shadow'], 'flat'); ?> /><?php echo addslashes(esc_html__('Flat', 'ARForms')); ?></label>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['form_border_shadow'] == 'shadow') ? 'arf_success' : ''; ?>"><input type="radio" name="arffbs" class="visuallyhidden" id="arfmainformbordershadow1" value="shadow" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset~|~box-shadow","material":".ar_main_div_{arf_form_id} .arf_fieldset~|~box-shadow"}' <?php checked($newarr['form_border_shadow'], 'shadow'); ?> data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_border_type" /><?php echo addslashes(esc_html__('Shadow', 'ARForms')); ?></label>
                                                </div>
                                            </div>
                                        </div>                                        
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text"><?php echo addslashes(esc_html__('Border Size', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_center" style="margin-left: -5px;">
                                                <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '8') { ?>
                                                    <div class="arf_float_right">
                                                        <input type="text" name="arfmfis" style="width:142px;" class="txtxbox_widget"  id="arfmainfieldset" value="<?php echo esc_attr($newarr['fieldset']) ?>" size="4" />&nbsp;<span class="arf_px"><?php echo addslashes(esc_html__('px', 'ARForms')) ?></span>
                                                    </div>
                                                <?php } else { ?>
                                                    <div class="arf_slider_wrapper">
                                                        <input id="arfmainfieldset_exs" class="arf_slider" data-slider-id='arfmainfieldset_exsSlider' type="text" data-slider-min="0" data-slider-max="50" data-slider-step="1" data-slider-value="<?php echo esc_attr($newarr['fieldset']) ?>" />
                                                        <div class="arf_slider_unit_data">
                                                            <div style="float:left;"><?php echo addslashes(esc_html__('0 px', 'ARForms')) ?></div>
                                                            <div style="float:right;"><?php echo addslashes(esc_html__('50 px', 'ARForms')) ?></div>
                                                        </div>

                                                        <input type="hidden" name="arfmfis" style="width:100px;" class="txtxbox_widget"  id="arfmainfieldset" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset~|~border-width","material":".ar_main_div_{arf_form_id} .arf_fieldset~|~border-width"}' value="<?php echo esc_attr($newarr['fieldset']) ?>" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_border_width" size="4" />
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text"><?php echo addslashes(esc_html__('Border Radius', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_center" style="margin-left: -5px;">
                                                <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '8') { ?>
                                                    <div class="arf_float_right">
                                                        <input type="text" name="arfmfsr" style="width:142px;" class="txtxbox_widget"  id="arfmainfieldsetradius" value="<?php echo esc_attr($newarr['arfmainfieldsetradius']) ?>" size="4" />&nbsp;<span class="arf_px"><?php echo addslashes(esc_html__('px', 'ARForms')); ?></span>
                                                    </div>
                                                <?php } else { ?>
                                                    <div class="arf_slider_wrapper">
                                                        <input id="arfmainfieldsetradius_exs" class="arf_slider" data-slider-id='arfmainfieldsetradius_exsSlider' type="text" data-slider-min="0" data-slider-max="100" data-slider-step="1" data-slider-value="<?php echo esc_attr($newarr['arfmainfieldsetradius']) ?>" />
                                                        <div class="arf_slider_unit_data">
                                                            <div style="float:left;"><?php echo addslashes(esc_html__('0 px', 'ARForms')); ?></div>
                                                            <div style="float:right;"><?php echo addslashes(esc_html__('100 px', 'ARForms')); ?></div>
                                                        </div>

                                                        <input type="hidden" name="arfmfsr" style="width:100px;" class="txtxbox_widget"  id="arfmainfieldsetradius" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset~|~border-radius","material":".ar_main_div_{arf_form_id} .arf_fieldset~|~border-radius"}' value="<?php echo esc_attr($newarr['arfmainfieldsetradius']) ?>" size="4" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_border_radius" />
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>

                                        
                                        

                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Window Opacity', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text"><?php echo addslashes(esc_html__('Window Opacity', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_center" style="margin-left: -5px;">
                                                <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '8') { ?>
                                                    <div class="arf_float_right" style="margin-right:5px;">
                                                        <input type="text" name="arfmainform_opacity" id="arfmainform_opacity" class="txtxbox_widget" value="<?php echo esc_attr($newarr['arfmainform_opacity']) ?>" style="width:142px;" />
                                                    </div>
                                                <?php } else { ?>
                                                    <div class="arf_slider_wrapper">
                                                        <input id="arfmainform_opacity_exs" class="arf_slider" data-slider-id='arfmainform_opacity_exsSlider' type="text" data-slider-min="0" data-slider-max="10" data-slider-step="1" data-slider-value="<?php echo ( esc_attr($newarr['arfmainform_opacity']) * 10 ) ?>"  />
                                                        <div class="arf_slider_unit_data">
                                                            <div style="float:left;"><?php echo addslashes(esc_html__('0', 'ARForms')); ?></div>
                                                            <div style="float:right;"><?php echo addslashes(esc_html__('1', 'ARForms')); ?></div>
                                                        </div>
                                                        <input type="hidden" name="arfmainform_opacity" id="arfmainform_opacity" class="txtxbox_widget" value="<?php echo esc_attr($newarr['arfmainform_opacity']) ?>" style="width:100px;" />
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>

                                    </div>
                                </dd>
                            </dl>
                            <dl class="arf_accordion_tab_input_settings">
                                <dd>
                                    <a href="javascript:void(0)" data-target="arf_accordion_tab_input_settings"><?php echo esc_html__('Input field Options', 'ARForms'); ?></a>
                                    <div class="arf_accordion_container">
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo esc_html__('Label Options', 'ARForms'); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_width_50"><?php echo addslashes(esc_html__('Label Position', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right arf_width_50 arfhieght35 arf_right">
                                                <?php
                                                    $newarr['position'] = isset($newarr['position']) ? $newarr['position'] : 'top';
                                                    $disable_label_position = '';
                                                    $checked_right = checked($newarr['position'],'right',false);
                                                    $checked_left = checked($newarr['position'],'left',false);
                                                    $checked_top = checked($newarr['position'],'top',false);
                                                    $disabled_right = $disabled_left = "";
                                                    if( $newarr['arfinputstyle'] == 'material' ){
                                                        $disable_label_position = 'disabled="disabled"';
                                                        $disabled_right = $disabled_left = "arf_disabled_toggle_button";
                                                    } else {
                                                        $disable_label_position = '';
                                                        $disabled_right = $disabled_left = "";
                                                    }
                                                ?>
                                                <div class="arf_toggle_button_group arf_three_button_group" style="margin-right:5px;">
                                                    <label class="arf_toggle_btn arf_label_position arf_right_position <?php echo ($checked_right != '') ? 'arf_success' : ''; echo $disabled_right; ?>" style="padding: 7px 10px;"><input type="radio" name="arfmps" class="visuallyhidden" onchange="frmSetPosClass('right');" <?php echo $disable_label_position; ?> value="right" <?php echo $checked_right; ?> /><?php echo addslashes(esc_html__('Right', 'ARForms')); ?></label>
                                                    <label class="arf_toggle_btn arf_label_position arf_left_position <?php echo ($checked_left != '') ? 'arf_success' : ''; echo $disabled_left; ?>" style="padding: 7px 10px;"><input type="radio" name="arfmps" class="visuallyhidden" onchange="frmSetPosClass('left');" <?php echo $disable_label_position; ?> value="left" <?php echo $checked_left; ?> /><?php echo addslashes(esc_html__('Left', 'ARForms')); ?></label>
                                                    <label class="arf_toggle_btn arf_label_position  arf_top_position <?php echo ($checked_top != '') ? 'arf_success' : ''; ?>" style="padding: 7px 10px;"><input type="radio" name="arfmps" class="visuallyhidden" onchange="frmSetPosClass('top');" value="top" <?php echo $checked_top; ?> /><?php echo addslashes(esc_html__('Top', 'ARForms')); ?></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_width_50"><?php echo addslashes(esc_html__('Label Align', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right arf_width_50 arf_right">
                                                <div class="arf_toggle_button_group arf_two_button_group" style="margin-right:5px;">
                                                    <?php $newarr['align'] = isset($newarr['align']) ? $newarr['align'] : 'right'; 
                                                    ?>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['align'] == 'right') ? 'arf_success' : ''; ?>" style="padding: 7px 12px;"><input type="radio" name="arffrma" id="frm_align" class="visuallyhidden" value="right" <?php checked($newarr['align'], 'right'); ?> data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} label.arf_main_label~|~text-align||.ar_main_div_{arf_form_id} .sortable_inner_wrapper .arfformfield .fieldname~|~text-align","material":".ar_main_div_{arf_form_id} .arf_materialize_form  label.arf_main_label~|~text-align||.ar_main_div_{arf_form_id} .sortable_inner_wrapper .arfformfield .fieldname~|~text-align||.arf_materialize_form .input-field label.arf_main_label:not(.arf_smiley_btn):not(.arf_star_rating_label):not(.arf_dislike_btn):not(.arf_like_btn):not(.arf_like_btn):not(.arf_field_option_content_cell_label):not(.arf_js_switch_label)~|~arf_set_right_position||.arf_materialize_form .input-field label.arf_main_label:not(.arf_smiley_btn):not(.arf_star_rating_label):not(.arf_dislike_btn):not(.arf_like_btn):not(.arf_like_btn):not(.arf_field_option_content_cell_label):not(.arf_js_switch_label)~|~arf_set_right_position_inherit"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_label_text_align"  /><?php echo addslashes(esc_html__('Right', 'ARForms')); ?></label>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['align'] == 'left') ? 'arf_success' : ''; ?>" style="padding: 7px 16px;"><input type="radio" name="arffrma" id="frm_align_2" class="visuallyhidden" value="left" <?php checked($newarr['align'], 'left'); ?> data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} label.arf_main_label~|~text-align||.ar_main_div_{arf_form_id} .sortable_inner_wrapper .arfformfield .fieldname~|~text-align","material":".ar_main_div_{arf_form_id} .arf_materialize_form label.arf_main_label~|~text-align||.ar_main_div_{arf_form_id} .sortable_inner_wrapper .arfformfield .fieldname~|~text-align||.arf_materialize_form .input-field label.arf_main_label:not(.arf_smiley_btn):not(.arf_star_rating_label):not(.arf_dislike_btn):not(.arf_like_btn):not(.arf_like_btn):not(.arf_field_option_content_cell_label):not(.arf_js_switch_label)~|~arf_set_left_position||.arf_materialize_form .input-field label.arf_main_label:not(.arf_smiley_btn):not(.arf_star_rating_label):not(.arf_dislike_btn):not(.arf_like_btn):not(.arf_like_btn):not(.arf_field_option_content_cell_label):not(.arf_js_switch_label)~|~arf_set_left_position_inherit"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_label_text_align" /><?php echo addslashes(esc_html__('Left', 'ARForms')); ?></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text arf_width_50"><?php echo esc_html__('Label Width', 'ARForms'); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right arf_width_50 arf_right">
                                                <span class="arfpxspan arffieldwidthpx">px</span>
                                                <input type="text" name="arfmws" class="arf_small_width_txtbox arfcolor arffieldwidthinput" id="arfmainformwidthsetting" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset label.arf_main_label~|~width","material":".ar_main_div_{arf_form_id} .arf_fieldset label.arf_main_label~|~width"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_label_width" value="<?php echo esc_attr($newarr["width"]) ?>" size="5" />
                                                <input type="hidden" name="arfmwu" id="arfmainwidthunit" value="px"  <?php echo($newarr['position'] == 'top')?'disabled="disabled"':'';?>/>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text"><?php echo esc_html__('Hide Label', 'ARForms'); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right">
                                                <div class="arf_float_right" style="margin-right:5px;">
                                                    <label class="arf_js_switch_label">
                                                        <span class=""><?php echo addslashes(esc_html__('No', 'ARForms')); ?>&nbsp;</span>
                                                    </label>
                                                    <span class="arf_js_switch_wrapper">
                                                        <input type="checkbox" class="js-switch" name="arfhl" id="arfhidelabels" value="<?php echo $newarr['hide_labels'] != "" ? $newarr['hide_labels'] : 0; ?>" onchange="frmSetPosClassHide()"  <?php echo ($newarr['hide_labels'] == '1') ? 'checked="checked"' : ""; ?> />
                                                        <span class="arf_js_switch"></span>
                                                    </span>
                                                    <label class="arf_js_switch_label">
                                                        <span class="">&nbsp;<?php echo addslashes(esc_html__('Yes', 'ARForms')); ?></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Input Field Description Options', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_width_50"><?php echo addslashes(esc_html__('Font Size', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_width_50 arf_right">
                                                <div class="arf_dropdown_wrapper" style="margin-right: 5px;">
                                                    <input id="arfdescfontsizesetting" name="arfdfss" value="<?php echo $newarr['arfdescfontsizesetting']; ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_description~|~font-size||.ar_main_div_{arf_form_id} .arftitlediv .arfeditorformdescription input~|~font-size","material":".ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_description~|~font-size||.ar_main_div_{arf_form_id} .arftitlediv .arfeditorformdescription input~|~font-size"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_field_description_font_size" type="hidden" >
                                                    <dl class="arf_selectbox" data-name="arfdfss" data-id="arfdescfontsizesetting" style="width:60px;">
                                                        <dt><span><?php echo $newarr['arfdescfontsizesetting']; ?></span>
                                                        <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                                        <dd>
                                                            <ul style="display: none;" data-id="arfdescfontsizesetting">
                                                                <?php for ($i = 8; $i <= 20; $i ++) { ?>
                                                                    <li class="arf_selectbox_option" data-value="<?php echo $i ?>" data-label="<?php echo $i ?>"><?php echo addslashes(esc_html__($i, 'ARForms')); ?></li>
                                                                <?php } ?>
                                                                <?php for ($i = 22; $i <= 28; $i = $i + 2) { ?>
                                                                    <li class="arf_selectbox_option" data-value="<?php echo $i ?>" data-label="<?php echo $i ?>"><?php echo addslashes(esc_html__($i, 'ARForms')); ?></li>
                                                                <?php } ?>
                                                                <?php for ($i = 32; $i <= 40; $i = $i + 4) { ?>
                                                                    <li class="arf_selectbox_option" data-value="<?php echo $i ?>" data-label="<?php echo $i ?>"><?php echo addslashes(esc_html__($i, 'ARForms')); ?></li>
                                                                <?php } ?>
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_width_50"><?php echo addslashes(esc_html__('Text Alignment', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_width_50 arf_right">
                                                <div class="toggle-btn-grp joint-toggle arffieldtextalignment">
                                                    <label onclick="" class="toggle-btn arf_three_button right <?php
                                                    if ($newarr['arfdescalighsetting'] == "right") {
                                                        echo "success";
                                                    }
                                                    ?>" style="float:right;margin: 5px 0px  !important;"><input type="radio" name="arfdas" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_description~|~text-align","material":".ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_description~|~text-align"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_field_description_align" class="visuallyhidden" value="right" <?php checked($newarr['arfdescalighsetting'], 'right'); ?> /><svg width="24px" height="29px" viewBox="3 0 23 27"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#BCC9E0" d="M12.089,24.783v-3h14.125v3H12.089z M12.089,7.783h14.063v3H12.089  V7.783z M1.089,0.784h24.938v2.999H1.089V0.784z M26.027,17.783H1.089v-2.999h24.938V17.783z"/></svg></label>
                                                    <label onclick="" class="toggle-btn arf_three_button center <?php
                                                    if ($newarr['arfdescalighsetting'] == "center") {
                                                        echo "success";
                                                    }
                                                    ?>" style="float:right;"><input type="radio" name="arfdas"  class="visuallyhidden" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_description~|~text-align","material":".ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_description~|~text-align"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_field_description_align" value="center" <?php checked($newarr['arfdescalighsetting'], 'center'); ?> /><svg width="24px" height="29px" viewBox="3 0 23 27"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#BCC9E0" d="M1.089,17.783v-2.999h24.938v2.999H1.089z M6.089,10.783v-3h14.063  v3H6.089z M1.089,0.784h24.938v2.999H1.089V0.784z M20.214,24.783H6.089v-3h14.125V24.783z"/></svg></label>
                                                    <label onclick="" class="toggle-btn arf_three_button left <?php
                                                    if ($newarr['arfdescalighsetting'] == "left") {
                                                        echo "success";
                                                    }
                                                    ?>" style="float:right;"><input type="radio" name="arfdas" class="visuallyhidden" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_description~|~text-align","material":".ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_description~|~text-align"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_field_description_align" value="left" <?php checked($newarr['arfdescalighsetting'], 'left'); ?> /><svg width="24px" height="29px" viewBox="3 0 23 27"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#BCC9E0" d="M1.089,17.783v-2.999h24.938v2.999H1.089z M1.089,0.784h24.938  v2.999H1.089V0.784z M15.152,10.783H1.089v-3h14.063V10.783z M15.214,24.783H1.089v-3h14.125V24.783z"/></svg></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Input Field Options', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Field Width', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container" style="margin-left: -6px;">
                                                <div class="arf_dropdown_wrapper">
                                                    <input id="arffieldunit" name="arffiu" value="<?php echo $newarr['field_width_unit']; ?>" type="hidden" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .controls~|~arf_field_width_unit","material":".ar_main_div_{arf_form_id} .controls~|~arf_field_width_unit"}'  data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_field_width" >
                                                    <dl class="arf_selectbox" data-name="arffiu" data-id="arffieldunit" style="width:50px;">
                                                        <dt><span><?php echo $newarr['field_width_unit']; ?></span>
                                                        <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                                        <dd>
                                                            <ul style="display: none;" data-id="arffieldunit">
                                                                <li class="arf_selectbox_option" data-value="<?php echo addslashes(esc_html__('px', 'ARForms')); ?>" data-label="<?php echo addslashes(esc_html__('px', 'ARForms')); ?>"><?php echo addslashes(esc_html__('px', 'ARForms')); ?></li>
                                                                <li class="arf_selectbox_option" data-value="<?php echo addslashes(esc_html__('%', 'ARForms')); ?>" data-label="<?php echo addslashes(esc_html__('%', 'ARForms')); ?>"><?php echo addslashes(esc_html__('%', 'ARForms')) ?></li>
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                </div>
                                                <input type="text" name="arfmfiws" id="arfmainfieldwidthsetting" class="arf_small_width_txtbox arfcolor" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .controls~|~width{arf_field_width_unit}","material":".ar_main_div_{arf_form_id} .controls~|~width{arf_field_width_unit}"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_field_width" value="<?php echo esc_attr($newarr['field_width']) ?>"  size="5" />
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width" style="height: auto;">
                                            <div class="arf_accordion_inner_title arf_two_row_text"><?php echo addslashes(esc_html__('Text Direction', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container" >
                                                <div class="toggle-btn-grp joint-toggle arf_right arffielddirrection" >
                                                    <label onclick="" class="toggle-btn arf_four_button left text_direction <?php
                                                    if ($newarr['text_direction'] == "1") {
                                                        echo "success";
                                                    }
                                                    ?>" style="font-size:10px !important;padding-top: 5px !important;height:33px;"><input type="radio" name="arftds" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)~|~direction||.ar_main_div_{arf_form_id} input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)~|~direction||.ar_main_div_{arf_form_id} .arfdropdown-menu > li > a~|~text-align||.ar_main_div_{arf_form_id} .bootstrap-select.btn-group .arfbtn .filter-option~|~text-align||.ar_main_div_{arf_form_id} .autocomplete-content li span, .ar_main_div_{arf_form_id} .autocomplete-content li~|~text-align||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=password]~|~direction||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=email]~|~direction||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=number]~|~direction||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=url]~|~direction||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=tel]~|~direction||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~direction||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~direction","material":".ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)~|~direction||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)~|~direction||.ar_main_div_{arf_form_id} .arf_materialize_form .arf-select-dropdown li span~|~text-align||.ar_main_div_{arf_form_id} .arf_materialize_form .autocomplete-content li span, .ar_main_div_{arf_form_id} .arf_materialize_form .autocomplete-content li~|~text-align||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_fieldset .controls input[type=password]~|~direction||.ar_main_div_{arf_form_id}  .arf_materialize_form .arf_fieldset .controls input[type=email]~|~direction||.ar_main_div_{arf_form_id}  .arf_materialize_form .arf_fieldset .controls input[type=number]~|~direction||.ar_main_div_{arf_form_id}  .arf_materialize_form .arf_fieldset .controls input[type=url]~|~direction||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_fieldset .controls input[type=tel]~|~direction||.ar_main_div_{arf_form_id} .arf_materialize_form textarea:not(.html_field_description)~|~direction||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_fieldset .controls select~|~direction"}' class="visuallyhidden" id="txt_dir_1" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_input_text_direction" value="1" <?php checked($newarr['text_direction'], 1); ?> /><svg width="25px" height="29px" viewBox="0 0 30 30"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#bcc9e0" d="M1.131,19.305h2V0.43h-2V19.305z M26.631,9.867l-7.5-5v3.5H5.06v3h14.071v3.5    L26.631,9.867z" /></svg></label><label onclick="" class="toggle-btn arf_four_button right text_direction <?php
                                                           if ($newarr['text_direction'] == "0") {
                                                               echo "success";
                                                           }
                                                           ?>" style="font-size:10px !important;padding-top: 5px !important;height:33px;"><input type="radio" name="arftds" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)~|~direction||.ar_main_div_{arf_form_id} input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)~|~direction||.ar_main_div_{arf_form_id} .arfdropdown-menu > li > a~|~text-align||.ar_main_div_{arf_form_id} .bootstrap-select.btn-group .arfbtn .filter-option~|~text-align||.ar_main_div_{arf_form_id} .autocomplete-content li span, .ar_main_div_{arf_form_id} .autocomplete-content li~|~text-align||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=password]~|~direction||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=email]~|~direction||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=number]~|~direction||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=url]~|~direction||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=tel]~|~direction||~|~direction||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~direction||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~direction","material":".ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)~|~direction||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)~|~direction||.ar_main_div_{arf_form_id} .arf_materialize_form .arf-select-dropdown li span~|~text-align||.ar_main_div_{arf_form_id} .arf_materialize_form .autocomplete-content li span, .ar_main_div_{arf_form_id} .arf_materialize_form .autocomplete-content li~|~text-align||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_fieldset .controls input[type=password]~|~direction||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_fieldset .controls input[type=email]~|~direction||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_fieldset .controls input[type=number]~|~direction||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_fieldset .controls input[type=url]~|~direction||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_fieldset .controls input[type=tel]~|~direction||.ar_main_div_{arf_form_id} .arf_materialize_form textarea:not(.html_field_description)~|~direction||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_fieldset .controls select~|~direction"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_input_text_direction" class="visuallyhidden" value="0"  id="txt_dir_2" <?php checked($newarr['text_direction'], 0); ?> /><svg width="25px" height="29px" viewBox="0 0 30 30"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" fill="#bcc9e0" clip-rule="evenodd" d="M23.881,0.43v18.875h2V0.43H23.881z M8.819,4.867l-7.938,5l7.938,5v-3.5H21.89    v-3H8.819V4.867z"/></svg></label><br>
                                                    <span class="arf_px arf_font_size arfinputfielddirectionltr"><?php echo addslashes(esc_html__('LTR', 'ARForms')); ?></span>
                                                    <span class="arf_px arf_font_size arfinputfielddirectionrtl"><?php echo addslashes(esc_html__('RTL', 'ARForms')); ?></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text"><?php echo addslashes(esc_html__('Field Transparency', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container">
                                                <div class="arf_float_right" style="margin-right:4px;">
                                                    <label class="arf_js_switch_label">
                                                        <span><?php echo addslashes(esc_html__('No', 'ARForms')); ?>&nbsp;</span>
                                                    </label>
                                                    <span class="arf_js_switch_wrapper">
                                                        <input type="checkbox" class="js-switch chkstanard <?php echo ($newarr['arfinputstyle'] == 'material') ? 'arfcursornotallow' : ''; ?>" name="arfmfo" id="arfmainfield_opacity" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=password]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=email]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=number]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=url]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=tel]~|~field_transparency||.ar_main_div_{arf_form_id} .controls textarea~|~field_transparency||.ar_main_div_{arf_form_id} .controls select~|~field_transparency||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~field_transparency||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group.open .arfdropdown-menu~|~field_transparency||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group.open .arfbtn.dropdown-toggle~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=text]:focus:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~field_transparency_focus||.ar_main_div_{arf_form_id} .controls input[type=password]:focus~|~field_transparency_focus||.ar_main_div_{arf_form_id} .controls input[type=email]:focus~|~field_transparency_focus||.ar_main_div_{arf_form_id} .controls input[type=number]:focus~|~field_transparency_focus||.ar_main_div_{arf_form_id} .controls input[type=url]:focus~|~field_transparency_focus||.ar_main_div_{arf_form_id} .controls input[type=tel]:focus~|~field_transparency_focus||.ar_main_div_{arf_form_id} .arfmainformfield .controls textarea:focus~|~field_transparency_focus||.ar_main_div_{arf_form_id} .controls select:focus~|~field_transparency_focus||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle:focus~|~field_transparency_focus||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group.open .arfdropdown-menu:focus~|~field_transparency_focus||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group.open .arfbtn.dropdown-toggle:focus~|~field_transparency_focus","material":".ar_main_div_{arf_form_id} .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=password]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=email]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=number]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=url]~|~field_transparency||.ar_main_div_{arf_form_id} .controls input[type=tel]~|~field_transparency||.ar_main_div_{arf_form_id} .controls textarea~|~field_transparency||.ar_main_div_{arf_form_id} .controls select~|~field_transparency"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_field_transparency" value="1" <?php echo ($newarr['arfmainfield_opacity'] == 1) ? 'checked="checked"' : ""; ?> <?php echo ($newarr['arfinputstyle'] == 'material') ? 'disabled="disabled"' : ""; ?> />
                                                        <span class="arf_js_switch"></span>
                                                    </span>
                                                    <label class="arf_js_switch_label">
                                                        <span>&nbsp;<?php echo addslashes(esc_html__('Yes', 'ARForms')); ?></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text"><?php echo esc_html__('Hide Required Indicator', 'ARForms'); ?></div>
                                            
                                                
                                                <div class="arf_accordion_content_container">
                                                    <div class="arf_float_right" style="margin-right:5px;">
                                                        <label class="arf_js_switch_label">
                                                            <span class=""><?php echo addslashes(esc_html__('No', 'ARForms')); ?>&nbsp;</span>
                                                        </label>
                                                        <span class="arf_js_switch_wrapper">
                                                           <input type="checkbox" class="js-switch chkstanard" name="arfrinc" id="arfreq_inc" data-arfstyle="true" data-arfstyledata='{"standard":".arf_main_label span.arf_edit_in_place+span~|~req_indicator","material":".arf_main_label span.arf_edit_in_place+span~|~req_indicator"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_arfreq_inc" value="1" <?php echo (isset($newarr['arf_req_indicator']) && $newarr['arf_req_indicator'] == 1) ? 'checked="checked"' : ""; ?> />
                                                            <span class="arf_js_switch"></span>
                                                        </span>
                                                        <label class="arf_js_switch_label">
                                                            <span class="">&nbsp;<?php echo addslashes(esc_html__('Yes', 'ARForms')); ?></span>
                                                        </label>
                                                    </div>
                                                </div>
                                        </div>

                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text arf_width_50"><?php echo addslashes(esc_html__('Space Between Two Fields', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_width_50 arf_right">
                                                <input type="text" name="arffms" id="arffieldmarginsetting" class="arf_small_width_txtbox arfcolor" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} #new_fields .arfmainformfield.edit_form_item~|~field-margin-bottom","material":".ar_main_div_{arf_form_id} #new_fields .arfmainformfield.edit_form_item~|~field-margin-bottom"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_space_between_fields" value="<?php echo esc_attr($newarr['arffieldmarginssetting']) ?>"  size="5" />
                                            </div>
                                        </div>

                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text">
                                                <?php echo addslashes(esc_html__('Placeholder Opacity', 'ARForms')); ?>
                                            </div>
                                            <div class="arf_accordion_content_container arf_align_center" style="margin-left: -5px;">
                                                <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '8') { ?>
                                                <div class="arf_float_right" style="margin-right:5px;">
                                                    <input type="text" name="arfplaceholder_opacity" id="arfplaceholder_opacity" class="txtxbox_widget" value="<?php echo isset($newarr['arfplaceholder_opacity']) ? esc_attr($newarr['arfplaceholder_opacity']) : 0.5?>" style="width:142px;" />
                                                </div>
                                                <?php } else { ?>
                                                <div class="arf_slider_wrapper">
                                                    <input id="arfplaceholder_opacity_exs" class="arf_slider" data-slider-id='arfplaceholder_opacity_exsSlider' type="text" data-slider-min="0" data-slider-max="10" data-slider-step="1" data-slider-value="<?php echo isset($newarr['arfplaceholder_opacity']) ? (esc_attr($newarr['arfplaceholder_opacity']) * 10 ) : (0.5 * 10) ?>"  />
                                                    <div class="arf_slider_unit_data">
                                                        <div style="float:left;"><?php echo addslashes(esc_html__('0', 'ARForms')); ?></div>
                                                        <div style="float:right;"><?php echo addslashes(esc_html__('1', 'ARForms')); ?></div>
                                                    </div>
                                                    <input type="hidden" name="arfplaceholder_opacity" id="arfplaceholder_opacity" class="txtxbox_widget" value="<?php echo isset($newarr['arfplaceholder_opacity']) ? esc_attr($newarr['arfplaceholder_opacity']) : 0.5 ?>" data-arfstyle="true" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_arfplaceholder_opacity" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} input[type=text]:not(.arfslider):not(.arf_autocomplete):not(.arf_field_option_input_text):not(.inplace_field)::-webkit-input-placeholder~|~opacity||.wp-admin .allfields .controls .smaple-textarea::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .controls textarea::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=password]::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=number]::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=url]::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=tel]::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} select::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=text]:not(.arfslider):not(.arf_autocomplete):not(.arf_field_option_input_text):not(.inplace_field):-moz-placeholder~|~opacity||.wp-admin .allfields .controls .smaple-textarea:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .controls textarea:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=password]:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=number]:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=url]:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=tel]:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} select:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=text]:not(.arfslider):not(.arf_autocomplete):not(.arf_field_option_input_text):not(.inplace_field)::-moz-placeholder~|~opacity||.wp-admin .allfields .controls .smaple-textarea::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .controls textarea::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=password]::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=number]::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=url]::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=tel]::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} select::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=text]:not(.arfslider):not(.arf_autocomplete):not(.arf_field_option_input_text):not(.inplace_field):-ms-input-placeholder~|~opacity||.wp-admin .allfields .controls .smaple-textarea:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .controls textarea:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=password]:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=number]:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=url]:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=tel]:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} select:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=text]:not(.arfslider):not(.arf_autocomplete):not(.arf_field_option_input_text):not(.inplace_field)::-ms-input-placeholder~|~opacity||.wp-admin .allfields .controls .smaple-textarea::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .controls textarea::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=password]::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=number]::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=url]::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} input[type=tel]::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} select::-ms-input-placeholder~|~opacity","material":".ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form textarea:not(.html_field_description)::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=email]::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=password]::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=number]::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=url]::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=tel]::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form select::-webkit-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete):-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form textarea:not(.html_field_description):-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=email]:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=password]:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=number]:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=url]:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=tel]:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form select:-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form textarea:not(.html_field_description)::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=email]::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=password]::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=number]::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=url]::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=tel]::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form select::-moz-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete):-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form textarea:not(.html_field_description):-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=email]:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=password]:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=number]:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=url]:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=tel]:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form select:-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form textarea:not(.html_field_description)::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=email]::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=password]::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=number]::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=url]::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=tel]::-ms-input-placeholder~|~opacity||.ar_main_div_{arf_form_id} .arf_materialize_form select::-ms-input-placeholder~|~opacity"}' style="width:100px;" />
                                                </div>
                                                <?php } ?>
                                            </div>
                                        </div>

                                        <div class="arf_accordion_container_row arf_half_width" style="height: 70px;">
                                            <div class="arf_accordion_container_inner_div">
                                                <div class="arf_accordion_inner_title arf_width_50"><?php echo addslashes(esc_html__('Font Settings','ARForms')); ?></div>
                                            </div>
                                            
                                            <div class="arf_accordion_container_inner_div">
                                                <div class="arf_custom_font" data-id="arf_input_font_settings">
                                                    <div class="arf_custom_font_icon">
                                                        <svg viewBox="-10 -10 35 35">
                                                        <g id="paint_brush">
                                                        <path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M7.423,14.117c1.076,0,2.093,0.022,3.052,0.068v-0.82c-0.942-0.078-1.457-0.146-1.542-0.205  c-0.124-0.092-0.203-0.354-0.235-0.787s-0.049-1.601-0.049-3.504l0.059-6.568c0-0.299,0.013-0.472,0.039-0.518  C8.772,1.744,8.85,1.725,8.981,1.725c1.549,0,2.584,0.043,3.105,0.128c0.162,0.026,0.267,0.076,0.313,0.148  c0.059,0.092,0.117,0.687,0.176,1.784h0.811c0.052-1.201,0.14-2.249,0.264-3.145l-0.107-0.156c-2.396,0.098-4.561,0.146-6.494,0.146  c-1.94,0-3.936-0.049-5.986-0.146L0.954,0.563c0.078,0.901,0.11,1.976,0.098,3.223h0.84c0.085-1.062,0.141-1.633,0.166-1.714  C2.083,1.99,2.121,1.933,2.17,1.9c0.049-0.032,0.262-0.065,0.641-0.098c0.652-0.052,1.433-0.078,2.34-0.078  c0.443,0,0.674,0.024,0.69,0.073c0.016,0.049,0.024,1.364,0.024,3.947c0,1.313-0.01,2.602-0.029,3.863  c-0.033,1.776-0.072,2.804-0.117,3.084c-0.039,0.201-0.098,0.34-0.176,0.414c-0.078,0.075-0.212,0.129-0.4,0.161  c-0.404,0.065-0.791,0.098-1.162,0.098v0.82C4.861,14.14,6.008,14.117,7.423,14.117L7.423,14.117z"></path>
                                                        </g></svg>
                                                    </div>
                                                    <div class="arf_custom_font_label"><?php echo addslashes(esc_html__('Advanced font options','ARForms')); ?></div>
                                                </div>
                                            </div>
                                            
                                        </div>

                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Field inner spacing', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Vertical', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_center" style="margin-left: -5px;">
                                                <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '8') { ?>
                                                    <div class="arf_float_right">
                                                        <input id="arffieldinnermarginsetting_1" name="arffieldinnermarginsetting_1" class="txtxbox_widget" style="width:142px;" type="text" onchange="arf_change_field_spacing2();" value="<?php echo esc_attr($newarr['arffieldinnermarginssetting_1']) ?>" />&nbsp;<span class="arf_px"><?php echo addslashes(esc_html__('px', 'ARForms')) ?></span>
                                                    </div>
                                                <?php } else { ?>
                                                    <div class="arf_slider_wrapper">
                                                        <input id="arffieldinnermarginssetting_1_exs" class="arf_slider" data-slider-id='arffieldinnermarginssetting_1_exsSlider' type="text" data-slider-min="0" data-slider-max="25" data-slider-step="1" data-dvalue="<?php echo floatval($newarr['arffieldinnermarginssetting_1']); ?>" data-slider-value="<?php echo floatval($newarr['arffieldinnermarginssetting_1']) ?>" />
                                                        <input type="hidden" name="arffieldinnermarginsetting_1" id="arffieldinnermarginsetting_1" value="<?php echo floatval($newarr['arffieldinnermarginssetting_1']) ?>" />
                                                        <div class="arf_slider_unit_data">
                                                            <div class="arf_px" style="float:left;"><?php echo addslashes(esc_html__('0 px', 'ARForms')) ?></div>
                                                            <div class="arf_px" style="float:right;"><?php echo addslashes(esc_html__('25 px', 'ARForms')) ?></div>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Horizontal', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_center" style="margin-left: -5px;">
                                                <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '8') { ?>
                                                    <div class="arf_float_right">
                                                        <input id="arffieldinnermarginsetting_2" name="arffieldinnermarginsetting_2" class="txtxbox_widget" style="width:142px;" type="text" onchange="arf_change_field_spacing2();" value="<?php echo esc_attr($newarr['arffieldinnermarginssetting_2']) ?>" />&nbsp;<span class="arf_px"><?php echo addslashes(esc_html__('px', 'ARForms')) ?></span>
                                                    </div>
                                                <?php } else { ?>
                                                    <div class="arf_slider_wrapper">
                                                        <input id="arffieldinnermarginssetting_2_exs" class="arf_slider" data-slider-id='arffieldinnermarginssetting_2_exsSlider' type="text" data-slider-min="0" data-slider-max="25" data-slider-step="1" data-dvalue="<?php echo floatval($newarr['arffieldinnermarginssetting_2']); ?>" data-slider-value="<?php echo floatval($newarr['arffieldinnermarginssetting_2']); ?>" />
                                                        <input type="hidden" name="arffieldinnermarginsetting_2" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .sltstandard_front .arfbtn.dropdown-toggle .filter-option~|~left||.ar_main_div_{arf_form_id} .sltstandard_front .arfbtn.dropdown-toggle .filter-option~|~right","material":".ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arf_colorpicker):not(.arf_autocomplete):not(.arfslider)~|~padding-left||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arf_colorpicker):not(.arf_autocomplete):not(.arfslider)~|~padding-right||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"email\"]~|~padding-left||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"email\"]~|~padding-right||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arf_colorpicker):not(.arf_autocomplete):not(.arfslider)~|~padding-left||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arf_colorpicker):not(.arf_autocomplete):not(.arfslider)~|~padding-right||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"phone\"]~|~padding-left||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"phone\"]~|~padding-right||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"tel\"]~|~padding-left||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"tel\"]~|~padding-right||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"password\"]~|~padding-left||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"password\"]~|~padding-right||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"hidden\"]~|~padding-left||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"hidden\"]~|~padding-right||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"number\"]~|~padding-left||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"number\"]~|~padding-right||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"url\"]~|~padding-left||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"url\"]~|~padding-right||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls textarea~|~padding-left||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls textarea~|~padding-right||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)~|~padding-left||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)~|~padding-right||.ar_main_div_{arf_form_id} .arf_materialize_form .arf-select-dropdown li span~|~padding-left||.ar_main_div_{arf_form_id} .arf_materialize_form .arf-select-dropdown li span~|~padding-right"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_field_inner_spacing_for_dropdown" id="arffieldinnermarginsetting_2" value="<?php echo floatval($newarr['arffieldinnermarginssetting_2']); ?>" />
                                                        <div class="arf_slider_unit_data">
                                                            <div class="arf_px" style="float:left;" ><?php echo addslashes(esc_html__('0 px', 'ARForms')) ?></div>
                                                            <div class="arf_px" style="float:right;" ><?php echo addslashes(esc_html__('25 px', 'ARForms')) ?></div>
                                                        </div>
                                                    </div>
                                                    <?php
                                                }
                                                $arffieldinnermarginssetting_value = $newarr['arffieldinnermarginssetting_1'] . "px " . $newarr['arffieldinnermarginssetting_2'] . "px " . $newarr['arffieldinnermarginssetting_1'] . "px " . $newarr['arffieldinnermarginssetting_2'] . "px";
                                                ?>
                                                <input type="hidden" name="arffims" id="arffieldinnermarginsetting" style="width:100px;" class="txtxbox_widget" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arf_autocomplete):not(.arfslider)~|~padding||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"email\"]~|~padding||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"phone\"]~|~padding||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"tel\"]~|~padding||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"password\"]~|~padding||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"hidden\"]~|~padding||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"number\"]~|~padding||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"url\"]~|~padding||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~padding||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~padding||.ar_main_div_{arf_form_id} .arfdropdown-menu > li > a~|~padding","material":""}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_field_padding" value="<?php echo $arffieldinnermarginssetting_value; ?>"  size="5" />
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Field Border Settings', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Border Size', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_center" style="margin-left: -5px;">
                                                <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '8') { ?>
                                                    <div class="arf_float_right">
                                                        <input type="text" name="arffbws" style="width:142px;" id="arffieldborderwidthsetting" class="txtxbox_widget" value="<?php echo esc_attr($newarr['arffieldborderwidthsetting']) ?>" size="4" />&nbsp;<span class="arf_px"><?php echo addslashes(esc_html__('px', 'ARForms')) ?></span>
                                                    </div>
                                                <?php } else { ?>
                                                    <div class="arf_slider_wrapper">
                                                        <input id="arffieldborderwidthsetting_exs" class="arf_slider" data-slider-id='arffieldborderwidthsetting_exsSlider' type="text" data-slider-min="0" data-slider-max="20" data-slider-step="1" data-slider-value="<?php echo esc_attr($newarr['arffieldborderwidthsetting']) ?>" />
                                                        <div class="arf_slider_unit_data">
                                                            <div class="arf_px" style="float:left;"><?php echo addslashes(esc_html__('0 px', 'ARForms')) ?></div>
                                                            <div class="arf_px" style="float:right;"><?php echo addslashes(esc_html__('20 px', 'ARForms')) ?></div>
                                                        </div>

                                                        <input type="hidden" name="arffbws" style="width:100px;" id="arffieldborderwidthsetting" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker)~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"email\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"phone\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"tel\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"password\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"hidden\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"number\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"url\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~border-width||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~border-width||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle:focus~|~border-width||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group.open .arfdropdown-menu.open~|~border-width||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group.open .arfdropdown-menu~|~border-width||.ar_main_div_{arf_form_id} .typeahead.arfdropdown-menu~|~border-width||.ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-left-width||.ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-top-width||.ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-bottom-width||.ar_main_div_{arf_form_id} input[type=text].arf_colorpicker:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~border-width","material":".ar_main_div_{arf_form_id} .arf_materialize_form .controls input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arf_autocomplete)~|~border-bottom-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"email\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"phone\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"tel\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"password\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"hidden\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"number\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"url\"]~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~border-width||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~border-width||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_colorpicker)~|~border-bottom-width||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-left-width||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-top-width||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-bottom-width||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text].arf_colorpicker:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~border-width"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_input_border_width" class="txtxbox_widget" value="<?php echo esc_attr($newarr['arffieldborderwidthsetting']) ?>" size="4" />
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text" ><?php echo addslashes(esc_html__('Border Radius', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_center" style="margin-left: -5px;">
                                                <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '8') { ?>
                                                    <div class="arf_float_right">
                                                        <input type="text" name="arfmbs" style="width:142px;" class="txtxbox_widget"  id="arfmainbordersetting" value="<?php echo esc_attr($newarr['border_radius']) ?>" size="4" />&nbsp;<span class="arf_px"><?php echo addslashes(esc_html__('px', 'ARForms')) ?></span>
                                                    </div>
                                                <?php } else { ?>
                                                    <div class="arf_slider_wrapper">
                                                        <input id="arfmainbordersetting_exs" class="arf_slider" data-slider-id='arfmainbordersetting_exsSlider' type="text" data-slider-min="0" data-slider-max="50" data-slider-step="1" data-slider-value="<?php echo esc_attr($newarr['border_radius']) ?>" />
                                                        <div class="arf_slider_unit_data">
                                                            <div class="arf_px" style="float:left;"><?php echo addslashes(esc_html__('0 px', 'ARForms')) ?></div>
                                                            <div class="arf_px" style="float:right;"><?php echo addslashes(esc_html__('50 px', 'ARForms')) ?></div>
                                                        </div>

                                                        <input type="hidden" name="arfmbs" style="width:100px;" class="txtxbox_widget"  id="arfmainbordersetting" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_editor_colorpicker):not(.arf_autocomplete):not(.arfslider)~|~border-radius||body:not(.rtl) .ar_main_div_{arf_form_id} .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~border-top-left-radius||body.rtl .ar_main_div_{arf_form_id} .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~border-top-right-radius||body:not(.rtl) .ar_main_div_{arf_form_id} .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon ~|~border-top-right-radius||body.rtl .ar_main_div_{arf_form_id} .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon ~|~border-top-left-radius||body:not(.rtl) .ar_main_div_{arf_form_id} .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~border-bottom-left-radius||body.rtl .ar_main_div_{arf_form_id} .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~border-bottom-right-radius||body:not(.rtl) .ar_main_div_{arf_form_id} .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon ~|~border-bottom-right-radius||body.rtl .ar_main_div_{arf_form_id} .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon ~|~border-bottom-left-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"email\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"phone\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"tel\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"password\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"hidden\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"number\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"url\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~border-radius||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~border-radius||body:not(.rtl) .ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-top-left-radius||body:not(.rtl) .ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-bottom-left-radius||body.rtl .ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-top-right-radius||body.rtl .ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-bottom-right-radius||body.rtl .ar_main_div_{arf_form_id} input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider).arf_editor_colorpicker~|~border-top-left-radius||body.rtl .ar_main_div_{arf_form_id} input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider).arf_editor_colorpicker~|~border-bottom-left-radius||body:not(.rtl) .ar_main_div_{arf_form_id} input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider).arf_editor_colorpicker~|~border-top-right-radius||body:not(.rtl) .ar_main_div_{arf_form_id} input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider).arf_editor_colorpicker~|~border-bottom-right-radius||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group.open .arfbtn.dropdown-toggle~|~border-top-left-radius-custom||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group.open .arfbtn.dropdown-toggle~|~border-top-right-radius-custom","material":".ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arf_colorpicker)~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"email\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"phone\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"tel\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"password\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"hidden\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"number\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"url\"]~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~border-radius||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~border-radius"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_input_border_radius" value="<?php echo esc_attr($newarr['border_radius']) ?>" size="4" />
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Border Style', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container">                                               
                                                <div class="arf_toggle_button_group arf_three_button_group" style="margin-right:5px;">
                                                    <?php $newarr['arffieldborderstylesetting'] = isset($newarr['arffieldborderstylesetting']) ? $newarr['arffieldborderstylesetting'] : 'solid'; ?>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['arffieldborderstylesetting'] == 'dashed') ? 'arf_success' : ''; ?>"><input type="radio" name="arffbss" id="arf_input_border_style_dashed" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~border-style||.ar_main_div_{arf_form_id}  input[type=\"email\"]~|~border-style||.ar_main_div_{arf_form_id}  input[type=\"phone\"]~|~border-style||.ar_main_div_{arf_form_id}  input[type=\"tel\"]~|~border-style||.ar_main_div_{arf_form_id}  input[type=\"password\"]~|~border-style||.ar_main_div_{arf_form_id}  input[type=\"hidden\"]~|~border-style||.ar_main_div_{arf_form_id}  input[type=\"number\"]~|~border-style||.ar_main_div_{arf_form_id}  input[type=\"url\"]~|~border-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~border-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~border-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~border-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~border-style||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~border-style||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle:focus~|~border-style||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group.open .arfdropdown-menu.open~|~border-style||.ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-style||.ar_main_div_{arf_form_id} input[type=text].arf_colorpicker:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~border-style","material":".ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker)~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"email\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"phone\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"tel\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"password\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"hidden\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"number\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"url\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-style||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text].arf_colorpicker:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~border-style"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_input_border_style" class="visuallyhidden" value="dashed" <?php checked($newarr['arffieldborderstylesetting'], 'dashed'); ?> /><?php echo addslashes(esc_html__('Dashed', 'ARForms')); ?></label>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['arffieldborderstylesetting'] == 'dotted') ? 'arf_success' : ''; ?>"><input type="radio" name="arffbss" id="arf_input_border_style_dotted" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~border-style||.ar_main_div_{arf_form_id} input[type=\"email\"]~|~border-style||.ar_main_div_{arf_form_id} input[type=\"phone\"]~|~border-style||.ar_main_div_{arf_form_id} input[type=\"tel\"]~|~border-style||.ar_main_div_{arf_form_id} input[type=\"password\"]~|~border-style||.ar_main_div_{arf_form_id} input[type=\"hidden\"]~|~border-style||.ar_main_div_{arf_form_id} input[type=\"number\"]~|~border-style||.ar_main_div_{arf_form_id} input[type=\"url\"]~|~border-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~border-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~border-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~border-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~border-style||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~border-style||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle:focus~|~border-style||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group.open .arfdropdown-menu.open~|~border-style||.ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-style||.ar_main_div_{arf_form_id} input[type=text].arf_colorpicker:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~border-style","material":".ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker)~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"email\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"phone\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"tel\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"password\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"hidden\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"number\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"url\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-style||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text].arf_colorpicker:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~border-style"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_input_border_style" class="visuallyhidden" value="dotted" <?php checked($newarr['arffieldborderstylesetting'], 'dotted'); ?> /><?php echo addslashes(esc_html__('Dotted', 'ARForms')); ?></label>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['arffieldborderstylesetting'] == 'solid') ? 'arf_success' : ''; ?>"><input type="radio" name="arffbss" id="arf_input_border_style_solid" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~border-style||.ar_main_div_{arf_form_id} input[type=\"email\"]~|~border-style||.ar_main_div_{arf_form_id} input[type=\"phone\"]~|~border-style||.ar_main_div_{arf_form_id} input[type=\"tel\"]~|~border-style||.ar_main_div_{arf_form_id} input[type=\"password\"]~|~border-style||.ar_main_div_{arf_form_id} input[type=\"hidden\"]~|~border-style||.ar_main_div_{arf_form_id} input[type=\"number\"]~|~border-style||.ar_main_div_{arf_form_id} input[type=\"url\"]~|~border-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~border-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~border-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~border-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~border-style||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~border-style||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle:focus~|~border-style||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group.open .arfdropdown-menu.open~|~border-style||.ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-style||.ar_main_div_{arf_form_id} input[type=text].arf_colorpicker:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~border-style","material":".ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .controls input[type=\"text\"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker)~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"email\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"phone\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"tel\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"password\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"hidden\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"number\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=\"url\"]~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~border-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~border-bottom-style||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_editor_prefix.arf_colorpicker_prefix_editor~|~border-style||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text].arf_colorpicker:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~border-style"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_input_border_style" class="visuallyhidden" value="solid" <?php checked($newarr['arffieldborderstylesetting'], 'solid'); ?> /><?php echo addslashes(esc_html__('Solid', 'ARForms')); ?></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Calendar Date Format', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Date Format', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container">
                                                <div class="arf_dropdown_wrapper" style="margin-right: 5px;float:right;">
                                                    <?php
                                                    $wp_format_date = get_option('date_format');

                                                    if ($wp_format_date == 'F j, Y' || $wp_format_date == 'm/d/Y') {
                                                        ?>
                                                        <div class="sltstandard1" style="float:left;">


                                                            <?php
                                                            $arf_selbx_dt_format = "";
                                                            if ($newarr['date_format'] == 'MMMM D, YYYY') {
                                                                $arf_selbx_dt_format = date('F d, Y', current_time('timestamp'));
                                                            } else if ($newarr['date_format'] == 'MMM D, YYYY') {
                                                                $arf_selbx_dt_format = date('M d, Y', current_time('timestamp'));
                                                            } else {
                                                                $arf_selbx_dt_format = date('m/d/Y', current_time('timestamp'));
                                                            }
                                                            ?>
                                                            <input id="frm_date_format" name="arffdaf" value="<?php echo $newarr['date_format']; ?>" type="hidden" onchange="change_date_format_new();">
                                                            <dl class="arf_selectbox arf_editor_styling_date_format" data-name="arffdaf" data-id="frm_date_format" style="width:155px;">
                                                                <dt><span><?php echo $arf_selbx_dt_format; ?></span>
                                                                <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                                                <dd>
                                                                    <ul style="display: none;" data-id="frm_date_format">
                                                                        <li class="arf_selectbox_option" data-value="MM/DD/YYYY" data-label="<?php echo date('m/d/Y', current_time('timestamp')); ?>"><?php echo date('m/d/Y', current_time('timestamp')); ?></li>
                                                                        <li class="arf_selectbox_option" data-value="MMM D, YYYY" data-label="<?php echo date('M d, Y', current_time('timestamp')); ?>"><?php echo date('M d, Y', current_time('timestamp')); ?></li>
                                                                        <li class="arf_selectbox_option" data-value="MMMM D, YYYY" data-label="<?php echo date('F d, Y', current_time('timestamp')); ?>"><?php echo date('F d, Y', current_time('timestamp')); ?></li>
                                                                    </ul>
                                                                </dd>
                                                            </dl>

                                                        </div>




                                                    <?php } else if ($wp_format_date == 'd/m/Y') { ?>

                                                        <div class="sltstandard1" style="float:left;">

                                                            <?php
                                                            $arf_selbx_dt_format = "";
                                                            if ($newarr['date_format'] == 'D MMMM, YYYY') {
                                                                $arf_selbx_dt_format = date('d F, Y', current_time('timestamp'));
                                                            } else if ($newarr['date_format'] == 'D MMM, YYYY') {
                                                                $arf_selbx_dt_format = date('d M, Y', current_time('timestamp'));
                                                            } else {
                                                                $arf_selbx_dt_format = date('d/m/Y', current_time('timestamp'));
                                                            }
                                                            ?>
                                                            <input id="frm_date_format" name="arffdaf" value="<?php echo $newarr['date_format']; ?>" type="hidden" onchange="change_date_format_new();">
                                                            <dl class="arf_selectbox" data-name="arffdaf" data-id="frm_date_format" style="width:122px;">
                                                                <dt><span><?php echo $arf_selbx_dt_format; ?></span>
                                                                <input value="<?php echo $arf_selbx_dt_format; ?>" style="display:none;width:110px;" class="arf_autocomplete" type="text">
                                                                <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                                                <dd>
                                                                    <ul style="display: none;" data-id="frm_date_format">
                                                                        <li class="arf_selectbox_option" data-value="DD/MM/YYYY" data-label="<?php echo date('d/m/Y', current_time('timestamp')); ?>"><?php echo date('d/m/Y', current_time('timestamp')); ?></li>
                                                                        <li class="arf_selectbox_option" data-value="D MMM, YYYY" data-label="<?php echo date('d M, Y', current_time('timestamp')); ?>"><?php echo date('d M, Y', current_time('timestamp')); ?></li>
                                                                        <li class="arf_selectbox_option" data-value="D MMMM, YYYY" data-label="<?php echo date('d F, Y', current_time('timestamp')); ?>"><?php echo date('d F, Y', current_time('timestamp')); ?></li>
                                                                    </ul>
                                                                </dd>
                                                            </dl>


                                                        </div>



                                                    <?php } else if ($wp_format_date == 'Y/m/d') { ?>

                                                        <div class="sltstandard1" style="float:left;">

                                                            <?php
                                                            $arf_selbx_dt_format = "";
                                                            if ($newarr['date_format'] == 'YYYY, MMMM D') {
                                                                $arf_selbx_dt_format = date('Y, F d', current_time('timestamp'));
                                                            } else if ($newarr['date_format'] == 'YYYY, MMM D') {
                                                                $arf_selbx_dt_format = date('Y, M d', current_time('timestamp'));
                                                            } else {
                                                                $arf_selbx_dt_format = date('Y/m/d', current_time('timestamp'));
                                                            }
                                                            ?>
                                                            <input id="frm_date_format" name="arffdaf" value="<?php echo $newarr['date_format']; ?>" type="hidden" onchange="change_date_format_new();">
                                                            <dl class="arf_selectbox" data-name="arffdaf" data-id="frm_date_format" style="width:122px;">
                                                                <dt><span><?php echo $arf_selbx_dt_format; ?></span>
                                                                <input value="<?php echo $arf_selbx_dt_format; ?>" style="display:none;width:110px;" class="arf_autocomplete" type="text">
                                                                <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                                                <dd>
                                                                    <ul style="display: none;" data-id="frm_date_format">
                                                                        <li class="arf_selectbox_option" data-value="YYYY/MM/DD" data-label="<?php echo date('Y/m/d', current_time('timestamp')); ?>"><?php echo date('Y/m/d', current_time('timestamp')); ?></li>
                                                                        <li class="arf_selectbox_option" data-value="YYYY, MMM D" data-label="<?php echo date('Y, M d', current_time('timestamp')); ?>"><?php echo date('Y, M d', current_time('timestamp')); ?></li>
                                                                        <li class="arf_selectbox_option" data-value="YYYY, MMMM D" data-label="<?php echo date('Y, F d', current_time('timestamp')); ?>"><?php echo date('Y, F d', current_time('timestamp')); ?></li>
                                                                    </ul>
                                                                </dd>
                                                            </dl>


                                                        </div>



                                                    <?php } else { ?>

                                                        <div class="sltstandard1" style="float:left;">

                                                            <?php
                                                            $arf_selbx_dt_format = "";
                                                            if ($newarr['date_format'] == 'MMMM D, YYYY') {
                                                                $arf_selbx_dt_format = date('F d, Y', current_time('timestamp'));
                                                            } else if ($newarr['date_format'] == 'MMM D, YYYY') {
                                                                $arf_selbx_dt_format = date('M d, Y', current_time('timestamp'));
                                                            } else if ($newarr['date_format'] == 'YYYY/MM/DD') {
                                                                $arf_selbx_dt_format = date('Y/m/d', current_time('timestamp'));
                                                            } else if ($newarr['date_format'] == 'MM/DD/YYYY') {
                                                                $arf_selbx_dt_format = date('m/d/Y', current_time('timestamp'));
                                                            } else {
                                                                $arf_selbx_dt_format = date('d/m/Y', current_time('timestamp'));
                                                            }
                                                            ?>
                                                            <input id="frm_date_format" name="arffdaf" value="<?php echo $newarr['date_format']; ?>" type="hidden" onchange="change_date_format_new();">
                                                            <dl class="arf_selectbox" data-name="arffdaf" data-id="frm_date_format" style="width:122px;">
                                                                <dt><span><?php echo $arf_selbx_dt_format; ?></span>
                                                                <input value="<?php echo $arf_selbx_dt_format; ?>" style="display:none;width:110px;" class="arf_autocomplete" type="text">
                                                                <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                                                <dd>
                                                                    <ul style="display: none;" data-id="frm_date_format">
                                                                        <li class="arf_selectbox_option" data-value="DD/MM/YYYY" data-label="<?php echo date('d/m/Y', current_time('timestamp')); ?>"><?php echo date('d/m/Y', current_time('timestamp')); ?></li>
                                                                        <li class="arf_selectbox_option" data-value="MM/DD/YYYY" data-label="<?php echo date('m/d/Y', current_time('timestamp')); ?>"><?php echo date('m/d/Y', current_time('timestamp')); ?></li>
                                                                        <li class="arf_selectbox_option" data-value="YYYY/MM/DD" data-label="<?php echo date('Y/m/d', current_time('timestamp')); ?>"><?php echo date('Y/m/d', current_time('timestamp')); ?></li>
                                                                        <li class="arf_selectbox_option" data-value="MMM D, YYYY" data-label="<?php echo date('M d, Y', current_time('timestamp')); ?>"><?php echo date('M d, Y', current_time('timestamp')); ?></li>
                                                                        <li class="arf_selectbox_option" data-value="MMMM D, YYYY" data-label="<?php echo date('F d, Y', current_time('timestamp')); ?>"><?php echo date('F d, Y', current_time('timestamp')); ?></li>
                                                                    </ul>
                                                                </dd>
                                                            </dl>
                                                        </div>
                                                    <?php }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Checkbox & Radio Style', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Style', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container">
                                                <div class="arf_dropdown_wrapper" style="margin-right: 5px;">
                                                    <input id="frm_check_radio_style" name="arfcksn" value="<?php echo $newarr['arfcheckradiostyle']; ?>" type="hidden" onchange="ShowColorSelect(this.value);">
                                                    <dl class="arf_selectbox" data-name="arfcksn" data-id="frm_check_radio_style" style="width:122px;">
                                                        <?php
                                                        $material_checkbox_style = array(
                                                            'custom' => addslashes(esc_html__('Custom', 'ARForms')),
                                                            'default' => addslashes(esc_html__('Default', 'ARForms')),
                                                            'material' => addslashes(esc_html__('Material 1', 'ARForms')),
                                                            'material_tick' => addslashes(esc_html__('Material 2', 'ARForms')),
                                                        );

                                                        if ($newarr['arfcheckradiostyle'] != 'custom' && $newarr['arfcheckradiostyle'] == '') {
                                                            $newarr['arfcheckradiostyle'] = 'default';
                                                        }
                                                        ?>
                                                        <dt><span><?php echo ucwords($material_checkbox_style[$newarr['arfcheckradiostyle']]); ?></span>
                                                        <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                                        <?php
                                                        $display_default = $display_material = "";
                                                        if ($newarr['arfinputstyle'] == 'standard' || $newarr['arfinputstyle'] == 'rounded') {
                                                            $default_class = "arfvisible";
                                                            $default_material_class = "arfhidden";
                                                        } else {
                                                            $default_class = "arfhidden";
                                                            $default_material_class = "arfvisible";
                                                        }
                                                        ?>
                                                        <dd>
                                                            <ul style="display: none;" data-id="frm_check_radio_style">
                                                                <li class="arf_selectbox_option" data-value="custom" data-label="Custom">Custom</li>
                                                                <li class="arf_selectbox_option <?php echo $default_class; ?>" data-value="default" data-label="Default">Default</li>
                                                                <li class="arf_selectbox_option <?php echo $default_material_class; ?>" data-value="material" data-label="Material 1" >Material 1</li>
                                                                <li class="arf_selectbox_option <?php echo $default_material_class; ?>" data-value="material_tick" data-label="Material 2">Material 2</li>
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width" id="check_radio_main_icon" style="<?php echo ($newarr['arfcheckradiostyle'] == "custom") ? 'display:block;margin-bottom: 20px;height: auto;' : 'display:none;margin-bottom: 20px;height: auto;'; ?>">
                                            <div class="arf_accordion_inner_title arf_width_50"><?php echo addslashes(esc_html__('Icon', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_width_50 " style="margin-right: -1px;">
                                                <div class="arf_field_check_radio_wrapper" id="arf_field_check_radio_wrapper arf_right" style="margin-left: -5px;">
                                                    <div class="custom_checkbox_wrapper">
                                                        <div class="arf_prefix_suffix_container_wrapper" data-action='edit' data-field='checkbox' id="arf_edit_check" data-toggle="arfmodal" href="#arf_fontawesome_modal" data-field_type='checkbox'>
                                                            <div class="arf_prefix_container" id="arf_select_checkbox">
                                                                <?php
                                                                if (isset($newarr['arf_checked_checkbox_icon']) && $newarr['arf_checked_checkbox_icon'] != '') {
                                                                    echo "<i id='arf_prefix_suffix_icon' class='arf_prefix_suffix_icon {$newarr['arf_checked_checkbox_icon']}'></i>";
                                                                } else {
                                                                    echo "<i id='arf_prefix_suffix_icon' class='arf_prefix_suffix_icon arfa arfa-check'></i>";
                                                                }
                                                                ?>
                                                            </div>
                                                            <div class="arf_prefix_suffix_action_container" style="position:relative;">
                                                                <div class="arf_prefix_suffix_action" title="Change Icon" style="margin-left: 15px;">
                                                                    <i class="arfa arfa-caret-down arfa-lg"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="howto"> <?php echo addslashes(esc_html__('CheckBoxes', 'ARForms')); ?> </div>
                                                    </div>
                                                    <br>
                                                    <br>
                                                    <div class="custom_checkbox_wrapper">
                                                        <div class="arf_prefix_suffix_container_wrapper" data-action='edit' data-field='radio' id="arf_edit_radio" data-field_type='radio'>
                                                            <div class="arf_suffix_container" id="arf_select_radio">
                                                                <?php
                                                                if (isset($newarr['arf_checked_radio_icon']) && $newarr['arf_checked_radio_icon'] != '') {
                                                                    echo "<i id='arf_prefix_suffix_icon' class='arf_prefix_suffix_icon  {$newarr['arf_checked_radio_icon']}'></i>";
                                                                } else {
                                                                    echo "<i id='arf_prefix_suffix_icon' class='arf_prefix_suffix_icon arfa arfa-circle'></i>";
                                                                }
                                                                ?>
                                                            </div>
                                                            <div class="arf_prefix_suffix_action_container" style="position:relative;">
                                                                <div class="arf_prefix_suffix_action" title="Change Icon" style="margin-left: 15px;">
                                                                    <i class="arfa arfa-caret-down arfa-lg"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="howto"> <?php echo addslashes(esc_html__('Radio Buttons', 'ARForms')); ?> </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width" style="height: 5px;min-height:5px"></div>
                                        <input type="hidden" name="enable_arf_checkbox" id="enable_arf_checkbox" value="<?php echo isset($newarr['enable_arf_checkbox']) ? $newarr['enable_arf_checkbox'] : ''; ?>" />
                                        <input type="hidden" name="arf_checkbox_icon" id="arf_checkbox_icon" value="<?php echo (isset($newarr['arf_checked_checkbox_icon']) && $newarr['arf_checked_checkbox_icon'] != '') ? $newarr['arf_checked_checkbox_icon'] : 'arfa arfa-check'; ?>" />
                                        <input type="hidden" name="enable_arf_radio" id="enable_arf_radio" value="<?php echo isset($newarr['enable_arf_radio']) ? $newarr['enable_arf_radio'] : ''; ?>" />
                                        <input type="hidden" name="arf_radio_icon" id="arf_radio_icon" value="<?php echo (isset($newarr['arf_checked_radio_icon']) && $newarr['arf_checked_radio_icon'] != '') ? $newarr['arf_checked_radio_icon'] : 'arfa arfa-circle'; ?>" />
                                    </div>
                                </dd>
                            </dl>

                            <dl class="arf_accordion_tab_submit_settings">
                                <dd>
                                    <a href="javascript:void(0)" data-target="arf_accordion_tab_submit_settings"><?php echo addslashes(esc_html__('Submit Button Settings', 'ARForms')); ?></a>
                                    <div class="arf_accordion_container">
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Button Settings', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text"><?php echo addslashes(esc_html__("Button Alignment", "ARForms")); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right">
                                                <div class="arf_toggle_button_group arf_three_button_group" style="margin-right:8px;">
                                                    <?php $newarr['arfsubmitalignsetting'] = isset($newarr['arfsubmitalignsetting']) ? $newarr['arfsubmitalignsetting'] : 'center'; 
                                                    ?>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['arfsubmitalignsetting'] == 'right') ? 'arf_success' : ''; ?>"><input type="radio" name="arfmsas" id="frm_submit_align_3"  class="visuallyhidden" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_submit_div~|~button_auto","material":".ar_main_div_{arf_form_id} .arf_submit_div~|~button_auto"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_submit_button_position" value="right" <?php checked($newarr['arfsubmitalignsetting'], 'right'); ?> /><?php echo addslashes(esc_html__('Right', 'ARForms')); ?></label>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['arfsubmitalignsetting'] == 'center') ? 'arf_success' : ''; ?>"><input type="radio" name="arfmsas" class="visuallyhidden" id="frm_submit_align_2" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_submit_div~|~button_auto","material":".ar_main_div_{arf_form_id} .arf_submit_div~|~button_auto"}' value="center" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_submit_button_position" <?php checked($newarr['arfsubmitalignsetting'], 'center'); ?> /><?php echo addslashes(esc_html__('Center', 'ARForms')); ?></label>
                                                    <label class="arf_toggle_btn <?php echo ($newarr['arfsubmitalignsetting'] == 'left') ? 'arf_success' : ''; ?>"><input type="radio" name="arfmsas" class="visuallyhidden" id="frm_submit_align_1" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_submit_div~|~button_auto","material":".ar_main_div_{arf_form_id} .arf_submit_div~|~button_auto"}' value="left" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_submit_button_position" <?php checked($newarr['arfsubmitalignsetting'], 'left'); ?> /><?php echo addslashes(esc_html__('Left', 'ARForms')); ?></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text"><?php echo addslashes(esc_html__('Button Width (optional)', 'ARForms')) ?></div>
                                            <div class="arf_accordion_content_container">
                                                <span class="arfpxspan">px</span>
                                                <input type="text" name="arfsbws" id="arfsubmitbuttonwidthsetting" style="margin-right: 1px;" class="arf_small_width_txtbox arfcolor" value="<?php echo esc_attr($newarr['arfsubmitbuttonwidthsetting']) ?>"  onchange="arfsetsubmitwidth();" size="5" />
                                                <input type="hidden" name="arfsbaw" id="arfsubmitautowidth" value="<?php echo $newarr['arfsubmitautowidth']; ?>" />

                                            </div>

                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text arf_width_50" ><?php echo addslashes(esc_html__('Button Height (optional)', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_width_50">
                                                <span class="arfpxspan">px</span>
                                                <input type="text" name="arfsbhs" id="arfsubmitbuttonheightsetting" class="arf_small_width_txtbox arfcolor" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~height","material":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~height"}' style="margin-right: 1px;" data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_submit_button_height" value="<?php echo esc_attr($newarr['arfsubmitbuttonheightsetting']) ?>"  size="5" />
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Button Text', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container">
                                                <?php
                                                $newarr['arfsubmitbuttontext'] = isset($newarr['arfsubmitbuttontext']) ? $newarr['arfsubmitbuttontext'] : '';
                                                if ($newarr['arfsubmitbuttontext'] == '') {
                                                    $arf_option = get_option('arf_options');
                                                    $submit_value = $arf_option->submit_value;
                                                } else {
                                                    $submit_value = esc_attr($newarr['arfsubmitbuttontext']);
                                                }
                                                ?>
                                                <input type="text" name="arfsubmitbuttontext" id="arfsubmitbuttontext" class="arf_large_input_box arfwidth108 arfcolor" value="<?php echo $submit_value; ?>"  style="margin-right:5px;text-align:left;" size="5" />
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row">
                                            <div class="arf_accordion_inner_title arf_width_50"><?php echo addslashes(esc_html__('Button Style', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_width_50 arf_right">
                                                <?php
                                                $newarr['arfsubmitbuttonstyle'] = isset($newarr['arfsubmitbuttonstyle']) ? $newarr['arfsubmitbuttonstyle'] : 'border';                                                
                                                ?>
                                                <input id="arfsubmitbuttonstyle" name="arfsubmitbuttonstyle" value="<?php echo $newarr['arfsubmitbuttonstyle']; ?>" type="hidden" onchange="arfchnagebuttonstyle(this.value);">
                                                <dl class="arf_selectbox arfsubmitbuttonstyledl" data-name="arfsubmitbuttonstyle" data-id="arfsubmitbuttonstyle" style="width:126px;" >
                                                    <dt><span><?php echo ucwords($newarr['arfsubmitbuttonstyle']); ?></span>
                                                    <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                                    <dd>
                                                        <ul style="display: none;" data-id="arfsubmitbuttonstyle">
                                                            <li class="arf_selectbox_option" data-value="flat" data-label="<?php echo ucwords('flat'); ?>"><?php echo ucwords('flat'); ?></li>
                                                            <li class="arf_selectbox_option" data-value="border" data-label="<?php echo ucwords('border'); ?>"><?php echo ucwords('border'); ?></li>
                                                            <li class="arf_selectbox_option" data-value="reverse border" data-label="<?php echo ucwords('reverse border'); ?>"><?php echo ucwords('reverse border'); ?></li>
                                                        </ul>
                                                    </dd>
                                                </dl>                                                
                                            </div>
                                        </div>
                                        <input type="hidden" name="arfsbcs" id="arfsubmitbuttoncolorsetting" class="hex txtxbox_widget" value="<?php echo esc_attr($newarr['arfsubmitbgcolor2setting']) ?>" style="width:80px;" />
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text " ><?php echo addslashes(esc_html__('Background Image', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right  arf_right">
                                                <div class="arf_imageloader arf_form_style_file_upload_loader" id="ajax_submit_loader"></div>
                                                <div id="submit_btn_img_div" <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '9') { ?> class="iframe_submit_original_btn" data-id="arfsbis" style="margin-right:5px; position: relative; overflow: hidden; cursor:pointer; max-width:130px; height:27px; background: #1BBAE1; font-weight:bold; <?php if ($newarr['submit_bg_img'] == '') { ?> background:#1BBAE1;padding:7px 10px 0 10px;font-size:13px;border-radius:3px;-webkit-border-radius:3px;-o-border-radius:3px;-moz-border-radius:3px;color:#FFFFFF;border:1px solid #CCCCCC;box-shadow: 0px 0px 2px rgba(0, 0, 0, 0.4);-webkit-box-shadow: 0px 0px 2px rgba(0, 0, 0, 0.4);-o-box-shadow: 0px 0px 2px rgba(0, 0, 0, 0.4);-moz-box-shadow: 0px 0px 2px rgba(0, 0, 0, 0.4);display: inline-block; <?php } ?>" <?php } else { ?> style="margin-left:0px;" <?php } ?>>
                                                    <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '9' && $newarr['submit_bg_img'] == '') { ?> <span class="arf_form_style_file_upload_icon">
                                                        <svg width="16" height="18" viewBox="0 0 18 20" fill="#ffffff"><path xmlns="http://www.w3.org/2000/svg" d="M15.906,18.599h-1h-12h-1h-1v-7h2v5h12v-5h2v7H15.906z M13.157,7.279L9.906,4.028v8.571c0,0.552-0.448,1-1,1c-0.553,0-1-0.448-1-1v-8.54l-3.22,3.22c-0.403,0.403-1.058,0.403-1.46,0 c-0.403-0.403-0.403-1.057,0-1.46l4.932-4.932c0.211-0.211,0.488-0.306,0.764-0.296c0.275-0.01,0.553,0.085,0.764,0.296 l4.932,4.932c0.403,0.403,0.403,1.057,0,1.46S13.561,7.682,13.157,7.279z"/></svg></span> <?php } ?>
                                                    <?php
                                                    if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '9') {
                                                        if ($newarr['submit_bg_img'] != '') {
                                                            ?>
                                                            <img src="<?php echo $newarr['submit_bg_img']; ?>" height="35" width="35" style="margin-left:5px;border:1px solid #D5E3FF !important;" />&nbsp;<span onclick="delete_image('button_image');" style="width:35px;height: 35px;display:inline-block;cursor: pointer;"><svg width="23px" height="27px" viewBox="0 0 30 30"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4786FF" d="M19.002,4.351l0.007,16.986L3.997,21.348L3.992,4.351H1.016V2.38  h1.858h4.131V0.357h8.986V2.38h4.146h1.859l0,0v1.971H19.002z M16.268,4.351H6.745H5.993l0.006,15.003h10.997L17,4.351H16.268z   M12.01,7.346h1.988v9.999H12.01V7.346z M9.013,7.346h1.989v9.999H9.013V7.346z"/></svg></span>
                                                            <input type="hidden" name="arfsbis" onclick="clear_file_submit();" value="<?php echo esc_attr($newarr['submit_bg_img']) ?>" id="arfsubmitbuttonimagesetting" />
                                                        <?php } else { ?>
                                                            <input type="text" class="original" name="submit_btn_img" id="field_arfsbis" data-form-id="" data-file-valid="true" style="position: absolute; cursor: pointer; top: 0px; width: 160px; height: 59px; left: -999px; z-index: 100; opacity: 0; filter:alpha(opacity=0);" />
                                                            <input type="hidden" id="type_arfsbis" name="type_arfsbis" value="1" >
                                                            <input type="hidden" value="jpg, jpeg, jpe, gif, png, bmp, tif, tiff, ico" id="file_types_arfsbis" name="field_types_arfsbis" />

                                                            <input type="hidden" name="imagename" id="imagename" value="" />
                                                            <input type="hidden" name="arfsbis" onclick="clear_file_submit();" value="" id="arfsubmitbuttonimagesetting" />
                                                            <?php
                                                        }
                                                        echo '<div id="arfsbis_iframe_div"><iframe style="display:none;" id="arfsbis_iframe" src="' . ARFURL . '/core/views/iframe.php" ></iframe></div>';
                                                    } else {
                                                        if ($newarr['submit_bg_img'] != '') {
                                                            ?>
                                                            <img src="<?php echo $newarr['submit_bg_img']; ?>" height="35" width="35" style="margin-left:5px;border:1px solid #D5E3FF !important;" />&nbsp;<span onclick="delete_image('button_image');" style="width:35px;height: 35px;display:inline-block;cursor: pointer;"><svg width="23px" height="27px" viewBox="0 0 30 30"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4786FF" d="M19.002,4.351l0.007,16.986L3.997,21.348L3.992,4.351H1.016V2.38  h1.858h4.131V0.357h8.986V2.38h4.146h1.859l0,0v1.971H19.002z M16.268,4.351H6.745H5.993l0.006,15.003h10.997L17,4.351H16.268z   M12.01,7.346h1.988v9.999H12.01V7.346z M9.013,7.346h1.989v9.999H9.013V7.346z"/></svg></span>
                                                            <input type="hidden" name="arfsbis" onclick="clear_file_submit();" value="<?php echo esc_attr($newarr['submit_bg_img']) ?>" id="arfsubmitbuttonimagesetting" />
                                                        <?php } else { ?>
                                                            <div class="arfajaxfileupload">
                                                                <div class="arf_form_style_file_upload_icon">
                                                                    <svg width="16" height="18" viewBox="0 0 18 20" fill="#ffffff"><path xmlns="http://www.w3.org/2000/svg" d="M15.906,18.599h-1h-12h-1h-1v-7h2v5h12v-5h2v7H15.906z M13.157,7.279L9.906,4.028v8.571c0,0.552-0.448,1-1,1c-0.553,0-1-0.448-1-1v-8.54l-3.22,3.22c-0.403,0.403-1.058,0.403-1.46,0 c-0.403-0.403-0.403-1.057,0-1.46l4.932-4.932c0.211-0.211,0.488-0.306,0.764-0.296c0.275-0.01,0.553,0.085,0.764,0.296 l4.932,4.932c0.403,0.403,0.403,1.057,0,1.46S13.561,7.682,13.157,7.279z"/></svg>
                                                                </div>
                                                                <input type="file" data-val="submit_btn_img" name="submit_btn_img" id="submit_btn_img" class="original" style="position: absolute; cursor: pointer; top: 0px; padding:0; margin:0; height:100%; width:100%; right:0; z-index: 100; opacity: 0; filter:alpha(opacity=0);" />
                                                            </div>

                                                            <input type="hidden" name="imagename" id="imagename" value="" />
                                                            <input type="hidden" name="arfsbis" onclick="clear_file_submit();" value="" id="arfsubmitbuttonimagesetting" />
                                                            <?php
                                                        }
                                                    }
                                                    ?>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width">
                                            <div class="arf_accordion_inner_title arf_two_row_text "><?php echo addslashes(esc_html__('Background Hover Image', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_right arf_right">
                                                <div class="arf_imageloader arf_form_style_file_upload_loader" id="ajax_submit_hover_loader"></div>
                                                <div id="submit_hover_btn_img_div" <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '9') { ?> class="iframe_submit_hover_original_btn" data-id="arfsbhis" style="margin-right:5px; position: relative; overflow: hidden; cursor:pointer; max-width:130px; height:27px; background: #1BBAE1; font-weight:bold; <?php if ($newarr['submit_hover_bg_img'] == '') { ?> background:#1BBAE1;padding:7px 10px 0 10px;font-size:13px;border-radius:3px;-webkit-border-radius:3px;-o-border-radius:3px;-moz-border-radius:3px;color:#FFFFFF;border:1px solid #CCCCCC;box-shadow: 0px 0px 2px rgba(0, 0, 0, 0.4);-webkit-box-shadow: 0px 0px 2px rgba(0, 0, 0, 0.4);-o-box-shadow: 0px 0px 2px rgba(0, 0, 0, 0.4);-moz-box-shadow: 0px 0px 2px rgba(0, 0, 0, 0.4);display: inline-block; <?php } ?>" <?php } else { ?> style="margin-left:0px;" <?php } ?>>
                                                    <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '9' && $newarr['submit_hover_bg_img'] == '') { ?> <span class="arf_form_style_file_upload_icon">
                                                        <svg width="16" height="18" viewBox="0 0 18 20" fill="#ffffff"><path xmlns="http://www.w3.org/2000/svg" d="M15.906,18.599h-1h-12h-1h-1v-7h2v5h12v-5h2v7H15.906z M13.157,7.279L9.906,4.028v8.571c0,0.552-0.448,1-1,1c-0.553,0-1-0.448-1-1v-8.54l-3.22,3.22c-0.403,0.403-1.058,0.403-1.46,0 c-0.403-0.403-0.403-1.057,0-1.46l4.932-4.932c0.211-0.211,0.488-0.306,0.764-0.296c0.275-0.01,0.553,0.085,0.764,0.296 l4.932,4.932c0.403,0.403,0.403,1.057,0,1.46S13.561,7.682,13.157,7.279z"/></svg></span> <?php } ?>
                                                    <?php
                                                    if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '9') {
                                                        if ($newarr['submit_hover_bg_img'] != '') {
                                                            ?>
                                                            <img src="<?php echo $newarr['submit_hover_bg_img']; ?>" height="35" width="35" style="margin-left:5px;border:1px solid #D5E3FF !important;" />&nbsp;<span onclick="delete_submit_hover_bg_img();" style="width:35px;height: 35px;display:inline-block;cursor: pointer;"><svg width="23px" height="27px" viewBox="0 0 30 30"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4786FF" d="M19.002,4.351l0.007,16.986L3.997,21.348L3.992,4.351H1.016V2.38  h1.858h4.131V0.357h8.986V2.38h4.146h1.859l0,0v1.971H19.002z M16.268,4.351H6.745H5.993l0.006,15.003h10.997L17,4.351H16.268z   M12.01,7.346h1.988v9.999H12.01V7.346z M9.013,7.346h1.989v9.999H9.013V7.346z"/></svg></span>
                                                            <input type="hidden" name="arfsbhis" onclick="clear_file_submit_hover();" value="<?php echo esc_attr($newarr['submit_hover_bg_img']) ?>" id="arfsubmithoverbuttonimagesetting" />
                                                        <?php } else { ?>
                                                            <input type="text" class="original" name="submit_hover_btn_img" id="field_arfsbhis" data-form-id="" data-file-valid="true" style="position: absolute; cursor: pointer; top: 0px; width: 160px; height: 59px; left: -999px; z-index: 100; opacity: 0; filter:alpha(opacity=0);" />
                                                            <input type="hidden" id="type_arfsbhis" name="type_arfsbhis" value="1" >
                                                            <input type="hidden" value="jpg, jpeg, jpe, gif, png, bmp, tif, tiff, ico" id="file_types_arfsbhis" name="field_types_arfsbhis" />

                                                            <input type="hidden" name="imagename_submit_hover" id="imagename_submit_hover" value="" />
                                                            <input type="hidden" name="arfsbhis" onclick="clear_file_submit_hover();" value="" id="arfsubmithoverbuttonimagesetting" />
                                                            <?php
                                                        }
                                                        echo '<div id="arfsbhis_iframe_div"><iframe style="display:none;" id="arfsbhis_iframe" src="' . ARFURL . '/core/views/iframe.php" ></iframe></div>';
                                                    } else {
                                                        if ($newarr['submit_hover_bg_img'] != '') {
                                                            ?>
                                                            <img src="<?php echo $newarr['submit_hover_bg_img']; ?>" height="35" width="35" style="margin-left:5px;border:1px solid #D5E3FF !important;" />&nbsp;<span onclick="delete_image('button_hover_image');" style="width:35px;height: 35px;display:inline-block;cursor: pointer;"><svg width="23px" height="27px" viewBox="0 0 30 30"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4786FF" d="M19.002,4.351l0.007,16.986L3.997,21.348L3.992,4.351H1.016V2.38  h1.858h4.131V0.357h8.986V2.38h4.146h1.859l0,0v1.971H19.002z M16.268,4.351H6.745H5.993l0.006,15.003h10.997L17,4.351H16.268z   M12.01,7.346h1.988v9.999H12.01V7.346z M9.013,7.346h1.989v9.999H9.013V7.346z"/></svg></span>
                                                            <input type="hidden" name="arfsbhis" onclick="clear_file_submit_hover();" value="<?php echo esc_attr($newarr['submit_hover_bg_img']) ?>" id="arfsubmithoverbuttonimagesetting" />
                                                        <?php } else { ?>
                                                            <div class="arfajaxfileupload">
                                                                <div class="arf_form_style_file_upload_icon">
                                                                    <svg width="16" height="18" viewBox="0 0 18 20" fill="#ffffff"><path xmlns="http://www.w3.org/2000/svg" d="M15.906,18.599h-1h-12h-1h-1v-7h2v5h12v-5h2v7H15.906z M13.157,7.279L9.906,4.028v8.571c0,0.552-0.448,1-1,1c-0.553,0-1-0.448-1-1v-8.54l-3.22,3.22c-0.403,0.403-1.058,0.403-1.46,0 c-0.403-0.403-0.403-1.057,0-1.46l4.932-4.932c0.211-0.211,0.488-0.306,0.764-0.296c0.275-0.01,0.553,0.085,0.764,0.296 l4.932,4.932c0.403,0.403,0.403,1.057,0,1.46S13.561,7.682,13.157,7.279z"/></svg>
                                                                </div>
                                                                <input type="file" name="submit_hover_btn_img" data-val="submit_hover_bg" id="submit_hover_btn_img" class="original" style="position: absolute; cursor: pointer; top: 0px; padding:0; margin:0; height:100%; width:100%; right:0; z-index: 100; opacity: 0; filter:alpha(opacity=0);" />
                                                            </div>

                                                            <input type="hidden" name="imagename_submit_hover" id="imagename_submit_hover" value="" />
                                                            <input type="hidden" name="arfsbhis" onclick="clear_file_submit_hover();" value="" id="arfsubmithoverbuttonimagesetting" />
                                                            <?php
                                                        }
                                                    }
                                                    ?>

                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="arf_accordion_container_row arf_half_width" style="height: 70px;">
                                            <div class="arf_accordion_container_inner_div">
                                                <div class="arf_accordion_inner_title arf_width_50"><?php echo addslashes(esc_html__('Font Settings','ARForms')); ?></div>
                                            </div>
                                            <div class="arf_accordion_container_inner_div">
                                                <div class="arf_custom_font" data-id="arf_submit_font_settings">
                                                    <div class="arf_custom_font_icon">
                                                        <svg viewBox="-10 -10 35 35">
                                                        <g id="paint_brush">
                                                        <path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M7.423,14.117c1.076,0,2.093,0.022,3.052,0.068v-0.82c-0.942-0.078-1.457-0.146-1.542-0.205  c-0.124-0.092-0.203-0.354-0.235-0.787s-0.049-1.601-0.049-3.504l0.059-6.568c0-0.299,0.013-0.472,0.039-0.518  C8.772,1.744,8.85,1.725,8.981,1.725c1.549,0,2.584,0.043,3.105,0.128c0.162,0.026,0.267,0.076,0.313,0.148  c0.059,0.092,0.117,0.687,0.176,1.784h0.811c0.052-1.201,0.14-2.249,0.264-3.145l-0.107-0.156c-2.396,0.098-4.561,0.146-6.494,0.146  c-1.94,0-3.936-0.049-5.986-0.146L0.954,0.563c0.078,0.901,0.11,1.976,0.098,3.223h0.84c0.085-1.062,0.141-1.633,0.166-1.714  C2.083,1.99,2.121,1.933,2.17,1.9c0.049-0.032,0.262-0.065,0.641-0.098c0.652-0.052,1.433-0.078,2.34-0.078  c0.443,0,0.674,0.024,0.69,0.073c0.016,0.049,0.024,1.364,0.024,3.947c0,1.313-0.01,2.602-0.029,3.863  c-0.033,1.776-0.072,2.804-0.117,3.084c-0.039,0.201-0.098,0.34-0.176,0.414c-0.078,0.075-0.212,0.129-0.4,0.161  c-0.404,0.065-0.791,0.098-1.162,0.098v0.82C4.861,14.14,6.008,14.117,7.423,14.117L7.423,14.117z"></path>
                                                        </g></svg>
                                                    </div>
                                                    <div class="arf_custom_font_label"><?php echo addslashes(esc_html__('Advanced font options','ARForms')); ?></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="arf_accordion_container_row_separator"></div>
                                        <div class="arf_accordion_container_row arf_padding">
                                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Border Settings', 'ARForms')); ?></div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width" style="margin-left: -5px;">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Size', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_center">
                                                <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '8') { ?>
                                                    <div class="arf_float_right">
                                                        <input type="text" name="arfsbbws" id="arfsubmitbuttonborderwidhtsetting" style="width:142px;" value="<?php echo esc_attr($newarr['arfsubmitborderwidthsetting']) ?>" class="txtxbox_widget" size="4" />&nbsp;<span class="arf_px"><?php echo addslashes(esc_html__('px', 'ARForms')) ?></span>
                                                    </div>
                                                <?php } else { ?>
                                                    <div class="arf_slider_wrapper">
                                                        <input id="arfsubmitbuttonborderwidhtsetting_exs" class="arf_slider" data-slider-id='arfsubmitbuttonborderwidhtsetting_exsSlider' type="text" data-slider-min="0" data-slider-max="20" data-slider-step="1" data-slider-value="<?php echo esc_attr($newarr['arfsubmitborderwidthsetting']) ?>" />
                                                        <div class="arf_slider_unit_data">
                                                            <div class="arf_px" style="float:left;"><?php echo addslashes(esc_html__('0 px', 'ARForms')) ?></div>
                                                            <div class="arf_px" style="float:right;"><?php echo addslashes(esc_html__('20 px', 'ARForms')) ?></div>
                                                        </div>

                                                        <input type="hidden" name="arfsbbws" id="arfsubmitbuttonborderwidhtsetting" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~border-width","material":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~border-width"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_submit_button_border_width" style="width:100px;" value="<?php echo esc_attr($newarr['arfsubmitborderwidthsetting']) ?>" class="txtxbox_widget" size="4" />
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width" style="margin-left: -5px;">
                                            <div class="arf_accordion_inner_title"><?php echo addslashes(esc_html__('Radius', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_align_center">
                                                <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '8') { ?>
                                                    <div class="arf_float_right">
                                                        <input type="text" value="<?php echo esc_attr($newarr['arfsubmitborderradiussetting']) ?>" name="arfsbbrs" id="arfsubmitbuttonborderradiussetting" class="txtxbox_widget" size="4" style="width:142px;" />&nbsp;<span class="arf_px"><?php echo addslashes(esc_html__('px', 'ARForms')) ?></span>
                                                    </div>
                                                <?php } else { ?>
                                                    <div class="arf_slider_wrapper">
                                                        <input id="arfsubmitbuttonborderradiussetting_exs" class="arf_slider" data-slider-id='arfsubmitbuttonborderradiussetting_exsSlider' type="text" data-slider-min="0" data-slider-max="50" data-slider-step="1" data-slider-value="<?php echo esc_attr($newarr['arfsubmitborderradiussetting']) ?>" />
                                                        <div class="arf_slider_unit_data">
                                                            <div class="arf_px" style="float:left;"><?php echo addslashes(esc_html__('0 px', 'ARForms')) ?></div>
                                                            <div class="arf_px" style="float:right;"><?php echo addslashes(esc_html__('50 px', 'ARForms')) ?></div>
                                                        </div>

                                                        <input type="hidden" value="<?php echo esc_attr($newarr['arfsubmitborderradiussetting']) ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~border-radius","material":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~border-radius"}' name="arfsbbrs" id="arfsubmitbuttonborderradiussetting" class="txtxbox_widget"  data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_submit_button_border_radius" size="4" style="width:100px;" />
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="arf_accordion_container_row arf_half_width" style="margin-bottom: 30px;">
                                            <div class="arf_accordion_inner_title arf_form_padding"><?php echo addslashes(esc_html__('Margin', 'ARForms')); ?></div>
                                            <div class="arf_accordion_content_container arf_form_container ">
                                                <div class="arf_submit_margin_box_wrapper"><input type="text" name="arfsubmitbuttonmarginsetting_1" id="arfsubmitbuttonmarginsetting_1" onchange="arf_change_field_padding('arfsubmitbuttonmarginsetting');" value="<?php echo esc_attr($newarr['arfsubmitbuttonmarginsetting_1']); ?>" class="arf_submit_margin_box" /><br /><span class="arf_px arf_font_size" ><?php echo addslashes(esc_html__('Top', 'ARForms')); ?></span></div>
                                                <div class="arf_submit_margin_box_wrapper"><input type="text" name="arfsubmitbuttonmarginsetting_2" id="arfsubmitbuttonmarginsetting_2" value="<?php echo esc_attr($newarr['arfsubmitbuttonmarginsetting_2']); ?>" onchange="arf_change_field_padding('arfsubmitbuttonmarginsetting');" class="arf_submit_margin_box" /><br /><span class="arf_px arf_font_size" ><?php echo addslashes(esc_html__('Right', 'ARForms')); ?></span></div>
                                                <div class="arf_submit_margin_box_wrapper"><input type="text" name="arfsubmitbuttonmarginsetting_3" id="arfsubmitbuttonmarginsetting_3" value="<?php echo esc_attr($newarr['arfsubmitbuttonmarginsetting_3']); ?>" onchange="arf_change_field_padding('arfsubmitbuttonmarginsetting');" class="arf_submit_margin_box" /><br /><span class="arf_px arf_font_size" style="    margin-left: 5px;"><?php echo addslashes(esc_html__('Bottom', 'ARForms')); ?></span></div>
                                                <div class="arf_submit_margin_box_wrapper"><input type="text" name="arfsubmitbuttonmarginsetting_4" id="arfsubmitbuttonmarginsetting_4" value="<?php echo esc_attr($newarr['arfsubmitbuttonmarginsetting_4']); ?>" onchange="arf_change_field_padding('arfsubmitbuttonmarginsetting');" class="arf_submit_margin_box" /><br /><span class="arf_px arf_font_size" style="margin-left: 10px;"><?php echo addslashes(esc_html__('Left', 'ARForms')); ?></span></div>
                                            </div>
                                            <?php
                                            $arfsubmitbuttonmarginsetting_value = '';

                                            if (esc_attr($newarr['arfsubmitbuttonmarginsetting_1']) != '') {
                                                $arfsubmitbuttonmarginsetting_value .= $newarr['arfsubmitbuttonmarginsetting_1'] . 'px ';
                                            } else {
                                                $arfsubmitbuttonmarginsetting_value .= '0px ';
                                            }
                                            if (esc_attr($newarr['arfsubmitbuttonmarginsetting_2']) != '') {
                                                $arfsubmitbuttonmarginsetting_value .= $newarr['arfsubmitbuttonmarginsetting_2'] . 'px ';
                                            } else {
                                                $arfsubmitbuttonmarginsetting_value .= '0px ';
                                            }
                                            if (esc_attr($newarr['arfsubmitbuttonmarginsetting_3']) != '') {
                                                $arfsubmitbuttonmarginsetting_value .= $newarr['arfsubmitbuttonmarginsetting_3'] . 'px ';
                                            } else {
                                                $arfsubmitbuttonmarginsetting_value .= '0px ';
                                            }
                                            if (esc_attr($newarr['arfsubmitbuttonmarginsetting_4']) != '') {
                                                $arfsubmitbuttonmarginsetting_value .= $newarr['arfsubmitbuttonmarginsetting_4'] . 'px';
                                            } else {
                                                $arfsubmitbuttonmarginsetting_value .= '0px';
                                            }
                                            ?>
                                            <input type="hidden" name="arfsbms" id="arfsubmitbuttonmarginsetting" style="width:100px;" class="txtxbox_widget"  data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arfsubmitbutton.arf_submit_div~|~margin","material":".ar_main_div_{arf_form_id} .arfsubmitbutton.arf_submit_div~|~margin"}'  data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_submit_button_margin" value="<?php echo $arfsubmitbuttonmarginsetting_value; ?>" size="6" />
                                        </div>
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="arf_form_style_tab_container" id="arf_form_custom_css">
                    <div class="arf_form_custom_css_tab">
                        <?php
                            global $custom_css_array;
                        ?>
                        <div class="arf_custom_css_cloud_wrapper">
                            <span><?php echo addslashes(esc_html__('Add CSS Elements','ARForms')) ?></span>
                            <i class="arfa arfa-caret-down"></i>
                            <ul class="arf_custom_css_cloud_list_wrapper">
                            <?php
                                foreach($custom_css_array as $key => $value ){
                                    ?>
                                    <li class="arf_custom_css_cloud_list_item <?php echo (isset($values[$key]) && $values[$key] != '') ? 'arfactive' : ''; ?>" id="<?php echo $value['onclick_1']; ?>"><span><?php echo $value['label_title']; ?></span></li>
                                    <?php
                                }
                            ?>
                            </ul>
                        </div>
                        <div id="arf_expand_css_code" class="arf_expand_css_code_button">
                            <svg width="40px" height="40px" viewBox="-10 -12 39 39">
                                <path fill="#ffffff" d="M18.08,6.598l-1.29,1.289l-0.009-0.009l-4.719,4.72l-1.289-1.29  l4.719-4.719L10.773,1.87l1.289-1.29l4.719,4.719l0.009-0.008l1.29,1.289l-0.009,0.009L18.08,6.598z M7.035,12.598l-4.72-4.72  L2.306,7.887L1.017,6.598l0.009-0.009L1.017,6.58l1.289-1.289l0.009,0.008l4.72-4.719l1.289,1.29L3.605,6.589l4.719,4.719  L7.035,12.598z">
                            </svg>
                        </div>
                        
                        
                        <div class="arf_form_other_css_wrapper">
                            <textarea id="arf_form_other_css" name="options[arf_form_other_css]" cols="50" rows="4" class="arf_other_css_textarea"><?php echo isset($form_opts['arf_form_other_css']) ? stripslashes_deep($form_opts['arf_form_other_css']) : ''; ?></textarea>
                        </div>
                    </div>
                </div>
                <!-- Custom Color Popup  -->
                <div class="arf_custom_color_popup">
                    <?php
                    $bgColor = (isset($newarr['arfmainformbgcolorsetting']) && $newarr['arfmainformbgcolorsetting'] != '' ) ? esc_attr($newarr['arfmainformbgcolorsetting']) : $skinJson->skins->$active_skin->form->background;
                    $bgColor = (substr($bgColor, 0, 1) != '#') ? '#' . $bgColor : $bgColor;

                    $frmTitleColor = (isset($newarr['arfmainformtitlecolorsetting']) && $newarr['arfmainformtitlecolorsetting'] != '') ? esc_attr($newarr['arfmainformtitlecolorsetting']) : $skinJson->skins->$active_skin->form->title;
                    $frmTitleColor = (substr($frmTitleColor, 0, 1) != '#') ? '#' . $frmTitleColor : $frmTitleColor;
                    
                    $formBrdColor = (isset($newarr['arfmainfieldsetcolor']) && $newarr['arfmainfieldsetcolor'] != "") ? esc_attr($newarr['arfmainfieldsetcolor']) : $skinJson->skins->$active_skin->form->border;
                    $formBrdColor = (substr($formBrdColor, 0, 1) != '#') ? '#' . $formBrdColor : $formBrdColor;

                    $inputBaseColor = (isset($newarr['arfmainbasecolor']) && $newarr['arfmainbasecolor'] != "" ) ? esc_attr($newarr['arfmainbasecolor']) : $skinJson->skins->$active_skin->main;

                    $inputBaseColor = (substr($inputBaseColor,0,1) != '#') ? '#'.$inputBaseColor : $inputBaseColor;

                    
                    $formShadowColor = (isset($newarr['arfmainformbordershadowcolorsetting']) && $newarr['arfmainformbordershadowcolorsetting'] != '') ? esc_attr($newarr['arfmainformbordershadowcolorsetting']) : $skinJson->skins->$active_skin->form->shadow;
                    $formShadowColor = (substr($formShadowColor, 0, 1) != '#') ? '#' . $formShadowColor : $formShadowColor;
                    
                    $formSectionColor = (isset($newarr['arfformsectionbackgroundcolor']) && $newarr['arfformsectionbackgroundcolor'] != '') ? esc_attr($newarr['arfformsectionbackgroundcolor']) : $skinJson->skins->$active_skin->form->section_background;

                    $activePgColor = (isset($newarr['bg_color_pg_break']) && $newarr['bg_color_pg_break']) ? esc_attr($newarr['bg_color_pg_break']) : $skinJson->skins->$active_skin->pagebreak->active_tab;
                    $activePgColor = (substr($activePgColor, 0, 1) != '#') ? '#' . $activePgColor : $activePgColor;

                    $inactivePgColor = (isset($newarr['bg_inavtive_color_pg_break']) && $newarr['bg_inavtive_color_pg_break'] != '' ) ? esc_attr($newarr['bg_inavtive_color_pg_break']) : $skinJson->skins->$active_skin->pagebreak->inactive_tab;
                    $inactivePgColor = (substr($inactivePgColor, 0, 1) != '#') ? '#' . $inactivePgColor : $inactivePgColor;
                    
                    $PgTextColor = ( isset($newarr['text_color_pg_break']) && $newarr['text_color_pg_break'] != '' ) ? esc_attr($newarr['text_color_pg_break']) : $skinJson->skins->$active_skin->pagebreak->text;
                    $PgTextColor = (substr($PgTextColor, 0, 1) != '#') ? '#' . $PgTextColor : $PgTextColor;
                    
                    $labelColor = (isset($newarr['label_color']) && $newarr['label_color'] != '' ) ? esc_attr($newarr['label_color']) : $skinJson->skins->$active_skin->label->text;
                    $labelColor = (substr($labelColor, 0, 1) != '#') ? '#' . $labelColor : $labelColor;
                    
                    $inputTxtColor = ( isset($newarr['text_color']) && $newarr['text_color'] != '' ) ? esc_attr($newarr['text_color']) : $skinJson->skins->$active_skin->input->text;
                    $inputTxtColor = (substr($inputTxtColor, 0, 1) != '#') ? '#' . $inputTxtColor : $inputTxtColor;
                    
                    $iconBgColor = ( isset($newarr['prefix_suffix_bg_color']) && $newarr['prefix_suffix_bg_color'] != '' ) ? esc_attr($newarr['prefix_suffix_bg_color']) : $skinJson->skins->$active_skin->input->prefix_suffix_background;
                    $iconBgColor = (substr($iconBgColor, 0, 1) != '#') ? '#' . $iconBgColor : $iconBgColor;
                    
                    $iconColor = (isset($newarr['prefix_suffix_icon_color']) && $newarr['prefix_suffix_icon_color'] != '' ) ? esc_attr($newarr['prefix_suffix_icon_color']) : $skinJson->skins->$active_skin->input->prefix_suffix_icon_color;
                    $iconColor = (substr($iconColor, 0, 1) != '#') ? '#' . $iconColor : $iconColor;
                    
                    $inputBg = (isset($newarr['bg_color']) && $newarr['bg_color'] != '') ? esc_attr($newarr['bg_color']) : $skinJson->skins->$active_skin->input->background;
                    $inputBg = (substr($inputBg, 0, 1) != '#') ? '#' . $inputBg : $inputBg;
                    
                    $inputActiveBg = ( isset($newarr['arfbgactivecolorsetting']) && $newarr['arfbgactivecolorsetting'] != '' ) ? esc_attr($newarr['arfbgactivecolorsetting']) : $skinJson->skins->$active_skin->input->background_active;
                    $inputActiveBg = (substr($inputActiveBg, 0, 1) != '#') ? '#' . $inputActiveBg : $inputActiveBg;
                    
                    $inputErrorBg = ( isset($newarr['arferrorbgcolorsetting']) && $newarr['arferrorbgcolorsetting'] != '' ) ? esc_attr($newarr['arferrorbgcolorsetting']) : $skinJson->skins->$active_skin->input->background_error;
                    $inputErrorBg = (substr($inputErrorBg, 0, 1) != '#') ? '#' . $inputErrorBg : $inputErrorBg;
                    
                    $inputBrdColor = ( isset($newarr['border_color']) && $newarr['border_color'] != '' ) ? esc_attr($newarr['border_color']) : $skinJson->skins->$active_skin->input->border;
                    $inputBrdColor = (substr($inputBrdColor, 0, 1) != '#') ? '#' . $inputBrdColor : $inputBrdColor;
                    
                    $inputActiveBrd = (isset($newarr['arfborderactivecolorsetting']) && $newarr['arfborderactivecolorsetting'] != '' ) ? esc_attr($newarr['arfborderactivecolorsetting']) : $skinJson->skins->$active_skin->input->border_active;
                    $inputActiveBrd = (substr($inputActiveBrd, 0, 1) != '#') ? '#' . $inputActiveBrd : $inputActiveBrd;
                    
                    $inputErrorBrd = (isset($newarr['arferrorbordercolorsetting']) && $newarr['arferrorbordercolorsetting'] != '' ) ? esc_attr($newarr['arferrorbordercolorsetting']) : $skinJson->skins->$active_skin->input->border_error;
                    $inputErrorBrd = (substr($inputErrorBrd, 0, 1) != '#') ? '#' . $inputErrorBrd : $inputErrorBrd;
                    
                    $submitTxtColor = (isset($newarr['arfsubmittextcolorsetting']) && $newarr['arfsubmittextcolorsetting'] != '' ) ? esc_attr($newarr['arfsubmittextcolorsetting']) : $skinJson->skins->$active_skin->input->text;
                    $submitTxtColor = (substr($submitTxtColor, 0, 1) != '#') ? '#' . $submitTxtColor : $submitTxtColor;
                    
                    $submitBgColor = (isset($newarr['submit_bg_color']) && $newarr['submit_bg_color'] != '' ) ? esc_attr($newarr['submit_bg_color']) : $skinJson->skins->$active_skin->submit->background;
                    $submitBgColor = (substr($submitBgColor, 0, 1) != '#') ? '#' . $submitBgColor : $submitBgColor;
                    
                    $submitHoverBg = (isset($newarr['arfsubmitbuttonbgcolorhoversetting']) && $newarr['arfsubmitbuttonbgcolorhoversetting'] != '' ) ? esc_attr($newarr['arfsubmitbuttonbgcolorhoversetting']) : $skinJson->skins->$active_skin->submit->background_hover;
                    $submitHoverBg = (substr($submitHoverBg, 0, 1) != '#') ? '#' . $submitHoverBg : $submitHoverBg;
                    
                    $submitBrdColor = isset($newarr['arfsubmitbordercolorsetting']) ? esc_attr($newarr['arfsubmitbordercolorsetting']) : $skinJson->skins->$active_skin->submit->border;
                    $submitBrdColor = (substr($submitBrdColor, 0, 1) != '#') ? '#' . $submitBrdColor : $submitBrdColor;
                    
                    $submitShadowColor = ( isset($newarr['arfsubmitshadowcolorsetting']) && $newarr['arfsubmitshadowcolorsetting'] != '' ) ? esc_attr($newarr['arfsubmitshadowcolorsetting']) : $skinJson->skins->$active_skin->submit->shadow;
                    $submitShadowColor = (substr($submitShadowColor, 0, 1) != '#') ? '#' . $submitShadowColor : $submitShadowColor;
                    
                    $successBgColor = ( isset($newarr['arfsucessbgcolorsetting']) && $newarr['arfsucessbgcolorsetting'] != '' ) ? esc_attr($newarr['arfsucessbgcolorsetting']) : $skinJson->skins->$active_skin->success_msg->background;
                    $successBgColor = (substr($successBgColor, 0, 1) != '#') ? '#' . $successBgColor : $successBgColor;
                    
                    $successBrdColor = (isset($newarr['arfsucessbordercolorsetting']) && $newarr['arfsucessbordercolorsetting'] != '') ? esc_attr($newarr['arfsucessbordercolorsetting']) : $skinJson->skins->$active_skin->success_msg->border;
                    $successBrdColor = (substr($successBrdColor, 0, 1) != '#') ? '#' . $successBrdColor : $successBrdColor;
                    
                    $successTxtColor = ( isset($newarr['arfsucesstextcolorsetting']) && $newarr['arfsucesstextcolorsetting'] != '' ) ? esc_attr($newarr['arfsucesstextcolorsetting']) : $skinJson->skins->$active_skin->success_msg->text;
                    $successTxtColor = (substr($successTxtColor, 0, 1) != '#') ? '#' . $successTxtColor : $successTxtColor;

                    $errorBgColor = ( isset($newarr['arfformerrorbgcolorsettings']) && $newarr['arfformerrorbgcolorsettings'] != '' ) ? esc_attr($newarr['arfformerrorbgcolorsettings']) : $skinJson->skins->$active_skin->error_msg->background;
                    $errorBgColor = (substr($errorBgColor,0,1) != '#') ? '#' . $errorBgColor : $errorBgColor;

                    $errorBrdColor = ( isset($newarr['arfformerrorbordercolorsettings']) && $newarr['arfformerrorbordercolorsettings'] != '' ) ? esc_attr($newarr['arfformerrorbordercolorsettings']) : $skinJson->skins->$active_skin->error_msg->border;
                    $errorBrdColor = (substr($errorBrdColor,0,1) != '#') ? '#' . $errorBrdColor : $errorBrdColor;

                    $errorTxtColor = ( isset($newarr['arfformerrortextcolorsettings']) && $newarr['arfformerrortextcolorsettings'] != '') ? esc_attr($newarr['arfformerrortextcolorsettings']) : $skinJson->skins->$active_skin->error_msg->text;
                    $errorTxtColor = (substr($errorTxtColor,0,1) != '#') ? '#' . $errorTxtColor : $errorTxtColor;

                    
                    $checkboxColor = ( isset($newarr['checked_checkbox_icon_color']) && $newarr['checked_checkbox_icon_color'] != '' ) ? esc_attr($newarr['checked_checkbox_icon_color']) : $skinJson->skins->$active_skin->input->checkbox_icon_color;
                    $checkboxColor = (substr($checkboxColor, 0, 1) != '#') ? '#' . $checkboxColor : $checkboxColor;
                    
                    $radioColor = ( isset($newarr['checked_radio_icon_color']) && $newarr['checked_radio_icon_color'] != '' ) ? esc_attr($newarr['checked_radio_icon_color']) : $skinJson->skins->$active_skin->input->radio_icon_color;
                    $radioColor = (substr($radioColor, 0, 1) != '#') ? '#' . $radioColor : $radioColor;
                    
                    $surveyBarColor = ( isset($newarr['bar_color_survey']) && $newarr['bar_color_survey'] != '' ) ? esc_attr($newarr['bar_color_survey']) : $skinJson->skins->$active_skin->survey->bar_color;
                    $surveyBarColor = (substr($surveyBarColor, 0, 1) != '#') ? '#' . $surveyBarColor : $surveyBarColor;
                    
                    $surveyBgColor = ( isset($newarr['bg_color_survey']) && $newarr['bg_color_survey'] != '' ) ? esc_attr($newarr['bg_color_survey']) : $skinJson->skins->$active_skin->survey->background;
                    $surveyBgColor = (substr($surveyBgColor, 0, 1) != '#') ? '#' . $surveyBgColor : $surveyBgColor;
                    
                    $surveyTxtColor = ( isset($newarr['text_color_survey']) && $newarr['text_color_survey'] != '' ) ? esc_attr($newarr['text_color_survey']) : $skinJson->skins->$active_skin->survey->text;
                    $surveyTxtColor = (substr($surveyTxtColor, 0, 1) != '#' ) ? '#' . $surveyTxtColor : $surveyTxtColor;
                    
                    $validationBgColor = ( isset($newarr['arfvalidationbgcolorsetting']) && $newarr['arfvalidationbgcolorsetting'] != '' ) ? esc_attr($newarr['arfvalidationbgcolorsetting']) : (($active_skin != 'custom') ? $skinJson->skins->$active_skin->validation_msg->background : '');
                    $validationBgColor = (substr($validationBgColor, 0, 1) != '#') ? '#' . $validationBgColor : $validationBgColor;
                    
                    $validationTxtColor = ( isset($newarr['arfvalidationtextcolorsetting']) && $newarr['arfvalidationtextcolorsetting'] != '' ) ? esc_attr($newarr['arfvalidationtextcolorsetting']) : (($active_skin != 'custom') ? $skinJson->skins->$active_skin->validation_msg->text : '');
                    $validationTxtColor = (substr($validationTxtColor, 0, 1) != '#') ? '#' . $validationTxtColor : $validationTxtColor;
                    
                    $datepickerBgColor = ( isset($newarr['arfdatepickerbgcolorsetting']) && $newarr['arfdatepickerbgcolorsetting'] != '' ) ? esc_attr($newarr['arfdatepickerbgcolorsetting']) : $skinJson->skins->$active_skin->datepicker->background;
                    $datepickerBgColor = (substr($datepickerBgColor, 0, 1) != '#') ? '#' . $datepickerBgColor : $datepickerBgColor;
                    
                    $datepickerTxtColor = ( isset($newarr['arfdatepickertextcolorsetting']) && $newarr['arfdatepickertextcolorsetting'] != '' ) ? esc_attr($newarr['arfdatepickertextcolorsetting']) : $skinJson->skins->$active_skin->datepicker->text;
                    $datepickerTxtColor = (substr($datepickerTxtColor, 0, 1) != '#') ? '#' . $datepickerTxtColor : $datepickerTxtColor;
                   
                    $uploadBtnTxtColor = ( isset($newarr['arfuploadbtntxtcolorsetting']) && $newarr['arfuploadbtntxtcolorsetting'] != '' ) ? esc_attr($newarr['arfuploadbtntxtcolorsetting']) : $skinJson->skins->$active_skin->uploadbutton->text;
                    $uploadBtnTxtColor = (substr($uploadBtnTxtColor, 0, 1) != '#') ? '#' . $uploadBtnTxtColor : $uploadBtnTxtColor;

                    $uploadBtnBgColor = ( isset($newarr['arfuploadbtnbgcolorsetting']) && $newarr['arfuploadbtnbgcolorsetting'] != '' ) ? esc_attr($newarr['arfuploadbtnbgcolorsetting']) : $skinJson->skins->$active_skin->uploadbutton->background;
                    $uploadBtnBgColor = (substr($uploadBtnBgColor, 0, 1) != '#') ? '#' . $uploadBtnBgColor : $uploadBtnBgColor;

                    $likeBtnColor = ( isset($newarr['arflikebtncolor']) && $newarr['arflikebtncolor'] != "" ) ? esc_attr($newarr['arflikebtncolor']) : $skinJson->skins->$active_skin->input->like_button;
                    $likeBtnColor = (substr($likeBtnColor,0,1) != "#") ? "#".$likeBtnColor : $likeBtnColor; 

                    $dislikeBtnColor = ( isset($newarr['arfdislikebtncolor']) && $newarr['arfdislikebtncolor'] != "" ) ? esc_attr($newarr['arfdislikebtncolor']) : $skinJson->skins->$active_skin->input->dislike_button;
                    $dislikeBtnColor = (substr($dislikeBtnColor,0,1) != "#") ? "#".$dislikeBtnColor : $dislikeBtnColor; 

                    $sliderLeftColor = ( isset($newarr['arfsliderselectioncolor']) && $newarr['arfsliderselectioncolor'] != "" ) ? esc_attr($newarr['arfsliderselectioncolor']) : $skinJson->skins->$active_skin->input->slider_selection_color;
                    $sliderLeftColor = (substr($sliderLeftColor,0,1) != "#") ? "#".$sliderLeftColor : $sliderLeftColor; 

                    $sliderRightColor = ( isset($newarr['arfslidertrackcolor']) && $newarr['arfslidertrackcolor'] != "" ) ? esc_attr($newarr['arfslidertrackcolor']) : $skinJson->skins->$active_skin->input->slider_track_color;
                    $sliderRightColor = (substr($sliderRightColor,0,1) != "#") ? "#".$sliderRightColor : $sliderRightColor;

                    $ratingColor = ( isset($newarr['arfstarratingcolor']) && $newarr['arfstarratingcolor'] != "" ) ? esc_attr($newarr['arfstarratingcolor']) : $skinJson->skins->$active_skin->input->rating_color;
                    $ratingColor = (substr($ratingColor,0,1) != "#") ? "#".$ratingColor : $ratingColor;

                    $allow_section_bg = isset($newarr['arf_divider_inherit_bg']) ? $newarr['arf_divider_inherit_bg'] : 0;
                    ?>
                    <div class="arf_custom_color_popup_header"><?php echo addslashes(esc_html__('Custom Color', 'ARForms')) ?></div>
                    <div class="arf_custom_color_popup_container">
                        <div class="arf_custom_color_popup_table">
                            <div class="arf_custom_color_popup_table_row">
                                <div class="arf_custom_color_popup_left_item" id="form_level_colors"><span><?php echo addslashes(esc_html__('Form', 'ARForms')); ?></span></div>
                                <div class="arf_custom_color_popup_right_item_wrapper">
                                    <div class="arf_custom_color_popup_right_item">

                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfformbgcolorsetting" style="background:<?php echo str_replace('##', '#', $bgColor); ?>;" data-skin="form.background" data-default-color="<?php echo str_replace('##', '#', $bgColor); ?>" jscolor-hash="true" jscolor-onfinechange="arf_update_color(this,'arfformbgcolorsetting')" jscolor-valueelement="arfformbgcolorsetting"></div>
                                        
                                        <input type="hidden" name="arffbcs" id="arfformbgcolorsetting" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $bgColor); ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset~|~background-color","material":".ar_main_div_{arf_form_id} .arf_fieldset~|~background-color"}'  data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_background_color" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Background', 'ARForms')); ?>
                                    </div>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfformtitlecolor" style="background:<?php echo str_replace('##', '#', $frmTitleColor); ?>;" data-skin="form.title" data-default-color="<?php echo str_replace('##', '#', $frmTitleColor); ?>" jscolor-hash="true" jscolor-valueelement="arfformtitlecolor" jscolor-onfinechange="arf_update_color(this,'arfformtitlecolor')"></div>
                                        <input type="hidden" name="arfftc" style="width:100px;" id="arfformtitlecolor" class="hex txtxbox_widget" data-arfstyle="true" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~color||.ar_main_div_{arf_form_id} .arf_fieldset .formdescription_style~|~color","material":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~color||.ar_main_div_{arf_form_id} .arf_fieldset .formdescription_style~|~color"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_title_color" value="<?php echo str_replace('##', '#', $frmTitleColor); ?>" /><?php echo addslashes(esc_html__('Form Title', 'ARForms')); ?>
                                    </div>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfmainfieldsetcolor" style="background:<?php echo str_replace('##', '#', $formBrdColor); ?>;" data-skin="form.border" data-default-color="<?php echo str_replace('##', '#', $formBrdColor); ?>" jscolor-hash="true" jscolor-onfinechange="arf_update_color(this,'arfmainfieldsetcolor')" jscolor-valueelement="arfmainfieldsetcolor"></div>
                                        <input type="hidden" name="arfmfsc" id="arfmainfieldsetcolor" class="hex txtxbox_widget" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset~|~border-color","material":".ar_main_div_{arf_form_id} .arf_fieldset~|~border-color"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_border_color" value="<?php echo str_replace('##', '#', $formBrdColor); ?>" style="width:100px;" /><?php echo addslashes(esc_html__('Border', 'ARForms')); ?>
                                    </div>

                                    <div class="arf_popup_clear"></div>
                                    
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfformbordershadowsetting" data-skin="form.shadow" style="background:<?php echo str_replace('##', '#', $formShadowColor); ?>;" data-default-color="<?php echo str_replace('##', '#', $formShadowColor); ?>" jscolor-hash="true" jscolor-valueelement="arfformbordershadowsetting" jscolor-onfinechange="arf_update_color(this,'arfformbordershadowsetting')"></div>
                                        <input type="hidden" name="arffboss" id="arfformbordershadowsetting" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset~|~box-shadow","property":".ar_main_div_{arf_form_id} .arf_fieldset~|~box-shadow","material":".ar_main_div_{arf_form_id} .arf_fieldset~|~box-shadow","property":".ar_main_div_{arf_form_id} .arf_fieldset~|~box-shadow"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_border_type" class="hex txtxbox_widget" value="<?php echo str_replace('##', '#', $formShadowColor); ?>" style="width:100px;" /> <?php echo addslashes(esc_html__('Shadow', 'ARForms')); ?>
                                    </div>

                                    <div class="arf_popup_clear"></div>

                                    <div class="arf_custom_color_popup_right_item" style="width: 60%;<?php echo (is_rtl()) ? 'margin-right:0px;margin-left:40px;' : 'margin-left:0px;margin-right:40px;'; ?>">
                                        <div class="arf_custom_checkbox_div">
                                            <div class="arf_custom_checkbox_wrapper">
                                                <input type="checkbox" value="1" <?php checked($allow_section_bg,1) ?> id="arf_divider_inherit_bg" name="arf_divider_inherit_bg"/>
                                                <svg width="18px" height="18px">
                                                    <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                                    <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                                </svg>
                                            </div>
                                        </div> 
                                        <label for="arf_divider_inherit_bg" style="<?php echo (is_rtl()) ? 'float: right;text-align: right;margin-right: -3px;position: relative;' : 'float: left;text-align: left;margin-left: -3px;'; ?>margin-top: 3px;"><?php echo addslashes(esc_html__('Section Background','ARForms')); ?></label>
                                    </div>

                                    <div id="arf_allow_section_bg" class="arf_custom_color_popup_right_item <?php if( $allow_section_bg != 1 ){ echo 'arfdisablediv'; } ?>" style="width:15%;">
                                        <div class="arf_custom_color_popup_picker jscolor <?php if( $allow_section_bg != 1 ){ echo 'arfdisablediv'; } ?>" data-fid="arfformsectionbackgroundcolor" data-skin="form.section_background" style="background:<?php echo str_replace('##','#',$formSectionColor); ?>;" data-default-color="<?php echo str_replace('##', '#', $formSectionColor); ?>" jscolor-hash="true" jscolor-valueelement="arfformsectionbackgroundcolor" jscolor-onfinechange="arf_update_color(this,'arfformsectionbackgroundcolor')" id="arf_allow_section_bg_inner"></div>
                                        <input type="hidden" name="arfsecbg" id="arfformsectionbackgroundcolor" class="hex txtxbox_widget" value="<?php echo str_replace('##', '#', $formSectionColor); ?>" style="width:100px;" />
                                    </div>

                                    <div class="arf_popup_clear"></div>
                                </div>
                            </div>
                            <div class="arf_custom_color_popup_table_row">
                                <div class="arf_custom_color_popup_left_item" id="input_colors"><span><?php echo addslashes(esc_html__('Main Input Colors', 'ARForms')); ?></span></div>
                                <div class="arf_custom_color_popup_right_item_wrapper">
                                    
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfmainbasecolor" style="background:<?php echo str_replace("##","#",$inputBaseColor); ?>;" data-default-color="<?php echo str_replace("##","#",$inputBaseColor); ?>" jscolor-onfinechange="arf_update_color(this,'arfmainbasecolor')" jscolor-hash="true" data-skin="input.main" jscolor-valueelement="arfmainbasecolor"></div>
                                        <input type="hidden" name="arfmbsc" data-arfstyle="true" data-arfstyledata='<?php echo json_encode($skinJson->css_main_classes); ?>' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_main_style" value="<?php echo $inputBaseColor; ?>" id="arfmainbasecolor" class="txtxbox_widget hex" style="width:100%;" />
                                        <?php echo addslashes(esc_html__("Base/Active Color","ARForms")); ?>
                                    </div>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arftextcolorsetting" style="background:<?php echo str_replace('##', '#', $inputTxtColor); ?>;" data-skin="input.text" data-default-color="<?php echo str_replace('##', '#', $inputTxtColor); ?>" jscolor-onfinechange="arf_update_color(this,'arftextcolorsetting')" jscolor-hash='true' jscolor-valueelement='arftextcolorsetting'></div>
                                        <input type="hidden" name="arftcs" id="arftextcolorsetting" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text)~|~color||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=password]~|~color||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=email]~|~color||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=number]~|~color||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=url]~|~color||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=tel]~|~color||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~color||.ar_main_div_{arf_form_id} .arf_fieldset .controls .bootstrap-select .dropdown-toggle~|~color||.ar_main_div_{arf_form_id} .arf_fieldset .controls .bootstrap-select .dropdown-toggle:focus~|~color||.ar_main_div_{arf_form_id} .arf_fieldset .controls .bootstrap-select ul li a~|~color||.ar_main_div_{arf_form_id} input[type=text]:not(.arfslider):not(.arf_autocomplete):not(.arf_field_option_input_text):not(.inplace_field)::-webkit-input-placeholder~|~color||.wp-admin .allfields .controls .smaple-textarea::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} .controls textarea::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=password]::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=number]::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=url]::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=tel]::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} select::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=text]:not(.arfslider):not(.arf_autocomplete):not(.arf_field_option_input_text):not(.inplace_field):-moz-placeholder~|~color||.wp-admin .allfields .controls .smaple-textarea:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .controls textarea:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=password]:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=number]:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=url]:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=tel]:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} select:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=text]:not(.arfslider):not(.arf_autocomplete):not(.arf_field_option_input_text):not(.inplace_field)::-moz-placeholder~|~color||.wp-admin .allfields .controls .smaple-textarea::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .controls textarea::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=password]::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=number]::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=url]::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=tel]::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} select::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=text]:not(.arfslider):not(.arf_autocomplete):not(.arf_field_option_input_text):not(.inplace_field):-ms-input-placeholder~|~color||.wp-admin .allfields .controls .smaple-textarea:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .controls textarea:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=password]:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=number]:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=url]:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=tel]:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} select:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=text]:not(.arfslider):not(.arf_autocomplete):not(.arf_field_option_input_text):not(.inplace_field)::-ms-input-placeholder~|~color||.wp-admin .allfields .controls .smaple-textarea::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .controls textarea::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=password]::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=number]::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=url]::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} input[type=tel]::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} select::-ms-input-placeholder~|~color","material":".ar_main_div_{arf_form_id}  .arf_materialize_form .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_autocomplete)~|~color||.ar_main_div_{arf_form_id}  .arf_materialize_form .controls input[type=password]~|~color||.ar_main_div_{arf_form_id}  .arf_materialize_form .controls input[type=email]~|~color||.ar_main_div_{arf_form_id}  .arf_materialize_form .controls input[type=number]~|~color||.ar_main_div_{arf_form_id}  .arf_materialize_form .controls input[type=url]~|~color||.ar_main_div_{arf_form_id}  .arf_materialize_form .controls input[type=tel]~|~color||.ar_main_div_{arf_form_id}  .arf_materialize_form .controls input[type=text].arf-select-dropdown~|~color||.ar_main_div_{arf_form_id}  .arf_materialize_form .controls textarea~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf-select-dropdown~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form ul.arf-select-dropdown li~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form textarea:not(.html_field_description)::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=email]::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=password]::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=number]::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=url]::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=tel]::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form select::-webkit-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete):-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form textarea:not(.html_field_description):-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=email]:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=password]:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=number]:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=url]:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=tel]:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form select:-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form textarea:not(.html_field_description)::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=email]::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=password]::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=number]::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=url]::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=tel]::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form select::-moz-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete):-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form textarea:not(.html_field_description):-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=email]:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=password]:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=number]:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=url]:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=tel]:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form select:-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form textarea:not(.html_field_description)::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=email]::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=password]::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=number]::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=url]::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=tel]::-ms-input-placeholder~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form select::-ms-input-placeholder~|~color"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_input_text_color" value="<?php echo str_replace('##', '#', $inputTxtColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Text Color', 'ARForms')); ?>
                                    </div>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="frm_border_color" style="background:<?php echo str_replace('##', '#', $inputBrdColor); ?>;" data-skin="input.border" data-default-color="<?php echo str_replace('##', '#', $inputBrdColor); ?>" jscolor-onfinechange="arf_update_color(this,'frm_border_color')" jscolor-hash='true' jscolor-valueelement='frm_border_color'></div>
                                        <input type="hidden" name="arffmboc" id="frm_border_color" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $inputBrdColor); ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~border-color||.ar_main_div_{arf_form_id} .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~border-color||.ar_main_div_{arf_form_id} .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~border-color||.ar_main_div_{arf_form_id} .controls input[type=password]~|~border-color||.ar_main_div_{arf_form_id} .controls input[type=email]~|~border-color||.ar_main_div_{arf_form_id} .controls input[type=number]~|~border-color||.ar_main_div_{arf_form_id} .controls input[type=url]~|~border-color||.ar_main_div_{arf_form_id} .controls input[type=tel]~|~border-color||.ar_main_div_{arf_form_id} .controls textarea~|~border-color||.ar_main_div_{arf_form_id} .controls select~|~border-color||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~border-color||.ar_main_div_{arf_form_id} input[type=text].arf_colorpicker:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider), .ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_prefix_editor, .ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_suffix_editor~|~border-color||.ar_main_div_{arf_form_id} .setting_checkbox.arf_standard_checkbox .arf_checkbox_input_wrapper input[type=checkbox]:not(:checked) + span~|~border-color||.ar_main_div_{arf_form_id} .setting_radio.arf_standard_radio .arf_radio_input_wrapper input[type=radio] + span~|~border-color||.ar_main_div_{arf_form_id} .controls .dropdown-toggle .arf_caret~|~border-top-color","material":".ar_main_div_{arf_form_id} .arf_materialize_form .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)~|~border-bottom-color||.ar_main_div_{arf_form_id} .controls input[type=password]~|~border-bottom-color||.ar_main_div_{arf_form_id} .controls input[type=email]~|~border-bottom-color||.ar_main_div_{arf_form_id} .controls input[type=number]~|~border-bottom-color||.ar_main_div_{arf_form_id} .controls input[type=url]~|~border-bottom-color||.ar_main_div_{arf_form_id} .controls input[type=tel]~|~border-bottom-color||.ar_main_div_{arf_form_id} .controls textarea~|~border-bottom-color||.ar_main_div_{arf_form_id} .controls select~|~border-color||.ar_main_div_{arf_form_id} .controls .arfdropdown-menu.open~|~border-color||.ar_main_div_{arf_form_id} .arf_materialize_form .controls textarea~|~border-bottom-color||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text].arf_colorpicker:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider), .ar_main_div_{arf_form_id} .arf_materialize_form .arf_editor_prefix.arf_colorpicker_prefix_editor, .ar_main_div_{arf_form_id} .arf_materialize_form .arf_editor_prefix.arf_colorpicker_suffix_editor~|~border-color||.arf_form_outer_wrapper .setting_checkbox.arf_material_checkbox.arf_default_material .arf_checkbox_input_wrapper input[type=checkbox] + span::after~|~border-color||.arf_form_outer_wrapper .setting_checkbox.arf_material_checkbox.arf_advanced_material .arf_checkbox_input_wrapper input[type=checkbox] + span::before~|~border-color||.arf_form_outer_wrapper .setting_radio.arf_material_radio.arf_default_material .arf_radio_input_wrapper input[type=radio] + span::before~|~border-color||.arf_form_outer_wrapper .setting_radio.arf_material_radio.arf_advanced_material .arf_radio_input_wrapper input[type=radio] + span::before~|~border-color||.ar_main_div_{arf_form_id} .arf_materialize_form .select-wrapper .caret~|~color"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_input_border_color" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Border Color', 'ARForms')); ?>
                                    </div>
                                    
                                    <div class="arf_popup_clear"></div>

                                    <div class="arf_custom_color_popup_right_item <?php echo ($newarr['arfinputstyle'] == 'material') ? 'arfdisablediv' : '';?>">
                                        <div class="arf_custom_color_popup_picker jscolor <?php echo ($newarr['arfinputstyle'] == 'material') ? 'arfdisablediv' : '';?>" data-fid="frm_bg_color" style="background:<?php echo str_replace('##', '#', $inputBg); ?>;" data-skin="input.background" data-default-color="<?php echo str_replace('##', '#', $inputBg); ?>" jscolor-onfinechange="arf_update_color(this,'frm_bg_color')" jscolor-hash='true' jscolor-valueelement='frm_bg_color'></div>
                                        <input type="hidden" name="arffmbc" id="frm_bg_color" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $inputBg); ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~check_field_transparency||.ar_main_div_{arf_form_id} .controls input[type=password]~|~check_field_transparency||.ar_main_div_{arf_form_id} .controls input[type=email]~|~check_field_transparency||.ar_main_div_{arf_form_id} .controls input[type=number]~|~check_field_transparency||.ar_main_div_{arf_form_id} .controls input[type=url]~|~check_field_transparency||.ar_main_div_{arf_form_id} .controls input[type=tel]~|~check_field_transparency||.ar_main_div_{arf_form_id} .controls textarea~|~check_field_transparency||.ar_main_div_{arf_form_id} .controls select~|~check_field_transparency||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~check_field_transparency||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group.open .arfdropdown-menu~|~check_field_transparency||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group.open .arfbtn.dropdown-toggle~|~check_field_transparency","material":".ar_main_div_{arf_form_id} .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text)~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=password]~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=email]~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=number]~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=url]~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=tel]~|~background-color||.ar_main_div_{arf_form_id} .controls textarea~|~background-color||.ar_main_div_{arf_form_id} .controls select~|~background-color"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_input_bg_color" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Background', 'ARForms')); ?>
                                    </div>

                                    <div class="arf_custom_color_popup_right_item  <?php echo ($newarr['arfinputstyle'] == 'material') ? 'arfdisablediv' : '';?>">
                                        <div class="arf_custom_color_popup_picker jscolor  <?php echo ($newarr['arfinputstyle'] == 'material') ? 'arfdisablediv' : '';?>" data-fid="arfbgcoloractivesetting" style="background:<?php echo str_replace('##', '#', $inputActiveBg); ?>;" data-skin="input.background_active" data-default-color="<?php echo str_replace('##', '#', $inputActiveBg); ?>" jscolor-onfinechange="arf_update_color(this,'arfbgcoloractivesetting')" jscolor-hash='true' jscolor-valueelement='arfbgcoloractivesetting'></div>
                                        <input type="hidden" name="arfbcas" id="arfbgcoloractivesetting" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arfmainformfield .controls input:focus~|~check_field_focus_transparency||.ar_main_div_{arf_form_id} textarea:focus:not(.arf_field_option_input_textarea)~|~check_field_focus_transparency||.ar_main_div_{arf_form_id} input:focus:not(.inplace_field):not(.arf_autocomplete):not(.arfslider):not(.arf_field_option_input_text)~|~check_field_focus_transparency||.ar_main_div_{arf_form_id} .controls input[type=text]:focus~|~check_field_focus_transparency||.ar_main_div_{arf_form_id} .controls input[type=text]:focus:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider)~|~check_field_focus_transparency||.ar_main_div_{arf_form_id} .controls input[type=text]:focus:not(.inplace_field):not(.arf_autocomplete):not(.arfslider)~|~check_field_focus_transparency||.ar_main_div_{arf_form_id} .controls input[type=password]:focus~|~check_field_focus_transparency||.ar_main_div_{arf_form_id} .controls input[type=email]:focus~|~check_field_focus_transparency||.ar_main_div_{arf_form_id} .controls input[type=number]:focus~|~check_field_focus_transparency||.ar_main_div_{arf_form_id} .controls input[type=url]:focus~|~check_field_focus_transparency||.ar_main_div_{arf_form_id} .controls input[type=tel]:focus~|~check_field_focus_transparency||.ar_main_div_{arf_form_id} .arfmainformfield .controls textarea:focus~|~check_field_focus_transparency||.ar_main_div_{arf_form_id} .arfmainformfield .controls select:focus~|~check_field_focus_transparency","material":".ar_main_div_{arf_form_id} .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):focus~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=password]:focus~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=email]:focus~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=number]:focus~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=url]:focus~|~background-color||.ar_main_div_{arf_form_id} .controls input[type=tel]:focus~|~background-color||.ar_main_div_{arf_form_id} .arfmainformfield .controls textarea:focus:not(.arf_field_option_input_textarea)~|~background-color||.ar_main_div_{arf_form_id} .arfmainformfield .controls select:focus~|~background-color"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_input_text_focus_bg_color" value="<?php echo str_replace('##', '#', $inputActiveBg); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Active State Background', 'ARForms')); ?>
                                    </div>

                                    <div class="arf_custom_color_popup_right_item <?php echo ($newarr['arfinputstyle'] == 'material') ? 'arfdisablediv' : '';?>">
                                        <div class="arf_custom_color_popup_picker jscolor <?php echo ($newarr['arfinputstyle'] == 'material') ? 'arfdisablediv' : '';?>" data-fid="arfbgerrorcolorsetting" style="background:<?php echo str_replace('##', '#', $inputErrorBg); ?>;" data-skin="input.background_error" data-default-color="<?php echo str_replace('##', '#', $inputErrorBg); ?>" jscolor-hash='true' jscolor-valueelement='arfbgerrorcolorsetting' jscolor-onfinechange="arf_update_color(this,'arfbgerrorcolorsetting')"></div>
                                        <input type="hidden" name="arfbecs" id="arfbgerrorcolorsetting" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $inputErrorBg); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Error State Background', 'ARForms')); ?>
                                    </div>

                                    <div class="arf_popup_clear"></div>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arflabelcolorsetting" style="background:<?php echo str_replace('##', '#', $labelColor); ?>;" data-skin="label.text" data-default-color="<?php echo str_replace('##', '#', $labelColor); ?>" jscolor-onfinechange="arf_update_color(this,'arflabelcolorsetting')" jscolor-hash='true' jscolor-valueelement='arflabelcolorsetting'></div>
                                        <input type="hidden" name="arflcs" id="arflabelcolorsetting" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset label.arf_main_label~|~color||.ar_main_div_{arf_form_id} .arf_fieldset .arf_field_description~|~color||.ar_main_div_{arf_form_id} .arf_checkbox_style label~|~color||.ar_main_div_{arf_form_id} .arf_radiobutton label~|~color||.ar_main_div_{arf_form_id} .bootstrap-datetimepicker-widget table span.month~|~color||.ar_main_div_{arf_form_id} .bootstrap-datetimepicker-widget table span.year:not(.disabled)~|~color||.ar_main_div_{arf_form_id} .bootstrap-datetimepicker-widget table span.decade:not(.disabled)~|~color||.ar_main_div_{arf_form_id} .arf_cal_body span.year~|~color||.ar_main_div_{arf_form_id} .arf_cal_body span.decade:not(.disabled)~|~color||.ar_main_div_{arf_form_id} .arf_cal_body td span.month~|~color||.ar_main_div_{arf_form_id} .datepicker .arf_cal_body .day:not(.old):not(.new)~|~color||.ar_main_div_{arf_form_id} .timepicker .timepicker-hour~|~color||.ar_main_div_{arf_form_id} .timepicker .timepicker-minute~|~color||.ar_main_div_{arf_form_id} .timepicker .arf_cal_hour~|~color||.ar_main_div_{arf_form_id} .timepicker .arf_cal_minute~|~color","material":".ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset label.arf_main_label~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form.arf_fieldset .arf_field_description~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_checkbox_style label~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_radiobutton label~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_checkbox_style label~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_radiobutton label~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body td span.month~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body td span.month:hover~|~border-color||.ar_main_div_{arf_form_id} .datepicker .arf_cal_body .day:not(.old):not(.new)~|~color||.ar_main_div_{arf_form_id} .timepicker .arf_cal_hour~|~color||.ar_main_div_{arf_form_id} .timepicker .arf_cal_minute~|~color||..ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body span.month~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body span.year~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body span.decade:not(.disabled)~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body span.decade:not(.disabled):hover~|~border-color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body span.year:hover~|~border-color"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_label_color" value="<?php echo str_replace('##', '#', $labelColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Label Text Color', 'ARForms')); ?>
                                    </div>
                                    
                                    <div class="arf_custom_color_popup_right_item <?php echo ($newarr['arfinputstyle'] == 'rounded') ? 'arfdisablediv' : '';?>">
                                        <div class="arf_custom_color_popup_picker jscolor <?php echo ($newarr['arfinputstyle'] == 'rounded') ? 'arfdisablediv' : '';?>" data-fid="prefix_suffix_bg_color" style="background:<?php echo str_replace('##', '#', $iconBgColor); ?>;" data-skin="input.prefix_suffix_background" data-default-color="<?php echo str_replace('##', '#', $iconBgColor); ?>" jscolor-hash="true" jscolor-valueelement="prefix_suffix_bg_color" jscolor-onfinechange="arf_update_color(this,'prefix_suffix_bg_color')"></div>
                                        <input type="hidden" name="pfsfsbg" id="prefix_suffix_bg_color" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_standard_form .controls .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~background-color||.ar_main_div_{arf_form_id} .arf_standard_form .controls .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~background-color||.ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_prefix_editor~|~background-color","material":".ar_main_div_{arf_form_id} .controls .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~background-color||.ar_main_div_{arf_form_id} .controls .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~background-color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_colorpicker_prefix_editor~|~background-color"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_icon_bg_color" value="<?php echo str_replace('##', '#', $iconBgColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Icon Background', 'ARForms')); ?>
                                    </div>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="prefix_suffix_icon_color" style="background:<?php echo str_replace('##', '#', $iconColor); ?>;" data-skin="input.prefix_suffix_icon_color" data-default-color="<?php echo str_replace('##', '#', $iconColor); ?>" jscolor-onfinechange="arf_update_color(this,'prefix_suffix_icon_color')" jscolor-hash='true' jscolor-valueelement='prefix_suffix_icon_color'></div>
                                        <input type="hidden" name="pfsfscol" id="prefix_suffix_icon_color" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .controls .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~color||.ar_main_div_{arf_form_id} .controls .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~color||.ar_main_div_{arf_form_id} .arf_editor_prefix.arf_colorpicker_prefix_editor svg path~|~fill","material":".ar_main_div_{arf_form_id} .controls .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~color||.ar_main_div_{arf_form_id} .controls .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_colorpicker_prefix_editor .paint_brush_position svg path~|~fill"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_icon_color" value="<?php echo str_replace('##', '#', $iconColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Icon Color', 'ARForms')); ?>
                                    </div>


                                    <input type="hidden" name="cbscol" id="checked_checkbox_icon_color" class="txtxbox_widget hex" value="<?php echo isset($newarr['checked_checkbox_icon_color']) ? str_replace('##', '#', $newarr['checked_checkbox_icon_color']) : '' ?>" style="width:100px;" />
                                    <input type="hidden" name="rbscol" id="checked_radio_icon_color" class="txtxbox_widget hex" value="<?php echo isset($newarr['checked_radio_icon_color']) ? (str_replace('##', '#', $newarr['checked_radio_icon_color'])) : '' ?>" style="width:100px;" />
                                    <div class="arf_popup_clear"></div>

                                    
                                    <span class="arf_custom_color_popup_subtitle"><?php echo addslashes(esc_html__('Like Button', 'ARForms')); ?></span>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="editor_like_button_color" style="background:<?php echo str_replace("##","#",$likeBtnColor); ?>;" data-skin="input.like_button" jscolor-hash="true" jscolor-valueelement="editor_like_button_color" jscolor-onfinechange="arf_update_color(this,'editor_like_button_color')"></div>
                                        <input type="hidden" name="albclr" id="editor_like_button_color" class="txtxbox_widget" value="<?php echo str_replace("##","#",$likeBtnColor); ?>" style="width:100px" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_like_btn.active~|~background","material":".ar_main_div_{arf_form_id} .arf_like_btn.active~|~background"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_like_button_color" /><?php echo addslashes(esc_html__('Like Button Color','ARForms')); ?>
                                    </div>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="editor_dislike_button_color" style="background:<?php echo str_replace("##","#",$dislikeBtnColor); ?>;" data-skin="input.dislike_button" jscolor-hash="true" jscolor-valueelement="editor_dislike_button_color" jscolor-onfinechange="arf_update_color(this,'editor_dislike_button_color')"></div>
                                        <input type="hidden" name="adlbclr" id="editor_dislike_button_color" class="txtxbox_widget" value="<?php echo str_replace("##","#",$dislikeBtnColor); ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_dislike_btn.active~|~background","material":".ar_main_div_{arf_form_id} .arf_dislike_btn.active~|~background"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_dislike_button_color" style="width:100px" /><?php echo addslashes(esc_html__('Dislike Button Color','ARForms')); ?>
                                    </div>

                                    <div class="arf_popup_clear"></div>

                                    <span class="arf_custom_color_popup_subtitle"><?php echo addslashes(esc_html__('Slider Color','ARForms')); ?></span>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="editor_slider_left_side" style="background:<?php echo str_replace("##","#",$sliderLeftColor); ?>" jscolor-hash="true" data-skin="input.slider_selection_color" jscolor-valueelement="editor_slider_left_side" jscolor-onfinechange="arf_update_color(this,'editor_slider_left_side')"></div>
                                        <input type="hidden" name="asldrsl" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .slider.slider-horizontal .slider-selection~|~background","material":".ar_main_div_{arf_form_id} .slider.slider-horizontal .slider-selection~|~background"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_slider_selection_color" id="editor_slider_left_side" class="txtxbox_widget" value="<?php echo str_replace("##","#",$sliderLeftColor); ?>" style="width:100px;" /><?php echo addslashes(esc_html__("Slider selected","ARForms")); ?>
                                    </div>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="editor_slider_right_side" style="background:<?php echo str_replace("##","#",$sliderRightColor); ?>" jscolor-hash="true" data-skin="input.slider_track_color" jscolor-valueelement="editor_slider_right_side" jscolor-onfinechange="arf_update_color(this,'editor_slider_right_side')"></div>
                                        <input type="hidden" name="asltrcl" id="editor_slider_right_side" class="txtxbox_widget" value="<?php echo str_replace("##","#",$sliderRightColor); ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .slider.slider-horizontal .arf-slider-track~|~background","material":".ar_main_div_{arf_form_id} .slider.slider-horizontal .arf-slider-track~|~background"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_slider_selection_color" style="width:100px;" /><?php echo addslashes(esc_html__("Slider Track","ARForms")); ?>
                                    </div>

                                    <div class='arf_popup_clear'></div>

                                    <span class="arf_custom_color_popup_subtitle"><?php echo addslashes(esc_html__('Star Rating color','ARForms')); ?></span>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="editor_rating_color" style="background:<?php echo str_replace("##","#",$ratingColor); ?>" jscolor-hash="true" data-skin="input.rating_color" jscolor-valueelement="editor_rating_color" jscolor-onfinechange="arf_update_color(this,'editor_rating_color')"></div>
                                        <input type="hidden" name="asclcl" id="editor_rating_color"  data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_star_rating_container input:checked ~ label.arf_star_rating_label svg path~|~fill||.ar_main_div_{arf_form_id} .control-group:not([data-view=arf_disabled]) .arf_star_rating_container label.arf_star_rating_label:hover svg path~|~fill||.ar_main_div_{arf_form_id} .control-group:not([data-view=arf_disabled]) .arf_star_rating_container label.arf_star_rating_label:hover ~ label.arf_star_rating_label svg path~|~fill","material":".ar_main_div_{arf_form_id} .arf_star_rating_container input:checked ~ label.arf_star_rating_label svg path~|~fill||.ar_main_div_{arf_form_id} .control-group:not([data-view=arf_disabled]) .arf_star_rating_container label.arf_star_rating_label:hover svg path~|~fill||.ar_main_div_{arf_form_id} .control-group:not([data-view=arf_disabled]) .arf_star_rating_container label.arf_star_rating_label:hover ~ label.arf_star_rating_label svg path~|~fill"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_rating_colors" class="txtxbox_widget" value="<?php echo str_replace("##","#",$ratingColor); ?>" style="width:100px;" /><?php echo addslashes(esc_html__('Star Rating Color','ARForms')); ?>
                                    </div>

                                    
                                    <div class='arf_popup_clear'></div>

                                    <span class="arf_custom_color_popup_subtitle"><?php echo addslashes(esc_html__('Field Tooltip','ARForms')); ?></span>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arf_tooltip_bg_color" style="background:<?php echo str_replace('##', '#', $newarr['arf_tooltip_bg_color']) ?>;" data-skin="tooltip.background" data-default-color="<?php echo str_replace('##', '#', $newarr['arf_tooltip_bg_color']) ?>;" jscolor-hash='true' jscolor-valueelement="arf_tooltip_bg_color" jscolor-onfinechange="arf_update_color(this,'arf_tooltip_bg_color')"></div>
                                        <input type="hidden" name="arf_tooltip_bg_color" id="arf_tooltip_bg_color" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .arf_tooltip_main~|~background-color","material":".ar_main_div_{arf_form_id} .arf_fieldset .arf_tooltip_main~|~background-color"}'  data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_tooltip_bg_color" value="<?php echo str_replace('##', '#', $newarr['arf_tooltip_bg_color']) ?>" style="width:100px;" onchange="arftooltipinitialization();"/>
                                        <?php echo addslashes(esc_html__('Background', 'ARForms')); ?>
                                    </div>
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arf_tooltip_font_color" style="background:<?php echo str_replace('##', '#', $newarr['arf_tooltip_font_color']) ?>;" data-skin="tooltip.text" data-default-color="<?php echo str_replace('##', '#', $newarr['arf_tooltip_font_color']) ?>;" jscolor-hash='true' jscolor-valueelement='arf_tooltip_font_color' jscolor-onfinechange="arf_update_color(this,'arf_tooltip_font_color')"></div>
                                        <input type="hidden" name="arf_tooltip_font_color" id="arf_tooltip_font_color" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .arf_tooltip_main~|~color","material":".ar_main_div_{arf_form_id} .arf_fieldset .arf_tooltip_main~|~color"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_tooltip_txt_color" value="<?php echo str_replace('##', '#', $newarr['arf_tooltip_font_color']) ?>" style="width:100px;" onchange="arftooltipinitialization();"/>
                                        <?php echo addslashes(esc_html__('Text Color', 'ARForms')); ?>
                                    </div>

                                    <div class='arf_popup_clear'></div>

                                    <span class="arf_custom_color_popup_subtitle"><?php echo addslashes(esc_html__('Other color','ARForms')); ?></span>

                                        <div class="arf_custom_color_popup_right_item">
                                            <div class="arf_custom_color_popup_picker jscolor" data-fid="arfdatepickertextcolorsetting" style="background:<?php echo str_replace('##', '#', $datepickerTxtColor); ?>;" data-skin="datepicker.text" data-default-color="<?php echo str_replace('##', '#', $datepickerTxtColor); ?>" jscolor-onfinechange="arf_update_color(this,'arfdatepickertextcolorsetting')" jscolor-hash='true' jscolor-valueelement='arfdatepickertextcolorsetting'></div>
                                            <input type="hidden" name="arfdtcs" id="arfdatepickertextcolorsetting" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .bootstrap-datetimepicker-widget table span.month~|~color||.ar_main_div_{arf_form_id} .bootstrap-datetimepicker-widget table span.year:not(.disabled)~|~color||.ar_main_div_{arf_form_id} .bootstrap-datetimepicker-widget table span.decade:not(.disabled)~|~color||.ar_main_div_{arf_form_id} .arf_cal_body span.year~|~color||.ar_main_div_{arf_form_id} .arf_cal_body span.decade:not(.disabled)~|~color||.ar_main_div_{arf_form_id} .arf_cal_body td span.month~|~color||.ar_main_div_{arf_form_id} .datepicker .arf_cal_body .day:not(.old):not(.new)~|~color||.ar_main_div_{arf_form_id} .timepicker .timepicker-hour~|~color||.ar_main_div_{arf_form_id} .timepicker .timepicker-minute~|~color||.ar_main_div_{arf_form_id} .timepicker .arf_cal_hour~|~color||.ar_main_div_{arf_form_id} .timepicker .arf_cal_minute~|~color","material":".ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body td span.month~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body td span.month:hover~|~border-color||.ar_main_div_{arf_form_id} .datepicker .arf_cal_body .day:not(.old):not(.new)~|~color||.ar_main_div_{arf_form_id} .timepicker .arf_cal_hour~|~color||.ar_main_div_{arf_form_id} .timepicker .arf_cal_minute~|~color||..ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body span.month~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body span.year~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body span.decade:not(.disabled)~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body span.decade:not(.disabled):hover~|~border-color||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_cal_body span.year:hover~|~border-color"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_datepicker_bgcolor" value="<?php echo str_replace('##', '#', $datepickerTxtColor); ?>" style="width:100px;" />
                                            <?php echo addslashes(esc_html__('Datepicker Text Color', 'ARForms')); ?>
                                        </div>
                                    
                                </div>
                            </div>
                            
                            <div class="arf_custom_color_popup_table_row">
                                <div class="arf_custom_color_popup_left_item" id="submit_button_colors"><span><?php echo addslashes(esc_html__('Submit Button Colors', 'ARForms')); ?></span></div>
                                <div class="arf_custom_color_popup_right_item_wrapper">
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfsubmitbuttontextcolorsetting" style="background:<?php echo str_replace('##', '#', $submitTxtColor); ?>;" data-skin="submit.text" data-default-color="<?php echo str_replace('##', '#', $submitTxtColor); ?>" jscolor-onfinechange="arf_update_color(this,'arfsubmitbuttontextcolorsetting')" jscolor-hash='true' jscolor-valueelement='arfsubmitbuttontextcolorsetting'></div>
                                        <input type="hidden" name="arfsbtcs" id="arfsubmitbuttontextcolorsetting" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~color||.ar_main_div_{arf_form_id} .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_border~|~color||.ar_main_div_{arf_form_id} .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_reverse_border~|~color||.ar_main_div_{arf_form_id} .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_flat~|~color||.arfajax-file-upload~|~color||.arfajax-file-upload-img svg~|~fill","material":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_border~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_reverse_border~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_flat~|~color||.arfajax-file-upload~|~color||.arfajax-file-upload-img svg~|~fill"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_submit_button_color" value="<?php echo str_replace('##', '#', $submitTxtColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Text Color', 'ARForms')); ?>
                                    </div>
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfsubmitbuttonbgcolorsetting" style="background:<?php echo str_replace('##', '#', $submitBgColor); ?>;" data-skin="submit.background" data-default-color="<?php echo str_replace('##', '#', $submitBgColor); ?>" jscolor-hash='true' jscolor-valueelement='arfsubmitbuttonbgcolorsetting' jscolor-onfinechange="arf_update_color(this,'arfsubmitbuttonbgcolorsetting')"></div>
                                        <input type="hidden" name="arfsbbcs" id="arfsubmitbuttonbgcolorsetting" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~background-color||.ar_main_div_{arf_form_id} .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_border~|~border-color||.ar_main_div_{arf_form_id} .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_border~|~color||.ar_main_div_{arf_form_id} .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_reverse_border~|~border-color","material":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~background-color||.ar_main_div_{arf_form_id} .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_border~|~border-color||.ar_main_div_{arf_form_id} .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_border~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_reverse_border~|~border-color"}' data-arfstyleappend="true" data-arfstyleappendid="ar_main_div_{arf_form_id}_submit_button_background_color" value="<?php echo str_replace('##', '#', $submitBgColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Background', "ARForms")); ?>
                                    </div>
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfsubmitbuttoncolorhoversetting" style="background:<?php echo str_replace('##', '#', $submitHoverBg); ?>;" data-skin="submit.background_hover" data-default-color="<?php echo str_replace('##', '#', $submitHoverBg); ?>" jscolor-onfinechange="arf_update_color(this,'arfsubmitbuttoncolorhoversetting')" jscolor-hash='true' jscolor-valueelement='arfsubmitbuttoncolorhoversetting'></div>
                                        <input type="hidden" name="arfsbchs" id="arfsubmitbuttoncolorhoversetting" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn:hover~|~background-color||.ar_main_div_{arf_form_id} .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_border~|~border-color||.ar_main_div_{arf_form_id} .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_border~|~background-color||.ar_main_div_{arf_form_id} .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_reverse_border~|~border-color||.ar_main_div_{arf_form_id} .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_reverse_border~|~color||.ar_main_div_{arf_form_id} .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_flat~|~background-color","material":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn:hover~|~background-color||.ar_main_div_{arf_form_id} .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_border~|~border-color||.ar_main_div_{arf_form_id} .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_border~|~background-color||.ar_main_div_{arf_form_id} .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_reverse_border~|~border-color||.ar_main_div_{arf_form_id} .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_reverse_border~|~color||.ar_main_div_{arf_form_id} .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_flat~|~background-color"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_submit_btn_hover" value="<?php echo str_replace('##', '#', $submitHoverBg); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Hover Background', 'ARForms')); ?>
                                    </div>
                                    <div class="arf_popup_clear"></div>
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfsubmitbuttonbordercolorsetting" style="background:<?php echo str_replace('##', '#', $submitBrdColor); ?>;" data-skin="submit.border" data-default-color="<?php echo str_replace('##', '#', $submitBrdColor); ?>" jscolor-hash='true' jscolor-valueelement='arfsubmitbuttonbordercolorsetting' jscolor-onfinechange="arf_update_color(this,'arfsubmitbuttonbordercolorsetting')"></div>
                                        <input type="hidden" name="arfsbobcs" id="arfsubmitbuttonbordercolorsetting" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn:not(.arf_submit_btn_border):not(.arf_submit_btn_reverse_border)~|~border-color","material":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn:not(.arf_submit_btn_border):not(.arf_submit_btn_reverse_border)~|~border-color"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_submit_btn_border_color" value="<?php echo str_replace('##', '#', $submitBrdColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Border Color', 'ARForms')); ?>
                                    </div>
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfsubmitbuttonshadowcolorsetting" style="background:<?php echo str_replace('##', '#', $submitShadowColor); ?>;" data-skin="submit.shadow" data-default-color="<?php echo str_replace('##', '#', $submitShadowColor); ?>" jscolor-onfinechange="arf_update_color(this,'arfsubmitbuttonshadowcolorsetting')" jscolor-hash='true' jscolor-valueelement='arfsubmitbuttonshadowcolorsetting'></div>
                                        <input type="hidden" name="arfsbscs" id="arfsubmitbuttonshadowcolorsetting" class="txtxbox_widget hex" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn:not(.arf_submit_btn_border):not(.arf_submit_btn_reverse_border)~|~box-shadow","material":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn:not(.arf_submit_btn_border):not(.arf_submit_btn_reverse_border)~|~box-shadow"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_submit_btn_box_shadow" value="<?php echo str_replace('##', '#', $submitShadowColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Shadow Color', 'ARForms')); ?>
                                    </div>
                                    <div class="arf_custom_color_popup_right_item">&nbsp;</div>
                                    <div class="arf_popup_clear"></div>
                                </div>
                            </div>

                            <div class="arf_custom_color_popup_table_row" id="wizard_color_box_wrapper">
                                <div class="arf_custom_color_popup_left_item" id="page_break_colors"><span><?php echo addslashes(esc_html__('Multistep', 'ARForms')); ?></span></div>
                                <div class="arf_custom_color_popup_right_item_wrapper">
                                    
                                    <span class="arf_custom_color_popup_subtitle"><?php echo addslashes(esc_html__('Wizard tabs','ARForms')); ?></span>

                                    <div class="arf_custom_color_popup_right_item ">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="frm_bg_color_pg_break" style="background:<?php echo str_replace('##', '#', $activePgColor); ?>;" data-skin="pagebreak.active_tab" data-default-color="<?php echo str_replace('##', '#', $activePgColor); ?>" jscolor-hash="true" jscolor-valueelement="frm_bg_color_pg_break" jscolor-onfinechange="arf_update_color(this,'frm_bg_color_pg_break')"></div>
                                        <input type="hidden" name="arffbcpb" id="frm_bg_color_pg_break" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $activePgColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Active Tab', 'ARForms')); ?>
                                    </div>
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="frm_bg_inactive_color_pg_break" style="background:<?php echo str_replace('##', '#', $inactivePgColor); ?>;" data-skin="pagebreak.inactive_tab" data-default-color="<?php echo str_replace('##', '#', $inactivePgColor); ?>" jscolor-hash='true' jscolor-valueelement='frm_bg_inactive_color_pg_break' jscolor-onfinechange="arf_update_color(this,'frm_bg_inactive_color_pg_break')"></div>
                                        <input type="hidden" name="arfbicpb" id="frm_bg_inactive_color_pg_break" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $inactivePgColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Inactive Tab', 'ARForms')); ?>
                                    </div>
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="frm_text_color_pg_break" style="background:<?php echo str_replace('##', '#', $PgTextColor); ?>;" data-skin="pagebreak.text" data-default-color="<?php echo str_replace('##', '#', $PgTextColor); ?>" jscolor-hash='true' jscolor-valueelement='frm_text_color_pg_break' jscolor-onfinechange="arf_update_color(this,'frm_text_color_pg_break')"></div>
                                        <input type="hidden" name="arfftcpb" id="frm_text_color_pg_break" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $PgTextColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Text Color', 'ARForms')); ?>
                                    </div>
                                    <div class="arf_popup_clear"></div>

                                    <span class="arf_custom_color_popup_subtitle"><?php echo addslashes(esc_html__('Survey Bar','ARForms')); ?></span>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="frm_bar_color_survey" style="background:<?php echo str_replace('##', '#', $surveyBarColor); ?>;" data-skin="survey.bar_color" data-default-color="<?php echo str_replace('##', '#', $surveyBarColor); ?>" jscolor-hash="true" jscolor-valueelement="frm_bar_color_survey" jscolor-onfinechange="arf_update_color(this,'frm_bar_color_survey')"></div>
                                        <input type="hidden" name="arfbcs" id="frm_bar_color_survey" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $surveyBarColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Bar Color', 'ARForms')); ?>
                                    </div>
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="frm_bg_color_survey" style="background:<?php echo str_replace('##', '#', $surveyBgColor); ?>;" data-skin="survey.background" data-default-color="<?php echo str_replace('##', '#', $surveyBgColor); ?>" jscolor-onfinechange="arf_update_color(this,'frm_bg_color_survey')" jscolor-hash="true" jscolor-valueelement="frm_bg_color_survey"></div>
                                        <input type="hidden" name="arfbgcs" id="frm_bg_color_survey" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $surveyBgColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Background Color', 'ARForms')); ?>
                                    </div>
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="frm_text_color_survey" style="background:<?php echo str_replace('##', '#', $surveyTxtColor); ?>;" data-skin="survey.text" data-default-color="<?php echo str_replace('##', '#', $surveyTxtColor); ?>" jscolor-onfinechange="arf_update_color(this,'frm_text_color_survey')" jscolor-hash='true' jscolor-valueelement='frm_text_color_survey'></div>
                                        <input type="hidden" name="arfftcs" id="frm_text_color_survey" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $surveyTxtColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Text Color', 'ARForms')); ?>
                                    </div>

                                    <div class="arf_popup_clear"></div>
                                </div>
                            </div>
                            
                            <div class="arf_custom_color_popup_table_row">
                                <div class="arf_custom_color_popup_left_item" id="success_message_colors"><span><?php echo addslashes(esc_html__('Success message Colors', 'ARForms')); ?></span></div>
                                <div class="arf_custom_color_popup_right_item_wrapper">
                                    <input type="hidden" name="arfmebs" id="arfmainerrorbgsetting" class="txtxbox_widget hex" value="<?php echo esc_attr($newarr['arferrorbgsetting']) ?>" style="width:100px;" />
                                    <input type="hidden" name="arfmebos" id="arfmainerrotbordersetting" class="txtxbox_widget hex" value="<?php echo esc_attr($newarr['arferrorbordersetting']) ?>" style="width:100px;" />
                                    <input type="hidden" name="arfmets" id="arfmainerrortextsetting" class="txtxbox_widget hex" value="<?php echo esc_attr($newarr['arferrortextsetting']) ?>" style="width:100px;" />
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfmainsucessbgcolorsetting" style="background:<?php echo str_replace('##', '#', $successBgColor); ?>;" data-skin="success_msg.background" data-default-color="<?php echo str_replace('##', '#', $successBgColor); ?>" jscolor-onfinechange="arf_update_color(this,'arfmainsucessbgcolorsetting')" data-checkskin="true" jscolor-hash='true' jscolor-valueelement='arfmainsucessbgcolorsetting'></div>
                                        <input name="arfmsbcs" id="arfmainsucessbgcolorsetting" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $successBgColor); ?>" type="hidden" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Background', 'ARForms')); ?>
                                    </div>
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfmainsucessbordercolorsetting" style="background:<?php echo str_replace('##', '#', $successBrdColor); ?>;" data-skin="success_msg.border" data-default-color="<?php echo str_replace('##', '#', $successBrdColor); ?>" jscolor-onfinechange="arf_update_color(this,'arfmainsucessbordercolorsetting')" data-checkskin="true" jscolor-hash='true' jscolor-valueelement='arfmainsucessbordercolorsetting'></div>
                                        <input type="hidden" name="arfmsbocs" id="arfmainsucessbordercolorsetting" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $successBrdColor); ?>" style="width:100px;" />
                                        <?php echo addslashes(esc_html__("Border", 'ARForms')); ?>
                                    </div>
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfmainsucesstextcolorsetting" style="background:<?php echo str_replace('##', '#', $successTxtColor); ?>;" data-skin="success_msg.text" data-default-color="<?php echo str_replace('##', '#', $successTxtColor); ?>" jscolor-onfinechange="arf_update_color(this,'arfmainsucesstextcolorsetting')" data-checkskin="true" jscolor-hash='true' jscolor-valueelement='arfmainsucesstextcolorsetting'></div>
                                        <input name="arfmstcs" id="arfmainsucesstextcolorsetting" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $successTxtColor); ?>" type="hidden" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Text', 'ARForms')); ?>
                                    </div>
                                    <div class="arf_popup_clear"></div>
                                </div>
                            </div>
                            <div class="arf_custom_color_popup_table_row">
                                <div class="arf_custom_color_popup_left_item" id="error_message_colors"><span><?php echo addslashes(esc_html__("Error Message Colors", "ARForms")); ?></span></div>
                                <div class="arf_custom_color_popup_right_item_wrapper">
                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfformerrorbgcolorsetting" style="background:<?php echo str_replace('##','#', $errorBgColor); ?>" data-skin="error_msg.background" data-default-color="<?php echo str_replace('##','#', $errorBgColor); ?>" jscolor-onfinechange="arf_update_color(this,'arfformerrorbgcolorsetting')" data-checkskin="true" jscolor-hash="true" jscolor-valueelement="arfformerrorbgcolorsetting" ></div>
                                        <input name="arffebgc" id="arfformerrorbgcolorsetting" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $errorBgColor); ?>" type="hidden" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Background','ARForms')); ?>
                                    </div>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfformerrorbordercolorsetting" style="background:<?php echo str_replace('##','#', $errorBrdColor); ?>" data-skin="error_msg.border" data-default-color="<?php echo str_replace('##','#', $errorBrdColor); ?>" jscolor-onfinechange="arf_update_color(this,'arfformerrorbordercolorsetting')" data-checkskin="true" jscolor-hash="true" jscolor-valueelement="arfformerrorbordercolorsetting" ></div>
                                        <input name="arffebrdc" id="arfformerrorbordercolorsetting" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $errorBrdColor); ?>" type="hidden" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Border','ARForms')); ?>
                                    </div>

                                    <div class="arf_custom_color_popup_right_item">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfformerrortextcolorsetting" style="background:<?php echo str_replace('##','#', $errorTxtColor); ?>" data-skin="error_msg.text" data-default-color="<?php echo str_replace('##','#', $errorTxtColor); ?>" jscolor-onfinechange="arf_update_color(this,'arfformerrortextcolorsetting')" data-checkskin="true" jscolor-hash="true" jscolor-valueelement="arfformerrortextcolorsetting" ></div>
                                        <input name="arffetxtc" id="arfformerrortextcolorsetting" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $errorTxtColor); ?>" type="hidden" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Text','ARForms')); ?>
                                    </div>
                                    <div class="arf_popup_clear"></div>
                                </div>
                            </div>
                            <div class="arf_custom_color_popup_table_row">
                                <div class="arf_custom_color_popup_left_item" id="validation_message_colors"><span><?php echo addslashes(esc_html__('Validation Message Colors', 'ARForms')); ?></span></div>
                                <div class="arf_custom_color_popup_right_item_wrapper">
                                    <div class="arf_custom_color_popup_right_item" id="arf_validation_background_color">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfmainvalidationbgcolorsetting" style="background:<?php echo str_replace('##', '#', $validationBgColor); ?>;" data-skin="validation_msg.background" data-default-color="<?php echo str_replace('##', '#', $validationBgColor); ?>" jscolor-hash='true' jscolor-valueelement='arfmainvalidationbgcolorsetting' jscolor-onfinechange="arf_update_color(this,'arfmainvalidationbgcolorsetting')"></div>
                                        <input name="arfmvbcs" id="arfmainvalidationbgcolorsetting" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $validationBgColor); ?>" type="hidden" style="width:100px;" />
                                        <span><?php echo ($newarr['arferrorstyle'] == 'normal') ? addslashes(esc_html__('Color','ARForms')) : addslashes(esc_html__('Background','ARForms')); ?></span>
                                    </div>
                                    <div class="arf_custom_color_popup_right_item" id="arf_validation_text_color" style="<?php echo ($newarr['arferrorstyle'] == 'normal') ? 'display:none;' : 'display:block;'; ?>">
                                        <div class="arf_custom_color_popup_picker jscolor" data-fid="arfmainvalidationtextcolorsetting" style="background:<?php echo str_replace('##', '#', $validationTxtColor); ?>;" data-skin="validation_msg.text" data-default-color="<?php echo str_replace('##', '#', $validationTxtColor); ?>" jscolor-onfinechange="arf_update_color(this,'arfmainvalidationtextcolorsetting')" jscolor-hash='true' jscolor-valueelement='arfmainvalidationtextcolorsetting'></div>
                                        <input name="arfmvtcs" id="arfmainvalidationtextcolorsetting" class="txtxbox_widget hex" value="<?php echo str_replace('##', '#', $validationTxtColor); ?>" type="hidden" style="width:100px;" />
                                        <?php echo addslashes(esc_html__('Text', 'ARForms')); ?>
                                    </div>
                                    <div class="arf_popup_clear"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="arf_custom_color_popup_footer">
                        <div class="arf_custom_color_button_position">
                            <div class="arf_custom_color_button" id="arf_custom_color_save_btn"><div class="arf_imageloader arf_form_style_custom_color_loader" id="arf_custom_color_loader"></div><?php echo addslashes(esc_html__('Apply', 'ARForms')); ?></div>
                            <div class="arf_custom_color_button arf_custom_color_cancel" id="arf_custom_color_cancel_btn"><?php echo addslashes(esc_html__('Cancel', 'ARForms')); ?></div>
                        </div>
                    </div>
                </div>
                <!-- Custom Font Popup -->
                <div class="arf_custom_font_popup">
                    <div class="arf_custom_color_popup_header"><?php echo addslashes(esc_html__('Custom Font Options', 'ARForms')); ?></div>
                    <div class="arf_custom_font_popup_container">
                        <div class="arf_accordion_container_row arf_margin">
                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Form Title Font Settings', 'ARForms')); ?></div>
                        </div>
                        <?php
                        $newarr['check_weight_form_title'] = isset($newarr['check_weight_form_title']) ? $newarr['check_weight_form_title'] : 'normal';
                        $label_font_weight = "";
                        if ($newarr['check_weight_form_title'] != "normal") {
                            $label_font_weight = ", " . $newarr['check_weight_form_title'];
                        }
                        ?>
                        <div class="arf_font_setting_class">
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Family', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right">
                                    <div class="arf_dropdown_wrapper">
                                        <input id="arftitlefontsetting" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .arfeditorformdescription~|~font-family","material":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .arfeditorformdescription~|~font-family"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_title_family" name="arftff" value="<?php echo $newarr['arftitlefontfamily']; ?>" type="hidden" class="arf_custom_font_options" data-default-font="<?php echo $newarr['arftitlefontfamily']; ?>">
                                        <dl class="arf_selectbox" data-name="arftff" data-id="arftitlefontsetting">
                                            <dt><span><?php echo $newarr['arftitlefontfamily']; ?></span>
                                            <input value="<?php echo $newarr['arftitlefontfamily']; ?>" style="display:none;" class="arf_autocomplete" type="text">
                                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                            <dd>
                                                <ul style="display: none;" data-id="arftitlefontsetting">
                                                    <?php arf_font_li_listing(); ?>
                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>

                                </div>
                            </div>
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Size', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right arfwidth63">
                                    <div class="arf_dropdown_wrapper arfmarginleft">
                                        <input id="arfformtitlefontsizesetting" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~font-size","material":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~font-size"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_title_size" name="arfftfss" value="<?php echo $newarr['form_title_font_size']; ?>" type="hidden"  class="arf_custom_font_options" data-default-font="<?php echo $newarr['form_title_font_size']; ?>">
                                        <dl class="arf_selectbox" data-name="arfftfss" data-id="arfformtitlefontsizesetting">
                                            <dt><span><?php echo $newarr['form_title_font_size']; ?></span>
                                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                            <dd>
                                                <ul style="display: none;" data-id="arfformtitlefontsizesetting">
                                                    <?php for ($i = 8; $i <= 20; $i ++) { ?>
                                                        <li class="arf_selectbox_option" data-value="<?php echo $i ?>" data-label="<?php echo $i ?>"><?php echo $i; ?></li>
                                                    <?php } ?>
                                                    <?php for ($i = 22; $i <= 28; $i = $i + 2) { ?>
                                                        <li class="arf_selectbox_option" data-value="<?php echo $i ?>" data-label="<?php echo $i ?>"><?php echo $i; ?></li>
                                                    <?php } ?>
                                                    <?php for ($i = 32; $i <= 60; $i = $i + 4) { ?>
                                                        <li class="arf_selectbox_option" data-value="<?php echo $i ?>" data-label="<?php echo $i ?>"><?php echo $i; ?></li>
                                                    <?php } ?>
                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>
                                    <div class="arfwidthpx" style="<?php echo (is_rtl()) ? 'margin-right: 25px;margin-left: 0px;position:relative;' : 'margin-left: 25px;'; ?>">px</div>
                                </div>
                            </div>
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Style', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right">
                                    <input id="arfformtitleweightsetting" name="arfftws" value="<?php echo $newarr['check_weight_form_title']; ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~font-style","material":".ar_main_div_{arf_form_id} .arf_fieldset .formtitle_style~|~font-style"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_form_title_style" type="hidden" class="arf_custom_font_options arf_custom_font_style" data-default-font="<?php echo $newarr['check_weight_form_title']; ?>" />
                                    <?php $arf_form_title_font_style_arr = explode(',', $newarr['check_weight_form_title']); ?>
                                    <span class="arf_font_style_button <?php echo (in_array('strikethrough', $arf_form_title_font_style_arr)) ? 'active' : ''; ?>" data-style="strikethrough" data-id="arfformtitleweightsetting"><i class="arfa arfa-strikethrough"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('underline', $arf_form_title_font_style_arr)) ? 'active' : ''; ?>" data-style="underline" data-id="arfformtitleweightsetting"><i class="arfa arfa-underline"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('italic', $arf_form_title_font_style_arr)) ? 'active' : ''; ?>" data-style="italic" data-id="arfformtitleweightsetting"><i class="arfa arfa-italic"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('bold', $arf_form_title_font_style_arr)) ? 'active' : ''; ?>" data-style="bold" data-id="arfformtitleweightsetting"><i class="arfa arfa-bold"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="arf_accordion_container_row_separator"></div>
                        <div class="arf_accordion_container_row arf_margin">
                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Label Font Settings', 'ARForms')); ?></div>
                        </div>
                        <?php
                        $label_font_weight = "";
                        if ($newarr['weight'] != "normal") {
                            $label_font_weight = ", " . $newarr['weight'];
                        }
                        ?>
                        <div class="arf_font_setting_class">
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Family', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right">
                                    <div class="arf_dropdown_wrapper">
                                        <input id="arfmainfontsetting" name="arfmfs" value="<?php echo $newarr['font']; ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset label.arf_main_label~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .arf_checkbox_input_wrapper + label~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .arf_radio_input_wrapper + label~|~font-family","material":".ar_main_div_{arf_form_id} .arf_fieldset label.arf_main_label~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .arf_checkbox_input_wrapper + label~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .arf_radio_input_wrapper + label~|~font-family"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_label_font_family" type="hidden"  class="arf_custom_font_options" data-default-font="<?php echo $newarr['font']; ?>">
                                        <dl class="arf_selectbox" data-name="arfmfs" data-id="arfmainfontsetting">
                                            <dt><span><?php echo $newarr['font']; ?></span>
                                            <input value="<?php echo $newarr['font']; ?>" style="display:none;" class="arf_autocomplete" type="text">
                                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                            <dd>
                                                <ul style="display: none;" data-id="arfmainfontsetting">
                                                    <?php arf_font_li_listing(); ?>
                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Size', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right arfwidth63">
                                    <div class="arf_dropdown_wrapper arfmarginleft">
                                        <input id="arffontsizesetting" name="arffss" value="<?php echo $newarr['font_size']; ?>" type="hidden" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset label.arf_main_label~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .arf_checkbox_input_wrapper + label~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .arf_radio_input_wrapper + label~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .arf_radiobutton:not(.arf_enable_radio_image_editor):not(.arf_enable_radio_image)~|~padding-left||.ar_main_div_{arf_form_id} .arf_fieldset .arf_checkbox_style:not(.arf_enable_checkbox_image_editor):not(.arf_enable_checkbox_image)~|~padding-left||.ar_main_div_{arf_form_id} .arf_fieldset .arf_checkbox_style:not(.arf_enable_checkbox_image_editor):not(.arf_enable_checkbox_image) .arf_checkbox_input_wrapper~|~margin-left||.ar_main_div_{arf_form_id} .arf_fieldset .arf_radiobutton:not(.arf_enable_radio_image_editor):not(.arf_enable_radio_image) .arf_radio_input_wrapper~|~margin-left","material":".ar_main_div_{arf_form_id} .arf_fieldset label.arf_main_label~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .arf_checkbox_input_wrapper + label~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .arf_radio_input_wrapper + label~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .arf_checkbox_style:not(.arf_enable_checkbox_image_editor):not(.arf_enable_checkbox_image)~|~padding-left||.ar_main_div_{arf_form_id} .arf_fieldset .arf_radiobutton:not(.arf_enable_radio_image_editor):not(.arf_enable_radio_image)~|~padding-left||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_fieldset .arf_checkbox_style:not(.arf_enable_checkbox_image_editor):not(.arf_enable_checkbox_image) .arf_checkbox_input_wrapper~|~margin-left||.ar_main_div_{arf_form_id} .arf_materialize_form .arf_fieldset .arf_radiobutton:not(.arf_enable_radio_image_editor):not(.arf_enable_radio_image) .arf_radio_input_wrapper~|~margin-left"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_label_font_size" class="arf_custom_font_options" data-default-font="<?php echo $newarr['font_size']; ?>" />
                                        <dl class="arf_selectbox" data-name="arfftfss" data-id="arffontsizesetting">
                                            <dt><span><?php echo $newarr['font_size']; ?></span>
                                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                            <dd>
                                                <ul style="display: none;" data-id="arffontsizesetting">
                                                    <?php for ($i = 8; $i <= 20; $i ++) { ?>
                                                        <li class="arf_selectbox_option" data-value="<?php echo $i ?>" data-label="<?php echo $i ?>"><?php echo $i; ?></li>
                                                    <?php } ?>
                                                    <?php for ($i = 22; $i <= 28; $i = $i + 2) { ?>
                                                        <li class="arf_selectbox_option" data-value="<?php echo $i ?>" data-label="<?php echo $i ?>"><?php echo $i; ?></li>
                                                    <?php } ?>
                                                    <?php for ($i = 32; $i <= 40; $i = $i + 4) { ?>
                                                        <li class="arf_selectbox_option" data-value="<?php echo $i ?>" data-label="<?php echo $i ?>"><?php echo $i; ?></li>
                                                    <?php } ?>
                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>
                                    <div class="arfwidthpx" style="<?php echo (is_rtl()) ? 'margin-right: 25px;margin-left: 0px;position:relative;' : 'margin-left: 25px;'; ?>">px</div>
                                </div>
                            </div>
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Style', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right">
                                    <input id="arfmainfontweightsetting" name="arfmfws" value="<?php echo $newarr['weight']; ?>" type="hidden" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset label.arf_main_label~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_checkbox_input_wrapper + label~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_radio_input_wrapper + label~|~font-style","material":".ar_main_div_{arf_form_id} .arf_fieldset label.arf_main_label~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_checkbox_input_wrapper + label~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .arf_radio_input_wrapper + label~|~font-style"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_label_font_style" class="arf_custom_font_options arf_custom_font_style" data-default-font="<?php echo $newarr['weight']; ?>">
                                    <?php $arf_label_font_style_arr = explode(',', $newarr['weight']); ?>
                                    <span class="arf_font_style_button <?php echo (in_array('strikethrough', $arf_label_font_style_arr)) ? 'active' : ''; ?>" data-style="strikethrough" data-id="arfmainfontweightsetting"><i class="arfa arfa-strikethrough"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('underline', $arf_label_font_style_arr)) ? 'active' : ''; ?>" data-style="underline" data-id="arfmainfontweightsetting"><i class="arfa arfa-underline"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('italic', $arf_label_font_style_arr)) ? 'active' : ''; ?>" data-style="italic" data-id="arfmainfontweightsetting"><i class="arfa arfa-italic"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('bold', $arf_label_font_style_arr)) ? 'active' : ''; ?>" data-style="bold" data-id="arfmainfontweightsetting"><i class="arfa arfa-bold"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="arf_accordion_container_row_separator"></div>
                        <div class="arf_accordion_container_row arf_margin" id="arf_input_font_settings_container">
                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Input Font Settings', 'ARForms')); ?></div>
                        </div>
                        <?php
                        $input_font_weight_html = "";
                        if ($newarr['check_weight'] != "normal") {
                            $input_font_weight_html = ", " . $newarr['check_weight'];
                        }
                        ?>
                        <div class="arf_font_setting_class">
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Family', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right">
                                    <div class="arf_dropdown_wrapper">
                                        <input id="arfcheckboxfontsetting" name="arfcbfs" value="<?php echo $newarr['check_font']; ?>" type="hidden" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=password]~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=email]~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=number]~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=url]~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=tel]~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~font-family||.ar_main_div_101 .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_description~|~font-family||.ar_main_div_{arf_form_id} .arfdropdown-menu > li > a~|~font-family||.ar_main_div_{arf_form_id} .arfajax-file-upload~|~font-family||.ar_main_div_{arf_form_id} .arfajax-file-upload-drag~|~font-family||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~font-family||.ar_main_div_{arf_form_id} .intl-tel-input .country-list~|~font-family","material":".ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text)~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=password]~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=email]~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=number]~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=url]~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=tel]~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=text].arf-select-dropdown~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf-select-dropdown li span~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~font-family||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf_field_description~|~font-family||.ar_main_div_{arf_form_id} .arfajax-file-upload~|~font-family||.ar_main_div_{arf_form_id} .arfajax-file-upload-drag~|~font-family||.ar_main_div_{arf_form_id} .arf_materialize_form input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)~|~font-family||.ar_main_div_{arf_form_id} .intl-tel-input .country-list~|~font-family"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_input_font_family" class="arf_custom_font_options" data-default-font="<?php echo $newarr['check_font']; ?>" />
                                        <dl class="arf_selectbox" data-name="arfcbfs" data-id="arfcheckboxfontsetting">
                                            <dt><span><?php echo $newarr['check_font']; ?></span>
                                            <input value="<?php echo $newarr['check_font']; ?>" style="display:none;" class="arf_autocomplete" type="text">
                                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                            <dd>
                                                <ul style="display: none;" data-id="arfcheckboxfontsetting">
                                                    <?php arf_font_li_listing(); ?>
                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Size', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right arfwidth63">
                                    <div class="arf_dropdown_wrapper arfmarginleft">
                                        <input id="arffieldfontsizesetting" name="arfffss" value="<?php echo $newarr['field_font_size']; ?>" type="hidden" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)~|~font-size||.ar_main_div_{arf_form_id} .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon~|~font-size||.ar_main_div_{arf_form_id} .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=password]~|~font-size||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=email]~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=number]~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=url]~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=tel]~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~font-size||.ar_main_div_{arf_form_id} .arfdropdown-menu > li > a~|~font-size||.ar_main_div_{arf_form_id} .arfajax-file-upload~|~font-size||.ar_main_div_{arf_form_id} .arfajax-file-upload-drag~|~font-size||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~font-size|| .ar_main_div_{arf_form_id} .intl-tel-input .country-list~|~font-size","material":".ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf-select-dropdown):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):not(.arf_autocomplete)~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=password]~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=email]~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=number]~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=url]~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=tel]~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=text].arf-select-dropdown~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf-select-dropdown li span~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~font-size||.ar_main_div_{arf_form_id} .arfajax-file-upload~|~font-size||.ar_main_div_{arf_form_id} .arfajax-file-upload-drag~|~font-size|| .ar_main_div_{arf_form_id} .intl-tel-input .country-list~|~font-size"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_input_font_size" class="arf_custom_font_options" data-default-font="<?php echo $newarr['field_font_size']; ?>" />
                                        <dl class="arf_selectbox" data-name="arfffss" data-id="arffieldfontsizesetting">
                                            <dt><span><?php echo $newarr['field_font_size']; ?></span>
                                           <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                            <dd>
                                                <ul style="display: none;" data-id="arffieldfontsizesetting">
                                                    <?php for ($i = 8; $i <= 20; $i ++) { ?>
                                                        <li class="arf_selectbox_option" data-value="<?php echo $i ?>" data-label="<?php echo $i ?>"><?php echo $i; ?></li>
                                                    <?php } ?>
                                                    <?php for ($i = 22; $i <= 28; $i = $i + 2) { ?>
                                                        <li class="arf_selectbox_option" data-value="<?php echo $i ?>" data-label="<?php echo $i ?>"><?php echo $i; ?></li>
                                                    <?php } ?>
                                                    <?php for ($i = 32; $i <= 40; $i = $i + 4) { ?>
                                                        <li class="arf_selectbox_option" data-value="<?php echo $i ?>" data-label="<?php echo $i ?>"><?php echo $i; ?></li>
                                                    <?php } ?>
                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>
                                    <div class="arfwidthpx" style="<?php echo (is_rtl()) ? 'margin-right: 25px;margin-left: 0px;position:relative;' : 'margin-left: 25px;'; ?>">px</div>
                                </div>
                            </div>
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Style', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right">
                                    <input id="arfcheckboxweightsetting" name="arfcbws" value="<?php echo $newarr['check_weight']; ?>" type="hidden" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .controls input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~font-style||.ar_main_div_101 .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~font-style||.ar_main_div_{arf_form_id} .arfdropdown-menu > li > a~|~font-style||.ar_main_div_{arf_form_id} .arfajax-file-upload~|~font-style||.ar_main_div_{arf_form_id} .arfajax-file-upload-drag~|~font-style||.ar_main_div_{arf_form_id} .sltstandard_front .btn-group .arfbtn.dropdown-toggle~|~font-style||.ar_main_div_{arf_form_id} .intl-tel-input .country-list~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf-select-dropdown li span~|~font-style","material":".ar_main_div_{arf_form_id} .arf_fieldset .controls input:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor)~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls textarea~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls select~|~font-style||.ar_main_div_{arf_form_id} .arfajax-file-upload~|~font-style||.ar_main_div_{arf_form_id} .arfajax-file-upload-drag~|~font-style||.ar_main_div_{arf_form_id} .intl-tel-input .country-list~|~font-style||.ar_main_div_{arf_form_id} .arf_fieldset .controls .arf-select-dropdown li span~|~font-style"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_input_font_style" class="arf_custom_font_options arf_custom_font_style" data-default-font="<?php echo $newarr['check_weight']; ?>" >
                                    <?php $arf_input_font_style_arr = explode(',', $newarr['check_weight']); ?>
                                    <span class="arf_font_style_button <?php echo (in_array('strikethrough', $arf_input_font_style_arr)) ? 'active' : ''; ?>" data-style="strikethrough" data-id="arfcheckboxweightsetting"><i class="arfa arfa-strikethrough"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('underline', $arf_input_font_style_arr)) ? 'active' : ''; ?>" data-style="underline" data-id="arfcheckboxweightsetting"><i class="arfa arfa-underline"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('italic', $arf_input_font_style_arr)) ? 'active' : ''; ?>" data-style="italic" data-id="arfcheckboxweightsetting"><i class="arfa arfa-italic"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('bold', $arf_input_font_style_arr)) ? 'active' : ''; ?>" data-style="bold" data-id="arfcheckboxweightsetting"><i class="arfa arfa-bold"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="arf_accordion_container_row_separator"></div>
                        <div class="arf_accordion_container_row arf_margin">
                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Section Font Settings', 'ARForms')); ?></div>
                        </div>        
                        <div class="arf_font_setting_class">
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Family', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right">
                                    <div class="arf_dropdown_wrapper">
                                        <input id="arfsectiontitlefamily" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .edit_field_type_divider .arfeditorfieldopt_divider_label~|~font-family","material":".ar_main_div_{arf_form_id} .arf_fieldset .edit_field_type_divider .arfeditorfieldopt_divider_label~|~font-family"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_section_title_family" name="arfsectiontitlefamily" value="<?php echo isset($newarr['arfsectiontitlefamily']) ? $newarr['arfsectiontitlefamily'] : 'Helvetica'; ?>" type="hidden" class="arf_custom_font_options" data-default-font="<?php echo isset($newarr['arfsectiontitlefamily']) ? $newarr['arfsectiontitlefamily'] : 'Helvetica'; ?>">
                                        <dl class="arf_selectbox" data-name="arfsectiontitlefamily" data-id="arfsectiontitlefamily">
                                            <dt><span><?php echo isset($newarr['arfsectiontitlefamily']) ? $newarr['arfsectiontitlefamily'] : 'Helvetica'; ?></span>
                                            <input value="<?php echo isset($newarr['arfsectiontitlefamily']) ? $newarr['arfsectiontitlefamily'] : 'Helvetica'; ?>" style="display:none;" class="arf_autocomplete" type="text">
                                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                            <dd>
                                                <ul style="display: none;" data-id="arfsectiontitlefamily">
                                                    <?php arf_font_li_listing(); ?>
                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>

                                </div>
                            </div>
                             <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Size', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right arfwidth63">
                                    <div class="arf_dropdown_wrapper arfmarginleft">
                                        <input id="arfsectiontitlefontsizesetting" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .edit_field_type_divider .arfeditorfieldopt_divider_label~|~font-size","material":".ar_main_div_{arf_form_id} .arf_fieldset .edit_field_type_divider .arfeditorfieldopt_divider_label~|~font-size||.ar_main_div_{arf_form_id} .arf_fieldset label.arf_width_counter_label_divider~|~font-size"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_section_title_size" name="arfsectiontitlefontsizesetting" value="<?php echo isset($newarr['arfsectiontitlefontsizesetting']) ? $newarr['arfsectiontitlefontsizesetting'] : '19'; ?>" type="hidden"  class="arf_custom_font_options" data-default-font="<?php echo isset($newarr['arfsectiontitlefontsizesetting']) ? $newarr['arfsectiontitlefontsizesetting'] : '19'; ?>">
                                        <dl class="arf_selectbox" data-name="arfsectiontitlefontsizesetting" data-id="arfsectiontitlefontsizesetting">
                                            <dt><span><?php echo isset($newarr['arfsectiontitlefontsizesetting']) ? $newarr['arfsectiontitlefontsizesetting'] : '19'; ?></span>
                                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                            <dd>
                                                <ul style="display: none;" data-id="arfsectiontitlefontsizesetting">
                                                    <?php for ($i = 8; $i <= 20; $i ++) { ?>
                                                        <li class="arf_selectbox_option" data-value="<?php echo $i ?>" data-label="<?php echo $i ?>"><?php echo $i; ?></li>
                                                    <?php } ?>
                                                    <?php for ($i = 22; $i <= 28; $i = $i + 2) { ?>
                                                        <li class="arf_selectbox_option" data-value="<?php echo $i ?>" data-label="<?php echo $i ?>"><?php echo $i; ?></li>
                                                    <?php } ?>
                                                    <?php for ($i = 32; $i <= 40; $i = $i + 4) { ?>
                                                        <li class="arf_selectbox_option" data-value="<?php echo $i ?>" data-label="<?php echo $i ?>"><?php echo $i; ?></li>
                                                    <?php } ?>
                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>
                                    <div class="arfwidthpx" style="<?php echo (is_rtl()) ? 'margin-right: 25px;margin-left: 0px;position:relative;' : 'margin-left: 25px;'; ?>">px</div>
                                </div>
                            </div>
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Style', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right">
                                    <input id="arfsectiontitleweightsetting" name="arfsectiontitleweightsetting" value="<?php echo isset($newarr['arfsectiontitleweightsetting']) ? $newarr['arfsectiontitleweightsetting'] : ''; ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arf_fieldset .edit_field_type_divider .arfeditorfieldopt_divider_label~|~font-style","material":".ar_main_div_{arf_form_id} .arf_fieldset .edit_field_type_divider .arfeditorfieldopt_divider_label~|~font-style"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_section_title_style" type="hidden" class="arf_custom_font_options arf_custom_font_style" data-default-font="<?php echo isset($newarr['arfsectiontitleweightsetting']) ? $newarr['arfsectiontitleweightsetting'] : ''; ?>" />
                                    <?php $arf_section_title_font_style_arr = isset($newarr['arfsectiontitleweightsetting']) ? explode(',', $newarr['arfsectiontitleweightsetting']) : array(); ?>
                                    <span class="arf_font_style_button <?php echo (in_array('strikethrough', $arf_section_title_font_style_arr)) ? 'active' : ''; ?>" data-style="strikethrough" data-id="arfsectiontitleweightsetting"><i class="arfa arfa-strikethrough"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('underline', $arf_section_title_font_style_arr)) ? 'active' : ''; ?>" data-style="underline" data-id="arfsectiontitleweightsetting"><i class="arfa arfa-underline"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('italic', $arf_section_title_font_style_arr)) ? 'active' : ''; ?>" data-style="italic" data-id="arfsectiontitleweightsetting"><i class="arfa arfa-italic"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('bold', $arf_section_title_font_style_arr)) ? 'active' : ''; ?>" data-style="bold" data-id="arfsectiontitleweightsetting"><i class="arfa arfa-bold"></i></span>                    
                                </div>
                            </div>
                        </div>
                        <div class="arf_accordion_container_row_separator"></div>
                        <div class="arf_accordion_container_row arf_margin" id="arf_submit_font_settings_container">
                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Submit Font Settings', 'ARForms')); ?></div>
                        </div>
                        <?php
                        $submit_font_weight_html = "";
                        if ($newarr['arfsubmitweightsetting'] != "normal") {
                            $submit_font_weight_html = ", " . $newarr['arfsubmitweightsetting'];
                        }
                        ?>
                        <div class="arf_font_setting_class">
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Family', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right">
                                    <div class="arf_dropdown_wrapper">
                                        <input id="arfsubmitfontfamily" name="arfsff" value="<?php echo $newarr['arfsubmitfontfamily']; ?>" type="hidden" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id} .arfsubmitbutton .arf_submit_btn~|~font-family","material":".ar_main_div_{arf_form_id}  .arfsubmitbutton .arf_submit_btn~|~font-family"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_submit_btn_font_family" class="arf_custom_font_options" data-default-font="<?php echo $newarr['arfsubmitfontfamily']; ?>">
                                        <dl class="arf_selectbox" data-name="arfsff" data-id="arfsubmitfontfamily">
                                            <dt><span><?php echo $newarr['arfsubmitfontfamily']; ?></span>
                                            <input value="<?php echo $newarr['arfsubmitfontfamily']; ?>" style="display:none;" class="arf_autocomplete" type="text">
                                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                            <dd>
                                                <ul style="display: none;" data-id="arfsubmitfontfamily">
                                                    <?php arf_font_li_listing(); ?>
                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Size', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right arfwidth63">
                                    <div class="arf_dropdown_wrapper arfmarginleft">
                                        <input id="arfsubmitbuttonfontsizesetting" name="arfsbfss" value="<?php echo $newarr['arfsubmitbuttonfontsizesetting']; ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id}  .arfsubmitbutton .arf_submit_btn~|~font-size","material":".ar_main_div_{arf_form_id} .arf_materialize_form .arfsubmitbutton .arf_submit_btn~|~font-size"}' data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_submit_btn_font_size" type="hidden" class="arf_custom_font_options" data-default-font="<?php echo $newarr['arfsubmitbuttonfontsizesetting']; ?>" >
                                        <dl class="arf_selectbox" data-name="arfffss" data-id="arfsubmitbuttonfontsizesetting">
                                            <dt><span><?php echo $newarr['arfsubmitbuttonfontsizesetting']; ?></span>
                                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                            <dd>
                                                <ul style="display: none;" data-id="arfsubmitbuttonfontsizesetting">
                                                    <?php for ($i = 8; $i <= 20; $i ++) { ?>
                                                        <li class="arf_selectbox_option" data-value="<?php echo $i ?>" data-label="<?php echo $i ?>"><?php echo $i; ?></li>
                                                    <?php } ?>
                                                    <?php for ($i = 22; $i <= 28; $i = $i + 2) { ?>
                                                        <li class="arf_selectbox_option" data-value="<?php echo $i ?>" data-label="<?php echo $i ?>"><?php echo $i; ?></li>
                                                    <?php } ?>
                                                    <?php for ($i = 32; $i <= 40; $i = $i + 4) { ?>
                                                        <li class="arf_selectbox_option" data-value="<?php echo $i ?>" data-label="<?php echo $i ?>"><?php echo $i; ?></li>
                                                    <?php } ?>
                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>
                                    <div class="arfwidthpx" style="<?php echo (is_rtl()) ? 'margin-right: 25px;margin-left: 0px;position:relative;' : 'margin-left: 25px;'; ?>">px</div>
                                </div>
                            </div>
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Style', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right">
                                    <input id="arfsubmitbuttonweightsetting" name="arfsbwes" value="<?php echo $newarr['arfsubmitweightsetting']; ?>" data-arfstyle="true" data-arfstyledata='{"standard":".ar_main_div_{arf_form_id}  .arfsubmitbutton .arf_submit_btn~|~font-style","material":".ar_main_div_{arf_form_id}  .arfsubmitbutton .arf_submit_btn~|~font-style"}'  data-arfstyleappend="true" data-arfstyleappendid="arf_{arf_form_id}_submit_btn_font_style" type="hidden" class="arf_custom_font_options arf_custom_font_style" data-default-font="<?php echo $newarr['arfsubmitweightsetting']; ?>">
                                    <?php $arf_submit_button_font_style_arr = explode(',', $newarr['arfsubmitweightsetting']); ?>
                                    <span class="arf_font_style_button <?php echo (in_array('strikethrough', $arf_submit_button_font_style_arr)) ? 'active' : ''; ?>" data-style="strikethrough" data-id="arfsubmitbuttonweightsetting"><i class="arfa arfa-strikethrough"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('underline', $arf_submit_button_font_style_arr)) ? 'active' : ''; ?>" data-style="underline" data-id="arfsubmitbuttonweightsetting"><i class="arfa arfa-underline"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('italic', $arf_submit_button_font_style_arr)) ? 'active' : ''; ?>" data-style="italic" data-id="arfsubmitbuttonweightsetting"><i class="arfa arfa-italic"></i></span>
                                    <span class="arf_font_style_button <?php echo (in_array('bold', $arf_submit_button_font_style_arr)) ? 'active' : ''; ?>" data-style="bold" data-id="arfsubmitbuttonweightsetting"><i class="arfa arfa-bold"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="arf_accordion_container_row_separator"></div>
                        <div class="arf_accordion_container_row arf_margin">
                            <div class="arf_accordion_outer_title"><?php echo addslashes(esc_html__('Validation Font Settings', 'ARForms')); ?></div>
                        </div>

                        <div class="arf_font_setting_class">
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Family', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right">
                                    <div class="arf_dropdown_wrapper">
                                        <input id="arfmainerrorfontsetting" name="arfmefs" value="<?php echo $newarr['error_font']; ?>" type="hidden" class="arf_custom_font_options" data-default-font="<?php echo $newarr['error_font']; ?>" >
                                        <dl class="arf_selectbox" data-name="arfmefs" data-id="arfmainerrorfontsetting">
                                            <dt><span><?php echo $newarr['error_font']; ?></span>
                                            <input value="<?php echo $newarr['error_font']; ?>" style="display:none;" class="arf_autocomplete" type="text">
                                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                            <dd>
                                                <ul style="display: none;" data-id="arfmainerrorfontsetting">
                                                    <?php arf_font_li_listing(); ?>
                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                            <div class="arf_font_style_popup_row">
                                <div class="arf_font_style_popup_left"><?php echo addslashes(esc_html__('Size', 'ARForms')); ?></div>
                                <div class="arf_font_style_popup_right arfwidth63">
                                    <div class="arf_dropdown_wrapper arfmarginleft">
                                        <input id="arfmainerrorfontsizesetting" name="arfmefss" value="<?php echo $newarr['arffontsizesetting']; ?>" type="hidden" class="arf_custom_font_options" data-default-font="<?php echo $newarr['arffontsizesetting']; ?>">
                                        <dl class="arf_selectbox" data-name="arfmefss" data-id="arfmainerrorfontsizesetting">
                                            <dt><span><?php echo $newarr['arffontsizesetting']; ?></span>
                                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                            <dd>
                                                <ul style="display: none;" data-id="arfmainerrorfontsizesetting">
                                                    <?php for ($i = 8; $i <= 20; $i ++) { ?>
                                                        <li class="arf_selectbox_option" data-value="<?php echo $i ?>" data-label="<?php echo $i ?>"><?php echo $i; ?></li>
                                                    <?php } ?>
                                                    <?php for ($i = 22; $i <= 28; $i = $i + 2) { ?>
                                                        <li class="arf_selectbox_option" data-value="<?php echo $i ?>" data-label="<?php echo $i ?>"><?php echo $i; ?></li>
                                                    <?php } ?>
                                                    <?php for ($i = 32; $i <= 40; $i = $i + 4) { ?>
                                                        <li class="arf_selectbox_option" data-value="<?php echo $i ?>" data-label="<?php echo $i ?>"><?php echo $i; ?></li>
                                                    <?php } ?>
                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>
                                    <div class="arfwidthpx" style="<?php echo (is_rtl()) ? 'margin-right: 25px;margin-left: 0px;position:relative;' : 'margin-left: 25px;'; ?>">px</div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="arf_custom_font_popup_footer">
                        <div class="arf_custom_font_button_position">
                            <div class="arf_custom_font_button arf_custom_font_save_close" id="arf_custom_font_save_btn"><?php echo addslashes(esc_html__('Apply', 'ARForms')) ?></div>
                            <div class="arf_custom_font_button arf_custom_font_cancel arf_custom_font_close" id="arf_custom_font_cancel_btn"><?php echo addslashes(esc_html__('Cancel', 'ARForms')); ?></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- Auto Response email -->
        <div class="arf_modal_overlay">
            <div id="arf_mail_notification_model" class="arf_popup_container arf_popup_container_mail_notification_model">
                <div class="arf_popup_container_header"><?php echo addslashes(esc_html__('Email Notifications', 'ARForms')); ?>
                <div class="arfpopupclosebutton arfmodalclosebutton" data-dismiss="arfmodal" data-id="arf_mail_notification_popup_button"><svg width="30px" height="30px" viewbox="1 0 20 20"><g id="preview"><path fill-rule="evenodd" clip-rule="evenodd" fill="#262944" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg></div>
                </div>
                <div class="arf_popup_content_container arf_mail_notification_container">
                    <div class="arf_popup_container_loader">
                        <i class="arfas arfa-spinner arfa-spin"></i>
                    </div>
                    <div class="arf_popup_checkbox_wrapper" style="width:100%; margin-bottom:10px;">
                        <?php $values['auto_responder'] = isset($values['auto_responder']) ? $values['auto_responder'] : ''; ?>
                        <div class="arf_custom_checkbox_div">
                            <div class="arf_custom_checkbox_wrapper" onclick="CheckUserAutomaticResponseEnableDisable();" style="margin-right: 9px;">
                                <?php $arf_checked = isset($values['auto_responder']) ? $values['auto_responder'] : 0; ?>
                                <input type="checkbox" name="options[auto_responder]" id="auto_responder" value="1" <?php checked($arf_checked, 1);  ?> />
                                <svg width="18px" height="18px">
                                <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                </svg>
                                <?php unset($arf_checked); ?>
                            </div>
                            <span><label id="arf_auto_responder" for="auto_responder" class="arffont16"><?php echo addslashes(esc_html__('Send an automatic response to users after form submission.', 'ARForms')); ?></label></span>
                        </div>

                         <div style="<?php echo (is_rtl()) ? 'float: left;' : 'float: right;'; ?>">
                            <a href="<?php echo ARFURL; ?>/documentation/index.html#email_notifiaction" target="_blank" title="help" class="arfa arfa-life-bouy arf_adminhelp_icon arfhelptip tipso_style" data-tipso="help"></a>
                        </div>
                    </div>

                    <div class="arf_auto_responder_content arfmarginl10" >
                        <div class="arf_auto_responder_row">
                            <div class="arf_auto_responder_column">
                                <label class="arf_auto_responder_label arf_send_mail_to_label"><?php echo addslashes(esc_html__('Select field to send E-mail', 'ARForms')); ?></label>
                                <?php
                                $auto_responder_disabled = "";
                                if (isset($values['auto_responder']) && $values['auto_responder'] < 1) {
                                    $auto_responder_disabled = "disabled='disabled'";
                                }
                                $selectbox_field_options = "";
                                $selectbox_field_value_label = "";
                                $user_responder_email = "";
                                if (!empty($values['fields'])) {
                                    foreach ($values['fields'] as $val_key => $fo) {
                                        if (in_array($fo['type'], array('email', 'text', 'hidden', 'radio', 'select'))) {
                                            if (($fo["id"] == $values['ar_email_to'])) {
                                                $selectbox_field_value_label = $fo["name"];
                                                $user_responder_email = $values['ar_email_to'];
                                            }

                                            $current_field_id = $fo["id"];
                                            if($current_field_id !="" && $arfieldhelper->arf_execute_function($fo["name"],'strip_tags')=="" ){
                                                $selectbox_field_options .= '<li class="arf_selectbox_option" data-value="' . $current_field_id . '" data-label="[Field id : '.$current_field_id.']">[Field id : '.$current_field_id.']</li>';

                                            }else{
                                                $selectbox_field_options .= '<li class="arf_selectbox_option" data-value="' . $current_field_id . '" data-label="' . $arfieldhelper->arf_execute_function($fo["name"],'strip_tags') . '">' . $arfieldhelper->arf_execute_function($fo["name"],'strip_tags') . '</li>';    
                                            }
                                            
                                        }
                                    }
                                    foreach($all_hidden_fields as $val_key => $fo){
                                        $fo = $arformcontroller->arfObjtoArray($fo);
                                        if(($fo['id'] == $values['ar_email_to']) ){
                                            $selectbox_field_value_label = $fo["name"];
                                            $user_responder_email = $values['ar_email_to'];
                                        }
                                        $current_field_id = $fo["id"];
                                        if($current_field_id !="" && $arfieldhelper->arf_execute_function($fo["name"],'strip_tags')=="" ){
                                                $selectbox_field_options .= '<li class="arf_selectbox_option" data-value="' . $current_field_id . '" data-label="[Field id : '.$current_field_id.']">[Field id : '.$current_field_id.']</li>';

                                            }else{
                                                $selectbox_field_options .= '<li class="arf_selectbox_option" data-value="' . $current_field_id . '" data-label="' . $arfieldhelper->arf_execute_function($fo["name"],'strip_tags') . '">' . $arfieldhelper->arf_execute_function($fo["name"],'strip_tags') . '</li>';    
                                            }
                                    }
                                }
                                $user_responder_email = apply_filters('arf_change_autoresponse_selected_email_value_in_outside', $user_responder_email, $id, $values);
                                $selectbox_field_value_label = apply_filters('arf_change_autoresponse_selected_email_label_in_outside', $selectbox_field_value_label, $id, $values);
                                ?>
                                <input id="options_ar_user_email_to" name="options[ar_email_to]" value="<?php echo ($responder_email != "" && $responder_email != '0') ? $responder_email : $user_responder_email; ?>" type="hidden" <?php echo $auto_responder_disabled; ?>
                                <?php
                                if (isset($values['arf_conditional_enable_mail']) && $values['arf_conditional_enable_mail'] == 1) {
                                    echo $arf_mail_disable = "disabled=disabled";
                                    $arf_mail_disable_class = "arf_disable_selectbox";
                                } else {
                                    echo $arf_mail_disable = "";
                                    $arf_mail_disable_class = "";
                                }
                                ?>
                                       />
                                <dl class="arf_selectbox" data-name="options[ar_email_to]" data-id="options_ar_user_email_to" style="width:80%;margin-top: 7px;">
                                    <dt class="options_ar_user_email_to_dt <?php
                                    if ($auto_responder_disabled != "" || (isset($values['arf_conditional_enable_mail']) && $values['arf_conditional_enable_mail'] == 1)) {
                                        echo 'arf_disable_selectbox';
                                    }
                                    ?>"><span><?php
                                            if ($selectbox_field_value_label != "") {
                                                echo $arfieldhelper->arf_execute_function($selectbox_field_value_label,'strip_tags');
                                            }else if($user_responder_email !="" && $selectbox_field_value_label ==""){
                                                echo '[Field id : '.$user_responder_email.']';

                                            } else {
                                                echo addslashes(esc_html__('Select Field', 'ARForms'));
                                            }
                                            ?></span>
                                    <input value="<?php echo ($responder_email != "") ? $responder_email : $user_responder_email; ?>" style="display:none;width:148px;" class="arf_autocomplete" autocomplete="off" type="text"/>
                                    <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                    <dd>
                                        <ul class="arf_email_field_dropdown" style="display: none;" data-id="options_ar_user_email_to">
                                            <li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?></li>
                                            <?php echo $selectbox_field_options; ?>
                                            <?php do_action('arf_add_autoresponse_email_option_in_out_side', $id, $values); ?>
                                        </ul>
                                    </dd>
                                </dl>
                                <div class="arf_popup_tooltip_main"><img src="<?php echo ARFIMAGESURL ?>/tooltips-icon.png" alt="?" style="margin-left:20px;" class="arfhelptip" title="<?php echo addslashes(esc_html__('Please map desired email field from the list of fields used in your form. And system will send response email to this address.', 'ARForms')) ?>"/></div>

                                <!--Mail redirection starts here.-->
                                <?php
                                if (isset($values['arf_conditional_mail_rules']) && !empty($values['arf_conditional_mail_rules'])) {
                                    $rule_array_conditional_mail_sent = $values['arf_conditional_mail_rules'];
                                } else {
                                    $rule_array_conditional_mail_sent[1]['id_mail'] = '';
                                    $rule_array_conditional_mail_sent[1]['field_id_mail'] = '';
                                    $rule_array_conditional_mail_sent[1]['field_type_mail'] = '';
                                    $rule_array_conditional_mail_sent[1]['value_mail'] = '';
                                    $rule_array_conditional_mail_sent[1]['send_mail_field'] = '';
                                }
                                $total_rule_array_mail = count(array_keys($rule_array_conditional_mail_sent));
                                ?>
                            </div>
                            <div class="arf_auto_responder_column">
                                <label class="arf_auto_responder_label_full"><?php echo addslashes(esc_html__('Subject E-mail', 'ARForms')); ?></label>
                                <?php
                                $ar_email_subject = isset($values['ar_email_subject']) ? $values['ar_email_subject'] : '';
                                $ar_email_subject = $arformhelper->replace_field_shortcode($ar_email_subject);
                                ?>
                                <input type="text" name="options[ar_email_subject]" class="arf_advanceemailfield arfheight34" id="ar_email_subject" value="<?php echo esc_attr($ar_email_subject); ?>" <?php echo $auto_responder_disabled; ?> />

                                <button type="button" class="arf_add_field_button" onclick="add_field_fun('add_field_subject')" id="add_field_email_subject_but" <?php echo $auto_responder_disabled; ?>><?php echo addslashes(esc_html__('Add Field', 'ARForms')); ?>&nbsp;&nbsp;<img src="<?php echo ARFIMAGESURL ?>/down-arrow.png" align="absmiddle" /></button>
                                <div class="arf_main_field_modal">
                                    <div class="arf_add_fieldmodal" id="add_field_subject">
                                        <div class="arf_modal_header">
                                            <div class="arf_add_field_title">
                                                <?php echo addslashes(esc_html__('Fields', 'ARForms')); ?>
                                                <div data-dismiss="arfmodal" onclick="close_add_field_subject('add_field_subject')" class="arf_field_model_close">
                                                    <svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#333333" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arfmodal-body_p">
                                            <?php
                                            if (isset($values['id'])) {
                                                $arfieldhelper->get_shortcode_modal($values['id'], 'ar_email_subject', 'no_email', 'style="width:330px;"', false, $field_list);
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="arf_auto_responder_row" style="margin-bottom: 0px;width:95%;">
                            <div class="arf_or_option"><?php echo addslashes(esc_html__('Or', 'ARForms')) ?></div>
                        </div>
                        <div class="arf_auto_responder_row" style="margin-bottom:20px;">
                            <?php $values['arf_conditional_enable_mail'] = isset($values['arf_conditional_enable_mail']) ? $values['arf_conditional_enable_mail'] : ''; ?>

                            <div class="arf_popup_checkbox_wrapper" >
                                <div class="arf_custom_checkbox_div">
                                    <div class="arf_custom_checkbox_wrapper" onclick="arf_conditional_enable_disable_mail_func();" style="margin-right: 9px;">
                                        <input type="checkbox"  <?php checked($values['arf_conditional_enable_mail'], 1); ?> value="1" id="arf_conditional_enable_disable_mail_id_chkbox" name="options[arf_conditional_enable_mail]">
                                        <svg width="18px" height="18px">
                                        <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                        <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                        </svg>
                                    </div>
                                <span><label for="arf_conditional_enable_disable_mail_id_chkbox" class="arf_auto_responder_label arfhelptip" title="<?php echo esc_html__('Please select options to send an automatic response to user.', 'ARForms'); ?>"><?php echo addslashes(esc_html__('Configure Conditional Email Notification', 'ARForms')); ?></label></span>
                                </div>
                            </div>

                            <?php
                            if (isset($values['arf_conditional_enable_mail']) && $values['arf_conditional_enable_mail'] == 1) {
                                $arf_dispaly_mail_div = "display:block;";
                            } else {
                                $arf_dispaly_mail_div = "display:none;";
                            }
                            ?>
                            <div id="arf_append_mail_add_div" style="<?php echo $arf_dispaly_mail_div ?>">
                                <span class="arfmailsendmailconditional_if"><?php echo addslashes(esc_html__('Send If', 'ARForms')); ?></span>
                                <?php foreach ($rule_array_conditional_mail_sent as $rule_i => $conditional_mail_value) { ?>
                                    <div class="arf_conditional_logic_mail_div" style="<?php echo $arf_dispaly_mail_div ?>" id="arf_rule_conditional_mail_for_delete_<?php echo $rule_i; ?>">
                                        <input type="hidden" value="<?php echo $rule_i; ?>" class="rule_array_conditional_mail_hidden" name="options[arf_conditional_mail_rules][<?php echo $rule_i;?>][id_mail]">

                                        <div class="arf_conditional_logic_div">
                                            <span id="select_ar_conditional_mail_filed_div" class="arf_conditional_logic_div_span">
                                                <div class="sltstandard">
                                                    <?php
                                                    $selectbox_field_options_for_mail = "";
                                                    $selectbox_field_value_label = "";
                                                    $user_responder_mail = "";
                                                    if (!empty($values['fields'])) {
                                                        foreach ($values['fields'] as $val_key => $fo) {

                                                            if ($fo['type'] != 'divider' && $fo['type'] != 'break' && $fo['type'] != 'captcha' && $fo['type'] != 'html' && $fo['type'] != 'password' && $fo['type'] != 'confirm_email') {
                                                                if (($fo["id"] == $conditional_mail_value['field_id_mail'])) {
                                                                    $selectbox_field_value_label = $fo["name"];
                                                                    $user_responder_mail = isset($values['field_id_mail']) ? $values['field_id_mail'] : '';
                                                                }

                                                                $current_field_id = $fo["id"];
                                                                if ($current_field_id !="" && $arfieldhelper->arf_execute_function($fo["name"],'strip_tags') =="") {
                                                                    $selectbox_field_options_for_mail .= '<li class="arf_selectbox_option" data-value="' . $current_field_id . '" data-type="'.$fo['type'].'" data-label="[Field id : '.$current_field_id.']">[Field id : '.$current_field_id.']</li>';

                                                                }else{
                                                                    $selectbox_field_options_for_mail .= '<li class="arf_selectbox_option" data-value="' . $current_field_id . '" data-type="'.$fo['type'].'" data-label="' . $arfieldhelper->arf_execute_function($fo["name"],'strip_tags') . '">' . $arfieldhelper->arf_execute_function($fo["name"],'strip_tags') . '</li>';    
                                                                }
                                                                
                                                            }
                                                        }
                                                    }
                                                    ?>

                                                    <input id="arf_conditional_mail_filed_<?php echo $rule_i; ?>" name="options[arf_conditional_mail_rules][<?php echo $rule_i; ?>][field_id_mail]" value="<?php echo $conditional_mail_value['field_id_mail']; ?>" type="hidden" />

                                                    <input id="arf_conditional_mail_field_type_<?php echo $rule_i; ?>" name="options[arf_conditional_mail_rules][<?php echo $rule_i; ?>][field_type_mail]" value="<?php echo $conditional_mail_value['field_type_mail']; ?>" type="hidden" />

                                                    <dl class="arf_selectbox" data-name="arf_conditional_mail_filed_<?php echo $rule_i; ?>" data-id="arf_conditional_mail_filed_<?php echo $rule_i; ?>" style="width:160px;">
                                                        <dt class="arf_conditional_mail_filed_<?php echo $rule_i; ?>_dt">
                                                        <span>
                                                            <?php
                                                            if ($selectbox_field_value_label != "") {
                                                                echo $arfieldhelper->arf_execute_function($selectbox_field_value_label,'strip_tags');
                                                            }else if($conditional_mail_value['field_id_mail'] !="" && $selectbox_field_value_label==""){
                                                                    echo '[Field id : '.$conditional_mail_value['field_id_mail'].']';

                                                            } else {
                                                                echo addslashes(esc_html__('Select Field', 'ARForms'));
                                                            }
                                                            ?>
                                                        </span>
                                                        <input value="" style="display:none;width:148px;" class="arf_autocomplete" type="text" autocomplete="off">
                                                        <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                                        <dd>
                                                            <ul class="arf_name_field_dropdown arf_conditional_field_dropdown arf_conditional_mail_field_dropdown_ajax" style="display: none;" data-id="arf_conditional_mail_filed_<?php echo $rule_i; ?>">

                                                                <li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?></li>
                                                                <?php echo $selectbox_field_options_for_mail; ?>
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                </div>
                                            </span>
                                            <span class="arf_conditional_filed_is_operator"><?php echo addslashes(esc_html__('is', 'ARForms')); ?></span>

                                            <span id="arf_conditional_filed_mail_operator" class="arf_conditional_filed_mail_operator">
                                                <div class="sltstandard">

                                                    <?php
                                                    $conditional_mail_value['operator_mail'] = isset($conditional_mail_value['operator_mail']) ? $conditional_mail_value['operator_mail'] : "";
                                                    echo $arfieldhelper->arf_cl_rule_for_conditional_email('arf_conditional_filed_mail_operator_' . $rule_i, 'arf_conditional_filed_mail_operator_' . $rule_i, $conditional_mail_value['operator_mail'],$rule_i);
                                                    ?>
                                                </div>
                                            </span>

                                            <span id="select_ar_conditional_mail_value" class="select_ar_conditional_mail_value">
                                                <input style="width:170px;" type="text" class="txtstandardnew arfheight34" value="<?php echo $conditional_mail_value['value_mail']; ?>" id="arf_conditional_filed_mail_value_<?php echo $rule_i; ?>" onkeyup="this.setAttribute('value',this.value)" name="options[arf_conditional_mail_rules][<?php echo $rule_i; ?>][value_mail]" />
                                            </span>


                                                                                       
                                            <?php
                                            $selectbox_field_options_mail = "";
                                            $selectbox_field_value_label = "";
                                            if (!empty($values['fields'])) {
                                                foreach ($values['fields'] as $val_key => $fo) {
                                                    if (in_array($fo['type'], array('email', 'text', 'hidden', 'radio', 'select'))) {
                                                        if (($fo["id"] == $conditional_mail_value['send_mail_field'])) {
                                                            $selectbox_field_value_label = $fo["name"];
                                                        }

                                                        $current_field_id = $fo["id"];
                                                        if($current_field_id !="" && $arfieldhelper->arf_execute_function($fo["name"],'strip_tags')=="" ){
                                                            $selectbox_field_options_mail .= '<li class="arf_selectbox_option" data-value="' . $current_field_id . '" data-label="[Field id : '.$current_field_id.']">[Field id : '.$current_field_id.']</li>';

                                                        }else{
                                                            $selectbox_field_options_mail .= '<li class="arf_selectbox_option" data-value="' . $current_field_id . '" data-label="' . $arfieldhelper->arf_execute_function($fo["name"],'strip_tags') . '">' . $arfieldhelper->arf_execute_function($fo["name"],'strip_tags') . '</li>';    
                                                        }
                                                        
                                                    }
                                                }
                                            }
                                            ?>
                                            <?php if($rule_i == 1){ ?>
                                            <span class="select_ar_conditional_filed_than" id="than_display_title">
                                                <?php echo addslashes(esc_html__('Then Mail Send To', 'ARForms')); ?>
                                            </span>
                                            <?php } ?>
                                            <span  id="select_ar_conditional_filed_span_id" class="arf_first_mail_condition" style="width:180px;">
                                                <div class="sltstandard">

                                                    <input id="arf_conditional_mailto_filed_<?php echo $rule_i; ?>" name="options[arf_conditional_mail_rules][<?php echo $rule_i; ?>][send_mail_field]" value="<?php echo $conditional_mail_value['send_mail_field']; ?>" type="hidden">

                                                    <dl class="arf_selectbox" data-name="arf_conditional_mailto_filed_<?php echo $rule_i; ?>" data-id="arf_conditional_mailto_filed_<?php echo $rule_i; ?>" style="width:211px;">
                                                        <dt class="arf_conditional_mailto_filed_<?php echo $rule_i; ?>_dt">
                                                        <span>
                                                            <?php
                                                            if ($selectbox_field_value_label != "") {
                                                                echo $arfieldhelper->arf_execute_function($selectbox_field_value_label,'strip_tags');
                                                            }else if($conditional_mail_value['send_mail_field'] !="" && $selectbox_field_value_label==""){
                                                                    echo '[Field id : '.$conditional_mail_value['send_mail_field'].']';

                                                            } else {
                                                                echo addslashes(esc_html__('Select Field', 'ARForms'));
                                                            }
                                                            ?>
                                                        </span>
                                                        <input value="" style="display:none;width:148px;" class="arf_autocomplete" type="text" autocomplete="off">
                                                        <i class="arfa arfa-caret-down arfa-lg"></i>
                                                        </dt>
                                                        <dd>
                                                            <ul class="arf_email_field_dropdown arf_second_mail_condition   arf_conditional_field_dropdown" style="display: none;" data-id="arf_conditional_mailto_filed_<?php echo $rule_i; ?>">
                                                                <li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?></li>
                                                                <?php echo $selectbox_field_options_mail; ?>
                                                            </ul>
                                                        </dd>
                                                    </dl>
                                                </div>
                                            </span>
                                            <span class="arf_conditional_mail_bulk_add_remove">
                                                <span class="bulk_add_mail" onclick="arf_conditional_mail_add_function();"><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.134,20.362c-5.521,0-9.996-4.476-9.996-9.996 c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.13,15.887,16.654,20.362,11.134,20.362z M11.133,2.314 c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052 C19.185,5.919,15.579,2.314,11.133,2.314z M12.146,14.341h-2v-3h-3v-2h3V6.372h2v2.969h3v2h-3V14.341z"/></g></svg></span>
                                                    <?php
                                                    if ($total_rule_array_mail > 1) {
                                                        $display_remove = "display:inline-block;";
                                                    } else {
                                                        $display_remove = "display:none;";
                                                    }
                                                    ?>
                                                <span class="bulk_remove_mail" onclick="arf_conditional_delete_mail_rule('<?php echo $rule_i; ?>')" style="<?php echo $display_remove; ?>"><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.12,20.389c-5.521,0-9.996-4.476-9.996-9.996 c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.116,15.913,16.64,20.389,11.12,20.389z M11.119,2.341 c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052C19.17,5.945,15.565,2.341,11.119,2.341z M12.131,11.367h3v-2h-3h-2h-3v2h3H12.131z"/></g></svg></span>
                                            </span> 
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="arf_auto_responder_row">
                            <div class="arf_auto_responder_column">
                                <label class="arf_auto_responder_label_full"><?php echo addslashes(esc_html__('From/Replyto Name', 'ARForms')); ?></label>
                                <input type="text" id="options_ar_user_from_name" name="options[ar_user_from_name]" value="<?php echo (isset($values['ar_user_from_name']) && $values['ar_user_from_name'] != '') ? $values['ar_user_from_name'] : $arfsettings->reply_to_name; ?>" <?php echo $auto_responder_disabled; ?>>
                            </div>

                            <div class="arf_auto_responder_column">
                                <label class="arf_auto_responder_label_full"><?php echo addslashes(esc_html__('From E-mail', 'ARForms')); ?></label>
                                <?php
                                $ar_user_from_email = isset($values['ar_user_from_email']) ? $values['ar_user_from_email'] : '';
                                if ($ar_user_from_email == '')
                                    $ar_user_from_email = $arfsettings->reply_to;
                                else
                                    $ar_user_from_email = $values['ar_user_from_email'];

                                $ar_user_from_email = $arformhelper->replace_field_shortcode($ar_user_from_email);
                                ?>
                                <input type="text" value="<?php echo $ar_user_from_email; ?>" id="ar_user_from_email" name="options[ar_user_from_email]"<?php echo $auto_responder_disabled; ?> class="arf_advanceemailfield" />
                                <button type="button" class="arf_add_field_button" onclick="add_field_fun('add_field_user_email')" id="add_field_user_email_but" <?php echo $auto_responder_disabled; ?>><?php echo addslashes(esc_html__('Add Field', 'ARForms')); ?>&nbsp;&nbsp;
                                    <img src="<?php echo ARFIMAGESURL ?>/down-arrow.png" align="absmiddle" />
                                </button>
                                <div class="arf_main_field_modal <?php echo isset($auto_res_email_cls) ? $auto_res_email_cls : ""; ?>">
                                    <div class="arf_add_fieldmodal" id="add_field_user_email">
                                        <div class="arf_modal_header">
                                            <div class="arf_add_field_title">
                                                <?php echo addslashes(esc_html__('Fields', 'ARForms')); ?>
                                                <div data-dismiss="arfmodal" class="arf_field_model_close" onclick="close_add_field_subject('add_field_user_email')">
                                                    <svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#333333" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="arfmodal-body_email arfmodal-body_p">
                                            <?php
                                            if (isset($values['id'])) {
                                                $arfieldhelper->get_shortcode_modal($values['id'], 'ar_user_from_email', 'email', 'style="width:330px;"', false, $field_list);
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="arf_auto_responder_row">
                            <div class="arf_auto_responder_column">
                                <label class="arf_auto_responder_label_full"><?php echo addslashes(esc_html__('Reply to E-mail', 'ARForms')); ?></label>
                                <?php
                                $ar_user_nreplyto_email = isset($values['ar_user_nreplyto_email']) ? $values['ar_user_nreplyto_email'] : '';

                                if ($ar_user_nreplyto_email == ''){
                                    $ar_user_nreplyto_email = $arfsettings->reply_to_email;
                                } else {
                                    $ar_user_nreplyto_email = $values['ar_user_nreplyto_email'];
                                }

                                $ar_user_nreplyto_email = $arformhelper->replace_field_shortcode($ar_user_nreplyto_email);
                                ?>

                                <input type="text" value="<?php echo $ar_user_nreplyto_email; ?>" id="ar_user_nreplyto_email" name="options[ar_user_nreplyto_email]"<?php echo $auto_responder_disabled; ?> class="arf_advanceemailfield" />

                                <button type="button" class="arf_add_field_button" onclick="add_field_fun('add_field_user_nreplyto_email')" id="add_field_user_nreplyto_email_but" <?php echo $auto_responder_disabled; ?>><?php echo addslashes(esc_html__('Add Field', 'ARForms')); ?>&nbsp;&nbsp;
                                    <img src="<?php echo ARFIMAGESURL ?>/down-arrow.png" align="absmiddle" />
                                </button>

                                <div class="arf_main_field_modal <?php echo isset($auto_res_email_cls) ? $auto_res_email_cls : ""; ?>">
                                    <div class="arf_add_fieldmodal" id="add_field_user_nreplyto_email">
                                        <div class="arf_modal_header">
                                            <div class="arf_add_field_title">
                                                <?php echo addslashes(esc_html__('Fields', 'ARForms')); ?>
                                                <div data-dismiss="arfmodal" class="arf_field_model_close" onclick="close_add_field_subject('add_field_user_nreplyto_email')">
                                                    <svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#333333" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arfmodal-body_email arfmodal-body_p">
                                        <?php
                                        if (isset($values['id'])) {
                                            $arfieldhelper->get_shortcode_modal($values['id'], 'ar_user_nreplyto_email', 'email', 'style="width:330px;"', false, $field_list);
                                        }
                                        ?>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>



                        <div class="arf_auto_responder_row">
                            <div class="arf_width_80">
                                <label class="arf_auto_responder_label_full"><?php echo addslashes(esc_html__('Message', 'ARForms')); ?></label>
                                <?php
                                $ar_email_message = (isset($values['ar_email_message']) and ! empty($values['ar_email_message']) ) ? esc_attr($arformcontroller->br2nl($values['ar_email_message'])) : '';
                                $ar_email_message = $arformhelper->replace_field_shortcode($ar_email_message);

                                $email_editor_settings = array(
                                    'wpautop' => true,
                                    'media_buttons' => false,
                                    'textarea_name' => 'options[ar_email_message]',
                                    'textarea_rows' => '4',
                                    'tinymce' => false,
                                    'editor_class' => "txtmultimodal1 arf_advanceemailfield ar_email_message_content",
                                );

                                wp_editor($ar_email_message, 'ar_email_message', $email_editor_settings);
                                ?>
                                <span class="arferrmessage" id="ar_email_message_error" style="top:0px;"><?php echo addslashes(esc_html__('This field cannot be blank','ARForms')); ?></span>
                                <textarea style="display:none;opacity: 0; width:0; height: 0" name="options[ar_email_message]" id="ar_email_message_text"><?php echo $ar_email_message; ?></textarea>
                            </div>
                            <div class="arf_width_20">
                                <button type="button" class="arf_add_field_button" onclick="add_field_fun('add_field_message')" id="add_field_message_but" <?php echo $auto_responder_disabled; ?>><?php echo addslashes(esc_html__('Add Field', 'ARForms')); ?>&nbsp;&nbsp;
                                    <img src="<?php echo ARFIMAGESURL ?>/down-arrow.png" align="absmiddle" />
                                </button>
                                <div class="arf_main_field_modal" style="top:36px;">
                                    <div class="arf_add_fieldmodal" id="add_field_message">
                                        <div class="arf_modal_header">
                                            <div class="arf_add_field_title">
                                                <?php echo addslashes(esc_html__('Fields', 'ARForms')); ?>
                                                <div data-dismiss="arfmodal" class="arf_field_model_close" onclick="close_add_field_subject('add_field_message')">
                                                    <svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#333333" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="arfmodal-body_p">
                                            <?php
                                            if (isset($values['id'])) {                                                
                                                $arfieldhelper->get_shortcode_modal($values['id'], 'ar_email_message', 'no_email', 'style="width:330px;"', false, $field_list);
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="clear: both;"></div>
                            <div style="margin-top: 5px;">
                                <div><label><code>[ARF_form_all_values]</code> - <?php echo addslashes(esc_html__('This will be replaced with form\'s all fields & labels.', 'ARForms')); ?></label></div>
                                <div><label><code>[ARF_form_referer]</code> - <?php echo esc_html__('This will be replaced with entry referer.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_form_added_date_time]</code> - <?php echo esc_html__('This will be replaced with entry added time.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_form_ipaddress]</code> - <?php echo esc_html__('This will be replaced with IP Address.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_form_browsername]</code> - <?php echo esc_html__('This will be replaced with user browser name.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_form_entryid]</code> - <?php echo esc_html__('This will be replaced with Entry ID.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_current_userid]</code> - <?php echo esc_html__('This will be replaced with current login ID.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_current_username]</code> - <?php echo esc_html__('This will be replaced with current login user name.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_current_useremail]</code> - <?php echo esc_html__('This will be replaced with current login user email.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_page_url]</code> - <?php echo esc_html__('This will be replaced with current form\'s page URL.', 'ARForms'); ?></label></div>

                                <?php do_action('arf_add_auto_response_mail_shortcode_in_out_side', $id, $values); ?>
                            </div>
                        </div>
                    </div>

                    <div class="arf_separater"></div>
                    <div class="arf_popup_checkbox_wrapper">
                        <div class="arf_custom_checkbox_div">
                            <div class="arf_custom_checkbox_wrapper" onclick="CheckAdminAutomaticResponseEnableDisable();" style="margin-right: 9px;">
                                <?php $arf_checked = isset($values['chk_admin_notification']) ? $values['chk_admin_notification'] : 0; ?>
                                <input type="checkbox" name="options[chk_admin_notification]" id="chk_admin_notification" value="1" <?php checked($arf_checked, 1); ?>  />
                                <svg width="18px" height="18px">
                                <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                <?php unset($arf_checked); ?>
                                </svg>
                            </div>
                        <span><label id="arf_admin_notification" for="chk_admin_notification" class="arffont16"><?php echo esc_html__('Send an automatic response to admin user after form submission.', 'ARForms'); ?></label></span>
                        </div>
                    </div>

                    <div class="arf_admin_notification_content arfmarginl10" style="width:100%;">
                        <div class="arf_auto_responder_row ">
                            <div class="arf_auto_responder_column">
                                <?php
                                $chk_admin_notification_disabled = "disabled='disabled'";
                                if (isset($values['chk_admin_notification']) && $values['chk_admin_notification'] > 0) {
                                    $chk_admin_notification_disabled = "";
                                    
                                }
                                $ar_admin_to_email = isset($values['notification'][0]['reply_to']) ? esc_attr($values['notification'][0]['reply_to']) : '';
                                if ($ar_admin_to_email == '') {
                                    $ar_admin_to_email = $arfsettings->reply_to;
                                } else {
                                    $ar_admin_to_email = $values['notification'][0]['reply_to'];
                                }
                                $ar_admin_to_email = $arformhelper->replace_field_shortcode($ar_admin_to_email);
                                ?>
                                <label class="arf_auto_responder_label_full"><?php echo addslashes(esc_html__('Admin E-mail', 'ARForms')); ?></label>
                                <input type="text" name="options[reply_to]" id="options_admin_reply_to_notification" value="<?php echo $ar_admin_to_email; ?>" <?php echo $chk_admin_notification_disabled; ?> class="arf_advanceemailfield" />
                                <button type="button" class="arf_add_field_button" onclick="add_field_fun('add_field_admin_email_to')" id="add_field_admin_email_but_to"  <?php echo $chk_admin_notification_disabled; ?> ><?php echo addslashes(esc_html__('Add Field', 'ARForms')); ?>&nbsp;&nbsp;<img src="<?php echo ARFIMAGESURL ?>/down-arrow.png" align="absmiddle" /></button>
                                <div class="arf_main_field_modal">
                                    <div class="arf_add_fieldmodal" id="add_field_admin_email_to">
                                        <div class="arf_modal_header">
                                            <div class="arf_add_field_title">
                                                <?php echo addslashes(esc_html__('Fields', 'ARForms')); ?>
                                                <div data-dismiss="arfmodal" class="arf_field_model_close" onclick="close_add_field_subject('add_field_admin_email_to')">
                                                    <svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#333333" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arfmodal-body_email arfmodal-body_p">
                                            <?php isset($values['id']) ? $arfieldhelper->get_shortcode_modal($values['id'], 'options_admin_reply_to_notification', 'email', 'style="width:330px;"', false, $field_list) : ''; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="arf_auto_responder_column">
                                <label class="arf_auto_responder_label_full"><?php echo addslashes(esc_html__('Subject E-mail', 'ARForms')); ?></label>
                                <?php
                                $admin_email_subject_value = (isset($values['admin_email_subject'])) ? esc_attr($values['admin_email_subject']) : '';
                                if ($admin_email_subject_value == '') {
                                    $admin_email_subject_value = '[form_name] Form submitted on [site_name]';
                                } else {
                                    $admin_email_subject_value = $values['admin_email_subject'];
                                }
                                ?>
                                <input type="text" name="options[admin_email_subject]" id="admin_email_subject" value="<?php echo $admin_email_subject_value; ?>" <?php echo $chk_admin_notification_disabled; ?> class="arf_advanceemailfield" />
                                <button type="button" class="arf_add_field_button" onclick="add_field_fun('add_field_admin_email_subject')" id="add_field_admin_email_but_subject"  <?php echo $chk_admin_notification_disabled; ?>><?php echo addslashes(esc_html__('Add Field', 'ARForms')); ?>&nbsp;&nbsp;<img src="<?php echo ARFIMAGESURL ?>/down-arrow.png" align="absmiddle" /></button>
                                <div class="arf_main_field_modal">
                                    <div class="arf_add_fieldmodal" id="add_field_admin_email_subject">
                                        <div class="arf_modal_header">
                                            <div class="arf_add_field_title">
                                                <?php echo addslashes(esc_html__('Fields', 'ARForms')); ?>
                                                <div data-dismiss="arfmodal" class="arf_field_model_close" onclick="close_add_field_subject('add_field_admin_email_subject')">
                                                    <svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#333333" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arfmodal-body_email arfmodal-body_p">
                                            <?php isset($values['id']) ? $arfieldhelper->get_shortcode_modal($values['id'], 'admin_email_subject', 'email', 'style="width:330px;"', false, $field_list) : ''; ?>
                                        </div>
                                    </div>
                                </div>
                                <div style="margin-top: 5px;">
                                    <div><label><code>[form_name]</code> - <?php echo addslashes(esc_html__('This will be replaced with form name.', 'ARForms')); ?></label></div>
                                    <div><label><code>[site_name]</code> - <?php echo addslashes(esc_html__('This will be replaced with name of site.', 'ARForms')); ?></label></div>
                                </div>
                            </div>
                        </div>
                        <div class="arf_auto_responder_row">
                           <div class="arf_auto_responder_column">
                                <?php
                                $chk_admin_notification_disabled = "disabled='disabled'";
                                if (isset($values['chk_admin_notification']) && $values['chk_admin_notification'] > 0) {
                                    $chk_admin_notification_disabled = "";
                                    
                                }
                                
                                $ar_admin_cc_email = isset($values['admin_cc_email']) ? esc_attr($values['admin_cc_email']) : '';
                                if ($ar_admin_cc_email == '') {
                                    $ar_admin_cc_email = '';
                                } else {
                                    $ar_admin_cc_email = $values['admin_cc_email'];
                                }
                                $ar_admin_cc_email = $arformhelper->replace_field_shortcode($ar_admin_cc_email);
                                ?>
                                <label class="arf_auto_responder_label_full"><?php echo addslashes(esc_html__('Admin CC Email', 'ARForms')); ?></label>
                                <input type="text" name="options[admin_cc_email]" id="options_admin_cc_email_notification" value="<?php echo $ar_admin_cc_email; ?>" <?php echo $chk_admin_notification_disabled; ?> class="arf_advanceemailfield" />
                                <button type="button" class="arf_add_field_button" onclick="add_field_fun('add_field_admin_cc_email')" id="add_field_admin_cc_email_but_to"  <?php echo $chk_admin_notification_disabled; ?> ><?php echo addslashes(esc_html__('Add Field', 'ARForms')); ?>&nbsp;&nbsp;<img src="<?php echo ARFIMAGESURL ?>/down-arrow.png" align="absmiddle" /></button>
                                <div class="arf_main_field_modal">
                                    <div class="arf_add_fieldmodal" id="add_field_admin_cc_email">
                                        <div class="arf_modal_header">
                                            <div class="arf_add_field_title">
                                                <?php echo addslashes(esc_html__('Fields', 'ARForms')); ?>
                                                <div data-dismiss="arfmodal" class="arf_field_model_close" onclick="close_add_field_subject('add_field_admin_cc_email')">
                                                    <svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#333333" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arfmodal-body_email arfmodal-body_p">
                                            <?php isset($values['id']) ? $arfieldhelper->get_shortcode_modal($values['id'], 'options_admin_cc_email_notification', 'email', 'style="width:330px;"', false, $field_list) : ''; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                           <div class="arf_auto_responder_column">
                                <?php
                                $chk_admin_notification_disabled = "disabled='disabled'";
                                if (isset($values['chk_admin_notification']) && $values['chk_admin_notification'] > 0) {
                                    $chk_admin_notification_disabled = "";
                                    
                                }
                                $ar_admin_bcc_email = isset($values['admin_bcc_email']) ? esc_attr($values['admin_bcc_email']) : '';
                                if ($ar_admin_bcc_email == '') {
                                    $ar_admin_bcc_email = '';
                                } else {
                                    $ar_admin_bcc_email = $values['admin_bcc_email'];
                                }
                                $ar_admin_bcc_email = $arformhelper->replace_field_shortcode($ar_admin_bcc_email);
                                ?>
                                <label class="arf_auto_responder_label_full"><?php echo addslashes(esc_html__('Admin BCC Email', 'ARForms')); ?></label>
                                <input type="text" name="options[admin_bcc_email]" id="options_admin_bcc_email_notification" value="<?php echo $ar_admin_bcc_email; ?>" <?php echo $chk_admin_notification_disabled; ?> class="arf_advanceemailfield" />
                                <button type="button" class="arf_add_field_button" onclick="add_field_fun('add_field_admin_bcc_email')" id="add_field_admin_bcc_email_but_to"  <?php echo $chk_admin_notification_disabled; ?> ><?php echo addslashes(esc_html__('Add Field', 'ARForms')); ?>&nbsp;&nbsp;<img src="<?php echo ARFIMAGESURL ?>/down-arrow.png" align="absmiddle" /></button>
                                <div class="arf_main_field_modal">
                                    <div class="arf_add_fieldmodal" id="add_field_admin_bcc_email">
                                        <div class="arf_modal_header">
                                            <div class="arf_add_field_title">
                                                <?php echo addslashes(esc_html__('Fields', 'ARForms')); ?>
                                                <div data-dismiss="arfmodal" class="arf_field_model_close" onclick="close_add_field_subject('add_field_admin_bcc_email')">
                                                    <svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#333333" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arfmodal-body_email arfmodal-body_p">
                                            <?php isset($values['id']) ? $arfieldhelper->get_shortcode_modal($values['id'], 'options_admin_bcc_email_notification', 'email', 'style="width:330px;"', false, $field_list) : ''; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                          
                            
                        </div>
                        <div class="arf_auto_responder_row">
                            <div class="arf_auto_responder_column">
                                <label class="arf_auto_responder_label_full"><?php echo addslashes(esc_html__('From/Replyto Name', 'ARForms')); ?></label>
                                <input type="text" id="options_ar_admin_from_name" name="options[ar_admin_from_name]" value="<?php echo (isset($values['ar_admin_from_name']) && $values['ar_admin_from_name'] != '') ? $values['ar_admin_from_name'] : $arfsettings->reply_to_name; ?>" <?php echo $chk_admin_notification_disabled; ?> class="arf_advanceemailfield" >
                                <button type="button" class="arf_add_field_button" onclick="add_field_fun('add_field_admin_from_name')" id="add_field_admin_from_but_name"  <?php echo $chk_admin_notification_disabled; ?>><?php echo addslashes(esc_html__('Add Field', 'ARForms')); ?>&nbsp;&nbsp;<img src="<?php echo ARFIMAGESURL ?>/down-arrow.png" align="absmiddle" /></button>
                                <div class="arf_main_field_modal">
                                    <div class="arf_add_fieldmodal" id="add_field_admin_from_name">
                                        <div class="arf_modal_header">
                                            <div class="arf_add_field_title">
                                                <?php echo addslashes(esc_html__('Fields', 'ARForms')); ?>
                                                <div data-dismiss="arfmodal" class="arf_field_model_close" onclick="close_add_field_subject('add_field_admin_from_name')">
                                                    <svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#333333" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arfmodal-body_email arfmodal-body_p">
                                            <?php isset($values['id']) ? $arfieldhelper->get_shortcode_modal($values['id'], 'options_ar_admin_from_name', 'email', 'style="width:330px;"', false, $field_list) : ''; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="arf_auto_responder_column">
                                <label class="arf_auto_responder_label_full"><?php echo addslashes(esc_html__('From E-mail', 'ARForms')); ?></label>
                                <?php
                                $ar_admin_from_email = isset($values['ar_admin_from_email']) ? $values['ar_admin_from_email'] : '';
                                if ($ar_admin_from_email == '') {
                                    $ar_admin_from_email = $arfsettings->reply_to;
                                } else {
                                    $ar_admin_from_email = $values['ar_admin_from_email'];
                                }
                                $ar_admin_from_email = $arformhelper->replace_field_shortcode($ar_admin_from_email);
                                ?>
                                <input type="text" value="<?php echo $ar_admin_from_email; ?>" id="ar_admin_from_email" name="options[ar_admin_from_email]" <?php echo $chk_admin_notification_disabled; ?> class="arf_advanceemailfield" />
                                <button type="button" class="arf_add_field_button" onclick="add_field_fun('add_field_admin_email')" id="add_field_admin_email_but"  <?php echo $chk_admin_notification_disabled; ?>><?php echo addslashes(esc_html__('Add Field', 'ARForms')); ?>&nbsp;&nbsp;<img src="<?php echo ARFIMAGESURL ?>/down-arrow.png" align="absmiddle" /></button>
                                <div class="arf_main_field_modal">
                                    <div class="arf_add_fieldmodal" id="add_field_admin_email">
                                        <div class="arf_modal_header">
                                            <div class="arf_add_field_title">
                                                <?php echo addslashes(esc_html__('Fields', 'ARForms')); ?>
                                                <div data-dismiss="arfmodal" class="arf_field_model_close" onclick="close_add_field_subject('add_field_admin_email')">
                                                    <svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#333333" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arfmodal-body_email arfmodal-body_p">
                                            <?php isset($values['id']) ? $arfieldhelper->get_shortcode_modal($values['id'], 'ar_admin_from_email', 'email', 'style="width:330px;"', false, $field_list) : ''; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="arf_auto_responder_row">
                            <div class="arf_auto_responder_column">
                                <label class="arf_auto_responder_label_full"><?php echo addslashes(esc_html__('Reply to E-mail', 'ARForms')); ?></label>
                                <?php
                                $ar_admin_reply_to_email = isset($values['ar_admin_reply_to_email']) ? $values['ar_admin_reply_to_email'] : '';
                                if ($ar_admin_reply_to_email == '')
                                    $ar_admin_reply_to_email = $arfsettings->reply_to_email;
                                else
                                    $ar_admin_reply_to_email = $values['ar_admin_reply_to_email'];

                                $ar_admin_reply_to_email = $arformhelper->replace_field_shortcode($ar_admin_reply_to_email);
                                ?>

                                <input type="text" value="<?php echo $ar_admin_reply_to_email; ?>" id="ar_admin_reply_to_email" name="options[ar_admin_reply_to_email]"<?php echo $chk_admin_notification_disabled; ?> class="arf_advanceemailfield" />

                                <button type="button" class="arf_add_field_button" onclick="add_field_fun('add_field_admin_nreplyto_email')" id="add_field_admin_nreplyto_email_but" <?php echo $chk_admin_notification_disabled; ?>><?php echo addslashes(esc_html__('Add Field', 'ARForms')); ?>&nbsp;&nbsp;
                                    <img src="<?php echo ARFIMAGESURL ?>/down-arrow.png" align="absmiddle" />
                                </button>

                                <div class="arf_main_field_modal <?php echo isset($auto_res_email_cls) ? $auto_res_email_cls : ""; ?>">
                                    <div class="arf_add_fieldmodal" id="add_field_admin_nreplyto_email">
                                        <div class="arf_modal_header">
                                            <div class="arf_add_field_title">
                                                <?php echo addslashes(esc_html__('Fields', 'ARForms')); ?>
                                                <div data-dismiss="arfmodal" class="arf_field_model_close" onclick="close_add_field_subject('add_field_admin_nreplyto_email')">
                                                    <svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#333333" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arfmodal-body_email arfmodal-body_p">
                                        <?php
                                        if (isset($values['id'])) {
                                            $arfieldhelper->get_shortcode_modal($values['id'], 'ar_admin_reply_to_email', 'email', 'style="width:330px;"', false, $field_list);
                                        }
                                        ?>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="arf_auto_responder_row">
                            <div class="arf_width_80">
                                <label class="arf_auto_responder_label_full"><?php echo addslashes(esc_html__('Admin Message', 'ARForms')); ?></label>
                                <div>
                                <?php
                                $ar_admin_email_message = (isset($values['ar_admin_email_message']) and ! empty($values['ar_admin_email_message']) ) ? esc_attr($arformcontroller->br2nl($values['ar_admin_email_message'])) : '';
                                $ar_admin_email_message = $arformhelper->replace_field_shortcode($ar_admin_email_message);
                                $email_editor_settings = array(
                                    'wpautop' => true,
                                    'media_buttons' => false,
                                    'textarea_name' => 'options[ar_admin_email_message]',
                                    'textarea_rows' => '4',
                                    'tinymce' => false,
                                    'editor_class' => "txtmultimodal1 arf_advanceemailfield ar_admin_email_message_content",
                                );
                                wp_editor($ar_admin_email_message, 'ar_admin_email_message', $email_editor_settings);
                                ?>
                                <textarea style="display:none;opacity: 0; width:0; height: 0" name="options[ar_admin_email_message]" id="ar_admin_email_message_text"><?php echo $ar_admin_email_message; ?></textarea>
                                </div>
                            </div>
                            <div class="arf_width_20">
                                <button type="button" class="arf_add_field_button" onclick="add_field_fun('add_field_admin_message')" id="add_field_admin_message_but"  <?php echo $chk_admin_notification_disabled; ?>><?php echo addslashes(esc_html__('Add Field', 'ARForms')); ?>&nbsp;&nbsp;<img src="<?php echo ARFIMAGESURL ?>/down-arrow.png" align="absmiddle" /></button>
                                <div class="arf_main_field_modal" style="margin-top:-21px;">
                                    <div class="arf_add_fieldmodal" id="add_field_admin_message">
                                        <div class="arf_modal_header">
                                            <div class="arf_add_field_title">
                                                <?php echo addslashes(esc_html__('Fields', 'ARForms')); ?>
                                                <div data-dismiss="arfmodal" class="arf_field_model_close" onclick="close_add_field_subject('add_field_admin_message')">
                                                    <svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#333333" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="arfmodal-body_email arfmodal-body_p">
                                            <?php isset($values['id']) ? $arfieldhelper->get_shortcode_modal($values['id'], 'ar_admin_email_message', 'no_email', 'style="width:330px;"', false, $field_list) : ''; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <span class="arferrmessage" id="ar_admin_email_message_error" style="top:0px;"><?php echo addslashes(esc_html__('This field cannot be blank','ARForms')); ?></span>
                            <div style="margin-top: 5px;clear: both;">
                                <div><label><code>[ARF_form_all_values]</code> - <?php echo addslashes(esc_html__('This will be replaced with form\'s all fields & labels.', 'ARForms')); ?></label></div>
                                <div><label><code>[ARF_form_referer]</code> - <?php echo esc_html__('This will be replaced with entry referer.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_form_added_date_time]</code> - <?php echo esc_html__('This will be replaced with entry added time.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_form_ipaddress]</code> - <?php echo esc_html__('This will be replaced with IP Address.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_form_browsername]</code> - <?php echo esc_html__('This will be replaced with user browser name.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_form_entryid]</code> - <?php echo esc_html__('This will be replaced with Entry ID.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_current_userid]</code> - <?php echo esc_html__('This will be replaced with current login ID.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_current_username]</code> - <?php echo esc_html__('This will be replaced with current login user name.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_current_useremail]</code> - <?php echo esc_html__('This will be replaced with current login user email.', 'ARForms'); ?></label></div>
                                <div><label><code>[ARF_page_url]</code> - <?php echo esc_html__('This will be replaced with current form\'s page URL.', 'ARForms'); ?></label></div>
                                <?php do_action('arf_add_admin_mail_shortcode_in_outside', $id, $values); ?>
                            </div>
                        </div>
                    </div>
                    <?php do_action('arf_additional_autoresponder_settings', $id, $values); ?>
                    <?php do_action('arf_after_autoresponder_settings_container', $id, $values); ?>
                </div>
                <div class="arf_popup_container_footer">
                    <button type="button" class="arf_popup_close_button" data-id="arf_mail_notification_popup_button" ><?php echo esc_html__('OK', 'ARForms'); ?></button>
                </div>
            </div>
        </div>
        <!-- Auto Response email -->

        <!--- Conditional Logic pop-up -->
        <div class="arf_modal_overlay">
            <div id="arf_conditional_logic_model" class="arf_popup_container arf_popup_container_conditional_logic_model" style="">
                <div class="arf_popup_container_header"><?php echo addslashes(esc_html__('Conditional Rule', 'ARForms')); ?>
                    <div class="arfpopupclosebutton arfmodalclosebutton" data-dismiss="arfmodal" data-id="arf_optin_popup_button">
                        <svg width="30px" height="30px" viewBox="1 0 20 20"><g id="preview"><path fill-rule="evenodd" clip-rule="evenodd" fill="#262944" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                    </div>
                </div>
                <div class="arf_popup_content_container arf_submit_popup_container">
                    <!-- content start-->
                    <div class="arf_popup_container_loader">
                        <i class="arfas arfa-spinner arfa-spin"></i>
                    </div>
                    <div style="<?php echo (is_rtl()) ? 'float: left;' : 'float: right;'; ?>clear: both;">
                        <a href="<?php echo ARFURL; ?>/documentation/index.html#conditional_logic" target="_blank" title="help" class="arfa arfa-life-bouy arf_adminhelp_icon arfhelptip tipso_style" data-tipso="help"></a>
                    </div>
                    <?php include 'arf_conditional_logic.php'; ?>
                    <!--content over-->
                </div>
                <div class="arf_popup_container_footer">
                    <button type="button" class="arf_popup_close_button" data-id="arf_optin_popup_button" ><?php echo esc_html__('OK', 'ARForms'); ?></button>
                </div>

            </div>
        </div>
        <!-- conditional logic over -->


        <!-- Submit Action Model -->
        <div class="arf_modal_overlay">
            <div id="arf_submit_action_model" class="arf_popup_container arf_popup_container_submit_action_model">
                <div class="arf_popup_container_header"><?php echo addslashes(esc_html__('Submit Action', 'ARForms')); ?>
                    <div class="arfpopupclosebutton arfmodalclosebutton" data-dismiss="arfmodal" data-id="arf_submit_popup_button">
                        <svg width="30px" height="30px" viewBox="1 0 20 20"><g id="preview"><path fill-rule="evenodd" clip-rule="evenodd" fill="#262944" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                    </div>
                </div>
                <div class="arf_popup_content_container arf_submit_action_container">
                    <div class="arf_popup_container_loader">
                        <i class="arfas arfa-spinner arfa-spin"></i>
                    </div>
                    <p class="arftitle_p">
                        <label for="conditional_logic_arfsubmit"><?php echo addslashes(esc_html__('Form submission action', 'ARForms')); ?></label>
                        <label style="<?php echo (is_rtl()) ? 'float: left;margin-left: 12px;' : 'float: right;margin-right: 12px;'; ?>">
                            <a href="<?php echo ARFURL; ?>/documentation/index.html#form_submit_act" target="_blank" title="help" class="arfa arfa-life-bouy arf_adminhelp_icon arfhelptip tipso_style" data-tipso="help"></a>
                        </label>
                    </p>
                    
                    <div class="arf_submit_action_options" style="margin-left: 10px;margin-top: -2px;<?php echo(is_rtl())?'margin-right: -20px':'';?>">
                        <div class="arf_radio_wrapper">
                            <div class="arf_custom_radio_div">
                                <div class="arf_custom_radio_wrapper">
                                    <input type="radio" class="arf_custom_radio arf_submit_action" name="options[success_action]" id="success_action_message" value="message" <?php checked($values['success_action'], 'message'); ?> />
                                    <svg width="18px" height="18px">
                                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                    </svg>
                                </div>
                            </div>
                            <span>
                                <label id="success_action_message" for="success_action_message"><?php echo addslashes(esc_html__('Display a Message', 'ARForms')); ?></label>
                            </span>
                        </div>
                        <div class="arf_radio_wrapper">
                            <div class="arf_custom_radio_div">
                                <div class="arf_custom_radio_wrapper">
                                    <input type="radio" name="options[success_action]" id="success_action_redirect" class="arf_submit_action arf_custom_radio" value="redirect" <?php checked($values['success_action'], 'redirect'); ?> />
                                    <svg width="18px" height="18px">
                                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                    </svg>
                                </div>
                            </div>
                            <span>
                                <label id="success_action_redirect" for="success_action_redirect"><?php echo esc_html__('Redirect to URL', 'ARForms'); ?></label>
                            </span>
                        </div>
                        <div class="arf_radio_wrapper">
                            <div class="arf_custom_radio_div" >
                                <div class="arf_custom_radio_wrapper">
                                    <input type="radio" name="options[success_action]" id="success_action_page" class="arf_submit_action arf_custom_radio" value="page" <?php checked($values['success_action'], 'page'); ?> />
                                    <svg width="18px" height="18px">
                                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                    </svg>
                                </div>
                            </div>
                            <span>
                                <label id="success_action_page" for="success_action_page"><?php echo esc_html__('Display content from another page', 'ARForms'); ?></label>
                            </span>
                        </div>
                    </div>

                    <div id="arf_success_action_message" class="arf_optin_tab_inner_container arfmarginl15 arf_submit_action_inner_container <?php echo ($values['success_action'] == 'message') ? 'arfactive' : ''; ?>">
                        <div class="arfcolumnleft arfsettingsubtitle"><label for="success_msg" class="arf_dropdown_autoresponder_label"><?php echo addslashes(esc_html__('Confirmation Message', 'ARForms')); ?></label></div>
                        <div class="arfcolumnright fix_height">
                            <textarea id="success_msg" class="auto_responder_webform_code_area txtmultimodal1" name="options[success_msg]" cols="2" rows="4"><?php echo $values['success_msg']; ?></textarea>
                            <span class="arferrmessage" id="success_msg_error"><?php echo addslashes(esc_html__('This field cannot be blank','ARForms')); ?></span>
                        </div>
                    </div>


                    <div id="arf_success_action_redirect" class="arf_optin_tab_inner_container arfmarginl15 arf_submit_action_inner_container <?php echo ($values['success_action'] == 'redirect') ? 'arfactive' : ''; ?>">
                        <label for="success_url" class="arf_dropdown_autoresponder_label"><?php echo esc_html__('Set Static Redirect URL', 'ARForms'); ?></label>
                        <input type="text" id="success_url" class="arf_large_input_box arf_redirect_to_url success_url_width" name="options[success_url]" value="<?php echo isset($values['success_url']) ? $values['success_url'] : ''; ?>" />
                        <span class="arferrmessage" id="success_url_error" style='top:0;'><?php echo addslashes(esc_html__('This field cannot be blank','ARForms')); ?></span>
                        <br/><i class="arf_notes" style="float: left;width: 100%;"><?php echo esc_html__('Please insert url with http:// or https://.', 'ARForms'); ?></i>
                        <?php do_action('arf_form_submit_after_redirect_to_url', $id, $values); ?>
                        <div class="arfcolumnleft arf_custom_margin_redirect arfsetcondtionalredirect"">
                            <div class="arf_custom_checkbox_div">
                                <div class="arf_custom_checkbox_wrapper">
                                    <input type="checkbox" value="1" name="options[arf_data_with_url]" class="chkstanard" id="arf_sa_data_with_url" <?php isset($values['arf_data_with_url']) ? checked($values['arf_data_with_url'], 1) : ''; ?>>
                                    <svg width="18px" height="18px"><path id="arfcheckbox_unchecked" d="M15.643,17.617H3.499c-1.34,0-2.427-1.087-2.427-2.429V3.045  c0-1.341,1.087-2.428,2.427-2.428h12.144c1.342,0,2.429,1.087,2.429,2.428v12.143C18.072,16.53,16.984,17.617,15.643,17.617z   M16.182,2.477H2.961v13.221h13.221V2.477z"></path><path id="arfcheckbox_checked" d="M15.645,17.62H3.501c-1.34,0-2.427-1.087-2.427-2.429V3.048  c0-1.341,1.087-2.428,2.427-2.428h12.144c1.342,0,2.429,1.087,2.429,2.428v12.143C18.074,16.533,16.986,17.62,15.645,17.62z   M16.184,2.48H2.963v13.221h13.221V2.48z M5.851,7.15l2.716,2.717l5.145-5.145l1.718,1.717l-5.146,5.145l0.007,0.007l-1.717,1.717  l-0.007-0.008l-0.006,0.008l-1.718-1.717l0.007-0.007L4.134,8.868L5.851,7.15z"></path></svg>
                                </div>
                                <span>
                                    <label for="arf_sa_data_with_url" style="margin-left: 4px;"><?php echo addslashes(esc_html__('Send form submission data along with URL','ARForms')); ?></label><br/>
                                </span>
                            </div>
                        </div>
                        <i class="arf_notes" style="float: left;width: 100%;margin-left:30px;font-size:13px;margin-top:-5px;margin-bottom:5px;">(<?php echo esc_html__('when the form has been successfuly submitted, it will send data to the redirect URL with the method you will choose below.', 'ARForms'); ?>)</i>

                        <?php
                            $method_type = "post";
                            if(isset($values["arf_data_with_url_type"]) && $values["arf_data_with_url_type"] == "get") {
                                $method_type = "get";
                            }
                        ?>

                        <div class="arf_submit_action_options arf_data_with_url_type_wrapper" style="<?php echo(is_rtl())?'margin-right: -20px':''; echo (isset($values['arf_data_with_url']) && $values['arf_data_with_url'] == "1") ? 'display: block;':''; ?>">
                            <div class="arf_radio_wrapper">
                                <div class="arf_custom_radio_div">
                                    <div class="arf_custom_radio_wrapper">
                                        <input type="radio" name="options[arf_data_with_url_type]" id="arf_data_with_url_post_type" class="arf_custom_radio" value="POST" <?php echo ($method_type == "post") ? 'checked' : '' ?> />
                                        <svg width="18px" height="18px">
                                        <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                        <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                        </svg>
                                    </div>
                                </div>
                                <span>
                                    <label id="arf_data_with_url_post_type" for="arf_data_with_url_post_type"><?php echo esc_html__('POST', 'ARForms'); ?></label>
                                </span>
                            </div>
                            <div class="arf_radio_wrapper">
                                <div class="arf_custom_radio_div">
                                    <div class="arf_custom_radio_wrapper">
                                        <input type="radio" class="arf_custom_radio" name="options[arf_data_with_url_type]" id="arf_data_with_url_get_type" value="GET" <?php echo ($method_type == "get") ? 'checked' : '' ?> />
                                        <svg width="18px" height="18px">
                                        <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                        <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                        </svg>
                                    </div>
                                </div>
                                <span>
                                    <label id="arf_data_with_url_get_type" for="arf_data_with_url_get_type"><?php echo addslashes(esc_html__('GET', 'ARForms')); ?></label>
                                </span>
                            </div>
                            
                        </div>
                    </div>

                    <div id="arf_success_action_page" class="arf_optin_tab_inner_container arfmarginl15 arf_submit_action_inner_container <?php echo ($values['success_action'] == 'page') ? 'arfactive' : ''; ?>">
                        <div class="arf_ar_dropdown_wrapper">
                            <label class="arf_dropdown_autoresponder_label" id="arf_use_content_from_page" style="margin-top: 10px;"><?php echo addslashes(esc_html__('Select Page', 'ARForms')); ?></label>
                            <?php $armainhelper->wp_pages_dropdown('options[success_page_id]', isset($values['success_page_id']) ? $values['success_page_id'] : "", '', 'option_success_page_id'); ?>
                            <span class="arferrmessage" id="option_success_page_id_error" style='top:0;'><?php echo addslashes(esc_html__('This field cannot be blank','ARForms')); ?></span>
                        </div>
                    </div>
                   
                    <div class="arf_popup_checkbox_wrapper" style="margin-left: 11px;margin-top:10px;">
                        <div class="arf_custom_checkbox_div" style="margin-top: 4px;">
                            <div class="arf_custom_checkbox_wrapper">
                                <input type="checkbox" name="options[arf_form_hide_after_submit]" id="arf_hide_form_after_submitted" value="1" <?php isset($values['arf_form_hide_after_submit']) ? checked($values['arf_form_hide_after_submit'], 1) : ''; ?> />
                                <svg width="18px" height="18px">
                                <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                </svg>
                            </div>
                            <span><label id="arf_hide_form_after_submitted" for="arf_hide_form_after_submitted" style="margin-left: 4px;"><?php echo addslashes(esc_html__('Hide Form after submission', 'ARForms')); ?></label></span>
                        </div>
                    </div>

                    <?php do_action('arf_option_before_submit_conditional_logic', $id, $values);  ?>

                    <div class="arf_separater" style="margin-top: 15px;width:98%;"></div>

                    <div class="submit_action_conditonal_law" style="margin-top: -15px;margin-left: 6px;">                        
                        <div class="field_conditional_law field_basic_option arf_fieldoptiontab" style="display:block;">
                            <?php
                            $cl_submit_conditional_login = ( isset($values['submit_conditional_logic']) ) ? $values['submit_conditional_logic'] : array();
                            $cl_rules_array = ( isset($cl_submit_conditional_login['rules']) ) ? $cl_submit_conditional_login['rules'] : array();
                            $cl_submit_conditional_login['enable'] = (isset($cl_submit_conditional_login['enable']) && count($cl_rules_array) > 0) ? $cl_submit_conditional_login['enable'] : 0;

                            ?>
                            <div class="arf_enable_conditional_submit_div" <?php echo(is_rtl())?'style="margin-right:1px;"':'' ?>>
                                <div class="arf_custom_checkbox_div">
                                    <div class="arf_custom_checkbox_wrapper">
                                        <input type="checkbox" class="" name="conditional_logic_arfsubmit" id="conditional_logic_arfsubmit" onchange="arf_cl_change('arfsubmit');" value="<?php echo $cl_submit_conditional_login['enable']; ?>" <?php checked($cl_submit_conditional_login['enable'], 1) ?> />
                                        <svg width="18px" height="18px">
                                        <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                        <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                        </svg>
                                    </div>
                                    <span>
                                        <label for="conditional_logic_arfsubmit" class="arftitle_p" style="margin-left: 4px;font-size: 16px !important; margin-top: 3px;"><?php echo addslashes(esc_html__('Configure conditional submission', 'ARForms')); ?></label>
                                    </span>
                                </div>
                            </div>
                            <div id="conditional_logic_div_arfsubmit" style="<?php
                            if (count($cl_rules_array) == 0) {
                                echo 'display:none;';
                            }
                            ?>">
                                <div class="arflabeltitle" style="margin-top: 27px;">
                                    <div class="sltstandard <?php
                                    if (count($cl_rules_array) == 0) {
                                        echo ' arfhelptip';
                                    }
                                    ?>" <?php if (count($cl_rules_array) == 0) { ?>title="<?php echo addslashes(esc_html__('Please add one or more rules', 'ARForms')); ?>"<?php } ?>   >
                                             <?php
                                             $selected_list_label = addslashes(esc_html__('Enable', 'ARForms'));
                                             ;
                                             if (isset($cl_submit_conditional_login['display'])) {
                                                 if ($cl_submit_conditional_login['display'] == 'show') {
                                                     $selected_list_label = addslashes(esc_html__('Enable', 'ARForms'));
                                                 }
                                                 if ($cl_submit_conditional_login['display'] == 'hide') {
                                                     $selected_list_label = addslashes(esc_html__('Disable', 'ARForms'));
                                                 }
                                             }
                                             ?>
                                        <input id="conditional_logic_display_arfsubmit" name="conditional_logic_display_arfsubmit" type="hidden" class="frm-dropdown frm-pages-dropdown" value="<?php echo isset($cl_submit_conditional_login['display']) ? $cl_submit_conditional_login['display'] : 'show'; ?>">
                                        <dl class="arf_selectbox" data-name="conditional_logic_display_arfsubmit" data-id="conditional_logic_display_arfsubmit" style="width:100px;">
                                            <dt><span><?php echo $selected_list_label; ?></span>
                                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                            <dd>
                                                <ul class="field_dropdown_list_menu" style="display: none;" data-id="conditional_logic_display_arfsubmit">
                                                    <li class="arf_selectbox_option" data-value="show" data-label="<?php echo addslashes(esc_html__('Enable', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Enable', 'ARForms')); ?></li>
                                                    <li class="arf_selectbox_option" data-value="hide" data-label="<?php echo addslashes(esc_html__('Disable', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Disable', 'ARForms')); ?></li>
                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>
                                    <span class="if_lable"><label id="txtmultimodal1" class="arf_dropdown_autoresponder_label"><?php echo esc_html__('submit button if', 'ARForms'); ?></label></span>
                                    <div class="sltstandard <?php
                                    if (count($cl_rules_array) == 0) {
                                        echo ' arfhelptip';
                                    }
                                    ?>" <?php if (count($cl_rules_array) == 0) { ?>title="<?php echo addslashes(esc_html__('Please add one or more rules', 'ARForms')); ?>"<?php } ?>>
                                             <?php
                                             $selected_list_label = addslashes(esc_html__('All', 'ARForms'));
                                             if (isset($cl_submit_conditional_login['if_cond'])) {
                                                 if ($cl_submit_conditional_login['if_cond'] == 'all') {
                                                     $selected_list_label = addslashes(esc_html__('All', 'ARForms'));
                                                 }
                                                 if ($cl_submit_conditional_login['if_cond'] == 'any') {
                                                     $selected_list_label = addslashes(esc_html__('Any', 'ARForms'));
                                                 }
                                             } else {
                                                $cl_submit_conditional_login['if_cond'] = 'all';
                                             }
                                             ?>
                                        <input id="conditional_logic_if_cond_arfsubmit" name="conditional_logic_if_cond_arfsubmit" type="hidden" class="frm-dropdown frm-pages-dropdown" value="<?php echo $cl_submit_conditional_login['if_cond']; ?>" />
                                        <dl class="arf_selectbox" data-name="conditional_logic_if_cond_arfsubmit" data-id="conditional_logic_if_cond_arfsubmit" style="width:100px;">
                                            <dt><span><?php echo $selected_list_label; ?></span>
                                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                            <dd>
                                                <ul class="field_dropdown_list_menu" style="display: none;" data-id="conditional_logic_if_cond_arfsubmit">
                                                    <li class="arf_selectbox_option" data-value="all" data-label="<?php echo addslashes(esc_html__('All', 'ARForms')); ?>"><?php echo addslashes(esc_html__('All', 'ARForms')); ?></li>
                                                    <li class="arf_selectbox_option" data-value="any" data-label="<?php echo addslashes(esc_html__('Any', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Any', 'ARForms')); ?></li>
                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>
                                    <span class="if_lable"><label id="txtmultimodal1" class="arf_dropdown_autoresponder_label"><?php echo addslashes(esc_html__('of the following match', 'ARForms')); ?></label></span>
                                    <div class="button_div">
                                        <button type="button" id="arf_new_law_arfsubmit" onclick="arf_add_new_law('arfsubmit');"  class="rounded_button arf_btn_dark_blue arfaddnewrule" style=" <?php
                                        if ($cl_submit_conditional_login['enable'] == 1 && count($cl_rules_array) > 0) {
                                            echo 'display:none;';
                                        }
                                        ?>"><?php echo addslashes(esc_html__('Add new condition', 'ARForms')); ?></button>
                                        <div id="logic_rules_div_arfsubmit" class="logic_rules_div" style=" <?php
                                        if ($cl_submit_conditional_login['enable'] == 0) {
                                            echo 'display:none;';
                                        }
                                        ?>">
                                        <span style="<?php echo (is_rtl()) ? 'float:right;' : 'float: left;';?> font-size: 14px; line-height: 30px; margin-right: 7px;color: #3f74e7;"><?php echo addslashes(esc_html__('If', 'ARForms')) ?></span>
                                                 <?php
                                                 if (count($cl_rules_array) > 0) {
                                                     $rule_i = 1;
                                                     if($arfaction == 'duplicate'){
                                                        $id = $define_template;
                                                     }
                                                     foreach ($cl_rules_array as $rule) {
                                                         ?>
                                                    <div id="arf_cl_rule_arfsubmit<?php echo '_' . $rule_i; ?>" class="cl_rules">
                                                        <input type="hidden" name="rule_array_arfsubmit[]" value="<?php echo $rule_i; ?>" />
                                                        <span>
                                                            <div class="sltstandard arf_cl_field_menu"><?php echo $arfieldhelper->arf_cl_field_menu_submit_cl($id, 'arf_cl_field_arfsubmit_' . $rule_i, 'arf_cl_field_arfsubmit_' . $rule_i, $rule['field_id']); ?></div>
                                                        </span>
                                                        <span style="float: left; font-size: 14px; line-height: 30px; margin-right: 7px;"><?php echo addslashes(esc_html__('is', 'ARForms')); ?></span>
                                                        <span>
                                                            <div class="sltstandard arf_cl_op_arfsubmit_operator"><?php echo $arfieldhelper->arf_cl_rule_menu('arf_cl_op_arfsubmit_' . $rule_i, 'arf_cl_op_arfsubmit_' . $rule_i, $rule['operator']); ?></div>
                                                        </span>                                                        
                                                        <span class="span_txtnew">
                                                            <input type="text" name="cl_rule_value_arfsubmit<?php echo '_' . $rule_i; ?>" id="cl_rule_value_arfsubmit<?php echo '_' . $rule_i; ?>" onkeyup="this.setAttribute('value',this.value)" class="txtstandardnew arfheight34" value='<?php echo esc_attr($rule['value']); ?>' style="width:100%;" />
                                                        </span>
                                                        <span class="bulk_add_remove arf_conditional_logic_on_submisson_bulk_add_remove">
                                                            <span class="bulk_add" onclick="add_new_rule('arfsubmit');"><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.134,20.362c-5.521,0-9.996-4.476-9.996-9.996c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.13,15.887,16.654,20.362,11.134,20.362z M11.133,2.314c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052C19.185,5.919,15.579,2.314,11.133,2.314z M12.146,14.341h-2v-3h-3v-2h3V6.372h2v2.969h3v2h-3V14.341z"></path></g></svg></span>
                                                            <span class="bulk_remove" onclick="delete_rule('arfsubmit', '<?php echo $rule_i; ?>');" ><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.12,20.389c-5.521,0-9.996-4.476-9.996-9.996c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.116,15.913,16.64,20.389,11.12,20.389z M11.119,2.341c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052C19.17,5.945,15.565,2.341,11.119,2.341z M12.131,11.367h3v-2h-3h-2h-3v2h3H12.131z"></path></g></svg></span>
                                                        </span>
                                                    </div>
                                                    <?php
                                                    $rule_i++;
                                                }
                                            }
                                            ?>
                                        </div>
                                        <input type="hidden" id="field_type_arfsubmit" data-fid="arfsubmit" value="arfsubmit" />
                                        <input type="hidden" id="field_ref_arfsubmit" value="arfsubmit" />
                                        <input type="hidden" name="field_options[field_key_arfsubmit]" class="txtstandardnew" value="arfsubmit_key" size="20" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php do_action('arf_after_onsubmit_settings_container', $id, $values); ?>
                </div>
                <div class="arf_popup_container_footer">
                    <button type="button" class="arf_popup_close_button" data-id="arf_submit_popup_button" ><?php echo esc_html__('OK', 'ARForms'); ?></button>
                </div>
            </div>
        </div>
        <!-- Submit Action Model -->

        <!-- Optins Model -->
        <div class="arf_modal_overlay">
            <?php $double_optin = isset($values['arf_enable_double_optin']) ? $values['arf_enable_double_optin'] : ""; ?>
            <div id="arf_optin_model" class="arf_popup_container arf_popup_container_option_model">
                <div class="arf_popup_container_header"><?php echo addslashes(esc_html__('Opt-ins (email marketing) configuration', 'ARForms')); ?>
                    <div class="arfpopupclosebutton arfmodalclosebutton" data-dismiss="arfmodal" data-id="arf_optin_popup_button">
                        <svg width="30px" height="30px" viewBox="1 0 20 20"><g id="preview"><path fill-rule="evenodd" clip-rule="evenodd" fill="#262944" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                    </div>
                </div>
                <div class="arf_option_model_popup_container arf_optins_container">
                    <div class="arf_popup_container_loader">
                        <i class="arfas arfa-spinner arfa-spin"></i>
                    </div>
                    <div class="arf_popup_container_autoresponder_values arf_autoresponder_values_container" style="margin-top: -10px;">
                        <div>
                            <p class="arftitle_p" style="margin-left: 0px;">
                                <label><?php echo addslashes(esc_html__('Form fields mapping', 'ARForms')); ?></label>
                                <span class="" style="padding-top: 0px;padding-bottom: 0px;font-style: italic;display: inline-block;font-weight: normal;font-size: 14px;display: block;margin-left: 25px;margin-bottom: 15px;"><?php echo addslashes(esc_html__('(please select appropriate form fields for first name, last name and email parameters to submit on email marketing softwares)', 'ARForms')); ?>)</span>
                            </p>
                            
                        </div>

                        <div style="margin-left: 25px;float: left;width:100%;display: block;">
                            <?php
                            $selectbox_field_options = "";
                            $selectbox_field_value_label = "";
                            if (isset($values['fields']) and count($values['fields']) > 0) {
                                foreach ($values['fields'] as $field1) {
                                    if ($field1['type'] != 'divider' && $field1['type'] != 'break' && $field1['type'] != 'captcha' && $field1['type'] != 'html') {

                                        if (($field1["id"] == $responder_fname)) {
                                            $selectbox_field_value_label = $field1["name"];
                                        }

                                        $current_field_id = $field1["id"];
                                        if ($current_field_id !="" && $arfieldhelper->arf_execute_function($field1["name"],'strip_tags')=="") {
                                            $selectbox_field_options .= '<li class="arf_selectbox_option" data-type="'.$field1['type'].'" data-value="' . $current_field_id . '" data-label="[Field Id:'.$current_field_id.']">[Field Id:'.$current_field_id.']</li>';
                                            
                                        }else{
                                            $selectbox_field_options .= '<li class="arf_selectbox_option" data-type="'.$field1['type'].'" data-value="' . $current_field_id . '" data-label="' . $arfieldhelper->arf_execute_function($field1["name"],'strip_tags') . '">' . $arfieldhelper->arf_execute_function($field1["name"],'strip_tags') . '</li>';    
                                        }
                                        
                                    }
                                }
                            }                        
                            ?>
                            <input id="autoresponder_fname" name="autoresponder_fname" value="<?php echo $responder_fname; ?>" type="hidden" <?php
                            if ($setvaltolic != 1) {
                                echo "readonly=readonly";
                            }
                            ?>>
                            <input id="autoresponder_lname" name="autoresponder_lname" value="<?php echo $responder_lname; ?>" type="hidden" <?php
                            if ($setvaltolic != 1) {
                                echo "readonly=readonly";
                            }
                            ?>>
                            <input id="autoresponder_email" name="autoresponder_email" value="<?php echo $responder_email; ?>" type="hidden" <?php
                            if ($setvaltolic != 1) {
                                echo "readonly=readonly";
                            }
                            ?>>
                            <div class="arf_ar_dropdown_wrapper">
                                <label class="arf_dropdown_autoresponder_label"><?php echo addslashes(esc_html__('First name field', 'ARForms')); ?></label>
                                <dl class="arf_selectbox" data-name="autoresponder_fname" data-id="autoresponder_fname" style="width:170px;">
                                    <dt><span><?php
                                        if ($selectbox_field_value_label != "") {
                                            echo $arfieldhelper->arf_execute_function($selectbox_field_value_label,'strip_tags');
                                        }else if($responder_fname!="" && $responder_fname!=0 && $selectbox_field_value_label ==""){
                                            echo '[Field Id:'.$responder_fname.']';
                                        } else {
                                            echo addslashes(esc_html__('Select First Name', 'ARForms'));
                                        }
                                        ?></span>
                                    <input value="<?php
                                    if (isset($values['id']) && $values["id"] == $responder_fname) {
                                        echo $values["id"];
                                    }
                                    ?>" style="display:none;width:128px;" class="arf_autocomplete" type="text" autocomplete="off">
                                    <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                    <dd>
                                        <ul class="arf_name_field_dropdown" style="display: none;" data-id="autoresponder_fname">
                                            <li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select First Name', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Select First Name', 'ARForms')); ?></li>

                                            <?php echo $selectbox_field_options; ?>

                                        </ul>
                                    </dd>
                                </dl>
                            </div>
                            <?php
                            $selectbox_field_options = "";
                            $selectbox_field_value_label = "";
                            if (isset($values['fields']) and count($values['fields']) > 0) {
                                foreach ($values['fields'] as $field1) {
                                    if ($field1['type'] != 'divider' && $field1['type'] != 'break' && $field1['type'] != 'captcha' && $field1['type'] != 'html') {

                                        if (($field1["id"] == $responder_lname)) {
                                            $selectbox_field_value_label = $field1["name"];
                                        }

                                        $current_field_id = $field1["id"];
                                        if($current_field_id !="" && $arfieldhelper->arf_execute_function($field1["name"],'strip_tags')==""){
                                            $selectbox_field_options .= '<li class="arf_selectbox_option" data-value="' . $current_field_id . '" data-label="[Field Id:'.$current_field_id.']">[Field Id:'.$current_field_id.']</li>';

                                        }else{
                                            $selectbox_field_options .= '<li class="arf_selectbox_option" data-value="' . $current_field_id . '" data-label="' . $arfieldhelper->arf_execute_function($field1["name"],'strip_tags') . '">' . $arfieldhelper->arf_execute_function($field1["name"],'strip_tags') . '</li>';    
                                        }
                                        
                                    }
                                }
                            }
                            ?>
                            <div class="arf_ar_dropdown_wrapper">
                                <label class="arf_dropdown_autoresponder_label"><?php echo addslashes(esc_html__('Last name field', 'ARForms')); ?></label>
                                <dl class="arf_selectbox" data-name="autoresponder_lname" data-id="autoresponder_lname" style="width:170px;">
                                    <dt><span><?php
                                        if ($selectbox_field_value_label != "") {
                                            echo $arfieldhelper->arf_execute_function($selectbox_field_value_label,'strip_tags');
                                        }else if($responder_lname !="" && $responder_lname!=0 && $selectbox_field_value_label=="" ){
                                            echo '[Field Id:'.$responder_lname.']';
                                        } else {
                                            echo addslashes(esc_html__('Select Last Name', 'ARForms'));
                                        }
                                        ?></span>
                                    <input value="<?php
                                    if (isset($values["id"]) && $values["id"] == $responder_lname) {
                                        echo $values["id"];
                                    } else if (isset($values["ref_field_id"]) && $values["ref_field_id"] == $responder_lname) {
                                        echo $values["ref_field_id"];
                                    }
                                    ?>" style="display:none;width:128px;" class="arf_autocomplete" type="text" autocomplete="off">
                                    <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                    <dd>
                                        <ul class="arf_name_field_dropdown" style="display: none;" data-id="autoresponder_lname">
                                            <li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select Last Name', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Select Last Name', 'ARForms')); ?></li>

                                            <?php echo $selectbox_field_options; ?>

                                        </ul>
                                    </dd>
                                </dl>
                            </div>
                            <?php
                            $selectbox_field_options = "";
                            $selectbox_field_value_label = "";
                            if (isset($values['fields']) and count($values['fields']) > 0) {
                                foreach ($values['fields'] as $field1) {
                                    if (in_array($field1['type'], array('email', 'text'))) {
                                        if (($field1["id"] == $responder_email)) {
                                            $selectbox_field_value_label = $field1["name"];
                                        }

                                        $current_field_id = $field1["id"];
                                        if ($current_field_id !="" && $arfieldhelper->arf_execute_function($field1["name"],'strip_tags')=="") {
                                            $selectbox_field_options .= '<li class="arf_selectbox_option" data-value="' . $current_field_id . '" data-label="[Field Id : '.$current_field_id.']">[Field Id : '.$current_field_id.']</li>';
                                        }else{
                                            $selectbox_field_options .= '<li class="arf_selectbox_option" data-value="' . $current_field_id . '" data-label="' . $arfieldhelper->arf_execute_function($field1["name"],'strip_tags') . '">' . $arfieldhelper->arf_execute_function($field1["name"],'strip_tags') . '</li>';    
                                        }
                                        
                                    }
                                }
                            }
                            ?>
                            <div class="arf_ar_dropdown_wrapper">
                                <label class="arf_dropdown_autoresponder_label"><?php echo addslashes(esc_html__('Email field', 'ARForms')); ?></label>
                                <dl class="arf_selectbox" data-name="autoresponder_email" data-id="autoresponder_email" style="width:170px;">
                                    <dt><span><?php
                                        if ($selectbox_field_value_label != "") {
                                            echo $arfieldhelper->arf_execute_function($selectbox_field_value_label,'strip_tags');
                                        }else if($responder_email !="" && $responder_email!=0 && $selectbox_field_value_label==""){
                                            echo '[Field Id : '.$responder_email.']';
                                        } else {
                                            echo addslashes(esc_html__('Select Email Field', 'ARForms'));
                                        }
                                        ?></span>
                                    <input value="<?php
                                    if (isset($values["id"]) && $values["id"] == $responder_email) {
                                        echo $values["id"];
                                    } else if (isset($values["ref_field_id"]) && $values["ref_field_id"] == $responder_email) {
                                        echo $values["ref_field_id"];
                                    }
                                    ?>" style="display:none;width:128px;" class="arf_autocomplete" type="text" autocomplete="off">
                                    <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                    <dd>
                                        <ul class="arf_email_field_dropdown" style="display: none;" data-id="autoresponder_email">
                                            <li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select Email Field', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Select Email Field', 'ARForms')); ?></li>

                                            <?php echo $selectbox_field_options; ?>

                                        </ul>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <?php do_action('arf_condition_on_subscription_html', $id, '', $values); ?>
                    <div class="arf_mailoptin_content_container">
                        <p class="arftitle_p" style="margin-left: 0px;margin-bottom: 30px;"><label><?php echo esc_html__('Select Opt-in provider','ARForms');?></label></p>
                        <ul class="arf_optin_tabs">
                            <li class="arf_optin_tab_item arfactive" data-id="mailchimp"><?php echo addslashes(esc_html__('Mailchimp', 'ARForms')); ?></li>
                            <li class="arf_optin_tab_item" data-id="aweber"><?php echo addslashes(esc_html__('Aweber', 'ARForms')); ?></li>
                            <li class="arf_optin_tab_item" data-id="icontact"><?php echo addslashes(esc_html__('Icontact', 'ARForms')); ?></li>
                            <li class="arf_optin_tab_item" data-id="constant_contact"><?php echo addslashes(esc_html__('Constant Contact', 'ARForms')); ?></li>
                            <li class="arf_optin_tab_item" data-id="get_response"><?php echo addslashes(esc_html__('GetResponse', 'ARForms')); ?></li>
                            <li class="arf_optin_tab_item" data-id="madmimi"><?php echo addslashes(esc_html__('Madmimi', 'ARForms')); ?></li>
                            <li class="arf_optin_tab_item" data-id="ebizac"><?php echo addslashes(esc_html__('Ebizac.com', 'ARForms')); ?></li>
                            <li class="arf_optin_tab_item" data-id="gvo"><?php echo addslashes(esc_html__('GVO', 'ARForms')); ?></li>
                            <?php do_action('arf_email_marketers_tab_outside'); ?>
                        </ul>
                        <div class="arf_optin_tab_wrapper">
                            <div class="arf_optin_tab_inner_container arfactive" id="mailchimp">
                                <div>
                                <?php 
                                $style = '';
                                $style_gray = '';
                                if(isset($mailchimp_arr['enable']) && $mailchimp_arr['enable'] == 1)
                                {
                                    $style = 'style="display:block;"';
                                    $style_gray = 'style="display:none;"';                                    
                                } else{
                                    $style = 'style="display:none;"';
                                    $style_gray = 'style="display:block;"';                                    
                                }?>
                                <div class="arf_optin_logo mailchimp_original" <?php echo $style;?>><img src="<?php echo ARFIMAGESURL . '/mailchimp.png'; ?>"/></div>
                                <div class="arf_optin_logo mailchimp_gray" <?php echo $style_gray;?>><img src="<?php echo ARFIMAGESURL . '/mailchimp_gray.png'; ?>"/></div>
                                <div class="arf_optin_checkbox">
                                <div>
                                <label class="arf_js_switch_label">
                                    <span></span>
                                </label>
                                <span class="arf_js_switch_wrapper">
                                    <input type="checkbox" class="js-switch arf_disable_enable_optins" name="autoresponders[]" id="autores_1" value="1" <?php echo (isset($mailchimp_arr['enable']) && $mailchimp_arr['enable'] == 1) ? 'checked=checked' : ''; ?> data-attr="mailchimp"/>
                                    <span class="arf_js_switch"></span>
                                </span>
                                <label class="arf_js_switch_label" for="autores_1">
                                    <span>&nbsp;<?php echo addslashes(esc_html__('Enable', 'ARForms')); ?></span>
                                </label>                                
                                </div>
                                </div>
                                </div>
                                <div class="arf_option_configuration_wrapper mailchimp_configuration_wrapper <?php echo (isset($mailchimp_arr['enable']) && $mailchimp_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>">
                                    
                                    <br/>
                                    <?php
                                    $rand_num = rand(1111, 9999);
                                    if ($res['mailchimp_type'] == 1) {
                                        ?>
                                        <br/>
                                        <div id="select-autores_<?php echo $rand_num; ?>" class="select_autores">
                                            <?php
                                            if (( $arfaction == 'new' || ( $arfaction == 'duplicate' and $arf_template_id < 100 ) ) || (isset($global_enable_ar['mailchimp']) and $global_enable_ar['mailchimp'] == 0 and isset($mailchimp_arr['enable']) and $mailchimp_arr['enable'] == 0 )) {
                                                ?>
                                                <div id="autores-aweber" class="autoresponder_inner_block" style="margin-left: 25px;">
                                                    <div class="textarea_space"></div>
                                                    <span class="lblstandard"><?php echo addslashes(esc_html__('Select List Name', 'ARForms')); ?></span>
                                                    <div class="textarea_space"></div>
                                                    <div class="sltstandard">
                                                        <?php
                                                        $selected_list_id = "";
                                                        $selected_list_label = addslashes(esc_html__('Select List','ARForms'));
                                                        $responder_list_option = "";
                                                        $lists = json_decode($res2['responder_list_id'],true);
                                                        if (is_array($lists) && count($lists) > 0) {
                                                            $cntr = 0;
                                                            foreach ($lists as $list) {
                                                                if ($res2['responder_list'] == $list['id'] || $cntr == 0) {
                                                                    $selected_list_id = $list['id'];
                                                                    $selected_list_label = $list['name'];
                                                                }

                                                                $responder_list_option .= '<li class="arf_selectbox_option" data-value="' . $list['id'] . '" data-label="' . htmlentities($list['name']) . '">' . $list['name'] . '</li>';
                                                                $cntr++;
                                                            }
                                                        }
                                                        ?>
                                                        <input id="i_mailchimp_list" name="i_mailchimp_list" value="<?php echo $selected_list_id; ?>" type="hidden" class="frm-dropdown frm-pages-dropdown" <?php echo ( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?>>
                                                        <dl class="arf_selectbox <?php echo (isset($mailchimp_arr['enable']) && $mailchimp_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>" data-name="i_mailchimp_list" data-id="i_mailchimp_list" style="width:170px;">
                                                            <dt class="<?php echo (isset($mailchimp_arr['enable']) && $mailchimp_arr['enable'] == 1) ? '' : 'arf_disabled_container'; ?>"><span><?php echo $selected_list_label; ?></span>
                                                            <input value="<?php echo $selected_list_label; ?>" style="display:none;width:118px;" class="arf_autocomplete" type="text" autocomplete="off">
                                                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                                            <dd>
                                                                <ul class="field_dropdown_list_menu" style="display: none;" data-id="i_mailchimp_list">
                                                                <li class="arf_selectbox_option" data-value="" data-label="<?php addslashes(esc_html__('Select Field','ARForms'));?>"><?php echo addslashes(esc_html__('Select Field','ARForms'));?></li>
                                                                    <?php echo $responder_list_option; ?>
                                                                </ul>
                                                            </dd>
                                                        </dl>
                                                    </div>
                                                </div>
                                                <?php
                                            } else {
                                                ?>
                                                <div id="autores-aweber" class="autoresponder_inner_block" style="margin-left: 25px;">
                                                    <div class="textarea_space"></div>
                                                    <span class="lblstandard"><?php echo addslashes(esc_html__('Select List Name', 'ARForms')); ?></span>
                                                    <div class="textarea_space"></div>
                                                    <div class="sltstandard">
                                                        <?php
                                                        $selected_list_id = "";
                                                        $selected_list_label = addslashes(esc_html__('Select List', 'ARForms'));
                                                        $responder_list_option = "";
                                                        $lists = json_decode($res2['responder_list_id'],true);
                                                        
                                                        $default_mail_chimp_select_list = isset($res2['responder_list']) ? $res2['responder_list'] : '';
                                                        $selected_list_id_mailchimp = isset($mailchimp_arr['type_val']) ? $mailchimp_arr['type_val'] : $default_mail_chimp_select_list;
                                                        if (count($lists) > 0 && is_array($lists)) {
                                                            $cntr = 0;
                                                            foreach ($lists as $list) {
                                                                if ($selected_list_id_mailchimp == $list['id'] || $cntr == 0) {
                                                                    $selected_list_id = $list['id'];
                                                                    $selected_list_label = $list['name'];
                                                                }

                                                                $responder_list_option .= '<li class="arf_selectbox_option" data-value="' . $list['id'] . '" data-label="' . htmlentities($list['name']) . '">' . $list['name'] . '</li>';
                                                                $cntr++;
                                                            }
                                                        }
                                                        ?>
                                                        <input id="i_mailchimp_list" name="i_mailchimp_list" value="<?php echo $selected_list_id; ?>" type="hidden" class="frm-dropdown frm-pages-dropdown" <?php echo ( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?>>
                                                        <dl class="arf_selectbox <?php echo (isset($mailchimp_arr['enable']) && $mailchimp_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>" data-name="i_mailchimp_list" data-id="i_mailchimp_list" style="width:170px;">
                                                            <dt class="<?php echo (isset($mailchimp_arr['enable']) && $mailchimp_arr['enable'] == 1) ? '' : 'arf_disabled_container'; ?>"><span><?php echo $selected_list_label; ?></span>
                                                            <input value="<?php echo $selected_list_label; ?>" style="display:none;width:118px;" class="arf_autocomplete" type="text">
                                                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                                            <dd>
                                                                <ul class="field_dropdown_list_menu" style="display: none;" data-id="i_mailchimp_list">
                                                                <li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?></li>
                                                                    <?php echo $responder_list_option; ?>
                                                                </ul>
                                                            </dd>
                                                        </dl>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <?php
                                    } else {
                                        ?>
                                        <textarea class="auto_responder_webform_code_area txtmultimodal1" name="web_form_mailchimp" id="web_form_mailchimp" <?php echo ($setvaltolic != 1 ? "readonly=readonly" : ''); ?>><?php echo stripslashes_deep($res2['responder_web_form']); ?></textarea>
                                    <?php } ?>
                                    <span class="arf_enable_double_optin">
                                        <label class="arf_js_switch_label">
                                            <span style="margin-left: -6px;"><?php echo addslashes(esc_html__('Enable Double Opt-in', 'ARForms')); ?>&nbsp;&nbsp;</span>

                                        </label>
                                        <span class="arf_js_switch_wrapper <?php echo (isset($mailchimp_arr['enable']) && $mailchimp_arr['enable'] == 1) ? '' : 'arf_disable_switch'; ?>"  <?php if ($setvaltolic != 1) {echo 'onclick="return false"';}?>>
                                            <input type="checkbox" class="js-switch" name="options[arf_enable_double_optin]" <?php checked($double_optin,1); ?> id="arf_enable_double_optin" value="1" onclick="arf_mailchimp_double_opti();" />
                                            <span class="arf_js_switch"></span>
                                        </span>
                                        <label class="arf_js_switch_label" for="arf_enable_double_optin">
                                            <span></span>
                                        </label>  
                                    </span>
                                    <?php do_action('arf_map_malchimp_fields_outside',$values,$record,$responder_list_option,$mailchimp_arr); ?>
                                </div>
                            </div>
                            <div class="arf_optin_tab_inner_container" id="aweber">
                                <div>
                                <?php 
                                $style = '';
                                $style_gray = '';
                                
                                if(isset($aweber_arr['enable']) && $aweber_arr['enable'] == 1)
                                {
                                    $style = 'style="display:block;"';
                                    $style_gray = 'style="display:none;"';                                    
                                } else{
                                    $style = 'style="display:none;"';
                                    $style_gray = 'style="display:block;"';                                    
                                }?>
                                <div class="arf_optin_logo aweber_original" <?php echo $style;?>><img src="<?php echo ARFIMAGESURL . '/aweber.png'; ?>"/></div>
                                <div class="arf_optin_logo aweber_gray" <?php echo $style_gray;?>><img src="<?php echo ARFIMAGESURL . '/aweber_gray.png'; ?>"/></div>
                                <div class="arf_optin_checkbox">
                                    <label class="arf_js_switch_label">
                                        <span></span>
                                    </label>
                                    <span class="arf_js_switch_wrapper">                                        
                                        <input type="checkbox" class="js-switch arf_disable_enable_optins" name="autoresponders[]" id="autores_3" value="3" <?php echo (isset($aweber_arr['enable']) && $aweber_arr['enable'] == 1) ? 'checked=checked' : ''; ?> data-attr="aweber"/>
                                        <span class="arf_js_switch"></span>
                                    </span>
                                    <label class="arf_js_switch_label" for="autores_3">
                                        <span>&nbsp;<?php echo addslashes(esc_html__('Enable', 'ARForms')); ?></span>
                                    </label>                                
                                </div>
                                </div>                                
                                <div class="arf_option_configuration_wrapper aweber_configuration_wrapper <?php echo (isset($aweber_arr['enable']) && $aweber_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>">                                    
                                    <br/><br/>
                                    <?php
                                    $rand_num = rand(1111, 9999);
                                    if ($res['aweber_type'] == 1) {
                                        $aweber_data = $res1;
                                        $is_aweber_old = false;
                                        if( ($aweber_data['consumer_key'] != '' && $aweber_data['consumer_secret'] != '' && ($aweber_data['consumer_key'] != ARF_AWEBER_CONSUMER_KEY && $aweber_data['consumer_secret'] != ARF_AWEBER_CONSUMER_SECRET) ) ){
                                            $is_aweber_old = true;
                                        }
                                        ?>
                                        <div id="select-autores_<?php echo $rand_num; ?>" class="select_autores">
                                            <?php
                                            if (($arfaction == 'new' || ($arfaction == 'duplicate' and $arf_template_id < 100)) || (isset($global_enable_ar['aweber']) and $global_enable_ar['aweber'] == 0 and isset($aweber_arr['enable']) and $aweber_arr['enable'] == 0 )) {
                                                ?>
                                                <div id="autores-aweber"  class="autoresponder_inner_block" style="margin-left: 25px;">
                                                    <div class="textarea_space"></div>
                                                    <span class="lblstandard"><?php echo addslashes(esc_html__('Select List Name', 'ARForms')); ?></span>
                                                    <div class="textarea_space"></div>
                                                    <div class="sltstandard">
                                                        <?php
                                                        $aweber_lists = explode("-|-", $aweber_data['responder_list_id']);
                                                        $i = 0;
                                                        $selected_list_id = "";
                                                        $selected_list_label = addslashes(esc_html__('Select List', 'ARForms'));
                                                        $responder_list_option = "";
                                                        $cntr = 0;
                                                        if (!empty($aweber_lists[0]) && false == $is_aweber_old) {
                                                            $aweber_lists_name = explode("|", $aweber_lists[0]);
                                                            $aweber_lists_id = explode("|", $aweber_lists[1]);

                                                            if (count($aweber_lists_name) > 0 && is_array($aweber_lists_name)) {
                                                                foreach ($aweber_lists_name as $aweber_lists_name1) {
                                                                    if ($aweber_lists_id[$i] != "") {
                                                                        if ($aweber_lists_id[$i] == $aweber_data['responder_list'] || $cntr == 0) {
                                                                            $selected_list_id = $aweber_lists_id[$i];
                                                                            $selected_list_label = $aweber_lists_name1;
                                                                        }
                                                                        $responder_list_option .= '<li class="arf_selectbox_option" data-value="' . $aweber_lists_id[$i] . '" data-label="' . htmlentities($aweber_lists_name1) . '">' . $aweber_lists_name1 . '</li>';
                                                                        $cntr++;
                                                                    }
                                                                    $i++;
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                        <input id="i_aweber_list" name="i_aweber_list" value="<?php echo $selected_list_id; ?>" type="hidden" class="frm-dropdown frm-pages-dropdown" <?php echo ( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?>>
                                                        <dl class="arf_selectbox <?php echo (isset($aweber_arr['enable']) && $aweber_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>" data-name="i_aweber_list" data-id="i_aweber_list" style="width:170px;">
                                                            <dt class="<?php echo (isset($aweber_arr['enable']) && $aweber_arr['enable'] == 1) ? '' : 'arf_disabled_container'; ?>"><span><?php echo $selected_list_label; ?></span>
                                                            <input value="<?php echo $selected_list_label; ?>" style="display:none;width:118px;" class="arf_autocomplete" type="text" autocomplete="off">
                                                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                                            <dd>
                                                                <ul class="field_dropdown_list_menu" style="display: none;" data-id="i_aweber_list">
                                                                <li class="arf_selectbox_option" data-value="" data-label="<?php addslashes(esc_html__('Select Field','ARForms'));?>"><?php echo addslashes(esc_html__('Select Field','ARForms'));?></li>
                                                                    <?php echo $responder_list_option; ?>
                                                                </ul>
                                                            </dd>
                                                        </dl>
                                                    </div>
                                                </div>
                                                <?php
                                            } else {
                                                ?>
                                                <div id="autores-aweber" class="autoresponder_inner_block" style="margin-left: 25px;">
                                                    <div class="textarea_space"></div>
                                                    <span class="lblstandard"><?php echo addslashes(esc_html__('Select List Name', 'ARForms')); ?></span>
                                                    <div class="textarea_space"></div>
                                                    <div class="sltstandard">
                                                        <?php
                                                        $aweber_lists = explode("-|-", $aweber_data['responder_list_id']);
                                                        $aweber_lists_name = explode("|", $aweber_lists[0]);
                                                        $i = 0;
                                                        $selected_list_id = "";
                                                        $selected_list_label = addslashes(esc_html__("Select List","ARForms"));
                                                        $responder_list_option = "";
                                                        $cntr = 0;
                                                        if (!empty($aweber_lists[0]) && false == $is_aweber_old) {
                                                            if (count($aweber_lists_name) > 0 && is_array($aweber_lists_name)) {
                                                                $aweber_lists_id = isset($aweber_lists[1]) ? explode("|", $aweber_lists[1]) : '';

                                                                foreach ($aweber_lists_name as $aweber_lists_name1) {
                                                                    if ($aweber_lists_id[$i] != "") {
                                                                        if ($aweber_lists_id[$i] == $aweber_arr['type_val'] || $cntr == 0) {
                                                                            $selected_list_id = $aweber_lists_id[$i];
                                                                            $selected_list_label = $aweber_lists_name1;
                                                                        }
                                                                        $responder_list_option .= '<li class="arf_selectbox_option" data-value="' . $aweber_lists_id[$i] . '" data-label="' . htmlentities($aweber_lists_name1) . '">' . $aweber_lists_name1 . '</li>';
                                                                        $cntr++;
                                                                    }
                                                                    $i++;
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                        <input id="i_aweber_list" name="i_aweber_list" value="<?php echo $selected_list_id; ?>" type="hidden" class="frm-dropdown frm-pages-dropdown" <?php echo ( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?>>
                                                        <dl class="arf_selectbox <?php echo (isset($aweber_arr['enable']) && $aweber_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>" data-name="i_aweber_list" data-id="i_aweber_list" style="width:170px;">
                                                            <dt class="<?php echo (isset($aweber_arr['enable']) && $aweber_arr['enable'] == 1) ? '' : 'arf_disabled_container'; ?>"><span><?php echo $selected_list_label; ?></span>
                                                            <input value="<?php echo $selected_list_label; ?>" style="display:none;width:118px;" class="arf_autocomplete" type="text">
                                                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                                            <dd>
                                                                <ul class="field_dropdown_list_menu" style="display: none;" data-id="i_aweber_list">
                                                                <li class="arf_selectbox_option" data-value="" data-label="<?php addslashes(esc_html__('Select Field','ARForms'));?>"><?php echo addslashes(esc_html__('Select Field','ARForms'));?></li>
                                                                    <?php echo $responder_list_option; ?>
                                                                </ul>
                                                            </dd>
                                                        </dl>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <?php
                                    } else {
                                        ?>
                                        <textarea class="auto_responder_webform_code_area txtmultimodal1" name="web_form_aweber" id="web_form_aweber" style="width:100%; height:100px;" <?php echo( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?>><?php echo stripslashes_deep($res1['responder_web_form']); ?></textarea> <?php
                                    }
                                    ?>
                                    <?php do_action('arf_map_aweber_fields_outside',$values,$record,$responder_list_option,$aweber_arr); ?>
                                </div>
                            </div>
                            <div class="arf_optin_tab_inner_container" id="icontact">
                                <div>
                                <?php 
                                $style = '';
                                $style_gray = '';
                                if(isset($icontact_arr['enable']) && $icontact_arr['enable'] == 1)
                                {
                                    $style = 'style="display:block;"';
                                    $style_gray = 'style="display:none;"';                                    
                                } else{
                                    $style = 'style="display:none;"';
                                    $style_gray = 'style="display:block;"';                                    
                                }?>
                                <div class="arf_optin_logo icontact_original" <?php echo $style;?>><img src="<?php echo ARFIMAGESURL . '/icontact.png'; ?>"/></div>
                                <div class="arf_optin_logo icontact_gray" <?php echo $style_gray;?>><img src="<?php echo ARFIMAGESURL . '/icontact_gray.png'; ?>"/></div>
                                <div class="arf_optin_checkbox">
                                <label class="arf_js_switch_label">
                                    <span></span>
                                </label>
                                <span class="arf_js_switch_wrapper ">
                                    <input type="checkbox" class="js-switch arf_disable_enable_optins" name="autoresponders[]" id="autores_8" value="8" <?php echo (isset($icontact_arr['enable']) && $icontact_arr['enable'] == 1) ? 'checked=checked' : ''; ?> data-attr="icontact"/>
                                    <span class="arf_js_switch"></span>
                                </span>
                                <label class="arf_js_switch_label" for="autores_8">
                                    <span>&nbsp;<?php echo addslashes(esc_html__('Enable', 'ARForms')); ?></span>
                                </label>                                
                                </div>
                                </div>                                
                                <div class="arf_option_configuration_wrapper icontact_configuration_wrapper <?php echo (isset($icontact_arr['enable']) && $icontact_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>">                                    
                                    <br/><br/>
                                    <?php
                                    $rand_num = rand(1111, 9999);
                                    if ($res['icontact_type'] == 1) {
                                        ?>
                                        <div id="select-autores_<?php echo $rand_num; ?>" class="select_autores" style="margin-left: 25px;">
                                            <?php
                                            if (( $arfaction == 'new' || ( $arfaction == 'duplicate' and $arf_template_id < 100 ) ) || (isset($global_enable_ar['icontact']) and $global_enable_ar['icontact'] == 0 and isset($icontact_arr['enable']) and $icontact_arr['enable'] == 0 )) {
                                                ?>
                                                <div id="autores-icontact" class="autoresponder_inner_block" style="margin-top:0px;">
                                                    <div class="textarea_space"></div>
                                                    <span class="lblstandard"><?php echo addslashes(esc_html__('Select List Name', 'ARForms')); ?></span>
                                                    <div class="textarea_space"></div>
                                                    <div class="sltstandard">
                                                        <?php
                                                        $selected_list_id = "";
                                                        $selected_list_label = addslashes(esc_html__('Select List', 'ARForms'));
                                                        $responder_list_option = "";
                                                        $cntr = 0;
                                                        $lists = maybe_unserialize($res6['responder_list_id']);
                                                        if (is_array($lists) && count($lists) > 0 ) {

                                                            foreach ($lists as $list) {
                                                                if ($res6['responder_list'] == $list->listId || $cntr == 0) {
                                                                    $selected_list_id = $list->listId;
                                                                    $selected_list_label = $list->name;
                                                                }
                                                                $responder_list_option .= '<li class="arf_selectbox_option" data-value="' . $list->listId . '" data-label="' . htmlentities($list->name) . '">' . $list->name . '</li>';
                                                                $cntr++;
                                                            }
                                                        }
                                                        ?>
                                                        <input id="i_icontact_list" name="i_icontact_list" value="<?php echo $selected_list_id; ?>" type="hidden" class="frm-dropdown frm-pages-dropdown" <?php echo ( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?>>
                                                        <dl class="arf_selectbox <?php echo (isset($icontact_arr['enable']) && $icontact_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>" data-name="i_icontact_list" data-id="i_icontact_list" style="width:170px;">
                                                            <dt class="<?php echo (isset($icontact_arr['enable']) && $icontact_arr['enable'] == 1) ? '' : 'arf_disabled_container'; ?>"><span><?php echo $selected_list_label; ?></span>
                                                            <input value="<?php echo $selected_list_label; ?>" style="display:none;width:118px;" class="arf_autocomplete" type="text" autocomplete="off">
                                                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                                            <dd>
                                                                <ul class="field_dropdown_list_menu" style="display: none;" data-id="i_icontact_list">
                                                                <li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select Field','ARForms'));?>"><?php echo addslashes(esc_html__('Select Field','ARForms'));?></li>
                                                                    <?php echo $responder_list_option; ?>
                                                                </ul>
                                                            </dd>
                                                        </dl>
                                                    </div>
                                                </div>
                                                <?php
                                            } else {
                                                ?>
                                                <div id="autores-aweber" class="autoresponder_inner_block" style="margin-top:0px;">
                                                    <div class="textarea_space"></div>
                                                    <span class="lblstandard"><?php echo addslashes(esc_html__('Select List Name', 'ARForms')); ?></span>
                                                    <div class="textarea_space"></div>
                                                    <div class="sltstandard">
                                                        <?php
                                                        $selected_list_id = "";
                                                        $selected_list_label = addslashes(esc_html__('Select List','ARForms'));
                                                        $responder_list_option = "";
                                                        $cntr = 0;
                                                        $lists = maybe_unserialize($res6['responder_list_id']);
                                                        if (count($lists) > 0 && is_array($lists)) {
                                                            foreach ($lists as $list) {
                                                                if ($icontact_arr['type_val'] == $list->listId || $cntr == 0) {
                                                                    $selected_list_id = $list->listId;
                                                                    $selected_list_label = $list->name;
                                                                }
                                                                $responder_list_option .= '<li class="arf_selectbox_option" data-value="' . $list->listId . '" data-label="' . htmlentities($list->name) . '">' . $list->name . '</li>';
                                                                $cntr++;
                                                            }
                                                        }
                                                        ?>

                                                        <input id="i_icontact_list" name="i_icontact_list" value="<?php echo $selected_list_id; ?>" type="hidden" class="frm-dropdown frm-pages-dropdown" <?php echo ( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?>>
                                                        <dl class="arf_selectbox <?php echo (isset($icontact_arr['enable']) && $icontact_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>" data-name="i_icontact_list" data-id="i_icontact_list" style="width:170px;">
                                                            <dt class="<?php echo (isset($icontact_arr['enable']) && $icontact_arr['enable'] == 1) ? '' : 'arf_disabled_container'; ?>"><span><?php echo $selected_list_label; ?></span>
                                                            <input value="<?php echo $selected_list_label; ?>" style="display:none;width:118px;" class="arf_autocomplete" type="text" autocomplete="off">
                                                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                                            <dd>
                                                                <ul class="field_dropdown_list_menu" style="display: none;" data-id="i_icontact_list">
                                                                <li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select Field','ARForms'));?>"><?php echo addslashes(esc_html__('Select Field','ARForms'));?></li>
                                                                    <?php echo $responder_list_option; ?>
                                                                </ul>
                                                            </dd>
                                                        </dl>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <?php
                                    } else {
                                        ?>
                                        <textarea class="auto_responder_webform_code_area txtmultimodal1" name="web_form_icontact" id="web_form_icontact" style="width:100%; height:100px;" <?php echo ( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?>><?php echo stripslashes_deep($res6['responder_web_form']); ?></textarea>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="arf_optin_tab_inner_container" id="constant_contact">
                                <div>
                                <?php 
                                $style = '';
                                $style_gray = '';
                                if(isset($constant_contact_arr['enable']) && $constant_contact_arr['enable'] == 1)
                                {
                                    $style = 'display:block;';
                                    $style_gray = 'display:none;';                                    
                                } else{
                                    $style = 'display:none;';
                                    $style_gray = 'display:block;';                                    
                                }?>
                                <div class="arf_optin_logo constant_contact_original arfconstantconstant" style="<?php echo $style;?>"><img src="<?php echo ARFIMAGESURL . '/constant-contact.png'; ?>"/></div>
                                <div class="arf_optin_logo constant_contact_gray arfconstantconstant" style="<?php echo $style_gray;?>"><img src="<?php echo ARFIMAGESURL . '/constant_contact_gray.png'; ?>"/></div>
                                <div class="arf_optin_checkbox">
                                <label class="arf_js_switch_label">
                                    <span></span>
                                </label>
                                <span class="arf_js_switch_wrapper">
                                    <input type="checkbox" class="js-switch arf_disable_enable_optins" name="autoresponders[]" id="autores_9" value="9" <?php echo (isset($constant_contact_arr['enable']) && $constant_contact_arr['enable'] == 1) ? 'checked=checked' : ''; ?> data-attr="constant_contact"/>
                                    <span class="arf_js_switch"></span>
                                </span>
                                <label class="arf_js_switch_label" for="autores_9">
                                    <span>&nbsp;<?php echo addslashes(esc_html__('Enable', 'ARForms')); ?></span>
                                </label>                                
                                </div>
                                </div>                                
                                <div class="arf_option_configuration_wrapper constant_contact_configuration_wrapper <?php echo (isset($constant_contact_arr['enable']) && $constant_contact_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>">
                                    <br/><br/>
                                    <?php
                                    $rand_num = rand(1111, 9999);
                                    if ($res['constant_type'] == 1) {
                                        ?>
                                        <div id="select-autores_<?php echo $rand_num; ?>" class="select_autores" style="margin-left: 25px;">
                                            <?php
                                            if (( $arfaction == 'new' || ( $arfaction == 'duplicate' and $arf_template_id < 100 ) ) || (isset($global_enable_ar['constant_contact']) and $global_enable_ar['constant_contact'] == 0 and isset($constant_contact_arr['enable']) and $constant_contact_arr['enable'] == 0 )) {
                                                ?>
                                                <div id="autores-constant_contact" class="autoresponder_inner_block">
                                                    <div class="textarea_space"></div>
                                                    <span class="lblstandard"><?php echo addslashes(esc_html__('Select List Name', 'ARForms')); ?></span>
                                                    <div class="textarea_space"></div>
                                                    <div class="sltstandard">
                                                        <?php
                                                        $selected_list_id = "";
                                                        $selected_list_label = addslashes(esc_html__('Select List','ARForms'));
                                                        $responder_list_option = "";
                                                        $cntr = 0;
                                                        $lists_new = maybe_unserialize($res7['list_data']);

                                                        if (is_array($lists_new) && count($lists_new) > 0 ) {

                                                            foreach ($lists_new as $list) {
                                                                if ($res7['responder_list'] == $list['id'] || $cntr == 0) {
                                                                    $selected_list_id = $list['id'];
                                                                    $selected_list_label = $list['name'];
                                                                }
                                                                $responder_list_option .= '<li class="arf_selectbox_option" data-value="' . $list['id'] . '" data-label="' . htmlentities($list['name']) . '">' . $list['name'] . '</li>';
                                                                $cntr++;
                                                            }
                                                        }
                                                        ?>
                                                        <input id="i_constant_contact_list" name="i_constant_contact_list" value="<?php echo $selected_list_id; ?>" type="hidden" class="frm-dropdown frm-pages-dropdown" <?php echo ( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?>>
                                                        <dl class="arf_selectbox <?php echo (isset($constant_contact_arr['enable']) && $constant_contact_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>" data-name="i_constant_contact_list" data-id="i_constant_contact_list" style="width:170px;">
                                                            <dt class="<?php echo (isset($constant_contact_arr['enable']) && $constant_contact_arr['enable'] == 1) ? '' : 'arf_disabled_container'; ?>"><span><?php echo $selected_list_label; ?></span>
                                                            <input value="<?php echo $selected_list_label; ?>" style="display:none;width:118px;" class="arf_autocomplete" type="text">
                                                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                                            <dd>
                                                                <ul class="field_dropdown_list_menu" style="display: none;" data-id="i_constant_contact_list">
                                                                <li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select Field','ARForms'));?>"><?php echo addslashes(esc_html__('Select Field','ARForms'));?></li>
                                                                    <?php echo $responder_list_option; ?>
                                                                </ul>
                                                            </dd>
                                                        </dl>
                                                    </div>
                                                </div>
                                                <?php
                                            } else {
                                                ?>
                                                <div id="autores-constant_contact" class="autoresponder_inner_block">
                                                    <div class="textarea_space"></div>
                                                    <span class="lblstandard"><?php echo addslashes(esc_html__('Select List Name', 'ARForms')); ?></span>
                                                    <div class="textarea_space"></div>
                                                    <div class="sltstandard">
                                                        <?php
                                                        $selected_list_id = "";
                                                        $selected_list_label = addslashes(esc_html__('Select List','ARForms'));
                                                        $responder_list_option = "";
                                                        $cntr = 0;
                                                        $lists_new = maybe_unserialize($res7['list_data']);
                                                        if (count($lists_new) > 0 && is_array($lists_new)) {
                                                            foreach ($lists_new as $list) {
                                                                if ($constant_contact_arr['type_val'] == $list['id']) {
                                                                    $selected_list_id = $list['id'];
                                                                    $selected_list_label = $list['name'];
                                                                }
                                                                $responder_list_option .= '<li class="arf_selectbox_option" data-value="' . $list['id'] . '" data-label="' . htmlentities($list['name']) . '">' . $list['name'] . '</li>';
                                                                $cntr++;
                                                            }
                                                        }
                                                        ?>
                                                        <input id="i_constant_contact_list" name="i_constant_contact_list" value="<?php echo $selected_list_id; ?>" type="hidden" class="frm-dropdown frm-pages-dropdown" <?php echo ( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?>>
                                                        <dl class="arf_selectbox <?php echo (isset($constant_contact_arr['enable']) && $constant_contact_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>" data-name="i_constant_contact_list" data-id="i_constant_contact_list" style="width:170px;">
                                                            <dt class="<?php echo (isset($constant_contact_arr['enable']) && $constant_contact_arr['enable'] == 1) ? '' : 'arf_disabled_container'; ?>"><span><?php echo $selected_list_label; ?></span>
                                                            <input value="<?php echo $selected_list_label; ?>" style="display:none;width:118px;" class="arf_autocomplete" type="text" autocomplete="off">
                                                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                                            <dd>
                                                                <ul class="field_dropdown_list_menu" style="display: none;" data-id="i_constant_contact_list">
                                                                <li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select Field','ARForms'));?>"><?php echo addslashes(esc_html__('Select Field','ARForms'));?></li>
                                                                    <?php echo $responder_list_option; ?>
                                                                </ul>
                                                            </dd>
                                                        </dl>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <?php
                                    } else {
                                        ?>
                                        <textarea class="auto_responder_webform_code_area txtmultimodal1" name="web_form_constant_contact" id="web_form_constant_contact" style="width:100%; height:100px;" <?php echo ( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?>><?php echo stripslashes_deep($res7['responder_web_form']); ?></textarea>
                                        <?php
                                    }
                                    ?>
                                    <?php do_action('arf_map_constant_contact_fields_outside',$values,$record,$responder_list_option,$constant_contact_arr); ?>
                                </div>
                            </div>
                            <div class="arf_optin_tab_inner_container" id="get_response">
                                <div>
                                <?php 
                                $style = '';
                                $style_gray = '';
                                if(isset($getresponse_arr['enable']) && $getresponse_arr['enable'] == 1)
                                {
                                    $style = 'style="display:block;"';
                                    $style_gray = 'style="display:none;"';                                    
                                } else{
                                    $style = 'style="display:none;"';
                                    $style_gray = 'style="display:block;"';                                    
                                }?>
                                <div class="arf_optin_logo getresponse_original" <?php echo $style;?>><img src="<?php echo ARFIMAGESURL . '/getresponse.png'; ?>"/></div>
                                <div class="arf_optin_logo getresponse_gray" <?php echo $style_gray;?>><img src="<?php echo ARFIMAGESURL . '/getresponse_gray.png'; ?>"/></div>
                                <div class="arf_optin_checkbox">
                                <label class="arf_js_switch_label">
                                    <span></span>
                                </label>
                                <span class="arf_js_switch_wrapper">
                                    <input type="checkbox" class="js-switch arf_disable_enable_optins" name="autoresponders[]" id="autores_4" value="4" <?php echo (isset($getresponse_arr['enable']) && $getresponse_arr['enable'] == 1) ? 'checked=checked' : ''; ?> data-attr="getresponse"/>
                                    <span class="arf_js_switch"></span>
                                </span>
                                <label class="arf_js_switch_label" for="autores_4">
                                    <span>&nbsp;<?php echo addslashes(esc_html__('Enable', 'ARForms')); ?></span>
                                </label>                                
                                </div>
                                </div>
                                <div class="arf_option_configuration_wrapper getresponse_configuration_wrapper <?php echo (isset($getresponse_arr['enable']) && $getresponse_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>">                                    

                                    <br/><br/>
                                    <?php
                                    $rand_num = rand(1111, 9999);
                                    if ($res['getresponse_type'] == 1) {
                                        ?>
                                        <div id="select-autores_<?php echo $rand_num; ?>" class="select_autores" style="margin-left: 25px;">
                                            <?php
                                            if (( $arfaction == 'new' || ( $arfaction == 'duplicate' and $arf_template_id < 100 ) ) || (isset($global_enable_ar['getresponse']) and $global_enable_ar['getresponse'] == 0 and isset($getresponse_arr['enable']) and $getresponse_arr['enable'] == 0 )) {
                                                ?>
                                                <div id="autores-getresponse" class="autoresponder_inner_block">
                                                    <div class="textarea_space"></div>
                                                    <span class="lblstandard"><?php echo addslashes(esc_html__('Campaign Name', 'ARForms')); ?></span>
                                                    <div class="textarea_space"></div>
                                                    <div class="sltstandard">
                                                        <?php
                                                        $selected_list_id = "";
                                                        $selected_list_label = addslashes(esc_html__('Select Field','ARForms'));
                                                        $responder_list_option = "";
                                                        $cntr = 0;
                                                        $lists = maybe_unserialize($res3['list_data']);
                                                        if ( is_array($lists) && count($lists) > 0 ) {
                                                            foreach ($lists as $listid => $list) {
                                                                if ($res3['responder_list_id'] == $list['name']) {
                                                                    $selected_list_id = $list['name'];
                                                                    $selected_list_label = $list['name'];
                                                                }
                                                                $responder_list_option .= '<li class="arf_selectbox_option" data-value="' . $list['name'] . '" data-label="' . htmlentities($list['name']) . '">' . $list['name'] . '</li>';
                                                                $cntr++;
                                                            }
                                                        }
                                                        ?>
                                                        <input id="i_campain_name" name="i_campain_name" value="<?php echo $selected_list_id; ?>" type="hidden" class="frm-dropdown frm-pages-dropdown"<?php echo ( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?>>
                                                        <dl class="arf_selectbox <?php echo (isset($getresponse_arr['enable']) && $getresponse_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>" data-name="i_campain_name" data-id="i_campain_name" style="width:170px;">
                                                            <dt class="<?php echo (isset($getresponse_arr['enable']) && $getresponse_arr['enable'] == 1) ? '' : 'arf_disabled_container'; ?>"><span><?php echo $selected_list_label; ?></span>
                                                            <input value="<?php echo $selected_list_label; ?>" style="display:none;width:118px;" class="arf_autocomplete" type="text" autocomplete="off">
                                                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                                            <dd>
                                                                <ul class="field_dropdown_list_menu" style="display: none;" data-id="i_campain_name">
                                                                <li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select Field','ARForms'));?>"><?php echo addslashes(esc_html__('Select Field','ARForms'));?></li>
                                                                    <?php echo $responder_list_option; ?>
                                                                </ul>
                                                            </dd>
                                                        </dl>
                                                    </div>
                                                </div>
                                                <?php
                                            } else {
                                                ?>
                                                <div id="autores-getresponse" class="autoresponder_inner_block">
                                                    <div class="textarea_space"></div>
                                                    <span class="lblstandard"><?php echo addslashes(esc_html__('Campaign Name', 'ARForms')); ?></span>
                                                    <div class="textarea_space"></div>
                                                    <div class="sltstandard">
                                                        <?php
                                                        $selected_list_id = "";
                                                        $selected_list_label = addslashes(esc_html__('Select Field','ARForms'));
                                                        $responder_list_option = "";
                                                        $cntr = 0;
                                                        $lists = maybe_unserialize($res3['list_data']);
                                                        if (count($lists) > 0 && is_array($lists)) {
                                                            foreach ($lists as $listid => $list) {
                                                                if ($getresponse_arr['type_val'] == $list['name']) {
                                                                    $selected_list_id = $list['name'];
                                                                    $selected_list_label = $list['name'];
                                                                }
                                                                $responder_list_option .= '<li class="arf_selectbox_option" data-value="' . $list['name'] . '" data-label="' . htmlentities($list['name']) . '">' . $list['name'] . '</li>';
                                                                $cntr++;
                                                            }
                                                        }
                                                        ?>
                                                        <input id="i_campain_name" name="i_campain_name" value="<?php echo $selected_list_id; ?>" type="hidden" class="frm-dropdown frm-pages-dropdown" <?php echo ( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?> />
                                                        <dl class="arf_selectbox <?php echo (isset($getresponse_arr['enable']) && $getresponse_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>" data-name="i_campain_name" data-id="i_campain_name" style="width:170px;">
                                                            <dt class="<?php echo (isset($getresponse_arr['enable']) && $getresponse_arr['enable'] == 1) ? '' : 'arf_disabled_container'; ?>"><span><?php echo $selected_list_label; ?></span>
                                                            <input value="<?php echo $selected_list_label; ?>" style="display:none;width:118px;" class="arf_autocomplete" type="text" autocomplete="off">
                                                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                                            <dd>
                                                                <ul class="field_dropdown_list_menu" style="display: none;" data-id="i_campain_name">
                                                                <li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select Field','ARForms'));?>"><?php echo addslashes(esc_html__('Select Field','ARForms'));?></li>
                                                                    <?php echo $responder_list_option; ?>
                                                                </ul>
                                                            </dd>
                                                        </dl>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <?php
                                    } else {
                                        ?>
                                        <textarea class="auto_responder_webform_code_area txtmultimodal1" name="web_form_getresponse" id="web_form_getresponse" style="width:100%; height:100px;" <?php echo ( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?>><?php echo stripslashes_deep($res3['responder_web_form']); ?></textarea>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="arf_optin_tab_inner_container" id="ebizac">
                                <div>
                                <?php 
                                $style = '';
                                $style_gray = '';
                                if(isset($ebizac_arr['enable']) && $ebizac_arr['enable'] == 1)
                                {
                                    $style = 'style="display:block;"';
                                    $style_gray = 'style="display:none;"';                                    
                                } else{
                                    $style = 'style="display:none;"';
                                    $style_gray = 'style="display:block;"';                                    
                                }?>
                                <div class="arf_optin_logo ebizac_original" <?php echo $style;?>><img src="<?php echo ARFIMAGESURL . '/ebizac.png'; ?>"/></div>
                                <div class="arf_optin_logo ebizac_gray" <?php echo $style_gray;?>><img src="<?php echo ARFIMAGESURL . '/ebizac_gray.png'; ?>"/></div>
                                <div class="arf_optin_checkbox">
                                <label class="arf_js_switch_label">
                                    <span></span>
                                </label>
                                <span class="arf_js_switch_wrapper">
                                    <input type="checkbox" class="js-switch arf_disable_enable_optins" name="autoresponders[]" id="autores_6" value="6" <?php echo (isset($ebizac_arr['enable']) && $ebizac_arr['enable'] == 1) ? 'checked=checked' : ''; ?> data-attr="ebizac"/>
                                    <span class="arf_js_switch"></span>
                                </span>
                                <label class="arf_js_switch_label" for="autores_6">
                                    <span>&nbsp;<?php echo addslashes(esc_html__('Enable', 'ARForms')); ?></span>
                                </label>                                
                                </div>
                                </div>
                                <div class="arf_option_configuration_wrapper ebizac_configuration_wrapper <?php echo (isset($ebizac_arr['enable']) && $ebizac_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>" >
                                    <?php
                                    $rand_num = rand(1111, 9999);
                                    ?>
                                    <div id="select-autores_<?php echo $rand_num; ?>" style="margin-left: 25px;">
                                        <?php
                                        if (( $arfaction == 'new' || ( $arfaction == 'duplicate' and $arf_template_id < 100 ) ) || (isset($global_enable_ar['ebizac']) and $global_enable_ar['ebizac'] == 0 and isset($ebizac_arr['enable']) and $ebizac_arr['enable'] == 0 )) {
                                            ?>
                                            <textarea class="auto_responder_webform_code_area txtmultimodal1 arfebizactextarea " name="web_form_ebizac" id="web_form_ebizac" style="height:100px;" <?php echo ( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?> <?php echo (isset($ebizac_arr['enable']) && $ebizac_arr['enable'] == 1) ? '' : 'readonly=readonly'; ?> > <?php echo stripslashes_deep($res5['responder_api_key']); ?> </textarea>
                                            <?php
                                        } else {
                                            $ebizac_arr['type_val'] = isset($ebizac_arr['type_val']) ? $ebizac_arr['type_val'] : '';
                                            ?>
                                            <textarea class="auto_responder_webform_code_area txtmultimodal1 arfebizactextarea" name="web_form_ebizac" id="web_form_ebizac" style="height:100px;" <?php echo( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?>><?php echo stripslashes_deep($ebizac_arr['type_val']); ?></textarea>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="arf_optin_tab_inner_container" id="gvo">
                                <div>
                                <?php 
                                $style = '';
                                $style_gray = '';
                                if(isset($gvo_arr['enable']) && $gvo_arr['enable'] == 1)
                                {
                                    $style = 'style="display:block;"';
                                    $style_gray = 'style="display:none;"';                                    
                                } else{
                                    $style = 'style="display:none;"';
                                    $style_gray = 'style="display:block;"';                                    
                                }?>
                                <div class="arf_optin_logo gvo_original arfgvo" <?php echo $style;?>><img src="<?php echo ARFIMAGESURL . '/gvo.png'; ?>"/></div>
                                <div class="arf_optin_logo gvo_gray arfgvo" <?php echo $style_gray;?>><img src="<?php echo ARFIMAGESURL . '/gvo_gray.png'; ?>"/></div>
                                <div class="arf_optin_checkbox">
                                <label class="arf_js_switch_label">
                                    <span></span>
                                </label>
                                <span class="arf_js_switch_wrapper">
                                    <input type="checkbox" class="js-switch arf_disable_enable_optins" name="autoresponders[]" id="autores_5" value="5" <?php echo (isset($gvo_arr['enable']) && $gvo_arr['enable'] == 1) ? 'checked=checked' : ''; ?> data-attr="gvo"/>
                                    <span class="arf_js_switch"></span>
                                </span>
                                <label class="arf_js_switch_label" for="autores_5">
                                    <span>&nbsp;<?php echo addslashes(esc_html__('Enable', 'ARForms')); ?></span>
                                </label>                                
                                </div>
                                </div>                                
                                <div class="arf_option_configuration_wrapper gvo_configuration_wrapper <?php echo (isset($gvo_arr['enable']) && $gvo_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>">
                                    <?php
                                    $rand_num = rand(1111, 9999); ?>
                                    <br/>
                                    <div id="select-autores_<?php echo $rand_num; ?>" style="margin-left: 25px;">
                                    <?php
                                    if (( $arfaction == 'new' || ( $arfaction == 'duplicate' && $arf_template_id < 100 ) ) || (isset($global_enable_ar['gvo']) && $global_enable_ar['gvo'] == 0 && isset($gvo_arr['enable']) && $gvo_arr['enable'] == 0 )) {
                                        ?>
                                        <textarea class="auto_responder_webform_code_area txtmultimodal1 arfgvotextarea" name="web_form_gvo" id="web_form_gvo" style="height:100px;" <?php echo ( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?> <?php echo (isset($gvo_arr['enable']) && $gvo_arr['enable'] == 1) ? '' : 'readonly=readonly'; ?>> <?php echo stripslashes_deep($res4['responder_api_key']); ?></textarea>
                                        <?php
                                    } else {
                                        $gvo_arr['type_val'] = isset($gvo_arr['type_val']) ? $gvo_arr['type_val'] : '';
                                        ?>
                                        <textarea class="auto_responder_webform_code_area txtmultimodal1 arfgvotextarea" name="web_form_gvo" id="web_form_gvo" style="height:100px;"<?php echo ( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?> <?php echo (isset($gvo_arr['enable']) && $gvo_arr['enable'] == 1) ? '' : 'readonly=readonly'; ?>><?php echo stripslashes_deep($gvo_arr['type_val']); ?></textarea>
                                        <?php
                                    }
                                    ?>
                                    </div>
                                </div>
                            </div>
                            <div class="arf_optin_tab_inner_container" id="madmimi">
                                <div>
                                <?php 
                                $style = '';
                                $style_gray = '';
                                if(isset($madmimi_arr['enable']) && $madmimi_arr['enable'] == 1)
                                {
                                    $style = 'style="display:block;"';
                                    $style_gray = 'style="display:none;"';                                    
                                } else{
                                    $style = 'style="display:none;"';
                                    $style_gray = 'style="display:block;"';                                    
                                }?>
                                <div class="arf_optin_logo madmimi_original arfmadmimi" <?php echo $style;?>><img src="<?php echo ARFIMAGESURL . '/madmimi.png'; ?>"/></div>
                                <div class="arf_optin_logo madmimi_gray arfmadmimi" <?php echo $style_gray;?>><img src="<?php echo ARFIMAGESURL . '/mad_mimi_gray.png'; ?>"/></div>
                                <div class="arf_optin_checkbox">
                                <label class="arf_js_switch_label">
                                    <span></span>
                                </label>
                                <span class="arf_js_switch_wrapper">
                                    <input type="checkbox" class="js-switch arf_disable_enable_optins" name="autoresponders[]" id="autores_10" value="10" <?php echo (isset($madmimi_arr['enable']) && $madmimi_arr['enable'] == 1) ? 'checked=checked' : ''; ?> data-attr="madmimi"/>
                                    <span class="arf_js_switch"></span>
                                </span>
                                <label class="arf_js_switch_label" for="autores_10">
                                    <span>&nbsp;<?php echo addslashes(esc_html__('Enable', 'ARForms')); ?></span>
                                </label>                                
                                </div>
                                </div>                                
                                <div class="arf_option_configuration_wrapper madmimi_configuration_wrapper <?php echo (isset($madmimi_arr['enable']) && $madmimi_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>">
                                    <br/><br/>
                                    <?php
                                    $rand_num = rand(1111, 9999);
                                    if ($res['madmimi_type'] == 1) {
                                        ?>
                                        <div id="select-autores_<?php echo $rand_num; ?>" class="select_autores" style="margin-left: 25px;">
                                            <?php
                                            if (( $arfaction == 'new' || ( $arfaction == 'duplicate' and $arf_template_id < 100 ) ) || (isset($madmimi_arr['enable']) and $madmimi_arr['enable'] == 0 )) {
                                                ?>
                                                <div id="autores-aweber" class="autoresponder_inner_block" data-if="sadsa" >
                                                    <div class="textarea_space"></div>
                                                    <span class="lblstandard"><?php echo addslashes(esc_html__('Select List Name', 'ARForms')); ?></span>
                                                    <div class="textarea_space"></div>
                                                    <div class="sltstandard">
                                                        <?php
                                                        $selected_list_id = "";
                                                        $selected_list_label = addslashes(esc_html__('Select List','ARForms'));
                                                        $responder_list_option = "";
                                                        $lists = maybe_unserialize($res14['responder_list_id']);
                                                        if ( is_array($lists) && count($lists) > 0 ) {
                                                            $cntr = 0;
                                                            foreach ($lists as $list) {
                                                                if ($res14['responder_list'] == $list['id'] || $cntr == 0) {
                                                                    $selected_list_id = $list['id'];
                                                                    $selected_list_label = $list['name'];
                                                                }

                                                                $responder_list_option .= '<li class="arf_selectbox_option" data-value="' . $list['id'] . '" data-label="' . htmlentities($list['name']) . '">' . $list['name'] . '</li>';
                                                                $cntr++;
                                                            }
                                                        }
                                                        ?>
                                                        <input id="i_madmimi_list" name="i_madmimi_list" value="<?php echo $selected_list_id; ?>" type="hidden" class="frm-dropdown frm-pages-dropdown" <?php echo ( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?>>
                                                        <dl class="arf_selectbox <?php echo (isset($madmimi_arr['enable']) && $madmimi_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>" data-name="i_madmimi_list" data-id="i_madmimi_list" style="width:170px;">
                                                            <dt class="<?php echo (isset($madmimi_arr['enable']) && $madmimi_arr['enable'] == 1) ? '' : 'arf_disabled_container'; ?>"><span><?php echo $selected_list_label; ?></span>
                                                            <input value="<?php print $selected_list_label; ?>" style="display:none;width:118px;" class="arf_autocomplete" type="text" autocomplete="off">
                                                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                                            <dd>
                                                                <ul class="field_dropdown_list_menu" style="display: none;" data-id="i_madmimi_list">
                                                                <li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select Field','ARForms'));?>"><?php echo addslashes(esc_html__('Select Field','ARForms'));?></li>
                                                                    <?php echo $responder_list_option; ?>
                                                                </ul>
                                                            </dd>
                                                        </dl>
                                                    </div>
                                                </div>
                                                <?php
                                            } else {
                                                ?>
                                                <div id="autores-aweber" class="autoresponder_inner_block">
                                                    <div class="textarea_space"></div>
                                                    <span class="lblstandard"><?php echo addslashes(esc_html__('Select List Name', 'ARForms')); ?></span>
                                                    <div class="textarea_space"></div>
                                                    <div class="sltstandard">
                                                        <?php
                                                        $selected_list_id = "";
                                                        $selected_list_label = addslashes(esc_html__('Select List','ARForms'));
                                                        $responder_list_option = "";
                                                        $lists = maybe_unserialize($res14['responder_list_id']);
                                                        $default_madmimi_select_list = isset($res14['responder_list']) ? $res14['responder_list'] : '';
                                                        $selected_list_id_madmimi = (isset($madmimi_arr['type_val']) && $madmimi_arr['type_val'] != '' ) ? $madmimi_arr['type_val'] : $default_madmimi_select_list;
                                                        if (count($lists) > 0 && is_array($lists)) {
                                                            $cntr = 0;
                                                            foreach ($lists as $list) {
                                                                if ($selected_list_id_madmimi == $list['id'] || $cntr == 0) {
                                                                    $selected_list_id = $list['id'];
                                                                    $selected_list_label = $list['name'];
                                                                }

                                                                $responder_list_option .= '<li class="arf_selectbox_option" data-value="' . $list['id'] . '" data-label="' . htmlentities($list['name']) . '">' . $list['name'] . '</li>';
                                                                $cntr++;
                                                            }
                                                        }
                                                        ?>
                                                        <input id="i_madmimi_list" name="i_madmimi_list" value="<?php echo $selected_list_id; ?>" type="hidden" class="frm-dropdown frm-pages-dropdown" <?php echo ( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?>>
                                                        <dl class="arf_selectbox <?php echo (isset($madmimi_arr['enable']) && $madmimi_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>" data-name="i_madmimi_list" data-id="i_madmimi_list" style="width:170px;">
                                                            <dt class="<?php echo (isset($madmimi_arr['enable']) && $madmimi_arr['enable'] == 1) ? '' : 'arf_disabled_container'; ?>"><span><?php echo $selected_list_label; ?></span>
                                                            <input value="<?php echo $selected_list_label; ?>" style="display:none;width:118px;" class="arf_autocomplete" type="text">
                                                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                                            <dd>
                                                                <ul class="field_dropdown_list_menu" style="display: none;" data-id="i_madmimi_list">
                                                                <li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select Field','ARForms'));?>"><?php echo addslashes(esc_html__('Select Field','ARForms'));?></li>
                                                                    <?php echo $responder_list_option; ?>
                                                                </ul>
                                                            </dd>
                                                        </dl>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    <?php }
                                    ?>
                                    <?php do_action('arf_map_madmimi_fields_outside',$values,$record,$responder_list_option,$madmimi_arr); ?>
                                </div>
                            </div>
                            <?php do_action('arf_email_marketers_tab_container_outside', $arfaction, $global_enable_ar, $current_active_ar, $ar_data, $setvaltolic); ?>
                        </div>
                    </div>
                    
                </div>
                <div class="arf_popup_container_footer">
                    <button type="button" class="arf_popup_close_button" data-id="arf_optin_popup_button" ><?php echo esc_html__('OK', 'ARForms'); ?></button>
                </div>
            </div>
        </div>
        <!-- Optins Model -->

        <!-- General Options Model -->
        <div class="arf_modal_overlay">
            <style type="text/css">
                 .arf_cal_header {
                    background-color: #66aaff!important;
                    color: #ffffff;
                    border-bottom: 1px solid #ffffff!important;
                }
                .arf_cal_month {
                    background-color: #66aaff!important;
                    color: #ffffff;
                    border-bottom: 1px solid #66aaff!important;
                }
                .arf_selectbox[data-name="arfredirecttolist"] ul{
                    width:302px !important;
                }
                #arf_other_options_model .bootstrap-datetimepicker-widget table td.active,
                #arf_other_options_model .bootstrap-datetimepicker-widget table td.active:hover {
                    color: #66aaff; 
                    background-image : url("data:image/svg+xml;utf8,<svg width='35px' xmlns='http://www.w3.org/2000/svg' height='29px'><path fill='rgb(0,126,228)' d='M15.732,27.748c0,0-14.495,0.2-14.71-11.834c0,0,0.087-7.377,7.161-11.82 c0,0,0.733-0.993-1.294-0.259c0,0-1.855,0.431-3.538,2.2c0,0-1.078,0.216-0.388-1.381c0,0,2.416-3.019,8.585-2.76 c0,0,2.372-2.458,7.419-1.293c0,0,0.819,0.517-0.518,0.819c0,0-5.361,0.514-3.753,1.122c0,0,14.021,3.073,14.322,13.943 C29.019,16.484,29.573,27.32,15.732,27.748z M26.991,16.182C26.24,7.404,14.389,3.543,14.389,3.543 c-2.693-0.747-4.285,0.683-4.285,0.683C8.767,4.969,6.583,7.804,6.583,7.804C2.216,13.627,3.612,18.47,3.612,18.47 c2.168,7.635,12.505,7.097,12.505,7.097C27.376,25.418,26.991,16.182,26.991,16.182z'/></svg>") !important;
                }
            </style>

            <div id="arf_other_options_model" class="arf_popup_container arf_popup_container_other_option_model">
                <div class="arf_popup_container_header"><?php echo addslashes(esc_html__('General Options', 'ARForms')); ?>
                    <div class="arfpopupclosebutton arfmodalclosebutton" data-dismiss="arfmodal" data-id="arf_general_popup_button">
                        <svg width="30px" height="30px" viewBox="1 0 20 20"><g id="preview"><path fill-rule="evenodd" clip-rule="evenodd" fill="#262944" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                    </div>
                </div>

                <div class="arf_popup_content_container arf_other_options_container">
                    <div class="arf_popup_container_loader">
                        <i class="arfas arfa-spinner arfa-spinner"></i>
                    </div>
                    <div class="arf_popup_checkbox_wrapper">
                        <div class="arf_custom_checkbox_div">
                            <div class="arf_custom_checkbox_wrapper">
                                <input type="checkbox" name="options[arf_form_set_cookie]" id="arf_form_set_cookie" value="1" <?php isset($values['arf_form_set_cookie']) ? checked($values['arf_form_set_cookie'], 1) : ''; ?> />
                                <svg width="18px" height="18px">
                                <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                </svg>
                            </div>
                            <span>
                            <label id="arf_form_set_cookie" for="arf_form_set_cookie"><?php echo addslashes(esc_html__('Auto save form progress', 'ARForms')) ?></label>
                            </span>
                        </div>  

                        <div style="clear: both;margin-left: 40px;font-size: 14px;font-style: italic;"><?php echo esc_html__('(Until form is not submitted, save data typed by user so they can come back to the form later on, and will be able to continue from.)', 'ARForms'); ?></div>
                    </div>


                    <div class="arf_submit_action_tab_wrapper">
                        <?php do_action('arf_additional_onsubmit_settings', $id, $values); ?>

                        <div class="arf_popup_checkbox_wrapper">
                            <div class="arf_custom_checkbox_div">
                                <div class="arf_custom_checkbox_wrapper">
                                    <input type="checkbox" name="options[arf_prevent_view_entry]" id="arf_prevent_view_entry" value="1" <?php isset($values['arf_prevent_view_entry']) ? checked($values['arf_prevent_view_entry']) : ''; ?> />
                                    <svg width="18px" height="18px">
                                    <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                    <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                    </svg>
                                </div>
                                <span>
                                    <label for="arf_prevent_view_entry"><?php echo esc_html__('Prevent storing visitor analytics data','ARForms'); ?></label>
                                </span>
                            </div>
                        </div>

                        <div class="arf_other_option_separator">
                        </div>
                        <span class="arf_hidden_field_title" style="margin-bottom: 10px;"><?php echo addslashes(esc_html__('Restrict Form Entries','ARForms')); ?></span>

                        <div class="arf_popup_checkbox_wrapper" style="width:100%; margin-top: 10px;">
                            <div  class="arf_custom_checkbox_div">
                                <div class="arf_custom_checkbox_wrapper" onclick="arfmaxentryinput();">
                                    <input type="checkbox" name="options[arf_restrict_entry]" id="arf_restrict_entry" value="1"  <?php checked((isset($values['arf_restrict_entry'])?$values['arf_restrict_entry']:''), 1); ?> />
                                    <svg width="18px" height="18px">
                                    <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                    <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                    </svg>
                                </div>
                                <span>
                                    <label id="" for="arf_restrict_entry"><?php echo addslashes(esc_html__('Disable form submission after', 'ARForms')); ?>
                                    </label>
                                </span>
                                <div class="arf_restrict_entry_div">
                                    <input type="text" id="arf_max_entry_textbox" name="options[arf_restrict_max_entries]" value="<?php echo(isset($values['arf_restrict_max_entries'])?$values['arf_restrict_max_entries']:''); ?>" class="arf_large_input_box" style="width: 50px;float: none !important;height: 30px !important;margin-left: unset !important;margin-right:3px;" <?php echo(!isset($values['arf_restrict_entry']) || $values['arf_restrict_entry']!='1')?'readonly':'';?>>
                                    <?php echo addslashes(esc_html__('Entries', 'ARForms')); ?>
                                </div>
                            </div>
                        </div>
                            <div class="arftablerow entry_res_msg" style="display:none;<?php echo (is_rtl()) ? 'margin-right: 45px;' : 'margin-left: 45px;';?>">
                                <div class="arfcolumnleft arfsettingsubtitle"><?php echo esc_html__('Restricted entry message', 'ARForms'); ?></div>
                                <div class="arfcolumnright arf_pre_dup_msg_width">
                                    <textarea rows="4" id="arf_restriction_message_entries" name="options[arf_res_msg_entry]" class="txtmodal1 auto_responder_webform_code_area" style="padding:10px;"><?php echo (isset($values['arf_res_msg_entry']) && $values['arf_res_msg_entry']!='') ?$values['arf_res_msg_entry']: esc_html__('Maximum entry limit is reached.','ARForms'); ?></textarea><br />
                                    <div class="arferrmessage" id="arf_res_entry_msg_error" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank','ARForms')); ?></div>
                                </div>
                            </div>

                        <div class="arf_popup_checkbox_wrapper" style="width:100%;margin-top: 10px;">
                            <div  class="arf_custom_checkbox_div">
                                <div class="arf_custom_checkbox_wrapper" onclick="arfrestrictentries();">
                                    <input type="checkbox" name="options[arf_restrict_form_entries]" id="arf_restrict_form_entries" value="1" <?php checked($values['arf_restrict_form_entries'], 1); ?>/>
                                    <svg width="18px" height="18px">
                                    <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                    <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                    </svg>
                                </div>
                                <span>
                                    <label id="arf_restrict_form_entries_label" for="arf_restrict_form_entries"><?php echo addslashes(esc_html__('Disable form Submission', 'ARForms')); ?> (<?php echo addslashes(esc_html__('Date wise','ARForms')); ?>)</label>
                                </span>
                            </div>
                        </div>
                        <?php

                        if ($values["arf_restrict_form_entries"] == 1) {
                            $arf_restrict_form_entries_class = 'arf_restrict_form_entries_show';
                        } else {
                            $arf_restrict_form_entries_class = 'arf_restrict_form_entries_hide';
                        }

                        if ($values['restrict_action'] == 'before_specific_date') {
                            $display_block_specific_date = 'style="display:block;"';
                        } else {
                            $display_block_specific_date = 'style="display:none;"';
                        }

                        if ($values['restrict_action'] == 'after_specific_date') {
                            $display_block_after_specific_date = 'style="display:block;"';
                        } else {
                            $display_block_after_specific_date = 'style="display:none;"';
                        }

                        if ($values['restrict_action'] == 'date_range') {
                            $display_block_date_range = 'style="display:block;"';
                        } else {
                            $display_block_date_range = 'style="display:none;"';
                        }
                        ?>
                        <div class="arf_restrict_form_entries arfactive <?php echo $arf_restrict_form_entries_class; ?>">
                            <div class="arf_submit_action_options" style="<?php echo (is_rtl()) ? 'margin-right: 45px;' : 'margin-left: 45px;';?>">
                                
                                <div class="arf_radio_wrapper">
                                    <div class="arf_custom_radio_div">
                                        <div class="arf_custom_radio_wrapper">
                                            <input type="radio" class="arf_submit_entries" name="options[restrict_action]" id="success_action_before_specific_date" value="before_specific_date" <?php checked($values['restrict_action'], 'before_specific_date'); ?> />
                                            <svg width="18px" height="18px">
                                            <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                            <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                            </svg>
                                        </div>
                                    </div>
                                    <span>
                                        <label id="success_action_redirect" for="success_action_before_specific_date"><?php echo addslashes(esc_html__('Before specific date', 'ARForms')); ?></label>
                                    </span>
                                </div>

                                <div class="arf_radio_wrapper">
                                    <div class="arf_custom_radio_div">
                                        <div class="arf_custom_radio_wrapper">
                                            <input type="radio" class="arf_submit_entries" name="options[restrict_action]" id="success_action_after_specific_date" value="after_specific_date" <?php checked($values['restrict_action'], 'after_specific_date'); ?> />
                                            <svg width="18px" height="18px">
                                            <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                            <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                            </svg>
                                        </div>
                                    </div>
                                    <span>
                                        <label id="success_action_page" for="success_action_after_specific_date"><?php echo addslashes(esc_html__('After specific date', 'ARForms')); ?></label>
                                    </span>
                                </div>
                                
                                <div class="arf_radio_wrapper">
                                    <div class="arf_custom_radio_div">
                                        <div class="arf_custom_radio_wrapper">
                                            <input type="radio" class="arf_submit_entries" name="options[restrict_action]" id="success_action_date_range" value="date_range" <?php checked($values['restrict_action'], 'date_range'); ?> />
                                            <svg width="18px" height="18px">
                                            <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                            <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                            </svg>
                                        </div>
                                    </div>
                                    <span>
                                        <label id="success_action_page" for="success_action_date_range"><?php echo addslashes(esc_html__('Between two dates', 'ARForms')); ?></label>
                                    </span>


                                </div>
                            </div>
                            <div class="arf_submit_action_options" style="<?php echo (is_rtl()) ? 'margin-right: 90px;' : 'margin-left: 90px;';?>margin-bottom: 20px;margin-top: 20px;">

                                
                                <div class="arf_restriction_entries_type_box" id="arf_type_success_action_before_specific_date" <?php echo $display_block_specific_date; ?>>
                                    <label><?php echo addslashes(esc_html__('Select date', 'ARForms')); ?></label>
                                    <?php $values['arf_restrict_entries_before_specific_date'] = (isset($values['arf_restrict_entries_before_specific_date']) && $values['arf_restrict_entries_before_specific_date'] !='') ? $values['arf_restrict_entries_before_specific_date'] : date('Y-m-d');?>
                                    <input type="text" id="arf_restrict_before_date" name="options[arf_restrict_entries_before_specific_date]" value="<?php echo date($arfdefine_date_formate_array['arfwp_dateformate'],strtotime($values['arf_restrict_entries_before_specific_date'])); ?>" class="arf_large_input_box arf_datetimepicker" style="width:160px;" />
                                    <span class="arferrmessage" id="arf_before_specific_date_error" style="top:0px;"><?php echo addslashes(esc_html__('This field cannot be blank','ARForms')); ?></span>
                                    <span class="arferrmessage" id="arf_before_specific_dateformat_error" style="top:0px;"><?php echo addslashes(esc_html__('Entered date is invalid','ARForms')); ?></span>
                                </div>
                                <div class="arf_restriction_entries_type_box" id="arf_type_success_action_after_specific_date" <?php echo $display_block_after_specific_date; ?>>
                                    <label><?php echo addslashes(esc_html__('Select date', 'ARForms')); ?></label>
                                    <?php $values['arf_restrict_entries_after_specific_date'] = (isset($values['arf_restrict_entries_after_specific_date']) && $values['arf_restrict_entries_after_specific_date'] !='') ? $values['arf_restrict_entries_after_specific_date'] : date('Y-m-d');?>
                                    <input type="text" id="arf_restrict_after_date" name="options[arf_restrict_entries_after_specific_date]" value="<?php echo date($arfdefine_date_formate_array['arfwp_dateformate'],strtotime($values['arf_restrict_entries_after_specific_date'])); ?>" class="arf_large_input_box arf_datetimepicker" style="width:160px;" />
                                    <span class="arferrmessage" id="arf_after_specific_date_error" style="top:0px;"><?php echo addslashes(esc_html__('This field cannot be blank','ARForms')); ?></span>
                                    <span class="arferrmessage" id="arf_after_specific_dateformat_error" style="top:0px;"><?php echo addslashes(esc_html__('Entered date is invalid','ARForms')); ?></span>
                                </div>
                                <div class="arf_restriction_entries_type_box" id="arf_type_success_action_date_range" <?php echo $display_block_date_range; ?>>
                                    <label><?php echo addslashes(esc_html__('Start from', 'ARForms')); ?></label>
                                    <?php $values['arf_restrict_entries_start_date'] = (isset($values['arf_restrict_entries_start_date']) && $values['arf_restrict_entries_start_date'] !='') ? $values['arf_restrict_entries_start_date'] : date('Y-m-d');

                                    $values['arf_restrict_entries_end_date'] = (isset($values['arf_restrict_entries_end_date']) && $values['arf_restrict_entries_end_date'] !='') ? $values['arf_restrict_entries_end_date'] : date('Y-m-d');
                                    ?>
                                    <input type="text" id="arf_restrict_daterange_start_date" name="options[arf_restrict_entries_start_date]" value="<?php echo date($arfdefine_date_formate_array['arfwp_dateformate'],strtotime($values['arf_restrict_entries_start_date'])); ?>" class="arf_large_input_box arf_datetimepicker" style="width:160px;" />
                                    <label style="<?php echo (is_rtl()) ? 'margin-right: 10px;' : 'margin-left: 10px;';?>"><?php echo addslashes(esc_html__('End date', 'ARForms')); ?></label>
                                    <input type="text" id="arf_restrict_daterange_end_date" name="options[arf_restrict_entries_end_date]" value="<?php echo date($arfdefine_date_formate_array['arfwp_dateformate'],strtotime($values['arf_restrict_entries_end_date'])); ?>" class="arf_large_input_box arf_datetimepicker" style="width:160px;" />
                                    <span class="arferrmessage" id="arf_date_range_start_error" style="top:0px;"><?php echo addslashes(esc_html__('This field cannot be blank','ARForms')); ?></span>
                                    <span class="arferrmessage" id="arf_date_range_start_error_dateformat_error" style="top:0px;"><?php echo addslashes(esc_html__('Entered date is invalid','ARForms')); ?></span>

                                    <span class="arferrmessage" id="arf_date_range_end_error" style="top:0px;"><?php echo addslashes(esc_html__('This field cannot be blank','ARForms')); ?></span>
                                    <span class="arferrmessage" id="arf_date_range_end_error_dateformat_error" style="top:0px;"><?php echo addslashes(esc_html__('Entered date is invalid','ARForms')); ?></SPAN>
                                </div>
                            </div>

                            <div class="arftablerow prevent_duplicate_message_box prevent_duplicate_box" style="<?php echo (is_rtl()) ? 'margin-right: 45px;' : 'margin-left: 45px;';?>">
                                <div class="arfcolumnleft arfsettingsubtitle"><?php echo esc_html__('Restricted entry message', 'ARForms'); ?></div>
                                <div class="arfcolumnright arf_pre_dup_msg_width">
                                    <textarea rows="4" id="arf_restriction_message" name="options[arf_res_msg]" class="txtmodal1 auto_responder_webform_code_area" style="padding:10px;"><?php echo(isset($values['arf_res_msg']) && $values['arf_res_msg']!='')?$values['arf_res_msg']: addslashes(esc_html__('Form Entry Restricted','ARForms')); ?></textarea><br />
                                    <div class="arferrmessage" id="arf_res_msg_error" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank','ARForms')); ?></div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <?php do_action('arf_add_form_other_option_outside',$values);?>

                </div>
                <div class="arf_popup_container_footer">
                    <button type="button" class="arf_popup_close_button" data-id="arf_general_popup_button" ><?php echo esc_html__('OK', 'ARForms'); ?></button>
                </div>
            </div>
        </div>        
        <!-- General Options Model -->

        <!-- Hidden Fields Options Model -->
        <div class="arf_modal_overlay">
            <style type="text/css">
                 .arf_cal_header {
                    background-color: #66aaff!important;
                    color: #ffffff;
                    border-bottom: 1px solid #ffffff!important;
                }
                .arf_cal_month {
                    background-color: #66aaff!important;
                    color: #ffffff;
                    border-bottom: 1px solid #66aaff!important;
                }
                .arf_selectbox[data-name="arfredirecttolist"] ul{
                    width:302px !important;
                }
                #arf_hidden_fields_options_model .bootstrap-datetimepicker-widget table td.active,
                #arf_hidden_fields_options_model .bootstrap-datetimepicker-widget table td.active:hover {
                    color: #66aaff; 
                    background-image : url("data:image/svg+xml;utf8,<svg width='35px' xmlns='http://www.w3.org/2000/svg' height='29px'><path fill='rgb(0,126,228)' d='M15.732,27.748c0,0-14.495,0.2-14.71-11.834c0,0,0.087-7.377,7.161-11.82 c0,0,0.733-0.993-1.294-0.259c0,0-1.855,0.431-3.538,2.2c0,0-1.078,0.216-0.388-1.381c0,0,2.416-3.019,8.585-2.76 c0,0,2.372-2.458,7.419-1.293c0,0,0.819,0.517-0.518,0.819c0,0-5.361,0.514-3.753,1.122c0,0,14.021,3.073,14.322,13.943 C29.019,16.484,29.573,27.32,15.732,27.748z M26.991,16.182C26.24,7.404,14.389,3.543,14.389,3.543 c-2.693-0.747-4.285,0.683-4.285,0.683C8.767,4.969,6.583,7.804,6.583,7.804C2.216,13.627,3.612,18.47,3.612,18.47 c2.168,7.635,12.505,7.097,12.505,7.097C27.376,25.418,26.991,16.182,26.991,16.182z'/></svg>") !important;
                }
            </style>

            <div id="arf_hidden_fields_options_model" class="arf_popup_container arf_popup_container_other_option_model">
                <div class="arf_popup_container_header"><?php echo esc_html__('Hidden Input Fields Options', 'ARForms'); ?>
                    <div class="arfpopupclosebutton arfmodalclosebutton" data-dismiss="arfmodal" data-id="arf_optin_popup_button">
                        <svg width="30px" height="30px" viewBox="1 0 20 20"><g id="preview"><path fill-rule="evenodd" clip-rule="evenodd" fill="#262944" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                    </div>
                </div>

                <div class="arf_popup_content_container arf_other_options_container">

                    <div class="arf_hidden_fields_wrapper">
                        <span class="arf_hidden_field_title"><?php echo esc_html__('Hidden Input Fields Setup','ARForms'); ?></span>
                        <div class="arf_hidden_field_note">
                            <div><?php echo addslashes(esc_html__('Note','ARForms')).': '.esc_html__('These fields will not shown in the form. Enter the value to be hidden','ARForms'); ?></div>

                            <div>[ARF_current_user_id] : <?php echo addslashes(esc_html__('This shortcode replace the value with currently logged-in User ID.', 'ARForms')); ?></div>
                            <div>[ARF_current_user_name] : <?php echo addslashes(esc_html__('This shortcode replace the value with currently logged-in User Name.', 'ARForms')); ?></div>
                            <div>[ARF_current_user_email] : <?php echo addslashes(esc_html__('This shortcode replace the value with currently logged-in User Email.', 'ARForms')); ?></div>
                            <div>[ARF_current_date] : <?php echo addslashes(esc_html__('This shortcode replace the value with current Date.', 'ARForms')); ?></div>
                            
                        </div>
                        <button type="button" id="arf_add_new_hidden_field" class="rounded_button arf_btn_dark_blue add_new_hidden_field_button" style="<?php echo (count($all_hidden_fields) > 0 ) ? 'display:none;' : ''; ?>"><?php echo addslashes(esc_html__('Add new hidden field','ARForms')); ?></button>
                        <div class="arf_hidden_field_input_wrapper_header <?php echo (count($all_hidden_fields) > 0 ) ? 'arfactive' : ''; ?>">
                            <span class="arf_hidden_field_input_wrapper_header_label"><?php echo addslashes(esc_html__('Label','ARForms')); ?></span>
                            <span class="arf_hidden_field_input_wrapper_header_value"><?php echo addslashes(esc_html__('Value','ARForms')); ?></span>
                            <span class="arf_hidden_field_input_wrapper_header_action"><?php echo addslashes(esc_html__('Action','ARForms')); ?></span>
                        </div>
                        <div class="arf_hidden_fields_input_wrapper">
                        <?php
                            if( count($all_hidden_fields) > 0 ){
                                $counter = 1;
                                $hidden_fields_content = "";
                                foreach($all_hidden_fields as $hkey => $hd_field){
                                    $field_opts = json_decode($hd_field->field_options);
                                    if( json_last_error() != JSON_ERROR_NONE ){
                                        $field_opts = maybe_unserialize($hd_field->field_options);
                                    }
                                    $hidden_fields_content .= "<div class='arf_hidden_field_input_container' id='arf_hidden_field_input_container_{$counter}'>";
                                    $hidden_fields_content .= "<label class='arf_hidden_field_input_label' for='arf_hidden_field_input_{$counter}'>";
                                    $hidden_fields_content .= "<input type='text' class='arf_large_input_box arf_hidden_field_label_input' value='{$hd_field->name}' data-field-id='{$hd_field->id}' id='arf_hidden_field_input_label_{$counter}' />";
                                    $hidden_fields_content .= "</label>";
                                    $hidden_fields_content .= "<input type='text' name='item_meta[{$hd_field->id}]' class='arf_large_input_box' id='arf_hidden_field_input_{$counter}' value='{$field_opts->default_value}' />";
                                    $hidden_fields_content .= "<input type='hidden' name='arf_field_data_{$hd_field->id}' id='arf_field_data_{$hd_field->id}' value='{$hd_field->field_options}' data-field-option='[]' />";
                                    $hidden_fields_content .= "<div class='arf_hidden_field_input_action_button'>";
                                    $hidden_fields_content .= '<span class="arf_hidden_field_add"><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.134,20.362c-5.521,0-9.996-4.476-9.996-9.996 c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.13,15.887,16.654,20.362,11.134,20.362z M11.133,2.314c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052C19.185,5.919,15.579,2.314,11.133,2.314z M12.146,14.341h-2v-3h-3v-2h3V6.372h2v2.969h3v2h-3V14.341z"/></g></svg></span>';
                                    $hidden_fields_content .= '<span class="arf_hidden_field_remove" data-id="'.$counter.'"><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.12,20.389c-5.521,0-9.996-4.476-9.996-9.996c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.116,15.913,16.64,20.389,11.12,20.389zM11.119,2.341c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052C19.17,5.945,15.565,2.341,11.119,2.341z M12.131,11.367h3v-2h-3h-2h-3v2h3H12.131z"/></g></svg></span>';
                                    $hidden_fields_content .= "</div>";
                                    $hidden_fields_content .= "</div>";
                                    $counter++;
                                }
                                echo $hidden_fields_content;
                            }
                        ?>
                        </div>
                    </div>
                </div>
                <div class="arf_popup_container_footer">
                    <button type="button" class="arf_popup_close_button" data-id="arf_optin_popup_button" ><?php echo esc_html__('OK', 'ARForms'); ?></button>
                </div>
            </div>
        </div>
        <!-- Hidden Fields Options Model -->

        <!-- Tracking Code Options Model -->
        <div class="arf_modal_overlay">
            <style type="text/css">
                 .arf_cal_header {
                    background-color: #66aaff!important;
                    color: #ffffff;
                    border-bottom: 1px solid #ffffff!important;
                }
                .arf_cal_month {
                    background-color: #66aaff!important;
                    color: #ffffff;
                    border-bottom: 1px solid #66aaff!important;
                }
                .arf_selectbox[data-name="arfredirecttolist"] ul{
                    width:302px !important;
                }
                #arf_tracking_code_options_model .bootstrap-datetimepicker-widget table td.active,
                #arf_tracking_code_options_model .bootstrap-datetimepicker-widget table td.active:hover {
                    color: #66aaff; 
                    background-image : url("data:image/svg+xml;utf8,<svg width='35px' xmlns='http://www.w3.org/2000/svg' height='29px'><path fill='rgb(0,126,228)' d='M15.732,27.748c0,0-14.495,0.2-14.71-11.834c0,0,0.087-7.377,7.161-11.82 c0,0,0.733-0.993-1.294-0.259c0,0-1.855,0.431-3.538,2.2c0,0-1.078,0.216-0.388-1.381c0,0,2.416-3.019,8.585-2.76 c0,0,2.372-2.458,7.419-1.293c0,0,0.819,0.517-0.518,0.819c0,0-5.361,0.514-3.753,1.122c0,0,14.021,3.073,14.322,13.943 C29.019,16.484,29.573,27.32,15.732,27.748z M26.991,16.182C26.24,7.404,14.389,3.543,14.389,3.543 c-2.693-0.747-4.285,0.683-4.285,0.683C8.767,4.969,6.583,7.804,6.583,7.804C2.216,13.627,3.612,18.47,3.612,18.47 c2.168,7.635,12.505,7.097,12.505,7.097C27.376,25.418,26.991,16.182,26.991,16.182z'/></svg>") !important;
                }
            </style>

            <div id="arf_tracking_code_options_model" class="arf_popup_container arf_popup_container_other_option_model ">
                <div class="arf_popup_container_header"><?php echo addslashes(esc_html__('Submit Tracking Script', 'ARForms')); ?>
                    <div class="arfpopupclosebutton arfmodalclosebutton" data-dismiss="arfmodal" data-id="arf_optin_popup_button">
                        <svg width="30px" height="30px" viewBox="1 0 20 20"><g id="preview"><path fill-rule="evenodd" clip-rule="evenodd" fill="#262944" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                    </div>
                </div>

                <div class="arf_popup_content_container arf_other_options_container">

                    <div class="arf_submit_action_tab_wrapper">

                        <div class="arf_after_submission_tracking_code">
                             <span class="arf_hidden_field_title" style="margin-top: 10px;margin-bottom: 10px;"><?php echo addslashes(esc_html__('After Submission Tracking Script', 'ARForms')); ?></span>
                            <div class="arftablerow prevent_duplicate_message_box prevent_duplicate_box" style="<?php echo (is_rtl()) ? 'margin-right: 45px;' : 'margin-left: 45px;';?>">
                                <div class="arfcolumnleft arfsettingsubtitle"><?php echo addslashes(esc_html__('Enter After submission tracking script', 'ARForms')); ?>&nbsp;(<?php echo addslashes(esc_html__('Example: Google Tracking Code', 'ARForms')); ?>)</div>
                                <div class="arfcolumnright arf_pre_dup_msg_width">
                                    <div class="" style="float:left;width: 100%;background: #f5f5f5;">&lt;script type="text/javascript"&gt;</div>
                                    <textarea rows="10" id="arf_submission_tracking_code" name="options[arf_sub_track_code]" class="txtmodal1 auto_responder_webform_code_area" style="padding:10px;margin:0;"><?php echo(isset($values['arf_sub_track_code']) && $values['arf_sub_track_code']!='')?rawurldecode(stripslashes_deep($values['arf_sub_track_code'])): ''; ?></textarea><br />
                                    <div class="arferrmessage" id="arf_submission_tracking_code" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank','ARForms')); ?></div>
                                    <div class="" style="float:left;width: 100%;background: #f5f5f5;">&lt;/script&gt;</div>
                                </div>
                                <div style="clear: both;margin-left: 0px;font-size: 14px;font-style: italic;"><?php echo esc_html__('(Do not insert script tag','ARForms').'(&lt;script&gt;)'.esc_html__(' inside code.)', 'ARForms'); ?></div>
                            </div>
                        </div>

                    </div>

                </div>
                <div class="arf_popup_container_footer">
                    <button type="button" class="arf_popup_close_button" data-id="arf_optin_popup_button" ><?php echo esc_html__('OK', 'ARForms'); ?></button>
                </div>
            </div>
        </div>
        <!-- Tracking Code Options Model -->

        <?php do_action('arf_add_modal_in_editor',$values); ?>

    </form>
</div>

<!-- Font Awesome Model -->
<div class="arf_modal_overlay">
    <div id="arf_fontawesome_model" class="arf_popup_container arf_popup_container_fontawesome_model">
        <div class="arf_popup_container_header"><?php echo addslashes(esc_html__('Font Awesome', 'ARForms')); ?></div>
        <div class="arf_popup_content_container">
            <?php $is_rtl = ''; ?>
            <?php require( VIEWS_PATH . '/arf_font_awesome.php' ); ?>
        </div>
        <div class="arf_popup_container_footer" style="height:auto !important;">
            <input type="hidden" id="icon_field_id">
            <input type="hidden" id="icon_field_type">
            <input type="hidden" id="icon_no_icon">
            <input type="hidden" id="icon_icon">
            <button type="button" class="arf_popup_close_button" style="background-color: #DFECF2;color:black;margin:0px 10px;"><?php echo addslashes(esc_html__('Cancel', 'ARForms')); ?></button>&nbsp;&nbsp;
            <button type="button" class="arf_popup_close_button arf_fainsideimge_ok_button" id="" ><?php echo esc_html__('OK', 'ARForms'); ?></button>  
        </div>
    </div>
</div>
<!-- Font Awesome Model -->

<!-- Add new form Popup -->
<div class="arf_modal_overlay">
    <input type="hidden" id="open_new_form_div" value="<?php echo isset($_REQUEST['isp']) ? $_REQUEST['isp'] : 0; ?>" />
    <div id="new_form_model" class="arf_popup_container arf_popup_container_new_form">
        <?php require(VIEWS_PATH . '/new-selection-modal.php'); ?>
    </div>
</div>
<!-- Add new form Popup -->

<!--delete modal popup-->
<div>    
</div>

<!--delete modal popup-->

<!--field option popup-->



<!-- preview model -->
<div class="arf_modal_overlay arf_whole_screen">
    <div id="form_previewmodal" class="arf_popup_container" style="overflow:hidden;">
        <div class="arf_preview_model_header">
            <div class="arf_preview_model_header_icons">
                <div onclick="arfchangedevice('computer');" title="<?php echo addslashes(esc_html__('Computer View', 'ARForms')); ?>" class="arfdevicesbg arfhelptip arf_preview_model_device_icon"><div id="arfcomputer" class="arfdevices arfactive"><svg width="75px" height="60px" viewBox="-16 -14 75 60"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#ffffff" d="M40.561,28.591H24.996v2.996h8.107c0.779,0,1.434,0.28,1.434,1.059  c0,0.779-0.655,0.935-1.434,0.935H9.951c-0.779,0-1.435-0.156-1.435-0.935c0-0.778,0.656-1.059,1.435-1.059h8.045v-2.996H2.452  c-0.779,0-1.435-0.656-1.435-1.435V2.086c0-0.779,0.656-1.434,1.435-1.434h38.109c0.778,0,1.434,0.655,1.434,1.434v25.071  C41.995,27.936,41.339,28.591,40.561,28.591z M22.996,31.587v-2.996h-3v2.996H22.996z M39.995,2.642H3.017v23.895h36.978V2.642z"/></svg></div></div>
                <div onclick="arfchangedevice('tablet');" title="<?php echo addslashes(esc_html__('Tablet View', 'ARForms')); ?>" class="arfdevicesbg arfhelptip arf_preview_model_device_icon"><div id="arftablet" class="arfdevices"><svg width="40px" height="60px" viewBox="-6 -15 40 60"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#ffffff" d="M23.091,33.642H4.088c-1.657,0-3-1.021-3-2.28V2.816  c0-1.259,1.343-2.28,3-2.28h19.003c1.657,0,3,1.021,3,2.28v28.546C26.091,32.622,24.749,33.642,23.091,33.642z M4.955,31.685h17.262  c1.035,0,1.875-0.638,1.875-1.425v-4.694H3.08v4.694C3.08,31.047,3.92,31.685,4.955,31.685z M24.092,4.002  c0-0.787-0.84-1.425-1.875-1.425H4.955c-1.035,0-1.875,0.638-1.875,1.425v1.563h21.012V4.002z M3.08,7.566v16h21.012v-16H3.08z   M13.618,26.551c1.09,0,1.974,0.896,1.974,2s-0.884,2-1.974,2c-1.09,0-1.974-0.896-1.974-2S12.527,26.551,13.618,26.551zz"/></svg></div></div>
                <div onclick="arfchangedevice('mobile');" title="<?php echo addslashes(esc_html__('Mobile View', 'ARForms')); ?>" class="arfdevicesbg arfhelptip arf_preview_model_device_icon"><div id="arfmobile" class="arfdevices"><svg width="45px" height="60px" viewBox="-12 -15 45 60"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#ffffff" d="M17.894,33.726H3.452c-1.259,0-2.28-1.021-2.28-2.28V2.899  c0-1.259,1.021-2.28,2.28-2.28h14.442c1.259,0,2.28,1.021,2.28,2.28v28.546C20.174,32.705,19.153,33.726,17.894,33.726z   M18.18,4.086c0-0.787-0.638-1.425-1.425-1.425H4.585c-0.787,0-1.425,0.638-1.425,1.425v26.258c0,0.787,0.638,1.425,1.425,1.425  h12.169c0.787,0,1.425-0.638,1.425-1.425V4.086z M13.787,6.656H7.568c-0.252,0-0.456-0.43-0.456-0.959s0.204-0.959,0.456-0.959  h6.218c0.251,0,0.456,0.429,0.456,0.959S14.038,6.656,13.787,6.656z M10.693,25.635c1.104,0,2,0.896,2,2c0,1.105-0.895,2-2,2  c-1.105,0-2-0.895-2-2C8.693,26.53,9.588,25.635,10.693,25.635z"/></svg></div></div>
            </div>
            <div class="arf_popup_header_close_button" data-dismiss="arfmodal"><svg width="16px" height="16px" viewBox="0 0 12 12"><path fill-rule="evenodd" clip-rule="evenodd" fill="#ffffff" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></svg></div>
        </div>
        <div class="arfmodal-body" style=" overflow:hidden; clear:both;padding:0;">
            <div class="iframe_loader arf_editor_preview_loader" align="center"><?php echo ARF_LOADER_ICON; ?></div>
            <iframe id="arfdevicepreview" name="arf_preview_frame" src="" frameborder="0" height="100%" width="100%"></iframe>
        </div>
    </div>
</div>
<!-- preview model -->

<!-- CSS Code Expand Model -->
<div class="arf_modal_overlay">
    <div id="arf_other_css_expanded_model"  class="arf_popup_container arf_popup_container_other_css_expanded_model" style="overflow:hidden;">
        <div class="arf_other_css_expanded_model_header">
            <span><?php echo addslashes(esc_html__('Custom CSS','ARForms')); ?></span>
            <div class="arf_other_css_expanded_add_element_btn" id="arf_expand_css_code_element_button">
                <span><?php echo addslashes(esc_html__('Add CSS Elements','ARForms')); ?></span>
                <i class="arfa arfa-caret-down"></i>
                <ul class="arf_custom_css_cloud_list_wrapper">
                <?php
                global $custom_css_array;
                    foreach($custom_css_array as $key => $value ){
                        ?>
                        <li data-target="expanded" class="arf_custom_css_cloud_list_item <?php echo (isset($values[$key]) && $values[$key] != '') ? 'arfactive' : ''; ?>" id="<?php echo $value['onclick_1']; ?>"><span><?php echo $value['label_title']; ?></span></li>
                        <?php
                    }
                ?>
                </ul>
            </div>
        </div>
        <div class="arf_other_css_expanded_model_container">
        <textarea id="arf_other_css_expanded_textarea"></textarea>
        </div>
        <div class="arf_popup_container_footer">
            <button type="button" class="arf_popup_close_button" id="arf_css_expanded_model_btn">OK</button>
        </div>
    </div>
</div>
<!-- CSS Code Expand Model -->

<!-- Field Option Model -->
<?php require_once VIEWS_PATH . '/arf_field_option_popup.php'; ?>
<!-- Field Option Model -->

<!-- Field Value Model -->
<?php require_once VIEWS_PATH . '/arf_field_values_popup.php'; ?>
<!-- Field Value Model -->

<!-- new field array -->
<?php require(VIEWS_PATH . '/new_field_array.php'); ?>
<!-- new field array -->

<script type="text/javascript" data-cfasync="false">

    function close_image(image_name) {
        if (image_name == 'button_hover_image')
        {
            if (jQuery('#submit_hover_btn_img_div').find('.arf_delete_image').length > 0)
            {
                jQuery('#submit_hover_btn_img_div').find('.arf_delete_image').remove();
            }
        }
        else if (image_name == 'button_image') {
            if (jQuery('#submit_btn_img_div').find('.arf_delete_image').length > 0)
            {
                jQuery('#submit_btn_img_div').find('.arf_delete_image').remove();
            }
        }
        else if (image_name == 'form_image') {
            if (jQuery('#form_bg_img_div').find('.arf_delete_image').length > 0)
            {
                jQuery('#form_bg_img_div').find('.arf_delete_image').remove();
            }
        }
    }
    
    function delete_image(image_name) {
        var html = '';
        var msg = '';
        if (image_name == 'form_image')
        {
            msg = 'Are you sure you want to<br>delete this image?';
            event = 'remove_image("delete_form_bg_img")';
        }
        else if (image_name == 'button_hover_image') {
            msg = 'Are you sure you want to<br>delete this image?';
            event = 'remove_image("delete_submit_hover_bg_img")';
        }
        else if (image_name == 'button_image') {
            msg = 'Are you sure you want to<br>delete this image?';
            event = 'remove_image("delete_submit_bg_img")';
        }
        html += '<div class="delete_popup arfactive arf_delete_image" id="arf_delete_image">';
        html += '<div class="delete_column_arrow" style="position:absolute;">';
        html += '</div>';
        html += '<div class="delete_title"><div class="delete_confirm_message">' + msg + '</div>';
        html += '<div class="delete_popup_footer"><button type="button" class="rounded_button add_button arf_delete_modal_left arfdelete_color_red" onclick=' + event + '>Delete</button>';
        html += '<button type="button" class="rounded_button delete_button arfdelete_color_gray" onclick="close_image(\'' + image_name + '\')" style="margin-left:10px;">Cancel</button>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        if (image_name == 'button_hover_image') {
            var select_content = '#submit_hover_btn_img_div span';
            if (jQuery('#submit_hover_btn_img_div').find('.arf_delete_image').length > 0)
            {
                jQuery('#submit_hover_btn_img_div').find('.arf_delete_image').remove();
            }
            jQuery(html).insertAfter(select_content);
            jQuery('.arf_delete_image').show();
        }
        else if (image_name == 'button_image') {
            var select_content = '#submit_btn_img_div span';
            if (jQuery('#submit_btn_img_div').find('.arf_delete_image').length > 0)
            {
                jQuery('#submit_btn_img_div').find('.arf_delete_image').remove();
            }
            jQuery(html).insertAfter(select_content);
            jQuery('.arf_delete_image').show();
        }
        else if (image_name == 'form_image') {
            var select_content = '#form_bg_img_div span';
            if (jQuery('#form_bg_img_div').find('.arf_delete_image').length > 0)
            {
                jQuery('#form_bg_img_div').find('.arf_delete_image').remove();
            }
            jQuery(html).insertAfter(select_content);
            jQuery('.arf_delete_image').show();
        }

    }
    
    function remove_image(image_name){

        <?php if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '9') { ?>
                jQuery.ajax({
                type: "POST",
                url: ajaxurl,
                xhrFields: {
                    withCredentials: true
                },
                data: "action="+image_name+"_IE89",
                success: function (msg) {
                    if(image_name == 'delete_form_bg_img')
                    {
                        var arf_bg_position_x = jQuery('#arf_bg_position_x').val();
                        var arf_bg_position_y = jQuery("#arf_bg_position_y").val();
                        var arf_bg_position_input_x = jQuery("#arf_form_bg_position_input_x").val();
                        var arf_bg_position_input_y = jQuery("#arf_form_bg_position_input_y").val();
                        var position_style = '';
                        
                        if(arf_bg_position_y!='' && arf_bg_position_y!='px'){
                            position_style += 'background-position-y: '+arf_bg_position_y+";";
                        } else if(arf_bg_position_y!='' && arf_bg_position_y=='px'){
                            position_style += 'background-position-y: '+arf_bg_position_input_y+"px;";
                        }

                        if(arf_bg_position_x!='' && arf_bg_position_x!='px'){
                            position_style += 'background-position-x: '+arf_bg_position_x+";";    
                        } else if(arf_bg_position_x!='' && arf_bg_position_x=='px'){
                            position_style += 'background-position-x: '+arf_bg_position_input_x+"px;";    
                        }

                        var form_id = jQuery('#id').val();
                        $style = jQuery(".ar_main_div_" + form_id + " .arf_fieldset").attr('style');
                        if (typeof $style == 'undefined') {
                            $style = "background-image:none !important;"+position_style+"background-repeat:no-repeat !important;";
                        } else {
                            $style = $style + "background-image:none !important;"+position_style+"background-repeat:no-repeat !important;";
                        }
                        jQuery(".ar_main_div_" + form_id + " .arf_fieldset").attr('style', $style);
                        jQuery('#form_bg_img_div').html(msg);
                        
                        jQuery('#form_bg_img_div').addClass("iframe_original_btn");
                        jQuery('#form_bg_img_div').css("background", "#1BBAE1");
                        jQuery('#form_bg_img_div').css("padding", "7px 10px 0 10px");
                        jQuery('#form_bg_img_div').css('border', '1px solid #CCCCCC');
                        jQuery('#form_bg_img_div').css('border-radius', '3px');
                        jQuery('#arfmfbi_iframe').contents().find('#iframe_form').trigger("reset");
                        jQuery('#form_bg_img_div').append('<div id="arfmfbi_iframe_div"><iframe style="display:none;" id="arfmfbi_iframe" src="<?php echo ARFURL; ?>/core/views/iframe.php"></iframe></div>');
                    }
                    else if(image_name == 'delete_submit_hover_bg_img'){
                        jQuery('#submit_hover_btn_img_div').html(msg);
                        jQuery('#submit_hover_btn_img_div').addClass("iframe_submit_hover_original_btn");
                        jQuery('#submit_hover_btn_img_div').css("background", "#1BBAE1");
                        jQuery('#submit_hover_btn_img_div').css("padding", "7px 10px 0 10px");
                        jQuery('#submit_hover_btn_img_div').css('border', '1px solid #CCCCCC');
                        jQuery('#submit_hover_btn_img_div').css('border-radius', '3px');
                        jQuery('#arfsbhis_iframe').contents().find('#iframe_form').trigger("reset");
                        jQuery('#submit_hover_btn_img_div').append('<div id="arfsbhis_iframe_div"><iframe style="display:none;" id="arfsbhis_iframe" src="<?php echo ARFURL; ?>/core/views/iframe.php"></iframe></div>');
                    }
                    else if(image_name == 'delete_submit_bg_img'){
                        var arf_bg_position_x = jQuery('#arf_bg_position_x').val();
                        var arf_bg_position_y = jQuery("#arf_bg_position_y").val();
                        var arf_bg_position_input_x = jQuery("#arf_form_bg_position_input_x").val();
                        var arf_bg_position_input_y = jQuery("#arf_form_bg_position_input_y").val();
                        var position_style = '';
                        
                        if(arf_bg_position_y!='' && arf_bg_position_y!='px'){
                            position_style += 'background-position-y: '+arf_bg_position_y+";";
                        } else if(arf_bg_position_y!='' && arf_bg_position_y=='px'){
                            position_style += 'background-position-y: '+arf_bg_position_input_y+"px;";
                        }

                        if(arf_bg_position_x!='' && arf_bg_position_x!='px'){
                            position_style += 'background-position-x: '+arf_bg_position_x+";";    
                        } else if(arf_bg_position_x!='' && arf_bg_position_x=='px'){
                            position_style += 'background-position-x: '+arf_bg_position_input_x+"px;";    
                        }

                        var form_id = jQuery('#id').val();
                        $style = jQuery(".ar_main_div_" + form_id + " .arfsubmitbutton .arf_submit_btn").attr('style');
                        if (typeof $style == 'undefined') {
                            $style = "background-image:none !important;"+position_style+"important;background-repeat:no-repeat !important;";
                        } else {
                            $style = $style + "background-image:none !important;"+position_style+"background-repeat:no-repeat !important;";
                        }
                        jQuery(".ar_main_div_" + form_id + " .arfsubmitbutton .arf_submit_btn").attr('style', $style);
                        jQuery(".ar_main_div_" + form_id + " .arfsubmitbutton .arf_submit_btn .arf_edit_in_place_input").css('display', 'block');
                        jQuery('#submit_btn_img_div').html(msg);                       
                        jQuery('#submit_btn_img_div').addClass("iframe_submit_original_btn");
                        jQuery('#submit_btn_img_div').css("background", "#1BBAE1");
                        jQuery('#submit_btn_img_div').css("padding", "7px 10px 0 10px");
                        jQuery('#submit_btn_img_div').css('border', '1px solid #CCCCCC');
                        jQuery('#submit_btn_img_div').css('border-radius', '3px');
                        jQuery('#arfsbis_iframe').contents().find('#iframe_form').trigger("reset");
                        jQuery('#submit_btn_img_div').append('<div id="arfsbis_iframe_div"><iframe style="display:none;" id="arfsbis_iframe" src="<?php echo ARFURL; ?>/core/views/iframe.php"></iframe></div>');
                     }
                }
            });
        <?php } else { ?>             
            var form_id = jQuery('#id').val();
            if( image_name == 'delete_submit_bg_img'){
                var arf_bg_position_x = jQuery('#arf_bg_position_x').val();
                var arf_bg_position_y = jQuery("#arf_bg_position_y").val();
                var arf_bg_position_input_x = jQuery("#arf_form_bg_position_input_x").val();
                var arf_bg_position_input_y = jQuery("#arf_form_bg_position_input_y").val();
                var position_style = '';
                
                if(arf_bg_position_y!='' && arf_bg_position_y!='px'){
                    position_style += 'background-position-y: '+arf_bg_position_y+";";
                } else if(arf_bg_position_y!='' && arf_bg_position_y=='px'){
                    position_style += 'background-position-y: '+arf_bg_position_input_y+"px;";
                }

                if(arf_bg_position_x!='' && arf_bg_position_x!='px'){
                    position_style += 'background-position-x: '+arf_bg_position_x+";";    
                } else if(arf_bg_position_x!='' && arf_bg_position_x=='px'){
                    position_style += 'background-position-x: '+arf_bg_position_input_x+"px;";    
                }

                jQuery("#ar_main_div_"+form_id+"_submit_button").remove();
                var define_style = '<style id="ar_main_div_"+form_id+"_submit_button">';
                define_style +='.ar_main_div_'+form_id+' .arf_fieldset .arf_submit_btn{background-image:none !important;'+position_style+'background-repeat:no-repeat !important;}';                        
                define_style +='</style>';
                jQuery('body').append(define_style);
                if(jQuery("#ar_main_div_"+form_id+"_submit_hover_button").length > 0)
                {
                    jQuery("#ar_main_div_"+form_id+"_submit_hover_button").remove();
                    var define_hover_style = '<style id="ar_main_div_"+form_id+"_submit_hover_button">';
                    define_hover_style +='.ar_main_div_'+form_id+' .arf_fieldset .arf_submit_btn:hover{background-image:none !important;'+position_style+'background-repeat:no-repeat !important;}';                        
                    define_hover_style +='</style>';
                    jQuery('body').append(define_hover_style);                    
                }                
                $style = jQuery(".ar_main_div_" + form_id + " .arfsubmitbutton .arf_submit_btn").attr('style');
                jQuery(".ar_main_div_" + form_id + " .arfsubmitbutton .arf_submit_btn .arf_edit_in_place_input").css('display', 'block');
                var msg = "<div class='arfajaxfileupload'> ";
                msg += "<div class='arf_form_style_file_upload_icon'>";
                msg += "<svg width='16' height='18' viewBox='0 0 18 20' fill='#ffffff'><path xmlns='http://www.w3.org/2000/svg' d='M15.906,18.599h-1h-12h-1h-1v-7h2v5h12v-5h2v7H15.906z M13.157,7.279L9.906,4.028v8.571c0,0.552-0.448,1-1,1c-0.553,0-1-0.448-1-1v-8.54l-3.22,3.22c-0.403,0.403-1.058,0.403-1.46,0 c-0.403-0.403-0.403-1.057,0-1.46l4.932-4.932c0.211-0.211,0.488-0.306,0.764-0.296c0.275-0.01,0.553,0.085,0.764,0.296 l4.932,4.932c0.403,0.403,0.403,1.057,0,1.46S13.561,7.682,13.157,7.279z'/></svg>";
                msg += "</div>";
                msg += "<input type='file' name='submit_btn_img' id='submit_btn_img' class='original' style='position: absolute; cursor: pointer; top: 0px; padding:0; margin:0; height:100%; width:100%; right:0; z-index: 100; opacity: 0; filter:alpha(opacity=0);' />";
                msg += "</div>";
                msg += "<input type='hidden' name='imagename' id='imagename' value='' />";
                jQuery('#submit_btn_img_div').html(msg);
            } else if( image_name == 'delete_form_bg_img'){
                var arf_bg_position_x = jQuery('#arf_bg_position_x').val();
                var arf_bg_position_y = jQuery("#arf_bg_position_y").val();
                var arf_bg_position_input_x = jQuery("#arf_form_bg_position_input_x").val();
                var arf_bg_position_input_y = jQuery("#arf_form_bg_position_input_y").val();
                var position_style = '';
                
                if(arf_bg_position_y!='' && arf_bg_position_y!='px'){
                    position_style += 'background-position-y: '+arf_bg_position_y+";";
                } else if(arf_bg_position_y!='' && arf_bg_position_y=='px'){
                    position_style += 'background-position-y: '+arf_bg_position_input_y+"px;";
                }

                if(arf_bg_position_x!='' && arf_bg_position_x!='px'){
                    position_style += 'background-position-x: '+arf_bg_position_x+";";    
                } else if(arf_bg_position_x!='' && arf_bg_position_x=='px'){
                    position_style += 'background-position-x: '+arf_bg_position_input_x+"px;";    
                }

                $style = jQuery(".ar_main_div_" + form_id + " .arf_fieldset").attr('style');
                if (typeof $style == 'undefined') {
                    $style = "background-image:none !important;"+position_style+"background-repeat:no-repeat !important;";
                } else {
                    $style = $style + "background-image:none !important;"+position_style+"background-repeat:no-repeat !important;";
                }
                jQuery(".ar_main_div_" + form_id + " .arf_fieldset").attr('style', $style);
                var msg = "<div class='arfajaxfileupload'>";
                msg += "<div class='arf_form_style_file_upload_icon'>";
                msg += "<svg width='16' height='18' viewBox='0 0 18 20' fill='#ffffff'><path xmlns='http://www.w3.org/2000/svg' d='M15.906,18.599h-1h-12h-1h-1v-7h2v5h12v-5h2v7H15.906z M13.157,7.279L9.906,4.028v8.571c0,0.552-0.448,1-1,1c-0.553,0-1-0.448-1-1v-8.54l-3.22,3.22c-0.403,0.403-1.058,0.403-1.46,0 c-0.403-0.403-0.403-1.057,0-1.46l4.932-4.932c0.211-0.211,0.488-0.306,0.764-0.296c0.275-0.01,0.553,0.085,0.764,0.296 l4.932,4.932c0.403,0.403,0.403,1.057,0,1.46S13.561,7.682,13.157,7.279z'/></svg>";
                msg += "</div>";
                msg += "<input type='file' name='form_bg_img' id='form_bg_img' data-val='form_bg' class='original' style='position: absolute; cursor: pointer; top: 0px; padding:0; margin:0; height:100%; width:100%; right:0; z-index: 100; opacity: 0; filter:alpha(opacity=0);' />";
                msg += "</div>";
                msg += "<input type='hidden' name='imagename_form' id='imagename_form' value='' />";
                msg += "<input type='hidden' name='arfmfbi' onClick='clear_file_submit();' value='' id='arfmainform_bg_img' /> ";
                jQuery('#form_bg_img_div').html(msg);
            } else if ( image_name == 'delete_submit_hover_bg_img' ){
                var arf_bg_position_x = jQuery('#arf_bg_position_x').val();
                var arf_bg_position_y = jQuery("#arf_bg_position_y").val();
                var arf_bg_position_input_x = jQuery("#arf_form_bg_position_input_x").val();
                var arf_bg_position_input_y = jQuery("#arf_form_bg_position_input_y").val();
                var position_style = '';
                
                if(arf_bg_position_y!='' && arf_bg_position_y!='px'){
                    position_style += 'background-position-y: '+arf_bg_position_y+";";
                } else if(arf_bg_position_y!='' && arf_bg_position_y=='px'){
                    position_style += 'background-position-y: '+arf_bg_position_input_y+"px;";
                }

                if(arf_bg_position_x!='' && arf_bg_position_x!='px'){
                    position_style += 'background-position-x: '+arf_bg_position_x+";";    
                } else if(arf_bg_position_x!='' && arf_bg_position_x=='px'){
                    position_style += 'background-position-x: '+arf_bg_position_input_x+"px;";    
                }

                var form_id = jQuery('#id').val();
                jQuery("#ar_main_div_"+form_id+"_submit_hover_button").remove();
                var define_hover_style = '<style id="ar_main_div_"+form_id+"_submit_hover_button">';
                define_hover_style +='.ar_main_div_'+form_id+' .arf_fieldset .arf_submit_btn:hover{background-image:none !important;'+position_style+'background-repeat:no-repeat !important;}';                        
                define_hover_style +='</style>';
                jQuery('body').append(define_hover_style);
                var msg = "<input type='hidden' name='arfsbhis' onClick='clear_file_submit_hover();' value='' id='arfsubmithoverbuttonimagesetting' />";
                msg += "<div class='arfajaxfileupload'>";
                msg += "<div class='arf_form_style_file_upload_icon'>";
                msg += "<svg width='16' height='18' viewBox='0 0 18 20' fill='#ffffff'><path xmlns='http://www.w3.org/2000/svg' d='M15.906,18.599h-1h-12h-1h-1v-7h2v5h12v-5h2v7H15.906z M13.157,7.279L9.906,4.028v8.571c0,0.552-0.448,1-1,1c-0.553,0-1-0.448-1-1v-8.54l-3.22,3.22c-0.403,0.403-1.058,0.403-1.46,0 c-0.403-0.403-0.403-1.057,0-1.46l4.932-4.932c0.211-0.211,0.488-0.306,0.764-0.296c0.275-0.01,0.553,0.085,0.764,0.296 l4.932,4.932c0.403,0.403,0.403,1.057,0,1.46S13.561,7.682,13.157,7.279z'/></svg>";
                msg += "</div>";
                msg += "<input type='file' name='submit_hover_btn_img' id='submit_hover_btn_img' data-val='submit_hover_bg' class='original' style='position: absolute; cursor: pointer; top: 0px; padding:0; margin:0; height:100%; width:100%; right:0; z-index: 100; opacity: 0; filter:alpha(opacity=0);' />";
                msg += "</div>";
                msg += "<input type='hidden' name='imagename_submit_hover' id='imagename_submit_hover' value='' />";
                jQuery('#submit_hover_btn_img_div').html(msg);
            }            
        <?php } ?>
    }

    

    var temp_css_preview_iframeurl = "<?php echo $pre_link; ?>";    
    function showorhidetitle() {
        if (document.getElementById("display_title_form").checked == false)
        {
            jQuery('#testiframe').contents().find('.formtitle_style').attr("style", "display:none");
            jQuery('#testiframe').contents().find('.arftitlecontainer').attr("style", "display:none");
        }
    }

    function CallApplyClick() {
        closeslide_hide_fn();
    }

    function CallPreview() {
        jQuery("#doslide_show").click();
    }

    function frmSetPosClass(value) {
        
        if (value == 'none')
            value = 'none';
        if (jQuery('#arfhidelabels').val() == '1' || jQuery('#arfhidelabels').is(':checked')) {
            value = 'none';
        }
        if (value == 'top') {
            var form_id = jQuery('#id').val();
            jQuery('#arf_'+form_id+'_label_width').remove();
            
            jQuery("#arfmainformwidthsetting").attr('readonly',true);
            arf_label_width_in_editor();
            jQuery('.inplace_field').trigger('keyup');
        }else{
            jQuery("#arfmainformwidthsetting").attr('readonly',false);
            jQuery('.inplace_field').parents('.arf_main_label').removeAttr('style');
            jQuery('#arfmainformwidthsetting').trigger('change');
        }

        jQuery("#arfmainformeditorcontainer").find('div.arfformfield').removeClass('top_container none_container left_container right_container').addClass(value + '_container');
        jQuery("#arfmainformeditorcontainer").find('div.arf_heading_div h2').removeClass('pos_top pos_none pos_left pos_right').addClass('pos_' + value);
        jQuery("#arfmainformeditorcontainer").find('div.arf_submit_div').removeClass('top_container none_container left_container right_container').addClass(value + '_container');
    }

    function change_form_title() {

        if (jQuery('#display_title_form').is(':checked')) {
            jQuery('#display_title_form').val('1');
            jQuery('#form_title_style_div').show();
        } else {
            jQuery('#display_title_form').val('0');
            jQuery('#form_title_style_div').hide();
        }

        if (document.getElementById("display_title_form").value == '0')
        {
            var value = 'none';
        }
        else
        {
            var value = 'block';
        }
        jQuery("#arfmainformeditorcontainer").find('.arftitlediv').css('display',value);
    }

    

    function change_submit_img() {        
        var upload_css_url = '<?php echo $upload_css_url; ?>';
        var img = jQuery('#imagename').val();
        var image = upload_css_url + img;
        jQuery("#ajax_submit_loader").hide();
        jQuery("#ajax_submit_loader").removeAttr("style");
        var msg = "<input type='hidden' name='arfsbis' onClick='clear_file_submit();' value='"+image+"' id='arfsubmitbuttonimagesetting' />";
        msg += "<img src='"+image+"' height='35' width='35' style='border:1px solid #D5E3FF !important;' />&nbsp;";
        msg += "<span onclick='delete_image(\"button_image\");' style='width:35px;height: 35px;display:inline-block;cursor: pointer;'>";
        msg += "<svg width='23px' height='27px' viewBox='0 0 30 30'>";
        msg += "<path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#4786FF' d='M19.002,4.351l0.007,16.986L3.997,21.348L3.992,4.351H1.016V2.38  h1.858h4.131V0.357h8.986V2.38h4.146h1.859l0,0v1.971H19.002z M16.268,4.351H6.745H5.993l0.006,15.003h10.997L17,4.351H16.268z   M12.01,7.346h1.988v9.999H12.01V7.346z M9.013,7.346h1.989v9.999H9.013V7.346z' />";
        msg += "</svg>";
        msg += "</span>";
        jQuery('#submit_btn_img_div').html(msg);
        var form_id = jQuery("#id").val();
        var $class = ".ar_main_div_" + form_id + " .arfsubmitbutton .arf_submit_btn";
        var $property = "background-image";
        var $style = jQuery($class).attr("style");
        var width_button = jQuery('#arfsubmitbuttonwidthsetting').val();
        var button_height = jQuery('#arfsubmitbuttonheightsetting').val();
        var border_size = jQuery("#arfsubmitbuttonborderwidhtsetting").val();
        var border_radius = jQuery("#arfsubmitbuttonborderradiussetting").val();
        if (typeof $style != 'undefined') {
            if (/(background\-image\:(.*?)\;)/g.test($style)) {
                $style = $style.replace(/(background\-image\:(.*?)\;)/g, '');
            }
            if (/(background\-position\:(.*?)\;)/g.test($style)) {
                $style = $style.replace(/(background\-position\:(.*?)\;)/g, '');
            }
            if (/(background\-repeat\:(.*?)\;)/g.test($style)) {
                $style = $style.replace(/(background\-repeat\:(.*?)\;)/g, '');
            }
            $style = $style + 'background-image:url(' + image + ') !important;background-position:top left;background-repeat:no-repeat !important;width:' + width_button + 'px;height:' + button_height + 'px;border-width:' + border_size + 'px !important;border-radius:' + border_radius + 'px !important;';            
        } else {
            $style = 'background-image:url(' + image + ') !important;background-position:top left;background-repeat:no-repeat !important;width:' + width_button + 'px;height:' + button_height + 'px;border-width:' + border_size + 'px !important;border-radius:' + border_radius + 'px !important;';            
        }
        jQuery(".ar_main_div_" + form_id + " .arfsubmitbutton .arf_submit_btn .arf_edit_in_place_input").css('display', 'none');
        var submitBtnHoverImg = jQuery("input[name='arfsbhis']").val();
        if (submitBtnHoverImg != '') {
            var aStyle = jQuery(".ar_main_div_" + form_id + " .arf_fieldset .arf_submit_btn").attr('style');
            if (typeof aStyle != 'undefined') {
                if (/(background\-image\:(.*?)\;)/gi.test(aStyle)) {
                    nStyle = aStyle.replace(/(background\-image\:(.*?)\;)/gi, '');
                    var hStyle = nStyle + 'background-image:url(' + submitBtnHoverImg + ') !important;';
                    var $aStyle = nStyle + 'background-image:url(' + image + ') !important;';
                    var mouseOver = "jQuery(this).attr('style','" + hStyle + "');";
                    var mouseOut = "jQuery(this).attr('style','" + $aStyle + "');";
                }
            } else {
                var mouseOver = "jQuery(this).attr('style','background-image:url(" + submitBtnHoverImg + ") !important;width:" + width_button + "px;height:" + button_height + "px;');"
                var mouseOut = "jQuery(this).attr('style','background-image:url(" + image + ") !important;width:" + width_button + "px;height:" + button_height + "px;')";
            }
        }
        
       
        
        if(image !='')
        {
            if(jQuery('#ar_main_div_'+form_id+'_submit_button').length > 0)
            {
                  jQuery('#ar_main_div_'+form_id+'_submit_button').remove();
            }
            var btn_style_define = '<style id="ar_main_div_'+form_id+'_submit_button">';
            btn_style_define +='.ar_main_div_'+form_id+' .arf_fieldset .arf_submit_btn,.ar_main_div_'+form_id+' .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_border,.ar_main_div_'+form_id+' .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_flat,.ar_main_div_'+form_id+' .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_reverse_border{ background-image:url('+image+') !important;background-position:top left; }'; 
            btn_style_define +='</style>';
            jQuery("body").append(btn_style_define);
        }
        
        if (submitBtnHoverImg != '' && image !='') {            
            if(jQuery('#ar_main_div_'+form_id+'_submit_hover_button').length > 0)
            {
                  jQuery('#ar_main_div_'+form_id+'_submit_hover_button').remove();
            }
            var btn_style_hover_define = '<style id="ar_main_div_'+form_id+'_submit_hover_button">';
            btn_style_hover_define +='.ar_main_div_'+form_id+' .arf_fieldset .arf_submit_btn:hover,.ar_main_div_'+form_id+' .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_border,.ar_main_div_'+form_id+' .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_flat,.ar_main_div_'+form_id+' .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_reverse_border{ background-image:url('+submitBtnHoverImg+') !important;background-position:top left; }';
            btn_style_hover_define +='</style>';
            jQuery("body").append(btn_style_hover_define);
        }  
    }

    function change_form_bg_img() {
        var arf_bg_position_x = jQuery('#arf_bg_position_x').val();
        var arf_bg_position_y = jQuery("#arf_bg_position_y").val();
        var arf_bg_position_input_x = jQuery("#arf_form_bg_position_input_x").val();
        var arf_bg_position_input_y = jQuery("#arf_form_bg_position_input_y").val();
        var position_style = '';
        
        if(arf_bg_position_y!='' && arf_bg_position_y!='px'){
            position_style += 'background-position-y: '+arf_bg_position_y+";";
        } else if(arf_bg_position_y!='' && arf_bg_position_y=='px'){
            position_style += 'background-position-y: '+arf_bg_position_input_y+"px;";
        }

        if(arf_bg_position_x!='' && arf_bg_position_x!='px'){
            position_style += 'background-position-x: '+arf_bg_position_x+";";    
        } else if(arf_bg_position_x!='' && arf_bg_position_x=='px'){
            position_style += 'background-position-x: '+arf_bg_position_input_x+"px;";    
        }

        var upload_css_url = '<?php echo $upload_css_url; ?>';
        var img = jQuery('#imagename_form').val();
        var image = upload_css_url + img;
        jQuery("#ajax_form_loader").removeAttr('style');
        jQuery("#ajax_form_loader").hide();
        var msg = "<input type='hidden' name='arfmfbi' onClick='clear_file_submit();' value='"+image+"' id='arfmainform_bg_img' />";
        msg += "<img src='"+image+"' height='35' width='35' style='border:1px solid #D5E3FF !important;' />&nbsp;";
        msg += "<span onclick='delete_image(\"form_image\");' style='width:35px;height: 35px;display:inline-block;cursor: pointer;'>";
        msg += "<svg width='23px' height='27px' viewBox='0 0 30 30'>";
        msg += "<path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#4786FF' d='M19.002,4.351l0.007,16.986L3.997,21.348L3.992,4.351H1.016V2.38  h1.858h4.131V0.357h8.986V2.38h4.146h1.859l0,0v1.971H19.002z M16.268,4.351H6.745H5.993l0.006,15.003h10.997L17,4.351H16.268z   M12.01,7.346h1.988v9.999H12.01V7.346z M9.013,7.346h1.989v9.999H9.013V7.346z' />";
        msg += "</svg>";
        msg += "</span>";
        jQuery('#form_bg_img_div').html(msg);
        var form_id = jQuery("#id").val();
        var $class = ".ar_main_div_" + form_id + " .arf_fieldset";
        var $property = "background-image";
        var $style = jQuery($class).attr("style");
        if (typeof $style != 'undefined') {
            if (/(background\-image\:(.*?)\;)/g.test($style)) {
                $style = $style.replace(/(background\-image\:(.*?)\;)/g, '');
            }
            if (/(background\-position\:(.*?)\;)/g.test($style)) {
                $style = $style.replace(/(background\-position\:(.*?)\;)/g, '');
            }
            if (/(background\-position\-x\:(.*?)\;)/g.test($style)) {
                $style = $style.replace(/(background\-position\-x\:(.*?)\;)/g, '');
            }
            if (/(background\-position\-y\:(.*?)\;)/g.test($style)) {
                $style = $style.replace(/(background\-position\-y\:(.*?)\;)/g, '');
            }
            if (/(background\-repeat\:(.*?)\;)/g.test($style)) {
                $style = $style.replace(/(background\-repeat\:(.*?)\;)/g, '');
            }
            $style = $style + 'background-image:url(' + image + ') !important;'+position_style+'background-repeat:no-repeat !important;';
            jQuery($class).attr('style', $style);
        } else {
            $style = 'background-image:url(' + image + ') !important;'+position_style+'background-repeat:no-repeat !important;';
            jQuery($class).attr('style', $style);
        }        
    }

    function change_submit_hover_img() {

        var upload_css_url = '<?php echo $upload_css_url; ?>';
        var img = jQuery('#imagename_submit_hover').val();
        var image = upload_css_url + img;
        jQuery.ajax({type: "POST", url: ajaxurl, data: "action=upload_submit_hover_bg&image=" + image,
            success: function (msg) {
                var submitBgImg = jQuery("input[name='arfsbis']").val();
                var submitBgHoverImg = image;
                var form_id = jQuery('#id').val();
                if (submitBgImg != '') {
                    var aStyle = jQuery(".ar_main_div_" + form_id + " .arf_fieldset .arf_submit_btn").attr('style');
                    if (typeof aStyle != 'undefined') {
                        if (/(background\-image\:(.*?)\;)/gi.test(aStyle)) {
                            nStyle = aStyle.replace(/(background\-image\:(.*?)\;)/gi, '');
                            var hStyle = nStyle + 'background-image:url(' + submitBgHoverImg + ') !important;';
                            var $aStyle = nStyle + 'background-image:url(' + submitBgImg + ') !important;';
                            var mouseOver = "jQuery(this).attr('style','" + hStyle + "');";
                            var mouseOut = "jQuery(this).attr('style','" + $aStyle + "');";
                        }
                    } else {
                        var mouseOver = "jQuery(this).attr('style','background-image:url(" + submitBgHoverImg + ") !important;');"
                        var mouseOut = "jQuery(this).attr('style','background-image:url(" + submitBgImg + ") !important;')";
                    }                    
                }
                if(submitBgImg !='')
                {
                    if(jQuery('#ar_main_div_'+form_id+'_submit_button').length > 0)
                    {
                          jQuery('#ar_main_div_'+form_id+'_submit_button').remove();
                    }
                    var btn_style_define = '<style id="ar_main_div_'+form_id+'_submit_button">';
                    btn_style_define +='.ar_main_div_'+form_id+' .arf_fieldset .arf_submit_btn,.ar_main_div_'+form_id+' .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_border,.ar_main_div_'+form_id+' .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_flat,.ar_main_div_'+form_id+' .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_reverse_border{ background-image:url('+submitBgImg+') !important;background-position:top left;}'; 
                    btn_style_define +='</style>';
                    jQuery("body").append(btn_style_define);
                }
                
                if (submitBgHoverImg != '' && submitBgImg !='') {
                    
                    if(jQuery('#ar_main_div_'+form_id+'_submit_hover_button').length > 0)
                    {
                          jQuery('#ar_main_div_'+form_id+'_submit_hover_button').remove();
                    }
                    var btn_style_hover_define = '<style id="ar_main_div_'+form_id+'_submit_hover_button">';
                    btn_style_hover_define +='.ar_main_div_'+form_id+' .arf_fieldset .arf_submit_btn:hover,.ar_main_div_'+form_id+' .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_border,.ar_main_div_'+form_id+' .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_flat,.ar_main_div_'+form_id+' .arf_materialize_form .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_reverse_border{ background-image:url('+submitBgHoverImg+') !important;background-position:top left;background-repeat:no-repeat !important; }';                    
                    btn_style_hover_define +='</style>';
                    jQuery("body").append(btn_style_hover_define);
                }             
                jQuery("#ajax_submit_hover_loader").removeAttr("style");
                jQuery("#ajax_submit_hover_loader").hide();
                jQuery('#submit_hover_btn_img_div').html(msg);                
            }

        });
    }
   
    /* arf_dev_flag : remove this function if not necessary */
    function change_auto_width() {

        if (jQuery('#arfautowidthsetting').is(':checked')) {

            jQuery('#sltstandard_front select').css({"width": "auto"});
            jQuery('#sltstandard_front select').val('').trigger("liszt:updated");
        } else {
            var width = 0;
            width = jQuery('#arfmainfieldwidthsetting').val();
            width = +width + +parseInt(2);
            jQuery('#drop_down_example_chzn').css({"width": width + "px"});
            width = jQuery('#arfmainfieldwidthsetting').val();
            jQuery('#drop_down_example_chzn .chzn-drop').css({"width": width + "px"});
        }

        width = jQuery('#arfmainfieldwidthsetting').val();
    }

    jQuery(document).ready(function () {

        jQuery("span[name=arfmfo]").click(function () {
            if (jQuery("input[name=arfmfo]").is(':checked'))
            {
                jQuery("input[name=arfmfo]:checkbox").val('1').trigger("change");
            }
            else
            {
                jQuery("input[name=arfmfo]:checkbox").val('0').trigger("change");
            }
        });
<?php
if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '8') {
    echo '';
} else {
    ?>

            jQuery('#arfmainfieldsetradius_exs').arf_slider();
            jQuery('#arfmainfieldsetradius_exs').arf_slider().on('slideStop', function (ev) {
                var data = jQuery('#arfmainfieldsetradius_exs').arf_slider('getValue');
                var id = jQuery(this).attr('id');
                id = id.replace('_exs', '');
                jQuery('#' + id).val(data).trigger('change');
            });
            jQuery('#arfmainfieldset_exs').arf_slider({tooltip: 'always'});
            jQuery('#arfmainfieldset_exs').arf_slider({tooltip: 'always'}).on('slideStop', function (ev) {

                var data = jQuery('#arfmainfieldset_exs').arf_slider('getValue');
                var id = jQuery(this).attr('id');
                id = id.replace('_exs', '');
                jQuery('#' + id).val(data).trigger('change');
            });
            jQuery('#arfmainform_opacity_exs').arf_slider({
                formater: function (value) {
                    if (value > 0 && !isNaN(value))
                    {
                        var value = (value == 0) ? 0 : value / 10;
                        if (value < 1 && value != 0) {
                            value = value.toFixed(2);
                        }

                        return value;
                    }
                    return 0;
                }
            });
            jQuery('#arfmainform_opacity_exs').arf_slider().on('slideStop', function (ev) {
                var data = jQuery('#arfmainform_opacity_exs').arf_slider('getValue');
                var id = jQuery(this).attr('id');
                id = id.replace('_exs', '');
                var val = (data == 0) ? 0 : data / 10;
                if (val < 1 && val != 0) {
                    val = val.toFixed(2);
                }
                jQuery('#' + id).val(val).trigger('change');
            });

            jQuery('#arfplaceholder_opacity_exs').arf_slider({
                formater: function (value) {
                    if (value > 0 && !isNaN(value))
                    {
                        var value = (value == 0) ? 0 : value / 10;
                        if (value < 1 && value != 0) {
                            value = value.toFixed(2);
                        }

                        return value;
                    }
                    return 0;
                }
            });
            jQuery('#arfplaceholder_opacity_exs').arf_slider().on('slideStop', function (ev) {
                var data = jQuery('#arfplaceholder_opacity_exs').arf_slider('getValue');
                var id = jQuery(this).attr('id');
                id = id.replace('_exs', '');
                var val = (data == 0) ? 0 : data / 10;
                if (val < 1 && val != 0) {
                    val = val.toFixed(2);
                }
                jQuery('#' + id).val(val).trigger('change');
            });
            
            jQuery('#arfmainbordersetting_exs').arf_slider();
            jQuery('#arfmainbordersetting_exs').arf_slider().on('slideStop', function (ev) {

                var data = jQuery('#arfmainbordersetting_exs').arf_slider('getValue');
                var id = jQuery(this).attr('id');
                id = id.replace('_exs', '');
                jQuery('#' + id).val(data).trigger('change');
            });
            if(jQuery('#arfmainforminputstyle').val() == 'material' || jQuery('#arfmainforminputstyle').val() == 'rounded'){
                jQuery('#arfmainbordersetting_exs').arf_slider('disable');
            } 
            jQuery('#arffieldborderwidthsetting_exs').arf_slider();
            jQuery('#arffieldborderwidthsetting_exs').arf_slider().on('slideStop', function (ev) {

                var data = jQuery('#arffieldborderwidthsetting_exs').arf_slider('getValue');
                var id = jQuery(this).attr('id');
                id = id.replace('_exs', '');
                jQuery('#' + id).val(data).trigger('change');
            });
            jQuery('#arfsubmitbuttonborderradiussetting_exs').arf_slider();
            jQuery('#arfsubmitbuttonborderradiussetting_exs').arf_slider().on('slideStop', function (ev) {

                var data = jQuery('#arfsubmitbuttonborderradiussetting_exs').arf_slider('getValue');
                var id = jQuery(this).attr('id');
                id = id.replace('_exs', '');
                jQuery('#' + id).val(data).trigger('change');
            });
            jQuery('#arfsubmitbuttonborderwidhtsetting_exs').arf_slider();
            jQuery('#arfsubmitbuttonborderwidhtsetting_exs').arf_slider().on('slideStop', function (ev) {

                var data = jQuery('#arfsubmitbuttonborderwidhtsetting_exs').arf_slider('getValue');
                var id = jQuery(this).attr('id');
                id = id.replace('_exs', '');
                jQuery('#' + id).val(data).trigger('change');
            });
            
            if(jQuery('#arfmainforminputstyle').val() == 'material'){
                jQuery('#arffieldinnermarginssetting_1_exs').arf_slider();
                jQuery('#arffieldinnermarginssetting_1_exs').arf_slider().on('slideStop', function (ev) {

                    var data = jQuery('#arffieldinnermarginssetting_1_exs').arf_slider('getValue');
                    var id = jQuery(this).attr('id');
                    id = id.replace('_exs', '');
                    if (!jQuery.isFunction(arf_change_field_spacing)) {
                        return;
                    }
                    arf_change_field_spacing();
                    jQuery('#arffieldinnermarginsetting_1').val(0);
                });
                jQuery('#arffieldinnermarginssetting_1_exs').arf_slider('disable');
            }
            else{
                jQuery('#arffieldinnermarginssetting_1_exs').arf_slider();
                jQuery('#arffieldinnermarginssetting_1_exs').arf_slider().on('slideStop', function (ev) {

                    var data = jQuery('#arffieldinnermarginssetting_1_exs').arf_slider('getValue');
                    var id = jQuery(this).attr('id');
                    id = id.replace('_exs', '');
                    if (!jQuery.isFunction(arf_change_field_spacing)) {
                        return;
                    }
                    arf_change_field_spacing();
                    jQuery('#arffieldinnermarginsetting_1').val(data);
                });
            }
            
            jQuery('#arffieldinnermarginssetting_2_exs').arf_slider();
            jQuery('#arffieldinnermarginssetting_2_exs').arf_slider().on('slideStop', function (ev) {

                var data = jQuery('#arffieldinnermarginssetting_2_exs').arf_slider('getValue');
                var id = jQuery(this).attr('id');
                id = id.replace('_exs', '');
                if (!jQuery.isFunction(arf_change_field_spacing)) {
                    return;
                }
                arf_change_field_spacing();
                jQuery('#arffieldinnermarginsetting_2').val(data).trigger('change');
            });
<?php } ?>

        jQuery('.widget .widget-inside').not('.current_widget .widget-inside').hide();
        jQuery('#preview-form-styling-setting').css('height', '');
        jQuery('#preview-form-styling-setting').removeAttr('style');
    });
    function ShowColorSelect(checkradiosty) {
        var inputstyle = jQuery("#arfmainforminputstyle").val();
        if (checkradiosty != "custom")
        {  
            jQuery('#check_radio_main_color').show();
            jQuery('#check_radio_main_icon').hide();
        }
        else
        {   
            jQuery('#check_radio_main_color').hide();
            if (checkradiosty == "custom") {
                jQuery('#check_radio_main_icon').show();
                if (inputstyle == 'rounded') {
                    jQuery("#arf_field_check_radio_wrapper").find('.arf_radio_wrapper').css('display', 'none');
                } else {
                    jQuery("#arf_field_check_radio_wrapper").find('.arf_radio_wrapper').css('display', 'block');
                }
            } else {
                jQuery('#check_radio_main_icon').hide();
            }
        }
    }
    function frmsetfieldtransparancy()
    {
        if (jQuery("input[name=arfmfo]:checkbox:checked").val() == 0)
        {
            jQuery("input[name=arfmfo]:checkbox").val('1').trigger('change');
        }
        else
        {
            jQuery("input[name=arfmfo]:checkbox").val('0').trigger('change');
        }
    }

    function arf_change_error_type() {
        var value = jQuery('input[name="arfest"]:checked').val();
        var form_id = jQuery('#id').val();
        jQuery('#testiframe').contents().find('form [data-id="form_tooltip_error_' + form_id + '"]').val(value);
        if (value == "advance")
        {
            jQuery("#showadvanceposition").css("display", 'block');
            jQuery("#color_palate_advance").css("display", 'block');
            jQuery("#color_palate_normal").css("display", 'none');
            jQuery('#testiframe').contents().find('.popover').remove();
            if (jQuery('#testiframe').contents().find('input,textarea,select').hasClass("arf_required"))
            {
                jQuery('#testiframe').contents().find('.arf_submit_btn').trigger('click');
            }
        }
        else
        {
            jQuery("#color_palate_normal").css("display", 'block');
            jQuery("#color_palate_advance").css("display", 'none');
            jQuery("#showadvanceposition").css("display", 'none');
            jQuery('#testiframe').contents().find('.help-block').empty().removeClass('arfanimated bounceInDownNor');
            if (jQuery('#testiframe').contents().find('input,textarea,select').hasClass("arf_required"))
            {
                jQuery('#testiframe').contents().find('.arf_submit_btn').trigger('click');
            }
        }
    }

    function arf_change_error_position() {
        var value = jQuery('input[name="arfestbc"]:checked').val();
        var form_id = jQuery('#id').val();
        jQuery('#testiframe').contents().find('form  [data-id="form_tooltip_error_' + form_id + '"]').attr('data-position', value);
        jQuery('#testiframe').contents().find('.popover').remove();
        if (jQuery('#testiframe').contents().find('input,textarea,select').hasClass("arf_required"))
        {
            jQuery('#testiframe').contents().find('.arf_submit_btn').trigger('click');
        }
    }

    function arf_change_check_radio() {
        var checkbox_class = '';
        var checked_checkbox_class = '';
        var checked_radio_class = '';
        var chk_style = jQuery('#frm_check_radio_style').val();
        var chk_color = jQuery('#frm_check_radio_style_color').val();
        var chk_checkbox_icon = jQuery('#arf_checkbox_icon').val();
        var chk_radio_icon = jQuery('#arf_radio_icon').val();
        if (chk_style != 'none') {
            checkbox_class = chk_style;
            if (chk_style != 'custom' && chk_style != 'futurico' && chk_style != 'polaris' && chk_color != 'default') {
                checkbox_class = checkbox_class + '-' + chk_color;
            }

            if (chk_style == 'custom') {

                if (chk_checkbox_icon != '') {
                    checked_checkbox_class = ' arfa ' + chk_checkbox_icon;
                } else {
                    checked_checkbox_class = '';
                }
                if (chk_radio_icon != '') {
                    checked_radio_class = ' arfa ' + chk_radio_icon;
                } else {
                    checked_radio_class = '';
                }
            } else {

            }
            
        }

    }

    function change_date_format_new() {
        var value = jQuery('#frm_date_format').val();
        if (value == '' || typeof value == 'undefined') {
            value = 'MM/DD/YYYY';
        }
        jQuery(".arf_editor_datetimepicker").each(function (e){     
            jQuery(this).data("DateTimePicker").format(value);
            var $this = jQuery(this);
            var attr_id = $this.parents('.arfmainformfield').attr('id');
            var id = attr_id.replace('arf_field_', '');
            var field_data = arf_retrieve_field_data(id);

            var placeholder_val = jQuery(this).val();
            if(placeholder_val==null || placeholder_val==undefined || placeholder_val==''){
                placeholder_val = jQuery(this).attr('placeholder');
            }
            if(placeholder_val !='' && placeholder_val != 'undefined' && placeholder_val != null){
                placeholder_changed_value = moment(placeholder_val).format(value);
                if(placeholder_changed_value=='Invalid date')
                {
                    placeholder_val_splt = placeholder_val.split('/'); //For dd/mm/yy format not converting to mm/dd/yy
                    if(placeholder_val_splt.length>2)
                    {
                        placeholder_changed_value = placeholder_val_splt[1]+"/"+placeholder_val_splt[0]+"/"+placeholder_val_splt[2];

                        placeholder_changed_value = moment(placeholder_changed_value).format(value);
                    }
                    else 
                    {
                        placeholder_changed_value = "";
                    }
                }
                jQuery(this).attr('placeholder',placeholder_changed_value);

                field_data.placeholdertext = placeholder_changed_value;
            }

            if( field_data.selectdefaultdate != '' ){
                var default_date = field_data.selectdefaultdate;
                var changed_default_val = moment(default_date).format(value);
                if( changed_default_val == 'Invalid date'){
                    var change_date_splt = default_date.split('/'); //For dd/mm/yy format not converting to mm/dd/yy
                    if(change_date_splt.length>2)
                    {
                        changed_default_val = change_date_splt[1]+"/"+change_date_splt[0]+"/"+change_date_splt[2];

                        changed_default_val = moment(changed_default_val).format(value);
                    }
                    else 
                    {
                        changed_default_val = "";
                    }   
                }
                field_data.selectdefaultdate = changed_default_val;
            }

            setTimeout(function(){
                var field_data_new = JSON.stringify(field_data);
                jQuery("#arf_field_data_" + id).val(field_data_new).trigger('change');
            },100);


        });
    }

<?php
if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '8') {
    echo '';
} else {
    ?>

        jQuery(document).ready(function () {
            
            jQuery('#arfmainfieldcommonsize_exs').arf_slider();
            jQuery('#arfmainfieldcommonsize_exs').arf_slider().on('slideStop', function (ev) {
                var data = jQuery('#arfmainfieldcommonsize_exs').arf_slider('getValue');
                var id = jQuery(this).attr('id');
                id = id.replace('_exs', '');
                jQuery('#' + id).val(data).trigger('change');
                if(jQuery('#arfmainforminputstyle').val() == 'material'){
                    var font_size = jQuery.parseJSON(convert_font_size_array_json_for_material());
                } else {
                    var font_size = jQuery.parseJSON(convert_font_size_array_json());
                }

                var font_size_val = font_size[data];
                jQuery.each(font_size_val, function (index, el) {
                    if (index == 'arfdescfontsizesetting') {
                        jQuery('#arf_form_styling_tools').find('#' + index).val(el).trigger('change');
                        jQuery('#arf_form_styling_tools').find('#' + index).next('dl').find('span').text(el);
                        jQuery('#arf_form_styling_tools').find('#' + index).next('dl').find('input').val(el);
                    } else {
                        jQuery('.arf_custom_font_popup').find('#' + index).val(el).trigger('change');
                        jQuery('.arf_custom_font_popup').find('#' + index).next('dl').find('span').text(el);
                        jQuery('.arf_custom_font_popup').find('#' + index).next('dl').find('input').val(el);
                    }
                });
                jQuery('.arf_custom_font_options').each(function(index, el) {
                    var value = jQuery(this).val();
                    jQuery(this).attr('data-default-font', value);
                });
            });
            jQuery('#arfmainfieldsetradius_exs').arf_slider();
            jQuery('#arfmainfieldsetradius_exs').arf_slider().on('slideStop', function (ev) {

                var data = jQuery('#arfmainfieldsetradius_exs').arf_slider('getValue');
                var id = jQuery(this).attr('id');
                id = id.replace('_exs', '');
                jQuery('#' + id).val(data).trigger('change');
            });
            jQuery('#arfmainfieldset_exs').arf_slider({tooltip: 'always'});
            jQuery('#arfmainfieldset_exs').arf_slider({tooltip: 'always'}).on('slideStop', function (ev) {

                var data = jQuery('#arfmainfieldset_exs').arf_slider('getValue');
                var id = jQuery(this).attr('id');
                id = id.replace('_exs', '');
                jQuery('#' + id).val(data).trigger('change');
            });
            jQuery('#arfmainform_opacity_exs').arf_slider({formater: function (value) {
                    if (value > 0 && !isNaN(value)) {
                        var value = (value == 0) ? 0 : value / 10;
                        if (value < 1 && value != 0) {
                            value = value.toFixed(2);
                        }

                        return value;
                    }
                    return 0;
                }
            });
            jQuery('#arfmainform_opacity_exs').arf_slider().on('slideStop', function (ev) {
                var data = jQuery('#arfmainform_opacity_exs').arf_slider('getValue');
                var id = jQuery(this).attr('id');
                id = id.replace('_exs', '');
                var val = (data == 0) ? 0 : data / 10;
                if (val < 1 && val != 0) {
                    val = val.toFixed(2);
                }
                jQuery('#' + id).val(val).trigger('change');
            });

            jQuery('#arfplaceholder_opacity_exs').arf_slider({formater: function (value) {
                    if (value > 0 && !isNaN(value)) {
                        var value = (value == 0) ? 0 : value / 10;
                        if (value < 1 && value != 0) {
                            value = value.toFixed(2);
                        }

                        return value;
                    }
                    return 0;
                }
            });
            jQuery('#arfplaceholder_opacity_exs').arf_slider().on('slideStop', function (ev) {
                var data = jQuery('#arfplaceholder_opacity_exs').arf_slider('getValue');
                var id = jQuery(this).attr('id');
                id = id.replace('_exs', '');
                var val = (data == 0) ? 0 : data / 10;
                if (val < 1 && val != 0) {
                    val = val.toFixed(2);
                }
                jQuery('#' + id).val(val).trigger('change');
            });

            jQuery('#arfmainbordersetting_exs').arf_slider();
            jQuery('#arfmainbordersetting_exs').arf_slider().on('slideStop', function (ev) {

                var data = jQuery('#arfmainbordersetting_exs').arf_slider('getValue');
                var id = jQuery(this).attr('id');
                id = id.replace('_exs', '');
                jQuery('#' + id).val(data).trigger('change');
            });
            jQuery('#arffieldborderwidthsetting_exs').arf_slider();
            jQuery('#arffieldborderwidthsetting_exs').arf_slider().on('slideStop', function (ev) {

                var data = jQuery('#arffieldborderwidthsetting_exs').arf_slider('getValue');
                var id = jQuery(this).attr('id');
                id = id.replace('_exs', '');
                jQuery('#' + id).val(data).trigger('change');
            });
            jQuery('#arfsubmitbuttonborderradiussetting_exs').arf_slider();
            jQuery('#arfsubmitbuttonborderradiussetting_exs').arf_slider().on('slideStop', function (ev) {

                var data = jQuery('#arfsubmitbuttonborderradiussetting_exs').arf_slider('getValue');
                var id = jQuery(this).attr('id');
                id = id.replace('_exs', '');
                jQuery('#' + id).val(data).trigger('change');
            });
            jQuery('#arfsubmitbuttonborderwidhtsetting_exs').arf_slider();
            jQuery('#arfsubmitbuttonborderwidhtsetting_exs').arf_slider().on('slideStop', function (ev) {

                var data = jQuery('#arfsubmitbuttonborderwidhtsetting_exs').arf_slider('getValue');
                var id = jQuery(this).attr('id');
                id = id.replace('_exs', '');
                jQuery('#' + id).val(data).trigger('change');
            });
            jQuery('#arffieldinnermarginssetting_1_exs').arf_slider();
            jQuery('#arffieldinnermarginssetting_1_exs').arf_slider().on('slideStop', function (ev) {

                var data = jQuery('#arffieldinnermarginssetting_1_exs').arf_slider('getValue');
                var id = jQuery(this).attr('id');
                id = id.replace('_exs', '');
                if (!jQuery.isFunction(arf_change_field_spacing)) {
                    return;
                }
                arf_change_field_spacing();
                jQuery('#arffieldinnermarginsetting_1').val(data);
            });
            jQuery('#arffieldinnermarginssetting_2_exs').arf_slider();
            jQuery('#arffieldinnermarginssetting_2_exs').arf_slider().on('slideStop', function (ev) {

                var data = jQuery('#arffieldinnermarginssetting_2_exs').arf_slider('getValue');
                var id = jQuery(this).attr('id');
                id = id.replace('_exs', '');
                if (!jQuery.isFunction(arf_change_field_spacing)) {
                    return;
                }
                arf_change_field_spacing();
                jQuery('#arffieldinnermarginsetting_2').val(data);
            });
        });
<?php } ?>    
    function arfmainformedit(is_addtosite_page) {
        var def_title = '(Click here to add text)';
        var arf_date_regex = /^(0[1-9]|1[0-2])\/(0[1-9]|1\d|2\d|3[01])\/(19|20)\d{2}$/;
        if (typeof (__ARFDEFAULTTITLE) != 'undefined') {
            var def_title = __ARFDEFAULTTITLE;
        }

        if (jQuery('.arfeditorformname').text() == def_title || jQuery('.arfeditorformname').text() == '') {
            jQuery('#form_name_message').delay(0).fadeIn('slow');
            if (!jQuery.isFunction(arf_form_preview_load)) {
                return;
            }

            setTimeout(function () {
                jQuery('#form_name_message').fadeOut("slow");
            }, 5000);
            arf_form_preview_load('form');
            return false;
        }

<?php
do_action('arf_render_admin_form_validation', $id, $values);
global $arfajaxurl;
?>

        var form = jQuery("#frm_main_form").serialize();
        var form_id = jQuery('#frm_main_form').find('#id').val();
        var old_form_id = form_id;
        var form_preview = "none";
        var old_arfaction = jQuery('#frm_main_form').find('#arfaction').val();
        var allFields = document.querySelectorAll("#frm_main_form, .arf_custom_color_popup_container, .arf_custom_font_popup");
        var field_length = allFields.length;
        var objarray = [];
        for( var x = 0; x < field_length; x++ ){
            var obj = allFields[x];
            var json = obj.serializeJSON();
            objarray.push(json);
        }
        var fields = objarray.reduce(function(result, currentObject) {
            for(var key in currentObject) {
                if (currentObject.hasOwnProperty(key)) {
                    result[key] = currentObject[key];
                }
            }
            return result;
        }, {});
        if(jQuery('#form_name').val() == ''){
            jQuery("#error_message").find('.message_descripiton > div').first().html('Please enter form title');
            jQuery('#error_message').delay(500).animate({width: 'toggle'}, 'slow');
            jQuery(window.opera ? 'html, .arfmodal-body' : 'html, body, .arfmodal-body').animate({scrollTop: jQuery('#error_message').offset().top - 250}, 'slow');
            jQuery('#error_message').delay(4000).fadeOut('slow');
            return false;
        }     
        jQuery('.arf_top_menu_save_button').attr('disabled', true);
        jQuery('#arfaddtosubmit').attr('disabled', true);
        jQuery('#arfsaveformloader').show();
        var current_tab = jQuery('.arfformtab.current').attr('id');
        
        fields['form_id'] = form_id;
        fields['form_preview'] = form_preview;
        fields['action'] = 'arfformsavealloptions';
        var jsondata = jQuery.toJSON(fields);
        var arfsack = new sack(ajaxurl);
        arfsack.execute = 0;
        arfsack.method = 'POST';
        arfsack.setVar("action", "arfformsavealloptions");
        arfsack.setVar("form_id", form_id);
        arfsack.setVar("form_preview", form_preview);
        arfsack.setVar("filtered_form", jsondata);
        arfsack.onError = function () {
            jQuery("#error_message").find('.message_descripiton > div').first().html('<?php echo esc_js(addslashes(esc_html__("There is something error while saving form", "ARForms"))) ?>');
            jQuery('#error_message').delay(500).animate({width: 'toggle'}, 'slow');
            jQuery(window.opera ? 'html, .arfmodal-body' : 'html, body, .arfmodal-body').animate({scrollTop: jQuery('#error_message').offset().top - 250}, 'slow');
            jQuery('#error_message').delay(4000).fadeOut('slow');
            return false;
        };
        arfsack.onCompletion = loaded_ajax;
        arfsack.runAJAX();
        function loaded_ajax() {

            var msg = arfsack.response;
            var reponse = msg.split('^|^');
            var sucmessage = reponse[0];
            var form_id = reponse[1];
            var change_data = reponse[2];
            var json = arf_parse_json(change_data);
            
            jQuery.each(json, function (index, val) {
                jQuery('#' + index).val(val);
            });

            if( json.arf_default_newarr != '' && json.arf_default_newarr != null ){
                document.getElementById('default_style_attr').value = json.arf_default_newarr;
            }

            if( typeof json.arf_hidden_field_ids != 'undefined' ){
                var hidden_fields = json.arf_hidden_field_ids;
                var hidden_field_length = hidden_fields.length;
                for( var hf = 0; hf < hidden_field_length; hf++ ){
                    var ho_id = hidden_fields[hf].old_id;
                    var hn_id = hidden_fields[hf].new_id;
                    var hiddenLabel = document.querySelector('.arf_hidden_field_label_input[data-field-id="'+ho_id+'"]');
                    var hiddenValue = document.querySelector('input[name="item_meta['+ho_id+']"]');
                    var hiddenfdata = document.getElementById('arf_field_data_'+ho_id);

                    hiddenLabel.setAttribute('data-field-id',hn_id);
                    hiddenValue.setAttribute('name','item_meta['+hn_id+']');
                    hiddenfdata.setAttribute('name','arf_field_data_'+hn_id);
                    hiddenfdata.setAttribute('id','arf_field_data_'+hn_id);
                }
            }

            var new_html = reponse[3];
            if (sucmessage == 'false') {
                error_message = jQuery.parseJSON(new_html);
                jQuery("#error_message").find('.message_descripiton > div').first().html(error_message[0]);
                jQuery('#arfsaveformloader').hide();
                jQuery('#error_message').delay(500).animate({width: 'toggle'}, 'slow');
                jQuery(window.opera ? 'html, .arfmodal-body' : 'html, body, .arfmodal-body').animate({scrollTop: jQuery('#error_message').offset().top - 250}, 'slow');
                jQuery('#error_message').delay(4000).fadeOut('slow');
                return false;
            }
            
            window.is_add_new_field = false;
            window.added_new_fields = [];

            window.is_updated_field = false;
            window.updated_fields = [];

            window.is_delete_field = false;
            window.deleted_fields = [];

            window.loaded_settings = [];

            document.getElementById('new_fields').innerHTML = '';
            document.getElementById('new_fields').innerHTML = new_html;

            pagebreak_pagcount();

            var field_order = jQuery("#arf_field_order").val();
            var field_order_saved = jQuery.parseJSON(field_order);
            document.getElementById('arf_single_column_field_ids').value = "";
            window.arf_sender_id = '';
            window.arf_sender_parent = {};
            window.arf_sender_previous = {};
            
            jQuery('#arfsaveformloader').hide();

            checkpage_breakpos();
            jQuery(".sltstandard select").selectpicker();

            if (sucmessage == 'deleted') {
                window.location = __ARFDELETEURL;
            } else {
                if (sucmessage != ""){
                    initialize_sortable('.sortable_inner_wrapper');
                    initialize_element_sortable();
                    initialize_resizable();
                    arf_label_width_in_editor();

                    var stylesheet = document.getElementsByClassName('added_new_style_css');
                    
                    if( stylesheet.length > 0 ){
                        for( var css = 0; css < stylesheet.length; css++ ){
                            var current_css = stylesheet[css];
                            var style_sheet = current_css.innerHTML;
                            var old_form_id_regex = new RegExp(old_form_id,'g');
                            style_sheet = style_sheet.replace(old_form_id_regex,form_id);
                            current_css.innerHTML = style_sheet;     
                        }
                    }
                    
                    jQuery('#frm_main_form').find('#id').val(form_id);
                    jQuery('#frm_main_form').find('#arfaction').val("update");
                    jQuery('#arfmainformid').val(form_id);
                    jQuery("#arfmainformeditorcontainer").removeClass('ar_main_div_' + old_form_id);
                    jQuery("#arfmainformeditorcontainer").addClass('ar_main_div_' + form_id);
                    jQuery("#arfmainformeditorcontainer").find('.arf_fieldset').attr('id', 'arf_fieldset_' + form_id);
                    if (jQuery('body').find(".append_theme").size() > 1) {
                        jQuery('body').find(".append_theme").remove();
                    }
                    if (is_addtosite_page == 1) {
                        jQuery('#success_message').delay(1000).animate({width: 'toggle'}, 'slow');
                        jQuery('#form_name_message').css("display", "none");
                        jQuery('#success_message').delay(4000).fadeOut('slow');
                        jQuery(window.opera ? 'html, .arfmodal-body' : 'html, body, .arfmodal-body').animate({scrollTop: jQuery('#success_message').offset().top - 250}, 'slow');
                        setTimeout(function () {
                            jQuery('#success_message').animate({width: 'toggle'}, 'slow');
                            jQuery('#arfsubmitall').attr('disabled', false);
                            jQuery('#arfaddtosubmit').attr('disabled', false);
                        }, 4000);
                    } else {
                        jQuery('#form_name_message').css("display", "none");
                        jQuery('#success_message .message_descripiton div:not(.message_svg_icon)').html(sucmessage);
                        jQuery('#success_message').animate({width: 'toggle'}, 'slow');                        
                        setTimeout(function () {
                            jQuery('#success_message').animate({width: 'toggle'}, 'slow');
                            jQuery('.arf_top_menu_save_button').attr('disabled', false);
                            jQuery('#arfaddtosubmit').attr('disabled', false);
                        }, 4000);
                    }
                    if(old_arfaction == 'new' || old_arfaction == 'duplicate'){
                        var instance = jQuery('#arf_form_other_css').getCodeMirror();
                        if( instance.length > 0 ){
                            var CMInstance = instance[0].CodeMirror;
                            var arf_custom_css_value = CMInstance.getValue(); 
                            var pattern_custom_css = new RegExp('(_|-)('+old_form_id+')', 'gi');
                            var new_arf_custom_css = arf_custom_css_value.replace(pattern_custom_css, '$1'+form_id);
                            CMInstance.setValue(new_arf_custom_css);     
                            jQuery("#arf_form_other_css_"+old_form_id).attr('id','arf_form_other_css_'+form_id);
                            jQuery("#arf_form_other_css_"+form_id).html(new_arf_custom_css);
                        }

                        var arf_shortcodes = jQuery("#arf_editor_saved_form_shortcodes").html();
                        arf_shortcodes = arf_shortcodes.replace(/{arf_form_id}/ig, form_id);
                        jQuery("#arf_editor_saved_form_shortcodes").html(arf_shortcodes);
                        jQuery("#arf_editor_unsaved_form_shortcodes").hide();
                        jQuery("#arf_editor_saved_form_shortcodes").show();
                        jQuery("#arf_export_current_form_link").removeClass('arf_export_form_editor_note');
                        jQuery("#arf_export_current_form_link").tipso('destroy');
                        jQuery(".arf_save_form_id_note").html("(Form ID: "+form_id+")");
                        jQuery(".arf_save_form_id_note").removeClass('arf_save_form_id_note');
                        jQuery("#frm_add_form_id_name").val(form_id);
                        arf_do_action('arf_after_save_form_first_time');
                    }

                    arf_do_action('arf_after_save_form');

                    if (window.history.pushState && form_id < 10000) {
                        if (!jQuery.isFunction(arf_removeVariableFromURL)) {
                            return;
                        }
                        var pageurl = arf_removeVariableFromURL(document.URL, 'arfaction');
                        pageurl = arf_removeVariableFromURL(pageurl, 'id');
                        pageurl = arf_removeVariableFromURL(pageurl, 'templete_style');
                        pageurl = arf_removeVariableFromURL(pageurl, 'form_name');
                        pageurl = arf_removeVariableFromURL(pageurl, 'form_desc');
                        pageurl = arf_removeVariableFromURL(pageurl, 'arf_rtl_switch_mode');
                        pageurl += '&arfaction=edit&id=' + form_id;
                        window.history.pushState({path: pageurl}, '', pageurl);
                    }

                    jQuery('.arf_editor_slider').each(function () {
                        jQuery(this).arf_slider();                                                
                        jQuery(this).arf_slider().on('slideStop', function (ev) {
                            var data = jQuery(this).arf_slider('getValue');                            
                            var attr_id = jQuery(this).attr('id');
                            var id = attr_id.replace('arf_slider_', '');
                            var field_data = arf_retrieve_field_data(id);
                            if(field_data.arf_range_selector == 1)
                            {
                                for (var i = 0; i < data.length; i++) {                                            
                                    if(i == 0)
                                    {
                                        field_data.arf_range_minnum = data[i];
                                        field_data.slider_value = data[i];
                                    }
                                    if(i == 1)
                                    {
                                        field_data.arf_range_maxnum = data[i];
                                    }

                                }                                        
                            }
                            else{
                                field_data.slider_value = data;                                    
                            }
                            field_data = JSON.stringify(field_data);
                            jQuery("#arf_field_data_" + id).val(field_data);
                        });
                    });
                    var field_order = {};
                    var arf_f_order_index = 1;
                    jQuery("#new_fields").find('div.sortable_inner_wrapper,div.unsortable_inner_wrapper').each(function (i) {
                        if (jQuery(this).find('.arfformfield').length == 1)
                        {
                            var field_id = jQuery(this).attr('id').replace('arfmainfieldid_', '');
                            if(field_id.indexOf('_confirm') !== -1)
                            {
                                if(!jQuery(this).hasClass('arf_confirm_field'))
                                {
                                    jQuery(this).addClass('arf_confirm_field');
                                }
                            }
                            field_order[field_id] = arf_f_order_index;
                        }
                        else if(jQuery(this).hasClass('arf_confirm_field'))
                        {
                            if(jQuery(this).attr('id')!=null)
                            {
                                var field_id = jQuery(this).attr('id').replace('arfmainfieldid_', '');
                                field_order[field_id] = index;
                            }else {
                                jQuery(this).removeClass('arf_confirm_field');
                                get_inner_class = jQuery(this).attr('inner_class');
                                field_order[get_inner_class+'|'+arf_f_order_index] = arf_f_order_index;
                            }
                        }else {
                            get_inner_class = jQuery(this).attr('inner_class');
                            field_order[get_inner_class+'|'+arf_f_order_index] = arf_f_order_index;
                        }
                        arf_f_order_index++;
                    });

                    var old_vals = field_order_saved;
                    var new_vals = field_order;
                    var keys_to_change = [];
                    var keys_to_remove = [];
                    var counter = 0;
                    
                    var old_keys = [];
                    var new_keys = [];

                    for( var x in old_vals ){
                        old_keys[old_vals[x] - 1] = x;
                    }

                    for( var i in new_vals ){
                        new_keys[new_vals[i] - 1] = i;
                    }

                    if( old_keys.length == new_keys.length ){
                        for( var o = 0; o < old_keys.length; o++ ){
                            var ok = old_keys[o];
                            var nk = new_keys[o];
                            if( ok != nk ){
                                keys_to_change.push(ok+'|'+nk);
                                counter++;
                            }
                        }
                    }                    
                    if( counter > 0 ){
                        for(var i = 0; i < counter; i++ ){
                            var k = keys_to_change[i].split('|');
                            var oi = k[0];
                            var ni = k[1];                            
                            arf_update_id_dropdown(oi,ni);
                        }
                    }

                    field_order = JSON.stringify(field_order);
                    jQuery('input#arf_field_order').val(field_order).attr('data-db-field-order',field_order);
                    jQuery('input#arf_field_resize_width').attr('data-db-field-resize',jQuery('input#arf_field_resize_width').val());
                    
                    var input_style = jQuery("#arfmainforminputstyle").val();
                    __arf_jspicker_object = [];
                    arf_load_external_js_function(true);
                    
                    var link = document.getElementsByTagName('link');
                    var link_length = link.length;
                    setTimeout(function(){

                        if (input_style == 'material') {
                            var maincss_material_url = jQuery("#arfuploadurl").val() + 'maincss/maincss_materialize_' + form_id + '.css?ver=' + jQuery("#arfmainformversion").val();
                            var maincss_material_flag = false;
                            var link_pos = -1;
                            if(jQuery('#arf-main-style-editor-materialize-css').length > 0){
                               //jQuery('#arf-main-style-editor-materialize').remove();
                               var new_href = maincss_material_url + '&time=<?php echo time(); ?>';
                               jQuery("#arf-main-style-editor-materialize-css").attr('href',new_href);
                               //var removeLiveEditorCSS = setTimeout(function(){
                                //clearTimeout(removeLiveEditorCSS);
                               //},50);
                            } else {
                                while (link_length--) {
                                    if (typeof link[link_length] != 'undefined' && link[link_length].href == maincss_material_url) {
                                        maincss_material_flag = true;
                                        link_pos = link_length;
                                    }

                                    if (!maincss_material_flag) {
                                        arf_create_style_node(document, 'link', 'arf-main-style-editor-materialize-css', maincss_material_url);
                                    } else {
                                        if (link_pos > -1) {
                                            document.getElementsByTagName('link')[link_pos];
                                        }
                                    }
                                    Materialize.updateTextFields();
                                }
                            }
                        } else {

                            var maincss_url = jQuery("#arfuploadurl").val() + 'maincss/maincss_' + form_id + '.css?ver=' + jQuery("#arfmainformversion").val();
                            var maincss_flag = false;
                            var link_pos = -1;
                            if(jQuery('#arf-main-style-editor-css').length > 0){
                               //jQuery('#arf-main-style-editor-css').remove();   
                               var new_href = maincss_url + '&time=<?php echo time(); ?>';
                               jQuery("#arf-main-style-editor-css").attr('href',new_href);
                               var removeLiveEditorCSS = setTimeout(function(){
                                //jQuery('.arf_editor_live_css').remove();
                                //jQuery('.added_new_style_css').remove();
                                //clearTimeout(removeLiveEditorCSS);
                               },50);
                            } else {

                                while (link_length--) {                            
                                    if (typeof link[link_length] != 'undefined' && link[link_length].href == maincss_url) {
                                        maincss_flag = true;
                                        link_pos = link_length;
                                    }
                                }

                                if (!maincss_flag) {
                                    arf_create_style_node(document, 'link', 'arf-main-style-editor-css', maincss_url);
                                } else {
                                    if (link_pos > -1) {
                                        document.getElementsByTagName('link')[link_pos];
                                    }
                                }
                            }
                        }

                        if (jQuery("#new_fields").length > 0 && jQuery("#new_fields").find('div.arfformfield').length > 0) {
                            jQuery("#new_fields").find('div.arfformfield').each(function () {
                            var field_id = jQuery(this).attr('id').replace('arf_field_', '');
                                if (jQuery.isFunction(jQuery().tipso)) {
                                    jQuery('.arfhelptip').tipso({
                                        position: 'top',
                                        maxWidth: '400',
                                        useTitle: true,
                                        background: '#444444',
                                        color: '#ffffff',
                                        width: 'auto',
                                        tooltipHover: true,
                                    });
                                    jQuery('#arf_field_'+field_id).find('#tooltip_field_'+field_id+'.arfhelptip').each(function () {
                                        jQuery(this).tipso('destroy');
                                        var bgcolor = document.getElementById('arf_tooltip_bg_color').value;
                                        var textcolor = document.getElementById('arf_tooltip_font_color').value;
                                        var title = jQuery(this).attr('data-title');
                                        jQuery(this).tipso({
                                            position: 'top',
                                            width: 'auto',
                                            useTitle: false,
                                            content: title,
                                            background: bgcolor,
                                            color: textcolor,
                                            tooltipHover: true
                                        });
                                    });
                                }
                            });
                        }
                        jQuery('.added_new_style_css').remove();
                        jQuery('.arf_editor_live_css').remove();
                        //heightdiv('all');
                    },5000);
                    setTimeout(function(){
                        heightdiv('all');
                    },15);
                    jQuery('#changed_style_attr').val('');
                    var arf_preview_default_url = jQuery('.arf_top_menu_preview_button').attr('data-default-url');
                    if(arf_preview_default_url!='')
                    {
                        jQuery('.arf_top_menu_preview_button').attr('data-url',arf_preview_default_url+'&form_id='+form_id);
                        jQuery('.arf_top_menu_preview_button').attr('data-default-url', '');
                    }
                    
                }
            }

            

        }
        return false;
    }

    
</script>

<?php require(VIEWS_PATH . '/footer.php'); ?>