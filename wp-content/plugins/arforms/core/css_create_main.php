<?php
if (!isset($saving))
    header("Content-type: text/css");

$form_id = isset($form_id) ? $form_id : '';

if (isset($use_saved) and $use_saved) {

    foreach ($new_values as $k => $v) {

        if (( preg_match('/color/', $k) or in_array($k, array('arferrorbgsetting', 'arferrorbordersetting', 'arferrortextsetting')) ) && !in_array($k, array('arfcheckradiocolor'))) {
            if(strpos($v,'#') === false) {
                $new_values[$k] = '#' . $v;
            } else {
                $new_values[$k] = $v;
            }
        } else {
            $new_values[$k] = $v;
        }
    }
    extract((array) $new_values);

    global $arsettingcontroller,$arformcontroller;

    $form_border_shadow_color = isset($new_values['arfmainformbordershadowcolorsetting']) ? $new_values['arfmainformbordershadowcolorsetting'] : '';

    $standard_error_position = isset($new_values['arfstandarderrposition']) ? $new_values['arfstandarderrposition'] : 'relative';

    $field_text_color_pg_break = isset($new_values['text_color_pg_break']) ? $new_values['text_color_pg_break'] : '';

    $field_bg_color_pg_break = isset($new_values['bg_color_pg_break']) ? $new_values['bg_color_pg_break'] : '';

    $field_bg_inactive_color_pg_break = isset($new_values['bg_inavtive_color_pg_break']) ? $new_values['bg_inavtive_color_pg_break']  : '';

    $checkbox_radio_style_val = isset($new_values['checkbox_radio_style']) ? $new_values['checkbox_radio_style'] : '';

    $form_bg_color = isset($new_values['arfmainformbgcolorsetting']) ? $new_values['arfmainformbgcolorsetting'] : '';

    $form_opacity = isset($new_values['arfmainform_opacity']) ? $new_values['arfmainform_opacity'] : '';

    $placeholder_opacity = isset($new_values['arfplaceholder_opacity']) ? $new_values['arfplaceholder_opacity'] : '';

    $fieldset_color = isset($new_values['arfmainfieldsetcolor']) ? $new_values['arfmainfieldsetcolor'] : '';

    $bg_color_active = isset($new_values['arfbgactivecolorsetting']) ? $new_values['arfbgactivecolorsetting'] : '';

    $border_color_active = isset($new_values['arfborderactivecolorsetting']) ? $new_values['arfborderactivecolorsetting'] : '';

    $submit_bg_img = isset($new_values['submit_bg_img']) ? $new_values['submit_bg_img'] : '';

    $submit_hover_bg_img = isset($new_values['submit_hover_bg_img']) ? $new_values['submit_hover_bg_img'] : '';

    $submit_border_color = isset($new_values['arfsubmitbordercolorsetting']) ? $new_values['arfsubmitbordercolorsetting'] : '';

    $arfsubmitbuttonstyle = isset($new_values['arfsubmitbuttonstyle']) ? $new_values['arfsubmitbuttonstyle'] : 'border';

    $submit_text_color = isset($new_values['arfsubmittextcolorsetting']) ? $new_values['arfsubmittextcolorsetting'] : '';

    $submit_weight = isset($new_values['arfsubmitweightsetting']) ? $new_values['arfsubmitweightsetting'] : '';

    $submit_shadow_color = isset($new_values['arfsubmitshadowcolorsetting']) ? str_replace("##", '#',$new_values['arfsubmitshadowcolorsetting']) : '';

    $submit_bg_color_hover = isset($new_values['arfsubmitbuttonbgcolorhoversetting']) ? str_replace("##", '#', $new_values['arfsubmitbuttonbgcolorhoversetting']) :'';

    $bg_color_error = isset($new_values['arferrorbgcolorsetting']) ? $new_values['arferrorbgcolorsetting']  : '';

    $border_color_error = isset($new_values['arferrorbordercolorsetting']) ? $new_values['arferrorbordercolorsetting'] : '';

    $field_border_style = isset($new_values['arffieldborderstylesetting']) ? $new_values['arffieldborderstylesetting'] : '';

    $border_style_error = isset($new_values['arfbordererrorstylesetting']) ? $new_values['arfbordererrorstylesetting'] : '';

    $success_border_color = isset($new_values['arfsucessbordercolorsetting']) ? $new_values['arfsucessbordercolorsetting'] : '';

    $success_bg_color = isset($new_values['arfsucessbgcolorsetting'] ) ? $new_values['arfsucessbgcolorsetting'] : '';

    $success_text_color = isset($new_values['arfsucesstextcolorsetting']) ? $new_values['arfsucesstextcolorsetting'] : '';

    $error_bg_color = isset($new_values['arfformerrorbgcolorsettings']) ? $new_values['arfformerrorbgcolorsettings'] : '';

    $error_border_color = isset($new_values['arfformerrorbordercolorsettings']) ? $new_values['arfformerrorbordercolorsettings'] : "";

    $error_txt_color = isset($new_values['arfformerrortextcolorsettings']) ? $new_values['arfformerrortextcolorsettings'] : "";

    $error_bg = isset($new_values['arferrorbgsetting']) ? $new_values['arferrorbgsetting'] : '';

    $error_border = isset($new_values['arferrorbordersetting']) ? $new_values['arferrorbordersetting'] : '';

    $error_text = isset($new_values['arferrortextsetting']) ? $new_values['arferrortextsetting'] : '';

    $form_title_padding = isset($new_values['arfmainformtitlepaddingsetting']) ? $new_values['arfmainformtitlepaddingsetting'] : '';

    $description_font = isset($new_values['check_font']) ? $new_values['check_font'] : '';

    $description_font_size = isset($new_values['arfdescfontsizesetting']) ? $new_values['arfdescfontsizesetting'] : '';

    $description_color = isset($new_values['label_color'] ) ? $new_values['label_color'] : '';

    $description_align = isset($new_values['arfdescalighsetting']) ? $new_values['arfdescalighsetting'] : '';

    $border_radius = ($new_values['border_radius'] == '') ? '0px' : $new_values['border_radius'] . 'px';

    $error_font_size = isset($new_values['arffontsizesetting']) ? $new_values['arffontsizesetting'] . 'px' : '';

    $form_title_color = isset($new_values['arfmainformtitlecolorsetting']) ? $new_values['arfmainformtitlecolorsetting'] : '';

    $form_width = isset($new_values['arfmainformwidth']) ? $new_values['arfmainformwidth'] : '';

    $check_align = isset($new_values['arfcheckboxalignsetting']) ? $new_values['arfcheckboxalignsetting'] : '';

    $radio_align = isset($new_values['arfradioalignsetting']) ? $new_values['arfradioalignsetting'] : '';

    $field_font_size_without_px = isset($new_values['field_font_size']) ? $new_values['field_font_size'] : '';

    $submit_align = isset($new_values['arfsubmitalignsetting']) ? $new_values['arfsubmitalignsetting'] : '';

    $form_width = $form_width . $form_width_unit;

    $fieldset = ($fieldset == '') ? '0px' : $fieldset . 'px';

    $field_font_size = $field_font_size . 'px';

    $font_size = isset($font_size) ? $font_size . 'px'  : '';

    $fieldset_padding = ($new_values['arfmainfieldsetpadding'] == '') ? '0px' : $new_values['arfmainfieldsetpadding'];

    $fieldset_radius = ($new_values['arfmainfieldsetradius'] == '') ? '0px' : $new_values['arfmainfieldsetradius'] . 'px';

    $hide_labels = isset($hide_labels) ? $hide_labels : 0;

    $width_unit = isset($new_values['arfmwu']) ? $new_values['arfmwu'] : '';

    $width = $width . $width_unit;

    $description_font_size = $description_font_size . 'px';

    if ($field_width_unit == '%' and $field_width > 100) {
        $field_width = '100%';
    } else {
        $field_width_select = $field_width;
        $field_width = ($field_width == '') ? 'auto' : $field_width . $field_width_unit;
    }

    $field_margin = ($new_values['arffieldmarginssetting'] == '') ? '0px' : $new_values['arffieldmarginssetting'] . 'px';

    $radio_checkbox_field_margin = ($new_values['arffieldmarginssetting'] == '') ? '0px' : ($new_values['arffieldmarginssetting']-7) . 'px';

    $field_border_width = ($new_values['arffieldborderwidthsetting'] == '') ? '0px' : $new_values['arffieldborderwidthsetting'] . 'px';

    $field_border_width_select = ($new_values['arffieldborderwidthsetting'] == '') ? '0' : $new_values['arffieldborderwidthsetting'];

    $border_width_error = $field_border_width;

    $submit_style = isset($submit_style) ? $submit_style : 0;

    $submit_font_size = isset($new_values['arfsubmitbuttonfontsizesetting']) ? $new_values['arfsubmitbuttonfontsizesetting'] . 'px !important' : '';

    $submit_font_size_wpx = isset($new_values['arfsubmitbuttonfontsizesetting']) ? $new_values['arfsubmitbuttonfontsizesetting'] : '';

    $form_title_weight = isset($new_values['check_weight_form_title']) ? $new_values['check_weight_form_title'] : 'bold';

    $arfsectiontitleweightsetting = isset($new_values['arfsectiontitleweightsetting']) ? $new_values['arfsectiontitleweightsetting'] : '';

    $arfsectiontitlefamily = isset($new_values['arfsectiontitlefamily']) ? $new_values['arfsectiontitlefamily'] : 'Helvetica';

    $arfsectiontitlefontsizesetting = isset($new_values['arfsectiontitlefontsizesetting']) ? $new_values['arfsectiontitlefontsizesetting'].'px' : '16px';

    $submit_width = ($new_values['arfsubmitbuttonwidthsetting'] == '') ? '' : $new_values['arfsubmitbuttonwidthsetting'] . 'px';

    $submit_auto_width = ($new_values['arfsubmitautowidth'] == '' || $new_values['arfsubmitautowidth'] < 100 ) ? '100' : $new_values['arfsubmitautowidth'];

    $submit_width = ( $submit_width == '' ) ? $submit_auto_width . 'px' : $submit_width;

    $submit_width_wpx = ($new_values['arfsubmitbuttonwidthsetting'] == '') ? $submit_auto_width : $new_values['arfsubmitbuttonwidthsetting'];

    $submit_height_hex = ($new_values['arfsubmitbuttonheightsetting'] == '') ? '36' : $new_values['arfsubmitbuttonheightsetting'];

    $submit_height_wpx = ($new_values['arfsubmitbuttonheightsetting'] == '') ? '' : $new_values['arfsubmitbuttonheightsetting'];

    $submit_height = ($new_values['arfsubmitbuttonheightsetting'] == '') ? 'auto' : $new_values['arfsubmitbuttonheightsetting'] . 'px';

    $submit_border_width = ($new_values['arfsubmitborderwidthsetting'] == '') ? '0px' : $new_values['arfsubmitborderwidthsetting'] . 'px';

    $submit_border_radius = ($new_values['arfsubmitborderradiussetting'] == '') ? '0px' : $new_values['arfsubmitborderradiussetting'] . 'px';

    $submit_margin = ($new_values['arfsubmitbuttonmarginsetting'] == '') ? '0px' : $new_values['arfsubmitbuttonmarginsetting'];

    $validation_textcolor = isset($new_values['arfvalidationtextcolorsetting']) ? $new_values['arfvalidationtextcolorsetting'] : '';
    $validation_bgcolor = isset($new_values['arfvalidationbgcolorsetting']) ? $new_values['arfvalidationbgcolorsetting'] : '';
    $arfvalidationerrorstyle = isset($new_values['arfest']) ? $new_values['arfest'] : '';
    $submit_padding = isset($submit_padding) ? $submit_padding : '';
    $submit_padding = $submit_padding . 'px !important';

    $success_font_size = $error_font_size;

    $field_textarea_width = $field_width;

    $field_textarea_margin = $field_margin;

    $field_textarea_font_size = $field_font_size;

    $textarea_bg_color = $bg_color;

    $textarea_text_color = $text_color;

    $textarea_border_color = $border_color;

    $field_textarea_border_width = $field_border_width;

    $field_textarea_border_style = $field_border_style;

    $color_bg_active = isset($color_bg_active) ? $color_bg_active : '';

    $text_direction = ($text_direction == 0) ? 'rtl' : 'ltr';

    $form_title_font_size = $form_title_font_size . 'px';

    $submit_width_loader = ($new_values['arfsubmitbuttonwidthsetting'] == '') ? '1' : $new_values['arfsubmitbuttonwidthsetting'];

    $arffieldpaddingsetting = $field_textarea_pad = isset($new_values['arffieldinnermarginssetting']) ? $new_values['arffieldinnermarginssetting'] : 0;

    $arfsubmitfontfamily = isset($new_values['arfsubmitfontfamily']) ? $new_values['arfsubmitfontfamily'] : '';

    $arfformtitlealign = isset($new_values['arfformtitlealign']) ? $new_values['arfformtitlealign'] : '';

    $arfcheck_style_name = isset($new_values['arfcheckradiostyle']) ? $new_values['arfcheckradiostyle'] : '';

    $arfinputstyle = isset($new_values['arfinputstyle']) ? $new_values['arfinputstyle'] : '';

    $arfcheck_style_color = isset($new_values['arfcheckradiocolor']) ? $new_values['arfcheckradiocolor'] : '';

    $arf_checked_checkbox_icon = isset($new_values['arf_checked_checkbox_icon']) ? $new_values['arf_checked_checkbox_icon'] : '';

    $enable_arf_checkbox = isset($new_values['enable_arf_checkbox']) ? $new_values['enable_arf_checkbox'] : '';

    $arf_checked_radio_icon = isset($new_values['arf_checked_radio_icon']) ? $new_values['arf_checked_radio_icon'] :'';

    $enable_arf_radio = isset($new_values['enable_arf_radio']) ? $new_values['enable_arf_radio'] : '';

    $checked_checkbox_icon_color = isset($new_values['checked_checkbox_icon_color']) ? $new_values['checked_checkbox_icon_color'] : '';

    $checked_radio_icon_color = isset($new_values['checked_radio_icon_color']) ? $new_values['checked_radio_icon_color'] :'';

    $arf_bar_color_survey = isset($new_values['bar_color_survey']) ? $new_values['bar_color_survey'] : '';

    $arf_bg_color_survey = isset($new_values['bg_color_survey']) ? $new_values['bg_color_survey'] : '';

    $arf_text_color_survey = isset($new_values['text_color_survey']) ? $new_values['text_color_survey'] : '';


    $arf_title_font_family = isset($new_values['arftitlefontfamily']) ? $new_values['arftitlefontfamily'] : '';
    
    $arf_date_picker_bg_color = isset($new_values['arfdatepickerbgcolorsetting']) ? $new_values['arfdatepickerbgcolorsetting'] : '';
    $arf_date_picker_text_color = isset($new_values['arfdatepickertextcolorsetting']) ? $new_values['arfdatepickertextcolorsetting'] : '#46484d';
    
    $arferrorstylecolor = isset($new_values['arfvalidationbgcolorsetting']) ? $new_values['arfvalidationbgcolorsetting'] : '';
    
    $arferrorstylecolorfont = isset($new_values['arfvalidationtextcolorsetting']) ? $new_values['arfvalidationtextcolorsetting'] : '';
    
    $arfvalidationerrorstyle = isset($new_values['arferrorstyle']) ? $new_values['arferrorstyle'] : '';

    $upload_bgcolor = isset($new_values['arfuploadbtnbgcolorsetting']) ? $new_values['arfuploadbtnbgcolorsetting'] : '#077BDD';

    $upload_text_color = isset($new_values['arfuploadbtntxtcolorsetting']) ? $new_values['arfuploadbtntxtcolorsetting'] : '#ffffff';
    
    $arf_required_indicator = isset($new_values['arf_req_indicator'])?$new_values['arf_req_indicator']:'0';

    if ($arfvalidationerrorstyle == 'normal') {
        $arferrorstylecolor2 = explode("|", $new_values['arferrorstylecolor2']);
        $arferrorstylecolor = $arferrorstylecolorfont;
    }

    if (!preg_match('/#/', $arferrorstylecolor))
        $arferrorstylecolor = '#' . $arferrorstylecolor;

    if (!preg_match('/#/', $arferrorstylecolorfont))
        $arferrorstylecolorfont = '#' . $arferrorstylecolorfont;

    if ($field_font_size < '20') {
        $fie_field_height = '29';
        $file_field_pad = '6';
    } else if ($field_font_size >= '20' and $field_font_size < '24') {
        $fie_field_height = '45';
        $file_field_pad = '14';
    } else if ($field_font_size >= '24') {
        $field_pad = '8px 15px';
        $fie_field_height = '49';
        $file_field_pad = '16';
    }

    if ($field_border_width_select == '1') {
        $file_field_pad = $file_field_pad + 1;
    } else if ($field_border_width_select > 2 and $field_border_width_select < 5) {
        $file_field_pad = $file_field_pad - floor($field_border_width_select / 2);
    } else if ($field_border_width_select == 5 || $field_border_width_select == 6) {
        $file_field_pad = $file_field_pad - floor($field_border_width_select / 1.5);
    } else if ($field_border_width_select >= 7) {
        $file_field_pad = $file_field_pad - floor($field_border_width_select / 1);
    }

    if ($form_title_font_size <= '20')
        $form_title_margin = '0 0 25px 35px;';
    else if ($form_title_font_size > '20' and $form_title_font_size <= '28')
        $form_title_margin = '0 0 35px 35px;';
    else if ($form_title_font_size >= '30' and $form_title_font_size <= '36')
        $form_title_margin = '0 0 40px 35px;';
    else if ($form_title_font_size > '36')
        $form_title_margin = '0 0 45px 35px;';

    $prefix_suffix_bg_color = isset($new_values['prefix_suffix_bg_color']) ? str_replace('##', '#', $new_values['prefix_suffix_bg_color']) : '';
    $prefix_suffix_icon_color = isset($new_values['prefix_suffix_icon_color']) ? $new_values['prefix_suffix_icon_color'] : '';

    $section_padding = (!isset($new_values['arfsectionpaddingsetting']) || $new_values['arfsectionpaddingsetting'] == '') ? '0px' : $new_values['arfsectionpaddingsetting'];

    $arf_divider_inherit_bg = isset($new_values['arf_divider_inherit_bg']) ? $new_values['arf_divider_inherit_bg']  : 0;

    $section_background = isset($new_values['arfformsectionbackgroundcolor']) ? $new_values['arfformsectionbackgroundcolor'] : '#ffffff';

    $base_color = isset($new_values['arfmainbasecolor']) ? $new_values['arfmainbasecolor'] : '';

    $like_btn_color = isset($new_values['arflikebtncolor']) ? $new_values['arflikebtncolor'] : '';
    $dislike_btn_color = isset($new_values['arfdislikebtncolor']) ? $new_values['arfdislikebtncolor'] : '';

    $star_rating_color = isset($new_values['arfstarratingcolor']) ? $new_values['arfstarratingcolor'] : '';

    $slider_selection_color = isset($new_values['arfsliderselectioncolor']) ? $new_values['arfsliderselectioncolor'] : '';
    $slider_track_color = isset($new_values['arfslidertrackcolor']) ? $new_values['arfslidertrackcolor'] : '';

    $bg_position_x = isset($new_values['arf_bg_position_x']) ? $new_values['arf_bg_position_x'] : '';
    $bg_position_y = isset($new_values['arf_bg_position_y']) ? $new_values['arf_bg_position_y'] : '';

    $bg_position_x_input = isset($new_values['arf_bg_position_input_x']) ? $new_values['arf_bg_position_input_x'] : '';
    $bg_position_y_input = isset($new_values['arf_bg_position_input_y']) ? $new_values['arf_bg_position_input_y'] : '';
} else if (isset($_REQUEST['arfmfws'])) {

    $form_id = isset($_REQUEST['arfmf']) ? $_REQUEST['arfmf'] : '';

    $form_width_unit = isset($_REQUEST['arffu']) ? $_REQUEST['arffu'] : '';

    $field_width_unit = isset($_REQUEST['arffiu']) ? $_REQUEST['arffiu'] : '';

    $width_unit = isset($_REQUEST['arfmwu']) ? $_REQUEST['arfmwu'] : '';

    $form_width = isset($_REQUEST['arffw']) ? $_REQUEST['arffw']  . $form_width_unit : '';

    $form_align = isset($_REQUEST['arffa']) ? $_REQUEST['arffa'] : '';

    $fieldset = ($_REQUEST['arfmfis'] == '') ? '0px' : $_REQUEST['arfmfis'] . 'px';

    $fieldset_color = isset($_REQUEST['arfmfsc']) ? $_REQUEST['arfmfsc'] : '';

    $fieldset_padding = ($_REQUEST['arfmfsp'] == '') ? '0px' : $_REQUEST['arfmfsp'];

    $fieldset_radius = ($_REQUEST['arfmfsr'] == '') ? '0px' : $_REQUEST['arfmfsr'] . 'px';

    $font = isset($_REQUEST['arfmfs']) ? $_REQUEST['arfmfs'] :'';

    $font_other = isset($_REQUEST['arfofs']) ? $_REQUEST['arfofs'] : '';

    $font_size = isset($_REQUEST['arffss']) ? $_REQUEST['arffss'] . 'px' : '';

    $label_color = isset($_REQUEST['arflcs']) ? $_REQUEST['arflcs'] : '';

    $weight = isset($_REQUEST['arfmfws']) ? $_REQUEST['arfmfws'] : '';

    $position = isset($_REQUEST['arfmps']) ? $_REQUEST['arfmps'] : '';

    $hide_labels = isset($_REQUEST['arfhl']) ? $_REQUEST['arfhl'] : 0;

    $align = isset($_REQUEST['arffrma']) ? $_REQUEST['arffrma'] : '';

    $width = isset($_REQUEST['arfmws'] ) ? $_REQUEST['arfmws'] . $width_unit : '';

    $description_font = isset($_REQUEST['arfcbfs']) ? $_REQUEST['arfcbfs'] : '';

    $description_font_size = isset($_REQUEST['arfdfss']) ? $_REQUEST['arfdfss'] . 'px' : '';

    $description_color = isset($_REQUEST['arflcs']) ? $_REQUEST['arflcs'] : '';

    $description_style = isset($_REQUEST['arfdss']) ? $_REQUEST['arfdss'] : '';

    $description_align = isset($_REQUEST['arfdas']) ? $_REQUEST['arfdas'] : '';

    $field_font_size_without_px = isset($_REQUEST['arfffss']) ? $_REQUEST['arfffss'] : '';

    $field_font_size = isset($_REQUEST['arfffss']) ? $_REQUEST['arfffss'] . 'px' : '';

    $field_width_unit = isset($_REQUEST['arffiu']) ? $_REQUEST['arffiu'] :'';

    if ($_REQUEST['arffiu'] == '%' and $_REQUEST['arfmfiws'] > 100)
        $field_width = '100%';
    else {
        $field_width_select = $_REQUEST['arfmfiws'];
        $field_width = ($_REQUEST['arfmfiws'] == '') ? 'auto' : $_REQUEST['arfmfiws'] . $_REQUEST['arffiu'];
    }

    $field_margin = (!isset($_REQUEST['arffms']) || $_REQUEST['arffms'] == '') ? '0px' : $_REQUEST['arffms'] . 'px';

    $radio_checkbox_field_margin = (!isset($_REQUEST['arffms']) || $_REQUEST['arffms'] == '') ? '0px' : ($_REQUEST['arffms']-7) . 'px';

    $text_color = isset($_REQUEST['arftcs']) ? $_REQUEST['arftcs'] : '';

    $bg_color = isset($_REQUEST['arffmbc']) ? $_REQUEST['arffmbc'] : '';

    $border_color = isset($_REQUEST['arffmboc']) ? $_REQUEST['arffmboc'] : '';

    $field_border_width = ($_REQUEST['arffbws'] == '') ? '0px' : $_REQUEST['arffbws'] . 'px';

    $field_border_width_select = ($_REQUEST['arffbws'] == '') ? '0' : $_REQUEST['arffbws'];

    $field_border_style = isset($_REQUEST['arffbss']) ? $_REQUEST['arffbss'] : '';

    $bg_color_active = isset($_REQUEST['arfbcas']) ? $_REQUEST['arfbcas'] : '';

    $border_color_active = isset($_REQUEST['arfbacs']) ? $_REQUEST['arfbacs'] : '';

    $bg_color_error = isset($_REQUEST['arfbecs']) ? $_REQUEST['arfbecs'] : '';

    $border_color_error = isset($_REQUEST['arfboecs']) ? $_REQUEST['arfboecs'] : '';

    $border_width_error = $field_border_width;

    $border_style_error = isset($_REQUEST['arfbess']) ? $_REQUEST['arfbess'] : '';

    $radio_align = isset($_REQUEST['arfras']) ? $_REQUEST['arfras'] : '';

    $check_align = isset($_REQUEST['arfcbas']) ? $_REQUEST['arfcbas'] : '';

    $check_font = isset($_REQUEST['arfcbfs']) ? $_REQUEST['arfcbfs'] : '';

    $check_font_other = isset($_REQUEST['arffcfo']) ? $_REQUEST['arffcfo'] : '';

    $check_font_size = isset($_REQUEST['arfffss'] ) ? $_REQUEST['arfffss'] : '';

    $check_weight = isset($_REQUEST['arfcbws']) ? $_REQUEST['arfcbws'] : '';

    $submit_style = isset($_REQUEST['arfsbs']) ? $_REQUEST['arfsbs'] : 0;

    $submit_font_size = isset($_REQUEST['arfsbfss']) ? ($_REQUEST['arfsbfss']) . 'px !important' : '';

    $submit_font_size_wpx = isset($_REQUEST['arfsbfss']) ? $_REQUEST['arfsbfss'] : '';

    $submit_width = ($_REQUEST['arfsbws'] == '') ? '' : $_REQUEST['arfsbws'] . 'px';

    $submit_auto_width = ($_REQUEST['arfsbaw'] == '' || $_REQUEST['arfsbaw'] < 100 ) ? '100' : $_REQUEST['arfsbaw'];

    $submit_width_wpx = ($_REQUEST['arfsbws'] == '') ? $submit_auto_width : $_REQUEST['arfsbws'];

    $submit_height_hex = ($_REQUEST['arfsbhs'] == '') ? '36' : $_REQUEST['arfsbhs'];

    $submit_height_wpx = ($_REQUEST['arfsbhs'] == '') ? '' : $_REQUEST['arfsbhs'];

    $submit_height = ($_REQUEST['arfsbhs'] == '') ? 'auto' : $_REQUEST['arfsbhs'] . 'px';

    $submit_bg_color = isset($_REQUEST['arfsbbcs']) ? str_replace('##','#',$_REQUEST['arfsbbcs']) : '';

    $submit_bg_color_hover = isset($_REQUEST['arfsbchs']) ? $_REQUEST['arfsbchs'] : '';

    $submit_bg_color2 = isset($_REQUEST['arfsbcs']) ? $_REQUEST['arfsbcs'] : '';

    $submit_bg_img = isset($_REQUEST['arfsbis']) ? $_REQUEST['arfsbis'] : '';

    $submit_hover_bg_img = isset($_REQUEST['arfsbhis']) ? $_REQUEST['arfsbhis'] : '';

    $submit_border_color = isset($_REQUEST['arfsbobcs']) ? $_REQUEST['arfsbobcs'] : '';

    $submit_border_width = ($_REQUEST['arfsbbws'] == '') ? '0px' : $_REQUEST['arfsbbws'] . 'px';

    $submit_text_color = isset($_REQUEST['arfsbtcs']) ? $_REQUEST['arfsbtcs'] : '';

    $submit_weight = isset($_REQUEST['arfsbwes']) ? $_REQUEST['arfsbwes'] : '';

    $arfsubmitbuttonstyle = isset($_REQUEST['arfsubmitbuttonstyle']) ? $_REQUEST['arfsubmitbuttonstyle'] : 'border';
    
    $submit_border_radius = ($_REQUEST['arfsbbrs'] == '') ? '0px' : $_REQUEST['arfsbbrs'] . 'px';

    $submit_margin = ($_REQUEST['arfsbms'] == '') ? '0px' : $_REQUEST['arfsbms'];

    $submit_shadow_color = isset($_REQUEST['arfsbscs']) ? str_replace('##', '#', $_REQUEST['arfsbscs']) : '';

    $border_radius = ($_REQUEST['arfmbs'] == '') ? '0px' : $_REQUEST['arfmbs'] . 'px';

    $error_bg = isset($_REQUEST['arfmebs']) ? $_REQUEST['arfmebs'] : '';

    $error_border = isset($_REQUEST['arfmebos']) ? $_REQUEST['arfmebos'] : '';

    $error_text = isset($_REQUEST['arfmets']) ? $_REQUEST['arfmets'] : '';

    $error_font_size = isset($_REQUEST['arfmefss']) ? $_REQUEST['arfmefss'] . 'px' : '';

    $success_bg_color = isset($_REQUEST['arfmsbcs']) ? $_REQUEST['arfmsbcs'] : '';

    $success_border_color = isset($_REQUEST['arfmsbocs']) ? $_REQUEST['arfmsbocs'] : '';

    $success_text_color = isset($_REQUEST['arfmstcs']) ? $_REQUEST['arfmstcs'] : '';

    $error_bg_color = isset($_REQUEST['arffebgc']) ? $_REQUEST['arffebgc'] : '';

    $error_border_color = isset($_REQUEST['arffebrdc']) ? $_REQUEST['arffebrdc'] : '';

    $error_txt_color = isset($_REQUEST['arffetxtc']) ? $_REQUEST['arffetxtc'] : '';

    $success_font_size = $error_font_size;

    $field_textarea_font_size = isset($_REQUEST['arfffss']) ? $_REQUEST['arfffss'] : '';

    $field_textarea_width = $field_width;

    $field_textarea_margin = $field_margin;

    $textarea_bg_color = $bg_color;

    $textarea_text_color = $text_color;

    $textarea_border_color = $border_color;

    $field_textarea_border_width = $field_border_width;

    $field_textarea_border_style = $field_border_style;

    $field_height = (!isset($_REQUEST['arfmfhs'])) ? 'auto' : $_REQUEST['arfmfhs'] . 'px';

    if( preg_match("/auto/",$field_height) ){
        $field_height = str_replace('px', '', $field_height);
    }

    $text_direction = ($_REQUEST['arftds'] == 0) ? 'rtl' : 'ltr';

    $error_font = isset($_REQUEST['arfmefs']) ? $_REQUEST['arfmefs'] : '';

    $error_font_other = isset($_REQUEST['arfmofs']) ? $_REQUEST['arfmofs'] : '';

    $form_title_color = isset($_REQUEST['arfftc']) ? $_REQUEST['arfftc'] : '';

    $form_title_font_size = $_REQUEST['arfftfss'] . 'px';

    $form_bg_color = isset($_REQUEST['arffbcs']) ? $_REQUEST['arffbcs'] : '';

    $form_opacity = isset($_REQUEST['arfmainform_opacity']) ? $_REQUEST['arfmainform_opacity'] : '';

    $placeholder_opacity = isset($_REQUEST['arfplaceholder_opacity']) ? $_REQUEST['arfplaceholder_opacity'] : '';

    $form_title_weight = isset($_REQUEST['arfftws']) ? $_REQUEST['arfftws'] : '';

    $arfsectiontitleweightsetting = isset($_REQUEST['arfsectiontitleweightsetting']) ? $_REQUEST['arfsectiontitleweightsetting'] : '';

    $arfsectiontitlefamily = isset($_REQUEST['arfsectiontitlefamily']) ? $_REQUEST['arfsectiontitlefamily'] : 'Helvetica';

    $arfsectiontitlefontsizesetting = isset($_REQUEST['arfsectiontitlefontsizesetting']) ? $_REQUEST['arfsectiontitlefontsizesetting'].'px' : '16px';

    $form_title_padding = isset($_REQUEST['arfftps'] ) ? $_REQUEST['arfftps'] : '';

    $form_border_shadow = isset($_REQUEST['arffbs']) ? $_REQUEST['arffbs'] : '';

    $submit_width_loader = ($_REQUEST['arfsbws'] == '') ? '1' : $_REQUEST['arfsbws'];

    $form_border_shadow_color = isset($_REQUEST['arffboss']) ? $_REQUEST['arffboss'] : '';

    $standard_error_position = isset($_REQUEST['arfstndrerr']) ? $_REQUEST['arfstndrerr'] : 'relative';

    $arf_title_font_family = isset($_REQUEST['arftff']) ? $_REQUEST['arftff'] : '';

    $section_padding = ($_REQUEST['arfscps'] == '') ? '0px' : $_REQUEST['arfscps'];
    
    $arf_date_picker_bg_color = isset($_REQUEST['arfdbcs']) ? $_REQUEST['arfdbcs'] : '';
    
    $arf_date_picker_text_color = isset($_REQUEST['arfdtcs']) ? $_REQUEST['arfdtcs'] : '';
    
    $arferrorstylecolor = $validation_bgcolor = isset($_REQUEST['arfmvbcs']) ? $_REQUEST['arfmvbcs'] : '';
    $arferrorstylecolorfont = $validation_textcolor = isset($_REQUEST['arfmvtcs']) ? $_REQUEST['arfmvtcs'] : '';
    
    $arfvalidationerrorstyle = isset($_REQUEST['arfest']) ? $_REQUEST['arfest'] : '';

    if ($arfvalidationerrorstyle == 'normal') {
        $arferrorstylecolor2 = explode("|", $_REQUEST['arfestc2']);
        $arferrorstylecolor = $arferrorstylecolorfont;
    }

    $submit_align = $_REQUEST['arfmsas'];

    $arfmainform_bg_img = $_REQUEST['arfmfbi'];
    $arfmainfield_opacity = (isset($_REQUEST['arfmfo']) && $_REQUEST['arfmfo'] != '') ? $_REQUEST['arfmfo'] : 0;

    $arffieldpaddingsetting = $field_textarea_pad = $_REQUEST['arffims'];

    if ($_REQUEST['arfffss'] < '20') {
        $fie_field_height = '29';
        $file_field_pad = '6';
    }

    if ($field_border_width_select == '1') {
        $file_field_pad = $file_field_pad + 1;
    } else if ($field_border_width_select > 2 and $field_border_width_select < 5) {
        $file_field_pad = $file_field_pad - floor($field_border_width_select / 2);
    } else if ($field_border_width_select == 5 || $field_border_width_select == 6) {
        $file_field_pad = $file_field_pad - floor($field_border_width_select / 1.5);
    } else if ($field_border_width_select >= 7) {
        $file_field_pad = $file_field_pad - floor($field_border_width_select / 1);
    }

    if ($_REQUEST['arfftfss'] <= '20')
        $form_title_margin = '0 0 25px 35px;';
    else if ($_REQUEST['arfftfss'] > '20' and $_REQUEST['arfftfss'] <= '28')
        $form_title_margin = '0 0 35px 35px;';
    else if ($_REQUEST['arfftfss'] >= '30' and $_REQUEST['arfftfss'] <= '36')
        $form_title_margin = '0 0 40px 35px;';
    else if ($_REQUEST['arfftfss'] > '36')
        $form_title_margin = '0 0 45px 35px;';
    $_REQUEST['arfcrs'] = isset($_REQUEST['arfcrs']) ? $_REQUEST['arfcrs'] : '';
    $checkbox_radio_style_val = ($_REQUEST['arfcrs'] == '') ? '1' : $_REQUEST['arfcrs'];

    $field_bg_color_pg_break = isset($_REQUEST['arffbcpb']) ? $_REQUEST['arffbcpb'] : '';

    $field_bg_inactive_color_pg_break = isset($_REQUEST['arfbicpb']) ? $_REQUEST['arfbicpb'] : '';

    $field_text_color_pg_break = isset($_REQUEST['arfftcpb']) ? $_REQUEST['arfftcpb'] : '';

    $arf_bar_color_survey = isset($_REQUEST['arfbcs']) ? $_REQUEST['arfbcs'] : '';

    $arf_bg_color_survey = isset($_REQUEST['arfbgcs']) ? $_REQUEST['arfbgcs'] : '';

    $arf_text_color_survey = isset($_REQUEST['arfftcs']) ? $_REQUEST['arfftcs'] : '';

    $arfsubmitfontfamily = isset($_REQUEST['arfsff']) ? $_REQUEST['arfsff'] : '';

    $arfformtitlealign = isset($_REQUEST['arffta']) ? $_REQUEST['arffta'] : '';

    $arfcheck_style_name = isset($_REQUEST['arfcksn']) ? $_REQUEST['arfcksn'] : '';

    $arfinputstyle = isset($_REQUEST['arfinpst']) ? $_REQUEST['arfinpst'] : '';

    $arfcheck_style_color = isset($_REQUEST['arfcksc']) ? $_REQUEST['arfcksc'] : '';

    $arf_checked_checkbox_icon = isset($_REQUEST['arf_checkbox_icon']) ? $_REQUEST['arf_checkbox_icon'] : '';

    $enable_arf_checkbox = isset($_REQUEST['enable_arf_checkbox'] ) ? $_REQUEST['enable_arf_checkbox'] : '';

    $arf_checked_radio_icon = isset($_REQUEST['arf_radio_icon']) ? $_REQUEST['arf_radio_icon'] : '';

    $enable_arf_radio = isset($_REQUEST['enable_arf_radio']) ? $_REQUEST['enable_arf_radio'] : '';

    $checked_checkbox_icon_color = isset($_REQUEST['cbscol']) ? $_REQUEST['cbscol'] : '';

    $checked_radio_icon_color = isset($_REQUEST['rbscol']) ? $_REQUEST['rbscol'] : '';

    $prefix_suffix_bg_color = isset($_REQUEST['pfsfsbg']) ? $_REQUEST['pfsfsbg'] : '';

    $prefix_suffix_icon_color = isset($_REQUEST['pfsfscol']) ? $_REQUEST['pfsfscol'] : '';

    $upload_bgcolor = isset($_REQUEST['arfupbg']) ? $_REQUEST['arfupbg'] : '#077BDD';
    
    $upload_text_color = isset($_REQUEST['arfuptxt']) ? $_REQUEST['arfuptxt'] : '#ffffff';

    $arf_required_indicator = isset($_REQUEST['arfrinc'])?$_REQUEST['arfrinc']:'0';

    $arf_divider_inherit_bg = isset($_REQUEST['arf_divider_inherit_bg']) ? $_REQUEST['arf_divider_inherit_bg']  : 0;

    $section_background = isset($_REQUEST['arfsecbg']) ? $_REQUEST['arfsecbg'] : '#ffffff';

    $base_color = isset($_REQUEST['arfmbsc']) ? $_REQUEST['arfmbsc'] : '';

    $like_btn_color = isset($_REQUEST['albclr']) ? $_REQUEST['albclr'] : '';

    $dislike_btn_color = isset($_REQUEST['adlbclr']) ? $_REQUEST['adlbclr'] : '';

    $star_rating_color = isset($_REQUEST['asclcl']) ? $_REQUEST['asclcl'] : '';

    $slider_selection_color = isset($_REQUEST['asldrsl']) ? $_REQUEST['asldrsl'] : '';
    $slider_track_color = isset($_REQUEST['asltrcl']) ? $_REQUEST['asltrcl'] : '';

    $bg_position_x = isset($_REQUEST['arf_bg_position_x']) ? $_REQUEST['arf_bg_position_x'] : '';
    $bg_position_y = isset($_REQUEST['arf_bg_position_y']) ? $_REQUEST['arf_bg_position_y'] : '';

    $bg_position_x_input = isset($_REQUEST['arf_bg_position_input_x']) ? $_REQUEST['arf_bg_position_input_x'] : '';
    $bg_position_y_input = isset($_REQUEST['arf_bg_position_input_y']) ? $_REQUEST['arf_bg_position_input_y'] : '';
}


