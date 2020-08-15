<?php
define('ARF_SWITCH_SLUG', 'arf_switch');

global $arf_switch_field_class_name, $arf_switch_new_field_data, $arf_switch_field_image_path, $arf_font_awesome_loaded;

$arf_switch_field_class_name = array(ARF_SWITCH_SLUG => 'red');
$arf_switch_new_field_data = array(ARF_SWITCH_SLUG => addslashes(esc_html__('Switch', 'ARForms')));
$arf_switch_total_class = array();
$arf_switch_field_class = new arf_switch_field();

global $arf_switch_loaded;
$arf_switch_loaded = array();                                                                                                                                              
class arf_switch_field {

    function __construct() {

        add_action('arfafterbasicfieldlisting', array($this, 'arf_add_switch_field'), 11, 2);

        add_filter('arf_all_field_css_class_for_editor', array($this, 'arf_get_switch_field_class'), 11, 3);

        add_filter('arfavailablefieldsbasicoptions', array($this, 'add_availablefieldsbasicoptions'), 11, 3);

        add_action('arfdisplayaddedfields', array($this, 'add_switch_field_to_editor'), 12);

        /* arf_dev_flag convert from action to filter */

        add_filter('form_fields', array($this, 'add_switch_field_to_frontend'), 12, 12);

        add_filter('arf_save_more_field_from_out_side', array($this, 'arf_save_switch_field'), 11, 2);    // Before Create new filed

        add_filter('arf_new_field_array_filter_outside', array($this, 'arf_add_switch_field_in_array'),11,4);

        add_filter('arf_new_field_array_materialize_filter_outside', array($this, 'arf_add_switch_field_in_array_materialize'),11,4);

        add_filter('arf_installed_fields_outside',array($this,'arf_install_switch_field'),11);

        add_filter('arf_onchange_only_click_event_outside',array($this,'arf_switch_change_type_func'),11);

        add_filter('arf_positioned_field_options_icon',array($this,'arf_positioned_field_options_icon_for_switch'),11,2);

        add_filter('arf_default_value_array_field_type_from_itemmeta', array($this,'arf_default_value_array_field_type_switch'),11,2); 

        add_filter('arf_form_fields_outside',array($this,'arf_form_field_data_for_switch'),10,2);  
    }

    function arf_default_value_array_field_type_switch($field_types){
        array_push($field_types, ARF_SWITCH_SLUG);
        return $field_types;
    }

    function arf_form_field_data_for_switch($skinJson,$arfinputstyle_template){

        $switch_data = new stdClass();
        $switch_data->required =0;
        $switch_data->required_indicator ='*';
        $switch_data->name ='Switch';
        $switch_data->description ='';
        $switch_data->field_width ='';
        $switch_data->blank ='This field cannot be blank.';
        $switch_data->leftlable = 'No';
        $switch_data->leftvalue = 'No';
        $switch_data->rightlable = 'Yes';
        $switch_data->rightvalue = 'Yes';
        $switch_data->arf_tooltip ='';
        $switch_data->frm_arf_tooltip_field_indicator ='';
        $switch_data->tooltip_text ='';
        $switch_data->type ='arf_switch';
        $switch_data->default_value ='';

        $skinJson->field_data->arf_switch = $switch_data;
         return $skinJson;

    }

    function arf_positioned_field_options_icon_for_switch($positioned_icon,$field_icons){
        $positioned_icon[ARF_SWITCH_SLUG] = "{$field_icons['field_require_icon']}".str_replace('{arf_field_type}',ARF_SWITCH_SLUG,$field_icons['arf_field_duplicate_icon'])."{$field_icons['field_delete_icon']}".str_replace('{arf_field_type}',ARF_SWITCH_SLUG,$field_icons['field_option_icon'])."{$field_icons['arf_field_move_icon']}";
        return $positioned_icon;
    }

    function arf_switch_change_type_func($field_types){
        
        array_push($field_types,ARF_SWITCH_SLUG);
        return $field_types;
    }

