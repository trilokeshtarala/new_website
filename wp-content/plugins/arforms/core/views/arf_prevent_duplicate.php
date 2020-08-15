<?php
global $arf_prevent_duplicate;
$arf_prevent_duplicate = new arf_prevent_duplicate();

class arf_prevent_duplicate {

    function __construct() {

        add_action('arf_additional_onsubmit_settings', array($this, 'arf_prevent_duplicate_data_onsubmit'), 10, 2);

        /* arf_dev_flag from action to filter*/
        add_filter('arf_predisplay_form', array($this, 'arf_prevent_duplicate_before_form_render'), 10, 2);

        add_filter('arf_validate_form_outside_errors', array($this, 'arf_prevent_duplicate_card'), 10, 4);

        add_filter('arf_prevent_duplicate_entry',array($this,'arf_prevent_duplicate_entry_before_create'), 10,3);
    }

    function arf_prevent_duplicate_entry_before_create($error_message, $form_id, $values){
        global $wpdb, $MdlDb, $arfsettings;

        $validate = false;

        $arf_errors = array();

        $arf_errors = $this->arf_prevent_duplicate_card($arf_errors, $form_id, $values, array() );

        $error_message = "";
        
        if( !empty($arf_errors) && $arf_errors['arf_message_error'] != '' ){
            $validate = true;
            $error_message = $arf_errors['arf_message_error'];
            $arfsettings->failed_msg = $error_message;
        }


        return $validate;
    }