if (isset($field_font_size_without_px) and $field_font_size_without_px < '20') {
    $file_upload_padding = '10';
    $file_upload_hw = '14px';
    $file_upload_bg = 'upload-icon.png';

    if (isset($field_font_size_without_px) and $field_font_size_without_px <= 13)
        $file_upload_margin_top = '0px';
    else
        $file_upload_margin_top = '3px';
} else if (isset($field_font_size_without_px) and $field_font_size_without_px >= '20' and $field_font_size_without_px < '26') {
    $file_upload_padding = '13';
    $file_upload_hw = '14px';

    if ($field_font_size_without_px > 22)
        $file_upload_margin_top = '9px';
    else
        $file_upload_margin_top = '7px';

    $file_upload_bg = 'upload-icon.png';
} else if (isset($field_font_size_without_px) and  $field_font_size_without_px >= '26' and $field_font_size_without_px < '33') {
    $file_upload_padding = '15';
    $file_upload_hw = '25px';
    $file_upload_margin_top = '5px';
    $file_upload_bg = 'upload-icon_25x25.png';
} else if (isset($field_font_size_without_px) and $field_font_size_without_px > '33') {
    $file_upload_hw = '32px';
    $file_upload_padding = '17';
    $file_upload_margin_top = '7px';
    $file_upload_bg = 'upload-icon_32x32.png';
} else {
    $file_upload_bg = 'upload-icon_32x32.png';
}
$lwidth = isset($width) ? (int) $width : 0;
$label_margin = $lwidth + 15;
$dweight = isset($weight) ? $weight : 0;
$weight_arr = explode(',', $dweight);
$weight = (in_array('bold', $weight_arr)) ? 'bold' : 'normal';
$weight_font_style = in_array('italic', $weight_arr) ? ' font-style:italic;' : ' font-style:normal; ';
$weight_font_style_decoration = ' text-decoration:none; ';
if (in_array('underline', $weight_arr)) {
    $weight_font_style_decoration = ' text-decoration:underline;';
}
if (in_array('strikethrough', $weight_arr)) {
    $weight_font_style_decoration = ' text-decoration:line-through;';
}
$weight_font_style.= $weight_font_style_decoration;
$dcheck_weight = isset($check_weight) ? $check_weight : '';
$check_weight_arr = explode(',', $dcheck_weight);
$check_weight = (in_array('bold', $check_weight_arr)) ? 'bold' : 'normal';
$check_weight_font_style = in_array('italic', $check_weight_arr) ? ' font-style:italic; ' : ' font-style:normal; ';
$check_weight_font_style_decoration = ' text-decoration:none; ';
if (in_array('underline', $check_weight_arr)) {
    $check_weight_font_style_decoration = ' text-decoration:underline;';
}
if (in_array('strikethrough', $check_weight_arr)) {
    $check_weight_font_style_decoration = ' text-decoration:line-through;';
}
$check_weight_font_style.= $check_weight_font_style_decoration;
$dsubmit_weight = isset($submit_weight) ? $submit_weight : '';
$submit_weight_arr = explode(',', $dsubmit_weight);
$submit_weight = (in_array('bold', $submit_weight_arr)) ? 'bold' : 'normal';
$submit_weight_font_style = in_array('italic', $submit_weight_arr) ? ' font-style:italic; ' : ' font-style:normal;';
$submit_weight_font_style_decoration = ' text-decoration:none; ';
if (in_array('underline', $submit_weight_arr)) {
    $submit_weight_font_style_decoration = ' text-decoration:underline;';
}
if (in_array('strikethrough', $submit_weight_arr)) {
    $submit_weight_font_style_decoration = ' text-decoration:line-through;';
}
$submit_weight_font_style.= $submit_weight_font_style_decoration;
$dform_title_weight = isset($form_title_weight) ? $form_title_weight : '';
$form_title_weight_arr = explode(',', $dform_title_weight);
$form_title_weight = (in_array('bold', $form_title_weight_arr)) ? 'bold' : 'normal';
$form_title_weight_font_style = (in_array('italic', $form_title_weight_arr)) ? ' font-style:italic;' : ' font-style:normal; ';
$form_title_weight_font_style_decoration = ' text-decoration:none; ';
if (in_array('underline', $form_title_weight_arr)) {
    $form_title_weight_font_style_decoration = ' text-decoration:underline;';
}
if (in_array('strikethrough', $form_title_weight_arr)) {
    $form_title_weight_font_style_decoration = ' text-decoration:line-through;';
}
$form_title_weight_font_style.= $form_title_weight_font_style_decoration;

if (isset($font) && $font == "Other") {
    $newfont = $font_other;
} else {
    $newfont = isset($font) ? $font :'';
}


if (isset($check_font) && $check_font == "Other") {
    $newfontother = $check_font_other;
} else {
    $newfontother = isset($check_font) ? $check_font : '';
}

if (isset($error_font) && $error_font == "Other") {
    $newerror_font = $error_font_other;
} else {
    $newerror_font = isset($error_font) ? $error_font : '';
}

$character_set = isset($arfsettings->arf_css_character_set) && !empty( $arfsettings->arf_css_character_set ) ? (array)$arfsettings->arf_css_character_set : array();
//$subset = (isset($arfsettings->arf_css_character_set) && !empty($arfsettings->arf_css_character_set)) ? "&subset=" . implode(',', (array)$arfsettings->arf_css_character_set) : '';
$subset = count($character_set) > 0 ? "&subset=". implode(',',$character_set) : '';

if ($newfont != "Arial" && $newfont != "Helvetica" && $newfont != "sans-serif" && $newfont != "Lucida Grande" && $newfont != "Lucida Sans Unicode" && $newfont != "Tahoma" && $newfont != "Times New Roman" && $newfont != "Courier New" && $newfont != "Verdana" && $newfont != "Geneva" && $newfont != "Courier" && $newfont != "Monospace" && $newfont != "Times" && $newfont != "") {
    if (is_ssl() or $arfssl == 1)
        $googlefontbaseurl = "https://fonts.googleapis.com/css?family=";
    else
        $googlefontbaseurl = "http://fonts.googleapis.com/css?family=";
    echo "@import url(" . $googlefontbaseurl . urlencode($newfont) . $subset . ");";
}

if ($arfsectiontitlefamily != "Arial" && $arfsectiontitlefamily != "Helvetica" && $arfsectiontitlefamily != "sans-serif" && $arfsectiontitlefamily != "Lucida Grande" && $arfsectiontitlefamily != "Lucida Sans Unicode" && $arfsectiontitlefamily != "Tahoma" && $arfsectiontitlefamily != "Times New Roman" && $arfsectiontitlefamily != "Courier New" && $arfsectiontitlefamily != "Verdana" && $arfsectiontitlefamily != "Geneva" && $arfsectiontitlefamily != "Courier" && $arfsectiontitlefamily != "Monospace" && $arfsectiontitlefamily != "Times" && $arfsectiontitlefamily != "") {
    if (is_ssl() or $arfssl == 1)
        $googlefontbaseurl = "https://fonts.googleapis.com/css?family=";
    else
        $googlefontbaseurl = "http://fonts.googleapis.com/css?family=";
    echo "@import url(" . $googlefontbaseurl . urlencode($arfsectiontitlefamily) . $subset . ");";
}

if ($newfontother != "Arial" && $newfontother != "Helvetica" && $newfontother != "sans-serif" && $newfontother != "Lucida Grande" && $newfontother != "Lucida Sans Unicode" && $newfontother != "Tahoma" && $newfontother != "Times New Roman" && $newfontother != "Courier New" && $newfontother != "Verdana" && $newfontother != "Geneva" && $newfontother != "Courier" && $newfontother != "Monospace" && $newfontother != "Times" && $newfontother != "") {
    if (is_ssl() or $arfssl == 1)
        $googlefontbaseurl = "https://fonts.googleapis.com/css?family=";
    else
        $googlefontbaseurl = "http://fonts.googleapis.com/css?family=";
    echo "@import url(" . $googlefontbaseurl . urlencode($newfontother) . $subset . ");";
}

if ($newerror_font != "Arial" && $newerror_font != "Helvetica" && $newerror_font != "sans-serif" && $newerror_font != "Lucida Grande" && $newerror_font != "Lucida Sans Unicode" && $newerror_font != "Tahoma" && $newerror_font != "Times New Roman" && $newerror_font != "Courier New" && $newerror_font != "Verdana" && $newerror_font != "Geneva" && $newerror_font != "Courier" && $newerror_font != "Monospace" && $newerror_font != "Times" && $newerror_font != "") {
    if (is_ssl() or $arfssl == 1)
        $googlefontbaseurl = "https://fonts.googleapis.com/css?family=";
    else
        $googlefontbaseurl = "http://fonts.googleapis.com/css?family=";
    echo "@import url(" . $googlefontbaseurl . urlencode($newerror_font) . $subset . ");";
}

if (isset($arfsubmitfontfamily) && $arfsubmitfontfamily != "Arial" && $arfsubmitfontfamily != "Helvetica" && $arfsubmitfontfamily != "sans-serif" && $arfsubmitfontfamily != "Lucida Grande" && $arfsubmitfontfamily != "Lucida Sans Unicode" && $arfsubmitfontfamily != "Tahoma" && $arfsubmitfontfamily != "Times New Roman" && $arfsubmitfontfamily != "Courier New" && $arfsubmitfontfamily != "Verdana" && $arfsubmitfontfamily != "Geneva" && $arfsubmitfontfamily != "Courier" && $arfsubmitfontfamily != "Monospace" && $arfsubmitfontfamily != "Times" && $arfsubmitfontfamily != "") {
    if (is_ssl() or $arfssl == 1)
        $googlefontbaseurl = "https://fonts.googleapis.com/css?family=";
    else
        $googlefontbaseurl = "http://fonts.googleapis.com/css?family=";
    echo "@import url(" . $googlefontbaseurl . urlencode($arfsubmitfontfamily) . $subset . ");";
}


if (isset($arf_title_font_family) && $arf_title_font_family != "Arial" && $arf_title_font_family != "Helvetica" && $arf_title_font_family != "sans-serif" && $arf_title_font_family != "Lucida Grande" && $arf_title_font_family != "Lucida Sans Unicode" && $arf_title_font_family != "Tahoma" && $arf_title_font_family != "Times New Roman" && $arf_title_font_family != "Courier New" && $arf_title_font_family != "Verdana" && $arf_title_font_family != "Geneva" && $arf_title_font_family != "Courier" && $arf_title_font_family != "Monospace" && $arf_title_font_family != "Times" && $arf_title_font_family != "") {
    if (is_ssl() or $arfssl == 1)
        $googlefontbaseurl = "https://fonts.googleapis.com/css?family=";
    else
        $googlefontbaseurl = "http://fonts.googleapis.com/css?family=";
    echo "@import url(" . $googlefontbaseurl . urlencode($arf_title_font_family) . $subset . ");";
}
?>
html{overflow-x:hidden !important;}
.arf_form.ar_main_div_<?php echo isset($form_id) ? $form_id : ''; ?> {max-width:<?php echo isset($form_width) ? $form_width : ''; ?>;margin:0 auto;}
.arf_form.ar_main_div_<?php echo $form_id;?> .arf_wizard_upper_tab .page_break_nav{
    font-family:<?php echo stripslashes($arf_title_font_family) ?>;
}
.ar_main_div_<?php echo isset($form_id) ? $form_id : ''; ?>, .ar_main_div_<?php echo isset($form_id) ? $form_id : ''; ?> form{text-align:<?php echo isset($form_align) ? $form_align : ''; ?>; }

.ar_main_div_<?php echo isset($form_id) ? $form_id : ''; ?> .arf_confirmation_summary_label_full_width,
.ar_main_div_<?php echo isset($form_id) ? $form_id : ''; ?> .arf_heading_div h2.arf_sec_heading_field,
.ar_main_div_<?php echo isset($form_id) ? $form_id : ''; ?> .arf_fieldset .edit_field_type_divider .arfeditorfieldopt_divider_label{
    font-family: <?php echo stripslashes($arfsectiontitlefamily);?>;
    font-size: <?php echo stripslashes($arfsectiontitlefontsizesetting);?> !important;
    <?php 
        $arf_heading_font_style = '';
        $arf_section_title_font_style_arr = isset($arfsectiontitleweightsetting) ? explode(',', $arfsectiontitleweightsetting) : array();                
            if (in_array('italic', $arf_section_title_font_style_arr)) {
                $arf_heading_font_style .= 'font-style:italic; ';
            } 
            if (in_array('bold', $arf_section_title_font_style_arr)) {
                $arf_heading_font_style .= ' font-weight:bold;';
            } 
            if (in_array('underline', $arf_section_title_font_style_arr)) {
                $arf_heading_font_style .= ' text-decoration:underline;';
            } else if (in_array('strikethrough', $arf_section_title_font_style_arr)) {                    
                $arf_heading_font_style .= ' text-decoration:line-through !important;';
            }
    echo $arf_heading_font_style;
    ?>
}
.ar_main_div_<?php echo isset($form_id) ? $form_id : ''; ?> .arf_confirmation_summary_wrapper,
.ar_main_div_<?php echo isset($form_id) ? $form_id : ''; ?> .arf_fieldset{ 
    <?php if (isset($arfmainform_bg_img) && $arfmainform_bg_img != "") { ?>
        background: rgba(<?php echo $arsettingcontroller->hex2rgb(isset($form_bg_color) && $form_bg_color != '' ? $form_bg_color : '0,0,0'); ?>, <?php echo isset($form_opacity) ? $form_opacity : ''; ?> ) url(<?php echo $arfmainform_bg_img; ?>);
    <?php } else { ?>
        background:rgba(<?php echo $arsettingcontroller->hex2rgb(isset($form_bg_color)  ? $form_bg_color :''); ?>, <?php echo isset($form_opacity) ? $form_opacity : ''; ?> );
    <?php } ?>
    border:<?php echo isset($fieldset) ? $fieldset : ''; ?> solid <?php echo isset($fieldset_color) ? $fieldset_color : ''; ?>;
    margin:0;
    padding:<?php echo isset($fieldset_padding) ? $fieldset_padding : 0; ?>;
    -moz-border-radius:<?php echo isset($fieldset_radius) ? $fieldset_radius : ''; ?>;
    -webkit-border-radius:<?php echo isset($fieldset_radius) ? $fieldset_radius : ''; ?>;
    -o-border-radius:<?php echo isset($fieldset_radius) ? $fieldset_radius : ''; ?>;
    border-radius:<?php echo isset($fieldset_radius) ? $fieldset_radius : ''; ?>;

    <?php if (isset($form_border_shadow) && $form_border_shadow == 'shadow') { ?>
        -moz-box-shadow:0px 0px 7px 2px <?php echo $form_border_shadow_color; ?>;
        -o-box-shadow:0px 0px 7px 2px <?php echo $form_border_shadow_color; ?>;
        -webkit-box-shadow:0px 0px 7px 2px <?php echo $form_border_shadow_color; ?>;
        box-shadow:0px 0px 7px 2px <?php echo $form_border_shadow_color; ?>;
    <?php } else { ?>
        -moz-box-shadow:none;
        -webkit-box-shadow:none;
        -o-box-shadow:none;
        box-shadow:none;
    <?php } ?>
    <?php 
        if(isset($bg_position_x) || $bg_position_x != "" || isset($bg_position_y) || $bg_position_y != ""){
            if( $bg_position_x == "px" ){
                echo 'background-position-x:'.$bg_position_x_input.'px;';
            }else{
                echo 'background-position-x:'.$bg_position_x.';';
            }

            if( $bg_position_y == "px" ){
                echo 'background-position-y:'.$bg_position_y_input.'px;';
            }else{
                echo 'background-position-y:'.$bg_position_y.';';
            }
        }
    ?>
    
    background-repeat: no-repeat;
}

#popup-form-<?php echo $form_id; ?>.arfmodal-fullscreen{
    background:rgba(<?php echo $arsettingcontroller->hex2rgb(isset($form_bg_color)  ? $form_bg_color :''); ?>, <?php echo isset($form_opacity) ? $form_opacity : ''; ?> );
}

#popup-form-<?php echo $form_id; ?> .arf_hide_form_after_submit .arf_form_outer_wrapper{
    background-color: <?php echo $form_bg_color;?> !important;
}

.ar_main_div_<?php echo $form_id; ?> .arf_inner_wrapper_sortable.arfmainformfield.ui-sortable-helper {
    <?php 
        $fieldset_padding_left = '0px';
        if(!empty($fieldset_padding)) {
        $fieldset_padding_left_exp = explode(' ', trim($fieldset_padding));
        if(count($fieldset_padding_left_exp)>1)
        {
            $fieldset_padding_left = $fieldset_padding_left_exp[3];
        }
    }
    ?>
    left:<?php echo $fieldset_padding_left;?> !important;
}

.ar_main_div_<?php echo isset($form_id) ? $form_id : ''; ?> label.arf_main_label{
    font-family:<?php echo stripslashes($newfont) ?>;
    font-size:<?php echo isset($font_size) ? $font_size :''; ?> !important;
    line-height:150%;
    color:<?php echo isset($label_color) ? $label_color : ''; ?>;
    font-weight:<?php echo $weight ?> !important;
    <?php echo $weight_font_style; ?>
    text-align:<?php echo isset($align) ? $align : ''; ?>;
    margin:0;
    padding:0;
    width:<?php echo $lwidth; ?>px;
    display:block;
    text-transform:none;
}

.ar_main_div_<?php echo isset($form_id) ? $form_id : ''; ?> .intl-tel-input .country-list{
    font-family:<?php echo stripslashes($description_font); ?>;
    font-size:<?php echo $field_textarea_font_size; ?>;
    font-weight:<?php echo $check_weight; ?>;
    <?php echo $check_weight_font_style; ?>

}

.ar_main_div_<?php echo isset($form_id) ? $form_id : ''; ?> .arf_confirmation_summary_label{
    color:<?php echo isset($label_color) ? $label_color : ''; ?> !important;
    font-family:<?php echo stripslashes($newfont); ?>;
}

.ar_main_div_<?php echo isset($form_id) ? $form_id : ''; ?> .arf_confirmation_summary_input{
    color:<?php echo isset($text_color) ?  $text_color : ''; ?> !important;
    font-family:<?php echo stripslashes($newfontother); ?>;
}

.ar_main_div_<?php echo isset($form_id) ? $form_id : ''; ?> #recaptcha_style{color:<?php echo isset($label_color) ? $label_color : ''; ?>;}

<?php if(isset($label_color)) { ?>
    .ar_main_div_<?php echo $form_id; ?> .arfaction_icon{ 
        background-image : url("data:image/svg+xml;base64,<?php echo base64_encode("<svg width='16px' xmlns='http://www.w3.org/2000/svg' height='17px'><path fill='rgb(".$arsettingcontroller->hex2rgb($label_color).")' d='M16.975,7.696l-0.732-2.717l-6.167,1.865l0.312-6.276H7.562l0.31,6.276L1.666,4.979L0.975,7.696L7.1,8.939l-3.69,5.574 l2.327,1.555l3.218-5.734l3.259,5.734l2.286-1.555L10.85,8.939L16.975,7.696z'/></svg>");?>") !important;
    }
<?php } ?>

.ar_main_div_<?php echo $form_id; ?> .arf_cal_header, .ar_main_div_<?php echo $form_id; ?> .arf_cal_month{background-color:<?php echo $base_color; ?> !important; color: <?php echo $arf_date_picker_text_color; ?> !important;}

.ar_main_div_<?php echo $form_id; ?> .bootstrap-datetimepicker-widget table tbody tr{background:#FFFFFF !important;}

.ar_main_div_<?php echo $form_id; ?> .timepicker-picker .timepicker-hour,.ar_main_div_<?php echo $form_id; ?> .timepicker-picker .timepicker-minute,.ar_main_div_<?php echo $form_id; ?> .timepicker-picker .arf-glyphicon,.ar_main_div_<?php echo $form_id; ?> .timepicker .arf_cal_minute,.ar_main_div_<?php echo $form_id; ?> .timepicker .arf_cal_hour {color:<?php echo $arf_date_picker_text_color; ?> !important; border:none; }

.ar_main_div_<?php echo $form_id; ?> .timepicker-picker .arf-glyphicon::before{
    color:<?php echo $base_color;?> !important;
}

.ar_main_div_<?php echo $form_id; ?> .timepicker-picker .btn.btn-primary{
    background-color:<?php echo $base_color;?> !important; 
    border-color:<?php echo $base_color;?> !important;   
}

.ar_main_div_<?php echo $form_id; ?> .timepicker .arf_cal_minute:hover,
.ar_main_div_<?php echo $form_id; ?> .timepicker .arf_cal_hour:hover {border-color:<?php echo $base_color; ?> !important;  }


.ar_main_div_<?php echo $form_id; ?> .bootstrap-datetimepicker-widget table td span.active { background-color:<?php echo $base_color; ?>; color: <?php echo $arf_date_picker_text_color; ?>; }
.ar_main_div_<?php echo $form_id; ?> .bootstrap-datetimepicker-widget table td span:hover{ border-color:<?php echo $base_color; ?>;}

.ar_main_div_<?php echo $form_id; ?> .picker-switch td span:hover{background-color:<?php echo $base_color; ?> !important;}



.ar_main_div_<?php echo $form_id; ?> .bootstrap-datetimepicker-widget table td.active, 
.ar_main_div_<?php echo $form_id; ?> .bootstrap-datetimepicker-widget table td.active:hover{ 
	color: <?php echo $base_color; ?> !important; 
}
.bootstrap-datetimepicker-widget table td.old, .bootstrap-datetimepicker-widget table td.new{color: #96979a !important;}

.ar_main_div_<?php echo $form_id; ?> .bootstrap-datetimepicker-widget table td.day,.ar_main_div_<?php echo $form_id; ?> .bootstrap-datetimepicker-widget table span.month,.ar_main_div_<?php echo $form_id; ?> .bootstrap-datetimepicker-widget table span.year:not(.disabled),.ar_main_div_<?php echo $form_id; ?> .bootstrap-datetimepicker-widget table span.decade:not(.disabled){
color :<?php echo $arf_date_picker_text_color; ?>;
}

.ar_main_div_<?php echo $form_id; ?> .arf_cal_month{border-bottom : <?php echo $base_color;?> !important;}

.ar_main_div_<?php echo $form_id; ?> .bootstrap-datetimepicker-widget table td.day:not(.active):hover {
   background-color: #F5F5F5;border-radius: 50px;-webkit-border-radius: 50px;-o-border-radius: 50px;-moz-border-radius: 50px;display:block;
}

.ar_main_div_<?php echo $form_id; ?> .bootstrap-datetimepicker-widget table td.active:not(.disabled), 
.ar_main_div_<?php echo $form_id; ?> .bootstrap-datetimepicker-widget table td.active:not(.disabled):hover{
 background-image : url("data:image/svg+xml;base64,<?php echo base64_encode("<svg width='35px' xmlns='http://www.w3.org/2000/svg' height='29px'><path fill='rgb(".$arsettingcontroller->hex2rgb($base_color).")' d='M15.732,27.748c0,0-14.495,0.2-14.71-11.834c0,0,0.087-7.377,7.161-11.82 c0,0,0.733-0.993-1.294-0.259c0,0-1.855,0.431-3.538,2.2c0,0-1.078,0.216-0.388-1.381c0,0,2.416-3.019,8.585-2.76 c0,0,2.372-2.458,7.419-1.293c0,0,0.819,0.517-0.518,0.819c0,0-5.361,0.514-3.753,1.122c0,0,14.021,3.073,14.322,13.943 C29.019,16.484,29.573,27.32,15.732,27.748z M26.991,16.182C26.24,7.404,14.389,3.543,14.389,3.543 c-2.693-0.747-4.285,0.683-4.285,0.683C8.767,4.969,6.583,7.804,6.583,7.804C2.216,13.627,3.612,18.47,3.612,18.47 c2.168,7.635,12.505,7.097,12.505,7.097C27.376,25.418,26.991,16.182,26.991,16.182z'/></svg>");?>") !important;
 background-repeat:no-repeat;
}

.ar_main_div_<?php echo $form_id; ?> .bootstrap-datetimepicker-widget table td.today:before{ border-color: <?php echo $base_color; ?>; }

.ar_main_div_<?php echo isset($form_id) ? $form_id : ''; ?> .arfmainformfield{
    margin-bottom:<?php echo isset($field_margin) ? $field_margin :''; ?>;
}

<?php
    $editor_margin = str_replace('px','',$field_margin);
    $editor_margin = $editor_margin - 19;
    echo ".arf_form_editor_content .ar_main_div_".$form_id." .arfmainformfield{";
        echo "margin-bottom:".$editor_margin."px;";
    echo "}";
?>

.ar_main_div_<?php echo isset($form_id) ? $form_id : ''; ?> .arfmainformfield.arf_field_type_checkbox, .ar_main_div_<?php echo isset($form_id) ? $form_id : ''; ?> .arfmainformfield.arf_field_type_radio {margin-bottom:<?php echo isset($radio_checkbox_field_margin) ? $radio_checkbox_field_margin :''; ?>;}

.ar_main_div_<?php echo $form_id; ?> .arfmainformfield.arf_column{clear:none;float:left;margin-right:20px;}

.ar_main_div_<?php echo $form_id; ?> p.description,
.ar_main_div_<?php echo $form_id; ?> div.description,
.ar_main_div_<?php echo $form_id; ?> div.arf_field_description,
.ar_main_div_<?php echo $form_id; ?> .arfeditorformdescription{
    margin:2px 0px 0px 0px;
    padding:0;
    font-family:<?php echo isset($description_font) ? stripslashes($description_font) : ''; ?>;
    font-size:<?php echo isset($description_font_size) ? $description_font_size : ''; ?>;
    color:<?php echo isset($description_color) ? $description_color : ''; ?>;
    text-align:<?php echo isset($description_align) ? $description_align :'';?>;
    font-style:<?php echo isset($description_style) ? $description_style : ''; ?>;
    max-width:100%;
    width:<?php echo (!isset($field_width) || $field_width == '') ? 'auto' : $field_width ?>;
    line-height: 20px;
}

.ar_main_div_<?php echo $form_id; ?> .help-block{
    margin:4px 0px 0px 0px;
    padding:0;
    font-family:<?php echo isset($description_font) ? stripslashes($description_font) : ''; ?>;
    font-size:<?php echo isset($description_font_size) ? $description_font_size : ''; ?>;
    text-align:<?php echo isset($description_align) ? $description_align :'';?>;
    font-style:<?php echo isset($description_style) ? $description_style : ''; ?>;
    max-width:100%;
    width:100%;
    line-height: 20px;
    position: <?php echo $standard_error_position ?> !important;
}

<?php
    if( $standard_error_position == 'absolute' ){
?>
.ar_main_div_<?php echo $form_id; ?> .arf_prefix_suffix_wrapper + .help-block{
    bottom:-20px !important;
}
<?php
    }
?>

 .ar_main_div_<?php echo $form_id; ?> .arfcount_text_char_div{margin:2px 0px 0px 0px;padding:0;font-family:<?php echo isset($description_font) ? stripslashes($description_font) : ''; ?>;font-size:<?php echo isset($description_font_size) ? $description_font_size : ''; ?>;color:<?php echo isset($description_color) ? $description_color : ''; ?>;text-align:<?php echo(is_rtl()) ? 'left' : 'right';?>;font-style:<?php echo isset($description_style) ? $description_style : ''; ?>;max-width:100%;width:auto; line-height: 20px;right:0;position:absolute;}

.ar_main_div_<?php echo $form_id; ?> .arf_textareachar_limit{float:left;width:95%;width:calc(100% - 50px) !important;}

.ar_main_div_<?php echo $form_id; ?> .left_container p.description, .ar_main_div_<?php echo $form_id; ?> .left_container div.description, .ar_main_div_<?php echo $form_id; ?> .left_container div.arf_field_description, .ar_main_div_<?php echo $form_id; ?> .left_container .help-block{margin-left:<?php echo $label_margin ?>px;}

.ar_main_div_<?php echo $form_id; ?> .arfmainformfield.arf_column div.arf_field_description{width:<?php echo (!isset($field_width) || $field_width == '') ? 'auto' : $field_width ?>;max-width:100%;}

.ar_main_div_<?php echo $form_id; ?> .left_container .attachment-thumbnail{clear:both;margin-left:<?php echo $label_margin ?>px;}

.ar_main_div_<?php echo $form_id; ?> .right_container p.description, .ar_main_div_<?php echo $form_id; ?> .right_container div.description, .ar_main_div_<?php echo $form_id; ?> .right_container div.arf_field_description, .ar_main_div_<?php echo $form_id; ?> .right_container .help-block{margin-right:<?php echo $label_margin ?>px;}

.ar_main_div_<?php echo $form_id; ?> .top_container label.arf_main_label, .ar_main_div_<?php echo $form_id; ?> .hidden_container label.arf_main_label, .ar_main_div_<?php echo $form_id; ?> .pos_top{display:inline-block;float:none;width:100%}

.ar_main_div_<?php echo $form_id; ?> .sortable_inner_wrapper .arfformfield .fieldname{ text-align:<?php echo $align;?>;}

.arf_form.ar_main_div_<?php echo $form_id; ?> .unsortable_inner_wrapper.edit_field_type_divider label.arf_main_label { text-align:<?php echo $form_align;?> !important; }

.ar_main_div_<?php echo $form_id; ?> .inline_container label.arf_main_label{ margin-right:10px; margin-left: 3px; }

.ar_main_div_<?php echo $form_id; ?> .left_container label.arf_main_label{display:inline;float:left;margin-right:15px;vertical-align:middle;padding-top:5px;width:<?php echo isset($width) ? $width : ''; if (strpos($width, 'px') === false) { echo 'px'; } ?>;word-wrap:break-word;}

.ar_main_div_<?php echo $form_id; ?> .right_container label.arf_main_label, .ar_main_div_<?php echo $form_id; ?> .pos_right{display:inline;float:right;margin-left:15px;vertical-align:middle;padding-top:5px;word-wrap:break-word;
width:<?php echo isset($width) ? $width : ''; if (strpos($width, 'px') === false) {
    echo 'px';
} ?>;}

.ar_main_div_<?php echo $form_id; ?> .none_container label.arf_main_label, .ar_main_div_<?php echo $form_id; ?> .pos_none{display:none;}

.ar_main_div_<?php echo $form_id; ?> input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider).arf_editor_colorpicker{
    width:100px !important;
    <?php if (is_rtl()) { ?>
        border-top-right-radius:0px !important;
        border-bottom-right-radius:0px !important;
    <?php } else { ?>
        border-top-left-radius:0px !important;
        border-bottom-left-radius:0px !important;
    <?php } ?>
}
.ar_main_div_<?php echo $form_id; ?> input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor),
.ar_main_div_<?php echo $form_id; ?> input[type=password],
.ar_main_div_<?php echo $form_id; ?> input[type=email],
.ar_main_div_<?php echo $form_id; ?> input[type=number],
.ar_main_div_<?php echo $form_id; ?> input[type=url], .ar_main_div_<?php echo $form_id; ?> input[type=tel]{font-family:<?php echo stripslashes($newfontother) ?> !important;font-size:<?php echo isset($field_font_size) ? $field_font_size : ''; ?> !important; height:<?php echo isset($field_height) ? $field_height : ''; ?>; font-weight:<?php echo $check_weight ?> !important; <?php echo $check_weight_font_style; ?> margin-bottom:0;clear:none;cursor:text;}

