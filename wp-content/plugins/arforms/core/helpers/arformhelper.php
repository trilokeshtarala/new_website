<?php

class arformhelper {
    function __construct() {
        add_filter('arfsetupnewformvars', array($this, 'setup_new_variables'));
    }

    function setup_new_variables($values) {
        global $arformhelper, $armainhelper;
        foreach ($arformhelper->get_default_options() as $var => $default) {
            $values[$var] = $armainhelper->get_param($var, $default);
        }
        return $values;
    }

    function get_direct_link($key) {
        global $arfsiteurl;
        $target_url = esc_url(site_url() . '/index.php?plugin=ARForms&controller=forms&arfaction=preview&form=' . $key);
        return $target_url;
    }

    function replace_shortcodes($html, $form, $title = false, $description = false) {
        foreach (array('form_name' => $title, 'form_description' => $description, 'entry_key' => true) as $code => $show) {
            if ($code == 'form_name') {
                $replace_with = $form->name;
            } else if ($code == 'form_description') {
                $replace_with = $form->description;
            } else if ($code == 'entry_key' and isset($_GET) and isset($_GET['entry'])) {
                $replace_with = $_GET['entry'];
            }

            if(($show == true || $show == 'true') && $replace_with != '') {
                $html = str_replace('[if ' . $code . ']', '', $html);
                $html = str_replace('[/if ' . $code . ']', '', $html);
            } else {
                $html = preg_replace('/(\[if\s+' . $code . '\])(.*?)(\[\/if\s+' . $code . '\])/mis', '', $html);
            }
            $html = str_replace('[' . $code . ']', $replace_with, $html);
        }
        $html = str_replace('[form_key]', $form->form_key, $html);
        $html = trim($html);
        return apply_filters('arfformreplaceshortcodes', stripslashes($html), $form);
    }

