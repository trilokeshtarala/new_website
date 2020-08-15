<?php

class arrecordhelper {

    function __construct() {

        add_filter('arfemailvalue', array($this, 'email_value'), 10, 3);
    }

    function email_value($value, $meta, $entry) {
        global $arffield, $db_record, $arfieldhelper;
        if ($entry->id != $meta->entry_id)
            $entry = $db_record->getOne($meta->entry_id);
        $field = $arffield->getOne($meta->field_id);
        if (!$field)
            return $value;
        $field->field_options = maybe_unserialize($field->field_options);
        switch ($field->type) {
            case 'file':
                $value = $arfieldhelper->get_file_name($value);
                break;
            case 'date':
                $value = $arfieldhelper->get_date_entry($value,$field->form_id,$field->field_options['show_time_calendar'],$field->field_options['clock'],$field->field_options['locale']);
        }
        if (is_array($value)) {
            $new_value = '';
            foreach ($value as $val) {
                if (is_array($val))
                    $new_value .= implode(', ', $val) . "\n";
            }
            if ($new_value != '')
                $value = rtrim($new_value, ',');
        }
        return $value;
    }

    function enqueue_scripts($params) {

        do_action('arfenqueueformscripts', $params);
    }

    function allow_delete($entry) {


        global $user_ID;


        $allowed = false;


        if (current_user_can('arfdeleteentries'))
            $allowed = true;


        if ($user_ID and ! $allowed) {


            if (is_numeric($entry)) {


                global $MdlDb;


                $allowed = $MdlDb->get_var($MdlDb->entries, array('id' => $entry, 'user_id' => $user_ID));
            } else {


                $allowed = ($entry->user_id == $user_ID);
            }
        }



        return apply_filters('arfallowdelete', $allowed, $entry);
    }

    function setup_new_vars($fields, $form = '', $reset = false) {

        global $arfform, $arfsettings, $arfsidebar_width, $arfieldhelper, $armainhelper, $arformhelper;

        $values = array();

        foreach (array('name' => '', 'description' => '', 'entry_key' => '') as $var => $default)
            $values[$var] = $armainhelper->get_post_param($var, $default);


        $values['fields'] = array();

        if ($fields) {

            foreach ($fields as $field) {
                
                $field_options = $field->field_options;
                
                $default = isset($field->field_options['default_value']) ? $field->field_options['default_value'] : '';

                if ($reset)
                    $new_value = $default;
                else
                    $new_value = ($_POST and isset($_POST['item_meta'][$field->id]) and $_POST['item_meta'][$field->id] != '') ? $_POST['item_meta'][$field->id] : $default;



                $is_default = ($new_value == $default) ? true : false;



                if (!is_array($new_value))
                    $new_value = apply_filters('arfgetdefaultvalue', $new_value, $field);



                $new_value = str_replace('"', '&quot;', $new_value);

                if ($is_default)
                    $field->default_value = $new_value;
                else
                    $field->default_value = apply_filters('arfgetdefaultvalue', $field->default_value, $field);



                $field_array = array(
                    'id' => $field->id,
                    'value' => $new_value,
                    'name' => $field->name,
                    'type' => apply_filters('arffieldtype', $field->type, $field, $new_value),
                    'options' => $field->options,
                    'required' => $field->required,
                    'field_key' => $field->field_key,
                    'form_id' => $field->form_id,
                    'option_order' => maybe_unserialize($field->option_order),
                );

                $opt_defaults = $arfieldhelper->get_default_field_options($field_array['type'], $field, true);

                $opt_defaults['required_indicator'] = '';



                foreach ($opt_defaults as $opt => $default_opt) {

                    if ($opt == "confirm_password_label") {
                        $field_array[$opt] = (isset($field->field_options[$opt])) ? $field->field_options[$opt] : $default_opt;
                    } else {
                        $field_array[$opt] = (isset($field->field_options[$opt]) && $field->field_options[$opt] != '') ? $field->field_options[$opt] : $default_opt;
                    }

                    unset($opt);

                    unset($default_opt);
                }



                unset($opt_defaults);



                if ($field_array['size'] == '')
                    $field_array['size'] = $arfsidebar_width;





                if ($field_array['custom_html'] == '')
                    $field_array['custom_html'] = $arfieldhelper->get_basic_default_html($field->type);



                $field_array = apply_filters('arfsetupnewfieldsvars', $field_array, $field);



                foreach ((array) $field->field_options as $k => $v) {

                    if (!isset($field_array[$k]))
                        $field_array[$k] = $v;

                    unset($k);

                    unset($v);
                }



                $values['fields'][] = $field_array;



                if (!$form or ! isset($form->id))
                    $form = $arfform->getOne($field->form_id);
            }



            $form->options = maybe_unserialize($form->options);

            if (is_array($form->options)) {

                foreach ($form->options as $opt => $value)
                    $values[$opt] = $armainhelper->get_post_param($opt, $value);
            }



            if (!isset($values['custom_style']))
                $values['custom_style'] = ($arfsettings->load_style != 'none');



            if (!isset($values['email_to']))
                $values['email_to'] = '';



            if (!isset($values['submit_value']))
                $values['submit_value'] = $arfsettings->submit_value;



            if (!isset($values['success_msg']))
                $values['success_msg'] = $arfsettings->success_msg;



            if (!isset($values['akismet']))
                $values['akismet'] = '';



            if (!isset($values['before_html']))
                $values['before_html'] = $arformhelper->get_default_html('before');



            if (!isset($values['after_html']))
                $values['after_html'] = $arformhelper->get_default_html('after');
        }

        

        return apply_filters('arfsetupnewentry', $values);
    }

