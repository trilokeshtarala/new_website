<?php
class arfieldmodel {
   function __construct() {
        add_filter('arfbeforefieldcreated', array($this, 'createfield'));
        add_filter('arfupdatefieldoptions', array($this, 'updatefield'), 10, 3);
    }
    function createfield($field_data) {
        global $style_settings;
        if ($field_data['field_options']['label'] != 'none')
            $field_data['field_options']['label'] = '';
        if (!isset($field_data['field_options']['field_width']))
            $field_data['field_options']['field_width'] = '';
        if (empty($field_data['field_options']['label_width']))
            $field_data['field_options']['label_width'] = $style_settings->width;
        if (empty($field_data['field_options']['text_direction']))
            $field_data['field_options']['text_direction'] = $style_settings->text_direction;
        switch ($field_data['type']) {
            case 'number':
                $field_data['name'] = addslashes(esc_html__('Number', 'ARForms'));
                $field_data['field_options']['maxnum'] = 0;
                break;
            case 'select':
                $field_data['field_options']['size'] = $style_settings->auto_width;
                break;
            case 'date':
                $field_data['field_options']['size'] = '10';
                $field_data['name'] = addslashes(esc_html__('Date', 'ARForms'));
                break;
            case 'time':
                $field_data['field_options']['size'] = '10';
                $field_data['name'] = addslashes(esc_html__('Time', 'ARForms'));
                break;
            case 'phone':
                $field_data['field_options']['size'] = '15';
                $field_data['name'] = addslashes(esc_html__('Phone', 'ARForms'));
                break;
            case 'website':
            case 'url':
                $field_data['name'] = addslashes(esc_html__('Website', 'ARForms'));
                break;
            case 'email':
                $field_data['name'] = addslashes(esc_html__('Email', 'ARForms'));
                break;
            case 'password':
                $field_data['name'] = addslashes(esc_html__('Password', 'ARForms'));
                break;
            case 'html':
                $field_data['name'] = addslashes(esc_html__('HTML', 'ARForms'));
                break;
            case 'divider':
                $field_data['name'] = addslashes(esc_html__('Heading', 'ARForms'));
                $field_data['field_options']['label'] = 'top';
                break;
            case 'imagecontrol':
                $field_data['name'] = addslashes(esc_html__('Image', 'ARForms'));
                break;
            case 'colorpicker':
                $field_data['name'] = addslashes(esc_html__('Color', 'ARForms'));
                break;
            case 'arf_switch':
                $field_data['name'] = addslashes(esc_html__('switch', 'ARForms'));
                break;
            case 'break':
                global $MdlDb;
                $page_num = $MdlDb->get_count($MdlDb->fields, array("form_id" => $field_data['form_id'], "type" => 'break'));
                $field_data['name'] = addslashes(esc_html__('Page', 'ARForms')) . ' ' . ($page_num + 2);
        }
        return apply_filters('arf_before_createfield', $field_data);
    }

    function updatefield($field_options, $field, $values) {
        global $style_settings, $arfieldhelper;
        $defaults = $arfieldhelper->get_default_field_opts(false, $field);
        unset($defaults['dependent_fields']);
        unset($defaults['post_field']);
        unset($defaults['custom_field']);
        unset($defaults['taxonomy']);
        unset($defaults['exclude_cat']);
        $defaults['minnum'] = 1;
        $defaults['maxnum'] = 9999;
        $defaults['field_width'] = '';
        $defaults['label_width'] = $style_settings->width;
        $defaults['text_direction'] = $style_settings->text_direction;
        foreach ($defaults as $opt => $default)
            $field_options[$opt] = isset($values['field_options'][$opt . '_' . $field->id]) ? $values['field_options'][$opt . '_' . $field->id] : $default;
        if ($field->type == 'scale') {
            global $arffield;
            $options = array();
            if ((int) $field_options['maxnum'] >= 99)
                $field_options['maxnum'] = 5;
            for ($i = $field_options['minnum']; $i <= $field_options['maxnum']; $i++)
                $options[] = $i;
            $arffield->update($field->id, array('options' => maybe_serialize($options)));
        }
        return $field_options;
    }

