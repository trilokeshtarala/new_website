<?php

class arfieldhelper {

    function __construct() {

        add_filter('arfgetdefaultvalue', array($this, 'get_default_value'), 10, 3);

        add_filter('arfreplaceshortcodes', array($this, 'replace_html_shortcodes'), 10, 5);

        add_filter('arfgetpagedfields', array($this, 'get_form_fields'), 10, 3);

        add_filter('arfothercustomhtml', array($this, 'get_default_html'), 10, 2);

        add_filter('arfsetupeditfieldvars', array($this, 'setup_new_field_vars'), 10);

        add_filter('arfsetupnewfieldsvars', array($this, 'setup_new_vars'), 10, 2);

        add_filter('arfbeforereplaceshortcodes', array($this, 'before_replace_shortcodes'), 10, 4);

        add_filter('arfpostedfieldids', array($this, 'posted_field_ids'));

        add_filter('arf_check_for_running_total_field', array($this, 'arf_check_running_total_field_func'), 10, 5);

        add_action('arf_material_style_editor_content', array($this, 'arf_add_material_style_block'), 10, 5);

        add_filter('arf_form_fields_outside',array($this, 'arf_add_blank_msg_from_globalsetting'),100,2);

    }

    function get_default_value($value, $field, $dynamic_default = true, $return_array = false) {
        if (is_array(maybe_unserialize($value)))
            return $value;
        if ($field and $dynamic_default) {
            $field->field_options = maybe_unserialize($field->field_options);
            if (isset($field->field_options['dyn_default_value']) and ! empty($field->field_options['dyn_default_value'])) {
                $prev_val = $value;
                $value = $field->field_options['dyn_default_value'];
            }
        }

        preg_match_all("/\[(date|time|email|login|display_name|first_name|last_name|user_meta|post_meta|post_id|post_title|post_author_email|ip_address|auto_id|get|get-(.?)|\d*)\b(.*?)(?:(\/))?\]/s", $value, $matches, PREG_PATTERN_ORDER);

        if (!isset($matches[0]))
            return $value;
        foreach ($matches[0] as $match_key => $val) {
            switch ($val) {
                case '[date]':
                    global $style_settings;
                    $new_value = date_i18n($style_settings->date_format, strtotime(current_time('mysql')));
                    break;
                case '[time]':
                    $new_value = date('H:i:s', strtotime(current_time('mysql')));
                    break;
                case '[email]':
                    global $current_user;
                    $new_value = (isset($current_user->user_email)) ? $current_user->user_email : '';
                    break;
                case '[login]':
                    global $current_user;
                    $new_value = (isset($current_user->user_login)) ? $current_user->user_login : '';
                    break;
                case '[display_name]':
                    global $current_user;
                    $new_value = (isset($current_user->display_name)) ? $current_user->display_name : '';
                    break;
                case '[first_name]':
                    global $current_user;
                    $new_value = (isset($current_user->user_firstname)) ? $current_user->user_firstname : '';
                    break;
                case '[last_name]':
                    global $current_user;
                    $new_value = (isset($current_user->user_lastname)) ? $current_user->user_lastname : '';
                    break;
                case '[post_id]':
                    global $post;
                    if ($post)
                        $new_value = $post->ID;
                    break;
                case '[post_title]':
                    global $post;
                    if ($post)
                        $new_value = $post->post_title;
                    break;
                case '[post_author_email]':
                    $new_value = get_the_author_meta('user_email');
                    break;
                case '[user_id]':
                    global $user_ID;
                    $new_value = $user_ID ? $user_ID : '';
                    break;
                case '[ip_address]':
                    $new_value = $_SERVER['REMOTE_ADDR'];
                    break;
                default:
                    $atts = shortcode_parse_atts(stripslashes($matches[3][$match_key]));
                    $shortcode = $matches[1][$match_key];

                    if (preg_match("/\[get-(.?)\b(.*?)?\]/s", $val)) {
                        $param = str_replace('[get-', '', $val);
                        if (preg_match("/\[/s", $param))
                            $val .= ']';
                        else
                            $param = trim($param, ']');
                        global $armainhelper;

                        $new_value = $armainhelper->get_param($param);
                        if (is_array($new_value) and ! $return_array)
                            $new_value = implode(', ', $new_value);
                    }else {
                        switch ($shortcode) {
                            case 'get':
                                $new_value = '';
                                if (isset($atts['param'])) {
                                    if (strpos($atts['param'], '&#91;')) {
                                        $atts['param'] = str_replace('&#91;', '[', $atts['param']);
                                        $atts['param'] = str_replace('&#93;', ']', $atts['param']);
                                    }
                                    global $armainhelper;

                                    $new_value = $armainhelper->get_param($atts['param'], false);
                                    if (!$new_value) {
                                        global $wp_query;
                                        if (isset($wp_query->query_vars[$atts['param']]))
                                            $new_value = $wp_query->query_vars[$atts['param']];
                                    }
                                    if (!$new_value and isset($atts['default']))
                                        $new_value = $atts['default'];
                                    else if (!$new_value and isset($prev_val))
                                        $new_value = $prev_val;
                                }

                                if (is_array($new_value) and ! $return_array)
                                    $new_value = implode(', ', $new_value);
                                break;
                            case'auto_id':
                                global $arfrecordmeta;

                                $last_entry = $arfrecordmeta->get_max($field);

                                if (!$last_entry and isset($atts['start']))
                                    $new_value = (int) $atts['start'];

                                if (!isset($new_value))
                                    $new_value = $last_entry + 1;
                                break;
                            case 'user_meta':
                                if (isset($atts['key'])) {
                                    global $current_user;
                                    $new_value = (isset($current_user->{$atts['key']})) ? $current_user->{$atts['key']} : '';
                                }
                                break;
                            case 'post_meta':
                                if (isset($atts['key'])) {
                                    global $post;
                                    if ($post) {
                                        $post_meta = get_post_meta($post->ID, $atts['key'], true);
                                        if ($post_meta)
                                            $new_value = $post_meta;
                                    }
                                }
                                break;
                            default:
                                if (is_numeric($shortcode)) {

                                    global $armainhelper;
                                    $new_value = $armainhelper->get_param('item_meta[' . $shortcode . ']', false);

                                    if (!$new_value and isset($atts['default']))
                                        $new_value = $atts['default'];

                                    if (is_array($new_value) and ! $return_array)
                                        $new_value = implode(', ', $new_value);
                                }else {
                                    $new_value = $val;
                                }
                                break;
                        }
                    }
            }
            if (!isset($new_value))
                $new_value = '';

            if (is_array($new_value))
                $value = $new_value;
            else
                $value = str_replace($val, $new_value, $value);
            unset($new_value);
        }
        return do_shortcode($value);
    }

    function setup_new_field_vars($values) {

        global $arfieldhelper;

        $values['field_options'] = maybe_unserialize($values['field_options']);
        foreach ($arfieldhelper->get_default_field_opts($values) as $opt => $default)
            $values[$opt] = (isset($values['field_options'][$opt])) ? $values['field_options'][$opt] : $default;
        return $values;
    }

