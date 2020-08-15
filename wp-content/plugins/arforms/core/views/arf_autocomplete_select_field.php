<?php
define('ARF_AUTOCOMPLETE_SLUG', 'arf_autocomplete');

global $arf_autocomplete_field_class_name, $arf_autocomplete_new_field_data, $arf_autocomplete_field_image_path;

$arf_autocomplete_field_image_path = array(ARF_AUTOCOMPLETE_SLUG => ARFIMAGESURL . '/fields_elements_icon/autocomplete-field-icon.png');
$arf_autocomplete_field_class_name = array(ARF_AUTOCOMPLETE_SLUG => 'purple');
$arf_autocomplete_new_field_data = array(ARF_AUTOCOMPLETE_SLUG => addslashes(esc_html__('Autocomplete', 'ARForms')));
$arf_autocomplete_total_class = array();
$arf_autocomplete_field_class = new arf_autocomplete_field();

global $arf_autocomplete_field_loaded;
$arf_autocomplete_field_loaded = array();

class arf_autocomplete_field {

    function __construct() {

        add_action('arfafterbasicfieldlisting', array($this, 'arf_add_autocomplete_field'), 10, 2);

        add_filter('arf_all_field_css_class_for_editor', array($this, 'arf_get_autocomplete_field_class'), 10, 3);

        add_filter('arfavailablefieldsbasicoptions', array($this, 'add_availablefieldsbasicoptions'), 10, 3);

        /* arf_dev_flag convert from action to filter */
        add_filter('form_fields', array($this, 'add_autocomplete_field_to_frontend'), 11, 11);

        add_filter('arf_before_createfield', array($this, 'arf_autocomplete_createfield'), 10, 2);    // Before Create new filed

        add_action('arf_field_option_model_outside',array($this,'arf_add_autocomplete_field_options'));
        
        add_filter('arf_add_more_field_options_outside',array($this,'arf_add_autocomplete_default_field_options'),10,2);
        
        add_filter('arf_field_values_options_outside',array($this,'arf_field_values_options_outside_function'),10);
        
        add_filter('arf_bootstraped_field_from_outside',array($this,'arf_bootstraped_field_from_outside_function'),10);
        
        add_filter('arf_new_field_array_filter_outside', array($this, 'arf_add_autocomplete_field_in_array'),10,4);
        
        add_filter('arf_new_field_array_materialize_filter_outside', array($this, 'arf_add_autocomplete_field_in_array_materialize'),10,4);
        
        add_action('arf_load_bootstrap_js_from_outside',array($this,'arf_load_bootstrap_js_from_outside_function'),10,1);
        
        add_filter('arf_installed_fields_outside',array($this,'arf_install_autocomplete_field'),10);

        //add_filter('arf_onchange_only_click_event_outside',array($this,'arf_auto_complete_change_type_func'),10);

        add_filter('arf_positioned_field_options_icon',array($this,'arf_positioned_field_options_icon_for_autocomplete'),10,2);

        add_filter('arf_default_value_array_field_type_from_itemmeta', array($this,'arf_default_value_array_field_type_autocomplete'),10);
    }

    function arf_default_value_array_field_type_autocomplete($field_types){
        array_push($field_types,ARF_AUTOCOMPLETE_SLUG);
        return $field_types;
    }

    

    function arf_positioned_field_options_icon_for_autocomplete($positioned_icon,$field_icons){
        $positioned_icon[ARF_AUTOCOMPLETE_SLUG] = "{$field_icons['arf_edit_option_icon']}{$field_icons['field_require_icon']}".str_replace('{arf_field_type}',ARF_AUTOCOMPLETE_SLUG,$field_icons['arf_field_duplicate_icon'])."{$field_icons['field_delete_icon']}".str_replace('{arf_field_type}',ARF_AUTOCOMPLETE_SLUG,$field_icons['field_option_icon'])."{$field_icons['arf_field_move_icon']}";
        return $positioned_icon;
    }

