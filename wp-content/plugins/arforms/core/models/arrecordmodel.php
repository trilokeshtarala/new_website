<?php

/*if (!(extension_loaded('geoip'))) {
    
    include("geoip.inc");
}*/

class arrecordmodel {
    function create($values,$return_values = false) {
        global $wpdb, $MdlDb, $arfrecordmeta, $fid, $armainhelper, $db_record, $arfieldhelper, $arfsettings, $arformhelper, $arfcreatedentry;

        $checkfield_validation = $db_record->validate($values, false, 1);

        if (!is_null($checkfield_validation) && count($checkfield_validation) > 0) {
            return false;
        }
        $form_id = $values["form_id"];
        $fields = $arfieldhelper->get_form_fields_tmp(false, $form_id, false, 0);        
        $posted_item_fields = isset($values["item_meta"]) ? $values["item_meta"] : array();
        $posted_item_fields = apply_filters('arf_trim_values',$posted_item_fields);
        $form_options = $wpdb->get_results( $wpdb->prepare("SELECT * FROM `".$MdlDb->forms."` WHERE id = %d",$form_id) );
        $form = $form_options[0];
        $options = maybe_unserialize($form->options);
        
        $field_order = json_decode($options['arf_field_order'],true);
 
        asort($field_order);

        $conditional_logic = $options['arf_conditional_logic_rules'];
        $tempfields = array();
        $file_path = $arformhelper->get_file_upload_path();
        $file_path = $arformhelper->replace_file_upload_path_shrtcd($file_path, $form_id);

        $arf_sorted_fields = array();
        if ($field_order != '') {
            foreach ($field_order as $field_id => $order) {
                if(is_int($field_id)){
                    foreach ($fields as $field) {
                        if ($field_id == $field->id) {
                            $arf_sorted_fields[] = $field;
                        }
                    }
                }
            }
        }

        $fields = $arf_sorted_fields;

        foreach ($fields as $field) {
            $field_conditional_logic = maybe_unserialize($field->conditional_logic);
            if( $field_conditional_logic == "1" ){
                $item_meta_values = isset($values["item_meta"]) ? $values["item_meta"] : array();
                $tempfields = $arfieldhelper->post_validation_filed_display($field, $fields, $item_meta_values,$conditional_logic);
            }
        }
       
        $removed_field_ids = array();
        if(!empty($values['item_meta'])){
           foreach ($values['item_meta'] as $key => $value) {
                if (is_array($tempfields)) {
                    if (in_array($key, $tempfields)) {
                        array_push($removed_field_ids,$key);
                        unset($values['item_meta'][$key]);
                    }
                }
            }
        }
        
        if( isset($_FILES) && isset($tempfields) && count($tempfields) > 0 ){
            foreach( $tempfields as $key => $tmp_val ){
                if( isset($_FILES['file'.$tmp_val]) ){
                    unset($_FILES['file'.$tmp_val]);
                }
            }
        }
        
        if( !empty($removed_field_ids) ){
            foreach($fields as $k => $pst_field ){
                if( in_array($pst_field->id,$removed_field_ids) ){
                    unset($fields[$k]);
                } 
            }
            $fields = array_values($fields);
        }
        
        $tmpbreaks = array();
        $tmpdivider = array();
        $allfieldsarr = array();
        $allfieldstype = array();
        foreach ($fields as $key => $postfield) {
            $allfieldsarr[] = $postfield->id;
            $allfieldstype[] = $postfield->type;
            if (is_array($tempfields) and ! empty($tempfields) and in_array($postfield->id, $tempfields)) {
                if (( $postfield->type == 'break' )) {
                    $tmpbreaks[] = $key;
                }
                if (( $postfield->type == 'divider')) {
                    $tmpdivider[] = $key;
                }
            }
        }

        $fieldsarray = array();

        /* Remove Fields from Hidden Page Break */
        foreach( $tmpbreaks as $key => $value ){
            $first = $tmpbreaks[$key];
            $last = isset($tmpbreaks[$key + 1]) ? $tmpbreaks[$key + 1] : count($allfieldsarr) - 1;
            for( $pg = $first; $pg < $last; $pg++ ){
                if( isset($allfieldstype[$pg + 1]) && $allfieldstype[$pg + 1] == 'break' && !in_array($allfieldsarr[$pg + 1], $tempfields) ){
                    $last = $pg + 1;
                }
            }

            for( $x1 = $first; $x1 <= $last; $x1++ ){
                $fieldsarray[] = isset($allfieldsarr[$x1]) ? $allfieldsarr[$x1] : '';
            }
        }

        /* Remove Fields from Hidden Section */
        foreach( $tmpdivider as $key => $value ){
            $first = $tmpdivider[$key];
            $last = isset($tmpdivider[$key + 1]) ? $tmpdivider[$key + 1] : count($allfieldsarr) - 1;
            for( $pd = $first; $pd < $last; $pd++ ){
                if( isset($allfieldstype[$pd + 1]) && ($allfieldstype[$pd + 1] == 'break' || $allfieldstype[$pd + 1] == 'divider') && !in_array($allfieldsarr[$pd + 1], $tempfields) ){
                    $last = $pd + 1;
                }
            }

            for( $x2 = $first; $x2 <= $last; $x2++ ){
                $fieldsarray[] = isset($allfieldsarr[$x2]) ? $allfieldsarr[$x2] : '';
            }
        }

        $fieldsarray = array_values(array_unique($fieldsarray));

      
        if (isset($fieldsarray) and ! empty($fieldsarray)) {
            foreach ($fieldsarray as $key => $value) {
                unset($values['item_meta'][$value]);
            }
        }

        foreach ($fields as $k => $f) {
            if (isset($fieldsarray) and ! empty($fieldsarray) and is_array($fieldsarray)) {
                if (in_array($f->id, $fieldsarray)) {
                    unset($fields[$k]);
                }
            }
        }
        
        foreach ($fields as $postfield) {
            $field_conditional_logic = maybe_unserialize($postfield->conditional_logic);
            if (isset($field_conditional_logic['enable']) && $field_conditional_logic['enable'] == '1') {
                $display = $arfieldhelper->post_validation_filed_display($postfield, $fields, $values["item_meta"]);
                if ($display == 'true') {
                    if ($postfield->required) {
                        if ($arfsettings->form_submit_type != 1) {
                            if ($postfield->type == "file") {
                                if (isset($_FILES["file" . $postfield->id]["name"]) && $_FILES["file" . $postfield->id]["name"] == '') {
                                    return false;
                                    break;
                                }
                            }
                            else if ($postfield->type == 'number') {
                                if ($posted_item_fields[$postfield->id] == '') {
                                    return false;
                                    break;
                                }
                            }
                            else {
                                if ($posted_item_fields[$postfield->id] == '') {
                                    return false;
                                    break;
                                }
                            }
                        }
                        else {
                            if ($postfield->type == 'number') {
                                if ($posted_item_fields[$postfield->id] == '') {
                                    return false;
                                    break;
                                }
                            }
                            else {
                                if ($posted_item_fields[$postfield->id] == '') {
                                    return false;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            else {
                if ($postfield->required) {
                    if ($arfsettings->form_submit_type != 1) {
                        if ($postfield->type == "file") {
                            if (isset($_FILES["file" . $postfield->id]["name"]) && $_FILES["file" . $postfield->id]["name"] == '') {
                                return false;
                                break;
                            }
                        }
                        else if ($postfield->type == 'number') {
                            if ($posted_item_fields[$postfield->id] == '') {
                                return false;
                                break;
                            }
                        }
                        else {
                            if ($posted_item_fields[$postfield->id] == '') {
                                return false;
                                break;
                            }
                        }
                    }
                    else {
                        if ($postfield->type == 'number') {
                            if ($posted_item_fields[$postfield->id] == '') {
                                return false;
                                break;
                            }
                        }
                        else {

                            if (isset($posted_item_fields[$postfield->id]) && $posted_item_fields[$postfield->id] == '') {
                                return false;
                                break;
                            }
                        }
                    }
                }
            }
        }

        if( isset($return_values) && $return_values == true ){
            return isset($values['item_meta']) ? $values['item_meta'] : array();
        }

        if( apply_filters('arf_prevent_duplicate_entry',false,$form_id,$values) ){
            return false;
        }
        
        $values = apply_filters('arf_before_create_formentry', $values);
        do_action('arfbeforecreateentry', $values);
        $fid = isset($values["form_id"])?$values["form_id"]:'';
        $new_values = array();
        $values['entry_key'] = isset($values['entry_key'])?$values['entry_key']:'';
        $new_values['entry_key'] = $armainhelper->get_unique_key($values['entry_key'], $MdlDb->entries, 'entry_key');
        $field_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->fields . " WHERE form_id = %d", $fid));

        if (count($field_data) > 0) {
            foreach ($field_data as $new_field) {
                if ($new_field->type == 'scale') {
                    $values['item_meta'][$new_field->id] = ( isset($values['item_meta'][$new_field->id]) and $values['item_meta'][$new_field->id] != '' ) ? $values['item_meta'][$new_field->id] : 0;
                }
                if ($new_field->type == 'arf_autocomplete') {
                    /* if result not found than entry value for autocomplete will be -21. So if entry value is euqual to -21 than replace it with null value */
                    $values['item_meta'][$new_field->id] = ( isset($values['item_meta'][$new_field->id]) and ( $values['item_meta'][$new_field->id]== 'Result not Found'|| $values['item_meta'][$new_field->id] == '-21' )) ? '' : $values['item_meta'][$new_field->id];
                }
            }
        }

        $new_values['name'] = isset($values['name']) ? $values['name'] : $values['entry_key'];

        if (is_array($new_values['name'])) {
            $new_values['name'] = reset($new_values['name']);
        }

        $new_values['ip_address'] = $_SERVER['REMOTE_ADDR'];

        if (isset($values['description']) and ! empty($values['description'])) {
            $new_values['description'] = $values['description'];
        }
        else {
            $referrerinfo = $armainhelper->get_referer_info();
            $new_values['description'] = maybe_serialize(array('browser' => $_SERVER['HTTP_USER_AGENT'],
                'referrer' => $referrerinfo,
                'http_referrer' => isset($values["arf_http_referrer_url"])?$values["arf_http_referrer_url"]:'',
                'page_url' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''
                    )
            );
        }

        $new_values['browser_info'] = $_SERVER['HTTP_USER_AGENT'];
        /*$file_url = dirname(__FILE__) . "/GeoIP.dat";

        if (!(extension_loaded('geoip'))) {
            $gi = geoip_open($file_url, GEOIP_STANDARD);
            $country_name = geoip_country_name_by_addr($gi, $new_values['ip_address']);
        }
        else {
            $country_name = "";
        }*/

        

        $country_name = arf_get_country_from_ip($new_values['ip_address']);

        $new_values['country'] = $country_name;
        $new_values['form_id'] = isset($values['form_id']) ? (int) $values['form_id'] : null;
        $new_values['created_date'] = isset($values['created_date']) ? $values['created_date'] : current_time('mysql');

        if (isset($values['arfuserid']) and is_numeric($values['arfuserid'])) {
            $new_values['user_id'] = $values['arfuserid'];
        }
        else {
            global $user_ID;
            if ($user_ID){
                $new_values['user_id'] = $user_ID;
            }
        }

        if( !isset($new_values['user_id']) || $new_values['user_id'] == null || $new_values['user_id'] == '' ) {
            $new_values['user_id'] = get_current_user_id();
        }

        $create_entry = true;

        if ($create_entry) {
            $query_results = $wpdb->insert($MdlDb->entries, $new_values);
        }
        
        if (isset($query_results) and $query_results) {
            $entry_id = $wpdb->insert_id;
            global $arfsavedentries;
            $arfsavedentries[] = (int) $entry_id;
            if (isset($_REQUEST['form_display_type']) and $_REQUEST['form_display_type'] != '') {
                global $wpdb,$MdlDb;
                $arf_meta_insert = array(
                    'entry_value' => arf_sanitize_value($_REQUEST['form_display_type']),
                    'field_id' => arf_sanitize_value('0', 'integer'),
                    'entry_id' => arf_sanitize_value($entry_id, 'integer'),
                    'created_date' => current_time('mysql'),
                );
                $wpdb->insert($MdlDb->entry_metas, $arf_meta_insert, array('%s', '%d', '%d', '%s'));
            }

            if (isset($values['item_meta'])) {
                if(isset($options['arf_twilio_to_number']) && ''!=$options['arf_twilio_to_number']){
                    if(isset($values['item_meta'][$options['arf_twilio_to_number']]) && ''!=trim($values['item_meta'][$options['arf_twilio_to_number']])){
                        $values['item_meta'][$options['arf_twilio_to_number']] = preg_replace("/^0/", "", $values['item_meta'][$options['arf_twilio_to_number']]);    
                    }
                }
                $tmp_key = array();
                foreach ($values['item_meta'] as $key => $value) {
                    if(strpos($key, '_country_code') !== false){
                        $key_id = str_replace("_country_code", "", $key);
                        if(isset($values['item_meta'][$key_id]) && !empty($values['item_meta'][$key_id])){
                            $tmp_key[$key_id] = $value;
                        }    
                        unset($values['item_meta'][$key]); 
                    }
                    
                }
                foreach ($tmp_key as $key => $value) {
                    if(isset($values['item_meta'][$key])){
                        $values['item_meta'][$key] = arf_sanitize_value($value." ".$values['item_meta'][$key]);
                    }
                }

                $arfrecordmeta->update_entry_metas($entry_id, $values['item_meta'],$_REQUEST['arfform_date_formate_'.$_POST['form_id']]);
            }

            $arfcreatedentry[$_POST['form_id']]['entry_id'] = $entry_id;
            $images_string = $_POST['imagename_' . $_POST['form_id'] . '_' . $_POST['form_data_id']];
            $imagesToUpload = explode(',', $images_string);
            $upload_field_string = explode(',', $_POST['upload_field_id_' . $_POST['form_id'] . '_' . $_POST['form_data_id']]);

            if (isset($_REQUEST['using_ajax']) && $_REQUEST['using_ajax'] == 'yes') {
                foreach ($imagesToUpload as $key => $image) {
                    if ($image != "") {
                        $full_image_name = pathinfo($image);
                        $image_name = $full_image_name['filename'];
                        $image_extention = $full_image_name['extension'];
                        $image_path = get_home_url() . "/" . $file_path . $image;
                        $image_path1 =  ABSPATH . $file_path . $image;

                        $info = getimagesize($image_path1);
                        $mime_type = $info['mime'];

                        $args = array("post_title" => $image_name . '.' . $image_extention, 'post_name' => arf_sanitize_value($image_name), 'post_type' => arf_sanitize_value('attachment'), 'post_mime_type' => arf_sanitize_value($mime_type), "guid" => $image_path);
                        $lastid = wp_insert_post($args);

                        $path = '';
                        if (preg_match('/image\//', $mime_type)) {
                            $path = $file_path.'thumbs/';
                        } else {
                            $path = $file_path;
                        }

                        $wpdb->query($wpdb->prepare("insert into " . $wpdb->prefix . "postmeta (post_id,meta_key,meta_value) values ('%d','_wp_attached_file','%s')", $lastid, $path . $image));
                        
                        $field_id = isset($_POST['field_id']) ? $_POST['field_id'] : "";

                        $upload_field_key = $upload_field_string[$key];

                        $arf_upload_key1 = explode("_", $upload_field_key);
                        $upload_field_id = $wpdb->get_row($wpdb->prepare("select * from " .$MdlDb->fields." where field_key =%s",$arf_upload_key1[0]));
                        $field_id = $upload_field_id->id;

                        //$entry_value = isset($_POST['item_meta'][$field_id]) ? $_POST['item_meta'][$field_id] : '';

                        $check_upload_field_available = $wpdb->get_row($wpdb->prepare("select * from " .$MdlDb->entry_metas." where field_id='%d' and entry_id='%d'",$field_id,$arfcreatedentry[$_POST['form_id']]['entry_id']));
                        $new_entry_value_ids = array();
                        
                        if ($check_upload_field_available != '' && $check_upload_field_available->id != '') {
                            $old_entry_value = $check_upload_field_available->entry_value;
                            $old_entry_value_ids = explode('|', $old_entry_value);

                            if (count($old_entry_value_ids) == 1 && !is_numeric($old_entry_value_ids[0])) {
                                $new_entry_value_ids[] = $lastid;
                            } else {
                                $new_entry_value_ids = $old_entry_value_ids;
                                if (!in_array($lastid, $old_entry_value_ids)) {
                                    $new_entry_value_ids[] = $lastid;
                                }
                            }

                            $new_entry_value_ids = implode('|', $new_entry_value_ids);
                            $wpdb->query('UPDATE ' .$MdlDb->entry_metas.' SET entry_value="' . $new_entry_value_ids . '" WHERE field_id="' . $field_id . '" and entry_id="' . $arfcreatedentry[$_POST['form_id']]['entry_id'] . '"');
                        } else {
                            $wpdb->query('insert into ' .$MdlDb->entry_metas.' (entry_value,field_id,entry_id,created_date) values("' . $lastid . '","' . $field_id . '","' . $arfcreatedentry[$_POST['form_id']]['entry_id'] . '",NOW())');
                        }
                    }
                }
            }
            
            $entry_id = apply_filters('arf_after_create_formentry', $entry_id, $new_values['form_id']);
            if ($entry_id == false || $entry_id == '' || !isset($entry_id)) {
                return false;
            }
            do_action('arfaftercreateentry', $entry_id, $new_values['form_id']);
            return $entry_id;
        }
        else {
            return false;
        }
    }

    function &destroy($id) {


        global $wpdb, $MdlDb;


        $id = (int) $id;

        $id = apply_filters('arf_before_destroy_entry', $id);

        $res = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$MdlDb->fields." a, ".$MdlDb->entry_metas." b WHERE a.type=%s AND b.entry_id=%d AND b.field_id=a.id GROUP BY b.id", 'file', $id));

        if(isset($res->entry_value) && $res->entry_value != ''){

            $new_ids = array();
            
            $exp_ids = explode("|", $res->entry_value);

            foreach ($exp_ids as $key => $file_id) {
                $image_url = "";
                $thumb_url = '';
                $post_meta_data = get_post_meta($file_id);
                if(isset($post_meta_data['_wp_attached_file']) && isset($post_meta_data['_wp_attached_file'][0])){
                    $image_name = explode('/',$post_meta_data['_wp_attached_file'][0]);

                    $image_name = $image_name[count($image_name) -1 ];

                    $image_ext = explode('.',$image_name);

                    $image_ext = $image_ext[count($image_ext) - 1];

                    $image_ext = strtolower($image_ext);

                    $exclude_ext = array('png','jpg','jpeg','jpe','gif','bmp','tif','tiff','ico');

                    if( in_array($image_ext,$exclude_ext) ){
                        $image_url = ABSPATH.str_replace('thumbs/', '', $post_meta_data['_wp_attached_file'][0]);
                        $thum_url = ABSPATH.$post_meta_data['_wp_attached_file'][0];
                    }
                    if($thum_url){ unlink($thum_url); }
                    if($image_url){ unlink($image_url); }
                    wp_delete_attachment($file_id);                    
                }
            }

        }

        $wpdb->query($wpdb->prepare('DELETE FROM ' . $MdlDb->entry_metas . ' WHERE entry_id=%d', $id));

        $result = $wpdb->query($wpdb->prepare('DELETE FROM ' . $MdlDb->entries . ' WHERE id=%d', $id));

        $result = apply_filters('arf_after_destroy_entry', $result);

        return $result;
    }

    function getOne($id, $meta = false) {


        global $wpdb, $MdlDb;


        $query = "SELECT it.*, fr.name as form_name, fr.form_key as form_key FROM $MdlDb->entries it 


                  LEFT OUTER JOIN $MdlDb->forms fr ON it.form_id=fr.id WHERE ";


        if (is_numeric($id))
            $query .= $wpdb->prepare('it.id=%d', $id);
        else
            $query .= $wpdb->prepare('it.entry_key=%s', $id);





        $entry = $wpdb->get_row($query);





        if ($meta and $entry) {


            global $arfrecordmeta;


            $metas = $arfrecordmeta->getAll("entry_id=$entry->id and field_id != 0");


            $entry_metas = array();


            foreach ($metas as $meta_val)
                $entry_metas[$meta_val->field_id] = $entry_metas[$meta_val->field_key] = maybe_unserialize($meta_val->entry_value);





            $entry->metas = $entry_metas;
        }





        return stripslashes_deep($entry);
    }

    function getAll($where = '', $order_by = '', $limit = '', $meta = false, $inc_form = true, $arfSearch = '', $arffieldorder = array()) {


        global $wpdb, $MdlDb, $armainhelper;

        if (is_numeric($limit))
            $limit = " LIMIT {$limit}";

        $left_outer_join = "";
        if ($arfSearch != ''){
            $left_outer_join = " LEFT OUTER JOIN $MdlDb->entry_metas itmeta ON it.id=itmeta.entry_id ";
            $where .= " and Concat(it.id, it.entry_key, it.ip_address, it.created_date, it.browser_info, it.country, itmeta.entry_value) LIKE '%".$arfSearch."%'";
        }


        if ($inc_form) {


            $query = "SELECT it.*, fr.name as form_name,fr.form_key as form_key


                FROM $MdlDb->entries it LEFT OUTER JOIN $MdlDb->forms fr ON it.form_id=fr.id" .$left_outer_join.
                    $armainhelper->prepend_and_or_where(' WHERE ', $where) . $order_by . $limit;
        } else {


            $query = "SELECT it.id, it.entry_key, it.name, it.ip_address, it.form_id, it.attachment_id, it.user_id, it.created_date FROM $MdlDb->entries it".$left_outer_join.$armainhelper->prepend_and_or_where(' WHERE ', $where) . $order_by . $limit;
        }


        $entries = $wpdb->get_results($query, OBJECT_K);


        unset($query);





        if ($meta and $entries) {


            if ($limit == '' and ! is_array($where) and preg_match('/^it\.form_id=\d+$/', $where)) {


                $meta_where = 'fi.form_id=' . substr($where, 11);
            } else if ($limit == '' and is_array($where) and count($where) == 1 and isset($where['it.form_id'])) {


                $meta_where = 'fi.form_id=' . $where['it.form_id'];
            } else {


                $meta_where = "entry_id in (" . implode(',', array_keys($entries)) . ")";
            }


            $query = $wpdb->prepare("SELECT entry_id, entry_value, field_id, 


                fi.field_key as field_key FROM $MdlDb->entry_metas it 


                LEFT OUTER JOIN $MdlDb->fields fi ON it.field_id=fi.id 


                WHERE $meta_where and field_id != %d", 0);





            $metas = $wpdb->get_results($query);


            unset($query);





            if ($metas) {

                if(count($arffieldorder) > 0){

                    $form_metas = array();
                    foreach ($arffieldorder as $fieldkey => $fieldorder) {
                        foreach ($metas as $fieldmetakey => $fieldmetaval) {
                            if($fieldmetaval->field_id == $fieldkey) {
                                $form_metas[] = $fieldmetaval;
                                unset($metas[$fieldmetakey]);
                            }
                        }
                    }

                    if(count($form_metas) > 0) {
                        if(count($metas) > 0) {
                            $arfothermetas = $metas;
                            $metas = array_merge($form_metas,$arfothermetas);
                        } else {
                            $metas = $form_metas;
                        }
                    }

                }


                foreach ($metas as $m_key => $meta_val) {


                    if (!isset($entries[$meta_val->entry_id]))
                        continue;





                    if (!isset($entries[$meta_val->entry_id]->metas))
                        $entries[$meta_val->entry_id]->metas = array();





                    $entries[$meta_val->entry_id]->metas[$meta_val->field_id] = $entries[$meta_val->entry_id]->metas[$meta_val->field_key] = maybe_unserialize($meta_val->entry_value);
                }
            }
        }





        return stripslashes_deep($entries);
    }

    function getRecordCount($where = '') {


        global $wpdb, $MdlDb, $armainhelper;


        if (is_numeric($where)) {


            $query = "SELECT COUNT(*) FROM $MdlDb->entries WHERE form_id=" . $where;
        } else {


            $query = "SELECT COUNT(*) FROM $MdlDb->entries it LEFT OUTER JOIN $MdlDb->forms fr ON it.form_id=fr.id" .
                    $armainhelper->prepend_and_or_where(' WHERE ', $where);
        }


        return $wpdb->get_var($query);
    }

    function getPageCount($p_size, $where = '') {


        if (is_numeric($where))
            return ceil((int) $where / (int) $p_size);
        else
            return ceil((int) $this->getRecordCount($where) / (int) $p_size);
    }

    function getPage($current_p, $p_size, $where = '', $order_by = '', $arfSearch = '', $arffieldorder = array()) {


        global $wpdb, $MdlDb, $armainhelper;


        $end_index = (int)$current_p * (int)$p_size;


        $start_index = (int)$end_index - (int)$p_size;

        if ($current_p != '' and $p_size != '')
            $results = $this->getAll($where, $order_by, " LIMIT $start_index,$p_size;", true, true, $arfSearch, $arffieldorder);
        else
            $results = $this->getAll($where, $order_by, "", true, true, $arfSearch, $arffieldorder);

        return $results;
    }

    function validate($values, $exclude = false, $unset_custom_captcha = 0) {
        
    }

    function akismet($values) {


        global $akismet_api_host, $akismet_api_port, $arfsiteurl;





        $content = '';


        foreach ($values['item_meta'] as $val) {


            if ($content != '')
                $content .= "\n\n";


            if (is_array($val))
                $val = implode(',', $val);


            $content .= $val;
        }





        if ($content == '')
            return false;





        $datas = array();


        $datas['blog'] = $arfsiteurl;


        $datas['user_ip'] = preg_replace('/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR']);


        $datas['user_agent'] = $_SERVER['HTTP_USER_AGENT'];


        $datas['referrer'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false;


        $datas['comment_type'] = 'ARForms';


        if ($permalink = get_permalink())
            $datas['permalink'] = $permalink;





        $datas['comment_content'] = $content;





        foreach ($_SERVER as $key => $value)
            if (!in_array($key, array('HTTP_COOKIE', 'argv')))
                $datas["$key"] = $value;





        $query_string = '';


        foreach ($datas as $key => $data)
            $query_string .= $key . '=' . urlencode(stripslashes($data)) . '&';





        $response = akismet_http_post($query_string, $akismet_api_host, '/1.1/comment-check', $akismet_api_port);


        return ( is_array($response) and $response[1] == 'true' ) ? true : false;
    }

    function user_can_edit($entry, $form) {

        global $db_record;

        $allowed = $db_record->user_can_edit_check($entry, $form);

        return apply_filters('arfusercanedit', $allowed, compact('entry', 'form'));
    }

    function user_can_edit_check($entry, $form) {

        global $user_ID, $armainhelper, $db_record, $arfform;



        if (!$user_ID)
            return false;



        if (is_numeric($form))
            $form = $arfform->getOne($form);



        $form->options = maybe_unserialize($form->options);

        if (is_object($entry)) {

            if ($entry->user_id == $user_ID)
                return true;
            else
                return false;
        }



        $where = "user_id='$user_ID' and fr.id='$form->id'";

        if ($entry and ! empty($entry)) {
	
            if (is_numeric($entry))
                $where .= ' and it.id=' . $entry;
            else
                $where .= " and entry_key='" . $entry . "'";
        }



        return $db_record->getAll($where, '', ' LIMIT 1', true);
    }

}
