<?php
global $ARF_condition_on_subscription;

$ARF_condition_on_subscription = new ARF_condition_on_subscription();

class ARF_condition_on_subscription {

    function __construct() {
        add_action('arf_condition_on_subscription_html', array($this, 'arf_condition_on_subscription_html_fun'), 10, 3);

        add_filter('arfformoptionsbeforeupdateform', array($this, 'arf_condition_on_subscription_save_opt'), 10, 2);

        add_action('wp_ajax_arf_add_condition_on_subscription_new_rule', array($this, 'arf_add_condition_on_subscription_new_rule'));

        add_filter('arf_check_condition_on_subscription', array($this, 'arf_check_condition_on_subscription_fun'), 1000, 2);

        add_action('arf_afterduplicate_update_fields', array($this, 'arf_afterduplicate_update_fields_subscription'), 10, 3);
        
        add_filter('arf_import_update_field_outside', array($this, 'arf_importtime_update_condition_on_subscription_field_id'), 10, 3);
    }

    function arf_condition_on_subscription_html_fun($id, $is_ref_form, $values) {

        if (isset($values['arf_condition_on_subscription_rules']) && !empty($values['arf_condition_on_subscription_rules'])) {
            $arf_condition_on_subscription_rules = $values['arf_condition_on_subscription_rules'];
        } else {
            $arf_condition_on_subscription_rules[1]['id'] = '';
            $arf_condition_on_subscription_rules[1]['field_id'] = '';
            $arf_condition_on_subscription_rules[1]['field_type'] = '';
            $arf_condition_on_subscription_rules[1]['operator'] = '';
            $arf_condition_on_subscription_rules[1]['value'] = '';
        }

        global $arfieldhelper;
        ?> 


        <?php $values['conditional_subscription'] = isset($values['conditional_subscription']) ? $values['conditional_subscription'] : ''; ?>
        <div class="arf_popup_container_autoresponder_values arf_height_auto" style="margin-top: 10px;">
            <div class="arf_custom_checkbox_div" onclick="arf_condition_on_subscription_enable_disable();" >
                <div class="arf_custom_checkbox_wrapper">
                    <input type="checkbox" <?php checked($values['conditional_subscription'], 1); ?> value="1" id="conditional_subscription" name="options[conditional_subscription]" class="chkstanard">
                    <svg width="18px" height="18px">
                    <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                    <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                    </svg>
                </div>
                <span class="conditional_subscriptionspanlabel">
                    <label for="conditional_subscription" class="arffont16" style="font-size: 16px !important;"><?php echo esc_html__('Set Condition on Subscription', 'ARForms'); ?></label>
                </span>
            </div>

        </div>

        <div class="arf_popup_container_autoresponder_values arf_height_auto">
            <div class="arftablerow">
                <div class="arfcolmnleft">

                    <div class="arftablerow">
                        <?php 
                        $marginval = '';
                        if(is_rtl()){
                            $marginval = 'margin-right:28px;';
                        } else {
                            $marginval = 'margin-left:28px;';
                        } ?>
                        <div class="arfcolumnright" id="conditional_subscription_main" <?php echo (isset($values['conditional_subscription']) && $values['conditional_subscription'] == '1') ? 'style="'.$marginval.'"' : 'style="display:none;'.$marginval.'"'; ?> >
                            <span class="arf_condition_on_subscription_if_div"><?php echo addslashes(esc_html__('If', 'ARForms')) ?></span>
                            <?php foreach ($arf_condition_on_subscription_rules as $rule_i => $condition_value) { ?>
                                <div class="arf_condition_on_subscription_logic_div" id="arf_rule_condition_on_subscription_<?php echo $rule_i; ?>">
                                    <input type="hidden" value="<?php echo $rule_i; ?>" class="rule_array_condition_on_subscription" name="options[arf_condition_on_subscription_rules][<?php echo $rule_i; ?>[id]">
                                    <div style="<?php echo (is_rtl()) ? 'float: right;' : 'float: left;';?> width: 100%">
                                        <span id="select_ar_condition_on_subscription_field">
                                            <div class="sltstandard" style="width: 100%;">
                                                <?php
                                                $selectbox_field_options = "";
                                                $selectbox_field_value_label = "";
                                                $user_responder_email = "";
                                                if (!empty($values['fields'])) {
                                                    foreach ($values['fields'] as $val_key => $fo) {
                                                        //if (in_array($fo['type'], array('email', 'text', 'hidden', 'radio', 'select', 'html'))) {
                                                        if ($fo['type'] != 'divider' && $fo['type'] != 'break' && $fo['type'] != 'captcha' && $fo['type'] != 'html' && $fo['type'] != 'confirm_email') {
                                                            if (($fo["id"] == $condition_value['field_id'])) {
                                                                $selectbox_field_value_label = $fo["name"];
                                                                $user_responder_email = $values['ar_email_to'];
                                                            }

                                                            $current_field_id = $fo["id"];
                                                            if($current_field_id !="" && $arfieldhelper->arf_execute_function($fo["name"],'strip_tags')=="" ){
                                                                $selectbox_field_options .= '<li class="arf_selectbox_option" data-value="' . $current_field_id . '" data-type="'.$fo['type'].'" data-label="[Field Id :'.$current_field_id.']">[Field Id :'.$current_field_id.']</li>';

                                                            }else{
                                                                $selectbox_field_options .= '<li class="arf_selectbox_option" data-value="' . $current_field_id . '" data-type="'.$fo['type'].'" data-label="' . $arfieldhelper->arf_execute_function($fo["name"],'strip_tags') . '">' . $arfieldhelper->arf_execute_function($fo["name"],'strip_tags') . '</li>';    
                                                            }
                                                            
                                                        }
                                                    }
                                                }
                                                ?>
                                                <input id="arf_subscription_condition_field_<?php echo $rule_i; ?>" name="options[arf_condition_on_subscription_rules][<?php echo $rule_i; ?>[field_id]" type="hidden" value="<?php echo $condition_value['field_id']; ?>" />

                                                <input id="arf_subscription_condition_field_type_<?php echo $rule_i; ?>" name="options[arf_condition_on_subscription_rules][<?php echo $rule_i; ?>][field_type]" type="hidden" value="<?php echo $condition_value['field_type']; ?>" />

                                                <dl class="arf_selectbox" data-name="arf_subscription_condition_field_<?php echo $rule_i; ?>" data-id="arf_subscription_condition_field_<?php echo $rule_i; ?>" style="width:100%">
                                                    <dt class="arf_subscription_condition_field_<?php echo $rule_i; ?>_dt"><span><?php
                                                        if ($selectbox_field_value_label != "") {
                                                            echo $arfieldhelper->arf_execute_function($selectbox_field_value_label,'strip_tags');
                                                        }else if($condition_value['field_id'] !="" &&  $selectbox_field_value_label==""){
                                                            echo '[Field Id : '.$condition_value['field_id'].']';
                                                        } else {
                                                            echo addslashes(esc_html__('Select Field', 'ARForms'));
                                                        }
                                                        ?></span>
                                                    <input value="<?php if ($user_responder_email != "") { echo $user_responder_email; } ?>" style="display:none;width:148px;" class="arf_autocomplete" type="text" autocomplete="off">
                                                    <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                                    <dd>
                                                        <ul class="arf_name_field_dropdown arf_conditional_field_dropdown arf_subscription_condition_field_dropdown" style="display: none;" data-id="arf_subscription_condition_field_<?php echo $rule_i; ?>">
                                                            <li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?></li>
                                                            <?php echo $selectbox_field_options; ?>
                                                        </ul>
                                                    </dd>
                                                </dl>
                                            </div>
                                        </span>
                                        <span  class="ar_conditional_redirect_operator_is"><?php echo addslashes(esc_html__('is', 'ARForms')) ?></span>

                                        <span id="select_ar_conditional_redirect_operator">
                                            <div class="sltstandard" style="width:100%">
                                                <?php echo $arfieldhelper->arf_cl_rule_menu_for_conditional_subscription('arf_subscription_condition_operator_' . $rule_i, 'arf_subscription_condition_operator_' . $rule_i, $condition_value['operator'], $rule_i); ?>
                                            </div>
                                        </span>

                                        <span id="select_ar_conditional_redirect_value">
                                            <input style="width:100%;" type="text" class="txtstandardnew arf_large_input_box" value="<?php if(isset($condition_value['value'])){echo $condition_value['value'];} ?>" id="arf_subscription_condition_field_value_<?php echo $rule_i; ?>" onkeyup="this.setAttribute('value',this.value)" name="options[arf_condition_on_subscription_rules][<?php echo $rule_i; ?>][value]">
                                        </span>

                                        <span class="arf_condition_on_subscription_bulk_add_remove">
                                            <span class="bulk_add_mail" onclick="arf_condition_on_subscription_add();"><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.134,20.362c-5.521,0-9.996-4.476-9.996-9.996 c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.13,15.887,16.654,20.362,11.134,20.362z M11.133,2.314c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052 C19.185,5.919,15.579,2.314,11.133,2.314z M12.146,14.341h-2v-3h-3v-2h3V6.372h2v2.969h3v2h-3V14.341z"/></g></svg></span>
                                            <span class="bulk_remove_mail" onclick="arf_condition_on_subscription_delete_rule(<?php echo $rule_i; ?>)" style="display:<?php echo (count($arf_condition_on_subscription_rules) > 1) ? 'inline-block' : 'none'; ?>;"><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.12,20.389c-5.521,0-9.996-4.476-9.996-9.996c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.116,15.913,16.64,20.389,11.12,20.389z M11.119,2.341 c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052C19.17,5.945,15.565,2.341,11.119,2.341 z M12.131,11.367h3v-2h-3h-2h-3v2h3H12.131z"/></g></svg></span>
                                        </span>
                                    </div>
                                </div>
                            <?php } ?>
                            <div style="clear:both"></div>
                        </div>
                    </div>
                </div>
            </div>   
        </div>

        <div style="clear: both; height: 20px;">&nbsp;</div>

        <div class="arf_condition_on_subscription_field_dropdown arf_name_field_dropdown" id="arf_condition_on_subscription_dropdown_html" style="display: none;">
            <?php
            $condition_on_subscription_options = '';
            if (!empty($values['fields'])) {
                foreach ($values['fields'] as $val_key => $fo) {
                    if ($fo['type'] != 'divider' && $fo['type'] != 'break' && $fo['type'] != 'captcha' && $fo['type'] != 'html') {
                        $current_field_id = $fo["id"];
                        if($current_field_id !="" && $arfieldhelper->arf_execute_function($fo["name"],'strip_tags') ==""){
                            $condition_on_subscription_options .= '<li class="arf_selectbox_option" data-type="'.$fo['type'].'" data-value="' . $current_field_id . '" data-label="[Field Id :'.$current_field_id.']">[Field Id :'.$current_field_id.']</li>';
                        }else{
                            $condition_on_subscription_options .= '<li class="arf_selectbox_option" data-type="'.$fo['type'].'" data-value="' . $current_field_id . '" data-label="' . $arfieldhelper->arf_execute_function($fo["name"],'strip_tags') . '">' . $arfieldhelper->arf_execute_function($fo["name"],'strip_tags') . '</li>';    
                        }
                        
                    }
                }
            }
            ?>
            <li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?></li>
            <?php echo $condition_on_subscription_options; ?>
        </div>

        <script type="text/javascript">
            jQuery(document).ready(function () {
                arf_condition_on_subscription_enable_disable();
            });
            function arf_condition_on_subscription_enable_disable() {
                if (jQuery("#conditional_subscription").is(':checked'))
                {

                    jQuery("#conditional_subscription_main").show();

                } else {

                    jQuery("#conditional_subscription_main").hide();
                }
            }
        </script>   
        <?php
    }

