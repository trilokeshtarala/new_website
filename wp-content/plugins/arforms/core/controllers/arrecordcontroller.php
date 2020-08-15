<?php

class arrecordcontroller {

    function __construct() {


        add_action('admin_menu', array($this, 'menu'), 20);

        add_action('admin_init', array($this, 'admin_js'), 1);

        add_action('init', array($this, 'register_scripts'));

        add_action('wp_enqueue_scripts', array($this, 'add_js'));

        add_action('wp_footer', array($this, 'footer_js'), 1);

        add_action('admin_footer', array($this, 'footer_js'));

        add_action('arfentryexecute', array($this, 'process_update_entry'), 10, 4);

        add_filter('arfactionsubmitbutton', array($this, 'ajax_submit_button'), 10, 3);

        add_filter('arfformsubmitsuccess', array($this, 'get_confirmation_method'), 10, 2);

        add_action('arfformsubmissionsuccessaction', array($this, 'confirmation'), 10, 4);

        add_filter('arffieldsreplaceshortcodes', array($this, 'filter_shortcode_value'), 10, 4);

        add_action('wp_ajax_updatechart', array($this, 'updatechart'));

        add_action('wp_ajax_managecolumns', array($this, 'managecolumns'));

        add_action('wp_ajax_updateentries', array($this, 'arf_form_entries'));

        add_action('wp_ajax_arf_retrieve_form_entry',array($this,'arf_retrieve_form_entry_data'));

        add_action('wp_ajax_arfchangebulkentries', array($this, 'arfchangebulkentries'));

        add_action('wp_ajax_recordactions', array($this, 'arfentryactionfunc'));

        add_action('wp', array($this, 'process_entry'), 10, 0);

        add_action('wp_process_entry', array($this, 'process_entry'), 10, 0);

        add_filter('arfemailvalue', array($this, 'filter_email_value'), 10, 3);

        add_action('wp_ajax_current_modal', array($this, 'current_modal'));

        add_action('wp_ajax_nopriv_current_modal', array($this, 'current_modal'));
        add_action('wp_ajax_arf_edit_entry_values', array($this, 'arf_edit_entry_values'));

        add_action('wp_ajax_arf_delete_single_entry', array($this, 'arf_delete_single_entry_function'));

        add_action('wp_ajax_arf_reset_key', array($this, 'arf_reset_spam_filter_key'));
        add_action('wp_ajax_nopriv_arf_reset_key', array($this, 'arf_reset_spam_filter_key'));
        add_action('wp_ajax_arf_forms_file_remove', array($this, 'arf_forms_file_remove'));
    }
    function arf_forms_file_remove(){
        global $wpdb, $arffield, $MdlDb, $armainhelper, $arfieldhelper;
        
        if(isset($_POST['entry_id']) && isset($_POST['field_id']) && isset($_POST['file_id']) ){
            $res = $wpdb->get_row("SELECT * FROM $MdlDb->entry_metas WHERE field_id=".$_POST['field_id']." and entry_id=".$_POST['entry_id']);
            if(!empty($res)){
                $new_ids = array();
                $delete_file_id = array();
                $exp_ids = explode("|", $res->entry_value);
                if(in_array($_POST['file_id'], $exp_ids)){
                    $delete_file_id[0] = $_POST['file_id'];
                }
                
                if(count($exp_ids) > 1){

                    $new_ids = array_diff( $exp_ids, $delete_file_id );
                    $entry_val = implode($new_ids, "|");
                    echo $wpdb->query($wpdb->prepare("UPDATE $MdlDb->entry_metas SET entry_value=%s WHERE field_id=%d and entry_id=%d", $entry_val, $_POST['field_id'], $_POST['entry_id']));
                    
                } else{
                    echo $wpdb->query( "DELETE FROM $MdlDb->entry_metas WHERE field_id=".$_POST['field_id']." and entry_id=".$_POST['entry_id']);
                }
                
            }
            
            $image_url='';
            $thum_url='';
            $post_meta_data = get_post_meta($_POST['file_id']);
            if(isset($post_meta_data['_wp_attached_file']) && isset($post_meta_data['_wp_attached_file'][0])){
                $image_name = explode('/',$post_meta_data['_wp_attached_file'][0]);

                $image_name = $image_name[count($image_name) -1 ];

                $image_ext = explode('.',$image_name);

                $image_ext = $image_ext[count($image_ext) - 1];

                $image_ext = strtolower($image_ext);

                $exclude_ext = array('png','jpg','jpeg','jpe','gif','bmp','tif','tiff','ico');

                if( in_array($image_ext,$exclude_ext) ){
                    $image_url = ABSPATH.str_replace('thumbs/', '', $post_meta_data['_wp_attached_file'][0]);
                    $thum_url = ABSPATH.$post_meta_data['_wp_attached_file'][0];
                }
            }
            if($thum_url && $image_url){
                @unlink($thum_url);
                @unlink($image_url);
            }
            wp_delete_attachment($_POST['file_id']);
        }
        
        exit;
    }
    function arf_reset_spam_filter_key() {
        global $armainhelper;
        $form_id = $_REQUEST['form_id'];
        $frm_id = $_REQUEST['frm_id'];
        $possible_letters = '23456789bcdfghjkmnpqrstvwxyz';
        $random_dots = 0;
        $random_lines = 20;

        $session_var = '';
        $i = 0;
        while ($i < 8) {
            $session_var .= substr($possible_letters, mt_rand(0, strlen($possible_letters) - 1), 1);
            $i++;
        }
        $_SESSION['ARF_FILTER_INPUT'][$frm_id] = $session_var;
        echo json_encode(array('new_var' => $session_var));
        die();
    }

    function show_form($id = '', $key = '', $title = false, $description = false, $preview = false, $is_widget_or_modal = false) {

        global $arfform, $user_ID, $arfsettings, $post, $wpdb, $armainhelper, $arrecordcontroller, $arformcontroller, $MdlDb;

        $func_val = "true";
        if (!$preview) {
            $func_val = apply_filters('arf_hide_forms', $arformcontroller->arf_class_to_hide_form($id), $id);
        }

        if ($func_val != 'true' && !isset($_REQUEST['using_ajax']) && $_REQUEST['is_submit_form_' . $id] != 1) {
            $error = json_decode($func_val);
            echo $error->message;
            exit;
        }

        if ($id) {
            $form = $arfform->getOne((int) $id);
        } else if ($key) {
            $form = $arfform->getOne($key);
        }

        $is_confirmation_method = false;
        if (isset($_REQUEST['arf_conf']) and $_REQUEST['arf_conf'] != '') {
            if (isset($_REQUEST['arf_conf']) and $_REQUEST['arf_conf'] == $id) {
                $is_confirmation_method = true;
            }
        }


        $form = apply_filters('arfpredisplayform', $form);

        if ((@$form->is_template or @ $form->status == 'draft') and ! ($preview)) {
            return addslashes(esc_html__('Please select a valid form', 'ARForms'));
        } else if (!$form or ( ($form->is_template or $form->status == 'draft') and ! isset($_GET) and ! isset($_GET['form']))) {
            return addslashes(esc_html__('Please select a valid form', 'ARForms'));
        } else if ($form->is_loggedin && !$user_ID) {
            global $arfsettings;
            return do_shortcode($arfsettings->login_msg);
        }

        return $arrecordcontroller->get_form(VIEWS_PATH . '/formsubmission.php', $form, $title, $description, $preview, $is_widget_or_modal, $is_confirmation_method, $func_val);
    }

    function get_recordparams($form = null) {


        global $arfform, $arfform_params, $armainhelper, $MdlDb;





        if (!$form) {
            $form = $arfform->getAll(array(), 'name', 1);
        }





        if ($arfform_params and isset($arfform_params[$form->id])) {
            return $arfform_params[$form->id];
        }





        $action_var = isset($_REQUEST['arfaction']) ? 'arfaction' : 'action';


        $action = apply_filters('arfshownewentrypage', $armainhelper->get_param($action_var, 'new'), $form);





        $default_values = array(
            'id' => '', 'form_name' => '', 'paged' => 1, 'form' => $form->id, 'form_id' => $form->id,
            'field_id' => '', 'search' => '', 'sort' => '', 'sdir' => '', 'action' => $action
        );





        $values['posted_form_id'] = $armainhelper->get_param('form_id');


        if (!is_numeric($values['posted_form_id']))
            $values['posted_form_id'] = $armainhelper->get_param('form');





        if ($form->id == $values['posted_form_id']) {


            foreach ($default_values as $var => $default) {


                if ($var == 'action')
                    $values[$var] = $armainhelper->get_param($action_var, $default);
                else
                    $values[$var] = $armainhelper->get_param($var, $default);


                unset($var);


                unset($default);
            }
        }else {


            foreach ($default_values as $var => $default) {


                $values[$var] = $default;


                unset($var);


                unset($default);
            }
        }





        if (in_array($values['action'], array('create', 'update')) and ( !isset($_POST) or ( !isset($_POST['action']) and ! isset($_POST['arfaction'])))) {
            $values['action'] = 'new';
        }





        return $values;
    }

    function process_entry($errors = '') {

        
        global $wpdb, $arformcontroller, $MdlDb, $arfsettings;
        if (!isset($_POST) or ! isset($_POST['form_id']) or ! is_numeric($_POST['form_id']) or ! isset($_POST['entry_key'])) {
            return;
        }

        global $db_record, $arfform, $arfcreatedentry, $arfform_params, $arrecordcontroller;

        $form = $arfform->getOne($_POST['form_id']);


        if (!$form) {
            return;
        }


        if (!$arfform_params) {
            $arfform_params = array();
        }


        $params = $arrecordcontroller->get_recordparams($form);

        $arfform_params[$form->id] = $params;

        if (!$arfcreatedentry) {
            $arfcreatedentry = array();
        }

        if (isset($arfcreatedentry[$_POST['form_id']]))
            return;

        $_SESSION['arf_recaptcha_allowed_' . $_POST['form_id']] = isset($_SESSION['arf_recaptcha_allowed_' . $_POST['form_id']]) ? $_SESSION['arf_recaptcha_allowed_' . $_POST['form_id']] : '';

        $arferrormsg = "";
        $errors1 = array();
        if ($errors == '' && $_SESSION['arf_recaptcha_allowed_' . $_POST['form_id']] == "") {
            $arferr = array();
            $errors = $arrecordcontroller->internal_check_recaptcha();
            if (count($errors) > 0) {
                foreach ($errors as $field_id => $field_value) {
                    $arferr[$field_id] = $field_value;
                    $arferrormsg = $field_value;
                }

                $return["conf_method"] = "captchaerror";
                $return["message"] = $arferr;
                $return = apply_filters( 'arf_reset_built_in_captcha', $return, $_POST );
                if($_POST['form_submit_type'] == 1) {
                    echo json_encode($return);
                    exit;
                }
            }
        }

        unset($_SESSION['arf_recaptcha_allowed_' . $_POST['form_id']]);

        $arfcreatedentry[$_POST['form_id']] = array('errors' => $errors);

        $submit_type = $arfsettings->form_submit_type;

        if (isset($_POST['using_ajax']) and strtolower(trim($_POST['using_ajax'])) == 'yes') {

            $form_id = $_POST['form_id'];

            $arf_errors = array();

            $arf_form_data = array();

            $values = $_POST;

            $arf_form_data = apply_filters('arf_populate_field_from_outside', $arf_form_data, $form_id, $values); 

            $arf_errors = apply_filters('arf_validate_form_outside_errors', $arf_errors, $form_id, $values, $arf_form_data);

            if (isset($arf_errors['arf_form_data']) and $arf_errors['arf_form_data']) {
                $arf_form_data = array_merge($arf_form_data, $arf_errors['arf_form_data']);
            }

            unset($arf_errors['arf_form_data']);

            if (count($arf_form_data) > 0) {
                foreach ($arf_form_data as $fieldid => $fieldvalue)
                    $_POST[$fieldid] = $fieldvalue;
            }

            
            $formRandomKey = isset($_POST['form_random_key']) ? $_POST['form_random_key'] : '';
            $validate = TRUE;
            $is_check_spam = true;

            if ($is_check_spam) {
                $validate = apply_filters('is_to_validate_spam_filter', $validate, $formRandomKey);
            }
            if (!$validate) {
                $return["conf_method"] = "spamerror";
                $message = '<div class="arf_form ar_main_div_{arf_form_id} arf_error_wrapper" id="arffrm_{arf_form_id}_container"><div class="frm_error_style" id="arf_message_error"><div class="msg-detail"><div class="arf_res_front_msg_desc">' . addslashes(esc_html__('Spam Detected', 'ARForms')) . '</div></div></div></div>';
                $return["message"] = $message;
                $return = apply_filters('arf_reset_built_in_captcha',$return,$_POST);
                echo json_encode($return);
                exit;
            }
        } else if( !isset($_REQUEST['using_ajax']) || (isset($_REQUEST['using_ajax']) && strtolower(trim($_POST['using_ajax'])) != 'yes') ){
            if( $submit_type != 0){
                $this->ajax_check_spam_filter();
            }
        }

        if( !isset($arf_errors)  ){
            $arf_errors = array();
        }
        if (empty($errors) && @count($arf_errors) == 0) {

            $_POST['arfentrycookie'] = 1;

            if ($params['action'] == 'create') {

                if (apply_filters('arfcontinuetocreate', true, $_POST['form_id']) and ! isset($arfcreatedentry[$_POST['form_id']]['entry_id'])) {
                    $arfcreatedentry[$_POST['form_id']]['entry_id'] = $db_record->create($_POST);
                }
            }
            
            $item_meta_values = isset($_POST['item_meta']) ? $_POST['item_meta'] : array();

            $item_meta_values = $db_record->create($_POST,true);
            
            do_action('arfentryexecute', $params, $errors, $form,$item_meta_values);
            unset($_POST['arfentrycookie']);
        } else {

            if ($arf_errors) {

                $return["conf_method"] = "validationerror";
                $return["message"] = $arf_errors;
                $arferrormsg = $arfsettings->failed_msg;
                $return = apply_filters( 'arf_reset_built_in_captcha', $return, $_POST );
                if($_POST['form_submit_type'] == 1) {
                    echo json_encode($return);
                    exit;
                }
            }

            if($_POST['form_submit_type'] == 1) {
                exit;
            } else {
                do_shortcode("[ARForms id=" . $_POST['form_id'] . " arfsubmiterrormsg='".$arferrormsg."' ]");
            }

        }

        if (isset($_POST['using_ajax']) and $_POST['using_ajax'] == 'yes') {
            echo do_shortcode("[ARForms id=" . $_POST['form_id'] . "]");
        }
    }

    function menu() {


        global $arfsettings, $armainhelper;


        if (current_user_can('administrator') and ! current_user_can('arfviewentries')) {

            global $wp_roles;

            $arfroles = $armainhelper->frm_capabilities();

            foreach ($arfroles as $arfrole => $arfroledescription) {


                if (!in_array($arfrole, array('arfviewforms', 'arfeditforms', 'arfdeleteforms', 'arfchangesettings', 'arfimportexport', 'arfviewpopupform'))) {
                    $wp_roles->add_cap('administrator', $arfrole);
                }
            }
        }


        add_submenu_page('ARForms', 'ARForms' . ' | ' . addslashes(esc_html__('Form Entries', 'ARForms')), addslashes(esc_html__('Form Entries', 'ARForms')), 'arfviewentries', 'ARForms-entries', array($this, 'route'));


        add_action('admin_head-' . 'ARForms' . '_page_ARForms-entries', array($this, 'head'));
    }

    function head() {


        global $style_settings, $armainhelper;


        $css_file = array($armainhelper->jquery_css_url($style_settings->arfcalthemecss));


        require(VIEWS_PATH . '/head.php');
    }

    function admin_js() {

        if (isset($_GET) and isset($_GET['page']) and ( 'ARForms-popups'==$_GET['page'] || $_GET['page'] == 'ARForms-entries' or $_GET['page'] == 'ARForms-entry-templates' or $_GET['page'] == 'ARForms-import' or $_REQUEST['page'] == "ARForms" && ((isset($_REQUEST['arfaction']) && $_REQUEST['arfaction'] == 'edit') || (isset($_REQUEST['arfaction']) && $_REQUEST['arfaction'] == 'new') || (isset($_REQUEST['arfaction']) && $_REQUEST['arfaction']) == 'duplicate' || (isset($_REQUEST['arfaction']) && $_REQUEST['arfaction'] == 'update')))) {

            if (!function_exists('wp_editor')) {


                add_action('admin_print_footer_scripts', 'wp_tiny_mce', 25);


                add_filter('tiny_mce_before_init', array($this, 'remove_fullscreen'));


                if (user_can_richedit()) {


                    wp_enqueue_script('editor');


                    wp_enqueue_script('media-upload');
                }


                wp_enqueue_script('common');


                wp_enqueue_script('post');
            }


            if ( 'ARForms-popups'==$_GET['page'] || $_GET['page'] == 'ARForms-entries' or $_REQUEST['page'] == "ARForms" && ($_REQUEST['arfaction'] == 'edit' || $_REQUEST['arfaction'] == 'new' || $_REQUEST['arfaction'] == 'duplicate' || $_REQUEST['arfaction'] == 'update')) {
                wp_enqueue_script('bootstrap-locale-js');
                wp_enqueue_script('bootstrap-datepicker');
            }
        }
    }

    function remove_fullscreen($init) {


        if (isset($init['plugins'])) {


            $init['plugins'] = str_replace('wpfullscreen,', '', $init['plugins']);


            $init['plugins'] = str_replace('fullscreen,', '', $init['plugins']);
        }

        return $init;
    }

    function register_scripts() {


        global $wp_scripts, $armainhelper, $arfversion;

        wp_register_script('bootstrap-locale-js', ARFURL . '/bootstrap/js/moment-with-locales.js', array('jquery'), $arfversion);

        wp_register_script('bootstrap-datepicker', ARFURL . '/bootstrap/js/bootstrap-datetimepicker.js', array('jquery'), $arfversion, true);
    }

    function add_js() {


        if (is_admin())
            return;


        global $arfsettings, $arfversion;


        if ($arfsettings->accordion_js) {


            wp_enqueue_script('jquery-ui-widget');


            wp_enqueue_script('jquery-ui-accordion', ARFURL . '/js/jquery.ui.accordion.js', array('jquery', 'jquery-ui-core'), $arfversion, true);
        }
    }

    function &filter_email_value($value, $meta, $entry, $atts = array()) {
        global $arffield;
        $field = $arffield->getOne($meta->field_id);
        if (!$field)
            return $value;
        $value = $this->filter_entry_display_value($value, $field, $atts);
        return $value;
    }