.ar_main_div_<?php echo $form_id; ?> input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider).arfautocompleterounded{
    border-bottom-left-radius: 0px !important;
    border-bottom-right-radius: 0px !important;

    <?php
    $border_radius_open = '0px';
    if(isset($border_radius) && !empty($border_radius))
    {
        $border_radius_open = str_replace('px', '', $border_radius);
        if($border_radius_open>19)
        {
            if($border_radius_open>$field_font_size_without_px)
            {
                if($field_font_size_without_px>=40)
                {
                    $border_radius_open = '36px';
                }
                else if($field_font_size_without_px>=36)
                {
                    $border_radius_open = '34px';
                }
                else if($field_font_size_without_px>20)
                {
                    $border_radius_open = $field_font_size_without_px+'px';
                }
                else 
                {
                    $border_radius_open = '20px';
                }
            }
            else if($border_radius_open>36 && $field_font_size_without_px==40)
            {
                $border_radius_open = '36px';
            }
            else if($field_font_size_without_px>14)
            {
                $border_radius_open = $border_radius;
            }
            else {
                $border_radius_open = '20px';
            }
        }
        else 
        {
            $border_radius_open = $border_radius;
        }
    }
    ?>
    border-top-left-radius:<?php echo isset($border_radius_open) ? $border_radius_open.' !important' : ''; ?>;
    border-top-right-radius:<?php echo isset($border_radius_open) ? $border_radius_open.' !important' : ''; ?>;
}

.ar_main_div_<?php echo $form_id; ?> select, #content .ar_main_div_<?php echo $form_id; ?> input:not([type=submit], [class=previous_btn]), #content .ar_main_div_<?php echo $form_id; ?> select {font-family:<?php echo stripslashes($newfontother) ?>;font-size:<?php echo isset($field_font_size) ? $field_font_size : '' ?>; font-weight:<?php echo $check_weight ?>; <?php echo $check_weight_font_style; ?> margin-bottom:0;clear:none;}

.ar_main_div_<?php echo $form_id; ?> textarea:not(.arf_field_option_input_textarea), #content .ar_main_div_<?php echo $form_id; ?> textarea{font-family:<?php echo stripslashes($newfontother) ?> !important;font-size:<?php echo isset($field_font_size) ? $field_font_size : ''; ?> !important;margin-bottom:0; font-weight:<?php echo $check_weight ?> !important; <?php echo $check_weight_font_style; ?>clear:none;}

.ar_main_div_<?php echo $form_id; ?> input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider),
.ar_main_div_<?php echo $form_id; ?> input[type=password],
.ar_main_div_<?php echo $form_id; ?> input[type=email],
.ar_main_div_<?php echo $form_id; ?> input[type=number],
.ar_main_div_<?php echo $form_id; ?> input[type=url],
.ar_main_div_<?php echo $form_id; ?> input[type=tel],
.ar_main_div_<?php echo $form_id; ?> select,
.allfields_style,
.allfields_active_style,
.allfields_error_style{
    color:<?php echo isset($text_color) ?  $text_color : ''; ?> !important;
    background-color:<?php echo isset($bg_color) ? $bg_color : '' ?> !important;
    border-color:<?php echo isset($border_color) ? $border_color : ''; ?> !important;
    border-width:<?php echo isset($field_border_width) ? $field_border_width : ''; ?> !important;
    border-style:<?php echo isset($field_border_style) ? $field_border_style : ''; ?> !important;
    -moz-border-radius:<?php echo isset($border_radius) ? $border_radius : ''; ?> !important;
    -webkit-border-radius:<?php echo isset($border_radius) ? $border_radius : ''; ?> !important;
    border-radius:<?php echo isset($border_radius) ? $border_radius : ''; ?> !important;
    -o-border-radius:<?php echo isset($border_radius) ? $border_radius : ''; ?> !important;
    width:<?php if (isset($field_width_unit) && $field_width_unit == '%') {
        echo '100%';
    } else {
        echo (!isset($field_width) || $field_width == '') ? 'auto' : $field_width;
    } ?> !important;
    font-size:<?php echo isset($field_font_size) ? $field_font_size : ''; ?>;
    padding:<?php echo isset($arffieldpaddingsetting) ? $arffieldpaddingsetting : ''; ?>!important;
    font-weight:<?php echo $check_weight ?>;
    <?php echo $check_weight_font_style; ?>
    -webkit-box-sizing:border-box;
    -moz-box-sizing:border-box;
    -o-box-sizing:border-box;
    box-sizing:border-box;
    height:<?php echo 'auto'; ?>;
    line-height:normal !important;
    direction:<?php echo isset($text_direction) ? $text_direction : ''; ?> !important;
    outline:none;
    clear:none;
    box-shadow:inherit;
    display:inline-block !important;
    margin: 0 !important;
}

.ar_main_div_<?php echo $form_id; ?> .controls .intl-tel-input .flag-container .selected-flag{
    border-top-left-radius:<?Php echo $border_radius; ?> !important;
    border-bottom-left-radius:<?Php echo $border_radius; ?> !important;
}

.ar_main_div_<?php echo $form_id; ?>  .controls .dropdown-toggle .arf_caret{border-top-color:<?php echo $border_color; ?>;}

.ar_main_div_<?php echo $form_id; ?> input[type=text].arf_colorpicker:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider) {
    <?php 
    $field_border_width_select_custom = $field_border_width_select;
    if(empty($field_border_width_select)) {
        $field_border_width_select_custom = 1;
    }
    ?>
    border-top:<?php echo $field_border_width_select_custom; ?>px <?php echo $field_border_style; ?> <?php echo $border_color; ?> !important;
    border-bottom:<?php echo $field_border_width_select_custom; ?>px <?php echo $field_border_style; ?> <?php echo $border_color; ?> !important;
    border-right:<?php echo $field_border_width_select_custom; ?>px <?php echo $field_border_style; ?> <?php echo $border_color; ?> !important;
    border-left:<?php echo $field_border_width_select_custom; ?>px <?php echo $field_border_style; ?> <?php echo $border_color; ?> !important;
}

.ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix.arf_colorpicker_prefix_editor {
    border-top:<?php echo $field_border_width_select_custom; ?>px <?php echo $field_border_style; ?> <?php echo $border_color; ?> !important;
    border-bottom:<?php echo $field_border_width_select_custom; ?>px <?php echo $field_border_style; ?> <?php echo $border_color; ?> !important;

    <?php if (is_rtl()) { ?>
        border-left:0px <?php echo $field_border_style; ?> <?php echo $border_color; ?> !important;
        border-right:<?php echo $field_border_width_select_custom; ?>px <?php echo $field_border_style; ?> <?php echo $border_color; ?> !important;
    <?php } else { ?>
        border-left:<?php echo $field_border_width_select_custom; ?>px <?php echo $field_border_style; ?> <?php echo $border_color; ?> !important;
        border-right:0px <?php echo $field_border_style; ?> <?php echo $border_color; ?> !important;
    <?php } ?>
}


<?php
    $placeholder_opacity = isset($placeholder_opacity) ? $placeholder_opacity : '0.5';
?>

.ar_main_div_<?php echo $form_id; ?> input[type=text]:not(.arfslider):not(.arf_autocomplete):not(.arf_field_option_input_text):not(.inplace_field)::-webkit-input-placeholder,
.wp-admin .allfields .controls .smaple-textarea::-webkit-input-placeholder, .ar_main_div_<?php echo $form_id; ?> .controls textarea::-webkit-input-placeholder,
.ar_main_div_<?php echo $form_id; ?> input[type=password]::-webkit-input-placeholder,
.ar_main_div_<?php echo $form_id; ?> input[type=number]::-webkit-input-placeholder,
.ar_main_div_<?php echo $form_id; ?> input[type=url]::-webkit-input-placeholder,
.ar_main_div_<?php echo $form_id; ?> input[type=tel]::-webkit-input-placeholder,
.ar_main_div_<?php echo $form_id; ?> select::-webkit-input-placeholder
{
    color:<?php echo isset($text_color) ? $text_color : ''; ?> !important;opacity:<?php echo $placeholder_opacity; ?> !important;<?php echo $check_weight_font_style_decoration; ?>
}

.ar_main_div_<?php echo $form_id; ?> input[type=text]:not(.arfslider):not(.arf_autocomplete):not(.arf_field_option_input_text):not(.inplace_field):-moz-placeholder,
.wp-admin .allfields .controls .smaple-textarea:-moz-placeholder, .ar_main_div_<?php echo $form_id; ?> .controls textarea:-moz-placeholder,
.ar_main_div_<?php echo $form_id; ?> input[type=password]:-moz-placeholder,
.ar_main_div_<?php echo $form_id; ?> input[type=number]:-moz-placeholder,
.ar_main_div_<?php echo $form_id; ?> input[type=url]:-moz-placeholder,
.ar_main_div_<?php echo $form_id; ?> input[type=tel]:-moz-placeholder,
.ar_main_div_<?php echo $form_id; ?> select:-moz-placeholder
{
    color:<?php echo isset($text_color) ? $text_color : ''; ?> !important;opacity:<?php echo $placeholder_opacity; ?> !important;<?php echo $check_weight_font_style_decoration; ?>
}

.ar_main_div_<?php echo $form_id; ?> input[type=text]:not(.arfslider):not(.arf_autocomplete):not(.arf_field_option_input_text):not(.inplace_field)::-moz-placeholder,
.wp-admin .allfields .controls .smaple-textarea::-moz-placeholder, .ar_main_div_<?php echo $form_id; ?> .controls textarea::-moz-placeholder,
.ar_main_div_<?php echo $form_id; ?> input[type=password]::-moz-placeholder,
.ar_main_div_<?php echo $form_id; ?> input[type=number]::-moz-placeholder,
.ar_main_div_<?php echo $form_id; ?> input[type=url]::-moz-placeholder,
.ar_main_div_<?php echo $form_id; ?> input[type=tel]::-moz-placeholder,
.ar_main_div_<?php echo $form_id; ?> select::-moz-placeholder
{
    color:<?php echo isset($text_color) ? $text_color : ''; ?> !important;opacity:<?php echo $placeholder_opacity; ?> !important;<?php echo $check_weight_font_style_decoration; ?>
}

.ar_main_div_<?php echo $form_id; ?> input[type=text]:not(.arfslider):not(.arf_autocomplete):not(.arf_field_option_input_text):not(.inplace_field):-ms-input-placeholder,
.wp-admin .allfields .controls .smaple-textarea:-ms-input-placeholder, .ar_main_div_<?php echo $form_id; ?> .controls textarea:-ms-input-placeholder,
.ar_main_div_<?php echo $form_id; ?> input[type=password]:-ms-input-placeholder,
.ar_main_div_<?php echo $form_id; ?> input[type=number]:-ms-input-placeholder,
.ar_main_div_<?php echo $form_id; ?> input[type=url]:-ms-input-placeholder,
.ar_main_div_<?php echo $form_id; ?> input[type=tel]:-ms-input-placeholder,
.ar_main_div_<?php echo $form_id; ?> select:-ms-input-placeholder
{
    color:<?php echo isset($text_color) ? 'rgba('.$arsettingcontroller->hex2rgb($text_color).', '.$placeholder_opacity.')' : ''; ?> !important;opacity:1 !important; <?php echo $check_weight_font_style_decoration; ?>
}

.ar_main_div_<?php echo $form_id; ?> input[type=text]:not(.arfslider):not(.arf_autocomplete):not(.arf_field_option_input_text):not(.inplace_field)::-ms-input-placeholder, 
.wp-admin .allfields .controls .smaple-textarea::-ms-input-placeholder, .ar_main_div_<?php echo $form_id; ?> .controls textarea::-ms-input-placeholder,
.ar_main_div_<?php echo $form_id; ?> input[type=password]::-ms-input-placeholder,
.ar_main_div_<?php echo $form_id; ?> input[type=number]::-ms-input-placeholder,
.ar_main_div_<?php echo $form_id; ?> input[type=url]::-ms-input-placeholder,
.ar_main_div_<?php echo $form_id; ?> input[type=tel]::-ms-input-placeholder,
.ar_main_div_<?php echo $form_id; ?> select::-ms-input-placeholder
{
    color:<?php echo isset($text_color) ? 'rgba('.$arsettingcontroller->hex2rgb($text_color).', '.$placeholder_opacity.')' : ''; ?> !important;opacity:1 !important; <?php echo $check_weight_font_style_decoration; ?>
}








.wp-admin .allfields .controls .smaple-textarea, .ar_main_div_<?php echo $form_id; ?> .controls textarea{color:<?php echo isset($textarea_text_color) ? $textarea_text_color : ''; ?> !important;background-color:<?php echo isset($textarea_bg_color) ? $textarea_bg_color : ''; ?> !important;border-color:<?php echo isset($textarea_border_color) ? $textarea_border_color : ''; ?> !important;border-width:<?php echo isset($field_textarea_border_width) ? $field_textarea_border_width : ''; ?> !important;border-style:<?php echo isset($field_textarea_border_style) ? $field_textarea_border_style.' !important' : ''; ?>;-moz-border-radius:<?php echo isset($border_radius) ? $border_radius : ''; ?> !important;-webkit-border-radius:<?php echo isset($border_radius) ? $border_radius : ''; ?> !important;border-radius:<?php echo isset($border_radius) ? $border_radius : ''; ?> !important; -o-border-radius:<?php echo isset($border_radius) ? $border_radius : ''; ?> !important; width:<?php
if (isset($field_width_unit) && $field_width_unit == '%') {
    echo '100%';
} else {
    echo (!isset($field_textarea_width) || $field_textarea_width == '') ? 'auto' : $field_textarea_width;
}
?> !important;max-width:100%;font-size:<?php echo isset($field_textarea_font_size) ? $field_textarea_font_size : ''; ?> !important; padding:<?php echo isset($field_textarea_pad) ? $field_textarea_pad : ''; ?>!important;-webkit-box-sizing:border-box;-moz-box-sizing:border-box; -o-box-sizing:border-box; box-sizing:border-box; -webkit-box-shadow:none;-o-box-shadow:none;-moz-box-shadow:none;box-shadow:none; direction:<?php echo isset($text_direction) ? $text_direction : ''; ?>; outline:none; margin-bottom:0; }

.wp-admin .ar_main_div_<?php echo $form_id; ?> select, .ar_main_div_<?php echo $form_id; ?> select {width:<?php echo (isset($auto_width)) ? 'auto' : (isset($field_width) ? $field_width : ''); ?>;max-width:100%; outline:none;-webkit-box-shadow:inherit;-o-box-shadow:inherit;-moz-box-shadow:inherit;box-shadow:inherit; }

.ar_main_div_<?php echo $form_id; ?> input[type="radio"], .ar_main_div_<?php echo $form_id; ?> input[type="checkbox"]{width:auto;border:none;background:transparent;padding:0;}

.ar_main_div_<?php echo $form_id; ?> input.auto_width, .ar_main_div_<?php echo $form_id; ?> select.auto_width, .ar_main_div_<?php echo $form_id; ?> textarea.auto_width{ width:auto; }

.ar_main_div_<?php echo $form_id; ?> select.auto_width { width:<?php echo (isset($auto_width)) ? 'auto' : (isset($field_width) ? $field_width : ''); ?>;max-width:100%; }

.ar_main_div_<?php echo $form_id; ?> input[disabled]:not(.arf_hide_opacity), .ar_main_div_<?php echo $form_id; ?> select[disabled]:not(.arf_hide_opacity), .ar_main_div_<?php echo $form_id; ?> textarea[disabled]:not(.arf_hide_opacity), .ar_main_div_<?php echo $form_id; ?> input[readonly], .ar_main_div_<?php echo $form_id; ?> select[readonly], .ar_main_div_<?php echo $form_id; ?> textarea[readonly]{opacity:.5;filter:alpha(opacity=50);}

.select_style .ar_main_div_<?php echo $form_id; ?> select, .select_style .ar_main_div_<?php echo $form_id; ?> select.auto_width{ width:100%;}

.ar_main_div_<?php echo $form_id; ?> .arfmainformfield .controls input:focus,
.ar_main_div_<?php echo $form_id; ?> input[type=text]:focus:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider),
.ar_main_div_<?php echo $form_id; ?> input[type=email]:focus:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider),.ar_main_div_<?php echo $form_id; ?> select:focus,.ar_main_div_<?php echo $form_id; ?> textarea:focus:not(.arf_field_option_input_textarea),
.ar_main_div_<?php echo $form_id; ?> input[type=password]:focus:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider),
.ar_main_div_<?php echo $form_id; ?> input[type=number]:focus:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider),
.ar_main_div_<?php echo $form_id; ?> input[type=url]:focus:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider),
.ar_main_div_<?php echo $form_id; ?> input[type=tel]:focus:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider),
.ar_main_div_<?php echo $form_id; ?> .controls textarea:focus,
.ar_main_div_<?php echo $form_id; ?> .frm_focus_field input[type=text]:not(.inplace_field),
.ar_main_div_<?php echo $form_id; ?> .frm_focus_field input[type=password],
.ar_main_div_<?php echo $form_id; ?> .frm_focus_field input[type=email],
.ar_main_div_<?php echo $form_id; ?> .frm_focus_field input[type=number],
.ar_main_div_<?php echo $form_id; ?> .frm_focus_field input[type=url],
.ar_main_div_<?php echo $form_id; ?> .frm_focus_field input[type=tel],
.allfields_active_style{
    background-color:<?php echo $bg_color_active ?> !important;
    border-color:<?php echo $base_color ?>  !important;
    box-shadow:none;
    -webkit-box-shadow:none;
    -o-box-shadow:none;
    -moz-box-shadow:none;
    -o-transition: all .4s;
    -moz-transition: all .4s;
    -webkit-transition: all .4s;
    -ms-transition: all .4s; outline:none;
    -moz-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb(isset($base_color) ? $base_color : ''); ?>, 0.4);
    -webkit-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb(isset($base_color) ? $base_color : ''); ?>, 0.4);
    -o-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb(isset($base_color) ? $base_color : ''); ?>, 0.4);
    box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb(isset($base_color) ? $base_color : ''); ?>, 0.4);
}

.ar_main_div_<?php echo $form_id; ?> .arf_submit_btn.arf_active_loader span.arfsubmitloader{
    width:<?php echo $submit_font_size; ?>;
    height:<?php echo $submit_font_size; ?>;
    <?php
        $border_width = ceil($submit_font_size_wpx / 8);
    ?>
    <?php if($arfsubmitbuttonstyle == 'border') { ?>
        border:<?php echo $border_width.'px'; ?> solid <?php echo isset($submit_bg_color) ? $submit_bg_color : '#ffffff'; ?>; 
    <?php }  else { ?>
        border:<?php echo $border_width.'px'; ?> solid <?php echo isset($submit_text_color) ? $submit_text_color : '#ffffff'; ?>;
    <?php } ?>
    border-bottom:<?php echo $border_width.'px'; ?> solid transparent;
}

.ar_main_div_<?php echo $form_id; ?> .arf_submit_btn.arf_complete_loader .arfsubmitloader{
    height: <?php echo $submit_font_size_wpx; ?>px;
    width: <?php echo ($submit_font_size_wpx / 2); ?>px;
    <?php
        $border_width = ceil($submit_font_size_wpx / 8);
    ?>
    <?php if($arfsubmitbuttonstyle == 'border') { ?>
        border-right: <?php echo $border_width.'px'; ?> solid <?php echo isset($submit_bg_color) ? $submit_bg_color : '#ffffff'; ?>;
        border-top: <?php echo $border_width.'px'; ?> solid <?php echo isset($submit_bg_color) ? $submit_bg_color : '#ffffff'; ?>;
    <?php } else { ?>
    border-right: <?php echo $border_width.'px'; ?> solid <?php echo isset($submit_text_color) ? $submit_text_color : '#ffffff'; ?>;
    border-top: <?php echo $border_width.'px'; ?> solid <?php echo isset($submit_text_color) ? $submit_text_color : '#ffffff'; 
    ?>;
    <?php } ?>
    animation-name: arf_loader_checkmark;
    animation-duration: 0.5s;
    animation-timing-function: linear;
    animation-fill-mode: initial;
    animation-iteration-count:1;
    -webkit-animation-name: arf_loader_checkmark;
    -webkit-animation-duration: 0.5s;
    -webkit-animation-timing-function: linear;
    -webkit-animation-iteration-count:1;
    -webkit-animation-fill-mode: initial;
    transform: scaleX(-1) rotate(140deg);
    -webkit-transform: scaleX(-1) rotate(140deg);
    -o-transform: scaleX(-1) rotate(140deg);
    -moz-transform: scaleX(-1) rotate(140deg);
}

<?php if($arfsubmitbuttonstyle == 'border') { ?>
.ar_main_div_<?php echo $form_id; ?>  .arf_submit_btn.arf_active_loader span.arfsubmitloader{
    border:<?php echo $border_width.'px'; ?> solid <?php echo isset($submit_text_color) ? $submit_text_color : '#ffffff'; ?>;
    border-bottom:<?php echo $border_width.'px'; ?> solid transparent;
    }
<?php } else if($arfsubmitbuttonstyle == 'reverse border') { ?>
.ar_main_div_<?php echo $form_id; ?>  .arf_submit_btn.arf_active_loader span.arfsubmitloader{
    border:<?php echo $border_width.'px'; ?> solid <?php echo isset($submit_bg_color) ? $submit_bg_color : '#ffffff'; ?>;
    border-bottom:<?php echo $border_width.'px'; ?> solid transparent;
    }
<?php } ?>
<?php if($arfsubmitbuttonstyle == 'border') { ?>
.ar_main_div_<?php echo $form_id; ?>  .arf_submit_btn.arf_complete_loader .arfsubmitloader{
    border-right: <?php echo $border_width.'px'; ?> solid <?php echo isset($submit_text_color) ? $submit_text_color : '#ffffff'; ?>;
    border-top: <?php echo $border_width.'px'; ?> solid <?php echo isset($submit_text_color) ? $submit_text_color : '#ffffff'; 
    ?>;
}
<?php } else if($arfsubmitbuttonstyle == 'reverse border') { ?>
.ar_main_div_<?php echo $form_id; ?>  .arf_submit_btn.arf_complete_loader .arfsubmitloader{
    border-right: <?php echo $border_width.'px'; ?> solid <?php echo isset($submit_bg_color) ? $submit_bg_color : '#ffffff'; ?>;
    border-top: <?php echo $border_width.'px'; ?> solid <?php echo isset($submit_bg_color) ? $submit_bg_color : '#ffffff'; 
    ?>;
}
<?php } ?>
@keyframes arf_loader_checkmark {
  0% {
    height: 0px;
    width: 0px;
    opacity: 1;
  }
  20% {
    height: 0px;
    width: <?php echo ($submit_font_size_wpx / 2); ?>px;
    opacity: 1;
  }
  40% {
    height: <?php echo $submit_font_size_wpx; ?>px;
    width: <?php echo ($submit_font_size_wpx / 2); ?>px;
    opacity: 1;
  }
  100% {
    height:<?php echo $submit_font_size_wpx; ?>px;
    width:<?php echo ($submit_font_size_wpx / 2); ?>px;
    opacity: 1;
  }
}
@-webkit-keyframes arf_loader_checkmark {
  0% {
    height: 0px;
    width: 0px;
    opacity: 1;
  }
  20% {
    height: 0px;
    width:<?php echo ($submit_font_size_wpx / 2); ?>px;
    opacity: 1;
  }
  40% {
    height: <?php echo $submit_font_size_wpx; ?>px;
    width: <?php echo ($submit_font_size_wpx / 2); ?>px;
    opacity: 1;
  }
  100% {
    height: <?php echo $submit_font_size_wpx; ?>px;
    width:<?php echo ($submit_font_size_wpx / 2); ?>px;
    opacity: 1;
  }
}
.ar_main_div_<?php echo $form_id; ?> .arfsubmitbutton input[type="submit"],
.ar_main_div_<?php echo $form_id; ?> .next_btn, 
.ar_main_div_<?php echo $form_id; ?> .arfsubmitbutton input[type="submit"].next_btn,
.ar_main_div_<?php echo $form_id; ?> input[type="button"].previous_btn,
.ar_main_div_<?php echo $form_id; ?> .previous_btn,
.submitbutton_style{
    clear:none;
    <?php
        if( trim($submit_width) == '' ){
            echo "min-width:".$submit_auto_width."px;";
        } else {
            echo "width:".$submit_width.";";
        }
    ?>
    font-family:<?php echo isset($arfsubmitfontfamily) ? stripslashes($arfsubmitfontfamily) : ''; ?>;
    font-size:<?php echo isset($submit_font_size) ? $submit_font_size : ''; ?>;
    height:<?php echo isset($submit_height) ? $submit_height : ''; ?>;
    text-align:center;
    <?php if($arfsubmitbuttonstyle == 'border') { ?>
        background:transparent !important;
        color:<?php echo isset($submit_bg_color) ? $submit_bg_color : ''; ?> !important;
        border:<?php echo ($submit_border_width > 0) ? $submit_border_width : '2px'; ?> solid <?php echo $submit_bg_color ?>;
    <?php } else if($arfsubmitbuttonstyle == 'reverse border') { ?>
        background:<?php echo $submit_bg_color ?> !important;
        color:<?php echo $submit_text_color; ?> !important;
        border:<?php echo ($submit_border_width > 0) ? $submit_border_width : '2px'; ?> solid <?php echo $submit_bg_color ?>;
    <?php } else { ?>
        background:<?php echo $submit_bg_color ?> !important;
        color:<?php echo $submit_text_color; ?> !important;
        border:<?php echo $submit_border_width ?> solid <?php echo $submit_border_color ?>;
    <?php } ?>
    border-style:solid;
    cursor:pointer;
    font-weight:<?php echo $submit_weight ?>;
    -moz-border-radius:<?php echo isset($submit_border_radius) ? $submit_border_radius : ''; ?>;
    -webkit-border-radius:<?php echo isset($submit_border_radius) ? $submit_border_radius : ''; ?>;
    border-radius:<?php echo isset($submit_border_radius) ? $submit_border_radius : ''; ?>;
    -o-border-radius:<?php echo isset($submit_border_radius) ? $submit_border_radius : ''; ?>;
    text-shadow:none;
    -moz-box-sizing:content-box;
    -webkit-box-sizing:content-box;
    -o-box-sizing:content-box;
    box-sizing:content-box;
    -ms-box-sizing:content-box;
    filter: progid:DXImageTransform.Microsoft.gradient(enabled = false);
    <?php if($arfsubmitbuttonstyle != 'border' && $arfsubmitbuttonstyle != 'reverse border') { ?>
    -moz-box-shadow:1px 2px 3px <?php echo isset($submit_shadow_color) ? $submit_shadow_color : ''; ?>;
    -o-box-shadow:1px 2px 3px <?php echo isset($submit_shadow_color) ? $submit_shadow_color : ''; ?>;
    -webkit-box-shadow:1px 2px 3px  ?php echo isset($submit_shadow_color) ? $submit_shadow_color : ''; ?>;
    box-shadow:1px 2px 3px <?php echo isset($submit_shadow_color) ? $submit_shadow_color : ''; ?>;
    -ms-filter:"progid:DXImageTransform.Microsoft.Shadow(Strength=3, Direction=135, Color='<?php echo isset($submit_shadow_color) ? $submit_shadow_color : ''; ?>')";
    <?php } ?>
    filter:progid:DXImageTransform.Microsoft.Shadow(Strength=3, Direction=135, Color='<?php echo isset($submit_shadow_color) ? $submit_shadow_color : ''; ?>'); 
    <?php echo $submit_weight_font_style; ?> padding:0 10px !important;
    <?php if (isset($submit_bg_img) && $submit_bg_img != '') {
        } else {
    ?>text-indent:0px;<?php } ?>
    text-transform: none;
    max-width:95%;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    appearance:none !important;
}
.ar_main_div_<?php echo $form_id; ?> .arfsubmitbutton input[type="submit"].next_btn {
    vertical-align: unset;
}
.ar_main_div_<?php echo $form_id; ?> input[type="button"].previous_btn{
    <?php
        echo (is_rtl()) ? "margin-left:15px;" : "margin-right:15px;";
    ?>
}