    function arf_condition_on_subscription_save_opt($options, $values) {

        $options['conditional_subscription'] = isset($values['options']['conditional_subscription']) ? $values['options']['conditional_subscription'] : "";

        $options['arf_condition_on_subscription_rules'] = isset($values['options']['arf_condition_on_subscription_rules']) ? $values['options']['arf_condition_on_subscription_rules'] : "";

        return $options;
    }

    function arf_add_condition_on_subscription_new_rule() {
        global $arfieldhelper;
        $rule_i = $_POST['next_rule_id'] ? $_POST['next_rule_id'] : 1;
        ?>
        <div class="arf_condition_on_subscription_logic_div" style="float: left; width: 100%; margin: 5px 0px;" id="arf_rule_condition_on_subscription_<?php echo $rule_i; ?>">
            <input type="hidden" value="<?php echo $rule_i; ?>" name="rule_array_condition_on_subscription[]">
            <div style="float: left; width: 100%">
                <span style="float: left; font-size: 14px; line-height: 30px; margin-right: 7px;"><?php echo addslashes(esc_html__('If', 'ARForms')) ?></span>
                <span id="select_ar_condition_on_subscription_field" style="width:170px; float: left;">
                    <div class="sltstandard">

                        <input id="arf_subscription_condition_field_<?php echo $rule_i; ?>" name="arf_subscription_condition_field_<?php echo $rule_i; ?>" value="" type="hidden">

                        <dl class="arf_selectbox" data-name="arf_subscription_condition_field_<?php echo $rule_i; ?>" data-id="arf_subscription_condition_field_<?php echo $rule_i; ?>" style="width:160px;">
                            <dt class="arf_subscription_condition_field_<?php echo $rule_i; ?>_dt"><span><?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?></span>
                            <input value="" style="display:none;width:148px;" class="arf_autocomplete" type="text">
                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                            <dd>
                                <ul class="arf_name_field_dropdown arf_conditional_field_dropdown arf_subscription_condition_field_dropdown arf_subscription_condition_field_dropdown_ajax" style="display: none;" data-id="arf_subscription_condition_field_<?php echo $rule_i; ?>">
                                    <li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?></li>
                                </ul>
                            </dd>
                        </dl>
                    </div>
                </span>
                <span style="float: left; font-size: 14px; line-height: 30px; margin-right: 7px;"><?php echo addslashes(esc_html__('is', 'ARForms')) ?></span>

                <span id="select_ar_conditional_redirect_operator" style="width:140px; float: left;">
                    <div class="sltstandard" style="width:140px;">
                        <?php echo $arfieldhelper->arf_cl_rule_menu('arf_subscription_condition_operator_' . $rule_i, 'arf_subscription_condition_operator_' . $rule_i, ''); ?>
                    </div>
                </span>

                <span id="select_ar_conditional_redirect_value" style="margin-left:7px;float: left;margin-right: 10px;">
                    <input style="width:140px;" type="text" class="txtstandardnew arf_large_input_box" id="arf_subscription_condition_field_value_<?php echo $rule_i; ?>" name="arf_subscription_condition_field_value_<?php echo $rule_i; ?>">
                </span>

                <span class="arf_condition_on_subscription_bulk_add_remove" style="float: left; margin-top: 15px;margin-left: -6px;">
                    <span class="bulk_add_mail" onclick="arf_condition_on_subscription_add();"><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.134,20.362c-5.521,0-9.996-4.476-9.996-9.996c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.13,15.887,16.654,20.362,11.134,20.362z M11.133,2.314c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052C19.185,5.919,15.579,2.314,11.133,2.314zM12.146,14.341h-2v-3h-3v-2h3V6.372h2v2.969h3v2h-3V14.341z"/></g></svg></span>
                    <span class="bulk_remove_mail" onclick="arf_condition_on_subscription_delete_rule(<?php echo $rule_i; ?>)" style=""><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.12,20.389c-5.521,0-9.996-4.476-9.996-9.996c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.116,15.913,16.64,20.389,11.12,20.389zM11.119,2.341c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052C19.17,5.945,15.565,2.341,11.119,2.341z M12.131,11.367h3v-2h-3h-2h-3v2h3H12.131z"/></g></svg></span>
                </span>
            </div>
        </div>
        <?php
        die();
    }

