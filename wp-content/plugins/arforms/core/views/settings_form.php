<?php
global $current_user, $arformcontroller;
?>
<style>
    .wrap table.widefat {
        background:none;
        width:98%;
    }
    .widefat th {
        background:#F9F9F9;
    }
    
    .txtmodal1 {
        width:400px !important;
        height: 35px;
        font-family: Asap-regular;
        font-size: 14px;
        color: #4e5462 !important;
    }
    .lbltitle {
        font-size:14px !important;
    }
    .tdclass {
        padding-bottom:20px !important;
        padding-left:0px !important;
    }
    #autoresponder_settings .tdclass {
        padding-bottom:25px !important;
    }
    .txtmultinew {
        width:400px !important;
        height:90px !important;
    }
    .txtmultinew.testmailmsg{
        height:50px !important
    }
    .dotted_line {
        margin-top: 20px;
        border-bottom:1px solid #e3eaec !important;
    }
    #poststuff #post-body {
        margin-top: 35px !important;
    }
    .wrap .frm_verify_li {
        color:green;
    }
    .arfdisabled{
        cursor:not-allowed !important;
    }
</style>

<div class="wrap arf_setting_page">

    <div class="top_bar">
        <span class="h2"><?php echo addslashes(esc_html__('General Settings', 'ARForms')); ?></span>
    </div>

    <div id="poststuff" class="metabox-holder">


        <div id="post-body">

            <div class="inside" style="background-color:#ffffff;">

                <div class="formsettings1" style="background-color:#ffffff;">

                    <div class="setting_tabrow">



                        <div class="arftab" style="padding: 0px;">
                            <?php
                            $setting_tab = get_option('arf_current_tab');
                            $setting_tab = (!isset($setting_tab) || empty($setting_tab) ) ? 'general_settings' : $setting_tab;
                            ?>

                            <ul id="arfsettingpagenav" class="arfmainformnavigation" style="height:42px; padding-bottom:0px; margin-bottom:0px;">


                                <li style="width:auto !important" class="general_settings <?php
                                if ($setting_tab == 'general_settings') {
                                    echo 'btn_sld';
                                } else {
                                    echo 'tab-unselected';
                                }
                                ?>">
                                    <a href="javascript:show_form_settimgs('general_settings','autoresponder_settings');"><?php echo addslashes(esc_html__('General Settings', 'ARForms')); ?></a>
                                </li>


                                <li style="width:auto !important" class="autoresponder_settings <?php
                                if ($setting_tab == 'autoresponder_settings') {
                                    echo 'btn_sld';
                                } else {
                                    echo 'tab-unselected';
                                }
                                ?>">
                                    <a href="javascript:show_form_settimgs('autoresponder_settings','general_settings');"><?php echo addslashes(esc_html__('Email Marketing Tools', 'ARForms')); ?></a>
                                </li>

                                <?php foreach ($sections as $sec_name => $section) { ?>


                                    <li><a href="#<?php echo $sec_name ?>_settings"><?php echo ucfirst($sec_name) ?></a></li>


                                <?php } ?>

                            </ul>



                        </div>

                    </div>



                    <form name="frm_settings_form" method="post" enctype="multipart/form-data" class="frm_settings_form" onsubmit="return global_form_validate();">


                        <input type="hidden" name="arfaction" value="process-form" />

                        <input type="hidden" name="arfcurrenttab" id="arfcurrenttab" value="<?php echo get_option('arf_current_tab'); ?>" />

                        <?php wp_nonce_field('update-options'); ?>

                        <div style="margin-left: 15px;">
                            <?php
                            if (isset($message) && $message != '') {
                                ?>
                                <?php
                                if (is_admin()) {
                                    ?>
                                    <script type="text/javascript" language="javascript"> setTimeout(function () {
                                            success_msg();
                                        }, 100);</script>
                                    <div id="success_message" class="arf_success_message">
                                        <div class="message_descripiton">
                                            <div style="float: left; margin-right: 15px;"><?php
                                            } echo $message;
                                            if (is_admin()) {
                                                ?></div>
                                            <div class="message_svg_icon">
                                                <svg style="height: 14px;width: 14px;"><path fill-rule="evenodd" clip-rule="evenodd" fill="#FFFFFF" d="M6.075,14.407l-5.852-5.84l1.616-1.613l4.394,4.385L17.181,0.411
                                                                                             l1.616,1.613L6.392,14.407H6.075z"></path></svg>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            ?>

                            <?php if (isset($errors) && is_array($errors) && count($errors) > 0) { ?>


                                <?php
                                foreach ($errors as $error) {
                                    ?><script type="text/javascript" language="javascript"> setTimeout(function () {
                                            error_msg();
                                        }, 10);</script>
                                    <div id="error_message" class="arf_error_message"><div class="message_descripiton">
                                            <?php echo stripslashes($error); ?>
                                        </div></div>
                                <?php } ?>

                            <?php } ?>
                        </div>

                        <div style="clear:both"></div>




                        <div id="general_settings" style="border-top:none; background-color:#FFFFFF; border-radius:5px 5px 5px 5px;-webkit-border-radius:5px 5px 5px 5px;-o-border-radius:5px 5px 5px 5px;-moz-border-radius:5px 5px 5px 5px;   padding-top:50px;padding-left:50px;padding-right: 50px;<?php if ($setting_tab != 'general_settings') echo 'display:none;'; ?>">


                            <table class="form-table" style="margin-top:0px;">

                                <?php
                                $hostname = $_SERVER["SERVER_NAME"];

                                $setvaltolic = 0;
                                global $check_current_val;
                                $setvaltolic = $arformcontroller->$check_current_val();
                                ?>

                                <?php
                                if (is_rtl()) {
                                    $float_style = 'float:right;';
                                } else {
                                    $float_style = 'float:left;';
                                }

                                function is_captcha_act(){
                                    if(!function_exists('is_plugin_active')){
                                        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                                    }
                                    return is_plugin_active('arformsgooglecaptcha/arformsgooglecaptcha.php');
                                }

                                    if(!is_captcha_act())
                                    { 
                                        $show_capt =  'style="display:none"';
                                    }else{
                                        $show_capt = 'style="display:table-row"';
                                    }
                                ?>

                                <tr class="arfmainformfield" valign="top" <?php echo $show_capt;?>>
                                    <td class="lbltitle" colspan="2"><?php echo addslashes(esc_html__('reCAPTCHA Configuration', 'ARForms')); ?>&nbsp;
                                    </td>
                                </tr>

                                <tr class="arfmainformfield" valign="top" <?php echo $show_capt;?>>
                                    <td colspan="2" style="padding-left:0px; padding-bottom:30px;padding-top:15px;">
                                        <label class="lblsubtitle"><?php echo stripslashes(esc_html__('reCAPTCHA requires an API key, consisting of a "site" and a "private" key. You can sign up for a', 'ARForms')); ?>&nbsp;&nbsp;<a href="https://www.google.com/recaptcha/" target="_blank" class="arlinks"><b><?php echo addslashes(esc_html__('free reCAPTCHA key', 'ARForms')); ?></b></a>.</label>
                                    </td>
                                </tr>

                                <tr class="arfmainformfield" valign="top" <?php echo $show_capt;?>>
                                    <td class="tdclass" style="padding-left:30px;" width="18%">
                                        <label class="lblsubtitle"><?php echo addslashes(esc_html__('Site Key', 'ARForms')); ?></label>
                                    </td>

                                    <td>
                                        <input type="text" name="frm_pubkey" id="frm_pubkey" class="txtmodal1" size="42" value="<?php echo esc_attr($arfsettings->pubkey) ?>" />
                                    </td>
                                </tr>


                                <tr class="arfmainformfield" valign="top" <?php echo $show_capt;?>>
                                    <td class="tdclass">
                                        <label class="lblsubtitle"><?php echo addslashes(esc_html__('Secret Key', 'ARForms')); ?></label>
                                    </td>

                                    <td>
                                        <input type="text" name="frm_privkey" id="frm_privkey" class="txtmodal1" size="42" value="<?php echo esc_attr($arfsettings->privkey) ?>" />
                                    </td>
                                </tr>

                                <tr class="arfmainformfield" valign="top" <?php echo $show_capt;?>>
                                    <td class="tdclass">
                                        <label class="lblsubtitle"><?php echo addslashes(esc_html__('reCAPTCHA Theme', 'ARForms')); ?></label>
                                    </td>

                                    <td style="padding-bottom:10px;">
                                        <?php
                                        $responder_list_option = '';
                                        $selected_list_id = '';
                                        $selected_list_label = '';

                                        foreach (array('light' => addslashes(esc_html__('Light', 'ARForms')), 'dark' => addslashes(esc_html__('Dark', 'ARForms'))) as $theme_value => $theme_name) {
                                            if ($arfsettings->re_theme == $theme_value) {
                                                $selected_list_id = esc_attr($theme_value);
                                                $selected_list_label = $theme_name;
                                            }
                                            $responder_list_option .= '<li class="arf_selectbox_option" data-value="' . esc_attr($theme_value) . '" data-label="' . $theme_name . '">' . $theme_name . '</li>';
                                            ?>
                                        <?php } ?>

                                        <div class="sltstandard" style="float:none;">
                                            <input id="frm_re_theme" name="frm_re_theme" value="<?php echo $selected_list_id; ?>" type="hidden" class="frm-dropdown frm-pages-dropdown">
                                            <dl class="arf_selectbox" data-name="frm_re_theme" data-id="frm_re_theme" style="width:400px;">
                                                <dt><span><?php echo $selected_list_label; ?></span>
                                                <svg viewBox="0 0 2000 1000" width="15px" height="15px">
                                                <g fill="#000">
                                                <path d="M1024 320q0 -26 -19 -45t-45 -19h-896q-26 0 -45 19t-19 45t19 45l448 448q19 19 45 19t45 -19l448 -448q19 -19 19 -45z"></path>
                                                </g>
                                                </svg>
                                                </dt>
                                                <dd>
                                                    <ul class="field_dropdown_menu field_dropdown_list_menu" style="display: none;" data-id="frm_re_theme">
                                                        <?php echo $responder_list_option; ?>
                                                    </ul>
                                                </dd>
                                            </dl>
                                        </div>
                                    </td>
                                </tr>


                                <tr class="arfmainformfield" valign="top" <?php echo $show_capt;?>>
                                    <td class="tdclass">
                                        <label class="lblsubtitle"><?php echo addslashes(esc_html__('reCAPTCHA Language', 'ARForms')); ?></label>
                                    </td>

                                    <td style="padding-bottom:10px;">
                                        <div class="sltstandard" style="float:none;  margin-top:5px;">
                                            <?php
                                            $responder_list_option = '';
                                            $selected_list_id = 'en';
                                            $selected_list_label = addslashes(esc_html__('English (US)', 'ARForms'));
                                            $rclang = array();
                                            $rclang['en'] = addslashes(esc_html__('English (US)', 'ARForms'));
                                            $rclang['ar'] = addslashes(esc_html__('Arabic', 'ARForms'));
                                            $rclang['bn'] = addslashes(esc_html__('Bengali', 'ARForms'));
                                            $rclang['bg'] = addslashes(esc_html__('Bulgarian', 'ARForms'));
                                            $rclang['ca'] = addslashes(esc_html__('Catalan', 'ARForms'));
                                            $rclang['zh-CN'] = addslashes(esc_html__('Chinese(Simplified)', 'ARForms'));
                                            $rclang['zh-TW'] = addslashes(esc_html__('Chinese(Traditional)', 'ARForms'));
                                            $rclang['hr'] = addslashes(esc_html__('Croatian', 'ARForms'));
                                            $rclang['cs'] = addslashes(esc_html__('Czech', 'ARForms'));
                                            $rclang['da'] = addslashes(esc_html__('Danish', 'ARForms'));
                                            $rclang['nl'] = addslashes(esc_html__('Dutch', 'ARForms'));
                                            $rclang['en-GB'] = addslashes(esc_html__('English (UK)', 'ARForms'));
                                            $rclang['et'] = addslashes(esc_html__('Estonian', 'ARForms'));
                                            $rclang['fil'] = addslashes(esc_html__('Filipino', 'ARForms'));
                                            $rclang['fi'] = addslashes(esc_html__('Finnish', 'ARForms'));
                                            $rclang['fr'] = addslashes(esc_html__('French', 'ARForms'));
                                            $rclang['fr-CA'] = addslashes(esc_html__('French (Canadian)', 'ARForms'));
                                            $rclang['de'] = addslashes(esc_html__('German', 'ARForms'));
                                            $rclang['gu'] = addslashes(esc_html__('Gujarati', 'ARForms'));
                                            $rclang['de-AT'] = addslashes(esc_html__('German (Autstria)', 'ARForms'));
                                            $rclang['de-CH'] = addslashes(esc_html__('German (Switzerland)', 'ARForms'));
                                            $rclang['el'] = addslashes(esc_html__('Greek', 'ARForms'));
                                            $rclang['iw'] = addslashes(esc_html__('Hebrew', 'ARForms'));
                                            $rclang['hi'] = addslashes(esc_html__('Hindi', 'ARForms'));
                                            $rclang['hu'] = addslashes(esc_html__('Hungarian', 'ARForms'));
                                            $rclang['id'] = addslashes(esc_html__('Indonesian', 'ARForms'));
                                            $rclang['it'] = addslashes(esc_html__('Italian', 'ARForms'));
                                            $rclang['ja'] = addslashes(esc_html__('Japanese', 'ARForms'));
                                            $rclang['kn'] = addslashes(esc_html__('Kannada', 'ARForms'));
                                            $rclang['ko'] = addslashes(esc_html__('Korean', 'ARForms'));
                                            $rclang['lv'] = addslashes(esc_html__('Latvian', 'ARForms'));
                                            $rclang['lt'] = addslashes(esc_html__('Lithuanian', 'ARForms'));
                                            $rclang['ms'] = addslashes(esc_html__('Malay', 'ARForms'));
                                            $rclang['ml'] = addslashes(esc_html__('Malayalam', 'ARForms'));
                                            $rclang['mr'] = addslashes(esc_html__('Marathi', 'ARForms'));
                                            $rclang['no'] = addslashes(esc_html__('Norwegian', 'ARForms'));
                                            $rclang['fa'] = addslashes(esc_html__('Persian', 'ARForms'));
                                            $rclang['pl'] = addslashes(esc_html__('Polish', 'ARForms'));
                                            $rclang['pt'] = addslashes(esc_html__('Portuguese', 'ARForms'));
                                            $rclang['pt-BR'] = addslashes(esc_html__('Portuguese (Brazil)', 'ARForms'));
                                            $rclang['pt-PT'] = addslashes(esc_html__('Portuguese (Portugal)', 'ARForms'));
                                            $rclang['ro'] = addslashes(esc_html__('Romanian', 'ARForms'));
                                            $rclang['ru'] = addslashes(esc_html__('Russian', 'ARForms'));
                                            $rclang['sr'] = addslashes(esc_html__('Serbian', 'ARForms'));
                                            $rclang['sk'] = addslashes(esc_html__('Slovak', 'ARForms'));
                                            $rclang['sl'] = addslashes(esc_html__('Slovenian', 'ARForms'));
                                            $rclang['es'] = addslashes(esc_html__('Spanish', 'ARForms'));
                                            $rclang['es-149'] = addslashes(esc_html__('Spanish (Latin America)', 'ARForms'));
                                            $rclang['sv'] = addslashes(esc_html__('Swedish', 'ARForms'));
                                            $rclang['ta'] = addslashes(esc_html__('Tamil', 'ARForms'));
                                            $rclang['te'] = addslashes(esc_html__('Telugu', 'ARForms'));
                                            $rclang['th'] = addslashes(esc_html__('Thai', 'ARForms'));
                                            $rclang['tr'] = addslashes(esc_html__('Turkish', 'ARForms'));
                                            $rclang['uk'] = addslashes(esc_html__('Ukrainian', 'ARForms'));
                                            $rclang['ur'] = addslashes(esc_html__('Urdu', 'ARForms'));
                                            $rclang['vi'] = addslashes(esc_html__('Vietnamese', 'ARForms'));
                                            ?>
                                            <?php
                                            foreach ($rclang as $lang => $lang_name) {
                                                if ($arfsettings->re_lang == $lang) {
                                                    $selected_list_id = esc_attr($lang);
                                                    $selected_list_label = $lang_name;
                                                }
                                                $responder_list_option .= '<li class="arf_selectbox_option" data-value="' . esc_attr($lang) . '" data-label="' . $lang_name . '">' . $lang_name . '</li>';
                                            }
                                            ?>
                                            <input id="frm_re_lang" name="frm_re_lang" value="<?php echo $selected_list_id; ?>" type="hidden" class="frm-dropdown frm-pages-dropdown">
                                            <dl class="arf_selectbox" data-name="frm_re_lang" data-id="frm_re_lang" style="width:400px;">
                                                <dt><span><?php echo $selected_list_label; ?></span>
                                                <svg viewBox="0 0 2000 1000" width="15px" height="15px">
                                                <g fill="#000">
                                                <path d="M1024 320q0 -26 -19 -45t-45 -19h-896q-26 0 -45 19t-19 45t19 45l448 448q19 19 45 19t45 -19l448 -448q19 -19 19 -45z"></path>
                                                </g>
                                                </svg>
                                                </dt>
                                                <dd>
                                                    <ul class="field_dropdown_menu field_dropdown_list_menu" style="display: none;" data-id="frm_re_lang">
                                                        <?php echo $responder_list_option; ?>
                                                    </ul>
                                                </dd>
                                            </dl>
                                        </div>
                                    </td>
                                </tr>


                                <tr class="arfmainformfield" valign="top" <?php echo $show_capt;?>>
                                    <td colspan="2"><div style="width:96%" class="dotted_line"></div></td>
                                </tr>
                
                                <?php
                                if (is_rtl()) {
                                    $float_style = 'float:right;';
                                } else {
                                    $float_style = 'float:left;';
                                }
                                ?>
                                <tr class="arfmainformfield">
                                    <td valign="top" colspan="2" class="lbltitle titleclass"><?php echo addslashes(esc_html__('Default Messages On Form', 'ARForms')); ?> </td>
                                </tr>

                                <tr>
                                    <td class="tdclass"  style="padding-left:30px;" width="18%">
                                        <label class="lblsubtitle"><?php echo addslashes(esc_html__('Blank Field', 'ARForms')); ?>&nbsp;&nbsp;<span style="vertical-align:middle" class="arfglobalrequiredfield">*</span></label> <br/>
                                    </td>
                                    <td class="arfmainformfield" >
                                        <input type="text" id="frm_blank_msg" name="frm_blank_msg" class="txtmodal1" value="<?php echo esc_attr($arfsettings->blank_msg) ?>" style=" <?php echo $float_style; ?>"/>

                                        <div class="arf_tooltip_main" style=" <?php echo $float_style; ?>"><img alt='' src="<?php echo ARFIMAGESURL ?>/tooltips-icon.png" alt="?" class="arfhelptip" title="<?php echo addslashes(esc_html__('Message will be displayed when required fields is left blank.', 'ARForms')); ?>" style="margin-left:10px; margin-top:4px;"/></div>
                                        <div style="clear:both"></div>
                                        <div class="arferrmessage" id="arfblankerrmsg" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms')); ?></div>
                                    </td>


                                </tr>





                                <tr class="arfmainformfield">


                                    <td class="tdclass">
                                        <label class="lblsubtitle"><?php echo addslashes(esc_html__('Incorrect Field', 'ARForms')); ?>&nbsp;&nbsp;<span style="vertical-align:middle" class="arfglobalrequiredfield">*</span></label> <br/>

                                    </td>

                                    <td >
                                        <input type="text" id="arfinvalidmsg" name="frm_invalid_msg" class="txtmodal1" value="<?php echo esc_attr($arfsettings->invalid_msg) ?>" style=" <?php echo $float_style; ?>"/>

                                        <div class="arf_tooltip_main" style=" <?php echo $float_style; ?>"><img alt='' src="<?php echo ARFIMAGESURL ?>/tooltips-icon.png" alt="?" class="arfhelptip" title="<?php echo addslashes(esc_html__('Message will be displayed when incorrect data is inserted of missing.', 'ARForms')); ?>" style="margin-left:10px; margin-top:4px;"/></div>
                                        <div style="clear:both"></div>
                                        <div class="arferrmessage" id="arfinvalidmsg_error" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms'));; ?></div>
                                    </td>


                                    </td>


                                </tr>


                                <tr class="arfmainformfield">


                                    <td class="tdclass">


                                        <label class="lblsubtitle"><?php echo addslashes(esc_html__('Success Message', 'ARForms')); ?>&nbsp;&nbsp;<span style="vertical-align:middle" class="arfglobalrequiredfield">*</span></label> </td>

                                    <td>

                                        <input type="text" id="arfsuccessmsg" name="frm_success_msg" class="txtmodal1" value="<?php echo esc_attr($arfsettings->success_msg) ?>" style=" <?php echo $float_style; ?>"/>

                                        <div class="arf_tooltip_main" style=" <?php echo $float_style; ?>"><img alt='' src="<?php echo ARFIMAGESURL ?>/tooltips-icon.png" alt="?" class="arfhelptip" title="<?php echo addslashes(esc_html__('Default message displayed after form is submitted.', 'ARForms')); ?>" style="margin-left:10px; margin-top:4px;"/></div>
                                        <div style="clear:both"></div>

                                        <div class="arferrmessage" id="arfsuccessmsgerr" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms')); ?></div>


                                    </td>


                                </tr>


                                <tr class="arfmainformfield">


                                    <td class="tdclass">


                                        <label class="lblsubtitle"><?php echo esc_html__('Submission Failed Message', 'ARForms'); ?>&nbsp;&nbsp;<span style="vertical-align:middle" class="arfglobalrequiredfield">*</span></label></td>

                                    <td >

                                        <input type="text" id="arfmessagefailed" name="frm_failed_msg" class="txtmodal1" value="<?php echo esc_attr($arfsettings->failed_msg) ?>" style=" <?php echo $float_style; ?>"/>

                                        <div class="arf_tooltip_main" style=" <?php echo $float_style; ?>"><img alt='' src="<?php echo ARFIMAGESURL ?>/tooltips-icon.png" alt="?" class="arfhelptip" title="<?php echo addslashes(esc_html__('Message will be displayed when form is submitted but Duplicate entry exists.', 'ARForms')); ?>" style="margin-left:10px; margin-top:4px;"/></div>
                                        <div style="clear:both"></div>

                                        <div class="arferrmessage" id="arferrormessagefailed" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms')); ?></div>


                                    </td>


                                </tr>


                                <tr class="arfmainformfield">


                                    <td class="tdclass" >


                                        <label class="lblsubtitle"><?php echo addslashes(esc_html__('Default Submit Button', 'ARForms')); ?>&nbsp;&nbsp;<span style="vertical-align:middle" class="arfglobalrequiredfield">*</span></label></td>


                                    <td >

                                        <input type="text" class="txtmodal1" value="<?php echo esc_attr($arfsettings->submit_value) ?>" id="arfvaluesubmit" name="frm_submit_value" />
                                        <div class="arferrmessage" id="arferrorsubmitvalue" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms')); ?></div>


                                    </td>


                                </tr>
                                <tr class="arfmainformfield" valign="top">
                                    <td colspan="2"><div style="width:96%" class="dotted_line"></div></td>
                                </tr>


                                <tr class="arfmainformfield">
                                    <td valign="top" colspan="2" class="lbltitle titleclass"><?php echo addslashes(esc_html__('Email Settings', 'ARForms')); ?></td>
                                </tr>

                                <tr>


                                    <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('From/Replyto Name', 'ARForms')); ?>&nbsp;&nbsp;<span style="vertical-align:middle" class="arfglobalrequiredfield">*</span></label> </td>


                                    <td valign="top" style="padding-bottom:10px;">


                                        <input type="text" class="txtmodal1" id="frm_reply_to_name" name="frm_reply_to_name" value="<?php echo $arfsettings->reply_to_name; ?>" style="width:400px;">
                                        <div class="arferrmessage" id="frm_reply_to_name_error" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms')); ?></div>

                                    </td>


                                </tr>


                                <tr>
                                    <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('From Email', 'ARForms')); ?>&nbsp;&nbsp;<span style="vertical-align:middle" class="arfglobalrequiredfield">*</span></label></td>
                                    <td valign="top" style="padding-bottom:10px;">
                                        <input type="text" class="txtmodal1" id="frm_reply_to" name="frm_reply_to" value="<?php echo $arfsettings->reply_to; ?>" style="width:400px;">
                                        <div class="arferrmessage" id="frm_reply_to_error" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms')); ?></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('Reply to Email', 'ARForms')); ?>&nbsp;&nbsp;<span style="vertical-align:middle" class="arfglobalrequiredfield">*</span></label></td>
                                    <td valign="top" style="padding-bottom:10px;">
                                        <input type="text" class="txtmodal1" id="reply_to_email" name="reply_to_email" value="<?php echo $arfsettings->reply_to_email; ?>" style="width:400px;">
                                        <div class="arferrmessage" id="frm_reply_to_error" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms')); ?></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('Send Email SMTP', 'ARForms')); ?></label> </td>
                                    <td valign="top" style="padding-bottom:10px;">
                                        <div class="arf_radio_wrapper">
                                            <div class="arf_custom_radio_div">
                                                <div class="arf_custom_radio_wrapper">
                                                    <input type="radio" class="arf_custom_radio arf_submit_action" name="frm_smtp_server" id="arf_wordpress_smtp" value="wordpress" <?php checked($arfsettings->smtp_server, 'wordpress'); ?> onchange="arfchangesmtpsetting();"  />
                                                    <svg width="18px" height="18px">
                                                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                                    </svg>
                                                </div>
                                            </div>
                                            <span>
                                                <label for="arf_wordpress_smtp"><?php echo addslashes(esc_html__('WordPress Server', 'ARForms')); ?></label>
                                            </span>
                                        </div>
                                        <div class="arf_radio_wrapper">
                                            <div class="arf_custom_radio_div">
                                                <div class="arf_custom_radio_wrapper">
                                                    <input type="radio" class="arf_custom_radio arf_submit_action" name="frm_smtp_server" id="arf_custom_custom" onchange="arfchangesmtpsetting();" value="custom" <?php checked($arfsettings->smtp_server, 'custom'); ?>  />
                                                    <svg width="18px" height="18px">
                                                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                                    </svg>
                                                </div>
                                            </div>
                                            <span>
                                                <label for="arf_custom_custom"><?php echo addslashes(esc_html__('SMTP Server', 'ARForms')); ?></label>
                                            </span>
                                        </div>
                                        <div class="arf_radio_wrapper">
                                            <div class="arf_custom_radio_div">
                                                <div class="arf_custom_radio_wrapper">
                                                    <input type="radio" class="arf_custom_radio arf_submit_action" name="frm_smtp_server" id="arf_wordpress_phpmailer" value="phpmailer" <?php checked($arfsettings->smtp_server, 'phpmailer'); ?> onchange="arfchangesmtpsetting();"  />
                                                    <svg width="18px" height="18px">
                                                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                                    </svg>
                                                </div>
                                            </div>
                                            <span>
                                                <label for="arf_wordpress_phpmailer"><?php echo addslashes(esc_html__('PHP Mailer', 'ARForms')); ?></label>
                                            </span>
                                        </div>
                                    </td>
                                </tr>

                                <tr>


                                    <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('Email Format', 'ARForms')); ?></label> </td>


                                    <td valign="top" style="padding-bottom:10px;">
                                        <div class="arf_radio_wrapper">
                                            <div class="arf_custom_radio_div" >
                                                <div class="arf_custom_radio_wrapper">
                                                    <input type="radio" name="arf_email_format" id="arf_email_html" class="arf_submit_action arf_custom_radio" value="html" <?php
                                                    if ($arfsettings->arf_email_format == 'html' || $arfsettings->arf_email_format == '') {
                                                        echo 'checked="checked"';
                                                    } else {
                                                        echo '';
                                                    }
                                                    ?> />
                                                    <svg width="18px" height="18px">
                                                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                                    </svg>
                                                </div>
                                            </div>
                                            <span>
                                                <label for="arf_email_html"><?php echo addslashes(esc_html__('HTML', 'ARForms')); ?></label>
                                            </span>
                                        </div>

                                        <div class="arf_radio_wrapper">
                                            <div class="arf_custom_radio_div" >
                                                <div class="arf_custom_radio_wrapper">
                                                    <input type="radio" name="arf_email_format" id="arf_email_plain" class="arf_submit_action arf_custom_radio" value="plain" <?php checked($arfsettings->arf_email_format, 'plain'); ?> />
                                                    <svg width="18px" height="18px">
                                                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                                    </svg>
                                                </div>
                                            </div>
                                            <span>
                                                <label for="arf_email_plain"><?php echo addslashes(esc_html__('Plain Text', 'ARForms')); ?></label>
                                            </span>
                                        </div>

                                    </td>
                                </tr>



                                <tr class="arfsmptpsettings" <?php echo ($arfsettings->smtp_server != 'custom') ? 'style="display:none;"' : ''; ?> >
                                    <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('Authentication', 'ARForms')); ?></label> </td>
                                    <td valign="top" style="padding-bottom:10px;">
                                        <div class="arf_custom_checkbox_div">
                                            <div class="arf_custom_checkbox_wrapper">
                                                <input type="checkbox" class="" onclick="arf_is_smtp_authentication();" id="is_smtp_authentication" name="is_smtp_authentication" value="1" <?php checked($arfsettings->is_smtp_authentication, 1) ?> style="border:none;">
                                                <svg width="18px" height="18px">
                                                <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                                <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                                </svg>
                                            </div>
                                            <span style="margin-left: 5px;"><label for="is_smtp_authentication"><?php echo esc_html__('Enable SMTP authentication', 'ARForms'); ?></label></span>
                                        </div>
                                    </td>
                                </tr>


                                <tr class="arfsmptpsettings" <?php
                                if ($arfsettings->smtp_server != 'custom') {
                                    echo 'style="display:none;"';
                                }
                                ?> >
                                    <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('SMTP Host', 'ARForms')); ?></label></td>
                                    <td valign="top" style="padding-bottom:10px;">
                                        <input type="text" class="txtmodal1" id="frm_smtp_host" name="frm_smtp_host" value="<?php echo $arfsettings->smtp_host; ?>" style="width:400px;">
                                    </td>
                                </tr>

                                <tr class="arfsmptpsettings" <?php
                                if ($arfsettings->smtp_server != 'custom') {
                                    echo 'style="display:none;"';
                                }
                                ?> >
                                    <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('SMTP Port', 'ARForms')); ?></label></td>
                                    <td valign="top" style="padding-bottom:10px;">
                                        <input onkeyup="arf_show_test_mail();" type="text" class="txtmodal1" id="frm_smtp_port" name="frm_smtp_port" value="<?php echo $arfsettings->smtp_port; ?>" style="width:400px;">
                                    </td>
                                </tr>



                                <tr class="arfsmptpsettings arf_authentication_field" <?php
                                if ($arfsettings->smtp_server != 'custom') {
                                    echo 'style="display:none;"';
                                } else {
                                    if ($arfsettings->is_smtp_authentication != '1') {
                                        echo 'style="display:none;"';
                                    }
                                }
                                ?> >
                                    <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php echo esc_html__('SMTP Username', 'ARForms'); ?></label></td>
                                    <td valign="top" style="padding-bottom:10px;">
                                        <input onkeyup="arf_show_test_mail();" type="text" class="txtmodal1" id="frm_smtp_username" name="frm_smtp_username" value="<?php echo $arfsettings->smtp_username; ?>" style="width:400px;">
                                    </td>
                                </tr>


                                <tr class="arfsmptpsettings arf_authentication_field" <?php
                                if ($arfsettings->smtp_server != 'custom') {
                                    echo 'style="display:none;"';
                                } else {
                                    if ($arfsettings->is_smtp_authentication != '1') {
                                        echo 'style="display:none;"';
                                    }
                                }
                                ?> >
                                    <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('SMTP Password', 'ARForms')); ?></label></td>
                                    <td valign="top" style="padding-bottom:10px;">
                                        <input onkeyup="arf_show_test_mail();" type="password" class="txtmodal1" id="frm_smtp_password" name="frm_smtp_password" value="<?php echo $arfsettings->smtp_password; ?>" style="width:400px;">


                                    </td>
                                </tr>


                                <tr class="arfsmptpsettings" <?php
                                if ($arfsettings->smtp_server != 'custom') {
                                    echo 'style="display:none;"';
                                }
                                ?> >
                                    <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('SMTP Encryption', 'ARForms')); ?></label></td>
                                    <td valign="top" style="padding-bottom:10px;">
                                        <div class="arf_radio_wrapper">
                                            <div class="arf_custom_radio_div" >
                                                <div class="arf_custom_radio_wrapper">
                                                    <input type="radio" name="frm_smtp_encryption" id="frm_smtp_encryption_none" class="arf_submit_action arf_custom_radio" value="none" <?php checked($arfsettings->smtp_encryption, 'none'); ?> />
                                                    <svg width="18px" height="18px">
                                                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                                    </svg>
                                                </div>
                                            </div>
                                            <span>
                                                <label for="frm_smtp_encryption_none"><?php echo addslashes(esc_html__('None', 'ARForms')); ?></label>
                                            </span>
                                        </div>
                                        <div class="arf_radio_wrapper">
                                            <div class="arf_custom_radio_div" >
                                                <div class="arf_custom_radio_wrapper">
                                                    <input type="radio" name="frm_smtp_encryption" id="frm_smtp_encryption_ssl" class="arf_submit_action arf_custom_radio" value="ssl" <?php checked($arfsettings->smtp_encryption, 'ssl'); ?> />
                                                    <svg width="18px" height="18px">
                                                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                                    </svg>
                                                </div>
                                            </div>
                                            <span>
                                                <label for="frm_smtp_encryption_ssl"><?php echo addslashes(esc_html__('SSL', 'ARForms')); ?></label>
                                            </span>
                                        </div>
                                        <div class="arf_radio_wrapper">
                                            <div class="arf_custom_radio_div" >
                                                <div class="arf_custom_radio_wrapper">
                                                    <input type="radio" name="frm_smtp_encryption" id="frm_smtp_encryption_tls" class="arf_submit_action arf_custom_radio" value="tls" <?php checked($arfsettings->smtp_encryption, 'tls'); ?> />
                                                    <svg width="18px" height="18px">
                                                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                                    </svg>
                                                </div>
                                            </div>
                                            <span>
                                                <label for="frm_smtp_encryption_tls"><?php echo addslashes(esc_html__('TLS', 'ARForms')); ?></label>
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                                $smtp_test_mail_style = "disabled='disabled'";
                                $smtp_test_main_class = "arfdisabled";

                                if ($arfsettings->is_smtp_authentication == '1') {
                                    if ($arfsettings->smtp_server == "custom" && $arfsettings->smtp_port != "" && $arfsettings->smtp_host != "" && $arfsettings->smtp_username != "" && $arfsettings->smtp_password != "") {
                                        $smtp_test_mail_style = "";
                                        $smtp_test_main_class = "";
                                    } else {
                                        $smtp_test_mail_style = "disabled='disabled'";
                                        $smtp_test_main_class = "arfdisabled";
                                    }
                                } else {
                                    if ($arfsettings->smtp_server == "custom" && $arfsettings->smtp_port != "" && $arfsettings->smtp_host != "") {
                                        $smtp_test_mail_style = "";
                                        $smtp_test_main_class = "";
                                    } else {
                                        $smtp_test_mail_style = "disabled='disabled'";
                                        $smtp_test_main_class = "arfdisabled";
                                    }
                                }
                                ?>
                                <tr class="arfsmptpsettings" <?php
                                if ($arfsettings->smtp_server != 'custom') {
                                    echo 'style="display:none;"';
                                }
                                ?> >
                                    <td class="tdclass" valign="top" style="padding-left:20px;">
                                        <label class="lbltitle">
                                            <?php echo addslashes(esc_html__('Send Test E-mail', 'ARForms')); ?>
                                        </label>
                                    </td>
                                    <td valign="top" style="padding-bottom:10px;">
                                        <label id="arf_success_test_mail"><?php echo addslashes(esc_html__('Your test mail is successfully sent', 'ARForms')); ?> </label>
                                        <label id="arf_error_test_mail"><?php echo addslashes(esc_html__('Your test mail is not sent for some reason, Please check your SMTP setting', 'ARForms')); ?> </label>
                                    </td>
                                </tr>
                                <tr class="arfsmptpsettings" <?php
                                if ($arfsettings->smtp_server != 'custom') {
                                    echo 'style="display:none;"';
                                }
                                ?> >
                                    <td class="tdclass" valign="top" style="padding-left:20px;">
                                        <label class="lblsubtitle">
                                            <?php echo addslashes(esc_html__('To', 'ARForms')); ?>
                                        </label>
                                    </td>
                                    <td valign="top" style="padding-bottom:10px;">
                                        <input type="text" id="sendtestmail_to" name="sendtestmail_to" class="txtmodal1 <?php echo $smtp_test_main_class; ?>" value="<?php echo isset($arfsettings->smtp_send_test_mail_to) ? $arfsettings->smtp_send_test_mail_to : '' ?>" <?php echo $smtp_test_mail_style; ?> />
                                    </td>
                                </tr>

                                <tr class="arfsmptpsettings" <?php
                                if ($arfsettings->smtp_server != 'custom') {
                                    echo 'style="display:none;"';
                                }
                                ?> >
                                    <td class="tdclass" valign="top" style="padding-left:20px;">
                                        <label class="lblsubtitle">
                                            <?php echo addslashes(esc_html__('Message', 'ARForms')); ?>
                                        </label>
                                    </td>
                                    <td valign="top" style="padding-bottom:10px;">
                                        <textarea class="txtmultinew testmailmsg  <?php echo $smtp_test_main_class; ?>" name="sendtestmail_msg" <?php echo $smtp_test_mail_style; ?> id="sendtestmail_msg" ><?php echo isset($arfsettings->smtp_send_test_mail_msg) ? $arfsettings->smtp_send_test_mail_msg : '' ?></textarea>
                                    </td>
                                </tr>

                                <tr class="arfsmptpsettings" <?php
                                if ($arfsettings->smtp_server != 'custom') {
                                    echo 'style="display:none;"';
                                }
                                ?> >
                                    <td class="tdclass" valign="top" style="padding-left:20px;">
                                        <label class="lblsubtitle">&nbsp;</label>
                                    </td>
                                    <td valign="top" style="padding-bottom:10px;">
                                        <input type="button" value="<?php echo addslashes(esc_html__('Send test mail', 'ARForms')); ?>" class="rounded_button arf_btn_dark_blue <?php echo $smtp_test_main_class; ?>" id="arf_send_test_mail" <?php echo $smtp_test_mail_style; ?> style="<?php echo (is_rtl()) ? 'margin-right: -4px;' : 'margin-left: -4px;'; ?>color:#ffffff;width: 118px !important;"> <img alt='' src="<?php echo ARFIMAGESURL . '/ajax_loader_gray_32.gif'; ?>" id="arf_send_test_mail_loader" style="display:none;position:relative;left:5px;top:5px;" width="16" height="16" /> <span  class="lblnotetitle">(<?php echo addslashes(esc_html__('Test e-mail works only after configure SMTP server settings', 'ARForms')); ?>)</span>
                                    </td>
                                </tr>
                                <tr class="arfmainformfield" valign="top">
                                    <td colspan="2"><div style="width:96%" class="dotted_line"></div></td>
                                </tr>


                                <tr class="arfmainformfield">
                                    <td valign="top" colspan="2" class="lbltitle titleclass"><?php echo addslashes(esc_html__('Other Settings', 'ARForms')); ?></td>
                                </tr>

                                <tr>

                                    <?php if ($setvaltolic == 1) { ?>
                                    <tr>


                                        <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php echo esc_html__('Rebranding', 'ARForms'); ?></label> </td>


                                        <td valign="top" style="padding-bottom:10px;">
                                            <div class="arf_custom_checkbox_div">
                                                <div class="arf_custom_checkbox_wrapper">
                                                    <input type="checkbox" name="arfmainformbrand" id="arfmainformbrand" value="1" <?php checked($arfsettings->brand, 1) ?> />
                                                    <svg width="18px" height="18px">
                                                    <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                                    <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                                    </svg>
                                                </div>
                                                <span style="margin-left: 5px;"><label for="arfmainformbrand"><?php echo esc_html__('Remove rebranding link', 'ARForms'); ?></label></span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php echo esc_html__('Affiliate Code', 'ARForms'); ?></label> </td>
                                        <td valign="top" style="padding-bottom:10px;">
                                            <input type="text" class="txtmodal1" id="affiliate_code" name="affiliate_code" value="<?php echo $arfsettings->affiliate_code; ?>" style="width:400px;">
                                        </td>
                                    </tr>

                                <?php } else { ?>
                                    <input type="hidden" name="arfmainformbrand" value="0"  />
                                <?php } ?>
                               
                                <tr>
                                    <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('Form Submission Method', 'ARForms')); ?></label> </td>

                                    <td valign="top" style="padding-bottom:10px;">
                                        <div class="arf_radio_wrapper">
                                            <div class="arf_custom_radio_div" >
                                                <div class="arf_custom_radio_wrapper">
                                                    <input type="radio" onchange="arf_change_form_submission_type(this);" name="arfmainformsubmittype" id="ajax_base_sbmt" class="arf_submit_action arf_custom_radio" value="1" <?php
                                                    if ($arfsettings->form_submit_type == 1) {
                                                        echo 'checked="checked"';
                                                    } else {
                                                        echo '';
                                                    }
                                                    ?> />
                                                    <svg width="18px" height="18px">
                                                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                                    </svg>
                                                </div>
                                            </div>
                                            <span>
                                                <label for="ajax_base_sbmt"><?php echo addslashes(esc_html__('Ajax based submission', 'ARForms')); ?></label>
                                            </span>
                                        </div>
                                        <div class="arf_radio_wrapper">
                                            <div class="arf_custom_radio_div" >
                                                <div class="arf_custom_radio_wrapper">
                                                    <input type="radio" onchange="arf_change_form_submission_type(this);" name="arfmainformsubmittype" id="normal_form_sbmt" class="arf_submit_action arf_custom_radio" value="0" <?php if ($arfsettings->form_submit_type == 0) echo 'checked="checked"'; ?> />
                                                    <svg width="18px" height="18px">
                                                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                                    </svg>
                                                </div>
                                            </div>
                                            <span>
                                                <label for="normal_form_sbmt"><?php echo addslashes(esc_html__('Normal submission', 'ARForms')); ?></label>
                                            </span>
                                        </div>
                                    </td>
                                </tr>

                                <tr class="arf_success_message_show_time_wrapper" <?php
                                    if ($arfsettings->form_submit_type == 0) {
                                        echo 'style="display: none"';
                                    }
                                    ?> >


                                    <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('Hide success message after', 'ARForms')); ?></label> </td>


                                    <td valign="top" style="padding-bottom:10px;">
                                        <?php
                                        if (!(isset($arfsettings->arf_success_message_show_time) && $arfsettings->arf_success_message_show_time >= 0)) {
                                            $arfsettings->arf_success_message_show_time = 3;
                                        }
                                        ?>
                                        <div class="arf_success_message_show_time_inner">
                                            <input type="text" name="arf_success_message_show_time" onkeydown="arfvalidatenumber_admin(this, event);" maxlength="3" value="<?php echo esc_attr($arfsettings->arf_success_message_show_time) ?>" class="arf_success_message_show_time txtmodal1" class="arf_small_width_txtbox arfcolor" style="width:8% !important"/>
                                            <?php echo addslashes(esc_html__('seconds', 'ARForms')).'&nbsp;&nbsp;'; ?>
                                            
                                            <span class="arf_success_message_show_time_inner" style="margin-top: 10px;">( <?php echo esc_html__('Note : 0 ( zero ) means it will never hide success message', 'ARForms'); ?> )</span>
                                        
                                        </div>

                                    </td>


                                </tr>

                                <tr class="arfmainformfield" valign="top">
                                    <td class="tdclass">
                                        <label class="lblsubtitle"><?php echo addslashes(esc_html__('Decimal separator', 'ARForms')); ?></label>
                                    </td>
                                    <td style="padding-bottom:10px;">
                                        <?php
                                        $responder_list_option = '';
                                        $selected_list_id = '.';
                                        $selected_list_label = addslashes(esc_html__('Dot (.)','ARForms'));

                                        foreach (array('.' => addslashes(esc_html__('Dot (.)', 'ARForms')), ',' => addslashes(esc_html__('Comma (,)', 'ARForms')), '' => addslashes(esc_html__('No Separator', 'ARForms'))) as $decimal_value => $decimal_name) {

                                            if (isset($arfsettings->decimal_separator) && $arfsettings->decimal_separator == $decimal_value) {
                                                $selected_list_id = esc_attr($decimal_value);
                                                $selected_list_label = $decimal_name;
                                            }

                                            $responder_list_option .= '<li class="arf_selectbox_option" data-value="' . esc_attr($decimal_value) . '" data-label="' . $decimal_name . '">' . $decimal_name . '</li>';
                                            ?>
                                        <?php } ?>

                                        <div class="sltstandard" style="float:none;">
                                            <input id="decimal_separator" name="decimal_separator" value="<?php echo $selected_list_id; ?>" type="hidden" class="frm-dropdown frm-pages-dropdown">
                                            <dl class="arf_selectbox" data-name="decimal_separator" data-id="decimal_separator" style="width:229px;">
                                                <dt><span><?php echo $selected_list_label; ?></span>
                                                <svg viewBox="0 0 2000 1000" width="15px" height="15px">
                                                <g fill="#000">
                                                <path d="M1024 320q0 -26 -19 -45t-45 -19h-896q-26 0 -45 19t-19 45t19 45l448 448q19 19 45 19t45 -19l448 -448q19 -19 19 -45z"></path>
                                                </g>
                                                </svg>
                                                </dt>
                                                <dd>
                                                    <ul class="field_dropdown_menu field_dropdown_list_menu" style="display: none;" data-id="decimal_separator">
                                                        <?php echo $responder_list_option; ?>
                                                    </ul>
                                                </dd>
                                            </dl>
                                        </div>
                                    </td>
                                </tr>


                                <tr>

                                    <td class="tdclass" valign="top" style="padding-left:30px; vertical-align:top;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('Select character sets for google fonts', 'ARForms')); ?></label> </td>

                                    <td valign="top" style="padding-bottom:10px;">

                                        <?php
                                        $arf_character_arr = array('latin' => 'Latin', 'latin-ext' => 'Latin-ext', 'menu' => 'Menu', 'greek' => 'Greek', 'greek-ext' => 'Greek-ext', 'cyrillic' => 'Cyrillic',
                                            'cyrillic-ext' => 'Cyrillic-ext', 'vietnamese' => 'Vietnamese', 'arabic' => 'Arabic', 'khmer' => 'Khmer', 'lao' => 'Lao', 'tamil' => 'Tamil', 'bengali' => 'Bengali',
                                            'hindi' => 'Hindi', 'korean' => 'Korean');
                                        ?>
                                        <div style=" <?php echo (is_rtl()) ? 'float:right;width:465px;' : 'width:455px;float:left;'; ?>">
                                            <span style="width:100%; float:left;height: 35px;">
                                                <?php $arf_chk_counter = 1; ?>
                                                <?php
                                                foreach ($arf_character_arr as $arf_character => $arf_character_value) {
                                                    
                                                    $default_charset = "";
                                                    if( isset($arfsettings->arf_css_character_set) ){
                                                        if( is_object($arfsettings->arf_css_character_set) ){
                                                            $default_charset = isset($arfsettings->arf_css_character_set->$arf_character) ? $arfsettings->arf_css_character_set->$arf_character : '';
                                                        } else if( is_array($arfsettings->arf_css_character_set) ){
                                                            $default_charset = ( isset($arfsettings->arf_css_character_set[$arf_character]) ) ? $arfsettings->arf_css_character_set[$arf_character] : '';
                                                        } else {
                                                            $default_charset = "";
                                                        }
                                                    }
                                                    ?>
                                                    <div class="arf_custom_checkbox_div" style="width: 110px;">
                                                        <div class="arf_custom_checkbox_wrapper">
                                                            <input type="checkbox" id="arf_character_<?php echo $arf_character; ?>" name="arf_css_character_set[<?php echo $arf_character; ?>]" <?php checked($default_charset, $arf_character); ?> value="<?php echo $arf_character; ?>" />
                                                            <svg width="18px" height="18px">
                                                            <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                                            <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                                            </svg>
                                                        </div>
                                                        <span style="margin-left: 5px;"><label for="arf_character_<?php echo $arf_character; ?>"><?php echo $arf_character_value; ?></label></span>
                                                    </div>
                                                    <?php echo ($arf_chk_counter % 4 == 0) ? '</span><span style="width:100%; float:left;height:35px;">' : ''; ?>
                                                    <?php $arf_chk_counter++; ?>
                                                <?php } ?>
                                            </span>
                                        </div>
                                    </td>

                                </tr>

                                <tr>

                                    <td class="tdclass" valign="top" style="padding-left:30px;"><label style="margin-top:-100px;display:block;" class="lblsubtitle"><?php echo addslashes(esc_html__('Form Global CSS', 'ARForms')); ?></label> </td>

                                    <td valign="top" style="padding-bottom:10px;"><div class="arf_gloabal_css_wrapper"><textarea name="arf_global_css" id="arf_global_css" class="txtmultinew"><?php echo stripslashes_deep(get_option('arf_global_css')); ?></textarea></div></td>

                                </tr>

                                <tr>
                                    <td class="tdclass" valign="top" style="padding-left:30px;padding-top: 20px;vertical-align: top"><label class="lblsubtitle"><?php echo esc_html__('File Upload Path :', 'ARForms'); ?></label> </td>
                                    <td valign="top" style="padding-bottom:10px;padding-top: 20px;">
                                        <span><?php echo ABSPATH; ?></span>
                                        <input type="text" class="txtmodal1" id="arf_file_uplod_dir_path" name="arf_file_uplod_dir_path" value="<?php echo isset($arfsettings->arf_file_uplod_dir_path) ? $arfsettings->arf_file_uplod_dir_path : 'wp-content/uploads/arforms/userfiles'; ?>" style="width:400px;">
                                        <br/><br/>
                                        <span style="color:#f20000;"><?php esc_html_e('Recommended for advanced user only. Please make sure that the upload directory you have set is writable, otherwise file upload will not work.','ARForms'); ?></span>
                                        <br/><br/>
                                        <span>{form_id} : <?php esc_html_e('this shortcode will replace with the form id','ARForms'); ?> </span>
                                        <br/>
                                        <br/>
                                        <span>{year} : <?php esc_html_e('this shortcode will replace with current year.','ARForms'); ?></span>
                                        <br/>
                                        <br/>
                                        <span>{month} : <?php esc_html_e('this shortcode will replace with current month','ARForms'); ?></span>
                                        <br/>
                                        <br/>
                                        <span>{day} : <?php esc_html_e('this shortcode will replace with current day','ARForms'); ?></span>
                                    </td>
                                </tr>

                                <tr class="arfmainformfield" valign="top">
                                    <td colspan="2"><div style="width:96%" class="dotted_line"></div></td>
                                </tr>
                                <tr class="arfmainformfield">
                                    <td valign="top" colspan="2" class="lbltitle titleclass"><?php echo esc_html__('Load JS & CSS in all pages', 'ARForms'); ?></td>
                                </tr>

                                <tr class="arfmainformfield" valign="top">

                                    <td colspan="2" style="padding-left:30px; padding-bottom:20px;">


                                        <label class="lblsubtitle">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo stripslashes(addslashes(esc_html__('( Not recommended - If you have any js/css loading issue in your theme, only in that case you should enable this settings )', 'ARForms'))); ?></label>


                                    </td>

                                </tr>



                                <tr>



                                    <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php echo esc_html__('Load JS & CSS', 'ARForms'); ?></label> </td>
                                    <td valign="top" style="padding-bottom:10px;">
                                        <div class="arf_js_switch_wrapper">
                                            <input type="checkbox" class="js-switch" name="frm_arfmainformloadjscss" value="1" <?php checked($arfsettings->arfmainformloadjscss, 1) ?> onchange="change_load_js_css_wrapper(this);" />
                                            <span class="arf_js_switch"></span>
                                        </div>
                                        <label class="arf_js_switch_label"><span>&nbsp;<?php echo addslashes(esc_html__('Enable', 'ARForms')); ?></span></label>
                                    </td>
                                </tr>
                                <tr class="arf_global_js_css_wrapper_show" <?php
                                    if ($arfsettings->arfmainformloadjscss) {
                                        echo 'style="display:table-row;"';
                                    } else {
                                        echo 'style="display:none;"';
                                    }
                                    ?> >


                                        <td></td>
                                        <td>
                                            <div  style="<?php echo (is_rtl()) ? 'float:right;' : 'float:left;'; ?>">
                                                
                                                    <?php
                                                    $i = 1;
                                                    $js_css_array = $arformcontroller->arf_field_wise_js_css();


                                                    foreach ($js_css_array as $key => $value) {
                                                        ?>
                                                        <div class="arf_custom_checkbox_div arf_load_js_css_option_wrapper" style="margin-bottom:10px;">
                                                            <div class="arf_custom_checkbox_wrapper">
                                                                <input type="checkbox" id="arf_all_<?php echo $key; ?>" name="arf_load_js_css[]" value="<?php echo $key; ?>" <?php
                                                                if (in_array($key, $arfsettings->arf_load_js_css)) {
                                                                    echo 'checked="checked"';
                                                                }
                                                                ?>  />
                                                                <svg width="18px" height="18px">
                                                                <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                                                <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                                                </svg>
                                                            </div>
                                                            <span style="<?php echo (is_rtl()) ? '' : 'margin-left: 5px;'; ?>"><label for="arf_all_<?php echo $key; ?>"><?php echo $value['title']; ?></label></span>
                                                        </div>
                                                        <?php
                                                        
                                                        $i++;
                                                    }
                                                    ?>
                                           
                                            </div>


                                        </td>

                                </tr>
                                <?php global $wp_version; ?>
                                <?php do_action('arf_outside_global_setting_block', $arfsettings, $setvaltolic); ?>

                                <input type="hidden" id="frm_permalinks" name="frm_permalinks" value="0" />

                            </table>


                        </div>


                        <div id="autoresponder_settings" style=" <?php if ($setting_tab != 'autoresponder_settings') echo 'display:none;'; ?> background-color:#FFFFFF; padding-top:10px; border-radius:5px 5px 5px 5px;-webkit-border-radius:5px 5px 5px 5px;-o-border-radius:5px 5px 5px 5px;-moz-border-radius:5px 5px 5px 5px; padding-left: 20px; padding-top: 30px; padding-bottom:1px;">

                            <span style="position: absolute;right: 30px;">
                                <a href="<?php echo ARFURL; ?>/documentation/index.html#congih_email_mark" target="_blank" title="" class="arfa arfa-life-bouy arf_adminhelp_icon " >
                                    <svg width="30px" height="30px" viewBox="0 0 26 32" class="arfsvgposition arfhelptip tipso_style" data-tipso="help" title="help">
                                    <?php echo ARF_LIFEBOUY_ICON;?>
                                    </svg>
                                    
                                </a>
                            </span>

                            <table class="wp-list-table widefat post " style="margin:0px 0 0 10px; border:none;">


                                <tr>

                                    <th style="background:none; border:0px;" width="18%">&nbsp;</th>
                                    <th style="background:none; border:0px;height:98px;" colspan="2"><img alt='' src="<?php echo ARFURL; ?>/images/aweber.png" align="absmiddle" /></th>
                                </tr>
                                <tr>

                                    <?php $autores_type['aweber_type'] = ($autores_type['aweber_type'] != '') ? $autores_type['aweber_type'] : 1; ?>
                                    <th style="background:none; border:0px;" width="18%">&nbsp;</th>
                                    <th id="th_aweber" style=" background:none; border:none; <?php
                                    if ($autores_type['aweber_type'] == 2)
                                        echo 'padding-left: 5px;';
                                    else
                                        echo 'padding-left: 5px;';
                                    ?>">
                                <div class="arf_radio_wrapper">
                                    <div class="arf_custom_radio_div" >
                                        <div class="arf_custom_radio_wrapper">
                                            <input type="radio" class="arf_submit_action arf_custom_radio" id="aweber_1" <?php if ($autores_type['aweber_type'] == 1) echo 'checked="checked"'; ?> name="aweber_type" value="1" style="margin-top:3px;" onclick="show_api('aweber');"  />
                                            <svg width="18px" height="18px">
                                            <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                            <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                            </svg>
                                        </div>
                                    </div>
                                    <span>
                                        <label for="aweber_1"><?php echo esc_html__('Using API', 'ARForms'); ?></label>
                                    </span>
                                </div>
                        </div>
                        <div class="arf_radio_wrapper">
                            <div class="arf_custom_radio_div" >
                                <div class="arf_custom_radio_wrapper">
                                    <input type="radio" class="arf_submit_action arf_custom_radio" id="aweber_2" <?php if ($autores_type['aweber_type'] == 0) echo 'checked="checked"'; ?> name="aweber_type" value="0" style="margin-top:3px;" onclick="show_web_form('aweber');" />
                                    <svg width="18px" height="18px">
                                    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                    </svg>
                                </div>
                            </div>
                            <span>
                                <label for="aweber_2"><?php echo addslashes(esc_html__('Using Web-form', 'ARForms')); ?></label>
                            </span>
                        </div>
                </div>
                <input type="hidden" name="aweber_status" id="aweber_status" value="<?php echo $aweber_data->is_verify; ?>" />

                </th>
                </tr>
                <?php
                    $is_aweber_old = false;

                    if( ($aweber_data->consumer_key != '' && $aweber_data->consumer_secret != '' && ($aweber_data->consumer_key != ARF_AWEBER_CONSUMER_KEY || $aweber_data->consumer_secret != ARF_AWEBER_CONSUMER_SECRET)) ){
                        $is_aweber_old = true;
                    }
                ?>
                <tr id="aweber_api_tr3" <?php
                if ($aweber_data->is_verify == '1' && false == $is_aweber_old ) {
                    echo 'style="display:none;"';
                } else if ($autores_type['aweber_type'] != 1) {
                    echo 'style="display:none;"';
                }
                ?>>

                    <td class="tdclass" style="padding-left:20px; text-align:left; width:18%;">&nbsp;</td>
                    <?php
                        $aweber_onclick = 'aweber_continue(\''.ARFAWEBERURL.'\')';
                        if( $setvaltolic != 1 ){
                            $aweber_onclick = 'alert(\'Please activate license to authorize Aweber app\');';
                        }
                    ?>
                    <td style="padding-left:4px;"><button class="rounded_button arf_btn_dark_blue"  style="width:103px !important; border:0px; color:#FFFFFF; height:41px;" type="button" name="continue" onclick="<?php echo $aweber_onclick; ?>"><?php echo addslashes(esc_html__('Authorize', 'ARForms')); ?></button></td>

                </tr>

                <tr id="aweber_web_form_tr" <?php if ($autores_type['aweber_type'] != 0) echo 'style="display:none;"'; ?>>

                    <td class="tdclass" style="padding-right:20px; text-align:left; width:18%;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('Webform code from Aweber', 'ARForms')); ?></label></td>

                    <td style="padding-left:5px;">

                        <textarea <?php
                        if ($setvaltolic != 1) {
                            echo "readonly=readonly";
                        }
                        ?> name="aweber_web_form" id="aweber_web_form" class="txtmultinew" <?php if ($setvaltolic != 1) { ?> onclick="alert('Please activate license to set aweber settings');" <?php } ?>><?php echo stripslashes($aweber_data->responder_web_form); ?></textarea>


                    </td>

                </tr>


                <?php if ($aweber_data->responder_list_id != "" && false == $is_aweber_old ) { ?>


                    <tr id="aweber_api_tr4" <?php if ($autores_type['aweber_type'] != 1) echo 'style="display:none;"'; ?>>


                        <td class="tdclass" style="padding-right:20px; text-align:left; width:18%;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('AWEBER LIST', 'ARForms')); ?></label></td>


                        <td style="padding-left:5px; overflow: visible;">

                            <span id="select_aweber">
                                <div class="sltstandard" style="float:none; display:inline;">
                                    


                                    <?php
                                    $aweber_lists = explode("-|-", $aweber_data->responder_list_id);


                                    $aweber_lists_name = explode("|", $aweber_lists[0]);


                                    $aweber_lists_id = explode("|", $aweber_lists[1]);


                                    $i = 0;

                                    $aweber_responder_list_option = "";
                                    foreach ($aweber_lists_name as $aweber_lists_name1) {


                                        if ($aweber_lists_id[$i] != "") {
                                            if ($aweber_lists_id[$i] == $aweber_data->responder_list) {
                                                $selected_list_id = $aweber_lists_id[$i];
                                                $selected_list_label = $aweber_lists_name1;
                                            }
                                            


                                            $aweber_responder_list_option .= '<li class="arf_selectbox_option" data-label="'.$aweber_lists_name1.'" data-value="'.$aweber_lists_id[$i].'" value="'.$aweber_lists_id[$i].'">'.$aweber_lists_name1.'</li>';

                                            
                                        } ?>


                                        <?php
                                        $i++;
                                    }
                                    ?>


                                    
                                    <input name="responder_list" id="aweber_listid" value="<?php echo $selected_list_id; ?>" type="hidden" class="frm-dropdown frm-pages-dropdown">
                                    <dl class="arf_selectbox" data-name="aweber_listid" data-id="aweber_listid" style="width: 400px;">
                                        <dt>
                                            <span><?php echo $selected_list_label; ?></span>
                                            <svg viewBox="0 0 2000 1000" width="15px" height="15px">
                                                <g fill="#000">
                                                <path d="M1024 320q0 -26 -19 -45t-45 -19h-896q-26 0 -45 19t-19 45t19 45l448 448q19 19 45 19t45 -19l448 -448q19 -19 19 -45z"></path>
                                                </g>
                                            </svg>
                                        </dt>
                                        <dd>
                                            <ul class="field_dropdown_menu field_dropdown_list_menu" style="display: none;" data-id="aweber_listid">
                                                <?php echo $aweber_responder_list_option; ?>
                                            </ul>
                                        </dd>
                                        <span id="aweber_loader2"><div class="arf_imageloader"></div></span>
                                    </dl>
                                </div>

                            </span>

                            <div style="padding-left:5px; margin-top: 10px;" class="arlinks">
                                <a href="javascript:void(0);" onclick="action_aweber('refresh');"><?php echo addslashes(esc_html__('Refresh List', 'ARForms')); ?></a>
                                &nbsp;  &nbsp;  &nbsp;  &nbsp;
                                <a href="javascript:void(0);" onclick="action_aweber('delete');"><?php echo addslashes(esc_html__('Delete Configuration', 'ARForms')); ?></a>
                            </div>


                        </td>


                    </tr>




                <?php } ?>

                <tr>
                    <td colspan="2" style="padding-left:5px;"><div class="dotted_line" style="width:96%"></div></td>
                </tr>
                </table>


                <table class="wp-list-table widefat post " style="margin:20px 0 0 10px; border:none;">

                    <tr>
                        <th style="background:none; border:0px;" width="18%">&nbsp;</th>
                        <th style="background:none; border:none;height:98px;" colspan="2"><img alt='' src="<?php echo ARFURL; ?>/images/mailchimp.png" align="absmiddle" /></th>

                        </th>

                    </tr>

                    <tr>
                        <?php $autores_type['mailchimp_type'] = ( $autores_type['mailchimp_type'] != '' ) ? $autores_type['mailchimp_type'] : 1; ?>
                        <th style="width:18%; background:none; border:none;">&nbsp;</th>
                        <th id="th_mailchimp" style=" background:none; border:none; padding-left:5px;">
                    <div class="arf_radio_wrapper">
                        <div class="arf_custom_radio_div" >
                            <div class="arf_custom_radio_wrapper">
                                <input type="radio" class="arf_submit_action arf_custom_radio" id="mailchimp_1" <?php if ($autores_type['mailchimp_type'] == 1) echo 'checked="checked"'; ?> name="mailchimp_type" value="1" style="margin-top:3px;" onclick="show_api('mailchimp');" />
                                <svg width="18px" height="18px">
                                <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                </svg>
                            </div>
                        </div>
                        <span>
                            <label for="mailchimp_1"><?php echo esc_html__('Using API', 'ARForms'); ?></label>
                        </span>
                    </div>
                    <div class="arf_radio_wrapper">
                        <div class="arf_custom_radio_div" >
                            <div class="arf_custom_radio_wrapper">
                                <input type="radio" class="arf_submit_action arf_custom_radio" id="mailchimp_2" <?php if ($autores_type['mailchimp_type'] == 0) echo 'checked="checked"'; ?>  name="mailchimp_type" value="0" style="margin-top:3px;" onclick="show_web_form('mailchimp');" />
                                <svg width="18px" height="18px">
                                <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                </svg>
                            </div>
                        </div>
                        <span>
                            <label for="mailchimp_2"><?php echo addslashes(esc_html__('Using Web-form', 'ARForms')); ?></label>
                        </span>
                    </div>
                    </th>
                    </tr>

                    <tr id="mailchimp_api_tr1" <?php if ($autores_type['mailchimp_type'] != 1) echo 'style="display:none;"'; ?>>

                        <td class="tdclass" style="width:18%; padding-right:20px; padding-bottom:3px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('API Key', 'ARForms')); ?></label></td>

                        <td style="padding-bottom:3px; padding-left:5px;"><input type="text" name="mailchimp_api" class="txtmodal1" <?php
                            if ($setvaltolic != 1) {
                                echo "readonly=readonly";
                            }
                            ?> <?php if ($setvaltolic != 1) { ?> onclick="alert('Please activate license to set mailchimp settings');" <?php } ?> id="mailchimp_api" size="80" onkeyup="show_verify_btn('mailchimp');" value="<?php echo $mailchimp_data->responder_api_key; ?>" /> &nbsp; &nbsp;
                            <span id="mailchimp_link" <?php if ($mailchimp_data->is_verify == 1) { ?>style="display:none;"<?php } ?>><a href="javascript:void(0);" onclick="verify_autores('mailchimp', '0');" class="arlinks"><?php echo addslashes(esc_html__('Verify', 'ARForms')); ?></a></span>
                            <span id="mailchimp_loader" style="display:none;"><div class="arf_imageloader" style="float: none !important;display:inline-block !important; "></div></span>
                            <span id="mailchimp_verify" class="frm_verify_li" style="display:none;"><?php echo addslashes(esc_html__('Verified', 'ARForms')); ?></span>
                            <span id="mailchimp_error" class="frm_not_verify_li" style="display:none;"><?php echo addslashes(esc_html__('Not Verified', 'ARForms')); ?></span>
                            <input type="hidden" name="mailchimp_status" id="mailchimp_status" value="<?php echo $mailchimp_data->is_verify; ?>" />
                            <div class="arferrmessage" id="mailchimp_api_error" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms')); ?></div></td>

                    </tr>


                    <tr id="mailchimp_api_tr2" <?php if ($autores_type['mailchimp_type'] != 1) echo 'style="display:none;"'; ?>>

                        <td class="tdclass" style="width:18%; padding-right:20px; padding-top:3px; padding-bottom:3px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('List ID', 'ARForms')); ?></label></td>

                        <td style=" padding-top:3px; padding-bottom:3px; padding-left:5px; overflow: visible;"><span id="select_mailchimp">
                                <div class="sltstandard" style="float:none;display:inline;">
                                    <?php
                                    $responder_list_option = '';
                                    $selected_list_label = esc_html__('Nothing Selected','ARForms');
                                    $selected_list_id = '';
                                    $lists = json_decode($mailchimp_data->responder_list_id,true);
                                    if ($lists != '' and count($lists) > 0) {
                                        foreach ($lists as $key => $list) {
                                            if ($mailchimp_data->responder_list != '') {
                                                if ($mailchimp_data->responder_list == $list['id']) {
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
                                    ?>
                                    <input name="mailchimp_listid" id="mailchimp_listid" value="<?php echo $selected_list_id; ?>" type="hidden" class="frm-dropdown frm-pages-dropdown">
                                    <dl class="arf_selectbox" data-name="mailchimp_listid" data-id="mailchimp_listid" style="width: 400px;">
                                        <dt><span><?php echo $selected_list_label; ?></span>
                                        <svg viewBox="0 0 2000 1000" width="15px" height="15px">
                                        <g fill="#000">
                                        <path d="M1024 320q0 -26 -19 -45t-45 -19h-896q-26 0 -45 19t-19 45t19 45l448 448q19 19 45 19t45 -19l448 -448q19 -19 19 -45z"></path>
                                        </g>
                                        </svg></dt>
                                        <dd>
                                            <ul class="field_dropdown_menu field_dropdown_list_menu" style="display: none;" data-id="mailchimp_listid">
                                                <?php echo $responder_list_option; ?>
                                            </ul>
                                        </dd>
                                    </dl>
                                </div></span>



                            <div id="mailchimp_del_link" style="padding-left:5px; margin-top:10px;<?php if ($mailchimp_data->is_verify == 0) { ?>display:none;<?php } ?>" class="arlinks">
                                <a href="javascript:void(0);" onclick="action_autores('refresh', 'mailchimp');"><?php echo addslashes(esc_html__('Refresh List', 'ARForms')); ?></a>
                                &nbsp;  &nbsp;  &nbsp;  &nbsp;
                                <a href="javascript:void(0);" onclick="action_autores('delete', 'mailchimp');"><?php echo addslashes(esc_html__('Delete Configuration', 'ARForms')); ?></a>
                            </div>


                        </td>

                    </tr>

                    <tr id="mailchimp_web_form_tr" <?php if ($autores_type['mailchimp_type'] != 0) echo 'style="display:none;"'; ?>>

                        <td class="tdclass" style="width:18%; padding-right:20px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('Webform code from Mailchimp', 'ARForms')); ?></label></td>

                        <td style="padding-left:5px;">

                            <textarea <?php
                            if ($setvaltolic != 1) {
                                echo "readonly=readonly";
                            }
                            ?> name="mailchimp_web_form" id="mailchimp_web_form" class="txtmultinew" <?php if ($setvaltolic != 1) { ?> onclick="alert('Please activate license to set mailchimp settings');" <?php } ?>><?php echo stripslashes($mailchimp_data->responder_web_form); ?></textarea>



                        </td>

                    </tr>

                    <tr>
                        <td colspan="2" style="padding-left:5px;"><div class="dotted_line" style="width:96%"></div></td>
                    </tr>

                </table>


                <table class="wp-list-table widefat post " style="margin:20px 0 0 10px; border:none;">

                    <tr>
                        <th style="background:none; border:0px;" width="18%">&nbsp;</th>
                        <th colspan="2" style="border:none; background:none;height:98px;"><img alt='' src="<?php echo ARFURL; ?>/images/getresponse.png" align="absmiddle" /></th>

                    </tr>

                    <tr>
                        <?php $autores_type['getresponse_type'] = ( $autores_type['getresponse_type'] != '' ) ? $autores_type['getresponse_type'] : 1; ?>
                        <th style="width:18%;  border:none; background:none;"></th>
                        <th id="th_getresponse" style=" padding-left:5px; border:none; background:none;">
                    <div class="arf_radio_wrapper">
                        <div class="arf_custom_radio_div" >
                            <div class="arf_custom_radio_wrapper">
                                <input type="radio" class="arf_submit_action arf_custom_radio" id="getresponse_1" <?php if ($autores_type['getresponse_type'] == 1) echo 'checked="checked"'; ?> name="getresponse_type" value="1" onclick="show_api('getresponse');" />
                                <svg width="18px" height="18px">
                                <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                </svg>
                            </div>
                        </div>
                        <span>
                            <label for="getresponse_1"><?php echo esc_html__('Using API', 'ARForms'); ?></label>
                        </span>
                    </div>

                    <div class="arf_radio_wrapper">
                        <div class="arf_custom_radio_div" >
                            <div class="arf_custom_radio_wrapper">
                                <input type="radio" class="arf_submit_action arf_custom_radio"  <?php if ($autores_type['getresponse_type'] == 0) echo 'checked="checked"'; ?> id="getresponse_2" name="getresponse_type" value="0" onclick="show_web_form('getresponse');"/>
                                <svg width="18px" height="18px">
                                <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                </svg>
                            </div>
                        </div>
                        <span>
                            <label for="getresponse_2"><?php echo addslashes(esc_html__('Using Web-form', 'ARForms')); ?></label>
                        </span>
                    </div>
                    </th>
                    </tr>

                    <tr id="getresponse_api_tr1" <?php if ($autores_type['getresponse_type'] != 1) echo 'style="display:none;"'; ?>>


                        <td class="tdclass" style="width:18%; padding-right:20px; padding-bottom:3px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('API Key', 'ARForms')); ?></label></td>


                        <td style=" padding-bottom:3px; padding-left:5px;"><input type="text" name="getresponse_api" class="txtmodal1" id="getresponse_api" size="80" <?php
                            if ($setvaltolic != 1) {
                                echo "readonly=readonly";
                            }
                            ?> <?php if ($setvaltolic != 1) { ?> onclick="alert('Please activate license to set Getresponse settings');" <?php } ?> onkeyup="show_verify_btn('getresponse');" value="<?php echo $getresponse_data->responder_api_key; ?>" /> &nbsp; &nbsp;

                            <span id="getresponse_link" <?php if ($getresponse_data->is_verify == 1) { ?> style="display:none;"<?php } ?>><a href="javascript:void(0);" onclick="verify_autores('getresponse', '0');" class="arlinks"><?php echo addslashes(esc_html__('Verify', 'ARForms')); ?></a></span>
                            <span id="getresponse_loader" style="display:none;"><div class="arf_imageloader" style="float: none !important;display:inline-block !important; "></div></span>
                            <span id="getresponse_verify" class="frm_verify_li" style="display:none;"><?php echo addslashes(esc_html__('Verified', 'ARForms')); ?></span>
                            <span id="getresponse_error" class="frm_not_verify_li" style="display:none;"><?php echo addslashes(esc_html__('Not Verified', 'ARForms')); ?></span>
                            <input type="hidden" name="getresponse_status" id="getresponse_status" value="<?php echo $getresponse_data->is_verify; ?>" />
                            <div class="arferrmessage" id="getresponse_api_error" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms')); ?></div></td>


                    </tr>


                    <tr id="getresponse_api_tr2" <?php if ($autores_type['getresponse_type'] != 1) echo 'style="display:none;"'; ?>>


                        <td class="tdclass" style="width:18%; padding-right:20px; padding-top:3px; padding-bottom:3px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('Campaign Name', 'ARForms')); ?></label></td>


                        <td style=" padding-top:3px; padding-bottom:3px; padding-left:5px; overflow: visible;"><span id="select_getresponse">
                                <div class="sltstandard" style="float:none;display:inline;">
                                    <?php
                                    $responder_list_option = '';
                                    $selected_list_label = esc_html__('Nothing Selected','ARForms');
                                    $selected_list_id = '';
                                    $lists = maybe_unserialize($getresponse_data->list_data);
                                    if ($lists != '' and count($lists) > 0 ) {
                                        foreach ($lists as $key => $list) {
                                            if ($getresponse_data->responder_list_id != '') {
                                                if ($getresponse_data->responder_list_id == $list['id']) {
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
                                    ?>
                                    <input name="getresponse_listid" id="getresponse_listid" value="<?php echo $selected_list_id; ?>" type="hidden" class="frm-dropdown frm-pages-dropdown">
                                    <dl class="arf_selectbox" data-name="getresponse_listid" data-id="getresponse_listid" style="width: 400px;">
                                        <dt><span><?php echo $selected_list_label; ?></span>
                                        <svg viewBox="0 0 2000 1000" width="15px" height="15px">
                                        <g fill="#000">
                                        <path d="M1024 320q0 -26 -19 -45t-45 -19h-896q-26 0 -45 19t-19 45t19 45l448 448q19 19 45 19t45 -19l448 -448q19 -19 19 -45z"></path>
                                        </g>
                                        </svg></dt>
                                        <dd>
                                            <ul class="field_dropdown_menu field_dropdown_list_menu" style="display: none;" data-id="getresponse_listid">
                                                <?php echo $responder_list_option; ?>
                                            </ul>
                                        </dd>
                                    </dl>
                                </div></span>                            


                            <div id="getresponse_del_link" style="padding-left:5px; margin-top:10px;<?php if ($getresponse_data->is_verify == 0) { ?> display:none;<?php } ?>" class="arlinks">

                                <a href="javascript:void(0);" onclick="action_autores('refresh', 'getresponse');"><?php echo addslashes(esc_html__('Refresh List', 'ARForms')); ?></a>
                                &nbsp;  &nbsp;  &nbsp;  &nbsp;
                                <a href="javascript:void(0);" onclick="action_autores('delete', 'getresponse');"><?php echo addslashes(esc_html__('Delete Configuration', 'ARForms')); ?></a>
                            </div>


                        </td>


                    </tr>

                    <tr id="getresponse_web_form_tr" <?php if ($autores_type['getresponse_type'] != 0) echo 'style="display:none;"'; ?>>

                        <td class="tdclass" style="width:18%; padding-right:20px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('Webform code from Getresponse', 'ARForms')); ?></label></td>

                        <td style="padding-left:5px;">

                            <textarea <?php
                            if ($setvaltolic != 1) {
                                echo "readonly=readonly";
                            }
                            ?> name="getresponse_web_form" id="getresponse_web_form" class="txtmultinew" <?php if ($setvaltolic != 1) { ?> onclick="alert('Please activate license to set Getresponse settings');" <?php } ?>><?php echo stripslashes($getresponse_data->responder_web_form); ?></textarea>


                        </td>

                    </tr>

                    <tr>
                        <td colspan="2" style="padding-left:5px;"><div class="dotted_line" style="width:96%"></div></td>
                    </tr>

                </table>

                <table class="wp-list-table widefat post " style="margin:20px 0 0 10px; border:none;">


                    <tr>

                        <th style="background:none; border:0px;" width="18%">&nbsp;</th>
                        <th colspan="2" style="background:none; border:none;height:98px;"><img alt='' src="<?php echo ARFURL; ?>/images/icontact.png" align="absmiddle" /></th>

                    </tr>

                    <tr>
                        <?php $autores_type['icontact_type'] = ( $autores_type['icontact_type'] != '' ) ? $autores_type['icontact_type'] : 1; ?>

                        <th style="width:18%; background:none; border:none;"></th>
                        <th id="th_icontact" style="background:none; border:none; padding-left:5px;">
                    <div class="arf_radio_wrapper">
                        <div class="arf_custom_radio_div" >
                            <div class="arf_custom_radio_wrapper">
                                <input type="radio" class="arf_submit_action arf_custom_radio"   id="icontact_1" <?php if ($autores_type['icontact_type'] == 1) echo 'checked="checked"'; ?> name="icontact_type" value="1"  onclick="show_api('icontact');" />
                                <svg width="18px" height="18px">
                                <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                </svg>
                            </div>
                        </div>
                        <span>
                            <label for="icontact_1"><?php echo esc_html__('Using API', 'ARForms'); ?></label>
                        </span>
                    </div>
                    <div class="arf_radio_wrapper">
                        <div class="arf_custom_radio_div" >
                            <div class="arf_custom_radio_wrapper">
                                <input type="radio" class="arf_submit_action arf_custom_radio" id="icontact_2" <?php if ($autores_type['icontact_type'] == 0) echo 'checked="checked"'; ?>  name="icontact_type" value="0" onclick="show_web_form('icontact');" />
                                <svg width="18px" height="18px">
                                <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                </svg>
                            </div>
                        </div>
                        <span>
                            <label for="icontact_2"><?php echo addslashes(esc_html__('Using Web-form', 'ARForms')); ?></label>
                        </span>
                    </div>
                    </th>

                    </tr>

                    <tr id="icontact_api_tr1" <?php if ($autores_type['icontact_type'] != 1) echo 'style="display:none;"'; ?>>

                        <td class="tdclass" style="width:18%; padding-right:20px; padding-bottom:3px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('APP ID', 'ARForms')); ?></label></td>

                        <td style="padding-bottom:3px; padding-left:5px;"><input type="text" name="icontact_api" class="txtmodal1" id="icontact_api" size="80" onkeyup="show_verify_btn('icontact');" value="<?php echo $icontact_data->responder_api_key; ?>" <?php
                            if ($setvaltolic != 1) {
                                echo "readonly=readonly";
                            }
                            ?>  <?php if ($setvaltolic != 1) { ?> onclick="alert('Please activate license to set Icontact settings');" <?php } ?>/>
                            <div class="arferrmessage" id="icontact_api_error" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms')); ?></div></td>

                    </tr>


                    <tr id="icontact_api_tr2" <?php if ($autores_type['icontact_type'] != 1) echo 'style="display:none;"'; ?>>

                        <td class="tdclass" style="width:18%; padding-top:3px; padding-bottom:3px; padding-right:20px; text-align: left;"><label class="lblsubtitle"><?php echo esc_html__('Username', 'ARForms'); ?></label></td>

                        <td style=" padding-top:3px; padding-bottom:3px; padding-left:5px;"><input type="text" name="icontact_username" class="txtmodal1" id="icontact_username" onkeyup="show_verify_btn('icontact');" size="80" value="<?php echo $icontact_data->responder_username; ?>" <?php
                            if ($setvaltolic != 1) {
                                echo "readonly=readonly";
                            }
                            ?> <?php if ($setvaltolic != 1) { ?> onclick="alert('Please activate license to set Icontact settings');" <?php } ?> />
                            <div class="arferrmessage" id="icontact_username_error" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms')); ?></div></div></td>


                    </tr>

                    <tr id="icontact_api_tr3" <?php if ($autores_type['icontact_type'] != 1) echo 'style="display:none;"'; ?>>

                        <td class="tdclass" style="width:18%; padding-top:3px; padding-bottom:3px; padding-right:20px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('Password', 'ARForms')); ?></label></td>

                        <td style=" padding-top:3px; padding-bottom:3px; padding-left:5px;"><input type="password" name="icontact_password" class="txtmodal1" id="icontact_password" onkeyup="show_verify_btn('icontact');" size="80" value="<?php echo $icontact_data->responder_password; ?>" <?php
                            if ($setvaltolic != 1) {
                                echo "readonly=readonly";
                            }
                            ?> <?php if ($setvaltolic != 1) { ?> onclick="alert('Please activate license to set Icontact settings');" <?php } ?>/> &nbsp; &nbsp;
                            <span id="icontact_link" <?php if ($icontact_data->is_verify == 1) { ?> style="display:none"<?php } ?>><a href="javascript:void(0);" onclick="verify_autores('icontact', '0');" class="arlinks"><?php echo addslashes(esc_html__('Verify', 'ARForms')); ?></a></span>
                            <span id="icontact_loader" style="display:none;"><div class="arf_imageloader" style="float: none !important;display:inline-block !important; "></div></span>                    <span id="icontact_verify" class="frm_verify_li" style="display:none;"><?php echo addslashes(esc_html__('Verified', 'ARForms')); ?></span>
                            <span id="icontact_error" class="frm_not_verify_li" style="display:none;"><?php echo addslashes(esc_html__('Not Verified', 'ARForms')); ?></span>
                            <input type="hidden" name="icontact_status" id="icontact_status" value="<?php echo $icontact_data->is_verify; ?>" />
                            <div class="arferrmessage" id="icontact_password_error" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms')); ?></div></td>


                    </tr>

                    <tr id="icontact_api_tr4" <?php if ($autores_type['icontact_type'] != 1) echo 'style="display:none;"'; ?>>

                        <td class="tdclass" style="width:18%; padding-top:3px; padding-bottom:3px; padding-right:20px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('List Name', 'ARForms')); ?></label></td>

                        <td style=" padding-top:3px; padding-bottom:3px; padding-left:5px; overflow: visible;"><span id="select_icontact">
                                <div class="sltstandard" style="float:none;display:inline;">
                                    <?php
                                    $responder_list_option = '';
                                    $selected_list_label = esc_html__('Nothing Selected','ARForms');
                                    $selected_list_id = '';
                                    $lists = maybe_unserialize($icontact_data->responder_list_id);
                                    if ($lists != '' and count($lists) > 0) {
                                        foreach ($lists as $key => $list) {
                                            $list = (array)$list;
                                            if ($icontact_data->responder_list != '') {
                                                if ($icontact_data->responder_list == $list['id']) {
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
                                    ?>
                                    <input name="icontact_listname" id="icontact_listname" value="<?php echo $selected_list_id; ?>" type="hidden" class="frm-dropdown frm-pages-dropdown">
                                    <dl class="arf_selectbox" data-name="icontact_listname" data-id="icontact_listname" style="width: 400px;">
                                        <dt><span><?php echo $selected_list_label; ?></span>
                                        <svg viewBox="0 0 2000 1000" width="15px" height="15px">
                                        <g fill="#000">
                                        <path d="M1024 320q0 -26 -19 -45t-45 -19h-896q-26 0 -45 19t-19 45t19 45l448 448q19 19 45 19t45 -19l448 -448q19 -19 19 -45z"></path>
                                        </g>
                                        </svg></dt>
                                        <dd>
                                            <ul class="field_dropdown_menu field_dropdown_list_menu" style="display: none;" data-id="icontact_listname">
                                                <?php echo $responder_list_option; ?>
                                            </ul>
                                        </dd>
                                    </dl>
                                </div></span>


                            <div id="icontact_del_link" style="padding-left:5px; margin-top:10px;<?php if ($icontact_data->is_verify == 0) { ?>display:none;<?php } ?>" class="arlinks">

                                <a href="javascript:void(0);" onclick="action_autores('refresh', 'icontact');"><?php echo addslashes(esc_html__('Refresh List', 'ARForms')); ?></a>
                                &nbsp;  &nbsp;  &nbsp;  &nbsp;
                                <a href="javascript:void(0);" onclick="action_autores('delete', 'icontact');"><?php echo addslashes(esc_html__('Delete Configuration', 'ARForms')); ?></a>
                            </div>


                        </td>


                    </tr>

                    <tr id="icontact_web_form_tr" <?php if ($autores_type['icontact_type'] != 0) echo 'style="display:none;"'; ?>>

                        <td class="tdclass" style="width:18%; padding-right:20px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('Webform code from Icontact', 'ARForms')); ?></label></td>

                        <td style="padding-left:5px;">

                            <textarea <?php
                            if ($setvaltolic != 1) {
                                echo "readonly=readonly";
                            }
                            ?> name="icontact_web_form" id="icontact_web_form" class="txtmultinew" <?php if ($setvaltolic != 1) { ?> onclick="alert('Please activate license to set Icontact settings');" <?php } ?>><?php echo stripslashes($icontact_data->responder_web_form); ?></textarea>


                        </td>

                    </tr>

                    <tr>
                        <td colspan="2" style="padding-left:5px;"><div class="dotted_line" style="width:96%"></div></td>
                    </tr>

                </table>


                <table class="wp-list-table widefat post " style="margin:20px 0 0 10px; border:none;">

                    <tr>
                        <th style="background:none; border:0px;" width="18%">&nbsp;</th>
                        <th colspan="2" style="background:none; border:none;height:98px;"><img alt='' src="<?php echo ARFURL; ?>/images/constant-contact.png" align="absmiddle" /></th>


                    </tr>

                    <tr>
                        <?php $autores_type['constant_type'] = ( $autores_type['constant_type'] != '' ) ? $autores_type['constant_type'] : 1; ?>
                        <th style="width:18%; background:none; border:none;">&nbsp;</th>
                        <th id="th_constant" style="background:none; border:none; padding-left:5px;">
                    <div class="arf_radio_wrapper">
                        <div class="arf_custom_radio_div" >
                            <div class="arf_custom_radio_wrapper">
                                <input type="radio" class="arf_submit_action arf_custom_radio" id="constant_contact_1" <?php if ($autores_type['constant_type'] == 1) echo 'checked="checked"'; ?> name="constant_type" value="1" onclick="show_api('constant');"/>
                                <svg width="18px" height="18px">
                                <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                </svg>
                            </div>
                        </div>
                        <span>
                            <label for="constant_contact_1"><?php echo esc_html__('Using API', 'ARForms'); ?></label>
                        </span>
                    </div>
                    <div class="arf_radio_wrapper">
                        <div class="arf_custom_radio_div" >
                            <div class="arf_custom_radio_wrapper">
                                <input type="radio" class="arf_submit_action arf_custom_radio" id="constant_contact_2" <?php if ($autores_type['constant_type'] == 0) echo 'checked="checked"'; ?>  name="constant_type" value="0"  onclick="show_web_form('constant');" />
                                <svg width="18px" height="18px">
                                <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                </svg>
                            </div>
                        </div>
                        <span>
                            <label for="constant_contact_2"><?php echo addslashes(esc_html__('Using Web-form', 'ARForms')); ?></label>
                        </span>
                    </div>
                    </th>
                    </tr>

                    <tr id="constant_api_tr1" <?php if ($autores_type['constant_type'] != 1) echo 'style="display:none;"'; ?>>

                        <td class="tdclass" style="width:18%; padding-bottom:3px; padding-right:20px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('API Key', 'ARForms')); ?></label></td>

                        <td style="padding-bottom:3px; padding-left:5px;"><input type="text" name="constant_api" class="txtmodal1" onkeyup="show_verify_btn('constant');" id="constant_api" size="80" value="<?php echo $constant_data->responder_api_key; ?>" <?php
                            if ($setvaltolic != 1) {
                                echo "readonly=readonly";
                            }
                            ?> <?php if ($setvaltolic != 1) { ?> onclick="alert('Please activate license to set Constant Contact settings');" <?php } ?>/>
                            <div class="arferrmessage" id="constant_api_error" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms')); ?></div></td>

                    </tr>

                    <tr id="constant_api_tr2" <?php if ($autores_type['constant_type'] != 1) echo 'style="display:none;"'; ?>>

                        <td class="tdclass" style="width:18%; padding-top:3px; padding-bottom:3px; padding-right:20px; text-align: left;"><label class="lblsubtitle"><?php echo esc_html__('Access Token', 'ARForms'); ?></label></td>

                        <td style="padding-top:3px; padding-bottom:3px; padding-left:5px;"><input type="text" name="constant_access_token" onkeyup="show_verify_btn('constant');" class="txtmodal1" id="constant_access_token" size="80" value="<?php echo $constant_data->responder_list_id; ?>" <?php
                            if ($setvaltolic != 1) {
                                echo "readonly=readonly";
                            }
                            ?> <?php if ($setvaltolic != 1) { ?> onclick="alert('Please activate license to set Constant Contact settings');" <?php } ?>/> &nbsp; &nbsp;

                            <span id="constant_link" <?php if ($constant_data->is_verify == 1) { ?> style="display:none;"<?php } ?> ><a href="javascript:void(0);" onclick="verify_autores('constant', '0');" class="arlinks"><?php echo addslashes(esc_html__('Verify', 'ARForms')); ?></a></span>
                            <span id="constant_loader" style="display:none;"><div class="arf_imageloader" style="float: none !important;display:inline-block !important; "></div></span>
                            <span id="constant_verify" class="frm_verify_li" style="display:none;"><?php echo addslashes(esc_html__('Verified', 'ARForms')); ?></span>
                            <span id="constant_error" class="frm_not_verify_li" style="display:none;"><?php echo addslashes(esc_html__('Not Verified', 'ARForms')); ?></span>
                            <input type="hidden" name="constant_status" id="constant_status" value="<?php echo $constant_data->is_verify; ?>" />
                            <div class="arferrmessage" id="constant_access_token_error" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms')); ?></div></td>

                    </tr>

                    <tr id="constant_api_tr3" <?php if ($autores_type['constant_type'] != 1) echo 'style="display:none;"'; ?>>

                        <td class="tdclass" style="width:18%; padding-top:3px; padding-bottom:3px; padding-right:20px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('List Name', 'ARForms')); ?></label></td>

                        <td style="padding-top:3px; padding-bottom:3px; padding-left:5px; overflow: visible;"><span id="select_constant">
                                <div class="sltstandard" style="float:none; display:inline;">
                                    <?php
                                    $responder_list_option = '';
                                    $selected_list_label = esc_html__('Nothing Selected','ARForms');
                                    $selected_list_id = '';
                                    $lists = maybe_unserialize($constant_data->list_data);
                                    if ($lists != '' and count($lists) > 0) {
                                        foreach ($lists as $key => $list) {
                                            if ($constant_data->responder_list != '') {
                                                if ($constant_data->responder_list == $list['id']) {
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
                                    ?>
                                    <input name="constant_listname" id="constant_listname" value="<?php echo $selected_list_id; ?>" type="hidden" class="frm-dropdown frm-pages-dropdown">
                                    <dl class="arf_selectbox" data-name="constant_listname" data-id="constant_listname" style="width: 400px;">
                                        <dt><span><?php echo $selected_list_label; ?></span>
                                        <svg viewBox="0 0 2000 1000" width="15px" height="15px">
                                        <g fill="#000">
                                        <path d="M1024 320q0 -26 -19 -45t-45 -19h-896q-26 0 -45 19t-19 45t19 45l448 448q19 19 45 19t45 -19l448 -448q19 -19 19 -45z"></path>
                                        </g>
                                        </svg></dt>
                                        <dd>
                                            <ul class="field_dropdown_menu field_dropdown_list_menu" style="display: none;" data-id="constant_listname">
                                                <?php echo $responder_list_option; ?>
                                            </ul>
                                        </dd>
                                    </dl>
                                </div></span>


                            <div id="constant_del_link" style="padding-left:5px; margin-top:10px;<?php if ($constant_data->is_verify == 0) { ?>display:none;<?php } ?>" class="arlinks">

                                <a href="javascript:void(0);" onclick="action_autores('refresh', 'constant');"><?php echo addslashes(esc_html__('Refresh List', 'ARForms')); ?></a>
                                &nbsp;  &nbsp;  &nbsp;  &nbsp;
                                <a href="javascript:void(0);" onclick="action_autores('delete', 'constant');"><?php echo addslashes(esc_html__('Delete Configuration', 'ARForms')); ?></a>
                            </div>


                        </td>

                    </tr>

                    <tr id="constant_web_form_tr" <?php if ($autores_type['constant_type'] != 0) echo 'style="display:none;"'; ?>>

                        <td class="tdclass" style="width:18%; padding-right:20px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('Webform code from Constant Contact', 'ARForms')); ?></label></td>

                        <td style="padding-left:5px;">

                            <textarea <?php
                            if ($setvaltolic != 1) {
                                echo "readonly=readonly";
                            }
                            ?> name="constant_web_form" id="constant_web_form" class="txtmultinew" <?php if ($setvaltolic != 1) { ?> onclick="alert('Please activate license to set Constant Contact settings');" <?php } ?>><?php echo stripslashes($constant_data->responder_web_form); ?></textarea>


                        </td>

                    </tr>

                    <tr>
                        <td colspan="2" style="padding-left:5px;"><div class="dotted_line" style="width:96%"></div></td>
                    </tr>

                </table>


                <table class="wp-list-table widefat post " style="margin:20px 0 0 10px; border:none;">

                    <tr>
                        <th style="background:none; border:0px;" width="18%">&nbsp;</th>
                        <th style="background:none; border:none;height:98px;" colspan="2"><img alt='' src="<?php echo ARFURL; ?>/images/madmimi.png" align="absmiddle" /></th>

                        </th>

                    </tr>

                    <tr>
                        <?php $autores_type['madmimi_type'] = ( $autores_type['madmimi_type'] != '') ? $autores_type['madmimi_type'] : 1; ?>
                        <th style="width:18%; background:none; border:none;">&nbsp;</th>
                        <th id="th_madmimi" style=" background:none; border:none; padding-left:5px; ">
                    <div class="arf_radio_wrapper">
                        <div class="arf_custom_radio_div" >
                            <div class="arf_custom_radio_wrapper">
                                <input type="radio" class="arf_submit_action arf_custom_radio" id="madmimi_1" <?php if ($autores_type['madmimi_type'] == 1) echo 'checked="checked"'; ?> name="madmimi_type" value="1" onclick="show_api('madmimi');" />
                                <svg width="18px" height="18px">
                                <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                </svg>
                            </div>
                        </div>
                        <span>
                            <label for="madmimi_1"><?php echo esc_html__('Using API', 'ARForms'); ?></label>
                        </span>
                    </div>
                    </th>

                    </tr>

                    <tr id="madmimi_api_tr1" <?php if ($autores_type['madmimi_type'] != 1) echo 'style="display:none;"'; ?>>

                        <td class="tdclass" style="width:18%; padding-right:20px; padding-bottom:3px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('Email Address', 'ARForms')); ?></label></td>

                        <td style="padding-bottom:3px; padding-left:5px;"><input type="text" name="madmimi_email" class="txtmodal1" <?php
                            if ($setvaltolic != 1) {
                                echo "readonly=readonly";
                            }
                            ?> <?php if ($setvaltolic != 1) { ?> onclick="alert('Please activate license to set madmimi settings');" <?php } ?> id="madmimi_email" size="80" onkeyup="show_verify_btn('madmimi');" value="<?php echo $madmimi_data->madmimi_email; ?>" /> &nbsp; &nbsp;
                            <div class="arferrmessage" id="madmimi_email_error" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms')); ?></div>
                            <div class="arferrmessage" id="madmimi_email_not_valid_error" style="display:none;"><?php echo addslashes(esc_html__('Please enter valid email address.', 'ARForms')); ?></div></td>

                    </tr>

                    <tr id="madmimi_api_tr2" <?php if ($autores_type['madmimi_type'] != 1) echo 'style="display:none;"'; ?>>

                        <td class="tdclass" style="width:18%; padding-right:20px; padding-bottom:3px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('API Key', 'ARForms')); ?></label></td>

                        <td style="padding-bottom:3px; padding-left:5px;"><input type="text" name="madmimi_api" class="txtmodal1" <?php
                            if ($setvaltolic != 1) {
                                echo "readonly=readonly";
                            }
                            ?> <?php if ($setvaltolic != 1) { ?> onclick="alert('Please activate license to set madmimi settings');" <?php } ?> id="madmimi_api" size="80" onkeyup="show_verify_btn('madmimi');" value="<?php echo $madmimi_data->responder_api_key; ?>" /> &nbsp; &nbsp;
                            <span id="madmimi_link" <?php if ($madmimi_data->is_verify == 1) { ?>style="display:none;"<?php } ?>><a href="javascript:void(0);" onclick="verify_autores('madmimi', '0');" class="arlinks"><?php echo addslashes(esc_html__('Verify', 'ARForms')); ?></a></span>
                            <span id="madmimi_loader" style="display:none;"><div class="arf_imageloader" style="float: none !important;display:inline-block !important; "></div></span>
                            <span id="madmimi_verify" class="frm_verify_li" style="display:none;"><?php echo addslashes(esc_html__('Verified', 'ARForms')); ?></span>
                            <span id="madmimi_error" class="frm_not_verify_li" style="display:none;"><?php echo addslashes(esc_html__('Not Verified', 'ARForms')); ?></span>
                            <input type="hidden" name="madmimi_status" id="madmimi_status" value="<?php echo $madmimi_data->is_verify; ?>" />
                            <div class="arferrmessage" id="madmimi_api_error" style="display:none;"><?php echo addslashes(esc_html__('This field cannot be blank.', 'ARForms')); ?></div></td>

                    </tr>


                    <tr id="madmimi_api_tr3" <?php if ($autores_type['madmimi_type'] != 1) echo 'style="display:none;"'; ?>>

                        <td class="tdclass" style="width:18%; padding-right:20px; padding-top:3px; padding-bottom:3px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('List ID', 'ARForms')); ?></label></td>

                        <td style=" padding-top:3px; padding-bottom:3px; padding-left:5px; overflow: visible;"><span id="select_madmimi">
                                <div class="sltstandard" style="float:none;display:inline;">
                                    <?php
                                    $responder_list_option = '';
                                    $selected_list_label = esc_html__('Nothing Selected','ARForms');
                                    $selected_list_id = '';
                                    $lists = maybe_unserialize($madmimi_data->responder_list_id);
                                    if ($lists != '' and count($lists) > 0) {
                                        if (is_array($lists)) {
                                            foreach ($lists as $key => $list) {
                                                if ($madmimi_data->responder_list != '') {
                                                    if ($madmimi_data->responder_list == $list['id']) {
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
                                    <input name="madmimi_listid" id="madmimi_listid" value="<?php echo $selected_list_id; ?>" type="hidden" class="frm-dropdown frm-pages-dropdown">
                                    <dl class="arf_selectbox" data-name="madmimi_listid" data-id="madmimi_listid" style="width: 400px;">
                                        <dt><span><?php echo $selected_list_label; ?></span>
                                        <svg viewBox="0 0 2000 1000" width="15px" height="15px">
                                        <g fill="#000">
                                        <path d="M1024 320q0 -26 -19 -45t-45 -19h-896q-26 0 -45 19t-19 45t19 45l448 448q19 19 45 19t45 -19l448 -448q19 -19 19 -45z"></path>
                                        </g>
                                        </svg></dt>
                                        <dd>
                                            <ul class="field_dropdown_menu field_dropdown_list_menu" style="display: none;" data-id="madmimi_listid">
                                                <?php echo $responder_list_option; ?>
                                            </ul>
                                        </dd>
                                    </dl>
                                </div></span>




                            <div id="madmimi_del_link" style="padding-left:5px; margin-top:10px;<?php if ($madmimi_data->is_verify == 0) { ?>display:none;<?php } ?>" class="arlinks">
                                <a href="javascript:void(0);" onclick="action_autores('refresh', 'madmimi');"><?php echo addslashes(esc_html__('Refresh List', 'ARForms')); ?></a>
                                &nbsp;  &nbsp;  &nbsp;  &nbsp;
                                <a href="javascript:void(0);" onclick="action_autores('delete', 'madmimi');"><?php echo addslashes(esc_html__('Delete Configuration', 'ARForms')); ?></a>
                            </div>


                        </td>

                    </tr>

                    <tr id="madmimi_web_form_tr" <?php if ($autores_type['madmimi_type'] != 0) echo 'style="display:none;"'; ?>>

                        <td class="tdclass" style="width:18%; padding-right:20px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('Webform code from madmimi', 'ARForms')); ?></label></td>

                        <td style="padding-left:5px;">

                            <textarea <?php
                            if ($setvaltolic != 1) {
                                echo "readonly=readonly";
                            }
                            ?> name="madmimi_web_form" id="madmimi_web_form" class="txtmultinew" <?php if ($setvaltolic != 1) { ?> onclick="alert('Please activate license to set madmimi settings');" <?php } ?>><?php echo stripslashes($madmimi_data->responder_web_form); ?></textarea>



                        </td>

                    </tr>
                    <tr>
                        <td colspan="2" style="padding-left:5px;"><div class="dotted_line" style="width:96%"></div></td>
                    </tr>

                </table>


                <table class="wp-list-table widefat post " style="margin:20px 0 0 10px; border:none;">

                    <tr>
                        <th style="background:none; border:0px;" width="18%">&nbsp;</th>
                        <th style="background:none; border:none;height:98px;" colspan="2"><img alt='' src="<?php echo ARFURL; ?>/images/gvo.png" align="absmiddle" /></label></th>

                    </tr>

                    <tr>
                        <?php $autores_type['gvo_type'] = ( $autores_type['gvo_type'] != '' ) ? $autores_type['gvo_type'] : 0; ?>
                        <th style="width:18%; background:none; border:none;"></th>
                        <th id="th_gvo" style="padding-left:5px;background:none; border:none;">
                    <div class="arf_radio_wrapper">
                        <div class="arf_custom_radio_div" >
                            <div class="arf_custom_radio_wrapper">
                                <input type="radio" class="arf_submit_action arf_custom_radio" id="gvo_1" <?php if ($autores_type['gvo_type'] == 0) echo 'checked="checked"'; ?>  name="gvo_type" value="0" onclick="show_web_form('gvo');" />
                                <svg width="18px" height="18px">
                                <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                </svg>
                            </div>
                        </div>
                        <span>
                            <label for="gvo_1"><?php echo addslashes(esc_html__('Using Web-form', 'ARForms')); ?></label>
                        </span>
                    </div>
                    </th>
                    </tr>

                    <tr id="gvo_web_form_tr" <?php if ($autores_type['gvo_type'] != 0) echo 'style="display:none;"'; ?>>

                        <td class="tdclass" style="width:18%; padding-right:20px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('Webform code from GVO Campaign', 'ARForms')); ?></label></td>

                        <td style="padding-left:5px;">

                            <textarea <?php
                            if ($setvaltolic != 1) {
                                echo "readonly=readonly";
                            }
                            ?> name="gvo_api" id="gvo_api" class="txtmultinew" <?php if ($setvaltolic != 1) { ?> onclick="alert('Please activate license to set GVO settings');" <?php } ?>><?php echo stripslashes($gvo_data->responder_api_key); ?></textarea>

                        </td>

                    </tr>

                    <tr>
                        <td colspan="2" style="padding-left:5px;"><div class="dotted_line" style="width:96%"></div></td>
                    </tr>

                </table>


                <table class="wp-list-table widefat post " style="margin:20px 0 20px 10px; border:none;">

                    <tr>
                        <th style="background:none; border:0px;" width="18%">&nbsp;</th>
                        <th style="background:none; border:none;height:98px;" colspan="2"><img alt='' src="<?php echo ARFURL; ?>/images/ebizac.png" align="absmiddle" /></th>

                    </tr>

                    <tr>
                        <?php $autores_type['ebizac_type'] = ( $autores_type['ebizac_type'] != '' ) ? $autores_type['ebizac_type'] : 0; ?>
                        <th style="width:18%; background:none; border:none;"></th>
                        <th id="th_ebizac" style="padding-left:5px;background:none; border:none;">
                    <div class="arf_radio_wrapper">
                        <div class="arf_custom_radio_div" >
                            <div class="arf_custom_radio_wrapper">
                                <input type="radio" class="arf_submit_action arf_custom_radio" id="ebizac_1" <?php if ($autores_type['ebizac_type'] == 0) echo 'checked="checked"'; ?>  name="ebizac_type" value="0" onclick="show_web_form('ebizac');" />
                                <svg width="18px" height="18px">
                                <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                </svg>
                            </div>
                        </div>
                        <span>
                            <label for="ebizac_1"><?php echo addslashes(esc_html__('Using Web-form', 'ARForms')); ?></label>
                        </span>
                    </div>
                    </th>

                    </tr>

                    <tr id="ebizac_web_form_tr" <?php if ($autores_type['ebizac_type'] != 0) echo 'style="display:none;"'; ?>>

                        <td class="tdclass" style="width:17%; padding-right:20px; text-align: left;"><label class="lblsubtitle"><?php echo addslashes(esc_html__('Webform code from eBizac', 'ARForms')); ?></label></td>

                        <td style="verticle-align:middle; padding-left:5px;">
                            <textarea <?php
                            if ($setvaltolic != 1) {
                                echo "readonly=readonly";
                            }
                            ?> name="ebizac_api" id="ebizac_api" class="txtmultinew" <?php if ($setvaltolic != 1) { ?> onclick="alert('Please activate license to set eBizac settings');" <?php } ?>><?php echo stripslashes($ebizac_data->responder_api_key); ?></textarea>


                    </tr>
                    <tr>
                        <td colspan="2" style="padding-left:5px;"><div class="dotted_line" style="width:96%"></div></td>
                    </tr>


                </table>

                <?php do_action('arf_autoresponder_global_setting_block', $autores_type, $setvaltolic); ?>


            </div>



            <div id="verification_settings" style=" display:none; background-color:#FFFFFF; padding-top:10px; border-radius:5px 5px 5px 5px;-webkit-border-radius:5px 5px 5px 5px;-o-border-radius:5px 5px 5px 5px;-moz-border-radius:5px 5px 5px 5px; padding-left: 20px;  padding-bottom:1px;">
                <?php ?>
            </div>

            <?php
            foreach ($sections as $sec_name => $section) {


                if (isset($section['class'])) {


                    call_user_func(array($section['class'], $section['function']));
                } else {


                    call_user_func((isset($section['function']) ? $section['function'] : $section));
                }
            }


            $user_roles = $current_user->roles;


            $user_role = array_shift($user_roles);
            ?>

            <br />
            <p class="submit">

                <?php
                if ($setting_tab == 'general_settings') {
                    if (is_rtl()) {
                        $style_attr = "margin-right:22%";
                    } else {
                        $style_attr = "margin-left:22%";
                    }
                } else if ($setting_tab == 'autoresponder_settings') {
                    if (is_rtl()) {
                        $style_attr = "margin-right:20%";
                    } else {
                        $style_attr = "margin-left:20%";
                    }
                }
                ?>
                <button class="rounded_button arf_btn_dark_blue general_submit_button"  style="border:0px; color:#FFFFFF; height:41px; width:120px !important;<?php echo $style_attr; ?>" type="submit" ><?php echo addslashes(esc_html__('Save Changes', 'ARForms')); ?></button></p>
            <br />





            </form>
        </div>


    </div>



</div>


</div>

<div class="documentation_link" align="right"><a href="<?php echo ARFURL; ?>/documentation/index.html" class="arlinks" style="margin-right:10px;" target="_blank"><?php echo addslashes(esc_html__('Documentation', 'ARForms')); ?></a>|<a href="https://helpdesk.arpluginshop.com/submit-a-ticket/" style="margin-left:10px;" target="_blank" class="arlinks"><?php echo addslashes(esc_html__('Support', 'ARForms')); ?></a>&nbsp;&nbsp;<img src="<?php echo ARFURL; ?>/images/dot.png" height="4" width="4" onclick="javascript:OpenInNewTab('<?php echo ARFURL; ?>/documentation/assets/sysinfo.php');" /></div>

</div>


<?php ?>
<script type="text/javascript" data-cfasync="false">

    function show_form_settimgs(id1, id2)
    {

        document.getElementById(id1).style.display = 'block';
        document.getElementById(id2).style.display = 'none';

        document.getElementById('arfcurrenttab').value = id1;
        if (id1 == 'general_settings')
        {
            jQuery(".general_submit_button").css('margin-left', "22%");

        }
        if (id1 == 'autoresponder_settings')
        {
            jQuery(".general_submit_button").css('margin-left', "20%");

        }
        jQuery('.' + id1).addClass('btn_sld').removeClass('tab-unselected');
        jQuery('#' + id1 + '_img').attr('src', '<?php echo ARFIMAGESURL; ?>/' + id1 + '.png');
        jQuery('.' + id2).removeClass('btn_sld').addClass('tab-unselected');
        jQuery('#' + id2 + '_img').attr('src', '<?php echo ARFIMAGESURL; ?>/' + id2 + '_hover.png');

    }

</script>