    function arf_prevent_duplicate_data_onsubmit($id, $values) {

        global $armainhelper, $arformcontroller,$arfieldhelper;
        ?>
        <div class="arfsettingspacer"></div>
        <div class="arfcolumnleft">
            <div class="arf_popup_checkbox_wrapper">
                <div class="arf_custom_checkbox_div" >
                    <div class="arf_custom_checkbox_wrapper" onclick="preventduplicatefield();">
                        <input type="checkbox" <?php isset($values["arf_pre_dup_check"]) ? checked($values["arf_pre_dup_check"], 1) : ''; ?> value="1" id="arf_pre_dup_check" name="options[arf_pre_dup_check]" class="chkstanard" >
                        <svg width="18px" height="18px">
                            <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                            <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                        </svg>
                    </div>
                    <span>
                        <label for="arf_pre_dup_check" onclick="preventduplicatefield();"><?php echo addslashes(esc_html__('Prevent duplicate form entries', 'ARForms')) ?></label>
                    </span>
                </div>
            </div>
        </div>
        <div class="arfsettingspacer"></div>
        <?php
        if (isset($values["arf_pre_dup_check"]) && $values["arf_pre_dup_check"] == 1) {
            $arf_pre_dup_class = 'arf_pre_dup_show';
        } else {
            $arf_pre_dup_class = 'arf_pre_dup_hide';
        }
        ?>
        <div class="arf_clear_both"></div>
        <div class="sltstandard <?php echo $arf_pre_dup_class; ?>" id="prevent_duplicate_field">

            <div class="arfsettingspacer prevent_duplicate_field_settingspacer"></div>
            <?php
                if( !isset($values['arf_pre_dup_check_type']) || $values['arf_pre_dup_check_type'] == '' ){
                    $values['arf_pre_dup_check_type'] = "ip_address";
                }
            ?>
            <div class="arf_prevent_duplicate_entry_options arfmarginl20">
                <div class="arf_prevent_duplicate_entry_opt_label"><?php echo addslashes(esc_html__('Check duplicate based on','ARForms')); ?> </div>
                <div class="arf_prevent_duplicate_entry_opt_input">
                    <div class="arf_radio_wrapper">
                        <div class="arf_custom_radio_div">
                            <div class="arf_custom_radio_wrapper">
                                <input type="radio" <?php isset($values["arf_pre_dup_check_type"]) ? checked($values["arf_pre_dup_check_type"],"ip_address") : ''; ?> value="ip_address" class="arf_pre_dup_check_type_object" id="arf_pre_dup_check_type_ip" name="options[arf_pre_dup_check_type]" />
                                <svg width="18px" height="18px">
                                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                </svg>
                            </div>
                        </div>
                        <span>
                            <label id="arf_pre_dup_check_type_ip" for="arf_pre_dup_check_type_ip"><?php echo addslashes(esc_html__('IP address', 'ARForms')) ?></label>
                        </span>
                    </div> 
                    <div class="arf_radio_wrapper">
                        <div class="arf_custom_radio_div">
                            <div class="arf_custom_radio_wrapper">
                                <input type="radio" <?php isset($values['arf_pre_dup_check_type']) ? checked($values['arf_pre_dup_check_type'],"current_user") : ''; ?> value="current_user" class="arf_pre_dup_check_type_object" id="arf_pre_dup_check_type_user" name="options[arf_pre_dup_check_type]" />
                                <svg width="18px" height="18px">
                                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                </svg>
                            </div>
                        </div>
                        <span>
                            <label id="arf_pre_dup_check_type_user" for="arf_pre_dup_check_type_user"><?php echo addslashes(esc_html__('Current logged in user','ARForms')); ?></label>
                        </span>
                    </div>  
                    <div class="arf_radio_wrapper">
                        <div class="arf_custom_radio_div">
                            <div class="arf_custom_radio_wrapper">
                                <input type="radio" <?php isset($values['arf_pre_dup_check_type']) ? checked($values['arf_pre_dup_check_type'],"fields") : ''; ?> value="fields" class="arf_pre_dup_check_type_object" id="arf_pre_dup_check_type_form_fields" name="options[arf_pre_dup_check_type]" />
                                <svg width="18px" height="18px">
                                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                </svg>
                            </div>
                        </div>
                        <span>
                            <label id="arf_pre_dup_check_type_form_fields" for="arf_pre_dup_check_type_form_fields"><?php echo addslashes(esc_html__('Form Fields','ARForms')); ?></label>
                        </span>
                    </div>
                </div>                                                   
            </div>

            <div class="arfsettingspacer prevent_duplicate_field_settingspacer"></div>
            <?php
            $enable_dropdown = "display:none;";
            if( isset($values['arf_pre_dup_check_type']) && $values['arf_pre_dup_check_type'] == "fields" ){
                $enable_dropdown = "display:block;";
            }
            ?>
            <span id="select_ar_prevent_duplicate_field" style="<?php echo $enable_dropdown; ?>">
                <div class="arf_prevent_duplicate_entry_field_label"><?php echo addslashes(esc_html__('Select Field','ARForms')); ?> </div>
                <div class="sltstandard">
                    <?php
                    $selectbox_field_options = "";
                    $selectbox_field_value_label = "";
                    $user_responder_email = "";

                    if (!empty($values['fields'])) {
                        foreach ($values['fields'] as $field2) {
                            if (in_array($field2['type'], array('email', 'text', 'hidden', 'radio', 'select'))) {
                                if (isset($values['arf_pre_dup_field']) && ($field2["id"] == $values['arf_pre_dup_field'])) {
                                    $selectbox_field_value_label = $field2["name"];
                                    $user_responder_email = $values['arf_pre_dup_field'];
                                } 

                                $current_field_id = $field2["id"];
                                if($current_field_id !="" && $arfieldhelper->arf_execute_function($field2["name"],'strip_tags')=="" ){
                                    $selectbox_field_options .= '<li class="arf_selectbox_option" data-value="' . $current_field_id . '" data-label="[Field Id:'.$current_field_id.']">[Field Id:'.$current_field_id.']</li>';
                                }else{
                                    $selectbox_field_options .= '<li class="arf_selectbox_option" data-value="' . $current_field_id . '" data-label="' . $arfieldhelper->arf_execute_function($field2["name"],'strip_tags') . '">' . $arfieldhelper->arf_execute_function($field2["name"],'strip_tags') . '</li>';                                    
                                }

                            }
                        }
                    }
                    ?>
                    <input id="arf_pre_dup_field" name="options[arf_pre_dup_field]" value="<?php
                    if ($user_responder_email != "") {
                        echo $user_responder_email;
                    }
                    else {
                        echo "";
                    }
                    ?>" type="hidden">

                    <dl class="arf_selectbox" data-name="arf_pre_dup_field" data-id="arf_pre_dup_field" style="width:240px;">
                        <dt class="arf_pre_dup_field_dt">
                            <span><?php
                            if ($selectbox_field_value_label != "") {
                                echo $arfieldhelper->arf_execute_function($selectbox_field_value_label,'strip_tags');
                            }else if($selectbox_field_value_label=="" && $user_responder_email !=""){
                                echo '[Field Id:'.$user_responder_email.']';
                            } else {
                                echo addslashes(esc_html__('Select Field', 'ARForms'));
                            }
                            ?></span>
                            <input value="<?php
                            if ($user_responder_email != "") {
                                echo $user_responder_email;
                            }
                            ?>" style="display:none;width:148px;" class="arf_autocomplete" type="text">
                            <i class="arfa arfa-caret-down arfa-lg"></i>
                        </dt>
                        <dd>
                            <ul class="arf_pre_dup_field_dropdown" style="display: none;" data-id="arf_pre_dup_field">
                                <li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?></li>
                                    <?php echo $selectbox_field_options; ?>
                            </ul>
                        </dd>
                    </dl>
                    <div class="arferrmessage" id="arf_pre_dup_field_error" style="display:none;top:5px;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms')); ?></div>
                </div>
            </span>
        </div>

        <div class="arfsettingspacer"></div>
        <div id="prevent_duplicate_message_box" class="arftablerow prevent_duplicate_message_box prevent_duplicate_box <?php echo $arf_pre_dup_class; ?>" style="margin-left: 45px;">
            <div class="arfcolumnleft arfsettingsubtitle"><?php echo esc_html__('Duplicate entry messages', 'ARForms'); ?></div>
            <div class="arfcolumnright arf_pre_dup_msg_width">
                <textarea id="arf_pre_dup_msg" name="options[arf_pre_dup_msg]" class="txtmodal1 auto_responder_webform_code_area" style="padding: 10px;"><?php echo (isset($values['arf_pre_dup_msg']) && $values['arf_pre_dup_msg']!='') ? $armainhelper->esc_textarea($arformcontroller->br2nl($values['arf_pre_dup_msg'])) : addslashes(esc_html__('You have already submitted this form before. You are not allowed to submit this form again.','ARForms')); ?></textarea><br />
                <div class="arferrmessage" id="arf_pre_dup_msg_error" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms')); ?></div>
            </div>
        </div>
        <div class="arfsettingspacer"></div>
    <?php
    }

