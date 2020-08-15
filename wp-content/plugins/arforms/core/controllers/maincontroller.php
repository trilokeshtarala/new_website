<?php

class maincontroller {

    function __construct() {
        global $is_active_cornorstone;
        add_action('admin_menu', array($this, 'menu'));

        add_action('admin_head', array($this, 'menu_css'));

        add_filter('plugin_action_links_arforms/arforms.php', array($this, 'settings_link'), 10, 2);

        add_action('init', array($this, 'front_head'));

        /* we have move this action to `editor_init` instead of `init` there is not necessity to fire it at all places */
        add_action('before_arforms_editor_init', array($this, 'arf_update_auto_increment_after_install'), 11, 0);

        add_action('wp_head', array($this, 'arf_register_add_action'),1);

        add_action('wp_head', array($this, 'front_head_js'), 1, 0);

        add_action('wp_footer', array($this, 'footer_js'), 2, 0);

        add_action('admin_footer', array($this, 'wp_enqeue_footer_script'), 10);

        add_action('admin_init', array($this, 'admin_js'), 11);

        add_action('admin_enqueue_scripts', array($this, 'set_js'), 11);

        add_action('admin_enqueue_scripts', array($this, 'set_css'), 11);

        register_activation_hook(FORMPATH . '/arforms.php', array($this, 'install'));

        register_activation_hook(FORMPATH . '/arforms.php', array($this, 'arfforms_check_network_activation'));

        add_action('init', array($this, 'parse_standalone_request'));

        add_action('init', array($this, 'referer_session'), 1);

        add_shortcode('ARForms', array($this, 'get_form_shortcode'));

        add_filter('widget_text', array($this, 'widget_text_filter'), 9);

        add_shortcode('ARForms_popup', array($this, 'get_form_shortcode_popup'));

        add_filter('widget_text', array($this, 'widget_text_filter_popup'), 9);

        add_action('arfstandaloneroute', array($this, 'globalstandalone_route'), 10, 2);

        add_filter('upgrader_pre_install', array($this, 'arf_backup'), 10, 2);

        add_action('admin_init', array($this, 'upgrade_data'));

        add_action('admin_init', array($this, 'arfafterinstall'));

        add_action('init', array($this, 'arfafterinstall_front'));

        add_action('admin_init', array($this, 'arf_db_check'));

        add_filter('the_content', array($this, 'arf_modify_the_content'), 10000);

        add_filter('widget_text', array($this, 'arf_modify_the_content'), 10000);

        add_action('admin_head', array($this, 'arf_hide_update_notice_to_all_admin_users'), 10000);

        add_action('init', array($this, 'arf_export_form_data'));

        add_action('wp_head', array($this, 'arf_front_assets'), 1, 0);

        add_action('print_admin_scripts', array($this, 'arf_print_all_admin_scripts'));

        /* Add what's new popup */
        add_action('admin_footer', array($this, 'arf_add_new_version_release_note'), 1);
        add_action('wp_ajax_arf_dont_show_upgrade_notice', array($this, 'arf_dont_show_upgrade_notice'), 1);


        if( !function_exists('is_plugin_active') ){
            require(ABSPATH.'/wp-admin/includes/plugin.php');
        }
        /* Register Element for Cornerstone */
        if($is_active_cornorstone){
            add_action('cornerstone_register_elements', array($this, 'arforms_cs_register_element'));
            add_filter('cornerstone_icon_map', array($this, 'arforms_cs_icon_map'));
        }
        /* Register Element for Cornerstone */
        if( is_plugin_active('wp-rocket/wp-rocket.php') && !is_admin() ){
            add_filter('script_loader_tag', array($this, 'arf_prevent_rocket_loader_script'), 10, 2);
        }

        if( is_admin() ){
            add_filter('script_loader_tag', array($this,'arf_defer_attribute_to_js_for_editor'),10, 2);
        }
	
    	add_action('wp_ajax_arf_change_entries_separator',  array($this,'changes_export_entry_separator'));

        add_action('user_register',array($this,'arf_add_capabilities_to_new_user'));

        add_action('admin_init',array($this,'arf_plugin_add_suggested_privacy_content'),20);

        if( is_plugin_active('elementor/elementor.php') ){
            add_action('wp_print_scripts',array($this,'arf_dequeue_elementor_script'),100);
        }
	
	    add_filter( 'upload_mimes',array($this,'arf_custom_mime_types'));

        add_action('enqueue_block_editor_assets',array($this,'arf_enqueue_gutenberg_assets'));
    }
    
    function arf_enqueue_gutenberg_assets(){

        global $arfversion;

        wp_register_script('arforms_gutenberg_script',ARFURL.'/js/arf_gutenberg_script.js',array('wp-blocks','wp-element', 'wp-editor','wp-i18n'),$arfversion);

        wp_enqueue_script('arforms_gutenberg_script');

        wp_register_style('arforms_gutenberg_style',ARFURL.'/css/arf_gutenberg_style.css',array(), $arfversion);

        wp_enqueue_style('arforms_gutenberg_style');

    }
    function arf_custom_mime_types($mimes){

        $mimes['heic'] = 'image/heic';
        $mimes['heif'] = 'image/heif';
        return $mimes;
    }

    function arf_dequeue_elementor_script(){
        global $wp_scripts;
            
        if( isset($_GET['page']) && preg_match('/ARForms*/', $_GET['page']) ){
            
            wp_deregister_script('backbone-marionette');
            wp_dequeue_script('backbone-marionette');

            wp_deregister_script('backbone-radio');
            wp_dequeue_script('backbone-radio');            

            wp_deregister_script('elementor-common');
            wp_dequeue_script('elementor-common');            
            
            wp_deregister_script('editor-preview');
            wp_dequeue_script('editor-preview');

            wp_deregister_script('elementor-admin');
            wp_dequeue_script('elementor-admin');

            wp_deregister_script('wp-color-picker-alpha');
            wp_dequeue_script('wp-color-picker-alpha');
        
        }
    }

    function arf_plugin_add_suggested_privacy_content(){
        global $arfsettings;

        $content  = '<b>'.esc_html__('Who we are?','ARForms').'</b>';
        $content .= '<p>'. esc_html__('ARForms is a WordPress Premium Form Builder Plugin to create stylish and modern style form withing few clicks.','ARForms').' </p>';
        $content .= '<br/>';
        $content .= '<b>'.esc_html__('What Personal Data we collect and why we collect it.','ARForms').'</b>';
        $content .= '<p>'.esc_html__('ARForms stores ip address and country of visitor. However, ARForms provide an option to prevent storing visitor data.','ARForms').'</p>';
        $content .= '<p>'.esc_html__('ARForms will not store any personal data except user_id (only if user is logged in), ip address, country, browser user_agent, referrer only when submit the form.','ARForms').'</p>';
        $content .= '<p>'.esc_html__('We store this data to provide the analytics of the visitor and the user who submit the form.','ARForms').'</p>';
        $content .= '<p>'.esc_html__('ARForms will also store the all type of data (this may contain personal data as well as subscribe user to third party opt-in like MailChimp, Aweber, etc) in the database which plugin user has included in the form. These data are editable as well as removable from form entry section of ARForms.','ARForms').'</p>';

        if( function_exists('wp_add_privacy_policy_content') ){
            wp_add_privacy_policy_content('ARForms', $content);
        }
    }

    function arf_register_add_action(){
        ?>
        <script type="text/javascript" data-cfasync="false">
            if( typeof arf_add_action == 'undefined' ){
                
            arf_actions = [];
            function arf_add_action( action_name, callback, priority ) {
                if ( ! priority )  {
                    priority = 10;
                }
                
                if ( priority > 100 ) {
                    priority = 100;
                } 
                
                if ( priority < 0 ) {
                    priority = 0;
                } 
                
                if ( typeof arf_actions[action_name] == 'undefined' ) {
                    arf_actions[action_name] = [];
                }
                
                if ( typeof arf_actions[action_name][priority] == 'undefined' ) {
                    arf_actions[action_name][priority] = []
                }
                
                arf_actions[action_name][priority].push( callback );
            }
            function arf_do_action() {
                if ( arguments.length == 0 ) {
                    return;
                }
                
                var args_accepted = Array.prototype.slice.call(arguments),
                    action_name = args_accepted.shift(),
                    _this = this,
                    i,
                    ilen,
                    j,
                    jlen;
                
                if ( typeof arf_actions[action_name] == 'undefined' ) {
                    return;
                }
                
                for ( i = 0, ilen=100; i<=ilen; i++ ) {
                    if ( arf_actions[action_name][i] ) {
                        for ( j = 0, jlen=arf_actions[action_name][i].length; j<jlen; j++ ) {
                            if( typeof window[arf_actions[action_name][i][j]] != 'undefined' ){
                                window[arf_actions[action_name][i][j]](args_accepted);
                            }
                        }
                    }
                }
            }
            }
        </script>
      <?php
    }
    
    function arf_add_capabilities_to_new_user($user_id){
	   global $armainhelper;
    	if( $user_id == '' ){
    	    return;
    	}
    	if( user_can($user_id,'administrator')){

    	    global $current_user;
    	    $arfroles = $armainhelper->frm_capabilities();

    	    $userObj = new WP_User($user_id);
    	    foreach ($arfroles as $arfrole => $arfroledescription){
    		  $userObj->add_cap($arfrole);
    	    }
    	    unset($arfrole);
    	    unset($arfroles);
    	    unset($arfroledescription);
    	}
    }
    /**
     *       arf_dev_flag review below function's query
     * * */
    function arf_update_auto_increment_after_install() {
        global $wpdb, $MdlDb;
        $result_1 = $wpdb->get_results("SHOW TABLE STATUS LIKE '" . $MdlDb->forms . "'");
        if ($result_1[0]->Auto_increment < 100) {
            $wpdb->query("ALTER TABLE {$MdlDb->forms} AUTO_INCREMENT = 100");
        }
    }

    function arf_prevent_rocket_loader_script($tag, $handle) {
        
        $script = htmlspecialchars($tag);
        $pattern2 = '/\/(wp\-content\/plugins\/arforms)|(wp\-includes\/js)/';
        preg_match($pattern2,$script,$match_script);

        if( !isset($match_script[0]) || $match_script[0] == '' ){
            return $tag;
        }

        $pattern = '/(.*?)(data\-cfasync\=)(.*?)/';
        preg_match_all($pattern, $tag, $matches);
        if (!is_array($matches)) {
            return str_replace(' src', ' data-cfasync="false" src', $tag);
        } else if (!empty($matches) && !empty($matches[2]) && !empty($matches[2][0]) && strtolower(trim($matches[2][0])) != 'data-cfasync=') {
            return str_replace(' src', ' data-cfasync="false" src', $tag);
        } else if (!empty($matches) && empty($matches[2])) {
            return str_replace(' src', ' data-cfasync="false" src', $tag);
        } else {
            return $tag;
        }
    }

    function arf_defer_attribute_to_js_for_editor($tag, $handle){
        if( isset($_GET['page']) && $_GET['page'] == 'ARForms' && isset($_GET['arfaction']) && $_GET['arfaction'] != ''  ){
            $script = htmlspecialchars($tag);
            $pattern = '/\/(wp\-content\/plugins\/arforms)/';
            preg_match($pattern,$script,$match_script);

            if( !isset($match_script[0]) || $match_script[0] == '' ){
                return $tag;
            }

            return str_replace( ' src', ' defer="defer" src', $tag);
        } else {
            return $tag;
        }
    }

