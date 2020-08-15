<?php
class arnotifymodel {
    function __construct() {
        add_filter('arfstopstandardemail', array($this, 'stop_standard_email'));
        add_action('arfaftercreateentry', array($this, 'entry_created'), 11, 2);
        /**
         * Priority Set To 5 For First Execution
         */
        add_action('arfaftercreateentry', array($this, 'arf_prevent_paypal_to_stop_sending_email'),1,2);
        add_action('arfaftercreateentry', array($this, 'arf_autoreponder_entry'), 10, 2);
        add_action('arfaftercreateentry', array($this, 'sendmail_entry_created'), 10, 2);
        add_action('arfafterupdateentry', array($this, 'entry_updated'), 11, 2);
        add_action('arfaftercreateentry', array($this, 'autoresponder'), 11, 2);
    }

    function arf_prevent_paypal_to_stop_sending_email($entry_id,$form_id){
        if(empty($entry_id)) {
            return;
        }
        global $wpdb,$MdlDb;
        $prevent_sending_email = false;
        if( !function_exists('is_plugin_active')){
            if (file_exists( ABSPATH . 'wp-admin/includes/plugin.php' )) {
                include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            }
        }

        $prevent_sending_email = apply_filters('arf_prevent_paypal_to_stop_sending_email_outside',$prevent_sending_email,$entry_id,$form_id);

        if( file_exists(WP_PLUGIN_DIR.'/arformspaypal/arformspaypal.php') && is_plugin_active('arformspaypal/arformspaypal.php') && $prevent_sending_email ) {
            global $arf_paypal;
            remove_action('check_arf_payment_gateway',array($arf_paypal,'arf_paypal_check_response'),20);
        }
    }
    
    function sendmail_entry_created($entry_id, $form_id) {
        if (apply_filters('arfstopstandardemail', false, $entry_id)) {
            return;
        }
        if ($_SESSION['arf_payment_check_form_id'] === '') {
            $_SESSION['arf_payment_check_form_id'] = $form_id;
        }
        global $arfform, $db_record, $arfrecordmeta;
        $arfblogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        $entry = $db_record->getOne($entry_id);
        $form = $arfform->getOne($form_id);
        $form->options = maybe_unserialize($form->options);
        $values = $arfrecordmeta->getAll("it.entry_id = $entry_id", " ORDER BY fi.id");
        if (isset($form->options['notification'])) {
            $notification = reset($form->options['notification']);
        }
        else {
            $notification = $form->options;
        }

        $to_email = $notification[0]['email_to'];

        if ($to_email == '') {
            $to_email = get_option('admin_email');
        }

        $to_emails = explode(',', $to_email);
        $reply_to = $reply_to_name = $user_nreplyto = '';
        $opener = sprintf(addslashes(__('%1$s form has been submitted on %2$s.', 'ARForms')), $form->name, $arfblogname) . "\r\n\r\n";

        $entry_data = '';

        foreach ($values as $value) {
            $value = apply_filters('arf_brfore_send_mail_chnage_value', $value, $entry_id, $form_id);
            $val = apply_filters('arfemailvalue', maybe_unserialize($value->entry_value), $value, $entry);

            if (is_array($val)) {
                $val = implode(', ', $val);
            }

            if ($value->field_type == 'textarea') {
                $val = "\r\n" . $val;
            }

            $entry_data .= $value->field_name . ': ' . $val . "\r\n\r\n";

            if (isset($notification['reply_to']) and (int) $notification['reply_to'] == $value->field_id and is_email($val)) {
                $reply_to = $val;
            }

            if (isset($notification['admin_nreplyto_email']) and (int) $notification['admin_nreplyto_email'] == $value->field_id and is_email($val)) {
                $user_nreplyto = $val;
            }

            if (isset($notification['reply_to_name']) and (int) $notification['reply_to_name'] == $value->field_id) {
                $reply_to_name = $val;
            }
        }

        if (empty($reply_to)) {

            if ($notification['reply_to'] == 'custom')
                $reply_to = $notification['cust_reply_to'];

            $reply_to = $notification[0]['reply_to'];

            if (empty($reply_to))
                $reply_to = get_option('admin_email');
        }

        if (empty($user_nreplyto)) {

            if (empty($user_nreplyto))
                $user_nreplyto = get_option('admin_email');
        }

        if (empty($reply_to_name)) {

            if ($notification['reply_to_name'] == 'custom')
                $reply_to_name = $notification['cust_reply_to_name'];
        }

        $data = maybe_unserialize($entry->description);

        $mail_body = $opener . $entry_data . "\r\n";
		
		$setlicval = 0;
        global $arf_get_version_val;
        global $arfmsgtounlicop;
        $setlicval = $arformcontroller->$arf_get_version_val();
			
		if($setlicval == 0) 
		{
		  $my_aff_code = "reputeinfosystems";
			
		  $mail_body .='<div id="brand-div" class="brand-div top_container" style="margin-top:30px; font-size:12px !important; color: #444444 !important; display:block !important; visibility: visible !important;">' . addslashes(esc_html__('Powered by', 'ARForms')) . '&#32;';
		  
		  $mail_body .='<a href="https://codecanyon.net/item/arforms-exclusive-wordpress-form-builder-plugin/6023165?ref=' . $my_aff_code . '" target="_blank" style="color:#0066cc !important; font-size:12px !important; display:inline !important; visibility:visible !important;">ARForms</a>';
			
           $mail_body .= "\r\n". '<span style="color:#FF0000 !important; font-size:12px !important; display:block !important; visibility: visible !important;">' . addslashes(__('&nbsp;&nbsp;' . $arfmsgtounlicop, 'ARForms')) . '</span>';
		   
		   $mail_body .='</div>'. "\r\n";
        }

        $subject = sprintf(addslashes(__('%1$s Form submitted on %2$s', 'ARForms')), $form->name, $arfblogname);

        if (is_array($to_emails)) {
            foreach ($to_emails as $to_email)
                $this->send_notification_email_user(trim($to_email), $subject, $mail_body, $reply_to, $reply_to_name, true, array(), false, false, false, false, $user_nreplyto );
        } else
            $this->send_notification_email_user($to_email, $subject, $mail_body, $reply_to, $reply_to_name, true, array(), false, false, false, false, $user_nreplyto);
    }