.ar_main_div_<?php echo $form_id; ?> input[type="submit"]:hover,
.ar_main_div_<?php echo $form_id; ?> input[type="submit"]:focus,
.ar_main_div_<?php echo $form_id; ?> .next_btn:hover,
.ar_main_div_<?php echo $form_id; ?> .next_btn:focus,
.ar_main_div_<?php echo $form_id; ?> .arfsubmitbutton input[type="submit"].next_btn:hover,
.ar_main_div_<?php echo $form_id; ?> .arfsubmitbutton input[type="submit"].next_btn:focus,
.ar_main_div_<?php echo $form_id; ?> .previous_btn:hover,
.ar_main_div_<?php echo $form_id; ?> .previous_btn:focus,
.submitbutton_style_<?php echo $form_id; ?> {
    <?php if($arfsubmitbuttonstyle == 'border') { ?>
        color:<?php echo $submit_text_color ?> !important;
        background-color:<?php echo $submit_bg_color_hover; ?> !important;
        border:<?php echo ($submit_border_width > 0) ? $submit_border_width : '2px'; ?> solid <?php echo $submit_bg_color_hover ?> !important;        
    <?php } else if($arfsubmitbuttonstyle == 'reverse border') { ?>
        background:transparent !important;
        color:<?php echo $submit_bg_color_hover ?> !important;
        border:<?php echo ($submit_border_width > 0) ? $submit_border_width : '2px'; ?> solid <?php echo $submit_bg_color_hover ?> !important;
    <?php } else { ?>
        background-color:<?php echo $submit_bg_color_hover ?> !important;
    <?php } ?>
    box-shadow:none;
    -webkit-box-shadow:none;
    -o-box-shadow:none;
    -moz-box-shadow:none;
    outline: none;
}

.ar_main_div_<?php echo $form_id; ?> .next_btn:hover,
.ar_main_div_<?php echo $form_id; ?> .next_btn:focus,
.ar_main_div_<?php echo $form_id; ?> .next_btn:active,
.ar_main_div_<?php echo $form_id; ?> .previous_btn:hover,
.ar_main_div_<?php echo $form_id; ?> .previous_btn:focus,
.ar_main_div_<?php echo $form_id; ?> .previous_btn:active,
.ar_main_div_<?php echo $form_id; ?> input[type="button"].previous_btn:active,
.ar_main_div_<?php echo $form_id; ?> input[type="button"].previous_btn:hover,
.ar_main_div_<?php echo $form_id; ?> input[type="button"].previous_btn:focus {
    background:none;
    background-color:<?php echo isset($submit_bg_color_hover) ? $submit_bg_color_hover : ''; ?> !important;
    padding:0 10px;
    border-width:<?php echo isset($submit_border_width) ? $submit_border_width : ''; ?>;
    border-color:<?php echo isset($submit_border_color) ? $submit_border_color : ''; ?>;
    border-style:solid;
    <?php if($arfsubmitbuttonstyle == 'border') { ?>
        color:<?php echo $submit_text_color ?> !important;
        background-color:<?php echo $submit_bg_color_hover; ?> !important;
        border:<?php echo ($submit_border_width > 0) ? $submit_border_width : '2px'; ?> solid <?php echo $submit_bg_color_hover ?> !important;        
    <?php } else if($arfsubmitbuttonstyle == 'reverse border') { ?>
        background:transparent !important;
        color:<?php echo $submit_bg_color_hover ?> !important;
        border:<?php echo ($submit_border_width > 0) ? $submit_border_width : '2px'; ?> solid <?php echo $submit_bg_color_hover ?> !important;
    <?php } else { ?>
        background-color:<?php echo $submit_bg_color_hover ?> !important;
    <?php } ?>
    padding:0 10px; 
    box-shadow:none;
    -webkit-box-shadow:none;
    -o-box-shadow:none;
    -moz-box-shadow:none;
    outline: none;
filter: progid:DXImageTransform.Microsoft.gradient(enabled = false);
} 

<?php if (isset($submit_bg_img) and $submit_bg_img != '') { ?>
    .ar_main_div_<?php echo $form_id; ?> .next_btn:active,
    .ar_main_div_<?php echo $form_id; ?> .next_btn:hover,
    .ar_main_div_<?php echo $form_id; ?> .previous_btn:hover,
    .ar_main_div_<?php echo $form_id; ?> .previous_btn:active,
    .ar_main_div_<?php echo $form_id; ?> input[type="button"].previous_btn:active,
    .ar_main_div_<?php echo $form_id; ?> input[type="button"].previous_btn:hover { background:<?php echo $submit_bg_color ?>; background-color:<?php echo $submit_bg_color_hover ?>; }
<?php } ?>

.submitbutton_style{height:auto;}

.ar_main_div_<?php echo $form_id; ?> .left_container .arf_radiobutton, .ar_main_div_<?php echo $form_id; ?> .none_container .arf_radiobutton{margin<?php echo (isset($radio_align) && $radio_align == 'block') ? "-bottom:5px;" : ':0 20px 5px 0'; ?>}

.ar_main_div_<?php echo $form_id; ?> .right_container .arf_radiobutton{margin<?php echo (isset($radio_align) &&  $radio_align == 'block') ? "-right:{$label_margin}px; margin-bottom:5px;" : ':0 0 5px 20px'; ?>}

.ar_main_div_<?php echo $form_id; ?> .arf_checkbox_style{display:<?php echo (isset($check_align) && $check_align == 'inline') ? 'inline-block' : (isset($check_align) ? $check_align : ''); ?>;clear:none;box-shadow:inherit;-moz-box-shadow:inherit;-webkit-box-shadow:inherit;-o-box-shadow:inherit;
}

.ar_main_div_<?php echo $form_id; ?> .left_container .arf_checkbox_style, .ar_main_div_<?php echo $form_id; ?> .none_container .arf_checkbox_style{margin<?php echo (isset($check_align) && $check_align == 'block') ? "-bottom:5px;" : ':2px 20px 5px 0'; ?>}

.ar_main_div_<?php echo $form_id; ?> .right_container .arf_checkbox_style{margin<?php echo (isset($check_align) && $check_align == 'block') ? "-right:{$label_margin}px;margin-bottom:5px;" : ':0 20px 5px 0'; ?>}

.ar_main_div_<?php echo $form_id; ?> .arf_horizontal_radio.left_container .arf_radiobutton, .ar_main_div_<?php echo $form_id; ?> .right_container .arf_radiobutton{margin:0 20px 10px 0;}

.ar_main_div_<?php echo $form_id; ?> .arf_vertical_radio .arf_checkbox_style, .ar_main_div_<?php echo $form_id; ?> .arf_vertical_radio .arf_radiobutton, .arf_vertical_radio {display:block;}

.ar_main_div_<?php echo $form_id; ?> .arf_horizontal_radio .arf_checkbox_style, .ar_main_div_<?php echo $form_id; ?> .arf_horizontal_radio .arf_radiobutton {display:inline-block;margin:0 20px 10px 0;}

.ar_main_div_<?php echo $form_id; ?> .arf_radiobutton{display:<?php echo (isset($radio_align) && $radio_align == 'inline') ? 'inline-block' : (isset($radio_align) ? $radio_align : ''); ?>;clear:none;box-shadow:inherit; -webkit-box-shadow:inherit;-moz-box-shadow:inherit;-o-box-shadow:inherit;
}

<?php
    $temp_label_font_size = str_replace('px','',$font_size);
    $final_width_calc = ($temp_label_font_size + 12) < 30 ? 30 : ($temp_label_font_size + 12);
?>

.ar_main_div_<?php echo $form_id; ?> .arf_radiobutton label, .ar_main_div_<?php echo $form_id; ?> .arf_checkbox_style label,
.ar_main_div_<?php echo $form_id; ?> .arf_checkbox_style span.arf_checkbox_label,.ar_main_div_<?php echo $form_id; ?>  .arf_radiobutton span.arf_radio_label{
    font-family:<?php echo stripslashes($newfont) ?> !important;
    font-size:<?php echo $font_size ?> !important;
    color:<?php echo isset($label_color) ? $label_color : ''; ?> !important;
    font-weight:<?php echo $weight ?>;
    <?php echo $weight_font_style; ?>
    display:inline-block;
    cursor:pointer;
    width:auto;
}

.ar_main_div_<?php echo $form_id; ?> .top_container .arf_checkbox_style:not(.arf_enable_checkbox_image_editor):not(.arf_enable_checkbox_image),
.ar_main_div_<?php echo $form_id; ?> .top_container .arf_radiobutton:not(.arf_enable_radio_image_editor):not(.arf_enable_radio_image) {
    margin:0 2% 10px 0;
    max-width:100%;
    <?php
    if( is_rtl() ){
        ?>
        padding-right:<?php echo $final_width_calc; ?>px;
        <?php
    } else {
    ?>
    padding-left:<?php echo $final_width_calc; ?>px;
    <?php
    }
    ?>

}

.ar_main_div_<?php echo $form_id; ?> .top_container .arf_radiobutton:not(.arf_enable_radio_image_editor):not(.arf_enable_radio_image) .arf_radio_input_wrapper,
.ar_main_div_<?php echo $form_id; ?> .top_container .arf_checkbox_style:not(.arf_enable_checkbox_image_editor):not(.arf_enable_checkbox_image) .arf_checkbox_input_wrapper{
    position:absolute !important;
    <?php
    if( is_rtl() ){
    ?>
        margin-right:-<?php echo $final_width_calc; ?>px !important;
    <?php
        } else {
    ?>
        margin-left:-<?php echo $final_width_calc; ?>px !important;
    <?php
    }
    ?>
}

.ar_main_div_<?php echo $form_id; ?> .arf_radiobutton.arf_enable_radio_image label,
.ar_main_div_<?php echo $form_id; ?> .arf_radiobutton.arf_enable_radio_image_editor label,
.ar_main_div_<?php echo $form_id; ?> .arf_checkbox_style.arf_enable_checkbox_image label,
.ar_main_div_<?php echo $form_id; ?> .arf_checkbox_style.arf_enable_checkbox_image_editor label{
    width:auto !important;
}

.ar_main_div_<?php echo $form_id; ?> .arf_checkbox_style.arf_enable_checkbox_image label .arf_checkbox_label_image.checked,
.ar_main_div_<?php echo $form_id; ?> .arf_radiobutton.arf_enable_radio_image label .arf_radio_label_image.checked{
    border-color:<?php echo ($base_color) ? $base_color : ''; ?> !important;
}

.ar_main_div_<?php echo $form_id; ?> input[type="checkbox"]:checked+.arf_js_field_switch 
{
    background:<?php echo ($base_color) ? $base_color : ''; ?> !important ;
    border-color:<?php echo ($base_color) ? $base_color : ''; ?> !important;
}


.ar_main_div_<?php echo $form_id; ?> .arfblankfield input[type=text]:not(.inplace_field), .ar_main_div_<?php echo $form_id; ?> .arfblankfield input[type=password], .ar_main_div_<?php echo $form_id; ?> .arfblankfield input[type=url], .ar_main_div_<?php echo $form_id; ?> .arfblankfield input[type=tel], .ar_main_div_<?php echo $form_id; ?> .arfblankfield input[type=number], .ar_main_div_<?php echo $form_id; ?> .arfblankfield input[type=email], .ar_main_div_<?php echo $form_id; ?> .arfblankfield select, .allfields_error_style {background-color:<?php echo isset($bg_color_error) ? $bg_color_error : ''; ?>;border-color:<?php echo isset($border_color_error) ? $border_color_error : ''; ?>;border-width:<?php echo isset($border_width_error) ? $border_width_error : ''; ?>;border-style:<?php echo isset($border_style_error) ? $border_style_error : ''; ?>;}

.ar_main_div_<?php echo $form_id; ?> .arfmainformfield .arf_htmlfield_control{color:<?php echo isset($label_color) ? $label_color : ''; ?>;}

.ar_main_div_<?php echo $form_id; ?> ul.arfdropdown-menu { overflow-x:hidden; margin:0 !important; }
.ar_main_div_<?php echo $form_id; ?> .arfdropdown-menu > li { margin:0 !important; }
.ar_main_div_<?php echo $form_id; ?> .sltstandard_front .btn-group .current {
border: <?php echo isset($field_border_width) ? $field_border_width : ''; ?> <?php echo isset($field_border_style) ? $field_border_style : ''; ?>;
}

<?php
$field_border_width_select = isset($field_border_width_select) ? $field_border_width_select : 0 ;
$field_font_size_without_px = isset($field_font_size_without_px) ? $field_font_size_without_px : '';
$arffieldpaddingsetting = isset($arffieldpaddingsetting) ? $arffieldpaddingsetting : 0;
$dropdown_menu_min_height = $field_font_size_without_px + ( 2 * ( (int) $field_border_width_select ) );
$fieldpadding = explode(' ', $arffieldpaddingsetting);
$fieldpadding_1 = $fieldpadding[0];
$fieldpadding_1 = str_replace('px', '', $fieldpadding_1);
$fieldpadding_2 = 0;
if(count($fieldpadding)>1)
{
    $fieldpadding_2 = $fieldpadding[1];
    $fieldpadding_2 = str_replace('px', '', $fieldpadding_2);
}

$dropdown_menu_min_height = $dropdown_menu_min_height + ( 2 * ( (int) $fieldpadding_1 ) );
$field_font_size = isset($field_font_size) ? $field_font_size : '';
$text_color = isset($text_color) ? $text_color : '';
?>
.ar_main_div_<?php echo $form_id; ?> .sltstandard_time .btn-group .arfbtn.dropdown-toggle,
.ar_main_div_<?php echo $form_id; ?> .sltstandard_front .btn-group .arfbtn.dropdown-toggle {
border: <?php echo isset($field_border_width) ? $field_border_width : ''; ?> <?php echo isset($field_border_style) ? $field_border_style : ''; ?> <?php echo isset($border_color) ? $border_color : ''; ?> !important;
background-color:<?php echo isset($bg_color) ? $bg_color : '';?> !important;
background-image:none;
box-shadow:none;
-webkit-box-shadow:none;
-o-box-shadow:none;
-moz-box-shadow:none;
outline:0 !important;
-moz-border-radius:<?php echo isset($border_radius) ? $border_radius : ''; ?> !important;
-webkit-border-radius:<?php echo isset($border_radius) ? $border_radius : ''; ?> !important;
-o-border-radius:<?php echo isset($border_radius) ? $border_radius : ''; ?> !important;
border-radius:<?php echo isset($border_radius) ? $border_radius : ''; ?>;
padding:<?php echo $arffieldpaddingsetting ?> !important;
line-height: normal;
font-size:<?php echo $field_font_size; ?>;
color:<?php echo $text_color; ?> !important; 
font-family:<?php echo stripslashes($newfontother) ?>;
font-weight:<?php echo $check_weight ?>;
text-shadow:none;
text-transform:none;	    
<?php echo $check_weight_font_style; ?>;
width:100%;
margin-top:0px;    
}

.ar_main_div_<?php echo $form_id; ?> .sltstandard_time .btn-group.open .arfbtn.dropdown-toggle,
.ar_main_div_<?php echo $form_id; ?> .sltstandard_front .btn-group.open .arfbtn.dropdown-toggle {
    <?php
    $border_radius_open = '0px';
    if(isset($border_radius) && !empty($border_radius))
    {
        $border_radius_open = str_replace('px', '', $border_radius);
        if($border_radius_open>19)
        {
            if($border_radius_open>$field_font_size_without_px)
            {
                if($field_font_size_without_px>=40)
                {
                    $border_radius_open = '36px';
                }
                else if($field_font_size_without_px>=36)
                {
                    $border_radius_open = '34px';
                }
                else if($field_font_size_without_px>20)
                {
                    $border_radius_open = $field_font_size_without_px+'px';
                }
                else 
                {
                    $border_radius_open = '20px';
                }
            }
            else if($border_radius_open>36 && $field_font_size_without_px==40)
            {
                $border_radius_open = '36px';
            }
            else if($field_font_size_without_px>14)
            {
                $border_radius_open = $border_radius;
            }
            else {
                $border_radius_open = '20px';
            }
        }
        else 
        {
            $border_radius_open = $border_radius;
        }
    }
    ?>
    border-radius:<?php echo isset($border_radius_open) ? $border_radius_open.' !important' : ''; ?>;
    -moz-border-radius:<?php echo isset($border_radius_open) ? $border_radius_open.' !important' : ''; ?>;
    -webkit-border-radius:<?php echo isset($border_radius_open) ? $border_radius_open.' !important' : ''; ?>;
    -o-border-radius:<?php echo isset($border_radius_open) ? $border_radius_open.' !important' : ''; ?>;
}

.ar_main_div_<?php echo $form_id; ?> .sltstandard_time .btn-group:focus,
.ar_main_div_<?php echo $form_id; ?> .sltstandard_front .btn-group:focus,
.ar_main_div_<?php echo $form_id; ?> .sltstandard_time .btn-group .arfbtn.dropdown-toggle:focus,
.ar_main_div_<?php echo $form_id; ?> .sltstandard_front .btn-group .arfbtn.dropdown-toggle:focus {
border: <?php echo $field_border_width ?> <?php echo $field_border_style ?> <?php echo $base_color ?> !important;
background-color: <?php echo $bg_color_active; ?>;
background-image:none;
box-shadow:none;
-webkit-box-shadow:none;
-o-box-shadow:none;
-moz-box-shadow:none;
outline:0 !important;

font-size:<?php echo $field_font_size; ?>;
color:<?php echo $text_color; ?> !important; 
font-family:<?php echo stripslashes($newfontother) ?>;
font-weight:<?php echo $check_weight ?>; 
<?php echo $check_weight_font_style; ?>;
width:100%;
-moz-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($base_color); ?>, 0.4);
-webkit-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($base_color); ?>, 0.4);
-o-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($base_color); ?>, 0.4);
box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($base_color); ?>, 0.4);
margin-top:0px;    
min-height:<?php echo $dropdown_menu_min_height . "px"; ?>;
}

.ar_main_div_<?php echo $form_id; ?> .sltstandard_time .btn-group.open .arfbtn.dropdown-toggle,
.ar_main_div_<?php echo $form_id; ?> .sltstandard_front .btn-group.open .arfbtn.dropdown-toggle {
border: <?php echo $field_border_width ?> <?php echo $field_border_style ?> <?php echo $base_color ?>;
background-color:<?php echo $bg_color_active ?> !important;
border-bottom-color:transparent;
box-shadow:none;
-webkit-box-shadow:none;
-o-box-shadow:none;
-moz-box-shadow:none;
outline:0 !important;
outline-style:none;
border-bottom-left-radius:0px !important;
border-bottom-right-radius:0px !important;

font-size:<?php echo $field_font_size; ?>;
color:<?php echo $text_color; ?> !important; 
font-family:<?php echo stripslashes($newfontother) ?>;
font-weight:<?php echo $check_weight ?>; 
<?php echo $check_weight_font_style; ?>;
width:100%;
-moz-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($base_color); ?>, 0.4);
-webkit-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($base_color); ?>, 0.4);
-o-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($base_color); ?>, 0.4);
box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($base_color); ?>, 0.4);
margin-top:0px;    
min-height:<?php echo $dropdown_menu_min_height . "px"; ?>;
}

.ar_main_div_<?php echo $form_id; ?> .sltstandard_time .btn-group.dropup.open .arfbtn.dropdown-toggle,
.ar_main_div_<?php echo $form_id; ?> .sltstandard_front .btn-group.dropup.open .arfbtn.dropdown-toggle {
border: <?php echo $field_border_width ?> <?php echo $field_border_style ?> <?php echo $base_color ?>;
background-color:<?php echo $bg_color_active ?> !important;
border-top-color:transparent;
box-shadow:none;
-webkit-box-shadow:none;
-moz-box-shadow:none;
-o-box-shadow:none;
outline:0 !important;
outline-style:none;
border-top-left-radius:0px;
border-top-right-radius:0px;
border-bottom-left-radius:<?php echo $border_radius ?>;
border-bottom-right-radius:<?php echo $border_radius ?>;
}

.ar_main_div_<?php echo $form_id; ?> .sltstandard_time .btn-group .arfdropdown-menu,
.ar_main_div_<?php echo $form_id; ?> .sltstandard_front .btn-group .arfdropdown-menu {
margin:0;
}

.ar_main_div_<?php echo $form_id; ?> .sltstandard_time .btn-group.open .arfdropdown-menu,
.ar_main_div_<?php echo $form_id; ?> .sltstandard_front .btn-group.open .arfdropdown-menu, 
.ar_main_div_<?php echo $form_id; ?> .controls .typeahead.arfdropdown-menu {
border: <?php echo $field_border_width ?> <?php echo $field_border_style ?> <?php echo $base_color ?>;
box-shadow:none;
-webkit-box-shadow:none;
-o-box-shadow:none;
-moz-box-shadow:none;
border-top:none;
margin:0;
margin-top:-<?php echo $field_border_width ?>;
border-top-left-radius:0px;
border-top-right-radius:0px;	
width:100%;
overflow:hidden;
}

.ar_main_div_<?php echo $form_id; ?> .sltstandard_time .btn-group.dropup.open .arfdropdown-menu,
.ar_main_div_<?php echo $form_id; ?> .sltstandard_front .btn-group.dropup.open .arfdropdown-menu {
border: <?php echo $field_border_width ?> <?php echo $field_border_style ?> <?php echo $base_color ?>;
box-shadow:none;
-webkit-box-shadow:none;
-o-box-shadow:none;
-moz-box-shadow:none;
border-bottom:none;
margin:0;
margin-bottom:-<?php echo $field_border_width ?>;
border-bottom-left-radius:0px;
border-bottom-right-radius:0px;
border-top-left-radius:<?php echo $border_radius ?>;
border-top-right-radius:<?php echo $border_radius ?>;

font-size:<?php echo $field_font_size; ?>;
color:<?php echo $text_color; ?> !important; 
font-family:<?php echo stripslashes($newfontother) ?>;
font-weight:<?php echo $check_weight ?>; 
<?php echo $check_weight_font_style; ?>;
width:100%;
margin-top:0px;    
min-height:<?php echo $dropdown_menu_min_height . "px"; ?>;
}

.ar_main_div_<?php echo $form_id; ?> .bootstrap-select.btn-group .arfbtn .filter-option {
padding-top:0px;
text-align: <?php echo ($text_direction == 'rtl') ? 'right' : 'left'; ?>;
<?php
if ($field_font_size_without_px <= 27 && $field_font_size_without_px > 14) {
    echo "padding-top:1px;";
} elseif ($field_font_size_without_px >= 28 && $field_font_size_without_px < 27) {
    echo "padding-top:1px;";
} elseif ($field_font_size_without_px >= 36) {
    echo "padding-top:2px;";
}
?>
left:<?php echo $fieldpadding_2;?>px;
right:<?php echo ($fieldpadding_2+13);?>px;
}

.ar_main_div_<?php echo $form_id; ?> .bootstrap-select:not([class*="span"]):not([class*="col-"]):not([class*="form-control"]) {
<?php if ($field_width == '' || $field_width == 'auto') {
    $combo_width = '245px';
} else if ($field_width_unit == '%') {
    $combo_width = '100%';
} else {
    $combo_width = $field_width;
} ?>
width:<?php echo $combo_width . "" ?>;
}

.arfdropdown-menu ul.arfdropdown-menu li a span.text {
font-size:<?php echo $field_font_size; ?>;
}

.ar_main_div_<?php echo $form_id; ?> .sltstandard_time .btn-group.dropup.open .arfdropdown-menu .arfdropdown-menu.inner,
.ar_main_div_<?php echo $form_id; ?> .sltstandard_front .btn-group.dropup.open .arfdropdown-menu .arfdropdown-menu.inner {
border-top:none;
}

.ar_main_div_<?php echo $form_id; ?> .bootstrap-select.btn-group, 
.ar_main_div_<?php echo $form_id; ?> .bootstrap-select.btn-group[class*="span"] {
margin-bottom:2px;
}

.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_time .btn-group .arfbtn.dropdown-toggle,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_front .btn-group .arfbtn.dropdown-toggle {
border: <?php echo $border_width_error ?> <?php echo $field_border_style ?> <?php echo $border_color_error ?>;
background-color: <?php echo $bg_color_error; ?> !important;
}

.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_time .btn-group.open .arfbtn.dropdown-toggle,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_front .btn-group.open .arfbtn.dropdown-toggle {
border: <?php echo $border_width_error ?> <?php echo $field_border_style ?> <?php echo $border_color_error ?>;
border-bottom:none;
-moz-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($border_color_error); ?>, 0.4);
-webkit-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($border_color_error); ?>, 0.4);
-o-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($border_color_error); ?>, 0.4);
box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($border_color_error); ?>, 0.4);
}

.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_time .btn-group.dropup.open .arfbtn.dropdown-toggle,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_front .btn-group.dropup.open .arfbtn.dropdown-toggle {
border: <?php echo $border_width_error ?> <?php echo $field_border_style ?> <?php echo $border_color_error ?>;
}

.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_time .btn-group.open .arfdropdown-menu,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_front .btn-group.open .arfdropdown-menu {
border: <?php echo $border_width_error ?> <?php echo $field_border_style ?> <?php echo $border_color_error ?>;
border-top:none;
}

.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_time .btn-group.dropup.open .arfdropdown-menu,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_front .btn-group.dropup.open .arfdropdown-menu {
border: <?php echo $border_width_error ?> <?php echo $field_border_style ?> <?php echo $border_color_error ?>;
border-bottom:none;
}

.ar_main_div_<?php echo $form_id; ?> .sltstandard_time .btn-group.open ul.arfdropdown-menu,
.ar_main_div_<?php echo $form_id; ?> .sltstandard_front .btn-group.open ul.arfdropdown-menu,
.ar_main_div_<?php echo $form_id; ?> .sltstandard_time .btn-group.dropup.open ul.arfdropdown-menu,
.ar_main_div_<?php echo $form_id; ?> .sltstandard_front .btn-group.dropup.open ul.arfdropdown-menu { 
border:none;
}

.ar_main_div_<?php echo $form_id; ?> .sltstandard_front .btn-group.open ul.arfdropdown-menu > li,
.ar_main_div_<?php echo $form_id; ?> .sltstandard_front .btn-group.open ul.arfdropdown-menu > li {
margin:0 !important;
outline:none;
}

.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_time .btn-group.open ul.arfdropdown-menu,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_front .btn-group.open ul.arfdropdown-menu,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_time .btn-group.dropup.open ul.arfdropdown-menu,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_front .btn-group.dropup.open ul.arfdropdown-menu {
border:none;
}

.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_front .btn-group.open ul.arfdropdown-menu > li,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_front .btn-group.open ul.arfdropdown-menu > li,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_front .btn-group.dropup.open ul.arfdropdown-menu > li,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_front .btn-group.dropup.open ul.arfdropdown-menu > li {
margin:0 !important;
}

.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_time .btn-group .arfbtn.dropdown-toggle:focus,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_front .btn-group .arfbtn.dropdown-toggle:focus {
-moz-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($border_color_error); ?>, 0.4);
-webkit-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($border_color_error); ?>, 0.4);
-o-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($border_color_error); ?>, 0.4);
box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($border_color_error); ?>, 0.4);
}

.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_front .btn-group .arfdropdown-menu.open,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_time .btn-group .arfdropdown-menu.open {
border-top:1px <?php echo $field_border_style ?> <?php echo $border_color_error ?>; 
-moz-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($border_color_error); ?>, 0.4);
-webkit-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($border_color_error); ?>, 0.4);
-o-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($border_color_error); ?>, 0.4);
box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($border_color_error); ?>, 0.4);
}

<?php
if ($field_width_unit == "%") {
    $dropdownwidthvar = "width:99%;";
} else {
    if ($field_font_size_without_px != "" && $field_font_size_without_px != "auto") {
        $dropdown_optionwidth = $field_width - 28;
        if ($dropdown_optionwidth < 0) {
            $dropdown_optionwidth = 0;
        }
        $dropdownwidthvar = "width:" . $dropdown_optionwidth . "px;";
    } else {
        $dropdownwidthvar = "width:217px;";
    }
}
?>

.ar_main_div_<?php echo $form_id; ?> .arfdropdown-menu > li > a {
font-size:<?php echo $field_font_size; ?>;
color:<?php echo $text_color; ?> !important; 
font-family:<?php echo stripslashes($newfontother) ?>;
font-weight:<?php echo $check_weight ?>; 
text-decoration:none;
<?php echo $check_weight_font_style; ?>;
<?php
if ($field_font_size_without_px >= 36) {
    echo "padding:14px 12px;";
} elseif ($field_font_size_without_px >= 28) {
    echo "padding:12px 12px;";
} elseif ($field_font_size_without_px >= 24) {
    echo "padding:10px 12px;";
} elseif ($field_font_size_without_px >= 22) {
    echo "padding:08px 12px;";
} elseif ($field_font_size_without_px >= 20) {
    echo "padding:06px 12px;";
} elseif ($field_font_size_without_px >= 24) {
    echo "padding:10px 12px;";
} elseif ($field_font_size_without_px <= 18) {
    echo "padding:3px 12px;";
}
?>
<?php
    echo "padding:".$fieldpadding_1."px ".($fieldpadding_2+13)."px ".$fieldpadding_1."px ".($fieldpadding_2)."px !important; "
?>
line-height: normal;    
}

.ar_main_div_<?php echo $form_id; ?> .sltstandard_front .btn-group.open ul.arfdropdown-menu > li,
.ar_main_div_<?php echo $form_id; ?> .sltstandard_time .btn-group.open ul.arfdropdown-menu > li {
text-align: <?php echo ($text_direction == 'rtl') ? 'right' : 'left'; ?>;
}

.ar_main_div_<?php echo $form_id; ?> ul.typeahead.arfdropdown-menu > li{
text-align: <?php echo ($text_direction == 'rtl') ? 'right' : 'left'; ?>;
}

.ar_main_div_<?php echo $form_id; ?> .arfdropdown-menu > li:hover > a,
.ar_main_div_<?php echo $form_id; ?> .arfdropdown-menu > li:focus > a,
.ar_main_div_<?php echo $form_id; ?> .arfdropdown-menu > .active > a,
.ar_main_div_<?php echo $form_id; ?> .arfdropdown-menu > li:hover > a > span.text {
color: #ffffff !important;
}

.ar_main_div_<?php echo $form_id; ?> .sltstandard_front .btn-group.bootstrap-select,
.ar_main_div_<?php echo $form_id; ?> .sltstandard_time .btn-group.bootstrap-select {
width:<?php echo ($field_width == '' || $field_width == 'auto') ? '245px' : $field_width . "" ?>;
}

.ar_main_div_<?php echo $form_id; ?> .arfdropdown-menu > li:hover > a,
.ar_main_div_<?php echo $form_id; ?> .arfdropdown-menu > .active > a,
.ar_main_div_<?php echo $form_id; ?> .arfdropdown-menu > li:focus > a{
background-color: <?php echo $base_color ?> !important;
}

.ar_main_div_<?php echo $form_id; ?> .arfblankfield textarea{background-color:<?php echo $bg_color_error ?>;border-color:<?php echo $border_color_error ?>;border-width:<?php echo $border_width_error ?>;border-style:<?php echo $border_style_error ?>;}

.ar_main_div_<?php echo $form_id; ?> :invalid, .ar_main_div_<?php echo $form_id; ?> :-moz-submit-invalid, .ar_main_div_<?php echo $form_id; ?> :-moz-ui-invalid {box-shadow:none;}

.ar_main_div_<?php echo $form_id; ?> .help-block{
    font-weight:<?php echo $weight ?>;
    color: <?php echo ($arfvalidationerrorstyle == 'normal') ? $validation_bgcolor : $validation_textcolor; ?> !important;
    <?php echo $weight_font_style; ?>
    font-family:<?php echo stripslashes($newerror_font) ?>;
    font-size:<?php echo $error_font_size ?>;
}

.ar_main_div_<?php echo $form_id; ?> .help-block ul li{
    color: <?php echo ($arfvalidationerrorstyle == 'normal') ? $validation_bgcolor : $validation_textcolor; ?> !important;
}

.ar_main_div_<?php echo $form_id; ?> .frm_error_style img{padding-right:10px;vertical-align:middle;}

.ar_main_div_<?php echo $form_id; ?> .frm_message img{padding-right:10px;vertical-align:middle;}

.ar_main_div_<?php echo $form_id; ?> .trigger_style{cursor:pointer;}

.ar_main_div_<?php echo $form_id; ?> .frm_message, .success_style{border:1px solid <?php echo $success_border_color ?>;background-color:<?php echo $success_bg_color ?>;color:<?php echo $success_text_color ?>;}

.allfields_style, .allfields_active_style, .allfields_error_style, .submitbutton_style{width:auto;}

