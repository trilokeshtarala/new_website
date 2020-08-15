<?php

global $armainhelper, $arformhelper, $arfversion, $wpdb, $arfform;
$arf_cs_control = array();

$forms = $arfform->getAll("is_template=0 AND (status is NULL OR status = '' OR status = 'published')", ' ORDER BY name');



$arf_forms = array();

$arf_forms[0]['value'] = '';
$arf_forms[0]['label'] = addslashes(esc_html__('- Select form -', 'ARForms'));

if (!empty($forms)) {
    $n = 1;
    foreach ($forms as $key => $forms_data) {
        $arf_forms[$n]['value'] = $forms_data->id;
        $arf_forms[$n]['label'] = $forms_data->name . ' [' . $forms_data->id . ']';
        $n++;
    }
}

$arf_cs_control['arf_forms'] = array(
    'type' => 'select',
    'ui' => array(
        'title' => addslashes(esc_html__('Select a form to insert into page', 'ARForms'))
    ),
    'options' => array(
        'choices' => $arf_forms
    )
);

$arf_cs_control['arf_forms_include_type'] = array(
    'type' => 'select',
    'ui' => array(
        'title' => addslashes(esc_html__('How you want to include this form into page?', 'ARForms'))
    ),
    'options' => array(
        'choices' => array(
            array('value' => 'internal', 'label' => addslashes(esc_html__('Internal', 'ARForms'))),
            array('value' => 'external', 'label' => addslashes(esc_html__('Modal(popup) window', 'ARForms'))),
        )
    )
);
$arf_cs_control['arf_link_type'] = array(
    'type' => 'select',
    'ui' => array(
        'title' => addslashes(esc_html__('Modal Trigger Type', 'ARForms'))
    ),
    'options' => array(
        'choices' => array(
            array('value' => 'onclick', 'label' => addslashes(esc_html__('On click', 'ARForms'))),
            array('value' => 'onload', 'label' => addslashes(esc_html__('On Page Load', 'ARForms'))),
            array('value' => 'scroll', 'label' => addslashes(esc_html__('On Page Scroll', 'ARForms'))),
            array('value' => 'timer', 'label' => addslashes(esc_html__('On Timer(Scheduled)', 'ARForms'))),
            array('value' => 'on_exit', 'label' => addslashes(esc_html__('On Exit(Exit Intent)', 'ARForms'))),
            array('value' => 'on_idle', 'label' => addslashes(esc_html__('On Idle', 'ARForms')))
        )
    ),
    'condition' => array(
        'arf_forms_include_type' => 'external'
    )
);
$arf_cs_control['arf_onclick_type'] = array(
    'type' => 'select',
    'ui' => array(
        'title' => esc_html__('Click Types', 'ARForms')
    ),
    'options' => array(
        'choices' => array(
            array('value' => 'link', 'label' => addslashes(esc_html__('Link', 'ARForms'))),
            array('value' => 'button', 'label' => addslashes(esc_html__('Button', 'ARForms'))),
            array('value' => 'sticky', 'label' => addslashes(esc_html__('Sticky', 'ARForms'))),
            array('value' => 'fly', 'label' => addslashes(esc_html__('Fly (sidebar)', 'ARForms'))),
        )
    ),
    'condition' => array(
        'arf_forms_include_type' => 'external',
        'arf_link_type' => array('onclick')
    )
);

$arf_cs_control['arf_link_caption'] = array(
    'type' => 'text',
    'ui' => array(
        'title' => addslashes(esc_html__('Caption', 'ARForms'))
    ),
    'suggest' => addslashes(esc_html__('Caption', 'ARForms')),
    'content' => '',
    'condition' => array(
        'arf_forms_include_type' => 'external',
        'arf_link_type' => array('onclick')
    )
);

$arf_cs_control['arf_onload_time'] = array(
    'type' => 'text',
    'ui' => array(
        'title' => addslashes(esc_html__('Open popup after page load', 'ARForms'))
    ),
    'suggest' => addslashes(esc_html__('in second', 'ARForms')),
    'content' => '',
    'condition' => array(
        'arf_forms_include_type' => 'external',
        'arf_link_type' => 'timer'
    )
);