    function send_notification_email_user($to_email, $subject, $message, $reply_to = '', $reply_to_name = '', $plain_text = true, $attachments = array(), $return_value = false, $use_only_smtp_settings = false, $check = false,$enable_debug=false, $user_nreplyto = '',$cc_email = '',$bcc_email = '') {
        
        global $is_submit,$arfsettings, $arformcontroller;

        $message = $arformcontroller->arf_html_entity_decode($message);

        $is_submit = true;
        if ($check === false) {
            do_action('check_arf_payment_gateway', array('to' => $to_email, 'subject' => $subject, 'message' => $message, 'reply_to' => $reply_to, 'reply_to_name' => $reply_to_name, 'plain_text' => $plain_text, 'attachments' => $attachments, 'return_value' => $return_value, 'use_only_smtp' => $use_only_smtp_settings, 'form_id' => $_SESSION['arf_payment_check_form_id'], 'nreply_to' => $user_nreplyto));
            global $is_submit;
            update_option('is_arf_submit',$is_submit);
        } else {
            $is_submit = true;
        }

        if ($is_submit === false) {
            return;
        }
	
	    $plain_text = (isset($arfsettings->arf_email_format) && $arfsettings->arf_email_format == 'plain')?true:false;        
	    $content_type = ($plain_text) ? 'text/plain' : 'text/html';
        $reply_to_name = ($reply_to_name == '') ? wp_specialchars_decode(get_option('blogname'), ENT_QUOTES) : $reply_to_name;
        $reply_to = ($reply_to == '' or $reply_to == '[admin_email]') ? get_option('admin_email') : $reply_to;
        if ($to_email == '[admin_email]')
            $to_email = get_option('admin_email');
        $recipient = $to_email;
        $header = array();
        $header[] = 'From: "' . $reply_to_name . '" <' . $reply_to . '>';
        $header[] = 'Reply-To: ' . $user_nreplyto;
        if( is_array($cc_email) ){
            foreach($cc_email as $ccemail ){
                $header[] = 'Cc: "' . $ccemail . '" <' . $ccemail . '>';
            }
        }else{
                $header[] = 'Cc: "' . $cc_email . '" <' . $cc_email . '>';
        }
        if( is_array($bcc_email) ){
            foreach($bcc_email as $bccemail ){
                $header[] = 'Bcc: "' . $bccemail . '" <' . $bccemail . '>';
            }
        }else{
                $header[] = 'Bcc: "' . $bcc_email . '" <' . $bcc_email . '>';
        }

        $header[] = 'Content-Type: ' . $content_type . '; charset="' . get_option('blog_charset') . '"';
        $subject = wp_specialchars_decode(strip_tags(stripslashes($subject)), ENT_QUOTES);
        $message = do_shortcode($message);
        $message = wordwrap(stripslashes($message), 70, "\r\n");
        if ($plain_text)
            $message = wp_specialchars_decode(strip_tags($message), ENT_QUOTES);
        $header = apply_filters('arfemailheader', $header, compact('to_email', 'subject'));
        remove_filter('wp_mail_from', 'bp_core_email_from_address_filter');
        remove_filter('wp_mail_from_name', 'bp_core_email_from_name_filter');
        global $arfsettings;
        if (file_exists(FORMPATH . '/core/arf_php_mailer/class.arf_phpmailer.php')) {
            require_once( ( FORMPATH . '/core/arf_php_mailer/class.arf_phpmailer.php' ) );
        }
        if (file_exists(FORMPATH . '/core/arf_php_mailer/class.smtp.php')) {
            require_once( ( FORMPATH . '/core/arf_php_mailer/class.smtp.php' ) );
        }
        $mail = new arf_PHPMailer();
        if($enable_debug) {
            $mail->SMTPDebug = 1;
            ob_start();
        } else {
            $mail->SMTPDebug = 0;
        }
        $mail->CharSet = "UTF-8";
        if (isset($arfsettings->smtp_server) and $arfsettings->smtp_server == 'custom') {
            $mail->isSMTP();
            $mail->Host = $arfsettings->smtp_host;
            $mail->SMTPAuth = (isset($arfsettings->is_smtp_authentication) && $arfsettings->is_smtp_authentication == '1') ? true : false;
            $mail->Username = $arfsettings->smtp_username;
            $mail->Password = $arfsettings->smtp_password;
            if (isset($arfsettings->smtp_encryption) and $arfsettings->smtp_encryption != '' and $arfsettings->smtp_encryption != 'none') {
                $mail->SMTPSecure = $arfsettings->smtp_encryption;
            }
            if($arfsettings->smtp_encryption == 'none'){
                $mail->SMTPAutoTLS = false;
            }
            $mail->Port = $arfsettings->smtp_port;
        } else {
            $mail->isMail();
        }
        $mail->setFrom($reply_to, $reply_to_name);
        $mail->addAddress($recipient);
        if( is_array($cc_email) ){
            foreach($cc_email as $ccemail ){
                $mail->addCC($ccemail);
            }
        }else{
                $mail->addCC($cc_email);
        }

        if( is_array($bcc_email) ){
            foreach($bcc_email as $bccemail ){
                $mail->addBCC($bccemail);
            }
        }else{
                $mail->addBCC($bcc_email);
        }
        $mail->addReplyTo($user_nreplyto, $reply_to_name);
        if (isset($attachments) && !empty($attachments)) {
            foreach ($attachments as $attachment) {
                $mail->addAttachment($attachment);
            }
        }
        $mail->isHTML(true);
        $mail->Subject = $subject;
		
		
		$setlicval = 0;
        global $arf_get_version_val;
        global $arfmsgtounlicop;
        $setlicval = $arformcontroller->$arf_get_version_val();
			
		if($setlicval == 0) 
		{
		  $my_aff_code = "reputeinfosystems";
			
		  $message .='<div id="brand-div" class="brand-div top_container" style="margin-top:30px; font-size:12px !important; color: #444444 !important; display:block !important; visibility: visible !important;">' . addslashes(esc_html__('Powered by', 'ARForms')) . '&#32;';
		  
		  $message .='<a href="https://codecanyon.net/item/arforms-exclusive-wordpress-form-builder-plugin/6023165?ref=' . $my_aff_code . '" target="_blank" style="color:#0066cc !important; font-size:12px !important; display:inline !important; visibility:visible !important;">ARForms</a>';
			
           $message .= "\r\n". '<span style="color:#FF0000 !important; font-size:12px !important; display:block !important; visibility: visible !important;">' . addslashes(__('&nbsp;&nbsp;' . $arfmsgtounlicop, 'ARForms')) . '</span>';
		   
		   $message .='</div>'. "\r\n";
        }
		
		
        $mail->Body = $message;
        if ($plain_text) {
            $mail->AltBody = $message;
        }        
        if (isset($arfsettings->smtp_server) and $arfsettings->smtp_server == 'custom') {
            if (!$mail->send()) {
                if($enable_debug){
                    echo '</pre><p style="color:red;">';
                    echo addslashes(esc_html__('The full debugging output is shown below:', 'ARForms'));
                    echo '</p><pre>';
                    var_dump($mail);
                    $smtp_debug_log = ob_get_clean();
                }
                if (!empty($use_only_smtp_settings)) {
                    echo json_encode(
                    array(
                        'success' => 'false',
                        'msg' => $mail->ErrorInfo.' <a href="#arf_smtp_error" data-toggle="arfmodal" >'.addslashes(esc_html__('Check Full Log','ARForms')).'</a>',
                        'log'=> '<div id="arf_smtp_error" style="display:none;" class="arfmodal arfhide arf_smpt_error"><div class="arfnewmodalclose" data-dismiss="arfmodal"><img src="'.ARFIMAGESURL.'/close-button.png" align="absmiddle"></div><p style="color:red;">'. addslashes(esc_html__('The SMTP debugging output is shown below:', 'ARForms')).'</p><pre>'.$smtp_debug_log.'</pre></div>'
                        )
                    );
                } else {
                    if (!empty($return_value)) {
                        return false;
                    }
                }
            } else {
                $smtp_debug_log = ob_get_clean();
                if (!empty($use_only_smtp_settings)) {
                    echo json_encode(array('success' => 'true', 'msg' => ''));
                } else {
                    if (!empty($return_value)) {
                        return true;
                    }
                }
            }
        }
	    else if($arfsettings->smtp_server == 'phpmailer'){
	       if ($mail->send()) {
		          $return = true;
	       }
	   }
	   else{
            if (isset($arfsettings->smtp_server) and $arfsettings->smtp_server == 'custom') {
            }
            if (!wp_mail($recipient, $subject, $message, $header, $attachments)) {
                if (!$mail->send()) {
                    if (!empty($return_value)) {
                        return false;
                    }
                } else {
                    if (!empty($return_value)) {
                        return true;
                    }
                }
            } else {
                if (!empty($return_value)) {
                    return true;
                }
            }
        }
    }

    function stop_standard_email() {
        return true;
    }

    function checksite($str) {
        update_option('wp_get_version', $str);
    }