.ar_main_div_<?php echo $form_id; ?> .trigger_style span{float:left;}

.ar_main_div_<?php echo $form_id; ?> .arfloadingimg{background:url(<?php echo ARFIMAGESURL ?>/ajax_loader.gif) no-repeat center center;padding:6px 12px;}

.ar_main_div_<?php echo $form_id; ?> #ui-datepicker-div{display:none;z-index:999 !important;}

.ar_main_div_<?php echo $form_id; ?> .arfformfield{clear:both;}
.arfpreivewform .ar_main_div_<?php echo $form_id; ?> .arfformfield{clear:none;}

.ar_main_div_<?php echo $form_id; ?> #arf_message_success_popup,
.ar_main_div_<?php echo $form_id; ?> #arf_message_success {width:100%; display: inline-block; float:none; min-height:35px; margin: 0 0 15px 0; border-left:1px solid <?php echo $success_border_color; ?>; border-right:1px solid <?php echo $success_border_color; ?>; border-bottom:1px solid <?php echo $success_border_color; ?>; border-top:1px solid <?php echo $success_border_color; ?>; moz-border-radius:3px;  -webkit-border-radius:3px;-o-border-radius:3px; border-radius:3px; font-family:<?php echo stripslashes($newerror_font) ?>; background: <?php echo $success_bg_color; ?>; color:<?php echo $success_text_color; ?>; font-size:20px; }

.ar_main_div_<?php echo $form_id; ?> #message_success_preview {width:87%; display: block; float:none; min-height:35px; margin: 0 0 15px 0; border:1px solid <?php echo $success_border_color; ?>; moz-border-radius:3px;  -webkit-border-radius:3px; -o-border-radius:3px; border-radius:3px; font-family:<?php echo stripslashes($newerror_font) ?>; background: <?php echo $success_bg_color; ?>; }

.ar_main_div_<?php echo $form_id; ?> .msg-detail { float:left; width: 100%; padding:20px 10px 20px 10px; min-height: 37px; line-height: 37px; text-shadow: none; }

.ar_main_div_<?php echo $form_id; ?> .msg-detail p { padding:0 !important; margin:0 !important; }

.ar_main_div_<?php echo $form_id; ?> .msg-detail::before {
    content: "";
    <?php $success_text_color_svg = str_replace("##", "#", $success_text_color);?>
    background-image: url(data:image/svg+xml;base64,<?php echo base64_encode('<svg width="60" height="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 52 52" enable-background="new 0 0 52 52" xml:space="preserve"><g><path fill="'.$success_text_color_svg.'" d="M26,0C11.66,0,0,11.66,0,26s11.66,26,26,26s26-11.66,26-26S40.34,0,26,0z M26,50C12.77,50,2,39.23,2,26   S12.77,2,26,2s24,10.77,24,24S39.23,50,26,50z"/><path fill="'.$success_text_color_svg.'" d="M38.25,15.34L22.88,32.63l-9.26-7.41c-0.43-0.34-1.06-0.27-1.41,0.16c-0.35,0.43-0.28,1.06,0.16,1.41l10,8   C22.56,34.93,22.78,35,23,35c0.28,0,0.55-0.11,0.75-0.34l16-18c0.37-0.41,0.33-1.04-0.08-1.41C39.25,14.88,38.62,14.92,38.25,15.34   z"/></g></svg>');?>);

    width: 60px;
    height: 60px;
    display: block;
    margin: 0 auto;
    background-repeat: no-repeat;
    position:relative;
}

<?php $msg_font_size = '20px';?>

.ar_main_div_<?php echo $form_id; ?> .msg-title-success { padding:0px 0 0 10px; vertical-align:middle; display:inline-block; font-weight:bold; }

.ar_main_div_<?php echo $form_id; ?> .msg-description-success { letter-spacing:0.1px; padding:10px 0 10px 0px; width:100%; vertical-align:middle; display:inline-block; }

.ar_main_div_<?php echo $form_id; ?> .msg-title-error { padding:5px 0 0 10px; vertical-align:middle; display:inline-block; }

.ar_main_div_<?php echo $form_id; ?> .msg-description-error { padding:7px 0 0 10px; letter-spacing:0.1px; vertical-align:middle; display:inline; }

.ar_main_div_<?php echo $form_id; ?> .arf_res_front_msg_desc { padding:10px 0 10px 0px; letter-spacing:0.1px; width:100%; vertical-align:middle; display:inline-block; text-align:center; }

.ar_main_div_<?php echo $form_id; ?> .frm_error_style { 
    width:100%; 
    display: inline-block; 
    float:none; 
    min-height:35px; 
    margin: 0 0 10px 0;
    border: 1px solid <?php echo $error_border_color; ?> !important;
    background: <?php echo $error_bg_color; ?> !important; 
    color:<?php echo $error_txt_color; ?>;
    font-family:<?php echo stripslashes($newerror_font) ?>; 
    font-weight:normal; 
    -moz-border-radius:3px;  
    -webkit-border-radius:3px; 
    -o-border-radius:3px; 
    border-radius:3px;
    font-size:20px; 
}
.ar_main_div_<?php echo $form_id; ?> .frm_error_style .msg-detail::before {
    content: "";
    <?php $validation_textcolor_svg = str_replace("##", "#", $error_txt_color);?>
    background-image: url(data:image/svg+xml;base64,<?php echo base64_encode('<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0" y="0" viewBox="10 10 100 100" enable-background="new 10 10 100 100" xml:space="preserve" height="60" width="60"><g><circle fill="none" stroke="'.$validation_textcolor_svg.'" stroke-width="4" stroke-miterlimit="10" cx="60" cy="60" r="47"></circle><line fill="none" stroke="'.$validation_textcolor_svg.'" stroke-width="4" stroke-miterlimit="10" x1="81.214" y1="81.213" x2="38.787" y2="38.787"></line><line fill="none" stroke="'.$validation_textcolor_svg.'" stroke-width="4" stroke-miterlimit="10" x1="38.787" y1="81.213" x2="81.214" y2="38.787"></line></g></svg>'); ?>);
    width: 60px;
    height: 60px;
    display: block;
    margin: 0 auto;
    background-repeat: no-repeat;
    position:relative;
}

.ar_main_div_<?php echo $form_id; ?> .frm_error_style_preview { width:87%; display: block; float:none; height:35px; margin: 0 0 10px 0; border:1px solid <?php echo $error_border; ?>; -moz-border-radius:3px;   -webkit-border-radius:3px; -o-border-radius:3px; border-radius:3px; font-family:<?php echo stripslashes($newerror_font) ?>; background: <?php echo $error_bg; ?>; }

.ar_main_div_<?php echo $form_id; ?> #recaptcha_table { line-height:0 !important; height: 123px; }	

.wp-admin .ar_main_div_<?php echo $form_id; ?> label.arf_main_label{text-align:<?php echo $align ?>;}

<?php if ($form_align == 'center' || $form_align == 'right') { ?>.wp-admin .ar_main_div_<?php echo $form_id; ?> .right_container .arf_radiobutton {margin<?php echo ($radio_align == 'block') ? "-right:{$label_margin}px;" : ':0'; ?>}<?php } ?>

.ar_main_div_<?php echo $form_id; ?> .original{ opacity: 0; position: relative; z-index: 100;<?php echo ($field_width == '') ? 'auto' : ($field_width) ?>; }

.ar_main_div_<?php echo $form_id; ?> .bootstrap-select .arfdropdown-menu > li > a:hover,
.ar_main_div_<?php echo $form_id; ?> .bootstrap-select .arfdropdown-menu > li > a:focus {
text-decoration: none;
color: #ffffff !important;
background-color: <?php echo $base_color ?> !important;
outline:none;
}
.ar_main_div_<?php echo $form_id; ?> .arf_confirmation_summary_title,
.ar_main_div_<?php echo $form_id; ?> .formtitle_style { padding:0; color:<?php echo $form_title_color; ?>; font-family:<?php echo stripslashes($arf_title_font_family) ?>; text-align:<?php echo $arfformtitlealign; ?>; font-size:<?php echo $form_title_font_size; ?>; font-weight:<?php echo $form_title_weight; ?>; <?php echo $form_title_weight_font_style; ?> }
.ar_main_div_<?php echo $form_id; ?> .arftitlecontainer, .ar_main_div_<?php echo $form_id; ?> .allfields .arftitlediv { margin:<?php echo $form_title_padding; ?>; }
.ar_main_div_<?php echo $form_id; ?> #titlediv .arftitlecontainer { margin:0px; }

<?php
$width_loader = ($submit_width_loader / 2);
?>

.ar_main_div_<?php echo $form_id; ?> .arf_submit_div.left_container { clear:both; text-align:<?php echo $submit_align; ?>; }
.ar_main_div_<?php echo $form_id; ?> .arf_submit_div.right_container { clear:both; text-align:<?php echo $submit_align; ?>; }
.ar_main_div_<?php echo $form_id; ?> .arf_submit_div.top_container,
.ar_main_div_<?php echo $form_id; ?> .arf_submit_div.none_container { clear:both; text-align:<?php echo $submit_align; ?>; }

.ar_main_div_<?php echo $form_id; ?> .arfsubmitbutton:not(.arfsubmitedit):not(.arf_confirmation_summary_submit_wrapper){float:left;width:100%;}
.ar_main_div_<?php echo $form_id; ?> .arf_confirmation_summary_submit_wrapper{text-align:<?php echo $submit_align; ?>; }

.ar_main_div_<?php echo $form_id; ?> .arf_submit_div div.hexagon { margin: <?php echo $submit_margin; ?> }

.ar_main_div_<?php echo $form_id; ?> #brand-div { font-size: 10px !important; color: #444444 !important; }
.ar_main_div_<?php echo $form_id; ?> #brand-div.left_container { text-align:<?php echo $submit_align; ?>; }
.ar_main_div_<?php echo $form_id; ?> #brand-div.right_container { text-align:<?php echo $submit_align; ?>; }
.ar_main_div_<?php echo $form_id; ?> #brand-div.top_container { text-align:<?php echo $submit_align; ?>; }

.ar_main_div_<?php echo $form_id; ?> #hex.left_container { text-align:center; }
.ar_main_div_<?php echo $form_id; ?> #hex.right_container { text-align:center; }
.ar_main_div_<?php echo $form_id; ?> #hex.top_container,
.ar_main_div_<?php echo $form_id; ?> #hex.none_container { text-align:center; }


.ar_main_div_<?php echo $form_id; ?> .arf_submit_div{
    margin:<?php echo isset($submit_margin) ? $submit_margin : ''; ?>;
}

.ar_main_div_<?php echo $form_id; ?> #hexacenter.left_container { margin-left:<?php echo ($label_margin + $width_loader - 10) . 'px'; ?>; }
.ar_main_div_<?php echo $form_id; ?> #hexacenter.right_container { margin-left:<?php echo (40 + $width_loader) . 'px'; ?>; }
.ar_main_div_<?php echo $form_id; ?> #hexacenter.top_container { margin-left:<?php echo (40 + $width_loader) . 'px'; ?>; }

.ar_main_div_<?php echo $form_id; ?> #recaptcha_style { display:inline-block; max-width:100%; }
.ar_main_div_<?php echo $form_id; ?> #recaptcha_style .help-block { margin-left:0px; }

.ar_main_div_<?php echo $form_id; ?> .recaptcha_style_custom .help-block { margin-left:0px; }

.ar_main_div_<?php echo $form_id; ?> div.help-block, .ar_main_div_<?php echo $form_id; ?> div.arf_field_description { clear:both; }

.ar_main_div_<?php echo $form_id; ?> div.formdescription_style { padding:0; text-align:<?php echo $arfformtitlealign; ?>; width:auto; color:<?php echo $form_title_color; ?>; font-family:<?php echo stripslashes($arf_title_font_family) ?>; } 

.ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning .control-label,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning .help-block,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning .help-inline {
color: <?php echo ($arfvalidationerrorstyle == 'normal') ? $validation_bgcolor : $validation_textcolor; ?> !important;
font-family:<?php echo stripslashes($newerror_font) ?>;
font-size:<?php echo $error_font_size ?>;
}

.ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning .checkbox,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning .radio,

.ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor),
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning input[type=password],
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning input[type=email],
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning input[type=number],
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning input[type=url],
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning input[type=tel],
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning select,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning textarea {
    color: <?php echo $border_color_error; ?> !important;
    background-color:<?php echo $bg_color_error ?> !important;
    border-color: <?php echo $border_color_error; ?> !important;
}


.ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):focus,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning input[type=password]:focus,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning input[type=email]:focus,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning input[type=number]:focus,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning input[type=url]:focus,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning input[type=tel]:focus,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning select:focus,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning textarea:focus{
border-color: <?php echo $border_color_error; ?> !important;
<?php if ($arfmainfield_opacity == 1) { ?>
        background-color:transparent !important;
    <?php } else { ?>
        background-color:<?php echo $bg_color_error ?> !important;
    <?php } ?>
-moz-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($border_color_error); ?>, 0.4) ;
-webkit-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($border_color_error); ?>, 0.4) !important;
-o-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($border_color_error); ?>, 0.4) !important;
box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($border_color_error); ?>, 0.4) !important;
}

.ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning .input-prepend .add-on,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning .input-append .add-on {
color: <?php echo $border_color_error; ?> !important;
background-color:<?php echo $bg_color_error ?> !important;
border-color: <?php echo $border_color_error; ?> !important;
-webkit-box-shadow:none;
-o-box-shadow:none;
-moz-box-shadow:none;
box-shadow:none;
}

.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .control-label,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .help-block,
.ar_main_div_<?php echo $form_id; ?> .control-group .arf_error .help-block,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .help-inline {
color: <?php echo ($arfvalidationerrorstyle == 'normal') ? $validation_bgcolor : $validation_textcolor; ?> !important;
font-family:<?php echo stripslashes($newerror_font) ?>;
font-size:<?php echo $error_font_size ?>;
}
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error.arf_success .control-label,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error.arf_success .help-block,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_success .arf_error .help-block,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error.arf_success .help-inline {
color: <?php echo ($arfvalidationerrorstyle == 'normal') ? $validation_bgcolor : $validation_textcolor; ?> !important;
font-family:<?php echo stripslashes($newerror_font) ?>;
font-size:<?php echo $error_font_size ?>;
}

.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .checkbox,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .radio,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error input,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error select,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error textarea {
color: <?php echo $border_color_error; ?> !important;
background-color:<?php echo $bg_color_error ?> !important;
-webkit-box-shadow:none;
-o-box-shadow:none;
-moz-box-shadow:none;
box-shadow:none;
}


.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error.arf_success .wp-core-ui.wp-editor-wrap textarea {
border-color: <?php echo $border_color_error; ?> !important;
-webkit-box-shadow:none;
-o-box-shadow:none;
-moz-box-shadow:none;
box-shadow:none;
}

.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error input,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error input[type=text]:not(.inplace_field):not(.arf_field_option_input_text),
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error select,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error textarea,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error #file-button1,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .arf_prefix,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .arf_prefix.arf_prefix_focus,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .arf_suffix,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .arf_suffix.arf_suffix_focus
{
border-color: <?php echo $border_color_error; ?> !important;
-webkit-box-shadow:none;
-o-box-shadow:none;
-moz-box-shadow:none;
box-shadow:none;
}

.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error input:focus,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error select:focus,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error textarea:focus {
border-color: <?php echo $border_color_error; ?> !important;
background-color:<?php echo $bg_color_error ?> !important;
-moz-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($border_color_error); ?>, 0.4);
-webkit-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($border_color_error); ?>, 0.4);
-o-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($border_color_error); ?>, 0.4);
box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($border_color_error); ?>, 0.4);
}

.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .input-prepend .add-on,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .input-append .add-on {
color: <?php echo $border_color_error; ?> !important;
background-color:<?php echo $bg_color_error ?> !important;
border-color: <?php echo $border_color_error; ?> !important;
-webkit-box-shadow:none;
-o-box-shadow:none;
-moz-box-shadow:none;
box-shadow:none;
}

.ar_main_div_<?php echo $form_id; ?> .control-group.arf_success .control-label,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_success .help-block,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_success .help-inline {
color: <?php echo $text_color ?> !important;
}

.ar_main_div_<?php echo $form_id; ?> .control-group.arf_success .checkbox,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_success .radio,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_success .controls input,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_success select,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_success textarea {
color: <?php echo $text_color ?> !important;
}

.ar_main_div_<?php echo $form_id; ?> .control-group.arf_success .controls input,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_success select,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_success textarea,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_success #file-button1 {
border-color: <?php echo $border_color; ?> !important;
-webkit-box-shadow:none;
-o-box-shadow:none;
-moz-box-shadow:none;
box-shadow:none;
}

.ar_main_div_<?php echo $form_id; ?> .control-group.arf_success .controls input:focus,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_success select:focus,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_success textarea:focus {
border-color: <?php echo $base_color ?> !important;
-moz-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($base_color); ?>, 0.4);
-webkit-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($base_color); ?>, 0.4);
-o-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($base_color); ?>, 0.4);
box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($base_color); ?>, 0.4);
}

.ar_main_div_<?php echo $form_id; ?> .control-group.arf_success .input-prepend .add-on,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_success .input-append .add-on {
color: <?php echo $text_color ?> !important;
background-color: #dff0d8;
border-color: <?php echo $base_color ?> !important;
-webkit-box-shadow:none;
-o-box-shadow:none;
-moz-box-shadow:none;
box-shadow:none;
}
.help-block ul
{
margin:0 !important;
}
.help-block li
{
list-style:none;
line-height:15px;	
}

.ar_main_div_<?php echo $form_id; ?> .left_container .setting_radio .help-block{margin-left:0px;}
.ar_main_div_<?php echo $form_id; ?> .left_container .setting_checkbox .help-block{margin-left:0px;}

.success { background:none !important; border:0px; }
#ui-datepicker-div { display:none; }

.ar_main_div_<?php echo $form_id; ?> #hexagon img { -webkit-box-sizing: content-box; -moz-box-sizing: content-box; box-sizing: content-box; -o-box-sizing: content-box; }

.ar_main_div_<?php echo $form_id; ?> .page_break_nav
{	
font-size:16px;
padding:15px 7px;
margin:3px 1px 3px 1px;
background:<?php echo $field_bg_inactive_color_pg_break; ?>;
color: <?php echo $field_text_color_pg_break; ?>;

text-align:center;
font-weight:bold;
line-height: 20px;
max-width:10%;
verticle-align:middle;
}
.ar_main_div_<?php echo $form_id; ?> .page_nav_selected
{	
background:<?php echo $field_bg_color_pg_break; ?>;    
}
.ar_main_div_<?php echo $form_id; ?> .allfields .arf_wizard {
border:1px solid <?php echo $field_bg_inactive_color_pg_break; ?>;
margin:3px 1% 35px 1%;
width:98%;
box-shadow: 0px 1px 1px rgba(0, 0, 0, 0.3);
-webkit-box-shadow: 0px 1px 1px rgba(0, 0, 0, 0.3), 0 0px 0px rgba(0, 0, 0, 0) inset;
-o-box-shadow: 0px 1px 1px rgba(0, 0, 0, 0.3), 0 0px 0px rgba(0, 0, 0, 0) inset;
-moz-box-shadow: 0px 1px 1px rgba(0, 0, 0, 0.3), 0 0px 0px rgba(0, 0, 0, 0) inset;
-moz-border-radius:1px;
-webkit-border-radius:1px;
-o-border-radius:1px;
border-radius:1px;
}
.ar_main_div_<?php echo $form_id; ?> .arf_wizard {
border:1px solid <?php echo $field_bg_inactive_color_pg_break; ?>;
margin:3px 1% 10px 1%;
width:98%;
-moz-border-radius:1px;
-webkit-border-radius:1px;
-o-border-radius:1px;
border-radius:1px;
}
.ar_main_div_<?php echo $form_id; ?> .arf_wizard td{
border:0px;
padding:15px 5px;
vertical-align:middle;
}
.ar_main_div_<?php echo $form_id; ?> .arf_current_tab_arrow
{	
border-left: 12px solid rgba(0, 0, 0, 0) !important;
border-right: 12px solid rgba(0, 0, 0, 0) !important;
border-top: 9px solid <?php echo $field_bg_color_pg_break; ?> !important;
height: 0;
margin: auto auto -9px !important;
width: 0;
}
.ar_main_div_<?php echo $form_id; ?> .arf_wizard.bottom .arf_current_tab_arrow{
    border-bottom: 9px solid <?php echo $field_bg_color_pg_break; ?> !important;
    border-top: 0 !important;
}
.ar_main_div_<?php echo $form_id; ?> .page_break_nav
{	
border-right:1px solid rgba(255,255,255,0.7) !important;
}
.ar_main_div_<?php echo $form_id; ?> .page_nav_selected,
.ar_main_div_<?php echo $form_id; ?> .arf_page_prev,
.ar_main_div_<?php echo $form_id; ?> .arf_page_last
{
border-right:none !important;    
}

.ar_main_div_<?php echo $form_id; ?> .controls .rating { visibility:hidden; height: 0; padding: 0; width: 0; }

.ar_main_div_<?php echo $form_id; ?> #hexagon {
width: <?php echo $submit_height_hex; ?>px;	
height: <?php echo $submit_height_hex; ?>px;	
border-radius: 50%;
-webkit-border-radius: 50%;
-o-border-radius: 50%;
-moz-border-radius: 50%;
background:<?php echo $submit_bg_color; ?>;	
}

#content .ar_main_div_<?php echo $form_id; ?> div.arfsubmitbutton .previous_btn, .ar_main_div_<?php echo $form_id; ?> div.arfsubmitbutton .previous_btn { font-weight:<?php echo $submit_weight ?>; <?php echo $submit_weight_font_style; ?> }

#popup-form-<?php echo $form_id; ?>.arfmodal .arfmodal-header { border-bottom: none;} 

<?php if ($arfmainfield_opacity == 1) { ?>
    .ar_main_div_<?php echo $form_id; ?> input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider), 
    .ar_main_div_<?php echo $form_id; ?> input[type=password], 
    .ar_main_div_<?php echo $form_id; ?> input[type=email], 
    .ar_main_div_<?php echo $form_id; ?> input[type=number], 
    .ar_main_div_<?php echo $form_id; ?> input[type=url], 
    .ar_main_div_<?php echo $form_id; ?> input[type=tel], 
    .ar_main_div_<?php echo $form_id; ?> textarea,
    .ar_main_div_<?php echo $form_id; ?> .controls textarea,
    .ar_main_div_<?php echo $form_id; ?> input:focus:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider),
    .ar_main_div_<?php echo $form_id; ?> input:focus,    
    .ar_main_div_<?php echo $form_id; ?> input[type=text]:focus:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete):not(.arfslider), 
    .ar_main_div_<?php echo $form_id; ?> input[type=password]:focus, 
    .ar_main_div_<?php echo $form_id; ?> input[type=email]:focus, 
    .ar_main_div_<?php echo $form_id; ?> input[type=number]:focus, 
    .ar_main_div_<?php echo $form_id; ?> input[type=url]:focus, 
    .ar_main_div_<?php echo $form_id; ?> input[type=tel]:focus, 
    .ar_main_div_<?php echo $form_id; ?> textarea:focus,
    .ar_main_div_<?php echo $form_id; ?> .controls textarea:focus,
    .ar_main_div_<?php echo $form_id; ?> .sltstandard_front .btn-group .arfbtn.dropdown-toggle,
    .ar_main_div_<?php echo $form_id; ?> .sltstandard_time .btn-group .arfbtn.dropdown-toggle,
    .ar_main_div_<?php echo $form_id; ?> .sltstandard_front .btn-group.open .arfbtn.dropdown-toggle,
    .ar_main_div_<?php echo $form_id; ?> .sltstandard_time .btn-group.open .arfbtn.dropdown-toggle,
    .ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_front .btn-group .arfbtn.dropdown-toggle,
    .ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_time .btn-group .arfbtn.dropdown-toggle,
    .ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_front .btn-group.open .arfdropdown-menu,
    .ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_time .btn-group.open .arfdropdown-menu,
    .ar_main_div_<?php echo $form_id; ?> .sltstandard_front .btn-group .arfbtn.dropdown-toggle:focus,
    .ar_main_div_<?php echo $form_id; ?> .sltstandard_time .btn-group .arfbtn.dropdown-toggle:focus,
    .ar_main_div_<?php echo $form_id; ?> .sltstandard_front .btn-group.open .arfdropdown-menu,
    .ar_main_div_<?php echo $form_id; ?> .sltstandard_time .btn-group.open .arfdropdown-menu,
    .ar_main_div_<?php echo $form_id; ?> .sltstandard_front .btn-group.open .arfdropdown-menu:focus,
    .ar_main_div_<?php echo $form_id; ?> .sltstandard_time .btn-group.open .arfdropdown-menu:focus { background-color: transparent !important; }

    .ar_main_div_<?php echo $form_id; ?> .sltstandard_front .btn-group.open .arfdropdown-menu{ background:#ffffff !important; }

    .ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning .checkbox,
    .ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning .radio,
    
    .ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor),
    .ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning input[type=password],
    .ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning input[type=email],
    .ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning input[type=number],
    .ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning input[type=url],
    .ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning input[type=tel],

    .ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning input[type=text]:not(.inplace_field):not(.arf_field_option_input_text):not(.arfslider):not(.arf_colorpicker):not(.arfhiddencolor):focus,
    .ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning input[type=password]:focus,
    .ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning input[type=email]:focus,
    .ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning input[type=number]:focus,
    .ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning input[type=url]:focus,
    .ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning input[type=tel]:focus,

    .ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning select,
    .ar_main_div_<?php echo $form_id; ?> .control-group.arf_warning textarea,
    .ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .checkbox,
    .ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .radio,
    .ar_main_div_<?php echo $form_id; ?> .control-group.arf_error input,
    .ar_main_div_<?php echo $form_id; ?> .control-group.arf_error input:focus,
    .ar_main_div_<?php echo $form_id; ?> .control-group.arf_error select,
    .ar_main_div_<?php echo $form_id; ?> .control-group.arf_error textarea,
    .ar_main_div_<?php echo $form_id; ?> .control-group.arf_error textarea:focus {
    background-color: transparent !important;
    }
<?php } else { ?>


    .ar_main_div_<?php echo $form_id; ?> .sltstandard_front .btn-group.open .arfbtn.dropdown-toggle,
    .ar_main_div_<?php echo $form_id; ?> .sltstandard_time .btn-group.open .arfbtn.dropdown-toggle,
    .ar_main_div_<?php echo $form_id; ?> .sltstandard_front .btn-group.open .arfdropdown-menu,
    .ar_main_div_<?php echo $form_id; ?> .sltstandard_time .btn-group.open .arfdropdown-menu { background-color: <?php echo $bg_color; ?> !important; }

    .ar_main_div_<?php echo $form_id; ?> .sltstandard_front .btn-group.open .arfdropdown-menu:focus,
    .ar_main_div_<?php echo $form_id; ?> .sltstandard_time .btn-group.open .arfdropdown-menu:focus { background-color: <?php echo $bg_color_active; ?> !important; }
<?php } ?>
.ar_main_div_<?php echo $form_id; ?> span.arfcheckrequiredfield { color:<?php echo $label_color ?> !important; font-style: normal; font-weight: normal; }
.ar_main_div_<?php echo $form_id; ?> h2.pos_left, .ar_main_div_<?php echo $form_id; ?> h2.pos_top, .ar_main_div_<?php echo $form_id; ?> h2.pos_right { color:<?php echo $label_color ?>; }

.ar_main_div_<?php echo $form_id; ?> input:not([type=submit], [type=button]) { margin:0 !important; }
.arfmodal-body { max-height:1000px; }

.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_front .btn-group .arfdropdown-menu,
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .sltstandard_time .btn-group .arfdropdown-menu { background-color: <?php echo $bg_color_error; ?> !important; }

.ar_main_div_<?php echo $form_id; ?> .file_main_control { 
    width: <?php if ($field_width_unit == 'px' && $field_width != '' && $field_width != 'auto') {
        echo $field_width;
    } else {
        echo $field_width;
    } ?>
}
.ar_main_div_<?php echo $form_id; ?> .arf_file_field { width: 100% }

.ar_main_div_<?php echo $form_id; ?> .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_border{
    background:transparent <?php if (!empty($submit_bg_img)) { ?> url(<?php echo $submit_bg_img; ?>)<?php } ?> !important;
    color:<?php echo $submit_bg_color; ?> !important;
    border:<?php echo ($submit_border_width > 0) ? $submit_border_width : '2px'; ?> solid <?php echo $submit_bg_color ?>;
}
.ar_main_div_<?php echo $form_id; ?> .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_flat{
    background:<?php echo $submit_bg_color ?> <?php if (!empty($submit_bg_img)) { ?> url(<?php echo $submit_bg_img; ?>)<?php } ?>;
    color:<?php echo $submit_text_color; ?> !important;
    border:<?php echo $submit_border_width ?> solid <?php echo $submit_bg_color; ?>;
    -moz-box-shadow:1px 2px 3px <?php echo $submit_shadow_color; ?>;
    -webkit-box-shadow:1px 2px 3px <?php echo $submit_shadow_color; ?>;
    -o-box-shadow:1px 2px 3px <?php echo $submit_shadow_color; ?>;
    box-shadow:1px 2px 3px <?php echo $submit_shadow_color; ?>;
}
.ar_main_div_<?php echo $form_id; ?> .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn.arf_submit_btn_reverse_border{
    background:<?php echo $submit_bg_color ?> <?php if (!empty($submit_bg_img)) { ?> url(<?php echo $submit_bg_img; ?>)<?php } ?>;
    color:<?php echo $submit_text_color; ?> !important;
    border-width:<?php echo ($submit_border_width > 0) ? $submit_border_width : '2px'; ?>;
    border-color: <?php echo $submit_bg_color ?>;
}

.ar_main_div_<?php echo $form_id; ?> .arfsubmitbutton .arf_submit_btn {
height:<?php echo $submit_height ?>;
<?php
    if( trim($submit_width) == '' ){
        echo "min-width:".$submit_auto_width."px;";
    } else {
        echo "width:".$submit_width.";";
    }
?>
max-width:100%;
display:inline-block;
font-weight:<?php echo $submit_weight; ?>;
font-family:<?php echo stripslashes($arfsubmitfontfamily) ?>;
font-size:<?php echo $submit_font_size; ?>;
<?php echo $submit_weight_font_style; ?> 
cursor:pointer;
outline:none;
line-height:1.3;
<?php if($arfsubmitbuttonstyle == 'border') { ?>
    background:transparent <?php if (!empty($submit_bg_img)) { ?> url(<?php echo $submit_bg_img; ?>)<?php } ?>;
    color:<?php echo $submit_bg_color; ?> !important;
    border:<?php echo ($submit_border_width > 0) ? $submit_border_width : '2px'; ?> solid <?php echo $submit_bg_color ?>;
<?php } else if($arfsubmitbuttonstyle == 'reverse border') { ?>
    background:<?php echo $submit_bg_color ?> <?php if (!empty($submit_bg_img)) { ?> url(<?php echo $submit_bg_img; ?>)<?php } ?>;
    color:<?php echo $submit_text_color; ?> !important;
    border:<?php echo ($submit_border_width > 0) ? $submit_border_width : '2px'; ?> solid <?php echo $submit_bg_color ?>;
<?php } else { ?>
    background:<?php echo $submit_bg_color ?> <?php if (!empty($submit_bg_img)) { ?> url(<?php echo $submit_bg_img; ?>)<?php } ?>;
    color:<?php echo $submit_text_color; ?> !important;
    border:<?php echo $submit_border_width ?> solid <?php echo $submit_border_color ?>;
    -moz-box-shadow:1px 2px 3px <?php echo $submit_shadow_color; ?>;
    -webkit-box-shadow:1px 2px 3px <?php echo $submit_shadow_color; ?>;
    -o-box-shadow:1px 2px 3px <?php echo $submit_shadow_color; ?>;
    box-shadow:1px 2px 3px <?php echo $submit_shadow_color; ?>;
<?php } ?>
background-position: left top;	
filter: progid:DXImageTransform.Microsoft.gradient(enabled = false);      
-ms-filter:"progid:DXImageTransform.Microsoft.Shadow(Strength=3, Direction=135, Color='<?php echo $submit_shadow_color; ?>')";
filter:progid:DXImageTransform.Microsoft.Shadow(Strength=3, Direction=135, Color='<?php echo $submit_shadow_color; ?>'); 

padding:0 10px;
vertical-align:top;
text-transform: none;
text-shadow:none;
-moz-box-sizing:content-box;
-ms-box-sizing:content-box;
-webkit-box-sizing:content-box;
-o-box-sizing:content-box;
box-sizing:content-box;

transition: .2s ease-out;
-webkit-transition: .2s ease-out;
-moz-transition: .2s ease-out;
-ms-transition: .2s ease-out;
-o-transition: .2s ease-out;


-moz-border-radius:<?php echo $submit_border_radius ?>;
-webkit-border-radius:<?php echo $submit_border_radius ?>;
-o-border-radius:<?php echo $submit_border_radius ?>;
border-radius:<?php echo $submit_border_radius ?>;


position:relative;
}

