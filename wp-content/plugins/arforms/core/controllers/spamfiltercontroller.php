<?php 


class spamfiltercontroller {
		const nonce_action = 'form_spam_filter';
		const nonce_name = 'arm_nonce_check';
		const nonce_start_time = 'form_filter_st';
		const nonce_keyboard_press = 'form_filter_kp';
		
		var $nonce_fields;
		
	 function __construct() {	
		add_filter('is_to_validate_spam_filter', array($this, 'arf_check_spam_filter_fields'),10,2);
		add_shortcode('arf_spam_filters', array($this, 'arf_spam_filters_func'));

		add_filter('arf_reset_built_in_captcha',array($this,'arf_reset_built_in_captcha_key'),10,2);
	}

	function arf_reset_built_in_captcha_key($return, $post_val){
		if( empty($post_val) ){
			$return['recaptcha_key'] = '';
		} else {
			global $armainhelper;
	        $form_id = $post_val['form_id'];
	        $frm_id = $post_val['form_random_key'];
	        $possible_letters = '23456789bcdfghjkmnpqrstvwxyz';
	        $random_dots = 0;
	        $random_lines = 20;

	        $session_var = '';
	        $i = 0;
	        while ($i < 8) {
	            $session_var .= substr($possible_letters, mt_rand(0, strlen($possible_letters) - 1), 1);
	            $i++;
	        }
	        $_SESSION['ARF_FILTER_INPUT'][$frm_id] = $session_var;
	        $return['recaptcha_key'] = base64_encode($session_var.'~|~'.$form_id.'~|~'.$frm_id);
		}
		return $return;
	}
	
  function arf_check_spam_filter_fields($validate = TRUE,$form_key = ''){
	$is_form_key = $arf_is_removed_field = TRUE;

	/* Return false if session is blank. */
	if( !isset($_SESSION['ARF_FILTER_INPUT']) && isset($_SESSION['ARF_VALIDATE_SCRIPT']) && $_SESSION['ARF_VALIDATE_SCRIPT'] == TRUE ){
		$arf_is_removed_field = FALSE;
	}

	/* Return false if form key not found */
	if( $form_key == '' || (isset($_SESSION['ARF_FILTER_INPUT']) && !array_key_exists($form_key, $_SESSION['ARF_FILTER_INPUT'])) ){
		$is_form_key = FALSE;
	}
	/* Get dynamic generated field */
	$field_name = isset($_SESSION['ARF_FILTER_INPUT'][$form_key]) ? $_SESSION['ARF_FILTER_INPUT'][$form_key] : '';
	
	if( isset($_REQUEST[$field_name]) ){ 
		$field_value = $_REQUEST[$field_name];
		$arf_is_dynamic_field = TRUE;
		/* Check if dynamic generated field value. Return if modified */
		if( $field_value != "" || !empty($field_value) || $field_value != NULL ){
			$arf_is_dynamic_field = FALSE;
		}
	} else {
		$arf_is_dynamic_field = FALSE;
	}

	$is_removed_field_exists = FALSE;
	/* Get dynamically removed field. Return if found */
	if( isset($_REQUEST['arf_filter_input']) || isset($_POST['arf_filter_input']) || isset($_GET['arf_filter_input']) ){
		$arf_is_removed_field = FALSE;
		$is_removed_field_exists = TRUE;
	}

	/* Remove old keys from stored session */
	unset($_SESSION['ARF_FILTER_INPUT'][$form_key]);

	/* Check if Script is Executed. Bypass if script is not executed due to suPHP extension or blocked iframe */
	if( !isset($_SESSION['ARF_VALIDATE_SCRIPT']) || $_SESSION['ARF_VALIDATE_SCRIPT'] == FALSE ){
		$arf_is_dynamic_field = TRUE;
		$is_form_key = TRUE;
	}

	$validateNonce = $validateReferer = $in_time = $is_user_keyboard = FALSE;
	if (isset($_REQUEST) && isset($_REQUEST[self::nonce_name])) {
		$referer = $this->validateReferer();
		if ($referer['pass'] === TRUE && $referer['hasReferrer'] === TRUE) {
			$validateReferer = TRUE;
		}
		/* Check Form Submission Time. */
		$in_time = $this->validateTimedFormSubmission();
		/* Check Keyboard Use */
		$is_user_keyboard = $this->validateUsedKeyboard();
	}
	$validateNonce = TRUE;
	
	if ($validateNonce && $validateReferer && $in_time && $is_user_keyboard && $is_form_key && $arf_is_dynamic_field && $arf_is_removed_field )	{
		$validate = TRUE;
	} else if( !$is_user_keyboard && $validateNonce && $validateReferer && $in_time && $is_form_key && $arf_is_dynamic_field && $arf_is_removed_field ){
		$validate = TRUE;
	} else {
		$validate = FALSE;
	}
	return $validate;
    }
    