    function footer_js($preview = false, $is_print = false) {
        global $wp_version;
        $path = $_SERVER['REQUEST_URI'];
        $file_path = basename($path);

        if (!strstr($file_path, "post.php")) {


            global $arfversion, $arfforms_loaded, $arf_form_all_footer_js, $is_arf_preview, $arf_modal_loaded, $arfsettings,$footer_cl_logic;

            $is_multi_column_loaded = array();

            if (empty($arfforms_loaded))
                return;

            if ($is_print) {
                $print_style = 'wp_print_styles';
                $print_script = 'wp_print_scripts';
            } else {
                $print_style = 'wp_enqueue_style';
                $print_script = 'wp_enqueue_script';
            }

            $load_js_css = $arfsettings->arf_load_js_css;
            foreach ($arfforms_loaded as $form) {

                if (!is_object($form))
                    continue;


                $wp_upload_dir = wp_upload_dir();
                if (is_ssl()) {
                    $upload_main_url = str_replace("http://", "https://", $wp_upload_dir['baseurl'] . '/arforms/maincss');
                } else {
                    $upload_main_url = $wp_upload_dir['baseurl'] . '/arforms/maincss';
                }
                $fid1 = $upload_main_url . '/maincss_' . $form->id . '.css';

                $print_script('jquery');

                $print_script('arfbootstrap-js');
                $print_script('jquery-validation');

                $form->options = maybe_unserialize($form->options);


                if ((isset($form->options['font_awesome_loaded']) && $form->options['font_awesome_loaded'] ) || in_array('fontawesome', $load_js_css)) {
                    $print_style('arf-fontawesome-css');
                }

                if ((isset($form->options['tooltip_loaded']) && $form->options['tooltip_loaded']) || in_array('tooltip', $load_js_css)) {
                    $print_style('arf_tipso_css_front');
                    $print_script('arf_tipso_js_front');
                }
                if ((isset($form->options['arf_input_mask']) && $form->options['arf_input_mask']) || in_array('mask_input', $load_js_css)) {
                    $print_script('arfbootstrap-inputmask');
                    $print_script('jquery-maskedinput');
                    $print_script('arforms_phone_intl_input');
                    $print_script('arforms_phone_utils');
                }
                
                if ((isset($form->options['arf_number_animation']) && $form->options['arf_number_animation'] ) || in_array('animate_number', $load_js_css)) {
                    $print_script('animate-numbers');
                }
                if ((isset($form->options['arf_page_break_wizard']) && $form->options['arf_page_break_wizard'] ) || in_array('page_break_wizard', $load_js_css)) {
                    if( version_compare($wp_version, '4.2', '<') ){
                        $print_script('jquery-ui-custom');
                    } else {
                        $print_script('jquery-ui-core');
                    }
                    $print_script('jquery-effects-slide');
                }
                if ((isset($form->options['arf_page_break_survey']) && $form->options['arf_page_break_survey'] ) || in_array('page_break_survey', $load_js_css)) {
                    if( version_compare($wp_version, '4.2', '<') ){
                        $print_script('jquery-ui-custom');
                        $print_script('jquery-ui-widget-custom');
                    } else {
                        $print_script('jquery-ui-core');
                        $print_script('jquery-ui-widget');
                    }
                    $print_script('jquery-effects-slide');
                }
                if ((isset($form->options['arf_autocomplete_loaded']) && $form->options['arf_autocomplete_loaded']) || in_array('autocomplete', $load_js_css)) {
                    $print_script('bootstrap-typeahead-js');
                }


                $loaded_field = isset($form->options['arf_loaded_field']) ? $form->options['arf_loaded_field'] : array();
                
                if (in_array('select', $loaded_field) || in_array('dropdown', $load_js_css) ) {
                    $print_style('arfbootstrap-select');
                    $print_script('jquery-bootstrap-slect');
                }
                if (in_array('file', $loaded_field) || in_array('file', $load_js_css)) {
                    $print_style('arf-filedrag');
                    $print_script('filedrag');
                }
                if (in_array('time', $loaded_field) || in_array('date', $loaded_field) || in_array('date_time', $load_js_css)) {

                    $css_file = ARFURL . '/bootstrap/css/bootstrap-datetimepicker.css';
                    $print_style('form_custom_css-default_theme', $css_file, array(), $arfversion);
                    $print_script('bootstrap-locale-js');
                    $print_style('arfbootstrap-datepicker-css');
                    $print_script('bootstrap-datepicker');
                }

                if (in_array('slider', $loaded_field) || in_array('arfslider', $loaded_field) || in_array('slider', $load_js_css)) {
                    $print_script('arfbootstrap-modernizr-js');
                    $print_script('arfbootstrap-slider-js');
                    $print_style('arfbootstrap-slider');
                }

                if (in_array('captcha', $loaded_field) || in_array('captcha', $load_js_css)) {
                    $print_script('recaptcha-ajax');
                    $print_style('arfrecaptchacss');
                }

                if ((in_array('colorpicker', $loaded_field) && $form->options['arf_advance_colorpicker'] == 1) || in_array('colorpicker', $load_js_css)) {
                    $print_style('arf-fontawesome-css');
                    $print_script('arf_js_color');
                }

                if ((in_array('colorpicker', $loaded_field) && $form->options['arf_normal_colorpicker'] == 1) || in_array('colorpicker', $load_js_css)) {
                    $print_script('arf-colorpicker-basic-js');
                }

                do_action('wp_arf_footer', $loaded_field);
                if( !is_admin() ){
                    $print_script('arforms');
                }
                $print_script('arf-conditional-logic-js');
                
                if ($arf_modal_loaded) {
                    $print_script('arf-modal-js');
                }

                if ((isset($form->options['google_captcha_loaded']) && $form->options['google_captcha_loaded'] ) || in_array('captcha', $load_js_css)) {
                    $lang = $arfsettings->re_lang;
                    echo '<script type="text/javascript" data-cfasync="false" src="https://www.google.com/recaptcha/api.js?hl=' . $lang . '&onload=render_arf_captcha&render=explicit"></script>';
                }
            }


            ?>
            <script  type="text/javascript" defer="defer" data-cfasync="false" id="arf_footer_js">
            //window.addEventListener('DOMContentLoaded', function() {
            //(function($) {
            jQuery(document).ready(function(){
                <?php
                if ($is_multi_column_loaded)
                    $is_multi_column_loaded = array_unique($is_multi_column_loaded);
                if (is_rtl() && count($is_multi_column_loaded) > 0) {
                    $form_str = "";
                    foreach ($is_multi_column_loaded as $multicol_forms) {
                        $form_str .= "#form_" . $multicol_forms . ", ";
                    }
                    $form_str = rtrim($form_str, ", ");

                    ?>

                        jQuery(document).ready(function () {



                            var screenwidth = jQuery(window).width();
                            if (screenwidth >= 480)
                            {
                                var tabindex = 2;
                                jQuery("<?php echo $form_str ?>").each(function () {
                                    var form = jQuery(this);
                                    var two_col_1_tabi = '';
                                    var two_col_1_field = '';
                                    var three_col_1_tabi = '';
                                    var three_col_1_field = '';
                                    var three_col_2_tabi = '';
                                    var three_col_2_field = '';

                                    jQuery(form).find('input, textarea, select, .vpb_input_fields').each(function (e, item) {
                                        var field = jQuery(this);
                                        field_id = field.attr('name');
                                        field_type = field.attr('type');
                                        if (field_id && (field_id.indexOf('item_meta') != '-1')
                                        {
                                            if (jQuery(field).parents('.arfformfield').first().hasClass('frm_first_half')) {
                                                three_col_1_tabi = '';
                                                three_col_1_field = '';
                                                three_col_2_tabi = '';
                                                three_col_2_field = '';

                                                two_col_1_tabi = tabindex;
                                                two_col_1_field = item;
                                                jQuery(field).attr('tabindex', tabindex);
                                                if (!jQuery.isFunction(change_tabindex_radio)) {
                                                    return;
                                                }
                                                change_tabindex_radio(field, tabindex);
                                            } else if (jQuery(field).parents('.arfformfield').first().hasClass('frm_last_half')) {
                                                three_col_1_tabi = '';
                                                three_col_1_field = '';
                                                three_col_2_tabi = '';
                                                three_col_2_field = '';

                                                jQuery(two_col_1_field).attr('tabindex', tabindex);
                                                jQuery(field).attr('tabindex', two_col_1_tabi);
                                                if (!jQuery.isFunction(change_tabindex_radio)) {
                                                    return;
                                                }
                                                change_tabindex_radio(two_col_1_field, tabindex);
                                                change_tabindex_radio(field, two_col_1_tabi);

                                                two_col_1_tabi = '';
                                                two_col_1_field = '';
                                            } else if (jQuery(field).parents('.arfformfield').first().hasClass('frm_first_third')) {
                                                two_col_1_tabi = '';
                                                two_col_1_field = '';

                                                jQuery(field).attr('tabindex', tabindex);
                                                if (!jQuery.isFunction(change_tabindex_radio)) {
                                                    return;
                                                }
                                                change_tabindex_radio(field, tabindex);

                                                three_col_1_tabi = tabindex;
                                                three_col_1_field = item;
                                                three_col_2_tabi = '';
                                                three_col_2_field = '';
                                            } else if (jQuery(field).parents('.arfformfield').first().hasClass('frm_third')) {
                                                two_col_1_tabi = '';
                                                two_col_1_field = '';

                                                jQuery(three_col_1_field).attr('tabindex', tabindex);
                                                jQuery(field).attr('tabindex', three_col_1_tabi);
                                                if (!jQuery.isFunction(change_tabindex_radio)) {
                                                    return;
                                                }
                                                change_tabindex_radio(three_col_1_field, tabindex);
                                                change_tabindex_radio(field, three_col_1_tabi);

                                                three_col_2_tabi = three_col_1_tabi;
                                                three_col_1_tabi = tabindex;
                                                three_col_2_field = item;
                                            } else if (jQuery(field).parents('.arfformfield').first().hasClass('frm_last_third')) {
                                                two_col_1_tabi = '';
                                                two_col_1_field = '';

                                                jQuery(three_col_1_field).attr('tabindex', tabindex);
                                                jQuery(three_col_2_field).attr('tabindex', three_col_1_tabi);
                                                jQuery(field).attr('tabindex', three_col_2_tabi);
                                                if (!jQuery.isFunction(change_tabindex_radio)) {
                                                    return;
                                                }
                                                change_tabindex_radio(three_col_1_field, tabindex);
                                                change_tabindex_radio(three_col_2_field, three_col_1_tabi);
                                                change_tabindex_radio(field, three_col_2_tabi);

                                                three_col_1_tabi = '';
                                                three_col_1_field = '';
                                                three_col_2_tabi = '';
                                                three_col_2_field = '';
                                            } else {
                                                two_col_1_tabi = '';
                                                two_col_1_field = '';
                                                three_col_1_tabi = '';
                                                three_col_1_field = '';
                                                three_col_2_tabi = '';
                                                three_col_2_field = '';
                                                jQuery(field).attr('tabindex', tabindex);
                                                if (!jQuery.isFunction(change_tabindex_radio)) {
                                                    return;
                                                }
                                                change_tabindex_radio(field, tabindex);
                                            }
                                            tabindex++;
                                        }
                                    });
                                });
                            }
                        });

                        jQuery(window).resize(function () {
                            var screenwidth = jQuery(window).width();
                            if (screenwidth >= 480)
                            {

                                var tabindex = 2;
                                jQuery("<?php echo $form_str ?>").each(function () {
                                    var form = jQuery(this);
                                    var two_col_1_tabi = '';
                                    var two_col_1_field = '';
                                    var three_col_1_tabi = '';
                                    var three_col_1_field = '';
                                    var three_col_2_tabi = '';
                                    var three_col_2_field = '';

                                    jQuery(form).find('input, textarea, select, .vpb_input_fields').each(function (e, item) {
                                        var field = jQuery(this);
                                        field_id = field.attr('name');
                                        field_type = field.attr('type');
                                        if (field_id && (field_id.indexOf('item_meta') != '-1')
                                        {
                                            if (jQuery(field).parents('.arfformfield').first().hasClass('frm_first_half')) {
                                                three_col_1_tabi = '';
                                                three_col_1_field = '';
                                                three_col_2_tabi = '';
                                                three_col_2_field = '';

                                                two_col_1_tabi = tabindex;
                                                two_col_1_field = item;
                                                jQuery(field).attr('tabindex', tabindex);
                                                if (!jQuery.isFunction(change_tabindex_radio)) {
                                                    return;
                                                }
                                                change_tabindex_radio(field, tabindex);
                                            } else if (jQuery(field).parents('.arfformfield').first().hasClass('frm_last_half')) {
                                                three_col_1_tabi = '';
                                                three_col_1_field = '';
                                                three_col_2_tabi = '';
                                                three_col_2_field = '';

                                                jQuery(two_col_1_field).attr('tabindex', tabindex);
                                                jQuery(field).attr('tabindex', two_col_1_tabi);
                                                if (!jQuery.isFunction(change_tabindex_radio)) {
                                                    return;
                                                }
                                                change_tabindex_radio(two_col_1_field, tabindex);
                                                change_tabindex_radio(field, two_col_1_tabi);

                                                two_col_1_tabi = '';
                                                two_col_1_field = '';
                                            } else if (jQuery(field).parents('.arfformfield').first().hasClass('frm_first_third')) {
                                                two_col_1_tabi = '';
                                                two_col_1_field = '';

                                                jQuery(field).attr('tabindex', tabindex);
                                                if (!jQuery.isFunction(change_tabindex_radio)) {
                                                    return;
                                                }
                                                change_tabindex_radio(field, tabindex);

                                                three_col_1_tabi = tabindex;
                                                three_col_1_field = item;
                                                three_col_2_tabi = '';
                                                three_col_2_field = '';
                                            } else if (jQuery(field).parents('.arfformfield').first().hasClass('frm_third')) {
                                                two_col_1_tabi = '';
                                                two_col_1_field = '';

                                                jQuery(three_col_1_field).attr('tabindex', tabindex);
                                                jQuery(field).attr('tabindex', three_col_1_tabi);
                                                if (!jQuery.isFunction(change_tabindex_radio)) {
                                                    return;
                                                }
                                                change_tabindex_radio(three_col_1_field, tabindex);
                                                change_tabindex_radio(field, three_col_1_tabi);

                                                three_col_2_tabi = three_col_1_tabi;
                                                three_col_1_tabi = tabindex;
                                                three_col_2_field = item;
                                            } else if (jQuery(field).parents('.arfformfield').first().hasClass('frm_last_third')) {
                                                two_col_1_tabi = '';
                                                two_col_1_field = '';

                                                jQuery(three_col_1_field).attr('tabindex', tabindex);
                                                jQuery(three_col_2_field).attr('tabindex', three_col_1_tabi);
                                                jQuery(field).attr('tabindex', three_col_2_tabi);
                                                if (!jQuery.isFunction(change_tabindex_radio)) {
                                                    return;
                                                }
                                                change_tabindex_radio(three_col_1_field, tabindex);
                                                change_tabindex_radio(three_col_2_field, three_col_1_tabi);
                                                change_tabindex_radio(field, three_col_2_tabi);

                                                three_col_1_tabi = '';
                                                three_col_1_field = '';
                                                three_col_2_tabi = '';
                                                three_col_2_field = '';
                                            } else {
                                                two_col_1_tabi = '';
                                                two_col_1_field = '';
                                                three_col_1_tabi = '';
                                                three_col_1_field = '';
                                                three_col_2_tabi = '';
                                                three_col_2_field = '';
                                                jQuery(field).attr('tabindex', tabindex);
                                                if (!jQuery.isFunction(change_tabindex_radio)) {
                                                    return;
                                                }
                                                change_tabindex_radio(field, tabindex);
                                            }
                                            tabindex++;
                                        }
                                    });
                                });
                            } else {
                                var tabindex = 2;
                                jQuery("<?php echo $form_str ?>").each(function () {
                                    var form = jQuery(this);
                                    jQuery(form).find('input, textarea, select, .vpb_input_fields').each(function (e, item) {
                                        var field = jQuery(this);
                                        field_id = field.attr('name');
                                        field_type = field.attr('type');
                                        if (field_id && (field_id.indexOf('item_meta') != '-1'))
                                        {
                                            field.attr("tabindex", tabindex);
                                            tabindex++;
                                        }
                                    });
                                });
                            }

                        });
                    <?php
                }
                ?>
                    if (typeof (__ARFERR) != 'undefined') {
                        var file_error = __ARFERR;
                    } else {
                        var file_error = '<?php echo addslashes(esc_html__('Sorry, this file type is not permitted for security reasons.', 'ARForms')); ?>';
                    }

                <?php
                $preview = isset($is_arf_preview) ? $is_arf_preview : 0;
                if ($preview != true) {
                    ?>
                        var form1chk = "";
                        jQuery(document).ready(function ($) {
                            var modalsCollection = document.querySelectorAll(".arfmodal");
                            var modalhtml = "";
                            var bodyhtml = "";

                            if (modalsCollection.length > 0)
                            {
                                for (var i = 0; i < modalsCollection.length; i++)
                                {
                                    var modal1 = modalsCollection[i].id;
                                    var checkmodal = modal1.split("-");
                                    if (checkmodal[0] == 'popup' && checkmodal[1] == 'form')
                                    {
                                        var attr_style = jQuery('#' + modal1).attr('style');
                                        var aria_hidden = jQuery('#' + modal1).attr('aria-hidden');
                                        var class_style = jQuery('#' + modal1).attr('class');
                                        modalhtml += '<div id="' + modal1 + '" class="' + class_style + '" aria-hidden="' + aria_hidden + '" style="' + attr_style + '">';
                                        modalhtml += jQuery('#' + modal1).html();
                                        modalhtml += '</div>';

                                        jQuery('#' + modal1).empty();
                                        jQuery('#' + modal1).removeAttr('class');
                                        jQuery('#' + modal1).removeAttr('aria-hidden');
                                        jQuery('#' + modal1).removeAttr('style');
                                        jQuery('#' + modal1).attr('style', 'display:none;');
                                        jQuery('#' + modal1).removeAttr('id');
                                    }
                                }
                                if (modalhtml != "")
                                {
                                    jQuery('body').append(modalhtml);
                                }
                            }


                            if (document.getElementsByClassName) {
                                var modalsCollection = document.getElementsByClassName("arf_flymodal");
                            }
                            else
                            {
                                var modalsCollection = document.querySelectorAll(".arf_flymodal");
                            }
                            var modalhtml = "";
                            var bodyhtml = "";
                            var modal1 = '';
                            var checkmodal = '';
                            var attr_style = '';
                            var class_style = '';
                            if (modalsCollection.length > 0)
                            {
                                for (var i = 0; i < modalsCollection.length; i++)
                                {
                                    modal1 = modalsCollection[i].id;
                                    checkmodal = modal1.split("-");
                                    if (checkmodal[0] == 'arf' && checkmodal[1] == 'popup' && checkmodal[2] == 'form')
                                    {
                                        attr_style = jQuery('#' + modal1).attr('style');
                                        class_style = jQuery('#' + modal1).attr('class');
                                        modalhtml += '<div id="' + modal1 + '" class="' + class_style + '" style="' + attr_style + '">';
                                        modalhtml += jQuery('#' + modal1).html();
                                        modalhtml += '</div>';

                                        jQuery('#' + modal1).empty();
                                        jQuery('#' + modal1).attr('style', 'display:none;');
                                        jQuery('#' + modal1).removeAttr('id');
                                    }
                                }
                                if (modalhtml != "")
                                {
                                    jQuery('body').append(modalhtml);
                                }
                            }

                            arfjqueryobj = new Object();
                            if (typeof $(".arfpagebreakform").find("input,select,textarea").not("[type=submit],div[id='recaptcha_style'] input").jqBootstrapValidation == 'function')
                            {
                                arfjqueryobj = $;
                            }
                            else
                            {
                                arfjqueryobj = jQuery.noConflict(true);
                            }
                            function getQueryStringValue(key) {
                                return unescape(window.location.search.replace(new RegExp("^(?:.*[&\\?]" + escape(key).replace(/[\.\+\*]/g, "\\$&") + "(?:\\=([^&]*))?)?.*$", "i"), "$1"));
                            }

                            var vc_action = getQueryStringValue("vc_editable");
                            if (typeof jQuery == 'undefined' && vc_action != '' && vc_action == 'true') {
                                jQuery = $;
                            }

                            if( typeof jQuery == 'undefined' && typeof $ != 'undefined' ){
                                jQuery = $;
                            }

                            var is_loaded = false;
                            if( typeof window.arfDocLoaded != 'undefined' && document.readyState === 'complete' ){
                                var formLength = document.getElementsByClassName('allfields');
                                setTimeout(function() {
                                    for (var f = 0; f < formLength.length; f++) {
                                        formLength[f].removeAttribute('style');
                                    }
                                }, 500);
                                var form_ids = [];
                                jQuery('.arf_form_outer_wrapper').each(function() {
                                    var form = jQuery(this).find('form.arfshowmainform');
                                    var id = form.attr('data-random-id');
                                    if (typeof id != 'undefined') {
                                        form_ids.push(id);
                                    }
                                });
                                if (typeof form_ids != 'undefined' && form_ids.length > 0) {
                                    for (var n in form_ids) {
                                        if (typeof form_ids[n] != "undefined") {
                                            var form_obj = jQuery("form[data-random-id='" + form_ids[n] + "']");
                                            var nonce_start_time = form_obj.find('[data-id="nonce_start_time"]').val();
                                            var nonce_keyboard_press = form_obj.find('[data-id="nonce_keyboard_press"]').val();
                                            arf_spam_filter_keypress_check(nonce_start_time, nonce_keyboard_press);
                                            if (typeof form_obj.attr('data-submission-key') != 'undefined') {
                                                var formSubmissionKey = form_obj.attr('data-submission-key');
                                                var filteredInput = document.createElement('input');
                                                filteredInput.setAttribute('type', 'text');
                                                filteredInput.setAttribute('data-jqvalidate', false);
                                                filteredInput.setAttribute('style', 'visibility:hidden !important;display:none !important;opacity:0 !important;');
                                                filteredInput.setAttribute('name', formSubmissionKey);
                                                form_obj.removeAttr('data-submission-key');
                                                form_obj.append(filteredInput);
                                            }
                                        }
                                    }
                                }
                                is_loaded = true;
                            }
                            
                            jQuery(document).ready(function (arfjqueryobj) {

                                if( is_loaded == false ){
                                    var formLength = document.getElementsByClassName('allfields');
                                    setTimeout(function() {
                                        for (var f = 0; f < formLength.length; f++) {
                                            formLength[f].removeAttribute('style');
    										
    										jQuery('#brand-div').attr('style', 'margin-top:30px !important; font-size:12px !important; color: #444444 !important; display:block !important; visibility:visible !important;');
    										
    										jQuery('#brand-div a').attr('style', 'color:#0066cc !important; font-size:12px !important; display:inline !important; visibility:visible !important;');
    										
    										jQuery('#brand-div span').attr('style', 'color:#FF0000 !important; font-size:12px !important; display:inline !important; visibility:visible !important;');
    										
                                        }
                                    }, 500);

                                    var form_ids = [];
                                    jQuery('.arf_form_outer_wrapper').each(function() {
                                        var form = jQuery(this).find('form.arfshowmainform');
                                        var id = form.attr('data-random-id');
                                        if (typeof id != 'undefined') {
                                            form_ids.push(id);
                                        }
                                    });
                                    if (typeof form_ids != 'undefined' && form_ids.length > 0) {
                                        for (var n in form_ids) {
                                            if (typeof form_ids[n] != "undefined") {
                                                var form_obj = jQuery("form[data-random-id='" + form_ids[n] + "']");
                                                var nonce_start_time = form_obj.find('[data-id="nonce_start_time"]').val();
                                                var nonce_keyboard_press = form_obj.find('[data-id="nonce_keyboard_press"]').val();
                                                arf_spam_filter_keypress_check(nonce_start_time, nonce_keyboard_press);
                                                if (typeof form_obj.attr('data-submission-key') != 'undefined') {
                                                    var formSubmissionKey = form_obj.attr('data-submission-key');
                                                    var filteredInput = document.createElement('input');
                                                    filteredInput.setAttribute('type', 'text');
                                                    filteredInput.setAttribute('data-jqvalidate', false);
                                                    filteredInput.setAttribute('style', 'visibility:hidden !important;display:none !important;opacity:0 !important;');
                                                    filteredInput.setAttribute('name', formSubmissionKey);
                                                    form_obj.removeAttr('data-submission-key');
                                                    form_obj.append(filteredInput);
                                                }
                                            }
                                        }
                                    }
                                }
                                
                                if( arfjqueryobj('.arf_materialize_form').length > 0 ){
                                    Materialize.updateTextFields();
                                    arfjqueryobj('.arf_materialize_form').find('select').material_select();
                                }

                                if( typeof selectpicker != 'undefined'){
                                    arfjqueryobj('.arf_fieldset:not(.arf_materialize_form) .sltstandard_front select').selectpicker('refresh');
                                }
                                
                                arf_initialize_material_autocomplete();

                                jQuery('.arf_previous_modal').remove();

                                var onLoadModals = document.querySelectorAll('a[data-onload="1"]');
                                
                                if( typeof window.onLoadClicked != 'undefined' && window.onLoadClicked == false ){
                                    if( onLoadModals.length > 0 ){
                                        setTimeout(function(){
                                            for( var i = 0; i < onLoadModals.length; i++ ){
                                                var current_modal = onLoadModals[i];
                                                jQuery(current_modal).trigger('click');
                                                window.onLoadClicked = true;
                                            }
                                        },1000);
                                    } else {
                                        window.onLoadClicked = false;
                                    }

                                } else if( onLoadModals.length < 1 ){
                                    window.onLoadClicked = false;
                                }

                                var onTimerModals = document.querySelectorAll('a[data-ontimer="1"]');
                                
                                if( typeof window.onTimerClicked != 'undefined' && window.onTimerClicked == false ){
                                    if( onTimerModals.length > 0 ){
                                        for( var t = 0; t < onTimerModals.length; t++ ){
                                            var currentTimer = onTimerModals[t];
                                            var currentDelay = jQuery(currentTimer).attr('data-delay');
                                            
                                            setTimeout(function(){
                                                jQuery(currentTimer).trigger('click');
                                                window.onTimerModals = true;
                                            },currentDelay);
                                        }
                                    } else {
                                        window.onTimerModals = false;
                                    }
                                } else {
                                    window.onTimerModals = false;
                                }

                                var onIdleModals = document.querySelectorAll('a[data-onidle="1"]');


                                if( typeof onIdleClicked != 'undefined' && window.onIdleClicked == false ){
                                    if( onIdleModals.length > 0 ){
                                        document.addEventListener("mousemove",function(){
                                            resetTimeout();
                                        });

                                        document.addEventListener("keydown",function(e){
                                            resetTimeout();
                                        });
                                        init_timer();
                                        
                                        window.onIdleClicked = true;
                                    } else {
                                        window.onIdleClicked = false;
                                    }
                                } else {
                                    window.onIdleClicked = false;
                                }
                                
                                arfjqueryobj(".arfpagebreakform").find("input,select,textarea").not("[type=submit],div[id='recaptcha_style'] input").jqBootstrapValidation({
                                    submitSuccess: function ($form, event) {
                                        
                                        var form1 = jQuery($form).attr('data-form-id');
                                                
                                        var object = jQuery('.arfshowmainform[data-form-id="' + form1+'"]');

                                        form_data_id = jQuery($form).attr('data-id');

                                        jQuery("form.arfshowmainform").each(function () {

                                            if (parseInt(jQuery(this).attr('data-id')) == parseInt(form_data_id)) {
                                                
                                                var object = jQuery(this);

                                                var break_form_id = jQuery(object).find('input[name="form_id"]').val();
                                                var break_val = jQuery(object).find('[data-id="submit_form_' + break_form_id + '"]').val();
                                                var next_id = jQuery(object).find('[data-id="submit_form_' + break_form_id + '"]').attr('data-val');
                                                var max_id = jQuery(object).find('[data-id="submit_form_' + break_form_id + '"]').attr('data-max');

                                                if (break_val == 1) {

                                                    event.preventDefault();

                                                    var is_goto_next = false;

                                                    if (jQuery(object).find('[data-id="form_submit_type"]').val() == 1){
                                                        var upload_flag = 0;
                                                        jQuery(object).find(".original,.arf_reply_drag_file").each(function (index) {
                                                            
                                                            var fileToUpload = jQuery(this).attr('data-file-valid');
                                                            
                                                            var is_visible = jQuery(this).is(':visible');
                                                            if( !is_visible ){
                                                                is_visible = jQuery(this).parents('.arf_field_type_file').is(':visible');
                                                            }

                                                            if (is_visible && fileToUpload == 'false'){
                                                                var fileId = jQuery(this).attr('id');
                                                                var file = document.getElementById(fileId);

                                                                var $this = jQuery('#' + fileId);
                                                                var $controlGroup = $this.parents(".control-group").first();
                                                                var $helpBlock = $controlGroup.find(".help-block").first();

                                                                if (jQuery('#' + fileId).attr('data-invalid-message') !== undefined && jQuery('#' + fileId).attr('data-invalid-message') != '') {
                                                                    var arf_invalid_file_message = jQuery('#' + fileId).attr('data-invalid-message');
                                                                } else {
                                                                    var arf_invalid_file_message = file_error;
                                                                }
                                                                var form_id = $this.closest('form').find('[data-id="form_id"]').val();
                                                                var error_type = (jQuery('[data-id="form_tooltip_error_' + form_id + '"]').val() == 'advance') ? 'advance' : 'normal';

                                                                window.arf_is_submitting_form = false;

                                                                if (error_type == 'advance')
                                                                {
                                                                    if (!jQuery.isFunction(arf_show_tooltip)) {
                                                                        return;
                                                                    }
                                                                    arf_show_tooltip($controlGroup, $helpBlock, arf_invalid_file_message);
                                                                } else {
                                                                    if (!$helpBlock.length) {
                                                                        $helpBlock = jQuery('<div class="help-block"><ul><li>' + arf_invalid_file_message + '</li></ul></div>');
                                                                        $controlGroup.find('.controls').append($helpBlock);
                                                                        $controlGroup.find('.controls .help-block').removeClass('arfanimated bounceInDownNor').addClass('arfanimated bounceInDownNor');
                                                                    }
                                                                    else
                                                                    {
                                                                        $helpBlock = jQuery('<ul role="alert"><li>' + arf_invalid_file_message + '</li></ul>');
                                                                        $controlGroup.find('.controls .help-block').empty();
                                                                        $controlGroup.find('.controls .help-block').append($helpBlock);
                                                                        $controlGroup.find('.controls .help-block').removeClass('arfanimated bounceInDownNor').addClass('arfanimated bounceInDownNor');
                                                                    }
                                                                }
                                                                upload_flag++;

                                                            }
                                                        });

                                                        if (upload_flag > 0)
                                                        {

                                                            jQuery('#submit_loader').hide();
                                                            jQuery(object).find('input[type="submit"]').show('');
                                                            is_goto_next = false;
                                                        }
                                                        else
                                                        {

                                                            if (jQuery(object).find("#recaptcha_style").length > 0 && jQuery(object).find("#recaptcha_style").is(":visible") == true) {
                                                                is_goto_next = false;
                                                                if (!jQuery.isFunction(checkRecaptcha)) {
                                                                    return;
                                                                }
                                                                checkRecaptcha(object, '<?php echo ARFSCRIPTURL ?>', next_id, max_id, break_val, 'no');

                                                            } else {
                                                                is_goto_next = true;
                                                            }

                                                        }
                                                    }
                                                    else
                                                    {

                                                        if (!jQuery.isFunction(arf_validate_file)) {
                                                            return;
                                                        }
                                                        if (!(arf_validate_file(event, form1, form_data_id)))
                                                        {
                                                            event.preventDefault();
                                                            is_goto_next = false;
                                                        }
                                                        else
                                                        {

                                                            if (jQuery(object).find("#recaptcha_style").length > 0 && jQuery(object).find("#recaptcha_style").is(":visible") == true) {
                                                                is_goto_next = false;
                                                                if (!jQuery.isFunction(checkRecaptcha)) {
                                                                    return;
                                                                }
                                                                checkRecaptcha(object, '<?php echo ARFSCRIPTURL ?>', next_id, max_id, break_val, 'no');

                                                            } else {
                                                                is_goto_next = true;
                                                            }

                                                        }
                                                    }

                                                    if (next_id == max_id && is_goto_next == true) {
                                                        jQuery(object).find('[data-id="submit_form_' + break_form_id + '"]').val('0');
                                                        if (!jQuery.isFunction(go_next)) {
                                                            return;
                                                        }
                                                        window.arf_is_submitting_form = false;
                                                        go_next(next_id, object);
                                                    } else if (is_goto_next == true) {
                                                        next_id_new = parseInt(next_id) + parseInt(1);
                                                        jQuery(object).find('[data-id="submit_form_' + break_form_id + '"]').attr('data-val', next_id_new);
                                                        if (!jQuery.isFunction(go_next)) {
                                                            return;
                                                        }
                                                        window.arf_is_submitting_form = false;
                                                        go_next(next_id, object);
                                                    }

                                                    if (is_goto_next == true) {
                                                        jQuery(object).find('div').removeClass('arf_error');
                                                        jQuery(object).find(".help-block").empty();
                                                        jQuery(object).find('.frm_error_style').hide();
                                                    }


                                                } else {

                                                    
                                                    var checkwhichsubmit = jQuery(object).find("input[name='form_submit_type']").attr('value');
                                                    var arf_form_id = jQuery(object).find("input[name='form_id']").attr('value');
                                                    if (checkwhichsubmit != 1)
                                                    {
                                                        var arf_is_prevalidate = jQuery(object).find("[data-id='arf_is_validate_outside_" + arf_form_id + "']").val();

                                                        var arf_is_prevalidate_form = jQuery(object).find("[data-id='arf_is_validate_outside_" + arf_form_id + "']").attr('data-validate');
                                                        if (arf_is_prevalidate == 1 && arf_is_prevalidate_form == 1)
                                                        {
                                                            arf_is_validateform_outside(jQuery(object), event);
                                                            event.preventDefault();
                                                            return;
                                                        }

                                                        jQuery(object).find("[data-id='arf_is_validate_outside_" + arf_form_id + "']").val(arf_is_prevalidate);
                                                        var arf_prevalidate = jQuery('#' + form1).find("[data-id='arf_validate_outside_" + arf_form_id + "']").val();

                                                        var arf_prevalidate_form = jQuery('#' + form1).find("[data-id='arf_validate_outside_" + arf_form_id + "']").attr('data-validate');
                                                        if (arf_prevalidate == 1 && arf_prevalidate_form == 1)
                                                        {
                                                            if (!jQuery.isFunction(arf_validate_form_outside)) {
                                                                return;
                                                            }
                                                            arf_validate_form_outside(jQuery(object), event);
                                                            event.preventDefault();
                                                            return;
                                                        }
                                                        jQuery(object).find("[data-id='arf_validate_outside_" + arf_form_id + "']").val(arf_prevalidate_form);
                                                    }

                                                    var is_submit_enable = jQuery(object).find('.arf_submit_btn:not(.arf_print_summary)').attr('disabled');
                                                    var is_captcha_success = window.is_captcha_success || false;

                                                    if ( !is_captcha_success && ( is_submit_enable == true || is_submit_enable == 'disabled')){
                                                        event.preventDefault();
                                                        return;
                                                    }

                                                    if (jQuery(object).find('[data-id="form_submit_type"]').val() == 1){

                                                        event.preventDefault();
                                                        var upload_flag = 0;
                                                        jQuery(object).find(".original,.arf_reply_drag_file").each(function (index) {
                                                            var fileToUpload = jQuery(this).attr('data-file-valid');
                                                            if (fileToUpload == 'false')
                                                            {
                                                                var fileId = jQuery(this).attr('id');
                                                                var file = document.getElementById(fileId);

                                                                if (jQuery('#' + fileId).attr('data-invalid-message') !== undefined && jQuery('#' + fileId).attr('data-invalid-message') != '') {
                                                                    var arf_invalid_file_message = jQuery('#' + fileId).attr('data-invalid-message');
                                                                } else {
                                                                    var arf_invalid_file_message = file_error;
                                                                }
                                                                var $this = jQuery('#' + fileId);
                                                                var $controlGroup = $this.parents(".control-group").first();
                                                                var $helpBlock = $controlGroup.find(".help-block").first();

                                                                var form_id = $this.closest('form').find('[data-id="form_id"]').val();
                                                                var error_type = (jQuery('[data-id="form_tooltip_error_' + form_id + '"]').val() == 'advance') ? 'advance' : 'normal';

                                                                window.arf_is_submitting_form = false;

                                                                if (error_type == 'advance')
                                                                {
                                                                    if (!jQuery.isFunction(arf_show_tooltip)) {
                                                                        return;
                                                                    }
                                                                    arf_show_tooltip($controlGroup, $helpBlock, arf_invalid_file_message);
                                                                } else {
                                                                    if (!$helpBlock.length) {
                                                                        $helpBlock = jQuery('<div class="help-block"><ul><li>' + arf_invalid_file_message + '</li></ul></div>');
                                                                        $controlGroup.find('.controls').append($helpBlock);
                                                                        $controlGroup.find('.controls .help-block').removeClass('arfanimated bounceInDownNor').addClass('arfanimated bounceInDownNor');
                                                                    }
                                                                    else
                                                                    {
                                                                        $helpBlock = jQuery('<ul role="alert"><li>' + arf_invalid_file_message + '</li></ul>');
                                                                        $controlGroup.find('.controls .help-block').empty();
                                                                        $controlGroup.find('.controls .help-block').append($helpBlock);
                                                                        $controlGroup.find('.controls .help-block').removeClass('arfanimated bounceInDownNor').addClass('arfanimated bounceInDownNor');
                                                                    }
                                                                }
                                                                upload_flag++;

                                                            }
                                                        });
                                                        
                                                        if (upload_flag > 0)
                                                        {

                                                            jQuery('#submit_loader').hide();
                                                            jQuery(object).find('input[type="submit"]').show('');
                                                            is_goto_next = false;
                                                        }
                                                        else
                                                        {

                                                            if (jQuery(object).find("#recaptcha_style").length > 0 && jQuery(object).find("#recaptcha_style").is(":visible") == true) {
                                                                is_goto_next = false;
                                                                if (!jQuery.isFunction(checkRecaptcha)) {
                                                                    return;
                                                                }
                                                                checkRecaptcha(object, '<?php echo ARFSCRIPTURL ?>', next_id, max_id, break_val, 'no');

                                                            } else {

                                                                if( jQuery(object).find('[data-arf-confirm]').length > 0 && document.getElementById('arf_submit_form_after_confirm_' + form_data_id).value != 'true' ){
                                                                    var display_summary = jQuery(object).find('[data-arf-confirm]').attr('data-arf-display-confirmation') || 'before';
                                                                    arf_do_action('arf_confirm_form_before_submit',form_data_id,display_summary);
                                                                    if( window.is_display_summary ){
                                                                        event.preventDefault();
                                                                        return false;
                                                                    }
                                                                }

                                                                jQuery(object).find('[data-id="previous_last"]').css('display', 'none');

                                                                var arf_bowser_name = jQuery(object).find('[data-id="arf_browser_name"]').val();
                                                                var arf_bowser_version = jQuery(object).find('[data-id="arf_browser_name"]').attr('data-version');

                                                                if ((arf_bowser_name == 'Opera' && arf_bowser_version <= 30)  || (arf_bowser_name == 'Internet Explorer' && arf_bowser_version <= 9)) {
                                                                    jQuery(object).find('.arf_submit_btn .arfstyle-label').hide();
                                                                    jQuery(object).find('.arf_submit_btn .arf_ie_image').css('display', 'inline-block');
                                                                } else {
                                                                   
                                                                }
                                                                jQuery(object).find('.arf_submit_btn:not(.arf_print_summary)').attr('disabled', true);
                                                                if (!jQuery.isFunction(arfformsubmission)) {
                                                                    return;
                                                                }
                                                                arfformsubmission(object, '<?php echo ARFSCRIPTURL ?>', 'yes');
                                                            }

                                                        }
                                                    }
                                                    else
                                                    {

                                                        if (!jQuery.isFunction(arf_validate_file)) {
                                                            return;
                                                        }
                                                        if (!(arf_validate_file(event, form1, form_data_id)))
                                                        {
                                                            event.preventDefault();
                                                            is_goto_next = false;
                                                        }
                                                        else
                                                        {

                                                            if (jQuery(object).find("#recaptcha_style").length > 0 && jQuery(object).find("#recaptcha_style").is(":visible") == true) {
                                                                is_goto_next = false;
                                                                event.preventDefault();
                                                                if (!jQuery.isFunction(checkRecaptcha)) {
                                                                    return;
                                                                }
                                                                checkRecaptcha(object, '<?php echo ARFSCRIPTURL ?>', next_id, max_id, break_val, 'no');

                                                            } else {

                                                                var is_submit_form = jQuery(object).find('[data-id="is_submit_form_' + break_form_id + '"]').val();

                                                                ARFClearCookieFormData(arf_form_id);

                                                                if (is_submit_form == 1) {

                                                                    if( jQuery(object).find('[data-arf-confirm]').length > 0 && document.getElementById('arf_submit_form_after_confirm_' + form_data_id).value != 'true' ){
                                                                        var display_summary = jQuery(object).find('[data-arf-confirm]').attr('data-arf-display-confirmation') || 'before';
                                                                        arf_do_action('arf_confirm_form_before_submit',form_data_id,display_summary);
                                                                        
                                                                        if( window.is_display_summary ){
                                                                            event.preventDefault();
                                                                            return false;
                                                                        }
                                                                    } else {
                                                                        jQuery(object).find('[data-id="is_submit_form_' + break_form_id + '"]').val('0');
                                                                    }

                                                                    if (jQuery(object).find('#recaptcha_style').length > 0) {
                                                                        jQuery(object).find('#recaptcha_style').html('  ');
                                                                    }


                                                                    jQuery(object).find('[data-id="previous_last"]').css('display', 'none');

                                                                    var arf_bowser_name = jQuery(object).find('[data-id="arf_browser_name"]').val();
                                                                    var arf_bowser_version = jQuery(object).find('[data-id="arf_browser_name"]').attr('data-version');

                                                                    if ((arf_bowser_name == 'Opera' && arf_bowser_version <= 30) || (arf_bowser_name == 'Internet Explorer' && arf_bowser_version <= 9)) {
                                                                        jQuery(object).find('.arf_submit_btn .arfstyle-label').hide();
                                                                        jQuery(object).find('.arf_submit_btn .arf_ie_image').css('display', 'inline-block');
                                                                    } else {
                                                                        
                                                                    }
                                                                    
                                                                    jQuery(object).find('.arf_submit_btn').addClass('arf_active_loader');
                                                                    jQuery(object).find('.arf_submit_btn:not(.arf_print_summary)').attr('disabled', true);
                                                                    var is_captcha_success = window.is_captcha_success || false;
                                                                    if( !is_captcha_success ){
                                                                        is_check_recaptcha(object,arf_form_id,event);
                                                                        event.preventDefault();
                                                                        return false;
                                                                    }
                                                                }

                                                            }

                                                        }
                                                    }
                                                }

                                            }
                                        });
                                    },
                                    submitError: function ($form, event) {
                                        window.arf_is_submitting_form = false;
                                        var form1 = jQuery($form).attr('data-form-id');
                                        
                                        var object = jQuery('.arfshowmainform[data-form-id="' + form1+'"]');

                                        var form_data_id = jQuery($form).attr('data-id');

                                        jQuery("form.arfshowmainform").each(function () {
                                            if (jQuery(this).attr('data-id') == form_data_id) {
                                                object = jQuery(this);
                                                
                                                if (jQuery('.arfmodal-body').parent(object).find('.arfformfield.arf_error').length > 0)
                                                {
                                                    var scrolltop = jQuery(jQuery('.arfmodal-body').parent(object).find('.arfformfield.arf_error').first()).offset().top;
                                                    jQuery(window.opera ? '.arfmodal-body' : '.arfmodal-body').animate({scrollTop: jQuery(jQuery('.arfmodal-body').parent(object).find('.arfformfield.arf_error').first()).offset().top - jQuery(jQuery('.arfmodal-body').parent(object).find('.arfformfield').first()).offset().top - 50}, 'slow');

                                                    var tmp_div_id = jQuery('.arfmodal-body').parent(object).find('.arfformfield.arf_error').first().attr('id');
                                                    var tmp_field_id = jQuery('.arfmodal-body').parent(object).find('.arfformfield.arf_error input, .arfformfield.arf_error select, .arfformfield.arf_error textarea').first().attr('id');
                                                    jQuery('.arfmodal-body').parent(object).find('.arfformfield.arf_error input, .arfformfield.arf_error select, .arfformfield.arf_error textarea').first().focus();
                                                    if (jQuery('#' + tmp_field_id).is('select')) {
                                                        jQuery('.arfmodal-body').parent(object).find('#' + tmp_div_id + ' .dropdown-toggle[data-toggle="arfdropdown"]').focus();
                                                    }

                                                    if (!jQuery.isFunction(revalidate_focus)) {
                                                        return;
                                                    }
                                                    revalidate_focus(tmp_field_id, tmp_div_id);
                                                }
                                                else if (jQuery(object).find('.arfformfield.arf_error').length > 0)
                                                {
                                                    jQuery(window.opera ? 'html, .arfmodal-body' : 'html, body, .arfmodal-body').animate({scrollTop: jQuery(jQuery(object).find('.arfformfield.arf_error').first()).offset().top - 100}, 'slow');

                                                    var tmp_div_id = jQuery(object).find('.arfformfield.arf_error').first().attr('id');
                                                    var tmp_field_id = jQuery(object).find('.arfformfield.arf_error input, .arfformfield.arf_error select, .arfformfield.arf_error textarea').first().attr('id');

                                                    jQuery(object).find('.arfformfield.arf_error input, .arfformfield.arf_error select, .arfformfield.arf_error textarea').first().focus();

                                                    if (jQuery('#' + tmp_field_id).is('select')) {
                                                        jQuery(object).find('#' + tmp_div_id + ' .dropdown-toggle[data-toggle="arfdropdown"]').focus();
                                                    }

                                                    if (!jQuery.isFunction(revalidate_focus)) {
                                                        return;
                                                    }
                                                    revalidate_focus(tmp_field_id, tmp_div_id);

                                                }

                                                var checkwhichsubmit = jQuery('#' + form1).find("input[name='form_submit_type']").attr('value');
                                                if (checkwhichsubmit != 1)
                                                {
                                                    if (!jQuery.isFunction(arf_validate_file)) {
                                                        return;
                                                    }
                                                    if (!(arf_validate_file(event, form1, form_data_id)))
                                                    {
                                                        event.preventDefault();
                                                    }
                                                }

                                            }
                                        });
                                    }

                                });
                            });


                        });


                <?php } ?>

                    var form1chk = "";
                    jQuery(document).ready(function ($) {
                <?php if ($preview == true) { ?>
    						jQuery('#brand-div').attr('style', 'margin-top:30px !important; font-size:12px !important; color: #444444 !important; display:block !important; visibility:visible !important;');
    										
    						jQuery('#brand-div a').attr('style', 'color:#0066cc !important; font-size:12px !important; display:inline !important; visibility:visible !important;');
    						
    						jQuery('#brand-div span').attr('style', 'color:#FF0000 !important; font-size:12px !important; display:inline !important; visibility:visible !important;');
                            jQuery('.original_normal').on('change', function(e) {
                                var id = jQuery(this).attr('id');
                                id = id.replace('field_', '');
                                jQuery('#file_name_' + id).html('');
                                var file_data = jQuery(this)[0].files;
                                for (var i = 0; i < file_data.length; i++) {
                                    var fileName = file_data[i].name;
                                    fileName = fileName.replace(/C:\\fakepath\\/i, '');
                                    if (fileName != '') {
                                        var old_file_name = jQuery('#file_name_' + id).html();
                                        if (old_file_name == 'No file selected') {
                                            old_file_name = '';
                                        }
                                        if (old_file_name != '') {
                                            jQuery('#file_name_' + id).html(old_file_name + ', ' + fileName);
                                        } else {
                                            jQuery('#file_name_' + id).html(fileName);
                                        }
                                    }
                                }
                            });
                            if( jQuery('.arf_materialize_form').length > 0 ){
                                Materialize.updateTextFields();
                                jQuery('.arf_materialize_form').find('select').material_select();
                            }

                            if( typeof selectpicker != 'undefined'){
                                jQuery('.arf_fieldset:not(.arf_materialize_form) .sltstandard_front select').selectpicker('refresh');
                            }
                            var formpreview1 = $('.arfshowmainform').not('.arfpagebreakform').attr('data-form-id');
                            $('.arfshowmainform').not('.arfpagebreakform').find("input,select,textarea").not("[type=submit],div[id='recaptcha_style'] input").jqBootstrapValidation({
                                submitSuccess: function ($form, event) {
                                    var checkwhichsubmit = jQuery('.arfshowmainform').find("input[name='form_submit_type']").attr('value');

                                    object = jQuery('.arfshowmainform');
                                    var arf_form_id = jQuery(object).find("input[name='form_id']").attr('value');


                                    var checkwhichsubmit = jQuery(object).find("input[name='form_submit_type']").attr('value');

                                    var break_form_id = jQuery(object).find('input[name="form_id"]').val();
                                    var form_data_id = jQuery(object).find('input[name="form_data_id"]').val();
                                    var next_id = 0;
                                    var max_id = 0;
                                    var break_val = 0;
                                    event.preventDefault();
                                    if (jQuery(object).find('[data-id="form_submit_type"]').val() == 1){

                                        if( jQuery(object).find('[data-arf-confirm]').length > 0 && document.getElementById('arf_submit_form_after_confirm_' + form_data_id).value != 'true' ){
                                            var display_summary = jQuery(object).find('[data-arf-confirm]').attr('data-arf-display-confirmation') || 'before';
                                            arf_do_action('arf_confirm_form_before_submit',form_data_id,display_summary);
                                            
                                            if( window.is_display_summary ){
                                                event.preventDefault();
                                                return false;
                                            }
                                        }

                                        event.preventDefault();
                                        var upload_flag = 0;
                                        jQuery(".original,.arf_reply_drag_file").each(function (index) {
                                            var fileToUpload = jQuery(this).attr('data-file-valid');
                                            if (fileToUpload == 'false'){
                                                var fileId = jQuery(this).attr('id');
                                                var file = document.getElementById(fileId);

                                                var keys = fileId.replace('field_','');

                                                var types = document.getElementById('file_types_'+keys).value;
                                                var size = document.getElementById('file_size_'+keys).value;

                                                var file_name = file.value;

                                                var counter = 1;
                                                var tmp_obj = document.querySelector('[data-info-id="info_'+keys+'_'+counter+'"]');
                                                if( tmp_obj == null ){
                                                    var cnt = jQuery(this).parents('.arf_field_type_file').find(".arf_file_info_item.arf_error").find('.ajax-file-remove').attr('data-info-id');
                                                    var counter = cnt.replace('info_'+keys+'_','');
                                                    var tmp_obj = document.querySelector('[data-info-id="info_'+keys+'_'+counter+'"]');
                                                }

                                                if( file_name == '' ){
                        
                                                    var file_name = jQuery(tmp_obj).parents('.arf_file_info_item').find('.file_name.arf_info').html();

                                                    file_name = file_name.replace(/,\s*$/, "");
                                                }

                                                var file_part = file_name.split('\\');

                                                var filenm = file_part[parseInt(file_part.length) - 1];

                                                var extension = filenm.split('.');

                                                var ext = extension[parseInt(extension.length) - 1];

                                                if( types.indexOf(ext) < 0 ){
                                                    if (jQuery('#' + fileId).attr('data-invalid-message') !== undefined && jQuery('#' + fileId).attr('data-invalid-message') != '') {
                                                        var arf_invalid_file_message = jQuery('#' + fileId).attr('data-invalid-message');
                                                    } else {
                                                        var arf_invalid_file_message = file_error;
                                                    }
                                                } else {
                                                    
                                                    var file_size = tmp_obj.getAttribute('data-current-file-size');
                                                    if( file_size > size ){
                                                        var arf_invalid_file_message = jQuery("#" + fileId).attr('data-size-invalid-message');
                                                    }
                                                    //imagename_127_73220
                                                }

                                                var $this = jQuery('#' + fileId);
                                                var $controlGroup = $this.parents(".control-group").first();
                                                var $helpBlock = $controlGroup.find(".help-block").first();

                                                var form_id = $this.closest('form').find('[data-id="form_id"]').val();
                                                var error_type = (jQuery('[data-id="form_tooltip_error_' + form_id + '"]').val() == 'advance') ? 'advance' : 'normal';

                                                window.arf_is_submitting_form = false;

                                                if (error_type == 'advance')
                                                {
                                                    if (!jQuery.isFunction(arf_show_tooltip)) {
                                                        return;
                                                    }
                                                    arf_show_tooltip($controlGroup, $helpBlock, arf_invalid_file_message);
                                                } else {
                                                    if (!$helpBlock.length) {
                                                        $helpBlock = jQuery('<div class="help-block"><ul><li>' + arf_invalid_file_message + '</li></ul></div>');
                                                        $controlGroup.find('.controls').append($helpBlock);
                                                        $controlGroup.find('.controls .help-block').removeClass('arfanimated bounceInDownNor').addClass('arfanimated bounceInDownNor');
                                                    }
                                                    else
                                                    {
                                                        $helpBlock = jQuery('<ul role="alert"><li>' + arf_invalid_file_message + '</li></ul>');
                                                        $controlGroup.find('.controls .help-block').empty();
                                                        $controlGroup.find('.controls .help-block').append($helpBlock);
                                                        $controlGroup.find('.controls .help-block').removeClass('arfanimated bounceInDownNor').addClass('arfanimated bounceInDownNor');
                                                    }
                                                }
                                                upload_flag++;

                                            }
                                        });

                                        if (upload_flag > 0)
                                        {
                                            jQuery(object).find('input[type="submit"]').show('');
                                            is_goto_next = false;
                                        }
                                        else
                                        {

                                            if (jQuery(object).find("#recaptcha_style").length > 0 && jQuery(object).find("#recaptcha_style").is(":visible") == true) {
                                                is_goto_next = false;
                                                if (!jQuery.isFunction(checkRecaptcha)) {
                                                    return;
                                                }
                                                checkRecaptcha(object, '<?php echo ARFSCRIPTURL ?>', next_id, max_id, break_val, 'yes');

                                            } else {
                                                jQuery(object).find('[data-id="previous_last"]').css('display', 'none');

                                                var arf_bowser_name = jQuery(object).find('[data-id="arf_browser_name"]').val();
                                                var arf_bowser_version = jQuery(object).find('[data-id="arf_browser_name"]').attr('data-version');

                                                if ((arf_bowser_name == 'Opera' && arf_bowser_version <= 30) || (arf_bowser_name == 'Internet Explorer' && arf_bowser_version <= 9)) {
                                                    jQuery(object).find('.arf_submit_btn .arfstyle-label').hide();
                                                    jQuery(object).find('.arf_submit_btn .arf_ie_image').css('display', 'inline-block');
                                                } else {
                                                    jQuery(object).find('.arf_submit_btn').addClass('arf_active_loader');
                                                }
                                                jQuery(object).find('.arf_submit_btn:not(.arf_print_summary)').attr('disabled', true);

                                                var start_time = new Date().getTime();

                                                setTimeout(function () {

                                                    var EndTime = new Date().getTime();
                                                    var totalTimeTaken = EndTime - start_time;
                                                    var timetoseconds = Math.ceil(totalTimeTaken/1000);
                                                    var stoms = timetoseconds * 1000;
                                                    var deduction = stoms - 140;
                                                    var sleepCounter = 860;

                                                    if( deduction < totalTimeTaken ){
                                                        sleepCounter = totalTimeTaken - deduction;
                                                    } else {
                                                        sleepCounter = deduction - totalTimeTaken;
                                                    }
                                                    var arf_bowser_name = jQuery(object).find('[data-id="arf_browser_name"]').val();
                                                    var arf_bowser_version = jQuery(object).find('[data-id="arf_browser_name"]').attr('data-version');
                                                    if ((arf_bowser_name == 'Opera' && arf_bowser_version <= 30) || (arf_bowser_name == 'Internet Explorer' && arf_bowser_version <= 9)) {
                                                        jQuery(object).find('.arf_submit_btn .arfstyle-label').show();
                                                        jQuery(object).find('.arf_submit_btn .arf_ie_image').hide();
                                                        jQuery(object).find('.arf_submit_btn:not(.arf_print_summary)').attr('disabled', false);
                                                    } else {
                                                       (function(obj,counter){
                                                            setTimeout(function(){
                                                                jQuery(obj).find('.arf_submit_btn').removeClass('arf_active_loader');
                                                                jQuery(obj).find('.arf_submit_btn').addClass('arf_complete_loader');

                                                                if( window.arf_set_value_of_applied["'"+form_data_id+"'"] !== undefined ){
                                                                    window.arf_set_value_of_applied["'"+form_data_id+"'"] = [];
                                                                }

                                                                window.arf_is_submitting_form = false;

                                                                setTimeout(function(){

                                                                    var arf_form_hide_after_submit = jQuery(obj).find('[data-id="arf_form_hide_after_submit_' + break_form_id + '"]').val();
                                                                    if( arf_form_hide_after_submit == '1'){
                                                                        jQuery(object).slideUp("slow");
                                                                    }

                                                                    jQuery('#form_success_' + break_form_id).slideDown();
                                                                    jQuery('html, body').animate({scrollTop: jQuery('#message_success')}, 'slow');
                                                                    arf_success_message_show_time = jQuery(obj).find('[data-id="arf_success_message_show_time_' + break_form_id + '"]').val();

                                                                    if (!arf_success_message_show_time > 0) {
                                                                        arf_success_message_show_time = 3;
                                                                    }
                                                                    
                                                                    if (arf_success_message_show_time != 0) {
                                                                        arf_success_message_show_time = arf_success_message_show_time * 1000;
                                                                        setTimeout(function() {
                                                                            jQuery('html, body').find('#arf_message_success').parent().slideUp("slow");
                                                                        }, arf_success_message_show_time);
                                                                    }

                                                                    jQuery(obj).find('.arf_confirmation_summary_wrapper input[id^="arf_submit_form_after_confirm_"]').val('false');

                                                                    var display = jQuery(obj).find('.arf_confirmation_summary_wrapper').attr('data-confirmation-display');
                                                                    if( jQuery(obj).find('.arf_confirmation_summary_wrapper').length > 0 && display != 'after' ){
                                                                        jQuery(obj).find('.arf_confirmation_summary_wrapper').slideUp('slow');
                                                                        jQuery(obj).find('.arf_fieldset').slideDown('slow');
                                                                    } else {
                                                                    }

                                                                    jQuery(obj).find('.arf_submit_btn').removeClass('arf_complete_loader');
                                                                    jQuery(obj).find('.arf_submit_btn').attr('disabled', false);
                                                                },1000);
                                                                setTimeout(function(){
                                                                    var display = jQuery(object).find('.arf_confirmation_summary_wrapper').attr('data-confirmation-display');
                                                                    if( display == 'after' ){
                                                                        jQuery(object).find('.arf_fieldset').slideUp('slow');
                                                                        jQuery(object).find('.arf_confirmation_summary_wrapper').slideDown('slow');
                                                                    }
                                                                },500);
                                                            },counter);
                                                        })(object,sleepCounter);
                                                    }


                                                    
                                                    jQuery('#form_<?php echo $form->form_key; ?>').show();
                                                    jQuery(object).find('input[type="submit"]').removeAttr('style');
                                                    jQuery(object).find('div').removeClass('arfblankfield');
                                                    jQuery(".help-block").empty();
                                                    jQuery('#hexagon').css('display', 'none');
                                                    jQuery(object).find('.arf_file_field').show();  
                                                    
                                                    var captcha_key = jQuery(object).find('input[name="field_captcha"]').attr('value');

                                                    reset_checkbox_radio_field(object);
                                                    reset_like_field(object);
                                                    reset_slider_field(object);
                                                    reset_running_total(object);
                                                    reset_colorpicker(object);

                                                    reset_datetimepicker(object);
                                                    reset_selectpicker(object);

                                                    
                                                    if(captcha_key !='' && captcha_key !=undefined && captcha_key != null){
                                                        reloadcapcha(object, captcha_key);
                                                    }
                                                    
                                                    var is_formreset = jQuery(object).find('input[name="arf_is_resetform_aftersubmit_' + break_form_id + '"]').val();

                                                    if (is_formreset == 1)
                                                    {

                                                        jQuery("form[data-form-id='form_<?php echo $form->form_key; ?>']").trigger("reset");

                                                        jQuery(object).find('textarea.arf_text_is_countable').each(function(i) {
                                                            jQuery(this).trigger('keyup');
                                                        });

                                                        setTimeout(function(){
                                                            jQuery(object).find('input[type="text"],input[type="email"],input[type="password"],input[type="phone"],input[type="tel"],select').trigger('keyup').trigger('onchange');
                                                        },1000);

                                                        jQuery(object).find('.arf_multi_file_info_container').html('');
                                                        jQuery(object).find('.arfprogress, .arf_info, .arf_multi_file_info_container').hide();

                                                        var is_material = jQuery(object).find('.arf_fieldset').hasClass('arf_materialize_form') || false;
                                                        if (jQuery.isFunction(jQuery().selectpicker) && !is_material) {
                                                            object.find('select').selectpicker('render');
                                                        }

                                                        if (!jQuery.isFunction(reset_like_field) || !jQuery.isFunction(reset_slider_field) || !jQuery.isFunction(reset_running_total) || !jQuery.isFunction(reset_colorpicker) || !jQuery.isFunction(reset_datetimepicker) || !jQuery.isFunction(reset_selectpicker) ) {
                                                            return;
                                                        }
                                                        reset_checkbox_radio_field(object);
                                                        reset_like_field(object);
                                                        reset_slider_field(object);
                                                        reset_running_total(object);
                                                        reset_colorpicker(object);
                                                        reset_datetimepicker(object);
                                                        reset_selectpicker(object);

                                                        if (typeof reset_preview_out_side == 'function') {
                                                            reset_preview_out_side('<?php echo json_encode(array('id' => $form->id, 'form_key' => $form->form_key)); ?>', object);
                                                        }

                                                        var result_data = JSON.stringify({"script":null,"conf_method":"message","message":"<div class=\"arf_form ar_main_div_187\" id=\"arffrm_187_container\"><div id=\"arf_message_success\"><div class=\"msg-detail\"><div class=\"msg-description-success\">Form is successfully submitted. Thank you!<\/div><\/div><\/div><\/div>"});

                                                        arf_do_action('reset_field_in_outsite',object,result_data);

                                                        if (typeof (__ARFSTRRNTH_INDICATOR) != 'undefined') {
                                                            var strenth_indicator = __ARFSTRRNTH_INDICATOR;
                                                        } else {
                                                            var strenth_indicator = 'Strength indicator';
                                                        }
                                                        jQuery(object).find('.arf_strenth_meter').removeClass('short bad good strong');
                                                        jQuery(object).find('.arf_strenth_mtr .inside_title').html(strenth_indicator);

                                                        jQuery(object).find('input[type="checkbox"], input[type="radio"]').not('.arf_hide_opacity').each(function (i) {
                                                            jQuery(this).attr("checked", jQuery(this).is(':checked'));
                                                            if (jQuery(this).is(':checked')) {
                                                                jQuery(this).parent('div').addClass('checked');
                                                            } else {
                                                                jQuery(this).parent('div').removeClass('checked');
                                                            }
                                                        });
                                                    }

                                                    if (!jQuery.isFunction(arf_reset_page_nav)) {
                                                        return;
                                                    }
                                                    arf_reset_page_nav();
                                                    if (typeof arf_rule_apply_bulk == 'function' && typeof window['arf_conditional_logic'] != 'undefined') {
                                                        arf_rule_apply_bulk('<?php echo $form->form_key; ?>', form_data_id);
                                                    }


                                                    var is_formreset_outside = jQuery(object).find('input[name="arf_is_resetform_outside_' + break_form_id + '"]').val();
                                                    if (is_formreset_outside == 1)
                                                    {
                                                        if (!jQuery.isFunction(arf_resetform_outside)) {
                                                            return;
                                                        }
                                                        arf_resetform_outside(object, break_form_id);
                                                    }

                                                    var arf_data_validate = jQuery(object).find("[data-id='arf_validate_outside_" + break_form_id + "']").attr('data-validate');
                                                    jQuery(object).find("[data-id='arf_validate_outside_" + break_form_id + "']").val(arf_data_validate);

                                                    var arf_bowser_name = jQuery(object).find('[data-id="arf_browser_name"]').val();
                                                    var arf_bowser_version = jQuery(object).find('[data-id="arf_browser_name"]').attr('data-version');

                                                    if( typeof Materialize != 'undefined' ){
                                                        Materialize.updateTextFields();
                                                    }

                                                }, 3000);

                                            }

                                        }
                                    }
                                    else
                                    {
                                        if (!jQuery.isFunction(arf_validate_file)) {
                                            return;
                                        }
                                        if (!(arf_validate_file(event, formpreview1, form_data_id)))
                                        {
                                            event.preventDefault();
                                            is_goto_next = false;
                                        }
                                        else
                                        {

                                            if (jQuery(object).find("#recaptcha_style").length > 0 && jQuery(object).find("#recaptcha_style").is(":visible") == true) {
                                                is_goto_next = false;
                                                event.preventDefault();
                                                if (!jQuery.isFunction(checkRecaptcha)) {
                                                    return;
                                                }
                                                checkRecaptcha(object, '<?php echo ARFSCRIPTURL ?>', next_id, max_id, break_val, 'yes');

                                            } else {
                                                var is_submit_form = jQuery('[data-id="is_submit_form_' + break_form_id + '"]').val();
                                                var validate_captcha = is_check_recaptcha(object,form_id,event);
                                                if (is_submit_form == 1 && validate_captcha ) {


                                                    if( jQuery(object).find('[data-arf-confirm]').length > 0 && document.getElementById('arf_submit_form_after_confirm_' + form_data_id).value != 'true' ){
                                                        var display_summary = jQuery(object).find('[data-arf-confirm]').attr('data-arf-display-confirmation') || 'before';
                                                        arf_do_action('arf_confirm_form_before_submit',form_data_id,display_summary);
                                                        
                                                        if( window.is_display_summary ){
                                                            event.preventDefault();
                                                            return false;
                                                        }
                                                    }

                                                    jQuery('[data-id="is_submit_form_' + break_form_id + '"]').val('0');
                                                    jQuery(object).find('.arf_submit_btn:not(.arf_print_summary)').attr('disabled', true);

                                                    jQuery(object).find('[data-id="previous_last"]').css('display', 'none');

                                                    var arf_bowser_name = jQuery(object).find('[data-id="arf_browser_name"]').val();
                                                    var arf_bowser_version = jQuery(object).find('[data-id="arf_browser_name"]').attr('data-version');
                                                    
                                                    var start_time = new Date().getTime();
                                                    if ((arf_bowser_name == 'Opera' && arf_bowser_version <= 30) || (arf_bowser_name == 'Internet Explorer' && arf_bowser_version <= 9)) {
                                                        jQuery(object).find('.arf_submit_btn .arfstyle-label').hide();
                                                        jQuery(object).find('.arf_submit_btn .arf_ie_image').css('display', 'inline-block');
                                                    } else {
                                                        jQuery(object).find('.arf_submit_btn').addClass('arf_active_loader');
                                                    }
                                                    setTimeout(function () {
                                                        jQuery('#form_<?php echo $form->form_key; ?>').show();
                                                        jQuery(object).find('input[type="submit"]').removeAttr('style');
                                                        jQuery(object).find('div').removeClass('arfblankfield');
                                                        jQuery(".help-block").empty();
                                                        jQuery('#hexagon').css('display', 'none');
                                                        jQuery(object).find('.arf_file_field').show();


                                                        var captcha_key = jQuery(object).find('input[name="field_captcha"]').attr('value');

                                                        if (!jQuery.isFunction(reloadcapcha)) {
                                                            return;
                                                        }
                                                        if(captcha_key !='' && captcha_key !=undefined && captcha_key != null){
                                                            reloadcapcha(object, captcha_key);
                                                        }                                                    

                                                        jQuery(object).find('.arf_submit_btn:not(.arf_print_summary)').attr('disabled', false);

                                                        var is_formreset = jQuery(object).find('input[name="arf_is_resetform_aftersubmit_' + break_form_id + '"]').val();
                                                        if (is_formreset == 1)
                                                        {
                                                            jQuery("form[data-form-id='form_<?php echo $form->form_key; ?>']").trigger("reset");
                                                            jQuery(object).find('.arfprogress, .arf_info').hide();
                                                            var is_material = jQuery(object).find('.arf_fieldset').hasClass('arf_materialize_form') || false;
                                                            if (jQuery.isFunction(jQuery().selectpicker) && !is_material ) {
                                                                object.find('select').selectpicker('render');
                                                            }

                                                            jQuery('textarea.arf_text_is_countable').each(function(i) {
                                                                jQuery(this).trigger('keyup');
                                                            });

                                                            if (!jQuery.isFunction(reset_like_field) || !jQuery.isFunction(reset_slider_field) || !jQuery.isFunction(reset_running_total) || !jQuery.isFunction(reset_colorpicker) || !jQuery.isFunction(reset_datetimepicker) || !jQuery.isFunction(reset_selectpicker)) {
                                                                return;
                                                            }
                                                            reset_checkbox_radio_field(object);
                                                            reset_like_field(object);
                                                            reset_slider_field(object);
                                                            reset_running_total(object);
                                                            reset_colorpicker(object);
                                                            reset_datetimepicker(object);
                                                            reset_selectpicker(object);

                                                            if (typeof reset_preview_out_side == 'function') {
                                                                reset_preview_out_side('<?php echo json_encode(array('id' => $form->id, 'form_key' => $form->form_key)); ?>', object);
                                                            }

                                                            var result_data = JSON.stringify({"script":null,"conf_method":"message","message":"<div class=\"arf_form ar_main_div_187\" id=\"arffrm_187_container\"><div id=\"arf_message_success\"><div class=\"msg-detail\"><div class=\"msg-description-success\">Form is successfully submitted. Thank you!<\/div><\/div><\/div><\/div>"});

                                                            arf_do_action('reset_field_in_outsite',object,result_data);

                                                            if (typeof (__ARFSTRRNTH_INDICATOR) != 'undefined') {
                                                                var strenth_indicator = __ARFSTRRNTH_INDICATOR;
                                                            } else {
                                                                var strenth_indicator = 'Strength indicator';
                                                            }
                                                            jQuery(object).find('.arf_strenth_meter').removeClass('short bad good strong');
                                                            jQuery(object).find('.arf_strenth_mtr .inside_title').html(strenth_indicator);

                                                            jQuery(object).find('input[type="checkbox"], input[type="radio"]').not('.arf_hide_opacity').each(function (i) {
                                                                jQuery(this).attr("checked", jQuery(this).is(':checked'));
                                                                if (jQuery(this).is(':checked')) {
                                                                    jQuery(this).parent('div').addClass('checked');
                                                                } else {
                                                                    jQuery(this).parent('div').removeClass('checked');
                                                                }
                                                            });
                                                            jQuery(object).find('.original_normal').each(function () {
                                                                var field_key = jQuery(this).attr('id');
                                                                field_key = field_key.replace('field_', '');
                                                                jQuery('#file_name_' + field_key).text('<?php echo addslashes(esc_html__('No file selected', 'ARForms')); ?>');
                                                            });
                                                        }

                                                        jQuery('[data-id="is_submit_form_' + break_form_id + '"]').val('1');
                                                        if (!jQuery.isFunction(arf_reset_page_nav)) {
                                                            return;
                                                        }
                                                        arf_reset_page_nav();

                                                        if (typeof arf_rule_apply_bulk == 'function' && typeof window['arf_conditional_logic'] != 'undefined' ) {
                                                            arf_rule_apply_bulk('<?php echo $form->form_key; ?>', form_data_id);
                                                        }

                                                        var is_formreset_outside = jQuery(object).find('input[name="arf_is_resetform_outside_' + break_form_id + '"]').val();
                                                        if (is_formreset_outside == 1)
                                                        {
                                                            if (!jQuery.isFunction(arf_resetform_outside)) {
                                                                return;
                                                            }
                                                            arf_resetform_outside(object, break_form_id);
                                                        }

                                                        var arf_data_validate = jQuery(object).find("[data-id='arf_validate_outside_" + break_form_id + "']").attr('data-validate');
                                                        jQuery(object).find("[data-id='arf_validate_outside_" + break_form_id + "']").val(arf_data_validate);

                                                        var arf_bowser_name = jQuery(object).find('[data-id="arf_browser_name"]').val();
                                                        var arf_bowser_version = jQuery(object).find('[data-id="arf_browser_name"]').attr('data-version');

                                                        var EndTime = new Date().getTime();
                                                        var totalTimeTaken = EndTime - start_time;
                                                        var timetoseconds = Math.ceil(totalTimeTaken/1000);
                                                        var stoms = timetoseconds * 1000;
                                                        var deduction = stoms - 140;
                                                        var sleepCounter = 860;

                                                        if( deduction < totalTimeTaken ){
                                                            sleepCounter = totalTimeTaken - deduction;
                                                        } else {
                                                            sleepCounter = deduction - totalTimeTaken;
                                                        }

                                                        
                                                        if ((arf_bowser_name == 'Opera' && arf_bowser_version <= 30) || (arf_bowser_name == 'Internet Explorer' && arf_bowser_version <= 9)) {
                                                            jQuery(object).find('.arf_submit_btn .arf_ie_image').hide();
                                                            jQuery(object).find('.arf_submit_btn .arfstyle-label').show();
                                                            jQuery(object).find('.arf_submit_btn:not(.arf_print_summary)').attr('disabled', false);
                                                        } else {
                                                            (function(obj,counter){
                                                                setTimeout(function(){
                                                                    jQuery(obj).find('.arf_submit_btn').removeClass('arf_active_loader');
                                                                    jQuery(obj).find('.arf_submit_btn').addClass('arf_complete_loader');

                                                                    if( window.arf_set_value_of_applied["'"+form_data_id+"'"] !== undefined ){
                                                                        window.arf_set_value_of_applied["'"+form_data_id+"'"] = [];
                                                                    }
                                                                    window.arf_is_submitting_form = false;

                                                                    setTimeout(function(){

                                                                        var arf_form_hide_after_submit = jQuery(obj).find('[data-id="arf_form_hide_after_submit_' + break_form_id + '"]').val();
                                                                        if( arf_form_hide_after_submit == '1'){
                                                                            jQuery(object).slideUp("slow");
                                                                        }

                                                                        jQuery('#form_success_' + break_form_id).slideDown();
                                                                        jQuery('html, body').animate({scrollTop: jQuery('#message_success')}, 'slow');

                                                                        arf_success_message_show_time = jQuery(obj).find('[data-id="arf_success_message_show_time_' + break_form_id + '"]').val();
                                                                        if (!arf_success_message_show_time > 0){
                                                                            arf_success_message_show_time = 3;
                                                                        }

                                                                        if (arf_success_message_show_time != 0){
                                                                            arf_success_message_show_time = arf_success_message_show_time * 1000;

                                                                            setTimeout(function () {
                                                                                jQuery('#form_success_' + break_form_id).slideUp("slow");
                                                                            }, arf_success_message_show_time);

                                                                        }

                                                                        jQuery(obj).find('.arf_confirmation_summary_wrapper input[id^="arf_submit_form_after_confirm_"]').val('false');

                                                                        var display = jQuery(obj).find('.arf_confirmation_summary_wrapper').attr('data-confirmation-display');
                                                                        if( jQuery(obj).find('.arf_confirmation_summary_wrapper').length > 0 && display != 'after' ){
                                                                            jQuery(obj).find('.arf_confirmation_summary_wrapper').slideUp('slow');
                                                                            jQuery(obj).find('.arf_fieldset').slideDown('slow');
                                                                        }else {
                                                                        }

                                                                        jQuery(obj).find('.arf_submit_btn').removeClass('arf_complete_loader');
                                                                        jQuery(obj).find('.arf_submit_btn').attr('disabled', false);
                                                                    },1000);
                                                                    setTimeout(function(){
                                                                        var display = jQuery(object).find('.arf_confirmation_summary_wrapper').attr('data-confirmation-display');
                                                                        if( display == 'after' ){
                                                                            jQuery(object).find('.arf_fieldset').slideUp('slow');
                                                                            jQuery(object).find('.arf_confirmation_summary_wrapper').slideDown('slow');
                                                                        }
                                                                    },500);
                                                                },counter);
                                                            })(object,sleepCounter);
                                                        }
                                                        
                                                        
                                                        if( typeof Materialize != 'undefined' ){
                                                            Materialize.updateTextFields();
                                                        }
                                                    }, 3000);

                                                }

                                            }

                                        }
                                    }

                                },
                                submitError: function ($form, event) {
                                    window.arf_is_submitting_form = false;
                                    object = jQuery('.arfshowmainform');
                                    if (jQuery(object).find('.arfformfield.arf_error').length > 0) {
                                        jQuery('html, body').animate({scrollTop: jQuery(jQuery(object).find('.arfformfield.arf_error').first()).offset().top - 100}, 'slow');
                                    }
                                    var tmp_div_id = jQuery(object).find('.arfformfield.arf_error').first().attr('id');
                                    var tmp_field_id = jQuery(object).find('.arfformfield.arf_error input, .arfformfield.arf_error select, .arfformfield.arf_error textarea').first().attr('id');

                                    if (!jQuery.isFunction(revalidate_focus)) {
                                        return;
                                    }
                                    revalidate_focus(tmp_field_id, tmp_div_id);

                                    event.preventDefault();
                                }
                            });

                            var formpreview = $('.arfpagebreakform').attr('data-form-id');
                            var formpreviewid = $('.arfpagebreakform[data-form-id="' + formpreview+'"]');
                            $('.arfpagebreakform').find("input,select,textarea").not("[type=submit],div[id='recaptcha_style'] input").jqBootstrapValidation({
                                submitSuccess: function ($form, event) {

                                    event.preventDefault();

                                    object = jQuery('.arfpagebreakform');

                                    var arf_form_id = jQuery(object).find("input[name='form_id']").attr('value');
                                    var form_data_id = jQuery(object).find("input[name='form_data_id']").attr('value');

                                    var break_form_id = jQuery(object).find('input[name="form_id"]').val();
                                    var break_val = jQuery('[data-id="submit_form_' + break_form_id + '"]').val();
                                    var next_id = jQuery('[data-id="submit_form_' + break_form_id + '"]').attr('data-val');
                                    var max_id = jQuery('[data-id="submit_form_' + break_form_id + '"]').attr('data-max');

                                    if (break_val == 1) {

                                        event.preventDefault();

                                        var is_goto_next = false;
                                        if (jQuery(object).find('[data-id="form_submit_type"]').val() == 1)
                                        {
                                            var upload_flag = 0;
                                            jQuery(".original,.arf_reply_drag_file").each(function (index) {
                                                var fileToUpload = jQuery(this).attr('data-file-valid');
                                                if (jQuery(this).is(':visible') && fileToUpload == 'false')
                                                {
                                                    var fileId = jQuery(this).attr('id');
                                                    var file = document.getElementById(fileId);

                                                    if (jQuery('#' + fileId).attr('data-invalid-message') !== undefined && jQuery('#' + fileId).attr('data-invalid-message') != '') {
                                                        var arf_invalid_file_message = jQuery('#' + fileId).attr('data-invalid-message');
                                                    } else {
                                                        var arf_invalid_file_message = file_error;
                                                    }
                                                    var $this = jQuery('#' + fileId);
                                                    var $controlGroup = $this.parents(".control-group").first();
                                                    var $helpBlock = $controlGroup.find(".help-block").first();

                                                    var form_id = $this.closest('form').find('[data-id="form_id"]').val();
                                                    var error_type = (jQuery('[data-id="form_tooltip_error_' + form_id + '"]').val() == 'advance') ? 'advance' : 'normal';

                                                    window.arf_is_submitting_form = false;

                                                    if (error_type == 'advance')
                                                    {
                                                        if (!jQuery.isFunction(arf_show_tooltip)) {
                                                            return;
                                                        }
                                                        arf_show_tooltip($controlGroup, $helpBlock, arf_invalid_file_message);
                                                    } else {
                                                        if (!$helpBlock.length) {
                                                            $helpBlock = jQuery('<div class="help-block"><ul><li>' + arf_invalid_file_message + '</li></ul></div>');
                                                            $controlGroup.find('.controls').append($helpBlock);
                                                            $controlGroup.find('.controls .help-block').removeClass('arfanimated bounceInDownNor').addClass('arfanimated bounceInDownNor');
                                                        }
                                                        else
                                                        {
                                                            $helpBlock = jQuery('<ul role="alert"><li>' + arf_invalid_file_message + '</li></ul>');
                                                            $controlGroup.find('.controls .help-block').empty();
                                                            $controlGroup.find('.controls .help-block').append($helpBlock);
                                                            $controlGroup.find('.controls .help-block').removeClass('arfanimated bounceInDownNor').addClass('arfanimated bounceInDownNor');
                                                        }
                                                    }
                                                    upload_flag++;

                                                }
                                            });
                                            if (upload_flag > 0)
                                            {
                                                jQuery(object).find('input[type="submit"]').show('');
                                                is_goto_next = false;
                                            }
                                            else
                                            {

                                                if (jQuery(object).find("#recaptcha_style").length > 0 && jQuery(object).find("#recaptcha_style").is(":visible") == true) {
                                                    is_goto_next = false;
                                                    if (!jQuery.isFunction(checkRecaptcha)) {
                                                        return;
                                                    }
                                                    checkRecaptcha(object, '<?php echo ARFSCRIPTURL ?>', next_id, max_id, break_val, 'yes');

                                                } else {
                                                    is_goto_next = true;
                                                }

                                            }
                                        }
                                        else
                                        {

                                            if (!jQuery.isFunction(arf_validate_file)) {
                                                return;
                                            }
                                            if (!(arf_validate_file(event, formpreview, form_data_id)))
                                            {
                                                event.preventDefault();
                                                is_goto_next = false;
                                            }
                                            else
                                            {

                                                if (jQuery(object).find("#recaptcha_style").length > 0 && jQuery(object).find("#recaptcha_style").is(":visible") == true) {
                                                    is_goto_next = false;
                                                    if (!jQuery.isFunction(checkRecaptcha)) {
                                                        return;
                                                    }
                                                    checkRecaptcha(object, '<?php echo ARFSCRIPTURL ?>', next_id, max_id, break_val, 'yes');

                                                } else {
                                                    is_goto_next = true;
                                                }

                                            }
                                        }

                                        if (next_id == max_id && is_goto_next == true) {
                                            jQuery('[data-id="submit_form_' + break_form_id + '"]').val('0');
                                            if (!jQuery.isFunction(go_next)) {
                                                return;
                                            }
                                            window.arf_is_submitting_form = false;
                                            go_next(next_id, object);
                                        } else if (is_goto_next == true) {
                                            next_id_new = parseInt(next_id) + parseInt(1);
                                            jQuery('[data-id="submit_form_' + break_form_id + '"]').attr('data-val', next_id_new);
                                            if (!jQuery.isFunction(go_next)) {
                                                return;
                                            }
                                            window.arf_is_submitting_form = false;
                                            go_next(next_id, object);
                                        }

                                        if (is_goto_next == true) {
                                            jQuery(object).find('div').removeClass('error');
                                            jQuery(object).find(".help-block").empty();
                                            jQuery(object).find('.frm_error_style').hide();
                                        }


                                    } else {


                                        var arf_is_prevalidate = jQuery(object).find("[data-id='arf_is_validate_outside_" + arf_form_id + "']").attr('value');
                                        var arf_is_prevalidate_form = jQuery(object).find("[data-id='arf_is_validate_outside_" + arf_form_id + "']").attr('data-validate');

                                        var checkwhichsubmit = jQuery(object).find("input[name='form_submit_type']").attr('value');

                                        if( jQuery(object).find('[data-arf-confirm]').length > 0 && document.getElementById('arf_submit_form_after_confirm_' + form_data_id).value != 'true' ){
                                            var display_summary = jQuery(object).find('[data-arf-confirm]').attr('data-arf-display-confirmation') || 'before';
                                            arf_do_action('arf_confirm_form_before_submit',form_data_id,display_summary);
                                            
                                            if( window.is_display_summary ){
                                                event.preventDefault();
                                                return false;
                                            }
                                        }

                                        var is_submit_enable = jQuery(object).find('.arf_submit_btn:not(.arf_print_summary)').attr('disabled');
                                        if (is_submit_enable == true || is_submit_enable == 'disabled')
                                        {
                                            event.preventDefault();
                                            return;
                                        }

                                        if (jQuery(object).find('[data-id="form_submit_type"]').val() == 1)
                                        {
                                            event.preventDefault();
                                            var upload_flag = 0;
                                            jQuery(".original,.arf_reply_drag_file").each(function (index) {
                                                var fileToUpload = jQuery(this).attr('data-file-valid');
                                                if (fileToUpload == 'false')
                                                {
                                                    var fileId = jQuery(this).attr('id');
                                                    var file = document.getElementById(fileId);

                                                    if (jQuery('#' + fileId).attr('data-invalid-message') !== undefined && jQuery('#' + fileId).attr('data-invalid-message') != '') {
                                                        var arf_invalid_file_message = jQuery('#' + fileId).attr('data-invalid-message');
                                                    } else {
                                                        var arf_invalid_file_message = file_error;
                                                    }
                                                    var $this = jQuery('#' + fileId);
                                                    var $controlGroup = $this.parents(".control-group").first();
                                                    var $helpBlock = $controlGroup.find(".help-block").first();

                                                    var form_id = $this.closest('form').find('[data-id="form_id"]').val();
                                                    var error_type = (jQuery('[data-id="form_tooltip_error_' + form_id + '"]').val() == 'advance') ? 'advance' : 'normal';

                                                    window.arf_is_submitting_form = false;

                                                    if (error_type == 'advance')
                                                    {
                                                        if (!jQuery.isFunction(arf_show_tooltip)) {
                                                            return;
                                                        }
                                                        arf_show_tooltip($controlGroup, $helpBlock, arf_invalid_file_message);
                                                    } else {
                                                        if (!$helpBlock.length) {
                                                            $helpBlock = jQuery('<div class="help-block"><ul><li>' + arf_invalid_file_message + '</li></ul></div>');
                                                            $controlGroup.find('.controls').append($helpBlock);
                                                            $controlGroup.find('.controls .help-block').removeClass('arfanimated bounceInDownNor').addClass('arfanimated bounceInDownNor');
                                                        }
                                                        else
                                                        {
                                                            $helpBlock = jQuery('<ul role="alert"><li>' + arf_invalid_file_message + '</li></ul>');
                                                            $controlGroup.find('.controls .help-block').empty();
                                                            $controlGroup.find('.controls .help-block').append($helpBlock);
                                                            $controlGroup.find('.controls .help-block').removeClass('arfanimated bounceInDownNor').addClass('arfanimated bounceInDownNor');
                                                        }
                                                    }
                                                    upload_flag++;

                                                }
                                            });
                                            if (upload_flag > 0)
                                            {
                                                jQuery(object).find('input[type="submit"]').show('');
                                                is_goto_next = false;
                                            }
                                            else
                                            {

                                                if (jQuery(object).find("#recaptcha_style").length > 0 && jQuery(object).find("#recaptcha_style").is(":visible") == true) {
                                                    is_goto_next = false;
                                                    if (!jQuery.isFunction(checkRecaptcha)) {
                                                        return;
                                                    }
                                                    checkRecaptcha(object, '<?php echo ARFSCRIPTURL ?>', next_id, max_id, break_val, 'yes');

                                                } else {
                                                    jQuery(object).find('[data-id="previous_last"]').css('display', 'none');

                                                    var arf_bowser_name = jQuery(object).find('[data-id="arf_browser_name"]').val();
                                                    var arf_bowser_version = jQuery(object).find('[data-id="arf_browser_name"]').attr('data-version');


                                                    var start_time = new Date().getTime();

                                                    if ((arf_bowser_name == 'Opera' && arf_bowser_version <= 30) || (arf_bowser_name == 'Internet Explorer' && arf_bowser_version <= 9)) {
                                                        jQuery(object).find('.arf_submit_btn .arfstyle-label').hide();
                                                        jQuery(object).find('.arf_submit_btn .arf_ie_image').css('display', 'inline-block');
                                                    } else {
                                                        jQuery(object).find('.arf_submit_btn').addClass('arf_active_loader');
                                                    }

                                                    jQuery(object).find('.arf_submit_btn:not(.arf_print_summary)').attr('disabled', true);

                                                    setTimeout(function () {

                                                        jQuery('#form_success_' + break_form_id).slideDown();
                                                        jQuery('html, body').animate({scrollTop: jQuery('#message_success')}, 'slow');
                                                        jQuery('#form_<?php echo $form->form_key; ?>').show();
                                                        jQuery(object).find('input[type="submit"]').removeAttr('style');
                                                        jQuery(object).find('div').removeClass('arfblankfield');
                                                        jQuery(".help-block").empty();
                                                        jQuery(object).find('.arf_file_field').show();
                                                        jQuery(object).find('[data-id="previous_last"]').show('');
                                                        jQuery(object).find('.arf_submit_btn:not(.arf_print_summary)').attr('disabled', false);
                                                        if (!jQuery.isFunction(go_previous)) {
                                                            return;
                                                        }
                                                        go_previous('0', break_form_id, 'yes', '<?php echo $form->form_key; ?>', '0');
                                                        if (!jQuery.isFunction(arf_reset_page_nav)) {
                                                            return;
                                                        }
                                                        arf_reset_page_nav();

                                                        var is_formreset = jQuery(object).find('input[name="arf_is_resetform_aftersubmit_' + break_form_id + '"]').val();
                                                        if (is_formreset == 1)
                                                        {
                                                            jQuery("form[data-form-id='form_<?php echo $form->form_key; ?>']").trigger("reset");
                                                            jQuery(object).find('.arfprogress, .arf_info').hide();
                                                            jQuery(object).find('input[type="checkbox"], input[type="radio"]').not('.arf_hide_opacity').each(function (i) {
                                                                jQuery(this).attr("checked", jQuery(this).is(':checked'));
                                                                if (jQuery(this).is(':checked')) {
                                                                    jQuery(this).parent('div').addClass('checked');
                                                                } else {
                                                                    jQuery(this).parent('div').removeClass('checked');
                                                                }
                                                            });
                                                            var is_material = jQuery(object).find('.arf_fieldset').hasClass('arf_materialize_form') || false;
                                                            jQuery('textarea.arf_text_is_countable').each(function(i) {
                                                                jQuery(this).trigger('keyup');
                                                            });

                                                            if (jQuery.isFunction(jQuery().selectpicker) && !is_material ) {
                                                                object.find('select').selectpicker('render');
                                                            }

                                                            if (!jQuery.isFunction(reset_like_field) || !jQuery.isFunction(reset_slider_field) || !jQuery.isFunction(reset_running_total) || !jQuery.isFunction(reset_colorpicker) || !jQuery.isFunction(reset_datetimepicker) || !jQuery.isFunction(reset_selectpicker)) {
                                                                return;
                                                            }

                                                            reset_checkbox_radio_field(object);
                                                            reset_like_field(object);
                                                            reset_slider_field(object);
                                                            reset_running_total(object);
                                                            reset_colorpicker(object);
                                                            reset_datetimepicker(object);
                                                            reset_selectpicker(object);

                                                            if (typeof reset_preview_out_side == 'function') {
                                                                reset_preview_out_side('<?php echo json_encode(array('id' => $form->id, 'form_key' => $form->form_key)); ?>', object);
                                                            }

                                                            if (typeof (__ARFSTRRNTH_INDICATOR) != 'undefined') {
                                                                var strenth_indicator = __ARFSTRRNTH_INDICATOR;
                                                            } else {
                                                                var strenth_indicator = 'Strength indicator';
                                                            }
                                                            jQuery(object).find('.arf_strenth_meter').removeClass('short bad good strong');
                                                            jQuery(object).find('.arf_strenth_mtr .inside_title').html(strenth_indicator);

                                                        }

                                                        if (typeof arf_rule_apply_bulk == 'function' && typeof window['arf_conditional_logic'] != 'undefined') {
                                                            arf_rule_apply_bulk('<?php echo $form->form_key; ?>', form_data_id);
                                                        }

                                                        var captcha_key = jQuery(object).find('input[name="field_captcha"]').attr('value');

                                                        if (!jQuery.isFunction(reloadcapcha)) {
                                                            return;
                                                        }
                                                        if(captcha_key !='' && captcha_key !=undefined && captcha_key != null){
                                                            reloadcapcha(object, captcha_key);
                                                        }
                                                        
                                                        var arf_bowser_name = jQuery(object).find('[data-id="arf_browser_name"]').val();
                                                        var arf_bowser_version = jQuery(object).find('[data-id="arf_browser_name"]').attr('data-version');                                                    
                                                        var EndTime = new Date().getTime();
                                                        var totalTimeTaken = EndTime - start_time;
                                                        var timetoseconds = Math.ceil(totalTimeTaken/1000);
                                                        var stoms = timetoseconds * 1000;
                                                        var deduction = stoms - 140;
                                                        var sleepCounter = 860;

                                                        if( deduction < totalTimeTaken ){
                                                            sleepCounter = totalTimeTaken - deduction;
                                                        } else {
                                                            sleepCounter = deduction - totalTimeTaken;
                                                        }

                                                        if ((arf_bowser_name == 'Opera' && arf_bowser_version <= 30) || (arf_bowser_name == 'Internet Explorer' && arf_bowser_version <= 9)) {
                                                            jQuery(object).find('.arf_submit_btn .arf_ie_image').hide();
                                                            jQuery(object).find('.arf_submit_btn .arfstyle-label').show();
                                                            jQuery(object).find('.arf_submit_btn:not(.arf_print_summary)').attr('disabled', false);
                                                        } else {
                                                            (function(obj,counter){
                                                                setTimeout(function(){
                                                                    var arf_form_hide_after_submit = jQuery(obj).find('[data-id="arf_form_hide_after_submit_' + break_form_id + '"]').val();
                                                                    if( arf_form_hide_after_submit == '1'){
                                                                        jQuery(object).slideUp("slow");
                                                                    }
                                                                    jQuery(obj).find('.arf_submit_btn').removeClass('arf_active_loader');
                                                                    jQuery(obj).find('.arf_submit_btn').addClass('arf_complete_loader');
                                                                    setTimeout(function(){
                                                                        jQuery(obj).find('.arf_submit_btn').removeClass('arf_complete_loader');
                                                                        jQuery(obj).find('.arf_submit_btn').attr('disabled', false);

                                                                        jQuery(obj).find('.arf_confirmation_summary_wrapper input[id^="arf_submit_form_after_confirm_"]').val('false');
                                                                        var display = jQuery(obj).find('.arf_confirmation_summary_wrapper').attr('data-confirmation-display');
                                                                        if( jQuery(obj).find('.arf_confirmation_summary_wrapper').length > 0 && display != 'after' ){
                                                                            jQuery(obj).find('.arf_confirmation_summary_wrapper').slideUp('slow');
                                                                            jQuery(obj).find('.arf_fieldset').slideDown('slow');
                                                                        } else {
                                                                        }
                                                                    },1000);
                                                                    setTimeout(function(){
                                                                        var display = jQuery(object).find('.arf_confirmation_summary_wrapper').attr('data-confirmation-display');
                                                                        if( display == 'after' ){
                                                                            jQuery(object).find('.arf_fieldset').slideUp('slow');
                                                                            jQuery(object).find('.arf_confirmation_summary_wrapper').slideDown('slow');
                                                                        }
                                                                    },500);
                                                                },counter);
                                                            })(object,sleepCounter);
                                                        }
                                                        
                                                    }, 3000);
                                                    setTimeout(function () {
                                                        jQuery('#form_success_' + break_form_id).slideUp("slow");
                                                    }, 6000);
                                                }

                                            }
                                        }
                                        else
                                        {

                                            if (!jQuery.isFunction(arf_validate_file)) {
                                                return;
                                            }
                                            if (!(arf_validate_file(event, formpreview, form_data_id)))
                                            {
                                                event.preventDefault();
                                                is_goto_next = false;
                                            }
                                            else
                                            {

                                                if (jQuery(object).find("#recaptcha_style").length > 0 && jQuery(object).find("#recaptcha_style").is(":visible") == true) {
                                                    is_goto_next = false;
                                                    event.preventDefault();
                                                    if (!jQuery.isFunction(checkRecaptcha)) {
                                                        return;
                                                    }
                                                    checkRecaptcha(object, '<?php echo ARFSCRIPTURL ?>', next_id, max_id, break_val, 'yes');

                                                } else {
                                                    var is_submit_form = jQuery('[data-id="is_submit_form_' + break_form_id + '"]').val();
                                                    var validate_captcha = is_check_recaptcha(object,form_id,event);
                                                    if (is_submit_form == 1 && validate_captcha) {

                                                        jQuery('[data-id="is_submit_form_' + break_form_id + '"]').val('0');
                                                        jQuery(object).find('[data-id="previous_last"]').css('display', 'none');
                                                        jQuery(object).find('.arf_submit_btn:not(.arf_print_summary)').attr('disabled', true);

                                                        var arf_bowser_name = jQuery(object).find('[data-id="arf_browser_name"]').val();
                                                        var arf_bowser_version = jQuery(object).find('[data-id="arf_browser_name"]').attr('data-version');

                                                        var start_time = new Date().getTime();
                                                        
                                                        if ((arf_bowser_name == 'Opera' && arf_bowser_version <= 30) || (arf_bowser_name == 'Internet Explorer' && arf_bowser_version <= 9)) {
                                                            jQuery(object).find('.arf_submit_btn .arfstyle-label').hide();
                                                            jQuery(object).find('.arf_submit_btn .arf_ie_image').css('display', 'inline-block');
                                                        } else {
                                                            jQuery(object).find('.arf_submit_btn').addClass('arf_active_loader');
                                                        }

                                                        setTimeout(function () {

                                                            jQuery('#form_success_' + break_form_id).slideDown();
                                                            jQuery('html, body').animate({scrollTop: jQuery('#message_success')}, 'slow');
                                                            jQuery('#form_<?php echo $form->form_key; ?>').show();
                                                            jQuery(object).find('input[type="submit"]').removeAttr('style');
                                                            jQuery(object).find('div').removeClass('arfblankfield');
                                                            jQuery(".help-block").empty();
                                                            jQuery('#hexagon').css('display', 'none');
                                                            jQuery(object).find('.arf_file_field').show();
                                                            if (!jQuery.isFunction(go_previous)) {
                                                                return;
                                                            }
                                                            go_previous('0', break_form_id, 'yes', '<?php echo $form->form_key; ?>', '0');
                                                            if (!jQuery.isFunction(arf_reset_page_nav)) {
                                                                return;
                                                            }
                                                            arf_reset_page_nav();

                                                            var is_formreset = jQuery(object).find('input[name="arf_is_resetform_aftersubmit_' + break_form_id + '"]').val();
                                                            if (is_formreset == 1)
                                                            {

                                                                jQuery("form[data-form-id='form_<?php echo $form->form_key; ?>']").trigger("reset");
                                                                jQuery(object).find('.arfprogress, .arf_info').hide();
                                                                jQuery(object).find('input[type="checkbox"], input[type="radio"]').not('.arf_hide_opacity').each(function (i) {
                                                                    jQuery(this).attr("checked", jQuery(this).is(':checked'));
                                                                    if (jQuery(this).is(':checked')) {
                                                                        jQuery(this).parent('div').addClass('checked');
                                                                    } else {
                                                                        jQuery(this).parent('div').removeClass('checked');
                                                                    }
                                                                });
                                                                var is_material = jQuery(object).parents('.arf_fieldset').hasClass('arf_materialize_form') || false;
                                                                jQuery('textarea.arf_text_is_countable').each(function(i) {
                                                                    jQuery(this).trigger('keyup');
                                                                });

                                                                if (jQuery.isFunction(jQuery().selectpicker) && !is_material ) {
                                                                    object.find('select').selectpicker('render');
                                                                }

                                                                if (!jQuery.isFunction(reset_like_field) || !jQuery.isFunction(reset_slider_field) || !jQuery.isFunction(reset_running_total) || !jQuery.isFunction(reset_colorpicker) || !jQuery.isFunction(reset_datetimepicker) || !jQuery.isFunction(reset_selectpicker)) {
                                                                    return;
                                                                }
                                                                reset_checkbox_radio_field(object);
                                                                reset_like_field(object);
                                                                reset_slider_field(object);
                                                                reset_running_total(object);
                                                                reset_colorpicker(object);
                                                                reset_datetimepicker(object);
                                                                reset_selectpicker(object);

                                                                if (typeof reset_preview_out_side == 'function') {
                                                                    reset_preview_out_side('<?php echo json_encode(array('id' => $form->id, 'form_key' => $form->form_key)); ?>', object);
                                                                }

                                                                if (typeof (__ARFSTRRNTH_INDICATOR) != 'undefined') {
                                                                    var strenth_indicator = __ARFSTRRNTH_INDICATOR;
                                                                } else {
                                                                    var strenth_indicator = 'Strength indicator';
                                                                }

                                                                jQuery(object).find('.arf_strenth_meter').removeClass('short bad good strong');
                                                                jQuery(object).find('.arf_strenth_mtr .inside_title').html(strenth_indicator);
                                                                jQuery(object).find('.arf_submit_btn:not(.arf_print_summary)').attr('disabled', false);

                                                                jQuery(object).find('.original_normal').each(function () {
                                                                    var field_key = jQuery(this).attr('id');
                                                                    field_key = field_key.replace('field_', '');
                                                                    jQuery('#file_name_' + field_key).text('<?php echo addslashes(esc_html__('No file selected', 'ARForms')); ?>');
                                                                });

                                                            }

                                                            if (typeof arf_rule_apply_bulk == 'function' && typeof window['arf_conditional_logic'] != 'undefined' ) {
                                                                arf_rule_apply_bulk('<?php echo $form->form_key; ?>', form_data_id);
                                                            }

                                                            var is_formreset_outside = jQuery(object).find('input[name="arf_is_resetform_outside_' + break_form_id + '"]').val();
                                                            if (is_formreset_outside == 1)
                                                            {
                                                                if (!jQuery.isFunction(arf_resetform_outside)) {
                                                                    return;
                                                                }
                                                                arf_resetform_outside(object, break_form_id);
                                                            }

                                                            jQuery('[data-id="is_submit_form_' + break_form_id + '"]').val('1');

                                                            var arf_data_validate = jQuery(object).find("[data-id='arf_validate_outside_" + break_form_id + "']").attr('data-validate');
                                                            jQuery(object).find("[data-id='arf_validate_outside_" + break_form_id + "']").val(arf_data_validate);

                                                            var captcha_key = jQuery(object).find('input[name="field_captcha"]').attr('value');
                                                            if (!jQuery.isFunction(reloadcapcha)) {
                                                                return;
                                                            }
                                                            if(captcha_key !='' && captcha_key !=undefined && captcha_key != null){
                                                                reloadcapcha(object, captcha_key);
                                                            }                                                        
                                                            
                                                            var arf_bowser_name = jQuery(object).find('[data-id="arf_browser_name"]').val();
                                                            var arf_bowser_version = jQuery(object).find('[data-id="arf_browser_name"]').attr('data-version');
                                                            var EndTime = new Date().getTime();
                                                            var totalTimeTaken = EndTime - start_time;
                                                            var timetoseconds = Math.ceil(totalTimeTaken/1000);
                                                            var stoms = timetoseconds * 1000;
                                                            var deduction = stoms - 140;
                                                            var sleepCounter = 860;

                                                            if( deduction < totalTimeTaken ){
                                                                sleepCounter = totalTimeTaken - deduction;
                                                            } else {
                                                                sleepCounter = deduction - totalTimeTaken;
                                                            }

                                                            if ((arf_bowser_name == 'Opera' && arf_bowser_version <= 30) || (arf_bowser_name == 'Internet Explorer' && arf_bowser_version <= 9)) {
                                                                jQuery(object).find('.arf_submit_btn .arfstyle-label').show();
                                                                jQuery(object).find('.arf_submit_btn .arf_ie_image').hide();
                                                                jQuery(object).find('.arf_submit_btn:not(.arf_print_summary)').attr('disabled', false);
                                                            } else {
                                                                (function(obj,counter){
                                                                setTimeout(function(){
                                                                    jQuery(obj).find('.arf_submit_btn').removeClass('arf_active_loader');
                                                                    jQuery(obj).find('.arf_submit_btn').addClass('arf_complete_loader');
                                                                    setTimeout(function(){
                                                                        jQuery(obj).find('.arf_submit_btn').removeClass('arf_complete_loader');
                                                                        jQuery(obj).find('.arf_submit_btn').attr('disabled', false);
                                                                        jQuery(obj).find('.arf_confirmation_summary_wrapper input[id^="arf_submit_form_after_confirm_"]').val('false');
                                                                        var display = jQuery(obj).find('.arf_confirmation_summary_wrapper').attr('data-confirmation-display');
                                                                        if( jQuery(obj).find('.arf_confirmation_summary_wrapper').length > 0 && display != 'after' ){
                                                                            jQuery(obj).find('.arf_confirmation_summary_wrapper').slideUp('slow');
                                                                            jQuery(obj).find('.arf_fieldset').slideDown('slow');
                                                                        } else {
                                                                        }
                                                                    },1000);
                                                                    setTimeout(function(){
                                                                        var display = jQuery(object).find('.arf_confirmation_summary_wrapper').attr('data-confirmation-display');
                                                                        if( display == 'after' ){
                                                                            jQuery(object).find('.arf_fieldset').slideUp('slow');
                                                                            jQuery(object).find('.arf_confirmation_summary_wrapper').slideDown('slow');
                                                                        }
                                                                    },500);
                                                                },counter);
                                                            })(object,sleepCounter);
                                                            }
                                                        }, 3000);
                                                        setTimeout(function () {
                                                            jQuery('#form_success_' + break_form_id).slideUp("slow");
                                                        }, 6000);

                                                    }

                                                }

                                            }
                                        }
                                    }

                                },
                                submitError: function (formpreviewid, event) {
                                    window.arf_is_submitting_form = false;
                                    object = jQuery('.arfshowmainform');
                                    var form_data_id = jQuery(object).find("input[name='form_data_id']").attr('value');
                                    if (jQuery(object).find('.arfformfield.arf_error').length > 0)
                                    {
                                        jQuery('html, body').animate({scrollTop: jQuery(jQuery(object).find('.arfformfield.arf_error').first()).offset().top - 100}, 'slow');
                                    }

                                    var tmp_div_id = jQuery(object).find('.arfformfield.arf_error').first().attr('id');
                                    var tmp_field_id = jQuery(object).find('.arfformfield.arf_error input, .arfformfield.arf_error select, .arfformfield.arf_error textarea').first().attr('id');

                                    if (!jQuery.isFunction(revalidate_focus)) {
                                        return;
                                    }
                                    revalidate_focus(tmp_field_id, tmp_div_id);


                                    var checkwhichsubmit = jQuery('#' + formpreview).find("input[name='form_submit_type']").attr('value');
                                    if (checkwhichsubmit != 1)
                                    {
                                        if (!jQuery.isFunction(arf_validate_file)) {
                                            return;
                                        }
                                        if (!(arf_validate_file(event, formpreview, form_data_id)))
                                        {
                                            event.preventDefault();
                                        }
                                    }
                                }

                            });

                <?php } else { ?>

                            var flagdata = "";

                            jQuery(document).ready(function (arfjqueryobj) {
                                arfjqueryobj(".arfshowmainform").not(".arfpagebreakform").find("input,select,textarea").not("[type=submit],div[id='recaptcha_style'] input").jqBootstrapValidation({
                                            submitSuccess: function ($form, event) {
                                                
                                                var form1 = jQuery($form).attr('data-form-id');
                                                
                                                var object = jQuery('.arfshowmainform[data-form-id="' + form1+'"]');

                                                var form_data_id = jQuery($form).attr('data-id');

                                                var arf_form_id = object.find("input[name='form_id']").attr('value');

                                                var checkwhichsubmit = object.find("input[name='form_submit_type']").attr('value');
                                                
                                                if (checkwhichsubmit == 1) {

                                                    if( $form.find('[data-arf-confirm]').length > 0 && document.getElementById('arf_submit_form_after_confirm_' + form_data_id).value != 'true' ){
                                                        var display_summary = jQuery(object).find('[data-arf-confirm]').attr('data-arf-display-confirmation') || 'before';
                                                        arf_do_action('arf_confirm_form_before_submit',form_data_id,display_summary);
                                                        
                                                        if( window.is_display_summary ){
                                                            event.preventDefault();
                                                            return false;
                                                        }
                                                    }

                                                    jQuery("form.arfshowmainform").each(function () {
                                                        if (jQuery(this).attr('data-id') == form_data_id) {
                                                            if (!jQuery.isFunction(arfgetformerrors_new)) {
                                                                return;
                                                            }
                                                            arfgetformerrors_new(jQuery(this), '<?php echo ARFSCRIPTURL ?>', event);
                                                            return false;
                                                        }
                                                    });

                                                    event.preventDefault();
                                                    
                                                }
                                                else
                                                {
                                                    ARFClearCookieFormData(arf_form_id);

                                                    if (!jQuery.isFunction(arf_validate_file)) {
                                                        return;
                                                    }
                                                    if (!(arf_validate_file(event, form1, form_data_id)))
                                                    {
                                                        event.preventDefault();
                                                        return false;
                                                    }

                                                    var arf_is_prevalidate = jQuery(object).find("[data-id='arf_is_validate_outside_" + arf_form_id + "']").attr('value');
                                                    var arf_is_prevalidate_form = jQuery(object).find("[data-id='arf_is_validate_outside_" + arf_form_id + "']").attr('data-validate');

                                                    if( arf_is_prevalidate == 1 && arf_is_prevalidate_form == 1 )
                                                    {
                                                        if (!jQuery.isFunction(arf_is_validateform_outside)) {
                                                            return;
                                                        }
                                                        arf_is_validateform_outside(jQuery(object), event);
                                                        event.preventDefault();
                                                        return false;
                                                    }

                                                    jQuery(object).find("[data-id='arf_is_validate_outside_" + arf_form_id + "']").val(arf_is_prevalidate_form);

                                                    var arf_prevalidate = jQuery('#' + form1).find("[data-id='arf_validate_outside_" + arf_form_id + "']").attr('value');
                                                    var arf_prevalidate_form = jQuery('#' + form1).find("[data-id='arf_validate_outside_" + arf_form_id + "']").attr('data-validate');

                                                    if (arf_prevalidate == 1 && arf_prevalidate_form == 1)
                                                    {
                                                        if (!jQuery.isFunction(arf_validate_form_outside)) {
                                                            return;
                                                        }
                                                        arf_validate_form_outside(jQuery('#' + form1), event);
                                                        event.preventDefault();
                                                        return false;
                                                    }
                                                    jQuery(object).find("[data-id='arf_validate_outside_" + arf_form_id + "']").val(arf_prevalidate_form);

                                                    if (!jQuery.isFunction(arf_validate_file)) {
                                                        return;
                                                    }
                                                    if (!(arf_validate_file(event, form1, form_data_id)))
                                                    {
                                                        event.preventDefault();
                                                    }
                                                    else
                                                    {

                                                        jQuery("form.arfshowmainform").each(function () {
                                                            if (jQuery(this).attr('data-id') == form_data_id) {
                                                                if (!jQuery.isFunction(arfgetformerrors_new)) {
                                                                    return;
                                                                }
                                                                arfgetformerrors_new(jQuery(this), '<?php echo ARFSCRIPTURL ?>', event);
                                                                return false;
                                                            }

                                                        });

                                                        if( jQuery(object).find('[data-arf-confirm]').length > 0 && document.getElementById('arf_submit_form_after_confirm_' + form_data_id).value != 'true' ){
                                                            var display_summary = jQuery(object).find('[data-arf-confirm]').attr('data-arf-display-confirmation') || 'before';
                                                            arf_do_action('arf_confirm_form_before_submit',form_data_id,display_summary);

                                                            if( window.is_display_summary ){
                                                                event.preventDefault();
                                                                return false;
                                                            }
                                                        } else {
                                                            var is_captcha_success = window.is_captcha_success || false;
                                                            if( !is_captcha_success ){
                                                                is_check_recaptcha(object,arf_form_id,event);
                                                                event.preventDefault();
                                                                return false;
                                                            }
                                                        }

                                                    }
                                                }

                                            },
                                            submitError: function ($form, event, $inputs) {
                                                window.arf_is_submitting_form = false;
                                                var form1 = jQuery($form).attr('data-form-id');
                                                
                                                var object = jQuery('.arfshowmainform[data-form-id="' + form1+'"]');

                                                var form_data_id = jQuery($form).attr('data-id');

                                                jQuery("form.arfshowmainform").each(function () {
                                                    if (jQuery(this).attr('data-id') == form_data_id) {
                                                        var object1 = jQuery(this);

                                                        if (jQuery('.arfmodal-body').parent(object1).find('.arfformfield.arf_error').length > 0)
                                                        {
                                                            var scrolltop = jQuery(jQuery('.arfmodal-body').parent(object1).find('.arfformfield.arf_error').first()).offset().top;
                                                            jQuery(window.opera ? '.arfmodal-body' : '.arfmodal-body').animate({scrollTop: jQuery(jQuery('.arfmodal-body').parent(object1).find('.arfformfield.arf_error').first()).offset().top - jQuery(jQuery('.arfmodal-body').parent(object1).find('.arfformfield').first()).offset().top - 50}, 'slow');

                                                            var tmp_div_id = jQuery('.arfmodal-body').parent(object1).find('.arfformfield.arf_error').first().attr('id');
                                                            var tmp_field_id = jQuery('.arfmodal-body').parent(object1).find('.arfformfield.arf_error input, .arfformfield.arf_error select, .arfformfield.arf_error textarea').first().attr('id');
                                                            jQuery('.arfmodal-body').parent(object1).find('.arfformfield.arf_error input, .arfformfield.arf_error select, .arfformfield.arf_error textarea').first().focus();
                                                            if (jQuery('#' + tmp_field_id).is('select')) {
                                                                jQuery('.arfmodal-body').parent(object1).find('#' + tmp_div_id + ' .dropdown-toggle[data-toggle="arfdropdown"]').focus();
                                                            }
                                                            if (!jQuery.isFunction(revalidate_focus)) {
                                                                return;
                                                            }
                                                            revalidate_focus(tmp_field_id, tmp_div_id, object1);

                                                        }
                                                        else if (jQuery(object1).find('.arfformfield.arf_error').length > 0)
                                                        {
                                                            jQuery(window.opera ? 'html, .arfmodal-body' : 'html, body, .arfmodal-body').animate({scrollTop: jQuery(jQuery(object1).find('.arfformfield.arf_error').first()).offset().top - 100}, 'slow');

                                                            var tmp_div_id = jQuery(object1).find('.arfformfield.arf_error').first().attr('id');
                                                            var tmp_field_id = jQuery(object1).find('.arfformfield.arf_error input, .arfformfield.arf_error select, .arfformfield.arf_error textarea').first().attr('id');
                                                            jQuery(object1).find('.arfformfield.arf_error input, .arfformfield.arf_error select, .arfformfield.arf_error textarea').first().focus();

                                                            if (jQuery('#' + tmp_field_id).is('select')) {
                                                                jQuery(object1).find('#' + tmp_div_id + ' .dropdown-toggle[data-toggle="arfdropdown"]').focus();
                                                            }
                                                            if (!jQuery.isFunction(revalidate_focus)) {
                                                                return;
                                                            }
                                                            revalidate_focus(tmp_field_id, tmp_div_id);

                                                        }
                                                        var checkwhichsubmit = jQuery('#' + form1).find("input[name='form_submit_type']").attr('value');           
                                                        if (checkwhichsubmit != 1)
                                                        {
                                                            if (!jQuery.isFunction(arf_validate_file)) {
                                                                return;
                                                            }
                                                            if (!(arf_validate_file(event, form1, form_data_id)))
                                                            {
                                                                event.preventDefault();
                                                            }
                                                        }
                                                    }
                                                });
                                            }


                                        });
                            });

                            jQuery("#main_form_flag_mange").html(flagdata);
                <?php } ?>



                    });
            });
            //})(jQuery);
            //});

            </script>
            <?php
            
            if (isset($arf_form_all_footer_js)) {
                echo '<script  id="arf_footer_field_js" type="text/javascript" data-cfasync="false">';
                echo 'window.addEventListener("DOMContentLoaded", function() { (function($) {';
                echo 'jQuery(document).ready(function (){';
                $arf_form_all_footer_js = apply_filters('arf_footer_javascript_from_outside',$arf_form_all_footer_js);
                echo $arf_form_all_footer_js;
                echo '}); })(jQuery); }); </script>';
            }
            
            if( isset($footer_cl_logic) && !empty($footer_cl_logic) ){
                echo "<script type='text/javascript'> window.addEventListener('DOMContentLoaded', function() { (function($) {";
                echo "jQuery(document).ready(function(){";
                    echo "setTimeout(function(){";
                        foreach($footer_cl_logic as $cl_logic){
                            echo "eval(".$cl_logic.");";
                        }
                    echo "},100);";
                echo "});";
                echo "})(jQuery); }); </script>";
            }
            return;
        }
    }

    function list_entries() {


        $params = $this->get_params();


        return $this->display_list($params);
    }

    function create() {


        global $arfform, $db_record;


        $params = $this->get_params();


        if ($params['form'])
            $form = $arfform->getOne($params['form']);


        $errors = $db_record->validate($_POST);


        if (count($errors) > 0) {


            $this->get_new_vars($errors, $form);
        } else {


            if (isset($_POST['arfpageorder' . $form->id])) {


                $this->get_new_vars('', $form);
            } else {


                $_SERVER['REQUEST_URI'] = str_replace('&arfaction=new', '', $_SERVER['REQUEST_URI']);


                $record = $db_record->create($_POST);


                if ($record)
                    $message = addslashes(esc_html__('Entry is Successfully Created', 'ARForms'));


                $this->display_list($params, $message, '', 1);
            }
        }
    }

    function destroy() {


        if (!current_user_can('arfdeleteentries')) {


            global $arfsettings;


            wp_die($arfsettings->admin_permission);
        }


        global $db_record, $arfform;


        $params = $this->get_params();


        if ($params['form'])
            $form = $arfform->getOne($params['form']);


        $message = '';


        if ($db_record->destroy($params['id']))
            $message = addslashes(esc_html__('Entry is Successfully Deleted', 'ARForms'));


        $this->display_list($params, $message, '', 1);
    }

    function destroy_all() {


        if (!current_user_can('arfdeleteentries')) {


            global $arfsettings;


            wp_die($arfsettings->admin_permission);
        }


        global $db_record, $arfform, $MdlDb;


        $params = $this->get_params();


        $message = '';


        $errors = array();


        if ($params['form']) {


            $form = $arfform->getOne($params['form']);


            $entry_ids = $MdlDb->get_col($MdlDb->entries, array('form_id' => $form->id));


            foreach ($entry_ids as $entry_id) {


                if ($db_record->destroy($entry_id))
                    $message = addslashes(esc_html__('Entries were Successfully Destroyed', 'ARForms'));
            }
        }else {


            $errors = addslashes(esc_html__('No entries were specified', 'ARForms'));
        }


        $this->display_list($params, $message, '', 0, $errors);
    }

    function bulk_actions($action = 'list-form') {


        global $db_record, $arfsettings, $armainhelper;


        $params = $this->get_params();


        $errors = array();


        $bulkaction = '-1';


        if ($action == 'list-form') {


            if ($_REQUEST['bulkaction'] != '-1')
                $bulkaction = $_REQUEST['bulkaction'];


            else if ($_POST['bulkaction2'] != '-1')
                $bulkaction = $_REQUEST['bulkaction2'];
        }else {


            $bulkaction = str_replace('bulk_', '', $action);
        }


        $items = $armainhelper->get_param('item-action', '');


        if (empty($items)) {


            $errors[] = addslashes(esc_html__('Please select one or more records.', 'ARForms'));
        } else {


            if (!is_array($items))
                $items = explode(',', $items);


            if ($bulkaction == 'delete') {


                if (!current_user_can('arfdeleteentries')) {


                    $errors[] = $arfsettings->admin_permission;
                } else {


                    if (is_array($items)) {


                        foreach ($items as $entry_id)
                            $db_record->destroy($entry_id);
                    }
                }
            } else if ($bulkaction == 'csv') {


                if (!current_user_can('arfviewentries'))
                    wp_die($arfsettings->admin_permission);





                global $arfform;


                $form_id = $params['form'];


                if ($form_id) {


                    $form = $arfform->getOne($form_id);
                } else {


                    $form = $arfform->getAll("is_template=0 AND (status is NULL OR status = '' OR status = 'published')", ' ORDER BY name', ' LIMIT 1');


                    if ($form)
                        $form_id = $form->id;
                    else
                        $errors[] = addslashes(esc_html__('No form is found', 'ARForms'));
                }


                if ($form_id and is_array($items)) {


                    echo '<script type="text/javascript" data-cfasync="false">window.onload=function(){location.href="' . site_url() . '/index.php?plugin=ARForms&controller=entries&form=' . $form_id . '&arfaction=csv&entry_id=' . implode(',', $items) . '";}</script>';
                }
            }
        }


        $this->display_list($params, '', false, false, $errors);
    }

    function show_form_popup($id = '', $key = '', $title = false, $description = false, $desc = '', $type = 'link', $modal_height = '540', $modal_width = '800', $position = 'left', $btn_angle = '0', $bgcolor = '', $txtcolor = '', $open_inactivity = '1', $open_scroll = '10', $open_delay = '0', $overlay = '0.6', $is_close_link = 'yes', $modal_bgcolor = '#000000') {

        global $arfform, $user_ID, $arfsettings, $post, $wpdb, $armainhelper, $arrecordcontroller, $arformcontroller, $MdlDb;

        $func_val = apply_filters('arf_hide_forms', $arformcontroller->arf_class_to_hide_form($id), $id);

        if ($id)
            $form = $arfform->getOne((int) $id);

        else if ($key)
            $form = $arfform->getOne($key);

        $form = apply_filters('arfpredisplayform', $form);


        if (( isset($form) and ! empty($form) ) and ( @$form->is_template or @ $form->status == 'draft')) {
            return addslashes(esc_html__('Please select a valid form', 'ARForms'));
        } else if (!$form or ( ($form->is_template or $form->status == 'draft') and ! isset($_GET) and ! isset($_GET['form']) )) {
            return addslashes(esc_html__('Please select a valid form', 'ARForms'));
        } else if ($form->is_loggedin && !$user_ID) {
            global $arfsettings;
            return do_shortcode($arfsettings->login_msg);
        }

        return $arrecordcontroller->get_form_popup(VIEWS_PATH . '/view-modal.php', $form, $title, $description, $desc, $type, $modal_height, $modal_width, $position, $btn_angle, $bgcolor, $txtcolor, $open_inactivity, $open_scroll, $open_delay, $overlay, $is_close_link, $modal_bgcolor, $func_val);
    }

    function get_form_popup($filename, $form, $title, $description, $desc, $type, $modal_height, $modal_width, $position, $btn_angle, $bgcolor, $txtcolor, $open_inactivity, $open_scroll, $open_delay, $overlay, $is_close_link, $modal_bgcolor, $func_val = 'true') {

        wp_print_styles('arfbootstrap-css');
        wp_print_styles('arfdisplaycss');
        wp_print_scripts('jquery-validation');
        wp_print_scripts('arfbootstrap-js');

        if (is_file($filename)) {

            $contents = '';
            ob_start();

            if ($bgcolor == '') {

                if ($type == 'fly') {

                    $bgcolor = ($position == 'left') ? '#2d6dae' : '#8ccf7a';
                } else if ($type == 'sticky') {

                    $bgcolor = ( in_array($position, array('right', 'bottom', 'left'))) ? '#1bbae1' : '#93979d';
                }
            }

            if ($txtcolor == '')
                $txtcolor = '#ffffff';





            include $filename;


            $contents .= ob_get_contents();


            ob_end_clean();


            return $contents;
        }


        return false;
    }

    function process_update_entry($params, $errors, $form, $final_input_meta) {

        global $db_record, $arfsavedentries, $arfcreatedentry, $arfsettings;

        $form->options = stripslashes_deep(maybe_unserialize($form->options));

        if ($params['action'] == 'update' and in_array((int) $params['id'], (array) $arfsavedentries))
            return;

        if ($params['action'] == 'create' and isset($arfcreatedentry[$form->id]) and isset($arfcreatedentry[$form->id]['entry_id']) and is_numeric($arfcreatedentry[$form->id]['entry_id'])) {


            $entry_id = $params['id'] = $arfcreatedentry[$form->id]['entry_id'];

            $conf_method = apply_filters('arfformsubmitsuccess', 'message', $form, $form->options);

            $return_script = '';
            $return["script"] = apply_filters('arf_after_submit_sucess_outside',$return_script,$form);

            if ($conf_method == 'redirect') {

                $success_url = apply_filters('arfcontent', $form->options['success_url'], $form, $entry_id);

                if ($success_url == false) {
                    global $arfsettings;
                    $message = '<div class="arf_form ar_main_div_' . $form->id . '" id="arffrm_' . $form->id . '_container"><div  id="arf_message_success"><div class="msg-detail"><div class="msg-description-success">' . $arfsettings->success_msg . '</div></div></div>';
                    $message .= do_action('arf_after_success_massage', $form);
                    $message .= '</div>';
                    $return["conf_method"] = "message";
                    $return["message"] = $message;
                    $return = apply_filters( 'arf_reset_built_in_captcha', $return, $_POST );
                    if($arfsettings->form_submit_type == 1) {
                        echo json_encode($return);
                        exit;
                    }
                } else if ($arfsettings->form_submit_type == 1) {
                    if(isset($form->options["arf_data_with_url"]) && $form->options["arf_data_with_url"] == 1) {
                        $return["conf_method"] = 'addon';
                        $return["message"] = $this->generate_redirect_form($form, $success_url, $form->options["arf_data_with_url_type"],$final_input_meta);
                    } else {
                        $return["conf_method"] = "redirect";
                        $return["message"] = $success_url;
                    }
                    $return = apply_filters( 'arf_reset_built_in_captcha', $return, $_POST );
                    echo json_encode($return);
                    exit;
                } else {
                    if(isset($form->options["arf_data_with_url"]) && $form->options["arf_data_with_url"] == 1) {
                        echo $redirection_form = $this->generate_redirect_form($form, $success_url, $form->options["arf_data_with_url_type"],$final_input_meta);
                    }
                    else {
                        echo "<script type='text/javascript' data-cfasync='false'> window.location='" . $success_url . "';</script>";
                    }
                    exit;
                }
            }
        } else if ($params['action'] == 'destroy') {

            $this->ajax_destroy($form->id, false, false);
        }
    }

    function generate_redirect_form($form_data, $url, $method,$item_meta_values) {

        global $wpdb, $MdlDb,$arfieldhelper;
        $redirect_form = "<form method='".strtoupper($method)."' action='".$url."' id='arf_new_redirect_form'>";


        foreach( $item_meta_values as $field_id => $field_value ){

            $field_type = $wpdb->get_row($wpdb->prepare("SELECT id, type, name FROM `".$MdlDb->fields."` WHERE form_id = %d AND id = %d",$form_data->id, $field_id));

            if( isset($field_type) && $field_type->id != '' ){
                if( $field_type->type == 'file' ){ continue; }
                if( $field_type->type == 'checkbox' ){
                    foreach( $field_value as $checkbox_val){
                        $redirect_form .= "<input type='hidden' data-type='{$field_type->type}' name='item_meta_{$field_id}[]' value='{$checkbox_val}' />";
                    }
                } else {
                    $redirect_form .= "<input type='hidden' data-type='{$field_type->type}' name='item_meta_{$field_id}' value='{$field_value}' />";
                }
            }
        }

        $redirect_form .= "</form>";
        $redirect_form .= '<script type="text/javascript" data-cfasync="false">document.getElementById("arf_new_redirect_form").submit();</script>';
        return $redirect_form;
    }

    function ajax_submit_button($arf_form, $form, $action = 'create') {

        global $arfnovalidate;

        if ($arfnovalidate) {
            $arf_form .= ' formnovalidate="formnovalidate"';
        }

        return $arf_form;
    }

    function get_confirmation_method($method, $form) {

        $method = (isset($form->options['success_action']) and ! empty($form->options['success_action'])) ? $form->options['success_action'] : $method;

        return $method;
    }

    function confirmation($method, $form, $form_options, $entry_id) {

        if ($method == 'page' and is_numeric($form_options['success_page_id'])) {


            global $post, $arfsettings;


            if ($form_options['success_page_id'] != $post->ID) {


                $page = get_post($form_options['success_page_id']);


                $old_post = $post;


                $post = $page;


                $content = apply_filters('arfcontent', $page->post_content, $form, $entry_id);

                $return["message"] = $content;

                $post = $old_post;

                if ($arfsettings->form_submit_type != 1) {
                    echo "<script type='text/javascript' data-cfasync='false'>
						jQuery(document).ready(function(){
							if (!jQuery.isFunction(popup_tb_show)) {
								return;
							}
							popup_tb_show('" . $form->id . "');
						});    
					</script>";
                }
            }
        } else if ($method == 'redirect') {

            $success_url = apply_filters('arfcontent', $form_options['success_url'], $form, $entry_id);

            $success_msg = isset($form_options['success_msg']) ? stripslashes($form_options['success_msg']) : addslashes(esc_html__('Please wait while you are redirected.', 'ARForms'));

            echo "<script type='text/javascript' data-cfasync='false'> jQuery(document).ready(function($){ setTimeout(window.location='" . $success_url . "', 5000); });</script>";
        }

        return $return["message"];
    }

    function csv($all_form_id, $search = '', $fid = '') {

        if (!current_user_can('arfviewentries')) {


            global $arfsettings;


            wp_die($arfsettings->admin_permission);
        }


        if (!ini_get('safe_mode')) {


            @set_time_limit(0);
        }


        global $current_user, $arfform, $arffield, $db_record, $arfrecordmeta, $wpdb, $style_settings;


        require(VIEWS_PATH . '/export_data.php');
    }

    function display_list($params = false, $message = '', $page_params_ov = false, $current_page_ov = false, $errors = array()) {


        global $wpdb, $MdlDb, $armainhelper, $arfform, $db_record, $arfrecordmeta, $arfpagesize, $arffield, $arfcurrentform;


        if (!$params)
            $params = $this->get_params();

        $errors = array();

        $form_select = $arfform->getAll("is_template=0 AND (status is NULL OR status = '' OR status = 'published')", ' ORDER BY name');

        if ($params['form']){
            $form = $arfform->getOne($params['form']);
        } else {
            $form = (isset($form_select[0])) ? $form_select[0] : 0;
        }

        if ($form) {
            $params['form'] = $form->id;
            $arfcurrentform = $form;
            $where_clause = " it.form_id=$form->id";
        } else {
            $where_clause = '';
        }

        $page_params = "&action=0&arfaction=0&form=";

        $page_params .= ($form) ? $form->id : 0;

        if (!empty($_REQUEST['s'])){
            $page_params .= '&s=' . urlencode($_REQUEST['s']);
        }

        if (!empty($_REQUEST['search'])){
            $page_params .= '&search=' . urlencode($_REQUEST['search']);
        }


        if (!empty($_REQUEST['fid'])){
            $page_params .= '&fid=' . $_REQUEST['fid'];
        }

        $item_vars = $this->get_sort_vars($params, $where_clause);


        $page_params .= ($page_params_ov) ? $page_params_ov : $item_vars['page_params'];

        $form_cols_order = array();
        $arffieldorder = array();
        $form_css = array();

        if ($form) {

            $form_cols_temp = array();

            $form_cols = $arffield->getAll("fi.type not in ('divider', 'captcha', 'break', 'html', 'imagecontrol') and fi.form_id=" . (int) $form->id, ' ORDER BY id');

            $form_options = $wpdb->get_row($wpdb->prepare("SELECT `form_css`,`options` FROM `" . $MdlDb->forms . "` WHERE `id` = %d", (int) $form->id));


            if(isset($form_options->form_css) && $form_options->form_css != ''){
                $form_css = maybe_unserialize($form_options->form_css);
            }

            if(isset($form_options->options) && $form_options->options != ''){

                $form_options = maybe_unserialize($form_options->options);

                if(isset($form_options['arf_field_order']) && $form_options['arf_field_order'] != ''){
                    $form_cols_order = json_decode($form_options['arf_field_order'], true);
                    asort($form_cols_order);
                    $arffieldorder = $form_cols_order;
                }
            }

            foreach ($form_cols_order as $fieldkey => $fieldorder) {
                foreach ($form_cols as $frmoptkey => $frmoptarr) {
                    if($frmoptarr->id == $fieldkey){
                        $form_cols_temp[] = $frmoptarr;
                        unset($form_cols[$frmoptkey]);
                    }
                }
            }

            if(count($form_cols_temp) > 0) {
                if(count($form_cols) > 0) {
                    $form_cols_other = $form_cols;
                    $form_cols = array_merge($form_cols_temp,$form_cols_other);
                } else {
                    $form_cols = $form_cols_temp;
                }
            }

            $record_where = ($item_vars['where_clause'] == " it.form_id=$form->id") ? $form->id : $item_vars['where_clause'];
        } else {


            $form_cols = array();


            $record_where = $item_vars['where_clause'];
        }

        $current_page = ($current_page_ov) ? $current_page_ov : $params['paged'];

        $sort_str = $item_vars['sort_str'];


        $sdir_str = $item_vars['sdir_str'];


        $search_str = $item_vars['search_str'];

        $fid = $item_vars['fid'];

        $record_count = $db_record->getRecordCount($record_where);

        $page_count = $db_record->getPageCount($arfpagesize, $record_count);

        $items = $db_record->getPage('', '', $item_vars['where_clause'], $item_vars['order_by'], '', $arffieldorder);

        $page_last_record = $armainhelper->getLastRecordNum($record_count, $current_page, $arfpagesize);

        $page_first_record = $armainhelper->getFirstRecordNum($record_count, $current_page, $arfpagesize);

        if (isset($_REQUEST['form']) && $_REQUEST['form'] == '-1' or ( !isset($_REQUEST['form']) or empty($_REQUEST['form']))) {
            $form_cols = array();
            $items = array();
        }




        require_once(VIEWS_PATH . '/view_records.php');
    }

    function get_sort_vars($params = false, $where_clause = '') {


        global $arfrecordmeta, $arfcurrentform;





        if (!$params)
            $params = $this->get_params($arfcurrentform);





        $order_by = '';


        $page_params = '';


        $sort_str = $params['sort'];


        $sdir_str = $params['sdir'];


        $search_str = $params['search'];


        $fid = $params['fid'];


        if (!empty($sort_str))
            $page_params .="&sort=$sort_str";


        if (!empty($sdir_str))
            $page_params .= "&sdir=$sdir_str";



        if (!empty($search_str)) {


            $where_clause = $this->get_search_str($where_clause, $search_str, $params['form'], $fid);


            $page_params .= "&search=$search_str";


            if (is_numeric($fid))
                $page_params .= "&fid=$fid";
        }


        if (is_numeric($sort_str))
            $order_by .= " ORDER BY ID";


        else if ($sort_str == "entry_key")
            $order_by .= " ORDER BY entry_key";
        else
            $order_by .= " ORDER BY ID";





        if ((empty($sort_str) and empty($sdir_str)) or $sdir_str == 'desc') {


            $order_by .= ' DESC';


            $sdir_str = 'desc';
        } else {


            $order_by .= ' ASC';


            $sdir_str = 'asc';
        }





        return compact('order_by', 'sort_str', 'sdir_str', 'fid', 'search_str', 'where_clause', 'page_params');
    }

    function get_search_str($where_clause = '', $search_str, $form_id = false, $fid = false) {


        global $arfrecordmeta, $armainhelper, $arfform;


        $where_item = '';


        $join = ' (';


        if (!is_array($search_str))
            $search_str = explode(" ", $search_str);



        foreach ($search_str as $search_param) {


            $search_param = esc_sql(like_escape($search_param));


            if (!is_numeric($fid)) {


                $where_item .= (empty($where_item)) ? ' (' : ' OR';



                if (in_array($fid, array('created_date', 'user_id'))) {


                    if ($fid == 'user_id' and ! is_numeric($search_param))
                        $search_param = $armainhelper->get_user_id_param($search_param);


                    $where_item .= " it.{$fid} like '%$search_param%'";
                }else {


                    $where_item .= " it.name like '%$search_param%' OR it.entry_key like '%$search_param%' OR it.description like '%$search_param%' OR it.created_date like '%$search_param%'";
                }
            }


            if (empty($fid) or is_numeric($fid)) {


                $where_entries = "(entry_value LIKE '%$search_param%'";


                if ($data_fields = $arfform->has_field('data', $form_id, false)) {


                    $df_form_ids = array();


                    foreach ((array) $data_fields as $df) {


                        $df->field_options = maybe_unserialize($df->field_options);


                        if (is_numeric($df->field_options['form_select']))
                            $df_form_ids[] = $df->field_options['form_select'];


                        unset($df);
                    }





                    unset($data_fields);


                    global $wpdb, $MdlDb;


                    $data_form_ids = $wpdb->get_col("SELECT form_id FROM $MdlDb->fields WHERE id in (" . implode(',', $df_form_ids) . ")");


                    unset($df_form_ids);


                    if ($data_form_ids) {


                        $data_entry_ids = $arfrecordmeta->getEntryIds("fi.form_id in (" . implode(',', $data_form_ids) . ") and entry_value LIKE '%" . $search_param . "%'");


                        if (!empty($data_entry_ids))
                            $where_entries .= " OR entry_value in (" . implode(',', $data_entry_ids) . ")";
                    }


                    unset($data_form_ids);
                }



                $where_entries .= ")";


                if (is_numeric($fid))
                    $where_entries .= " AND fi.id=$fid";



                $meta_ids = $arfrecordmeta->getEntryIds($where_entries);


                if (!empty($meta_ids)) {


                    if (!empty($where_clause)) {


                        $where_clause .= " AND" . $join;


                        if (!empty($join))
                            $join = '';
                    }


                    $where_clause .= " it.id in (" . implode(',', $meta_ids) . ")";
                }else {


                    if (!empty($where_clause)) {


                        $where_clause .= " AND" . $join;


                        if (!empty($join))
                            $join = '';
                    }


                    $where_clause .= " it.id=0";
                }
            }
        }





        if (!empty($where_item)) {


            $where_item .= ')';


            if (!empty($where_clause))
                $where_clause .= empty($fid) ? ' OR' : ' AND';


            $where_clause .= $where_item;


            if (empty($join))
                $where_clause .= ')';
        }else {


            if (empty($join))
                $where_clause .= ')';
        }





        return $where_clause;
    }

    function get_new_vars($errors = '', $form = '', $message = '') {


        global $arfform, $arffield, $db_record, $arfsettings, $arfnextpage, $arfieldhelper;


        $title = true;


        $description = true;


        $fields = $arfieldhelper->get_all_form_fields($form->id, !empty($errors));


        $values = $arrecordhelper->setup_new_vars($fields, $form);


        $submit = (isset($arfnextpage[$form->id])) ? $arfnextpage[$form->id] : (isset($values['submit_value']) ? $values['submit_value'] : $arfsettings->submit_value);


        require_once(VIEWS_PATH . '/new.php');
    }

    function get_params($form = null) {


        global $arfform, $armainhelper;


        if (!$form)
            $form = $arfform->getAll("is_template=0 AND (status is NULL OR status = '' OR status = 'published')", ' ORDER BY name', ' LIMIT 1');


        $values = array();


        foreach (array('id' => '', 'form_name' => '', 'paged' => 1, 'form' => (($form) ? $form->id : 0), 'field_id' => '', 'search' => '', 'sort' => '', 'sdir' => '', 'fid' => '') as $var => $default)
            $values[$var] = $armainhelper->get_param($var, $default);

        return $values;
    }

    function &filter_shortcode_value($value, $tag, $atts, $field) {


        if (isset($atts['show']) and $atts['show'] == 'value')
            return $value;


        $value = $this->filter_display_value($value, $field);


        return $value;
    }

    function &filter_entry_display_value($value, $field, $atts = array()) {
        $field->field_options = maybe_unserialize($field->field_options);
        $saved_value = (isset($atts['saved_value']) and $atts['saved_value']) ? true : false;
        if (!in_array($field->type, array('checkbox')) or ! isset($field->field_options['separate_value']) or ! $field->field_options['separate_value'] or $saved_value)
            return $value;
        $field->options = maybe_unserialize($field->options);
        $f_values = array();
        $f_labels = array();
        if(is_array($field->options))
        {
            foreach ($field->options as $opt_key => $opt) {
            if (!is_array($opt))
                continue;
            $f_labels[$opt_key] = isset($opt['label']) ? $opt['label'] : reset($opt);
            $f_values[$opt_key] = isset($opt['value']) ? $opt['value'] : $f_labels[$opt_key];
            if ($f_labels[$opt_key] == $f_values[$opt_key]) {
                unset($f_values[$opt_key]);
                unset($f_labels[$opt_key]);
            }
            unset($opt_key);
            unset($opt);
            }        
        }
        if (!empty($f_values)) {
            foreach ((array) $value as $v_key => $val) {
                if (in_array($val, $f_values)) {
                    $opt = array_search($val, $f_values);
                    if (is_array($value))
                        $value[$v_key] = $f_labels[$opt];
                    else
                        $value = $f_labels[$opt];
                }
                unset($v_key);
                unset($val);
            }
        }        
        return $value;
    }

    function &filter_display_value($value, $field) {
        global $arrecordcontroller;
        $value = $arrecordcontroller->filter_entry_display_value($value, $field);


        return $value;
    }

    function route() {

        global $armainhelper;
        $action = $armainhelper->get_param('arfaction');

        if ($action == 'create')
            return $this->create();

        else if ($action == 'destroy')
            return $this->destroy();


        else if ($action == 'destroy_all')
            return $this->destroy_all();


        else if ($action == 'graph')
            return $this->display_graph();


        else if ($action == 'list-form')
            return $this->bulk_actions($action);


        else {
            $action = $armainhelper->get_param('action');

            if ($action == -1)
                $action = $armainhelper->get_param('action2');

            if (strpos($action, 'bulk_') === 0) {

                if (isset($_GET) and isset($_GET['action']))
                    $_SERVER['REQUEST_URI'] = str_replace('&action=' . $_GET['action'], '', $_SERVER['REQUEST_URI']);

                if (isset($_GET) and isset($_GET['action2']))
                    $_SERVER['REQUEST_URI'] = str_replace('&action=' . $_GET['action2'], '', $_SERVER['REQUEST_URI']);

                return $this->bulk_actions($action);
            } else {
                return $this->display_list();
            }
        }
    }

    function get_form($filename, $form, $title, $description, $preview = false, $is_widget_or_modal = false, $is_confirmation_method = false, $func_val = 'true') {
        ;
        global $arfsettings;
        if ($func_val != 'true') {
            echo $func_val;
            exit;
        }


        if ($arfsettings->form_submit_type != 1) {
            wp_print_styles('arfbootstrap-css');
            wp_print_styles('arfdisplaycss');
            wp_print_scripts('jquery-validation');
            wp_print_scripts('arfbootstrap-js');
        }

        if (is_file($filename)) {


            ob_start();


            include $filename;


            $contents = ob_get_contents();


            ob_end_clean();

            return $contents;
        }


        return false;
    }

    function ajax_create() {


        global $db_record;

        $errors = $db_record->validate($_POST, array('file'));


        if (empty($errors)) {


            echo false;
        } else {


            $errors = str_replace('"', '&quot;', stripslashes_deep($errors));


            $obj = array();


            foreach ($errors as $field => $error) {


                $field_id = str_replace('field', '', $field);


                $obj[$field_id] = $error;
            }


            echo json_encode($obj);
        }


        die();
    }

    function ajax_update() {


        return $this->ajax_create();
    }

    function ajax_destroy($form_id = false, $ajax = true, $echo = true) {


        global $user_ID, $MdlDb, $db_record, $arfdeletedentries, $armainhelper;



        $entry_key = $armainhelper->get_param('entry');


        if (!$form_id)
            $form_id = $armainhelper->get_param('form_id');


        if (!$entry_key)
            return;



        if (is_array($arfdeletedentries) and in_array($entry_key, $arfdeletedentries))
            return;





        $where = array();


        if (!current_user_can('arfdeleteentries'))
            $where['user_id'] = $user_ID;





        if (is_numeric($entry_key))
            $where['id'] = $entry_key;
        else
            $where['entry_key'] = $entry_key;



        $entry = $MdlDb->get_one_record($MdlDb->entries, $where, 'id, form_id');



        if ($form_id and $entry->form_id != (int) $form_id)
            return;





        $entry_id = $entry->id;



        apply_filters('arfallowdelete', $entry_id, $entry_key, $form_id);


        if (!$entry_id) {


            $message = addslashes(esc_html__('There is an error deleting that entry', 'ARForms'));


            if ($echo)
                echo '<div class="frm_message">' . $message . '</div>';
        }else {


            $db_record->destroy($entry_id);


            if (!$arfdeletedentries)
                $arfdeletedentries = array();


            $arfdeletedentries[] = $entry_id;





            if ($ajax) {


                if ($echo)
                    echo $message = 'success';
            }else {


                $message = addslashes(esc_html__('Your entry is successfully deleted', 'ARForms'));





                if ($echo)
                    echo '<div class="frm_message">' . $message . '</div>';
            }
        }


        return $message;
    }

    function send_email($entry_id, $form_id, $type) {

        global $arnotifymodel;

        if (current_user_can('arfviewforms') or current_user_can('arfeditforms')) {


            if ($type == 'autoresponder')
                $sent_to = $arnotifymodel->autoresponder($entry_id, $form_id);
            else
                $sent_to = $arnotifymodel->entry_created($entry_id, $form_id);





            if (is_array($sent_to))
                echo implode(',', $sent_to);
            else
                echo $sent_to;
        }else {


            echo addslashes(esc_html__('No one! You do not have permission', 'ARForms'));
        }
    }

    function display_graph() {

        $form = $_REQUEST['form'];
        require_once(VIEWS_PATH . '/graph.php');
    }

    function updatechart() {

        $form = $_POST['form'];
        $type = $_POST['type'];
        $graph_type = $_POST['graph_type'];
        require_once(VIEWS_PATH . '/graph_ajax.php');

        die();
    }

    function managecolumns() {

        global $wpdb, $MdlDb;

        $form = $_POST['form'];

        $colsArray = $_POST['colsArray'];

        $new_arr = explode(',', $colsArray);

        $array_hidden = array();

        foreach ($new_arr as $key => $val) {

            if ($key % 2 == 0) {

                if ($new_arr[$key + 1] == 'hidden')
                    $array_hidden[] = $val;
            }
        }

        $ser_arr = maybe_serialize($array_hidden);

        $wpdb->update($MdlDb->forms, array('columns_list' => $ser_arr), array('id' => $form));

        die();
    }

    function arfchangebulkentries() {
        global $armainhelper, $wpdb, $MdlDb,$arfsettings,$db_record;
        $action1 = isset($_REQUEST['action1']) ? $_REQUEST['action1'] : '-1';
        $action2 = isset($_REQUEST['action2']) ? $_REQUEST['action2'] : '-1';

        $form_id = isset($_REQUEST['form_id']) ? $_REQUEST['form_id'] : '';
        $start_date = isset($_REQUEST['start_date']) ? $_REQUEST['start_date'] : '';
        $end_date = isset($_REQUEST['end_date']) ? $_REQUEST['end_date'] : '';

        $items = isset($_REQUEST['item-action']) ? $_REQUEST['item-action'] : array();

        $bulk_action = "-1";
        if ($action1 != '-1') {
            $bulk_action = $action1;
        } else if ($action1 == "-1" && $action2 != "-1") {
            $bulk_action = $action2;
        }

        if ($bulk_action == '-1') {
            echo json_encode(array('error' => true, 'message' => addslashes(esc_html__('Please select valid action.', 'ARForms'))));
            die();
        }

        if (empty($items)) {
            echo json_encode(array('error' => true, 'message' => addslashes(esc_html__('Please select one or more records', 'ARForms'))));
            die();
        }

        if ($bulk_action == 'bulk_delete') {
            if (!current_user_can('arfdeleteentries')) {
                echo json_encode(array('error' => true, 'message' => $arfsettings->admin_permission)); 
                die();
            } else {
                if (is_array($items)) {
                    foreach ($items as $entry_id) {
                        $del_res = $db_record->destroy($entry_id);
                    }

                    if ($del_res) {

                        $total_records = '';
                        if($form_id != ''){
                            $total_records = $db_record->getRecordCount( (int)$form_id );
                        }

                        $message = addslashes(esc_html__('Entries deleted successfully.', 'ARForms'));
                        echo json_encode(array('error'=>false, 'message'=> $message, 'arftotrec' => $total_records));
                    }
                }
            }
        } else if ($bulk_action == 'bulk_csv') {
                
    	    if (!current_user_can('arfviewentries'))
    		wp_die($arfsettings->admin_permission);

                        global $arfform;


                        if ($form_id) {

                            $form = $arfform->getOne($form_id);
                        } else {


                            $form = $arfform->getAll("is_template=0 AND (status is NULL OR status = '' OR status = 'published')", ' ORDER BY name', ' LIMIT 1');


                            if ($form)
                                $form_id = $form->id;
                            else
                                $errors[] = addslashes(esc_html__('No form is found', 'ARForms'));
                        }





                        if ($form_id and is_array($items)) {

    		$link = site_url() . '/index.php?plugin=ARForms&controller=entries&form=' . $form_id . '&arfaction=csv&entry_id=' . implode(',', $items);
    		echo json_encode($link);
    	    }
    	}
	die();
    }

    function arf_retrieve_form_entry_data(){
        global $wpdb, $MdlDb, $arffield, $armainhelper, $db_record, $arfform,$arfpagesize;
        

        $requested_data = json_decode(stripslashes_deep($_REQUEST['data']),true);

        $filtered_aoData = $requested_data['aoData'];

        $form_id = isset($filtered_aoData['form']) ? $filtered_aoData['form'] : '-1';
        $start_date = isset($filtered_aoData['start_date']) ? $filtered_aoData['start_date'] : '';
        $end_date = isset($filtered_aoData['end_date']) ? $filtered_aoData['end_date'] : '';

        $return_data = array();

        $form_select = $wpdb->get_row( $wpdb->prepare("SELECT * FROM `".$MdlDb->forms."` WHERE `id` = %d AND `is_template` != %d AND `status` = %s", $form_id, 1,'published') );

        $form_name = $form_select->name;

        $form_css = maybe_unserialize($form_select->form_css);

        $form_options = maybe_unserialize($form_select->options);

        $arffieldorder = array();

        if(isset($form_options['arf_field_order']) && $form_options['arf_field_order'] != ''){
            $arffieldorder = json_decode($form_options['arf_field_order'], true);
            asort($arffieldorder);
        }

        $offset = isset($filtered_aoData['iDisplayStart']) ? $filtered_aoData['iDisplayStart'] : 0;
        $limit = isset($filtered_aoData['iDisplayLength']) ? $filtered_aoData['iDisplayLength'] : 10;

        $searchStr = isset($filtered_aoData['sSearch']) ? $filtered_aoData['sSearch'] : '';
        $sorting_order = isset($filtered_aoData['sSortDir_0']) ? $filtered_aoData['sSortDir_0'] : 'desc';
        $sorting_column = (isset($filtered_aoData['iSortCol_0']) && $filtered_aoData['iSortCol_0'] > 0) ? $filtered_aoData['iSortCol_0'] : 1;

        $form_cols = $wpdb->get_results( $wpdb->prepare("SELECT * FROM `".$MdlDb->fields."` WHERE form_id = %d AND type NOT IN ('divider', 'captcha', 'break', 'html', 'imagecontrol') ORDER BY id ", $form_id) );

        if(count($arffieldorder) > 0){
            $form_cols_temp = array();
            foreach ($arffieldorder as $fieldkey => $fieldorder) {
                foreach ($form_cols as $frmoptkey => $frmoptarr) {
                    if($frmoptarr->id == $fieldkey){
                        $form_cols_temp[] = $frmoptarr;
                        unset($form_cols[$frmoptkey]);
                    }
                }
            }

            if(count($form_cols_temp) > 0) {
                if(count($form_cols) > 0) {
                    $form_cols_other = $form_cols;
                    $form_cols = array_merge($form_cols_temp,$form_cols_other);
                } else {
                    $form_cols = $form_cols_temp;
                }
            }
        }

        global $style_settings, $wp_scripts;
        $wp_format_date = get_option('date_format');

        if ($wp_format_date == 'F j, Y' || $wp_format_date == 'm/d/Y') {
            $date_format_new = 'mm/dd/yy';
        } else if ($wp_format_date == 'd/m/Y') {
            $date_format_new = 'dd/mm/yy';
        } else if ($wp_format_date == 'Y/m/d') {
            $date_format_new = 'dd/mm/yy';
        } else {
            $date_format_new = 'mm/dd/yy';
        }
        $new_start_date = $start_date;
        $new_end_date = $end_date;
        $show_new_start_date = $new_start_date;
        $show_new_end_date = $new_end_date;


        $arf_db_columns = array('0' => '', '1' => 'id');

        $form_cols = apply_filters('arfpredisplayformcols', $form_cols, $form_id);

        $arf_sorting_array = array();

        if (count($form_cols) > 0) {
            for ($col_i = 2; $col_i <= count($form_cols) + 1; $col_i++) {
                $col_j = $col_i - 2;
                $arf_db_columns[$col_i] = $armainhelper->truncate($form_cols[$col_j]->name, 40);
                $arf_sorting_array[$form_cols[$col_j]->id] = $col_i;
            }
            $arf_db_columns[$col_i] = 'entry_key';
            $arf_db_columns[$col_i + 1] = 'created_date';
            $arf_db_columns[$col_i + 2] = 'browser_info';
            $arf_db_columns[$col_i + 3] = 'ip_address';
            $arf_db_columns[$col_i + 4] = 'country';
            $arf_db_columns[$col_i + 5] = 'Page URL';
            $arf_db_columns[$col_i + 6] = 'Referrer URL';
            $arf_db_columns[$col_i + 7] = 'Action';

        } else {
            $arf_db_columns['2'] = 'entry_key';
            $arf_db_columns['3'] = 'created_date';
            $arf_db_columns['4'] = 'browser_info';
            $arf_db_columns['5'] = 'ip_address';
            $arf_db_columns['6'] = 'country';
            $arf_db_columns['7'] = 'Page URL';
            $arf_db_columns['8'] = 'Referrer URL';
            $arf_db_columns['9'] = 'Action';
        }

        $arforderbycolumn = isset($arf_db_columns[$sorting_column]) ? $arf_db_columns[$sorting_column] : 'id';
        $item_order_by = " ORDER BY it.$arforderbycolumn $sorting_order";

        $where_clause = "it.form_id=".$form_id;

        if ($new_start_date != '' and $new_end_date != '') {
            if ($date_format_new == 'dd/mm/yy') {
                $new_start_date = str_replace('/', '-', $new_start_date);
                $new_end_date = str_replace('/', '-', $new_end_date);
            }
            $new_start_date_var = date('Y-m-d', strtotime($new_start_date));

            $new_end_date_var = date('Y-m-d', strtotime($new_end_date));

            $where_clause .= " and DATE(it.created_date) >= '" . $new_start_date_var . "' and DATE(it.created_date) <= '" . $new_end_date_var . "'";
        } else if ($new_start_date != '' and $new_end_date == '') {
            if ($date_format_new == 'dd/mm/yy') {
                $new_start_date = str_replace('/', '-', $new_start_date);
            }
            $new_start_date_var = date('Y-m-d', strtotime($new_start_date));

            $where_clause .= " and DATE(it.created_date) >= '" . $new_start_date_var . "'";
        } else if ($new_start_date == '' and $new_end_date != '') {
            if ($date_format_new == 'dd/mm/yy') {
                $new_end_date = str_replace('/', '-', $new_end_date);
            }
            $new_end_date_var = date('Y-m-d', strtotime($new_end_date));

            $where_clause .= " and DATE(it.created_date) <= '" . $new_end_date_var . "'";
        }

        $total_records = $wpdb->get_var("SELECT count(*) as total_entries FROM `".$MdlDb->entries."` it WHERE ".$where_clause);
        
        $item_order_by .= " LIMIT {$offset},{$limit}";
        if( isset($arf_sorting_array) && !empty($arf_sorting_array) && in_array($sorting_column,$arf_sorting_array) ){
            $temp_items = $db_record->getPage('', '', $where_clause, '', $searchStr, $arffieldorder);
            $temp_field_metas = array();
            $sorting_value = array_search($sorting_column, $arf_sorting_array);
            foreach( $temp_items as $K => $I ){
                foreach( $arf_sorting_array as $a => $b ){
                    $temp_field_metas[$K][$a] = $I->metas[$a];
                    $temp_field_metas[$K]['sorting_column'] = $sorting_value;
                }
            }
                        
            if( $sorting_order == 'asc' ){
                uasort( $temp_field_metas, function($a, $b){
                    $sort_on = $a['sorting_column'];
                    return strnatcasecmp($a[$sort_on],$b[$sort_on]);
                });
            } else {
                uasort( $temp_field_metas, function($a, $b){
                    $sort_on = $a['sorting_column'];
                    return strnatcasecmp($b[$sort_on],$a[$sort_on]);
                });
            }
            $sorted_columns = array();
            $counter = 0;

            foreach( $temp_field_metas as $c => $d ){
            	$sorted_columns[$c] = $temp_items[$c];
                $counter++;
            }
            $sorted_cols = array_chunk($sorted_columns, $limit);

            $chuncked_array_key = ceil($offset / $limit) + 1;

            $chunk_key = $chuncked_array_key - 1;
            $items = $sorted_cols[$chunk_key];

        } else {

            $items = $db_record->getPage('', '', $where_clause, $item_order_by, $searchStr, $arffieldorder);
        }

        $action_no = 0;

        if( is_rtl() ){
            $divStyle = "display:inline-block;position:relative;";
        } else {
            $divStyle = "position:relative;width:100%;text-align:center;";
        }

        $default_hide = array(
            '0' => '<div style="'.$divStyle.'"><div class="arf_custom_checkbox_div arfmarginl15"><div class="arf_custom_checkbox_wrapper arfmargin10custom"><input id="cb-select-all-1" type="checkbox" class=""><svg width="18px" height="18px">'.ARF_CUSTOM_UNCHECKED_ICON.'
                                '.ARF_CUSTOM_CHECKED_ICON.'</svg></div></div>
            <label for="cb-select-all-1"  class="cb-select-all"><span class="cb-select-all-checkbox"></span></label></div>',
            '1' => 'ID'
        );

        $items = apply_filters('arfpredisplaycolsitems', $items, $form_id);

        if (count($form_cols) > 0) {
            for ($i = 2; $i <= count($form_cols) + 1; $i++) {
                $j = $i - 2;
                $default_hide[$i] = $armainhelper->truncate($form_cols[$j]->name, 40);
            }
            $default_hide[$i] = 'Entry key';
            $default_hide[$i + 1] = 'Entry Creation Date';
            $default_hide[$i + 2] = 'Browser Name';
            $default_hide[$i + 3] = 'IP Address';
            $default_hide[$i + 4] = 'Country';
            $default_hide[$i + 5] = 'Page URL';
            $default_hide[$i + 6] = 'Referrer URL';
            $default_hide[$i + 7] = 'Action';

            $action_no = $i + 7;
        } else {
            $default_hide['2'] = 'Entry Key';
            $default_hide['3'] = 'Entry creation date';
            $default_hide['4'] = 'Browser Name';
            $default_hide['5'] = 'IP Address';
            $default_hide['6'] = 'Country';
            $default_hide['7'] = 'Page URL';
            $default_hide['8'] = 'Referrer URL';
            $default_hide['9'] = 'Action';
            $action_no = 9;
        }


        $columns_list_res = $wpdb->get_results($wpdb->prepare('SELECT columns_list FROM ' . $MdlDb->forms . ' WHERE id = %d', $form_id), ARRAY_A);

        $columns_list_res = $columns_list_res[0];

        $columns_list = maybe_unserialize($columns_list_res['columns_list']);

        $is_colmn_array = is_array($columns_list);

        $exclude = '';

        $exclude_array = array();

        if ($is_colmn_array && count($columns_list) > 0 and $columns_list != '') {

            foreach ($columns_list as $keys => $column) {

                foreach ($default_hide as $key => $val) {

                    if ($column == $val) {
                        if ($exclude_array == "") {
                            $exclude_array[] = $key;
                        } else {
                            if (!in_array($key, $exclude_array)) {
                                $exclude_array[] = $key;

                                $exclude_no++;
                            }
                        }
                    }
                }
            }
        }

        $ipcolumn = ($action_no - 4);
        $page_url_column = ($action_no - 2);
        $referrer_url_column = ($action_no - 1);

        if ($exclude_array == "" and ! $is_colmn_array) {
            $exclude_array = array($ipcolumn, $page_url_column, $referrer_url_column);
        } else if (is_array($exclude_array) and ! $is_colmn_array) {

            if (!in_array($ipcolumn, $exclude_array)) {
                array_push($exclude_array, $ipcolumn);
            }
            if (!in_array($page_url_column, $exclude_array)) {
                array_push($exclude_array, $page_url_column);
            }
            if (!in_array($referrer_url_column, $exclude_array)) {
                array_push($exclude_array, $referrer_url_column);
            }
        }

        if ($exclude_array != "") {
            $exclude = implode(",", $exclude_array);
        }

        if ($wp_format_date == 'F j, Y' || $wp_format_date == 'm/d/Y') {
            $date_format_new = 'MM/DD/YYYY';
            $date_format_new1 = 'MM-DD-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '12/31/2050';
        } else if ($wp_format_date == 'd/m/Y') {
            $date_format_new = 'DD/MM/YYYY';
            $date_format_new1 = 'DD-MM-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '31/12/2050';
        } else if ($wp_format_date == 'Y/m/d') {
            $date_format_new = 'DD/MM/YYYY';
            $date_format_new1 = 'DD-MM-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '31/12/2050';
        } else {
            $date_format_new = 'MM/DD/YYYY';
            $date_format_new1 = 'MM-DD-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '12/31/2050';
        }

        $data = array();

        if( count($items) > 0 ){
            $ai = 0;
            $arf_edit_select_array = array();
            foreach ($items as $key => $item) {
                if( is_rtl() ){
                    $divStyle = "display:inline-block;position:relative;";
                } else {
                    $divStyle = "position:relative;width:100%;text-align:center;";
                }
                $data[$ai][0] = "<div class='DataTables_sort_wrapper'><div style='{$divStyle}'>
                       <div class='arf_custom_checkbox_div arfmarginl15'><div class='arf_custom_checkbox_wrapper'><input id='cb-item-action-{$item->id}' class='' type='checkbox' value='{$item->id}' name='item-action[]' />
                                        <svg width='18px' height='18px'>
                                        ".ARF_CUSTOM_UNCHECKED_ICON."
                                        ".ARF_CUSTOM_CHECKED_ICON."
                                        </svg>
                                    </div>
                                </div>
                    <label for='cb-item-action-{$item->id}'><span></span></label></div></div>" ;
                $data[$ai][1] = $item->id;
                $ni = 2;
                foreach ($form_cols as $col) {
                    $field_value = isset($item->metas[$col->id]) ? $item->metas[$col->id] : false;
                    if( !is_array($col->field_options) ){
                        $col->field_options = json_decode($col->field_options,true);
                    }

                    if( !is_array($col->options) ){
                        $col->options = json_decode($col->options,true);
                    }
                    
                    if ($col->type == 'checkbox' || $col->type == 'radio' || $col->type == 'select') {
                        if (isset($col->field_options['separate_value']) && $col->field_options['separate_value'] == '1') {
                            $option_separate_value = array();

                            foreach ($col->options as $k => $options) {
                                $option_separate_value[] = array('value' => htmlentities($options['value']), 'text' => $options['label']);
                            }
                            $arf_edit_select_array[] = array($col->id => json_encode($option_separate_value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
                        } else {
                            $option_value = '';
                            $option_value = array();
                            if(is_array($col->options))
                            {
                                foreach ($col->options as $k => $options) {
                                    if (is_array($options)) {
                                        $option_value[] = ($options['label']);
                                    } else {
                                        $option_value[] = ($options);
                                    }
                                }
                            }
                            $arf_edit_select_array[] = array($col->id => json_encode($option_value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
                        }
                    }
                    global $arrecordhelper;

                    $data[$ai][$ni] = $arrecordhelper->display_value($field_value, $col, array('type' => $col->type, 'truncate' => true, 'attachment_id' => $item->attachment_id, 'entry_id' => $item->id),$form_css);
                    $ni++;
                }
                $data[$ai][$ni] = $item->entry_key;
                $data[$ai][$ni + 1] = date(get_option('date_format'), strtotime($item->created_date));
                $browser_info = $this->getBrowser($item->browser_info);
                $data[$ai][$ni + 2] = $browser_info['name'] . ' (Version: ' . $browser_info['version'] . ')';
                $data[$ai][$ni + 3] = $item->ip_address;
                $data[$ai][$ni + 4] = $item->country;
                $http_referrer = maybe_unserialize($item->description);
                $data[$ai][$ni + 5] = urldecode($http_referrer['page_url']);
                $data[$ai][$ni + 6] = urldecode($http_referrer['http_referrer']);

                $view_entry_icon = is_rtl() ? 'view_icon23_rtl.png' : 'view_icon23.png';
                $view_entry_icon_hover = is_rtl() ? 'view_icon23_hover_rtl.png' : 'view_icon23_hover.png';

                $view_entry_btn = "<div class='arfformicondiv arfhelptip' title='" . addslashes(esc_html__('Preview', 'ARForms')) . "'><a href='javascript:void(0);'  onclick='open_entry_thickbox({$item->id},\"".htmlentities($form_name, ENT_QUOTES)."\");'><svg width='30px' height='30px' viewBox='-3 -8 32 32' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#ffffff' d='M12.993,15.23c-7.191,0-11.504-7.234-11.504-7.234  S5.801,0.85,12.993,0.85c7.189,0,11.504,7.19,11.504,7.19S20.182,15.23,12.993,15.23z M12.993,2.827  c-5.703,0-8.799,5.214-8.799,5.214s3.096,5.213,8.799,5.213c5.701,0,8.797-5.213,8.797-5.213S18.694,2.827,12.993,2.827z   M12.993,11.572c-1.951,0-3.531-1.581-3.531-3.531s1.58-3.531,3.531-3.531c1.949,0,3.531,1.581,3.531,3.531  S14.942,11.572,12.993,11.572z'/></svg></a></div>";

                global $PDF_button;
                do_action('arf_additional_action_entries', $item->id, $form_id,true);
                global $PDF_button;
                
                $id = $item->id;

                $delete_entry_icon = is_rtl() ? 'delete_icon223_rtl.png' : 'delete_icon223.png';
                $delete_entry_icon_hover = is_rtl() ? 'delete_icon223_hover_rtl.png' : 'delete_icon223_hover.png';

                $delete_entry_btn = "<div class='arfformicondiv arfhelptip arfentry_delete_div_".$item->id."' title='" . addslashes(esc_html__('Delete', 'ARForms')) . "'><a data-id='".$item->id."' id='arf_delete_single_entry' style='cursor:pointer'><svg width='30px' height='30px' viewBox='-5 -5 32 32' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#ffffff' d='M18.435,4.857L18.413,19.87L3.398,19.88L3.394,4.857H1.489V2.929  h1.601h3.394V0.85h8.921v2.079h3.336h1.601l0,0v1.928H18.435z M15.231,4.857H6.597H5.425l0.012,13.018h10.945l0.005-13.018H15.231z   M11.4,6.845h2.029v9.065H11.4V6.845z M8.399,6.845h2.03v9.065h-2.03V6.845z' /></svg></a></div>";

                $delete_entry_overlay = "<div id='view_entry_detail_container_{$item->id}' style='display:none;'>" . $this->get_entries_list_edit($item->id,$arffieldorder) . "</div><div style='clear:both;' class='arfmnarginbtm10'></div>";

                $data[$ai][$ni + 7] = "<div class='arf-row-actions'>{$view_entry_btn}{$PDF_button}{$delete_entry_btn} {$delete_entry_overlay} <input type='hidden' id='arf_edit_select_array_one' value='" . json_encode($arf_edit_select_array) . "' /></div>";
                $data[$ai][$ni + 7] .= "<input type='hidden' id='arf_edit_select_array_{$item->id}' value='".json_encode($arf_edit_select_array)."' />";
                $PDF_button = '';
                $action_no = $ni + 7;
                $ai++;
            }
            $sEcho = isset($filtered_aoData['sEcho']) ? intval($filtered_aoData['sEcho']) : intval(10);

            $return_data = array(
                'sEcho' => $sEcho,
                'iTotalRecords' => (int)$total_records,
                'iTotalDisplayRecords' => (int)$total_records,
                'aaData' => $data,
            );

        } else {
            $sEcho = isset($filtered_aoData['sEcho']) ? intval($filtered_aoData['sEcho']) : intval(10);
            $return_data = array(
                'sEcho' => $sEcho,
                'iTotalRecords' => (int)$total_records,
                'iTotalDisplayRecords' => (int)$total_records,
                'aaData' => $data,
            );
        }

        echo json_encode( $return_data );

        die;
    }

    function arf_form_entries() {
        global $wpdb, $MdlDb, $arffield, $armainhelper, $db_record, $arfform,$arfpagesize;

        $form_id = isset($_REQUEST['form']) ? $_REQUEST['form'] : '-1';
        $start_date = isset($_REQUEST['start_date']) ? $_REQUEST['start_date'] : '';
        $end_date = isset($_REQUEST['end_date']) ? $_REQUEST['end_date'] : '';

        if (isset($params) && $params['form']){
    		$form = $arfform->getOne($params['form']);
    	} else {
    		$form = (isset($form_select[0])) ? $form_select[0] : 0;
    	}

        if (empty($params) || !$params) {
            $params = $this->get_params();
        }

        $params['form'] = $form_id;

        $form_select = $arfform->getAll("is_template=0 AND (status is NULL OR status = '' OR status = 'published')", ' ORDER BY name');
        if ($params['form']){
            $form = $arfform->getOne($params['form']);
        } else {
            $form = (isset($form_select[0])) ? $form_select[0] : 0;
        }

        if ($form) {
            $params['form'] = $form->id;
            $arfcurrentform = $form;
            $where_clause = " it.form_id=$form->id";
        } else {
            $where_clause = '';
        }

        $page_params = "&action=0&arfaction=0&form=";
        $page_params .= ($form) ? $form->id : 0;

        if (!empty($_REQUEST['fid']))
            $page_params .= '&fid=' . $_REQUEST['fid'];

        $form_css = maybe_unserialize($form->form_css);

        $item_vars = $this->get_sort_vars($params, $where_clause);
        $page_params .= ( isset($page_params_ov) ) ? $page_params_ov : $item_vars['page_params'];

        if ($form) {
            $form_cols = $arffield->getAll("fi.type not in ('divider', 'captcha', 'break', 'html', 'imagecontrol') and fi.form_id=" . (int) $form->id, ' ORDER BY id');
            $record_where = ($item_vars['where_clause'] == " it.form_id=$form->id") ? $form->id : $item_vars['where_clause'];
        } else {
            $form_cols = array();
            $record_where = $item_vars['where_clause'];
        }

        $current_page = ( isset($current_page_ov) ) ? $current_page_ov : $params['paged'];

        $sort_str = $item_vars['sort_str'];

        $sdir_str = $item_vars['sdir_str'];

        $search_str = $item_vars['search_str'];

        $fid = $item_vars['fid'];

        $record_count = $db_record->getRecordCount($record_where);

        $page_count = $db_record->getPageCount($arfpagesize, $record_count);


        global $style_settings, $wp_scripts;
        $wp_format_date = get_option('date_format');

        if ($wp_format_date == 'F j, Y' || $wp_format_date == 'm/d/Y') {
            $date_format_new = 'mm/dd/yy';
        } else if ($wp_format_date == 'd/m/Y') {
            $date_format_new = 'dd/mm/yy';
        } else if ($wp_format_date == 'Y/m/d') {
            $date_format_new = 'dd/mm/yy';
        } else {
            $date_format_new = 'mm/dd/yy';
        }
        $new_start_date = $start_date;
        $new_end_date = $end_date;
        $show_new_start_date = $new_start_date;
        $show_new_end_date = $new_end_date;

        if ($new_start_date != '' and $new_end_date != '') {
            if ($date_format_new == 'dd/mm/yy') {
                $new_start_date = str_replace('/', '-', $new_start_date);
                $new_end_date = str_replace('/', '-', $new_end_date);
            }
            $new_start_date_var = date('Y-m-d', strtotime($new_start_date));

            $new_end_date_var = date('Y-m-d', strtotime($new_end_date));

            $item_vars['where_clause'] .= " and DATE(it.created_date) >= '" . $new_start_date_var . "' and DATE(it.created_date) <= '" . $new_end_date_var . "'";
        } else if ($new_start_date != '' and $new_end_date == '') {
            if ($date_format_new == 'dd/mm/yy') {
                $new_start_date = str_replace('/', '-', $new_start_date);
            }
            $new_start_date_var = date('Y-m-d', strtotime($new_start_date));

            $item_vars['where_clause'] .= " and DATE(it.created_date) >= '" . $new_start_date_var . "'";
        } else if ($new_start_date == '' and $new_end_date != '') {
            if ($date_format_new == 'dd/mm/yy') {
                $new_end_date = str_replace('/', '-', $new_end_date);
            }
            $new_end_date_var = date('Y-m-d', strtotime($new_end_date));

            $item_vars['where_clause'] .= " and DATE(it.created_date) <= '" . $new_end_date_var . "'";
        }

        $items = $db_record->getPage('', '', $item_vars['where_clause'], $item_vars['order_by']);

        $page_last_record = $armainhelper->getLastRecordNum($record_count, $current_page, $arfpagesize);

        $page_first_record = $armainhelper->getFirstRecordNum($record_count, $current_page, $arfpagesize);

        if ((isset($form_id) && $form_id == '-1') || ( empty($form_id) || empty($form->id) )) {
            $form_cols = array();
            $items = array();
        }

        $action_no = 0;
        if( is_rtl() ){
            $divStyle = "display:inline-block;position:relative;";
        } else {
            $divStyle = "position:relative;width:100%;text-align:center;";
        }

        $default_hide = array(
            '0' => '<div style="'.$divStyle.'"><div class="arf_custom_checkbox_div arfmarginl15"><div class="arf_custom_checkbox_wrapper arfmargin10custom"><input id="cb-select-all-1" type="checkbox" class=""><svg width="18px" height="18px">'.ARF_CUSTOM_UNCHECKED_ICON.'
                                '.ARF_CUSTOM_CHECKED_ICON.'</svg></div></div>
            <label for="cb-select-all-1"  class="cb-select-all"><span class="cb-select-all-checkbox"></span></label></div>',
            '1' => 'ID'
        );

        $form_cols = apply_filters('arfpredisplayformcols', $form_cols, $form->id);
        $items = apply_filters('arfpredisplaycolsitems', $items, $form->id);

        if (count($form_cols) > 0) {
            for ($i = 2; $i <= count($form_cols) + 1; $i++) {
                $j = $i - 2;
                $default_hide[$i] = $armainhelper->truncate($form_cols[$j]->name, 40);
            }
            $default_hide[$i] = 'Entry key';
            $default_hide[$i + 1] = 'Entry Creation Date';
            $default_hide[$i + 2] = 'Browser Name';
            $default_hide[$i + 3] = 'IP Address';
            $default_hide[$i + 4] = 'Country';
            $default_hide[$i + 5] = 'Page URL';
            $default_hide[$i + 6] = 'Referrer URL';
            $default_hide[$i + 7] = 'Action';

            $action_no = $i + 7;
        } else {
            $default_hide['2'] = 'Entry Key';
            $default_hide['3'] = 'Entry creation date';
            $default_hide['4'] = 'Browser Name';
            $default_hide['5'] = 'IP Address';
            $default_hide['6'] = 'Country';
            $default_hide['7'] = 'Page URL';
            $default_hide['8'] = 'Referrer URL';
            $default_hide['9'] = 'Action';
            $action_no = 9;
        }

        $columns_list_res = $wpdb->get_results($wpdb->prepare('SELECT columns_list FROM ' . $MdlDb->forms . ' WHERE id = %d', $form->id), ARRAY_A);
        $columns_list_res = $columns_list_res[0];

        $columns_list = maybe_unserialize($columns_list_res['columns_list']);

        $is_colmn_array = is_array($columns_list);

        $exclude = '';

        $exclude_array = array();

        if (count($columns_list) > 0 and $columns_list != '') {

            foreach ($columns_list as $keys => $column) {

                foreach ($default_hide as $key => $val) {

                    if ($column == $val) {
                        if ($exclude_array == "") {
                            $exclude_array[] = $key;
                        } else {
                            if (!in_array($key, $exclude_array)) {
                                $exclude_array[] = $key;

                                $exclude_no++;
                            }
                        }
                    }
                }
            }
        }

        $ipcolumn = ($action_no - 4);
        $page_url_column = ($action_no - 2);
        $referrer_url_column = ($action_no - 1);

        if ($exclude_array == "" and ! $is_colmn_array) {
            $exclude_array = array($ipcolumn, $page_url_column, $referrer_url_column);
        } else if (is_array($exclude_array) and ! $is_colmn_array) {

            if (!in_array($ipcolumn, $exclude_array)) {
                array_push($exclude_array, $ipcolumn);
            }
            if (!in_array($page_url_column, $exclude_array)) {
                array_push($exclude_array, $page_url_column);
            }
            if (!in_array($referrer_url_column, $exclude_array)) {
                array_push($exclude_array, $referrer_url_column);
            }
        }

        if ($exclude_array != "") {
            $exclude = implode(",", $exclude_array);
        }

        if ($wp_format_date == 'F j, Y' || $wp_format_date == 'm/d/Y') {
            $date_format_new = 'MM/DD/YYYY';
            $date_format_new1 = 'MM-DD-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '12/31/2050';
        } else if ($wp_format_date == 'd/m/Y') {
            $date_format_new = 'DD/MM/YYYY';
            $date_format_new1 = 'DD-MM-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '31/12/2050';
        } else if ($wp_format_date == 'Y/m/d') {
            $date_format_new = 'DD/MM/YYYY';
            $date_format_new1 = 'DD-MM-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '31/12/2050';
        } else {
            $date_format_new = 'MM/DD/YYYY';
            $date_format_new1 = 'MM-DD-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '12/31/2050';
        }

        $data = array();

        if (count($items) > 0) {
            $ai = 0;
            $arf_edit_select_array = array();
            foreach ($items as $key => $item) {
                if( is_rtl() ){
                    $divStyle = "display:inline-block;position:relative;";
                } else {
                    $divStyle = "position:relative;width:100%;text-align:center;";
                }
                $data[$ai][0] = "<div class='DataTables_sort_wrapper'><div style='{$divStyle}'>
                       <div class='arf_custom_checkbox_div arfmarginl15'><div class='arf_custom_checkbox_wrapper'><input id='cb-item-action-{$item->id}' class='' type='checkbox' value='{$item->id}' name='item-action[]' />
                                        <svg width='18px' height='18px'>
                                        ".ARF_CUSTOM_UNCHECKED_ICON."
                                        ".ARF_CUSTOM_CHECKED_ICON."
                                        </svg>
                                    </div>
                                </div>
                    <label for='cb-item-action-{$item->id}'><span></span></label></div></div>" ;
                $data[$ai][1] = $item->id;
                $ni = 2;
                foreach ($form_cols as $col) {
                    $field_value = isset($item->metas[$col->id]) ? $item->metas[$col->id] : false;
                    $col->field_options = maybe_unserialize($col->field_options);
                    if ($col->type == 'checkbox' || $col->type == 'radio' || $col->type == 'select') {
                        if (isset($col->field_options['separate_value']) && $col->field_options['separate_value'] == '1') {
                            $option_separate_value = array();
                            foreach ($col->options as $k => $options) {
                                $option_separate_value[] = array('value' => htmlentities($options['value']), 'text' => $options['label']);
                            }
                            $arf_edit_select_array[] = array($col->id => json_encode($option_separate_value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
                        } else {
                            $option_value = '';
                            $option_value = array();
                            if(is_array($col->options))
                            {
                                foreach ($col->options as $k => $options) {
                                    if (is_array($options)) {
                                        $option_value[] = ($options['label']);
                                    } else {
                                        $option_value[] = ($options);
                                    }
                                }
                            }
                            $arf_edit_select_array[] = array($col->id => json_encode($option_value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
                        }
                    }
                    global $arrecordhelper;
                    $data[$ai][$ni] = $arrecordhelper->display_value($field_value, $col, array('type' => $col->type, 'truncate' => true, 'attachment_id' => $item->attachment_id, 'entry_id' => $item->id),$form_css);
                    $ni++;
                }
                $data[$ai][$ni] = $item->entry_key;
                $data[$ai][$ni + 1] = date(get_option('date_format'), strtotime($item->created_date));
                $browser_info = $this->getBrowser($item->browser_info);
                $data[$ai][$ni + 2] = $browser_info['name'] . ' (Version: ' . $browser_info['version'] . ')';
                $data[$ai][$ni + 3] = $item->ip_address;
                $data[$ai][$ni + 4] = $item->country;
                $http_referrer = maybe_unserialize($item->description);
                $data[$ai][$ni + 5] = $http_referrer['page_url'];
                $data[$ai][$ni + 6] = $http_referrer['http_referrer'];

                $view_entry_icon = is_rtl() ? 'view_icon23_rtl.png' : 'view_icon23.png';
                $view_entry_icon_hover = is_rtl() ? 'view_icon23_hover_rtl.png' : 'view_icon23_hover.png';

                $view_entry_btn = "<div class='arfformicondiv arfhelptip' title='" . addslashes(esc_html__('Preview', 'ARForms')) . "'><a href='javascript:void(0);'  onclick='open_entry_thickbox({$item->id},\"{$form->name}\");'><svg width='30px' height='30px' viewBox='-3 -8 32 32' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#ffffff' d='M12.993,15.23c-7.191,0-11.504-7.234-11.504-7.234  S5.801,0.85,12.993,0.85c7.189,0,11.504,7.19,11.504,7.19S20.182,15.23,12.993,15.23z M12.993,2.827  c-5.703,0-8.799,5.214-8.799,5.214s3.096,5.213,8.799,5.213c5.701,0,8.797-5.213,8.797-5.213S18.694,2.827,12.993,2.827z   M12.993,11.572c-1.951,0-3.531-1.581-3.531-3.531s1.58-3.531,3.531-3.531c1.949,0,3.531,1.581,3.531,3.531  S14.942,11.572,12.993,11.572z'/></svg></a></div>";

                global $PDF_button;
		do_action('arf_additional_action_entries', $item->id, $form->id,true);
		global $PDF_button;
                $delete_link = "?page=ARForms-entries&arfaction=destroy&id={$item->id}";
                $delete_link .= "&form=" . $params['form'];
                $id = $item->id;

                $delete_entry_icon = is_rtl() ? 'delete_icon223_rtl.png' : 'delete_icon223.png';
                $delete_entry_icon_hover = is_rtl() ? 'delete_icon223_hover_rtl.png' : 'delete_icon223_hover.png';

                $delete_entry_btn = "<div class='arfformicondiv arfhelptip arfentry_delete_div_".$item->id."' title='" . addslashes(esc_html__('Delete', 'ARForms')) . "'><a data-id='".$item->id."' id='arf_delete_single_entry' style='cursor:pointer'><svg width='30px' height='30px' viewBox='-5 -5 32 32' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#ffffff' d='M18.435,4.857L18.413,19.87L3.398,19.88L3.394,4.857H1.489V2.929  h1.601h3.394V0.85h8.921v2.079h3.336h1.601l0,0v1.928H18.435z M15.231,4.857H6.597H5.425l0.012,13.018h10.945l0.005-13.018H15.231z   M11.4,6.845h2.029v9.065H11.4V6.845z M8.399,6.845h2.03v9.065h-2.03V6.845z' /></svg></a></div>";

                

                $delete_entry_overlay = "<div id='view_entry_detail_container_{$item->id}' style='display:none;'>" . $this->get_entries_list_edit($item->id) . "</div><div style='clear:both;' class='arfmnarginbtm10'></div>";

                $data[$ai][$ni + 7] = "<div class='arf-row-actions'>{$view_entry_btn}{$PDF_button}{$delete_entry_btn} {$delete_entry_overlay} <input type='hidden' id='arf_edit_select_array_one' value='" . json_encode($arf_edit_select_array) . "' /></div>";
                $PDF_button = '';
                $action_no = $ni + 7;
                $ai++;
            }
        }

        $sEcho = isset($_REQUEST['sEcho']) ? intval($_REQUEST['sEcho']) : intval(10);
        $response = array(
            'sColumns' => implode('||', $default_hide),
            'sEcho' => $sEcho,
            'iTotalRecords' => count($items),
            'iTotalDisplayRecords' => count($items),
            'aaData' => $data,
            'action_no' => $action_no,
            'exclude' => $exclude_array
        );

        echo json_encode($response);
        die();

    }

    function frm_change_entries($new_form_id = '', $new_start_date = '', $new_end_date = '', $bulk = '', $message = '', $errors = '') {

        global $wpdb, $MdlDb, $armainhelper, $arfform, $db_record, $arfrecordmeta, $arfpagesize, $arffield, $arfcurrentform, $arformhelper, $arrecordcontroller, $arfversion;

        if (isset($bulk) && $bulk == '1') {
            $new_form_id = $new_form_id;
            $new_start_date = $new_start_date;
            $new_end_date = $new_end_date;
        } else {
            $new_form_id = $_POST['form'];
            $new_start_date = $_POST['start_date'];
            $new_end_date = $_POST['end_date'];
        }

        if (!isset($new_form_id) && $new_form_id == '')
            $new_form_id == '-1';


        if (empty($params) || !$params)
            $params = $this->get_params();



        $params['form'] = $new_form_id;


        $form_select = $arfform->getAll("is_template=0 AND (status is NULL OR status = '' OR status = 'published')", ' ORDER BY name');



        if ($params['form'])
            $form = $arfform->getOne($params['form']);
        else
            $form = (isset($form_select[0])) ? $form_select[0] : 0;




        if ($form) {


            $params['form'] = $form->id;


            $arfcurrentform = $form;


            $where_clause = " it.form_id=$form->id";
        } else {


            $where_clause = '';
        }




        $page_params = "&action=0&arfaction=0&form=";


        $page_params .= ($form) ? $form->id : 0;



        if (!empty($_REQUEST['fid']))
            $page_params .= '&fid=' . $_REQUEST['fid'];



        $item_vars = $this->get_sort_vars($params, $where_clause);


        $page_params .= ( isset($page_params_ov) ) ? $page_params_ov : $item_vars['page_params'];



        if ($form) {


            $form_cols = $arffield->getAll("fi.type not in ('divider', 'captcha', 'break', 'html', 'imagecontrol') and fi.form_id=" . (int) $form->id, ' ORDER BY id');

            $record_where = ($item_vars['where_clause'] == " it.form_id=$form->id") ? $form->id : $item_vars['where_clause'];
        } else {


            $form_cols = array();


            $record_where = $item_vars['where_clause'];
        }


        $current_page = ( isset($current_page_ov) ) ? $current_page_ov : $params['paged'];

        $sort_str = $item_vars['sort_str'];


        $sdir_str = $item_vars['sdir_str'];


        $search_str = $item_vars['search_str'];

        $fid = $item_vars['fid'];

        $record_count = $db_record->getRecordCount($record_where);

        $page_count = $db_record->getPageCount($arfpagesize, $record_count);

        wp_enqueue_style('bootstrap-editable-css', ARFURL . '/bootstrap/css/bootstrap-editable.css', array(), $arfversion);
        wp_enqueue_script('bootstrap-editable-js', ARFURL . '/bootstrap/js/bootstrap-editable.js', array(), $arfversion);

        global $style_settings, $wp_scripts;
        $wp_format_date = get_option('date_format');

        if ($wp_format_date == 'F j, Y' || $wp_format_date == 'm/d/Y') {
            $date_format_new = 'mm/dd/yy';
        } else if ($wp_format_date == 'd/m/Y') {
            $date_format_new = 'dd/mm/yy';
        } else if ($wp_format_date == 'Y/m/d') {
            $date_format_new = 'dd/mm/yy';
        } else {
            $date_format_new = 'mm/dd/yy';
        }

        $show_new_start_date = $new_start_date;
        $show_new_end_date = $new_end_date;

        if ($new_start_date != '' and $new_end_date != '') {
            if ($date_format_new == 'dd/mm/yy') {
                $new_start_date = str_replace('/', '-', $new_start_date);
                $new_end_date = str_replace('/', '-', $new_end_date);
            }
            $new_start_date_var = date('Y-m-d', strtotime($new_start_date));

            $new_end_date_var = date('Y-m-d', strtotime($new_end_date));

            $item_vars['where_clause'] .= " and DATE(it.created_date) >= '" . $new_start_date_var . "' and DATE(it.created_date) <= '" . $new_end_date_var . "'";
        } else if ($new_start_date != '' and $new_end_date == '') {
            if ($date_format_new == 'dd/mm/yy') {
                $new_start_date = str_replace('/', '-', $new_start_date);
            }
            $new_start_date_var = date('Y-m-d', strtotime($new_start_date));

            $item_vars['where_clause'] .= " and DATE(it.created_date) >= '" . $new_start_date_var . "'";
        } else if ($new_start_date == '' and $new_end_date != '') {
            if ($date_format_new == 'dd/mm/yy') {
                $new_end_date = str_replace('/', '-', $new_end_date);
            }
            $new_end_date_var = date('Y-m-d', strtotime($new_end_date));

            $item_vars['where_clause'] .= " and DATE(it.created_date) <= '" . $new_end_date_var . "'";
        }




        $items = $db_record->getPage('', '', $item_vars['where_clause'], $item_vars['order_by']);

        $page_last_record = $armainhelper->getLastRecordNum($record_count, $current_page, $arfpagesize);

        $page_first_record = $armainhelper->getFirstRecordNum($record_count, $current_page, $arfpagesize);

        if ((isset($new_form_id) && $new_form_id == '-1') || ( empty($new_form_id) || empty($form->id) )) {
            $form_cols = array();
            $items = array();
        }

        if ($form->id != '-1' || $form->id != '') {

            $form_cols = apply_filters('arfpredisplayformcols', $form_cols, $form->id);
            $items = apply_filters('arfpredisplaycolsitems', $items, $form->id);

            $action_no = 0;

            $default_hide = array(
                '0' => '',
                '1' => 'ID',
            );
            if (count($form_cols) > 0) {

                for ($i = 2; 1 + count($form_cols) >= $i; $i++) {
                    $j = $i - 2;
                    $default_hide[$i] = $armainhelper->truncate($form_cols[$j]->name, 40);
                }
                $default_hide[$i] = 'Entry Key';
                $default_hide[$i + 1] = 'Entry creation date';
                $default_hide[$i + 2] = 'Browser Name';
                $default_hide[$i + 3] = 'IP Address';
                $default_hide[$i + 4] = 'Country';
                $default_hide[$i + 5] = 'Page URL';
                $default_hide[$i + 6] = 'Referrer URL';
                $default_hide[$i + 7] = 'Action';
                $action_no = $i + 7;
            } else {
                $default_hide['2'] = 'Entry Key';
                $default_hide['3'] = 'Entry creation date';
                $default_hide['4'] = 'Browser Name';
                $default_hide['5'] = 'IP Address';
                $default_hide['6'] = 'Country';
                $default_hide['7'] = 'Page URL';
                $default_hide['8'] = 'Referrer URL';
                $default_hide['9'] = 'Action';
                $action_no = 9;
            }


            $columns_list_res = $wpdb->get_results($wpdb->prepare('SELECT columns_list FROM ' . $MdlDb->forms . ' WHERE id = %d', $form->id), ARRAY_A);
            $columns_list_res = $columns_list_res[0];

            $columns_list = maybe_unserialize($columns_list_res['columns_list']);
            $is_colmn_array = is_array($columns_list);

            $exclude = '';

            $exclude_array = "";
            if (count($columns_list) > 0 and $columns_list != '') {

                foreach ($columns_list as $keys => $column) {

                    foreach ($default_hide as $key => $val) {

                        if ($column == $val) {
                            if ($exclude_array == "") {
                                $exclude_array[] = $key;
                            } else {
                                if (!in_array($key, $exclude_array)) {
                                    $exclude_array[] = $key;

                                    $exclude_no++;
                                }
                            }
                        }
                    }
                }
            }

            $ipcolumn = ($action_no - 4);
            $page_url_column = ($action_no - 2);
            $referrer_url_column = ($action_no - 1);

            if ($exclude_array == "" and ! $is_colmn_array) {
                $exclude_array = array($ipcolumn, $page_url_column, $referrer_url_column);
            } else if (is_array($exclude_array) and ! $is_colmn_array) {
                if (!in_array($ipcolumn, $exclude_array)) {
                    array_push($exclude_array, $ipcolumn);
                }
                if (!in_array($page_url_column, $exclude_array)) {
                    array_push($exclude_array, $page_url_column);
                }
                if (!in_array($referrer_url_column, $exclude_array)) {
                    array_push($exclude_array, $referrer_url_column);
                }
            }
        }

        if ($exclude_array != "") {
            $exclude = implode(",", $exclude_array);
        }

        $actions = array('bulk_delete' => addslashes(esc_html__('Delete', 'ARForms')));

        $actions['bulk_csv'] = addslashes(esc_html__('Export to CSV', 'ARForms'));

        global $style_settings, $wp_scripts;
        $wp_format_date = get_option('date_format');

        if ($wp_format_date == 'F j, Y' || $wp_format_date == 'm/d/Y') {
            $date_format_new = 'MM/DD/YYYY';
            $date_format_new1 = 'MM-DD-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '12/31/2050';
        } else if ($wp_format_date == 'd/m/Y') {
            $date_format_new = 'DD/MM/YYYY';
            $date_format_new1 = 'DD-MM-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '31/12/2050';
        } else if ($wp_format_date == 'Y/m/d') {
            $date_format_new = 'DD/MM/YYYY';
            $date_format_new1 = 'DD-MM-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '31/12/2050';
        } else {
            $date_format_new = 'MM/DD/YYYY';
            $date_format_new1 = 'MM-DD-YYYY';
            $start_date_new = '01/01/1970';
            $end_date_new = '12/31/2050';
        }

        global $arf_entries_action_column_width;
        ?>
        <script type="text/javascript" data-cfasync="false" charset="utf-8">
            jQuery(document).ready(function () {
                jQuery("#datepicker_from").datetimepicker({
                    useCurrent: false,
                    format: '<?php echo $date_format_new; ?>',
                    locale: '<?php echo (isset($options['locale'])) ? $options['locale'] : ''; ?>',
                    minDate: moment('<?php echo $start_date_new; ?>', '<?php echo $date_format_new1; ?>'),
                    maxDate: moment('<?php echo $end_date_new; ?>', '<?php echo $date_format_new1; ?>')
                });

                jQuery("#datepicker_to").datetimepicker({
                    useCurrent: false,
                    format: '<?php echo $date_format_new; ?>',
                    locale: '<?php echo (isset($options['locale'])) ? $options['locale'] : ''; ?>',
                    minDate: moment('<?php echo $start_date_new; ?>', '<?php echo $date_format_new1; ?>'),
                    maxDate: moment('<?php echo $end_date_new; ?>', '<?php echo $date_format_new1; ?>')
                });

                jQuery.fn.dataTableExt.oPagination.four_button = {
                    "fnInit": function (oSettings, nPaging, fnCallbackDraw)
                    {
                        nFirst = document.createElement('span');
                        nPrevious = document.createElement('span');


                        var nInput = document.createElement('input');
                        var nPage = document.createElement('span');
                        var nOf = document.createElement('span');
                        nOf.className = "paginate_of";
                        nInput.className = "current_page_no";
                        nPage.className = "paginate_page";
                        nInput.type = "text";
                        nInput.style.width = "40px";
                        nInput.style.height = "26px";
                        nInput.style.display = "inline";


                        nPaging.appendChild(nPage);


                        jQuery(nInput).keyup(function (e) {

                            if (e.which == 38 || e.which == 39)
                            {
                                this.value++;
                            }
                            else if ((e.which == 37 || e.which == 40) && this.value > 1)
                            {
                                this.value--;
                            }

                            if (this.value == "" || this.value.match(/[^0-9]/))
                            {

                                return;
                            }

                            var iNewStart = oSettings._iDisplayLength * (this.value - 1);
                            if (iNewStart > oSettings.fnRecordsDisplay())
                            {

                                oSettings._iDisplayStart = (Math.ceil((oSettings.fnRecordsDisplay() - 1) /
                                        oSettings._iDisplayLength) - 1) * oSettings._iDisplayLength;
                                fnCallbackDraw(oSettings);
                                return;
                            }

                            oSettings._iDisplayStart = iNewStart;
                            fnCallbackDraw(oSettings);
                        });



                        nNext = document.createElement('span');
                        nLast = document.createElement('span');
                        var nFirst = document.createElement('span');
                        var nPrevious = document.createElement('span');
                        var nPage = document.createElement('span');
                        var nOf = document.createElement('span');

                        nNext.style.backgroundImage = "url('<?php echo ARFURL; ?>/images/next_normal-icon.png')";
                        nNext.style.backgroundRepeat = "no-repeat";
                        nNext.style.backgroundPosition = "center";
                        nNext.title = "Next";

                        nLast.style.backgroundImage = "url('<?php echo ARFURL; ?>/images/last_normal-icon.png')";
                        nLast.style.backgroundRepeat = "no-repeat";
                        nLast.style.backgroundPosition = "center";
                        nLast.title = "Last";

                        nFirst.style.backgroundImage = "url('<?php echo ARFURL; ?>/images/first_normal-icon.png')";
                        nFirst.style.backgroundRepeat = "no-repeat";
                        nFirst.style.backgroundPosition = "center";
                        nFirst.title = "First";

                        nPrevious.style.backgroundImage = "url('<?php echo ARFURL; ?>/images/previous_normal-icon.png')";
                        nPrevious.style.backgroundRepeat = "no-repeat";
                        nPrevious.style.backgroundPosition = "center";
                        nPrevious.title = "Previous";


                        nFirst.appendChild(document.createTextNode(' '));
                        nPrevious.appendChild(document.createTextNode(' '));
                        nNext.appendChild(document.createTextNode(' '));
                        nLast.appendChild(document.createTextNode(' '));


                        nOf.className = "paginate_button nof";

                        nPaging.appendChild(nFirst);
                        nPaging.appendChild(nPrevious);

                        nPaging.appendChild(nInput);
                        nPaging.appendChild(nOf);

                        nPaging.appendChild(nNext);
                        nPaging.appendChild(nLast);

                        jQuery(nFirst).click(function () {
                            oSettings.oApi._fnPageChange(oSettings, "first");
                            fnCallbackDraw(oSettings);
                        });

                        jQuery(nPrevious).click(function () {
                            oSettings.oApi._fnPageChange(oSettings, "previous");
                            fnCallbackDraw(oSettings);
                        });

                        jQuery(nNext).click(function () {
                            oSettings.oApi._fnPageChange(oSettings, "next");
                            fnCallbackDraw(oSettings);
                        });

                        jQuery(nLast).click(function () {
                            oSettings.oApi._fnPageChange(oSettings, "last");
                            fnCallbackDraw(oSettings);
                        });


                        jQuery(nFirst).bind('selectstart', function () {
                            return false;
                        });
                        jQuery(nPrevious).bind('selectstart', function () {
                            return false;
                        });
                        jQuery('span', nPaging).bind('mousedown', function () {
                            return false;
                        });
                        jQuery('span', nPaging).bind('selectstart', function () {
                            return false;
                        });
                        jQuery(nNext).bind('selectstart', function () {
                            return false;
                        });
                        jQuery(nLast).bind('selectstart', function () {
                            return false;
                        });
                    },
                    "fnUpdate": function (oSettings, fnCallbackDraw)
                    {
                        if (!oSettings.aanFeatures.p)
                        {
                            return;
                        }


                        var an = oSettings.aanFeatures.p;
                        for (var i = 0, iLen = an.length; i < iLen; i++)
                        {
                            var buttons = an[i].getElementsByTagName('span');
                            if (oSettings._iDisplayStart === 0)
                            {
                                buttons[1].className = "paginate_disabled_first arfhelptip";
                                buttons[2].className = "paginate_disabled_previous arfhelptip";
                            }
                            else
                            {
                                buttons[1].className = "paginate_enabled_first arfhelptip";
                                buttons[2].className = "paginate_enabled_previous arfhelptip";
                            }

                            if (oSettings.fnDisplayEnd() == oSettings.fnRecordsDisplay())
                            {
                                buttons[4].className = "paginate_disabled_next arfhelptip";
                                buttons[5].className = "paginate_disabled_last arfhelptip";
                            }
                            else
                            {

                                buttons[4].className = "paginate_enabled_next arfhelptip";
                                buttons[5].className = "paginate_enabled_last arfhelptip";
                            }


                            if (!oSettings.aanFeatures.p)
                            {
                                return;
                            }
                            var iPages = Math.ceil((oSettings.fnRecordsDisplay()) / oSettings._iDisplayLength);

                            var iCurrentPage = Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength) + 1;

                            if (iPages == 0 && iCurrentPage == 1)
                                iPages = iPages + 1;

                            var an = oSettings.aanFeatures.p;
                            for (var i = 0, iLen = an.length; i < iLen; i++)
                            {
                                var spans = an[i].getElementsByTagName('span');
                                var inputs = an[i].getElementsByTagName('input');
                                spans[spans.length - 3].innerHTML = " of " + iPages
                                inputs[0].value = iCurrentPage;
                            }


                        }
                    }
                }

                var oTables = jQuery('#example').dataTable({
                    "sDom": '<"H"lCfr>t<"footer"ip>',
                    "sPaginationType": "four_button",
                    "bJQueryUI": true,
                    "bPaginate": true,
                    "bAutoWidth": false,
                    "sScrollX": "100%",
                    "bScrollCollapse": true,
                    "oColVis": {
                        "aiExclude": [0, <?php echo $action_no; ?>]
                    },
                    "aoColumnDefs": [
                        {"sType": "html", "bVisible": false, "aTargets": [<?php if ($exclude != '') echo $exclude; ?>]},
                        {"bSortable": false, "aTargets": [0, <?php echo $action_no; ?>]}
                    ],
                });
                new FixedColumns(oTables, {
                    "iLeftColumns": 0,
                    "iLeftWidth": 0,
                    "iRightColumns": 1,
                    "iRightWidth": <?php echo isset($arf_entries_action_column_width) ? $arf_entries_action_column_width : '120'; ?>,
                });
            });

            jQuery(document).ready(function () {
                jQuery("#cb-select-all-1").click(function () {
                    jQuery('input[name="item-action[]"]').attr('checked', this.checked);
                });

                jQuery('input[name="item-action[]"]').click(function () {

                    if (jQuery('input[name="item-action[]"]').length == jQuery('input[name="item-action[]"]:checked').length) {
                        jQuery("#cb-select-all-1").attr("checked", "checked");
                    } else {
                        jQuery("#cb-select-all-1").removeAttr("checked");
                    }

                });

            });

        </script>

        <?php
        if (is_rtl()) {
            $sel_frm_div = 'float:right;margin-top:15px;';
            $sel_frm_txt = 'float:right;text-align:right;width:27%;';
        } else {
            $sel_frm_div = 'float:left;margin-top:15px;';
            $sel_frm_txt = 'float:left;text-align:left;width:27%;';
        }
        ?>
        <div class="arf_form_entry_select">
            <div class="arf_form_entry_select_sub">	
                <div>
                    <div class="arf_form_entry_left"><?php echo addslashes(esc_html__('Select form', 'ARForms')); ?>:</div>
                    <div style=" <?php echo $sel_frm_txt; ?>" ><div class="sltstandard" style="float:none;"><?php $arformhelper->forms_dropdown('arfredirecttolist', $new_form_id, addslashes(esc_html__('Select Form', 'ARForms')), false, ""); ?></div></div>
                </div>
        <?php
        if (is_rtl()) {
            $sel_frm_date_wrap = 'float:right;text-align:right;width:65%';
            $sel_frm_sel_date = 'float:right;';
            $sel_frm_button = 'float:right;margin-top:15px;';
        } else {
            $sel_frm_date_wrap = 'float:left;text-align:left;width:65%';
            $sel_frm_sel_date = 'float:left;';
            $sel_frm_button = 'float:left;margin-top:15px;';
        }
        ?>
                <div style=" <?php echo $sel_frm_div ?>">
                    <div class="arf_form_entry_left"><div><?php echo addslashes(esc_html__('Select date From', 'ARForms')); ?>:</div><div class="arf_form_entry_left_sub">(<?php echo addslashes(esc_html__('optional', 'ARForms')); ?>)</div></div>
                    <div style=" <?php echo $sel_frm_date_wrap; ?>">
                        <div style=" <?php echo $sel_frm_sel_date; ?>"><input type="text" class="txtmodal1" id="datepicker_from" value="<?php echo $show_new_start_date; ?>" name="datepicker_from" style="vertical-align:middle; width:130px;" /></div> <div class="arfentrytitle"><?php echo addslashes(esc_html__('To', 'ARForms')); ?>:</div>&nbsp;&nbsp;<div style="float:left;"><input type="text" class="txtmodal1" id="datepicker_to" name="datepicker_to"  value="<?php echo $show_new_end_date; ?>" style="vertical-align:middle;  width:130px;"/></div>

                    </div>

                    <div style=" <?php echo $sel_frm_button; ?>">
                        <div class="arf_form_entry_left">&nbsp;</div>
                        <div style="float:left;text-align:left;"><button type="button" class="rounded_button btn_green" onclick="change_frm_entries();"><?php echo addslashes(esc_html__('Submit', 'ARForms')); ?></button></div>
                    </div>        

                    <input type="hidden" name="please_select_form" id="please_select_form" value="<?php echo addslashes(esc_html__('Please select a form', 'ARForms')); ?>" />
                </div>
                <div style="clear:both;"></div>
            </div>    
        </div>
        <div style="clear:both; height:30px;"></div>


        <?php do_action('arfbeforelistingentries'); ?>

        <div class="arf_loder_entries_section" id="arf_loder_entrie_div">
            <img src="<?php echo ARFIMAGESURL; ?>/ajax_loader_gray_64.gif" />
        </div>  

        <form method="get" id="list_entry_form" class="arf_list_entries_form" onsubmit="return apply_bulk_action();" style="float:left;width:100%;">

            <input type="hidden" name="page" value="ARForms-entries" />

            <input type="hidden" name="form" value="<?php echo ($form) ? $form->id : '-1'; ?>" />

            <input type="hidden" name="arfaction" value="list" />

            <input type="hidden" name="show_hide_columns" id="show_hide_columns" value="<?php echo addslashes(esc_html__('Show / Hide columns', 'ARForms')); ?>"/>
            <input type="hidden" name="search_grid" id="search_grid" value="<?php echo addslashes(esc_html__('Search', 'ARForms')); ?>"/>
            <input type="hidden" name="entries_grid" id="entries_grid" value="<?php echo addslashes(esc_html__('entries', 'ARForms')); ?>"/>
            <input type="hidden" name="show_grid" id="show_grid" value="<?php echo addslashes(esc_html__('Show', 'ARForms')); ?>"/>
            <input type="hidden" name="showing_grid" id="showing_grid" value="<?php echo addslashes(esc_html__('Showing', 'ARForms')); ?>"/>
            <input type="hidden" name="to_grid" id="to_grid" value="<?php echo addslashes(esc_html__('to', 'ARForms')); ?>"/>
            <input type="hidden" name="of_grid" id="of_grid" value="<?php echo addslashes(esc_html__('of', 'ARForms')); ?>"/>
            <input type="hidden" name="no_match_record_grid" id="no_match_record_grid" value="<?php echo addslashes(esc_html__('No matching records found', 'ARForms')); ?>"/>
            <input type="hidden" name="no_record_grid" id="no_record_grid" value="<?php echo addslashes(esc_html__('No data available in table', 'ARForms')); ?>"/>
            <input type="hidden" name="filter_grid" id="filter_grid" value="<?php echo addslashes(esc_html__('filtered from', 'ARForms')); ?>"/>
            <input type="hidden" name="totalwd_grid" id="totalwd_grid" value="<?php echo addslashes(esc_html__('total', 'ARForms')); ?>"/>

        <?php require(VIEWS_PATH . '/shared_errors.php'); ?>  

            <?php $two = '1'; ?>
            <div class="alignleft actions">
                <div class="arf_list_bulk_action_wrapper">
                    <input id="arf_bulk_action_one" name="action<?php echo $two; ?>" value="-1" type="hidden">
                    <dl class="arf_selectbox" data-name="action<?php echo $two; ?>" data-id="arf_bulk_action_one">
                        <dt style="width:130px;"><span><?php echo addslashes(esc_html__('Bulk Actions', 'ARForms')); ?></span>
                        <svg viewBox="0 0 2000 1000" width="15px" height="15px">
                        <g fill="#000">
                        <path d="M1024 320q0 -26 -19 -45t-45 -19h-896q-26 0 -45 19t-19 45t19 45l448 448q19 19 45 19t45 -19l448 -448q19 -19 19 -45z"/>
                        </g>
                        </svg>
                        </dt>
                        <dd>
                            <ul style="display: none;" data-id="arf_bulk_action_one">
                                <li data-value='-1' data-label='<?php echo addslashes(esc_html__('Bulk Actions', 'ARForms')); ?>'><?php echo addslashes(esc_html__('Bulk Actions', 'ARForms')); ?></li>
        <?php
        foreach ($actions as $name => $title) {
            $class = 'edit' == $name ? ' class="hide-if-no-js" ' : '';
            ?>
                                    <li <?php echo $class; ?> data-value='<?php echo $name; ?>' data-label='<?Php echo $title; ?>'><?php echo $title; ?></li>
                                <?php } ?>
                            </ul>
                        </dd>
                    </dl>
                </div>
                <input type="submit" id="doaction<?php echo $two; ?>" class="rounded_button btn_blue" value="<?php echo addslashes(esc_html__('Apply', 'ARForms')) ?>" style='margin-top:-2px;' />
            </div>


            <table cellpadding="0" cellspacing="0" border="0" class="display" id="example">
                <thead>
                    <tr>
                        <th class="box" style="text-align:center"><div style="position:relative;">
                    <div class="arf_custom_checkbox_div">
                        <div class="arf_custom_checkbox_wrapper arfmargin10custom">
                            <input id="cb-select-all-1" type="checkbox" class="">
                            <svg width="18px" height="18px">
        <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                            <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                            </svg>
                        </div>
                    </div>
                    <label for="cb-select-all-1"  class="cb-select-all"><span></span></label></div>
                </th>
                <th><?php echo addslashes(esc_html__('ID', 'ARForms')); ?></th>
        <?php
        if (count($form_cols) > 0) {
            foreach ($form_cols as $col) {
                ?>
                        <th><?php echo $armainhelper->truncate($col->name, 40) ?></th>
                        <?php
                    }
                }
                ?>
                <th><?php echo addslashes(esc_html__('Entry Key', 'ARForms')); ?></th>
                <th><?php echo addslashes(esc_html__('Entry creation date', 'ARForms')); ?></th>
                <th><?php echo addslashes(esc_html__('Browser Name', 'ARForms')); ?></th>
                <th><?php echo addslashes(esc_html__('IP Address', 'ARForms')); ?></th>
                <th><?php echo addslashes(esc_html__('Country', 'ARForms')); ?></th>
                <th><?php echo esc_html__('Page URL', 'ARForms'); ?></th>
                <th><?php echo addslashes(esc_html__('Referrer URL', 'ARForms')); ?></th>
                <th class="arf_col_action"><?php echo addslashes(esc_html__('Action', 'ARForms')); ?></th>
                </tr>
                </thead>
                <tbody>
                    <?php
                    $arf_edit_select_array = array();
                    if (count($items) > 0) {
                        $arf_edit_select_array = array();
                        foreach ($items as $key => $item) {
                            ?>
                            <tr>
                                <td class="center">
                                    <div class="arf_custom_checkbox_div">
                                        <div class="arf_custom_checkbox_wrapper arfmarginl15">
                                            <input id="cb-item-action-<?php echo $item->id; ?>" class="" type="checkbox" value="<?php echo $item->id; ?>" name="item-action[]">
                                            <svg width="18px" height="18px">
                                            <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                            <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                            </svg>
                                        </div>
                                    </div>
                                    <label for="cb-item-action-<?php echo $item->id; ?>"><span></span></label></td>
                                <td><?php echo $item->id; ?></td>
                                <?php foreach ($form_cols as $col) { ?>

                                    <td>
                                        <?php
                                        $field_value = isset($item->metas[$col->id]) ? $item->metas[$col->id] : false;


                                        $col->field_options = maybe_unserialize($col->field_options);

                                        if ($col->type == 'checkbox' || $col->type == 'radio' || $col->type == 'select') {
                                            if (isset($col->field_options['separate_value']) && $col->field_options['separate_value'] == '1') {
                                                $option_separate_value = array();
                                                foreach ($col->options as $k => $options) {
                                                    $option_separate_value[] = array('value' => htmlentities($options['value']), 'text' => $options['label']);
                                                }
                                                $arf_edit_select_array[] = array($col->id => json_encode($option_separate_value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP ));
                                            } else {
                                                $option_value = '';
                                                $option_value = array();
                                                foreach ($col->options as $k => $options) {
                                                    if (is_array($options)) {
                                                        $option_value[] = ($options['label']);
                                                    } else {
                                                        $option_value[] = ($options);
                                                    }
                                                }
                                                $arf_edit_select_array[] = array($col->id => json_encode($option_value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP ));
                                            }
                                        }


                                        global $arrecordhelper;
                                        echo $arrecordhelper->display_value($field_value, $col, array('type' => $col->type, 'truncate' => true, 'attachment_id' => $item->attachment_id, 'entry_id' => $item->id));
                                        ?>

                                    </td>

                                <?php } ?>
                                <td><?php echo $item->entry_key; ?></td>
                                <td><?php echo date(get_option('date_format'), strtotime($item->created_date)); ?></td>
                                <td><?php
                                    $browser_info = $this->getBrowser($item->browser_info);
                                    echo $browser_info['name'] . ' (Version: ' . $browser_info['version'] . ')';
                                    ?></td>
                                <td><?php echo $item->ip_address; ?></td>
                                <td><?php echo $item->country; ?></td>
                                <?php $http_referrer = maybe_unserialize($item->description); ?>
                                <td><?php echo $http_referrer['page_url']; ?></td>
                                <td><?php echo $http_referrer['http_referrer']; ?></td>
                                <td class="arf_col_action">			
                                    <div class="arf-row-actions">  


                                        <?php
                                        if (is_rtl()) {
                                            echo "<a href='javascript:void(0);' onclick='open_entry_thickbox({$item->id});'><img src='" . ARFIMAGESURL . "/view_icon23_rtl.png' title='" . addslashes(esc_html__("View Entry", "ARForms")) . "' class='arfhelptip' onmouseover=\"this.src='" . ARFIMAGESURL . "/view_icon23_hover_rtl.png';\" onmouseout=\"this.src='" . ARFIMAGESURL . "/view_icon23_rtl.png';\" /></a>";
                                        } else {
                                            echo "<a href='javascript:void(0);' onclick='open_entry_thickbox({$item->id});'><img src='" . ARFIMAGESURL . "/view_icon23.png' title='" . addslashes(esc_html__("View Entry", "ARForms")) . "' class='arfhelptip' onmouseover=\"this.src='" . ARFIMAGESURL . "/view_icon23_hover.png';\" onmouseout=\"this.src='" . ARFIMAGESURL . "/view_icon23.png';\" /></a>";
                                        }

                                        

                                        $delete_link = "?page=ARForms-entries&arfaction=destroy&id={$item->id}";


                                        $delete_link .= "&form=" . $params['form'];


                                        $id = $item->id;

                                        if (is_rtl()) {
                                            echo "<img src='" . ARFIMAGESURL . "/delete_icon223_rtl.png' class='arfhelptip' title=" . addslashes(esc_html__("Delete", "ARForms")) . " onmouseover=\"this.src='" . ARFIMAGESURL . "/delete_icon223_hover_rtl.png';\" onmouseout=\"this.src='" . ARFIMAGESURL . "/delete_icon223_rtl.png';\" onclick=\"ChangeID({$id}); arfchangedeletemodalwidth('arfdeletemodabox');\" data-toggle='arfmodal' href='#delete_form_message' style='cursor:pointer' /></a>";
                                        } else {
                                            echo "<img src='" . ARFIMAGESURL . "/delete_icon223.png' class='arfhelptip' title=" .addslashes(esc_html__("Delete", "ARForms")) . " onmouseover=\"this.src='" . ARFIMAGESURL . "/delete_icon223_hover.png';\" onmouseout=\"this.src='" . ARFIMAGESURL . "/delete_icon223.png';\" onclick=\"ChangeID({$id}); arfchangedeletemodalwidth('arfdeletemodabox');\" data-toggle='arfmodal' href='#delete_form_message' style='cursor:pointer' /></a>";
                                        }

                                        do_action('arf_additional_action_entries', $item->id, $form->id);

                                        echo "<div class='arf_modal_overlay'>
                        <div id='view_entry_{$item->id}' class='arf_popup_container arf_view_entry_modal'>
                        <div class='arf_popup_container_header'>" . esc_html__('View entry', 'ARForms') . "
						  <div class='arfnewmodalclose arf_entry_model_close' data-dismiss='arfmodal'>
                            <svg viewBox='0 -4 32 32'>
                                <g id='email'><path fill-rule='evenodd' clip-rule='evenodd' fill='#333333' d='M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z'></path></g>
                                </svg>
                          </div>
                        </div>
                        <div class='arfentry_modal_content arf_popup_content_container'>" . $arrecordcontroller->get_entries_list_edit($item->id) . "</div>
						<div style='clear:both;' class='arfmnarginbtm10'></div>";
                                        ?>

                                    </div>
                                    </div>


                                    <!-- For Edit entry  -->
                                    <input type="hidden" id="arf_edit_select_array_one" value='<?php echo json_encode($arf_edit_select_array); ?>' />

                                    </div>
                                </td>              
                            </tr>
                            <?php
                        }
                    }
                    ?>

                </tbody>
            </table>

            <?php $two = '2'; ?>
            <div class="alignleft actions">
                <div class="arf_list_bulk_action_wrapper">
                    <input id="arf_bulk_action_two" name="action<?php echo $two; ?>" value="-1" type="hidden">
                    <dl class="arf_selectbox" data-name="action<?php echo $two; ?>" data-id="arf_bulk_action_two">
                        <dt style="width:130px;"><span><?php echo addslashes(esc_html__('Bulk Actions', 'ARForms')); ?></span>
                        <svg viewBox="0 0 2000 1000" width="15px" height="15px">
                        <g fill="#000">
                        <path d="M1024 320q0 -26 -19 -45t-45 -19h-896q-26 0 -45 19t-19 45t19 45l448 448q19 19 45 19t45 -19l448 -448q19 -19 19 -45z"/>
                        </g>
                        </svg>
                        </dt>
                        <dd>
                            <ul style="display: none;" data-id="arf_bulk_action_two">
                                <li data-value='-1' data-label='<?php echo addslashes(esc_html__('Bulk Actions', 'ARForms')); ?>'><?php echo addslashes(esc_html__('Bulk Actions', 'ARForms')); ?></li>
                                <?php
                                foreach ($actions as $name => $title) {
                                    $class = 'edit' == $name ? ' class="hide-if-no-js" ' : '';
                                    ?>
                                    <li <?php echo $class; ?> data-value='<?php echo $name; ?>' data-label='<?Php echo $title; ?>'><?php echo $title; ?></li>
                                <?php } ?>
                            </ul>
                        </dd>
                    </dl>
                </div>
                <input type="submit" id="doaction<?php echo $two; ?>" class="rounded_button btn_blue" value="<?php echo addslashes(esc_html__('Apply', 'ARForms')) ?>" style='margin-top:-2px;' />
            </div>
            <div class="footer_grid"></div> 
        </form>

        <?php do_action('arfafterlistingentries'); ?>

        <div style="clear:both;"></div>
        <br /><br />

        <script type="text/javascript">
            var __ARF_edit_select_array = <?php echo json_encode($arf_edit_select_array); ?>;
            function ChangeID(id)
            {
                document.getElementById('delete_entry_id').value = id;
            }
        </script>
        <?php
        die();
    }

    function get_entries_list($id = '') {

        global $db_record, $arffield, $arfrecordmeta, $user_ID, $armainhelper, $arrecordhelper;

        if (!$id)
            $id = $armainhelper->get_param('id');


        if (!$id)
            $id = $armainhelper->get_param('entry_id');





        $entry = $db_record->getOne($id, true);


        $data = maybe_unserialize($entry->description);


        if (!is_array($data) or ! isset($data['referrer']))
            $data = array('referrer' => $data);



        $fields = $arffield->getAll("fi.type not in ('captcha','html', 'imagecontrol') and fi.form_id=" . (int) $entry->form_id, ' ORDER BY id');

        $fields = apply_filters('arfpredisplayformcols', $fields, $entry->form_id);
        $entry = apply_filters('arfpredisplayonecol', $entry, $entry->form_id);

        $date_format = get_option('date_format');


        $time_format = get_option('time_format');


        $show_comments = true;



        if ($show_comments) {


            $comments = $arfrecordmeta->getAll("entry_id=$id and field_id=0", ' ORDER BY it.created_date ASC');


            $to_emails = apply_filters('arftoemail', array(), $entry, $entry->form_id);
        }



        $var = '<table class="form-table"><tbody>';


        foreach ($fields as $field) {


            if ($field->type == 'divider') {


                $var .= '</tbody></table>
 				   <div class="arfentrydivider">' . stripslashes($field->name) . '</div>
				   <table class="form-table view_enty_table"><tbody>';
            } else if ($field->type == 'break') {

                $var .= '</tbody></table>
										
										<div class="arfpagebreakline"></div>
										
										<table class="form-table"><tbody>';
            } else {

                if (is_rtl()) {
                    $txt_align = 'text-align:right;';
                } else {
                    $txt_align = 'text-align:left;';
                }
                $var .= '<tr class="arfviewentry_row" valign="top">


                            <td class="arfviewentry_left" scope="row"><strong>' . stripslashes($field->name) . ':</strong></td>


                            <td  class="arfviewentry_right" style="' . $txt_align . '">';





                $field_value = isset($entry->metas[$field->id]) ? $entry->metas[$field->id] : false;


                $field->field_options = maybe_unserialize($field->field_options);


                $var .= $display_value = $arrecordhelper->display_value($field_value, $field, array('type' => $field->type, 'attachment_id' => $entry->attachment_id, 'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id));





                if (is_email($display_value) and ! in_array($display_value, $to_emails))
                    $to_emails[] = $display_value;





                $var .= '</td>


                        </tr>';
            }
        }

        $var .= '<tr class="arfviewentry_row"><td class="arfviewentry_left"><strong>' . addslashes(esc_html__('Created at', 'ARForms')) . ':</strong></td><td class="arfviewentry_right">' . $armainhelper->get_formatted_time($entry->created_date, $date_format, $time_format);



        if ($entry->user_id) {
            
        }



        $var .= '</td></tr>';

        $var .= '<tr class="arfviewentry_row"><td class="arfviewentry_left"><strong>' . esc_html__('Page url', 'ARForms') . ':</strong></td><td class="arfviewentry_right">' . $data['page_url'];
        $var .= '</td></tr>';

        $var .= '<tr class="arfviewentry_row"><td class="arfviewentry_left"><strong>' . addslashes(esc_html__('Referrer url', 'ARForms')) . ':</strong></td><td class="arfviewentry_right">' . $data['http_referrer'];
        $var .= '</td></tr>';

        $temp_var = apply_filters('arf_entry_payment_detail', $id);

        $var .= ( $temp_var != $id ) ? $temp_var : '';

        $var = apply_filters('arfafterviewentrydetail', $var, $id);

        $var .= '</tbody></table>';

        return $var;
    }

    function get_entries_list_edit($id = '', $arffieldorder = array()) {

        global $db_record, $arffield, $arfrecordmeta, $user_ID, $armainhelper, $arrecordhelper;

        if (!$id)
            $id = $armainhelper->get_param('id');


        if (!$id)
            $id = $armainhelper->get_param('entry_id');



        $entry = $db_record->getOne($id, true);

        $data = maybe_unserialize($entry->description);

        if (!is_array($data) or ! isset($data['referrer']))
            $data = array('referrer' => $data);

        if( !isset($GLOBALS['get_entries_list_edit'][$entry->form_id])){
            $fields = $arffield->getAll("fi.type not in ('captcha','html', 'imagecontrol') and fi.form_id=" . (int) $entry->form_id);

            $GLOBALS['get_entries_list_edit'][$entry->form_id] = $fields;
        } else {
            $fields = $GLOBALS['get_entries_list_edit'][$entry->form_id];
        }
        
        $fields = apply_filters('arfpredisplayformcols', $fields, $entry->form_id);
        $entry = apply_filters('arfpredisplayonecol', $entry, $entry->form_id);

        $date_format = get_option('date_format');


        $time_format = get_option('time_format');


        $show_comments = true;



        if ($show_comments) {


            $comments = $arfrecordmeta->getAll("entry_id=$id and field_id=0", ' ORDER BY it.created_date ASC');


            $to_emails = apply_filters('arftoemail', array(), $entry, $entry->form_id);
        }



        $var = '<table class="form-table"><tbody>';


        $as_edit_entry_value = array();

        if(count($arffieldorder) > 0){

            $form_fields = array();
            foreach ($arffieldorder as $fieldkey => $fieldorder) {
                foreach ($fields as $fieldordkey => $fieldordval) {
                    if($fieldordval->id == $fieldkey) {
                        $form_fields[] = $fieldordval;
                        unset($fields[$fieldordkey]);
                    }
                }
            }

            if(count($form_fields) > 0) {
                if(count($fields) > 0) {
                    $arfotherfields = $fields;
                    $fields = array_merge($form_fields, $arfotherfields);
                } else {
                    $fields = $form_fields;
                }
            }
        }

        foreach ($fields as $field) {


            if ($field->type == 'divider') {


                $var .= '</tbody></table>


                       	 					<div class="arfentrydivider">' . stripslashes($field->name) . '</div>


                        					<table class="form-table"><tbody>';
            } else if ($field->type == 'break') {

                $var .= '</tbody></table>
										
										<div class="arfpagebreakline"></div>
										
										<table class="form-table"><tbody>';
            } else {

                if (is_rtl()) {
                    $txt_align = 'text-align:right;';
                } else {
                    $txt_align = 'text-align:left;';
                }
                $var .= '<tr class="arfviewentry_row" valign="top">


                            <td class="arfviewentry_left arfwidth25" scope="row">' . stripslashes($field->name) . ':</td>


                            <td  class="arfviewentry_right" style="' . $txt_align . '">';





                $field_value = isset($entry->metas[$field->id]) ? $entry->metas[$field->id] : false;


                $field->field_options = maybe_unserialize($field->field_options);



                if ($field->type == 'checkbox') {
                    $as_edit_entry_value[$field->id] = $field_value;
                }

                if ($field->type == 'radio' || $field->type == 'select') {
                    $as_edit_entry_value[$field->id] = $field_value;
                }

                $var .= $display_value = $arrecordhelper->display_value_with_edit($field_value, $field, array('type' => $field->type, 'attachment_id' => $entry->attachment_id, 'show_filename' => true, 'show_icon' => true, 'entry_id' => $entry->id));

                $var .= '<input type="hidden" name="arf_edit_form_field_values_'.$entry->id.'[]" id="arf_edit_new_values_'.$field->id.'_'.$entry->id.'" value="" data-id="' . $field->id . '" data-entry-id="' . $entry->id . '">';



                if (is_email($display_value) and ! in_array($display_value, $to_emails))
                    $to_emails[] = $display_value;



                $var .= '</td>
                        </tr>';
            }
        }

        $var .= '<tr class="arfviewentry_row"><td class="arfviewentry_left arfwidth25">' . addslashes(esc_html__('Created at', 'ARForms')) . ':</td><td class="arfviewentry_right"><span class="arf_not_editable_values_container">' . $armainhelper->get_formatted_time($entry->created_date, $date_format, $time_format) .'</span>';
        if ($entry->user_id) {
            
        }

        $json_data = json_encode($as_edit_entry_value);
        
        $var .= '<input type="hidden" id="arf_edit_select_value_array_'.$entry->id.'" value="'.htmlspecialchars($json_data).'" />';

        $var .= '<script type="text/javascript">';
        $var .= 'var __ARF_edit_select_value_array = ' . json_encode($as_edit_entry_value);
        $var .= '</script>';

        $var .= '</td></tr>';



        $temp_var = apply_filters('arf_entry_payment_detail', $id);

        $var .= ( $temp_var != $id ) ? $temp_var : '';
        $data['page_url'] = isset($data['page_url']) ? $data['page_url'] : '';
        $data['http_referrer'] = isset($data['http_referrer']) ? $data['http_referrer'] : '';

        $var .= '<tr class="arfviewentry_row"><td class="arfviewentry_left arfwidth25">' . esc_html__('Page url', 'ARForms') . ':</td><td class="arfviewentry_right"><span class="arf_not_editable_values_container">' . urldecode($data['page_url']) . '</span>';
        $var .= '</td></tr>';

        $var .= '<tr class="arfviewentry_row"><td class="arfviewentry_left arfwidth25">' . addslashes(esc_html__('Referrer url', 'ARForms')) . ':</td><td class="arfviewentry_right"><span class="arf_not_editable_values_container">' . urldecode($data['http_referrer']) . '</span>';
        $var .= '</td></tr>';

        $var = apply_filters('arfafterviewentrydetail', $var, $id);

        $var .= '</tbody></table>';

        return $var;
    }

    function arfentryactionfunc() {

        global $db_record;

        if ($_REQUEST['act'] == 'delete' and $_REQUEST['id'] != '') {

            $del_res = $db_record->destroy($_REQUEST['id']);

            if ($del_res)
                $message = addslashes(esc_html__('Entry deleted successfully', 'ARForms'));

            $errors = array();

            return $this->frm_change_entries($_POST['form'], $_POST['start_date'], $_POST['end_date'], '1', $message, $errors);
        }


        die();
    }

    function include_css_from_form_content($post_content) {

        global $post, $submit_ajax_page, $arfversion, $arf_jscss_version;

        $submit_ajax_page = 1;

        $wp_upload_dir = wp_upload_dir();
        if (is_ssl()) {
            $upload_main_url = str_replace("http://", "https://", $wp_upload_dir['baseurl'] . '/arforms/maincss');
        } else {
            $upload_main_url = $wp_upload_dir['baseurl'] . '/arforms/maincss';
        }

        $parts = explode("[ARForms", $post_content);
        $myidpart = explode("id=", $parts[1]);
        $myid = explode("]", $myidpart[1]);



        if (!is_admin()) {
            global $wp_query;
            $posts = $wp_query->posts;
            $pattern = get_shortcode_regex();


            if (preg_match_all('/' . $pattern . '/s', $post_content, $matches) && array_key_exists(2, $matches) && in_array('ARForms', $matches[2])) {
                
            }

            $formids = array();

            foreach ($matches as $k => $v) {
                foreach ($v as $key => $val) {
                    $parts = explode("id=", $val);
                    if ($parts > 0) {

                        if (@stripos($parts[1], ']') !== false) {
                            $partsnew = @explode("]", $parts[1]);
                            $formids[] = @$partsnew[0];
                        } else if (@stripos($parts[1], ' ') !== false) {

                            $partsnew = @explode(" ", $parts[1]);
                            $formids[] = @$partsnew[0];
                        } else {
                            
                        }
                    }
                }
            }

            $newvalarr = array();

            if (is_array($formids) && count($formids) > 0) {
                foreach ($formids as $newkey => $newval) {
                    if (stripos($newval, ' ') !== false) {
                        $partsnew = explode(" ", $newval);
                        $newvalarr[] = $partsnew[0];
                    } else
                        $newvalarr[] = $newval;
                }
            }

            if (is_array($newvalarr) && count($newvalarr) > 0) {
                $newvalarr = array_unique($newvalarr);
                foreach ($newvalarr as $newkey => $newval) {
                    $fid1 = $upload_main_url . '/maincss_' . $newval . '.css';

                    wp_register_style('arfformscss' . $newval, $upload_main_url . '/maincss_' . $newval . '.css', array(), $arf_jscss_version);
                    wp_print_styles('arfformscss' . $newval);
                }
            }
        }
    }

    function ajax_check_recaptcha() {

        global $wpdb, $errors, $arfieldhelper, $maincontroller;

        $errors = array();

        $arf_options = get_option('arf_options');

        $default_blank_msg = $arf_options->blank_msg;

        $fields = $arfieldhelper->get_form_fields_tmp(false, $_POST['form_id'], false, 0);

        foreach ($fields as $field) {
            $field_id = $field->id;

                if ($field->type == 'captcha' and isset($_POST['recaptcha_challenge_field'])) {

                    $maincontroller->arfafterinstall();

                    global $arfsettings;




                    require_once(FORMPATH . '/core/recaptchalib/recaptchalib.php');

                    $site_key = $arfsettings->pubkey;
                    $private_key = $arfsettings->privkey;


                    if ($site_key == "" || $private_key == "") {

                        $errors[$field_id] = (!isset($field->field_options['invalid']) or $field->field_options['invalid'] == '') ? $arfsettings->re_msg : $field->field_options['invalid'];
                    } else {

                        $recaptcha = new ARForms_ReCaptcha($private_key);

                        $response = $recaptcha->verifyResponse($_SERVER['REMOTE_ADDR'], $_POST['g-recaptcha-response']);


                        if ($response->success) {
                            $errors['captcha'] = 'success';
                            $_SESSION['arf_recaptcha_allowed_' . $_POST['form_id']] = 1;
                        } else {
                            $errors[$field_id] = (!isset($field->field_options['invalid']) or $field->field_options['invalid'] == '') ? $arfsettings->re_msg : $field->field_options['invalid'];
                        }
                    }
                }
        }

        echo json_encode($errors);
        die();
    }

    function internal_check_recaptcha() {

        global $wpdb, $errors, $arfieldhelper, $maincontroller,$arfsettings;

        $errors = array();

        $arf_options = get_option('arf_options');

        $default_blank_msg = $arf_options->blank_msg;

        $fields = $arfieldhelper->get_form_fields_tmp(false, $_POST['form_id'], false, 0);

        foreach ($fields as $field) {
            $field_id = $field->id;
        
                if ($field->type == 'captcha' && $arfsettings->pubkey !='' && $arfsettings->privkey !='') {

                    $maincontroller->arfafterinstall();

                    global $arfsettings;


                    require_once( FORMPATH . '/core/recaptchalib/recaptchalib.php' );

                    $sitekey = $arfsettings->pubkey;
                    $secret = $arfsettings->privkey;

                    if ($sitekey == "" || $secret == "") {
                        $errors[$field_id] = (!isset($field->field_options['invalid']) or $field->field_options['invalid'] == '') ? $arfsettings->re_msg : $field->field_options['invalid'];
                    } else {

                        $recaptcha = new ARForms_ReCaptcha($secret);
                        $recptcha_response = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : "";
                        $response = $recaptcha->verifyResponse($_SERVER['REMOTE_ADDR'], $recptcha_response);
                        if ($response->success) {
                            
                        } else {
                            $errors[$field_id] = (!isset($field->field_options['invalid']) or $field->field_options['invalid'] == '') ? $arfsettings->re_msg : $field->field_options['invalid'];
                        }
                    }
                }
        }

        return $errors;
    }

    function getBrowser($user_agent) {
        $u_agent = $user_agent;
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version = "";


        if (@preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (@preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (@preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        $ub = "Unknown";
        
        if (@preg_match('/MSIE/i', $u_agent) && !@preg_match('/Opera/i', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif (@preg_match('/Firefox/i', $u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (@preg_match('/OPR/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "OPR";
        } elseif (@preg_match('/Edge/i', $u_agent)) {
            $bname = 'Edge';
            $ub = "Edge";
        } elseif (@preg_match('/Chrome/i', $u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (@preg_match('/Safari/i', $u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (@preg_match('/Opera/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (@preg_match('/Netscape/i', $u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        } elseif (@preg_match('/Trident/', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "rv";
        }

        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
                ')[/ |:]+(?<version>[0-9.|a-zA-Z.]*)#';

        if (!@preg_match_all($pattern, $u_agent, $matches)) {
            
        }


        $i = count($matches['browser']);
        if ($i != 1) {

            if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
                $version = $matches['version'][0];
            } else {
                $version = $matches['version'][1];
            }
        } else {
            $version = $matches['version'][0];
        }


        if ($version == null || $version == "") {
            $version = "?";
        }

        return array(
            'userAgent' => $u_agent,
            'name' => $bname,
            'version' => $version,
            'platform' => $platform,
            'pattern' => $pattern
        );
    }

    function current_modal() {
        if (isset($_REQUEST['position_modal']) && $_REQUEST['position_modal'] != '') {
            $current_modal = $_REQUEST['position_modal'];
            $_SESSION['last_open_modal'] = $current_modal;
            echo $_SESSION['last_open_modal'];
            exit;
        }
    }

    function arf_edit_entry_values() {

        global $wpdb, $arffield, $MdlDb, $armainhelper;

        $arf_return = array('status' => 'error', 'message' => addslashes(esc_html__('Record could not be updated','ARForms')));

        $arfform_id =  (isset($_POST['arf_form']) && !empty($_POST['arf_form'])) ? $_POST['arf_form'] : '';
        $entry_id = (isset($_POST['entry_id']) && !empty($_POST['entry_id'])) ? $_POST['entry_id'] : '';
        $arfupdatedfields = (isset($_POST['updatedfields']) && !empty($_POST['updatedfields'])) ? explode("||", $_POST['updatedfields']) : array();
        $arfupdatedfieldvalues = (isset($_POST['newvalues']) && !empty($_POST['newvalues'])) ? explode("||", $_POST['newvalues']) : array();
        $field_types = (isset($_POST['field_types']) && !empty($_POST['field_types'])) ? explode("||", $_POST['field_types']) : array();

        $form_cols = array();
        $arfdraw_cols = array();
        $edit_select_options = array();

        if (!empty($entry_id) && count($arfupdatedfields) > 0) {

            if($arfform_id != '' && $arfform_id != '-1') {
                $form_cols = $wpdb->get_results( $wpdb->prepare("SELECT `id` FROM `".$MdlDb->fields."` WHERE form_id = %d AND type NOT IN ('divider', 'captcha', 'break', 'html', 'imagecontrol')", $arfform_id) );
                $form_cols = apply_filters('arfpredisplayformcols', $form_cols, $arfform_id);
            }

            $arf_columns = array('0' => '', '1' => 'id');

            if (count($form_cols) > 0) {

                $get_form_options = $wpdb->get_row( $wpdb->prepare("SELECT `options` FROM `".$MdlDb->forms."` WHERE `id` = %d AND `is_template` != %d AND `status` = %s", $arfform_id, 1,'published') );
                $form_options = maybe_unserialize($get_form_options->options);

                $arffieldorder = array();
                if(isset($form_options['arf_field_order']) && $form_options['arf_field_order'] != ''){
                    $arffieldorder = json_decode($form_options['arf_field_order'], true);
                    asort($arffieldorder);
                }

                if(count($arffieldorder) > 0){
                    $form_cols_temp = array();
                    foreach ($arffieldorder as $fieldkey => $fieldorder) {
                        foreach ($form_cols as $frmoptkey => $frmoptarr) {
                            if($frmoptarr->id == $fieldkey){
                                $form_cols_temp[] = $frmoptarr;
                                unset($form_cols[$frmoptkey]);
                            }
                        }
                    }

                    if(count($form_cols_temp) > 0) {
                        if(count($form_cols) > 0) {
                            $form_cols_other = $form_cols;
                            $form_cols = array_merge($form_cols_temp,$form_cols_other);
                        } else {
                            $form_cols = $form_cols_temp;
                        }
                    }
                }

                for ($col_i = 2; $col_i <= count($form_cols) + 1; $col_i++) {
                    $col_j = $col_i - 2;
                    $arf_columns[$form_cols[$col_j]->id] = $col_i;
                }

            }



            foreach ($arfupdatedfields as $arfkey => $field_id) {

                $new_value_sep = '';

                if(!empty($field_id)){

                    $field_type = isset($field_types[$arfkey]) ? $field_types[$arfkey] : '';

                    $new_value = isset($arfupdatedfieldvalues[$arfkey]) ? $arfupdatedfieldvalues[$arfkey] : '';
                    $draw_col_value = $new_value;
                    $is_multiselect_update = 0;

                    if ($field_type == 'checkbox' || $field_type == 'select' || $field_type == 'radio' || $field_type == 'arf_autocomplete') {
                        $is_multiselect_update = 1;
                        $field = $arffield->getOne($field_id);
                        $as_new_value_sep = array();

                        if ($field_type == 'checkbox') {
                            $op_value = explode(',', $new_value);
                            $new_value = maybe_serialize($op_value);
                            $draw_col_value = implode(', ', $op_value);
                            $edit_select_options[$field_id] = json_encode($op_value);

                        } else {
                            $edit_select_options[$field_id] = json_encode($new_value);
                        }

                        if( isset($field->field_options['separate_value']) && $field->field_options['separate_value'] == 1 ) {
                            if(isset($field->field_options['options']) && $field->field_options['options'] != ''){
                                $arf_select_values = array_column($field->field_options['options'], 'value');
                                $arf_select_label = array_column($field->field_options['options'], 'label');
                                if ($field_type == 'checkbox' && isset($op_value) ) {
                                    $op_label = array();
                                    foreach ($op_value as $key => $value) {
                                        if(in_array($value, $arf_select_values)){
                                            $op_label[] = $arf_select_label[array_search($value, $arf_select_values)];
                                        }
                                    }
                                    $draw_col_value = implode(', ', $op_label);
                                } else {
                                    $draw_col_value = $arf_select_label[array_search($new_value, $arf_select_values)];
                                }
                            }
                        }

                        if (!empty($field->options)) {

                            foreach ($arf_field_options as $key => $option) {
                                if ($field_type == 'checkbox') {
                                    if (in_array($option['value'], $op_value)) {
                                        $as_new_value_sep[] = $option;
                                    }
                                } else {
                                    if ($option['value'] == $new_value) {
                                        $as_new_value_sep = $option;
                                    }
                                }
                            }

                        }

                        if (!empty($as_new_value_sep)) {
                            $new_value_sep = maybe_serialize($as_new_value_sep);
                        }
                    }

                    $entry_data = $wpdb->get_row($wpdb->prepare("SELECT id FROM " . $MdlDb->entry_metas . " WHERE entry_id = %d AND field_id = %d", array($entry_id, $field_id)));

                    if (isset($entry_data) && !empty($entry_data->id)) {
                        $rec = $wpdb->update($MdlDb->entry_metas, array('entry_value' => $new_value), array('entry_id' => $entry_id, 'field_id' => $field_id));
                        if($rec){
                            $arfdraw_cols[] = array('field' => $field_id, 'col' => isset($arf_columns[$field_id]) ? $arf_columns[$field_id] : '', 'val' => $draw_col_value);
                        }
                        if ($new_value_sep) {
                            $rec_sep = $wpdb->update($MdlDb->entry_metas, array('entry_value' => $new_value_sep), array('entry_id' => $entry_id, 'field_id' => "-" . $field_id));
                        }
                    } else {

                        $arf_meta_insert = array(
                            'entry_value' => $new_value,
                            'field_id' => arf_sanitize_value($field_id, 'integer'),
                            'entry_id' => arf_sanitize_value($entry_id, 'integer'),
                            'created_date' => current_time('mysql'),
                        );
                        $rec = $wpdb->insert($MdlDb->entry_metas, $arf_meta_insert, array('%s', '%d', '%d', '%s'));
                        if($rec){
                            $arfdraw_cols[] = array('field' => $field_id, 'col' => isset($arf_columns[$field_id]) ? $arf_columns[$field_id] : '', 'val' => $draw_col_value);
                        }

                        if ($new_value_sep) {
                            $arf_meta_insert_wiht_sep = array(
                                'entry_value' => $new_value_sep,
                                'field_id' => "-" . $field_id,
                                'entry_id' => arf_sanitize_value($entry_id, 'integer'),
                                'created_date' => current_time('mysql'),
                            );
                            $wpdb->insert($MdlDb->entry_metas, $arf_meta_insert_wiht_sep, array('%s', '%d', '%d', '%s'));
                        }
                    }
                }
            }

            if(count($arfdraw_cols) > 0){
                $arf_return = array('status' => 'success', 'message' => addslashes(esc_html__('Record is updated successfully.','ARForms')), 'updatecols' => $arfdraw_cols, 'edit_select_options' => $edit_select_options);
            }

        }
        echo json_encode($arf_return);
        die();
    }

    function load_footer_script() {

        global $arfversion;
        $path = $_SERVER['REQUEST_URI'];
        $file_path = basename($path);
        $css_array = array('bootstrap');
        $url = ARFURL . '/bootstrap/css/';
        foreach ($css_array as $cssfile) {
            echo "<link rel='stylesheet' type='text/css' href='" . $url . $cssfile . ".css?ver=" . $arfversion . "' />";
        }

        echo "<script type='text/javascript' data-cfasync='false' src='" . ARFURL . "/js/arf_conditional_logic.js?ver=" . $arfversion . "'></script>";

        if (!strstr($file_path, "post.php")) {


            global $arfrtloaded, $arfdatepickerloaded, $arftimepickerloaded;

            global $arfhiddenfields, $arfforms_loaded, $arfcalcfields, $arfrules, $arfinputmasks;

            if (empty($arfforms_loaded))
                return;


            foreach ($arfforms_loaded as $form) {


                if (!is_object($form))
                    continue;
            }

            $scripts = array('arforms');


            if (!empty($arfdatepickerloaded)) {
                $scripts[] = 'bootstrap-locale-js';
                $scripts[] = 'bootstrap-datepicker';
            }


            if (!empty($arftimepickerloaded)) {

                $scripts[] = 'bootstrap-locale-js';
                $scripts[] = 'bootstrap-datepicker';
            }



            $arfinputmasks = apply_filters('arfinputmasks', $arfinputmasks, $arfforms_loaded);




            $scripts[] = 'jquery-maskedinput';


            if (!empty($scripts)) {


                global $wp_scripts;


                $wp_scripts->do_items($scripts);
            }


            unset($scripts);

            include_once(VIEWS_PATH . '/common.php');

            echo '<script type="text/javascript" data-cfasync="false">';
            echo 'function load_arf_selectpicker(){';
            echo 'if (jQuery.isFunction(jQuery().selectpicker))';
            echo '{';
            echo 'jQuery(".sltstandard_front select").selectpicker();';
            echo '}';
            echo '}';
            echo '</script>';
            ?>
            <script type="text/javascript" data-cfasync="false">
                function arf_load_colorpicker() {
                    if (jQuery.isFunction(jQuery().colpick))
                    {
                        jQuery("form.arfshowmainform").each(function () {
                            var color_data_id = jQuery(this).attr('data-id');
                            var color_curr_form = jQuery("form.arfshowmainform[data-id='" + color_data_id + "']");
                            color_curr_form.find('.arf_colorpicker').colpick({
                                layout: 'hex',
                                submit: 1,
                                color: 'ffffff',
                                onBeforeShow: function () {
                                    var fid = jQuery(this).attr('id');
                                    var fid = fid.replace('arfcolorpicker_', '');
                                    var color = color_curr_form.find('#field_' + fid).val();
                                    var new_color = color.replace('#', '');
                                    if (new_color)
                                        jQuery(this).colpickSetColor(new_color);
                                },
                                onChange: function (hsb, hex, rgb, el, bySetColor) {
                                    var field_key = jQuery(el).attr('id');
                                    field_key = field_key.replace('arfcolorpicker_', '');
                                    color_curr_form.find('#field_' + field_key).val('#' + hex).trigger('change');
                                    jQuery(el).find('.arfcolorvalue').text('#' + hex);
                                    jQuery(el).find('.arfcolorvalue').css('background', '#' + hex);
                                    var arffontcolor = HextoHsl(hex) > 0.5 ? '#000000' : '#ffffff';
                                    jQuery(el).find('.arfcolorvalue').css('color', arffontcolor);
                                },
                                onSubmit: function () {
                                    color_curr_form.find('.arf_colorpicker').colpickHide();
                                }
                            });
                        });
                    }
                    jQuery('.colpick_hex_field').find('input').bind('paste', function (event) {
                        event.preventDefault();
                        var clipboardData = event.originalEvent.clipboardData.getData('text');
                        clipboardData = clipboardData.replace('#', '');
                        jQuery(this).val(clipboardData).trigger('change');
                    });
                }

                function arf_load_simple_colpicker() {
                    if (jQuery.isFunction(jQuery().simpleColorPicker))
                    {
                        jQuery("form.arfshowmainform").each(function () {
                            var scolor_data_id = jQuery(this).attr('data-id');
                            var scolor_curr_form = jQuery("form.arfshowmainform[data-id='" + scolor_data_id + "']");
                            scolor_curr_form.find('.arf_basic_colorpicker').simpleColorPicker({
                                onChangeColor: function (color) {
                                    var field_key = jQuery(this).attr('id');
                                    field_key = field_key.replace('arfcolorpicker_', '');
                                    scolor_curr_form.find('#field_' + field_key).val(color).trigger('change');
                                    jQuery(this).find('.arfcolorvalue').text(color);
                                    jQuery(this).find('.arfcolorvalue').css('background', color);
                                    var hex = color.replace('#', '');
                                    var arffontcolor = HextoHsl(hex) > 0.5 ? '#000000' : '#ffffff';

                                    if (hex == "ffff00")
                                    {
                                        arffontcolor = "#000000";
                                    }
                                    jQuery(this).find('.arfcolorvalue').css('color', arffontcolor);
                                }
                            });
                        });
                    }
                    jQuery('.colpick_hex_field').find('input').bind('paste', function (event) {
                        event.preventDefault();
                        var clipboardData = event.originalEvent.clipboardData.getData('text');
                        clipboardData = clipboardData.replace('#', '');
                        jQuery(this).val(clipboardData).trigger('change');
                    });
                }
            </script>
            <?php
            $form_id = $form->id;
            global $arfsettings;
            if (!isset($arfsettings)) {
                $arfsettings_new = get_option('arf_options');
            } else {
                $arfsettings_new = $arfsettings;
            }
            ?>
            <script type="text/javascript" data-cfasync="false">
            <?php
            if ((isset($arfsettings_new->arfmainformloadjscss) && $arfsettings_new->arfmainformloadjscss == 1)) {
                ?> load_arf_selectpicker(); <?php
            }

            if ((isset($arfsettings_new->arfmainformloadjscss) && $arfsettings_new->arfmainformloadjscss == 1)) {
                ?> arf_load_colorpicker(); <?php
            }

            if ((isset($arfsettings_new->arfmainformloadjscss) && $arfsettings_new->arfmainformloadjscss == 1)) {
                ?> arf_load_simple_colpicker(); <?php
            }
            ?>
            </script>
            <?php
        }
    }
    
    function arf_delete_single_entry_function(){
        global $db_record;
        
        $entry_id = isset($_REQUEST['entry_id']) ? $_REQUEST['entry_id'] : 0;
        $form_id = isset($_REQUEST['form_id']) ? $_REQUEST['form_id'] : '';

        if( $entry_id < 1 ){
            echo json_encode(array('error'=>true,'message'=>addslashes(esc_html__('Please select one or more record.','ARForms'))));
            die();
        }
        
        $del_res = $db_record->destroy($entry_id);
        
        if( $del_res ){

            $total_records = '';
            if($form_id != ''){
                $total_records = $db_record->getRecordCount( (int)$form_id );
            }

            echo json_encode(array('error' => false, 'message' => addslashes(esc_html__('Record is deleted successfully.','ARForms')), 'arftotrec' => $total_records));

        } else {
            echo json_encode(array('error' => true, 'message' => addslashes(esc_html__('Record could not be deleted','ARForms'))));
        }

        die();
    }

    function ajax_check_spam_filter() {
        $formRandomKey = isset($_POST['form_random_key']) ? $_POST['form_random_key'] : '';
        $validate = TRUE;
        $is_check_spam = true;

        if ($is_check_spam) {
            $validate = apply_filters('is_to_validate_spam_filter', $validate, $formRandomKey);
        }
        $response = array();
        if (!$validate) {
            $response['error'] = true;
            $response['message'] = addslashes(esc_html__('Spam Detected', 'ARForms'));
            
        } else {
            $response['error'] = false;
        }
        $response = apply_filters( 'arf_reset_built_in_captcha', $response, $_POST );
        echo json_encode($response);
        die();
    }

}
?>