    function arf_check_condition_on_subscription_fun($form, $entry_id) {

        global $wpdb, $arfrecordmeta, $ARF_condition_on_subscription, $MdlDb, $arffield, $armainhelper;
        $form_data = maybe_unserialize($form);

        if ($entry_id == '') {
            return false;
        }
        $entry_ids = array($entry_id);
        $values = $arfrecordmeta->getAll("it.field_id != 0 and it.entry_id in (" . implode(',', $entry_ids) . ")", " ORDER BY fi.id");

        if (isset($form_data['conditional_subscription']) && $form_data['conditional_subscription'] == 1 && !empty($values)) {
            $arf_condition_on_subscription_rules = isset($form_data['arf_condition_on_subscription_rules']) ? $form_data['arf_condition_on_subscription_rules'] : array();

            if (!empty($arf_condition_on_subscription_rules)) {

                foreach ($arf_condition_on_subscription_rules as $key => $subcription_rules) {
                    if (count($values) > 0) {
                        foreach ($values as $value) {
                            if ($subcription_rules['field_id'] == $value->field_id) {
                                $subscription_value1 = $value->entry_value;
                                break;
                            }
                        }
                    }

                    $condition_on_subscription_field_type = $subcription_rules['field_type'];

                    $condition_on_subscription_value1 = isset($subscription_value1) ? $subscription_value1 : '';

                    $condition_on_subscription_value1 = trim(strtolower($condition_on_subscription_value1));

                    $condition_on_subscription_value2 = trim(strtolower($subcription_rules['value']));

                    $condition_on_subscription_operator = $subcription_rules['operator'];

                    if ($ARF_condition_on_subscription->arf_condition_on_subscription_calculate_rule($condition_on_subscription_value1, $condition_on_subscription_value2, $condition_on_subscription_operator, $condition_on_subscription_field_type)) {
                        
                    } else {
                        return false;
                        break;
                    }
                }
            }
        }

        return true;
    }

