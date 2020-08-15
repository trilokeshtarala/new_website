<div class="arf_field_option_model" id="arf_field_option_model_skeleton">
    <div class="arf_field_option_model_header"><?php echo addslashes(esc_html__('Field Options', 'ARForms')); ?></div>
    <div class="arf_field_option_model_container">
        <div class="arf_field_option_content_row">
            <div class="arf_field_option_content_cell" data-sort="-1" id="labelname">
                <input type="checkbox" class="" name="required" id="frm_req_field_{arf_field_id}" onchange="arfmakerequiredfieldfunction('{arf_field_id}', 0, 1);" value="1" style="display:none;" />
                <label class="arf_field_option_content_cell_label"><?php echo esc_html__('Label Name', 'ARForms'); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input class="arf_field_option_input_text" name="name" id="arfname_{arf_field_id}" value="" type="text">
                </div>
            </div>

            <div class="arf_field_option_content_cell" data-sort="-1" id="max_opt_selected">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Max Option Selected', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" class="arf_field_option_input_text" name="max_opt_sel" id="maxoptsel" value="" />
                </div>
            </div>

            <div class="arf_field_option_content_cell" data-sort="-1" id="max_opt_selected_msg">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Max Option Selected Message', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" class="arf_field_option_input_text" name="max_opt_sel_msg" id="maxoptselmsg" value="" />
                </div>
            </div>

            <div class="arf_field_option_content_cell" data-sort="-1" id="requiredmsg">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Message for blank field', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" class="arf_field_option_input_text" name="blank" id="arfrequiredfieldtext{arf_field_id}" value=" " />
                </div>
            </div>

            <div class="arf_field_option_content_cell" data-sort="-1" id="leftlable">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Left Lable', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" class="arf_field_option_input_text" name="leftlable" id="arfleftlabletext{arf_field_id}" value="" />
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="leftvalue">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Left Value', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" class="arf_field_option_input_text" name="leftvalue" id="arfleftlabletext{arf_field_id}" value="" />
                </div>
            </div>

            <div class="arf_field_option_content_cell" data-sort="-1" id="rightlable">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Right Lable', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" class="arf_field_option_input_text" name="rightlable" id="arfrightlabletext{arf_field_id}" value="" />
                </div>
            </div>

            <div class="arf_field_option_content_cell" data-sort="-1" id="rightvalue">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Right Value', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" class="arf_field_option_input_text" name="rightvalue" id="arfrightlabletext{arf_field_id}" value="" />
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="attachfiletoemail">
                <label class="arf_field_option_content_cell_label"><?php echo esc_html__('Attach file with email', 'ARForms'); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <label class="arf_js_switch_label">
                        <span><?php echo addslashes(esc_html__('NO', 'ARForms')); ?>&nbsp;</span>
                    </label>
                    <span class="arf_js_switch_wrapper arf_no_transition">
                        <input type="checkbox" class="js-switch attach_{arf_field_id}" name="attach" id="field_options[attach_{arf_field_id}]" value="1" />
                        <span class="arf_js_switch"></span>
                    </span>
                    <label class="arf_js_switch_label">
                        <span>&nbsp;<?php echo addslashes(esc_html__('YES', 'ARForms')); ?></span>
                    </label>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="uploadbuttontext">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Upload button text', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input id="arffileuploadbuttontext" class="arf_field_option_input_text" value="<?php echo addslashes(esc_html__('Upload', 'ARForms')); ?>" name="file_upload_text" type="text">
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="number_of_rows">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Number of Rows', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" class="arf_field_option_input_text" name="max_rows" id="maxrows_{arf_field_id}" />
                </div>
            </div>
	    
	    <div class="arf_field_option_content_cell" data-sort="-1" id="maxfileuploadsize">
		<label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Maximum Upload Size', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" id="" class="arf_field_option_input_text arfwidth80" name="max_fileuploading_size" />
		    <div class="arfwidthpx">MB</div>
            <span class="arf_field_option_input_note">
            <span class="arf_field_option_input_note_text"><?php echo addslashes(esc_html__('Current Server Limit : ' . ini_get('upload_max_filesize'), 'ARForms')); ?></span>
            </span>
            </div>
	    </div>

	    <div class="arf_field_option_content_cell" data-sort="-1" id="invalidfilesizemessage">
		<label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Message for Invalid File Size', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" id="invalid_file_size_message_{arf_field_id}" class="arf_field_option_input_text" name="invalid_file_size"  />
                </div>
	    </div>
	     <div class="arf_field_option_content_cell" data-sort="-1" id="enable_multiple_file_upload">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Enable Multiple File Upload', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <label class="arf_js_switch_label">
                        <span><?php echo addslashes(esc_html__('NO', 'ARForms')); ?>&nbsp;</span>
                    </label>
                    <span class="arf_js_switch_wrapper">
                        <input type="checkbox" class="js-switch arf_is_multiple_file_{arf_field_id}" name="arf_is_multiple_file" id="arf_is_multiple_file_{arf_field_id}" value="1" />
                        <span class="arf_js_switch"></span>
                    </span>
                    <label class="arf_js_switch_label">
                        <span>&nbsp;<?php echo addslashes(esc_html__('YES', 'ARForms')); ?></span>
                    </label>
                </div>
            </div>
        <div class="arf_field_option_content_cell" data-sort="-1" id="arf_enable_readonly">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Enable Readonly', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <label class="arf_js_switch_label">
                        <span><?php echo addslashes(esc_html__('NO', 'ARForms')); ?>&nbsp;</span>
                    </label>
                    <span class="arf_js_switch_wrapper">
                        <input type="checkbox" class="js-switch arf_enable_readonly_{arf_field_id}" name="arf_enable_readonly" id="arf_enable_readonly_{arf_field_id}" value="1" />
                        <span class="arf_js_switch"></span>
                    </span>
                    <label class="arf_js_switch_label">
                        <span>&nbsp;<?php echo addslashes(esc_html__('YES', 'ARForms')); ?></span>
                    </label>
                </div>
            </div>
        <?php if (isset($arfsettings->form_submit_type) && $arfsettings->form_submit_type == 1) { ?>
	       <div class="arf_field_option_content_cell" data-sort="-1" id="isdragable">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Enable Droppable Area', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <label class="arf_js_switch_label">
                        <span><?php echo addslashes(esc_html__('NO', 'ARForms')); ?>&nbsp;</span>
                    </label>
                    <span class="arf_js_switch_wrapper">
                        <input type="checkbox" class="js-switch arf_draggable_{arf_field_id}" name="arf_draggable" id="arf_draggable_field_{arf_field_id}" value="1" />
                        <span class="arf_js_switch"></span>
                    </span>
                    <label class="arf_js_switch_label">
                        <span>&nbsp;<?php echo addslashes(esc_html__('YES', 'ARForms')); ?></span>
                    </label>
                </div>
            </div>
        <div class="arf_field_option_content_cell" data-sort="-1" id="dragable_label">
            <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Label Of Drag area', 'ARForms')); ?></label>
            <div class="arf_field_option_content_cell_input">
                <input type="text" id="arf_dragable_label_{arf_field_id}" class="arf_field_option_input_text" name="arf_dragable_label" readonly="readonly" />
            </div>
        </div>
        <?php } ?>
	     <div class="arf_field_option_content_cell" data-sort="-1" id="fieldsize_phone">
            <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Field Size (Characters)', 'ARForms')); ?></label>
            <div class="arf_field_option_content_cell_input">
                <input type="text" class="arf_field_option_input_text" name="max" id="fieldsize_phone_{arf_field_id}"/>
                <span class="arf_field_option_input_note">
                    <span class="arf_field_option_input_note_text"><?php echo addslashes(esc_html__('Maximum', 'ARForms')); ?></span>
                </span>
            </div>
	    </div>	    
        <div class="arf_field_option_content_cell" data-sort="-1" id="fieldsize">
            <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Field Size (Characters)', 'ARForms')); ?></label>
            <div class="arf_field_option_content_cell_input">
                <input type="text" id="arf_input_min_width_{arf_field_id}" class="arf_field_option_input_text arf_half_width" name="minlength" />
                <input type="text" data-id="arf_input_max_width_{arf_field_id}" class="arf_field_option_input_text arf_half_width" name="max" />
                <span class="arf_field_option_input_note">
                    <span class="arf_field_option_input_note_text arf_half_width"><?php echo addslashes(esc_html__('Minimum', 'ARForms')); ?></span>
                    <span class="arf_field_option_input_note_text arf_half_width"><?php echo addslashes(esc_html__('Maximum', 'ARForms')); ?></span>
                </span>
            </div>
        </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="customwidth">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Field Custom Width', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input id="frm_custom_width_field_{arf_field_id}_div" type="text" class="arf_field_option_input_text arfwidth80" name="field_width"  />
                    <div class="arfwidthpx"><?php echo addslashes(esc_html__('px', 'ARForms')); ?></div>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="minlength_message">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Message for minimum length', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input id="arf_min_length_message_{arf_field_id}" type="text" class="arf_field_option_input_text" name="minlength_message" />
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="placeholdertext">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Placeholder Text', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" class="arf_field_option_input_text" id="arf_placeholder_text_{arf_field_id}" name="placeholdertext" />
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="default_value">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Default Value','ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" class="arf_field_option_input_text" id="arf_default_value_text_{arf_field_id}" name="default_value" />
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="cleartextonfocus">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Clear default text on focus', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <label class="arf_js_switch_label">
                        <span><?php echo addslashes(esc_html__('No', 'ARForms')); ?>&nbsp;</span>
                    </label>
                    <span class="arf_js_switch_wrapper arf_no_transition">
                        <input class="js-switch frm_clear_field_{arf_field_id}" name="frm_clear_field" id="frm_clear_field_{arf_field_id}" onchange='arfcleardefaultvalueonfocus("{arf_field_id}", 0, 2)' value="1" type="checkbox" />
                        <span class="arf_js_switch"></span>
                    </span>
                    <label class="arf_js_switch_label">
                        <span><?php echo addslashes(esc_html__('Yes', 'ARForms')); ?>&nbsp;</span>
                    </label>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="validatedefaultvalue">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Validate default value', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <label class="arf_js_switch_label">
                        <span><?php echo addslashes(esc_html__('No', 'ARForms')); ?>&nbsp;</span>
                    </label>
                    <span class="arf_js_switch_wrapper arf_no_transition">
                        <input class="js-switch frm_default_blank_{arf_field_id}" name="frm_default_blank" id="frm_default_blank_{arf_field_id}" onchange='arfdefaultblank("{arf_field_id}", 0, 2)' value="1" type="checkbox" />
                        <span class="arf_js_switch"></span>
                    </span>
                    <label class="arf_js_switch_label"><span><?php echo addslashes(esc_html__('Yes', 'ARForms')); ?>&nbsp;</span></label>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="fielddescription">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Field description', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" id="arf_field_description_input_{arf_field_id}" class="arf_field_option_input_text" name="description" />
                </div>
            </div>

            <div class="arf_field_option_content_cell" data-sort="-1" id="ishidetitle">
                <label class="arf_field_option_content_cell_label"><?php esc_html_e('Hide title from front-end', 'ARForms') ?></label>
                <div class="arf_field_option_content_cell_input">
                    <label class="arf_js_switch_label">
                        <span><?php echo addslashes(esc_html__('NO', 'ARForms')); ?>&nbsp;</span>
                    </label>

                    <span class="arf_js_switch_wrapper arf_no_transition">
                        <input type="checkbox" class="js-switch ishidetitle_{arf_field_id} arf_switch_input is_hide_section" name="ishidetitle" id="ishidetitle_{arf_field_id}" value="0" data-leftval="0" data-rightval="1"/>
                        <span class="arf_js_switch"></span>
                    </span>
                    <label class="arf_js_switch_label">
                        <span>&nbsp;<?php echo addslashes(esc_html__('YES', 'ARForms')); ?></span>
                    </label>
                </div>
            </div>

            <div class="arf_field_option_content_cell" data-sort="-1" id="arf_prefix">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Add icon (Bootstrap style)', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <div class="arf_field_prefix_suffix_wrapper" id="arf_field_prefix_suffix_wrapper_{arf_field_id}">
                        <div class="arf_prefix_wrapper">
                            <div class="arf_prefix_suffix_container_wrapper" data-action="edit" data-field="prefix" field-id="{arf_field_id}" id="arf_edit_prefix_{arf_field_id}" data-toggle="arfmodal" href="#arf_fontawesome_modal" data-field_type="text">
                                <div class="arf_prefix_container" id="arf_select_prefix_{arf_field_id}"><?php echo addslashes(esc_html__('No icon', 'ARForms')); ?></div>
                                <div class="arf_prefix_suffix_action_container">
                                    <div class="arf_prefix_suffix_action" title="Change Icon" style="<?php echo (is_rtl()) ? 'margin-right:5px;' : 'margin-left:5px;';?>">
                                        <i class="arfa arfa-caret-down arfa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="arf_suffix_wrapper">
                            <div class="arf_prefix_suffix_container_wrapper" data-action="edit" data-field="suffix" field-id="{arf_field_id}" id="arf_edit_suffix_{arf_field_id}" data-toggle="arfmodal" href="#arf_fontawesome_modal" data-field_type="text">
                                <div class="arf_suffix_container" id="arf_select_suffix_{arf_field_id}"><?php echo addslashes(esc_html__('No icon', 'ARForms')); ?></div>
                                <div class="arf_prefix_suffix_action_container">
                                    <div class="arf_prefix_suffix_action" title="Change Icon" style="margin-left:5px;">
                                        <i class="arfa arfa-caret-down arfa-lg"></i>
                                    </div>
                                </div>  
                            </div>
                        </div>
                        <input type="hidden" name="enable_arf_prefix" id="enable_arf_prefix_{arf_field_id}" />
                        <input type="hidden" name="arf_prefix_icon" id="arf_prefix_icon_{arf_field_id}" />
                        <input type="hidden" name="enable_arf_suffix" id="enable_arf_suffix_{arf_field_id}" />
                        <input type="hidden" name="arf_suffix_icon" id="arf_suffix_icon_{arf_field_id}" />
                    </div>
                    <span class="arf_field_option_input_note">
                        <span class="arf_field_option_input_note_text arf_half_width"><?php echo addslashes(esc_html__('Prefix', 'ARForms')); ?></span>
                        <span class="arf_field_option_input_note_text arf_half_width" style="margin-left: 5px;"><?php echo addslashes(esc_html__('Suffix', 'ARForms')); ?></span>
                    </span>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="alignment">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Alignment', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <span class='arf_custom_radio_wrapper arf_field_option_radio'>
                        <input type="radio" class="arf_custom_radio" name="align" id="arf_field_align_{arf_field_id}_1" value="inline" data-id="{arf_field_id}" />
                        <svg width='18px' height='18px'>
                             <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                             <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                        </svg>
                        <label class="arf_custom_radio_label" for="arf_field_align_{arf_field_id}_1"><?php echo addslashes(esc_html__('Inline','ARForms')); ?></label>
                    </span>
                    <span class="arf_custom_radio_wrapper arf_field_option_radio">
                        <input type="radio" class="arf_custom_radio" name="align" id="arf_field_align_{arf_field_id}_2" value="block" data-id="{arf_field_id}" checked="checked" />
                        <svg width='18px' height='18px'>
                             <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                             <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                        </svg>
                        <label class="arf_custom_radio_label" for="arf_field_align_{arf_field_id}_2"><?php echo addslashes(esc_html__('1 Column','ARForms')); ?></label>
                    </span>
                    <span class="arf_custom_radio_wrapper arf_field_option_radio">
                        <input type="radio" class="arf_custom_radio" name="align" id="arf_field_align_{arf_field_id}_3" value="arf_col_2" data-id="{arf_field_id}" />
                        <svg width='18px' height='18px'>
                             <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                             <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                        </svg>
                        <label class="arf_custom_radio_label" for="arf_field_align_{arf_field_id}_3"><?php echo addslashes(esc_html__('2 Column','ARForms')); ?></label>
                    </span>
                    <span class="arf_custom_radio_wrapper arf_field_option_radio">
                        <input type="radio" class="arf_custom_radio" name="align" id="arf_field_align_{arf_field_id}_4" value="arf_col_3" data-id="{arf_field_id}" />
                        <svg width='18px' height='18px'>
                             <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                             <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                        </svg>
                        <label class="arf_custom_radio_label" for="arf_field_align_{arf_field_id}_4"><?php echo addslashes(esc_html__('3 Column','ARForms')); ?></label>
                    </span>
                    <span class="arf_custom_radio_wrapper arf_field_option_radio">
                        <input type="radio" class="arf_custom_radio" name="align" id="arf_field_align_{arf_field_id}_5" value="arf_col_4" data-id="{arf_field_id}" />
                        <svg width='18px' height='18px'>
                             <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                             <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                        </svg>
                        <label class="arf_custom_radio_label" for="arf_field_align_{arf_field_id}_5"><?php echo addslashes(esc_html__('4 Column','ARForms')); ?></label>
                    </span>
                </div>
            </div>
            <div class="arf_field_option_content_cell arf_full_width_cell" id="allowedfiletypes">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Allowed file types', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <div class="arf_radio_wrapper">
                        <div class="arf_custom_radio_div">
                            <div class="arf_custom_radio_wrapper">
                                <input type="radio" name="restrict" id="restrict_{arf_field_id}_0" value="0" checked="checked" class="arf_submit_action arf_custom_radio" onclick="arfshowconditionaldiv('restrict_box_{arf_field_id}', this.value, 1, '.')" />
                                <svg width="18px" height="18px">
                                <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                </svg>
                            </div>
                        </div>
                        <span>
                            <label for="restrict_{arf_field_id}_0"><span></span><?php echo addslashes(esc_html__('All types', 'ARForms')); ?></label>
                        </span>
                    </div>
                    <div class="arf_radio_wrapper">
                        <div class="arf_custom_radio_div">
                            <div class="arf_custom_radio_wrapper">
                                <input type="radio" name="restrict" id="restrict_{arf_field_id}_1" value="1" onclick="arfshowconditionaldiv('restrict_box_{arf_field_id}', this.value, 1, '.')" class="arf_submit_action arf_custom_radio" />
                                <svg width="18px" height="18px">
                                <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                </svg>
                            </div>
                        </div>
                        <span>
                            <label for="restrict_{arf_field_id}_1"><span></span><?php echo addslashes(esc_html__('Specific types', 'ARForms')); ?></label>
                        </span>
                    </div>
                    <div class="arf_file_upload_restrict_box restrict_box_{arf_field_id}" id="restrict_box_{arf_field_id}">
                        <div class="main_allowed_types">
                            <div class="arffieldoptionslist" style="width:100%;">
                                <div class="alignleft">
                                    <?php
                                    $mimes = get_allowed_mime_types();
                                    ksort($mimes);
                                    $mcount = count($mimes);
                                    $third = ceil($mcount / 3);
                                    $c = 0;
                                    $mimes['exe'] = '';
                                    unset($mimes['exe']);
                                    foreach ($mimes as $ext_preg => $mime) {
                                        ?>
                                        <div class="arf_file_type_restriction_item">
                                            <div class="arf_custom_checkbox_div">
                                                <div class="arf_custom_checkbox_wrapper">
                                                    <input type="checkbox" id="field_options[ftypes_{arf_field_id}][<?php echo $ext_preg ?>]" name="ftypes_<?php echo $ext_preg ?>" value="<?php echo $mime ?>" class="file_type_checkbox ftypes_{arf_field_id}_<?php echo $ext_preg ?>" />
                                                    <svg width="18px" height="18px">
                                                        <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                                        <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                                    </svg>
                                                </div>
                                            </div>
                                            <span><label for="field_options[ftypes_{arf_field_id}][<?php echo $ext_preg ?>]" class="howto"><span></span><?php echo str_replace('|', ', ', $ext_preg); ?></label></span>
                                        </div>
                                        <?php
                                        $c++;
                                        unset($ext_preg);
                                        unset($mime);
                                    }
                                    unset($c);
                                    unset($mcount);
                                    unset($third);
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            
            <div class="arf_field_option_content_cell arf_full_width_cell" id="allowedphonetype">
                <label class="arf_field_option_content_cell_label"><?php echo esc_html__('Enable country flag dropdown', 'ARForms'); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <label class="arf_js_switch_label">
                        <span><?php echo addslashes(esc_html__('NO', 'ARForms')); ?>&nbsp;</span>
                    </label>
                    <span class="arf_js_switch_wrapper arf_no_transition">
                        <input type="checkbox" class="js-switch phonetype_{arf_field_id} phone_type_switch" name="phonetype" id="phonetype_{arf_field_id}" value="0" onclick="arfshowphoneformatdiv('phoneformate_box_{arf_field_id}', this, 0, '#');" checked="checked" />
                        <span class="arf_js_switch"></span>
                    </span>
                    <label class="arf_js_switch_label">
                        <span>&nbsp;<?php echo addslashes(esc_html__('YES', 'ARForms')); ?></span>
                    </label>
                </div>

                <div class="arf_field_option_content_cell_input" id="phoneformate_box_{arf_field_id}">
                    

                    <div class="arf_file_upload_restrict_box phonetype_box_{arf_field_id}" id="phonetype_box_{arf_field_id}">
                        <div class="main_allowed_types">
                            <div class="arffieldoptionslist" style="width:100%;">
                                <div class="alignleft">
                                <?php
                                    $phonetype_arr = get_country_code();
                                    $c=0;
                                    foreach ($phonetype_arr as $key => $value) {
                                        ?>
                                        <div class="arf_file_type_restriction_item arf_phone_type_item">
                                            <div class="arf_custom_checkbox_div">
                                                <div class="arf_custom_checkbox_wrapper">
                                                    <input type="checkbox" id="field_options[phtypes_{arf_field_id}][<?php echo $value['code'] ?>]" name="phtypes_<?php echo $value['code'] ?>" value="<?php echo $value['dial_code'] ?>" class="phone_type_checkbox phtypes_<?php echo $value['code'] ?>_{arf_field_id}" />
                                                    <svg width="18px" height="18px">
                                                        <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                                        <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                                    </svg>
                                                </div>
                                            </div>
                                            <span><label for="field_options[phtypes_{arf_field_id}][<?php echo $value['code'] ?>]" class="howto"><span></span><?php echo str_replace('|', ', ', $value['name']); ?></label></span>
                                        </div>
                                        <?php
                                        $c++;
                                        unset($key);
                                        unset($value);
                                    }
                                ?>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="arf_radio_wrapper" style="padding-top: 10px;">
                        <span class="arf_check_all_label">
                            <a href="javascript:void(0)" onclick="arfselectphonetypediv('arf_phone_type_item', 1, 1, '.')"><?php echo addslashes(esc_html__('Check All', 'ARForms')); ?></a>
                        </span>
                    </div>
                    <div class="arf_radio_wrapper" style="padding-top: 10px;">
                        <span class="arf_check_all_label">
                            <a href="javascript:void(0)" onclick="arfselectphonetypediv('arf_phone_type_item', 0, 1, '.')"><?php echo addslashes(esc_html__('Uncheck All', 'ARForms')); ?></a>
                        </span>
                    </div>
                </div>
            </div>

            <div class="arf_field_option_content_cell" data-sort="-1" id="country_validation">
                <label class="arf_field_option_content_cell_label"><?php echo esc_html__('Country wise number validation','ARForms'); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <label class="arf_js_switch_label">
                        <span><?php echo addslashes(esc_html__('NO','ARForms')); ?>&nbsp;</span>
                    </label>
                    <span class="arf_js_switch_wrapper arf_no_transition">
                        <input type="checkbox" class="js-switch country_validation_{arf_field_id} country_validation" name="country_validation" id="country_validation_{arf_field_id}" value="1" checked="checked" />
                        <span class="arf_js_switch"></span>
                    </span>
                    <label class="arf_js_switch_label">
                        <span>&nbsp;<?php echo addslashes(esc_html__('YES','ARForms')); ?></span>
                    </label>
                </div>
                <input type='hidden' name='default_country' id='default_country_{arf_field_id}' value='' />
            </div>

            <div class="arf_field_option_content_cell" data-sort="-1" id="invalidmessage">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Message for invalid submission', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" name="invalid" class="arf_field_option_input_text" value="" id="invalid_message_{arf_field_id}" >
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="emailfieldsize">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Field Size (Characters)', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" data-id="arf_input_max_width_{arf_field_id}" class="arf_field_option_input_text" name="max" />
                    <span class="arf_field_option_input_note">
                        <span class="arf_field_option_input_note_text"><?php echo addslashes(esc_html__('Maximum', 'ARForms')); ?></span>
                    </span>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="confirm_email">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Confirm Email', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <label class="arf_js_switch_label">
                        <span><?php echo addslashes(esc_html__('NO', 'ARForms')); ?>&nbsp;</span>
                    </label>
                    <span class="arf_js_switch_wrapper arf_no_transition">
                        <input type="checkbox" class="js-switch confirm_email_{arf_field_id}" name="confirm_email" onchange="arfchangeconfirmemail('{arf_field_id}');" id="confirm_email_{arf_field_id}" data-field_id={arf_field_id} value="1" />
                        <span class="arf_js_switch"></span>
                    </span>
                    <label class="arf_js_switch_label">
                        <span>&nbsp;<?php echo addslashes(esc_html__('YES', 'ARForms')); ?></span>
                    </label>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="confirm_email_label">
                <label class="arf_field_option_content_cell_label"><?php echo esc_html__('Confirm Email label', 'ARForms'); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" id="confirm_email_label_{arf_field_id}" name="confirm_email_label" class="arf_field_option_input_text" />
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="invalid_confirm_email">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Message for invalid confirm email', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" id="invalid_confirm_email_{arf_field_id}" class="arf_field_option_input_text" name="invalid_confirm_email" />
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="confirm_email_placeholder" >
                <label class="arf_field_option_content_cell_label"><?php echo esc_html__('Confirm email placeholder', 'ARForms'); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" id="confirm_email_placeholder_{arf_field_id}" class="arf_field_option_input_text" name="confirm_email_placeholder" />
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="numberrange">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Number Range', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" name="minnum" id="arf_minnum_{arf_field_id}" class="arf_field_option_input_text arf_half_width" value="0" size="5">
                    <input type="text" name="maxnum" id="arf_maxnum_{arf_field_id}" class="arf_field_option_input_text arf_half_width" value="0" size="5">
                    <span class="arf_field_option_input_note">
                        <span class="arf_field_option_input_note_text arf_half_width"><?php echo addslashes(esc_html__('Minimum', 'ARForms')); ?></span>
                        <span class="arf_field_option_input_note_text arf_half_width"><?php echo addslashes(esc_html__('Maximum', 'ARForms')); ?></span>
                    </span>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="phone_validation">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Default Number format', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input id="phone_validation_{arf_field_id}" name="phone_validation" value="international" type="hidden">
                    <dl class="arf_selectbox" data-name="phone_validation" data-field-id="{arf_field_id}" data-id="phone_validation_{arf_field_id}">
                        <dt><span>1234567890</span>
                        <input value="international" style="display:none;width:153px;" class="arf_autocomplete" type="text">
                        <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                        <dd>
                            <ul style="display: none;" data-id="phone_validation_{arf_field_id}">
                                <li class="arf_selectbox_option" data-value="international" data-label="1234567890">1234567890</li>
                                <li class="arf_selectbox_option" data-value="custom_validation_1" data-label="(123)456 7890">(123)456 7890</li>
                                <li class="arf_selectbox_option" data-value="custom_validation_2" data-label="(123) 456 7890">(123) 456 7890</li>
                                <li class="arf_selectbox_option" data-value="custom_validation_3" data-label="(123)456-7890">(123)456-7890</li>
                                <li class="arf_selectbox_option" data-value="custom_validation_4" data-label="(123) 456-7890">(123) 456-7890</li>
                                <li class="arf_selectbox_option" data-value="custom_validation_5" data-label="123 456 7890">123 456 7890</li>
                                <li class="arf_selectbox_option" data-value="custom_validation_6" data-label="123 456-7890">123 456-7890</li>
                                <li class="arf_selectbox_option" data-value="custom_validation_7" data-label="123-456-7890">123-456-7890</li>
                                <li class="arf_selectbox_option" data-value="custom_validation_8" data-label="01234 123 456">01234 123 456</li>
                                <li class="arf_selectbox_option" data-value="custom_validation_9" data-label="01234 123456">01234 123456</li>
                            </ul>
                        </dd>
                    </dl>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="calendarlocalization">
                <?php
                $locales = array(
                    'en' => addslashes(esc_html__('English/Western', 'ARForms')), 'af' => addslashes(esc_html__('Afrikaans', 'ARForms')),
                    'sq' => addslashes(esc_html__('Albanian', 'ARForms')), 'ar' => addslashes(esc_html__('Arabic', 'ARForms')),
                    'hy-am' => addslashes(esc_html__('Armenian', 'ARForms')),
                    'az' => addslashes(esc_html__('Azerbaijani', 'ARForms')),
                    'eu' => addslashes(esc_html__('Basque', 'ARForms')), 'bs' => addslashes(esc_html__('Bosnian', 'ARForms')),
                    'bg' => addslashes(esc_html__('Bulgarian', 'ARForms')), 'ca' => addslashes(esc_html__('Catalan', 'ARForms')),
                    'zh-CN' => addslashes(esc_html__('Chinese Simplified', 'ARForms')),
                    'zh-TW' => addslashes(esc_html__('Chinese Traditional', 'ARForms')), 'hr' => addslashes(esc_html__('Croatian', 'ARForms')),
                    'cs' => addslashes(esc_html__('Czech', 'ARForms')), 'da' => addslashes(esc_html__('Danish', 'ARForms')),
                    'nl' => addslashes(esc_html__('Dutch', 'ARForms')), 'en-GB' => addslashes(esc_html__('English/UK', 'ARForms')),
                    'eo' => addslashes(esc_html__('Esperanto', 'ARForms')), 'et' => addslashes(esc_html__('Estonian', 'ARForms')),
                    'fo' => addslashes(esc_html__('Faroese', 'ARForms')), 'fa' => addslashes(esc_html__('Farsi/Persian', 'ARForms')),
                    'fi' => addslashes(esc_html__('Finnish', 'ARForms')), 'fr' => addslashes(esc_html__('French', 'ARForms')),
                    'fr-CH' => addslashes(esc_html__('French/Swiss', 'ARForms')), 'de' => addslashes(esc_html__('German', 'ARForms')),
                    'el' => addslashes(esc_html__('Greek', 'ARForms')), 'he' => addslashes(esc_html__('Hebrew', 'ARForms')),
                    'hu' => addslashes(esc_html__('Hungarian', 'ARForms')), 'is' => addslashes(esc_html__('Icelandic', 'ARForms')),
                    'it' => addslashes(esc_html__('Italian', 'ARForms')), 'ja' => addslashes(esc_html__('Japanese', 'ARForms')),
                    'ko' => addslashes(esc_html__('Korean', 'ARForms')), 'lv' => addslashes(esc_html__('Latvian', 'ARForms')),
                    'lt' => addslashes(esc_html__('Lithuanian', 'ARForms')),
                    'nb' => addslashes(esc_html__('Norwegian', 'ARForms')),
                    'pl' => addslashes(esc_html__('Polish', 'ARForms')),
                    'pt-BR' => addslashes(esc_html__('Portuguese/Brazilian', 'ARForms')), 'ro' => addslashes(esc_html__('Romanian', 'ARForms')),
                    'ru' => addslashes(esc_html__('Russian', 'ARForms')), 'sr' => addslashes(esc_html__('Serbian', 'ARForms')),
                    'sr-cyrl' => addslashes(esc_html__('Serbian Cyrillic', 'ARForms')), 'sk' => addslashes(esc_html__('Slovak', 'ARForms')),
                    'sl' => addslashes(esc_html__('Slovenian', 'ARForms')), 'es' => addslashes(esc_html__('Spanish', 'ARForms')),
                    'sv' => addslashes(esc_html__('Swedish', 'ARForms')), 'ta' => addslashes(esc_html__('Tamil', 'ARForms')),
                    'th' => addslashes(esc_html__('Thai', 'ARForms')),
                    'tr' => addslashes(esc_html__('Turkish', 'ARForms')),
                    'uk' => addslashes(esc_html__('Ukrainian', 'ARForms')), 'vi' => addslashes(esc_html__('Vietnamese', 'ARForms'))
                );
                ?>
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Calendar localization', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input class="frm-bulk-select-class" id="field_date_locale-{arf_field_id}" name="locale" value="en" type="hidden">
                    <dl class="arf_selectbox" data-name="locale" data-field-id="{arf_field_id}" data-id="field_date_locale-{arf_field_id}">
                        <dt><span><?php echo addslashes(esc_html__('English/Western', 'ARForms')); ?></span>
                        <input value="<?php echo addslashes(esc_html__('English/Western', 'ARForms')); ?>" style="display:none;width:128px;" class="arf_autocomplete" type="text">
                        <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                        <dd>
                            <ul style="display:none;" data-id="field_date_locale-{arf_field_id}">
                                <?php
                                foreach ($locales as $locale_key => $locale) {
                                    ?><li class="arf_selectbox_option" data-value="<?php echo $locale_key; ?>" data-label="<?php echo htmlentities($locale); ?>"><?php echo $locale; ?></li>
                                <?php } ?>
                            </ul>
                        </dd>
                    </dl>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="calendartimehideshow">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Show time picker', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <label class="arf_js_switch_label">
                        <span><?php echo addslashes(esc_html__('NO', 'ARForms')); ?>&nbsp;</span>
                    </label>
                    <span class="arf_js_switch_wrapper arf_no_transition">
                        <input type="checkbox" class="js-switch show_time_calendar_{arf_field_id}" name="show_time_calendar" id="frm_show_time_calendar_field_{arf_field_id}" value="1" onchange='arf_hide_show_time_picker_option("{arf_field_id}");' />
                        <span class="arf_js_switch"></span>
                    </span>
                    <label class="arf_js_switch_label">
                        <span>&nbsp;<?php echo addslashes(esc_html__('YES', 'ARForms')); ?></span>
                    </label>
                    <input type="hidden" name="frm_show_time_calendar_field_indicator" value="" />
                </div>
            </div>
            <div class="arf_field_option_content_cell arf_time_settings_{arf_field_id}" id="clocksetting">
                <label class="arf_field_option_content_cell_label"><?php echo esc_html__('Clock Settings', 'ARForms') ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input id="field_time_clock-{arf_field_id}" name="clock" value="" type="hidden" onchange="javascript:changeclockhours(this.value, '{arf_field_key}', '{arf_field_id}', '');">
                    <dl class="arf_selectbox arf_half_width" data-name="clock" data-field-id="{arf_field_id}" data-id="field_time_clock-{arf_field_id}">
                        <dt>
                        <span>24</span>
                        <input value="24" style="display:none;width:48px;" class="arf_autocomplete" type="text">
                        <i class="arfa arfa-caret-down arfa-lg"></i>
                        </dt>
                        <dd>
                            <ul style="display:none;" data-id="field_time_clock-{arf_field_id}">
                                <li class="arf_selectbox_option" data-value="12" data-label="12">12</li>
                                <li class="arf_selectbox_option" data-value="24" data-label="24">24</li>
                            </ul>
                        </dd>
                    </dl>
                    <input id="time_step_{arf_field_id}" name="step" value="30" type="hidden">
                    <dl class="arf_selectbox arf_half_width" data-name="step" data-field-id="{arf_field_id}" data-id="time_step_{arf_field_id}">
                        <dt>
                        <span>30</span>
                        <input value="30" style="display:none;width:48px;" class="arf_autocomplete" type="text">
                        <i class="arfa arfa-caret-down arfa-lg"></i>
                        </dt>
                        <dd>
                            <ul style="display:none;" data-id="time_step_{arf_field_id}">
                                <li class="arf_selectbox_option" data-value="1" data-label="1">1</li>
                                <li class="arf_selectbox_option" data-value="2" data-label="2">2</li>
                                <li class="arf_selectbox_option" data-value="3" data-label="3">3</li>
                                <li class="arf_selectbox_option" data-value="4" data-label="4">4</li>
                                <li class="arf_selectbox_option" data-value="5" data-label="5">5</li>
                                <li class="arf_selectbox_option" data-value="10" data-label="10">10</li>
                                <li class="arf_selectbox_option" data-value="15" data-label="15">15</li>
                                <li class="arf_selectbox_option" data-value="20" data-label="20">20</li>
                                <li class="arf_selectbox_option" data-value="25" data-label="25">25</li>
                                <li class="arf_selectbox_option" data-value="30" data-label="30">30</li>
                            </ul>
                        </dd>
                    </dl>
                    <span class="arf_field_option_input_note arf_time_field_options_note">
                        <span class="arf_field_option_input_note_text arf_half_width"><?php echo addslashes(esc_html__('Hour', 'ARForms')); ?></span>
                        <span class="arf_field_option_input_note_text arf_half_width"><?php echo addslashes(esc_html__('Minute', 'ARForms')); ?></span>
                    </span>
                </div>
            </div>
            <div class="arf_field_option_content_cell arf_full_width_cell" id="offdays">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Off days', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="hidden" name="off_days" id="arf_off_days_{arf_field_id}" class="txtstandardnew arf_date_days_val" value="" size="4"/>
                    <div class="arf_date_days_btn" day_val="0"><?php echo addslashes(esc_html__('Sunday', 'ARForms')); ?></div>
                    <div class="arf_date_days_btn" day_val="1"><?php echo addslashes(esc_html__('Monday', 'ARForms')); ?></div>
                    <div class="arf_date_days_btn" day_val="2"><?php echo addslashes(esc_html__('Tuesday', 'ARForms')); ?></div>
                    <div class="arf_date_days_btn" day_val="3"><?php echo addslashes(esc_html__('Wednesday', 'ARForms')); ?></div>
                    <div class="arf_date_days_btn" day_val="4"><?php echo addslashes(esc_html__('Thursday', 'ARForms')); ?></div>
                    <div class="arf_date_days_btn" day_val="5"><?php echo addslashes(esc_html__('Friday', 'ARForms')); ?></div>
                    <div class="arf_date_days_btn" day_val="6"><?php echo addslashes(esc_html__('Saturday', 'ARForms')); ?></div>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="daterange">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Date range', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('From', 'ARForms')); ?></label>
                    <input type="text" id="arf_start_date_{arf_field_id}" name="start_date" class="arf_field_option_input_text" value="" size="4" />
                    <span class="arf_field_option_input_note arf_date_range_option_note">
                        <label class="arf_js_switch_label">
                            <span class="arf_current_date_hide_show_label"><?php echo addslashes(esc_html__('Set Current Date', 'ARForms')); ?>:&nbsp;</span>
                            <span><?php echo addslashes(esc_html__('NO', 'ARForms')); ?>&nbsp;</span>
                        </label>
                        <span class="arf_js_switch_wrapper arf_no_transition">
                            <input type="checkbox" class="js-switch arf_show_min_current_date_{arf_field_id}" name="arf_show_min_current_date" id="frm_arf_show_min_current_date_field_{arf_field_id}" onchange='arfmincurrentdatefieldfunction("{arf_field_id}", "", "2")' value="1" />
                            <span class="arf_js_switch"></span>
                        </span>
                        <label class="arf_js_switch_label">
                            <span>&nbsp;<?php echo addslashes(esc_html__('YES', 'ARForms')); ?></span>
                        </label>
                        <span class="arf_field_option_input_note arf_time_field_options_note">
                        <span class="arf_field_option_input_note_text"><?php echo addslashes(esc_html__('Min Date e.g. 20/01/2000', 'ARForms')); ?></span>
                        </span>
                        <input type="hidden" name="frm_arf_show_min_current_date_field_indicator" value="" />
                    </span>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="daterange">
                <label class="arf_field_option_content_cell_label">&nbsp;</label>
                <div class="arf_field_option_content_cell_input">
                    <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('To', 'ARForms')); ?></label>
                    <input type="text" id="arf_end_date_{arf_field_id}" name="end_date" class="arf_field_option_input_text" value="" size="4" />
                    <span class="arf_field_option_input_note arf_date_range_option_note">
                        <label class="arf_js_switch_label">
                            <span class="arf_current_date_hide_show_label"><?php echo addslashes(esc_html__('Set Current Date', 'ARForms')); ?>:&nbsp;</span>
                            <span><?php echo addslashes(esc_html__('NO', 'ARForms')); ?>&nbsp;</span>
                        </label>
                        <span class="arf_js_switch_wrapper arf_no_transition">
                            <input type="checkbox" class="js-switch arf_show_max_current_date_{arf_field_id}" name="arf_show_max_current_date" id="frm_arf_show_max_current_date_field_{arf_field_id}" onchange='arfmaxcurrentdatefieldfunction("{arf_field_id}", "", "2")' value="1" />
                            <span class="arf_js_switch"></span>
                        </span>
                        <label class="arf_js_switch_label">
                            <span>&nbsp;<?php echo addslashes(esc_html__('YES', 'ARForms')); ?></span>
                        </label>
                        <span class="arf_field_option_input_note arf_time_field_options_note">
                        <span class="arf_field_option_input_note_text"><?php echo addslashes(esc_html__('Max Date e.g. 31/12/2020', 'ARForms')); ?></span>
                        </span>
                    </span>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="daterange"></div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="set_default_selected_date">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Set default date', 'ARForms')) ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" name="selectdefaultdate" id="set_current_date_field_{arf_field_id}" class="arf_field_option_input_text" value="" />
                    <!-- <div class="select_current_default_date_{arf_field_id} arf_current_default_date">< ?php echo addslashes(esc_html__('Current Date', 'ARForms') )?></div>-->
                    <div class="arf_field_option_input_note arf_date_range_option_note">
                        <label class="arf_js_switch_label">
                            <span class="arf_current_date_hide_show_label"><?php echo addslashes(esc_html__('Set Current Date:','ARForms')); ?>&nbsp;</span>
                            <span><?php echo addslashes(esc_html__('NO','ARForms')); ?>&nbsp;
                        </label>
                        <label class="arf_js_switch_wrapper arf_no_transition">
                            <input type="checkbox" class="js-switch arf_set_current_date currentdefaultdate_{arf_field_id}" name="currentdefaultdate" id="currentdefaultdate_{arf_field_id}" value="1" />
                            <span class="arf_js_switch"></span>
                        </label>
                        <label class="arf_js_switch_label">
                            <span><?php echo addslashes(esc_html__('Yes','ARForms')); ?></span>
                        </label>
                        <span class="arf_field_option_input_note arf_time_field_options_note">
                            <?php 
                            if($newarr['date_format']=="MM/DD/YYYY"){
                                $date = date('d/m/Y', current_time('timestamp'));
                            }else if($newarr['date_format']=="MMM D, YYYY"){
                                $date = date('M d, Y', current_time('timestamp'));
                            }else if($newarr['date_format']=="MMMM D, YYYY"){
                                $date = date('F d, Y', current_time('timestamp'));
                            } else{
                                $date = date('d/m/Y', current_time('timestamp'));    
                            }
                            
                            $date_eg = "Set Date e.g. ".$date;
                            ?>
                        <span class="arf_field_option_input_note_text"><?php echo addslashes(esc_html__($date_eg, 'ARForms')); ?></span>

                        </span>
                    </div>
                    <input type="hidden" name="currentdefaultdate" class="arf_field_option_input_text" id="currentdefaultdatestatus_{arf_field_id}" value=""/>
                </div>
            </div>            
            <div class="arf_field_option_content_cell" data-sort="-1" id="password_strength">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Password strength', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <label class="arf_js_switch_label">
                        <span><?php echo addslashes(esc_html__('NO', 'ARForms')); ?>&nbsp;</span>
                    </label>
                    <span class="arf_js_switch_wrapper arf_no_transition">
                        <input type="checkbox" class="js-switch password_strength_{arf_field_id}" name="password_strength" id="password_strength_{arf_field_id}" value="1" />
                        <span class="arf_js_switch"></span>
                    </span>
                    <label class="arf_js_switch_label"><span>&nbsp;<?php echo addslashes(esc_html__('YES', 'ARForms')); ?></span></label>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="confirm_password">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Confirm password', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <label class="arf_js_switch_label">
                        <span><?php echo addslashes(esc_html__('NO', 'ARForms')); ?>&nbsp;</span>
                    </label>
                    <span class="arf_js_switch_wrapper arf_no_transition">
                        <input type="checkbox" class="js-switch confirm_password_{arf_field_id}" name="confirm_password" data-field_id="{arf_field_id}" onchange="arfchangeconfirmpassword('{arf_field_id}');" id="confirm_password_{arf_field_id}" value="1" />
                        <span class="arf_js_switch"></span>
                    </span>
                    <label class="arf_js_switch_label">
                        <span>&nbsp;<?php echo addslashes(esc_html__('YES', 'ARForms')); ?></span>
                    </label>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="confirm_password_label">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Confirm password label', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" class="arf_field_option_input_text" name="confirm_password_label" id="confirm_password_label_{arf_field_id}" value="" />
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="invalid_password">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Message for invalid password', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" class="arf_field_option_input_text" name="invalid_password" id="invalid_password_{arf_field_id}" value="" />
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="password_placeholder">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Confirm password placeholder', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" class="arf_field_option_input_text" name="password_placeholder" id="password_placeholder_{arf_field_id}" value=""/>
                </div>
            </div>
	    
            <div class="arf_field_option_content_cell_htmlcontent arf_field_option_content_cell" data-sort="-1" id="htmlcontent">
                <div class="arf_field_option_content_cell arf_field_height20">
                    <span class="arf_js_switch_wrapper">
                        <input type="checkbox" class="js-switch" name="enable_total" id="arfenable_total_{arf_field_id}" value="1" onchange="arf_show_runnig_total('{arf_field_id}');" />                        
                        <span class="arf_js_switch"></span>
                    </span>
                    <label class="arf_js_switch_label" for="arfenable_total_{arf_field_id}">
                        <span>&nbsp;<?php echo addslashes(esc_html__('Enable Running Total', 'ARForms')); ?></span>
                    </label>
                </div>

                <div class="arf_field_option_content_cell arf_field_height20" id="htmlcontent_round_total">
                    <span class="arf_js_switch_wrapper">
                        <input type="checkbox" class="js-switch" name="round_total" id="arfround_total_{arf_field_id}" value="1" />                        
                        <span class="arf_js_switch"></span>
                    </span>
                    <label class="arf_js_switch_label" for="arfround_total_{arf_field_id}">
                        <span>&nbsp;<?php echo addslashes(esc_html__('Round Up Total', 'ARForms')); ?></span>
                    </label>
                    &nbsp;&nbsp;<img src="<?php echo esc_url(ARFIMAGESURL.'/tooltips-icon.png'); ?>" class="arf_popup_tooltip_main" data-title="<?php printf(esc_html__('If you enable this switch, total amount will be round up. %s For eg. if total amount will be 99.70, it will round up with 100','ARForms'),'<br/>'); ?>" />
                </div>                                
                
                <div class="arf_running_total_note arf_runnigtotal_block arf_field_list_total_{arf_field_id}"><?php echo esc_html__('For Running Total you need to add formula inside', 'ARForms'); ?> &lt;arftotal>&lt;/arftotal>.    <br> e.g. <b>&lt;arftotal></b><span style="color:#4786ff">( [Prodcut:123] * [Qty:125] ) + 5 </span><b>&lt;/arftotal></b>
                </div>

                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Content', 'ARForms')); ?></label>
                
                <div class="arf_field_option_content_cell_input">
                    <div class="arf_field_option_content_cell_input arfrunningtotaladdfielddiv arf_field_list_total_{arf_field_id}">
                    <button type="button" class="arf_add_field_button arfrunningtotaladdfieldbutton" onclick="add_field_fun('add_field_total_{arf_field_id}')" id="add_field_subject_but"><?php echo addslashes(esc_html__('Add Field', 'ARForms')); ?>
                    </button>
                    <div class="arf_main_field_modal">
                        <div class="arf_add_fieldmodal arf_running_total_fields" id="add_field_total_{arf_field_id}">
                            <div class="arf_modal_header">
                                <div class="arf_add_field_title">
                                    <?php echo addslashes(esc_html__('Fields', 'ARForms')); ?>
                                    <div data-dismiss="arfmodal" onclick="close_add_field_subject('add_field_total_{arf_field_id}')" class="arf_field_model_close">
                                      <svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#333333" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
                                    </div>
                                </div>
                            </div>
                            <div class="arfmodal-body_p">
                            </div>
                        </div>
                    </div>
                    <div class="arfrunningtotlaoperationdiv">
                        <div class="arf_runningtotal_operator_btn" onclick="arfaddtotalopcode('{arf_field_id}', '+');">
                            <svg width="25px" height="25px">
                                <g id="Layer_75">
                                    <path fill="#4E5462" d="M6.086,0.521H5.793c-0.362,0-0.628,0.049-0.795,0.147C4.831,0.768,4.747,0.929,4.747,1.154v3.253H1.589
                                        c-0.223,0-0.384,0.084-0.48,0.253C1.011,4.83,0.962,5.097,0.962,5.463v0.148c0,0.366,0.049,0.634,0.146,0.803
                                        c0.097,0.169,0.258,0.253,0.48,0.253h3.158v3.21c0,0.226,0.084,0.387,0.251,0.486s0.433,0.148,0.795,0.148h0.293
                                        c0.362,0,0.627-0.049,0.795-0.148c0.167-0.099,0.251-0.26,0.251-0.486v-3.21h3.199c0.224,0,0.384-0.084,0.481-0.253
                                        s0.146-0.437,0.146-0.803V5.463c0-0.366-0.049-0.634-0.146-0.803s-0.258-0.253-0.481-0.253H7.132V1.154
                                        c0-0.225-0.084-0.387-0.251-0.486C6.713,0.57,6.448,0.521,6.086,0.521L6.086,0.521z"/>
                                </g>
                            </svg>

                        </div>
                        <div class="arf_runningtotal_operator_btn" onclick="arfaddtotalopcode('{arf_field_id}', '-');">
                            <svg width="25px" height="25px" viewBox="0 -4 25 25">
                                <g id="Layer_76">
                                    <path fill="#4E5462" d="M9.206,0.612H1.798c-0.283,0-0.486,0.075-0.61,0.226c-0.123,0.15-0.186,0.389-0.186,0.715v0.132
                                        c0,0.326,0.063,0.564,0.186,0.715c0.124,0.151,0.327,0.226,0.61,0.226h7.407c0.282,0,0.486-0.075,0.609-0.226
                                        C9.939,2.25,10,2.012,10,1.686V1.554c0-0.326-0.062-0.565-0.186-0.715C9.692,0.688,9.488,0.612,9.206,0.612L9.206,0.612z"/>
                                </g>
                            </svg>
                        </div>
                        <div class="arf_runningtotal_operator_btn" onclick="arfaddtotalopcode('{arf_field_id}', '*');">
                            <svg width="25px" height="25px">
                                <g id="Layer_77">
                                    <path fill="#4E5462" d="M5.85,0.124c-0.359,0-0.617,0.079-0.774,0.236C4.918,0.517,4.84,0.775,4.84,1.134v2.762L2.246,3.054
                                        C2.021,3.009,1.863,2.987,1.774,2.987c-0.315,0-0.551,0.135-0.708,0.404C0.909,3.66,0.831,3.941,0.831,4.233
                                        c0,0.359,0.247,0.618,0.741,0.775l2.526,0.808L2.212,8.477C2.01,8.702,1.909,8.927,1.909,9.15c0,0.202,0.129,0.416,0.388,0.64
                                        c0.258,0.225,0.51,0.337,0.758,0.337c0.27,0,0.527-0.168,0.774-0.505l1.987-2.795l1.987,2.795c0.247,0.337,0.505,0.505,0.774,0.505
                                        c0.225,0,0.466-0.112,0.725-0.337c0.258-0.224,0.387-0.438,0.387-0.64c0-0.247-0.09-0.471-0.27-0.673L7.5,5.816l2.561-0.808
                                        c0.27-0.09,0.46-0.191,0.572-0.303s0.168-0.27,0.168-0.472c0-0.381-0.101-0.685-0.303-0.91c-0.202-0.224-0.427-0.336-0.674-0.336
                                        c-0.067,0-0.213,0.022-0.438,0.067L6.86,3.896V1.134c0-0.359-0.079-0.618-0.235-0.775C6.467,0.203,6.209,0.124,5.85,0.124
                                        L5.85,0.124z"/>
                                </g>
                            </svg>
                        </div>
                        <div class="arf_runningtotal_operator_btn" onclick="arfaddtotalopcode('{arf_field_id}', '/');">
                            <svg width="25px" height="25px">
                                <g id="Layer_78">
                                    <path fill="#4E5462" d="M7.592,0.067h-0.48c-0.653,0-1.06,0.2-1.22,0.6l-4.82,11.42c-0.053,0.16-0.08,0.253-0.08,0.28
                                        c0,0.213,0.287,0.32,0.86,0.32h0.479c0.653,0,1.061-0.2,1.221-0.6l4.819-11.42c0.04-0.12,0.061-0.213,0.061-0.28
                                        C8.432,0.174,8.151,0.067,7.592,0.067L7.592,0.067z"/>
                                </g>
                            </svg>
                        </div>
                        <div class="arf_runningtotal_operator_btn" onclick="arfaddtotalopcode('{arf_field_id}', '(');">
                            <svg width="25px" height="25px">
                               <g id="Layer_79">
                                    <path fill="#4E5462" d="M5.215,0.788c-0.2,0-0.38,0.08-0.54,0.238C3.7,2.067,2.956,3.196,2.442,4.412
                                        C1.929,5.627,1.672,6.88,1.672,8.17s0.257,2.54,0.771,3.75s1.258,2.337,2.232,3.379c0.16,0.158,0.34,0.238,0.54,0.238
                                        c0.214,0,0.457-0.074,0.73-0.223s0.41-0.303,0.41-0.461c0-0.109-0.033-0.208-0.1-0.297c-0.801-1.111-1.375-2.171-1.722-3.178
                                        S4.014,9.301,4.014,8.17c0-1.131,0.174-2.203,0.521-3.215S5.455,2.881,6.256,1.77c0.066-0.089,0.1-0.188,0.1-0.298
                                        c0-0.159-0.137-0.313-0.41-0.461S5.429,0.788,5.215,0.788L5.215,0.788z"/>
                                </g>
                            </svg>
                        </div>
                        <div class="arf_runningtotal_operator_btn" onclick="arfaddtotalopcode('{arf_field_id}', ')');">
                            <svg width="25px" height="25px">
                               <g id="Layer_80">
                                    <path fill="#4E5462" d="M2.547,0.031c0.215,0,0.408,0.08,0.58,0.24c1.047,1.052,1.846,2.191,2.397,3.418
                                        c0.552,1.228,0.827,2.492,0.827,3.794c0,1.302-0.275,2.564-0.827,3.786c-0.552,1.222-1.351,2.359-2.397,3.411
                                        c-0.172,0.16-0.365,0.24-0.58,0.24c-0.229,0-0.49-0.075-0.784-0.225c-0.294-0.15-0.441-0.306-0.441-0.466
                                        c0-0.11,0.036-0.21,0.108-0.3c0.859-1.122,1.476-2.191,1.848-3.208c0.373-1.017,0.56-2.096,0.56-3.238
                                        c0-1.142-0.187-2.224-0.56-3.245C2.905,3.216,2.289,2.144,1.43,1.022c-0.072-0.09-0.108-0.19-0.108-0.301
                                        c0-0.16,0.147-0.315,0.441-0.465C2.057,0.106,2.318,0.031,2.547,0.031L2.547,0.031z"/>
                                </g>
                            </svg>
                        </div>
                    </div>
                </div>
                    <textarea id="arf_field_description_{arf_field_id}" name="description" class="arf_field_option_input_textarea html_field_description"></textarea>
                    <span class="arf_field_option_input_note arfwidth50">
			             <span class="arf_field_option_input_note_text" style="color:#4786ff;margin-bottom:25px;">[ <?php echo addslashes(esc_html__('Embedded tags for youtube, map etc are supported.', 'ARForms')); ?> ]
                        </span>
                    </span>
                    <div class="arf_validateregex_fnc arf_field_list_total_{arf_field_id}">
                            <div class="arf_validate_result_btn" onclick="arfvalidateregex('{arf_field_id}');"><?php echo addslashes(esc_html__('Validate Formula', 'ARForms')); ?></div>
                            <div id="arf_validate_result_{arf_field_id}" class="arf_validate_result"></div>
                        </div>
                    <br/>   

                </div>
            </div>
	    
            <div class="arf_field_option_content_cell" data-sort="-1" id="fontfamilyoption">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Font family', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <?php $get_googlefonts_data = $arformcontroller->get_arf_google_fonts(); ?>
                    <input id="field_arf_divider_font_{arf_field_id}" name="arf_divider_font" value="Helvetica" type="hidden" >
                    <dl class="arf_selectbox" data-name="arf_divider_font" data-field-id="{arf_field_id}" data-id="field_arf_divider_font_{arf_field_id}">
                        <dt>
                        <span>Helvetica</span>
                        <input value="Helvetica" class="arf_autocomplete" type="text">
                        <i class="arfa arfa-caret-down arfa-lg"></i>
                        </dt>
                        <dd>
                            <ul style="display: none;" data-id="field_arf_divider_font_{arf_field_id}">
                                <ol class="arp_selectbox_group_label"><?php echo addslashes(esc_html__('Default Fonts', 'ARForms')); ?></ol>
                                <li class="arf_selectbox_option" data-value="Arial" data-label="Arial">Arial</li>
                                <li class="arf_selectbox_option" data-value="Helvetica" data-label="Helvetica">Helvetica</li>
                                <li class="arf_selectbox_option" data-value="sans-serif" data-label="sans-serif">sans-serif</li>
                                <li class="arf_selectbox_option" data-value="Lucida Grande" data-label="Lucida Grande">Lucida Grande</li>
                                <li class="arf_selectbox_option" data-value="Lucida Sans Unicode" data-label="Lucida Sans Unicode">Lucida Sans Unicode</li>
                                <li class="arf_selectbox_option" data-value="Tahoma" data-label="Tahoma">Tahoma</li>
                                <li class="arf_selectbox_option" data-value="Times New Roman" data-label="Times New Roman">Times New Roman</li>
                                <li class="arf_selectbox_option" data-value="Courier New" data-label="Courier New">Courier New</li>
                                <li class="arf_selectbox_option" data-value="Verdana" data-label="Verdana">Verdana</li>
                                <li class="arf_selectbox_option" data-value="Geneva" data-label="Geneva">Geneva</li>
                                <li class="arf_selectbox_option" data-value="Courier" data-label="Courier">Courier</li>
                                <li class="arf_selectbox_option" data-value="Monospace" data-label="Monospace">Monospace</li>
                                <li class="arf_selectbox_option" data-value="Times" data-label="Times">Times</li>
                                <ol class="arp_selectbox_group_label"><?php echo addslashes(esc_html__('Google Fonts', 'ARForms')); ?></ol>
                                <?php
                                if (count($get_googlefonts_data) > 0) {
                                    foreach ($get_googlefonts_data as $goglefontsfamily) {
                                        echo "<li class='arf_selectbox_option' data-value='" . $goglefontsfamily . "' data-label='" . $goglefontsfamily . "'>" . $goglefontsfamily . "</li>";
                                    }
                                }
                                ?>
                            </ul>
                        </dd>
                    </dl>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="fontsizeoption">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Font size', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input id="field_arf_divider_font_size_{arf_field_id}" name="arf_divider_font_size" value="16" type="hidden">
                    <dl class="arf_selectbox" data-name="arf_divider_font_size" data-field-id="{arf_field_id}" data-id="field_arf_divider_font_size_{arf_field_id}">
                        <dt>
                        <span>16</span>
                        <input value="16" style="display:none;" class="arf_autocomplete" type="text">
                        <i class="arfa arfa-caret-down arfa-lg"></i>
                        </dt>
                        <dd>
                            <ul style="display: none;" data-id="field_arf_divider_font_size_{arf_field_id}">
                                <?php for ($i = 8; $i <= 20; $i ++) { ?>
                                    <li class="arf_selectbox_option" data-value="<?php echo $i ?>" data-label="<?php echo htmlentities($i) ?>"><?php echo addslashes(esc_html__($i, 'ARForms')); ?></li>
                                <?php } ?>
                                <?php for ($i = 22; $i <= 28; $i = $i + 2) { ?>
                                    <li class="arf_selectbox_option" data-value="<?php echo $i ?>" data-label="<?php echo htmlentities($i) ?>"><?php echo addslashes(esc_html__($i, 'ARForms')); ?></li>
                                <?php } ?>
                                <?php for ($i = 32; $i <= 40; $i = $i + 4) { ?>
                                    <li class="arf_selectbox_option" data-value="<?php echo $i ?>" data-label="<?php echo htmlentities($i) ?>"><?php echo addslashes(esc_html__($i, 'ARForms')); ?></li>
                                <?php } ?>
                            </ul>
                        </dd>
                    </dl>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="fontstyleoption">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Font style', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input id="field_arf_divider_font_style_{arf_field_id}" name="arf_divider_font_style" value="bold" type="hidden">
                    <dl class="arf_selectbox" data-name="arf_divider_font_style" data-field-id="{arf_field_id}" data-id="field_arf_divider_font_style_{arf_field_id}">
                        <dt>
                        <span>bold</span>
                        <input value="bold" style="display:none;" class="arf_autocomplete" type="text">
                        <i class="arfa arfa-caret-down arfa-lg"></i>
                        </dt>
                        <dd>
                            <ul style="display: none;" data-id="field_arf_divider_font_style_{arf_field_id}">
                                <li class="arf_selectbox_option" data-value="normal" data-label="<?php echo addslashes(esc_html__('normal', 'ARForms')); ?>"><?php echo addslashes(esc_html__('normal', 'ARForms')); ?></li>
                                <li class="arf_selectbox_option" data-value="bold" data-label="<?php echo addslashes(esc_html__('bold', 'ARForms')) ?>"><?php echo addslashes(esc_html__('bold', 'ARForms')) ?></li>
                                <li class="arf_selectbox_option" data-value="italic" data-label="<?php echo addslashes(esc_html__('italic', 'ARForms')) ?>"><?php echo addslashes(esc_html__('italic', 'ARForms')) ?></li>
                            </ul>
                        </dd>
                    </dl>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="bgcoloroption">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Background color', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <div class="arf_clr_disable arf_field_option_color_opt" id="arf_divider_bg_color_disabled_{arf_field_id}" style="display:none;">
                        <div class="arf_coloroption_sub arf_colpick_disable">
                            <div class="arf_coloroption" data-fid="arf_divider_bg_color_{arf_field_id}" style="background:#ffffff;" ></div>
                            <div class="arf_coloroption_subarrow_bg">
                                <div class="arf_coloroption_subarrow"></div>
                            </div>
                        </div>
                    </div>
                    <div class="arf_field_option_color_opt"  data-cls="arf_clr_disable" style="display:inline-block;">
                        <div class="arf_coloroption_sub arf_colpick_disable">
                            <div class="arf_coloroption jscolor" data-fid="arf_divider_bg_color_{arf_field_id}" style="background:#ffffff;" data-jscolor='{hash:true}' jscolor-hash='true' jscolor-valueelement='arf_divider_bg_color_{arf_field_id}' jscolor-onfinechange="arf_update_color(this,'arf_divider_bg_color_{arf_field_id}')"></div>
                            <div class="arf_coloroption_subarrow_bg">
                                <div class="arf_coloroption_subarrow"></div>
                            </div>
                        </div>
                        <input type="hidden" name="arf_divider_bg_color" id="arf_divider_bg_color_{arf_field_id}" class="hex txtstandardnew" value=""/>
                    </div>
                    <div class="arf_field_option_color_opt">
                        <div class="arf_custom_checkbox_div">
                            <div class="arf_custom_checkbox_wrapper">
                                <input type="checkbox" onchange="changearfsectionbgtype('{arf_field_id}', this.checked);" data-id='arf_divider_inherit_bg' value="1" id="arf_divider_inherit_bg_{arf_field_id}" name="arf_divider_inherit_bg"/>
                                <svg width="18px" height="18px">
                                <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                </svg>
                            </div>
                        </div>
                        <span><label for="arf_divider_inherit_bg_{arf_field_id}"><?php echo addslashes(esc_html__('Inherit', 'ARForms')) ?></label></span>
                        )
                    </div>
                </div>
            </div>
            <div class="arf_field_option_content_cell pg_break_div_{arf_field_id}" id="firstpagelabel">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('First page label', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" id="field_options[first_page_label_{arf_field_id}]" name="first_page_label" class="arf_field_option_input_text" value="Step1" />
                    <input type="hidden" id="page_break_first_use_{arf_field_id}" name="page_break_first_use" value="1" />
                    <input type="hidden" name="page_number_{arf_field_id}" class="pagebreak_field" value="{arf_field_id}" id="page_number_{arf_field_id}" data-field-id="{arf_field_id}" />
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="secondpagelabel">
                <label class="arf_field_option_content_cell_label" id="arf_page_break_label_{arf_field_id}"><?php echo addslashes(esc_html__('Second page label', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" id="field_options[second_page_label_{arf_field_id}]" name="second_page_label" class="arf_field_option_input_text arfnextpagetitle" value="<?php echo addslashes(esc_html__('Step 2', 'ARForms')); ?>"/>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="prevbtntext">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Previous button text', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" id='first_pg_break_pre' onblur='save_pg_break_pre_btn_val()' name="pre_page_title" class="arf_field_option_input_text" value="<?php echo addslashes(esc_html__('Previous', 'ARForms')); ?>" />
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="nextbtntext">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Next button text', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" id='first_pg_break_next' onblur='save_pg_break_next_btn_val()' name="next_page_title" class="arf_field_option_input_text" value="<?php echo addslashes(esc_html__('Next', 'ARForms')); ?>" />
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="pagebreakstyle">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Multistep Style', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input id="page_break_type_{arf_field_id}" class="page_break_select" name="page_break_type" value="wizard" type="hidden" data-field-id="{arf_field_id}"/>
                    <dl class="arf_selectbox" data-name="page_break_type" data-field-id="{arf_field_id}" data-id="page_break_type_{arf_field_id}">
                        <dt class="page_break_type_{arf_field_id}">
                        <span><?php echo addslashes(esc_html__('Wizard', 'ARForms')).'('.addslashes(esc_html__('Tab','ARForms')).')'; ?></span>
                        <i class="arfa arfa-caret-down arfa-lg"></i>
                        </dt>
                        <dd>
                            <ul style="display: none;" data-id="page_break_type_{arf_field_id}">
                                <li id="field_page_break_type_{arf_field_id}_wizard" class="arf_selectbox_option" data-value="wizard" data-label="<?php echo addslashes(esc_html__('Wizard', 'ARForms')).' ('.addslashes(esc_html__('Tab','ARForms')).')'; ?>"><?php echo addslashes(esc_html__('Wizard', 'ARForms')).' ('.addslashes(esc_html__('Tab','ARForms')).')'; ?></li>
                                <li id="field_page_break_type_{arf_field_id}_survey" class="arf_selectbox_option" data-value="survey" data-label="<?php echo addslashes(esc_html__('Survey', 'ARForms')).' ('.addslashes(esc_html__('Progressbar','ARForms')).')'; ?>"><?php echo addslashes(esc_html__('Survey', 'ARForms')).' ('.addslashes(esc_html__('Progressbar','ARForms')).')'; ?></li>
                            </ul>
                        </dd>
                    </dl>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="pagebreaktabsbar">
                <label class="arf_field_option_content_cell_label"><?php echo esc_html__('Hide Survey Bar / Tab Belt', 'ARForms'); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <label class="arf_js_switch_label">
                        <span><?php echo addslashes(esc_html__('NO', 'ARForms')); ?>&nbsp;</span>
                    </label>
                    <span class="arf_js_switch_wrapper arf_no_transition">
                        <input type="checkbox" class="js-switch pagebreaktabsbar_{arf_field_id}" name="pagebreaktabsbar" id="pagebreaktabsbar_{arf_field_id}" data-field_id={arf_field_id} value="1" />
                        <span class="arf_js_switch"></span>
                    </span>
                    <label class="arf_js_switch_label">
                        <span>&nbsp;<?php echo addslashes(esc_html__('YES', 'ARForms')); ?></span>
                    </label>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="pagebreakstyle_position">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Multistep Position', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input id="page_break_type_possition_{arf_field_id}" class="page_break_select_possition" name="page_break_type_possition" value="top" type="hidden" data-field-id="{arf_field_id}"/>
                    <dl class="arf_selectbox" data-name="page_break_type_possition" data-field-id="{arf_field_id}" data-id="page_break_type_possition_{arf_field_id}">
                        <dt class="page_break_type_possition_{arf_field_id}">
                        <span><?php echo addslashes(esc_html__('Top', 'ARForms')); ?></span>
                        <i class="arfa arfa-caret-down arfa-lg"></i>
                        </dt>
                        <dd>
                            <ul style="display: none;" data-id="page_break_type_possition_{arf_field_id}">
                                <li id="field_page_break_type_possition_{arf_field_id}_top" class="arf_selectbox_option" data-value="top" data-label="<?php echo addslashes(esc_html__('Top', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Top', 'ARForms')); ?></li>
                                <li id="field_page_break_type_possition_{arf_field_id}_bottom" class="arf_selectbox_option" data-value="bottom" data-label="<?php echo addslashes(esc_html__('Bottom', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Bottom', 'ARForms')); ?></li>
                            </ul>
                        </dd>
                    </dl>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="starrange">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Range', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" class="arf_field_option_input_text" id="starrange_{arf_field_id}" name="maxnum" value="5"/>
                    <input type="hidden" class="txtstandardnew" name="minnum" value="1" />
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="starcolor">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Star color', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <div class="arf_field_option_color_opt">
                        <div class="arf_coloroption_sub arf_colpick_disable">
                            <div class="arf_coloroption jscolor" data-fid="star_rating_color_{arf_field_id}" style="background:#ffeb3d;" data-jscolor='{hash:true}' jscolor-hash='true' jscolor-valueelement='star_rating_color_{arf_field_id}' jscolor-onfinechange="arf_update_color(this,'star_rating_color_{arf_field_id}')"></div>
                            <div class="arf_coloroption_subarrow_bg">
                                <div class="arf_coloroption_subarrow"></div>
                            </div>
                        </div>
                        <input type="hidden" name="star_rating_color" id="star_rating_color_{arf_field_id}" class="hex txtstandardnew" value="#ffeb3d" />
                    </div>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="starsize">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Size', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input id="field_star_size_{arf_field_id}" name="star_size" value="14" type="hidden" onchange="ShowCurrentStar('{arf_field_id}');">
                    <dl class="arf_selectbox" data-name="star_size" data-field-id="{arf_field_id}" data-id="field_star_size_{arf_field_id}" style="width:70%;">
                        <dt class="field_star_size_{arf_field_id}_dt" style="height:30px;line-height: 29px;top:0px;">
                        <span>14px</span>
                        <input value="14" style="display:none;" class="arf_autocomplete" type="text" />
                        <i class="arfa arfa-caret-down arfa-lg"></i>
                        </dt>
                        <dd>
                            <ul style="display: none;" data-id="field_star_size_{arf_field_id}">
                                <?php
                                for ($i = 8; $i <= 20; $i++) {
                                    ?>
                                    <li class="arf_selectbox_option" data-value="<?php echo $i; ?>" data-label="<?php echo htmlentities($i).'px'; ?>"><?php echo $i . 'px'; ?></li>
                                    <?php
                                }
                                for ($n = 22; $n <= 40;) {
                                    ?>
                                    <li class="arf_selectbox_option" data-value="<?php echo $n; ?>" data-label="<?php echo htmlentities($n).'px'; ?>"><?php echo $n . 'px'; ?></li>
                                    <?php
                                    $n += 2;
                                }
                                ?>
                            </ul>
                        </dd>
                    </dl>
                    <div id="showlivestar_{arf_field_id}" style="float:left;padding-left:10px;margin-top:2px;margin-left:15px;">
                        <svg viewBox="0 0 26 26" width="24" height="23"><g><?php echo ARF_STAR_RATING_ICON; ?></g></svg>
                    </div>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="likebtntitle">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Like title', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" class="arf_field_option_input_text" name="lbllike" value="<?php echo addslashes(esc_html__('Like', 'ARForms')); ?>"  style="width:180px;" />
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="dislikebtntitle">
                <label class="arf_field_option_content_cell_label"><?php echo esc_html__('Dislike title', 'ARForms'); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" class="arf_field_option_input_text" name="lbldislike" value="<?php addslashes(esc_html__('Dislike', 'ARForms')); ?>"  style="width:160px;" id="dislike_btntitle_{arf_field_id}"/>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="likebtnactivecolor">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Active Color', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <div class="arf_field_option_color_opt">
                        <div class="arf_coloroption_sub arf_colpick_disable">
                            <div class="arf_coloroption jscolor" data-fid="like_bg_color_{arf_field_id}" style="background:#4786ff;" data-jscolor='{hash:true}' jscolor-hash='true' jscolor-valueelement='like_bg_color_{arf_field_id}' jscolor-onfinechange="arf_update_color(this,'like_bg_color_{arf_field_id}')"></div>
                            <div class="arf_coloroption_subarrow_bg">
                                <div class="arf_coloroption_subarrow"></div>
                            </div>
                        </div>
                        <input type="hidden" name="like_bg_color" id="like_bg_color_{arf_field_id}" class="hex txtstandardnew" value="#4786ff" style="width:90px;" />
                    </div>
                    <div class="arf_field_option_color_opt">
                        <div class="arf_coloroption_sub arf_colpick_disable">
                            <div class="arf_coloroption jscolor" data-fid="dislike_bg_color_{arf_field_id}" style="background:#ec3838;" data-jscolor='{hash:true}' jscolor-hash='true' jscolor-valueelement='dislike_bg_color_{arf_field_id}' jscolor-onfinechange="arf_update_color(this,'dislike_bg_color_{arf_field_id}')"></div>
                            <div class="arf_coloroption_subarrow_bg">
                                <div class="arf_coloroption_subarrow"></div>
                            </div>
                        </div>
                        <input type="hidden" name="dislike_bg_color" id="dislike_bg_color_{arf_field_id}" class="hex txtstandardnew" value="#ec3838" style="width:90px;">
                    </div>
                    <span class="arf_field_option_input_note">
                        <span class="arf_field_option_input_note_text arf_half_width arfwidth30"><?php echo addslashes(esc_html__('Like', 'ARForms')); ?></span>
                        <span class="arf_field_option_input_note_text arf_half_width arfwidth30"><?php echo addslashes(esc_html__('Dislike', 'ARForms')); ?></span>
                    </span>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="handletype">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Handle type', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input id="slider_handle_{arf_field_id}" name="slider_handle" value="round" type="hidden" onchange='arf_change_slider_class("{arf_field_id}");' >
                    <dl class="arf_selectbox" data-name="slider_handle" data-field-id="{arf_field_id}" data-id="slider_handle_{arf_field_id}">
                        <dt class="slider_handle_{arf_field_id}_dt">
                        <span><?php echo addslashes(esc_html__('Round', 'ARForms')); ?></span>
                        <input value="round" style="display:none;" class="arf_autocomplete" type="text">
                        <i class="arfa arfa-caret-down arfa-lg"></i>
                        </dt>
                        <dd>
                            <ul style="display: none;" data-id="slider_handle_{arf_field_id}">
                                <li class="arf_selectbox_option" data-value="round" data-label="<?php echo addslashes(esc_html__('Round', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Round', 'ARForms')); ?></li>
                                <li class="arf_selectbox_option" data-value="square" data-label="<?php echo addslashes(esc_html__('Square', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Square', 'ARForms')); ?></li>
                                <li class="arf_selectbox_option" data-value="triangle" data-label="<?php echo addslashes(esc_html__('Triangle', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Triangle', 'ARForms')); ?></li>
                            </ul>
                        </dd>
                    </dl>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="numberofsteps">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Steps', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" class="arf_field_option_input_text" id="slider_step_{arf_field_id}" name="slider_step" value="1" />
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="defaultvalue">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Default Value', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" class="arf_field_option_input_text" id="slider_value_{arf_field_id}" name="slider_value" value="1" />
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="arf_range_selector">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Range selector', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <label class="arf_js_switch_label">
                        <span><?php echo addslashes(esc_html__('NO', 'ARForms')); ?>&nbsp;</span>
                    </label>
                    <span class="arf_js_switch_wrapper arf_no_transition">
                        <input type="checkbox" onchange="arf_change_range_selector_slider('{arf_field_id}');" class="js-switch arf_slider_{arf_field_id} arf_range_selector_{arf_field_id}" name="arf_range_selector"  id="arf_range_selector_{arf_field_id}" value="1" />
                        <span class="arf_js_switch"></span>
                    </span>
                    <label class="arf_js_switch_label">
                        <span>&nbsp;<?php echo addslashes(esc_html__('YES', 'ARForms')); ?></span>
                    </label>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="arf_range_defaultvalue">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Range default value', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" name="arf_range_minnum" id="arf_range_minnum_{arf_field_id}" class="arf_field_option_input_text arf_half_width" value="1" size="5" />
                    <input type="text" name="arf_range_maxnum" id="arf_range_maxnum_{arf_field_id}" class="arf_field_option_input_text arf_half_width" value="10" size="5" />
                    <span class="arf_field_option_input_note">
                        <span class="arf_field_option_input_note_text arf_half_width"><?php echo addslashes(esc_html__('Minimum', 'ARForms')); ?></span>
                        <span class="arf_field_option_input_note_text arf_half_width"><?php echo addslashes(esc_html__('Maximum', 'ARForms')); ?></span>
                    </span>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="trackbgcolor">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Track BG color', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <div class="arf_field_option_color_opt">
                        <div class="arf_coloroption_sub arf_colpick_disable">
                            <div class="arf_coloroption jscolor" data-fid="slider_bg_color_{arf_field_id}" style="background:#d1dee5;" data-jscolor='{hash:true}' jscolor-hash='true' jscolor-valueelement='slider_bg_color_{arf_field_id}' jscolor-onfinechange="arf_update_color(this,'slider_bg_color_{arf_field_id}')"></div>
                            <div class="arf_coloroption_subarrow_bg">
                                <div class="arf_coloroption_subarrow"></div>
                            </div>
                        </div>
                        <input type="hidden" name="slider_bg_color" id="slider_bg_color_{arf_field_id}" class="hex txtstandardnew" value="#d1dee5" style="width:90px;">
                    </div>
                    <div class="arf_field_option_color_opt">
                        <div class="arf_coloroption_sub arf_colpick_disable">
                            <div class="arf_coloroption jscolor" data-fid="slider_bg_color2_{arf_field_id}" style="background:#bcc7cd;" data-jscolor='{hash:true}' jscolor-hash='true' jscolor-valueelement='slider_bg_color2_{arf_field_id}' jscolor-onfinechange="arf_update_color(this,'slider_bg_color2_{arf_field_id}')"></div>
                            <div class="arf_coloroption_subarrow_bg">
                                <div class="arf_coloroption_subarrow"></div>
                            </div>
                        </div>
                        <input type="hidden" name="slider_bg_color2" id="slider_bg_color2_{arf_field_id}" class="hex txtstandardnew" value="#bcc7cd" style="width:90px;">
                    </div>
                    <span class="arf_field_option_input_note">
                        <span class="arf_field_option_input_note_text arf_half_width arfwidth30"><?php echo addslashes(esc_html__('Left side', 'ARForms')); ?></span>
                        <span class="arf_field_option_input_note_text arf_half_width arfwidth30"><?php echo addslashes(esc_html__('Right side', 'ARForms')); ?></span>
                    </span>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="handlecolor">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Handle color', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <div class="arf_field_option_color_opt">
                        <div class="arf_coloroption_sub arf_colpick_disable">
                            <div class="arf_coloroption jscolor" data-fid="slider_handle_color_{arf_field_id}" style="background:#0480BE;" data-jscolor='{hash:true}' jscolor-hash='true' jscolor-valueelement='slider_handle_color_{arf_field_id}' jscolor-onfinechange="arf_update_color(this,'slider_handle_color_{arf_field_id}')"></div>
                            <div class="arf_coloroption_subarrow_bg">
                                <div class="arf_coloroption_subarrow"></div>
                            </div>
                        </div>
                        <input type="hidden" class="hex txtstandardnew" name="slider_handle_color" id="slider_handle_color_{arf_field_id}" value="#0480BE"/>
                    </div>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="colorpicker_type">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Colorpicker type', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input id="arf_field_colorpicker_type_{arf_field_id}" name="colorpicker_type" value="advanced" type="hidden" >
                    <dl class="arf_selectbox" data-name="colorpicker_type" data-field-id="{arf_field_id}" data-id="arf_field_colorpicker_type_{arf_field_id}">
                        <dt class="arf_field_colorpicker_type_{arf_field_id}_dt">
                        <span>Advanced</span>
                        <input value="advanced" style="display:none;width:148px;" class="arf_autocomplete" type="text">
                        <i class="arfa arfa-caret-down arfa-lg"></i>
                        </dt>
                        <dd>
                            <ul style="display: none;" data-id="arf_field_colorpicker_type_{arf_field_id}">
                                <li class="arf_selectbox_option" data-value="advanced" data-label="<?php echo addslashes(esc_html__('Advanced', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Advanced', 'ARForms')); ?></li>
                                <li class="arf_selectbox_option" data-value="basic" data-label="<?php echo addslashes(esc_html__('Basic', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Basic', 'ARForms')); ?></li>
                            </ul>
                        </dd>
                    </dl>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="defaultcolor">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Default value', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" class="arf_field_option_input_text" name="placeholdertext" id="placeholdertext_{arf_field_id}" onkeyup="arfchangeplaceholder('{arf_field_id}');" value="" />
                </div>
            </div>
            <div class="arf_field_option_content_cell arf_full_width_cell" id="image_url">
                <label class="arf_field_option_content_cell_label"><?php echo esc_html__('Image URL', 'ARForms'); ?><div class="arf_imageloader arf_imagecontrol_loader" id="ajax_form_loader"></div></label>
                <div class="arf_field_option_content_cell_input">
                    <div style="float:left;width:100%;">
                        <input type="text" class="arf_field_option_input_text_with_button inplace_field" name="image_url" id="arfimage_url_{arf_field_id}" value="" />
                        <div data-insert="image" data-id="{arf_field_id}" class="arf_modal_add_file_btn" ><input type="file" class="original arf_image_control_add_image_button" data-val="arf_img_control_image_control_{arf_field_id}" id="arf_imagecontol_url_{arf_field_id}" />&nbsp;&nbsp;<?php echo addslashes(esc_html__('Add File', 'ARForms')); ?></div>
                        <input type="hidden" id="arf_image_control_name" />
                    </div>
                    
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="image_horizontal_center">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Horizontal center', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <div class="arf_custom_radio_wrapper arf_field_option_radio">
                        <input type="radio" class="arf_custom_radio" name="image_center" id="arfimage_center_{arf_field_id}_0" value="No" />
                        <svg width="18px" height="18px">
                        <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                        <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                        </svg>
                        <label class="arf_custom_radio_label" for="arfimage_center_{arf_field_id}_0"><?php echo addslashes(esc_html__('No','ARForms')); ?></label>
                    </div>
                    <div class="arf_custom_radio_wrapper arf_field_option_radio">
                        <input type="radio" class="arf_custom_radio" name="image_center" id="arfimage_center_{arf_field_id}_1" value="Yes" />
                        <svg width="18px" height="18px">
                        <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                        <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                        </svg>
                        <label class="arf_custom_radio_label" for="arfimage_center_{arf_field_id}_1"><?php echo addslashes(esc_html__('Yes','ARForms')); ?></label>
                    </div>
                </div>
            </div>
            <div class="arf_field_option_content_cell arf_imagecontrol_field_position_opt" id="image_horizontal_center">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Count position from','ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <div class="arf_custom_radio_wrapper arf_field_option_radio">
                        <input type="radio" class="arf_custom_radio" name="image_position_from" id="arfimage_position_{arf_field_id}_top_left" value="top_left" />
                        <svg width="18px" height="18px">
                        <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                        <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                        </svg>
                        <label class="arf_custom_radio_label" for="arfimage_position_{arf_field_id}_top_left"><?php echo addslashes(esc_html__('Top Left','ARForms')); ?></label>
                    </div>
                    <div class="arf_custom_radio_wrapper arf_field_option_radio">
                        <input type="radio" class="arf_custom_radio" name="image_position_from" id="arfimage_position_{arf_field_id}_top_right" value="top_right" />
                        <svg width="18px" height="18px">
                        <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                        <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                        </svg>
                        <label class="arf_custom_radio_label" for="arfimage_position_{arf_field_id}_top_right"><?php echo addslashes(esc_html__('Top Right','ARForms')); ?></label>
                    </div>
                    <div class="arf_custom_radio_wrapper arf_field_option_radio">
                        <input type="radio" class="arf_custom_radio" name="image_position_from" id="arfimage_position_{arf_field_id}_bottom_left" value="bottom_left" />
                        <svg width="18px" height="18px">
                        <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                        <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                        </svg>
                        <label class="arf_custom_radio_label" for="arfimage_position_{arf_field_id}_bottom_left"><?php echo addslashes(esc_html__('Bottom Left','ARForms')); ?></label>
                    </div>
                    <div class="arf_custom_radio_wrapper arf_field_option_radio">
                        <input type="radio" class="arf_custom_radio" name="image_position_from" id="arfimage_position_{arf_field_id}_bottom_right" value="bottom_right" />
                        <svg width="18px" height="18px">
                        <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                        <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                        </svg>
                        <label class="arf_custom_radio_label" for="arfimage_position_{arf_field_id}_bottom_right"><?php echo addslashes(esc_html__('Bottom Right','ARForms')); ?></label>
                    </div>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="image_left">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('X', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" class="arf_field_option_input_text" name="image_left" id="arfimage_left_{arf_field_id}" value="" />
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="image_top">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Y', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" class="arf_field_option_input_text" name="image_top" id="arfimage_top_{arf_field_id}" value="" />
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="image_height">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Height', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" class="arf_field_option_input_text" name="image_height" id="arfimage_height_{arf_field_id}" value=""/>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="image_width">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Width', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input type="text" class="arf_field_option_input_text" name="image_width" id="arfimage_width_{arf_field_id}" value="" />
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="arf_input_custom_validation">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Validation', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input id="single_custom_validation_{arf_field_id}" name="single_custom_validation" value="custom_validation_none" type="hidden" onchange="Showvalidationmessage('{arf_field_id}');">
                    <dl class="arf_selectbox" data-name="single_custom_validation" data-field-id="{arf_field_id}" data-id="single_custom_validation_{arf_field_id}">
                        <dt>
                        <span style="width: 90%;"><?php echo addslashes(esc_html__('None', 'ARForms')); ?></span>
                        <input value="custom_validation_none" style="display:none;" class="arf_autocomplete" type="hidden">
                        <i class="arfa arfa-caret-down arfa-lg"></i>
                        </dt>
                        <dd>
                            <ul style="display: none;" data-id="single_custom_validation_{arf_field_id}">
                                <li class="arf_selectbox_option" data-value="custom_validation_none" data-label="<?php echo addslashes(esc_html__('None', 'ARForms')); ?>"><?php echo addslashes(esc_html__('None', 'ARForms')); ?></li>
                                <li class="arf_selectbox_option" data-value="custom_validation_alpha" data-label="<?php echo addslashes(esc_html__('Only Alphabets', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Only Alphabets', 'ARForms')); ?></li>
                                <li class="arf_selectbox_option" data-value="custom_validation_number" data-label="<?php echo addslashes(esc_html__('Only Numbers', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Only Numbers', 'ARForms')); ?></li>
                                <li class="arf_selectbox_option" data-value="custom_validation_alphanumber" data-label="<?php echo addslashes(esc_html__('Only Alphabets & Numbers', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Only Alphabets & Numbers', 'ARForms')); ?></li>
                                <li class="arf_selectbox_option" data-value="custom_validation_regex" data-label="<?php echo addslashes(esc_html__('Regular Expression (Custom)', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Regular Expression (Custom)', 'ARForms')); ?></li>
                            </ul>
                        </dd>
                    </dl>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="arf_regular_expression_msg">
                <label class="arf_field_option_content_cell_label"><?php echo esc_html__('Message for regular expression', 'ARForms'); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <input id="arf_regular_expression_msg_{arf_field_id}" type="text" name="arf_regular_expression_msg" value="<?php echo addslashes(esc_html__('Entered value is invalid', 'ARForms')); ?>" class="arf_field_option_input_text txtstandardnew arfblank_txt" disabled="disabled"/>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="arf_regular_expression">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Regular expression', 'ARForms')); ?>
                    <span class="arf_popup_tooltip_main arfhelptip tipso_style" data-title="<strong>Sample RegExp</strong><br><div style='text-align:left'><strong>[0-9]{6}</strong>: Allow only digits upto 6 digits. e.g. : pincode<br><strong>[a-zA-Z0-9]{8,16}</strong> : Allow alpha numeric characters and length must be between 8 to 16 characters<br><strong>\([\d]{3}\)\-[\d]{7} </strong>: Allow phone number like (123)-1234567</div>">
                    <img src="<?php echo ARFIMAGESURL ?>/tooltips-icon.png" alt="?" style="position: absolute;"/></span>
                </label>
                <div class="arf_field_option_content_cell_input">
                    <input id="arf_regular_expression_{arf_field_id}" type="text" name="arf_regular_expression" value="" class="arf_field_option_input_text txtstandardnew arfblank_txt" disabled="disabled" />
                    <span class="arf_pre_regex arf_pre_regex_{arf_field_id} arf_pre_regex_disable" data-field-id="{arf_field_id}" data-pattern="(http(s)?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?"><?php echo addslashes(esc_html__("URL", 'ARForms')); ?></span>
                    <span class="arf_pre_regex arf_pre_regex_{arf_field_id} arf_pre_regex_disable" data-field-id="{arf_field_id}" data-pattern="(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)"><?php echo addslashes(esc_html__("IP Address", 'ARForms')); ?></span>
                    <span class="arf_pre_regex arf_pre_regex_{arf_field_id} arf_pre_regex_disable" data-field-id="{arf_field_id}" data-pattern="[a-z0-9_-]{3,16}"><?php echo addslashes(esc_html__("User Name", 'ARForms')); ?></span>
                    <span class="arf_pre_regex arf_pre_regex_{arf_field_id} arf_pre_regex_disable" data-field-id="{arf_field_id}" data-pattern="[0-9]{3,4}"><?php echo addslashes(esc_html__("CVC/CVV", 'ARForms')); ?></span>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="istooltip">
                <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Tooltip', 'ARForms')); ?></label>
                <div class="arf_field_option_content_cell_input">
                    <label class="arf_js_switch_label">
                        <span><?php echo addslashes(esc_html__("NO", 'ARForms')); ?>&nbsp;</span>
                    </label>
                    <span class="arf_js_switch_wrapper arf_no_transition">
                        <input type="checkbox" class="js-switch arf_tooltip_{arf_field_id}" name="arf_tooltip" id="frm_arf_tooltip_field_{arf_field_id}" onchange='arftooltipfieldfunction("{arf_field_id}", "0", "2")' value="1" />
                        <span class="arf_js_switch"></span>
                    </span>
                    <label class="arf_js_switch_label">
                        <span>&nbsp;<?php echo addslashes(esc_html__('YES', 'ARForms')); ?></span>
                    </label>
                    <input type="hidden" name="frm_arf_tooltip_field_indicator" value="" />
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="tooltipmsg">
                <label class="arf_field_option_content_cell_label"><?php echo esc_html__('Message for tooltip', 'ARForms'); ?></label>
                <div class="arf_field_option_content_cell_label">
                    <input id="arftooltiptext{arf_field_id}" type="text" name="tooltip_text" value="" class="arf_field_option_input_text txtstandardnew arfblank_txt" />
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="position_for_mobile_x">
                <label class="arf_field_option_content_cell_label"><?php echo esc_html__('Position for mobile (X)', 'ARForms'); ?></label>
                <div class="arf_field_option_content_cell_label">
                    <input id="arfposition_for_mobile_x{arf_field_id}" type="text" name="position_for_mobile_x" value="" class="arf_field_option_input_text txtstandardnew arfblank_txt" />
                        <span class="arf_field_option_input_note_text"><?php echo addslashes(esc_html__('Leave blank for default settings', 'ARForms')); ?></span>   
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="position_for_mobile_y">
                <label class="arf_field_option_content_cell_label"><?php echo esc_html__('Position for mobile (Y)', 'ARForms'); ?></label>
                <div class="arf_field_option_content_cell_label">
                    <input id="arfposition_for_mobile_y{arf_field_id}" type="text" name="position_for_mobile_y" value="" class="arf_field_option_input_text txtstandardnew arfblank_txt" />
                    <span class="arf_field_option_input_note_text"><?php echo addslashes(esc_html__('Leave blank for default settings', 'ARForms')); ?></span>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="width_for_mobile">
                <label class="arf_field_option_content_cell_label"><?php echo esc_html__('Width for mobile', 'ARForms'); ?></label>
                <div class="arf_field_option_content_cell_label">
                    <input id="arfwidth_for_moblie{arf_field_id}" type="text" name="width_for_mobile" value="" class="arf_field_option_input_text txtstandardnew arfblank_txt" />
                    <span class="arf_field_option_input_note_text"><?php echo addslashes(esc_html__('Leave blank for default settings', 'ARForms')); ?></span>
                </div>
            </div>
            <div class="arf_field_option_content_cell" data-sort="-1" id="height_for_mobile">
                <label class="arf_field_option_content_cell_label"><?php echo esc_html__('Height for mobile', 'ARForms'); ?></label>
                <div class="arf_field_option_content_cell_label">
                    <input id="arfheight_for_moblie{arf_field_id}" type="text" name="height_for_mobile" value="" class="arf_field_option_input_text txtstandardnew arfblank_txt" />
                    <span class="arf_field_option_input_note_text"><?php echo addslashes(esc_html__('Leave blank for default settings', 'ARForms')); ?></span>
                </div>
            </div>

            <?php do_action('arf_field_option_model_outside'); ?>
        </div>
    </div>
    <div class="arf_field_option_model_footer">
        <button type="button" class="arf_field_option_close_button" id="arf_field_option_close_button"><?php echo addslashes(esc_html__('Cancel', 'ARForms')); ?></button>
        <button type="button" class="arf_field_option_submit_button" data-field_id=""><?php echo esc_html__('OK', 'ARForms'); ?></button>
    </div>
</div>