    function get_default_options() {
        global $style_settings, $arfsettings;
        return array (
            'edit_value' => $style_settings->update_value, 'edit_msg' => $style_settings->edit_msg,
            'logged_in_role' => '',
            'editable_role' => '', 'open_editable' => 0, 'open_editable_role' => '',
            'copy' => 0, 'single_entry' => 0, 'single_entry_type' => 'user',
            'success_page_id' => '', 'success_url' => '', 'ajax_submit' => 0,
            'create_post' => 0, 'cookie_expiration' => 8000,
            'post_type' => 'post', 'post_category' => array(), 'post_content' => '',
            'post_excerpt' => '', 'post_title' => '', 'post_name' => '', 'post_date' => '',
            'post_status' => '', 'post_custom_fields' => array(), 'post_password' => '',
            'plain_text' => 0, 'also_email_to' => array(), 'update_email' => 0,
            'email_subject' => '', 'email_message' => '[default-message]',
            'inc_user_info' => 1, 'auto_responder' => 0, 'ar_plain_text' => 0,
            'ar_email_to' => '', 'ar_reply_to' => get_option('admin_email'),
            'ar_reply_to_name' => get_option('blogname'), 'ar_email_subject' => '',
            'ar_email_message' => addslashes(esc_html__('Thank you for subscription with us. We will contact you soon.', 'ARForms')),
            'ar_update_email' => 0, 'chk_admin_notification' => 0,
            'form_custom_css' => '', 'label_position' => $style_settings->position, 'is_custom_css' => 0,
            'ar_admin_email_to' => get_option('admin_email'), 'ar_admin_reply_to' => get_option('admin_email'),
            'ar_admin_email_message' => addslashes(esc_html__('[ARF_form_all_values]', 'ARForms')),
            'arf_enable_double_optin' => 1,
            'ar_admin_reply_to_name' => get_option('blogname'), 'email_to' => $arfsettings->reply_to,
            'reply_to' => $arfsettings->reply_to,
            'reply_to_name' => get_option('blogname'),
            'ar_admin_reply_to_email' => get_option('admin_email'),
            'user_nreplyto_email' => get_option('admin_email'),
            'display_title_form' => '1',
            'ar_user_from_name' => (isset($arfsettings->ar_user_from_name)) ? $arfsettings->ar_user_from_name : '',
            'ar_user_from_email' => (isset($arfsettings->ar_user_from_email)) ? $arfsettings->ar_user_from_email : '',
            'ar_user_nreplyto_email' => (isset($arfsettings->ar_user_nreplyto_email)) ? $arfsettings->ar_user_nreplyto_email : '',
            'ar_admin_from_name' => (isset($arfsettings->ar_admin_from_name)) ? $arfsettings->ar_admin_from_name : '',
            'ar_admin_from_email' => (isset($arfsettings->ar_admin_from_email)) ? $arfsettings->ar_admin_from_email : '',
            'admin_nreplyto_email' => (isset($arfsettings->admin_nreplyto_email)) ? $arfsettings->admin_nreplyto_email : '',
            'arf_form_outer_wrapper' => '', 'arf_form_inner_wrapper' => '',
            'arf_form_title' => '', 'arf_form_description' => '', 'arf_form_element_wrapper' => '',
            'arf_form_element_label' => '', 'arf_form_submit_button' => '', 'arf_form_success_message' => '',
            'arf_form_elements' => '', 'arf_submit_outer_wrapper' => '', 'arf_form_next_button' => '',
            'arf_form_previous_button' => '', 'arf_form_error_message' => '', 'arf_form_page_break' => '',
            'arf_form_fly_sticky' => '', 'arf_form_modal_css' => '', 'arf_form_other_css' => '',
            'admin_email_subject' => '[form_name] ' . addslashes(esc_html__('Form submitted on', 'ARForms')) . ' [site_name] ',
            'arf_form_link_css' => '', 'arf_form_button_css' => '', 'arf_form_link_hover_css' => '', 'arf_form_button_hover_css' => '', 'arf_form_hide_after_submit' => '', 'arf_pre_dup_check' => '', 'arf_pre_dup_check_type' => '', 'arf_pre_dup_field' => '', 'arf_pre_dup_msg' => $arfsettings->arf_pre_dup_msg,
            'conditional_subscription' => 0, 'arf_form_set_cookie' => ''
        );
    }

