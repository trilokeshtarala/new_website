<?php

class arfieldcontroller {

    function __construct() {

        add_filter('arfdisplayfieldtype', array($this, 'show_normal_field'), 10, 2);

        add_filter('arfdisplayfieldhtml', array($this, 'arfdisplayfieldhtml'), 10, 2);

        add_action('arfdisplayfieldtype1', array($this, 'show_other'), 10, 5);

        add_filter('arffieldtype', array($this, 'change_type'), 15, 2);

        add_filter('arfdisplaysavedfieldvalue', array($this, 'use_field_key_value'), 10, 3);

        add_action('arfdisplayaddedfields', array($this, 'show'));

        add_filter('arfdisplayfieldoptions', array($this, 'display_field_options'));

        add_filter('arfdisplayfieldoptions', array($this, 'display_basic_field_options'));

        
        add_action('arffieldinputhtml', array($this, 'input_html'));

        add_action('arffieldinputhtml', array($this, 'input_fieldhtml'));

        add_filter('arfaddfieldclasses', array($this, 'add_field_class'), 20, 2);

        
        add_action('arfaddradioimagevalues', array($this, 'add_radio_value_opt_label'));

        add_action('arfdatepickerjs', array($this, 'arfdatepickerjs'), 10, 2);

        
        add_action('wp_ajax_arfmakereqfield', array($this, 'mark_required'));

        add_action('wp_ajax_arfupdateajaxoption', array($this, 'arfupdateajaxoption'));

        add_action('wp_ajax_arfdeleteformfield', array($this, 'destroy'));

        add_action('wp_ajax_arfeditorfieldoption', array($this, 'edit_option'));

        add_action('wp_ajax_arfdeletefieldoption', array($this, 'delete_option'));

        add_action('wp_ajax_arfupdatefieldorder', array($this, 'update_order'));

        add_filter('arffieldvaluesaved', array($this, 'check_value'), 50, 3);

        add_filter('arffieldlabelseen', array($this, 'check_label'), 10, 3);

        add_action('wp_ajax_arf_is_prevalidateform_outside', array($this, 'arf_prevalidateform_outside'));

        add_action('wp_ajax_nopriv_arf_is_prevalidateform_outside', array($this, 'arf_prevalidateform_outside'));

        add_action('wp_ajax_arf_is_resetformoutside', array($this, 'arf_resetformoutside'));

        add_action('wp_ajax_nopriv_arf_is_resetformoutside', array($this, 'arf_resetformoutside'));

        add_action('wp_ajax_arf_add_new_preset', array($this, 'arf_add_new_preset'));

        add_action('wp_ajax_arf_save_new_preset', array($this, 'arf_save_new_preset'));

        add_action('wp_ajax_arf_save_field_option', array($this, 'arf_save_field_option'));

        add_action('wp_ajax_arf_field_op_lord_html', array($this, 'arf_field_op_lord_html'));

        add_action('wp_ajax_upload_radio_label_img', array($this, 'arf_upload_radio_label_img'));

        add_action('wp_ajax_arf_save_new_preset_field', array($this, 'arf_save_new_preset_field_function'));
        
    }

    function mark_required() {


        global $arffield;


        $arffield->update($_POST['field'], array('required' => $_POST['required']));


        die();
    }

    function arfupdateajaxoption() {


        global $arffield, $MdlDb;


        $field = $arffield->getOne($_POST['field']);


        $field->field_options = maybe_unserialize($field->field_options);


        foreach (array('clear_on_focus', 'separate_value', 'default_blank') as $val) {


            if (isset($_POST[$val])) {


                $new_val = $_POST[$val];


                if ($val == 'separate_value')
                    $new_val = (isset($field->field_options[$val]) and $field->field_options[$val]) ? 0 : 1;





                $field->field_options[$val] = $new_val;


                unset($new_val);
            }


            unset($val);
        }



        global $wpdb;
        $wpdb->update($MdlDb->fields, array('field_options' => maybe_serialize($field->field_options)), array('id' => $_POST['field']), array('%s'), array('%d'));
        die();
    }

    function &show_normal_field($show, $field_type) {


        if (in_array($field_type, array('hidden', 'user_id', 'break')))
            $show = false;


        return $show;
    }

    function &arfdisplayfieldhtml($show, $field_type) {


        if (in_array($field_type, array('hidden', 'user_id', 'break', 'divider', 'html')))
            $show = false;


        return $show;
    }

    function show_other($field,$fields_form,$form, $total_page = 0, $current_form_data_id = 0) {


        $field_name = "item_meta[$field[id]]";


        require(VIEWS_PATH . '/displayotheroptions.php');
    }

    function &change_type($type, $field) {


        global $arfshowfields;


        if ($type != 'user_id' and ! empty($arfshowfields) and ! in_array($field->id, $arfshowfields) and ! in_array($field->field_key, $arfshowfields))
            $type = 'hidden';


        if ($type == 'website')
            $type = 'url';


        return $type;
    }

    function use_field_key_value($opt, $opt_key, $field) {


        if ((isset($field['use_key']) and $field['use_key']) or ( isset($field['type']) and $field['type'] == 'data'))
            $opt = $opt_key;


        return $opt;
    }

    function show($field) {


        global $arfajaxurl;


        $field_name = "item_meta[" . $field['id'] . "]";


        require(VIEWS_PATH . '/displayfield.php');
    }

