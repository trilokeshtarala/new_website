<?php
global $arf_conditional_redirect_to_url;

$arf_conditional_redirect_to_url = new ARF_conditional_redirect_to_url();

class ARF_conditional_redirect_to_url {

    function __construct() {
        add_action('arf_afterduplicate_update_fields', array($this, 'arf_afterduplicate_update_fields'), 10, 3);

        add_action('arf_form_submit_after_redirect_to_url', array($this, 'arf_form_submit_after_redirect_to_url_html'), 10, 2);

        add_filter('arfformoptionsbeforeupdateform', array($this, 'arf_conditional_redirect_save_opt'), 10, 2);

        add_action('wp_ajax_arf_add_conditional_redirect_new_rule', array($this, 'arf_add_conditional_redirect_new_rule'));

        add_filter('arfcontent', array($this, 'arf_set_conditional_redirect_url'), 11, 3);

        add_filter('arf_import_update_field_outside', array($this, 'arf_importtime_update_conditional_redirect_field_id'), 10, 3);
    }

    function arf_afterduplicate_update_fields($options, $arf_fields, $form_id) {

        global $wpdb, $MdlDb;

        foreach ($arf_fields as $original_id => $field_new_id) {
            $options['arf_conditional_redirect_rules'] = isset($options['arf_conditional_redirect_rules']) ? $options['arf_conditional_redirect_rules'] : array();
            if (count($options['arf_conditional_redirect_rules']) > 0) {

                $arf_conditional_redirect_rules = array();
                foreach ($options['arf_conditional_redirect_rules'] as $new_rule) {

                    if ($new_rule['field_id'] == $original_id) {
                        $new_rule['field_id'] = $field_new_id;
                    }
                    $arf_conditional_redirect_rules[$new_rule['id']] = array(
                        'id' => $new_rule['id'],
                        'field_id' => $new_rule['field_id'],
                        'field_type' => $new_rule['field_type'],
                        'operator' => $new_rule['operator'],
                        'value' => $new_rule['value'],
                        'redirect_url' => $new_rule['redirect_url'],
                    );
                }

                if (isset($arf_conditional_redirect_rules) && !empty($arf_conditional_redirect_rules)) {
                    $options['arf_conditional_redirect_rules'] = $arf_conditional_redirect_rules;
                }
                $options_new = maybe_serialize($options);
                $wpdb->update($MdlDb->forms, array('options' => $options_new), array('id' => arf_sanitize_value($form_id)));
            }
        }
    }