    function forms_dropdown_new($field_name, $field_value = '', $blank = true, $field_id = false, $onchange = false, $multiple = false, $is_import_export = 0, $show_id = false,$selectClass='') {
        global $arfform, $armainhelper, $arfieldhelper;
        $array = '';
        if (!$field_id) {
            $field_id = $field_name;
        }

        if ($multiple == 'mutliple') {
            $multiple = "multiple";
            $array = '[]';
        }

        $optionheight = '';
        $customfontsize = '';
        if ($is_import_export == 1) {
            $optionheight = 'style="height:22px;font-size:14px;padding-top:4px;"';
            $customfontsize = "font-size:14px;";
        }

        $where = apply_filters('arfformsdropdowm', "is_template=0 AND (status is NULL OR status = '' OR status = 'published')", $field_name);

        $forms = $arfform->getAll($where, ' ORDER BY name');
        ?>
        <?php
        if (is_rtl()) {
            $sel_frm_box = 'text-align:right;width:250px;outline:none;' . $customfontsize;
        } else {
            $sel_frm_box = 'text-align:left;width:250px;outline:none;' . $customfontsize;
        }
        if($field_name == 'arfaddformid' || $field_name == 'arfaddformid_vc_popup'){
        ?>
            <div class="dt_dl">
                <input type="hidden" name="<?php echo $field_name; ?>" id="<?php echo $field_id ?>" <?php if ($onchange) echo 'onchange="' . $onchange . '"'; ?> />
                <?php if ($blank) {} ?>
                <dl class="arf_selectbox" data-name="arfinpst" data-id="<?php echo $field_name; ?>">
                <?php
                    $show_form_itd = '';
                    $selected_list_label = addslashes(esc_html__('Select Form', 'ARForms'));
                    $selected_list_id = '';
                    $list = '<li class="lblnotetitle arf_selectbox_option" ' . $optionheight . ' data-value="0" data-label="' . addslashes(esc_html__('Select Form', 'ARForms')) . '">' . addslashes(esc_html__('Select Form', 'ARForms')) . '</li>';

                    foreach ($forms as $form) {
                        if ($show_id) {
                            $show_form_itd = $form->id . " - ";
                        }
                        $form->name = $arfieldhelper->arf_execute_function($form->name,'strip_tags');
                        if ($form->id == $field_value) {
                            $selected_list_id = $form->id;
                            $selected_list_label = $armainhelper->truncate($form->name, 33);
                        }
                        $list .='<li class="lblnotetitle arf_selectbox_option" ' . $optionheight . ' data-value="' . $form->id . '" data-label="' . $show_form_itd . $arfieldhelper->arf_execute_function(html_entity_decode($armainhelper->truncate($form->name, 33)),'strip_tags') . '"> ' . $show_form_itd . $arfieldhelper->arf_execute_function(html_entity_decode($armainhelper->truncate($form->name, 33)),'strip_tags') . ' (id: '.$form->id.') </li>';
                    }
                ?>
                    <dt style="width:515px;">
                    <span style="float:left;"><?php echo $selected_list_label; ?></span>
                    <input value="<?php echo $selected_list_label; ?>" style="display:none;border:none;box-shadow:none;width:100%;" class="arf_autocomplete" type="text">
                    <i class="arfa arfa-caret-down arfa-lg"></i>
                    </dt>
                    <dd>
                    <ul style="display:none;width:531px;" data-id="<?php echo $field_name; ?>">
                        <?php echo $list; ?>
                    </ul>
                    </dd>
                </dl>
            </div>
        <?php } else { ?>
            <div class="multiple_select_box">
                <select name="<?php echo $field_name . $array; ?>" id="<?php echo $field_id ?>" style="border-color:#D5E3FF;<?php echo $sel_frm_box; ?>" class="frm-dropdown <?php echo $selectClass; ?>" <?php if ($onchange) echo 'onchange="' . $onchange . '"'; ?> data-width="360px" data-size="10" <?php echo $multiple; ?>>


            <?php if ($blank) { ?>


                        <option <?php echo $optionheight; ?> value=""><?php echo ($blank == 1) ? '' : '- ' . $blank . ' -'; ?></option>


            <?php } ?>

            <?php $show_form_itd = ''; ?>
            <?php foreach ($forms as $form) { ?>
                <?php
                if ($show_id) {
                    $show_form_itd = $form->id . " - ";
                }
                ?>

                        <option class="lblnotetitle" <?php echo $optionheight; ?> value="<?php echo $form->id; ?>" <?php selected($field_value, $form->id); ?>><?php echo $show_form_itd . html_entity_decode($armainhelper->truncate($form->name, 33)); ?></option>


            <?php } ?>


                </select>
            </div>
        <?php } ?>
        <?php if ($is_import_export == 1) { ?>
            <div class="arf_import_export_entries_dropdown dt_dl" style="display: none;">
                <input type="hidden" name="is_single_form" value="0" id="is_single_form"/>
                <input type="hidden" name="<?php echo $field_name; ?>_name" id="<?php echo $field_id ?>_name" <?php if ($onchange) echo 'onchange="' . $onchange . '"'; ?> />
            <?php if ($blank) { ?>
            <?php } ?>
                <dl class="arf_selectbox" data-name="arfinpst" data-id="<?php echo $field_name; ?>">
            <?php
            $show_form_itd = '';
            $selected_list_label = addslashes(esc_html__('Select Form', 'ARForms'));
            $selected_list_id = '';
            $list = '<li class="lblnotetitle arf_selectbox_option" ' . $optionheight . ' data-value="0" data-label="' . addslashes(esc_html__('Select Form', 'ARForms')) . '">' . addslashes(esc_html__('Select Form', 'ARForms')) . '</li>';
            foreach ($forms as $form) {
                if ($show_id) {
                    $show_form_itd = $form->id . " - ";
                }
                $form->name = $arfieldhelper->arf_execute_function($form->name,'strip_tags');
                if ($form->id == $field_value) {
                    $selected_list_id = $form->id;
                    $selected_list_label = $armainhelper->truncate($form->name, 33);
                }
                $list .='<li class="lblnotetitle arf_selectbox_option" ' . $optionheight . ' data-value="' . $form->id . '" data-label="' . $show_form_itd . $arfieldhelper->arf_execute_function(html_entity_decode($armainhelper->truncate($form->name, 33)),'strip_tags') . '">' . $show_form_itd . $arfieldhelper->arf_execute_function(html_entity_decode($armainhelper->truncate($form->name, 33)),'strip_tags') . '</li>';
            }
            ?>
                    <dt style="width:300px;">
                    <span style="float:left;"><?php echo $selected_list_label; ?></span>
                    <input value="<?php echo $selected_list_label; ?>" style="display:none;" class="arf_autocomplete" type="text" autocomplete="off">
                    <svg viewBox="0 0 2000 1000" width="15px" height="15px">
                    <g fill="#000">
                    <path d="M1024 320q0 -26 -19 -45t-45 -19h-896q-26 0 -45 19t-19 45t19 45l448 448q19 19 45 19t45 -19l448 -448q19 -19 19 -45z"></path>
                    </g>
                    </svg>
                    </dt>
                    <dd>
                        <ul style="display:none;width:316px;" data-id="<?php echo $field_name; ?>_name">
                    <?php
                    echo $list;
                    ?>
                        </ul>
                    </dd>
                </dl>
            </div>
                <?php
                }
            }

