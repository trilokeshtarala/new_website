<?php
global $arfieldhelper,$conditional_logic_array_if,$conditional_logic_array_than;
$arf_conditional_logic_rules = array();

if (isset($values['arf_conditional_logic_rules']) && !empty($values['arf_conditional_logic_rules'])) {
    $arf_conditional_logic_rules = $values['arf_conditional_logic_rules'];
} else {

    $arf_conditional_logic_rules[0]['id'] = '';
    $arf_conditional_logic_rules[0]['logical_operator'] = 'and';

    $arf_conditional_logic_rules[0]['condition'][0]['condition_id'] = '';
    $arf_conditional_logic_rules[0]['condition'][0]['field_id'] = '';
    $arf_conditional_logic_rules[0]['condition'][0]['operator'] = '';
    $arf_conditional_logic_rules[0]['condition'][0]['value'] = '';

    $arf_conditional_logic_rules[0]['result'][0]['result_id'] = '';
    $arf_conditional_logic_rules[0]['result'][0]['action'] = '';
    $arf_conditional_logic_rules[0]['result'][0]['field_id'] = '';
    $arf_conditional_logic_rules[0]['result'][0]['value'] = '';
}
$is_ajax = 'no';
?>


<div class="arf_new_conditional_logic_field_dropdown_html" id="arf_new_conditional_logic_field_dropdown_html" style="display:none;">
    <?php
    $conditional_logic_field_options = '';
    if (!empty($values['fields'])) {
        foreach ($values['fields'] as $val_key => $fo) {
            if (!in_array($fo['type'], $conditional_logic_array_than)) {
                $current_field_id = $fo["id"];
                if($fo['type'] =='break')
                {
                    $display_name = $fo['second_page_label'];
                }
                else
                {
                    $display_name = $fo["name"];
                }
                if($current_field_id !="" && $arfieldhelper->arf_execute_function($display_name,'strip_tags') ==""){
                    $conditional_logic_field_options .= '<li class="arf_selectbox_option" data-value="' . $current_field_id . '" data-type="' . $fo["type"] . '" data-label="[Field Id:'.$current_field_id.']">[Field Id:'.$current_field_id.']</li>';
                }else{
                    $conditional_logic_field_options .= '<li class="arf_selectbox_option" data-value="' . $current_field_id . '" data-type="' . $fo["type"] . '" data-label="' . $arfieldhelper->arf_execute_function($display_name,'strip_tags') . '">' . $arfieldhelper->arf_execute_function($display_name,'strip_tags') . '</li>';    
                }
                
            }
        }
    }
    ?>
    <li class="arf_selectbox_option" data-type="" data-value="" data-label="<?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?></li>
    <?php echo $conditional_logic_field_options; ?>
</div>

<div class="arf_new_conditional_action_field_dropdown_html" id="arf_new_conditional_action_field_dropdown_html" style="display:none;">
    <?php
    $conditional_logic_field_options = '';
    if (!empty($values['fields'])) {
        foreach ($values['fields'] as $val_key => $fo) {
            if (!in_array($fo['type'], $conditional_logic_array_if)) {
                if($fo['type'] == 'arfslider' && $fo['arf_range_selector'] == 1) {
                    continue;
                }
                $current_field_id = $fo["id"];
                if($current_field_id !="" && $arfieldhelper->arf_execute_function($fo["name"],'strip_tags')==""){
                    $conditional_logic_field_options .= '<li class="arf_selectbox_option" data-value="' . $current_field_id . '" data-type="' . $fo["type"] . '" data-label="[Field Id:'.$current_field_id.']">[Field Id:'.$current_field_id.']</li>';
                }else{
                    $conditional_logic_field_options .= '<li class="arf_selectbox_option" data-value="' . $current_field_id . '" data-type="' . $fo["type"] . '" data-label="' . $arfieldhelper->arf_execute_function($fo["name"],'strip_tags') . '">' . $arfieldhelper->arf_execute_function($fo["name"],'strip_tags') . '</li>';    
                }
                
            }
        }
    }
    ?>
    <li class="arf_selectbox_option" data-type="" data-value="" data-label="<?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Select Field', 'ARForms')); ?></li>
    <?php echo $conditional_logic_field_options; ?>
</div>




