<?php

$arf_mailerlite = new arf_mailerlite();

class arf_mailerlite {

    function __construct() {

        add_action('arfafterinstall', array($this, 'arf_add_mailerlite_afterinstall'), 10);

        add_action('arf_autoresponder_global_setting_block', array($this, 'arf_add_mailerlite_global_setting_block'), 10, 2);

        add_action('arf_autoresponder_out_side_email_marketing_tools_update', array($this, 'arf_mailerlite_update_api_data'), 10, 1);

        add_action('arf_email_marketers_tab_outside', array($this, 'arf_mailerlite_logo'), 10);

        add_action('arf_email_marketers_tab_container_outside', array($this, 'arf_render_mailerlite_block'), 10, 5);

        add_action('arfafterupdateform', array($this, 'arf_mailerlite_after_form_save'), 10, 4);

        add_action('arfaftercreateentry', array($this, 'arf_mailerlite_after_create_entry'), 10, 2);

        add_filter('arf_current_autoresponse_set_outside', array($this, 'arf_set_current_autoresponse_mailerlite'), 10, 2);

        add_action('arf_autoresponder_ref_update', array($this, 'arforms_mailerlite_reference_update'), 14, 3);

        add_action('arf_autoresponder_after_insert', array($this, 'arforms_mailerlite_save_form_data'), 14, 2);

        add_action('arf_autoresponder_after_update', array($this, 'arforms_mailerlite_save_form_data'), 14, 2);

    }