    function arf_get_remote_post_params($plugin_info = "") {
        global $wpdb, $arfversion;

        $action = "";
        $action = $plugin_info;

        if (!function_exists('get_plugins')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        $plugin_list = get_plugins();
        $site_url = home_url();
        $plugins = array();

        $active_plugins = get_option('active_plugins');

        foreach ($plugin_list as $key => $plugin) {
            $is_active = in_array($key, $active_plugins);


            if (strpos(strtolower($plugin["Title"]), "arforms") !== false) {
                $name = substr($key, 0, strpos($key, "/"));
                $plugins[] = array("name" => $name, "version" => $plugin["Version"], "is_active" => $is_active);
            }
        }
        $plugins = json_encode($plugins);


        $theme = wp_get_theme();
        $theme_name = $theme->get("Name");
        $theme_uri = $theme->get("ThemeURI");
        $theme_version = $theme->get("Version");
        $theme_author = $theme->get("Author");
        $theme_author_uri = $theme->get("AuthorURI");

        $im = is_multisite();
        $sortorder = get_option("arfSortOrder");

        $post = array("wp" => get_bloginfo("version"), "php" => phpversion(), "mysql" => $wpdb->db_version(), "plugins" => $plugins, "tn" => $theme_name, "tu" => $theme_uri, "tv" => $theme_version, "ta" => $theme_author, "tau" => $theme_author_uri, "im" => $im, "sortorder" => $sortorder);

        return $post;
    }

    public static function arfforms_check_network_activation($network_wide) {
        if (!$network_wide)
            return;

        deactivate_plugins(plugin_basename(__FILE__), TRUE, TRUE);

        header('Location: ' . network_admin_url('plugins.php?deactivate=true'));
        exit;
    }

    function arf_modify_the_content($content) {

        /* arf_dev_flag removed */
        $regex = '/<arfsubmit>(.*?)<\/arfsubmit>/is';
        $content = preg_replace_callback($regex, array($this, 'arf_the_content_remove_ptag'), $content);

        /* arf_dev_flag removed */
        $regex = '/<arffile>(.*?)<\/arffile>/is';
        $content = preg_replace_callback($regex, array($this, 'arf_the_content_remove_ptag'), $content);

        /* arf_dev_flag removed */
        $regex = '/<arfpassword>(.*?)<\/arfpassword>/is';
        $content = preg_replace_callback($regex, array($this, 'arf_the_content_remove_ptag'), $content);

        /* arf_dev_flag removed */
        $content = preg_replace("/<arfsubmit>|<\/arfsubmit>|<arffile>|<\/arffile>|<arfpassword>|<\/arfpassword>/is", '', $content);

        return $content;
    }

    function arf_the_content_remove_ptag($match) {
        $content = $match[1];

        $content = preg_replace('|<p>|', '', $content);

        $content = preg_replace('|</p>|', '', $content);

        $content = preg_replace('|<br />|', '', $content);

        return $content;
    }

    function arf_the_content_removeptag($matches) {
        return $matches[1];
    }

    function arf_the_content_removeemptyptag($matches) {
        return $matches[1];
    }

    function arfafterinstall() {
        global $arfsettings;
        $arfsettings = get_transient('arf_options');

        if (!is_object($arfsettings)) {
            if ($arfsettings) {
                $arfsettings = unserialize(serialize($arfsettings));
            } else {
                $arfsettings = get_option('arf_options');


                if (!is_object($arfsettings)) {
                    if ($arfsettings)
                        $arfsettings = unserialize(serialize($arfsettings));
                    else
                        $arfsettings = new arsettingmodel();
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
                $style_settings = unserialize(serialize($style_settings));
            } else {
                $style_settings = get_option('arfa_options');
                if (!is_object($style_settings)) {
                    if ($style_settings)
                        $style_settings = unserialize(serialize($style_settings));
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
                $style_settings = unserialize(serialize($style_settings));
            else
                $style_settings = new arstylemodel();
            update_option('arfa_options', $style_settings);
        }

        $style_settings->set_default_options();

        if (!is_admin() and $arfsettings->jquery_css)
            $arfdatepickerloaded = true;

        global $arfadvanceerrcolor;

        $arfadvanceerrcolor = array('white' => '#e9e9e9|#000000|#e9e9e9', 'black' => '#000000|#FFFFFF|#000000', 'darkred' => '#ed4040|#FFFFFF|#ed4040', 'blue' => '#D9EDF7|#31708F|#0561bf', 'pink' => '#F2DEDE|#A94442|#508b27', 'yellow' => '#FAEBCC|#8A6D3B|#af7a0c', 'red' => '#EF8A80|#FFFFFF|#1393c3', 'green' => '#6CCAC9|#FFFFFF|#7a37ac', 'color1' => '#6cca7b|#FFFFFF|#fb9900', 'color2' => '#c2b079|#FFFFFF|#ed40ae', 'color3' => '#f3b431|#FFFFFF|#ff6600', 'color4' => '#6d91d3|#FFFFFF|#0bb7b5', 'color5' => '#a466cc|#FFFFFF|#a79902');

        global $arfdefaulttemplate;
        $arfdefaulttemplate = array(
            '3' => array('name' => addslashes(esc_html__('Contact us', 'ARForms')),'theme'=> addslashes(esc_html__('standard', 'ARForms'))),
            '1' => array('name' => addslashes(esc_html__('Subscription Form', 'ARForms')),'theme'=> addslashes(esc_html__('standard', 'ARForms'))),
            '5' => array('name' => addslashes(esc_html__('Feedback Form', 'ARForms')),'theme'=> addslashes(esc_html__('standard', 'ARForms'))),
            '6' => array('name' => addslashes(esc_html__('RSVP Form', 'ARForms')),'theme'=> addslashes(esc_html__('standard', 'ARForms'))),
            '2' => array('name' => esc_html__('Registration Form', 'ARForms'),'theme'=> addslashes(esc_html__('standard', 'ARForms'))),
            '4' => array('name' => esc_html__('Survey Form', 'ARForms'),'theme'=> addslashes(esc_html__('standard', 'ARForms'))),
            '7' => array('name' => esc_html__('Job Application', 'ARForms'),'theme'=> addslashes(esc_html__('standard', 'ARForms'))),
            '8' => array('name' => addslashes(esc_html__('Donation Form', 'ARForms')),'theme'=> addslashes(esc_html__('material', 'ARForms'))),
            '9' => array('name' => addslashes(esc_html__('Request a Quote', 'ARForms')),'theme'=> addslashes(esc_html__('standard', 'ARForms'))),
            '10' => array('name' => addslashes(esc_html__('Memeber Login', 'ARForms')),'theme'=> addslashes(esc_html__('standard', 'ARForms'))),
            '11' => array('name' => addslashes(esc_html__('Order Form', 'ARForms')),'theme'=> addslashes(esc_html__('material', 'ARForms'))),
        );

        global $arfmsgtounlicop;
        $arfmsgtounlicop = "(";
        $arfmsgtounlicop .= "Un";
        $arfmsgtounlicop .= "lic";
        $arfmsgtounlicop .= "ens";
        $arfmsgtounlicop .= "ed";
        $arfmsgtounlicop .= ")";
    }

    function arfafterinstall_front() {
        if (!is_admin()) {
            global $arfsettings;
            $arfsettings = get_transient('arf_options');

            if (!is_object($arfsettings)) {
                if ($arfsettings) {
                    $arfsettings = unserialize(serialize($arfsettings));
                } else {
                    $arfsettings = get_option('arf_options');

                    if (!is_object($arfsettings)) {
                        if ($arfsettings)
                            $arfsettings = unserialize(serialize($arfsettings));
                        else
                            $arfsettings = new arsettingmodel();
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
                    $style_settings = unserialize(serialize($style_settings));
                } else {
                    $style_settings = get_option('arfa_options');
                    if (!is_object($style_settings)) {
                        if ($style_settings)
                            $style_settings = unserialize(serialize($style_settings));
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
                    $style_settings = unserialize(serialize($style_settings));
                else
                    $style_settings = new arstylemodel();
                update_option('arfa_options', $style_settings);
            }

            $style_settings->set_default_options();

            if (!is_admin() and $arfsettings->jquery_css)
                $arfdatepickerloaded = true;

            global $arfadvanceerrcolor;

            $arfadvanceerrcolor = array('white' => '#e9e9e9|#000000|#e9e9e9', 'black' => '#000000|#FFFFFF|#000000', 'darkred' => '#ed4040|#FFFFFF|#ed4040', 'blue' => '#D9EDF7|#31708F|#0561bf', 'pink' => '#F2DEDE|#A94442|#508b27', 'yellow' => '#FAEBCC|#8A6D3B|#af7a0c', 'red' => '#EF8A80|#FFFFFF|#1393c3', 'green' => '#6CCAC9|#FFFFFF|#7a37ac', 'color1' => '#6cca7b|#FFFFFF|#fb9900', 'color2' => '#c2b079|#FFFFFF|#ed40ae', 'color3' => '#f3b431|#FFFFFF|#ff6600', 'color4' => '#6d91d3|#FFFFFF|#0bb7b5', 'color5' => '#a466cc|#FFFFFF|#a79902');

            global $arfdefaulttemplate;
            $arfdefaulttemplate = array(
                '3' => addslashes(esc_html__('Contact us', 'ARForms')),
                '1' => addslashes(esc_html__('Subscription Form', 'ARForms')),
                '5' => addslashes(esc_html__('Feedback Form', 'ARForms')),
                '6' => addslashes(esc_html__('RSVP Form', 'ARForms')),
                '2' => addslashes(esc_html__('Registration Form', 'ARForms')),
                '4' => addslashes(esc_html__('Survey Form', 'ARForms')),
                '7' => addslashes(esc_html__('Job Application', 'ARForms')),
            );

            global $arfmsgtounlicop;
            $arfmsgtounlicop = "(";
            $arfmsgtounlicop .= "Un";
            $arfmsgtounlicop .= "lic";
            $arfmsgtounlicop .= "ens";
            $arfmsgtounlicop .= "ed";
            $arfmsgtounlicop .= ")";
        }
    }

    function globalstandalone_route($controller, $action) {
        global $armainhelper, $arsettingcontroller;

        if ($controller == 'fields') {


            if (!defined('DOING_AJAX'))
                define('DOING_AJAX', true);


            global $arfieldcontroller;


            if ($action == 'ajax_get_data')
                $arfieldcontroller->ajax_get_data($armainhelper->get_param('entry_id'), $armainhelper->get_param('field_id'), $armainhelper->get_param('current_field'));


            else if ($action == 'ajax_time_options')
                $arfieldcontroller->ajax_time_options();
        }else if ($controller == 'entries') {

            global $arrecordcontroller;


            if ($action == 'csv') {


                $s = isset($_REQUEST['s']) ? 's' : 'search';


                $arrecordcontroller->csv($armainhelper->get_param('form'), $armainhelper->get_param($s), $armainhelper->get_param('fid'));


                unset($s);
            } else {


                if (!defined('DOING_AJAX'))
                    define('DOING_AJAX', true);

                if ($action == 'send_email')
                    $arrecordcontroller->send_email($armainhelper->get_param('entry_id'), $armainhelper->get_param('form_id'), $armainhelper->get_param('type'));


                else if ($action == 'create')
                    $arrecordcontroller->ajax_create();

                else if ($action == 'previous')
                    $arrecordcontroller->ajax_previous();
                else if ($action == 'check_recaptcha')
                    $arrecordcontroller->ajax_check_recaptcha();
                else if ($action == 'checkinbuiltcaptcha')
                    $arrecordcontroller->ajax_check_spam_filter();
                
                else if ($action == 'update')
                    $arrecordcontroller->ajax_update();


                else if ($action == 'destroy')
                    $arrecordcontroller->ajax_destroy();
            }
        }else if ($controller == 'settingspreview') {


            global $style_settings, $arfsettings;


            if (!is_admin())
                $use_saved = true;

            if (isset($_REQUEST['arfmfws'])) {
                $arfssl = (is_ssl()) ? 1 : 0;
                $css_class = '';
                if( isset($_REQUEST['arfinpst']) && $_REQUEST['arfinpst'] == 'material'){
                    $css_class = ' .arf_materialize_form ';
                    include(FORMPATH . '/core/css_create_materialize.php');
                } else {
                    $css_class = '';
                    include(FORMPATH . '/core/css_create_main.php');
                }

                global $arfform, $wpdb, $arrecordhelper, $arfieldhelper, $arformcontrollerm, $arformcontroller;
                $arfformid = $_REQUEST['arfformid'];
                $form = $arfform->getOne((int) $arfformid);

                $fields = $arfieldhelper->get_form_fields_tmp(false, $form->id, false, 0);
                $values = $arrecordhelper->setup_new_vars($fields, $form);

                echo stripslashes_deep(get_option('arf_global_css'));
                $form->options['arf_form_other_css'] = $arformcontroller->br2nl($form->options['arf_form_other_css']);
                echo $armainhelper->esc_textarea($form->options['arf_form_other_css']);

                $custom_css_array_form = array(
                    'arf_form_outer_wrapper' => '.arf_form_outer_wrapper|.arfmodal',
                    'arf_form_inner_wrapper' => '.arf_fieldset|.arfmodal',
                    'arf_form_title' => '.formtitle_style',
                    'arf_form_description' => 'div.formdescription_style',
                    'arf_form_element_wrapper' => '.arfformfield',
                    'arf_form_element_label' => 'label.arf_main_label',
                    'arf_form_elements' => '.controls',
                    'arf_submit_outer_wrapper' => 'div.arfsubmitbutton',
                    'arf_form_submit_button' => '.arfsubmitbutton button.arf_submit_btn',
                    'arf_form_next_button' => 'div.arfsubmitbutton .next_btn',
                    'arf_form_previous_button' => 'div.arfsubmitbutton .previous_btn',
                    'arf_form_success_message' => '#arf_message_success',
                    'arf_form_error_message' => '.control-group.arf_error .help-block|.control-group.arf_warning .help-block|.control-group.arf_warning .help-inline|.control-group.arf_warning .control-label|.control-group.arf_error .popover|.control-group.arf_warning .popover',
                    'arf_form_page_break' => '.page_break_nav',
                );

                foreach ($custom_css_array_form as $custom_css_block_form => $custom_css_classes_form) {


                    if (isset($form->options[$custom_css_block_form]) and $form->options[$custom_css_block_form] != '') {

                        $form->options[$custom_css_block_form] = $arformcontroller->br2nl($form->options[$custom_css_block_form]);

                        if ($custom_css_block_form == 'arf_form_outer_wrapper') {
                            $arf_form_outer_wrapper_array = explode('|', $custom_css_classes_form);

                            foreach ($arf_form_outer_wrapper_array as $arf_form_outer_wrapper1) {
                                if ($arf_form_outer_wrapper1 == '.arf_form_outer_wrapper')
                                    echo '.ar_main_div_' . $form->id . $css_class . '.arf_form_outer_wrapper { ' . $form->options[$custom_css_block_form] . ' } ';
                                if ($arf_form_outer_wrapper1 == '.arfmodal')
                                    echo '#popup-form-' . $form->id . $css_class. '.arfmodal{ ' . $form->options[$custom_css_block_form] . ' } ';
                            }
                        }
                        else if ($custom_css_block_form == 'arf_form_inner_wrapper') {
                            $arf_form_inner_wrapper_array = explode('|', $custom_css_classes_form);
                            foreach ($arf_form_inner_wrapper_array as $arf_form_inner_wrapper1) {
                                if ($arf_form_inner_wrapper1 == '.arf_fieldset')
                                    echo '.ar_main_div_' . $form->id . $css_class. ' ' . $arf_form_inner_wrapper1 . ' { ' . $form->options[$custom_css_block_form] . ' } ';
                                if ($arf_form_inner_wrapper1 == '.arfmodal')
                                    echo '.arfmodal .arfmodal-body .ar_main_div_' . $form->id . $css_class . ' .arf_fieldset { ' . $form->options[$custom_css_block_form] . ' } ';
                            }
                        }
                        else if ($custom_css_block_form == 'arf_form_error_message') {
                            $arf_form_error_message_array = explode('|', $custom_css_classes_form);

                            foreach ($arf_form_error_message_array as $arf_form_error_message1) {
                                echo '.ar_main_div_' . $form->id . $css_class . ' ' . $arf_form_error_message1 . ' { ' . $form->options[$custom_css_block_form] . ' } ';
                            }
                        } else {
                            echo '.ar_main_div_' . $form->id . $css_class . ' ' . $custom_css_classes_form . ' { ' . $form->options[$custom_css_block_form] . ' } ';
                        }
                    }
                }

                foreach ($values['fields'] as $field) {

                    $field['id'] = $arfieldhelper->get_actual_id($field['id']);

                    if (isset($field['field_width']) and $field['field_width'] != '') {
                        echo ' .ar_main_div_' . $form->id . $css_class . ' #arf_field_' . $field['id'] . '_container .help-block { width: ' . $field['field_width'] . 'px; } ';
                    }

                    if ($field['type'] == 'divider') {

                        if ($newarr['arfsectiontitlefamily'] != "Arial" && $newarr['arfsectiontitlefamily'] != "Helvetica" && $newarr['arfsectiontitlefamily'] != "sans-serif" && $newarr['arfsectiontitlefamily'] != "Lucida Grande" && $newarr['arfsectiontitlefamily'] != "Lucida Sans Unicode" && $newarr['arfsectiontitlefamily'] != "Tahoma" && $newarr['arfsectiontitlefamily'] != "Times New Roman" && $newarr['arfsectiontitlefamily'] != "Courier New" && $newarr['arfsectiontitlefamily'] != "Verdana" && $newarr['arfsectiontitlefamily'] != "Geneva" && $newarr['arfsectiontitlefamily'] != "Courier" && $newarr['arfsectiontitlefamily'] != "Monospace" && $newarr['arfsectiontitlefamily'] != "Times" && $newarr['arfsectiontitlefamily'] != "") {
                            if (is_ssl())
                             $googlefontbaseurl = "https://fonts.googleapis.com/css?family=";
                            else
                             $googlefontbaseurl = "http://fonts.googleapis.com/css?family=";
                            echo "@import url(" . $googlefontbaseurl . urlencode($newarr['arfsectiontitlefamily']) . ");";
                        }

                        if ($newarr['arfsectiontitleweightsetting'] == 'italic') {
                            $arf_heading_font_style = ' font-weight:normal; font-style:italic; ';
                        } else {
                            $arf_heading_font_style = ' font-weight:' . $field['arfsectiontitleweightsetting'] . '; font-style:normal; ';
                        }

                        
                    }

                    $custom_css_array = array(
                        'css_outer_wrapper' => '.arf_form_outer_wrapper',
                        'css_label' => '.css_label',
                        'css_input_element' => '.css_input_element',
                        'css_description' => '.arf_field_description',
                    );

                    foreach ($custom_css_array as $custom_css_block => $custom_css_classes) {

                        if (isset($field[$custom_css_block]) and $field[$custom_css_block] != '') {

                            $field[$custom_css_block] = $arformcontroller->br2nl($field[$custom_css_block]);

                            if ($custom_css_block == 'css_outer_wrapper' and $field['type'] != 'divider') {
                                echo ' .ar_main_div_' . $form->id . $css_class . ' #arf_field_' . $field['id'] . '_container { ' . $field[$custom_css_block] . ' } ';
                            } else if ($custom_css_block == 'css_outer_wrapper' and $field['type'] == 'divider') {
                                echo ' .ar_main_div_' . $form->id . $css_class . ' #heading_' . $field['id'] . ' { ' . $field[$custom_css_block] . ' } ';
                            } else if ($custom_css_block == 'css_label' and $field['type'] != 'divider') {
                                echo ' .ar_main_div_' . $form->id . $css_class . ' #arf_field_' . $field['id'] . '_container label.arf_main_label { ' . $field[$custom_css_block] . ' } ';
                            } else if ($custom_css_block == 'css_label' and $field['type'] == 'divider') {
                                echo ' .ar_main_div_' . $form->id . ' #heading_' . $field['id'] . ' h2.arf_sec_heading_field { ' . $field[$custom_css_block] . ' } ';
                            } else if ($custom_css_block == 'css_input_element') {

                                if ($field['type'] == 'textarea') {
                                    echo ' .ar_main_div_' . $form->id . $css_class . '  #arf_field_' . $field['id'] . '_container .controls textarea { ' . $field[$custom_css_block] . ' } ';
                                } else if ($field['type'] == 'select' || $field['type'] == ARF_AUTOCOMPLETE_SLUG) {
                                    echo ' .ar_main_div_' . $form->id . $css_class . '  #arf_field_' . $field['id'] . '_container .controls select { ' . $field[$custom_css_block] . ' } ';
                                    echo ' .ar_main_div_' . $form->id . $css_class . '  #arf_field_' . $field['id'] . '_container .controls .arfbtn.dropdown-toggle { ' . $field[$custom_css_block] . ' } ';
                                } else if ($field['type'] == 'radio') {
                                    echo ' .ar_main_div_' . $form->id . $css_class . '  #arf_field_' . $field['id'] . '_container .arf_radiobutton label { ' . $field[$custom_css_block] . ' } ';
                                } else if ($field['type'] == 'checkbox') {
                                    echo ' .ar_main_div_' . $form->id . $css_class . '  #arf_field_' . $field['id'] . '_container .arf_checkbox_style label { ' . $field[$custom_css_block] . ' } ';
                                } else if ($field['type'] == 'file') {
                                    echo ' .ar_main_div_' . $form->id . $css_class . '  #arf_field_' . $field['id'] . '_container .controls .arfajax-file-upload { ' . $field[$custom_css_block] . ' } ';
                                } else if ($field['type'] == 'colorpicker') {
                                    echo ' .ar_main_div_' . $form->id . $css_class . '  #arf_field_' . $field['id'] . '_container .controls .arfcolorpickerfield { ' . $field[$custom_css_block] . ' } ';
                                } else {
                                    echo ' .ar_main_div_' . $form->id . $css_class . '  #arf_field_' . $field['id'] . '_container .controls input { ' . $field[$custom_css_block] . ' } ';
                                    if ($field['type'] == 'email') {
                                        echo '.ar_main_div_' . $form->id . $css_class . ' #arf_field_' . $field['id'] . '_container + .confirm_email_container .controls input {' . $field[$custom_css_block] . '}';
                                    }
                                    if ($field['type'] == 'password') {
                                        echo '.ar_main_div_' . $form->id . $css_class . ' #arf_field_' . $field['id'] . '_container + .confirm_password_container .controls input{ ' . $field[$custom_css_block] . '}';
                                    }
                                }
                            } else if ($custom_css_block == 'css_description' and $field['type'] != 'divider') {
                                echo ' .ar_main_div_' . $form->id . $css_class . '  #arf_field_' . $field['id'] . '_container .arf_field_description { ' . $field[$custom_css_block] . ' } ';
                            } else if ($custom_css_block == 'css_description' and $field['type'] == 'divider') {
                                echo ' .ar_main_div_' . $form->id . $css_class . '  #heading_' . $field['id'] . ' .arf_heading_description { ' . $field[$custom_css_block] . ' } ';
                            }
                        }
                    }

                    
                }
            } else
                return false;
        }
    }

    function menu() {

        global $arfsettings, $armainhelper;

        function get_free_menu_position($start, $increment = 0.1) {
            foreach ($GLOBALS['menu'] as $key => $menu) {
                $menus_positions[] = $key;
            }

            if (!in_array($start, $menus_positions)) {
                return $start;
            } else {
                $start += $increment;
            }

	    while (in_array($start, $menus_positions)) {
                $start += $increment;
            }
            return $start;
        }

        $place = get_free_menu_position(26.1, .1);

        if (current_user_can('arfviewforms')) {


            global $arformcontroller;

            add_menu_page('ARForms', 'ARForms', 'arfviewforms', 'ARForms', array($arformcontroller, 'route'), ARFIMAGESURL . '/main-icon-small2n.png', (string) $place);
        } elseif (current_user_can('arfviewentries')) {


            global $arrecordcontroller;


            add_menu_page('ARForms', 'ARForms', 'arfviewentries', 'ARForms', array($arrecordcontroller, 'route'), ARFIMAGESURL . '/main-icon-small2n.png', (string) $place);
        }

        add_submenu_page('', '', '', 'administrator', 'ARForms-settings1', array($this, 'list_entries'));
    }

    function menu_css() {
        ?>


        <style type="text/css">
            #adminmenu .toplevel_page_ARForms div.wp-menu-image img{  padding: 5px 0 0 2px; }

        </style>    


        <?php

    }

    function get_form_nav($id, $show_nav = false, $values, $record, $template_id = 0, $is_ref_form = 0) {


        global $pagenow, $armainhelper;





        $show_nav = $armainhelper->get_param('show_nav', $show_nav);





        if ($show_nav)
            include(VIEWS_PATH . '/formmenu.php');
    }

    function settings_link($links, $file) {

        $settings = '<a href="' . admin_url('admin.php?page=ARForms-settings') . '">' . addslashes(esc_html__('Settings', 'ARForms')) . '</a>';

        array_unshift($links, $settings);

        return $links;
    }

    function admin_js() {


        global $arfversion, $pagenow, $maincontroller, $wp_version;

        $jquery_handler = 'jquery';
        $jquery_ui_handler = 'jquery-ui-core';
        $jq_draggable_handler = 'jquery-ui-draggable';
        if( version_compare($wp_version, '4.2','<') ){
            $jquery_handler = 'jquery-custom';
            $jquery_ui_handler = 'jquery-ui-core-custom';
            $jq_draggable_handler = 'jquery-ui-draggable-custom';
            wp_register_script('jquery-custom',ARFURL.'/js/jquery/compatibility_js/jquery.js',array(),$arfversion);
            wp_enqueue_script('jquery-custom');
            wp_register_script('jquery-ui-core-custom',ARFURL.'/js/jquery/compatibility_js/core.min.js',array(),$arfversion);
            wp_enqueue_script('jquery-ui-core-custom');
        } else {
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-ui-core');
        }

        if (isset($_GET) and ( isset($_GET['page']) and preg_match('/ARForms*/', $_GET['page'])) or ( $pagenow == 'edit.php' and isset($_GET) and isset($_GET['post_type']) and $_GET['post_type'] == 'frm_display')) {

            add_filter('admin_body_class', array($this, 'admin_body_class'));

            
            if( version_compare($wp_version, '4.2', '<')){
                wp_register_script('jquery-ui-widget-custom',ARFURL.'/js/jquery/compatibility_js/widget.min.js',array(),$arfversion);
                wp_enqueue_script('jquery-ui-widget-custom');
                wp_register_script('jquery-ui-mouse-custom',ARFURL.'/js/jquery/compatibility_js/mouse.min.js',array(),$arfversion);
                wp_enqueue_script('jquery-ui-mouse-custom');

                wp_register_script('jquery-ui-sortable-custom',ARFURL.'/js/jquery/compatibility_js/sortable.min.js',array($jquery_ui_handler),$arfversion);
                wp_enqueue_script('jquery-ui-sortable-custom');
                wp_register_script('jquery-ui-draggable-custom',ARFURL.'/js/jquery/compatibility_js/draggable.min.js',array($jquery_ui_handler),$arfversion);
                wp_enqueue_script('jquery-ui-draggable-custom');
                wp_register_script('jquery-ui-droppable-custom',ARFURL.'/js/jquery/compatibility_js/droppable.min.js',array($jquery_ui_handler),$arfversion);
                wp_enqueue_script('jquery-ui-droppable-custom');
                wp_register_script('jquery-ui-resizable-custom',ARFURL.'/js/jquery/compatibility_js/resizable.min.js',array($jquery_ui_handler),$arfversion);
                wp_enqueue_script('jquery-ui-resizable-custom');
            } else {
                wp_enqueue_script('jquery-ui-sortable');
                wp_enqueue_script('jquery-ui-draggable');
                wp_enqueue_script('jquery-ui-resizable');
                wp_enqueue_script('admin-widgets');
            }
            wp_enqueue_style('widgets');

            wp_enqueue_script('arforms_admin', ARFURL . '/js/arforms_admin.js', array($jquery_handler, $jq_draggable_handler), $arfversion);

            wp_enqueue_script('arforms_admin_v3.0', ARFURL . '/js/arforms_admin_3.0.js', array($jquery_handler, $jq_draggable_handler), $arfversion);

            if (is_rtl()) {
                wp_enqueue_style('arforms-admin-rtl', ARFURL . '/css/arforms-rtl.css', array(), $arfversion);
            }

            wp_enqueue_style('arforms_v3.0', ARFURL . '/css/arforms_v3.0.css', array(), $arfversion);

            /* NEW CSS FOR ALL MEDIA QUERY */ 
            wp_register_style('arf-media-css', ARFURL . '/css/arf_media_css.css', array(), $arfversion);
            wp_enqueue_style('arf-media-css');
            
            if (isset($_REQUEST['page']) && $_REQUEST['page'] != '' && $_REQUEST['page'] == 'ARForms' && isset($_REQUEST['arfaction']) && $_REQUEST['arfaction'] != '') {
                wp_enqueue_script('arfjquery-json', ARFURL . '/js/jquery/jquery.json-2.4.js', array($jquery_handler), $arfversion);
            }

            if ($GLOBALS['wp_version'] >= '3.8' and version_compare($GLOBALS['wp_version'], '3.9', '<')) {

                wp_enqueue_style('arforms-admin-3.8', ARFURL . '/css/arf_plugin_3.8.css', array(), $arfversion);
            }

            if ($GLOBALS['wp_version'] >= '3.9' and version_compare($GLOBALS['wp_version'], '3.10', '<')) {

                wp_enqueue_style('arforms-admin-3.9', ARFURL . '/css/arf_plugin_3.9.css', array(), $arfversion);
            }

            if ($GLOBALS['wp_version'] >= '4.0') {

                wp_enqueue_style('arforms-admin-4.0', ARFURL . '/css/arf_plugin_4.0.css', array(), $arfversion);
            }
        } else if ($pagenow == 'post.php' or ( $pagenow == 'post-new.php' and isset($_REQUEST['post_type']) and $_REQUEST['post_type'] == 'frm_display')) {


            if (isset($_REQUEST['post_type'])) {


                $post_type = $_REQUEST['post_type'];
            } else if (isset($_REQUEST['post']) and ! empty($_REQUEST['post'])) {


                $post = get_post($_REQUEST['post']);


                if (!$post)
                    return;


                $post_type = $post->post_type;
            }else {


                return;
            }

            if ($post_type == 'frm_display') {

                if( version_compare($wp_version, '4.2', '<')){
                     wp_enqueue_script('jquery-ui-widget-custom',ARFURL.'/js/jquery/compatibility_js/widget.min.js',array(),$arfversion);
                    wp_enqueue_script('jquery-ui-mouse-custom',ARFURL.'/js/jquery/compatibility_js/mouse.min.js',array(),$arfversion);
                    wp_enqueue_script('jquery-ui-draggable-custom',ARFURL.'/js/jquery/compatibility_js/draggable.min.js',array(),$arfversion);
                } else {
                    wp_enqueue_script('jquery-ui-draggable');
                }



                wp_enqueue_script('arforms_admin', ARFURL . '/js/arforms_admin.js', array($jquery_handler, $jq_draggable_handler), $arfversion);

                wp_enqueue_script('arforms_admin_v3.0', ARFURL . '/js/arforms_admin_3.0.js', array($jquery_handler, $jq_draggable_handler), $arfversion);

                wp_enqueue_style('arforms_v3.0', ARFURL . '/css/arforms_v3.0.css', array(), $arfversion);

                /* NEW CSS FOR ALL MEDIA QUERY */ 
                wp_register_style('arf-media-css', ARFURL . '/css/arf_media_css.css', array(), $arfversion);
                wp_enqueue_style('arf-media-css');

                if ($GLOBALS['wp_version'] >= '3.8' and version_compare($GLOBALS['wp_version'], '3.9', '<')) {

                    wp_enqueue_style('arforms-admin-3.8', ARFURL . '/css/arf_plugin_3.8.css', array(), $arfversion);
                }
            }
        }
    }

    function admin_body_class($classes) {


        global $wp_version;


        if (version_compare($wp_version, '3.4.9', '>'))
            $classes .= ' arf35trigger';

        return $classes;
    }

    function front_head($ispost = '') {

        
        global $arfsettings, $arfversion, $arfdbversion, $maincontroller, $arformcontroller;

        if (!is_admin()) {
            wp_enqueue_script('jquery');
            wp_register_script('arfbootstrap-js', ARFURL . '/bootstrap/js/bootstrap.min.js', array('jquery'), $arfversion);
            wp_register_script('jquery-bootstrap-slect', ARFURL . '/bootstrap/js/bootstrap-select.js', array('jquery'), $arfversion);
            wp_register_script('jquery-validation', ARFURL . '/bootstrap/js/jqBootstrapValidation.js', array('jquery'), $arfversion);
            wp_register_style('arfbootstrap-css', ARFURL . '/bootstrap/css/bootstrap.css', array(), $arfversion);
            wp_register_style('arfbootstrap-select', ARFURL . '/bootstrap/css/bootstrap-select.css', array(), $arfversion);



            wp_register_script('arfbootstrap-modernizr-js', ARFURL . '/bootstrap/js/modernizr.js', array(), $arfversion);
            wp_register_script('arfbootstrap-slider-js', ARFURL . '/bootstrap/js/bootstrap-slider.js', array(), $arfversion);
            wp_register_style('arfbootstrap-slider', ARFURL . '/bootstrap/css/bootstrap-slider.css', array(), $arfversion);
            wp_register_style('arfdisplaycss', ARFURL . '/css/arf_front.css', array(), $arfversion);

            
            wp_register_style('arfdisplayflagiconcss', ARFURL . '/css/flag_icon.css', array(), $arfversion);

            wp_register_style('arfrecaptchacss', ARFURL . '/css/recaptcha_style.css', array(), $arfversion);
            wp_register_style('arf-filedrag', ARFURL . '/css/arf_filedrag.css', array(), $arfversion);
            
            wp_register_script('arf-modal-js', ARFURL . '/js/arf_modal_js.js', array('jquery'), $arfversion);

            wp_register_script('arf-conditional-logic-js', ARFURL . '/js/arf_conditional_logic.js', array('jquery'), $arfversion);
            wp_register_style('arfbootstrap-datepicker-css', ARFURL . '/bootstrap/css/bootstrap-datetimepicker.css', array('arfbootstrap-css'), $arfversion);
            wp_register_script('arfbootstrap-inputmask', ARFURL . '/bootstrap/js/bootstrap-inputmask.js', array('jquery'), $arfversion);
            wp_register_script('jquery-maskedinput', ARFURL . '/js/jquery.maskedinput.min.js', array('jquery'), $arfversion, true);

            wp_register_script('arf_js_color',ARFURL.'/js/jscolor.js',array('jquery'), $arfversion);

            wp_register_script('arf-colorpicker-basic-js', ARFURL . '/js/jquery.simple-color-picker.js', array(), $arfversion);

            wp_register_style('arf-fontawesome-css', ARFURL . '/css/font-awesome.min.css', array(), $arfversion);

            wp_register_script('arf_tipso_js_front', ARFURL . '/js/tipso.min.js', array(), $arfversion);

            wp_register_style('arf_tipso_css_front', ARFURL . '/css/tipso.min.css', array(), $arfversion);

            wp_register_script('animate-numbers', ARFURL . '/js/jquery.animateNumber.js', array(), $arfversion);

            wp_register_script('filedrag', ARFURL . '/js/filedrag/filedrag_front.js', array(), $arfversion);
            wp_register_script('bootstrap-typeahead-js', ARFURL . '/bootstrap/js/bootstrap-typeahead.js', array(), $arfversion);
        } else {
            wp_enqueue_script('jquery');
        }

        $path = $_SERVER['REQUEST_URI'];
        $file_path = basename($path);

        if (!strstr($file_path, "post.php")) {
            wp_register_script('jquery-maskedinput', ARFURL . '/js/jquery.maskedinput.min.js', array('jquery'), $arfversion, true);
            wp_register_script('arfbootstrap-inputmask', ARFURL . '/bootstrap/js/bootstrap-inputmask.js', array('jquery'), $arfversion);
            wp_register_script('arforms_phone_intl_input', ARFURL . '/js/intlTelInput.min.js', array(), $arfversion, true);
            wp_register_script('arforms_phone_utils', ARFURL . '/js/arf_phone_utils.js', array(), $arfversion, true);
            wp_register_script('arforms', ARFURL . '/js/arforms.js', array('jquery'), $arfversion, true);
        }

        wp_register_script('recaptcha-ajax', ARFURL . '/js/recaptcha_ajax.js', array(), $arfversion);

        if ($ispost = '1' && !is_admin()) {
            global $post;
            $post_content = isset($post->post_content) ? $post->post_content : '';
            $parts = explode("[ARForms", $post_content);
            if (isset($parts[1])) {
                $myidpart = explode("id=", $parts[1]);
                $myid = isset($myidpart[1]) ? explode("]", $myidpart[1]) : array() ;
                if (isset($myid[0]) && $myid[0] > 0) {
                    
                }
            }
        }

        if (!is_admin() and isset($arfsettings->load_style) and $arfsettings->load_style == 'all') {


            $css = apply_filters('getarfstylesheet', ARFURL . '/css/arf_front.css', 'header');


            if (is_array($css)) {


                foreach ($css as $css_key => $file)
                    wp_enqueue_style('arf-forms' . $css_key, $file, array(), $arfversion);


                unset($css_key);


                unset($file);
            } else
                wp_enqueue_style('arf-forms', $css, array(), $arfversion);


            unset($css);





            global $arfcssloaded;


            $arfcssloaded = true;
        }
    }

    function footer_js($location = 'footer') {
        global $arfloadcss, $arfsettings, $arfversion, $arfcssloaded, $arfforms_loaded, $armainhelper,$forms_in_menu,$wpdb,$arformcontroller,$MdlDb, $arf_jscss_version;
    
    /* Direct Nav Menu */
    $wp_upload_dir = wp_upload_dir();
        $upload_main_url = $wp_upload_dir['baseurl'] . '/arforms/maincss';
    

    if(!is_null($forms_in_menu) && count($forms_in_menu) > 0){

	foreach($forms_in_menu as $formid){
	    
	    if (is_ssl()) {
		$fid = str_replace("http://", "https://", $upload_main_url . '/maincss_' . $formid . '.css');
	    } else {
		$fid = $upload_main_url . '/maincss_' . $formid . '.css';
	    }
	    
	    if (is_ssl()) {
		$fid_material = str_replace("http://", "https://", $upload_main_url . '/maincss_materialize_' . $formid . '.css');
	    } else {
		$fid_material = $upload_main_url . '/maincss_materialize_' . $formid . '.css';
	    }
	    
	   
	     $res = $wpdb->get_row($wpdb->prepare("SELECT is_template,status,form_css FROM " . $MdlDb->forms . " WHERE id = %d", $formid), 'ARRAY_A');

	    if (isset($res['is_template']) && isset($res['status']) && $res['is_template'] == '0' && $res['status'] == 'published') {
		/* arf_dev_flag below function contain query */
		$func_val = apply_filters('arf_hide_forms', $arformcontroller->arf_class_to_hide_form($formid), $formid);

		$form_css = maybe_unserialize($res['form_css']);
		if ($func_val == '') {
		    if (isset($form_css['arfinputstyle']) && $form_css['arfinputstyle'] != 'material') {
                wp_enqueue_style('arfformscss' . $formid, $fid, array(), $arf_jscss_version);
		    }

		    if (isset($form_css['arfinputstyle']) && $form_css['arfinputstyle'] == 'material') {
            wp_enqueue_style('arfformscss_materialize_' . $formid, $fid_material, array(), $arf_jscss_version);
			wp_enqueue_style('arf_materialize_css', ARFURL . '/materialize/materialize.css', array(), $arfversion);
			wp_enqueue_script('arf_materialize_js', ARFURL . '/materialize/materialize.js', array(), $arfversion);
		    }
		    wp_enqueue_style('arfbootstrap-css');
		    wp_enqueue_style('arfdisplaycss');
            wp_enqueue_style('arfdisplayflagiconcss');
		} else {
            wp_enqueue_style('arfdisplaycss');
            wp_enqueue_style('arfdisplayflagiconcss');
		    if (isset($form_css['arfinputstyle']) && $form_css['arfinputstyle'] != 'material') {
                wp_enqueue_style('arfformscss' . $formid, $fid, array(), $arf_jscss_version);
    	    }
            if (isset($form_css['arfinputstyle']) && $form_css['arfinputstyle'] == 'material') {
                wp_enqueue_style('arfformscss_materialize_' . $formid, $fid_material, array(), $arf_jscss_version);
                wp_enqueue_style('arf_materialize_css', ARFURL . '/materialize/materialize.css', array(), $arfversion);
    	        wp_enqueue_script('arf_materialize_js', ARFURL . '/materialize/materialize.js', array(), $arfversion);
    	    }
		}
	    }
	   }
	}
	/* Direct Nav over */
	
        if ($arfloadcss and ! is_admin() and ( $arfsettings->load_style != 'none')) {
            if ($arfcssloaded) {
                $css = apply_filters('getarfstylesheet', '', $location);
            } else {
                $css = apply_filters('getarfstylesheet', ARFURL . '/css/arf_front.css', $location);
            }

            if (!empty($css)) {
                echo "\n" . '<script type="text/javascript" data-cfasync="false">';
                if (is_array($css)) {
                    foreach ($css as $css_key => $file) {
                        echo 'jQuery("head").append(unescape("%3Clink rel=\'stylesheet\' id=\'arf-forms' . ($css_key + $arfcssloaded) . '-css\' href=\'' . $file . '\' type=\'text/css\' media=\'all\' /%3E"));';
                        unset($css_key);
                        unset($file);
                    }
                } else {
                    echo 'jQuery("head").append(unescape("%3Clink rel=\'stylesheet\' id=\'arfformscss\' href=\'' . $css . '\' type=\'text/css\' media=\'all\' /%3E"));';
                }
                unset($css);
                echo '</script>' . "\n";
            }
        }

        if (!is_admin() and $location != 'header' and ! empty($arfforms_loaded)) {
            $armainhelper->load_scripts(array('arforms'));
        }
	
    }

    function wp_enqeue_footer_script() {
        
        global $fields_with_external_js, $bootstraped_fields_array, $wpdb, $MdlDb,$arfversion;

        if (is_admin() && isset($_REQUEST['page']) && $_REQUEST['page'] == 'ARForms' && isset($_REQUEST['arfaction']) && $_REQUEST['arfaction'] != '') {
            if (isset($fields_with_external_js) && is_array($fields_with_external_js) && !empty($fields_with_external_js)) {
                $matched_fields = array_intersect($fields_with_external_js, $bootstraped_fields_array);

                foreach ($matched_fields as $field_type) {
                    switch ($field_type) {
                        case 'select':
                            wp_register_script('arfbootstrap-select-js', ARFURL . '/bootstrap/js/bootstrap-select.js', array('jquery'), $arfversion);
                            wp_enqueue_script('arfbootstrap-select-js');
                            wp_register_style('arfbootstrap-select-css', ARFURL . '/bootstrap/css/bootstrap-select.css', array(), $arfversion);
                            wp_enqueue_style('arfbootstrap-select-css');
                            break;
                        case 'date':
                            break;
                        case 'time':
                            break;
                        case 'colorpicker':
                            $action = isset($_REQUEST['arfaction']) ? $_REQUEST['arfaction'] : '';
                            if ($action == 'edit') {
                                $form_id = $_REQUEST['id'];
                                $getcpfields = $wpdb->get_results($wpdb->prepare("SELECT field_options FROM `" . $MdlDb->fields . "` WHERE `type` = %s and `form_id` = %d", 'colorpicker', $form_id));
                                $load_simple_colorpicker = false;
                                if (!empty($getcpfields)) {
                                    foreach ($getcpfields as $key => $cpfieldoptions) {
                                        $field_options = json_decode($cpfieldoptions->field_options, true);
                                        if (json_last_error() != JSON_ERROR_NONE) {
                                            $field_options = maybe_unserialize($field_options);
                                        }
                                        $colorpicker_type = $field_options['colorpicker_type'];
                                        if ($colorpicker_type == 'basic') {
                                            $load_simple_colorpicker = true;
                                        }
                                    }
                                }
                                if( $load_simple_colorpicker == true ){
                                    wp_enqueue_script('arf-colorpicker-basic-js', ARFURL . '/js/jquery.simple-color-picker.js', array(), $arfversion);
                                }
                            }
                            break;
                        
                        default:
                            do_action('arf_load_bootstrap_js_from_outside', $field_type);
                            break;
                    }
                }
            }
        }
    }

    function front_head_js() {
        global $post, $wpdb, $arformcontroller, $arfversion, $arfform, $armainhelper, $arrecordhelper, $arfieldhelper, $form_type_with_id, $MdlDb,$func_val, $arf_jscss_version;
        $wp_upload_dir = wp_upload_dir();
        $upload_main_url = $wp_upload_dir['baseurl'] . '/arforms/maincss';

        if( !isset($form_type_with_id) || $form_type_with_id == '' ){
            $form_type_with_id = array();
        }

        $post_content = isset($post->post_content) ? $post->post_content : '';
        $parts = explode("[ARForms", $post_content);
        $parts[1] = isset($parts[1]) ? $parts[1] : '';
        $myidpart = ($parts[1]!='') ? explode("id=", $parts[1]) : array();
        $myidpart[1] = isset($myidpart[1]) ? $myidpart[1] : '';
        $myid = ($myidpart[1]!='') ? explode("]", $myidpart[1]) : '';
		
        if (!is_admin()) {
            global $wp_query,$is_active_cornorstone;
            $posts = $wp_query->posts;            
            if($is_active_cornorstone)
            {
                $pattern = '\[(\[?)(ARForms|ARForms_popup|cs_arforms_cs)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
            }
            else{
                $pattern = '\[(\[?)(ARForms|ARForms_popup)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
            }

            if (is_array($posts)) {
                foreach ($posts as $post) {
                    if (preg_match_all('/' . $pattern . '/s', $post->post_content, $matches) && array_key_exists(2, $matches) && in_array('ARForms', $matches[2])) {
                        break;
                    }
                }

                $formids = array();
                $form_type_with_id = array();

                if (isset($matches)) {
                    if (is_array($matches) && count($matches) > 0) {
                        foreach ($matches as $k => $v) {
                            foreach ($v as $key => $val) {
                                $parts_cornerstone = 0;
                                if (strpos($val, 'id=') !== false) {
                                    $parts = explode("id=", $val);
                                } else if (strpos($val, 'arf_forms=') !== false) {

                                    $parts_cornerstone = explode("arf_forms=", $val);
                                }

                                if ($parts > 0 && isset($parts[1])) {

                                    if (stripos($parts[1], ']') !== false) {
                                        $partsnew = explode("]", $parts[1]);
                                        $formids[] = $partsnew[0];
                                    } else if (stripos($parts[1], ' ') !== false) {

                                        $partsnew = explode(" ", $parts[1]);
                                        $formids[] = $partsnew[0];
                                    } else {
                                        
                                    }
                                }
                                if ($parts_cornerstone > 0 && isset($parts_cornerstone[1])) {
                                    if (!is_array($parts_cornerstone[1])) {

                                        $parts_cornerstone[1] = explode(' ', $parts_cornerstone[1]);
                                        $parts_cornerstone[1][0] = str_replace('"', '', $parts_cornerstone[1][0]);

                                        $formids[] = $parts_cornerstone[1][0];
                                    }
                                }


                                /* arf_dev_flag need improvement */
                                if (strpos($val, '[') !== false && strpos($val, ']') !== false) {
                                    $temp_value = shortcode_parse_atts($val);
                                    if (isset($temp_value[1])) {

                                        $temp_value[1] = explode('=', $temp_value[1]);
                                        if (isset($temp_value[1][1])) {
                                            $temp_value[1][1] = str_replace("'", '', $temp_value[1][1]);
                                            $temp_value[1][1] = str_replace('"', '', $temp_value[1][1]);
                                            $temp_value[1][1] = str_replace(']', '', $temp_value[1][1]);
                                            $temp_value[1][1] = str_replace('[', '', $temp_value[1][1]);
                                            $temp_value[$temp_value[1][0]] = $temp_value[1][1];
                                        }
                                    }

                                    if (isset($temp_value['id'])) {
                                        $form_type_with_id[] = $temp_value;
                                    } else if (isset($temp_value['arf_forms'])) {
                                        $temp_value['id'] = $temp_value['arf_forms'];
                                        $form_type_with_id[] = $temp_value;
                                    }
                                }
                            }
                        }
                    }
                }
            }



            $newvalarr = array();

            if (isset($formids) and is_array($formids) && count($formids) > 0) {                
                foreach ($formids as $newkey => $newval) {
                    if (stripos($newval, ' ') !== false) {
                        $partsnew = explode(" ", $newval);
                        $newvalarr[] = $partsnew[0];
                    } else
                        $newvalarr[] = $newval;
                }
            }            


            if (is_array($newvalarr) && count($newvalarr) > 0) {
                $newvalarr = array_unique($newvalarr);
                
                foreach ($newvalarr as $newkey => $newval) {
                    $pattern = '/(\d+)/';
                    preg_match_all($pattern,$newval,$matches);
                    $newval = $matches[0][0];
                    if (is_ssl()) {
                        $fid = str_replace("http://", "https://", $upload_main_url . '/maincss_' . $newval . '.css');
                    } else {
                        $fid = $upload_main_url . '/maincss_' . $newval . '.css';
                    }

                    if (is_ssl()) {
                        $fid_material = str_replace("http://", "https://", $upload_main_url . '/maincss_materialize_' . $newval . '.css');
                    } else {
                        $fid_material = $upload_main_url . '/maincss_materialize_' . $newval . '.css';
                    }
                    
                    if( !isset($GLOBALS['arf_form_data'][$newval])){
                        $res = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $MdlDb->forms . " WHERE id = %d", $newval), 'ARRAY_A');
                        $GLOBALS['arf_form_data'][$newval] = $arformcontroller->arfArraytoObj($res);
                    } else {
                        $res = $arformcontroller->arfObjtoArray($GLOBALS['arf_form_data'][$newval]);
                    }


                    if (isset($res['is_template']) && isset($res['status']) && $res['is_template'] == '0' && $res['status'] == 'published') {
                        /* arf_dev_flag below function contain query */
                        $func_val = apply_filters('arf_hide_forms', $arformcontroller->arf_class_to_hide_form($newval), $newval);

                        $GLOBALS['function_val'][$newval] = $func_val;
                        $form_css = maybe_unserialize($res['form_css']);
                        if ($func_val == '') {
                            if (isset($form_css['arfinputstyle']) && $form_css['arfinputstyle'] != 'material') {
                                wp_enqueue_style('arfformscss' . $newval, $fid, array(), $arf_jscss_version);
                            }

                            if (isset($form_css['arfinputstyle']) && $form_css['arfinputstyle'] == 'material') {
                                wp_enqueue_style('arfformscss_materialize_' . $newval, $fid_material, array(), $arf_jscss_version);
                                wp_enqueue_style('arf_materialize_css', ARFURL . '/materialize/materialize.css', array(), $arfversion);
                                wp_enqueue_script('arf_materialize_js', ARFURL . '/materialize/materialize.js', array(), $arfversion);
                            }
                            wp_enqueue_style('arfbootstrap-css');
                            wp_enqueue_style('arfdisplaycss');
                            wp_enqueue_style('arfdisplayflagiconcss');
                        } else {
                            if (isset($form_css['arfinputstyle']) && $form_css['arfinputstyle'] != 'material') {
                                wp_enqueue_style('arfformscss' . $newval, $fid, array(), $arf_jscss_version);
                            }

                            if (isset($form_css['arfinputstyle']) && $form_css['arfinputstyle'] == 'material') {
                                wp_enqueue_style('arfformscss_materialize_' . $newval, $fid_material, array(), $arf_jscss_version);
                                wp_enqueue_style('arf_materialize_css', ARFURL . '/materialize/materialize.css', array(), $arfversion);
                                wp_enqueue_script('arf_materialize_js', ARFURL . '/materialize/materialize.js', array(), $arfversion);
                            }
                        }
                    }
                }
            }
            /* arf_dev_flag if form restricted with max_entries or date than  echo style or not?? */

            foreach ($form_type_with_id as $key => $value) {
                
                $define_cs_position = '';
                if(isset($value['arf_link_type']) == 'fly')
                {
                    $define_cs_position = (isset($value['arf_fly_position']) ? $value['arf_fly_position'] : '');
                }
                else
                {
                    $define_cs_position = (isset($value['arf_link_position']) ? $value['arf_link_position'] : '');
                }
                $value['type'] = isset($value['type']) ? $value['type'] : (isset($value['arf_link_type']) ? $value['arf_link_type'] : '');
                $value['position'] = isset($value['position']) ? $value['position'] : (isset($define_cs_position) ? $define_cs_position : '');
                $bgcolor = isset($value['bgcolor']) ? $value['bgcolor'] : (isset($value['arf_button_background_color']) ? $value['arf_button_background_color'] : '#8ccf7a');
                $txtcolor = isset($value['txtcolor']) ? $value['txtcolor'] : (isset($value['arf_button_text_color']) ? $value['arf_button_text_color'] : '#ffffff');
                $btn_angle = isset($value['angle']) ? $value['angle'] : (isset($value['arf_fly_button_angle']) ? $value['arf_fly_button_angle'] : '0');
                $modal_bgcolor = isset($value['modal_bgcolor']) ? $value['modal_bgcolor'] : (isset($value['arf_background_overlay_color']) ? $value['arf_background_overlay_color'] : '#000000');
                $overlay = isset($value['overlay']) ? $value['overlay'] : (isset($value['arf_background_overlay']) ? $value['arf_background_overlay'] : '0.6');

                $is_fullscreen_act = (isset($value['is_fullscreen']) && $value['is_fullscreen'] == 'yes') ? $value['is_fullscreen'] : 'no';
                 
                if( isset($value['arf_show_full_screen']) && $value['arf_show_full_screen'] == 'yes' ){
                    $is_fullscreen_act = 'yes';
                }
                

                $inactive_min      = isset($value['inactive_min']) ? $value['inactive_min'] : (isset($value['arf_inactive_min']) ? $value['arf_inactive_min'] : '0');

                $modaleffect       = isset($value['modaleffect']) ? $value['modaleffect'] : (isset($value['arf_modaleffect']) ? $value['arf_modaleffect'] : 'no_animation');
                
               
                $type = $value['type'];
                if(isset($value['arf_onclick_type']) && !empty($value['arf_onclick_type'])){
                    $type = $value['arf_onclick_type'];
                }
                
            }
        }
    }

    public static function arf_db_check() {
        global $MdlDb;
        $arf_db_version = get_option('arf_db_version');
        if (( $arf_db_version == '' || !isset($arf_db_version) ) && IS_WPMU)
            $MdlDb->upgrade($old_db_version);
    }

    public static function install($old_db_version = false) {

        global $MdlDb,$armainhelper;

        $arf_db_version = get_option('arf_db_version');
        if ($arf_db_version == '' || !isset($arf_db_version))
            $MdlDb->upgrade($old_db_version);
	

	$args = array(
            'role' => 'administrator',
            'fields' => 'id'
        );
        $users = get_users($args);
        if( count($users) > 0 ){
            foreach($users as $key => $user_id ){
                
		 global $current_user;
		 $arfroles = $armainhelper->frm_capabilities();


                $userObj = new WP_User($user_id);
                foreach ($arfroles as $arfrole => $arfroledescription){
                    $userObj->add_cap($arfrole);
                }
                unset($arfrole);
                unset($arfroles);
                unset($arfroledescription);
            }
        }
    }

    function referer_session() {


        global $arfsiteurl, $arfsettings;

        if (!session_id()) {
            @session_start();
        }





        if (!isset($_SESSION['arfhttppages']) or ! is_array($_SESSION['arfhttppages']))
            $_SESSION['arfhttppages'] = array("http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);





        if (!isset($_SESSION['arfhttpreferer']) or ! is_array($_SESSION['arfhttpreferer']))
            $_SESSION['arfhttpreferer'] = array();





        if (!isset($_SERVER['HTTP_REFERER']) or ( isset($_SERVER['HTTP_REFERER']) and ( strpos($_SERVER['HTTP_REFERER'], $arfsiteurl) === false) and ! (in_array($_SERVER['HTTP_REFERER'], $_SESSION['arfhttpreferer'])) )) {


            if (!isset($_SERVER['HTTP_REFERER'])) {


                $direct = addslashes(esc_html__('Type-in or bookmark', 'ARForms'));


                if (!in_array($direct, $_SESSION['arfhttpreferer']))
                    $_SESSION['arfhttpreferer'][] = $direct;
            }else {


                $_SESSION['arfhttpreferer'][] = $_SERVER['HTTP_REFERER'];
            }
        }





        if ($_SESSION['arfhttppages'] and ! empty($_SESSION['arfhttppages']) and ( end($_SESSION['arfhttppages']) != "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']))
            $_SESSION['arfhttppages'][] = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];




        if (count($_SESSION['arfhttppages']) > 100) {


            foreach ($_SESSION['arfhttppages'] as $pkey => $ppage) {


                if (count($_SESSION['arfhttppages']) <= 100)
                    break;





                unset($_SESSION['arfhttppages'][$pkey]);
            }
        }
    }

    function parse_standalone_request() {


        $plugin = $this->get_param('plugin');


        $action = isset($_REQUEST['arfaction']) ? 'arfaction' : 'action';


        $action = $this->get_param($action);


        $controller = $this->get_param('controller');





        if (!empty($plugin) and $plugin == 'ARForms' and ! empty($controller)) {


            $this->standalone_route($controller, $action);


            exit;
        }
    }

    function standalone_route($controller, $action = '') {

        global $arformcontroller;


        if ($controller == 'forms' and ! in_array($action, array('export', 'import')))
            $arformcontroller->preview($this->get_param('form'));
        else
            do_action('arfstandaloneroute', $controller, $action);
    }

    function get_param($param, $default = '') {


        return (isset($_POST[$param]) ? $_POST[$param] : (isset($_GET[$param]) ? $_GET[$param] : $default));
    }

    function get_form_shortcode($atts) {

        global $arfskipshortcode, $arrecordcontroller, $arfsettings, $arf_loaded_form_unique_id_array, $arformcontroller;


        if ($arfskipshortcode) {


            $sc = '[ARForms';


            foreach ($atts as $k => $v)
                $sc .= ' ' . $k . '="' . $v . '"';


            return $sc . ']';
        }

        extract(shortcode_atts(array('id' => '', 'key' => '', 'title' => false, 'description' => false, 'readonly' => false, 'entry_id' => false, 'fields' => array()), $atts));


        do_action('ARForms_shortcode_atts', compact('id', 'key', 'title', 'description', 'readonly', 'entry_id', 'fields'));


        global $wpdb, $MdlDb;

        if(!isset($GLOBALS['arf_form_data'][$id])){
            $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->forms . " WHERE id = %d", $id), 'ARRAY_A');
            $GLOBALS['arf_form_data'] = $res;
        } else {
            $res = $arformcontroller->arfObjtoArray($GLOBALS['arf_form_data'][$id]);
        }
        $res = (is_array($res) and count($res) > 0 and isset($res[0])) ? $res[0] : $res;


        $values = maybe_unserialize((isset($res['options'])) ? $res['options'] : '' );

        if (isset($values['display_title_form']) and $values['display_title_form'] == '0') {
            $title = false;
            $description = false;
        } else {
            $title = true;
            $description = true;
        }

        $arf_data_uniq_id = '';
        if (isset($arf_loaded_form_unique_id_array[$id]['normal'][0])) {
            $arf_data_uniq_id = current($arf_loaded_form_unique_id_array[$id]['normal']);
            if (is_array($arf_loaded_form_unique_id_array[$id]['normal'])) {
                array_shift($arf_loaded_form_unique_id_array[$id]['normal']);
            } else {
                unset($arf_loaded_form_unique_id_array[$id]['normal']);
            }
        } else {
            $arf_data_uniq_id = rand(1, 99999);
            if (empty($arf_data_uniq_id) || $arf_data_uniq_id == '') {
                $arf_data_uniq_id = $id;
            }
        }

        if(isset($atts['arfsubmiterrormsg'])){
            $_REQUEST['arfsubmiterrormsg'] = $atts['arfsubmiterrormsg'];
        }

        require_once VIEWS_PATH . '/arf_front_form.php';
        $contents = ars_get_form_builder_string($id, $key, false, false, '', $arf_data_uniq_id);
        $contents = apply_filters('arf_pre_display_arfomrms', $contents, $id, $key);

        return $contents;
    }

    function get_form_shortcode_popup($atts) {

        global $arfskipshortcode, $arrecordcontroller, $arfsettings, $arf_loaded_form_unique_id_array;

        wp_enqueue_style('arfbootstrap-css');
        wp_enqueue_style('arfdisplaycss');
        wp_enqueue_style('arfdisplayflagiconcss');
        if ($arfskipshortcode) {


            $sc = '[ARForms_popup';


            foreach ($atts as $k => $v)
                $sc .= ' ' . $k . '="' . $v . '"';


            return $sc . ']';
        }


        extract(shortcode_atts(array('id' => '', 'key' => '', 'title' => false, 'description' => false, 'readonly' => false, 'entry_id' => false, 'fields' => array(), 'desc' => 'Click here to open Form', 'shortcode_type' => '','preset_data' => ''), $atts));

        do_action('ARForms_popup_shortcode_atts', compact('id', 'key', 'title', 'description', 'readonly', 'entry_id', 'fields', 'desc', 'shortcode_type','preset_data'));

        global $wpdb, $MdlDb;
        if( isset($GLOBALS['arf_form_options']) && isset($GLOBALS['arf_form_options'][$id]) ){
            $res = $GLOBALS['arf_form_options'][$id];
        } else {
            $res = $wpdb->get_results($wpdb->prepare("SELECT options FROM " . $MdlDb->forms . " WHERE id = %d", $id), 'ARRAY_A');
            if( !isset($GLOBALS['arf_form_options']) ){
                $GLOBALS['arf_form_options'] = array();
            }
            $GLOBALS['arf_form_options'][$id] = $res;
        }
        $res = ( count($res) > 0 ) ? $res[0] : '';

        $values = maybe_unserialize(isset($res['options']) ? $res['options'] : '');

        if (isset($values['display_title_form']) and $values['display_title_form'] == '0') {
            $title = false;
            $description = false;
        } else {
            $title = true;
            $description = true;
        }

        $type = isset($atts['type']) ? $atts['type'] : 'link';
        $modal_height = isset($atts['height']) ? $atts['height'] : 'auto';
        $modal_width = isset($atts['width']) ? $atts['width'] : '800';
        $position = isset($atts['position']) ? $atts['position'] : 'top';
        $btn_angle = isset($atts['angle']) ? $atts['angle'] : '0';
        $bgcolor = isset($atts['bgcolor']) ? $atts['bgcolor'] : '#8ccf7a';
        $txtcolor = isset($atts['txtcolor']) ? $atts['txtcolor'] : '#ffffff';

        $open_inactivity = isset($atts['on_inactivity']) ? $atts['on_inactivity'] : '1';
        $open_scroll = isset($atts['on_scroll']) ? $atts['on_scroll'] : '10';
        $open_delay = isset($atts['on_delay']) ? $atts['on_delay'] : '0';
        $overlay = isset($atts['overlay']) ? $atts['overlay'] : '0.6';
        $is_close_link = isset($atts['is_close_link']) ? $atts['is_close_link'] : 'yes';
        $modal_bgcolor = isset($atts['modal_bgcolor']) ? $atts['modal_bgcolor'] : '#000000';
        $is_fullscreen_act = isset($atts['is_fullscreen']) ? $atts['is_fullscreen'] : 'no';
        $inactive_min  = isset($atts['inactive_min']) ? $atts['inactive_min'] : '0';
        $modaleffect  = isset($atts['modaleffect']) ? $atts['modaleffect'] : 'no_animation';
        $arf_preset_data  = isset($atts['preset_data']) ? $atts['preset_data'] : '';
        

        $desc = isset($atts['desc']) ? $atts['desc'] : addslashes(esc_html__('Click here to open Form', 'ARForms'));

        $arf_data_uniq_id = '';

        if (isset($arf_loaded_form_unique_id_array[$id]['type'][$type][$position])) {
            $arf_data_uniq_id = current($arf_loaded_form_unique_id_array[$id]['type'][$type][$position]);
            if (is_array($arf_loaded_form_unique_id_array[$id]['type'][$type][$position])) {

                array_shift($arf_loaded_form_unique_id_array[$id]['type'][$type][$position]);
            } else {


                unset($arf_loaded_form_unique_id_array[$id]['type'][$type][$position]);
            }
        } else if (isset($arf_loaded_form_unique_id_array[$id]['type'][$type])) {

            $arf_data_uniq_id = current($arf_loaded_form_unique_id_array[$id]['type'][$type]);
            if (is_array($arf_loaded_form_unique_id_array[$id]['type'][$type])) {

                array_shift($arf_loaded_form_unique_id_array[$id]['type'][$type]);
            } else {


                unset($arf_loaded_form_unique_id_array[$id]['type'][$type]);
            }
        } else {
            $arf_data_uniq_id = rand(1, 99999);
            if (empty($arf_data_uniq_id) || $arf_data_uniq_id == '') {
                $arf_data_uniq_id = $id;
            }
        }
        /* arf_dev_flag - Cornerstone Check Once */
        if(is_array($arf_data_uniq_id))
        {
            $arf_data_uniq_id = $arf_data_uniq_id[0];            
        }
        else
        {
          $arf_data_uniq_id = $arf_data_uniq_id;   
        }

       
        require_once VIEWS_PATH . '/arf_front_form.php';
        $is_navigation = (isset($atts['is_navigation'])) ? $atts['is_navigation'] : false;
        if((isset($atts['shortcode_type']) && $atts['shortcode_type'] !='') || (isset($atts['type']) && $atts['type'] !='')) {           
           $contents = ars_get_form_builder_string($id, $key, false, false, '', $arf_data_uniq_id, $desc, $type, $modal_height, $modal_width, $position, $btn_angle, $bgcolor, $txtcolor, $open_inactivity, $open_scroll, $open_delay, $overlay, $is_close_link, $modal_bgcolor,$is_fullscreen_act,$inactive_min,$modaleffect,$is_navigation,$arf_preset_data);  
        } else {
            $contents = ars_get_form_builder_string($id, $key, false, false, '', $arf_data_uniq_id,'','','','','','','','','','','','','','','','','',false,$arf_preset_data);            
        }
        $contents = apply_filters('arf_pre_display_arfomrms', $contents, $id, $key);

        return $contents;

        
    }

    function widget_text_filter($content) {


        $regex = '/\[\s*ARForms\s+.*\]/';


        return preg_replace_callback($regex, array($this, 'widget_text_filter_callback'), $content);
    }

    function widget_text_filter_callback($matches) {

        if ($matches[0]) {
            $parts = explode("id=", $matches[0]);
            $partsnew = explode(" ", $parts[1]);
            $formid = $partsnew[0];
            $formid = str_replace(']', '', $formid);
            $formid = trim($formid);
            global $arforms_loaded;
            $arforms_loaded[$formid] = true;
        }

        return do_shortcode($matches[0]);
    }

    function widget_text_filter_popup($content) {


        $regex = '/\[\s*ARForms_popup\s+.*\]/';


        return preg_replace_callback($regex, array($this, 'widget_text_filter_callback_popup'), $content);
    }

    function widget_text_filter_callback_popup($matches) {

        if ($matches[0]) {
            $parts = explode("id=", $matches[0]);
            $partsnew = explode(" ", $parts[1]);
            $formid = $partsnew[0];
            $formid = trim($formid);
            global $arforms_loaded;
            $arforms_loaded[$formid] = true;
        }

        return do_shortcode($matches[0]);
    }

    function get_postbox_class() {

        return 'postbox-container';
    }

    function set_js() {
        global $arfversion,$wp_version;
        $jquery_handler = 'jquery';
        $jq_draggable_handler = "jquery-ui-draggable";
        if( version_compare($wp_version, '4.2','<') ){
            $jquery_handler = "jquery-custom";
            $jq_draggable_handler = "jquery-ui-draggable-custom";
        }
        if ((isset($_REQUEST['page']) && $_REQUEST['page'] != '') && ($_REQUEST['page'] == "ARForms-entries" || "ARForms-popups" == $_REQUEST['page'])) {
            wp_register_script('arfbootstrap-js', ARFURL . '/bootstrap/js/bootstrap.min.js', array($jquery_handler), $arfversion);
            wp_enqueue_script('arfbootstrap-js');
            wp_enqueue_script('jquery-bootstrap-slect', ARFURL . '/bootstrap/js/bootstrap-select.js', array($jquery_handler), $arfversion);
            wp_enqueue_script($jquery_handler);
            
            wp_enqueue_script('arfhighcharts', ARFURL . '/js/highcharts/arfhighcharts.js', array(), $arfversion);
            wp_enqueue_script('arfexporting', ARFURL . '/js/highcharts/arfexporting.js', array(), $arfversion);
            wp_enqueue_script('arfmap', ARFURL . '/js/highcharts/arfmap.js', array(), $arfversion);
            wp_enqueue_script('arfdata', ARFURL . '/js/highcharts/arfdata.js', array(), $arfversion);
            wp_enqueue_script('arfworld', ARFURL . '/js/highcharts/arfworld.js', array(), $arfversion);

            wp_enqueue_script('jquery_dataTables', ARFURL . '/datatables/media/js/jquery.dataTables.js', array(), $arfversion);
            wp_enqueue_script('ColVis', ARFURL . '/datatables/media/js/ColVis.js', array(), $arfversion);
            wp_enqueue_script('FixedColumns', ARFURL . '/datatables/media/js/FixedColumns.js', array(), $arfversion);
            wp_register_script('arf_tipso', ARFURL . '/js/tipso.min.js', array($jquery_handler), $arfversion);
            wp_enqueue_script('arf_tipso');
        } elseif (isset($_REQUEST['page']) && $_REQUEST['page'] != '' && $_REQUEST['page'] == "ARForms-settings") {
            wp_register_script('arfbootstrap-js', ARFURL . '/bootstrap/js/bootstrap.min.js', array($jquery_handler), $arfversion);
            wp_enqueue_script('arfbootstrap-js');
            wp_enqueue_script('jquery-bootstrap-slect', ARFURL . '/bootstrap/js/bootstrap-select.js', array($jquery_handler), $arfversion);


            wp_register_script('arf_tipso', ARFURL . '/js/tipso.min.js', array($jquery_handler), $arfversion);
            wp_enqueue_script('arf_tipso');
            wp_register_script('arf_codemirror', ARFURL . '/js/arf_codemirror.js', array(), $arfversion);
            wp_enqueue_script('arf_codemirror');
        } elseif (isset($_REQUEST['page']) && $_REQUEST['page'] != '' && $_REQUEST['page'] == "ARForms-import-export") {
            wp_register_script('arfbootstrap-js', ARFURL . '/bootstrap/js/bootstrap.min.js', array($jquery_handler), $arfversion);
            wp_enqueue_script('arfbootstrap-js');
            wp_enqueue_script('jquery-bootstrap-slect', ARFURL . '/bootstrap/js/bootstrap-select.js', array($jquery_handler), $arfversion);

            wp_enqueue_script('form1', ARFURL . '/js/jquery.form.js', array(), $arfversion);
            wp_register_script('arf_tipso', ARFURL . '/js/tipso.min.js', array($jquery_handler), $arfversion);
            wp_enqueue_script('arf_tipso');
        } elseif (isset($_REQUEST['page']) && $_REQUEST['page'] != '' && ($_REQUEST['page'] == "ARForms" || $_REQUEST['page'] == "ARForms-license") && !isset($_REQUEST['arfaction'])) {
            wp_enqueue_script($jquery_handler);
            
            wp_enqueue_script('jquery_dataTables', ARFURL . '/datatables/media/js/jquery.dataTables.js', array(), $arfversion);
            wp_enqueue_script('ColVis', ARFURL . '/datatables/media/js/ColVis.js', array(), $arfversion);

            wp_register_script('arfbootstrap-js', ARFURL . '/bootstrap/js/bootstrap.min.js', array($jquery_handler), $arfversion);
            if ($_REQUEST['page'] == 'ARForms-license') {
                wp_enqueue_script('arfbootstrap-js');
            }
            if ($_REQUEST['page'] == 'ARForms' && isset($_REQUEST['action'])) {
                wp_enqueue_script('jquery-bootstrap-slect', ARFURL . '/bootstrap/js/bootstrap-select.js', array($jquery_handler), $arfversion);
            } else if ($_REQUEST['page'] != 'ARForms') {
                wp_enqueue_script('jquery-bootstrap-slect', ARFURL . '/bootstrap/js/bootstrap-select.js', array($jquery_handler), $arfversion);
            }


            wp_register_script('arf_tipso', ARFURL . '/js/tipso.min.js', array('jquery'), $arfversion);
            wp_enqueue_script('arf_tipso');
        } elseif (isset($_REQUEST['page']) && $_REQUEST['page'] != '' && $_REQUEST['page'] == "ARForms" && ($_REQUEST['arfaction'] == 'edit' || $_REQUEST['arfaction'] == 'new' || $_REQUEST['arfaction'] == 'duplicate' || $_REQUEST['arfaction'] == 'update')) {
            /*enqueue tinymce script */
            /*$js_src = includes_url('js/tinymce/') . 'tinymce.min.js';
            wp_register_script('tinymce_js', $js_src);
            wp_enqueue_script('tinymce_js');*/
            
            wp_enqueue_script('arforms_admin', ARFURL . '/js/arforms_admin.js', array(), $arfversion);
            wp_enqueue_script('arforms_admin_v3.0', ARFURL . '/js/arforms_admin_3.0.js', array($jquery_handler, $jq_draggable_handler), $arfversion);
            
            wp_register_script('arfbootstrap-js', ARFURL . '/bootstrap/js/bootstrap.min.js', array($jquery_handler), $arfversion);
            wp_enqueue_script('arfbootstrap-js');
            wp_enqueue_script('slideControl_new', ARFURL . '/bootstrap/js/modernizr.js', array($jquery_handler), $arfversion, true);
            wp_enqueue_script('slideControl', ARFURL . '/bootstrap/js/bootstrap-slider.js', array($jquery_handler), $arfversion, true);
            
            wp_enqueue_script('arf_js_color',ARFURL.'/js/jscolor.js',array($jquery_handler), $arfversion);
            wp_register_script('arf_tipso', ARFURL . '/js/tipso.min.js', array($jquery_handler), $arfversion);
            wp_enqueue_script('arf_tipso');
            wp_register_script('arf_codemirror', ARFURL . '/js/arf_codemirror.js', array(), $arfversion);
            wp_enqueue_script('arf_codemirror');
            
            wp_enqueue_script('arf_materialize_js', ARFURL . '/materialize/materialize.js', array(), $arfversion);
            wp_enqueue_script('arf_bootstrap_select_js', ARFURL.'/bootstrap/js/bootstrap-select.js', array(), $arfversion);
            wp_enqueue_script('bootstrap-typeahead-js', ARFURL.'/bootstrap/js/bootstrap-typeahead.js');

            wp_enqueue_script('arforms_editor_phone_utils', ARFURL . '/js/arf_phone_utils.js', array(), $arfversion, true);
            wp_enqueue_script('arforms_editor_phone_intl_input', ARFURL . '/js/intlTelInput.min.js', array(), $arfversion, true);
        }elseif (isset($_REQUEST['page']) && $_REQUEST['page'] != '' && $_REQUEST['page'] == "AROrder-entries") {
            wp_enqueue_script('arforms_admin', ARFURL . '/js/arforms_admin.js', array(), $arfversion);
        }    

        $field_type_label_array = array(
            'text' => esc_html__('Single Line Text','ARForms'),
            'textarea' => esc_html__('Multiline Text','ARForms'),
            'checkbox' => esc_html__('Checkbox','ARForms'),
            'radio' => esc_html__('Radio','ARForms'),
            'select' => esc_html__('Dropdown','ARForms'),
            'file' => esc_html__('File Upload','ARForms'),
            'email' => esc_html('Email Address','ARForms'),
            'number' => esc_html('Number','ARForms'),
            'phone' => esc_html__('Phone Number','ARForms'),
            'date' => esc_html__('Date','ARForms'),
            'time' => esc_html__('Time','ARForms'),
            'url' => esc_html__('Website/URL','ARForms'),
            'image'=> esc_html__('Image URL','ARForms'),
            'password' => esc_html__('Password','ARForms'),
            'html' => esc_html__('HTML','ARForms'),
            'divider' => esc_html__('Section','ARForms'),
            'break' => esc_html__('Page Break','ARForms'),
            'scale' => esc_html__('Star Rating','ARForms'),
            'like' => esc_html__('Like button','ARForms'),
            'arfslider' => esc_html__('Slider','ARForms'),
            'colorpicker' => esc_html__('Color Picker','ARForms'),
            'imagecontrol' => esc_html__('Image','ARForms'),
            'arf_smiley' => esc_html__('Smiley','ARForms'),
            'arf_autocomplete' => esc_html__('Autocomplete','ARForms'),
            'arf_switch' => esc_html__('Switch','ARForms'),
            'arfcreditcard' => esc_html__('Credit Card','ARForms'),
            'signature' => esc_html__('Signature','ARForms'),
            'arf_product' => esc_html__('Product','ARForms')
        );

        $field_type_label_array = apply_filters('arf_field_type_label_filter',$field_type_label_array);

        $js_data = "__ARF_FIELD_TYPE_LABELS = '".json_encode($field_type_label_array)."';";

        $cc_expiry_year_opts = array(
            array(
                "label" => "Please Select",
                "value" => ""
            )
        );

        $n = 1;
        for( $i = date('Y'); $i < date('Y',strtotime('+31 Years')); $i++ ){
            $cc_expiry_year_opts[$n]['label'] = $i;
            $cc_expiry_year_opts[$n]['value'] = $i;
            $n++;
        }

        $cc_field_data = array(
            'cc_holder_name' => array(
                "required" => "1",
                "required_indicator" =>  "*",
                "max" =>  "",
                "minlength" =>  "",
                "field_width" =>  "",
                "name" =>  "Cardholder Name",
                "blank" =>  "This field cannot be blank.",
                "minlength_message" =>  "Invalid minimum characters => ",
                "placeholdertext" =>  "",
                "description" =>  "",
                "classes" =>  "arf_1",
                "key" =>  "{arf_unique_key}",
                "inner_class" =>  "arf_1col",
                "enable_arf_prefix" =>  "",
                "arf_prefix_icon" =>  "",
                "enable_arf_suffix" =>  "",
                "arf_suffix_icon" =>  "",
                "single_custom_validation" =>  "",
                "arf_regular_expression_msg" =>  "Entered value is invalid",
                "arf_regular_expression" =>  "",
                "arf_tooltip" =>  "",
                "frm_arf_tooltip_field_indicator" =>  "",
                "tooltip_text" =>  "",
                "css_outer_wrapper" =>  "",
                "css_label" =>  "",
                "css_input_element" =>  "",
                "css_description" =>  "",
                "css_add_icon" =>  "",
                "type" =>  "text",
                "default_value" =>  "",
                "arf_enable_readonly" => "0"
            ),
            'cc_number' => array(
                "required" => "1",
                "required_indicator" => "*",
                "max" => "16",
                "minlength" => "13",
                "field_width" => "",
                "default_value" => "",
                "name" => "Card Number",
                "blank" => "This field cannot be blank.",
                "minlength_message" => "Invalid minimum characters length",
                "minnum" => "",
                "maxnum" => "",
                "invalid" => "Number is out of range",
                "placeholdertext" => "",
                "description" => "",
                "arf_tooltip" => "",
                "frm_arf_tooltip_field_indicator" => "",
                "tooltip_text" => "",
                "enable_arf_prefix" => "",
                "arf_prefix_icon" => "",
                "enable_arf_suffix" => "",
                "arf_suffix_icon" => "",
                "classes" => "arf_1",
                "inner_class" => "arf_1col",
                "key" => "{arf_unique_key}",
                "css_outer_wrapper" => "",
                "css_label" => "",
                "css_input_element" => "",
                "css_description" => "",
                "css_add_icon" => "",
                "type" => "number",
                "arf_enable_readonly" =>"0"
            ),
            'cc_expiry_month' => array(
                "required" => "1",
                "required_indicator" => "*",
                "blank" => "This field cannot be blank.",
                "field_width" => "",
                "name" => "Expiration Month",
                "description" => "",
                "default_value" => "",
                "arf_tooltip" => "",
                "frm_arf_tooltip_field_indicator" => "",
                "tooltip_text" => "",
                "classes" => "arf_3",
                "inner_class" => "arf31colclass",
                "key" => "{arf_unique_key}",
                "css_outer_wrapper" => "",
                "css_label" => "",
                "css_input_element" => "",
                "css_description" => "",
                "separate_value" => "1",
                "type" => "select",
                "options" => array(
                    array(
                        "label" => "Please Select",
                        "value" => ""
                    ),
                    array(
                        "label" => "January",
                        "value" => "01"
                    ),
                    array(
                        "label" => "February",
                        "value" => "02"
                    ),
                    array(
                        "label" => "March",
                        "value" => "03"
                    ),
                    array(
                        "label" => "April",
                        "value" => "04"
                    ),
                    array(
                        "label" => "May",
                        "value" => "05"
                    ),
                    array(
                        "label" => "June",
                        "value" => "06"
                    ),
                    array(
                        "label" => "July",
                        "value" => "07"
                    ),
                    array(
                        "label" => "August",
                        "value" => "08"
                    ),
                    array(
                        "label" => "September",
                        "value" => "09"
                    ),
                    array(
                        "label" => "October",
                        "value" => "10"
                    ),
                    array(
                        "label" => "November",
                        "value" => "11"
                    ),
                    array(
                        "label" => "December",
                        "value" => "12"
                    ),
                )
            ),
            'cc_expiry_year' => array(
                "required" => "1",
                "required_indicator" => "*",
                "blank" => "This field cannot be blank.",
                "field_width" => "",
                "name" => "Expiration Year",
                "description" => "",
                "default_value" => "",
                "arf_tooltip" => "",
                "frm_arf_tooltip_field_indicator" => "",
                "tooltip_text" => "",
                "classes" => "arf_3",
                "inner_class" => "arf_23col",
                "key" => "{arf_unique_key}",
                "css_outer_wrapper" => "",
                "css_label" => "",
                "css_input_element" => "",
                "css_description" => "",
                "separate_value" => "1",
                "type" => "select",
                "options" => $cc_expiry_year_opts
            ),
            'cc_cvc_number' => array(
                "required" => "1",
                "required_indicator" => "*",
                "max" => "4",
                "minlength" => "3",
                "field_width" => "",
                "default_value" => "",
                "name" => "CVC",
                "blank" => "This field cannot be blank.",
                "minlength_message" => "Invalid minimum characters length",
                "minnum" => "",
                "maxnum" => "",
                "invalid" => "Number is out of range",
                "placeholdertext" => "",
                "description" => "",
                "arf_tooltip" => "",
                "frm_arf_tooltip_field_indicator" => "",
                "tooltip_text" => "",
                "enable_arf_prefix" => "",
                "arf_prefix_icon" => "",
                "enable_arf_suffix" => "",
                "arf_suffix_icon" => "",
                "classes" => "arf_3",
                "inner_class" => "arf_3col",
                "key" => "{arf_unique_key}",
                "css_outer_wrapper" => "",
                "css_label" => "",
                "css_input_element" => "",
                "css_description" => "",
                "css_add_icon" => "",
                "type" => "number",
                "arf_enable_readonly" =>"0"
            )
        );

        $cc_field_options = "__ARFCCFIELDOPTIONS = '".json_encode($cc_field_data)."'; ";

        wp_add_inline_script('arforms_admin_v3.0',$js_data);
        wp_add_inline_script('arforms_admin_v3.0',$cc_field_options);
        wp_add_inline_script('arforms_admin',$cc_field_options);
    }

    function set_css() {
        global $arfversion;

        if ((isset($_REQUEST['page']) && $_REQUEST['page'] != '') && ($_REQUEST['page'] == "ARForms-entries" || "ARForms-popups"==$_REQUEST['page'])) {
            wp_register_style('arfbootstrap-css', ARFURL . '/bootstrap/css/bootstrap.css', array(), $arfversion);
            wp_enqueue_style('arfbootstrap-css');
            wp_enqueue_style('arfbootstrap-select', ARFURL . '/bootstrap/css/bootstrap-select.css', array(), $arfversion);
            wp_register_style('arf_tipso_css', ARFURL . '/css/tipso.min.css', array(), $arfversion);
            wp_enqueue_style('arf_tipso_css');

            wp_register_style('arfbootstrap-datepicker-css', ARFURL . '/bootstrap/css/bootstrap-datetimepicker.css', array(), $arfversion);
            wp_enqueue_style('arfbootstrap-datepicker-css');
        } elseif (isset($_REQUEST['page']) && $_REQUEST['page'] != '' && $_REQUEST['page'] == "ARForms-settings") {
            wp_register_style('arfbootstrap-css', ARFURL . '/bootstrap/css/bootstrap.css', array(), $arfversion);
            wp_enqueue_style('arfbootstrap-css');
            wp_enqueue_style('arfbootstrap-select', ARFURL . '/bootstrap/css/bootstrap-select.css', array(), $arfversion);
            wp_register_style('arf_tipso_css', ARFURL . '/css/tipso.min.css', array(), $arfversion);
            wp_enqueue_style('arf_tipso_css');
            wp_register_style('arf_codemirror', ARFURL . '/css/arf_codemirror.css', array(), $arfversion);
            wp_enqueue_style('arf_codemirror');
        } elseif (isset($_REQUEST['page']) && $_REQUEST['page'] != '' && $_REQUEST['page'] == "ARForms-import-export") {
            wp_register_style('arfbootstrap-css', ARFURL . '/bootstrap/css/bootstrap.css', array(), $arfversion);
            wp_enqueue_style('arfbootstrap-css');
            wp_enqueue_style('arfbootstrap-select', ARFURL . '/bootstrap/css/bootstrap-select.css', array(), $arfversion);
             wp_register_style('arf_tipso_css', ARFURL . '/css/tipso.min.css', array(), $arfversion);
            wp_enqueue_style('arf_tipso_css');
        } elseif (isset($_REQUEST['page']) && $_REQUEST['page'] != '' && ($_REQUEST['page'] == "ARForms" || $_REQUEST['page'] == "ARForms-license" ) && !isset($_REQUEST['arfaction'])) {
            wp_register_style('arf-fontawesome-css', ARFURL . '/css/font-awesome.min.css', array(), $arfversion);
            wp_register_style('arfbootstrap-css', ARFURL . '/bootstrap/css/bootstrap.css', array(), $arfversion);
            if ($_REQUEST['page'] == 'ARForms-license') {
                wp_enqueue_style('arfbootstrap-css');
                wp_enqueue_style('arfbootstrap-select', ARFURL . '/bootstrap/css/bootstrap-select.css', array(), $arfversion);
                wp_enqueue_style('arf-fontawesome-css');
            }
            wp_register_style('arf_tipso_css', ARFURL . '/css/tipso.min.css', array(), $arfversion);
            wp_enqueue_style('arf_tipso_css');
        } elseif (isset($_REQUEST['page']) && $_REQUEST['page'] != '' && $_REQUEST['page'] == "ARForms" && ($_REQUEST['arfaction'] == 'edit' || $_REQUEST['arfaction'] == 'new' || $_REQUEST['arfaction'] == 'duplicate' || $_REQUEST['arfaction'] == 'update')) {
            wp_register_style('arfbootstrap-css', ARFURL . '/bootstrap/css/bootstrap.css', array(), $arfversion);
            wp_enqueue_style('arfbootstrap-css');
            wp_register_style('arfdisplaycss_editor', ARFURL . '/css/arf_front.css', array(), $arfversion);
            wp_enqueue_style('arfdisplaycss_editor');
            wp_register_style('arfdisplayflagiconcss_editor', ARFURL . '/css/flag_icon.css', array(), $arfversion);
            wp_enqueue_style('arfdisplayflagiconcss_editor');
            wp_register_style('slideControl-css', ARFURL . '/bootstrap/css/bootstrap-slider.css', array(), $arfversion);
            wp_enqueue_style('slideControl-css');

            wp_register_style('arf_tipso_css', ARFURL . '/css/tipso.min.css', array(), $arfversion);
            wp_enqueue_style('arf_tipso_css');

            wp_register_style('arf-fontawesome-css', ARFURL . '/css/font-awesome.min.css', array(), $arfversion);
            wp_enqueue_style('arf-fontawesome-css');
            wp_register_style('arfbootstrap-datepicker-css', ARFURL . '/bootstrap/css/bootstrap-datetimepicker.css', array(), $arfversion);
            wp_enqueue_style('arfbootstrap-datepicker-css');
            wp_register_script('bootstrap-typeahead-js', ARFURL . '/bootstrap/js/bootstrap-typeahead.js', array('jquery'), $arfversion);
            wp_enqueue_style('bootstrap-typeahead-js');
            wp_register_style('arf_codemirror', ARFURL . '/css/arf_codemirror.css', array(), $arfversion);
            wp_enqueue_style('arf_codemirror');

            wp_register_style('arf_flags_css', ARFURL . '/css/flag_icon.css', array(), $arfversion);
            wp_enqueue_style('arf_flags_css');         
        }
    }

    function wp_dequeue_script_custom($handle) {
        global $wp_scripts;
        if (!is_a($wp_scripts, 'WP_Scripts'))
            $wp_scripts = new WP_Scripts();

        $wp_scripts->dequeue($handle);
    }

    function wp_dequeue_style_custom($handle) {
        global $wp_styles;
        if (!is_a($wp_styles, 'WP_Styles'))
            $wp_styles = new WP_Styles();

        $wp_styles->dequeue($handle);
    }

    function getwpversion() {
        global $arfversion, $MdlDb, $arnotifymodel, $arfform, $arfrecordmeta;
        $bloginformation = array();
        $str = $MdlDb->get_rand_alphanumeric(10);

        if (is_multisite())
            $multisiteenv = "Multi Site";
        else
            $multisiteenv = "Single Site";

        $bloginformation[] = $arnotifymodel->sitename();
        $bloginformation[] = $arfform->sitedesc();
        $bloginformation[] = home_url();
        $bloginformation[] = get_bloginfo('admin_email');
        $bloginformation[] = $arfrecordmeta->wpversioninfo();
        $bloginformation[] = $arfrecordmeta->getlanguage();
        $bloginformation[] = $arfversion;
        $bloginformation[] = $_SERVER['REMOTE_ADDR'];
        $bloginformation[] = $str;
        $bloginformation[] = $multisiteenv;

        $arnotifymodel->checksite($str);

        $valstring = implode("||", $bloginformation);
        $encodedval = base64_encode($valstring);

        $urltopost = $arfform->getsiteurl();
        $response = wp_remote_post($urltopost, array(
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(),
            'body' => array('wpversion' => $encodedval),
            'cookies' => array()
                )
        );
    }

    function arf_backup() {
        $databaseversion = get_option('arf_db_version');
        update_option('old_db_version', $databaseversion);
    }

    function upgrade_data() {
        global $newdbversion;

        if (!isset($newdbversion) || $newdbversion == ""){
            $newdbversion = get_option('arf_db_version');
        }

        if (version_compare($newdbversion, '3.7.1', '<')) {
            $path = FORMPATH . '/core/views/upgrade_latest_data.php';
            include($path);
        }
    }

    function arf_rmdirr($dirname) {

        if (!file_exists($dirname)) {
            return false;
        }


        if (is_file($dirname)) {
            return unlink($dirname);
        }


        $dir = dir($dirname);
        while (false !== $entry = $dir->read()) {

            if ($entry == '.' || $entry == '..') {
                continue;
            }


            $this->arf_rmdirr("$dirname/$entry");
        }


        $dir->close();
        return rmdir($dirname);
    }

    function arf_copyr($source, $dest) {
        global $wp_filesystem;

        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }


        if (is_file($source)) {
            return $wp_filesystem->copy($source, $dest);
        }


        if (!is_dir($dest)) {
            $wp_filesystem->mkdir($dest);
        }


        $dir = dir($source);
        while (false !== $entry = $dir->read()) {

            if ($entry == '.' || $entry == '..') {
                continue;
            }


            $this->arf_copyr("$source/$entry", "$dest/$entry");
        }


        $dir->close();
        return true;
    }

    function arf_hide_update_notice_to_all_admin_users() {
        global $pagenow;

        if (isset($_GET) and ( isset($_GET['page']) and preg_match('/ARForms*/', $_GET['page'])) or ( $pagenow == 'edit.php' and isset($_GET) and isset($_GET['post_type']) and $_GET['post_type'] == 'frm_display')) {
            remove_all_actions('network_admin_notices', 10000);
            remove_all_actions('user_admin_notices', 10000);
            remove_all_actions('admin_notices', 10000);
            remove_all_actions('all_admin_notices', 10000);
        }
    }

    function arf_export_form_data() {

        if (isset($_POST['s_action']) && !in_array($_POST['s_action'], array('opt_export_form', 'opt_export_both'))) {
            return false;
        }

        global $wpdb, $submit_bg_img, $arfmainform_bg_img, $form_custom_css, $WP_Filesystem, $submit_hover_bg_img, $MdlDb,$arformcontroller;

        $arf_db_version = get_option('arf_db_version');

        $wp_upload_dir = wp_upload_dir();
        $upload_dir = $wp_upload_dir['basedir'] . '/arforms/';
        $upload_baseurl = $wp_upload_dir['baseurl'] . '/arforms/';
        $form_id_req = (isset($_REQUEST['is_single_form']) && $_REQUEST['is_single_form'] == 1) ? $_REQUEST['frm_add_form_id_name'] : (isset($_REQUEST['frm_add_form_id']) ? $_REQUEST['frm_add_form_id'] : '');

        if (isset($_REQUEST['export_button'])) {
            if (!empty($form_id_req)) {
                if($_REQUEST['is_single_form'] == 1 )
                {
                    $form_ids = $_REQUEST['frm_add_form_id_name'];
                }
                else{
                    $arf_frm_add_form_id =  $_REQUEST['frm_add_form_id'];
                    $arf_frm_add_form_ids = array();
                    if(count($arf_frm_add_form_id)>0 && is_array($arf_frm_add_form_id))
                    {
                        foreach ($arf_frm_add_form_id as $arf_frm_add_form_id_key => $arf_frm_add_form_id_value) {
                            if($arf_frm_add_form_id_value!='')
                            {
                                $arf_frm_add_form_ids[] = $arf_frm_add_form_id_value;
                            }
                        }
                    }
                    $form_ids = (count($arf_frm_add_form_ids) > 0) ? implode(",", $arf_frm_add_form_ids) : '';
                }
                
                $res = $wpdb->get_results("SELECT * FROM " . $MdlDb->forms . " WHERE id in (" . $form_ids . ")");

                if( !is_array($form_ids) && empty($res) ){
                    
                }

                $file_name = "ARForms_" . time();

                $filename = $file_name . ".txt";

                

                $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

                $xml .= "<forms>\n";

                foreach ($res as $key => $result_array) {

                    $form_id = $res[$key]->id;

                    $xml .= "\t<form id='" . $res[$key]->id . "'>\n";

                    $xml .= "\t<site_url>" . site_url() . "</site_url>\n";

                    $xml .= "\t<exported_site_uploads_dir>" . $upload_baseurl . "</exported_site_uploads_dir>\n";

                    $xml .= "\t<arf_db_version>" . $arf_db_version . "</arf_db_version>\n";

                    $xml .= "\t\t<general_options>\n";
                    foreach ($result_array as $key => $value) {

                        if ($key == 'options') {
                            foreach (unserialize($value) as $ky => $vl) {
                                if ($ky != 'before_html') {
                                    if (!is_array($vl)) {
                                        if ($ky == 'success_url') {
                                            $new_field[$ky] = $vl;

                                            $new_field[$ky] = str_replace('&amp;', '[AND]', $new_field[$ky]);
                                        } else if ($ky == 'form_custom_css') {
                                            $form_custom_css = str_replace(site_url(), '[REPLACE_SITE_URL]', $vl);

                                            $form_custom_css = str_replace('&lt;br /&gt;', '[ENTERKEY]', str_replace('&lt;br/&gt;', '[ENTERKEY]', str_replace('&lt;br&gt;', '[ENTERKEY]', str_replace('<br />', '[ENTERKEY]', str_replace('<br/>', '[ENTERKEY]', str_replace('<br>', '[ENTERKEY]', trim(preg_replace('/\s\s+/', '[ENTERKEY]', $form_custom_css))))))));
                                        } else if ($ky == 'arf_form_other_css') {
                                            $new_field[$ky] = str_replace('&lt;br /&gt;', '[ENTERKEY]', str_replace('&lt;br/&gt;', '[ENTERKEY]', str_replace('&lt;br&gt;', '[ENTERKEY]', str_replace('<br />', '[ENTERKEY]', str_replace('<br/>', '[ENTERKEY]', str_replace('<br>', '[ENTERKEY]', trim(preg_replace('/\s\s+/', '[ENTERKEY]', str_replace(site_url(), '[REPLACE_SITE_URL]', $vl)))))))));
                                        } else {
                                            $string = ( ( is_array($vl) and count($vl) > 0 ) ? $vl : str_replace('&lt;br /&gt;', '[ENTERKEY]', str_replace('&lt;br/&gt;', '[ENTERKEY]', str_replace('&lt;br&gt;', '[ENTERKEY]', str_replace('<br />', '[ENTERKEY]', str_replace('<br/>', '[ENTERKEY]', str_replace('<br>', '[ENTERKEY]', trim(preg_replace('/\s\s+/', '[ENTERKEY]', $vl)))))))) );

                                            $new_field[$ky] = $string;
                                        }
                                    } else
                                        $new_field[$ky] = $vl;
                                }
                                else {
                                    $vl2 = '[REPLACE_BEFORE_HTML]';
                                    $new_field[$ky] = $vl2;
                                }
                            }
                            $value1 = serialize($new_field);

                            $value1 = "<![CDATA[" . $value1 . "]]>";

                            $xml .= "\t\t\t<$key>";


                            $xml .= "$value1";


                            $xml .= "</$key>\n";
                        } elseif ($key == 'form_css') {
                            $form_css_arry = maybe_unserialize($value);
                            foreach ($form_css_arry as $form_css_key => $form_css_val) {
                                if ($form_css_key == "submit_bg_img") {
                                    $submit_bg_img = $form_css_val;
                                } else if ($form_css_key == "submit_hover_bg_img") {
                                    $submit_hover_bg_img = $form_css_val;
                                } elseif ($form_css_key == "arfmainform_bg_img") {
                                    $arfmainform_bg_img = $form_css_val;
                                }
                            }

                            $xml .= "\t\t\t<$key>";

                            $xml .= "<![CDATA[" . $value . "]]>";

                            //and close the element
                            $xml .= "</$key>\n";
                        } else if ($key == "description" || $key == "name") {
                            $value = "<![CDATA[" . $value . "]]>";

                            $xml .= "\t\t\t<$key>";

                            //embed the SQL data in a CDATA element to avoid XML entity issues
                            $xml .= "$value";

                            //and close the element
                            $xml .= "</$key>\n";
                        } else {
                            $xml .= "\t\t\t<$key>";

                            //embed the SQL data in a CDATA element to avoid XML entity issues
                            $xml .= "$value";

                            //and close the element
                            $xml .= "</$key>\n";
                        }
                    }
                    $xml .= "\t\t</general_options>\n";


                    $xml .= "\t\t<fields>\n";

                    $res_fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->fields . " WHERE form_id = %d",$result_array->id));

                    foreach ($res_fields as $key_fields => $result_field_array) {
                        $xml .= "\t\t\t<field>\n";
                        $field_options_array = array();
                        $new_field1 = array();
                        foreach ($result_field_array as $key_field => $value_field) {
                            if ($key_field == 'field_options') {
                                $field_options_array = json_decode($value_field);
                                if (json_last_error() == JSON_ERROR_NONE) {
                                    
                                } else {
                                    $field_options_array = maybe_unserialize($value_field);
                                }
                                
                                foreach ($field_options_array as $ky => $vl) {
                                    if ($ky != 'custom_html') {
                                        if(is_object($vl))
                                        {
                                            $vl = $arformcontroller->arfObjtoArray($vl);
                                        }
                                        $vl = ( (is_array($vl) and count($vl) > 0 ) ? $vl : str_replace('&lt;br /&gt;', '[ENTERKEY]', str_replace('&lt;br/&gt;', '[ENTERKEY]', str_replace('&lt;br&gt;', '[ENTERKEY]', str_replace('<br />', '[ENTERKEY]', str_replace('<br/>', '[ENTERKEY]', str_replace('<br>', '[ENTERKEY]', trim(preg_replace('/\s\s+/', '[ENTERKEY]', $vl)))))))) );

                                        $new_field1[$ky] = $vl;
                                    }
                                }
                                $value_field_ser = serialize($new_field1);

                                $value_field_ser = "<![CDATA[" . $value_field_ser . "]]>";

                                $xml .= "\t\t\t\t<$key_field>";


                                $xml .= "$value_field_ser";


                                $xml .= "</$key_field>\n";
                            } elseif ($key_field == 'conditional_logic') {
                                $conditional_logic_array = maybe_unserialize($value_field);
                                if (is_array($conditional_logic_array)) {
                                    foreach ($conditional_logic_array as $ky_cl => $vl_cl) {
                                        $new_field_cl[$ky_cl] = $vl_cl;
                                    }
                                    $new_field_cl1 = serialize($new_field_cl);
                                    $xml .= "\t\t\t\t<$key_field>";


                                    $new_field_cl1 = "<![CDATA[" . $new_field_cl1 . "]]>";

                                    $xml .= "$new_field_cl1";


                                    $xml .= "</$key_field>\n";
                                }
                            } else {
                                if ($key_field == "description" || $key_field == "name" || $key_field == "default_value") {
                                    $vl1 = "<![CDATA[" . stripslashes_deep($value_field). "]]>";
                                } elseif ($key_field == "options" && $result_field_array->type == 'radio') {
                                    $vl1 = $value_field;
                                } else if ($key_field == "options") {
                                    $vl1 = "<![CDATA[" . $value_field . "]]>";
                                } else {
                                    $vl1 = $value_field;
                                }

                                $xml .= "\t\t\t\t<$key_field>";

                                //embed the SQL data in a CDATA element to avoid XML entity issues
                                $xml .= "$vl1";


                                //and close the element
                                $xml .= "</$key_field>\n";
                            }
                        }
                        $xml .= "\t\t\t</field>\n";
                    }
                    $xml .= "\t\t</fields>\n";

                    $xml .= "\t\t<autoresponder>\n";

                    $res_ar = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->ar . " WHERE frm_id = %d",$result_array->id));

                    foreach ($res_ar as $key_ar => $result_ar_array) {
                        foreach ($result_ar_array as $key_ar => $value_ar) {
                            if ($key_ar == 'aweber' || $key_ar == 'mailchimp' || $key_ar == 'getresponse' || $key_ar == 'gvo' || $key_ar == 'ebizac' || $key_ar == 'madmimi' || $key_ar == 'icontact' || $key_ar == 'constant_contact' || $key_ar == 'infusionsoft' || $key_ar == 'mailerlite') {

                                $xml .= "\t\t\t\t<$key_ar>\n";

                                if ($value_ar != "") {
                                    foreach (maybe_unserialize($value_ar) as $autores_key => $autores_val) {

                                        $xml .= "\t\t\t\t\t<$autores_key>";

                                        $autores_val = "<![CDATA[" . $autores_val . "]]>";


                                        $xml .= "$autores_val";


                                        $xml .= "</$autores_key>\n";
                                    }
                                }

                                $xml .= "\t\t\t\t</$key_ar>\n";
                            } else {

                                $xml .= "\t\t\t\t<$key_ar>";

                                $value_ar = "<![CDATA[" . $value_ar . "]]>";


                                $xml .= "$value_ar";


                                $xml .= "</$key_ar>\n";
                            }
                        }
                    }
                    $xml .= "\t\t</autoresponder>\n";

                    $xml .= "\t\t<submit_bg_img>";


                    $xml .= "$submit_bg_img";


                    $xml .= "</submit_bg_img>\n";


                    $xml .= "\t\t<submit_hover_bg_img>";


                    $xml .= "$submit_hover_bg_img";


                    $xml .= "</submit_hover_bg_img>\n";


                    $xml .= "\t\t<arfmainform_bg_img>";


                    $xml .= "$arfmainform_bg_img";


                    $xml .= "</arfmainform_bg_img>\n";

                    $xml .= "\t\t<form_custom_css>";


                    $xml .= "$form_custom_css";


                    $xml .= "</form_custom_css>\n";

                    /* Exporting Form Entries */
                    if ($_REQUEST['opt_export'] == 'opt_export_both') {

                        global $wpdb, $arfform, $arffield, $db_record, $style_settings, $armainhelper, $arfieldhelper, $arrecordhelper;

                        $form = $arfform->getOne($form_id);

                        $form_name = sanitize_title_with_dashes($form->name);

                        $form_cols = $arffield->getAll("fi.type not in ('divider', 'captcha', 'break', 'html', 'imagecontrol') and fi.form_id=" . $form->id, 'ORDER BY id');

                        $entry_id = $armainhelper->get_param('entry_id', false);

                        $where_clause = "it.form_id=" . (int) $form_id;

                        $wp_date_format = apply_filters('arfcsvdateformat', 'Y-m-d H:i:s');

                        if ($entry_id) {


                            $where_clause .= " and it.id in (";


                            $entry_ids = explode(',', $entry_id);


                            foreach ((array) $entry_ids as $k => $it) {


                                if ($k)
                                    $where_clause .= ",";


                                $where_clause .= $it;


                                unset($k);


                                unset($it);
                            }

                            $where_clause .= ")";
                        }else if (!empty($search)) {
                            $where_clause = $arrecordcontroller->get_search_str($where_clause, $search, $form_id, $fid);
                        }

                        $where_clause = apply_filters('arfcsvwhere', $where_clause, compact('form_id'));

                        $entries = $db_record->getAll($where_clause, '', '', true, false);

                        $form_cols = apply_filters('arfpredisplayformcols', $form_cols, $form->id);
                        $entries = apply_filters('arfpredisplaycolsitems', $entries, $form->id);
                        $to_encoding = isset($style_settings->csv_format) ? $style_settings->csv_format : 'UTF-8';

                        $xml .= "\n\t\t<form_entries>\n";

                        foreach ($entries as $entry) {

                            global $wpdb, $MdlDb;

                            $get_form_submit_type = $wpdb->get_results($wpdb->prepare("SELECT entry_value FROM " . $MdlDb->entry_metas . " WHERE entry_id = %d and field_id = %d", $entry->id, 0), 'ARRAY_A');

                            $form_submit_type = $get_form_submit_type[0]['entry_value'];

                            $res_data = $wpdb->get_results($wpdb->prepare('SELECT country, browser_info FROM ' . $MdlDb->entries . ' WHERE id = %d', $entry->id), 'ARRAY_A');

                            $entry->country = $res_data[0]['country'];
                            $entry->browser = $res_data[0]['browser_info'];

                            $i = 0;
                            $size_of_form_cols = count($form_cols);

                            $list = '';

                            $xml .= "\n\t\t\t<form_entry>\n";

                            foreach ($form_cols as $col) {

                                $field_value = isset($entry->metas[$col->id]) ? $entry->metas[$col->id] : false;

                                if (!$field_value and $entry->attachment_id) {

                                    $col->field_options = maybe_unserialize($col->field_options);
                                }


                                if ($col->type == 'file') {

                                    $old_entry_values = explode('|', $field_value);
                                    $new_field_value = array();

                                    foreach ($old_entry_values as $old_entry_val) {
                                        $new_field_value[] = str_replace('thumbs/', '', wp_get_attachment_url($old_entry_val));
                                    }
                                    $new_field_value = implode('|', $new_field_value);
                                    $field_value = $new_field_value;
                                } else if ($col->type == 'date') {

                                    $field_value = $arfieldhelper->get_date($field_value, $wp_date_format);
                                } else {

                                    $checked_values = maybe_unserialize($field_value);

                                    $checked_values = apply_filters('arfcsvvalue', $checked_values, array('field' => $col));

                                    if (is_array($checked_values)) {

                                        if( in_array($col->type,array('checkbox','radio','select','arf_autocomplete')) ){
                                            $field_value = implode('^|^', $checked_values);
                                        } else {
                                            $field_value = implode(',', $checked_values);
                                        }

                                    } else {


                                        $field_value = $checked_values;
                                    }

                                    $charset = get_option('blog_charset');

                                    $field_value = $arrecordhelper->encode_value($field_value, $charset, $to_encoding);


                                    $field_value = str_replace('"', '""', stripslashes($field_value));
                                }


                                $field_value = str_replace(array("\r\n", "\r", "\n"), ' <br />', $field_value);

                                if ($size_of_form_cols == $i) {  // - 1
                                    $list .= $field_value;
                                } else
                                    $list .= $field_value . ',';

                                $col_name = str_replace(' ', '_ARF_', $col->name);

                                $col_name = str_replace('/', '_ARF_SLASH_', $col_name);

                                $col_name = str_replace('&','&amp;',$col_name);

                                $col_name = str_replace('"','&quot;',$col_name);

                                $xml .= "\t\t\t\t<ARF_Field field_label=\"".$col_name."\" field_type='$col->type'>";

                                $xml .= "<![CDATA[" . $field_value . "]]>";

                                $xml .= "</ARF_Field>\n";
                                
                                unset($col);
                                unset($field_value);

                                $i++;
                            }
                            $formatted_date = date($wp_date_format, strtotime($entry->created_date));
                            $xml .= "\t\t\t\t<ARF_Field field_label='Created_ARF_Date'><![CDATA[{$formatted_date}]]></ARF_Field>";
                            $xml .= "\n\t\t\t\t<ARF_Field field_label='IP_ARF_Address'><![CDATA[{$entry->ip_address}]]></ARF_Field>";
                            $xml .= "\n\t\t\t\t<ARF_Field field_label='Entry_ARF_id'><![CDATA[{$entry->id}]]></ARF_Field>";
                            $xml .= "\n\t\t\t\t<ARF_Field field_label='Country'><![CDATA[{$entry->country}]]></ARF_Field>";
                            $xml .= "\n\t\t\t\t<ARF_Field field_label='Browser'><![CDATA[{$entry->browser}]]></ARF_Field>";

                            $xml .= "\n\t\t\t</form_entry>";
                            unset($entry);
                        }

                        $xml .= "\n\t\t</form_entries>\n";
                    }

                    /* Exporting Form Entries */

                    $xml .= "\t</form>\n\n";
                }
                $xml .= "</forms>";

                $xml = base64_encode($xml);

                ob_start();
                ob_clean();
                header("Content-Type: plain/text");
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header("Pragma: no-cache");
                print($xml);
                exit;
            }
        }
    }

