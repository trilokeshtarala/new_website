<?php

global $armainhelper, $arformhelper, $arfversion, $wpdb, $arfform;
$default_options = array();

$forms = $arfform->getAll("is_template=0 AND (status is NULL OR status = '' OR status = 'published')", ' ORDER BY name');


$default_options['arf_forms'] = '';
$default_options['arf_forms_include_type'] = 'internal';
$default_options['arf_link_type'] = 'onclick';
$default_options['arf_onclick_type'] = 'link';
$default_options['arf_link_caption'] = addslashes(esc_html__('Click here to open Form', 'ARForms'));
$default_options['arf_background_overlay'] = '0.6';
$default_options['arf_fly_position'] = 'left';
$default_options['arf_background_overlay_color'] = '#000000';
$default_options['arf_button_background_color'] = '#808080';
$default_options['arf_link_position'] = 'top';
$default_options['arf_button_text_color'] = '#ffffff';
$default_options['arf_show_close_button'] = true;
$default_options['arf_popup_height'] = 'auto';
$default_options['arf_popup_width'] = '800';
$default_options['arf_fly_button_angle'] = '0';
$default_options['arf_onload_time'] = '0';
$default_options['arf_scroll_per'] = '10';

$default_options['arf_inact_time'] = '0';
$default_options['arf_show_full_screen'] = 'no';
$default_options['arf_modal_effect'] = 'no_animation';

$default_options['class'] = '';
$default_options['style'] = '';
$default_options['heading'] = '';
return $default_options;