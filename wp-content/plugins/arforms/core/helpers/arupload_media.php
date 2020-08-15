<?php
include(FORMPATH.'/js/filedrag/simple_image.php');
		
function wp_handle_upload_custom(&$file, $overrides = false, $time = null, $form_id = null, $ftypes_array = array(), $file_id_new = null) {
	
	if(!function_exists('wp_handle_upload_error')) {
		function wp_handle_upload_error(&$file, $message) {
			return array('error'=>$message);
		}
	}
	
	$file = apply_filters('wp_handle_upload_prefilter', $file);
	$upload_error_handler = 'wp_handle_upload_error';
	
	if(isset($file['error']) && !is_numeric($file['error']) && $file['error']) {
		return $upload_error_handler($file, $file['error']);
	}
	
	$unique_filename_callback = null;
	$action = 'wp_handle_upload';
	$upload_error_strings = array(false,
		addslashes(esc_html__("The uploaded file exceeds the upload_max_filesize directive in php.ini.","ARForms")),
		addslashes(esc_html__("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.","ARForms")),
		addslashes(esc_html__("The uploaded file was only partially uploaded.","ARForms")),
		addslashes(esc_html__("No file was uploaded.","ARForms")),
		'',
		addslashes(esc_html__("Missing a temporary folder.","ARForms")),
		addslashes(esc_html__("Failed to write file to disk.","ARForms")),
		addslashes(esc_html__("File upload stopped by extension.","ARForms"))
	);
	
	$test_form = true;
	$test_size = true;
	$test_upload = true;
	$test_type = true;
	$mimes = false;
	
	if(is_array($overrides)) {
		extract($overrides, EXTR_OVERWRITE);
	}
	
	if($test_form && (!isset($_POST['action']) || ($_POST['action'] != $action))) {
		return call_user_func($upload_error_handler, $file, addslashes(esc_html__('Invalid form submission.','ARForms')));
	}
	
	if($file['error'] > 0) {
		return call_user_func($upload_error_handler, $file, $upload_error_strings[$file['error']]);
	}
	
	if($test_size && !($file['size'] > 0)) {
		if(is_multisite()) {
			$error_msg = addslashes(esc_html__('File is empty. Please upload something more substantial.','ARForms'));
		}
		else {
			$error_msg = addslashes(esc_html__('File is empty. Please upload something more substantial. This error could also be caused by uploads being disabled in your php.ini or by post_max_size being defined as smaller than upload_max_filesize in php.ini.','ARForms'));
		}

		return call_user_func($upload_error_handler, $file, $error_msg);
	}
	
	if($test_upload && ! @ is_uploaded_file($file['tmp_name'])) {
		return call_user_func($upload_error_handler, $file, addslashes(esc_html__('Specified file failed upload test.','ARForms')));
	}
	
	if($test_type) {
		$wp_filetype = wp_check_filetype_and_ext($file['tmp_name'], $file['name'], $mimes);
		extract($wp_filetype);
		if($proper_filename) {
			$file['name'] = $proper_filename;
		}
		if((!$type || !$ext) && !current_user_can('unfiltered_upload')) {
			return call_user_func($upload_error_handler, $file, addslashes(esc_html__('Sorry, this file type is not permitted for security reasons.','ARForms')));
		}
		
		if(!$ext) {
			$ext = ltrim(strrchr($file['name'], '.'), '.');
		}
		
		if(!$type) {
			$type = $file['type'];
		}
	}
	else {
		$type = '';
	}
	$ext = strtolower($ext);
	if($ext=="php" || $ext=="php3" || $ext=="php4" || $ext=="php5" || $ext=="pl" || $ext=="py" || $ext=="jsp" || $ext=="asp" || $ext=="exe" || $ext=="cgi")
		return call_user_func($upload_error_handler, $file, addslashes(esc_html__('Sorry, this file type is not permitted for security reasons.','ARForms')));
	
	if(count($ftypes_array) > 0 and !in_array($type, $ftypes_array)) {
		return call_user_func($upload_error_handler, $file, addslashes(esc_html__('Sorry, this file type is not permitted for security reasons.','ARForms')));
	}
		
	if(!(($uploads = wp_upload_dir($time)) && false === $uploads['error']))
		return call_user_func($upload_error_handler, $file, $uploads['error']);
	
	$filename = $form_id."_".$file_id_new."_".time()."_".$file['name'];
	
	$filename = str_replace('?','-', $filename);
	$filename = str_replace('&','-', $filename);
		
	//$new_file = $uploads['path'] . "/".$filename;
	global $arformhelper;
	$movable = $arformhelper->manage_uploaded_file_path($form_id);
	if($movable["status"]) {
		$new_file = $movable["path"] . $filename;
		if(false === @ move_uploaded_file($file['tmp_name'], $new_file)) {
			return $upload_error_handler($file, sprintf(addslashes(esc_html__('The uploaded file could not be moved to %s.','ARForms')), $movable["path"]));
		}
	}
	else {
		return $upload_error_handler($file, sprintf(addslashes(esc_html__('The uploaded file could not be moved to %s.','ARForms')), $movable["path"]));
	}
	
	$stat = stat(dirname($new_file));
	$perms = $stat['mode'] & 0000666;
	@ chmod($new_file, $perms);

	$url = $movable["path"] . "thumbs/$filename";
	
	if(is_multisite()) delete_transient('dirsize_cache');

	$new_file1 = $movable["path"] . "thumbs/".$filename;
	
	return apply_filters('wp_handle_upload', array('file' => $new_file1, 'url' => $url, 'type' => $type,'file_name'=>$filename), 'upload');
}