    function Create_zip($source, $destination, $destindir) {
        $filename = array();
        $filename = unserialize($source);

        $zip = new ZipArchive();
        if ($zip->open($destination, ZipArchive::CREATE) === TRUE) {
            $i = 0;
            foreach ($filename as $file) {
                $zip->addFile($destindir . $file, $file);
                $i++;
            }
            $zip->close();
        }

        foreach ($filename as $file1) {
            unlink($destindir . $file1);
        }
    }

    function arf_front_assets() {
        global $arfsettings,$arfversion;
        if (!isset($arfsettings)) {
            $arfsettings_new = get_option('arf_options');
        } else {
            $arfsettings_new = $arfsettings;
        }

        if (isset($arfsettings_new->arfmainformloadjscss) && $arfsettings_new->arfmainformloadjscss == 1) {
            wp_enqueue_script('arfbootstrap-inputmask');
            wp_enqueue_script('jquery-maskedinput');
            wp_enqueue_script('arforms_phone_utils');
            wp_enqueue_script('arforms');
            wp_enqueue_script('arf-conditional-logic-js');
            wp_enqueue_script('arf-modal-js');
            wp_enqueue_style('arfdisplaycss');
            wp_enqueue_style('arfdisplayflagiconcss');
            wp_enqueue_script('jquery-validation');
            if(!empty($arfsettings_new->arf_load_js_css))
            {
                if(in_array('slider',$arfsettings_new->arf_load_js_css))
                {
                    wp_enqueue_script('arfbootstrap-js');
                    wp_enqueue_style('arfbootstrap-css');
                    wp_enqueue_script('arfbootstrap-modernizr-js');
                    wp_enqueue_script('arfbootstrap-slider-js');
                    wp_enqueue_style('arfbootstrap-slider');
                }
                if(in_array('colorpicker',$arfsettings_new->arf_load_js_css))
                {
                    wp_enqueue_script('arf_js_color');
                    wp_enqueue_script('arf-colorpicker-basic-js');
                    
                }
                if(in_array('dropdown',$arfsettings_new->arf_load_js_css))
                {
                    wp_enqueue_script('arfbootstrap-js');
                    wp_enqueue_script('jquery-bootstrap-slect');
                    wp_enqueue_style('arfbootstrap-css');
                    wp_enqueue_style('arfbootstrap-select');
                    
                }
                if(in_array('file',$arfsettings_new->arf_load_js_css))
                {
                    wp_enqueue_script('filedrag');
                    wp_enqueue_style('arf-filedrag');
                }
                if(in_array('date_time',$arfsettings_new->arf_load_js_css))
                {
                    wp_enqueue_script('arfbootstrap-js');
                    wp_enqueue_style('arfbootstrap-css');
                    wp_enqueue_script('bootstrap-locale-js');
                    wp_enqueue_script('bootstrap-datepicker');
                    wp_enqueue_style('arfbootstrap-datepicker-css');
                }
                if(in_array('autocomplete',$arfsettings_new->arf_load_js_css)){
                    wp_enqueue_script('arfbootstrap-js');
                    wp_enqueue_style('arfbootstrap-css');
                    wp_enqueue_script('bootstrap-typeahead-js');
                }
                if(in_array('fontawesome',$arfsettings_new->arf_load_js_css)){
                    wp_enqueue_style('arf-fontawesome-css');
                }
                if(in_array('mask_input',$arfsettings_new->arf_load_js_css)){
                    wp_enqueue_script('arfbootstrap-js');
                    wp_enqueue_style('arfbootstrap-css');
                    wp_enqueue_script('arfbootstrap-inputmask');
                    wp_enqueue_script('jquery-maskedinput');
                }
                if(in_array('tooltip',$arfsettings_new->arf_load_js_css)){
                    wp_enqueue_script('arf_tipso_js_front');
                    wp_enqueue_style('arf_tipso_css_front');
                }
                if(in_array('animate_number',$arfsettings_new->arf_load_js_css)){
                    wp_enqueue_script('animate-numbers');
                }
                if(in_array('material',$arfsettings_new->arf_load_js_css) ){
                    wp_enqueue_style('arf_materialize_css', ARFURL.'/materialize/materialize.css',array(),$arfversion);
                    wp_enqueue_script('arf_materialize_js', ARFURL.'/materialize/materialize.js',array(),$arfversion);
                }
                
            }
        }
    }