    function create($values, $return = true, $template = false, $res_field_id = '') {
        global $wpdb, $MdlDb, $armainhelper,$arformcontroller;
        $new_values = array();
        $key = isset($values['field_key']) ? $values['field_key'] : $values['name'];
        $new_values['field_options'] = (!is_array($values['field_options'])) ? json_decode($values['field_options'],true) : $values['field_options'];
        $new_values['field_key'] = $armainhelper->get_unique_key('', $MdlDb->fields, 'field_key');
        foreach (array('name', 'type') as $col)
            $new_values[$col] = stripslashes($values[$col]);
        $new_values['options'] = isset($values['options']) ? $values['options'] : NULL;
        $new_values['required'] = isset($values['required']) ? (int) $values['required'] : NULL;
        $new_values['form_id'] = isset($values['form_id']) ? (int) $values['form_id'] : NULL;
        $new_values['option_order'] = isset($values['option_order']) ? maybe_serialize($values['option_order']) : 0;
        if (isset($new_values['field_options']['classes']) && $new_values['field_options']['classes'] == ""){
            $new_values['field_options']['classes'] = 'arf_1';
        }
        if(isset($new_values['field_options']['key'])){
            $new_values['field_options']['key'] = $new_values['field_key'];            
        }        
        if (isset($new_values['field_options']['required_indicator']) && $new_values['field_options']['required_indicator'] == ""){
            $new_values['field_options']['required_indicator'] = '*';
        }
        $new_values['field_options'] = is_array($new_values['field_options']) ? json_encode($new_values['field_options']) : $new_values['field_options'];
        $new_values['created_date'] = current_time('mysql');
        $conditional_logic = array(
            'enable' => 0,
            'display' => 'show',
            'if_cond' => 'all',
            'rules' => array(),
        );
        $query_results = $wpdb->insert($MdlDb->fields, $new_values);
        if ($return) {
            if ($query_results) {
                $return_insert_id = $wpdb->insert_id;
                if ($template) {
                    if ($res_field_id != '')
                        $_SESSION['arf_fields'][$res_field_id] = $return_insert_id;
                }
                return $return_insert_id;
            } else
                return false;
        } else {
            if ($query_results) {
                $return_insert_id = $wpdb->insert_id;
                if ($template) {
                    if ($res_field_id != '')
                        $_SESSION['arf_fields'][$res_field_id] = $return_insert_id;
                }
            }
        }
    }

    function duplicate($old_form_id, $form_id, $copy_keys = false, $blog_id = false, $template = false) {
        global $wpdb, $MdlDb, $armainhelper;
        $form_options = $wpdb->get_results($wpdb->prepare("SELECT `options` FROM `" . $MdlDb->forms . "` WHERE `id` = %d", $old_form_id));
        $form_opts = maybe_unserialize($form_options[0]->options);
        $field_order = isset($form_opts['arf_field_order']) ? json_decode($form_opts['arf_field_order']) : array();
        $form_fields = $this->getAll("fi.form_id = $old_form_id", '', '', $blog_id);
        $new_form_fields = array();
        if (!empty($field_order) && count($field_order) > 0) {
            foreach ($field_order as $field_id => $field_ord) {
                foreach ($form_fields as $field) {
                    if ($field->id == $field_id) {
                        $new_form_fields[] = $field;
                    }
                }
            }
        }
        $new_field_order = array();
        $n = 1;
        if (!empty($new_field_order)) {
            $form_fields = $new_form_fields;
        }
        foreach ($form_fields as $field) {
            $values = array();
            $new_key = ($copy_keys) ? $field->field_key : '';
            $values['field_key'] = $new_key;
            $values['options'] = maybe_serialize($field->options);
            $conditional_logic = array(
                'enable' => 0,
                'display' => 'show',
                'if_cond' => 'all',
                'rules' => array(),
            );
            
            $values['form_id'] = $form_id;
            $res_field_id = $field->id;
            
            foreach (array('name', 'description', 'type', 'default_value', 'required', 'field_options', 'option_order') as $col)
                if ($col == "default_value") {
                    $values{$col} = maybe_serialize($field->$col);
                } else {
                    $values[$col] = $field->{$col};
                }
            $new_field_id = $this->create($values, true, $template, $res_field_id);
            $new_field_order[$new_field_id] = $n;
            $n++;
            unset($field);
        }
    }

