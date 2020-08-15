<?php
class arformmodel {
    function __construct() {
        add_filter('arfformoptionsbeforeupdateform', array($this, 'update_options'), 10, 2);
        add_filter('arfupdatefieldtoptions', array($this, 'arfupdatefieldtoptions'), 10, 3);
        add_filter('arf_addon_getallforms', array($this, 'getAll_forms_addon'), 10, 5);
        //add_action('change_form', array($this, 'updateform'), 10, 2);
        add_filter('arfvalidationofcurrentform', array($this, 'validateform'), 10, 2);
       
    }
    
    function update_options($options, $values) {
        global $style_settings, $arformhelper;
        $defaults = $arformhelper->get_default_options();
        $defaults['inc_user_info'] = 0;
        foreach ($defaults as $opt => $default)
            $options[$opt] = (isset($values['options'][$opt])) ? $values['options'][$opt] : $default;
        unset($defaults);
        $options['single_entry'] = (isset($values['options']['single_entry'])) ? $values['options']['single_entry'] : 0;
        if ($options['single_entry'])
            $options['single_entry_type'] = (isset($values['options']['single_entry_type'])) ? $values['options']['single_entry_type'] : 'cookie';
        if (IS_WPMU)
            $options['copy'] = (isset($values['options']['copy'])) ? $values['options']['copy'] : 0;
        return $options;
    }

    function sitedesc() {
        return get_bloginfo('description');
    }

    function arfupdatefieldtoptions($field_options, $field, $values) {
        $post_fields = array(
            'post_category', 'post_content', 'post_excerpt', 'post_title',
            'post_name', 'post_date', 'post_status'
        );
        $field_options['post_field'] = $field_options['custom_field'] = '';
        $field_options['taxonomy'] = 'category';
        $field_options['exclude_cat'] = 0;
        if (!isset($values['options']['create_post']) or ! $values['options']['create_post'])
            return $field_options;
        foreach ($post_fields as $post_field) {
            if (isset($values['options'][$post_field]) and $values['options'][$post_field] == $field->id)
                $field_options['post_field'] = $post_field;
        }
        return $field_options;
    }

    function updateform($id, $values) {
        global $wpdb, $arfform, $MdlDb, $arffield;
        if (isset($values['field_options'])) {
            $all_fields = $arffield->getAll(array('fi.form_id' => $id));
            if ($all_fields) {
                foreach ($all_fields as $field) {
                    $option_array[$field->id] = maybe_unserialize($field->field_options);
                    $option_array[$field->id]['dependent_fields'] = false;
                    unset($field);
                }
                foreach ($option_array as $field_id => $field_options) {
                    $arffield->update($field_id, array('field_options' => $field_options));
                    unset($field_options);
                }
                unset($option_array);
            }
        }
    }
    function validateform($errors, $values) {
        global $arffield, $arfieldhelper;
        
        if (isset($values['options']['auto_responder']) && $values['options']['auto_responder'] == 1) {
            if (!isset($values['options']['ar_email_message']) or $values['options']['ar_email_message'] == '')
                $errors[] = addslashes(esc_html__("Please insert a message for your auto responder.", 'ARForms'));
            if (isset($values['options']['ar_reply_to']) and ! is_email(trim($values['options']['ar_reply_to'])))
                $errors[] = addslashes(esc_html__("That is not a valid reply-to email address for your auto responder.", 'ARForms'));
        }
        if (isset($values['options']['chk_admin_notification']) && $values['options']['auto_responder'] == 1) {
            if (!isset($values['options']['ar_admin_email_message']) or $values['options']['ar_admin_email_message'] == '')
                $errors[] = addslashes(esc_html__("Please insert a message for your auto responder.", 'ARForms'));
        }
        return $errors;
    }

