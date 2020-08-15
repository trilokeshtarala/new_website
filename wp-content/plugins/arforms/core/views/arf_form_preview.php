<?php

if (!function_exists('arf_display_form_preview')) {

    function arf_display_form_preview($form_id, $form_key, $posted_data = array()) {
        global $arsettingcontroller,$arfforms_loaded, $arf_preview_form;
        
        $arf_form = '';
        if (!isset($posted_data) || empty($posted_data)) {
            $arf_form .= addslashes(esc_html__('Please select valid form', 'ARForms'));
            echo $arf_form;
            die();
        }
        @ini_set('max_execution_time', 0);

        global $arfform, $user_ID, $arfsettings, $post, $wpdb, $armainhelper, $arrecordcontroller, $arformcontroller, $arfieldhelper, $arrecordhelper, $page_break_hidden_array, $arf_page_number, $arfforms_loaded, $arf_form_all_footer_js, $arfcreatedentry, $MdlDb, $arformhelper;


        $page_break_hidden_array = array();
        $arf_page_number = 0;
        $browser_info = $arrecordcontroller->getBrowser($_SERVER['HTTP_USER_AGENT']);

        $arf_data_uniq_id = $arf_popup_data_uniq_id = rand(1, 99999);

        $form = new stdClass();

        $form->id = $_REQUEST['arfmf'] = $posted_data['id'];
        $form->form_key = $form_key;
        $form->name = $posted_data['name'];
        $form->description = $posted_data['description'];
        $form->is_template = 0;
        $form->status = 'published';
        $arf_temp_fields = array();

        $options = $new_values = array();

        $new_values['name'] = $posted_data['name'];
        $new_values['description'] = $posted_data['description'];
        $new_values['status'] = 'published';

        $defaults = $arformhelper->get_default_opts();

        foreach ($defaults as $var => $default) {
            if ($var == 'notification') {
                $options[$var] = isset($posted_data[$var]) ? $posted_data[$var] : $default;
            } else {
                $options[$var] = isset($posted_data['options'][$var]) ? $posted_data['options'][$var] : $default;
            }
        }

        $options['arf_show_post_value'] = isset($posted_data['options']['arf_show_post_value']) ? $posted_data['options']['arf_show_post_value'] : 'no';

        $options['arf_post_value_url'] = isset($posted_data['options']['arf_post_value_url']) ? $posted_data['options']['arf_post_value_url'] : '';

        $options['arf_form_other_css'] = isset($values['options']['arf_form_other_css']) ? addslashes($values['options']['arf_form_other_css']) : '';

        $options['custom_style'] = isset($posted_data['options']['custom_style']) ? $posted_data['options']['custom_style'] : 0;

        $options['before_html'] = isset($posted_data['options']['before_html']) ? $posted_data['options']['before_html'] : $arformhelper->get_default_html('before');

        $options['after_html'] = isset($posted_data['options']['after_html']) ? $posted_data['options']['after_html'] : $arformhelper->get_default_html('after');

        $options = apply_filters('arfformoptionsbeforeupdateform', $options, $posted_data);

        $options['display_title_form'] = isset($posted_data['options']['display_title_form']) ? $posted_data['options']['display_title_form'] : 0;

        $double_optin = $options['arf_enable_double_optin'] = isset($posted_data['options']['arf_enable_double_optin']) ? $posted_data['options']['arf_enable_double_optin'] : 0;

        $options['email_to'] = isset($posted_data['reply_to']) ? $posted_data['reply_to'] : '';

        $options['arf_restrict_form_entries'] = isset($posted_data['options']['arf_restrict_form_entries']) ? $posted_data['options']['arf_restrict_form_entries'] : 0;

        $options['restrict_action'] = isset($posted_data['options']['restrict_action']) ? $posted_data['options']['restrict_action'] : '';

        $options['arf_restrict_max_entries'] = isset($posted_data['options']['arf_restrict_max_entries']) ? $posted_data['options']['arf_restrict_max_entries'] : 50;

        $options['arf_restrict_entries_before_specific_date'] = isset($posted_data['options']['arf_restrict_entries_before_specific_date']) ? date('Y-m-d',strtotime($posted_data['options']['arf_restrict_entries_before_specific_date'])) : '';

        $options['arf_restrict_entries_after_specific_date'] = isset($posted_data['options']['arf_restrict_entries_after_specific_date']) ? date('Y-m-d',strtotime($posted_data['options']['arf_restrict_entries_after_specific_date'])) : '';

        $options['arf_restrict_entries_start_date'] = isset($posted_data['options']['arf_restrict_entries_start_date']) ? date('Y-m-d',strtotime($posted_data['options']['arf_restrict_entries_start_date'])) : '';

        $options['arf_restrict_entries_end_date'] = isset($posted_data['options']['arf_restrict_entries_end_date']) ? date('Y-m-d',strtotime($posted_data['options']['arf_restrict_entries_end_date'])) : '';

        $options['arf_res_msg'] = isset($posted_data['options']['arf_res_msg']) ? $posted_data['options']['arf_res_msg'] : '';

        $options['arf_field_order'] = isset($posted_data['arf_field_order']) ? $posted_data['arf_field_order'] : json_encode(array());
        $options['arf_field_resize_width'] = isset($posted_data['arf_field_resize_width']) ? $posted_data['arf_field_resize_width'] : json_encode(array());
        $options['define_template'] = isset($posted_data['define_template']) ? $posted_data['define_template'] : 0;

        $options = apply_filters('arf_save_form_options_outside',$options,$posted_data,$form_id);

        $submitbtnid = "arfsubmit";
        if (isset($posted_data['conditional_logic_' . $submitbtnid]) and stripslashes_deep($posted_data['conditional_logic_' . $submitbtnid]) == '1') {
            $conditional_logic_display = stripslashes_deep($posted_data['conditional_logic_display_' . $submitbtnid]);
            $conditional_logic_if_cond = stripslashes_deep($posted_data['conditional_logic_if_cond_' . $submitbtnid]);
            $conditional_logic_rules = array();

            $rule_array = isset($posted_data['rule_array_' . $submitbtnid]) ? $posted_data['rule_array_' . $submitbtnid] : array();
            if (count($rule_array) > 0) {
                $i = 1;
                foreach ($rule_array as $v) {
                    $conditional_logic_field = stripslashes_deep($posted_data['arf_cl_field_' . $submitbtnid . '_' . $v]);
                    $conditional_logic_field_type = $arfieldhelper->get_field_type($conditional_logic_field);
                    $conditional_logic_op = stripslashes_deep($posted_data['arf_cl_op_' . $submitbtnid . '_' . $v]);
                    $conditional_logic_value = stripslashes_deep($posted_data['cl_rule_value_' . $submitbtnid . '_' . $v]);
                    $conditional_logic_rules[$i] = array(
                        'id' => $i,
                        'field_id' => $conditional_logic_field,
                        'field_type' => $conditional_logic_field_type,
                        'operator' => $conditional_logic_op,
                        'value' => $conditional_logic_value,
                    );
                    $i++;
                }
            }

            $conditional_logic = array(
                'enable' => 1,
                'display' => $conditional_logic_display,
                'if_cond' => $conditional_logic_if_cond,
                'rules' => $conditional_logic_rules,
            );

            $options['submit_conditional_logic'] = $conditional_logic;
        } else {
            $conditional_logic_display = isset($conditional_logic_display) ? $conditional_logic_display : 'show';
            $conditional_logic_if_cond = isset($conditional_logic_if_cond) ? $conditional_logic_if_cond : 'all';
            $conditional_logic_rules = isset($conditional_logic_rules) ? $conditional_logic_rules : array();
            $conditional_logic = array(
                'enable' => 0,
                'display' => $conditional_logic_display,
                'if_cond' => $conditional_logic_if_cond,
                'rules' => $conditional_logic_rules,
            );

            $options['submit_conditional_logic'] = $conditional_logic;
        }

        $form->autoresponder_fname = (isset($posted_data['autoresponder_fname']) && $posted_data['autoresponder_fname'] != '') ? $posted_data['autoresponder_fname'] : '';

        $form->autoresponder_lname = (isset($posted_data['autoresponder_lname']) && $posted_data['autoresponder_lname'] != '') ? $posted_data['autoresponder_lname'] : '';

        $form->autoresponder_email = (isset($posted_data['autoresponder_email']) && $posted_data['autoresponder_email']) ? $posted_data['autoresponder_email'] : '';

        $new_values['autoresponder_fname'] = $form->autoresponder_fname;
        $new_values['autoresponder_lname'] = $form->autoresponder_lname;
        $new_values['autoresponder_email'] = $form->autoresponder_email;

        $form_css = array();

        $form_css['display_title_form'] = isset($options['display_title_form']) ? $options['display_title_form'] : '';

        $form_css['arfmainformwidth'] = $_REQUEST['arffw'] = isset($posted_data['arffw']) ? $posted_data['arffw'] : '';        

        $form_css['form_width_unit'] = $_REQUEST['arffu'] = isset($posted_data['arffu']) ? $posted_data['arffu'] : '';

        $form_css['text_direction'] = $_REQUEST['arftds'] = isset($posted_data['arftds']) ? $posted_data['arftds'] : '';

        $form_css['form_align'] = $_REQUEST['arffa'] = isset($posted_data['arffa']) ? $posted_data['arffa'] : '';

        $form_css['arfmainfieldsetpadding'] = $_REQUEST['arfmfsp'] = isset($posted_data['arfmfsp']) ? $posted_data['arfmfsp'] : '';

        $form_css['form_border_shadow'] = $_REQUEST['arffbs'] = isset($posted_data['arffbs']) ? $posted_data['arffbs'] : '';

        $form_css['fieldset'] = $_REQUEST['arfmfis'] = isset($posted_data['arfmfis']) ? $posted_data['arfmfis'] : '';

        $form_css['arfmainfieldsetradius'] = $_REQUEST['arfmfsr'] = isset($posted_data['arfmfsr']) ? $posted_data['arfmfsr'] : '';

        $form_css['arfmainfieldsetcolor'] = $_REQUEST['arfmfsc'] = isset($posted_data['arfmfsc']) ? $posted_data['arfmfsc'] : '';

        $form_css['arfmainformbordershadowcolorsetting'] = $_REQUEST['arffboss'] = isset($posted_data['arffboss']) ? $posted_data['arffboss'] : '';

        $form_css['arfstandarderrposition'] = $_REQUEST['arfstndrerr'] = isset($posted_data['arfstndrerr']) ? $posted_data['arfstndrerr'] : 'relative';

        $form_css['arfmainformtitlecolorsetting'] = $_REQUEST['arfftc'] = isset($posted_data['arfftc']) ? $posted_data['arfftc'] : '';

        $form_css['check_weight_form_title'] = $_REQUEST['arfftws'] = isset($posted_data['arfftws']) ? $posted_data['arfftws'] : '';

        $form_css['form_title_font_size'] = $_REQUEST['arfftfss'] = isset($posted_data['arfftfss']) ? $posted_data['arfftfss'] : '';

        $form_css['arfmainformtitlepaddingsetting'] = $_REQUEST['arfftps'] = isset($posted_data['arfftps']) ? $posted_data['arfftps'] : '';

        $form_css['arfmainformbgcolorsetting'] = $_REQUEST['arffbcs'] = isset($posted_data['arffbcs']) ? $posted_data['arffbcs'] : '';

        $form_css['font'] = $_REQUEST['arfmfs'] = isset($posted_data['arfmfs']) ? $posted_data['arfmfs'] : '';

        $form_css['label_color'] = $_REQUEST['arflcs'] = isset($posted_data['arflcs']) ? $posted_data['arflcs'] : '';

        $form_css['weight'] = $_REQUEST['arfmfws'] = isset($posted_data['arfmfws']) ? $posted_data['arfmfws'] : '';

        $form_css['font_size'] = $_REQUEST['arffss'] = isset($posted_data['arffss']) ? $posted_data['arffss'] : '';

        $form_css['align'] = $_REQUEST['arffrma'] = isset($posted_data['arffrma']) ? $posted_data['arffrma'] : '';

        $form_css['position'] = $_REQUEST['arfmps'] = isset($posted_data['arfmps']) ? $posted_data['arfmps'] : '';

        $form_css['width'] = $_REQUEST['arfmws'] = isset($posted_data['arfmws']) ? $posted_data['arfmws'] : '';

        $form_css['width_unit'] = $_REQUEST['arfmwu'] = isset($posted_data['arfmwu']) ? $posted_data['arfmwu'] : '';

        $form_css['arfdescfontsizesetting'] = $_REQUEST['arfdfss'] = isset($posted_data['arfdfss']) ? $posted_data['arfdfss'] : '';

        $form_css['arfdescalighsetting'] = $_REQUEST['arfdas'] = isset($posted_data['arfdas']) ? $posted_data['arfdas'] : '';

        $form_css['hide_labels'] = $_REQUEST['arfhl'] = isset($posted_data['arfhl']) ? $posted_data['arfhl'] : '';

        $form_css['check_font'] = $_REQUEST['arfcbfs'] = isset($posted_data['arfcbfs']) ? $posted_data['arfcbfs'] : '';

        $form_css['check_weight'] = $_REQUEST['arfcbws'] = isset($posted_data['arfcbws']) ? $posted_data['arfcbws'] : "";

        $form_css['field_font_size'] = $_REQUEST['arfffss'] = isset($posted_data['arfffss']) ? $posted_data['arfffss'] : "";

        $form_css['text_color'] = $_REQUEST['arftcs'] = isset($posted_data['arftcs']) ? $posted_data['arftcs'] : "";

        $form_css['border_radius'] = $_REQUEST['arfmbs'] = isset($posted_data['arfmbs']) ? $posted_data['arfmbs'] : '';

        $form_css['border_color'] = $_REQUEST['arffmboc'] = isset($posted_data['arffmboc']) ? $posted_data['arffmboc'] : '';

        $form_css['arffieldborderwidthsetting'] = $_REQUEST['arffbws'] = isset($posted_data['arffbws']) ? $posted_data['arffbws'] : '';

        $form_css['arffieldborderstylesetting'] = $_REQUEST['arffbss'] = isset($posted_data['arffbss']) ? $posted_data['arffbss'] : '';

        if (isset($posted_data['arffiu']) and $posted_data['arffiu'] == '%' and isset($posted_data['arfmfiws']) and $posted_data['arfmfiws'] > '100') {
            $form_css['field_width'] = $_REQUEST['field_width'] = '100';
        } else {
            $form_css['field_width'] = $_REQUEST['arfmfiws'] = isset($posted_data['arfmfiws']) ? $posted_data['arfmfiws'] : '';
        }

        $form_css['field_width_unit'] = $_REQUEST['arffiu'] = isset($posted_data['arffiu']) ? $posted_data['arffiu'] : "";

        $form_css['arffieldmarginssetting'] = $_REQUEST['arffms'] = isset($posted_data['arffms']) ? $posted_data['arffms'] : '';

        $form_css['arffieldinnermarginssetting'] = $_REQUEST['arffims'] = isset($posted_data['arffims']) ? $posted_data['arffims'] : "";

        $form_css['bg_color'] = $_REQUEST['arffmbc'] = isset($posted_data['arffmbc']) ? $posted_data['arffmbc'] : '';

        $form_css['arfbgactivecolorsetting'] = $_REQUEST['arfbcas'] = isset($posted_data['arfbcas']) ? $posted_data['arfbcas'] : "";

        $form_css['arfborderactivecolorsetting'] = $_REQUEST['arfbacs'] = isset($posted_data['arfbacs']) ? $posted_data['arfbacs'] : "";

        $form_css['arferrorbgcolorsetting'] = $_REQUEST['arfbecs'] = isset($posted_data['arfbecs']) ? $posted_data['arfbecs'] : "";

        $form_css['arferrorbordercolorsetting'] = $_REQUEST['arfboecs'] = isset($posted_data['arfboecs']) ? $posted_data['arfboecs'] : '';

        $form_css['arfradioalignsetting'] = $_REQUEST['arfras'] = isset($posted_data['arfras']) ? $posted_data['arfras'] : "";

        $form_css['arfcheckboxalignsetting'] = $_REQUEST['arfcbas'] = isset($posted_data['arfcbas']) ? $posted_data['arfcbas'] : '';


        $form_css['auto_width'] = $_REQUEST['arfautowidthsetting'] = isset($posted_data['arfautowidthsetting']) ? $posted_data['arfautowidthsetting'] : '';

        $form_css['arfcalthemecss'] = $_REQUEST['arffthc'] = isset($posted_data['arffthc']) ? $posted_data['arffthc'] : "";

        $form_css['date_format'] = $_REQUEST['arffdaf'] = isset($posted_data['arffdaf']) ? $posted_data['arffdaf'] : '';
        
        $form_css['arfinputstyle'] = $_REQUEST['arfinpst'] = isset($posted_data['arfinpst']) ? $posted_data['arfinpst'] : '';

        $form_css['arfsubmitbuttontext'] = $_REQUEST['arfsubmitbuttontext'] = isset($posted_data['arfsubmitbuttontext']) ? $posted_data['arfsubmitbuttontext'] : '';

        $form_css['arfsubmitweightsetting'] = $_REQUEST['arfsbwes'] = isset($posted_data['arfsbwes']) ? $posted_data['arfsbwes'] : '';

        $form_css['arfsubmitbuttonfontsizesetting'] = $_REQUEST['arfsbfss'] = isset($posted_data['arfsbfss']) ? $posted_data['arfsbfss'] : '';

        $form_css['arfsubmitbuttonwidthsetting'] = $_REQUEST['arfsbws'] = isset($posted_data['arfsbws']) ? $posted_data['arfsbws'] : '';

        $form_css['arfsubmitbuttonheightsetting'] = $_REQUEST['arfsbhs'] = isset($posted_data['arfsbhs']) ? $posted_data['arfsbhs'] : '';
        $form_css['submit_bg_color'] = $_REQUEST['arfsbbcs'] = isset($posted_data['arfsbbcs']) ? $posted_data['arfsbbcs'] : "";

        $form_css['arfsubmitbuttonbgcolorhoversetting'] = $_REQUEST['arfsbchs'] = isset($posted_data['arfsbchs']) ? $posted_data['arfsbchs'] : '';

        $form_css['arfsubmitbgcolor2setting'] = $_REQUEST['arfsbcs'] = isset($posted_data['arfsbcs']) ? $posted_data['arfsbcs'] : '';

        $form_css['arfsubmittextcolorsetting'] = $_REQUEST['arfsbtcs'] = isset($posted_data['arfsbtcs']) ? $posted_data['arfsbtcs'] : '';

        $form_css['arfsubmitbordercolorsetting'] = $_REQUEST['arfsbobcs'] = isset($posted_data['arfsbobcs']) ? $posted_data['arfsbobcs'] : '';

        $form_css['arfsubmitborderwidthsetting'] = $_REQUEST['arfsbbws'] = isset($posted_data['arfsbbws']) ? $posted_data['arfsbbws'] : '';

        $form_css['arfsubmitborderradiussetting'] = $_REQUEST['arfsbbrs'] = isset($posted_data['arfsbbrs']) ? $posted_data['arfsbbrs'] : '';

        $form_css['arfsubmitshadowcolorsetting'] = $_REQUEST['arfsbscs'] = isset($posted_data['arfsbscs']) ? $posted_data['arfsbscs'] : '';

        $form_css['arfsubmitbuttonmarginsetting'] = $_REQUEST['arfsbms'] = isset($posted_data['arfsbms']) ? $posted_data['arfsbms'] : '';

        $form_css['submit_bg_img'] = $_REQUEST['arfsbis'] = isset($posted_data['arfsbis']) ? $posted_data['arfsbis'] : '';

        $form_css['submit_hover_bg_img'] = $_REQUEST['arfsbhis'] = isset($posted_data['arfsbhis']) ? $posted_data['arfsbhis'] : '';

        $form_css['error_font'] = $_REQUEST['arfmefs'] = isset($posted_data['arfmefs']) ? $posted_data['arfmefs'] : '';

        $form_css['error_font_other'] = $_REQUEST['arfmofs'] = isset($posted_data['arfmofs']) ? $posted_data['arfmofs'] : '';

        $form_css['arffontsizesetting'] = $_REQUEST['arfmefss'] = isset($posted_data['arfmefss']) ? $posted_data['arfmefss'] : '';

        $form_css['arferrorbgsetting'] = $_REQUEST['arfmebs'] = isset($posted_data['arfmebs']) ? $posted_data['arfmebs'] : '';

        $form_css['arferrortextsetting'] = $_REQUEST['arfmets'] = isset($posted_data['arfmets']) ? $posted_data['arfmets'] : '';

        $form_css['arferrorbordersetting'] = $_REQUEST['arfmebos'] = isset($posted_data['arfmebos']) ? $posted_data['arfmebos'] : '';

        $form_css['arfsucessbgcolorsetting'] = $_REQUEST['arfmsbcs'] = isset($posted_data['arfmsbcs']) ? $posted_data['arfmsbcs'] : '';

        $form_css['arfsucessbordercolorsetting'] = $_REQUEST['arfmsbocs'] = isset($posted_data['arfmsbocs']) ? $posted_data['arfmsbocs'] : "";

        $form_css['arfsucesstextcolorsetting'] = $_REQUEST['arfmstcs'] = isset($posted_data['arfmstcs']) ? $posted_data['arfmstcs'] : '';

        $form_css['arfformerrorbgcolorsettings'] = $_REQUEST['arffebgc'] =  isset($posted_data['arffebgc']) ? $posted_data['arffebgc'] : '';

        $form_css['arfformerrorbordercolorsettings'] = $_REQUEST['arffebrdc'] = isset($posted_data['arffebrdc']) ? $posted_data['arffebrdc'] : '';

        $form_css['arfformerrortextcolorsettings'] = $_REQUEST['arffetxtc'] = isset($posted_data['arffetxtc']) ? $posted_data['arffetxtc'] : '';        

        $form_css['arfsubmitalignsetting'] = $_REQUEST['arfmsas'] = isset($posted_data['arfmsas']) ? $posted_data['arfmsas'] : '';

        $form_css['checkbox_radio_style'] = $_REQUEST['arfcrs'] = isset($posted_data['arfcrs']) ? $posted_data['arfcrs'] : '';

        $form_css['bg_color_pg_break'] = $_REQUEST['arffbcpb'] = isset($posted_data['arffbcpb']) ? $posted_data['arffbcpb'] : '';

        $form_css['arfcommonfont'] = $_REQUEST['arfcommonfont'] = isset($posted_data['arfcommonfont']) ? $posted_data['arfcommonfont'] : "Helvetica";

        $form_css['bg_inavtive_color_pg_break'] = $_REQUEST['arfbicpb'] = isset($posted_data['arfbicpb']) ? $posted_data['arfbicpb'] : "";

        $form_css['text_color_pg_break'] = $_REQUEST['arfftcpb'] = isset($posted_data['arfftcpb']) ? $posted_data['arfftcpb'] : "";

        $form_css['arfmainform_bg_img'] = $_REQUEST['arfmfbi'] = isset($posted_data['arfmfbi']) ? $posted_data['arfmfbi'] : '';

        $form_css['arfmainform_color_skin'] = $_REQUEST['arfmcs'] = isset($posted_data['arfmcs']) ? $posted_data['arfmcs'] : '';

        $form_css['arfsubmitfontfamily'] = $_REQUEST['arfsff'] = isset($posted_data['arfsff']) ? $posted_data['arfsff'] : '';

        $form_css['arfvalidationbgcolorsetting'] = $_REQUEST['arfmvbcs'] = isset($posted_data['arfmvbcs']) ? $posted_data['arfmvbcs'] : '';
        
        $form_css['arfvalidationtextcolorsetting'] = $_REQUEST['arfmvtcs'] = isset($posted_data['arfmvtcs']) ? $posted_data['arfmvtcs'] : '';
        
        $form_css['arfdatepickerbgcolorsetting'] = $_REQUEST['arfdbcs'] = isset($posted_data['arfdbcs']) ? $posted_data['arfdbcs'] : '';
        
        $form_css['arfdatepickertextcolorsetting'] = $_REQUEST['arfdtcs'] = isset($posted_data['arfdtcs']) ? $posted_data['arfdtcs'] : '';
        
        $form_css['arfmainfieldsetpadding_1'] = $_REQUEST['arfmainfieldsetpadding_1'] = isset($posted_data['arfmainfieldsetpadding_1']) ? $posted_data['arfmainfieldsetpadding_1'] : "";
        $form_css['arfmainfieldsetpadding_2'] = $_REQUEST['arfmainfieldsetpadding_2'] = isset($posted_data['arfmainfieldsetpadding_2']) ? $posted_data['arfmainfieldsetpadding_2'] : '';
        $form_css['arfmainfieldsetpadding_3'] = $_REQUEST['arfmainfieldsetpadding_3'] = isset($posted_data['arfmainfieldsetpadding_3']) ? $posted_data['arfmainfieldsetpadding_3'] : '';
        $form_css['arfmainfieldsetpadding_4'] = $_REQUEST['arfmainfieldsetpadding_4'] = isset($posted_data['arfmainfieldsetpadding_4']) ? $posted_data['arfmainfieldsetpadding_4'] : '';
        $form_css['arfmainformtitlepaddingsetting_1'] = $_REQUEST['arfformtitlepaddingsetting_1'] = isset($posted_data['arfformtitlepaddingsetting_1']) ? $posted_data['arfformtitlepaddingsetting_1'] : '';
        $form_css['arfmainformtitlepaddingsetting_2'] = $_REQUEST['arfformtitlepaddingsetting_2'] = isset($posted_data['arfformtitlepaddingsetting_2']) ? $posted_data['arfformtitlepaddingsetting_2'] : "";
        $form_css['arfmainformtitlepaddingsetting_3'] = $_REQUEST['arfformtitlepaddingsetting_3'] = isset($posted_data['arfformtitlepaddingsetting_3']) ? $posted_data['arfformtitlepaddingsetting_3'] : '';
        $form_css['arfmainformtitlepaddingsetting_4'] = $_REQUEST['arfformtitlepaddingsetting_4'] = isset($posted_data['arfformtitlepaddingsetting_4']) ? $posted_data['arfformtitlepaddingsetting_4'] : "";
        $form_css['arffieldinnermarginssetting_1'] = $_REQUEST['arffieldinnermarginsetting_1'] = isset($posted_data['arffieldinnermarginsetting_1']) ? $posted_data['arffieldinnermarginsetting_1'] : '';
        $form_css['arffieldinnermarginssetting_2'] = $_REQUEST['arffieldinnermarginsetting_2'] = isset($posted_data['arffieldinnermarginsetting_2']) ? $posted_data['arffieldinnermarginsetting_2'] : '';
        $form_css['arffieldinnermarginssetting_3'] = $_REQUEST['arffieldinnermarginsetting_3'] = isset($posted_data['arffieldinnermarginsetting_3']) ? $posted_data['arffieldinnermarginsetting_3'] : '';
        $form_css['arffieldinnermarginssetting_4'] = $_REQUEST['arffieldinnermarginsetting_4'] = isset($posted_data['arffieldinnermarginsetting_4']) ? $posted_data['arffieldinnermarginsetting_4'] : "";
        $form_css['arfsubmitbuttonmarginsetting_1'] = $_REQUEST['arfsubmitbuttonmarginsetting_1'] = isset($posted_data['arfsubmitbuttonmarginsetting_1']) ? $posted_data['arfsubmitbuttonmarginsetting_1'] : '';
        $form_css['arfsubmitbuttonmarginsetting_2'] = $_REQUEST['arfsubmitbuttonmarginsetting_2'] = isset($posted_data['arfsubmitbuttonmarginsetting_2']) ? $posted_data['arfsubmitbuttonmarginsetting_2'] : '';
        $form_css['arfsubmitbuttonmarginsetting_3'] = $_REQUEST['arfsubmitbuttonmarginsetting_3'] = isset($posted_data['arfsubmitbuttonmarginsetting_3']) ? $posted_data['arfsubmitbuttonmarginsetting_3'] : '';
        $form_css['arfsubmitbuttonmarginsetting_4'] = $_REQUEST['arfsubmitbuttonmarginsetting_4'] = isset($posted_data['arfsubmitbuttonmarginsetting_4']) ? $posted_data['arfsubmitbuttonmarginsetting_4'] : '';
        $form_css['arfsectionpaddingsetting_1'] = $_REQUEST['arfsectionpaddingsetting_1'] = isset($posted_data['arfsectionpaddingsetting_1']) ? $posted_data['arfsectionpaddingsetting_1'] : '';
        $form_css['arfsectionpaddingsetting_2'] = $_REQUEST['arfsectionpaddingsetting_2'] = isset($posted_data['arfsectionpaddingsetting_2']) ? $posted_data['arfsectionpaddingsetting_2'] : '';
        $form_css['arfsectionpaddingsetting_3'] = $_REQUEST['arfsectionpaddingsetting_3'] = isset($posted_data['arfsectionpaddingsetting_3']) ? $posted_data['arfsectionpaddingsetting_3'] : '';
        $form_css['arfsectionpaddingsetting_4'] = $_REQUEST['arfsectionpaddingsetting_4'] = isset($posted_data['arfsectionpaddingsetting_4']) ? $posted_data['arfsectionpaddingsetting_4'] : "";
        $form_css['arfcheckradiostyle'] = $_REQUEST['arfcksn'] = isset($posted_data['arfcksn']) ? $posted_data['arfcksn'] : '';
        $form_css['arfcheckradiocolor'] = $_REQUEST['arfcksc'] = isset($posted_data['arfcksc']) ? $posted_data['arfcksc'] : '';
        $form_css['arf_checked_checkbox_icon'] = $_REQUEST['arf_checkbox_icon'] = isset($posted_data['arf_checkbox_icon']) ? $posted_data['arf_checkbox_icon'] : '';
        $form_css['enable_arf_checkbox'] = $_REQUEST['enable_arf_checkbox'] = isset($posted_data['enable_arf_checkbox']) ? $posted_data['enable_arf_checkbox'] : "";
        $form_css['arf_checked_radio_icon'] = $_REQUEST['arf_radio_icon'] = isset($posted_data['arf_radio_icon']) ? $posted_data['arf_radio_icon'] : '';
        $form_css['enable_arf_radio'] = $_REQUEST['enable_arf_radio'] = isset($posted_data['enable_arf_radio']) ? $posted_data['enable_arf_radio'] : '';
        $form_css['checked_checkbox_icon_color'] = $_REQUEST['cbscol'] = isset($posted_data['cbscol']) ? $posted_data['cbscol'] : "";
        $form_css['checked_radio_icon_color'] = $_REQUEST['rbscol'] = isset($posted_data['rbscol']) ? $posted_data['rbscol'] : '';

        $form_css['arferrorstyle'] = $_REQUEST['arfest'] = isset($posted_data['arfest']) ? $posted_data['arfest'] : '';
        $form_css['arferrorstylecolor'] = $_REQUEST['arfestc'] = isset($posted_data['arfestc']) ? $posted_data['arfestc'] : '';
        $form_css['arferrorstylecolor2'] = $_REQUEST['arfestc2'] = isset($posted_data['arfestc2']) ? $posted_data['arfestc2'] : '';
        $form_css['arferrorstyleposition'] = $_REQUEST['arfestbc'] = isset($posted_data['arfestbc']) ? $posted_data['arfestbc'] : '';

        $form_css['arfformtitlealign'] = $_REQUEST['arffta'] = isset($posted_data['arffta']) ? $posted_data['arffta'] : '';
        $form_css['arfsubmitautowidth'] = $_REQUEST['arfsbaw'] = isset($posted_data['arfsbaw']) ? $posted_data['arfsbaw'] : '';

        $form_css['arftitlefontfamily'] = $_REQUEST['arftff'] = isset($posted_data['arftff']) ? $posted_data['arftff'] : '';

        $form_css['bar_color_survey'] = $_REQUEST['arfbcs'] = isset($posted_data['arfbcs']) ? $posted_data['arfbcs'] : '';
        $form_css['bg_color_survey'] = $_REQUEST['arfbgcs'] = isset($posted_data['arfbgcs']) ? $posted_data['arfbgcs'] : "";
        $form_css['text_color_survey'] = $_REQUEST['arfftcs'] = isset($posted_data['arfftcs']) ? $posted_data['arfftcs'] : '';

        $form_css['arfsectionpaddingsetting'] = $_REQUEST['arfscps'] = isset($posted_data['arfscps']) ? $posted_data['arfscps'] : '';

        if (isset($posted_data['arfmainform_opacity']) and $posted_data['arfmainform_opacity'] > 1) {
            $form_css['arfmainform_opacity'] = $_REQUEST['arfmainform_opacity'] = '1';
        } else {
            $form_css['arfmainform_opacity'] = $_REQUEST['arfmainform_opacity'] = isset($posted_data['arfmainform_opacity']) ? $posted_data['arfmainform_opacity'] : '';
        }

        if (isset($posted_data['arfplaceholder_opacity']) and $posted_data['arfplaceholder_opacity'] > 1) {
            $form_css['arfplaceholder_opacity'] = $_REQUEST['arfplaceholder_opacity'] = '1';
        } else {
            $form_css['arfplaceholder_opacity'] = $_REQUEST['arfplaceholder_opacity'] = isset($posted_data['arfplaceholder_opacity']) ? $posted_data['arfplaceholder_opacity'] : '0.5';
        }

        $form_css['arfmainfield_opacity'] = $_REQUEST['arfmfo'] = isset($posted_data['arfmfo']) ? $posted_data['arfmfo'] : "";

        if( $form_css['arfinputstyle'] == 'material' ){
            $form_css['arfmainfield_opacity'] = 1;
        }

        $form_css['prefix_suffix_bg_color'] = $_REQUEST['pfsfsbg'] = isset($posted_data['pfsfsbg']) ? $posted_data['pfsfsbg'] : '';
        $form_css['prefix_suffix_icon_color'] = $_REQUEST['pfsfscol'] = isset($posted_data['pfsfscol']) ? $posted_data['pfsfscol'] : "";

        $form_css['arf_tooltip_bg_color'] = $_REQUEST['arf_tooltip_bg_color'] = isset($posted_data['arf_tooltip_bg_color']) ? $posted_data['arf_tooltip_bg_color'] : "";
        $form_css['arf_tooltip_font_color'] = $_REQUEST['arf_tooltip_font_color'] = isset($posted_data['arf_tooltip_font_color']) ? $posted_data['arf_tooltip_font_color'] : "";
        $form_css['arf_tooltip_width'] = $_REQUEST['arf_tooltip_width'] = isset($posted_data['arf_tooltip_width']) ? $posted_data['arf_tooltip_width'] : "";
        $form_css['arf_tooltip_position'] = $_REQUEST['arf_tooltip_position'] = isset($posted_data['arf_tooltip_position']) ? $posted_data['arf_tooltip_position'] : "";
        $form_css['arfsectiontitlefamily'] = $_REQUEST['arfsectiontitlefamily'] = isset($posted_data['arfsectiontitlefamily']) ? $posted_data['arfsectiontitlefamily'] : "Helvetica";
        $form_css['arfsectiontitlefontsizesetting'] = $_REQUEST['arfsectiontitlefontsizesetting'] = isset($posted_data['arfsectiontitlefontsizesetting']) ? $posted_data['arfsectiontitlefontsizesetting'] : "16";
        $form_css['arfsectiontitleweightsetting'] = $_REQUEST['arfsectiontitleweightsetting'] = isset($posted_data['arfsectiontitleweightsetting']) ? $posted_data['arfsectiontitleweightsetting'] : "";
        
        $form_css['arfsubmitbuttonstyle'] = $_REQUEST['arfsubmitbuttonstyle'] = isset($posted_data['arfsubmitbuttonstyle']) ? $posted_data['arfsubmitbuttonstyle'] : "border"; 

        $form_css['arfuploadbtntxtcolorsetting']=$_REQUEST['arfupbg']=isset($posted_data['arfupbg'])? $posted_data['arfupbg']: '#077BDD';
        
        $form_css['arfuploadbtnbgcolorsetting']=$_REQUEST['arfuptxt']=isset($posted_data['arfuptxt'])? $posted_data['arfuptxt']:'#ffffff';
        
        $form_css['arf_req_indicator'] = $_REQUEST['arfrinc'] = isset($posted_data['arfrinc']) ? $posted_data['arfrinc'] : "0";

        $form_css['arf_divider_inherit_bg'] = $_REQUEST['arf_divider_inherit_bg'] = isset($posted_data['arf_divider_inherit_bg']) ? $posted_data['arf_divider_inherit_bg'] : 0;
        $form_css['arfformsectionbackgroundcolor'] = $_REQUEST['arfsecbg'] = isset($posted_data['arfsecbg']) ? $posted_data['arfsecbg'] : '';
        $form_css['arfmainbasecolor'] = $_REQUEST['arfmbsc'] = isset($posted_data['arfmbsc']) ? $posted_data['arfmbsc'] : '';

        $form_css['arflikebtncolor'] = $_REQUEST['albclr'] =  isset($posted_data['albclr']) ? $posted_data['albclr'] : '';
        $form_css['arfdislikebtncolor'] = $_REQUEST['adlbclr'] = isset($posted_data['adlbclr']) ? $posted_data['adlbclr'] : '';

        $form_css['arfstarratingcolor'] = $_REQUEST['asclcl'] = isset($posted_data['asclcl']) ? $posted_data['asclcl'] : '';

        $form_css['arfsliderselectioncolor'] = $_REQUEST['asldrsl'] = isset($posted_data['asldrsl']) ? $posted_data['asldrsl'] : '';
        $form_css['arfslidertrackcolor'] = $_REQUEST['asltrcl'] = isset($posted_data['asltrcl']) ? $posted_data['asltrcl'] : '';

        $form_css['arf_bg_position_x'] = $_REQUEST['arf_bg_position_x'] = (isset($posted_data['arf_bg_position_x']) && $posted_data['arf_bg_position_x'] != '') ? $posted_data['arf_bg_position_x'] : "left";
        $form_css['arf_bg_position_y'] = $_REQUEST['arf_bg_position_y'] = (isset($posted_data['arf_bg_position_y']) && $posted_data['arf_bg_position_y'] != '') ? $posted_data['arf_bg_position_y'] : "top";
    
        $form_css['arf_bg_position_input_x'] = $_REQUEST['arf_bg_position_input_x'] = (isset($posted_data['arf_bg_position_input_x']) && $posted_data['arf_bg_position_input_x'] != '') ? $posted_data['arf_bg_position_input_x'] : "";
        $form_css['arf_bg_position_input_y'] = $_REQUEST['arf_bg_position_input_y'] = (isset($posted_data['arf_bg_position_input_y']) && $posted_data['arf_bg_position_input_y'] != '') ? $posted_data['arf_bg_position_input_y'] : "";
        
        $form->form_css = maybe_serialize($form_css);

        if (is_array($form->form_css)) {
            if ($form->form_css['arfsubmitbuttontext'] != '') {
                $submit = $form->form_css['arfsubmitbuttontext'];
            } else {
                $submit = $arfsettings->submit_value;
            }
        } else {
            $submit = $arfsettings->submit_value;
        }

        $fields = array();

        global $arf_loaded_fields;
        $arf_loaded_fields = $arf_all_preview_fields = array();


        $total_page_break = 0;
        $page_break = array();
        $arf_page_break_survey = 0;
        $arf_page_break_wizard = 0;
        $arf_page_break_possition_top = 0;
        $arf_page_break_possition_bottom = 0;
        $arf_hide_bar_belt = 0;
        $loaded_field = array();
        $is_font_awesome = 0;
        $is_tooltip = 0;
        $is_input_mask = 0;
        $normal_color_picker = 0;
        $advance_color_picker = 0;
        $arfdatepickerloaded = 0;
        $animate_number = 0;
        $round_total_number=0;
        $html_running_total_field_array = array();
        $google_captcha_loaded = 0;
        $arf_autocomplete_loaded = 0;
        $is_imagecontrol_field = 0;
        $default_value_field_array = apply_filters('arf_default_value_array_field_type',array('scale', 'checkbox', 'radio', 'like'));
        $default_value_from_itemmeta = apply_filters('arf_default_value_array_field_type_from_itemmeta',array('select', 'colorpicker', 'hidden'));
        foreach ($posted_data as $key => $post_data) {
            if (preg_match('/(arf_field_data_)/', $key)) {
                $name_array = explode('arf_field_data_', $key);
                $field_id = $name_array[1];
                $field_data = $arf_all_fields_data = new stdClass();

                             
                
                $post_data = json_decode($post_data,true);

                $default_value = "";

                if(in_array($post_data["type"],$default_value_field_array)){
                    $default_value = isset($post_data['default_value']) ? $post_data['default_value'] : ''; 
                } else if( in_array($post_data['type'],$default_value_from_itemmeta)){
                    $default_value = isset($posted_data['item_meta'][$field_id]) ? $posted_data['item_meta'][$field_id] : '';
                } else if( isset($post_data['default_value']) && $post_data['default_value'] != '' ){
                    $default_value = isset($post_data['default_value']) ? $post_data['default_value'] : '';
                }

                
                $clear_on_focus = isset($post_data['frm_clear_field']) ? $post_data['frm_clear_field'] : 0;
                $default_blank = isset($post_data['frm_default_blank']) ? $post_data['frm_default_blank'] : 0;
                
                $post_data['clear_on_focus'] = $clear_on_focus;
                $post_data['default_blank'] = $default_blank;
                $post_data['default_value'] = $default_value;
                
                
                
                $new_temp_value = json_encode($post_data);
                
                $post_data = $new_temp_value;
                
                $field_data_array = json_decode($post_data, true);
                
                $field_data->field_id = $arf_all_fields_data->field_id = $field_id;

                $field_data->id = $arf_all_fields_data->id = $field_id;

                $field_key = $armainhelper->get_unique_key('', $MdlDb->fields, 'field_key');

                $field_data->field_key = $arf_all_fields_data->field_key = $field_key;

                array_push($loaded_field, $field_data_array['type']);

                $field_data->name = $arf_all_fields_data->name = isset($field_data_array['name']) ? $field_data_array['name'] : '';
                
                $field_data->description = $arf_all_fields_data->description = isset($field_data_array['description']) ? $field_data_array['description'] : '';

                $field_data->type = $arf_all_fields_data->type = $field_data_array['type'];

                if ($field_data->type == 'break') {
                    $total_page_break++;
                    $page_break[] = $field_id;

                    if (isset($field_data_array['page_break_type']) && $field_data_array['page_break_type'] == 'survey') {
                        $arf_page_break_survey = 1;

                    }

                    if (isset($field_data_array['page_break_type']) && $field_data_array['page_break_type'] == 'wizard') {
                        $arf_page_break_wizard = 1;
                    }

                    if (isset($field_data_array['page_break_type_possition']) && $field_data_array['page_break_type_possition'] == 'top') {
                        $arf_page_break_possition_top = 1;
                    }

                    if (isset($field_data_array['page_break_type_possition']) && $field_data_array['page_break_type_possition'] == 'bottom') {
                        $arf_page_break_possition_bottom = 1;
                    }
                    
                    if (isset($field_data_array['pagebreaktabsbar']) && $field_data_array['pagebreaktabsbar'] == 1) {
                        $arf_hide_bar_belt = 1;
                    }
                }
                
                if ((isset($field_data_array['enable_arf_prefix']) && $field_data_array['enable_arf_prefix'] == 1) || (isset($field_data_array['enable_arf_suffix']) && $field_data_array['enable_arf_suffix'] == 1 ) || (isset($form_css['arfcheckradiostyle']) && $form_css['arfcheckradiostyle'] == 'custom') || $field_data->type == 'arf_smiley' || $field_data->type == 'scale') {
                    $is_font_awesome = 1;
                }

                if ($field_data->type == 'phone' && ( isset($field_data_array['phone_validation']) && $field_data_array['phone_validation'] != 'international')) {
                    $is_input_mask = 1;
                }

                if ($field_data->type == 'colorpicker' && (isset($field_data_array['colorpicker_type']) && $field_data_array['colorpicker_type'] == 'basic' )) {
                    $normal_color_picker = 1;
                }

                if ($field_data->type == 'colorpicker' && (isset($field_data_array['colorpicker_type']) && $field_data_array['colorpicker_type'] == 'advanced')) {
                    $advance_color_picker = 1;
                    $is_font_awesome = 1;
                }                
                if ($field_data->type == 'html' && (isset($field_data_array['enable_total']) && $field_data_array['enable_total'] == 1)) {                    
                    $animate_number = 1;
                    $html_running_total_field_array[] = $field_data->id;
                }
                if ($field_data->type == 'html' && (isset($field_data_array['enable_total']) && $field_data_array['enable_total'] == 1) && (isset($field_data_array['round_total']) && $field_data_array['round_total'] == 1)) {
                    $round_total_number = 1;
                }

                if ($field_data->type == 'captcha') {
                    $google_captcha_loaded = 1;
                }

                if ($field_data->type == 'arf_autocomplete') {
                    $arf_autocomplete_loaded = 1;
                }

                if ($field_data->type == 'imagecontrol') {
                    $is_imagecontrol_field = 1;
                }

                if (isset($field_data_array['tooltip_text']) && $field_data_array['tooltip_text'] != "") {
                    $is_tooltip = 1;
                }

                $field_data->default_value = $arf_all_fields_data->default_value = isset($field_data_array['default_value']) ? $field_data_array['default_value'] : '';

                $field_data->options = $arf_all_fields_data->options = isset($field_data_array['options']) ? $field_data_array['options'] : '';

                $field_data->required = $arf_all_fields_data->required = isset($field_data_array['required']) ? $field_data_array['required'] : 0;
                foreach ($field_data_array as $k => $v) {
                    $field_data->$k = $v;
                }
                $field_data->field_options = $field_data_array;

                $arf_all_fields_data->field_options = $post_data;

                $field_data->form_id = $arf_all_fields_data->form_id = $form_id;

                $field_data->conditional_logic = $arf_all_fields_data->conditional_logic = isset($field_data_array['conditional_logic']) ? $field_data_array['conditional_logic'] : '';


                $field_data->option_order = $arf_all_fields_data->option_order = isset($field_data_array['option_order']) ? $field_data_array['option_order'] : '';

                $field_data->form_name = $arf_all_fields_data->form_name = $posted_data['name'];

                $fields[] = $field_data;

                $arf_all_preview_fields[] = $arf_all_fields_data;

                if (isset($field_data_array['arf_tooltip']) && $field_data_array['arf_tooltip'] == 1) {
                    $is_tooltip = 1;
                }

                if($field_data->type=='email')
                {
                    if($field_data_array['confirm_email']=='1')
                    {
                        $email_field_key = $armainhelper->get_unique_key('', $MdlDb->fields, 'field_key');
                        $confirm_field_order_arr = json_decode($options['arf_field_order'],true);
                        $confirm_field_order = $confirm_field_order_arr[$field_id.'_confirm'];
                        
                        $arf_temp_fields['confirm_email_'.$field_id] = array( 'key' => $email_field_key, 'order' => $confirm_field_order, 'parent_field_id' => $field_id, 'confirm_inner_class' => $field_data_array['confirm_email_inner_classes']);
                    }
                }
                if($field_data->type=='password')
                {
                    if($field_data_array['confirm_password']=='1')
                    {
                        $password_field_key = $armainhelper->get_unique_key('', $MdlDb->fields, 'field_key');
                        $confirm_field_order_arr = json_decode($options['arf_field_order'],true);
                        $confirm_field_order = $confirm_field_order_arr[$field_id.'_confirm'];
                        
                        $arf_temp_fields['confirm_password_'.$field_id] = array( 'key' => $password_field_key, 'order' => $confirm_field_order, 'parent_field_id' => $field_id, 'confirm_inner_class' => $field_data_array['confirm_password_inner_classes']);
                    }
                }
            }
        }

        $form->temp_fields = maybe_serialize($arf_temp_fields);

        $options['arf_loaded_field'] = $arf_loaded_fields = $loaded_field;
        $options['total_page_break'] = $total_page_break;
        $options['page_break_field'] = $page_break;
        $options['tooltip_loaded'] = $is_tooltip;
        $options['font_awesome_loaded'] = $is_font_awesome;
        $options['tooltip_loaded'] = $is_tooltip;
        $options['arf_input_mask'] = $is_input_mask;
        $options['arf_normal_colorpicker'] = $normal_color_picker;
        $options['arf_advance_colorpicker'] = $advance_color_picker;
        $options['arf_number_animation'] = $animate_number;
        $options['arf_number_round'] = $round_total_number;
        $options['arf_page_break_survey'] = $arf_page_break_survey;
        $options['arf_page_break_wizard'] = $arf_page_break_wizard;
        $options['arf_page_break_possition_top'] = $arf_page_break_possition_top;
        $options['arf_page_break_possition_bottom'] = $arf_page_break_possition_bottom;
        $options['arf_hide_bar_belt'] = $arf_hide_bar_belt;
        $options['html_running_total_field_array'] = $html_running_total_field_array;
        $options['arf_autocomplete_loaded'] = $arf_autocomplete_loaded;
        $options['google_captcha_loaded'] = $google_captcha_loaded;
        $options['is_imagecontrol_field'] = $is_imagecontrol_field;

        $form->options = $options;

        foreach (array_merge($form_css) as $k => $frm_css) {
            $new_values[$k] = $frm_css;
        }
        $arf_all_preview_fields = $fields;
        
        foreach( $fields as $key => $value){
            $value->field_options = json_decode($value->field_options,true);
            $fields[$key] = $value;
        }
        
        $values = $arrecordhelper->setup_new_vars($fields, $form);

        $page_num = isset($options['total_page_break']) ? $options['total_page_break'] : 0;

        $params = $arrecordcontroller->get_recordparams($form);

        $arf_form .= $arformcontroller->arf_get_form_style_for_preview($form, $posted_data['id'], $fields, $arf_data_uniq_id);

        if ($page_num > 0) {
            $temp_calss = 'arfpagebreakform';
        } else {
            $temp_calss = '';
        }

        $form_attr = '';

        $arfssl = (is_ssl()) ? 1 : 0;
        $saving = true;

        $arf_form .= "<style type='text/css'>";

        $arfssl = (is_ssl()) ? 1 : 0;
        $preview = true;
        $inputStyle = isset($form->form_css['arfinputstyle']) ? $form->form_css['arfinputstyle'] : 'standard';
        if( $inputStyle == 'material' ){
            $filename = FORMPATH . '/core/css_create_materialize.php';
            
            ob_start();
            
            include $filename;
            
            $css = ob_get_contents();
            
            $css = str_replace('##','#', $css);
            
            $arf_form .= $css;
            
            ob_end_clean();
        } else {
            $filename = FORMPATH . '/core/css_create_main.php';
        
            ob_start();

            include $filename;

            $css = ob_get_contents();

            $css = str_replace('##', '#', $css);

            $arf_form .= $css;

                ob_end_clean();
        }

        $arf_form .= "</style>";
        if( $inputStyle == 'material' ){
            $arf_form .= "<link rel='stylesheet' type='text/css' href='".ARFURL."/materialize/materialize.css' />";
            $arf_form .= "<script type='text/javascript' data-cfasync='false' src='".ARFURL."/materialize/materialize.js'></script>";
        }
        $arf_form .= '<div class="arf_form ar_main_div_' . $form->id . ' arf_form_outer_wrapper" id="arffrm_' . $form->id . '_container">';
        $saved_message = isset($form->options['success_msg']) ? '<div id="arf_message_success"><div class="msg-detail"><div class="msg-description-success">' . $form->options['success_msg'] . '</div></div></div>' : $arfsettings->success_msg;
        $arf_form .= '<div id="form_success_' . $form->id . '" style="display:none;">' . $saved_message . '</div>';
        $arf_form .= '<form enctype="' . apply_filters('arfformenctype', 'multipart/form-data', $form) . '" method="post" class="arfshowmainform arfpreivewform ' . $temp_calss . ' ' . do_action('arfformclasses', $form) . ' " data-form-id="form_' . $form->form_key . '" novalidate="" data-id="' . $arf_data_uniq_id . '" data-popup-id="' . $arf_popup_data_uniq_id . '" "' . $form_attr . '">';

        $arf_form .= $arformcontroller->arf_get_form_hidden_field($form, $fields, $values, true, false, $arf_data_uniq_id, 'preview', $loaded_field, '', '');

        $arf_form .= $arfieldhelper->get_form_pagebreak_fields($form->id,$form->form_key,$values);

        $arf_form .='<div class="allfields">';
		$totalpass = 0;
        if (count(array_intersect(array('imagecontrol', 'password', 'email'), $loaded_field))) {

            foreach ($values['fields'] as $arrkey => $field) {
                
                if ($field['type'] == 'imagecontrol') {
                    $arf_form .= $arformcontroller->arf_front_display_image_field($field);
                }

                /** for confirm email and confirm password arf_dev_flag ( query and loop) */
                $field['id'] = $arfieldhelper->get_actual_id($field['id']);
                if ($field['type'] == 'password' && $field['confirm_password']) {
                    if (isset($field['confirm_password']) and $field['confirm_password'] == 1 and isset($arf_load_password['confrim_pass_field']) and $arf_load_password['confrim_pass_field'] == $field['id']) {
                        $values['confirm_password_arr'][$field['id']] = isset($field['confirm_password_field']) ? $field['confirm_password_field'] : "";
                    } else {
                        $arf_load_password['confrim_pass_field'] = isset($field['confirm_password_field']) ? $field['confirm_password_field'] : "";
                    }
                    $confirm_password_field = $arfieldhelper->get_confirm_password_field($field);
                    $values['fields'] = $arfieldhelper->array_push_after($values['fields'], array($confirm_password_field), $arrkey + $totalpass);
                    $totalpass++;
                }

                if ($field['type'] == 'email' && $field['confirm_email']) {
                    if (isset($field['confirm_email']) and $field['confirm_email'] == 1 and isset($arf_load_confirm_email['confrim_email_field']) and $arf_load_confirm_email['confrim_email_field'] == $field['id']) {
                        $values['confirm_email_arr'][$field['id']] = isset($field['confirm_email_field']) ? $field['confirm_email_field'] : "";
                    } else {
                        $arf_load_confirm_email['confrim_email_field'] = isset($field['confirm_email_field']) ? $field['confirm_email_field'] : "";
                    }
                    $confirm_email_field = $arfieldhelper->get_confirm_email_field($field);
                    $values['fields'] = $arfieldhelper->array_push_after($values['fields'], array($confirm_email_field), $arrkey + $totalpass);
                    $totalpass++;
                }
            }
        }


        
        $inputStyle = isset($form->form_css['arfinputstyle']) ? $form->form_css['arfinputstyle'] : 'standard';
        $form_class = ($inputStyle == 'material') ? 'arf_materialize_form' : 'arf_' . $inputStyle . '_form';
        
        $arf_form .= '<div class="arf_fieldset ' . $form_class . ' " id="arf_fieldset_' . $arf_data_uniq_id . '">';

        if (isset($form->options['display_title_form']) && $form->options['display_title_form'] == 1) {

            $arf_form .='<div class="arftitlecontainer">';

            if (isset($form->name) && $form->name != '') {
                $arf_form .='<div class="formtitle_style">' . stripslashes($form->name) . '</div>';
            }
            if (isset($form->description) && $form->description != '') {
                $arf_form .='<div class="arf_field_description formdescription_style">' . stripslashes($form->description) . '</div>';
            }

            $arf_form .= '</div>';
        }

        $i = 1;
        $field_page_break_type = '';
        $field_page_break_type_possition = '';
        $field_page_break_top_bar = 0;
        if ($values['fields'] and $page_num > 0) {
            $cntr_break = 0;

            /** arf_dev_flag => Loop */
            foreach ($values['fields'] as $field) {
                if ($field['type'] == 'break') {
                    if ($cntr_break == 0 && $i == 1) {
                        $field_page_break_type = $field['page_break_type'];
                        $field_page_break_type_possition = $field['page_break_type_possition'];
                        $field_page_break_top_bar = isset($field['pagebreaktabsbar']) ? $field['pagebreaktabsbar'] : 0;
                    }
                    $field_pre_page_title = $field['pre_page_title'];
                    $i++;
                }
            }

            if ($field_page_break_type == 'survey' && $field_page_break_type_possition=='top') {

                $total_page_shows = $page_num;
                if($field_page_break_top_bar != 1) {
                    //$arf_form .= '<div class="arf_survey_nav"><div id="current_survey_page" class="survey_step">' . esc_html__('Step', 'ARForms') . ' </div><div id="current_survey_page" class="current_survey_page">1</div><div class="survey_middle">' . esc_html__('of', 'ARForms') . '</div><div id="total_survey_page" class="total_survey_page">' . ($total_page_shows + 1) . '</div></div>';
                    $arf_form .= '<div class="arf_survey_nav"><div id="current_survey_page" class="survey_step">' . sprintf(addslashes(esc_html__('Step %s of %s', 'ARForms')),'</div><div id="current_survey_page" class="current_survey_page">1</div><div class="survey_middle">','</div><div id="total_survey_page" class="total_survey_page">' . ($total_page_shows + 1) . '</div></div>');

                    $arf_form .= '<div style="clear:both; margin-top:25px;"></div><div id="arf_progress_bar" style="margin-bottom:20px; clear:both;" class="ui-progress-bar"><div class="ui-progressbar-value" ><span class="ui-label"></span></div></div>';
                }
            } else {

                $total_page_shows = $page_num;
            }
        }

        $i = 1;
        if ($page_num > 0) {

            $td_width_w = number_format((100 / ($total_page_shows + 1)), 3);
            $td_width = $td_width_w . "%";
        }
        if ($values['fields'] and $page_num > 0) {
            if($field_page_break_top_bar != 1) {
                $enterrowdata = "";
                if ($field_page_break_type == 'wizard' && $field_page_break_type_possition=='top') {
                    $arf_form .= '<div id="arf_wizard_table" class="arf_wizard top">';
                    $arf_form .= '<div class="arf_wizard_upper_tab">';
                
                    $cntr_break = 0;
                    foreach ($values['fields'] as $field) {
                        $field_type = $field['type'];
                        $field['id'] = $arfieldhelper->get_actual_id($field['id']);
                        if ($field_type == "break") {
                            $first_page_break_field_val = $field; //first page break field
                            
                            $display_page_break = '';

                            $field_first_page_label = $field['first_page_label'];
                            $field_second_page_label = $field['second_page_label'];
                            $field_pre_page_title = $field['pre_page_title'];
                            if ($cntr_break == 0 && $i == 1) {
                                $field_page_break_type = $field['page_break_type'];
                            }
                            if ($field_page_break_type == "wizard") {
                                if ($cntr_break == 0 && $i == 1) {

                                    $arf_form .= '<div style="width:' . $td_width . ';" id="page_nav_' . $i . '" class="page_break_nav page_nav_selected">' . $field_first_page_label . '</div>';
                                    $i++;
                                    $arf_form .= '<div style="width:' . $td_width . '; ' . $display_page_break . '" id="page_nav_' . $i . '" class="page_break_nav">' . $field_second_page_label . '</div>';
                                    $cntr_break++;
                                } else {
                                    $arf_form .= '<div style="width:' . $td_width . '; ' . $display_page_break . '" id="page_nav_' . $i . '" class="page_break_nav">' . $field_second_page_label . '</div>';
                                }
                                $i++;
                                $enterrowdata = "<br>";
                            }
                        }
                        $field_name = 'item_meta[' . $field['id'] . ']';
                    }

                    if ($field_page_break_type == 'wizard') {
                        $arf_form .= '</div>';
                    }

                    $cntr_break = 0;
                    $i = 1;
                    if ($field_page_break_type == 'wizard') {
                        $arf_form .= '<div class="arf_wizard_lower_tab">';
                    }

                    foreach ($values['fields'] as $field) {
                        $field_type = $field['type'];
                        $field['id'] = $arfieldhelper->get_actual_id($field['id']);
                        if ($field_type == "break") {
                            $field_first_page_label = $field['first_page_label'];
                            $field_second_page_label = $field['second_page_label'];
                            $field_pre_page_title = $field['pre_page_title'];
                            if ($cntr_break == 0 && $i == 1) {
                                $field_page_break_type = $field['page_break_type'];
                            }
                            if ($field_page_break_type == "wizard") {
                                $display = "";
                                if ($cntr_break == 0 && $i == 1) {

                                    $arf_form .= '<div style="width:' . $td_width . '; padding:0;" id="page_nav_arrow_' . $i . '" class="page_break_nav page_nav_selected"><div class="arf_current_tab_arrow"></div></div>';
                                    $i++;
                                    $arf_form .= '<div style="width:' . $td_width . ';padding:0;' . $display . '" id="page_nav_arrow_' . $i . '" class="page_break_nav"></div>';
                                    $cntr_break++;
                                } else {
                                    $arf_form .= '<div style="width:' . $td_width . ';padding:0;' . $display . '" id="page_nav_arrow_' . $i . '" class="page_break_nav"></div>';
                                }
                                $i++;
                                $enterrowdata = "<div class='arf_wizard_clear' style='clear:both; height:15px;'></div>";
                            }
                        }
                        $field_name = 'item_meta[' . $field['id'] . ']';
                    }

                
                    $arf_form .= '</div>';
                    $arf_form .= '</div>' . $enterrowdata;
                }
            }
        }

        $arf_logic = $form->options['arf_conditional_logic_rules'];
        $arf_submit_logic = $form->options['submit_conditional_logic'];
        
        foreach ($arf_logic as $key => $rule) {
            $results = $rule['result'];
            $logicType = (isset($rule['logical_operator']) && $rule['logical_operator'] == 'and') ? 'all' : 'any';

            if(is_array($results))
            {
                foreach ($results as $rK => $result) {
                    $conditions = $rule['condition'];
                    $arf_cl_condition = array();
                    foreach ($conditions as $cK => $condition) {
                        foreach($arf_all_preview_fields as $k => $value ){
                            if( $value->id == $condition['field_id']) {
                                $arf_all_preview_fields[$k]->conditional_logic = true;
                            }
                        }
                    }
                }
            }

            if (isset($arf_submit_logic) && is_array($arf_submit_logic) && !empty($arf_submit_logic) && $arf_submit_logic['enable'] == 1) {

                foreach ($arf_submit_logic['rules'] as $arf_submit_rules) {
                    foreach($arf_all_preview_fields as $k => $value){
                        if( $value->id == $arf_submit_rules['field_id']){
                            $arf_all_preview_fields[$k]->conditional_logic = true;
                        }
                    }
                }
            }
        }

        $running_total_fields = array();
        foreach($values['fields'] as $k=> $field_d){
            if( $field_d['type'] == 'html' && $field_d['enable_total'] == 1 ){
                $description = $field_d['description'];
                
                $pattern = "/\:\d+/";
                
                $pattern_new = "/\<arftotal\>(.*?)\<\/arftotal\>/is";

                preg_match_all($pattern_new,$description,$matches_new);                
                
                if( isset($matches_new[1]) && isset($matches_new[1][0]) && $matches_new[1][0] != '' ){
                    
                    preg_match_all($pattern,$matches_new[1][0],$matches);
                    if( isset($matches[0]) && is_array($matches[0]) && !empty($matches[0]) ){
                        foreach( $matches[0] as $k => $val ){
                            $running_total_fields[preg_replace('/[^0-9]/','',$val)][] = $field_d['id'];
                        }
                    }
                }
            }
        }

        if( isset($running_total_fields) && $running_total_fields != '' && count($running_total_fields) > 0){
            $running_total_fields = array_map('array_unique', array_map('array_values',$running_total_fields));
            foreach($running_total_fields as $l => $field_i ){
                $rtfields = implode(',',$field_i);
                $key = $arformcontroller->arfSearchArray($l,'id',$arformcontroller->arfObjtoArray($arf_all_preview_fields));
                $arf_all_preview_fields[$key]->enable_running_total = $rtfields;
            }
        }

        $arf_form .='<div id="page_0" class="page_break">';
        
        $arf_form .= $arformcontroller->get_all_field_html($form, $values, $arf_data_uniq_id, $arf_all_preview_fields, true, array(),$inputStyle);

        /* if section started than end it */
        global $arf_section_div;
        if ($arf_section_div) {
            $arf_form .= "<div class='arf_clear'></div></div>";
            $arf_section_div = 0;
        }

        /* arf_dev_flag action to filter conversion affects paypalpro addon authorise.net addon */
        $arf_form = apply_filters('arfentryform', $arf_form, $form, 'preview', array());
        /* get all field html */
        $arf_form .='<div style="clear:both;height:1px;">&nbsp;</div>';
        $arf_form .='</div><!-- page_break && page_0-->';

        /*         * * page break another setting */
        $page_break_hidden_array[$form->id]['data-hide'] = '';
        if ($page_num > 0) {
            if (isset($page_break_hidden_array[$form->id]))
                $page_break_hidden_array[$form->id]['data-hide'] = ',' . $page_break_hidden_array[$form->id]['data-hide'];
        }

        if (!$form->is_template and $form->id != '') {

            if ($page_num == 1) {

                $display_submit = $display_previous = 'style="display:none;"';
                if ($display_submit == '') {
                    $is_submit_form = 0;
                    $last_show_page = 0;
                } else {
                    $is_submit_form = 1;
                    $last_show_page = 1;
                }
            } else if ($page_num > 1) {
                $total_page_number = $arf_page_number;
                $last_show_page = $arf_page_number;
                $compare_value = explode(',', $page_break_hidden_array[$form->id]['data-hide']);

                foreach ($compare_value as $k1 => $v1) {
                    if (is_null($v1) || $v1 == '')
                        unset($compare_value[$k1]);
                }

                for ($i = 0; $i <= $total_page_number; $i++) {

                    if (in_array($i, $compare_value)) {
                        continue;
                    } else {
                        $last_show_page = $i;
                    }
                }

                if ($last_show_page == 0) {
                    $display_submit = '';
                    $display_previous = 'style="display:none;"';
                    /* arf_dev_flag in line css */
                    $arf_form .= '<style type="text/css">.ar_main_div_' . $form->id . ' #arf_submit_div_0 { display:none; }</style>';
                    $is_submit_form = 0;
                } else {
                    $display_submit = 'style="display:none;"';
                    $display_previous = 'style="display:none;"';
                    $is_submit_form = 1;
                }
            } else {
                $display_submit = 'style="display:none;"';
                $display_previous = '';
                $is_submit_form = 1;
            }
            $preview = true;
            if (isset($preview) and $preview) {
                global $style_settings;


                $aweber_arr = "";
                $aweber_arr = $form->form_css;

                $arr = maybe_unserialize($aweber_arr);

                /* arf_dev_flag loop */
                $newarr = array();
                foreach ($arr as $k => $v)
                    $newarr[$k] = $v;

                $submit_height = ($newarr['arfsubmitbuttonheightsetting'] == '') ? '35' : $newarr['arfsubmitbuttonheightsetting'];
                $padding_loading_tmp = $submit_height - 24;
                $padding_loading = $padding_loading_tmp / 2;

                $submit_width = isset($newarr['arfsubmitbuttonwidthsetting']) ? $newarr['arfsubmitbuttonwidthsetting'] : '';

                $submit_width_loader = ($submit_width == '') ? '1' : $submit_width;
                $width_loader = ($submit_width_loader / 2);
                $width_to_add = $submit_width_loader;
                $top_margin = $submit_height + 5;
                $label_margin = isset($newarr['width']) ? $newarr['width'] : 0;
                $label_margin = $label_margin + 15;
                $arf_form .= '<div class="arfsubmitbutton ' . $_SESSION['label_position'] . '_container" ';
                if ($arf_page_number > 0 and $page_num > 0) {
                    $arf_form .= 'id="page_last"';
                    $arf_form .= $display_submit;
                }
                $arf_form .= '>';
                $arf_form .= '<div class="arf_submit_div ' . $_SESSION['label_position'] . '_container">';
                if ($arf_page_number > 0 and $page_num > 0) {
                    $arf_form .= '<input type="button" value="' . $field_pre_page_title . '" ' . $display_previous . ' name="previous" data-id="previous_last" class="previous_btn" onclick="go_previous(\'' . ($arf_page_number - 1) . '\', \'' . $form->id . '\', \'no\', \'' . $form->form_key . '\', \'' . $arf_data_uniq_id . '\');"  />';
                    $arf_form .= '<input type="hidden" value="' . $arf_page_number . '" name="last_page_id" data-id="last_page_id"  />';
                }

                if ($arf_page_number > 0 and $page_num > 0) {
                    $arf_form .= '<input type="hidden" value="1" name="is_submit_form_' . $form->id . '" data-id="is_submit_form_' . $form->id . '" />';
                    $arf_form .= '<input type="hidden" data-last="' . $last_show_page . '" value="' . $last_show_page . '" name="last_show_page_' . $form->id . '" data-id="last_show_page_' . $form->id . '" />';
                    $arf_form .= '<input type="hidden" value="' . $is_submit_form . '" data-val="1" data-hide="' . $page_break_hidden_array[$form->id]['data-hide'] . '" data-max="' . $arf_page_number . '" name="submit_form_' . $form->id . '" data-id="submit_form_' . $form->id . '" />';
                    $arf_form .= '<input type="hidden" value="' . $page_break_hidden_array[$form->id]['data-hide'] . '" name="get_hidden_pages_' . $form->id . '" data-id="get_hidden_pages_' . $form->id . '" />';
                } else {
                    $arf_form .= '<input type="hidden" value="1" name="is_submit_form_' . $form->id . '" data-id="is_submit_form_' . $form->id . '" />';
                    $arf_form .= '<input type="hidden" value="0" data-val="0" data-max="0" name="submit_form_' . $form->id . '" data-id="submit_form_' . $form->id . '" />';
                }

        		global $arfsettings;
        		if (is_array($form->form_css)) {
        		    if ($form->form_css['arfsubmitbuttontext'] != '') {
        			$submit = $form->form_css['arfsubmitbuttontext'];
        		    } else {
        			$submit = $arfsettings->submit_value;
        		    }
        		} else {
        		    $submit = $arfsettings->submit_value;
        		}

                $submit = apply_filters('getsubmitbutton',$submit,$form);
		
                $is_submit_hidden = false;
                $submitbtnstyle = '';
                $submitbtnclass = '';
                
                $arfbrowser_name = strtolower(str_replace(' ','_',$browser_info['name']));
                $submit_btn_content = '<button class="arf_submit_btn btn btn-info arfstyle-button ' . $submitbtnclass . ' '.$arfbrowser_name.'" id="arf_submit_btn_' . $arf_data_uniq_id . '" name="arf_submit_btn_' . $arf_data_uniq_id . '"';
                $submit_btn_content = apply_filters('arf_add_submit_btn_attributes_outside',$submit_btn_content,$form);
                $submit_btn_content .= ' data-style="zoom-in" ' . $submitbtnstyle . '><span class="arfsubmitloader"></span><span class="arfstyle-label">' . esc_attr($submit) . '</span>
                <span class="arf_ie_image" style="display:none;">';
                if (( $browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '9' ) || $browser_info['name'] == 'Opera') {
                    $submit_btn_content .= '<img src="' . ARFURL . '/images/submit_btn_image.gif" style="width:24px; box-shadow:none;-webkit-box-shadow:none;-o-box-shadow:none;-moz-box-shadow:none; vertical-align:middle; height:24px; padding-top:' . $padding_loading . 'px" />';
                }
                $submit_btn_content .= '</span></button>';


                $arf_form .= $submit_btn_content;

                $arf_form .= '</div><input type="hidden" name="submit_btn_image" id="submit_btn_image" value="' . ARFURL . '/images/submit_loading_img.gif" /></div><div style="clear:both"></div>';
            } else {

                $arf_form .= '<div class="arfsubmitbutton ' . $_SESSION['label_position'] . '_container" ';
                if ($arf_page_number > 0 and $page_num > 0) {
                    $arf_form .= 'id="page_last" ';
                    $arf_form .= $display_submit;
                }

                $arf_form .= '>';
                $arf_form .= '<div class="arf_submit_div ' . $_SESSION['label_position'] . '_container">';
                if ($arf_page_number > 0 and $page_num > 0) {
                    $arf_form .= '<input type="button" value="' . $field_pre_page_title . '" ' . $display_previous . ' name="previous" data-id="previous_last" class="previous_btn" onclick="go_previous(\'' . ($arf_page_number - 1) . '\', \'' . $form->id . '\', \'no\', \'' . $form->form_key . '\', \'' . $arf_data_uniq_id . '\');"  />';
                    $arf_form .= '<input type="hidden" value="' . $arf_page_number . '" name="last_page_id" data-id="last_page_id" />';
                }

                if ($arf_page_number > 0 and $page_num > 0) {
                    $arf_form .= '<input type="hidden" value="1" name="is_submit_form_' . $form->id . '" data-id="is_submit_form_' . $form->id . '" />';
                    $arf_form .= '<input type="hidden" data-last="' . $last_show_page . '" value="' . $last_show_page . '" name="last_show_page_' . $form->id . '" data-id="last_show_page_' . $form->id . '" />';
                    $arf_form .= '<input type="hidden" value="' . $is_submit_form . '" data-val="1" data-hide="' . $page_break_hidden_array[$form->id]['data-hide'] . '" data-max="' . $arf_page_number . '" name="submit_form_' . $form->id . '" data-id="submit_form_' . $form->id . '" />';
                    $arf_form .= '<input type="hidden" value="' . $page_break_hidden_array[$form->id]['data-hide'] . '" name="get_hidden_pages_' . $form->id . '" data-id="get_hidden_pages_' . $form->id . '" />';
                } else {
                    $arf_form .= '<input type="hidden" value="1" name="is_submit_form_' . $form->id . '" data-id="is_submit_form_' . $form->id . '" />';
                    $arf_form .= '<input type="hidden" value="0" data-val="0" data-max="0" name="submit_form_' . $form->id . '" data-id="submit_form_' . $form->id . '" />';
                }

                $submit = apply_filters('getsubmitbutton', $submit, $form);
                $is_submit_hidden = false;
                $submitbtnstyle = '';
                $submitbtnclass = '';
                
                $submit_btn_content = '';

                $arfbrowser_name = strtolower(str_replace(' ','_',$browser_info['name']));
                $submit_btn_content .= '<button class="arf_submit_btn btn btn-info arfstyle-button  ' . $submitbtnclass . ' '.$arfbrowser_name.'"  id="arf_submit_btn_' . $arf_data_uniq_id . '" name="arf_submit_btn_' . $arf_data_uniq_id . '"';

                $submit_btn_content = apply_filters('arf_add_submit_btn_attributes_outside',$submit_btn_content,$form);

                $submit_btn_content .= ' data-style="zoom-in" ' . $submitbtnstyle . ' >';

                $submit_btn_content .= '<span class="arfsubmitloader"></span><span class="arfstyle-label">' . esc_attr($submit) . '</span>';
                if (( $browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '9' ) || $browser_info['name'] == 'Opera') {
                    $padding_loading = isset($padding_loading) ? $padding_loading : '';
                    $submit_btn_content .= '<span class="arf_ie_image" style="display:none;">';
                    $submit_btn_content .= '<img src="' . ARFURL . '/images/submit_btn_image.gif" style="width:24px; box-shadow:none;-webkit-box-shadow:none;-moz-box-shadow:none;-o-box-shadow:none; vertical-align:middle; height:24px; padding-top:' . $padding_loading . 'px;"/>';
                    $submit_btn_content .= '</span>';
                }

                $submit_btn_content .= '</button>';


                $arf_form .= $submit_btn_content;


                $arf_form .='</div></div><div style="clear:both"></div>';
            }
        } else {

            $arf_form .= '<p class="arfsubmitbutton ' . $_SESSION['label_position'] . '_container">';
            $submit = apply_filters('getsubmitbutton', $submit, $form);
            $arf_form .= '<input type="submit" value="' . esc_attr($submit) . '" onclick="return false;" ';
            $arf_form = apply_filters('arfactionsubmitbutton', $arf_form, $form, 'preview');
            $arf_form .= '/>';
            $arf_form .= '<div id="submit_loader" class="submit_loader" style="display:none;"></div></p>';
        }
        /**         * page break another setting */
        /* arf_dev_flag we can use global variable of global settings */
        if ($field_page_break_type == 'survey' && $field_page_break_type_possition=='bottom') {

            $total_page_shows = $page_num;
            if($field_page_break_top_bar != 1) {
                //$arf_form .= '<div style="clear:both; margin-top:25px;"></div><div class="arf_survey_nav"><div id="current_survey_page" class="survey_step">' . esc_html__('Step', 'ARForms') . ' </div><div id="current_survey_page" class="current_survey_page">1</div><div class="survey_middle">' . esc_html__('of', 'ARForms') . '</div><div id="total_survey_page" class="total_survey_page">' . ($total_page_shows + 1) . '</div></div>';

                $arf_form .= '<div class="arf_survey_nav"><div id="current_survey_page" class="survey_step">' . sprintf(addslashes(esc_html__('Step %s of %s', 'ARForms')),'</div><div id="current_survey_page" class="current_survey_page">1</div><div class="survey_middle">','</div><div id="total_survey_page" class="total_survey_page">' . ($total_page_shows + 1) . '</div></div>');

                $arf_form .= '<div id="arf_progress_bar" style="margin-bottom:20px; clear:both;" class="ui-progress-bar"><div class="ui-progressbar-value" ><span class="ui-label"></span></div></div>';
            }
        }

        $i = 1;
        if ($page_num > 0) {

            $td_width_w = number_format((100 / ($total_page_shows + 1)), 3);
            $td_width = $td_width_w . "%";
        }
        if ($values['fields'] and $page_num > 0) {
            if($field_page_break_top_bar != 1) {
                $enterrowdata = "";
                if ($field_page_break_type == 'wizard' && $field_page_break_type_possition=='bottom') {
                    $arf_form .= "<div class='arf_wizard_clear' style='clear:both; height:30px;'></div>";
                    $arf_form .= '<div id="arf_wizard_table" class="arf_wizard bottom">';
                    $arf_form .= '<div class="arf_wizard_upper_tab">';
                
                    $cntr_break = 0;
                    foreach ($values['fields'] as $field) {
                        $field_type = $field['type'];
                        $field['id'] = $arfieldhelper->get_actual_id($field['id']);
                        if ($field_type == "break") {
                            $first_page_break_field_val = $field; //first page break field
                            
                            $display_page_break = '';

                            $field_first_page_label = $field['first_page_label'];
                            $field_second_page_label = $field['second_page_label'];
                            $field_pre_page_title = $field['pre_page_title'];
                            if ($cntr_break == 0 && $i == 1) {
                                $field_page_break_type = $field['page_break_type'];
                            }
                            if ($field_page_break_type == "wizard") {
                                if ($cntr_break == 0 && $i == 1) {

                                    $arf_form .= '<div style="width:' . $td_width . ';" id="page_nav_' . $i . '" class="page_break_nav page_nav_selected">' . $field_first_page_label . '</div>';
                                    $i++;
                                    $arf_form .= '<div style="width:' . $td_width . '; ' . $display_page_break . '" id="page_nav_' . $i . '" class="page_break_nav">' . $field_second_page_label . '</div>';
                                    $cntr_break++;
                                } else {
                                    $arf_form .= '<div style="width:' . $td_width . '; ' . $display_page_break . '" id="page_nav_' . $i . '" class="page_break_nav">' . $field_second_page_label . '</div>';
                                }
                                $i++;
                                $enterrowdata = "<br>";
                            }
                        }
                        $field_name = 'item_meta[' . $field['id'] . ']';
                    }

                    if ($field_page_break_type == 'wizard') {
                        $arf_form .= '</div>';
                    }

                    $cntr_break = 0;
                    $i = 1;
                    if ($field_page_break_type == 'wizard') {
                        $arf_form .= '<div class="arf_wizard_lower_tab">';
                    }

                    foreach ($values['fields'] as $field) {
                        $field_type = $field['type'];
                        $field['id'] = $arfieldhelper->get_actual_id($field['id']);
                        if ($field_type == "break") {
                            $field_first_page_label = $field['first_page_label'];
                            $field_second_page_label = $field['second_page_label'];
                            $field_pre_page_title = $field['pre_page_title'];
                            if ($cntr_break == 0 && $i == 1) {
                                $field_page_break_type = $field['page_break_type'];
                            }
                            if ($field_page_break_type == "wizard") {
                                $display = "";
                                if ($cntr_break == 0 && $i == 1) {

                                    $arf_form .= '<div style="width:' . $td_width . '; padding:0;" id="page_nav_arrow_' . $i . '" class="page_break_nav page_nav_selected"><div class="arf_current_tab_arrow"></div></div>';
                                    $i++;
                                    $arf_form .= '<div style="width:' . $td_width . ';padding:0;' . $display . '" id="page_nav_arrow_' . $i . '" class="page_break_nav"></div>';
                                    $cntr_break++;
                                } else {
                                    $arf_form .= '<div style="width:' . $td_width . ';padding:0;' . $display . '" id="page_nav_arrow_' . $i . '" class="page_break_nav"></div>';
                                }
                                $i++;
                                
                            }
                        }
                        $field_name = 'item_meta[' . $field['id'] . ']';
                    }

                
                    $arf_form .= '</div>';
                    $arf_form .= '</div>' . $enterrowdata;
                }
            }
        }

        $arfoptions = get_option("arf_options");

        $remove_status = ($arfoptions->brand);

        $my_aff_code = "";

        if (!isset($arfoptions->affiliate_code) || $arfoptions->affiliate_code == "")
            $my_aff_code = "reputeinfosystems";
        else
            $my_aff_code = $arfoptions->affiliate_code;

        if ($remove_status == 0) {

            $arf_form .='<div id="brand-div" class="brand-div ' . $_SESSION['label_position'] . '_container" style="margin-top:30px; font-size:12px !important; display:block !important;">' . addslashes(esc_html__('Powered by', 'ARForms')) . '&#32;';
            if(is_ssl()) {
                $arf_form .='<a href="https://codecanyon.net/item/arforms-exclusive-wordpress-form-builder-plugin/6023165?ref=' . $my_aff_code . '" target="_blank" style="margin:20px 0;">ARForms</a>';    
            } else {
                 $arf_form .='<a href="http://codecanyon.net/item/arforms-exclusive-wordpress-form-builder-plugin/6023165?ref=' . $my_aff_code . '" target="_blank" style="margin:20px 0;">ARForms</a>';    
            }
            
            $setlicval = 0;

            $setlicval = 0;
            global $arformsplugin;
            global $arfmsgtounlicop;
            $setlicval = $arformcontroller->$arformsplugin();

            if ($setlicval == 0) {
                $arf_form .='<span style="color:#FF0000; font-size:12px !important; display:block !important;">' . addslashes(__('&nbsp;&nbsp;' . $arfmsgtounlicop, 'ARForms')) . '</span>';
            }
            $arf_form .='</div>';
        }

        $arf_form .='</div>';

        $arf_form = apply_filters('arf_additional_form_content_outside',$arf_form,$form,$arf_data_uniq_id,$arfbrowser_name,$browser_info);

        $arf_form .='</div>';

        $arf_form .= '</form>';

        
        $arf_cl = "";

        $arf_cl_data = new stdClass();
        $arf_cl_fields = array();
        $arf_cl_dependents = array();
        $arf_cl_defaults = array();

        if (isset($arf_logic) && is_array($arf_logic) && !empty($arf_logic)) {

            $arf_conditional_logic_loaded[$form->id] = 1;
            $page_no = 0;
            $arf_field_array = array();
            
            $arf_field_array = array();
            foreach($values['fields'] as $k => $val ){
                if( $val['type'] == 'break' ){
                    $page_no++;
                }
                
                $fid = $val['id'];
                $arf_field_array[$fid] = array("page_no" => $page_no,"field_key" => $val['field_key'], "default_value" => $val['default_value']);
            }
            
            $arf_cl = "";

            $arf_logic = $form->options['arf_conditional_logic_rules'];
            $arf_submit_logic = $form->options['submit_conditional_logic'];

            foreach ($arf_logic as $key => $rule) {
                $results = $rule['result'];
                $logicType = (isset($rule['logical_operator']) && $rule['logical_operator'] == 'and') ? 'all' : 'any';

                if(isset($results))
                {
                    foreach ($results as $rK => $result) {
                        $conditions = $rule['condition'];
                        $arf_cl_condition = array();
                        foreach ($conditions as $cK => $condition) {
                            $field_key_val = isset($arf_field_array[$condition['field_id']]['field_key']) ? $arf_field_array[$condition['field_id']]['field_key'] : '';

                            $arf_cl_condition[] = array(
                                'fieldId' => $condition['field_id'],
                                'operator' => $condition['operator'],
                                'value' => $condition['value'],
                                'fieldType' => $condition['field_type'],
                                'fieldKey' => $field_key_val
                            );
                            
                            foreach($arf_all_preview_fields as $k => $value ){
                                if( $value->id == $condition['field_id']) {
                                    $arf_all_preview_fields[$k]->conditional_logic = true;
                                }
                            }
                        }
                        
                        $field_defalt_val = isset($arf_field_array[$result['field_id']]['default_value']) ? $arf_field_array[$result['field_id']]['default_value'] : '';
                        if ($result['field_id'] == '') {
                            continue;
                        }
                        if( !isset($arf_cl_fields[$result['field_id']]) ){
                            $arf_cl_fields[$result['field_id']] = array();
                        }
                        $arf_cl_fields[$result['field_id']]['fields'][] = array(
                            'actionType' => $result['action'],
                            'logicType' => $logicType,
                            'field_key' => isset($arf_field_array[$result['field_id']]['field_key']) ? $arf_field_array[$result['field_id']]['field_key'] : '',
                            'value' => $result['value'],
                            'default_value' => $field_defalt_val,
                            'field_type' => $result['field_type'],
                            'page_no' => isset($arf_field_array[$result['field_id']]['page_no']) ? $arf_field_array[$result['field_id']]['page_no'] : '',
                            'rules' => $arf_cl_condition
                        );

                        /* arf_dev_flag : Dependent fields logic need to change while having section and page break in form */
                        $arf_cl_dependents[$result['field_id']][] = (int) $result['field_id'];
                    }
                }

                if (isset($arf_submit_logic) && is_array($arf_submit_logic) && !empty($arf_submit_logic) && $arf_submit_logic['enable'] == 1) {

                    foreach ($arf_submit_logic['rules'] as $arf_submit_rules) {
                        $field_key_val = isset($arf_field_array[$arf_submit_rules['field_id']]['field_key']) ? $arf_field_array[$arf_submit_rules['field_id']]['field_key'] : '';
                        $arf_submit_cl_condition[] = array(
                            'fieldId' => $arf_submit_rules['field_id'],
                            'operator' => $arf_submit_rules['operator'],
                            'value' => $arf_submit_rules['value'],
                            'fieldType' => $arf_submit_rules['field_type'],
                            'fieldKey' => $field_key_val
                        );
                    }
                    $arf_cl_fields['submit'] = array();
                    $submit_action = ($arf_submit_logic['display'] == 'Enable' || $arf_submit_logic['display'] == 'show') ? 'show' : 'hide';
                    $arf_cl_fields['submit']['fields'][] = array(
                        'actionType' => $submit_action,
                        'logicType' => $arf_submit_logic['if_cond'],
                        'field_key' => '',
                        'value' => '',
                        'default_value' => '',
                        'field_type' => 'submit',
                        'page_no' => isset($arf_field_array[$result['field_id']]['page_no']) ? $arf_field_array[$result['field_id']]['page_no'] : '',
                        'rules' => isset($arf_submit_cl_condition) ? $arf_submit_cl_condition : array(),
                    );
                }
            }

            $arf_cl_data->logic = $arf_cl_fields;
            $arf_cl_data->dependents = $arf_cl_dependents;
            $arf_cl_data->defaults = $arf_cl_defaults;
            
            $arf_cl .= "<script type='text/javascript' data-cfasync='false'>";
            $arf_cl .= "if(!window['arf_conditional_logic']){window['arf_conditional_logic'] = new Array();}";
            $arf_cl .= "window['arf_conditional_logic'][{$arf_data_uniq_id}] = " . json_encode($arf_cl_data,JSON_UNESCAPED_UNICODE). ";";
            $arf_cl .= "</script>";
        }


        

        $arf_form .= $arf_cl;

        /* action after render form 
         * arf_dev_flag => if concept is for display content than change it to filter 
         * 
         *  */
        do_action('arf_afterdisplay_form', $form);
        do_action('arf_afterdisplay_form' . $form->id, $form);
        $arf_form .= '</div><!--arf_form_outer_wrapper -->';
        /* actual output end */


        $arf_form .= '<div class="brand-div"></div><div class=""><input type="hidden" name="form_id" data-id="form_id" value="' . $form->id . '" /><input type="hidden" name="arfmainformurl" data-id="arfmainformurl" value="' . ARFURL . '" /></div>';


        $arf_form .= "<input type='hidden' id='arf_settings_recaptcha_v2_public_key' value='{$arfsettings->pubkey}' />";
        $arf_form .= "<input type='hidden' id='arf_settings_recaptcha_v2_public_theme' value='{$arfsettings->re_theme}' />";
        $arf_form .= "<input type='hidden' id='arf_settings_recaptcha_v2_public_lang' value='{$arfsettings->re_lang}' />";


        /** if tooltip loaded than append its js */
        if ($form->options['tooltip_loaded']) {
            $arf_tootip_width = (isset($form->form_css['arf_tooltip_width']) && $form->form_css['arf_tooltip_width']!='') ? $form->form_css['arf_tooltip_width'] : 'auto';
            $arf_tooltip_position = (isset($form->form_css['arf_tooltip_position']) && $form->form_css['arf_tooltip_position']!='') ? $form->form_css['arf_tooltip_position'] : 'top';
            $arf_form_all_footer_js .= '
                if (jQuery.isFunction(jQuery().tipso)) {
                  jQuery(".ar_main_div_' . $form->id . '").find(".arfhelptip").each(function () {
                        jQuery(this).tipso("destroy");
                        var arf_data_title = jQuery(this).attr("data-title");
                        jQuery(this).tipso({
                            position: "' . $arf_tooltip_position . '",
                            width: "' . $arf_tootip_width . '",
                            useTitle: false,
                            content: arf_data_title,
                            background: "' . str_replace('##', '#', $form->form_css['arf_tooltip_bg_color']) . '",
                            color:"' . str_replace('##', '#',$form->form_css['arf_tooltip_font_color']) . '",
                            tooltipHover: true
                        });
                    });

                    jQuery(".ar_main_div_' . $form->id . ' .arf_materialize_form .arfhelptipfocus input,.ar_main_div_' . $form->id . ' .arf_materialize_form .arfhelptipfocus textarea").on( "focus", function(e){
                        jQuery(this).parent().each(function () {
                            
                            var arf_data_title = jQuery(this).attr("data-title");
                            if(arf_data_title!=null && arf_data_title!=undefined)
                            {
                                jQuery(this).tipso("destroy");
                                var arftooltip = jQuery(this).tipso({
                                    position: "' . $arf_tooltip_position . '",
                                    width: "' . $arf_tootip_width . '",
                                    useTitle: false,
                                    content: arf_data_title,
                                    background: "' . str_replace('##', '#', $form->form_css['arf_tooltip_bg_color']) . '",
                                    color:"' . str_replace('##', '#',$form->form_css['arf_tooltip_font_color']) . '",
                                    tooltipHover: true,
                                });
                                jQuery(this).tipso("show");
                                arftooltip.off("mouseover.tipso");
                                arftooltip.off("mouseout.tipso");
                            }
                        });
                    });

                    jQuery(".ar_main_div_' . $form->id . ' .arf_materialize_form .arfhelptipfocus input,.ar_main_div_' . $form->id . ' .arf_materialize_form .arfhelptipfocus textarea").focusout( function(e){
                        jQuery(this).parent().each(function () {
                            var arf_data_title = jQuery(this).attr("data-title");
                            if(arf_data_title!=null && arf_data_title!=undefined)
                            {
                                jQuery(this).tipso("hide");
                                jQuery(this).tipso("destroy");
                            }
                        });
                        
                    });
                }
                ';
        }

        /* if checkbox or radio field loaded start */

        if (in_array('radio', $loaded_field) || in_array('checkbox', $loaded_field)) {

            $form_css_submit = $form->form_css;
            $checkradio_property = "";
            if ($form_css_submit['arfcheckradiostyle'] != "") {

                if ($form_css_submit['arfcheckradiostyle'] != "none") {
                    if ($form_css_submit['arfcheckradiocolor'] != "default" && $form_css_submit['arfcheckradiocolor'] != "") {
                        if ($form_css_submit['arfcheckradiostyle'] == "custom" || $form_css_submit['arfcheckradiostyle'] == "futurico" || $form_css_submit['arfcheckradiostyle'] == "polaris") {
                            $checkradio_property = $form_css_submit['arfcheckradiostyle'];
                        } else {
                            $checkradio_property = $form_css_submit['arfcheckradiostyle'] . "-" . $form_css_submit['arfcheckradiocolor'];
                        }
                    } else {
                        $checkradio_property = $form_css_submit['arfcheckradiostyle'];
                    }
                } else {
                    $checkradio_property = "";
                }
            }

            $checked_checkbox_property = '';
            if ($form_css_submit['arf_checked_checkbox_icon'] != "") {
                $checked_checkbox_property = ' arfa ' . $form_css_submit['arf_checked_checkbox_icon'];
            } else {
                $checked_checkbox_property = '';
            }
            $checked_radio_property = '';
            if ($form_css_submit['arf_checked_radio_icon'] != "") {
                $checked_radio_property = ' arfa ' . $form_css_submit['arf_checked_radio_icon'];
            } else {
                $checked_radio_property = '';
            }
            
        }
        /* if checkbox or radio field loaded end */

        /* if smiley field loaded start */

        if (in_array('arf_smiley', $loaded_field)) {


            $arf_form_all_footer_js .='
                    jQuery(".arf_smiley_btn").each(function () {
                        var title = jQuery(this).attr("data-title");
                        if (title !== undefined) {
                            jQuery(this).popover({
                                html: true,
                                trigger: "hover",
                                placement: "top",
                                content: title,
                                title: "",
                                animation: false
                            });
                        }
                    });
              
                /*jQuery(document).on("click", ".arf_smiley_btn", function () {
                    var field_id = jQuery(this).attr("data-id");
                    var form_data_id = jQuery(this).attr("data-form-data-id");
                    jQuery("#arf_smiley_container_" + form_data_id + "_" + field_id).find(".arf_smiley_btn").removeClass("arf_smiley_selected");
                    jQuery(this).addClass("arf_smiley_selected");
                });*/';

            /** arf_dev_flag internal css need to remove */
        }

        /* if smiley field loaded end */

        if (in_array('like', $loaded_field)) {
            $arf_form_all_footer_js .= 'jQuery(".arf_like_btn, .arf_dislike_btn").each(function () {
                            var title = jQuery(this).attr("data-title");
                            if (title !== undefined) {
                                jQuery(this).popover({
                                    html: true,
                                    trigger: "hover",
                                    placement: "top",
                                    content: title,
                                    title: "",
                                    animation: false
                                });
                            }
                        });';
        }

        if (in_array('colorpicker', $loaded_field)) {
            $arf_form_all_footer_js .= "__JSPICKER_NEWROW = [];
            jQuery('.jscolor').each(function (e) {
            var this_val = jQuery(this);
            var object = {};
            var el = this_val[0];
            var pattern = /(jscolor)\-(.*?)/;
            var x = 0;
            for (var att, i = 0, atts = el.attributes, n = atts.length; i < n; i++) {
                var att = atts[i];
                var nodename = att.nodeName;
                var nodeval = att.nodeValue;
                if (pattern.test(nodename)) {
                    var name = nodename.replace('jscolor-', '');
                    switch (name) {
                        case 'onfinechange':
                        name = 'onFineChange';
                        break;
                        case 'styleelement':
                        name = 'styleElement';
                        break;
                        case 'valueelement':
                        name = 'valueElement';
                        break;
                        default:
                        name = name;
                        break;
                    }
                    object[name] = nodeval;
                    x++;
                }
            }
             __JSPICKER_NEWROW[e] = new jscolor(el, object);
            if (typeof __JSPICKER === 'undefined') {
                __JSPICKER = __JSPICKER_NEWROW;
            } else {
                __JSPICKER = __JSPICKER.concat(__JSPICKER_NEWROW);
            }            
            });
            ";
        }


        /* arf_dev_flag move it to script localization `need to discuss` as ARMember */

        $arf_form_all_footer_js .= "__ARFMAINURL='" . ARFSCRIPTURL . "';\n";

        $arf_form_all_footer_js .= "__ARFERR='" . addslashes(esc_html__('Sorry, this file type is not permitted for security reasons.', 'ARForms')) . "';\n";

        $arf_form_all_footer_js .= "__ARFAJAXURL='" . admin_url('admin-ajax.php') . "';\n";

        $arf_form_all_footer_js .= "__ARFSTRRNTH_INDICATOR='" . addslashes(esc_html__('Strength indicator', 'ARForms')) . "';\n";

        $arf_form_all_footer_js .= "__ARFSTRRNTH_SHORT='" . addslashes(esc_html__('Short', 'ARForms')) . "';\n";

        $arf_form_all_footer_js .= "__ARFSTRRNTH_BAD='" . addslashes(esc_html__('Bad', 'ARForms')) . "';\n";

        $arf_form_all_footer_js .= "__ARFSTRRNTH_GOOD='" . addslashes(esc_html__('Good', 'ARForms')) . "';\n";

        $arf_form_all_footer_js .= "__ARFSTRRNTH_STRONG='" . addslashes(esc_html__('Strong', 'ARForms')) . "';\n";

        $arf_form_all_footer_js .= 'jQuery("#arffrm_' . $form->id . '_container").find("form").find(".arfformfield").each(function () {
                    var data_view = jQuery(this).attr("data-view");
                    if (data_view == "arf_disable") {
                        var data_type = jQuery(this).attr("data-type");
                        arf_field_disable(jQuery(this), data_type);
                    }
                });';


        unset($page_break_hidden_array[$form->id]);

        $arf_form .= '</div>';
        $arfforms_loaded[] = $form;
        $arf_preview_form = $form;
        return $arf_form;
    }

}