            function forms_dropdown_widget($field_name, $field_value = '', $blank = true, $field_id = false, $onchange = false) {


                global $arfform, $armainhelper;


                if (!$field_id)
                    $field_id = $field_name;





                $where = apply_filters('arfformsdropdowm', "is_template=0 AND (status is NULL OR status = '' OR status = 'published')", $field_name);


                $forms = $arfform->getAll($where, ' ORDER BY name');
                ?>


        <select name="<?php echo $field_name; ?>" id="<?php echo $field_id ?>" style="width:225px;" class="frm-dropdown" <?php if ($onchange) echo 'onchange="' . $onchange . '"'; ?> data-width="225px" data-size="15">


                <?php if ($blank) { ?>


                <option value=""><?php echo ($blank == 1) ? '' : '- ' . $blank . ' -'; ?></option>


                <?php } ?>


                <?php foreach ($forms as $form) { ?>


                <option value="<?php echo $form->id; ?>" <?php selected($field_value, $form->id); ?>><?php echo $armainhelper->truncate($form->name, 33); ?></option>


        <?php } ?>


        </select>


        <?php
    }

    function setup_new_vars() {


        global $MdlDb, $arfsettings, $arformhelper, $armainhelper;


        $values = array();


        foreach (array('name' => addslashes(esc_html__('Untitled Form', 'ARForms')), 'description' => '') as $var => $default)
            $values[$var] = $armainhelper->get_param($var, $default);


        foreach (array('form_id' => '', 'is_template' => 0) as $var => $default)
            $values[$var] = $armainhelper->get_param($var, $default);


        $values['form_key'] = ($_POST and isset($_POST['form_key'])) ? $_POST['form_key'] : ($armainhelper->get_unique_key('', $MdlDb->forms, 'form_key'));


        $defaults = $arformhelper->get_default_opts();


        foreach ($defaults as $var => $default) {


            if ($var == 'notification') {


                $values[$var] = array();


                foreach ($default as $k => $v) {


                    $values[$var][$k] = (isset($_POST) and $_POST and isset($_POST['notification'][$var])) ? $_POST['notification'][$var] : $v;


                    unset($k);


                    unset($v);
                }
            } else {


                $values[$var] = (isset($_POST) and $_POST and isset($_POST['options'][$var])) ? $_POST['options'][$var] : $default;
            }





            unset($var);


            unset($default);
        }





        $values['custom_style'] = (isset($_POST) and $_POST and isset($_POST['options']['custom_style'])) ? $_POST['options']['custom_style'] : ($arfsettings->load_style != 'none');


        $values['before_html'] = $arformhelper->get_default_html('before');


        $values['after_html'] = $arformhelper->get_default_html('after');





        return apply_filters('arfsetupnewformvars', $values);
    }

