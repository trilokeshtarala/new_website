<?php
@ini_set("memory_limit", "512M");

global $arrecordhelper,$arrecordcontroller,$maincontroller,$arfieldhelper,$armainhelper,$arfsettings;



if(!isset($_REQUEST['bulk_export']) && $_REQUEST['bulk_export']!='yes')
{
	$maincontroller->arfafterinstall();
	global $style_settings;


	
	$form_id = $all_form_id;
	
	$form = $arfform->getOne($form_id);
	
	$form_name = sanitize_title_with_dashes($form->name);


	$form_cols = $arffield->getAll("fi.type not in ('divider', 'captcha', 'break', 'html', 'imagecontrol') and fi.form_id=".$form->id, 'id ASC');


	$entry_id = $armainhelper->get_param('entry_id', false);


	$where_clause = "it.form_id=". (int)$form_id;



	if($entry_id){


		$where_clause .= " and it.id in (";


		$entry_ids = explode(',', $entry_id);
		

		foreach((array)$entry_ids as $k => $it){


			if($k)


				$where_clause .= ",";


			$where_clause .= $it;


			unset($k);


			unset($it);


		}

		$where_clause .= ")";


	}else if(!empty($search)){


		$where_clause = $this->get_search_str($where_clause, $search, $form_id, $fid);


	}

	$where_clause = apply_filters('arfcsvwhere', $where_clause, compact('form_id'));

	$entries = $db_record->getAll($where_clause, '', '', true, false);
	
	$form_cols	= apply_filters('arfpredisplayformcols', $form_cols, $form->id);
	$entries		= apply_filters('arfpredisplaycolsitems', $entries, $form->id);

	$filename = 'ARForms_'.$form_name.'_'. time() .'_0.csv';

	$wp_date_format = apply_filters('arfcsvdateformat', 'Y-m-d H:i:s');

	$charset = get_option('blog_charset');

	$to_encoding = $style_settings->csv_format;

        $entry_separator_id = get_option('arf_form_entry_separator');
        
        if($entry_separator_id == 'arf_comma'){
            $entry_separator = ',';
        }
        elseif($entry_separator_id == 'arf_semicolon'){
            $entry_separator = ';';
        }
        elseif($entry_separator_id == 'arf_pipe'){
            $entry_separator = '|';
        }

header('Content-Description: File Transfer');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Content-Type: text/csv; charset=' . $charset, true);
header('Expires: '. gmdate("D, d M Y H:i:s", mktime(date('H')+2, date('i'), date('s'), date('m'), date('d'), date('Y'))) .' GMT');
header('Last-Modified: '. gmdate('D, d M Y H:i:s') .' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');


$field_order = json_decode($form->options['arf_field_order'],true);
$new_form_cols = array();

asort($field_order);
$hidden_fields = array();
$hidden_field_ids = array();
foreach ($field_order as $field_id => $order) {
    if(is_int($field_id))
    {
        foreach ($form_cols as $field) {
            if ($field_id == $field->id) {
                $new_form_cols[] = $field;
            } else if( $field->type == 'hidden' ){
            	if( !in_array($field->id,$hidden_field_ids) ){
        			$hidden_fields[] = $field;
        			$hidden_field_ids[] = $field->id;
            	}
            }
        }
    }
}

if( count($hidden_fields) > 0 ){
	$new_form_cols = array_merge($new_form_cols,$hidden_fields);
}

$form_cols = $new_form_cols;



echo '"ID"'.$entry_separator;

foreach ($form_cols as $col)
	echo '"'. $arrecordhelper->encode_value(strip_tags($col->name), $charset, $to_encoding) .'"'.$entry_separator.'';


echo '"'. addslashes(esc_html__('Timestamp', 'ARForms')) .'"'.$entry_separator.'"IP"'.$entry_separator.'"Key"'.$entry_separator.'"Country"'.$entry_separator.'"Browser"'.$entry_separator.'"Page URL"'.$entry_separator.'"Referrer URL"'."\n";

foreach($entries as $entry){
	global $wpdb,$MdlDb;
	echo "\"{$entry->id}\"$entry_separator";
	$res_data = $wpdb->get_results( $wpdb->prepare('SELECT description,country, browser_info FROM '.$MdlDb->entries.' WHERE id = %d', $entry->id), 'ARRAY_A');
	$description = maybe_unserialize($res_data[0]['description']);
	$entry->page_url = isset($description['page_url']) ? $description['page_url'] : '';
	$entry->referrer = isset($description['http_referrer']) ? $description['http_referrer'] : '';
	$entry->country = $res_data[0]['country'];
	$arfrecord_browser = $arrecordcontroller->getBrowser($res_data[0]['browser_info']);
	$entry->browser = $arfrecord_browser['name'] . ' (Version: ' . $arfrecord_browser['version'] . ')';
	foreach ($form_cols as $col){
		$field_value = isset($entry->metas[$col->id]) ? $entry->metas[$col->id] : false;
		if(!$field_value and $entry->attachment_id){
			$col->field_options = maybe_unserialize($col->field_options);
		}
	   if ($col->type == 'file'){
			$old_entry_values = explode('|', $field_value);			
			$new_field_value = array();
			
			foreach ($old_entry_values as $old_entry_val){
				$new_field_value[] = str_replace('thumbs/', '', wp_get_attachment_url($old_entry_val));
			}
			$new_field_value = implode('|', $new_field_value);
			$field_value = $new_field_value;
		}else if ($col->type == 'date'){
			$field_value = $arfieldhelper->get_date($field_value, $wp_date_format);
		}else{
			$checked_values = maybe_unserialize($field_value);
			$checked_values = apply_filters('arfcsvvalue', $checked_values, array('field' => $col));
			if (is_array($checked_values)){
					$field_value = implode(', ', $checked_values);
			}else{
				$field_value = $checked_values;
			}
			$field_value = $arrecordhelper->encode_value($field_value, $charset, $to_encoding);
			$field_value = str_replace('"', '""', stripslashes($field_value));  
	                
	                //$field_value = htmlentities($field_value);
		}
		$field_value = str_replace(array("\r\n", "\r", "\n"), ' <br />', $field_value);
		echo "\"$field_value\"$entry_separator";
		unset($col);
		unset($field_value);
	}
	$formatted_date = date($wp_date_format, strtotime($entry->created_date));
	echo "\"{$formatted_date}\"$entry_separator";
	echo "\"{$entry->ip_address}\"$entry_separator";
	echo "\"{$entry->entry_key}\"$entry_separator";
	echo "\"{$entry->country}\"$entry_separator";
	echo "\"{$entry->browser}\"$entry_separator";
	echo "\"{$entry->page_url}\"$entry_separator";
	echo "\"{$entry->referrer}\"$entry_separator\n";
	unset($entry);
}

}
else
{		
global $wpdb;
$arf_get_options = get_option('arf_options');
$entry_separator_id = get_option('arf_form_entry_separator');

if($entry_separator_id == 'arf_comma'){
    $entry_separator = ',';
}
elseif($entry_separator_id == 'arf_semicolon'){
    $entry_separator = ';';
}
elseif($entry_separator_id == 'arf_pipe'){
    $entry_separator = '|';
}

$plugin_url_list = wp_upload_dir();
$baseurl = $plugin_url_list['baseurl'];
$basedir = $plugin_url_list['basedir'];

$filename_arry = array();

$form_id_arr = explode(",",$all_form_id);
$j=0;
foreach($form_id_arr as $form_id)
{
	$form = $arfform->getOne($form_id);
		
	$form_name = sanitize_title_with_dashes($form->name);


	$form_cols = $arffield->getAll("fi.type not in ('divider', 'captcha', 'break', 'html', 'imagecontrol') and fi.form_id=".$form->id, 'id ASC');

	$field_order = json_decode($form->options['arf_field_order'],true);
	$new_form_cols = array();

	asort($field_order);
	$hidden_fields = array();
	$hidden_field_ids = array();
	foreach ($field_order as $field_id => $order) {
	    if(is_int($field_id))
	    {
	        foreach ($form_cols as $field) {
	            if ($field_id == $field->id) {
	                $new_form_cols[] = $field;
	            } else if( $field->type == 'hidden' ){
	            	if( !in_array($field->id,$hidden_field_ids) ){
	        			$hidden_fields[] = $field;
	        			$hidden_field_ids[] = $field->id;
	            	}
	            }
	        }
	    }
	}

	if( count($hidden_fields) > 0 ){
		$new_form_cols = array_merge($new_form_cols,$hidden_fields);
	}

	$form_cols = $new_form_cols;

	$entry_id = $armainhelper->get_param('entry_id', false);


	$where_clause = "it.form_id=". (int)$form_id;


	if($entry_id){


		$where_clause .= " and it.id in (";


		$entry_ids = explode(',', $entry_id);
		

		foreach((array)$entry_ids as $k => $it){


			if($k)


				$where_clause .= ",";


			$where_clause .= $it;


			unset($k);


			unset($it);


		}

		$where_clause .= ")";


	}else if(!empty($search)){


		$where_clause = $this->get_search_str($where_clause, $search, $form_id, $fid);


	}

	$where_clause = apply_filters('arfcsvwhere', $where_clause, compact('form_id'));

	$entries = $db_record->getAll($where_clause, '', '', true, false);
	
	$form_cols	= apply_filters('arfpredisplayformcols', $form_cols, $form->id);
	$entries		= apply_filters('arfpredisplaycolsitems', $entries, $form->id);
	
	$wp_upload_dir 	= wp_upload_dir();
	$dest_dir = $wp_upload_dir['basedir'].'/arforms/';
	
	$filename = $dest_dir.'ARForms_'.$form_name.'_'. time() .'_'.$j.'.csv';

	$wp_date_format = apply_filters('arfcsvdateformat', 'Y-m-d H:i:s');

	$charset = get_option('blog_charset');

	$to_encoding = isset($style_settings->csv_format) ? $style_settings->csv_format : 'UTF-8';
	$list = '';
	foreach ($form_cols as $col)
	
		$list .= $arrecordhelper->encode_value(strip_tags($col->name), $charset, $to_encoding).$entry_separator;

		$list .= addslashes(esc_html__('Timestamp', 'ARForms')) .$entry_separator.'IP'.$entry_separator.'ID'.$entry_separator.'Key'.$entry_separator.'Country'.$entry_separator.'Browser'.'<br>';

	foreach($entries as $entry){
		
		
		global $wpdb,$MdlDb;
		
		$res_data = $wpdb->get_results( $wpdb->prepare('SELECT country, browser_info FROM '.$MdlDb->entries.' WHERE id = %d', $entry->id), 'ARRAY_A');
		$entry->country = $res_data[0]['country'];
		$arfrecord_browser = $arrecordcontroller->getBrowser($res_data[0]['browser_info']);
		$entry->browser = $arfrecord_browser['name'] . ' (Version: ' . $arfrecord_browser['version'] . ')';
		
		$i= 0 ;
		$size_of_form_cols =  count($form_cols);
		foreach ($form_cols as $col){
			
			$field_value = isset($entry->metas[$col->id]) ? $entry->metas[$col->id] : false;
	
	
			if(!$field_value and $entry->attachment_id){
	
	
				$col->field_options = maybe_unserialize($col->field_options);
	
			}
	
	
		   if ($col->type == 'file'){
	
				$old_entry_values = explode('|', $field_value);			
				$new_field_value = array();

				foreach ($old_entry_values as $old_entry_val){
					$new_field_value[] = str_replace('thumbs/', '', wp_get_attachment_url($old_entry_val));
				}
				$new_field_value = implode('|', $new_field_value);
				$field_value = $new_field_value;
	
	
			}else if ($col->type == 'date'){
	
	
				$field_value = $arfieldhelper->get_date($field_value, $wp_date_format);
	
	
			}else{
	
	
				$checked_values = maybe_unserialize($field_value);
	
	
				$checked_values = apply_filters('arfcsvvalue', $checked_values, array('field' => $col));
	
	
				
	
	
				if (is_array($checked_values)){
		
                                                $field_value = implode(', ', $checked_values);
	
	
					
	
	
				}else{
	
	
					$field_value = $checked_values;
	
	
				}
				
				$field_value = $arrecordhelper->encode_value($field_value, $charset, $to_encoding);
	
	
				$field_value = str_replace('"', '""', stripslashes($field_value));  
                    
                                //$field_value = htmlentities($field_value);
			}
	
			
				$field_value = str_replace(array("\r\n", "\r", "\n"), ' <br />', $field_value);
			
			if($size_of_form_cols == $i)  
			{
				$list .= $field_value;			
			}
			else
				$list .= $field_value.$entry_separator;
			
			unset($col);
			unset($field_value);
			
			if(!isset($_REQUEST['bulk_export']) && $_REQUEST['bulk_export']!='yes')
			{
				$formatted_date = date($wp_date_format, strtotime($entry->created_date));
				echo "\"{$formatted_date}\"$entry_separator";
				echo "\"{$entry->ip_address}\"$entry_separator";
				echo "\"{$entry->id}\"$entry_separator";
				echo "\"{$entry->entry_key}\"$entry_separator";
				echo "\"{$entry->country}\"$entry_separator";
                    echo "\"{$entry->browser}\"\n";
				unset($entry);
			}		
			$i++;
			
		}
	
	
		$formatted_date = date($wp_date_format, strtotime($entry->created_date));
	
	
		$list .= $formatted_date."$entry_separator".$entry->ip_address."$entry_separator".$entry->id."$entry_separator".$entry->entry_key."$entry_separator".$entry->country."$entry_separator".$entry->browser."<br>";
		
}
	
	$fp = fopen($filename, 'w');
        $temp_array = array();
	foreach (explode('<br>',$list) as $line)
	{
		$temp_array1 = explode($entry_separator,$line);
                $temp_array2 = array();
			foreach($temp_array1 as $temp_i => $temp_k){
				 $temp_array2[$temp_i] = $temp_k; 
			}
		fputcsv($fp,$temp_array2, $entry_separator);
	}
	fclose($fp);
	
	$file = pathinfo($filename);
	


	$filename_arry[] = $file['basename'];

	
	$j++;
	
	unset($list);
	unset($entry);
	unset($form_cols);
	unset($cols);
	
	}
	
	header('Content-Type: application/csv');
	header('Content-disposition: attachment; filename='.$filename_arry[0]);
	header('Content-Length: '.filesize($dest_dir.$filename_arry[0]));
	readfile($dest_dir.$filename_arry[0]);
	unlink($dest_dir.$filename_arry[0]);
}

function Create_zip($source, $destination, $destinationdir)

{
	$filename = array();
	$filename = unserialize($source);
	
	$zip = new ZipArchive();
	if($zip->open($destination,ZipArchive::CREATE)===TRUE)
	{
		$i = 0;
		foreach($filename as $file)
		{
			
			if($zip->addFile($destinationdir.$file , $file))

			$i++;
		}
		$zip->close(); 
	}
	
	foreach($filename as $file1)
	{

		unlink($destinationdir.$file1);

	}
}
?>