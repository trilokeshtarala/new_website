<?php
if (!defined('WPINC')) {
    die;
}

class ARForms_VCExtendArp {

    protected static $instance = null;
    var $is_arforms_vdextend = 0;

    public function __construct() {
        add_action('init', array($this, 'ARFintegrateWithVC'));
        add_action('init', array($this, 'ArfCallmyFunction'));
    }

    public static function arp_get_instance() {
        if ($this->instance == null) {
            $this->instance = new self;
        }

        return $this->instance;
    }

    public function ARFintegrateWithVC() {
        if (function_exists('vc_map')) {
            global $arfversion, $armainhelper;
	    
            if (version_compare(WPB_VC_VERSION, '4.3.4', '>=')) {


                if (isset($_REQUEST['vc_action']) && !empty($_REQUEST['vc_action'])) {
		   
                    wp_register_style('arfbootstrap-css', ARFURL . '/bootstrap/css/bootstrap.css', array(), $arfversion);
                    wp_enqueue_style('arfbootstrap-css');

                    wp_register_script('arfbootstrap-js', ARFURL . '/bootstrap/js/bootstrap.min.js', array('jquery'), $arfversion);
                    wp_enqueue_script('arfbootstrap-js');
		    
                    wp_register_script('jquery-validation', ARFURL . '/bootstrap/js/jqBootstrapValidation.js', array('jquery'), $arfversion);
                    wp_enqueue_script('jquery-validation');

		            wp_register_style('arf-fontawesome-css', ARFURL . '/css/font-awesome.min.css', array(), $arfversion);
		            wp_enqueue_script('arf-fontawesome-css');

                    wp_enqueue_style( 'wp-color-picker' );
                    wp_enqueue_script( 'wp-color-picker');
                }
            }


            vc_map(array(
                'name' => addslashes(esc_html__('ARForms', 'ARForms')),
                'description' => addslashes(esc_html__('Exclusive Wordpress Form Builder Plugin', 'ARForms')),
                'base' => 'ARForms_popup',
                'category' => addslashes(esc_html__('Content', 'ARForms')),
                'class' => '',
                'controls' => 'full',
                'admin_enqueue_css' => array(ARFURL . '/core/vc/arforms_vc.css'),
                'front_enqueue_css' => ARFURL . '/core/vc/arforms_vc.css',
                'front_enqueue_js' => ARFURL . '/core/vc/arforms_vc.js',
                'icon' => 'arforms_vc_icon',
                'params' => array(
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'id',
                        'value' => '',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'shortcode_type',
                        'value' => 'normal',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => 'ARForms_Popup_Shortode',
                        'heading' => false,
                        'param_name' => 'type',
                        'value' => 'link',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'position',
                        'value' => 'top',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'desc',
                        'value' => 'Click here to open Form',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'width',
                        'value' => '800',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'height',
                        'value' => 'auto',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'angle',
                        'value' => '0',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'bgcolor',
                        'value' => '#8ccf7a',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'txtcolor',
                        'value' => '#ffffff',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ), array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'on_inactivity',
                        'value' => '1',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'on_scroll',
                        'value' => '10',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ), array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'on_delay',
                        'value' => '0',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ), array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'overlay',
                        'value' => '0.6',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ), array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'is_close_link',
                        'value' => 'yes',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ), array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'modal_bgcolor',
                        'value' => '#000000',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),

                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'inactive_min',
                        'value' => '0',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'is_fullscreen',
                        'value' => 'no',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'modaleffect',
                        'value' => 'no_animation',
                        'description' => addslashes('&nbsp;'),
                        'admin_label' => true
                    ),

                )
            ));
        }
    }

    public function ArfCallmyFunction() {
        if (function_exists('vc_add_shortcode_param')) {
            vc_add_shortcode_param('ARForms_Popup_Shortode', array($this, 'arforms_param_html'), ARFURL . '/core/vc/arforms_vc.js');
        }
    }

    public function arforms_param_html($settings, $value) {

        global $armainhelper, $arformhelper;

        echo '<input  id="Arf_param_id" type="hidden" name="id" value="" class="wpb_vc_param_value">';

        echo '<input id="' . esc_attr($settings['param_name']) . '" name="' . esc_attr($settings['param_name']) . '" class=" ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_arfield" type="hidden" value="' . esc_attr($value) . '" />';

	
	    
        if ($this->is_arforms_vdextend == 0) {
            $this->is_arforms_vdextend = 1;
            ?>


            <style type="text/css">
                @font-face {
                    font-family: 'Asap-Regular';
                    src: url('<?php echo ARFURL; ?>/fonts/Asap-Regular.eot');
                    src: url('<?php echo ARFURL; ?>/fonts/asap-regular-webfont.woff2') format('woff2'), 
                         url('<?php echo ARFURL; ?>/fonts/Asap-Regular.woff') format('woff'), 
                         url('<?php echo ARFURL; ?>/fonts/Asap-Regular.ttf') format('truetype'), 
                         url('<?php echo ARFURL; ?>/fonts/Asap-Regular.svg#Asap-Regular') format('svg'), 
                         url('<?php echo ARFURL; ?>/fonts/Asap-Regular.eot?#iefix') format('embedded-opentype');
                    font-weight: normal;
                    font-style: normal;
                }

                @font-face {
                    font-family: 'Asap-Medium';
                    src: url('<?php echo ARFURL; ?>/fonts/Asap-Medium.eot');
                    src: url('<?php echo ARFURL; ?>/fonts/asap-medium-webfont.woff2') format('woff2'), 
                         url('<?php echo ARFURL; ?>/fonts/Asap-Medium.woff') format('woff'), 
                         url('<?php echo ARFURL; ?>/fonts/Asap-Medium.ttf') format('truetype'), 
                         url('<?php echo ARFURL; ?>/fonts/Asap-Medium.svg#Asap-Medium') format('svg'), 
                         url('<?php echo ARFURL; ?>/fonts/Asap-Medium.eot?#iefix') format('embedded-opentype');
                    font-weight: normal;
                    font-style: normal;
                }


                .arfmodal_vc .btn-group.bootstrap-select 
                {
                    text-align:left;
                }

                .arfmodal_vc .btn-group .btn.dropdown-toggle,.arfmodal_vc .btn-group .arfbtn.dropdown-toggle {
                    border: 1px solid #CCCCCC;
                    background-color:#FFFFFF;
                    background-image:none;
                    box-shadow:none;
                    -webkit-box-shadow:none;
                    -moz-box-shadow:none;
                    -o-box-shadow:none;
                    outline:0 !important;
                    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -moz-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -o-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                }
                .arfmodal_vc .btn-group.open .btn.dropdown-toggle,.arfmodal_vc .btn-group.open .arfbtn.dropdown-toggle {
                    border:solid 1px #CCCCCC;
                    background-color:#FFFFFF;
                    border-bottom-color:transparent;
                    box-shadow:none;
                    -webkit-box-shadow:none;
                    -moz-box-shadow:none;
                    -o-box-shadow:none;
                    outline:0 !important;
                    outline-style:none;
                    border-bottom-left-radius:0px;
                    border-bottom-right-radius:0px;
                    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -moz-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -o-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                }
                .arfmodal_vc .btn-group.dropup.open .btn.dropdown-toggle, .arfmodal_vc .btn-group.dropup.open .arfbtn.dropdown-toggle {
                    border:solid 1px #CCCCCC;
                    background-color:#FFFFFF;
                    border-top-color:transparent;
                    box-shadow:none;
                    -webkit-box-shadow:none;
                    -moz-box-shadow:none;
                    -o-box-shadow:none;
                    outline:0 !important;
                    outline-style:none;
                    border-top-left-radius:0px;
                    border-top-right-radius:0px;
                    border-bottom-left-radius:6px;
                    border-bottom-right-radius:6px;
                }
                .arfmodal_vc .btn-group .arfdropdown-menu {
                    margin:0;
                }
                .arfmodal_vc .btn-group.open .arfdropdown-menu {
                    border:solid 1px #CCCCCC;
                    box-shadow:none;
                    -webkit-box-shadow:none;
                    -moz-box-shadow:none;
                    -o-box-shadow:none;
                    border-top:none;
                    margin:0;
                    margin-top:-1px;
                    border-top-left-radius:0px;
                    border-top-right-radius:0px;	
                }
                .arfmodal_vc .btn-group.dropup.open .arfdropdown-menu {
                    border-top:solid 1px #CCCCCC;
                    box-shadow:none;
                    -webkit-box-shadow:none;
                    -moz-box-shadow:none;
                    -o-box-shadow:none;
                    border-bottom:none;
                    margin:0;
                    margin-bottom:-1px;
                    border-bottom-left-radius:0px;
                    border-bottom-right-radius:0px;
                    border-top-left-radius:6px;
                    border-top-right-radius:6px;	
                }
                .arfmodal_vc .btn-group.dropup.open .arfdropdown-menu .arfdropdown-menu.inner {
                    border-top:none;
                }
                .arfmodal_vc .btn-group.open ul.arfdropdown-menu {
                    border:none;
                }

                .arfmodal_vc .arfdropdown-menu > li {
                    margin:0px;
                }

                .arfmodal_vc .arfdropdown-menu > li > a {
                    padding: 6px 12px;
                    text-decoration:none;
                }

                .arfmodal_vc .arfdropdown-menu > li:hover > a {
                    background:#1BBAE1;
                }

                .arfmodal_vc .bootstrap-select.btn-group, 
                .arfmodal_vc .bootstrap-select.btn-group[class*="span"] {
                    margin-bottom:5px;
                }

                .arfmodal_vc ul, .wrap ol {
                    margin:0;
                    padding:0;
                }

                .arfmodal_vc form {
                    margin:0;
                }	

                .arfmodal_vc label {
                    display:inline;
                    margin-left:5px;
                }

                .arfnewmodalclose
                {
                    font-size: 15px;
                    font-weight: bold;
                    height: 19px;
                    position: absolute;
                    right: 3px;
                    top:5px;
                    width: 19px;
                    cursor:pointer;
                    color:#D1D6E5;
                } 
                #arfinsertform
                {
                    text-align:center;
                }
                .newform_modal_title
                {
                    font-size:24px;
                    font-family:'Asap-Medium', Arial, Helvetica, Verdana, sans-serif;
                    color:#d1d6e5;
                    margin-top:14px;
                }

                #arfcontinuebtn
                {
                    background:#1bbae1;
                    font-family:'Asap-Medium', Arial, Helvetica, Verdana, sans-serif;
                    font-size:18px;
                    cursor:pointer;
                    color:#ffffff;
                    margin-top:10px;
                    padding-top:18px;	
                    height:42px;
                }

                .arfmodal_vc .txtmodal1 
                {
                    height:36px;
                    border:1px solid #cccccc;
                    -o-border-radius:3px;
                    -moz-border-radius:3px;
                    -webkit-border-radius:3px;
                    border-radius:3px;
                    color:#353942;
                    background:#FFFFFF;
                    font-family:'Asap-Regular', Arial, Helvetica, Verdana, sans-serif;
                    font-size:14px;
                    margin:0px;
                    letter-spacing:0.8px;
                    padding:0px 10px 0 10px;
                    width:360px;
                    outline:none;
                    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -webkit-box-shadow: 0px 0px 1px rgba(0, 0, 0, 0), 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -moz-box-shadow: 0px 0px 1px rgba(0, 0, 0, 0), 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -o-box-shadow: 0px 0px 1px rgba(0, 0, 0, 0), 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -webkit-box-sizing: content-box;
                    -o-box-sizing: content-box;
                    -moz-box-sizing: content-box;
                    box-sizing: content-box;
                }
                .arfmodal_vc .txtmodal1:focus
                {
                    border:1px solid #1BBAE1;
                    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -webkit-box-shadow: 0px 0px 1px rgba(0, 0, 0, 0), 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -moz-box-shadow: 0px 0px 1px rgba(0, 0, 0, 0), 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -o-box-shadow: 0px 0px 1px rgba(0, 0, 0, 0), 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    transition:none;
                    -webkit-transition:none;
                    -o-transition:none;
                    -moz-transition:none;
                }
                .newmodal_field_title
                {
                    margin:20px 0 10px 0;
                    font-family:'Asap-Medium', Arial, Helvetica, Verdana, sans-serif;

                    font-size:14px;
                    color:#353942;
                }
                .arfmodal_vc input[class="rdomodal"] {
                    display:none;
                }

                .arfmodal_vc input[class="rdomodal"] + label {
                    color:#333333;
                    font-size:14px;
                    font-family:'Asap-Regular', Arial, Helvetica, Verdana, sans-serif;
                }

                .arfmodal_vc input[class="rdomodal"] + label span {
                    display:inline-block;
                    width:19px;
                    height:19px;
                    margin:-1px 4px 0 0;
                    vertical-align:middle;
                    background:url(<?php echo ARFURL; ?>/images/dark-radio-green.png) -37px top no-repeat;
                    cursor:pointer;
                }

                .arfmodal_vc input[class="rdomodal"]:checked + label span
                {
                    background:url(<?php echo ARFURL; ?>/images/dark-radio-green.png) -56px top no-repeat;
                }
                .arfmodal_vcfields
                {

                    display:table;
                    text-align: center;
                    margin-top:10px;
                    width:100%;

                    float:left !important;
                    width:250px !important;
                    height:80px !important;
                }
                .arfmodal_vcfields .arfmodal_vcfield_left
                {
                    display:table-cell;
                    text-align:right;
                    width:45%;
                    padding-right:20px;	
                    font-family:'Asap-Medium', Arial, Helvetica, Verdana, sans-serif;
                    font-weight:normal;
                    font-size:14px;
                    color:#353942;
                }
                .arfmodal_vcfields .arfmodal_vcfield_right
                {
                    display:table-cell;
                    text-align:left;
                }
                .arfmodal_vc .arf_px
                {
                    font-family:'Asap-Regular', Arial, Helvetica, Verdana, sans-serif;
                    font-size:12px;
                    color:#353942;	
                }


                body.rtl .arfnewmodalclose
                {
                    right:auto;
                    left:3px;
                }
                body.rtl .arfmodal_vcfields .arfmodal_vcfield_left
                {
                    text-align:left;
                }
                body.rtl .arfmodal_vcfields .arfmodal_vcfield_right
                {
                    text-align:right;
                    padding-right:20px;	

                    float:left !important;
                }
                body.rtl .arfmodal_vc .bootstrap-select.btn-group .arfbtn .filter-option
                {
                    top:5px;
                    right:8px;
                    left:auto;
                }

                body.rtl .arfmodal_vc .bootstrap-select.btn-group .arfbtn .caret
                {
                    left:8px;
                    right:auto;
                }
                body.rtl .arfmodal_vc .btn-group.open .arfdropdown-menu {
                    text-align:right;
                }

                .arf_coloroption_sub{
                    border: 4px solid #dcdfe4;
                    border-radius: 2px;
                    -webkit-border-radius: 2px;
                    -moz-border-radius: 2px;
                    -o-border-radius: 2px;
                    cursor: pointer;
                    height: 22px;
                    width: 47px;
                    margin-left:22px;
                    margin-top:5px;
                }

                .arf_coloroption{
                    cursor: pointer;
                    height: 22px;
                    width: 47px;
                }

                .arf_coloroption_subarrow_bg{
                    background: none repeat scroll 0 0 #dcdfe4;
                    height: 8px;
                    margin-left: 39px;
                    margin-top: -8px;
                    text-align: center;
                    vertical-align: middle;
                    width: 8px;
                }

                .arf_coloroption_subarrow{
                    background: <?php echo "url(" . ARFURL . "/images/colpickarrow.png) no-repeat center center"; ?>;
                    height: 3px;
                    padding-left: 5px;
                    padding-top: 6px;
                    width: 5px;
                }

                .colpick_hex{
                    z-index:999999;
                }
                .arfmodal_vc.fade{ opacity:1; }

                .arf_label{
                    float:left;
                    margin-bottom:5px;
                }
		.arfinsertform_modal_container .arf_custom_radio_div{
		    margin-top:0px;
		}
		.arfinsertform_modal_container .arf_radio_wrapper{
		    margin-right:0px;
		}
		.arf_js_colorpicker{
		    z-index: 100000 !important;
		}
		
            </style>        

            <div class='arfinsertform_modal_container arf_popup_content_container' style="overflow: visible;">
		
                <div class="main_div_container" style="padding:0px;margin-left: 25px;">
                    <div class="select_form arfmarginb20">
                        <label><?php echo addslashes(esc_html__('Select a form to insert into page', 'ARForms')); ?>&nbsp;<span class="newmodal_required" style="color:#000000; vertical-align:top;">*</span></label>
                        <div class="selectbox">
                            <?php $arformhelper->forms_dropdown_new('arfaddformid_vc_popup', '', 'Select form') ?>

                        </div>
                    </div>
                    <input type="hidden" id="arf_shortcode_type" value="normal" name="shortcode_type"  class="wpb_vc_param_value" />
                    <div class="select_type arfmarginb20">
                        <label><?php echo addslashes(esc_html__('How you want to include this form into page?', 'ARForms')); ?></label>
                        <div class="radio_selection">
                            <div class="arf_radio_wrapper">
                                <div class="arf_custom_radio_div">
                                    <div class="arf_custom_radio_wrapper">
                                        <input type="radio" class="" checked="checked" name="shortcode_type" value="normal" id="shortcode_type_normal_vc" onclick="showarfpopupfieldlist();"/>
                                        <svg width="18px" height="18px">
                                        <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                        <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                        </svg>
                                    </div>
                                
                                    <span style="margin-left: 8px;float: left;">
                                        <label for="shortcode_type_normal_vc" <?php if (is_rtl()) {
                					   echo 'style="float:right; margin-right:167px;"';
                				        } ?>><?php echo addslashes(esc_html__('Internal', 'ARForms')); ?></label>
                					</span>
                                </div>
                            </div>
                            <div class="arf_radio_wrapper">
                                <div class="arf_custom_radio_div">
                                    <div class="arf_custom_radio_wrapper">
                                        <input type="radio" class=" arf_submit_entries" name="shortcode_type" value="popup" id="shortcode_type_popup_vc" onclick="showarfpopupfieldlist();" />
                                        <svg width="18px" height="18px">
                                        <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                        <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                        </svg>
                                    </div>
                                
                                    <span style="margin-left: 8px;float: left;">
                                        <label for="shortcode_type_popup_vc" <?php if (is_rtl()) {
                    					echo 'style="float:right; margin-right:167px;"';
                    				    } else {
                    					echo 'style="width:170px;"';
                    				    } ?>><?php echo addslashes(esc_html__('Modal(popup) window', 'ARForms')); ?></label>
                                    </span>
                                </div>
                            </div>

                        </div>

                    </div>


                </div>

           

            <div id="arfinsertform" class="arfmodal_vc fade">

                <input type="hidden" id="form_title_i" value="" />
                <div class="newform_modal_fields" style="margin-bottom:30px; clear:both;">
                    <div id="show_link_type_vc" style="display:none; margin-top:15px;">   

                        <div class="arfmodal_vcfields arfsecond_div" id="normal_link_type"> 	
                            <label class="arf_label"><?php echo addslashes(esc_html__('Modal Trigger Type', 'ARForms')); ?></label>

                            <div class="sltmodal" style="float:none; font-size:15px; <?php
                                 if (is_rtl()) {
                                     echo 'text-align:right;';
                                 } else {
                                     echo 'text-align:left;';
                                 }
                                 ?>">

                                <input type="hidden" name="type" id="link_type_vc" onChange="javascript:changetopposition(this.value); arf_set_link_type_data(this.value)" value="link" class="wpb_vc_param_value"/>

                                <dl class="arf_selectbox" data-name="link_type_vc" data-id="link_type_vc" style="width:235px;">
                                    <dt>
                                    <span style="float:left;"><?php echo addslashes(esc_html__('On Click', 'ARForms')); ?></span>
                                    <input value="onclick" style="display:none;" class="" type="text">
                                    <i class="arfa arfa-caret-down arfa-lg"></i>
                                    </dt>
                                    <dd>
                                        <ul style="display:none;width:250px;" data-id="link_type_vc">
                                            
                                            <li class="lblnotetitle arf_selectbox_option" data-value="onclick" data-label="<?php echo addslashes(esc_html__('On Click', 'ARForms')); ?>"><?php echo addslashes(esc_html__('On Click', 'ARForms')); ?></li>
                                            <li class="lblnotetitle arf_selectbox_option" data-value="onload" data-label="<?php echo addslashes(esc_html__('On Page Load', 'ARForms')); ?>"><?php echo addslashes(esc_html__('On Page Load', 'ARForms')); ?></li>
                                            <li class="lblnotetitle arf_selectbox_option" data-value="scroll" data-label="<?php echo addslashes(esc_html__('On Page Scroll', 'ARForms')); ?>"><?php echo addslashes(esc_html__('On Page Scroll', 'ARForms')); ?></li>

                                            <li class="lblnotetitle arf_selectbox_option" data-value="timer" data-label="<?php echo addslashes(esc_html__('On Timer(Scheduled)', 'ARForms')); ?>"><?php echo addslashes(esc_html__('On Timer(Scheduled)', 'ARForms')); ?></li>
                                            <li class="lblnotetitle arf_selectbox_option" data-value="on_exit" data-label="<?php echo addslashes(esc_html__('On Exit(Exit Intent)', 'ARForms')); ?>"><?php echo addslashes(esc_html__('On Exit(Exist Intent)', 'ARForms')); ?></li>
                                            <li class="lblnotetitle arf_selectbox_option" data-value="on_idle" data-label="<?php echo addslashes(esc_html__('On Idle', 'ARForms')); ?>"><?php echo addslashes(esc_html__('On Idle', 'ARForms')); ?></li>
                                        </ul>
                                    </dd>
                                </dl>  
                            </div>

                        </div>

                        <!-- -->
                        <div class="arfmodal_vcfields arfsecond_div" id="list_of_onclick_vc" style="width: 100% !important">
                            <label style="text-align: left;display:block;"><?php echo addslashes(esc_html__('Click Types', 'ARForms')); ?></label>     
                            <div class="radio_selection ">
                                   
                               <div class="arf_radio_wrapper">
                                   <div class="arf_custom_radio_div">
                                       <div class="arf_custom_radio_wrapper">
                                           <input type="radio" class="arf_custom_radio" checked="checked" name="onclick_type" value="link" id="onclick_type_link" />
                                           <svg width="18px" height="18px">
                                           <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                           <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                           </svg>
                                       </div>
                                       <span>
                                           <label for="onclick_type_link" <?php if (is_rtl()) { echo 'style="float:right; margin-right:167px;"';}?>><?php echo addslashes(esc_html__('Link', 'ARForms')); ?></label>
                                       </span>
                                   </div>
                               </div>

                               <div class="arf_radio_wrapper">
                                   <div class="arf_custom_radio_div">
                                       <div class="arf_custom_radio_wrapper">
                                           <input type="radio" class="arf_custom_radio arf_submit_entries" name="onclick_type" value="button" id="onclick_type_button" />
                                           <svg width="18px" height="18px">
                                           <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                           <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                           </svg>
                                       </div>
                                       <span>
                                           <label for="onclick_type_button" <?php if (is_rtl()) { echo 'style="float:right; margin-right:167px;"';}?>><?php echo addslashes(esc_html__('Button', 'ARForms')); ?></label>
                                       </span>
                                   </div>
                               </div>

                               <div class="arf_radio_wrapper">
                                   <div class="arf_custom_radio_div">
                                       <div class="arf_custom_radio_wrapper">
                                           <input type="radio" class="arf_custom_radio" name="onclick_type" value="sticky" id="onclick_type_sticky" />
                                           <svg width="18px" height="18px">
                                           <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                           <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                           </svg>
                                       </div>
                                       <span>
                                           <label for="onclick_type_sticky" <?php if (is_rtl()) { echo 'style="float:right; margin-right:167px;"';}?>><?php echo addslashes(esc_html__('Sticky', 'ARForms')); ?></label>
                                       </span>
                                   </div>
                               </div>

                                <div class="arf_radio_wrapper">
                                   <div class="arf_custom_radio_div">
                                       <div class="arf_custom_radio_wrapper">
                                           <input type="radio" class="arf_custom_radio" name="onclick_type" value="fly" id="onclick_type_fly" />
                                           <svg width="18px" height="18px">
                                           <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                           <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                           </svg>
                                       </div>
                                       <span>
                                           <label for="onclick_type_fly" <?php if (is_rtl()) { echo 'style="float:right; margin-right:167px;"';}?>><?php echo addslashes(esc_html__('Fly(Sidebar)', 'ARForms')); ?></label>
                                       </span>
                                   </div>
                               </div>

                            </div>
                         </div>


                        <!-- -->

                        <div class="arfmodal_vcfields arfsecond_div" id="shortcode_caption_vc" style="width: 225px;float:left;"> 	
                            <label class="arf_label"><?php echo addslashes(esc_html__('Caption :', 'ARForms')); ?></label>
                            <div class="arfmodal_vcfield_right" style="float:left;">
                                <input type="text" name="desc" id="short_caption" value="Click here to open Form" class="wpb_vc_param_value txtstandardnew" style="width:230px;" />
                            </div>          
                        </div>

                        <div class="arfmodal_vcfields arfsecond_div" id="is_scroll_vc" style="display:none;width:450px !important;"> 	
                            <label style="float:left;text-align:left"><?php echo addslashes(esc_html__('Open popup when user scroll % of page after page load', 'ARForms')); ?></label>
                            <div class="arfmodal_vcfield_right" style="float:left;width:250px;">
                                <input type="text" name="on_scroll" id="open_scroll" value="10" class="wpb_vc_param_value txtstandardnew" style="width:70px;" /> %
                                <span style="font-style:italic;">&nbsp;<?php echo addslashes(esc_html__('(eg. 100% - end of page)', 'ARForms')); ?></span>
                            </div>          
                        </div>


                        <div class="arfmodal_vcfields arfsecond_div" id="is_delay_vc" style="display:none;"> 	
                            <label class="arf_label"><?php echo addslashes(esc_html__('Open popup after page load', 'ARForms')); ?></label>
                            <div class="arfmodal_vcfield_right" style="float:left;width:250px;">
                                <input type="text" name="on_delay" id="open_delay" value="0" class="wpb_vc_param_value txtstandardnew" style="width:70px;" />
                                <span style="font-size:12px;"><?php echo addslashes(esc_html__('(in seconds)', 'ARForms')); ?></span>
                            </div>          
                        </div>



                        <div class="arfmodal_vcfields arfsecond_div" id="is_sticky_vc" style="display:none;"> 	
                            <label class="arf_label"><?php echo addslashes(esc_html__('Link Position?', 'ARForms')); ?></label>
                            <div class="arfmodal_vcfield_right" style="float:left;width:250px;">
                            <div class="sltmodal" style="float:none; font-size:15px;<?php
                                 if (is_rtl()) {
                                     echo 'text-align:right;';
                                 } else {
                                     echo 'text-align:left;';
                                 }
                                 ?>">
                                <input type="hidden" name="position" id="link_position_vc" class="wpb_vc_param_value" value="top"/>
                                <dl class="arf_selectbox" data-name="link_position_vc" data-id="link_position_vc" style="width:235px;">
                                    <dt>
                                    <span style="float:left;"><?php echo addslashes(esc_html__('Top', 'ARForms')); ?></span>
                                    <input value="top" style="display:none;" class="" type="text">
                                    <i class="arfa arfa-caret-down arfa-lg"></i>
                                    </dt>
                                    <dd>
                                    <ul style="display:none;width:251px;" data-id="link_position_vc">
                                        <li class="lblnotetitle arf_selectbox_option" data-value="top" data-label="<?php echo addslashes(esc_html__('Top', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Top', 'ARForms')); ?></li>
                                        <li class="lblnotetitle arf_selectbox_option" data-value="bottom" data-label="<?php echo addslashes(esc_html__('Bottom', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Bottom', 'ARForms')); ?></li>
                                        <li class="lblnotetitle arf_selectbox_option" data-value="left" data-label="<?php echo addslashes(esc_html__('Left', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Left', 'ARForms')); ?></li>
                                        <li class="lblnotetitle arf_selectbox_option" data-value="right" data-label="<?php echo addslashes(esc_html__('Right', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Right', 'ARForms')); ?></li>                                        
                                    </ul>
                                    </dd>
                                </dl>
                            </div>
                            </div>          
                        </div>

                        <div class="arfmodal_vcfields arfsecond_div" id="overlay_div_vc" style="display:none;clear:both;"> 	
                            <label class="arf_label"><?php echo addslashes(esc_html__('Background Overlay :', 'ARForms')); ?></label>
                            <div class="arfmodal_vcfield_right" style="float:left;">
                                <div class="sltmodal" style="float:none; font-size:15px;display:inline-block; float: left; margin-top:5px; <?php
                                     if (is_rtl()) {
                                         echo 'text-align:right;';
                                     } else {
                                         echo 'text-align:left;';
                                     }
                                     ?>">
                                    <input type="hidden" name="overlay" class="wpb_vc_param_value" id="overlay" value="0.6"/>
                                    <dl class="arf_selectbox" data-name="overlay" data-id="overlay" style="width:85px;">
                                        <dt>
                                        <span style="float:left;"><?php echo addslashes(esc_html__('60%', 'ARForms')); ?></span>
                                        <input value="0.6" style="display:none;" class="" type="text">
                                        <i class="arfa arfa-caret-down arfa-lg"></i>
                                        </dt>
                                        <dd>
                                            <ul style="display:none;width:100px;" data-id="overlay">
                                                <li class="lblnotetitle arf_selectbox_option" data-value="0" data-label="<?php echo addslashes(esc_html__('0 (None)', 'ARForms')); ?>"><?php echo addslashes(esc_html__('0 (None)', 'ARForms')); ?></li>
                                                <li class="lblnotetitle arf_selectbox_option" data-value="0.1" data-label="<?php echo addslashes(esc_html__('10%', 'ARForms')); ?>"><?php echo addslashes(esc_html__('10%', 'ARForms')); ?></li>
                                                <li class="lblnotetitle arf_selectbox_option" data-value="0.2" data-label="<?php echo addslashes(esc_html__('20%', 'ARForms')); ?>"><?php echo addslashes(esc_html__('20%', 'ARForms')); ?></li>
                                                <li class="lblnotetitle arf_selectbox_option" data-value="0.3" data-label="<?php echo addslashes(esc_html__('30%', 'ARForms')); ?>"><?php echo addslashes(esc_html__('30%', 'ARForms')); ?></li>
                                                <li class="lblnotetitle arf_selectbox_option" data-value="0.4" data-label="<?php echo addslashes(esc_html__('40%', 'ARForms')); ?>"><?php echo addslashes(esc_html__('40%', 'ARForms')); ?></li>
                                                <li class="lblnotetitle arf_selectbox_option" data-value="0.5" data-label="<?php echo addslashes(esc_html__('50%', 'ARForms')); ?>"><?php echo addslashes(esc_html__('50%', 'ARForms')); ?></li>
                                                <li class="lblnotetitle arf_selectbox_option" data-value="0.6" data-label="<?php echo addslashes(esc_html__('60%', 'ARForms')); ?>"><?php echo addslashes(esc_html__('60%', 'ARForms')); ?></li>
                                                <li class="lblnotetitle arf_selectbox_option" data-value="0.7" data-label="<?php echo addslashes(esc_html__('70%', 'ARForms')); ?>"><?php echo addslashes(esc_html__('70%', 'ARForms')); ?></li>
                                                <li class="lblnotetitle arf_selectbox_option" data-value="0.8" data-label="<?php echo addslashes(esc_html__('80%', 'ARForms')); ?>"><?php echo addslashes(esc_html__('80%', 'ARForms')); ?></li>
                                                <li class="lblnotetitle arf_selectbox_option" data-value="0.9" data-label="<?php echo addslashes(esc_html__('90%', 'ARForms')); ?>"><?php echo addslashes(esc_html__('90%', 'ARForms')); ?></li>
                                                <li class="lblnotetitle arf_selectbox_option" data-value="1" data-label="<?php echo addslashes(esc_html__('100%', 'ARForms')); ?>"><?php echo addslashes(esc_html__('100%', 'ARForms')); ?></li>
                                            </ul>
                                        </dd>
                                    </dl>
                                </div>

    				            <div style="display: inline-block; float:left;" class="arf_coloroption_sub" id="arf_vc_wp_picker_container">
                                    <div class="arf_coloroption" id="arf_vc_modal_bg_color"></div>
                                    <div class="arf_coloroption_subarrow_bg">
                                        <div class="arf_coloroption_subarrow"></div>
                                    </div>
                                    <div class="arfbgcolornote">(<?php echo addslashes(esc_html__('Background Color', 'ARForms')); ?>)</div>
                                    <input type="hidden" name="modal_bgcolor" id="arf_vc_modal_bg_color_input" class="txtmodal1 wpb_vc_param_value" value="#000000" />
                                </div>
                                
                            </div> 
                        </div>

                        <div class="arfmodal_vcfields arfsecond_div" id="is_close_link_div_vc" style="float:left;">
                            <label class="arf_label"><?php echo addslashes(esc_html__('Show Close Button :', 'ARForms')); ?></label>
                           
                            <div class="radio_selection" style="clear: both;">
                                <input type="hidden" id="is_close_link_value" value="yes" name="is_close_link"  class="wpb_vc_param_value" />
                                  
                                <div class="arf_radio_wrapper arfminwidth30">
                                    <div class="arf_custom_radio_div">
                                        <div class="arf_custom_radio_wrapper">
                                            <input onclick="is_close_link_change();" type="radio" checked="checked"  name="is_close_link_vc" value="yes" id="show_close_link_yes_vc" class="arf_custom_radio"  />
                                            <svg width="18px" height="18px"r>
                    					    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON;?>
                    					    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON;?>
                                            </svg>
                                        </div>
                                  
                                        <span style="margin-left: 8px;float: left;">
                                            <label for="show_close_link_yes_vc" <?php
                                                if (is_rtl()) {
                                                    echo 'style="float:right; margin-right:167px;"';
                                                }
                                                ?>>
                                                <span <?php
                                        if (is_rtl()) {
                                            echo 'style="margin-left:5px;"';
                                        }
                                        ?>></span><?php echo addslashes(esc_html__('Yes', 'ARForms')); ?>
                                            </label>
                                        </span>
                                    </div>
                                </div>

                                <div class="arf_radio_wrapper arfminwidth30">
                                    <div class="arf_custom_radio_div">
                                        <div class="arf_custom_radio_wrapper">
                                            <input onclick="is_close_link_change();" type="radio" name="is_close_link_vc" value="no" id="show_close_link_no_vc" class="arf_custom_radio" />
                                            <svg width="18px" height="18px"r>
                    					    <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                    					    <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                            </svg>
                                        </div>
                                    
                                        <span style="margin-left: 8px;float: left;">
                                            <label for="show_close_link_no_vc" <?php
                                                if (is_rtl()) {
                                                    echo 'style="float:right;"';
                                                }
                                                ?>>
                                                <span <?php
                                        if (is_rtl()) {
                                            echo 'style="margin-left:5px;"';
                                        }
                                        ?>></span><?php echo addslashes(esc_html__('No', 'ARForms')); ?>
                                            </label>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            
                        </div>



                         <div class="arfmodal_vcfields arfsecond_div" id="arfmodalbuttonstyles" style="display:none;min-height:95px;">
                            <label class="arf_label" style="margin-bottom:0px !important;"><?php echo addslashes(esc_html__('Button Colors :', 'ARForms')); ?></label>
                            <div class="arfmodal_vcfield_right" style="float:left;width:250px;">
                                <div style="display:inline">
				                <div style="display: inline-block; float:left;margin-left:0px;" id="arf_btn_bgcolor arf_vc_btn_bgcolor_picker" class="arf_coloroption_sub">
            					<div class="arf_coloroption" id='arf_vc_modal_btn_bg_color'></div>
            					<div class="arf_coloroption_subarrow_bg">
            					    <div class="arf_coloroption_subarrow"></div>
            					</div>
            					<div class="arfbgcolornote" style="width:90px !important;line-height: normal;">(<?php echo addslashes(esc_html__('Button Background', 'ARForms')); ?>)</div>
            				    <input type="hidden" name="bgcolor" id="arf_vc_modal_btn_bg_color_input" class="txtmodal1 wpb_vc_param_value" value="#808080" />
            				    </div>
				    
            				    <div style="display: inline-block; float:left;margin-left:30px !important;" id="arf_btn_txtcolor arf_vc_btn_txtcolor_picker"  class="arf_coloroption_sub">
            					<div class="arf_coloroption" id="arf_vc_modal_btn_txt_color"></div>
            					<div class="arf_coloroption_subarrow_bg">
            					    <div class="arf_coloroption_subarrow"></div>
            					</div>
            					<div class="arfbgcolornote">(<?php echo addslashes(esc_html__('Button Text', 'ARForms')); ?>)</div>
            				    <input type="hidden" name="txtcolor" id="arf_vc_modal_btn_txt_color_input" class="txtmodal1 wpb_vc_param_value" value="#FFFFFF" />
            				    </div>
                                    
                                </div>
                            </div>
                        </div> 

                        <div class="arfmodal_vcfields arfsecond_div" style="margin-bottom: 20px;"> 	
                            <label class="arf_label"><?php echo addslashes(esc_html__('Size :', 'ARForms')); ?></label>
                            <div class="arfmodal_vcfield_right" style="float:left;width:250px;">
                                <div style="display:inline;">
                                    <div class="height_setting" style="float: left;display: none;"><input type="text" onkeyup="if (jQuery(this).val() == 'auto') {
                                                            jQuery('span#arf_vc_height_px').hide();
                                                        } else {
                                                            jQuery('span#arf_vc_height_px').show();
                                                        }" class="wpb_vc_param_value txtstandardnew" name="height" id="modal_height" value="auto" style="width:70px;" />&nbsp;<span style="display:none;"  class="arf_px" id="arf_vc_height_px"><?php echo addslashes(esc_html__('px', 'ARForms')); ?> &nbsp; &nbsp;</span><br/><div style="margin-top: 4px;padding-left: 22px; width: 50px !important;line-height: normal !important;" class="arfbgcolornote"><?php echo addslashes(esc_html__('Height', 'ARForms')); ?></div></div>                    
                                    <div class="height_setting" style="float: left;">
                                        <input type="text" class="wpb_vc_param_value txtstandardnew" name="width" id="modal_width" value="800" style="width:70px;" />&nbsp;<span class="arf_px"><?php echo addslashes(esc_html__('px', 'ARForms')); ?></span><br/><div style="margin-top: 4px;padding-left: 22px;max-width:130px !important; line-height: normal !important;" class="arfbgcolornote"><?php echo addslashes(__('Width &nbsp; (Form width will be overwritten)', 'ARForms')); ?></div>
                                       
                                    </div>

                                </div>
                            </div>          
                        </div>
                        
                        <div class="arfmodal_vcfields arfsecond_div" id="button_angle_div_vc" style="float:left;"> 	
                            <label class="arf_label"><?php echo addslashes(esc_html__('Button angle :', 'ARForms')); ?></label>
                            <div class="arfmodal_vcfield_right" style="float:left;width:250px;">
                                <div class="sltmodal" style="float:none; font-size:15px;display:inline-block; <?php
                                     if (is_rtl()) {
                                         echo 'text-align:right;';
                                     } else {
                                         echo 'text-align:left;';
                                     }
                                     ?>">
                                    <input type="hidden" name="angle" class="wpb_vc_param_value" id="button_angle" value="0" onchange="changeflybutton();"/>
                                    <dl class="arf_selectbox" data-name="overlay" data-id="button_angle" style="width:85px;">
                                        <dt>
                                        <span style="float:left;"><?php echo addslashes(esc_html__('0', 'ARForms')); ?></span>
                                        <input value="0.6" style="display:none;" class="" type="text">
                                        <i class="arfa arfa-caret-down arfa-lg"></i>
                                        </dt>
                                        <dd>
                                            <ul style="display:none;width:101px;" data-id="button_angle">
                                                <li class="lblnotetitle arf_selectbox_option" data-value="0" data-label="<?php echo addslashes(esc_html__('0', 'ARForms')); ?>"><?php echo addslashes(esc_html__('0', 'ARForms')); ?></li>
                                                <li class="lblnotetitle arf_selectbox_option" data-value="90" data-label="<?php echo addslashes(esc_html__('90', 'ARForms')); ?>"><?php echo addslashes(esc_html__('90', 'ARForms')); ?></li>
                                                <li class="lblnotetitle arf_selectbox_option" data-value="-90" data-label="<?php echo addslashes(esc_html__('-90', 'ARForms')); ?>"><?php echo addslashes(esc_html__('-90', 'ARForms')); ?></li>
                                            </ul>
                                        </dd>
                                    </dl>  
                                </div>
                            </div>          
                        </div>


                        <div class="arfmodal_vcfields arfsecond_div" id="ideal_time">
                            <label class="arf_label"><?php echo addslashes(esc_html__('Show after user is inactive for', 'ARForms')); ?></label>     
                            <div class="arfmodal_vcfield_right" style="float:left;width:250px;">
                                <input type="text" name="inactive_min" id="inact_time" value="1" class="wpb_vc_param_value txtstandardnew" style="width:70px;" />
                                <span style="font-size:12px;"><?php echo addslashes(esc_html__('(in Minute)', 'ARForms')); ?></span>
                            </div> 
                        </div>

                        <div class="arfmodal_vcfields arfsecond_div" id="arf_full_screen_modal" style="text-align: left">
                            <label style="text-align: left;"><?php echo addslashes(esc_html__('Show Full Screen Popup :', 'ARForms')); ?></label>
                            <div class="radio_selection ">
                             <input type="hidden" class="arf_custom_radio wpb_vc_param_value" name="is_fullscreen" value="no" id="is_fullscreen_id"/>
                               <div class="arf_radio_wrapper arfminwidth30">
                                   <div class="arf_custom_radio_div">
                                       <div class="arf_custom_radio_wrapper">
                                           <input type="radio" class="arf_custom_radio wpb_vc_param_value" name="_is_fullscreen" value="yes" id="show_full_screen_yes" />
                                           <svg width="18px" height="18px">
                                           <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                           <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                           </svg>
                                       </div>
                                       <span>
                                           <label for="show_full_screen_yes" <?php if (is_rtl()) { echo 'style="float:right; margin-right:167px;"';}?>><?php echo addslashes(esc_html__('Yes', 'ARForms')); ?></label>
                                       </span>
                                   </div>
                               </div>
                               <div class="arf_radio_wrapper arfminwidth30">
                                   <div class="arf_custom_radio_div">
                                       <div class="arf_custom_radio_wrapper">
                                           <input type="radio" class="arf_custom_radio wpb_vc_param_value" checked="checked" name="_is_fullscreen" value="no" id="show_full_screen_no" />
                                           <svg width="18px" height="18px">
                                           <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                           <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                           </svg>
                                       </div>
                                       <span>
                                           <label for="show_full_screen_no" <?php if (is_rtl()) { echo 'style="float:right; margin-right:167px;"';}?>><?php echo addslashes(esc_html__('No', 'ARForms')); ?></label>
                                       </span>
                                   </div>
                               </div>
                            </div>                        

                        </div>

                        <div class="arfmodal_vcfields arfsecond_div" id="modal_effect_div">
                            <label class="arf_label"><?php echo addslashes(esc_html__('Animation Effect', 'ARForms')); ?></label>
                            <div class="dt_dl" id="" style="<?php
                                if (is_rtl()) {
                                    echo 'text-align:right;';
                                } else {
                                    echo 'text-align:left;';
                                }
                                ?>">
                                <input type="hidden" name="modaleffect" id="modal_effect" value="fade_in" onchange="" class="wpb_vc_param_value" />
                                <dl class="arf_selectbox" data-name="overlay" data-id="modal_effect" style="width:135px;">
                                    <dt>
                                    <span style="float:left;"><?php echo addslashes(esc_html__('Fade-in', 'ARForms')); ?></span>
                                    <input value="fade_in" style="display:none;" class="" type="text">
                                    <i class="arfa arfa-caret-down arfa-lg"></i>
                                    </dt>
                                    <dd>
                                        <ul style="display:none;width:151px;" data-id="modal_effect">
                                            
                                            <li class="lblnotetitle arf_selectbox_option" data-value="no_animation" data-label="<?php echo addslashes(esc_html__('No Animation', 'ARForms')); ?>"><?php echo addslashes(esc_html__('No Animation', 'ARForms')); ?></li>

                                            <li class="lblnotetitle arf_selectbox_option" data-value="fade_in" data-label="<?php echo addslashes(esc_html__('Fade in', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Fade in', 'ARForms')); ?></li>
                                            
                                            <li class="lblnotetitle arf_selectbox_option" data-value="slide_in_top" data-label="<?php echo addslashes(esc_html__('Slide In Top', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Slide In Top', 'ARForms')); ?></li>
                                            
                                            <li class="lblnotetitle arf_selectbox_option" data-value="slide_in_bottom" data-label="<?php echo addslashes(esc_html__('Slide In Bottom', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Slide In Bottom', 'ARForms')); ?></li>
                                            
                                            <li class="lblnotetitle arf_selectbox_option" data-value="slide_in_right" data-label="<?php echo addslashes(esc_html__('Slide In Right', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Slide In Right', 'ARForms')); ?></li>

                                            <li class="lblnotetitle arf_selectbox_option" data-value="slide_in_left" data-label="<?php echo addslashes(esc_html__('Slide In Left', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Slide In Left', 'ARForms')); ?></li>

                                            <li class="lblnotetitle arf_selectbox_option" data-value="zoom_in" data-label="<?php echo addslashes(esc_html__('Zoom In', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Zoom In', 'ARForms')); ?></li>
                                            

                                        </ul>
                                    </dd>
                                </dl>  
                            </div>                     
                        </div>
                </div>

                <div style="float:left; width:100%; height:25px;"> </div>
                <div style="clear:both;"></div>
                <script type="text/javascript" data-cfasync="false">
                    __LINK_POSITION_TOP = '<?php echo addslashes(esc_html__('Top', 'ARForms')); ?>';
                    __LINK_POSITION_BOTTOM = '<?php echo addslashes(esc_html__('Bottom', 'ARForms')); ?>';
                    __LINK_POSITION_LEFT = '<?php echo addslashes(esc_html__('Left', 'ARForms')); ?>';
                    __LINK_POSITION_RIGHT = '<?php echo addslashes(esc_html__('Right', 'ARForms')); ?>';
		    __BLANK_FORM_MSG  = '<?php echo addslashes(esc_html__('Please select a form', 'ARForms')) ?>';
                </script>
            </div>   

		 </div>
            <?php
        }
    }

}
?>