$arf_cs_control['arf_scroll_per'] = array(
    'type' => 'text',
    'ui' => array(
        'title' => addslashes(esc_html__('Open popup when user scroll % of page after page load', 'ARForms'))
    ),
    'suggest' => addslashes(esc_html__(' %  (eg. 100% - end of page)', 'ARForms')),
    'content' => '',
    'condition' => array(
        'arf_forms_include_type' => 'external',
        'arf_link_type' => 'scroll'
    )
);



$arf_cs_control['arf_link_position'] = array(
    'type' => 'select',
    'ui' => array(
        'title' => addslashes(esc_html__('Link Position', 'ARForms'))
    ),
    'options' => array(
        'choices' => array(
            array('value' => 'top', 'label' => addslashes(esc_html__('Top', 'ARForms'))),
            array('value' => 'bottom', 'label' => addslashes(esc_html__('Bottom', 'ARForms'))),
            array('value' => 'left', 'label' => addslashes(esc_html__('Left', 'ARForms'))),
            array('value' => 'right', 'label' => addslashes(esc_html__('Right', 'ARForms'))),
        )
    ),
    'condition' => array(
        'arf_forms_include_type' => 'external',
        'arf_onclick_type' => 'sticky'
    )
);

$arf_cs_control['arf_fly_position'] = array(
    'type' => 'select',
    'ui' => array(
        'title' => addslashes(esc_html__('Link Position', 'ARForms'))
    ),
    'options' => array(
        'choices' => array(
            array('value' => 'left', 'label' => addslashes(esc_html__('Left', 'ARForms'))),
            array('value' => 'right', 'label' => addslashes(esc_html__('Right', 'ARForms'))),
        )
    ),
    'condition' => array(
        'arf_forms_include_type' => 'external',
        'arf_onclick_type' => 'fly'
    )
);


$arf_cs_control['arf_background_overlay'] = array(
    'type' => 'select',
    'ui' => array(
        'title' => addslashes(esc_html__('Background Overlay', 'ARForms'))
    ),
    'options' => array(
        'choices' => array(
            array('value' => '0', 'label' => addslashes(esc_html__('0 (None)', 'ARForms'))),
            array('value' => '0.1', 'label' => addslashes(esc_html__('10%', 'ARForms'))),
            array('value' => '0.2', 'label' => addslashes(esc_html__('20%', 'ARForms'))),
            array('value' => '0.3', 'label' => addslashes(esc_html__('30%', 'ARForms'))),
            array('value' => '0.4', 'label' => addslashes(esc_html__('40%', 'ARForms'))),
            array('value' => '0.5', 'label' => addslashes(esc_html__('50%', 'ARForms'))),
            array('value' => '0.6', 'label' => addslashes(esc_html__('60%', 'ARForms'))),
            array('value' => '0.7', 'label' => addslashes(esc_html__('70%', 'ARForms'))),
            array('value' => '0.8', 'label' => addslashes(esc_html__('80%', 'ARForms'))),
            array('value' => '0.9', 'label' => addslashes(esc_html__('90%', 'ARForms'))),
            array('value' => '1', 'label' => addslashes(esc_html__('100%', 'ARForms'))),
        )
    ),
    'condition' => array(
        'arf_forms_include_type' => 'external',
        'arf_onclick_type' => array('link', 'button'),
        'arf_link_type' => array('onload', 'scroll', 'on_exit','onclick')
    )
);

$arf_cs_control['arf_background_overlay_color'] = array(
    'type' => 'color',
    'ui' => array(
        'title' => addslashes(esc_html__('Background Overlay', 'ARForms'))
    ),
    'condition' => array(
        'arf_forms_include_type' => 'external',
        'arf_onclick_type' => array('link', 'button'),
        'arf_link_type' => array('onload', 'scroll', 'on_exit','onclick')
    )
);

