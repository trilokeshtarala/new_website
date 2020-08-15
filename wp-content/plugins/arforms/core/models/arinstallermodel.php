<?php

class arinstallermodel {

    var $fields;
    var $forms;
    var $entries;
    var $entry_metas;
    var $autoresponder;
    var $ar;
    var $view;
    var $form_popup;

    function __construct() {
        global $wpdb,$blog_id;

    	if ($blog_id and IS_WPMU) {
            $prefix = $wpdb->get_blog_prefix($blog_id);

    	    $this->fields = "{$prefix}arf_fields";
    	    $this->forms = "{$prefix}arf_forms";
    	    $this->entries = "{$prefix}arf_entries";
    	    $this->entry_metas = "{$prefix}arf_entry_values";
    	    $this->autoresponder = "{$prefix}arf_autoresponder";
    	    $this->ar = "{$prefix}arf_ar";
    	    $this->views = "{$prefix}arf_views";
            $this->form_popup = "{$prefix}arf_popup_forms";

        } else {
    	    $this->fields = $wpdb->prefix . "arf_fields";
    	    $this->forms = $wpdb->prefix . "arf_forms";
    	    $this->entries = $wpdb->prefix . "arf_entries";
    	    $this->entry_metas = $wpdb->prefix . "arf_entry_values";
    	    $this->autoresponder = $wpdb->prefix . "arf_autoresponder";
    	    $this->ar = $wpdb->prefix . "arf_ar";
    	    $this->views = $wpdb->prefix . "arf_views";
            $this->form_popup = $wpdb->prefix . "arf_popup_forms";
    	}

    }

    function upgrade($old_db_version = false) {


        global $wpdb, $arfdbversion;


        $old_db_version = (float) $old_db_version;


        if (!$old_db_version) {
            $old_db_version = get_option('arf_db_version');
        }

        if ($arfdbversion != $old_db_version) {

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            $charset_collate = '';

            if ($wpdb->has_cap('collation')) {


                if (!empty($wpdb->charset))
                    $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";


                if (!empty($wpdb->collate))
                    $charset_collate .= " COLLATE $wpdb->collate";
            }



            $sql = "CREATE TABLE IF NOT EXISTS {$this->fields} (

                id int(11) NOT NULL auto_increment,

                field_key varchar(25) default NULL,

                name text default NULL,

                type varchar(50) default NULL,

                options longtext default NULL,

                required int(1) default NULL,

                field_options longtext default NULL,

                form_id int(11) default NULL,

                created_date datetime NOT NULL,

		        conditional_logic tinyint(1) default 0,

                enable_running_total longtext default NULL,

                option_order text default NULL,

                PRIMARY KEY  (id),


                KEY form_id (form_id),


                UNIQUE KEY field_key (field_key)


              ) {$charset_collate};";





		   dbDelta( $sql );

		   if($wpdb->last_error !== '') { update_option('ARF_ERROR_'.time().rand(),"ERROR===>".htmlspecialchars( $wpdb->last_result, ENT_QUOTES )."QUERY===>".htmlspecialchars( $wpdb->last_query, ENT_QUOTES )); }


            $sql = "CREATE TABLE IF NOT EXISTS {$this->forms} (

                id int(11) NOT NULL auto_increment,

                form_key varchar(25) default NULL,

                name varchar(255) default NULL,

                description text default NULL,

                is_template boolean default 0,

                status varchar(25) default NULL,

                options longtext default NULL,

                created_date datetime NOT NULL,

        		autoresponder_fname int(11),

        		autoresponder_lname int(11),

        		autoresponder_email int(11),

        		columns_list text default NULL,

        		form_css longtext default NULL,

                temp_fields longtext default NULL,

                arf_mapped_addon longtext default NULL,

                PRIMARY KEY  (id),


                UNIQUE KEY form_key (form_key)


              ) {$charset_collate};";


                dbDelta( $sql );

            if($wpdb->last_error !== '') { update_option('ARF_ERROR_'.time().rand(),"ERROR===>".htmlspecialchars( $wpdb->last_result, ENT_QUOTES )."QUERY===>".htmlspecialchars( $wpdb->last_query, ENT_QUOTES )); }

            $sql = "CREATE TABLE IF NOT EXISTS {$this->entries} (

                id int(11) NOT NULL auto_increment,

                entry_key varchar(25) default NULL,