    function arf_add_switch_field($id = '', $is_ref_form = '', $values = '') {

        global $arf_switch_field_class_name, $arf_switch_new_field_data, $arf_switch_field_image_path, $arf_switch_total_class;

        if (is_rtl()) {
            $floating_style = 'float:right;';
        } else {
            $floating_style = 'float:left;';
        }

        foreach ($arf_switch_new_field_data as $field_key => $field_type) {
            ?>
            <li class="arf_form_element_item frmbutton frm_t<?php echo $field_key ?>" id="<?php echo $field_key; ?>" data-field-id="<?php echo $id; ?>" data-type="<?php echo $field_key; ?>">
                <div class="arf_form_element_item_inner_container">
                            <span class="arf_form_element_item_icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -7 30 30"><g id="smiley"><path xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#4E5462" stroke-width="2" d="M22.194,1.617h-14c-3.866,0-7,3.134-7,7s3.134,7,7,7h14c3.866,0,7-3.134,7-7S26.06,1.617,22.194,1.617zM9.194,12.617c-2.209,0-4-1.791-4-4s1.791-4,4-4s4,1.791,4,4S11.403,12.617,9.194,12.617z"/></g></svg>
                            </span>
                    <label class="arf_form_element_item_text"><?php echo $field_type; ?></label>
                </div>
            </li>
            <?php
        }
    }

   function arf_get_switch_field_class($class) {
        global $arf_switch_field_class_name, $arf_switch_total_class;
        $as_class = array_merge($class, $arf_switch_field_class_name);
        $arf_switch_total_class = count($as_class);
        return $as_class;
    }

    function add_availablefieldsbasicoptions($basic_option) {

        $switch_filed_option = array(
            ARF_SWITCH_SLUG => array(
                'labelname' => 1,
                'fielddescription' => 2,
                'tooltipmsg' => 3,
                'requiredmsg' => 4,
                'leftlable' => 5,
                'leftvalue' => 6,
                'rightlable' => 7,
                'rightvalue' => 8,
            )
        );

        return array_merge($basic_option, $switch_filed_option);
    }
    function add_switch_field_to_editor($field) {
        global $arfajaxurl, $wpdb;
        $field_name = "item_meta[" . $field['id'] . "]";
        

        $field['field_options'] = json_decode($field['field_options'],true);

        if( json_last_error() != JSON_ERROR_NONE ){
            $field['field_options'] = maybe_unserialize($field['field_options']);
       }

  
        $field['field_options']['default_value'] = isset($field['field_options']['default_value']) ? $field['field_options']['default_value'] : '';
        $field['field_options']['leftvalue'] = isset($field['field_options']['leftvalue']) ? $field['field_options']['leftvalue'] : '' ;        
        $field['field_options']['rightvalue'] = isset($field['field_options']['rightvalue']) ? $field['field_options']['rightvalue'] : '' ;        
        
        $switch_default_val='';
        if(isset($field['field_options']['default_value']) && $field['field_options']['default_value'] == $field['field_options']['rightvalue'] ){
            $switch_default_val='checked';
        }
        if ($field['type'] == ARF_SWITCH_SLUG) {
            
        ?>
    
            <div class="arf_field_switch_container id='<?php echo 'arf_field_switch_container_'.$field['id']; ?>' ">
               
                <div class='arf_field_switch_input'>
                    <label class='arf_js_field_switch_label' for="<?php echo 'field_'.$field['id'].'-0' ; ?>">
                        <span id="<?php echo 'arf_js_field_switch_left_label'.$field['id']; ?>"><?php echo $field['field_options']['leftlable']; ?>&nbsp;</span>
                    </label>
                    <span class='arf_js_field_switch_wrapper'>
                        <input type='checkbox'  class='js-field_switch arf_hide_opacity arf_field_switch_input arf_switch_input' name='<?php echo'switch_item_meta_'.$field['id']; ?>' id='<?php echo 'switch_field_'.$field['id'].'-0'; ?>' value='<?php echo $field['field_options']['default_value']; ?>' data-leftval='<?php echo $field['field_options']['leftvalue']; ?>' data-rightval='<?php echo $field['field_options']['rightvalue']; ?>' <?php echo $switch_default_val; ?> >
                        <span class='arf_js_field_switch'></span>

                          <input type="hidden" name="<?php echo'item_meta['.$field['id'].']' ?>"  id='<?php echo 'field_'.$field['id'].'-0'; ?>' class="arf_hidden_field_switch arf_hide_opacity arf_switch_input" value='<?php echo $field['field_options']['default_value']; ?>'> 

                    </span>
                    <label class='arf_js_field_switch_label' for="<?php echo 'field_'.$field['id'].'-0' ; ?>">
                       <span id="<?php echo 'arf_js_field_switch_right_label'.$field['id']; ?>">&nbsp;<?php echo $field['field_options']['rightlable']; ?></span>
                    </label>
                </div>
                   
            </div>
            <?php
        }
    }


