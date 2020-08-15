<?php
class arsettingmodel {
	var $menu;
	var $mu_menu; 
	var $custom_stylesheet;
	var $jquery_css;
	var $accordion_js;
	var $submit_value;
	var $admin_permission;
	var $pubkey;
	var $privkey;
	var $re_theme;
	var $re_lang;
	var $re_msg;
	var $current_tab;
	var $use_html;
	var $custom_style;
	var $load_style;
	var $email_to;
	var $reply_to_name;
	var $reply_to;
	var $ar_admin_reply_to_email;
	var $user_nreplyto_email;
	var $reply_to_email;
	var $form_submit_type;
	var $brand;
	var $success_msg;
	var $failed_msg;
	var $blank_msg;
	var $unique_msg;
	var $invalid_msg;
	var $smtp_server;
	var $smtp_host;
	var $smtp_port;
	var $smtp_username;
	var $smtp_password;
	var $smtp_encryption;
	var $affiliate_code;
	var $decimal_separator;
	var $arf_success_message_show_time;
	var $arf_file_uplod_dir_path;
	var $arf_css_character_set;
	var $is_smtp_authentication;
	var $arf_email_format;
	var $arf_pre_dup_msg;
	var $arfmainformloadjscss;
	
	function __construct() {
		$this->set_default_options();
	}

	function default_options() {
		return array (
			'menu' => 'ARForms',
			'mu_menu' => 0,
			'use_html' => true,
			'jquery_css' => false,
			'accordion_js' => false,
			'brand' => false,
			're_theme' => 'light',
			'success_msg' => 'Form is successfully submitted. Thank you!',
			'blank_msg' => 'This field cannot be blank.',
			'unique_msg' => 'This value must be unique.',
			'invalid_msg' => 'Problem in submission. Errors are marked below.',
			'failed_msg' => 'We\'re sorry. Form is not submitted successfully.',
			'submit_value' => 'Submit',
			'admin_permission' => 'You do not have permission to perform this action',
			'email_to' => '[admin_email]',
			'current_tab' => 'general_settings',
			'form_submit_type' => 1,
			'reply_to_name' => get_option('blogname'),
			'reply_to' => get_option('admin_email'),
			'ar_admin_reply_to_email' => get_option('admin_email'),
			'user_nreplyto_email' => get_option('admin_email'),
			'reply_to_email' => get_option('admin_email'),
			'smtp_server' => 'wordpress',
			'smtp_host' => '',
			'smtp_port' => '',
			'smtp_username' => '',
			'smtp_password' => '',
			'smtp_encryption' => 'none',
			'affiliate_code' => 'reputeinfosystems',
			'decimal_separator' => '.',
			'arf_file_uplod_dir_path' => 'wp-content/uploads/arforms/userfiles/',
			'arf_success_message_show_time' => 3,
			'arf_css_character_set' => '',
			'is_smtp_authentication' => 1,
			'arf_email_format' => 'html',
			'arf_pre_dup_msg' => 'You have already submitted this form before. You are not allowed to submit this form again.',
			'arfmainformloadjscss' => 0,
			'arf_load_js_css' =>array(),
		);
	}

	function checkdbstatus() {
		return "https://reputeinfosystems.net/arforms/wpinfo.php";
	}

	function set_default_options() {
		global $armainhelper;
		if(!isset($this->load_style)) {
			if(!isset($this->custom_style)) {
				$this->custom_style = true;
			}

			if(!isset($this->custom_stylesheet)) {
				$this->custom_stylesheet = false;
			}

			$this->load_style = ($this->custom_stylesheet) ? 'none' : 'all';
		}

		$settings = $this->default_options();

		foreach($settings as $setting => $default) {
			if(!isset($this->{$setting})) {
				$this->{$setting} = $default;
			}
			unset($setting);
			unset($default);
		}

		if(IS_WPMU and is_admin()) {
			$mu_menu = get_site_option('arfadminmenuname');
			if($mu_menu and !empty($mu_menu)) {
				$this->menu = $mu_menu;
				$this->mu_menu = 1;
			}
		}

		foreach($this as $k => $v) {
			$this->{$k} = stripslashes_deep($v);
			unset($k);
			unset($v);
		}
	}

