<?php
global $arfieldhelper, $arformhelper, $MdlDb, $fields_with_external_js, $bootstraped_fields_array,$arformcontroller;
$frm_class = 'arf_standard_form';
if( $newarr['arfinputstyle'] == 'rounded' ){
    $frm_class = 'arf_rounded_form';
} else if ($newarr['arfinputstyle'] == 'material' ){
    $frm_class = 'arf_materialize_form';
}

if($_GET['arfaction'] == 'new' || $_GET['arfaction'] =='duplicate'){
    if($define_template < 100){
        $values['name'] = isset($_GET['form_name']) ? stripslashes_deep($arformcontroller->arfHtmlEntities($_GET['form_name'],true)) : '';
        $values['description'] = isset($_GET['form_desc']) ? stripslashes_deep($arformcontroller->arfHtmlEntities($_GET['form_desc'],true)) : '';
    }
}
?>
<div id="arfmainformeditorcontainer" class="arf_form arf_form_outer_wrapper arf_main_tabs active_tabs arf_form ar_main_div_<?php echo $id; ?>">
    <div class="allfields">
        <div id="arf_fieldset_<?php echo $id; ?>" class="arf_fieldset <?php echo $frm_class; ?>">
            <div id="success_message" class="arf_success_message">
                <div class="message_descripiton">
                    <div style="float: left; margin-right: 15px;"><?php echo addslashes(esc_html__('Form is successfully updated', 'ARForms')); ?></div>
                    <div class="message_svg_icon">
                        <svg style="height: 14px;width: 14px;"><path fill-rule="evenodd" clip-rule="evenodd" fill="#FFFFFF" d="M6.075,14.407l-5.852-5.84l1.616-1.613l4.394,4.385L17.181,0.411
                                                                            l1.616,1.613L6.392,14.407H6.075z"></path></svg>
                    </div>
                </div>
            </div>
            <div id="error_message" class="arf_error_message">
                <div class="message_descripiton">
                    <div style="float: left; margin-right: 15px;"><?php echo addslashes(esc_html__('Form is not successfully updated','ARForms')); ?></div>
                    <div class="message_svg_icon">
                            <svg style="height: 14px;width: 14px;"><path fill-rule="evenodd" clip-rule="evenodd" fill="#ffffff" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></svg>
                    </div>
                </div>
            </div>            
            <div id="titlediv" class="arftitlediv" <?php echo (isset($newarr['display_title_form']) && $newarr['display_title_form'] == 0 ) ? 'style="display:none;"' : '';?>>
                <input type="hidden" value="<?php echo ARFURL . '/images'; ?>" id="plugin_image_path" />

                <div id="form_desc" class="edit_form_item arffieldbox frm_head_box">

                    <div class="arfformnamediv">
                        <div class="arfformedit arftitlecontainer">
                            <span class="arfeditorformname formtitle_style arf_edit_in_place" id="frmform_<?php echo $id; ?>">
                                <input type="text" name="name" id="form_name" class="arf_edit_in_place_input inplace_field" value="<?php echo stripslashes_deep($values['name']); ?>" data-default-value="<?php echo stripslashes_deep($values['name']); ?>" data-ajax="false" data-action="arfupdateformname" placeholder="<?php echo addslashes(esc_html__('Click here to enter form title', 'ARForms'));?>"/>
                            </span>
                        </div>
                        <div class="arfformeditpencil" style="margin-top:3px;"></div>
                    </div>
                    <div style="clear:both;"></div>
                    <div class="arfformdescriptiondiv">
                        <div class="arfdescriptionedit">

                            <div class="arfeditorformdescription arf_edit_in_place formdescription_style"><input type="text" data-default-value="<?php echo ($values['description'] != '') ? stripslashes_deep($values['description']) : addslashes(esc_html__('Click here to enter form description', 'ARForms')); ?>" class="arf_edit_in_place_input inplace_field" data-ajax="false" name="description" data-action="arfupdateformdescription" value="<?php echo ($values['description'] != '') ? stripslashes_deep($values['description']) : ""; ?>" placeholder="<?php echo addslashes(esc_html__('Click here to enter form description', 'ARForms'));?>"/></div>
                        </div>
                        <div class="arfdescriptioneditpencil"></div>    
                    </div>
                    <div style="clear:both;"></div>
                </div>
                <div style="clear:both;"></div>

            </div>




            <div id="new_fields" data-flag="1" class="newfield_div">


                <?php
                $index_arf_fields = 0;
                if (isset($values['fields']) && !empty($values['fields'])) {
                    $arf_load_password = array();
                    $arf_load_confirm_email = array();
                    $totalpass = 0;
                    foreach ($values['fields'] as $arrkey => $field) {
                        if ($field['type'] == 'password') {
                            $field['id'] = $arfieldhelper->get_actual_id($field['id']);
                            if (isset($field['confirm_password']) and $field['confirm_password'] == 1 and isset($arf_load_password['confrim_pass_field']) and $arf_load_password['confrim_pass_field'] == $field['id'])
                                $values['confirm_password_arr'][$field['id']] = isset($field['confirm_password_field']) ? $field['confirm_password_field'] : "";
                            else
                                $arf_load_password['confrim_pass_field'] = isset($field['confirm_password_field']) ? $field['confirm_password_field'] : "";
                        }

                        if ($field['type'] == 'email') {
                            $field['id'] = $arfieldhelper->get_actual_id($field['id']);

                            if (isset($field['confirm_email']) and $field['confirm_email'] == 1 and isset($arf_load_confirm_email['confrim_email_field']) and $arf_load_confirm_email['confrim_email_field'] == $field['id'])
                                $values['confirm_email_arr'][$field['id']] = isset($field['confirm_email_field']) ? $field['confirm_email_field'] : "";
                            else
                                $arf_load_confirm_email['confrim_email_field'] = isset($field['confirm_email_field']) ? $field['confirm_email_field'] : "";
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
                    $arf_fields = array();
                    
                    if($arfaction == 'duplicate')
                    {
                        $arf_fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM `" . $MdlDb->fields . "` WHERE `form_id` = %d", $define_template), ARRAY_A);
                    }
                    else if($arfaction == 'edit')
                    {
                        $arf_fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM `" . $MdlDb->fields . "` WHERE `form_id` = %d", $id), ARRAY_A);
                    }

                    $arf_is_page_break_no = 0;

                    $frm_opts = maybe_unserialize($data['options']);
                    
                    $frm_css = maybe_unserialize($data['form_css']);

                    $field_order = $frm_opts['arf_field_order'];

                    $field_resize_width = isset($frm_opts['arf_field_resize_width']) ? $frm_opts['arf_field_resize_width'] : '';

                    $field_temp_fields = maybe_unserialize($data['temp_fields']);

                    $arf_field_counter = 1;
                    if ($field_resize_width != '') {
                        $field_resize_width = json_decode($field_resize_width, true);
                    }


                    $arf_sorted_fields = array();
                    if ($field_order != '') {
                        $field_order = json_decode($field_order, true);

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
                    
                    
                    foreach ($arf_fields as $field_key => $field) {
                        if(is_array($field)){
			                 if( $field['type'] == 'hidden' ){
                                continue;
                            }
                            if ($field['type'] == 'break' && $arf_is_page_break_no == 0) {
                                $field['page_break_first_use'] = 1;
                                $arf_is_page_break_no++;
                            }
                            $field_name = "item_meta[" . $field['id'] . "]";
                            $has_field_opt = false;
                            if (isset($field['options']) && $field['options'] != '' && !empty($field['options'])) {
                                $has_field_opt = true;
                                $field_options_db = json_decode($field['options'], true);
                                if (json_last_error() != JSON_ERROR_NONE) {
                                    $field_options_db = maybe_unserialize($field['options'], true);
                                }
                            }
                            
                            $field_opt = json_decode($field['field_options'], true);
                            
                            if (json_last_error() != JSON_ERROR_NONE) {
                                $field_opt = maybe_unserialize($field['field_options']);
                            }

                            $class = (isset($field_opt['inner_class']) && $field_opt['inner_class']) ? $field_opt['inner_class'] : 'arf_1col';
                            array_push($class_array,$class);

                            if (isset($field_opt) && !empty($field_opt) && is_array($field_opt) ) {
                                foreach ($field_opt as $k => $field_opt_val) {
                                    if ($k != 'options') {
                                        $field[$k] = $arformcontroller->arf_html_entity_decode($field_opt_val);
                                    } else {
                                        if ($has_field_opt == true && $k == 'options') {
                                            $field[$k] = $field_options_db;
                                        }
                                    }
                                }
                            }
                            if (in_array($field['type'], $bootstraped_fields_array)) {
                                array_push($fields_with_external_js, $field['type']);
                            }
                        }
                        
                        require(VIEWS_PATH . '/arf_field_editor.php');
                        
                        unset($field);


                        unset($field_name);

                        $arf_field_counter++;
                    }
                }
                
                ?>

            </div>

            <?php
            echo "<label class='arf_main_label arf_width_counter_label'></label>";            
            echo "<label class='arf_main_label arf_width_counter_label_divider'></label>";            
            $newarr['arfsubmitbuttontext'] = isset($newarr['arfsubmitbuttontext']) ? $newarr['arfsubmitbuttontext'] : '';
            if ($newarr['arfsubmitbuttontext'] == '') {
                $arf_option = get_option('arf_options');
                $submit_value = $arf_option->submit_value;
            } else {
                $submit_value = esc_attr($newarr['arfsubmitbuttontext']);
            }

            $submit_buttonwidth = $newarr['arfsubmitbuttonwidthsetting'] ? $newarr['arfsubmitbuttonwidthsetting'] : '';
            ?>
            <div style="clear:both;"></div>
            <div class="arfeditorsubmitdiv arf_submit_div top_container">
                <div class="arfsubmitedit arfsubmitbutton">
                    <div class="arf_greensave_button_wrapper">
                        <?php 
                        $arfsubmitbuttonstyleclass = '';

                        if(isset($newarr['arfsubmitbuttonstyle']) && $newarr['arfsubmitbuttonstyle'] == 'flat'){
                            $arfsubmitbuttonstyleclass= 'arf_submit_btn_flat';
                        } else if(isset($newarr['arfsubmitbuttonstyle']) &&  $newarr['arfsubmitbuttonstyle'] == 'border'){
                            $arfsubmitbuttonstyleclass= 'arf_submit_btn_border';
                        } else if(isset($newarr['arfsubmitbuttonstyle']) && $newarr['arfsubmitbuttonstyle'] == 'reverse border'){
                            $arfsubmitbuttonstyleclass= 'arf_submit_btn_reverse_border';
                        }
                        ?>
                        <div class="greensavebtn arf_submit_btn btn btn-info arfstyle-button waves-effect waves-light <?php echo $arfsubmitbuttonstyleclass;?>" data-auto="<?php
                        if ($submit_buttonwidth != '') {
                            echo '1';
                        } else {
                            echo '0';
                        }
                        ?>" <?php
                                if ($submit_buttonwidth != '') {
                                    echo 'style="width:' . $submit_buttonwidth . 'px;"';
                                }
                                ?> data-style="zoom-in" data-width="<?php echo $submit_buttonwidth; ?>">
                            <div class="arfsubmitbtn arf_edit_in_place" id="arfeditorsubmit">
                                <input type='text' class='arf_edit_in_place_input inplace_field arf_submit_button_textbox' data-id="arf_form_submit_button" data-ajax='false' value="<?php echo $submit_value; ?>" />
                            </div>
                        </div>
                        <span class="arf_submit_button_edit_icon"><svg width='18' height='18' fill='rgb(255, 255, 255)' xmlns='http://www.w3.org/2000/svg' data-name='Layer 1' viewBox='0 0 512 512' x='0px' y='0px'><title>Edit</title><path d='M318.37,85.45L422.53,190.11,158.89,455,54.79,350.38ZM501.56,60.2L455.11,13.53a45.93,45.93,0,0,0-65.11,0L345.51,58.24,449.66,162.9l51.9-52.15A35.8,35.8,0,0,0,501.56,60.2ZM0.29,497.49a11.88,11.88,0,0,0,14.34,14.17l116.06-28.28L26.59,378.72Z'/></svg></span>
                    </div>
                </div>
                <div class="arfsubmiteditpencil arfhelptip" title="<?php echo addslashes(esc_html__('Edit Text', 'ARForms')); ?>"></div>
                <div class="arfsubmitsettingpencil arfhelptip" title="<?php echo addslashes(esc_html__('Settings', 'ARForms')); ?>" id="field-setting-button-arfsubmit" onclick="arfshowfieldoptions('arfsubmit')" data-lower="false"></div>
            </div>
        </div>
    </div>
    <?php
    $key = isset($values['form_key']) ? $values['form_key'] : '';

    $width = isset($_COOKIE['width']) ? $_COOKIE['width'] * 0.80 : 0;

    $width_new = '&width=' . $width;
    ?>
    <?php
    $delete_modal_width = isset($_COOKIE['width']) ? ($_COOKIE['width'] - 850) / 2 : 'auto';
    $delete_modal_height = isset($_COOKIE['height']) ? ($_COOKIE['height'] - 500) / 2 : 'auto';
    ?>
    <div style="clear:both;"></div>

</div>




<?php
$widthmaincontent = isset($_COOKIE['width']) ? $_COOKIE['width'] - 650 : 0;
$extra_width = "0";

$left_width = ( ($widthmaincontent) / 2 + $extra_width) . 'px';
if (is_rtl()) {
    $iframediv_loader_style = 'right:' . $left_width . ';top:180px;';
} else {
    $iframediv_loader_style = 'left:' . $left_width . ';top:180px;';
}

$delete_modal_width = isset($_COOKIE['width']) ? ($_COOKIE['width'] - 350) / 2 : 'auto';
$delete_modal_height = isset($_COOKIE['height']) ? ($_COOKIE['height'] - 180) / 2 : 'auto';

$key = isset($values['form_key']) ? $values['form_key'] : '';
?>
<div style="left:-999px; position:fixed; visibility:hidden;">
    <div class="greensavebtn" style="float:left;min-width: 105px;" id="arfsubmitbuttontext2"><?php echo $submit_value; ?></div>
</div>
<input type="hidden" name="arf_editor_total_rows" id="arf_editor_total_rows" value="<?php echo $index_arf_fields;?>" />