    function add_switch_field_to_frontend($return_string, $form, $field_name, $arf_data_uniq_id, $field, $field_tootip, $field_description,$res_data,$inputStyle,$arf_main_label,$get_onchage_func_data) {
        if ($field['type'] != 'arf_switch') {
            return $return_string;
        }
        global $style_settings, $arfsettings, $arfeditingentry, $arffield, $arfieldhelper, $wpdb, $MdlDb; 

        $form_data = new stdClass();
        $form_data->id = $form->id;
        $form_data->form_key = $form->form_key;
        $form_data->options = maybe_serialize($form->options);

        if( $res_data == '' ){
            $res_data = $wpdb->get_results($wpdb->prepare("SELECT id, type, field_options,conditional_logic FROM " . $MdlDb->fields . " WHERE form_id = %d ORDER BY id", $form->id));
        }

        $entry_id = $arfeditingentry;

        if ($field['type'] == ARF_SWITCH_SLUG) {
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
                        $field_tooltip_class = " arfhelptip ";
                    }
                    else {
                        $field_tootip_standard = $field_tootip;
                    }
                    
                }
             
                if( $inputStyle == 'material' ){
                    $return_string .= $arf_main_label;
                }
             
                       $arf_switch_id = 'field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '';

      
                        $material_input_cls = ($inputStyle == 'material') ? 'arf_switch' : '';
                        $arf_data_provided = ($inputStyle != 'material' ) ? 'data-provide="typeahead"' : '';

                       $field['leftvalue']=  isset($field['leftvalue']) ? $field['leftvalue'] : '' ;
                       $field['rightvalue'] = isset($field['rightvalue']) ? $field['rightvalue'] : '' ;

                       $switch_default_val='';
                        if (isset($field['value']) && $field['value']== $field['rightvalue']) {
                            $switch_default_val='checked';
                        }
                       $return_string .='<div class="arf_input_field_switch_wrapper controls '.$field_tooltip_class.'" '.$field_tootip_material.'>';
                       
                       if(isset($field['leftlable']) && $field['leftlable']!= ''){
                       $return_string .='<label class="arf_js_field_switch_label" for="switch_field_' . $field['field_key'] . '-' . $arf_data_uniq_id . '"><span>'.$field['leftlable'].'&nbsp;</span></label>';
                       }
                    
                        $return_string .='<span class="arf_js_field_switch_wrapper">';
                        $return_string .= '<input type="checkbox"   id="switch_field_' . $field['field_key'] . '-' . $arf_data_uniq_id . '" name="switch_' . $field_name . '" class="js-field_switch arf_hide_opacity arf_field_switch_input arf_switch_input"  value="'.$field['value'].'" data-leftval="'.$field['leftvalue'].'" data-rightval="'.$field['rightvalue'].'" '.$switch_default_val.' ';
                        if (isset($field['required']) and $field['required']) {
                        $return_string .=' data-validation-required-message="' . esc_attr($field['blank']) . '"';
                    }
                        $return_string .='/ >';