    function arf_condition_on_subscription_calculate_rule($value1, $value2, $operator, $field_type) {
        global $arfieldhelper;
        if ($field_type == 'checkbox') {
            $chk = 0;
            $default_value = maybe_unserialize($value1);

            if ($default_value && is_array($default_value)) {
                foreach ($default_value as $chk_value) {
                    $value1 = trim(strtolower($chk_value));
                    if ($arfieldhelper->ar_match_rule($value1, $value2, $operator))
                        $chk++;
                }
            }
            else if ($arfieldhelper->ar_match_rule($value1, $value2, $operator)) {
                $chk++;
            }

            if ($chk > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return $arfieldhelper->ar_match_rule($value1, $value2, $operator);
        }

        return false;
    }

    function arf_afterduplicate_update_fields_subscription($options, $arf_fields, $form_id) {

        global $wpdb, $MdlDb;

        foreach ($arf_fields as $original_id => $field_new_id) {

            if (isset($options['arf_condition_on_subscription_rules']) && count($options['arf_condition_on_subscription_rules']) > 0) {

                $arf_condition_on_subscription_rules = array();
                foreach ($options['arf_condition_on_subscription_rules'] as $new_rule) {

                    if ($new_rule['field_id'] == $original_id) {
                        $new_rule['field_id'] = $field_new_id;
                    }
                    $arf_condition_on_subscription_rules[$new_rule['id']] = array(
                        'id' => $new_rule['id'],
                        'field_id' => $new_rule['field_id'],
                        'field_type' => $new_rule['field_type'],
                        'operator' => $new_rule['operator'],
                        'value' => $new_rule['value'],
                    );
                }

                if (isset($arf_condition_on_subscription_rules) && !empty($arf_condition_on_subscription_rules)) {
                    $options['arf_condition_on_subscription_rules'] = $arf_condition_on_subscription_rules;
                }
                $options_new = maybe_serialize($options);
                $wpdb->update($MdlDb->forms, array('options' => $options_new), array('id' => $form_id));
            }
        }
    }

    function arf_importtime_update_condition_on_subscription_field_id($option_arr_new, $old_new_field_id, $form_id) {
        global $wpdb,$MdlDb;
        if (isset($option_arr_new['arf_condition_on_subscription_rules']) && count($option_arr_new['arf_condition_on_subscription_rules']) > 0 and is_array($option_arr_new['arf_condition_on_subscription_rules'])) {
            $arf_condition_on_subscription_rules = array();
            if (!empty($option_arr_new['arf_condition_on_subscription_rules'])) {
                foreach ($option_arr_new['arf_condition_on_subscription_rules'] as $new_rule) {
                    
                    $cl_subscription_id = $old_new_field_id[$new_rule['field_id']];
                    $cl_subscription_type = $new_rule['field_type'];

                    $cl_sub_db_type = $wpdb->get_row($wpdb->prepare("SELECT type FROM `".$MdlDb->fields."` WHERE id = %d",$cl_subscription_id) );

                    if( isset($cl_sub_db_type) && isset($cl_sub_db_type->type) && $cl_sub_db_type->type != $cl_subscription_type){
                        $new_rule['field_type'] = $cl_sub_db_type->type;
                    }

                    $arf_condition_on_subscription_rules[$new_rule['id']] = array(
                        'id' => $new_rule['id'],
                        'field_id' => isset($old_new_field_id[$new_rule['field_id']]) ? $old_new_field_id[$new_rule['field_id']] : '',
                        'field_type' => $new_rule['field_type'],
                        'operator' => $new_rule['operator'],
                        'value' => $new_rule['value'],
                    );
                }
            }
            $option_arr_new['arf_condition_on_subscription_rules'] = $arf_condition_on_subscription_rules;
        }
        return $option_arr_new;
    }

}
?>