    function validateReferer()
    {
	if (isset($_SERVER['HTTPS'])) {
		$protocol = "https://";
	} else {
		$protocol = "http://";
	}
	$absurl = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
	$absurlParsed = parse_url($absurl);
	$result["pass"] = false;
	$result["hasReferrer"] = false;
	$httpReferer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
	if (isset($httpReferer)) {
		$refererParsed = parse_url($httpReferer);
		if (isset($refererParsed['host'])) {
			$result["hasReferrer"] = true;
			$absUrlRegex = '/' . strtolower($absurlParsed['host']) . '/';
			$isRefererValid = preg_match($absUrlRegex, strtolower($refererParsed['host']));
			if ($isRefererValid == 1) {
				$result["pass"] = true;
			}
		} else {
			$result["status"] = "Absolute URL: " . $absurl . " Referer: " . $httpReferer;
		}
	} else {
		$result["status"] = "Absolute URL: " . $absurl . " Referer: " . $httpReferer;
	}
	return $result;
    }
    
    function validateTimedFormSubmission($formContents=array())
    {
	$in_time = FALSE;
	if(empty($formContents[self::nonce_start_time])) {
		$formContents[self::nonce_start_time] = isset($_REQUEST[self::nonce_start_time]) ? $_REQUEST[self::nonce_start_time] : '';
	}
	if(isset($formContents[self::nonce_start_time]))
	{
		$displayTime = $formContents[self::nonce_start_time] - 14921;
		$submitTime = time();
		$fillOutTime = $submitTime - $displayTime;
		/* Less than 3 seconds */
		if ($fillOutTime < 3) {
			$in_time = FALSE;
		} else {
			$in_time = TRUE;
		}
	}
	return $in_time;
    }
    function validateUsedKeyboard($formContents=array())
    {
	$is_user_keyboard = FALSE;
	if (empty($formContents[self::nonce_keyboard_press])) {
	    $formContents[self::nonce_keyboard_press] = isset($_REQUEST[self::nonce_keyboard_press]) ? $_REQUEST[self::nonce_keyboard_press] : '';
	}
	if (isset($formContents[self::nonce_keyboard_press])) {
		if (is_numeric($formContents[self::nonce_keyboard_press]) !== false) {
			$is_user_keyboard = TRUE;
		}
	}
	return $is_user_keyboard;
    }
     
    function arf_spam_filters_func($atts, $content = "")
    {
	    $defaults = array(
		    'var' => '',
	    );
	    /* Extract Shortcode Attributes */
	    $opts = shortcode_atts( $defaults, $atts, 'spam_filters' );
	    extract( $opts );

	    $content .= $this->add_form_fields();

	    return do_shortcode($content);
    }
    
    function add_form_fields()
    {
	$this->nonce_fields = '<input type="hidden" data-jqvalidate="false" class="kpress" value="" />';
	$this->nonce_fields .= '<input type="hidden" data-jqvalidate="false" class="stime" value="'. (time()+14921) .'" />';
	$this->nonce_fields .= '<input type="hidden" data-jqvalidate="false" data-id="nonce_start_time" class="nonce_start_time" value="'.self::nonce_start_time.'" />';
	$this->nonce_fields .= '<input type="hidden" data-jqvalidate="false" data-id="nonce_keyboard_press" class="nonce_keyboard_press" value="'.self::nonce_keyboard_press.'" />';
	$this->nonce_fields .= '<input type="hidden" data-jqvalidate="false" data-id="'.self::nonce_name.'" name="'.self::nonce_name.'" value="'.wp_create_nonce(self::nonce_action).'" />';
	return $this->nonce_fields;
    }

}