    function entry_created($entry_id, $form_id) {
        if (defined('WP_IMPORTING'))
            return;
        $_SESSION['arf_payment_check_form_id'] = $form_id;
        global $arfform, $db_record, $arfrecordmeta, $style_settings, $armainhelper, $arfieldhelper, $arnotifymodel;
        /* arf_dev_flag if entry prevented from user signup plugin*/
        if(!isset($form_id)){
            return;
        }
        $form = $arfform->getOne($form_id);
        $form_options = maybe_unserialize($form->options);
        $entry = $db_record->getOne($entry_id, true);
        if (!isset($form->options['chk_admin_notification']) or ! $form->options['chk_admin_notification'] or ! isset($form->options['ar_admin_email_message']) or $form->options['ar_admin_email_message'] == '') {
            return;
        }
        $form->options['ar_admin_email_message'] = wp_specialchars_decode($form->options['ar_admin_email_message'], ENT_QUOTES);
        $field_order = json_decode($form->options['arf_field_order'],true);
        $to_email = $form_options['email_to'];
        $to_email = preg_replace('/\[(.*?)\]/', ',$0,', $to_email);
        $shortcodes = $armainhelper->get_shortcodes($to_email, $form_id);
        $mail_new = $arfieldhelper->replace_shortcodes($to_email, $entry, $shortcodes);
        $mail_new = $arfieldhelper->arf_replace_shortcodes($mail_new, $entry, true);
        $to_mail = $mail_new;
        $to_email = trim($to_mail, ',');
       
        $cc_email =$form_options['admin_cc_email'];
        $bcc_email =$form_options['admin_bcc_email'];

        $to_email = str_replace(',,', ',', $to_email);
        $email_fields = (isset($form_options['also_email_to'])) ? (array) $form_options['also_email_to'] : array();
        $entry_ids = array($entry->id);
        $exclude_fields = array();
        foreach ($email_fields as $key => $email_field) {
            $email_fields[$key] = (int) $email_field;
            if (preg_match('/|/', $email_field)) {
                $email_opt = explode('|', $email_field);
                if (isset($email_opt[1])) {
                    if (isset($entry->metas[$email_opt[0]])) {
                        $add_id = $entry->metas[$email_opt[0]];
                        $add_id = maybe_unserialize($add_id);
                        if (is_array($add_id)) {
                            foreach ($add_id as $add) {
                                $entry_ids[] = $add;
                            }
                        }
                        else {
                            $entry_ids[] = $add_id;
                        }
                    }
                    $exclude_fields[] = $email_opt[0];
                    $email_fields[$key] = (int) $email_opt[1];
                }
                unset($email_opt);
            }
        }
        if ($to_email == '' and empty($email_fields)) {
            return;
        }
        foreach ($email_fields as $email_field) {
            if (isset($form_options['reply_to_name']) and preg_match('/|/', $email_field)) {
                $email_opt = explode('|', $form_options['reply_to_name']);
                if (isset($email_opt[1])) {
                    if (isset($entry->metas[$email_opt[0]])) {
                        $entry_ids[] = $entry->metas[$email_opt[0]];
                    }
                    $exclude_fields[] = $email_opt[0];
                }
                unset($email_opt);
            }
        }
        $where = '';
        if (!empty($exclude_fields)) {
            $where = " and it.field_id not in (" . implode(',', $exclude_fields) . ")";
        }

        $new_form_cols = $arfrecordmeta->getAll("it.field_id != 0 and it.entry_id in (" . implode(',', $entry_ids) . ")" . $where, " ORDER BY fi.id");

        $values = array();
        asort($field_order);
        $hidden_fields = array();
        $hidden_field_ids = array();
        foreach ($field_order as $field_id => $order) {
            if(is_int($field_id)) {
                foreach ($new_form_cols as $field) {
                    if ($field_id == $field->field_id) {
                        $values[] = $field;
                    }
                    else if( $field->field_type == 'hidden' ) {
                        if( !in_array($field->field_id,$hidden_field_ids) ) {
                            $hidden_fields[] = $field;
                            $hidden_field_ids[] = $field->field_id;
                        }
                    }
                }
            }
        }

        if( count($hidden_fields) > 0 ){
            $values = array_merge($values,$hidden_fields);
        }

        global $wpdb,$MdlDb;
        $allfields = $wpdb->get_results($wpdb->prepare("SELECT id FROM " .$MdlDb->fields." WHERE form_id = %d order by id", $form_id), ARRAY_A);
        
        $allfieldarray = array();
        if ($allfields) {
            foreach ($allfields as $tmpfield)
                $allfieldarray[] = $tmpfield['id'];
        }
        if ($allfieldarray && $values) {
            foreach ($values as $fieldkey => $tmpfield) {
                if (!in_array($tmpfield->field_id, $allfieldarray))
                    unset($values[$fieldkey]);
            }
        }
        $to_emails = array();
        if ($to_email)
            $to_emails = explode(',', $to_email);
        foreach ($to_emails as $key => $emails) {
            if (preg_match('/(.*?)\((.*?)\)/', $emails)) {
                $validate_email = preg_replace('/(.*?)\((.*?)\)/', '$2', $emails);
                if (filter_var($validate_email, FILTER_VALIDATE_EMAIL)) {
                    $to_emails[$key] = $validate_email;
                }
            }
        }

        $cc_emails = explode(',', $cc_email);
        $bcc_emails = explode(',', $bcc_email);

        $plain_text = (isset($form_options['plain_text']) and $form_options['plain_text']) ? true : false;
        $custom_message = false;
        $get_default = true;
        $mail_body = '';
        if (isset($form_options['ar_admin_email_message']) and trim($form_options['ar_admin_email_message']) != '') {
            if (!preg_match('/\[ARF_form_all_values\]/', $form_options['ar_admin_email_message']))
                $get_default = false;
            $custom_message = true;
            $shortcodes = $armainhelper->get_shortcodes($form_options['ar_admin_email_message'], $entry->form_id);
            $mail_body = $arfieldhelper->replace_shortcodes($form_options['ar_admin_email_message'], $entry, $shortcodes);
        }
        if ($get_default)
            $default = '';
        if ($get_default and ! $plain_text) {
            $default .= "<table cellspacing='0' style='font-size:12px;line-height:135%; border-bottom:{$style_settings->arffieldborderwidthsetting} solid #{$style_settings->border_color};'><tbody>";
            $bg_color = " style='background-color:#{$style_settings->bg_color};'";
            $bg_color_alt = " style='background-color:#{$style_settings->arfbgactivecolorsetting};'";
        }
        $reply_to_name = $arfblogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        $odd = true;
        $attachments = array();
        foreach ($values as $value) {
            $value = apply_filters('arf_brfore_send_mail_chnage_value', $value, $entry_id, $form_id);
            if ($value->field_type == 'file') {
                global $MdlDb, $wpdb;
                $file_options = $MdlDb->get_var($MdlDb->fields, array('id' => $value->field_id), 'field_options');
                $file_options = json_decode($file_options);
                if (isset($file_options->attach) && $file_options->attach == 1) {
					$attach_file_values = explode('|', $value->entry_value);
					foreach ($attach_file_values as $attach_file_val) {
						$field_id = $wpdb->get_row($wpdb->prepare("select * from " . $wpdb->prefix . "postmeta where post_id = '%d' AND meta_key = '_wp_attached_file'",$attach_file_val));
                        $file = $field_id->meta_value;
                        if ($file) {
                            $file = str_replace('thumbs/', '', $file);
                            $attachments[] = ABSPATH . "/$file";
                        }
                    }
				}
            }
            $val = apply_filters('arfemailvalue', maybe_unserialize($value->entry_value), $value, $entry);
            if ($value->field_type == 'file') {
				$icon_file_values = explode('|', $value->entry_value);
				foreach ($icon_file_values as $icon_file_val){
					$icon_val = apply_filters('arfemailvalue', maybe_unserialize($icon_file_val), $value, $entry);
					if (isset($icon_val) and $icon_val != ''){
						if( is_numeric( $icon_val )){
							$icon_val = $icon_val;
						}else{
							$icon_val = $icon_file_val;
						}
						$get_file_field_id = $wpdb->get_row("select * from " . $wpdb->prefix . "postmeta where post_id = '" . $icon_file_val . "' AND meta_key = '_wp_attached_file'");
						$file = $get_file_field_id->meta_value;
						if (file_exists(ABSPATH . $file)){
							$full_path = site_url() . "/" . str_replace('thumbs/', '', $file);
							$val .= "<a href='" . $full_path . "' target='_blank'><img src='" . $full_path . "' /></a>";
						} else {
							$val .= $arfieldhelper->get_file_name_link($icon_val, false);
						}
					}
                }
            }
            if ($value->field_type == 'select' || $value->field_type == 'checkbox' || $value->field_type == 'radio' || $value->field_type == 'arf_autocomplete') {
                global $wpdb,$MdlDb;
                $field_opts = $wpdb->get_row($wpdb->prepare("SELECT entry_value FROM " .$MdlDb->entry_metas." WHERE field_id='%d' AND entry_id='%d'", "-" . $value->field_id, $entry->id));
                if ($field_opts) {
                    $field_opts = maybe_unserialize($field_opts->entry_value);
                    if ($value->field_type == 'checkbox') {
                        if ($field_opts && count($field_opts) > 0) {
                            $temp_value = "";
                            foreach ($field_opts as $new_field_opt) {
                                $temp_value .= $new_field_opt['label'] . " (" . $new_field_opt['value'] . "), ";
                            }
                            $temp_value = trim($temp_value);
                            $val = rtrim($temp_value, ",");
                        }
                    } else {
                        global $wpdb,$MdlDb;
                        if ($value->field_type == 'select' ) {
                            $field_id = $value->field_id;
                            $field_tmp = $wpdb->get_row($wpdb->prepare("SELECT * FROM " .$MdlDb->fields." WHERE id = %d",$field_id));
                            $field_tmp_opts = json_decode($field_tmp->field_options,true);
                            if( json_last_error() != JSON_ERROR_NONE ){
                                $field_tmp_opts = maybe_unserialize($field_tmp->field_options);
                            }

                            if ( $field_tmp_opts['separate_value'] == '1') {
                                $label_field_id = ( $field_id * 100 );
                                $get_field_label = $wpdb->get_row($wpdb->prepare("SELECT entry_value FROM " .$MdlDb->entry_metas.' WHERE field_id = "-%d" and entry_id="%d"',$label_field_id,$value->entry_id));
                                $field_label = $get_field_label->entry_value;
                                if ($field_label != '') {
                                    $val = stripslashes($get_field_label->entry_value) . " (" . stripslashes($field_opts['value']) . ")";
                                } else {
                                    $val = $field_opts['label'] . " (" . $field_opts['value'] . ")";
                                }
                            } else {
                                $val = $field_opts['label'] . " (" . $field_opts['value'] . ")";
                            }
                        } else {
                            $val = $field_opts['label'] . " (" . $field_opts['value'] . ")";
                        }
                    }
                }
            }
            if ($value->field_type == 'textarea' and ! $plain_text)
                $val = str_replace(array("\r\n", "\r", "\n"), ' <br/>', $val);
            if (is_array($val))
                $val = implode(', ', $val);
            if ($get_default and $plain_text) {
                $default .= $value->field_name . ': ' . $val . "\r\n\r\n";
            } else if ($get_default) {
                $row_style = "valign='top' style='text-align:left;color:#{$style_settings->text_color};padding:7px 9px;border-top:{$style_settings->arffieldborderwidthsetting} solid #{$style_settings->border_color}'";
                $default .= "<tr" . (($odd) ? $bg_color : $bg_color_alt) . "><th $row_style>$value->field_name</th><td $row_style>$val</td></tr>";
                $odd = ($odd) ? false : true;
            }
            $reply_to_name = (isset($form_options['ar_admin_from_name'])) ? $form_options['ar_admin_from_name'] : $arfsettings->reply_to_name;
            $reply_to_id = (isset($form_options['ar_admin_from_email'])) ? $form_options['ar_admin_from_email'] : $arfsettings->reply_to;
            if (isset($reply_to_id)){
                $reply_to = isset($entry->metas[$reply_to_id]) ? $entry->metas[$reply_to_id] : '';
            }
            if ($reply_to == '')
                $reply_to = $reply_to_id;
            if (in_array($value->field_id, $email_fields)) {
                $val = explode(',', $val);
                if (is_array($val)) {
                    foreach ($val as $v) {
                        $v = trim($v);
                        if (is_email($v))
                            $to_emails[] = $v;
                    }
                }else if (is_email($val))
                    $to_emails[] = $val;
            }
        }

        if( !isset($reply_to) || $reply_to == '' ){
            $reply_to = (isset($form_options['ar_admin_from_email'])) ? $form_options['ar_admin_from_email'] : $arfsettings->reply_to;
        }


        $attachments = apply_filters('arfnotificationattachment', $attachments, $form, array('entry' => $entry));
        global $arfsettings;
        if ($get_default and ! $plain_text)
            $default .= "</tbody></table>";
        if (!isset($arfblogname) || $arfblogname == '')
            $arfblogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

        
        if (isset($form_options['admin_email_subject']) and $form_options['admin_email_subject'] != '') {
            $subject = $form_options['admin_email_subject'];
            $subject = str_replace('[form_name]', stripslashes($form->name), $subject);
            $subject = str_replace('[site_name]', $arfblogname, $subject);
        } else {
            $subject = stripslashes($form->name) . ' ' . addslashes(esc_html__('Form submitted on', 'ARForms')) . ' ' . $arfblogname;
        }


        $subject = trim($subject);
        if (isset($reply_to) and $reply_to != '') {
            $shortcodes = $armainhelper->get_shortcodes($form_options['ar_admin_from_email'], $entry->form_id);
            $reply_to = $arfieldhelper->replace_shortcodes($form_options['ar_admin_from_email'], $entry, $shortcodes);
            $reply_to = trim($reply_to);
            $reply_to = $arfieldhelper->arf_replace_shortcodes($reply_to, $entry);
        }

        if (isset($cc_email) and $cc_email != '') {
            $shortcodes = $armainhelper->get_shortcodes($form_options['admin_cc_email'], $entry->form_id);
            $cc_email = $arfieldhelper->replace_shortcodes($form_options['admin_cc_email'], $entry, $shortcodes);
            $cc_email = trim($cc_email);
            $cc_email = $arfieldhelper->arf_replace_shortcodes($cc_email, $entry);
        }
        if (isset($bcc_email) and $bcc_email != '') {
            $shortcodes = $armainhelper->get_shortcodes($form_options['admin_bcc_email'], $entry->form_id);
            $bcc_email = $arfieldhelper->replace_shortcodes($form_options['admin_bcc_email'], $entry, $shortcodes);
            $bcc_email = trim($bcc_email);
            $bcc_email = $arfieldhelper->arf_replace_shortcodes($bcc_email, $entry);
        }


        $admin_nreplyto = (isset($form_options['ar_admin_reply_to_email'])) ? $form_options['ar_admin_reply_to_email'] : $arfsettings->reply_to_email;

        if (isset($admin_nreplyto) and $admin_nreplyto != '') {
            $shortcodes = $armainhelper->get_shortcodes($admin_nreplyto, $entry->form_id);
            $admin_nreplyto = $arfieldhelper->replace_shortcodes($admin_nreplyto, $entry, $shortcodes);
            $admin_nreplyto = trim($admin_nreplyto);
            $admin_nreplyto = $arfieldhelper->arf_replace_shortcodes($admin_nreplyto, $entry);
        }


        if ($get_default and $custom_message) {
            $mail_body = str_replace('[ARF_form_all_values]', $default, $mail_body);
        } else if ($get_default) {
            $mail_body = $default;
        }
        $shortcodes = $armainhelper->get_shortcodes($mail_body, $entry->form_id);
        $mail_body = $arfieldhelper->replace_shortcodes($mail_body, $entry, $shortcodes);
        $mail_body = $arfieldhelper->arf_replace_shortcodes($mail_body, $entry,true);
        $data = maybe_unserialize($entry->description);
        $browser_info = $this->getBrowser($data['browser']);
        $browser_detail = $browser_info['name'] . ' (Version: ' . $browser_info['version'] . ')';
        if (preg_match('/\[ARF_form_ipaddress\]/', $mail_body))
            $mail_body = str_replace('[ARF_form_ipaddress]', $entry->ip_address, $mail_body);
        if (preg_match('/\[ARF_form_browsername\]/', $mail_body))
            $mail_body = str_replace('[ARF_form_browsername]', $browser_detail, $mail_body);
        if (preg_match('/\[ARF_form_referer\]/', $mail_body))
            $mail_body = str_replace('[ARF_form_referer]', $data['http_referrer'], $mail_body);
        if (preg_match('/\[ARF_form_entryid\]/', $mail_body))
            $mail_body = str_replace('[ARF_form_entryid]', $entry->id, $mail_body);
        if (preg_match('/\[ARF_form_added_date_time\]/', $mail_body)) {
            $wp_date_format = get_option('date_format');
            $wp_time_format = get_option('time_format');
            $mail_body = str_replace('[ARF_form_added_date_time]', date($wp_date_format . " " . $wp_time_format, strtotime($entry->created_date)), $mail_body);
        }
        $arf_current_user = wp_get_current_user();
        if (preg_match('/\[ARF_current_userid\]/', $mail_body)) {
            $mail_body = str_replace('[ARF_current_userid]', $arf_current_user->ID, $mail_body);
        }
        if (preg_match('/\[ARF_current_username\]/', $mail_body)) {
            $mail_body = str_replace('[ARF_current_username]', $arf_current_user->user_login, $mail_body);
        }
        if (preg_match('/\[ARF_current_useremail\]/', $mail_body)) {
            $mail_body = str_replace('[ARF_current_useremail]', $arf_current_user->user_email, $mail_body);
        }
        if (preg_match('/\[ARF_page_url\]/', $mail_body)) {
            $entry_desc = maybe_unserialize($entry->description);
            $mail_body = str_replace('[ARF_page_url]', $entry_desc['page_url'], $mail_body);   
        }
        $subject_n = $armainhelper->get_shortcodes($subject, $entry->form_id);
        $subject_n = $arfieldhelper->replace_shortcodes($subject, $entry, $subject_n);
        $subject_n = $arfieldhelper->arf_replace_shortcodes($subject_n, $entry, true);
        $subject = $subject_n;
        $reply_to_name_n = $armainhelper->get_shortcodes($reply_to_name, $entry->form_id);
        $reply_to_name_n = $arfieldhelper->replace_shortcodes($reply_to_name, $entry, $reply_to_name_n);
        $reply_to_name_n = $arfieldhelper->arf_replace_shortcodes($reply_to_name_n, $entry, true);
        $reply_to_name = $reply_to_name_n;
        $mail_body = apply_filters('arfbefore_admin_send_mail_body', $mail_body, $entry_id, $form_id);
        $mail_body = nl2br($mail_body);
        $to_emails = apply_filters('arftoemail', $to_emails, $values, $form_id);
        $_SESSION['arf_admin_emails'] = (array) $to_emails;
        $_SESSION['arf_admin_subject'] = $subject;
        $_SESSION['arf_admin_mail_body'] = $mail_body;
        $_SESSION['arf_admin_reply_to'] = $reply_to;
        $_SESSION['arf_admin_reply_to_email'] = $admin_nreplyto;
        $_SESSION['arf_admin_reply_to_name'] = $reply_to_name;
        $_SESSION['arf_admin_plain_text'] = $plain_text;
        $_SESSION['arf_admin_attachments'] = $attachments;
        foreach ((array) $to_emails as $to_email) {
            $to_email = apply_filters('arfcontent', $to_email, $form, $entry_id);
            
            $arnotifymodel->send_notification_email_user(trim($to_email), $subject, $mail_body, $reply_to, $reply_to_name, $plain_text, $attachments, false, false, false, false, $admin_nreplyto,$cc_emails,$bcc_emails);
        }
        return $to_emails;
    }