	function update($params, $cur_tab = '') {
		global $wp_roles, $armainhelper;
		if($cur_tab == 'general_settings') {
			if($this->mu_menu) {
				update_site_option('arfadminmenuname', $this->menu);
			}
			else if($armainhelper->is_super_admin()) {
				update_site_option('arfadminmenuname', false);
			}

			update_option('arf_global_css', stripslashes_deep($params['arf_global_css']));

			$this->pubkey = trim($params['frm_pubkey']);
			$this->privkey = $params['frm_privkey'];
			$this->re_theme = $params['frm_re_theme'];
			$this->re_lang = $params['frm_re_lang'];

			$settings = $this->default_options();

			foreach($settings as $setting => $default) {
				if(isset($params['frm_'. $setting])) {
					$this->{$setting} = $params['frm_'. $setting];
				}
				unset($setting);
				unset($default);
			}

			$this->arf_success_message_show_time = isset($params['arf_success_message_show_time'])?$params['arf_success_message_show_time']:3;

			$this->jquery_css = isset($params['arfmainjquerycss']) ? $params['arfmainjquerycss'] : 0;
			$this->accordion_js = isset($params['arfmainformaccordianjs']) ? $params['arfmainformaccordianjs'] : 0;
			$this->form_submit_type = isset($params['arfmainformsubmittype']) ? $params['arfmainformsubmittype'] : 0;		
			$this->brand = isset($params['arfmainformbrand']) ? $params['arfmainformbrand'] : 0;
			$this->arf_css_character_set = isset($params['arf_css_character_set']) ? $params['arf_css_character_set'] : array();
			$this->affiliate_code = isset($params['affiliate_code']) ? $params['affiliate_code'] : 'reputeinfosystems';
			$this->arf_file_uplod_dir_path = isset($params['arf_file_uplod_dir_path']) ? $params['arf_file_uplod_dir_path'] : 'wp-content/uploads/arforms/userfiles';

			$this->decimal_separator = isset($params['decimal_separator']) ? $params['decimal_separator'] : '.';
			$this->is_smtp_authentication = isset($params['is_smtp_authentication']) ? $params['is_smtp_authentication'] : 0;
			$this->arf_email_format = isset($params['arf_email_format']) ? $params['arf_email_format'] : 'html';
			$this->arfmainformloadjscss = isset($params['frm_arfmainformloadjscss']) ? $params['frm_arfmainformloadjscss'] : 0;
			$this->arf_load_js_css = isset($params['arf_load_js_css']) ? $params['arf_load_js_css'] : array();
			$this->reply_to_email = isset($params['reply_to_email']) ? $params['reply_to_email'] : get_option('admin_email');

			$opt_data_from_outside = array();
			$opt_data_from_outside = apply_filters('arf_update_global_setting_outside',$opt_data_from_outside,$params);  

			if(is_array($opt_data_from_outside) && !empty($opt_data_from_outside) && count($opt_data_from_outside) > 0) {
				foreach ($opt_data_from_outside as $key => $optdata) {
					$this->$key = $optdata;
				}
			}

			$arfroles = $armainhelper->frm_capabilities();
			$roles = get_editable_roles();

			foreach($arfroles as $arfrole => $arfroledescription) {

				$this->$arfrole = isset($params[$arfrole]) ? $params[$arfrole] : 'administrator';

				foreach ($roles as $role => $details) {
					if($this->$arfrole == $role or ($this->$arfrole == 'editor' and $role == 'administrator') or ($this->$arfrole == 'author' and in_array($role, array('administrator', 'editor'))) or ($this->$arfrole == 'contributor' and in_array($role, array('administrator', 'editor', 'author'))) or $this->$arfrole == 'subscriber') {
						$wp_roles->add_cap( $role, $arfrole );	
					}
					else {
						$wp_roles->remove_cap( $role, $arfrole );
					}
				}
			}
		}

		foreach($this as $k => $v) {
			$this->{$k} = stripslashes_deep($v);
			unset($k);
			unset($v);
		}
	}

	function store($cur_tab = '') {

		global $arformcontroller;
		$value_store   =  array();
		$value_store_2 = array();

		$value_store   =  $arformcontroller->arfObjtoArray($this);
		$value_store_2 = apply_filters('arf_trim_values',$value_store);
		$value_store   = $arformcontroller->arfArraytoObj($value_store_2);

		$tempObj = new arsettingmodel();

		foreach($value_store as $k => $v) {
			$tempObj->$k = $v;
		}

		if($cur_tab == 'general_settings') {
			update_option('arf_options', $tempObj);
			delete_transient('arf_options');
			set_transient('arf_options', $tempObj);
		}
	}
}