<?php
global $maincontroller, $arrecordcontroller, $arfversion, $is_arf_preview, $arsettingcontroller, $arformcontroller, $wpdb,$armainhelper,$arfieldhelper,$arrecordhelper;

$is_arf_preview = 1;

function my_function_admin_bar() {
    return false;
}
if( !isset($form) ){
    $form = new stdClass();
}
if( !isset($form->id) ){
    $form->id = $armainhelper->get_param('form_id');
}
$arf_data_uniq_id = rand(1, 99999);
if (empty($arf_data_uniq_id) || $arf_data_uniq_id == '') {
    $arf_data_uniq_id = $form->id;
}

add_filter('show_admin_bar', 'my_function_admin_bar');

remove_action('wp_head', 'wc_products_rss_feed');
remove_action('wp_head', 'wc_generator_tag');
remove_action('get_the_generator_html', 'wc_generator_tag');
remove_action('get_the_generator_xhtml', 'wc_generator_tag');
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
    <head>
        <script type="text/javascript" data-cfasync="false">
            var ajaxurl = '<?php echo get_admin_url() . "admin-ajax.php" ?>';
        </script>

        <meta charset="<?php bloginfo('charset'); ?>" />

        <meta http-equiv="pragma" content="no-cache">
        <meta http-equiv="Cache-control" content="no-cache,no-store,must-revalidate">
        <meta http-equiv="expires" content="0">
        <script type="text/javascript" data-cfasync="false">
            arf_actions = [];
            function arf_add_action( action_name, callback, priority ) {
                if ( ! priority )  {
                    priority = 10;
                }
                
                if ( priority > 100 ) {
                    priority = 100;
                } 
                
                if ( priority < 0 ) {
                    priority = 0;
                } 
                
                if ( typeof arf_actions[action_name] == 'undefined' ) {
                    arf_actions[action_name] = [];
                }
                
                if ( typeof arf_actions[action_name][priority] == 'undefined' ) {
                    arf_actions[action_name][priority] = []
                }
                
                arf_actions[action_name][priority].push( callback );
            }
            function arf_do_action() {
                if ( arguments.length == 0 ) {
                    return;
                }
                
                var args_accepted = Array.prototype.slice.call(arguments),
                    action_name = args_accepted.shift(),
                    _this = this,
                    i,
                    ilen,
                    j,
                    jlen;
                
                if ( typeof arf_actions[action_name] == 'undefined' ) {
                    return;
                }
                
                for ( i = 0, ilen=100; i<=ilen; i++ ) {
                    if ( arf_actions[action_name][i] ) {
                        for ( j = 0, jlen=arf_actions[action_name][i].length; j<jlen; j++ ) {
                            if( typeof window[arf_actions[action_name][i][j]] != 'undefined' ){
                                window[arf_actions[action_name][i][j]](args_accepted);
                            }
                        }
                    }
                }
            }
        </script>
        <title><?php bloginfo('name'); ?></title>
        <?php
        global $wp_version;
        if( version_compare($wp_version, '4.2', '<') ){
            wp_print_scripts('jquery-custom',ARFURL.'/js/jquery/compatibility_js/jquery.js');
            wp_print_scripts('jquery-ui-core-custom',ARFURL.'/js/jquery/compatibility_js/core.min.js');
            wp_print_scripts('jquery-ui-draggable-custom',ARFURL.'/js/jquery/compatibility_js/draggable.min.js');
        } else {
            wp_print_scripts('jquery');
            wp_print_scripts('jquery-ui-core');
            wp_print_scripts('jquery-ui-draggable');
        }
        ?>
        <style type="text/css">
            .ar_main_div div.allfields {
                padding:0 0 20px;
            }
            ::-webkit-scrollbar {
                width: 8px;
                height: 8px;
                background-color: #f5f5f5;
                border-radius: 10px;
            } 
            ::-webkit-scrollbar-thumb {
                background-color: #2e5fc7;
                border-radius: 10px;
            }

            ::-webkit-scrollbar-track {
                -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.0);
                background-color: #ffffff;
                border-radius:3px;
                -webkit-border-radius:3px;
                -moz-border-radius:3px;
                -o-border-radius:3px;
            }
        </style>
        <?php $maincontroller->front_head(); ?>
        <style type="text/css">
            input, select, textarea
            {
                outline:none;
            }
            body{ padding:50px 20px 60px; }
            .arf_form .arfpreivewform .arf_image_field.ui-draggable img { border:2px solid transparent !important; padding:2px !important; }
            .arf_form .arfpreivewform .arf_image_field.ui-draggable img:hover { border:2px dashed #077bdd !important; cursor:move !important; box-shadow: 0px 0px 4px rgba(0, 0, 0, 0.4) !important;-webkit-box-shadow: 0px 0px 4px rgba(0, 0, 0, 0.4) !important;-o-box-shadow: 0px 0px 4px rgba(0, 0, 0, 0.4) !important;-moz-box-shadow: 0px 0px 4px rgba(0, 0, 0, 0.4) !important; padding:2px !important; }
            .arf_form .arf_image_field.ui-draggable:hover img,
            .arf_form .arf_image_field.ui-draggable img:active,
            .arf_form .arf_image_field.ui-draggable img:focus,
            .arf_form .arf_image_field.ui-draggable img:hover { -webkit-border:2px dashed #077bdd !important; -moz-border:2px dashed #077bdd !important; border:2px dashed #077bdd !important; cursor:move !important; box-shadow: 0px 0px 4px rgba(0, 0, 0, 0.4) !important;-webkit-box-shadow: 0px 0px 4px rgba(0, 0, 0, 0.4) !important;-o-box-shadow: 0px 0px 4px rgba(0, 0, 0, 0.4) !important;-moz-box-shadow: 0px 0px 4px rgba(0, 0, 0, 0.4) !important; padding:2px !important; }
            .ar_main_div div.allfields {
                padding:0 0 20px;
            }
            pre{text-align:left;}
        </style>

        <style type="text/css" id='arf_form_<?php echo $form->id; ?>'>
        <?php
        $form->form_css = isset($form->form_css) ? maybe_unserialize($form->form_css) : '';
        $loaded_field = isset($form->options['arf_loaded_field']) ? $form->options['arf_loaded_field'] : array();
        echo stripslashes_deep(get_option('arf_global_css'));
        $form->options['arf_form_other_css'] = isset($form->options['arf_form_other_css']) ? $arformcontroller->br2nl($form->options['arf_form_other_css']) : '';
        
        echo $armainhelper->esc_textarea($form->options['arf_form_other_css']);
        $fields = $arfieldhelper->get_form_fields_tmp(false, $form->id, false, 0);
        $values = $arrecordhelper->setup_new_vars($fields, $form);
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
            if (in_array('arf_smiley', $loaded_field)) {
                echo '.arf_form .arf_smiley_container .popover { background-color: #000000 !important; color:#FFFFFF !important; width:auto; }
                    .arf_form .arf_smiley_container .popover .popover-content { color:#FFFFFF !important; }
                    .arf_form .arf_smiley_container .popover .popover-title { display:none; }
                    .arf_form .arf_smiley_container .popover.top .arrow:after { border-top-color: #000000 !important; }

                    .arf_form .arf_smiley_btn {cursor:pointer; display: inline-block; padding:0 3px;}
                    .arf_form .arf_smiley_btn .arf_smiley_img{opacity: 0.6; box-shadow:none;}
                    .arf_form .arf_smiley_btn .arf_smiley_icon{opacity: 0.6; box-shadow:none; text-align: center; }

                    .ar_main_div_' . $form->id . ' .arf_smiley_btn .arf_smiley_icon{
                    float: left;
                    line-height:normal;
                    padding:0 2px;
                    }


                    ';
            }
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
            foreach ($values['fields'] as $field) {

            $field['id'] = $arfieldhelper->get_actual_id($field['id']);

            if ($field['type'] == 'select') {
                if ($field['size'] != 1) {
                    if (isset($newarr) && $newarr['auto_width'] != "1") {

                        if (isset($field['field_width']) and $field['field_width'] != '') {

                            echo '.ar_main_div_' . $field['form_id'] . ' .select_controll_' . $field['id'] . ':not([class*="span"]):not([class*="col-"]):not([class*="form-control"]){width:' . $field['field_width'] . 'px !important;}';
                        }
                    }
                }
            } else if ($field['type'] == 'time') {
                if (isset($field['field_width']) and $field['field_width'] != '') {
                    echo '.ar_main_div_' . $field['form_id'] . ' .time_controll_' . $field['id'] . ':not([class*="span"]):not([class*="col-"]):not([class*="form-control"]){width:' . $field['field_width'] . 'px !important;}';
                }
            }

            if (isset($field['field_width']) and $field['field_width'] != '') {
                echo ' .ar_main_div_' . $form->id . ' #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .help-block { width: ' . $field['field_width'] . 'px; } ';
            }

            if ($field['type'] == 'divider') {

                if ($form->form_css['arfsectiontitlefamily'] != "Arial" && $form->form_css['arfsectiontitlefamily'] != "Helvetica" && $form->form_css['arfsectiontitlefamily'] != "sans-serif" && $form->form_css['arfsectiontitlefamily'] != "Lucida Grande" && $form->form_css['arfsectiontitlefamily'] != "Lucida Sans Unicode" && $form->form_css['arfsectiontitlefamily'] != "Tahoma" && $form->form_css['arfsectiontitlefamily'] != "Times New Roman" && $form->form_css['arfsectiontitlefamily'] != "Courier New" && $form->form_css['arfsectiontitlefamily'] != "Verdana" && $form->form_css['arfsectiontitlefamily'] != "Geneva" && $form->form_css['arfsectiontitlefamily'] != "Courier" && $form->form_css['arfsectiontitlefamily'] != "Monospace" && $form->form_css['arfsectiontitlefamily'] != "Times" && $form->form_css['arfsectiontitlefamily'] != "") {
                    if (is_ssl())
                        $googlefontbaseurl = "https://fonts.googleapis.com/css?family=";
                    else
                        $googlefontbaseurl = "http://fonts.googleapis.com/css?family=";
                    echo "@import url(" . $googlefontbaseurl . urlencode($form->form_css['arfsectiontitlefamily']) . ");";
                }                

                $arf_heading_font_style = '';                
                $arf_section_title_font_style_arr = isset($form->form_css['arfsectiontitleweightsetting']) ? explode(',', $form->form_css['arfsectiontitleweightsetting']) : array();                
                    if (in_array('italic', $arf_section_title_font_style_arr)) {
                        $arf_heading_font_style .= 'font-style:italic; ';
                    } else if (in_array('bold', $arf_section_title_font_style_arr)) {
                        $arf_heading_font_style .= ' font-weight:bold;';
                    } else if (in_array('underline', $arf_section_title_font_style_arr)) {
                        $arf_heading_font_style .= ' text-decoration:underline;';
                    } else{
                        $arf_heading_font_style .= ' font-weight:100 !important;';
                    }

                

                echo ' .ar_main_div_' . $form->id . ' #heading_' . $field['id'] . ' h2.arf_sec_heading_field { font-family:' . stripslashes($form->form_css['arfsectiontitlefamily']) . '; font-size:' . $form->form_css['arfsectiontitlefontsizesetting'] . 'px !important; ' . $arf_heading_font_style . '}';
            }

            $custom_css_array = array(
                'css_outer_wrapper' => '.arf_form_outer_wrapper',
                'css_label' => '.css_label',
                'css_input_element' => '.css_input_element',
                'css_description' => '.arf_field_description',
            );

            if (in_array($field['type'], array('text', 'email', 'date', 'time', 'password', 'number', 'image', 'url', 'phone', 'number'))) {
                $custom_css_array['css_add_icon'] = '.arf_prefix, .arf_suffix';
            }

            foreach ($custom_css_array as $custom_css_block => $custom_css_classes) {
                if (isset($field[$custom_css_block]) and $field[$custom_css_block] != '') {

                    $field[$custom_css_block] = $arformcontroller->br2nl($field[$custom_css_block]);

                    if ($custom_css_block == 'css_outer_wrapper' and $field['type'] != 'divider') {
                        echo ' .ar_main_div_' . $form->id . ' #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container { ' . $field[$custom_css_block] . ' } ';
                    } else if ($custom_css_block == 'css_outer_wrapper' and $field['type'] == 'divider') {
                        echo ' .ar_main_div_' . $form->id . ' #heading_' . $field['id'] . ' { ' . $field[$custom_css_block] . ' } ';
                    } else if ($custom_css_block == 'css_label' and $field['type'] != 'divider') {
                        echo ' .ar_main_div_' . $form->id . ' #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container label.arf_main_label { ' . $field[$custom_css_block] . ' } ';
                    } else if ($custom_css_block == 'css_label' and $field['type'] == 'divider') {
                        echo ' .ar_main_div_' . $form->id . ' #heading_' . $field['id'] . ' h2.arf_sec_heading_field { ' . $field[$custom_css_block] . ' } ';
                    } else if ($custom_css_block == 'css_input_element') {

                        if ($field['type'] == 'textarea') {
                            echo ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .controls textarea { ' . $field[$custom_css_block] . ' } ';
                        } else if ($field['type'] == 'select' || $field['type'] == ARF_AUTOCOMPLETE_SLUG ) {
                            echo ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .controls select { ' . $field[$custom_css_block] . ' } ';
                            echo ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .controls .arfbtn.dropdown-toggle { ' . $field[$custom_css_block] . ' } ';
                        } else if ($field['type'] == 'radio') {
                            echo ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .arf_radiobutton label { ' . $field[$custom_css_block] . ' } ';
                        } else if ($field['type'] == 'checkbox') {
                            echo ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .arf_checkbox_style label { ' . $field[$custom_css_block] . ' } ';
                        } else if ($field['type'] == 'file') {
                            echo ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .controls .arfajax-file-upload { ' . $field[$custom_css_block] . ' } ';
                        } else if ($field['type'] == 'colorpicker') {
                            echo ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .controls .arfcolorpickerfield { ' . $field[$custom_css_block] . ' } ';
                        } else {
                            echo ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .controls input { ' . $field[$custom_css_block] . ' } ';
                            if ($field['type'] == 'email') {
                                echo '.ar_main_div_' . $form->id . ' #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container + .confirm_email_container .controls input {' . $field[$custom_css_block] . '}';
                                echo ' .ar_main_div_' . $form->id . ' #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container + .confirm_email_container .arf_prefix_suffix_wrapper{ ' . $field[$custom_css_block] . ' }';
                            }
                            if ($field['type'] == 'password') {
                                echo '.ar_main_div_' . $form->id . ' #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container + .confirm_password_container .controls input{ ' . $field[$custom_css_block] . '}';
                                echo ' .ar_main_div_' . $form->id . ' #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container + .confirm_password_container .arf_prefix_suffix_wrapper { ' . $field[$custom_css_block] . ' } ';
                            }
                            echo ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .arf_prefix_suffix_wrapper { ' . $field[$custom_css_block] . ' } ';
                        }
                    } else if ($custom_css_block == 'css_description' and $field['type'] != 'divider') {
                        echo ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .arf_field_description { ' . $field[$custom_css_block] . ' } ';
                    } else if ($custom_css_block == 'css_description' and $field['type'] == 'divider') {
                        echo ' .ar_main_div_' . $form->id . '  #heading_' . $field['id'] . ' .arf_heading_description { ' . $field[$custom_css_block] . ' } ';
                    } else if ($custom_css_block == 'css_add_icon' and $field['type'] != 'divider') {
                        echo '.ar_main_div_' . $form->id . ' #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .arf_prefix,
                            .ar_main_div_' . $form->id . ' #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .arf_suffix { ' . $field[$custom_css_block] . ' } ';
                        if ($field['type'] == 'email') {
                            echo '.ar_main_div_' . $form->id . ' .arf_confirm_email_field_' . $field['id'] . ' .arf_prefix,
                                .ar_main_div_' . $form->id . ' .arf_confirm_email_field_' . $field['id'] . ' .arf_suffix {' . $field[$custom_css_block] . ' } ';
                        }
                        if ($field['type'] == 'password') {
                            echo '.ar_main_div_' . $form->id . ' .arf_confirm_password_field_' . $field['id'] . ' .arf_prefix,
                                .ar_main_div_' . $form->id . ' .arf_confirm_password_field_' . $field['id'] . ' .arf_suffix {' . $field[$custom_css_block] . ' } ';
                        }
                    }

                    do_action('arf_add_css_from_outside', $field, $custom_css_block, $arf_data_uniq_id);
                }
            }
        }
            ?>
        </style>
        <?php
        wp_print_styles('arfbootstrap-css');
        wp_print_styles('arfbootstrap-select');

        wp_print_styles('arfbootstrap-slider');

        wp_print_styles('arfdisplaycss');

        wp_print_scripts('jquery-validation');
        wp_print_scripts('jquery-bootstrap-slect');
        wp_print_scripts('animate-numbers');
        wp_print_scripts('arfbootstrap-inputmask');
        wp_print_scripts('arforms_phone_intl_input');
        wp_print_scripts('arforms_phone_utils');
        wp_print_scripts('jquery-maskedinput');
        wp_print_scripts('arforms');
        wp_print_styles('arfdisplayflagiconcss');
        
        wp_print_scripts('arfbootstrap-js');
        wp_print_scripts('arf-conditional-logic-js');

        wp_print_styles('arf_tipso_css_front');
        wp_print_scripts('arf_tipso_js_front');

        wp_print_scripts('bootstrap-typeahead-js');
        
        do_action('include_outside_js_css_for_preview_header');

        $wp_upload_dir = wp_upload_dir();
        ?>
        <script type="text/javascript" data-cfasync="false">
            jQuery(document).ready(function () {
                setTimeout(function () {
                    var width = jQuery('.arfshowmainform.arfpreivewform').find('.arf_fieldset').width();
                    jQuery('.arfshowmainform.arfpreivewform').find('.arf_prefix_suffix_wrapper').css('max-width', width + 'px');
                }, 500);
            });

            jQuery(window).resize(function () {
                setTimeout(function () {
                    var width = jQuery('.arfshowmainform.arfpreivewform').find('.arf_fieldset').width();
                    jQuery('.arfshowmainform.arfpreivewform').find('.arf_prefix_suffix_wrapper').css('max-width', width + 'px');
                }, 500);
            });
        </script>
    </head>
    <body style=" background:none; background-color:#FFFFFF;" class="arf_preview_modal_body <?php echo (is_rtl()) ? 'arf_preview_rtl' : ''; ?>" >
        <?php
        global $wpdb, $MdlDb;
        
        if( isset($form->id) && !isset($_REQUEST['form_id']) ){
            $res = $wpdb->get_results($wpdb->prepare("SELECT options FROM " . $MdlDb->forms . " WHERE id = %d", $form->id), 'ARRAY_A');
        }
        if (isset($res)){
            $res = $res[0];
        }

        $res['options'] = isset($res['options']) ? $res['options'] : '';

        $values = ($res['options'] != '') ? maybe_unserialize($res['options']) : array();

        $form_style_css = maybe_unserialize($form->form_css);

        $form_style_css = $arformcontroller->arfObjtoArray($form_style_css);

        $loaded_field = isset($form->options['arf_loaded_field']) ? $form->options['arf_loaded_field'] : array();

        $values['display_title_form'] = isset($values['display_title_form']) ? $values['display_title_form'] : '';
        if ($values['display_title_form'] == '0' and $new == 'list') {
            $title = false;
            $description = false;
        } else {
            $title = true;
            $description = true;
        }
        $checkradio_property = "";
        if (isset($_REQUEST['checkradiostyle']) && $_REQUEST['checkradiostyle'] != "") {
            if (isset($_REQUEST['checkradiostyle']) && $_REQUEST['checkradiostyle'] != "none") {
                if (isset($_REQUEST['checkradiocolor']) && $_REQUEST['checkradiocolor'] != "default" && $_REQUEST['checkradiocolor'] != "") {
                    if (isset($_REQUEST['checkradiostyle']) && $_REQUEST['checkradiostyle'] == "custom" || $_REQUEST['checkradiostyle'] == "futurico" || $_REQUEST['checkradiostyle'] == "polaris") {
                        $checkradio_property = isset($_REQUEST['checkradiostyle']) ? $_REQUEST['checkradiostyle'] : '';
                    } else {
                        $arf_checkradio = isset($_REQUEST['checkradiostyle']) ? $_REQUEST['checkradiostyle'] : ''; 
                        $checkradio_property = $arf_checkradio . "-" . $_REQUEST['checkradiocolor'];
                    }
                } else {
                    $checkradio_property = isset($_REQUEST['checkradiostyle']) ? $_REQUEST['checkradiostyle'] : '';
                }
            } else {
                $checkradio_property = "";
            }
        } else {
            if (isset($form_style_css['arfcheckradiostyle']) && $form_style_css['arfcheckradiostyle'] != "") {
                if ($form_style_css['arfcheckradiostyle'] != "none") {
                    if ($form_style_css['arfcheckradiocolor'] != "default" && $form_style_css['arfcheckradiocolor'] != "") {
                        $form_css_submit['arfcheckradiostyle'] = isset($form_css_submit['arfcheckradiostyle']) ? $form_css_submit['arfcheckradiostyle'] : array();
                        if ((isset($form_css_submit['arfcheckradiostyle']) && $form_css_submit['arfcheckradiostyle'] == "custom") || $form_style_css['arfcheckradiostyle'] == "futurico" || $form_style_css['arfcheckradiostyle'] == "polaris") {
                            $checkradio_property = $form_style_css['arfcheckradiostyle'];
                        } else {
                            $checkradio_property = $form_style_css['arfcheckradiostyle'] . "-" . $form_style_css['arfcheckradiocolor'];
                        }
                    } else {
                        $checkradio_property = $form_style_css['arfcheckradiostyle'];
                    }
                } else {
                    $checkradio_property = "";
                }
            }
        }
        ?>	
        <div id="arfdevicebody" class="arfdevicecomputer" align="center" style="width:100%; max-width:100%; margin:0 auto;">
            <?php
            require_once VIEWS_PATH . '/arf_form_preview.php';
            $opt_id = isset($_REQUEST['arf_opt_id']) ? $_REQUEST['arf_opt_id'] : '';
            $home_preview = isseT($_REQUEST['arf_is_home']) ? $_REQUEST['arf_is_home'] : '';

            if ($opt_id != '') {
                $saved_preview_data = get_option($opt_id);
                $posted_data = json_decode(stripslashes_deep($saved_preview_data['posted_data']), true);
                $contents = arf_display_form_preview($form->id, $key, $posted_data);
                $contents = apply_filters('arf_pre_display_arfomrms', $contents, $form->id, $key);
                echo $contents;
            } else if ($home_preview == true) {
                $form_id = isset($_REQUEST['form_id']) ? $_REQUEST['form_id'] : '';
                require_once VIEWS_PATH . '/arf_front_form.php';

                $contents = ars_get_form_builder_string($form_id, $key, true, false, '', $arf_data_uniq_id);

                echo $contents = apply_filters('arf_pre_display_arfomrms', $contents, $form_id, $key);
            } else {
                echo addslashes(esc_html__('Please select valid forms', 'ARForms'));
            }
            ?>
        </div>
        <?php
        global $arf_loaded_fields, $arf_preview_form,$arfversion;
        $loaded_field = isset($form->options['arf_loaded_field']) ? $form->options['arf_loaded_field'] : array();
        
        wp_print_scripts('jquery-effects-slide');
        if ((isset($form->options['arf_number_animation']) && $form->options['arf_number_animation']) || isset($arf_preview_form->options['arf_number_animation']) && $arf_preview_form->options['arf_number_animation']) {        
            wp_print_scripts('animate-numbers');
        }

        wp_print_scripts('arfbootstrap-modernizr-js', '', '', '', false);
        wp_print_scripts('arfbootstrap-slider-js', '', '', '', false);

        if ((isset($form->options['font_awesome_loaded']) && $form->options['font_awesome_loaded']) || (isset($arf_preview_form->options['font_awesome_loaded']) && $arf_preview_form->options['font_awesome_loaded'])) {
            wp_print_styles('arf-fontawesome-css');
        }

        if ((isset($form->options['tooltip_loaded']) && $form->options['tooltip_loaded']) || isset($arf_preview_form->options['tooltip_loaded']) && $arf_preview_form->options['tooltip_loaded']) {
            wp_print_styles('arf_tipso_css_front');
            wp_print_scripts('arf_tipso_js_front');
        }

        if ((isset($form->options['arf_input_mask']) && $form->options['arf_input_mask']) || isset($arf_preview_form->options['arf_input_mask']) && $arf_preview_form->options['arf_input_mask']) {
            wp_print_scripts('jquery-maskedinput');
            wp_print_scripts('arfbootstrap-inputmask');
        }

        if ((isset($loaded_field) && in_array('file', $loaded_field)) || (isset($arf_loaded_fields) && in_array('file', $arf_loaded_fields))) {
            wp_print_styles('arf-filedrag');
            wp_print_scripts('filedrag');
        }

        
        if ((isset($loaded_field) && (in_array('time', $loaded_field) || in_array('date', $loaded_field))) || (isset($arf_loaded_fields) && (in_array('time', $arf_loaded_fields) || in_array('date', $arf_loaded_fields)))) {
            
            if (!isset($date_picker_theme) || $date_picker_theme == "")
                $date_picker_theme = 'default_theme';
            
            wp_print_scripts('bootstrap-locale-js');
            wp_print_styles('arfbootstrap-datepicker-css');
            wp_print_scripts('bootstrap-datepicker');
        }
        if ((isset($loaded_field) && in_array('colorpicker', $loaded_field) && $form->options['arf_advance_colorpicker'] == 1) || (isset($arf_loaded_fields) && in_array('colorpicker', $arf_loaded_fields) && $arf_preview_form->options['arf_advance_colorpicker'] == 1)) {
            wp_print_styles('arf-fontawesome-css');
            wp_print_scripts('arf_js_color');
        }
        if ((isset($loaded_field) && in_array('colorpicker', $loaded_field) && $form->options['arf_normal_colorpicker'] == 1) || (isset($arf_loaded_fields) && in_array('colorpicker', $arf_loaded_fields) && $arf_preview_form->options['arf_normal_colorpicker'] == 1)) {
            wp_print_scripts('arf-colorpicker-basic-js');
        }
        

        wp_print_styles('form_custom_css-default_theme');
        
        wp_print_scripts('recaptcha-ajax');
        wp_print_styles('arfrecaptchacss');
        $preview = true;
        
        $arrecordcontroller->footer_js(true, false);
        do_action('include_outside_js_css_for_preview_footer');
        ?>
        <?php
        if ($checkradio_property != "") {
            ?>
            <?php
        }
        if (isset($_GET['is_editorform']) && $_GET['is_editorform'] == '1') {
            ?>
            <script type="text/javascript" data-cfasync="false">
                jQuery(document).ready(function () {

                    jQuery('.arfpreivewform .arf_image_horizontal_center').each(function () {
                        var top = jQuery(this).attr('data-ctop');
                        jQuery(this).css('top', '').css('position', 'inherit');
                        jQuery(this).find('.arf_image_field').css('top', top);
                    });
                    jQuery('.arfpreivewform .arf_image_field').draggable({
                        containment: 'parent',
                        cursor: "move",
                        scroll: false,
                        iframeFix: true,
                        drag: function (event, ui) {
                            jQuery(this).css('top', ui.position.top + 'px');
                            jQuery(this).css('left', ui.position.left + 'px');
                        },
                        stop: function (event, ui) {
                            jQuery(this).css('top', ui.position.top + 'px');
                            jQuery(this).css('left', ui.position.left + 'px');
                            var field_id = jQuery(this).attr('id');
                            field_id = field_id.replace('arf_imagefield_', '');
                            window.parent.change_image_field_pos(field_id, ui.position.top, ui.position.left);
                        }
                    });
                    
                });
            </script>
        <?php } $is_arf_preview = 0; ?>
        <script type="text/javascript" data-cfasync="false">
            jQuery(document).ready(function () {
                jQuery(document).on('keydown', function(e) {
                    if (e.keyCode == 27) {
                        parent.document.getElementsByClassName('arf_popup_header_close_button')[0].click();
                    }
                });
            });
        </script>
    </body>
</html>