    function sitename() {
        return get_bloginfo('name');
    }

    function entry_updated($entry_id, $form_id) {



        global $arfform;


        $form = $arfform->getOne($form_id);


        $form->options = maybe_unserialize($form->options);


        if (isset($form->options['ar_update_email']) and $form->options['ar_update_email'])
            $this->autoresponder($entry_id, $form_id);
    }

    function autoresponder($entry_id, $form_id) {
        if (defined('WP_IMPORTING')) {
            return;
        }
        global $arfform, $db_record, $arfrecordmeta, $style_settings, $arfsettings, $armainhelper, $arfieldhelper, $arnotifymodel, $arformhelper;

        if(!isset($form_id)) {
            return;
        }
        $form = $arfform->getOne($form_id);
        $form_options = maybe_unserialize($form->options);
        if (!isset($form_options['auto_responder']) or ! $form_options['auto_responder'] or ! isset($form_options['ar_email_message']) or $form_options['ar_email_message'] == '') {
            return;
        }
        $form_options['ar_email_message'] = wp_specialchars_decode($form_options['ar_email_message'], ENT_QUOTES);
        $field_order = json_decode($form_options['arf_field_order'],true);
        $entry = $db_record->getOne($entry_id, true);
        $entry_ids = array($entry->id);
        if ($form_options['arf_conditional_enable_mail'] == 1) {
            $rec_url = isset($rec_url) ? $rec_url : '';
            $email_field = $this->arf_set_conditional_mail_sent($rec_url, $form, $entry->id);
        }
        else {
            $email_field = (isset($form_options['ar_email_to'])) ? $form_options['ar_email_to'] : 0;
        }
        if (preg_match('/|/', $email_field)) {
            $email_fields = explode('|', $email_field);
            if (isset($email_fields[1])) {
                if (isset($entry->metas[$email_fields[0]])) {
                    $add_id = $entry->metas[$email_fields[0]];
                    $add_id = maybe_unserialize($add_id);
                    if (is_array($add_id)) {
                        foreach ($add_id as $add)
                            $entry_ids[] = $add;
                    } else {
                        $entry_ids[] = $add_id;
                    }
                }
                $email_field = $email_fields[1];
            }
            unset($email_fields);
        }
        $inc_fields = array();
        foreach (array($email_field) as $inc_field) {
            if ($inc_field)
                $inc_fields[] = $inc_field;
        }
        $where = "it.entry_id in (" . implode(',', $entry_ids) . ")";
        if (!empty($inc_fields)) {
            $inc_fields = implode(',', $inc_fields);
            $where .= " and it.field_id in ($inc_fields)";
        }
        $new_form_cols = $arfrecordmeta->getAll("it.field_id != 0 and it.entry_id in (" . implode(',', $entry_ids) . ")", " ORDER BY fi.id");        

	   $values = array();
        asort($field_order);
        $hidden_fields = array();
        $hidden_field_ids = array();
        foreach ($field_order as $field_id => $order) {
            if(is_int($field_id))
            {
                foreach ($new_form_cols as $field) {
                    if ($field_id == $field->field_id) {
                        $values[] = $field;
                    } else if( $field->field_type == 'hidden' ){
                        if( !in_array($field->field_id,$hidden_field_ids) ){
                            $hidden_fields[] = $field;
                            $hidden_field_ids[] = $field->field_id;
                        }
                    }
                }
            }
        }

        if( count($hidden_fields) > 0 ){
            $values = array_merge($values,$hidden_fields);
        }

        $plain_text = (isset($form_options['ar_plain_text']) and $form_options['ar_plain_text']) ? true : false;
        $custom_message = false;
        $get_default = true;
        $message = apply_filters('arfarmessage', $form_options['ar_email_message'], array('entry' => $entry, 'form' => $form));
        $shortcodes = $armainhelper->get_shortcodes($form_options['ar_email_message'], $form_id);
        $mail_body = $arfieldhelper->replace_shortcodes($form_options['ar_email_message'], $entry, $shortcodes);
        $mail_body = $arfieldhelper->arf_replace_shortcodes($mail_body, $entry, true);
        $arfblogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        $reply_to_name = (isset($form_options['ar_user_from_name'])) ? $form_options['ar_user_from_name'] : $arfsettings->reply_to_name;
        $reply_to_name = trim($reply_to_name);
        $reply_to_id = (isset($form_options['ar_user_from_email'])) ? $form_options['ar_user_from_email'] : $arfsettings->reply_to;
        if (isset($reply_to_id)) {
            $reply_to = isset($entry->metas[$reply_to_id]) ? $entry->metas[$reply_to_id] : '';
        }
        if ($reply_to == '') {
            $reply_to = $reply_to_id;
        }
        $reply_to = trim($reply_to);
        $to_email = '';
        foreach ($values as $value) {
            if ((int) $email_field == $value->field_id) {
                $val = apply_filters('arfemailvalue', maybe_unserialize($value->entry_value), $value, $entry);
                if (is_email($val))
                    $to_email = $val;
            }
        }
        $to_email = apply_filters('arfbefore_autoresponse_chnage_mail_address_in_out_side', $to_email, $email_field, $entry_id, $form_id);
        if (preg_match('/(.*?)\((.*?)\)/', $to_email)) {
            $validate_email = preg_replace('/(.*?)\((.*?)\)/', '$2', $to_email);
            if (filter_var($validate_email, FILTER_VALIDATE_EMAIL)) {
                $to_email = $validate_email;
            }
        }
        if (!isset($to_email)) {
            return;
        }
        $get_default = true;
        $mail_body = '';
        if (isset($form_options['ar_email_message']) and trim($form_options['ar_email_message']) != '') {
            if (!preg_match('/\[ARF_form_all_values\]/', $form_options['ar_email_message']))
                $get_default = false;
            $custom_message = true;
            $shortcodes = $armainhelper->get_shortcodes($form_options['ar_email_message'], $entry->form_id);
            $mail_body = $arfieldhelper->replace_shortcodes($form_options['ar_email_message'], $entry, $shortcodes);
            $mail_body = $arfieldhelper->arf_replace_shortcodes($mail_body, $entry, true);
        }

        $default = "";
        if ($get_default and ! $plain_text) {
            $default .= "<table cellspacing='0' style='font-size:12px;line-height:135%; border-bottom:{$style_settings->arffieldborderwidthsetting} solid #{$style_settings->border_color};'><tbody>";
            $bg_color = " style='background-color:#{$style_settings->bg_color};'";
            $bg_color_alt = " style='background-color:#{$style_settings->arfbgactivecolorsetting};'";
        }
        $odd = true;
        $attachments = array();

        foreach ($values as $value) {            
            $value = apply_filters('arf_brfore_send_mail_chnage_value', $value, $entry_id, $form_id);
            if ($value->field_type == 'file') {
                global $MdlDb, $wpdb;
                $file_options = $MdlDb->get_var($MdlDb->fields, array('id' => $value->field_id), 'field_options');
                $file_options = json_decode($file_options);
                if (isset($file_options->attach) && $file_options->attach == 1) {
                    $attach_file_values = explode('|', $value->entry_value);
                    foreach ($attach_file_values as $attach_file_val){
                        $field_id = $wpdb->get_row("select * from " . $wpdb->prefix . "postmeta where post_id = '" . $attach_file_val . "' AND meta_key = '_wp_attached_file'");
                        $file = $field_id->meta_value;                        
                        if ($file) {
                            $file = str_replace('thumbs/', '', $file);
                            $attachments[] = ABSPATH . "/$file";
                        }
                    }
                }
            }
            $val = apply_filters('arfemailvalue', maybe_unserialize($value->entry_value), $value, $entry);
            if ($value->field_type == 'file') {
				$icon_file_values = explode('|', $value->entry_value);
				foreach ($icon_file_values as $icon_file_val){
					$icon_val = apply_filters('arfemailvalue', maybe_unserialize($icon_file_val), $value, $entry);
					if (isset($icon_val) and $icon_val != '') {
						if (is_numeric($icon_val)) {
							$icon_val = $icon_val;
						} else {
							$icon_val = $icon_file_val;
						}
						$get_file_field_id = $wpdb->get_row("select * from " . $wpdb->prefix . "postmeta where post_id = '" . $icon_file_val . "' AND meta_key = '_wp_attached_file'");
						$file = $get_file_field_id->meta_value;
						
						if (file_exists(ABSPATH . $file)){
							$full_path = site_url() . "/" . str_replace('thumbs/', '', $file);
							$val .= "<a href='" . $full_path . "' target='_blank'><img src='" . $full_path . "' /></a>";
						} else {
							$val .= $arfieldhelper->get_file_name_link($icon_val, false);
						}
					}
				}
            }

            if ($value->field_type == 'checkbox' || $value->field_type == 'radio' || $value->field_type == 'select' || $value->field_type == 'arf_autocomplete' ) {
                if (isset($value->entry_value)) {
                    if (is_array(maybe_unserialize($value->entry_value))) {
                        $val = implode(', ', maybe_unserialize($value->entry_value));
                    } else {
                        $val = $value->entry_value;
                    }
                }
            }

            if ($value->field_type == 'select' || $value->field_type == 'checkbox' || $value->field_type == 'radio' || $value->field_type == 'arf_autocomplete' ) {
                global $wpdb,$MdlDb;
                $field_opts = $wpdb->get_row($wpdb->prepare("SELECT entry_value FROM " .$MdlDb->entry_metas." WHERE field_id='%d' AND entry_id='%d'", "-" . $value->field_id, $entry->id));

                if ($field_opts) {
                    $field_opts = maybe_unserialize($field_opts->entry_value);

                    if ($value->field_type == 'checkbox') {
                        if ($field_opts && count($field_opts) > 0) {
                            $temp_value = "";
                            foreach ($field_opts as $new_field_opt) {
                                $temp_value .= $new_field_opt['label'] . " (" . $new_field_opt['value'] . "), ";
                            }
                            $temp_value = trim($temp_value);
                            $val = rtrim($temp_value, ",");
                        }
                    } else {
                        if ($value->field_type == 'select' ) {
                            $field_id = $value->field_id;
                            $field_tmp = $wpdb->get_row($wpdb->prepare("SELECT * FROM " .$MdlDb->fields." WHERE id = '%d'",$field_id));
                            $field_tmp_opts = json_decode($field_tmp->field_options,true);
                            if( json_last_error() != JSON_ERROR_NONE ){
                                $field_tmp_opts = maybe_unserialize($field_tmp->field_options);
                            }
                            if ($field_tmp_opts['separate_value']) {
                                $label_field_id = ( $value->field_id * 100 );
                                $get_field_label = $wpdb->get_row($wpdb->prepare("SELECT entry_value FROM " .$MdlDb->entry_metas.' WHERE field_id = "-%d" and entry_id="%d"',$label_field_id,$value->entry_id));
                                $field_label = $get_field_label->entry_value;
                                if ($field_label != '') {
                                    $val = stripslashes($get_field_label->entry_value) . " (" . stripslashes($field_opts['value']) . ")";
                                } else {
                                    $val = $field_opts['label'] . " (" . $field_opts['value'] . ")";
                                }
                            } else {
                                $val = $field_opts['label'] . " (" . $field_opts['value'] . ")";
                            }
                        } else {
                            $val = $field_opts['label'] . " (" . $field_opts['value'] . ")";
                        }
                    }
                }
            }
            if ($value->field_type == 'textarea' and ! $plain_text)
                $val = str_replace(array("\r\n", "\r", "\n"), ' <br/>', $val);
            if (is_array($val))
                $val = implode(', ', $val);
            if ($get_default and $plain_text) {
                $default .= $value->field_name . ': ' . $val . "\r\n\r\n";
            } else if ($get_default) {
                $row_style = "valign='top' style='text-align:left;color:#{$style_settings->text_color};padding:7px 9px;border-top:{$style_settings->arffieldborderwidthsetting} solid #{$style_settings->border_color}'";
                $label_name = '';
                if ($value->field_name != ''){
                    $label_name = $value->field_name;
                }
                $default .= "<tr" . (($odd) ? $bg_color : $bg_color_alt) . "><th $row_style>$label_name</th><td $row_style>$val</td></tr>";
                $odd = ($odd) ? false : true;
            }
            if ( isset($email_fields) and is_array($email_fields)) {
                if (in_array($value->field_id, $email_fields)) {
                    $val = explode(',', $val);
                    if (is_array($val)) {
                        foreach ($val as $v) {
                            $v = trim($v);
                            if (is_email($v))
                                $to_emails[] = $v;
                        }
                    }else if (is_email($val))
                        $to_emails[] = $val;
                }
            }
        }        
        if ($get_default and ! $plain_text)
            $default .= "</tbody></table>";
        if (isset($form_options['ar_email_subject']) and $form_options['ar_email_subject'] != '') {
            $shortcodes = $armainhelper->get_shortcodes($form_options['ar_email_subject'], $form_id);
            $subject = $arfieldhelper->replace_shortcodes($form_options['ar_email_subject'], $entry, $shortcodes);
            $subject = $arfieldhelper->arf_replace_shortcodes($subject, $entry, true);
        } else {
            $subject = sprintf(addslashes(__('%1$s Form submitted on %2$s', 'ARForms')), stripslashes($form->name), $arfblogname);
        }
        $subject = trim($subject);
        if ($reply_to) {

            $reply_to = $arfieldhelper->arf_replace_shortcodes($reply_to, $entry, true);
        }

        $user_nreplyto = (isset($form_options['ar_user_nreplyto_email'])) ? $form_options['ar_user_nreplyto_email'] : $arfsettings->reply_to;

        if (isset($user_nreplyto) and $user_nreplyto != '') {
            $user_nreplyto = $arfieldhelper->arf_replace_shortcodes($user_nreplyto, $entry, true);
        }

        if ($get_default and $custom_message) {
            $mail_body = str_replace('[ARF_form_all_values]', $default, $mail_body);
        }
        else if ($get_default)
            $mail_body = $default;
        $data = maybe_unserialize($entry->description);
        $browser_info = $this->getBrowser($data['browser']);
        $browser_detail = $browser_info['name'] . ' (Version: ' . $browser_info['version'] . ')';
        if (preg_match('/\[ARF_form_ipaddress\]/', $mail_body))
            $mail_body = str_replace('[ARF_form_ipaddress]', $entry->ip_address, $mail_body);
        if (preg_match('/\[ARF_form_browsername\]/', $mail_body))
            $mail_body = str_replace('[ARF_form_browsername]', $browser_detail, $mail_body);
        if (preg_match('/\[ARF_form_referer\]/', $mail_body))
            $mail_body = str_replace('[ARF_form_referer]', $data['http_referrer'], $mail_body);
        if (preg_match('/\[ARF_form_entryid\]/', $mail_body))
            $mail_body = str_replace('[ARF_form_entryid]', $entry->id, $mail_body);
        if (preg_match('/\[ARF_form_added_date_time\]/', $mail_body)) {
            $wp_date_format = get_option('date_format');
            $wp_time_format = get_option('time_format');
            $mail_body = str_replace('[ARF_form_added_date_time]', date($wp_date_format . " " . $wp_time_format, strtotime($entry->created_date)), $mail_body);
        }
        $arf_current_user = wp_get_current_user();
        if (preg_match('/\[ARF_current_userid\]/', $mail_body)) {
            $mail_body = str_replace('[ARF_current_userid]', $arf_current_user->ID, $mail_body);
        }
        if (preg_match('/\[ARF_current_username\]/', $mail_body)) {
            $mail_body = str_replace('[ARF_current_username]', $arf_current_user->user_login, $mail_body);
        }
        if (preg_match('/\[ARF_current_useremail\]/', $mail_body)) {
            $mail_body = str_replace('[ARF_current_useremail]', $arf_current_user->user_email, $mail_body);
        }
        if (preg_match('/\[ARF_page_url\]/', $mail_body)) {
            $entry_desc = maybe_unserialize($entry->description);
            $mail_body = str_replace('[ARF_page_url]', $entry_desc['page_url'], $mail_body);   
        }
        $mail_body = apply_filters('arfbefore_autoresponse_send_mail_body', $mail_body, $entry_id, $form_id);
        $attachments = apply_filters('arfautoresponderattachment', $attachments, $form, array('entry' => $entry));
        $mail_body = nl2br($mail_body);
        
        $arnotifymodel->send_notification_email_user($to_email, $subject, $mail_body, $reply_to, $reply_to_name, $plain_text, $attachments, false, false, false, false, $user_nreplyto);
        return $to_email;
    }