                        $return_string .='<span class="arf_js_field_switch"></span>';
                        $return_string .='<input type="hidden" id="field_' . $field['field_key'] . '-' . $arf_data_uniq_id . '" name="' . $field_name . '" value="'.$field['value'].'" class="arf_hidden_field_switch"';
                        $return_string .= $get_onchage_func_data;
                        $return_string .='/ >';

                        $return_string .='</span>';
                        if(isset($field['rightlable']) && $field['rightlable']!= ''){
                        $return_string .=' <label class="arf_js_field_switch_label" for="switch_field_' . $field['field_key'] . '-' . $arf_data_uniq_id . '"><span>&nbsp;'.$field['rightlable'].'</span></label>';
                        }
                       
                
                $return_string .=$field_tootip_standard;
                $return_string .=$field_description;
                 $return_string .='</div>';

        }

            return $return_string;
        }


    function arf_add_more_switch_field($field, $option) {
        global $armainhelper, $arfieldhelper, $arformcontroller, $arformhelper;
      
    }

    function arf_save_switch_field($field_array) {

        return array_merge($field_array, array('arf_switch_type', 'arf_switch_title'));
    }

    function arf_add_switch_field_in_array($fields,$field_icons,$json_data,$positioned_field_icons) {
        global $arfieldhelper;

        $field_opt_arr = $arfieldhelper->arf_getfields_basic_options_section();
        $field_order_arf_switch = isset($field_opt_arr['arf_switch']) ? $field_opt_arr['arf_switch'] : '';
 
        $field_data_array = $json_data;
        
        $field_data_obj_arf_switch = $field_data_array->field_data->arf_switch;

        

        $arf_field_move_option_icon = "<div class='arf_field_option_icon'><a class='arf_field_option_input'><svg id='moveing' height='20' width='21'><g>".ARF_CUSTOM_MOVING_ICON."</g></svg></a></div>";

        $fields['arf_switch'] = "<div  class='arf_inner_wrapper_sortable arfmainformfield edit_form_item arffieldbox ui-state-default 1  arf1columns single_column_wrapper' data-id='arf_editor_main_row_{arf_editor_index_row}'>
                                <div class='arf_multiiconbox'>
                                    <div class='arf_field_option_multicolumn' id='arf_multicolumn_wrapper'>
                                        <input type='hidden' name='multicolumn' />{$field_icons['multicolumn_one']} {$field_icons['multicolumn_two']} {$field_icons['multicolumn_three']} {$field_icons['multicolumn_four']} {$field_icons['multicolumn_five']} {$field_icons['multicolumn_six']}
                                    </div>{$field_icons['multicolumn_expand_icon']}
                                </div>

                                    <div class='sortable_inner_wrapper edit_field_type_arf_switch' inner_class='arf_1col' id='arfmainfieldid_{arf_field_id}'>
                                    <div id='arf_field_{arf_field_id}' class='arfformfield control-group arfmainformfield top_container  arfformfield  arf_field_{arf_field_id}'>
                                    <div class='fieldname-row' style='display : block;'>
                                    <div class='fieldname'>
                                        <label class='arf_main_label' id='field_{arf_field_id}'>
                                            <span class='arfeditorfieldopt_label arf_edit_in_place'>
                                                <input type='text' class='arf_edit_in_place_input inplace_field' data-ajax='false' data-field-opt-change='true' data-field-opt-key='name' value='Switch' data-field-id='{arf_field_id}' />
                                            </span>
                                            <span id='require_field_{arf_field_id}'>
                                                <a href='javascript:arfmakerequiredfieldfunction({arf_field_id},0,1)' class='arfaction_icon arfhelptip arffieldrequiredicon alignleft arfcheckrequiredfield0' id='req_field_{arf_field_id}' title='Click to mark as not compulsory field'>
                                                </a>
                                            </span>
                                        </label>
                                    </div>
                                    </div>
                                    <div class='arf_fieldiconbox'>".$positioned_field_icons[ARF_SWITCH_SLUG]."</div>
                                    <div class='controls'>

                                    <div class='arf_input_field_switch_container' id='arf_input_field_switch_container_{arf_field_id}'>
                                        <div class='arf_input_switch'>
                                            <label class='arf_js_field_switch_label' for='field_{arf_field_id}-0'>
                                                <span id='arf_js_field_switch_left_label{arf_field_id}'>No&nbsp;</span>
                                            </label>
                                            <span class='arf_js_field_switch_wrapper'>
                                                <input type='checkbox' class='js-field_switch arf_hide_opacity arf_field_switch_input arf_switch_input' name='switch_item_meta_{arf_field_id}' id='switch_field_{arf_field_id}-0'  value='No' data-leftval='No' data-rightval='Yes'>
                                                <span class='arf_js_field_switch'></span>
                                                <input type='hidden' name='item_meta[{arf_field_id}]' id='field_{arf_field_id}-0'  value='No' class='arf_hidden_field_switch'>
                                            </span>
                                            <label class='arf_js_field_switch_label' for ='field_{arf_field_id}-0'>
                                                <span id='arf_js_field_switch_right_label{arf_field_id}'>&nbsp;Yes</span>
                                            </label>
                                        </div>

                                    </div>
                                        <div class='arf_field_description' id='field_description_{arf_field_id}'></div>
                                        <div class='help-block'></div>
                                        </div>

                                            <input type='hidden' name='arf_field_data_{arf_field_id}' id='arf_field_data_{arf_field_id}' value='" . htmlspecialchars(json_encode($field_data_obj_arf_switch)) . "' data-field_options='" . json_encode($field_order_arf_switch) . "' />
                                            <div class='arf_field_option_model arf_field_option_model_cloned'>
                                            <div class='arf_field_option_model_header'>".esc_html__('Field Options','ARForms')."</div>
                                            <div class='arf_field_option_model_container'>
                                            <div class='arf_field_option_content_row'></div>
                                            </div>
                                            <div class='arf_field_option_model_footer'>
                                                <button type='button' class='arf_field_option_close_button' onClick='arf_close_field_option_popup({arf_field_id});'>".esc_html__('Cancel','ARForms')."</button>
                                                <button type='button' class='arf_field_option_submit_button' data-field_id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>";

        return $fields;
    }

    function arf_add_switch_field_in_array_materialize($fields,$field_icons,$json_data,$positioned_field_icons) {
        global $arfieldhelper;

        $field_opt_arr = $arfieldhelper->arf_getfields_basic_options_section();

        $field_order_arf_switch = isset($field_opt_arr['arf_switch']) ? $field_opt_arr['arf_switch'] : '';

        $wp_upload_dir = wp_upload_dir();

        $upload_dir = $wp_upload_dir['basedir'] . '/arforms';
        $upload_url = $wp_upload_dir['baseurl'] . '/arforms';

        $field_data_array = $json_data;
        $field_data_obj_arf_switch = $field_data_array->field_data->arf_switch;

        $fields['arf_switch'] = "<div  class='arf_inner_wrapper_sortable arfmainformfield edit_form_item arffieldbox ui-state-default 1  arf1columns single_column_wrapper' data-id='arf_editor_main_row_{arf_editor_index_row}'>
                                    <div class='arf_multiiconbox'><div class='arf_field_option_multicolumn' id='arf_multicolumn_wrapper'><input type='hidden' name='multicolumn' />
                                            {$field_icons['multicolumn_one']} {$field_icons['multicolumn_two']} {$field_icons['multicolumn_three']} {$field_icons['multicolumn_four']} {$field_icons['multicolumn_five']} {$field_icons['multicolumn_six']}</div>{$field_icons['multicolumn_expand_icon']}
                                    </div>
                                    <div class='sortable_inner_wrapper edit_field_type_arf_switch' inner_class='arf_1col' id='arfmainfieldid_{arf_field_id}'>
                                        <div id='arf_field_{arf_field_id}' class='arfformfield control-group arfmainformfield top_container  arfformfield  arf_field_{arf_field_id}'>
                                            <div class='fieldname-row' style='display : block;'>
                                                <div class='fieldname'>
                                                <label class='arf_main_label' id='field_{arf_field_id}'>
                                                    <span class='arfeditorfieldopt_label arf_edit_in_place'>
                                                        <input type='text' class='arf_edit_in_place_input inplace_field' data-ajax='false' data-field-opt-change='true' data-field-opt-key='name' value='Switch' data-field-id='{arf_field_id}' />
                                                    </span>
                                                    <span id='require_field_{arf_field_id}'>
                                                        <a href='javascript:arfmakerequiredfieldfunction({arf_field_id},0,1)' class='arfaction_icon arfhelptip arffieldrequiredicon alignleft arfcheckrequiredfield0' id='req_field_{arf_field_id}' title='Click to mark as not compulsory field'>
                                                        </a>
                                                    </span>
                                                </label>
                                                </div>
                                            </div>
                                            <div class='arf_fieldiconbox'>".$positioned_field_icons[ARF_SWITCH_SLUG]."</div>
                                            <div class='controls'>
                                            <div class='arf_input_field_switch_container' id='arf_input_field_switch_container_{arf_field_id}'>
                                                <div class='arf_input_switch'>
                                                    <label class='arf_js_field_switch_label' for='field_{arf_field_id}-0'>
                                                        <span id='arf_js_field_switch_left_label{arf_field_id}'>No&nbsp;</span>
                                                    </label>
                                                    <span class='arf_js_field_switch_wrapper'>
                                                        <input type='checkbox' class='js-field_switch arf_hide_opacity arf_field_switch_input arf_switch_input' name='switch_item_meta_{arf_field_id}' id='switch_field_{arf_field_id}-0'  value='No' default_value='1' data-leftval='No' data-rightval='Yes'>
                                                        <span class='arf_js_field_switch'></span>
                                                        <input type='hidden' name='item_meta[{arf_field_id}]' id='field_{arf_field_id}-0'  value='No' class='arf_hidden_field_switch'>
                                                    </span>
                                                    <label class='arf_js_field_switch_label' for='field_{arf_field_id}-0'>
                                                        <span id='arf_js_field_switch_right_label{arf_field_id}'>&nbsp;Yes</span>
                                                    </label>
                                                </div>

                                                </div>
                                                <div class='arf_field_description' id='field_description_{arf_field_id}'></div>
                                                <div class='help-block'></div>
                                                </div>
                                                <input type='hidden' name='arf_field_data_{arf_field_id}' id='arf_field_data_{arf_field_id}' value='" . htmlspecialchars(json_encode($field_data_obj_arf_switch)) . "' data-field_options='" . json_encode($field_order_arf_switch) . "' />
                                                <div class='arf_field_option_model arf_field_option_model_cloned'>
                                                <div class='arf_field_option_model_header'>".esc_html__('Field Options','ARForms')."</div>
                                                <div class='arf_field_option_model_container'>
                                                <div class='arf_field_option_content_row'></div>
                                                </div>
                                                <div class='arf_field_option_model_footer'>
                                                    <button type='button' class='arf_field_option_close_button' onClick='arf_close_field_option_popup({arf_field_id});'>".esc_html__('Cancel','ARForms')."</button>
                                                    <button type='button' class='arf_field_option_submit_button' data-field_id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>";
        
        return $fields;
    }

    function arf_install_switch_field($fields){
        array_push($fields,'ARF_SWITCH_SLUG');
        return $fields;
    }
}
?>