<div class="arftablerow" style="width:100%;min-height:100%;box-sizing:  border-box;-webkit-box-sizing:  border-box;-o-box-sizing:  border-box;-moz-box-sizing:  border-box;margin-left:0px;padding: 5px;">


    <div class="arfcolmnleft" style="box-sizing:border-box;-webkit-box-sizing:border-box;-o-box-sizing:border-box;-moz-box-sizing:border-box;">
        <div class="arftablerow" style="width:100%;box-sizing:border-box;-webkit-box-sizing:border-box;-o-box-sizing:border-box;-moz-box-sizing:border-box;">



            <div class="arf_rule_conditional_logic_mian">


                <div id="arf_rule_conditional_logic">

                    <?php foreach ($arf_conditional_logic_rules as $rule_i => $logic_rules) { ?>

                        <div class="arftablerow arf_conditional_logic_div" style="margin-top:15px;width:100%; box-sizing: border-box;-webkit-box-sizing: border-box;-o-box-sizing: border-box;-moz-box-sizing: border-box;" id="arf_conditional_logic_div_<?php echo $rule_i; ?>">

                            <input type="hidden" value="<?php echo $rule_i; ?>" name="options[arf_conditional_logic_rules][<?php echo $rule_i; ?>][id]" class="arf_condtional_logic_array">

                                <div class="arflogiccommoncondition">
                                <div class="arfcolumnleft arfsettingsubtitle arfbcolor"><?php echo addslashes(esc_html__('IF', 'ARForms')); ?>
                                 <div class="arfcommonconditionlogin">
                                    <span id="select_arf_conditional_logic_logical_operator">
                                    <div class="sltstandard" style="width:100%">

                                        <input id="arf_conditional_logic_logical_operator_<?php echo $rule_i; ?>" name="options[arf_conditional_logic_rules][<?php echo $rule_i; ?>][logical_operator]" value="<?php echo ($logic_rules['logical_operator'] == 'and') ? 'and' : 'or'; ?>" type="hidden">
                                        <dl class="arf_selectbox" data-name="arf_conditional_logic_logical_operator_<?php echo $rule_i; ?>" data-id="arf_conditional_logic_logical_operator_<?php echo $rule_i; ?>" style="width:100%;">
                                            <dt><span><?php echo ($logic_rules['logical_operator'] == 'and') ? addslashes(esc_html__('All', 'ARForms')) : addslashes(esc_html__('Any', 'ARForms')); ?></span>
                                            <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                            <dd>
                                                <ul style="display: none;" data-id="arf_conditional_logic_logical_operator_<?php echo $rule_i; ?>">
                                                    <li class="arf_selectbox_option" data-value="and" data-label="<?php echo addslashes(esc_html__('All', 'ARForms')); ?>"><?php echo addslashes(esc_html__('All', 'ARForms')); ?></li>
                                                    <li class="arf_selectbox_option" data-value="or" data-label="<?php echo addslashes(esc_html__('Any', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Any', 'ARForms')); ?></li>
                                                </ul>
                                            </dd>
                                        </dl>

                                    </div>
                                    </span>
                                </div>
                                </div>

                                <div class="arfcolumnleft arfsettingsubtitle arfbcolor arfconditional_logic_than_span"><?php echo addslashes(esc_html__('THEN', 'ARForms')); ?></div>
                                </div>

                            <div class="arfcolmnleft arf_new_conditional_logic" style="<?php echo (is_rtl()) ? 'padding-left: 5px;' : 'padding-right: 5px;';?>" id="arf_condition_<?php echo $rule_i; ?>">
                                <?php foreach ($logic_rules['condition'] as $condition_i => $condition) { ?>
                                    <?php $arformcontroller->arf_condition_add($rule_i, $condition_i, $is_ajax, $values, count($logic_rules['condition'])); ?>
                                <?php } ?>
                            </div>


                            <div class="arfcolumnright arf_new_conditional_logic" style="<?php echo (is_rtl()) ? 'padding-left: 5px;' : 'padding-right: 5px;'; ?>" id="arf_result_<?php echo $rule_i; ?>">
                                <?php foreach ($logic_rules['result'] as $result_i => $result) {
                                    ?>
                                    
                                    <?php $arformcontroller->arf_result_add($rule_i, $result_i, $is_ajax, $values, count($logic_rules['result'])); ?>
                                <?php } ?>
                            </div>

                            <div class="arf_new_conditional_logic_delete_div" style="display:<?php echo (count($arf_conditional_logic_rules) > 1) ? 'inline-block' : 'none'; ?>" onclick="arf_new_conditional_logic_delete('<?php echo $rule_i; ?>')">

                                <div class="arf_new_conditional_logic_delete"><span><svg style="width: 18px;height: 18px;"><g><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M16.939,5.845h-1.415V17.3c0,0.292-0.236,0.529-0.529,0.529H4.055  c-0.292,0-0.529-0.237-0.529-0.529V5.845H2.018c-0.292,0-0.529-0.739-0.529-1.031s0.237-0.982,0.529-0.982h2.509V1.379  c0-0.293,0.237-0.529,0.529-0.529h8.954c0.293,0,0.529,0.236,0.529,0.529v2.452h2.399c0.292,0,0.529,0.69,0.529,0.982  S17.231,5.845,16.939,5.845z M12.533,2.811H6.517v1.011h6.016V2.811z M13.541,5.845l-0.277-0.031L5.788,5.845H5.534v10.001h8.007  V5.845z M8.525,13.849H7.534v-6.08h0.991V13.849z M11.525,13.849h-0.991v-6.08h0.991V13.849z"></path></g></svg></span></div>
                            </div>

                        </div>
                    <?php } ?>

                </div>
</div>
                <div id="arf_conditional_law_loader" style="width:100%; text-align:center; display:none;">
                    <img style="width: 28px;" src="<?php echo ARFIMAGESURL ?>/ajax_loader_gray_32.gif" />
                </div>

                <div class="arf_add_new_law" id="arf_add_new_conditional_law" onclick="arf_add_new_conditional_law();">
                    <?php echo addslashes(esc_html__('Add New Condition', 'ARForms')); ?>
                </div>
            </div>

    </div>



</div>