    function arf_print_all_admin_scripts() {
        global $arfversion,$wp_version;
        
        $jquery_handler = 'jquery';
        $jq_draggable_handler = "jquery-ui-draggable";
        if( version_compare($wp_version, '4.2','<') ){
            $jquery_handler = "jquery-custom";
            $jq_draggable_handler = "jquery-ui-draggable-custom";
        }
        wp_register_script('arf_tipso_ajax', ARFURL . '/js/tipso.min.js', array($jquery_handler), $arfversion);
        wp_print_scripts('arf_tipso_ajax');
	    /* enqueue tinymce script */
        /*$js_src = includes_url('js/tinymce/') . 'tinymce.min.js';
        wp_register_script('tinymce_js', $js_src);
        wp_enqueue_script('tinymce_js');*/

        wp_register_script('arf_admin_js_ajax', ARFURL . '/js/arforms_admin.js', array(), $arfversion);
        wp_print_scripts('arf_admin_js_ajax');

        wp_register_script('arf_admin_js_ajax_v3.0', ARFURL . '/js/arforms_admin_3.0.js', array($jquery_handler, $jq_draggable_handler), $arfversion);
        wp_print_scripts('arf_admin_js_ajax_v3.0');

        wp_register_script('arfbootstrap-js-ajax', ARFURL . '/bootstrap/js/bootstrap.min.js', array($jquery_handler), $arfversion);
        wp_print_scripts('arfbootstrap-js-ajax');

        wp_register_script('slideControl_new_ajax', ARFURL . '/bootstrap/js/modernizr.js', array($jquery_handler), $arfversion, true);
        wp_print_scripts('slideControl_new_ajax');

        wp_register_script('slideControl_ajax', ARFURL . '/bootstrap/js/bootstrap-slider.js', array($jquery_handler), $arfversion, true);
        wp_print_scripts('slideControl_ajax');

        wp_register_script('arf_codemirror_ajax', ARFURL . '/js/arf_codemirror.js', array(), $arfversion);
        wp_print_scripts('arf_codemirror_ajax');

        if(version_compare($wp_version, '4.2', '<')){
            wp_print_scripts('jquery-ui-widget-custom');
            wp_print_scripts('jquery-ui-mouse-custom');

            wp_print_scripts('jquery-ui-sortable-custom');
            wp_print_scripts('jquery-ui-draggable-custom');
            wp_print_scripts('jquery-ui-resizable-custom');
        } else {
            wp_print_scripts('jquery-ui-sortable');

            wp_print_scripts('jquery-ui-draggable');
        }

        wp_print_scripts('admin-widgets');

        wp_print_scripts('widgets');

        wp_register_script('arfjquery-json-ajax', ARFURL . '/js/jquery/jquery.json-2.4.js', array($jquery_handler), $arfversion);
        if (isset($_REQUEST['page']) && $_REQUEST['page'] != '' && $_REQUEST['page'] == 'ARForms' && isset($_REQUEST['arfaction']) && $_REQUEST['arfaction'] != '') {
            wp_print_scripts('arfjquery-json-ajax');
        }

        wp_register_script('arfbootstrap-select', ARFURL . '/bootstrap/js/bootstrap-select.js', array($jquery_handler), $arfversion);
        wp_print_scripts('arfbootstrap-select');

    }