    function encode_value($line, $from_encoding, $to_encoding) {

        $convmap = false;

        switch ($to_encoding) {
            case 'macintosh':
                $convmap = array(
                    256, 304, 0, 0xffff,
                    306, 337, 0, 0xffff,
                    340, 375, 0, 0xffff,
                    377, 401, 0, 0xffff,
                    403, 709, 0, 0xffff,
                    712, 727, 0, 0xffff,
                    734, 936, 0, 0xffff,
                    938, 959, 0, 0xffff,
                    961, 8210, 0, 0xffff,
                    8213, 8215, 0, 0xffff,
                    8219, 8219, 0, 0xffff,
                    8227, 8229, 0, 0xffff,
                    8231, 8239, 0, 0xffff,
                    8241, 8248, 0, 0xffff,
                    8251, 8259, 0, 0xffff,
                    8261, 8363, 0, 0xffff,
                    8365, 8481, 0, 0xffff,
                    8483, 8705, 0, 0xffff,
                    8707, 8709, 0, 0xffff,
                    8711, 8718, 0, 0xffff,
                    8720, 8720, 0, 0xffff,
                    8722, 8729, 0, 0xffff,
                    8731, 8733, 0, 0xffff,
                    8735, 8746, 0, 0xffff,
                    8748, 8775, 0, 0xffff,
                    8777, 8799, 0, 0xffff,
                    8801, 8803, 0, 0xffff,
                    8806, 9673, 0, 0xffff,
                    9675, 63742, 0, 0xffff,
                    63744, 64256, 0, 0xffff,
                );
                break;
            case 'ISO-8859-1':
                $convmap = array(256, 10000, 0, 0xffff);
                break;
        }

        if (is_array($convmap))
            $line = mb_encode_numericentity($line, $convmap, $from_encoding);


        if ($to_encoding != $from_encoding)
            return iconv($from_encoding, $to_encoding . '//IGNORE', $line);
        else
            return $line;
    }