    function create($values) {
        global $wpdb, $MdlDb, $arfsettings, $arformhelper, $armainhelper;
        $new_values = array();
        if($values['form_key'] =='')
        {
            $new_values['form_key'] = $armainhelper->get_unique_key($values['form_key'], $MdlDb->forms, 'form_key');
        }
        else
        {
            $new_values['form_key'] = $values['form_key'];
        }
        $new_values['name'] = arf_sanitize_value($values['name']);
        $new_values['description'] = arf_sanitize_value($values['description']);
        $new_values['status'] = isset($values['status']) ? arf_sanitize_value($values['status']) : arf_sanitize_value('draft');
        $new_values['is_template'] = isset($values['is_template']) ? (int) $values['is_template'] : 0;
        $options = array();
        $defaults = $arformhelper->get_default_opts();
        foreach ($defaults as $var => $default) {
            $options[$var] = isset($values['options'][$var]) ? $values['options'][$var] : $default;
            unset($var);
            unset($default);
        }
        $options['before_html'] = isset($values['options']['before_html']) ? $values['options']['before_html'] : $arformhelper->get_default_html('before');
        $options['after_html'] = isset($values['options']['after_html']) ? $values['options']['after_html'] : $arformhelper->get_default_html('after');
        $values['is_importform'] = isset($values['is_importform']) ? $values['is_importform'] : '';
        if ($values['is_importform'] != 'Yes') {
            $options = apply_filters('arfformoptionsbeforeupdateform', $options, $values);
            $new_values['options'] = maybe_serialize($options);
        } else {
            $new_values['options'] = $values['options'];
        }
        $new_values['form_css'] = isset($values['form_css']) ? $values['form_css'] : maybe_serialize(array());
        $new_values['created_date'] = current_time('mysql', 1);
        if( isset($values['id']) ){
            $new_values['id'] = $values['id'];
        }
        $query_results = $wpdb->insert($MdlDb->forms, $new_values);     
        return $wpdb->insert_id;
    }
    function duplicate($id, $template = false, $copy_keys = false, $blog_id = false, $is_from_edit = false, $newformid = 0, $is_ref_form = 0) {
        global $wpdb, $MdlDb, $arfform, $arffield, $arformhelper, $armainhelper;
        $values = $arfform->getOne($id, $blog_id);
        $autoresponder_fname = $values->autoresponder_fname;
        $autoresponder_lname = $values->autoresponder_lname;
        $autoresponder_email = $values->autoresponder_email;
        if (!$values) {
            return false;
        }
        $new_values = array();
        $new_key = ($copy_keys) ? $values->form_key : '';
    	$new_values['form_key'] = $armainhelper->get_unique_key($new_key, $MdlDb->forms, 'form_key');
        $form_name = (isset($_REQUEST['form_name'])) ? $_REQUEST['form_name'] : '';
        $form_desc = (isset($_REQUEST['form_desc'])) ? $_REQUEST['form_desc'] : '';
        $new_values['name'] = trim($form_name);
        $new_values['description'] = trim($form_desc);
        $new_values['status'] = (!$template) ? 'draft' : '';
        if ($blog_id) {
            $new_values['status'] = 'published';
            $new_options = maybe_unserialize($values->options);
            $new_options['email_to'] = get_option('admin_email');
            $new_options['copy'] = false;
            $new_values['options'] = $new_options;
        } else
            $new_values['options'] = $values->options;
        $new_values['options']['notification'][0] = array('email_to' => get_option('admin_email'), 'reply_to' => get_option('admin_email'),
            'reply_to_name' => get_option('blogname'),'admin_cc_email'=>'','admin_bcc_email'=>'', 'cust_reply_to' => '', 'cust_reply_to_name' => '');
        if (is_array($new_values['options']))
            $new_values['options'] = maybe_serialize($new_values['options']);
        $new_values['created_date'] = current_time('mysql', 1);
        $new_values['is_template'] = ($template) ? arf_sanitize_value(1, 'integer') : arf_sanitize_value(0, 'integer');
        if ($newformid > 0)
            $query_results = $wpdb->update($MdlDb->forms, $new_values, array('id' => $newformid));
        else {
            $query_results = $wpdb->insert($MdlDb->forms, $new_values);            
        }
        if ($query_results) {
            if ($newformid > 0)
                $form_id = $newformid;
            else
                $form_id = $wpdb->insert_id;
            if ($is_from_edit) {
                $arffield->duplicate($id, $form_id, $copy_keys, $blog_id);
            } else {
                $arffield->duplicate($id, $form_id, $copy_keys, $blog_id, true);
                $form_options_sql = $wpdb->get_results($wpdb->prepare("SELECT options FROM `".$MdlDb->forms."` WHERE id = %d",$form_id));
                $form_options = maybe_unserialize($form_options_sql[0]->options);
            /* duplicate time conditional logic update field id 22sep2016 */
                $conditional_logic = isset($form_options['arf_conditional_logic_rules']) ? $form_options['arf_conditional_logic_rules'] : '';
                if(is_array($conditional_logic)){
                foreach($conditional_logic as $i=> $value_rules){
                        if(isset($value_rules['condition'])&&is_array($value_rules['condition'])){
                            foreach ($value_rules['condition'] as $j=>$condition_rules){
                                $conditional_logic[$i]['condition'][$j]['field_id'] = $_SESSION['arf_fields'][$condition_rules['field_id']];
                            }
                        }
                        if(isset($value_rules['result'])&&is_array($value_rules['result'])){                               
                            foreach ($value_rules['result'] as $k=>$result_rules){
                                $conditional_logic[$i]['result'][$k]['field_id'] = isset($_SESSION['arf_fields'][$result_rules['field_id']])?$_SESSION['arf_fields'][$result_rules['field_id']]:"";
                            }
                        }
                    }
                    $form_options['arf_conditional_logic_rules'] = $conditional_logic;
                }
                /*duplicate time conditional logic update field id 22sep2016 */
                $autoresponder_fname = (isset($autoresponder_fname) and isset($_SESSION['arf_fields'][$autoresponder_fname]) ) ? $_SESSION['arf_fields'][$autoresponder_fname] : '';
                $autoresponder_lname = (isset($autoresponder_lname) and isset($_SESSION['arf_fields'][$autoresponder_lname]) ) ? $_SESSION['arf_fields'][$autoresponder_lname] : '';
                $autoresponder_email = (isset($autoresponder_email) and isset($_SESSION['arf_fields'][$autoresponder_email]) ) ? $_SESSION['arf_fields'][$autoresponder_email] : '';
                if ($template < 100) {
                    global $arfsettings;
                    $form_options['success_msg'] = $arfsettings->success_msg;
                }
                $new_field_order = array();
                if (isset($_SESSION['arf_fields']) && count($_SESSION['arf_fields']) > 0 and is_array($_SESSION['arf_fields'])) {
                    $fields_array = $arffield->getAll(array('fi.form_id' => $form_id), 'id');
                    foreach ($_SESSION['arf_fields'] as $original_id => $field_new_id) {
                        if($original_id == $form_options['ar_email_to']){
                            $form_options['ar_email_to'] = $field_new_id;                         
                        }
                        $form_options['ar_email_subject'] = str_replace('[' . $original_id . ']', '[' . $field_new_id . ']', $form_options['ar_email_subject']);
                        $form_options['ar_email_message'] = str_replace('[' . $original_id . ']', '[' . $field_new_id . ']', $form_options['ar_email_message']);
                        $form_options['ar_user_from_email'] = str_replace('[' . $original_id . ']', '[' . $field_new_id . ']', $form_options['ar_user_from_email']);
                        $form_options['reply_to'] = str_replace('[' . $original_id . ']', '[' . $field_new_id . ']', $form_options['reply_to']);
                        $form_options['ar_admin_from_email'] = str_replace('[' . $original_id . ']', '[' . $field_new_id . ']', $form_options['ar_admin_from_email']);
                        $form_options['admin_email_subject'] = str_replace('[' . $original_id . ']', '[' . $field_new_id . ']', $form_options['admin_email_subject']);
                        $form_options['ar_admin_from_name'] = str_replace('[' . $original_id . ']', '[' . $field_new_id . ']', $form_options['ar_admin_from_name']);
                        $form_options['ar_admin_email_message'] = str_replace('[' . $original_id . ']', '[' . $field_new_id . ']', $form_options['ar_admin_email_message']);
                        $form_options['ar_email_subject'] = $arformhelper->replace_field_shortcode_import($form_options['ar_email_subject'], $original_id, $field_new_id);
                        $form_options['ar_email_message'] = $arformhelper->replace_field_shortcode_import($form_options['ar_email_message'], $original_id, $field_new_id);
                        $form_options['ar_user_from_email'] = $arformhelper->replace_field_shortcode_import($form_options['ar_user_from_email'], $original_id, $field_new_id);
                        $form_options['reply_to'] = $arformhelper->replace_field_shortcode_import($form_options['reply_to'], $original_id, $field_new_id);
                        $form_options['ar_admin_from_email'] = $arformhelper->replace_field_shortcode_import($form_options['ar_admin_from_email'], $original_id, $field_new_id);
                        $form_options['admin_email_subject'] = $arformhelper->replace_field_shortcode_import($form_options['admin_email_subject'], $original_id, $field_new_id);
                        $form_options['ar_admin_from_name'] = $arformhelper->replace_field_shortcode_import($form_options['ar_admin_from_name'], $original_id, $field_new_id);
                        $form_options['ar_admin_email_message'] = $arformhelper->replace_field_shortcode_import($form_options['ar_admin_email_message'], $original_id, $field_new_id);
                        $field_order = json_decode($form_options['arf_field_order']);
                        foreach ($field_order as $key => $value) {
                            $new_field_order[$field_new_id] = $original_id;
                        }                        
                        if (count($fields_array) > 0) {
                            foreach ($fields_array as $new_field) {
                                if (isset($new_field->conditional_login)) {
                                    $coditional_logic = maybe_unserialize($new_field->conditional_logic);
                                    if (count($coditional_logic['rules']) > 0) {
                                        $coditional_logic_rules = array();
                                        foreach ($coditional_logic['rules'] as $new_rule) {
                                            if ($new_rule['field_id'] == $original_id)
                                                $new_rule['field_id'] = $field_new_id;

                                            $coditional_logic_rules[$new_rule['id']] = array(
                                                'id' => $new_rule['id'],
                                                'field_id' => $new_rule['field_id'],
                                                'field_type' => $new_rule['field_type'],
                                                'operator' => $new_rule['operator'],
                                                'value' => $new_rule['value'],
                                            );
                                        }
                                        $coditional_logic['rules'] = $coditional_logic_rules;
                                        $coditional_logic_new = maybe_serialize($coditional_logic);
                                        $wpdb->update($MdlDb->fields, array('conditional_logic' => $coditional_logic_new), array('id' => $new_field->id));
                                    }
                                }
                                $arf_field_options = maybe_unserialize($new_field->field_options);
                                if (count($arf_field_options) > 0) {
                                    $new_field_options = array();
                                    foreach ($arf_field_options as $key_field_options => $value_field_options) {
                                        $new_field_options[$key_field_options] = str_replace('[ENTERKEY]', '<br/>', $value_field_options);
                                    }
                                    global $MdlDb, $wpdb;
                                    if ($new_field->type == 'html') {
                                        $newdescription = $arformhelper->replace_field_shortcode_import($new_field->description, $original_id, $field_new_id);
                                        $wpdb->update($MdlDb->fields, array('description' => $newdescription), array('id' => $new_field->id));
                                    }
                                    $new_field_options = maybe_serialize($new_field_options);
                                    $wpdb->update($MdlDb->fields, array('field_options' => $new_field_options), array('id' => $new_field->id));
                                }
                            }
                        }
                    }
                    $form_options['arf_field_order'] = json_encode($new_field_order);
                    /*
                     * Conditional Mail Fields Selected
                     */
                    foreach ($_SESSION['arf_fields'] as $original_id => $field_new_id) {
                      $form_options['arf_conditional_mail_rules'] = isset($form_options['arf_conditional_mail_rules'])?$form_options['arf_conditional_mail_rules']:array();  
                        if (count($form_options['arf_conditional_mail_rules']) > 0) {
                            $arf_conditional_mail_rules = array();
                            foreach ($form_options['arf_conditional_mail_rules'] as $new_rule) {
                                if ($new_rule['send_mail_field'] == $original_id) {
                                    $new_rule['send_mail_field'] = $field_new_id;
                                }
                                if ($new_rule['field_id_mail'] == $original_id) {
                                    $new_rule['field_id_mail'] = $field_new_id;
                                }
                                $arf_conditional_mail_rules[$new_rule['id_mail']] = array(
                                    'id_mail' => $new_rule['id_mail'],
                                    'field_id_mail' => $new_rule['field_id_mail'],
                                    'field_type_mail' => $new_rule['field_type_mail'],
                                    'operator_mail' => $new_rule['operator_mail'],
                                    'value_mail' => $new_rule['value_mail'],
                                    'send_mail_field' => $new_rule['send_mail_field'],
                                );
                            }
                            if (isset($arf_conditional_mail_rules) && !empty($arf_conditional_mail_rules)) {
                                $form_options['arf_conditional_mail_rules'] = $arf_conditional_mail_rules;
                            }
                            /*position moved to down at duplicate time this condtion not true while coping default template*/
                        }
                    }
                    $form_options_new = maybe_serialize($form_options);
                    $wpdb->update($MdlDb->forms, array('options' => $form_options_new), array('id' => $form_id));
                    do_action('arf_afterduplicate_update_fields', $form_options, $_SESSION['arf_fields'], $form_id);
                }
                $wpdb->update($MdlDb->forms, array('autoresponder_fname' => $autoresponder_fname, 'autoresponder_lname' => $autoresponder_lname, 'autoresponder_email' => $autoresponder_email), array('id' => $form_id));
                //duplicate autoresponder
                if (isset($id) and $id != '') {
                    $sel_rec = $wpdb->prepare("select * from " .$MdlDb->ar." where frm_id = %d", $id);
                    $res_rec = $wpdb->get_results($sel_rec, 'ARRAY_A');
                    if ($res_rec)
                       $res_rec = $res_rec[0];
                    $aweber = isset($res_rec["aweber"]) ? $res_rec["aweber"] : '';
                    $mailchimp = isset($res_rec["mailchimp"]) ? $res_rec["mailchimp"] : '';
                    $madmimi = isset($res_rec["madmimi"]) ? $res_rec["madmimi"] : '';
                    $getresponse = isset($res_rec["getresponse"]) ? $res_rec["getresponse"] : '';
                    $gvo = isset($res_rec["gvo"]) ? $res_rec["gvo"] : '';
                    $ebizac = isset($res_rec["ebizac"]) ? $res_rec["ebizac"] : '';
                    $madmimi = isset($res_rec["madmimi"]) ? $res_rec["madmimi"] : '';
                    $icontact = isset($res_rec["icontact"]) ? $res_rec["icontact"] : '';
                    $constant_contact = isset($res_rec["constant_contact"]) ? $res_rec["constant_contact"] : '';
                    $enable_ar = isset($res_rec["enable_ar"]) ? $res_rec["enable_ar"] : '';
                    $wpdb->insert(
                            $MdlDb->ar, array(
                        'aweber' => $aweber,
                        'mailchimp' => $mailchimp,
                        'getresponse' => $getresponse,
                        'gvo' => $gvo,
                        'ebizac' => $ebizac,
                        'madmimi' => $madmimi,
                        'icontact' => $icontact,
                        'constant_contact' => $constant_contact,
                        'enable_ar' => $enable_ar,
                        'frm_id' => $form_id
                            ), array(
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%d'
                            )
                    );
                }
            }
            return $form_id;
        } else
            return false;
    }
    function update($id, $values, $create_link = false, $is_ref_form = 0) {
        global $wpdb, $MdlDb, $arffield, $arfsettings, $arformhelper, $arfieldhelper, $armainhelper;
        $values = apply_filters('arfchangevaluesbeforeupdateform', $values);
        do_action('arfbeforeupdateform', $id, $values, $create_link);
        do_action('arfbeforeupdateform_' . $id, $id, $values, $create_link);
        if ($create_link or isset($values['options']) or isset($values['item_meta']) or isset($values['field_options']))
            $values['status'] = 'published';
        $form_fields = array('form_key', 'name', 'description', 'status');
        $new_values = array();
        $double_optin =0;
        if (isset($values['options'])) {
            $options = array();
            $defaults = $arformhelper->get_default_opts();
            foreach ($defaults as $var => $default) {
                if ($var == 'notification')
                    $options[$var] = isset($values[$var]) ? $values[$var] : $default;
                else
                    $options[$var] = isset($values['options'][$var]) ? $values['options'][$var] : $default;
            }
            $options['arf_show_post_value'] = isset($values['options']['arf_show_post_value']) ? $values['options']['arf_show_post_value'] : 0;
            $options['arf_post_value_url'] = isset($values['options']['arf_post_value_url']) ? $values['options']['arf_post_value_url'] : '';
            $options['custom_style'] = isset($values['options']['custom_style']) ? $values['options']['custom_style'] : 0;

            $options['before_html'] = isset($values['options']['before_html']) ? $values['options']['before_html'] : $arformhelper->get_default_html('before');


            $options['after_html'] = isset($values['options']['after_html']) ? $values['options']['after_html'] : $arformhelper->get_default_html('after');


            $options = apply_filters('arfformoptionsbeforeupdateform', $options, $values);

            $options['display_title_form'] = isset($values['options']['display_title_form']) ? $values['options']['display_title_form'] : 0;
            $double_optin =$options['arf_enable_double_optin'] = isset($values['options']['arf_enable_double_optin']) ? $values['options']['arf_enable_double_optin'] : 0;

            $options['email_to'] = $options['reply_to'];

            $options['arf_restrict_form_entries'] = isset($values['options']['arf_restrict_form_entries']) ? $values['options']['arf_restrict_form_entries'] : 0;

            $options['restrict_action'] = isset($values['options']['restrict_action']) ? $values['options']['restrict_action'] : '';

            $options['arf_restrict_max_entries'] = isset($values['options']['arf_restrict_max_entries']) ? $values['options']['arf_restrict_max_entries'] : 50;

            $options['arf_restrict_entries_before_specific_date'] = isset($values['options']['arf_restrict_entries_before_specific_date']) ? $values['options']['arf_restrict_entries_before_specific_date'] : '';
            $options['arf_restrict_entries_after_specific_date'] = isset($values['options']['arf_restrict_entries_after_specific_date']) ? $values['options']['arf_restrict_entries_after_specific_date'] : '';
            $options['arf_restrict_entries_start_date'] = isset($values['options']['arf_restrict_entries_start_date']) ? $values['options']['arf_restrict_entries_start_date'] : '';
            $options['arf_restrict_entries_end_date'] = isset($values['options']['arf_restrict_entries_end_date']) ? $values['options']['arf_restrict_entries_end_date'] : '';

            $options['arf_res_msg'] = isset($values['options']['arf_res_msg']) ? $values['options']['arf_res_msg'] : '';





            //---------- for submit button conditional logic ----------//
            $submitbtnid = "arfsubmit";
            if (isset($_REQUEST['conditional_logic_' . $submitbtnid]) and stripslashes_deep($_REQUEST['conditional_logic_' . $submitbtnid]) == '1') {

                $conditional_logic_display = stripslashes_deep($_REQUEST['conditional_logic_display_' . $submitbtnid]);

                $conditional_logic_if_cond = stripslashes_deep($_REQUEST['conditional_logic_if_cond_' . $submitbtnid]);

                $conditional_logic_rules = array();

                $rule_array = $_REQUEST['rule_array_' . $submitbtnid] ? $_REQUEST['rule_array_' . $submitbtnid] : array();
                if (count($rule_array) > 0) {
                    $i = 1;
                    foreach ($rule_array as $v) {

                        $conditional_logic_field = stripslashes_deep($_REQUEST['arf_cl_field_' . $submitbtnid . '_' . $v]);
                        $conditional_logic_field_type = $arfieldhelper->get_field_type($conditional_logic_field);
                        $conditional_logic_op = stripslashes_deep($_REQUEST['arf_cl_op_' . $submitbtnid . '_' . $v]);
                        $conditional_logic_value = stripslashes_deep($_REQUEST['cl_rule_value_' . $submitbtnid . '_' . $v]);

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
                $new_values[$value_key] = $value;
        }

        $sel_fields = $wpdb->prepare("select id from " .$MdlDb->fields." where form_id = %d", $id);


                $res_fields_arr = $wpdb->get_results($sel_fields, 'ARRAY_A');
                
                $old_field_array = array();
                $changed_field_value = array();
                /* arf_dev_flag make above query proper and remove below loop o_0 */
                foreach ($res_fields_arr as $id_temp => $temp_value) {                   
                    $old_field_array[] = $temp_value['id'];
                }
                
                $scale_field_available = "";
                $selectbox_field_available = "";
                $radio_field_available = "";
                $checkbox_field_available = "";
                foreach($_REQUEST as $key=>$value){
                    if(preg_match('/(arf_field_data_)/',$key)){
                        
                        $name_array = explode('arf_field_data_',$key);
                        $field_id_new = $name_array[1];
                        $field_otions_new = array();
                        $field_otions_new =json_decode($value,true);
                        $options = '';
                        if(isset($_REQUEST['arf_field_options_'.$field_id_new])){
                            $options = $_REQUEST['arf_field_options_'.$field_id_new];
                        }
                        
                        if(in_array($field_id_new,$old_field_array)){
                    $update = $wpdb->query($wpdb->prepare("update " . $MdlDb->fields . " set name = '%s',description = '%s',type = '%s',default_value = '%s',options = '%s', required = '%s',field_options = '%s',form_id='%s',conditional_logic='%s',option_order='%s' where id = %d", arf_sanitize_value($field_otions_new["name"]), arf_sanitize_value($field_otions_new["description"]), arf_sanitize_value($field_otions_new["type"]), arf_sanitize_value($field_otions_new["default_value"]), $options, arf_sanitize_value($field_otions_new["required"], 'integer'), $value, arf_sanitize_value($_REQUEST['ref_formid'], 'integer'), arf_sanitize_value($field_otions_new["conditional_logic"], 'integer'), $field_otions_new["option_order"], $field_id_new));
                            $changed_field_value[] = $field_id_new;
                } else {

                    $field_key = $armainhelper->get_unique_key('', $MdlDb->fields, 'field_key');

                    $insfields = $wpdb->query($wpdb->prepare("insert into " . $MdlDb->fields . " (field_key,name,description,type,default_value,options,required,field_options,form_id,created_date,conditional_logic,option_order) VALUES ('%s','%s','%s','%s','%s','%s','%s','%s', '%s','%s','%s','%s') ", $field_key, arf_sanitize_value($field_otions_new["name"]), arf_sanitize_value($field_otions_new["description"]), arf_sanitize_value($field_otions_new["type"]), $field_otions_new["default_value"], $options, arf_sanitize_value($field_otions_new["required"], 'integer'), $value, $_REQUEST['ref_formid'], current_time('mysql'), arf_sanitize_value($field_otions_new["conditional_logic"], 'integer'), $field_otions_new["option_order"]));

                            $changed_field_value[] = $new_field_id = $wpdb->insert_id;
                        }
                    }
                }
                
                if (isset($changed_field_value) and !empty($changed_field_value)) {
                    $del_fields = $wpdb->query($wpdb->prepare("delete from " .$MdlDb->fields." where form_id = %d and id NOT IN (" . implode(',',$changed_field_value) . ")", $_REQUEST['ref_formid']));
                }

        $all_fields = $arffield->getAll(array('fi.form_id' => $id));
        
        

        if ($all_fields and ( isset($values['options']) or isset($values['item_meta']) or isset($values['field_options']))) {

        

            if (!isset($values['item_meta']))
                $values['item_meta'] = array();


            $existing_keys = array_keys($values['item_meta']);
            $total_page_break = 0;
            $page_break = array();
            $is_font_awesome = 0;
            $is_tooltip = 0;
            $is_input_mask = 0;
            $normal_color_pikcker =0;
            $advance_color_pikcker =0;
            $animate_number = 0;
            $round_total_number=0;
            $arf_page_break_survey = 0;
            $arf_page_break_wizard = 0;
            $arf_page_break_possition_top = 0;
            $arf_page_break_possition_botttom = 0;
            $arf_hide_bar_belt = 0;
            $arf_autocomplete_loaded = 0;
            $html_running_total_field_array = array();
            $google_captcha_loaded = 0;
            $is_imagecontrol_field = 0;
          
            
            foreach ($all_fields as $fid) {
                
              
         
                if (!in_array($fid->id, $existing_keys))
                    $values['item_meta'][$fid->id] = '';
                
                 $loaded_field[] = $fid->type;
                 if($fid->type=='break'){
                     $total_page_break++;
                     $page_break[] = $fid->id;
                   
                    if( isset($values['field_options']['page_break_type_' . $fid->id]) && $values['field_options']['page_break_type_' . $fid->id] =='survey'){
                        $arf_page_break_survey = 1;
                    }

                    if(isset($values['field_options']['page_break_type_' . $fid->id]) && $values['field_options']['page_break_type_' . $fid->id]=='wizard'){
                        $arf_page_break_wizard = 1; 
                    
                    }
                    
                    if( isset($values['field_options']['page_break_type_possition_' . $fid->id]) && $values['field_options']['page_break_type_possition_' . $fid->id] =='top'){
                        $arf_page_break_possition_top = 1;
                    }

                    if(isset($values['field_options']['page_break_type_possition_' . $fid->id]) && $values['field_options']['page_break_type_possition_' . $fid->id]=='bottom'){
                        $arf_page_break_possition_bottom = 1; 
                    }

                    if (isset($values['pagebreaktabsbar']) && $values['pagebreaktabsbar'] == 1) {
                        $arf_hide_bar_belt = 1;
                    }
                 }
                  
                 if((isset($values['field_options']['enable_arf_prefix_' . $fid->id])  && $values['field_options']['enable_arf_prefix_' . $fid->id]==1 ) || ( isset($values['field_options']['enable_arf_suffix_' . $fid->id])  && $values['field_options']['enable_arf_suffix_' . $fid->id]==1 ) ||($values['arfcksn']=='custom') || ($fid->type=='arf_smiley')|| ($fid->type=='scale')) {
                     $is_font_awesome = 1;
                 }
                 
                 if($fid->type=='phone' && ( isset($values['field_options']['phone_validation_' . $fid->id]) && $values['field_options']['phone_validation_' . $fid->id] != 'international')){
                    $is_input_mask = 1;
                }
                if ($fid->type == 'colorpicker' && (isset($values['field_options']['colorpicker_type_' . $fid->id]) && $values['field_options']['colorpicker_type_' . $fid->id] == 'basic')) {
                    $normal_color_pikcker = 1;
                } else if ($fid->type == 'colorpicker' && (isset($values['field_options']['colorpicker_type_' . $fid->id]) && $values['field_options']['colorpicker_type_' . $fid->id] == 'advanced')) {
                     $advance_color_pikcker = 1;
                 }
                 
                 if($fid->type=='html' && (isset($values['field_options']['enable_total_' . $fid->id])  && $values['field_options']['enable_total_' . $fid->id] == 1)) {
                     $animate_number = 1;
                     $html_running_total_field_array[] = $fid->id;
                 }
                 if($fid->type=='html' && (isset($values['field_options']['round_total_' . $fid->id])  && $values['field_options']['round_total_' . $fid->id] == 1)) {
                     $round_total_number = 1;
                 }
                 if($fid->type=='captcha' && (isset($values['field_options']['is_recaptcha_' . $fid->id])  && $values['field_options']['is_recaptcha_' . $fid->id] == 'recaptcha')) {
                    $google_captcha_loaded = 1;
                 }
                 if($fid->type=='arf_autocomplete') {
                     $arf_autocomplete_loaded =1;                     
                 }
                 if($fid->type=='imagecontrol') {
                     $is_imagecontrol_field =1;                     
                 }
                 
                 
                 if(isset($values['field_options']['arf_tooltip_' . $fid->id])&& $values['field_options']['arf_tooltip_' . $fid->id]==1){
                     $is_tooltip = 1;
                 } 
            }   
            
            $options['arf_loaded_field'] = array_unique($loaded_field);
            $options['total_page_break'] = $total_page_break;
            $options['page_break_field'] = $page_break;
            $options['font_awesome_loaded'] = $is_font_awesome;
            $options['tooltip_loaded'] = $is_tooltip;
            $options['arf_input_mask'] = $is_input_mask;
            $options['arf_normal_colorpicker'] = $normal_color_pikcker;
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
            $new_values['options'] = maybe_serialize($options);
        }

       
        if (isset($_REQUEST['autoresponder']) and count($_REQUEST['autoresponder']) > 0) {

            foreach ($_REQUEST['autoresponder'] as $aresponder) {


                $_REQUEST['autoresponder_id'] .= $aresponder . "|";
            }
        } else {

	    $_REQUEST['autoresponder_id'] = "";
        }
       
        $type = maybe_unserialize(get_option('arf_ar_type'));
        $autoresponder_all_data_query = $wpdb->get_results("SELECT * FROM " .$MdlDb->autoresponder,'ARRAY_A');
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
            $aweber_arr['type_val'] =  isset($_REQUEST['web_form_aweber']) ? arf_sanitize_value(stripslashes_deep($_REQUEST['web_form_aweber'])) : '';
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
            $mailchimp_arr['type_val'] = (isset($_REQUEST['web_form_mailchimp'])) ? arf_sanitize_value(stripslashes_deep($_REQUEST['web_form_mailchimp'])) : '';
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
            $madmimi_arr['type_val'] = (isset($_REQUEST['web_form_madmimi'])) ? arf_sanitize_value(stripslashes_deep($_REQUEST['web_form_madmimi'])) : '';
        }

        $res = $autoresponder_all_data_query[3];

        if (isset($_REQUEST['autoresponders']) && in_array('4', $_REQUEST['autoresponders'])) {
            $getresponse_arr['enable'] = arf_sanitize_value(1, 'integer');
        } else {
            $getresponse_arr['enable'] = arf_sanitize_value(0, 'integer');
        }

        if ($type['getresponse_type'] == 1) {
            $getresponse_arr['type'] = arf_sanitize_value(1, 'integer');
            $getresponse_arr['type_val'] = isset($_REQUEST['i_campain_name']) ? arf_sanitize_value($_REQUEST['i_campain_name']) : '';
        } else if ($type['getresponse_type'] == 0) {
            $getresponse_arr['type'] = arf_sanitize_value(0, 'integer');
            $getresponse_arr['type_val'] = (isset($_REQUEST['web_form_getresponse'])) ? arf_sanitize_value(stripslashes_deep($_REQUEST['web_form_getresponse'])) : '';
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
            $icontact_arr['type_val'] = (isset($_REQUEST['web_form_icontact'])) ?  arf_sanitize_value(stripslashes_deep($_REQUEST['web_form_icontact'])) : '';
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
            $constant_contact_arr['type_val'] = (isset($_REQUEST['web_form_constant_contact'])) ?  arf_sanitize_value(stripslashes_deep($_REQUEST['web_form_constant_contact'])) : '';
        }
        $res = $autoresponder_all_data_query[4];

        if (isset($_REQUEST['autoresponders']) && in_array('5', $_REQUEST['autoresponders'])) {
            $gvo_arr['enable'] = arf_sanitize_value(1, 'integer');
        } else {
            $gvo_arr['enable'] = arf_sanitize_value(0, 'integer');
        }

        if ($type['gvo_type'] == 0) {
            $gvo_arr['type'] = arf_sanitize_value(0, 'integer');
            $gvo_arr['type_val'] = (isset($_REQUEST['web_form_gvo'])) ? arf_sanitize_value(stripslashes_deep($_REQUEST['web_form_gvo'])) : '';
        }
        
        $res = $autoresponder_all_data_query[5];

        if (isset($_REQUEST['autoresponders']) && in_array('6', $_REQUEST['autoresponders'])) {

            $ebizac_arr['enable'] = arf_sanitize_value(1, 'integer');
        } else {

            $ebizac_arr['enable'] = arf_sanitize_value(0, 'integer');
        }

        if ($type['ebizac_type'] == 0) {
            $ebizac_arr['type'] = 0;
            $ebizac_arr['type_val'] = (isset($_REQUEST['web_form_ebizac'])) ? arf_sanitize_value(stripslashes_deep($_REQUEST['web_form_ebizac'])) : '';
        }

        $ar_global_autoresponder = array(
            'aweber' => $aweber_arr['enable'],
            'mailchimp' => $mailchimp_arr['enable'],
            'madmimi' => $madmimi_arr['enable'],
            'getresponse' => $getresponse_arr['enable'],
            'gvo' => $gvo_arr['enable'],
            'ebizac' => $ebizac_arr['enable'],
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


        $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " .$MdlDb->ar." WHERE frm_id = %d", $id), 'ARRAY_A');
        
        if ($wpdb->num_rows != 1) {
            $res = $wpdb->query($wpdb->prepare("INSERT INTO " .$MdlDb->ar." (frm_id, aweber, mailchimp, getresponse, gvo, ebizac,madmimi, icontact, constant_contact) VALUES (%d, %s, %s, %s, %s,  %s,%s, %s, %s)", $id, $ar_aweber, $ar_mailchimp, $ar_getresponse, $ar_gvo, $ar_ebizac,$ar_madmimi,$ar_icontact, $ar_constant_contact));
            do_action('arf_autoresponder_after_insert', $wpdb->insert_id,$_REQUEST);
        } else {
            $res = $wpdb->update($MdlDb->ar, array('aweber' => $ar_aweber, 'mailchimp' => $ar_mailchimp, 'getresponse' => $ar_getresponse, 'gvo' => $ar_gvo, 'ebizac' => $ar_ebizac,'madmimi' => $ar_madmimi, 'icontact' => $ar_icontact, 'constant_contact' => $ar_constant_contact), array('frm_id' => $id));
            do_action('arf_autoresponder_after_update', $id,$_REQUEST);
        }
        
        if ($id < 10000) {
            $enable_ar = maybe_serialize($ar_global_autoresponder);
            $res = $wpdb->update($MdlDb->ar, array('enable_ar' => $enable_ar), array('frm_id' => $id));
        }

        $new_values['autoresponder_fname'] = (isset($_REQUEST['autoresponder_fname'])) ? arf_sanitize_value($_REQUEST['autoresponder_fname']) : '';

        $new_values['autoresponder_lname'] = (isset($_REQUEST['autoresponder_lname'])) ? arf_sanitize_value($_REQUEST['autoresponder_lname']) : '';

        $new_values['autoresponder_email'] = (isset($_REQUEST['autoresponder_email'])) ? arf_sanitize_value($_REQUEST['autoresponder_email'], 'email') : '';


        if (!empty($new_values)) {
	    $query_results = $wpdb->update($MdlDb->forms, $new_values, array('id' => $id));

            if ($query_results)
                wp_cache_delete($id, 'arfform');
        }else {


            $query_results = true;
        }

        $new_values2 = array();

        $_REQUEST['arfmf'] = $id;

        $new_values2['arfmainformwidth'] = isset($_REQUEST['arffw'])? arf_senitize_value($_REQUEST['arffw']):'';

        $new_values2['form_width_unit'] = isset($_REQUEST['arffu'])? arf_senitize_value($_REQUEST['arffu']):'';

        $new_values2['text_direction'] = isset($_REQUEST['arftds'])? arf_sanitize_value($_REQUEST['arftds']):'';

        $new_values2['form_align'] = isset($_REQUEST['arffa'])? arf_sanitize_value($_REQUEST['arffa']):'';

        $new_values2['arfmainfieldsetpadding'] = isset($_REQUEST['arfmfsp'])? arf_sanitize_value($_REQUEST['arfmfsp']):'';

        $new_values2['form_border_shadow'] = isset($_REQUEST['arffbs'])? arf_sanitize_value($_REQUEST['arffbs']):'';

        $new_values2['fieldset'] = isset($_REQUEST['arfmfis'])? arf_sanitize_value($_REQUEST['arfmfis']):'';

        $new_values2['arfmainfieldsetradius'] = isset($_REQUEST['arfmfsr'])? arf_sanitize_value($_REQUEST['arfmfsr']):'';

        $new_values2['arfmainfieldsetcolor'] = isset($_REQUEST['arfmfsc'])? arf_sanitize_value($_REQUEST['arfmfsc']):'';

        $new_values2['arfmainformbordershadowcolorsetting'] = isset($_REQUEST['arffboss'])? arf_sanitize_value($_REQUEST['arffboss']):'';

        $new_values2['arfmainformtitlecolorsetting'] = isset($_REQUEST['arfftc'])? arf_sanitize_value($_REQUEST['arfftc']):'';

        $new_values2['check_weight_form_title'] = isset($_REQUEST['arfftws'])? arf_sanitize_value($_REQUEST['arfftws']):'';

        $new_values2['form_title_font_size'] = isset($_REQUEST['arfftfss'])? arf_sanitize_value($_REQUEST['arfftfss']):'';

        $new_values2['arfmainformtitlepaddingsetting'] = isset($_REQUEST['arfftps'])? arf_sanitize_value($_REQUEST['arfftps']):'';

        $new_values2['arfmainformbgcolorsetting'] = isset($_REQUEST['arffbcs'])? arf_sanitize_value($_REQUEST['arffbcs']):'';

        $new_values2['font'] = isset($_REQUEST['arfmfs'])? arf_sanitize_value($_REQUEST['arfmfs']):'';

        $new_values2['label_color'] = isset($_REQUEST['arflcs'])? arf_sanitize_value($_REQUEST['arflcs']):'';

        $new_values2['weight'] = isset($_REQUEST['arfmfws'])? arf_sanitize_value($_REQUEST['arfmfws']):'';

        $new_values2['font_size'] = isset($_REQUEST['arffss'])? arf_sanitize_value($_REQUEST['arffss']):'';

        $new_values2['align'] = isset($_REQUEST['arffrma'])? arf_sanitize_value($_REQUEST['arffrma']):'';

        $new_values2['position'] = isset($_REQUEST['arfmps'])? arf_sanitize_value($_REQUEST['arfmps']):'';

        $new_values2['width'] = isset($_REQUEST['arfmws'])? arf_sanitize_value($_REQUEST['arfmws']):'';

        $new_values2['width_unit'] = isset($_REQUEST['arfmwu'])? arf_sanitize_value($_REQUEST['arfmwu']):'';

        $new_values2['arfdescfontsizesetting'] = isset($_REQUEST['arfdfss'])? arf_sanitize_value($_REQUEST['arfdfss']):'';

        $new_values2['arfdescalighsetting'] = isset($_REQUEST['arfdas'])?arf_sanitize_value($_REQUEST['arfdas']):'';

        $new_values2['hide_labels'] = isset($_REQUEST['arfhl'])? arf_sanitize_value($_REQUEST['arfhl']):'';

        $new_values2['check_font'] = isset($_REQUEST['arfcbfs'])? arf_sanitize_value($_REQUEST['arfcbfs']):'';

        $new_values2['check_weight'] = isset($_REQUEST['arfcbws'])? arf_sanitize_value($_REQUEST['arfcbws']):"";

        $new_values2['field_font_size'] = isset($_REQUEST['arfffss'])? arf_sanitize_value($_REQUEST['arfffss']):"";

        $new_values2['text_color'] = isset($_REQUEST['arftcs'])? arf_sanitize_value($_REQUEST['arftcs']):"";

        $new_values2['border_radius'] = isset($_REQUEST['arfmbs'])? arf_sanitize_value($_REQUEST['arfmbs']):'';

        $new_values2['border_color'] = isset($_REQUEST['arffmboc'])? arf_sanitize_value($_REQUEST['arffmboc']):'';

        $new_values2['arffieldborderwidthsetting'] = isset($_REQUEST['arffbws'])? arf_sanitize_value($_REQUEST['arffbws']):'';

        $new_values2['arffieldborderstylesetting'] = isset($_REQUEST['arffbss'])? arf_sanitize_value($_REQUEST['arffbss']):'';

        if (isset($_REQUEST['arffiu']) and $_REQUEST['arffiu'] == '%' and isset($_REQUEST['arfmfiws']) and $_REQUEST['arfmfiws'] > '100') {
            $new_values2['field_width'] = arf_sanitize_value('100');
        } else {
            $new_values2['field_width'] = isset($_REQUEST['arfmfiws']) ? arf_sanitize_value($_REQUEST['arfmfiws']) : '';
        }

        $new_values2['field_width_unit'] = isset($_REQUEST['arffiu'])?arf_sanitize_value($_REQUEST['arffiu']):"";

        $new_values2['arffieldmarginssetting'] = isset($_REQUEST['arffms'])?arf_sanitize_value($_REQUEST['arffms']):'';

        $new_values2['arffieldinnermarginssetting'] = isset($_REQUEST['arffims'])?arf_sanitize_value($_REQUEST['arffims']):"";

        $new_values2['bg_color'] = isset($_REQUEST['arffmbc'])?arf_sanitize_value($_REQUEST['arffmbc']):'';

        $new_values2['arfbgactivecolorsetting'] = isset($_REQUEST['arfbcas'])?arf_sanitize_value($_REQUEST['arfbcas']):"";

        $new_values2['arfborderactivecolorsetting'] = isset($_REQUEST['arfbacs'])?arf_sanitize_value($_REQUEST['arfbacs']):"";

        $new_values2['arferrorbgcolorsetting'] = isset($_REQUEST['arfbecs'])?arf_sanitize_value($_REQUEST['arfbecs']):"";

        $new_values2['arferrorbordercolorsetting'] = isset($_REQUEST['arfboecs'])?arf_sanitize_value($_REQUEST['arfboecs']):'';

        $new_values2['arfradioalignsetting'] = isset($_REQUEST['arfras'])?arf_sanitize_value($_REQUEST['arfras']):"";

        $new_values2['arfcheckboxalignsetting'] = isset($_REQUEST['arfcbas'])?arf_sanitize_value($_REQUEST['arfcbas']):'';


        $new_values2['auto_width'] = isset($_REQUEST['arfautowidthsetting'])?arf_sanitize_value($_REQUEST['arfautowidthsetting']):'';

        $new_values2['arfcalthemecss'] = isset($_REQUEST['arffthc'])?arf_sanitize_value($_REQUEST['arffthc']):"";

        $new_values2['date_format'] = isset($_REQUEST['arffdaf'])? arf_sanitize_value($_REQUEST['arffdaf']):'';

        $new_values2['arfsubmitbuttontext'] = isset($_REQUEST['arfsubmitbuttontext'])? arf_sanitize_value($_REQUEST['arfsubmitbuttontext']):'';

        $new_values2['arfsubmitweightsetting'] = isset($_REQUEST['arfsbwes'])? arf_sanitize_value($_REQUEST['arfsbwes']):'';

        $new_values2['arfsubmitbuttonfontsizesetting'] = isset($_REQUEST['arfsbfss'])?arf_sanitize_value($_REQUEST['arfsbfss']):'';

        $new_values2['arfsubmitbuttonwidthsetting'] = isset($_REQUEST['arfsbws'])?arf_sanitize_value($_REQUEST['arfsbws']):'';

        $new_values2['arfsubmitbuttonheightsetting'] = isset($_REQUEST['arfsbhs'])? arf_sanitize_value($_REQUEST['arfsbhs']):'';


        $new_values2['submit_bg_color'] = isset($_REQUEST['arfsbbcs'])?arf_sanitize_value($_REQUEST['arfsbbcs']):"";

        $new_values2['arfsubmitbuttonbgcolorhoversetting'] = isset($_REQUEST['arfsbchs'])?arf_sanitize_value($_REQUEST['arfsbchs']):'';

        $new_values2['arfsubmitbgcolor2setting'] = isset($_REQUEST['arfsbcs'])?arf_sanitize_value($_REQUEST['arfsbcs']):'';

        $new_values2['arfsubmittextcolorsetting'] = isset($_REQUEST['arfsbtcs'])?arf_sanitize_value($_REQUEST['arfsbtcs']):'';

        $new_values2['arfsubmitbordercolorsetting'] = isset($_REQUEST['arfsbobcs'])?arf_sanitize_value($_REQUEST['arfsbobcs']):'';

        $new_values2['arfsubmitborderwidthsetting'] = isset($_REQUEST['arfsbbws'])?arf_sanitize_value($_REQUEST['arfsbbws']):'';

        $new_values2['arfsubmitborderradiussetting'] = isset($_REQUEST['arfsbbrs'])?arf_sanitize_value($_REQUEST['arfsbbrs']):'';

        $new_values2['arfsubmitshadowcolorsetting'] = isset($_REQUEST['arfsbscs'])?arf_sanitize_value($_REQUEST['arfsbscs']):'';

        $new_values2['arfsubmitbuttonmarginsetting'] = isset($_REQUEST['arfsbms'])?arf_sanitize_value($_REQUEST['arfsbms']):'';


        $new_values2['submit_bg_img'] = isset($_REQUEST['arfsbis'])?arf_sanitize_value($_REQUEST['arfsbis']):'';

        $new_values2['submit_hover_bg_img'] = isset($_REQUEST['arfsbhis'])?arf_sanitize_value($_REQUEST['arfsbhis']):'';

        $new_values2['error_font'] = isset($_REQUEST['arfmefs'])?arf_sanitize_value($_REQUEST['arfmefs']):'';

        $new_values2['error_font_other'] = isset($_REQUEST['arfmofs'])?arf_sanitize_value($_REQUEST['arfmofs']):'';

        $new_values2['arffontsizesetting'] = isset($_REQUEST['arfmefss'])?arf_sanitize_value($_REQUEST['arfmefss']):'';

        $new_values2['arferrorbgsetting'] = isset($_REQUEST['arfmebs'])?arf_sanitize_value($_REQUEST['arfmebs']):'';

        $new_values2['arferrortextsetting'] = isset($_REQUEST['arfmets'])?arf_sanitize_value($_REQUEST['arfmets']):'';

        $new_values2['arferrorbordersetting'] = isset($_REQUEST['arfmebos'])?arf_sanitize_value($_REQUEST['arfmebos']):'';

        $new_values2['arfsucessbgcolorsetting'] = isset($_REQUEST['arfmsbcs'])?arf_sanitize_value($_REQUEST['arfmsbcs']):'';

        $new_values2['arfsucessbordercolorsetting'] = isset($_REQUEST['arfmsbocs'])?arf_sanitize_value($_REQUEST['arfmsbocs']):"";

        $new_values2['arfsucesstextcolorsetting'] = isset($_REQUEST['arfmstcs'])?arf_sanitize_value($_REQUEST['arfmstcs']):'';

        $new_values2['arfsubmitalignsetting'] = isset($_REQUEST['arfmsas'])?arf_sanitize_value($_REQUEST['arfmsas']):'';

        $new_values2['checkbox_radio_style'] = isset($_REQUEST['arfcrs'])?arf_sanitize_value($_REQUEST['arfcrs']):'';

        $new_values2['bg_color_pg_break'] = isset($_REQUEST['arffbcpb'])?arf_sanitize_value($_REQUEST['arffbcpb']):'';

        $new_values2['bg_inavtive_color_pg_break'] = isset($_REQUEST['arfbicpb'])?arf_sanitize_value($_REQUEST['arfbicpb']):"";

        $new_values2['text_color_pg_break'] = isset($_REQUEST['arfftcpb'])?arf_sanitize_value($_REQUEST['arfftcpb']):"";

        $new_values2['arfmainform_bg_img'] = isset($_REQUEST['arfmfbi'])?arf_sanitize_value($_REQUEST['arfmfbi']):'';
        
        $new_values2['arfmainform_color_skin'] = isset($_REQUEST['arfmcs'])?arf_sanitize_value($_REQUEST['arfmcs']):'';

        $new_values2['arfsubmitfontfamily'] = isset($_REQUEST['arfsff'])?arf_sanitize_value($_REQUEST['arfsff']):'';

        $new_values2['arf_bg_position_x'] = isset($_REQUEST['arf_bg_position_x'])?arf_sanitize_value($_REQUEST['arf_bg_position_x']):arf_sanitize_value("left");
        $new_values2['arf_bg_position_y'] = isset($_REQUEST['arf_bg_position_y'])?arf_sanitize_value($_REQUEST['arf_bg_position_y']):arf_sanitize_value("top");

        $new_values2['arf_bg_position_input_x'] = isset($_REQUEST['arf_bg_position_input_x'])?arf_sanitize_value($_REQUEST['arf_bg_position_input_x']):"";
        $new_values2['arf_bg_position_input_y'] = isset($_REQUEST['arf_bg_position_input_y'])?arf_sanitize_value($_REQUEST['arf_bg_position_input_y']):"";


        $new_values2['arfmainfieldsetpadding_1'] = isset($_REQUEST['arfmainfieldsetpadding_1'])?arf_sanitize_value($_REQUEST['arfmainfieldsetpadding_1']):"";
        $new_values2['arfmainfieldsetpadding_2'] = isset($_REQUEST['arfmainfieldsetpadding_2'])?arf_sanitize_value($_REQUEST['arfmainfieldsetpadding_2']):'';
        $new_values2['arfmainfieldsetpadding_3'] = isset($_REQUEST['arfmainfieldsetpadding_3'])?arf_sanitize_value($_REQUEST['arfmainfieldsetpadding_3']):'';
        $new_values2['arfmainfieldsetpadding_4'] = isset($_REQUEST['arfmainfieldsetpadding_4'])?arf_sanitize_value($_REQUEST['arfmainfieldsetpadding_4']):'';
        $new_values2['arfmainformtitlepaddingsetting_1'] = isset($_REQUEST['arfformtitlepaddingsetting_1'])?arf_sanitize_value($_REQUEST['arfformtitlepaddingsetting_1']):'';
        $new_values2['arfmainformtitlepaddingsetting_2'] = isset($_REQUEST['arfformtitlepaddingsetting_2'])?arf_sanitize_value($_REQUEST['arfformtitlepaddingsetting_2']):"";
        $new_values2['arfmainformtitlepaddingsetting_3'] = isset($_REQUEST['arfformtitlepaddingsetting_3'])?arf_sanitize_value($_REQUEST['arfformtitlepaddingsetting_3']):'';
        $new_values2['arfmainformtitlepaddingsetting_4'] = isset($_REQUEST['arfformtitlepaddingsetting_4'])?arf_sanitize_value($_REQUEST['arfformtitlepaddingsetting_4']):"";
        $new_values2['arffieldinnermarginssetting_1'] = isset($_REQUEST['arffieldinnermarginsetting_1'])?arf_sanitize_value($_REQUEST['arffieldinnermarginsetting_1']):'';
        $new_values2['arffieldinnermarginssetting_2'] = isset($_REQUEST['arffieldinnermarginsetting_2'])?arf_sanitize_value($_REQUEST['arffieldinnermarginsetting_2']):'';
        $new_values2['arffieldinnermarginssetting_3'] = isset($_REQUEST['arffieldinnermarginsetting_3'])?arf_sanitize_value($_REQUEST['arffieldinnermarginsetting_3']):'';
        $new_values2['arffieldinnermarginssetting_4'] = isset($_REQUEST['arffieldinnermarginsetting_4'])?arf_sanitize_value($_REQUEST['arffieldinnermarginsetting_4']):"";
        $new_values2['arfsubmitbuttonmarginsetting_1'] = isset($_REQUEST['arfsubmitbuttonmarginsetting_1'])?arf_sanitize_value($_REQUEST['arfsubmitbuttonmarginsetting_1']):'';
        $new_values2['arfsubmitbuttonmarginsetting_2'] = isset($_REQUEST['arfsubmitbuttonmarginsetting_2'])?arf_sanitize_value($_REQUEST['arfsubmitbuttonmarginsetting_2']):'';
        $new_values2['arfsubmitbuttonmarginsetting_3'] = isset($_REQUEST['arfsubmitbuttonmarginsetting_3'])?arf_sanitize_value($_REQUEST['arfsubmitbuttonmarginsetting_3']):'';
        $new_values2['arfsubmitbuttonmarginsetting_4'] = isset($_REQUEST['arfsubmitbuttonmarginsetting_4'])?arf_sanitize_value($_REQUEST['arfsubmitbuttonmarginsetting_4']):'';
        $new_values2['arfsectionpaddingsetting_1'] = isset($_REQUEST['arfsectionpaddingsetting_1'])?arf_sanitize_value($_REQUEST['arfsectionpaddingsetting_1']):'';
        $new_values2['arfsectionpaddingsetting_2'] = isset($_REQUEST['arfsectionpaddingsetting_2'])?arf_sanitize_value($_REQUEST['arfsectionpaddingsetting_2']):'';
        $new_values2['arfsectionpaddingsetting_3'] = isset($_REQUEST['arfsectionpaddingsetting_3'])?arf_sanitize_value($_REQUEST['arfsectionpaddingsetting_3']):'';
        $new_values2['arfsectionpaddingsetting_4'] = isset($_REQUEST['arfsectionpaddingsetting_4'])?arf_sanitize_value($_REQUEST['arfsectionpaddingsetting_4']):"";
        $new_values2['arfcheckradiostyle'] = isset($_REQUEST['arfcksn'])?arf_sanitize_value($_REQUEST['arfcksn']):'';
        $new_values2['arfcheckradiocolor'] = isset($_REQUEST['arfcksc'])?arf_sanitize_value($_REQUEST['arfcksc']):'';
        $new_values2['arf_checked_checkbox_icon'] = isset($_REQUEST['arf_checkbox_icon'])?arf_sanitize_value($_REQUEST['arf_checkbox_icon']):'';        
        $new_values2['enable_arf_checkbox'] = isset($_REQUEST['enable_arf_checkbox'])?arf_sanitize_value($_REQUEST['enable_arf_checkbox']):"";
        $new_values2['arf_checked_radio_icon'] = isset($_REQUEST['arf_radio_icon'])?arf_sanitize_value($_REQUEST['arf_radio_icon']):'';
        $new_values2['enable_arf_radio'] = isset($_REQUEST['enable_arf_radio'])?arf_sanitize_value($_REQUEST['enable_arf_radio']):'';
        $new_values2['checked_checkbox_icon_color'] = isset($_REQUEST['cbscol'])?arf_sanitize_value($_REQUEST['cbscol']):"";        
        $new_values2['checked_radio_icon_color'] = isset($_REQUEST['rbscol'])?arf_sanitize_value($_REQUEST['rbscol']):'';

        $new_values2['arferrorstyle'] = isset($_REQUEST['arfest'])?arf_sanitize_value($_REQUEST['arfest']):'';
        $new_values2['arferrorstylecolor'] = isset($_REQUEST['arfestc'])?arf_sanitize_value($_REQUEST['arfestc']):'';
        $new_values2['arferrorstylecolor2'] = isset($_REQUEST['arfestc2'])?arf_sanitize_value($_REQUEST['arfestc2']):'';
        $new_values2['arferrorstyleposition'] = isset($_REQUEST['arfestbc'])?arf_sanitize_value($_REQUEST['arfestbc']):'';

        $new_values2['arfformtitlealign'] = isset($_REQUEST['arffta'])?arf_sanitize_value($_REQUEST['arffta']):'';
        $new_values2['arfsubmitautowidth'] = isset($_REQUEST['arfsbaw'])?arf_sanitize_value($_REQUEST['arfsbaw']):'';

        $new_values2['arftitlefontfamily'] = isset($_REQUEST['arftff'])?arf_sanitize_value($_REQUEST['arftff']):'';

        $new_values2['bar_color_survey'] = isset($_REQUEST['arfbcs'])?arf_sanitize_value($_REQUEST['arfbcs']):'';
        $new_values2['bg_color_survey'] = isset($_REQUEST['arfbgcs'])?arf_sanitize_value($_REQUEST['arfbgcs']):"";
        $new_values2['text_color_survey'] = isset($_REQUEST['arfftcs'])?arf_sanitize_value($_REQUEST['arfftcs']):'';

        $new_values2['arfsectionpaddingsetting'] = isset($_REQUEST['arfscps'])?arf_sanitize_value($_REQUEST['arfscps']):'';

        if (isset($_REQUEST['arfmainform_opacity']) and $_REQUEST['arfmainform_opacity'] > 1) {
            $new_values2['arfmainform_opacity'] = arf_sanitize_value('1');
        } else {
            $new_values2['arfmainform_opacity'] = isset($_REQUEST['arfmainform_opacity']) ? arf_sanitize_value($_REQUEST['arfmainform_opacity']) : '';
        }

        $new_values2['arfmainfield_opacity'] = isset($_REQUEST['arfmfo'])?arf_sanitize_value($_REQUEST['arfmfo']):"";

        $new_values2['prefix_suffix_bg_color'] = isset($_REQUEST['pfsfsbg'])?arf_sanitize_value($_REQUEST['pfsfsbg']):'';
        $new_values2['prefix_suffix_icon_color'] = isset($_REQUEST['pfsfscol'])?arf_sanitize_value($_REQUEST['pfsfscol']):"";
        
        $new_values2['arf_tooltip_bg_color'] = isset($_REQUEST['arf_tooltip_bg_color'])?arf_sanitize_value($_REQUEST['arf_tooltip_bg_color']):"";
        $new_values2['arf_tooltip_font_color'] = isset($_REQUEST['arf_tooltip_font_color'])?arf_sanitize_value($_REQUEST['arf_tooltip_font_color']):"";
        $new_values2['arf_tooltip_width'] = isset($_REQUEST['arf_tooltip_width'])?arf_sanitize_value($_REQUEST['arf_tooltip_width']):"";
        $new_values2['arf_tooltip_position'] = isset($_REQUEST['arf_tooltip_position'])?arf_sanitize_value($_REQUEST['arf_tooltip_position']):"";

        $new_values1 = maybe_serialize($new_values2);


        if (!empty($new_values2)) {

            $query_results = $wpdb->query("update " . $MdlDb->forms . " set form_css = '" . $new_values1 . "' where id = '" . $id . "'");

            if ($query_results > 0) {
                $saving = true;
		$use_saved = true;
                global $arsettingcontroller;

                $arfssl = (is_ssl()) ? 1 : 0;
                $new_values = array();
                $new_values = $new_values2;
                $filename = FORMPATH . '/core/css_create_main.php';

                $wp_upload_dir = wp_upload_dir();

                $target_path = $wp_upload_dir['basedir'] . '/arforms/maincss';

                $css = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";


                $css .= "\n";


                ob_start();


                include $filename;


                $css .= ob_get_contents();


                ob_end_clean();


                $css .= "\n " . $warn;

                $css_file = $target_path . '/maincss_' . $id . '.css';

                if (file_exists($css_file)) {
                    WP_Filesystem();
                    global $wp_filesystem;
                    $wp_filesystem->put_contents($css_file, $css, 0777);
                }
                wp_cache_delete($id, 'arfform');
            }
        } else {

            $query_results = true;
        }


        do_action('change_form', $id, $values);

        do_action('arfafterupdateform', $id, $values, $create_link);
        do_action('arfafterupdateform_' . $id, $id, $values, $create_link);

        do_action('arfupdateform_' . $id, $values);


        $query_results = apply_filters('arfchangevaluesafterupdateform', $query_results);


        return $query_results;
    }

    function destroy($id) {


        global $wpdb, $MdlDb, $db_record;

        $form = $this->getOne($id);

        if (!$form or $form->is_template)
            return false;

        do_action('arfbeforedestroyform', $id);

        do_action('arfbeforedestroyform_' . $id);

       $form_css_res = $wpdb->get_results($wpdb->prepare("SELECT id FROM " .$MdlDb->forms." WHERE id = %d", $id), ARRAY_A);
	
        if ($form_css_res) {
            foreach ($form_css_res as $refform) {
                $rformid = $refform['id'];
                if (isset($rformid) && $rformid > 0 && $rformid != "") {
                    $entries = $db_record->getAll(array('it.form_id' => $rformid));
                    foreach ($entries as $item)
                        $db_record->destroy($item->id);

                    $query_results_r1 = $wpdb->query($wpdb->prepare("DELETE FROM `$MdlDb->fields` WHERE `form_id` = %d", $rformid));
                    $query_results_r2 = $wpdb->query($wpdb->prepare("DELETE FROM `$MdlDb->views` WHERE `form_id` = %d", $rformid));
                    $query_results_r3 = $wpdb->query($wpdb->prepare("DELETE FROM `$MdlDb->ar` WHERE `frm_id` = %d", $rformid));

                    $uploads = wp_upload_dir();
                    $target_path = $uploads['basedir'];
                    $target_path .= "/arforms";
                    $css_path = $target_path . "/css/";
                    $maincss_path = $target_path . "/maincss/";
                    if (file_exists($css_path . 'form_' . $rformid . '.css')) {
                        unlink($css_path . 'form_' . $rformid . '.css');
                    }
                    if (file_exists($maincss_path . 'maincss_' . $rformid . '.css')) {
                        unlink($maincss_path . 'maincss_' . $rformid . '.css');
                    }
                    if (file_exists($maincss_path . 'maincss_materialize_' . $rformid . '.css')) {
                        unlink($maincss_path . 'maincss_materialize_' . $rformid . '.css');
                    }

		    $query_results = $wpdb->query($wpdb->prepare("DELETE FROM `$MdlDb->forms` WHERE `id` = %d", $rformid));
                }
            }
        }
        

        $entries = $db_record->getAll(array('it.form_id' => $id));


        foreach ($entries as $item)
            $db_record->destroy($item->id);



        $query_results = $wpdb->query($wpdb->prepare("DELETE FROM `$MdlDb->fields` WHERE `form_id` = %d", $id));

        $query_results = $wpdb->query($wpdb->prepare("DELETE FROM `$MdlDb->views` WHERE `form_id` = %d", $id));

        $query_results = $wpdb->query($wpdb->prepare("DELETE FROM `$MdlDb->ar` WHERE `frm_id` = %d", $id));


        $uploads = wp_upload_dir();

        $target_path = $uploads['basedir'];

        $target_path .= "/arforms";

        $css_path = $target_path . "/css/";

        $maincss_path = $target_path . "/maincss/";

        if (file_exists($css_path . 'form_' . $id . '.css')) {
            unlink($css_path . 'form_' . $id . '.css');
        }

        if (file_exists($maincss_path . 'maincss_' . $id . '.css')) {
            unlink($maincss_path . 'maincss_' . $id . '.css');
        }

        if (file_exists($maincss_path . 'maincss_materialize' . $id . '.css')) {
            unlink($maincss_path . 'maincss_materialize' . $id . '.css');
        }

        $query_results = $wpdb->query($wpdb->prepare("DELETE FROM `$MdlDb->forms` WHERE `id` = %d", $id));



        if ($query_results) {


            do_action('arfdestroyform', $id);


            do_action('arfdestroyform_' . $id);
        }


        return $query_results;
    }

    function getName($id) {


        global $wpdb, $MdlDb;


        $query = "SELECT name FROM $MdlDb->forms WHERE ";


        $query .= (is_numeric($id)) ? "id" : "form_key";


        $query .= $wpdb->prepare("=%s", $id);


        $r = $wpdb->get_var($query);


        return stripslashes($r);
    }

    function getOne($id, $blog_id = false) {


        global $wpdb, $MdlDb;





        if ($blog_id and IS_WPMU) {
            $prefix = $wpdb->get_blog_prefix($blog_id);
            $table_name = "{$prefix}arf_forms";
        } else {


            $table_name = $MdlDb->forms;


            $cache = wp_cache_get($id, 'arfform');


            if ($cache) {


                if (isset($cache->options))
                    $cache->options = maybe_unserialize($cache->options);





                return stripslashes_deep($cache);
            }
        }





        if (is_numeric($id))
            $where = array('id' => $id);
        else
            $where = array('form_key' => $id);





        $results = $MdlDb->get_one_record($table_name, $where);





        if (isset($results->options)) {


            wp_cache_set($results->id, $results, 'arfform');


            $results->options = maybe_unserialize($results->options);
        }

        
        return stripslashes_deep($results);
    }

    function getRefOne($id, $blog_id = false) {


        global $wpdb, $MdlDb;




	   
	    $table_name = $MdlDb->forms;
            $cache = wp_cache_get($id, 'arfform');


            if ($cache) {


                if (isset($cache->options))
                    $cache->options = maybe_unserialize($cache->options);





                return stripslashes_deep($cache);
            }





        if(is_numeric($id)){
            $where = array('id' => $id);
        } else {
            $where = array('form_key' => $id);
        }




        $results = $MdlDb->get_one_record($table_name, $where);





        if (isset($results->options)) {


            wp_cache_set($results->id, $results, 'arfform');


            $results->options = maybe_unserialize($results->options);
        }


        return stripslashes_deep($results);
    }

    function getsiteurl() {
        global $arsettingmodel;
        $siteurl = $arsettingmodel->checkdbstatus();
        return $siteurl;
    }

    function getAll($where = array(), $order_by = '', $limit = '', $is_ref_form = 0) {


        global $wpdb, $MdlDb, $armainhelper;





        if (is_numeric($limit))
            $limit = " LIMIT {$limit}";
        
	   $query = 'SELECT * FROM ' . $MdlDb->forms . $armainhelper->prepend_and_or_where(' WHERE ', $where) . $order_by . $limit;


        if ($limit == ' LIMIT 1' or $limit == 1) {


            if (is_array($where)) {
               $results = $MdlDb->get_one_record($MdlDb->forms, $where, '*', $order_by);
            } else {
                $results = $wpdb->get_row($query);
            }

            if ($results) {
                wp_cache_set($results->id, $results, 'arfform');
                $results->options = maybe_unserialize($results->options);
            }
        } else {
            
            if (is_array($where)) {
                $results = $MdlDb->get_records($MdlDb->forms, $where, $order_by, $limit);
            } else {
                if( isset($GLOBALS['arf_all_form_query']) && isset($GLOBALS['arf_all_form_query'][$query])){
                    $results = $GLOBALS['arf_all_form_query'][$query];
                } else {
                    $results = $wpdb->get_results($query);
                    if( !isset($GLOBALS['arf_all_form_query']) ){
                        $GLOBALS['arf_all_form_query'] = array();
                    }
                    $GLOBALS['arf_all_form_query'][$query] = $results;
                }
            }

            if ($results) {
                foreach ($results as $result) {
                    wp_cache_set($result->id, $result, 'arfform');
                    $result->options = maybe_unserialize($result->options);
                }
            }
        }





        return stripslashes_deep($results);
    }

    function getAll_forms_addon($return_results = array(),$where = array(), $order_by = '', $limit = '', $is_ref_form = 0) {
        global $wpdb, $MdlDb, $armainhelper;
        if (is_numeric($limit)){
            $limit = " LIMIT {$limit}";
        }
        $query = 'SELECT * FROM ' . $MdlDb->forms . $armainhelper->prepend_and_or_where(' WHERE ', $where) . $order_by . $limit;
        if ($limit == ' LIMIT 1' or $limit == 1) {
            if (is_array($where)) {
               $results = $MdlDb->get_one_record($MdlDb->forms, $where, '*', $order_by);
            } else {
                $results = $wpdb->get_row($query);
            }
            if ($results) {
                wp_cache_set($results->id, $results, 'arfform');
                $results->options = maybe_unserialize($results->options);
            }
        } else {
            if (is_array($where)) {           
                $results = $MdlDb->get_records($MdlDb->forms, $where, $order_by, $limit);
            } else {
                $results = $wpdb->get_results($query);
            }
            if ($results) {
                foreach ($results as $result) {
                    wp_cache_set($result->id, $result, 'arfform');
                    $result->options = maybe_unserialize($result->options);
                }
            }
        }
        foreach ($results as $key => $value) {
            $return_results[$key]['id'] = $value->id;
            $return_results[$key]['name'] = $value->name;
            $return_results[$key]['form_key'] = $value->form_key;
            $return_results[$key]['is_template'] = $value->is_template;
            $return_results[$key]['name_width_id'] = $value->name." (".$value->id.")";
            $return_results[$key]['status'] = $value->status;                    
        }        
        return stripslashes_deep($return_results);
    }

    function validate($values) {


        $errors = array();

        return apply_filters('arfvalidationofcurrentform', $errors, $values);
    }

    function has_field($type, $form_id, $single = true) {


        global $MdlDb;


        if ($single)
            $included = $MdlDb->get_one_record($MdlDb->fields, compact('form_id', 'type'));
        else
            $included = $MdlDb->get_records($MdlDb->fields, compact('form_id', 'type'));


        return $included;
    }

    function post_type($form_id) {


        if (is_numeric($form_id)) {


            global $MdlDb;


            $cache = wp_cache_get($form_id, 'arfform');


            if ($cache)
                $form_options = $cache->options;
            else
                $form_options = $MdlDb->get_var($MdlDb->forms, array('id' => $form_id), 'options');


            $form_options = maybe_unserialize($form_options);


            return (isset($form_options['post_type'])) ? $form_options['post_type'] : 'post';
        }else {


            $form = (array) $form_id;


            return (isset($form['post_type'])) ? $form['post_type'] : 'post';
        }
    }

}