    function display_field_options($display) {

        if (isset($display['type']) and $display['type'] != '') {

            switch ($display['type']) {


                case 'user_id':


                case 'hidden':


                    $display['label_position'] = false;


                    $display['description'] = false;


                case 'form':


                    $display['required'] = false;


                    $display['default_blank'] = false;


                    break;


                case 'break':


                    $display['required'] = false;


                    $display['options'] = true;


                    $display['default_blank'] = false;


                    $display['css'] = false;


                    break;


                case 'email':


                case 'url':


                case 'website':


                case 'phone':


                case 'image':


                case 'date':


                case 'number':


                    $display['size'] = true;


                    $display['invalid'] = true;


                    $display['clear_on_focus'] = true;


                    break;


                case 'password':


                    $display['size'] = true;


                    $display['clear_on_focus'] = true;


                    break;


                case 'time':


                    $display['size'] = true;


                    break;


                case 'file':


                    $display['invalid'] = true;


                    $display['size'] = true;


                    break;

                case 'html':


                    $display['label_position'] = false;


                    $display['description'] = false;


                case 'divider':


                    $display['required'] = false;


                    $display['default_blank'] = false;


                    break;
            }
        }

        return $display;
    }

    function display_basic_field_options($display) {

        if (isset($display['type']) and $display['type'] != '') {

            switch ($display['type']) {


                case 'captcha':


                    $display['required'] = false;


                    $display['invalid'] = true;


                    $display['default_blank'] = false;


                    break;


                case 'radio':


                    $display['default_blank'] = false;


                    break;


                case 'text':


                case 'textarea':


                    $display['size'] = true;


                    $display['clear_on_focus'] = true;


                    break;


                case 'select':


                    $display['size'] = true;


                    break;
            }
        }



        return $display;
    }

    function check_value($opt, $opt_key, $field) {


        if (is_array($opt)) {


            if (isset($field['separate_value']) and $field['separate_value']) {


                $opt = isset($opt['value']) ? $opt['value'] : (isset($opt['label']) ? $opt['label'] : reset($opt));
            } else {


                $opt = (isset($opt['label']) ? $opt['label'] : reset($opt));
            }
        }


        return $opt;
    }

    function check_label($opt, $opt_key, $field) {


        if (is_array($opt))
            $opt = (isset($opt['label']) ? $opt['label'] : reset($opt));





        return $opt;
    }


    function input_html($field, $echo = true) {


        global $arfsettings, $arfnovalidate;

        $add_html = '';

        if (isset($field['read_only']) and $field['read_only']) {


            global $arfreadonly;


            if ($arfreadonly == 'disabled' or ( current_user_can('administrator') and is_admin()))
                return;


            $add_html .= ' readonly="readonly" ';
        }

        if( isset($field['max']) && $field['max'] != '' ){
            $add_html .= ' maxlength="'.$field['max'].'" ';
        }


        if ($arfsettings->use_html) {


            if ($field['type'] == 'number') {


                if ($field['maxnum'] != '' && !is_numeric($field['minnum']))
                    $field['minnum'] = 0;


                if ($field['maxnum'] != '' && !is_numeric($field['maxnum']))
                    $field['maxnum'] = 9999999;


                if (isset($field['step']) && !is_numeric($field['step']))
                    $field['step'] = 1;

                if ($field['maxnum'] > 0)
                    $add_html .= ' max="' . $field['maxnum'] . '"';

                if ($field['minnum'] > 0)
                    $add_html .= ' min="' . $field['minnum'] . '"';


            }else if (in_array($field['type'], array('url', 'email'))) {


                if (!$arfnovalidate and isset($field['value']) and $field['default_value'] == $field['value'])
                    $arfnovalidate = true;
            }
        }





        if (isset($field['dependent_fields']) and $field['dependent_fields']) {


            $trigger = ($field['type'] == 'checkbox' or $field['type'] == 'radio') ? 'onclick' : 'onchange';

            $add_html .= ' ' . $trigger . '="frmCheckDependent(this.value,\'' . $field['id'] . '\')"';
        }


        if ($echo)
            echo $add_html;


        return $add_html;
    }

    function add_field_class($class, $field) {


        if ($field['type'] == 'scale' and isset($field['star']) and $field['star'])
            $class .= ' star';


        else if ($field['type'] == 'date')
            $class .= ' frm_date';



        return $class;
    }

    function add_radio_value_opt_label($field) {
        echo '<div class="arfshowfieldclick">';
        echo '<div class="field_' . $field['id'] . '_option_key frm_option_val_label">' . addslashes(esc_html__('Image', 'ARForms')) . '</div>';
        echo '<div class="field_' . $field['id'] . '_option_key frm_option_key_label" style="display:block;">' . addslashes(esc_html__('Saved Value', 'ARForms')) . '</div>';
        echo '</div>';
    }