    function arf_add_autocomplete_field_in_array_materialize($fields,$field_icons,$field_json,$positioned_field_icons) {
        global $arfieldhelper;
        
        $field_opt_arr = $arfieldhelper->arf_getfields_basic_options_section();        
        $field_order_arf_autocomplete = isset($field_opt_arr['arf_autocomplete']) ? $field_opt_arr['arf_autocomplete'] : '';        
        $field_data_array = $field_json;
        $field_data_obj_arf_autocomplete = $field_data_array->field_data->arf_autocomplete;

        $fields['arf_autocomplete'] = "<div class='arf_inner_wrapper_sortable arfmainformfield edit_form_item arffieldbox ui-state-default 1  arf1columns single_column_wrapper' data-id='arf_editor_main_row_{arf_editor_index_row}'><div class='arf_multiiconbox'><div class='arf_field_option_multicolumn' id='arf_multicolumn_wrapper'><input type='hidden' name='multicolumn' />{$field_icons['multicolumn_one']} {$field_icons['multicolumn_two']} {$field_icons['multicolumn_three']} {$field_icons['multicolumn_four']} {$field_icons['multicolumn_five']} {$field_icons['multicolumn_six']}</div>{$field_icons['multicolumn_expand_icon']}</div><div class='sortable_inner_wrapper edit_field_type_arf_autocomplete' inner_class='arf_1col' id='arfmainfieldid_{arf_field_id}'><div id='arf_field_{arf_field_id}' class='arfformfield control-group arfmainformfield top_container  arfformfield  arf_field_{arf_field_id}'><div class='arf_fieldiconbox arf_fieldiconbox_with_edit_option'>".$positioned_field_icons[ARF_AUTOCOMPLETE_SLUG]."</div><div class='controls input-field'><input id='field_{arf_unique_key}' name='item_meta[{arf_field_id}]' type='text' class='' style='float: left;'><label class='arf_main_label' id='field_{arf_field_id}'><span class='arfeditorfieldopt_label arf_edit_in_place'><input type='text' class='arf_edit_in_place_input inplace_field' data-ajax='false' data-field-opt-change='true' data-field-opt-key='name' value='Autocomplete' data-field-id='{arf_field_id}' /></span><span id='require_field_{arf_field_id}'><a  href='javascript:void(0);' onclick='javascript:arfmakerequiredfieldfunction({arf_field_id},0,1)' class='arfaction_icon arfhelptip arffieldrequiredicon alignleft arfcheckrequiredfield0' id='req_field_{arf_field_id}' title='Click to mark as not compulsory field'></a></span></label><div class='arf_field_description' id='field_description_{arf_field_id}'></div><div class='help-block'></div></div><input type='hidden' name='arf_field_data_{arf_field_id}' id='arf_field_data_{arf_field_id}' value='". htmlspecialchars(json_encode($field_data_obj_arf_autocomplete))."' data-field_options='".json_encode($field_order_arf_autocomplete)."' /><div class='arf_field_option_model arf_field_option_model_cloned' data-field_id='{arf_field_id}'><div class='arf_field_option_model_header'>".esc_html__('Field Options','ARForms')."</div><div class='arf_field_option_model_container'><div class='arf_field_option_content_row'></div></div><div class='arf_field_option_model_footer'><button type='button' class='arf_field_option_close_button' onClick='arf_close_field_option_popup({arf_field_id});'>".esc_html__('Cancel','ARForms')."</button><button type='button' class='arf_field_option_submit_button' data-field_id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button></div></div><div class='arf_field_values_model' id='arf_field_values_model_skeleton_{arf_field_id}'><div class='arf_field_values_model_header'>".esc_html__('Edit Options','ARForms')."</div><div class='arf_field_values_model_container'><div class='arf_field_values_content_row'><div class='arf_field_values_content_loader'><svg version='1.1' id='arf_field_values_loader' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='48px' height='48px' viewBox='0 0 26.349 26.35' style='enable-background:new 0 0 26.349 26.35;' fill='#3f74e7' xml:space='preserve' ><g><g><circle cx='13.792' cy='3.082' r='3.082' /><circle cx='13.792' cy='24.501' r='1.849'/><circle cx='6.219' cy='6.218' r='2.774'/><circle cx='21.365' cy='21.363' r='1.541'/><circle cx='3.082' cy='13.792' r='2.465'/><circle cx='24.501' cy='13.791' r='1.232'/><path d='M4.694,19.84c-0.843,0.843-0.843,2.207,0,3.05c0.842,0.843,2.208,0.843,3.05,0c0.843-0.843,0.843-2.207,0-3.05 C6.902,18.996,5.537,18.988,4.694,19.84z'/><circle cx='21.364' cy='6.218' r='0.924'/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg></div></div></div><div class='arf_field_values_model_footer'><button type='button' class='arf_field_values_close_button'>".esc_html__('Cancel','ARForms')."</button><button type='button' class='arf_field_values_submit_button' data-field-id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button></div></div></div></div></div>";
        return $fields;
    }
    
