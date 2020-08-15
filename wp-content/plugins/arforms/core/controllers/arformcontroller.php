<?php

global $arf_memory_limit, $memory_limit, $arfversion;
if(isset($arf_memory_limit) && isset($memory_limit) && ($arf_memory_limit * 1024 * 1024) > $memory_limit) {
    @ini_set("memory_limit", $arf_memory_limit . 'M');
}
class arformcontroller {
    function __construct() {
        add_action('admin_menu', array($this, 'menu'));
        add_action('admin_head-toplevel_page_ARForms', array($this, 'head'));        
        add_action('admin_footer', array($this, 'insert_form_popup'));
        add_action('wp_ajax_change_show_hide_column', array($this, 'change_show_hide_column'));
        add_action('wp_ajax_arfupdateformbulkoption', array($this, 'arfupdateformbulkoption'));       
        add_action('wp_ajax_arfupdateactionfunction', array($this, 'arfupdateactionfunction'));
        add_action('wp_ajax_arfformsavealloptions', array($this, 'arfformsavealloptions'));
        add_action('ARForms_shortcode_atts', array($this, 'ARForms_shortcode_atts'));
        add_action('ARForms_popup_shortcode_atts', array($this, 'ARForms_popup_shortcode_atts'));
        add_action('wp_ajax_arf_delete_file', array($this, 'arf_delete_file'));
        add_action('wp_ajax_nopriv_arf_delete_file', array($this, 'arf_delete_file'));
        add_action('media_buttons', array($this, 'insert_form_button'), 20);
        add_action('wp_ajax_arfsavepreviewdata', array($this, 'arf_save_preview_data_to_db'));
        add_action('wp_ajax_arf_delete_form', array($this, 'arf_delete_form_function'));    
        add_action('wp_ajax_arf_csv_form', array($this, 'arf_csv_form_function'));
        add_action('wp_ajax_arfchangestyle', array($this, 'arf_change_input_style'));
        add_action('wp_ajax_arf_send_form_data', array($this,'arf_upload_image_function'));
        add_action('wp_ajax_nopriv_arf_send_form_data',array($this,'arf_upload_image_function'));
        add_action('wp_ajax_arf_send_form_data_admin',array($this,'arf_upload_image_from_admin') );

        add_filter('arfadminactionformlist', array($this, 'process_bulk_form_actions'));
        add_filter('getarfstylesheet', array($this, 'custom_stylesheet'), 10, 2);
        add_filter('arfaddnewfieldlinks', array($this, 'arfaddnewfieldlinks'), 10, 3);
        add_filter('arffielddrag', array($this, 'arffielddrag_class'));
        add_filter('arfcontent', array($this, 'filter_content'), 10, 3);
        add_filter('getsubmitbutton', array($this, 'formsubmit_button_label'), 5, 2);        
        add_filter('arfformoptionsbeforeupdateform', array($this, 'arf_conditional_mail_save_opt_function'), 11, 2);
        add_filter('plugin_action_links_' . PLUGIN_BASE_FILE, array($this, 'arf_add_action_links'));
        add_filter('arfformoptionsbeforeupdateform', array($this, 'arf_new_conditional_logic_rules_save'), 12, 2);
        add_filter('arf_migrate_fields_id_in_import_form', array($this,'arf_import_export_migrate_old_field'), 10,2);
        add_filter('arf_after_submit_sucess_outside',array($this,'arf_after_submit_sucess_outside_function'),10,2);

        add_filter('arf_replace_default_value_shortcode',array($this,'arf_replace_default_value_shortcode_func'),10,3);
        
        global $arformsplugin;
        $arformsplugin = "checksorting";

        global $valid_wp_version;
        $valid_wp_version = "valid_wp_version";

        global $arf_get_version_val;
        $arf_get_version_val = "arf_get_version_val";

		global $check_current_val;
        $check_current_val = "check_current_val";

        global $check_valid_sample;
        $check_valid_sample = 'check_valid_sample';
    }

    function arf_upload_image_from_admin(){

        if( false == current_user_can( 'arfviewforms' ) || false == current_user_can( 'arfeditforms' ) || false == current_user_can( 'arfchangesettings' ) ){
            echo '<p class="error_upload">'.esc_html__("Sorry, you don't have permission to perform this action.","ARForms").'</p>';
            die;
        }

        $fn = (isset($_SERVER['HTTP_X_FILENAME']) ? $_SERVER['HTTP_X_FILENAME'] : false);

        $wp_upload_dir = wp_upload_dir();
        $upload_main_url = $wp_upload_dir['basedir'] . '/arforms/';

        if ($fn) {
            $checkext = explode(".", $_FILES['files']['name']);
            $ext = $checkext[count($checkext) - 1];
            $ext = strtolower($ext);
            if ($ext != "php" && $ext != "php3" && $ext != "php4" && $ext != "php5" && $ext != "pl" && $ext != "py" && $ext != "jsp" && $ext != "asp" && $ext != "exe" && $ext != "cgi") {

                file_put_contents( $upload_main_url . $fn, file_get_contents($_FILES['files']['tmp_name']) );
                echo "$fn";
                exit();
            }
        } else {
            $files = $_FILES['fileselect'];
            $fn = $_REQUEST['fname'];
            $file_type_new = $_FILES['fileselect']['type'];

            $checkext = explode(".", $_FILES['fileselect']['name']);
            $ext = $checkext[count($checkext) - 1];
            $ext = strtolower($ext);
            $is_preview = (isset($_REQUEST['is_preview'])) ? $_REQUEST['is_preview'] : 0;
            $type_array = ($_REQUEST['types_arr'] != '') ? explode(',', $_REQUEST['types_arr']) : array();

            if (isset($_REQUEST['file_type']) && $_REQUEST['file_type'] != '')
                $file_type = $_REQUEST['file_type'];
            else
                $file_type = '';

            if (isset($_REQUEST['ie_version']) and $_REQUEST['ie_version'] <= '9' and isset($_REQUEST['browser']) and $_REQUEST['browser'] == 'Internet Explorer') {
                $vari = 0;
                foreach ($type_array as $val_new) {
                    $val_array = explode('|', $val_new);
                    if (count($val_array) > 0) {
                        foreach ($val_array as $new_ext) {
                            if (trim($new_ext) == $ext)
                                $vari++;
                        }
                    }
                }
                if ($vari > 0)
                    $ie89_validation = true;
                else
                    $ie89_validation = false;
            } else {
                $ie89_validation = false;
            }



            if ($ext != "php" && $ext != "php3" && $ext != "php4" && $ext != "php5" && $ext != "pl" && $ext != "py" && $ext != "jsp" && $ext != "asp" && $ext != "exe" && $ext != "cgi") {
                if (( is_array($type_array) and count($type_array) > 0 and in_array($file_type_new, $type_array) and $is_preview == 0 ) or ( count($type_array) == 0 and $is_preview == 0) or $ie89_validation) {

                    move_uploaded_file(
                            $files['tmp_name'], $upload_main_url . $fn
                    );

                    echo "<p class='uploaded'>|$fn|$field_id</p>";
                } else {
                    echo "<p class='error_upload'>file type not allowed</p>";
                }
            } else {
                echo "<p class='error_upload'>file type not allowed</p>";
            }
        }
    }

    function arf_upload_image_function() {
        global $wpdb,$MdlDb,$maincontroller;
        $fn = (isset($_SERVER['HTTP_X_FILENAME']) ? $_SERVER['HTTP_X_FILENAME'] : false);
        $field_id = (isset($_REQUEST['field_id']) ? $_REQUEST['field_id'] : false);
        $wp_upload_dir  = wp_upload_dir();
        if(isset($_REQUEST['file_type']) && $_REQUEST['file_type'] != '') {
            $file_type = $_REQUEST['file_type'];
        } else {
            $file_type = '';
        }

        $file_bytes = $_FILES['files']['size'];

        $file_size = number_format($file_bytes / 1048576, 2);

        if( preg_match('/(image\/)/',$_FILES['files']['type']) && $file_size <= 10 ){

            WP_Filesystem();
            global $wp_filesystem;
            $file_content = $wp_filesystem->get_contents($_FILES['files']['tmp_name']);

            $isValidFile = $maincontroller->arf_check_valid_file($file_content);
            if( false == $isValidFile ){
                echo "<p class='error_upload_security'>".esc_html__('The file could not be uploaded due to security reason as it contains malicious code','ARForms')."</p>";
                die;
            }
        }
        $field_data = explode('_',$field_id);
        $field_key = $field_data[0];
        $form_id = isset($_REQUEST['frm']) ? $_REQUEST['frm'] : '';
        $db_field = $wpdb->get_row( $wpdb->prepare("SELECT field_options FROM `".$MdlDb->fields."` WHERE field_key = %s AND type = %s AND form_id = %d",$field_key,'file',$form_id ),ARRAY_A);
        $all_types = true;
        $specific_files = array();
        if(count($db_field) > 0 ){
            $field_options = json_decode($db_field['field_options'],true);
            if($field_options['restrict'] != 0 ){
                $field_types = $field_options['ftypes'];
                foreach($field_types as $key => $value){
                    if($value != '0' ){
                        array_push($specific_files,str_replace('ftypes_','',$key));
                    }
                }
            }
            if(count($specific_files) > 0){
                $all_types = false;
            }
        } else {
            die;
        }
        include(FORMPATH.'/js/filedrag/simple_image.php');
        $is_preview = (isset($_REQUEST['is_preview']) && $_REQUEST['is_preview'] != '' ) ? $_REQUEST['is_preview']: 0;

        $type_array = ($_REQUEST['types_arr']!= '') ? explode(',', $_REQUEST['types_arr']) : array();
        $_REQUEST['file_size'] = isset($_REQUEST['file_size'])?$_REQUEST['file_size']:'';
        $file_size = ($_REQUEST['file_size'] == 'auto') ? 'auto' : $_REQUEST['file_size'];
        $allowed_mimes = array();
        if($all_types == true) {
            $mimes = get_allowed_mime_types();
            ksort($mimes);
            $mcount = count($mimes);
            $third = ceil($mcount / 3);
            $c = 0;
            $mimes['exe'] = '';
            unset($mimes['exe']);
            foreach( $mimes as $ext => $type ){
                if(strpos($ext, '|') !== false ){
                    $exts = explode('|',$ext);
                    foreach( $exts as $extension){
                        if($extension != '' ){
                            array_push($allowed_mimes,$extension);
                        }
                    }
                } else {
                    array_push($allowed_mimes,$ext);
                }
            }
        } else {
            foreach( $specific_files as $ext ){
                if(strpos($ext, '|') !== false ){
                    $exts = explode('|',$ext);
                    foreach( $exts as $extension){
                        if($extension != '' ){
                            array_push($allowed_mimes,$extension);
                        }
                    }
                } else {
                    array_push($allowed_mimes,$ext);
                }   
            }
        }

        if($fn){
            $checkext = explode(".",$fn);
            $ext = $checkext[count($checkext)-1];
            $ext = strtolower($ext);
            if($ext!="php" && $ext!="php3" && $ext!="php4" && $ext!="php5" && $ext!="pl" && $ext!="pl" && $ext!="py" && $ext!="jsp" && $ext!="asp" && $ext!="exe" && $ext!="cgi" && in_array($ext,$allowed_mimes) ){

                if($is_preview == 0) {
                    global $arformhelper;
                    $movable = $arformhelper->manage_uploaded_file_path($form_id);
                    
                    if($movable["status"]) {
                        file_put_contents($movable["path"].$fn, file_get_contents($_FILES['files']['tmp_name']));
                        $pos = preg_match('/image\//',$file_type);
                        if($pos) {
                            $image = new SimpleImage();
                            $image->load($movable["path"].$fn);
                            $image->resizeToHeight(100);
                            $image->save($movable["path"].'thumbs/'.$fn);
                        }
                        $_SESSION['arf_form_fileuploads'][] = $fn;
                        echo "|$fn|$field_id";
                    }
                }
                else {
                    $_SESSION['arf_form_fileuploads'][] = $fn;
                    echo "|$fn|$field_id";
                }
            }
        } else {
            $files = $_FILES['fileselect'];
            $fn = $_REQUEST['fname'];
            $file_type_new = $_FILES['fileselect']['type'];
            $file_size_new = $_FILES['fileselect']['size'];
            $checkext = explode(".",$fn);   
            $ext = $checkext[count($checkext)-1];
            $ext = strtolower($ext);
            if(isset($_REQUEST['ie_version']) and $_REQUEST['ie_version'] <= '9' and isset($_REQUEST['browser']) and $_REQUEST['browser'] == 'Internet Explorer') {
                $vari = 0;
                foreach($type_array as $val_new ){
                    $val_array = explode('|', $val_new);
                    if(count($val_array) > 0 ){
                        foreach($val_array as $new_ext) { 
                            if(trim($new_ext) == $ext) $vari++;
                        }
                    }
                    
                }
                if($vari > 0 )
                    $ie89_validation = true;
                else
                    $ie89_validation = false;
                            
            } else {
                $ie89_validation = false;
            }
            
            if($ext!="php" && $ext!="php3" && $ext!="php4" && $ext!="php5" && $ext!="pl" && $ext!="py" && $ext!="jsp" && $ext!="asp" && $ext!="exe" && $ext!="cgi" && in_array($ext,$allowed_mimes) ){
                if(( is_array($type_array) and count($type_array) > 0 and in_array($file_type_new, $type_array) and $is_preview == 0 ) or (count($type_array) == 0 and  $is_preview == 0) or $ie89_validation ){
                    $movable = $arformhelper->manage_uploaded_file_path($form_id);
                    if($movable["status"] && ($file_size == 'auto' || ($file_size_new <= $file_size))) {
                        move_uploaded_file($files['tmp_name'], $movable["path"] . $fn);
                        $pos = strpos('jpg, jpeg, jpe, gif, png, bmp, tif, tiff, ico,', $ext);
                        if($pos !== false) {
                            $image = new SimpleImage();     
                            $image->load($movable["path"].$fn);
                            $image->resizeToHeight(100);
                            $image->save($movable["path"].'thumbs/'.$fn);
                        }
                        //changes end here. 
                        echo "<p class='uploaded'>|$fn|$field_id</p>";
                    } else {
                        echo "<p class='error_upload_size'>file size not allowed</p>";
                    }
                } else {
                    echo "<p class='error_upload'>file type not allowed</p>";
                }
            } else {
                echo "<p class='error_upload'>file type not allowed</p>";
            }
        }
        die;   
    }

    function arf_import_export_migrate_old_field( $new_form_options, $migrate_fields) {
        foreach( $new_form_options as $opt_key => $opt_val) {
            if(is_array($opt_val)) {
                $new_form_options[$opt_key] = $this->arf_import_export_migrate_old_field($new_form_options[$opt_key],$migrate_fields);
            } else {
                $pattern = "/(\:[\d+]+)/";
                preg_match_all($pattern,$new_form_options[$opt_key],$matched);
                if(isset($matched[0]) && $matched[0] != '' ){
                    foreach( $matched[0] as $ki => $vi ){
                        $replace_pattern = "/\b".$vi."\b/";
                        $old_id = str_replace(':','',$vi);
                        $new_id = $migrate_fields[$old_id];
                        $new_form_options[$opt_key] = preg_replace($replace_pattern,':'.$new_id,$new_form_options[$opt_key]);
                    }
                }
            }
        }
        return $new_form_options;
    }

    function arf_add_action_links($links) {
        $mylinks = array(
            '<a href="' . admin_url('admin.php?page=ARForms-addons') . '">Addons</a>',
        );
        return array_merge($mylinks, $links);
    }

    function arf_class_to_hide_form($id, $hide_form = false) {
        global $wpdb, $MdlDb;
        $table = $MdlDb->forms;
        if(!isset($GLOBALS['arf_form_data'][$id])){
            $form_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $table . " WHERE `id` = '%d'",$id));
            $GLOBALS['arf_form_data'][$id] = $form_data;
        } else {
            $form_data = $GLOBALS['arf_form_data'][$id];
        }

        if (empty($form_data))
            return;

        $form_options = maybe_unserialize($form_data->options);


        $arf_disable_form = false;
        if(isset($_SESSION['arf_form_fileuploads'])){ $_SESSION['arf_form_fileuploads'] = array(); }

        if (isset($form_options['arf_restrict_form_entries']) && $form_options['arf_restrict_form_entries'] == 1) {

         
            if (isset($form_options['restrict_action']) && $form_options['restrict_action'] != '') {
                $restrict_action = $form_options['restrict_action'];
                if ($restrict_action != 'max_entries') {
                    $defaultdate_format = 'Y-m-d';
                    $arf_current_date = date($defaultdate_format);
                }

                $is_restrict_entry = false;

                if ($restrict_action == 'before_specific_date') {

                    $arf_restrict_entries_date = $form_options['arf_restrict_entries_before_specific_date'];
                    
                    if (strtotime($arf_restrict_entries_date) > strtotime($arf_current_date)) {
                        $is_restrict_entry = true;
                    }

                } else if ($restrict_action == 'after_specific_date') {
                    $arf_restrict_entries_date = $form_options['arf_restrict_entries_after_specific_date'];
                    if (strtotime($arf_restrict_entries_date) < strtotime($arf_current_date)) {
                        $is_restrict_entry = true;
                    }
                } else if ($restrict_action == 'date_range') {
                    $arf_start_date = $form_options['arf_restrict_entries_start_date'];
                    $arf_end_date = $form_options['arf_restrict_entries_end_date'];
                    if (strtotime($arf_start_date) <= strtotime($arf_current_date) && strtotime($arf_end_date) >= strtotime($arf_current_date)) {
                        $is_restrict_entry = true;
                    }
                } else {
                    $is_restrict_entry = false;
                }


                if ($is_restrict_entry) {

                    $arf_res_msg_disp = '<div class="arf_form ar_main_div_' . $form_data->id . '" id="arffrm_' . $form_data->id . '_container"><div  class="frm_error_style" id="arf_message_error"><div class="msg-detail"><div class="arf_res_front_msg_desc">' . $form_options['arf_res_msg'] . '</div></div></div></div>';
                    $return["conf_method"] = "message";
                    $return["message"] = $arf_res_msg_disp;
                    
                    return apply_filters('arf_res_front_msg', json_encode($return), $form_options['arf_res_msg']);
                } 
            } 
        }

        if(isset($form_options['arf_restrict_entry']) && $form_options['arf_restrict_entry']){
            $is_restrict_entry = false;
            $arf_restrict_max_entries = $form_options['arf_restrict_max_entries'];

            $arf_form_total_entries = $wpdb->get_var($wpdb->prepare("select count(*) from " . $MdlDb->entries . " where form_id = %d", $id));

            if ($arf_form_total_entries >= $arf_restrict_max_entries) {
                $is_restrict_entry = true;
            }

            if (isset($_REQUEST['is_submit_form_' . $id]) && $_REQUEST['is_submit_form_' . $id] == 1 && ($arf_form_total_entries) >= $arf_restrict_max_entries) {
                $return["hide_forms"] = true;
                $is_restrict_entry = false;
            }
            if (isset($_REQUEST['is_submit_form_' . $id]) && $_REQUEST['is_submit_form_' . $id] == 0 && ($arf_form_total_entries + 1) >= $arf_restrict_max_entries) {
                $is_restrict_entry = false;
            }

            if ($is_restrict_entry) {
                $arf_res_msg_disp = '<div class="arf_form ar_main_div_' . $form_data->id . '" id="arffrm_' . $form_data->id . '_container"><div  class="frm_error_style" id="arf_message_error"><div class="msg-detail"><div class="arf_res_front_msg_desc">' . $form_options['arf_res_msg_entry'] . '</div></div></div></div>';
                $return["conf_method"] = "message";
                $return["message"] = $arf_res_msg_disp;

                return apply_filters('arf_res_front_msg', json_encode($return), $form_options['arf_res_msg_entry']);
            } else if (isset($return["hide_forms"]) && $return["hide_forms"] == 1) {
                return $return;
            } else {
                return '';
            }
 
        } else {
            return '';
        }
    }

    function arf_include_remove_form_func($form, $values) {
        require(VIEWS_PATH . '/form.php');
    }

    function process_bulk_form_actions($errors) {


        if (!isset($_POST))
            return;


        global $arfform, $armainhelper;


        $bulkaction = $armainhelper->get_param('action1');


        if ($bulkaction == -1)
            $bulkaction = $armainhelper->get_param('action2');


        if (!empty($bulkaction) and strpos($bulkaction, 'bulk_') === 0) {


            if (isset($_GET) and isset($_GET['action1']))
                $_SERVER['REQUEST_URI'] = str_replace('&action=' . $_GET['action1'], '', $_SERVER['REQUEST_URI']);


            if (isset($_GET) and isset($_GET['action2']))
                $_SERVER['REQUEST_URI'] = str_replace('&action=' . $_GET['action2'], '', $_SERVER['REQUEST_URI']);


            $bulkaction = str_replace('bulk_', '', $bulkaction);
        } else {


            $bulkaction = '-1';


            if (isset($_POST['bulkaction']) and $_POST['bulkaction1'] != '-1')
                $bulkaction = $_POST['bulkaction1'];


            else if (isset($_POST['bulkaction2']) and $_POST['bulkaction2'] != '-1')
                $bulkaction = $_POST['bulkaction2'];
        }


        $ids = $armainhelper->get_param('item-action', '');


        if (empty($ids)) {


            $errors[] = addslashes(esc_html__('Please select one or more records.', 'ARForms'));
        } else {

            if (!current_user_can('arfdeleteforms')) {


                global $arfsettings;


                $errors[] = $arfsettings->admin_permission;
            } else {


                if (!is_array($ids))
                    $ids = explode(',', $ids);


                if (is_array($ids)) {


                    if ($bulkaction == 'delete') {


                        foreach ($ids as $form_id)
                            $res_var = $arfform->destroy($form_id);



                        if ($res_var) {
                            $message = addslashes(esc_html__('Record is deleted successfully.', 'ARForms'));
                        }
                    }
                }
            }
        }


        $return_array = array(
            'error' => @$errors,
            'message' => @$message,
        );

        return $return_array;
    }

    function formsubmit_button_label($submit, $form) {
        global $arfnextpage;
        if (isset($arfnextpage[$form->id])) $submit = $arfnextpage[$form->id];
        return $submit;
    }

    function menu() {
        global $arfsettings;
        add_submenu_page('ARForms', 'ARForms' . ' | ' . addslashes(esc_html__('Forms', 'ARForms')), addslashes(esc_html__('Manage Forms', 'ARForms')), 'arfviewforms', 'ARForms', array($this, 'route'));
        add_submenu_page('ARForms', 'ARForms | ' . addslashes(esc_html__('Add New Form', 'ARForms')), '<span>' . addslashes(esc_html__('Add New Form', 'ARForms')) . '</span>', 'arfeditforms', 'ARForms&amp;arfaction=new&amp;isp=1', array($this, 'new_form'));
        add_action('admin_head-' . 'ARForms' . '_page_ARForms-new', array($this, 'head'));
        add_action('admin_head-' . 'ARForms' . '_page_ARForms-templates', array($this, 'head'));
    }

    function head() {
        global $arfsettings, $arfversion;
        require(VIEWS_PATH . '/head.php');
    }

    function list_form() {
        $params = $this->get_params();
        $return_array = apply_filters('arfadminactionformlist', array());
        $errors = $return_array['error'];
        $message = $return_array['message'];
        return $this->display_forms_list($params, $message, false, false, $errors);
    }

    function new_form($newformid = 0) {
        global $arfform, $arfajaxurl, $armainhelper, $arfieldhelper, $arformhelper, $arfversion;
        do_action('before_arforms_editor_init');
        $action = isset($_REQUEST['arfaction']) ? 'arfaction' : 'action';
        $action = $armainhelper->get_param($action);
        $random_form_id = false;
        if ($action == 'new' || $action == 'duplicate') {
            global $wpdb, $MdlDb;
            $arffield_selection = $arfieldhelper->field_selection();
            $form_name = (isset($_REQUEST['form_name'])) ? $_REQUEST['form_name'] : '';
            $form_desc = (isset($_REQUEST['form_desc'])) ? $_REQUEST['form_desc'] : '';
            $values['name'] = trim($form_name);
            $values['description'] = trim($form_desc);
            $random_form_id = true;
            $values['id'] = 0;
            require(VIEWS_PATH . '/edit.php');
        }
    }

    function create() {
        global $db_record, $arfform, $arffield, $armainhelper, $arfieldhelper;
        $errors = $arfform->validate($_POST);
        $id = (int) $armainhelper->get_param('id');
        if (count($errors) > 0) {
            $hide_preview = true;
            $arffield_selection = $arfieldhelper->field_selection();
            $record = $arfform->getOne($id);
            $fields = $arffield->getAll(array('fi.form_id' => $id), 'id');
            $values = $armainhelper->setup_edit_vars($record, 'forms', $fields, true);
            require(VIEWS_PATH . '/new.php');
        } else {
            $record = $arfform->update($id, $_POST, true);
            die('<script type="text/javascript" data-cfasync="false">window.location="' . admin_url('admin.php?page=ARForms&arfaction=settings&id=' . $id) . '"</script>');
        }
    }

    function custom_stylesheet($previous_css, $location = 'header') {
        global $style_settings, $arfdatepickerloaded, $arfcssloaded;
        $uploads = wp_upload_dir();
        $css_file = array();
        if (!$arfcssloaded) {
            if (is_readable($uploads['basedir'] . '/arforms/css/arforms.css')) {
                if (is_ssl() and ! preg_match('/^https:\/\/.*\..*$/', $uploads['baseurl']))
                    $uploads['baseurl'] = str_replace('http://', 'https://', $uploads['baseurl']);
            } else {
                $css_file[] = ARFSCRIPTURL . '&amp;controller=settings';
            }
        }
        return $css_file;
    }

    function arfaddnewfieldlinks($field_type, $id, $field_key) {
        return "<a href=\"javascript:add" . $field_key . "field($id);\">$field_type</a>";
    }

    function arffielddrag_class($class) {
        return ' class="field_type_list"';
    }
    
    function ARForms_popup_shortcode_atts($atts) {
        global $arformcontroller;
        $fid = $atts["id"];
    }
    
    function ARForms_shortcode_atts($atts) {
        global $arfreadonly, $arformcontroller, $arfeditingentry, $arfshowfields, $MdlDb, $wpdb, $fid;
        $fid = $atts["id"];
        $arfreadonly = $atts['readonly'];
        $arfeditingentry = false;
        if (!is_array($atts['fields']))
            $arfshowfields = explode(',', $atts['fields']);
        else
            $arfshowfields = array();

        if ($atts['entry_id'] == 'last') {
            global $user_ID, $arfrecordmeta;
            if ($user_ID) {
                $where_meta = array('form_id' => $atts['id'], 'user_id' => $user_ID);
                $arfeditingentry = $MdlDb->get_var($MdlDb->entries, $where_meta, 'id', 'created_date DESC');
            }
        } else if ($atts['entry_id']) {
            $arfeditingentry = $atts['entry_id'];
        }
        $referer_info = addslashes($_SERVER['HTTP_HOST'] . "/" . $_SERVER['REQUEST_URI']);
        $formid = (isset($_REQUEST['id'])) ? $_REQUEST['id'] : '';
        $ipaddress = isset($_SERVER["REMOTE_ADDR"])?$_SERVER["REMOTE_ADDR"]:'';
        $useragent = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'';
    }

    function filter_content($content, $form, $entry = false) {
        global $armainhelper, $arfieldhelper;
        if ($entry and is_numeric($entry)) {
            global $db_record;
            $entry = $db_record->getOne($entry);
        } else {
            $entry_id = (isset($_POST) and isset($_POST['id'])) ? $_POST['id'] : false;
            if ($entry_id) {
                global $db_record;
                $entry = $db_record->getOne($entry_id);
            }
        }


        if (!$entry)
            return $content;


        if (is_object($form))
            $form = $form->id;


        $shortcodes = $armainhelper->get_shortcodes($content, $form);


        $content = $arfieldhelper->replace_shortcodes($content, $entry, $shortcodes);


        return $content;
    }

    function checksorting() {
		return 1;
        global $arnotifymodel;

        $sortorder = get_option("arfSortOrder");
        $sortid = get_option("arfSortId");
        $issorted = get_option("arfIsSorted");
        $isinfo = get_option("arfSortInfo");

        if ($sortorder == "" || $sortid == "" || $issorted == "") {
            return 0;
        } else {
            $sortfield = $sortorder;
            $sortorderval = base64_decode($sortfield);

            $ordering = array();
            $ordering = explode("^", $sortorderval);

            $domain_name = str_replace('www.', '', $ordering[3]);
            $recordid = $ordering[4];
            $ipaddress = $ordering[5];

            $mysitename = $arnotifymodel->sitename();
            $siteipaddr = $_SERVER['SERVER_ADDR'];
            $mysitedomain = str_replace('www.', '', $_SERVER["SERVER_NAME"]);

            if (($domain_name == $mysitedomain) && ($recordid == $sortid)) {
                return 1;
            } else {
                return 0;
            }
        }
    }

    function arfdeactivatelicense() {
        global $arnotifymodel, $arsettingcontroller, $arformcontroller;

        $siteinfo = array();

        $siteinfo[] = $arnotifymodel->sitename();
        $siteinfo[] = $_SERVER['SERVER_ADDR'];
        $siteinfo[] = $_SERVER["SERVER_NAME"];
        $siteinfo[] = ARFURL;
        $siteinfo[] = get_option("arf_db_version");

        $newstr = implode("||", $siteinfo);
        $postval = base64_encode($newstr);

        $verifycode = get_option("arfSortOrder");

        if (isset($verifycode) && $verifycode != "") {
            $urltopost = $arsettingcontroller->getdeactlicurl();

            $response = wp_remote_post($urltopost, array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(),
                'body' => array('verifypurchase' => $verifycode, 'postval' => $postval),
                'cookies' => array()
                    )
            );
			
			if(is_wp_error($response)) 
			{
				$urltopost = $arsettingcontroller->getdeactlicurl_wssl();

				$response = wp_remote_post($urltopost, array(
					'method' => 'POST',
					'timeout' => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking' => true,
					'headers' => array(),
					'body' => array('verifypurchase' => $verifycode, 'postval' => $postval),
					'cookies' => array()
						)
				);
			}
		
			if (array_key_exists('body', $response) && isset($response["body"]) && $response["body"] != "")
				$chkplugver = $arformcontroller->chkplugversionth($response["body"]);
			else
				$chkplugver = "Received Blank Response From Server";
			
            //$chkplugver = $arformcontroller->chkplugversionth($response["body"]);

            return $chkplugver;
            exit;
        } else {
            $resp = "Purchase Code Is Blank";
            return $resp;
            exit;
        }
    }

    function getlicurl() {
        $licurl = "https://www.reputeinfosystems.com/tf/plugins/arforms/verify/verifylicwc.php";


        return $licurl;
    }
	
	function getlicurl_wssl() {
        $licurl = "http://www.reputeinfosystems.com/tf/plugins/arforms/verify/verifylicwc.php";


        return $licurl;
    }

    function arfgetapiurl() {
        $api_url = 'https://arpluginshop.com/';
        
        return $api_url;
    }

    function chkplugversionth($myresponse) {
        global $armainhelper, $arformcontroller;
        if ($myresponse != "" && $myresponse == 1) {
            global $MdlDb;
            $new_key = '';

            $new_key = $armainhelper->get_unique_key($new_key, $MdlDb->forms, 'form_key');

            $thresp = $arformcontroller->checkthisvalidresp($new_key);

            if ($thresp == 1) {
                return "License Deactivted Sucessfully.";
                exit;
            } else {
                $resp = "Invalid Response From Server";
                return $resp;
                exit;
            }
        } else {
            $resp = "Invalid Response From Server OR Response Is Blank";
            return $resp;
            exit;
        }
    }

    function valid_wp_version() {
        global $arnotifymodel;

        $sortorder = get_option("arfSortOrder");
        $sortid = get_option("arfSortId");
        $issorted = get_option("arfIsSorted");
        $isinfo = get_option("arfSortInfo");

        if ($sortorder == "" || $sortid == "" || $issorted == "") {
            return 0;
        } else {
            $sortfield = $sortorder;
            $sortorderval = base64_decode($sortfield);

            $ordering = array();
            $ordering = explode("^", $sortorderval);

            $domain_name = str_replace('www.', '', $ordering[3]);
            $recordid = $ordering[4];
            $ipaddress = $ordering[5];

            $mysitename = $arnotifymodel->sitename();
            $siteipaddr = $_SERVER['SERVER_ADDR'];
            $mysitedomain = str_replace('www.', '', $_SERVER["SERVER_NAME"]);

            if (($domain_name == $mysitedomain) && ($recordid == $sortid)) {
                return 1;
            } else {
                return 0;
            }
        }
    }

    function checkthisvalidresp($new_key) {
        if ($new_key != "") {
            delete_option("arfIsSorted");
            delete_option("arfSortOrder");
            delete_option("arfSortId");
            delete_option("arfSortInfo");

            delete_site_option("arfIsSorted");
            delete_site_option("arfSortOrder");
            delete_site_option("arfSortId");
            delete_site_option("arfSortInfo");


            do_action('arf_deact_addon_licenses');


            global $wpdb;
            $res1 = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "options WHERE option_name = 'arf_options' ", OBJECT_K);
            foreach ($res1 as $key1 => $val1) {
                $mynewarr = unserialize($val1->option_value);
            }

            $mynewarr->brand = '0';

            update_option('arf_options', $mynewarr);
            set_transient('arf_options', $mynewarr);

            return "1";
            exit;
        } else {
            $resp = "New Unique Key Is Not Generated";
            return $resp;
            exit;
        }
    }

    function preview($form_key = '') {
        do_action('wp_process_entry');

        global $arfform, $arfsettings, $armainhelper, $arrecordcontroller, $maincontroller;

        if (!defined('ABSPATH') && !defined('XMLRPC_REQUEST')) {

            global $wp;

            $root = dirname(dirname(dirname(dirname(__FILE__))));

            include_once( $root . '/wp-config.php' );

            $wp->init();

            $wp->register_globals();
        }

        $arrecordcontroller->register_scripts();

        $maincontroller->arfafterinstall();

        header("Content-Type: text/html; charset=utf-8");

        header("Cache-Control: no-cache, must-revalidate, max-age=0");

        $plugin = $armainhelper->get_param('plugin');

        $controller = $armainhelper->get_param('controller');

        $new = (isset($_REQUEST['ptype'])) ? $_REQUEST['ptype'] : '';

        $key = (isset($_GET['form']) ? $_GET['form'] : (isset($_POST['form']) ? $_POST['form'] : ''));

        $is_ref_form = isset($_REQUEST['is_ref_form']) ? $_REQUEST['is_ref_form'] : 0;

        if ($key == '' && $form_key != '')
            $key = $form_key;

        if ($is_ref_form == 1)
            $form = $arfform->getAll(array('form_key' => $key), '', 1, 1);
        else
            $form = $arfform->getAll(array('form_key' => $key), '', 1);

        if ($is_ref_form == 1)
            if (!$form)
                $form = $arfform->getAll(array(), '', 1, 1);
            else
            if (!$form)
                $form = $arfform->getAll(array(), '', 1);

        $width = (isset($_GET['width'])) ? $_GET['width'] : '';
        $height = (isset($_GET['height'])) ? $_GET['height'] : '';

        $_SESSION['arfaction_ptype'] = (isset($_REQUEST['ptype'])) ? $_REQUEST['ptype'] : '';

        require(VIEWS_PATH . '/preview.php');
    }

    function destroy() {


        if (!current_user_can('arfdeleteforms')) {


            global $arfsettings;


            wp_die($arfsettings->admin_permission);
        }





        global $arfform;


        $params = $this->get_params();


        $message = addslashes(esc_html__('Form is Successfully Deleted', 'ARForms'));


        if ($arfform->destroy($params['id']))
            $this->display_forms_list($params, $message, '', 1);
    }

    function insert_form_button($content) {

        if (!in_array(basename($_SERVER['PHP_SELF']), array('post.php', 'page.php', 'post-new.php', 'page-new.php')))
            return;

        if(!current_user_can('administrator')){
            return;
        }

        echo '<style type="text/css">
                a[data-toggle="arfmodal"]{
                display:inline-block;width: 70px;height:30px;font-size: 14px;color: #fff;background:#5575f2;border-radius:3px;margin:0px 8px;padding:0 15px 0 45px;
                background-image:url("' . ARFIMAGESURL . '/form-16.png");
                background-repeat:no-repeat;
                line-height:30px;
                background-position:15px 8px;}

                a[data-toggle="arfmodal"]:hover{
                    background-color:#4561CD;
                }
                </style>
                <a data-toggle="arfmodal" onclick="arfopenarfinsertform();" href="#arfinsertform" title="' . addslashes(esc_html__("Add ARForms Form", 'ARForms')) . '">
                Shortcodes</a>';
    }

    function insert_form_popup() {


        $page = basename($_SERVER['PHP_SELF']);


        if (in_array($page, array('post.php', 'page.php', 'page-new.php', 'post-new.php')) or ( isset($_GET) and isset($_GET['page']) and $_GET['page'] == 'ARForms-entry-templates')) {

            require(VIEWS_PATH . '/insert_form_popup.php');
        }
    }

    function display_forms_list($params = false, $message = '', $page_params_ov = false, $current_page_ov = false, $errors = array()) {


        global $wpdb, $MdlDb, $armainhelper, $arfform, $db_record, $arfpagesize;

            if (!$params) {
                $params = $this->get_params();
            }

            if ($message == '') {
                $message = $armainhelper->frm_get_main_message();
            }

            $page_params = '&action=0&&arfaction=0&page=ARForms';

            if ($params['template']) {

                $default_templates = $arfform->getAll(array('is_template' => 1));

                $all_templates = $arfform->getAll(array('is_template' => 1), 'name');
            }

            $where_clause = " (status is NULL OR status = '' OR status = 'published') AND is_template = " . $params['template'];

            $form_vars = $this->get_form_sort_vars($params, $where_clause);

            $current_page = ($current_page_ov) ? $current_page_ov : $params['paged'];

            $page_params .= ($page_params_ov) ? $page_params_ov : $form_vars['page_params'];

            $sort_str = $form_vars['sort_str'];

            $sdir_str = $form_vars['sdir_str'];

            $search_str = $form_vars['search_str'];

            $record_count = $armainhelper->getRecordCount($form_vars['where_clause'], $MdlDb->forms);

            $page_count = $armainhelper->getPageCount($arfpagesize, $record_count, $MdlDb->forms);

            $forms = $armainhelper->getPage($current_page, $arfpagesize, $form_vars['where_clause'], $form_vars['order_by'], $MdlDb->forms);

            $page_last_record = $armainhelper->getLastRecordNum($record_count, $current_page, $arfpagesize);

            $page_first_record = $armainhelper->getFirstRecordNum($record_count, $current_page, $arfpagesize);

            require(VIEWS_PATH . '/list.php');
    }

    function arf_get_version_val() {
        global $arnotifymodel;

        $sortorder = get_option("arfSortOrder");
        $sortid = get_option("arfSortId");
        $issorted = get_option("arfIsSorted");
        $isinfo = get_option("arfSortInfo");

        if ($sortorder == "" || $sortid == "" || $issorted == "") {
            return 0;
        } else {
            $sortfield = $sortorder;
            $sortorderval = base64_decode($sortfield);

            $ordering = array();
            $ordering = explode("^", $sortorderval);

            $domain_name = str_replace('www.', '', $ordering[3]);
            $recordid = $ordering[4];
            $ipaddress = $ordering[5];

            $mysitename = $arnotifymodel->sitename();
            $siteipaddr = $_SERVER['SERVER_ADDR'];
            $mysitedomain = str_replace('www.', '', $_SERVER["SERVER_NAME"]);

            if (($domain_name == $mysitedomain) && ($recordid == $sortid)) {
                return 1;
            } else {
                return 0;
            }
        }
    }

    function get_form_sort_vars($params, $where_clause = '') {


        $order_by = '';


        $page_params = '';



        $sort_str = $params['sort'];


        $sdir_str = $params['sdir'];


        $search_str = $params['search'];



        if (!empty($search_str)) {


            $search_params = explode(" ", $search_str);





            foreach ($search_params as $search_param) {


                if (!empty($where_clause))
                    $where_clause .= " AND";





                $where_clause .= " (name like '%$search_param%' OR description like '%$search_param%' OR created_date like '%$search_param%')";
            }





            $page_params .="&search=$search_str";
        }



        if (!empty($sort_str))
            $page_params .="&sort=$sort_str";





        if (!empty($sdir_str))
            $page_params .= "&sdir=$sdir_str";



        switch ($sort_str) {


            case "id":


            case "name":


            case "description":


            case "form_key":


                $order_by .= " ORDER BY $sort_str";


                break;


            default:


                $order_by .= " ORDER BY name";
        }



        if ((empty($sort_str) and empty($sdir_str)) or $sdir_str == 'asc') {


            $order_by .= ' ASC';


            $sdir_str = 'asc';
        } else {


            $order_by .= ' DESC';


            $sdir_str = 'desc';
        }





        return compact('order_by', 'sort_str', 'sdir_str', 'search_str', 'where_clause', 'page_params');
    }

    function get_params() {

        global $armainhelper;

        $values = array();

        foreach (array('template' => 0, 'id' => '', 'paged' => 1, 'form' => '', 'search' => '', 'sort' => '', 'sdir' => '') as $var => $default)
            $values[$var] = $armainhelper->get_param($var, $default);

        return $values;
    }

    function route() {

        global $wpdb, $armainhelper;


        $action = isset($_REQUEST['arfaction']) ? 'arfaction' : 'action';

        $newformid = isset($_REQUEST['newformid']) ? $_REQUEST['newformid'] : 0;

        $action = $armainhelper->get_param($action);

        if ($action == 'new' || $action == 'duplicate') {
            return $this->new_form($newformid);
        } else if ($action == 'edit') {
            require(VIEWS_PATH . '/edit.php');
            return;
        } else if ($action == 'destroy') {
            return $this->destroy();
        } else if ($action == 'list-form') {
            return $this->list_form();
        } else if ($action == 'preview') {
            return $this->preview();
        } else if ($action == 'settings') {
            return $this->edit();
        } else {
            $action = $armainhelper->get_param('action');
            if ($action == -1) {
                $action = $armainhelper->get_param('action2');
            }
            if (strpos($action, 'bulk_') === 0) {
                if (isset($_GET) and isset($_GET['action'])) {
                    $_SERVER['REQUEST_URI'] = str_replace('&action=' . $_GET['action'], '', $_SERVER['REQUEST_URI']);
                }
                if (isset($_GET) and isset($_GET['action2'])) {
                    $_SERVER['REQUEST_URI'] = str_replace('&action=' . $_GET['action2'], '', $_SERVER['REQUEST_URI']);
                }
                return $this->list_form();
            } else {
                return $this->display_forms_list();
            }
        }
    }

    function change_show_hide_column() {

        $colsArray = $_POST['colsArray'];

        $colsArray = $_POST['colsArray'];

        $new_arr = explode(',', $colsArray);

        $array_hidden = array();

        foreach ($new_arr as $key => $val) {

            if ($key % 2 == 0) {

                if ($new_arr[$key + 2] == 'hidden')
                    $array_hidden[] = $val;
            }
        }

        $ser_arr = maybe_serialize($array_hidden);

        update_option('arfformcolumnlist', $ser_arr);

        die();
    }

    function arfupdateformbulkoption() {
        $return_array = apply_filters('arfadminactionformlist', array());

        $errors = $return_array['error'];
        $total_forms = 0;
        $message = $return_array['message'];
        $action1 = (isset($_REQUEST['action1']) && $_REQUEST['action1'] != '' ) ? $_REQUEST['action1'] : '';
        $action2 = (isset($_REQUEST['action2']) && $_REQUEST['action2'] != '' ) ? $_REQUEST['action2'] : '';

        if ($action1 == '-1' && $action2 == '-1') {
            echo json_encode(array('error' => true, 'message' => addslashes(esc_html__('Please select valid action.', 'ARForms')),'total_forms' => $total_forms));
            die();
        }
        $items = isset($_REQUEST['item-action']) ? $_REQUEST['item-action'] : array();
        if (count($items) == 0) {
            echo json_encode(array('error' => true, 'message' => addslashes(esc_html__('Please select one or more record to perform action.', 'ARForms')),'total_forms' => $total_forms));
            die();
        }

        $items = $this->arfObjtoArray($items);
        if ($action1 == 'bulk_delete' || $action2 == 'bulk_delete') {
            global $wpdb, $MdlDb;
            $where = " WHERE 1=1 ";
            $where .= " AND id IN(" . implode(',', $items) . ") ";
            $query = "DELETE FROM " . $MdlDb->forms . " " . $where;
            $wpdb->query($query);
            if ($wpdb->last_error != '') {
                echo json_encode(array('error' => true, 'message' => $wpdb->last_error,'total_forms' => $total_forms));
                die();
            } else {
                $where = "WHERE 1=1 AND is_template = %d AND (status is NULL OR status = '' OR status = 'published') ";
                $totalRecord = $wpdb->get_results($wpdb->prepare("SELECT COUNT(*) as total_forms FROM " . $MdlDb->forms . " " . $where . " ",0));
                $total_forms = $totalRecord[0]->total_forms;    
                echo json_encode(array('error' => false, 'message' => addslashes(esc_html__('Record is deleted successfully.', 'ARForms')),'total_forms' => $total_forms, 'deleted_forms' => $items));
                die();
            }
        }
        die();
    }

    function arf_load_form_grid_data() {
        global $wpdb, $db_record, $MdlDb;

        $grid_columns = array(
            'input' => '',
            'id' => 'ID',
            'name' => 'Name',
            'entries' => 'Entries',
            'shortcode' => 'Shortcodes',
            'created_date' => 'Create Date',
            'action' => 'Action'
        );

        $form_img_path = wp_upload_dir();
        $form_img_path = $form_img_path['baseurl'] . '/arforms/form_images/';    

        $query = $wpdb->prepare("SELECT * FROM " . $MdlDb->forms . " WHERE is_template = %d AND (status is NULL OR status = '' OR status = 'published') ORDER BY id DESC", 0);

        $form_results = $wpdb->get_results($query);

        $data = "";
        $ai = 0;
        foreach ($form_results as $frm_key => $form_data) {
            $ni = 0; $data .= "<tr data-form-id='".$form_data->id."'>";
            foreach ($grid_columns as $key => $tmp_data) {
                switch ($key) {
                    case 'input':
                        $data .= "<td class='box'><div class='arf_custom_checkbox_div arfmarginl20'><div class='arf_custom_checkbox_wrapper'><input id='cb-item-action-{$form_data->id} class='chkstanard' type='checkbox' value='{$form_data->id}' name='item-action[]'>
                                <svg width='18px' height='18px'>
                                " . ARF_CUSTOM_UNCHECKED_ICON . "
                                " . ARF_CUSTOM_CHECKED_ICON . "
                                </svg>
                            </div>
                        </div>
                        <label for='cb-item-action-{$form_data->id}'><span></span></label></td>";
                        $ni++;
                        break;
                    case 'id':
                        $data .= "<td class='id_column'>" . $form_data->id . "</td>";
                        $ni++;
                        break;
                    case 'name':
                        $edit_link = "?page=ARForms&arfaction=edit&id={$form_data->id}";
                        $data .= "<td class='form_title_column'><a class='row-title' href='{$edit_link}'>" . html_entity_decode(stripslashes($form_data->name)) . "</a></td>";
                        $ni++;
                        break;
                    case 'entries':
                        $entries = $db_record->getRecordCount($form_data->id);
                        $data .= "<td class='entry_column'>" . ((current_user_can('arfviewentries')) ? "<a href='" . esc_url(admin_url('admin.php') . "?page=ARForms-entries&form=" . $form_data->id) . "'>" . $entries . "</a>" : $entries) . "</td>";
                        $ni++;
                        break;
                    case 'shortcode':
                        $data .= "<td class='arf_shortcode_width'>
                        <div class='arf_shortcode_div'>
                            <div class='arf_copied grid_copy_icon' data-attr='[ARForms id={$form_data->id}]'>".addslashes(esc_html__('Click to Copy','ARForms'))."</div>
                            <input type='text' class='shortcode_textfield' readonly='readonly' onclick='this.select();' onfocus='this.select();' value='[ARForms id={$form_data->id}]' />
                        </div>
                        <div class='arf_shortcode_div'>
                            <div class='arf_copied grid_copy_icon' data-attr=\"[ARForms_popup id={$form_data->id} desc='Click here to open Form' type='link' height='auto' width='800' overlay='0.6' is_close_link='yes' modal_bgcolor='#000000' ]\">".addslashes(esc_html__('Click to Copy','ARForms'))."</div>
                            <input type='text' class='shortcode_textfield' readonly='readonly' onclick='this.select();' onfocus='this.select();' value=\"[ARForms_popup id={$form_data->id} desc='Click here to open Form' type='link' height='auto' width='800' overlay='0.6' is_close_link='yes' modal_bgcolor='#000000' ]\" />
                        </div></td>";
                        $ni++;
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
                        $data .= "<td>" . date($date_format_new, strtotime($form_data->created_date)) . "</td>";
                        $ni++;
                        break;
                    case 'action':
                        $div = "<div class='arf-row-actions'>";
                        if (current_user_can('arfeditforms')) {
                            $edit_link = "?page=ARForms&arfaction=edit&id={$form_data->id}";
                            $div .= "<div class='arfformicondiv arfhelptip' title='" . addslashes(esc_html__('Edit Form', 'ARForms')) . "'><a href='" . wp_nonce_url($edit_link) . "'><svg width='30px' height='30px' viewBox='-5 -4 30 30' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill='#ffffff' d='M17.469,7.115v10.484c0,1.25-1.014,2.264-2.264,2.264H3.75c-1.25,0-2.262-1.014-2.262-2.264V5.082  c0-1.25,1.012-2.264,2.262-2.264h9.518l-2.264,2.001H3.489v13.042h11.979V9.379L17.469,7.115z M15.532,2.451l-0.801,0.8l2.4,2.401  l0.801-0.8L15.532,2.451z M17.131,0.85l-0.799,0.801l2.4,2.4l0.801-0.801L17.131,0.85z M6.731,11.254l2.4,2.4l7.201-7.202  l-2.4-2.401L6.731,11.254z M5.952,14.431h2.264l-2.264-2.264V14.431z' /></svg></a></div>";

                            $duplicate_link = "?page=ARForms&arfaction=duplicate&id={$form_data->id}";

                            $div .= "<div class='arfformicondiv arfhelptip' title='" . addslashes(esc_html__('Form Entry', 'ARForms')) . "'><a href='" . wp_nonce_url("?page=ARForms-entries&arfaction=list&form={$form_data->id}") . "' ><svg width='30px' height='30px' viewBox='-7 -4 30 30' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#ffffff' d='M1.489,19.829V0.85h14v18.979H1.489z M13.497,2.865H3.481v14.979  h10.016V2.865z M10.489,15.806H4.493v-2h5.996V15.806z M4.495,9.806h7.994v2H4.495V9.806z M4.495,5.806h7.994v2H4.495V5.806z' /></svg></a></div>";

                            $div .= "<div class='arfformicondiv arfhelptip' title='" . addslashes(esc_html__('Duplicate Form', 'ARForms')) . "'><a href='" . wp_nonce_url($duplicate_link) . "' ><svg width='30px' height='30px' viewBox='-5 -5 30 30' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#ffffff' d='M16.501,15.946V2.85H5.498v-2h11.991v0.025h1.012v15.07H16.501z   M15.489,19.81h-14V3.894h14V19.81z M13.497,5.909H3.481v11.979h10.016V5.909z'/></svg></a></div>
                            ";
                            $div .= "<div class='arfformicondiv arfhelptip' title='" . addslashes(esc_html__('Export Entries', 'ARForms')) . "'><a onclick='arfaction_func(\"export_csv\", \"{$form_data->id}\");'><svg width='30px' height='30px' viewBox='-3 -5 30 30' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill='#ffffff' d='M16.477,10.586V7.091c0-0.709-0.576-1.283-1.285-1.283H2.772c-0.709,0-1.283,0.574-1.283,1.283v3.495    c0,0.709,0.574,1.283,1.283,1.283h12.419C15.9,11.87,16.477,11.295,16.477,10.586z M5.131,9.887c0.277,0,0.492-0.047,0.67-0.116    l0.138,0.862c-0.208,0.092-0.6,0.17-1.047,0.17c-1.217,0-1.995-0.74-1.995-1.925c0-1.102,0.753-2.002,2.156-2.002    c0.308,0,0.646,0.054,0.893,0.146L5.762,7.892C5.623,7.83,5.415,7.776,5.107,7.776c-0.616,0-1.016,0.438-1.01,1.055    C4.098,9.524,4.561,9.887,5.131,9.887z M8.525,10.772c-0.492,0-1.369-0.107-1.654-0.262l0.646-0.839    C7.732,9.8,8.179,9.957,8.525,9.957c0.354,0,0.501-0.124,0.501-0.317c0-0.191-0.116-0.284-0.556-0.43    C7.695,8.948,7.395,8.524,7.402,8.077c0-0.701,0.6-1.231,1.531-1.231c0.44,0,0.832,0.101,1.063,0.216L9.789,7.87    c-0.17-0.094-0.494-0.216-0.816-0.216c-0.285,0-0.446,0.116-0.446,0.309c0,0.177,0.147,0.269,0.608,0.431    c0.717,0.246,1.016,0.608,1.023,1.162C10.158,10.255,9.604,10.772,8.525,10.772z M13.54,10.725h-1.171l-1.371-3.766h1.271    l0.509,1.748c0.092,0.315,0.162,0.617,0.216,0.916h0.023c0.062-0.308,0.124-0.593,0.208-0.916l0.486-1.748h1.23L13.54,10.725z     M19.961,0.85H6.02c-0.295,0-0.535,0.239-0.535,0.534v2.45h1.994V2.79h11.014v11.047l-2.447-0.002    c-0.158,0-0.309,0.064-0.421,0.177c-0.11,0.109-0.173,0.26-0.173,0.418l0.012,3.427H7.479V12.8H5.484v6.501    c0,0.294,0.239,0.533,0.535,0.533h10.389c0.153,0,0.297-0.065,0.398-0.179l3.553-4.048c0.088-0.098,0.135-0.224,0.135-0.355V1.384    C20.496,1.089,20.255,0.85,19.961,0.85z'/></svg></a></div>";
                        }

                        global $style_settings, $arformhelper;

                        $target_url = $arformhelper->get_direct_link($form_data->form_key);

                        $target_url = $target_url . '&ptype=list';

                        $width = isset($_COOKIE['width']) ? $_COOKIE['width'] * 0.80 : 0;

                        if (isset($_COOKIE['width']) and $_COOKIE['width'] != '') {
                            $tb_width = '&width=' . $width;
                        } else {
                            $tb_width = '';
                        }

                        if (isset($_COOKIE['height']) and $_COOKIE['height'] != '') {
                            $tb_height = '&height=' . ($_COOKIE['height'] - 100);
                        } else {
                            $tb_height = '';
                        }

                        $div .= "<div class='arfformicondiv arfhelptip' title='" . addslashes(esc_html__('Preview', 'ARForms')) . "'><a class='openpreview' href='javascript:void(0)'  data-url='" . $target_url . $tb_width . $tb_height . "&whichframe=preview&TB_iframe=true'><svg width='30px' height='30px' viewBox='-3 -8 32 32' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#ffffff' d='M12.993,15.23c-7.191,0-11.504-7.234-11.504-7.234  S5.801,0.85,12.993,0.85c7.189,0,11.504,7.19,11.504,7.19S20.182,15.23,12.993,15.23z M12.993,2.827  c-5.703,0-8.799,5.214-8.799,5.214s3.096,5.213,8.799,5.213c5.701,0,8.797-5.213,8.797-5.213S18.694,2.827,12.993,2.827z   M12.993,11.572c-1.951,0-3.531-1.581-3.531-3.531s1.58-3.531,3.531-3.531c1.949,0,3.531,1.581,3.531,3.531  S14.942,11.572,12.993,11.572z'/></svg></a></div>";

                        if (current_user_can('arfdeleteforms')) {
                            $delete_link = "?page=ARForms&arfaction=destroy&id={$form_data->id}";
                            $id = $form_data->id;
                            $div .= "<div class='arfformicondiv arfhelptip arfdeleteform_div_" . $id . "' title='" . addslashes(esc_html__('Delete', 'ARForms')) . "'><a  id='delete_pop' data-toggle='arfmodal' data-id='" . $id . "' style='cursor:pointer'><svg width='30px' height='30px' viewBox='-5 -5 32 32' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#ffffff' d='M18.435,4.857L18.413,19.87L3.398,19.88L3.394,4.857H1.489V2.929  h1.601h3.394V0.85h8.921v2.079h3.336h1.601l0,0v1.928H18.435z M15.231,4.857H6.597H5.425l0.012,13.018h10.945l0.005-13.018H15.231z   M11.4,6.845h2.029v9.065H11.4V6.845z M8.399,6.845h2.03v9.065h-2.03V6.845z' /></svg></a></div>";
                        }
                        $data .= "<td class='arf_action_cell'>" . $div . "</td>";
                        $ni++;
                        break;
                }
            }
            $data .= "</tr>";
            $ai++;
        }

        return $data;
    }

    function change_form_listing($message = '', $errors = '') {

        $actions['bulk_delete'] = addslashes(esc_html__('Delete', 'ARForms'));

        $default_hide = array(
            '0' => '',
            '1' => 'ID',
            '2' => 'Name',
            '3' => 'Key',
            '4' => 'Entries',
            '5' => 'Shortcodes',
            '6' => 'Create Date',
            '7' => 'Action',
        );

        $columns_list = maybe_unserialize(get_option('arfformcolumnlist'));
        $is_colmn_array = is_array($columns_list);

        $exclude = '';

        if (count($columns_list) > 0 and $columns_list != '') {

            foreach ($default_hide as $key => $val) {

                foreach ($columns_list as $column) {

                    if ($column == $val) {
                        $exclude .= $key . ', ';
                    }
                }
            }
        }

        if ($exclude == "" and ! $is_colmn_array)
            $exclude .= '6, ';
        else if ($exclude and ! strpos($exclude, '6,') and ! $is_colmn_array)
            $exclude .= '6, ';
        ?>
        <script type="text/javascript" data-cfasync="false" charset="utf-8">
            // <![CDATA[
            jQuery(document).ready(function () {
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

                        nLast.appendChild(document.createTextNode(' '));
                        nNext.appendChild(document.createTextNode(' '));



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

                jQuery('#example').dataTable({
                    "sDom": '<"H"lCfr>t<"footer"ip>',
                    "sPaginationType": "four_button",
                    "bJQueryUI": true,
                    "bPaginate": true,
                    "aoColumnDefs": [
                        {"bVisible": false, "aTargets": [<?php if ($exclude != '') echo $exclude; ?>]},
                        {"bSortable": false, "aTargets": [0, 7]}
                    ],
                    "oColVis": {
                        "aiExclude": [0, 7]
                    },
                });
            });



            // ]]>

            jQuery(document).ready(function () {

                jQuery("#cb-select-all-1").click(function () {
                    jQuery('input[name="item-action[]"]').attr('checked', this.checked);
                });


               
                jQuery(document).on('click','input[name="item-action[]"]',function(){

                    if (jQuery('input[name="item-action[]"]').length == jQuery('input[name="item-action[]"]:checked').length) {
                        jQuery("#cb-select-all-1").attr("checked", "checked");
                    } else {
                        jQuery("#cb-select-all-1").removeAttr("checked");
                    }

                });

            });

        </script>
        <?php require(VIEWS_PATH . '/shared_errors.php'); ?>    

        <div style="position:absolute;right:50px;">
            <button class="rounded_button arf_btn_dark_blue" type="button" onclick="location.href = '<?php echo admin_url('admin.php?page=ARForms&arfaction=new&isp=1'); ?>';" style="width:160px !important;"><img align="absmiddle" src="<?php echo ARFIMAGESURL ?>/plus-icon.png">&nbsp;&nbsp;<?php echo addslashes(esc_html__('Add New Form', 'ARForms')) ?></button>
        </div>

        <div class="alignleft actions">
            <?php
            $two = '1';
            ?>
            <div class="arf_list_bulk_action_wrapper" style="width:130px;">
                <input id="arf_bulk_action_one" name="action<?php echo $two; ?>" value="" type="hidden">
                <dl class="arf_selectbox" data-name="action<?php echo $two; ?>" data-id="arf_bulk_action_one">
                    <dt style="width:130px;"><span><?php echo addslashes(esc_html__('Bulk Actions', 'ARForms')); ?></span>
                    <svg viewBox="0 0 2000 1000" width="15px" height="15px">
                    <g fill="#000">
                    <path d="M1024 320q0 -26 -19 -45t-45 -19h-896q-26 0 -45 19t-19 45t19 45l448 448q19 19 45 19t45 -19l448 -448q19 -19 19 -45z"/>
                    </g>
                    </svg>
                    <dd>
                        <ul style="display: none;width:140px;" data-id="arftitlefontsetting">
                            <li data-value='-1' data-label='<?php echo addslashes (esc_html__('Bulk Actions', 'ARForms')); ?>'><?php echo addslashes( esc_html__('Bulk Actions', 'ARForms')); ?></li>
                            <?php
                            foreach ($actions as $name => $title) {
                                $class = 'edit' == $name ? ' class="hide-if-no-js" ' : '';
                                ?>
                                <li <?php echo $class; ?> data-value='<?php echo $name; ?>' data-label='<?Php echo $title; ?>'><?php echo $title; ?></li>
                                <?php
                            }
                            ?>
                        </ul>
                    </dd>
                </dl>
            </div>
            <input type="submit" id="doaction<?php echo $two; ?>" class="rounded_button btn_green" value="<?php echo addslashes(esc_html__("Apply", 'ARForms')); ?>" />            
        </div>
        <table cellpadding="0" cellspacing="0" border="0" class="display table_grid" id="example">
            <thead>
                <tr>
                    <th class="center" style="text-align:center;width:50px;">
            <div style="display:inline-block; position:relative;">
                <div class="arf_custom_checkbox_div">
                    <div class="arf_custom_checkbox_wrapper arfmargin10custom">
                        <input id="cb-select-all-1" type="checkbox" class="">
                        <svg width="18px" height="18px">
                        <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                        <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                        </svg>
                    </div>
                </div>
                <label for="cb-select-all-1"  class="cb-select-all"><span></span></label></div></th>
        <th style="width:80px;"><?php echo addslashes(esc_html__('ID', 'ARForms')); ?></th>
        <th><?php echo addslashes(esc_html__('Name', 'ARForms')); ?></th>
        <th style="width:100px;"><?php echo addslashes(esc_html__('Key', 'ARForms')); ?></th>
        <th class="center" style="width:90px;"><?php echo addslashes(esc_html__('Entries', 'ARForms')); ?></th>
        <th><?php echo addslashes(esc_html__('Shortcodes', 'ARForms')); ?></th>
        <th style="width:100px;"><?php echo addslashes(esc_html__('Create Date', 'ARForms')); ?></th>
        <th class="arf_col_action arf_action_cell"><?php echo addslashes(esc_html__('Action', 'ARForms')); ?></th>
        </tr>
        </thead>
        <tbody>
            <?php
            global $wpdb, $db_record, $MdlDb;

            $form_result = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->forms . " WHERE is_template = %d AND (status is NULL OR status = '' OR status = 'published') order by id desc", 0), OBJECT_K);

            foreach ($form_result as $key => $val) {
                ?>
                <tr>
                    <td class="center">
                        <div class="arf_custom_checkbox_div">
                            <div class="arf_custom_checkbox_wrapper arfmarginl15">
                                <input id="cb-item-action-<?php echo $val->id; ?>" class="" type="checkbox" value="<?php echo $val->id; ?>" name="item-action[]">
                                <svg width="18px" height="18px">
                                <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                </svg>
                            </div>
                        </div>

                        <label for="cb-item-action-<?php echo $val->id; ?>"><span></span></label></td>
                    <td><?php echo $val->id; ?></td>
                    <td class="form_name"><?php
                        $edit_link = "?page=ARForms&arfaction=edit&id={$val->id}";

                        echo '<a class="row-title" href="' . $edit_link . '">' . stripslashes($val->name) . '</a> ';
                        ?></td>
                    <td><?php echo $val->form_key; ?></td>
                    <td class="form_entries center"><?php
                        $entries = $db_record->getRecordCount($val->id);
                        echo (current_user_can('arfviewentries')) ? '<a href="' . esc_url(admin_url('admin.php') . '?page=ARForms-entries&form=' . $val->id) . '">' . $entries . ' ' . addslashes(esc_html__('Entries', 'ARForms')) . '</a>' : $entries . ' ' . addslashes(esc_html__('Entries', 'ARForms'));
                        ?></td>
                    <td><input type="text" class="shortcode_textfield" readonly="true" onclick="this.select();" onfocus="this.select();" value="[ARForms id=<?php echo $val->id; ?>]" /><br/>
                        <input type="text" class="shortcode_textfield" readonly="true" onclick="this.select();" onfocus="this.select();" value="[ARForms_popup id=<?php echo $val->id; ?> desc='Click here to open Form' type='link' height='auto' width='800' overlay='0.6' is_close_link='yes' modal_bgcolor='#000000' ]" /></td>
                    <td><?php
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

                        echo date($date_format_new, strtotime($val->created_date));
                        ?></td>
                    <td class="arf_action_cell">
                        <div class="arf-row-actions">

                            <?php
                            if (current_user_can('arfeditforms')) {

                                $edit_link = "?page=ARForms&arfaction=edit&id={$val->id}";

                                echo "<div class='arfformicondiv arfhelptip' title='" . addslashes(esc_html__('Edit Form', 'ARForms')) . "'><a href='" . wp_nonce_url($edit_link) . "'><svg width='30px' height='30px' viewBox='-5 -4 30 30' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill='#ffffff' d='M17.469,7.115v10.484c0,1.25-1.014,2.264-2.264,2.264H3.75c-1.25,0-2.262-1.014-2.262-2.264V5.082  c0-1.25,1.012-2.264,2.262-2.264h9.518l-2.264,2.001H3.489v13.042h11.979V9.379L17.469,7.115z M15.532,2.451l-0.801,0.8l2.4,2.401  l0.801-0.8L15.532,2.451z M17.131,0.85l-0.799,0.801l2.4,2.4l0.801-0.801L17.131,0.85z M6.731,11.254l2.4,2.4l7.201-7.202  l-2.4-2.401L6.731,11.254z M5.952,14.431h2.264l-2.264-2.264V14.431z'></path></svg></a></div>";
                            }

                            if (current_user_can('arfeditforms')) {
                                $duplicate_link = "?page=ARForms&arfaction=duplicate&id={$val->id}";
                                echo "<div class='arfformicondiv arfhelptip' title='" . addslashes(esc_html__('Form Entry', 'ARForms')) . "'><a href='" . wp_nonce_url("?page=ARForms-entries&arfaction=list&form={$val->id}") . "' ><svg width='30px' height='30px' viewBox='-7 -4 30 30' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#ffffff' d='M1.489,19.829V0.85h14v18.979H1.489z M13.497,2.865H3.481v14.979  h10.016V2.865z M10.489,15.806H4.493v-2h5.996V15.806z M4.495,9.806h7.994v2H4.495V9.806z M4.495,5.806h7.994v2H4.495V5.806z'/></path></svg></a></div>";

                                echo "<div class='arfformicondiv arfhelptip' title='" . addslashes(esc_html__('Duplicate Form', 'ARForms')) . "'><a href='" . wp_nonce_url($duplicate_link) . "' ><svg width='30px' height='30px' viewBox='-5 -5 30 30' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#ffffff' d='M16.501,15.946V2.85H5.498v-2h11.991v0.025h1.012v15.07H16.501z   M15.489,19.81h-14V3.894h14V19.81z M13.497,5.909H3.481v11.979h10.016V5.909z'/></svg></a></div>";

                                echo "<div class='arfformicondiv arfhelptip' title='" . addslashes(esc_html__('Export Entries', 'ARForms')) . "'><a onclick='arfaction_func(\"export_csv\", \"{$val->id}\");'><svg width='30px' height='30px' viewBox='-3 -5 30 30' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill='#ffffff' d='M16.477,10.586V7.091c0-0.709-0.576-1.283-1.285-1.283H2.772c-0.709,0-1.283,0.574-1.283,1.283v3.495    c0,0.709,0.574,1.283,1.283,1.283h12.419C15.9,11.87,16.477,11.295,16.477,10.586z M5.131,9.887c0.277,0,0.492-0.047,0.67-0.116    l0.138,0.862c-0.208,0.092-0.6,0.17-1.047,0.17c-1.217,0-1.995-0.74-1.995-1.925c0-1.102,0.753-2.002,2.156-2.002    c0.308,0,0.646,0.054,0.893,0.146L5.762,7.892C5.623,7.83,5.415,7.776,5.107,7.776c-0.616,0-1.016,0.438-1.01,1.055    C4.098,9.524,4.561,9.887,5.131,9.887z M8.525,10.772c-0.492,0-1.369-0.107-1.654-0.262l0.646-0.839    C7.732,9.8,8.179,9.957,8.525,9.957c0.354,0,0.501-0.124,0.501-0.317c0-0.191-0.116-0.284-0.556-0.43    C7.695,8.948,7.395,8.524,7.402,8.077c0-0.701,0.6-1.231,1.531-1.231c0.44,0,0.832,0.101,1.063,0.216L9.789,7.87    c-0.17-0.094-0.494-0.216-0.816-0.216c-0.285,0-0.446,0.116-0.446,0.309c0,0.177,0.147,0.269,0.608,0.431    c0.717,0.246,1.016,0.608,1.023,1.162C10.158,10.255,9.604,10.772,8.525,10.772z M13.54,10.725h-1.171l-1.371-3.766h1.271    l0.509,1.748c0.092,0.315,0.162,0.617,0.216,0.916h0.023c0.062-0.308,0.124-0.593,0.208-0.916l0.486-1.748h1.23L13.54,10.725z     M19.961,0.85H6.02c-0.295,0-0.535,0.239-0.535,0.534v2.45h1.994V2.79h11.014v11.047l-2.447-0.002    c-0.158,0-0.309,0.064-0.421,0.177c-0.11,0.109-0.173,0.26-0.173,0.418l0.012,3.427H7.479V12.8H5.484v6.501    c0,0.294,0.239,0.533,0.535,0.533h10.389c0.153,0,0.297-0.065,0.398-0.179l3.553-4.048c0.088-0.098,0.135-0.224,0.135-0.355V1.384    C20.496,1.089,20.255,0.85,19.961,0.85z'/></svg></a></div>";
                            }

                            do_action('arf_additional_action_formlisting', $val->id);



                            global $style_settings, $arformhelper;



                            $target_url = $arformhelper->get_direct_link($val->id);

                            $target_url = $target_url . '&ptype=list';

                            $width = isset($_COOKIE['width']) ? $_COOKIE['width'] * 0.80 : 0;

                            if (isset($_COOKIE['width']) and $_COOKIE['width'] != '')
                                $tb_width = '&width=' . $width;
                            else
                                $tb_width = '';

                            if (isset($_COOKIE['height']) and $_COOKIE['height'] != '')
                                $tb_height = '&height=' . ($_COOKIE['height'] - 100);
                            else
                                $tb_height = '';

                            $target_url = $arformhelper->get_direct_link($val->form_key);
                            echo "<div class='arfformicondiv arfhelptip' title='" . addslashes(esc_html__('Preview', 'ARForms')) . "'><a class='openpreview' href='javascript:void(0)'  data-url='" . $target_url . $tb_width . $tb_height . "&whichframe=preview&TB_iframe=true&ptype=list'><svg width='30px' height='30px' viewBox='-3 -8 32 32' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#ffffff' d='M12.993,15.23c-7.191,0-11.504-7.234-11.504-7.234  S5.801,0.85,12.993,0.85c7.189,0,11.504,7.19,11.504,7.19S20.182,15.23,12.993,15.23z M12.993,2.827  c-5.703,0-8.799,5.214-8.799,5.214s3.096,5.213,8.799,5.213c5.701,0,8.797-5.213,8.797-5.213S18.694,2.827,12.993,2.827z   M12.993,11.572c-1.951,0-3.531-1.581-3.531-3.531s1.58-3.531,3.531-3.531c1.949,0,3.531,1.581,3.531,3.531  S14.942,11.572,12.993,11.572z'/></svg></a></div>";

                            if (current_user_can('arfdeleteforms')) {

                                $delete_link = "?page=ARForms&arfaction=destroy&id={$val->id}";



                                $id = $val->id;
                                echo "<div class='arfformicondiv arfhelptip arfdeleteform_div_" . $id . "' title='" . addslashes(esc_html__('Delete', 'ARForms')) . "'><a  id='delete_pop' data-toggle='arfmodal' data-id='" . $id . "' style='cursor:pointer'><svg width='30px' height='30px' viewBox='-5 -5 32 32' class='arfsvgposition'><path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#ffffff' d='M18.435,4.857L18.413,19.87L3.398,19.88L3.394,4.857H1.489V2.929  h1.601h3.394V0.85h8.921v2.079h3.336h1.601l0,0v1.928H18.435z M15.231,4.857H6.597H5.425l0.012,13.018h10.945l0.005-13.018H15.231z   M11.4,6.845h2.029v9.065H11.4V6.845z M8.399,6.845h2.03v9.065h-2.03V6.845z' /></svg></a></div>";
                            }
                            ?>

                        </div>
                    </td>
                </tr>
            <?php } ?>   
        </tbody>
        </table>
        <div class="clear"></div>
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
        <div class="alignleft actions2">
            <?php
            $two = '2';
            ?>
            <div class="arf_list_bulk_action_wrapper" style="width:130px;">
                <input id="arf_bulk_action_one" name="action<?php echo $two; ?>" value="" type="hidden">
                <dl class="arf_selectbox" data-name="action<?php echo $two; ?>" data-id="arf_bulk_action_one">
                    <dt style="width:130px;"><span><?php echo addslashes(esc_html__('Bulk Actions', 'ARForms')); ?></span>
                    <svg viewBox="0 0 2000 1000" width="15px" height="15px">
                    <g fill="#000">
                    <path d="M1024 320q0 -26 -19 -45t-45 -19h-896q-26 0 -45 19t-19 45t19 45l448 448q19 19 45 19t45 -19l448 -448q19 -19 19 -45z"/>
                    </g>
                    </svg>
                    <dd>
                        <ul style="display: none;width:140px;" data-id="arftitlefontsetting">
                            <li data-value='-1' data-label='<?php echo addslashes(esc_html__('Bulk Actions', 'ARForms')); ?>'><?php echo addslashes(esc_html__('Bulk Actions', 'ARForms')); ?></li>
                            <?php
                            foreach ($actions as $name => $title) {
                                $class = 'edit' == $name ? ' class="hide-if-no-js" ' : '';
                                ?>
                                <li <?php echo $class; ?> data-value='<?php echo $name; ?>' data-label='<?Php echo $title; ?>'><?php echo $title; ?></li>
                                <?php
                            }
                            ?>
                        </ul>
                    </dd>
                </dl>
            </div>
            <input type="submit" id="doaction<?php echo $two; ?>" class="rounded_button btn_green" value="<?php echo addslashes(esc_html__('Apply', 'ARForms')); ?>" />            
        </div>
        <div class="footer_grid"></div>
<?php
        die();
    }

    function arfupdateactionfunction() {

        global $wpdb, $arfform;

        $action = $_POST['act'];
        $id = $_POST['id'];

        if ($action == 'delete') {
            $del_res = $arfform->destroy($id);
            if ($del_res)
                $message = addslashes(esc_html__('Record is deleted successfully.', 'ARForms'));
        }

        if ($action == 'export_csv') {
        
            
        }

        $errors = array();
        return $this->change_form_listing(@$message, @$errors);

        die();
    }

    function arfformsavealloptions() {
        global $arfform, $wpdb, $MdlDb, $armainhelper, $arsettingcontroller, $arfsettings, $arffield, $arformhelper, $arfieldhelper;
        $str = json_decode(stripslashes_deep($_REQUEST['filtered_form']), true);  
        $temp_form_id = 0;      
        if ($str['arfaction'] == 'new' || $str['arfaction'] == 'duplicate') {
            $temp_form_id = $str['id'];
            $form_id = $id = $str['id'] = 0;
        }
        else {
            $form_id = $id = $str['id'];
        }

        $errors = apply_filters('arfvalidationofcurrentform', array(), $str);

        if (count($errors) > 0) {            
            echo 'false^|^' . json_encode($errors);
            die();
        }

        $_REQUEST = $values = $str;
        $values = apply_filters('arfchangevaluesbeforeupdateform', $values);

        do_action('arfbeforeupdateform', $id, $values, false);
        do_action('arfbeforeupdateform_' . $id, $id, $values, false);
        $db_data = array();
        if (isset($values['options']) || isset($values['item_meta']) || isset($values['field_options'])) {
            $values['status'] = 'published';
        }

        if (isset($values['form_key'])) {
            $values['form_key'] = $armainhelper->get_unique_key($values['form_key'], $MdlDb->forms, 'form_key', $id);
        }

        $form_fields = array('form_key', 'name', 'description', 'status');
        $new_values = array();
        $double_optin = 0;

        $options = array();
        if (isset($values['options'])) {

            $defaults = $arformhelper->get_default_opts();

            foreach ($defaults as $var => $default) {
                if ($var == 'notification') {
                    $options[$var] = isset($values[$var]) ? $values[$var] : $default;
                } else {
                    $options[$var] = isset($values['options'][$var]) ? $values['options'][$var] : $default;
                }
            }
            $options['admin_cc_email'] = isset($values['options']['admin_cc_email']) ? $values['options']['admin_cc_email'] : "";

            $options['admin_bcc_email'] = isset($values['options']['admin_bcc_email']) ? $values['options']['admin_bcc_email'] : "";
            
            $options['arf_data_with_url'] = isset($values['options']['arf_data_with_url']) ? $values['options']['arf_data_with_url'] : 0;

            if($options['arf_data_with_url'] != 0) {
                $options['arf_data_with_url_type'] = isset($values['options']['arf_data_with_url_type']) ? $values['options']['arf_data_with_url_type'] : 'post';
            }

            $options['arf_show_post_value'] = isset($values['options']['arf_show_post_value']) ? $values['options']['arf_show_post_value'] : 'no';

            $options['arf_post_value_url'] = isset($values['options']['arf_post_value_url']) ? $values['options']['arf_post_value_url'] : '';

            $options['arf_form_other_css'] = isset($values['options']['arf_form_other_css']) ? addslashes($values['options']['arf_form_other_css']) : '';

            $options['custom_style'] = isset($values['options']['custom_style']) ? $values['options']['custom_style'] : 0;

            $options['before_html'] = isset($values['options']['before_html']) ? $values['options']['before_html'] : $arformhelper->get_default_html('before');

            $options['after_html'] = isset($values['options']['after_html']) ? $values['options']['after_html'] : $arformhelper->get_default_html('after');

            $options = apply_filters('arfformoptionsbeforeupdateform', $options, $values);

            $options['display_title_form'] = isset($values['options']['display_title_form']) ? $values['options']['display_title_form'] : 0;

            $double_optin = $options['arf_enable_double_optin'] = isset($values['options']['arf_enable_double_optin']) ? $values['options']['arf_enable_double_optin'] : 0;

            $options['email_to'] = $options['reply_to'];

            $options['arf_restrict_entry'] = isset($values['options']['arf_restrict_entry']) ? $values['options']['arf_restrict_entry'] : 0;

            $options['arf_prevent_view_entry'] = isset($values['options']['arf_prevent_view_entry']) ? $values['options']['arf_prevent_view_entry'] : 0;


            $options['arf_restrict_form_entries'] = isset($values['options']['arf_restrict_form_entries']) ? $values['options']['arf_restrict_form_entries'] : 0;

            $options['restrict_action'] = isset($values['options']['restrict_action']) ? $values['options']['restrict_action'] : '';

            $options['arf_restrict_max_entries'] = isset($values['options']['arf_restrict_max_entries']) ? $values['options']['arf_restrict_max_entries'] : 50;


            $options['arf_res_msg_entry'] = isset($values['options']['arf_res_msg_entry']) ? $values['options']['arf_res_msg_entry'] : '';

            $options['arf_restrict_entries_before_specific_date'] = isset($values['options']['arf_restrict_entries_before_specific_date']) ? date('Y-m-d',strtotime($values['options']['arf_restrict_entries_before_specific_date'])) : '';

            $options['arf_restrict_entries_after_specific_date'] = isset($values['options']['arf_restrict_entries_after_specific_date']) ? date('Y-m-d',strtotime($values['options']['arf_restrict_entries_after_specific_date'])) : '';

            $options['arf_restrict_entries_start_date'] = isset($values['options']['arf_restrict_entries_start_date']) ? date('Y-m-d',strtotime($values['options']['arf_restrict_entries_start_date'])) : '';

            $options['arf_restrict_entries_end_date'] = isset($values['options']['arf_restrict_entries_end_date']) ? date('Y-m-d',strtotime($values['options']['arf_restrict_entries_end_date'])) : '';

            $options['arf_res_msg'] = isset($values['options']['arf_res_msg']) ? $values['options']['arf_res_msg'] : '';

            $options['arf_sub_track_code'] = isset($values['options']['arf_sub_track_code']) ? addslashes(rawurlencode($values['options']['arf_sub_track_code'])) : '';

            $options['arf_field_order'] = isset($values['arf_field_order']) ? $values['arf_field_order'] : json_encode(array());
            $options['arf_field_resize_width'] = isset($values['arf_field_resize_width']) ? $values['arf_field_resize_width'] : json_encode(array());
            $options['define_template'] = isset($values['define_template']) ? $values['define_template'] : 0;

            $options = apply_filters('arf_save_form_options_outside',$options,$values,$form_id);
            
            $submitbtnid = "arfsubmit";
            if (isset($_REQUEST['conditional_logic_' . $submitbtnid]) and stripslashes_deep($_REQUEST['conditional_logic_' . $submitbtnid]) == '1') {
                $arf_conditional_logic_display = isset($_REQUEST['conditional_logic_display_' . $submitbtnid]) ? $_REQUEST['conditional_logic_display_' . $submitbtnid] : '';
                $arf_conditional_logic_id_cond = isset($_REQUEST['conditional_logic_if_cond_' . $submitbtnid]) ? $_REQUEST['conditional_logic_if_cond_' . $submitbtnid] : '';
                $conditional_logic_display = stripslashes_deep($arf_conditional_logic_display);
                $conditional_logic_if_cond = stripslashes_deep($arf_conditional_logic_id_cond);
                $conditional_logic_rules = array();

                $rule_array = isset($_REQUEST['rule_array_' . $submitbtnid]) ? $_REQUEST['rule_array_' . $submitbtnid] : array();
                if (count($rule_array) > 0) {
                    $i = 1;
                    foreach ($rule_array as $v) {
                        $conditional_logic_field = isset($_REQUEST['arf_cl_field_' . $submitbtnid . '_' . $v]) ? stripslashes_deep($_REQUEST['arf_cl_field_' . $submitbtnid . '_' . $v]) : '';

                        $conditional_logic_field_type = ($conditional_logic_field!='') ? $arfieldhelper->get_field_type($conditional_logic_field) : '';
                        $conditional_logic_op = isset($_REQUEST['arf_cl_op_' . $submitbtnid . '_' . $v]) ? stripslashes_deep($_REQUEST['arf_cl_op_' . $submitbtnid . '_' . $v]) : '';
                        $conditional_logic_value = isset($_REQUEST['cl_rule_value_' . $submitbtnid . '_' . $v]) ? stripslashes_deep($_REQUEST['cl_rule_value_' . $submitbtnid . '_' . $v]) : '';
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
        }

        foreach ($values as $value_key => $value) {
            if (in_array($value_key, $form_fields))
                $db_data[$value_key] = $this->arfHtmlEntities($value,true);
        }

        $sel_fields = $wpdb->prepare("SELECT id FROM " . $MdlDb->fields . " where form_id = %d", $id);

        $sel_fields_arr = $wpdb->get_results($sel_fields, 'ARRAY_A');

        $old_field_array = array();
        $change_field_value = array();
        if (!empty($sel_fields_arr) && count($sel_fields_arr) > 0) {
            foreach ($sel_fields_arr as $id_temp => $temp_value) {
                array_push($old_field_array, $temp_value['id']);
            }
        }

        $db_data['autoresponder_fname'] = (isset($values['autoresponder_fname']) && $values['autoresponder_fname'] != '') ? $values['autoresponder_fname'] : '';

        $db_data['autoresponder_lname'] = (isset($values['autoresponder_lname']) && $values['autoresponder_lname'] != '') ? $values['autoresponder_lname'] : '';

        $db_data['autoresponder_email'] = (isset($values['autoresponder_email']) && $values['autoresponder_email']) ? $values['autoresponder_email'] : '';

        $form_css = array();
        
        $form_css['display_title_form'] = isset($options['display_title_form']) ? arf_sanitize_value($options['display_title_form']) : '';
        
        $form_css['arfmainformwidth'] = isset($_REQUEST['arffw']) ? arf_sanitize_value($_REQUEST['arffw']) : '';

        $form_css['form_width_unit'] = isset($_REQUEST['arffu']) ? arf_sanitize_value($_REQUEST['arffu']) : '';

        $form_css['text_direction'] = isset($_REQUEST['arftds']) ? arf_sanitize_value($_REQUEST['arftds']) : '';

        $form_css['form_align'] = isset($_REQUEST['arffa']) ? arf_sanitize_value($_REQUEST['arffa']) : '';

        $form_css['arfmainfieldsetpadding'] = isset($_REQUEST['arfmfsp']) ? arf_sanitize_value($_REQUEST['arfmfsp']) : '';

        $form_css['form_border_shadow'] = isset($_REQUEST['arffbs']) ? arf_sanitize_value($_REQUEST['arffbs']) : '';

        $form_css['fieldset'] = isset($_REQUEST['arfmfis']) ? arf_sanitize_value($_REQUEST['arfmfis']) : '';

        $form_css['arfmainfieldsetradius'] = isset($_REQUEST['arfmfsr']) ? arf_sanitize_value($_REQUEST['arfmfsr']) : '';

        $form_css['arfmainfieldsetcolor'] = isset($_REQUEST['arfmfsc']) ? arf_sanitize_value($_REQUEST['arfmfsc']) : '';

        $form_css['arfmainformbordershadowcolorsetting'] = isset($_REQUEST['arffboss']) ? arf_sanitize_value($_REQUEST['arffboss']) : '';

        $form_css['arfmainformtitlecolorsetting'] = isset($_REQUEST['arfftc']) ? arf_sanitize_value($_REQUEST['arfftc']) : '';

        $form_css['check_weight_form_title'] = isset($_REQUEST['arfftws']) ? arf_sanitize_value($_REQUEST['arfftws']) : '';

        $form_css['form_title_font_size'] = isset($_REQUEST['arfftfss']) ? arf_sanitize_value($_REQUEST['arfftfss']) : '';

        $form_css['arfmainformtitlepaddingsetting'] = isset($_REQUEST['arfftps']) ? arf_sanitize_value($_REQUEST['arfftps']) : '';

        $form_css['arfmainformbgcolorsetting'] = isset($_REQUEST['arffbcs']) ? arf_sanitize_value($_REQUEST['arffbcs']) : '';

        $form_css['font'] = isset($_REQUEST['arfmfs']) ? arf_sanitize_value($_REQUEST['arfmfs']) : '';

        $form_css['label_color'] = isset($_REQUEST['arflcs']) ? arf_sanitize_value($_REQUEST['arflcs']) : '';

        $form_css['weight'] = isset($_REQUEST['arfmfws']) ? arf_sanitize_value($_REQUEST['arfmfws']) : '';

        $form_css['font_size'] = isset($_REQUEST['arffss']) ? arf_sanitize_value($_REQUEST['arffss']) : '';

        $form_css['align'] = isset($_REQUEST['arffrma']) ? arf_sanitize_value($_REQUEST['arffrma']) : '';

        $form_css['position'] = isset($_REQUEST['arfmps']) ? arf_sanitize_value($_REQUEST['arfmps']) : '';

        $form_css['width'] = isset($_REQUEST['arfmws']) ? arf_sanitize_value($_REQUEST['arfmws']) : '';

        $form_css['width_unit'] = isset($_REQUEST['arfmwu']) ? arf_sanitize_value($_REQUEST['arfmwu']) : '';

        $form_css['arfdescfontsizesetting'] = isset($_REQUEST['arfdfss']) ? arf_sanitize_value($_REQUEST['arfdfss']) : '';

        $form_css['arfdescalighsetting'] = isset($_REQUEST['arfdas']) ? arf_sanitize_value($_REQUEST['arfdas']) : '';

        $form_css['hide_labels'] = isset($_REQUEST['arfhl']) ? arf_sanitize_value($_REQUEST['arfhl']) : '';

        $form_css['check_font'] = isset($_REQUEST['arfcbfs']) ? arf_sanitize_value($_REQUEST['arfcbfs']) : '';

        $form_css['check_weight'] = isset($_REQUEST['arfcbws']) ? arf_sanitize_value($_REQUEST['arfcbws']) : "";

        $form_css['field_font_size'] = isset($_REQUEST['arfffss']) ? arf_sanitize_value($_REQUEST['arfffss']) : "";

        $form_css['text_color'] = isset($_REQUEST['arftcs']) ? arf_sanitize_value($_REQUEST['arftcs']) : "";

        $form_css['border_radius'] = isset($_REQUEST['arfmbs']) ? arf_sanitize_value($_REQUEST['arfmbs']) : '';

        $form_css['border_color'] = isset($_REQUEST['arffmboc']) ? arf_sanitize_value($_REQUEST['arffmboc']) : '';

        $form_css['arffieldborderwidthsetting'] = isset($_REQUEST['arffbws']) ? arf_sanitize_value($_REQUEST['arffbws']) : '';

        $form_css['arffieldborderstylesetting'] = isset($_REQUEST['arffbss']) ? arf_sanitize_value($_REQUEST['arffbss']) : '';

        $form_css['arfsubmitbuttonstyle'] = isset($_REQUEST['arfsubmitbuttonstyle']) ? arf_sanitize_value($_REQUEST['arfsubmitbuttonstyle']) : 'border';

        if (isset($_REQUEST['arffiu']) and $_REQUEST['arffiu'] == '%' and isset($_REQUEST['arfmfiws']) and $_REQUEST['arfmfiws'] > '100') {
            $form_css['field_width'] = arf_sanitize_value('100');
        } else {
            $form_css['field_width'] = isset($_REQUEST['arfmfiws']) ? arf_sanitize_value($_REQUEST['arfmfiws']) : '';
        }

        $form_css['field_width_unit'] = isset($_REQUEST['arffiu']) ? arf_sanitize_value($_REQUEST['arffiu']) : "";

        $form_css['arffieldmarginssetting'] = isset($_REQUEST['arffms']) ? arf_sanitize_value($_REQUEST['arffms']) : '';

        $form_css['arffieldinnermarginssetting'] = isset($_REQUEST['arffims']) ? arf_sanitize_value($_REQUEST['arffims']) : "";

        $form_css['bg_color'] = isset($_REQUEST['arffmbc']) ? arf_sanitize_value($_REQUEST['arffmbc']) : '';

        $form_css['arfbgactivecolorsetting'] = isset($_REQUEST['arfbcas']) ? arf_sanitize_value($_REQUEST['arfbcas']) : "";

        $form_css['arfborderactivecolorsetting'] = isset($_REQUEST['arfbacs']) ? arf_sanitize_value($_REQUEST['arfbacs']) : "";

        $form_css['arferrorbgcolorsetting'] = isset($_REQUEST['arfbecs']) ? arf_sanitize_value($_REQUEST['arfbecs']) : "";

        $form_css['arferrorbordercolorsetting'] = isset($_REQUEST['arfboecs']) ? arf_sanitize_value($_REQUEST['arfboecs']) : '';

        $form_css['arfradioalignsetting'] = isset($_REQUEST['arfras']) ? arf_sanitize_value($_REQUEST['arfras']) : "";

        $form_css['arfcheckboxalignsetting'] = isset($_REQUEST['arfcbas']) ? arf_sanitize_value($_REQUEST['arfcbas']) : '';


        $form_css['auto_width'] = isset($_REQUEST['arfautowidthsetting']) ? arf_sanitize_value($_REQUEST['arfautowidthsetting']) : '';

        $form_css['arfcalthemename'] = isset($_REQUEST['arffths']) ? arf_sanitize_value($_REQUEST['arffths']) : '';

        $form_css['arfcalthemecss'] = isset($_REQUEST['arffthc']) ? arf_sanitize_value($_REQUEST['arffthc']) : "";

        $form_css['date_format'] = isset($_REQUEST['arffdaf']) ? arf_sanitize_value($_REQUEST['arffdaf']) : '';

        $form_css['arfsubmitbuttontext'] = isset($_REQUEST['arfsubmitbuttontext']) ? arf_sanitize_value($_REQUEST['arfsubmitbuttontext']) : '';

        $form_css['arfsubmitweightsetting'] = isset($_REQUEST['arfsbwes']) ? arf_sanitize_value($_REQUEST['arfsbwes']) : '';

        $form_css['arfsubmitbuttonfontsizesetting'] = isset($_REQUEST['arfsbfss']) ? arf_sanitize_value($_REQUEST['arfsbfss']) : '';

        $form_css['arfsubmitbuttonwidthsetting'] = isset($_REQUEST['arfsbws']) ? arf_sanitize_value($_REQUEST['arfsbws']) : '';

        $form_css['arfsubmitbuttonheightsetting'] = isset($_REQUEST['arfsbhs']) ? arf_sanitize_value($_REQUEST['arfsbhs']) : '';
        $form_css['submit_bg_color'] = isset($_REQUEST['arfsbbcs']) ? arf_sanitize_value($_REQUEST['arfsbbcs']) : "";

        $form_css['arfsubmitbuttonbgcolorhoversetting'] = isset($_REQUEST['arfsbchs']) ? arf_sanitize_value($_REQUEST['arfsbchs']) : '';

        $form_css['arfsubmitbgcolor2setting'] = isset($_REQUEST['arfsbcs']) ? arf_sanitize_value($_REQUEST['arfsbcs']) : '';

        $form_css['arfsubmittextcolorsetting'] = isset($_REQUEST['arfsbtcs']) ? arf_sanitize_value($_REQUEST['arfsbtcs']) : '';

        $form_css['arfsubmitbordercolorsetting'] = isset($_REQUEST['arfsbobcs']) ? arf_sanitize_value($_REQUEST['arfsbobcs']) : '';

        $form_css['arfsubmitborderwidthsetting'] = isset($_REQUEST['arfsbbws']) ? arf_sanitize_value($_REQUEST['arfsbbws']) : '';

        $form_css['arfsubmitborderradiussetting'] = isset($_REQUEST['arfsbbrs']) ? arf_sanitize_value($_REQUEST['arfsbbrs']) : '';

        $form_css['arfsubmitshadowcolorsetting'] = isset($_REQUEST['arfsbscs']) ? arf_sanitize_value($_REQUEST['arfsbscs']) : '';

        $form_css['arfsubmitbuttonmarginsetting'] = isset($_REQUEST['arfsbms']) ? arf_sanitize_value($_REQUEST['arfsbms']) : '';
        $form_css['submit_bg_img'] = isset($_REQUEST['arfsbis']) ? arf_sanitize_value($_REQUEST['arfsbis']) : '';

        $form_css['submit_hover_bg_img'] = isset($_REQUEST['arfsbhis']) ? arf_sanitize_value($_REQUEST['arfsbhis']) : '';

        $form_css['error_font'] = isset($_REQUEST['arfmefs']) ? arf_sanitize_value($_REQUEST['arfmefs']) : '';

        $form_css['error_font_other'] = isset($_REQUEST['arfmofs']) ? arf_sanitize_value($_REQUEST['arfmofs']) : '';

        $form_css['arffontsizesetting'] = isset($_REQUEST['arfmefss']) ? arf_sanitize_value($_REQUEST['arfmefss']) : '';

        $form_css['arferrorbgsetting'] = isset($_REQUEST['arfmebs']) ? arf_sanitize_value($_REQUEST['arfmebs']) : '';

        $form_css['arferrortextsetting'] = isset($_REQUEST['arfmets']) ? arf_sanitize_value($_REQUEST['arfmets']) : '';

        $form_css['arferrorbordersetting'] = isset($_REQUEST['arfmebos']) ? arf_sanitize_value($_REQUEST['arfmebos']) : '';

        $form_css['arfsucessbgcolorsetting'] = isset($_REQUEST['arfmsbcs']) ? arf_sanitize_value($_REQUEST['arfmsbcs']) : '';

        $form_css['arfsucessbordercolorsetting'] = isset($_REQUEST['arfmsbocs']) ? arf_sanitize_value($_REQUEST['arfmsbocs']) : "";

        $form_css['arfsucesstextcolorsetting'] = isset($_REQUEST['arfmstcs']) ? arf_sanitize_value($_REQUEST['arfmstcs']) : '';

        $form_css['arfformerrorbgcolorsettings'] = isset($_REQUEST['arffebgc']) ? arf_sanitize_value($_REQUEST['arffebgc']) : '';

        $form_css['arfformerrorbordercolorsettings'] = isset($_REQUEST['arffebrdc']) ? arf_sanitize_value($_REQUEST['arffebrdc']) : '';

        $form_css['arfformerrortextcolorsettings'] = isset($_REQUEST['arffetxtc']) ? arf_sanitize_value($_REQUEST['arffetxtc']) : '';

        $form_css['arfsubmitalignsetting'] = isset($_REQUEST['arfmsas']) ? arf_sanitize_value($_REQUEST['arfmsas']) : '';

        $form_css['checkbox_radio_style'] = isset($_REQUEST['arfcrs']) ? arf_sanitize_value($_REQUEST['arfcrs']) : '';

        $form_css['bg_color_pg_break'] = isset($_REQUEST['arffbcpb']) ? arf_sanitize_value($_REQUEST['arffbcpb']) : '';

        $form_css['bg_inavtive_color_pg_break'] = isset($_REQUEST['arfbicpb']) ? arf_sanitize_value($_REQUEST['arfbicpb']) : "";

        $form_css['text_color_pg_break'] = isset($_REQUEST['arfftcpb']) ? arf_sanitize_value($_REQUEST['arfftcpb']) : "";

        $form_css['arfmainform_bg_img'] = isset($_REQUEST['arfmfbi']) ? arf_sanitize_value($_REQUEST['arfmfbi']) : '';

        $form_css['arfmainform_color_skin'] = isset($_REQUEST['arfmcs']) ? arf_sanitize_value($_REQUEST['arfmcs']) : '';

        $form_css['arfinputstyle'] = isset($_REQUEST['arfinpst']) ? arf_sanitize_value($_REQUEST['arfinpst']) : arf_sanitize_value('standard');

        $form_css['arfsubmitfontfamily'] = isset($_REQUEST['arfsff']) ? arf_sanitize_value($_REQUEST['arfsff']) : '';

        $form_css['arfmainfieldcommonsize'] = isset($_REQUEST['arfmainfieldcommonsize']) ? arf_sanitize_value($_REQUEST['arfmainfieldcommonsize']) : arf_sanitize_value('3');

        $form_css['arfdatepickerbgcolorsetting'] = isset($_REQUEST['arfdbcs']) ? arf_sanitize_value($_REQUEST['arfdbcs']) : arf_sanitize_value('#23b7e5');
        $form_css['arfdatepickertextcolorsetting'] = isset($_REQUEST['arfdtcs']) ? arf_sanitize_value($_REQUEST['arfdtcs']) : arf_sanitize_value('#ffffff');

        $form_css['arfuploadbtntxtcolorsetting'] = isset($_REQUEST['arfuptxt']) ? arf_sanitize_value($_REQUEST['arfuptxt']) : arf_sanitize_value('#ffffff');
        $form_css['arfuploadbtnbgcolorsetting'] = isset($_REQUEST['arfupbg']) ? arf_sanitize_value($_REQUEST['arfupbg']) : arf_sanitize_value('#077BDD');
    
        $form_css['arf_bg_position_x'] = (isset($_REQUEST['arf_bg_position_x']) && $_REQUEST['arf_bg_position_x'] != '') ? arf_sanitize_value($_REQUEST['arf_bg_position_x']) : arf_sanitize_value("left");
        $form_css['arf_bg_position_y'] = (isset($_REQUEST['arf_bg_position_y']) && $_REQUEST['arf_bg_position_y'] != '') ? arf_sanitize_value($_REQUEST['arf_bg_position_y']) : arf_sanitize_value("top");
	
        $form_css['arf_bg_position_input_x'] = (isset($_REQUEST['arf_bg_position_input_x']) && $_REQUEST['arf_bg_position_input_x'] != '') ? arf_sanitize_value($_REQUEST['arf_bg_position_input_x']) : "";
        $form_css['arf_bg_position_input_y'] = (isset($_REQUEST['arf_bg_position_input_y']) && $_REQUEST['arf_bg_position_input_y'] != '') ? arf_sanitize_value($_REQUEST['arf_bg_position_input_y']) : "";

        $form_css['arfmainfieldsetpadding_1'] = (isset($_REQUEST['arfmainfieldsetpadding_1']) && $_REQUEST['arfmainfieldsetpadding_1'] != '') ? arf_sanitize_value($_REQUEST['arfmainfieldsetpadding_1']) : 0;
        $form_css['arfmainfieldsetpadding_2'] = (isset($_REQUEST['arfmainfieldsetpadding_2']) && $_REQUEST['arfmainfieldsetpadding_2'] != '') ? arf_sanitize_value($_REQUEST['arfmainfieldsetpadding_2']) : 0;
        $form_css['arfmainfieldsetpadding_3'] = (isset($_REQUEST['arfmainfieldsetpadding_3']) && $_REQUEST['arfmainfieldsetpadding_3'] != '') ? arf_sanitize_value($_REQUEST['arfmainfieldsetpadding_3']) : 0;
        $form_css['arfmainfieldsetpadding_4'] = (isset($_REQUEST['arfmainfieldsetpadding_4']) && $_REQUEST['arfmainfieldsetpadding_4'] != '') ? arf_sanitize_value($_REQUEST['arfmainfieldsetpadding_4']) : 0;
        $form_css['arfmainformtitlepaddingsetting_1'] = (isset($_REQUEST['arfformtitlepaddingsetting_1']) && $_REQUEST['arfformtitlepaddingsetting_1'] != '') ? arf_sanitize_value($_REQUEST['arfformtitlepaddingsetting_1']) : 0;
        $form_css['arfmainformtitlepaddingsetting_2'] = (isset($_REQUEST['arfformtitlepaddingsetting_2']) && $_REQUEST['arfformtitlepaddingsetting_2'] != '') ? arf_sanitize_value($_REQUEST['arfformtitlepaddingsetting_2']) : 0;
        $form_css['arfmainformtitlepaddingsetting_3'] = (isset($_REQUEST['arfformtitlepaddingsetting_3']) && $_REQUEST['arfformtitlepaddingsetting_3'] != '') ? arf_sanitize_value($_REQUEST['arfformtitlepaddingsetting_3']) : 0;
        $form_css['arfmainformtitlepaddingsetting_4'] = (isset($_REQUEST['arfformtitlepaddingsetting_4']) && $_REQUEST['arfformtitlepaddingsetting_4'] != '') ? arf_sanitize_value($_REQUEST['arfformtitlepaddingsetting_4']) : 0;
        $form_css['arffieldinnermarginssetting_1'] = (isset($_REQUEST['arffieldinnermarginsetting_1']) && $_REQUEST['arffieldinnermarginsetting_1'] != '') ? arf_sanitize_value($_REQUEST['arffieldinnermarginsetting_1']) : 0;
        $form_css['arffieldinnermarginssetting_2'] = (isset($_REQUEST['arffieldinnermarginsetting_2']) && $_REQUEST['arffieldinnermarginsetting_2'] != '') ? arf_sanitize_value($_REQUEST['arffieldinnermarginsetting_2']) : 0;
        $form_css['arffieldinnermarginssetting_3'] = (isset($_REQUEST['arffieldinnermarginsetting_3']) && $_REQUEST['arffieldinnermarginsetting_3'] != '') ? arf_sanitize_value($_REQUEST['arffieldinnermarginsetting_3']) : 0;
        $form_css['arffieldinnermarginssetting_4'] = (isset($_REQUEST['arffieldinnermarginsetting_4']) && $_REQUEST['arffieldinnermarginsetting_4'] != '') ? arf_sanitize_value($_REQUEST['arffieldinnermarginsetting_4']) : 0;
        $form_css['arfsubmitbuttonmarginsetting_1'] = (isset($_REQUEST['arfsubmitbuttonmarginsetting_1']) && $_REQUEST['arfsubmitbuttonmarginsetting_1'] != '') ? arf_sanitize_value($_REQUEST['arfsubmitbuttonmarginsetting_1']) : 0;
        $form_css['arfsubmitbuttonmarginsetting_2'] = (isset($_REQUEST['arfsubmitbuttonmarginsetting_2']) && $_REQUEST['arfsubmitbuttonmarginsetting_2'] != '') ? arf_sanitize_value($_REQUEST['arfsubmitbuttonmarginsetting_2']) : 0;
        $form_css['arfsubmitbuttonmarginsetting_3'] = (isset($_REQUEST['arfsubmitbuttonmarginsetting_3']) && $_REQUEST['arfsubmitbuttonmarginsetting_3'] != '') ? arf_sanitize_value($_REQUEST['arfsubmitbuttonmarginsetting_3']) : 0;
        $form_css['arfsubmitbuttonmarginsetting_4'] = (isset($_REQUEST['arfsubmitbuttonmarginsetting_4']) && $_REQUEST['arfsubmitbuttonmarginsetting_4'] != '') ? arf_sanitize_value($_REQUEST['arfsubmitbuttonmarginsetting_4']) : 0;
        $form_css['arfsectionpaddingsetting_1'] = (isset($_REQUEST['arfsectionpaddingsetting_1']) && $_REQUEST['arfsectionpaddingsetting_1'] != '') ? arf_sanitize_value($_REQUEST['arfsectionpaddingsetting_1']) : 0;
        $form_css['arfsectionpaddingsetting_2'] = (isset($_REQUEST['arfsectionpaddingsetting_2']) && $_REQUEST['arfsectionpaddingsetting_2'] != '') ? arf_sanitize_value($_REQUEST['arfsectionpaddingsetting_2']) : 0;
        $form_css['arfsectionpaddingsetting_3'] = (isset($_REQUEST['arfsectionpaddingsetting_3']) && $_REQUEST['arfsectionpaddingsetting_3'] != '') ? arf_sanitize_value($_REQUEST['arfsectionpaddingsetting_3']) : 0;
        $form_css['arfsectionpaddingsetting_4'] = (isset($_REQUEST['arfsectionpaddingsetting_4']) && $_REQUEST['arfsectionpaddingsetting_4'] != '') ? arf_sanitize_value($_REQUEST['arfsectionpaddingsetting_4']) : 0;
        $form_css['arfcheckradiostyle'] = isset($_REQUEST['arfcksn']) ? arf_sanitize_value($_REQUEST['arfcksn']) : '';
        $form_css['arfcheckradiocolor'] = isset($_REQUEST['arfcksc']) ? arf_sanitize_value($_REQUEST['arfcksc']) : '';
        $form_css['arf_checked_checkbox_icon'] = isset($_REQUEST['arf_checkbox_icon']) ? arf_sanitize_value($_REQUEST['arf_checkbox_icon']) : '';
        $form_css['enable_arf_checkbox'] = isset($_REQUEST['enable_arf_checkbox']) ? arf_sanitize_value($_REQUEST['enable_arf_checkbox']) : "";
        $form_css['arf_checked_radio_icon'] = isset($_REQUEST['arf_radio_icon']) ? arf_sanitize_value($_REQUEST['arf_radio_icon']) : '';
        $form_css['enable_arf_radio'] = isset($_REQUEST['enable_arf_radio']) ? arf_sanitize_value($_REQUEST['enable_arf_radio']) : '';
        $form_css['checked_checkbox_icon_color'] = isset($_REQUEST['cbscol']) ? arf_sanitize_value($_REQUEST['cbscol']) : "";
        $form_css['checked_radio_icon_color'] = isset($_REQUEST['rbscol']) ? arf_sanitize_value($_REQUEST['rbscol']) : '';

        $form_css['arferrorstyle'] = isset($_REQUEST['arfest']) ? arf_sanitize_value($_REQUEST['arfest']) : '';
        $form_css['arferrorstylecolor'] = isset($_REQUEST['arfestc']) ? arf_sanitize_value($_REQUEST['arfestc']) : '';
        $form_css['arferrorstylecolor2'] = isset($_REQUEST['arfestc2']) ? arf_sanitize_value($_REQUEST['arfestc2']) : '';
        $form_css['arferrorstyleposition'] = isset($_REQUEST['arfestbc']) ? arf_sanitize_value($_REQUEST['arfestbc']) : '';
        $form_css['arfstandarderrposition'] = isset($_REQUEST['arfstndrerr']) ? arf_sanitize_value($_REQUEST['arfstndrerr']) : 'relative';
        
        $form_css['arfvalidationbgcolorsetting'] = isset($_REQUEST['arfmvbcs']) ? arf_sanitize_value($_REQUEST['arfmvbcs']) :arf_sanitize_value('#ed4040');
        $form_css['arfvalidationtextcolorsetting'] = isset($_REQUEST['arfmvtcs']) ? arf_sanitize_value($_REQUEST['arfmvtcs']) : arf_sanitize_value('#ffffff');

        $form_css['arfformtitlealign'] = isset($_REQUEST['arffta']) ? arf_sanitize_value($_REQUEST['arffta']) : '';
        $form_css['arfsubmitautowidth'] = isset($_REQUEST['arfsbaw']) ? arf_sanitize_value($_REQUEST['arfsbaw']) : '';

        $form_css['arftitlefontfamily'] = isset($_REQUEST['arftff']) ? arf_sanitize_value($_REQUEST['arftff']) : '';

        $form_css['bar_color_survey'] = isset($_REQUEST['arfbcs']) ? arf_sanitize_value($_REQUEST['arfbcs']) : '';
        $form_css['bg_color_survey'] = isset($_REQUEST['arfbgcs']) ? arf_sanitize_value($_REQUEST['arfbgcs']) : "";
        $form_css['text_color_survey'] = isset($_REQUEST['arfftcs']) ? arf_sanitize_value($_REQUEST['arfftcs']) : '';

        $form_css['arfsectionpaddingsetting'] = isset($_REQUEST['arfscps']) ? arf_sanitize_value($_REQUEST['arfscps']) : '';

        if (isset($_REQUEST['arfmainform_opacity']) and $_REQUEST['arfmainform_opacity'] > 1) {
            $form_css['arfmainform_opacity'] = arf_sanitize_value('1');
        } else {
            $form_css['arfmainform_opacity'] = isset($_REQUEST['arfmainform_opacity']) ? arf_sanitize_value($_REQUEST['arfmainform_opacity']) : '';
        }

        if (isset($_REQUEST['arfplaceholder_opacity']) and $_REQUEST['arfplaceholder_opacity'] > 1) {
            $form_css['arfplaceholder_opacity'] = arf_sanitize_value('1');
        } else {
            $form_css['arfplaceholder_opacity'] = isset($_REQUEST['arfplaceholder_opacity']) ? $_REQUEST['arfplaceholder_opacity'] : arf_sanitize_value('0.50');
        }

        $form_css['arfmainfield_opacity'] = isset($_REQUEST['arfmfo']) ? arf_sanitize_value($_REQUEST['arfmfo']) : "";
        if($_REQUEST['arfinpst'] == 'material')
        {
            $form_css['arfmainfield_opacity'] = arf_sanitize_value(1, 'integer');
        }
        $form_css['arf_req_indicator'] = isset($_REQUEST['arfrinc']) ? arf_sanitize_value($_REQUEST['arfrinc']) : arf_sanitize_value("0");

        $form_css['prefix_suffix_bg_color'] = isset($_REQUEST['pfsfsbg']) ? arf_sanitize_value($_REQUEST['pfsfsbg']) : '';
        $form_css['prefix_suffix_icon_color'] = isset($_REQUEST['pfsfscol']) ? arf_sanitize_value($_REQUEST['pfsfscol']) : "";

        $form_css['arf_tooltip_bg_color'] = isset($_REQUEST['arf_tooltip_bg_color']) ? arf_sanitize_value($_REQUEST['arf_tooltip_bg_color']) : "";
        $form_css['arf_tooltip_font_color'] = isset($_REQUEST['arf_tooltip_font_color']) ? arf_sanitize_value($_REQUEST['arf_tooltip_font_color']) : "";
        $form_css['arf_tooltip_width'] = isset($_REQUEST['arf_tooltip_width']) ? arf_sanitize_value($_REQUEST['arf_tooltip_width']) : "";
        $form_css['arf_tooltip_position'] = isset($_REQUEST['arf_tooltip_position']) ? arf_sanitize_value($_REQUEST['arf_tooltip_position']) : "";
        $form_css['arfcommonfont'] = isset($_REQUEST['arfcommonfont']) ? arf_sanitize_value($_REQUEST['arfcommonfont']) : arf_sanitize_value("Helvetica");
        $form_css['arfsectiontitlefamily'] = isset($_REQUEST['arfsectiontitlefamily']) ? arf_sanitize_value($_REQUEST['arfsectiontitlefamily']) : arf_sanitize_value("Helvetica");
        $form_css['arfsectiontitlefontsizesetting'] = isset($_REQUEST['arfsectiontitlefontsizesetting']) ? arf_sanitize_value($_REQUEST['arfsectiontitlefontsizesetting']) : arf_sanitize_value("16");
        $form_css['arfsectiontitleweightsetting'] = isset($_REQUEST['arfsectiontitleweightsetting']) ? arf_sanitize_value($_REQUEST['arfsectiontitleweightsetting']) : "";

        $form_css['arf_divider_inherit_bg'] = isset($_REQUEST['arf_divider_inherit_bg']) ? arf_sanitize_value($_REQUEST['arf_divider_inherit_bg']) : arf_sanitize_value(0, 'integer');
        $form_css['arfformsectionbackgroundcolor'] = isset($_REQUEST['arfsecbg']) ? arf_sanitize_value($_REQUEST['arfsecbg']) : '';
        $form_css['arfmainbasecolor'] = isset($_REQUEST['arfmbsc']) ? arf_sanitize_value($_REQUEST['arfmbsc']) : '';

        $form_css['arflikebtncolor'] = isset($_REQUEST['albclr']) ? arf_sanitize_value($_REQUEST['albclr']) : '';
        $form_css['arfdislikebtncolor'] = isset($_REQUEST['adlbclr']) ? arf_sanitize_value($_REQUEST['adlbclr']) : '';

        $form_css['arfstarratingcolor'] = isset($_REQUEST['asclcl']) ? arf_sanitize_value($_REQUEST['asclcl']) : '';

        $form_css['arfsliderselectioncolor'] = isset($_REQUEST['asldrsl']) ? arf_sanitize_value($_REQUEST['asldrsl']) : '';
        $form_css['arfslidertrackcolor'] = isset($_REQUEST['asltrcl']) ? arf_sanitize_value($_REQUEST['asltrcl']) : '';
        

        if ($form_css['arfcheckradiostyle'] == 'custom') {
            $is_font_awesome = true;
            $options['font_awesome_loaded'] = $is_font_awesome;
        }

        $options = apply_filters('arf_trim_values',$options);
    
        if (!empty($form_css)) {
            $db_data['options'] = maybe_serialize($options);
            $db_data['form_css'] = maybe_serialize($form_css);
            $db_data['status'] = 'published';
            if ($str['arfaction'] == 'new' || $str['arfaction'] == 'duplicate') {
                $db_data['form_key'] = $armainhelper->get_unique_key('', $MdlDb->forms, 'form_key');
                $db_data['created_date'] = date('Y-m-d H:i:s');
                $query_results = $wpdb->insert($MdlDb->forms, $db_data);
                $form_id = $str['id'] = $values['id'] = $_REQUEST['id'] = $id = $wpdb->insert_id;
                $query_results = true;
            } else {
                if (!empty($db_data)) {
                    $query_results = $wpdb->update($MdlDb->forms, $db_data, array('id' => $id));
                    if ($query_results) {
                        wp_cache_delete($id, 'arfform');
                    }
                } else {
                    $query_results = true;
                }
            }
        } else {
            $query_results = true;
        }

        $scale_field_available = "";
        $selectbox_field_available = "";
        $radio_field_available = "";
        $checkbox_field_available = "";
        $new_field_order = array();
        $temp_order = json_decode($values['arf_field_order'], true);
        $type_array = array();
        $content_array = array();
        $new_id_array = array();
        $total_page_break = 0;
        $page_break = array();
        $is_font_awesome = 0;
        $is_tooltip = 0;
        $is_input_mask = 0;
        $normal_color_picker = 0;
        $advance_color_pikcker = 0;
        $animate_number = 0;
        $round_total_number=0;
        $arf_page_break_survey = 0;
        $arf_page_break_wizard = 0;
        $arf_page_break_possition_top = 0;
        $arf_page_break_possition_bottom = 0;
        $arf_hide_bar_belt = 0;
        $arf_autocomplete_loaded = 0;
        $html_running_total_field_array = array();
        $google_captcha_loaded = 0;
        $is_imagecontrol_field = 0;
        $loaded_field = array();
        $i = 0;
        $return_json_data = array();
        $changed_field_value = array();
        $arf_temp_fields = array();
        $hidden_field_ids = array();
        $default_value_field_array = apply_filters('arf_default_value_array_field_type',array('scale','checkbox','radio','like'));
        $default_value_from_itemmeta = apply_filters('arf_default_value_array_field_type_from_itemmeta',array('select','colorpicker', 'hidden'));
        foreach ($values as $key => $value) {
            if (preg_match('/(arf_field_data_)/', $key)) {
                $name_array = explode('arf_field_data_', $key);
                $field_id_new = $name_array[1];
                $field_otions_new = array();
                $field_otions_new = json_decode($value, true);
                //$field_otions_new = $this->arfHtmlEntities($field_otions_new,true);
                $type_array[$key] = $field_otions_new["type"];
                $default_value = '';
                $field_options = '';
                if(in_array($field_otions_new["type"],$default_value_field_array)){
                    $default_value = isset($field_otions_new['default_value']) ? $field_otions_new['default_value'] : ''; 
                } else if(in_array($field_otions_new['type'],$default_value_from_itemmeta)){
                    $default_value = isset($values['item_meta'][$field_id_new]) ? $values['item_meta'][$field_id_new] : '';
                } else if($field_otions_new['default_value'] != '' ){
                    $default_value = $field_otions_new['default_value'];
                }

                $clear_on_focus = isset($field_otions_new['frm_clear_field']) ? $field_otions_new['frm_clear_field'] : 0;
                $default_blank = isset($field_otions_new['frm_default_blank']) ? $field_otions_new['frm_default_blank'] : 0;
                $value = json_decode($value, true);
                $value['default_value'] = $default_value;
                $value['clear_on_focus'] = $clear_on_focus;
                $value['default_blank'] = $default_blank;
                if ($default_blank == 1 || $clear_on_focus == 1) {
                    $value['value'] = ($default_value == '' ) ? $value['placeholdertext'] : $default_value;
                }
        
                $value = apply_filters('arf_trim_values',$value);
         
                $new_temp_value = json_encode($value);

                $value = $new_temp_value;

                if (isset($field_otions_new['options']) && !empty($field_otions_new['options'])) {
                    if (is_array($field_otions_new['options'])) {
                        $field_options = json_encode($field_otions_new['options']);
                    } else if (is_object($field_otions_new['options'])) {
                        $field_otions_new['options'] = $this->arfObjtoArray($field_otions_new['options']);
                        $field_options = json_encode($field_otions_new['options']);
                    }
                }

                if (!isset($values['item_meta'])) {
                    $values['item_meta'] = array();
                }

                $existing_keys = array_keys($values['item_meta']);

                if (in_array($field_id_new, $old_field_array)) {
            
            
             
                    $field_data_to_save = array(
                        'name' => isset($field_otions_new['name']) ? arf_sanitize_value($field_otions_new['name']) : '',
                        'type' => arf_sanitize_value($field_otions_new['type']),
                        'options' => $field_options,
                        'required' => isset($field_otions_new['required']) ? arf_sanitize_value($field_otions_new['required']) : arf_sanitize_value('0'),
                        'field_options' => $value,
                        'form_id' => arf_sanitize_value($id, 'integer'),
                        'enable_running_total' => '',
                        'option_order' => isset($field_otions_new['option_order']) ? $field_otions_new['option_order'] : '',
                    );

                    if($field_otions_new["type"]=='email')
                    {
                        if($field_otions_new['confirm_email']=='1')
                        {
                            $email_field_key = $armainhelper->get_unique_key('', $MdlDb->fields, 'field_key');
                            $confirm_field_order_arr = json_decode($options['arf_field_order'],true);
                            $confirm_field_order = $confirm_field_order_arr[$field_id_new.'_confirm'];
                            $arf_temp_fields['confirm_email_'.$field_id_new] = array( 'key' => $email_field_key, 'order' => $confirm_field_order, 'parent_field_id' => $field_id_new, 'confirm_inner_class' => $field_otions_new['confirm_email_inner_classes']);
                        }
                    }
                    if($field_otions_new["type"]=='password')
                    {
                        if($field_otions_new['confirm_password']=='1')
                        {
                            $email_field_key = $armainhelper->get_unique_key('', $MdlDb->fields, 'field_key');
                            $confirm_field_order_arr = json_decode($options['arf_field_order'],true);
                            $confirm_field_order = $confirm_field_order_arr[$field_id_new.'_confirm'];
                            $arf_temp_fields['confirm_password_'.$field_id_new] = array( 'key' => $email_field_key, 'order' => $confirm_field_order, 'parent_field_id' => $field_id_new, 'confirm_inner_class' => $field_otions_new['confirm_password_inner_classes']);
                        }
                    }

                      $update = $wpdb->update($MdlDb->fields, $field_data_to_save, array('id' => $field_id_new));

                        $new_id_array[$i]['old_id'] = $field_id_new;
                        $new_id_array[$i]['new_id'] = $field_id_new;
                        $new_id_array[$i]['name'] = isset($field_otions_new["name"]) ? $field_otions_new["name"] : ''; 
                        $new_id_array[$i]['type'] = $field_otions_new["type"];

                    $loaded_field[$i] = $field_otions_new['type'];
                    if ($field_otions_new['type'] == 'break') {
                        $total_page_break++;
                        $page_break[] = $field_id_new;
                        if (isset($field_otions_new['page_break_type']) && $field_otions_new['page_break_type'] == 'survey') {
                            $arf_page_break_survey = 1;
                        
                        } else if (isset($field_otions_new['page_break_type']) && $field_otions_new['page_break_type'] == 'wizard') {
                            
                            $arf_page_break_wizard = 1;

                        }

                        if (isset($field_otions_new['page_break_type_possition']) && $field_otions_new['page_break_type_possition'] == 'top') {
                                $arf_page_break_possition_top = 1;
                        } else if (isset($field_otions_new['page_break_type_possition']) && $field_otions_new['page_break_type_possition'] == 'bottom') {
                                $arf_page_break_possition_bottom = 1;
                        }

                        

                        if (isset($field_otions_new['pagebreaktabsbar']) && $field_otions_new['pagebreaktabsbar'] == 1) {
                            $arf_hide_bar_belt = 1;
                        }
                    }

                    if ((isset($field_otions_new['enable_arf_prefix_' . $field_id_new]) && $field_otions_new['enable_arf_prefix_' . $field_id_new] == 1) || (isset($field_otions_new['enable_arf_suffix_' . $field_id_new]) && $field_otions_new['enable_arf_prefix_' . $field_id_new] == 1) || ($field_otions_new['type'] == 'arf_smiley') || ($field_otions_new['type'] == 'scale') || ($_REQUEST['arfcksn'] == 'custom')) {
                        $is_font_awesome = 1;
                    }

                    if ($field_otions_new['type'] == 'phone' && ( isset($field_otions_new['phone_validation']) && $field_otions_new['phone_validation'] != 'international' )) {
                        $is_input_mask = 1;
                    }

                    if( $field_otions_new['type'] == 'phone' && ( isset($field_otions_new['phonetype']) && $field_otions_new['phonetype'] == 1 ) ){
                        $is_input_mask = 1;
                    }

                    if ($field_otions_new['type'] == 'colorpicker' && (isset($field_otions_new['colorpicker_type']) && $field_otions_new['colorpicker_type'] == 'basic')) {
                        $normal_color_picker = 1;
                    }
                    if ($field_otions_new['type'] == 'colorpicker' && ($field_otions_new['colorpicker_type'] == 'advanced')) {
                        $advance_color_pikcker = 1;
                        $is_font_awesome = 1;
                    }

                    if ($field_otions_new['type'] == 'html' && (isset($field_otions_new['enable_total']) && $field_otions_new['enable_total'] == 1)) {
                        $animate_number = 1;
                        $html_running_total_field_array[] = $field_id_new;
                    }
                    if ($field_otions_new['type'] == 'html' && (isset($field_otions_new['enable_total']) && $field_otions_new['enable_total'] == 1) && (isset($field_otions_new['round_total']) && $field_otions_new['round_total'] == 1)) {
                        $round_total_number = 1;
                    }
                    if ($field_otions_new['type'] == 'captcha' && (isset($field_otions_new['is_recaptcha_' . $field_id_new]) && $field_otions_new['is_recaptcha_' . $field_id_new] == 'recaptcha')) {
                        $google_captcha_loaded = 1;
                    }
                    if ($field_otions_new['type'] == 'arf_autocomplete') {
                        $arf_autocomplete_loaded = 1;
                    }
                    if ($field_otions_new['type'] == 'arf_switch') {
                        $arf_switch_loaded = 1;
                    }
                    
                    if ($field_otions_new['type'] == 'imagecontrol') {
                        $is_imagecontrol_field = 1;
                    }
                    if ((isset($field_otions_new['enable_arf_prefix']) && $field_otions_new['enable_arf_prefix'] == 1) || (isset($field_otions_new['enable_arf_suffix']) && $field_otions_new['enable_arf_suffix'] == 1)) {
                        $is_font_awesome = 1;
                    }

                    if (isset($field_otions_new['tooltip_text']) && $field_otions_new['tooltip_text'] != '') {
                        $is_tooltip = 1;
                    }

                    $field_id_all = $field_id_new;
                    $changed_field_value[] = $field_id_new;   
                    if($field_otions_new['type'] !='hidden'){
                        $new_field_order[$field_id_new] = $temp_order[$field_id_new];                        
                    }
                } else {
                    $field_otions_new["name"] = isset($field_otions_new["name"]) ? $field_otions_new["name"] : '';

                    $insert_default_value = is_array($default_value) ? json_encode($default_value) : $default_value;

                    $field_key = $armainhelper->get_unique_key('', $MdlDb->fields, 'field_key');
                    $new_val = json_decode($value,true);
                    $new_val['key'] = $field_key;
                    $new_val = $this->arfHtmlEntities($new_val);
                    $final_val = json_encode($new_val);
                    $field_otions_new_order = isset($field_otions_new['option_order']) ? $field_otions_new['option_order']: '';
                    $args = array(
                        'field_key' => $field_key,
                        'name' => arf_sanitize_value($field_otions_new['name']),
                        'type' => arf_sanitize_value($field_otions_new['type']),
                        'options' => $field_options,
                        'required' => arf_sanitize_value($field_otions_new['required'], 'integer'),
                        'field_options' => $final_val,
                        'form_id' => arf_sanitize_value($id, 'integer'),
                        'created_date' => current_time('mysql'),
                        'option_order' => $field_otions_new_order
                    );
                    $format = array('%s','%s','%s','%s','%d','%s','%d','%s','%s');
                    $wpdb->insert($MdlDb->fields,$args,$format);
                    
                    $new_id_array[$i]['old_id'] = $field_id_new;
                    $new_id_array[$i]['new_id'] = $wpdb->insert_id;
                    $new_id_array[$i]['name'] = $field_otions_new["name"];
                    $new_id_array[$i]['type'] = $field_otions_new["type"];
                    if($field_otions_new['type'] == 'hidden' ){
                        $hidden_field_ids[] = array(
                            'old_id' => $field_id_new,
                            'new_id' => $wpdb->insert_id
                        );
                    }
                    $field_opt = json_decode($field_options,true);
                    
                    if(json_last_error() != JSON_ERROR_NONE ){
                        $field_opt = maybe_unserialize($field_options);
                    }
                    
                    $changed_field_value[] = $new_field_id = $field_id_all = $wpdb->insert_id;
                    if($field_otions_new["type"] !='hidden'){
                        $new_field_order[$new_field_id] = $temp_order[$field_id_new];                        
                    }

                    if($field_otions_new["type"]=='email')
                    {
                        if($field_otions_new['confirm_email']=='1')
                        {
                            $email_field_key = $armainhelper->get_unique_key('', $MdlDb->fields, 'field_key');
                            $confirm_field_order_arr = json_decode($options['arf_field_order'],true);
                            $confirm_field_order = $confirm_field_order_arr[$field_id_new.'_confirm'];

                            $arf_temp_fields['confirm_email_'.$new_field_id] = array( 'key' => $email_field_key, 'order' => $confirm_field_order, 'parent_field_id' => $new_field_id, 'confirm_inner_class' => $field_otions_new['confirm_email_inner_classes']);
                        }
                    }

                    if($field_otions_new["type"]=='password')
                    {
                        if($field_otions_new['confirm_password']=='1')
                        {
                            $email_field_key = $armainhelper->get_unique_key('', $MdlDb->fields, 'field_key');
                            $confirm_field_order_arr = json_decode($options['arf_field_order'],true);
                            $confirm_field_order = $confirm_field_order_arr[$field_id_new.'_confirm'];
                            
                            $arf_temp_fields['confirm_password_'.$new_field_id] = array( 'key' => $email_field_key, 'order' => $confirm_field_order, 'parent_field_id' => $new_field_id, 'confirm_inner_class' => $field_otions_new['confirm_password_inner_classes']);
                        }
                    }


                    $loaded_field[$i] = $field_otions_new['type'];
                    if ($field_otions_new['type'] == 'break') {
                        $total_page_break++;
                        $page_break[] = $field_id_new;
                        if (isset($field_otions_new['page_break_type']) && $field_otions_new['page_break_type'] == 'survey') {
                            $arf_page_break_survey = 1;
                        
                        } else if (isset($field_otions_new['page_break_type']) && $field_otions_new['page_break_type'] == 'wizard') {
                            
                            $arf_page_break_wizard = 1;
                            
                        }
                        if (isset($field_otions_new['page_break_type_possition']) && $field_otions_new['page_break_type_possition'] == 'top') {
                            $arf_page_break_possition_top = 1;
                        } else if (isset($field_otions_new['page_break_type_possition']) && $field_otions_new['page_break_type_possition'] == 'bottom') {
                            $arf_page_break_possition_bottom = 1;
                        }
                        
                        if (isset($field_otions_new['pagebreaktabsbar']) && $field_otions_new['pagebreaktabsbar'] == 1) {
                            $arf_hide_bar_belt = 1;
                        }
                    }

                    if ((isset($field_otions_new['enable_arf_prefix_' . $field_id_new]) && $field_otions_new['enable_arf_prefix_' . $field_id_new] == 1) || (isset($field_otions_new['enable_arf_suffix_' . $field_id_new]) && $field_otions_new['enable_arf_prefix_' . $field_id_new] == 1) || ($field_otions_new['type'] == 'arf_smiley') || ($field_otions_new['type'] == 'scale') || ($_REQUEST['arfcksn'] == 'custom')) {
                        $is_font_awesome = 1;
                    }

                    if ($field_otions_new['type'] == 'phone' && ( isset($field_otions_new['phone_validation']) && $field_otions_new['phone_validation'] != 'international' )) {
                        $is_input_mask = 1;
                    }

                    if( $field_otions_new['type'] == 'phone' && ( isset($field_otions_new['phonetype']) && $field_otions_new['phonetype'] == 1 ) ){
                        $is_input_mask = 1;
                    }

                    if ($field_otions_new['type'] == 'colorpicker' && (isset($field_otions_new['colorpicker_type']) && $field_otions_new['colorpicker_type'] == 'basic')) {
                        $normal_color_picker = 1;
                    }

                    if ($field_otions_new['type'] == 'colorpicker' && ($field_otions_new['colorpicker_type'] == 'advanced')) {
                        $advance_color_pikcker = 1;
                        $is_font_awesome = 1;
                    }

                    if ($field_otions_new['type'] == 'html' && (isset($field_otions_new['enable_total']) && $field_otions_new['enable_total'] == 1)) {
                        $animate_number = 1;
                        $html_running_total_field_array[] = $field_id_new;
                    }
                    if ($field_otions_new['type'] == 'html' && (isset($field_otions_new['enable_total']) && $field_otions_new['enable_total'] == 1) && (isset($field_otions_new['round_total']) && $field_otions_new['round_total'] == 1)) {
                        $round_total_number = 1;
                    }

                    if ($field_otions_new['type'] == 'captcha' && (isset($field_otions_new['is_recaptcha_' . $field_id_new]) && $field_otions_new['is_recaptcha_' . $field_id_new] == 'recaptcha')) {
                        $google_captcha_loaded = 1;
                    }
                    if ($field_otions_new['type'] == 'arf_autocomplete') {
                        $arf_autocomplete_loaded = 1;
                    }
                    if ($field_otions_new['type'] == 'imagecontrol') {
                        $is_imagecontrol_field = 1;
                    }
                    if ((isset($field_otions_new['enable_arf_prefix']) && $field_otions_new['enable_arf_prefix'] == 1) || (isset($field_otions_new['enable_arf_suffix']) && $field_otions_new['enable_arf_suffix'] == 1)) {
                        $is_font_awesome = 1;
                    }

                    if (isset($field_otions_new['tooltip_text']) && $field_otions_new['tooltip_text'] != '') {
                        $is_tooltip = 1;
                    }

                    
                    $ar_email_subject = '[' . $field_otions_new["name"] . ':' . $field_id_new . ']';
                    $replace_with_ar_email_subject = '[' . $field_otions_new["name"] . ':' . $new_field_id . ']';
                    $options['ar_email_subject'] = str_replace($ar_email_subject, $replace_with_ar_email_subject, $options['ar_email_subject']);
                    $return_json_data['ar_email_subject'] = $options['ar_email_subject'];

                    $ar_user_from_email = '[' . $field_otions_new["name"] . ':' . $field_id_new . ']';
                    $replace_with_ar_user_from_email = '[' . $field_otions_new["name"] . ':' . $new_field_id . ']';
                    $options['ar_user_from_email'] = str_replace($ar_user_from_email, $replace_with_ar_user_from_email, $options['ar_user_from_email']);
                    $return_json_data['ar_user_from_email'] = $options['ar_user_from_email'];


                    $ar_user_nreplyto_email = '[' . $field_otions_new["name"] . ':' . $field_id_new . ']';
                    $replace_with_ar_user_nreplyto_email = '[' . $field_otions_new["name"] . ':' . $new_field_id . ']';
                    $options['ar_user_nreplyto_email'] = str_replace($ar_user_nreplyto_email, $replace_with_ar_user_nreplyto_email, $options['ar_user_nreplyto_email']);
                    $return_json_data['ar_user_nreplyto_email'] = $options['ar_user_nreplyto_email'];

                    $ar_email_message =   '[' . $field_otions_new["name"] . ':' . $field_id_new . ']';
                    $replace_with_ar_email_message = '[' . $field_otions_new["name"] . ':' . $new_field_id . ']';
                    $options['ar_email_message'] = str_replace($ar_email_message, $replace_with_ar_email_message, $options['ar_email_message']);
                    $return_json_data['ar_email_message'] = $options['ar_email_message'];

                    $reply_to = '[' . $field_otions_new["name"] . ':' . $field_id_new . ']';
                    $replace_with_reply_to = '[' . $field_otions_new["name"] . ':' . $new_field_id . ']';
                    $options['reply_to'] = str_replace($reply_to, $replace_with_reply_to, $options['reply_to']);
                    $return_json_data['options_admin_reply_to_notification'] = $options['reply_to'];

                    $admin_email_subject = '[' . $field_otions_new["name"] . ':' . $field_id_new . ']';
                    $replace_with_admin_email_subject = '[' . $field_otions_new["name"] . ':' . $new_field_id . ']';
                    $options['admin_email_subject'] = str_replace($admin_email_subject, $replace_with_admin_email_subject, $options['admin_email_subject']);
                    $return_json_data['admin_email_subject'] = $options['admin_email_subject'];

                    $ar_admin_email_message =   '[' . $field_otions_new["name"] . ':' . $field_id_new . ']';
                    $replace_with_ar_admin_email_message = '[' . $field_otions_new["name"] . ':' . $new_field_id . ']';
                    $options['ar_admin_email_message'] = str_replace($ar_admin_email_message, $replace_with_ar_admin_email_message, $options['ar_admin_email_message']);
                    $return_json_data['ar_admin_email_message'] = $options['ar_admin_email_message'];

                    $ar_admin_from_name = '[' . $field_otions_new["name"] . ':' . $field_id_new . ']';
                    $replace_with_ar_admin_from_name = '[' . $field_otions_new["name"] . ':' . $new_field_id . ']';
                    $options['ar_admin_from_name'] = str_replace($ar_admin_from_name, $replace_with_ar_admin_from_name, $options['ar_admin_from_name']);
                    $return_json_data['options_ar_admin_from_name'] = $options['ar_admin_from_name'];

                    $ar_admin_cc_email = '[' . $field_otions_new["name"] . ':' . $field_id_new . ']';
                    $replace_with_ar_admin_cc_email = '[' . $field_otions_new["name"] . ':' . $new_field_id . ']';
                    $options['ar_admin_cc_email'] = str_replace($ar_admin_cc_email, $replace_with_ar_admin_cc_email, $options['admin_cc_email']);
                    $options['admin_cc_email'] = $options['ar_admin_cc_email'];
                    

                    $ar_admin_bcc_email = '[' . $field_otions_new["name"] . ':' . $field_id_new . ']';
                    $replace_with_ar_admin_bcc_email = '[' . $field_otions_new["name"] . ':' . $new_field_id . ']';
                    $options['ar_admin_bcc_email'] = str_replace($ar_admin_bcc_email, $replace_with_ar_admin_bcc_email, $options['admin_bcc_email']);
                    $options['admin_bcc_email'] = $options['ar_admin_bcc_email'];

                    $ar_admin_from_email = '[' . $field_otions_new["name"] . ':' . $field_id_new . ']';
                    $replace_with_ar_admin_from_email = '[' . $field_otions_new["name"] . ':' . $new_field_id . ']';
                    $options['ar_admin_from_email'] = str_replace($ar_admin_from_email, $replace_with_ar_admin_from_email, $options['ar_admin_from_email']);
                    $return_json_data['ar_admin_from_email'] = $options['ar_admin_from_email'];

                    $ar_admin_reply_to_email = '[' . $field_otions_new["name"] . ':' . $field_id_new . ']';
                    $replace_with_ar_admin_reply_to_email = '[' . $field_otions_new["name"] . ':' . $new_field_id . ']';
                    $options['ar_admin_reply_to_email'] = str_replace($ar_admin_reply_to_email, $replace_with_ar_admin_reply_to_email, $options['ar_admin_reply_to_email']);
                    $return_json_data['ar_admin_reply_to_email'] = $options['ar_admin_reply_to_email'];
                }
                if ($field_otions_new["type"] == 'html') {
                    $value_array = json_decode($value, true);
                    if ($value_array['enable_total'] == 1) {
                        $content_array[$field_id_all]['html_content'] = $value_array['description'];
                    }
                }
                $i++;
            }
        }
        $options['arf_form_other_css'] = isset($values['options']['arf_form_other_css']) ? addslashes($values['options']['arf_form_other_css']) : '';        
        if($options['arf_form_other_css'] !='' && ($str['arfaction'] == 'new' || $str['arfaction'] == 'duplicate'))
        {
            $temp_arf_form_other_css =  str_replace($temp_form_id , $id, $options['arf_form_other_css']);
            $options['arf_form_other_css'] = $temp_arf_form_other_css;
        }        
        
        $options['arf_loaded_field'] = ($loaded_field);
        $options['total_page_break'] = $total_page_break;
        $options['page_break_field'] = $page_break;
        $options['font_awesome_loaded'] = $is_font_awesome;
        $options['tooltip_loaded'] = $is_tooltip;
        $options['arf_input_mask'] = $is_input_mask;
        $options['arf_normal_colorpicker'] = $normal_color_picker;
        $options['arf_advance_colorpicker'] = $advance_color_pikcker;
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
        $options['calender_theme'] = isset($values['arffths']) ? $values['arffths'] : 'default_theme';
        $autoresponder_fname = $db_data['autoresponder_fname'];
        $autoresponder_lname = $db_data['autoresponder_lname'];
        $autoresponder_email = $db_data['autoresponder_email'];
        $new_html_running_total = array();
        foreach ($new_id_array as $key_new => $value_new) {
            if($options['ar_email_to'] == $value_new["old_id"]){
                $options['ar_email_to'] = str_replace($value_new["old_id"], $value_new["new_id"], $options['ar_email_to']);
                $return_json_data['options_ar_user_email_to'] = $options['ar_email_to'];
            }
            if($db_data['autoresponder_fname'] == $value_new["old_id"]){
                $autoresponder_fname = str_replace($value_new["old_id"], $value_new["new_id"], $autoresponder_fname);
                $return_json_data['autoresponder_fname'] = $autoresponder_fname;
            }
            if($db_data['autoresponder_lname'] == $value_new["old_id"]){
                $autoresponder_lname = str_replace($value_new["old_id"], $value_new["new_id"], $autoresponder_lname);
                $return_json_data['autoresponder_lname'] = $autoresponder_lname;
            }
            if($db_data['autoresponder_email'] == $value_new["old_id"]){
                $autoresponder_email = str_replace($value_new["old_id"], $value_new["new_id"], $autoresponder_email);
                $return_json_data['autoresponder_email'] = $autoresponder_email;
            }
            if($options['arf_pre_dup_field'] == $value_new["old_id"]){
                $options['arf_pre_dup_field'] = str_replace($value_new["old_id"], $value_new["new_id"], $options['arf_pre_dup_field']);
                $return_json_data['arf_pre_dup_field'] = $options['arf_pre_dup_field'];
            }
            foreach ($options['html_running_total_field_array'] as $html_running_total_field_array_key => $html_running_total_field_array_value) {
                if($html_running_total_field_array_value == $value_new["old_id"]){
                    $new_html_running_total[$html_running_total_field_array_key] = str_replace($value_new["old_id"], $value_new["new_id"], $html_running_total_field_array_value);
                } else {
                    $new_html_running_total[$html_running_total_field_array_key] = $html_running_total_field_array_value;
                }
            }
            $options['html_running_total_field_array'] = $new_html_running_total;
            $options = apply_filters('arf_update_form_option_outside',$options,$return_json_data,$value_new["old_id"],$value_new["new_id"]);
            
            $return_json_data = apply_filters('arf_update_form_return_json_outside',$return_json_data,$options);
        }

        $new_conditional_logic_fields = array();

        foreach ($options['arf_conditional_logic_rules'] as $key => $value) {
            foreach ($value['condition'] as $key_condition => $value_condition) {
                foreach ($new_id_array as $key_new => $value_new) {
                    if($value_condition['field_id'] == $value_new["old_id"]){
                        $value_condition['field_id'] = str_replace($value_new["old_id"], $value_new["new_id"], $value_condition['field_id']);
                        $return_json_data['arf_condition_field_'.$key.'_'.$key_condition] = $value_condition['field_id'];
                        array_push($new_conditional_logic_fields,$value_new["new_id"]);
                    }
                }
                $value['condition'][$key_condition] = $value_condition;
            }
            
            foreach ($value['result'] as $key_result => $value_result) {
                foreach ($new_id_array as $key_new => $value_new) {
                    if($value_result['field_id'] == $value_new["old_id"]){
                        $value_result['field_id'] = str_replace($value_new["old_id"], $value_new["new_id"], $value_result['field_id']);
                        $return_json_data['arf_result_field_'.$key.'_'.$key_result] = $value_result['field_id'];
                    }
                }
                $value['result'][$key_result] = $value_result;
            }
            $new_conditional_logic[$key] = $value;
        }
        $options['arf_conditional_logic_rules'] = $new_conditional_logic;

        $new_submit_conditional_logic = $options['submit_conditional_logic'];
        foreach ($options['submit_conditional_logic']['rules'] as $key => $value) {

            foreach ($new_id_array as $key_new => $value_new) {
                if($value_new['old_id'] == $value['field_id'] ){
                    $value['field_id'] = str_replace($value_new["old_id"], $value_new["new_id"], $value['field_id']);
                    $return_json_data['arf_cl_field_arfsubmit_'.$key] = $value['field_id'];
                    array_push($new_conditional_logic_fields,$value_new["new_id"]);
                }
            }
            $new_submit_conditional_logic['rules'][$key] = $value;
        }
        $options['submit_conditional_logic'] = $new_submit_conditional_logic;

        $new_conditional_mail = $options['arf_conditional_mail_rules'];
        foreach ($options['arf_conditional_mail_rules'] as $key => $value) {
            foreach ($new_id_array as $key_new => $value_new) {
                if($value_new['old_id'] == $value['field_id_mail']){
                    $value['field_id_mail'] = str_replace($value_new["old_id"], $value_new["new_id"], $value['field_id_mail']);
                    $return_json_data['arf_conditional_mail_filed_'.$key] = $value['field_id_mail'];
                }
                if($value_new['old_id'] == $value['send_mail_field'] ){
                    $value['send_mail_field'] = str_replace($value_new["old_id"], $value_new["new_id"], $value['send_mail_field']);
                    $return_json_data['arf_conditional_mailto_filed_'.$key] = $value['send_mail_field'];
                }
            }
            $new_conditional_mail[$key] = $value;
        }
        $options['arf_conditional_mail_rules'] = $new_conditional_mail;

        $new_conditional_redirect_rules = $options['arf_conditional_redirect_rules'];
        foreach ($options['arf_conditional_redirect_rules'] as $key => $value) {
            foreach ($new_id_array as $key_new => $value_new) {
                if($value_new['old_id'] == $value['field_id'] ){
                    $value['field_id'] = str_replace($value_new["old_id"], $value_new["new_id"], $value['field_id']);
                    $return_json_data['arf_conditional_redirect_filed_'.$key] = $value['field_id'];
                }
            }
            $new_conditional_redirect_rules[$key] = $value;
        }
        $options['arf_conditional_redirect_rules'] = $new_conditional_redirect_rules;

        $new_conditional_on_subscription_rules = $options['arf_condition_on_subscription_rules'];
        foreach ($options['arf_condition_on_subscription_rules'] as $key => $value) {
            foreach ($new_id_array as $key_new => $value_new) {
                if($value_new['old_id'] == $value['field_id']){
                    $value['field_id'] = str_replace($value_new["old_id"], $value_new["new_id"], $value['field_id']);
                    $return_json_data['arf_subscription_condition_field_'.$key] = $value['field_id'];
                }
            }
            $new_conditional_on_subscription_rules[$key] = $value;
        }
        $options['arf_condition_on_subscription_rules'] = $new_conditional_on_subscription_rules;
        $running_total_fields = array();
        if (in_array('html', $type_array)) {
            if (!empty($content_array) && !empty($new_id_array)) {
                foreach ($content_array as $key_type => $value_type) {
                    $arf_html_content_new = $value_type['html_content'];
                    foreach ($new_id_array as $key_new => $value_new) {
                        
                        $arf_html_content = ':' . $value_new["old_id"] . ']';
                        $replace_with_arf_html_content = ':' . $value_new["new_id"] . ']';
                        if($value_new['type'] == 'checkbox'){
                            
                            $pattern_ch = "/\:(".$value_new['old_id'].")(\.\d+)\]/";
                            $pattern = "/\:(\d+)/";
                            preg_match_all($pattern,$replace_with_arf_html_content,$Matches);
                            if(isset($Matches[1]) && count($Matches[1]) > 0){
                                foreach($Matches[1] as $kk => $Match){
                                    $arf_html_content_new = preg_replace($pattern_ch,':'.$Match.'$2]',$arf_html_content_new);
                                    $running_total_fields[$Match][] = $key_type;
                                }
                            }
                        } else {
                            $arf_html_content_new = str_replace($arf_html_content, $replace_with_arf_html_content, $arf_html_content_new);
                            $pattern = "/\:\d+/";
                            
                            $pattern_new = "/\<arftotal\>(.*?)\<\/arftotal\>/is";

                            preg_match_all($pattern_new,$arf_html_content_new,$matches_new);                
                            
                            if( isset($matches_new[1]) && isset($matches_new[1][0]) && $matches_new[1][0] != '' ){
                                
                                preg_match_all($pattern,$matches_new[1][0],$matches);
                                if( isset($matches[0]) && is_array($matches[0]) && !empty($matches[0]) ){
                                    foreach( $matches[0] as $k => $val ){
                                        $running_total_fields[preg_replace('/[^0-9]/','',$val)][] = $field_d['id'];
                                    }
                                }
                            }

                            if(isset($matches[0]) && $matches[0] != '' ){
                                foreach( $matches[0] as $k => $val ){
                                    $running_total_fields[preg_replace('/[^0-9]/','',$val)][] = $key_type;
                                }
                            }
                        }
                    }
                    $fleld_data = $wpdb->get_results($wpdb->prepare("SELECT field_options FROM " . $MdlDb->fields . " WHERE id=%d" , $key_type));
                    $fleld_data_options = json_decode($fleld_data[0]->field_options, 1);
                    $fleld_data_options['description'] = arf_sanitize_value($arf_html_content_new,'text',true);
                    $fleld_data_options = json_encode( $fleld_data_options );
                    
                    $wpdb->update($MdlDb->fields, array('field_options'=>$fleld_data_options), array('id' => $key_type) );
                }
            }
        }
        $running_total_fields = array_map('array_unique', array_map('array_values',$running_total_fields));
        
        if(isset($running_total_fields) && count($running_total_fields) > 0 ){
            foreach($running_total_fields as $k => $rtfield_id){
                
                foreach($rtfield_id as $i => $rtfield ){
                    $is_rt_enable = $wpdb->get_results($wpdb->prepare("SELECT enable_running_total FROM `".$MdlDb->fields."` WHERE id = %d",$k));
                    if(isset($is_rt_enable) && count($is_rt_enable) > 0 ){
                        $new_total_field = '';
                        foreach($is_rt_enable as $i => $rtenable){
                            if(isset($rtenable->enable_running_total) && $rtenable->enable_running_total != '' && $rtenable->enable_running_total > 0 ){
                                $new_total_fields = explode(',',$rtenable->enable_running_total);
                                $new_total_fields = array_unique($new_total_fields);
                                if(!in_array($rtfield,$rtenable->enable_running_total) ){
                                    array_push($new_total_fields,$rtfield);
                                }
                                $new_total_fields = array_unique($new_total_fields);
                                $new_total_field = implode(',',$new_total_fields);
                                $wpdb->update($MdlDb->fields,array('enable_running_total' => $new_total_field),array('id'=> $k));
                            } else {
                                $wpdb->update($MdlDb->fields,array('enable_running_total' => $rtfield),array('id'=> $k));
                            }
                        }
                    }
                }
            }
        }

        if(isset($new_conditional_logic_fields) && count($new_conditional_logic_fields) > 0 ){
            $new_conditional_logic_fields = array_unique($new_conditional_logic_fields);
            foreach($new_conditional_logic_fields as $fk => $new_cl_field_id){
                $wpdb->update($MdlDb->fields,array('conditional_logic'=>1),array('id'=>$new_cl_field_id));
            }
        }

        $values_field_order = json_decode($values['arf_field_order'], true);
        $final_field_order = array();
        foreach($values_field_order as $values_field_order_key => $values_field_order_value )
        {
            if(!array_key_exists($values_field_order_key, $new_field_order) && is_int($values_field_order_key))
            {
                $changed_new_field_key = array_search ( $values_field_order_value, $new_field_order);
                $final_field_order[$changed_new_field_key] = $values_field_order_value;

                if(array_key_exists($values_field_order_key.'_confirm', $values_field_order)) {
                    unset($final_field_order[$values_field_order_key.'_confirm']);
                    
                    $final_field_order[$changed_new_field_key.'_confirm'] = $values_field_order[$values_field_order_key.'_confirm'];
                    
                    unset($values_field_order[$values_field_order_key.'_confirm']);
                }
            }
            else if(array_key_exists($values_field_order_key, $new_field_order) && is_int($values_field_order_key))
            {
                $final_field_order[$values_field_order_key] = $values_field_order_value;

                if(array_key_exists($values_field_order_key.'_confirm', $values_field_order)) {
                    unset($final_field_order[$values_field_order_key.'_confirm']);
                    
                    $final_field_order[$values_field_order_key.'_confirm'] = $values_field_order[$values_field_order_key.'_confirm'];
                    
                    unset($values_field_order[$values_field_order_key.'_confirm']);
                }
            }
            else {
                if (strpos($values_field_order_key, '_confirm') === false ) 
                {
                    $final_field_order[$values_field_order_key] = $values_field_order_value;
                }
            }
        }
        $options['arf_field_order'] = isset($final_field_order) ? json_encode($final_field_order) : array();

        $selectDeletedFields = array();
        if (isset($changed_field_value) and ! empty($changed_field_value)) {
            $selectDeletedFields = $wpdb->get_results($wpdb->prepare("SELECT id FROM `".$MdlDb->fields."` WHERE id NOT IN( '". implode('\',\'',$changed_field_value) . "') AND form_id = %d",$id));
            $del_fields = $wpdb->query($wpdb->prepare("delete from " . $MdlDb->fields . " where form_id = %d and id NOT IN (" . implode(',', $changed_field_value) . ")", $id));
        } else if (empty($changed_field_value)) {
            $del_fields = $wpdb->query($wpdb->prepare("delete from " . $MdlDb->fields . " where form_id = %d", $id));
        }
        if(isset($selectDeletedFields) && count($selectDeletedFields) > 0 ){
            foreach($selectDeletedFields as $k => $deleted_fields ){
                $is_html_fields = $wpdb->get_results($wpdb->prepare("SELECT id,enable_running_total FROM `".$MdlDb->fields."` WHERE `enable_running_total` LIKE %s AND form_id = %d",'%'.$deleted_fields->id.'%',$id));
                if(isset($is_html_fields) && count($is_html_fields) > 0 ){
                    foreach($is_html_fields as $hf => $hf_id){
                        if(isset($hf_id) && $hf_id->enable_running_total != '' ){
                            $running_total_field = $hf_id->enable_running_total;
                            $rt_fields = explode(',',$running_total_field);
                            foreach($rt_fields as $k => $rtfield){
                                if($rtfield == $deleted_fields->id ){
                                    unset($rt_fields[$k]);
                                }
                            }
                            if(count($rt_fields) < 1 ){
                                $rt_fields = "";
                            } else {
                                $rt_fields = implode(',',$rt_fields);
                            }
                            $wpdb->update($MdlDb->fields,array('enable_running_total'=>$rt_fields),array('id'=>$hf_id->id));
                        }
                    }
                }
            }
        }
        
        $query_results = $wpdb->query("update " . $MdlDb->forms . " set options = '" . addslashes(maybe_serialize($options)) . "' , autoresponder_fname ='".$autoresponder_fname."' , autoresponder_lname ='".$autoresponder_lname."' , autoresponder_email ='".$autoresponder_email."' , temp_fields='".maybe_serialize($arf_temp_fields)."' where id = '" . $id . "'");
        
        if (isset($_REQUEST['autoresponder']) and count($_REQUEST['autoresponder']) > 0) {
            foreach ($_REQUEST['autoresponder'] as $aresponder) {
                $_REQUEST['autoresponder_id'] .= $aresponder . "|";
            }
        } else {
            $_REQUEST['autoresponder_id'] = "";
        }

        $type = (get_option('arf_ar_type')!='') ? maybe_unserialize(get_option('arf_ar_type')) : '';
        $autoresponder_all_data_query = $wpdb->get_results("SELECT * FROM " . $MdlDb->autoresponder, 'ARRAY_A');
        
        $res = $autoresponder_all_data_query[2];
        if (isset($_REQUEST['autoresponders']) && in_array('3', $_REQUEST['autoresponders'])) {
            $aweber_arr['enable'] = arf_sanitize_value(1, 'integer');
        } else {
            $aweber_arr['enable'] = arf_sanitize_value(0, 'integer');
        }

        if ($type['aweber_type'] == 1) {
            $aweber_arr['type'] = arf_sanitize_value(1, 'integer');
            $aweber_arr['type_val'] = isset($_REQUEST['i_aweber_list']) ? arf_sanitize_value($_REQUEST['i_aweber_list']) : '';
        } else if ($type['aweber_type'] == 0) {
            $aweber_arr['type'] = arf_sanitize_value(0, 'integer');
            $aweber_arr['type_val'] = isset($_REQUEST['web_form_aweber']) ? stripslashes_deep(arf_sanitize_value($_REQUEST['web_form_aweber'])) : '';
        }

        
        $res = $autoresponder_all_data_query[0];

        if (isset($_REQUEST['autoresponders']) && in_array('1', $_REQUEST['autoresponders'])) {
            $mailchimp_arr['enable'] = arf_sanitize_value(1, 'integer');
        } else {
            $mailchimp_arr['enable'] = arf_sanitize_value(0, 'integer');
        }

        if ($type['mailchimp_type'] == 1) {
            $mailchimp_arr['type'] = arf_sanitize_value(1, 'integer');
            $mailchimp_arr['type_val'] = isset($_REQUEST['i_mailchimp_list']) ? arf_sanitize_value($_REQUEST['i_mailchimp_list']) : '';
            $mailchimp_arr['double_optin'] = arf_sanitize_value($double_optin, 'integer');
        } else if ($type['mailchimp_type'] == 0) {
            $mailchimp_arr['type'] = arf_sanitize_value(0, 'integer');
            $mailchimp_arr['type_val'] = isset($_REQUEST['web_form_mailchimp']) ? stripslashes_deep(arf_sanitize_value($_REQUEST['web_form_mailchimp'])) : '';
        }

        
        $res = $autoresponder_all_data_query[9];

        if (isset($_REQUEST['autoresponders']) && in_array('10', $_REQUEST['autoresponders'])) {
            $madmimi_arr['enable'] = arf_sanitize_value(1, 'integer');
        } else {
            $madmimi_arr['enable'] = arf_sanitize_value(0, 'integer');
        }

        if ($type['madmimi_type'] == 1) {
            $madmimi_arr['type'] = arf_sanitize_value(1, 'integer');
            $madmimi_arr['type_val'] = isset($_REQUEST['i_madmimi_list']) ? arf_sanitize_value($_REQUEST['i_madmimi_list']) : '';
        } else if ($type['madmimi_type'] == 0) {
            $madmimi_arr['type'] = arf_sanitize_value(0, 'integer');
            $madmimi_arr['type_val'] = isset($_REQUEST['web_form_madmimi']) ? stripslashes_deep(arf_sanitize_value($_REQUEST['web_form_madmimi'])) : '';
        }

        
        $res = $autoresponder_all_data_query[3];

        if (isset($_REQUEST['autoresponders']) && in_array('4', $_REQUEST['autoresponders'])) {
            $getresponse_arr['enable'] = arf_sanitize_value(1,'integer');
        } else {
            $getresponse_arr['enable'] = arf_sanitize_value(0,'integer');
        }

        if ($type['getresponse_type'] == 1) {
            $getresponse_arr['type'] = arf_sanitize_value(1, 'integer');
            $getresponse_arr['type_val'] = isset($_REQUEST['i_campain_name']) ? arf_sanitize_value($_REQUEST['i_campain_name']) : '';
        } else if ($type['getresponse_type'] == 0) {
            $getresponse_arr['type'] = arf_sanitize_value(0, 'integer');
            $getresponse_arr['type_val'] = (isset($_REQUEST['web_form_getresponse'])) ? stripslashes_deep(arf_sanitize_value($_REQUEST['web_form_getresponse'])) : '';
        }

        
        $res = $autoresponder_all_data_query[7];

        if (isset($_REQUEST['autoresponders']) && in_array('8', $_REQUEST['autoresponders'])) {
            $icontact_arr['enable'] = arf_sanitize_value(1, 'integer');
        } else {
            $icontact_arr['enable'] = arf_sanitize_value(0, 'integer');
        }

        if ($type['icontact_type'] == 1) {
            $icontact_arr['type'] = arf_sanitize_value(1, 'integer');
            $icontact_arr['type_val'] = isset($_REQUEST['i_icontact_list']) ? arf_sanitize_value($_REQUEST['i_icontact_list']) : '';
        } else if ($type['icontact_type'] == 0) {
            $icontact_arr['type'] = arf_sanitize_value(0, 'integer');
            $icontact_arr['type_val'] = (isset($_REQUEST['web_form_icontact'])) ? stripslashes_deep(arf_sanitize_value($_REQUEST['web_form_icontact'])) : '';
        }

        
        $res = $autoresponder_all_data_query[8];

        if (isset($_REQUEST['autoresponders']) && in_array('9', $_REQUEST['autoresponders'])) {
            $constant_contact_arr['enable'] = arf_sanitize_value(1, 'integer');
        } else {
            $constant_contact_arr['enable'] = arf_sanitize_value(0, 'integer');
        }


        if ($type['constant_type'] == 1) {
            $constant_contact_arr['type'] = arf_sanitize_value(1, 'integer');
            $constant_contact_arr['type_val'] = isset($_REQUEST['i_constant_contact_list']) ? arf_sanitize_value($_REQUEST['i_constant_contact_list']) : '';
        } else if ($type['constant_type'] == 0) {
            $constant_contact_arr['type'] = arf_sanitize_value(0, 'integer');
            $constant_contact_arr['type_val'] = (isset($_REQUEST['web_form_constant_contact'])) ? arf_sanitize_value(stripslashes_deep($_REQUEST['web_form_constant_contact'])) : '';
        }

        
        $res = $autoresponder_all_data_query[4];

        if (isset($_REQUEST['autoresponders']) && in_array('5', $_REQUEST['autoresponders'])) {
            $gvo_arr['enable'] = arf_sanitize_value(1, 'integer');
        } else {
            $gvo_arr['enable'] = arf_sanitize_value(0, 'integer');
        }

        if ($type['gvo_type'] == 0) {
            $gvo_arr['type'] = arf_sanitize_value(0, 'integer');
            $gvo_arr['type_val'] = (isset($_REQUEST['web_form_gvo'])) ? stripslashes_deep(arf_sanitize_value($_REQUEST['web_form_gvo'])) : '';
        }

        
        $res = $autoresponder_all_data_query[5];

        if (isset($_REQUEST['autoresponders']) && in_array('6', $_REQUEST['autoresponders'])) {

            $ebizac_arr['enable'] = arf_sanitize_value(1, 'integer');
        } else {

            $ebizac_arr['enable'] = arf_sanitize_value(0, 'integer');
        }

        if ($type['ebizac_type'] == 0) {
            $ebizac_arr['type'] = arf_sanitize_value(0, 'integer');
            $ebizac_arr['type_val'] = (isset($_REQUEST['web_form_ebizac'])) ? stripslashes_deep(arf_sanitize_value($_REQUEST['web_form_ebizac'])) : '';
        }

        $ar_global_autoresponder = array(
            'aweber' => $aweber_arr['enable'],
            'mailchimp' => $mailchimp_arr['enable'],
            'getresponse' => $getresponse_arr['enable'],
            'gvo' => $gvo_arr['enable'],
            'ebizac' => $ebizac_arr['enable'],
            'madmimi' => $madmimi_arr['enable'],
            'icontact' => $icontact_arr['enable'],
            'constant_contact' => $constant_contact_arr['enable'],
        );

        $ar_aweber = maybe_serialize($aweber_arr);
        $ar_mailchimp = maybe_serialize($mailchimp_arr);
        $ar_madmimi = maybe_serialize($madmimi_arr);
        $ar_getresponse = maybe_serialize($getresponse_arr);
        $ar_gvo = maybe_serialize($gvo_arr);
        $ar_ebizac = maybe_serialize($ebizac_arr);
        $ar_icontact = maybe_serialize($icontact_arr);
        $ar_constant_contact = maybe_serialize($constant_contact_arr);

        $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->ar . " WHERE frm_id = %d", $id), 'ARRAY_A');
        $enable_ar = maybe_serialize($ar_global_autoresponder);

        if ($wpdb->num_rows != 1) {
            $res = $wpdb->query($wpdb->prepare("INSERT INTO " . $MdlDb->ar . " (frm_id, aweber, mailchimp, getresponse, gvo, ebizac,madmimi,icontact, constant_contact, enable_ar) VALUES (%d, %s, %s,%s, %s,%s,%s, %s, %s, %s)", arf_sanitize_value($id, 'integer'), $ar_aweber, $ar_mailchimp, $ar_getresponse, $ar_gvo, $ar_ebizac, $ar_madmimi, $ar_icontact, $ar_constant_contact, $enable_ar));
            do_action('arf_autoresponder_after_insert', $wpdb->insert_id, $_REQUEST);
        } else {
            $res = $wpdb->update($MdlDb->ar, array('aweber' => $ar_aweber, 'mailchimp' => $ar_mailchimp, 'getresponse' => $ar_getresponse, 'gvo' => $ar_gvo, 'ebizac' => $ar_ebizac, 'madmimi' => $ar_madmimi, 'icontact' => $ar_icontact, 'constant_contact' => $ar_constant_contact, 'enable_ar' => $enable_ar), array('frm_id' => $id));
            do_action('arf_autoresponder_after_update', $id, $_REQUEST);
        }

        do_action('arfafterupdateform', $id, $values, false, 0);
        do_action('arfafterupdateform_' . $id, $id, $values, false, 0);

        do_action('arfupdateform_' . $id, $values);

        $query_results = apply_filters('arfchangevaluesafterupdateform', $query_results);


        $sel_fields = $wpdb->prepare("select * from " . $MdlDb->fields . " where form_id = %d", $str['id']);

        $res_fields_arr = $wpdb->get_results($sel_fields, 'ARRAY_A');

        $scale_field_available = "";
        $selectbox_field_available = "";
        $radio_field_available = "";
        $checkbox_field_available = "";

        foreach ($res_fields_arr as $res_fields) {
            if ($res_fields["type"] == "scale" && $scale_field_available == "") {
                $scale_field_available = true;
            }

            if (( $res_fields["type"] == "select" || $res_fields["type"] == ARF_AUTOCOMPLETE_SLUG || $res_fields["type"] == "time") && $selectbox_field_available == "") {
                $selectbox_field_available = true;
            }

            if ($res_fields["type"] == "checkbox" && $checkbox_field_available == "") {
                $checkbox_field_available = true;
            }

            if ($res_fields["type"] == "radio" && $radio_field_available == "") {
                $radio_field_available = true;
            }
        }

        $wp_upload_dir = wp_upload_dir();
        $upload_dir = $wp_upload_dir['basedir'] . '/arforms/css/';
        $dest_dir = $wp_upload_dir['basedir'] . '/arforms/maincss/';
        $dest_css_url = $wp_upload_dir['baseurl'] . '/arforms/maincss/';

        $form_id = $id;

        $cssoptions = $form_css;
        $preview = "none";
        if (count($cssoptions) > 0) {
            $new_values = array();
            $temp_new_values = array();
            
            foreach ($cssoptions as $k => $v) {
                $new_values[$k] = $temp_new_values[$k] = str_replace('##', '#', $v);
            }

            $saving = true;
            $use_saved = true;

            $arfssl = (is_ssl()) ? 1 : 0;

            $preview = false;
            $filename = FORMPATH . '/core/css_create_main.php';

            $wp_upload_dir = wp_upload_dir();

            $target_path = $wp_upload_dir['basedir'] . '/arforms/maincss';

            $temp_css_file = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";

            $temp_css_file .= "\n";

            ob_start();

            include $filename;

            $temp_css_file .= str_replace('##', '#', ob_get_contents());

            ob_end_clean();

            $temp_css_file .= "\n " . $warn;

            
            $file_name_materialize = FORMPATH . '/core/css_create_materialize.php';

            $wp_upload_dir = wp_upload_dir();

            $target_path = $wp_upload_dir['basedir'] . '/arforms/maincss';

            $temp_materialize_file = $materialize_warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";

            $temp_materialize_file .= "\n";

            ob_start();

            include $file_name_materialize;

            $temp_materialize_file .= str_replace('##', '#', ob_get_contents());

            ob_end_clean();

            $temp_materialize_file .= "\n " . $materialize_warn;
            
        } else {

            $temp_css_file = file_get_contents($upload_dir . 'arforms.css');
            $temp_css_file = str_replace('.ar_main_div_', '.ar_main_div_' . $id, $temp_css_file);
            $temp_css_file = str_replace('#popup-form-', '#popup-form-' . $id, $temp_css_file);
            $temp_css_file = str_replace('cycle_', 'cycle_' . $id, $temp_css_file);
            $temp_css_file = str_replace('##', '#', $temp_css_file);
        }

        if ($scale_field_available == "") {

            $start_get_css_rate_position = strpos($temp_css_file, '/*arf star rating css start*/');
            $end_get_css_rate_position = strpos($temp_css_file, '/*arf star rating css end*/');

            $end_get_css_rate_lenght = strlen('/*arf star rating css end*/');

            if ($start_get_css_rate_position && $end_get_css_rate_position) {

                $temp_css_file_star_rating = substr($temp_css_file, $start_get_css_rate_position, ($end_get_css_rate_position + $end_get_css_rate_lenght) - $start_get_css_rate_position);
                $temp_css_file = str_replace($temp_css_file_star_rating, '', $temp_css_file);
            }
        }
        if ($selectbox_field_available == "") {
            $start_get_css_selbox_position = strpos($temp_css_file, '/*arf selectbox css start*/');
            $end_get_css_selbox_position = strpos($temp_css_file, '/*arf selectbox css end*/');

            $end_get_css_selbox_lenght = strlen('/*arf selectbox css end*/');

            if ($start_get_css_selbox_position && $end_get_css_selbox_position) {

                $temp_css_file_star_selectbox = substr($temp_css_file, $start_get_css_selbox_position, ($end_get_css_selbox_position + $end_get_css_selbox_lenght) - $start_get_css_selbox_position);
                $temp_css_file = str_replace($temp_css_file_star_selectbox, '', $temp_css_file);
            }
        }

        if ($radio_field_available == "" && $checkbox_field_available == "") {
            $start_get_css_radiocheck_position = strpos($temp_css_file, '/*arf checkbox radio css start*/');
            $end_get_css_radiocheck_position = strpos($temp_css_file, '/*arf checkbox radio css end*/');

            $end_get_css_radiocheck_lenght = strlen('/*arf checkbox radio css end*/');

            if ($start_get_css_radiocheck_position && $end_get_css_radiocheck_position) {

                $temp_css_file_radiocheckbox = substr($temp_css_file, $start_get_css_radiocheck_position, ($end_get_css_radiocheck_position + $end_get_css_radiocheck_lenght) - $start_get_css_radiocheck_position);
                $temp_css_file = str_replace($temp_css_file_radiocheckbox, '', $temp_css_file);
            }
        }

        $css_file_new = $dest_dir . 'maincss_' . $id . '.css';

        $material_css_file_new = $dest_dir . 'maincss_materialize_' . $id . '.css';


        WP_Filesystem();
        global $wp_filesystem;
        $temp_css_file = str_replace('##', '#', $temp_css_file);
        $temp_materialize_file = str_replace('##', '#', $temp_materialize_file);
        $wp_filesystem->put_contents($css_file_new, $temp_css_file, 0777);
        $wp_filesystem->put_contents($material_css_file_new, $temp_materialize_file, 0777);

        $message = addslashes(esc_html__('Form is saved successfully.', 'ARForms'));
        if(isset($hidden_field_ids) && !empty($hidden_field_ids) && count($hidden_field_ids) > 0 ){
            $return_json_data['arf_hidden_field_ids'] = $hidden_field_ids;
        }
        
        $return_json_data['arf_default_newarr'] = json_encode($temp_new_values);
        $return_json_data_final = json_encode($return_json_data);
        echo $message . '^|^' . $id . '^|^' . $return_json_data_final . '^|^' ;

        $all_fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM `" . $MdlDb->fields . "` WHERE form_id = %d", $id));
        $arf_all_fields = array();

        foreach ($all_fields as $key => $field_) {
            foreach ($field_ as $k => $field_val) {
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
                    foreach ($field_opts as $ki => $val_) {
                        $arf_all_fields[$key][$ki] = $val_;
                    }
                } else {
                    $arf_all_fields[$key][$k] = $field_val;
                }
            }
        }

        $values['fields'] = $arf_all_fields;

        if (isset($values['fields']) && !empty($values['fields'])) {
            $arf_is_page_break_no = 0;
            $arf_load_password = array();
            $arf_load_confirm_email = array();
            $totalpass = 0;
            foreach ($values['fields'] as $arrkey => $field) {
                if ($field['type'] == 'password') {
                    $field['id'] = $arfieldhelper->get_actual_id($field['id']);
                    if (isset($field['confirm_password']) and $field['confirm_password'] == 1 and isset($arf_load_password['confrim_pass_field']) and $arf_load_password['confrim_pass_field'] == $field['id']) {
                        $values['confirm_password_arr'][$field['id']] = isset($field['confirm_password_field']) ? $field['confirm_password_field'] : "";
                    } else {
                        $arf_load_password['confrim_pass_field'] = isset($field['confirm_password_field']) ? $field['confirm_password_field'] : "";
                    }
                }

                if ($field['type'] == 'email') {
                    $field['id'] = $arfieldhelper->get_actual_id($field['id']);
                    if (isset($field['confirm_email']) and $field['confirm_email'] == 1 and isset($arf_load_confirm_email['confrim_email_field']) and $arf_load_confirm_email['confirm_email_field'] == $field['id']) {
                        $values['confirm_email_arr'][$field['id']] = isset($field['confirm_email_field']) ? $field['confirm_email_field'] : "";
                    } else {
                        $arf_load_confirm_email['confrim_email_field'] = isset($field['confirm_email_field']) ? $field['confirm_email_field'] : "";
                    }
                }

                if ($field['type'] == 'email' && isset($field['confirm_email']) && $field['confirm_email'] == 1) {
                    if (isset($field['confirm_email']) and $field['confirm_email'] == 1 and isset($arf_load_confirm_email['confrim_email_field']) and $arf_load_confirm_email['confrim_email_field'] == $field['id']) {
                        $values['confirm_email_arr'][$field['id']] = isset($field['confirm_email_field']) ? $field['confirm_email_field'] : "";
                    } else {
                        $arf_load_confirm_email['confrim_email_field'] = isset($field['confirm_email_field']) ? $field['confirm_email_field'] : "";
                    }
                    $confirm_email_field = $arfieldhelper->get_confirm_email_field($field);
                    $values['fields'] = $arfieldhelper->array_push_after($values['fields'], array($confirm_email_field), $arrkey + $totalpass);
                    $totalpass++;
                }

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
            }
            $field_data = file_get_contents(VIEWS_PATH . '/arf_editor_data.json');

            $field_data_obj = json_decode($field_data);

            $arf_fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM `" . $MdlDb->fields . "` WHERE `form_id` = %d", $id), ARRAY_A);

            $field_order = $final_field_order;
            $field_resize_width = json_decode($options['arf_field_resize_width'],true);
            $data['form_css'] = $db_data['form_css'];
            $frm_css = maybe_unserialize($data['form_css']);
            $arf_sorted_fields = array();
            if ($field_order != '') {
                if (!is_array($field_order)) {
                    $field_order = json_decode($field_order, true);
                }
                asort($field_order);
                foreach ($field_order as $field_id => $order) {
                    if(is_int($field_id))
                    {
                        foreach ($arf_fields as $field) {
                            if ($field_id == $field['id']) {
                                $arf_sorted_fields[] = $field;
                            }
                        }
                    }
                    else {
                        $arf_sorted_fields[] = $field_id;
                    }

                }
            }

            if (isset($arf_sorted_fields) && !empty($arf_sorted_fields)) {
                $arf_fields = $arf_sorted_fields;
            }
            $class_array = array();
            $conut_arf_fields = count($arf_fields);
            $index_arf_fields = 0;
            $arf_field_counter = 1;
            foreach ($arf_fields as $key => $field) {
                if(is_array($field)){
                    if($field['type'] == 'hidden' ){
                       continue;
                    }
                    if ($field['type'] == 'break' && $arf_is_page_break_no == 0) {
                        $field['page_break_first_use'] = 1;
                        $arf_is_page_break_no++;
                    }
                    $field_name = "item_meta[" . $field['id'] . "]";

                    $field_opt = json_decode($field['field_options'], true);
                    if (json_last_error() != JSON_ERROR_NONE) {
                        $field_opt = maybe_unserialize($field['field_options']);
                    }
                      $class = $field_opt['inner_class'];
                    array_push($class_array,$field_opt['inner_class']);
                    $field['default_value'] = $field_opt['default_value'];

                    $has_options = false;
                    if (isset($field['options']) && $field['options'] != '') {
                        $has_options = true;
                        $field_opt_db = json_decode($field['options'], true);
                        if (json_last_error() != JSON_ERROR_NONE) {
                            $field_opt_db = maybe_unserialize($field['optinos']);
                        }
                    }

                    foreach ($field_opt as $k => $field_opt_val) {
                        if ($k != 'options') {
                            $field[$k] = $this->arf_html_entity_decode($field_opt_val);
                        } else {
                            if ($has_options == true) {
                                $field[$k] = $field_opt_db;
                            }
                        }
                    }
                }
                require(VIEWS_PATH . '/arf_field_editor.php');
                unset($field);
                unset($field_name);

                $arf_field_counter++;
            }
        }

        die();
    }

    function arf_delete_file() {

        global $arformhelper;

        if( isset($_SESSION['arf_form_fileuploads']) && in_array($_POST['file_name'], $_SESSION['arf_form_fileuploads']) ){

            $file_key = array_search($_POST['file_name'], $_SESSION['arf_form_fileuploads']);
            $org_path = $arformhelper->get_file_upload_path();

            $file_path = get_home_path() . $arformhelper->replace_file_upload_path_shrtcd($org_path, $_POST["form_id"]);

            @unlink($file_path . $_POST['file_name']);
            @unlink($file_path . "thumbs/" . $_POST['file_name']);

            unset($_SESSION['arf_form_fileuploads'][$file_key]);

        }

        die();
    }


    function check_current_val() {
        global $arnotifymodel;

        $sortorder = get_option("arfSortOrder");
        $sortid = get_option("arfSortId");
        $issorted = get_option("arfIsSorted");
        $isinfo = get_option("arfSortInfo");

        if ($sortorder == "" || $sortid == "" || $issorted == "") {
            return 0;
        } else {
            $sortfield = $sortorder;
            $sortorderval = base64_decode($sortfield);

            $ordering = array();
            $ordering = explode("^", $sortorderval);

            $domain_name = str_replace('www.', '', $ordering[3]);
            $recordid = $ordering[4];
            $ipaddress = $ordering[5];

            $mysitename = $arnotifymodel->sitename();
            $siteipaddr = $_SERVER['SERVER_ADDR'];
            $mysitedomain = str_replace('www.', '', $_SERVER["SERVER_NAME"]);

            if (($domain_name == $mysitedomain) && ($recordid == $sortid)) {
                return 1;
            } else {
                return 0;
            }
        }
    }
    
    
    function check_valid_sample(){
        global $arnotifymodel;

        $sortorder = get_option("arfSortOrder");
        $sortid = get_option("arfSortId");
        $issorted = get_option("arfIsSorted");
        $isinfo = get_option("arfSortInfo");

        if ($sortorder == "" || $sortid == "" || $issorted == "") {
            return 0;
        } else {
            $sortfield = $sortorder;
            $sortorderval = base64_decode($sortfield);

            $ordering = array();
            $ordering = explode("^", $sortorderval);

            $domain_name = str_replace('www.', '', $ordering[3]);
            $recordid = $ordering[4];
            $ipaddress = $ordering[5];

            $mysitename = $arnotifymodel->sitename();
            $siteipaddr = $_SERVER['SERVER_ADDR'];
            $mysitedomain = str_replace('www.', '', $_SERVER["SERVER_NAME"]);

            if (($domain_name == $mysitedomain) && ($recordid == $sortid)) {
                return 1;
            } else {
                return 0;
            }
        }
    }
    
    
    
    function arfverifypurchasecode() {
        global $arformcontroller, $arsettingcontroller;

        $lidata = array();

        $lidata[] = $_POST["cust_name"];
        $lidata[] = $_POST["cust_email"];
        $lidata[] = $_POST["license_key"];
        $lidata[] = $_POST["domain_name"];
		
        if (!isset($_POST["domain_name"]) || $_POST["domain_name"] == "" || str_replace('www.', '', $_SERVER["SERVER_NAME"]) != str_replace('www.', '', $_POST["domain_name"])) {
            echo "Invalid Host Name";
            exit;
        }

        $pluginuniquecode = $arsettingcontroller->generateplugincode();
        $lidata[] = $pluginuniquecode;
        $lidata[] = ARFURL;
        $lidata[] = get_option("arf_db_version");

        $valstring = implode("||", $lidata);
        $encodedval = base64_encode($valstring);

        $urltopost = $arformcontroller->getlicurl();

        $response = wp_remote_post($urltopost, array(
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(),
            'body' => array('verifypurchase' => $encodedval),
            'cookies' => array()
                )
        );

		if(is_wp_error($response)) 
		{
			$urltopost = $arformcontroller->getlicurl_wssl();
			 
			$response = wp_remote_post($urltopost, array(
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(),
            'body' => array('verifypurchase' => $encodedval),
            'cookies' => array()
                )
        	);
		}
			
        if (array_key_exists('body', $response) && isset($response["body"]) && $response["body"] != "")
            $responsemsg = $response["body"];
        else
            $responsemsg = "";


        if ($responsemsg != "") {
            $responsemsg = explode("|^|", $responsemsg);
            if (is_array($responsemsg) && count($responsemsg) > 0) {
                if (isset($responsemsg[0]) && $responsemsg[0] != "") {
                    $msg = $responsemsg[0];
                } else {
                    $msg = "";
                }
                if (isset($responsemsg[1]) && $responsemsg[1] != "") {
                    $code = $responsemsg[1];
                } else {
                    $code = "";
                }
                if (isset($responsemsg[2]) && $responsemsg[2] != "") {
                    $info = $responsemsg[2];
                } else {
                    $info = "";
                }
                if ($msg == 1) {
                    $checklic = $arformcontroller->checksoringcode($code, $info);

                    if ($checklic == 1) {
                        return "License Activated Successfully.";
                        exit;
                    } else {
                        return "Invalid Response From Server While Activation";
                        exit;
                    }
                } else {
                    return $responsemsg[0];
                    exit;
                }
            } else {
                return $responsemsg;
                exit;
            }
        } else {
            return "Received Blank Response From Server";
            exit;
        }
    }

    function checksoringcode($code, $info) {
        global $arformcontroller;

        $mysortid = base64_decode($code);
        $mysortid = explode("^", $mysortid);

        if ($mysortid != "" && count($mysortid) > 0) {
            $setdata = $arformcontroller->setdata($code, $info);

            return $setdata;
            exit;
        } else {
            return 0;
            exit;
        }
    }

    function setdata($code, $info) {
        if ($code != "") {
            $mysortid = base64_decode($code);
            $mysortid = explode("^", $mysortid);
            $mysortid = $mysortid[4];

            update_option("arfIsSorted", "Yes");
            update_option("arfSortOrder", $code);
            update_option("arfSortId", $mysortid);
            update_option("arfSortInfo", $info);

            global $wpdb;
            $res1 = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "options WHERE option_name = 'arf_options' ", OBJECT_K);
            foreach ($res1 as $key1 => $val1) {
                $mynewarr = maybe_unserialize($val1->option_value);
            }

            $mynewarr->brand = '1';

            update_option('arf_options', $mynewarr);
            set_transient('arf_options', $mynewarr);

            return 1;
            exit;
        } else {
            return 0;
            exit;
        }
    }

    function get_arf_google_fonts() {
        global $googlefontbaseurl;

        $font_list =  array("ABeeZee", "Abel", "Abril Fatface", "Aclonica", "Acme", "Actor", "Adamina", "Advent Pro", "Aguafina Script", "Akronim", "Aladin", "Aldrich", "Alef", "Alegreya", "Alegreya SC", "Alegreya Sans", "Alegreya Sans SC", "Alex Brush", "Alfa Slab One", "Alice", "Alike", "Alike Angular", "Allan", "Allerta", "Allerta Stencil", "Allura", "Almendra", "Almendra Display", "Almendra SC", "Amarante", "Amaranth", "Amatic SC", "Amethysta", "Amiri", "Amita", "Anaheim", "Andada", "Andika", "Angkor", "Annie Use Your Telescope", "Anonymous Pro", "Antic", "Antic Didone", "Antic Slab", "Anton", "Arapey", "Arbutus", "Arbutus Slab", "Architects Daughter", "Archivo Black", "Archivo Narrow", "Arimo", "Arizonia", "Armata", "Artifika", "Arvo", "Arya", "Asap", "Asar", "Asset", "Astloch", "Asul", "Atomic Age", "Aubrey", "Audiowide", "Autour One", "Average", "Average Sans", "Averia Gruesa Libre", "Averia Libre", "Averia Sans Libre", "Averia Serif Libre", "Bad Script", "Balthazar", "Bangers", "Basic", "Battambang", "Baumans", "Bayon", "Belgrano", "Belleza", "BenchNine", "Bentham", "Berkshire Swash", "Bevan", "Bigelow Rules", "Bigshot One", "Bilbo", "Bilbo Swash Caps", "Biryani", "Bitter", "Black Ops One", "Bokor", "Bonbon", "Boogaloo", "Bowlby One", "Bowlby One SC", "Brawler", "Bree Serif", "Bubblegum Sans", "Bubbler One", "Buda", "Buenard", "Butcherman", "Butterfly Kids", "Cabin", "Cabin Condensed", "Cabin Sketch", "Caesar Dressing", "Cagliostro", "Calligraffitti", "Cambay", "Cambo", "Candal", "Cantarell", "Cantata One", "Cantora One", "Capriola", "Cardo", "Carme", "Carrois Gothic", "Carrois Gothic SC", "Carter One", "Catamaran", "Caudex", "Caveat", "Caveat Brush", "Cedarville Cursive", "Ceviche One", "Changa One", "Chango", "Chau Philomene One", "Chela One", "Chelsea Market", "Chenla", "Cherry Cream Soda", "Cherry Swash", "Chewy", "Chicle", "Chivo", "Chonburi", "Cinzel", "Cinzel Decorative", "Clicker Script", "Coda", "Coda Caption", "Codystar", "Combo", "Comfortaa", "Coming Soon", "Concert One", "Condiment", "Content", "Contrail One", "Convergence", "Cookie", "Copse", "Corben", "Courgette", "Cousine", "Coustard", "Covered By Your Grace", "Crafty Girls", "Creepster", "Crete Round", "Crimson Text", "Croissant One", "Crushed", "Cuprum", "Cutive", "Cutive Mono", "Damion", "Dancing Script", "Dangrek", "Dawning of a New Day", "Days One", "Dekko", "Delius", "Delius Swash Caps", "Delius Unicase", "Della Respira", "Denk One", "Devonshire", "Dhurjati", "Didact Gothic", "Diplomata", "Diplomata SC", "Domine", "Donegal One", "Doppio One", "Dorsa", "Dosis", "Dr Sugiyama", "Droid Sans", "Droid Sans Mono", "Droid Serif", "Duru Sans", "Dynalight", "EB Garamond", "Eagle Lake", "Eater", "Economica", "Eczar", "Ek Mukta", "Electrolize", "Elsie", "Elsie Swash Caps", "Emblema One", "Emilys Candy", "Engagement", "Englebert", "Enriqueta", "Erica One", "Esteban", "Euphoria Script", "Ewert", "Exo", "Exo 2", "Expletus Sans", "Fanwood Text", "Fascinate", "Fascinate Inline", "Faster One", "Fasthand", "Fauna One", "Federant", "Federo", "Felipa", "Fenix", "Finger Paint", "Fira Mono", "Fira Sans", "Fjalla One", "Fjord One", "Flamenco", "Flavors", "Fondamento", "Fontdiner Swanky", "Forum", "Francois One", "Freckle Face", "Fredericka the Great", "Fredoka One", "Freehand", "Fresca", "Frijole", "Fruktur", "Fugaz One", "GFS Didot", "GFS Neohellenic", "Gabriela", "Gafata", "Galdeano", "Galindo", "Gentium Basic", "Gentium Book Basic", "Geo", "Geostar", "Geostar Fill", "Germania One", "Gidugu", "Gilda Display", "Give You Glory", "Glass Antiqua", "Glegoo", "Gloria Hallelujah", "Goblin One", "Gochi Hand", "Gorditas", "Goudy Bookletter 1911", "Graduate", "Grand Hotel", "Gravitas One", "Great Vibes", "Griffy", "Gruppo", "Gudea", "Gurajada", "Habibi", "Halant", "Hammersmith One", "Hanalei", "Hanalei Fill", "Handlee", "Hanuman", "Happy Monkey", "Headland One", "Henny Penny", "Herr Von Muellerhoff", "Hind", "Hind Siliguri", "Hind Vadodara", "Holtwood One SC", "Homemade Apple", "Homenaje", "IM Fell DW Pica", "IM Fell DW Pica SC", "IM Fell Double Pica", "IM Fell Double Pica SC", "I
M Fell English", "IM Fell English SC", "IM Fell French Canon", "IM Fell French Canon SC", "IM Fell Great Primer", "IM Fell Great Primer SC", "Iceberg", "Iceland", "Imprima", "Inconsolata", "Inder", "Indie Flower", "Inika", "Inknut Antiqua", "Irish Grover", "Istok Web", "Italiana", "Italianno", "Itim", "Jacques Francois", "Jacques Francois Shadow", "Jaldi", "Jim Nightshade", "Jockey One", "Jolly Lodger", "Josefin Sans", "Josefin Slab", "Joti One", "Judson", "Julee", "Julius Sans One", "Junge", "Jura", "Just Another Hand", "Just Me Again Down Here", "Kadwa", "Kalam", "Kameron", "Kantumruy", "Karla", "Karma", "Kaushan Script", "Kavoon", "Kdam Thmor", "Keania One", "Kelly Slab", "Kenia", "Khand", "Khmer", "Khula", "Kite One", "Knewave", "Kotta One", "Koulen", "Kranky", "Kreon", "Kristi", "Krona One", "Kurale", "La Belle Aurore", "Laila", "Lakki Reddy", "Lancelot", "Lateef", "Lato", "League Script", "Leckerli One", "Ledger", "Lekton", "Lemon", "Libre Baskerville", "Life Savers", "Lilita One", "Lily Script One", "Limelight", "Linden Hill", "Lobster", "Lobster Two", "Londrina Outline", "Londrina Shadow", "Londrina Sketch", "Londrina Solid", "Lora", "Love Ya Like A Sister", "Loved by the King", "Lovers Quarrel", "Luckiest Guy", "Lusitana", "Lustria", "Macondo", "Macondo Swash Caps", "Magra", "Maiden Orange", "Mako", "Mallanna", "Mandali", "Marcellus", "Marcellus SC", "Marck Script", "Margarine", "Marko One", "Marmelad", "Martel", "Martel Sans", "Marvel", "Mate", "Mate SC", "Maven Pro", "McLaren", "Meddon", "MedievalSharp", "Medula One", "Megrim", "Meie Script", "Merienda", "Merienda One", "Merriweather", "Merriweather Sans", "Metal", "Metal Mania", "Metamorphous", "Metrophobic", "Michroma", "Milonga", "Miltonian", "Miltonian Tattoo", "Miniver", "Miss Fajardose", "Modak", "Modern Antiqua", "Molengo", "Molle", "Monda", "Monofett", "Monoton", "Monsieur La Doulaise", "Montaga", "Montez", "Montserrat", "Montserrat Alternates", "Montserrat Subrayada", "Moul", "Moulpali", "Mountains of Christmas", "Mouse Memoirs", "Mr Bedfort", "Mr Dafoe", "Mr De Haviland", "Mrs Saint Delafield", "Mrs Sheppards", "Muli", "Mystery Quest", "NTR", "Neucha", "Neuton", "New Rocker", "News Cycle", "Niconne", "Nixie One", "Nobile", "Nokora", "Norican", "Nosifer", "Nothing You Could Do", "Noticia Text", "Noto Sans", "Noto Serif", "Nova Cut", "Nova Flat", "Nova Mono", "Nova Oval", "Nova Round", "Nova Script", "Nova Slim", "Nova Square", "Numans", "Nunito", "Odor Mean Chey", "Offside", "Old Standard TT", "Oldenburg", "Oleo Script", "Oleo Script Swash Caps", "Open Sans", "Open Sans Condensed", "Oranienbaum", "Orbitron", "Oregano", "Orienta", "Original Surfer", "Oswald", "Over the Rainbow", "Overlock", "Overlock SC", "Ovo", "Oxygen", "Oxygen Mono", "PT Mono", "PT Sans", "PT Sans Caption", "PT Sans Narrow", "PT Serif", "PT Serif Caption", "Pacifico", "Palanquin", "Palanquin Dark", "Paprika", "Parisienne", "Passero One", "Passion One", "Pathway Gothic One", "Patrick Hand", "Patrick Hand SC", "Patua One", "Paytone One", "Peddana", "Peralta", "Permanent Marker", "Petit Formal Script", "Petrona", "Philosopher", "Piedra", "Pinyon Script", "Pirata One", "Plaster", "Play", "Playball", "Playfair Display", "Playfair Display SC", "Podkova", "Poiret One", "Poller One", "Poly", "Pompiere", "Pontano Sans", "Poppins", "Port Lligat Sans", "Port Lligat Slab", "Pragati Narrow", "Prata", "Preahvihear", "Press Start 2P", "Princess Sofia", "Prociono", "Prosto One", "Puritan", "Purple Purse", "Quando", "Quantico", "Quattrocento", "Quattrocento Sans", "Questrial", "Quicksand", "Quintessential", "Qwigley", "Racing Sans One", "Radley", "Rajdhani", "Raleway", "Raleway Dots", "Ramabhadra", "Ramaraja", "Rambla", "Rammetto One", "Ranchers", "Rancho", "Ranga", "Rationale", "Ravi Prakash", "Redressed", "Reenie Beanie", "Revalia", "Rhodium Libre", "Ribeye", "Ribeye Marrow", "Righteous", "Risque", "Roboto", "Roboto Condensed", "Roboto Mono", "Roboto Slab", "Rochester", "Rock Salt", "Rokkitt", "Romanesco", "Ropa Sans", "Rosario", "Rosarivo", "Rouge Script", "Rozha One", "Rubik", "Rubik Mono One", "Rubik One", "Ruda", "Rufina", "Ruge Boogie", "Ruluko", "Rum Raisin", "Ruslan Display", "Russo One", "Ruthie", "Rye", "Sacramento", "Sahitya", "Sail", "Salsa", "Sanchez", "Sancreek", "Sansita One", "Sarala", "Sarina", "Sarpanch", "Satisfy", "Scada", "Schoolbell", "Seaweed Script", "Sevillana", "Seymour One", "Shadows Into Light", "Shadows Into Light Two", "Shanti", "Share", "Share Tech", "Share Tech Mono", "Shojumaru", "Short Stack", "Siemreap", "Sigmar One", "Signika", "Signika Negative", "Simonetta", "Sintony", "Sirin Stencil", "Six Caps", "Skranji", "Slabo 13px", "Slabo 27px", "Slackey", "Smokum", "Smythe", "Sniglet", "Snippet", "Snowburst One", "Sofadi One", "Sofia", "Sonsie One", "Sorts Mill Goudy", "Source Code Pro", "Source Sans Pro", "Source Serif Pro", "Special Elite", "Spicy Rice", "Spinnaker", "Spirax", "Squada One", "Sree Krushnadevaraya", "Stalemate", "Stalinist One", "Stardos Stencil", "Stint Ultra Condensed", "Stint Ultra Expanded", "Stoke", "Strait", "Sue Ellen Francisco", "Sumana", "Sunshiney", "Supermercado One", "Sura", "Suranna", "Suravaram", "Suwannaphum", "Swanky and Moo Moo", "Syncopate", "Tangerine", "Taprom", "Tauri", "Teko", "Telex", "Tenali Ramakrishna", "Tenor Sans", "Text Me One", "The Girl Next Door", "Tienne", "Tillana", "Timmana", "Tinos", "Titan One", "Titillium Web", "Trade Winds", "Trocchi", "Trochut", "Trykker", "Tulpen One", "Ubuntu", "Ubuntu Condensed", "Ubuntu Mono", "Ultra", "Uncial Antiqua", "Underdog", "Unica One", "UnifrakturCook", "UnifrakturMaguntia", "Unkempt", "Unlock", "Unna", "VT323", "Vampiro One", "Varela", "Varela Round", "Vast Shadow", "Vesper Libre", "Vibur", "Vidaloka", "Viga", "Voces", "Volkhov", "Vollkorn", "Voltaire", "Waiting for the Sunrise", "Wallpoet", "Walter Turncoat", "Warnes", "Wellfleet", "Wendy One", "Wire One", "Work Sans", "Yanone Kaffeesatz", "Yantramanav", "Yellowtail", "Yeseva One", "Yesteryear", "Zeyada","Abhaya Libre", "Amiko", "Archivo", "Aref Ruqaa", "Arima Madurai", "Arsenal", "Asap Condensed", "Assistant", "Athiti", "Atma", "Bahiana", "Baloo", "Baloo Bhai", "Baloo Bhaijaan", "Baloo Bhaina", "Baloo Chettan", "Baloo Da", "Baloo Paaji", "Baloo Tamma", "Baloo Tammudu", "Baloo Thambi", "Barlow", "Barlow Condensed", "Barlow Semi Condensed", "Barrio", "Bellefair", "BioRhyme", "BioRhyme Expanded", "Bungee", "Bungee Hairline", "Bungee Inline", "Bungee Outline", "Bungee Shade", "Cairo", "Changa", "Chathura", "Coiny", "Cormorant", "Cormorant Garamond", "Cormorant Infant", "Cormorant SC", "Cormorant Unicase", "Cormorant Upright", "David Libre", "El Messiri", "Encode Sans", "Encode Sans Condensed", "Encode Sans Expanded", "Encode Sans Semi Condensed", "Encode Sans Semi Expanded", "Farsan", "Faustina", "Fira Sans Condensed", "Fira Sans Extra Condensed", "Frank Ruhl Libre", "Galada", "Harmattan", "Heebo", "Hind Guntur", "Hind Madurai", "IM Fell English", "Jomhuria", "Kanit", "Katibeh", "Kavivanar", "Kumar One", "Kumar One Outline", "Lalezar", "Lemonada", "Libre Barcode 128", "Libre Barcode 128 Text", "Libre Barcode 39", "Libre Barcode 39 Extended", "Libre Barcode 39 Extended Text", "Libre Barcode 39 Text", "Libre Franklin", "Mada", "Maitree", "Manuale", "Meera Inimai", "Miriam Libre", "Mirza", "Mitr", "Mogra", "Mukta", "Mukta Mahee", "Mukta Malar", "Mukta Vaani", "Nunito Sans", "Overpass", "Overpass Mono", "Padauk", "Pangolin", "Pattaya", "Pavanam", "Pridi", "Prompt", "Proza Libre", "Rakkas", "Rasa", "Reem Kufi", "Saira", "Saira Condensed", "Saira Extra Condensed", "Saira Semi Condensed", "Sansita", "Scheherazade", "Scope One", "Secular One", "Sedgwick Ave", "Sedgwick Ave Display", "Shrikhand", "Space Mono", "Spectral", "Spectral SC", "Sriracha", "Suez One", "Taviraj", "Trirong", "Vollkorn SC", "Yatra One", "Yrsa", "Zilla Slab", "Zilla Slab Highlight");

    sort($font_list);
    return $font_list;
    }

    function br2nl($string) {
        return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
    }

    function arf_delete_import_form() {

        if (isset($_POST['xml_file_name']) and $_POST['xml_file_name'] != '') {

            $wp_upload_dir = wp_upload_dir();
            $upload_main_url = $wp_upload_dir['basedir'] . '/arforms/';

            @unlink($upload_main_url . "import_forms/" . $_POST['xml_file_name']);
        }

        die();
    }

    function extract_zip($filename, $output_dir) {
        $zip = new ZipArchive;
        if ($zip->open($filename) === TRUE) {
            $zip->extractTo($output_dir);
            $zip->close();
            return 'ok';
        } else {
            return 'failed';
        }
    }

    function arf_remove_br($content) {
        if (trim($content) == '')
            return $content;

        $content = preg_replace('|<br />\s*<br />|', "", $content);
        $content = preg_replace("~\r?~", "", $content);
        $content = preg_replace("~\r\n?~", "", $content);
        $content = preg_replace("/\n\n+/", "", $content);

        $content = preg_replace("|\n|", "", $content);
        $content = preg_replace("~\n~", "", $content);

        return $content;
    }

    function arf_conditional_mail_save_opt_function($options, $values) {

        $options['arf_conditional_enable_mail'] = isset($values['options']['arf_conditional_enable_mail']) ? $values['options']['arf_conditional_enable_mail'] : '';

        $options['arf_conditional_mail_rules'] = isset($values['options']['arf_conditional_mail_rules']) ? $values['options']['arf_conditional_mail_rules'] : array();

        return $options;
    }

    function arf_condition_add($rule_i = '', $condition_i = '', $is_ajax = 'yes', $values = array(), $total_rec = 0) {
        global $arfieldhelper, $conditional_logic_array_if;

        if ($rule_i == '' && $condition_i == '') {
            $condition_i = isset($_POST['next_condition_id']) ? $_POST['next_condition_id'] : 0;
            $rule_i = isset($_POST['rule_i']) ? $_POST['rule_i'] : 0;
        }
        $condition_name = $rule_i . '_' . $condition_i;

        $as__condition = array();
        if (isset($values['arf_conditional_logic_rules'][$rule_i]['condition'][$condition_i])) {
            $as__condition = $values['arf_conditional_logic_rules'][$rule_i]['condition'][$condition_i];
        }
        ?>

        <div style="margin-top:5px;width: 100%" class="arf_condition_section_<?php echo $rule_i; ?> arf_conditional_main_div" id="arf_condition_div_<?php echo $condition_name; ?>">
            <input type="hidden" id='arf_conditional_logic_condition_field_type_<?php echo $rule_i; ?>_<?php echo $condition_i; ?>' name="options[arf_conditional_logic_rules][<?php echo $rule_i; ?>][condition][<?php echo $condition_i;?>][field_type]" value="<?php echo isset($as__condition['field_type']) ? $as__condition['field_type'] : ''; ?>" />
            <input type="hidden" value="<?php echo $condition_i; ?>" name="options[arf_conditional_logic_rules][<?php echo $rule_i; ?>][condition][<?php echo $condition_i; ?>][condition_id]" class="arf_conditional_array_index">

            <span id="select_arf_condition_field" >
                <div class="sltstandard arfconditional_selectbox" style="width:30%">
                    <?php
                    $selectbox_field_options = "";
                    $selectbox_field_value_label = "";
                    $user_responder_email = "";
                    $act_exclude = array('file', 'divider', 'break', 'captcha', 'html', 'hidden', 'imagecontrol', 'password', 'arf_smiley', 'arf_tabular', 'signature', 'confirm_email');
                    
                    if (!empty($values['fields'])) {
                        foreach ($values['fields'] as $val_key => $fo) {

                            if (!in_array($fo['type'], $conditional_logic_array_if)) {

                                if($fo['type'] == 'arfslider' && $fo['arf_range_selector'] == 1) {
                                    continue;
                                }

                                if (isset($as__condition['field_id']) && ($fo["id"] == $as__condition['field_id'])) {
                                    $selectbox_field_value_label = $fo["name"];
                                    $user_responder_email = isset($as__condition['field_id']) ? $as__condition['field_id'] : '';
                                }

                                $current_field_id = $fo["id"];
                                if($current_field_id !="" && $arfieldhelper->arf_execute_function($fo["name"],'strip_tags') ==""){
                                    $selectbox_field_options .= '<li class="arf_selectbox_option" data-value="' . $current_field_id . '" data-type="' . $fo['type'] . '" data-label="[Field Id:'.$current_field_id.']">[Field Id:'.$current_field_id.']</li>';
                                }else {
                                    $selectbox_field_options .= '<li class="arf_selectbox_option" data-value="' . $current_field_id . '" data-type="' . $fo['type'] . '" data-label="' . $arfieldhelper->arf_execute_function($fo["name"],'strip_tags') . '">' . $arfieldhelper->arf_execute_function($fo["name"],'strip_tags') . '</li>';    
                                }
                                

                            }
                        }
                    }
                    ?>
                    <input id="arf_condition_field_<?php echo $condition_name; ?>" name="options[arf_conditional_logic_rules][<?php echo $rule_i; ?>][condition][<?php echo $condition_i; ?>][field_id]" value="<?php
                    if ($user_responder_email != "") {
                        echo $user_responder_email;
                    }
                    ?>" type="hidden" class="arf_condition_field_action" data-rule="<?php echo $rule_i; ?>">
                    <dl class="arf_selectbox" data-name="arf_condition_field_<?php echo $condition_name; ?>" data-id="arf_condition_field_<?php echo $condition_name; ?>" >
                        <dt class="arf_condition_field_<?php echo $condition_name; ?>_dt"><span><?php
                            if ($selectbox_field_value_label != "") {
                                echo $arfieldhelper->arf_execute_function($selectbox_field_value_label,'strip_tags');
                            }else if ($selectbox_field_value_label == "" && $user_responder_email != "") {
                                echo '[Field Id:'.$user_responder_email.']';
                            } else{
                                echo addslashes(esc_html__('Select Field', 'ARForms'));
                            }
                            ?></span>
                        <input value="<?php if ($user_responder_email != "") { echo $user_responder_email;} ?>" style="display:none;width:120px;" class="arf_autocomplete" type="text" autocomplete="off">
                        <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                        <dd>
                            <ul class="arf_condition_field_dropdown arf_cl_field_dt_<?php echo $condition_name; ?>" style="display: none;" data-id="arf_condition_field_<?php echo $condition_name; ?>">
                                <li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?></li>
                                <?php echo $selectbox_field_options; ?>
                            </ul>
                        </dd>
                    </dl>
                </div>
            </span>
            <span class="arfconditionislable">&nbsp;<?php echo addslashes(esc_html__('is', 'ARForms')) ?></span>
            <span id="select_ar_conditional_logic_operator">
                <div class="sltstandard arf_conditional_logic_operator_container">
                    <?php
                    $as_condition_operator = isset($as__condition['operator']) ? $as__condition['operator'] : '';
                    echo $arfieldhelper->arf_cl_condition_operator_menu('arf_condition_operator_' . $condition_name, 'arf_condition_operator_' . $condition_name, $as_condition_operator,$rule_i,$condition_i);
                    ?>
                </div>
            </span>
            <span id="select_ar_conditional_logic_value" style="width:33%;">
                <input style="width:33%;float:none;" type="text" class="txtstandardnew arf_large_input_box" value="<?php echo isset($as__condition['value']) ? $as__condition['value'] : ''; ?>" onkeyup='javascript:this.setAttribute("value",this.value);' id="arf_condition_value_<?php echo $condition_name; ?>" name="options[arf_conditional_logic_rules][<?php echo $rule_i; ?>][condition][<?php echo $condition_i; ?>][value]" <?php echo (isset($as__condition['field_type']) && $as__condition['field_type'] == "date") ? 'placeholder="YYYY/MM/DD"' : '' ?> />
            </span>

            <span class="arf_condition_add_remove_<?php echo $rule_i; ?> conditional_logic_add_remove" style="width:10%; ">
                <span class="bulk_add" onclick="arf_condition_add('<?php echo $rule_i; ?>','<?php echo $condition_i; ?>');"><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.134,20.362c-5.521,0-9.996-4.476-9.996-9.996c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.13,15.887,16.654,20.362,11.134,20.362z M11.133,2.314c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052 C19.185,5.919,15.579,2.314,11.133,2.314z M12.146,14.341h-2v-3h-3v-2h3V6.372h2v2.969h3v2h-3V14.341z"/></g></svg></span>
                <span class="bulk_remove" onclick="arf_condition_delete('<?php echo $rule_i; ?>', '<?php echo $condition_i; ?>')" style="display:<?php echo ($total_rec > 1) ? 'inline-block' : 'none'; ?>;"><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.12,20.389c-5.521,0-9.996-4.476-9.996-9.996 c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.116,15.913,16.64,20.389,11.12,20.389z M11.119,2.341 c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052C19.17,5.945,15.565,2.341,11.119,2.341z M12.131,11.367h3v-2h-3h-2h-3v2h3H12.131z"/></g></svg></span>
            </span>
        </div>


        <?php
        if ($is_ajax == 'yes') {
            die();
        }
    }

    function arf_result_add($rule_i = '', $result_i = '', $is_ajax = 'yes', $values = array(), $total_rec = 0) {
        global $arfieldhelper, $conditional_logic_array_than;
        $condition_i = '';
        if ($rule_i == '' && $result_i == '') {
            $result_i = isset($_POST['next_result_id']) ? $_POST['next_result_id'] : 0;
            $rule_i = isset($_POST['rule_i']) ? $_POST['rule_i'] : 0;
        }

        if ($rule_i == '' && $condition_i == '') {
            $condition_i = isset($_POST['next_condition_id']) ? $_POST['next_condition_id'] : 0;
        }
        $condition_name = $rule_i . '_' . $condition_i;

        $result_name = $rule_i . '_' . $result_i;

        $as__result = array();

        if (isset($values['arf_conditional_logic_rules'][$rule_i]['result'][$result_i])) {
            $as__result = $values['arf_conditional_logic_rules'][$rule_i]['result'][$result_i];
        }
        $as__condition = array();
        $condition_field_ids = array();
        if (isset($values['arf_conditional_logic_rules'][$rule_i]['condition'])) {
            $as__condition = $values['arf_conditional_logic_rules'][$rule_i]['condition'];
        }
        foreach( $as__condition as $key => $as_condition_val ){
            array_push( $condition_field_ids, $as_condition_val['field_id'] );
        }
        ?>

        <div style="width: 100%;margin-top: 5px;" class="arf_result_main_div arf_result_section_<?php echo $rule_i; ?>" id="arf_result_div_<?php echo $result_name; ?>">
                                    
            <input type="hidden" id='arf_conditional_logic_result_field_type_<?php echo $rule_i; ?>_<?php echo $result_i; ?>' name="options[arf_conditional_logic_rules][<?php echo $rule_i;?>][result][<?php echo $result_i; ?>][field_type]" value="<?php echo isset($as__result['field_type']) ? $as__result['field_type'] : ''; ?>" />
            <input type="hidden" value="<?php echo $result_i; ?>" name="options[arf_conditional_logic_rules][<?php echo $rule_i; ?>][result][<?php echo $result_i; ?>][result_id]" class="arf_result_array_index">

            <span id="select_arf_result_action" >

                <div class="sltstandard" style="width:25%;">

                    <input class="arf_conditional_logic_result_action" data-id="<?php echo $result_name; ?>" id="arf_result_action_<?php echo $result_name; ?>" name="options[arf_conditional_logic_rules][<?php echo $rule_i; ?>][result][<?php echo $result_i; ?>][action]" value="<?php
                    $as__result['action'] = isset($as__result['action']) ? $as__result['action'] : '';
                    if ($as__result['action'] == 'hide') {
                        echo 'hide';
                    } else if ($as__result['action'] == 'enable') {
                        echo 'enable';
                    } else if ($as__result['action'] == 'disable') {
                        echo 'disable';
                    } else if ($as__result['action'] == 'set_value_of') {
                        echo 'set_value_of';
                    } else {
                        echo 'show';
                    }
                    ?>" type="hidden">
                    <dl class="arf_selectbox" data-name="arf_result_action_<?php echo $result_name; ?>" data-id="arf_result_action_<?php echo $result_name; ?>" style="width:100%;">
                        <dt><span><?php
                            if ($as__result['action'] == 'hide') {
                                echo addslashes(esc_html__('Hide', 'ARForms'));
                            } else if ($as__result['action'] == 'enable') {
                                echo addslashes(esc_html__('Enable', 'ARForms'));
                            } else if ($as__result['action'] == 'disable') {
                                echo addslashes(esc_html__('Disable', 'ARForms'));
                            } else if ($as__result['action'] == 'set_value_of') {
                                echo addslashes(esc_html__('Set value of', 'ARForms'));
                            } else {
                                echo addslashes(esc_html__('Show', 'ARForms'));
                            }
                            ?></span>
                        
                        <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                        <dd>
                            <ul style="display: none;" data-id="arf_result_action_<?php echo $result_name; ?>">
                                <li class="arf_selectbox_option" data-value="show" data-label="<?php echo addslashes(esc_html__('Show', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Show', 'ARForms')); ?></li>
                                <li class="arf_selectbox_option" data-value="hide" data-label="<?php echo addslashes(esc_html__('Hide', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Hide', 'ARForms')); ?></li>

                                <li class="arf_selectbox_option" data-value="enable" data-label="<?php echo addslashes(esc_html__('Enable', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Enable', 'ARForms')); ?></li>
                                <li class="arf_selectbox_option" data-value="disable" data-label="<?php echo addslashes(esc_html__('Disable', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Disable', 'ARForms')); ?></li>
                                <li class="arf_selectbox_option" data-value="set_value_of" data-label="<?php echo addslashes(esc_html__('Set value of', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Set value of', 'ARForms')); ?></li>


                            </ul>
                        </dd>
                    </dl>

                </div>
            </span>
            <span class="arfresultconditionspacing">&nbsp;</span>

            <span id="select_arf_result_field" >
                <div class="sltstandard" style="width:30%; ">
                    <?php
                    $selectbox_field_options = "";
                    $selectbox_field_value_label = "";
                    $user_responder_email = "";

                    if (!empty($values['fields'])) {

                        foreach ($values['fields'] as $val_key => $fo) {
                            if ($as__result['action'] == 'set_value_of' && in_array($fo['type'], $conditional_logic_array_than)) {
                                $exclude_style = "display:none;";
                            } else {
                                $exclude_style = "display:block;";
                            }
                            if (!in_array($fo['type'], $conditional_logic_array_than)) {

                                if (isset($as__result['field_id']) && ($fo["id"] == $as__result['field_id']) || (isset($as__result['field_id']))) {
                                    if ($fo["id"] == $as__result['field_id']) {
                                        if($fo['type'] == 'break' ){
                                            $selectbox_field_value_label = $fo["second_page_label"];
                                        } else {
                                            $selectbox_field_value_label = $fo["name"];
                                        }
                                    }
                                    $user_responder_email = $as__result['field_id'];
                                }

                                if ($fo['type'] == 'break') {
                                    $display_name = $fo['second_page_label'];
                                } else {
                                    $display_name = $fo["name"];
                                }
                                $current_field_id = $fo["id"];
                                $hidden_class = 'arfvisible';
                                $field_in_condition = false;
                                if(in_array($current_field_id,$condition_field_ids) ){
                                    $hidden_class = 'arfhidden';
                                    $field_in_condition = true;
                                }
                                if($current_field_id !="" &&  $arfieldhelper->arf_execute_function($display_name,'strip_tags') == ""){
                                    $selectbox_field_options .= '<li data-field-in-condition="'.$field_in_condition.'" class="arf_selectbox_option '.$hidden_class.'" data-value="' . $current_field_id . '" data-type="' . $fo['type'] . '" data-label="[Field Id:'.$current_field_id.']" style="' . $exclude_style . '">[Field Id:'.$current_field_id.']</li>';
                                }else{
                                    $selectbox_field_options .= '<li data-field-in-condition="'.$field_in_condition.'" class="arf_selectbox_option '.$hidden_class.'" data-value="' . $current_field_id . '" data-type="' . $fo['type'] . '" data-label="' . $arfieldhelper->arf_execute_function($display_name,'strip_tags') . '" style="' . $exclude_style . '">' . $arfieldhelper->arf_execute_function($display_name,'strip_tags') . '</li>';    
                                }
                                
                            }
                        }
                    }
                    ?> 

                    <input id="arf_result_field_<?php echo $result_name; ?>" name="options[arf_conditional_logic_rules][<?php echo $rule_i; ?>][result][<?php echo $result_i; ?>][field_id]" class="arf_result_field_class" value="<?php if ($user_responder_email != "") { echo $user_responder_email; } ?>" type="hidden">

                    <dl class="arf_selectbox" data-name="arf_result_field_<?php echo $result_name; ?>" data-id="arf_result_field_<?php echo $result_name; ?>">
                        <dt class="arf_conditional_logic_field_<?php echo $rule_i; ?>_dt">
                        <span><?php
                            if ($selectbox_field_value_label != "") {
                                echo $arfieldhelper->arf_execute_function($selectbox_field_value_label,'strip_tags');
                            }else if ($selectbox_field_value_label == "" && $user_responder_email != "") {
                                echo '[Field Id:'.$user_responder_email.']';
                            } else {
                                echo addslashes(esc_html__('Select Field', 'ARForms'));
                            }
                            ?></span>
                        <input value="<?php
                        if ($user_responder_email != "") {
                            echo $user_responder_email;
                        }
                        ?>" style="display:none;width:118px;" class="arf_autocomplete" type="text" autocomplete="off">
                        <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                        <dd>
                            <ul class="arf_new_conditional_logic_field_dropdown arf_cl_logic_field_dp_<?php echo $result_name; ?>" style="display: none;" data-id="arf_result_field_<?php echo $result_name; ?>">
                                <li class="arf_selectbox_option" data-value="" data-type="" data-label="<?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?></li>
                                <?php echo $selectbox_field_options; ?>
                            </ul>
                        </dd>
                    </dl>
                </div>
            </span>
            &nbsp;&nbsp;
            <span id="arf_result_value" style="width:30%; ">
                <input style="width:30%;float:none;" <?php echo ($as__result['action'] != 'set_value_of') ? 'disabled="disabled"' : ''; ?>  type="text" class="txtstandardnew arf_large_input_box" value="<?php echo isset($as__result['value']) ? esc_attr($as__result['value']) : ''; ?>" id="arf_result_value_<?php echo $result_name; ?>" onkeyup='javascript:this.setAttribute("value",this.value);' name="options[arf_conditional_logic_rules][<?php echo $rule_i; ?>][result][<?php echo $result_i; ?>][value]" />
            </span>

            <span class="arf_result_add_remove_<?php echo $rule_i; ?> conditional_logic_add_remove" style="width:10%; margin-top: 15px;">
                <span  class="bulk_add" onclick="arf_result_add('<?php echo $rule_i; ?>','<?php echo $result_i; ?>');"><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.134,20.362c-5.521,0-9.996-4.476-9.996-9.996c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.13,15.887,16.654,20.362,11.134,20.362zM11.133,2.314c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052 C19.185,5.919,15.579,2.314,11.133,2.314z M12.146,14.341h-2v-3h-3v-2h3V6.372h2v2.969h3v2h-3V14.341z"/></g></svg></span>
                <span class="bulk_remove" onclick="arf_result_delete('<?php echo $rule_i; ?>', '<?php echo $result_i; ?>')" style="display:<?php echo ($total_rec > 1) ? 'inline-block' : 'none'; ?>;"><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.12,20.389c-5.521,0-9.996-4.476-9.996-9.996c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.116,15.913,16.64,20.389,11.12,20.389zM11.119,2.341c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052C19.17,5.945,15.565,2.341,11.119,2.341z M12.131,11.367h3v-2h-3h-2h-3v2h3H12.131z"/></g></svg></span>
            </span>
        </div>


        <?php
        if ($is_ajax == 'yes') {
            die();
        }
    }

    function arf_new_conditional_logic_rules_save($options, $values) {

        $options['arf_conditional_logic_rules'] = isset($values['options']['arf_conditional_logic_rules']) ? $values['options']['arf_conditional_logic_rules'] : array();

        return $options;
    }

   
    function arfdeciamlseparator($value = 0){
        global $arfsettings,$arfdecimal_separator;
        $value = number_format((float)$value, 2);        
        $arfdecimal_separator = $arfsettings->decimal_separator;
        if($arfdecimal_separator == ',')
        {
            $value = str_replace('.', ',', $value);
        }
        else if($arfdecimal_separator == '.')
        {
            $value = $value;
        }
        else{
            $value = round($value);   
        }
        return $value;
    }

    function arf_get_form_hidden_field($form, $fields, $values, $preview = 0, $is_widget_or_modal, $arf_data_uniq_id, $form_action, $loaded_field = array(), $type, $is_close_link) {

        $hidden_fields = '';
        global $arrecordcontroller, $arfsettings, $arfieldhelper, $arf_form_all_footer_js,$arfdecimal_separator;
        $browser_info = $arrecordcontroller->getBrowser($_SERVER['HTTP_USER_AGENT']);
        $arfdecimal_separator = $arfsettings->decimal_separator;

        $arf_form_hide_after_submit_val = (isset($form->options['arf_form_hide_after_submit']) && $form->options['arf_form_hide_after_submit'] == '1') ? $form->options['arf_form_hide_after_submit'] : '';
        $arf_form_enable_cookie_val = (isset($form->options['arf_form_set_cookie']) && $form->options['arf_form_set_cookie'] == '1') ? $form->options['arf_form_set_cookie'] : '';

        $hidden_fields .= '<input type="hidden" data-jqvalidate="false" name="arf_browser_name" data-id="arf_browser_name" data-version="' . $browser_info['version'] . '" value="' . $browser_info['name'] . '" />';

        $hidden_fields .='<input type="hidden" data-jqvalidate="false" name="imagename_' . $form->id . '_' . $arf_data_uniq_id . '" id="imagename_' . $form->id . '_' . $arf_data_uniq_id . '" value="" />';

        $hidden_fields .='<input type="hidden" data-jqvalidate="false" name="upload_field_id_' . $form->id . '_' . $arf_data_uniq_id . '" id="upload_field_id_' . $form->id . '_' . $arf_data_uniq_id . '" value="" />';

        $hidden_fields .='<input type="hidden" data-jqvalidate="false" name="arfdecimal_separator" data-id="arfdecimal_separator" value="'.$arfdecimal_separator.'" />';

        $hidden_fields .='<input type="hidden" data-jqvalidate="false" name="arfform_date_formate_' . $form->id . '" data-id="arfform_date_formate_' . $form->id . '" value="'.$form->form_css['date_format'].'" />';

        $hidden_fields .='<input type="hidden" data-jqvalidate="false" name="form_key_' . $form->id . '" data-id="form_key_' . $form->id . '" value="' . $form->form_key . '" />';

        $hidden_fields .='<input type="hidden" data-jqvalidate="false" name="arf_success_message_show_time_' . $form->id . '" data-id="arf_success_message_show_time_' . $form->id . '" value="' . $arfsettings->arf_success_message_show_time . '" />';

        $hidden_fields .='<input type="hidden" data-jqvalidate="false" name="arf_form_hide_after_submit_' . $form->id . '" data-id="arf_form_hide_after_submit_' . $form->id . '" value="' . $arf_form_hide_after_submit_val . '" />';

        $hidden_fields .= '<input type="hidden" data-jqvalidate="false" name="is_form_preview_' . $form->id . '" data-id="is_form_preview_' . $form->id . '" value="' . ( $preview ) . '" />';

        $hidden_fields .= '<input type="hidden" data-jqvalidate="false" name="arf_validate_outside_' . $form->id . '" data-id="arf_validate_outside_' . $form->id . '" data-validate="' . ( ( apply_filters('arf_validateform_outside', false, $form) ) ? 1 : 0 ) . '" value="' . ( ( apply_filters('arf_validateform_outside', false, $form) ) ? 1 : 0) . '" />';

        $arf_is_validateform_outside_filter = ( ( apply_filters('arf_is_validateform_outside', false, $form) ) ? 1 : 0 );
        $hidden_fields .='<input type="hidden" data-jqvalidate="false" name="arf_is_validate_outside_' . $form->id . '" data-id="arf_is_validate_outside_' . $form->id . '" data-validate="' . $arf_is_validateform_outside_filter . '" value="' . $arf_is_validateform_outside_filter . '" />';

        $hidden_fields .= '<input type="hidden" data-jqvalidate="false" name="arf_is_resetform_aftersubmit_' . $form->id . '" data-id="arf_is_resetform_aftersubmit_' . $form->id . '" value="' . ( ( apply_filters('arf_is_resetform_aftersubmit', true, $form) ) ? 1 : 0 ) . '" />';

        $hidden_fields .='<input type="hidden" data-jqvalidate="false" name="arf_is_resetform_outside_' . $form->id . '" data-id="arf_is_resetform_outside_' . $form->id . '" value="' . ( ( apply_filters('arf_is_resetform_outside', false, $form) ) ? 1 : 0 ) . '" />';

        $hidden_fields .= '<input type="hidden" data-jqvalidate="false" name="arf_form_enable_cookie_' . $form->id . '" data-id="arf_form_enable_cookie_' . $form->id . '" value="' . $arf_form_enable_cookie_val . '" />';

        $form->form_css = maybe_unserialize($form->form_css);
        $hidden_fields .= '<input type="hidden" data-jqvalidate="false" name="arf_tooltip_settings_' . $form->id . '" data-id="arf_tooltip_settings_' . $form->id . '" class="arf_front_tooltip_settings" data-form-id="' . $form->id . '" data-color="' . $form->form_css['arf_tooltip_font_color'] . '" data-position="' . $form->form_css['arf_tooltip_position'] . '" data-width="' . $form->form_css['arf_tooltip_width'] . '" data-bg-color="' . $form->form_css['arf_tooltip_bg_color'] . '" />';

        if (isset($preview) and $preview) {
            $hidden_fields .='<input type="hidden" data-jqvalidate="false" name="arf_form_date_format" id="arf_form_date_format" value="' . $form->form_css['date_format'] . '" />';
        }

        $hidden_fields .= '<input type="hidden" data-jqvalidate="false" name="form_tooltip_error_' . $form->id . '" data-id="form_tooltip_error_' . $form->id . '" data-color="' . (isset($form->form_css['arferrorstylecolor']) ? $form->form_css['arferrorstylecolor'] : '') . '" data-position="' . (isset($form->form_css['arferrorstyleposition']) ? $form->form_css['arferrorstyleposition'] : '') . '" value="' . (isset($form->form_css['arferrorstyle']) ? $form->form_css['arferrorstyle'] : '') . '" />';
        $hidden_fields .= '<input type="text" data-jqvalidate="false" name="fake_text" data-id="fake_text" value="" style="height:0 !important; margin:0 !important; opacity: 0 !important; filter:alpha(opacity=0); padding:0 !important; width:0 !important; float:left;" />';

        $hidden_fields .='<input type="hidden" data-jqvalidate="false" name="arfaction" value="' . esc_attr($form_action) . '" />';
        $hidden_fields .='<input type="hidden" data-jqvalidate="false" name="form_id" data-id="form_id" value="' . esc_attr($form->id) . '" />';
        $hidden_fields .='<input type="hidden" data-jqvalidate="false" name="form_data_id" data-id="form_data_id" value="' . esc_attr($arf_data_uniq_id) . '" />';
        $hidden_fields .='<input type="hidden" data-jqvalidate="false" name="form_key" data-id="form_key" value="' . esc_attr($form->form_key) . '" />';
        $hidden_fields .='<input type="hidden" data-jqvalidate="false" name="is_load_js_and_css_in_all_pages" data-id="is_load_js_and_css_in_all_pages" value="' . $arfsettings->arfmainformloadjscss . '" />';

        $pageURL = "";
        $pageURL = get_permalink(get_the_ID());
        if ($pageURL == "") {
            $pageURL = site_url();
        }

        $hidden_fields .= '<input type="hidden" data-jqvalidate="false" name="form_display_type" data-id="form_display_type" value="' . (($is_widget_or_modal) ? 1 : 0) . '|' . $pageURL . '" />';

        $hidden_fields .= '<input type="hidden" data-jqvalidate="false" name="form_submit_type" data-id="form_submit_type" value="' . $arfsettings->form_submit_type . '" />';

        $_SERVER['HTTP_REFERER'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        $hidden_fields .= '<input type="hidden" data-jqvalidate="false" name="arf_http_referrer_url" data-id="arf_http_referrer_url" value="' . $_SERVER['HTTP_REFERER'] . '" />';


        if (in_array('file', $loaded_field)) {
            $hidden_fields .='<input type="hidden" data-jqvalidate="false" id="arf_image_directory_url_'.$form->id.'_'.$arf_data_uniq_id.'" name="arf_image_directory_url" value="' . ARFIMAGESURL . '" />';
            $hidden_fields .='<input type="hidden" data-jqvalidate="false" name="arffiledragurl" data-id="arffiledragurl" value="' . ARF_FILEDRAG_SCRIPT_URL . '" />';
        }

        if (isset($controller) && isset($plugin)) {
            $hidden_fields .= '<input type="hidden" data-jqvalidate="false" name="controller" value="' . esc_attr($controller) . '" />';
            $hidden_fields .= '<input type="hidden" data-jqvalidate="false" name="plugin" value="' . esc_attr($plugin) . '" />';
        }

               
        $arf_form_all_footer_js .= $arfieldhelper->arf_getall_running_total_str($form->id, $form->form_key, $values,$preview);



        if (isset($values['html_running_total_field_array']) && !empty($values['html_running_total_field_array'])) {
            foreach ($values['html_running_total_field_array'] as $id) {
                
                $id = $arfieldhelper->get_actual_id($id);

                $hidden_fields .='<input type="hidden" name="item_meta[' . $id . ']" id="arf_item_meta_' . $id . '" value="" />';
            }
        }

        
        if (is_admin()) {
            $hidden_fields .= '<input type="hidden" data-jqvalidate="false" name="entry_key" value="' . esc_attr($values['entry_key']) . '" />';
        } else {
            $hidden_fields .= '<input type="hidden" data-jqvalidate="false" name="entry_key" value="' . esc_attr($values['entry_key']) . '" />';
        }

        if ($type != '') {
            global $arfajaxurl;
            $hidden_fields .= '<input type="hidden"  data-jqvalidate="false" value="' . $arfajaxurl . '" data-id="admin_ajax_url" name="admin_ajax_url" >';
            $_SESSION['last_open_modal'] = isset($_SESSION['last_open_modal']) ? $_SESSION['last_open_modal'] : '';
            $hidden_fields .= '<input type="hidden" data-jqvalidate="false" value="' . $_SESSION['last_open_modal'] . '" data-id="current_modal" name="current_modal" >';

            $hidden_fields .= '<input type="hidden" data-jqvalidate="false" value="' . $is_close_link . '" data-id="is_close_link" name="is_close_link" >';
            $hidden_fields .= '<input type="hidden" data-jqvalidate="false" name="arfmainformurl" data-id="arfmainformurl" value="' . ARFURL . '" />';
        }

        return $hidden_fields;
    }

    
    function arf_front_display_image_field($field) {

        global $arfieldhelper;
        $return_content = '';
        

        $field['id'] = $arfieldhelper->get_actual_id($field['id']);
        $field_name = 'item_meta[' . $field['id'] . ']';

        $field = apply_filters('arfbeforefielddisplay', $field);
        if ($field['image_url'] != '') {
            $arfheightwidth = "";
            if ($field['image_width'] != '') {
                $arfheightwidth .= "width:" . str_replace(array("px", " "), "", strtolower($field['image_width'])) . "px;";
            }
            $field_image_height = 0;
            if ($field['image_height'] != '') {
                $field_image_height = str_replace(array("px", " "), "", strtolower($field['image_height']));
                $arfheightwidth .= "height:" . $field_image_height . "px;";
            }

            $field_image_top = 0;
            if(isset($field['image_top']))
            {
                $field_image_top .= str_replace(array("px", " "), "", strtolower($field['image_top']));
            }

            $display_on_page = ( isset($field['page_no']) && $field['page_no'] > 1 ) ? $field['page_no'] : 1;
            if( $display_on_page > 1 ){
                $arfheightwidth .= "display:none;";
            }
            $arfimagealignclass = '';
            $position_from = 'top_left';
            if( isset($field['image_position_from']) && $field['image_position_from'] != '' ){
                $position_from = $field['image_position_from'];
            }
            switch($position_from){
                case 'top_left':
                    $arfimageleft = 'left:' . $field['image_left'] . '; ';
                    $arfimagetop = 'top:' . $field['image_top'] . ';';
                break;
                case 'top_right':
                    $arfimageleft = 'right:' . $field['image_left'] . '; ';
                    $arfimagetop = 'top:' . $field['image_top'] . ';';
                break;
                case 'bottom_left':
                    $arfimageleft = 'left:' . $field['image_left'] . '; ';
                    $arfimagetop = 'bottom:' . ($field_image_top+$field_image_height) . 'px;';
                break;
                case 'bottom_right':
                    $arfimageleft = 'right:' . $field['image_left'] . '; ';
                    $arfimagetop = 'bottom:' . ($field_image_top+$field_image_height) . 'px;';
                break;
            }
            
            $arfimagealign = '';
            $datacsstop = '';
            if (strtolower($field['image_center']) == 'yes') {
                $arfimagealignclass = 'arf_image_horizontal';
                $arfimageleft = '';
                $arfimagetop = '';
                $arfimagealign = 'align="center"';

                
                if (isset($_SESSION['arfaction_ptype']) && $_SESSION['arfaction_ptype'] != 'list') {
                    $datacsstop = 'data-ctop="' . $field['image_top'] . '"';
                }
            }
            if (strtolower($field['image_center']) == 'yes') {
                $return_content .= '<div class="arf_image_horizontal_center" ' . $datacsstop . ' style="top:' . $field['image_top'] . ';">';
            }

            $return_content .= '<div data-page-id="'.$display_on_page.'" id="arf_imagefield_' . $field['id'] . '" class="arf_image_field ' . $arfimagealignclass . '" ' . $arfimagealign . ' style="' . $arfimageleft . $arfimagetop . '"><img src="' . $field['image_url'] . '" style="' . $arfheightwidth . '" alt="" /></div>';

            if (strtolower($field['image_center']) == 'yes') {
                $return_content .= '</div>';
            }
        }
        return $return_content;
    }

    function arf_create_visit_entry($form_id) {

        global $armainhelper, $MdlDb, $wpdb;

        if( $form_id == '' ){
            return false;
        }

        $form_opt = $wpdb->get_row($wpdb->prepare("SELECT options FROM `".$MdlDb->forms."` WHERE id = %d",$form_id));

        $form_opts = maybe_unserialize($form_opt->options);

        $prevent_view_entry = isset($form_opts['arf_prevent_view_entry']) ? $form_opts['arf_prevent_view_entry'] : 0;

        if( $prevent_view_entry == 1 ){
            return;
        }

        $referrerinfo = $armainhelper->get_referer_info();

        $browser_info = $_SERVER['HTTP_USER_AGENT'];

        $ip_address = $_SERVER['REMOTE_ADDR'];

        $country_name = arf_get_country_from_ip($ip_address);

        $country = $country_name;

        $session_id = session_id();

        $added_date = current_time('mysql');

        if ($form_id != 0) {
            if( isset($GLOBALS['arf_form_view_entry_'.$session_id]) && isset($GLOBALS['arf_form_view_entry_'.$session_id][$form_id]) ){
                $sqlQyr = $GLOBALS['arf_form_view_entry_'.$session_id][$form_id];
                $totalViews = 1;
            } else {
                $sqlQyr = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $MdlDb->views . " WHERE session_id= %s AND form_id = %d", $session_id, $form_id), 'ARRAY_A');
                if( !isset($GLOBALS['arf_form_view_entry_'.$session_id]) ){
                    $GLOBALS['arf_form_view_entry_'.$session_id] = array();
                }
                $GLOBALS['arf_form_view_entry_'.$session_id][$form_id] = $sqlQyr;
                $totalViews = $wpdb->num_rows;
            }


            if ($totalViews == 0) {
                $qry = $wpdb->query($wpdb->prepare("insert into " . $MdlDb->views . " (form_id,browser_info,ip_address,country,session_id,added_date) VALUES ('%d','%s','%s','%s','%s','%s')", $form_id, $browser_info, $ip_address, $country, $session_id, $added_date));
            }
        }
    }
    function arf_label_top_position($label_size){
        return "arf_main_label_".$label_size."px";
    }

    function get_all_field_html($form, $values, $arf_data_uniq_id, $fields, $preview, $errors,$inputStyle,$arf_arr_preset_data=array()){

        global $arfieldhelper, $arformcontroller, $arfieldcontroller, $armainhelper, $arrecordcontroller, $arfsettings, $arf_form_all_footer_js, $wpdb, $MdlDb, $arfversion,$footer_cl_logic,$style_settings;
        $return_string = '';
        $arf_classes_blank = '';
        $confirm_password_style = '';
        $confirm_email_style = '';

        $arf_prevent_auto_save = false;
        $arf_prevent_auto_save = apply_filters('arf_prevent_auto_save_form', false, $form);

        foreach ($values['fields'] as $fieldkey => $fieldarr) {
            $fields_key = '';
            $update_arr =false;

            if(isset($fields[$fieldkey]) && $fieldarr['id'] == $fields[$fieldkey]->id ) {
                $fields_key = $fieldkey;
                $update_arr = true;
            } else {
                foreach ($fields as $key => $value) {
                    if($fieldarr['id'] == $value->id) {
                        $fields_key = $key;
                        $update_arr = true;
                    }
                }
            }

            if($update_arr) {

                if( isset($fieldarr['value']) && $fieldarr['value'] != "" ) {

                    $fields[$fields_key]->value = $fieldarr['value'];

                }

                if( isset($fieldarr['default_value']) && $fieldarr['default_value'] != "" ) {

                    $fields[$fields_key]->default_value = $fieldarr['default_value'];

                }

            }

        }

        $form_data = new stdClass();
        $form_data->id = $form->id;
        $form_data->form_key = $form->form_key;
        $form_data->options = maybe_serialize($form->options);
        $form_temp_fields = maybe_unserialize($form->temp_fields);
        foreach ($fields as $key => $value) {
            if (!isset($res_data[$key])) {
                $res_data[$key] = new stdClass();
            }
            $res_data[$key]->id = $value->id;
            $res_data[$key]->type = $value->type;
            $res_data[$key]->name = $value->name;
            $res_data[$key]->field_options = json_encode($value->field_options);
            $res_data[$key]->conditional_logic = maybe_serialize($value->conditional_logic);
        }
        $css_data_arr = $form->form_css;
        $arr = maybe_unserialize($css_data_arr);
        $newarr = array();
        
        $newarr = $arr;
        $_SESSION['label_position'] = $newarr['position'];
        if ($newarr['position'] == 'right') {
            $class_position = 'right_container';
        } else if ($newarr['position'] == 'left') {
            $class_position = 'left_container';
        } else {
            $class_position = 'top_container';
        }

        if ($newarr['hide_labels'] == 1) {
            $class_position .=' none_container';
        }

        $arf_fields = $fields;

        $arf_column_field_custom_width = array(
            'arf_2' => '1.5',
            'arf_3' => '2',
            'arf_4' => '2.25',
            'arf_5' => '2.4',
            'arf_6' => '2.5',
            );

        $arf_fields_merged = array_merge($arf_fields, $values['fields']);
        $field_order = isset($form->options['arf_field_order']) ? $form->options['arf_field_order'] : "";
        $field_order = ($field_order != "") ? json_decode($field_order, true) : array();
        asort($field_order);

        $field_resize_width = isset($form->options['arf_field_resize_width']) ? $form->options['arf_field_resize_width'] : "";
        $field_resize_width = ($field_resize_width != "") ? json_decode($field_resize_width, true) : array();

        $arf_sorted_fields = array();

        $temp_arf_fields = $values['fields'];

        $confirm_email_field_id = $confirm_pass_field_id = array();
        $email_field_ids = $password_field_ids = array();
        $x = 0;
        $email_exist = 0;
        $fields_key = array();
        foreach ($values['fields'] as $temp_key => $temp_value) {
            $fields_key[$temp_key] = $temp_value['id'];
        }
        
        $all_hidden_fields = array();
        foreach ($arf_fields as $key => $tmp_field) {
            if ($tmp_field->type == 'email' && $tmp_field->field_options['confirm_email'] == '1') {
                $current_key = array_search($tmp_field->id, $fields_key);
                $current_field_arr = $form_temp_fields['confirm_email_'.$tmp_field->id];
                $current_field_arr['key'] = $current_key;
                
                $confirm_email_field_id[$x] = $values['fields'][$current_key + 1]['id'];
                $email_field_ids[$x] = $tmp_field->id;
                array_push($arf_fields, $values['fields'][$current_key + 1]);

                $email_field_key = array_keys($email_field_ids, $tmp_field->id);
                if(($key = array_search($current_field_arr['order'], $field_order)) !== false) {
                        unset($field_order[$key]);
                        $field_order[$confirm_email_field_id[$email_field_key[0]]] = $current_field_arr['order'];
                }
            }
            if ($tmp_field->type == 'password' && $tmp_field->field_options['confirm_password'] == '1') {
                $current_key = array_search($tmp_field->id, $fields_key);
                $current_field_arr = $form_temp_fields['confirm_password_'.$tmp_field->id];
                $current_field_arr['key'] = $current_key;

                $confirm_pass_field_id[$x] = $values['fields'][$current_key + 1]['id'];
                $password_field_ids[$x] = $tmp_field->id;
                array_push($arf_fields, $values['fields'][$current_key + 1]);
                $password_field_key = array_keys($password_field_ids, $tmp_field->id);
                if(isset($current_field_arr['order']) && ($key = array_search($current_field_arr['order'], $field_order)) !== false) {
                        unset($field_order[$key]);
                        $field_order[$confirm_pass_field_id[$password_field_key[0]]] = $current_field_arr['order'];
                }
            }
            if( $tmp_field->type == 'hidden' ){
                $all_hidden_fields[] = $tmp_field;
            }
            $x++;
        }
        $field_pos = $x;

        $field_order_updated = array();
        
        $field_order_updated = $field_order;
        
        asort($field_order_updated);
        foreach($all_hidden_fields as $field_id => $field ){
            $field_order_updated[$field->id] = $field_pos;
            $field_pos++;
        }

        foreach ($field_order_updated as $field_id => $field) {
            if(is_int($field_id))
            {
                foreach ($arf_fields as $temp_field) {
                    $temp_field = $this->arfObjtoArray($temp_field);
                    $temp_field_id = $temp_field['id'];
                    if ($temp_field_id == $field_id) {
                        $arf_sorted_fields[] = $temp_field;
                    }
                }
            }
            else {
                $arf_sorted_fields[] = $field_id;
            }
        }
        
        if (isset($arf_sorted_fields) && !empty($arf_sorted_fields)) {
            $arf_fields = $arf_sorted_fields;
        }
        unset($field);
        $class_array = array();
        $conut_arf_fields = count($arf_fields);
        $index_arf_fields = 0;
        $arf_field_front_counter = 1;
        if(isset($GLOBALS['arf_form_field_data'][$form->id])){
            $OFData = $GLOBALS['arf_form_field_data'][$form->id];
        } else {
            $OFData = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->fields . " WHERE form_id = %d ORDER BY id", $form->id));
            $GLOBALS['arf_form_field_data'][$form->id] = $OFData;
        }

        $arf_cookie_field_arr = array();

        foreach ($arf_fields as $field) {
            $material_input_cls = ($inputStyle == 'material') ? 'input-field' : '';
            
            if(is_array($field) || is_object($field)){
                $field = $this->arfObjtoArray($field);

                $field_opt = isset($field['field_options']) ? $field['field_options'] : array();
                if (is_array($field_opt) && !empty($field_opt)) {
                    foreach ($field_opt as $k => $fieldOpt) {
                        if ($k != 'options' && $k != 'default_value') {
                            $field[$k] = $fieldOpt;
                        }
                    }
                } else {
                    $field_opt = isset($field['field_options']) ? json_decode($field['field_options'], true) : json_decode(json_encode(array()), true);
                    if (json_last_error() != JSON_ERROR_NONE) {
                        $field_opt = maybe_unserialize($field['field_options']);
                    }
                    if (is_array($field_opt) && !empty($field_opt)) {
                        foreach ($field_opt as $k => $fieldOpt) {
                            if ($k != 'options' && $k != 'default_value') {
                                $field[$k] = $fieldOpt;
                            }
                        }
                    }
                }
                    $arf_front_main_element_style = "";
                    if(isset($field_resize_width[$arf_field_front_counter]))
                    {

                       if($field['type']=='confirm_email')
                        {
                            $field_level_class = isset($field['confirm_email_classes']) ? $field['confirm_email_classes'] : 'arf_1';
                        }
                        else if($field['type'] == 'confirm_password')
                        {                
                            $field_level_class = isset($field['confirm_password_classes']) ? $field['confirm_password_classes'] : 'arf_1';
                        }
                        else {
                            $field_level_class = isset($field_opt['classes']) ? $field_opt['classes'] : 'arf_1';
                        }                    
                       $calculte_width = str_replace('%','',$field_resize_width[$arf_field_front_counter]) - (isset($arf_column_field_custom_width[$field_level_class]) ? $arf_column_field_custom_width[$field_level_class] : '0');
                        $arf_front_main_element_style = "style='width: ".$calculte_width."%'"; 
                    }

                    if($field['type']=='confirm_email')
                    {
                        $class = isset($field['confirm_email_inner_classes']) ? $field['confirm_email_inner_classes'] : 'arf_1col';
                    }
                    else if($field['type'] == 'confirm_password')
                    {                
                        $class = isset($field['confirm_password_inner_classes']) ? $field['confirm_password_inner_classes'] : 'arf_1col';
                    }
                    else {
                        $class = isset($field_opt['inner_class']) ? $field_opt['inner_class'] : 'arf_1col';
                    }
                array_push($class_array,$class);
                
                $field['value'] = isset($field['value']) ? $field['value'] : '';

                $field['id'] = $arfieldhelper->get_actual_id($field['id']);

                $field_name = 'item_meta[' . $field['id'] . ']';

                
                if (isset($is_confirmation_method) && !$is_confirmation_method || !isset($is_confirmation_method)) {
                    if (isset($_REQUEST) && isset($_REQUEST['item_meta']) && array_key_exists($field['id'], $_REQUEST['item_meta'])) {
                        $field['set_field_value'] = $_REQUEST['item_meta'][$field['id']];
                    }
                    
                    if (isset($_COOKIE['unsave_form_data']) && $arf_prevent_auto_save == false) {
                        $arf_cookie_unsave_form_data = json_decode(stripslashes_deep($_COOKIE['unsave_form_data']));
                        $arf_cookie_form_id = $form->id;
                        if(isset($arf_cookie_unsave_form_data->$arf_cookie_form_id))
                        {
                            $count_arf_cookie_unsave_form_data = count($arf_cookie_unsave_form_data->$arf_cookie_form_id);
                            if($count_arf_cookie_unsave_form_data>0)
                            {
                                foreach ($arf_cookie_unsave_form_data->$arf_cookie_form_id as $arf_cookie_form_id_key => $arf_cookie_form_id_value) {
                                    
                                    if($arf_cookie_form_id_value->name=='item_meta['.$field['id'].']')
                                    {
                                        $arf_cookie_field_arr[$field['id']] = $field['default_value'];
                                        $field['set_field_value'] = $arf_cookie_form_id_value->value;
                                    }
                                    else if($arf_cookie_form_id_value->name=='item_meta['.$field['id'].'][]')
                                    {
                                        if(!empty($field['default_value']))
                                        {
                                            $arf_cookie_field_arr[$field['id']] = implode('~~|~||~|~~', $field['default_value']);
                                        }
                                        else {
                                            $arf_cookie_field_arr[$field['id']] = "";
                                        }
                                        $field['set_field_value'][] = $arf_cookie_form_id_value->value;
                                    }
                                }
                            }
                        }
                        
                    }
                }

                $arf_cookie_field_arr_attr = "";
                if(!empty($arf_cookie_field_arr[$field['id']]))
                {
                    $arf_cookie_field_arr_attr = 'arf_cookie_field_default_attr="'.$arf_cookie_field_arr[$field['id']].'"';
                }

                $field = apply_filters('arfbeforefielddisplay', $field);

                
                $required_class = '';
                $required_class = ($field['required'] == '0') ? '' : ' arffieldrequired';

                if ($field['type'] == 'confirm_password') {
                    $required_class .= ' confirm_password_container arf_confirm_password_field_' . $field['confirm_password_field'];
                }

                if ($field['type'] == 'confirm_email') {
                    $required_class .= ' confirm_email_container arf_confirm_email_field_' . $field['confirm_email_field'];
                }
               
                $field_name = 'item_meta[' . $field['id'] . ']';

                
                $field_description = '';
                if (isset($field['description']) && $field['description'] != '') {
                    
                    $arf_textarea_charlimit_class = '';                   
                    if($field['type'] == 'textarea' && $field['field_options']['max'] > 0){
                        $arf_textarea_charlimit_class = 'arf_textareachar_limit';
                    }

                    $field_description = '<div class="arf_field_description '.$arf_textarea_charlimit_class.'">' . $field['description'] . '</div>';
                }

                

                if (isset($field['multiple']) and $field['multiple'] and ( $field['type'] == 'select' || $field['type'] == ARF_AUTOCOMPLETE_SLUG  || ( $field['type'] == 'data' and isset($field['data_type']) and $field['data_type'] == 'select'))) {
                    $field_name .= '[]';
                }
                
                $field_tooltip = '';
                $field_tooltip_class = '';
                $field_standard_tooltip = '';
                if (isset($field['tooltip_text']) and $field['tooltip_text'] != "") {
                    if($inputStyle=='material')
                    {
                        $field_tooltip = $arfieldhelper->arf_tooltip_display($field['tooltip_text'],$inputStyle);
                        if( $field['type'] == 'text' || $field['type'] == 'textarea' || $field['type'] == 'email' || $field['type'] == 'number' || $field['type'] == 'phone' || $field['type'] == 'date' || $field['type'] == 'time' || $field['type'] == 'url' || $field['type'] == 'image' || $field['type'] == 'password' || $field['type'] == 'arf_autocomplete' )
                        {
                            $field_tooltip_class = ' arfhelptipfocus ';
                        }
                        else {
                            $field_tooltip_class = ' arfhelptip ';
                        }
                    }
                    else {
                        $field_standard_tooltip = $arfieldhelper->arf_tooltip_display($field['tooltip_text'],$inputStyle);
                    }
                }
                if (isset($field['inline_css']) and $field['inline_css'] != '') {
                    $inline_css_with_style_tag = ' style="' . stripslashes_deep($armainhelper->esc_textarea($field['inline_css'])) . '" ';
                    $inline_css_without_style = stripslashes_deep($armainhelper->esc_textarea($field['inline_css']));
                } else {
                    $inline_css_with_style_tag = $inline_css_without_style = '';
                }

                $error_class = isset($errors['field' . $field['id']]) ? ' arfblankfield' : '';

                
                $field['label'] = (isset($values['label_position']) and $values['label_position'] != '') ? $values['label_position'] : $style_settings->position;
                $error_class .= ' ' . $field['label'] . '_container';

                if (isset($field['classes'])) {

                    $error_class .= ' arfformfield';

                    global $arf_column_classes, $is_multi_column_loaded;

                    if ($field['type'] != 'imagecontrol') {
                        if($field['type'] == 'confirm_password'){
                            $field['classes'] = $field['confirm_password_classes'];
                        }
                        if($field['type'] == 'confirm_email'){
                            $field['classes'] = $field['confirm_email_classes'];
                        }
                        if (isset($field['classes']) and $field['classes'] == 'arf_2' and empty($arf_column_classes['two'])) {
                            $arf_column_classes['two'] = '1';
                            $arf_classes = 'frm_first_half';

                            $arf_column_classes['three'] = '';
                            $arf_column_classes['four'] = '';
                            $arf_column_classes['five'] = '';
                            $arf_column_classes['six'] = '';

                            unset($arf_column_classes['three']);
                            unset($arf_column_classes['four']);
                            unset($arf_column_classes['five']);
                            unset($arf_column_classes['six']);



                            $is_multi_column_loaded[] = $form->form_key; 
                        } else if (isset($field['classes']) and $field['classes'] == 'arf_2' and isset($arf_column_classes['two']) and $arf_column_classes['two'] == '1') {
                            $arf_classes = 'frm_last_half';
                            $arf_column_classes['two'] = '';
                            $arf_column_classes['three'] = '';
                            $arf_column_classes['four'] = '';
                            $arf_column_classes['five'] = '';
                            $arf_column_classes['six'] = '';
                            unset($arf_column_classes['two']);
                            unset($arf_column_classes['three']);
                            unset($arf_column_classes['four']);
                            unset($arf_column_classes['five']);
                            unset($arf_column_classes['six']);
                        } else if (isset($field['classes']) and $field['classes'] == 'arf_3' and empty($arf_column_classes['three'])) {
                            $arf_column_classes['three'] = '1';
                            $arf_classes = 'frm_first_third';

                            $arf_column_classes['two'] = '';
                            $arf_column_classes['four'] = '';
                            $arf_column_classes['five'] = '';
                            $arf_column_classes['six'] = '';

                            unset($arf_column_classes['two']);
                            unset($arf_column_classes['four']);
                            unset($arf_column_classes['five']);
                            unset($arf_column_classes['six']);

                            $is_multi_column_loaded[] = $form->form_key; 
                        } else if (isset($field['classes']) and $field['classes'] == 'arf_3' and isset($arf_column_classes['three']) and $arf_column_classes['three'] == '1') {
                            $arf_column_classes['three'] = '2';
                            $arf_classes = 'frm_third';

                            $arf_column_classes['two'] = '';
                            $arf_column_classes['four'] = '';
                            $arf_column_classes['five'] = '';
                            $arf_column_classes['six'] = '';

                            unset($arf_column_classes['two']);
                            unset($arf_column_classes['four']);
                            unset($arf_column_classes['five']);
                            unset($arf_column_classes['six']);
                        } else if (isset($field['classes']) and $field['classes'] == 'arf_3' and isset($arf_column_classes['three']) and $arf_column_classes['three'] == '2') {
                            $arf_classes = 'frm_last_third';

                            $arf_column_classes['two'] = '';
                            $arf_column_classes['three'] = '';
                            $arf_column_classes['four'] = '';
                            $arf_column_classes['five'] = '';
                            $arf_column_classes['six'] = '';
                            unset($arf_column_classes['two']);
                            unset($arf_column_classes['three']);
                            unset($arf_column_classes['four']);
                            unset($arf_column_classes['five']);
                            unset($arf_column_classes['six']);
                        } else if (isset($field['classes']) and $field['classes'] == 'arf_4' and empty($arf_column_classes['four'])) {
                            $arf_column_classes['four'] = '1';
                            $arf_classes = 'frm_first_fourth';

                            $arf_column_classes['two'] = '';
                            $arf_column_classes['three'] = '';
                            $arf_column_classes['five'] = '';
                            $arf_column_classes['six'] = '';

                            unset($arf_column_classes['two']);
                            unset($arf_column_classes['three']);
                            unset($arf_column_classes['five']);
                            unset($arf_column_classes['six']);


                            $is_multi_column_loaded[] = $form->form_key;
                        } else if (isset($field['classes']) and $field['classes'] == 'arf_4' and isset($arf_column_classes['four']) and $arf_column_classes['four'] == '1') {
                            $arf_column_classes['four'] = '2';
                            $arf_classes = 'frm_fourth';

                            $arf_column_classes['two'] = '';
                            $arf_column_classes['three'] = '';
                            $arf_column_classes['five'] = '';
                            $arf_column_classes['six'] = '';

                            unset($arf_column_classes['two']);
                            unset($arf_column_classes['three']);
                            unset($arf_column_classes['five']);
                            unset($arf_column_classes['six']);
                        } else if (isset($field['classes']) and $field['classes'] == 'arf_4' and isset($arf_column_classes['four']) and $arf_column_classes['four'] == '2') {
                            $arf_column_classes['four'] = '3';
                            $arf_classes = 'frm_fourth';

                            $arf_column_classes['two'] = '';
                            $arf_column_classes['three'] = '';
                            $arf_column_classes['five'] = '';
                            $arf_column_classes['six'] = '';

                            unset($arf_column_classes['two']);
                            unset($arf_column_classes['three']);
                            unset($arf_column_classes['five']);
                            unset($arf_column_classes['six']);
                        } else if (isset($field['classes']) and $field['classes'] == 'arf_4' and isset($arf_column_classes['four']) and $arf_column_classes['four'] == '3') {
                            $arf_classes = 'frm_last_fourth';

                            $arf_column_classes['two'] = '';
                            $arf_column_classes['three'] = '';
                            $arf_column_classes['four'] = '';
                            $arf_column_classes['five'] = '';
                            $arf_column_classes['six'] = '';
                            unset($arf_column_classes['two']);
                            unset($arf_column_classes['three']);
                            unset($arf_column_classes['four']);
                            unset($arf_column_classes['five']);
                            unset($arf_column_classes['six']);

                        } else if (isset($field['classes']) and $field['classes'] == 'arf_5' and empty($arf_column_classes['five'])) {
                            $arf_column_classes['five'] = '1';
                            $arf_classes = 'frm_first_fifth';


                            $arf_column_classes['two'] = '';
                            $arf_column_classes['three'] = '';
                            $arf_column_classes['four'] = '';
                            $arf_column_classes['six'] = '';

                            unset($arf_column_classes['two']);
                            unset($arf_column_classes['three']);
                            unset($arf_column_classes['four']);
                            unset($arf_column_classes['six']);

                            $is_multi_column_loaded[] = $form->form_key;
                        } else if (isset($field['classes']) and $field['classes'] == 'arf_5' and isset($arf_column_classes['five']) and $arf_column_classes['five'] == '1') {
                            $arf_column_classes['five'] = '2';
                            $arf_classes = 'frm_fifth';

                            $arf_column_classes['two'] = '';
                            $arf_column_classes['three'] = '';
                            $arf_column_classes['four'] = '';
                            $arf_column_classes['six'] = '';

                            unset($arf_column_classes['two']);
                            unset($arf_column_classes['three']);
                            unset($arf_column_classes['four']);
                            unset($arf_column_classes['six']);
                        } else if (isset($field['classes']) and $field['classes'] == 'arf_5' and isset($arf_column_classes['five']) and $arf_column_classes['five'] == '2') {
                            $arf_column_classes['five'] = '3';
                            $arf_classes = 'frm_fifth';

                            $arf_column_classes['two'] = '';
                            $arf_column_classes['three'] = '';
                            $arf_column_classes['four'] = '';
                            $arf_column_classes['six'] = '';

                            unset($arf_column_classes['two']);
                            unset($arf_column_classes['three']);
                            unset($arf_column_classes['four']);
                            unset($arf_column_classes['six']);
                        } else if (isset($field['classes']) and $field['classes'] == 'arf_5' and isset($arf_column_classes['five']) and $arf_column_classes['five'] == '3') {
                            $arf_column_classes['five'] = '4';
                            $arf_classes = 'frm_fifth';

                            $arf_column_classes['two'] = '';
                            $arf_column_classes['three'] = '';
                            $arf_column_classes['four'] = '';
                            $arf_column_classes['six'] = '';

                            unset($arf_column_classes['two']);
                            unset($arf_column_classes['three']);
                            unset($arf_column_classes['four']);
                            unset($arf_column_classes['six']);
                        } else if (isset($field['classes']) and $field['classes'] == 'arf_5' and isset($arf_column_classes['five']) and $arf_column_classes['five'] == '4') {
                            $arf_classes = 'frm_last_fifth';


                            $arf_column_classes['two'] = '';
                            $arf_column_classes['three'] = '';
                            $arf_column_classes['four'] = '';
                            $arf_column_classes['five'] = '';
                            $arf_column_classes['six'] = '';
                            unset($arf_column_classes['two']);
                            unset($arf_column_classes['three']);
                            unset($arf_column_classes['four']);
                            unset($arf_column_classes['five']);
                            unset($arf_column_classes['six']);
                        } else if (isset($field['classes']) and $field['classes'] == 'arf_6' and empty($arf_column_classes['six'])) {
                            $arf_column_classes['six'] = '1';
                            $arf_classes = 'frm_first_six';


                            $arf_column_classes['two'] = '';
                            $arf_column_classes['three'] = '';
                            $arf_column_classes['four'] = '';
                            $arf_column_classes['five'] = '';

                            unset($arf_column_classes['two']);
                            unset($arf_column_classes['three']);
                            unset($arf_column_classes['four']);
                            unset($arf_column_classes['five']);
                        } else if (isset($field['classes']) and $field['classes'] == 'arf_6' and isset($arf_column_classes['six']) and $arf_column_classes['six'] == '1') {
                            $arf_column_classes['six'] = '2';
                            $arf_classes = 'frm_six';

                            $arf_column_classes['two'] = '';
                            $arf_column_classes['three'] = '';
                            $arf_column_classes['four'] = '';
                            $arf_column_classes['five'] = '';

                            unset($arf_column_classes['two']);
                            unset($arf_column_classes['three']);
                            unset($arf_column_classes['four']);
                            unset($arf_column_classes['five']);
                        } else if (isset($field['classes']) and $field['classes'] == 'arf_6' and isset($arf_column_classes['six']) and $arf_column_classes['six'] == '2') {
                            $arf_column_classes['six'] = '3';
                            $arf_classes = 'frm_six';

                            $arf_column_classes['two'] = '';
                            $arf_column_classes['three'] = '';
                            $arf_column_classes['four'] = '';
                            $arf_column_classes['five'] = '';

                            unset($arf_column_classes['two']);
                            unset($arf_column_classes['three']);
                            unset($arf_column_classes['four']);
                            unset($arf_column_classes['five']);
                        } else if (isset($field['classes']) and $field['classes'] == 'arf_6' and isset($arf_column_classes['six']) and $arf_column_classes['six'] == '3') {
                            $arf_column_classes['six'] = '4';
                            $arf_classes = 'frm_six';

                            $arf_column_classes['two'] = '';
                            $arf_column_classes['three'] = '';
                            $arf_column_classes['four'] = '';
                            $arf_column_classes['five'] = '';

                            unset($arf_column_classes['two']);
                            unset($arf_column_classes['three']);
                            unset($arf_column_classes['four']);
                            unset($arf_column_classes['five']);
                        } else if (isset($field['classes']) and $field['classes'] == 'arf_6' and isset($arf_column_classes['six']) and $arf_column_classes['six'] == '4') {
                            $arf_column_classes['six'] = '5';
                            $arf_classes = 'frm_six';

                            $arf_column_classes['two'] = '';
                            $arf_column_classes['three'] = '';
                            $arf_column_classes['four'] = '';
                            $arf_column_classes['five'] = '';

                            unset($arf_column_classes['two']);
                            unset($arf_column_classes['three']);
                            unset($arf_column_classes['four']);
                            unset($arf_column_classes['five']);
                        } else if (isset($field['classes']) and $field['classes'] == 'arf_6' and isset($arf_column_classes['six']) and $arf_column_classes['six'] == '5') {
                            $arf_column_classes['six'] = '6';
                            $arf_classes = 'frm_last_six';

                            $arf_column_classes['two'] = '';
                            $arf_column_classes['three'] = '';
                            $arf_column_classes['four'] = '';
                            $arf_column_classes['five'] = '';
                            $arf_column_classes['six'] = '';

                            unset($arf_column_classes['two']);
                            unset($arf_column_classes['three']);
                            unset($arf_column_classes['four']);
                            unset($arf_column_classes['five']);
                            unset($arf_column_classes['six']);
                        } else {
                            $arf_column_classes = array();
                            $arf_classes = '';
                        }

                        if (isset($arf_column_classes['six']) and $arf_column_classes['six'] == '6') {
                            $arf_column_classes['six'] = '';
                            unset($arf_column_classes['six']);
                        }
                        if (isset($arf_column_classes['five']) and $arf_column_classes['five'] == '5') {
                            $arf_column_classes['five'] = '';
                            unset($arf_column_classes['five']);
                        }
                        if (isset($arf_column_classes['four']) and $arf_column_classes['four'] == '4') {
                            $arf_column_classes['four'] = '';
                            unset($arf_column_classes['four']);
                        }
                        if (isset($arf_column_classes['three']) and $arf_column_classes['three'] == '3') {
                            $arf_column_classes['three'] = '';
                            unset($arf_column_classes['three']);
                        }
                        if (isset($arf_column_classes['two']) and $arf_column_classes['two'] == '2') {
                            $arf_column_classes['two'] = '';
                            unset($arf_column_classes['two']);
                        }
                    }
                    
                    if($class == 'arf21colclass'){
                         $arf_classes = 'frm_first_half';
                    } else if($class == 'arf_2col') {
                         $arf_classes = 'frm_last_half';
                    }

                    if($class == 'arf31colclass'){
                        $arf_classes = 'frm_first_third';
                    } else if($class == 'arf_23col') {
                        $arf_classes = 'frm_third';
                    } else if($class == 'arf_3col') {
                        $arf_classes = 'frm_last_third';
                    } else if($class == 'arf41colclass'){
                        $arf_classes = 'frm_first_fourth';
                    } else if($class == 'arf42colclass' || $class == 'arf43colclass') {
                        $arf_classes = 'frm_fourth';
                    } else if($class == 'arf_4col') {
                        $arf_classes = 'frm_last_fourth';
                    } else if($class == 'arf51colclass') {
                        $arf_classes = 'frm_first_fifth';
                    } else if($class == 'arf52colclass' || $class == 'arf53colclass' || $class == 'arf54colclass') {
                        $arf_classes = 'frm_fifth';
                    } else if($class == 'arf_5col') {
                        $arf_classes = 'frm_last_fifth';
                    } else if($class == 'arf61colclass') {
                        $arf_classes = 'frm_first_six';
                    } else if($class == 'arf62colclass' || $class == 'arf63colclass' || $class == 'arf64colclass' || $class == 'arf65colclass') {
                        $arf_classes = 'frm_six';
                    } else if($class == 'arf_6col') {
                        $arf_classes = 'frm_last_six';
                    }
                    $arf_classes = isset($arf_classes) ? $arf_classes : '';
                    $error_class .= ' ' . $arf_classes;
                }
                $field_style = $arf_front_main_element_style;
                $prefix = $suffix = "";
                if( $inputStyle != 'material' ){
                    $prefix = $this->arf_prefix_suffix('prefix', $field);
                    $suffix = $this->arf_prefix_suffix('suffix', $field);
                }

                $arf_required = '';
                if ($field['required']) {
                    $field['required_indicator'] = ( isset($field['required_indicator']) && ($field['required_indicator'] != '' )) ? $field['required_indicator'] : '*';
                    $arf_required = '<span class="arfcheckrequiredfield">' . $field['required_indicator'] . '</span>';
                }

                $arf_main_label_cls = $this->arf_label_top_position($newarr['font_size']);

                $arf_main_label = '';

                if( $field['type'] == 'select' || $inputStyle == 'material' ){
                    $arf_main_label_cls .= ' active ';
                }
                

                if( $field['type'] == 'phone' &&  isset($field['phonetype']) && $field['phonetype'] == 1 ){
                    $arf_main_label_cls .= ' arf_phone_label_cls ';
                }

                if( $field['name'] != '' ){
                    $arf_label_for_attribute = '';
                    if( $field['type'] != 'file') {
                        $arf_label_for_attribute = 'for="field_' . $field['field_key'] . '_' . $arf_data_uniq_id.'"';
                    }
                    $arf_main_label .='<label '.$arf_label_for_attribute.' class="arf_main_label '.$arf_main_label_cls.'">' . $field['name'];
                    $arf_main_label .=$arf_required;
                    $arf_main_label .='</label>';
                }

                $field_width = '';
                if (isset($field['field_width']) && $field['field_width'] != '') {
                    $field_width = 'style="width:' . $field['field_width'] . 'px;"';
                }

                $arf_input_field_html = '';
                $arf_input_field_html .= $arfieldcontroller->input_fieldhtml($field, false);
                $arf_input_field_html .= $arfieldcontroller->input_html($field, false);

                $arf_on_change_function = '';

                $is_enable_cl = (isset($field['conditional_logic']) && $field['conditional_logic'] == 1) ? true : false;

                if( $is_enable_cl ){
                    $arf_on_change_function_v3 = $arfieldhelper->arf_field_on_change_function($field['id'],$arf_data_uniq_id,$form_data->options,$field['type']);
                    $arf_on_change_function = $arf_on_change_function_v3;
                    $pattern_for_onclick = '/(arf_cl_apply_v3\((.*?)\))/';
                    preg_match_all($pattern_for_onclick,$arf_on_change_function,$matchesv3);
                    if( isset($matchesv3[0]) && isset($matchesv3[0][0]) && !empty($matchesv3[0][0])){
                        $footer_cl_logic[] = "'".$matchesv3[0][0]."'";
                    }
                }

                $arf_on_change_function = apply_filters('arf_check_for_running_total_field',$arf_on_change_function,$field,$arf_data_uniq_id,$form_data,$res_data);
                if(!empty($arf_on_change_function))
                {
                    $pattern_for_onclick = '/(arf_calculate_total\((.*?)\))/';
                    preg_match_all($pattern_for_onclick,$arf_on_change_function,$matchesv3);
                    if( isset($matchesv3[0]) && isset($matchesv3[0][0]) && !empty($matchesv3[0][0])){
                        $footer_cl_logic[] = "'".$matchesv3[0][0]."'";
                    }
                }

                $display_confirmation_summary = false;

                $frm_opt = maybe_unserialize($form_data->options);

                if( isset($frm_opt['arf_confirmation_summary']) && $frm_opt['arf_confirmation_summary'] == 1 ){
                    $display_confirmation_summary = true;
                }
               
                $required_class .= " arf_field_type_{$field['type']} ";

                $required_class .= ($display_confirmation_summary) ? 'arf_display_to_confirmation_summary' : '';
                
                switch ($field['type']) {
                    case 'imagecontrol':
                        break;
                    case 'break':
                        global $arfprevpage, $arffield;

                        $total_page = 0;

                        if (isset($arfprevpage[$field['form_id']]) and $arfprevpage[$field['form_id']] == $field['field_order']) {

                            $return_string .='<h2 class=" pos_' . $field['label'] . ' [collapse_class]">' . $field['name'] . '</h2>';
                            if ($field['description'] != '') {
                                $description_style = ( isset($field['field_width']) and $field['field_width'] != '' ) ? 'style="width:' . $field['field_width'] . 'px;"' : '';
                                $return_string .='<div class="arf_field_description arf_heading_description" ' . $description_style . '>' . $field['description'] . '</div>';
                            }

                            
                            $previous_fields = $arffield->getAll("fi.type not in ('divider','captcha','break','html') and fi.form_id=$field[form_id]");

                            foreach ($previous_fields as $prev) {

                                if (isset($_POST['item_meta'][$prev->id])) {

                                    if (is_array($_POST['item_meta'][$prev->id])) {

                                        foreach ($_POST['item_meta'][$prev->id] as $checked) {

                                            $checked = apply_filters('arfhiddenvalue', $checked, (array) $prev);

                                            $return_string .= '<input type="hidden" name="item_meta[' . $prev->id . '][]" value="' . $checked . '"/>' . "\n";
                                        }
                                    } else {

                                        $return_string .='<input type="' . apply_filters('arfpagedfieldtype', 'hidden', array('field' => $prev)) . '" id="field_' . $prev->field_key . '" name="item_meta[' . $prev->id . ']" value="' . stripslashes(esc_html($_POST['item_meta'][$prev->id])) . '" />';
                                    }
                                }
                            }
                        } else {

                            global $arf_page_number, $arfform, $arf_column_classes, $page_break_hidden_array, $arf_previous_label;

                            if (isset($field['classes'])) {
                                unset($arf_column_classes['two']);
                                unset($arf_column_classes['three']);
                                unset($arf_column_classes);
                            }

                            $display_page = '';
                            if ($arf_page_number == 0 and $total_page == 1) {
                                $display_temp = $arfieldhelper->get_display_style_new($field, $arf_fields, $form);
                                $display_page = (strpos($display_temp, 'display:none')) ? 'style="display:none;"' : '';
                            } else if ($arf_page_number != 0){
                                $display_page = 'style="display:none;"';
                            }

                            global $arf_section_div;
                            if ($arf_section_div) {
                                $return_string .= "<div class='arf_clear'></div></div>";
                                $arf_section_div = 0;
                            }

                            $return_string .= "<div style='clear:both;height:1px;'>&nbsp;</div></div>";

                            if ($arf_page_number == 0) {
                                $arf_previous_label[0] = $field['pre_page_title'];
                                $arf_previous_label[1] = $field['pre_page_title'];
                            } else {
                                $arf_previous_label[0] = $arf_previous_label[1];
                                $arf_previous_label[1] = $field['pre_page_title'];
                            }
                            $arf_previous_label_txt = $arf_previous_label[0];
                            if (empty($arf_previous_label_txt)) {
                                $arf_previous_label_txt = 'Previous';
                            }

                            $return_string .='<div class="arfsubmitbutton arf_submit_div ' . $_SESSION['label_position'] . '_container" id="arf_submit_div_' . $arf_page_number . '" ' . $display_page . '>';
                            if ($arf_page_number != 0) {

                                $return_string .= '<input type="button" name="previous" class="previous_btn" onclick="go_previous(\'' . ( $arf_page_number - 1) . '\', \'' . $form->id . '\', \'no\', \'' . $form->form_key . '\', \'' . $arf_data_uniq_id . '\');" value="' . $arf_previous_label_txt . '" />';
                            }
                            $return_string .='<input type="submit" class="next_btn" name="next" value="' . $field['next_page_title'] . '" /></div>';

                            $arf_page_number++; 

                            $return_string .= '<div id="page_' . $arf_page_number . '" class="page_break" style="display:none;">';
                        }
                        break;
                    case 'divider':
                        global $arf_page_number, $arf_section_div;
                        if ($arf_section_div) {
                            $return_string .= "<div class='arf_clear'></div></div>\n";
                        } else {
                            $arf_section_div = 1;
                        }
                        $return_string .= '<div id="heading_' . $field['id'] . '" class="arf_heading_div">';
                        $divider_class_for_confirmation = ($display_confirmation_summary) ? 'arf_display_to_confirmation_summary' : '';
                        
                        $section_style = "style='display:block';";
                        if(isset($field['field_options']['ishidetitle']) && $field['field_options']['ishidetitle']==1){
                            $section_style = "style='display:none';";
                        }
                        $return_string .='<h2 '.$section_style.' class="arf_sec_heading_field pos_' . $field['label'] . ' '.$divider_class_for_confirmation.' [collapse_class]" data-field-type="divider">' . $field['name'] . '</h2>';

                        $page_num = isset($values['total_page_break']) ? $values['total_page_break'] : 0;

                        if ($page_num > 0) {
                            $return_string .= '<div '.$section_style.' class="divider_' . $arf_page_number . '">' . "\n";
                        } else {
                            $return_string .= '<div '.$section_style.'>' . "\n";
                        }

                        if ($field['description'] != '') {
                            $description_style = ( isset($field['field_width']) and $field['field_width'] != '' ) ? 'style="width:' . $field['field_width'] . 'px;"' : '';
                            $return_string .='<div class="arf_field_description arf_heading_description" ' . $description_style . '>' . $field['description'] . '</div>';
                        }
                        $return_string .='</div>';
                        break;
                    case 'text':
                        $return_string .= '<div id="arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container" class="arfformfield control-group arfmainformfield ' . $required_class . ' ' . $class_position . '' . $error_class . ' arf_field_' . $field['id'] . '"  ' . $field_style . ' data-field-type="'.$field['type'].'" >';
                        if( $inputStyle != 'material' ){
                            $return_string .=$arf_main_label;
                        }
                        $return_string .='<div class="controls'.$field_tooltip_class.'" ' . $field_width . ' '.$field_tooltip.' >';
                        if( $inputStyle == 'material' ){
                            $return_string .= $arf_main_label;
                        }
                        if (apply_filters('arf_check_for_draw_outside', false, $field)) {
                            
                            $return_string = apply_filters('arf_drawthisfieldfromoutside', $return_string, $field,$arf_on_change_function,$arf_data_uniq_id);
                        } else {
                            $return_string .=$prefix;

                            $arf_single_custom_validation = isset($field['single_custom_validation']) ? $field['single_custom_validation'] : 'custom_validation_none';
                            $arf_custom_validation_expression = "";
                            if ($arf_single_custom_validation == 'custom_validation_none') {
                                $arf_custom_validation_expression = '';
                            } else if ($arf_single_custom_validation == 'custom_validation_alpha') {
                                $arf_custom_validation_expression = '^[a-zA-Z]*$';
                            } else if ($arf_single_custom_validation == 'custom_validation_number') {
                                $arf_custom_validation_expression = '^[0-9]*$';
                            } else if ($arf_single_custom_validation == 'custom_validation_alphanumber') {
                                $arf_custom_validation_expression = '^[a-zA-Z0-9]*$';
                            } else if ($arf_single_custom_validation == 'custom_validation_regex') {
                                $arf_custom_validation_expression = isset($field['arf_regular_expression']) ? $field['arf_regular_expression'] : '';
                            }

                            $arf_regular_expression = ( isset($field['single_custom_validation']) && $arf_custom_validation_expression != '' ) ? 'data-validation-regex-regex="' . esc_attr($arf_custom_validation_expression) . '"  data-validation-regex-message="' . esc_attr($field['arf_regular_expression_msg']) . '"' : '';

                            $return_string .= '<input ' . $arf_regular_expression . '  type="text" id="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" '.$arf_cookie_field_arr_attr.' ';
                            if(isset($field['arf_enable_readonly']) && $field['arf_enable_readonly'] == 1){
                                $return_string .='readonly="readonly" ';    
                            }
                            $return_string .= 'name="' . $field_name . '" ';
                            $return_string .= $arf_input_field_html;

                            
                            $default_value = $field['default_value'];

                            if(isset($arf_arr_preset_data) && count($arf_arr_preset_data) > 0 && isset($arf_arr_preset_data[$field['id']])){

                                $default_value = $arf_arr_preset_data[$field['id']];
                            }

                            if( isset($field['set_field_value']) && $field['set_field_value'] != '' ){
                                $default_value = $field['set_field_value'];
                            }

                            $default_value = apply_filters('arf_replace_default_value_shortcode',$default_value,$field,$form);

                            if( $default_value != '' ){
                                $return_string .=  ' value="'. $default_value .'" ';
                            }

                            if( isset($field['placeholdertext']) && $field['placeholdertext'] != '' ){
                                $return_string .= ' placeholder="'.$field['placeholdertext'].'" ';
                            }

                            if( isset($field['clear_on_focus']) && $field['clear_on_focus'] ){
                                $return_string .= ' onfocus="arfcleardedaultvalueonfocus(\''.$field['placeholdertext'].'\',this,\''.$is_default_blank.'\')"';
                                $return_string .= ' onblur="arfreplacededaultvalueonfocus(\''.$field['placeholdertext'].'\',this,\''.$is_default_blank.'\')"';
                            }

                            if (isset($field['field_width']) and $field['field_width'] != '' and $field['enable_arf_prefix'] != 1 and $field['enable_arf_suffix'] != 1) {
                                $return_string .= ' style="width:' . $field['field_width'] . 'px !important; ' . $inline_css_without_style . '" ';
                            } else {
                                $return_string .= $inline_css_with_style_tag;
                            }

                            if (isset($field['required']) and $field['required']) {
                                $return_string .= ' data-validation-required-message="' . esc_attr($field['blank']) . '" ';
                            }
                            if ($field['minlength'] != '') {
                                $return_string .='minlength="' . $field['minlength'] . '" data-validation-minlength-message="' . esc_attr($field['minlength_message']) . '"';
                            }

                            $return_string .= $arf_on_change_function . '  />';
                            $return_string .= $suffix;
                            $return_string .= $field_standard_tooltip;
                            $return_string .= $field_description;
                        }
                        $return_string .='</div>';
                        $return_string .='</div>';
                        break;
                    case 'textarea':
                        $return_string .= '<div id="arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container" class="arfformfield control-group arfmainformfield ' . $required_class . ' ' . $class_position . '' . $error_class . ' arf_field_' . $field['id'] . '"  ' . $field_style . '  data-field-type="'.$field['type'].'">';
                        if( $inputStyle != 'material' ){
                            $return_string .=$arf_main_label;
                        }
                        $return_string .='<div class="controls'.$field_tooltip_class.'" ' . $field_width . ' '.$field_tooltip.' >';
                        if( $inputStyle == 'material' ){
                            $return_string .= $arf_main_label;
                        }
                        if (apply_filters('arf_check_for_draw_outside', false, $field)) {
                            
                            $return_string = apply_filters('arf_drawthisfieldfromoutside', $return_string, $field,$arf_on_change_function,$arf_data_uniq_id);
                        } else {
                            $arf_text_is_countable = ($field['field_options']['max']>0)?'arf_text_is_countable':'';
                            $return_string .='<textarea name="' . $field_name . '" id="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" ';
                            if (isset($field['max_rows']) && $field['max_rows']) {
                                $return_string .=' rows="' . $field['max_rows'] . '" ';
                            }
                            $return_string .= $arf_input_field_html;

                            $return_string .= $arf_cookie_field_arr_attr;

                            if(isset($field['arf_enable_readonly']) && $field['arf_enable_readonly'] == 1){
                                $return_string .='readonly="readonly" ';    
                            }

                            $default_value = $field['default_value'];
                            if(isset($arf_arr_preset_data) && count($arf_arr_preset_data) > 0 && isset($arf_arr_preset_data[$field['id']])){
                         
                                $default_value = $arf_arr_preset_data[$field['id']];
                            }

                            $default_value = apply_filters('arf_replace_default_value_shortcode',$default_value,$field,$form);

                            if( isset($field['set_field_value']) && $field['set_field_value'] != '' ){
                                $default_value = $field['set_field_value'];
                            }

                            

                            if( isset($field['placeholdertext']) && $field['placeholdertext'] != '' ){
                                $return_string .= ' placeholder="'.$field['placeholdertext'].'" ';
                            }

                            if( isset($field['clear_on_focus']) && $field['clear_on_focus'] ){
                                $return_string .= ' onfocus="arfcleardedaultvalueonfocus(\''.$field['placeholdertext'].'\',this,\''.$is_default_blank.'\')"';
                                $return_string .= ' onblur="arfreplacededaultvalueonfocus(\''.$field['placeholdertext'].'\',this,\''.$is_default_blank.'\')"';
                            }

                            if (isset($field['field_width']) and $field['field_width'] != '') {
                                $return_string .=' style="width:' . $field['field_width'] . 'px !important; ' . $inline_css_without_style . '"';
                            } else {
                                $return_string .= $inline_css_with_style_tag;
                            }

                            if (isset($field['required']) and $field['required']) {
                                $return_string .=' data-validation-required-message="' . esc_attr($field['blank']) . '" ';
                            }

                            if ($field['max'] != '') {
                                $return_string .=' maxlength="'.$field['max'].'" data-validation-maxlength-message="' . addslashes(esc_html__('Invalid maximum characters length', 'ARForms')) . '" ';
                            }
                            
                            if ($field['minlength'] != '') {
                                $return_string .=' minlength="' . $field['minlength'] . '" data-validation-minlength-message="' . esc_attr($field['minlength_message']) . '" ';
                            }
                            

                            $return_string .= $arf_on_change_function . ' >';
                            if( $default_value != '' ){
                                $return_string .= $default_value;
                            }
                            
                            $return_string .= '</textarea>';
                            
                            $number_of_allowed_char = (isset($field['field_options']['max']) && $field['field_options']['max'] != '')?$field['field_options']['max']:'';

                            if($number_of_allowed_char != ''){
                            
                                $return_string .= '<div class="arfcount_text_char_div"><span class="arftextarea_char_count">0</span> / '.$number_of_allowed_char.'</div>';
                            }

                            $return_string .= $field_standard_tooltip;
                            $return_string .= $field_description;
                        }
                        $return_string .='</div>';
                        $return_string .='</div>';
                        break;
                    case 'checkbox':

                        if( $inputStyle == 'material' ){
                            $alignment_class = (isset($field['align']) && $field['align'] == 'block') ? ' arf_vertical_radio' : ' arf_horizontal_radio';
                            $return_string .= '<div id="arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container" class="arfformfield control-group arfmainformfield ' . $alignment_class . ' ' . $required_class . ' ' . $error_class . ' '.$class_position.' arf_field_' . $field['id'] . '"  ' . $field_style . '  data-field-type="'.$field['type'].'">';
                            $return_string .=$arf_main_label;
                            $field_width = '';
                            if (isset($field['field_width']) and $field['field_width'] != '') {
                                $field_width = 'style="width:' . $field['field_width'] . 'px;padding-top:5px;"';
                            } else {
                                $field_width = 'style="padding-top:5px;"';
                            }
                            $checked_values = '';

                            if ($preview) {
                                if (isset($field['field_options']['default_value']) && !empty($field['field_options']['default_value'])) {
                                    $checked_values = $field['field_options']['default_value'];
                                }

                                if(isset($arf_arr_preset_data) && count($arf_arr_preset_data) > 0 && isset($arf_arr_preset_data[$field['id']])){

                                    if(is_array($checked_values)){
                                        array_push($checked_values, $arf_arr_preset_data[$field['id']]);                                            
                                    }else{
                                        $checked_values = array($arf_arr_preset_data[$field['id']]);                                            
                                    }                                
                                }
                            }else {

                                if (isset($field['default_value']) && !empty($field['default_value'])) {
                                    $checked_values = $field['default_value'];
                                } 
                                if(isset($arf_arr_preset_data) && count($arf_arr_preset_data) > 0 && isset($arf_arr_preset_data[$field['id']])){

                                    if(is_array($checked_values)){
                                        array_push($checked_values, $arf_arr_preset_data[$field['id']]);                                            
                                    }else{
                                            $checked_values = array($arf_arr_preset_data[$field['id']]);                                            
                                    }                                
                                }
                            }

                            if (isset($field['set_field_value'])) {
                                if (is_array($checked_values)) {
                                    array_push($checked_values, $field['set_field_value']);
                                } else {
                                    $checked_values = array($field['set_field_value']);
                                }
                                if (is_array($checked_values)) {
                                    array_unique($checked_values);
                                }
                            }


                            if (!is_array($checked_values)) {
                                $checked_values = array($checked_values);
                            }

                            $requested_checked_values = "";
                            if (isset($_REQUEST['checkbox_radio_style_requested'])) {
                                $requested_checked_values = $_REQUEST['checkbox_radio_style_requested'];
                            }


                            if ($field['options']) {                            
                                $checkbox_class = 'arf_material_checkbox';
                                $use_custom_checkbox = false;
                                if ($form->form_css['arfcheckradiostyle'] == 'custom') {
                                    $checkbox_class = "arf_custom_checkbox";
                                    $use_custom_checkbox = true;
                                } else if ($form->form_css['arfcheckradiostyle'] == 'material') {
                                    $checkbox_class .= " arf_default_material ";
                                } else {
                                    $checkbox_class .= " arf_advanced_material ";
                                }
                                $return_string .='<div class="setting_checkbox controls '.$field_tooltip_class.' '. $checkbox_class . '" ' . $field_width . ' '.$field_tooltip.'>';
                                if (apply_filters('arf_check_for_draw_outside', false, $field)) {
                                    
                                    $return_string = apply_filters('arf_drawthisfieldfromoutside', $return_string, $field,$arf_on_change_function,$arf_data_uniq_id);
                                } else {

                                    $field['options'] = $arfieldhelper->changeoptionorder($field);
                                    $k = 0;

                                    $arf_chk_counter = 1;

                                    if (isset($field['align']) && $field['align'] == 'arf_col_2') {
                                        $return_string .= '<div class="arf_chk_radio_col_two">';
                                    } else if (isset($field['align']) && $field['align'] == 'arf_col_3') {
                                        $return_string .= '<div class="arf_chk_radio_col_thiree">';
                                    } else if (isset($field['align']) && $field['align'] == 'arf_col_4') {
                                        $return_string .= '<div class="arf_chk_radio_col_four">';
                                    }


                                    foreach ($field['options'] as $opt_key => $opt) {
                                        $label_image = '';
                                        if (isset($atts) and isset($atts['opt']) and ( $atts['opt'] != $opt_key))
                                            continue;

                                        $field_val = apply_filters('arfdisplaysavedfieldvalue', $opt, $opt_key, $field);

                                        $opt = apply_filters('show_field_label', $opt, $opt_key, $field);

                                        if (is_array($opt)) {
                                            $label_image = isset($opt['label_image']) ? $opt['label_image'] : '';
                                            $opt = $opt['label'];
                                            $field_val = (isset($field['separate_value'])) ? $field_val['value'] : $opt;
                                        }

                                        $arf_radion_image_class = '';
                                        if ($label_image != '') {
                                            $arf_radion_image_class = 'arf_enable_checkbox_image';
                                        }

                                        $checked = '';
                                        if (is_array($checked_values)) {
                                            foreach ($checked_values as $as_val) {
                                                $is_checkbox_checked = false;
                                                if($as_val != '' || $field_val != '') {
                                                    if( is_array($as_val) ){
                                                        if( in_array($field_val,$as_val)){
                                                            $is_checkbox_checked = true;
                                                            $checked = ' checked="checked"';
                                                        }
                                                    } else {
                                                        if (trim(esc_attr($as_val)) === trim(esc_attr($field_val))) {
                                                            $is_checkbox_checked = true;
                                                            $checked = ' checked="checked"';
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        $return_string .= '<div class="arf_checkbox_style ' . $arf_radion_image_class . '" id="frm_checkbox_' . $field['id'] . '-' . $opt_key . '">';
                                        if (!isset($atts) or ! isset($atts['label']) or $atts['label']) {
                                            $_REQUEST['arfaction'] = ( isset($_REQUEST['arfaction']) ) ? $_REQUEST['arfaction'] : "";

                                            $return_string .= "<div class='arf_checkbox_input_wrapper'>";
                                            $return_string .= '<input type="checkbox" name="' . $field_name . '[]" id="field_' . $field['id'] . '-' . $opt_key . '-' . $arf_data_uniq_id . '" value="' . esc_attr($field_val) . '" ' . $checked . ' '.$arf_cookie_field_arr_attr.' ';


                                            $return_string .= $arf_input_field_html;
                                            if ($k == 0) {
                                                if (isset($field['required']) and $field['required']) {
                                                    $return_string .= 'data-validation-minchecked-minchecked="1" data-validation-minchecked-message="' . esc_attr($field['blank']) . '"';
                                                }
                                            }
                                            if(isset($field["max_opt_sel"]) && $field["max_opt_sel"] != '' && $field["max_opt_sel"] > 0) {
                                                if( $field["max_opt_sel"] > count($field["options"]) ) {
                                                    $return_string .= 'data-validation-maxchecked-maxchecked="'.count($field["options"]).'" data-validation-maxchecked-message="' . esc_attr($field['max_opt_sel_msg']) . '"';
                                                }
                                                else {
                                                    $return_string .= 'data-validation-maxchecked-maxchecked="'.$field["max_opt_sel"].'" data-validation-maxchecked-message="' . esc_attr($field['max_opt_sel_msg']) . '"';
                                                }
                                            }

                                            if (isset($_REQUEST['arfaction']) && $_REQUEST['arfaction'] == 'preview') {
                                                $return_string .= $arf_on_change_function;
                                            }

                                            $return_string .= " style='{$inline_css_without_style}' {$arf_on_change_function} />";
                                            $return_string .= '<span>';
                                            if ($use_custom_checkbox == true) {
                                                $custom_checkbox = $form->form_css['arf_checked_checkbox_icon'];
                                                $return_string .= "<i class='{$custom_checkbox}'></i>";
                                            }
                                            $return_string .= '</span>';
                                            $return_string .= '</div>';
                                            $return_string .= '<label for="field_' . $field['id'] . '-' . $opt_key . '-' . $arf_data_uniq_id . '" >';
                                            if ($label_image != '') {
                                                $temp_check = '';
                                                    if ($is_checkbox_checked) {
                                                    $temp_check = 'checked';
                                                }
                                                $return_string .= '<span data-fid="field_' . $field['id'] . '-' . $opt_key . '-' . $arf_data_uniq_id . '" class="arf_checkbox_label_image ' . $temp_check . '" style="height:120px;width: 120px;background-image : url(' . esc_attr($label_image) . ');background-size : contain;display:block;"></span>';
                                                $return_string .= '<span class="arf_checkbox_label">';
                                            }
                                            $return_string .= html_entity_decode($opt);

                                            if ($label_image != '') {
                                                $return_string .='</span>';
                                            }

                                            $return_string .='</label>';
                                        }
                                        $return_string .='</div>';

                                        if (isset($field['align']) && $field['align'] == 'arf_col_2') {
                                            if ($arf_chk_counter % 2 == 0) {
                                                $return_string .='</div><div class="arf_chk_radio_col_two">';
                                            }
                                        } else if (isset($field['align']) && $field['align'] == 'arf_col_3') {
                                            if ($arf_chk_counter % 3 == 0) {
                                                $return_string .='</div><div class="arf_chk_radio_col_thiree">';
                                            }
                                        } else if (isset($field['align']) && $field['align'] == 'arf_col_4') {
                                            if ($arf_chk_counter % 4 == 0) {
                                                $return_string .='</div><div class="arf_chk_radio_col_four">';
                                            }
                                        }
                                        $k++;
                                        $arf_chk_counter++;
                                    }

                                    if (isset($field['align']) && ($field['align'] == 'arf_col_2' || $field['align'] == 'arf_col_3' || $field['align'] == 'arf_col_4')) {
                                        $return_string .= '</div>';
                                    }
                                }
                                $return_string .= $field_standard_tooltip;
                                $return_string .= $field_description;
                                $return_string .= '</div>';
                            }
                            $return_string .='</div>';
                        } else {
                            $alignment_class = (isset($field['align']) && $field['align'] == 'block') ? ' arf_vertical_radio' : ' arf_horizontal_radio';
                            $return_string .= '<div id="arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container" class="arfformfield control-group arfmainformfield ' . $alignment_class . ' ' . $required_class . '  ' . $class_position . ' ' . $error_class . ' arf_field_' . $field['id'] . '"  ' . $field_style . '  data-field-type="'.$field['type'].'">';
                            $return_string .=$arf_main_label;
                            $field_width = '';
                            if (isset($field['field_width']) and $field['field_width'] != '') {
                                $field_width = 'style="width:' . $field['field_width'] . 'px;padding-top:5px;"';
                            } else {
                                $field_width = 'style="padding-top:5px;"';
                            }
                            $checked_values = '';

                            if ($preview) {
                                if (isset($field['field_options']['default_value']) && !empty($field['field_options']['default_value'])) {
                                    $checked_values = $field['field_options']['default_value'];
                                }

                                if(isset($arf_arr_preset_data) && count($arf_arr_preset_data) > 0 && isset($arf_arr_preset_data[$field['id']])){

                                    if(is_array($checked_values)){
                                        array_push($checked_values, $arf_arr_preset_data[$field['id']]);                                            
                                    }else{
                                        $checked_values = array($arf_arr_preset_data[$field['id']]);                                            
                                    }                                
                                }
                            } else {
                                if (isset($field['default_value']) && !empty($field['default_value'])) {
                                    $checked_values = $field['default_value'];
                                }

                                if(isset($arf_arr_preset_data) && count($arf_arr_preset_data) > 0 && isset($arf_arr_preset_data[$field['id']])){

                                    if(is_array($checked_values)){
                                        array_push($checked_values, $arf_arr_preset_data[$field['id']]);                                            
                                    }else{
                                        $checked_values = array($arf_arr_preset_data[$field['id']]);                                            
                                    }                                
                                }
                            }

                            if (isset($field['set_field_value'])) {
                                if (is_array($checked_values)) {
                                    array_push($checked_values, $field['set_field_value']);
                                } else {
                                    $checked_values = array($field['set_field_value']);
                                }
                                if (is_array($checked_values)) {
                                    array_unique($checked_values);
                                }
                            }


                            if (!is_array($checked_values)) {
                                $checked_values = array($checked_values);
                            }

                            $requested_checked_values = "";
                            if (isset($_REQUEST['checkbox_radio_style_requested'])) {
                                $requested_checked_values = $_REQUEST['checkbox_radio_style_requested'];
                            }


                            if ($field['options']) {
                                $checkbox_class = 'arf_standard_checkbox';
                                $use_custom_checkbox = false;
                                if ($form->form_css['arfcheckradiostyle'] == 'custom') {
                                    $checkbox_class = 'arf_custom_checkbox';
                                    $use_custom_checkbox = true;
                                }
                                if ($form->form_css['arfinputstyle'] == 'rounded' && $form->form_css['arfcheckradiostyle'] != 'custom') {
                                    $checkbox_class = 'arf_rounded_flat_checkbox';
                                    $use_custom_checkbox = false;
                                }
                                if ($form->form_css['arfinputstyle'] == 'rounded' && $form->form_css['arfcheckradiostyle'] == 'custom') {
                                    $checkbox_class = 'arf_rounded_flat_checkbox arf_custom_checkbox';
                                    $use_custom_checkbox = true;
                                }
                                $return_string .='<div class="setting_checkbox controls ' . $checkbox_class . '" ' . $field_width . ' >';
                                if (apply_filters('arf_check_for_draw_outside', false, $field)) {
                                    
                                    $return_string = apply_filters('arf_drawthisfieldfromoutside', $return_string, $field,$arf_on_change_function,$arf_data_uniq_id);                            
                                } else {

                                    $field['options'] = $arfieldhelper->changeoptionorder($field);
                                    $k = 0;

                                    $arf_chk_counter = 1;

                                    if (isset($field['align']) && $field['align'] == 'arf_col_2') {
                                        $return_string .= '<div class="arf_chk_radio_col_two">';
                                    } else if (isset($field['align']) && $field['align'] == 'arf_col_3') {
                                        $return_string .= '<div class="arf_chk_radio_col_thiree">';
                                    } else if (isset($field['align']) && $field['align'] == 'arf_col_4') {
                                        $return_string .= '<div class="arf_chk_radio_col_four">';
                                    }

                                    
                                    foreach ($field['options'] as $opt_key => $opt) {
                                        $label_image = '';
                                        if (isset($atts) and isset($atts['opt']) and ( $atts['opt'] != $opt_key))
                                            continue;

                                        $field_val = apply_filters('arfdisplaysavedfieldvalue', $opt, $opt_key, $field);

                                        $opt = apply_filters('show_field_label', $opt, $opt_key, $field);

                                        if (is_array($opt)) {
                                            $label_image = isset($opt['label_image']) ? $opt['label_image'] : '';
                                            $opt = $opt['label'];
                                            $field_val = (isset($field['separate_value'])) ? $field_val['value'] : $opt;
                                        }

                                        $arf_radion_image_class = '';
                                        if ($field['use_image'] == 1 && $label_image != '') {
                                            $arf_radion_image_class = 'arf_enable_checkbox_image';
                                        }

                                        $checked = '';

                                        if (is_array($checked_values)) {
                                            foreach ($checked_values as $as_val) {
                                                $is_checkbox_checked = false;
                                                if($as_val != '' || $field_val != '') {
                                                    if( is_array($as_val) ){
                                                        if( in_array($field_val,$as_val)){
                                                            $is_checkbox_checked = true;
                                                            $checked = ' checked="checked"';
                                                        }
                                                    } else {
                                                        if (trim(esc_attr($as_val)) === trim(esc_attr($field_val))) {
                                                            $is_checkbox_checked = true;
                                                            $checked = ' checked="checked"';
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        $return_string .= '<div class="arf_checkbox_style ' . $arf_radion_image_class . '" id="frm_checkbox_' . $field['id'] . '-' . $opt_key . '">';
                                        if (!isset($atts) or ! isset($atts['label']) or $atts['label']) {
                                            $_REQUEST['arfaction'] = ( isset($_REQUEST['arfaction']) ) ? $_REQUEST['arfaction'] : "";

                                            $return_string .= "<div class='arf_checkbox_input_wrapper'>";
                                            $return_string .= '<input type="checkbox" name="' . $field_name . '[]" id="field_' . $field['id'] . '-' . $opt_key . '-' . $arf_data_uniq_id . '" value="' . esc_attr($field_val) . '" ' . $checked . ' '.$arf_cookie_field_arr_attr.' ';


                                            $return_string .= $arf_input_field_html;
                                            if ($k == 0) {
                                                if (isset($field['required']) and $field['required']) {
                                                    $return_string .= 'data-validation-minchecked-minchecked="1" data-validation-minchecked-message="' . esc_attr($field['blank']) . '"';
                                                }
                                            }
                                            if(isset($field["max_opt_sel"]) && $field["max_opt_sel"] != '' && $field["max_opt_sel"] > 0) {
                                                if( $field["max_opt_sel"] > count($field["options"]) ) {
                                                    $return_string .= 'data-validation-maxchecked-maxchecked="'.count($field["options"]).'" data-validation-maxchecked-message="' . esc_attr($field['max_opt_sel_msg']) . '"';
                                                }
                                                else {
                                                    $return_string .= 'data-validation-maxchecked-maxchecked="'.$field["max_opt_sel"].'" data-validation-maxchecked-message="' . esc_attr($field['max_opt_sel_msg']) . '"';
                                                }
                                            }

                                            if (@$_REQUEST['arfaction'] == 'preview') {
                                                $return_string .= $arf_on_change_function;
                                            }
                                            
                                            $return_string .= " style='".$inline_css_without_style."' ".$arf_on_change_function." />";
                                            
                                            $return_string .= "<span>";
                                            if ($use_custom_checkbox) {
                                                $custom_checkbox = $form->form_css['arf_checked_checkbox_icon'];
                                                $return_string .= "<i class='{$custom_checkbox}'></i>";
                                            }
                                            $return_string .= "</span>";
                                            $return_string .= "</div>";
                                            $return_string .= '<label for="field_' . $field['id'] . '-' . $opt_key . '-' . $arf_data_uniq_id . '" >';
                                            if ($field['use_image'] == 1 && $label_image != '') {
                                                $temp_check = '';
                                                if ($is_checkbox_checked) {
                                                    $temp_check = 'checked';
                                                }
                                                $return_string .= '<span data-fid="field_' . $field['id'] . '-' . $opt_key . '-' . $arf_data_uniq_id . '" class="arf_checkbox_label_image ' . $temp_check . '" style="height:120px;width: 120px;background-image : url(' . esc_attr($label_image) . ');background-size : contain;display:block;"></span>';
                                                $return_string .= '<span class="arf_checkbox_label">';
                                            }
                                            $return_string .= html_entity_decode($opt);

                                            if ($field['use_image'] == 1 && $label_image != '') {
                                                $return_string .='</span>';
                                            }

                                            $return_string .='</label>';

                                        }
                                        $return_string .='</div>';

                                        if (isset($field['align']) && $field['align'] == 'arf_col_2') {
                                            if ($arf_chk_counter % 2 == 0) {
                                                $return_string .='</div><div class="arf_chk_radio_col_two">';
                                            }
                                        } else if (isset($field['align']) && $field['align'] == 'arf_col_3') {
                                            if ($arf_chk_counter % 3 == 0) {
                                                $return_string .='</div><div class="arf_chk_radio_col_thiree">';
                                            }
                                        } else if (isset($field['align']) && $field['align'] == 'arf_col_4') {
                                            if ($arf_chk_counter % 4 == 0) {
                                                $return_string .='</div><div class="arf_chk_radio_col_four">';
                                            }
                                        }
                                        $k++;
                                        $arf_chk_counter++;
                                    }

                                    if (isset($field['align']) && ($field['align'] == 'arf_col_2' || $field['align'] == 'arf_col_3' || $field['align'] == 'arf_col_4')) {
                                        $return_string .= '</div>';
                                    }
                                }
                                $return_string .= $field_standard_tooltip;
                                $return_string .= $field_description;
                                $return_string .= '</div>';
                            }
                            $return_string .='</div>';
                        }
                        break;
                    case 'radio':
                        if( $inputStyle == 'material' ){
                            $alignment_class = (isset($field['align']) && $field['align'] == 'block') ? ' arf_vertical_radio' : ' arf_horizontal_radio';
                            $return_string .= '<div id="arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container" class="arfformfield control-group arfmainformfield ' . $alignment_class . ' ' . $required_class . ' ' . $error_class . ' '.$class_position.' arf_field_' . $field['id'] . '"  ' . $field_style . '  data-field-type="'.$field['type'].'">';
                            $return_string .=$arf_main_label;

                            $requested_radio_checked_values = "";
                            if (isset($_REQUEST['checkbox_radio_style_requested'])) {
                                $requested_radio_checked_values = $_REQUEST['checkbox_radio_style_requested'];
                            }
                            if (isset($field['set_field_value'])) {
                                $field['value'] = $field['set_field_value'];
                            }
                            
                            $arf_radion_image_class = '';
                            if (isset($field['label_image']) && $field['label_image']) {
                                $arf_radion_image_class = 'arf_enable_radio_image';
                            }

                            if (is_array($field['options'])) {

                                $field_width = '';
                                if (isset($field['field_width']) and $field['field_width'] != '') {
                                    $field_width = 'style="width:' . $field['field_width'] . 'px;padding-top:5px;"';
                                } else {
                                    $field_width = 'style="padding-top:5px;"';
                                }
                                $radio_class = 'arf_material_radio';
                                $use_custom_radio = false;
                                if ($form->form_css['arfcheckradiostyle'] == 'custom') {
                                    $radio_class = "arf_custom_radio";
                                    $use_custom_radio = true;
                                } else if ($form->form_css['arfcheckradiostyle'] == 'material') {
                                    $radio_class .= " arf_default_material ";
                                } else {
                                    $radio_class .= " arf_advanced_material ";
                                }
                                
                                $return_string .='<div class="setting_radio controls '.$field_tooltip_class.' ' . $radio_class . '" ' . $field_width . ' '.$field_tooltip.'>';
                                if (apply_filters('arf_check_for_draw_outside', false, $field)) {
                                    
                                    $return_string = apply_filters('arf_drawthisfieldfromoutside', $return_string, $field,$arf_on_change_function,$arf_data_uniq_id);
                                } else {
                                    $field['options'] = $arfieldhelper->changeoptionorder($field);

                                    $k = 0;
                                    $arf_chk_counter = 1;

                                    if (isset($field['align']) && $field['align'] == 'arf_col_2') {
                                        $return_string .= '<div class="arf_chk_radio_col_two">';
                                    } else if (isset($field['align']) && $field['align'] == 'arf_col_3') {
                                        $return_string .= '<div class="arf_chk_radio_col_thiree">';
                                    } else if (isset($field['align']) && $field['align'] == 'arf_col_4') {
                                        $return_string .= '<div class="arf_chk_radio_col_four">';
                                    }
                                    
                                    foreach ($field['options'] as $opt_key => $opt) {
                                        $label_image = '';
                                        if (isset($atts) and isset($atts['opt']) and ( $atts['opt'] != $opt_key))
                                            continue;

                                        $field_val = apply_filters('arfdisplaysavedfieldvalue', $opt, $opt_key, $field);

                                        $opt = apply_filters('show_field_label', $opt, $opt_key, $field);
                                        if (is_array($opt)) {
                                            $label_image = isset($opt['label_image']) ? $opt['label_image'] : '';
                                            $opt = $opt['label'];
                                            $field_val = (isset($field['separate_value'])) ? $field_val['value'] : $opt;
                                        }

                                        if(isset($field['value']) && isset($field['set_field_value']) && $field['value'] != "" ){
                                            $field['default_value'] = $field['value'];
                                        }

                                        $arf_radio_input_wrapper_cls = '';
                                        if ($field['use_image'] == 1 && isset($label_image) && $label_image != '') {
                                            $arf_radio_input_wrapper_cls = 'arf_enable_radio_image';
                                        }
                                        $return_string .= '<div class="arf_radiobutton '.$arf_radio_input_wrapper_cls.'">';

                                        if (!isset($atts) or ! isset($atts['label']) or $atts['label']) {

                                            $return_string .= "<div class='arf_radio_input_wrapper'>";

                                            $return_string .='<input type="radio" name="' . $field_name . '" id="field_' . $field['id'] . '-' . $opt_key . '-' . $arf_data_uniq_id . '" data-unique-id="' . $arf_data_uniq_id . '" value="' . esc_attr($field_val) . '" '.$arf_cookie_field_arr_attr.' ';
                                            $is_radio_checked = false;
                                            if (isset($field['default_value']) && $field['default_value'] != '' && $field_val == $field['default_value']) {
                                                $is_radio_checked = true;
                                                $return_string .= 'checked="checked" ';
                                            }

                                            if(isset($arf_arr_preset_data) && count($arf_arr_preset_data) > 0 && isset($arf_arr_preset_data[$field['id']]) && $field_val == $arf_arr_preset_data[$field['id']]){

                                                $is_radio_checked = true;
                                                $return_string .= 'checked="checked" ';                                               
                                            }

                                            $return_string .= $arf_input_field_html;

                                            if ($k == 0) {
                                                if (isset($field['required']) and $field['required']) {
                                                    $return_string .= ' data-validation-minchecked-minchecked="1" data-validation-minchecked-message="' . esc_attr($field['blank']) . '"';
                                                }
                                            }
                                            $return_string .= $arf_on_change_function;
                                            $return_string .= ' style=" ' . $inline_css_without_style . '" />';
                                            $return_string .= "<span>";
                                            if ($use_custom_radio == true) {
                                                $custom_radio = $form->form_css['arf_checked_radio_icon'];
                                                $return_string .= "<i class='{$custom_radio}'></i>";
                                            }
                                            $return_string .= "</span>";
                                            $return_string .= "</div>";
                                            $arf_radion_image_class = '';
                                            if ($field['use_image'] == 1 && isset($label_image) && $label_image != '') {
                                                $arf_radion_image_class = 'arf_enable_radio_image';
                                            }

                                            $return_string .='<label for="field_' . $field['id'] . '-' . $opt_key . '-' . $arf_data_uniq_id . '" class="' . $arf_radion_image_class . '">';
                                            if ($field['use_image'] == 1 && $label_image != '') {

                                                $return_string .= '<span data-fid="field_' . $field['id'] . '-' . $opt_key . '-' . $arf_data_uniq_id . '" class="arf_radio_label_image ';
                                                if ($is_radio_checked) {
                                                    $return_string .= 'checked';
                                                }
                                                $return_string .= '" style="height:120px;width: 120px;background-image : url(' . esc_attr($label_image) . ');background-size : contain;display:block;"></span><span class="arf_radio_label">';
                                            }
                                            $return_string .= html_entity_decode($opt);
                                            if (isset($field['radio_use_image']) && $field['radio_use_image']) {
                                                $return_string .='</span>';
                                            }


                                            $return_string .='</label>';
                                        }
                                        $return_string .= '</div>';
                                        if (isset($field['align']) && $field['align'] == 'arf_col_2') {
                                            if ($arf_chk_counter % 2 == 0) {
                                                $return_string .='</div><div class="arf_chk_radio_col_two">';
                                            }
                                        } else if (isset($field['align']) && $field['align'] == 'arf_col_3') {
                                            if ($arf_chk_counter % 3 == 0) {
                                                $return_string .='</div><div class="arf_chk_radio_col_thiree">';
                                            }
                                        } else if (isset($field['align']) && $field['align'] == 'arf_col_4') {
                                            if ($arf_chk_counter % 4 == 0) {
                                                $return_string .='</div><div class="arf_chk_radio_col_four">';
                                            }
                                        }
                                        $k++;
                                        $arf_chk_counter++;
                                    }

                                    if (isset($field['align']) && ($field['align'] == 'arf_col_2' || $field['align'] == 'arf_col_3' || $field['align'] == 'arf_col_4')) {
                                        $return_string .= '</div>';
                                    }
                                }
                                $return_string .= $field_standard_tooltip;
                                $return_string .= $field_description;

                                $return_string .= '</div>';
                            }

                            $return_string .='</div>';
                        } else {
                            $alignment_class = (isset($field['align']) && $field['align'] == 'block') ? ' arf_vertical_radio' : ' arf_horizontal_radio';
                            $return_string .= '<div id="arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container" class="arfformfield control-group arfmainformfield ' . $alignment_class . ' ' . $required_class . '  ' . $class_position . ' ' . $error_class . ' arf_field_' . $field['id'] . '"  ' . $field_style . '  data-field-type="'.$field['type'].'">';
                            $return_string .=$arf_main_label;

                            $requested_radio_checked_values = "";
                            if (isset($_REQUEST['checkbox_radio_style_requested'])) {
                                $requested_radio_checked_values = $_REQUEST['checkbox_radio_style_requested'];
                            }
                            if (isset($field['set_field_value'])) {
                                $field['value'] = $field['set_field_value'];
                            }
                            
                            $arf_radion_image_class = '';
                            if (isset($field['label_image']) && $field['label_image']) {
                                $arf_radion_image_class = 'arf_enable_radio_image';
                            }

                            

                            if (is_array($field['options'])) {

                                $field_width = '';
                                if (isset($field['field_width']) and $field['field_width'] != '') {
                                    $field_width = 'style="width:' . $field['field_width'] . 'px;padding-top:5px;"';
                                } else {
                                    $field_width = 'style="padding-top:5px;"';
                                }
                                $radio_class = 'arf_standard_radio';
                                $use_custom_radio = false;
                                if ($form->form_css['arfcheckradiostyle'] == 'custom') {
                                    $radio_class = 'arf_custom_radio';
                                    $use_custom_radio = true;
                                }
                                if ($form->form_css['arfinputstyle'] == 'rounded' && $form->form_css['arfcheckradiostyle'] != 'custom') {
                                    $radio_class = 'arf_rounded_flat_radio';
                                    $use_custom_radio = false;
                                }

                                $return_string .='<div class="setting_radio controls ' . $radio_class . ' " ' . $field_width . '>';
                                if (apply_filters('arf_check_for_draw_outside', false, $field)) {
                                    
                                    $return_string = apply_filters('arf_drawthisfieldfromoutside', $return_string, $field,$arf_on_change_function,$arf_data_uniq_id);
                                } else {
                                    $field['options'] = $arfieldhelper->changeoptionorder($field);

                                    $k = 0;
                                    $arf_chk_counter = 1;

                                    if (isset($field['align']) && $field['align'] == 'arf_col_2') {
                                        $return_string .= '<div class="arf_chk_radio_col_two">';
                                    } else if (isset($field['align']) && $field['align'] == 'arf_col_3') {
                                        $return_string .= '<div class="arf_chk_radio_col_thiree">';
                                    } else if (isset($field['align']) && $field['align'] == 'arf_col_4') {
                                        $return_string .= '<div class="arf_chk_radio_col_four">';
                                    }
                                    foreach ($field['options'] as $opt_key => $opt) {
                                        $label_image = '';
                                        if (isset($atts) and isset($atts['opt']) and ( $atts['opt'] != $opt_key))
                                            continue;

                                        $field_val = apply_filters('arfdisplaysavedfieldvalue', $opt, $opt_key, $field);

                                        $opt = apply_filters('show_field_label', $opt, $opt_key, $field);
                                        if (is_array($opt)) {
                                            $label_image = isset($opt['label_image']) ? $opt['label_image'] : '';
                                            $opt = $opt['label'];
                                            $field_val = (isset($field['separate_value'])) ? $field_val['value'] : $opt;
                                        }
                                        if(isset($field['value']) && isset($field['set_field_value']) && $field['value'] != "" ){
                                            $field['default_value'] = $field['value'];
                                        }

                                            
                                            $arf_radion_image_class = '';
                                            if ($field['use_image'] == 1 && isset($label_image) && $label_image != '') {
                                                $arf_radion_image_class = 'arf_enable_radio_image';
                                            }
                                            $return_string .= '<div class="arf_radiobutton ' . $arf_radion_image_class . '">';

                                        if (!isset($atts) or ! isset($atts['label']) or $atts['label']) {

                                            $return_string .= "<div class='arf_radio_input_wrapper'>";

                                                $return_string .='<input type="radio" name="' . $field_name . '" id="field_' . $field['id'] . '-' . $opt_key . '-' . $arf_data_uniq_id . '" data-unique-id="' . $arf_data_uniq_id . '" value="' . esc_attr($field_val) . '" '.$arf_cookie_field_arr_attr.' ';
                                                $is_radio_checked = false;
                                                if (isset($field['default_value']) && $field['default_value'] != '' && $field_val == $field['default_value']) {
                                                    $is_radio_checked = true;
                                                    $return_string .= 'checked="checked" ';
                                                }

                                                if(isset($arf_arr_preset_data) && count($arf_arr_preset_data) > 0 && isset($arf_arr_preset_data[$field['id']]) && $field_val == $arf_arr_preset_data[$field['id']]){

                                                    $is_radio_checked = true;
                                                    $return_string .= 'checked="checked" ';
                                                }

                                            $return_string .= $arf_input_field_html;

                                            if ($k == 0) {
                                                if (isset($field['required']) and $field['required']) {
                                                    $return_string .= ' data-validation-minchecked-minchecked="1" data-validation-minchecked-message="' . esc_attr($field['blank']) . '"';
                                                }
                                            }
                                            $return_string .= $arf_on_change_function;
                                            $return_string .= ' style=" ' . $inline_css_without_style . '" />';
                                            $return_string .= "<span>";
                                            if ($use_custom_radio == true) {
                                                $custom_radio = $form->form_css['arf_checked_radio_icon'];
                                                $return_string .= "<i class='{$custom_radio}'></i>";
                                            }
                                            $return_string .= "</span>";
                                            $return_string .= "</div>";

                                            $return_string .='<label for="field_' . $field['id'] . '-' . $opt_key . '-' . $arf_data_uniq_id . '" class="' . $arf_radion_image_class . '">';

                                            if ($field['use_image'] == 1 && $label_image != '') {

                                                $return_string .= '<span data-fid="field_' . $field['id'] . '-' . $opt_key . '-' . $arf_data_uniq_id . '" class="arf_radio_label_image ';
                                                if ($is_radio_checked) {
                                                    $return_string .= 'checked';
                                                }
                                                $return_string .= '" style="height:120px;width: 120px;background-image : url(' . esc_attr($label_image) . ');background-size : contain;display:block;"></span><span class="arf_radio_label">';
                                            }

                                            $return_string .= html_entity_decode($opt);

                                            if ($label_image != '') {
                                                $return_string .='</span>';
                                            }


                                            $return_string .='</label>';
                                        }
                                        $return_string .= '</div>';
                                        if (isset($field['align']) && $field['align'] == 'arf_col_2') {
                                            if ($arf_chk_counter % 2 == 0) {
                                                $return_string .='</div><div class="arf_chk_radio_col_two">';
                                            }
                                        } else if (isset($field['align']) && $field['align'] == 'arf_col_3') {
                                            if ($arf_chk_counter % 3 == 0) {
                                                $return_string .='</div><div class="arf_chk_radio_col_thiree">';
                                            }
                                        } else if (isset($field['align']) && $field['align'] == 'arf_col_4') {
                                            if ($arf_chk_counter % 4 == 0) {
                                                $return_string .='</div><div class="arf_chk_radio_col_four">';
                                            }
                                        }
                                        $k++;
                                        $arf_chk_counter++;
                                    }

                                    if (isset($field['align']) && ($field['align'] == 'arf_col_2' || $field['align'] == 'arf_col_3' || $field['align'] == 'arf_col_4')) {
                                        $return_string .= '</div>';
                                    }
                                }
                                $return_string .= $field_standard_tooltip;
                                $return_string .= $field_description;

                                $return_string .= '</div>';
                            }

                            $return_string .='</div>';
                        }
                        break;
                    case 'select':
                        $return_string .= '<div id="arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container" class="arfformfield input-field control-group arfmainformfield ' . $required_class . ' '.$class_position.' ' . $error_class . ' arf_field_' . $field['id'] . '"  ' . $field_style . '  data-field-type="'.$field['type'].'">';
                        if( $inputStyle != 'material' ){
                            $return_string .= $arf_main_label;
                        }
                        $return_string .='<div class=" sltstandard_front controls'.$field_tooltip_class.'" ' . $field_width . ' '.$field_tooltip.'>';
                        if( $inputStyle == 'material' ){
                            $return_string .= $arf_main_label;   
                        }
                        $arfdefault_selected_val = (isset($field['separate_value']) && $field['separate_value']) ? $field['default_value'] : (isset($field['value']) ? $field['value'] : '');

                        if(isset($arf_arr_preset_data) && count($arf_arr_preset_data) > 0 && isset($arf_arr_preset_data[$field['id']])){

                            $arfdefault_selected_val = $arf_arr_preset_data[$field['id']];
                        }

                        if (isset($field['set_field_value'])) {
                            $arfdefault_selected_val = $field['set_field_value'];
                        }
                        if (apply_filters('arf_check_for_draw_outside', false, $field)) {
                            
                            $return_string = apply_filters('arf_drawthisfieldfromoutside', $return_string, $field,$arf_on_change_function,$arf_data_uniq_id);
                        } else {
                            $field['options'] = $arfieldhelper->changeoptionorder($field);

                            $return_string .= '<select name="' . $field_name . '"';
                            if (isset($field['required']) and $field['required']) {
                                $return_string .=' data-validation-required-message="' . esc_attr($field['blank']) . '"';
                            }
                            $return_string .=' id="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" ';

                            $return_string .= $arf_input_field_html;
                            if (isset($field['size']) && $field['size'] != 1) {
                                if (($field['field_width'] != '' || $newarr['auto_width'] != 1) and $field['field_width'] != '') {
                                    $return_string .= 'style="width:' . $field['field_width'] . 'px !important; ' . $inline_css_without_style . '"';
                                } else {
                                    $return_string .= $inline_css_with_style_tag;
                                }
                            } else {
                                $return_string .= 'style="width:auto;min-width:100px; ' . $inline_css_without_style . '"';
                            }

                            

                            $return_string .= ' data-size="15" data-default-val="'.$arfdefault_selected_val.'" ' . $arf_on_change_function . ' '.$arf_cookie_field_arr_attr.' >';

                            $count_i = 0;
                            if (!empty($field['options'])) {
                                foreach ($field['options'] as $opt_key => $opt) {

                                    $field_val = apply_filters('arfdisplaysavedfieldvalue', $opt, $opt_key, $field);
                                    
                                    $opt = apply_filters('show_field_label', $opt, $opt_key, $field);

                                    if (is_array($opt)) {
                                        $opt = $opt['label'];
                                        if ($field_val['value'] == '(Blank)'){
                                            $field_val['value'] = "";
                                        }    
                                        $field_val = (isset($field['separate_value'])) ? $field_val['value'] : $opt;
                                    }
                                    if ($count_i == 0 and $opt == '')
                                        $opt = esc_html__('Please select', 'ARForms');

                                    $field['value'] = isset($field['value']) ? $field['value'] : "";
                                    $arfdefault_selected_val = (isset($field['separate_value'])) ? $field['default_value'] : $field['value'];
                                    if (isset($field['set_field_value'])) {
                                        $arfdefault_selected_val = $field['set_field_value'];
                                    }


                                    $return_string .= '<option value="' . esc_attr($field_val) . '" ';
                                    if ($armainhelper->check_selected($arfdefault_selected_val, $field_val)) {
                                        $return_string .= 'selected="selected" ';
                                    }

                                    $return_string .=' data-content="' . esc_attr($opt) . '">' . $opt . '</option>';

                                    $count_i++;
                                }
                            }
                            $return_string .= '</select>';
                            $return_string .= $field_standard_tooltip;
                        }
                        $return_string .= $field_description;
                        $return_string .= '</div>';
                        $return_string .='</div>';
                        break;
                    case 'file':
                        $return_string .= '<div id="arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container" class="arfformfield control-group arfmainformfield ' . $required_class . ' ' . $class_position . ' ' . $error_class . ' arf_field_' . $field['id'] . '"  ' . $field_style . ' data-field-type="'.$field['type'].'">';
                        $return_string .=$arf_main_label;
                        $return_string .='<div class="controls'.$field_tooltip_class.'" ' . $field_width . ' '.$field_tooltip.' >';
                        $file_extention = get_allowed_mime_types();
                        $file_ext = '';
                        $file_ext_new = '';
                        $field_types = array();
                        if (!empty($field['ftypes']) && (isset($field['restrict']) && $field['restrict'] == '1')) {
                            $index = 0;

                            foreach ($field['ftypes'] as $key => $value) {
                                if ($value != '0') {
                                    $field_types[$index] = $value;
                                    $index++;
                                }
                            }
                            
                            $i = 0;
                            foreach ($field_types as $field_type) {
                                if ($i == 0)
                                    $ftype = $field_type;
                                else
                                    $ftype = $ftype . "," . $field_type;
                                $i++;

                                foreach ($file_extention as $ext => $file_type_name) {
                                    if ($file_type_name == $field_type) {
                                        $file_ext .= $ext . ', ';
                                        if (strpos($ext, '|') !== false) {
                                            $ext = explode('|', $ext);
                                            foreach ($ext as $ext) {
                                                $file_ext_new .= '.' . $ext . ', ';
                                            }
                                        } else {
                                            $file_ext_new .= '.' . $ext . ', ';
                                        }
                                    }
                                }
                            }
                        } else {
                            $field_types = get_allowed_mime_types();
                            $i = 0;
                            foreach ($field_types as $field_type) {
                                if ($field_type != 'application/x-msdownload') {
                                    if ($i == 0)
                                        $ftype = $field_type;
                                    else
                                        $ftype = $ftype . "," . $field_type;
                                    $i++;

                                    foreach ($file_extention as $ext => $file_type_name) {
                                        if ($file_type_name == $field_type) {
                                            $file_ext .= $ext . ', ';
                                            if (strpos($ext, '|') !== false) {
                                                $ext = explode('|', $ext);
                                                foreach ($ext as $ext) {
                                                    $file_ext_new .= '.' . $ext . ', ';
                                                }
                                            } else {
                                                $file_ext_new .= '.' . $ext . ', ';
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $file_size = ($field['max_fileuploading_size'] == "auto") ? "auto" : $field['max_fileuploading_size'] * 1048576;
                        if (isset($field['field_width']) and $field['field_width'] != '') {
                            $file_field_width = $field['field_width'] . 'px';
                        }
                        if($arr['field_font_size'] >= '26' ){
                                                
                            $arf_fileupload_icon_size = 'width="20" height="20"' ;

                        } elseif ($arr['field_font_size'] <= '14'){ 
                                                
                            $arf_fileupload_icon_size = 'width="10" height="10"';

                        } else {

                            $arf_fileupload_icon_size = 'width="14" height="14"';
                        }
                        if ($arfsettings->form_submit_type == 1) {

                            if (apply_filters('arf_check_for_draw_outside', false, $field)) {
                                
                                $return_string = apply_filters('arf_drawthisfieldfromoutside', $return_string, $field,$arf_on_change_function,$arf_data_uniq_id);
                            } else {
                                $browser_info = $arrecordcontroller->getBrowser($_SERVER['HTTP_USER_AGENT']);

                                $browser_check = 1;

                                if (isset($browser_info) and $browser_info != "") {
                                    if ($browser_info['name'] == 'Internet Explorer' || $browser_info['name'] == 'Apple Safari') {
                                        if ($browser_info['name'] == 'Apple Safari') {
                                            $browser_check = 0;
                                        } elseif ($browser_info['name'] == 'Internet Explorer' && $browser_info['version'] <= '9') {
                                            $browser_check = 0;
                                        }
                                    }
                                }
                                if ($field['arf_draggable'] != '' && $field['arf_draggable'] == 1 && $browser_check == 1) {

                                    $return_string .= '<div class="file_main_control" style="display:inline-block; ';
                                    if (isset($field['field_width']) and $field['field_width'] != '') {
                                        $return_string .= "width:" . $field['field_width'] . 'px';
                                    }
                                    $return_string .='">';

                                    $return_string .='<div class="arf_file_field" style="display:inline-block;">';

                                    $return_string .='<div id="arf_reply_drag_and_drop" class="arf_reply_uploader_container">';
                                    $return_string .='<label for="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" class="arfajax-file-upload-drag arf_reply_drag_file_label label_' . $field['field_key'] . '_' . $arf_data_uniq_id . '">';
                                    $return_string .= '<span id="arf_file_drag_reply" class="arf_file_drag_reply_container" data-id="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '">';
                                    if (isset($field['arf_dragable_label'])) {
                                        $return_string .= $field['arf_dragable_label'];
                                    } else {
                                        $return_string .= addslashes(esc_html__('Drop files here or click to select', 'ARForms'));
                                    }
                                    $return_string .='</span>';
                                    $return_string .='</label>';

                                    $return_string .='<input class="arf_reply_drag_file" type="file"  data-max-file-upload-size="'.@ini_get('upload_max_filesize').'" ';
                                    if (isset($field['required']) and $field['required']) {
                                        $return_string .= 'data-validation-required-message="' . esc_attr($field['blank']) . '"';
                                    }
                                    $return_string .=' name="file' . $field['id'] . '" id="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" data-form-data-id="' . $arf_data_uniq_id . '" data-form-id="' . $field['form_id'] . '" data-invalid-message="' . esc_attr($field['invalid']) . '" data-is-insecure-file="false" data-insecure-file-message="'.esc_html__("The file could not be uploaded due to security reason as it contains malicious code","ARForms").'" data-size-invalid-message="' . esc_attr($field['invalid_file_size']) . '" data-file-valid="true" style="visibility: hidden;float: left;margin-top: -50px;padding: 0;width: 0 ;"  accept="' . $file_ext_new . '"';
                                    if ($field['arf_is_multiple_file']) {
                                        $return_string .=' multiple="multiple" ';
                                    }
                                    $return_string .='>';
                                    $return_string .='<input type="hidden" id="type_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" name="type_' . $field['field_key'] . '" value="0" >';
                                    $return_string .='<input type="hidden" value="' . $file_ext . '" id="file_types_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" name="field_types_' . $field['field_key'] . '" />';
                                    $return_string .='<input type="hidden" value="' . $file_size . '" id="file_size_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" name="file_size_' . $field['field_key'] . '" >';
                                    
                                    $return_string .='</div>';
                                    $return_string .='<div id="progress_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" class="arfprogress progress-striped active" style="margin-top: 120px;">';
                                    $return_string .='<div class="bar" style="width:0%;"></div>';
                                    $return_string .='</div>';
                                    $return_string .='<div id="info_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" class="arf_info" style="display:none;">';
                                    $return_string .='<span class="percent">% ' . addslashes(esc_html__('Completed', 'ARForms')) . '</span>';
                                    $return_string .='<span id="percent" class="percent">0</span>';
                                    $return_string .='</div>';
                                    $return_string .='</div>';
                                    $return_string .='<div id="arf_multi_file_uploader_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" class="arf_multi_file_info_container arf_file_field" style="display:none; margin-top: 0px;display:inline-block;"></div>';
                                    $return_string .='<div id="arf_multi_file_info_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" class="arf_multi_file_info_container arf_file_field" style="display:none; margin-top: 0px;"></div>';
                                    $return_string .='</div>';
                                } else {
                                    $return_string .='<div class="file_main_control" style="display:inline-block; ';
                                    if (isset($field['field_width']) and $field['field_width'] != '') {
                                        $return_string .='width:' . $field['field_width'] . 'px';
                                    }
                                    $return_string .='">';
                                    $return_string .='<div class="arf_file_field" style="display:inline-block;">';
                                    $return_string .= '<div class="';
                                    if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '9') {
                                        $return_string .= ' original_btn ';
                                    }
                                    

                                    $return_string .=' arfajax-file-upload" id="div_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" data-id="' . $field['id'] . '" data-form-data-id="' . $arf_data_uniq_id . '" data-form-id="' . $field['form_id'] . '" style="position: relative; overflow: hidden; cursor:pointer;">';
                                    $return_string .= '<div class="arfajax-file-upload-img" style="float:left;"><svg '.$arf_fileupload_icon_size.' viewBox="0 0 100 100"><path xmlns="http://www.w3.org/2000/svg" d="M77.656,56.25c2.396,0,4.531-0.625,6.406-1.875c1.822-1.303,3.385-2.865,4.688-4.688c1.25-1.875,1.875-4.037,1.875-6.484  s-1.275-5-3.828-7.656l-6.328-6.484c-6.719-6.927-13.49-13.646-20.312-20.156L50-0.781L39.844,8.906  c-6.823,6.51-13.594,13.229-20.312,20.156l-6.719,6.953c-2.292,2.344-3.438,4.609-3.438,6.797v0.781  c0,1.667,0.208,3.021,0.625,4.062s1.042,2.083,1.875,3.125c0.885,1.094,1.875,2.084,2.969,2.969  c1.042,0.834,2.083,1.459,3.125,1.875s2.344,0.625,3.906,0.625s2.865-0.209,3.906-0.625c1.094-0.469,2.24-1.197,3.438-2.188  c1.25-0.99,2.682-2.344,4.297-4.062l3.984-4.141v41.562c0,2.553,0.417,4.557,1.25,6.016c0.885,1.459,1.719,2.604,2.5,3.438  c0.833,0.781,1.979,1.615,3.438,2.5C46.146,99.584,47.917,100,50,100c2.084,0,3.854-0.416,5.312-1.25  c1.459-0.885,2.604-1.719,3.438-2.5c0.781-0.834,1.615-1.979,2.5-3.438c0.834-1.459,1.25-3.463,1.25-6.016V45.859l7.422,6.719  C72.631,55.025,75.209,56.25,77.656,56.25"/></svg></div>&nbsp;' . $field['file_upload_text'];
                                    if ($browser_info['name'] == 'Internet Explorer' and $browser_info['version'] <= '9') {
                                        if ($field['arf_is_multiple_file']) {
                                            $return_string .= '<div id="' . $field['field_key'] . '_' . $arf_data_uniq_id . '_iframe_div"><iframe id="' . $field['field_key'] . '_' . $arf_data_uniq_id . '_iframe" src="' . ARFURL . '/core/views/iframe.php?multiple=true"></iframe></div>';
                                        } else {
                                            $return_string .= '<div id="' . $field['field_key'] . '_' . $arf_data_uniq_id . '_iframe_div"><iframe id="' . $field['field_key'] . '_' . $arf_data_uniq_id . '_iframe" src="' . ARFURL . '/core/views/iframe.php"></iframe></div>';
                                        }
                                        $return_string .='<input type="text" ';
                                        if (isset($field['required']) and $field['required']) {
                                            $return_string .= 'data-validation-required-message="' . esc_attr($field['blank']) . '"';
                                        }
                                        $return_string .=' class="original" data-max-file-upload-size="'.@ini_get('upload_max_filesize').'" name="file' . $field['id'] . '" id="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" ';

                                        $return_string .= $arf_input_field_html;

                                        $return_string .= ' data-is-insecure-file="false" data-insecure-file-message="'.esc_html__("The file could not be uploaded due to security reason as it contains malicious code","ARForms").'" data-form-data-id="' . $arf_data_uniq_id . '" data-form-id="' . $field['form_id'] . '" data-file-valid="true" data-invalid-message="' . esc_attr($field['invalid']) . '" data-size-invalid-message="' . esc_attr($field['invalid_file_size']) . '" style="position: absolute; cursor: pointer; top: 0px; width: 100%; height:100%; left: -999px; z-index: 100; opacity: 0; filter:alpha(opacity=0);" />';
                                        $return_string .= '<input type="hidden" id="type_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" name="type_' . $field['field_key'] . '" value="1" >';
                                        $return_string .= '<input type="hidden" value="' . $file_ext . '" id="file_types_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" name="field_types_' . $field['field_key'] . '" />';
                                        $return_string .= '<input type="hidden" value="' . $file_size . '" id="file_size_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" name="file_size_' . $field['field_key'] . '" >';
                                        
                                    } else {
                                        $return_string .= '<input type="file" ';
                                        if (isset($field['required']) and $field['required']) {
                                            $return_string .= 'data-validation-required-message="' . esc_attr($field['blank']) . '"';
                                        }
                                        $return_string .=' class="file original" data-max-file-upload-size="'.@ini_get('upload_max_filesize').'" name="file' . $field['id'] . '" data-is-insecure-file="false" data-insecure-file-message="'.esc_html__("The file could not be uploaded due to security reason as it contains malicious code","ARForms").'" id="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" data-invalid-message="' . esc_attr($field['invalid']) . '" data-size-invalid-message="' . esc_attr($field['invalid_file_size']) . '" data-form-data-id="' . $arf_data_uniq_id . '" data-form-id="' . $field['form_id'] . '" data-file-valid="true" style="position: absolute; cursor: pointer; top: 0px; padding:0; margin:0; height:100%; outline:none; right:0; z-index: 100; opacity: 0; width:100%"  accept="' . $file_ext_new . '"';
                                        if ($field['arf_is_multiple_file']) {
                                            $return_string .=' multiple="multiple" ';
                                        }
                                        $return_string .=' />';
                                        $return_string .= '<input type="hidden" id="type_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" name="type_' . $field['field_key'] . '" value="0" >';
                                        $return_string .='<input type="hidden" value="' . $file_ext . '" id="file_types_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" name="field_types_' . $field['field_key'] . '" />';
                                        $return_string .='<input type="hidden" value="' . $file_size . '" id="file_size_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" name="file_size_' . $field['field_key'] . '" >';
                                        
                                    }
                                    $return_string .='</div>';


                                    $return_string .='<div id="progress_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" class="arfprogress progress-striped active">';

                                    $return_string .= '<div class="bar" style="width:0%;"></div>';
                                    $return_string .='</div>';

                                    $return_string .= '<div id="info_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" class="arf_info" style="display:none;">';

                                    $return_string .='<span class="percent">% ' . addslashes(esc_html__('Completed', 'ARForms')) . '</span>';
                                    $return_string .='<span id="percent" class="percent">0</span>';
                                    $return_string .='</div>';
                                    $return_string .='</div>';
                                    $return_string .='<div id="arf_multi_file_uploader_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" class="arf_multi_file_info_container arf_file_field" style="display:none; margin-top: 0px;"></div>';
                                    $return_string .='<div id="arf_multi_file_info_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" class="arf_multi_file_info_container arf_file_field" style="display:none;"></div>';
                                    $return_string .='</div>';
                                }
                                $return_string .= $field_standard_tooltip;
                            }
                            $return_string .= $field_description;
                        } else {
                            if (apply_filters('arf_check_for_draw_outside', false, $field)) {
                                
                                $return_string = apply_filters('arf_drawthisfieldfromoutside', $return_string, $field,$arf_on_change_function,$arf_data_uniq_id);
                            } else {
                                $return_string .='<div class="file_main_control" style="display:inline-block; ';
                                if (isset($field['field_width']) and $field['field_width'] != '') {
                                    $return_string .='width: ' . $field['field_width'] . 'px';
                                }
                                $return_string .='">';
                                $return_string .='<div class="arf_file_field">';
                                $return_string .='<div class="arfajax-file-upload" id="divi_' . $field['field_key'] . '" style="position: relative; overflow: hidden; float:left; cursor: pointer;"><div class="arfajax-file-upload-img"><svg '.$arf_fileupload_icon_size.' viewBox="0 0 100 100" ><path xmlns="http://www.w3.org/2000/svg" d="M77.656,56.25c2.396,0,4.531-0.625,6.406-1.875c1.822-1.303,3.385-2.865,4.688-4.688c1.25-1.875,1.875-4.037,1.875-6.484  s-1.275-5-3.828-7.656l-6.328-6.484c-6.719-6.927-13.49-13.646-20.312-20.156L50-0.781L39.844,8.906  c-6.823,6.51-13.594,13.229-20.312,20.156l-6.719,6.953c-2.292,2.344-3.438,4.609-3.438,6.797v0.781  c0,1.667,0.208,3.021,0.625,4.062s1.042,2.083,1.875,3.125c0.885,1.094,1.875,2.084,2.969,2.969  c1.042,0.834,2.083,1.459,3.125,1.875s2.344,0.625,3.906,0.625s2.865-0.209,3.906-0.625c1.094-0.469,2.24-1.197,3.438-2.188  c1.25-0.99,2.682-2.344,4.297-4.062l3.984-4.141v41.562c0,2.553,0.417,4.557,1.25,6.016c0.885,1.459,1.719,2.604,2.5,3.438  c0.833,0.781,1.979,1.615,3.438,2.5C46.146,99.584,47.917,100,50,100c2.084,0,3.854-0.416,5.312-1.25  c1.459-0.885,2.604-1.719,3.438-2.5c0.781-0.834,1.615-1.979,2.5-3.438c0.834-1.459,1.25-3.463,1.25-6.016V45.859l7.422,6.719  C72.631,55.025,75.209,56.25,77.656,56.25"/></svg></div>&nbsp;' . $field['file_upload_text'] . '<input type="file" ';
                                if (isset($field['required']) and $field['required']) {
                                    $return_string .= 'data-validation-required-message="' . esc_attr($field['blank']) . '"';
                                }
                                $return_string .= ' class="original_normal" name="file' . $field['id'] . '[]" id="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" data-invalid-message="' . esc_attr($field['invalid']) . '" data-size-invalid-message="' . esc_attr($field['invalid_file_size']) . '" data-form-data-id="' . $arf_data_uniq_id . '" data-form-id="' . $field['form_id'] . '" data-file-valid="true" style="position: absolute; cursor: pointer; top: 0px; padding:0; margin:0; height:100%; width:100%; right:0; z-index: 100; opacity: 0; filter:alpha(opacity=0);"    accept="' . $file_ext_new . '"';
                                if ($field['arf_is_multiple_file']) {
                                    $return_string .=' multiple="multiple" ';
                                }
                                $return_string .=' />';
                                $browser_info = $arrecordcontroller->getBrowser($_SERVER['HTTP_USER_AGENT']);
                                $return_string .='<input type="hidden" value="' . $file_ext . '" id="file_types_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" name="field_types_' . $field['field_key'] . '"  />';
                                $return_string .='<input type="hidden" value="' . $file_size . '" id="file_size_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" name="file_size_' . $field['field_key'] . '" >';
                                
                                $return_string .='</div>';
                                $return_string .='<div id="file_name_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" class="file_name_info">' . addslashes(esc_html__('No file selected', 'ARForms')) . '</div>';
                                $return_string .='</div>';
                                $return_string .='</div>';

                                $return_string .= $field_standard_tooltip;
                            }
                            $return_string .= $field_description;
                        }
                        


                        $return_string .= $arfieldhelper->get_file_icon(@$field['value']);

                        $return_string .='</div>';
                        $return_string .='</div>';
                        break;
                    case 'number':
                        $return_string .= '<div id="arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container" class="arfformfield control-group arfmainformfield '.$material_input_cls.' ' . $required_class . ' ' . $class_position . ' ' . $error_class . ' arf_field_' . $field['id'] . '"  ' . $field_style . '  data-field-type="'.$field['type'].'">';

                        if( $inputStyle != 'material' ){
                            $return_string .= $arf_main_label;
                        }

                        $return_string .='<div class="controls'.$field_tooltip_class.'" ' . $field_width . ' '.$field_tooltip.' >';
                        if( $inputStyle == 'material' ){
                            $return_string .= $arf_main_label;
                        }
                        if (apply_filters('arf_check_for_draw_outside', false, $field)) {
                            
                            $return_string = apply_filters('arf_drawthisfieldfromoutside', $return_string, $field,$arf_on_change_function,$arf_data_uniq_id);
                        } else {

                            $return_string .=$prefix;
                            $num_field_type='text';
                            if ( wp_is_mobile() && strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false) {
                                $num_field_type='number';
                            }    
                            $return_string .='<input type="'.$num_field_type.'" class="arf_number_field" id="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" dir=" ';

                            if (isset($field['text_direction']) && $field['text_direction'] == '0') {
                                $return_string .= 'rtl';
                            } else {
                                $return_string .= 'ltr';
                            }
                            $return_string .='"';
                            $return_string .= 'name="' . $field_name . '" ';

                            if(isset($field['arf_enable_readonly']) && $field['arf_enable_readonly'] == 1){
                                $return_string .='readonly="readonly" ';    
                            }

                            $default_value = $field['default_value'];

                            if(isset($arf_arr_preset_data) && count($arf_arr_preset_data) > 0 && isset($arf_arr_preset_data[$field['id']])){                         
                                $default_value = $arf_arr_preset_data[$field['id']];
                            }

                            $default_value = apply_filters('arf_replace_default_value_shortcode',$default_value,$field,$form);

                            if( isset($field['set_field_value']) && $field['set_field_value'] != '' ){
                                $default_value = $field['set_field_value'];
                            }

                            if( $default_value != '' ){
                                $return_string .= " value='{$default_value}'";
                            }

                            if( isset($field['placeholdertext']) && $field['placeholdertext'] != '' ){
                                $return_string .= ' placeholder="'.$field['placeholdertext'].'" ';
                            }

                            $return_string .= $arf_input_field_html;

                            if (isset($field['field_width']) and $field['field_width'] != '' and ( $field['enable_arf_prefix'] != 1 || $field['enable_arf_suffix'] != 1 )) {

                                $return_string .= 'style="width:' . $field['field_width'] . 'px !important; ' . $inline_css_without_style . '"';
                            } else {
                                $return_string .= $inline_css_with_style_tag;
                            }


                            if (isset($field['required']) and $field['required']) {
                                $return_string .= 'data-validation-required-message="' . esc_attr($field['blank']) . '"';
                            }
                            
                            if ($field['type'] == 'number' && $field["maxnum"] != "" && $field["maxnum"] > 0) {
                                $return_string .= ' data-validation-max-message="' . esc_attr($field['invalid']) . '" ';
                            }

                            if( $field['minnum'] != '' ){
                                $return_string .= ' min="'.$field['minnum'].'" ';
                            }

                            if( $field['maxnum'] != '' ){
                                $return_string .= ' max="'.$field['maxnum'].'" ';
                            }

                            $return_string .= ' onkeydown="arfvalidatenumber(this, event);" ';
                            if ($field['minlength'] != '') {
                                $return_string .= ' minlength="' . $field['minlength'] . '" data-validation-minlength-message="' . esc_attr($field['minlength_message']) . '" ';
                            }
                            $return_string .= $arf_on_change_function;

                            /*if (wp_is_mobile()) {
                                if(isset($inputmask) && $inputmask!=''){
                                    $return_string .= ' data-mask-input="' . $inputmask . '" data-ismask="true"';
                                }
                            }*/

                            $return_string .= '/>';
                            $return_string .= $suffix;

                            $return_string .= $field_standard_tooltip;
                        }
                        $return_string .= $field_description;
                        $return_string .='</div>';
                        $return_string .='</div>';
                        break;
                    case 'phone':
                    case 'tel':
                        $return_string .= '<div id="arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container" class="arfformfield control-group arfmainformfield ' . $required_class . ' ' . $class_position . ' ' . $error_class . ' arf_field_' . $field['id'] . '"  ' . $field_style . '  data-field-type="'.$field['type'].'">';
                        if( $inputStyle != 'material' ){
                            $return_string .= $arf_main_label;
                        }
                        $return_string .='<div class="controls'.$field_tooltip_class.'" ' . $field_width . ' '.$field_tooltip.' >';
                        if( $inputStyle == 'material' ){
                            $return_string .= $arf_main_label;
                        }
                        if (apply_filters('arf_check_for_draw_outside', false, $field)) {
                            
                            $return_string = apply_filters('arf_drawthisfieldfromoutside', $return_string, $field,$arf_on_change_function,$arf_data_uniq_id);
                        } else {
                            $return_string .=$prefix;
                            $return_string .='<input type="text"  id="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '"';
                            $return_string .= 'name="' . $field_name . '"';

                            if( isset($field['placeholdertext']) && $field['placeholdertext'] != '' ){
                                $return_string .= ' placeholder="'.$field['placeholdertext'].'" ';
                            }

                            $return_string .= $arfieldcontroller->input_fieldhtml($field, false);
                            $phone_flag = false;

                            if( isset($field['phonetype']) && $field['phonetype'] == 1 ){
                                $phtypes = array();
                                foreach( $field['phtypes'] as $key => $vphtype ){
                                    if( $vphtype != 0 ){
                                        array_push($phtypes, strtolower(str_replace('phtypes_','',$key)) );
                                    }
                                }

                                $return_string .= ' data-defaultCountryCode="'.$phtypes[0].'" ';

                                if( isset($field['country_validation']) && $field['country_validation'] == 1 ){
                                    $return_string .= ' data-do-validation="true" ';
                                    $return_string .= 'data-invalid-format-message="' . esc_attr($field['invalid']) . '"';
                                } else {
                                    $return_string .= ' data-do-validation="false" ';
                                }
                                $phone_flag = true;
                            }

                            if (isset($field['required']) and $field['required']) {
                                $return_string .= 'data-validation-required-message="' . esc_attr($field['blank']) . '"';
                            }

                            if( !$phone_flag ){
                                if ($field['phone_validation'] == 'international') {
                                    $return_string .= 'data-validation-number-message="' . esc_attr($field['invalid']) . '"';
                                    $phone_regex = "";
                                    $inputmask = "";
                                } else {
                                    if ($field['phone_validation'] == 'custom_validation_1') {
                                        $phone_regex = "^[(]{1}[0-9]{3,4}[)]{1}[0-9]{3}[\s]{1,1}[0-9]{4}$";
                                        $inputmask = "(999)999 9999";
                                    } else if ($field['phone_validation'] == 'custom_validation_2') {
                                        $phone_regex = "^[(]{1}[0-9]{3,4}[)]{1}[\s]{1}[0-9]{3}[\s]{1}[0-9]{4}$";
                                        $inputmask = "(999) 999 9999";
                                    } else if ($field['phone_validation'] == 'custom_validation_3') {
                                        $phone_regex = "^[(]{1}[0-9]{3,4}[)]{1}[0-9]{3}[-]{1}[0-9]{4}$";
                                        $inputmask = "(999)999-9999";
                                    } else if ($field['phone_validation'] == 'custom_validation_4') {
                                        $phone_regex = "^[(]{1}[0-9]{3,4}[)]{1}[\s]{1}[0-9]{3}[-]{1}[0-9]{4}$";
                                        $inputmask = "(999) 999-9999";
                                    } else if ($field['phone_validation'] == 'custom_validation_5') {
                                        $phone_regex = "^[0-9]{3,4}[\s]{1}[0-9]{3}[\s]{1}[0-9]{4}$";
                                        $inputmask = "999 999 9999";
                                    } else if ($field['phone_validation'] == 'custom_validation_6') {
                                        $phone_regex = "^[0-9]{3,4}[\s]{1}[0-9]{3}[-]{1}[0-9]{4}$";
                                        $inputmask = "999 999-9999";
                                    } else if ($field['phone_validation'] == 'custom_validation_7') {
                                        $phone_regex = "^[0-9]{3,4}[-]{1}[0-9]{3}[-]{1}[0-9]{4}$";
                                        $inputmask = "999-999-9999";
                                    } else if ($field['phone_validation'] == 'custom_validation_8') {
                                        $phone_regex = "^[0-9]{4,5}[\s]{1}[0-9]{3}[\s]{1}[0-9]{3}$";
                                        $inputmask = "99999 999 999";
                                    } else if ($field['phone_validation'] == 'custom_validation_9') {
                                        $phone_regex = "^[0-9]{4,5}[\s]{1}[0-9]{6}$";
                                        $inputmask = "99999 999999";
                                    }
                                    $return_string .= ' data-validation-regex-regex="' . @$phone_regex . '"';
                                    $return_string .= ' data-mask="' . @$inputmask . '"';
                                    $return_string .= ' data-validation-regex-message="' . esc_attr($field['invalid']) . '"';
                                }
                            }

                            
                            $return_string .= $arf_on_change_function;
                            if (wp_is_mobile()) {
                                if(isset($inputmask) && $inputmask!=''){
                                    $return_string .= ' data-mask-input="' . $inputmask . '"';
                                    $return_string .= ' data-ismask="true" ';
                                }
                            }
                            if(isset($field['arf_enable_readonly']) && $field['arf_enable_readonly'] == 1){
                                $return_string .='readonly="readonly" ';    
                            }
                            
                            $default_value = $field['default_value'];

                            if(isset($arf_arr_preset_data) && count($arf_arr_preset_data) > 0 && isset($arf_arr_preset_data[$field['id']])){                         
                                $default_value = $arf_arr_preset_data[$field['id']];
                                
                            }

                            $default_value = apply_filters('arf_replace_default_value_shortcode',$default_value,$field,$form);

                            if( isset($field['set_field_value']) && $field['set_field_value'] != '' ){
                                $default_value = $field['set_field_value'];
                            }

                            if( $default_value != '' ){
                                $return_string .= " value='{$default_value}'";
                            }

                            $return_string .= '/>';
                            $return_string .=$suffix;
                            $return_string .= $field_standard_tooltip;
                        }

                        $return_string .= $field_description;
                        $return_string .='</div>';
                        if( isset($field['phonetype']) && $field['phonetype'] == 1 ){

                            if( isset($phtypes) && count($phtypes) > 0 ){
                                $return_string .= "<input type='hidden' data-jqvalidate='false' id='field_".$field['field_key']."_country_list' value='".json_encode($phtypes)."' />";
                            }

                            $return_string .= "<input type='hidden' data-jqvalidate='false' name='item_meta[".$field['id']."_country_code]' id='field_".$field['field_key']."_".$arf_data_uniq_id."_country_code' />";

                            $default_country = isset($field['default_country']) ? $field['default_country'] : '';
                            $arf_default_country = '';
                            if( $default_country != '' && in_array($default_country, $phtypes) ){
                                $arf_default_country = $default_country;
                            }

                            $return_string .= "<input type='hidden' data-jqvalidate='false' id='field_".$field['field_key']."_".$arf_data_uniq_id."_default_country' value='".$arf_default_country."' />";
                        }
                        $return_string .='</div>';
                        break;
                    case 'url':
                        $return_string .= '<div id="arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container" class="arfformfield control-group arfmainformfield ' . $required_class . ' ' . $class_position . '' . $error_class . ' arf_field_' . $field['id'] . '"  ' . $field_style . '  data-field-type="'.$field['type'].'">';
                        if( $inputStyle != 'material' ){
                            $return_string .= $arf_main_label;
                        }

                        $return_string .='<div class="controls'.$field_tooltip_class.'" ' . $field_width . ' '.$field_tooltip.' >';
                        if( $inputStyle == 'material' ){
                            $return_string .= $arf_main_label;
                        }
                        if (apply_filters('arf_check_for_draw_outside', false, $field)) {
                            
                            $return_string = apply_filters('arf_drawthisfieldfromoutside', $return_string, $field,$arf_on_change_function,$arf_data_uniq_id);
                        } else {

                            $regex = "((https?|ftp)\:\/\/)?";
                            $regex .= "((HTTPS?|ftp)\:\/\/)?";
                            $regex .= "([A-Za-z0-9+!*(),;?&=\$_.-]+(\:[A-Za-z0-9+!*(),;?&=\$_.-]+)?@)?";
                            $regex .= "([A-Za-z0-9-.]*)\.([A-Za-z]+)";
                            $regex .= "(\:[0-9]{2,5})?";
                            $regex .= "(\/([A-Za-z0-9+\$_-]\.?)+)*\/?";
                            $regex .= "(\?[A-Za-z+&\$_.-][A-Za-z0-9;:@&%=+\/\$_.-]*)?";
                            $regex .= "(#[A-Za-z_.-][A-Za-z0-9+\$_.-]*)?";

                            $return_string .=$prefix;

                            $return_string .='<input type="url" id="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" ';

                            $return_string .= 'name="' . $field_name . '" ';
                            
                            $default_value = $field['default_value'];

                            if(isset($arf_arr_preset_data) && count($arf_arr_preset_data) > 0 && isset($arf_arr_preset_data[$field['id']])){

                                $default_value = $arf_arr_preset_data[$field['id']];
                                
                            }

                            $default_value = apply_filters('arf_replace_default_value_shortcode',$default_value,$field,$form);

                            if( isset($field['set_field_value']) && $field['set_field_value'] != '' ){
                                $default_value = $field['set_field_value'];
                            }
                            if(isset($field['arf_enable_readonly']) && $field['arf_enable_readonly'] == 1){
                                $return_string .='readonly="readonly" ';    
                            }

                            if( $default_value != '' ){
                                $return_string .= " value='{$default_value}'";
                            }

                            if( isset($field['placeholdertext']) && $field['placeholdertext'] != '' ){
                                $return_string .= ' placeholder="'.$field['placeholdertext'].'" ';
                            }

                            if( isset($field['clear_on_focus']) && $field['clear_on_focus'] ){
                                $return_string .= ' onfocus="arfcleardedaultvalueonfocus(\''.$field['placeholdertext'].'\',this,\''.$is_default_blank.'\')"';
                                $return_string .= ' onblur="arfreplacededaultvalueonfocus(\''.$field['placeholdertext'].'\',this,\''.$is_default_blank.'\')"';
                            }


                            $return_string .= $arf_input_field_html;

                            if (isset($field['field_width']) and $field['field_width'] != '' and ( $field['enable_arf_prefix'] != 1 || $field['enable_arf_suffix'] != 1 )) {

                                $return_string .= 'style="width:' . $field['field_width'] . 'px !important; ' . $inline_css_without_style . '"';
                            } else {
                                $return_string .= $inline_css_with_style_tag;
                            }

                            if (isset($field['required']) and $field['required']) {
                                $return_string .= ' data-validation-required-message="' . esc_attr($field['blank']) . '"';
                            }
                            $return_string .= ' data-validation-regex-regex="' . $regex . '" data-validation-regex-message="' . esc_attr($field['invalid']) . '" ';

                            $return_string .= $arf_on_change_function;

                            /*if (wp_is_mobile()) {
                                if(isset($inputmask) && $inputmask!=''){
                                    $return_string .= ' data-mask-input="' . $inputmask . '" data-ismask="true"';
                                }
                            }*/


                            $return_string .= '/>';
                            $return_string .=$suffix;
                            
                            $return_string .= $field_standard_tooltip;
                        }
                        $return_string .= $field_description;
                        $return_string .='</div>';
                        $return_string .='</div>';
                        break;
                    case 'date':
                        $return_string .= '<div id="arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container" class="arfformfield control-group arfmainformfield ' . $required_class . ' ' . $class_position . ' ' . $error_class . ' arf_field_' . $field['id'] . '"  ' . $field_style . '  data-field-type="'.$field['type'].'">';

                        if( $inputStyle != 'material' ){
                            $return_string .= $arf_main_label;
                        }

                        $return_string .='<div class="controls arf_date_main_controls '.$field_tooltip_class.'" ' . $field_width . ' '.$field_tooltip.' >';
                        if( $inputStyle == 'material' ){
                            $return_string .= $arf_main_label;
                        }
                        if (apply_filters('arf_check_for_draw_outside', false, $field)) {
                            
                            $return_string = apply_filters('arf_drawthisfieldfromoutside', $return_string, $field,$arf_on_change_function,$arf_data_uniq_id);
                        } else {

                            $return_string .=$prefix;

                            $wp_format_date = get_option('date_format');
                            if ($wp_format_date == 'F j, Y' || $wp_format_date == 'm/d/Y') {
                                if ($field['arfnewdateformat'] == 'MMMM D, YYYY') {
                                    $defaultdate_format = 'F d, Y';
                                } elseif ($field['arfnewdateformat'] == 'MMM D, YYYY') {
                                    $defaultdate_format = 'M d, Y';
                                } else {
                                    $defaultdate_format = 'm/d/Y';
                                }
                            } else if ($wp_format_date == 'd/m/Y') {
                                if ($field['arfnewdateformat'] == 'D MMMM, YYYY') {
                                    $defaultdate_format = 'd F, Y';
                                } elseif ($field['arfnewdateformat'] == 'D MMM, YYYY') {
                                    $defaultdate_format = 'd M, Y';
                                } else {
                                    $defaultdate_format = 'd/m/Y';
                                }
                            } else if ($wp_format_date == 'Y/m/d') {
                                if ($field['arfnewdateformat'] == 'YYYY, MMMM D') {
                                    $defaultdate_format = 'Y, F d';
                                } elseif ($field['arfnewdateformat'] == 'YYYY, MMM D') {
                                    $defaultdate_format = 'Y, M d';
                                } else {
                                    $defaultdate_format = 'Y/m/d';
                                }
                            } else {
                                if ($field['arfnewdateformat'] == 'MMMM D, YYYY') {
                                    $defaultdate_format = 'F d, Y';
                                } elseif ($field['arfnewdateformat'] == 'MMM D, YYYY') {
                                    $defaultdate_format = 'M d, Y';
                                } elseif ($field['arfnewdateformat'] == 'YYYY/MM/DD') {
                                    $defaultdate_format = 'Y/m/d';
                                } elseif ($field['arfnewdateformat'] == 'MM/DD/YYYY') {
                                    $defaultdate_format = 'm/d/Y';
                                } else {
                                    $defaultdate_format = 'd/m/Y';
                                }
                            }
                            
                            $show_year_month_calendar = "true";

                            if (isset($field['show_year_month_calendar']) && $field['show_year_month_calendar'] < 1) {
                                $show_year_month_calendar = "false";
                            }

                            $show_time_calendar = "true";
                            if (@$field['show_time_calendar'] < 1) {
                                $show_time_calendar = "false";
                            }

                            $arf_show_min_current_date = "true";
                            if (@$field['arf_show_min_current_date'] < 1) {
                                $arf_show_min_current_date = "false";
                            }

                            if ($arf_show_min_current_date == "true") {
                                $field['start_date'] = current_time('d/m/Y');
                            } else {
                                $field['start_date'] = $field['start_date'];
                            }

                            $arf_show_max_current_date = "true";
                            if (@$field['arf_show_max_current_date'] < 1) {
                                $arf_show_max_current_date = "false";
                            }

                            if ($arf_show_max_current_date == "true") {
                                $field['end_date'] = current_time('d/m/Y');
                            } else {
                                $field['end_date'] = $field['end_date'];
                            }

                            $date = new DateTime();

                            
                            if( $field['end_date'] == '' ){
                                $field['end_date'] = "31/12/2050";
                            }

                            if( $field['start_date'] == '' ){
                                $field['start_date'] = "01/01/1950";
                            }

                            $end_date_temp = explode("/", $field['end_date']);
                            $date->setDate($end_date_temp[2], $end_date_temp[1], $end_date_temp[0]);
                                $date1 = new DateTime();
                                $start_date_temp = explode("/", $field['start_date']);
                                $date1->setDate($start_date_temp[2], $start_date_temp[1], $start_date_temp[0]);
                            

                            if ($newarr['date_format'] == 'MM/DD/YYYY' || $newarr['date_format'] == 'MMMM D, YYYY' || $newarr['date_format'] == 'MMM D, YYYY') {
                                $start_date = $date1->format("m/d/Y");
                                $end_date = $date->format("m/d/Y");
                                $date_new_format = 'MM/DD/YYYY';
                            } elseif ($newarr['date_format'] == 'DD/MM/YYYY' || $newarr['date_format'] == 'D MMMM, YYYY' || $newarr['date_format'] == 'D MMM, YYYY') {
                                $start_date = $date1->format("d/m/Y");
                                $end_date = $date->format("d/m/Y");
                                $date_new_format = 'DD-MM-YYYY';
                            } elseif ($newarr['date_format'] == 'YYYY/MM/DD' || $newarr['date_format'] == 'YYYY, MMMM D' || $newarr['date_format'] == 'YYYY, MMM D') {
                                $start_date = $date1->format("Y/m/d");
                                $end_date = $date->format("Y/m/d");
                                    $date_new_format = 'YYYY-MM-DD';
                            } else {
                                $start_date = $date1->format("m/d/Y");
                                $end_date = $date->format("m/d/Y");
                                $date_new_format = 'MM/DD/YYYY';
                                $field['date_format'] = 'MMM D, YYYY';
                            }

                            if($newarr['date_format'] == 'MM/DD/YYYY'){
                                $date_new_format_main = 'MM/DD/YYYY';
                            } else if($newarr['date_format'] == 'DD/MM/YYYY') {
                                $date_new_format_main = 'DD/MM/YYYY';
                            } else if($newarr['date_format'] == 'YYYY/MM/DD') {
                                $date_new_format_main = 'YYYY/MM/DD';
                            } else if($newarr['date_format'] == 'MMM D, YYYY') {
                                $date_new_format_main = 'MMM D, YYYY';
                            } else  {
                                $date_new_format_main = 'MMMM D, YYYY';
                            }

                            if (isset($field['clock']) && $field['clock'] == '24') {
                                $format = 'H:mm';
                            } else {
                                $format = 'h:mm A';
                            }

                            $off_days = array();

                            if ($field['off_days'] != "") {
                                $off_days = explode(",", $field['off_days']);
                            }

                            $off_days_result = "";
                            $off_day_count = "";

                            $off_day_count1 = "";
                            foreach ($off_days as $offday) {
                                $off_day_count .= " day != " . $offday . " &&";
                                $off_day_count1 .= " day == " . $offday . " ||";
                            }


                            if ($field['off_days'] != "" && $off_day_count != "") {
                                $off_day_count = substr($off_day_count, 0, -2);
                                $off_days_result = ",beforeShowDay:function(date){ var day = date.getDay();return [(" . $off_day_count . ")]; }";
                            } else {
                                $off_days_result = ",beforeShowDay:function(date){ var day = date.getDay();return [true]; }";
                            }
                            $field['locale'] = ( $field['locale'] != '' ) ? $field['locale'] : 'en';
                            
                            $date_formate = $newarr['date_format'];
                            if ($show_time_calendar == "true") {
                                $field['clock'] = (isset($field['clock']) and $field['clock']) ? $field['clock'] : 'h:mm A';
                                $date_new_format_main = $date_new_format_main . ' ' . $format;
                                $date_formate .=' ' . $format;
                            }       

                            $arf_form_all_footer_js .= 'setTimeout(function(){ jQuery("#field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '").trigger("change");},200);';

                            $datetimepicker_locale = (in_array($field['locale'], array('ms', 'zh-HK'))) ? '' : $field['locale'];
                            if ($datetimepicker_locale == 'hy') {
                                $datetimepicker_locale = 'hy-am';
                            } else if ($datetimepicker_locale == 'no') {
                                $datetimepicker_locale = 'nb';
                            } else if ($datetimepicker_locale == 'tu') {
                                $datetimepicker_locale = 'tr';
                            }

                            $step = (isset($field['step']) and $field['step']) ? $field['step'] : '30';
                            $arf_form_all_footer_js .= 'var date_data_id = jQuery(this).attr("data-id"); jQuery("#field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '").datetimepicker({';


                            $cl_date_format = "YYYY-MM-DD";
                            if ($show_time_calendar == "true") {
                                $arf_form_all_footer_js .= 'stepping: ' . $step . ',';
                                $cl_date_format .= " ".$format;
                            }

                            if( $field['currentdefaultdate'] == 1 ){
                                $arf_form_all_footer_js .= 'useCurrent:true,';
                            } else {
                                $arf_form_all_footer_js .= 'useCurrent:false,';
                            }
                                                    
                            $arf_form_all_footer_js .='format: "' . $date_formate . '",
                                locale: "' . $datetimepicker_locale . '",    
                                minDate: moment("' . $start_date . ' 00:00 AM", "' . $date_new_format . '"),
                                maxDate: moment("' . $end_date . ' 11:59 PM", "' . $date_new_format . '"),
                                daysOfWeekDisabled: [' . $field['off_days'] . '],
                                keyBinds:"",';
                                if (is_rtl()) {
                                    $arf_form_all_footer_js .= 'widgetPositioning: {
                                        horizontal: "right",
                                        vertical: "auto"
                                    },';
                                }
                            $arf_form_all_footer_js .=  '});

                            jQuery("#field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '").on("dp.change", function(e) {
                                jQuery(this).trigger("change");
                                var formated_date = jQuery(this).data("DateTimePicker").viewDate();
                                var formatted_date = formated_date._d;
                                var data = moment(formatted_date).format("'.$cl_date_format.'");
                                jQuery("#field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '_formatted").val(data).trigger("change");
                            });
                            
                            jQuery(document).on("click",".arf_submit_btn",function(){
                                jQuery("#field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '").trigger("blur");
                            });';

                            
                            $set_default_date = '';

                            if (isset($field['set_field_value'])) {
                                $set_default_date = $armainhelper->convert_date($field['set_field_value'], 'd/m/Y', $defaultdate_format);
                                
                                $field['default_blank'] = 1;
                                $set_default_date = date($wp_format_date,strtotime($field['set_field_value']));

                            } else {

                                if ( isset($field['currentdefaultdate']) && $field['currentdefaultdate'] == 1 ) {

                                    $set_default_date = date($wp_format_date, current_time('timestamp'));

                                } elseif (isset($field['selectdefaultdate']) && $field['selectdefaultdate'] != '') {
                                    
                                    $set_default_date = $field['selectdefaultdate'];// $armainhelper->convert_date($field['selectdefaultdate'], $date_new_format_main, $defaultdate_format);

                                    //$set_default_date = date($wp_format_date, strtotime($set_default_date));
                                }
                            }

                            $data_off_days = "";
                            if( !empty($off_days) ){
                                $data_off_days = "data-off-days='".json_encode($off_days)."'";
                            }

                            $return_string .= '<input type="text" id="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" '.$arf_cookie_field_arr_attr.' '.$data_off_days.' ';

                            $return_string .= $arf_input_field_html;

                            $return_string .= " data-name='".$field_name."'";
                            $return_string .= " data-format='".$date_formate."'";

                            $is_default_blank = 1;

                            $placeholdertext_date = $field['placeholdertext'];
                            
                            if( isset($placeholdertext_date) && $placeholdertext_date != '' ){
                                $return_string .= ' placeholder="' . $placeholdertext_date . '" ';
                            }
                            if(isset($field['arf_enable_readonly']) && $field['arf_enable_readonly'] == 1){
                                $return_string .='readonly="readonly" ';    
                            }

                            if( $field['currentdefaultdate'] == 1 ){
                                $return_string .= ' data-default-value="'.$set_default_date.'" value="' . $set_default_date . '"';
                            } else if( $set_default_date != '' ){
                                $return_string .= ' data-default-value="'.$set_default_date.'" value="' . $set_default_date . '"';
                            }

                            if (isset($field['field_width']) and $field['field_width'] != '' and $field['enable_arf_prefix'] != 1 and $field['enable_arf_suffix'] != 1) {
                                $return_string .= ' style="width:' . $field['field_width'] . 'px !important ' . $inline_css_without_style . '"';
                            } else {
                                $return_string .= $inline_css_with_style_tag;
                            }

                            if (isset($field['required']) and $field['required']) {
                                $return_string .= ' data-validation-required-message="' . esc_attr($field['blank']) . '"';
                            }
                            
                            $return_string .=' />';

                            $return_string .= '<input type="hidden" name="' . $field_name . '" id="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '_formatted"';
                            $return_string .= $arf_on_change_function;
                            if( $field['currentdefaultdate'] == 1 ){
                                $return_string .= ' value="' . $set_default_date . '"';
                            } else if(isset($field['selectdefaultdate']) && $field['selectdefaultdate']!=''){
                                $return_string .= ' value="' . $field['selectdefaultdate'] . '"';
                            }
                            $return_string .= ' />';

                            $return_string .= $suffix;
                            $return_string .= $field_standard_tooltip;
                        }
                        $return_string .= $field_description;
                        $return_string .='</div>';
                        $return_string .='</div>';
                        break;
                    case 'time':
                        $return_string .= '<div id="arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container" class="arfformfield control-group arfmainformfield '. $required_class . ' ' . $class_position . ' ' . $error_class . ' arf_field_' . $field['id'] . '"  ' . $field_style . '  data-field-type="'.$field['type'].'">';
                        
                        if( $inputStyle != 'material' ){
                            $return_string .= $arf_main_label;
                        }

                        $return_string .= '<div class="sltstandard_time controls arf_time_main_controls arf_cal_theme_' . $newarr['arfcalthemecss'] . ' '.$field_tooltip_class.'" ' . $field_width . ' '.$field_tooltip.'>';
                        if( $inputStyle == 'material' ){
                            $return_string .= $arf_main_label;
                        }
                        if (apply_filters('arf_check_for_draw_outside', false, $field)) {
                            $return_string = apply_filters('arf_drawthisfieldfromoutside', $return_string, $field,$arf_on_change_function,$arf_data_uniq_id);
                        } else {
                            $arf_cookie_field_arr_attr = '';
                            if (isset($field['set_field_value']) && !empty($field['set_field_value'])) {
                                $arf_cookie_field_arr_attr = $field['set_field_value'];
                            }

                            $arf_form_all_footer_js .= 'setTimeout(function(){
                                jQuery("#field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '").trigger("change");
                                jQuery("#field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '").attr("data-value",jQuery("#field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '").val());
                            },200);';

                            $field['clock'] = (isset($field['clock']) and $field['clock'] == 24) ? 'H:mm' : 'h:mm A';
                            $field['step'] = (isset($field['step']) and $field['step']) ? $field['step'] : '30';
                            $field['default_hour'] = (isset($field['default_hour']) && $field['default_hour'] != "") ? $field['default_hour'] : '00';
                            $field['default_minutes'] = (isset($field['default_minutes']) && $field['default_minutes'] != "") ? $field['default_minutes'] : '00';
                            $arf_form_all_footer_js .= 'jQuery("#field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '").datetimepicker({
                                format: "' . $field['clock'] . '",
                                stepping: ' . $field['step'] . ',';
                                if(is_rtl())
                                {
                                    $arf_form_all_footer_js .= 'widgetPositioning: {
                                            horizontal: "right",
                                            vertical: "auto"
                                        },';
                                }
                            $arf_form_all_footer_js .= '});
                        
                            jQuery("#field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '").on("dp.change", function(e) {
                                jQuery("#field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '").trigger("change");
                            });';

                            if(!empty($arf_cookie_field_arr_attr))
                            {
                                $arf_form_all_footer_js .= 'jQuery(document).ready(function(){
                                    setTimeout(function(){
                                        jQuery("#field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '").attr("value","'.$arf_cookie_field_arr_attr.'");
                                    },500);
                                });';
                            }

                            $return_string .=$prefix;

                            $return_string .='<input type="text" name="' . $field_name . '" class="arf_timepciker" id="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" value="'.$field['default_value'].'" ';
                            if (isset($field['required']) and $field['required']) {
                                $return_string .=' data-validation-required-message="' . esc_attr($field['blank']) . '"';
                            }
                            //$return_string .= ' '.$arf_cookie_field_arr_attr.' ';
                            if(isset($field['arf_enable_readonly']) && $field['arf_enable_readonly'] == 1){
                                $return_string .='readonly="readonly" ';    
                            }
                            $return_string .= $arf_on_change_function;
                            $return_string .='/>';

                            $return_string .=$suffix;

                            $return_string .= $field_standard_tooltip;
                        }
                        $return_string .= $field_description;
                        $return_string .='</div>';
                        $return_string .='</div>';
                        break;
                    case 'image':
                        $return_string .= '<div id="arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container" class="arfformfield control-group arfmainformfield ' . $required_class . ' ' . $class_position . ' ' . $error_class . ' arf_field_' . $field['id'] . '"  ' . $field_style . '  data-field-type="'.$field['type'].'">';

                        if( $inputStyle != 'material' ){
                            $return_string .= $arf_main_label;
                        }

                        $return_string .='<div class="controls'.$field_tooltip_class.'" ' . $field_width . ' '.$field_tooltip.' >';
                        if( $inputStyle == 'material' ){
                            $return_string .= $arf_main_label;
                        }
                        if (apply_filters('arf_check_for_draw_outside', false, $field)) {
                            
                            $return_string = apply_filters('arf_drawthisfieldfromoutside', $return_string, $field,$arf_on_change_function,$arf_data_uniq_id);
                        } else {
                            $return_string .=$prefix;

                            $return_string .='<input type="url" id="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" name="' . $field_name . '" ';
                            if (isset($field['set_field_value'])) {
                                $return_string .=' value="' . $field['set_field_value'] . '"';
                            }

                            if( isset($field['placeholdertext']) && $field['placeholdertext'] != '' ){
                                $return_string .= ' placeholder="'.$field['placeholdertext'].'" ';
                            }
                            if(isset($field['arf_enable_readonly']) && $field['arf_enable_readonly'] == 1){
                                $return_string .='readonly="readonly" ';    
                            }

                            $default_value = $field['default_value'];

                            $default_value = apply_filters('arf_replace_default_value_shortcode',$default_value,$field,$form);

                            if( isset($field['set_field_value']) && $field['set_field_value'] != '' ){
                                $default_value = $field['set_field_value'];
                            }

                            if( $default_value != '' ){
                                $return_string .= " value='{$default_value}'";
                            }

                            $return_string .= $arf_input_field_html;
                            if (isset($field['field_width']) and $field['field_width'] != '' and $field['enable_arf_prefix'] != 1 and $field['enable_arf_suffix'] != 1) {
                                $return_string .= 'style="width:' . $field['field_width'] . 'px !important;  ' . $inline_css_without_style . '"';
                            } else {
                                $return_string .= $inline_css_with_style_tag;
                            }

                            if (isset($field['required']) and $field['required']) {
                                $return_string .= 'data-validation-required-message="' . esc_attr($field['blank']) . '"';
                            }
                            $return_string .= $arf_on_change_function;
                            $return_string .='/>';

                            $return_string .= $suffix;
                            $return_string .= $field_standard_tooltip;
                        }
                        $return_string .= $field_description;
                        $return_string .='</div>';
                        $return_string .='</div>';
                        break;
                    case 'hidden':
                        $arfaction = (isset($_GET) and isset($_GET['arfaction'])) ? 'arfaction' : 'action';

                        if (is_admin() and ( !isset($_GET[$arfaction]) or $_GET[$arfaction] != 'new')) {

                            $return_string .='<div id="arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container" class="arfformfield arfmainformfield top_container arf_field_' . $field['id'] . ']">';
                            $return_string .='<label class="arf_main_label">' . $field['name'] . ':</label>';
                            $return_string .= $field['value'];
                            $return_string .='</div>';
                        }

                        if (!is_admin() && apply_filters('arf_check_for_draw_outside', false, $field)) {
                            
                            $return_string = apply_filters('arf_drawthisfieldfromoutside', $return_string, $field,$arf_on_change_function,$arf_data_uniq_id);
                        } else {
                            if (isset($field['set_field_value'])) {
                                $return_string .= '<input type="hidden" id="field_' . $field['field_key'] . '" name="' . $field_name . '" value="' . esc_attr($field['set_field_value']) . '"/>';
                            } else {
                                if (isset($field['value']) && is_array($field['value'])) {
                                    foreach ($field['value'] as $checked) {
                                        $checked = apply_filters('arfhiddenvalue', $checked, $field);
                                        $return_string .='<input type="hidden" name="' . $field_name . '[]" value="' . esc_attr($checked) . '"/>';
                                    }
                                } else {
                                    $hidden_field_value = isset($field['default_value']) ? $field['default_value'] : '';
                                    $arf_current_user = wp_get_current_user();

                                    if (preg_match('/\[ARF_current_user_id\]/', $hidden_field_value)) {
                                        $hidden_field_value = str_replace('[ARF_current_user_id]', $arf_current_user->ID, $hidden_field_value);
                                    }
                                    if (preg_match('/\[ARF_current_user_name\]/', $hidden_field_value)) {
                                        $hidden_field_value = str_replace('[ARF_current_user_name]', $arf_current_user->user_login, $hidden_field_value);
                                    }
                                    if (preg_match('/\[ARF_current_user_email\]/', $hidden_field_value)) {
                                        $hidden_field_value = str_replace('[ARF_current_user_email]', $arf_current_user->user_email, $hidden_field_value);
                                    }
                                    if (preg_match('/\[ARF_current_date\]/', $hidden_field_value)) {
                                        $wp_format_date = get_option('date_format');
                                        $arf_current_date = date($wp_format_date, current_time('timestamp'));
                                        $hidden_field_value = str_replace('[ARF_current_date]', $arf_current_date, $hidden_field_value);
                                    }

                                    $hidden_field_value = apply_filters('arf_replace_default_value_shortcode',$hidden_field_value,$field,$form);

                                    $return_string .= '<input type="hidden" id="field_' . $field['field_key'] . '" name="' . $field_name . '" value="' . esc_attr($hidden_field_value) . '" />';
                                }
                            }
                        }
                        break;                
                    case 'scale':
                        $return_string .= '<div id="arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container" class="arfformfield control-group arfmainformfield ' . $required_class . ' ' . $class_position . ' ' . $error_class . ' arf_field_' . $field['id'] . '"  ' . $field_style . '  data-field-type="'.$field['type'].'">';
                        $return_string .= $arf_main_label;

                        $return_string .= '<div class="controls'.$field_tooltip_class.'" ' . $field_width . ' '.$field_tooltip.' >';
                        if (isset($field['set_field_value']) && !empty($field['set_field_value'])) {
                            $field['value'] = $field['set_field_value'];
                            $field['default_value'] = $field['set_field_value'];
                        }

                        if (apply_filters('arf_check_for_draw_outside', false, $field)) {
                            
                            $return_string = apply_filters('arf_drawthisfieldfromoutside', $return_string, $field,$arf_on_change_function,$arf_data_uniq_id);
                        } else {
                            $total_scales = (isset($field['maxnum']) && $field['maxnum'] > 0) ? $field['maxnum'] : 5;
                            $return_string .= "<div class='arf_star_rating_container arf_star_rating_container_{$field['id']}'>";
                            $is_scale_required = "";
                            if( isset($field['required']) && $field['required'] ){
                                $is_scale_required = " arf_required ";
                            }
                            for ($scl = $total_scales; $scl >= 0; $scl--) {
                                $return_string .= "<input type='radio' name='{$field_name}' class='arf_star_rating_input arf_hide_opacity {$is_scale_required}' value='{$scl}' id='field_{$field['field_key']}_{$scl}_{$arf_data_uniq_id}' ".$arf_on_change_function."";

                                $return_string .= (isset($field['default_value']) && $field['default_value']!='' && $field['default_value'] == $scl && $scl > 0) ? 'checked=checked' : '' ;
                                if (isset($field['required']) and $field['required'] && $scl == $total_scales) {
                                    $return_string .= ' data-validation-rating-message="' . esc_attr($field['blank']).'"';
                                }
                                $return_string .= $arf_on_change_function;
                                $return_string .= ' '.$arf_cookie_field_arr_attr.' ';
                                $return_string .= "/>";
                                if ($scl == 0) {
                                    $return_string .= "<label class='arf_star_rating_label arf_star_rating_label_null' for='field_{$field['field_key']}_{$scl}_{$arf_data_uniq_id}'></label>";
                                } else {
                                    $return_string .= "<label class='arf_star_rating_label' for='field_{$field['field_key']}_{$scl}_{$arf_data_uniq_id}'>";
                                        $return_string .= "<svg viewBox='0 0 24 24'><g>".ARF_STAR_RATING_ICON."</g></svg>";
                                    $return_string .= "</label>";
                                }
                            }
                            $return_string .= "</div>";
                            $return_string .= $field_standard_tooltip;
                        }


                        $return_string .= $field_description;
                        $return_string .='</div>';
                        $return_string .='<div style="clear:both;"></div>';
                        $return_string .='</div>';
                        break;
                    case 'like':
                        $return_string .= '<div id="arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container" class="arfformfield control-group arfmainformfield ' . $required_class . ' ' . $class_position . ' ' . $error_class . ' arf_field_' . $field['id'] . '"  ' . $field_style . '  data-field-type="'.$field['type'].'">';
                        $return_string .=$arf_main_label;

                        $return_string .='<div class="controls'.$field_tooltip_class.'" ' . $field_width . ' '.$field_tooltip.' >';

                        if (apply_filters('arf_check_for_draw_outside', false, $field)) {
                            
                            $return_string = apply_filters('arf_drawthisfieldfromoutside', $return_string, $field,$arf_on_change_function,$arf_data_uniq_id);
                        } else {


                            $return_string .='<div class="like_container">';
                            
                            $return_string .='<input type="radio" class="arf_hide_opacity arf_like" style="';
                            if (is_rtl()) {
                                $return_string .= 'right: -999px;';
                            } else {
                                $return_string .= 'left: -999px;';
                            }

                            if( isset($field['set_field_value']) && $field['set_field_value'] != '' ){
                                $field['default_value'] = $field['set_field_value'];
                            }

                            if(isset($arf_arr_preset_data) && count($arf_arr_preset_data) > 0 && isset($arf_arr_preset_data[$field['id']])){

                                $field['default_value'] = $arf_arr_preset_data[$field['id']];
                            }

                            $return_string .='position: absolute;" name="item_meta[' . $field['id'] . ']" id="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '-0" value="1" ' . checked($field['default_value'], 1, false);
                            if (isset($field['required']) and $field['required']) {
                                $return_string .=' data-validation-minchecked-minchecked="1" data-validation-minchecked-message="' . esc_attr($field['blank']) . '" ';
                            }
                            $return_string .= $arf_on_change_function;
                            $return_string .='/>';
                            $return_string .='<label id="like_' . $field['field_key'] . '_' . $arf_data_uniq_id . '-0" class="arf_like_btn ';

                            if (isset($field['default_value']) && $field['default_value'] == '1') {
                                $return_string .='active';
                            }
                            $return_string .='" for="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '-0" data-title="' . esc_attr($field['lbllike']) . '"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" height="30px" width="30px" viewBox="0 0 25 25"><g><g><path fill="#FFFFFF" d="M22.348,12.349c-0.017,0.011-0.031,0.021-0.047,0.029c0.241,0.281,0.451,0.678,0.451,1.207   c0,0.814-0.486,1.366-1.095,1.692c0.25,0.319,0.378,0.715,0.378,1.178c0,0.579-0.219,1.308-1.168,1.748   c0.175,0.315,0.288,0.722,0.204,1.248c-0.156,0.983-1.39,1.335-3.447,1.335H8.352c-0.842,0-1.207-0.395-1.374-0.98L6.96,19.745   v-9.289c0-0.439,0.081-0.576,0.111-0.627l0.018-0.028C7.311,9.485,7.804,9.19,7.998,8.913c1.802-2.566,2.632-3.43,2.519-5.011   C10.396,2.197,10.509,1.03,12,0.879c0.085-0.009,0.172-0.013,0.258-0.013c0.422,0,1.382,0.105,2.108,0.812   c0.706,0.686,1.451,1.746,1.589,3.151c0.103,1.044,0.127,2.343-0.168,3.242c1.628,0.001,4.758,0.003,5.252,0.003   c1.067,0,2.217,1.08,2.217,2.593C23.255,11.582,22.762,12.087,22.348,12.349z M4.718,20.854H3.442   c-0.409,0-0.756-0.295-0.816-0.694l-1.395-9.732c-0.035-0.234,0.034-0.472,0.191-0.651C1.58,9.598,1.808,9.495,2.047,9.495h2.67   c0.456,0,0.826,0.365,0.826,0.814v9.731C5.543,20.491,5.173,20.854,4.718,20.854z"/></g></g></svg></label>';
                            $return_string .='<input type="radio" class="arf_hide_opacity arf_like" style="';
                            if (is_rtl()) {
                                $return_string .= 'right: -999px;';
                            } else {
                                $return_string .= 'left: -999px;';
                            }
                            $return_string .='position: absolute;" name="item_meta[' . $field['id'] . ']" id="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '-1" value="0" ' . checked($field['default_value'], 0, false) . ' ';
                            $return_string .= ' '.$arf_cookie_field_arr_attr.' ';
                            $return_string .= $arf_on_change_function;
                            $return_string .='/><label id="like_' . $field['field_key'] . '_' . $arf_data_uniq_id . '-1" class="arf_dislike_btn ';
                            if (isset($field['default_value']) && $field['default_value'] == '0') {
                                $return_string .= 'active';
                            }
                            $return_string .='" for="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '-1" data-title="' . esc_attr($field['lbldislike']) . '"><svg version="1.1" xmlns="http://www.w3.org/2000/svg"  height="30px" width="30px" viewBox="0 0 25 25"><g xmlns="http://www.w3.org/2000/svg"><g><path fill="#ffffff" d="M23.041,11.953c-0.156,0.179-0.385,0.282-0.625,0.282h-2.668c-0.455,0-0.824-0.365-0.824-0.815V1.682   c0-0.451,0.369-0.816,0.824-0.816h1.274c0.409,0,0.757,0.296,0.816,0.696l1.394,9.739C23.268,11.535,23.199,11.774,23.041,11.953z    M17.379,11.929c-0.221,0.316-0.715,0.612-0.908,0.889c-1.801,2.568-2.63,3.434-2.518,5.015c0.121,1.707,0.008,2.874-1.481,3.026   c-0.085,0.009-0.172,0.013-0.258,0.013c-0.422,0-1.381-0.104-2.107-0.813c-0.705-0.686-1.451-1.746-1.588-3.152   c-0.103-1.045-0.126-2.346,0.169-3.244c-1.627-0.002-4.756-0.004-5.249-0.004c-1.067,0-2.216-1.081-2.216-2.595   c0-0.916,0.494-1.421,0.907-1.683c0.016-0.01,0.032-0.02,0.047-0.029C1.935,9.068,1.726,8.671,1.726,8.142   c0-0.815,0.486-1.367,1.094-1.694C2.569,6.129,2.441,5.733,2.441,5.27c0-0.58,0.219-1.309,1.168-1.749   c-0.176-0.316-0.288-0.723-0.205-1.25c0.156-0.984,1.39-1.336,3.446-1.336h9.267c0.842,0,1.207,0.394,1.373,0.982l0.018,0.06v9.296   c0,0.44-0.081,0.578-0.112,0.628L17.379,11.929z"/></g></g></svg></label>';


                            $return_string .='</div>';
                            $return_string .= $field_standard_tooltip;
                        }

                        $return_string .= $field_description;
                        $return_string .='</div>';
                        $return_string .='</div>';
                        break;
                    case 'arfslider':
                    case 'slider':
                        $return_string .= '<div id="arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container" class="arfformfield control-group arfmainformfield ' . $required_class . ' ' . $class_position . ' ' . $error_class . ' arf_field_' . $field['id'] . '"  ' . $field_style . '  data-field-type="'.$field['type'].'">';
                        $return_string .=$arf_main_label;

                        $return_string .='<div class="arf_slider_control controls'.$field_tooltip_class.'" ' . $field_width . ' '.$field_tooltip.'>';

                        if (apply_filters('arf_check_for_draw_outside', false, $field)) {
                            
                            $return_string = apply_filters('arf_drawthisfieldfromoutside', $return_string, $field,$arf_on_change_function,$arf_data_uniq_id);
                        } else {

                            $field['slider_step'] = is_numeric($field['slider_step']) ? $field['slider_step'] : 1;
                            $field['minnum'] = is_numeric($field['minnum']) ? $field['minnum'] : 1;
                            $field['maxnum'] = is_numeric($field['maxnum']) ? $field['maxnum'] : 50;
                            $field['slider_value'] = is_numeric($field['slider_value']) ? $field['slider_value'] : $field['minnum'];


                            if (isset($field['arf_range_selector']) && $field['arf_range_selector'] == '1') {

                                $arf_range_minnum = (isset($field['arf_range_minnum']) && $field['arf_range_minnum'] != '') ? $field['arf_range_minnum'] : $field['minnum'];
                                $arf_range_maxnum = (isset($field['arf_range_maxnum']) && $field['arf_range_maxnum'] != '') ? $field['arf_range_maxnum'] : $field['maxnum'];

                                $slider_value = "[" . $arf_range_minnum . "," . $arf_range_maxnum . "]";
                                $slider_defloat_value = $arf_range_minnum . "," . $arf_range_maxnum;
                                $slider_defloat_value_field = $arf_range_minnum . "," . $arf_range_maxnum;
                                $default_slider_range = $arf_range_minnum . " - " . $arf_range_maxnum;
                                if (isset($field['set_field_value'])) {
                                    $slider_value = "[" . $field['set_field_value']. "]";
                                    $slider_defloat_value_field = $field['set_field_value'];
                                    $default_slider_range = "[" . $field['set_field_value']. "]";
                                    //$field['slider_value'] = $field['set_field_value'];
                                }
                            } else {
                                $slider_defloat_value_field = $slider_value = $slider_defloat_value = $field['slider_value'];
                                $default_slider_range = $field['slider_value'];
                                if (isset($field['set_field_value'])) {
                                    $slider_defloat_value_field = $slider_value = $field['set_field_value'];
                                    $default_slider_range = $field['set_field_value'];
                                }
                            }

                            $field['slider_step'] = is_numeric($field['slider_step']) ? $field['slider_step'] : 1;
                            $field['minnum'] = is_numeric($field['minnum']) ? $field['minnum'] : 1;
                            $field['maxnum'] = is_numeric($field['maxnum']) ? $field['maxnum'] : 50;
                            $field['slider_value'] = is_numeric($field['slider_value']) ? $field['slider_value'] : $field['minnum'];
                            
                            $arf_form_all_footer_js .= 'jQuery("#field_' . $field['field_key'] . '_slide_'.$arf_data_uniq_id.'").arf_slider({ tooltip: "always", handle : "' . $field['slider_handle'] . '", value : "' . $field['slider_value'] . '" }).on("slideStop", function(ev){
                                    var val = jQuery("#field_' . $field['field_key'] . '_slide_'.$arf_data_uniq_id.'").arf_slider("getValue");
                                    if (val || val == "0"){
                                        var range_selector = jQuery("#field_' . $field['field_key'] . '_slide_'.$arf_data_uniq_id.'").attr("data-slider-range-selector");
                                            if (range_selector == "1"){
                                            }
                                        jQuery("#field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '").val(val).trigger("change");
                                    }
                                });';

                            if ($preview != true) {
                                $arf_form_all_footer_js .= 'if(jQuery("#field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '").is(":visible")) { jQuery("#field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '").val("' . $field['slider_value'] . '").trigger("change"); }';
                            }
                            
                            if ($preview != true) {
                                $arf_form_all_footer_js .= 'if(jQuery("#field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '").is(":visible")) { jQuery("#field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '").val("' . $field['slider_value'] . '").trigger("change"); }';
                            }
                            
                            /*if(!empty($arf_cookie_field_arr[$field['id']])) {
                                $data_slider_default_value = 'data-slider-default-value="' . $arf_cookie_field_arr[$field['id']]. '"';
                            }
                            else {*/
                                $data_slider_default_value = 'data-slider-default-value="' . $slider_defloat_value. '"';
                            /*}*/

                            $return_string .= '<input type="text" id="field_' . $field['field_key'] . '_slide_'.$arf_data_uniq_id.'" class="arfslider" data-form-data-id="' . $arf_data_uniq_id . '" data-slider-id="field_' . $field['field_key'] . '_slider" data-slider-min="' . $field['minnum'] . '" data-slider-max="' . $field['maxnum'] . '" data-slider-step="' . $field['slider_step'] . '" data-slider-value="' . $slider_value . '"  data-slider-range-selector="' . $field['arf_range_selector'] . '" '.$data_slider_default_value.' autocomplete="off" style="cursor:pointer;" />';
                            $return_string .= '<input type="hidden" id="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" class="arfslider_hidden" data-is-range-slider="' . $field['arf_range_selector'] . '" data-form-data-id="' . $arf_data_uniq_id . '" autocomplete="off" name="' . $field_name . '" data-value="' . $field['slider_value'] . '" data-slider-range-selector="' . $field['arf_range_selector'] . '" value="' . $slider_defloat_value_field . '" ';
                            if (isset($field['required']) and $field['required']) {
                                $return_string .= 'data-validation-required-message="' . esc_attr($field['blank']) . '"';
                            }
                            $return_string .= $arf_on_change_function;
                            $return_string .= '/>';


                            $return_string .= $field_standard_tooltip;
                        }
                        $return_string .= $field_description;
                        $return_string .='</div>';
                        $return_string .='</div>';
                        break;
                    case 'colorpicker':
                        $return_string .= '<div id="arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container" class="arfformfield control-group arfmainformfield ' . $required_class . ' ' . $class_position . ' ' . $error_class . ' arf_field_' . $field['id'] . '"  ' . $field_style . '  data-field-type="'.$field['type'].'">';
                        $return_string .=$arf_main_label;

                        $return_string .='<div class="arf_colorpicker_control controls'.$field_tooltip_class.'" ' . $field_width . ' '.$field_tooltip.'>';

                        if (apply_filters('arf_check_for_draw_outside', false, $field)) {
                            
                            $return_string = apply_filters('arf_drawthisfieldfromoutside', $return_string, $field,$arf_on_change_function,$arf_data_uniq_id);
                        } else {

                            if ($field['colorpicker_type'] == 'basic') {
                                $colorpickerclass = "arf_basic_colorpicker";
                            } else {
                                $colorpickerclass = "arf_js_colorpicker jscolor";
                            }

                            $defaultcolor = '';
                            $arfcolorpickerstyle = '';

                            if (isset($field['set_field_value'])) {
                                $defaultcolor = $field['set_field_value'];
                                if ($field['set_field_value'] != '') {
                                    $defaultcolor = $field['set_field_value'];
                                    $defaultcolor = @strtolower(str_replace('#', '', $defaultcolor));
                                    if ($defaultcolor == '000' || $defaultcolor == '000000') {
                                        $arfcolorpickerstyle = 'style="background:#000000;color:#FFFFFF;"';
                                        $defaultcolor = '#' . $defaultcolor;
                                    } else if ($defaultcolor == 'fff' || $defaultcolor == 'ffffff') {
                                        $arfcolorpickerstyle = 'style="background:#ffffff;color:#000000;"';
                                        $defaultcolor = '#ffffff';
                                    } else {
                                        $arfcolorpickerstyle = 'style="background:#' . $defaultcolor . ';color:#333333;"';
                                        $defaultcolor = '#' . $defaultcolor;
                                    }
                                }
                            } else {
                                if ($field['default_value'] != '') {
                                    $defaultcolor = $field['default_value'];
                                    $defaultcolor = @strtolower(str_replace('#', '', $defaultcolor));
                                    if ($defaultcolor == '000' || $defaultcolor == '000000') {
                                        $arfcolorpickerstyle = 'style="background:#000000;color:#FFFFFF;"';
                                        $defaultcolor = '#' . $defaultcolor;
                                    } else if ($defaultcolor == 'fff' || $defaultcolor == 'ffffff') {
                                        $arfcolorpickerstyle = 'style="background:#ffffff;color:#000000;"';
                                        $defaultcolor = '#ffffff';
                                    } else {
                                        $arfcolorpickerstyle = 'style="background:#' . $defaultcolor . ';color:#333333;"';
                                        $defaultcolor = '#' . $defaultcolor;
                                    }
                                }

                                if(isset($arf_arr_preset_data) && count($arf_arr_preset_data) > 0 && isset($arf_arr_preset_data[$field['id']])){                         
                                    $defaultcolor = $arf_arr_preset_data[$field['id']];
                                    $defaultcolor = @strtolower(str_replace('#', '', $defaultcolor));
                                    if ($defaultcolor == '000' || $defaultcolor == '000000'){
                                        $arfcolorpickerstyle = 'style="background:#000000;color:#FFFFFF;"';
                                                $defaultcolor = '#' . $defaultcolor;
                                    }else if ($defaultcolor == 'fff' || $defaultcolor == 'ffffff'){

                                        $arfcolorpickerstyle = 'style="background:#ffffff;color:#000000;"';
                                                $defaultcolor = '#ffffff';
                                    }else{
                                                        
                                        // echo $defaultcolor;
                                        $arfcolorpickerstyle = 'style="background:#' . $defaultcolor . ';color:#333333;"';
                                        $defaultcolor = '#' . $defaultcolor;
                                    }
                                }
                            }

                            $data_jscolor = 'data-jscolor="{hash:true,valueElement:field_'.$field['field_key'].'_'.$arf_data_uniq_id.'}"';

                            $return_string .='<div class="arfcolorpickerfield  " id="arfcolorpicker_' . $field['field_key'] . '_' . $arf_data_uniq_id . '">';
                            $return_string .='<div class="arfcolorimg"><div class="paint_brush_position"><svg width="18px" height="18px" viewBox="0 0 22 22"><g id="email"><path fill="#333333" fill-rule="evenodd" clip-rule="evenodd" d="M15.948,7.303L15.875,7.23l0.049-0.049l-2.459-2.459l3.944-3.872l2.313,0.024v2.654L15.948,7.303z M12.631,6.545c0.058,0.039,0.111,0.081,0.167,0.122c0.036,0.005,0.066,0.011,0.066,0.011c0.022,0.008,0.034,0.023,0.056,0.032l1.643,1.643c0.58,5.877-7.619,6.453-7.619,6.453c-5.389,0.366-5.455-1.907-5.455-1.907c3.559,1.164,6.985-5.223,6.985-5.223C11.001,4.915,12.631,6.545,12.631,6.545z"></path></g></svg></div></div>';
                            $return_string .= '<div class="arfcolorvalue  ' . $colorpickerclass . '" ' . $arfcolorpickerstyle . ' id="colorpicker_div_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" data-fid="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" '.$data_jscolor.' value ="'.$defaultcolor.'">' . $defaultcolor . '</div>';
                            $return_string .= '</div>';
                            $return_string .= '<input type="hidden" id="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" class="arfhiddencolor arf_color_picker_input" data-defaultcolor="'.$defaultcolor.'"  value="' . $defaultcolor . '"';
                            if (isset($field['required']) and $field['required']) {
                                $return_string .= ' data-validation-required-message="' . esc_attr($field['blank']) . '"';
                            }
                            $return_string .= ' '.$arf_cookie_field_arr_attr.' ';
                            $return_string .=$arf_on_change_function;
                            $return_string .= ' name="' . $field_name . '" autocomplete="off" />';

                            $return_string .= $field_standard_tooltip;
                        }
                        $return_string .= $field_description;
                        $return_string .='</div>';
                        $return_string .='</div>';
                        break;

                    case 'html':
                        $divider_class_for_confirmation = ($display_confirmation_summary) ? 'arf_display_to_confirmation_summary' : '';
                        $return_string .= '<div id="arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container" class="arfformfield '.$divider_class_for_confirmation.' control-group arfmainformfield ' . $required_class . ' ' . $class_position . ' ' . $error_class . ' arf_field_' . $field['id'] . '" data-field-type="html"  ' . $field_style . '>';
                        $return_string .= '<div class="arf_htmlfield_control">';

                        $html_field_description = $this->arf_html_entity_decode($field['description']);

                        if ($field['enable_total'] == 1) {
                            $html_field_description = do_shortcode($html_field_description);
                            $regex = '/<arftotal>(.*?)<\/arftotal>/is';
                            preg_match($regex, $html_field_description, $arftotalmatches);                        
                            if ($arftotalmatches) {
                                $return_string .= $arfieldhelper->arf_replace_running_total_field($html_field_description, $arftotalmatches, $field, $fields);
                            }
                            
                        } else {
                            $return_string .=  do_shortcode($html_field_description);
                        }

                        $return_string .= '</div>';
                        $return_string .='</div>';
                        break;
                    case 'email':
                    case 'confirm_email' :
                        $return_string .= '<div id="arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container" class="arfformfield control-group arfmainformfield ' . $required_class . ' ' . $class_position . ' ' . $error_class . ' arf_field_' . $field['id'] . '"  ' . $field_style . '  data-field-type="'.$field['type'].'">';

                        if( $inputStyle != 'material' ){
                            $return_string .= $arf_main_label;
                        }

                        $return_string .='<div class="controls'.$field_tooltip_class.'" ' . $field_width . ' '.$field_tooltip.' >';
                        if( $inputStyle == 'material' ){
                            $return_string .= $arf_main_label;
                        }
                        if (apply_filters('arf_check_for_draw_outside', false, $field)) {
                            
                            $return_string = apply_filters('arf_drawthisfieldfromoutside', $return_string, $field,$arf_on_change_function,$arf_data_uniq_id);
                        } else {
                            $return_string .=$prefix;

                            $confirm_email_field = '0';
                            if ($field['type'] == 'email' and isset($field['confirm_email_arr'][$field['id']]) and $field['confirm_email_arr'][$field['id']] != '') {
                                $confirm_password_field = $field['confirm_email_arr'][$field['id']];
                            }
                            if ($field['type'] == 'confirm_email') {
                                $field['value'] = $field['confirm_email_placeholder'];
                                $field['placeholdertext'] = $field['confirm_email_placeholder'];
                            }

                            $return_string .= '<input type="text" id="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" name="' . $field_name . '" ';

                            if( isset($field['placeholdertext']) && $field['placeholdertext'] != '' ){
                                $return_string .= ' placeholder="'.$field['placeholdertext'].'" ';
                            }
                            if(isset($field['arf_enable_readonly']) && $field['arf_enable_readonly'] == 1){
                                $return_string .='readonly="readonly" ';    
                            }

                            $default_value = $field['default_value'];

                            if(isset($arf_arr_preset_data) && count($arf_arr_preset_data) > 0 && isset($arf_arr_preset_data[$field['id']])){
                           
                                $default_value = $arf_arr_preset_data[$field['id']];
                            }

                            $default_value = apply_filters('arf_replace_default_value_shortcode',$default_value,$field,$form);

                            if( isset($field['set_field_value']) && $field['set_field_value'] != '' ){
                                $default_value = $field['set_field_value'];
                            }

                            if( $default_value != '' ){
                                $return_string .= " value='{$default_value}'";
                            }

                            $return_string .= $arf_input_field_html;

                            if (isset($field['field_width']) and $field['field_width'] != '' and ( $field['enable_arf_prefix'] != 1 || $field['enable_arf_suffix'] != 1 )) {

                                $return_string .= 'style="width:' . $field['field_width'] . 'px !important; ' . $inline_css_without_style . '"';
                            } else {
                                $return_string .= $inline_css_with_style_tag;
                            }
                            if (isset($field['required']) and $field['required']) {
                                $return_string .= ' data-validation-required-message="' . esc_attr($field['blank']) . '" ';
                            }

                            $return_string .=' data-validation-regex-regex="[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]+" data-validation-regex-message="' . esc_attr($field['invalid']) . '" ';

                            $return_string .= $arf_on_change_function;

                            if ($field['type'] == 'confirm_email') {

                                $return_string .=' data-validation-match-match="item_meta[' . $field['confirm_email_field'] . ']" data-cpass="1" data-validation-match-message="' . esc_attr($field['invalid']) . '"';
                            }
                            /*if (wp_is_mobile() && isset($inputmask)) {
                                $return_string .= ' data-mask-input="' . $inputmask . '"  data-ismask="true" ';
                            }*/

                            $return_string .=' />';

                            $return_string .=$suffix;

                            $return_string .= $field_standard_tooltip;
                        }
                        $return_string .= $field_description;
                        $return_string .='</div>';
                        $return_string .='</div>';
                        break;
                    case 'password':
                    case 'confirm_password' :
                        $return_string .= '<div id="arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container" class="arfformfield control-group arfmainformfield ' . $required_class . ' ' . $class_position . ' ' . $error_class . ' arf_field_' . $field['id'] . '"  ' . $field_style . ' data-field-type="'.$field['type'].'">';

                        if( $inputStyle != 'material' ){
                            $return_string .= $arf_main_label;
                        }

                        $return_string .='<div class="controls'.$field_tooltip_class.'" ' . $field_width . ' '.$field_tooltip.' >';
                        if( $inputStyle == 'material' ){
                            $return_string .= $arf_main_label;
                        }
                        if (apply_filters('arf_check_for_draw_outside', false, $field)) {
                            
                            $return_string = apply_filters('arf_drawthisfieldfromoutside', $return_string, $field,$arf_on_change_function,$arf_data_uniq_id);
                        } else {
                            $return_string .= $prefix;

                            $confirm_password_field = '0';
                            if (isset($field['confirm_password_arr'][$field['id']]) and $field['confirm_password_arr'][$field['id']] != '') {
                                $confirm_password_field = $field['confirm_password_arr'][$field['id']];
                            }
                            if ($field['type'] == 'confirm_password') {                            
                                $field['value'] = $field['password_placeholder'];
                                $field['placeholdertext'] = $field['password_placeholder'];
                            }
                            $return_string .= '<input type="password" id="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" name="' . $field_name . '" ';

                            $default_value = $field['default_value'];

                            if(isset($arf_arr_preset_data) && count($arf_arr_preset_data) > 0 && isset($arf_arr_preset_data[$field['id']])){                         
                                $default_value = $arf_arr_preset_data[$field['id']];
                            }

                            if( isset($field['set_field_value']) && $field['set_field_value'] != '' ){
                                $default_value = $field['set_field_value'];
                            }

                            if( $default_value != '' ){
                                $return_string .= " value='{$default_value}'";
                            }

                            if( isset($field['placeholdertext']) && $field['placeholdertext'] != '' ){
                                $return_string .= ' placeholder="'.$field['placeholdertext'].'" ';
                            }
                            if(isset($field['arf_enable_readonly']) && $field['arf_enable_readonly'] == 1){
                                $return_string .='readonly="readonly" ';    
                            }

                            $return_string .= $arf_input_field_html;

                            if (isset($field['field_width']) and $field['field_width'] != '' and ( $field['enable_arf_prefix'] != 1 || $field['enable_arf_suffix'] != 1 )) {
                                $return_string .= 'style="width:' . $field['field_width'] . 'px !important; ' . $inline_css_without_style . '"';
                            } else {
                                $return_string .= $inline_css_with_style_tag;
                            }
                            if ($field['minlength'] != '') {
                                $return_string .=' minlength="' . $field['minlength'] . '" ';
                                $return_string .=' data-validation-minlength-message="' . esc_attr($field['minlength_message']) . '" ';
                            }

                            if (isset($field['required']) and $field['required']) {
                                $return_string .= 'data-validation-required-message="' . esc_attr($field['blank']) . '"';
                            }

                            $return_string .= $arf_on_change_function;
                            if ($field['type'] == 'confirm_password') {
                                $return_string .= 'data-validation-match-match="item_meta[' . $field['confirm_password_field'] . ']"  data-cpass="1" data-validation-match-message="' . esc_attr($field['invalid']) . '" class="arf_password_field" ';
                            }

                            /*if (wp_is_mobile() && isset($inputmask)) {
                                $return_string .=' data-mask-input="' . $inputmask . '" data-ismask="true" ';
                            }*/

                            $return_string .= ' />';

                            $return_string .=$suffix;


                            if ($field['type'] == 'password' and isset($field['password_strength']) and $field['password_strength'] == 1) {
                                $arf_form_all_footer_js .= 'jQuery("#field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '").on("keyup", function(){
                                    if (!jQuery.isFunction(arf_password_meter)) {
                                        return;
                                    }
                                    arf_password_meter("' . $field['field_key'] . '_' . $arf_data_uniq_id . '");
                                });';
                                $return_string .= '<div id="strenth_meter_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" class="arf_strenth_mtr"><div class="inside_title">' . addslashes(esc_html__('Strength indicator', 'ARForms')) . '</div><div class="arf_strenth_meter"><div class="arfp_box"></div><div class="arfp_box"></div><div class="arfp_box"></div><div class="arfp_box"></div><div class="arfp_box"></div></div></div>';
                            }

                            $return_string .= $field_standard_tooltip;
                        }
                        $return_string .= $field_description;
                        $return_string .='</div>';
                        $return_string .='</div>';
                        break;
                    default :
                        
                        if (apply_filters('arf_wrap_input_field', true, $field['type'])) {
                            $arf_material_input_cls = apply_filters('arf_add_material_input_cls',$material_input_cls,$field['type'],$inputStyle);
                            $return_string .= '<div id="arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container" class="arfformfield control-group arfmainformfield '.$arf_material_input_cls.' ' . $required_class . ' ' . $class_position . ' ' . $error_class . ' arf_field_' . $field['id'] . '"  ' . $field_style . ' data-field-type="'.$field['type'].'">';
                            if( $inputStyle != 'material' ){
                                $return_string .=$arf_main_label;
                                $field_tooltip = $field_standard_tooltip;
                            }
                                                 
                            $return_string = apply_filters('form_fields', $return_string, $form, $field_name, $arf_data_uniq_id, $field, $field_tooltip, $field_description,$OFData,$inputStyle,$arf_main_label,$arf_on_change_function);

                            $return_string .='</div>';
                        } else {
                            $return_string = apply_filters('form_fields', $return_string, $form, $field_name, $arf_data_uniq_id, $field, $field_tooltip, $field_description,$OFData,$inputStyle,$arf_main_label,$arf_on_change_function);
                        }
                }

                
                global $arf_column_classes;
                
                if( !isset($field['inner_class']) ){
                    $field['inner_class'] = 'arf_1col';
                }

                if($field['type'] == 'confirm_email'){
                    $field['inner_class'] = $field['confirm_email_inner_classes'];
                }
                if($field['type'] == 'confirm_password'){
                    $field['inner_class'] = $field['confirm_password_inner_classes'];
                }

                if($field['inner_class']=='arf_1col' || $field['inner_class']=='arf_2col' || $field['inner_class']=='arf_3col' || $field['inner_class']=='arf_4col' || $field['inner_class']=='arf_5col' || $field['inner_class']=='arf_6col') { 
                        $return_string .= '<div style="clear:both;"></div>';
                } else if($field['inner_class']=='arf21colclass' || $field['inner_class']=='arf31colclass'  || $field['inner_class']=='arf41colclass' || $field['inner_class']=='arf42colclass' || $field['inner_class']=='arf43colclass' || $field['inner_class']=='arf51colclass' || $field['inner_class']=='arf52colclass' || $field['inner_class']=='arf53colclass' || $field['inner_class']=='arf54colclass' || $field['inner_class']=='arf61colclass' || $field['inner_class']=='arf62colclass' || $field['inner_class']=='arf63colclass' || $field['inner_class']=='arf64colclass' || $field['inner_class']=='arf65colclass') { 
                    $return_string .= '<div class="arf_half_middle"></div>'; 
                } else if($field['inner_class'] == 'arf_23col'){
                    $return_string .= '<div class="arf_third_middle"></div>'; 
                }
            } else {
                $field_ext_extract = explode('|', $field);  
                $field_level_class_blank ='';

                $arf_classes_blank_0 = $field_ext_extract[0];
                $arf_next_div_classes = '';

                if($arf_classes_blank_0 == 'arf21colclass')
                {
                     $arf_classes_blank = 'frm_first_half';
                     $arf_next_div_classes = 'arf_half_middle';
                     $field_level_class_blank = 'arf_2';
                }
                else if($arf_classes_blank_0 == 'arf_2col')
                {
                     $arf_classes_blank = 'frm_last_half';
                     $field_level_class_blank = 'arf_2';

                }
                if($arf_classes_blank_0 == 'arf31colclass')
                {
                    $arf_classes_blank = 'frm_first_third';
                    $arf_next_div_classes = 'arf_half_middle';
                    $field_level_class_blank = 'arf_3';
                }
                else if($arf_classes_blank_0 == 'arf_23col')
                {
                    $arf_classes_blank = 'frm_third';
                    $arf_next_div_classes = 'arf_half_middle';
                    $field_level_class_blank = 'arf_3';
                }
                else if($arf_classes_blank_0 == 'arf_3col')
                {
                    $arf_classes_blank = 'frm_last_third';
                    $field_level_class_blank = 'arf_3';
                }
                else if($arf_classes_blank_0 == 'arf41colclass')
                {
                    $arf_classes_blank = 'frm_first_fourth';
                    $arf_next_div_classes = 'arf_half_middle';
                    $field_level_class_blank = 'arf_4';
                }
                else if($arf_classes_blank_0 == 'arf42colclass' || $arf_classes_blank_0 == 'arf43colclass')
                {
                    $arf_classes_blank = 'frm_fourth';
                    $arf_next_div_classes = 'arf_half_middle';
                    $field_level_class_blank = 'arf_4';
                }
                else if($arf_classes_blank_0 == 'arf_4col')
                {
                    $arf_classes_blank = 'frm_last_fourth';
                    $field_level_class_blank = 'arf_4';
                }
                else if($arf_classes_blank_0 == 'arf51colclass')
                {
                    $arf_classes_blank = 'frm_first_fifth';
                    $arf_next_div_classes = 'arf_half_middle';
                    $field_level_class_blank = 'arf_5';
                }
                else if($arf_classes_blank_0 == 'arf52colclass' || $arf_classes_blank_0 == 'arf53colclass' || $arf_classes_blank_0 == 'arf54colclass')
                {
                    $arf_classes_blank = 'frm_fifth';
                    $arf_next_div_classes = 'arf_half_middle';
                    $field_level_class_blank = 'arf_5';
                }
                else if($arf_classes_blank_0 == 'arf_5col')
                {
                    $arf_classes_blank = 'frm_last_fifth';
                    $field_level_class_blank = 'arf_5';
                }
                else if($arf_classes_blank_0 == 'arf61colclass')
                {
                    $arf_classes_blank = 'frm_first_six';
                    $arf_next_div_classes = 'arf_half_middle';
                    $field_level_class_blank = 'arf_6';
                }
                else if($arf_classes_blank_0 == 'arf62colclass' || $arf_classes_blank_0 == 'arf63colclass' || $arf_classes_blank_0 == 'arf64colclass' || $arf_classes_blank_0 == 'arf65colclass')
                {
                    $arf_classes_blank = 'frm_six';
                    $arf_next_div_classes = 'arf_half_middle';
                    $field_level_class_blank = 'arf_6';
                }
                else if($arf_classes_blank_0 == 'arf_6col')
                {
                    $arf_classes_blank = 'frm_last_six';
                    $field_level_class_blank = 'arf_6';
                }

                $calculte_width = @$field_resize_width[$arf_field_front_counter] - (isset($arf_column_field_custom_width[$field_level_class_blank]) ? @$arf_column_field_custom_width[$field_level_class_blank] : '0');                    

                $return_string .='<div class="arfformfield control-group arfmainformfield arfformfield arfemptyfield '.$arf_classes_blank.'" style=width:'.$calculte_width.'%;></div>';
                
                if($arf_next_div_classes=='')
                {
                    $return_string .='<div style="clear:both;"></div>';
                }else {
                    $return_string .='<div class="'.$arf_next_div_classes.'"></div>';
                }
            }

            
            do_action('arfafterdisplayfield', $field);
            $index_arf_fields++;
            $arf_field_front_counter++;
        }

        return $return_string;
    }

    function arf_field_wise_js_css() {
        $arf_field_wise_js_css = apply_filters('arf_field_wise_js_css', array(
            'slider' => array(
                'title' => addslashes(esc_html__('Slider Control', 'ARForms')),
                'handle' => array(
                    'js' => array('arfbootstrap-modernizr-js', 'arfbootstrap-slider-js'),
                    'css' => array('arfbootstrap-slider'),
                ),
            ),
            'colorpicker' => array(
                'title' => addslashes(esc_html__('Color Picker', 'ARForms')),
                'handle' => array(
                    'js' => array('arf_js_color'),
                    'css' => array('arf-fontawesome-css'),
                ),
            ),
            'dropdown' => array(
                'title' => addslashes(esc_html__('Drop Down', 'ARForms')),
                'handle' => array(
                    'js' => array('jquery-bootstrap-slect'),
                    'css' => array('arfbootstrap-select'),
                ),
            ),
            'file' => array(
                'title' => addslashes(esc_html__('File Upload', 'ARForms')),
                'handle' => array(
                    'js' => array('filedrag'),
                    'css' => array('arf-filedrag'),
                ),
            ),
            'date_time' => array(
                'title' => addslashes(esc_html__('Datepicker / Timepicker', 'ARForms')),
                'handle' => array(
                    'js' => array('bootstrap-locale-js', 'bootstrap-datepicker'),                    
                    'css' => array('arfbootstrap-datepicker-css'),
                ),
            ),
            'autocomplete' => array(
                'title' => addslashes(esc_html__('Autocomplete', 'ARForms')),
                'handle' => array(
                    'js' => array('bootstrap-typeahead-js')
                ),
            ),
            'fontawesome' => array(
                'title' => addslashes(esc_html__('Font Awesome', 'ARForms')),
                'handle' => array(
                    'css' => array('arf-fontawesome-css'),
                ),
            ),
            'mask_input' => array(
                'title' => addslashes(esc_html__('Mask Input', 'ARForms')),
                'handle' => array(
                    'js' => array('arfbootstrap-inputmask','jquery-maskedinput'),
                ),
            ),
            'tooltip' => array(
                'title' => addslashes(esc_html__('Tooltip', 'ARForms')),
                'handle' => array(
                    'js' => array('arf_tipso_js_front'),
                    'css' => array('arf_tipso_css_front'),
                ),
            ),
            'animate_number' => array(
                'title' => addslashes(esc_html__('Number Animation', 'ARForms')),
                'handle' => array(
                    'js' => array('animate-numbers'),
                ),
            ),
            'material' => array(
                'title' => addslashes(esc_html__('Material', 'ARForms')),
                'handle' => array(
                    'css' => array('arf_materialize_css'),
                    'js' => array('arf_materialize_js'),
                )
            )
        ));
        return $arf_field_wise_js_css;
    }

    function arf_get_form_style($id, $arf_data_uniq_id = '', $type = '', $position = '', $bgcolor = '', $txtcolor = '', $btn_angle = '', $modal_bgcolor = '', $overlay_value = '', $is_fullscrn = '', $inactive_min= '', $modal_effect = '') {
        
        global $arf_loaded_form_unique_id_array, $arfieldhelper, $arrecordhelper, $arfform, $armainhelper, $arformcontroller,$arsettingcontroller,$front_end_get_temp_fields;
        $return_css = '';
        if ($arf_data_uniq_id == '') {
            $arf_data_uniq_id = rand(1, 99999);
            
            if (empty($arf_data_uniq_id) || $arf_data_uniq_id == '') {
                $arf_data_uniq_id = $id;
            }

            if ($type != '') {
                if ($position != '') {
                    $arf_loaded_form_unique_id_array[$id]['type'][$type][$position][] = $arf_data_uniq_id;
                } else {
                    $arf_loaded_form_unique_id_array[$id]['type'][$type][] = $arf_data_uniq_id;
                }
            } else {
                $arf_loaded_form_unique_id_array[$id]['normal'][] = $arf_data_uniq_id;
            }
        }

        if(!isset($GLOBALS['arf_form_data'][$id])){
            $form = $arfform->getOne((int) $id);
            $GLOBALS['arf_form_data'][$id] = $form;
        } else {
            $form = $GLOBALS['arf_form_data'][$id];
            $form->options = maybe_unserialize($form->options);
        }

        if (!isset($form)) {
            return;
        }
        $css_data_arr = $form->form_css;

        $arr = maybe_unserialize($css_data_arr);

        $newarr = array();
        $newarr = $arr;
        $return_css .= '<style type="text/css" id="'.$id.'" data-form-unique-id="'.$arf_data_uniq_id.'" >';
        
        $form->form_css = maybe_unserialize($form->form_css);

        $loaded_field = isset($form->options['arf_loaded_field']) ? $form->options['arf_loaded_field'] : array();
        $return_css .= stripslashes_deep(get_option('arf_global_css'));
        $form->options['arf_form_other_css'] = $arformcontroller->br2nl($form->options['arf_form_other_css']);
        $return_css .= $armainhelper->esc_textarea($form->options['arf_form_other_css']);

        $fields = $arfieldhelper->get_form_fields_tmp(false, $form->id, false, 0);
        $GLOBALS['form_fields'][$form->id] = $fields;
        
        $newFields = array();

        foreach($fields as $k => $f ){

            if( is_array($f) ){
                foreach($f as $n => $i ){
                    $newFields[$k][$n] = $i;
                }
            } else if( is_object($f) ){
                $fi = $this->arfObjtoArray($f);
                foreach($fi as $n => $i ){
                    $newFields[$k][$n] = $i;
                }
            } else {
                $newFields[$k] = $f;
            }
        }
        unset($k);
        unset($f);
        unset($n);
        unset($i);
        unset($fi);

        $values['fields'] = $this->arfObjtoArray($newFields);

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
            $return_css .= '  
            .arf_form .arf_smiley_container .popover { background-color: #000000 !important; color:#FFFFFF !important; width:auto; }
            .arf_form .arf_smiley_container .popover .popover-content { color:#FFFFFF !important; }
            .arf_form .arf_smiley_container .popover .popover-title { display:none; }
            .arf_form .arf_smiley_container .popover.top .arrow:after { border-top-color: #000000 !important; }

            .arf_form .arf_smiley_btn {cursor:pointer; display: inline-block;}
            .arf_form .arf_smiley_btn .arf_smiley_img{opacity: 0.6; box-shadow:none;}
            .arf_form .arf_smiley_btn .arf_smiley_icon{opacity: 0.6; box-shadow:none; text-align: center; }

            .ar_main_div_' . $form->id . ' .arf_smiley_btn .arf_smiley_icon{
                float: left;
                line-height:normal;
                padding:0 2px;
            }';
        }



        foreach ($custom_css_array_form as $custom_css_block_form => $custom_css_classes_form) {

            if (isset($form->options[$custom_css_block_form]) and $form->options[$custom_css_block_form] != '') {

                $form->options[$custom_css_block_form] = $arformcontroller->br2nl($form->options[$custom_css_block_form]);

                if ($custom_css_block_form == 'arf_form_outer_wrapper') {
                    $arf_form_outer_wrapper_array = explode('|', $custom_css_classes_form);

                    foreach ($arf_form_outer_wrapper_array as $arf_form_outer_wrapper1) {
                        if ($arf_form_outer_wrapper1 == '.arf_form_outer_wrapper')
                            $return_css .= '.ar_main_div_' . $form->id . '.arf_form_outer_wrapper { ' . $form->options[$custom_css_block_form] . ' } ';
                        if ($arf_form_outer_wrapper1 == '.arfmodal')
                            $return_css .= '#popup-form-' . $form->id . '.arfmodal{ ' . $form->options[$custom_css_block_form] . ' } ';
                    }
                }
                else if ($custom_css_block_form == 'arf_form_inner_wrapper') {
                    $arf_form_inner_wrapper_array = explode('|', $custom_css_classes_form);
                    foreach ($arf_form_inner_wrapper_array as $arf_form_inner_wrapper1) {
                        if ($arf_form_inner_wrapper1 == '.arf_fieldset')
                            $return_css .= '.ar_main_div_' . $form->id . ' ' . $arf_form_inner_wrapper1 . ' { ' . $form->options[$custom_css_block_form] . ' } ';
                        if ($arf_form_inner_wrapper1 == '.arfmodal')
                            $return_css .= '.arfmodal .arfmodal-body .ar_main_div_' . $form->id . ' .arf_fieldset { ' . $form->options[$custom_css_block_form] . ' } ';
                    }
                }
                else if ($custom_css_block_form == 'arf_form_error_message') {
                    $arf_form_error_message_array = explode('|', $custom_css_classes_form);

                    foreach ($arf_form_error_message_array as $arf_form_error_message1) {
                        $return_css .= '.ar_main_div_' . $form->id . ' ' . $arf_form_error_message1 . ' { ' . $form->options[$custom_css_block_form] . ' } ';
                    }
                } else {
                    $return_css .= '.ar_main_div_' . $form->id . ' ' . $custom_css_classes_form . ' { ' . $form->options[$custom_css_block_form] . ' } ';
                }
            }
        }

        if ($type != '') {
            $form_opacity = ( $form->form_css['arfmainform_opacity'] == '' || $form->form_css['arfmainform_opacity'] > 1) ? 1 : $form->form_css['arfmainform_opacity'];
            $arf_popup_data_uniq_id = $arf_data_uniq_id;
            $return_css .= ' #arf-popup-form-' . $form->id . '.arf_popup_' . $arf_popup_data_uniq_id . ' .arf_fly_sticky_btn{
                background:' . $bgcolor . ';
                color:' . $txtcolor . ';
            }
            .arfmodal { margin:0; padding:0; }';

            $custom_css_array_form = array(
                'arf_form_fly_sticky' => '.arf_fly_sticky_btn',
                'arf_form_modal_css' => '.arfmodal',
                'arf_form_link_css' => '.arform_modal_link_' . $form->id,
                'arf_form_link_hover_css' => '.arform_modal_link_' . $form->id . ':hover',
                'arf_form_button_css' => '.arform_modal_button_' . $form->id,
                'arf_form_button_hover_css' => '.arform_modal_button_' . $form->id . ':hover',
            );

            foreach ($custom_css_array_form as $custom_css_block_form => $custom_css_classes_form) {
                if (isset($form->options[$custom_css_block_form]) and $form->options[$custom_css_block_form] != '') {

                    $form->options[$custom_css_block_form] = $arformcontroller->br2nl($form->options[$custom_css_block_form]);

                    if ($custom_css_block_form == 'arf_form_modal_css') {
                        $return_css .= '#popup-form-' . $form->id . $custom_css_classes_form . ' { ' . $form->options[$custom_css_block_form] . ' } ';
                    } elseif ($custom_css_block_form == 'arf_form_link_css' || $custom_css_block_form == 'arf_form_button_css' || $custom_css_block_form == 'arf_form_link_hover_css' || $custom_css_block_form == 'arf_form_button_hover_css') {
                        $return_css .= $custom_css_classes_form . ' { ' . $form->options[$custom_css_block_form] . ' } ';
                    } else {
                        $return_css .= '#arf-popup-form-' . $form->id . ' ' . $custom_css_classes_form . ' { ' . $form->options[$custom_css_block_form] . ' } ';
                    }
                }
            }
            $return_css .= ' #popup-form-' . $form->id . '.arf_flymodal .arfmodal-header, 
#popup-form-' . $form->id . '.arform_right_fly_form_block_right_main .arfmodal-header, 
#popup-form-' . $form->id . '.arform_sb_fx_form_left_' . $form->id . ' .arfmodal-header, 
#popup-form-' . $form->id . '.arform_bottom_fixed_form_block_top .arfmodal-header { border-bottom:none; }';

            $return_css .= '.arf_pop_'.$arf_popup_data_uniq_id.' .arfmodal-body .ar_main_div_' . $form->id . ' .arf_fieldset{box-shadow:none !important;}';

            if ($form_opacity < 1 && $type != 'fly' && $type != 'sticky') {

                $return_css .= '
#popup-form-' . $form->id . '.arfmodal:not(.arfmodal-fullscreen){
background:none;
border:none;
box-shadow:none;
}
.arfmodal .arfmodal-body .ar_main_div_' . $form->id . ' .arf_fieldset { background: rgba(' . $arsettingcontroller->hex2rgb($form->form_css['arfmainformbgcolorsetting']) . ',' . $form_opacity . ') url(' . $form->form_css['arfmainform_bg_img'] . ');  background-repeat:no-repeat; background-position:top left; }
.arfmodal-backdrop, .arfmodal-backdrop.arffade.in {
opacity: 0.5;
}
.arfmodal-body { padding:0 !important; }
#popup-form-' . $form->id . '.arf_flymodal, #popup-form-' . $form->id . '.arform_right_fly_form_block_right_main, #popup-form-' . $form->id . '.arform_sb_fx_form_left_' . $form->id . ', #popup-form-' . $form->id . '.arform_bottom_fixed_form_block_top {
background : rgba(' . $arsettingcontroller->hex2rgb($form->form_css['arfmainformbgcolorsetting']) . ', ' . $form_opacity . ');
background-position: left top;
background-repeat: no-repeat;';
                if ($form_opacity == 0) {

                    $return_css .= ' border: 0px !important; box-shadow:none !important;';
                }
                $return_css .= ' }';
            } else if ($form_opacity == 1 && $type != 'fly' && $type != 'sticky') {
                $return_css .= '
#popup-form-' . $form->id . '.arfmodal:not(.arfmodal-fullscreen){
background:none;
border:none;
box-shadow:none;
}
';
            }

            if ($type == 'fly') {
                if ($position == 'right') {
                    $button_angle_class = '';
                    if ($btn_angle != '' && $btn_angle != '0') {
                        $button_angle_class = '-webkit-transform:rotate(' . $btn_angle . 'deg);
-moz-transform:rotate(' . $btn_angle . 'deg);
-ms-transform:rotate(' . $btn_angle . 'deg);
-o-transform:rotate(' . $btn_angle . 'deg);
transform:rotate(' . $btn_angle . 'deg);';
                    }

                    $return_css .= '.arf_popup_' . $arf_popup_data_uniq_id . ' .arform_side_block_right_' . $form->id . ' {opacity:1;top:50%; right:-2px; position:fixed;z-index:9999; background:#8ccf7a; border:none; border-right:0px; padding:10px 13px 0 13px; cursor:pointer; border-top-left-radius:3px; border-bottom-left-radius:3px; font-size:14px; height:25px; color:#ffffff; font-weight:bold; ' . $button_angle_class . '}
.arf_popup_' . $arf_popup_data_uniq_id . ' .arform_side_block_right_' . $form->id . ':hover {opacity:1;}
.arf_popup_' . $arf_popup_data_uniq_id . ' .arform_sb_fx_form_right_' . $form->id . ' {position:fixed;}
';
                } else {
                    $button_angle_class = '';
                    if ($btn_angle != '' && $btn_angle != '0') {
                        $button_angle_class = '-webkit-transform:rotate(' . $btn_angle . 'deg);
-moz-transform:rotate(' . $btn_angle . 'deg);
-ms-transform:rotate(' . $btn_angle . 'deg);
-o-transform:rotate(' . $btn_angle . 'deg);
transform:rotate(' . $btn_angle . 'deg);';
                    }

                    $return_css .= '.arf_popup_' . $arf_popup_data_uniq_id . ' .arform_side_block_left_' . $form->id . ' {opacity:1;top:50%;left:-2px; position:fixed;z-index:9999; background:#2d6dae; border:none; border-left:0px; padding:10px 13px 0 13px; cursor:pointer; border-top-right-radius:3px; border-bottom-right-radius:3px; font-size:14px; height:25px; color:#ffffff; font-weight:bold; ' . $button_angle_class . ' }
.arf_popup_' . $arf_popup_data_uniq_id . ' .arform_side_block_left_' . $form->id . ':hover {opacity:1;}
.arf_popup_' . $arf_popup_data_uniq_id . ' .arform_sb_fx_form_left_' . $form->id . ' { position:fixed; }
';
                }
            }


            $return_css .= '.arforms_model_popup_id_' . $arf_popup_data_uniq_id . ' .arfmodal-backdrop,
.arforms_model_popup_id_' . $arf_popup_data_uniq_id . ' .arfmodal-backdrop.arffade.in {
opacity: ' . $overlay_value . ';
filter: alpha(opacity= ' . ($overlay_value * 100) . ');
}';
            if (!empty($modal_bgcolor)) {
                $return_css .= '#popup-form-' . $arf_popup_data_uniq_id . '.arfmodal-fullscreen{background-color: ' . $modal_bgcolor . ';} ';
                
            }
            if ($type == 'button') {

                $return_css .= ' .arform_modal_button_popup_' . $arf_popup_data_uniq_id . ' {
background:' . $bgcolor . ' !important;
color: ' . $txtcolor . ' !important;
}';
            }
        }

        foreach ($values['fields'] as $field) {
            foreach($field['field_options'] as $f => $fopt) {
                $field[$f] = $fopt;
            }
            $field['id'] = $arfieldhelper->get_actual_id($field['id']);

            if ($field['type'] == 'select' ) {
                if (isset($field['size']) && $field['size'] != 1) {
                    if ($newarr['auto_width'] != "1") {

                        if (isset($field['field_width']) and $field['field_width'] != '') {

                            $return_css .= '.ar_main_div_' . $field['form_id'] . ' .select_controll_' . $field['id'] . ':not([class*="span"]):not([class*="col-"]):not([class*="form-control"]){width:' . $field['field_width'] . 'px !important;}';
                        }
                    }
                }
            }else if ($field['type'] == 'time') {
                if (isset($field['field_width']) and $field['field_width'] != '') {
                    $return_css .= '.ar_main_div_' . $field['form_id'] . ' .time_controll_' . $field['id'] . ':not([class*="span"]):not([class*="col-"]):not([class*="form-control"]){width:' . $field['field_width'] . 'px !important;}';
                }
            }

            if (isset($field['field_width']) and $field['field_width'] != '') {
                $return_css .= ' .ar_main_div_' . $form->id . ' #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .help-block { width: ' . $field['field_width'] . 'px; } ';
            }

            if ($field['type'] == 'divider') {

                if ($newarr['arfsectiontitlefamily'] != "Arial" && $newarr['arfsectiontitlefamily'] != "Helvetica" && $newarr['arfsectiontitlefamily'] != "sans-serif" && $newarr['arfsectiontitlefamily'] != "Lucida Grande" && $newarr['arfsectiontitlefamily'] != "Lucida Sans Unicode" && $newarr['arfsectiontitlefamily'] != "Tahoma" && $newarr['arfsectiontitlefamily'] != "Times New Roman" && $newarr['arfsectiontitlefamily'] != "Courier New" && $newarr['arfsectiontitlefamily'] != "Verdana" && $newarr['arfsectiontitlefamily'] != "Geneva" && $newarr['arfsectiontitlefamily'] != "Courier" && $newarr['arfsectiontitlefamily'] != "Monospace" && $newarr['arfsectiontitlefamily'] != "Times" && $newarr['arfsectiontitlefamily'] != "") {
                    if (is_ssl())
                        $googlefontbaseurl = "https://fonts.googleapis.com/css?family=";
                    else
                        $googlefontbaseurl = "http://fonts.googleapis.com/css?family=";
                    $return_css .= "@import url(" . $googlefontbaseurl . urlencode($newarr['arfsectiontitlefamily']) . ");";
                }
                $arf_heading_font_style = '';
                $arf_section_title_font_style_arr = isset($newarr['arfsectiontitleweightsetting']) ? explode(',', $newarr['arfsectiontitleweightsetting']) : array();                
                if (in_array('italic', $arf_section_title_font_style_arr)) {
                    $arf_heading_font_style .= 'font-style:italic; ';
                } else if (in_array('bold', $arf_section_title_font_style_arr)) {
                    $arf_heading_font_style .= ' font-weight:bold;';
                } else if (in_array('underline', $arf_section_title_font_style_arr)) {
                    $arf_heading_font_style .= ' text-decoration:underline;';
                } else if (in_array('strikethrough', $arf_section_title_font_style_arr)) {                    
                    $arf_heading_font_style .= ' text-decoration:line-through !important;';
                } 
       
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
                        $return_css .= ' .ar_main_div_' . $form->id . ' #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container { ' . $field[$custom_css_block] . ' } ';
                    } else if ($custom_css_block == 'css_outer_wrapper' and $field['type'] == 'divider') {
                        $return_css .= ' .ar_main_div_' . $form->id . ' #heading_' . $field['id'] . ' { ' . $field[$custom_css_block] . ' } ';
                    } else if ($custom_css_block == 'css_label' and $field['type'] != 'divider') {
                        $return_css .= ' .ar_main_div_' . $form->id . ' #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container label.arf_main_label { ' . $field[$custom_css_block] . ' } ';
                    } else if ($custom_css_block == 'css_label' and $field['type'] == 'divider') {
                        $return_css .= ' .ar_main_div_' . $form->id . ' #heading_' . $field['id'] . ' h2.arf_sec_heading_field { ' . $field[$custom_css_block] . ' } ';
                    } else if ($custom_css_block == 'css_input_element') {

                        if ($field['type'] == 'textarea') {
                            $return_css .= ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .controls textarea { ' . $field[$custom_css_block] . ' } ';
                        } else if ($field['type'] == 'select' || $field['type'] == ARF_AUTOCOMPLETE_SLUG ) {
                            $return_css .= ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .controls select { ' . $field[$custom_css_block] . ' } ';
                            $return_css .= ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .controls .arfbtn.dropdown-toggle { ' . $field[$custom_css_block] . ' } ';
                        } else if ($field['type'] == 'radio') {
                            $return_css .= ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .arf_radiobutton label { ' . $field[$custom_css_block] . ' } ';
                        } else if ($field['type'] == 'checkbox') {
                            $return_css .= ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .arf_checkbox_style label { ' . $field[$custom_css_block] . ' } ';
                        } else if ($field['type'] == 'file') {
                            $return_css .= ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .controls .arfajax-file-upload { ' . $field[$custom_css_block] . ' } ';
                        } else if ($field['type'] == 'colorpicker') {
                            $return_css .= ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .controls .arfcolorpickerfield { ' . $field[$custom_css_block] . ' } ';
                        } else {
                            $return_css .= ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .controls input { ' . $field[$custom_css_block] . ' } ';
                            if ($field['type'] == 'email') {
                                $return_css .= '.ar_main_div_' . $form->id . ' #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container + .confirm_email_container .controls input {' . $field[$custom_css_block] . '}';
                                $return_css .= ' .ar_main_div_' . $form->id . ' #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container + .confirm_email_container .arf_prefix_suffix_wrapper{ ' . $field[$custom_css_block] . ' }';
                            }
                            if ($field['type'] == 'password') {
                                $return_css .= '.ar_main_div_' . $form->id . ' #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container + .confirm_password_container .controls input{ ' . $field[$custom_css_block] . '}';
                                $return_css .= ' .ar_main_div_' . $form->id . ' #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container + .confirm_password_container .arf_prefix_suffix_wrapper { ' . $field[$custom_css_block] . ' } ';
                            }
                            $return_css .= ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .arf_prefix_suffix_wrapper { ' . $field[$custom_css_block] . ' } ';
                        }
                    } else if ($custom_css_block == 'css_description' and $field['type'] != 'divider') {
                        $return_css .= ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .arf_field_description { ' . $field[$custom_css_block] . ' } ';
                    } else if ($custom_css_block == 'css_description' and $field['type'] == 'divider') {
                        $return_css .= ' .ar_main_div_' . $form->id . '  #heading_' . $field['id'] . ' .arf_heading_description { ' . $field[$custom_css_block] . ' } ';
                    } else if ($custom_css_block == 'css_add_icon' and $field['type'] != 'divider') {
                        $return_css .= '.ar_main_div_' . $form->id . ' #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .arf_prefix,
.ar_main_div_' . $form->id . ' #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .arf_suffix { ' . $field[$custom_css_block] . ' } ';
                        if ($field['type'] == 'email') {
                            $return_css .= '.ar_main_div_' . $form->id . ' .arf_confirm_email_field_' . $field['id'] . ' .arf_prefix,
.ar_main_div_' . $form->id . ' .arf_confirm_email_field_' . $field['id'] . ' .arf_suffix {' . $field[$custom_css_block] . ' } ';
                        }
                        if ($field['type'] == 'password') {
                            $return_css .= '.ar_main_div_' . $form->id . ' .arf_confirm_password_field_' . $field['id'] . ' .arf_prefix,
.ar_main_div_' . $form->id . ' .arf_confirm_password_field_' . $field['id'] . ' .arf_suffix {' . $field[$custom_css_block] . ' } ';
                        }
                    }

                    do_action('arf_add_css_from_outside', $field, $custom_css_block, $arf_data_uniq_id);
                }
            }

        }
        $return_css .= '</style>';
        return $return_css;    
    }

    function arf_get_form_style_for_preview($form, $id, $fields, $arf_data_uniq_id = '') {

        global $arf_loaded_form_unique_id_array, $arfieldhelper, $arrecordhelper, $arfform, $armainhelper, $arformcontroller;
        $return_css = '';
        $type = '';
        if ($arf_data_uniq_id == '') {
            $arf_data_uniq_id = rand(1, 99999);
            if (empty($arf_data_uniq_id) || $arf_data_uniq_id == '') {
                $arf_data_uniq_id = $id;
            }

            if ($type != '') {
                if ($position != '') {
                    $arf_loaded_form_unique_id_array[$id]['type'][$type][$position][] = $arf_data_uniq_id;
                } else {
                    $arf_loaded_form_unique_id_array[$id]['type'][$type][] = $arf_data_uniq_id;
                }
            } else {
                $arf_loaded_form_unique_id_array[$id]['normal'][] = $arf_data_uniq_id;
            }
        }

        $css_data_arr = $form->form_css;

        $arr = maybe_unserialize($css_data_arr);

        $newarr = array();
        $newarr = $arr;
        
        $return_css .= '<style type="text/css" id="arf_form_'.$id.'" data-form-unique-id="'.$arf_data_uniq_id.'" >';

            $form->form_css = maybe_unserialize($form->form_css);

            $loaded_field = isset($form->options['arf_loaded_field']) ? $form->options['arf_loaded_field'] : array();
            $return_css .= stripslashes_deep(get_option('arf_global_css'));
            $form->options['arf_form_other_css'] = $arformcontroller->br2nl($form->options['arf_form_other_css']);
            $return_css .= $armainhelper->esc_textarea($form->options['arf_form_other_css']);

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
                $return_css .= '  
                .arf_form .arf_smiley_container .popover { background-color: #000000 !important; color:#FFFFFF !important; width:auto; }
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


                .arf_smiley_container .arf_smiley_img:hover,
                .arf_smiley_container .arf_smiley_icon:hover,
                .arf_form .arf_smiley_selected .arf_smiley_img,
                .arf_form .arf_smiley_selected .arf_smiley_icon{
                    -ms-transform: scale(1.10);
                    -moz-transform: scale(1.10);
                    -webkit-transform: scale(1.10);
                    transform: scale(1.10);
                    opacity: 1;
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
                                $return_css .= '.ar_main_div_' . $form->id . '.arf_form_outer_wrapper { ' . $form->options[$custom_css_block_form] . ' } ';
                            if ($arf_form_outer_wrapper1 == '.arfmodal')
                                $return_css .= '#popup-form-' . $form->id . '.arfmodal{ ' . $form->options[$custom_css_block_form] . ' } ';
                        }
                    }
                    else if ($custom_css_block_form == 'arf_form_inner_wrapper') {
                        $arf_form_inner_wrapper_array = explode('|', $custom_css_classes_form);
                        foreach ($arf_form_inner_wrapper_array as $arf_form_inner_wrapper1) {
                            if ($arf_form_inner_wrapper1 == '.arf_fieldset')
                                $return_css .= '.ar_main_div_' . $form->id . ' ' . $arf_form_inner_wrapper1 . ' { ' . $form->options[$custom_css_block_form] . ' } ';
                            if ($arf_form_inner_wrapper1 == '.arfmodal')
                                $return_css .= '.arfmodal .arfmodal-body .ar_main_div_' . $form->id . ' .arf_fieldset { ' . $form->options[$custom_css_block_form] . ' } ';
                        }
                    }
                    else if ($custom_css_block_form == 'arf_form_error_message') {
                        $arf_form_error_message_array = explode('|', $custom_css_classes_form);

                        foreach ($arf_form_error_message_array as $arf_form_error_message1) {
                            $return_css .= '.ar_main_div_' . $form->id . ' ' . $arf_form_error_message1 . ' { ' . $form->options[$custom_css_block_form] . ' } ';
                        }
                    } else {
                        $return_css .= '.ar_main_div_' . $form->id . ' ' . $custom_css_classes_form . ' { ' . $form->options[$custom_css_block_form] . ' } ';
                    }
                }
            }

            foreach ($fields as $field) {
                $field = $this->arfObjtoArray($field);
                $field['id'] = $arfieldhelper->get_actual_id($field['id']);

                if ($field['type'] == 'select' ) {
                    if (isset($field['size']) && $field['size'] != 1) {
                        if ($newarr['auto_width'] != "1") {

                            if (isset($field['field_width']) and $field['field_width'] != '') {

                                $return_css .= '.ar_main_div_' . $field['form_id'] . ' .select_controll_' . $field['id'] . ':not([class*="span"]):not([class*="col-"]):not([class*="form-control"]){width:' . $field['field_width'] . 'px !important;}';
                            }
                        }
                    }
                }else if ($field['type'] == 'time') {
                    if (isset($field['field_width']) and $field['field_width'] != '') {
                        $return_css .= '.ar_main_div_' . $field['form_id'] . ' .time_controll_' . $field['id'] . ':not([class*="span"]):not([class*="col-"]):not([class*="form-control"]){width:' . $field['field_width'] . 'px !important;}';
                    }
                }

                if (isset($field['field_width']) and $field['field_width'] != '') {
                    $return_css .= ' .ar_main_div_' . $form->id . ' #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .help-block { width: ' . $field['field_width'] . 'px; } ';
                }

                if ($field['type'] == 'divider') {

                    if ($form->form_css['arfsectiontitlefamily'] != "Arial" && $form->form_css['arfsectiontitlefamily'] != "Helvetica" && $form->form_css['arfsectiontitlefamily'] != "sans-serif" && $form->form_css['arfsectiontitlefamily'] != "Lucida Grande" && $form->form_css['arfsectiontitlefamily'] != "Lucida Sans Unicode" && $form->form_css['arfsectiontitlefamily'] != "Tahoma" && $form->form_css['arfsectiontitlefamily'] != "Times New Roman" && $form->form_css['arfsectiontitlefamily'] != "Courier New" && $form->form_css['arfsectiontitlefamily'] != "Verdana" && $form->form_css['arfsectiontitlefamily'] != "Geneva" && $form->form_css['arfsectiontitlefamily'] != "Courier" && $form->form_css['arfsectiontitlefamily'] != "Monospace" && $form->form_css['arfsectiontitlefamily'] != "Times" && $form->form_css['arfsectiontitlefamily'] != "") {
                        if (is_ssl())
                            $googlefontbaseurl = "https://fonts.googleapis.com/css?family=";
                        else
                            $googlefontbaseurl = "http://fonts.googleapis.com/css?family=";
                        $return_css .= "@import url(" . $googlefontbaseurl . urlencode($form->form_css['arfsectiontitlefamily']) . ");";
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
                            $arf_heading_font_style .= ' font-weight:100;';
                        }

                    

                    $return_css .= ' .ar_main_div_' . $form->id . ' #heading_' . $field['id'] . ' h2.arf_sec_heading_field { font-family:' . stripslashes($form->form_css['arfsectiontitlefamily']) . '; font-size:' . $form->form_css['arfsectiontitlefontsizesetting'] . 'px !important; ' . $arf_heading_font_style . '}';
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
                            $return_css .= ' .ar_main_div_' . $form->id . ' #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container { ' . $field[$custom_css_block] . ' } ';
                        } else if ($custom_css_block == 'css_outer_wrapper' and $field['type'] == 'divider') {
                            $return_css .= ' .ar_main_div_' . $form->id . ' #heading_' . $field['id'] . ' { ' . $field[$custom_css_block] . ' } ';
                        } else if ($custom_css_block == 'css_label' and $field['type'] != 'divider') {
                            $return_css .= ' .ar_main_div_' . $form->id . ' #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container label.arf_main_label { ' . $field[$custom_css_block] . ' } ';
                        } else if ($custom_css_block == 'css_label' and $field['type'] == 'divider') {
                            $return_css .= ' .ar_main_div_' . $form->id . ' #heading_' . $field['id'] . ' h2.arf_sec_heading_field { ' . $field[$custom_css_block] . ' } ';
                        } else if ($custom_css_block == 'css_input_element') {

                            if ($field['type'] == 'textarea') {
                                $return_css .= ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .controls textarea { ' . $field[$custom_css_block] . ' } ';
                            } else if ($field['type'] == 'select' || $field['type'] == ARF_AUTOCOMPLETE_SLUG ) {
                                $return_css .= ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .controls select { ' . $field[$custom_css_block] . ' } ';
                                $return_css .= ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .controls .arfbtn.dropdown-toggle { ' . $field[$custom_css_block] . ' } ';
                            } else if ($field['type'] == 'radio') {
                                $return_css .= ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .arf_radiobutton label { ' . $field[$custom_css_block] . ' } ';
                            } else if ($field['type'] == 'checkbox') {
                                $return_css .= ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .arf_checkbox_style label { ' . $field[$custom_css_block] . ' } ';
                            } else if ($field['type'] == 'file') {
                                $return_css .= ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .controls .arfajax-file-upload { ' . $field[$custom_css_block] . ' } ';
                            } else if ($field['type'] == 'colorpicker') {
                                $return_css .= ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .controls .arfcolorpickerfield { ' . $field[$custom_css_block] . ' } ';
                            } else {
                                $return_css .= ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .controls input { ' . $field[$custom_css_block] . ' } ';
                                if ($field['type'] == 'email') {
                                    $return_css .= '.ar_main_div_' . $form->id . ' #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container + .confirm_email_container .controls input {' . $field[$custom_css_block] . '}';
                                    $return_css .= ' .ar_main_div_' . $form->id . ' #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container + .confirm_email_container .arf_prefix_suffix_wrapper{ ' . $field[$custom_css_block] . ' }';
                                }
                                if ($field['type'] == 'password') {
                                    $return_css .= '.ar_main_div_' . $form->id . ' #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container + .confirm_password_container .controls input{ ' . $field[$custom_css_block] . '}';
                                    $return_css .= ' .ar_main_div_' . $form->id . ' #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container + .confirm_password_container .arf_prefix_suffix_wrapper { ' . $field[$custom_css_block] . ' } ';
                                }
                                $return_css .= ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .arf_prefix_suffix_wrapper { ' . $field[$custom_css_block] . ' } ';
                            }
                        } else if ($custom_css_block == 'css_description' and $field['type'] != 'divider') {
                            $return_css .= ' .ar_main_div_' . $form->id . '  #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .arf_field_description { ' . $field[$custom_css_block] . ' } ';
                        } else if ($custom_css_block == 'css_description' and $field['type'] == 'divider') {
                            $return_css .= ' .ar_main_div_' . $form->id . '  #heading_' . $field['id'] . ' .arf_heading_description { ' . $field[$custom_css_block] . ' } ';
                        } else if ($custom_css_block == 'css_add_icon' and $field['type'] != 'divider') {
                            $return_css .= '.ar_main_div_' . $form->id . ' #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .arf_prefix,
                                .ar_main_div_' . $form->id . ' #arf_field_' . $field['id'] . '_' . $arf_data_uniq_id . '_container .arf_suffix { ' . $field[$custom_css_block] . ' } ';
                            if ($field['type'] == 'email') {
                                $return_css .= '.ar_main_div_' . $form->id . ' .arf_confirm_email_field_' . $field['id'] . ' .arf_prefix,
                                        .ar_main_div_' . $form->id . ' .arf_confirm_email_field_' . $field['id'] . ' .arf_suffix {' . $field[$custom_css_block] . ' } ';
                            }
                            if ($field['type'] == 'password') {
                                $return_css .= '.ar_main_div_' . $form->id . ' .arf_confirm_password_field_' . $field['id'] . ' .arf_prefix,
                                        .ar_main_div_' . $form->id . ' .arf_confirm_password_field_' . $field['id'] . ' .arf_suffix {' . $field[$custom_css_block] . ' } ';
                            }
                        }

                        do_action('arf_add_css_from_outside', $field, $custom_css_block, $arf_data_uniq_id);
                    }
                }               
            }
            
        $return_css .= '</style>';
        
        return $return_css;
    }

    function arf_prefix_suffix($prefix_suffix, $field) {
        $return_string = '';

        $has_phone_with_utils = false;
        $phone_with_utils_cls = '';
        if(isset($field['phonetype'])){
            if( $field['type'] == 'phone' && $field['phonetype'] == 1){
                $has_phone_with_utils = true;
                $phone_with_utils_cls = 'arf_phone_with_flag';
            }
        }
        if ($prefix_suffix == 'prefix') {
            if ((isset($field['enable_arf_prefix']) && $field['enable_arf_prefix'] == 1 ) || (isset($field['enable_arf_suffix']) && $field['enable_arf_suffix'] == 1)) {
                $return_string .='<div class="arf_prefix_suffix_wrapper '.$phone_with_utils_cls.'">';
                if ($field['enable_arf_prefix'] == 1) {
                    if( $has_phone_with_utils == false ){
                        $return_string .='<span id="arf_prefix_' . $field['field_key'] . '" class="arf_prefix" onclick="arfFocusInputField(this,\''.$field['field_key'].'\');"><i class="' . $field['arf_prefix_icon'] . '"></i></span>';
                    }
                }
            }
        } else {
            if ((isset($field['enable_arf_prefix']) && $field['enable_arf_prefix'] == 1 ) || (isset($field['enable_arf_suffix']) && $field['enable_arf_suffix'] == 1)) {
                if ($field['enable_arf_suffix'] == 1) {
                    $return_string .='<span id="arf_suffix_' . $field['field_key'] . '" class="arf_suffix" onclick="arfFocusInputField(this,\''.$field['field_key'].'\');"><i class="' . $field['arf_suffix_icon'] . '"></i></span>';
                }
                $return_string .='</div>';
            }
        }
        return $return_string;
    }

    function arfObjtoArray($obj) {
        if (is_object($obj)) {
            $obj = get_object_vars($obj);
        }
        if (is_array($obj)) {
            return array_map(array($this, __FUNCTION__), $obj);
        } else {
            return $obj;
        }
    }

    function arfArraytoObj($array) {
        if (is_array($array)) {
            $array = json_decode(json_encode($array));
        }
        return $array;
    }

    function arf_save_preview_data_to_db() {
        $all_previewtabledata_option = get_option('arf_previewoptions');
        $all_previewtabledata_option = maybe_unserialize($all_previewtabledata_option);
        $all_previewtabledata_option = (array) $all_previewtabledata_option;
        if (get_option('arf_previewtabledata_1') == '') {
            update_option('arf_previewtabledata_1', $_POST);
            $all_previewtabledata_option['arf_previewtabledata_1'] = time();
            echo 'arf_previewtabledata_1';
        } else if (get_option('arf_previewtabledata_2') == '') {
            update_option('arf_previewtabledata_2', $_POST);
            $all_previewtabledata_option['arf_previewtabledata_2'] = time();
            echo 'arf_previewtabledata_2';
        } else if (get_option('arf_previewtabledata_3') == '') {
            update_option('arf_previewtabledata_3', $_POST);
            $all_previewtabledata_option['arf_previewtabledata_3'] = time();
            echo 'arf_previewtabledata_3';
        } else if (get_option('arf_previewtabledata_4') == '') {
            update_option('arf_previewtabledata_4', $_POST);
            $all_previewtabledata_option['arf_previewtabledata_4'] = time();
            echo 'arf_previewtabledata_4';
        } else if (get_option('arf_previewtabledata_5') == '') {
            update_option('arf_previewtabledata_5', $_POST);
            $all_previewtabledata_option['arf_previewtabledata_5'] = time();
            echo 'arf_previewtabledata_5';
        } else if (get_option('arf_previewtabledata_6') == '') {
            update_option('arf_previewtabledata_6', $_POST);
            $all_previewtabledata_option['arf_previewtabledata_6'] = time();
            echo 'arf_previewtabledata_6';
        } else if (get_option('arf_previewtabledata_7') == '') {
            update_option('arf_previewtabledata_7', $_POST);
            $all_previewtabledata_option['arf_previewtabledata_7'] = time();
            echo 'arf_previewtabledata_7';
        } else if (get_option('arf_previewtabledata_8') == '') {
            update_option('arf_previewtabledata_8', $_POST);
            $all_previewtabledata_option['arf_previewtabledata_8'] = time();
            echo 'arf_previewtabledata_8';
        } else if (get_option('arf_previewtabledata_9') == '') {
            update_option('arf_previewtabledata_9', $_POST);
            $all_previewtabledata_option['arf_previewtabledata_9'] = time();
            echo 'arf_previewtabledata_9';
        } else {
            $random = rand(11, 9999);
            if (get_option('arf_previewtabledata_' . $random) != '') {
                $random = rand(11, 9999);
            }
            update_option('arf_previewtabledata_' . $random, $_POST);
            $all_previewtabledata_option['arf_previewtabledata_' . $random] = time();
            echo 'arf_previewtabledata_' . $random;
        }

        update_option('arf_previewoptions', $all_previewtabledata_option);
        die();
    }

    function arf_csv_form_function(){
    
     $form_id = isset($_REQUEST['form_id']) ? $_REQUEST['form_id'] : '';
     $data_url =   site_url() . '/index.php?plugin=ARForms&controller=entries&form=' .$form_id. '&arfaction=csv';
     
     echo json_encode(array('url_data'=>$data_url ,'error' => false, 'message' => addslashes(esc_html__('CSV generated successfully.', 'ARForms')))); 
    die();
    }
    
    function arf_delete_form_function() {
        global $wpdb, $MdlDb,$arfform;
        $total_forms = 0;
        $form_id = isset($_REQUEST['form_id']) ? $_REQUEST['form_id'] : '';
        if ($form_id == '') {
            echo json_encode(array('error' => true, 'message' => addslashes(esc_html__('Please select valid form', 'ARForms')),'total_forms' => $total_forms));
            die();
        }

        $result = $arfform->destroy($form_id);
        if ($result) {
            echo json_encode(array('error' => true, 'message' => addslashes(esc_html__('Please select valid form', 'ARForms')),'total_forms' => $total_forms));            
        } else {
            $where = "WHERE 1=1 AND is_template = %d AND (status is NULL OR status = '' OR status = 'published') ";
            $totalRecord = $wpdb->get_results($wpdb->prepare("SELECT COUNT(*) as total_forms FROM " . $MdlDb->forms . " " . $where . " ",0));
            $total_forms = $totalRecord[0]->total_forms;   
            echo json_encode(array('error' => false, 'message' => addslashes(esc_html__('Record is deleted successfully.', 'ARForms')),'total_forms' => $total_forms));            
        }

        die();
    }

    function arf_change_input_style() {
        $form_id = $id = isset($_POST['form_id']) ? $_POST['form_id'] : '';
        $style = isset($_POST['style']) ? $_POST['style'] : 'material';
        $styling_opts = isset($_POST['styling_opts']) ? json_decode(stripslashes_deep($_POST['styling_opts']),true) : array();

        $field_order = isset($_POST['field_order']) ? json_decode(stripslashes_deep($_POST['field_order']), true) : '';
        $field_resize_width = isset($_POST['field_resize_width']) ? json_decode(stripslashes_deep($_POST['field_resize_width']), true) : '';
        if ($form_id == '' || $form_id < 100) {
            $return['error'] = true;
            echo json_encode($return);
            die();
        }
        global $wpdb, $MdlDb;
        $unsaved_fields = (isset($_REQUEST['extra_fields']) && $_REQUEST['extra_fields'] != '' ) ? json_decode(stripslashes_deep($_REQUEST['extra_fields']), true) : array();
        $arf_fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM `" . $MdlDb->fields . "` WHERE `form_id` = %d", $form_id), ARRAY_A);
        $arf_is_page_break_no = 0;
        asort($field_order);
        $arf_sorted_fields = array();
        foreach ($field_order as $field_id => $order) {
            if(is_int($field_id))
            {
                foreach ($arf_fields as $field) {
                    if ($field_id == $field['id'] && !array_key_exists($field_id,$unsaved_fields)) {
                        $arf_sorted_fields[] = $field;
                    }
                }
            }
            else 
            {
                $arf_sorted_fields[] = $field_id;
            }
        }
        
        if (isset($arf_sorted_fields)) {
            $arf_fields = $arf_sorted_fields;
        }        
        $field_data = file_get_contents(VIEWS_PATH . '/arf_editor_data.json');

        $field_data_obj = json_decode($field_data);
        $return['error'] = false;
        $content = "";

        
        
        if (!empty($unsaved_fields)) {
            $arf_sorted_unsave_fields = array();
            foreach ($field_order as $field_id => $order) {
                foreach ($unsaved_fields as $fid => $field_data) {
                    if ($field_id == $fid) {
                        $arf_sorted_unsave_fields[$fid] = $field_data;
                    }
                }
            }     

            $unsaved_fields = $arf_sorted_unsave_fields;
            
            $temp_fields = array();
            foreach ($unsaved_fields as $key => $value) {
                $opts = json_decode($value, true);
                if (json_last_error() != JSON_ERROR_NONE) {
                    $opts = maybe_unserialize($value);
                }
                foreach ($opts as $k => $val) {
                    if($k == 'key'){
                        $temp_fields[$key]['field_key'] = $val;         
                    } else if ($k == 'options' || $k == 'default_value') {
                        $temp_fields[$key][$k] = ($val != '' ) ? json_encode($val) : '';
                    } else {
                        $temp_fields[$key][$k] = $val;
                    }
                }
                $temp_fields[$key]['field_options'] = $value;
                $temp_fields[$key]['id'] = $key;                
            }            
            $arf_fields = array_merge($arf_fields, $temp_fields);
            
        }        
        $arf_sorted_fields_new = array();
        foreach ($field_order as $field_id => $order) {
            if(is_int($field_id))
            {
                foreach ($arf_fields as $field) {
                    if (isset($field['id']) && ($field_id == $field['id'])) {
                        $arf_sorted_fields_new[] = $field;
                    }
                }
            }
            else 
            {
                $arf_sorted_fields_new[] = $field_id;
            }
        }
        $arf_fields = $arf_sorted_fields_new;
        $class_array = array();
        $arf_field_counter = 1;
        $index_arf_fields = 0;
        if (is_array($styling_opts) && !empty($styling_opts) && count($styling_opts) > 0) {
            $form_css = array();
            $form_css['arfmainformwidth'] = isset($styling_opts['arffw']) ? $styling_opts['arffw'] : '';
            $form_css['form_width_unit'] = isset($styling_opts['arffu']) ? $styling_opts['arffu'] : '';
            $form_css['text_direction'] = isset($styling_opts['arftds']) ? $styling_opts['arftds'] : '';
            $form_css['form_align'] = isset($styling_opts['arffa']) ? $styling_opts['arffa'] : '';
            $form_css['arfmainfieldsetpadding'] = isset($styling_opts['arfmfsp']) ? $styling_opts['arfmfsp'] : '';
            $form_css['form_border_shadow'] = isset($styling_opts['arffbs']) ? $styling_opts['arffbs'] : '';
            $form_css['fieldset'] = isset($styling_opts['arfmfis']) ? $styling_opts['arfmfis'] : '';
            $form_css['arfmainfieldsetradius'] = isset($styling_opts['arfmfsr']) ? $styling_opts['arfmfsr'] : '';
            $form_css['arfmainfieldsetcolor'] = isset($styling_opts['arfmfsc']) ? $styling_opts['arfmfsc'] : '';
            $form_css['arfmainformbordershadowcolorsetting'] = isset($styling_opts['arffboss']) ? $styling_opts['arffboss'] : '';
            $form_css['arfmainformtitlecolorsetting'] = isset($styling_opts['arfftc']) ? $styling_opts['arfftc'] : '';
            $form_css['check_weight_form_title'] = isset($styling_opts['arfftws']) ? $styling_opts['arfftws'] : '';
            $form_css['form_title_font_size'] = isset($styling_opts['arfftfss']) ? $styling_opts['arfftfss'] : '';
            $form_css['arfmainformtitlepaddingsetting'] = isset($styling_opts['arfftps']) ? $styling_opts['arfftps'] : '';
            $form_css['arfmainformbgcolorsetting'] = isset($styling_opts['arffbcs']) ? $styling_opts['arffbcs'] : '';
            $form_css['font'] = isset($styling_opts['arfmfs']) ? $styling_opts['arfmfs'] : '';
            $form_css['label_color'] = isset($styling_opts['arflcs']) ? $styling_opts['arflcs'] : '';
            $form_css['weight'] = isset($styling_opts['arfmfws']) ? $styling_opts['arfmfws'] : '';
            $form_css['font_size'] = isset($styling_opts['arffss']) ? $styling_opts['arffss'] : '';
            $form_css['align'] = isset($styling_opts['arffrma']) ? $styling_opts['arffrma'] : '';
            $form_css['position'] = isset($styling_opts['arfmps']) ? $styling_opts['arfmps'] : '';
            $form_css['width'] = isset($styling_opts['arfmws']) ? $styling_opts['arfmws'] : '';
            $form_css['width_unit'] = isset($styling_opts['arfmwu']) ? $styling_opts['arfmwu'] : '';
            $form_css['arfdescfontsizesetting'] = isset($styling_opts['arfdfss']) ? $styling_opts['arfdfss'] : '';
            $form_css['arfdescalighsetting'] = isset($styling_opts['arfdas']) ? $styling_opts['arfdas'] : '';
            $form_css['hide_labels'] = isset($styling_opts['arfhl']) ? $styling_opts['arfhl'] : '';
            $form_css['check_font'] = isset($styling_opts['arfcbfs']) ? $styling_opts['arfcbfs'] : '';
            $form_css['check_weight'] = isset($styling_opts['arfcbws']) ? $styling_opts['arfcbws'] : "";
            $form_css['field_font_size'] = isset($styling_opts['arfffss']) ? $styling_opts['arfffss'] : "";
            $form_css['text_color'] = isset($styling_opts['arftcs']) ? $styling_opts['arftcs'] : "";
            $form_css['border_radius'] = isset($styling_opts['arfmbs']) ? $styling_opts['arfmbs'] : '';
            $form_css['border_color'] = isset($styling_opts['arffmboc']) ? $styling_opts['arffmboc'] : '';
            $form_css['arffieldborderwidthsetting'] = isset($styling_opts['arffbws']) ? $styling_opts['arffbws'] : '';
            $form_css['arffieldborderstylesetting'] = isset($styling_opts['arffbss']) ? $styling_opts['arffbss'] : '';

            $form_css['arf_bg_position_x'] = (isset($styling_opts['arf_bg_position_x']) && $styling_opts['arf_bg_position_x'] != '') ? $styling_opts['arf_bg_position_x'] : "left";
            $form_css['arf_bg_position_y'] = (isset($styling_opts['arf_bg_position_y']) && $styling_opts['arf_bg_position_y'] != '') ? $styling_opts['arf_bg_position_y'] : "top";
        
            $form_css['arf_bg_position_input_x'] = (isset($styling_opts['arf_bg_position_input_x']) && $styling_opts['arf_bg_position_input_x'] != '') ? $styling_opts['arf_bg_position_input_x'] : "";
            $form_css['arf_bg_position_input_y'] = (isset($styling_opts['arf_bg_position_input_y']) && $styling_opts['arf_bg_position_input_y'] != '') ? $_REQUEST['arf_bg_position_input_y'] : "";

            if (isset($styling_opts['arffiu']) and $styling_opts['arffiu'] == '%' and isset($styling_opts['arfmfiws']) and $styling_opts['arfmfiws'] > '100') {
                $form_css['field_width'] = '100';
            } else {
                $form_css['field_width'] = isset($styling_opts['arfmfiws']) ? $styling_opts['arfmfiws'] : '';
            }
            $form_css['field_width_unit'] = isset($styling_opts['arffiu']) ? $styling_opts['arffiu'] : "";
            $form_css['arffieldmarginssetting'] = isset($styling_opts['arffms']) ? $styling_opts['arffms'] : '';
            $form_css['arffieldinnermarginssetting'] = isset($styling_opts['arffims']) ? $styling_opts['arffims'] : "";
            $form_css['bg_color'] = isset($styling_opts['arffmbc']) ? $styling_opts['arffmbc'] : '';
            $form_css['arfbgactivecolorsetting'] = isset($styling_opts['arfbcas']) ? $styling_opts['arfbcas'] : "";
            $form_css['arfborderactivecolorsetting'] = isset($styling_opts['arfbacs']) ? $styling_opts['arfbacs'] : "";
            $form_css['arferrorbgcolorsetting'] = isset($styling_opts['arfbecs']) ? $styling_opts['arfbecs'] : "";
            $form_css['arferrorbordercolorsetting'] = isset($styling_opts['arfboecs']) ? $styling_opts['arfboecs'] : '';
            $form_css['arfradioalignsetting'] = isset($styling_opts['arfras']) ? $styling_opts['arfras'] : "";
            $form_css['arfcheckboxalignsetting'] = isset($styling_opts['arfcbas']) ? $styling_opts['arfcbas'] : '';
            $form_css['auto_width'] = isset($styling_opts['arfautowidthsetting']) ? $styling_opts['arfautowidthsetting'] : '';
            $form_css['arfcalthemename'] = isset($styling_opts['arffths']) ? $styling_opts['arffths'] : '';
            $form_css['arfcalthemecss'] = isset($styling_opts['arffthc']) ? $styling_opts['arffthc'] : "";
            $form_css['date_format'] = isset($styling_opts['arffdaf']) ? $styling_opts['arffdaf'] : '';
            $form_css['arfsubmitbuttontext'] = isset($styling_opts['arfsubmitbuttontext']) ? $styling_opts['arfsubmitbuttontext'] : '';
            $form_css['arfsubmitweightsetting'] = isset($styling_opts['arfsbwes']) ? $styling_opts['arfsbwes'] : '';
            $form_css['arfsubmitbuttonfontsizesetting'] = isset($styling_opts['arfsbfss']) ? $styling_opts['arfsbfss'] : '';
            $form_css['arfsubmitbuttonwidthsetting'] = isset($styling_opts['arfsbws']) ? $styling_opts['arfsbws'] : '';
            $form_css['arfsubmitbuttonheightsetting'] = isset($styling_opts['arfsbhs']) ? $styling_opts['arfsbhs'] : '';
            $form_css['submit_bg_color'] = isset($styling_opts['arfsbbcs']) ? $styling_opts['arfsbbcs'] : "";
            $form_css['arfsubmitbuttonbgcolorhoversetting'] = isset($styling_opts['arfsbchs']) ? $styling_opts['arfsbchs'] : '';
            $form_css['arfsubmitbgcolor2setting'] = isset($styling_opts['arfsbcs']) ? $styling_opts['arfsbcs'] : '';
            $form_css['arfsubmittextcolorsetting'] = isset($styling_opts['arfsbtcs']) ? $styling_opts['arfsbtcs'] : '';
            $form_css['arfsubmitbordercolorsetting'] = isset($styling_opts['arfsbobcs']) ? $styling_opts['arfsbobcs'] : '';
            $form_css['arfsubmitborderwidthsetting'] = isset($styling_opts['arfsbbws']) ? $styling_opts['arfsbbws'] : '';
            $form_css['arfsubmitborderradiussetting'] = isset($styling_opts['arfsbbrs']) ? $styling_opts['arfsbbrs'] : '';
            $form_css['arfsubmitshadowcolorsetting'] = isset($styling_opts['arfsbscs']) ? $styling_opts['arfsbscs'] : '';
            $form_css['arfsubmitbuttonmarginsetting'] = isset($styling_opts['arfsbms']) ? $styling_opts['arfsbms'] : '';
            $form_css['submit_bg_img'] = isset($styling_opts['arfsbis']) ? $styling_opts['arfsbis'] : '';
            $form_css['submit_hover_bg_img'] = isset($styling_opts['arfsbhis']) ? $styling_opts['arfsbhis'] : '';
            $form_css['error_font'] = isset($styling_opts['arfmefs']) ? $styling_opts['arfmefs'] : '';
            $form_css['error_font_other'] = isset($styling_opts['arfmofs']) ? $styling_opts['arfmofs'] : '';
            $form_css['arffontsizesetting'] = isset($styling_opts['arfmefss']) ? $styling_opts['arfmefss'] : '';
            $form_css['arferrorbgsetting'] = isset($styling_opts['arfmebs']) ? $styling_opts['arfmebs'] : '';
            $form_css['arferrortextsetting'] = isset($styling_opts['arfmets']) ? $styling_opts['arfmets'] : '';
            $form_css['arferrorbordersetting'] = isset($styling_opts['arfmebos']) ? $styling_opts['arfmebos'] : '';
            $form_css['arfsucessbgcolorsetting'] = isset($styling_opts['arfmsbcs']) ? $styling_opts['arfmsbcs'] : '';
            $form_css['arfsucessbordercolorsetting'] = isset($styling_opts['arfmsbocs']) ? $styling_opts['arfmsbocs'] : "";
            $form_css['arfsucesstextcolorsetting'] = isset($styling_opts['arfmstcs']) ? $styling_opts['arfmstcs'] : '';
            $form_css['arfformerrorbgcolorsettings'] = isset($styling_opts['arffebgc']) ? $styling_opts['arffebgc'] : '';
            $form_css['arfformerrorbordercolorsettings'] = isset($styling_opts['arffebrdc']) ? $styling_opts['arffebrdc'] : '';
            $form_css['arfformerrortextcolorsettings'] = isset($styling_opts['arffetxtc']) ? $styling_opts['arffetxtc'] : '';
            $form_css['arfsubmitalignsetting'] = isset($styling_opts['arfmsas']) ? $styling_opts['arfmsas'] : '';
            $form_css['checkbox_radio_style'] = isset($styling_opts['arfcrs']) ? $styling_opts['arfcrs'] : '';
            $form_css['bg_color_pg_break'] = isset($styling_opts['arffbcpb']) ? $styling_opts['arffbcpb'] : '';
            $form_css['bg_inavtive_color_pg_break'] = isset($styling_opts['arfbicpb']) ? $styling_opts['arfbicpb'] : "";
            $form_css['text_color_pg_break'] = isset($styling_opts['arfftcpb']) ? $styling_opts['arfftcpb'] : "";
            $form_css['arfmainform_bg_img'] = isset($styling_opts['arfmfbi']) ? $styling_opts['arfmfbi'] : '';
            $form_css['arfmainform_color_skin'] = isset($styling_opts['arfmcs']) ? $styling_opts['arfmcs'] : '';
            $form_css['arfinputstyle'] = isset($styling_opts['arfinpst']) ? $styling_opts['arfinpst'] : 'standard';
            $form_css['arfsubmitfontfamily'] = isset($styling_opts['arfsff']) ? $styling_opts['arfsff'] : '';
            $form_css['arfcommonfont'] = isset($styling_opts['arfcommonfont']) ? $styling_opts['arfcommonfont'] : "Helvetica";
            $form_css['arfmainfieldcommonsize'] = isset($styling_opts['arfmainfieldcommonsize']) ? $styling_opts['arfmainfieldcommonsize'] : '3';
            $form_css['arfdatepickerbgcolorsetting'] = isset($styling_opts['arfdbcs']) ? $styling_opts['arfdbcs'] : '#23b7e5';
            $form_css['arfuploadbtntxtcolorsetting'] = isset($styling_opts['arfuptxt']) ? $styling_opts['arfuptxt'] : '#ffffff';
            $form_css['arfuploadbtnbgcolorsetting'] = isset($styling_opts['arfupbg']) ? $styling_opts['arfupbg'] : '#077BDD';
            $form_css['arfdatepickertextcolorsetting'] = isset($styling_opts['arfdtcs']) ? $styling_opts['arfdtcs'] : '#ffffff';
            $form_css['arfmainfieldsetpadding_1'] = (isset($styling_opts['arfmainfieldsetpadding_1']) && $styling_opts['arfmainfieldsetpadding_1'] != '') ? $styling_opts['arfmainfieldsetpadding_1'] : 0;
            $form_css['arfmainfieldsetpadding_2'] = (isset($styling_opts['arfmainfieldsetpadding_2']) && $styling_opts['arfmainfieldsetpadding_2'] != '') ? $styling_opts['arfmainfieldsetpadding_2'] : 0;
            $form_css['arfmainfieldsetpadding_3'] = (isset($styling_opts['arfmainfieldsetpadding_3']) && $styling_opts['arfmainfieldsetpadding_3'] != '') ? $styling_opts['arfmainfieldsetpadding_3'] : 0;
            $form_css['arfmainfieldsetpadding_4'] = (isset($styling_opts['arfmainfieldsetpadding_4']) && $styling_opts['arfmainfieldsetpadding_4'] != '') ? $styling_opts['arfmainfieldsetpadding_4'] : 0;
            $form_css['arfmainformtitlepaddingsetting_1'] = (isset($styling_opts['arfformtitlepaddingsetting_1']) && $styling_opts['arfformtitlepaddingsetting_1'] != '') ? $styling_opts['arfformtitlepaddingsetting_1'] : 0;
            $form_css['arfmainformtitlepaddingsetting_2'] = (isset($styling_opts['arfformtitlepaddingsetting_2']) && $styling_opts['arfformtitlepaddingsetting_2'] != '') ? $styling_opts['arfformtitlepaddingsetting_2'] : 0;
            $form_css['arfmainformtitlepaddingsetting_3'] = (isset($styling_opts['arfformtitlepaddingsetting_3']) && $styling_opts['arfformtitlepaddingsetting_3'] != '') ? $styling_opts['arfformtitlepaddingsetting_3'] : 0;
            $form_css['arfmainformtitlepaddingsetting_4'] = (isset($styling_opts['arfformtitlepaddingsetting_4']) && $styling_opts['arfformtitlepaddingsetting_4'] != '') ? $styling_opts['arfformtitlepaddingsetting_4'] : 0;
            $form_css['arffieldinnermarginssetting_1'] = (isset($styling_opts['arffieldinnermarginsetting_1']) && $styling_opts['arffieldinnermarginsetting_1'] != '') ? $styling_opts['arffieldinnermarginsetting_1'] : 0;
            $form_css['arffieldinnermarginssetting_2'] = (isset($styling_opts['arffieldinnermarginsetting_2']) && $styling_opts['arffieldinnermarginsetting_2'] != '') ? $styling_opts['arffieldinnermarginsetting_2'] : 0;
            $form_css['arffieldinnermarginssetting_3'] = (isset($styling_opts['arffieldinnermarginsetting_3']) && $styling_opts['arffieldinnermarginsetting_3'] != '') ? $styling_opts['arffieldinnermarginsetting_3'] : 0;
            $form_css['arffieldinnermarginssetting_4'] = (isset($styling_opts['arffieldinnermarginsetting_4']) && $styling_opts['arffieldinnermarginsetting_4'] != '') ? $styling_opts['arffieldinnermarginsetting_4'] : 0;
            $form_css['arfsubmitbuttonmarginsetting_1'] = (isset($styling_opts['arfsubmitbuttonmarginsetting_1']) && $styling_opts['arfsubmitbuttonmarginsetting_1'] != '') ? $styling_opts['arfsubmitbuttonmarginsetting_1'] : 0;
            $form_css['arfsubmitbuttonmarginsetting_2'] = (isset($styling_opts['arfsubmitbuttonmarginsetting_2']) && $styling_opts['arfsubmitbuttonmarginsetting_2'] != '') ? $styling_opts['arfsubmitbuttonmarginsetting_2'] : 0;
            $form_css['arfsubmitbuttonmarginsetting_3'] = (isset($styling_opts['arfsubmitbuttonmarginsetting_3']) && $styling_opts['arfsubmitbuttonmarginsetting_3'] != '') ? $styling_opts['arfsubmitbuttonmarginsetting_3'] : 0;
            $form_css['arfsubmitbuttonmarginsetting_4'] = (isset($styling_opts['arfsubmitbuttonmarginsetting_4']) && $styling_opts['arfsubmitbuttonmarginsetting_4'] != '') ? $styling_opts['arfsubmitbuttonmarginsetting_4'] : 0;
            $form_css['arfsectionpaddingsetting_1'] = (isset($styling_opts['arfsectionpaddingsetting_1']) && $styling_opts['arfsectionpaddingsetting_1'] != '') ? $styling_opts['arfsectionpaddingsetting_1'] : 0;
            $form_css['arfsectionpaddingsetting_2'] = (isset($styling_opts['arfsectionpaddingsetting_2']) && $styling_opts['arfsectionpaddingsetting_2'] != '') ? $styling_opts['arfsectionpaddingsetting_2'] : 0;
            $form_css['arfsectionpaddingsetting_3'] = (isset($styling_opts['arfsectionpaddingsetting_3']) && $styling_opts['arfsectionpaddingsetting_3'] != '') ? $styling_opts['arfsectionpaddingsetting_3'] : 0;
            $form_css['arfsectionpaddingsetting_4'] = (isset($styling_opts['arfsectionpaddingsetting_4']) && $styling_opts['arfsectionpaddingsetting_4'] != '') ? $styling_opts['arfsectionpaddingsetting_4'] : 0;
            $form_css['arfcheckradiostyle'] = isset($styling_opts['arfcksn']) ? $styling_opts['arfcksn'] : '';
            $form_css['arfcheckradiocolor'] = isset($styling_opts['arfcksc']) ? $styling_opts['arfcksc'] : '';
            $form_css['arf_checked_checkbox_icon'] = isset($styling_opts['arf_checkbox_icon']) ? $styling_opts['arf_checkbox_icon'] : '';
            $form_css['enable_arf_checkbox'] = isset($styling_opts['enable_arf_checkbox']) ? $styling_opts['enable_arf_checkbox'] : "";
            $form_css['arf_checked_radio_icon'] = isset($styling_opts['arf_radio_icon']) ? $styling_opts['arf_radio_icon'] : '';
            $form_css['enable_arf_radio'] = isset($styling_opts['enable_arf_radio']) ? $styling_opts['enable_arf_radio'] : '';
            $form_css['checked_checkbox_icon_color'] = isset($styling_opts['cbscol']) ? $styling_opts['cbscol'] : "";
            $form_css['checked_radio_icon_color'] = isset($styling_opts['rbscol']) ? $styling_opts['rbscol'] : '';
            $form_css['arferrorstyle'] = isset($styling_opts['arfest']) ? $styling_opts['arfest'] : '';
            $form_css['arferrorstylecolor'] = isset($styling_opts['arfestc']) ? $styling_opts['arfestc'] : '';
            $form_css['arferrorstylecolor2'] = isset($styling_opts['arfestc2']) ? $styling_opts['arfestc2'] : '';
            $form_css['arferrorstyleposition'] = isset($styling_opts['arfestbc']) ? $styling_opts['arfestbc'] : '';
            $form_css['arfstandarderrposition'] = isset($styling_opts['arfstndrerr']) ? $styling_opts['arfstndrerr'] : 'relative';
            $form_css['arfformtitlealign'] = isset($styling_opts['arffta']) ? $styling_opts['arffta'] : '';
            $form_css['arfsubmitautowidth'] = isset($styling_opts['arfsbaw']) ? $styling_opts['arfsbaw'] : '';
            $form_css['arftitlefontfamily'] = isset($styling_opts['arftff']) ? $styling_opts['arftff'] : '';
            $form_css['bar_color_survey'] = isset($styling_opts['arfbcs']) ? $styling_opts['arfbcs'] : '';
            $form_css['bg_color_survey'] = isset($styling_opts['arfbgcs']) ? $styling_opts['arfbgcs'] : "";
            $form_css['text_color_survey'] = isset($styling_opts['arfftcs']) ? $styling_opts['arfftcs'] : '';
            $form_css['arfsectionpaddingsetting'] = isset($styling_opts['arfscps']) ? $styling_opts['arfscps'] : '';
            if (isset($styling_opts['arfmainform_opacity']) and $styling_opts['arfmainform_opacity'] > 1) {
                $form_css['arfmainform_opacity'] = '1';
            } else {
                $form_css['arfmainform_opacity'] = isset($styling_opts['arfmainform_opacity']) ? $styling_opts['arfmainform_opacity'] : '';
            }

            if (isset($styling_opts['arfplaceholder_opacity']) and $styling_opts['arfplaceholder_opacity'] > 1) {
                $form_css['arfplaceholder_opacity'] = '1';
            } else {
                $form_css['arfplaceholder_opacity'] = isset($styling_opts['arfplaceholder_opacity']) ? $styling_opts['arfplaceholder_opacity'] : '0.50';
            }
            $form_css['arfmainfield_opacity'] = isset($styling_opts['arfmfo']) ? $styling_opts['arfmfo'] : "";
            $form_css['arf_req_indicator'] = isset($styling_opts['arfrinc']) ? $styling_opts['arfrinc'] : "0";
            $form_css['prefix_suffix_bg_color'] = isset($styling_opts['pfsfsbg']) ? $styling_opts['pfsfsbg'] : '';
            $form_css['prefix_suffix_icon_color'] = isset($styling_opts['pfsfscol']) ? $styling_opts['pfsfscol'] : "";
            $form_css['arf_tooltip_bg_color'] = isset($styling_opts['arf_tooltip_bg_color']) ? $styling_opts['arf_tooltip_bg_color'] : "";
            $form_css['arf_tooltip_font_color'] = isset($styling_opts['arf_tooltip_font_color']) ? $styling_opts['arf_tooltip_font_color'] : "";
            $form_css['arf_tooltip_width'] = isset($styling_opts['arf_tooltip_width']) ? $styling_opts['arf_tooltip_width'] : "";
            $form_css['arf_tooltip_position'] = isset($styling_opts['arf_tooltip_position']) ? $styling_opts['arf_tooltip_position'] : "";
            $form_css['arfsectiontitlefamily'] = isset($styling_opts['arfsectiontitlefamily']) ? $styling_opts['arfsectiontitlefamily'] : "Helvetica";
            $form_css['arfsectiontitlefontsizesetting'] = isset($styling_opts['arfsectiontitlefontsizesetting']) ? $styling_opts['arfsectiontitlefontsizesetting'] : "16";
            $form_css['arfsectiontitleweightsetting'] = isset($styling_opts['arfsectiontitleweightsetting']) ? $styling_opts['arfsectiontitleweightsetting'] : "";
            $form_css['arfsubmitbuttonstyle'] = isset($styling_opts['arfsubmitbuttonstyle']) ? $styling_opts['arfsubmitbuttonstyle'] : 'border';
            $form_css['arf_divider_inherit_bg'] = isset($styling_opts['arf_divider_inherit_bg']) ? $styling_opts['arf_divider_inherit_bg'] : 0;
            $form_css['arfformsectionbackgroundcolor'] = isset($styling_opts['arfsecbg']) ? $styling_opts['arfsecbg'] : '';
            $form_css['arfmainbasecolor'] = isset($styling_opts['arfmbsc']) ? $styling_opts['arfmbsc'] : '';
            $form_css['arferrorbordercolorsetting'] = $form_css['arfmainbasecolor'];
            $form_css['arflikebtncolor'] = isset($styling_opts['albclr']) ? $styling_opts['albclr'] : '';
            $form_css['arfdislikebtncolor'] = isset($styling_opts['adlbclr']) ? $styling_opts['adlbclr'] : '';
            $form_css['arfstarratingcolor'] = isset($styling_opts['asclcl']) ? $styling_opts['asclcl'] : '';
            $form_css['arfsliderselectioncolor'] = isset($styling_opts['asldrsl']) ? $styling_opts['asldrsl'] : '';
            $form_css['arfslidertrackcolor'] = isset($styling_opts['asltrcl']) ? $styling_opts['asltrcl'] : '';
            $new_values = array();
            foreach($form_css as $k => $val ){
                $new_values[$k] = $val;
            }
            $css_filename = FORMPATH.'/core/css_create_main.php';
            if( $style == 'material' ){
                $css_filename = FORMPATH.'/core/css_create_materialize.php';
            } else {
                $css_filename = FORMPATH.'/core/css_create_main.php';
            }
            ob_start();
            $use_saved = true;
            include $css_filename;
            $css = ob_get_contents();
            $css = str_replace('##','#',$css);
            $return['css'] = $css;
            ob_end_clean();
        }
        $frm_css = $new_values;
        $data['form_css'] = maybe_serialize($frm_css);        
        foreach ($arf_fields as $field) {
            $field['name'] = $this->arf_html_entity_decode($field['name'],true);
            if(is_array($field))
            {
                $field['form_id'] = $form_id;
                if ($field['type'] == 'break' && $arf_is_page_break_no == 0) {
                    $field['page_break_first_use'] = 1;
                    $arf_is_page_break_no++;
                }
                $field_name = "item_meta[" . $field['id'] . "]";
                $has_field_opt = false;
                if (isset($field['options']) && $field['options'] != '' && !empty($field['options'])) {
                    $has_field_opt = true;
                    $field_options_db = @json_decode($field['options'], true);
                    if (json_last_error() != JSON_ERROR_NONE) {
                        $field_options_db = maybe_unserialize($field['options'], true);
                    }
                }

                $field_opt = json_decode($field['field_options'], true);
                $class = (isset($field_opt['inner_class']) && $field_opt['inner_class']) ? $field_opt['inner_class'] : 'arf_1col';
                array_push($class_array,$field_opt['inner_class']);
            
                if (json_last_error() != JSON_ERROR_NONE) {
                    $field_opt = maybe_unserialize($field['field_options']);
                }

                if (isset($field_opt) && !empty($field_opt)) {
                    foreach ($field_opt as $k => $field_opt_val) {
                        if ($k != 'options' ) {
                            $field[$k] = $this->arf_html_entity_decode($field_opt_val);
                        } else {
                            if ($has_field_opt == true && $k == 'options') {
                                $field[$k] = $field_options_db;
                            }
                        }
                    }
                }
            }

            $filename = VIEWS_PATH . '/arf_field_editor.php';
            ob_start();
            include $filename;
            $content .= ob_get_contents();
            ob_end_clean();
            unset($field);
            unset($field_name);
            $arf_field_counter++;
        }
        $return['content'] = $content;
        echo json_encode($return);
        die();
    }

    function arfSearchArray($id, $column, $array) {
        foreach ($array as $key => $val) {
            if ($val[$column] == $id) {
                return $key;
            }
        }
        return null;
    }

    function arf_html_entity_decode($data){
        if( is_array($data) ){
            return array_map(array($this, __FUNCTION__), $data);
        } else if(is_object($data) ) {
            $data = $this->arfObjtoArray($data);
            return array_map(array($this, __FUNCTION__), $data);
        } else {
            return html_entity_decode($data);
        }
    }

    function arfHtmlEntities($data,$addslashes = false){
        if( is_array($data) ){
            return array_map(array($this, __FUNCTION__), $data);
        } else if(is_object($data) ) {
            $data = $this->arfObjtoArray($data);
            return array_map(array($this, __FUNCTION__), $data);
        } else {
            if( $addslashes ){
                return addslashes(htmlentities($data));
            } else {
                return htmlentities($data);
            }
        }         
    }

    function arfgetfieldfromid($field_id, $field_values, $type = 'object'){
        if( $field_id == '' || $field_id < 1 ){
            return false;
        }

        if( preg_match('/(\d+)\.(\d+)/',$field_id,$match )){
            $field_id = $match[1];
        }

        if( is_object($field_values) ){
            $field_values = $this->arfObjtoArray($field_values);
        }

        $newObject = array();
        $key = $this->arfSearchArray($field_id,'id',$field_values);
        $object = isset($field_values[$key]) ? $field_values[$key] : array();
        if( $type == 'object' ) {
            $object = $this->arfArraytoObj($object);
        }
        return $object;
    }

    function arfcode_to_country($code ='',$country_name ='',$all= fale){
    $countryList = array(
        'AF' => 'Afghanistan',
        'AX' => 'Aland Islands',
        'AL' => 'Albania',
        'DZ' => 'Algeria',
        'AS' => 'American Samoa',
        'AD' => 'Andorra',
        'AO' => 'Angola',
        'AI' => 'Anguilla',
        'AQ' => 'Antarctica',
        'AG' => 'Antigua and Barbuda',
        'AR' => 'Argentina',
        'AM' => 'Armenia',
        'AW' => 'Aruba',
        'AU' => 'Australia',
        'AT' => 'Austria',
        'AZ' => 'Azerbaijan',
        'BS' => 'Bahamas',
        'BH' => 'Bahrain',
        'BD' => 'Bangladesh',
        'BB' => 'Barbados',
        'BY' => 'Belarus',
        'BE' => 'Belgium',
        'BZ' => 'Belize',
        'BJ' => 'Benin',
        'BM' => 'Bermuda',
        'BT' => 'Bhutan',
        'BO' => 'Bolivia',
        'BQ' => 'Bonaire',
        'BA' => 'Bosnia and Herzegovina',
        'BW' => 'Botswana',
        'BV' => 'Bouvet Island',
        'BR' => 'Brazil',
        'IO' => 'British Indian Ocean Territory',
        'VG' => 'British Virgin Islands',
        'BN' => 'Brunei Darussalam',
        'BG' => 'Bulgaria',
        'BF' => 'Burkina Faso',
        'BI' => 'Burundi',
        'KH' => 'Cambodia',
        'CM' => 'Cameroon',
        'CA' => 'Canada',
        'CV' => 'Cape Verde',
        'KY' => 'Cayman Islands',
        'CF' => 'Central African Republic',
        'TD' => 'Chad',
        'CL' => 'Chile',
        'CN' => 'China',
        'CX' => 'Christmas Island',
        'CC' => 'Cocos (Keeling) Islands',
        'CO' => 'Colombia',
        'KM' => 'Comoros',
        'CD' => 'Democratic Republic of the Congo',
        'CG' => 'Congo',
        'CK' => 'Cook Islands',
        'CR' => 'Costa Rica',
        'CI' => 'Cote d\'Ivoire',
        'HR' => 'Croatia',
        'CU' => 'Cuba',
        'CW' => 'Curacao',
        'CY' => 'Cyprus',
        'CZ' => 'Czech Republic',
        'DK' => 'Denmark',
        'DJ' => 'Djibouti',
        'DM' => 'Dominica',
        'DO' => 'Dominican Republic',
        'EC' => 'Ecuador',
        'EG' => 'Egypt',
        'SV' => 'El Salvador',
        'GQ' => 'Equatorial Guinea',
        'ER' => 'Eritrea',
        'EE' => 'Estonia',
        'ET' => 'Ethiopia',
        'FO' => 'Faroe Islands',
        'FK' => 'Falkland Islands (Malvinas)',
        'FJ' => 'Fiji',
        'FI' => 'Finland',
        'FR' => 'France',
        'GF' => 'French Guiana',
        'PF' => 'French Polynesia',
        'TF' => 'French Southern Territories',
        'GA' => 'Gabon',
        'GM' => 'Gambia',
        'GE' => 'Georgia',
        'DE' => 'Germany',
        'GH' => 'Ghana',
        'GI' => 'Gibraltar',
        'GR' => 'Greece',
        'GL' => 'Greenland',
        'GD' => 'Grenada',
        'GP' => 'Guadeloupe',
        'GU' => 'Guam',
        'GT' => 'Guatemala',
        'GG' => 'Guernsey',
        'GN' => 'Guinea',
        'GW' => 'Guinea-Bissau',
        'GY' => 'Guyana',
        'HT' => 'Haiti',
        'HM' => 'Heard Island and McDonald Islands',
        'VA' => 'Holy See (Vatican City State)',
        'HN' => 'Honduras',
        'HK' => 'Hong Kong',
        'HU' => 'Hungary',
        'IS' => 'Iceland',
        'IN' => 'India',
        'ID' => 'Indonesia',
        'IR' => 'Iran',
        'IQ' => 'Iraq',
        'IE' => 'Ireland',
        'IM' => 'Isle of Man',
        'IL' => 'Israel',
        'IT' => 'Italy',
        'JM' => 'Jamaica',
        'JP' => 'Japan',
        'JE' => 'Jersey',
        'JO' => 'Jordan',
        'KZ' => 'Kazakhstan',
        'KE' => 'Kenya',
        'KI' => 'Kiribati',
        'KP' => 'Korea, Democratic People\'s Republic of',
        'KR' => 'Korea, Republic of',
        'KW' => 'Kuwait',
        'KG' => 'Kyrgyzstan',
        'LA' => 'Lao People\'s Democratic Republic',
        'LV' => 'Latvia',
        'LB' => 'Lebanon',
        'LS' => 'Lesotho',
        'LR' => 'Liberia',
        'LY' => 'Libyan',
        'LI' => 'Liechtenstein',
        'LT' => 'Lithuania',
        'LU' => 'Luxembourg',
        'MO' => 'Macao',
        'MK' => 'Macedonia, the Former Yugoslav Republic of',
        'MG' => 'Madagascar',
        'MW' => 'Malawi',
        'MY' => 'Malaysia',
        'MV' => 'Maldives',
        'ML' => 'Mali',
        'MT' => 'Malta',
        'MH' => 'Marshall Islands',
        'MQ' => 'Martinique',
        'MR' => 'Mauritania',
        'MU' => 'Mauritius',
        'YT' => 'Mayotte',
        'MX' => 'Mexico',
        'FM' => 'Micronesia, Federated States of',
        'MD' => 'Moldova, Republic of',
        'MC' => 'Monaco',
        'MN' => 'Mongolia',
        'ME' => 'Montenegro',
        'MS' => 'Montserrat',
        'MA' => 'Morocco',
        'MZ' => 'Mozambique',
        'MM' => 'Myanmar',
        'NA' => 'Namibia',
        'NR' => 'Nauru',
        'NP' => 'Nepal',
        'AN' => 'Netherlands Antilles',
        'NL' => 'Netherlands',
        'NC' => 'New Caledonia',
        'NZ' => 'New Zealand',
        'NI' => 'Nicaragua',
        'NE' => 'Niger',
        'NG' => 'Nigeria',
        'NU' => 'Niue',
        'NF' => 'Norfolk Island',
        'MP' => 'Northern Mariana Islands',
        'NO' => 'Norway',
        'OM' => 'Oman',
        'PK' => 'Pakistan',
        'PW' => 'Palau',
        'PS' => 'Palestine, State of',
        'PA' => 'Panama',
        'PG' => 'Papua New Guinea',
        'PY' => 'Paraguay',
        'PE' => 'Peru',
        'PH' => 'Philippines',
        'PN' => 'Pitcairn',
        'PL' => 'Poland',
        'PT' => 'Portugal',
        'PR' => 'Puerto Rico',
        'QA' => 'Qatar',
        'RE' => 'Reunion',
        'RO' => 'Romania',
        'RU' => 'Russian Federation',
        'RW' => 'Rwanda',
        'BL' => 'Saint Barthelemy',
        'SH' => 'Saint Helena',
        'KN' => 'Saint Kitts and Nevis',
        'LC' => 'Saint Lucia',
        'MF' => 'Saint Martin (French part)',
        'PM' => 'Saint Pierre and Miquelon',
        'VC' => 'Saint Vincent and the Grenadines',
        'WS' => 'Samoa',
        'SM' => 'San Marino',
        'ST' => 'Sao Tome and Principe',
        'SA' => 'Saudi Arabia',
        'SN' => 'Senegal',
        'RS' => 'Serbia',
        'SC' => 'Seychelles',
        'SL' => 'Sierra Leone',
        'SG' => 'Singapore',
        'SX' => 'Sint Maarten (Dutch part)',
        'SK' => 'Slovakia',
        'SI' => 'Slovenia',
        'SB' => 'Solomon Islands',
        'SO' => 'Somalia',
        'ZA' => 'South Africa',
        'SS' => 'South Sudan',
        'GS' => 'South Georgia and the South Sandwich Islands',
        'ES' => 'Spain',
        'LK' => 'Sri Lanka',
        'SD' => 'Sudan',
        'SR' => 'Suriname',
        'SJ' => 'Svalbard and Jan Mayen',
        'SZ' => 'Swaziland',
        'SE' => 'Sweden',
        'CH' => 'Switzerland',
        'SY' => 'Syrian Arab Republic',
        'TW' => 'Taiwan, Province of China',
        'TJ' => 'Tajikistan',
        'TZ' => 'United Republic of Tanzania',
        'TH' => 'Thailand',
        'TL' => 'Timor-Leste',
        'TG' => 'Togo',
        'TK' => 'Tokelau',
        'TO' => 'Tonga',
        'TT' => 'Trinidad and Tobago',
        'TN' => 'Tunisia',
        'TR' => 'Turkey',
        'TM' => 'Turkmenistan',
        'TC' => 'Turks and Caicos Islands',
        'TV' => 'Tuvalu',
        'UG' => 'Uganda',
        'UA' => 'Ukraine',
        'AE' => 'United Arab Emirates',
        'GB' => 'United Kingdom',
        'US' => 'United States of America',
        'US' => 'United States',
        'UM' => 'United States Minor Outlying Islands',
        'VI' => 'United States Virgin Islands',
        'UY' => 'Uruguay',
        'UZ' => 'Uzbekistan',
        'VU' => 'Vanuatu',
        'VE' => 'Venezuela',
        'VN' => 'Viet Nam',
        'VG' => 'British Virgin Islands',
        'VI' => 'US Virgin Islands',
        'WF' => 'Wallis and Futuna',
        'EH' => 'Western Sahara',
        'YE' => 'Yemen',
        'ZM' => 'Zambia',
        'ZW' => 'Zimbabwe'
    );
    if($all){
        return $countryList;
    }
    if(isset($code))
    {
        return array_search($code,$countryList);        
    }
}
    function arfreturndateformate(){
        $return_array = array();
        $return_array['arfwp_dateformate'] = $wp_format_date = get_option('date_format');
        if ($wp_format_date == 'F j, Y') {
            $date_format_new = 'MMMM D, YYYY';
        } else if($wp_format_date == 'Y-m-d'){
            $date_format_new = 'YYYY-MM-DD';            
        } else if ($wp_format_date == 'm/d/Y') {
            $date_format_new = 'MM/DD/YYYY';            
        } else if ($wp_format_date == 'd/m/Y') {
            $date_format_new = 'DD/MM/YYYY';            
        } else if ($wp_format_date == 'Y/m/d') {
            $date_format_new = 'DD/MM/YYYY';            
        } else {
            $date_format_new = 'MM/DD/YYYY';            
        }
        $return_array['arfjs_dateformate'] = $date_format_new;
        return $return_array;
    }



    function arf_after_submit_sucess_outside_function($return_script,$form) {
        $arf_form_option = isset($form->options) ? $form->options : '';
        $arf_sub_track_code = isset($arf_form_option['arf_sub_track_code']) ? $arf_form_option['arf_sub_track_code'] : '';
        $arf_submission_tracking_code = trim(rawurldecode(stripslashes_deep($arf_sub_track_code)));
        if($arf_submission_tracking_code!='')
        {
            $return_script .= "<script type='text/javascript'>";
            $return_script .= $arf_submission_tracking_code;
            $return_script .= "</script>";
            return $return_script;
        }
    }

    function arf_load_form_css( $form_id, $inputStyle ){
        global $arfsettings, $arf_jscss_version;
        $arf_db_version = get_option("arf_db_version");
        $wp_upload_dir = wp_upload_dir();
        $upload_main_url = $wp_upload_dir['baseurl'] . '/arforms/maincss';
        $is_material = false;
        $materialize_css = '';
        if( $inputStyle == 'material' ){
            $materialize_css = '_materialize';
            $is_material = true;
        }
        if( is_ssl() ){
            $fid = str_replace("http://", "https://", $upload_main_url . '/maincss' . $materialize_css .'_'. $form_id . '.css?ver='.$arf_jscss_version);
        } else {
            $fid = $upload_main_url . '/maincss' . $materialize_css .'_' . $form_id . '.css?ver='.$arf_jscss_version;
        }
        $return_link = "";
        $stylesheet_handler = 'arfformscss'.$materialize_css.$form_id;
        
        if( $arfsettings->arfmainformloadjscss != 1 && !wp_style_is($stylesheet_handler,'enqueued') ){
            wp_enqueue_style($stylesheet_handler,$fid);
            wp_enqueue_style('arf_material_css',ARFURL.'/materialize/materialize.css');
            wp_enqueue_script('arf_material_script',ARFURL.'/materialize/materialize.js');
        } else {
            $new_key = '';
            global $MdlDb,$armainhelper;
            $unique_key = $armainhelper->get_unique_key($new_key, $MdlDb->forms, 'form_key');
            $return_link .= "<link rel='stylesheet' type='text/css' id='{$stylesheet_handler}-fallback-css' href='{$fid}' />";
            $return_link .= "<link rel='stylesheet' type='text/css' id='arf_materialize_css_fallback_".$unique_key."' href='".ARFURL . "/materialize/materialize.css?ver={$arf_db_version}' />";
            $return_link .= "<script id='arf_materialize_script_fallback' src='".ARFURL . "/materialize/materialize.js?ver={$arf_db_version}'></script>";  
        }
        return $return_link;
    }

    function arf_replace_default_value_shortcode_func($default_value,$field,$form){

        if( '' == $default_value || is_array($default_value) ){
            return $default_value;
        }

        $current_user = wp_get_current_user();

        $pattern = '/(\[arf_current_user_detail(\s+)field\=(\'|\")(.*?)(\'|\")\])/';

        preg_match_all($pattern,$default_value,$matches);

        foreach( $matches as $parent_k => $match ){
            if( isset($match) && is_array($match) && count($match) == 1 ){
                if( $parent_k == 4 ){
                    $meta_key = isset($match[0]) ? $match[0] : '';
                    if( $meta_key == '' ){
                        $default_value = preg_replace($pattern, '', $default_value);
                    } else if( 0 == $current_user->ID || $current_user->ID < 1 ){
                        $default_value = preg_replace($pattern, '', $default_value);
                    } else {
                        $user_obj = get_userdata($current_user->ID);

                        if( isset($user_obj->data->$meta_key) ){
                            $default_value = preg_replace($pattern, $user_obj->data->$meta_key, $default_value);
                        } else {
                            $user_meta = get_user_meta($current_user->ID,$meta_key,true);

                            if( is_array($user_meta) ){
                                $user_meta = implode(',',$user_meta);
                            }
                            $default_value = preg_replace($pattern, $user_meta, $default_value);
                        }
                    }
                }
            } else if( isset($match) && is_array($match) && count($match) > 1 ) {
                if( $parent_k == 4 ){
                    $meta_keys = $match;
                    foreach( $meta_keys as $meta_key){
                        $pattern_new = '/(\[arf_current_user_detail(\s+)field\=(\'|\")'.$meta_key.'(\'|\")\])/';
                        if( $meta_key == '' ){
                            $default_value = preg_replace($pattern_new, '', $default_value);
                        } else if( 0 == $current_user->ID || $current_user->ID < 1 ){
                            $default_value = preg_replace($pattern_new, '', $default_value);
                        } else {
                            $user_obj = get_userdata($current_user->ID);
                            if( isset($user_obj->data->$meta_key) ){
                                $default_value = preg_replace($pattern_new, $user_obj->data->$meta_key, $default_value);
                            } else {
                                $user_meta = get_user_meta($current_user->ID,$meta_key,true);

                                if( is_array($user_meta) ){
                                    $user_meta = implode(',',$user_meta);
                                }
                                $default_value = preg_replace($pattern_new, $user_meta, $default_value);
                            }
                        }
                    }
                }
            } else {
                $default_value = preg_replace($pattern, '', $default_value);
            }
        }


        return $default_value;
    }
}
function sort_callback_event_start($a, $b)
{
    return (int)$a->field_order - (int)$b->field_order;
}


?>