    function update($id, $values) {
        global $wpdb, $MdlDb, $arfieldhelper, $armainhelper;
        if (isset($values['field_key']))
            $values['field_key'] = $armainhelper->get_unique_key($values['field_key'], $MdlDb->fields, 'field_key', $id);
        if (empty($values['field_options']['required_indicator']))
            $values['field_options']['required_indicator'] = '*';
        if (isset($values['field_options']) and is_array($values['field_options']))
            $values['field_options'] = maybe_serialize($values['field_options']);
        if (isset($_REQUEST['conditional_logic_' . $id]) and stripslashes_deep($_REQUEST['conditional_logic_' . $id]) == '1') {
            $conditional_logic_display = stripslashes_deep($_REQUEST['conditional_logic_display_' . $id]);
            $conditional_logic_if_cond = stripslashes_deep($_REQUEST['conditional_logic_if_cond_' . $id]);
            $conditional_logic_rules = array();
            $rule_array = $_REQUEST['rule_array_' . $id] ? $_REQUEST['rule_array_' . $id] : array();
            if (count($rule_array) > 0) {
                $i = 1;
                foreach ($rule_array as $v) {
                    $conditional_logic_field = stripslashes_deep($_REQUEST['arf_cl_field_' . $id . '_' . $v]);
                    $conditional_logic_field_type = $arfieldhelper->get_field_type($conditional_logic_field);
                    $conditional_logic_op = stripslashes_deep($_REQUEST['arf_cl_op_' . $id . '_' . $v]);
                    $conditional_logic_value = stripslashes_deep($_REQUEST['cl_rule_value_' . $id . '_' . $v]);
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
            $values['conditional_logic'] = maybe_serialize($conditional_logic);
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
            $values['conditional_logic'] = maybe_serialize($conditional_logic);
        }
        $query_results = $wpdb->update($MdlDb->fields, $values, array('id' => $id));
        unset($values);
        if ($query_results)
            wp_cache_delete($id, 'arf_field');
        return $query_results;
    }
    /* arf_dev_flag remove below function and confirm actions and inside field */
    function destroy($id) {
        global $wpdb, $MdlDb;
        do_action('arfbeforedestroyfield', $id);
        do_action('arfbeforedestroyfield_' . $id);
        $wpdb->query($wpdb->prepare("DELETE FROM $MdlDb->entry_metas WHERE field_id=%d", $id));
        return $wpdb->query($wpdb->prepare("DELETE FROM $MdlDb->fields WHERE id=%d", $id));
    }

    function getOne($id) {
        global $wpdb, $MdlDb;
        $results = wp_cache_get($id, 'arf_field');
        if (!$results) {
            if (is_numeric($id))
                $where = array('id' => $id);
            else
                $where = array('field_key' => $id);
            $results = $MdlDb->get_one_record($MdlDb->fields, $where);
            if ($results)
                wp_cache_set($results->id, $results, 'arf_field');
        }
        if ($results) {
            if( is_array($results->field_options) ){
                $results->field_options = $results->field_options;
            } else {
                $results->field_options = json_decode( $results->field_options,true);
                if( json_last_error() != JSON_ERROR_NONE ){
                    $results->field_options = maybe_unserialize($results->field_options);
                }
            }
            $results->options = maybe_unserialize($results->options);
            $results->default_value = isset($results->field_options['default_value']) ? maybe_unserialize($results->field_options['default_value']) :'';
            $results->option_order = maybe_unserialize($results->option_order);
        }
        return stripslashes_deep($results);
    }

    function getAll($where = array(), $order_by = '', $limit = '', $blog_id = false, $is_ref_form = 0) {
        global $wpdb, $MdlDb, $armainhelper;
        $table_name = $MdlDb->fields;
        $form_table_name = $MdlDb->forms;
        if (!empty($order_by) and ! preg_match("/ORDER BY/", $order_by))
            $order_by = " ORDER BY {$order_by}";
        if (is_numeric($limit))
            $limit = " LIMIT {$limit}";
        $query = 'SELECT fi.*, ' .
                'fr.name as form_name ' .
                'FROM ' . $table_name . ' fi ' .
                'LEFT OUTER JOIN ' . $form_table_name . ' fr ON fi.form_id=fr.id';
        if (is_array($where)) {
            extract($MdlDb->get_where_clause_and_values($where));
            $query .= "{$where}{$order_by}{$limit}";
            $query = $wpdb->prepare($query, $values);
        } else {
            $query .= $armainhelper->prepend_and_or_where(' WHERE ', $where);
            $query .= ' ' . $order_by . ' ' . $limit;
        }

        if( $table_name == $MdlDb->fields ){
            $form_id = preg_replace('/(.*?)\=(\d+)/','$2',$where);
            $form_id = (int)$form_id;
            if( $limit == 'LIMIT 1' || $limit == 1 ){
                if( isset($GLOBALS['arf_form_fields_with_limit']) && isset($GLOBALS['arf_form_fields_with_limit'][$form_id]) && $GLOBALS['arf_form_fields_with_limit']['query'][$form_id] == $query ){
                    $results = $GLOBALS['arf_form_fields_with_limit'][$form_id];
                } else {
                    $results = $wpdb->get_row($query);
                    if( !isset($GLOBALS['arf_form_fields_with_limit']) ){
                        $GLOBALS['arf_form_fields_with_limit'] = array();
                        $GLOBALS['arf_form_fields_with_limit']['query'] = array();
                    }
                    $GLOBALS['arf_form_fields_with_limit']['query'][$form_id] = $query;
                    $GLOBALS['arf_form_fields_with_limit'][$form_id] = $results;
                }
            } else {
                if( isset($GLOBALS['arf_form_fields_without_limit']) && isset($GLOBALS['arf_form_fields_without_limit'][$form_id]) && $GLOBALS['arf_form_fields_without_limit']['query'][$form_id] == $query ){
                    $results = $GLOBALS['arf_form_fields_without_limit'][$form_id];
                } else {
                    $results = $wpdb->get_results($query);
                    if( !isset($GLOBALS['arf_form_fields_without_limit']) ){
                        $GLOBALS['arf_form_fields_without_limit'] = array();
                        $GLOBALS['arf_form_fields_without_limit']['query'] = array();
                    }
                    $GLOBALS['arf_form_fields_without_limit']['query'][$form_id] = $query;
                    $GLOBALS['arf_form_fields_without_limit'][$form_id] = $results;
                }
            }
        } else {
            if ($limit == ' LIMIT 1' or $limit == 1){
                $results = $wpdb->get_row($query);
            } else {
                $results = $wpdb->get_results($query);  
            }
        }
        $pattern = '/(fi.form_id=(\d+))/';
        preg_match_all($pattern,$where,$matches);
        if( isset($matches[2]) && isset($matches[2][0]) ){
            $form_id = $matches[2][0];            
            $GLOBALS['arf_form_field_data'][$form_id] = $results;
        }
        if ($results) {
            if (is_array($results)) {
                foreach ($results as $r_key => $result) {
                    wp_cache_set($result->id, $result, 'arf_field');
                    wp_cache_set($result->field_key, $result, 'arf_field');
                    if( is_array($result->field_options) ){
                        $results[$r_key]->field_options = $result->field_options;
                    } else {
                        $results[$r_key]->field_options = json_decode($result->field_options, true);
                        if (json_last_error() != JSON_ERROR_NONE) {
                            $results[$r_key]->field_options = maybe_unserialize($result->field_options);
                        }
                    }
        		    $results[$r_key]->field_options['arf_regular_expression'] = isset($results[$r_key]->field_options['arf_regular_expression']) ? addslashes($results[$r_key]->field_options['arf_regular_expression']) : '';
                    if( is_array($result->options) ){
                        $results[$r_key]->options = $result->options;
                    } else {
                        $results[$r_key]->options = json_decode($result->options, true);
                        if (json_last_error() != JSON_ERROR_NONE) {
                            $results[$r_key]->options = maybe_unserialize($result->options);
                        }
                    }
                    $results[$r_key]->default_value = isset($result->field_options['default_value']) ? maybe_unserialize($result->field_options['default_value']) : '';
                    $results[$r_key]->option_order = maybe_unserialize($result->option_order);
                }
            } else {
                wp_cache_set($results->id, $results, 'arf_field');
                wp_cache_set($results->field_key, $results, 'arf_field');
                $results->field_options = maybe_unserialize($results->field_options);
                $results->options = maybe_unserialize($results->options);
                $results->default_value = $results->default_value;
                $results->option_order = maybe_unserialize($results->option_order);
            }
        }
        return stripslashes_deep($results);
    }
}