    function arf_form_submit_after_redirect_to_url_html($id, $values) {
        global $arfieldhelper;
        $arf_conditional_redirect_rules = array();
        if (isset($values['arf_conditional_redirect_rules']) && !empty($values['arf_conditional_redirect_rules'])) {
            $arf_conditional_redirect_rules = $values['arf_conditional_redirect_rules'];
        } else {
            $arf_conditional_redirect_rules[1]['id'] = '';
            $arf_conditional_redirect_rules[1]['field_id'] = '';
            $arf_conditional_redirect_rules[1]['operator'] = '';
            $arf_conditional_redirect_rules[1]['value'] = '';
            $arf_conditional_redirect_rules[1]['redirect_url'] = '';
            $arf_conditional_redirect_rules[1]['field_type'] = '';
        }
        
        global $arfieldhelper;
        ?> 
        <div style="clear: both; height: 10px;">&nbsp;</div>

        <div class="arf_or_option" style="width:20%;"><?php echo addslashes(esc_html__('Or', 'ARForms')) ?></div>

        <?php $values['arf_conditional_redirect_enable'] = isset($values['arf_conditional_redirect_enable']) ? $values['arf_conditional_redirect_enable'] : ''; ?>
        <div class="arfcolumnleft arf_custom_margin_redirect arfsetcondtionalredirect">
            <div class="arf_custom_checkbox_div">
                <div class="arf_custom_checkbox_wrapper" onclick="arf_conditional_redirect_enable_disable();">
                    <input type="checkbox"  <?php checked($values['arf_conditional_redirect_enable'], 1); ?> value="1" id="arf_conditional_redirect_enable" name="options[arf_conditional_redirect_enable]" class="chkstanard">
                    <svg width="18px" height="18px">
                    <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                    <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                    </svg>
                </div>
                <span>
                    <label for="arf_conditional_redirect_enable" style="margin-left: 4px;"><?php echo esc_html__('Set conditional redirect URL', 'ARForms') ?> (<span class="howto"><?php echo esc_html__('Please insert url with http:// or https://.', 'ARForms'); ?></span>)</label>
                </span>
            </div>
        </div>

        <div class="arfcolumnright">
            <div class="arftablerow">
                <div class="arfcolmnleft">
                    <div class="arftablerow" style="margin-left:0;">

                        <div class="arfcolumnright" id="arf_rule_conditional_redirect_mian" <?php echo (isset($values['arf_conditional_redirect_enable']) && $values['arf_conditional_redirect_enable'] == '1') ? 'style="margin-left: 23px;width:100%;"' : 'style="display:none;"'; ?> >
                            <span class="arf_rule_conditional_redirect_if"><?php echo addslashes(esc_html__('If', 'ARForms')) ?></span>
                            <?php foreach ($arf_conditional_redirect_rules as $rule_i => $redirect_value) { ?>
                                <div class="arf_conditional_redirect_logic_div" id="arf_rule_conditional_redirect_<?php echo $rule_i; ?>">
                                    <input type="hidden" value="<?php echo $rule_i; ?>" class="rule_array_conditional_redirect" name="options[arf_conditional_redirect_rules][<?php echo $rule_i; ?>][id]">
                                    
                                    <div class="arf_conditional_redirect_div_content">
                                        <span id="select_ar_conditional_redirect_filed">
                                            <div class="sltstandard" style="width: 100%;">
                                                <?php
                                                $selectbox_field_options = "";
                                                $selectbox_field_value_label = "";
                                                $user_responder_email = "";
                                                if (!empty($values['fields'])) {
                                                    foreach ($values['fields'] as $val_key => $fo) {
                                                        if ($fo['type'] != 'confirm_email' && $fo['type'] != 'divider' && $fo['type'] != 'break' && $fo['type'] != 'captcha' && $fo['type'] != 'html' && $fo['type'] != 'imagecontrol' && $fo['type'] != 'arf_signature' && $fo['type'] != 'arf_product' ) {
                                                            if (($fo["id"] == $redirect_value['field_id'])) {
                                                                $selectbox_field_value_label = $fo["name"];
                                                                $user_responder_email = $values['ar_email_to'];
                                                            }

                                                            $current_field_id = $fo["id"];
                                                            if($current_field_id !="" && $arfieldhelper->arf_execute_function($fo["name"],'strip_tags') ==""){
                                                                $selectbox_field_options .= '<li class="arf_selectbox_option" data-value="' . $current_field_id . '" data-type="' . $fo["type"] . '" data-label="[Field Id:'.$current_field_id.']">[Field Id:'.$current_field_id.']</li>';

                                                            }else{
                                                                $selectbox_field_options .= '<li class="arf_selectbox_option" data-value="' . $current_field_id . '" data-type="' . $fo["type"] . '" data-label="' . $arfieldhelper->arf_execute_function($fo["name"],'strip_tags') . '">' . $arfieldhelper->arf_execute_function($fo["name"],'strip_tags') . '</li>';    
                                                            }
                                                            
                                                        }
                                                    }
                                                }
                                                ?>
                                                <input id="arf_conditional_redirect_filed_<?php echo $rule_i; ?>" name="options[arf_conditional_redirect_rules][<?php echo $rule_i; ?>][field_id]" value="<?php echo $redirect_value['field_id']; ?>" type="hidden" />

                                                <input id="arf_conditional_redirect_field_type_<?php echo $rule_i; ?>" name="options[arf_conditional_redirect_rules][<?php echo $rule_i; ?>][field_type]" value="<?php echo isset($redirect_value['field_type']) ? $redirect_value['field_type'] : ''; ?>" type="hidden" />

                                                <dl class="arf_selectbox" data-name="arf_conditional_redirect_filed_<?php echo $rule_i; ?>" data-id="arf_conditional_redirect_filed_<?php echo $rule_i; ?>" style="width: 100%;">
                                                    <dt class="arf_conditional_redirect_filed_<?php echo $rule_i; ?>_dt"><span><?php
                                                        if ($selectbox_field_value_label != "") {
                                                            echo $arfieldhelper->arf_execute_function($selectbox_field_value_label,'strip_tags');
                                                        }elseif ($selectbox_field_value_label == "" && $user_responder_email !="" ) {
                                                             echo '[Field Id:'.$user_responder_email.']';
                                                        } else {
                                                            echo addslashes(esc_html__('Select Field', 'ARForms'));
                                                        }
                                                        ?></span>
                                                    <input value="<?php
                                                    if ($user_responder_email != "") {
                                                        echo $user_responder_email;
                                                    }
                                                    ?>" style="display:none;width:148px;" class="arf_autocomplete" type="text" autocomplete="off">
                                                    <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                                    <dd>
                                                        <ul class="arf_name_field_dropdown arf_conditional_field_dropdown arf_conditional_redirect_field_dropdown" style="display: none;" data-id="arf_conditional_redirect_filed_<?php echo $rule_i; ?>">
                                                            <li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?></li>
                                                            <?php echo $selectbox_field_options; ?>
                                                        </ul>
                                                    </dd>
                                                </dl>
                                            </div>
                                        </span>
                                        <span class="arfconditionalredirectis"><?php echo addslashes(esc_html__('is', 'ARForms')) ?></span>

                                        <span id="select_ar_conditional_redirect_operator_url">
                                            <div class="sltstandard" style="width: 100%;">
                                                <?php echo $arfieldhelper->arf_cl_rule_menu_for_conditional_redirect('arf_conditional_redirect_filed_operator_' . $rule_i, 'arf_conditional_redirect_filed_operator_' . $rule_i, $redirect_value['operator'],$rule_i); ?>
                                            </div>
                                        </span>

                                        <span id="select_ar_conditional_redirect_value_url" style="">
                                            <input style="width:100%" type="text" class="txtstandardnew arfheight34" value="<?php echo $redirect_value['value']; ?>" id="arf_conditional_redirect_filed_value_<?php echo $rule_i; ?>" onkeyup="this.setAttribute('value',this.value)" name="options[arf_conditional_redirect_rules][<?php echo $rule_i; ?>][value]" /> 
                                        </span>
                                        <span id="than_redirect_title"><?php if($rule_i == 1) { echo addslashes(esc_html__('Than redirect to', 'ARForms')); } ?></span>
                                        <span id="arfcondtional_redirect_div_result">
                                            <input style="width:100%;" type="text" class="txtstandardnew arfheight34 arf_conditional_redirect_url_input" data-id="<?php echo $rule_i; ?>" value="<?php echo $redirect_value['redirect_url']; ?>" id="arf_conditional_redirect_url_<?php echo $rule_i; ?>" name="options[arf_conditional_redirect_rules][<?php echo $rule_i; ?>][redirect_url]" onkeyup="javascript:this.setAttribute('value',this.value);"/>
                                            <div class="arferrmessage" id="arf_conditional_redirect_url_error_<?php echo $rule_i; ?>" style="display:none;top:0px;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms')); ?></div>
                                        </span>

                                        <span class="arf_conditional_redirect_bulk_add_remove">
                                            <span class="bulk_add_mail" onclick="arf_conditional_redirect_add();"><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.134,20.362c-5.521,0-9.996-4.476-9.996-9.996
                                            c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.13,15.887,16.654,20.362,11.134,20.362z M11.133,2.314
                                            c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052
                                            C19.185,5.919,15.579,2.314,11.133,2.314z M12.146,14.341h-2v-3h-3v-2h3V6.372h2v2.969h3v2h-3V14.341z"/></g></svg></span>
                                            <span class="bulk_remove_mail" onclick="arf_conditional_redirect_delete_rule(<?php echo $rule_i; ?>)" style="display:<?php echo (count($arf_conditional_redirect_rules) > 1) ? 'inline-block' : 'none'; ?>;"><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.12,20.389c-5.521,0-9.996-4.476-9.996-9.996
                                            c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.116,15.913,16.64,20.389,11.12,20.389z M11.119,2.341
                                            c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052C19.17,5.945,15.565,2.341,11.119,2.341
                                            z M12.131,11.367h3v-2h-3h-2h-3v2h3H12.131z"/></g></svg></span>
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

	

        <div class="arf_conditional_field_dropdown arf_name_field_dropdown" id="arf_conditional_redirect_filed_dropdown_html" style="display: none;">
            <?php
            $conditional_redirect_options = '';
            if (!empty($values['fields'])) {
                foreach ($values['fields'] as $val_key => $fo) {
                    if ($fo['type'] != 'divider' && $fo['type'] != 'break' && $fo['type'] != 'captcha' && $fo['type'] != 'html' && $fo['type'] != 'password') {
                        $current_field_id = $fo["id"];
                        if($current_field_id !="" && $arfieldhelper->arf_execute_function($fo["name"],'strip_tags')==""){
                            $conditional_redirect_options .= '<li class="arf_selectbox_option" data-type="'.$fo['type'].'" data-value="' . $current_field_id . '" data-label="[Field Id:'.$current_field_id.']">[Field Id:'.$current_field_id.']</li>';
                        }else{
                            $conditional_redirect_options .= '<li class="arf_selectbox_option" data-type="'.$fo['type'].'" data-value="' . $current_field_id . '" data-label="' . $arfieldhelper->arf_execute_function($fo["name"],'strip_tags') . '">' . $arfieldhelper->arf_execute_function($fo["name"],'strip_tags') . '</li>';    
                        }
                        
                    }
                }
            }
            ?>
            <li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?></li>
            <?php echo $conditional_redirect_options; ?>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                arf_conditional_redirect_enable_disable();
            });
            function arf_conditional_redirect_enable_disable() {
                if (jQuery("#arf_conditional_redirect_enable").is(':checked'))
                {
                    jQuery("#arf_rule_conditional_redirect_mian").show();
                    jQuery('#success_url').attr('disabled', 'disabled');

                } else {
                    jQuery("#arf_rule_conditional_redirect_mian").hide();
                    jQuery('#success_url').removeAttr('disabled');
                }
            }
        </script>    
        <?php
    }

    function arf_add_conditional_redirect_new_rule() {
        global $arfieldhelper;
        $rule_i = $_POST['next_rule_id'] ? $_POST['next_rule_id'] : 1;
        ?>

        <div class="arf_conditional_redirect_logic_div" style="float: left; width: 100%; margin: 5px 0px;" id="arf_rule_conditional_redirect_<?php echo $rule_i; ?>">
            <input type="hidden" value="<?php echo $rule_i; ?>" name="rule_array_conditional_redirect[]">
            <div style="float: left; width: 100%">
                <span style="float: left; font-size: 14px; line-height: 30px; margin-right: 7px;"><?php echo addslashes(esc_html__('If', 'ARForms')) ?></span>
                <span id="select_ar_conditional_redirect_filed" style="width:170px; float: left;">
                    <div class="sltstandard">

                        <input id="arf_conditional_redirect_filed_<?php echo $rule_i; ?>" name="arf_conditional_redirect_filed_<?php echo $rule_i; ?>" value="" type="hidden">

                        <dl class="arf_selectbox" data-name="arf_conditional_redirect_filed_<?php echo $rule_i; ?>" data-id="arf_conditional_redirect_filed_<?php echo $rule_i; ?>" style="width:160px;">
                            <dt class="arf_conditional_redirect_filed_<?php echo $rule_i; ?>_dt"><span><?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?></span>
                            <input value="" style="display:none;width:148px;" class="arf_autocomplete" type="text">
                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                            <dd>
                                <ul class="arf_name_field_dropdown arf_conditional_field_dropdown arf_conditional_redirect_field_dropdown_ajax" style="display: none;" data-id="arf_conditional_redirect_filed_<?php echo $rule_i; ?>">
                                    <li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?></li>
                                </ul>
                            </dd>
                        </dl>
                    </div>
                </span>
                <span style="float: left; font-size: 14px; line-height: 30px; margin-right: 7px;"><?php echo addslashes(esc_html__('is', 'ARForms')) ?></span>

                <span id="select_ar_conditional_redirect_operator" style="width:160px; float: left;">
                    <div class="sltstandard">
                        <?php echo $arfieldhelper->arf_cl_rule_menu('arf_conditional_redirect_filed_operator_' . $rule_i, 'arf_conditional_redirect_filed_operator_' . $rule_i, ''); ?>
                    </div>
                </span>

                <span id="select_ar_conditional_redirect_value" style="width:190px; float: left;">
                    <input style="width:180px;" type="text" class="txtstandardnew" value="" id="arf_conditional_redirect_filed_value_<?php echo $rule_i; ?>" name="arf_conditional_redirect_filed_value_<?php echo $rule_i; ?>" /> 
                </span>


                <span class="arf_conditional_redirect_bulk_add_remove" style="float: left; margin-top: 15px;margin-left: -6px;">
                    <span class="bulk_add_mail" onclick="arf_conditional_redirect_add();"><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.134,20.362c-5.521,0-9.996-4.476-9.996-9.996             c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.13,15.887,16.654,20.362,11.134,20.362z M11.133,2.314 c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052 C19.185,5.919,15.579,2.314,11.133,2.314z M12.146,14.341h-2v-3h-3v-2h3V6.372h2v2.969h3v2h-3V14.341z"/></g></svg></span>
                    <span class="bulk_remove_mail" onclick="arf_conditional_redirect_delete_rule('<?php echo $rule_i; ?>')" style="display: inline-block;"><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.12,20.389c-5.521,0-9.996-4.476-9.996-9.996c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.116,15.913,16.64,20.389,11.12,20.389z M11.119,2.341c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052C19.17,5.945,15.565,2.341,11.119,2.341z M12.131,11.367h3v-2h-3h-2h-3v2h3H12.131z"/></g></svg></span>
                </span>
            </div>
            <div style="float: left; width: 100%; margin-top: 10px;">
                <span style="float: left; font-size: 14px; line-height: 30px; margin-right: 7px;">
                    <?php echo addslashes(esc_html__('Than redirect to', 'ARForms')) ?>
                </span>
                <span id="" style="float: left;">
                    <input style="width:437px;" type="text" class="txtstandardnew arf_conditional_redirect_url_input" value=""  data-id="<?php echo $rule_i; ?>" id="arf_conditional_redirect_url_<?php echo $rule_i; ?>" name="arf_conditional_redirect_url_<?php echo $rule_i; ?>" onkeyup="javascript:this.setAttribute('value',this.value);" />
                    <div class="howto"><?php esc_html_e('Please insert url with http:// or https://.', 'ARForms'); ?></div>
                    <div class="arferrmessage" id="arf_conditional_redirect_url_error_<?php echo $rule_i; ?>" style="display:none;top:0px;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms')); ?></div>
                </span>
            </div>
        </div>

        <?php
        die();
    }

    function arf_conditional_redirect_save_opt($options, $values) {

        $options['arf_conditional_redirect_enable'] = isset($values['options']['arf_conditional_redirect_enable']) ? $values['options']['arf_conditional_redirect_enable'] : '';

        $options['arf_conditional_redirect_rules'] = isset($values['options']['arf_conditional_redirect_rules']) ? $values['options']['arf_conditional_redirect_rules'] : array();

        return $options;
    }

    function arf_set_conditional_redirect_url($rec_url, $form, $entry_id) {

        if( filter_var($rec_url, FILTER_VALIDATE_EMAIL) ){
            return $rec_url;
        }

        global $wpdb, $arfrecordmeta, $arf_conditional_redirect_to_url;

        $options = $form->options;


        if ($options['success_action'] == 'redirect' && isset($options['arf_conditional_redirect_enable']) && $options['arf_conditional_redirect_enable'] == '1' && !empty($entry_id)) {
            $entry_ids = array($entry_id);
            $values = $arfrecordmeta->getAll("it.field_id != 0 and it.entry_id in (" . implode(',', $entry_ids) . ")", " ORDER BY fi.id");

            if (isset($options['arf_conditional_redirect_rules']) && !empty($options['arf_conditional_redirect_rules'])) {

                $not_matched = 0;
                foreach ($options['arf_conditional_redirect_rules'] as $key => $rules_value) {
                    $redirect_value = '';
                    if (count($values) > 0) {
                        foreach ($values as $value) {
                            if ($rules_value['field_id'] == $value->field_id) {
                                $redirect_value = $value->entry_value;
                                break;
                            }
                        }
                    }

                    $conditional_logic_field_type = $rules_value['field_type'];

                    $conditional_logic_value1 = isset($redirect_value) ? $redirect_value : '';

                    $conditional_logic_value1 = trim(strtolower($conditional_logic_value1));

                    $conditional_logic_value2 = trim(strtolower($rules_value['value']));

                    $conditional_logic_operator = $rules_value['operator'];

                    if ($arf_conditional_redirect_to_url->arf_conditional_redirect_calculate_rule($conditional_logic_value1, $conditional_logic_value2, $conditional_logic_operator, $conditional_logic_field_type)) {
                        $not_matched = 0;
                        $rec_url = $rules_value['redirect_url'];
                        break;
                    } else {
                        $not_matched++;
                    }
                }
            }
        }

        if (isset($not_matched) && $not_matched > 0) {
            $rec_url = false;
        }

        return $rec_url;
    }

    function arf_conditional_redirect_calculate_rule($value1, $value2, $operator, $field_type) {
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

            if ($chk > 0)
                return true;
            else
                return false;
        } else {

            return $arfieldhelper->ar_match_rule($value1, $value2, $operator);
        }

        return false;
    }

    function arf_importtime_update_conditional_redirect_field_id($option_arr_new, $old_new_field_id, $form_id) {
        global $wpdb,$MdlDb;
        if (count($option_arr_new['arf_conditional_redirect_rules']) > 0 and is_array($option_arr_new['arf_conditional_redirect_rules'])) {
            $arf_conditional_redirect_rules = array();
            if (!empty($option_arr_new['arf_conditional_redirect_rules'])) {
                foreach ($option_arr_new['arf_conditional_redirect_rules'] as $new_rule) {

                    $cl_redirect_id = $old_new_field_id[$new_rule['field_id']];
                    $cl_redirect_type = $new_rule['field_type'];

                    $cl_redirect_db_type = $wpdb->get_row($wpdb->prepare("SELECT type FROM `".$MdlDb->fields."` WHERE id = %d",$cl_redirect_id) );

                    if( isset($cl_redirect_db_type) && isset($cl_redirect_db_type->type) && $cl_redirect_db_type->type != $cl_redirect_type){
                        $new_rule['field_type'] = $cl_redirect_db_type->type;
                    }

                    $arf_conditional_redirect_rules[$new_rule['id']] = array(
                        'id' => $new_rule['id'],
                        'field_id' => $old_new_field_id[$new_rule['field_id']],
                        'field_type' => $new_rule['field_type'],
                        'operator' => $new_rule['operator'],
                        'value' => $new_rule['value'],
                        'redirect_url' => $new_rule['redirect_url']
                    );
                }
            }
            $option_arr_new['arf_conditional_redirect_rules'] = $arf_conditional_redirect_rules;
        }
        return $option_arr_new;
    }

}
?>
