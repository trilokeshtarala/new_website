<?php

class ARForms_CS {

    public function ui() {
        return array(
            'title' => addslashes(esc_html__('ARFORMS', 'ARForms')),
            'autofocus' => array(
                'heading' => 'h4.arforms-cs-heading',
                'content' => '.arforms-cs'
            ),
            'icon_group' => 'ARFORMS'
        );
    }

    public function update_build_shortcode_atts($atts) {

        if (!isset($atts['style'])) {
            $atts['style'] = '';
        }

        if (isset($atts['background_color'])) {
            $atts['style'] .= ' background-color: ' . $atts['background_color'] . ';';
            unset($atts['background_color']);
        }

        return $atts;
    }

}