    function arfdatepickerjs($field_id, $options) {


        if (isset($options['unique'])) {


            global $MdlDb, $wpdb, $arffield;


            $field = $arffield->getOne($options['field_id']);


            $field->field_options = maybe_unserialize($field->field_options);


            $query = "SELECT entry_value FROM $MdlDb->entry_metas WHERE field_id=" . (int) $options['field_id'];


            if (is_numeric($options['entry_id'])) {


                $query .= " and entry_id != " . (int) $options['entry_id'];
            } else {


                $disabled = wp_cache_get($options['field_id'], 'arfuseddates');
            }


            if (!isset($disabled) or ! $disabled)
                $disabled = $wpdb->get_col($query);



            if (isset($post_dates) and $post_dates)
                $disabled = array_merge((array) $post_dates, (array) $disabled);


            $disabled = apply_filters('arfuseddates', $disabled, $field, $options);


            if (!$disabled)
                return;


            if (!is_numeric($options['entry_id']))
                wp_cache_set($options['field_id'], $disabled, 'arfuseddates');


            $formatted = array();


            foreach ($disabled as $dis)
                $formatted[] = date('Y-n-j', strtotime($dis));


            $disabled = $formatted;


            unset($formatted);


            echo ',beforeShowDay: function(date){var m=(date.getMonth()+1),d=date.getDate(),y=date.getFullYear();var disabled=' . json_encode($disabled) . ';if($.inArray(y+"-"+m+"-"+d,disabled) != -1){return [false];} return [true];}';
        }
    }

    function ajax_get_data($entry_id, $field_id, $current_field) {


        global $arfrecordmeta, $arffield, $arrecordhelper, $arfieldhelper;


        $data_field = $arffield->getOne($field_id);


        $current = $arffield->getOne($current_field);


        $entry_value = $arrecordhelper->get_post_or_entry_value($entry_id, $data_field);


        $value = $arfieldhelper->get_display_value($entry_value, $data_field);

        if ($value and ! empty($value))
            echo "<p class='frm_show_it'>" . $value . "</p>\n";



        echo '<input type="hidden" id="field_' . $current->field_key . '" name="item_meta[' . $current_field . ']" value="' . stripslashes(esc_attr($entry_value)) . '"/>';


        die();
    }

    function input_fieldhtml($field, $echo = true) {
        global $arfsettings, $armainhelper;

        $class = '';


        $add_html = '';


        if ($field['type'] == 'date' || $field['type'] == 'phone'){
            $field['size'] = '';
        }

        if( isset($field['max']) && $field['max'] != '' ){
            $add_html .= ' maxlength="'.$field['max'].'" ';
        }

        if( isset($field['minlength']) && $field['minlength'] != '' ){
            $add_html .= ' minlength="'.$field['minlength'].'" ';
            if( $field['type'] == 'phone' || $field['type'] == 'tel' ){
                $add_html .= ' data-validation-minlength-message="'.$field['invalid'].'" ';
            }
        }


        if (isset($field['size']) and $field['size'] > 0) {


            if (!in_array($field['type'], array('textarea', 'select', 'data', 'time')))
                $add_html .= ' size="' . $field['size'] . '"';


            $class .= " auto_width";
        }



        if (!is_admin() or ! isset($_GET) or ! isset($_GET['page']) or $_GET['page'] == 'ARForms_entries') {


            $action = isset($_REQUEST['arfaction']) ? 'arfaction' : 'action';


            $action = $armainhelper->get_param($action);





            if (isset($field['required']) and $field['required']) {



                if ($field['type'] == 'file' and $action == 'edit') {
                    
                } else {
                    if ($field['type'] == 'select' || $field['type'] == ARF_AUTOCOMPLETE_SLUG) {
                        $class .= "select_controll_" . $field['id'] . " arf_required arf_select_controll";
                    } elseif ($field['type'] == 'time') {
                        $class .= "time_controll_" . $field['id'] . " arf_required";
                    } else {
                        $class .= " arf_required";
                    }
                }
            }

            if( $field['type'] == 'phone' && isset($field['phonetype']) && $field['phonetype'] == 1 ){
                $class .= " arf_phone_utils ";
            }

            if (isset($field['clear_on_focus']) and $field['clear_on_focus'] and ! empty($field['default_value'])) {


                $val = esc_attr($field['default_value']);

                $add_html .= ' onfocus="arfcleardedaultvalueonfocus(' . "'" . $val . "'" . ',this,' . "'" . $field['default_blank'] . "'" . ')" onblur="arfreplacededaultvalueonfocus(' . "'" . $val . "'" . ',this,' . "'" . $field['default_blank'] . "'" . ')" placeholder="' . $val . '"';


                if ($field['value'] == $field['default_value'])
                    $class .= ' arfdefault';
            }
        }





        if (isset($field['input_class']) and ! empty($field['input_class']))
            $class .= ' ' . $field['input_class'];





        $class = apply_filters('arfaddfieldclasses', $class, $field);


        if (!empty($class))
            $add_html .= ' class="' . $class . '"';





        if (isset($field['shortcodes']) and ! empty($field['shortcodes'])) {


            foreach ($field['shortcodes'] as $k => $v) {


                $add_html .= ' ' . $k . '="' . $v . '"';


                unset($k);


                unset($v);
            }
        }





        if ($echo)
            echo $add_html;





        return $add_html;
    }