    function arf_prevent_duplicate_card($arf_errors, $form_id, $values, $arf_form_data = array()) {
        global $wpdb, $arfrecordmeta, $arfsettings, $current_user,$MdlDb;

        $form_data = $wpdb->get_results($wpdb->prepare('SELECT * FROM  ' .$MdlDb->forms.' WHERE id = %d', $form_id));

        if (count($form_data) < 1)
            return $arf_errors;

        $form_data = $form_data[0];

        $form_options = maybe_unserialize($form_data->options);
        $arf_pre_dup_field = '';
        $arf_pre_dup_type = '';
        if ($form_options['arf_pre_dup_check'] && $form_options['arf_pre_dup_check'] == 1) {

            if( isset($form_options['arf_pre_dup_check_type']) && $form_options['arf_pre_dup_check_type']) {
                $arf_pre_dup_type = $form_options['arf_pre_dup_check_type'];
            }

            if (isset($form_options['arf_pre_dup_field']) && $form_options['arf_pre_dup_field']){
                $arf_pre_dup_field = $form_options['arf_pre_dup_field'];
            }

            if (isset($form_options['arf_pre_dup_msg']) && $form_options['arf_pre_dup_msg']){
                $arf_pre_dup_msg = $form_options['arf_pre_dup_msg'];
            }

            if ($arf_pre_dup_type && $arf_pre_dup_type == 'fields') {
                $pre_dup_form_data = $wpdb->get_results($wpdb->prepare('SELECT em.*,e.* FROM ' .$MdlDb->entry_metas.' em LEFT JOIN '.$MdlDb->entries.' e ON em.entry_id=e.id  WHERE em.field_id = %d AND e.form_id = %d', $arf_pre_dup_field, $form_id));

                if (isset($values['item_meta']) && $values['item_meta']) {

                    foreach ($values['item_meta'] as $curr_field_id => $curr_field_value) {

                        if ($arf_pre_dup_field == $curr_field_id && $curr_field_value != '')
                        {
                            if(!empty($pre_dup_form_data)){

                                foreach ($pre_dup_form_data as $pre_dup_form) {

                                    if(isset($pre_dup_form->entry_value) && $pre_dup_form->entry_value!= '' && $pre_dup_form->entry_value == $curr_field_value)
                                    {
                                        $arf_errors['arf_message_error'] =$arf_pre_dup_msg;
                                        break;
                                    }

                                 }
                            }

                        }
                    }
                }

            } elseif ($arf_pre_dup_type && $arf_pre_dup_type == 'ip_address') {
                $pre_dup_form_data = $wpdb->get_results($wpdb->prepare('SELECT * FROM  ' .$MdlDb->entries.' WHERE form_id = %d', $form_id));

                if(!empty($pre_dup_form_data)){
                    foreach ($pre_dup_form_data as $pre_dup_form) {

                        if(isset($pre_dup_form->ip_address) && $pre_dup_form->ip_address!= '' && $_SERVER['REMOTE_ADDR']!= '' && $pre_dup_form->ip_address == $_SERVER['REMOTE_ADDR'])
                        {
                            $arf_errors['arf_message_error'] = $arf_pre_dup_msg;
                            break;
                        }

                    }
                }
            } elseif ($arf_pre_dup_type && $arf_pre_dup_type == 'current_user') {
                global $user_ID;

                if($user_ID){
                    $pre_dup_form_data = $wpdb->get_results($wpdb->prepare('SELECT * FROM  ' .$MdlDb->entries.' WHERE form_id = %d', $form_id));

                    if(!empty($pre_dup_form_data)){
                        foreach ($pre_dup_form_data as $pre_dup_form) {

                            if(isset($pre_dup_form->user_id) && $pre_dup_form->user_id != '' && $user_ID != '' && $pre_dup_form->user_id == $user_ID)
                            {
                                $arf_errors['arf_message_error'] = $arf_pre_dup_msg;
                                break;
                            }

                        }
                    }
                }
            }
        }
        return $arf_errors;
    }

    function arf_prevent_duplicate_before_form_render($arf_form,$form) {
        global $wpdb,$MdlDb;

        $form_id2 = $form->id;

        if(!isset($GLOBALS['arf_form_data'][$form_id2])){
            $form_data2 = $wpdb->get_results($wpdb->prepare('SELECT * FROM '.$MdlDb->forms.' WHERE id = %d', $form_id2));
            $form_data2 = $form_data2[0];
        } else {
            $form_data2 = $GLOBALS['arf_form_data'][$form_id2];
        }



        if (count((array)$form_data2) < 1){
            return;
        }

        $form_options = maybe_unserialize($form_data2->options);

        if (isset($form_options['arf_pre_dup_check']) && $form_options['arf_pre_dup_check'] == 1) {
            return $arf_form.'<div id="arf_message_error" class="frm_error_style" style="display:none;"><div class="msg-detail"><div class="msg-description-success">' . addslashes(esc_html__('Prevent Duplicate Entry', 'ARForms')) . '</div></div></div>';
        } else {
            return $arf_form;
        }
    }
}
?>