    function display_value($value, $field, $atts = array(),$form_css = array()) {

        global $wpdb, $arfieldhelper, $armainhelper, $MdlDb;
        
        $defaults = array(
            'type' => '', 'show_icon' => true, 'show_filename' => true,
            'truncate' => false, 'sep' => ', ', 'attachment_id' => 0, 'form_id' => $field->form_id,
            'field' => $field
        );


        $atts = wp_parse_args($atts, $defaults);


        $field->field_options = maybe_unserialize($field->field_options);

        if (!isset($field->field_options['post_field']))
            $field->field_options['post_field'] = '';


        if (!isset($field->field_options['custom_field']))
            $field->field_options['custom_field'] = '';


        if ($value == ''){
            $value = '-';
            return $value;
        }


        $value = maybe_unserialize($value);


        if (is_array($value))
            $value = stripslashes_deep($value);


        $value = apply_filters('arfdisplayvaluecustom', $value, $field, $atts);
        

        $new_value = '';


        if (is_array($value)) {


            foreach ($value as $val) {


                if (is_array($val)) {


                    $new_value .= implode($atts['sep'], $val);


                    if ($atts['type'] != 'data')
                        $new_value .= "<br/>";
                }


                unset($val);
            }
        }
        
        if (!empty($new_value)){
            $value = $new_value;
        } else if (is_array($value)){
            $value = implode($atts['sep'], $value);
        }


        if ($atts['truncate'] and $atts['type'] != 'image' and $atts['type'] != 'select'){
            $value = $armainhelper->truncate($value, 50);
        }

        /*if ($atts['type'] == 'signature') {
            $image_info=pathinfo($value);
            $value = '<span class="arf_signature_inner_'.$image_info['filename'].'">'.$value.'</span>';
        }*/
        
        if ($atts['type'] == 'image') {
            $value = '<img src="' . $value . '" height="50px" alt="" />';
        } else if ($atts['type'] == 'file') {
            $old_value = explode('|', $value);
            $value = '';
            
            foreach ($old_value as $val) {
                if ($atts['show_icon']){
                    $value .= $arfieldhelper->get_file_icon($val);
                }

                if ($atts['show_icon'] and $atts['show_filename']){
                    $value .= '<br/>';
                }

                if ($atts['show_filename'] && !$atts['show_icon']){
                    $value .= $arfieldhelper->get_file_name($val);
                }
            }
        }else if ($atts['type'] == 'date') {

            $value = $arfieldhelper->get_date_entry($value,$field->form_id,$field->field_options['show_time_calendar'],$field->field_options['clock'],$field->field_options['locale']);
        } else if ($atts['type'] == 'time') {
            $value = date_i18n(get_option('time_format'), strtotime($value));
        } else if ($atts['type'] == 'textarea') {
            $value = nl2br($value);
        } else if ($atts['type'] == 'like') {
            if ($value != '') {

                $class = ($value == '1') ? 'arf_like_btn' : 'arf_dislike_btn';
                $like_bgcolor = ($value == '1') ? (isset($form_css['arflikebtncolor']) ? $form_css['arflikebtncolor'] : '#4786FF') : (isset($form_css['arfdislikebtncolor']) ? $form_css['arfdislikebtncolor'] : '#EC3838');
                if ($value == '1')
                    $value = '<label style="margin-left:0;background:' . $like_bgcolor . ';" class="' . $class . ' active  field_in_list"><img src="' . ARFURL . '/images/like-icon.png" alt="' . addslashes(esc_html__('Like', 'ARForms')) . '" /></label>';
                else
                    $value = '<label style="margin-left:0;background:' . $like_bgcolor . ';" class="' . $class . ' active  field_in_list"><img src="' . ARFURL . '/images/dislike-icon.png" alt="' . addslashes(esc_html__('Dislike', 'ARForms')) . '" /></label>';
            }
        }
        
        if ($field->type == 'select' || $field->type == 'checkbox' || $field->type == 'radio' || $field->type == 'arf_autocomplete') {
            $field_opts = '';
            if( isset($GLOBALS['arf_form_entry']) && array_key_exists($field->id,$GLOBALS['arf_form_entry']) ){
                $field_opts = $GLOBALS['arf_form_entry'][$field->id];
            } else {
                //$field_opts = $wpdb->get_row($wpdb->prepare("SELECT entry_value FROM " . $MdlDb->entry_metas . " WHERE field_id='%d' AND entry_id='%d'", "-".$field->id, $atts['entry_id']));
                $field_opts = $wpdb->get_row($wpdb->prepare("SELECT entry_value FROM " . $MdlDb->entry_metas . " WHERE field_id='%d' AND entry_id='%d'", $field->id, $atts['entry_id']));

                if( !isset($GLOBALS['arf_form_entry'])){
                    $GLOBALS['arf_form_entry'] = array();
                }
                $GLOBALS['arf_form_entry'][$field->id] = $field_opts;
            }

            $field_opts = $wpdb->get_row($wpdb->prepare("SELECT entry_value FROM " . $MdlDb->entry_metas . " WHERE field_id='%d' AND entry_id='%d'", $field->id, $atts['entry_id']));

            if( !empty($field_opts) )
            {
                $field_opts = maybe_unserialize($field_opts->entry_value);
                
                
                if( $field->type == 'checkbox')
                {
                    if($field->field_options['separate_value']==1){
                        $temp_value = "";
                        if( $field_opts && count($field_opts) > 0 )
                        {
                            foreach ($field->field_options['options'] as $key => $val)
                            {
                                if(isset($val['value']) && is_array($field_opts)){
                                    

                                    if(in_array($val['value'], $field_opts)){
                                        $temp_value .= stripslashes($val['value'])." (".stripslashes($val['label'])."), ";
                                    }
                                    else if( $val['value'] == $field_opts )
                                    {
                                        $temp_value .= stripslashes($val['value'])." (".stripslashes($val['label'])."), ";
                                    }
                                }    
                            }
                        }    
                        
                        $temp_value = trim($temp_value);
                        $value      = rtrim($temp_value, ",");  
                    }
                    else{
                        $temp_value = '';
                        if( is_array($field_opts) && count($field_opts) > 0 )
                        {
                            foreach ($field_opts as $val){
                                $temp_value .= $val.", ";
                            }    
                        }
                        else
                        {
                            $temp_value .= $field_opts;                        
                        }

                        $temp_value = trim($temp_value);
                        $value      = rtrim($temp_value, ", ");
                    }
                                       
                } 
                else 
                {
                    if($field->field_options['separate_value']==1){
                        foreach ($field->field_options['options'] as $key => $val)
                        {
                            if( $val['value'] == $field_opts )
                            {
                                $value  = stripslashes($val['value'])." (".stripslashes($val['label']).")";
                            }
                        }
                    } else {
                        $value  = stripslashes($field_opts);
                    }
                    
                }
            }  

        }
        return apply_filters('arfdisplayvalue', $value, $field, $atts);
    }

