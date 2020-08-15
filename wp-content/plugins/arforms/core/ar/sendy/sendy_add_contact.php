<?php
global $email, $fname, $lname, $wpdb, $fid, $MdlDb ;
$res = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$MdlDb->autoresponder." WHERE responder_id=%d",10));
$res = $res[0];
$responder_api_key = $res->responder_api_key;
$responder_api_url = $res->sendy_url;
$data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$MdlDb->ar." WHERE frm_id = %d", $fid), 'ARRAY_A' );
$arr_sendy 	= maybe_unserialize( $data[0]['sendy'] );
$responder_list_id = isset($arr_sendy['type_val']) ? $arr_sendy['type_val'] : '';
if($responder_list_id !='')
{
	$postdata = http_build_query(
	    array(
	    'name' => $fname." ".$lname,
	    'email' => $email,
	    'list' => $responder_list_id,
	    'boolean' => 'true'
	    )
	);
	$opts = array('http' => array('method'  => 'POST', 'header'  => 'Content-type: application/x-www-form-urlencoded', 'content' => $postdata));
	$context  = stream_context_create($opts);
    $result = file_get_contents($responder_api_url.'/subscribe', false, $context);
}
?>