.ar_main_div_<?php echo $form_id; ?> .arfsubmitbutton .arf_greensave_button_wrapper {
    <?php 
    if (substr($submit_margin, 0, 1) === '-') {
        $submit_margin_exp = explode('px', $submit_margin);
        $submit_margin_exp_top = isset($submit_margin_exp[0]) ? $submit_margin_exp[0] : '';
        if($submit_margin_exp_top<0) {
    ?>
        top:<?php echo $submit_margin_exp_top.'px';?>;
    <?php
        }
    }
    ?>
}
.ar_main_div_<?php echo $form_id; ?> .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn {
    margin:10px 0 0; 
}

.ar_main_div_<?php echo $form_id; ?> .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_border{
    <?php if (!empty($submit_hover_bg_img) && !empty($submit_bg_img)) { ?>
        background-image:url(<?php echo $submit_hover_bg_img; ?>) !important;
        color:<?php echo $submit_text_color ?> !important;
        background-color:<?php echo $submit_bg_color_hover; ?> !important;
        border:<?php echo ($submit_border_width > 0) ? $submit_border_width : '2px'; ?> solid <?php echo $submit_bg_color_hover ?>;        
    <?php } else { ?>
        background-image:none !important;
        color:<?php echo $submit_text_color ?> !important;
        background-color:<?php echo $submit_bg_color_hover; ?> !important;
        border:<?php echo ($submit_border_width > 0) ? $submit_border_width : '2px'; ?> solid <?php echo $submit_bg_color_hover ?>;        
    <?php } ?>
}
.ar_main_div_<?php echo $form_id; ?> .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_reverse_border{
    <?php if (!empty($submit_hover_bg_img) && !empty($submit_bg_img)) { ?>
        background-image:url(<?php echo $submit_hover_bg_img; ?>) !important;
        background-color:transparent !important;
        color:<?php echo $submit_bg_color_hover ?> !important;
        border:<?php echo ($submit_border_width > 0) ? $submit_border_width : '2px'; ?> solid <?php echo $submit_bg_color_hover ?>;
    <?php } else { ?>
        background-image:none !important;
        background-color:transparent !important;
        color:<?php echo $submit_bg_color_hover ?> !important;
        border:<?php echo ($submit_border_width > 0) ? $submit_border_width : '2px'; ?> solid <?php echo $submit_bg_color_hover ?>;      
    <?php } ?>
}
.ar_main_div_<?php echo $form_id; ?> .arfsubmitbutton .arf_greensave_button_wrapper .arf_submit_btn:hover.arf_submit_btn_flat{
    <?php if (!empty($submit_hover_bg_img) && !empty($submit_bg_img)) { ?>
        background-image:url(<?php echo $submit_hover_bg_img; ?>) !important;
        background-color:<?php echo $submit_bg_color_hover ?> !important;
    <?php } else { ?>
        background-image:none !important;
        background-color:<?php echo $submit_bg_color_hover ?> !important;
    <?php } ?>
}

.ar_main_div_<?php echo $form_id; ?> .arf_submit_btn.arf_active_loader,
.ar_main_div_<?php echo $form_id; ?> .arf_submit_btn.arf_complete_loader,
.ar_main_div_<?php echo $form_id; ?> .arf_submit_btn:hover,
.ar_main_div_<?php echo $form_id; ?> .arf_submit_btn:focus{
<?php if (!empty($submit_hover_bg_img) && !empty($submit_bg_img)) { ?>
    background-image:url(<?php echo $submit_hover_bg_img; ?>) !important;
    <?php if($arfsubmitbuttonstyle == 'border') { ?>
        color:<?php echo $submit_text_color ?> !important;
        background-color:<?php echo $submit_bg_color_hover; ?> !important;
        border:<?php echo ($submit_border_width > 0) ? $submit_border_width : '2px'; ?> solid <?php echo $submit_bg_color_hover ?>;        
    <?php } else if($arfsubmitbuttonstyle == 'reverse border') { ?>
        color:<?php echo $submit_bg_color_hover ?> !important;
        border:<?php echo ($submit_border_width > 0) ? $submit_border_width : '2px'; ?> solid <?php echo $submit_bg_color_hover ?>;
    <?php } else { ?>
        background-color:<?php echo $submit_bg_color_hover ?> !important;
    <?php } ?>
<?php } else { ?>
    background-image:none !important;
    <?php if($arfsubmitbuttonstyle == 'border') { ?>
        color:<?php echo $submit_text_color ?> !important;
        background-color:<?php echo $submit_bg_color_hover; ?> !important;
        border:<?php echo ($submit_border_width > 0) ? $submit_border_width : '2px'; ?> solid <?php echo $submit_bg_color_hover ?>;        
    <?php } else if($arfsubmitbuttonstyle == 'reverse border') { ?>
        background:transparent !important;
        color:<?php echo $submit_bg_color_hover ?> !important;
        border:<?php echo ($submit_border_width > 0) ? $submit_border_width : '2px'; ?> solid <?php echo $submit_bg_color_hover ?>;
    <?php } else { ?>
        background-color:<?php echo $submit_bg_color_hover ?> !important;
    <?php } ?>
<?php } ?>
}

<?php
$submit_height_wpx = ( $submit_height_wpx == '' ) ? '35' : $submit_height_wpx;
$submit_width_wpx = ( $submit_width_wpx == '' ) ? '150' : $submit_width_wpx;

if ($submit_height_wpx < 25) {
    $logo_margin = '-8px';
    $logo_p_margin = '9px';
    $spinner_margin = '40px';

    $submit_width_d2 = ($submit_width_wpx / 2);

    if ($submit_font_size_wpx > 32 and $submit_font_size_wpx <= 40) {
        $spinner_margin_top = '-14px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 10);
    } else if ($submit_font_size_wpx > 24 and $submit_font_size_wpx <= 32) {
        $spinner_margin_top = '-13px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 10);
    } else if ($submit_font_size_wpx > 18 and $submit_font_size_wpx <= 24) {
        $spinner_margin_top = '-12px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 10);
    } else if ($submit_font_size_wpx < 17) {
        $spinner_margin_top = '-9px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 7);
    } else {
        $spinner_margin_top = '-10px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 10);
    }

    $perspective = '100px';
    $transform_origin = '4px';
    $b_width = '8px';
    $b_div_width = '8px';
    $translateX = '7px';
    $translateX_70 = '7px';
    $translateX_60 = '4px';
    $b_div_width_extra = '2px';
} else if ($submit_height_wpx < 35 and $submit_height_wpx >= 25) {
    $logo_margin = '-8px';
    $logo_p_margin = '9px';
    $spinner_margin = '40px';

    $submit_width_d2 = ($submit_width_wpx / 2);

    if ($submit_font_size_wpx > 32 and $submit_font_size_wpx <= 40) {
        $spinner_margin_top = '-14px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 10);
    } else if ($submit_font_size_wpx > 24 and $submit_font_size_wpx <= 32) {
        $spinner_margin_top = '-13px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 10);
    } else if ($submit_font_size_wpx > 18 and $submit_font_size_wpx <= 24) {
        $spinner_margin_top = '-12px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 10);
    } else if ($submit_font_size_wpx < 17) {
        $spinner_margin_top = '-9px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 7);
    } else {
        $spinner_margin_top = '-10px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 10);
    }

    $perspective = '100px';
    $transform_origin = '4px';
    $b_width = '8px';
    $b_div_width = '8px';
    $translateX = '7px';
    $translateX_70 = '7px';
    $translateX_60 = '4px';
    $b_div_width_extra = '2px';
} else if ($submit_height_wpx > 35 and $submit_height_wpx <= 49) {
    $logo_margin = '2px';
    $logo_p_margin = '18px';
    $spinner_margin = '40px';

    $submit_width_d2 = ($submit_width_wpx / 2);

    if ($submit_font_size_wpx > 32 and $submit_font_size_wpx <= 40) {
        $spinner_margin_top = '-14px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 6);
    } else if ($submit_font_size_wpx > 24 and $submit_font_size_wpx <= 32) {
        $spinner_margin_top = '-13px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 6);
    } else if ($submit_font_size_wpx > 18 and $submit_font_size_wpx <= 24) {
        $spinner_margin_top = '-12px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 6);
    } else if ($submit_font_size_wpx < 17) {
        $spinner_margin_top = '-9px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 4);
    } else {
        $spinner_margin_top = '-10px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 6);
    }

    $perspective = '100px';
    $transform_origin = '4px';
    $b_width = '8px';
    $b_div_width = '8px';
    $translateX = '7px';
    $translateX_70 = '7px';
    $translateX_60 = '4px';
    $b_div_width_extra = '2px';
} else if ($submit_height_wpx > 49 and $submit_height_wpx <= 60) {
    $logo_margin = '22px';
    $logo_p_margin = '24px';
    $spinner_margin = '40px';

    $submit_width_d2 = ($submit_width_wpx / 2);

    if ($submit_font_size_wpx > 32 and $submit_font_size_wpx <= 40) {
        $spinner_margin_top = '-17px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 6);
    } else if ($submit_font_size_wpx > 24 and $submit_font_size_wpx <= 32) {
        $spinner_margin_top = '-15px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 6);
    } else if ($submit_font_size_wpx > 18 and $submit_font_size_wpx <= 24) {
        $spinner_margin_top = '-14px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 6);
    } else if ($submit_font_size_wpx < 17) {
        $spinner_margin_top = '-11px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 4);
    } else {
        $spinner_margin_top = '-12px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 6);
    }

    $perspective = '150px';
    $transform_origin = '6px';
    $b_width = '12px';
    $b_div_width = '12px';
    $translateX = '14px';
    $translateX_70 = '14px';
    $translateX_60 = '8px';
    $b_div_width_extra = '2px';
} else if ($submit_height_wpx > 60 and $submit_height_wpx <= 70) {
    $logo_margin = '23px';
    $logo_p_margin = '23px';
    $spinner_margin = '50px';

    $submit_width_d2 = ($submit_width_wpx / 2);

    if ($submit_font_size_wpx > 32 and $submit_font_size_wpx <= 40) {
        $spinner_margin_top = '-19px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 8);
    } else if ($submit_font_size_wpx > 24 and $submit_font_size_wpx <= 32) {
        $spinner_margin_top = '-17px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 8);
    } else if ($submit_font_size_wpx > 18 and $submit_font_size_wpx <= 24) {
        $spinner_margin_top = '-16px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 8);
    } else if ($submit_font_size_wpx < 17) {
        $spinner_margin_top = '-13px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 6);
    } else {
        $spinner_margin_top = '-14px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 8);
    }

    $perspective = '200px';
    $transform_origin = '8px';
    $b_width = '16px';
    $b_div_width = '16px';
    $translateX = '21px';
    $translateX_70 = '21px';
    $translateX_60 = '12px';
    $b_div_width_extra = '3px';
} else if ($submit_height_wpx > 70 and $submit_height_wpx <= 80) {
    $logo_margin = '27px';
    $logo_p_margin = '27px';
    $spinner_margin = '60px';

    $submit_width_d2 = ($submit_width_wpx / 2);

    if ($submit_font_size_wpx > 32 and $submit_font_size_wpx <= 40) {
        $spinner_margin_top = '-19px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 11);
    } else if ($submit_font_size_wpx > 24 and $submit_font_size_wpx <= 32) {
        $spinner_margin_top = '-17px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 11);
    } else if ($submit_font_size_wpx > 18 and $submit_font_size_wpx <= 24) {
        $spinner_margin_top = '-16px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 11);
    } else if ($submit_font_size_wpx < 17) {
        $spinner_margin_top = '-13px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 9);
    } else {
        $spinner_margin_top = '-14px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 11);
    }

    $perspective = '150px';
    $transform_origin = '10px';
    $b_width = '18px';
    $b_div_width = '18px';
    $translateX = '26px';
    $translateX_70 = '26px';
    $translateX_60 = '14px';
    $b_div_width_extra = '3px';
} else if ($submit_height_wpx > 80 and $submit_height_wpx <= 90) {
    $logo_margin = '30px';
    $logo_p_margin = '29px';
    $spinner_margin = '70px';

    $submit_width_d2 = ($submit_width_wpx / 2);

    if ($submit_font_size_wpx > 32 and $submit_font_size_wpx <= 40) {
        $spinner_margin_top = '-21px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 13);
    } else if ($submit_font_size_wpx > 24 and $submit_font_size_wpx <= 32) {
        $spinner_margin_top = '-18px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 13);
    } else if ($submit_font_size_wpx > 18 and $submit_font_size_wpx <= 24) {
        $spinner_margin_top = '-17px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 13);
    } else if ($submit_font_size_wpx < 17) {
        $spinner_margin_top = '-15px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 11);
    } else {
        $spinner_margin_top = '-16px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 13);
    }

    $perspective = '150px';
    $transform_origin = '11px';
    $b_width = '20px';
    $b_div_width = '20px';
    $translateX = '30px';
    $translateX_70 = '30px';
    $translateX_60 = '15px';
    $b_div_width_extra = '4px';
} else if ($submit_height_wpx > 90 and $submit_height_wpx <= 100) {
    $logo_margin = '36px';
    $logo_p_margin = '36px';
    $spinner_margin = '80px';

    $submit_width_d2 = ($submit_width_wpx / 2);

    if ($submit_font_size_wpx > 32 and $submit_font_size_wpx <= 40) {
        $spinner_margin_top = '-23px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 17);
    } else if ($submit_font_size_wpx > 24 and $submit_font_size_wpx <= 32) {
        $spinner_margin_top = '-21px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 17);
    } else if ($submit_font_size_wpx > 18 and $submit_font_size_wpx <= 24) {
        $spinner_margin_top = '-20px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 17);
    } else if ($submit_font_size_wpx < 17) {
        $spinner_margin_top = '-17px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 15);
    } else {
        $spinner_margin_top = '-18px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 17);
    }

    $perspective = '200px';
    $transform_origin = '13px';
    $b_width = '22px';
    $b_div_width = '22px';
    $translateX = '35px';
    $translateX_70 = '35px';
    $translateX_60 = '17px';
    $b_div_width_extra = '4px';
} else if ($submit_height_wpx > 100) {
    $logo_margin = '38px';
    $logo_p_margin = '38px';
    $spinner_margin = '90px';

    $submit_width_d2 = ($submit_width_wpx / 2);

    if ($submit_font_size_wpx > 32 and $submit_font_size_wpx <= 40) {
        $spinner_margin_top = '-24px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 20);
    } else if ($submit_font_size_wpx > 24 and $submit_font_size_wpx <= 32) {
        $spinner_margin_top = '-22px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 20);
    } else if ($submit_font_size_wpx > 18 and $submit_font_size_wpx <= 24) {
        $spinner_margin_top = '-21px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 20);
    } else if ($submit_font_size_wpx < 17) {
        $spinner_margin_top = '-18px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 18);
    } else {
        $spinner_margin_top = '-19px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 20);
    }

    $perspective = '250px';
    $transform_origin = '14px';
    $b_width = '24px';
    $b_div_width = '24px';
    $translateX = '40px';
    $translateX_70 = '40px';
    $translateX_60 = '19px';
    $b_div_width_extra = '5px';
} else {
    $logo_margin = '-3px';
    $logo_p_margin = '14px';
    $spinner_margin = '40px';

    $submit_width_d2 = ($submit_width_wpx / 2);

    if ($submit_font_size_wpx > 32 and $submit_font_size_wpx <= 40) {
        $spinner_margin_top = '-14px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 6);
    } else if ($submit_font_size_wpx > 24 and $submit_font_size_wpx <= 32) {
        $spinner_margin_top = '-13px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 6);
    } else if ($submit_font_size_wpx > 18 and $submit_font_size_wpx <= 24) {
        $spinner_margin_top = '-12px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 6);
    } else if ($submit_font_size_wpx < 17) {
        $spinner_margin_top = '-9px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 4);
    } else {
        $spinner_margin_top = '-10px';
        $spinner_margin_left = '-' . ($submit_width_d2 + 6);
    }

    $perspective = '100px';
    $transform_origin = '4px';
    $b_width = '8px';
    $b_div_width = '8px';
    $translateX = '7px';
    $translateX_70 = '7px';
    $translateX_60 = '4px';
    $b_div_width_extra = '2px';
}
?>
.arf_submit_btn.arfstyle-button .arfstyle-spinner {
z-index: 2;
display: block;
opacity: 0;
filter:alpha(opacity=0);
pointer-events: none; 
}

.ar_main_div_<?php echo $form_id; ?> .arf_submit_btn.arfstyle-button .arfstyle-label {
z-index: 1;
width:<?php echo ( $submit_width_wpx + 2 ) . 'px'; ?>;
max-width:100%;
text-decoration: inherit;
<?php if ($submit_bg_img != '') { ?> text-indent:-9999px;<?php } else { ?>text-indent:0px;<?php } ?> 
}
<?php if($arfsubmitbuttonstyle == 'flat') { ?>
.ar_main_div_<?php echo $form_id; ?> .arfstyle-button[data-style=zoom-in],
.ar_main_div_<?php echo $form_id; ?> .arfstyle-button[data-style=zoom-in] .arfstyle-label,
.ar_main_div_<?php echo $form_id; ?> .arfstyle-button[data-style=zoom-in] .arfstyle-spinner {
-webkit-transition: 0.2s ease all !important;
-moz-transition: 0.2s ease all !important;
-ms-transition: 0.2s ease all !important;
-o-transition: 0.2s ease all !important;
transition: 0.2s ease all !important; 
}
<?php } ?>

.ar_main_div_<?php echo $form_id; ?> .arfstyle-button[data-style=zoom-in] {
overflow: hidden; 
}

.ar_main_div_<?php echo $form_id; ?> .arfstyle-spinner {
-webkit-transform: scale(1);
-moz-transform: scale(1);
-ms-transform: scale(1);
-o-transform: scale(1);
transform: scale(1); 
}

.ar_main_div_<?php echo $form_id; ?> .arfstyle-button[data-style=zoom-in] .arfstyle-label {
    display: inline-block;
    width:<?php echo ( $submit_width == '' ) ? 'auto' : ($submit_width_wpx + 2) . 'px'; ?>;
    margin-right: -5px;
}

.ar_main_div_<?php echo $form_id; ?> .arfstyle-button[data-style=zoom-in].data-loading .arfstyle-label {
    opacity: 0;
    -webkit-transform: scale(2.2);
    -moz-transform: scale(2.2);
    -ms-transform: scale(2.2);
    -o-transform: scale(2.2);
    transform: scale(2.2);
}

.ar_main_div_<?php echo $form_id; ?> .arfstyle-button[data-style=zoom-in].data-loading .arfstyle-spinner {
    opacity: 1;
    -webkit-transform: none;
    -moz-transform: none;
    -ms-transform: none;
    -o-transform: none;
    transform: none;
}

.ar_main_div_<?php echo $form_id; ?> .arf_submit_btn.arfstyle-button[data-style=zoom-in].data-loading{
    background-color:<?php echo $submit_bg_color_hover ?> !important;
}

.ar_main_div_<?php echo $form_id; ?> .arf_submit_btn.arfstyle-button[data-style=zoom-in].data-loading .arfstyle-spinner .arflogo{
<?php
if ($submit_width <= 100) {
    ?>
    left: -3px;
    <?php
} else if ($submit_width > 100 and $submit_width <= 150) {
    ?>
    left: -2px;
    <?php
} else if ($submit_width > 150 and $submit_width <= 200) {
    ?>
    left: -1px;
    <?php
} else if ($submit_width > 200) {
    ?>
    left: 0;
    <?php
}
?>

float:left;
<?php
$extra_width = 0;
?>


<?php
$spinner_form_width = str_replace('px', '', $form_width);
$spinner_form_width = str_replace(';', '', $spinner_form_width);




$submit_button_center_percent = "";
if ($submit_height_wpx == $submit_width_wpx) {
    $submit_button_center_percent = "35%";
} elseif (($submit_width_wpx / 2 ) < $submit_height_wpx) {
    $gab_between_height_width = $submit_width_wpx - $submit_height_wpx;
    if ($gab_between_height_width >= 1 && $gab_between_height_width <= 20) {
        $submit_button_center_percent = "37%";
    } elseif ($gab_between_height_width >= 21 && $gab_between_height_width <= 30) {
        $submit_button_center_percent = "40%";
    } else {
        $submit_button_center_percent = "45%";
    }
} else {
    $submit_button_center_percent = "47%";
}

if ($submit_height_wpx == $submit_width_wpx) {
    $submit_button_center_percent = "35%";
} elseif ($submit_width_wpx > $submit_height_wpx) {
    $gab_between_width = $submit_width_wpx - $submit_height_wpx;
    if ($gab_between_width >= 1 && $gab_between_width <= 10) {
        $submit_button_center_percent = "42%";
    } elseif ($gab_between_width >= 11 && $gab_between_width <= 20) {
        $submit_button_center_percent = "42%";
    } elseif ($gab_between_width >= 21 && $gab_between_width <= 30) {
        $submit_button_center_percent = "41%";
    } elseif ($gab_between_width >= 31 && $gab_between_width <= 35) {
        $submit_button_center_percent = "45%";
    } elseif ($gab_between_width >= 36 && $gab_between_width <= 40) {
        $submit_button_center_percent = "43%";
    } elseif ($gab_between_width >= 41 && $gab_between_width <= 50) {
        $submit_button_center_percent = "41%";
    } elseif ($gab_between_width >= 51 && $gab_between_width <= 60) {
        $submit_button_center_percent = "43%";
    } elseif ($gab_between_width >= 61 && $gab_between_width <= 80) {
        $submit_button_center_percent = "45%";
    } elseif ($gab_between_width >= 81 && $gab_between_width <= 110) {
        $submit_button_center_percent = "49%";
    } elseif ($gab_between_width >= 111 && $gab_between_width <= 120) {
        $submit_button_center_percent = "49%";
    } elseif ($gab_between_width > 80) {
        $submit_button_center_percent = "48.5%";
    } else {
        $submit_button_center_percent = "47%";
    }
} elseif ($submit_height_wpx > $submit_width_wpx) {
    $gab_between_height = $submit_height_wpx - $submit_width_wpx;
    if ($gab_between_height >= 1 && $gab_between_height <= 20) {
        $submit_button_center_percent = "35%";
    } elseif ($gab_between_height >= 21 && $gab_between_height <= 30) {
        $submit_button_center_percent = "33%";
    } elseif ($gab_between_height >= 31 && $gab_between_height <= 40) {
        $submit_button_center_percent = "30%";
    } elseif ($gab_between_height >= 41 && $gab_between_height <= 50) {
        $submit_button_center_percent = "25%";
    } elseif ($gab_between_height >= 51 && $gab_between_height <= 60) {
        $submit_button_center_percent = "18%";
    } else {
        $submit_button_center_percent = "47%";
    }
} else {
    $submit_button_center_percent = "49%";
}


if ((int)$submit_width_wpx > $spinner_form_width) {
    ?>
    margin-left:<?php echo $submit_button_center_percent; ?>;

<?php } else { ?>
    margin-left:<?php echo ((int)$submit_width_wpx / 2) - ((int)$b_div_width / 2) - (int)$b_div_width_extra; ?>px;
<?php } ?>



<?php
$spinner_margin_top = str_replace('px', '', $spinner_margin_top);
$spinner_margin_top = str_replace(';', '', $spinner_margin_top);
if ($submit_font_size_wpx >= 32 and $submit_font_size_wpx <= 40) {
    $spinner_margin_top -= 12;
} else {
    $spinner_margin_top -= 5;
}
$spinner_margin_top = $spinner_margin_top . 'px';
?>
top:<?php echo $spinner_margin_top; ?>;

margin-bottom:0px;
position:relative;

-webkit-perspective: <?php echo $perspective; ?>;
-webkit-animation: base-cycle 2s linear infinite;
-webkit-transform-origin: <?php echo $transform_origin . ' ' . $transform_origin; ?>;
-webkit-perspective-origin: <?php echo $transform_origin . ' ' . $transform_origin; ?>;

-moz-perspective: <?php echo $perspective; ?>;
-moz-animation: base-cycle 2s linear infinite;
-moz-transform-origin: <?php echo $transform_origin . ' ' . $transform_origin; ?>;
-moz-perspective-origin: <?php echo $transform_origin . ' ' . $transform_origin; ?>;

perspective: <?php echo $perspective; ?>;
animation: base-cycle 2s linear infinite;
transform-origin: <?php echo $transform_origin . ' ' . $transform_origin; ?>;
perspective-origin: <?php echo $transform_origin . ' ' . $transform_origin; ?>;

zoom: 1;
}

@media (max-width: 480px) {
    .ar_main_div_<?php echo $form_id; ?> .arf_submit_btn.arfstyle-button[data-style=zoom-in].data-loading .arfstyle-spinner .arflogo {
        margin-left:<?php echo $submit_button_center_percent; ?>;
    }
    .ar_main_div_<?php echo $form_id; ?> input[type="button"].previous_btn{
        display: block;
        margin: 0px auto 15px auto;
    }
    .ar_main_div_<?php echo $form_id; ?> input[type="submit"].next_btn{ margin-right:0px; }
}

.ar_main_div_<?php echo $form_id; ?> .arf_submit_btn.arfstyle-button[data-style=zoom-in].data-loading .arfstyle-spinner .arflogo .b{
    width: <?php echo $b_width; ?>;
    height: <?php echo $b_width; ?>;
    position: absolute;
    -webkit-transform-style: preserve-3d;
    -moz-transform-style: preserve-3d;
    -o-transform-style: preserve-3d;
    transform-style: preserve-3d;
}

.ar_main_div_<?php echo $form_id; ?> .arf_submit_btn.arfstyle-button[data-style=zoom-in].data-loading .arfstyle-spinner .arflogo .b span{ 
width: <?php echo $b_div_width; ?>;
height: <?php echo $b_div_width; ?>;
border-radius: 100%;
-webkit-border-radius: 100%;
-o-border-radius: 100%;
-moz-border-radius: 100%;
position: absolute;
left: 0;
top: 0;
-webkit-transform-style: preserve-3d;
-moz-transform-style: preserve-3d;
-o-transform-style: preserve-3d;
transform-style: preserve-3d;
}

.ar_main_div_<?php echo $form_id; ?> .arf_submit_btn.arfstyle-button[data-style=zoom-in].data-loading .arfstyle-spinner .arflogo .arfred{}
.ar_main_div_<?php echo $form_id; ?> .arf_submit_btn.arfstyle-button[data-style=zoom-in].data-loading .arfstyle-spinner .arflogo .arfyellow{ 
-webkit-transform: rotate(90deg); 
-moz-transform: rotate(90deg); 
-o-transform: rotate(90deg)
transform: rotate(90deg)
}
.ar_main_div_<?php echo $form_id; ?> .arf_submit_btn.arfstyle-button[data-style=zoom-in].data-loading .arfstyle-spinner .arflogo .arfgreen{ 
-webkit-transform: rotate(180deg);
-moz-transform: rotate(180deg);
-o-transform: rotate(180deg)
transform: rotate(180deg)
}
.ar_main_div_<?php echo $form_id; ?> .arf_submit_btn.arfstyle-button[data-style=zoom-in].data-loading .arfstyle-spinner .arflogo .arfblue{ 
-webkit-transform: rotate(270deg);
-moz-transform: rotate(270deg);
-o-transform: rotate(270deg);
transform: rotate(270deg);
}

.ar_main_div_<?php echo $form_id; ?> .arf_submit_btn.arfstyle-button[data-style=zoom-in].data-loading .arfstyle-spinner .arflogo .arfred span {
background-color: <?php echo $submit_text_color ?>;
}

.ar_main_div_<?php echo $form_id; ?> .arf_submit_btn.arfstyle-button[data-style=zoom-in].data-loading .arfstyle-spinner .arflogo .arfyellow span {
background-color: <?php echo $submit_text_color ?>;
}

.ar_main_div_<?php echo $form_id; ?> .arf_submit_btn.arfstyle-button[data-style=zoom-in].data-loading .arfstyle-spinner .arflogo .arfblue span {
background-color: <?php echo $submit_text_color ?>;
}

.ar_main_div_<?php echo $form_id; ?> .arf_submit_btn.arfstyle-button[data-style=zoom-in].data-loading .arfstyle-spinner .arflogo .arfgreen span {
background-color: <?php echo $submit_text_color ?>;
}

.ar_main_div_<?php echo $form_id; ?> .arf_submit_btn.arfstyle-button[data-style=zoom-in].data-loading .arfstyle-spinner .arflogo .b span{
-webkit-animation: cycle_<?php echo $form_id; ?> 2s ease-out infinite;
-moz-animation: cycle_<?php echo $form_id; ?> 2s ease-out infinite;
animation: cycle_<?php echo $form_id; ?> 2s ease-out infinite;
}

@-webkit-keyframes base-cycle {
0%{ 
-webkit-transform: rotate(0);
}
100%{ 
-webkit-transform: rotate(360deg);
}
}

@-moz-keyframes base-cycle {
0%{ 
-moz-transform: rotate(0);
}
100%{ 
-moz-transform: rotate(360deg);
}
}

@keyframes base-cycle {
0%{ 
transform: rotate(0)
}
100%{ 
transform: rotate(360deg)
}
}


@-webkit-keyframes cycle_<?php echo $form_id; ?> {
0%   { 
-webkit-transform: translateX( <?php echo $translateX; ?> ) rotateY( 0deg );
}
60%  { 
-webkit-transform: translateX( 0 ) rotateY(0deg);
background-color: <?php echo $submit_text_color ?>; }
70%  { 
-webkit-transform: translateX( <?php echo $translateX_60; ?> ) rotateY( 90deg );
}
100% { 
-webkit-transform: translateX( <?php echo $translateX_70; ?> ) rotateY( 0deg );
}
}

@-moz-keyframes cycle_<?php echo $form_id; ?> {
0%   { 
-moz-transform: translateX( <?php echo $translateX; ?> ) rotateY( 0deg );
}
60%  { 
-moz-transform: translateX( 0 ) rotateY(0deg);
background-color: <?php echo $submit_text_color ?>; }
70%  { 
-moz-transform: translateX( <?php echo $translateX_60; ?> ) rotateY( 90deg );
}
100% { 
-moz-transform: translateX( <?php echo $translateX_70; ?> ) rotateY( 0deg );
}
}

@keyframes cycle_<?php echo $form_id; ?> {
0%   { 
transform: translateX( <?php echo $translateX; ?> ) rotateY( 0deg )
}
60%  { 
transform: translateX( 0 ) rotateY(0deg);
background-color: <?php echo $submit_text_color ?>; }
70%  { 
transform: translateX( <?php echo $translateX_60; ?> ) rotateY( 90deg )
}
100% { 
transform: translateX( <?php echo $translateX_70; ?> ) rotateY( 0deg )
}
}

.ar_main_div_<?php echo $form_id; ?> .arfajax-file-upload {
font-family:<?php echo stripslashes($newfontother) ?>;
font-size:<?php echo $field_font_size ?>;
height:<?php echo isset($field_height) ? $field_height : 'auto'; ?>;
font-weight:<?php echo $check_weight ?>;
<?php echo $check_weight_font_style; ?>
padding: 7px <?php echo $file_upload_padding . 'px'; ?> 5px <?php echo $file_upload_padding . 'px'; ?> !important;
}

.ar_main_div_<?php echo $form_id; ?> .arfajax-file-upload-drag {
font-family:<?php echo stripslashes($newfontother) ?>;
font-size:<?php echo $field_font_size ?>;
font-weight:<?php echo $check_weight ?>;
<?php echo $check_weight_font_style; ?>
}

.ar_main_div_<?php echo $form_id; ?> .ajax-file-remove {
font-family:<?php echo stripslashes($newfontother) ?>;
}

.ar_main_div_<?php echo $form_id; ?> .arfajax-file-upload-img {
border: medium none !important;
border-radius: 0 0 0 0 !important;
-webkit-border-radius: 0 0 0 0 !important;
-o-border-radius: 0 0 0 0 !important;
-moz-border-radius: 0 0 0 0 !important;
box-shadow: none !important;
height: <?php echo $file_upload_hw; ?>;
width: <?php echo $file_upload_hw; ?>;
float:left;
margin-top: 0px;
margin-left:-2px;
margin-right:2px;
}


