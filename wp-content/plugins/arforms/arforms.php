<?php
/*
  Plugin Name: ARForms - Premium WordPress Form Builder Plugin
  Description: Most Powerful Form Builder to create wide variety of forms within a minute
  Version: 3.7.1
  Plugin URI: http://www.arformsplugin.com/
  Author: Repute InfoSystems
  Author URI: http://reputeinfosystems.com/
  Text Domain: ARForms
 */

if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false))
    header('X-UA-Compatible: IE=edge,chrome=1');

define('ARFPLUGINTITLE', 'ARForms');
define('ARFPLUGINNAME', 'ARForms');
define('FORMPATH', WP_PLUGIN_DIR . '/arforms');
define('MODELS_PATH', FORMPATH . '/core/models');
define('VIEWS_PATH', FORMPATH . '/core/views');
define('HELPERS_PATH', FORMPATH . '/core/helpers');
define('CONTROLLERS_PATH', FORMPATH . '/core/controllers');
define('AUTORESPONDER_PATH', FORMPATH . '/core/ar/');
if( !defined('FS_METHOD') ){
	define('FS_METHOD', 'direct');
}
define('PLUGIN_BASE_FILE', plugin_basename(__FILE__));

$geoip_file = MODELS_PATH.'/geoip/autoload.php';
if( file_exists($geoip_file) ){
    include $geoip_file;
}
use GeoIp2\Database\Reader;

global $arfsiteurl, $is_active_cornorstone;
$is_active_cornorstone = false;

global $arf_mcapi_version;
$arf_mcapi_version = '3.0';

if( !function_exists('is_plugin_active') ){
    require(ABSPATH.'/wp-admin/includes/plugin.php');
}

$cs_splugin = 'cornerstone/cornerstone.php';
if (is_plugin_active($cs_splugin)) {
    $is_active_cornorstone = true;
}


$arfsiteurl = home_url();
if (is_ssl() and ( !preg_match('/^https:\/\/.*\..*$/', $arfsiteurl) or ! preg_match('/^https:\/\/.*\..*$/', WP_PLUGIN_URL))) {
    $arfsiteurl = str_replace('http://', 'https://', $arfsiteurl);
    define('ARFURL', str_replace('http://', 'https://', WP_PLUGIN_URL . '/arforms'));
} else {
    define('ARFURL', WP_PLUGIN_URL . '/arforms');
}


if (!defined('ARF_FILEDRAG_SCRIPT_URL')) {
    define('ARF_FILEDRAG_SCRIPT_URL', plugins_url('', __FILE__));
}


define('ARFSCRIPTURL', $arfsiteurl . (is_admin() ? '/wp-admin' : '') . '/?plugin=ARForms');
define('ARFIMAGESURL', ARFURL . '/images');
define('ARFAWEBERURL', ARFURL . '/core/ar/aweber/configuration.php');


if ($is_active_cornorstone) {
    define('ARF_CSURL', ARFURL . '/arforms_cs');
    define('ARF_CSDIR', FORMPATH . '/arforms_cs');
}

define('ARF_AWEBER_CONSUMER_KEY', 'AkZx4YJlD9mf6HHcfX4SdsdV');
define('ARF_AWEBER_CONSUMER_SECRET', 'LNT9G9Yg4eEO4GehL4C8wdvhHeq27ywLzsNQk7a1');

define('ARF_LOADER_ICON','<div class="arf_loader_icon_wrapper" id="{arf_id}"><div class="arf_loader_icon_box"><div class="arf-spinner arf-skeleton arf-grid-loader"></div></div></div>');


define('ARF_PLUS_ICON', '<path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.134,20.362c-5.521,0-9.996-4.476-9.996-9.996c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.13,15.887,16.654,20.362,11.134,20.362z M11.133,2.314c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052C19.185,5.919,15.579,2.314,11.133,2.314z M12.146,14.341h-2v-3h-3v-2h3V6.372h2v2.969h3v2h-3V14.341z"/>');

define('ARF_MINUS_ICON', '<path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.12,20.389c-5.521,0-9.996-4.476-9.996-9.996c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.116,15.913,16.64,20.389,11.12,20.389z M11.119,2.341c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052C19.17,5.945,15.565,2.341,11.119,2.341z M12.131,11.367h3v-2h-3h-2h-3v2h3H12.131z" />');

define('ARF_CUSTOM_UNCHECKED_ICON', '<path id="arfcheckbox_unchecked" d="M15.643,17.617H3.499c-1.34,0-2.427-1.087-2.427-2.429V3.045  c0-1.341,1.087-2.428,2.427-2.428h12.144c1.342,0,2.429,1.087,2.429,2.428v12.143C18.072,16.53,16.984,17.617,15.643,17.617z   M16.182,2.477H2.961v13.221h13.221V2.477z" />');

define('ARF_CUSTOM_CHECKED_ICON', '<path id="arfcheckbox_checked" d="M15.645,17.62H3.501c-1.34,0-2.427-1.087-2.427-2.429V3.048  c0-1.341,1.087-2.428,2.427-2.428h12.144c1.342,0,2.429,1.087,2.429,2.428v12.143C18.074,16.533,16.986,17.62,15.645,17.62z   M16.184,2.48H2.963v13.221h13.221V2.48z M5.851,7.15l2.716,2.717l5.145-5.145l1.718,1.717l-5.146,5.145l0.007,0.007l-1.717,1.717  l-0.007-0.008l-0.006,0.008l-1.718-1.717l0.007-0.007L4.134,8.868L5.851,7.15z" />');

define('ARF_CUSTOM_UNCHECKED_ICON_EDITOR', '');
define('ARF_CUSTOM_CHECKED_ICON_EDITOR', '<path fill="#353942" d="M7.698,13.386c-0.365,0-0.731-0.14-1.01-0.418L1.641,7.919c-0.558-0.558-0.558-1.462,0-2.02s1.461-0.558,2.019,0  l4.039,4.039l9.086-9.086c0.558-0.558,1.462-0.558,2.019,0c0.558,0.558,0.558,1.462,0,2.019L8.708,12.967  C8.429,13.246,8.063,13.386,7.698,13.386z"/>');

define('ARF_CUSTOM_UNCHECKEDRADIO_ICON', '<path id="arfradio" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#B3BBCB" d="M9.03,16.688c-4.418,0-8-3.583-8-8.001s3.582-8.001,8-8.001  s8,3.583,8,8.001S13.448,16.688,9.03,16.688z M9.029,2.887c-3.203,0-5.798,2.596-5.798,5.799s2.596,5.799,5.798,5.799  c3.203,0,5.8-2.596,5.8-5.799S12.232,2.887,9.029,2.887z"/>');

define('ARF_CUSTOM_CHECKEDRADIO_ICON', '<path id="arfradio_checked" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#03A9F4" d="M9.03,16.688c-4.418,0-8-3.583-8-8.001s3.582-8.001,8-8.001  s8,3.583,8,8.001S13.448,16.688,9.03,16.688z M9.029,2.887c-3.203,0-5.798,2.596-5.798,5.799s2.596,5.799,5.798,5.799  c3.203,0,5.8-2.596,5.8-5.799S12.232,2.887,9.029,2.887z M9.03,12.117c-1.895,0-3.43-1.537-3.43-3.43c0-1.895,1.535-3.43,3.43-3.43  c1.894,0,3.43,1.535,3.43,3.43C12.46,10.58,10.924,12.117,9.03,12.117z"/>');

