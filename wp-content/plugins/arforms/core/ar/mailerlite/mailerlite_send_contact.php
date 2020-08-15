<?php
require_once 'vendor/autoload.php';

global $email, $fname, $lname, $wpdb, $fid, $MdlDb ;

$res = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$MdlDb->autoresponder." WHERE responder_id=%d",14));
$res = $res[0];
$api_key = $res->responder_api_key;
$list_id = $res->responder_list;

$data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$MdlDb->ar." WHERE frm_id = %d", $fid), 'ARRAY_A' );
$arr_mailerlite = maybe_unserialize( $data[0]['mailerlite'] );
$responder_list_id = isset($arr_mailerlite['type_val'])?$arr_mailerlite['type_val']:'';

$responder_list_id = ($responder_list_id != '') ? $responder_list_id : $list_id;


if (!empty($responder_list_id) && !empty($api_key)) {

		if (isset($email) && strlen($email) > 1) 
		{

			try 
			{
					$mailerlitegroupsApi = (new \MailerLiteApi\MailerLite($api_key))->groups();
					$mailerlitesubscribersApi = (new \MailerLiteApi\MailerLite($api_key))->subscribers();
					$response = $mailerlitesubscribersApi->search($email);

					if(empty($response))
					{

						$arf_subscriber = array('email' => $email,
				  					 'name' => $fname,
				  					 'fields' => array( 'last_name' => $lname)
									);
						
						$arf_addedSubscriber = $mailerlitegroupsApi->addSubscriber($responder_list_id, $arf_subscriber);

					}
					else
					{

						$arf_subscriberData = array( 'fields' => array( 'name' => $fname, 'last_name' => $lname ) );

						$arf_updsubscriber = $mailerlitesubscribersApi->update($email, $arf_subscriberData);

					}

			}
			catch (Exception $e) 
			{

			}

		}
}
?>