.ar_main_div_<?php echo $form_id; ?> .arf_enable_checkbox_image span.arf_checkbox_label_image.checked::after,#arf_fieldset_<?php echo $form_id; ?> .arf_enable_checkbox_image_editor span.arf_checkbox_label_image_editor.checked::after{
    background-color: <?php echo $base_color;?> !important;
    border-color: <?php echo $base_color; ?> !important;
}
.ar_main_div_<?php echo $form_id; ?> .arf_enable_radio_image span.arf_radio_label_image.checked::after,#arf_fieldset_<?php echo $form_id; ?> .arf_enable_radio_image_editor span.arf_radio_label_image_editor.checked::after{
    background-color: <?php echo $base_color;?> !important;
    border-color: <?php echo $base_color; ?> !important;
}
.ar_main_div_<?php echo $form_id; ?> #form_success_<?php echo $form_id; ?>,
.ar_main_div_<?php echo $form_id; ?> #arf_message_success .msg-detail,
.ar_main_div_<?php echo $form_id; ?> #arf_message_error .msg-detail
{
    <?php if (is_rtl()) {
        echo 'text-align:right !important;';
    } else {
        echo 'text-align:left !important;';
    } ?>
}
<?php
if ($arfcheck_style_name == "custom") {
    $style_property = $arfcheck_style_name;
    $style_property_image = "custom";
    ?>
    .ar_main_div_<?php echo $form_id; ?> .icheckbox_<?php echo $style_property ?>,
    .ar_main_div_<?php echo $form_id; ?> .iradio_<?php echo $style_property ?> {
    display: inline-block;
    *display: inline;
    vertical-align: middle;
    margin: -2px 7px 0px 0px;
    padding: 0;
    width: 26px;
    height: 26px;
    background: url(<?php echo ARFURL; ?>/images/skins/<?php echo $arfcheck_style_name; ?>/<?php echo $style_property_image; ?>.png) no-repeat;
    border: none;
    cursor: pointer;
    }

    <?php if ($base_color != '') { ?>
        .ar_main_div_<?php echo $form_id; ?> .icheckbox_<?php echo $style_property . '.arfa' ?> {
        color: <?php echo $base_color; ?>;
        }
    <?php
    }
    if ($base_color != '') {
        ?>
        .ar_main_div_<?php echo $form_id; ?> .iradio_<?php echo $style_property . '.arfa' ?> {
        color: <?php echo $base_color; ?>;
        }
    <?php } ?>   

    .ar_main_div_<?php echo $form_id; ?> .icheckbox_<?php echo $style_property ?> {
    background-position: 0 0;
    }
    .ar_main_div_<?php echo $form_id; ?> .icheckbox_<?php echo $style_property ?>.arf_hover {
    background-position: -28px 0;
    }
    .ar_main_div_<?php echo $form_id; ?> .icheckbox_<?php echo $style_property ?>.checked {
    background-position: -56px 0;
    }
    .ar_main_div_<?php echo $form_id; ?> .icheckbox_<?php echo $style_property ?>.disabled {
    background-position: -84px 0;
    cursor: default;
    }
    .ar_main_div_<?php echo $form_id; ?> .icheckbox_<?php echo $style_property ?>.checked.disabled {
    background-position: -112px 0;
    }

    .ar_main_div_<?php echo $form_id; ?> .iradio_<?php echo $style_property ?> {
    background-position: -140px 0;
    }
    .ar_main_div_<?php echo $form_id; ?> .iradio_<?php echo $style_property ?>.arf_hover {
    background-position: -168px 0;
    }
    .ar_main_div_<?php echo $form_id; ?> .iradio_<?php echo $style_property ?>.checked {
    background-position: -196px 0;
    }
    .ar_main_div_<?php echo $form_id; ?> .iradio_<?php echo $style_property ?>.disabled {
    background-position: -242px 0;
    cursor: default;
    }
    .ar_main_div_<?php echo $form_id; ?> .iradio_<?php echo $style_property ?>.checked.disabled {
    background-position: -252px 0;
    }

    @media only screen and (-webkit-min-device-pixel-ratio: 1.5),
    only screen and (-moz-min-device-pixel-ratio: 1.5),
    only screen and (-o-min-device-pixel-ratio: 3/2),
    only screen and (min-device-pixel-ratio: 1.5) {
    .ar_main_div_<?php echo $form_id; ?> .icheckbox_<?php echo $style_property ?>,
    .ar_main_div_<?php echo $form_id; ?> .iradio_<?php echo $style_property ?> {
    background-image: url(<?php echo ARFURL; ?>/images/skins/<?php echo $arfcheck_style_name; ?>/<?php echo $style_property_image; ?>@2x.png);
    -webkit-background-size: 280px 28px;
    background-size: 280px 28px;
    }
    }	
    <?php
}

if ($arfcheck_style_name == "none") {
    ?>
    .ar_main_div_<?php echo $form_id; ?> .arf_checkbox_style label, .ar_main_div_<?php echo $form_id; ?> .arf_radiobutton:not(#foo) > label {font-size:<?php echo $field_font_size; ?>; color:<?php echo $text_color; ?>; font-family:<?php echo stripslashes($newfontother) ?>;font-weight:<?php echo $check_weight ?>; <?php echo $check_weight_font_style; ?> }

    .ar_main_div_<?php echo $form_id; ?> .ar_main_div_<?php echo $form_id; ?> .arf_checkbox_style img, .ar_main_div_<?php echo $form_id; ?> .ar_main_div_<?php echo $form_id; ?> .arf_radiobutton img {
    border: none;
    }
    .ar_main_div_<?php echo $form_id; ?> .arf_checkbox_style input[type="checkbox"], .ar_main_div_<?php echo $form_id; ?> .arf_radiobutton input[type="radio"] {
    padding: 0; height: auto; width: auto; float: none; left: auto; position:inherit; opacity:1; margin-right:5px;
    }
    .ar_main_div_<?php echo $form_id; ?> .arf_checkbox_style label, .ar_main_div_<?php echo $form_id; ?> .arf_radiobutton label {
    display:inline-block !important;
    margin-bottom:0px;
    }
    <?php
}
?>
.ar_main_div_<?php echo $form_id; ?> .file_name_info {
font-family:<?php echo stripslashes($newfontother) ?>;
font-size:<?php echo $field_font_size ?>; 
font-weight:<?php echo $check_weight ?>; 
<?php echo $check_weight_font_style; ?>
color:<?php echo $text_color ?> !important;
}

.ar_main_div_<?php echo $form_id; ?> .sltstandard_front .btn-group.open .arfdropdown-menu.open,
.ar_main_div_<?php echo $form_id; ?> .sltstandard_time .btn-group.open .arfdropdown-menu.open { 
    border-top:<?php echo $field_border_width ?> <?php echo $field_border_style ?> <?php echo $base_color ?>;
}

.ar_main_div_<?php echo $form_id; ?> .arfformfield .controls { width:<?php echo $field_width; ?>; }

<?php if ($field_width_unit == '%') { ?>
    @media screen and (max-width: 480px) {
    .ar_main_div_<?php echo $form_id; ?> .arfformfield .controls,
    .ar_main_div_<?php echo $form_id; ?> .arfformfield.frm_first_half .controls, 
    .ar_main_div_<?php echo $form_id; ?> .arfformfield.frm_last_half .controls,
    .ar_main_div_<?php echo $form_id; ?> .arfformfield.frm_third .controls, 
    .ar_main_div_<?php echo $form_id; ?> .arfformfield.frm_first_third .controls, 
    .ar_main_div_<?php echo $form_id; ?> .arfformfield.frm_last_third .controls 
    {width:100% !important;}
    }
<?php } ?>

.ar_main_div_<?php echo $form_id; ?> .popover
{
background-color: <?php echo $arferrorstylecolor; ?> !important;
}
.ar_main_div_<?php echo $form_id; ?> .popover.right .arrow:after,
#cs-content .ar_main_div_<?php echo $form_id; ?> .popover.right .arrow
{
border-right-color: <?php echo $arferrorstylecolor; ?> !important;
} 
.ar_main_div_<?php echo $form_id; ?> .popover.left .arrow:after ,
#cs-content .ar_main_div_<?php echo $form_id; ?> .popover.left .arrow
{
border-left-color: <?php echo $arferrorstylecolor; ?> !important;
}
.ar_main_div_<?php echo $form_id; ?> .popover.top .arrow:after,
#cs-content .ar_main_div_<?php echo $form_id; ?> .popover.top .arrow
{
border-top-color: <?php echo $arferrorstylecolor; ?> !important;
} 
.ar_main_div_<?php echo $form_id; ?> .popover.bottom .arrow:after ,
#cs-content .ar_main_div_<?php echo $form_id; ?> .popover.bottom .arrow
{
border-bottom-color: <?php echo $arferrorstylecolor; ?> !important;
z-index: 0;
left:0px;
border-width: 0 10px 10px 10px;
}
.ar_main_div_<?php echo $form_id; ?> .popover-content
{
color: <?php echo $arferrorstylecolorfont; ?> !important;
font-family:<?php echo stripslashes($newerror_font) ?>;
font-size:<?php echo $error_font_size ?>;
line-height:normal;
}

.ar_main_div_<?php echo $form_id; ?> .arf_strenth_mtr .inside_title {
font-family:<?php echo stripslashes($description_font) ?>;font-size:<?php echo $description_font_size ?>;color:<?php echo $description_color ?>;text-align:left;font-style:<?php echo isset($description_style) ? $description_style : '' ?>;
}

.ar_main_div_<?php echo $form_id; ?> .arfsubmitbutton .arf_submit_btn.arfsubmitdisabled:hover {    
    background:<?php echo $submit_bg_color ?><?php if (!empty($submit_bg_img)) { ?> url(<?php echo $submit_bg_img; ?>)<?php } ?> !important;
}


.ar_main_div_<?php echo $form_id; ?> .arf_survey_nav { color:<?php echo $arf_text_color_survey; ?>; font-family:<?php echo stripslashes($newfont) ?>; font-size: 14px; line-height: 1.5; }
.ar_main_div_<?php echo $form_id; ?> #arf_progress_bar.ui-progress-bar { background:<?php echo $arf_bg_color_survey; ?> !important; }
.ar_main_div_<?php echo $form_id; ?> #arf_progress_bar.ui-progress-bar .ui-progressbar-value { background-color:<?php echo $arf_bar_color_survey; ?> !important; font-family:<?php echo stripslashes($newfont) ?>; }

<?php
$colorpickerpadding = "0";
$padding_array = explode(" ", $arffieldpaddingsetting);
$colorpickerpadding = isset($padding_array[1]) ? $padding_array[1] : "0";
$colorpickerpadding = trim(str_replace('px', '', $colorpickerpadding));

$colorpickerpaddingtop = isset($padding_array[0]) ? $padding_array[0] : "0";
$colorpickerpaddingtop = trim(str_replace('px', '', $colorpickerpaddingtop));

$colorpickerfield_border_width = trim(str_replace('px', '', $field_border_width));
$colorpickerheight = ( ($field_font_size_without_px) + ($colorpickerpaddingtop * 2) );

$colorpickerheight_new = ( ($field_font_size_without_px) + ($colorpickerpaddingtop * 2) ) + (2 * $colorpickerfield_border_width);

$colorpickerheight_new = $colorpickerheight_new < 20 ? 20 : $colorpickerheight_new;

$arfcolorpickerfullheight = $colorpickerheight_new;
$colorpickerwidth1 = 148;
$colvaluewidth = 109;
$arfcolorpickerfullwidth = 15;
$arfcolorpickerfullpadding = "0 13px";
if ($colorpickerheight_new < 30) {
    $arfcolorpickerheight = $colorpickerheight_new - 6;
    $colorpickerpaddingtop = 6;
    $colorpickerwidth = $colorpickerwidth1 + (2 * $colorpickerfield_border_width);
    $colvaluewidth = $colorpickerwidth - $colorpickerfield_border_width - 15 - 5;
    $colrpick_upload_bg = '16';
} else if ($colorpickerheight_new < 36) {
    $arfcolorpickerheight = $colorpickerheight_new - 8;
    $colorpickerpaddingtop = 8;
    $colorpickerwidth = $colorpickerwidth1 + (2 * $colorpickerfield_border_width);
    $colvaluewidth = $colorpickerwidth - $colorpickerfield_border_width - 15 - 5;
    $colrpick_upload_bg = '16';
} else if ($colorpickerheight_new < 41) {
    $arfcolorpickerheight = $colorpickerheight_new - 10;
    $colorpickerpaddingtop = 10;
    $colorpickerwidth = $colorpickerwidth1 + (2 * $colorpickerfield_border_width);
    $colvaluewidth = $colorpickerwidth - $colorpickerfield_border_width - 15 - 5;
    $colrpick_upload_bg = '16';
} else if ($colorpickerheight_new < 46) {
    $arfcolorpickerheight = $colorpickerheight_new - 12;
    $colorpickerpaddingtop = 12;
    $colorpickerwidth = $colorpickerwidth1 + (2 * $colorpickerfield_border_width);
    $colvaluewidth = $colorpickerwidth - $colorpickerfield_border_width - 15 - 5;
    $colrpick_upload_bg = '16';
} else if ($colorpickerheight_new < 51) {
    $arfcolorpickerheight = $colorpickerheight_new - 14;
    $colorpickerpaddingtop = 14;
    $colorpickerwidth = $colorpickerwidth1 + (2 * $colorpickerfield_border_width);
    $colvaluewidth = $colorpickerwidth - $colorpickerfield_border_width - 24 - 5;
    $arfcolorpickerfullwidth = 24;
    $colrpick_upload_bg = '22';
} else {
    $arfcolorpickerheight = $colorpickerheight_new - 16;
    $colorpickerpaddingtop = 16;
    $colorpickerwidth = $colorpickerwidth1 + (2 * $colorpickerfield_border_width);
    $colvaluewidth = $colorpickerwidth - $colorpickerfield_border_width - 24 - 5;
    $arfcolorpickerfullwidth = 24;
    $colrpick_upload_bg = '22';
}

$colorvaluemargin = $arfcolorpickerfullwidth + $colorpickerfield_border_width;

$border_radius_pxx = str_replace('px', '', $border_radius);
$border_radius_px2 = ( $border_radius_pxx < 2 ) ? 0 : $border_radius_pxx - 1;
$border_radius_pxx = ( $border_radius_pxx < 3 ) ? 0 : $border_radius_pxx - 2;
?>
.ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix.arf_colorpicker_prefix_editor{
<?php if (is_rtl()) { ?>
    -webkit-border-radius: 0px <?php echo $border_radius;?> <?php echo $border_radius;?> 0px !important;
    -o-border-radius: 0px <?php echo $border_radius;?> <?php echo $border_radius;?> 0px !important;
    -moz-border-radius: 0px <?php echo $border_radius;?> <?php echo $border_radius;?> 0px !important;
    border-radius: 0px <?php echo $border_radius;?> <?php echo $border_radius;?> 0px !important;
<?php } else { ?>
    -webkit-border-radius:<?php echo $border_radius;?> 0px 0px  <?php echo $border_radius;?>;
    -o-border-radius:<?php echo $border_radius;?> 0px 0px  <?php echo $border_radius;?>;
    -moz-border-radius:<?php echo $border_radius;?> 0px 0px  <?php echo $border_radius;?>;
    border-radius:<?php echo $border_radius;?> 0px 0px  <?php echo $border_radius;?>;    
<?php } ?>
}
.ar_main_div_<?php echo $form_id; ?> .arfcolorpickerfield {
border:<?php echo $field_border_width_select_custom; ?>px <?php echo $field_border_style; ?> <?php echo $border_color; ?>;
width:80px;
height:30px;
-webkit-border-radius:<?php echo $border_radius;?>;
-o-border-radius:<?php echo $border_radius;?>;
-moz-border-radius:<?php echo $border_radius;?>;
border-radius:<?php echo $border_radius;?>;
overflow:hidden;
cursor:pointer;
}
.ar_main_div_<?php echo $form_id; ?> .controls .arfcolorpickerfield .arfcolorimg,.ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix.arf_colorpicker_prefix_editor{
    background:<?php echo $prefix_suffix_bg_color; ?> !important;
    border-color: <?php echo $border_color; ?>;
}
.ar_main_div_<?php echo $form_id; ?> .controls .arfcolorpickerfield .arfcolorimg svg path,.ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix.arf_colorpicker_prefix_editor .paint_brush_position svg path{
    fill:<?php echo $prefix_suffix_icon_color; ?>;    
}
.ar_main_div_<?php echo $form_id; ?> .arfcolorpickerfield .arfcolorimg {
height:30px;
width:20px;
background:<?php echo $prefix_suffix_bg_color; ?> !important;
background-repeat:no-repeat;
background-position:center center;
<?php if (is_rtl()) { ?>
    border-left:<?php echo $field_border_width_select_custom; ?>px <?php echo $field_border_style; ?> <?php echo $border_color; ?>;
    float:right;
<?php } else { ?>
    border-right:<?php echo $field_border_width_select_custom; ?>px <?php echo $field_border_style; ?> <?php echo $border_color; ?>;
    float:left;
<?php } ?>
font-size:<?php echo $colrpick_upload_bg; ?>;
padding:0 3px;
}
.ar_main_div_<?php echo $form_id; ?> .arfcolorpickerfield .arfcolorimg i.arfa-paint-brush {
height:<?php echo $arfcolorpickerfullheight . 'px'; ?>;
line-height:<?php echo $arfcolorpickerfullheight . 'px'; ?>;
color:<?php echo $prefix_suffix_icon_color; ?>;
}

.ar_main_div_<?php echo $form_id; ?> .arfcolorvalue {
color: #333333;
vertical-align: middle;
<?php if (is_rtl()) { ?>
    padding:8px 30px 0px 0px;
<?php } else { ?>
    padding:8px 0px 0px 30px;
<?php } ?>

height:23px;
background:<?php echo $bg_color; ?>;
font-family:Arial, Helvetica, sans-serif;
font-size: 12px;
line-height:normal;
text-transform:lowercase;
text-align:<?php echo $text_direction == 'rtl' ? 'right' : 'left'; ?>;
}
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .arfcolorpickerfield {
border: <?php echo $border_width_error ?> <?php echo $field_border_style; ?> <?php echo $border_color_error ?>;    
}
.ar_main_div_<?php echo $form_id; ?> .control-group.arf_error .arfcolorpickerfield:focus {
-moz-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($border_color_error); ?>, 0.4);
-webkit-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($border_color_error); ?>, 0.4);
-o-box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($border_color_error); ?>, 0.4);
box-shadow:0px 0px 2px rgba(<?php echo $arsettingcontroller->hex2rgb($border_color_error); ?>, 0.4);	
}
.ar_main_div_<?php echo $form_id; ?> .arf_heading_div{
padding:<?php echo $section_padding; ?>;
}

