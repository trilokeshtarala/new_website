<?php
global $email, $fname, $lname, $wpdb, $fid, $MdlDb ;
$res = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$MdlDb->autoresponder." WHERE responder_id=%d",10));
$res = $res[0];
$responder_api_key = $res->responder_api_key;
$responder_api_email = $res->madmimi_email;
$data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$MdlDb->ar." WHERE frm_id = %d", $fid), 'ARRAY_A' );
$arr_madmimi = maybe_unserialize( $data[0]['madmimi'] );
$responder_list_id = $arr_madmimi['type_val'];
if( $responder_list_id != '' )
{
	if($email==""){ return "No email address provided"; }
	if(!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/i", $email)) {return "Email address is invalid";}
	$args = array(
    'timeout'     => 15,
    'redirection' => 15,
    'headers'     => "Accept: application/json",
	);
	$url = "https://api.madmimi.com/audience_lists/$responder_list_id/add?email=$email&name=$fname&username=$responder_api_email&api_key=$responder_api_key";

	/*if (!empty($fname)) {
	    //add name to query
	    $url .= "&firstname=$fname";
	    $url .= "&lastname=$lname";
	}*/
	$url = apply_filters('arf_madmimi_additional_fields_from_outside',$url,$fid,$arr_madmimi);
	$response = wp_remote_post( $url, $args );

	if( is_wp_error( $response ) ) {
	    return false;
	} else {

	    if ( $response['response']['code'] == 200 ) {
	        return true;
	    } else {
	        return false;
	    }
	}
}
?>