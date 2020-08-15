<?php

$arforms_final_shortcode = '';
$form_id = isset($atts['arf_forms']) ? $atts['arf_forms'] : '';
if ($form_id != '') {
    if ($atts['arf_forms_include_type'] == 'internal') {
        $arforms_final_shortcode .= "[ARForms id=" . $form_id . "]";
    } else if ($atts['arf_forms_include_type'] == 'external') {
        $arf_show_close_button = 'yes';
        if (!$atts['arf_show_close_button']) {
            $arf_show_close_button = 'no';
        }

        

        $arf_show_full_screen = 'yes';
        if (!$atts['arf_show_full_screen']) {
            $arf_show_full_screen = 'no';
        }
        

        if($atts['arf_link_type'] == 'onclick'){
           $atts['arf_link_type'] =  $atts['arf_onclick_type'];
        }
        

        $arforms_final_shortcode .= "[ARForms_popup id=" . $form_id . " desc='" . $atts['arf_link_caption'] . "' type='" . $atts['arf_link_type'] . "' height='" . $atts['arf_popup_height'] . "' width='" . $atts['arf_popup_width'] . "' overlay='" . $atts['arf_background_overlay'] . "' is_close_link='" . $arf_show_close_button . "' modal_bgcolor='" . $atts['arf_background_overlay_color'] . "' ";

        if ($atts['arf_link_type'] == 'button' || $atts['arf_link_type'] == 'sticky' || $atts['arf_link_type'] == 'fly' || $atts['arf_link_type'] == 'timer' || $atts['arf_link_type'] == 'on_exit' || $atts['arf_link_type'] == 'on_idle') {
            $arforms_final_shortcode .= " bgcolor='" . $atts['arf_button_background_color'] . "' txtcolor='" . $atts['arf_button_text_color'] . "' ";
        }

        if ($atts['arf_link_type'] == 'sticky') {
            $arforms_final_shortcode .= "  position='" . $atts['arf_link_position'] . "'  ";
        } else if ($atts['arf_link_type'] == 'fly') {
            $arforms_final_shortcode .= "  position='" . $atts['arf_fly_position'] . "'  angle='" . $atts['arf_fly_button_angle'] . "' ";
        }

        if ($atts['arf_link_type'] == 'onload') {
            $arforms_final_shortcode .= " ";
        } else if ($atts['arf_link_type'] == 'scroll') {

            $arforms_final_shortcode .= "  on_scroll='" . $atts['arf_scroll_per'] . "' ";
        }

        if($atts['arf_link_type'] != 'fly' || $atts['arf_link_type'] != 'sticky'){
            $arforms_final_shortcode .= " modaleffect='".$atts['arf_modal_effect']."' is_fullscreen='".$arf_show_full_screen."' ";
        }

        if($atts['arf_link_type'] == 'timer'){
            $arforms_final_shortcode .= " on_delay='".$atts['arf_onload_time']." ";
        }

        if($atts['arf_link_type'] == 'on_idle'){
            $arforms_final_shortcode .= " inactive_min='".$atts['arf_inact_time']."' ";
        }


        $arforms_final_shortcode .= "]";
        
        if (is_admin()) {
            wp_print_scripts('arforms');
            global $arfversion;
            wp_register_script('arf-modal-js', ARFURL . '/js/arf_modal_js.js', array('jquery'), $arfversion);
            wp_print_scripts('arf-modal-js');
            wp_register_script('arfbootstrap-js', ARFURL . '/bootstrap/js/bootstrap.min.js', array('jquery'), $arfversion);
            wp_print_scripts('arfbootstrap-js');
        }

    }
}

echo do_shortcode($arforms_final_shortcode);