<?php

    if (isset($form_id) && !empty($form_id)) {

        global $arfieldhelper;

        if (!isset($preview) || (isset($preview) && !$preview) ){
            if( isset($arfaction) && $arfaction == 'duplicate'){
            $fields = $arfieldhelper->get_form_fields_tmp(false, $_GET['id'], false, 0);
        } else {
            $fields = $arfieldhelper->get_form_fields_tmp(false, $form_id, false, 0);
        }
        } else {
            $fields = $arf_all_preview_fields;
        }
           
        if (isset($fields) && count($fields) > 0) {
            global $arformcontroller;
            $all_fields = new stdClass();
            foreach($fields as $k => $field){
                $temp_field = new stdClass();
                
                foreach($field as $ki => $field_ ){
                    
                    if( $ki == 'field_options' ){
                        if( !is_array($field_) ){
                            $tempObj = json_decode($field_);
                            if(json_last_error() != JSON_ERROR_NONE){
                                $tempObj = maybe_unserialize($field_);
                            }
                            $field_ = $tempObj;
                        }
                        if(is_array($field_)){
                            foreach( $field_ as $i => $f ){
                                $temp_field->$i = $f;
                            }
                        }
                    } else {
                        $temp_field->$ki = $field_;
                    }
                }
                $all_fields->$k = $temp_field;
            }

           
            
            foreach ($all_fields as $field) {
                $field_type = '';



                if ($field->type == 'text' or $field->type == 'email' or $field->type == 'number' or $field->type == 'time' or $field->type == 'date')
                    $field_type = 'text';
                else if ($field->type == 'phone')
                    $field_type = 'tel';
                else if ($field->type == 'image')
                    $field_type = 'url';
                else
                    $field_type = $field->type;
                if (isset($field->enable_arf_prefix) && $field->enable_arf_prefix == 1) {
                    $field->id = $arfieldhelper->get_actual_id($field->id);

                    $arf_prefix_padding = '';
                    $arf_prefix_width = '';
                    $arf_prefix_padding = '0 0px';

                        if ($field_font_size < 10) $arf_prefix_width = '32px';
                        else if ($field_font_size >= 10 && $field_font_size < 12) $arf_prefix_width = '34px';
                        else if ($field_font_size >= 12 && $field_font_size < 14) $arf_prefix_width = '36px';
                        else if ($field_font_size >= 14 && $field_font_size < 16) $arf_prefix_width = '38px';
                        else if ($field_font_size >= 16 && $field_font_size < 18) $arf_prefix_width = '40px';
                        else if ($field_font_size >= 18 && $field_font_size < 20) $arf_prefix_width = '42px';
                        else if ($field_font_size >= 20 && $field_font_size < 22) $arf_prefix_width = '44px';
                        else if ($field_font_size == 22) $arf_prefix_width = '46px';
                        else if ($field_font_size == 24) $arf_prefix_width = '51px';
                        else if ($field_font_size == 26) $arf_prefix_width = '53px';
                        else if ($field_font_size == 28) $arf_prefix_width = '55px';
                        else if ($field_font_size == 32) $arf_prefix_width = '60px';
                        else if ($field_font_size == 34) $arf_prefix_width = '62px';
                        else if ($field_font_size == 36) $arf_prefix_width = '64px';
                        else if ($field_font_size == 38) $arf_prefix_width = '67px';
                        else if ($field_font_size == 40) $arf_prefix_width = '70px';

                    echo '.ar_main_div_' . $form_id . ' .arfformfield.arf_field_' . $field->id . ' .arf_prefix';
                    if ($field_type == 'password')
                        echo ', .ar_main_div_' . $form_id . ' .arf_confirm_password_field_' . $field->id . ' .arf_prefix';
                    if ($field->type == 'email')
                        echo ', .ar_main_div_' . $form_id . ' .arf_confirm_email_field_' . $field->id . ' .arf_prefix';
                    echo '{
    						display:table-cell;
    						width:' . $arf_prefix_width . ';
    						padding:' . $arf_prefix_padding . ';
    						vertical-align:middle;
    						color:' . $prefix_suffix_icon_color . ';
    						text-align:center;
    						background:' . $prefix_suffix_bg_color . ';
    						border:' . $field_border_width . ' ' . $field_border_style . ' ' . $border_color . ';';
                    if (is_rtl()) {
                        echo '	border-top-right-radius:' . $border_radius . ';
    									border-bottom-right-radius:' . $border_radius . ';';
                    } else {
                        echo '	border-top-left-radius:' . $border_radius . ';
    							border-bottom-left-radius:' . $border_radius . ';';
                    }

                    echo '}';

                    echo "@media (min-width:290px) and (max-width:480px){";




                    echo '.ar_main_div_' . $form_id . ' .arfformfield.arf_field_' . $field->id . ' .arf_prefix';

                    if ($field_type == 'password')
                        echo ', .ar_main_div_' . $form_id . ' .arf_confirm_password_field_' . $field->id . ' .arf_prefix';
                    if ($field->type == 'email')
                        echo ', .ar_main_div_' . $form_id . ' .arf_confirm_email_field_' . $field->id . ' .arf_prefix';
                    echo '{
    							display:table-cell;
    							width:40px !important;
    							padding:0 !important;
    							vertical-align:middle;
    							text-align:center;
    							color:' . $prefix_suffix_icon_color . ';
    							background:' . $prefix_suffix_bg_color . ';
    							border:' . $field_border_width . ' ' . $field_border_style . ' ' . $border_color . ';';
                    if (is_rtl()) {
                        echo '	border-top-right-radius:' . $border_radius . ';
    										border-bottom-right-radius:' . $border_radius . ';';
                    } else {
                        echo '	border-top-left-radius:' . $border_radius . ';
    								border-bottom-left-radius:' . $border_radius . ';';
                    }

                    echo '}';

                    echo "}";

                    echo '.ar_main_div_' . $form_id . ' .arfformfield.arf_field_' . $field->id . ' .arf_prefix.arf_prefix_focus';
                    if ($field_type == 'password')
                        echo ', .ar_main_div_' . $form_id . ' .arf_confirm_password_field_' . $field->id . ' .arf_prefix.arf_prefix_focus';
                    if ($field->type == 'email')
                        echo ', .ar_main_div_' . $form_id . ' .arf_confirm_email_field_' . $field->id . ' .arf_prefix.arf_prefix_focus';
                    echo '{
    						border-color:' . $base_color . ' !important;
    						transition:all 0.4s ease 0s;
    						-webkit-transition:all 0.4s ease 0s;
    						-moz-transition:all 0.4s ease 0s;
    						-o-transition:all 0.4s ease 0s;
    						box-shadow:0 0 2px rgba(' . $arsettingcontroller->hex2rgb($base_color) . ',0.4);
    						-moz-box-shadow:0 0 2px rgba(' . $arsettingcontroller->hex2rgb($base_color) . ',0.4);
    						-webkit-box-shadow:0 0 2px rgba(' . $arsettingcontroller->hex2rgb($base_color) . ',0.4);
    						-o-box-shadow:0 0 2px rgba(' . $arsettingcontroller->hex2rgb($base_color) . ',0.4);
    						
    					}';

                    echo '.ar_main_div_' . $form_id . ' .arfformfield.arf_field_' . $field->id . ' .arf_prefix i';
                    if ($field_type == 'password')
                        echo ', .ar_main_div_' . $form_id . ' .arf_confirm_password_field_' . $field->id . ' .arf_prefix i';
                    if ($field->type == 'email')
                        echo ', .ar_main_div_' . $form_id . ' .arf_confirm_email_field_' . $field->id . ' .arf_prefix i';
                    echo '{
    						font-size:' . $field_font_size . ' !important;
    					}';

                    echo "@media (min-width:290px) and (max-width:480px){";
                    echo '.ar_main_div_' . $form_id . ' .arfformfield.arf_field_' . $field->id . ' .arf_prefix i';
                    if ($field_type == 'password')
                        echo ', .ar_main_div_' . $form_id . ' .arf_confirm_password_field_' . $field->id . ' .arf_prefix i';
                    if ($field->type == 'email')
                        echo ', .ar_main_div_' . $form_id . ' .arf_confirm_email_field_' . $field->id . ' .arf_prefix i';
                    echo '{
    							font-size:20px !important;
    						}';
                    echo "}";

                    echo '.ar_main_div_' . $form_id . ' .arfformfield.arf_field_' . $field->id . '.arf_error .arf_prefix.arf_prefix_focus,
    					.ar_main_div_' . $form_id . ' .arfformfield.arf_field_' . $field->id . '.arf_warning .arf_prefix.arf_prefix_focus,
                        .ar_main_div_' . $form_id . ' .arfformfield.arf_field_' . $field->id . '.arf_warning .arf_suffix.arf_suffix_focus,
                        .ar_main_div_' . $form_id . ' .arfmainformfield.arf_warning .controls input:focus';

                    if ($field_type == 'password') {
                        echo ',.ar_main_div_' . $form_id . ' .arf_confirm_password_field_' . $field->id . '.arf_error .arf_prefix,
    							.ar_main_div_' . $form_id . ' .arf_confirm_password_field_' . $field->id . '.arf_warning .arf_prefix';
                    }
                    if ($field->type == 'email') {
                        echo ',.ar_main_div_' . $form_id . '  .arf_confirm_email_field_' . $field->id . '.arf_error .arf_prefix.arf_prefix_focus,
    							.ar_main_div_' . $form_id . ' .arf_confirm_email_field_' . $field->id . '.arf_warning .arf_prefix.arf_prefix_focus';
                    }
                    echo '{ border-color:' . $border_color_error . ' !important;
    						transition:all 0.4s ease 0s;
    						-webkit-transition:all 0.4s ease 0s;
    						-moz-transition:all 0.4s ease 0s;
    						-o-transition:all 0.4s ease 0s;
                            -moz-box-shadow:0px 0px 2px rgba('.$arsettingcontroller->hex2rgb($border_color_error).', 0.4) ;
                            -webkit-box-shadow:0px 0px 2px rgba('.$arsettingcontroller->hex2rgb($border_color_error).', 0.4) !important;
                            -o-box-shadow:0px 0px 2px rgba('.$arsettingcontroller->hex2rgb($border_color_error).', 0.4) !important;
                            box-shadow:0px 0px 2px rgba('.$arsettingcontroller->hex2rgb($border_color_error).', 0.4) !important;
                                                    
    					}';

                    echo '.ar_main_div_' . $form_id . ' #arf_field_' . $field->id . '_container input';
                    if ($field_type == 'password')
                        echo ',.ar_main_div_' . $form_id . ' .arf_confirm_password_field_' . $field->id . ' input[type="password"]';
                    if ($field->type == 'email')
                        echo ',.ar_main_div_' . $form_id . ' .arf_confirm_email_field_' . $field->id . ' input[type="text"]';
                    if (is_rtl()) {
                        echo '{
    									border-right:none !important;
    									border-top-right-radius:0px !important;
    									border-bottom-right-radius:0px !important;
    								}';
                    } else {
                        echo '{
    							border-left:none !important;
    							border-top-left-radius:0px !important;
    							border-bottom-left-radius:0px !important;
    						}';
                    }

                    echo '.ar_main_div_' . $form_id . ' .arfformfield.arf_field_' . $field->id . ' input';
                    if ($field_type == 'password')
                        echo ', .ar_main_div_' . $form_id . ' .arf_confirm_password_field_' . $field->id . ' input[type="password"]';
                    if ($field->type == 'email')
                        echo ', .ar_main_div_' . $form_id . ' .arf_confirm_email_field_' . $field->id . ' input[type="text"]';
                    echo '{
    						width:100% !important;
    					}';
                }
                if (isset($field->enable_arf_suffix) && $field->enable_arf_suffix == 1) {

                    $field->id = $arfieldhelper->get_actual_id($field->id);
                    $arf_suffix_padding = '';
                    $arf_suffix_width = '';

                        $arf_suffix_padding = '0 0px';

                        if ($field_font_size < 10) $arf_prefix_width = '32px';
                        else if ($field_font_size >= 10 && $field_font_size < 12) $arf_suffix_width = '34px';
                        else if ($field_font_size >= 12 && $field_font_size < 14) $arf_suffix_width = '36px';
                        else if ($field_font_size >= 14 && $field_font_size < 16) $arf_suffix_width = '38px';
                        else if ($field_font_size >= 16 && $field_font_size < 18) $arf_suffix_width = '40px';
                        else if ($field_font_size >= 18 && $field_font_size < 20) $arf_suffix_width = '42px';
                        else if ($field_font_size >= 20 && $field_font_size < 22) $arf_suffix_width = '44px';
                        else if ($field_font_size == 22) $arf_suffix_width = '46px';
                        else if ($field_font_size == 24) $arf_suffix_width = '51px';
                        else if ($field_font_size == 26) $arf_suffix_width = '53px';
                        else if ($field_font_size == 28) $arf_suffix_width = '55px';
                        else if ($field_font_size == 32) $arf_suffix_width = '60px';
                        else if ($field_font_size == 34) $arf_suffix_width = '62px';
                        else if ($field_font_size == 36) $arf_suffix_width = '64px';
                        else if ($field_font_size == 38) $arf_suffix_width = '67px';
                        else if ($field_font_size == 40) $arf_suffix_width = '70px';

                    echo '.ar_main_div_' . $form_id . ' .arfformfield.arf_field_' . $field->id . ' .arf_suffix';
                    if ($field_type == 'password')
                        echo ', .ar_main_div_' . $form_id . ' .arf_confirm_password_field_' . $field->id . ' .arf_suffix';
                    if ($field->type == 'email')
                        echo ', .ar_main_div_' . $form_id . ' .arf_confirm_email_field_' . $field->id . ' .arf_suffix';
                    echo '{
    						display:table-cell;
    						width:' . $arf_suffix_width . ';
    						text-align:center;
    						padding:' . $arf_suffix_padding . ';
    						vertical-align:middle;
    						color:' . $prefix_suffix_icon_color . ';
    						background:' . $prefix_suffix_bg_color . ';
    						border:' . $field_border_width . ' ' . $field_border_style . ' ' . $border_color . ';';
                    if (is_rtl()) {
                        echo 'border-top-left-radius:' . $border_radius . ';
    							border-bottom-left-radius:' . $border_radius . ';';
                    } else {
                        echo 'border-top-right-radius:' . $border_radius . ';
    							border-bottom-right-radius:' . $border_radius . ';';
                    }
                    echo '}';

                    echo "@media (min-width:290px) and (max-width:480px){";

                    echo '.ar_main_div_' . $form_id . ' .arfformfield.arf_field_' . $field->id . ' .arf_suffix';
                    if ($field_type == 'password')
                        echo ', .ar_main_div_' . $form_id . ' .arf_confirm_password_field_' . $field->id . ' .arf_suffix';
                    if ($field->type == 'email')
                        echo ', .ar_main_div_' . $form_id . ' .arf_confirm_email_field_' . $field->id . ' .arf_suffix';
                    echo '{
    							display:table-cell;
    							width:40px !important;
    							padding:0 !important;
    							vertical-align:middle;
    							text-align:center;
    							color:' . $prefix_suffix_icon_color . ';
    							background:' . $prefix_suffix_bg_color . ';
    							border:' . $field_border_width . ' ' . $field_border_style . ' ' . $border_color . ';';
                    if (is_rtl()) {
                        echo 'border-top-left-radius:' . $border_radius . ';
    									border-bottom-left-radius:' . $border_radius . ';';
                    } else {
                        echo 'border-top-right-radius:' . $border_radius . ';
    									border-bottom-right-radius:' . $border_radius . ';';
                    }

                    echo '}';

                    echo "}";

                    echo '.ar_main_div_' . $form_id . ' .arfformfield.arf_field_' . $field->id . ' .arf_suffix i';
                    if ($field_type == 'password')
                        echo ',	.ar_main_div_' . $form_id . ' .arf_confirm_password_field_' . $field->id . ' .arf_suffix i';
                    if ($field->type == 'email')
                        echo ', .ar_main_div_' . $form_id . ' .arf_confirm_email_field_' . $field->id . ' .arf_suffix i';
                    echo '{
    						font-size:' . $field_font_size . ' !important;
    					}';

                    echo "@media (min-width:290px) and (max-width:480px){";
                    echo '.ar_main_div_' . $form_id . ' .arfformfield.arf_field_' . $field->id . ' .arf_suffix i';
                    if ($field_type == 'password')
                        echo ', .ar_main_div_' . $form_id . ' .arf_confirm_password_field_' . $field->id . ' .arf_suffix i';
                    if ($field->type == 'email')
                        echo ', .ar_main_div_' . $form_id . ' .arf_confirm_email_field_' . $field->id . ' .arf_suffix i';
                    echo '{
    							font-size:20px !important;
    						}';
                    echo "}";

                    echo '.ar_main_div_' . $form_id . ' .arfformfield.arf_field_' . $field->id . ' .arf_suffix.arf_suffix_focus';
                    if ($field_type == 'password')
                        echo ', .ar_main_div_' . $form_id . ' .arf_confirm_password_field_' . $field->id . ' .arf_suffix.arf_suffix_focus';
                    if ($field->type == 'email')
                        echo ', .ar_main_div_' . $form_id . ' .arf_confirm_email_field_' . $field->id . ' .arf_suffix.arf_suffix_focus';
                    echo '{
    						border-color:' . $base_color . ' !important;
    						transition:all 0.4s ease 0s;
    						-webkit-transition:all 0.4s ease 0s;
    						-moz-transition:all 0.4s ease 0s;
    						-o-transition:all 0.4s ease 0s;
    						box-shadow:0 0 2px rgba(' . $arsettingcontroller->hex2rgb($base_color) . ',0.4);
    						-moz-box-shadow:0 0 2px rgba(' . $arsettingcontroller->hex2rgb($base_color) . ',0.4);
    						-webkit-box-shadow:0 0 2px rgba(' . $arsettingcontroller->hex2rgb($base_color) . ',0.4);
    						-o-box-shadow:0 0 2px rgba(' . $arsettingcontroller->hex2rgb($base_color) . ',0.4);
    					}';

                    echo '.ar_main_div_' . $form_id . ' .arfformfield.arf_field_' . $field->id . ' .arf_error .arf_suffix,
                    .ar_main_div_' . $form_id . ' .arfformfield.arf_field_' . $field->id . '.arf_error .arf_suffix.arf_suffix_focus,
    					.ar_main_div_' . $form_id . ' .arfformfield.arf_field_' . $field->id . '.arf_warning .arf_suffix,
                        .ar_main_div_' . $form_id . ' .arfformfield.arf_field_' . $field->id . '.arf_warning .arf_suffix.arf_suffix_focus';
                    if ($field_type == 'password') {
                        echo ',	.ar_main_div_' . $form_id . ' .arf_confirm_password_field_' . $field->id . '.arf_error .arf_suffix,
    								.ar_main_div_' . $form_id . ' .arf_confirm_password_field_' . $field->id . '.arf_warning .arf_suffix';
                    }
                    if ($field->type == 'email') {
                        echo ', .ar_main_div_' . $form_id . ' .arf_confirm_email_field_' . $field->id . '.arf_error .arf_suffix,
    								.ar_main_div_' . $form_id . ' .arf_confirm_email_field_' . $field->id . '.arf_warning .arf_suffix';
                    }
                    echo '{
    						border-color:' . $border_color_error . ' !important;
    						transition:all 0.4s ease 0s;
    						-webkit-transition:all 0.4s ease 0s;
    						-moz-transition:all 0.4s ease 0s;
    						-o-transition:all 0.4s ease 0s;
                            -moz-box-shadow:0px 0px 2px rgba('.$arsettingcontroller->hex2rgb($border_color_error).', 0.4) ;
                            -webkit-box-shadow:0px 0px 2px rgba('.$arsettingcontroller->hex2rgb($border_color_error).', 0.4) !important;
                            -o-box-shadow:0px 0px 2px rgba('.$arsettingcontroller->hex2rgb($border_color_error).', 0.4) !important;
                            box-shadow:0px 0px 2px rgba('.$arsettingcontroller->hex2rgb($border_color_error).', 0.4) !important;
                                                    
    					}';

                    echo '.ar_main_div_' . $form_id . ' .arfformfield.arf_field_' . $field->id . ' input';
                    if ($field_type == 'password')
                        echo ', .ar_main_div_' . $form_id . ' .arf_confirm_password_field_' . $field->id . ' input[type="password"]';
                    if ($field->type == 'email')
                        echo ', .ar_main_div_' . $form_id . ' .arf_confirm_email_field_' . $field->id . ' input[type="text"]';
                    echo '{
    						width:100% !important;
    					}';
                }
                if ((isset($field->enable_arf_prefix) && $field->enable_arf_prefix == 1) || (isset($field->enable_arf_suffix) && $field->enable_arf_suffix == 1)) {

                    echo '.ar_main_div_' . $form_id . ' .arfformfield.arf_field_' . $field->id . ' .arf_prefix_suffix_wrapper';
                    if ($field_type == 'password')
                        echo ', .ar_main_div_' . $form_id . ' .arf_confirm_password_field_' . $field->id . ' .arf_prefix_suffix_wrapper';
                    if ($field->type == 'email') {
                        echo ', .ar_main_div_' . $form_id . ' .arf_confirm_email_field_' . $field->id . ' .arf_prefix_suffix_wrapper';
                    }
                    echo '{';
                    if ($field_width_unit == '%') {
                        echo 'width:100%;';
                    } else if ($field_width_unit == 'px') {
                        echo 'max-width:' . ($field_width - $field_border_width) . 'px;';
                        echo 'width:100%;';
                    }
                    echo '}';

                    if ($field->enable_arf_suffix == 1) {
                        echo '.ar_main_div_'.$form_id.' .controls .arf_prefix_suffix_wrapper input[name="item_meta['.$field->id.']"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete),';
                        echo '.ar_main_div_'.$form_id.' .controls .arf_prefix_suffix_wrapper input[data-name="item_meta['.$field->id.']"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete)';
                        if ($field_type == 'password')
                            echo ', .ar_main_div_' . $form_id . ' .arf_confirm_password_field_' . $field->id . ' input[type="password"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete)';
                        if ($field->type == 'email')
                            echo ', .ar_main_div_' . $form_id . ' .arf_confirm_email_field_' . $field->id . ' input[type="text"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete)';
                        if (is_rtl()) {
                            echo '{
    								border-left:none !important;
    								border-top-left-radius:0px !important;
    								border-bottom-left-radius:0px !important;
    							}';
                        } else {
                            echo '{
    								border-right:none !important;
    								border-top-right-radius:0px !important;
    								border-bottom-right-radius:0px !important;
    							}';
                        }
                    }

                    if ($field->enable_arf_prefix == 1) {
                        
                        echo '.ar_main_div_'.$form_id.' .controls .arf_prefix_suffix_wrapper:not(.arf_phone_with_flag) input[name="item_meta['.$field->id.']"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete),';
                        echo '.ar_main_div_'.$form_id.' .controls .arf_prefix_suffix_wrapper:not(.arf_phone_with_flag) input[data-name="item_meta['.$field->id.']"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete)';
                        if ($field_type == 'password')
                            echo ', .ar_main_div_' . $form_id . ' .arf_confirm_password_field_' . $field->id . ' input[type="pasword"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete)';
                        if ($field->type == 'email')
                            echo ', .ar_main_div_' . $form_id . ' .arf_confirm_email_field_' . $field->id . ' input[type="text"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete)';
                        if (is_rtl()) {
                            echo '{
    								border-right:none !important;
    								border-top-right-radius:0px !important;
    								border-bottom-right-radius:0px !important;
    							}';
                        } else {
                            echo '{
    								border-left:none !important;
    								border-top-left-radius:0px !important;
    								border-bottom-left-radius:0px !important;
    							}';
                        }
                    }
                }
                    if($field->type =='imagecontrol'){
                            echo ' @media all and (max-width: 480px) {';
                        echo '#arf_imagefield_' . $field->id.'{';
                            if ( isset($field->position_for_mobile_x) && $field->position_for_mobile_x != "" ) {
                                $field_position_x = $field->position_for_mobile_x;
                                if( !preg_match('/px/',$field_position_x) ){
                                    $field_position_x .= 'px';
                                }
                                echo 'left:'.$field_position_x.' !important;';
                            }
                            if (isset($field->position_for_mobile_y) && $field->position_for_mobile_y !="") {
                                $field_position_y = $field->position_for_mobile_y;
                                if( !preg_match('/px/',$field_position_y) ){
                                    $field_position_y .= 'px';
                                }
                                echo 'top:'.$field_position_y.' !important;';
                            }
                        echo'}';
                        echo '#arf_imagefield_' . $field->id.' '.'img'.'{';    
                            if(isset($field->width_for_mobile) && $field->width_for_mobile !=""){
                                echo 'width:'.$field->width_for_mobile.' !important;';
                            }
                            if(isset($field->height_for_mobile) && $field->height_for_mobile !=""){
                                echo 'height:'.$field->height_for_mobile.' !important;';
                            }
                        echo'}';
                        echo'#image_horizontal_center_'.$field->id.'{';
                            if (isset($field->position_for_mobile_y) && $field->position_for_mobile_y !="") {
                                echo 'top:'.$field->position_for_mobile_y.' !important;';
                            }
                        echo '}';

                    echo '} ';
                    }
                if( $field->type == 'scale' ){
                    echo ".arf_star_rating_container_".$field->id."{";
                        echo "float:none;";
                        echo "width:auto;";
                        echo "display:inline-block;";
                        echo "margin-left:-15px;";
                    echo "}";
                    echo ".arf_star_rating_container_".$field->id." input{";
                        echo "display:none;";
                    echo "}";
                    echo ".arf_star_rating_container_{$field->id} label.arf_star_rating_label:not(.arf_star_rating_label_null),";
                    echo ".arf_star_rating_container_{$field->id} label.arf_star_rating_label:not(.arf_star_rating_label_null) svg{";
                        echo "width:{$field->star_size}px;";
                        echo "height:".($field->star_size-1)."px;";
                    echo "}";
                    echo ".arf_star_rating_container_{$field->id} label.arf_star_rating_label.arf_star_rating_label_null{";
                        echo "width:10px !important;";
                        echo "height:{$field->star_size}px;";
                        echo "margin:0px;";
                    echo "}";
                }
                if( $field->type == 'checkbox' || $field->type == 'radio' ){
                    if( isset($field->use_image) && $field->use_image == 1 ){
                        echo "@media (max-width:480px){";
                            echo ".ar_main_div_{$form_id} .arf_field_type_checkbox.arf_field_{$field->id} .controls{";
                                echo "text-align:center;";
                            echo "}";
                        echo "}";
                    }
                }
            }
        }
    }

    echo ".ar_main_div_{$form_id} .arf_star_rating_container input:checked ~ label.arf_star_rating_label svg path{";
        echo "fill:{$star_rating_color};";
    echo "}";
    echo ".ar_main_div_{$form_id} .control-group:not([data-view='arf_disabled']) .arf_star_rating_container label.arf_star_rating_label:hover svg path,";
    echo ".ar_main_div_{$form_id} .control-group:not([data-view='arf_disabled']) .arf_star_rating_container label.arf_star_rating_label:hover ~ label.arf_star_rating_label svg path{";
        echo "fill:{$star_rating_color};";
    echo "}";

    echo ".ar_main_div_{$form_id} .setting_checkbox.arf_standard_checkbox .arf_checkbox_input_wrapper input[type=checkbox] + span,";
    echo ".ar_main_div_{$form_id} .setting_radio.arf_standard_radio .arf_radio_input_wrapper input[type=radio] + span{";
    echo "border-color:{$border_color};";
    echo "}";

        echo ".arfformfield .setting_checkbox.arf_custom_checkbox .arf_checkbox_input_wrapper input[type='checkbox']:checked + span {";
        echo "border-color:{$base_color};";
        echo "}";
        echo ".arfformfield .setting_checkbox.arf_custom_checkbox .arf_checkbox_input_wrapper input[type='checkbox']:checked + span i{";
        echo "display:block;";
        echo "height:auto; width:auto;";
        echo "color:{$base_color}";
        echo "}";
        echo ".ar_main_div_".$form_id." .arfformfield .setting_checkbox.arf_rounded_flat_checkbox:not(.arf_custom_checkbox) .arf_checkbox_input_wrapper input[type='checkbox']:checked + span{";
        echo "background:{$base_color};";
        echo "border-color:{$base_color};border-radius: 4px;-webkit-border-radius: 4px;-moz-border-radius: 4px;-o-border-radius: 4px;";
        echo "}";
        echo ".ar_main_div_".$form_id." .arfformfield .setting_checkbox.arf_standard_checkbox:not(.arf_custom_checkbox) .arf_checkbox_input_wrapper input[type='checkbox']:checked + span{";
        echo "background:{$base_color};";
        echo "border-color:{$base_color};";
        echo "}";

    $temp_label_font_size = str_replace('px','',$font_size);

        echo ".arfformfield .setting_radio.arf_custom_radio .arf_radio_input_wrapper input[type='radio']:checked + span {";
        echo "border-color:$base_color;";
        echo "}";
        echo ".arfformfield .setting_radio.arf_custom_radio .arf_radio_input_wrapper input[type='radio']:checked + span i{";
        echo "display:block;";
        echo "height:auto; width:auto;";
        echo "color:$base_color";
        echo "}";

        echo ".ar_main_div_{$form_id} .arfformfield .setting_radio.arf_rounded_flat_radio .arf_radio_input_wrapper input[type='radio']:checked + span::before{";
        echo "border:4px solid $base_color;";
        echo "}";
        echo ".ar_main_div_{$form_id} .arfformfield .setting_radio.arf_rounded_flat_radio .arf_radio_input_wrapper input[type='radio']:checked + span::after{";
        echo "-webkit-transform: scale(0.3);-o-transform: scale(0.3);-moz-transform: scale(0.3);transform: scale(0.3);-ms-transform: scale(0.3);background: $base_color;border: 2px solid $base_color;";
        echo "}";
        echo ".ar_main_div_{$form_id} .arfformfield .setting_radio.arf_standard_radio .arf_radio_input_wrapper input[type='radio']:checked + span{";
        echo "background:$base_color;";
        echo "border-color:$base_color;";
        echo "}";

    if( $temp_label_font_size > 20){
        
        echo ".ar_main_div_".$form_id." .setting_checkbox.arf_custom_checkbox .arf_checkbox_input_wrapper,";
        echo ".ar_main_div_".$form_id." .setting_checkbox.arf_custom_checkbox .arf_checkbox_input_wrapper input[type=\"checkbox\"],";

        echo ".ar_main_div_".$form_id." .setting_radio.arf_custom_radio .arf_radio_input_wrapper,";
        echo ".ar_main_div_".$form_id." .setting_radio.arf_custom_radio .arf_radio_input_wrapper input[type=\"radio\"],";
        
        echo ".ar_main_div_".$form_id." .setting_checkbox.arf_rounded_flat_checkbox .arf_checkbox_input_wrapper,";
        echo ".ar_main_div_".$form_id." .setting_checkbox.arf_rounded_flat_checkbox .arf_checkbox_input_wrapper input[type=\"checkbox\"],";
        
        echo ".ar_main_div_".$form_id." .setting_radio.arf_rounded_flat_radio .arf_radio_input_wrapper,";
        echo ".ar_main_div_".$form_id." .setting_radio.arf_rounded_flat_radio .arf_radio_input_wrapper input[type=\"radio\"],";

        echo ".ar_main_div_".$form_id." .setting_checkbox.arf_standard_checkbox .arf_checkbox_input_wrapper,";
        echo ".ar_main_div_".$form_id." .setting_checkbox.arf_standard_checkbox .arf_checkbox_input_wrapper input[type=\"checkbox\"],";
        
        echo ".ar_main_div_".$form_id." .setting_radio.arf_standard_radio .arf_radio_input_wrapper,";
        echo ".ar_main_div_".$form_id." .setting_radio.arf_standard_radio .arf_radio_input_wrapper input[type=\"radio\"]{";
        
        echo "height:".($temp_label_font_size + 2)."px !important;";
        echo "width:".($temp_label_font_size + 2)."px !important;";
        
        echo "}";

        echo ".ar_main_div_".$form_id." .setting_checkbox.arf_custom_checkbox .arf_checkbox_input_wrapper input[type=\"checkbox\"] + span,";
        echo ".ar_main_div_".$form_id." .setting_radio.arf_custom_radio .arf_radio_input_wrapper input[type=\"radio\"] + span,";

        echo ".ar_main_div_".$form_id." .setting_radio.arf_standard_radio .arf_radio_input_wrapper input[type=\"radio\"] + span,";
        
        echo ".ar_main_div_".$form_id." .setting_radio.arf_rounded_flat_radio .arf_radio_input_wrapper input[type=\"radio\"] + span::before,";
        echo ".ar_main_div_".$form_id." .setting_radio.arf_rounded_flat_radio .arf_radio_input_wrapper input[type=\"radio\"] + span::after,";

        echo ".ar_main_div_".$form_id." .setting_checkbox.arf_rounded_flat_checkbox .arf_checkbox_input_wrapper input[type=\"checkbox\"] + span,";
        echo ".ar_main_div_".$form_id." .setting_checkbox.arf_standard_checkbox .arf_checkbox_input_wrapper input[type=\"checkbox\"] + span{";
        
        echo "height:".($temp_label_font_size)."px !important;";
        echo "width:".($temp_label_font_size)."px !important;";
        
        echo "}";

        if( ($temp_label_font_size - 14) > 16 ){
            echo ".ar_main_div_{$form_id} .setting_radio.arf_custom_radio .arf_radio_input_wrapper input[type=\"radio\"] + span i,";
            echo ".ar_main_div_{$form_id} .setting_checkbox.arf_custom_checkbox .arf_checkbox_input_wrapper input[type=\"checkbox\"] + span i{";
            echo "font-size:".($temp_label_font_size - 14)."px !important;";
            echo "}";
        }

    }
    if($arfinputstyle == 'rounded'){
        if( $temp_label_font_size > 20 && $temp_label_font_size < 30 ){
            echo ".ar_main_div_{$form_id} .arfformfield .setting_radio.arf_rounded_flat_radio .arf_radio_input_wrapper input[type='radio']:checked + span::after{";
            echo "-webkit-transform: scale(0.4);-o-transform: scale(0.4);-moz-transform: scale(0.4);transform: scale(0.4);-ms-transform: scale(0.4);";
            echo "}";
        } else if( $temp_label_font_size > 30 ){
            echo ".ar_main_div_{$form_id} .arfformfield .setting_radio.arf_rounded_flat_radio .arf_radio_input_wrapper input[type='radio']:checked + span::after{";
            echo "-webkit-transform: scale(0.5);-o-transform: scale(0.5);-moz-transform: scale(0.5);transform: scale(0.5);-ms-transform: scale(0.5);";
            echo "}";
        }

    }
?>
    @media (max-width: 480px) {
        .ar_main_div_<?php echo $form_id; ?> .arf_prefix_suffix_wrapper {
            width:100% !important;
        }
    }

    .ar_main_div_<?php echo $form_id; ?> .picker-switch td span.arf-glyphicon-time,
    .ar_main_div_<?php echo $form_id; ?> .picker-switch td span.arf-glyphicon-calendar{
        background-color: <?php echo $base_color ;?>
    }

    
    .ar_main_div_<?php echo $form_id; ?> .arf-glyphicon-time:before,
    .ar_main_div_<?php echo $form_id; ?> .arf-glyphicon-calendar:before{
        color:<?php echo($arsettingcontroller->isColorDark($base_color) == '1')?'#ffffff':'#1A1A1A'. ' !important'?>;
    }

    .ar_main_div_<?php echo $form_id; ?> .arf_cal_header th, 
    .ar_main_div_<?php echo $form_id; ?> .arf_cal_month th{ 
        color :<?php echo($arsettingcontroller->isColorDark($base_color) == '1')?'#ffffff':'#1A1A1A'. ' !important'?>;
    }

    
    .ar_main_div_<?php echo $form_id; ?> .arfajax-file-upload{
        color: <?php echo $upload_text_color?>
    }
    .ar_main_div_<?php echo $form_id; ?> .arfajax-file-upload-img svg{
        fill : <?php echo $upload_text_color;?>
    }
    .ar_main_div_<?php echo $form_id; ?> .arfajax-file-upload{
        background : <?php echo $base_color;?>;
        border-color:<?php echo $base_color;?>;
    }

    .ar_main_div_<?php echo $form_id; ?> .arf-slider-handle{
        background:<?php echo $base_color; ?>;
    }

    .ar_main_div_<?php echo $form_id; ?> .arf-slider-handle.triangle{
        border-bottom-color:<?php echo $base_color; ?>;
    }

    .ar_main_div_<?php echo $form_id; ?> .slider-selection{
        background:<?php echo $slider_selection_color; ?> !important;
    }

    .ar_main_div_<?php echo $form_id; ?> .arf-slider-track{
        background:<?php echo $slider_track_color; ?> !important;
    }

    .ar_main_div_<?php echo $form_id; ?> .like_container .arf_like_btn.active{
        background:<?php echo $like_btn_color; ?>;
    }

    .ar_main_div_<?php echo $form_id; ?> .like_container .arf_dislike_btn.active{
        background:<?php echo $dislike_btn_color; ?>;
    }

    <?php if( $arf_divider_inherit_bg ){
        echo ".ar_main_div_{$form_id} .arf_heading_div{  background:{$section_background}; }";
    } ?>
    
    <?php if($arf_required_indicator == '1'){?>
        .ar_main_div_<?php echo $form_id; ?> .arf_main_label span.arf_edit_in_place+span,
        .ar_main_div_<?php echo $form_id; ?> span.arfcheckrequiredfield,.arfcheckrequiredfield{
            display:none;
        }
    <?php }else{?>
        .ar_main_div_<?php echo $form_id; ?> .arf_main_label span.arf_edit_in_place+span,
        .ar_main_div_<?php echo $form_id; ?> span.arfcheckrequiredfield,.arfcheckrequiredfield{
            display:inline-block;
        }
    <?php }

if (is_admin()) {
    $arf_prefix_width = '';

        if ($field_font_size < 10) $arf_prefix_width = '32px';
        else if ($field_font_size >= 10 && $field_font_size < 12) $arf_prefix_width = '34px';
        else if ($field_font_size >= 12 && $field_font_size < 14) $arf_prefix_width = '36px';
        else if ($field_font_size >= 14 && $field_font_size < 16) $arf_prefix_width = '38px';
        else if ($field_font_size >= 16 && $field_font_size < 18) $arf_prefix_width = '40px';
        else if ($field_font_size >= 18 && $field_font_size < 20) $arf_prefix_width = '42px';
        else if ($field_font_size >= 20 && $field_font_size < 22) $arf_prefix_width = '44px';
        else if ($field_font_size == 22) $arf_prefix_width = '46px';
        else if ($field_font_size == 24) $arf_prefix_width = '51px';
        else if ($field_font_size == 26) $arf_prefix_width = '53px';
        else if ($field_font_size == 28) $arf_prefix_width = '55px';
        else if ($field_font_size == 32) $arf_prefix_width = '60px';
        else if ($field_font_size == 34) $arf_prefix_width = '62px';
        else if ($field_font_size == 36) $arf_prefix_width = '64px';
        else if ($field_font_size == 38) $arf_prefix_width = '67px';
        else if ($field_font_size == 40) $arf_prefix_width = '70px';
    ?>

    .ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix_suffix_wrapper .arf_prefix_focus{
	   border:<?php echo $field_border_width . ' ' . $field_border_style . ' ' . $base_color.' !important'; ?>;
    }

    .ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix_suffix_wrapper .arf_suffix_focus{
        border:<?php echo $field_border_width . ' ' . $field_border_style . ' ' . $base_color.' !important'; ?>;
    }

    
    .ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon,
    .ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon{
    background:<?php echo $prefix_suffix_bg_color; ?>;
    color:<?php echo $prefix_suffix_icon_color; ?>;
    border:<?php echo $field_border_width . ' ' . $field_border_style . ' ' . $border_color; ?>;
    font-size:<?php echo $field_font_size; ?>;
    width:<?php echo $arf_prefix_width; ?>;
    padding: 0 10px;
    }

    .ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix_suffix_wrapper.arf_prefix_only input[type="text"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete), 
    .ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix_suffix_wrapper.arf_prefix_only input[type="password"],
    .ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix_suffix_wrapper.arf_prefix_only input[type="email"],
    .ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix_suffix_wrapper.arf_prefix_only input[type="number"],
    .ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix_suffix_wrapper.arf_prefix_only input[type="url"],
    .ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix_suffix_wrapper.arf_prefix_only input[type="tel"] {
    
    <?php
    if(is_rtl()){ ?>
        border-right:none !important;
        border-top-right-radius:0 !important;
        border-bottom-right-radius:0 !important;
        -webkit-border-top-right-radius:0 !important;
        -webkit-border-bottom-right-radius:0 !important;
        -o-border-top-right-radius:0 !important;
        -o-border-bottom-right-radius:0 !important;
        -moz-border-top-right-radius:0 !important;
        -moz-border-bottom-right-radius:0 !important;
    <?php } else { ?>
        border-left:none !important;
        border-top-left-radius:0 !important;
        border-bottom-left-radius:0 !important;
        -webkit-border-top-left-radius:0 !important;
        -webkit-border-bottom-left-radius:0 !important;
        -o-border-top-left-radius:0 !important;
        -o-border-bottom-left-radius:0 !important;
        -moz-border-top-left-radius:0 !important;
        -moz-border-bottom-left-radius:0 !important;
    <?php } ?>
    }

    .ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix_suffix_wrapper .arf_editor_prefix_icon{
    <?php
    if(is_rtl()){ ?>
        border-top-right-radius:<?php echo $border_radius; ?>;
        border-bottom-right-radius:<?php echo $border_radius; ?>;
        -webkit-border-top-right-radius:<?php echo $border_radius; ?>;
        -webkit-border-bottom-right-radius:<?php echo $border_radius; ?>;
        -o-border-top-right-radius:<?php echo $border_radius; ?>;
        -o-border-bottom-right-radius:<?php echo $border_radius; ?>;
        -moz-border-top-right-radius:<?php echo $border_radius; ?>;
        -moz-border-bottom-right-radius:<?php echo $border_radius; ?>;
    <?php } else { ?>
        border-top-left-radius:<?php echo $border_radius; ?>;
        border-bottom-left-radius:<?php echo $border_radius; ?>;
        -webkit-border-top-left-radius:<?php echo $border_radius; ?>;
        -webkit-border-bottom-left-radius:<?php echo $border_radius; ?>;
        -o-border-top-left-radius:<?php echo $border_radius; ?>;
        -o-border-bottom-left-radius:<?php echo $border_radius; ?>;
        -moz-border-top-left-radius:<?php echo $border_radius; ?>;
        -moz-border-bottom-left-radius:<?php echo $border_radius; ?>;
    <?php } ?>
    }

    .ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix_suffix_wrapper.arf_suffix_only input[type="text"]:not(.inplace_field):not(.arf_field_option_input_text):not(.arf_autocomplete), 
    .ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix_suffix_wrapper.arf_suffix_only input[type="password"],
    .ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix_suffix_wrapper.arf_suffix_only input[type="email"],
    .ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix_suffix_wrapper.arf_suffix_only input[type="number"],
    .ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix_suffix_wrapper.arf_suffix_only input[type="url"],
    .ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix_suffix_wrapper.arf_suffix_only input[type="tel"] {
    <?php
    if(is_rtl()){ ?>
        border-left:none !important;
        border-top-left-radius:0 !important;
        border-bottom-left-radius:0 !important;
        -webkit-border-top-left-radius:0 !important;
        -webkit-border-bottom-left-radius:0 !important;
        -o-border-top-left-radius:0 !important;
        -o-border-bottom-left-radius:0 !important;
        -moz-border-top-left-radius:0 !important;
        -moz-border-bottom-left-radius:0 !important;
    <?php } else { ?>
        border-right:none !important;
        border-top-right-radius:0 !important;
        border-bottom-right-radius:0 !important;
        -webkit-border-top-right-radius:0 !important;
        -webkit-border-bottom-right-radius:0 !important;
        -o-border-top-right-radius:0 !important;
        -o-border-bottom-right-radius:0 !important;
        -moz-border-top-right-radius:0 !important;
        -moz-border-bottom-right-radius:0 !important;
    <?php } ?>
    
    }

    .ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix_suffix_wrapper .arf_editor_suffix_icon {
    <?php
    if(is_rtl()){ ?>
        border-top-left-radius:<?php echo $border_radius; ?>;
        border-bottom-left-radius:<?php echo $border_radius; ?>;
        -webkit-border-top-left-radius:<?php echo $border_radius; ?>;
        -webkit-border-bottom-left-radius:<?php echo $border_radius; ?>;
        -o-border-top-left-radius:<?php echo $border_radius; ?>;
        -o-border-bottom-left-radius:<?php echo $border_radius; ?>;
        -moz-border-top-left-radius:<?php echo $border_radius; ?>;
        -moz-border-bottom-left-radius:<?php echo $border_radius; ?>;
    <?php } else { ?>
        border-top-right-radius:<?php echo $border_radius; ?>;
        border-bottom-right-radius:<?php echo $border_radius; ?>;
        -webkit-border-top-right-radius:<?php echo $border_radius; ?>;
        -webkit-border-bottom-right-radius:<?php echo $border_radius; ?>;
        -o-border-top-right-radius:<?php echo $border_radius; ?>;
        -o-border-bottom-right-radius:<?php echo $border_radius; ?>;
        -moz-border-top-right-radius:<?php echo $border_radius; ?>;
        -moz-border-bottom-right-radius:<?php echo $border_radius; ?>;
    <?php } ?>
    
    }

    .ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix_suffix_wrapper.arf_both_pre_suffix input:not(.input_field):not(.arf_autocomplete),
    .ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix_suffix_wrapper.arf_both_pre_suffix input[type="text"]:not(.input_field):not(.arf_autocomplete),
    .ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix_suffix_wrapper.arf_both_pre_suffix input[type="email"]:not(.input_field):not(.arf_autocomplete),
    .ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix_suffix_wrapper.arf_both_pre_suffix input[type="phone"]:not(.input_field):not(.arf_autocomplete),
    .ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix_suffix_wrapper.arf_both_pre_suffix input[type="tel"]:not(.input_field):not(.arf_autocomplete),
    .ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix_suffix_wrapper.arf_both_pre_suffix input[type="password"]:not(.input_field):not(.arf_autocomplete),
    .ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix_suffix_wrapper.arf_both_pre_suffix input[type="hidden"]:not(.input_field):not(.arf_autocomplete),
    .ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix_suffix_wrapper.arf_both_pre_suffix input[type="number"]:not(.input_field):not(.arf_autocomplete),
    .ar_main_div_<?php echo $form_id; ?> .arf_editor_prefix_suffix_wrapper.arf_both_pre_suffix input[type="url"]:not(.input_field):not(.arf_autocomplete){
    
    <?php
    if(is_rtl()){ ?>
        border-right:none !important;
        border-left:none!important;
        border-radius:0 !important;
        -webkit-border-radius:0 !important;
        -o-border-radius:0 !important;
        -moz-border-radius:0 !important;
    <?php } else { ?>
        border-left:none !important;
        border-right:none!important;
        border-radius:0 !important;
        -webkit-border-radius:0 !important;
        -o-border-radius:0 !important;
        -moz-border-radius:0 !important;
    <?php } ?>
    }
    
    .ar_main_div_<?php echo $form_id; ?> .bootstrap-select.btn-group .arfbtn .arf_caret{
    <?php
    if(is_rtl()){ ?>
        left:12px;
        right:inherit;
    <?php } else { ?>
        right:12px;        
    <?php } ?>

    }
   

    <?php
}

$use_saved = isset($use_saved) ? $use_saved : '';
$new_values = isset($new_values) ? $new_values : array();
do_action('arf_outsite_print_style', $new_values, $use_saved, $form_id);
?>