    function arf_add_mailerlite_afterinstall() {

        global $wpdb, $MdlDb;

        $wpdb->query("ALTER TABLE " . $MdlDb->ar . "  ADD `mailerlite` TEXT NOT NULL");

        $get_responder_id = $wpdb->get_row($wpdb->prepare("SELECT responder_id FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 14));

        if (!isset($get_responder_id->responder_id) || $get_responder_id->responder_id != 14) {
            $wpdb->query("INSERT INTO " . $MdlDb->autoresponder . " (responder_id) VALUES (14)");
        }

        $ar_types = maybe_unserialize(get_option('arf_ar_type'));

        $ar_types['mailerlite_type'] = 1;

        $ar_types = maybe_serialize($ar_types);

        update_option('arf_ar_type', $ar_types);
    }

    function arf_add_mailerlite_global_setting_block( $autores_type, $setvaltolic ) {

        global $wpdb, $MdlDb;

        $mailerlite_alldata = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d",14));
        $mailerlite_data = new stdClass();
        if( count($mailerlite_alldata) > 0 ){
            $mailerlite_data = $mailerlite_alldata[0];
        }

        ?>
            <table class="wp-list-table widefat post " style="margin:20px 0 20px 10px; border:none;">

                <tr>
                    <th style="background:none; border:0px;" width="18%">&nbsp;</th>
                    <th style="background:none; border:none;height:98px;" colspan="2"><img alt='' src="<?php echo ARFURL; ?>/images/mailerlite.png" align="absmiddle" /></th>

                </tr>

                <tr>
                    <?php $autores_type['mailerlite_type'] = ( isset($autores_type['mailerlite_type']) && $autores_type['mailerlite_type'] != '' ) ? $autores_type['mailerlite_type'] : 1; ?>
                    <th style="width:18%; background:none; border:none;"></th>
                    <th id="th_mailerlite" style="padding-left:5px;background:none; border:none;">
                        <div class="arf_radio_wrapper">
                            <div class="arf_custom_radio_div" >
                                <div class="arf_custom_radio_wrapper">
                                    <input type="radio" class="arf_submit_action arf_custom_radio" id="mailerlite_14" <?php if ($autores_type['mailerlite_type'] == 1) echo 'checked="checked"'; ?>  name="mailerlite_type" value="1" onclick="show_api('mailerlite');" />
                                    <svg width="18px" height="18px">
                                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                    </svg>
                                </div>
                            </div>
                            <span>
                                <label for="mailerlite_14"><?php echo addslashes(esc_html__('Using API', 'ARForms')); ?></label>
                            </span>
                        </div>
                    </th>

                </tr>

                <tr id="mailerlite_api_tr1" <?php if ($autores_type['mailerlite_type'] != 1) echo 'style="display:none;"'; ?>>

                    <td class="tdclass" style="width:18%; padding-right:20px; padding-bottom:3px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('API Key', 'ARForms')); ?></label></td>

                    <td style="padding-bottom:3px; padding-left:5px;"><input type="text" name="mailerlite_api" class="txtmodal1" <?php
                        if ($setvaltolic != 1) {
                            echo "readonly=readonly";
                            echo ' onclick="alert(\'Please activate license to set mailerlite settings\');"';
                        }
                        ?> id="mailerlite_api" size="80" onkeyup="show_verify_btn('mailerlite');" value="<?php echo isset($mailerlite_data->responder_api_key) ? $mailerlite_data->responder_api_key : ""; ?>" /> &nbsp; &nbsp;
                        <span id="mailerlite_link" <?php if (isset($mailerlite_data->is_verify) && $mailerlite_data->is_verify == 1) { ?>style="display:none;"<?php } ?>><a href="javascript:void(0);" onclick="verify_autores('mailerlite', '0');" class="arlinks"><?php echo addslashes(esc_html__('Verify', 'ARForms')); ?></a></span>
                        <span id="mailerlite_loader" style="display:none;"><div class="arf_imageloader" style="float: none !important;display:inline-block !important; "></div></span>
                        <span id="mailerlite_verify" class="frm_verify_li" style="display:none;"><?php echo addslashes(esc_html__('Verified', 'ARForms')); ?></span>
                        <span id="mailerlite_error" class="frm_not_verify_li" style="display:none;"><?php echo addslashes(esc_html__('Not Verified', 'ARForms')); ?></span>
                        <input type="hidden" name="mailerlite_status" id="mailerlite_status" value="<?php echo $mailerlite_data->is_verify; ?>" />
                        <div class="arferrmessage" id="mailerlite_api_error" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms')); ?></div></td>
                </tr>

                <tr id="mailerlite_api_tr2" <?php if ($autores_type['mailerlite_type'] != 1) echo 'style="display:none;"'; ?>>

                    <td class="tdclass" style="width:18%; padding-right:20px; padding-top:3px; padding-bottom:3px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('Group Name', 'ARForms')); ?></label></td>

                    <td style=" padding-top:3px; padding-bottom:3px; padding-left:5px; overflow: visible;"><span id="select_mailerlite">
                            <div class="sltstandard" style="float:none;display:inline;">
                                <?php
                                $responder_list_option = '';
                                $selected_list_label = esc_html__('Nothing Selected','ARForms');
                                $selected_list_id = '';
                                $lists = isset($mailerlite_data->responder_list_id) ? maybe_unserialize($mailerlite_data->responder_list_id) : array();
                                if ($lists != '' and count($lists) > 0) {
                                    if (is_array($lists)) {
                                        foreach ($lists as $key => $list) {
                                            if ($mailerlite_data->responder_list != '') {
                                                if ($mailerlite_data->responder_list == $list['id']) {
                                                    $selected_list_id = $list['id'];
                                                    $selected_list_label = $list['name'];
                                                }
                                            } else {
                                                if ($key == 0) {
                                                    $selected_list_id = $list['id'];
                                                    $selected_list_label = $list['name'];
                                                }
                                            }
                                            $responder_list_option .='<li class="arf_selectbox_option" data-value="' . $list['id'] . '" data-label="' . htmlentities($list['name']) . '">' . $list['name'] . '</li>';
                                        }
                                    }
                                }
                                ?>
                                <input name="mailerlite_listid" id="mailerlite_listid" value="<?php echo $selected_list_id; ?>" type="hidden" class="frm-dropdown frm-pages-dropdown">
                                <dl class="arf_selectbox" data-name="mailerlite_listid" data-id="mailerlite_listid" style="width: 400px;">
                                    <dt><span><?php echo $selected_list_label; ?></span>
                                    <svg viewBox="0 0 2000 1000" width="15px" height="15px">
                                    <g fill="#000">
                                    <path d="M1024 320q0 -26 -19 -45t-45 -19h-896q-26 0 -45 19t-19 45t19 45l448 448q19 19 45 19t45 -19l448 -448q19 -19 19 -45z"></path>
                                    </g>
                                    </svg></dt>
                                    <dd>
                                        <ul class="field_dropdown_menu field_dropdown_list_menu" style="display: none;" data-id="mailerlite_listid">
                                            <?php echo $responder_list_option; ?>
                                        </ul>
                                    </dd>
                                </dl>
                            </div></span>




                        <div id="mailerlite_del_link" style="padding-left:5px; margin-top:10px;<?php if ($mailerlite_data->is_verify == 0) { ?>display:none;<?php } ?>" class="arlinks">
                            <a href="javascript:void(0);" onclick="action_autores('refresh', 'mailerlite');"><?php echo addslashes(esc_html__('Refresh List', 'ARForms')); ?></a>
                            &nbsp;  &nbsp;  &nbsp;  &nbsp;
                            <a href="javascript:void(0);" onclick="action_autores('delete', 'mailerlite');"><?php echo addslashes(esc_html__('Delete Configuration', 'ARForms')); ?></a>
                        </div>


                    </td>

                </tr>

                <tr>
                    <td colspan="2" style="padding-left:5px;"><div class="dotted_line" style="width:96%"></div></td>
                </tr>


            </table>
        <?php

    }

    function arf_mailerlite_update_api_data($arf_mailerlite_data) {
        global $wpdb, $MdlDb;
        $arf_mailerlite_api = isset($arf_mailerlite_data['mailerlite_api']) ? $arf_mailerlite_data['mailerlite_api'] : '';
        $arf_mailerlite_listid = isset($arf_mailerlite_data['mailerlite_listid']) ? $arf_mailerlite_data['mailerlite_listid'] : '';
        $arf_mailerlite_data = apply_filters('arf_trim_values',$arf_mailerlite_data);
        
        if ( isset($arf_mailerlite_data['mailerlite_type']) && $arf_mailerlite_data['mailerlite_type'] == 1 ) {
            $wpdb->update($MdlDb->autoresponder, array('responder_api_key' => $arf_mailerlite_api, 'responder_list' => $arf_mailerlite_listid), array('responder_id' => '14'));
        }

    }

    function arf_mailerlite_logo() {
        ?>
            <li class="arf_optin_tab_item" data-id="mailerlite"><?php addslashes(esc_html_e('MailerLite', 'ARForms')); ?></li>
        <?php 
    }

    function arf_render_mailerlite_block($arfaction = '', $global_enable_ar = '', $current_active_ar = '', $data = '', $setvaltolic = '') {

        global $wpdb, $MdlDb;

        $res = maybe_unserialize(get_option('arf_ar_type'));
        $res14 = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 14), 'ARRAY_A');
        if( count($res14) > 0){
            $res14 = $res14[0];
        }
        $mailerlite_arr = maybe_unserialize(isset($data[0]['mailerlite']) ? $data[0]['mailerlite'] : '' );

        ?>
        <div class="arf_optin_tab_inner_container" id="mailerlite">
            <div>
                <?php 
                $style = '';
                $style_gray = '';
                if(isset($mailerlite_arr['enable']) && $mailerlite_arr['enable'] == 1)
                {
                    $style = 'style="display:block;"';
                    $style_gray = 'style="display:none;"';
                } else{
                    $style = 'style="display:none;"';
                    $style_gray = 'style="display:block;"';
                }?>
                <div class="arf_optin_logo mailerlite_original arfmailerlite" <?php echo $style;?>><img src="<?php echo ARFURL; ?>/images/mailerlite.png" /></div>
                <div class="arf_optin_logo mailerlite_gray arfmailerlite" <?php echo $style_gray;?>><img src="<?php echo ARFURL; ?>/images/mailerlite_gray.png" /></div>
                <div class="arf_optin_checkbox arfmailerlite">
                    <div>
                        <label class="arf_js_switch_label">
                            <span></span>
                        </label>
                        <span class="arf_js_switch_wrapper">
                            <input type="checkbox" class="js-switch arf_disable_enable_optins" name="autoresponders[]" id="autores_14" data-attr="mailerlite" value="14" <?php
                            if (isset($res['mailerlite_type']) && $res['mailerlite_type'] == 2) {
                                echo 'disabled="disabled"';
                            }
                            ?> <?php if (isset($mailerlite_arr['enable']) and $mailerlite_arr['enable'] == 1) { echo "checked=checked"; } ?> onchange="show_setting('mailerlite', '14');" <?php if ($setvaltolic != 1) { echo 'onclick="return false"'; } ?> />
                            <span class="arf_js_switch"></span>
                        </span>
                        <label class="arf_js_switch_label" for="autores_14">
                            <span>&nbsp;<?php addslashes(esc_html_e('Enable', 'ARForms')); ?></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="arf_option_configuration_wrapper mailerlite_configuration_wrapper <?php echo (isset($mailerlite_arr['enable']) && $mailerlite_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>">
                <br/><br/>
                <?php
                $rand_num = rand(1111, 9999);
                if (isset($res['mailerlite_type']) && $res['mailerlite_type'] == 1) {
                    ?>
                    <div id="select-autores_<?php echo $rand_num; ?>" class="select_autores" style="margin-left: 25px;">
                        <?php
                        if (( $arfaction == 'new' || ( $arfaction == 'duplicate' and isset($arf_template_id) and $arf_template_id < 100 ) ) || (isset($mailerlite_arr['enable']) and $mailerlite_arr['enable'] == 0 )) {
                            ?>
                            <div id="autores-mailerlite" class="autoresponder_inner_block" data-if="sadsa" >
                                <div class="textarea_space"></div>
                                <span class="lblstandard"><?php echo addslashes(esc_html__('Select Group Name', 'ARForms')); ?></span>
                                <div class="textarea_space"></div>
                                <div class="sltstandard">
                                    <?php
                                    $selected_list_id = "";
                                    $selected_list_label = addslashes(esc_html__('Select Group','ARForms'));
                                    $responder_list_option = "";
                                    $lists = maybe_unserialize($res14['responder_list_id']);
                                    if (count($lists) > 0 && is_array($lists)) {
                                        $cntr = 0;
                                        foreach ($lists as $list) {
                                            if ($res14['responder_list'] == $list['id'] || $cntr == 0) {
                                                $selected_list_id = $list['id'];
                                                $selected_list_label = $list['name'];
                                            }

                                            $responder_list_option .= '<li class="arf_selectbox_option" data-value="' . $list['id'] . '" data-label="' . htmlentities($list['name']) . '">' . $list['name'] . '</li>';
                                            $cntr++;
                                        }
                                    }
                                    ?>
                                    <input id="i_mailerlite_list" name="i_mailerlite_list" value="<?php echo $selected_list_id; ?>" type="hidden" class="frm-dropdown frm-pages-dropdown" <?php echo ( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?>>
                                    <dl class="arf_selectbox <?php echo (isset($mailerlite_arr['enable']) && $mailerlite_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>" data-name="i_mailerlite_list" data-id="i_mailerlite_list" style="width:170px;">
                                        <dt class="<?php echo (isset($mailerlite_arr['enable']) && $mailerlite_arr['enable'] == 1) ? '' : 'arf_disabled_container'; ?>"><span><?php echo $selected_list_label; ?></span>
                                        <input value="<?php print $selected_list_label; ?>" style="display:none;width:118px;" class="arf_autocomplete" type="text" autocomplete="off">
                                        <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                        <dd>
                                            <ul class="field_dropdown_list_menu" style="display: none;" data-id="i_mailerlite_list">
                                            <li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select Field','ARForms'));?>"><?php echo addslashes(esc_html__('Select Field','ARForms'));?></li>
                                                <?php echo $responder_list_option; ?>
                                            </ul>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                            <?php
                        } else {
                            ?>
                            <div id="autores-mailerlite" class="autoresponder_inner_block">
                                <div class="textarea_space"></div>
                                <span class="lblstandard"><?php echo addslashes(esc_html__('Select Group Name', 'ARForms')); ?></span>
                                <div class="textarea_space"></div>
                                <div class="sltstandard">
                                    <?php
                                    $selected_list_id = "";
                                    $selected_list_label = addslashes(esc_html__('Select Group','ARForms'));
                                    $responder_list_option = "";
                                    $lists = maybe_unserialize($res14['responder_list_id']);
                                    $default_mailerlite_select_list = isset($res14['responder_list']) ? $res14['responder_list'] : '';
                                    $selected_list_id_mailerlite = (isset($mailerlite_arr['type_val']) && $mailerlite_arr['type_val'] != '' ) ? $mailerlite_arr['type_val'] : $default_mailerlite_select_list;
                                    if (is_array($lists) && count($lists) > 0) {
                                        $cntr = 0;
                                        foreach ($lists as $list) {
                                            if ($selected_list_id_mailerlite == $list['id'] || $cntr == 0) {
                                                $selected_list_id = $list['id'];
                                                $selected_list_label = $list['name'];
                                            }

                                            $responder_list_option .= '<li class="arf_selectbox_option" data-value="' . $list['id'] . '" data-label="' . htmlentities($list['name']) . '">' . $list['name'] . '</li>';
                                            $cntr++;
                                        }
                                    }
                                    ?>
                                    <input id="i_mailerlite_list" name="i_mailerlite_list" value="<?php echo $selected_list_id; ?>" type="hidden" class="frm-dropdown frm-pages-dropdown" <?php echo ( $setvaltolic != 1 ? "readonly=readonly" : '' ); ?>>
                                    <dl class="arf_selectbox <?php echo (isset($mailerlite_arr['enable']) && $mailerlite_arr['enable'] == 1) ? '' : 'arf_not_allowd_optins'; ?>" data-name="i_mailerlite_list" data-id="i_mailerlite_list" style="width:170px;">
                                        <dt class="<?php echo (isset($mailerlite_arr['enable']) && $mailerlite_arr['enable'] == 1) ? '' : 'arf_disabled_container'; ?>"><span><?php echo $selected_list_label; ?></span>
                                        <input value="<?php echo $selected_list_label; ?>" style="display:none;width:118px;" class="arf_autocomplete" type="text">
                                        <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                                        <dd>
                                            <ul class="field_dropdown_list_menu" style="display: none;" data-id="i_mailerlite_list">
                                            <li class="arf_selectbox_option" data-value="" data-label="<?php echo addslashes(esc_html__('Select Field','ARForms'));?>"><?php echo addslashes(esc_html__('Select Field','ARForms'));?></li>
                                                <?php echo $responder_list_option; ?>
                                            </ul>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                <?php }
                ?>
            </div>
        </div>
        <?php

    }

    function arf_mailerlite_after_form_save($id, $values, $create_link, $is_ref_form) {

        global $wpdb, $armainhelper, $MdlDb;

        $get_enabled_ar = $wpdb->get_results($wpdb->prepare("SELECT enable_ar FROM " . $MdlDb->ar . " WHERE frm_id = %d ",$id));

        $enable_ar = maybe_unserialize($get_enabled_ar[0]->enable_ar);

        if (isset($values['autoresponders']) && is_array($values['autoresponders'])) {
            if (in_array(14, $values['autoresponders'])) {

                $mailerlite_entry['enable'] = 1;
                $mailerlite_entry['type'] = 0;
                $mailerlite_entry['type_val'] = isset($values['i_mailerlite_list']) ? $values['i_mailerlite_list'] : 0;

                $mailerlite_entries = maybe_serialize($mailerlite_entry);

                $wpdb->query("UPDATE " . $MdlDb->ar . " SET mailerlite = '" . $mailerlite_entries . "' WHERE frm_id = " . $id);

                $enable_ar['mailerlite'] = 1;
            } else {
                $mailerlite_entry['enable'] = 0;
                $mailerlite_entry['type'] = 0;
                $mailerlite_entry['type_val'] = 0;

                $mailerlite_entries = maybe_serialize($mailerlite_entry);

                $wpdb->query("UPDATE " . $MdlDb->ar . " SET mailerlite = '" . $mailerlite_entries . "' WHERE frm_id = " . $id);
                $enable_er['mailerlite'] = 0;
            }

            $enable_ar = maybe_serialize($enable_ar);

            $wpdb->query("UPDATE " . $MdlDb->ar . " SET enable_ar = '" . $enable_ar . "' WHERE frm_id = " . $id);
        }
        return '';
    }

    function arf_mailerlite_after_create_entry($entry_id, $form_id) {

        global $wpdb, $MdlDb;

        if( $entry_id == '' || $form_id == '' ){
            return;
        }

        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->forms . " WHERE id = %d", $form_id));

        $form_options = maybe_unserialize($results[0]->options);

        $check_condition_on_subscription = true;
        if (isset($form_options['conditional_subscription']) && $form_options['conditional_subscription'] == 1) {
            $check_condition_on_subscription = apply_filters('arf_check_condition_on_subscription', $form_options, $entry_id);
        }

        if( !$check_condition_on_subscription ){
            return;
        }

        $res = $wpdb->get_results($wpdb->prepare("SELECT `mailerlite` FROM " .$MdlDb->ar." WHERE frm_id = %d", $form_id), 'ARRAY_A');
        $ar_mailerlite = maybe_unserialize($res[0]['mailerlite']);

        if ( isset($ar_mailerlite['enable']) && $ar_mailerlite['enable'] == 1 ) {

            require(AUTORESPONDER_PATH . '/mailerlite/mailerlite_send_contact.php');

        }

    }