    function arfchangesmtpsetting($phpmailer) {
        global $arfsettings;


        if (isset($arfsettings->is_smtp_authentication) && $arfsettings->is_smtp_authentication == '1') {
            if (!isset($arfsettings->smtp_host) || empty($arfsettings->smtp_host) || !isset($arfsettings->smtp_username) || empty($arfsettings->smtp_username) || !isset($arfsettings->smtp_password) || empty($arfsettings->smtp_password)) {
                return;
            }
        } else {
            if (!isset($arfsettings->smtp_host) || empty($arfsettings->smtp_host)) {
                return;
            }
        }

        if (!isset($arfsettings->smtp_port) || empty($arfsettings->smtp_port))
            $arfsettings->smtp_port = 25;


        $phpmailer->IsSMTP();


        $phpmailer->Host = $arfsettings->smtp_host;
        $phpmailer->Port = $arfsettings->smtp_port;


        if (isset($arfsettings->is_smtp_authentication) && $arfsettings->is_smtp_authentication == '1') {
            $phpmailer->SMTPAuth = true;
        }else{
            $phpmailer->SMTPAuth = false;
        }

        $phpmailer->Username = $arfsettings->smtp_username;
        $phpmailer->Password = $arfsettings->smtp_password;
        if (isset($arfsettings->smtp_encryption) and $arfsettings->smtp_encryption != '' and $arfsettings->smtp_encryption != 'none') {
            $phpmailer->SMTPSecure = $arfsettings->smtp_encryption;
        }
    }