    function get_default_opts() {
        global $arfsettings;

        return array(
            'notification' => array(
                array('email_to' => $arfsettings->reply_to,
                    'reply_to' => $arfsettings->reply_to,
                    'reply_to_name' => get_option('blogname'),
                    'cust_reply_to' => '',
                    'cust_reply_to_name' => ''
                )
            ),
            'submit_value' => $arfsettings->submit_value,
            'success_action' => 'message',
            'success_msg' => $arfsettings->success_msg,
            'show_form' => 0, 'akismet' => '',
            'ar_email_message' => addslashes(esc_html__('Thank you for subscription with us. We will contact you soon.', 'ARForms')),
            'ar_admin_email_message' => addslashes(esc_html__('[ARF_form_all_values]', 'ARForms')),
            'no_save' => 0,
            'admin_email_subject' => '[form_name] ' . addslashes(esc_html__('Form submitted on', 'ARForms')) . ' [site_name] ',
            'arf_restrict_form_entries' => 0,
            'restrict_action' => 'max_entries',
            'arf_restrict_max_entries' => 50,
            'arf_restrict_entries_before_specific_date' => '',
            'arf_restrict_entries_after_specific_date' => '',
            'arf_res_msg' => '',
            'arf_restrict_entries_start_date' => '',
            'arf_restrict_entries_end_date' => ''
        );
    }

    function get_default_html($loc) {

        if ($loc == 'before') {

            $default_html = '[if form_name]<div class="formtitle_style">[form_name]</div>[/if form_name]

						[if form_description]<div class="arf_field_description formdescription_style">[form_description]</div>[/if form_description]';
        } else {

            $default_html = '';
        }
        return $default_html;
    }