    function arf_add_autocomplete_field_in_array($fields,$field_icons,$field_json,$positioned_field_icons) {
        global $arfieldhelper;
        
        $field_opt_arr = $arfieldhelper->arf_getfields_basic_options_section();        
        $field_order_arf_autocomplete = isset($field_opt_arr['arf_autocomplete']) ? $field_opt_arr['arf_autocomplete'] : '';        
        $field_data_array = $field_json;
        $field_data_obj_arf_autocomplete = $field_data_array->field_data->arf_autocomplete;

        $fields['arf_autocomplete'] = "<div class='arf_inner_wrapper_sortable arfmainformfield edit_form_item arffieldbox ui-state-default 1  arf1columns single_column_wrapper' data-id='arf_editor_main_row_{arf_editor_index_row}'><div class='arf_multiiconbox'><div class='arf_field_option_multicolumn' id='arf_multicolumn_wrapper'><input type='hidden' name='multicolumn' />{$field_icons['multicolumn_one']} {$field_icons['multicolumn_two']} {$field_icons['multicolumn_three']} {$field_icons['multicolumn_four']} {$field_icons['multicolumn_five']} {$field_icons['multicolumn_six']}</div>{$field_icons['multicolumn_expand_icon']}</div><div class='sortable_inner_wrapper edit_field_type_arf_autocomplete' inner_class='arf_1col' id='arfmainfieldid_{arf_field_id}'><div id='arf_field_{arf_field_id}' class='arfformfield control-group arfmainformfield top_container  arfformfield  arf_field_{arf_field_id}'><div class='fieldname-row' style='display : block;'><div class='fieldname'><label class='arf_main_label' id='field_{arf_field_id}'><span class='arfeditorfieldopt_label arf_edit_in_place'><input type='text' class='arf_edit_in_place_input inplace_field' data-ajax='false' data-field-opt-change='true' data-field-opt-key='name' value='Autocomplete' data-field-id='{arf_field_id}' /></span><span id='require_field_{arf_field_id}'><a href='javascript:void(0);' onclick='javascript:arfmakerequiredfieldfunction({arf_field_id},0,1)' class='arfaction_icon arfhelptip arffieldrequiredicon alignleft arfcheckrequiredfield0' id='req_field_{arf_field_id}' title='Click to mark as not compulsory field'></a></span></label></div></div><div class='arf_fieldiconbox arf_fieldiconbox_with_edit_option'>".$positioned_field_icons[ARF_AUTOCOMPLETE_SLUG]."</div><div class='controls'><input id='field_{arf_unique_key}' name='item_meta[{arf_field_id}]' data-items='1' data-provide='typeahead' type='text' class='' style='float: left;'><div class='arf_field_description' id='field_description_{arf_field_id}'></div><div class='help-block'></div></div><input type='hidden' name='arf_field_data_{arf_field_id}' id='arf_field_data_{arf_field_id}' value='". htmlspecialchars(json_encode($field_data_obj_arf_autocomplete))."' data-field_options='".json_encode($field_order_arf_autocomplete)."' /><div class='arf_field_option_model arf_field_option_model_cloned' data-field_id='{arf_field_id}'><div class='arf_field_option_model_header'>".esc_html__('Field Options','ARForms')."</div><div class='arf_field_option_model_container'><div class='arf_field_option_content_row'></div></div><div class='arf_field_option_model_footer'><button type='button' class='arf_field_option_close_button' onClick='arf_close_field_option_popup({arf_field_id});'>".esc_html__('Cancel','ARForms')."</button><button type='button' class='arf_field_option_submit_button' data-field_id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button></div></div><div class='arf_field_values_model' id='arf_field_values_model_skeleton_{arf_field_id}'><div class='arf_field_values_model_header'>".esc_html__('Edit Options','ARForms')."</div><div class='arf_field_values_model_container'><div class='arf_field_values_content_row'><div class='arf_field_values_content_loader'><svg version='1.1' id='arf_field_values_loader' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='48px' height='48px' viewBox='0 0 26.349 26.35' style='enable-background:new 0 0 26.349 26.35;' fill='#3f74e7' xml:space='preserve' ><g><g><circle cx='13.792' cy='3.082' r='3.082' /><circle cx='13.792' cy='24.501' r='1.849'/><circle cx='6.219' cy='6.218' r='2.774'/><circle cx='21.365' cy='21.363' r='1.541'/><circle cx='3.082' cy='13.792' r='2.465'/><circle cx='24.501' cy='13.791' r='1.232'/><path d='M4.694,19.84c-0.843,0.843-0.843,2.207,0,3.05c0.842,0.843,2.208,0.843,3.05,0c0.843-0.843,0.843-2.207,0-3.05 C6.902,18.996,5.537,18.988,4.694,19.84z'/><circle cx='21.364' cy='6.218' r='0.924'/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg></div></div></div><div class='arf_field_values_model_footer'><button type='button' class='arf_field_values_close_button'>".esc_html__('Cancel','ARForms')."</button><button type='button' class='arf_field_values_submit_button' data-field-id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button></div></div></div></div></div>";
        return $fields;
    }
    function arf_add_autocomplete_field($id = '', $is_ref_form = '', $values = '') {

        global $arf_autocomplete_field_class_name, $arf_autocomplete_new_field_data, $arf_autocomplete_field_image_path, $arf_autocomplete_total_class;

        if (is_rtl()) {
            $floating_style = 'float:right;';
        } else {
            $floating_style = 'float:left;';
        }

        foreach ($arf_autocomplete_new_field_data as $field_key => $field_type) {
            ?>
            <li class="arf_form_element_item frmbutton frm_t<?php echo $field_key ?>" id="<?php echo $field_key; ?>" data-field-id="<?php echo $id; ?>" data-type="<?php echo $field_key; ?>">
                <div class="arf_form_element_item_inner_container">
                    <span class="arf_form_element_item_icon">
                        <svg viewBox="0 0 30 30"><g id="autocomplete"><path fill-rule="evenodd" clip-rule="evenodd" fill="#4F5562" d="M25.447,9.119v19.762h-2v-0.017h-20v0.019h-2V2.864h-0.01v-2H17.15l0.083-0.091l0.091,0.091h0.119v0.121l7.893,7.973h0.111V9.07l0.023,0.023L25.447,9.119z M16.447,3.041v5.813h5.755L16.447,3.041z M14.451,10.854V8.96h-0.004V2.966h1.926l-0.101-0.102H3.447v24h20v-16.01H14.451z M11.937,22.996l-0.158,0.002l-4.84-4.839l1.613-1.614l3.385,3.447l6.772-6.053l1.613,1.613l-8.227,7.445L11.937,22.996z"/></g></svg>
                    </span>
                    <label class="arf_form_element_item_text"><?php echo $field_type; ?></label>
                </div>
            <li>
            <?php
        }
    }