    function getBrowser($user_agent) {
        $u_agent = $user_agent;
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version = "";
        $ub = '';

        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }


        if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif (preg_match('/Firefox/i', $u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (preg_match('/Chrome/i', $u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (preg_match('/Safari/i', $u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (preg_match('/Opera/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (preg_match('/Netscape/i', $u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        }


        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
                ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {

        }


        $i = count($matches['browser']);
        if ($i != 1) {

            if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
                $version = $matches['version'][0];
            } else {
                $version = $matches['version'][1];
            }
        } else {
            $version = $matches['version'][0];
        }


        if ($version == null || $version == "") {
            $version = "?";
        }

        return array(
            'userAgent' => $u_agent,
            'name' => $bname,
            'version' => $version,
            'platform' => $platform,
            'pattern' => $pattern
        );
    }

    function arf_set_conditional_mail_sent($rec_url, $form, $entry_id) {
        global $wpdb,$MdlDb, $arfrecordmeta;

        $options = $form->options;

        if (isset($options['arf_conditional_enable_mail']) && $options['arf_conditional_enable_mail'] == '1' && !empty($entry_id)) {
            $entry_ids = array($entry_id);
            $values = $arfrecordmeta->getAll("it.field_id != 0 and it.entry_id in (" . implode(',', $entry_ids) . ")", " ORDER BY fi.id");

            if (isset($options['arf_conditional_mail_rules']) && !empty($options['arf_conditional_mail_rules'])) {
                foreach ($options['arf_conditional_mail_rules'] as $key => $rules_value) {

                    if (count($values) > 0) {
                        foreach ($values as $value) {
                            if ($rules_value['field_id_mail'] == $value->field_id) {
                                $mail_send_value = $value->entry_value;
                                break;
                            }
                        }
                    }

                    $conditional_logic_field_type = $rules_value['field_type_mail'];

                    $conditional_logic_value1 = isset($mail_send_value) ? $mail_send_value : '';

                    $conditional_logic_value1 = trim(strtolower($conditional_logic_value1));

                    $conditional_logic_value2 = trim(strtolower($rules_value['value_mail']));

                    $conditional_logic_operator = $rules_value['operator_mail'];

                    if ($this->arf_conditional_mail_send_calculate_rule($conditional_logic_value1, $conditional_logic_value2, $conditional_logic_operator, $conditional_logic_field_type)) {
                        $rec_url = $rules_value['send_mail_field'];
                        break;
                    }
                }
            }
        }
        return $rec_url;
    }

    function arf_conditional_mail_send_calculate_rule($value1, $value2, $operator, $field_type) {
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

    function arf_autoreponder_entry($entry_id, $form_id) {
        global $wpdb, $MdlDb, $fid, $check_itemid, $form_responder_fname, $form_responder_lname, $form_responderemail, $email, $fname, $lname, $arfrecordmeta;

        if ($check_itemid == "") {

            $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM " .$MdlDb->forms." WHERE id=%d", $fid));

            /* arf_dev_flag if entry prevented from user signup plugin*/
            if(!isset($result[0])){
                return;
            }
            $result = $result[0];

            $autoresponder_fname = $result->autoresponder_fname;


            $autoresponder_lname = $result->autoresponder_lname;


            $autoresponder_email = $result->autoresponder_email;

            $autoresponder_email = apply_filters('arf_check_autoresponder_email_outside',$autoresponder_email,$fid,$entry_id);


            $entry_ids = array($entry_id);

            $values = $arfrecordmeta->getAll("it.field_id != 0 and it.entry_id in (" . implode(',', $entry_ids) . ")", " ORDER BY fi.id");


            foreach ($values as $key => $entry_details) {
                if ($autoresponder_fname == $entry_details->field_id) {


                    $form_responder_fname = $result->autoresponder_fname;


                    $fname = trim($entry_details->entry_value);
                }


                if ($autoresponder_lname == $entry_details->field_id) {


                    $form_responder_lname = $result->autoresponder_lname;


                    $lname = trim($entry_details->entry_value);
                }


                if ($autoresponder_email == $entry_details->field_id) {


                    $form_responderemail = $result->autoresponder_email;


                    $email = trim($entry_details->entry_value);
                }
            }


            $check_condition_on_subscription = true;

            $form_options = maybe_unserialize($result->options);


            /* condition on subscription */
            if (isset($form_options['conditional_subscription']) && $form_options['conditional_subscription'] == 1) {
                $check_condition_on_subscription = apply_filters('arf_check_condition_on_subscription', $form_options, $entry_id);
            }

            $is_mapped_outside = apply_filters('arf_send_autoresponder_data',false,$fid,$entry_id);

            if ( ($check_condition_on_subscription && ($autoresponder_fname != '' || $autoresponder_lname != '') && $autoresponder_email != '' && ($form_responder_fname != '' || $form_responder_lname != '') && $form_responderemail != '') || $is_mapped_outside) {

                $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " .$MdlDb->ar." WHERE frm_id = %d", $fid), 'ARRAY_A');

                $ar_aweber = maybe_unserialize($res[0]['aweber']);
                $ar_mailchimp = maybe_unserialize($res[0]['mailchimp']);
                $ar_madmimi = maybe_unserialize($res[0]['madmimi']);
                $ar_getresponse = maybe_unserialize($res[0]['getresponse']);
                $ar_gvo = maybe_unserialize($res[0]['gvo']);
                $ar_ebizac = maybe_unserialize($res[0]['ebizac']);
                $ar_icontact = maybe_unserialize($res[0]['icontact']);
                $ar_constant = maybe_unserialize($res[0]['constant_contact']);

                $type = maybe_unserialize(get_option('arf_ar_type'));
                if ($ar_aweber['enable'] == 1 && $type['aweber_type'] == 0 && $type['aweber_type'] != 2) {
                    require(AUTORESPONDER_PATH . '/aweber/addsubscriber.php');
                } else if ($ar_aweber['enable'] == 1 && $type['aweber_type'] == 1 && $type['aweber_type'] != 2) {
                    require(AUTORESPONDER_PATH . '/aweber/addsubscriber_api.php');
                }


                if ($ar_mailchimp['enable'] == 1 && $ar_mailchimp['type'] == 1 && $type['mailchimp_type'] != 2) {
                    //require(AUTORESPONDER_PATH . '/mailchimp/inc/store-address.php');
                    do_action('arf_add_mailchimp_subscriber',$ar_mailchimp,$fname,$lname,$email,$fid);
                } else if ($ar_mailchimp['enable'] == 1 && $ar_mailchimp['type'] == 0 && $type['mailchimp_type'] != 2) {
                    require(AUTORESPONDER_PATH . '/mailchimp/mailchimp_webform.php');
                }

                if ($ar_madmimi['enable'] == 1 && $ar_madmimi['type'] == 1 && $type['madmimi_type'] != 2) {
                    require(AUTORESPONDER_PATH . '/madmimi/madmimi_send_contact.php');
                } else if ($ar_madmimi['enable'] == 1 && $ar_madmimi['type'] == 0 && $type['madmimi_type'] != 2) {
                    require(AUTORESPONDER_PATH . '/madmimi/madmimi_webform.php');
                }

                if ($ar_getresponse['enable'] == 1 && $ar_getresponse['type'] == 1 && $type['getresponse_type'] != 2) {

                    require(AUTORESPONDER_PATH . '/getresponse/getresponse.php');
                } else if ($ar_getresponse['enable'] == 1 && $ar_getresponse['type'] == 0 && $type['getresponse_type'] != 2) {

                    require(AUTORESPONDER_PATH . '/getresponse/getresponse_webform.php');
                }


                if ($ar_gvo['enable'] == 1 && $type['gvo_type'] != 2) {

                    require(AUTORESPONDER_PATH . '/gvo/gvo.php');
                }

                if ($ar_ebizac['enable'] == 1 && $type['ebizac_type'] != 2) {

                    require(AUTORESPONDER_PATH . '/ebizac/ebizac.php');
                }


                if ($ar_icontact['enable'] == 1 && $ar_icontact['type'] == 1 && $type['icontact_type'] != 2) {

                    require(AUTORESPONDER_PATH . '/icontact/icontact.php');
                } else if ($ar_icontact['enable'] == 1 && $ar_icontact['type'] == 0 && $type['icontact_type'] != 2) {

                    require(AUTORESPONDER_PATH . '/icontact/icontact_webform.php');
                }


                if ($ar_constant['enable'] == 1 && $ar_constant['type'] == 1 && $type['constant_type'] != 2) {

                    require(AUTORESPONDER_PATH . '/constant_contact/addOrUpdateContact.php');
                } else if ($ar_constant['enable'] == 1 && $ar_constant['type'] == 0 && $type['constant_type'] != 2) {

                    require(AUTORESPONDER_PATH . '/constant_contact/constant_contact_webform.php');
                }


                $check_itemid = $entry_id;
            }
        }
    }

}

?>