    function forms_dropdown($field_name, $field_value = '', $blank = true, $field_id = false, $onchange = false) {

        global $arfform, $armainhelper, $arfieldhelper;

        if (!$field_id)
            $field_id = $field_name;

        $where = apply_filters('arfformsdropdowm', "is_template=0 AND (status is NULL OR status = '' OR status = 'published')", $field_name);

        $forms = $arfform->getAll($where, ' ORDER BY name');

        global $wpdb, $MdlDb, $db_record;
        
        if( isset($GLOBALS['arf_dropdown_record_count']) && isset($GLOBALS['arf_dropdown_record_count'][$field_id]) ){
            $record_count = $GLOBALS['arf_dropdown_record_count'][$field_id];
        } else {
            $record_count = $wpdb->get_results("SELECT $MdlDb->forms.id, COUNT($MdlDb->entries.id) AS count_num FROM $MdlDb->entries RIGHT JOIN $MdlDb->forms ON $MdlDb->entries.form_id=$MdlDb->forms.id WHERE $MdlDb->forms.is_template=0 AND ($MdlDb->forms.status is NULL OR $MdlDb->forms.status = '' OR $MdlDb->forms.status = 'published') group by $MdlDb->forms.id", OBJECT_K);
            if( !isset($GLOBALS['arf_dropdown_record_count'])){
                $GLOBALS['arf_dropdown_record_count'] = array();
            }
            $GLOBALS['arf_dropdown_record_count'][$field_id] = $record_count;
        }
        $selected_list_label = '';
        $responder_list_option = '';
        $selected_list_id = ''; 
        ?>
        <?php if ($blank) { 
            $selected_list_label = " - ".$blank." - ";
            $responder_list_option .='<li class="arf_selectbox_option" data-value="" data-label=" - ' .$arfieldhelper->arf_execute_function($blank,'strip_tags') .' - "> - ' .$arfieldhelper->arf_execute_function($blank,'strip_tags').' - </li>';
            
        }        
        ?>
        <?php
        foreach ($forms as $form) {
            $count_num = isset($record_count[$form->id]->count_num) ? $record_count[$form->id]->count_num : 0;
            if ($field_value == $form->id) {
                $selected_list_id = $form->id;
                $selected_list_label = $armainhelper->truncate($form->name, 23) . ' ['.$form->id.']' . ' (' . $count_num . ' - '.esc_html__("Entries", "ARForms").')';
            }
            $arfform_display_option = $arfieldhelper->arf_execute_function($armainhelper->truncate($form->name, 23),'strip_tags') . ' ['.$form->id.']' . ' (' . $count_num . ' - '.esc_html__("Entries", "ARForms").')';
            $responder_list_option .='<li class="arf_selectbox_option" data-value="' . $form->id . '" data-label="' . $arfform_display_option . '">' . $arfform_display_option .'</li>';
        }
        ?>
        <input id="<?php echo $field_id ?>" name="<?php echo $field_name; ?>" value="<?php echo $selected_list_id; ?>" type="hidden" class="frm-dropdown frm-pages-dropdown" onchange="<?php echo $onchange; ?>">
        <dl class="arf_selectbox" data-name="<?php echo $field_name; ?>" data-id="<?php echo $field_name; ?>">
            <dt><span><?php echo $selected_list_label; ?></span>
            <input value="<?php echo $selected_list_label; ?>" style="display:none;width:118px;" class="arf_autocomplete" type="text">
            <svg viewBox="0 0 2000 1000" width="15px" height="15px">
            <g fill="#000">
            <path d="M1024 320q0 -26 -19 -45t-45 -19h-896q-26 0 -45 19t-19 45t19 45l448 448q19 19 45 19t45 -19l448 -448q19 -19 19 -45z"></path>
            </g>
            </svg></dt>
            <dd>
                <ul class="field_dropdown_list_menu" style="display: none;width:360px;padding: 2px;" data-id="<?php echo $field_name; ?>">                    
                <?php echo $responder_list_option; ?>
                </ul>
            </dd>
        </dl>
        <?php
    }

    function replace_field_shortcode($content) {
        global $wpdb, $arffield;

        $tagregexp = '';

        preg_match_all("/\[(if )?($tagregexp)(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?/s", $content, $matches, PREG_PATTERN_ORDER);

        if ($matches and $matches[3]) {
            foreach ($matches[3] as $shortcode) {
                if ($shortcode) {
                    global $arffield;
                    $display = false;
                    $show = 'one';
                    $odd = '';
                    $optionvalue = '';

                    $field_ids = explode(':', $shortcode);

                    if (is_array($field_ids)) {
                        $field_id = end($field_ids);

                        if( strpos($field_id,'.') !== false ){
                            $is_checkbox = explode(".", $field_id);
                        } else {
                            $is_checkbox = array();
                        }

                        if (count($is_checkbox) > 0) {
                            $field_id = $is_checkbox[0];
                            $is_checkbox[1] = isset($is_checkbox[1]) ? $is_checkbox[1] : '';
                            $option_id = $is_checkbox[1];
                        } else {
                            $option_id = "";
                        }
                    } else {
                        $option_id = "";
                    }

                    $field = $arffield->getOne($field_id);

                    if (!isset($field) || !$field->id)
                        return $content;

                    if ($field) {
                        $field_opts = (!is_array($field->field_options) ? json_decode($field->field_options, true) : $field->field_options);

                        $is_sep_val = isset($field_opts['separate_value']) ? $field_opts['separate_value'] : '';

                        $fieldoptions = json_decode($field->options);
                        if( json_last_error() != JSON_ERROR_NONE ){
                            $fieldoptions = maybe_unserialize($field->options);
                        }

                        if (isset($option_id) && $option_id != ""){
                            $optionvalue = $fieldoptions[$option_id];
                        }

                        if ($field->type == "checkbox") {
                            if ($is_sep_val == 1) {
                                $optionvalue1 = $optionvalue['value'];
                                $optionlabel = $optionvalue['label'];

                                $replace_with = '[' . $optionvalue['label'] . ':' . $field_id . '.' . $option_id . ']';
                            } else {

                                if (is_array($optionvalue)) {
                                    $optionvalue = $optionvalue['label'];
                                    $replace_with = '[' . $optionvalue . ':' . $field_id . '.' . $option_id . ']';
                                } else if( $optionvalue == "" ){
                                    $replace_with = '[' . $field->name . ':' . $field_id . ']';
                                }
                            }
                        } else {
                            $replace_with = '[' . $field->name . ':' . $field_id . ']';
                        }
                    }

                    $content = str_replace('[' . $shortcode . ']', $replace_with, $content);
                }
            }
        }

        return $content;
    }