define('ARF_CUSTOM_REQUIRED_ICON', '<path d="M16.975,7.696l-0.732-2.717l-6.167,1.865l0.312-6.276H7.562l0.31,6.276L1.666,4.979L0.975,7.696L7.1,8.939l-3.69,5.574
    l2.327,1.555l3.218-5.734l3.259,5.734l2.286-1.555L10.85,8.939L16.975,7.696z" fill="#ffffff"/>');

define('ARF_CUSTOM_MULTICOLUMN_ICON', "<path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#9EABC9' d='M9.489,8.85l0.023-2h6l-0.024,2H9.489z M9.489,2.85l0.023-2h6  l-0.024,2H9.489z M1.489,14.85l0.023-2h5.969l-0.023,2H1.489z M1.489,8.85l0.023-2h5.969l-0.023,2H1.489z M1.489,2.85l0.023-2h5.969  l-0.023,2H1.489z M15.512,12.85l-0.024,2H9.489l0.023-2H15.512z'/>");

define('ARF_CUSTOM_CUSTOMCSS_ICON', "<path xmlns='http://www.w3.org/2000/svg' fill='#9EABC9' d='M5.451,7.921V4.386c0-0.469,0.207-0.912,0.584-1.248c0.376-0.335,0.873-0.521,1.397-0.521V0.85  c-2.18,0-3.962,1.591-3.962,3.536v2.651c0,0.488-0.444,0.884-0.991,0.884h-0.99V9.69h0.99c0.547,0,0.991,0.396,0.991,0.884v2.652  c0,1.944,1.782,3.535,3.962,3.535v-1.768c-0.524,0-1.021-0.185-1.397-0.521c-0.377-0.336-0.584-0.779-0.584-1.247V9.69  c0-0.488-0.443-0.885-0.99-0.885C5.007,8.806,5.451,8.41,5.451,7.921z M13.375,9.69v3.536c0,0.468-0.207,0.911-0.583,1.247  c-0.377,0.336-0.873,0.521-1.398,0.521v1.769c2.18,0,3.963-1.592,3.963-3.536v-2.652c0-0.488,0.443-0.884,0.99-0.884h0.991V7.921  h-0.991c-0.547,0-0.99-0.396-0.99-0.884V4.386c0-1.945-1.783-3.536-3.963-3.536v1.768c0.525,0,1.021,0.186,1.398,0.521  c0.376,0.336,0.583,0.778,0.583,1.247v3.536c0,0.487,0.444,0.884,0.991,0.884C13.82,8.806,13.375,9.202,13.375,9.69z'/>");

define('ARF_CUSTOM_FIELDOPTION_ICON', '<path fill="#ffffff" d="M17.947,15.47l-1.633-1.362c0.584-0.854,0.973-1.824,1.139-2.838l2.172,0.175
    c0.232-0.002,0.42-0.189,0.42-0.421l-0.008-1.995c0-0.232-0.188-0.419-0.42-0.419l-2.201,0.197
    c-0.193-1.006-0.604-1.958-1.201-2.787l1.662-1.425c0.078-0.078,0.121-0.185,0.121-0.297c0-0.111-0.045-0.219-0.123-0.296
    l-1.414-1.406c-0.164-0.163-0.432-0.162-0.594,0.002l-1.42,1.706c-0.826-0.561-1.762-0.94-2.74-1.111l0.174-2.22
    c0-0.232-0.189-0.42-0.422-0.419L9.467,0.561c-0.232,0.001-0.42,0.19-0.42,0.421l0.197,2.22C8.26,3.379,7.318,3.771,6.492,4.344
    l-1.42-1.672C4.906,2.508,4.641,2.509,4.479,2.673L3.072,4.089C2.994,4.168,2.949,4.275,2.951,4.386
    c0,0.111,0.045,0.218,0.123,0.297L4.74,6.078C4.156,6.907,3.756,7.856,3.57,8.854L1.463,8.671c-0.23,0.001-0.418,0.189-0.418,0.422
    l0.006,1.994c0.002,0.232,0.189,0.42,0.422,0.419l2.074-0.188c0.17,1.005,0.561,1.965,1.143,2.811L3.07,15.483
    c-0.164,0.165-0.164,0.432,0.002,0.595l1.412,1.405c0.08,0.078,0.188,0.123,0.299,0.122C4.893,17.604,5,17.56,5.078,17.481
    l1.338-1.596c0.855,0.609,1.836,1.019,2.869,1.198l-0.184,2.06c0,0.232,0.189,0.42,0.422,0.419l1.992-0.007
    c0.232,0,0.42-0.19,0.42-0.421l-0.188-2.06c1.023-0.184,1.996-0.597,2.844-1.204l1.355,1.611c0.16,0.156,0.438,0.156,0.594-0.002
    l1.406-1.415C18.111,15.899,18.109,15.633,17.947,15.47z M10.561,15.223c-2.852,0.01-5.17-2.295-5.178-5.146
    c-0.008-2.853,2.295-5.172,5.146-5.182c2.85-0.01,5.168,2.294,5.178,5.146C15.715,12.893,13.41,15.213,10.561,15.223z"/>');

define('ARF_CUSTOM_COL1_ICON', '<path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M1.059,14.666v-2h17v2H1.059z M1.059,6.666h17v2h-17V6.666z M1.059,0.666h17v2h-17
  V0.666z"/>');

define('ARF_CUSTOM_COL2_ICON', '<path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M15.047,14.714v-2H27.03v2H15.047z M15.047,6.714H27.03v2H15.047V6.714z
   M15.047,0.714H27.03v2H15.047V0.714z M1.031,12.714h12.015v2H1.031V12.714z M1.03,6.714h12.015v2H1.03V6.714z M1.03,0.714h12.015v2
  H1.03V0.714z"/>');

define('ARF_CUSTOM_COL3_ICON', '<path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M18.07,14.615v-2h6.853v2H18.07z M18.069,6.615h6.853v2h-6.853V6.615zM18.069,0.615h6.853v2h-6.853V0.615z M9.497,12.615h6.853v2H9.497V12.615z M9.496,6.615h6.853v2H9.496V6.615z M9.496,0.615h6.853v2H9.496V0.615z M0.923,12.615h6.853v2H0.923V12.615z M0.922,6.615h6.853v2H0.922V6.615z M0.922,0.615h6.853v2H0.922V0.615z"/>');

define('ARF_CUSTOM_COL4_ICON', '<path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M27.928,14.646v-2h6.995v2H27.928z M27.927,6.646h6.995v2h-6.995V6.646z
   M27.927,0.646h6.995v2h-6.995V0.646z M18.927,12.646h6.995v2h-6.995V12.646z M18.926,6.646h6.995v2h-6.995V6.646z M18.926,0.646
  h6.995v2h-6.995V0.646z M9.925,12.646h6.995v2H9.925V12.646z M9.924,6.646h6.995v2H9.924V6.646z M9.924,0.646h6.995v2H9.924V0.646z
   M0.924,12.646h6.996v2H0.924V12.646z M0.923,6.646h6.996v2H0.923V6.646z M0.923,0.646h6.996v2H0.923V0.646z"/>');

define('ARF_CUSTOM_COL5_ICON', '<path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M34.931,14.599v-2h6.056v2H34.931z M34.93,6.599h6.056v2H34.93V6.599z
   M34.93,0.599h6.056v2H34.93V0.599z M26.445,12.599h6.057v2h-6.057V12.599z M26.444,6.599H32.5v2h-6.056V6.599z M26.444,0.599H32.5
  v2h-6.056V0.599z M17.959,12.599h6.057v2h-6.057V12.599z M17.958,6.599h6.056v2h-6.056V6.599z M17.958,0.599h6.056v2h-6.056V0.599z
   M9.474,12.599h6.057v2H9.474V12.599z M9.473,6.599h6.056v2H9.473V6.599z M9.473,0.599h6.056v2H9.473V0.599z M0.988,12.599h6.057v2
  H0.988V12.599z M0.987,6.599h6.057v2H0.987V6.599z M0.987,0.599h6.057v2H0.987V0.599z"/>');

define('ARF_CUSTOM_COL6_ICON', '<path fill="#ffffff" fill-rule="evenodd" clip-rule="evenodd" d="M36.022,14.568v-2h4.996v2H36.022z M36.021,6.568h4.996v2h-4.996V6.568z
   M36.021,0.568h4.996v2h-4.996V0.568z M29.021,12.568h4.996v2h-4.996V12.568z M29.021,6.568h4.996v2h-4.996V6.568z M29.021,0.568
  h4.996v2h-4.996V0.568z M22.021,12.568h4.996v2h-4.996V12.568z M22.02,6.568h4.996v2H22.02V6.568z M22.02,0.568h4.996v2H22.02V0.568
  z M15.021,12.568h4.996v2h-4.996V12.568z M15.02,6.568h4.996v2H15.02V6.568z M15.02,0.568h4.996v2H15.02V0.568z M8.02,12.568h4.996
  v2H8.02V12.568z M8.019,6.568h4.996v2H8.019V6.568z M8.019,0.568h4.996v2H8.019V0.568z M1.019,12.568h4.997v2H1.019V12.568z
   M1.018,6.568h4.997v2H1.018V6.568z M1.018,0.568h4.997v2H1.018V0.568z"/>');

define('ARF_CUSTOM_DUPLICATE_ITEM', "<path xmlns='http://www.w3.org/2000/svg' fill='#ffffff' d='M9.465,0.85h-6.72c-0.691,0-1.257,0.565-1.257,1.256v8.733H3.47V2.827h5.995V0.85z M13.227,3.833H5.728  c-0.691,0-1.258,0.565-1.258,1.257v11.509c0,0.691,0.566,1.257,1.258,1.257h7.499c0.691,0,1.257-0.565,1.257-1.257V5.089  C14.484,4.398,13.918,3.833,13.227,3.833z M12.465,15.869H6.469V5.837h5.996V15.869z'/>");

define('ARF_CUSTOM_DELETE_ICON', "<path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#ffffff' d='M16.939,5.845h-1.415V17.3c0,0.292-0.236,0.529-0.529,0.529H4.055  c-0.292,0-0.529-0.237-0.529-0.529V5.845H2.018c-0.292,0-0.529-0.739-0.529-1.031s0.237-0.982,0.529-0.982h2.509V1.379  c0-0.293,0.237-0.529,0.529-0.529h8.954c0.293,0,0.529,0.236,0.529,0.529v2.452h2.399c0.292,0,0.529,0.69,0.529,0.982  S17.231,5.845,16.939,5.845z M12.533,2.811H6.517v1.011h6.016V2.811z M13.541,5.845l-0.277-0.031L5.788,5.845H5.534v10.001h8.007  V5.845z M8.525,13.849H7.534v-6.08h0.991V13.849z M11.525,13.849h-0.991v-6.08h0.991V13.849z' />");

define('ARF_CUSTOM_MOVE_ICON', "<path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#3f74e7' stroke='#3f74e7' d='M18.401,9.574l-3.092,3.092  c-0.06,0.061-0.139,0.091-0.218,0.091s-0.159-0.03-0.219-0.091c-0.121-0.121-0.121-0.316,0-0.438l2.563-2.564H11.69  c-0.171,0-0.309-0.139-0.309-0.31c0-0.17,0.138-0.309,0.309-0.309h5.746l-2.563-2.564c-0.121-0.121-0.121-0.316,0-0.438  c0.12-0.121,0.316-0.121,0.437,0l3.092,3.092c0.028,0.029,0.051,0.063,0.066,0.101c0.031,0.076,0.031,0.161,0,0.236  C18.452,9.51,18.429,9.544,18.401,9.574z M13.081,4.56c-0.079,0-0.158-0.03-0.218-0.091l-2.563-2.564v5.748  c0,0.171-0.139,0.31-0.31,0.31s-0.31-0.139-0.31-0.31V1.905L7.117,4.469C7.057,4.53,6.978,4.56,6.899,4.56S6.741,4.53,6.68,4.469  c-0.121-0.12-0.121-0.316,0-0.437L9.771,0.94c0.028-0.028,0.063-0.051,0.101-0.066c0.075-0.031,0.161-0.031,0.236,0  c0.038,0.016,0.072,0.038,0.101,0.066l3.091,3.093c0.121,0.12,0.121,0.316,0,0.437C13.239,4.53,13.161,4.56,13.081,4.56z   M2.543,9.045H8.29c0.171,0,0.309,0.139,0.309,0.309c0,0.171-0.138,0.31-0.309,0.31H2.543l2.563,2.564  c0.121,0.121,0.121,0.316,0,0.438c-0.06,0.061-0.139,0.091-0.218,0.091c-0.08,0-0.158-0.03-0.219-0.091L1.58,9.574  C1.55,9.544,1.528,9.51,1.512,9.472c-0.031-0.075-0.031-0.16,0-0.236C1.528,9.198,1.55,9.164,1.58,9.135L4.67,6.043  c0.12-0.121,0.316-0.121,0.437,0c0.121,0.121,0.121,0.316,0,0.438L2.543,9.045z M7.117,14.239l2.563,2.564v-5.747  c0-0.171,0.139-0.31,0.31-0.31s0.31,0.139,0.31,0.31v5.747l2.563-2.564c0.121-0.12,0.315-0.12,0.437,0  c0.121,0.121,0.121,0.316,0,0.438l-3.091,3.092c-0.028,0.029-0.063,0.052-0.101,0.067S10.03,17.86,9.99,17.86  s-0.08-0.009-0.118-0.024s-0.072-0.038-0.101-0.067L6.68,14.676c-0.121-0.121-0.121-0.316,0-0.438  C6.801,14.119,6.997,14.119,7.117,14.239z' />");

define('ARF_CUSTOM_CLOSE_BUTTON', "<path xmlns='http://www.w3.org/2000/svg' fill-rule='evenodd' clip-rule='evenodd' fill='#333333' d='M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249  L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z' />");

define('ARF_TOOLTIP_ICON', '<path xmlns="http://www.w3.org/2000/svg" d="M9.609,0.33c-4.714,0-8.5,3.786-8.5,8.5s3.786,8.5,8.5,8.5s8.5-3.786,8.5-8.5S14.323,0.33,9.609,0.33z   M10.381,13.467c0,0.23-0.154,0.387-0.387,0.387H9.222c-0.231,0-0.387-0.156-0.387-0.387v-0.772c0-0.231,0.155-0.388,0.387-0.388  h0.772c0.232,0,0.387,0.156,0.387,0.388V13.467z M11.425,10.028c-0.541,0.463-0.929,0.772-1.044,1.197  c-0.039,0.193-0.193,0.309-0.387,0.309H9.222c-0.231,0-0.426-0.193-0.387-0.425c0.155-1.12,0.966-1.738,1.623-2.279  c0.697-0.541,1.082-0.889,1.082-1.546c0-1.082-0.85-1.932-1.932-1.932s-1.933,0.85-1.933,1.932c0,0.078,0,0.154,0,0.232  c0.04,0.192-0.077,0.386-0.27,0.425L6.672,8.173C6.44,8.25,6.208,8.096,6.169,7.864C6.131,7.67,6.131,7.478,6.131,7.284  c0-1.932,1.545-3.478,3.478-3.478c1.932,0,3.477,1.546,3.477,3.478C13.085,8.714,12.16,9.448,11.425,10.028L11.425,10.028z" fill="#BEC5D5"/>');

define('ARF_CUSTOM_MOVING_ICON','<path fill="#ffffff" d="M20.062,10.027l-3.563-3.563V8.84h-4.75V4.088h2.376l-3.563-3.562L6.999,4.088h2.375V8.84h-4.75V6.464
    l-3.563,3.563l3.563,3.563v-2.376h4.75v4.751H6.999l3.563,3.562l3.563-3.562h-2.376v-4.751h4.75v2.376L20.062,10.027z"/>');

define('ARF_CUSTOM_RESET_ICON','<path fill="#B4BACA" d="M83.803,13.197C74.896,5.009,63.023,0,50,0C22.43,0,0,22.43,0,50s22.43,50,50,50c13.763,0,26.243-5.59,35.293-14.618  l-9.895-9.895C68.883,81.979,59.902,86,50,86c-19.851,0-36-16.149-36-36s16.149-36,36-36c9.164,0,17.533,3.447,23.895,9.105L62,35  h20.713H96v-4.586V1L83.803,13.197z"/>');

define('ARF_FIELD_EDIT_OPTION_ICON','<path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#ffffff" d="M14.968,5.735l-0.223,0.22l-2.817-2.78l1.351-1.333l1.689,1.666   l3.599-3.552l1.351,1.333l-4.728,4.666L14.968,5.735z M0.923,8.951h9v3h-9V8.951z M0.923,1.951h9v3h-9V1.951z M14.968,10.507   l3.599-3.552l1.351,1.333l-4.728,4.666l-0.222-0.22l-0.223,0.22l-2.817-2.78l1.351-1.333L14.968,10.507z"/>');

define('ARF_FIELD_HTML_RUNNING_TOTAL_ICON','<path xmlns="http://www.w3.org/2000/svg" fill="#ffffff" d="M10.844,0.452H0.833v1.749L6.256,7.45l-5.423,5.249v1.749h10.011v-2.624H5.005L9.176,7.45L5.005,3.076 h5.839V0.452z"/>');

define('ARF_FIELD_MULTICOLUMN_EXPAND_ICON','<path xmlns="http://www.w3.org/2000/svg" fill="#ffffff" d="M8.88,8.166c0-0.269-0.096-0.538-0.287-0.742L2.549,0.977c-0.383-0.41-1.007-0.41-1.392,0   c-0.382,0.411-0.382,1.075,0,1.485l5.348,5.704L1.16,13.87c-0.385,0.409-0.385,1.075,0,1.485c0.383,0.411,1.007,0.411,1.39,0   l6.043-6.447C8.784,8.704,8.88,8.435,8.88,8.166z"/>');

define('ARF_STAR_RATING_ICON','<path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" d="M13.002-0.057l3.966,7.228l8.065,1.557l-5.615,6.024l1.019,8.19   l-7.436-3.505l-7.436,3.505l1.019-8.19L0.97,8.728l8.066-1.557L13.002-0.057"/>');

define('ARF_LIFEBOUY_ICON','<path fill="#FF5A5A" d="M10.079,0.623c-4.971,0-9,4.029-9,9s4.029,9,9,9s9-4.029,9-9C19.073,4.654,15.047,0.628,10.079,0.623z    M10.079,1.796c1.159-0.001,2.304,0.257,3.35,0.755l-2.133,2.132c-0.833-0.206-1.705-0.197-2.534,0.025L6.645,2.593   C7.713,2.068,8.888,1.795,10.079,1.796z M5.14,10.839l-2.132,2.133c-1.02-2.149-1.005-4.646,0.041-6.783l2.117,2.117   c-0.222,0.828-0.231,1.699-0.025,2.532V10.839z M10.079,17.449c-1.224,0.002-2.43-0.285-3.521-0.838l2.107-2.097   c0.893,0.26,1.841,0.27,2.739,0.027l2.109,2.11C12.444,17.177,11.269,17.45,10.079,17.449z M10.079,13.536   c-2.161,0-3.913-1.752-3.913-3.913s1.752-3.913,3.913-3.913s3.913,1.752,3.913,3.913S12.24,13.536,10.079,13.536z M17.905,9.623   c0.001,1.19-0.271,2.365-0.797,3.434l-2.116-2.117c0.242-0.898,0.232-1.846-0.027-2.739l2.103-2.1   C17.62,7.192,17.907,8.399,17.905,9.623z"/>');

define('ARF_EDIT_ENTRY_ICON','<path fill="#4786ff" d="M29.015,12.169l-0.808,0.809l0,0l-0.018,0.018l0,0l0,0 l-1.651,1.652l-2.478-2.479l1.669-1.669l0,0l0.809-0.808L29.015,12.169z M16.333,24.709h-2.336v-2.336L16.333,24.709z M18.02,16.669l-12,0.011v-1.979h12V16.669z M6.02,6.675h12v2.01h-12V6.675z M18.02,12.684h-12v-2.01h12V12.684z M25.711,15.474 l-8.433,8.435L14.8,21.431l6.203-6.204V2.699H2.995v23.972h18.008v-6.385L23,18.222v10.483H0.999V0.696H23v12.533l0.233-0.233 L25.711,15.474z"/>');

require_once(FORMPATH . '/core/wp_ar_auto_update.php');
require_once(MODELS_PATH . '/arsettingmodel.php');
require_once(MODELS_PATH . '/arstylemodel.php');

$wp_upload_dir = wp_upload_dir();
$imageupload_dir = $wp_upload_dir['basedir'] . '/arforms/userfiles/';
$imageupload_dir_sub = $wp_upload_dir['basedir'] . '/arforms/userfiles/thumbs/';
$import_preset_value = $wp_upload_dir['basedir'] . '/arforms/import_preset_value/';

if (!is_dir($imageupload_dir))
    wp_mkdir_p($imageupload_dir);

if (!is_dir($imageupload_dir_sub))
    wp_mkdir_p($imageupload_dir_sub);

if (!is_dir($import_preset_value))
    wp_mkdir_p($import_preset_value);
if (!defined('IS_WPMU')) {
    global $wpmu_version;
    $is_wpmu = ((function_exists('is_multisite') and is_multisite()) or $wpmu_version) ? 1 : 0;
    define('IS_WPMU', $is_wpmu);
}

global $arfversion, $arfdbversion, $arfadvanceerrcolor, $arf_memory_limit, $memory_limit, $arf_jscss_version;
$arfversion = '3.7.1';
$arfdbversion = '3.7.1';
$arf_jscss_version=$arfversion.'.'.rand(10,100);
$arf_memory_limit = 256;
$memory_limit = ini_get("memory_limit");

if (isset($memory_limit)) {
    if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
        if ($matches[2] == 'M') {
            $memory_limit = $matches[1] * 1024 * 1024;
        } else if ($matches[2] == 'K') {
            $memory_limit = $matches[1] * 1024;
        }
    }
} else {
    $memory_limit = 0;
}

global $arfajaxurl;
$arfajaxurl = admin_url('admin-ajax.php');

global $arformsplugin;
global $valid_wp_version;
global $arf_get_version_val;
global $check_current_val;

global $arfloadcss, $arfforms_loaded, $arfcssloaded, $arfsavedentries, $arf_form_all_footer_js, $arf_loaded_form_unique_id_array;
$arfloadcss = $arfcssloaded = false;
$arfforms_loaded = $arfsavedentries = $arf_loaded_form_unique_id_array = array();
$arf_form_all_footer_js = '';

add_action('admin_enqueue_scripts','arf_declare_js_actions',1);

function arf_declare_js_actions(){
    $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
    if( !isset($page) || $page == "" ){
        return;
    }
    preg_match("/(ARForms(|\-(.*?)))/",$page,$matches);
    if( empty($matches) ){
        return;
    }
    ?>
    <script type="text/javascript">
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
    </script>
  <?php
}

require_once(HELPERS_PATH . '/armainhelper.php');
global $armainhelper;
$armainhelper = new armainhelper();

require_once(MODELS_PATH . '/arinstallermodel.php');
require_once(MODELS_PATH . '/arfieldmodel.php');
require_once(MODELS_PATH . '/arformmodel.php');
require_once(MODELS_PATH . '/arrecordmodel.php');
require_once(MODELS_PATH . '/arrecordmeta.php');

global $MdlDb;
global $arffield;
global $arfform;
global $db_record;
global $arfrecordmeta;

global $arfsettings;
global $style_settings;
global $arsettingmodel;

$MdlDb = new arinstallermodel();
$arffield = new arfieldmodel();
$arfform = new arformmodel();
$db_record = new arrecordmodel();
$arfrecordmeta = new arrecordmeta();
$arsettingmodel = new arsettingmodel();

require_once(CONTROLLERS_PATH . '/maincontroller.php');
require_once(CONTROLLERS_PATH . '/arformcontroller.php');
require_once(CONTROLLERS_PATH . '/spamfiltercontroller.php');
require_once(CONTROLLERS_PATH . '/arelementcontroller.php');

global $maincontroller;
global $arformcontroller;
global $spam_filter_controller;
global $arelementcontroller;

$maincontroller = new maincontroller();
$arformcontroller = new arformcontroller();
$spam_filter_controller = new spamfiltercontroller();
$arelementcontroller= new arelementcontroller();

require_once(HELPERS_PATH . '/arrecordhelper.php');
require_once(HELPERS_PATH . '/arformhelper.php');
require_once(MODELS_PATH . '/arnotifymodel.php');

global $arnotifymodel;
$arnotifymodel = new arnotifymodel();

require_once(CONTROLLERS_PATH . "/arrecordcontroller.php");
require_once(CONTROLLERS_PATH . "/arfieldcontroller.php");
require_once(CONTROLLERS_PATH . "/arsettingcontroller.php");
require_once(CONTROLLERS_PATH . "/arsamplecontroller.php");

global $arrecordcontroller;
global $arfieldcontroller;
global $arsettingcontroller;
global $arsamplecontroller;

$arrecordcontroller = new arrecordcontroller();
$arfieldcontroller = new arfieldcontroller();
$arsettingcontroller = new arsettingcontroller();
$arsamplecontroller = new arsamplecontroller();

require_once(HELPERS_PATH . "/arfieldhelper.php");
global $arfieldhelper;
global $arrecordhelper;
global $arformhelper;
$arfieldhelper = new arfieldhelper();
$arrecordhelper = new arrecordhelper();
$arformhelper = new arformhelper();

global $arfnextpage, $arfprevpage;
$arfnextpage = $arfprevpage = array();

global $arfmediaid;
$arfmediaid = array();

global $arfreadonly;
$arfreadonly = false;

global $arfshowfields, $arfrtloaded, $arfdatepickerloaded;
global $arftimepickerloaded, $arfhiddenfields, $arfcalcfields, $arfinputmasks;

$arfshowfields = $arfrtloaded = $arfdatepickerloaded = $arftimepickerloaded = array();
$arfhiddenfields = $arfcalcfields = $arfinputmasks = array();

global $arfpagesize;
$arfpagesize = 20;
global $arfsidebar_width;
$arfsidebar_width = '';

global $arf_column_classes, $arf_column_classes_edit;
$arf_column_classes = $arf_column_classes_edit = array();
global $arf_page_number;
$arf_page_number = 0;
global $submit_ajax_page;
$submit_ajax_page = 0;
global $arf_section_div;
$arf_section_div = 0;
global $arf_captcha_loaded, $arf_file_loaded, $arf_modal_form_loaded;
$arf_captcha_loaded = $arf_file_loaded = $arf_modal_form_loaded = 0;

global $arf_slider_loaded;
$arf_slider_loaded = array();

global $arfmsgtounlicop;
$arfmsgtounlicop = '';

global $arf_password_loaded;
$arf_password_loaded = array();

global $arf_previous_label;
$arf_previous_label = array();

global $arf_selectbox_loaded;
$arf_selectbox_loaded = array();

global $arf_radio_checkbox_loaded;
$arf_radio_checkbox_loaded = array();

global $arf_conditional_logic_loaded;
$arf_conditional_logic_loaded = array();

global $arf_inputmask_loaded;
$arf_inputmask_loaded = array();

global $arfcolorpicker_loaded;
$arfcolorpicker_loaded = array();

global $arfcolorpicker_basic_loaded;
$arfcolorpicker_basic_loaded = array();

global $arf_wizard_form_loaded;
$arf_wizard_form_loaded = array();

global $arf_survey_form_loaded;
$arf_survey_form_loaded = array();

global $arf_entries_action_column_width;
$arf_entries_action_column_width = 120;

global $is_multi_column_loaded;
$is_multi_column_loaded = array();

global $conditional_logic_array_if, $conditional_logic_array_than, $footer_cl_logic;
$conditional_logic_array_if = array('file', 'divider', 'break', 'captcha', 'html', 'imagecontrol', 'password', 'arf_smiley', 'signature', 'confirm_email', 'confirm_password','arf_product','captcha','colorpicker','time');

$conditional_logic_array_than = array('captcha', 'hidden', 'confirm_email', 'imagecontrol', 'confirm_password', 'arf_product');

global $custom_css_array;
$custom_css_array = array(
    'arf_form_outer_wrapper' => array(
      'id' => 'form_outer_wrapper',
      'onclick_1' => 'arf_form_outer_wrapper',
      'onclick_2' => addslashes(esc_html__('Form outer wrapper', 'ARForms')),
      'label_title' => addslashes(esc_html__('Form outer wrapper', 'ARForms'))
    ),
    'arf_form_inner_wrapper' => array(
      'id' => 'form_inner_wrapper',
      'onclick_1' => 'arf_form_inner_wrapper',
      'onclick_2' => addslashes(esc_html__('Form inner wrapper', 'ARForms')),
      'label_title' => addslashes(esc_html__('Form inner wrapper', 'ARForms'))
    ),
    'arf_form_title' => array(
      'id' => 'form_title',
      'onclick_1' => 'arf_form_title',
      'onclick_2' => addslashes(esc_html__('Form Title', 'ARForms')),
      'label_title' => addslashes(esc_html__('Form title', 'ARForms'))
    ),
    'arf_form_description' => array(
      'id' => 'form_description',
      'onclick_1' => 'arf_form_description',
      'onclick_2' => addslashes(esc_html__('Form description', 'ARForms')),
      'label_title' => addslashes(esc_html__('Form description', 'ARForms'))
    ),
    'arf_form_element_wrapper' => array(
      'id' => 'field_wrapper',
      'onclick_1' => 'arf_form_element_wrapper',
      'onclick_2' => addslashes(esc_html__('Field wrapper', 'ARForms')),
      'label_title' => addslashes(esc_html__('Field Wrapper', 'ARForms'))
    ),
    'arf_form_element_label' => array(
      'id' => 'field_label',
      'onclick_1' => 'arf_form_element_label',
      'onclick_2' => addslashes(esc_html__('Field label', 'ARForms')),
      'label_title' => addslashes(esc_html__('Field label', 'ARForms'))
    ),
    'arf_form_text_elements' => array(
      'id' => 'text_elements',
      'onclick_1' => 'arf_form_text_elements',
      'label_title' => addslashes(esc_html__('Textbox Elements','ARForms'))
    ),
    'arf_form_textarea_elements' => array(
      'id' => 'textarea_elements',
      'onclick_1' => 'arf_form_textarea_elements',
      'label_title' => addslashes(esc_html__('Textarea Elements','ARForms'))
    ),
    'arf_form_phone_elements' => array(
      'id' => 'phone_elements',
      'onclick_1' => 'arf_form_phone_elements',
      'label_title' => addslashes(esc_html__('Phone Elements','ARForms'))
    ),
    'arf_form_number_elements' => array(
      'id' => 'number_elements',
      'onclick_1' => 'arf_form_number_elements',
      'label_title' => addslashes(esc_html__('Number Elements','ARForms'))
    ),
    'arf_form_email_elements' => array(
      'id' => 'email_elements',
      'onclick_1' => 'arf_form_email_elements',
      'label_title' => addslashes(esc_html__('Email Elements','ARForms'))
    ),
    'arf_form_password_elements' => array(
      'id' => 'password_elements',
      'onclick_1' => 'arf_form_password_elements',
      'label_title' => addslashes(esc_html__('Password Elements','ARForms'))
    ),
    'arf_form_date_elements' => array(
      'id' => 'date_elements',
      'onclick_1' => 'arf_form_date_elements',
      'label_title' => addslashes(esc_html__('Date Elements','ARForms'))
    ),
    'arf_form_time_elements' => array(
      'id' => 'time_elements',
      'onclick_1' => 'arf_form_time_elements',
      'label_title' => addslashes(esc_html__('Time Elements','ARForms'))
    ),
      'arf_form_switch_elements' => array(
      'id' => 'switch_elements',
      'onclick_1' => 'arf_form_switch_elements',
      'label_title' => addslashes(esc_html__('Switch Elements','ARForms'))
    ),
    'arf_form_url_elements' => array(
      'id' => 'url_elements',
      'onclick_1' => 'arf_form_url_elements',
      'label_title' => addslashes(esc_html__('Website Elements','ARForms'))
    ),
    'arf_form_image_url_elements' => array(
      'id' => 'img_url_elements',
      'onclick_1' => 'arf_form_image_url_elements',
      'label_title' => addslashes(esc_html__('Image URL Elements','ARForms'))
    ),
    'arf_form_page_break' => array(
      'id' => 'page_break',
      'onclick_1' => 'arf_form_page_break',
      'onclick_2' => addslashes(esc_html__('Page break', 'ARForms')),
      'label_title' => addslashes(esc_html__('Page break', 'ARForms'))),
    'arf_form_submit_button' => array('id' => 'submit_wrapper', 'onclick_1' => 'arf_form_submit_button', 'onclick_2' => addslashes(esc_html__('Submit Wrapper', 'ARForms')), 'label_title' => addslashes(esc_html__('Submit Wrapper', 'ARForms'))),
    'arf_form_next_button' => array('id' => 'next_button', 'onclick_1' => 'arf_form_next_button', 'onclick_2' => addslashes(esc_html__('Next Button', 'ARForms')), 'label_title' => addslashes(esc_html__('Next Button', 'ARForms'))),
    'arf_form_previous_button' => array('id' => 'prev_button', 'onclick_1' => 'arf_form_previous_button', 'onclick_2' => addslashes(esc_html__('Previous Button', 'ARForms')), 'label_title' => addslashes(esc_html__('Previous Button', 'ARForms'))),
    'arf_form_success_message' => array('id' => 'success_message', 'onclick_1' => 'arf_form_success_message', 'onclick_2' => addslashes(esc_html__('Success Message', 'ARForms')), 'label_title' => addslashes(esc_html__('Success Message', 'ARForms'))),
    'arf_form_error_message' => array('id' => 'validation_error', 'onclick_1' => 'arf_form_error_message', 'onclick_2' => addslashes(esc_html__('Validation (error)', 'ARForms')), 'label_title' => addslashes(esc_html__('Validation (error)', 'ARForms'))),
    'arf_form_fly_sticky' => array('id' => 'fly_sticky_button', 'onclick_1' => 'arf_form_fly_sticky', 'onclick_2' => addslashes(esc_html__('Fly / Stick Button', 'ARForms')), 'label_title' => addslashes(esc_html__('Fly / Stick Button', 'ARForms'))),
    'arf_form_modal_css' => array('id' => 'modal', 'onclick_1' => 'arf_form_modal_css', 'onclick_2' => addslashes(esc_html__('Form Modal wrapper', 'ARForms')), 'label_title' => addslashes(esc_html__('Modal', 'ARForms'))),
    'arf_form_link_css' => array('id' => 'popup_link', 'onclick_1' => 'arf_form_link_css', 'onclick_2' => addslashes(esc_html__('Link (popup)', 'ARForms')), 'label_title' => addslashes(esc_html__('Link (popup)', 'ARForms'))),
    'arf_form_link_hover_css' => array('id' => 'popup_link_hover', 'onclick_1' => 'arf_form_link_hover_css', 'onclick_2' => addslashes(esc_html__('Link Hover (popup)', 'ARForms')), 'label_title' => addslashes(esc_html__('Link Hover (popup)', 'ARForms'))),
    'arf_form_button_css' => array('id' => 'popup_button', 'onclick_1' => 'arf_form_button_css', 'onclick_2' => addslashes(esc_html__('Button (popup)', 'ARForms')), 'label_title' => addslashes(esc_html__('Button (popup)', 'ARForms'))),
    'arf_form_button_hover_css' => array('id' => 'popup_button_hover', 'onclick_1' => 'arf_form_button_hover_css', 'onclick_2' => addslashes(esc_html__('Button Hover (popup)', 'ARForms')), 'label_title' => addslashes(esc_html__('Button Hover (popup)', 'ARForms'))),
);

global $is_arf_preview;
$is_arf_preview = 0;
global $api_url, $plugin_slug;

if (class_exists('WP_Widget')) {
    require_once(FORMPATH . '/core/widgets/ARFwidgetForm.php');
    add_action('widgets_init', 'arf_init_widget');
    function arf_init_widget(){
      return register_widget("ARFwidgetForm");
    }
}

if (file_exists(FORMPATH . '/core/vc/class_vc_extend.php')) {
    require_once( ( FORMPATH . '/core/vc/class_vc_extend.php' ) );
    global $arforms_vdextend;
    $arforms_vdextend = new ARForms_VCExtendArp();
}
// Add Dashboard File
if (file_exists(VIEWS_PATH . '/arf_dashboard.php')) {
    require_once(VIEWS_PATH . '/arf_dashboard.php');
    global $arf_dashboard_widget;
    $arf_dashboard_widget = new ARF_Dashboard_Widget();
}

// add smiley field 
if (file_exists(VIEWS_PATH . '/smiley_field.php')) {
    require_once(VIEWS_PATH . '/smiley_field.php');
}

// add switch field 
if (file_exists(VIEWS_PATH . '/arf_switch_field.php')) {
    require_once(VIEWS_PATH . '/arf_switch_field.php');
}

// Add Conditional Redirect to url File
if (file_exists(VIEWS_PATH . '/arf_conditional_redirect_to_url.php')) {
    require_once(VIEWS_PATH . '/arf_conditional_redirect_to_url.php');
}

// Add post Values File
if (file_exists(VIEWS_PATH . '/arf_post_value.php')) {
    require_once(VIEWS_PATH . '/arf_post_value.php');
}

// Add Condition on subscription
if (file_exists(VIEWS_PATH . '/arf_condition_on_subscription.php')) {
    require_once(VIEWS_PATH . '/arf_condition_on_subscription.php');
}
// Add autocomplete select field
if (file_exists(VIEWS_PATH . '/arf_autocomplete_select_field.php')) {
    require_once(VIEWS_PATH . '/arf_autocomplete_select_field.php');
}

if (file_exists(CONTROLLERS_PATH . '/arfdisplaypopup.php')) {
    require_once(CONTROLLERS_PATH . '/arfdisplaypopup.php');
}

// Add Model in menu
if (file_exists(VIEWS_PATH . '/arf_modal_view_in_menu.php')) {
    require_once(VIEWS_PATH . '/arf_modal_view_in_menu.php');
}

if( file_exists(VIEWS_PATH.'/arf_confirmation_summary.php') ){
    require_once(VIEWS_PATH.'/arf_confirmation_summary.php');
}

if( file_exists(VIEWS_PATH.'/arf_field_type_conversion.php') ){
    require_once(VIEWS_PATH.'/arf_field_type_conversion.php');
}

if (file_exists(VIEWS_PATH . '/arf_prevent_duplicate.php')) {
    require_once(VIEWS_PATH . '/arf_prevent_duplicate.php');
}

// Add Email Marketing Tools
if (file_exists(VIEWS_PATH . '/arf_mailerlite.php')) {
    require_once(VIEWS_PATH . '/arf_mailerlite.php');
}


global $fields_with_external_js, $bootstraped_fields_array;
$fields_with_external_js = array();
$bootstraped_fields_array = apply_filters('arf_bootstraped_field_from_outside', array('select', 'date', 'time', 'colorpicker'));

function pluginUninstall() {
    global $wpdb, $arsettingcontroller, $MdlDb;

    if (IS_WPMU) {

        $blogs = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);
        if ($blogs) {
            foreach ($blogs as $blog) {
                switch_to_blog($blog['blog_id']);
                
                $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_autoresponder');
                $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_fields');
                $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_forms');
                $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_entries');
                $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_entry_values');
                $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_ar');
                $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_views');
                $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_popup_forms');
                
                $wpdb->query("DELETE FROM `" . $wpdb->options . "` WHERE  `option_name` LIKE  '%arf_previewtabledata%'");

                delete_option('_transient_arf_options');
                delete_option('_transient_arfa_options');
                delete_option('arfa_css');
                delete_option('_transient_arfa_css');
                delete_option('arf_options');
                delete_option('arf_db_version');
                delete_option('arf_ar_type');
                delete_option('arf_current_tab');
                delete_option('arfdefaultar');
                delete_option('arfa_options');
                delete_option('arf_global_css');
                delete_option('widget_arforms_widget_form');
                delete_option('arf_plugin_activated');
                delete_option('is_arf_submit');
                delete_option("arf_update_token");
                delete_option("arfformcolumnlist");
                delete_option("arfIsSorted");
                delete_option("arfSortOrder");
                delete_option("arfSortId");
                delete_option("arfSortInfo");
                delete_option("arf_form_entry_separator");
                delete_option("arf_previewoptions");
            }
            restore_current_blog();
        }
    } else {
        
        $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_autoresponder');
        $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_fields');
        $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_forms');
        $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_entries');
        $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_entry_values');
        $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_ar');
        $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_views');
        $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_popup_forms');
    
        delete_option('_transient_arf_options');
        delete_option('_transient_arfa_options');
        delete_option('arfa_css');
        delete_option('_transient_arfa_css');
        delete_option('arf_options');
        delete_option('arf_db_version');
        delete_option('arf_ar_type');
        delete_option('arf_current_tab');
        delete_option('arfdefaultar');
        delete_option('arfa_options');
        delete_option('arf_global_css');
        delete_option('widget_arforms_widget_form');
        delete_option('arf_plugin_activated');
        delete_option('is_arf_submit');
        delete_option("arf_update_token");
        delete_option("arfformcolumnlist");
        delete_option("arfIsSorted");
        delete_option("arfSortOrder");
        delete_option("arfSortId");
        delete_option("arfSortInfo");
        delete_option("arf_form_entry_separator");
        delete_option("arf_previewoptions");
        

        $wpdb->query("DELETE FROM `" . $wpdb->options . "` WHERE  `option_name` LIKE  '%arf_previewtabledata%'");
    }
    $arsettingcontroller->arfreqlicdeactuninst();
}

register_uninstall_hook(__FILE__, 'pluginUninstall');

global $arformcontroller;

$api_url = $arformcontroller->arfgetapiurl();
$plugin_slug = basename(dirname(__FILE__));

$file   = basename( __FILE__ );
$folder = basename( dirname( __FILE__ ) );
$hook = "in_plugin_update_message-{$folder}/{$file}";
add_action( $hook, 'update_message_arforms_plugin', 10, 2 ); 

function update_message_arforms_plugin( $plugin_data, $r )
{
    global $api_url, $plugin_slug, $wp_version, $maincontroller, $arfversion;

    $compare_version = "";
	
    $args = array(
        'slug' => $plugin_slug,
        'version' => $arfversion,
        'other_variables' => $maincontroller->arf_get_remote_post_params(),
    );

    $request_string = array(
        'body' => array(
            'action' => 'plugin_new_version_check',
            'request' => serialize($args),
            'api-key' => md5(home_url())
        ),
        'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url()
    );

    
    $raw_response = wp_remote_post($api_url, $request_string);
	
	if (isset($raw_response['body']) && $raw_response['body']!= "")
	{
    	$compare_version = $raw_response['body'];
	}
	
    if($compare_version != "" )
    {
        if(version_compare($compare_version, $arfversion, '>'))
        {
            $setvaltolic = 0;
            global $arformcontroller,$arformsplugin;
            $setvaltolic = $arformcontroller->$arformsplugin(); 

            if($setvaltolic != 1)
            {
                $license_link = admin_url('admin.php?page=ARForms-license');

                echo " <i> To avail automatic update, Click <a href=".$license_link.">here</a> to activate your license.</i>";
            }

        }    
    }   

}

add_filter('pre_set_site_transient_update_plugins', 'arf_check_for_plugin_update');

function arf_check_for_plugin_update($checked_data) {
    global $api_url, $plugin_slug, $wp_version, $maincontroller, $arfversion;


    if (empty($checked_data->checked))
        return $checked_data;

    $args = array(
        'slug' => $plugin_slug,
        'version' => $arfversion,
        'other_variables' => $maincontroller->arf_get_remote_post_params(),
    );

    $request_string = array(
        'body' => array(
            'action' => 'basic_check',
            'request' => serialize($args),
            'api-key' => md5(home_url())
        ),
        'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url()
    );


    $raw_response = wp_remote_post($api_url, $request_string);

    if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200) && isset($raw_response['body']) && $raw_response['body']!= "")
	{
        $response = maybe_unserialize($raw_response['body']);
	}
	
    if (isset($response->token)) {
        update_option('arf_update_token', $response->token);
    }


    if (isset($response) && is_object($response) && is_object($checked_data) && !empty($response))
        $checked_data->response[$plugin_slug . '/' . $plugin_slug . '.php'] = $response;

    return $checked_data;
}

add_filter('plugins_api', 'arf_plugin_api_call', 10, 3);

function arf_plugin_api_call($def, $action, $args) {
    global $plugin_slug, $api_url, $wp_version;

    if (!isset($args->slug) || ($args->slug != $plugin_slug))
        return false;


    $plugin_info = get_site_transient('update_plugins');
    $current_version = $plugin_info->checked[$plugin_slug . '/' . $plugin_slug . '.php'];
    $args->version = $current_version;

    $request_string = array(
        'body' => array(
            'action' => $action,
            'update_token' => get_site_option('arf_update_token'),
            'request' => serialize($args),
            'api-key' => md5(home_url())
        ),
        'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url()
    );

    $request = wp_remote_post($api_url, $request_string);

    if (is_wp_error($request)) {
        $res = new WP_Error('plugins_api_failed', 'An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>', $request->get_error_message());
    } else {
        $res = maybe_unserialize($request['body']);

        if ($res === false)
            $res = new WP_Error('plugins_api_failed', 'An unknown error occurred', $request['body']);
    }

    return $res;
}

add_action('plugins_loaded', 'arf_arform_load_textdomain');

function arf_arform_load_textdomain() {
    load_plugin_textdomain('ARForms', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

add_action('admin_notices', 'arf_addon_version_admin_notices');

function arf_addon_version_admin_notices(){
    $class = 'notice notice-error arf-notice-update-warning is-dismissible';

    $arf_plugin_list = "";
    if( file_exists(WP_PLUGIN_DIR.'/arformspdfcreator/arformspdfcreator.php') ){
	    $arf_addon_data = get_plugin_data(WP_PLUGIN_DIR.'/arformspdfcreator/arformspdfcreator.php');
	    $arf_addon_version = $arf_addon_data['Version'];

	    if($arf_addon_version < '1.4') {
	      $arf_plugin_list = $arf_addon_data['Name'].', ';
	    }
    }

    if( file_exists(WP_PLUGIN_DIR.'/arformsauthorizenet/arformsauthorizenet.php') ){

	    $arf_addon_data = get_plugin_data(WP_PLUGIN_DIR.'/arformsauthorizenet/arformsauthorizenet.php'); 
	    $arf_addon_version = $arf_addon_data['Version'];  
	    if($arf_addon_version < '1.4') {
	        $arf_plugin_list .= $arf_addon_data['Name'].', ';
	    }
    }
    
    if( file_exists(WP_PLUGIN_DIR.'/arformsusersignup/arformsusersignup.php') ){
	    $arf_addon_data = get_plugin_data(WP_PLUGIN_DIR.'/arformsusersignup/arformsusersignup.php'); 
	    $arf_addon_version = $arf_addon_data['Version'];  
	    if($arf_addon_version < '1.5') {
	      $arf_plugin_list .= $arf_addon_data['Name'].', ';
	    }
    }

    if( file_exists(WP_PLUGIN_DIR.'/arformsstripe/arformsstripe.php') ){
	    $arf_addon_data = get_plugin_data(WP_PLUGIN_DIR.'/arformsstripe/arformsstripe.php'); 
	    $arf_addon_version = $arf_addon_data['Version'];  
	    if($arf_addon_version < '1.3') {
	      $arf_plugin_list .= $arf_addon_data['Name'].', ';
	    }
    }

    if( file_exists(WP_PLUGIN_DIR.'/arformspaypal/arformspaypal.php') ){
	    $arf_addon_data = get_plugin_data(WP_PLUGIN_DIR.'/arformspaypal/arformspaypal.php'); 
	    $arf_addon_version = $arf_addon_data['Version'];  
	    if($arf_addon_version < '1.5') {
	      $arf_plugin_list .= $arf_addon_data['Name'].', ';
	    }
    }

    if( file_exists(WP_PLUGIN_DIR.'/arformspaypalpro/arformspaypalpro.php') ){
	    $arf_addon_data = get_plugin_data(WP_PLUGIN_DIR.'/arformspaypalpro/arformspaypalpro.php'); 
	    $arf_addon_version = $arf_addon_data['Version'];  
	    if($arf_addon_version < '1.2') {
	      $arf_plugin_list .= $arf_addon_data['Name'].', ';
	    }
    }

    if( file_exists(WP_PLUGIN_DIR.'/arformsmymail/arformsmymail.php') ){
	    $arf_addon_data = get_plugin_data(WP_PLUGIN_DIR.'/arformsmymail/arformsmymail.php'); 
	    $arf_addon_version = $arf_addon_data['Version'];  
	    if($arf_addon_version < '1.4') {
	      $arf_plugin_list .= $arf_addon_data['Name'].', ';
	    }
    }
    
    if( file_exists(WP_PLUGIN_DIR.'/arformsignature/arformsignature.php') ){
	    $arf_addon_data = get_plugin_data(WP_PLUGIN_DIR.'/arformsignature/arformsignature.php'); 
	    $arf_addon_version = $arf_addon_data['Version'];  
	    if($arf_addon_version < '1.3') {
	      $arf_plugin_list .= $arf_addon_data['Name'].', ';
	    }
    }

    if( file_exists(WP_PLUGIN_DIR.'/arformsdigitalproduct/arformsdigitalproduct.php') ){
	    $arf_addon_data = get_plugin_data(WP_PLUGIN_DIR.'/arformsdigitalproduct/arformsdigitalproduct.php'); 
	    $arf_addon_version = $arf_addon_data['Version'];  
	    if($arf_addon_version < '1.3') {
	      $arf_plugin_list .= $arf_addon_data['Name'].', ';
	    }
    }

    if( file_exists(WP_PLUGIN_DIR.'/arformssms/arformssms.php')){
	    $arf_addon_data = get_plugin_data(WP_PLUGIN_DIR.'/arformssms/arformssms.php');
	    $arf_addon_version = $arf_addon_data['Version'];  
	    if($arf_addon_version < '1.2') {
	      $arf_plugin_list .= $arf_addon_data['Name'].', ';
	    }
    }

    if( file_exists(WP_PLUGIN_DIR.'/arformsadvancemailchimp/arformsadvancemailchimp.php') ){
      $arf_addon_data = get_plugin_data( WP_PLUGIN_DIR.'/arformsadvancemailchimp/arformsadvancemailchimp.php' );
      $arf_addon_version = $arf_addon_data['Version'];
      if( $arf_addon_version < '1.1' ){
        $arf_plugin_list .= $arf_addon_data['Name'].', ';
      }
    }

    if(!empty($arf_plugin_list)) {
      $arf_plugin_list = rtrim(trim($arf_plugin_list),',');
      printf( '<div class="%1$s"><p><b>One or more add-on of ARForms must be updated with latest version</b> (%2$s).</p></div>', esc_attr( $class ), esc_html( $arf_plugin_list ) ); 
    }

    if( isset($_GET['arf_license_deactivate'])  && isset($_SESSION['arf_deactivate_plugin']) && $_SESSION['arf_deactivate_plugin'] != '' ){
        $arf_deactivate_plugin = $_SESSION['arf_deactivate_plugin'];
        echo "<div class='notice notice-error arf_auto_deactivate_addon_plugin' style='background:#fba9a9;border-left-color:#fba9a9;color:#fff;font-weight:bold;'><p>".sprintf(esc_html__('Please activate %s license to enable %s','ARForms'),'ARForms',$arf_deactivate_plugin)."</p></div>";
        unset($_SESSION['arf_deactivate_plugin']);
    }

    global $wpdb,$MdlDb;

    $res = $wpdb->get_row($wpdb->prepare("SELECT consumer_key,consumer_secret FROM ".$MdlDb->autoresponder." WHERE responder_id=%d AND is_verify = %d",3,1));    


    if( isset($res) && isset($res->consumer_key) && isset($res->consumer_secret) && '' != $res->consumer_key && '' != $res->consumer_secret ){
        echo "<div class='notice notice-warning' style='display:block !important;'>";
            echo "<p>";
                printf(esc_html__('Please re-authorize %s app again from %s -> General Settings -> Email Marketers, otherwise %s will not work.','ARForms'),'Aweber','ARForms','Aweber','Aweber');
            echo "</p>";
        echo "</div>";
    }


}

global $arf_installed_field_types;
$arf_installed_field_types = array(
  'text', 'textarea', 'checkbox', 'radio', 'select', 'file', 'email', 'captcha', 'number', 'phone', 'date', 'time', 'url', 'image', 'hidden', 'password', 'html', 'divider', 'break', 'scale', 'like', 'arfslider', 'colorpicker', 'imagecontrol','arf_switch','arfcreditcard',);

function get_country_code(){
    $country_code = array(
      0 => array(
          'name' => addslashes(esc_html__('Afghanistan', 'ARForms')),
          'dial_code' => '+93',
          'code' => 'af',
      ),
      1 => array(
          'name' => addslashes(esc_html__('Aland Islands', 'ARForms')),
          'dial_code' => '+358',
          'code' => 'ax',
      ),
      2 => array(
          'name' => addslashes(esc_html__('Albania', 'ARForms')),
          'dial_code' => '+355',
          'code' => 'al',
      ),
      3 => array(
          'name' => addslashes(esc_html__('Algeria', 'ARForms')),
          'dial_code' => '+213',
          'code' => 'dz',
      ),
      4 => array(
          'name' => addslashes(esc_html__('American Samoa', 'ARForms')),
          'dial_code' => '+1684',
          'code' => 'as',
      ),
      5 => array(
        'name' => addslashes(esc_html__('Andorra', 'ARForms')),
        'dial_code' => '+376',
        'code' => 'ad',
      ),
      6 => array(
        'name' => addslashes(esc_html__('Angola', 'ARForms')),
        'dial_code' => '+244',
        'code' => 'ao',
      ),
      7 => array(
      'name' => addslashes(esc_html__('Anguilla', 'ARForms')),
      'dial_code' => '+1264',
      'code' => 'ai',
      ),
      8 => array(
      'name' => addslashes(esc_html__('Antigua and Barbuda', 'ARForms')),
      'dial_code' => '+1268',
      'code' => 'ag',
      ),
      9 => array(
      'name' => addslashes(esc_html__('Argentina', 'ARForms')),
      'dial_code' => '+54',
      'code' => 'ar',
      ),
      10 => array(
      'name' => addslashes(esc_html__('Armenia', 'ARForms')),
      'dial_code' => '+374',
      'code' => 'am',
      ),
      11 => array(
      'name' => addslashes(esc_html__('Aruba', 'ARForms')),
      'dial_code' => '+297',
      'code' => 'aw',
      ),
      12 => array(
      'name' => addslashes(esc_html__('Australia', 'ARForms')),
      'dial_code' => '+61',
      'code' => 'au',
      ),
      13 => array(
      'name' => addslashes(esc_html__('Austria', 'ARForms')),
      'dial_code' => '+43',
      'code' => 'at',
      ),
      14 => array(
      'name' => addslashes(esc_html__('Azerbaijan', 'ARForms')),
      'dial_code' => '+994',
      'code' => 'az',
      ),
      15 => array(
      'name' => addslashes(esc_html__('Bahamas', 'ARForms')),
      'dial_code' => '+1242',
      'code' => 'bs',
      ),
      16 => array(
      'name' => addslashes(esc_html__('Bahrain', 'ARForms')),
      'dial_code' => '+973',
      'code' => 'bh',
      ),
      17 => array(
      'name' => addslashes(esc_html__('Bangladesh', 'ARForms')),
      'dial_code' => '+880',
      'code' => 'bd',
      ),
      18 => array(
      'name' => addslashes(esc_html__('Barbados', 'ARForms')),
      'dial_code' => '+1246',
      'code' => 'bb',
      ),
      19 => array(
      'name' => addslashes(esc_html__('Belarus', 'ARForms')),
      'dial_code' => '+375',
      'code' => 'by',
      ),
      20 => array(
      'name' => addslashes(esc_html__('Belgium', 'ARForms')),
      'dial_code' => '+32',
      'code' => 'be',
      ),
      21 => array(
      'name' => addslashes(esc_html__('Belize', 'ARForms')),
      'dial_code' => '+501',
      'code' => 'bz',
      ),
      22 => array(
      'name' => addslashes(esc_html__('Benin', 'ARForms')),
      'dial_code' => '+229',
      'code' => 'bj',
      ),
      23 => array(
      'name' => addslashes(esc_html__('Bermuda', 'ARForms')),
      'dial_code' => '+1441',
      'code' => 'bm',
      ),
      24 => array(
      'name' => addslashes(esc_html__('Bhutan', 'ARForms')),
      'dial_code' => '+975',
      'code' => 'bt',
      ),
      25 => array(
      'name' => addslashes(esc_html__('Bolivia', 'ARForms')),
      'dial_code' => '+591',
      'code' => 'bo',
      ),
      26 => array(
      'name' => addslashes(esc_html__('Bosnia and Herzegovina', 'ARForms')),
      'dial_code' => '+387',
      'code' => 'ba',
      ),
      27 => array(
      'name' => addslashes(esc_html__('Botswana', 'ARForms')),
      'dial_code' => '+267',
      'code' => 'bw',
      ),
      28 => array(
      'name' => addslashes(esc_html__('Brazil', 'ARForms')),
      'dial_code' => '+55',
      'code' => 'br',
      ),
      29 => array(
      'name' => addslashes(esc_html__('British Indian Ocean Territory', 'ARForms')),
      'dial_code' => '+246',
      'code' => 'io',
      ),
      30 => array(
      'name' => addslashes(esc_html__('British Virgin Islands', 'ARForms')),
      'dial_code' => '+1284',
      'code' => 'vg',
      ),
      31 => array(
      'name' => addslashes(esc_html__('Brunei', 'ARForms')),
      'dial_code' => '+673',
      'code' => 'bn',
      ),
      32 => array(
      'name' => addslashes(esc_html__('Bulgaria', 'ARForms')),
      'dial_code' => '+359',
      'code' => 'bg',
      ),
      33 => array(
      'name' => addslashes(esc_html__('Burkina Faso', 'ARForms')),
      'dial_code' => '+226',
      'code' => 'bf',
      ),
      34 => array(
      'name' => addslashes(esc_html__('Burundi', 'ARForms')),
      'dial_code' => '+257',
      'code' => 'bi',
      ),
      35 => array(
      'name' => addslashes(esc_html__('Cambodia', 'ARForms')),
      'dial_code' => '+855',
      'code' => 'kh',
      ),
      36 => array(
      'name' => addslashes(esc_html__('Cameroon', 'ARForms')),
      'dial_code' => '+237',
      'code' => 'cm',
      ),
      37 => array(
      'name' => addslashes(esc_html__('Canada', 'ARForms')),
      'dial_code' => '+1',
      'code' => 'ca',
      ),
      38 => array(
      'name' => addslashes(esc_html__('Cape Verde', 'ARForms')),
      'dial_code' => '+238',
      'code' => 'cv',
      ),
      39 => array(
      'name' => addslashes(esc_html__('Caribbean Netherlands', 'ARForms')),
      'dial_code' => '+599',
      'code' => 'bq',
      ),
      40 => array(
      'name' => addslashes(esc_html__('Cayman Islands', 'ARForms')),
      'dial_code' => '+1345',
      'code' => 'ky',
      ),
      41 => array(
      'name' => addslashes(esc_html__('Central African Republic', 'ARForms')),
      'dial_code' => '+236',
      'code' => 'cf',
      ),
      42 => array(
      'name' => addslashes(esc_html__('Chad', 'ARForms')),
      'dial_code' => '+235',
      'code' => 'td',
      ),
      43 => array(
      'name' => addslashes(esc_html__('Chile', 'ARForms')),
      'dial_code' => '+56',
      'code' => 'cl',
      ),
      44 => array(
      'name' => addslashes(esc_html__('China', 'ARForms')),
      'dial_code' => '+86',
      'code' => 'cn',
      ),
      45 => array(
      'name' => addslashes(esc_html__('Christmas Island', 'ARForms')),
      'dial_code' => '+61',
      'code' => 'cx',
      ),
      46 => array(
      'name' => addslashes(esc_html__('Cocos Islands', 'ARForms')),
      'dial_code' => '+61',
      'code' => 'cc',
      ),
      47 => array(
      'name' => addslashes(esc_html__('Colombia', 'ARForms')),
      'dial_code' => '+57',
      'code' => 'co',
      ),
      48 => array(
      'name' => addslashes(esc_html__('Comoros', 'ARForms')),
      'dial_code' => '+269',
      'code' => 'km',
      ),
      49 => array(
      'name' => addslashes(esc_html__('Congo (DRC)', 'ARForms')),
      'dial_code' => '+243',
      'code' => 'cd',
      ),
      50 => array(
      'name' => addslashes(esc_html__('Congo (Republic)', 'ARForms')),
      'dial_code' => '+242',
      'code' => 'cg',
      ),
      51 => array(
      'name' => addslashes(esc_html__('Cook Islands', 'ARForms')),
      'dial_code' => '+682',
      'code' => 'ck',
      ),
      52 => array(
      'name' => addslashes(esc_html__('Costa Rica', 'ARForms')),
      'dial_code' => '+506',
      'code' => 'cr',
      ),
      53 => array(
      'name' => addslashes(esc_html__('Cote d\'Ivoire', 'ARForms')),
      'dial_code' => '+225',
      'code' => 'ci',
      ),
      54 => array(
      'name' => addslashes(esc_html__('Croatia', 'ARForms')),
      'dial_code' => '+385',
      'code' => 'hr',
      ),
      55 => array(
      'name' => addslashes(esc_html__('Cuba', 'ARForms')),
      'dial_code' => '+53',
      'code' => 'cu',
      ),
      56 => array(
      'name' => addslashes(esc_html__('Curacao', 'ARForms')),
      'dial_code' => '+599',
      'code' => 'cw',
      ),
      57 => array(
      'name' => addslashes(esc_html__('Cyprus', 'ARForms')),
      'dial_code' => '+357',
      'code' => 'cy',
      ),
      58 => array(
      'name' => addslashes(esc_html__('Czech Republic', 'ARForms')),
      'dial_code' => '+420',
      'code' => 'cz',
      ),
      59 => array(
      'name' => addslashes(esc_html__('Denmark', 'ARForms')),
      'dial_code' => '+45',
      'code' => 'dk',
      ),
      60 => array(
      'name' => addslashes(esc_html__('Djibouti', 'ARForms')),
      'dial_code' => '+253',
      'code' => 'dj',
      ),
      61 => array(
      'name' => addslashes(esc_html__('Dominica', 'ARForms')),
      'dial_code' => '+1767',
      'code' => 'dm',
      ),
      62 => array(
      'name' => addslashes(esc_html__('Dominican Republic', 'ARForms')),
      'dial_code' => '+1',
      'code' => 'do',
      ),
      63 => array(
      'name' => addslashes(esc_html__('Ecuador', 'ARForms')),
      'dial_code' => '+593',
      'code' => 'ec',
      ),
      64 => array(
      'name' => addslashes(esc_html__('Egypt', 'ARForms')),
      'dial_code' => '+20',
      'code' => 'eg',
      ),
      65 => array(
      'name' => addslashes(esc_html__('El Salvador', 'ARForms')),
      'dial_code' => '+503',
      'code' => 'sv',
      ),
      66 => array(
      'name' => addslashes(esc_html__('Equatorial Guinea', 'ARForms')),
      'dial_code' => '+240',
      'code' => 'gq',
      ),
      67 => array(
      'name' => addslashes(esc_html__('Eritrea', 'ARForms')),
      'dial_code' => '+291',
      'code' => 'er',
      ),
      68 => array(
      'name' => addslashes(esc_html__('Estonia', 'ARForms')),
      'dial_code' => '+372',
      'code' => 'ee',
      ),
      69 => array(
      'name' => addslashes(esc_html__('Ethiopia', 'ARForms')),
      'dial_code' => '+251',
      'code' => 'et',
      ),
      70 => array(
      'name' => addslashes(esc_html__('Falkland Islands', 'ARForms')),
      'dial_code' => '+500',
      'code' => 'fk',
      ),
      71 => array(
      'name' => addslashes(esc_html__('Faroe Islands', 'ARForms')),
      'dial_code' => '+298',
      'code' => 'fo',
      ),
      72 => array(
      'name' => addslashes(esc_html__('Fiji', 'ARForms')),
      'dial_code' => '+679',
      'code' => 'fj',
      ),
      73 => array(
      'name' => addslashes(esc_html__('Finland', 'ARForms')),
      'dial_code' => '+358',
      'code' => 'fi',
      ),
      74 => array(
      'name' => addslashes(esc_html__('France', 'ARForms')),
      'dial_code' => '+33',
      'code' => 'fr',
      ),
      75 => array(
      'name' => addslashes(esc_html__('French Guiana', 'ARForms')),
      'dial_code' => '+594',
      'code' => 'gf',
      ),
      76 => array(
      'name' => addslashes(esc_html__('French Polynesia', 'ARForms')),
      'dial_code' => '+689',
      'code' => 'pf',
      ),
      77 => array(
      'name' => addslashes(esc_html__('Gabon', 'ARForms')),
      'dial_code' => '+241',
      'code' => 'ga',
      ),
      78 => array(
      'name' => addslashes(esc_html__('Gambia', 'ARForms')),
      'dial_code' => '+220',
      'code' => 'gm',
      ),
      79 => array(
      'name' => addslashes(esc_html__('Georgia', 'ARForms')),
      'dial_code' => '+995',
      'code' => 'ge',
      ),
      80 => array(
      'name' => addslashes(esc_html__('Germany', 'ARForms')),
      'dial_code' => '+49',
      'code' => 'de',
      ),
      81 => array(
      'name' => addslashes(esc_html__('Ghana', 'ARForms')),
      'dial_code' => '+233',
      'code' => 'gh',
      ),
      82 => array(
      'name' => addslashes(esc_html__('Gibraltar', 'ARForms')),
      'dial_code' => '+350',
      'code' => 'gi',
      ),
      83 => array(
      'name' => addslashes(esc_html__('Greece', 'ARForms')),
      'dial_code' => '+30',
      'code' => 'gr',
      ),
      84 => array(
      'name' => addslashes(esc_html__('Greenland', 'ARForms')),
      'dial_code' => '+299',
      'code' => 'gl',
      ),
      85 => array(
      'name' => addslashes(esc_html__('Grenada', 'ARForms')),
      'dial_code' => '+1473',
      'code' => 'gd',
      ),
      86 => array(
      'name' => addslashes(esc_html__('Guadeloupe', 'ARForms')),
      'dial_code' => '+590',
      'code' => 'gp',
      ),
      87 => array(
      'name' => addslashes(esc_html__('Guam', 'ARForms')),
      'dial_code' => '+1671',
      'code' => 'gu',
      ),
      88 => array(
      'name' => addslashes(esc_html__('Guatemala', 'ARForms')),
      'dial_code' => '+502',
      'code' => 'gt',
      ),
      89 => array(
      'name' => addslashes(esc_html__('Guernsey', 'ARForms')),
      'dial_code' => '+44',
      'code' => 'gg',
      ),
      90 => array(
      'name' => addslashes(esc_html__('Guinea', 'ARForms')),
      'dial_code' => '+224',
      'code' => 'gn',
      ),
      91 => array(
      'name' => addslashes(esc_html__('Guinea-Bissau', 'ARForms')),
      'dial_code' => '+245',
      'code' => 'gw',
      ),
      92 => array(
      'name' => addslashes(esc_html__('Guyana', 'ARForms')),
      'dial_code' => '+592',
      'code' => 'gy',
      ),
      93 => array(
      'name' => addslashes(esc_html__('Haiti', 'ARForms')),
      'dial_code' => '+509',
      'code' => 'ht',
      ),
      94 => array(
      'name' => addslashes(esc_html__('Honduras', 'ARForms')),
      'dial_code' => '+504',
      'code' => 'hn',
      ),
      95 => array(
      'name' => addslashes(esc_html__('Hong Kong', 'ARForms')),
      'dial_code' => '+852',
      'code' => 'hk',
      ),
      96 => array(
      'name' => addslashes(esc_html__('Hungary', 'ARForms')),
      'dial_code' => '+36',
      'code' => 'hu',
      ),
      97 => array(
      'name' => addslashes(esc_html__('Iceland', 'ARForms')),
      'dial_code' => '+354',
      'code' => 'is',
      ),
      98 => array(
      'name' => addslashes(esc_html__('India', 'ARForms')),
      'dial_code' => '+91',
      'code' => 'in',
      ),
      99 => array(
      'name' => addslashes(esc_html__('Indonesia', 'ARForms')),
      'dial_code' => '+62',
      'code' => 'id',
      ),
      100 => array(
      'name' => addslashes(esc_html__('Iran', 'ARForms')),
      'dial_code' => '+98',
      'code' => 'ir',
      ),
      101 => array(
      'name' => addslashes(esc_html__('Iraq', 'ARForms')),
      'dial_code' => '+964',
      'code' => 'iq',
      ),
      102 => array(
      'name' => addslashes(esc_html__('Ireland', 'ARForms')),
      'dial_code' => '+353',
      'code' => 'ie',
      ),
      103 => array(
      'name' => addslashes(esc_html__('Isle of Man', 'ARForms')),
      'dial_code' => '+44',
      'code' => 'im',
      ),
      104 => array(
      'name' => addslashes(esc_html__('Israel', 'ARForms')),
      'dial_code' => '+972',
      'code' => 'il',
      ),
      105 => array(
      'name' => addslashes(esc_html__('Italy', 'ARForms')),
      'dial_code' => '+39',
      'code' => 'it',
      ),
      106 => array(
      'name' => addslashes(esc_html__('Jamaica', 'ARForms')),
      'dial_code' => '+1',
      'code' => 'jm',
      ),
      107 => array(
      'name' => addslashes(esc_html__('Japan', 'ARForms')),
      'dial_code' => '+81',
      'code' => 'jp',
      ),
      108 => array(
      'name' => addslashes(esc_html__('Jersey', 'ARForms')),
      'dial_code' => '+44',
      'code' => 'je',
      ),
      109 => array(
      'name' => addslashes(esc_html__('Jordan', 'ARForms')),
      'dial_code' => '+962',
      'code' => 'jo',
      ),
      110 => array(
      'name' => addslashes(esc_html__('Kazakhstan', 'ARForms')),
      'dial_code' => '+7',
      'code' => 'kz',
      ),
      111 => array(
      'name' => addslashes(esc_html__('Kenya', 'ARForms')),
      'dial_code' => '+254',
      'code' => 'ke',
      ),
      112 => array(
      'name' => addslashes(esc_html__('Kiribati', 'ARForms')),
      'dial_code' => '+686',
      'code' => 'ki',
      ),
      113 => array(
      'name' => addslashes(esc_html__('Kosovo', 'ARForms')),
      'dial_code' => '+383',
      'code' => 'xk',
      ),
      114 => array(
      'name' => addslashes(esc_html__('Kuwait', 'ARForms')),
      'dial_code' => '+965',
      'code' => 'kw',
      ),
      115 => array(
      'name' => addslashes(esc_html__('Kyrgyzstan', 'ARForms')),
      'dial_code' => '+996',
      'code' => 'kg',
      ),
      116 => array(
      'name' => addslashes(esc_html__('Laos', 'ARForms')),
      'dial_code' => '+856',
      'code' => 'la',
      ),
      117 => array(
      'name' => addslashes(esc_html__('Latvia', 'ARForms')),
      'dial_code' => '+371',
      'code' => 'lv',
      ),
      118 => array(
      'name' => addslashes(esc_html__('Lebanon', 'ARForms')),
      'dial_code' => '+961',
      'code' => 'lb',
      ),
      119 => array(
      'name' => addslashes(esc_html__('Lesotho', 'ARForms')),
      'dial_code' => '+266',
      'code' => 'ls',
      ),
      120 => array(
      'name' => addslashes(esc_html__('Liberia', 'ARForms')),
      'dial_code' => '+231',
      'code' => 'lr',
      ),
      121 => array(
      'name' => addslashes(esc_html__('Libya', 'ARForms')),
      'dial_code' => '+218',
      'code' => 'ly',
      ),
      122 => array(
      'name' => addslashes(esc_html__('Liechtenstein', 'ARForms')),
      'dial_code' => '+423',
      'code' => 'li',
      ),
      123 => array(
      'name' => addslashes(esc_html__('Lithuania', 'ARForms')),
      'dial_code' => '+370',
      'code' => 'lt',
      ),
      124 => array(
      'name' => addslashes(esc_html__('Luxembourg', 'ARForms')),
      'dial_code' => '+352',
      'code' => 'lu',
      ),
      125 => array(
      'name' => addslashes(esc_html__('Macau', 'ARForms')),
      'dial_code' => '+853',
      'code' => 'mo',
      ),
      126 => array(
      'name' => addslashes(esc_html__('Macedonia', 'ARForms')),
      'dial_code' => '+389',
      'code' => 'mk',
      ),
      127 => array(
      'name' => addslashes(esc_html__('Madagascar', 'ARForms')),
      'dial_code' => '+261',
      'code' => 'mg',
      ),
      128 => array(
      'name' => addslashes(esc_html__('Malawi', 'ARForms')),
      'dial_code' => '+265',
      'code' => 'mw',
      ),
      129 => array(
      'name' => addslashes(esc_html__('Malaysia', 'ARForms')),
      'dial_code' => '+60',
      'code' => 'my',
      ),
      130 => array(
      'name' => addslashes(esc_html__('Maldives', 'ARForms')),
      'dial_code' => '+960',
      'code' => 'mv',
      ),
      131 => array(
      'name' => addslashes(esc_html__('Mali', 'ARForms')),
      'dial_code' => '+223',
      'code' => 'ml',
      ),
      132 => array(
      'name' => addslashes(esc_html__('Malta', 'ARForms')),
      'dial_code' => '+356',
      'code' => 'mt',
      ),
      133 => array(
      'name' => addslashes(esc_html__('Marshall Islands', 'ARForms')),
      'dial_code' => '+692',
      'code' => 'mh',
      ),
      134 => array(
      'name' => addslashes(esc_html__('Martinique', 'ARForms')),
      'dial_code' => '+596',
      'code' => 'mq',
      ),
      135 => array(
      'name' => addslashes(esc_html__('Mauritania', 'ARForms')),
      'dial_code' => '+222',
      'code' => 'mr',
      ),
      136 => array(
      'name' => addslashes(esc_html__('Mauritius', 'ARForms')),
      'dial_code' => '+230',
      'code' => 'mu',
      ),
      137 => array(
      'name' => addslashes(esc_html__('Mayotte', 'ARForms')),
      'dial_code' => '+262',
      'code' => 'yt',
      ),
      138 => array(
      'name' => addslashes(esc_html__('Mexico', 'ARForms')),
      'dial_code' => '+52',
      'code' => 'mx',
      ),
      139 => array(
      'name' => addslashes(esc_html__('Micronesia', 'ARForms')),
      'dial_code' => '+691',
      'code' => 'fm',
      ),
      140 => array(
      'name' => addslashes(esc_html__('Moldova', 'ARForms')),
      'dial_code' => '+373',
      'code' => 'md',
      ),
      141 => array(
      'name' => addslashes(esc_html__('Monaco', 'ARForms')),
      'dial_code' => '+377',
      'code' => 'mc',
      ),
      142 => array(
      'name' => addslashes(esc_html__('Mongolia', 'ARForms')),
      'dial_code' => '+976',
      'code' => 'mn',
      ),
      143 => array(
      'name' => addslashes(esc_html__('Montenegro', 'ARForms')),
      'dial_code' => '+382',
      'code' => 'me',
      ),
      144 => array(
      'name' => addslashes(esc_html__('Montserrat', 'ARForms')),
      'dial_code' => '+1664',
      'code' => 'ms',
      ),
      145 => array(
      'name' => addslashes(esc_html__('Morocco', 'ARForms')),
      'dial_code' => '+212',
      'code' => 'ma',
      ),
      146 => array(
      'name' => addslashes(esc_html__('Mozambique', 'ARForms')),
      'dial_code' => '+258',
      'code' => 'mz',
      ),
      147 => array(
      'name' => addslashes(esc_html__('Myanmar', 'ARForms')),
      'dial_code' => '+95',
      'code' => 'mm',
      ),
      148 => array(
      'name' => addslashes(esc_html__('Namibia', 'ARForms')),
      'dial_code' => '+264',
      'code' => 'na',
      ),
      149 => array(
      'name' => addslashes(esc_html__('Nauru', 'ARForms')),
      'dial_code' => '+674',
      'code' => 'nr',
      ),
      150 => array(
      'name' => addslashes(esc_html__('Nepal', 'ARForms')),
      'dial_code' => '+977',
      'code' => 'np',
      ),
      151 => array(
      'name' => addslashes(esc_html__('Netherlands', 'ARForms')),
      'dial_code' => '+31',
      'code' => 'nl',
      ),
      152 => array(
      'name' => addslashes(esc_html__('New Caledonia', 'ARForms')),
      'dial_code' => '+687',
      'code' => 'nc',
      ),
      153 => array(
      'name' => addslashes(esc_html__('New Zealand', 'ARForms')),
      'dial_code' => '+64',
      'code' => 'nz',
      ),
      154 => array(
      'name' => addslashes(esc_html__('Nicaragua', 'ARForms')),
      'dial_code' => '+505',
      'code' => 'ni',
      ),
      155 => array(
      'name' => addslashes(esc_html__('Niger', 'ARForms')),
      'dial_code' => '+227',
      'code' => 'ne',
      ),
      156 => array(
      'name' => addslashes(esc_html__('Nigeria', 'ARForms')),
      'dial_code' => '+234',
      'code' => 'ng',
      ),
      157 => array(
      'name' => addslashes(esc_html__('Niue', 'ARForms')),
      'dial_code' => '+683',
      'code' => 'nu',
      ),
      158 => array(
      'name' => addslashes(esc_html__('Norfolk Island', 'ARForms')),
      'dial_code' => '+672',
      'code' => 'nf',
      ),
      159 => array(
      'name' => addslashes(esc_html__('North Korea', 'ARForms')),
      'dial_code' => '+850',
      'code' => 'kp',
      ),
      160 => array(
      'name' => addslashes(esc_html__('Northern Mariana Islands', 'ARForms')),
      'dial_code' => '+1670',
      'code' => 'mp',
      ),
      161 => array(
      'name' => addslashes(esc_html__('Norway', 'ARForms')),
      'dial_code' => '+47',
      'code' => 'no',
      ),
      162 => array(
      'name' => addslashes(esc_html__('Oman', 'ARForms')),
      'dial_code' => '+968',
      'code' => 'om',
      ),
      163 => array(
      'name' => addslashes(esc_html__('Pakistan', 'ARForms')),
      'dial_code' => '+92',
      'code' => 'pk',
      ),
      164 => array(
      'name' => addslashes(esc_html__('Palau', 'ARForms')),
      'dial_code' => '+680',
      'code' => 'pw',
      ),
      165 => array(
      'name' => addslashes(esc_html__('Palestine', 'ARForms')),
      'dial_code' => '+970',
      'code' => 'ps',
      ),
      166 => array(
      'name' => addslashes(esc_html__('Panama', 'ARForms')),
      'dial_code' => '+507',
      'code' => 'pa',
      ),
      167 => array(
      'name' => addslashes(esc_html__('Papua New Guinea', 'ARForms')),
      'dial_code' => '+675',
      'code' => 'pg',
      ),
      168 => array(
      'name' => addslashes(esc_html__('Paraguay', 'ARForms')),
      'dial_code' => '+595',
      'code' => 'py',
      ),
      169 => array(
      'name' => addslashes(esc_html__('Peru', 'ARForms')),
      'dial_code' => '+51',
      'code' => 'pe',
      ),
      170 => array(
      'name' => addslashes(esc_html__('Philippines', 'ARForms')),
      'dial_code' => '+63',
      'code' => 'ph',
      ),
      171 => array(
      'name' => addslashes(esc_html__('Poland', 'ARForms')),
      'dial_code' => '+48',
      'code' => 'pl',
      ),
      172 => array(
      'name' => addslashes(esc_html__('Portugal', 'ARForms')),
      'dial_code' => '+351',
      'code' => 'pt',
      ),
      173 => array(
      'name' => addslashes(esc_html__('Puerto Rico', 'ARForms')),
      'dial_code' => '+1',
      'code' => 'pr',
      ),
      174 => array(
      'name' => addslashes(esc_html__('Qatar', 'ARForms')),
      'dial_code' => '+974',
      'code' => 'qa',
      ),
      175 => array(
      'name' => addslashes(esc_html__('Reunion', 'ARForms')),
      'dial_code' => '+262',
      'code' => 're',
      ),
      176 => array(
      'name' => addslashes(esc_html__('Romania', 'ARForms')),
      'dial_code' => '+40',
      'code' => 'ro',
      ),
      177 => array(
      'name' => addslashes(esc_html__('Russia', 'ARForms')),
      'dial_code' => '+7',
      'code' => 'ru',
      ),
      178 => array(
      'name' => addslashes(esc_html__('Rwanda', 'ARForms')),
      'dial_code' => '+250',
      'code' => 'rw',
      ),
      179 => array(
      'name' => addslashes(esc_html__('Saint Barthelemy', 'ARForms')),
      'dial_code' => '+590',
      'code' => 'bl',
      ),
      180 => array(
      'name' => addslashes(esc_html__('Saint Helena', 'ARForms')),
      'dial_code' => '+290',
      'code' => 'sh',
      ),
      181 => array(
      'name' => addslashes(esc_html__('Saint Kitts and Nevis', 'ARForms')),
      'dial_code' => '+1869',
      'code' => 'kn',
      ),
      182 => array(
      'name' => addslashes(esc_html__('Saint Lucia', 'ARForms')),
      'dial_code' => '+1758',
      'code' => 'lc',
      ),
      183 => array(
      'name' => addslashes(esc_html__('Saint Martin', 'ARForms')),
      'dial_code' => '+590',
      'code' => 'mf',
      ),
      184 => array(
      'name' => addslashes(esc_html__('Saint Pierre and Miquelon', 'ARForms')),
      'dial_code' => '+508',
      'code' => 'pm',
      ),
      185 => array(
      'name' => addslashes(esc_html__('Saint Vincent and the Grenadines', 'ARForms')),
      'dial_code' => '+1784',
      'code' => 'vc',
      ),
      186 => array(
      'name' => addslashes(esc_html__('Samoa', 'ARForms')),
      'dial_code' => '+685',
      'code' => 'ws',
      ),
      187 => array(
      'name' => addslashes(esc_html__('San Marino', 'ARForms')),
      'dial_code' => '+378',
      'code' => 'sm',
      ),
      188 => array(
      'name' => addslashes(esc_html__('Sao Tome and Principe', 'ARForms')),
      'dial_code' => '+239',
      'code' => 'st',
      ),
      189 => array(
      'name' => addslashes(esc_html__('Saudi Arabia', 'ARForms')),
      'dial_code' => '+966',
      'code' => 'sa',
      ),
      190 => array(
      'name' => addslashes(esc_html__('Senegal', 'ARForms')),
      'dial_code' => '+221',
      'code' => 'sn',
      ),
      191 => array(
      'name' => addslashes(esc_html__('Serbia', 'ARForms')),
      'dial_code' => '+381',
      'code' => 'rs',
      ),
      192 => array(
      'name' => addslashes(esc_html__('Seychelles', 'ARForms')),
      'dial_code' => '+248',
      'code' => 'sc',
      ),
      193 => array(
      'name' => addslashes(esc_html__('Sierra Leone', 'ARForms')),
      'dial_code' => '+232',
      'code' => 'sl',
      ),
      194 => array(
      'name' => addslashes(esc_html__('Singapore', 'ARForms')),
      'dial_code' => '+65',
      'code' => 'sg',
      ),
      195 => array(
      'name' => addslashes(esc_html__('Sint Maarten', 'ARForms')),
      'dial_code' => '+1721',
      'code' => 'sx',
      ),
      196 => array(
      'name' => addslashes(esc_html__('Slovakia', 'ARForms')),
      'dial_code' => '+421',
      'code' => 'sk',
      ),
      197 => array(
      'name' => addslashes(esc_html__('Slovenia', 'ARForms')),
      'dial_code' => '+386',
      'code' => 'si',
      ),
      198 => array(
      'name' => addslashes(esc_html__('Solomon Islands', 'ARForms')),
      'dial_code' => '+677',
      'code' => 'sb',
      ),
      199 => array(
      'name' => addslashes(esc_html__('Somalia', 'ARForms')),
      'dial_code' => '+252',
      'code' => 'so',
      ),
      200 => array(
      'name' => addslashes(esc_html__('South Africa', 'ARForms')),
      'dial_code' => '+27',
      'code' => 'za',
      ),
      201 => array(
      'name' => addslashes(esc_html__('South Korea', 'ARForms')),
      'dial_code' => '+82',
      'code' => 'kr',
      ),
      202 => array(
      'name' => addslashes(esc_html__('South Sudan', 'ARForms')),
      'dial_code' => '+211',
      'code' => 'ss',
      ),
      203 => array(
      'name' => addslashes(esc_html__('Spain', 'ARForms')),
      'dial_code' => '+34',
      'code' => 'es',
      ),
      204 => array(
      'name' => addslashes(esc_html__('Sri Lanka', 'ARForms')),
      'dial_code' => '+94',
      'code' => 'lk',
      ),
      205 => array(
      'name' => addslashes(esc_html__('Sudan', 'ARForms')),
      'dial_code' => '+249',
      'code' => 'sd',
      ),
      206 => array(
      'name' => addslashes(esc_html__('Suriname', 'ARForms')),
      'dial_code' => '+597',
      'code' => 'sr',
      ),
      207 => array(
      'name' => addslashes(esc_html__('Svalbard and Jan Mayen', 'ARForms')),
      'dial_code' => '+47',
      'code' => 'sj',
      ),
      208 => array(
      'name' => addslashes(esc_html__('Swaziland', 'ARForms')),
      'dial_code' => '+268',
      'code' => 'sz',
      ),
      209 => array(
      'name' => addslashes(esc_html__('Sweden', 'ARForms')),
      'dial_code' => '+46',
      'code' => 'se',
      ),
      210 => array(
      'name' => addslashes(esc_html__('Switzerland', 'ARForms')),
      'dial_code' => '+41',
      'code' => 'ch',
      ),
      211 => array(
      'name' => addslashes(esc_html__('Syria', 'ARForms')),
      'dial_code' => '+963',
      'code' => 'sy',
      ),
      212 => array(
      'name' => addslashes(esc_html__('Taiwan', 'ARForms')),
      'dial_code' => '+886',
      'code' => 'tw',
      ),
      213 => array(
      'name' => addslashes(esc_html__('Tajikistan', 'ARForms')),
      'dial_code' => '+992',
      'code' => 'tj',
      ),
      214 => array(
      'name' => addslashes(esc_html__('Tanzania', 'ARForms')),
      'dial_code' => '+255',
      'code' => 'tz',
      ),
      215 => array(
      'name' => addslashes(esc_html__('Thailand', 'ARForms')),
      'dial_code' => '+66',
      'code' => 'th',
      ),
      216 => array(
      'name' => addslashes(esc_html__('Timor-Leste', 'ARForms')),
      'dial_code' => '+670',
      'code' => 'tl',
      ),
      217 => array(
      'name' => addslashes(esc_html__('Togo', 'ARForms')),
      'dial_code' => '+228',
      'code' => 'tg',
      ),
      218 => array(
      'name' => addslashes(esc_html__('Tokelau', 'ARForms')),
      'dial_code' => '+690',
      'code' => 'tk',
      ),
      219 => array(
      'name' => addslashes(esc_html__('Tonga', 'ARForms')),
      'dial_code' => '+676',
      'code' => 'to',
      ),
      220 => array(
      'name' => addslashes(esc_html__('Trinidad and Tobago', 'ARForms')),
      'dial_code' => '+1868',
      'code' => 'tt',
      ),
      221 => array(
      'name' => addslashes(esc_html__('Tunisia', 'ARForms')),
      'dial_code' => '+216',
      'code' => 'tn',
      ),
      222 => array(
      'name' => addslashes(esc_html__('Turkey', 'ARForms')),
      'dial_code' => '+90',
      'code' => 'tr',
      ),
      223 => array(
      'name' => addslashes(esc_html__('Turkmenistan', 'ARForms')),
      'dial_code' => '+993',
      'code' => 'tm',
      ),
      224 => array(
      'name' => addslashes(esc_html__('Turks and Caicos Islands', 'ARForms')),
      'dial_code' => '+1649',
      'code' => 'tc',
      ),
      225 => array(
      'name' => addslashes(esc_html__('Tuvalu', 'ARForms')),
      'dial_code' => '+688',
      'code' => 'tv',
      ),
      226 => array(
      'name' => addslashes(esc_html__('U.S. Virgin Islands', 'ARForms')),
      'dial_code' => '+1340',
      'code' => 'vi',
      ),
      227 => array(
      'name' => addslashes(esc_html__('Uganda', 'ARForms')),
      'dial_code' => '+256',
      'code' => 'ug',
      ),
      228 => array(
      'name' => addslashes(esc_html__('Ukraine', 'ARForms')),
      'dial_code' => '+380',
      'code' => 'ua',
      ),
      229 => array(
      'name' => addslashes(esc_html__('United Arab Emirates', 'ARForms')),
      'dial_code' => '+971',
      'code' => 'ae',
      ),
      230 => array(
      'name' => addslashes(esc_html__('United Kingdom', 'ARForms')),
      'dial_code' => '+44',
      'code' => 'gb',
      ),
      231 => array(
      'name' => addslashes(esc_html__('United States', 'ARForms')),
      'dial_code' => '+1',
      'code' => 'us',
      ),
      232 => array(
      'name' => addslashes(esc_html__('Uruguay', 'ARForms')),
      'dial_code' => '+598',
      'code' => 'uy',
      ),
      233 => array(
      'name' => addslashes(esc_html__('Uzbekistan', 'ARForms')),
      'dial_code' => '+998',
      'code' => 'uz',
      ),
      234 => array(
      'name' => addslashes(esc_html__('Vanuatu', 'ARForms')),
      'dial_code' => '+678',
      'code' => 'vu',
      ),
      235 => array(
      'name' => addslashes(esc_html__('Vatican City', 'ARForms')),
      'dial_code' => '+39',
      'code' => 'va',
      ),
      236 => array(
      'name' => addslashes(esc_html__('Venezuela', 'ARForms')),
      'dial_code' => '+58',
      'code' => 've',
      ),
      237 => array(
      'name' => addslashes(esc_html__('Vietnam', 'ARForms')),
      'dial_code' => '+84',
      'code' => 'vn',
      ),
      238 => array(
      'name' => addslashes(esc_html__('Wallis and Futuna', 'ARForms')),
      'dial_code' => '+681',
      'code' => 'wf',
      ),
      239 => array(
      'name' => addslashes(esc_html__('Western Sahara', 'ARForms')),
      'dial_code' => '+212',
      'code' => 'eh',
      ),
      240 => array(
      'name' => addslashes(esc_html__('Yemen', 'ARForms')),
      'dial_code' => '+967',
      'code' => 'ye',
      ),
      241 => array(
      'name' => addslashes(esc_html__('Zambia', 'ARForms')),
      'dial_code' => '+260',
      'code' => 'zm',
      ),
      242 => array(
      'name' => addslashes(esc_html__('Zimbabwe', 'ARForms')),
      'dial_code' => '+263',
      'code' => 'zw',
      ),
  );
    return $country_code;
}

function arf_sanitize_value($value, $type='text', $allow_html=false){

  $allowed_html_arr = array(
      'a' => array('title'=>array(), 'href'=>array(), 'target'=>array(), 'class'=>array(), 'id'=>array(), 'style'=>array()),
      'arftotal' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
      'b' => array(),
      'blockquote' => array(),
      'br' => array(),
      'button' => array('class'=>array(), 'id'=>array(), 'style'=>array(), 'title'=>array()),
      'canvas' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
      'center' => array(),
      'code' => array(),
      'dd' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
      'del' => array('datetime' => array(), 'title' => array()),
      'div' => array('class'=>array(), 'id'=>array(), 'style'=>array(), 'title'=>array()),
      'dl' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
      'dt' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
      'em' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
      'embed' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
      'font' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
      'frame' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
      'frameset' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
      'h1' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
      'h2' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
      'h3' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
      'h4' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
      'h5' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
      'hr' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
      'i' => array(),
      'iframe' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
      'img' => array('class'=>array(), 'id'=>array(), 'style'=>array(), 'src'=>array(), 'alt'=>array(), 'height'=>array(), 'width'=>array()),
      'label' => array('class'=>array(), 'id'=>array(), 'style'=>array(), 'for'=>array()),
      'li' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
      'link' => array('href'=>array(), 'type'=>array()),
      'meta' => array(),
      'object' => array(),
      'ol' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
      'p' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
      'pre' => array(),
      'q' => array('cite' => array(), 'title' => array()),
      'span' => array('class'=>array(), 'id'=>array(), 'style'=>array(), 'title'=>array()),
      'script' => array('src'=>array(), 'type'=>array()),
      'strike' => array(),
      'sub' => array(),
      'sup' => array(),
      'svg' => array(),
      'strong' => array(),
      'tfooter' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
      'tbody' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
      'thead' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
      'th' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
      'td' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
      'tr' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
      'table' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
      'u' => array(),
      'ul' => array('class'=>array(), 'id'=>array(), 'style'=>array()),
  );

  if($allow_html==true){
    $value = wp_kses( $value, $allowed_html_arr );
  } else if($type == 'text'){
    $value = sanitize_text_field($value);
  } else if($type=='integer' || $type=='number' ){
    $value = intval($value);
  } else if($type=='textarea' ){
    $value = sanitize_textarea_field($value);
  } else if($type == 'email' ){
    $value = sanitize_email($value);
  }
  return $value;
}

function arf_get_country_from_ip($ip_address = ''){
  if( '' == $ip_address ){
    return '';
  }

  $country_reader = new Reader(MODELS_PATH.'/geoip/inc/GeoLite2-Country.mmdb');
  $country_name = "";
  try{
      $record = $country_reader->country($ip_address);
      $country_name = $record->country->name;
  } catch(Exception $e){
      $country_name = "";
  }
  return $country_name;
}