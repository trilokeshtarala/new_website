<?php

global $wpdb, $db_record, $MdlDb, $armainhelper, $arfieldhelper, $arsettingcontroller, $arfsettings;

if (version_compare($newdbversion, '1.0', '>') || version_compare($newdbversion, '1', '=')) {
    global $wpdb;

    delete_option('arftempsetting');

    $resval = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "options WHERE option_name = 'arf_options' ", OBJECT_K);
    foreach ($resval as $mykey => $myval) {
        $mynewarrsetting = addslashes($myval->option_value);
        $ins = $wpdb->query("insert into " . $wpdb->prefix . "options (option_name,option_value,autoload) VALUES ('".arf_sanitize_value('arftempsetting')."','" . $mynewarrsetting . "','".arf_sanitize_value('yes')."') ");
    }

    if (version_compare($newdbversion, '1.2', '<')) {
        global $wpdb;
        $wpdb->query("RENAME TABLE " . $wpdb->prefix . "arf_items TO " . $MdlDb->entries);
        $wpdb->query("RENAME TABLE " . $wpdb->prefix . "arf_item_metas TO " . $MdlDb->entry_metas);

        delete_option('arfa_db_version');
    }

    if (version_compare($newdbversion, '2.0', '<')) {
        require_once(MODELS_PATH . '/arsettingmodel.php');
        require_once(MODELS_PATH . '/arstylemodel.php');

        global $wpdb;

        $updateoptionsetting = new arsettingmodel();
        update_option('arf_options', $updateoptionsetting);
        set_transient('arf_options', $updateoptionsetting);

        $res = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "options WHERE option_name = 'arftempsetting' ", OBJECT_K);
        foreach ($res as $key => $val) {
            $optionval = $val->option_value;

            $optionval = str_replace("settingmodel", "arsettingmodel", $optionval);
            $optionval = str_replace("O:12:", "O:14:", $optionval);
            $myarr = maybe_unserialize($optionval);

            $res1 = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "options WHERE option_name = 'arf_options' ", OBJECT_K);
            foreach ($res1 as $key1 => $val1) {
                $mynewarr = maybe_unserialize($val1->option_value);
            }

            $mynewarr->pubkey = $myarr->pubkey;
            $mynewarr->privkey = $myarr->privkey;
            $mynewarr->re_theme = arf_sanitize_value($myarr->re_theme);
            $mynewarr->re_lang = arf_sanitize_value($myarr->re_lang);
            $mynewarr->re_msg = arf_sanitize_value($myarr->re_msg);
            $mynewarr->success_msg = arf_sanitize_value($myarr->success_msg);
            $mynewarr->failed_msg = arf_sanitize_value($myarr->failed_msg);
            $mynewarr->blank_msg = arf_sanitize_value($myarr->blank_msg);

            $mynewarr->invalid_msg = arf_sanitize_value($myarr->invalid_msg);
            $mynewarr->submit_value = arf_sanitize_value($myarr->submit_value);
            $mynewarr->reply_to_name = arf_sanitize_value($myarr->reply_to_name);
            $mynewarr->reply_to = arf_sanitize_value($myarr->reply_to, 'email');
            $mynewarr->brand = arf_sanitize_value($myarr->brand, 'integer');
            $mynewarr->form_submit_type = form_submit_type($myarr->form_submit_type, 'integer');


            update_option('arf_options', $mynewarr);
            set_transient('arf_options', $mynewarr);
        }
        delete_option('arftempsetting');

        $updateoptionsetting->set_default_options();

        $updatestylesettings = new arstylemodel();

        update_option('arfa_options', $updatestylesettings);
        set_transient('arfa_options', $updatestylesettings);

        $updatestylesettings->set_default_options();
        $updatestylesettings->store();



        $cssoptions = get_option("arfa_options");
        $new_values = array();

        foreach ($cssoptions as $k => $v)
            $new_values[$k] = $v;

        $arfssl = (is_ssl()) ? 1 : 0;

        $filename = FORMPATH . '/core/css_create_main.php';

        if (is_file($filename)) {
            $uploads = wp_upload_dir();
            $target_path = $uploads['basedir'];
            $target_path .= "/arforms";
            $target_path .= "/css";
            $use_saved = true;
            $form_id = '';
            $css = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
            $css .= "\n";
            ob_start();
            include $filename;
            $css .= ob_get_contents();
            ob_end_clean();
            $css .= "\n " . $warn;
            $css_file = $target_path . '/arforms.css';

            WP_Filesystem();
            global $wp_filesystem;
            $wp_filesystem->put_contents($css_file, $css, 0777);

            update_option('arfa_css', $css);
            delete_transient('arfa_css');
            set_transient('arfa_css', $css);
        }


        global $wpdb, $db_record, $MdlDb;
        $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->forms . " WHERE status!=%s order by id desc",draft), OBJECT_K);

        foreach ($res as $key => $val) {
            $form_css_res = $wpdb->get_results($wpdb->prepare("SELECT id, form_css FROM " . $MdlDb->forms . " WHERE id = %d", $val->id), ARRAY_A);
            $form_id = $val->id;
            $cssoptions = maybe_unserialize($form_css_res[0]['form_css']);

            $cssoptions['arffieldinnermarginssetting'] = '6px 10px 6px 10px';
            $cssoptions['bg_inavtive_color_pg_break'] = '7ec3fc';

            $cssoptions['arfsubmitfontfamily'] = $cssoptions['check_font'];
            $cssoptions['arfmainfieldsetpadding_1'] = arf_sanitize_value('30');
            $cssoptions['arfmainfieldsetpadding_2'] = arf_sanitize_value('10');
            $cssoptions['arfmainfieldsetpadding_3'] = arf_sanitize_value('30');
            $cssoptions['arfmainfieldsetpadding_4'] = arf_sanitize_value('10');
            $cssoptions['arfmainformtitlepaddingsetting_1'] = arf_sanitize_value('0');
            $cssoptions['arfmainformtitlepaddingsetting_2'] = arf_sanitize_value('0');
            $cssoptions['arfmainformtitlepaddingsetting_3'] = arf_sanitize_value('15');
            $cssoptions['arfmainformtitlepaddingsetting_4'] = arf_sanitize_value('45');
            $cssoptions['arffieldinnermarginssetting_1'] = arf_sanitize_value('6');
            $cssoptions['arffieldinnermarginssetting_2'] = arf_sanitize_value('10');
            $cssoptions['arffieldinnermarginssetting_3'] = arf_sanitize_value('6');
            $cssoptions['arffieldinnermarginssetting_4'] = arf_sanitize_value('10');
            $cssoptions['arfsubmitbuttonmarginsetting_1'] = arf_sanitize_value('10');
            $cssoptions['arfsubmitbuttonmarginsetting_2'] = arf_sanitize_value('0');
            $cssoptions['arfsubmitbuttonmarginsetting_3'] = arf_sanitize_value('0');
            $cssoptions['arfsubmitbuttonmarginsetting_4'] = arf_sanitize_value('10');
            $cssoptions['arfformtitlealign'] = arf_sanitize_value('left');

            $cssoptions['arfcheckradiostyle'] = arf_sanitize_value('minimal');
            $cssoptions['arfcheckradiocolor'] = arf_sanitize_value('default');

            $sernewarr = maybe_serialize($cssoptions);
            $res = $wpdb->update($MdlDb->forms, array('form_css' => $sernewarr), array('id' => $val->id));

            if (count($cssoptions) > 0) {
                $new_values = array();

                foreach ($cssoptions as $k => $v)
                    $new_values[$k] = str_replace("#", '', $v);

                $saving = true;
                $use_saved = true;
                $arfssl = (is_ssl()) ? 1 : 0;
                $filename = FORMPATH . '/core/css_create_main.php';
                $temp_css_file = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
                $temp_css_file .= "\n";
                ob_start();
                include $filename;
                $temp_css_file .= ob_get_contents();
                ob_end_clean();
                $temp_css_file .= "\n " . $warn;
                $wp_upload_dir = wp_upload_dir();
                $dest_dir = $wp_upload_dir['basedir'] . '/arforms/maincss/';
                $css_file_new = $dest_dir . 'maincss_' . $form_id . '.css';

                WP_Filesystem();
                global $wp_filesystem;
                $wp_filesystem->put_contents($css_file_new, $temp_css_file, 0777);
            }


            $unserarr = array();
            $unserarr = maybe_unserialize($val->options);
            $unserarr["arf_form_outer_wrapper"] = '';
            $unserarr["arf_form_inner_wrapper"] = '';
            $unserarr["arf_form_title"] = '';
            $unserarr["arf_form_description"] = '';
            $unserarr["arf_form_element_wrapper"] = '';
            $unserarr["arf_form_element_label"] = '';
            $unserarr["arf_form_submit_button"] = '';
            $unserarr["arf_form_success_message"] = '';
            $unserarr["arf_form_elements"] = '';
            $unserarr["arf_submit_outer_wrapper"] = '';
            $unserarr["arf_form_next_button"] = '';
            $unserarr["arf_form_previous_button"] = '';
            $unserarr["arf_form_error_message"] = '';
            $unserarr["arf_form_page_break"] = '';
            $unserarr["arf_form_fly_sticky"] = '';
            $unserarr["arf_form_modal_css"] = '';
            $unserarr["arf_form_other_css"] = $unserarr["form_custom_css"];


            $seriarr = maybe_serialize($unserarr);
            $res = $wpdb->update($MdlDb->forms, array('options' => $seriarr), array('id' => $val->id));



            $arsetting = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->ar . " WHERE frm_id = %d", $val->id), ARRAY_A);
            $aweber_settings = maybe_unserialize($arsetting[0]["aweber"]);
            $mailchimp_settings = maybe_unserialize($arsetting[0]["mailchimp"]);
            $getresponse_settings = maybe_unserialize($arsetting[0]["getresponse"]);
            $gvo_settings = maybe_unserialize($arsetting[0]["gvo"]);
            $ebizac_settings = maybe_unserialize($arsetting[0]["ebizac"]);
            $icontact_settings = maybe_unserialize($arsetting[0]["icontact"]);
            $constant_contact_settings = maybe_unserialize($arsetting[0]["constant_contact"]);

            $aweber_arr = array();
            $aweber_arr['enable'] = arf_sanitize_value($aweber_settings['enable'], 'integer');
            $aweber_arr['type'] = arf_sanitize_value($aweber_settings['type'], 'integer');
            $aweber_arr['type_val'] = arf_sanitize_value($aweber_settings['type_val'], 'integer');
            $ar_aweber = maybe_serialize($aweber_arr);

            $mailchimp_arr = array();
            $mailchimp_arr['enable'] = arf_sanitize_value($mailchimp_settings['enable'], 'integer');
            $mailchimp_arr['type'] = arf_sanitize_value($mailchimp_settings['type'], 'integer');
            $mailchimp_arr['type_val'] = arf_sanitize_value($mailchimp_settings['type_val'], 'integer');
            $ar_mailchimp = maybe_serialize($mailchimp_arr);

            $getresponse_arr = array();
            $getresponse_arr['enable'] = arf_sanitize_value($getresponse_settings['enable'], 'integer');
            $getresponse_arr['type'] = arf_sanitize_value($getresponse_settings['type'], 'integer');
            $getresponse_arr['type_val'] = arf_sanitize_value($getresponse_settings['type_val'], 'integer');
            $ar_getresponse = maybe_serialize($getresponse_arr);

            $gvo_arr = array();
            $gvo_arr['enable'] = arf_sanitize_value($gvo_settings['enable'], 'integer');
            $gvo_arr['type'] =arf_sanitize_value ($gvo_settings['type'], 'integer');
            $gvo_arr['type_val'] = arf_sanitize_value($gvo_settings['type_val'], 'integer');
            $ar_gvo = maybe_serialize($gvo_arr);

            $ebizac_arr = array();
            $ebizac_arr['enable'] = arf_sanitize_value($ebizac_settings['enable'], 'integer');
            $ebizac_arr['type'] = arf_sanitize_value($ebizac_settings['type'], 'integer');
            $ebizac_arr['type_val'] = arf_sanitize_value($ebizac_settings['type_val'], 'integer');
            $ar_ebizac = maybe_serialize($ebizac_arr);

            $icontact_arr = array();
            $icontact_arr['enable'] = arf_sanitize_value($icontact_settings['enable'], 'integer');
            $icontact_arr['type'] = arf_sanitize_value($icontact_settings['type'], 'integer');
            $icontact_arr['type_val'] = arf_sanitize_value($icontact_settings['type_val'], 'integer');
            $ar_icontact = maybe_serialize($icontact_arr);

            $constant_contact_arr = array();
            $constant_contact_arr['enable'] = arf_sanitize_value($constant_contact_settings['enable'], 'integer');
            $constant_contact_arr['type'] = arf_sanitize_value($constant_contact_settings['type'], 'integer');
            $constant_contact_arr['type_val'] = arf_sanitize_value($constant_contact_settings['type_val'], 'integer');
            $ar_constant_contact = maybe_serialize($constant_contact_arr);


            $wpdb->query("ALTER TABLE " . $MdlDb->ar . " ADD `enable_ar` TEXT DEFAULT NULL ");

            $ar_global_autoresponder = array(
                'aweber' => $aweber_arr['enable'],
                'mailchimp' => $mailchimp_arr['enable'],
                'getresponse' => $getresponse_arr['enable'],
                'gvo' => $gvo_arr['enable'],
                'ebizac' => $ebizac_arr['enable'],
                'icontact' => $icontact_arr['enable'],
                'constant_contact' => $constant_contact_arr['enable'],
            );

            $enable_ar = maybe_serialize($ar_global_autoresponder);
            $res = $wpdb->update($MdlDb->ar, array('enable_ar' => $enable_ar), array('frm_id' => $form_id));

            $res = $wpdb->update($MdlDb->ar, array('aweber' => $ar_aweber, 'mailchimp' => $ar_mailchimp, 'getresponse' => $ar_getresponse, 'gvo' => $ar_gvo, 'ebizac' => $ar_ebizac, 'icontact' => $ar_icontact, 'constant_contact' => $ar_constant_contact), array('frm_id' => $form_id));



            global $arffield;
            $form_fields = $arffield->getAll("fi.form_id = " . $form_id, " ORDER BY id");
            foreach ($form_fields as $key => $val) {
                $val->field_options['is_recaptcha'] = arf_sanitize_value('recaptcha');
                $val->field_options['file_upload_text'] = arf_sanitize_value('Upload');
                $val->field_options['file_remove_text'] = arf_sanitize_value('Remove');
                $val->field_options['upload_btn_color'] = arf_sanitize_value('#077bdd');
                $val->field_options['inline_css'] = '';
                $val->field_options['css_outer_wrapper'] = '';
                $val->field_options['css_label'] = '';
                $val->field_options['css_input_element'] = '';
                $val->field_options['css_description'] = '';

                $val->field_options['arf_divider_font'] = arf_sanitize_value('Helvetica');
                $val->field_options['arf_divider_font_size'] = arf_sanitize_value('16');
                $val->field_options['arf_divider_font_style'] = arf_sanitize_value('bold');

                $val->field_options['arf_divider_bg_color'] = arf_sanitize_value('#ffffff');

                $optionsnewval = maybe_serialize($val->field_options);
                $res = $wpdb->update($MdlDb->fields, array('field_options' => $optionsnewval), array('id' => $val->id));
            }
        }


        $wpdb->query("ALTER TABLE " . $MdlDb->fields . " ADD conditional_logic longtext default NULL");




        $wpdb->query("ALTER TABLE " . $MdlDb->entry_metas . " CHANGE `meta_value` `entry_value` LONGTEXT DEFAULT NULL");
        $wpdb->query("ALTER TABLE " . $MdlDb->entry_metas . " CHANGE `item_id` `entry_id` INT( 11 ) NOT NULL");
        $wpdb->query("ALTER TABLE " . $MdlDb->entry_metas . " CHANGE `created_at` `created_date` DATETIME NOT NULL ");


        $wpdb->query("ALTER TABLE " . $MdlDb->views . " CHANGE `ip` `ip_address` VARCHAR(255) DEFAULT NULL");
        $wpdb->query("ALTER TABLE " . $MdlDb->views . " CHANGE `browser` `browser_info` VARCHAR(255) DEFAULT NULL");
        $wpdb->query("ALTER TABLE " . $MdlDb->views . " DROP `referer` ");


        $wpdb->query("ALTER TABLE " . $MdlDb->entries . " CHANGE `item_key` `entry_key` VARCHAR( 255 ) DEFAULT NULL ");
        $wpdb->query("ALTER TABLE " . $MdlDb->entries . " CHANGE `ip` `ip_address` TEXT DEFAULT NULL");
        $wpdb->query("ALTER TABLE " . $MdlDb->entries . " CHANGE `browser` `browser_info` TEXT DEFAULT NULL");
        $wpdb->query("ALTER TABLE " . $MdlDb->entries . " DROP `referer` ");
        $wpdb->query("ALTER TABLE " . $MdlDb->entries . " DROP `parent_item_id` ");
        $wpdb->query("ALTER TABLE " . $MdlDb->entries . " CHANGE `post_id` `attachment_id` INT( 11 ) DEFAULT NULL ");
        $wpdb->query("ALTER TABLE " . $MdlDb->entries . " CHANGE `created_at` `created_date` DATETIME NOT NULL ");


        $wpdb->query("ALTER TABLE " . $MdlDb->forms . " CHANGE `created_at` `created_date` DATETIME NOT NULL ");
        $wpdb->query("ALTER TABLE " . $MdlDb->forms . " DROP `default_template` ");

        $wpdb->query("ALTER TABLE " . $MdlDb->fields . " CHANGE `created_at` `created_date` DATETIME NOT NULL");


        $wpdb->query("DELETE FROM " . $MdlDb->forms . " WHERE `form_id` > 0 ");


        $charset_collate = '';
        if ($wpdb->has_cap('collation')) {
            if (!empty($wpdb->charset))
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if (!empty($wpdb->collate))
                $charset_collate .= " COLLATE $wpdb->collate";
        }


          $sql = "CREATE TABLE " . $MdlDb->ref_forms." (
          id int(11) NOT NULL auto_increment,
          form_key varchar(255) default NULL,
          name varchar(255) default NULL,
          description text default NULL,
          is_template boolean default 0,
          status varchar(255) default NULL,
          options longtext default NULL,
          created_date datetime NOT NULL,
          autoresponder_id VARCHAR(255),
          autoresponder_fname VARCHAR(255),
          autoresponder_lname VARCHAR(255),
          autoresponder_email VARCHAR(255),
          columns_list text default NULL,
          form_css longtext default NULL,
          form_id int(11) NOT NULL default 0,
          PRIMARY KEY  (id),
          UNIQUE KEY form_key (form_key)
          ) {$charset_collate};";

          $wpdb->query($sql);

          $wpdb->query("ALTER TABLE " .$MdlDb->ref_forms." AUTO_INCREMENT = 10000");
          
         
    }

    if (version_compare($newdbversion, '2.0.5', '<')) {

        global $wpdb;
        /*         * **** reposnder_id column not req. in form table
          $wpdb->query("ALTER TABLE ".$MdlDb->forms." MODIFY `autoresponder_id` VARCHAR(255) NULL DEFAULT NULL");
         */

        $updatestylesettings = new arstylemodel();

        update_option('arfa_options', $updatestylesettings);
        set_transient('arfa_options', $updatestylesettings);

        $updatestylesettings->set_default_options();
        $updatestylesettings->store();


        $cssoptions = get_option("arfa_options");
        $new_values = array();

        foreach ($cssoptions as $k => $v)
            $new_values[$k] = $v;
        $arfssl = (is_ssl()) ? 1 : 0;
        $filename = FORMPATH . '/core/css_create_main.php';

        if (is_file($filename)) {
            $uploads = wp_upload_dir();
            $target_path = $uploads['basedir'];
            $target_path .= "/arforms";
            $target_path .= "/css";
            $use_saved = true;
            $form_id = '';
            $css = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
            $css .= "\n";
            ob_start();
            include $filename;
            $css .= ob_get_contents();
            ob_end_clean();
            $css .= "\n " . $warn;
            $css_file = $target_path . '/arforms.css';

            WP_Filesystem();
            global $wp_filesystem;
            $wp_filesystem->put_contents($css_file, $css, 0777);

            update_option('arfa_css', $css);
            delete_transient('arfa_css');
            set_transient('arfa_css', $css);
        }


        global $wpdb, $db_record, $MdlDb;
        $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->forms . " WHERE status!=%s order by id desc",'draft'), OBJECT_K);

        foreach ($res as $key => $val) {
            $form_css_res = $wpdb->get_results($wpdb->prepare("SELECT id, form_css, options FROM " . $MdlDb->forms . " WHERE id = %d", $val->id), ARRAY_A);
            $form_id = $val->id;
            $cssoptions = maybe_unserialize($form_css_res[0]['form_css']);


            $formoptions = maybe_unserialize($form_css_res[0]['options']);
            $formoptions['admin_email_subject'] = '[form_name] ' . addslashes(esc_html__('Form submitted on', 'ARForms')) . ' [site_name] ';

            $sernewoptarr = maybe_serialize($formoptions);

            $res = $wpdb->update($MdlDb->forms, array('options' => $sernewoptarr), array('id' => $val->id));


            if (count($cssoptions) > 0) {
                $new_values = array();

                foreach ($cssoptions as $k => $v)
                    $new_values[$k] = str_replace("#", '', $v);

                $saving = true;
                $use_saved = true;
                $arfssl = (is_ssl()) ? 1 : 0;
                $filename = FORMPATH . '/core/css_create_main.php';
                $temp_css_file = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
                $temp_css_file .= "\n";
                ob_start();
                include $filename;
                $temp_css_file .= ob_get_contents();
                ob_end_clean();
                $temp_css_file .= "\n " . $warn;
                $wp_upload_dir = wp_upload_dir();
                $dest_dir = $wp_upload_dir['basedir'] . '/arforms/maincss/';
                $css_file_new = $dest_dir . 'maincss_' . $form_id . '.css';

                WP_Filesystem();
                global $wp_filesystem;
                $wp_filesystem->put_contents($css_file_new, $temp_css_file, 0777);
            }



            global $arffield;
            $form_fields = $arffield->getAll("fi.form_id = " . $form_id, " ORDER BY id");
            foreach ($form_fields as $key => $val) {
                $val->field_options['upload_font_color'] = arf_sanitize_value('#ffffff');

                $optionsnewval = maybe_serialize($val->field_options);
                $res = $wpdb->update($MdlDb->fields, array('field_options' => $optionsnewval), array('id' => $val->id));
            }
        }
    }

    if (version_compare($newdbversion, '2.5', '<')) {

        $wpdb->query("ALTER TABLE " . $MdlDb->fields . " ADD option_order text default NULL");



        $updatestylesettings = new arstylemodel();

        update_option('arfa_options', $updatestylesettings);
        set_transient('arfa_options', $updatestylesettings);

        $updatestylesettings->set_default_options();
        $updatestylesettings->store();


        $cssoptions = get_option("arfa_options");
        $new_values = array();

        foreach ($cssoptions as $k => $v)
            $new_values[$k] = $v;

        $arfssl = (is_ssl()) ? 1 : 0;

        $filename = FORMPATH . '/core/css_create_main.php';

        if (is_file($filename)) {
            $uploads = wp_upload_dir();
            $target_path = $uploads['basedir'];
            $target_path .= "/arforms";
            $target_path .= "/css";
            $use_saved = true;
            $form_id = '';
            $css = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
            $css .= "\n";
            ob_start();
            include $filename;
            $css .= ob_get_contents();
            ob_end_clean();
            $css .= "\n " . $warn;
            $css_file = $target_path . '/arforms.css';

            WP_Filesystem();
            global $wp_filesystem;
            $wp_filesystem->put_contents($css_file, $css, 0777);

            update_option('arfa_css', $css);
            delete_transient('arfa_css');
            set_transient('arfa_css', $css);
        }


        global $wpdb, $db_record, $MdlDb;
        $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->forms . " WHERE status!='draft' order by id desc",'draft'), OBJECT_K);

        foreach ($res as $key => $val) {
            $form_css_res = $wpdb->get_results($wpdb->prepare("SELECT id, form_css, options FROM " . $MdlDb->forms . " WHERE id = %d", $val->id), ARRAY_A);
            $form_id = $val->id;
            $cssoptions = maybe_unserialize($form_css_res[0]['form_css']);


            $cssoptions['arferrorstyle'] = arf_sanitize_value('advance');
            $cssoptions['arferrorstylecolor'] = arf_sanitize_value('#ed4040|#FFFFFF|#ed4040');
            $cssoptions['arferrorstylecolor2'] = arf_sanitize_value('#ed4040|#FFFFFF|#ed4040');
            $cssoptions['arferrorstyleposition'] = arf_sanitize_value('bottom');
            $cssoptions['arfsubmitautowidth'] = arf_sanitize_value('100');
            $cssoptions['arftitlefontfamily'] = arf_sanitize_value('Helvetica');

            if ($cssoptions['width_unit'] == "%") {
                $cssoptions['width'] = arf_sanitize_value('130');
                $cssoptions['width_unit'] = arf_sanitize_value('px');
            }

            $sernewarr = maybe_serialize($cssoptions);

            $formoptions = maybe_unserialize($form_css_res[0]['options']);

            $shortcodes = $armainhelper->get_shortcodes($formoptions['ar_email_message'], $val->id);
            if (count($shortcodes[3]) > 0 && is_array($shortcodes[3])) {
                global $arffield;
                foreach ($shortcodes[3] as $fieldkey => $fieldval) {
                    $field = $arffield->getOne($fieldval);
                    $myfieldname = $field->name;

                    $replacewith = '[' . $myfieldname . ':' . $fieldval . ']';

                    $formoptions['ar_email_message'] = str_replace('[' . $fieldval . ']', $replace_with, $formoptions['ar_email_message']);
                }
            }

            $shortcodes = $armainhelper->get_shortcodes($formoptions['ar_email_subject'], $val->id);
            if (count($shortcodes[3]) > 0 && is_array($shortcodes[3])) {
                global $arffield;
                foreach ($shortcodes[3] as $fieldkey => $fieldval) {
                    $field = $arffield->getOne($fieldval);
                    $myfieldname = $field->name;

                    $replacewith = '[' . $myfieldname . ':' . $fieldval . ']';

                    $formoptions['ar_email_subject'] = str_replace('[' . $fieldval . ']', $replace_with, $formoptions['ar_email_subject']);
                }
            }

            $shortcodes = $armainhelper->get_shortcodes($formoptions['ar_user_from_email'], $val->id);
            if (count($shortcodes[3]) > 0 && is_array($shortcodes[3])) {
                global $arffield;
                foreach ($shortcodes[3] as $fieldkey => $fieldval) {
                    $field = $arffield->getOne($fieldval);
                    $myfieldname = $field->name;

                    $replacewith = '[' . $myfieldname . ':' . $fieldval . ']';

                    $formoptions['ar_user_from_email'] = str_replace('[' . $fieldval . ']', $replace_with, $formoptions['ar_user_from_email']);
                }
            }

            $shortcodes = $armainhelper->get_shortcodes($formoptions['ar_admin_from_email'], $val->id);
            if (count($shortcodes[3]) > 0 && is_array($shortcodes[3])) {
                global $arffield;
                foreach ($shortcodes[3] as $fieldkey => $fieldval) {
                    $field = $arffield->getOne($fieldval);
                    $myfieldname = $field->name;

                    $replacewith = '[' . $myfieldname . ':' . $fieldval . ']';

                    $formoptions['ar_admin_from_email'] = str_replace('[' . $fieldval . ']', $replace_with, $formoptions['ar_admin_from_email']);
                }
            }

            $sernewoptarr = maybe_serialize($formoptions);

            $res = $wpdb->update($MdlDb->forms, array('form_css' => $sernewarr, 'options' => $sernewoptarr), array('id' => $val->id));

            if (count($cssoptions) > 0) {
                $new_values = array();

                foreach ($cssoptions as $k => $v)
                    $new_values[$k] = str_replace("#", '', $v);

                $saving = true;
                $use_saved = true;
                $arfssl = (is_ssl()) ? 1 : 0;
                $filename = FORMPATH . '/core/css_create_main.php';
                $temp_css_file = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
                $temp_css_file .= "\n";
                ob_start();
                include $filename;
                $temp_css_file .= ob_get_contents();
                ob_end_clean();
                $temp_css_file .= "\n " . $warn;
                $wp_upload_dir = wp_upload_dir();
                $dest_dir = $wp_upload_dir['basedir'] . '/arforms/maincss/';
                $css_file_new = $dest_dir . 'maincss_' . $form_id . '.css';

                WP_Filesystem();
                global $wp_filesystem;
                $wp_filesystem->put_contents($css_file_new, $temp_css_file, 0777);
            }



            global $arffield;
            $form_fields = $arffield->getAll("fi.form_id = " . $form_id, " ORDER BY id");
            foreach ($form_fields as $key => $val) {
                $val->field_options['lbllike'] = addslashes(esc_html__(arf_sanitize_value('Like'), 'ARForms'));
                $val->field_options['lbldislike'] = addslashes(esc_html__(arf_sanitize_value('Dislike'), 'ARForms'));
                $val->field_options['slider_handle'] = arf_sanitize_value('round');
                $val->field_options['slider_step'] = arf_sanitize_value('1');
                $val->field_options['slider_bg_color'] = arf_sanitize_value('#d1dee5');
                $val->field_options['slider_handle_color'] = arf_sanitize_value('#0480BE');
                $val->field_options['slider_value'] = arf_sanitize_value('1');
                $val->field_options['like_bg_color'] = arf_sanitize_value('#087ee2');
                $val->field_options['dislike_bg_color'] = arf_sanitize_value('#ff1f1f');
                $val->field_options['slider_bg_color2'] = arf_sanitize_value('#bcc7cd');
                $val->field_options['upload_font_color'] = arf_sanitize_value('#ffffff');
                $val->field_options['confirm_password'] = arf_sanitize_value('0');
                $val->field_options['password_strenth'] = arf_sanitize_value('0');
                $val->field_options['invalid_password'] = addslashes(esc_html__(arf_sanitize_value('Confirm Password does not match with password'), 'ARForms'));
                $val->field_options['placehodertext'] = '';
                $val->field_options['phone_validation'] = arf_sanitize_value('international');
                $val->field_options['confirm_password_label'] = addslashes(esc_html__(arf_sanitize_value('Confirm Password'), 'ARForms'));


                if ($val->field_options['custom_width_field'] == '0') {
                    $val->field_options['field_width'] = '';
                }

                $optionsnewval = maybe_serialize($val->field_options);
                $res = $wpdb->update($MdlDb->fields, array('field_options' => $optionsnewval), array('id' => $val->id));
            }
        }




        global $wpdb, $db_record, $MdlDb;
        $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->forms . " WHERE id =%d and is_template = %d ",1,1), OBJECT_K);

        foreach ($res as $key => $val) {
            $form_id = $val->id;

            $form_css_res = $wpdb->get_results($wpdb->prepare("SELECT id, form_css, options FROM " . $MdlDb->forms . " WHERE id = %d", $val->id), ARRAY_A);
            $formoptions = maybe_unserialize($form_css_res[0]['options']);

            $formoptions['display_title_form'] = '1';
            $sernewoptarr = maybe_serialize($formoptions);

            $res = $wpdb->update($MdlDb->forms, array('options' => $sernewoptarr), array('id' => $val->id));

            $cssoptions = get_option("arfa_options");

            $new_values = array();

            foreach ($cssoptions as $k => $v)
                $new_values[$k] = $v;

            $new_values['arfmainformwidth'] = arf_sanitize_value("550");
            $new_values['form_width_unit'] = arf_sanitize_value("px");
            $new_values['form_border_shadow'] = arf_sanitize_value("shadow");
            $new_values['arfmainformbordershadowcolorsetting'] = arf_sanitize_value("#d4d2d4");
            $new_values['arfmainformtitlecolorsetting'] = arf_sanitize_value("#696969");
            $new_values['arfformtitlealign'] = arf_sanitize_value("center");
            $new_values['check_weight_form_title'] = arf_sanitize_value("bold");
            $new_values['form_title_font_size'] = arf_sanitize_value(26, 'integer');
            $new_values['arfmainformtitlepaddingsetting_3'] = arf_sanitize_value(25, 'integer');
            $new_values['width'] = arf_sanitize_value(90, 'integer');
            $new_values['arfdescfontsizesetting'] = arf_sanitize_value(14, 'integer');
            $new_values['arfbgactivecolorsetting'] = arf_sanitize_value("#fafafa");
            $new_values['arfborderactivecolorsetting'] = arf_sanitize_value("#20bfe3");
            $new_values['arffieldborderwidthsetting'] = arf_sanitize_value("2");
            $new_values['arffieldinnermarginssetting_1'] = arf_sanitize_value("10");
            $new_values['arffieldinnermarginssetting_3'] = arf_sanitize_value("10");
            $new_values['arfsubmitalignsetting'] = arf_sanitize_value("auto");
            $new_values['arfsubmitbuttonwidthsetting'] = arf_sanitize_value("150");
            $new_values['arfsubmitbuttonheightsetting'] = arf_sanitize_value("42");
            $new_values['submit_bg_color'] = arf_sanitize_value("#20bfe3");
            $new_values['arfsubmitbuttonbgcolorhoversetting'] = arf_sanitize_value("#19adcf");
            $new_values['arfsubmitbordercolorsetting'] = arf_sanitize_value("#e1e1e3");
            $new_values['arfsubmitshadowcolorsetting'] = arf_sanitize_value("#f0f0f0");
            $new_values['arfsubmitbuttonmarginsetting_4'] = arf_sanitize_value("-20");
            $new_values['arffieldmarginssetting'] = arf_sanitize_value(20, 'integer');
            $new_values['arferrorstyle'] = arf_sanitize_value("normal");

            $new_values1 = maybe_serialize($new_values);

            $res = $wpdb->update($MdlDb->forms, array('form_css' => $new_values1), array('id' => $val->id));


            if (!empty($new_values)) {

                $query_results = $wpdb->query($wpdb->prepare("update " . $MdlDb->forms . " set form_css = '%s' where id = '%d'", $new_values1, $form_id));

                $use_saved = true;
                $arfssl = (is_ssl()) ? 1 : 0;
                $filename = FORMPATH . '/core/css_create_main.php';
                $wp_upload_dir = wp_upload_dir();
                $target_path = $wp_upload_dir['basedir'] . '/arforms/maincss';
                $css = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
                $css .= "\n";
                ob_start();
                include $filename;
                $css .= ob_get_contents();
                ob_end_clean();
                $css .= "\n " . $warn;
                $css_file = $target_path . '/maincss_' . $form_id . '.css';
                WP_Filesystem();
                global $wp_filesystem;
                $wp_filesystem->put_contents($css_file, $css, 0777);
            }

            $query_results_r1 = $wpdb->query($wpdb->prepare("DELETE FROM " . $MdlDb->fields . " WHERE `form_id` = %d", $form_id));

            $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('text', $form_id));
            $field_values['name'] = 'First Name';
            $field_values['default_value'] = 'First Name';
            $field_values['description'] = '';
            $field_values['required'] = 1;

            $field_values['field_options']['classes'] = '';
            $arffield->create($field_values);
            unset($field_values);

            $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('text', $form_id));
            $field_values['name'] = 'Last Name';
            $field_values['default_value'] = 'Last Name';
            $field_values['description'] = '';
            $field_values['required'] = 1;

            $field_values['field_options']['classes'] = '';
            $arffield->create($field_values);
            unset($field_values);

            $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('email', $form_id));
            $field_values['name'] = 'Email';
            $field_values['default_value'] = 'Email Address';
            $field_values['required'] = 1;
            $field_values['field_options']['invalid'] = 'Please enter a valid email address';
            $field_values['field_options']['classes'] = '';
            $arffield->create($field_values);
            unset($field_values);
            unset($values);
        }





        global $wpdb, $db_record, $MdlDb;
        $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->forms . " WHERE id = %d and is_template = %d ",3,1), OBJECT_K);

        foreach ($res as $key => $val) {
            $form_id = $val->id;

            $form_css_res = $wpdb->get_results($wpdb->prepare("SELECT id, form_css, options FROM " . $MdlDb->forms . " WHERE id = %d", $val->id), ARRAY_A);
            $formoptions = maybe_unserialize($form_css_res[0]['options']);

            $formoptions['display_title_form'] = arf_sanitize_value('1');
            $sernewoptarr = maybe_serialize($formoptions);

            $res = $wpdb->update($MdlDb->forms, array('options' => $sernewoptarr), array('id' => $val->id));

            $cssoptions = get_option("arfa_options");

            $new_values = array();

            foreach ($cssoptions as $k => $v)
                $new_values[$k] = $v;

            $new_values['arfmainformtitlecolorsetting'] = arf_sanitize_value("#0d0e12");
            $new_values['arfmainformtitlepaddingsetting_3'] = arf_sanitize_value(30, 'integer');
            $new_values['border_radius'] = arf_sanitize_value(2, 'integer');
            $new_values['arffieldmarginssetting'] = arf_sanitize_value(18, 'integer');
            $new_values['arffieldinnermarginssetting_1'] = arf_sanitize_value(10, 'integer');
            $new_values['arffieldinnermarginssetting_3'] = arf_sanitize_value(10, 'integer');
            $new_values['arfsubmitbuttonwidthsetting'] = arf_sanitize_value(120, 'integer');
            $new_values['arfsubmitbuttonheightsetting'] = arf_sanitize_value(40, 'integer');
            $new_values['arfsubmitbuttonmarginsetting_1'] = arf_sanitize_value(20, 'integer');
            $new_values['arfsubmitbuttonmarginsetting_4'] = arf_sanitize_value("-46");

            $new_values1 = maybe_serialize($new_values);

            $res = $wpdb->update($MdlDb->forms, array('form_css' => $new_values1), array('id' => $val->id));

            if (!empty($new_values)) {

                $query_results = $wpdb->query($wpdb->prepare("update " . $MdlDb->forms . " set form_css = '%s' where id = '%d'", $new_values1, $form_id));

                $use_saved = true;
                $arfssl = (is_ssl()) ? 1 : 0;
                $filename = FORMPATH . '/core/css_create_main.php';
                $wp_upload_dir = wp_upload_dir();
                $target_path = $wp_upload_dir['basedir'] . '/arforms/maincss';
                $css = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
                $css .= "\n";
                ob_start();
                include $filename;
                $css .= ob_get_contents();
                ob_end_clean();
                $css .= "\n " . $warn;
                $css_file = $target_path . '/maincss_' . $form_id . '.css';
                WP_Filesystem();
                global $wp_filesystem;
                $wp_filesystem->put_contents($css_file, $css, 0777);
            }

            $query_results_r1 = $wpdb->query($wpdb->prepare("DELETE FROM " . $MdlDb->fields . " WHERE `form_id` = %d", $form_id));

            $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('text', $form_id));
            $field_values['name'] = 'First Name';
            $field_values['description'] = '';
            $field_values['required'] = 1;
            // $field_values['field_order'] = '1';
            $field_values['field_options']['classes'] = '';
            $field_id = $arffield->create($field_values, true);
            $field_order[$field_id] = '1';
            unset($field_values);
            unset($field_id);

            $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('text', $form_id));
            $field_values['name'] = 'Last Name';
            $field_values['required'] = 1;
            //$field_values['field_order'] = '2';
            $field_values['field_options']['label'] = 'hidden';
            $field_values['field_options']['classes'] = '';
            $field_id = $arffield->create($field_values, true);
            $field_order[$field_id] = '2';
            unset($field_values);
            unset($field_id);

            $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('email', $form_id));
            $field_values['name'] = addslashes(esc_html__('Email', 'ARForms'));
            $field_values['required'] = 1;
            $field_values['field_options']['invalid'] = addslashes(esc_html__('Please enter a valid email address', 'ARForms'));
            //$field_values['field_order'] = '3';
            $field_values['field_options']['classes'] = '';
            $field_id = $arffield->create($field_values, true);
            $field_order[$field_id] = '3';
            unset($field_values);
            unset($field_id);

            $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('url', $form_id));
            $field_values['name'] = addslashes(esc_html__('Website', 'ARForms'));
            $field_values['field_options']['invalid'] = addslashes(esc_html__('Please enter a valid website', 'ARForms'));
            //$field_values['field_order'] = '4';
            $field_values['field_options']['classes'] = '';
            $field_id = $arffield->create($field_values, true);
            $field_order[$field_id] = '4';
            unset($field_values);
            unset($field_id);

            $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('text', $form_id));
            $field_values['name'] = addslashes(esc_html__('Subject', 'ARForms'));
            $field_values['required'] = 1;
            //$field_values['field_order'] = '5';
            $field_values['field_options']['classes'] = '';
            $field_id = $arffield->create($field_values, true);
            $field_order[$field_id] = '5';
            unset($field_values);
            unset($field_id);

            $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('textarea', $form_id));
            $field_values['name'] = addslashes(esc_html__('Message', 'ARForms'));
            $field_values['required'] = 1;
            //$field_values['field_order'] = '6';
            $field_values['field_options']['classes'] = '';
            $field_id = $arffield->create($field_values, true);
            $field_order[$field_id] = '6';
            unset($field_values);
            unset($field_id);

            $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('captcha', $form_id));
            $field_values['name'] = addslashes(esc_html__('Captcha', 'ARForms'));
            $field_values['field_options']['label'] = 'none';
            $field_values['field_options']['is_recaptcha'] = 'custom-captcha';
            //$field_values['field_order'] = '7';
            $field_id = $arffield->create($field_values, true);
            $field_order[$field_id] = '7';
            unset($field_values);
            unset($field_id);
            unset($values);

            $field_options = $wpdb->get_results($wpdb->prepare("SELECT `options` FROM `" . $MdlDb->forms . "` WHERE `id` = %d", $form_id));

            $form_opt = maybe_unserialize($field_options[0]->options);

            $form_opt['arf_field_order'] = json_encode($field_order);

            $form_options = maybe_serialize($form_opt);

            $wpdb->update($MdlDb->forms, array('options' => $form_options), array('id' => $form_id));
            unset($field_order);
        }


        global $wpdb, $db_record, $MdlDb;
        $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->forms . " WHERE id = %d and is_template = %d ",4,1), OBJECT_K);

        foreach ($res as $key => $val) {
            $form_id = $val->id;

            $form_css_res = $wpdb->get_results($wpdb->prepare("SELECT id, form_css, options FROM " . $MdlDb->forms . " WHERE id = %d", $val->id), ARRAY_A);
            $formoptions = maybe_unserialize($form_css_res[0]['options']);

            $formoptions['display_title_form'] = arf_sanitize_value('1');
            $formoptions['arf_form_title'] = arf_sanitize_value("border-bottom:1px solid #4a494a;padding-bottom:5px;");
            $sernewoptarr = maybe_serialize($formoptions);

            $res = $wpdb->update($MdlDb->forms, array('options' => $sernewoptarr), array('id' => $val->id));

            $cssoptions = get_option("arfa_options");

            $new_values = array();

            foreach ($cssoptions as $k => $v)
                $new_values[$k] = $v;

            $new_values['fieldset'] = arf_sanitize_value("0");
            $new_values['arfformtitlealign'] = arf_sanitize_value("center");
            $new_values['check_weight_form_title'] = arf_sanitize_value("bold");
            $new_values['form_title_font_size'] = arf_sanitize_value("32");

            $new_values1 = maybe_serialize($new_values);

            $res = $wpdb->update($MdlDb->forms, array('form_css' => $new_values1), array('id' => $val->id));

            if (!empty($new_values)) {

                $query_results = $wpdb->query($wpdb->prepare("update " . $MdlDb->forms . " set form_css = '%s' where id = '%d'", $new_values1, $form_id));

                $use_saved = true;
                $arfssl = (is_ssl()) ? 1 : 0;
                $filename = FORMPATH . '/core/css_create_main.php';
                $wp_upload_dir = wp_upload_dir();
                $target_path = $wp_upload_dir['basedir'] . '/arforms/maincss';
                $css = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
                $css .= "\n";
                ob_start();
                include $filename;
                $css .= ob_get_contents();
                ob_end_clean();
                $css .= "\n " . $warn;
                $css_file = $target_path . '/maincss_' . $form_id . '.css';
                WP_Filesystem();
                global $wp_filesystem;
                $wp_filesystem->put_contents($css_file, $css, 0777);
            }

            $query_results_r1 = $wpdb->query($wpdb->prepare("DELETE FROM " . $MdlDb->fields . " WHERE `form_id` = %d", $form_id));

            $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('radio', $form_id));
            $field_values['name'] = '1. When you visit ARForms, do you see it as... (choose one)';
            $field_values['required'] = 1;
            $field_values['field_options']['classes'] = '';
            $field_values['options'] = maybe_serialize(array('Problem solvers', 'An inspiration', 'Ideas generator', 'Solution'));
            $field_values['field_options']['css_input_element'] = 'padding-top:10px;padding-left:20px;';
            $arffield->create($field_values);
            unset($field_values);

            $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('checkbox', $form_id));
            $field_values['name'] = '2. Which words best describe ARForms? (choose as many that apply)';
            $field_values['required'] = 1;
            $field_values['field_options']['classes'] = '';
            $field_values['options'] = maybe_serialize(array('Unhelpful', 'Difficult to use', 'Supportive', 'Solutions focused', 'Good value', 'Global', 'Community based', 'Friendly', 'Creative', 'Inspiring', 'Developer world'));
            $field_values['field_options']['css_input_element'] = 'padding-top:10px;padding-left:20px;';
            $arffield->create($field_values);
            unset($field_values);

            $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('radio', $form_id));
            $field_values['name'] = '3. Which best describes your relationship with ARForms?';
            $field_values['required'] = 1;
            $field_values['field_options']['classes'] = '';
            $field_values['options'] = maybe_serialize(array('I am aware of it', 'Rarely use it', 'Use it sometimes', 'Frequent user', 'Do not know it'));
            $field_values['field_options']['css_input_element'] = 'padding-top:10px;padding-left:20px;';
            $arffield->create($field_values);
            unset($field_values);

            $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('radio', $form_id));
            $field_values['name'] = '4. When I visit ARForms for something I need to work on, I feel...(choose one)';
            $field_values['required'] = 1;
            $field_values['field_options']['classes'] = '';
            $field_values['options'] = maybe_serialize(array('Concerned I won\'t be able to find what I am looking for', 'Inspired', 'Reluctant', 'Indifferent', 'Excited to be starting a project', 'Know I will end up browsing lots of things'));
            $field_values['field_options']['css_input_element'] = 'padding-top:10px;padding-left:20px;';
            $arffield->create($field_values);
            unset($field_values);

            $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('radio', $form_id));
            $field_values['name'] = '5. Which of the following best describes your area of work?';
            $field_values['required'] = 1;
            $field_values['field_options']['classes'] = '';
            $field_values['options'] = maybe_serialize(array('Administrative', 'Computing', 'Web Design', 'Creative', 'Web Development', 'Marketing', 'Technical'));
            $field_values['field_options']['css_input_element'] = 'padding-top:10px;padding-left:20px;';
            $arffield->create($field_values);
            unset($field_values);

            $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('radio', $form_id));
            $field_values['name'] = '6. How often do you use ARForms?';
            $field_values['required'] = 1;
            $field_values['field_options']['classes'] = '';
            $field_values['options'] = maybe_serialize(array('It is my first time', 'Weekly', 'Monthly', 'Quarterly', 'Annually', 'Occasionally'));
            $field_values['field_options']['css_input_element'] = 'padding-top:10px;padding-left:20px;';
            $arffield->create($field_values);
            unset($field_values);

            $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('textarea', $form_id));
            $field_values['name'] = 'Other Comments About ARForms';
            $field_values['required'] = 0;
            $field_values['field_options']['classes'] = '';
            $arffield->create($field_values);
            unset($field_values);

            unset($values);
        }


        global $wpdb, $db_record, $MdlDb;
        $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->forms . " WHERE id = %d and is_template = %d ",6,1), OBJECT_K);

        foreach ($res as $key => $val) {
            $form_id = $val->id;

            $form_css_res = $wpdb->get_results($wpdb->prepare("SELECT id, form_css, options FROM " . $MdlDb->forms . " WHERE id = %d", $val->id), ARRAY_A);
            $formoptions = maybe_unserialize($form_css_res[0]['options']);

            $formoptions['display_title_form'] = arf_sanitize_value('1');
            $formoptions['arf_form_title'] = arf_sanitize_value("background-color:rgb(147, 217, 226);padding: 10px;border-radius:5px;-webkit-border-radius:5px;-o-border-radius:5px;-moz-border-radius:5px;");
            $sernewoptarr = maybe_serialize($formoptions);

            $res = $wpdb->update($MdlDb->forms, array('options' => $sernewoptarr), array('id' => $val->id));

            $cssoptions = get_option("arfa_options");

            $new_values = array();

            foreach ($cssoptions as $k => $v)
                $new_values[$k] = $v;

            $new_values['form_border_shadow'] = arf_sanitize_value("shadow");
            $new_values['form_border_shadow'] = arf_sanitize_value(1, 'integer');
            $new_values['arfmainfieldsetcolor'] = arf_sanitize_value("#c9c7c9");
            $new_values['arfmainformbordershadowcolorsetting'] = arf_sanitize_value("#ebebeb");
            $new_values['arfmainformtitlecolorsetting'] = arf_sanitize_value("#ffffff");
            $new_values['arfformtitlealign'] = arf_sanitize_value("center");
            $new_values['arftitlefontfamily'] = arf_sanitize_value("Courier");
            $new_values['check_weight_form_title'] = arf_sanitize_value("bold");
            $new_values['form_title_font_size'] = arf_sanitize_value(28, 'integer');
            $new_values['arfmainformtitlepaddingsetting_3'] = arf_sanitize_value(30, 'integer');
            $new_values['check_font'] = arf_sanitize_value("sans-serif");
            $new_values['text_color'] = arf_sanitize_value("#384647");
            $new_values['arfborderactivecolorsetting'] = arf_sanitize_value("#6fdeed");
            $new_values['arferrorbordercolorsetting'] = arf_sanitize_value("#f28888");
            $new_values['arfcheckradiocolor'] = arf_sanitize_value("aero");
            $new_values['arfsubmitfontfamily'] = arf_sanitize_value("Verdana");
            $new_values['arfsubmitweightsetting'] = arf_sanitize_value("bold");
            $new_values['arfsubmitbuttonfontsizesetting'] = arf_sanitize_value("19");
            $new_values['arfsubmitbuttonwidthsetting'] = arf_sanitize_value("140");
            $new_values['arfsubmitbuttonheightsetting'] = arf_sanitize_value("44");
            $new_values['submit_bg_color'] = arf_sanitize_value("#84d1db");
            $new_values['arfsubmitbuttonbgcolorhoversetting'] = arf_sanitize_value("#6ac7d4");
            $new_values['arfsubmitshadowcolorsetting'] = arf_sanitize_value("#f0f0f0");
            $new_values['arfsubmitbuttonmarginsetting_1'] = arf_sanitize_value("15");
            $new_values['arfsubmitbuttonmarginsetting_4'] = arf_sanitize_value("-45");
            $new_values['arferrorstylecolor'] = arf_sanitize_value("#F2DEDE|#A94442|#508b27");

            $new_values1 = maybe_serialize($new_values);

            $res = $wpdb->update($MdlDb->forms, array('form_css' => $new_values1), array('id' => $val->id));

            if (!empty($new_values)) {

                $query_results = $wpdb->query($wpdb->prepare("update " . $MdlDb->forms . " set form_css = '%s' where id = '%d'", $new_values1, $form_id));

                $use_saved = true;
                $arfssl = (is_ssl()) ? 1 : 0;
                $filename = FORMPATH . '/core/css_create_main.php';
                $wp_upload_dir = wp_upload_dir();
                $target_path = $wp_upload_dir['basedir'] . '/arforms/maincss';
                $css = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
                $css .= "\n";
                ob_start();
                include $filename;
                $css .= ob_get_contents();
                ob_end_clean();
                $css .= "\n " . $warn;
                $css_file = $target_path . '/maincss_' . $form_id . '.css';
                WP_Filesystem();
                global $wp_filesystem;
                $wp_filesystem->put_contents($css_file, $css, 0777);
            }

            $query_results_r1 = $wpdb->query($wpdb->prepare("DELETE FROM " . $MdlDb->fields . " WHERE `form_id` = %d", $form_id));

            $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('text', $form_id));
            $field_values['name'] = 'Full Name';
            $field_values['required'] = 1;
            $field_values['field_options']['classes'] = '';
            $arffield->create($field_values);
            unset($field_values);

            $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('email', $form_id));
            $field_values['name'] = 'Email';
            $field_values['required'] = 1;
            $field_values['field_options']['classes'] = '';
            $arffield->create($field_values);
            unset($field_values);

            $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('phone', $form_id));
            $field_values['name'] = 'Phone';
            $field_values['required'] = 1;
            $field_values['field_options']['classes'] = '';
            $arffield->create($field_values);
            unset($field_values);

            $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('textarea', $form_id));
            $field_values['name'] = 'Address';
            $field_values['required'] = 1;
            $field_values['field_options']['classes'] = '';
            $arffield->create($field_values);
            unset($field_values);

            $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('text', $form_id));
            $field_values['name'] = 'City';
            $field_values['required'] = 1;
            $field_values['field_options']['classes'] = '';
            $arffield->create($field_values);
            unset($field_values);

            $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('select', $form_id));
            $field_values['name'] = 'Your Meal Selection';
            $field_values['required'] = 1;
            $field_values['field_options']['classes'] = '';
            $field_values['options'] = maybe_serialize(array('Chicken', 'Steak', 'Vegetarian'));
            $arffield->create($field_values);
            unset($field_values);

            $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('select', $form_id));
            $field_values['name'] = 'Are you bringing a guest?';
            $field_values['required'] = 1;
            $field_values['field_options']['classes'] = '';
            $field_values['options'] = maybe_serialize(array('Yes', 'No'));
            $bringing_guest_field_id = $arffield->create($field_values);
            unset($field_values);

            $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('select', $form_id));
            $field_values['name'] = 'How many guests will be there?';
            $field_values['required'] = 1;
            $field_values['field_options']['classes'] = '';
            $field_values['options'] = maybe_serialize(array('One', 'Two', 'Three', 'Four'));
            $conditional_rule = array(
                '1' => array(
                    'id' => 1,
                    'field_id' => $bringing_guest_field_id,
                    'field_type' => 'select',
                    'operator' => 'equals',
                    'value' => 'Yes',
                ),
            );
            $conditional_logic_exp = array(
                'enable' => 1,
                'display' => 'show',
                'if_cond' => 'all',
                'rules' => $conditional_rule,
            );
            $field_values['conditional_logic'] = maybe_serialize($conditional_logic_exp);

            $arffield->create($field_values);
            unset($field_values);

            $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('time', $form_id));
            $field_values['name'] = 'Which is your suitable time?';
            $field_values['required'] = 1;
            $field_values['field_options']['classes'] = '';
            $arffield->create($field_values);
            unset($field_values);

            $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('radio', $form_id));
            $field_values['name'] = 'How much interested in our ARForms?';
            $field_values['required'] = 1;
            $field_values['field_options']['classes'] = '';
            $field_values['options'] = maybe_serialize(array('Extremely', 'Very', 'Moderately', 'Slightly', 'Not Excited'));
            $arffield->create($field_values);
            unset($field_values);
            unset($values);
        }


        global $arffield, $arfform, $MdlDb, $wpdb;

        $values['name'] = addslashes(esc_html__('Job Application Form', 'ARForms'));
        $values['description'] = '';
        $values['options']['custom_style'] = 1;
        $values['options']['display_title_form'] = 1;
        $values['is_template'] = '1';
        $values['status'] = 'published';
        $values['form_key'] = 'JobApplication';
        $values['options']['display_title_form'] = "1";
        $values['options']['arf_form_description'] = "margin:0px !important;";

        $form_id = $arfform->create($values);

        $updatestat = $wpdb->query($wpdb->prepare("update " . $MdlDb->forms . " set id = '7' where id = %d", $form_id));

        $form_id = '7';

        $cssoptions = get_option("arfa_options");

        $new_values = array();

        foreach ($cssoptions as $k => $v)
            $new_values[$k] = $v;

        $new_values['arfmainformwidth'] = "800";
        $new_values['arfmainformbgcolorsetting'] = "#fcfcfc";
        $new_values['form_width_unit'] = "px";
        $new_values['form_border_shadow'] = "shadow";
        $new_values['fieldset'] = "1";
        $new_values['arfmainfieldsetcolor'] = "#e0e0de";
        $new_values['arfmainformbordershadowcolorsetting'] = "#dedede";
        $new_values['arfmainfieldsetpadding_1'] = "20";
        $new_values['arfmainfieldsetpadding_2'] = "30";
        $new_values['arfmainfieldsetpadding_4'] = "30";
        $new_values['arfmainformtitlecolorsetting'] = "#767a74";
        $new_values['arfformtitlealign'] = "center";
        $new_values['check_weight_form_title'] = "bold";
        $new_values['arfmainformtitlepaddingsetting_3'] = "30";
        $new_values['label_color'] = "#787778";
        $new_values['weight'] = "bold";
        $new_values['font_size'] = "14";
        $new_values['text_color'] = "#565657";
        $new_values['bg_color'] = "#fffcff";
        $new_values['arfbgactivecolorsetting'] = "#f5f9fc";
        $new_values['arferrorbordercolorsetting'] = "#ebc173";
        $new_values['border_radius'] = "2";
        $new_values['border_color'] = "#b0b0b5";
        $new_values['arffieldmarginssetting'] = "18";
        $new_values['arfcheckradiostyle'] = "square";
        $new_values['arfcheckradiocolor'] = "yellow";
        $new_values['arfsubmitalignsetting'] = "auto";
        $new_values['arfsubmitbuttonwidthsetting'] = "100";
        $new_values['arfsubmitbuttonheightsetting'] = "45";
        $new_values['arfsubmitbuttontext'] = "Apply Now";
        $new_values['submit_bg_color'] = "#a969e0";
        $new_values['arfsubmitbuttonbgcolorhoversetting'] = "#9249d1";
        $new_values['arfsubmitbuttonmarginsetting_1'] = "0";
        $new_values['error_font'] = "Verdana";
        $new_values['arffontsizesetting'] = "11";
        $new_values['arferrorstylecolor'] = "#FAEBCC|#8A6D3B|#af7a0c";
        $new_values['arferrorstyleposition'] = "right";
        $new_values['arfborderactivecolorsetting'] = '#a969e0';




        $new_values1 = maybe_serialize($new_values);

        if (!empty($new_values)) {

            $query_results = $wpdb->query($wpdb->prepare("update " . $MdlDb->forms . " set form_css = '%s' where id = '%d'", $new_values1, $form_id));

            $use_saved = true;
            $arfssl = (is_ssl()) ? 1 : 0;
            $filename = FORMPATH . '/core/css_create_main.php';

            $wp_upload_dir = wp_upload_dir();

            $target_path = $wp_upload_dir['basedir'] . '/arforms/maincss';

            $css = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";


            $css .= "\n";


            ob_start();


            include $filename;


            $css .= ob_get_contents();


            ob_end_clean();


            $css .= "\n " . $warn;

            $css_file = $target_path . '/maincss_' . $form_id . '.css';

            WP_Filesystem();
            global $wp_filesystem;
            $wp_filesystem->put_contents($css_file, $css, 0777);

            wp_cache_delete($form_id, 'arfform');
        } else {

            $query_results = true;
        }


        $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('text', $form_id));
        $field_values['name'] = addslashes(esc_html__('First Name', 'ARForms'));
        $field_values['required'] = 1;
        $field_values['field_options']['classes'] = 'arf_2';
        $field_values['default_value'] = 'First Name';
        $field_values['field_options']['blank'] = 'Please Enter First Name';
        $arffield->create($field_values);
        unset($field_values);

        $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('text', $form_id));
        $field_values['name'] = addslashes(esc_html__('Last name', 'ARForms'));
        $field_values['required'] = 0;
        $field_values['field_options']['classes'] = 'arf_2';
        $field_values['default_value'] = 'Last Name';
        $field_values['field_options']['blank'] = 'Please Enter Last Name';
        $arffield->create($field_values);
        unset($field_values);

        $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('email', $form_id));
        $field_values['name'] = addslashes(esc_html__('Email', 'ARForms'));
        $field_values['required'] = 1;
        $field_values['field_options']['classes'] = 'arf_2';
        $field_values['default_value'] = 'Email';
        $field_values['field_options']['blank'] = 'Please Enter Email';
        $arffield->create($field_values);
        unset($field_values);

        $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('phone', $form_id));
        $field_values['name'] = addslashes(esc_html__('Contact No', 'ARForms'));
        $field_values['required'] = 1;
        $field_values['field_options']['classes'] = 'arf_2';
        $field_values['default_value'] = 'Contact No';
        $field_values['field_options']['blank'] = 'Please Enter Contact No';
        $arffield->create($field_values);
        unset($field_values);

        $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('textarea', $form_id));
        $field_values['name'] = addslashes(esc_html__('Address', 'ARForms'));
        $field_values['required'] = 1;
        $field_values['field_options']['classes'] = '';
        $field_values['field_options']['blank'] = 'Please Enter Address';
        $field_values['field_options']['max'] = '2';
        $arffield->create($field_values);
        unset($field_values);

        $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('select', $form_id));
        $field_values['name'] = addslashes(esc_html__('Position apply for?', 'ARForms'));
        $field_values['required'] = 1;
        $field_values['field_options']['classes'] = 'arf_2';
        $field_values['options'] = maybe_serialize(array('', addslashes(esc_html__('Developer', 'ARForms')), addslashes(esc_html__('Manager', 'ARForms')), addslashes(esc_html__('Clerk', 'ARForms')), addslashes(esc_html__('Representative', 'ARForms'))));
        $field_values['field_options']['blank'] = 'Please Select Position';
        $arffield->create($field_values);
        unset($field_values);

        $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('select', $form_id));
        $field_values['name'] = addslashes(esc_html__('Are you applying for?', 'ARForms'));
        $field_values['required'] = 1;
        $field_values['field_options']['classes'] = 'arf_2';
        $field_values['options'] = maybe_serialize(array('', addslashes(esc_html__('Full Time', 'ARForms')), addslashes(esc_html__('Part Time', 'ARForms'))));
        $field_values['field_options']['blank'] = 'Please Select Applying for';
        $arffield->create($field_values);
        unset($field_values);

        $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('divider', $form_id));
        $field_values['name'] = addslashes(esc_html__('Education and Experience Details', 'ARForms'));
        $field_values['required'] = 0;
        $field_values['field_options']['css_label'] = 'padding-top:20px;margin-bottom:20px;';
        $field_values['field_options']['arf_divider_font'] = 'Arial';
        $field_values['field_options']['arf_divider_font_size'] = '18';
        $field_values['field_options']['arf_divider_bg_color'] = '#fcfcfc';
        $field_values['field_options']['classes'] = '';
        $arffield->create($field_values);
        unset($field_values);

        $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('text', $form_id));
        $field_values['name'] = addslashes(esc_html__('Diploma / Degree Name', 'ARForms'));
        $field_values['required'] = 1;
        $field_values['field_options']['classes'] = 'arf_2';
        $field_values['field_options']['blank'] = 'Please Enter Diploma / Degree';
        $arffield->create($field_values);
        unset($field_values);

        $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('text', $form_id));
        $field_values['name'] = addslashes(esc_html__('College / University Name', 'ARForms'));
        $field_values['required'] = 1;
        $field_values['field_options']['classes'] = 'arf_2';
        $field_values['field_options']['blank'] = 'Please Enter College / University';
        $arffield->create($field_values);
        unset($field_values);

        $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('number', $form_id));
        $field_values['name'] = addslashes(esc_html__('Graduation Year', 'ARForms'));
        $field_values['required'] = 1;
        $field_values['field_options']['classes'] = 'arf_2';
        $field_values['field_options']['blank'] = 'Please Enter Graduation Year';
        $arffield->create($field_values);
        unset($field_values);

        $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('text', $form_id));
        $field_values['name'] = addslashes(esc_html__('Percentage', 'ARForms'));
        $field_values['required'] = 1;
        $field_values['field_options']['classes'] = 'arf_2';
        $field_values['field_options']['blank'] = 'Please Enter Percentage';
        $arffield->create($field_values);
        unset($field_values);

        $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('textarea', $form_id));
        $field_values['name'] = addslashes(esc_html__('Skills & Qualification', 'ARForms'));
        $field_values['required'] = 1;
        $field_values['field_options']['classes'] = '';
        $field_values['field_options']['blank'] = 'Please Enter Skills & Qualification';
        $field_values['field_options']['max'] = '2';
        $arffield->create($field_values);
        unset($field_values);

        $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('number', $form_id));
        $field_values['name'] = addslashes(esc_html__('Desired Salary', 'ARForms'));
        $field_values['required'] = 1;
        $field_values['field_options']['classes'] = 'arf_2';
        $field_values['field_options']['blank'] = 'Please Enter Desired Salary';
        $arffield->create($field_values);
        unset($field_values);

        $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('radio', $form_id));
        $field_values['name'] = addslashes(esc_html__('Fresher / Experienced', 'ARForms'));
        $field_values['required'] = 1;
        $field_values['options'] = maybe_serialize(array(addslashes(esc_html__('Fresher', 'ARForms')), addslashes(esc_html__('Experienced', 'ARForms'))));
        $field_values['field_options']['align'] = 'inline';
        $field_values['field_options']['classes'] = 'arf_2';
        $field_values['field_options']['blank'] = 'Please Select Fresher / Experienced';
        $frsh_exp_id = $arffield->create($field_values);
        unset($field_values);

        $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('text', $form_id));
        $field_values['name'] = addslashes(esc_html__('Experience', 'ARForms'));
        $field_values['description'] = addslashes(esc_html__('(e.g. 3 months, 2 years etc)', 'ARForms'));
        $field_values['required'] = 1;
        $conditional_rule = array(
            '1' => array(
                'id' => 1,
                'field_id' => $frsh_exp_id,
                'field_type' => 'radio',
                'operator' => 'equals',
                'value' => addslashes(esc_html__('Experienced', 'ARForms')),
            ),
        );
        $conditional_logic_exp = array(
            'enable' => 1,
            'display' => 'show',
            'if_cond' => 'all',
            'rules' => $conditional_rule,
        );
        $field_values['conditional_logic'] = maybe_serialize($conditional_logic_exp);
        $field_values['field_options']['classes'] = 'arf_2';
        $field_values['field_options']['blank'] = 'Please Enter Experience';
        $arffield->create($field_values);
        unset($field_values);

        $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('number', $form_id));
        $field_values['name'] = addslashes(esc_html__('Current Salary', 'ARForms'));
        $field_values['required'] = 1;
        $conditional_rule = array(
            '1' => array(
                'id' => 1,
                'field_id' => $frsh_exp_id,
                'field_type' => 'radio',
                'operator' => 'equals',
                'value' => addslashes(esc_html__('Experienced', 'ARForms'))
            ),
        );
        $conditional_logic_exp = array(
            'enable' => 1,
            'display' => 'show',
            'if_cond' => 'all',
            'rules' => $conditional_rule
        );
        $field_values['conditional_logic'] = maybe_serialize($conditional_logic_exp);
        $field_values['field_options']['classes'] = 'arf_2';
        $field_values['field_options']['blank'] = 'Please Enter Current Salary';
        $arffield->create($field_values);
        unset($field_values);

        $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('textarea', $form_id));
        $field_values['name'] = addslashes(esc_html__('Current Company Detail', 'ARForms'));
        $field_values['required'] = 1;
        $conditional_rule = array(
            '1' => array(
                'id' => 1,
                'field_id' => $frsh_exp_id,
                'field_type' => 'radio',
                'operator' => 'equals',
                'value' => addslashes(esc_html__('Experienced', 'ARForms'))
            ),
        );
        $conditional_logic_exp = array(
            'enable' => 1,
            'display' => 'show',
            'if_cond' => 'all',
            'rules' => $conditional_rule
        );
        $field_values['conditional_logic'] = maybe_serialize($conditional_logic_exp);
        $field_values['field_options']['classes'] = '';
        $field_values['field_options']['blank'] = 'Please Enter Current Company Detail';
        $arffield->create($field_values);
        unset($field_values);

        $field_values = apply_filters('arfbeforefieldcreated', $arfieldhelper->setup_new_variables('file', $form_id));
        $field_values['name'] = addslashes(esc_html__('Upload Resume', 'ARForms'));
        $field_values['required'] = 1;
        $field_values['field_options']['restrict'] = 1;
        $field_values['field_options']['upload_btn_color'] = '#a969e0';
        $field_values['field_options']['ftypes'] = array('doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'pdf' => 'application/pdf', 'txt|asc|c|cc|h' => 'text/plain', 'rtf' => 'application/rtf');
        $field_values['field_options']['classes'] = 'arf_2';
        $field_values['field_options']['blank'] = 'Please Select Resume';
        $arffield->create($field_values);
        unset($field_values);
        unset($values);
    }

    if (version_compare($newdbversion, '2.5.2', '<')) {

        global $wpdb, $db_record, $MdlDb;
        $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->forms . " WHERE status!=%s order by id desc",'draft'), OBJECT_K);

        foreach ($res as $key => $val) {
            $form_css_res = $wpdb->get_results($wpdb->prepare("SELECT id, form_css, options FROM " . $MdlDb->forms . " WHERE id = %d", $val->id), ARRAY_A);
            $form_id = $val->id;
            $cssoptions = maybe_unserialize($form_css_res[0]['form_css']);

            if (count($cssoptions) > 0) {
                $new_values = array();

                foreach ($cssoptions as $k => $v)
                    $new_values[$k] = str_replace("#", '', $v);

                $saving = true;
                $use_saved = true;
                $arfssl = (is_ssl()) ? 1 : 0;
                $filename = FORMPATH . '/core/css_create_main.php';
                $temp_css_file = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
                $temp_css_file .= "\n";
                ob_start();
                include $filename;
                $temp_css_file .= ob_get_contents();
                ob_end_clean();
                $temp_css_file .= "\n " . $warn;
                $wp_upload_dir = wp_upload_dir();
                $dest_dir = $wp_upload_dir['basedir'] . '/arforms/maincss/';
                $css_file_new = $dest_dir . 'maincss_' . $form_id . '.css';

                WP_Filesystem();
                global $wp_filesystem;
                $wp_filesystem->put_contents($css_file_new, $temp_css_file, 0777);
            }
        }
    }


    if (version_compare($newdbversion, '2.5.3', '<')) {

        global $wpdb, $db_record, $MdlDb;
        $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->forms . " WHERE status!=%s order by id desc",'draft'), OBJECT_K);

        foreach ($res as $key => $val) {
            $form_css_res = $wpdb->get_results($wpdb->prepare("SELECT id, form_css, options FROM " . $MdlDb->forms . " WHERE id = %d", $val->id), ARRAY_A);
            $form_id = $val->id;
            $cssoptions = maybe_unserialize($form_css_res[0]['form_css']);

            if (count($cssoptions) > 0) {
                $new_values = array();

                foreach ($cssoptions as $k => $v)
                    $new_values[$k] = str_replace("#", '', $v);

                $saving = true;
                $use_saved = true;
                $arfssl = (is_ssl()) ? 1 : 0;
                $filename = FORMPATH . '/core/css_create_main.php';
                $temp_css_file = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
                $temp_css_file .= "\n";
                ob_start();
                include $filename;
                $temp_css_file .= ob_get_contents();
                ob_end_clean();
                $temp_css_file .= "\n " . $warn;
                $wp_upload_dir = wp_upload_dir();
                $dest_dir = $wp_upload_dir['basedir'] . '/arforms/maincss/';
                $css_file_new = $dest_dir . 'maincss_' . $form_id . '.css';

                WP_Filesystem();
                global $wp_filesystem;
                $wp_filesystem->put_contents($css_file_new, $temp_css_file, 0777);
            }
        }
    }

    if (version_compare($newdbversion, '2.5.4', '<')) {

        require_once(MODELS_PATH . '/arstylemodel.php');

        $updatestylesettings = new arstylemodel();

        update_option('arfa_options', $updatestylesettings);
        set_transient('arfa_options', $updatestylesettings);

        $updatestylesettings->set_default_options();
        $updatestylesettings->store();

        $cssoptions = get_option("arfa_options");
        $new_values = array();

        foreach ($cssoptions as $k => $v)
            $new_values[$k] = $v;
        $arfssl = (is_ssl()) ? 1 : 0;
        $filename = FORMPATH . '/core/css_create_main.php';

        if (is_file($filename)) {
            $uploads = wp_upload_dir();
            $target_path = $uploads['basedir'];
            $target_path .= "/arforms";
            $target_path .= "/css";
            $use_saved = true;
            $form_id = '';
            $css = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
            $css .= "\n";
            ob_start();
            include $filename;
            $css .= ob_get_contents();
            ob_end_clean();
            $css .= "\n " . $warn;
            $css_file = $target_path . '/arforms.css';

            WP_Filesystem();
            global $wp_filesystem;
            $wp_filesystem->put_contents($css_file, $css, 0777);

            update_option('arfa_css', $css);
            delete_transient('arfa_css');
            set_transient('arfa_css', $css);
        }


        global $wpdb, $db_record, $MdlDb;
        $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->forms . " WHERE status!=%s order by id desc",'draft'), OBJECT_K);

        foreach ($res as $key => $val) {
            $form_css_res = $wpdb->get_results($wpdb->prepare("SELECT id, form_css, options FROM " . $MdlDb->forms . " WHERE id = %d", $val->id), ARRAY_A);
            $form_id = $val->id;
            $cssoptions = maybe_unserialize($form_css_res[0]['form_css']);

            if (count($cssoptions) > 0) {
                $new_values = array();

                foreach ($cssoptions as $k => $v)
                    $new_values[$k] = str_replace("#", '', $v);

                $saving = true;
                $use_saved = true;
                $arfssl = (is_ssl()) ? 1 : 0;
                $filename = FORMPATH . '/core/css_create_main.php';
                $temp_css_file = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
                $temp_css_file .= "\n";
                ob_start();
                include $filename;
                $temp_css_file .= ob_get_contents();
                ob_end_clean();
                $temp_css_file .= "\n " . $warn;
                $wp_upload_dir = wp_upload_dir();
                $dest_dir = $wp_upload_dir['basedir'] . '/arforms/maincss/';
                $css_file_new = $dest_dir . 'maincss_' . $form_id . '.css';

                WP_Filesystem();
                global $wp_filesystem;
                $wp_filesystem->put_contents($css_file_new, $temp_css_file, 0777);
            }
        }
    }

    if (version_compare($newdbversion, '2.6', '<')) {


        require_once(MODELS_PATH . '/arstylemodel.php');
        $updatestylesettings = new arstylemodel();

        update_option('arfa_options', $updatestylesettings);
        set_transient('arfa_options', $updatestylesettings);

        $updatestylesettings->set_default_options();
        $updatestylesettings->store();

        $cssoptions = get_option("arfa_options");
        $new_values = array();

        foreach ($cssoptions as $k => $v)
            $new_values[$k] = $v;

        $arfssl = (is_ssl()) ? 1 : 0;

        $filename = FORMPATH . '/core/css_create_main.php';

        if (is_file($filename)) {
            $uploads = wp_upload_dir();
            $target_path = $uploads['basedir'];
            $target_path .= "/arforms";
            $target_path .= "/css";
            $use_saved = true;
            $form_id = '';
            $css = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
            $css .= "\n";
            ob_start();
            include $filename;
            $css .= ob_get_contents();
            ob_end_clean();
            $css .= "\n " . $warn;
            $css_file = $target_path . '/arforms.css';

            WP_Filesystem();
            global $wp_filesystem;
            $wp_filesystem->put_contents($css_file, $css, 0777);

            update_option('arfa_css', $css);
            delete_transient('arfa_css');
            set_transient('arfa_css', $css);
        }


        global $wpdb, $db_record, $MdlDb;
        $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->forms . " WHERE status!=%s order by id desc",'draft'), OBJECT_K);

        foreach ($res as $key => $val) {
            $form_css_res = $wpdb->get_results($wpdb->prepare("SELECT id, form_css, options FROM " . $MdlDb->forms . " WHERE id = %d", $val->id), ARRAY_A);
            $form_id = $val->id;
            $cssoptions = maybe_unserialize($form_css_res[0]['form_css']);

            $cssoptions['bar_color_survey'] = arf_sanitize_value('#007ee4');
            $cssoptions['bg_color_survey'] = arf_sanitize_value('#dadde2');
            $cssoptions['text_color_survey'] = arf_sanitize_value('#333333');

            $sernewarr = maybe_serialize($cssoptions);

            $res = $wpdb->update($MdlDb->forms, array('form_css' => $sernewarr), array('id' => $val->id));

            if (count($cssoptions) > 0) {
                $new_values = array();

                foreach ($cssoptions as $k => $v)
                    $new_values[$k] = str_replace("#", '', $v);

                $saving = true;
                $use_saved = true;
                $arfssl = (is_ssl()) ? 1 : 0;
                $filename = FORMPATH . '/core/css_create_main.php';
                $temp_css_file = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
                $temp_css_file .= "\n";
                ob_start();
                include $filename;
                $temp_css_file .= ob_get_contents();
                ob_end_clean();
                $temp_css_file .= "\n " . $warn;
                $wp_upload_dir = wp_upload_dir();
                $dest_dir = $wp_upload_dir['basedir'] . '/arforms/maincss/';
                $css_file_new = $dest_dir . 'maincss_' . $form_id . '.css';

                WP_Filesystem();
                global $wp_filesystem;
                $wp_filesystem->put_contents($css_file_new, $temp_css_file, 0777);
            }


            global $arffield, $MdlDb;
            $form_fields = $arffield->getAll("fi.form_id = " . $form_id, " ORDER BY id");
            foreach ($form_fields as $key => $val) {
                $val->field_options['image_url'] = ARFURL . '/images/no-image.png';
                $val->field_options['image_left'] = arf_sanitize_value('0px');
                $val->field_options['image_top'] = arf_sanitize_value('0px');
                $val->field_options['image_height'] = '';
                $val->field_options['image_width'] = '';
                $val->field_options['image_center'] = arf_sanitize_value('no');
                $val->field_options['enable_total'] = arf_sanitize_value('0');
                $val->field_options['colorpicker_type'] = arf_sanitize_value('advanced');
                $val->field_options['show_year_month_calendar'] = arf_sanitize_value('0');

                $optionsnewval = maybe_serialize($val->field_options);
                $res = $wpdb->update($MdlDb->fields, array('field_options' => $optionsnewval), array('id' => $val->id));
            }
        }
    }

    if (version_compare($newdbversion, '2.6.2', '<')) {

        global $wpdb, $db_record, $MdlDb;
        $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->forms . " WHERE status!='%s order by id desc",'draft'), OBJECT_K);

        foreach ($res as $key => $val) {
            $form_id = $val->id;


            global $arffield;
            $form_fields = $arffield->getAll("fi.form_id = " . $form_id, " ORDER BY id");
            foreach ($form_fields as $key => $val) {
                $val->field_options['password_placeholder'] = '';

                $optionsnewval = maybe_serialize($val->field_options);
                $res = $wpdb->update($MdlDb->fields, array('field_options' => $optionsnewval), array('id' => $val->id));
            }
        }
    }

    if (version_compare($newdbversion, '2.7', '<')) {



        require_once(MODELS_PATH . '/arstylemodel.php');
        $updatestylesettings = new arstylemodel();

        update_option('arfa_options', $updatestylesettings);
        set_transient('arfa_options', $updatestylesettings);

        $updatestylesettings->set_default_options();
        $updatestylesettings->store();



        $cssoptions = get_option("arfa_options");
        $new_values = array();

        foreach ($cssoptions as $k => $v)
            $new_values[$k] = $v;
        $arfssl = (is_ssl()) ? 1 : 0;
        $filename = FORMPATH . '/core/css_create_main.php';

        if (is_file($filename)) {
            $uploads = wp_upload_dir();
            $target_path = $uploads['basedir'];
            $target_path .= "/arforms";
            $target_path .= "/css";
            $use_saved = true;
            $form_id = '';
            $css = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
            $css .= "\n";
            ob_start();
            include $filename;
            $css .= ob_get_contents();
            ob_end_clean();
            $css .= "\n " . $warn;
            $css_file = $target_path . '/arforms.css';

            WP_Filesystem();
            global $wp_filesystem;
            $wp_filesystem->put_contents($css_file, $css, 0777);

            update_option('arfa_css', $css);
            delete_transient('arfa_css');
            set_transient('arfa_css', $css);
        }



        global $wpdb, $db_record, $MdlDb;
        $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->forms . " WHERE status!=%s order by id desc",'draft'), OBJECT_K);

        foreach ($res as $key => $val) {
            $form_css_res = $wpdb->get_results($wpdb->prepare("SELECT id, form_css, options FROM " . $MdlDb->forms . " WHERE id = %d", $val->id), ARRAY_A);
            $form_id = $val->id;
            $cssoptions = maybe_unserialize($form_css_res[0]['form_css']);

            $cssoptions['prefix_suffix_bg_color'] = arf_sanitize_value('#e7e8ec');
            $cssoptions['prefix_suffix_icon_color'] = arf_sanitize_value('#808080');
            $cssoptions['submit_hover_bg_img'] = '';

            $cssoptions['arfsectionpaddingsetting_1'] = arf_sanitize_value('15');
            $cssoptions['arfsectionpaddingsetting_2'] = arf_sanitize_value('10');
            $cssoptions['arfsectionpaddingsetting_3'] = arf_sanitize_value('15');
            $cssoptions['arfsectionpaddingsetting_4'] = arf_sanitize_value('10');

            $sernewarr = maybe_serialize($cssoptions);

            $res = $wpdb->update($MdlDb->forms, array('form_css' => $sernewarr), array('id' => $val->id));



            $formoptions = maybe_unserialize($form_css_res[0]['options']);
            $formoptions['ar_admin_email_message'] = '[ARF_form_all_values]';

            $sernewoptarr = maybe_serialize($formoptions);

            $res = $wpdb->update($MdlDb->forms, array('options' => $sernewoptarr), array('id' => $val->id));

            if (count($cssoptions) > 0) {
                $new_values = array();

                foreach ($cssoptions as $k => $v)
                    $new_values[$k] = str_replace("#", '', $v);

                $saving = true;
                $use_saved = true;
                $arfssl = (is_ssl()) ? 1 : 0;
                $filename = FORMPATH . '/core/css_create_main.php';
                $temp_css_file = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
                $temp_css_file .= "\n";
                ob_start();
                include $filename;
                $temp_css_file .= ob_get_contents();
                ob_end_clean();
                $temp_css_file .= "\n " . $warn;
                $wp_upload_dir = wp_upload_dir();
                $dest_dir = $wp_upload_dir['basedir'] . '/arforms/maincss/';
                $css_file_new = $dest_dir . 'maincss_' . $form_id . '.css';

                WP_Filesystem();
                global $wp_filesystem;
                $wp_filesystem->put_contents($css_file_new, $temp_css_file, 0777);
            }
        }
    }

    if (version_compare($newdbversion, '2.7.4', '<')) {


        require_once(MODELS_PATH . '/arstylemodel.php');
        $updatestylesettings = new arstylemodel();

        update_option('arfa_options', $updatestylesettings);
        set_transient('arfa_options', $updatestylesettings);

        $updatestylesettings->set_default_options();
        $updatestylesettings->store();



        $cssoptions = get_option("arfa_options");
        $new_values = array();

        foreach ($cssoptions as $k => $v)
            $new_values[$k] = $v;
        $arfssl = (is_ssl()) ? 1 : 0;
        $filename = FORMPATH . '/core/css_create_main.php';

        if (is_file($filename)) {
            $uploads = wp_upload_dir();
            $target_path = $uploads['basedir'];
            $target_path .= "/arforms";
            $target_path .= "/css";
            $use_saved = true;
            $form_id = '';
            $css = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
            $css .= "\n";
            ob_start();
            include $filename;
            $css .= ob_get_contents();
            ob_end_clean();
            $css .= "\n " . $warn;
            $css_file = $target_path . '/arforms.css';

            WP_Filesystem();
            global $wp_filesystem;
            $wp_filesystem->put_contents($css_file, $css, 0777);

            update_option('arfa_css', $css);
            delete_transient('arfa_css');
            set_transient('arfa_css', $css);
        }



        global $wpdb, $db_record, $MdlDb;
        $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->forms . " WHERE status!=%s order by id desc",'draft'), OBJECT_K);

        foreach ($res as $key => $val) {
            $form_css_res = $wpdb->get_results($wpdb->prepare("SELECT id, form_css, options FROM " . $MdlDb->forms . " WHERE id = %d", $val->id), ARRAY_A);
            $form_id = $val->id;
            $cssoptions = maybe_unserialize($form_css_res[0]['form_css']);

            $cssoptions['arf_checked_checkbox_icon'] = '';
            $cssoptions['enable_arf_checkbox'] = arf_sanitize_value('0');
            $cssoptions['arf_checked_radio_icon'] = '';
            $cssoptions['enable_arf_radio'] = '0';
            $cssoptions['checked_checkbox_icon_color'] = arf_sanitize_value('#666666');
            $cssoptions['checked_radio_icon_color'] = arf_sanitize_value('#666666');

            $cssoptions['date_format'] = arf_sanitize_value('MMM D, YYYY');
            $cssoptions['cal_date_format'] = arf_sanitize_value('MMM D, YYYY');

            $cssoptions['arfcalthemename'] = arf_sanitize_value('default_theme');
            $cssoptions['arfcalthemecss'] = arf_sanitize_value('default_theme');
            $cssoptions['theme_nicename'] = arf_sanitize_value('default_theme');


            $sernewarr = maybe_serialize($cssoptions);

            $res = $wpdb->update($MdlDb->forms, array('form_css' => $sernewarr), array('id' => $val->id));



            if (count($cssoptions) > 0) {
                $new_values = array();

                foreach ($cssoptions as $k => $v)
                    $new_values[$k] = str_replace("#", '', $v);

                $saving = true;
                $use_saved = true;
                $arfssl = (is_ssl()) ? 1 : 0;
                $filename = FORMPATH . '/core/css_create_main.php';
                $temp_css_file = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
                $temp_css_file .= "\n";
                ob_start();
                include $filename;
                $temp_css_file .= ob_get_contents();
                ob_end_clean();
                $temp_css_file .= "\n " . $warn;
                $wp_upload_dir = wp_upload_dir();
                $dest_dir = $wp_upload_dir['basedir'] . '/arforms/maincss/';
                $css_file_new = $dest_dir . 'maincss_' . $form_id . '.css';

                WP_Filesystem();
                global $wp_filesystem;
                $wp_filesystem->put_contents($css_file_new, $temp_css_file, 0777);
            }



            global $arffield;
            $form_fields = $arffield->getAll("fi.form_id = " . $form_id, " ORDER BY id");
            foreach ($form_fields as $key => $val) {
                if ($val->type == "slider") {
                    $val->type = "arfslider";
                }
                $field_options = maybe_unserialize($val->field_options);


                if ($field_options['arf_prefix_icon'] != "") {
                    $field_options['arf_prefix_icon'] = "ar" . $field_options['arf_prefix_icon'];
                }
                if ($field_options['arf_suffix_icon'] != "") {
                    $field_options['arf_suffix_icon'] = "ar" . $field_options['arf_suffix_icon'];
                }

                $fieldtype = $val->type;
                $optionsnewval = maybe_serialize($field_options);
                $res = $wpdb->update($MdlDb->fields, array('type' => $fieldtype, 'field_options' => $optionsnewval), array('id' => $val->id));
            }
        }
    }
    

    if (version_compare($newdbversion, '3.0', '<')) {
        require FORMPATH . '/core/views/upgrade_latest_data_v3.0.php';
    }

    if( version_compare($newdbversion, '3.1', '<')){

        $arf_forms = $wpdb->get_results( $wpdb->prepare( "SELECT form_css FROM `".$MdlDb->forms."` WHERE status = %s", 'published' ) );

        foreach($arf_forms as $form ){

            $new_form_css = maybe_unserialize($form->form_css);

            if( count($new_form_css) > 0 ){
                $new_values = array();
                foreach ($new_form_css as $k => $v) {
                    $new_values[$k] = $v;
                    if( preg_match("/auto/",$new_values[$k]) ){
                        $new_values[$k] = str_replace("px","",$new_values[$k]);
                    }
                }

                update_option('arf_form_css_'.$form_id,json_encode( $new_values) );

                $saving = true;
                $use_saved = true;
                $arfssl = (is_ssl()) ? 1 : 0;

                $filename = FORMPATH . '/core/css_create_main.php';

                $temp_css_file = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
                $temp_css_file .= "\n";
                ob_start();
                include $filename;
                $temp_css_file .= ob_get_contents();
                ob_end_clean();
                $temp_css_file .= "\n " . $warn;
                $wp_upload_dir = wp_upload_dir();
                $dest_dir = $wp_upload_dir['basedir'] . '/arforms/maincss/';
                $css_file_new = $dest_dir . 'maincss_' . $form_id. '.css';

                WP_Filesystem();
                global $wp_filesystem;
                $wp_filesystem->put_contents($css_file_new, $temp_css_file, 0777);

                $filename1 = FORMPATH . '/core/css_create_materialize.php';
                $temp_css_file1 = $warn1 = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
                $temp_css_file1 .= "\n";
                ob_start();
                include $filename1;
                $temp_css_file1 .= ob_get_contents();
                ob_end_clean();
                $temp_css_file1 .= "\n " . $warn1;
                $wp_upload_dir = wp_upload_dir();
                $dest_dir = $wp_upload_dir['basedir'] . '/arforms/maincss/';
                $css_file_new1 = $dest_dir . 'maincss_materialize_' . $form_id. '.css';

                WP_Filesystem();
                global $wp_filesystem;
                $wp_filesystem->put_contents($css_file_new1, $temp_css_file1, 0777);
            }
        }

        $arf_update_templates = true;

        $wpdb->query( $wpdb->prepare("DELETE FROM `".$MdlDb->forms."` WHERE id < %d",12) );

        $wpdb->query( $wpdb->prepare("DELETE FROM `".$MdlDb->fields."` WHERE form_id < %d", 12));

        $arfsettings = get_transient('arf_options');

        if (!is_object($arfsettings)) {
            if ($arfsettings) {
                $arfsettings = maybe_unserialize(maybe_serialize($arfsettings));
            } else {
                $arfsettings = get_option('arf_options');

                if (!is_object($arfsettings)) {
                    if ($arfsettings){
                        $arfsettings = maybe_unserialize(maybe_serialize($arfsettings));
                    } else {
                        $arfsettings = new arsettingmodel();
                    }
                    update_option('arf_options', $arfsettings);
                    set_transient('arf_options', $arfsettings);
                }
            }
        }

        $arfsettings->set_default_options();

        $style_settings = get_transient('arfa_options');
        if (!is_object($style_settings)) {
            if ($style_settings) {
                $style_settings = maybe_unserialize(maybe_serialize($style_settings));
            } else {
                $style_settings = get_option('arfa_options');
                if (!is_object($style_settings)) {
                    if ($style_settings)
                        $style_settings = maybe_unserialize(maybe_serialize($style_settings));
                    else
                        $style_settings = new arstylemodel();
                    update_option('arfa_options', $style_settings);
                    set_transient('arfa_options', $style_settings);
                }
            }
        }
        $style_settings = get_option('arfa_options');
        if (!is_object($style_settings)) {
            if ($style_settings)
                $style_settings = maybe_unserialize(maybe_serialize($style_settings));
            else
                $style_settings = new arstylemodel();
            update_option('arfa_options', $style_settings);
        }

        $style_settings->set_default_options();
        $style_settings->store();

        include(MODELS_PATH."/artemplate.php");

    }

    if( version_compare($newdbversion, '3.2', '<') ){

        $all_forms = $wpdb->get_results($wpdb->prepare("SELECT id,options FROM `".$MdlDb->forms."` WHERE is_template = %d AND status = %s ",0,'published'));

        foreach($all_forms as $key => $form ){
            
            $form_opts = maybe_unserialize($form->options);

            $new_conditional_rule = $form_opts['arf_conditional_logic_rules'];
            $new_conditional_mail = $form_opts['arf_conditional_mail_rules'];
            $new_conditional_redirect = $form_opts['arf_conditional_redirect_rules'];
            $new_conditional_subscription = $form_opts['arf_condition_on_subscription_rules'];
            $new_submit_cl = $form_opts['submit_conditional_logic'];

            $conditional_rules = $form_opts['arf_conditional_logic_rules'];
            if(isset($conditional_rules) && is_array($conditional_rules)){
                foreach( $conditional_rules as $k => $cs ){
                    $cl_conditions = $cs['condition'];
                    $cl_results = $cs['result'];

                    foreach($cl_conditions as $ck => $clc ){
                        $clc_field_id = $clc['field_id'];
                        $types = $wpdb->get_row($wpdb->prepare("SELECT type FROM `".$MdlDb->fields."` WHERE id = %d",$clc_field_id) );
                        if( isset($types) && $clc['field_type'] != $types->type ){
                            $new_conditional_rule[$k]['condition'][$ck]['field_type'] = $types->type;
                        }
                    }

                    foreach($cl_results as $cr => $clr ){
                        $clr_field_id = $clr['field_id'];
                        $types = $wpdb->get_row($wpdb->prepare("SELECT type FROM `".$MdlDb->fields."` WHERE id = %d",$clr_field_id) );
                        if( isset($types) && $clc['field_type'] != $types->type ){
                            $new_conditional_rule[$k]['result'][$cr]['field_type'] = $types->type;
                        }
                    }
                }
            }

            $conditional_mail = $form_opts['arf_conditional_mail_rules'];
            if(isset($conditional_rules) && is_array($conditional_rules)){
                foreach($conditional_mail as $i => $ce){
                    $cle_field_type = $ce['field_type_mail'];
                    $cle_field_id = $ce['field_id_mail'];
                    $etypes = $wpdb->get_row($wpdb->prepare("SELECT type FROM `".$MdlDb->fields."` WHERE id = %d",$cle_field_id));

                    if( isset($etypes) && $cle_field_type != $etypes->type ){
                        $new_conditional_mail[$i]['field_type_mail'] = $etypes->type;
                    }
                }
            }

            $conditional_redirect = $form_opts['arf_conditional_redirect_rules'];
            if( isset($conditional_redirect) && is_array($conditional_redirect) ){
                foreach($conditional_redirect as $i => $cr){
                    $clr_field_type = $cr['field_type'];
                    $clr_field_id = $cr['field_id'];
                    $etypes = $wpdb->get_row($wpdb->prepare("SELECT type FROM `".$MdlDb->fields."` WHERE id = %d",$clr_field_id));

                    if( isset($etypes) && $clr_field_type != $etypes->type ){
                        $new_conditional_redirect[$i]['field_type_mail'] = $etypes->type;
                    }
                }
            }


            $conditional_subscription = $form_opts['arf_condition_on_subscription_rules'];
            if( isset($conditional_subscription) && is_array($conditional_subscription) ){
                foreach($conditional_subscription as $i => $csub){
                    $cls_field_type = $csub['field_type'];
                    $cls_field_id = $csub['field_id'];
                    $etypes = $wpdb->get_row($wpdb->prepare("SELECT type FROM `".$MdlDb->fields."` WHERE id = %d",$cls_field_id));

                    if( isset($etypes) && $cls_field_type != $etypes->type ){
                        $new_conditional_subscription[$i]['field_type'] = $etypes->type;
                    }
                }
            }

            $submit_cl_logic = $form_opts['submit_conditional_logic'];
            if( isset($submit_cl_logic) && is_array($submit_cl_logic) ){
                foreach($submit_cl_logic as $s => $submit_cl){
                    if( isset($submit_cl['enable']) && $submit_cl['enable'] == 1 ){
                        $cls_results = $submit_cl['rules'];
                        foreach($cls_results as $r => $rule){
                            $clsub_field_type = $rule['field_type'];
                            $clsub_field_id = $rule['field_id'];
                            $sub_type = $wpdb->get_row($wpdb->prepare("SELECT type FROM `".$MdlDb->fields."` WHERE id = %d",$clsub_field_id));

                            if( isset($sub_type) && $clsub_field_type != $sub_type->type ){
                                $new_submit_cl[$s]['rules'][$r]['field_type'] = $sub_type->type;
                            }
                        }
                    }
                }
            }


            $form_opts['arf_conditional_logic_rules'] = $new_conditional_rule;
            $form_opts['arf_conditional_mail_rules'] = arf_sanitize_value($new_conditional_mail, 'email');
            $form_opts['arf_conditional_redirect_rules'] = arf_sanitize_value($new_conditional_redirect, 'integer');
            $form_opts['arf_condition_on_subscription_rules'] = arf_sanitize_value($new_conditional_subscription, 'integer');

            $wpdb->update($MdlDb->forms, array('options'=>maybe_serialize($form_opts)), array('id'=>$form->id) );

        }

    }

    if( version_compare($newdbversion, '3.3', '<') ){

        $arf_forms = $wpdb->get_results( $wpdb->prepare( "SELECT id,form_css FROM `".$MdlDb->forms."` WHERE is_template = %d AND status = %s ", 0, 'published' ) );

        foreach($arf_forms as $form ){

            $new_form_css = maybe_unserialize($form->form_css);
            $form_id = $form->id;

            if( count($new_form_css) > 0 ){
                $new_values = array();
                foreach ($new_form_css as $k => $v) {
                    $new_values[$k] = $v;
                    if( preg_match("/auto/",$new_values[$k]) ){
                        $new_values[$k] = str_replace("px","",$new_values[$k]);
                    }
                }

                update_option('arf_form_css_'.$form_id,json_encode( $new_values) );

                $saving = true;
                $use_saved = true;
                $arfssl = (is_ssl()) ? 1 : 0;

                $filename = FORMPATH . '/core/css_create_main.php';

                $temp_css_file = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
                $temp_css_file .= "\n";
                ob_start();
                include $filename;
                $temp_css_file .= ob_get_contents();
                ob_end_clean();
                $temp_css_file .= "\n " . $warn;
                $wp_upload_dir = wp_upload_dir();
                $dest_dir = $wp_upload_dir['basedir'] . '/arforms/maincss/';
                $css_file_new = $dest_dir . 'maincss_' . $form_id. '.css';

                WP_Filesystem();
                global $wp_filesystem;
                $wp_filesystem->put_contents($css_file_new, $temp_css_file, 0777);

                $filename1 = FORMPATH . '/core/css_create_materialize.php';
                $temp_css_file1 = $warn1 = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
                $temp_css_file1 .= "\n";
                ob_start();
                include $filename1;
                $temp_css_file1 .= ob_get_contents();
                ob_end_clean();
                $temp_css_file1 .= "\n " . $warn1;
                $wp_upload_dir = wp_upload_dir();
                $dest_dir = $wp_upload_dir['basedir'] . '/arforms/maincss/';
                $css_file_new1 = $dest_dir . 'maincss_materialize_' . $form_id. '.css';

                WP_Filesystem();
                global $wp_filesystem;
                $wp_filesystem->put_contents($css_file_new1, $temp_css_file1, 0777);
            }
        }

        $all_forms = $wpdb->get_results($wpdb->prepare("SELECT id,options FROM `".$MdlDb->forms."` WHERE is_template = %d AND status = %s ",0,'published'));

        foreach($all_forms as $key => $form ){
            
            $form_opts = maybe_unserialize($form->options);

            $new_conditional_rule = $form_opts['arf_conditional_logic_rules'];
            $new_conditional_mail = $form_opts['arf_conditional_mail_rules'];
            $new_conditional_redirect = $form_opts['arf_conditional_redirect_rules'];
            $new_conditional_subscription = $form_opts['arf_condition_on_subscription_rules'];
            $new_submit_cl = $form_opts['submit_conditional_logic'];

            $conditional_rules = $form_opts['arf_conditional_logic_rules'];
            if(isset($conditional_rules) && is_array($conditional_rules)){
                foreach( $conditional_rules as $k => $cs ){
                    $cl_conditions = $cs['condition'];
                    $cl_results = $cs['result'];

                    foreach($cl_conditions as $ck => $clc ){
                        $clc_field_id = $clc['field_id'];
                        $types = $wpdb->get_row($wpdb->prepare("SELECT type FROM `".$MdlDb->fields."` WHERE id = %d",$clc_field_id) );
                        if( isset($types) && $clc['field_type'] != $types->type ){
                            $new_conditional_rule[$k]['condition'][$ck]['field_type'] = $types->type;
                        }
                    }

                    foreach($cl_results as $cr => $clr ){
                        $clr_field_id = $clr['field_id'];
                        $types = $wpdb->get_row($wpdb->prepare("SELECT type FROM `".$MdlDb->fields."` WHERE id = %d",$clr_field_id) );
                        if( isset($types) && $clr['field_type'] != $types->type ){
                            $new_conditional_rule[$k]['result'][$cr]['field_type'] = $types->type;
                        }
                    }
                }
            }

            $conditional_mail = $form_opts['arf_conditional_mail_rules'];
            if(isset($conditional_rules) && is_array($conditional_rules)){
                foreach($conditional_mail as $i => $ce){
                    $cle_field_type = $ce['field_type_mail'];
                    $cle_field_id = $ce['field_id_mail'];
                    $etypes = $wpdb->get_row($wpdb->prepare("SELECT type FROM `".$MdlDb->fields."` WHERE id = %d",$cle_field_id));

                    if( isset($etypes) && $cle_field_type != $etypes->type ){
                        $new_conditional_mail[$i]['field_type_mail'] = $etypes->type;
                    }
                }
            }

            $conditional_redirect = $form_opts['arf_conditional_redirect_rules'];
            if( isset($conditional_redirect) && is_array($conditional_redirect) ){
                foreach($conditional_redirect as $i => $cr){
                    $clr_field_type = $cr['field_type'];
                    $clr_field_id = $cr['field_id'];
                    $etypes = $wpdb->get_row($wpdb->prepare("SELECT type FROM `".$MdlDb->fields."` WHERE id = %d",$clr_field_id));

                    if( isset($etypes) && $clr_field_type != $etypes->type ){
                        $new_conditional_redirect[$i]['field_type_mail'] = $etypes->type;
                    }
                }
            }

            $conditional_subscription = $form_opts['arf_condition_on_subscription_rules'];
            if( isset($conditional_subscription) && is_array($conditional_subscription) ){
                foreach($conditional_subscription as $i => $csub){
                    $cls_field_type = $csub['field_type'];
                    $cls_field_id = $csub['field_id'];
                    $etypes = $wpdb->get_row($wpdb->prepare("SELECT type FROM `".$MdlDb->fields."` WHERE id = %d",$cls_field_id));

                    if( isset($etypes) && $cls_field_type != $etypes->type ){
                        $new_conditional_subscription[$i]['field_type'] = $etypes->type;
                    }
                }
            }

            $submit_cl_logic = $form_opts['submit_conditional_logic'];
            if( isset($submit_cl_logic) && is_array($submit_cl_logic) ){

                $operator = $submit_cl_logic['if_cond'];

                $operator_arr = array('all','كل','All','alle','todo','tous','すべて','모든','Все','Tüm','所有');

                if( in_array($operator,$operator_arr) ){
                    $operator = 'all';
                } else {
                    $operator = 'any';
                }

                $new_submit_cl['if_cond'] = $operator;

                foreach($submit_cl_logic as $s => $submit_cl){
                    if( isset($submit_cl['enable']) && $submit_cl['enable'] == 1 ){
                        $cls_results = $submit_cl['rules'];
                        foreach($cls_results as $r => $rule){
                            $clsub_field_type = $rule['field_type'];
                            $clsub_field_id = $rule['field_id'];
                            $sub_type = $wpdb->get_row($wpdb->prepare("SELECT type FROM `".$MdlDb->fields."` WHERE id = %d",$clsub_field_id));

                            if( isset($sub_type) && $clsub_field_type != $sub_type->type ){
                                $new_submit_cl[$s]['rules'][$r]['field_type'] = $sub_type->type;
                            }
                        }
                    }
                }
            }

            $form_opts['arf_conditional_logic_rules'] = $new_conditional_rule;
            $form_opts['arf_conditional_mail_rules'] = arf_sanitize_value($new_conditional_mail, 'email');
            $form_opts['arf_conditional_redirect_rules'] = $new_conditional_redirect;
            $form_opts['arf_condition_on_subscription_rules'] = arf_sanitize_value($new_conditional_subscription, 'integer');
            $form_opts['submit_conditional_logic'] = $new_submit_cl;

            $wpdb->update($MdlDb->forms, array('options'=>maybe_serialize($form_opts)), array('id'=>$form->id) );

        }      

        $arf_update_templates = true;

        $wpdb->query( $wpdb->prepare("DELETE FROM `".$MdlDb->forms."` WHERE id < %d",12) );

        $wpdb->query( $wpdb->prepare("DELETE FROM `".$MdlDb->fields."` WHERE form_id < %d", 12));

        $arfsettings = get_transient('arf_options');

        if (!is_object($arfsettings)) {
            if ($arfsettings) {
                $arfsettings = maybe_unserialize(maybe_serialize($arfsettings));
            } else {
                $arfsettings = get_option('arf_options');

                if (!is_object($arfsettings)) {
                    if ($arfsettings){
                        $arfsettings = maybe_unserialize(maybe_serialize($arfsettings));
                    } else {
                        $arfsettings = new arsettingmodel();
                    }
                    update_option('arf_options', $arfsettings);
                    set_transient('arf_options', $arfsettings);
                }
            }
        }

        $arfsettings->set_default_options();
        
        global $style_settings;

        $style_settings = get_transient('arfa_options');
        if (!is_object($style_settings)) {
            if ($style_settings) {
                $style_settings = maybe_unserialize(maybe_serialize($style_settings));
            } else {
                $style_settings = get_option('arfa_options');
                if (!is_object($style_settings)) {
                    if ($style_settings)
                        $style_settings = maybe_unserialize(maybe_serialize($style_settings));
                    else
                        $style_settings = new arstylemodel();
                    update_option('arfa_options', $style_settings);
                    set_transient('arfa_options', $style_settings);
                }
            }
        }

        $style_settings = get_option('arfa_options');
        if (!is_object($style_settings)) {
            if ($style_settings)
                $style_settings = maybe_unserialize(maybe_serialize($style_settings));
            else
                $style_settings = new arstylemodel();
            update_option('arfa_options', $style_settings);
        }

        $style_settings->set_default_options();
        $style_settings->store();

        include(MODELS_PATH."/artemplate.php");

        $updateoptionsetting = get_option('arf_options');

        $updateoptionsetting->arf_file_uplod_dir_path = 'wp-content/uploads/arforms/userfiles';

        update_option('arf_options',$updateoptionsetting);

        set_transient('arf_options', $updateoptionsetting);

    }

    if( version_compare($newdbversion, '3.4', '<') ){
        $updateoptionsetting = get_option('arf_options');
        $updateoptionsetting->arfprivacyguidline = 1;
        $updateoptionsetting->arfprivacyguidlinetext = '<b> Who we are? </b>
            <p> ARForms is a WordPress Premium Form Builder Plugin to create stylish and modern style form withing few clicks.</p>
            <br/>
            <b> What Personal Data we collect and why we collect it </b>
            <p> ARForms stores ip address and country of visitor. However, ARForms provide an option to prevent storing visitor data. </p>
            <p> ARForms will not store any personal data except user_id (only if user is logged in), ip address, country, browser user_agent, referrer only when submit the form </p>
            <p> We store this data to provide the analytics of the visitor and the user who submit the form. </p>
            <p> ARForms will also store the all type of data (this may contain personal data as well as subscribe user to third party opt-in like MailChimp, Aweber, etc) in the database which plugin user has included in the form. These data are editable as well as removable from form entry section of ARForms </p>';

        update_option('arf_options',$updateoptionsetting);
        set_transient('arf_options',$updateoptionsetting);

        $wpdb->query("ALTER TABLE `" . $MdlDb->ar . "`  ADD `mailerlite` TEXT NOT NULL");

        $wpdb->query("INSERT INTO `" . $MdlDb->autoresponder. "` (responder_id) VALUES (".arf_sanitize_value(14, 'integer').")");

        $ar_types = maybe_unserialize(get_option('arf_ar_type'));
        $ar_types['mailerlite_type'] = 1;
        $ar_types = maybe_serialize($ar_types);
        update_option('arf_ar_type', $ar_types);

    }

    if( version_compare($newdbversion, '3.5', '<') ){

        $res = $wpdb->get_results($wpdb->prepare("SELECT `id`, `form_css` FROM " . $MdlDb->forms . " WHERE status = %s order by id desc",'published'), OBJECT_K);

        foreach ($res as $key => $val) {
            $arform_id = $val->id;
            $arform_css = maybe_unserialize($val->form_css);

            if( isset($arform_css['arf_checked_checkbox_icon']) && $arform_css['arf_checked_checkbox_icon'] != '' ){
                $arform_css['arf_checked_checkbox_icon'] = $armainhelper->arf_update_fa_font_class( $arform_css['arf_checked_checkbox_icon'] );
            }

            if( isset($arform_css['arf_checked_radio_icon']) && $arform_css['arf_checked_radio_icon'] != '' ){
                $arform_css['arf_checked_radio_icon'] = $armainhelper->arf_update_fa_font_class( $arform_css['arf_checked_radio_icon'] );
            }

            $wpdb->update($MdlDb->forms, array('form_css'=>maybe_serialize($arform_css)), array('id'=>$arform_id) );

            $arform_fields = $wpdb->get_results($wpdb->prepare("SELECT `id`, `type`, `field_options` FROM `".$MdlDb->fields."` WHERE form_id = %d",$arform_id), OBJECT_K);
            foreach ($arform_fields as $fk => $f_val) {
                if(isset($f_val->field_options)){
                    $field_options = json_decode($f_val->field_options,true);
                    if( json_last_error() != JSON_ERROR_NONE ){
                        $field_options = maybe_unserialize($f_val->field_options);
                    }
                    $field_id = $f_val->id;

                    if( isset($field_options['arf_prefix_icon']) && $field_options['arf_prefix_icon'] != '' ){
                        $field_options['arf_prefix_icon'] = $armainhelper->arf_update_fa_font_class( $field_options['arf_prefix_icon'] );
                    }

                    if( isset($field_options['arf_suffix_icon']) && $field_options['arf_suffix_icon'] != '' ){
                        $field_options['arf_suffix_icon'] = $armainhelper->arf_update_fa_font_class( $field_options['arf_suffix_icon'] );
                    }

                    $wpdb->update($MdlDb->fields, array('field_options'=>json_encode($field_options)), array('id'=>$field_id) );

                    if( $f_val->type == 'html' && $field_options['enable_total'] == 1  ){
                        $html_content = $field_options['description'];
                        $formula_pattern = "/\<arftotal\>(.*?)\<\/arftotal\>/is";
                        $new_description = $html_content;

                        if(preg_match($formula_pattern,$html_content,$matches)) {
                            $formula_content = $matches[0];
                            $ids_pattern = "/\[(.*?)\:(\d+)(|\.(\d+))\]/";
                            preg_match_all($ids_pattern,$formula_content,$match_ids);
                            if(isset($match_ids[2]) && is_array($match_ids[2]) && !empty($match_ids[2]) ){
                                foreach($match_ids[2] as $matched_id ){
                                    $n_field_id = arf_sanitize_value($matched_id, 'integer');
                                    $wpdb->update( $MdlDb->fields, array('enable_running_total' => $field_id), array('id' => $n_field_id) );
                                }
                            }

                        }
                    }
                }
            }

        }

    }

    if( version_compare($newdbversion, '3.5.2', '<') ){

        global $arsettingcontroller;

        $wp_upload_dir = wp_upload_dir();

        $directory = $wp_upload_dir['basedir'] . '/arforms/import_forms/';

        $arsettingcontroller->arf_remove_directory($directory);

    }

    if( version_compare($newdbversion, '3.6', '<') ){

        global $wpdb, $MdlDb;

        $arf_forms = $wpdb->get_results( $wpdb->prepare( "SELECT id,form_css FROM `".$MdlDb->forms."` WHERE is_template = %d AND status = %s ", 0, 'published' ) );

        foreach($arf_forms as $form ){

            $new_form_css = maybe_unserialize($form->form_css);
            $form_id = $form->id;

            if( count($new_form_css) > 0 ){
                $new_values = array();

                $new_form_css['arf_bg_position_x'] = "left";
                $new_form_css['arf_bg_position_input_x'] = "";
                $new_form_css['arf_bg_position_y'] = "top";
                $new_form_css['arf_bg_position_input_y'] = "";

                foreach ($new_form_css as $k => $v) {
                    $new_values[$k] = $v;
                }

                $saving = true;
                $use_saved = true;
                $arfssl = (is_ssl()) ? 1 : 0;

                $filename = FORMPATH . '/core/css_create_main.php';

                $temp_css_file = $warn = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
                $temp_css_file .= "\n";
                ob_start();
                include $filename;
                $temp_css_file .= ob_get_contents();
                ob_end_clean();
                $temp_css_file .= "\n " . $warn;
                $wp_upload_dir = wp_upload_dir();
                $dest_dir = $wp_upload_dir['basedir'] . '/arforms/maincss/';
                $css_file_new = $dest_dir . 'maincss_' . $form_id. '.css';

                WP_Filesystem();
                global $wp_filesystem;
                $wp_filesystem->put_contents($css_file_new, $temp_css_file, 0777);

                $filename1 = FORMPATH . '/core/css_create_materialize.php';
                $temp_css_file1 = $warn1 = "/* WARNING: Any changes made to this file will be lost when your ARForms settings are updated */";
                $temp_css_file1 .= "\n";
                ob_start();
                include $filename1;
                $temp_css_file1 .= ob_get_contents();
                ob_end_clean();
                $temp_css_file1 .= "\n " . $warn1;
                $wp_upload_dir = wp_upload_dir();
                $dest_dir = $wp_upload_dir['basedir'] . '/arforms/maincss/';
                $css_file_new1 = $dest_dir . 'maincss_materialize_' . $form_id. '.css';

                WP_Filesystem();
                global $wp_filesystem;
                $wp_filesystem->put_contents($css_file_new1, $temp_css_file1, 0777);
            }
        }

        $arf_update_templates = true;

        $wpdb->query( $wpdb->prepare("DELETE FROM `".$MdlDb->forms."` WHERE id < %d",12) );

        $wpdb->query( $wpdb->prepare("DELETE FROM `".$MdlDb->fields."` WHERE form_id < %d", 12));

        $arf_update_templates = true;

        $wpdb->query( $wpdb->prepare("DELETE FROM `".$MdlDb->forms."` WHERE id < %d",12) );

        $wpdb->query( $wpdb->prepare("DELETE FROM `".$MdlDb->fields."` WHERE form_id < %d", 12));

        $arfsettings = get_transient('arf_options');

        if (!is_object($arfsettings)) {
            if ($arfsettings) {
                $arfsettings = maybe_unserialize(maybe_serialize($arfsettings));
            } else {
                $arfsettings = get_option('arf_options');

                if (!is_object($arfsettings)) {
                    if ($arfsettings){
                        $arfsettings = maybe_unserialize(maybe_serialize($arfsettings));
                    } else {
                        $arfsettings = new arsettingmodel();
                    }
                    update_option('arf_options', $arfsettings);
                    set_transient('arf_options', $arfsettings);
                }
            }
        }

        $arfsettings->set_default_options();
        
        global $style_settings;

        $style_settings = get_transient('arfa_options');
        if (!is_object($style_settings)) {
            if ($style_settings) {
                $style_settings = maybe_unserialize(maybe_serialize($style_settings));
            } else {
                $style_settings = get_option('arfa_options');
                if (!is_object($style_settings)) {
                    if ($style_settings)
                        $style_settings = maybe_unserialize(maybe_serialize($style_settings));
                    else
                        $style_settings = new arstylemodel();
                    update_option('arfa_options', $style_settings);
                    set_transient('arfa_options', $style_settings);
                }
            }
        }

        $style_settings = get_option('arfa_options');
        if (!is_object($style_settings)) {
            if ($style_settings)
                $style_settings = maybe_unserialize(maybe_serialize($style_settings));
            else
                $style_settings = new arstylemodel();
            update_option('arfa_options', $style_settings);
        }

        $style_settings->set_default_options();
        $style_settings->store();

        include(MODELS_PATH."/artemplate.php");

    }

    if( version_compare($newdbversion, '3.7','<') ){

        /* CREATE TABLE FOR SITE WIDE POPUP */

        $charset_collate = '';

        if ($wpdb->has_cap('collation')) {
            if (!empty($wpdb->charset)){
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            }
            if (!empty($wpdb->collate)){
                $charset_collate .= " COLLATE $wpdb->collate";
            }
        }

        $popup_table = $MdlDb->form_popup;

        $popup_table_query = "CREATE TABLE IF NOT EXISTS {$popup_table} (
                popup_id int(11) NOT NULL auto_increment,
                form_id int(11) NOT NULL,
                popup_type varchar(15) default NULL,
                popup_option longtext default NULL,
                status tinyint(1) default 0,
                created_date datetime NOT NULL,
                PRIMARY KEY (popup_id)
            ) {$charset_collate};";

        if( !function_exists('dbDelta') ){
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        }

        dbDelta($popup_table_query);


        /* MailChimp Update for API v3 */

        $autoresponder = $MdlDb->autoresponder;

        $mailchimp_data = $wpdb->get_row( $wpdb->prepare("SELECT responder_api_key FROM {$autoresponder} WHERE responder_id = %d AND is_verify = %d",1,1));
        if( isset($mailchimp_data) ){
            global $arf_mcapi_version;

            if( '' == $arf_mcapi_version ){
                $arf_mcapi_version = '3.0';
            }

            $api_key = $mailchimp_data->responder_api_key;
                
            $dataCenter = substr($api_key,strpos($api_key,'-')+1);

            $mailchimp_url = 'https://'.$dataCenter.'.api.mailchimp.com/'.$arf_mcapi_version.'/lists?apikey='.$api_key.'&count=500';

            $response = wp_remote_get($mailchimp_url,array(
                'timeout' => '5000'
            ));

            
            if( is_wp_error($response) ){
                update_option( 'ARF_MAILCHIMP_UPDATE_FAILED_'.time(), json_encode($response) );
            } else {
                $mailchimp_list = json_decode($response['body'],true);
                $ls = 0;
                foreach ($mailchimp_list['lists'] as $key => $list) {
                    $list_str[$ls]['id'] = $list['id'];
                    $list_str[$ls]['name'] = $list['name'];
                    $ls++;
                }

                $wpdb->update(
                    $MdlDb->autoresponder,
                    array(
                        'responder_list_id' => json_encode($list_str)
                    ),
                    array(
                        'responder_id' => 1
                    )
                );
            }
        }

    }


    update_option('arf_db_version', '3.7.1');

    global $newdbversion;
    $newdbversion = '3.7.1';

    update_option('arf_new_version_installed', arf_sanitize_value(1, 'integer'));
}
?>