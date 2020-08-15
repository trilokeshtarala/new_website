<?php

if (!function_exists('ars_get_form_builder_string')) {

    function ars_get_form_builder_string($id, $key = "", $preview = 0, $is_widget_or_modal = 0, $errors = array(), $arf_data_uniq_id = '', $desc = '', $type = '', $modal_height = '', $modal_width = '', $position = '', $btn_angle = '', $bgcolor = '', $txtcolor = '', $open_inactivity = '', $open_scroll = '', $open_delay = '', $overlay = '', $is_close_link = '', $modal_bgcolor = '', $is_fullscrn ='',$inactive_min = '',$model_effect = '',$navigation = false,$arf_preset_data = '') {
        @ini_set('max_execution_time', 0);
        /* declare global */
        
        $home_preview = false;
        if( isset($_REQUEST['arf_is_home']) ){
            $home_preview = $_REQUEST['arf_is_home'];
        }
        
        global $arfform, $user_ID, $arfsettings, $post, $wpdb, $armainhelper, $arrecordcontroller, $arformcontroller, $arfieldhelper, $arrecordhelper, $page_break_hidden_array, $arf_page_number, $arfforms_loaded, $arf_form_all_footer_js, $arfcreatedentry, $MdlDb,$func_val,$front_end_get_temp_fields,$arfdecimal_separator,$arfmessage_rest;

        if(isset($_SESSION['arf_form_fileuploads'])){ $_SESSION['arf_form_fileuploads'] = array(); }

        $arf_form = '';


        $arf_popup_data_uniq_id = $arf_data_uniq_id;


        $page_break_hidden_array = array();
        $arf_page_number = 0;
        $browser_info = $arrecordcontroller->getBrowser($_SERVER['HTTP_USER_AGENT']);
        /* get form data */
        if ($id) {
            if( !isset($GLOBALS['arf_form_data'][$id]) ){
                $form = $arfform->getOne((int) $id);
            } else {
                $form = $GLOBALS['arf_form_data'][$id];
            }
        } else if ($key) {
            $form = $arfform->getOne($key);
        }

            /* get form data */

            $form = apply_filters('arfpredisplayform', $form);

            if ((is_object($form) && (isset($form->is_template) && (!isset($form->status) || $form->status == 'draft'))) && ! ($preview)) {
                $arf_form .= addslashes(esc_html__('Please select a valid form', 'ARForms'));
                return $arf_form;
            } else if (!$form || ( ($form->is_template || $form->status == 'draft'))) {
                $arf_form .= addslashes(esc_html__('Please select a valid form', 'ARForms'));
                return $arf_form;
            } else if ( isset($form->is_loggedin) && ( ($form->is_loggedin == 1 && !$user_ID) || ($form->is_loggedin == 2 && $user_ID) ) ) {
                global $arfsettings;
                return do_shortcode($arfsettings->login_msg);
            }
            $arfforms_loaded[] = $form;


            /* below filter have query */
            if( !isset($GLOBALS['function_val'][$id]) ){

                $func_val = apply_filters('arf_hide_forms', $arformcontroller->arf_class_to_hide_form($id), $id);
                

            } else {
                $func_val = $GLOBALS['function_val'][$id];
            }



            /* if entry restricted */
            /* arf_dev_flag here is some confusion with last entry & restrict entry with max entries than while submitting the last entry form will be hide and error message will be shown */

            if ($func_val !='' && isset($_POST['is_submit_form_' . $id])) {             
                if (!isset($func_val['hide_forms'])) {
                    echo $func_val;
                    return false;
                }
            } else if ($func_val != '' && !$navigation) {
                $error_restrict_entry = json_decode($func_val);
                echo $error_restrict_entry->message;
                return false;
            }

            /** submit button text start */
            $form_css_submit = $form->form_css = maybe_unserialize($form->form_css);

            if (is_array($form->form_css)) {
                if ($form->form_css['arfsubmitbuttontext'] != '') {
                    $submit = $form->form_css['arfsubmitbuttontext'];
                } else {
                    $submit = $arfsettings->submit_value;
                }
            } else {
                $submit = $arfsettings->submit_value;
            }
            /** submit button text end */
            /* get fields data */

            if( !isset($GLOBALS['form_fields'][$form->id]) ){
                $fields = $arfieldhelper->get_form_fields_tmp(false, $form->id, false, 0);
            } else {
                $fields = $GLOBALS['form_fields'][$form->id];
            }     
            /* arf_dev_flag  => "there is query in below function" */


            $values = $arrecordhelper->setup_new_vars($fields, $form);

            /* get fields data */

            /* after submit action start */

            $params = $arrecordcontroller->get_recordparams($form);

            $saved_message = isset($form->options['success_msg']) ? '<div id="arf_message_success"><div class="msg-detail"><div class="msg-description-success">' . $form->options['success_msg'] . '</div></div></div>' : $arfsettings->success_msg;

            $saved_popup_message = isset($form->options['success_msg']) ? '<div id="arf_message_success_popup" style="display:none;"><div class="msg-detail"><div class="msg-description-success">' . $form->options['success_msg'] . '</div></div></div>' : '<div id="arf_message_success_popup" style="display:none;"><div class="msg-detail"><div class="msg-description-success">' . $arfsettings->success_msg . '</div></div></div>';


            if ($params['action'] == 'create' and $params['posted_form_id'] == $form->id and isset($_POST)) {

                if(isset($_REQUEST['arfsubmiterrormsg'])) {

                    $arferror_message  = ($_REQUEST['arfsubmiterrormsg'] != "") ? $_REQUEST['arfsubmiterrormsg'] : $arfsettings->failed_msg;

                    $failed_message = '<div class="frm_error_style" id="arf_message_error"><div class="msg-detail"><div class="arf_res_front_msg_desc">'.$arferror_message.'</div></div></div>';

                    $arf_display_error = '<div class="arf_form ar_main_div_' . $form->id . '" id="arffrm_' . $form->id . '_container">'.$failed_message.'</div>';

                    return $arf_display_error;
                }

                $errors = isset($arfcreatedentry[$form->id]['errors']) ? $arfcreatedentry[$form->id]['errors'] : array();

                if (!empty($errors)) {
                    $created = isset($arfcreatedentry[$form->id]['entry_id']) ? $arfcreatedentry[$form->id]['entry_id'] : '';
                    if ($arfsettings->form_submit_type == 1) {

                    } else {
                        foreach ($errors as $e) {
                            if (!empty($e)) {

                                foreach ($e as $key => $val) {
                                    $failed_msg = '<div class="frm_error_style" id="arf_message_error"><div class="msg-detail"><div class="arf_res_front_msg_desc">' . $val . '</div></div></div>';

                                    $message = ((isset($created) && is_numeric($created)) ? do_shortcode($saved_message) : $failed_msg);
                                    $arf_form .= '<div class="arf_form ar_main_div_' . $form->id . '" id="arffrm_' . $form->id . '_container">' . $message . '</div>';
                                       
                                    
                                    
                                }
                            }
                        }
                        return $arf_form;
                    }
                } else {

                    if (apply_filters('arfcontinuetocreate', true, $form->id)) {

                        $created = isset($arfcreatedentry[$form->id]['entry_id']) ? $arfcreatedentry[$form->id]['entry_id'] : '';

                        $saved_message = apply_filters('arfcontent', $saved_message, $form, $created);

                        $saved_popup_message = $saved_message;

                        $conf_method = apply_filters('arfformsubmitsuccess', 'message', $form, $form->options);

                        /* For normal submission method if condition false for conditional redirect. */
                        if ($arfsettings->form_submit_type != 1 && $conf_method == 'redirect' && $saved_message == false) {
                            $conf_method = 'message';
                            $saved_message = '<div  id="arf_message_success"><div class="msg-detail"><div class="msg-description-success">' . $arfsettings->success_msg . '</div></div></div>';
                            $saved_popup_message = '<div id="arf_message_success_popup" style="display:none;"><div class="msg-detail"><div class="msg-description-success">'.$arfsettings->success_msg.'</div>';
                        }
                        
                        if (!$created or ! is_numeric($created)){
                            $conf_method = 'message';
                        }

                        $return_script = '';

                        $return["script"] = apply_filters('arf_after_submit_sucess_outside',$return_script,$form);

                        if (!$created or ! is_numeric($created) or $conf_method == 'message') {

                            if ($arfsettings->form_submit_type == 1) {
                                $return["conf_method"] = $conf_method;

                                /* if ajax sumssion and restrict entry than hide form at last entry */

                                if (isset($func_val['hide_forms'])&&$func_val['hide_forms']==true) {

                                    $return["hide_forms"] = $func_val['hide_forms'];
                                }
                            }

                            $failed_msg = '<div class="frm_error_style" id="arf_message_error"><div class="msg-detail"><div class="arf_res_front_msg_desc">' . $arfsettings->failed_msg . '</div></div></div>';

                            $message = (($created and is_numeric($created)) ? do_shortcode($saved_message) : $failed_msg);

                            if (!isset($form->options['show_form']) or $form->options['show_form']) {

                            } else {
                                if (isset($values['custom_style']) && $values['custom_style'])
                                    $arfloadcss = true;


                                if ($arfsettings->form_submit_type != 1) {

                                    $custom_css_array_form = array(
                                        'arf_form_success_message' => '#message_success',
                                        );

                                    foreach ($custom_css_array_form as $custom_css_block_form => $custom_css_classes_form) {
                                        $form->options[$custom_css_block_form] = $arformcontroller->br2nl($form->options[$custom_css_block_form]);

                                        if (isset($form->options[$custom_css_block_form]) and $form->options[$custom_css_block_form] != '') {
                                            echo '<style type="text/css">.ar_main_div_' . $form->id . ' ' . $custom_css_classes_form . ' { ' . $form->options[$custom_css_block_form] . ' } </style>';
                                        }
                                    }
                                }
                                $return = apply_filters( 'arf_reset_built_in_captcha', $return, $_POST );
                                if ($arfsettings->form_submit_type == 1) {
                                    $return["message"] = '<div class="arf_form ar_main_div_' . $form->id . '" id="arffrm_' . $form->id . '_container">' . $message . '</div>';
                                    echo json_encode($return);
                                    exit;
                                } else {                                    
                                    if($arfmessage_rest == ''){
                                        $arf_form .= $return["script"];
                                        $arf_form .= '<div class="arf_form ar_main_div_' . $form->id . '" id="arffrm_' . $form->id . '_container">' . $message . '</div>';
                                        $arfmessage_rest = 1;
                                    }
                                    return $arf_form;
                                }

                                if ($arfsettings->form_submit_type == 1)
                                    exit;
                            }
                        } else {
                            if ($arfsettings->form_submit_type == 1) {
                                $return["conf_method"] = $conf_method;
                            }

                            $form_options = $form->options;
                            $entry_id = $arfcreatedentry[$form->id]['entry_id'];
                            if ($conf_method == 'page' and is_numeric($form_options['success_page_id'])) {
                                global $post;
                                if ($form_options['success_page_id'] != $post->ID) {
                                    $page = get_post($form_options['success_page_id']);
                                    $old_post = $post;
                                    $post = $page;
                                    $content = apply_filters('arfcontent', $page->post_content, $form, $entry_id);
                                        $arf_old_content = get_post($post->ID)->post_content;

                                        $pattern = '\[(\[?)(ARForms|ARForms_popup)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';

                                        preg_match_all('/' . $pattern . '/s', $arf_old_content, $matches);

                                        foreach ($matches[0] as $key => $val) {
                                            $new_val = trim(str_replace(']', '', $val));
                                            $new_val1 = explode(' ', $new_val);

                                            $arf_form_id_extracted = isset($new_val1[1]) ? str_replace('id=','',$new_val1[1]) : $form->id;

                                            $var = 'id=' . $arf_form_id_extracted;
                                            $wp_upload_dir = wp_upload_dir();
                                        
                                            $upload_main_url = $wp_upload_dir['baseurl'] . '/arforms/maincss';
                                            $is_material = false;
                                            $materialize_css = '';

                                            $temp_form_opts = $wpdb->get_row($wpdb->prepare("SELECT `form_css` FROM `".$MdlDb->forms."` WHERE id = %d",$arf_form_id_extracted) );

                                            if( empty($temp_form_opts) || $temp_form_opts == null ){
                                                continue;
                                            }

                                            $temp_opts = maybe_unserialize($temp_form_opts->form_css);

                                            $inputStyle = isset($temp_opts['arfinputstyle']) ? $temp_opts['arfinputstyle'] : 'material';

                                            if( $inputStyle == 'material' ){
                                                $materialize_css = '_materialize';
                                                $is_material = true;
                                            }
                                            if( is_ssl() ){
                                                $fid = str_replace("http://", "https://", $upload_main_url . '/maincss' . $materialize_css .'_' . $arf_form_id_extracted . '.css');
                                            } else {
                                                $fid = $upload_main_url . '/maincss' . $materialize_css .'_' . $arf_form_id_extracted . '.css';
                                            }       
                                            $return_link = "";
                                            $stylesheet_handler = 'arfformscss'.$materialize_css.$arf_form_id_extracted;

                                            /*$return_link .= "<link rel='stylesheet' type='text/css' id='{$stylesheet_handler}-fallback-css' href='{$fid}' />";
                                            $return_link .= "<link rel='stylesheet' type='text/css' id='arf_materialize_css_fallback' href='".ARFURL . "/materialize/materialize.css' />";
                                            $return_link .= "<script id='arf_materialize_script_fallback' src='".ARFURL . "/materialize/materialize.js'></script>";*/
                                            
                                            $arf_form .= stripslashes($return_link);

                                            if (trim($new_val1[1]) == $var) {
                                                $replace = $matches[0][$key];
                                            }
                                        }
                                        $arf_form .= $return["script"];
                                        $arf_form .= apply_filters('the_content', $content);

                                        if ($arfsettings->form_submit_type == 1) {
                                            $return['message'] = $arf_form;
                                        } else {
                                            return $arf_form;
                                        }
                                }
                            } else if ($method == 'redirect') {
                                $success_url = apply_filters('arfcontent', $form_options['success_url'], $form, $entry_id);
                                $success_msg = isset($form_options['success_msg']) ? stripslashes($form_options['success_msg']) : addslashes(esc_html__('Please wait while you are redirected.', 'ARForms'));

                                echo "<script type='text/javascript' data-cfasync='false'> jQuery(document).ready(function($){ setTimeout(window.location='" . $success_url . "', 5000); });</script>";
                            }



                            if ($arfsettings->form_submit_type == 1) {
                                echo json_encode($return);
                                exit;
                            }
                        }
                    }
                }
            }
            /* after submit action end */

            $is_hide_form_after_submit = isset($form->options['arf_form_hide_after_submit']) ? $form->options['arf_form_hide_after_submit'] : false;

            /* popup related settings */
            if ($type != '') {

                global $arf_modal_loaded;
                $arf_modal_loaded = true;
                $open_inactivity_value = '1';
                $open_scroll_value = '10';
                $open_delay_value = '500';
                $overlay_value = '0.6';
                $data_inactive = '';
                $class_for_idle = '';
                $is_open_form_class = false;

                $is_onload = false;
                $is_scroll = false;
                $is_onexit = false;
                $is_x_seconds = false;
                $is_onideal = false;

                if ($type == 'onload') {
                    $type = 'link';
                    $is_onload = true;

                    if (!empty($open_delay) && is_numeric($open_delay)) {
                        $open_delay_value = ($open_delay * 1000);
                    }
                }

                /** New Setting for time **/
                $is_timer = false;
                if ($type == 'timer') {
                    $is_timer = true;
                    $type = 'link';
                    $is_onload = true;

                    if (!empty($open_delay) && is_numeric($open_delay)) {
                        $open_delay_value = ($open_delay * 1000);
                    }
                } else if ($type == 'x_seconds') {
                    $type = 'link';
                    $is_onload = true;
                    $is_x_seconds = true;
                    if (!empty($open_inactivity) && is_numeric($open_inactivity)) {
                        $open_inactivity_value = $open_inactivity;
                    }
                } else if ($type == 'scroll') {
                    $type = 'link';
                    $is_onload = true;
                    $is_scroll = true;
                    if (!empty($open_scroll) && is_numeric($open_scroll)) {
                        $open_scroll_value = $open_scroll;
                    }
                } else if ($type == 'on_exit') {
                    $type = 'link';
                    $is_onload = true;
                    $is_onexit = true;
                    $is_open_form_class = true;
                } else if ($type == 'on_idle') {
                    $type = 'link';
                    $is_onload = true;
                    $is_onideal = true;
                    
                    if(!empty($inactive_min) && is_numeric($inactive_min)){
                        $inactive_time = $inactive_min;
                    }else{
                        $inactive_time ='';
                    }

                    $data_inactive = 'data-inactive-minute="'.$inactive_time.'"';
                    $class_for_idle = 'arf_modal_cls';
                }


                if (is_numeric($overlay)) {
                    $overlay_value = $overlay;
                }

                if (empty($modal_width)) {
                    $modal_width = 800;
                }

                if ($is_onload) {
                    $style_onload = ' style="display:none !important;"';
                } else {
                    $style_onload = ' style="cursor:pointer;"';
                }

                if($is_open_form_class){
                    $add_class_onexit = 'show_onexit_window';
                }else{
                    $add_class_onexit = '';
                }

                if($model_effect != ''){
                    $class_modeleffect = $model_effect;
                }
                

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
                $checked_radio_property = '';

                if ($checkradio_property == 'custom') {
                    $arf_font_awesome_loaded = 1;

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
                $form_name = $form->name;
                $popup_extra_attr = "";
                
                if($is_timer){
                    $popup_extra_attr .= " data-ontimer='1' data-delay='{$open_delay_value}' ";
                } else if( $is_onideal ){
                    $popup_extra_attr .= " data-onidle='1' ";
                } else if( $is_onload && !$is_scroll && !$is_onexit && !$is_x_seconds ){
                    $popup_extra_attr .= " data-onload='1' ";
                }
                if ($type == 'link' || $type == '') {
                    $arf_form .= '<div><a href="#" onclick="open_modal_box(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $checkradio_property . '\', \'' . $is_close_link . '\', \'' . $checked_checkbox_property . '\', \'' . $checked_radio_property . '\', \'' . $arf_popup_data_uniq_id . '\');" id="arf_modal_default" '.$popup_extra_attr.' data-toggle="arfmodal" title="' . $form_name . '" data-link-popup-id="' . $arf_popup_data_uniq_id . '" class="arform_modal_link_' . $form->id . '_' . $arf_popup_data_uniq_id . ' '.$add_class_onexit.' '.$class_for_idle.' " ' . $style_onload . ' '.$data_inactive.'>' . $desc . '</a></div>';
                } elseif ($type != 'fly' && $type != 'sticky') {
                    $arf_form .= '<div><button href="#" onclick="open_modal_box(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $checkradio_property . '\', \'' . $is_close_link . '\', \'' . $checked_checkbox_property . '\', \'' . $checked_radio_property . '\', \'' . $arf_popup_data_uniq_id . '\');" id="arf_modal_default"  '.$popup_extra_attr.' data-toggle="arfmodal"  title="' . $form_name . '" class="arform_modal_button_' . $form->id . ' arform_modal_button_popup_' . $arf_popup_data_uniq_id . '" ' . $style_onload . '>' . $desc . '</button></div>';
                }

                $form_opacity = ($form_css_submit['arfmainform_opacity'] == '' || $form_css_submit['arfmainform_opacity'] > 1) ? 1 : $form_css_submit['arfmainform_opacity'];


                $arf_form_all_footer_js .='function popup_tb_show(form_id, submitted)
                {
                    var last_open_modal = jQuery("[data-id=\'current_modal\']").val();
                    if (last_open_modal == "arf_modal_left")
                    {
                        jQuery(".arform_side_block_left_" + form_id).trigger("click");
                    }
                    else if (last_open_modal == "arf_modal_right")
                    {
                        jQuery(".arform_side_block_right_" + form_id).trigger("click");
                    }
                    else if (last_open_modal == "arf_modal_top")
                    {
                        setTimeout(function () {
                            jQuery(".arform_bottom_fixed_form_block_top_main").css("display", "block");
                            jQuery(".arform_bottom_fixed_form_block_top_main").css("height", "auto");
                        }, 500);
                    }
                    else if (last_open_modal == "arf_modal_bottom")
                    {
                        setTimeout(function () {
                            jQuery(".arform_bottom_fixed_form_block_bottom_main").css("display", "block");
                            jQuery(".arform_bottom_fixed_form_block_bottom_main").css("height", "auto");
                        }, 500);
                    }
                    else if (last_open_modal == "arf_modal_sitcky_left")
                    {
                        setTimeout(function () {
                            jQuery(".arform_bottom_fixed_form_block_left_main").css("display", "block");
                            jQuery(".arform_bottom_fixed_form_block_left_main").css("height", "auto");
                        }, 500);
                    }
                    else if (last_open_modal == "arf_modal_default")
                    {
                        jQuery("#arf_modal_default").trigger("click");
                        if (submitted == true) {
                            var len = jQuery(".arfmodal-backdrop").length;
                            jQuery(".arfmodal-backdrop").each(function () {

                                if (len != 1) {
                                    jQuery(this).remove();
                                }
                                len = len - 1;
                            });
                        }
                    }
                }';


                if ($type == 'link' && $is_onload) {
                    if ($is_scroll) {

                        $arf_form_all_footer_js .='var arf_open_scroll = "' . $open_scroll_value . '";
                        var arf_op_welcome = false;
                        window.onLoadClicked = false;
                        jQuery(window).scroll(function (event) {
                            var scrollPercent = 100 * jQuery(window).scrollTop() / (jQuery(document).height() - jQuery(window).height());
                            if (Math.round(scrollPercent) == arf_open_scroll) {

                            }
                        });

                        jQuery(window).scroll(function () {
                            var h = jQuery(document).height() - jQuery(window).height();
                            var sp = jQuery(window).scrollTop();
                            var p = parseInt(sp / h * 100);

                            if (p >= arf_open_scroll && arf_op_welcome == false) {
                                var mypopup_data_uniq_id = ' . $arf_popup_data_uniq_id . ';
                                jQuery(".arform_modal_link_' . $form->id . '_" + mypopup_data_uniq_id).trigger("click");
                                window.onLoadClicked = true;
                                arf_op_welcome = true;
                            }
                        });';
                    } else if ($is_x_seconds) {

                        /*$arf_form_all_footer_js .='var arf_idletime = 0;
                        var arf_open_inactivity = "' . $open_inactivity_value . '";
                        function arf_timerIncrement()
                        {
                            arf_idletime++;
                            if (arf_idletime > arf_open_inactivity)
                            {
                                window.clearTimeout(arf_idleInterval);
                                jQuery(".arform_modal_link_' . $form->id . '_" + mypopup_data_uniq_id).trigger("click");
                            }
                        }
                        var arf_idleInterval = setInterval(arf_timerIncrement, 1000);
                        jQuery(this).mousemove(function (e) {
                            arf_idletime = 0;
                        });
                        jQuery(this).keypress(function (e) {
                            arf_idletime = 0;
                        });';*/
                    } else if ($is_onexit){
                         $arf_form_all_footer_js .='var arf_op_welcome = false;
                                var mypopup_data_uniq_id = ' . $arf_popup_data_uniq_id . ';                              

                                arf_op_welcome = true;';
                                

                    } else if($is_onideal){
                        $arf_form .= '<script type="text/javascript">

                        window.arf_timer_popup = {};
                        window.arf_opened_popup = new Array();

                        function startTimer(popup_id, timer){
                            var timer = ( timer * 1000 ) * 60;
                            var timerObj = setTimeout(function(){
                                IdleTimeout(popup_id,timer);
                            },timer);
                            window.arf_timer_popup[popup_id] = timerObj;
                        }

                        function IdleTimeout(popup_id,timer){
                           
                            var modal_display = popup_id.split(" ");
                            
                            if(jQuery.inArray( modal_display[0], arf_opened_popup ) < 0){

                                jQuery("."+modal_display[0]).trigger("click");
                                arf_opened_popup.push(modal_display[0]);
                                clearTimeout(window.arf_timer_popup[popup_id]);
                            }
                        }

                        function resetTimeout(){
                            var keys = Object.keys(window.arf_timer_popup);
                            for( var x = 0; x < keys.length; x++ ){
                                var timer_key = keys[x];
                                var current_time = window.arf_timer_popup[timer_key];
                                clearTimeout(current_time);
                            }
                            init_timer();
                        }

                        function init_timer(){
                            var timer_popups = document.getElementsByClassName("arf_modal_cls");
                            for( var i = 0; i < timer_popups.length; i++ ){
                                var current_popup = timer_popups[i];
                                var inactiv_time = current_popup.getAttribute("data-inactive-minute");
                                var popup_id = current_popup.getAttribute("class");
                                startTimer(popup_id,inactiv_time);
                            }
                        }


                        </script>
                    ';

                    } else {
                        /*$arf_form_all_footer_js .='  var mypopup_data_uniq_id = ' . $arf_popup_data_uniq_id . ';
                        (function(popup_data_uniq_id){
                            setTimeout(function () {
                                jQuery(".arform_modal_link_' . $form->id . '_" + popup_data_uniq_id).trigger("click");
                            }, ' . $open_delay_value . ');
                        })(mypopup_data_uniq_id);';*/

                        /*if( $arf_popup_data_uniq_id ){
                            $arf_form_all_footer_js .= '
                            jQuery(window).on("load",function(){
                                setTimeout(function(){
                                    var mypopup_data_uniq_id = '. $arf_popup_data_uniq_id.';
                                    jQuery(".arform_modal_link_' . $form->id . '_" + mypopup_data_uniq_id).trigger("click");
                                }, '.$open_delay_value.')
                            });';
                        }*/
                    }
                }

                $arf_form_all_footer_js .='  jQuery(".arform_right_fly_form_block_right_main").hide();
                jQuery(".arform_left_fly_form_block_left_main").hide();
                var mybtnangle = ' . $btn_angle . ';
                var myformid = ' . $form->id . ';
                var mypopup_data_uniq_id = ' . $arf_popup_data_uniq_id . ';

                if (Number(mybtnangle) == - 90)
                {
                    jQuery(".arf_popup_" + mypopup_data_uniq_id).find(".arform_side_block_right_" + myformid + "").css("transform-origin", "bottom right");
                    jQuery(".arf_popup_" + mypopup_data_uniq_id).find(".arform_side_block_left_" + myformid + "").css("transform-origin", "top left");
                }
                else if (Number(mybtnangle) == 90)
                {
                    jQuery(".arf_popup_" + mypopup_data_uniq_id).find(".arform_side_block_right_" + myformid + "").css("transform-origin", "top right");
                    jQuery(".arf_popup_" + mypopup_data_uniq_id).find(".arform_side_block_left_" + myformid + "").css("transform-origin", "bottom left");
                }
                ';



                if ($type == 'fly') {
                    if ($position == 'right') {

                        $arf_form .= '<div id="arf-popup-form-' . $form->id . '" class="arf_flymodal arf_popup_' . $arf_popup_data_uniq_id . '" style="z-index:9999;">';

                        $arf_modal_height = ($modal_height=='auto') ? '' : 'max-height:'.$modal_height.'px;';
                        $arf_form .= '<span href="#" onclick="open_modal_box_fly_right(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $checkradio_property . '\', \'' . $checked_checkbox_property . '\', \'' . $checked_radio_property . '\', \'' . $arf_popup_data_uniq_id . '\');"  title="' . $form_name . '" class="arform_side_block_right_' . $form->id . ' arf_fly_sticky_btn">' . $desc . '</span>';

                        $arf_form .= '<div class="arform_side_fixed_form_block_right_main_' . $form->id . '">';
                        $arf_form .= '<div id="popup-form-' . $form->id . '" aria-hidden="false" class="arform_right_fly_form_block_right_main arform_sb_fx_form_right_' . $form->id . ' arf_pop_' . $arf_popup_data_uniq_id . '" style="' . $arf_modal_height . ' width: ' . $modal_width . 'px;z-index:9999; top:20%; right:-110%;">';
                    } else {

                        $arf_form .= '<div id="arf-popup-form-' . $form->id . '" class="arf_flymodal arf_popup_' . $arf_popup_data_uniq_id . '" style="z-index:9999;">';

                        $arf_form .= '<span href="#" onclick="open_modal_box_fly_left(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $checkradio_property . '\', \'' . $checked_checkbox_property . '\', \'' . $checked_radio_property . '\', \'' . $arf_popup_data_uniq_id . '\');"  title="' . $form_name . '" class="arform_side_block_left_' . $form->id . ' arf_fly_sticky_btn">' . $desc . '</span>';

                        $arf_modal_height = ($modal_height=='auto') ? '' : 'max-height:'.$modal_height.'px;';

                        $arf_form .= '<div class="arform_side_fixed_form_block_left_main_' . $form->id . '">';
                        $arf_form .= '<div id="popup-form-' . $form->id . '" aria-hidden="false" class="arform_left_fly_form_block_left_main arform_sb_fx_form_left_' . $form->id . ' arf_pop_' . $arf_popup_data_uniq_id . '" style="' . $arf_modal_height . ' width: ' . $modal_width . 'px;z-index:9999; top:20%; right:110%; ">';
                    }
                } elseif ($type == 'sticky') {
                    if ($position == 'top') {
                        $arf_modal_height = ($modal_height=='auto') ? '' : 'max-height:'.$modal_height.'px;';

                        $arf_form .= '<div id="arf-popup-form-' . $form->id . '" class="arf_flymodal arform_bottom_fixed_main_block_top arf_popup_' . $arf_popup_data_uniq_id . '" style="z-index:100000;">';
                        $arf_form .= '<div class="arform_bottom_fixed_form_block_top_main" style="display:none;">';
                        $arf_form .= '<div id="popup-form-' . $form->id . '"  aria-hidden="false" class="arform_bottom_fixed_form_block_top arf_pop_' . $arf_popup_data_uniq_id . '" style="display:block;' . $arf_modal_height . ' width: ' . ($modal_width) . 'px; left: 20%;z-index:9999;border:none;" >';
                    } else if ($position == 'left') {

                        $arf_form .= '<div id="arf-popup-form-' . $form->id . '" class="arf_flymodal arform_bottom_fixed_main_block_left arf_popup_' . $arf_popup_data_uniq_id . '" style="z-index:9999;">';

                        $arf_form .= '<div class="arform_bottom_fixed_block_left arf_fly_sticky_btn arform_modal_stickybottom_' . $form->id . '" onclick="open_modal_box_sitcky_left(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $checkradio_property . '\', \'' . $checked_checkbox_property . '\', \'' . $checked_radio_property . '\', \'' . $arf_popup_data_uniq_id . '\');" style="cursor:pointer; ">
                        <span href="#" data-toggle="arfmodal" title="' . $form_name . '" >' . $desc . '</span>
                    </div>';
                    $arf_form .= '<div style="clear:both;"></div>';

                    $arf_modal_height = ($modal_height=='auto') ? '' : 'max-height:'.$modal_height.'px;';

                    $arf_form .= '<div class="arform_bottom_fixed_form_block_left_main" style="float:left;  margin-left:-' . $modal_width . 'px">';
                    $arf_form .= '<div id="popup-form-' . $form->id . '" aria-hidden="false" class="arf_flymodal arform_bottom_fixed_form_block_left arf_pop_' . $arf_popup_data_uniq_id . '" style="display:block; ' . $arf_modal_height . ' width: ' . ($modal_width) . 'px; left: 20%;z-index:9999;  border:none;">';
                } else if ($position == 'right') {

                    $arf_form .= '<div id="arf-popup-form-' . $form->id . '" class="arf_flymodal arform_bottom_fixed_main_block_right arf_popup_' . $arf_popup_data_uniq_id . '" style="z-index:9999;">';
                    $arf_form .= '<div class="arform_bottom_fixed_block_right arf_fly_sticky_btn arform_modal_stickybottom_' . $form->id . '" onclick="open_modal_box_sitcky_right(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $checkradio_property . '\', \'' . $checked_checkbox_property . '\', \'' . $checked_radio_property . '\', \'' . $arf_popup_data_uniq_id . '\');" style="cursor:pointer;">
                    <span href="#" data-toggle="arfmodal" title="' . $form_name . '" >' . $desc . '</span>
                </div>';
                $arf_form .= '<div style="clear:both;"></div>';

                $arf_modal_height = ($modal_height=='auto') ? '' : 'max-height:'.$modal_height.'px;';

                $arf_form .= '<div class="arform_bottom_fixed_form_block_right_main" style="float:right; margin-right:-' . $modal_width . 'px"" >';
                $arf_form .= '<div id="popup-form-' . $form->id . '" aria-hidden="false" class="arf_flymodal arform_bottom_fixed_form_block_right arf_pop_' . $arf_popup_data_uniq_id . '" style="display:block;' . $arf_modal_height . ' width: ' . ($modal_width) . 'px; left: 20%;z-index:9999;border:none;">';
            } else {

                $arf_form .= '<div id="arf-popup-form-' . $form->id . '" class="arf_flymodal arform_bottom_fixed_main_block_bottom arf_popup_' . $arf_popup_data_uniq_id . '" style="z-index:10000;">';
                $arf_form .= '<div class="arform_bottom_fixed_block_bottom arf_fly_sticky_btn arform_modal_stickybottom_' . $form->id . '" onclick="open_modal_box_sitcky_bottom(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $checkradio_property . '\', \'' . $checked_checkbox_property . '\', \'' . $checked_radio_property . '\', \'' . $arf_popup_data_uniq_id . '\');" style="cursor:pointer;">
                <span href="#" data-toggle="arfmodal" title="' . $form_name . '" >' . $desc . '</span>
            </div>';
            $arf_modal_height = ($modal_height=='auto') ? '' : 'max-height:'.$modal_height.'px;';
            $arf_form .= '<div style="clear:both;"></div>';
            $arf_form .= '<div class="arform_bottom_fixed_form_block_bottom_main" style="display:none;">';
            $arf_form .= '<div id="popup-form-' . $form->id . '" aria-hidden="false" class="arf_flymodal arform_bottom_fixed_form_block_bottom arf_pop_' . $arf_popup_data_uniq_id . '" style="display:block;' . $arf_modal_height . ' width: ' . ($modal_width) . 'px; left: 20%;z-index:9999;border:none;">';
        }
    } else {
        $model_class = "";
        if( $is_fullscrn == 'yes' ){
            $model_class = "arfmodal-fullscreen";
        }
        $arf_modal_height = ($modal_height=='auto') ? '' : 'max-height:'.$modal_height.'px;';
        $arf_form .= '<div id="popup-form-' . $form->id . '" style="' . $arf_modal_height . ' width: ' . $modal_width . 'px; left: 20%;" aria-hidden="false" class="arfmodal arfhide arf_pop_' . $arf_popup_data_uniq_id . ' '.$class_modeleffect.' '.$model_class.' "  >';
    }

    $button_close_div = "";
    $inner_button_close_func = "";
    if ($type == 'fly') {
        if ($position == 'right') {
            $inner_button_close_func = 'open_modal_box_fly_left_move(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $arf_popup_data_uniq_id . '\');';
            $arf_form .= '<button id="open_modal_box_fly_right_' . $form->id . '" onclick="open_modal_box_fly_left_move(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $arf_popup_data_uniq_id . '\');" data-toggle="arfmodal" title="' . $form_name . '"  class="close_btn arf_close_btn_outer" data-poup-unique-id="'.$arf_popup_data_uniq_id.'" type="button" style="background: transparent !important;border: none !important;margin-right:1px; z-index:9999;"></button>';
        } else {
            $inner_button_close_func = 'open_modal_box_fly_right_move(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $arf_popup_data_uniq_id . '\');';
            $arf_form .= '<button id="open_modal_box_fly_left_' . $form->id . '" onclick="open_modal_box_fly_right_move(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $arf_popup_data_uniq_id . '\');" class="close_btn arf_close_btn_outer" data-poup-unique-id="'.$arf_popup_data_uniq_id.'" type="button" style="background: transparent !important;border: none !important;margin-right:1px;z-index:9999;"></button>';
        }
    } else if ($type != 'sticky') {
        $display_button = ($is_close_link == 'no') ? 'display:none;' : '';
        $arf_close_btn_class = "";
        if( $is_fullscrn == 'yes' ){
            $arf_close_btn_class = "arf_full_screen_close_btn";
        }
        $button_close_div = '<button data-modalcolor="'.$modal_bgcolor.'" data-modal-overlay="'.$overlay_value.'" data-form-id="'.$form->id.'" data-poup-unique-id="'.$arf_popup_data_uniq_id.'" class="close_btn arf_close_btn_outer '.$arf_close_btn_class.'" type="button" style="background: transparent !important;border: none !important;margin-right:15px; margin-top:15px; z-index:9999; ' . $display_button . ' " id="arf_popup_close_button"></button>';
        //$arf_form .= '<button data-modalcolor="'.$modal_bgcolor.'" data-modal-overlay="'.$overlay_value.'" data-form-id="'.$form->id.'" data-poup-unique-id="'.$arf_popup_data_uniq_id.'" class="close_btn arf_close_btn_outer '.$arf_close_btn_class.'" type="button" style="background: transparent !important;border: none !important;margin-right:15px; margin-top:15px; z-index:9999; ' . $display_button . ' " id="arf_popup_close_button"></button>';
    }

    $arfmodalbodypadding = '0';
    
    $hide_form_class = ($is_hide_form_after_submit != false) ? 'arf_hide_form_after_submit' : '';
    $arf_form .= '<div class="arfmodal-body '.$hide_form_class.'" style="padding:' . $arfmodalbodypadding . ';">';

    $arf_form .= $button_close_div;
}






/* arf_dev_flag => two queries */
if (!$preview) {
    $arformcontroller->arf_create_visit_entry($form->id);
}



$page_num = isset($values['total_page_break']) ? $values['total_page_break'] : 0;



global $arf_total_page_break;
$arf_total_page_break = $page_num;

if ($page_num > 0) {
    $temp_calss = 'arfpagebreakform';
} else {
    $temp_calss = '';
}




//if($preview==1){

$arf_form .= $arformcontroller->arf_get_form_style($id,$arf_data_uniq_id, $type, $position, $bgcolor, $txtcolor, $btn_angle, $modal_bgcolor, $overlay,$is_fullscrn, $inactive_min, $model_effect);



$arf_form .= '<div class="arf_form ar_main_div_' . $form->id . ' arf_form_outer_wrapper " id="arffrm_' . $form->id . '_container">';

if( $type != "" && $type != 'sticky' && $is_fullscrn != 'yes' ){
    $arf_form .= "<style type='text/css'>.arf_close_btn_outer[data-poup-unique-id='{$arf_popup_data_uniq_id}']{display:none !important;}</style>";
    $arf_form .= '<button data-modalcolor="'.$modal_bgcolor.'" onclick="'.$inner_button_close_func.'" data-modal-overlay="'.$overlay_value.'" data-form-id="'.$form->id.'" data-poup-unique-id="'.$arf_popup_data_uniq_id.'" class="close_btn arf_close_btn_inner '.((isset($arf_close_btn_class) && $arf_close_btn_class =!'')?$arf_close_btn_class:'').'" type="button" style="background: transparent !important;border: none !important;margin-right:15px; margin-top:15px; z-index:9999; ' . ((isset($display_button) && $display_button =!'')?$display_button:'') . ' " id="arf_popup_close_button"></button>';
}

$arf_form = apply_filters('arf_predisplay_form', $arf_form, $form);
do_action('arf_predisplay_form' . $form->id, $form);


if (isset($preview) and $preview) {
    $arf_form .= '<div id="form_success_' . $form->id . '" style="display:none;">' . $saved_message . '</div>';
}



/* arf_dev_flag consider action `arfformclasses` */


$form_attr = '';
$formRandomID = $form->id.'_'.$armainhelper->arf_generate_captcha_code('10');

$captcha_code = $armainhelper->arf_generate_captcha_code('8');

if (!isset($_SESSION['ARF_FILTER_INPUT'])) {
   $_SESSION['ARF_FILTER_INPUT'] = array();
}
if (isset($_SESSION['ARF_FILTER_INPUT'][$formRandomID])) {
	  //  unset($_SESSION['ARF_FILTER_INPUT'][$formRandomID]);
}
$_SESSION['ARF_VALIDATE_SCRIPT'] = true;
$_SESSION['ARF_FILTER_INPUT'][$formRandomID] = $captcha_code;

$form_attr .= ' data-random-id="' . $formRandomID . '" ';
$form_attr .= ' data-submission-key="' . $captcha_code . '" ';

if( isset($arf_modal_loaded) and $arf_modal_loaded )
    $arf_form .='<div class="arf_content_another_page" style="display:none;"></div>';

$arf_form .= $saved_popup_message;

$is_hide_form = "";
if($func_val != '' && $navigation){
    $is_hide_form = "display:none;";
    $error_restrict_entry = json_decode($func_val);
    $arf_form .= $error_restrict_entry->message;
}

if (isset($preview) and $preview) {
    $arf_form .= '<form enctype="' . apply_filters('arfformenctype', 'multipart/form-data', $form) . '" method="post" class="arfshowmainform arfpreivewform ' . $temp_calss . ' ' . do_action('arfformclasses', $form) . ' " data-form-id="form_' . $form->form_key . '" novalidate="" data-id="' . $arf_data_uniq_id . '" data-popup-id="' . $arf_popup_data_uniq_id . '" "'.$form_attr.'">';
} else {
    $action_html = ($arfsettings->use_html) ? '' : 'action=""';
    $arf_form .= '<form enctype="' . apply_filters('arfformenctype', 'multipart/form-data', $form) . '" method="post" class="arfshowmainform ' . $temp_calss . ' ' . do_action('arfformclasses', $form) . '" style="'.$is_hide_form.'" data-form-id="form_' . $form->form_key . '" ' . $action_html . ' novalidate="" data-id="' . $arf_data_uniq_id . '" '. $form_attr ;
    if ($type != '') {
        $arf_form .=' data-popup-id="' . $arf_popup_data_uniq_id . '">';
    } else {
        $arf_form .=' data-popup-id="">';
    }
}

$arf_form .= $arfieldhelper->get_form_pagebreak_fields($form->id,$form->form_key,$values);

$arf_form .= "<input type='text' name='arf_filter_input' data-jqvalidate='false' data-random-key='{$formRandomID}' value='' style='opacity:0 !important;display:none !important;visibility:hidden !important;' />";
$arf_form .= "<input type='hidden' id='arf_ajax_url' value='".admin_url('admin-ajax.php')."' />";
$arf_form .=  do_shortcode('[arf_spam_filters]');

/* arf_dev_flag =>  $form_action passed fixed value */
$form_action = 'create';
$loaded_field = isset( $form->options['arf_loaded_field'] ) ? $form->options['arf_loaded_field'] : array();
$arf_form .= $arformcontroller->arf_get_form_hidden_field($form, $fields, $values, $preview , $is_widget_or_modal, $arf_data_uniq_id, $form_action, $loaded_field, $type, $is_close_link);



$arf_form .='<div class="allfields"  style="visibility:hidden;height:0;">';


/* arf_dev_flag => loop and seperate function */
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
$arf_form .= '<div class="arf_fieldset ' . $form_class . '" id="arf_fieldset_' . $arf_data_uniq_id . '">';



$arf_form .= $arformcontroller->arf_load_form_css($form->id,$inputStyle);


/* arf_dev_flag =>"Old method have function I have removed . not necessary may be. there was filter `arfformreplaceshortcodes` which need to consider or not o_O" */
if (isset($form->options['display_title_form']) && $form->options['display_title_form'] == 1) {

    $arf_form .='<div class="arftitlecontainer">';

    if (isset($form->name) && $form->name != '') {
        $arf_form .='<div class="formtitle_style">' . html_entity_decode( stripslashes($form->name) ) . '</div>';
    }
    if (isset($form->description) && $form->description != '') {
        $arf_form .='<div class="arf_field_description formdescription_style">' . html_entity_decode( stripslashes($form->description) ) . '</div>';
    }

    $arf_form .= '</div>';
}

$is_recaptcha = 0;


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
            //$arf_form .= '<div class="arf_survey_nav"><div id="current_survey_page" class="survey_step">' . addslashes(esc_html__('Step', 'ARForms')) . ' </div><div id="current_survey_page" class="current_survey_page">1</div><div class="survey_middle">' . addslashes(esc_html__('of', 'ARForms')) . '</div><div id="total_survey_page" class="total_survey_page">' . ($total_page_shows + 1) . '</div></div>';

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
                    $display_page_break = "";

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
                        $display = '';

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

        /* if page break than get page break tab end */


        /* get all field html */
        $arf_form .='<div id="page_0" class="page_break">';

        //preset_data='Item_meta_395||test~!~Item_meta_396||a4^!^a3^!^a2~!~Item_meta_397||test2'
        if(isset($arf_preset_data)){
           
            $arf_arr_preset_data = array();
            $arf_preset_data_new = explode('~!~',$arf_preset_data);
                   
            foreach ($arf_preset_data_new as $key => $value) {
                
                $arf_preset_data_final   = explode('||', $value);
                $arf_preset_data_id      = str_replace('item_meta_', '', $arf_preset_data_final[0]);

                if(isset($arf_preset_data_final[1]) && preg_match("^!^",$arf_preset_data_final[1])){

                    $arf_preset_data_final[1] = explode("^!^", $arf_preset_data_final[1]);
                }
                $arf_preset_data_value   = isset($arf_preset_data_final[1]) ? $arf_preset_data_final[1] : '';
                $arf_arr_preset_data[$arf_preset_data_id] = $arf_preset_data_value;
            }
        }
        
        $arf_form .= $arformcontroller->get_all_field_html($form,$values,$arf_data_uniq_id,$fields,$preview,$errors,$inputStyle,$arf_arr_preset_data);

        $captcha_key = $arformcontroller->arfSearchArray('captcha','type',$values['fields']);
        if( '' != $captcha_key ){
            $is_recaptcha = 1;
        }


        /* if section started than end it */
        global $arf_section_div;
        if ($arf_section_div) {
            $arf_form .= "<div class='arf_clear'></div></div>";
            $arf_section_div = 0;
        }

        /* arf_dev_flag action to filter conversion affects paypalpro addon authorise.net addon */
        $arf_form = apply_filters('arfentryform', $arf_form, $form, $form_action, $errors);
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
                    $arf_form .= '<input type="hidden" data-jqvalidate="false" value="' . $arf_page_number . '" name="last_page_id" data-id="last_page_id"  />';
                }

                if ($arf_page_number > 0 and $page_num > 0) {
                    $arf_form .= '<input type="hidden" value="1" data-jqvalidate="false" name="is_submit_form_' . $form->id . '" data-id="is_submit_form_' . $form->id . '" />';
                    $arf_form .= '<input type="hidden" data-jqvalidate="false" data-last="' . $last_show_page . '" value="' . $last_show_page . '" name="last_show_page_' . $form->id . '" data-id="last_show_page_' . $form->id . '" />';
                    $arf_form .= '<input type="hidden" data-jqvalidate="false" value="' . $is_submit_form . '" data-val="1" data-hide="' . $page_break_hidden_array[$form->id]['data-hide'] . '" data-max="' . $arf_page_number . '" name="submit_form_' . $form->id . '" data-id="submit_form_' . $form->id . '" />';
                    $arf_form .= '<input type="hidden" data-jqvalidate="false" value="' . $page_break_hidden_array[$form->id]['data-hide'] . '" name="get_hidden_pages_' . $form->id . '" data-id="get_hidden_pages_' . $form->id . '" />';
                } else {
                    $arf_form .= '<input type="hidden" data-jqvalidate="false" value="1" name="is_submit_form_' . $form->id . '" data-id="is_submit_form_' . $form->id . '" />';
                    $arf_form .= '<input type="hidden" data-jqvalidate="false" value="0" data-val="0" data-max="0" name="submit_form_' . $form->id . '" data-id="submit_form_' . $form->id . '" />';
                }

                $submit = apply_filters('getsubmitbutton', $submit, $form);
                $is_submit_hidden = false;
                $submitbtnstyle = '';
                $submitbtnclass = '';
                
                $sbmt_class = "";
                if( $inputStyle == 'material' ){
                    $sbmt_class = "btn btn-flat";
                }
                $arfbrowser_name = strtolower(str_replace(' ','_',$browser_info['name']));
                $submit_btn_content = '<button class="arf_submit_btn  arfstyle-button '.$sbmt_class.' ' . $submitbtnclass . ' '.$arfbrowser_name.'" id="arf_submit_btn_' . $arf_data_uniq_id . '" name="arf_submit_btn_' . $arf_data_uniq_id . '" data-style="zoom-in" ' . $submitbtnstyle;
                $submit_btn_content = apply_filters('arf_add_submit_btn_attributes_outside', $submit_btn_content, $form);
                $submit_btn_content .= ' ><span class="arfsubmitloader"></span><span class="arfstyle-label">' . esc_attr($submit) . '</span><span class="arf_ie_image" style="display:none;">';
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
                $sbtm_wrapper_class = "";
                if( $inputStyle == 'material' ){
                    $sbtm_wrapper_class = "file-field ";
                }
                $arf_form .= '<div class="arf_submit_div '.$sbtm_wrapper_class.' ' . $_SESSION['label_position'] . '_container">';
                if ($arf_page_number > 0 and $page_num > 0) {
                    $arf_form .= '<input type="button" value="' . $field_pre_page_title . '" ' . $display_previous . ' name="previous" data-id="previous_last" class="previous_btn" onclick="go_previous(\'' . ($arf_page_number - 1) . '\', \'' . $form->id . '\', \'no\', \'' . $form->form_key . '\', \'' . $arf_data_uniq_id . '\');"  />';
                    $arf_form .= '<input type="hidden" data-jqvalidate="false" value="' . $arf_page_number . '" name="last_page_id" data-id="last_page_id" />';
                }

                if ($arf_page_number > 0 and $page_num > 0) {
                    $arf_form .= '<input type="hidden" data-jqvalidate="false" value="1" name="is_submit_form_' . $form->id . '" data-id="is_submit_form_' . $form->id . '" />';
                    $arf_form .= '<input type="hidden" data-jqvalidate="false" data-last="' . $last_show_page . '" value="' . $last_show_page . '" name="last_show_page_' . $form->id . '" data-id="last_show_page_' . $form->id . '" />';
                    $arf_form .= '<input type="hidden" data-jqvalidate="false" value="' . $is_submit_form . '" data-val="1" data-hide="' . $page_break_hidden_array[$form->id]['data-hide'] . '" data-max="' . $arf_page_number . '" name="submit_form_' . $form->id . '" data-id="submit_form_' . $form->id . '" />';
                    $arf_form .= '<input type="hidden" data-jqvalidate="false" value="' . $page_break_hidden_array[$form->id]['data-hide'] . '" name="get_hidden_pages_' . $form->id . '" data-id="get_hidden_pages_' . $form->id . '" />';
                } else {
                    $arf_form .= '<input type="hidden" data-jqvalidate="false" value="1" name="is_submit_form_' . $form->id . '" data-id="is_submit_form_' . $form->id . '" />';
                    $arf_form .= '<input type="hidden" data-jqvalidate="false" value="0" data-val="0" data-max="0" name="submit_form_' . $form->id . '" data-id="submit_form_' . $form->id . '" />';
                }

                $submit = apply_filters('getsubmitbutton', $submit, $form);
                $is_submit_hidden = false;
                $submitbtnstyle = '';
                $submitbtnclass = '';
                
                $submit_btn_content = '';

                $sbmt_class = "";
                if( $inputStyle == 'material' ){
                    $sbmt_class = "btn btn-flat";
                }
                

                $arfbrowser_name = strtolower(str_replace(' ','_',$browser_info['name']));
                $submit_btn_content .= '<button class="arf_submit_btn '.$sbmt_class.' btn-info arfstyle-button ' . $submitbtnclass .' '.$arfbrowser_name.'"  id="arf_submit_btn_' . $arf_data_uniq_id . '" name="arf_submit_btn_' . $arf_data_uniq_id . '" data-style="zoom-in" ';
                
                $submit_btn_content = apply_filters('arf_add_submit_btn_attributes_outside', $submit_btn_content, $form);

                $submit_btn_content .= $submitbtnstyle . ' >';

                $submit_btn_content .= '<span class="arfsubmitloader"></span><span class="arfstyle-label">' . esc_attr($submit) . '</span>';
                if (( $browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '9' ) || $browser_info['name'] == 'Opera') {
                    $padding_loading = isset($padding_loading) ? $padding_loading : '';
                    $submit_btn_content .= '<span class="arf_ie_image" style="display:none;">';
                    $submit_btn_content .= '<img src="' . ARFURL . '/images/submit_btn_image.gif" style="width:24px; box-shadow:none;-webkit-box-shadow:none;-o-box-shadow:none;-moz-box-shadow:none; vertical-align:middle; height:24px; padding-top:' . $padding_loading . 'px;"/>';
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
            $arf_form = apply_filters('arfactionsubmitbutton', $arf_form, $form, $form_action);
            $arf_form .= '/>';
            $arf_form .= '<div id="submit_loader" class="submit_loader" style="display:none;"></div></p>';
        }
        /**         * page break another setting */
        /* arf_dev_flag we can use global variable of global settings */
        if ($field_page_break_type == 'survey' && $field_page_break_type_possition=='bottom') {

            $total_page_shows = $page_num;
            if($field_page_break_top_bar != 1) {
                //$arf_form .= '<div style="clear:both; margin-top:25px;"></div><div class="arf_survey_nav"><div id="current_survey_page" class="survey_step">' . esc_html__('Step', 'ARForms') . ' </div><div id="current_survey_page" class="current_survey_page">1</div><div class="survey_middle">' . esc_html__('of', 'ARForms') . '</div><div id="total_survey_page" class="total_survey_page">' . ($total_page_shows + 1) . '</div></div>';

                $arf_form .= '<div style="clear:both; margin-top:25px;"></div><div class="arf_survey_nav"><div id="current_survey_page" class="survey_step">' . sprintf(addslashes(esc_html__('Step %s of %s', 'ARForms')),'</div><div id="current_survey_page" class="current_survey_page">1</div><div class="survey_middle">', '</div><div id="total_survey_page" class="total_survey_page">' . ($total_page_shows + 1) . '</div></div>');

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
                            $display_page_break = "";

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
                                $display = '';

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

        $mybrand = isset($arfoptions->brand) ? $arfoptions->brand : '' ;

        $doliact = 0;
        global $valid_wp_version;
        global $arfmsgtounlicop;
        $doliact = $arformcontroller->$valid_wp_version();

	if($doliact == 0)
	{
	  $mybrand = 0;
	}
	
        $my_aff_code = "";

        if (!isset($arfoptions->affiliate_code) || $arfoptions->affiliate_code == "")
            $my_aff_code = "reputeinfosystems";
        else
            $my_aff_code = $arfoptions->affiliate_code;

        if ($mybrand == 0) {

            $arf_form .='<div id="brand-div" class="brand-div ' . $_SESSION['label_position'] . '_container" style="margin-top:30px; font-size:12px !important; color: #444444 !important; display:block !important; visibility: visible !important;">' . addslashes(esc_html__('Powered by', 'ARForms')) . '&#32;';
            if(is_ssl()) {
                $arf_form .='<a href="https://codecanyon.net/item/arforms-exclusive-wordpress-form-builder-plugin/6023165?ref=' . $my_aff_code . '" target="_blank" style="color:#0066cc !important; font-size:12px !important; display:inline !important; visibility:visible !important;">ARForms</a>';
            } else {
                 $arf_form .='<a href="http://codecanyon.net/item/arforms-exclusive-wordpress-form-builder-plugin/6023165?ref=' . $my_aff_code . '" target="_blank" style="color:#FF0000 !important; font-size:12px !important; display:inline !important; visibility:visible !important;">ARForms</a>';
            }
            $setlicval = 0;

            $setlicval = 0;
            global $valid_wp_version;
            global $arfmsgtounlicop;
            $setlicval = $arformcontroller->$valid_wp_version();

            if ($setlicval == 0) {
                $arf_form .='<span style="color:#FF0000 !important; font-size:12px !important; display:inline !important; visibility: visible !important;">' . addslashes(__('&nbsp;&nbsp;' . $arfmsgtounlicop, 'ARForms')) . '</span>';
            }
            $arf_form .='</div>';
        }


        $arf_form .='</div><!-- arf_fieldset -->';
        $arf_form = apply_filters('arf_additional_form_content_outside',$arf_form,$form,$arf_data_uniq_id,$arfbrowser_name,$browser_info);
        $arf_form .='</div><!-- allfields -->';
        /* get all fields end */


        $arf_form .='</form>';
        
        /* actual from end */
        $form = apply_filters('arfafterdisplayform', $form);
        
        $arf_logic = $form->options['arf_conditional_logic_rules'];
        $arf_submit_logic = isset($form->options['submit_conditional_logic']) ? $form->options['submit_conditional_logic'] : array();
        $arf_cl = "";
        $arf_pages_field_array = array();
        if (isset($arf_logic) && is_array($arf_logic) && !empty($arf_logic)) {

            $arf_conditional_logic_loaded[$form->id] = 1;
            $page_no = 0;
            $arf_field_array = array();
            /* arf_dev_flag query */
            
            if( !isset($GLOBALS['form_fields'][$form->id]) ){
               $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->fields . " WHERE form_id = %d ORDER BY id", $form->id));
           } else {
               $res = $GLOBALS['form_fields'][$form->id];
           }

           
           foreach ($res as $data) {
            if ($data->type == 'break') {
                $page_no++;
            }
            $fid = $data->id;
    		if( is_array($data->field_options) ){    
    		    $field_options =  $data->field_options;
    		} else {
    		    $field_options = json_decode( $data->field_options, true );
    	            if( json_last_error() != JSON_ERROR_NONE ){
    	                $field_options = maybe_unserialize($data->field_options);
    	            }
    		}
            $default_value_temp = apply_filters('arf_replace_default_value_shortcode',$field_options['default_value'],$field_options,$form);
    		if( isset($field_options['type']) && $field_options['type'] == 'arfslider' ){
                if( $field_options['arf_range_selector'] == 1  ){
                    $default_value_temp = array((double)$field_options['arf_range_minnum'],(double)$field_options['arf_range_maxnum']);
                } else {
                    $default_value_temp = (double)$field_options['slider_value'];
                }
            }
		    $arf_field_array[$fid] = array("page_no" => $page_no, "field_key" => $data->field_key, "default_value" => $default_value_temp);
            
        }

        $form_cols=$res;
        $field_order = json_decode($form->options['arf_field_order'],true);
        $new_form_cols = array();

        asort($field_order);
        $hidden_fields = array();
        $hidden_field_ids = array();
        foreach ($field_order as $field_id => $order) {
            if(is_int($field_id))
            {
                foreach ($form_cols as $field) {
                    if ($field_id == $field->id) {
                        $new_form_cols[] = $field;
                    } 
                }
            }
        }
        $pageno=0;
        $heading_id=0;
        $form_cols = $new_form_cols;
        foreach ($form_cols as $data1) {
            if ($data1->type == 'break') {
                $pageno++;
            }
           
            if ($data1->type == 'divider') {
                $heading_id=$data1->id;
            }
            if( is_array($data1->field_options) ){    
                $field_options1 =  $data1->field_options;
            } else {
                $field_options1 = json_decode( $data1->field_options, true );
                    if( json_last_error() != JSON_ERROR_NONE ){
                        $field_options1 = maybe_unserialize($data1->field_options);
                    }
            }
            $default_value_temp1 = apply_filters('arf_replace_default_value_shortcode',$field_options1['default_value'],$field_options1,$form);
            if( isset($field_options1['type']) && $field_options1['type'] == 'arfslider' ){
                if( $field_options1['arf_range_selector'] == 1  ){
                    $default_value_temp1 = array((double)$field_options1['arf_range_minnum'],(double)$field_options1['arf_range_maxnum']);
                } else {
                    $default_value_temp1 = (double)$field_options1['slider_value'];
                }
            }
            $arf_pages_field_array[]=array("field_id"=>$data1->id,"field_key" => $data1->field_key,"default_value" => $default_value_temp1,"field_type"=>$field_options1['type'],"page_no" => $pageno,"heading_id"=>$heading_id);
        }


            $arf_cl = "";
              $arf_cl_data = new stdClass();
              $arf_cl_fields = array();
              $arf_cl_dependents = array();
              $arf_cl_defaults = array();
              
              foreach ($arf_logic as $key => $rule) {
                    $results = $rule['result'];
                    $logicType = (isset($rule['logical_operator']) && $rule['logical_operator'] == 'and') ? 'all' : 'any';
                    
                    foreach ($results as $rK => $result) {
                        $conditions = $rule['condition'];
                        $arf_cl_condition = array();
                        $arf_submit_cl_condition = array();
                        foreach ($conditions as $cK => $condition) {
                            $field_key_val = isset($arf_field_array[$condition['field_id']]['field_key']) ? $arf_field_array[$condition['field_id']]['field_key'] : '';
                            $arf_cl_condition[] = array(
                                'fieldId' => $condition['field_id'],
                                'operator' => $condition['operator'],
                                'value' => $condition['value'],
                                'fieldType' => $condition['field_type'],
                                'fieldKey' => $field_key_val
                                );
                        }
                        $field_defalt_val = isset($arf_field_array[$result['field_id']]['default_value']) ? $arf_field_array[$result['field_id']]['default_value'] : '';
                        $result_field_opt = isset($arf_field_array[$result['field_id']]) ? $arf_field_array[$result['field_id']] : '';
                        $field_defalt_val = apply_filters('arf_replace_default_value_shortcode',$field_defalt_val,$result_field_opt,$form);

                        
                        if( $result['field_id'] == '' ) { continue; }
                        if( !isset($arf_cl_fields[$result['field_id']]) ){
                            $arf_cl_fields[$result['field_id']] = array();
                        }
                        $arf_cl_fields[$result['field_id']]['fields'][] = array(
                            'actionType' => $result['action'],
                            'logicType' => $logicType,
                            'field_key' => isset($arf_field_array[$result['field_id']]['field_key']) ? $arf_field_array[$result['field_id']]['field_key'] : '',
                            'value' => isset($result['value']) ? $result['value'] : '',
                            'default_value' => $field_defalt_val,
                            'field_type' => $result['field_type'],
                            'page_no' => isset($arf_field_array[$result['field_id']]['page_no']) ? $arf_field_array[$result['field_id']]['page_no'] : '',
                            'rules' => $arf_cl_condition
                        );

                        /* arf_dev_flag : Dependent fields logic need to change while having section and page break in form */
                        $arf_cl_dependents[$result['field_id']][] = (int) $result['field_id'];
                    }
                    
                    if( isset($arf_submit_logic) && is_array($arf_submit_logic) && !empty($arf_submit_logic) && $arf_submit_logic['enable'] == 1 ){

                        foreach( $arf_submit_logic['rules'] as $arf_submit_rules ){
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
                        $submit_action = ($arf_submit_logic['display'] == 'Enable' || $arf_submit_logic['display'] == 'show')? 'show' : 'hide';
                        $arf_cl_fields['submit']['fields'][] = array(
                            'actionType' => $submit_action,
                            'logicType' => $arf_submit_logic['if_cond'],
                            'field_key' => '',
                            'value' => '',
                            'default_value' => '',
                            'field_type' => 'submit',
                            'page_no' => isset($arf_field_array[$result['field_id']]['page_no']) ? $arf_field_array[$result['field_id']]['page_no'] : '',
                            'rules' => $arf_submit_cl_condition
                            );
                    }
                }
            
            $arf_cl_data->logic = $arf_cl_fields;
            $arf_cl_data->dependents = $arf_cl_dependents;
            $arf_cl_data->defaults = $arf_cl_defaults;
            
            $arf_cl .= "<script type='text/javascript' data-cfasync='false'>";
            $arf_cl .= "if(!window['arf_conditional_logic']){window['arf_conditional_logic'] = new Array();}";
            
            $arf_cl .= "window['arf_conditional_logic'][{$arf_data_uniq_id}] = " . json_encode($arf_cl_data,JSON_UNESCAPED_UNICODE) . ";" ;

            $arf_cl .= "if(!window['arf_pages_fields']){window['arf_pages_fields'] = new Array();}";

            $arf_cl .= "window['arf_pages_fields'][{$arf_data_uniq_id}] = " . json_encode($arf_pages_field_array,JSON_UNESCAPED_UNICODE) . ";" ;
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
        if ($type != '') {
            $arf_form .= '</div>';
            $arf_form .= '</div>';
            if ($type == 'sticky') {
                $arf_form .= '</div>';
                if ($position == 'top') {
                    $arf_form .= '<div style="clear:both;"></div>';
                    $arf_form .= '<div class="arform_bottom_fixed_block_top arf_fly_sticky_btn arform_modal_stickytop_' . $form->id . '" onclick="open_modal_box_sitcky_top(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $checkradio_property . '\', \'' . $checked_checkbox_property . '\', \'' . $checked_radio_property . '\', \'' . $arf_popup_data_uniq_id . '\');" style="cursor:pointer;"><span href="#" data-toggle="arfmodal" title="' . $form_name . '">' . $desc . '</span></div>';
                }
                $arf_form .= '</div>';
            } elseif ($type == 'fly') {
                $arf_form .= '</div>';
                $arf_form .= '</div>';
            }

            if ($type == 'sticky' && $position == 'left') {

                $arf_form_all_footer_js .='var winodwHeight = jQuery(window).height();
                var modal_height_left = "' . $modal_height . '";
                
                /*jQuery(".arform_bottom_fixed_main_block_left").css("top", Number(Number(winodwHeight) - Number(modal_height_left)) / Number(2));*/

                jQuery("#arf-popup-form-' . $form->id . ' .arform_bottom_fixed_block_left").parents(".arform_bottom_fixed_main_block_left").find(".arform_bottom_fixed_form_block_left_main").css("margin-top", "-35px");
                jQuery("#arf-popup-form-' . $form->id . '.arform_bottom_fixed_main_block_left").css("display", "inline-block");
                jQuery(".arf_popup_' . $arf_popup_data_uniq_id . '").find(".arform_modal_stickybottom_' . $form->id . '").css("transform-origin", "left top");';
            }
            if ($type == 'sticky' && $position == 'right') {

                $arf_form_all_footer_js .='  var winodwHeight = jQuery(window).height();
                var modal_height_right = "' . $modal_height . '";
                /*jQuery(".arform_bottom_fixed_main_block_right").css("top", Number(Number(winodwHeight) - Number(modal_height_right)) / Number(2));*/
                jQuery("#arf-popup-form-' . $form->id . ' .arform_bottom_fixed_block_right").parents(".arform_bottom_fixed_main_block_right").find(".arform_bottom_fixed_form_block_right_main").css("margin-top", "-35px");
                jQuery("#arf-popup-form-' . $form->id . '.arform_bottom_fixed_main_block_right").css("display", "inline-block");
                jQuery(".arf_popup_' . $arf_popup_data_uniq_id . '").find(".arform_modal_stickybottom_' . $form->id . '").css("transform-origin", "right top");';
            }
        }

        $arf_form .= '<div class="brand-div"></div><div class=""><input type="hidden" data-jqvalidate="false" name="form_id" data-id="form_id" value="' . $form->id . '" /><input type="hidden" data-jqvalidate="false" name="arfmainformurl" data-id="arfmainformurl" value="' . ARFURL . '" /></div>';

        
        
        if($is_recaptcha==1){
            $arf_form .= "<input type='hidden' id='arf_settings_recaptcha_v2_public_key' value='{$arfsettings->pubkey}' />";
            $arf_form .= "<input type='hidden' id='arf_settings_recaptcha_v2_public_theme' value='{$arfsettings->re_theme}' />";
            $arf_form .= "<input type='hidden' id='arf_settings_recaptcha_v2_public_lang' value='{$arfsettings->re_lang}' />";    
        }
        
               
        
        if( $home_preview == true ){
            $wp_upload_dir = wp_upload_dir();
            $dest_css_url = $wp_upload_dir['baseurl'] . '/arforms/maincss/';
            if ($inputStyle == 'material') {
                $arf_form .= "<script type='text/javascript' data-cfasync='false' src='" . ARFURL . "/materialize/materialize.js' ></script>";
                $arf_form .= "<link rel='stylesheet' type='text/css' href='" . ARFURL . "/materialize/materialize.css' />";
                if (is_ssl()) {
                  $fid_material = str_replace("http://", "https://", $dest_css_url . '/maincss_materialize_' . $form->id . '.css');
                } else {
                  $fid_material = $dest_css_url . '/maincss_materialize_' . $form->id . '.css';
                }
                $arf_form .= "<link rel='stylesheet' type='text/css' href=".$fid_material." />";
            } else {
                if (is_ssl()) {
                  $fid = str_replace("http://", "https://", $dest_css_url . '/maincss_' . $form->id . '.css');
                } else {
                  $fid = $dest_css_url . '/maincss_' . $form->id . '.css';
                }
                $arf_form .= "<link rel='stylesheet' type='text/css' href=".$fid." />";
            }
        }


        /** if tooltip loaded than append its js */
        if ( isset($form->options['tooltip_loaded']) && $form->options['tooltip_loaded']) {
            $arf_tootip_width = (isset($form->form_css['arf_tooltip_width']) && $form->form_css['arf_tooltip_width']!='') ? $form->form_css['arf_tooltip_width'] : 'auto';
            $arf_tooltip_position = (isset($form->form_css['arf_tooltip_position']) && $form->form_css['arf_tooltip_position']!='') ? $form->form_css['arf_tooltip_position'] : 'top';
            $arf_form_all_footer_js .= '
            if (jQuery.isFunction(jQuery().tipso)) {
                jQuery(".ar_main_div_' . $form->id . '").find(".arfhelptip").each(function () {
                    jQuery(this).tipso("destroy");
                    var title = jQuery(this).attr("data-title");
                    jQuery(this).tipso({
                        position: "' . $arf_tooltip_position . '",
                        width: "' . $arf_tootip_width . '",
                        useTitle: false,
                        content: title,
                        background: "' . str_replace('##', '#', $form->form_css['arf_tooltip_bg_color']) . '",
                        color:"' . str_replace('##', '#',$form->form_css['arf_tooltip_font_color']) . '",
                        tooltipHover: true
                    });
                });
            }';
            
        if ($inputStyle == 'material') {
            $arf_form_all_footer_js .= '
            if (jQuery.isFunction(jQuery().tipso)) {
                jQuery(".ar_main_div_' . $form->id . ' .arfshowmainform[data-id='.$arf_data_uniq_id.'] .arf_materialize_form .arfhelptipfocus input,.ar_main_div_' . $form->id . ' .arfshowmainform[data-id='.$arf_data_uniq_id.'] .arf_materialize_form .arfhelptipfocus textarea").on( "focus", function(e){
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

                jQuery(".ar_main_div_' . $form->id . ' .arfshowmainform[data-id='.$arf_data_uniq_id.'] .arf_materialize_form .arfhelptipfocus input,.ar_main_div_' . $form->id . ' .arfshowmainform[data-id='.$arf_data_uniq_id.'] .arf_materialize_form .arfhelptipfocus textarea").focusout( function(e){
                    jQuery(this).parent().each(function () {
                        var arf_data_title = jQuery(this).attr("data-title");
                        if(arf_data_title!=null && arf_data_title!=undefined)
                        {
                            jQuery(this).tipso("hide");
                            jQuery(this).tipso("destroy");
                        }
                    });
                    
                });
                
            }';
        }
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
        if (isset($form_css_submit['arf_checked_checkbox_icon']) && $form_css_submit['arf_checked_checkbox_icon'] != "") {
            $checked_checkbox_property = ' arfa ' . $form_css_submit['arf_checked_checkbox_icon'];
        } else {
            $checked_checkbox_property = '';
        }
        $checked_radio_property = '';
        if (isset($form_css_submit['arf_checked_radio_icon']) && $form_css_submit['arf_checked_radio_icon'] != "") {
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
        });";
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
    return $arf_form;
}

}

?>