    function arf_get_autocomplete_field_class($class) {
        global $arf_autocomplete_field_class_name, $arf_autocomplete_total_class;
        $as_class = array_merge($class, $arf_autocomplete_field_class_name);
        $arf_autocomplete_total_class = count($as_class);
        return $as_class;
    }

    function add_availablefieldsbasicoptions($basic_option) {

        $autocomplete_field_option = array(
            ARF_AUTOCOMPLETE_SLUG => array(
                'labelname' => 1,
                'fielddescription' => 2,
                'tooltipmsg' => 3,
                'placeholdertext' => 4,
                'requiredmsg' => 5,
                'customwidth' => 6,
            )
        );

        return array_merge($basic_option, $autocomplete_field_option);
    }

    function arf_autocomplete_createfield($field_data) {

        if ($field_data['type'] == ARF_AUTOCOMPLETE_SLUG) {
            $field_data['name'] = addslashes(esc_html__('Autocomplete', 'ARForms'));
        }
        return $field_data;
    }

        function add_autocomplete_field_to_frontend($return_string, $form, $field_name, $arf_data_uniq_id, $field, $field_tootip, $field_description, $res_data, $inputStyle,$arf_main_label,$get_onchage_func_data) {

            if ($field['type'] != ARF_AUTOCOMPLETE_SLUG) {
                return $return_string;
            }

            global $style_settings, $arfsettings, $arfeditingentry, $arffield, $arfieldhelper, $arfversion, $arf_form_all_footer_js, $wpdb, $MdlDb;
            $entry_id = $arfeditingentry;
            
            $field_width = '';
            if (isset($field['field_width']) && $field['field_width'] != '') {
                $field_width = 'style="width:' . $field['field_width'] . 'px;"';
            }

            $form_data = new stdClass();
            $form_data->id = $form->id;
            $form_data->form_key = $form->form_key;
            $form_data->options = maybe_serialize($form->options);

            if ($res_data == '') {
                $res_data = $wpdb->get_results($wpdb->prepare("SELECT id, type, field_options,conditional_logic FROM " . $MdlDb->fields . " WHERE form_id = %d ORDER BY id", $form->id));
            }

            if ($field['type'] == ARF_AUTOCOMPLETE_SLUG) {
                if (isset($field['set_field_value'])) {
                    $field['default_value'] = $field['set_field_value'];
                }

                $field_tooltip_class = "";
                $field_tootip_material = "";
                $field_tootip_standard =  "";
                if($field_tootip!='')
                {
                    if($inputStyle=="material")
                    {
                        $field_tootip_material = $field_tootip;
                        $field_tooltip_class = " arfhelptipfocus ";
                    }
                    else {
                        $field_tootip_standard = $field_tootip;
                    }
                    
                }

                $return_string .= '<div class="controls'.$field_tooltip_class.'" '.$field_width.' '.$field_tootip_material.'>';
                if( $inputStyle == 'material' ){
                    $return_string .= $arf_main_label;
                }

                if (isset($field['options']) && is_array($field['options']) && !empty($field['options'])) {
                    if (isset($field['separate_value']) && $field['separate_value'] == '1') {
                        $arfsepvaluesLabels = array();
                        if( $inputStyle == 'material' ){
                            $autocomplete_separate_value = array();
                            $is_default_value_blank = false;
                        } else {
                            $autocomplete_separate_value = '';
                        }
                        foreach ($field['options'] as $k => $options) {
                            if ($options['value'] == $field['default_value']) {
                                if( $inputStyle == 'material' && $options['value'] == '' ){
                                    $is_default_value_blank = true;
                                }
                                $field['default_value'] = $options['label'];
                            }

                            if( $inputStyle == 'material' ){
                                $autocomplete_separate_value[esc_attr($options['value'])] = esc_attr($options['label']);
                            } else {
                                $autocomplete_separate_value .= "{id: '" . esc_attr($options['value']) . "', name: '" . esc_attr($options['label']) . "'},";
                            }

                            $arfsepvaluesLabels[esc_attr($options['value'])] = esc_attr($options['label']);

                        }

                        $arf_autocomplete_id = 'arf_autocomplete_' . $field['id'] . '';
                        if( $inputStyle == 'material' ){
                            $autocomplete_separate_value = json_encode($autocomplete_separate_value);
                            $arf_autocomplete_id = 'field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '';
                        }
                        $arfsepvaluesLabels = json_encode($arfsepvaluesLabels);

                        $return_string .= '<input id="' . $arf_autocomplete_id . '" data-id="' . $field['id'] . '" type="text" name="arf_autocomplete_' . $field['id'] . '" data-field-id="arf_autocomplete_value_' . $field['id'] . '"  autocomplete="off" ';
                        if (isset($field['required']) and $field['required']) {
                            $return_string .= 'data-validation-required-message="' . esc_attr($field['blank']) . '" ';
                        }
                        if(isset($is_default_value_blank) && $is_default_value_blank == true && $field['default_value'] != "" ){
                            $return_string .= '  placeholder="' . $field['default_value'] . '" ';
                        } else {
                            $return_string .= '  value="' . $field['default_value'] . '" ';
                        }
                        $return_string .= $get_onchage_func_data;

                        if( $inputStyle == 'material' ){
                            $return_string .= ' data-source=\''.$autocomplete_separate_value.'\'';
                            $return_string .= ' class="arf_auto_complete arf_has_separate_value" ';
                        }
                        $return_string .=' autocomplete="off" />';
                        $return_string .='<input  id="arf_autocomplete_value_' . $field['id'] . '" type="hidden" name="' . $field_name . '" value="' . $field['default_value'] . '" ';

                        $return_string .= ' data-sep-labels=\''.$arfsepvaluesLabels.'\'';

                        $return_string .= $get_onchage_func_data;

                    $return_string .='/>';

                    /* arf_dev_flag inline js */

                        if( $inputStyle != 'material' ){
                            $arf_form_all_footer_js .='
                                jQuery("#arf_autocomplete_' . $field['id'] . '").typeahead({
                                    source: [' . substr($autocomplete_separate_value, 0, -1) . '],
                                    onSelect: function (item) {
                                        jQuery("#arf_autocomplete_value_' . $field['id'] . '").val(item.value);
                                    },
                                });';
                        }
                    } else {
                        $autocomplete_value = '';

                    foreach ($field['options'] as $k => $options) {
                        if (is_array($options)) {
                            $autocomplete_value .= '"' . addslashes(esc_attr($options['label'])) . '",';
                        } else {
                            $autocomplete_value .= '"' . addslashes(esc_attr($options)) . '",';
                        }
                    }   
                    $placeholdertext = "";
                    if(isset($field['placeholdertext']) && $field['placeholdertext']!=""){
                        $placeholdertext = 'placeholder="'.$field['placeholdertext'].'"';
                    }

                        $material_input_cls = ($inputStyle == 'material') ? 'arf_auto_complete' : '';
                        $arf_data_provided = ($inputStyle != 'material' ) ? 'data-provide="typeahead"' : '';

                        $return_string .= '<input type="text"   id="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '" name="' . $field_name . '" data-source=\'[' . substr($autocomplete_value, 0, -1) . ']\' data-items="10" '.$arf_data_provided.' class="'.$inputStyle.' '.$material_input_cls.'" value="' . $field['default_value'] . '" ';

                    if (isset($field['required']) and $field['required']) {
                        $return_string .=' data-validation-required-message="' . esc_attr($field['blank']) . '" ';
                    }
                    ///$return_string .= $arfieldhelper->get_onchage_func($field, $arf_data_uniq_id,$form_data,$res_data);
                    $return_string .= $get_onchage_func_data;
                    $return_string .= $placeholdertext;
                    $return_string .=' autocomplete="off" />';
                }
            }

                
                $return_string .=$field_tootip_standard;
                $return_string .=$field_description;

            $return_string .='</div>';
        }

            return $return_string;
        }

    function arf_add_autocomplete_field_options(){
        
    }
    
    function arf_add_autocomplete_default_field_options($field_options,$type){
        if( $type == ARF_AUTOCOMPLETE_SLUG ){
            $field_options['options'] = json_encode(array('','Select 1'));
        }
        return $field_options;
    }
    
    function arf_field_values_options_outside_function($fields){
        $count = count($fields);
        $fields[$count+1] = ARF_AUTOCOMPLETE_SLUG;
        return $fields;
    }

    function arf_bootstraped_field_from_outside_function($bootstraped_field){
        $bootstraped_field[count($bootstraped_field) + 1] = ARF_AUTOCOMPLETE_SLUG;
        return $bootstraped_field;
    }
    
    function arf_load_bootstrap_js_from_outside_function($field_type){
        global $arfversion;
        if( $field_type == ARF_AUTOCOMPLETE_SLUG ){

            wp_enqueue_script('bootstrap-typeahead-js');
        }
    }

    function arf_install_autocomplete_field($arf_installed_fields){
        array_push($arf_installed_fields,ARF_AUTOCOMPLETE_SLUG);
        return $arf_installed_fields;
    }
}
?>