    function ajax_time_options() {


        global $style_settings, $MdlDb, $wpdb, $armainhelper, $arfrecordmeta;


        extract($_POST);



        $time_key = str_replace('field_', '', $time_field);


        $date_key = str_replace('field_', '', $date_field);


        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($date)))
            $date = $armainhelper->convert_date($date, $style_settings->date_format, 'Y-m-d');


        $date_entries = $arfrecordmeta->getEntryIds("fi.field_key='$date_key' and entry_value='$date'");





        $opts = array('' => '');


        $time = strtotime($start);


        $end = strtotime($end);


        $step = explode(':', $step);


        $step = (isset($step[1])) ? ($step[0] * 3600 + $step[1] * 60) : ($step[0] * 60);


        $format = ($clock) ? 'H:i' : 'h:i A';





        while ($time <= $end) {


            $opts[date($format, $time)] = date($format, $time);


            $time += $step;
        }





        if ($date_entries and ! empty($date_entries)) {


            $used_times = $wpdb->get_col($wpdb->prepare("SELECT entry_value FROM $MdlDb->entry_metas it LEFT JOIN $MdlDb->fields fi ON (it.field_id = fi.id) WHERE fi.field_key= %s and it.entry_id in (" . implode(',', $date_entries) . ")", $time_key));





            if ($used_times and ! empty($used_times)) {


                $number_allowed = apply_filters('arfallowedtimecount', 1, $time_key, $date_key);


                $count = array();


                foreach ($used_times as $used) {


                    if (!isset($opts[$used]))
                        continue;





                    if (!isset($count[$used]))
                        $count[$used] = 0;


                    $count[$used] ++;





                    if ((int) $count[$used] >= $number_allowed)
                        unset($opts[$used]);
                }


                unset($count);
            }
        }





        echo json_encode($opts);


        die();
    }

    function destroy() {


        global $arffield;


        $field_id = $arffield->destroy($_POST['field_id']);


        die();
    }

    function edit_option() {


        global $arffield, $MdlDb;


        $ids = explode('-', $_POST['element_id']);


        $id = str_replace('field_', '', $ids[0]);


        if (strpos($_POST['element_id'], 'key_')) {


            $id = str_replace('key_', '', $id);


            $new_value = $_POST['update_value'];
        } else {


            $new_label = $_POST['update_value'];
        }


        $field = $arffield->getOne($id);


        $options = maybe_unserialize($field->options);


        $this_opt = (array) $options[$ids[1]];





        $label = isset($this_opt['label']) ? $this_opt['label'] : reset($this_opt);


        if (isset($this_opt['value']))
            $value = $this_opt['value'];





        if (!isset($new_label))
            $new_label = $label;





        if (isset($new_value) or isset($value))
            $update_value = isset($new_value) ? $new_value : $value;





        if (isset($update_value) and $update_value != $new_label)
            $options[$ids[1]] = array('value' => $update_value, 'label' => $new_label);
        else
            $options[$ids[1]] = $_POST['update_value'];


        global $wpdb;
        $wpdb->update($MdlDb->fields, array('options' => maybe_serialize($options)), array('id' => $id), array('%s'), array('%d'));
        echo stripslashes($_POST['update_value']);


        die();
    }

    function delete_option() {


        global $arffield, $MdlDb;


        $field = $arffield->getOne($_POST['field_id']);


        $options = maybe_unserialize($field->options);


        unset($options[$_POST['opt_key']]);


        global $wpdb;
        $wpdb->update($MdlDb->fields, array('options' => maybe_serialize($options)), array('id' => $_POST['field_id']), array('%s'), array('%d'));
        die();
    }

    function update_order() {


        if (isset($_POST) and isset($_POST['arfmainfieldid'])) {


            global $arffield, $wpdb, $MdlDb;

            
        }


        die();
    }

    function arf_prevalidateform_outside() {

        $form_id = $_POST['form_id'];

        $arf_errors = array();

        $arf_form_data = array();

        $values = $_POST;

        $arf_form_data = apply_filters('arf_populate_field_from_outside', $arf_form_data, $form_id, $values);

        $arf_errors = apply_filters('arf_validate_form_outside_errors', $arf_errors, $form_id, $values, $arf_form_data);

        if (isset($arf_errors['arf_form_data'])) {
            $arf_form_data = array_merge($arf_form_data, $arf_errors['arf_form_data']);
        }

        unset($arf_errors['arf_form_data']);

        if (count($arf_form_data) > 0) {
            echo '^arf_populate=';
            foreach ($arf_form_data as $field_id => $field_value) {
                echo $field_id . '^|^' . $field_value . '~|~';
            }
            echo '^arf_populate=';
        }

        if (count($arf_errors) > 0) {
            foreach ($arf_errors as $field_id => $error) {
                echo $field_id . '^|^' . $error . '~|~';
            }
        } else {
            echo 0;
        }

        die();
    }

    function arf_resetformoutside() {
        global $arfform, $arfieldhelper;

        $form_id = $_POST['form_id'];

        $arf_form_data = array();

        $form = $arfform->getOne((int) $form_id);

        $fields = $arfieldhelper->get_form_fields_tmp(false, $form->id, false, 0);

        $values = $arrecordhelper->setup_new_vars($fields, $form);

        $arf_form_data = apply_filters('arf_populate_field_after_from_submit', $arf_form_data, $form_id, $values, $form);

        if (count($arf_form_data) > 0) {
            $arferr = array();
            foreach ($arf_form_data as $field_id => $field_value) {
                $arferr[$fieldid] = $fieldvalue;
            }
            $return["conf_method"] = "validationerror";
            $return["message"] = $arferr;
            $return = apply_filters( 'arf_reset_built_in_captcha', $return, $_POST );
            echo json_encode($return);
            exit;
        }

        die();
    }

    function arf_add_new_preset() {

        if( false == current_user_can( 'arfviewforms' ) || false == current_user_can( 'arfeditforms' ) || false == current_user_can( 'arfchangesettings' ) ){
            echo 'error~|~'.esc_html__("Sorry, you don't have permission to perform this action.","ARForms");
            die;
        }

        $fn = (isset($_SERVER['HTTP_X_FILENAME']) ? $_SERVER['HTTP_X_FILENAME'] : false);
        $wp_upload_dir = wp_upload_dir();
        $upload_main_url = $wp_upload_dir['basedir'] . '/arforms/import_preset_value/';

        if( !isset($_FILES['preset_file']) ){
            echo 'error~|~'.esc_html__('Please select a CSV file','ARForms');
            die;
        }

        $checkext = explode(".", $_FILES['preset_file']['name']);
        $ext = $checkext[count($checkext) - 1];
        $ext = strtolower($ext);

        $prevent_ext = array('php','php3','php4','php5','pl','py','jsp','asp','exe','cgi');

        if ( in_array($ext, $prevent_ext) || $ext != 'csv' ) {
            echo 'error~|~'.esc_html__('Please select a CSV file','ARForms');
            die;
        }

        if ($fn) {

            file_put_contents(
                    $upload_main_url . $fn, file_get_contents($_FILES['preset_file']['tmp_name'])
            );
            echo $fn;
            $csvData = file_get_contents($upload_main_url . $fn);
            $lines = explode(PHP_EOL, $csvData);
            $csv_array = array();
            foreach ($lines as $line) {
                $csv_array[] = str_getcsv($line);
            }            
                        
            $preset_data_array = array();
            $i = 0;         
            $preset_data_array['seperate_value'] = 0;
            foreach ($csv_array as $data) {
                if ($data[0] != "") {
                    $preset_data_array[$i]['label'] = $data[0];                    
                    if (isset($data[1]) && $data[1] != "" && $data[0] != "") {      
                        $preset_data_array['seperate_value'] = 1;                  
                        $preset_data_array[$i]['key'] = $data[1];
                    } 
                    $i++;
                }
            }
        } else {
            echo 'error~|~'.esc_html__('Please select a CSV file','ARForms');
        }

        die();
    }

    function arf_save_new_preset() {
        $fn = isset($_POST['file_name']) ? $_POST['file_name'] : "";
        $arf_preset_future_use = isset($_POST['arf_preset_future_use']) ? $_POST['arf_preset_future_use'] : "";
        $arf_preset_title = isset($_POST['arf_preset_title']) ? $_POST['arf_preset_title'] : "";
        $wp_upload_dir = wp_upload_dir();
        $upload_main_url = $wp_upload_dir['basedir'] . '/arforms/import_preset_value/';
        if ($fn != "") {

            $file = $upload_main_url . $fn;
            $csv_data = array();
            ini_set('auto_detect_line_endings', true);

            $fh = fopen($file, 'r');
            $i = 0;

            while (($line = fgetcsv($fh, 1000, "\t")) !== false) {
                $csv_data[] = $line;
                $i++;
            }




            $preset_data_array = array();
            $preset_data_array['title'] = $arf_preset_title;
            $i = 0;
            $data_value = "";
            foreach ($csv_data as $data) {
                if ($data[0] != "") {
                    $preset_data_array['data'][$i]['label'] = $data[0];
                    $data[0] = str_replace('"', "'", $data[0]);



                    if (isset($data[1]) && $data[1] != "") {
                        $data_value .='"' . htmlspecialchars($data[0], ENT_QUOTES, 'UTF-8') . '|' . htmlspecialchars(str_replace('"', "'", $data[1]), ENT_QUOTES, 'UTF-8') . '",';
                        $preset_data_array['data'][$i]['value'] = htmlspecialchars(str_replace('"', "'", $data[1]), ENT_QUOTES, 'UTF-8');
                    } else {
                        $data_value .='"' . htmlspecialchars($data[0], ENT_QUOTES, 'UTF-8') . '",';
                        $preset_data_array['data'][$i]['value'] = htmlspecialchars(str_replace('"', "'", $data[0]), ENT_QUOTES, 'UTF-8');
                    }
                    $i++;
                }
            }
            if ($arf_preset_future_use == 1 && isset($preset_data_array['data'])) {
                $arf_preset_values = maybe_unserialize(get_option('arf_preset_values'));
                $arf_preset_values[] = $preset_data_array;
                $arf_preset_values = maybe_serialize($arf_preset_values);
                update_option('arf_preset_values', $arf_preset_values);

                $data_value = substr($data_value, 0, -1);
                echo '<li class="arf_selectbox_option" data-label="' . htmlspecialchars(str_replace('"', "'", $arf_preset_title), ENT_QUOTES, 'UTF-8') . '" data-value=\'[' . $data_value . ']\'>' . htmlspecialchars(str_replace('"', "'", $arf_preset_title), ENT_QUOTES, 'UTF-8') . '</li>';
            } else if ($data_value != "") {
                $data_value = substr($data_value, 0, -1);
                echo '<li class="arf_selectbox_option" data-label="Custom" data-value=\'[' . $data_value . ']\'>' . addslashes(esc_html__('Custom', 'ARForms')) . '</li>';
            }
        } else {
            echo 'error';
        }

        die();
    }

    function arf_save_new_preset_field_function() {
        $fn = isset($_POST['file_name']) ? $_POST['file_name'] : "";
        $arf_preset_future_use = isset($_POST['arf_save_preset_for_future']) ? $_POST['arf_save_preset_for_future'] : "";
        $arf_preset_title = isset($_POST['arf_preset_title']) ? $_POST['arf_preset_title'] : "";
        $wp_upload_dir = wp_upload_dir();
        $upload_main_url = $wp_upload_dir['basedir'] . '/arforms/import_preset_value/';

        if ($fn != "") {
            $file = $upload_main_url . $fn;

            $csv_data = array();
            ini_set('auto_detect_line_endings', true);

            $fh = fopen($file, 'r');
            $i = 0;

            $csv_length = 0;
            while (($line = fgetcsv($fh, 1000, "\t")) !== FALSE) {
                $csv_data[] = $line;
                $i++;
            }

            $preset_data_array = array();
            $preset_data_array['title'] = $arf_preset_title;
            $data_value = "";
            if (is_array($csv_data) && count($csv_data) > 0 && $csv_data[0][0] != "") {
                $k = 0;
                foreach ($csv_data as $data) {
                    if ($data[0] != "") {
                        $preset_data_array['data'][$k]['label'] = $data[0];
                        $data[0] = str_replace('"', "'", $data[0]);

                        if (isset($data[1]) && $data[1] != "") {
                            $data_value .= '"' . htmlspecialchars($data[0], ENT_QUOTES, 'UTF-8') . '|' . htmlspecialchars(str_replace('"', "'", $data[1]), ENT_QUOTES, 'UTF-8') . '",';
                            $preset_data_array['data'][$k]['value'] = htmlspecialchars(str_replace('"', "'", $data[1]), ENT_QUOTES, 'UTF-8');
                        } else {
                            $data_value .='"' . htmlspecialchars($data[0], ENT_QUOTES, 'UTF-8') . '",';
                            $preset_data_array['data'][$k]['value'] = htmlspecialchars(str_replace('"', "'", $data[0]), ENT_QUOTES, 'UTF-8');
                        }
                        $k++;
                    }
                }
                if ($arf_preset_future_use == true && isset($preset_data_array['data'])) {
                    $arf_preset_values = (get_option('arf_preset_values')!='') ? maybe_unserialize(get_option('arf_preset_values')): '';
                    
                    if( !is_array($arf_preset_values) || $arf_preset_values == '' ){
                        $arf_preset_values = array();
                    }
                    array_push($arf_preset_values,$preset_data_array);
                    $arf_preset_values = isset($arf_preset_values) ? maybe_serialize($arf_preset_values) : '';
                    update_option('arf_preset_values', $arf_preset_values);

                    $data_value = substr($data_value, 0, -1);
                    echo '<li class="arf_selectbox_option" data-label="' . htmlspecialchars(str_replace('"', "'", $arf_preset_title), ENT_QUOTES, 'UTF-8') . '" data-value=\'[' . $data_value . ']\'>' . htmlspecialchars(str_replace('"', "'", $arf_preset_title), ENT_QUOTES, 'UTF-8') . '</li>';
                } else {
                    $data_value = substr($data_value, 0, -1);
                    echo '<li class="arf_selectbox_option" data-label="Custom" data-value=\'[' . $data_value . ']\'>' . addslashes(esc_html__('Custom', 'ARForms')) . '</li>';
                }
            } else {
                echo "error";
            }
        } else {
            echo "error";
        }
        die();
    }

    function arf_save_field_option() {
        global $wpdb, $MdlDb;

        $field_id = (isset($_POST['field_id']) && !empty($_POST['field_id'])) ? $_POST['field_id'] : '';
        $ref_field_id = $field_id;
        $separate_value = (isset($_POST['separate_value']) && !empty($_POST['separate_value'])) ? $_POST['separate_value'] : '';

        $arf_selected_val = (isset($_POST['arf_selected_val'])) ? $_POST['arf_selected_val'] : '';
        $default_value = (isset($_POST['arf_op_item_meta'][$field_id]) && !empty($_POST['arf_op_item_meta'][$field_id])) ? $_POST['arf_op_item_meta'][$field_id] : '';

        $arf_op_label = $arf_op_value = $options = $as_arf_selected_val = array();
        $default_val = '';
        if ($arf_selected_val != '') {
            $as_arf_selected_val = explode(',', $arf_selected_val);
        }
        if (!empty($field_id)) {
            $arf_op_label = (isset($_POST['arf_op_label']) && !empty($_POST['arf_op_label'])) ? $_POST['arf_op_label'] : array();
            $arf_op_value = (isset($_POST['arf_op_value']) && !empty($_POST['arf_op_value'])) ? $_POST['arf_op_value'] : array();
            $arf_image_op_image = (isset($_POST['arf_image_op_image']) && !empty($_POST['arf_image_op_image'])) ? $_POST['arf_image_op_image'] : array();

            if (isset($separate_value) && $separate_value == 'true') {
                foreach ($arf_op_label[$field_id] as $k => $label) {
                    $options[] = array(
                        'value' => $arf_op_value[$field_id][$k],
                        'label' => $label,
                        'label_image' => $arf_image_op_image[$field_id][$k]
                    );
                }
            } else {
                $options = $arf_op_label[$field_id];
            }

            if (is_array($default_value)) {
                if (count($as_arf_selected_val) == count($default_value)) {
                    $as_default_value = array();
                    foreach ($as_arf_selected_val as $selected_key => $selected_val) {
                        $as_default_value[$selected_val] = $default_value[$selected_key];
                    }

                    $default_val = maybe_serialize($as_default_value);
                } else {
                    $default_val = maybe_serialize($default_value);
                }
            } else {
                $default_val = $default_value;
            }

            $update = $wpdb->update($MdlDb->fields, array('options' => maybe_serialize($options), 'default_value' => $default_val), array('ref_field_id' => $ref_field_id), array('%s'), array('%d'));
        }
        die();
    }

    function arf_field_op_lord_html() {
        global $wpdb, $arffield, $arfajaxurl;
        $field_id = (isset($_POST['field_id']) && !empty($_POST['field_id'])) ? $_POST['field_id'] : '';

        if (!empty($field_id)) {

            $field_obj = $arffield->getOne($field_id);
            $field = json_decode(json_encode($field_obj), true);
            $field['value'] = $field['default_value'];
            require(VIEWS_PATH . '/edit_option_popup.php');
        }
        die();
    }

    function arf_upload_radio_label_img() {
        $file = $_POST['image'];
        ?>
        <img src="<?php echo esc_attr($file); ?>" style="float: left; margin: 0 10px 0 0; height: 20px; width: 20px;" />
        <?php
        die();
    }

    function arfspecialchars($obj) {
        global $arformcontroller;
        $newArray = $return = array();
        if (is_object($obj)) {
            $newArray = $arformcontroller->arfObjtoArray($obj);
        } else {
            $newArray = $obj;
        }
        if (is_array($newArray)) {
            foreach ($newArray as $key => $value) {
                if (is_array($value)) {
                    $return[$key] = array_map(array($this, __FUNCTION__), $value);
                } else if (is_object($value)) {
                    $value = $arformcontroller->arfObjtoArray($value);
                    $return[$key] = array_map(array($this, __FUNCTION__), $value);
                } else {
                    $value = str_replace("'", "&#8217", $value);
                    $return[$key] = $value;
                }
            }
        } else {
            $return = str_replace("'", "&#8217", $newArray);
        }
        return $return;
    }
    
    function arf_change_imagecontrol_field_data_outside($field_json_data){
        if( $field_json_data['field_data']['imagecontrol']['image_url'] == '' ){
            $field_json_data['field_data']['imagecontrol']['image_url'] = ARFIMAGESURL."/no-image.png";
        }
        return $field_json_data;
    }

    function arf_get_field_multicolumn_icon($column,$arf_editor_index_row_val = '{arf_editor_index_row}'){
        if( $column == "" || $column < 1 || $column > 6 ){
            return '';
        }
        $data_value = $function_id = $checked = $svg_icon = "";
        switch($column){
            case 1:
                $function_id = "single_column";
                $data_value = "arf_1";
                $checked = "checked='checked'";
                $svg_icon  = "<svg id='multicolumn_one' height='24' width='18'>".ARF_CUSTOM_COL1_ICON."</svg>";
                break;
            case 2:
                $function_id = "two_column";
                $data_value = "arf_2";
                $checked = "";
                $svg_icon  = "<svg id='multicolumn_two' height='24' width='27'>" . ARF_CUSTOM_COL2_ICON . "</svg>";
                break;
            case 3:
                $function_id = "three_column";
                $data_value = "arf_3";
                $checked = "";
                $svg_icon  = "<svg id='multicolumn_three' height='24' width='35'>" . ARF_CUSTOM_COL3_ICON . "</svg>";
                break;
            case 4:
                $function_id = "four_column";
                $data_value = "arf_4";
                $checked = "";
                $svg_icon  = "<svg id='multicolumn_four' height='24' width='35'>" . ARF_CUSTOM_COL4_ICON . "</svg>";
                break;
            case 5:
                $function_id = "five_column";
                $data_value = "arf_5";
                $checked = "";
                $svg_icon  = "<svg id='multicolumn_five' height='24' width='45'>" . ARF_CUSTOM_COL5_ICON . "</svg>";
                break;
            case 6:
                $function_id = "six_column";
                $data_value = "arf_6";
                $checked = "";
                $svg_icon  = "<svg id='multicolumn_six' height='24' width='50'>" . ARF_CUSTOM_COL6_ICON . "</svg>";
                break;
        }
        $return_func  = "<div class='arf_multicolumn_opt' id='{$function_id}' data-value='{$data_value}'>";
        $return_func .= "<input type='radio' class='rdostandard multicolfield' name='classes' onclick='makeNewSortable({$column},this);' data-id='{$arf_editor_index_row_val}' id='classes_{$arf_editor_index_row_val}_{$column}' {$checked} value='{$data_value}' style='display:none;' />";
        $return_func .= "<label for='classes_{$arf_editor_index_row_val}_{$column}'>";
        $return_func .= "<span class='lblsubtitle_span_column'></span>";
        $return_func .= $svg_icon;
        $return_func .= "</label>";
        $return_func .= "</div>";
        return $return_func;
    }

    function arf_get_multicolumn_expand_icon(){
        $icon = '<div class="arf_multi_column_expand_icon"><svg width="11px" height="20px"><g>' . ARF_FIELD_MULTICOLUMN_EXPAND_ICON . '</g></svg></div>';
        return $icon;
    }

    function arf_get_field_control_icons($type = '',$field_required_cls = '',$field_id = '{arf_field_id}',$field_required = 0,$field_type = '{arf_field_type}',$form_id = '{arf_form_id}'){
        if( $type == "" ){
            return '';
        }
        $svg_icon = "";
        switch($type){
            case 'require':
                $svg_icon = "<div class='arf_field_option_icon'><a title='".addslashes(esc_html__('Required','ARForms'))."' data-title='".addslashes(esc_html__('Required','ARForms'))."' class='arf_field_option_input arf_field_icon_tooltip {$field_required_cls}' id='isrequired_{$field_id}' href='javascript:void(0)' onclick='javascript:arfmakerequiredfieldfunction({$field_id},{$field_required},2)'><svg id='required' height='20' width='21'><g>".ARF_CUSTOM_REQUIRED_ICON."</g></svg></a></div>";
                break;
            case 'options':
                $svg_icon = "<div  class='arf_field_option_icon arf_field_settings_icon'><a title='".addslashes(esc_html__('Field Settings','ARForms'))."' data-title='".addslashes(esc_html__('Field Settings','ARForms'))."' class='arf_field_option_input arf_field_icon_tooltip' href='javascript:void(0)' onClick=\"javascript:arfshowfieldoptions({$field_id},'{$field_type}');\"><svg id='fieldoption' height='20' width='20'><g>".ARF_CUSTOM_FIELDOPTION_ICON."</g></svg></a></div>";
                break;
            case 'delete':
                if( $field_type == 'imagecontrol' ){
                    $svg_icon = "<div class='arf_field_option_icon arf_field_action_iconbox'><a title='".addslashes(esc_html__('Delete Field','ARForms'))."' data-title='".addslashes(esc_html__('Delete Field','ARForms'))."' class='arf_field_option_input arf_field_icon_tooltip' data-toggle='arfmodal' href='#delete_field_message_{$field_id}' id='arf_field_delete_{$field_id}' onClick=\"arfchangedeletemodalwidth('arfimagecontrol', {$field_id});\"><svg id='delete' height='19' width='19'><g>".ARF_CUSTOM_DELETE_ICON."</g></svg></a></div>";
                } else {
                    $svg_icon = "<div class='arf_field_option_icon arf_field_action_iconbox'><a title='".addslashes(esc_html__('Delete Field','ARForms'))."' data-title='".addslashes(esc_html__('Delete Field','ARForms'))."' class='arf_field_option_input arf_field_icon_tooltip' data-toggle='arfmodal' href='#delete_field_message_{$field_id}' id='arf_field_delete_{$field_id}' onClick=\"arfchangedeletemodalwidth('arfdeletemodabox', {$field_id});\"><svg id='delete' height='19' width='19'><g>".ARF_CUSTOM_DELETE_ICON."</g></svg></a></div>";
                }
                break;
            case 'duplicate':
                $svg_icon = "<div class='arf_field_option_icon'><a title='".addslashes(esc_html__('Duplicate Field','ARForms'))."' data-title='".addslashes(esc_html__('Duplicate Field','ARForms'))."' class='arf_field_option_input arf_field_icon_tooltip' href='javascript:void(0)' onclick=\"javascript:arfduplicatefield({$form_id},'{$field_type}',{$field_id},{$field_id});\"><svg id='duplicate' height='19' width='19'><g>".ARF_CUSTOM_DUPLICATE_ITEM."</g></svg></a></div>";
                break;
            case 'move':
                $svg_icon = "<div class='arf_field_option_icon'><a title='".addslashes(esc_html__('Move','ARForms'))."' data-title='".addslashes(esc_html__('Move','ARForms'))."' class='arf_field_option_input arf_field_icon_tooltip'><svg id='moveing' height='20' width='21'><g>".ARF_CUSTOM_MOVING_ICON."</g></svg></a></div>";
                break;
            case 'edit_options':
                $svg_icon = "<div class='arf_field_option_icon'><a title='".addslashes(esc_html__('Manage Options','ARForms'))."' data-title='".addslashes(esc_html__('Manage Options','ARForms'))."' class='arf_field_option_input arf_field_icon_tooltip arf_edit_value_option_button' data-field-id='{$field_id}' id='arf_edit_value_option_button'><svg id='edit_opt_icon' height='20' width='21'><g>".ARF_FIELD_EDIT_OPTION_ICON."</g></svg></a></div>";
                break;
            case 'running_total_icon':
                $svg_icon = "<div class='arf_field_option_icon arf_html_running_total_icon'><a title='".addslashes(esc_html__('Running Total (Math Logic) is Enabled','ARForms'))."' data-title='".addslashes(esc_html__('Running Total (Math Logic) is Enabled','ARForms'))."' class='arf_field_option_input arf_field_icon_tooltip'><svg id='running_total_icon' height='20' width='21'><g>".ARF_FIELD_HTML_RUNNING_TOTAL_ICON."</g></svg></a></div>";
            default:
                $svg_icon = apply_filters("arf_field_option_icon_render_outside",$svg_icon);
                break;
        }
        return $svg_icon;
    }
}
?>