    function arf_set_current_autoresponse_mailerlite($current_active_ar, $data) {
        $mailerlite_arr = maybe_unserialize(isset($data[0]['mailerlite']) ? $data[0]['mailerlite'] : '' );

        if (isset($mailerlite_arr['enable']) && $mailerlite_arr['enable'] == 1) {
            $current_active_ar = 'mailerlite';
        }
        return $current_active_ar;
    }

    function arforms_mailerlite_save_form_data($id, $data) {
        global $wpdb, $MdlDb;

        $mailerlite_arr = array();
        $type = maybe_unserialize(get_option('arf_ar_type'));
        $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->autoresponder . " WHERE responder_id = %d", 14), 'ARRAY_A');
        if (isset($data['autoresponders']) && in_array('14', $data['autoresponders'])) {
            $mailerlite_arr['enable'] = 1;
        } else {
            $mailerlite_arr['enable'] = 0;
        }
        $mailerlite_arr = apply_filters('arf_trim_values',$mailerlite_arr);
        $ar_mailerlite = maybe_serialize($mailerlite_arr);
        $res = $wpdb->update($MdlDb->ar, array('mailerlite' => $ar_mailerlite), array('frm_id' => $id));

    }

    function arforms_mailerlite_reference_update($id, $res_rec, $resrpw) {
        global $wpdb, $MdlDb;
        $update = $wpdb->query($wpdb->prepare("update " . $MdlDb->ar . " set mailerlite = '%s' where frm_id = %d", $res_rec["mailerlite"], $resrpw));
    }

}

?>