                name varchar(255) default NULL,

                description text default NULL,

                ip_address varchar(255) default NULL,

		        country varchar(255) default NULL,

                browser_info text default NULL,

                form_id int(11) default NULL,

                attachment_id int(11) default NULL,

                user_id int(11) default NULL,

                created_date datetime NOT NULL,


                PRIMARY KEY  (id),


                KEY form_id (form_id),


                KEY attachment_id (attachment_id),


                KEY user_id (user_id),


                UNIQUE KEY entry_key (entry_key)


              ) {$charset_collate};";




			dbDelta( $sql );
		   if($wpdb->last_error !== '') { update_option('ARF_ERROR_'.time().rand(),"ERROR===>".htmlspecialchars( $wpdb->last_result, ENT_QUOTES )."QUERY===>".htmlspecialchars( $wpdb->last_query, ENT_QUOTES )); }


            $sql = "CREATE TABLE IF NOT EXISTS {$this->entry_metas} (

                id int(11) NOT NULL auto_increment,

                entry_value longtext default NULL,

                field_id int(11) NOT NULL,

                entry_id int(11) NOT NULL,

                created_date datetime NOT NULL,

                PRIMARY KEY  (id),


                KEY field_id (field_id),


                KEY entry_id (entry_id)


              ) {$charset_collate};";



                    dbDelta( $sql );
		   if($wpdb->last_error !== '') { update_option('ARF_ERROR_'.time().rand(),"ERROR===>".htmlspecialchars( $wpdb->last_result, ENT_QUOTES )."QUERY===>".htmlspecialchars( $wpdb->last_query, ENT_QUOTES )); }

            $sql = "CREATE TABLE IF NOT EXISTS {$this->autoresponder} (

					`id` INT( 11 ) NOT NULL AUTO_INCREMENT ,

					`responder_id` INT( 11 ) NOT NULL ,

					`responder_api_key` varchar(255) NOT NULL ,

					`responder_list_id` TEXT NOT NULL ,

					`responder_list` VARCHAR( 255 ) NOT NULL,

					`consumer_key` VARCHAR( 255 ) NOT NULL,

					`consumer_secret` VARCHAR( 255 ) NOT NULL,

					`responder_username` VARCHAR( 255 ) NOT NULL,

					`responder_password` VARCHAR( 255 ) NOT NULL,

					`responder_web_form` TEXT NOT NULL,

					`is_verify` tinyint(1) default 0,

					`list_data` TEXT NOT NULL,

                    `madmimi_email` VARCHAR( 55 ) NOT NULL,


					PRIMARY KEY ( `id` )


					) {$charset_collate};";


			dbDelta( $sql );
		   if($wpdb->last_error !== '') { update_option('ARF_ERROR_'.time().rand(),"ERROR===>".htmlspecialchars( $wpdb->last_result, ENT_QUOTES )."QUERY===>".htmlspecialchars( $wpdb->last_query, ENT_QUOTES )); }

            $sql = "CREATE TABLE IF NOT EXISTS {$this->views} (

                id int(11) NOT NULL auto_increment,

                form_id int(11) default NULL,

                browser_info text default NULL,

                ip_address varchar(255) default NULL,

                country varchar(255) default NULL,

                session_id varchar(255) default NULL,

                added_date datetime NOT NULL ,

                PRIMARY KEY  (id)


              ) {$charset_collate};";


			dbDelta( $sql );
		   if($wpdb->last_error !== '') { update_option('ARF_ERROR_'.time().rand(),"ERROR===>".htmlspecialchars( $wpdb->last_result, ENT_QUOTES )."QUERY===>".htmlspecialchars( $wpdb->last_query, ENT_QUOTES )); }


            for ($i = 1; $i <= 10; $i++) {


                $sql = "INSERT INTO {$this->autoresponder} (responder_id)VALUES('" . $i . "')";


				dbDelta( $sql );
		   		if($wpdb->last_error !== '') { update_option('ARF_ERROR_'.time().rand(),"ERROR===>".htmlspecialchars( $wpdb->last_result, ENT_QUOTES )."QUERY===>".htmlspecialchars( $wpdb->last_query, ENT_QUOTES )); }
            }



            $sql = "CREATE TABLE IF NOT EXISTS {$this->ar} (

                id int(11) NOT NULL auto_increment,

                frm_id int(11) NOT NULL,

                aweber TEXT NOT NULL,

                mailchimp TEXT NOT NULL,

                getresponse TEXT NOT NULL,

        		gvo TEXT NOT NULL,

        		ebizac TEXT NOT NULL,

                madmimi TEXT NOT NULL,

               	icontact TEXT NOT NULL,

        		constant_contact TEXT NOT NULL,

        		enable_ar TEXT default NULL,


                PRIMARY KEY  (id)

              ) {$charset_collate};";


		   dbDelta( $sql );
		   if($wpdb->last_error !== '') { update_option('ARF_ERROR_'.time().rand(),"ERROR===>".htmlspecialchars( $wpdb->last_result, ENT_QUOTES )."QUERY===>".htmlspecialchars( $wpdb->last_query, ENT_QUOTES )); }


           $sql = "CREATE TABLE IF NOT EXISTS {$this->form_popup} (
                popup_id int(11) NOT NULL auto_increment,
                form_id int(11) NOT NULL,
                popup_type varchar(15) default NULL,
                popup_option longtext default NULL,
                status tinyint(1) default 0,
                created_date datetime NOT NULL,
                PRIMARY KEY (popup_id)
            ) {$charset_collate};";

            dbDelta( $sql );
            if($wpdb->last_error !== '') { update_option('ARF_ERROR_'.time().rand(),"ERROR===>".htmlspecialchars( $wpdb->last_result, ENT_QUOTES )."QUERY===>".htmlspecialchars( $wpdb->last_query, ENT_QUOTES )); }

            update_option('arf_db_version', $arfdbversion);

            update_option('arf_global_css', '');

            $arr = array(
                'aweber_type' => 1,
                'mailchimp_type' => 1,
                'getresponse_type' => 1,
                'icontact_type' => 1,
                'constant_type' => 1,
                'gvo_type' => 0,
                'ebizac_type' => 0,
                'madmimi_type' => 1,
            );

            $arr_new = maybe_serialize($arr);

            update_option('arf_ar_type', $arr_new);


            $uploads = wp_upload_dir();

            $target_path = $uploads['basedir'];

            wp_mkdir_p($target_path);

            $target_path .= "/arforms";

            wp_mkdir_p($target_path);

            $target_path .= "/maincss";

            wp_mkdir_p($target_path);

            global $arfsettings;
            $arfsettings = get_transient('arf_options');

            if (!is_object($arfsettings)) {
                if ($arfsettings) {
                    $arfsettings = maybe_unserialize(maybe_serialize($arfsettings));
                } else {
                    $arfsettings = get_option('arf_options');


                    if (!is_object($arfsettings)) {
                        if ($arfsettings)
                            $arfsettings = maybe_unserialize(maybe_serialize($arfsettings));
                        else
                            $arfsettings = new arsettingmodel();
                        update_option('arf_options', $arfsettings);
                        set_transient('arf_options', $arfsettings);
                    }
                }
            }

            $arfsettings->set_default_options();

            global $style_settings, $maincontroller;

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

            if (!is_admin() and $arfsettings->jquery_css)
                $arfdatepickerloaded = true;

            include("artemplate.php");

			global $wpdb;
            $wpdb->query("ALTER TABLE {$this->forms} AUTO_INCREMENT = 100");
		    if($wpdb->last_error !== '') { update_option('ARF_ERROR_'.time().rand(),"ERROR===>".htmlspecialchars( $wpdb->last_result, ENT_QUOTES )."QUERY===>".htmlspecialchars( $wpdb->last_query, ENT_QUOTES )); }



            $maincontroller->getwpversion();
            
	        /**** Adding new wp_option for Form Entry Separation *****/
	        update_option( 'arf_form_entry_separator', 'arf_comma' );
	    
            update_option('arf_plugin_activated', 1);
        }

        do_action('arfafterinstall');
    }

    function get_count($table, $args = array()) {


        global $wpdb, $MdlDb;


        extract($MdlDb->get_where_clause_and_values($args));



        $query = "SELECT COUNT(*) FROM {$table}{$where}";


        $query = $wpdb->prepare($query, $values);


        return $wpdb->get_var($query);
    }

    function get_where_clause_and_values($args) {


        $where = '';


        $values = array();


        if (is_array($args)) {


            foreach ($args as $key => $value) {


                $where .= (!empty($where)) ? ' AND' : ' WHERE';


                $where .= " {$key}=";


                $where .= (is_numeric($value)) ? "%d" : "%s";





                $values[] = $value;
            }
        }





        return compact('where', 'values');
    }

    function get_var($table, $args = array(), $field = 'id', $order_by = '') {


        global $wpdb, $MdlDb;





        extract($MdlDb->get_where_clause_and_values($args));


        if (!empty($order_by))
            $order_by = " ORDER BY {$order_by}";





        $query = $wpdb->prepare("SELECT {$field} FROM {$table}{$where}{$order_by} LIMIT 1", $values);


        return $wpdb->get_var($query);
    }

    function get_col($table, $args = array(), $field = 'id', $order_by = '') {


        global $wpdb, $MdlDb;





        extract($MdlDb->get_where_clause_and_values($args));


        if (!empty($order_by))
            $order_by = " ORDER BY {$order_by}";





        $query = $wpdb->prepare("SELECT {$field} FROM {$table}{$where}{$order_by}", $values);


        return $wpdb->get_col($query);
    }

    function get_one_record($table, $args = array(), $fields = '*', $order_by = '') {


        global $wpdb, $MdlDb;


        extract($MdlDb->get_where_clause_and_values($args));


        if (!empty($order_by))
            $order_by = " ORDER BY {$order_by}";


        $query = "SELECT {$fields} FROM {$table}{$where} {$order_by} LIMIT 1";


        $query = $wpdb->prepare($query, $values);
        
        return $wpdb->get_row($query);
    }

    function get_records($table, $args = array(), $order_by = '', $limit = '', $fields = '*') {


        global $wpdb, $MdlDb;





        extract($MdlDb->get_where_clause_and_values($args));





        if (!empty($order_by))
            $order_by = " ORDER BY {$order_by}";





        if (!empty($limit))
            $limit = " LIMIT {$limit}";





        $query = "SELECT {$fields} FROM {$table}{$where}{$order_by}{$limit}";


        $query = $wpdb->prepare($query, $values);


        return $wpdb->get_results($query);
    }

    function assign_rand_value($num) {

        switch ($num) {
            case "1" : $rand_value = "a";
                break;
            case "2" : $rand_value = "b";
                break;
            case "3" : $rand_value = "c";
                break;
            case "4" : $rand_value = "d";
                break;
            case "5" : $rand_value = "e";
                break;
            case "6" : $rand_value = "f";
                break;
            case "7" : $rand_value = "g";
                break;
            case "8" : $rand_value = "h";
                break;
            case "9" : $rand_value = "i";
                break;
            case "10" : $rand_value = "j";
                break;
            case "11" : $rand_value = "k";
                break;
            case "12" : $rand_value = "l";
                break;
            case "13" : $rand_value = "m";
                break;
            case "14" : $rand_value = "n";
                break;
            case "15" : $rand_value = "o";
                break;
            case "16" : $rand_value = "p";
                break;
            case "17" : $rand_value = "q";
                break;
            case "18" : $rand_value = "r";
                break;
            case "19" : $rand_value = "s";
                break;
            case "20" : $rand_value = "t";
                break;
            case "21" : $rand_value = "u";
                break;
            case "22" : $rand_value = "v";
                break;
            case "23" : $rand_value = "w";
                break;
            case "24" : $rand_value = "x";
                break;
            case "25" : $rand_value = "y";
                break;
            case "26" : $rand_value = "z";
                break;
            case "27" : $rand_value = "0";
                break;
            case "28" : $rand_value = "1";
                break;
            case "29" : $rand_value = "2";
                break;
            case "30" : $rand_value = "3";
                break;
            case "31" : $rand_value = "4";
                break;
            case "32" : $rand_value = "5";
                break;
            case "33" : $rand_value = "6";
                break;
            case "34" : $rand_value = "7";
                break;
            case "35" : $rand_value = "8";
                break;
            case "36" : $rand_value = "9";
                break;
        }
        return $rand_value;
    }

    function get_rand_alphanumeric($length) {
        global $MdlDb;
        if ($length > 0) {
            $rand_id = "";
            for ($i = 1; $i <= $length; $i++) {
                mt_srand((double) microtime() * 1000000);
                $num = mt_rand(1, 36);
                $rand_id .= $MdlDb->assign_rand_value($num);
            }
        }
        return $rand_id;
    }

}

?>