    function replace_field_shortcode_import($content, $res_field_id, $new_field_id) {

        if (!$res_field_id || !$new_field_id)
            return $content;

        global $wpdb, $arffield;

        $tagregexp = '';

        preg_match_all("/\[(if )?($tagregexp)(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?/s", $content, $matches, PREG_PATTERN_ORDER);

        if ($matches and $matches[3]) {
            foreach ($matches[3] as $shortcode) {
                if ($shortcode) {
                    global $arffield;
                    $display = false;
                    $show = 'one';
                    $odd = '';
                    $optionvalue = '';

                    $field_ids = explode(':', $shortcode);
                    $field_id = end($field_ids);

                    if (is_array($field_ids)) {
                        $field_id = end($field_ids);

                        if( strpos($field_id,'.') !== false ){
                            $is_checkbox = explode(".", $field_id);
                        } else {
                            $is_checkbox = array();
                        }

                        if (count($is_checkbox) > 0) {
                            $field_id = $is_checkbox[0];
                            $is_checkbox[1] = isset($is_checkbox[1]) ? $is_checkbox[1] : '';
                            $option_id = $is_checkbox[1];
                        } else {
                            $option_id = "";
                        }
                    } else {
                        $option_id = "";
                    }

                    $temp_field = $arffield->getOne($field_id);

                    if (!isset($temp_field) || !$temp_field->id)
                        return $content;

                    if ($field_id == $res_field_id) {
                        $field = $arffield->getOne($new_field_id);

                        if ($field) {
                            $field_opts = maybe_unserialize($field->field_options);

                            $is_sep_val = isset($field_opts['separate_value']) ? $field_opts['separate_value'] : '';

                            $fieldoptions = json_decode($field->options,true);

                            if( json_last_error() != JSON_ERROR_NONE ){
                                $fieldoptions = maybe_unserialize($field->options);
                            }

                            if (isset($option_id) && $option_id != ""){
                                $optionvalue = $fieldoptions[$option_id];
                            }

                            if ($field->type == "checkbox") {
                                
                                if ($is_sep_val == 1) {
                                    $optionvalue1 = $optionvalue['value'];
                                    $optionlabel = $optionvalue['label'];

                                    $replace_with = '[' . $optionvalue['label'] . ':' . $field_id . '.' . $option_id . ']';
                                } else {
                                    if (is_array($optionvalue)) {
                                        $optionvalue = $optionvalue['label'];
                                    }
                                    $replace_with = '[' . $optionvalue . ':' . $field_id . '.' . $option_id . ']';
                                }
                            } else {
                                $replace_with = '[' . $field->name . ':' . $field_id . ']';
                            }

                            $content = str_replace('[' . $shortcode . ']', $replace_with, $content);
                        }
                    }
                }
            }
        }

        return $content;
    }