function media_handle_upload_custom($file_id, $attach_id,$form_id, $post_data = array(), $overrides = array('test_form' => false)) {
	
	$time = current_time('mysql');
	if($post = get_post($attach_id)) {
		if(substr($post->post_date, 0, 4) > 0)
			$time = $post->post_date;
	}

	$files = $_FILES[$file_id];
		
	global $wpdb, $MdlDb; $arformhelper;
	$file_id_new = str_replace('file','',$file_id);
	$res_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$MdlDb->fields." WHERE id = %d", $file_id_new));
	$field_options_new = maybe_unserialize($res_data[0]->field_options);
	$field_types = get_allowed_mime_types();
	$field_types['exe'] = '';
	unset($field_types['exe']);
	
	$ftypes_array = (isset($field_options_new['restrict']) && $field_options_new['restrict']==1) ? (($field_options_new['ftypes']=='') ? $field_types : $field_options_new['ftypes']) : $field_types;
	
	$new_media_value_ids = array();
	
	foreach ($files['name'] as $fkey => $fvalue) {
		
		if($files['name'][$fkey]) {
			
			$name = $files['name'][$fkey];
			$name_org = $files['name'][$fkey];
			
			$file_data = array(
			  'name'     => $files['name'][$fkey],
			  'type'     => $files['type'][$fkey],
			  'tmp_name' => $files['tmp_name'][$fkey],
			  'error'    => $files['error'][$fkey],
			  'size'     => $files['size'][$fkey]
			);

			global $arformhelper;
			$movable = $arformhelper->manage_uploaded_file_path($form_id);

			if($movable["status"]) {
				$file = wp_handle_upload_custom($file_data, $overrides, $time, $form_id, $ftypes_array, $file_id_new);
			}

			$title = $file['file_name'];

			if(isset($file['error']))
				return new WP_Error('upload_error', $file['error']);
			
			$name_parts = pathinfo($name);
			$name = trim(substr($name, 0, -(1 + strlen($name_parts['extension']))));
			
			$url = $file['url'];
			$type = $file['type'];
			$file = $file['file'];
			$content = '';
	
			if($image_meta = @wp_read_image_metadata($file)) {
				if(trim($image_meta['title']) && ! is_numeric(sanitize_title($image_meta['title'])))
					$title = $image_meta['title'];
				if(trim($image_meta['caption']))
					$content = $image_meta['caption'];
			}
			
			$attachment = array_merge(array(
				'post_mime_type' => $type,
				'guid' => $url,
				'post_parent' => $attach_id,
				'post_title' => $title,
				'post_content' => $content,
			), $post_data);
			
			if(isset($attachment['ID'])) {
				unset($attachment['ID']);
			}

			$file_path = $arformhelper->get_file_upload_path();
			$file_path = $arformhelper->replace_file_upload_path_shrtcd($file_path, $form_id);
			$file_path = $file_path . "thumbs/" . basename($file);
			
			$id = wp_insert_attachment($attachment, $file_path, $attach_id);
	
			if(!is_wp_error($id)) {

				$pos = strpos($type,'image/');
				if($pos !== false)
				{
					$image = new SimpleImage();
					$image->load($movable["path"] . $title);
					$image->resizeToHeight(100);
					$image->save($movable["path"].'thumbs/'.$title);
				}
			}
			$new_media_value_ids[] = $id;
		}
	}
	
	$new_media_value_ids = implode('|', $new_media_value_ids);
	
	return $new_media_value_ids;
}
?>