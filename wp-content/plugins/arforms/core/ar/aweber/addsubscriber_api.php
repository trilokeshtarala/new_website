<?php
require_once('aweber_api/aweber_api.php');

global $wpdb,$MdlDb;

	$res = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$MdlDb->autoresponder." WHERE responder_id=%d",3));$res = $res[0];
	
	$data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$MdlDb->ar." WHERE frm_id = %d", $fid), 'ARRAY_A' );
			
   	$arr_aweber 	= isset($data[0]['aweber'])?maybe_unserialize($data[0]['aweber']):array();

	$responder_api_key = stripslashes( stripslashes_deep( $arr_aweber['type_val'] ) );
	
	if( $responder_api_key != '' )
	{	
		$temp_data = isset($res->list_data)?maybe_unserialize($res->list_data):array();
		
		$consumerKey 	= ARF_AWEBER_CONSUMER_KEY;				# put your credentials here
		$consumerSecret = ARF_AWEBER_CONSUMER_SECRET;			# put your credentials here
		$accessKey      = $temp_data['accessToken'];		 	# put your credentials here
		$accessSecret   = $temp_data['accessTokenSecret']; 		# put your credentials here
		$account_id     = $temp_data['acc_id']; 				# put the Account ID here
		$list_id        = $responder_api_key; 					# put the List ID here
		
		$aweber = new AWeberAPI($consumerKey, $consumerSecret);
		
		try {
			$account = $aweber->getAccount($accessKey, $accessSecret);
			$listURL = "/accounts/{$account_id}/lists/{$list_id}";
			$list = $account->loadFromUrl($listURL);
		
			# create a subscriber
			$params = array(
				'email' => $email,
				'name' => $fname." ".$lname,
				);
			
			$params = apply_filters('arf_aweber_additional_fields_from_outside',$params,$fid,$arr_aweber);

			$subscribers = $list->subscribers;
			$new_subscriber = $subscribers->create($params);
		
			# success!
			//print "A new subscriber was added to the $list->name list!";
				
		} catch(AWeberAPIException $exc) {
			
		}
	}
?>