    function changes_export_entry_separator(){
        $separator =  $_REQUEST['separator'];
        update_option( 'arf_form_entry_separator', $separator );
    }

    /* Cornerstone Methods */

    function arforms_cs_register_element() {
        cornerstone_register_element('ARForms_CS', 'arforms-cs', ARF_CSDIR . '/includes/arforms-cs');
    }

    function arforms_cs_icon_map($icon_map) {
        $icon_map['ARFORMS'] = ARF_CSURL . '/assets/svg/ar_forms.svg';
        return $icon_map;
    }

    /* Cornerstone Methods */

    function arf_add_new_version_release_note() {
        global $wp, $wpdb, $pagenow, $arfajaxurl, $plugin_slug, $wp_version, $maincontroller, $arfversion;;
        
        $popupData = '';
        $arf_slugs = array('ARForms', 'ARForms-entries', 'ARForms-settings', 'ARForms-import-export', 'ARForms-addons');

        if (isset($_REQUEST['page']) && in_array($_REQUEST['page'], (array) $arf_slugs)) {

            $show_document_video = get_option('arf_new_version_installed', 0);

            if ($show_document_video == '0') {
                return;
            }

            $popupData = '<div class="arf_modal_overlay arfactive">
                <div class="arf_whatsnew_popup_container_wrapper">
                    <div class="arf_popup_container arf_popup_container_whatnew_model arf_view_whatsnew_modal arfactive arf_whatsnew_model_larger">
                        <div class="arf_popup_container_header">'.esc_html__("What's New in ARForms", "ARForms"). ' '.$arfversion.'</div>
                        <div class="arfwhatsnew_modal_content arf_whatsnew_popup_content_container">

                            <div class="arf_whatsnew_popup_row">
                                <div class="arf_whatsnew_popup_inner_content">

                                    You can always refer our online documentation for all the features <a href="https://www.arformsplugin.com/documentation/1-getting-started-with-arforms/" target="_blank">here</a><br>
                                        <ul style="list-style-type: disc;">
                                            <li> Minor bug fixes </li>
                                        </ul>
                                </div>';

                    

            $arf_addon_list_api_url = "https://www.arformsplugin.com/addonlist/arf_addon_api_details.php";

            $args = array(
                'slug' => $plugin_slug,
                'version' => $arfversion,
                'other_variables' => $maincontroller->arf_get_remote_post_params(),
            );
            $arf_addon_list_api_request_str = array(
                'body' => array(
                    'action' => 'plugin_new_version_check',
                    'request' => serialize($args),
                    'api-key' => md5(home_url())
                ),
                'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url()
            );
            //$arf_addon_list_api_request_str = array();
            $arf_addon_raw_response_json = wp_remote_post($arf_addon_list_api_url, $arf_addon_list_api_request_str);
            if ( !is_wp_error( $arf_addon_raw_response_json ) ) 
            {
                $arf_addon_raw_response_json = $arf_addon_raw_response_json['body'];
                $arf_addon_raw_response = json_decode($arf_addon_raw_response_json,true);
                $count_arf_addon_raw_response = count($arf_addon_raw_response);
                if(!empty($arf_addon_raw_response) && $count_arf_addon_raw_response>0)
                {
                    $arf_list_addon_width = (142)*($count_arf_addon_raw_response);
                    $popupData .= '<div class="arf_whatsnew_addons_list_title">' . addslashes(esc_html__('Available Add-ons', "ARForms")) . '</div>';
                    $popupData .= '<div class="arf_whatsnew_addons_list_div" style="min-height:165px;">';
                    $popupData .= '<div class="arf_whatsnew_addons_list" style="width:'.$arf_list_addon_width.'px;min-width:100%;">';

                    foreach($arf_addon_raw_response as $arf_addon_raw_key => $arf_addon_raw)
                    {
                        $popupData .= '<div class="arf_whatsnew_add_on"><a href="'.$arf_addon_raw['arf_plugin_link'].'" target="_blank"><img src="' . $arf_addon_raw['arf_plugin_image'] . '" width="82" height="82" /></a><div class="arf_whatsnew_add_on_text"><a href="'.$arf_addon_raw['arf_plugin_link'].'" target="_blank">'.$arf_addon_raw['arf_plugin_name'].'</a></div></div>';
                    }

                    $popupData .= '</div>';
                    $popupData .= '</div>';
                }
            }

                    $popupData .= '</div></div>
                        <div class="arf_popup_footer arf_view_whatsnew_modal_footer">
                            <button class="rounded_button arf_btn_dark_blue" style="margin-right:7px;" name="arf_update_whatsnew_button" onclick="arf_hide_update_notice();">'. esc_html__('OK','ARForms').'</button>
                        </div>
                    </div>
                </div>
            </div>';

            $popupData .= '<script type="text/javascript">';
            $popupData .= 'jQuery(document).ready(function(){ jQuery("html").css("overflow","hidden");  });';
            $popupData .= 'function arf_hide_update_notice(){
                var ishide = 1;
                jQuery.ajax({
                type: "POST",
                url: "'.$arfajaxurl.'",
                data: "action=arf_dont_show_upgrade_notice&is_hide=" + ishide,
                success: function (res) {
                        jQuery(".arf_view_whatsnew_modal.arfactive").parents(".arf_modal_overlay.arfactive").removeClass("arfactive");
                        jQuery(".arf_view_whatsnew_modal.arfactive").removeClass("arfactive");
                        jQuery("html").css("overflow",""); 
                        return false;
                        
                }
                });
                return false;
            }';
            $popupData .= '</script>';
            echo $popupData;
        }
    }

    function arf_dont_show_upgrade_notice() {
        global $wp, $wpdb;
        delete_option('arf_new_version_installed');
        die();
    }

    function arf_check_valid_file($file_content = ''){
        if( '' == $file_content ){
            return true;
        }

        $arf_valid_pattern = '/(\<\?(php))/';

        if( preg_match($arf_valid_pattern,$file_content) ){
            return false;
        }

        return true;
    }
}