    function get_file_upload_path() {
        $org_path = get_option("arf_options");
        $org_path = maybe_unserialize($org_path);
        $org_path = $org_path->arf_file_uplod_dir_path . "/";
        $org_path = trim(preg_replace('#/+#','/',$org_path));
        return $org_path;
    }

    function manage_uploaded_file_path($form_id, $is_post = false) {
        $org_path = $this->get_file_upload_path();
        if($is_post) {
            $org_path = "wp-content/uploads/" . $org_path;
        }
        $custom_path = ABSPATH . $this->replace_file_upload_path_shrtcd($org_path, $form_id);

        $flag = array("status" => false, "path" => "",);
        if(is_dir($custom_path)) {
            $flag["status"] = true;
            $flag["path"] = $custom_path;
            if(!is_dir($custom_path . "thumbs")) mkdir($custom_path . "thumbs");
        }
        else {
            $org_path = trim($org_path);
            if(is_writable(ABSPATH) && !empty($org_path)) {

                $pos_arr = array(strpos($org_path, "{form_id}"), strpos($org_path, "{year}"), strpos($org_path, "{month}"), strpos($org_path, "{day}"));
                
                $pos_arr = array_filter($pos_arr, function($var){return is_numeric($var);});

                if(!empty($pos_arr)) {

                    if(in_array(0, $pos_arr) || $is_post) {
                        $org_path = $this->replace_file_upload_path_shrtcd($org_path, $form_id);

                        $org_path_arr = explode("/", substr($org_path, 0, strlen($org_path) - 1));
                        $make_path = ABSPATH;
                        for($i = 0; $i < count($org_path_arr); $i++) {
                            $folder = trim($org_path_arr[$i]);
                            if(!empty($folder) && !is_dir($make_path . $folder)) {
                                mkdir($make_path . $folder);
                            }
                            $make_path .= $folder . "/";
                        }

                        if($make_path != ABSPATH && is_dir($make_path) && $custom_path == $make_path) {
                            $flag["status"] = true;
                            $flag["path"] = $custom_path;
                            if(!is_dir($custom_path . "thumbs")) mkdir($custom_path . "thumbs");
                        }
                    } else {
                        $first_shtcd_pos = min($pos_arr);
                        $static_path = substr($org_path, 0, $first_shtcd_pos);
                        $static_path = substr($static_path, 0, strrpos($static_path, "/"));

                        if(is_dir(ABSPATH . $static_path) && is_writable(ABSPATH .$static_path)) {
                            $dynamic_path = substr($org_path, (strlen($static_path) + 1));
                            $dynamic_path = $this->replace_file_upload_path_shrtcd($dynamic_path, $form_id);
                            $dynamic_path_arr = explode("/", substr($dynamic_path, 0, strlen($dynamic_path)-1));
                            $make_dynamic_path = ABSPATH . $static_path . "/";

                            for($i = 0; $i < count($dynamic_path_arr); $i++) {
                                $folder = trim($dynamic_path_arr[$i]);
                                if(!empty($folder) && !is_dir($make_dynamic_path . $folder)) {
                                    mkdir($make_dynamic_path . $folder);
                                    $make_dynamic_path .= $folder . "/";
                                }
                            }

                            if($make_dynamic_path != ABSPATH && is_dir($make_dynamic_path) && $custom_path == $make_dynamic_path) {
                                $flag["status"] = true;
                                $flag["path"] = $custom_path;
                                if(!is_dir($custom_path . "thumbs")) mkdir($custom_path . "thumbs");
                            }
                        }
                    }
                }
            }
        }
        return $flag;
    }

    function replace_file_upload_path_shrtcd($path, $form_id) {
        $replace_path = $path;
        $replace_path = str_replace("{form_id}", $form_id, $replace_path);
        $replace_path = str_replace("{year}", date("Y"), $replace_path);
        $replace_path = str_replace("{month}", date("m"), $replace_path);
        $replace_path = str_replace("{day}", date("d"), $replace_path);
        return $replace_path;
    }
}