    function setup_new_vars($values, $field) {


        $values['use_key'] = false;

        $field->field_options = maybe_unserialize($field->field_options);
        foreach ($this->get_default_field_opts($values, $field) as $opt => $default)
            $values[$opt] = (isset($field->field_options[$opt]) && $field->field_options[$opt] != '') ? $field->field_options[$opt] : $default;
        $values['hide_field'] = (array) $values['hide_field'];
        $values['hide_field_cond'] = (array) $values['hide_field_cond'];
        $values['hide_opt'] = (array) $values['hide_opt'];
        if ($values['type'] == 'date') {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $values['value'])) {
                global $style_settings, $armainhelper;
                $values['value'] = $armainhelper->convert_date($values['value'], 'Y-m-d', $style_settings->date_format);
            }
        } else if (!empty($values['options'])) {

            if (is_array($values['options'])) {
                foreach ($values['options'] as $val_key => $val_opt) {
                    if (is_array($val_opt)) {
                        foreach ($val_opt as $opt_key => $opt) {
                            $values['options'][$val_key][$opt_key] = $this->get_default_value($opt, $field, false);
                            unset($opt_key);
                            unset($opt);
                        }
                    } else {
                        $values['options'][$val_key] = $this->get_default_value($val_opt, $field, false);
                    }
                    unset($val_key);
                    unset($val_opt);
                }
            }
        }

        if (is_array($values['value'])) {
            foreach ($values['value'] as $val_key => $val)
                $values['value'][$val_key] = apply_filters('arfgetdefaultvalue', $val, $field);
        } else if (!empty($values['value'])) {
            $values['value'] = apply_filters('arfgetdefaultvalue', $values['value'], $field);
        }

        return $values;
    }

    function field_selection() {
        $fields = apply_filters('arfavailablefields', array(
            'text' => array(
                'icon' => '<svg viewBox="0 0 30 30"><g id="single_line"><path fill="#4E5462" fill-rule="evenodd" clip-rule="evenodd" d="M1.489,28.849V0.85h27.99v27.999H1.489z M27.501,2.892H3.499v23.915h24.002V2.892z M23.493,12.828H7.492v-2.001h16.001V12.828z"/></g>
                        </svg>',
                'label' => addslashes(esc_html__('Single Line Text', 'ARForms')),
            ),
            'textarea' => array(
                'icon' => '<svg viewBox="0 0 30 30"><g id="multi_line"><path fill="#4E5462" fill-rule="evenodd" clip-rule="evenodd" d="M1.489,28.845V0.85h27.987v27.995H1.489z M27.498,2.891H3.499v23.913h23.999V2.891z M20.49,15.837H8.478v-2.02H20.49V15.837z M23.524,21.836H8.479v-2.02h15.045V21.836z M8.479,7.819h15.045v2.02H8.479V7.819z"/></g></svg>',
                'label' => addslashes(esc_html__('Multiline Text', 'ARForms'))
            ),
            'checkbox' => array(
                'icon' => '<svg viewBox="0 0 30 30"><g id="checkbox"><path fill="#4E5462" fill-rule="evenodd" clip-rule="evenodd" d="M1.489,28.845V0.85h27.987v27.995H1.489z M27.498,2.891H3.499v23.913h23.999V2.891z M12.777,18.35l9.928-9.929l1.613,1.613L12.936,21.417l-0.159-0.158l-0.158,0.158l-5.84-5.839l1.613-1.613L12.777,18.35z"/></g></svg>',
                'label' => addslashes(esc_html__('Checkboxes', 'ARForms'))
            ),
            'radio' => array(
                'icon' => '<svg viewBox="0 0 30 30"><g id="radio"><path fill="#4E5462" fill-rule="evenodd" clip-rule="evenodd" d="M15.491,28.853c-7.733,0-14.002-6.268-14.002-14.001S7.757,0.85,15.491,0.85c7.732,0,14.001,6.269,14.001,14.002S23.223,28.853,15.491,28.853z M15.491,2.846c-6.631,0-12.006,5.375-12.006,12.006S8.86,26.858,15.491,26.858c6.63,0,12.006-5.375,12.006-12.006S22.121,2.846,15.491,2.846zM15.491,19.52c-2.579,0-4.669-2.09-4.669-4.668s2.09-4.669,4.669-4.669c2.578,0,4.668,2.091,4.668,4.669S18.069,19.52,15.491,19.52z"/></g></svg>',
                'label' => addslashes(esc_html__('Radio Buttons', 'ARForms'))
            ),
            'select' => array(
                'icon' => '<svg viewBox="0 0 30 30"><g id="dropdown"><path fill="#4E5462" fill-rule="evenodd" clip-rule="evenodd" d="M1.489,28.845V0.85h27.987v27.995H1.489z M27.498,2.891H3.499v23.912h23.999V2.891z M15.517,15.981l6.127-6.127l2,2l-8.001,8.002l-0.126-0.127l-0.126,0.127L7.39,11.854l2-2L15.517,15.981z"/></g></svg>',
                'label' => addslashes(esc_html__('Dropdown', 'ARForms'))
            ),
            'file' => array(
                'icon' => '<svg viewBox="0 0 30 30"><g id="file_upload"><path fill="#4E5462" fill-rule="evenodd" clip-rule="evenodd" d="M27.513,15.877h1.999v9.996h-1.999V15.877z M25.102,12.172c-0.236,0-0.472-0.092-0.648-0.275l-7.548-7.859v17.914c0,0.499-0.404,0.902-0.902,0.902s-0.902-0.404-0.902-0.902V4.039l-7.548,7.858c-0.347,0.357-0.917,0.367-1.275,0.021C5.919,11.572,5.911,11,6.256,10.643l8.999-9.366c0.161-0.25,0.428-0.427,0.748-0.427c0.318,0,0.586,0.176,0.746,0.427l9.001,9.364c0.347,0.359,0.337,0.93-0.021,1.276C25.554,12.087,25.328,12.172,25.102,12.172z M1.516,15.877h2v9.996h-2V15.877z M29.495,27.856H1.52l-0.031-1.938h28.037L29.495,27.856z"/></g></svg>',
                'label' => addslashes(esc_html__('File Upload', 'ARForms'))
            )
        ));

        return $fields;
    }

    function get_all_form_fields($form_id, $error = false) {
        global $arffield;
        $fields = apply_filters('arfgetpagedfields', false, $form_id, $error);
        if (!$fields)
            $fields = $arffield->getAll(array('fi.form_id' => $form_id), 'id');
        return $fields;
    }

    function pro_field_selection() {

        $pro_fields = apply_filters('arfaavailablefields', array(
            'email' => array(
                'icon' => '<svg viewBox="0 0 30 30"><g id="email"><path fill="#4E5462" fill-rule="evenodd" clip-rule="evenodd" d="M27.321,22.868H3.661c-1.199,0-2.172-0.973-2.172-2.172V3.053c0-1.2,0.973-2.203,2.172-2.203h23.66c1.199,0,2.171,1.003,2.171,2.203v17.643C29.492,21.895,28.52,22.868,27.321,22.868zM27.501,20.894V3.69l-12.28,9.268v0.008l-0.005-0.004l-0.005,0.004v-0.008L3.484,3.676v17.218H27.501z M24.994,2.844H5.95l9.267,7.377L24.994,2.844z"/></g></svg>',
                'label' => addslashes(esc_html__('Email Address', 'ARForms'))
            ),
            'number' => array(
                'icon' => '<svg viewBox="0 0 30 30"><g id="number"><path fill="#4E5462" fill-rule="evenodd" clip-rule="evenodd" d="M1.489,28.846V0.85h27.986v27.996H1.489z M27.498,2.893H3.499v23.912h23.999V2.893z M6.88,13.297c-0.136,0-0.234-0.109-0.294-0.33s-0.09-0.445-0.09-0.678c0-0.184,0.104-0.297,0.312-0.336l2.987-0.553c0.104,0,0.212,0.031,0.324,0.09c0.112,0.061,0.168,0.127,0.168,0.199v6.754h1.284c0.128,0,0.22,0.049,0.276,0.145c0.056,0.096,0.084,0.248,0.084,0.457v0.203c0,0.207-0.028,0.359-0.084,0.455c-0.056,0.098-0.148,0.145-0.276,0.145H6.916c-0.128,0-0.22-0.047-0.276-0.145c-0.056-0.096-0.084-0.248-0.084-0.455v-0.203c0-0.209,0.028-0.361,0.084-0.457c0.056-0.096,0.148-0.145,0.276-0.145h1.596v-5.387L6.88,13.297z M15.938,14.893c0.304-0.377,0.456-0.76,0.456-1.152c0-0.313-0.104-0.551-0.312-0.719s-0.476-0.252-0.804-0.252c-0.224,0-0.418,0.033-0.582,0.102s-0.338,0.158-0.522,0.27c-0.136,0.096-0.236,0.145-0.3,0.145c-0.176,0-0.334-0.096-0.474-0.289c-0.14-0.191-0.21-0.371-0.21-0.539c0-0.336,0.244-0.604,0.732-0.805c0.488-0.199,1.04-0.299,1.656-0.299c0.824,0,1.466,0.215,1.926,0.646c0.46,0.434,0.69,1,0.69,1.705c0,0.703-0.244,1.355-0.732,1.955l-2.256,2.783h2.772c0.128,0,0.22,0.049,0.276,0.145c0.056,0.096,0.084,0.248,0.084,0.457v0.203c0,0.207-0.028,0.359-0.084,0.455c-0.056,0.098-0.148,0.145-0.276,0.145h-4.775c-0.08,0-0.156-0.125-0.228-0.377c-0.072-0.252-0.108-0.479-0.108-0.68c0-0.088,0.012-0.146,0.036-0.18L15.938,14.893zM19.646,18.223c0.088-0.117,0.176-0.174,0.264-0.174c0.048,0,0.152,0.039,0.312,0.119c0.232,0.111,0.456,0.203,0.672,0.27c0.216,0.068,0.468,0.102,0.756,0.102c0.359,0,0.648-0.102,0.863-0.305c0.217-0.205,0.324-0.49,0.324-0.857c0-0.408-0.111-0.715-0.336-0.918c-0.224-0.205-0.58-0.307-1.067-0.307h-0.337c-0.127,0-0.219-0.049-0.275-0.145c-0.056-0.096-0.084-0.248-0.084-0.455v-0.217c0-0.207,0.028-0.359,0.084-0.455c0.056-0.096,0.148-0.145,0.275-0.145h0.229c0.385,0,0.686-0.09,0.906-0.27c0.22-0.18,0.33-0.422,0.33-0.727c0-0.303-0.082-0.541-0.246-0.713s-0.406-0.258-0.727-0.258c-0.264,0-0.483,0.025-0.659,0.076c-0.176,0.053-0.344,0.119-0.504,0.199c-0.112,0.064-0.196,0.096-0.251,0.096c-0.12,0-0.25-0.109-0.39-0.33c-0.14-0.221-0.21-0.414-0.21-0.582c0-0.279,0.226-0.496,0.678-0.648c0.452-0.15,1.001-0.227,1.65-0.227c0.752,0,1.35,0.189,1.794,0.57c0.444,0.379,0.665,0.889,0.665,1.529c0,0.408-0.1,0.766-0.299,1.074c-0.201,0.307-0.473,0.533-0.816,0.678v0.047c0.464,0.176,0.813,0.453,1.044,0.828c0.231,0.377,0.348,0.816,0.348,1.32c0,0.783-0.25,1.404-0.749,1.859c-0.5,0.457-1.154,0.684-1.963,0.684c-0.352,0-0.729-0.031-1.133-0.096s-0.75-0.17-1.038-0.318c-0.288-0.146-0.432-0.338-0.432-0.57c0-0.078,0.032-0.193,0.096-0.342C19.482,18.471,19.558,18.338,19.646,18.223z"/></g></svg>',
                'label' => addslashes(esc_html__('Number', 'ARForms'))
            ),
            'phone' => array(
                'icon' => '<svg viewBox="0 0 30 30"><g id="phone_number"><path fill="#4E5462" stroke="#4E5462" stroke-width="0.3" d="M7.139,19.462c2.762,3.301,6.085,5.9,9.878,7.739c1.444,0.685,3.375,1.497,5.527,1.637c0.134,0.006,0.261,0.012,0.394,0.012c1.445,0,2.605-0.499,3.551-1.526c0.006-0.006,0.017-0.018,0.023-0.029c0.336-0.406,0.719-0.771,1.119-1.16c0.271-0.261,0.551-0.533,0.817-0.813c1.235-1.288,1.235-2.924-0.012-4.171l-3.485-3.487c-0.592-0.615-1.299-0.939-2.042-0.939c-0.742,0-1.456,0.324-2.065,0.934l-2.076,2.077c-0.191-0.11-0.389-0.209-0.574-0.302c-0.231-0.116-0.446-0.227-0.638-0.348c-1.891-1.201-3.607-2.768-5.249-4.781c-0.829-1.05-1.386-1.932-1.774-2.831c0.545-0.493,1.056-1.01,1.549-1.515c0.174-0.18,0.354-0.359,0.533-0.539c0.627-0.627,0.963-1.353,0.963-2.089c0-0.737-0.331-1.462-0.963-2.089l-1.729-1.729c-0.203-0.202-0.395-0.399-0.592-0.603C9.911,2.515,9.512,2.109,9.117,1.743C8.52,1.157,7.818,0.85,7.075,0.85c-0.736,0-1.444,0.307-2.064,0.898l-2.169,2.17C2.053,4.708,1.606,5.666,1.514,6.773c-0.11,1.387,0.145,2.86,0.806,4.642C3.335,14.17,4.866,16.729,7.139,19.462z M2.929,6.895c0.069-0.771,0.365-1.416,0.922-1.973l2.158-2.158c0.336-0.325,0.707-0.493,1.066-0.493c0.354,0,0.714,0.168,1.044,0.505c0.389,0.359,0.754,0.736,1.148,1.138c0.197,0.202,0.4,0.405,0.604,0.614L11.6,6.257c0.359,0.359,0.545,0.725,0.545,1.085c0,0.359-0.186,0.725-0.545,1.085c-0.18,0.18-0.359,0.365-0.54,0.545c-0.539,0.546-1.044,1.062-1.601,1.555c-0.012,0.012-0.018,0.018-0.028,0.029c-0.481,0.481-0.406,0.94-0.29,1.288c0.005,0.018,0.011,0.029,0.017,0.047c0.447,1.073,1.067,2.094,2.036,3.313c1.74,2.146,3.572,3.812,5.591,5.094c0.249,0.163,0.517,0.29,0.766,0.418c0.232,0.116,0.447,0.227,0.638,0.349c0.023,0.011,0.041,0.022,0.064,0.034c0.191,0.099,0.377,0.146,0.563,0.146c0.464,0,0.766-0.296,0.864-0.395l2.169-2.17c0.337-0.337,0.702-0.517,1.062-0.517c0.44,0,0.8,0.272,1.026,0.517l3.498,3.492c0.695,0.696,0.689,1.451-0.018,2.188c-0.244,0.261-0.499,0.511-0.771,0.771c-0.406,0.395-0.83,0.801-1.213,1.26c-0.666,0.719-1.461,1.056-2.488,1.056c-0.098,0-0.202-0.006-0.301-0.012c-1.902-0.122-3.672-0.864-5-1.497c-3.607-1.746-6.774-4.224-9.401-7.368c-2.163-2.605-3.619-5.03-4.582-7.63C3.062,9.343,2.836,8.061,2.929,6.895z"/></g></svg>',
                'label' => addslashes(esc_html__('Phone Number', 'ARForms'))
            ),
            'date' => array(
                'icon' => '<svg viewBox="0 0 30 30"><g id="date"><path fill="#4E5462" fill-rule="evenodd" clip-rule="evenodd" d="M1.489,28.831V3.834h4.996V0.85h2.012v2.984h13.982V0.85h2.012v2.984h4.984v24.996H1.489z M27.498,5.875h-3.006v1.993h-2.012V5.875H8.497v1.993H6.484V5.875H3.499v20.913h23.999V5.875z M10.525,12.833H5.469v-2.028h5.056V12.833z M10.525,16.829H5.469V14.8h5.056V16.829z M10.525,20.855H5.469v-2.029h5.056V20.855z M10.525,24.849H5.469v-2.06h5.056V24.849z M18.484,12.833h-6.055v-2.028h6.055V12.833z M18.484,16.829h-6.055V14.8h6.055V16.829z M18.484,20.855h-6.055v-2.029h6.055V20.855z M18.484,24.849h-6.055v-2.06h6.055V24.849z M25.475,12.833h-4.994v-2.028h4.994V12.833z M25.475,16.829h-4.994V14.8h4.994V16.829z M25.475,20.855h-4.994v-2.029h4.994V20.855z M25.475,24.849h-4.994v-2.06h4.994V24.849z"/></g></svg>',
                'label' => esc_html__('Date', 'ARForms')
            ),
            'time' => array(
                'icon' => '<svg viewBox="0 0 30 30"><g id="time"><path fill="#4E5462" fill-rule="evenodd" clip-rule="evenodd" d="M15.491,28.853c-7.733,0-14.002-6.268-14.002-14.001S7.757,0.85,15.491,0.85c7.732,0,14.001,6.269,14.001,14.002S23.223,28.853,15.491,28.853z M15.491,2.708c-6.707,0-12.144,5.437-12.144,12.144c0,6.706,5.437,12.143,12.144,12.143c6.706,0,12.144-5.437,12.144-12.143C27.634,8.145,22.197,2.708,15.491,2.708z M21.045,16.34h-5.569c-0.556,0-0.99-0.435-0.99-0.99V7.416c0-0.556,0.466-0.99,1.021-0.99s0.991,0.435,0.991,0.99v6.913h4.547c0.556,0,0.99,0.435,0.99,0.99S21.601,16.34,21.045,16.34z"/></g></svg>',
                'label' => addslashes(esc_html__('Time', 'ARForms'))
            ),
            'url' => array(
                'icon' => '<svg viewBox="0 0 30 30"><g id="website"><path fill="#4E5462" stroke="#4E5462" stroke-width="0.6" d="M27.344,5.157l-3.389-3.422c-1.166-1.18-3.065-1.18-4.232,0l-5.928,5.988c-1.168,1.18-1.168,3.1,0,4.279c0.234,0.235,0.613,0.235,0.848,0c0.232-0.236,0.232-0.62,0-0.856c-0.701-0.707-0.701-1.859,0-2.567l5.926-5.988c0.701-0.707,1.841-0.707,2.541,0l3.387,3.422c0.7,0.707,0.7,1.859,0,2.568l-5.928,5.988c-0.678,0.686-1.861,0.686-2.54,0c-0.233-0.236-0.612-0.236-0.846,0c-0.234,0.236-0.234,0.619,0,0.855c0.565,0.572,1.316,0.887,2.116,0.887c0.801,0,1.552-0.314,2.117-0.887l5.928-5.989C28.51,8.256,28.51,6.336,27.344,5.157zM15.064,16.708c-0.232,0.236-0.232,0.619,0,0.855c0.701,0.708,0.701,1.86,0,2.568L9.138,26.12c-0.701,0.707-1.841,0.707-2.541,0l-3.386-3.422c-0.701-0.708-0.701-1.86,0-2.568l5.927-5.989c0.678-0.686,1.861-0.686,2.54,0c0.234,0.237,0.613,0.237,0.846,0c0.234-0.235,0.234-0.619,0-0.854c-1.128-1.145-3.103-1.145-4.232,0l-5.927,5.988c-1.167,1.179-1.167,3.1,0,4.279l3.387,3.422c0.583,0.59,1.35,0.885,2.117,0.885c0.767,0,1.533-0.295,2.117-0.885l5.927-5.988c1.168-1.18,1.168-3.101,0-4.279C15.678,16.471,15.299,16.471,15.064,16.708z M7.445,21.842c0.116,0.117,0.27,0.177,0.423,0.177s0.307-0.06,0.423-0.177L21.839,8.151c0.233-0.236,0.233-0.619,0-0.855s-0.612-0.236-0.847,0L7.445,20.987C7.211,21.223,7.211,21.606,7.445,21.842z"/></g></svg>',
                'label' => addslashes(esc_html__('Website/URL', 'ARForms'))
            ),
            'image' => array(
                'icon' => '<svg viewBox="0 0 30 30"><g id="image_url"><path fill="#4E5462" fill-rule="evenodd" clip-rule="evenodd" d="M25.496,9.194v19.76h-2v-0.017H3.499v0.019h-2V2.94H1.489V0.941h15.712l0.082-0.091l0.09,0.091h0.12v0.12l7.892,7.973h0.111v0.112l0.023,0.022L25.496,9.194z M16.498,3.118v5.812h5.753L16.498,3.118z M14.502,10.929V9.036h-0.004V2.948h1.925l-0.1-0.008H3.499v23.998h19.998V10.929H14.502z M21.495,22.698h-2.696c-0.112,0-0.193-0.024-0.244-0.075c-0.051-0.051-0.076-0.132-0.076-0.244v-5.039c0-0.171,0.16-0.257,0.48-0.257h0.272c0.32,0,0.48,0.086,0.48,0.257v4.312h1.784c0.085,0,0.146,0.031,0.184,0.096c0.037,0.064,0.056,0.166,0.056,0.304v0.248c0,0.139-0.019,0.24-0.056,0.304C21.642,22.667,21.581,22.698,21.495,22.698z M16.242,22.714h-0.328c-0.267,0-0.421-0.056-0.464-0.168l-0.632-1.56c-0.091-0.176-0.188-0.3-0.292-0.372s-0.276-0.108-0.516-0.108h-0.312v1.952c0,0.171-0.16,0.256-0.479,0.256h-0.272c-0.32,0-0.48-0.085-0.48-0.256v-5.04c0-0.111,0.025-0.192,0.076-0.243s0.132-0.076,0.244-0.076h1.56c1.328,0,1.992,0.501,1.992,1.504c0,0.299-0.084,0.559-0.252,0.78c-0.168,0.221-0.396,0.385-0.684,0.492v0.031c0.101,0.037,0.205,0.127,0.312,0.268c0.106,0.142,0.194,0.298,0.264,0.469l0.704,1.76c0.026,0.068,0.04,0.119,0.04,0.151c0,0.048-0.04,0.087-0.12,0.116S16.402,22.714,16.242,22.714z M14.895,18.311c-0.12-0.109-0.284-0.164-0.492-0.164h-0.704v1.296h0.76c0.165,0,0.309-0.066,0.432-0.2c0.123-0.133,0.184-0.296,0.184-0.488C15.074,18.569,15.014,18.42,14.895,18.311z M10.177,22.254c-0.406,0.339-0.976,0.508-1.712,0.508s-1.307-0.169-1.712-0.508c-0.405-0.338-0.608-0.814-0.608-1.428v-3.487c0-0.171,0.16-0.257,0.48-0.257h0.272c0.32,0,0.48,0.086,0.48,0.257v3.407c0,0.283,0.101,0.515,0.304,0.696c0.203,0.181,0.464,0.271,0.784,0.271c0.32,0,0.583-0.091,0.788-0.271c0.206-0.182,0.308-0.413,0.308-0.696v-3.407c0-0.171,0.16-0.257,0.48-0.257h0.264c0.32,0,0.479,0.086,0.479,0.257v3.487C10.784,21.44,10.582,21.917,10.177,22.254z"/></g></svg>',
                'label' => esc_html__('Image URL', 'ARForms')
            ),
            
            'password' => array(
                'icon' => '<svg viewBox="0 0 30 30"><g id="password"><path fill="#4E5462" d="M7.683,28.485c0.478-0.477,0.478-1.193,0-1.67l-2.031-2.029l2.21-2.209l2.091,2.088c0.478,0.479,1.195,0.479,1.672,0c0.478-0.477,0.478-1.193,0-1.67l-2.031-2.029l4.958-4.955c1.434,1.074,3.166,1.672,5.077,1.672c2.33,0,4.42-0.955,5.974-2.447c1.493-1.551,2.449-3.641,2.449-5.969c0-4.654-3.763-8.416-8.422-8.416c-2.33,0-4.42,0.955-5.973,2.447s-2.449,3.641-2.449,5.969c0,1.91,0.657,3.641,1.673,5.074L1.889,25.381c-0.717,0.717-0.299,1.434-0.06,1.672c0.478,0.477,1.194,0.477,1.672,0l0.597-0.598l2.091,2.09C6.488,28.963,7.205,28.963,7.683,28.485z M13.477,9.208c0-3.344,2.688-6.029,6.033-6.029c3.345,0,6.033,2.686,6.033,6.029c0,3.342-2.688,6.027-6.033,6.027C16.165,15.235,13.477,12.549,13.477,9.208z"/></g></svg>',
                'label' => addslashes(esc_html__('Password', 'ARForms'))
            ),
            'html' => array(
                'icon' => '<svg viewBox="0 0 30 30"><g id="html"><path fill="#4E5462" fill-rule="evenodd" clip-rule="evenodd" d="M28.736,28.85H2.21c-0.398,0-0.722-0.323-0.722-0.722V1.572c0-0.398,0.323-0.722,0.722-0.722h26.525c0.398,0,0.722,0.323,0.722,0.722v26.557C29.458,28.527,29.134,28.85,28.736,28.85zM5.508,2.825H3.464v2.013h2.044V2.825z M23.495,2.825H7.483v2.013h16.012V2.825z M27.482,2.825h-2.044v2.013h2.044V2.825zM27.482,6.844H3.464v20.03h24.019V6.844z M10.589,11.958c0.298-0.265,0.754-0.238,1.019,0.06c0.265,0.299,0.238,0.755-0.06,1.02l-4.856,4.319l4.856,4.319c0.298,0.266,0.325,0.722,0.06,1.02c-0.143,0.16-0.341,0.242-0.54,0.242c-0.171,0-0.342-0.061-0.479-0.183l-5.463-4.858c-0.154-0.138-0.242-0.334-0.242-0.54s0.088-0.402,0.242-0.539L10.589,11.958z M16.298,12.082c0.116-0.382,0.519-0.598,0.9-0.481c0.382,0.115,0.598,0.519,0.482,0.899l-3.032,10.012c-0.094,0.312-0.381,0.513-0.691,0.513c-0.069,0-0.14-0.01-0.209-0.031c-0.381-0.115-0.597-0.519-0.481-0.9L16.298,12.082z M24.253,17.356l-4.856-4.319c-0.298-0.265-0.325-0.721-0.06-1.02c0.265-0.297,0.721-0.324,1.019-0.06l5.463,4.859c0.154,0.137,0.242,0.333,0.242,0.539s-0.088,0.402-0.242,0.54l-5.463,4.858c-0.138,0.123-0.309,0.183-0.479,0.183c-0.199,0-0.397-0.082-0.54-0.242c-0.266-0.298-0.239-0.754,0.06-1.02L24.253,17.356z"/></g></svg>',
                'label' => addslashes(esc_html__('HTML', 'ARForms'))
            ),
            'divider' => array(
                'icon' => '<svg viewBox="0 0 30 30"><g id="section"><path fill="#4E5462" fill-rule="evenodd" clip-rule="evenodd" d="M1.489,28.845V0.85h27.987v27.995H1.489z M3.499,26.804h4.988V8.787H3.499V26.804z M27.498,2.891H3.499v3.896h23.999V2.891z M27.498,8.787H10.487v18.018h17.011V8.787z"/></g></svg>',
                'label' => addslashes(esc_html__('Section', 'ARForms'))
            ),
            'break' => array(
                'icon' => '<svg viewBox="0 0 30 30"><g id="page_break"><path fill="#4E5462" fill-rule="evenodd" clip-rule="evenodd" d="M25.032,10.833H2.025v-0.016H1.489V0.85h2v7.983h19.998V0.85h2v9.967h-0.454V10.833z M6.488,4.826h13.998v2.016H6.488V4.826z M6.488,1.827h13.998v2.015H6.488V1.827z M5.488,15.84h-4v-2.016h4V15.84z M12.487,13.825v2.016h-4v-2.016H12.487z M19.486,13.825v2.016h-4v-2.016H19.486z M22.486,13.825h3v2.016h-3V13.825zM1.943,18.878v-0.016H24.95v0.016h0.536v9.968h-2v-7.983H3.489v7.983h-2v-9.968H1.943z M20.487,25.855v2.015H6.488v-2.015H20.487zM6.488,22.855h13.999v2.016H6.488V22.855z"/></g></svg>',
                'label' => addslashes(esc_html__('Page Break', 'ARForms'))
            ),
            'scale' => array(
                'icon' => '<svg viewBox="0 0 30 30"><g id="star_rating"><path fill="#4E5462" d="M28.832,13.069c0.55-0.539,0.744-1.332,0.507-2.068s-0.857-1.263-1.618-1.374l-6.768-0.99c-0.288-0.042-0.537-0.224-0.666-0.487l-3.025-6.174c-0.34-0.694-1.029-1.125-1.798-1.125c-0.768,0-1.457,0.431-1.797,1.125L10.64,8.149c-0.129,0.264-0.378,0.445-0.666,0.487L3.206,9.627c-0.76,0.11-1.38,0.637-1.617,1.373s-0.043,1.529,0.507,2.068l4.896,4.806c0.209,0.205,0.305,0.501,0.255,0.789L6.091,25.45c-0.13,0.763,0.175,1.519,0.797,1.974c0.621,0.456,1.43,0.516,2.111,0.154l6.052-3.204c0.258-0.137,0.566-0.137,0.824,0l6.053,3.204c0.296,0.156,0.615,0.233,0.934,0.233c0.414,0,0.826-0.13,1.178-0.388c0.621-0.455,0.927-1.211,0.797-1.974l-1.156-6.785c-0.05-0.289,0.046-0.584,0.255-0.789L28.832,13.069z M21.707,18.302l1.015,5.959c0.052,0.3-0.064,0.587-0.309,0.766c-0.245,0.179-0.551,0.201-0.818,0.061l-5.315-2.814c-0.256-0.135-0.538-0.203-0.819-0.203s-0.563,0.068-0.819,0.203l-5.313,2.814c-0.27,0.141-0.574,0.118-0.819-0.061s-0.359-0.465-0.309-0.766l1.015-5.959c0.098-0.574-0.092-1.161-0.507-1.568l-4.3-4.221c-0.217-0.212-0.469-0.417-0.375-0.707s0.081-0.49,0.392-0.536l6.251-1.052c0.587-0.086,1.094-0.456,1.355-0.992l2.732-5.309c0.134-0.272,0.395-0.436,0.697-0.436s0.563,0.163,0.697,0.436l2.657,5.422c0.256,0.523,0.752,0.886,1.325,0.97l5.942,0.869c0.299,0.044,0.534,0.243,0.627,0.533c0.094,0.289,0.021,0.59-0.196,0.802l-4.3,4.221C21.797,17.141,21.608,17.727,21.707,18.302z"/></g></svg>',
                'label' => addslashes(esc_html__('Star Rating', 'ARForms'))
            ),
            'like' => array(
                'icon' => '<svg viewBox="0 0 30 30"><g id="like"><path fill="#4E5462" d="M17.949,1.55c1.022,0.746,1.539,2.433,1.539,4.195v4.184h5.458c2.066,0,3.753,1.681,3.753,3.751v0.115c0,0.033-0.005,0.071-0.011,0.104l-1.544,9.349c-0.286,2.328-1.912,3.668-4.456,3.668H11.193c-2.065,0-3.721-1.68-3.721-3.75V13.46c0-0.324-0.264-0.588-0.588-0.588H4.079c-0.324,0-0.588,0.264-0.588,0.588v9.854c0,0.324,0.264,0.588,0.588,0.588h1.444c0.374,0,0.676,0.583,0.676,0.957c0,0.373-0.302,1.019-0.676,1.019h-2.1c-1.066,0-1.934-0.867-1.934-1.933V12.835c0-1.065,0.868-1.934,1.934-1.934h3.43c0.758,0,1.417,0.346,1.736,0.982c0.044-0.027,0.093-0.044,0.148-0.061c0.181-0.049,4.753-1.307,4.753-5.008V1.709c0-0.297,0.192-0.555,0.473-0.643C14.051,1.039,16.333,0.363,17.949,1.55z M9.782,14.114c-0.104,0.027-0.209,0.033-0.308,0.011v8.385c0,1.323,1.077,2.405,2.406,2.405h2.648h7.159c1.868,0,2.917-0.835,3.126-2.51l1.539-8.071v-0.061c0-1.323-1.077-2.405-2.407-2.405h-5.79c-0.374,0-0.676-0.302-0.676-0.675V5.745c0-1.313-0.018-2.281-0.671-2.765c-0.709-0.527-0.75-0.415-1.321-0.322v5.152C15.488,12.532,10.007,14.054,9.782,14.114z"/></g></svg>',
                'label' => addslashes(esc_html__('Like button', 'ARForms'))
            ),
            'arfslider' => array(
                'icon' => '<svg viewBox="0 0 30 30"><g id="slider"><path fill="#4E5462" fill-rule="evenodd" clip-rule="evenodd" d="M12.501,20.85v2.002H7.474V20.85H1.489v-2h5.985v-2.002h5.027v2.002h16.953v2H12.501z M18.473,14.853v-2.002H1.489v-2h16.984V8.849H23.5v2.002h5.954v2H23.5v2.002H18.473z M12.501,6.854H7.474V4.852H1.489v-2h5.985V0.85h5.027v2.002h16.953v2H12.501V6.854z"/></g></svg>',
                'label' => addslashes(esc_html__('Slider', 'ARForms'))
            ),
            'colorpicker' => array(
                'icon' => '<svg viewBox="0 0 30 30"><g id="colorpicker"><path fill="#4E5462" d="M24.599,1.949c-1.465-1.465-3.84-1.466-5.307,0L18.28,2.962c-0.544-0.544-1.425-0.544-1.969,0c-0.544,0.543-0.544,1.425,0,1.969l0.329,0.328l-10.94,10.94H5.698L2.15,19.998c-0.302,0.302-0.48,0.705-0.499,1.131l-0.009,0.203c0,0.001,0,0.002,0,0.003L1.489,24.77c-0.006,0.16,0.053,0.314,0.166,0.428c0.106,0.105,0.251,0.164,0.401,0.164c0.008,0,0.017,0,0.025,0l1.572-0.08h0l1.11-0.049l0.504-0.023c0.558-0.023,1.087-0.258,1.482-0.652L21.344,9.964l0.273,0.273c0.272,0.271,0.628,0.407,0.984,0.407c0.356,0,0.712-0.136,0.985-0.407c0.544-0.544,0.544-1.426,0-1.97l1.013-1.013C26.064,5.79,26.064,3.415,24.599,1.949z M11.061,17.622l-5.125,0.967l11.36-11.36l2.08,2.079L11.061,17.622z"/></g></svg>',
                'label' => addslashes(esc_html__('Color Picker', 'ARForms'))
            ),
            'imagecontrol' => array(
                'icon' => '<svg viewBox="0 0 30 30"><g id="image_control"><path fill="#4E5462" d="M27.485,5.856v18.998H6.494V5.856H27.485 M29.497,3.844H4.482v22.99h25.015V3.844L29.497,3.844zM8.35,20.797h18.56l-3.712-10.666l-5.568,7.424l-3.712-3.713L8.35,20.797z M9.519,10.038c-1.026,0-1.856,0.83-1.856,1.855s0.831,1.855,1.856,1.855c1.026,0,1.856-0.83,1.856-1.855S10.545,10.038,9.519,10.038z M1.489,0.85v21.961h2.012V2.801l23.003-0.063V0.85H1.489z"/></g></svg>',
                'label' => addslashes(esc_html__('Image', 'ARForms'))
            ),
            'arfcreditcard' => array(
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid" width="28" height="20" viewBox="0 0 28 20"><path fill="#4e5462" fill-rule="evenodd" d="M-0.000,20.000 L-0.000,-0.000 L28.000,-0.000 L28.000,20.000 L-0.000,20.000 ZM26.000,2.000 L2.000,2.000 L2.000,18.000 L26.000,18.000 L26.000,2.000 ZM10.000,8.000 L4.000,8.000 L4.000,4.000 L10.000,4.000 L10.000,8.000 ZM9.000,13.000 L4.000,13.000 L4.000,11.000 L9.000,11.000 L9.000,13.000 ZM9.000,16.000 L4.000,16.000 L4.000,14.000 L9.000,14.000 L9.000,16.000 ZM14.000,13.000 L10.000,13.000 L10.000,11.000 L14.000,11.000 L14.000,13.000 ZM14.000,16.000 L10.000,16.000 L10.000,14.000 L14.000,14.000 L14.000,16.000 ZM19.000,13.000 L15.000,13.000 L15.000,11.000 L19.000,11.000 L19.000,13.000 ZM24.000,13.000 L20.000,13.000 L20.000,11.000 L24.000,11.000 L24.000,13.000 Z" class="cls-1"/></svg>',
                'label' => addslashes(esc_html__('Credit Card','ARForms'))
            )
        ));
        return $pro_fields;
        
    }

    function get_default_field_opts($values = false, $field = false) {
        global $style_settings;
        $minnum = 1;
        $maxnum = 10;
        $step = 1;
        $align = 'block';
        if ($values) {
            if ($values['type'] == 'number') {
                $minnum = 0;
                $maxnum = 9999;
            } else if ($values['type'] == 'time') {
                $step = 30;
            } else if ($values['type'] == 'radio') {
                $align = 'inline';
            } else if ($values['type'] == 'checkbox') {
                $align = 'block';
            } else if ($values['type'] == 'scale') {
                $maxnum = 5;
            }
        }

        if ($values['type'] == 'arfslider') {
            $minnum = 0;
            $maxnum = 50;
        }

        $end_minute = 60 - (int) $step;
        unset($values);
        unset($field);

        $style_settings->width = isset($style_settings->width) ? $style_settings->width : '';
        $style_settings->text_direction = isset($style_settings->text_direction) ? $style_settings->text_direction : '';

        return array(
            'slide' => 0, 'form_select' => '', 'show_hide' => 'show', 'any_all' => 'any', 'align' => $align,
            'hide_field' => array(), 'hide_field_cond' => array('=='), 'hide_opt' => array(), 'star' => 0,
            'post_field' => '', 'custom_field' => '', 'taxonomy' => 'category', 'exclude_cat' => 0, 'ftypes' => array(),
            'data_type' => '', 'restrict' => 0, 'start_year' => 2000, 'end_year' => 2020, 'read_only' => 0,
            'locale' => '', 'attach' => false, 'minnum' => $minnum, 'maxnum' => $maxnum,
            'step' => $step, 'clock' => 12, 'start_time' => '00:00', 'end_time' => '23:' . $end_minute,
            'dependent_fields' => 0, 'use_calc' => 0, 'calc' => '', 'duplication' => 1,
            'dyn_default_value' => '', 'field_width' => '', 'label_width' => $style_settings->width,
            'text_direction' => $style_settings->text_direction, 'align_radio' => '1', 'custom_width_field' => '0',
            'start_date' => "", 'end_date' => "", 'off_days' => "", 'arf_range_minnum' => '10', 'arf_range_maxnum' => '20',
            'phonetype' => 0, 'phtypes' => array(), 'country_validation' => 0, 'default_country' => ''
        );
    }

    function check_data_values($values) {
        $check = true;

        return $check;
    }

    function setup_new_variables($type = '', $form_id = '') {
        global $arfsettings, $arfieldhelper;

        $defaults = $arfieldhelper->get_default_field_options($type, $form_id);
        $defaults['field_options']['custom_html'] = $arfieldhelper->get_basic_default_html($type);

        $values = array();

        foreach ($defaults as $var => $default) {
            if ($var == 'field_options') {
                $values['field_options'] = array();
                foreach ($default as $opt_var => $opt_default) {
                    $values['field_options'][$opt_var] = $opt_default;
                    unset($opt_var);
                    unset($opt_default);
                }
            } else {
                $values[$var] = $default;
            }
            unset($var);
            unset($default);
        }

        if ($type == 'checkbox')
            $values['options'] = maybe_serialize(array(addslashes(esc_html__('Checkbox 1', 'ARForms')), addslashes(esc_html__('Checkbox 2', 'ARForms'))));
        else if ($type == 'radio')
            $values['options'] = maybe_serialize(array(addslashes(esc_html__('Radio 1', 'ARForms')), addslashes(esc_html__('Radio 2', 'ARForms'))));
        else if ($type == 'select' || $type == ARF_AUTOCOMPLETE_SLUG)
            $values['options'] = maybe_serialize(array('', addslashes(esc_html__('Select 1', 'ARForms'))));
        else if ($type == 'captcha')
            $values['invalid'] = $arfsettings->re_msg;

        return $values;
    }

    function _show_category($atts) {

        global $arfieldhelper;
        extract($atts);
        if (!is_object($cat))
            return;
        $checked = '';
        if (is_array($value))
            $checked = (in_array($cat->cat_ID, $value)) ? 'checked="checked" ' : '';
        else if ($cat->cat_ID == $value)
            $checked = 'checked="checked" ';
        else
            $checked = '';
        $class = '';
        $sanitized_name = ((isset($field['id'])) ? $field['id'] : $field['field_options']['taxonomy']) . '-' . $cat->cat_ID;
        ?>
        <div class="frm_<?php echo $type ?>" id="frm_<?php echo $type . '_' . $sanitized_name ?>">
            <label<?php echo $class ?> for="field_<?php echo $sanitized_name ?>"><input type="<?php echo $type ?>" name="<?php echo $field_name ?>" <?php echo (isset($hide_id) and $hide_id) ? '' : 'id="field_' . $sanitized_name . '"'; ?> value="<?php echo $cat->cat_ID ?>" <?php
                echo $checked;
                do_action('arffieldinputhtml', $field);
                ?> /><?php echo $cat->cat_name ?></label>

            <?php
            $children = get_categories(array('type' => $post_type, 'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false, 'exclude' => $exclude, 'parent' => $cat->cat_ID, 'taxonomy' => $taxonomy));
            if ($children) {
                $level++;
                foreach ($children as $key => $cat) {
                    ?>
                    <div class="catlevel_<?php echo $level ?>"><?php $arfieldhelper->_show_category(compact('cat', 'field', 'field_name', 'exclude', 'type', 'value', 'exclude', 'level', 'onchange', 'post_type', 'taxonomy', 'hide_id')) ?></div>
                    <?php
                }
            }
            ?>
        </div>
        <?php
    }

    function get_status_options($field) {
        global $arfform;

        $post_type = $arfform->post_type($field->form_id);
        $post_type_object = get_post_type_object($post_type);
        $options = array();
        if (!$post_type_object)
            return $options;
        $can_publish = current_user_can($post_type_object->cap->publish_posts);
        $options = get_post_statuses();

        if (!$can_publish) {
            unset($options['publish']);
            if (isset($options['future']))
                unset($options['future']);
        }
        return $options;
    }

    function get_user_options() {
        global $wpdb;
        $users = (function_exists('get_users')) ? get_users(array('fields' => array('ID', 'user_login', 'display_name'), 'blog_id' => $GLOBALS['blog_id'])) : get_users(array('fields' => array('ID', 'user_login', 'display_name'), 'blog_id' => $GLOBALS['blog_id']));
        $options = array('' => '');
        foreach ($users as $user)
            $options[$user->ID] = (!empty($user->display_name)) ? $user->display_name : $user->user_login;
        return $options;
    }

    function get_linked_options($values, $field, $entry_id = false) {
        global $arfrecordmeta, $user_ID, $arffield, $MdlDb, $arrecordhelper;
        $metas = array();
        $selected_field = $arffield->getOne($values['form_select']);
        if (!$selected_field)
            return array();
        $selected_field->field_options = maybe_unserialize($selected_field->field_options);

        $attach_ids = array();
        if ($values['restrict'] and $user_ID) {
            $entry_user = $user_ID;
            if ($entry_id and is_admin()) {
                $entry_user = $MdlDb->get_var($MdlDb->entries, array('id' => $entry_id), 'user_id');
                if (!$entry_user or empty($entry_user))
                    $entry_user = $user_ID;
            }

            if (isset($selected_field->form_id)) {
                $linked_where = array('form_id' => $selected_field->form_id, 'user_id' => $entry_user);
                $entry_ids = $MdlDb->get_col($MdlDb->entries, $linked_where, 'id');
                unset($linked_where);
            }
            if (isset($entry_ids) and ! empty($entry_ids))
                $metas = $arfrecordmeta->getAll("it.entry_id in (" . implode(',', $entry_ids) . ") and field_id=" . (int) $values['form_select'], ' ORDER BY entry_value');
        }else {
            $metas = $MdlDb->get_records($MdlDb->entry_metas, array('field_id' => $values['form_select']), 'entry_value', '', 'entry_id, entry_value');
            $attach_ids = $MdlDb->get_records($MdlDb->entries, array('form_id' => $selected_field->form_id), '', '', 'id, attachment_id');
        }

        $options = array();
        foreach ($metas as $meta) {
            $meta = (array) $meta;
            if (empty($meta['entry_value']))
                continue;
            if ($selected_field->type == 'image')
                $options[$meta['entry_id']] = $meta['entry_value'];
            else
                $options[$meta['entry_id']] = $arrecordhelper->display_value($meta['entry_value'], $selected_field, array('type' => $selected_field->type, 'show_icon' => false, 'show_filename' => false));
            unset($meta);
        }
        unset($metas);
        natcasesort($options);

        return $options;
    }

    function posted_field_ids($where) {

        return $where;
        if (isset($_POST['form_id']) and isset($_POST['arfpageorder' . $_POST['form_id']]))
            $where .= ' and fi.field_order < ' . (int) $_POST['arfpageorder' . $_POST['form_id']];
        return $where;
    }

    function get_form_fields($fields, $form_id, $error = false) {
        global $arfprevpage, $arffield, $arfnextpage, $armainhelper;
        $prev_page = $armainhelper->get_param('arfpageorder' . $form_id, false);
        $prev_page = (int) $prev_page;
        $where = "fi.type='break' AND fi.form_id=" . (int) $form_id;
        if ($error and ! $prev_page)
            $prev_page = 999;

        if ($prev_page) {
            if ($error) {
                $prev_page_obj = $arffield->getAll($where_error, 'id DESC', 1);
                $prev_page = false;
            }

            if ($prev_page and ! isset($prev_page_obj)) {
                $prev_where = $where . " AND fi.field_order=" . $prev_page;
                $prev_page_obj = $arffield->getAll($prev_where, 'id DESC', 1);
            }
            $arfprevpage[$form_id] = $prev_page;
        } else
            unset($arfprevpage[$form_id]);
        $next_page = $arffield->getAll($where, 'id', 1);
        unset($where);
        if ($next_page or $prev_page) {
            $query = "(fi.type != 'break'";
            if ($next_page)
                $query .= " or fi.id = $next_page->id";
            if ($prev_page)
                $query .= " or fi.id = $prev_page_obj->id";
            $query .= ") and fi.form_id=$form_id";

            /* arf_dev_flag , check for prev_page value, set to false intentionally */
            $prev_page = $next_page = false;
            if ($prev_page)
                $query .= " and fi.field_order >= $prev_page";
            if ($next_page)
                $query .= " and fi.field_order <= $next_page->field_order";
            if (is_admin())
                $query .= " and fi.type != 'captcha'";
            $fields = $arffield->getAll($query, ' ORDER BY id');
        }
        if ($next_page)
            $arfnextpage[$form_id] = $next_page->name;
        else
            unset($arfnextpage[$form_id]);
        return $fields;
    }

    function get_form_fields_tmp($fields, $form_id, $error = false, $previous = false) {
        global $arfprevpage, $arffield, $arfnextpage;
        $query = "fi.form_id=" . (int) $form_id;

        $fields = $arffield->getAll($query, ' ORDER BY id');

        return $fields;
    }

    function get_basic_default_html($type = 'text') {

        global $arf_data_uniq_id;
        if (apply_filters('arfdisplayfieldhtml', true, $type)) {
            $for = (in_array($type, array('radio', 'checkbox', 'data', 'like', 'arfslider', 'captcha'))) ? '' : 'for="field_[key]"';
            $default_html = '<div id="arf_field_[id]_' . $arf_data_uniq_id . '_container" class="arfformfield control-group arfmainformfield [required_class][error_class] arf_field_[id]"  [field_style]>

            <label ' . $for . ' class="arf_main_label">[field_name]

            <span class="arfcheckrequiredfield">[required_label]</span>

            </label>

            [input]

            [if description]<div class="arf_field_description" [description_style]>[description]</div>[/if description]

            [if error]<div class="arf_frm_error" [description_style]>[error]</div>[/if error]

            </div>';
        } else
            $default_html = apply_filters('arfothercustomhtml', '', $type);

        return apply_filters('arfcustomhtml', $default_html, $type);
    }

    function get_default_field_options($type, $field, $limit = false) {

        global $arfsettings;
        $field_options = array(
            'size' => '', 'max' => '', 'label' => '', 'blank' => addslashes(esc_html__($arfsettings->blank_msg, 'ARForms')), 'max_rows' => '3',
            'required_indicator' => '*', 'invalid' => '', 'separate_value' => 0,
            'clear_on_focus' => 0, 'default_blank' => 0, 'classes' => 'arf_1',
            'custom_html' => '', 'star_color' => 'yellow', 'star_size' => 'small', 'star_val' => '',
            'first_page_label' => 'Step1', 'second_page_label' => 'Step2', 'pre_page_title' => 'Previous', 'next_page_title' => 'Next', 'page_break_type' => 'wizard', 'page_break_first_use' => '0', 'pagebreaktabsbar' => 0, 'page_break_type_possition' => 'top', 'is_recaptcha' => 'recaptcha',
            'inline_css' => '', 'css_outer_wrapper' => '', 'css_label' => '', 'css_input_element' => '', 'css_description' => '',
            'file_upload_text' => 'Upload', 'max_fileuploading_size' => 'auto', 'upload_btn_color' => '#077bdd', 'arf_divider_font' => 'Helvetica',
            'arf_divider_font_size' => '16', 'arf_divider_font_style' => 'bold', 'arf_divider_bg_color' => '#ffffff', 'arf_divider_inherit_bg' => '0',
            'lbllike' => addslashes(esc_html__('Like', 'ARForms')), 'lbldislike' => addslashes(esc_html__('Dislike', 'ARForms')), 'slider_handle' => 'round', 'slider_step' => '1',
            'slider_bg_color' => '#d1dee5', 'slider_handle_color' => '#0480BE', 'slider_value' => '1',
            'like_bg_color' => '#087ee2', 'dislike_bg_color' => '#ff1f1f', 'slider_bg_color2' => '#bcc7cd',
            'upload_font_color' => '#ffffff', 'confirm_password' => 0, 'password_strength' => 0,
            'is_set_confirm' => 0, 'invalid_password' => addslashes(esc_html__('Confirm Password does not match with password', 'ARForms')),
            'placehodertext' => '', 'phone_validation' => 'international', 'confirm_password_label' => addslashes(esc_html__('Confirm Password', 'ARForms')),
            'image_url' => ARFURL . '/images/no-image.png', 'image_left' => '0px', 'image_top' => '0px', 'image_height' => '', 'image_width' => '',
            'image_center' => 'no', 'enable_total' => 0, 'round_total'=>0, 'colorpicker_type' => 'advanced', 'default_hour' => '0', 'default_minutes' => '0',
            'show_year_month_calendar' => '0', 'show_time_calendar' => '0', 'selectdefaultdate' => '', 'currentdefaultdate' => 1, 'password_placeholder' => '', 'minlength' => '', 'minlength_message' => addslashes(esc_html__('Invalid minimum characters length', 'ARForms')),
            'confirm_email' => '', 'confirm_email_label' => addslashes(esc_html__('Confirm Email', 'ARForms')), 'invalid_confirm_email' => addslashes(esc_html__('Confirm Email does not match with email', 'ARForms')),
            'confirm_email_placeholder' => '', 'enable_arf_prefix' => '0', 'arf_prefix_icon' => '', 'enable_arf_suffix' => '0', 'arf_suffix_icon' => '', 'arf_show_min_current_date' => 0, 'arf_show_max_current_date' => 0, 'arfnewdateformat' => '',
            'single_custom_validation' => 'custom_validation_none', 'arf_is_regular_expression' => 0, 'arf_regular_expression' => '', 'arf_regular_expression_msg' => addslashes(esc_html__('Entered value is invalid', 'ARForms')), 'arf_tooltip' => 0, 'tooltip_text' => '', 'arf_draggable' => 0, 'arf_dragable_label' => addslashes(esc_html__('Drop files here or click to select', 'ARForms')), 'arf_is_multiple_file' => 0
        );
        if ($type == 'captcha') {
            $field_options['invalid'] = addslashes(esc_html__('The reCAPTCHA was not entered correctly', 'ARForms'));
        } else if ($type == 'email') {
            $field_options['invalid'] = addslashes(esc_html__('Email is invalid', 'ARForms'));
        } else if ($type == 'file') {
            $field_options['invalid'] = addslashes(esc_html__('File is invalid', 'ARForms'));
            $field_options['invalid_file_size'] = addslashes(esc_html__('Invalid File Size', 'ARForms'));
        } else if ($type == 'number') {
            $field_options['invalid'] = addslashes(esc_html__('Number is out of range', 'ARForms'));
        } else if ($type == 'phone') {
            $field_options['invalid'] = addslashes(esc_html__('Phone is invalid', 'ARForms'));
        } else if ($type == 'image') {
            $field_options['invalid'] = addslashes(esc_html__('Image is invalid', 'ARForms'));
        } else if ($type == 'date') {
            $field_options['invalid'] = addslashes(esc_html__('Date is invalid', 'ARForms'));
        } else if ($type == 'url') {
            $field_options['invalid'] = addslashes(esc_html__('Website is invalid', 'ARForms'));
        } else if ($type == 'checkbox') {
            $field_options['options'] = json_encode(array('Checkbox 1', 'Checkbox2'));
        } else if ($type == 'radio') {
            $field_options['options'] = json_encode(array('Radio 1', 'Radio 2'));
        } else if ($type == 'select') {
            $field_options['options'] = json_encode(array('', 'Select 1'));
        }

        $field_options = apply_filters('arf_add_more_field_options_outside', $field_options, $type);

        if ($limit)
            return $field_options;

        global $MdlDb, $armainhelper, $arfsettings;

        $form_id = (is_numeric($field)) ? $field : $field->form_id;

        $key = is_numeric($field) ? $armainhelper->get_unique_key('', $MdlDb->fields, 'field_key') : $field->field_key;
        $field_count = $armainhelper->getRecordCount("form_id='$form_id'", $MdlDb->fields);

        return array(
            'name' => addslashes(esc_html__('Untitled', 'ARForms')), 'description' => '',
            'field_key' => $key, 'type' => $type, 'options' => '', 'default_value' => '',
            'required' => false,
            'blank' => $arfsettings->blank_msg, 'unique_msg' => $arfsettings->unique_msg,
            'invalid' => addslashes(esc_html__('This field is invalid', 'ARForms')), 'form_id' => $form_id,
            'field_options' => $field_options
        );
    }

    function show_onfocus_js($field_id, $clear_on_focus) {
        
    }

    function get_default_html($default_html, $type) {

        global $arf_data_uniq_id;
        if ($type == 'break') {
            $default_html = '<h2 class="pos_[label_position]">[field_name]</h2>

            [if description]<div class="arf_field_description">[description]</div>[/if description]';
        } else if ($type == 'divider') {
            $default_html = '<div id="heading_[id]" class="arf_heading_div" [field_style]>

            <h2 class="arf_sec_heading_field pos_[label_position][collapse_class]">[field_name]</h2>

            [collapse_this]

            [if description]<div class="arf_field_description arf_heading_description" [description_style]>[description]</div>[/if description]

            </div>';
        } else if ($type == 'html') {
            $default_html = '<div id="arf_field_[id]_' . $arf_data_uniq_id . '_container" class="arfformfield control-group arfmainformfield [error_class] arf_field_[id]" [field_style]><div class="arf_htmlfield_control">[description]</div></div>';
        }
        return $default_html;
    }

    function replace_field_shortcodes($html, $field, $errors = array(), $fields = array() , $form = false) {
        global $arfreadonly, $arfieldhelper, $arrecordcontroller;

        $html = stripslashes($html);
        $html = apply_filters('arfbeforereplaceshortcodes', $html, $field, $errors, $form);

        $field_name = 'item_meta[' . $field['id'] . ']';
        if (isset($field['multiple']) and $field['multiple'] and ( $field['type'] == 'select' || $field['type'] == ARF_AUTOCOMPLETE_SLUG or ( $field['type'] == 'data' and isset($field['data_type']) and $field['data_type'] == 'select')))
            $field_name .= '[]';

        $html = str_replace('[id]', $field['id'], $html);


        $html = str_replace('[key]', $field['field_key'], $html);

        $required = ($field['required'] == '0') ? '' : $field['required_indicator'];
        if (!is_array($errors))
            $errors = array();
        $error = (isset($errors['field' . $field['id']])) ? stripslashes($errors['field' . $field['id']]) : false;
        foreach (array('description' => $field['description'], 'required_label' => $required, 'error' => $error) as $code => $value) {

            if ($code == 'description') {
                if ($field['type'] != 'html' && $field['type'] != 'divider')
                    $value = '';
            }

            if (!$value or $value == '')
                $html = preg_replace('/(\[if\s+' . $code . '\])(.*?)(\[\/if\s+' . $code . '\])/mis', '', $html);
            else {
                $html = str_replace('[if ' . $code . ']', '', $html);
                $html = str_replace('[/if ' . $code . ']', '', $html);
            }
            if ($field['type'] == 'html' && $code == 'description' && $field['enable_total'] == 1) {

                $regex = '/<arftotal>(.*?)<\/arftotal>/is';

                preg_match($regex, $value, $arftotalmatches);

                if ($arftotalmatches) {
                    $value = $arfieldhelper->arf_replace_running_total_field($value, $arftotalmatches, $field,$fields);
                }
            }
            if ($field['type'] != 'checkbox') {
                $html = str_replace('[' . $code . ']', $value, $html);
            } else {
                if ($field['name'] != '' and $code == 'required_label') {
                    $html = str_replace('[' . $code . ']', $value, $html);
                } else if ($field['name'] == '' and $code == 'required_label') {
                    $html = str_replace('[' . $code . ']', '', $html);
                } else {
                    $html = str_replace('[' . $code . ']', $value, $html);
                }
            }
            $description_style = ( isset($field['field_width']) and $field['field_width'] == '' ) ? 'style="width:' . $field['field_width'] . 'px;"' : '';

            $html = str_replace('[description_style]', $description_style, $html);
        }
        $field_style = $arfieldhelper->get_display_style_new($field,$fields,$form);

        $html = str_replace('[field_style]', $field_style, $html);

        $required_class = ($field['required'] == '0') ? '' : ' arffieldrequired';

        if ($field['type'] == 'confirm_password')
            $required_class .= ' confirm_password_container arf_confirm_password_field_' . $field['confirm_password_field'];

        if ($field['type'] == 'confirm_email')
            $required_class .= ' confirm_email_container arf_confirm_email_field_' . $field['confirm_email_field'];

        $html = str_replace('[required_class]', $required_class, $html);

        global $db_record, $arfform, $arffield, $arfajaxurl, $MdlDb, $wpdb;

        $data = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->forms . " WHERE id = %d", $form->id), 'ARRAY_A');

        $aweber_arr = "";
        $aweber_arr = $data[0]['form_css'];

        $newarr = array();

        if ($aweber_arr != "") {
            $arr = maybe_unserialize($aweber_arr);

            foreach ($arr as $k => $v)
                $newarr[$k] = $v;
        }
        $values['label_position'] = ($newarr['hide_labels'] == '1') ? 'none' : $newarr['position'];
        global $style_settings;

        $field['label'] = ($values['label_position'] and $values['label_position'] != '') ? $values['label_position'] : $style_settings->position;
        $html = str_replace('[label_position]', (($field['type'] == 'divider' or $field['type'] == 'break') ? $field['label'] : ' arf_main_label'), $html);


        $html = str_replace('[field_name]', $field['name'], $html);

        $error_class = isset($errors['field' . $field['id']]) ? ' arfblankfield' : '';
        $error_class .= ' ' . $field['label'] . '_container';

        if (isset($field['classes'])) {

            if (!strpos($html, 'arfformfield '))
                $error_class .= ' arfformfield';

            global $arf_column_classes, $is_multi_column_loaded;

            if ($field['type'] != 'imagecontrol') {

                if (isset($field['classes']) and $field['classes'] == 'arf_2' and empty($arf_column_classes['two'])) {
                    $arf_column_classes['two'] = '1';
                    $arf_classes = 'frm_first_half';

                    $arf_column_classes['three'] = '';
                    unset($arf_column_classes['three']);

                    $is_multi_column_loaded[] = $form->form_key;
                } else if (isset($field['classes']) and $field['classes'] == 'arf_2' and isset($arf_column_classes['two']) and $arf_column_classes['two'] == '1') {
                    $arf_classes = 'frm_last_half';
                    $arf_column_classes['two'] = '';

                    unset($arf_column_classes['two']);
                    $arf_column_classes['three'] = '';
                    unset($arf_column_classes['three']);
                } else if (isset($field['classes']) and $field['classes'] == 'arf_3' and empty($arf_column_classes['three'])) {
                    $arf_column_classes['three'] = '1';
                    $arf_classes = 'frm_first_third';

                    $arf_column_classes['two'] = '';
                    unset($arf_column_classes['two']);
                    $is_multi_column_loaded[] = $form->form_key;
                } else if (isset($field['classes']) and $field['classes'] == 'arf_3' and isset($arf_column_classes['three']) and $arf_column_classes['three'] == '1') {
                    $arf_column_classes['three'] = '2';
                    $arf_classes = 'frm_third';

                    $arf_column_classes['two'] = '';
                    unset($arf_column_classes['two']);
                } else if (isset($field['classes']) and $field['classes'] == 'arf_3' and isset($arf_column_classes['three']) and $arf_column_classes['three'] == '2') {
                    $arf_classes = 'frm_last_third';

                    $arf_column_classes['three'] = '';
                    unset($arf_column_classes['three']);
                    $arf_column_classes['two'] = '';
                    unset($arf_column_classes['two']);
                } else {
                    $arf_column_classes = array();
                    $arf_classes = '';
                }

                if (isset($arf_column_classes['three']) and $arf_column_classes['three'] == '3') {
                    $arf_column_classes['three'] = '';
                    unset($arf_column_classes['three']);
                }
                if (isset($arf_column_classes['two']) and $arf_column_classes['two'] == '2') {
                    $arf_column_classes['two'] = '';
                    unset($arf_column_classes['two']);
                }
            }

            $arf_classes = isset($arf_classes) ? $arf_classes : '';
            $error_class .= ' ' . $arf_classes;
        }
        $html = str_replace('[error_class]', $error_class, $html);


        $entry_key = (isset($_GET) and isset($_GET['entry'])) ? $_GET['entry'] : '';
        $html = str_replace('[entry_key]', $entry_key, $html);

        preg_match_all("/\[(input|deletelink)\b(.*?)(?:(\/))?\]/s", $html, $shortcodes, PREG_PATTERN_ORDER);

        foreach ($shortcodes[0] as $short_key => $tag) {
            $atts = shortcode_parse_atts($shortcodes[2][$short_key]);

            if (!empty($shortcodes[2][$short_key])) {
                $tag = str_replace('[', '', $shortcodes[0][$short_key]);
                $tag = str_replace(']', '', $tag);
                $tags = explode(' ', $tag);
                if (is_array($tags))
                    $tag = $tags[0];
            } else
                $tag = $shortcodes[1][$short_key];

            $replace_with = '';

            if ($tag == 'input') {
                if (isset($atts['opt']))
                    $atts['opt'] --;
                $field['input_class'] = isset($atts['class']) ? $atts['class'] : '';
                if (isset($atts['class']))
                    unset($atts['class']);
                $field['shortcodes'] = $atts;
                ob_start();
                include(VIEWS_PATH . '/inputelements.php');
                $replace_with = ob_get_contents();
                ob_end_clean();
            }
            $html = str_replace($shortcodes[0][$short_key], $replace_with, $html);
        }

        if ($form) {
            $form = (array) $form;

            $html = str_replace('[form_key]', $form['form_key'], $html);


            $html = str_replace('[form_name]', $form['name'], $html);
        }
        if ($field['type'] == 'select' && $field['separate_value'] == 1) {
            $field_id = ($field['id'] * 100);
            $html .= "<input type='hidden' name='item_meta[-" . $field_id . "]' />";
        }
        $html .= "\n";

        return apply_filters('arfreplaceshortcodes',$html,$field,$fields,$errors,$form);
    }

    function display_recaptcha($field, $error = null, $display_mode = "") {
        global $arfsettings, $arfieldhelper, $arfversion;
        $lang = apply_filters('arfrecaptchalang', $arfsettings->re_lang, $field);

        if (defined('DOING_AJAX')) {
            global $arfrecaptchaloaded;
            if (!$arfrecaptchaloaded)
                $arfrecaptchaloaded = '';

            $arfrecaptchaloaded .= "Recaptcha.create('" . $arfsettings->pubkey . "','field_" . $field['field_key'] . "',{theme:'" . $arfsettings->re_theme . "',lang:'" . $lang . "'" . apply_filters('arfrecaptchacustom', '', $field) . "});";
            ?>
            <div id="field_<?php echo $field['field_key'] ?>"></div>
        <?php }else { ?>

            <?php
            $data_size = "data-size='normal'";
            $dsize = "normal";
            if ($field['classes'] == 'arf_2' || $field['classes'] == 'arf_3') {
                $data_size = "data-size='compact'";
                $dsize = "compact";
            }
            ?>
            <?php if ($display_mode == "preview") { ?>
                <script type="text/javascript" data-cfasync="false" src="https://www.google.com/recaptcha/api.js?hl=<?php echo $lang; ?>&onload=render_arf_captcha&render=explicit"></script>
                <?php
            } else {
                wp_enqueue_script('arf-google-recaptcha', 'https://www.google.com/recaptcha/api.js?hl=' . $lang . '&onload=render_arf_captcha&render=explicit', array(), $arfversion);
            }
            ?>                
            <script type="text/javascript" data-cfasync="false">
                if (!window['arf_recaptcha']) {
                    window['arf_recaptcha'] = {};
                }
                window['arf_recaptcha']['arf_recaptcha_<?php echo $field['field_key'] ?>'] = {
                    size: '<?php echo $dsize; ?>'
                };
            </script>

            <?php
            echo '<div id="recaptcha_style">';

            echo "<div id='arf_recaptcha_{$field['field_key']}' class='arf_captcha_wrapper'></div>";
            echo "<div class='help-block'></div>";
            echo '</div>';
        }
    }

    function before_replace_shortcodes($html, $field, $error, $form) {

        if ($form != '') {
            $form_css = maybe_unserialize($form->form_css);
            if (is_array($form_css)) {
                $arfcheckboxalignsetting = $form_css['arfcheckboxalignsetting'];
                $arfradioalignsetting = $form_css['arfradioalignsetting'];
            }
        }
        global $style_settings;

        if (isset($field['align']) and ( $field['type'] == 'radio' or $field['type'] == 'checkbox')) {

            $required_class = '[required_class]';

            if (($field['type'] == 'radio' and $field['align'] != $arfradioalignsetting) or ( $field['type'] == 'checkbox' and $field['align'] != $arfcheckboxalignsetting)) {

                if ($field['align'] != 'global')
                    $required_class .= ($field['align'] == 'block') ? ' arf_vertical_radio' : ' arf_horizontal_radio';
                $html = str_replace('[required_class]', $required_class, $html);
            }
        }

        if (isset($field['classes']) and strpos($field['classes'], 'frm_grid') !== false) {

            $opt_count = count($field['options']) + 1;

            $html = str_replace('[required_class]', '[required_class] frm_grid_' . $opt_count, $html);

            unset($opt_count);
        }

        return $html;
    }

    function replace_html_shortcodes($html,$field,$fields,$errors,$form) {
        if ($field['type'] == 'divider') {

            global $arfdiv;
            $trigger = '';
            $html = str_replace(array('none_container', 'top_container', 'left_container', 'right_container'), '', $html);
            global $MdlDb, $arf_page_number, $arfieldhelper;
            $page_num = $MdlDb->get_count($MdlDb->fields, array("form_id" => $field['form_id'], "type" => 'break'));

            if ($page_num > 0) {
                $collapse_div = '<div class="divider_' . $arf_page_number . '">' . "\n";
            } else {
                $collapse_div = '<div>' . "\n";
            }

            if (preg_match('/\[(collapse_this)\]/s', $html)) {
                global $arf_section_div;

                if ($arf_section_div) {
                    $html = "<div class='arf_clear'></div></div>\n" . $html;
                } else {
                    $arf_section_div = 1;
                }

                $html = str_replace('[collapse_this]', $collapse_div, $html);
            }
            $field_style = $arfieldhelper->get_display_style_new($field,$fields,$form);

            $html = str_replace('[field_style]', $field_style, $html);

            $html = str_replace('[collapse_class]', $trigger, $html);
        } else if ($field['type'] == 'html') {

            $html = apply_filters('arfgetdefaultvalue', $html, (object) $field, false);

            $html = do_shortcode($html);
        }

        return $html;
    }

    function get_file_icon($media_id) {
        global $arfieldhelper;
        
        if (!is_numeric($media_id)) {
            return;
        }

        $post_meta_data = get_post_meta($media_id);

        $image_array_link = $post_meta_data["_wp_attached_file"][0];
        
        $image_name = explode('/',$image_array_link);

        $image_name = $image_name[count($image_name) -1 ];

        $image_ext = explode('.',$image_name);

        $image_ext = $image_ext[count($image_ext) - 1];

        $image_ext = strtolower($image_ext);

        $exclude_ext = array('png','jpg','jpeg','jpe','gif','bmp','tif','tiff','ico');

        if( in_array($image_ext,$exclude_ext) ){
            $image_array_link = get_home_url() . "/" . str_replace('thumbs/', '', $image_array_link);
            $img_height = 'height=150';
            $img_width = 'width=150';
        } else {
            $img_height = '';
            $img_width = '';
            $image_array_link = $this->arf_get_file_icon( $image_ext );
        }

        
        $image = '<img class="attachment-thumnail" alt="' . $image_array_link . '" src="' . $image_array_link . '" border="0" ' . $img_height . ' ' . $img_width . '>';

        $attachment = get_post($media_id);
        if( empty($attachment)) {
            return '';
        }
        if ($attachment and $image and ! preg_match("/wp-content\/uploads/", $image)) {
            $label = basename($attachment->guid);
            $image = $arfieldhelper->get_file_name_link($media_id);
            $image .= '<img class="attachment-thumnail" alt="' . $image_array_link . '" src="' . $image_array_link . '" border="0" ' . $img_height . ' ' . $img_width . '></a>';
        } else {
            $image = '<a href="' . $image_array_link . '" class="arf_file_inner_'.$media_id.'" target="_blank"><img class="attachment-thumnail" alt="' . $image_array_link . '" src="' . $image_array_link . '" border="0" ' . $img_height . ' ' . $img_width . '></a>';
        }

        return $image;
    }

    function arf_get_file_icon( $file_ext ) {
       
       $mimes = get_allowed_mime_types();

        foreach ( $mimes as $type => $mime ) {
          if ( false !== strpos( $type, strtolower($file_ext) ) ) {
              return wp_mime_type_icon( $mime );
            }
        }
    }

    function get_file_name_link($media_id, $short = true) {
        if (is_numeric($media_id)) {
            if ($short) {
                $attachment = get_post($media_id);
                $label = basename($attachment->guid);
            }
            $url = get_post_meta($media_id);
            $url = $url["_wp_attached_file"][0];
            $url = get_home_url() . "/" . str_replace('thumbs/', '', $url);

            if (is_admin()) {
                global $arfsiteurl;
                $url = '<a href="' . $url . '" target="_blank">';
            }
            return $url;
        }
    }

    function get_file_name($media_id, $short = true) {
        if (is_numeric($media_id)) {
            if ($short) {
                $attachment = get_post($media_id);
                $label = basename($attachment->guid);
            }
            $url = get_post_meta($media_id);
            $url = isset($url["_wp_attached_file"][0]) ? $url["_wp_attached_file"][0] : '';
            $url = get_home_url() . "/" . str_replace('thumbs/', '', $url);
            if (is_admin()) {
                global $arfsiteurl;
                $url = '<a href="' . $url . '">' . $label . '</a>';
            }
            return $url;
        }
    }

    function get_date_entry($value,$form_id,$show_time_calendar = false,$field_clock = 12, $locale = 'en', $from_addon = ''){
        global $wpdb,$MdlDb;
        if ($value == ''){
            $value = '-';
            return $value;
        }
                
        if( isset($GLOBALS['arf_form_css']) && isset($GLOBALS['arf_form_css'][$form_id]) ){
            $form_data = $GLOBALS['arf_form_css'][$form_id];
        } else {
            $form_data = $wpdb->get_results($wpdb->prepare("SELECT form_css FROM " . $MdlDb->forms . " WHERE id=%d" ,$form_id));
            if( !isset($GLOBALS['arf_form_css']) ){
                $GLOBALS['arf_form_css'] = array();
            }
            $GLOBALS['arf_form_css'][$form_id] = $form_data;
        }

        $form_data_unserialize = maybe_unserialize($form_data[0]->form_css);
        $formate = $form_data_unserialize['date_format'];
        if($formate == 'MM/DD/YYYY'){
            $formate = 'm/d/Y';    
        } else if($formate == 'MMM D, YYYY'){
            $formate = 'M d, Y';    
        } else if($formate == 'MMMM D, YYYY'){
            $formate = 'F d, Y';    
        } else if($formate == 'YYYY/MM/DD'){
            $formate = 'Y/m/d';    
        } else if($formate == 'DD/MM/YYYY'){
            $formate = 'd/m/Y';    
        }

        if($show_time_calendar)
        {
            if($field_clock == '24')
            {
                $formate .=' H:i';
            } else {
                $formate .=' g:i a';                    
            }
        }

        //return $formate;

        $final_date = "";

        $locale = strtolower($locale);

        $addonarray = array();

        $addonarray = apply_filters('arf_check_date_from_outside',$addonarray);

        $arf_fallback_language_for_date = array('zh-cn','zh-tw','ja','ko','ta');

        if(count($addonarray) > 0 && in_array($from_addon,$addonarray)){
            if( in_array($locale,$arf_fallback_language_for_date) ){
                $locale = 'en';
            }
        }

        if( $locale != '' && $locale != 'en' ){
            $final_date = $this->get_date_with_locale($value,$formate,$locale);
        } else {
            $final_date = date($formate,strtotime($value));
        }

        return $final_date;

    }

    function get_date_with_locale($date,$format,$locale){
        global $ARForms;
        $json_file = VIEWS_PATH.'/arf_editor_data.json';
        $json_data = file_get_contents($json_file);

        $json_data = json_decode( $json_data );

        $locale_data = $json_data->date_locale->$locale;

        $new_date = date($format,strtotime($date));

        $digits = $locale_data->digits;
        $month = $locale_data->months;
        $month_sort = $locale_data->month_sort;
        $meridiem_lower = $locale_data->meridiem_lower;
        $meridiem_upper = $locale_data->meridiem_upper;

        $final_date = "";

        $delimiter = "";
        if( preg_match('/\//',$new_date) ){
            $exploded_new_date = explode('/',$new_date);
            $delimiter = '/';
        } else if( preg_match('/\-/',$new_date) ){
            $exploded_new_date = explode('-',$new_date);
            $delimiter = '-';
        } else {
            $exploded_new_date = explode(" ",$new_date);
            $delimiter = ' ';
        }

        foreach($exploded_new_date as $key => $value ){

            if( preg_match_all('/[\d\,\:]/',$value,$matches) ){

                foreach($matches[0] as $key => $val ){
                    if( $val == ',' ){
                        $final_date .= ", ";
                    } else if( $val == ':' ){
                        $final_date .= ":";
                    } else {
                        $final_date .= $digits->$val;
                    }
                }
                $final_date .= " ";
            } else {

                if( isset($month->$value) ){
                    $final_date .= $month->$value." ";
                } else if( isset($month_sort->$value) ){
                    $final_date .= $month_sort->$value.' ';
                } else if( isset($meridiem_lower->$value) ){
                    $final_date .= $meridiem_lower->$value.' ';
                } else if( isset($meridiem_upper->$value) ){
                    $final_date .= $meridiem_upper->$value.' ';
                }
            }
            $final_date .= $delimiter;
        }

        return trim(rtrim(ltrim($final_date,$delimiter),$delimiter));

    }

    function get_date($date, $date_format = false, $field_id = 0) {

        global $arffield;
        $fielddata = $arffield->getOne($field_id);
        $fieldoptions = array();
        if (isset($fielddata->field_options)) {
            $fieldoptions = maybe_unserialize($fielddata->field_options);
        }

        $show_time_calendar = isset($fieldoptions['show_time_calendar']) ? $fieldoptions['show_time_calendar'] : '';
        if (empty($date))
            return $date;
        if (!$date_format)
            $date_format = get_option('date_format');
        if (preg_match('/^\d{1-2}\/\d{1-2}\/\d{4}$/', $date)) {

            global $style_settings, $armainhelper;

            $date = $armainhelper->convert_date($date, $style_settings->date_format, 'Y-m-d');
        }

        $date = str_replace('/', '-', $date);

        if (strtotime($date) == '')
            return $date;

        if ($show_time_calendar == 1) {
            $date_format .= " ";
            $date_format .= get_option('time_format');
        }

        return date_i18n($date_format, strtotime($date));
    }

    function get_field_options($form_id, $value = '', $include = 'not', $types = "'break','divider','data','file','captcha'", $data_children = false) {
        global $arffield, $armainhelper;
        $fields = $arffield->getAll("fi.type $include in ($types) and fi.form_id=" . (int) $form_id, 'fi.id');
        foreach ($fields as $field) {
            $field->field_options = maybe_unserialize($field->field_options);
            ?>
            <option value="<?php echo $field->id ?>" <?php selected($value, $field->id) ?>><?php echo $armainhelper->truncate($field->name, 50) ?></option>
            <?php
        }
    }

    function value_meets_condition($observed_value, $cond, $hide_opt) {
        if ($hide_opt == '')
            return false;

        if (is_array($observed_value)) {
            if ($cond == '==') {
                $m = in_array($hide_opt, $observed_value);
            } else if ($cond == '!=') {
                $m = !in_array($hide_opt, $observed_value);
            } else if ($cond == '>') {
                $min = min($observed_value);
                $m = $min > $hide_opt;
            } else if ($cond == '<') {
                $max = max($observed_value);
                $m = $max < $hide_opt;
            }
        } else {
            if ($cond == '==')
                $m = $observed_value == $hide_opt;
            else if ($cond == '!=')
                $m = $observed_value != $hide_opt;
            else if ($cond == '>')
                $m = $observed_value > $hide_opt;
            else if ($cond == '<')
                $m = $observed_value < $hide_opt;
        }
        return $m;
    }
    
    function replace_shortcodes($content, $entry, $shortcodes, $display = false, $show = 'one', $odd = '') {
        global $arffield, $arfrecordmeta, $post, $style_settings, $armainhelper, $arfieldhelper, $arrecordhelper, $arrecordcontroller;
        
        if (is_array($shortcodes[0])) {
            foreach ($shortcodes[0] as $short_key => $tag) {
                $conditional = false;
                $atts = shortcode_parse_atts($shortcodes[3][$short_key]);

                if (!empty($shortcodes[3][$short_key])) {
                    if ($conditional)
                        $tag = str_replace('[if ', '', $shortcodes[0][$short_key]);
                    else
                        $tag = str_replace('[', '', $shortcodes[0][$short_key]);
                    $tag = str_replace(']', '', $tag);
                    $tags = explode(' ', $tag);
                    if (is_array($tags))
                        $tag = $tags[0];
                } else
                    $tag = $shortcodes[2][$short_key];

                switch ($tag) {
                    case 'detaillink':
                        if ($display and $detail_link)
                            $content = str_replace($shortcodes[0][$short_key], $detail_link, $content);
                        break;
                    case 'id':
                        $content = str_replace($shortcodes[0][$short_key], $entry->id, $content);
                        break;
                    case 'post-id':
                    case 'attachment_id':
                        $content = str_replace($shortcodes[0][$short_key], $entry->attachment_id, $content);
                        break;

                    case 'key':
                        $content = str_replace($shortcodes[0][$short_key], $entry->entry_key, $content);
                        break;

                    case 'ip_address':
                        $content = str_replace($shortcodes[0][$short_key], $entry->ip_address, $content);
                        break;

                    case 'user_agent':
                    case 'user-agent':
                        $entry->description = maybe_unserialize($entry->description);
                        $content = str_replace($shortcodes[0][$short_key], $entry->description['browser'], $content);
                        break;

                    case 'created-at':
                    case 'updated-at':
                    case 'evenodd':
                        $content = str_replace($shortcodes[0][$short_key], $odd, $content);
                        break;

                    case 'siteurl':
                        global $arfsiteurl;
                        $content = str_replace($shortcodes[0][$short_key], $arfsiteurl, $content);
                        break;

                    case 'sitename':
                        $content = str_replace($shortcodes[0][$short_key], get_option('blogname'), $content);
                        break;

                    case 'get':
                        if (isset($atts['param'])) {
                            $param = $atts['param'];
                            $replace_with = $armainhelper->get_param($param);
                            if (is_array($replace_with))
                                $replace_with = implode(', ', $replace_with);

                            $content = str_replace($shortcodes[0][$short_key], $replace_with, $content);
                            unset($param);
                            unset($replace_with);
                        }
                        break;

                    default:
                        if ($tag == 'deletelink') {
                            
                        } else {
                            $field = $arffield->getOne($tag);
                        }

                        $sep = (isset($atts['sep'])) ? $atts['sep'] : ', ';

                        if (!isset($field))
                            $field = false;

                        if ($field) {
                            $field->field_options = maybe_unserialize($field->field_options);
                            $replace_with = $arrecordhelper->get_post_or_entry_value($entry, $field, $atts);
                            $replace_with = maybe_unserialize($replace_with);
                            $atts['entry_id'] = $entry->id;
                            $atts['entry_key'] = $entry->entry_key;
                            $atts['attachment_id'] = $entry->attachment_id;
                            $replace_with = apply_filters('arffieldsreplaceshortcodes', $replace_with, $tag, $atts, $field);
                        }

                        if (isset($replace_with) and is_array($replace_with))
                            $replace_with = implode($sep, $replace_with);

                        if ($field and $field->type == 'file') {

                            $size = (isset($atts['size'])) ? $atts['size'] : 'thumbnail';
                            if ($size != 'id')
                                $replace_with = $arfieldhelper->get_media_from_id($replace_with, $size);
                        }


                        if ($field) {
                            if (isset($atts['show']) and $atts['show'] == 'field_label') {
                                $replace_with = stripslashes($field->name);
                            } else if (empty($replace_with) and $replace_with != '0') {
                                $replace_with = '';
                                if ($field->type == 'number')
                                    $replace_with = '0';
                            }else {
                                $replace_with = $arfieldhelper->get_display_value($replace_with, $field, $atts);
                            }
                        }

                        if (isset($atts['sanitize']))
                            $replace_with = sanitize_title_with_dashes($replace_with);

                        if (isset($atts['sanitize_url']))
                            $replace_with = urlencode(htmlentities($replace_with));

                        if (isset($atts['truncate'])) {
                            if (isset($atts['more_text'])) {
                                $more_link_text = $atts['more_text'];
                            } else
                                $more_link_text = (isset($atts['more_link_text'])) ? $atts['more_link_text'] : '. . .';

                            if ($display and $show == 'all') {
                                $more_link_text = ' <a href="' . $detail_link . '">' . $more_link_text . '</a>';
                                $replace_with = $armainhelper->truncate($replace_with, (int) $atts['truncate'], 3, $more_link_text);
                            } else {
                                $replace_with = wp_specialchars_decode(strip_tags($replace_with), ENT_QUOTES);
                                $part_one = substr($replace_with, 0, (int) $atts['truncate']);
                                $part_two = substr($replace_with, (int) $atts['truncate']);
                                $replace_with = $part_one . '<a onclick="jQuery(this).next().css(\'display\', \'inline\');jQuery(this).css(\'display\', \'none\')" class="frm_text_exposed_show"> ' . $more_link_text . '</a><span style="display:none;">' . $part_two . '</span>';
                            }
                        }

                        if (isset($atts['clickable']))
                            $replace_with = make_clickable($replace_with);

                        if (!isset($replace_with))
                            $replace_with = '';

                        $content = str_replace($shortcodes[0][$short_key], $replace_with, $content);



                        unset($replace_with);

                        if (isset($field))
                            unset($field);
                }
                unset($atts);
                unset($conditional);
            }
        }
        return $content;
    }

    function get_media_from_id($replace_with, $size = 'thumbnail') {

        $new_replace_with = array();

        $replace_with = explode('|', $replace_with);

        foreach ($replace_with as $replace_key => $replace_val) {
            if ($size == 'label') {
                $attachment = get_post($replace_val);

                $new_replace_with[] = basename($attachment->guid);
            } else {
                $image = wp_get_attachment_image_src($replace_val, $size);

                if ($image)
                    $new_replace_with[] = $image[0];
                else
                    $new_replace_with[] = wp_get_attachment_url($replace_val);
            }
        }

        $new_replace_with = implode('|', $new_replace_with);

        return $new_replace_with;
    }

    function get_display_value($replace_with, $field, $atts = array(), $is_for_mail = false) {

        global $armainhelper, $arfieldhelper;

        $sep = (isset($atts['sep'])) ? $atts['sep'] : ', ';
        if ($field->type == 'date') {
            if (isset($atts['time_ago']))
                $atts['format'] = 'Y-m-d H:i:s';

            if (!isset($atts['format']))
                $atts['format'] = false;

            $replace_with = $arfieldhelper->get_date($replace_with, $atts['format']);

            if (isset($atts['time_ago']))
                $replace_with = $armainhelper->human_time_diff(strtotime($replace_with), strtotime(date_i18n('Y-m-d')));
        }else if (is_numeric($replace_with) and $field->type == 'file') {
            $size = (isset($atts['size'])) ? $atts['size'] : 'thumbnail';
            if ($size != 'id')
                $replace_with = $arfieldhelper->get_media_from_id($replace_with, $size);
        }else if ($field->type == 'textarea') {
            $autop = isset($atts['wpautop']) ? $atts['wpautop'] : true;
            if (apply_filters('arfusewpautop', $autop))
                $replace_with = wpautop($replace_with);
            unset($autop);
        }else if ($field->type == 'number') {
            if (!isset($atts['decimal'])) {
                $num = explode('.', $replace_with);
                $atts['decimal'] = (isset($num[1])) ? strlen($num[1]) : 0;
            }

            if (!isset($atts['dec_point']))
                $atts['dec_point'] = '.';

            if (!isset($atts['thousands_sep']))
                $atts['thousands_sep'] = '';


            /* arf_dev_flag if for email than not to convert */
            if ($is_for_mail == false) {
                $replace_with = number_format($replace_with, $atts['decimal'], $atts['dec_point'], $atts['thousands_sep']);
            }
        }

        $replace_with = stripslashes_deep($replace_with);
        return $replace_with;
    }

    function get_table_options($field_options) {
        $columns = array();
        $rows = array();
        if (is_array($field_options)) {
            foreach ($field_options as $opt_key => $opt) {
                switch (substr($opt_key, 0, 3)) {
                    case 'col':
                        $columns[$opt_key] = $opt;
                        break;
                    case 'row':
                        $rows[$opt_key] = $opt;
                        break;
                }
            }
        }
        return array($columns, $rows);
    }

    function set_table_options($field_options, $columns, $rows) {
        if (is_array($field_options)) {
            foreach ($field_options as $opt_key => $opt) {
                if (substr($opt_key, 0, 3) == 'col' or substr($opt_key, 0, 3) == 'row')
                    unset($field_options[$opt_key]);
            }
        } else
            $field_options = array();

        foreach ($columns as $opt_key => $opt)
            $field_options[$opt_key] = $opt;

        foreach ($rows as $opt_key => $opt)
            $field_options[$opt_key] = $opt;

        return $field_options;
    }

    function show_default_blank_js($field_id, $default_blank) {
        
    }

    function arf_cl_field_menu($form_id, $select_name, $select_id = '', $default_field_id = 0) {
        global $arffield, $arfieldhelper;
        $arf_cl_field_selected_option = array();
        $arf_cl_field_selected_option['name'] = addslashes(esc_html__('Select Field','ARForms'));
        if (empty($form_id) or ! $form_id)
            return false;

        $fields = $arffield->getAll("fi.type not in ('divider', 'captcha', 'break', 'html', 'file', 'imagecontrol') and fi.form_id=" . (int) $form_id, 'id');

        if (count($fields) > 0) {

            $select_id = (isset($select_id)) ? $select_id : $select_name;

            $arf_cl_field_options = '';
            $cntr = 0;
            foreach ($fields as $field) {

                $field_id = $arfieldhelper->get_actual_id($field->id);

                if (( $default_field_id != 0 and $default_field_id == $field_id ) || ( $cntr == 0 )) {
                    $arf_cl_field_selected_option['field_id'] = $field_id;
                    $arf_cl_field_selected_option['name'] = $field->name;
                }

                $slider_class = '';
                if ($field->type == 'arfslider') {
                    if ($field->field_options['arf_range_selector'] == 1) {
                        $slider_class = ' arf_slider_li arf_hidden_slider_li';
                    } else {
                        $slider_class = ' arf_slider_li arf_show_slider_li';
                    }
                } else {
                    $slider_class = '';
                }
                if($field_id !="" && $this->arf_execute_function($field->name,'strip_tags') ==""){
                    $arf_cl_field_options .= '<li class="arf_selectbox_option ' . $slider_class . '" data-value="' . $field_id . '" data-label="[Field Id:'.$field_id.']">[Field Id:'.$field_id.']</li>';
                }else{
                    $arf_cl_field_options .= '<li class="arf_selectbox_option ' . $slider_class . '" data-value="' . $field_id . '" data-label="' . $this->arf_execute_function($field->name,'strip_tags') . '">' . $this->arf_execute_function($field->name,'strip_tags') . '</li>';
                }
                
                $cntr++;
            }
            echo '<input id="' . $select_id . '" name="' . $select_name . '" value="' . $arf_cl_field_selected_option['field_id'] . '" type="hidden">
				  <dl class="arf_selectbox" data-name="' . $select_name . '" data-id="' . $select_id . '" style="width:130px;">
				  	<dt><span>' . $this->arf_execute_function($arf_cl_field_selected_option['name'],'strip_tags') . '</span>
					<input value="' . $arf_cl_field_selected_option['name'] . '" style="display:none;width:118px;" class="arf_autocomplete" type="text" autocomplete="off">
					<i class="arfa arfa-caret-down arfa-lg"></i></dt>
				  	<dd>
						<ul class="field_dropdown_menu" style="display: none;" data-id="' . $select_id . '">
                        <li class="arf_selectbox_option" data-value="" data-label="'.addslashes(esc_html__('Select Field','ARForms')).'">'.addslashes(esc_html__('Select Field','ARForms')).'</li>
					  		' . $arf_cl_field_options . '
						</ul>
				  	</dd>
				  </dl>';
        }
    }

    function arf_cl_field_menu_submit_cl($form_id, $select_name, $select_id = '', $default_field_id = 0) {
        global $arffield, $arfieldhelper;
        $arf_cl_field_selected_option = array();
        $arf_cl_field_selected_option['name'] = addslashes(esc_html__('Select Field','ARForms'));
        if (empty($form_id) or ! $form_id)
            return false;

        $fields = $arffield->getAll("fi.type not in ('divider', 'captcha', 'break', 'html', 'file', 'imagecontrol','colorpicker','arf_product','arf_signature') and fi.form_id=" . (int) $form_id, 'id');

        if (count($fields) > 0) {

            $select_id = (isset($select_id)) ? $select_id : $select_name;

            $arf_cl_field_options = '';
            $cntr = 0;
            foreach ($fields as $field) {

                $field_id = $arfieldhelper->get_actual_id($field->id);

                if (( $default_field_id != 0 and $default_field_id == $field_id ) || ( $cntr == 0 )) {
                    $arf_cl_field_selected_option['field_id'] = $field_id;
                    $arf_cl_field_selected_option['name'] = $field->name;
                }

                $slider_class = '';
                if ($field->type == 'arfslider') {
                    if ($field->field_options['arf_range_selector'] == 1) {
                        $slider_class = ' arf_slider_li arf_hidden_slider_li';
                    } else {
                        $slider_class = ' arf_slider_li arf_show_slider_li';
                    }
                } else {
                    $slider_class = '';
                }
                if($field_id !="" && $this->arf_execute_function($field->name,'strip_tags')==""){
                    $arf_cl_field_options .= '<li class="arf_selectbox_option ' . $slider_class . '" data-value="' . $field_id . '" data-label="[Field Id:'.$field_id.']">[Field Id:'.$field_id.']</li>';
                }else{
                    $arf_cl_field_options .= '<li class="arf_selectbox_option ' . $slider_class . '" data-value="' . $field_id . '" data-label="' . $this->arf_execute_function($field->name,'strip_tags') . '">' . $this->arf_execute_function($field->name,'strip_tags') . '</li>';    
                }
                
                $cntr++;
            }
            echo '<input id="' . $select_id . '" name="' . $select_name . '" value="' . $arf_cl_field_selected_option['field_id'] . '" type="hidden">
                  <dl class="arf_selectbox" data-name="' . $select_name . '" data-id="' . $select_id . '" style="width:130px;">
                    <dt><span>'; 
                    if($this->arf_execute_function($arf_cl_field_selected_option['name'],'strip_tags') !=""){
                        echo $this->arf_execute_function($arf_cl_field_selected_option['name'],'strip_tags');
                    }else if($this->arf_execute_function($arf_cl_field_selected_option['name'],'strip_tags') =="" && $arf_cl_field_selected_option['field_id'] !=""){
                        echo '[Field Id:'.$arf_cl_field_selected_option['field_id'].']';
                    }else{
                        echo addslashes(esc_html__('Select Field', 'ARForms'));
                    }
            echo'</span>
                    <input value="' . $arf_cl_field_selected_option['name'] . '" style="display:none;width:118px;" class="arf_autocomplete" type="text" autocomplete="off">
                    <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                    <dd>
                        <ul class="field_dropdown_menu" style="display: none;" data-id="' . $select_id . '">
                        <li class="arf_selectbox_option" data-value="" data-label="'.addslashes(esc_html__('Select Field','ARForms')).'">'.addslashes(esc_html__('Select Field','ARForms')).'</li>
                            ' . $arf_cl_field_options . '
                        </ul>
                    </dd>
                  </dl>';
        }
    }

    function arf_cl_condition_operator_menu($select_name, $select_id = '', $default_rule = 'is', $ri = 0, $ci = 0) {

        $conditional_rules = array(
            'is' => addslashes(esc_html__('equals', 'ARForms')),
            'is not' => addslashes(esc_html__('not equals', 'ARForms')),
            'greater than' => addslashes(esc_html__('greater than', 'ARForms')),
            'less than' => addslashes(esc_html__('less than', 'ARForms')),
            'contains' => addslashes(esc_html__('contains', 'ARForms')),
            'not contains' => addslashes(esc_html__('not contains', 'ARForms')),
        );

        $select_id = (isset($select_id)) ? $select_id : $select_name;
        $arf_cl_field_selected_option = array();
        $arf_cl_field_options = '';
        $cntr = 0;
        foreach ($conditional_rules as $rule_id => $rule) {

            if (( isset($default_rule) and $default_rule == $rule_id ) || ( $cntr == 0 )) {

                $arf_cl_field_selected_option['rule_id'] = $rule_id;
                $arf_cl_field_selected_option['rule'] = $rule;
            }
            if($rule_id !="" && $this->arf_execute_function($rule,'strip_tags')==""){
                $arf_cl_field_options .= '<li class="arf_selectbox_option" data-value="' . $rule_id . '" data-label="[Field Id:'.$rule_id.']">[Field Id:'.$rule_id.']</li>';
            }else{
                $arf_cl_field_options .= '<li class="arf_selectbox_option" data-value="' . $rule_id . '" data-label="' . $this->arf_execute_function($rule,'strip_tags') . '">' . $this->arf_execute_function($rule,'strip_tags') . '</li>';
            }
            $cntr++;
        }
        echo '<input id="' . $select_id . '" name="options[arf_conditional_logic_rules]['.$ri.'][condition]['.$ci.'][operator]" value="' . $arf_cl_field_selected_option['rule_id'] . '" type="hidden">
                  <dl class="arf_selectbox" data-name="' . $select_name . '" data-id="' . $select_id . '" >
                    <dt><span>' . $this->arf_execute_function($arf_cl_field_selected_option['rule'],'strip_tags') . '</span>
                    <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                    <dd>
                        <ul class="operator_dropdown_menu" style="display: none;" data-id="' . $select_id . '">
                            ' . $arf_cl_field_options . '
                        </ul>
                    </dd>
                  </dl>';
    }

    function arf_cl_rule_for_conditional_email($select_name, $select_id, $default_rule = 'is', $rule_i = 1 ){
        $conditional_rules = array(
            'is' => addslashes(esc_html__('equals', 'ARForms')),
            'is not' => addslashes(esc_html__('not equals', 'ARForms')),
            'greater than' => addslashes(esc_html__('greater than', 'ARForms')),
            'less than' => addslashes(esc_html__('less than', 'ARForms')),
            'contains' => addslashes(esc_html__('contains', 'ARForms')),
            'not contains' => addslashes(esc_html__('not contains', 'ARForms')),
        );

        $select_id = (isset($select_id)) ? $select_id : $select_name;
        $arf_cl_field_selected_option = array();
        $arf_cl_field_options = '';
        $cntr = 0;
        foreach ($conditional_rules as $rule_id => $rule) {

            if (( isset($default_rule) and $default_rule == $rule_id ) || ( $cntr == 0 )) {

                $arf_cl_field_selected_option['rule_id'] = $rule_id;
                $arf_cl_field_selected_option['rule'] = $rule;
            }
            if($rule_id !="" && $this->arf_execute_function($rule,'strip_tags')=="" ){
                $arf_cl_field_options .= '<li class="arf_selectbox_option" data-value="' . $rule_id . '" data-label="[Field Id:'.$rule_id.']">[Field Id:'.$rule_id.']</li>';
            }else{
                $arf_cl_field_options .= '<li class="arf_selectbox_option" data-value="' . $rule_id . '" data-label="' . $this->arf_execute_function($rule,'strip_tags') . '">' . $this->arf_execute_function($rule,'strip_tags') . '</li>';
            }
            
            $cntr++;
        }
        echo '<input id="' . $select_id . '" name="options[arf_conditional_mail_rules]['.$rule_i.'][operator_mail]" value="' . $arf_cl_field_selected_option['rule_id'] . '" type="hidden">
                  <dl class="arf_selectbox" data-name="' . $select_name . '" data-id="' . $select_id . '" >
                    <dt><span>' . $this->arf_execute_function($arf_cl_field_selected_option['rule'],'strip_tags') . '</span>
                    <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                    <dd>
                        <ul class="operator_dropdown_menu" style="display: none;" data-id="' . $select_id . '">
                            ' . $arf_cl_field_options . '
                        </ul>
                    </dd>
                  </dl>';
    }

    function arf_cl_rule_menu_for_conditional_redirect($select_name, $select_id = '', $default_rule = 'is',$rule_i){
        $conditional_rules = array(
            'is' => addslashes(esc_html__('equals', 'ARForms')),
            'is not' => addslashes(esc_html__('not equals', 'ARForms')),
            'greater than' => addslashes(esc_html__('greater than', 'ARForms')),
            'less than' => addslashes(esc_html__('less than', 'ARForms')),
            'contains' => addslashes(esc_html__('contains', 'ARForms')),
            'not contains' => addslashes(esc_html__('not contains', 'ARForms')),
        );

        $select_id = (isset($select_id)) ? $select_id : $select_name;
        $arf_cl_field_selected_option = array();
        $arf_cl_field_options = '';
        $cntr = 0;
        foreach ($conditional_rules as $rule_id => $rule) {

            if (( isset($default_rule) and $default_rule == $rule_id ) || ( $cntr == 0 )) {

                $arf_cl_field_selected_option['rule_id'] = $rule_id;
                $arf_cl_field_selected_option['rule'] = $rule;
            }
            if($rule_id !="" && $this->arf_execute_function($rule,'strip_tags')){
                $arf_cl_field_options .= '<li class="arf_selectbox_option" data-value="' . $rule_id . '" data-label="[Field Id:'.$rule_id.']">[Field Id:'.$rule_id.']</li>';
            }else{
                $arf_cl_field_options .= '<li class="arf_selectbox_option" data-value="' . $rule_id . '" data-label="' . $this->arf_execute_function($rule,'strip_tags') . '">' . $this->arf_execute_function($rule,'strip_tags') . '</li>';    
            }
            
            $cntr++;
        }
        echo '<input id="' . $select_id . '" name="options[arf_conditional_redirect_rules]['.$rule_i.'][operator]" value="' . $arf_cl_field_selected_option['rule_id'] . '" type="hidden">
                  <dl class="arf_selectbox" data-name="' . $select_name . '" data-id="' . $select_id . '" >
                    <dt><span>' . $this->arf_execute_function($arf_cl_field_selected_option['rule'],'strip_tags') . '</span>
                    <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                    <dd>
                        <ul class="operator_dropdown_menu" style="display: none;" data-id="' . $select_id . '">
                            ' . $arf_cl_field_options . '
                        </ul>
                    </dd>
                  </dl>';
    }

    function arf_cl_rule_menu_for_conditional_subscription($select_name, $select_id = '', $default_rule = 'is',$rule_i){
        $conditional_rules = array(
            'is' => addslashes(esc_html__('equals', 'ARForms')),
            'is not' => addslashes(esc_html__('not equals', 'ARForms')),
            'greater than' => addslashes(esc_html__('greater than', 'ARForms')),
            'less than' => addslashes(esc_html__('less than', 'ARForms')),
            'contains' => addslashes(esc_html__('contains', 'ARForms')),
            'not contains' => addslashes(esc_html__('not contains', 'ARForms')),
        );

        $select_id = (isset($select_id)) ? $select_id : $select_name;
        $arf_cl_field_selected_option = array();
        $arf_cl_field_options = '';
        $cntr = 0;
        foreach ($conditional_rules as $rule_id => $rule) {

            if (( isset($default_rule) and $default_rule == $rule_id ) || ( $cntr == 0 )) {

                $arf_cl_field_selected_option['rule_id'] = $rule_id;
                $arf_cl_field_selected_option['rule'] = $rule;
            }
            if($rule_id !="" && $this->arf_execute_function($rule,'strip_tags')==""){
                $arf_cl_field_options .= '<li class="arf_selectbox_option" data-value="' . $rule_id . '" data-label="[Field Id:'.$rule_id.']">[Field Id:'.$rule_id.']</li>';
            }else{
                $arf_cl_field_options .= '<li class="arf_selectbox_option" data-value="' . $rule_id . '" data-label="' . $this->arf_execute_function($rule,'strip_tags') . '">' . $this->arf_execute_function($rule,'strip_tags') . '</li>';    
            }
            
            $cntr++;
        }
        echo '<input id="' . $select_id . '" name="options[arf_condition_on_subscription_rules]['.$rule_i.'][operator]" value="' . $arf_cl_field_selected_option['rule_id'] . '" type="hidden">
                  <dl class="arf_selectbox" data-name="' . $select_name . '" data-id="' . $select_id . '" >
                    <dt><span>' . $this->arf_execute_function($arf_cl_field_selected_option['rule'],'strip_tags') . '</span>
                    <i class="arfa arfa-caret-down arfa-lg"></i></dt>
                    <dd>
                        <ul class="operator_dropdown_menu" style="display: none;" data-id="' . $select_id . '">
                            ' . $arf_cl_field_options . '
                        </ul>
                    </dd>
                  </dl>';
    }

    function arf_cl_rule_menu($select_name, $select_id = '', $default_rule = 'is') {

        $conditional_rules = array(
            'is' => addslashes(esc_html__('equals', 'ARForms')),
            'is not' => addslashes(esc_html__('not equals', 'ARForms')),
            'greater than' => addslashes(esc_html__('greater than', 'ARForms')),
            'less than' => addslashes(esc_html__('less than', 'ARForms')),
            'contains' => addslashes(esc_html__('contains', 'ARForms')),
            'not contains' => addslashes(esc_html__('not contains', 'ARForms')),
        );

        $select_id = (isset($select_id)) ? $select_id : $select_name;
        $arf_cl_field_selected_option = array();
        $arf_cl_field_options = '';
        $cntr = 0;
        foreach ($conditional_rules as $rule_id => $rule) {

            if (( isset($default_rule) and $default_rule == $rule_id ) || ( $cntr == 0 )) {

                $arf_cl_field_selected_option['rule_id'] = $rule_id;
                $arf_cl_field_selected_option['rule'] = $rule;
            }
            if($rule_id !="" && $this->arf_execute_function($rule,'strip_tags')==""){
                $arf_cl_field_options .= '<li class="arf_selectbox_option" data-value="' . $rule_id . '" data-label="[Field Id:'.$rule_id.']">[Field Id:'.$rule_id.']</li>';
            }else{
                $arf_cl_field_options .= '<li class="arf_selectbox_option" data-value="' . $rule_id . '" data-label="' . $this->arf_execute_function($rule,'strip_tags') . '">' . $this->arf_execute_function($rule,'strip_tags') . '</li>';    
            }
            
        }
        echo '<input id="' . $select_id . '" name="' . $select_name . '" value="' . $arf_cl_field_selected_option['rule_id'] . '" type="hidden">
				  <dl class="arf_selectbox" data-name="' . $select_name . '" data-id="' . $select_id . '" >
				  	<dt><span>' . $this->arf_execute_function($arf_cl_field_selected_option['rule'],'strip_tags') . '</span>
					<i class="arfa arfa-caret-down arfa-lg"></i></dt>
				  	<dd>
						<ul class="operator_dropdown_menu" style="display: none;" data-id="' . $select_id . '">
					  		' . $arf_cl_field_options . '
						</ul>
				  	</dd>
				  </dl>';
    }

    function get_actual_id($field_id) {
        global $wpdb, $MdlDb;
        return $field_id;
    }

    function get_field_type($filed_id = '') {

        if (empty($filed_id) or $filed_id == '')
            return false;

        global $wpdb, $MdlDb;
        $res = $wpdb->get_results($wpdb->prepare("SELECT id, type FROM " . $MdlDb->fields . " WHERE id = %d", $filed_id));

        /* sometime value not set for select field `0_o` */
        if (isset($res[0])) {
            $res = $res[0];
            return $res->type;
        } else {
            return '';
        }
    }

    /* arf_dev_flag - Currently Not used in ARForms but need to check in All Addons */
    function get_onchage_func($field = '', $arf_data_uniq_id = '',$form = '',$res = array()) {
        if (empty($field) or $field == '' or is_admin())
            return false;

        $returnstring = "";
        $conditional_change_fnc = "";
        $runningtotal_change_fnc = "";
        

        global $arfieldhelper;

        $field['id'] = $arfieldhelper->get_actual_id($field['id']);
        global $wpdb, $MdlDb;

        $string = '';
        $stringfnc = '';
        foreach ($res as $data) {

            $conditional_logic = maybe_unserialize($data->conditional_logic);
            if (isset($conditional_logic['enable']) and $conditional_logic['enable'] == 1) {
                if (count($conditional_logic['rules']) > 0) {

                    foreach ($conditional_logic['rules'] as $val) {

                        if ($val['field_id'] == $field['id']) {
                            $data->id = $arfieldhelper->get_actual_id($data->id);
                            $string .= $data->id . ',';
                        }
                    }
                }
            }
            if( is_array($data->field_options) ){
                $field_options = $data->field_options;
            } else {
                $field_options = json_decode($data->field_options, true);
                if (json_last_error() != JSON_ERROR_NONE) {
                    $field_options = maybe_unserialize($data->field_options);
                }
            }

            if ($data->type == 'html' && isset($field_options['enable_total']) && $field_options['enable_total'] == 1) {
                $regex = '/<arftotal>(.*?)<\/arftotal>/is';
		      /* arf_dev_flag = check this preg_match condition */
                preg_match($regex, $field_options['description'], $arftotalmatches);

                if ($arftotalmatches) {
                    $regexp = $arftotalmatches[1];

                    if ($arfieldhelper->arf_is_field_inregexp($regexp, $field['id'])) {
                        $data->id = $arfieldhelper->get_actual_id($data->id);
                        $stringfnc .= $data->id . ',';
                    }
                }
            }
        }

        $formoptions = isset( $form->options ) ? maybe_unserialize($form->options) : array();
        $submit_conditional_logic = isset($formoptions['submit_conditional_logic']) ? $formoptions['submit_conditional_logic'] : array();

        if (isset($submit_conditional_logic['enable']) and $submit_conditional_logic['enable'] == 1) {
            if (count($submit_conditional_logic['rules']) > 0) {

                foreach ($submit_conditional_logic['rules'] as $val) {

                    if ($val['field_id'] == $field['id']) {
                        $string .= "'arfsubmit',";
                    }
                }
            }
        }
        if( !isset($form) || $form == '' ){
            $form = new stdClass();
        }
        $form->form_key = isset( $form->form_key ) ? $form->form_key : '';
        
        if ($string != '') {

            $string = rtrim($string, ',');

            $conditional_change_fnc = ' arf_rule_apply(\'' . $form->form_key . '\',\'' . $arf_data_uniq_id . '\',\'' . $field['field_key'] . '\', \'' . $field['id'] . '\', [' . $string . ']);';
        }

        if ($stringfnc != '') {

            $stringfnc = rtrim($stringfnc, ',');

            $runningtotal_change_fnc = ' arf_calculate_total(\'' . $form->form_key . '\', \'' . $field['id'] . '\', [' . $stringfnc . ']);';
        }

        $arf_new_conditional_on_change_fnc = ' arf_new_cl_apply(\'' . $form->form_key . '\', \'' . $field['id'] . '\', \'' . $field['type'] . '\', \'' . $field['field_key'] . '\',\'' . $arf_data_uniq_id . '\');';
        if ($conditional_change_fnc != '' || $runningtotal_change_fnc != '') {
            if ($field['type'] == 'checkbox' || $field['type'] == 'radio' || $field['type'] == 'like') {
                if ($field['type'] == 'radio')
                    return ' onclick="' . $conditional_change_fnc . $runningtotal_change_fnc . $arf_new_conditional_on_change_fnc . '" ';
                else
                    return ' onchange="' . $conditional_change_fnc . $runningtotal_change_fnc . $arf_new_conditional_on_change_fnc . '" ';
            } else if ($field['type'] == 'select' || $field['type'] == 'time' || $field['type'] == 'scale' || $field['type'] == 'arfslider') {
                return ' onchange="' . $conditional_change_fnc . $runningtotal_change_fnc . $arf_new_conditional_on_change_fnc . '" ';
            } else {
                return ' onchange="' . $conditional_change_fnc . $arf_new_conditional_on_change_fnc . '"  onkeyup="setTimeout(function(){' . $conditional_change_fnc . $arf_new_conditional_on_change_fnc . '}, 100);" onblur="'.$runningtotal_change_fnc.'" ';
            }
        } elseif ($arf_new_conditional_on_change_fnc != '') {
            if ($field['type'] == 'checkbox' || $field['type'] == 'radio' || $field['type'] == 'like') {
                if ($field['type'] == 'radio')
                    return ' onclick="' . $arf_new_conditional_on_change_fnc . '" ';
                else
                    return ' onchange="' . $arf_new_conditional_on_change_fnc . '" ';
            } else if ($field['type'] == 'select' || $field['type'] == 'time' || $field['type'] == 'scale' || $field['type'] == 'arfslider') {
                return ' onchange="' . $arf_new_conditional_on_change_fnc . '" ';
            } else {

                return ' onchange="' . $arf_new_conditional_on_change_fnc . '" onkeyup="setTimeout(function(){' . $arf_new_conditional_on_change_fnc . '}, 100);" ';
            }
        } else {
            return '';
        }
    }

    function arf_check_running_total_field_func($arf_on_change_function = '',$field = '',$arf_data_uniq_id = '',$form = '',$res_data = array()){
        global $arformcontroller;
        if( empty($field) || $field == '' || is_admin() ){
            return $arf_on_change_function;
        }

        if( isset($field['enable_running_total']) && $field['enable_running_total'] != '' && $field['enable_running_total'] > 0 ){
            $field_running_total = array_unique(explode(',',$field['enable_running_total']));
            foreach($field_running_total as $ki => $frt ){
                $key = $arformcontroller->arfSearchArray($frt,'id',$arformcontroller->arfObjtoArray($res_data));
                if( (string)$key == '' ){
                    unset($field_running_total[$ki]);
                }
            }
            $field_running_total = implode(',',$field_running_total );
            if( $arf_on_change_function != '' ){
                $pattern = "/(arf_cl_apply_v3\((.*?)\)\;)/";

                preg_match_all($pattern,$arf_on_change_function,$matches);

                
                $new_string = "arf_calculate_total(\"".$form->form_key."\",\"{$field['id']}\",[{$field_running_total}]);";

                if( isset($matches[1]) && isset($matches[1][0]) && $matches[1][0] != '' ){
                    $new_string = $matches[1][0].$new_string;
                    $arf_on_change_function = preg_replace($pattern,$new_string,$arf_on_change_function);
                }
                return $arf_on_change_function;

            } else {

                $onKeyFields = apply_filters('arf_onchange_only_click_event_outside', array('checkbox', 'radio', 'scale','select','arfslider'));
                
                if (!empty($field['field_key'])) {
                    if (in_array($field['type'], $onKeyFields)) {
                        $arf_on_change_function .= " onChange='clearTimeout(__arf_timeout_handle); __arf_timeout_handle = setTimeout(function(){arf_calculate_total(\"".$form->form_key."\",\"{$field['id']}\",[{$field_running_total}]);},100);'";
                    } else {
                        $arf_on_change_function .= " onkeyup='clearTimeout(__arf_timeout_handle); __arf_timeout_handle = setTimeout(function(){arf_calculate_total(\"".$form->form_key."\",\"{$field['id']}\",[{$field_running_total}])},100);' ";
                    }
                }
            }
        }

        return $arf_on_change_function;
    }

    function arf_field_on_change_function($field_id = '', $unique_id = '', $form_options = '',$field_type) {
        if (empty($field_id) or $field_id == '' or is_admin()) {
            return false;
        }
        $form_data_opts = maybe_unserialize($form_options);
        
        $arf_conditional_logic = isset($form_data_opts['arf_conditional_logic_rules']) ? $form_data_opts['arf_conditional_logic_rules'] : array();
        $arf_submit_button_logic = isset($form_data_opts['submit_conditional_logic']) ? $form_data_opts['submit_conditional_logic'] : array();
        $field_keys = array();

        
        foreach ($arf_conditional_logic as $key => $conditional_logic) {
            $operator = isset($conditional_logic['logical_operator']) ? $conditional_logic['logical_operator'] : '';
            $conditions = isset($conditional_logic['condition']) ? $conditional_logic['condition'] : '';
            $results = $conditional_logic['result'];
            if(is_array($conditions))
            {
                foreach ($conditions as $cKey => $condition) {
                    if ($field_id == $condition['field_id']) {
                        if ($results && !empty($results)) {
                            foreach ($results as $rKey => $result) {
                                $field_keys[] = $result['field_id'];
                            }
                        }
                    }
                }
            }
        }

        if (isset($arf_submit_button_logic) && !empty($arf_submit_button_logic) && $arf_submit_button_logic['enable'] == 1) {
            foreach ($arf_submit_button_logic['rules'] as $key => $condition) {
                if ($field_id == $condition['field_id']) {
                    $field_keys[] = 'submit';
                }
            }
        }

        $field_keys = array_unique($field_keys);

        $return = "";
        $onKeyFields = apply_filters('arf_onchange_only_click_event_outside', array('checkbox', 'radio', 'scale','select','arfslider','date'));
        if (!empty($field_keys)) {
            if (in_array($field_type, $onKeyFields)) {
                $return .= " onChange='arf_cl_apply_v3(" . $unique_id . "," . json_encode($field_keys) . ");'";
            } else {
                $return .= " onChange='arf_cl_apply_v3(" . $unique_id . "," . json_encode($field_keys) . ");' ";
                $return .= " onkeyup='clearTimeout(__arf_timeout_handle); __arf_timeout_handle = setTimeout(function(){arf_cl_apply_v3(" . $unique_id . "," . json_encode($field_keys) . ");},300);' ";
            }
        }
        return $return;
    }

    function get_form_logic_rules($form_id, $form_key) {

        global $arf_data_uniq_id;
        if (empty($form_id) || $form_id == '' || empty($form_key))
            return false;

        global $wpdb, $MdlDb, $arfieldhelper;
        $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->fields . " WHERE form_id = %d ORDER BY id", $form_id));

        $logic_rules = '';
        $page_no = 0;
        foreach ($res as $data) {

            if ($data->type == 'break')
                $page_no++;

            $conditional_logic = maybe_unserialize($data->conditional_logic);

            if (isset($conditional_logic['enable']) and $conditional_logic['enable'] == 1) {

                if (count($conditional_logic['rules']) > 0) {

                    $string = '';
                    foreach ($conditional_logic['rules'] as $val) {

                        $field_key = $wpdb->get_var($wpdb->prepare("SELECT `field_key` from " . $MdlDb->fields . " WHERE `id` = %d",$val['field_id']));
			
                        $string .= " { 'rule_no' : '" . $val['id'] . "', 'field_id' : '" . $val['field_id'] . "', 'field_type' : '" . $val['field_type'] . "','field_key' : '" . $field_key . "', 'operator' : '" . $val['operator'] . "', 'value' : '" . addslashes($val['value']) . "' },";
                    }

                    $string = rtrim($string, ',');

                    $default_value = $arfieldhelper->get_field_defautl_value($data);

                    $logic_rules .= (($data->id > 0) ? $data->id : $data->id) . " : { 'display': '" . $conditional_logic['display'] . "', 'if_cond': '" . $conditional_logic['if_cond'] . "', 'field_type': '" . $data->type . "', 'field_key': '" . $data->field_key . "', 'default_value': " . $default_value . ", 'page': '" . $page_no . "', 'rules':[" . $string . "] }, ";
                }
            }
        }
        
        $form = $wpdb->get_results($wpdb->prepare("SELECT options FROM " . $MdlDb->forms . " WHERE id = %d", $form_id));

        $form = $form[0];

        $formoptions = maybe_unserialize($form->options);
        $submit_conditional_logic = isset($formoptions['submit_conditional_logic']) ? $formoptions['submit_conditional_logic'] : array();

        if (isset($submit_conditional_logic['enable']) and $submit_conditional_logic['enable'] == 1) {
            if (count($submit_conditional_logic['rules']) > 0) {
                $string = '';
                foreach ($submit_conditional_logic['rules'] as $val) {

                    $field_key = $wpdb->get_var($wpdb->prepare("SELECT `field_key` from " . $MdlDb->fields . " WHERE `id` = %d",$val['field_id']));
		    
                    $string .= " { 'rule_no' : '" . $val['id'] . "', 'field_id' : '" . $val['field_id'] . "', 'field_key' : '" . $field_key . "', 'field_type' : '" . $val['field_type'] . "', 'operator' : '" . $val['operator'] . "', 'value' : '" . $val['value'] . "' },";
                }

                $string = rtrim($string, ',');

                $default_value = '';

                $logic_rules .= "'arfsubmit' : { 'display': '" . $submit_conditional_logic['display'] . "', 'if_cond': '" . $submit_conditional_logic['if_cond'] . "', 'field_type': 'submit', 'field_key': '" . $form_key . "', 'default_value': '" . $default_value . "', 'page': '" . $page_no . "', 'rules':[" . $string . "] }, ";
            }
        }
        if (isset($logic_rules) and $logic_rules != '') {

            return '<div><script type="text/javascript" data-cfasync="false">if(window[\'jQuery\']){ if(!window[\'arf_cl\']) window[\'arf_cl\'] = new Array(); window[\'arf_cl\'][\'' . $form_key . '\'] = { ' . $logic_rules . ' }; }</script></div>';
        }

        return '';
    }

    function get_field_defautl_value($field) {
        global $armainhelper;
        if (!$field)
            return;

        $field = (array) $field;

        $value1 = '';

        $field_options = maybe_unserialize($field['field_options']);
        $field['default_value'] = isset($field['default_value']) ? $field['default_value'] : '';

        $field_options['default_blank'] = isset($field_options['default_blank']) ? $field_options['default_blank'] : '';

        if ((isset($field_options['clear_on_focus']) and $field_options['clear_on_focus'] and ! empty($field['default_value']))) {

            if ($field_options['default_blank'] == 1) {
                $value1 = trim($armainhelper->esc_textarea($field['default_value']));
            }
        } else {

            if ($field_options['default_blank'] == 1) {
                $value1 = trim($armainhelper->esc_textarea($field['default_value']));
            }
        }

        if ($field['type'] == 'scale') {
            $value1 = ( isset($field['default_value']) and $field['default_value'] != '' ) ? $field['default_value'] : '';
        }

        if ($field['type'] == 'radio' || $field['type'] == 'select' || $field['type'] == ARF_AUTOCOMPLETE_SLUG) {

            $field_options = maybe_unserialize($field['options']);
            $field_options_other = maybe_unserialize($field['field_options']);

            foreach ($field_options as $opt_key => $opt) {
                $field_val = $opt;
                if (is_array($opt)) {
                    $opt = $opt['label'];

                    $field_val = isset($field_options_other['separate_value']) ? $field_val['value'] : $opt;
                }
                if (trim($field['default_value']) == trim($field_val)) {
                    $value1 = addslashes($field_val);
                }
            }
        }

        if ($field['type'] == 'checkbox') {

            $field_options = maybe_unserialize($field['options']);

            $default_value = maybe_unserialize($field['default_value']);

            foreach ($field_options as $opt_key => $opt) {
                $field_val = $opt;

                if (is_array($opt)) {
                    $opt = $opt['label'];
                    $field_val = isset($field_options['separate_value']) ? $field_val['value'] : $opt;
                }
            }

            if ($default_value && is_array($default_value)) {
                $str_for_check = "[";
                foreach ($default_value as $chk_value) {
                    $value1 = $chk_value;
                    $str_for_check .= "'" . addslashes($value1) . "', ";
                }
                $str_for_check = rtrim($str_for_check, ', ');
                $str_for_check .= "]";
                return $str_for_check;
            } else {
                return "''";
            }
        }
        if ($field['type'] == 'hidden' || $field['type'] == 'like') {
            $value1 = $field['default_value'];
        }

        if ($field['type'] == 'arfslider') {
            $field['slider_value'] = isset($field_options['slider_value']) ? $field_options['slider_value'] : '';
            $value1 = ($field['slider_value'] != '') ? $field['slider_value'] : ( is_numeric($field_options['minnum']) ? $field_options['minnum'] : 1 );
        }

        return "'" . $value1 . "'";
    }

    function get_display_style($field = '') {

        global $wpdb, $MdlDb, $arfieldhelper;
        $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->fields . " WHERE form_id = %d  ORDER BY id", $field['form_id']), OBJECT_K);
        $style = '';
        $field['id'] = $arfieldhelper->get_actual_id($field['id']);
        $form_id = $field['form_id'];
        foreach ($res as $data) {

            $data->id = $arfieldhelper->get_actual_id($data->id);

            if ($field['id'] == $data->id) {

                $conditional_logic = maybe_unserialize($data->conditional_logic);

                if (isset($conditional_logic['enable']) and $conditional_logic['enable'] == 1) {

                    if (count($conditional_logic['rules']) > 0) {

                        $matched = 0;
                        $res_field_send = '';
                        $rule_cout = count($conditional_logic['rules']);
                        foreach ($conditional_logic['rules'] as $val) {

                            foreach ($res as $data_field) {
                                $data_field->id = $arfieldhelper->get_actual_id($data_field->id);
                                if ($data_field->id == $val['field_id']) {
                                    $res_field_send = $data_field;
                                }
                            }
                            if ($arfieldhelper->calculate_rule($res_field_send, $val['operator'], $val['value']))
                                $matched++;
                        }
                        if (($conditional_logic['if_cond'] == 'all' && $rule_cout == $matched) || ($conditional_logic['if_cond'] == 'any' && $matched > 0)) {

                            if ($conditional_logic['display'] == 'hide') {
                                $style = 'style="display:none;"';
                            } else if ($conditional_logic['display'] == 'disable') {
                                $style = 'style="pointer-events: none; opacity: 0.7;"  data-view="arf_disable"  data-type="' . $data->type . '"';
                            } else {
                                $style = '';
                            }
                        } else {
                            if ($conditional_logic['display'] == 'show') {
                                $style = 'style="display:none;"';
                            } else if ($conditional_logic['display'] == 'enable') {
                                $style = 'style="pointer-events: none; opacity: 0.7;" data-view="arf_disable"  data-type="' . $data->type . '"';
                            } else {
                                $style = '';
                            }
                        }
                    }
                }
            }
        }

        return $style;
    }

    function get_display_style_new($field = '', $fields, $form) {
        global $wpdb, $MdlDb, $arfieldhelper, $arformcontroller;

        $arf_form_options = maybe_unserialize($form->options);

        $arf_conditional_logic_rules = $arf_form_options['arf_conditional_logic_rules'];
        $confirm_email = 0;
        $confirm_email_style = '';
        $style = '';
        $else_style = ' style="';
        $else_attr = '';

        $res = is_array($fields) ? $arformcontroller->arfArraytoObj($fields) : $fields;

        foreach ($res as $data) {
            $data->id = $arfieldhelper->get_actual_id($data->id);

            if ($field['id'] == $data->id) {
                if(isset($data->field_options))
                {
                    if (is_object($data->field_options)) {
                        $data_opts = $arformcontroller->arfObjtoArray($data->field_options);
                    } else {
                        $data_opts = json_decode($data->field_options, true);
                        if (json_last_error() != JSON_ERROR_NONE) {
                            $data_opts = maybe_unserialize($data->field_options);
                        }
                    }
                }
                if(isset($data_opts) && !empty($data_opts))
                {
                    foreach ($data_opts as $data_key => $data_opt) {
                        $data->$data_key = $data_opt;
                    }
                }
                
                if (isset($arf_conditional_logic_rules) && !empty($arf_conditional_logic_rules)) {

                    foreach ($arf_conditional_logic_rules as $key => $logic_rules) {
                        if (isset($logic_rules['condition']) && !empty($logic_rules['condition'])) {
                            $matched = 0;
                            $rule_cout = count($logic_rules['condition']);
                            foreach ($logic_rules['condition'] as $key_condition => $arf_condition) {
                                $res_field_send = '';
                                foreach ($res as $data_field) {
                                    if(isset($data_field->field_options))
                                    {
                                        if( is_object($data_field->field_options) ){
                                            $data_f_opts = $arformcontroller->arfObjtoArray($data_field->field_options);
                                        } else {
                                            $data_f_opts = json_decode($data_field->field_options, true);
                                            if (json_last_error() != JSON_ERROR_NONE) {
                                                $data_f_opts = maybe_unserialize($data_field->field_options);
                                            }
                                        }
                                    }
                                    if(isset($data_f_opts) && !empty($data_f_opts))
                                    {
                                        foreach ($data_f_opts as $data_f_key => $data_f_opt) {
                                            $data_field->$data_f_key = $data_f_opt;
                                        }
                                    }
                                    $data_field->id = $arfieldhelper->get_actual_id($data_field->id);

                                    if ($data_field->id == $arf_condition['field_id']) {
                                        $res_field_send = $data_field;
                                    }
                                }
                                if ($arfieldhelper->calculate_rule($res_field_send, $arf_condition['operator'], $arf_condition['value'])) {
                                    $matched++;
                                }
                            }
                        }

                        /* if multipal result action and one of them is hide but after hide action there is action */
                        $final_action_list = array();
                        if (isset($logic_rules['result']) && !empty($logic_rules['result'])) {
                            foreach ($logic_rules['result'] as $key_result => $arf_result) {
                                if ($field['id'] == $arf_result['field_id']) {
                                    $final_action_list[] = $arf_result['action'];
                                }
                            }
                        }

                        foreach ($logic_rules['result'] as $key_result => $arf_result) {

                            if ($field['id'] == $arf_result['field_id']) {

                                if (($logic_rules['logical_operator'] == 'and' && $rule_cout == $matched) || ($logic_rules['logical_operator'] == 'or' && $matched > 0)) {
                                    if (in_array('hide', $final_action_list)) {
                                        $else_style .= 'display:none;';
                                    }
                                    if (in_array('disable', $final_action_list)) {
                                        $else_style .= 'pointer-events: none; opacity: 0.7;';
                                        $else_attr = '  data-view="arf_disable"  data-type="' . $arf_result['field_type'] . '"';
                                    }
                                } else {
                                    if (in_array('show', $final_action_list)) {
                                        $else_style .= 'display:none;';
                                    }
                                    if ((in_array('enable', $final_action_list))) {
                                        $else_style .= 'pointer-events: none; opacity: 0.7;';
                                        $else_attr = ' data-view="arf_disable"  data-type="' . $arf_result['field_type'] . '"';
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        /* IF YOU WANT TO CHANGE RETURN VALUE FORMAT THAN ONCE CHECK PAGE BREAK WITH ALL POSSIBILITIES */
        $style = $style . $else_style . '" ' . $else_attr;

        return $style;
    }

    function get_display_style_submit($form) {
        $style = '';
        global $wpdb, $MdlDb, $arfieldhelper;
        if( !isset($GLOBALS['form_fields'][$form->id])){
            $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $MdlDb->fields . " WHERE form_id = %d  ORDER BY id", $form->id), OBJECT_K);
        } else {
            $res = $GLOBALS['form_fields'][$form->id];
        }
        
        
        $formoptions = maybe_unserialize($form->options);

        $submit_conditional_logic = isset($formoptions['submit_conditional_logic']) ? $formoptions['submit_conditional_logic'] : array();

        if (isset($submit_conditional_logic['enable']) and $submit_conditional_logic['enable'] == 1) {

            if (count($submit_conditional_logic['rules']) > 0) {
                $res_field_send = array();
                $matched = 0;
                $rule_cout = count($submit_conditional_logic['rules']);
                foreach ($submit_conditional_logic['rules'] as $val) {

                    foreach ($res as $data_field) {
                        $data_field->id = $arfieldhelper->get_actual_id($data_field->id);
                        
                        if ($data_field->id == $val['field_id']) {
                            $res_field_send = $data_field;
                        }
                    }

                    if ($arfieldhelper->calculate_rule($res_field_send, $val['operator'], $val['value'], true))
                        $matched++;
                }
                
                $display_type = $submit_conditional_logic['display'];
                
                if ((strtolower($submit_conditional_logic['if_cond']) == 'all' && $rule_cout == $matched) || ($submit_conditional_logic['if_cond'] == 'any' && $matched > 0)) {
                    
                    $style = ($display_type == 'Disable' || $display_type == 'hide') ? 'style="display:none;"' : '';
                } else {
                    $style = ($display_type == 'Enable' || $display_type == 'show') ? 'style="display:none;"' : '';
                }
            }
        }

        return $style;
    }

    function calculate_rule($field, $operator, $value, $submit = false) {

        global $armainhelper, $arfieldhelper;

        $field = (array) $field;

        if (empty($field[0]) && !$submit) {
            return;
        } else if( empty($field) && $submit ){
            return;
        }
        $value1 = '';
        $value2 = isset($value) ? $value : '';
        if( is_array($field['field_options'])){
            $field_options = $field['field_options'];
        } else {
            $field_options = json_decode($field['field_options'], true);
            if (json_last_error() != JSON_ERROR_NONE) {
                $field_options = maybe_unserialize($field['field_options']);
            }
        }

        $field['default_value'] = isset($field['default_value']) ? $field['default_value'] : '';
        $field_options['default_blank'] = isset($field_options['default_blank']) ? $field_options['default_blank'] : '';

        if ((isset($field_options['clear_on_focus']) and $field_options['clear_on_focus'] and ! empty($field['default_value']))) {

            if ($field_options['default_blank'] == 1) {
                $value1 = trim($armainhelper->esc_textarea($field['default_value']));
            }
        } else {

            if ($field_options['default_blank'] == 1) {
                $value1 = trim($armainhelper->esc_textarea($field['default_value']));
            }
        }

        if (isset($field['type'])) {

            if ($field['type'] == 'scale') {
                $value1 = ( isset($field['default_value']) and $field['default_value'] != '' ) ? $field['default_value'] : '';
            }

            if ($field['type'] == 'radio' || $field['type'] == 'select' || $field['type'] == ARF_AUTOCOMPLETE_SLUG) {
                if( is_array($field['options'])){
                    $fieldoptions = $field['options'];
                } else {
                    $fieldoptions = json_decode($field['options'], true);
                    if (json_last_error() != JSON_ERROR_NONE) {
                        $fieldoptions = maybe_unserialize($field['options']);
                    }
                }

                foreach ($fieldoptions as $opt_key => $opt) {
                    $field_val = $opt;
                    if (is_array($opt)) {
                        $opt = $opt['label'];
                        $field_val = ($field_options['separate_value']) ? $field_val['value'] : $opt;
                    }
                    if ($field['default_value'] == $field_val)
                        $value1 = $field_val;
                }
            }

            if ($field['type'] == 'checkbox') {
                
                if( is_array($field['options'])){
                    $fieldoptions = $field['options'];
                } else {
                    $fieldoptions = json_decode($field['options'], true);
                    if (json_last_error() != JSON_ERROR_NONE) {
                        $fieldoptions = maybe_unserialize($field['options']);
                    }
                }
                if( is_array($field['default_value']) ){
                    $default_value = $field['default_value'];
                } else {
                    $default_value = json_decode($field['default_value'],true);
                    if( json_last_error() != JSON_ERROR_NONE ){
                        $default_value = maybe_unserialize($field['default_value']);
                    }
                }

                foreach ($fieldoptions as $opt_key => $opt) {
                    $field_val = $opt;

                    if (is_array($opt)) {
                        $opt = $opt['label'];
                        $field_val = ($field_options['separate_value']) ? $field_val['value'] : $opt;
                    }
                }
            }
            if ($field['type'] == 'hidden') {

                $hidden_field_value = $field['default_value'];
                $arf_current_user = wp_get_current_user();

                if (preg_match('/\[ARF_current_user_id\]/', $hidden_field_value)) {
                    $hidden_field_value = str_replace('[ARF_current_user_id]', $arf_current_user->ID, $hidden_field_value);
                }
                if (preg_match('/\[ARF_current_user_name\]/', $hidden_field_value)) {
                    $hidden_field_value = str_replace('[ARF_current_user_name]', $arf_current_user->user_login, $hidden_field_value);
                }
                if (preg_match('/\[ARF_current_user_email\]/', $hidden_field_value)) {
                    $hidden_field_value = str_replace('[ARF_current_user_email]', $arf_current_user->user_email, $hidden_field_value);
                }
                if (preg_match('/\[ARF_current_date\]/', $hidden_field_value)) {
                    $wp_format_date = get_option('date_format');
                    $arf_current_date = date($wp_format_date, current_time('timestamp'));
                    $hidden_field_value = str_replace('[ARF_current_date]', $arf_current_date, $hidden_field_value);
                }
                $value1 = $hidden_field_value;
            }
            if ($field['type'] == 'like') {
                $value1 = $field['default_value'];
            }

            if ($field['type'] == 'arfslider') {
                $field['slider_value'] = isset($field_options['slider_value']) ? $field_options['slider_value'] : '';
                $value1 = ($field['slider_value'] != '') ? $field['slider_value'] : ( is_numeric($field_options['minnum']) ? $field_options['minnum'] : 1 );
            }
            if (isset($_REQUEST) && isset($_REQUEST['item_meta']) && array_key_exists($field['id'], $_REQUEST['item_meta'])) {
                $value1 = $_REQUEST['item_meta'][$field['id']];
                if ($field['type'] == 'checkbox') {
                    if (isset($default_value) && is_array($default_value)) {
                        array_push($default_value, $value1);
                        array_unique($default_value);
                    } else {
                        $default_value = array($value1);
                    }
                }
            }
            $value1 = trim(strtolower($value1));

            $value2 = trim(strtolower($value2));

            if (isset($field['type']) && $field['type'] == 'checkbox') {
                $chk = 0;
                if ($default_value && is_array($default_value)) {
                    foreach ($default_value as $chk_value) {
                        $value1 = trim(strtolower($chk_value));
                        if ($arfieldhelper->ar_match_rule($value1, $value2, $operator))
                            $chk++;
                    }
                }
                
                if ($chk > 0){
                    return true;
                } else {
                    return false;
                }
            } else {
                return $arfieldhelper->ar_match_rule($value1, $value2, $operator);
            }
        }
    }

    function ar_match_rule($value1, $value2, $operator) {
        $value1 = stripslashes_deep($value1);
        $value2 = stripslashes_deep($value2);
        switch ($operator) {

            case 'is':
                return $value1 == $value2;
                break;

            case 'is not':
                return $value1 != $value2;
                break;

            case 'greater than':
                $value1 = floatval($value1);
                $value2 = floatval($value2);

                return $value1 > $value2;
                break;

            case 'less than':
                $value1 = floatval($value1);
                $value2 = floatval($value2);
                return $value1 < $value2;
                break;
            case 'contains':
                if ($value1 != '' && empty($value2))
                    return false;
                else if (empty($value1) && $value2 != '')
                    return false;
                else if (empty($value1) && empty($value2))
                    return true;
                else if ($value1 != '' && $value2 != '')
                    return ( strpos($value1, $value2) !== FALSE ) ? true : false;
                break;

            case 'not contains':
                if ($value1 != '' && empty($value2))
                    return true;
                else if (empty($value1) && $value2 != '')
                    return true;
                else if (empty($value1) && empty($value2))
                    return false;
                else if ($value1 != '' && $value2 != '')
                    return ( strpos($value1, $value2) !== FALSE ) ? false : true;
                break;
        }

        return false;
    }

    function get_shortcode_modal($form_id, $target_id = 'content', $type = 'all', $style = '', $is_total_field = false,$field_list = array()) {

        global $arffield, $MdlDb, $armainhelper,$arfieldhelper;
        
        $linked_forms = array();
        
        if (!empty($field_list)) {
            foreach ($field_list as $field) {
                $field->field_options = maybe_unserialize($field->field_options);
                if( $field->type == 'imagecontrol' ){
                    continue;
                }
                if ($type == 'email' && $target_id != 'options_admin_reply_to_notification' && $target_id != 'ar_admin_from_email' && $target_id != 'ar_user_from_email' && $target_id != 'admin_email_subject' && $target_id != 'options_ar_admin_from_name' && $target_id != 'options_admin_cc_email_notification' && $target_id != 'options_admin_bcc_email_notification') {
                    if ($field->type == 'email' || $field->type == 'text') {
                        ?>
                        <?php 
                        $field_name = $armainhelper->truncate($arfieldhelper->arf_execute_function($field->name,'strip_tags'), 60);
                        if($field->id !="" && $field_name=="" ){
                            ?>
                            <div class="modal_field_val" id="arfmodalfieldval_<?php echo $field->id; ?>" onclick="arfaddcodefornewfield('<?php echo $target_id; ?>', '<?php echo $field->id; ?>')"><?php echo "[Field Id:".$field->id."]" ?></div>
                            <?php
                        }else{ ?>
                            <div class="modal_field_val" id="arfmodalfieldval_<?php echo $field->id; ?>" onclick="arfaddcodefornewfield('<?php echo $target_id; ?>', '<?php echo $field->id; ?>')"><?php echo $field_name; ?></div>
                            <?php
                        } ?>


                        <?php
                    }
                } else if ($type == 'email' && ( $target_id == 'options_admin_reply_to_notification' || $target_id == 'ar_admin_from_email' || $target_id == 'ar_user_from_email' || $target_id == 'options_ar_admin_from_name' || $target_id == 'options_admin_cc_email_notification' || $target_id == 'options_admin_bcc_email_notification')) {
                   
                    if ($field->type == 'email' || $field->type == 'text' || $field->type == 'radio' || $field->type == 'select' || $field->type == 'hidden') {
                        ?>
                         <?php 
                        $field_name = $armainhelper->truncate($arfieldhelper->arf_execute_function($field->name,'strip_tags'), 60);
                        if($field->id !="" && $field_name=="" ){
                            ?>
                            <div class="modal_field_val" id="arfmodalfieldval_<?php echo $field->id; ?>" onclick="arfaddcodefornewfield('<?php echo $target_id; ?>', '<?php echo $field->id; ?>')"><?php echo "[Field Id:".$field->id."]" ?></div>
                            <?php
                        }else{ ?>
                            <div class="modal_field_val" id="arfmodalfieldval_<?php echo $field->id; ?>" onclick="arfaddcodefornewfield('<?php echo $target_id; ?>', '<?php echo $field->id; ?>')"><?php echo $field_name; ?></div>
                            <?php
                        } ?>
                        <?php
                    }
                } else {
                    if ($is_total_field) {
                        if ($field->type != 'html') {
                            ?>
                           <?php 
                            $field_name = $armainhelper->truncate($arfieldhelper->arf_execute_function($field->name,'strip_tags'), 60);
                            if($field->id !="" && $field_name=="" ){
                                ?>
                                <div class="modal_field_val" id="arfmodalfieldval_<?php echo $field->id; ?>" onclick="arfaddcodefornewfield('<?php echo $target_id; ?>', '<?php echo $field->id; ?>')"><?php echo "[Field Id:".$field->id."]" ?></div>
                                <?php
                            }else{ ?>
                                <div class="modal_field_val" id="arfmodalfieldval_<?php echo $field->id; ?>" onclick="arfaddcodefornewfield('<?php echo $target_id; ?>', '<?php echo $field->id; ?>')"><?php echo $field_name; ?></div>
                                <?php
                            } ?>

                            <?php
                        }
                    } else {

                        if (( $target_id == "ar_email_subject" || $target_id == 'admin_email_subject' ) && $field->type != 'html' || $field->type != 'file' || $field->type != 'like') {
                            ?>

                         <?php 
                            $field_name = $armainhelper->truncate($arfieldhelper->arf_execute_function($field->name,'strip_tags'), 60);
                            if($field->id !="" && $field_name=="" ){
                                ?>
                                <div class="modal_field_val" id="arfmodalfieldval_<?php echo $field->id; ?>" onclick="arfaddcodefornewfield('<?php echo $target_id; ?>', '<?php echo $field->id; ?>')"><?php echo "[Field Id:".$field->id."]" ?></div>
                                <?php
                            }else{ ?>
                                <div class="modal_field_val" id="arfmodalfieldval_<?php echo $field->id; ?>" onclick="arfaddcodefornewfield('<?php echo $target_id; ?>', '<?php echo $field->id; ?>')"><?php echo $field_name; ?></div>
                                <?php
                            } ?>

                            <?php
                        } else if ($target_id != "ar_email_subject") {
                            ?>
                            <?php 
                                $field_name = $armainhelper->truncate($arfieldhelper->arf_execute_function($field->name,'strip_tags'), 60);
                                if($field->id !="" && $field_name=="" ){
                                    ?>
                                    <div class="modal_field_val" id="arfmodalfieldval_<?php echo $field->id; ?>" onclick="arfaddcodefornewfield('<?php echo $target_id; ?>', '<?php echo $field->id; ?>')"><?php echo "[Field Id:".$field->id."]" ?></div>
                                    <?php
                                }else{ ?>
                                    <div class="modal_field_val" id="arfmodalfieldval_<?php echo $field->id; ?>" onclick="arfaddcodefornewfield('<?php echo $target_id; ?>', '<?php echo $field->id; ?>')"><?php echo $field_name; ?></div>
                                    <?php
                                } 
                            ?>
                            <?php
                        }
                    }
                }
            }
        }
    }

    function get_shortcode_total_modal($form_id, $target_id = 'content', $type = 'all', $style = '', $is_total_field = false) {
        global $arffield, $MdlDb, $armainhelper,$arfieldhelper;
        $field_list = array();
        if (is_numeric($form_id)) {

            $exclude = "'divider','captcha','break','html'";

            $field_list = $arffield->getAll("fi.type not in (" . $exclude . ") and fi.form_id=" . (int) $form_id, 'id');
        }
        $linked_forms = array();
        if (!empty($field_list)) {

            foreach ($field_list as $field) {

                if( $field->name == '' ){
                    $field->name = '[Field Id:'.$field->id.']';
                }

                if ($field->type == "checkbox") {
                    $choices = maybe_unserialize($field->options);

                    $field_opts = maybe_unserialize($field->field_options);

                    $is_sep_val = $field_opts['separate_value'];
                    ?>
                    <div class="modal_field_val" id="arfmodalfieldval_<?php echo $field->id; ?>" onclick="javascript:void(0);">
                        <strong>
                            <?php
                            echo $field_name = $armainhelper->truncate($field->name, 40);
                            ?>
                        </strong>
                    </div>
                    <?php
                    $inc = 0;
                    foreach ($choices as $choice) {
                        if ($is_sep_val == 0) {
                            if (is_array($choice)) {
                                $choice = $choice['label'];
                            }
                                ?>
                            <?php 
                                $field_name = $armainhelper->truncate($arfieldhelper->arf_execute_function($choice,'strip_tags'), 40);
                                if($field->id !="" && trim($field_name)==""){
                                    ?>
                                    <div class="modal_field_val" id="arfmodalfieldval_<?php echo $field->id . '_' . $inc; ?>" onclick="arfaddtotalfield(this, '<?php echo $field->id; ?>', '<?php echo $inc; ?>')"><?php echo "[Field Id:".$field->id."]" ?></div>
                                    <?php
                                }else{
                                    ?>
                                    <div class="modal_field_val" id="arfmodalfieldval_<?php echo $field->id . '_' . $inc; ?>" onclick="arfaddtotalfield(this, '<?php echo $field->id; ?>', '<?php echo $inc; ?>')"><?php echo $field_name; ?></div>
                                    <?php
                                }
                             ?>

                            <?php
                        } else {
                            if (is_array($choice)) {
                                $choice = $choice['label'];
                            }
                            ?>
                            <?php 
                                $field_name = $armainhelper->truncate($arfieldhelper->arf_execute_function($choice,'strip_tags'), 40);
                                if($field->id !="" && trim($field_name)==""){
                                    ?>
                                    <div class="modal_field_val" id="arfmodalfieldval_<?php echo $field->id . '_' . $inc; ?>" onclick="arfaddtotalfield(this, '<?php echo $field->id; ?>', '<?php echo $inc; ?>')"><?php echo "[Field Id:".$field->id."]" ?></div>
                                    <?php
                                }else{
                                    ?>
                                    <div class="modal_field_val" id="arfmodalfieldval_<?php echo $field->id . '_' . $inc; ?>" onclick="arfaddtotalfield(this, '<?php echo $field->id; ?>', '<?php echo $inc; ?>')"><?php echo $field_name; ?></div>
                                    <?php
                                }
                             ?>

                            <?php
                        }
                        $inc++;
                    }
                } elseif ($field->type == 'arfslider') {
                    $field->field_options = maybe_unserialize($field->field_options);
                    $slider_custom_class = '';
                    if (isset($field->field_options['arf_range_selector']) && $field->field_options['arf_range_selector'] == '1') {
                        $slider_custom_class = ' arf_slider_li arf_hidden_slider_li';
                    } else {
                        $slider_custom_class = ' arf_slider_li arf_show_slider_li';
                    }
                    if( trim($field->name) == '' ){
                        ?>
                        <div class="modal_field_val <?php echo $slider_custom_class; ?>" id="arfmodalfieldval_<?php echo $field->id; ?>" onclick="arfaddtotalfield(this, '<?php echo $field->id; ?>', '')">[Field Id:<?php echo $field->id; ?>]</div>
                        <?php
                    } else {
                        ?>
                        <div class="modal_field_val <?php echo $slider_custom_class; ?>" id="arfmodalfieldval_<?php echo $field->id; ?>" onclick="arfaddtotalfield(this, '<?php echo $field->id; ?>', '')"><?php echo $field_name = $armainhelper->truncate($arfieldhelper->arf_execute_function($field->name,'strip_tags'), 40); ?></div>
                        <?php
                    }
                    ?>
                    <div class="modal_field_val <?php echo $slider_custom_class; ?>" id="arfmodalfieldval_<?php echo $field->id; ?>" onclick="arfaddtotalfield(this, '<?php echo $field->id; ?>', '')"><?php echo $field_name = $armainhelper->truncate($arfieldhelper->arf_execute_function($field->name,'strip_tags'), 40); ?></div>        
                    <?php
                } else {
                    $field->field_options = maybe_unserialize($field->field_options);
                    ?>

                    <?php 
                        $field_name = $armainhelper->truncate($arfieldhelper->arf_execute_function($choice,'strip_tags'), 40);
                        if($field->id !="" && trim($field_name)==""){
                            ?>
                            <div class="modal_field_val" id="arfmodalfieldval_<?php echo $field->id . '_' . $inc; ?>" onclick="arfaddtotalfield(this, '<?php echo $field->id; ?>', '<?php echo $inc; ?>')"><?php echo "[Field Id:".$field->id."]" ?></div>
                            <?php
                        }else{
                            ?>
                            <div class="modal_field_val" id="arfmodalfieldval_<?php echo $field->id . '_' . $inc; ?>" onclick="arfaddtotalfield(this, '<?php echo $field->id; ?>', '<?php echo $inc; ?>')"><?php echo $field_name; ?></div>
                            <?php
                        }
                     ?>

                    <?php
                }
            }
        }
    }

    function replace_description_shortcode($field) {

        global $arformcontroller;

        $code = 'description';
        $value = $field['description'];

        $html = '[if description]<div class="arf_field_description" [description_style]>[description]</div>[/if description]';

        if (!$value or $value == '')
            $html = preg_replace('/(\[if\s+' . $code . '\])(.*?)(\[\/if\s+' . $code . '\])/mis', '', $html);
        else {
            $html = str_replace('[if ' . $code . ']', '', $html);
            $html = str_replace('[/if ' . $code . ']', '', $html);
        }
        $html = str_replace('[' . $code . ']', $value, $html);

        $description_style = ( isset($field['field_width']) and $field['field_width'] != '' ) ? 'style="width:' . $field['field_width'] . 'px;"' : '';
        $html = str_replace('[description_style]', $description_style, $html);

        $html = $arformcontroller->arf_remove_br($html);
        return $html;
    }

    function arf_getfields_basic_options_section() {

        $args = array(
            'text' => array(
                'labelname' => 1,
                'fielddescription' => 2,
                'tooltipmsg' => 3,
                'fieldsize' => 4,
                'placeholdertext' => 5,
                'default_value' => 6,
                'arf_input_custom_validation' => 7,
                'arf_regular_expression' => 8,
                'arf_regular_expression_msg' => 9,
                'requiredmsg' => 10,
                'minlength_message' => 11,
                'arf_prefix' => 12,
                'customwidth' => 14,
                'arf_enable_readonly' =>15,
            ),
            'textarea' => array(
                'labelname' => 1,
                'fielddescription' => 2,
                'tooltipmsg' => 3,
                'fieldsize' => 4,
                'number_of_rows' => 5,
                'placeholdertext' => 6,
                'default_value' => 7,
                'requiredmsg' => 8,
                'minlength_message' => 9,
                'customwidth' => 10,
                'arf_enable_readonly' =>11,
            ),
            'checkbox' => array(
                'labelname' => 1,
                'requiredmsg' => 2,
                'tooltipmsg' => 3,
                'fielddescription' => 4,
                'max_opt_selected' => 5,
                'max_opt_selected_msg' => 6,
                'alignment' => 7,
            ),
            'radio' => array(
                'labelname' => 1,
                'requiredmsg' => 2,
                'tooltipmsg' => 3,
                'fielddescription' => 4,
                'alignment' => 5
            ),
            'select' => array(
                'labelname' => 1,
                'fielddescription' => 2,
                'tooltipmsg' => 3,
                'requiredmsg' => 4,
                'customwidth' => 5,
            ),
            'file' => array(
                'labelname' => 1,
                'fielddescription' => 2,
                'tooltipmsg' => 3,
                'allowedfiletypes' => 4,
                'requiredmsg' => 5,
                'invalidmessage' => 6,
                'attachfiletoemail' => 7,
                'maxfileuploadsize' => 8,
                'invalidfilesizemessage' => 9,
                'enable_multiple_file_upload' => 14,
                'isdragable' => 10,
                'dragable_label' => 11,
                'customwidth' => 12,
                'uploadbuttontext' =>16,
            ),
            'email' => array(
                'labelname' => 1,
                'fielddescription' => 2,
                'tooltipmsg' => 3,
                'emailfieldsize' => 4,
                'placeholdertext' => 5,
                'default_value' => 6,
                'requiredmsg' => 7,
                'invalidmessage' => 8,
                'confirm_email' => 9,
                'confirm_email_label' => 10,
                'invalid_confirm_email' => 11,
                'confirm_email_placeholder' => 12,
                'arf_prefix' => 13,
                'customwidth' => 14,
                'arf_enable_readonly' =>15,
            ),
            'number' => array(
                'labelname' => 1,
                'fielddescription' => 2,
                'tooltipmsg' => 3,
                'fieldsize' => 4,
                'placeholdertext' => 5,
                'default_value' => 6,
                'requiredmsg' => 7,
                'minlength_message' => 8,
                'numberrange' => 9,
                'invalidmessage' => 10,
                'arf_prefix' => 11,
                'customwidth' => 12,
                'arf_enable_readonly' =>13,
            ),
            'phone' => array(
                'labelname' => 1,
                'fielddescription' => 2,
                'tooltipmsg' => 3,
                'allowedphonetype'=> 4,
                'country_validation' => 5,
                'fieldsize' => 6,
                'placeholdertext' => 7,
                'default_value' => 8,
                'requiredmsg' => 9,
                'invalidmessage' => 10,
                'phone_validation' => 11,
                'arf_prefix' => 12,
                'customwidth' => 13,
                'arf_enable_readonly' =>14,
            ),
            'date' => array(
                'labelname' => 1,
                'fielddescription' => 2,
                'tooltipmsg' => 3,
                'calendarlocalization' => 4,
                'placeholdertext' => 5,
                'requiredmsg' => 6,
                'calendartimehideshow' => 7,
                'clocksetting' => 8,
                'offdays' => 9,
                'daterange' => 10,
                'set_default_selected_date' => 11,
                'arf_prefix' => 12,
                'customwidth' => 13,
                'arf_enable_readonly' =>14,
            ),
            'time' => array(
                'labelname' => 1,
                'fielddescription' => 2,
                'tooltipmsg' => 3,
                'clocksetting' => 4,
                'requiredmsg' => 2,
                'arf_prefix' => 6,
                'customwidth' => 7,
                'arf_enable_readonly' =>8,
            ),
            'url' => array(
                'labelname' => 1,
                'fielddescription' => 2,
                'tooltipmsg' => 3,
                'requiredmsg' => 4,
                'placeholdertext' => 5,
                'default_value' => 6,
                'invalidmessage' => 7,
                'arf_prefix' => 8,
                'customwidth' => 9,
                'arf_enable_readonly' =>10,
            ),
            'image' => array(
                'labelname' => 1,
                'fielddescription' => 2,
                'tooltipmsg' => 3,
                'requiredmsg' => 4,
                'placeholdertext' => 5,
                'default_value' => 6,
                'arf_prefix' => 7,
                'customwidth' => 8,
                'arf_enable_readonly' =>10,
            ),
            'hidden' => array(),
            'password' => array(
                'labelname' => 1,
                'fielddescription' => 2,
                'tooltipmsg' => 3,
                'fieldsize' => 4,
                'placeholdertext' => 5,
                'default_value' => 6,
                'requiredmsg' => 7,
                'minlength_message' => 8,
                'password_strength' => 9,
                'confirm_password' => 10,
                'confirm_password_label' => 11,
                'invalid_password' => 12,
                'password_placeholder' => 13,
                'arf_prefix' => 14,
                'customwidth' => 15
            ),
            'html' => array(
                'labelname' => 1,
                'htmlcontent' => 2
            ),
            'divider' => array(
                'labelname' => 1,
                'fielddescription' => 2,
                'ishidetitle' => 3
            ),
            'break' => array(
                'firstpagelabel' => 1,
                'secondpagelabel' => 2,
                'prevbtntext' => 3,
                'nextbtntext' => 4,
                'pagebreakstyle' => 5,
                'pagebreaktabsbar' => 6,
                'pagebreakstyle_position' => 7,
            ),
            'scale' => array(
                'labelname' => 1,
                'fielddescription' => 2,
                'tooltipmsg' => 3,
                'requiredmsg' => 4,
                'starrange' => 5,
                'starsize' => 6,
            ),
            'like' => array(
                'labelname' => 1,
                'fielddescription' => 2,
                'tooltipmsg' => 3,
                'requiredmsg' => 4,
                'likebtntitle' => 5,
                'dislikebtntitle' => 6,
            ),
            'arfslider' => array(
                'labelname' => 1,
                'fielddescription' => 2,
                'tooltipmsg' => 3,
                'handletype' => 4,
                'numberofsteps' => 5,
                'defaultvalue' => 6,
                'numberrange' => 7,
                'arf_range_selector' => 8,
                'arf_range_defaultvalue' => 9,
            ),
            'colorpicker' => array(
                'labelname' => 1,
                'fielddescription' => 2,
                'tooltipmsg' => 3,
                'requiredmsg' => 4,
                'colorpicker_type' => 5,
                'defaultcolor' => 6,
            ),
            'imagecontrol' => array(
                'image_url' => 1,
                'image_horizontal_center' => 2,
                'image_position' => 3,'image_left' => 4,
                'image_top' => 5,
                'image_height' => 6,
                'image_width' => 7,
                'position_for_mobile_x' => 8,
                'position_for_mobile_y' => 9,
                'width_for_mobile' => 10,
                'height_for_mobile' => 11
            ),
        );

        $fieldsbasicoptionsarr = apply_filters('arfavailablefieldsbasicoptions', $args);

        return $fieldsbasicoptionsarr;
    }

    function arf_replace_shortcodes($content = '', $entry = 0, $is_for_mail = false) {
        if (!$entry)
            return $content;

        $tagregexp = '';

        preg_match_all("/\[([^\]]*)\]/", $content, $matches);
        if ($matches and $matches[1]) {
            foreach ($matches[1] as $shortcode) {
                if ($shortcode) {
                    global $arffield;
                    $display = false;
                    $show = 'one';
                    $odd = '';

                    $field_ids = explode(':', $shortcode);
                    $field_id = end($field_ids);

                    $field = "";
                    if (count($field_ids) > 1) {
                        $field = $arffield->getOne($field_id);

                        if (!isset($field))
                            $field = false;

                        $sep = (isset($atts['sep'])) ? $atts['sep'] : ', ';
                    }

                    if ($field) {

                        global $arfieldhelper, $arrecordhelper;
                        $field->field_options = maybe_unserialize($field->field_options);

                        $replace_with = $arrecordhelper->get_post_or_entry_value($entry, $field, array(), $is_for_mail);

                        $replace_with = maybe_unserialize($replace_with);

                        $atts['entry_id'] = $entry->id;
                        $atts['entry_key'] = $entry->entry_key;
                        $atts['attachment_id'] = $entry->attachment_id;

                        $tag = isset($tag) ? $tag : '';
                        $replace_with = apply_filters('arffieldsreplaceshortcodes', $replace_with, $tag, $atts, $field);
                        if (isset($replace_with) and is_array($replace_with))
                            $replace_with = implode($sep, $replace_with);

                        if ($field and $field->type == 'file') {

                            $size = (isset($atts['size'])) ? $atts['size'] : 'thumbnail';

                            if ($size != 'id')
                                $replace_with = $arfieldhelper->get_media_from_id($replace_with, $size);
                        }


                        if ($field) {
                            if (isset($atts['show']) and $atts['show'] == 'field_label') {
                                $replace_with = stripslashes($field->name);
                            } else if (empty($replace_with) and $replace_with != '0') {
                                $replace_with = '';
                                if ($field->type == 'number')
                                    $replace_with = '0';
                            }else {
                                $replace_with = $arfieldhelper->get_display_value($replace_with, $field, $atts, $is_for_mail);
                            }
                        }

                        if (isset($atts['sanitize']))
                            $replace_with = sanitize_title_with_dashes($replace_with);

                        if (isset($atts['sanitize_url']))
                            $replace_with = urlencode(htmlentities($replace_with));

                        if (isset($atts['clickable']))
                            $replace_with = make_clickable($replace_with);
                        if (!isset($replace_with))
                            $replace_with = '';
                        $content = str_replace('[' . $shortcode . ']', $replace_with, $content);
                    }
                }
            }
        }

        return $content;
    }

    function changeoptionorder($field) {
        if (!$field)
            return;

        global $wpdb;

        if ($field['type'] == 'radio' || $field['type'] == 'checkbox' || $field['type'] == 'select' || $field['type'] == ARF_AUTOCOMPLETE_SLUG) {
            return $field['options'];
        }

        $option_order = maybe_unserialize($field['option_order']);

        if (is_array($option_order)) {
            $options = $field['options'];
            $arr2ordered = array();

            foreach ($option_order as $key) {
                $arr2ordered[$key] = $options[$key];
            }
            return $arr2ordered;
        } else
            return $field['options'];
    }

    function array_push_after($src, $in, $pos) {
        if (is_int($pos))
            $R = array_merge(array_slice($src, 0, $pos + 1), $in, array_slice($src, $pos + 1));
        else {
            foreach ($src as $k => $v) {
                $R[$k] = $v;
                if ($k == $pos)
                    $R = array_merge($R, $in);
            }
        }return $R;
    }

    function get_confirm_password_field($field) {
        if (!$field)
            return;

        global $MdlDb, $wpdb, $armainhelper, $arfieldhelper;

        $key = $armainhelper->get_unique_key('', $MdlDb->fields, 'field_key');
        $label = $field['confirm_password_label'];
        $invalid = $field['invalid_password'];

        $field['confirm_password_field'] = $arfieldhelper->get_actual_id($field['id']);
        $field['id'] = rand(0000000, 9999999);
        $field['field_key'] = $key;
        $field['name'] = $label;
        $field['invalid'] = $invalid;
        $field['type'] = 'confirm_password';
        $field['required'] = 0;
        $field['password_strenth'] = 0;

        unset($field['description']);
        unset($field['tooltip_text']);
        return $field;
    }

    function get_confirm_email_field($field) {
        if (!$field)
            return;

        global $MdlDb, $wpdb, $armainhelper, $arfieldhelper;

        $key = $armainhelper->get_unique_key('', $MdlDb->fields, 'field_key');
        $label = $field['confirm_email_label'];
        $invalid = $field['invalid_confirm_email'];

        $field['confirm_email_field'] = $arfieldhelper->get_actual_id($field['id']);
        $field['id'] = rand(0000000, 9999999);
        $field['field_key'] = $key;
        $field['name'] = $label;
        $field['invalid'] = $invalid;
        $field['type'] = 'confirm_email';
        $field['required'] = 0;

        unset($field['description']);
        unset($field['tooltip_text']);
        return $field;
    }

    function get_form_pagebreak_fields($form_id, $form_key, $values) {
        global $MdlDb, $wpdb, $arfieldhelper;
        //$page_num = $MdlDb->get_count($MdlDb->fields, array("form_id" => $form_id, "type" => 'break'));

        $page_num = $wpdb->get_var( $wpdb->prepare("SELECT count(*) as total_breaks FROM `".$MdlDb->fields."` WHERE form_id = %d AND type = %s ",$form_id,'break') );

        if ($page_num > 0 && $values['fields']) {
            $pagebreak_fields = "0:[";
            $page_number = 1;
            foreach ($values['fields'] as $field) {
                if ($field['type'] == 'break') {
                    $pagebreak_fields .= "], " . $page_number . ": [";
                    $page_number++;
                } else {
                    $field['id'] = $arfieldhelper->get_actual_id($field['id']);
                    $pagebreak_fields .= $field['id'] . ",";
                }
            }
            $pagebreak_fields .= "]";

            return '<div><script type="text/javascript" data-cfasync="false">if(window[\'jQuery\']){ if(!window[\'arf_page_fields\']) window[\'arf_page_fields\'] = new Array(); window[\'arf_page_fields\'][\'' . $form_id . '\'] = { ' . $pagebreak_fields . ' }; }</script></div>';
        }
    }

    function arf_replace_running_total_field($value, $matches, $field, $fields = array()) {
        
        if (!$matches[1])
            return $value;
        $regexp = $matches[1];

        global $arfieldhelper;

        $total = $arfieldhelper->arf_replace_runningtotal_shortcode($regexp, $field, $fields);
        $round_total_number = 0;
        if ($field['type'] == 'html' && (isset($field['field_options']['enable_total']) && $field['field_options']['enable_total'] == 1) && (isset($field['field_options']['round_total']) && $field['field_options']['round_total'] == 1)) {
            $round_total_number = 1;
        }
        $replaceWith = '<div id="arf_running_total_' . $field['id'] . '" class="arf_running_total" data-arfcalc="' . $total . '" data-round="'.$round_total_number.'" >&nbsp;</div>';

        $regex = '/<arftotal>(.*?)<\/arftotal>/is';

        $value = preg_replace($regex, $replaceWith, $value);

        return $value;
    }

    function arf_replace_runningtotal_shortcode($content = '', $field_ref, $fields) {
        global $armainhelper,$arformcontroller;

        $tagregexp = '';

        preg_match_all("/\[(if )?($tagregexp)(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?/s", $content, $matches, PREG_PATTERN_ORDER);

        if ($matches and $matches[3]) {
            foreach ($matches[3] as $shortcode) {
                if ($shortcode) {
                    global $arffield, $wpdb, $MdlDb;
                    $display = false;
                    $show = 'one';
                    $odd = '';

                    $field_ids = explode(':', $shortcode);

                    if (is_array($field_ids)) {
                        $field_id = end($field_ids);
                        $is_checkbox = explode(".", $field_id);
                        $is_checkbox[1] = isset($is_checkbox[1]) ? $is_checkbox[1] : '';
                        if (count($is_checkbox) > 0) {
                            $field_id = $is_checkbox[0];
                            $option_id = $is_checkbox[1];
                        } else {
                            $option_id = "";
                        }
                    }

                    $field_id = end($field_ids);
                    if( preg_match('/(\d+)\.(\d+)/',$field_id,$match )){
                        $field_id = $match[1];
                    }
                    
                    if( isset($GLOBALS['arf_field_running_total']) && isset($GLOBALS['arf_field_running_total'][$field_id])){
                        $field = $GLOBALS['arf_field_running_total'][$field_id];
                    } else {
                        $field = $arffield->getOne($field_id);
                        if( isset($_REQUEST['arfaction']) && $_REQUEST['arfaction'] == 'preview' && !isset($_REQUEST['arf_is_home']) ){
                            $field_arr = $arformcontroller->arfObjtoArray($fields);
                            $field_arr_key = $arformcontroller->arfSearchArray($field_id,'id',$field_arr);
                            $field = $fields[$field_arr_key];
                        }
                        $GLOBALS['arf_field_running_total'] = array();
                        $GLOBALS['arf_field_running_total'][$field_id] = $field;
                    }
                    if (!isset($field))
                        $field = false;

                    if ($field) {

                        $field = (array) $field;

                        $value1 = '';
                        if( is_array($field['field_options'])){
                            $field_options = $field['field_options'];
                        } else {
                            $field_options = json_decode($field['field_options'], true);
                            if (json_last_error() != JSON_ERROR_NONE) {
                                $field_options = maybe_unserialize($field['field_options']);
                            }
                        }
                        $field['default_value'] = isset($field['default_value']) ? $field['default_value'] : '';
                        $field_options['default_blank'] = isset($field_options['default_blank']) ? $field_options['default_blank'] : '';

                        if ((isset($field_options['clear_on_focus']) and $field_options['clear_on_focus'] and ! empty($field['default_value']))) {

                            if ($field_options['default_blank'] == 1) {
                                $value1 = trim($armainhelper->esc_textarea($field['default_value']));
                            }
                        } else {

                            if ($field_options['default_blank'] == 1) {
                                $value1 = trim($armainhelper->esc_textarea($field['default_value']));
                            }
                        }
                        if ($field['type'] == 'scale') {
                            $value1 = ( isset($field['default_value']) and $field['default_value'] != '' ) ? $field['default_value'] : '';
                        }

                        if ($field['type'] == 'radio' || $field['type'] == 'select') {
                            if( is_array($field['field_options']) ){
                                $fieldoptions = $field['field_options'];
                            } else {
                                $fieldoptions = json_decode($field['field_options'], true);
                                if (json_last_error() != JSON_ERROR_NONE) {
                                    $fieldoptions = maybe_unserialize($field['field_options']);
                                }
                            }
                            foreach ($fieldoptions as $opt_key => $opt) {
                                $field_val = $opt;
                                if (is_array($opt)) {
                                    $opt = isset($opt['label']) ? $opt['label'] : '';

                                    $field_val = ($field_options['separate_value']) ? (isset($field_val['value']) ? $field_val['value'] : '') : $opt;
                                }
                                if (trim($field['default_value']) == trim($field_val))
                                    $value1 = $field_val;
                            }
                        }

                        if ($field['type'] == 'checkbox') {
                            if( is_array($field['field_options']) ){
                                $fieldoptions = $field['field_options'];
                            } else {
                                $fieldoptions = json_decode($field['field_options'], true);
                                if (json_last_error() != JSON_ERROR_NONE) {
                                    $fieldoptions = maybe_unserialize($field['field_options']);
                                }
                            }
                            $default_value = maybe_unserialize($field['default_value']);

                            if (isset($option_id) && $option_id != "") {
                                $optionval = $fieldoptions['options'][$option_id];

                                if ($field_options['separate_value'] == 1) {
                                    if (is_array($optionval) and ! empty($optionval)) {
                                        $optionvalue = $optionval['value'];
                                        $optionlabel = $optionval['label'];
                                    } else {
                                        $optionvalue = $optionval;
                                    }
                                } else {
                                    if (isset($optionvalue) and is_array($optionvalue)) {
                                        $optionvalue = $optionval['label'];
                                    } else {
                                        if (is_array($optionval) && count($optionval) > 0 && array_key_exists('label', $optionval))
                                            $optionvalue = $optionval['label'];
                                        else
                                            $optionvalue = $optionval;
                                    }
                                }

                                if (is_array($default_value)) {
                                    if (isset($_REQUEST) && isset($_REQUEST['item_meta']) && array_key_exists($field['id'], $_REQUEST['item_meta']) && $field['type'] == 'checkbox') {
                                        array_push($default_value, $_REQUEST['item_meta'][$field['id']]);
                                    }
                                    foreach ($default_value as $as_val) {
                                        if (trim($as_val) === trim($optionvalue)) {
                                            $value1 = $optionvalue;
                                        }
                                    }
                                }
                            }
                        }
                        if ($field['type'] == 'hidden' || $field['type'] == 'like') {
                            $value1 = $field['default_value'];
                        }

                        if ($field['type'] == 'arfslider') {
                            $field['slider_value'] = isset($field_options['slider_value']) ? $field_options['slider_value'] : '';
                            $value1 = ($field['slider_value'] != '') ? $field['slider_value'] : ( is_numeric($field_options['minnum']) ? $field_options['minnum'] : 1 );
                        }

                        if (isset($_REQUEST) && isset($_REQUEST['item_meta']) && array_key_exists($field['id'], $_REQUEST['item_meta']) && $field['type'] != 'checkbox') {
                            $value1 = $_REQUEST['item_meta'][$field['id']];
                        }
                        $value1 = trim(strtolower($value1));

                        $replace_with = (float) $value1 ? (float) $value1 : 0;
                        if (!isset($replace_with))
                            $replace_with = '';

                        $content = str_replace('[' . $shortcode . ']', $replace_with, $content);
                    }
                }
            }
        }

        return $content;
    }

    function arf_getall_running_total_str($form_id, $form_key, $values,$preview = 0) {
        global $arfieldhelper;

        $returnstr = "";
        if ($values['fields']) {
            $running_total_array = array();

            foreach ($values['fields'] as $field) {
                
                if ($field['type'] == 'html' && $field['enable_total'] == 1) {
                    $regex = '/<arftotal>(.*?)<\/arftotal>/is';

                    preg_match($regex, $field['description'], $arftotalmatches);                                        
                    if ($arftotalmatches) {
                        $regexp = $arftotalmatches[1];

                        $running_total_array[$field['id']] = $arfieldhelper->arf_replace_runningtotal_shortcode_exp($regexp, $field,$preview,$values['fields']);
                    }
                }
            }

            if ($running_total_array) {
                $runningtotal_fields = "";

                foreach ($running_total_array as $field_id => $field_data) {
                    $field_id = $arfieldhelper->get_actual_id($field_id);

                    $runningtotal_fields_new = $field_id . ": { 'regexp': '" . $field_data['regexp'] . "', 'fields':[" . $field_data['dep_fields'] . "] }, ";
                    $runningtotal_fields .= trim(preg_replace('/\s+/', ' ', $runningtotal_fields_new));
                }
                

                
                $returnstr .= 'if(window[\'jQuery\']){ if(!window[\'arf_runningtotal_fields\']) window[\'arf_runningtotal_fields\'] = new Array(); window[\'arf_runningtotal_fields\'][\'' . $form_key . '\'] = { ' . $runningtotal_fields . ' }; }';
            }

        }

        return $returnstr;
    }

    function arf_replace_runningtotal_shortcode_exp($content = '', $field_ref,$preview = 0,$all_fields) {
        $tagregexp = '';

        
        preg_match_all("/\[(if )?($tagregexp)(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?/s", $content, $matches, PREG_PATTERN_ORDER);

        if ($matches and $matches[3]) {
            $regexp = "";
            $dep_array = "";
            foreach ($matches[3] as $shortcode) {
                if ($shortcode) {
                    global $arffield,$arformcontroller;

                    $field_ids = explode(':', $shortcode);

                    $field_id = end($field_ids);

                    if( preg_match('/(\d+)\.(\d+)/',$field_id,$match )){
                        $field_id = $match[0];
                    }



                    if(!$preview){
                        if( isset($GLOBALS['arf_field_running_total']) && isset($GLOBALS['arf_field_running_total'][$field_id])){
                            $field = $GLOBALS['arf_field_running_total'][$field_id];
                        } else {
                            $field = $arffield->getOne($field_id);
                            $GLOBALS['arf_field_running_total'] = array();
                            $GLOBALS['arf_field_running_total'][$field_id] = $field;
                        }
                    } else {
                        $field = $arformcontroller->arfgetfieldfromid($field_id,$all_fields,'object');
                    }
                    
                    if (!isset($field))
                        $field = false;

                    if ($field) {

                        $replace_with = $field->id ? $field_id : 0;

                        $dep_array .= "{'field_id': '" . $replace_with . "', 'field_type' : '" . $field->type . "'}, ";

                        $replace_with = "{" . $replace_with . "}";

                        $content = str_replace('[' . $shortcode . ']', $replace_with, $content);
                    }
                }
            }
        }
        $dep_array = isset($dep_array) ? $dep_array : '';
        return array('regexp' => $content, 'dep_fields' => $dep_array);
    }

    function arf_is_field_inregexp($content = '', $field_ref) {
        $tagregexp = '';

        preg_match_all("/\[(if )?($tagregexp)(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?/s", $content, $matches, PREG_PATTERN_ORDER);

        if ($matches and $matches[3]) {
            foreach ($matches[3] as $shortcode) {
                if ($shortcode) {
                    global $arffield;

                    $field_ids = explode(':', $shortcode);
                    $field_id = end($field_ids);
                    
                    if( preg_match('/(\d+)\.(\d+)/',$field_id,$match )){
                        $field_id = $match[1];
                    }
                    if( isset($GLOBALS['arf_field_in_regexp']) && isset($GLOBALS['arf_field_in_regexp'][$field_id])){
                        $field = $GLOBALS['arf_field_in_regexp'][$field_id];
                    } else {
                        $field = $arffield->getOne($field_id);
                        $GLOBALS['arf_field_in_regexp'] = array();
                        $GLOBALS['arf_field_in_regexp'][$field_id] = $field;
                    }
                    if (!isset($field))
                        $field = false;

                    if ($field) {
                        if ($field_ref == $field->id) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    function post_validation_filed_display($field = '', $allfield, $posted_item_fields, $arf_conditional_logic_rules){
        $style = "";
        $hidden_fields = array();
        $visible_fields = array();

        $arf_conditional_logic_rules = isset($arf_conditional_logic_rules) ? $arf_conditional_logic_rules : array();


        foreach( $arf_conditional_logic_rules  as $conditional_rule ){


            $logical_operator = $conditional_rule['logical_operator'];
            $conditions = $conditional_rule['condition'];
            $results = $conditional_rule['result'];
            $total_rules = count($conditions);

            $match = 0;
            if( $total_rules > 0 ){
                foreach($conditions as $condition){
                    $field_id = $condition['field_id'];

                    $operator = $condition['operator'];
                    $value = $condition['value'];

                    if( !array_key_exists($field_id, $posted_item_fields) ){
                        continue;
                    }

                    $posted_value = $posted_item_fields[$field_id];
                    $field_type = $condition['field_type'];
                    if( $this->post_validation_calculate_rule($field_id,$field_type,$operator,$value,$posted_value) ){
                        $match++;
                    }
                }
            }
            
            if( ($logical_operator == 'and' && $total_rules == $match) || ($logical_operator == 'or' && $match > 0) ){
                
                foreach($results as $result){
                    $action = $result['action'];
                    if( $action == 'hide' || $action == 'disabled'  || $action == 'disable' ){
                        array_push($hidden_fields,$result['field_id']);
                    } else {
                        array_push($visible_fields,$result['field_id']);
                    }
                }
            } else {
                foreach($results as $result){
                    $action = $result['action'];
                    if( $action == 'show' || $action == 'enabled' || $action == 'enable' ){
                        array_push($hidden_fields,$result['field_id']);
                    }
                }
            }
        }
        $hidden_fields = array_unique($hidden_fields);

        if( isset($visible_fields) && !empty($visible_fields) && count($visible_fields) > 0 ){
            foreach( $visible_fields as $vfields_val ){
                
                if( in_array($vfields_val, $hidden_fields) ){
                    $hidden_field_key = array_search($vfields_val, $hidden_fields);
                    if( isset($hidden_field_key) && $hidden_field_key !== '' ){
                        
                        if( isset($hidden_fields[$hidden_field_key]) ){
                            unset($hidden_fields[$hidden_field_key]);
                        }
                    }
                }
            }
        }
        
        $hidden_fields = array_values($hidden_fields);
        return $hidden_fields;
    }

    function post_validation_calculate_rule($field_id, $field_type, $operator, $value2, $value1) {

        global $armainhelper, $arfieldhelper;

        $value2 = trim(strtolower($value2));

        if ($field_type == 'checkbox') {
            $chk = 0;
            $default_value = $value1;
            if ($default_value && is_array($default_value)) {
                foreach ($default_value as $chk_value) {
                    $value1 = trim(strtolower($chk_value));
                    if ($arfieldhelper->ar_match_rule($value1, $value2, $operator))
                        $chk++;
                }
            }
            else{
                if ($arfieldhelper->ar_match_rule($value1, $value2, $operator)){
                    $chk++;
                }
            }

            if ($chk > 0)
                return true;
            else
                return false;
        } else {
            $value1 = trim(strtolower($value1));

            return $arfieldhelper->ar_match_rule($value1, $value2, $operator);
        }
    }

    function arf_tooltip_display($content,$is_materialize_style='') {
        if($is_materialize_style=='material')
        {
            return ' data-title="' . esc_attr($content) . '" ';
        }
        else
        {
            return '<div class="arf_tooltip_main" ><img alt="" src="' . ARFIMAGESURL . '/tooltips-icon.png" class="arfhelptip" title="' . esc_attr($content) . '" data-title="' . esc_attr($content) . '" style="margin-left:10px; margin-top:4px;"/></div>';
        }
    }

    function arf_add_material_style_block($field, $frm_css, $display, $arf_main_label_cls, $arf_column_classes) {
        if ($frm_css['arfinputstyle'] == 'material') {
            $arf_disply_required_field = true;
            $arf_disply_required_field = apply_filters('arf_disply_required_field_outside', $arf_disply_required_field, $field);
            $is_required_field = false;
            if ($display['required'] and $field['type'] != 'arfslider' && $field['type'] != 'imagecontrol' && $arf_disply_required_field) {
                $is_required_field = true;
            }
            
            if ($field['type'] == 'divider') { ?>
                <label class="arf_main_label <?php echo $arf_main_label_cls; ?>" id="field_<?php echo $field['id']; ?>">
                    <span class="arfeditorfieldopt_divider_label arf_edit_in_place arfeditorfieldopt_label">
                        <input type="text" class="arf_edit_in_place_input inplace_field" data-ajax="false" data-field-opt-change="true" data-field-opt-key='name' value="<?php echo htmlspecialchars($field['name']); ?>" data-field-id="<?php echo $field['id']; ?>" />
                    </span>
                    <span id="require_field_<?php echo $field['id']; ?>">
                        <a href="javascript:void(0)" onclick="javascript:arfmakerequiredfieldfunction(<?php echo $field['id']; ?>,<?php echo $field_required = ($field['required'] == '0') ? '0' : '1'; ?>,'1')" class="arfaction_icon arfhelptip arffieldrequiredicon alignleft arfcheckrequiredfield<?php echo $field_required ?>" id="req_field_<?php echo $field['id']; ?>" title="<?php echo addslashes(esc_html__('Click to mark as', 'ARForms')) . ( $field['required'] == '0' ? ' ' : ' not ') . addslashes(esc_html__('compulsory field.', 'ARForms')); ?>"></a>
                    </span>

                </label>
            <?php } else { ?>
                <label class="arf_main_label <?php echo $arf_main_label_cls; ?>" id="field_<?php echo $field['id']; ?>">
                    <span class="arfeditorfieldopt_label arf_edit_in_place">
                        <input type="text" class="arf_edit_in_place_input inplace_field" data-ajax="false" data-field-opt-change="true" data-field-opt-key='name' value="<?php echo htmlspecialchars($field['name']); ?>" data-field-id="<?php echo $field['id']; ?>" />
                    </span>
                    <span id="require_field_<?php echo $field['id']; ?>">
                        <a href="javascript:void(0)" onclick="javascript:arfmakerequiredfieldfunction(<?php echo $field['id']; ?>,<?php echo $field_required = ($field['required'] == '0') ? '0' : '1'; ?>,'1')" class="arfaction_icon arfhelptip arffieldrequiredicon alignleft arfcheckrequiredfield<?php echo $field_required ?>" id="req_field_<?php echo $field['id']; ?>" title="<?php echo addslashes(esc_html__('Click to mark as', 'ARForms')) . ( $field['required'] == '0' ? ' ' : ' not ') . addslashes(esc_html__('compulsory field.', 'ARForms')); ?>"></a>
                    </span>
                </label>

                <?php if ($field['type'] == 'hidden') { ?>
                    <input type="hidden" name="field_options[name_<?php echo $field['id']; ?>]" id="arfname_<?php echo $field['id']; ?>" value="<?php echo esc_attr($field['name']); ?>" />
                <?php } ?>
            <?php }
            $arf_input_style_label_position = array('checkbox','radio','scale','arf_smiley','arf_switch', 'html','arfslider','slider','hidden','colorpicker','imagecontrol','like','file','break','divider','captcha');
            $arf_input_style_label_position = apply_filters('arf_input_style_label_position_outside',$arf_input_style_label_position,$frm_css['arfinputstyle'],$field['type']);

            if( in_array($field['type'],$arf_input_style_label_position)){
            ?>
            <div class="fieldname-row" style="display : block;">
                <?php
                if (isset($arf_column_classes['three']) and $arf_column_classes['three'] == '(Third)')
                    unset($arf_column_classes['three']);
                if (isset($arf_column_classes['two']) and $arf_column_classes['two'] == '(Second)')
                    unset($arf_column_classes['two']);
                do_action('arfextrafieldactions', $field['id']);
                ?>
                <div class="fieldname">
                </div>
            </div>
            <?php
            }
        }
    }
    function arf_add_blank_msg_from_globalsetting($form_field_json,$arfinputstyle){

        global $arfsettings;

        if( !isset($form_field_json) || empty($form_field_json) ){
            $form_field_json = file_get_contents(VIEWS_PATH.'/arf_editor_data.json');
            $form_field_json = json_decode($form_field_json);
        }
        
        foreach ($form_field_json->field_data as $key => $value) {                           
            foreach ($value as $data_key => $data_value) {
                if($data_key == 'blank'){
                   $form_field_json->field_data->$key->$data_key = $arfsettings->blank_msg;
                }
            }
        }
        $default_data_varible = 'default_data_'.$arfinputstyle;
        $form_field_json->$default_data_varible->arfsubmitbuttontext = $arfsettings->submit_value;
        return $form_field_json;
    }

    function arf_execute_function($value,$callback){

        if( $callback != '' ){
            return trim($callback($value));
        }

        return $value;

    }

    

}
?>