$arf_cs_control['arf_show_close_button'] = array(
    'type' => 'toggle',
    'ui' => array(
        'title' => addslashes(esc_html__('Show Close Button', 'ARForms'))
    ),
    'condition' => array(
        'arf_forms_include_type' => 'external',
    )
);

$arf_cs_control['arf_button_background_color'] = array(
    'type' => 'color',
    'ui' => array(
        'title' => addslashes(esc_html__('Button Background Color', 'ARForms'))
    ),
    'condition' => array(
        'arf_forms_include_type' => 'external',
        'arf_onclick_type' => array('button', 'sticky', 'fly')
    )
);


$arf_cs_control['arf_button_text_color'] = array(
    'type' => 'color',
    'ui' => array(
        'title' => addslashes(esc_html__('Button Text Color', 'ARForms'))
    ),
    'condition' => array(
        'arf_forms_include_type' => 'external',
        'arf_onclick_type' => array('button', 'sticky', 'fly')
    )
);


$arf_cs_control['arf_popup_width'] = array(
    'type' => 'text',
    'ui' => array(
        'title' => addslashes(esc_html__('Width', 'ARForms'))
    ),
    'suggest' => addslashes(esc_html__('In px (Form width will be overwritten)', 'ARForms')),
    'content' => '',
    'condition' => array(
        'arf_forms_include_type' => 'external'
    )
);


$arf_cs_control['arf_fly_button_angle'] = array(
    'type' => 'select',
    'ui' => array(
        'title' => addslashes(esc_html__('Button angle', 'ARForms'))
    ),
    'options' => array(
        'choices' => array(
            array('value' => '0', 'label' => addslashes(esc_html__('0', 'ARForms'))),
            array('value' => '90', 'label' => addslashes(esc_html__('90', 'ARForms'))),
            array('value' => '-90', 'label' => addslashes(esc_html__('-90', 'ARForms'))),
        )
    ),
    'condition' => array(
        'arf_forms_include_type' => 'external',
        'arf_onclick_type' => array('fly')
    )
);



$arf_cs_control['arf_inact_time'] = array(
    'type' => 'text',
    'ui' => array(
        'title' => addslashes(esc_html__('Show after user is inactive for (in minutes)', 'ARForms'))
    ),
    'suggest' => addslashes(esc_html__('In Minute', 'ARForms')),
    'content' => '0.025',
    'condition' => array(
        'arf_forms_include_type' => 'external',
        'arf_link_type' => array('on_idle')
    )
);

$arf_cs_control['arf_modal_effect'] = array(
    'type' => 'select',
    'ui' => array(
        'title' => addslashes(esc_html__('Animation Effect', 'ARForms'))
    ),
    'options' => array(
        'choices' => array(
            array('value' => 'no_animation', 'label' => addslashes(esc_html__('No Animation','ARForms') )),
            array('value' => 'fade_in', 'label' => addslashes(esc_html__('Fade-In', 'ARForms'))),
            array('value' => 'slide_in_top', 'label' => addslashes(esc_html__('Slide In Top', 'ARForms'))),
            array('value' => 'slide_in_bottom', 'label' => addslashes(esc_html__('Slide In Bottom', 'ARForms'))),
            array('value' => 'slide_in_right', 'label' => addslashes(esc_html__('Slide In right', 'ARForms'))),
            array('value' => 'slide_in_left', 'label' => addslashes(esc_html__('Slide In Left', 'ARForms'))),
            array('value' => 'zoom_in', 'label' => addslashes(esc_html__('Zoom In','ARForms')))
        )
    ),
    'condition' => array(
        'arf_forms_include_type' => 'external',
    )
);

$arf_cs_control['arf_show_full_screen'] = array(
    'type' => 'toggle',
    'ui' => array(
        'title' => addslashes(esc_html__('Show Full Screen Popup', 'ARForms'))
    ),
    'condition' => array(
        'arf_forms_include_type' => 'external',
    )
);



return $arf_cs_control;