    function display_value_with_edit($value, $field, $atts = array()) {      
        $is_remove_icon_with_edit = 0;
        if($field->type=='signature'){
            $is_remove_icon_with_edit = 1;
        }
        global $wpdb, $arfieldhelper, $armainhelper, $MdlDb;

        $form_css = $wpdb->get_row($wpdb->prepare('SELECT form_css FROM '.$MdlDb->forms.' WHERE id=%d',$field->form_id));
        $form_css = maybe_unserialize( $form_css->form_css );
        
        $defaults = array(
            'type' => '', 'show_icon' => true, 'show_filename' => true,
            'truncate' => false, 'sep' => ', ', 'attachment_id' => 0, 'form_id' => $field->form_id,
            'field' => $field
        );


        $atts = wp_parse_args($atts, $defaults);


        $field->field_options = maybe_unserialize($field->field_options);


        if (!isset($field->field_options['post_field']))
            $field->field_options['post_field'] = '';


        if (!isset($field->field_options['custom_field']))
            $field->field_options['custom_field'] = '';


        $value = maybe_unserialize($value);


        if (is_array($value))
            $value = stripslashes_deep($value);


        $value = apply_filters('arfdisplayvaluecustom', $value, $field, $atts);


        $new_value = '';


        if (is_array($value)) {


            foreach ($value as $val) {


                if (is_array($val)) {


                    $new_value .= implode($atts['sep'], $val);

                    if ($atts['type'] != 'data')
                        $new_value .= "<br/>";
                }


                unset($val);
            }
        }

        if ($field->type == 'email') {
            $temp_value = is_array($value) ? implode(',',$value) : $value;
            $value = '<span class="arf_editable_entry_icon_wrapper"><a data-type="text" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '"><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . '" class="arf_editable_values_container arf_edit_type_text" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . $temp_value . '</span>';
        } elseif ($field->type == 'textarea') {
            $temp_value = is_array($value) ? implode(',',$value) : $value;
            $value = '<span class="arf_editable_entry_icon_wrapper"><a data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '"><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . '" class="arf_editable_values_container arf_edit_type_text" data-type="textarea" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . $temp_value . '</span>';
        } else {
            if ($field->type == 'text' || $field->type == 'password' || $field->type == 'url' || $field->type == 'phone' || $field->type == 'number' || $field->type == 'time' || $field->type == 'hidden') {
                $temp_value = is_array($value) ? implode(',',$value) : $value;
                $value = '<span class="arf_editable_entry_icon_wrapper"><a data-type="text" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '"><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . '" class="arf_editable_values_container arf_edit_type_text" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . $temp_value . '</span>';
            }
        }





        if (!empty($new_value))
            $value = $new_value;


        else if (is_array($value))
            $value = implode($atts['sep'], $value);


        if ($atts['truncate'] and $atts['type'] != 'image' and $atts['type'] != 'select')
            $value = $armainhelper->truncate($value, 50);

        if ($atts['type'] == 'image') {
            $value = '<img src="' . $value . '" height="50px" alt="" />';
        } else if ($atts['type'] == 'file') {

            $old_value = explode('|', $value);

            $value = '';
            $url='';
            
            foreach ($old_value as $val) {
                $value .= '<div class="arf_file_inner_div arf_file_inner_container_'.$val.'"><span class="arf_deletable_entry_icon_wrapper arf_file_inner_'.$val.'"><a data-id="'.$field->id.'" data-entry-id="' . $atts['entry_id'] . '" data-file="'.$val.'" class="arf_file_remove" style="cursor:pointer"><svg height="28" width="28"><g><path fill="#FF0000" d="M18.435,4.857L18.413,19.87L3.398,19.88L3.394,4.857H1.489V2.929  h1.601h3.394V0.85h8.921v2.079h3.336h1.601l0,0v1.928H18.435z M15.231,4.857H6.597H5.425l0.012,13.018h10.945l0.005-13.018H15.231z   M11.4,6.845h2.029v9.065H11.4V6.845z M8.399,6.845h2.03v9.065h-2.03V6.845z"></path></g></svg></a></span>';
                if ($atts['show_icon'])
                    $value .= $arfieldhelper->get_file_icon($val);

                if ($atts['show_icon'] and $atts['show_filename'])
                    $value .= '<br/>';

                if ($atts['show_filename'] && !$atts['show_icon'])
                    $value .= $arfieldhelper->get_file_name($val);

                $url = wp_get_attachment_url($val);
                $value .= '</div>';
               
            }
            if($url){
                
                $value = '<span class="arf_not_editable_values_container arf_file_viewer arf_file_inner_'.$old_value[0].'" id="arf_file_inner_'.$old_value[0].'">'.$value.'</span>';
            }
        }
        else if ($atts['type'] == 'date') {
            $value = $arfieldhelper->get_date_entry($value,$field->form_id,$field->field_options['show_time_calendar'],$field->field_options['clock'],$field->field_options['locale']);
            $value = '<span class="arf_editable_entry_icon_wrapper"><a data-type="text" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '"><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . '" class="arf_editable_values_container arf_edit_type_text" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . $value . '</span>';
        } else if ($atts['type'] == 'textarea') {
            $value = nl2br($value);
        } else if ($atts['type'] == 'like') {
            if ($value != '') {
                $class = ($value == '1') ? 'arf_like_btn' : 'arf_dislike_btn';
                $like_bgcolor = ($value == '1') ?  $form_css['arflikebtncolor'] : $form_css['arfdislikebtncolor'];

                if ($value == '1')
                    $value = '<span class="arf_not_editable_values_container"><label style="margin-left:0;background:' . $like_bgcolor . ';" class="' . $class . ' active field_in_list"><img src="' . ARFURL . '/images/like-icon.png" /></label></span>';
                else
                    $value = '<span class="arf_not_editable_values_container"><label style="margin-left:0;background:' . $like_bgcolor . ';" class="' . $class . ' active field_in_list"><img src="' . ARFURL . '/images/dislike-icon.png" /></label></span>';
            }
        }
        if ($field->type == 'select' || $field->type == 'checkbox' || $field->type == 'radio' || $field->type == 'arf_autocomplete') {

            if( isset($GLOBALS['arf_form_entry']) && array_key_exists($field->id, $GLOBALS['arf_form_entry']) ){
                $field_opts = $GLOBALS['arf_form_entry'][$field->id];
            } else {
                //$field_opts = $wpdb->get_row($wpdb->prepare("SELECT entry_value FROM " . $MdlDb->entry_metas . " WHERE field_id='%d' AND entry_id='%d'", "-" . $field->id, $atts['entry_id']));
                $field_opts = $wpdb->get_row($wpdb->prepare("SELECT entry_value FROM " . $MdlDb->entry_metas . " WHERE field_id='%d' AND entry_id='%d'", $field->id, $atts['entry_id']));
                if( !isset($GLOBALS['arf_form_entry']) ){
                    $GLOBALS['arf_form_entry'] = array();
                }
                $GLOBALS['arf_form_entry'][$field->id] = $field_opts;
            }
            $field_opts = $wpdb->get_row($wpdb->prepare("SELECT entry_value FROM " . $MdlDb->entry_metas . " WHERE field_id='%d' AND entry_id='%d'", $field->id, $atts['entry_id']));
            if (!empty($field_opts)) {
                $field_opts = maybe_unserialize($field_opts->entry_value);
                if ($field->type == 'checkbox') {
                    if($field->field_options['separate_value']==1){
                        $temp_value = "";
                        if ($field_opts && count($field_opts) > 0) {
                            foreach ($field->field_options['options'] as $key => $val) {
                                if(isset($val['value']) && is_array($field_opts)){

                                    if(in_array($val['value'], $field_opts)){
                                        $temp_value .= stripslashes($val['label']) . " (" . stripslashes($val['value']) . "), ";    
                                    }
                                    else if( $val['value'] == $field_opts )
                                    {
                                        $temp_value .= stripslashes($val['value']) . " (" . stripslashes($val['label']) . "), ";
                                    }
                                }
                            }
                            $temp_value = trim($temp_value);
                            $value = rtrim($temp_value, ",");
                            $value = str_replace('^|^',',',$value);
                            $value = '<span class="arf_editable_entry_icon_wrapper"><a data-field-type="checkbox" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '" ><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . '" class="arf_editable_values_container arf_edit_type_select_option_' . $field->id . '" data-type="checklist" data-pk="1" data-separate-value="1" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . $value . '</span>';
                        }
                    }
                    else{
                        $temp_value = '';
                        if( is_array($field_opts) && count($field_opts) > 0 )
                        {
                            foreach ($field_opts as $val){
                                $temp_value .= $val.", ";
                            }    
                        }
                        else
                        {
                            $temp_value .= $field_opts;                        
                        }

                        $temp_value = trim($temp_value);
                        $value      = rtrim($temp_value, ", ");
                        $value = str_replace('^|^',',',$value);
                        $value = '<span class="arf_editable_entry_icon_wrapper"><a data-field-type="checkbox" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '" ><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . '" class="arf_editable_values_container arf_edit_type_select_option_' . $field->id . '" data-type="checklist" data-pk="1" data-separate-value="1" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . $value . '</span>';
                    }
                    
                } else {

                    if ($field->type == 'select' || $field->type == 'radio' || $field->type == 'arf_autocomplete' ) {

                        /*if ($field->field_options['separate_value']) {
                            $label_field_id = ( $field->id * 100 );
                            $get_field_label = $wpdb->get_row($wpdb->prepare("SELECT entry_value FROM " . $MdlDb->entry_metas . ' WHERE field_id = "-%d" and entry_id="%d"',$label_field_id,$atts['entry_id']));
                            $field_label = $get_field_label->entry_value;
                            if ($field_label != '') {
                                $value = stripslashes($get_field_label->entry_value) . " (" . stripslashes($field_opts['value']) . ")";
                            } else {
                                $value = stripslashes($field_opts['label']) . " (" . stripslashes($field_opts['value']) . ")";
                            }
                        }*/ 
                        if ($field->field_options['separate_value'] == 1) {
                            
                                foreach ($field->field_options['options'] as $key => $val)
                                {
                                    if( $val['value'] == $field_opts )
                                    {
                                        $value  = stripslashes($val['value'])." (".stripslashes($val['label']).")";
                                    }
                                }
                            
                            //$value = stripslashes($field_opts['label']) . " (" . stripslashes($field_opts['value']) . ")";
                        } else {
                            $value  = stripslashes($field_opts);
                        }

                            $value = '<span class="arf_editable_entry_icon_wrapper"><a data-field-type="'.$field->type.'" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '"><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . '" class="arf_editable_values_container arf_edit_type_select_option_' . $field->id . '" data-type="select" data-pk="1" data-separate-value="1" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . $value . '</span>';
                    } else {
                            $value = '<span class="arf_editable_entry_icon_wrapper"><a data-field-type="'.$field->type.'" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '"><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . '" class="arf_editable_values_container arf_edit_type_select_option_' . $field->id . '" data-type="select" data-pk="1" data-separate-value="1" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . stripslashes($field_opts['label']) . ' (' . stripslashes($field_opts['value']) . ')' . '</span>';
                    }
                }
            } else {

                if ($field->type == 'select') {
                    $value = '<span class="arf_editable_entry_icon_wrapper"><a data-field-type="select" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '"><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . '" class="arf_editable_values_container arf_edit_type_select_option_' . $field->id . '" data-type="select" data-pk="1" data-separate-value="0" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . $value . '</span>';
                }

                if ($field->type == 'radio') {
                    $value = '<span class="arf_editable_entry_icon_wrapper"><a data-field-type="radio" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '"><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . '" class="arf_editable_values_container arf_edit_type_select_option_' . $field->id . '" data-type="select" data-pk="1" data-separate-value="0" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . $value . '</span>';
                }

                if ($field->type == 'checkbox') {
                    $value = '<span class="arf_editable_entry_icon_wrapper"><a  data-field-type="checkbox" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '" ><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . '" class="arf_editable_values_container arf_edit_type_select_option_' . $field->id . '" data-type="checklist" data-pk="1" data-separate-value="0" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . $value . '</span>';
                }

                if ($field->type == 'arf_autocomplete') {
                    $value = '<span class="arf_editable_entry_icon_wrapper"><a  data-field-type="arf_autocomplete" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '" ><svg id="arf_edit_entry" height="28" width="28"><g>'.ARF_EDIT_ENTRY_ICON.'</g></svg></a></span><span id="arf_value_' . $atts['entry_id'] . '_' . $field->id . '" class="arf_editable_values_container arf_edit_type_select_option_' . $field->id . '" data-type="checklist" data-pk="1" data-separate-value="0" data-id="' . $field->id . '" data-entry-id="' . $atts['entry_id'] . '">' . $value . '</span>';
                }
            }
        }

        if(in_array($field->type, array('scale','arfslider','colorpicker','arf_smiley'))){
            $value = "<span class='arf_not_editable_values_container'>".$value."</span>";
        }
        if($is_remove_icon_with_edit==1){
            $atts['is_remove_icon_with_edit'] = 1;
        }
        return apply_filters('arfdisplayvalue', $value, $field, $atts);
    }

    function get_post_or_entry_value($entry, $field, $atts = array(), $is_for_mail = false) {

        global $arfrecordmeta;



        if (!is_object($entry)) {


            global $db_record;


            $entry = $db_record->getOne($entry);
        }



        $field->field_options = maybe_unserialize($field->field_options);



        if ($entry->attachment_id) {


            if (!isset($field->field_options['custom_field']))
                $field->field_options['custom_field'] = '';





            if (!isset($field->field_options['post_field']))
                $field->field_options['post_field'] = '';





            $links = true;


            if (isset($atts['links']))
                $links = $atts['links'];

            $value = $arfrecordmeta->get_entry_meta_by_field($entry->id, $field->id, true, $is_for_mail);
        }else {

            $value = $arfrecordmeta->get_entry_meta_by_field($entry->id, $field->id, true, $is_for_mail);
        }


        return $value;
    }

}
