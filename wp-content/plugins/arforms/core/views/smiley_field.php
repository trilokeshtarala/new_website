<?php
define('ARF_SMILEY_SLUG', 'arf_smiley');

global $arf_smiley_field_class_name, $arf_smiley_new_field_data, $arf_smiley_field_image_path, $arf_font_awesome_loaded;

$arf_smiley_field_image_path = array(ARF_SMILEY_SLUG => ARFIMAGESURL . '/fields_elements_icon/smiley-field-icon.png');
$arf_smiley_field_class_name = array(ARF_SMILEY_SLUG => 'red');
$arf_smiley_new_field_data = array(ARF_SMILEY_SLUG => addslashes(esc_html__('Smiley', 'ARForms')));
$arf_smiley_total_class = array();
$arf_smiley_field_class = new arf_smiley_field();

global $arf_smiley_loaded;
$arf_smiley_loaded = array();

class arf_smiley_field {

    function __construct() {

        add_action('arfafterbasicfieldlisting', array($this, 'arf_add_smiley_field'), 10, 2);

        add_filter('arf_all_field_css_class_for_editor', array($this, 'arf_get_smiley_field_class'), 10, 3);

        add_filter('arfavailablefieldsbasicoptions', array($this, 'add_availablefieldsbasicoptions'), 10, 3);

        add_action('arfdisplayaddedfields', array($this, 'add_smiley_field_to_editor'), 11);

        /* arf_dev_flag convert from action to filter */
        add_filter('form_fields', array($this, 'add_smiley_field_to_frontend'), 11, 11);

        add_filter('arf_before_createfield', array($this, 'arf_product_createfield'), 10, 2);    // Before Create new filed

        add_filter('arf_save_more_field_from_out_side', array($this, 'arf_save_smiley_field'), 10, 2);    // Before Create new filed

        add_filter('arf_set_field_width_in_outside', array($this, 'arf_smiley_set_smiley_field_width'), 10, 3);    // Before Create new filed

        add_filter('arf_add_more_field_options_outside', array($this, 'arf_smiley_add_product_options'), 10, 2);

        add_action('wp_ajax_arf_add_new_smiley_image', array($this, 'arf_add_new_smiley_image'));

        add_action('arf_afterdisplay_form', array($this, 'arf_smiley_add_css_and_javascript'));

        add_action('wp_ajax_upload_smiley_img', array($this, 'arf_upload_smiley_img'));

        add_action('arf_field_option_model_outside', array($this, 'arf_add_smiley_field_options'));

        add_filter('arf_new_field_array_filter_outside', array($this, 'arf_add_smiley_field_in_array'),10,4);

        add_filter('arf_new_field_array_materialize_filter_outside', array($this, 'arf_add_smiley_field_in_array_materialize'),10,4);

        add_action('arfafterinstall', array($this, 'arf_move_smiley_default_images_to_uploads'));

        add_filter('arf_installed_fields_outside',array($this,'arf_install_smiley_field'),10);

        add_filter('arf_add_material_input_cls',array($this,'arf_allow_material_input_cls_on_smiley'),10,3);

        add_filter('arf_onchange_only_click_event_outside',array($this,'arf_smiley_change_type_func'),10);

        add_filter('arf_positioned_field_options_icon',array($this,'arf_positioned_field_options_icon_for_smiley'),10,2);

        add_filter('arf_default_value_array_field_type', array($this,'arf_default_value_array_field_type_smiley'),10,2);
    }

    function arf_default_value_array_field_type_smiley($field_types){
        array_push($field_types,ARF_SMILEY_SLUG);
        return $field_types;
    }

    function arf_positioned_field_options_icon_for_smiley($positioned_icon,$field_icons){
        $positioned_icon[ARF_SMILEY_SLUG] = "{$field_icons['field_require_icon']}".str_replace('{arf_field_type}',ARF_SMILEY_SLUG,$field_icons['arf_field_duplicate_icon'])."{$field_icons['field_delete_icon']}".str_replace('{arf_field_type}',ARF_SMILEY_SLUG,$field_icons['field_option_icon'])."{$field_icons['arf_field_move_icon']}";
        return $positioned_icon;
    }

    function arf_smiley_change_type_func($field_types){
        
        array_push($field_types,ARF_SMILEY_SLUG);
        return $field_types;
    }

    function arf_allow_material_input_cls_on_smiley($input_cls,$field_type,$inputStyle){
        if( $inputStyle != 'material' ){
            return $input_cls;
        }
        if( $field_type == 'arf_smiley' ){
            return '';
        }
        return $input_cls;
    }

    function arf_add_smiley_field($id = '', $is_ref_form = '', $values = '') {

        global $arf_smiley_field_class_name, $arf_smiley_new_field_data, $arf_smiley_field_image_path, $arf_smiley_total_class;

        if (is_rtl()) {
            $floating_style = 'float:right;';
        } else {
            $floating_style = 'float:left;';
        }

        foreach ($arf_smiley_new_field_data as $field_key => $field_type) {
            ?>
            <li class="arf_form_element_item frmbutton frm_t<?php echo $field_key ?>" id="<?php echo $field_key; ?>" data-field-id="<?php echo $id; ?>" data-type="<?php echo $field_key; ?>">
                <div class="arf_form_element_item_inner_container">
                    <span class="arf_form_element_item_icon">
                        <svg viewBox="0 0 30 30"><g id="smiley"><path fill="#4E5462" d="M15.236,28.534c-7.7,0-14.091-6.3-14.091-14s6.392-14,14.091-14c7.702,0,14,6.3,14,14S22.938,28.534,15.236,28.534z M15.236,2.558C8.564,2.558,3.26,7.862,3.26,14.534c0,6.673,5.304,11.976,11.976,11.976c6.673,0,11.976-5.303,11.976-11.976C27.211,7.862,21.909,2.558,15.236,2.558z M15.423,22.509c-3.5,0-6.65-2.101-8.05-5.427l1.575-0.698c1.05,2.625,3.675,4.198,6.476,4.198c2.799,0,5.424-1.75,6.475-4.198l1.574,0.698C22.073,20.583,18.923,22.509,15.423,22.509z M19.643,13.035c-1.104,0-2-0.897-2-2.001s0.897-2.001,2-2.001c1.104,0,2.002,0.897,2.002,2.001S20.747,13.035,19.643,13.035z M10.672,13.035c-1.104,0-2.001-0.897-2.001-2.001s0.897-2.001,2.001-2.001s2,0.897,2,2.001S11.776,13.035,10.672,13.035z"/></g></svg>
                    </span>
                    <label class="arf_form_element_item_text"><?php echo $field_type; ?></label>
                </div>
            </li>
            <?php
        }
    }

    function arf_get_smiley_field_class($class) {
        global $arf_smiley_field_class_name, $arf_smiley_total_class;
        $as_class = array_merge($class, $arf_smiley_field_class_name);
        $arf_smiley_total_class = count($as_class);
        return $as_class;
    }

    function add_availablefieldsbasicoptions($basic_option) {

        $smiley_filed_option = array(
            ARF_SMILEY_SLUG => array(
                'labelname' => 1,
                'fielddescription' => 2,
                'tooltipmsg' => 3,
                'requiredmsg' => 4,
                'arf_smiley_images' => 5
            )
        );

        return array_merge($basic_option, $smiley_filed_option);
    }

    function arf_product_createfield($field_data) {

        if ($field_data['type'] == ARF_SMILEY_SLUG) {
            $field_data['name'] = addslashes(esc_html__('Smiley', 'ARForms'));
        }
        return $field_data;
    }

    function add_smiley_field_to_editor($field) {
        global $arfajaxurl, $wpdb;
        $field_name = "item_meta[" . $field['id'] . "]";
        $field['field_options'] = json_decode($field['field_options'],true);
        if( json_last_error() != JSON_ERROR_NONE ){
            $field['field_options'] = maybe_unserialize($field['field_options']);
        }
        if ($field['type'] == ARF_SMILEY_SLUG) {
            $wp_upload_dir = wp_upload_dir();
            $smiley_upload_url = $wp_upload_dir['baseurl'] . '/arforms';
        ?>
            <style> 
                .arf_smiley_container .arf_smiley_btn{display: inline-block; margin: 0 8px 0 0;float:left;}
                .arf_smiley_container .arf_smiley_btn img{float: left; height: 30px; line-height: 30px; width: 30px;}
                .arf_smiley_container .arf_smiley_btn .arf_smiley_icon{float: left; line-height:normal; text-align: center; width:auto;}
            </style>
            <div class="arf_smiley_container like_container">
            <?php
                if (!empty($field['arf_smiley_images_array'])) {

                    $as_arf_smiley_images = $as_arf_smiley_title = $as_arf_smiley_type = array();

                    foreach ($field['arf_smiley_images_array'] as $k => $v) {
                        $as_arf_smiley_images[] = $v;
                    }
                    foreach ($field['arf_smiley_title'] as $k => $v) {
                        $as_arf_smiley_title[] = $v;
                    }

                    foreach ($field['arf_smiley_type'] as $k => $v) {
                        $as_arf_smiley_type[] = $v;
                    }

                    
                    foreach ($as_arf_smiley_images as $key => $value) {
                        $titlekey = ($key == 0 ) ? $key : $key - 1;
                        ?>
                        <input type="radio" class="arf_hide_opacity arf_smiley_input" data-id="<?php echo $field['id'];?>" style="position: absolute;" name="<?php echo $field_name; ?>" id="field_<?php echo $field['id'] . '_' . $key; ?>" value="<?php echo esc_attr($as_arf_smiley_title[$key]); ?>" <?php checked($field['field_options']['default_value'], esc_attr($as_arf_smiley_title[$key])); ?> />
                        <label id="smiley_<?php echo $field['id'] . '_' . $key; ?>" class="arf_smiley_btn arfhelptip" for="field_<?php echo $field['id'] . '_' . $key; ?>" title="<?php echo esc_attr($as_arf_smiley_title[$key]); ?>" <?php echo ($as_arf_smiley_type[$key] != "image")?'style="padding-top:4px;"':'';?>>
                            <?php if ($as_arf_smiley_type[$key] == 'image') { ?>
                                <img class="arf_smiley_img" src="<?php echo $smiley_upload_url . '/' . $value; ?>" alt="<?php echo esc_attr($as_arf_smiley_title[$key]); ?>" />
                            <?php } else { ?>
                                <span class="arf_smiley_icon"><i class="arfa-2x <?php echo $value; ?>"></i></span>
                            <?php } ?>
                        </label>
                    <?php } ?>
                <?php } ?>
            </div>
            <?php
        }
    }

    function add_smiley_field_to_frontend($return_string, $form, $field_name, $arf_data_uniq_id, $field, $field_tootip, $field_description,$res_data,$inputStyle,$arf_main_label,$get_onchage_func_data) {
        if ($field['type'] != 'arf_smiley') {
            return $return_string;
        }
        
        global $style_settings, $arfsettings, $arfeditingentry, $arffield, $arfieldhelper, $wpdb, $MdlDb;
        $form_data = new stdClass();
        $form_data->id = $form->id;
        $form_data->form_key = $form->form_key;
        $form_data->options = maybe_serialize($form->options);

        if( $res_data == '' ){
            $res_data = $wpdb->get_results($wpdb->prepare("SELECT id, type, field_options,conditional_logic FROM " . $MdlDb->fields . " WHERE form_id = %d ORDER BY id", $form->id));
        }

        $entry_id = $arfeditingentry;
        $wp_upload_dir = wp_upload_dir();
        $upload_url = $wp_upload_dir['baseurl'] . '/arforms/';
        if ($field['type'] == ARF_SMILEY_SLUG) {
            if( $inputStyle == 'material' ){
                $return_string .= $arf_main_label;
            }

            $field_tooltip_class = "";
            $field_tootip_material = "";
            $field_tootip_standard =  "";
            if($field_tootip!='')
            {
                if($inputStyle=="material")
                {
                    $field_tootip_material = $field_tootip;
                    $field_tooltip_class = " arfhelptip ";
                }
                else {
                    $field_tootip_standard = $field_tootip;
                }
                
            }

            $return_string .= '<div class="controls'.$field_tooltip_class.'" '.$field_tootip_material.'>';
            $return_string .= '<div class="arf_smiley_container" id="arf_smiley_container_' . $arf_data_uniq_id . '_' . $field['id'] . '" >';
            $as_arf_smiley_images = $as_arf_smiley_title = array();
            if (!empty($field['arf_smiley_images_array'])) {
                foreach ($field['arf_smiley_images_array'] as $k => $v) {
                    $as_arf_smiley_images[] = $v;
                }
                foreach ($field['arf_smiley_title'] as $k => $v) {
                    $as_arf_smiley_title[] = $v;
                }

                foreach ($field['arf_smiley_type'] as $k => $v) {
                    $as_arf_smiley_type[] = $v;
                }

                if( isset($field['set_field_value']) && $field['set_field_value'] != '' ){
                    $field['default_value'] = $field['set_field_value'];
                }

                foreach ($as_arf_smiley_images as $key => $value) {

                    $smiley_images = $upload_url . $value;
                    $titlekey = ($key == 0 ) ? $key : $key - 1;

                    $return_string .='<input type="radio" class="arf_hide_opacity arf_smiley_input" style="position: absolute;" name="' . $field_name . '" id="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '_' . $key . '" value="' . esc_attr($as_arf_smiley_title[$key]) . '" ' . checked($field['default_value'], esc_attr($as_arf_smiley_title[$key]), false) . ' ';
                    if (isset($field['required']) and $field['required']) {
                        $return_string .=' data-validation-minchecked-minchecked="1" data-validation-minchecked-message="' . esc_attr($field['blank']) . '"';
                    }

                    //ADDED DIRECTLY BY FILTER
                    $return_string .= $get_onchage_func_data;

                    $return_string .='/>';
                    
                    $return_string .='<label id="smiley_' . $field['field_key'] . '-' . $key . '" class="arf_smiley_btn"  data-form-data-id="' . $arf_data_uniq_id . '" data-id="' . $field['id'] . '" for="field_' . $field['field_key'] . '_' . $arf_data_uniq_id . '_' . $key . '" data-title="' . esc_attr($as_arf_smiley_title[$key]) . '" '.(($as_arf_smiley_type[$key] != "image")?"style='vertical-align:top;margin-top: 5px;margin-bottom: 5px;'":"").'>';

                    

                    if ($as_arf_smiley_type[$key] == 'image') {
                        $return_string .='<img  class="arf_smiley_img" src="' . $smiley_images . '" alt="' . esc_attr($as_arf_smiley_title[$key]) . '" height="36px" width="36px"/>';
                    } else {
                        $return_string .='<span class="arf_smiley_icon"><i class="' . $value . ' arfa-lg "></i></span>';
                    }
                    $return_string .='</label>';
                }
            }

            $return_string .='</div>';
            $return_string .=$field_tootip_standard;
            $return_string .=$field_description;



            $return_string .='</div>';
        }
        return $return_string;
    }

    function arf_add_more_smiley_field($field, $option) {
        global $armainhelper, $arfieldhelper, $arformcontroller, $arformhelper;

        switch ($option) {

            case 'arf_smiley_images':
                ?>
                <table style="float:left; width: 100%; margin:15px 0 5px 0;" border="0" cellpadding="0" cellspacing="0" >
                    <tr class="fieldoptions_label_style"><td><?php echo addslashes(esc_html__('Smiley Images', 'ARForms')); ?>:</td></tr>
                    <tr class="fieldoptions_field_style">

                        <td style="float:left; width: 100%;">

                            <div id="arf_smiley_images_section" class="arf_smiley_images_main1">
                                <?php
                                $as_arf_smiley_images = $as_arf_smiley_title = array();
                                if (!empty($field['arf_smiley_images'])) {

                                    foreach ($field['arf_smiley_images'] as $k => $v) {
                                        $as_arf_smiley_images[] = $v;
                                    }
                                    foreach ($field['arf_smiley_title'] as $k => $v) {
                                        $as_arf_smiley_title[] = $v;
                                    }

                                    foreach ($field['arf_smiley_type'] as $k => $v) {
                                        $as_arf_smiley_type[] = $v;
                                    }



                                    $i = 0;
                                    ?>

                                    <ul id="arf_smiley_images_section_<?php echo $field['id']; ?>" class="arf_smiley_images_main">
                                        <?php
                                        foreach ($as_arf_smiley_images as $key => $smiley_value) {
                                            $titlekey = ($key == 0 ) ? $key : $key - 1;
                                            ?>
                                            <li class="arf_smiley_images_content" style="float:left; width: 100%; margin: 5px 0 5px 0;">
                                                <input type="hidden" name="arf_smiley_images_array[]" value="<?php echo $key; ?>" />

                                                <div style="float:left; width: 47%;">
                                                    <?php if ($as_arf_smiley_type[$key] == 'image') { ?>
                                                        <span id="arfsmiley_image_value_<?php echo $field['id'] . '_' . $key; ?>"><img alt="" src="<?php echo esc_attr($smiley_value); ?>" style="float: left; margin: 0 10px 0 0; height: 30px; width: 30px;" /></span>
                                                    <?php } else { ?>
                                                        <spna class="arfsmiley_icon_value" id="arfsmiley_icon_value_<?php echo $field['id'] . '_' . $key; ?>" ><i class="<?php echo $smiley_value; ?>"></i></spna>
                                                    <?php } ?>
                                                    <input id="arf_smiley_images_title_<?php echo $field['id'] . '_' . $key; ?>" type="text" style="width:264px;" onkeyup="arfsmiley_change_title('<?php echo $field['id'] . '_' . $key; ?>');" class="txtstandardnew" name="field_options[arf_smiley_title_<?php echo $field['id']; ?>][]" value="<?php echo esc_attr($as_arf_smiley_title[$key]); ?>" />
                                                </div>

                                                <input type="hidden" class="txtstandardnew" name="field_options[arf_smiley_images_<?php echo $field['id']; ?>][]" id="arfimage_url_<?php echo $field['id'] . '_' . $key; ?>" value="<?php echo esc_attr($smiley_value); ?>" style="width:250px;float:left;" />
                                                <input type="hidden" class="txtstandardnew" name="field_options[arf_smiley_type_<?php echo $field['id']; ?>][]" id="arfsmiley_type_<?php echo $field['id'] . '_' . $key; ?>" value="<?php echo esc_attr($as_arf_smiley_type[$key]); ?>" />
                                                <div style="float:left;width:19%;">

                                                    <div class="arfajaxfileupload arf_smiley_fileupload" style="position: relative; overflow: hidden; float:left; cursor: pointer;">
                                                        <div class="file-upload-img"></div>
                                                        <?php echo addslashes(esc_html__('Add Image', 'ARForms')); ?>
                                                        <input type="file" name="arf_smiley_img_<?php echo $field['id'] . '_' . $key; ?>" id="arf_smiley_add_image_<?php echo $field['id'] . '_' . $key; ?>" data-val="arf_smiley_add_smiley_image_<?php echo $field['id'] . '_' . $key; ?>" class="original" style="position: absolute; cursor: pointer; top: 0px; padding:0; margin:0; height:100%; width:100%; right:0; z-index: 100; opacity: 0; filter:alpha(opacity=0);" />
                                                    </div>
                                                </div>
                                                <div style="float:left;width:14%;">
                                                    <button field-id="<?php echo $field['id']; ?>" data-field="arf_smiley_icon" data-id="<?php echo $key; ?>" type="button" class="arf_smiley_add_btn arf_smiley_icon_btn"  href="#arf_fontawesome_modal" data-toggle="arfmodal"><?php echo addslashes(esc_html__('Add Icon', 'ARForms')); ?></button>
                                                </div>

                                                <span class="bulk_add_remove" style="margin-top:4px; float: left;">
                                                    <span class="bulk_add" onclick="arf_add_new_smiley_image('<?php echo $field['id']; ?>');" style="margin-top:0px;"><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.134,20.362c-5.521,0-9.996-4.476-9.996-9.996
                                                    c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.13,15.887,16.654,20.362,11.134,20.362z M11.133,2.314
                                                    c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052
                                                    C19.185,5.919,15.579,2.314,11.133,2.314z M12.146,14.341h-2v-3h-3v-2h3V6.372h2v2.969h3v2h-3V14.341z"/></g></svg></span>&nbsp;
                                                    <span class="bulk_remove" onclick="arf_delete_smiley_image(this);" style="margin-top:0px; display: <?php echo (count($as_arf_smiley_images) > 1) ? 'inline-block' : 'none'; ?>;"><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.12,20.389c-5.521,0-9.996-4.476-9.996-9.996
                                                    c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.116,15.913,16.64,20.389,11.12,20.389z M11.119,2.341
                                                    c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052C19.17,5.945,15.565,2.341,11.119,2.341
                                                    z M12.131,11.367h3v-2h-3h-2h-3v2h3H12.131z"/></g></svg></span>
                                                </span>
                                                <span><img src="<?php echo ARFIMAGESURL ?>/move-icon2.png" alt="Move" style="vertical-align:middle; float:left; cursor:move; margin:6px 0 0 5px;" /></span>
                                            </li>
                                            <?php $i++; ?>
                                        <?php } ?>

                                    </ul>
                                    <input type="hidden" name="arf_smiley_image_name" id="arf_smiley_image_name" value="" /> 
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                </table>

                <?php
                break;               

            default:
        }
    }

    function arf_upload_smiley_img() {
        $file = $_POST['image'];
        ?>
        <img alt="" src="<?php echo esc_attr($file); ?>" style="float: left; margin: 0 10px 0 0; height: 30px; width: 30px;" />
        <?php
        die();
    }

    function arf_save_smiley_field($field_array) {

        return array_merge($field_array, array('arf_smiley_images', 'arf_smiley_type', 'arf_smiley_title'));
    }

    function arf_smiley_set_smiley_field_width($mainwidth, $fieldname, $field) {
        if ($field['type'] == ARF_SMILEY_SLUG) {
            if ($fieldname == 'arf_smiley_images') {
                $mainwidth = '100%';
            }
        }
        return $mainwidth;
    }

    function arf_smiley_add_product_options($field_options, $type) {
        return array_merge($field_options, array(
            'arf_smiley_images' => array(ARFIMAGESURL . '/smiley/4.png', ARFIMAGESURL . '/smiley/2.png', ARFIMAGESURL . '/smiley/1.png', ARFIMAGESURL . '/smiley/5.png', ARFIMAGESURL . '/smiley/3.png'),
            'arf_smiley_title' => array('Terrible', 'Could be better', 'Just okay', 'I like it', 'Awesome!'),
            'arf_smiley_type' => array('image', 'image', 'image', 'image', 'image')
        ));
    }

    function arf_add_new_smiley_image() {

        $field_id = (isset($_POST['field_id']) && !empty($_POST['field_id'])) ? $_POST['field_id'] : '';
        $key = (isset($_POST['next_rule_id']) && !empty($_POST['next_rule_id'])) ? $_POST['next_rule_id'] : '1';

        if (empty($field_id)) {
            return false;
        }
        ?>
        <li class="arf_smiley_images_content" style="float:left; width: 100%; margin: 5px 0 5px 0;">
            <input type="hidden" name="arf_smiley_images_array[]" value="<?php echo $key; ?>" />

            <div style="float:left; width:47%;">
                <span class="arfsmiley_icon_value" id="arfsmiley_icon_value_<?php echo $field_id . '_' . $key; ?>" ><i class=""></i>&nbsp;</span>
                <input id="arf_smiley_images_<?php echo $field_id ?>_div" type="text" style="width:264px;" class="txtstandardnew" name="field_options[arf_smiley_title_<?php echo $field_id; ?>][]" value="" />
            </div>

            <input type="hidden" class="txtstandardnew" name="field_options[arf_smiley_images_<?php echo $field_id; ?>][]" id="arfimage_url_<?php echo $field_id . '_' . $key; ?>" value="" style="width:250px;float:left;" />
            <input type="hidden" class="txtstandardnew" name="field_options[arf_smiley_type_<?php echo $field_id; ?>][]" id="arfsmiley_type_<?php echo $field_id . '_' . $key; ?>" value="icon" />

            <div style="float:left;width:19%;">
                <div class="arfajaxfileupload arf_smiley_fileupload" style="position: relative; overflow: hidden; float:left; cursor: pointer;">
                    <div class="file-upload-img"></div>
                    <?php echo addslashes(esc_html__('Add Image', 'ARForms')); ?>
                    <input type="file" name="arf_smiley_img_<?php echo $field_id . '_' . $key; ?>" id="arf_smiley_add_image_<?php echo $field_id . '_' . $key; ?>" data-val="arf_smiley_add_smiley_image_<?php echo $field_id . '_' . $key; ?>" class="original" style="position: absolute; cursor: pointer; top: 0px; padding:0; margin:0; height:100%; width:100%; right:0; z-index: 100; opacity: 0; filter:alpha(opacity=0);" />
                </div>
            </div>
            <div style="float:left;width:14%;">
                <button field-id="<?php echo $field_id; ?>" data-field="arf_smiley_icon" data-id="<?php echo $key; ?>" type="button" class="arf_smiley_add_btn arf_smiley_icon_btn"  href="#arf_fontawesome_modal" data-toggle="arfmodal"><?php echo addslashes(esc_html__('Add Icon', 'ARForms')); ?></button>
            </div>

            <span class="bulk_add_remove" style="margin-top:4px; float: left;">
                <span class="bulk_add" onclick="arf_add_new_smiley_image('<?php echo $field_id; ?>');" style="margin-top:0px;"><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.134,20.362c-5.521,0-9.996-4.476-9.996-9.996
                c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.13,15.887,16.654,20.362,11.134,20.362z M11.133,2.314
                c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052
                C19.185,5.919,15.579,2.314,11.133,2.314z M12.146,14.341h-2v-3h-3v-2h3V6.372h2v2.969h3v2h-3V14.341z"/></g></svg></span>&nbsp;
                <span class="bulk_remove"  onclick="arf_delete_smiley_image(this);" style="margin-top:0px; display: inline-block;"><svg viewBox="0 -4 32 32"><g id="email"><path fill-rule="evenodd" clip-rule="evenodd" fill="#3f74e7" d="M11.12,20.389c-5.521,0-9.996-4.476-9.996-9.996
                c0-5.521,4.476-9.997,9.996-9.997s9.996,4.476,9.996,9.997C21.116,15.913,16.64,20.389,11.12,20.389z M11.119,2.341
                c-4.446,0-8.051,3.604-8.051,8.051c0,4.447,3.604,8.052,8.051,8.052s8.052-3.604,8.052-8.052C19.17,5.945,15.565,2.341,11.119,2.341
                z M12.131,11.367h3v-2h-3h-2h-3v2h3H12.131z"/></g></svg></span>
            </span>
            <span><img src="<?php echo ARFIMAGESURL ?>/move-icon2.png" alt="Move" style="vertical-align:middle; float:left; cursor:move; margin:6px 0 0 5px;" /></span>
        </li>
        <?php
        die();
    }

    function arf_smiley_add_css_and_javascript($form) {

        if (empty($form->id)) {
            return;
        }
        $form_css = maybe_unserialize($form->form_css);




        global $arf_smiley_loaded;
        if (isset($arf_smiley_loaded) && !empty($arf_smiley_loaded)) {
            ?>
            <style type="text/css">
                .arf_form .arf_smiley_container .popover { background-color: #000000 !important; color:#FFFFFF !important; width:auto; }
                .arf_form .arf_smiley_container .popover .popover-content { color:#FFFFFF !important; }
                .arf_form .arf_smiley_container .popover .popover-title { display:none; }
                .arf_form .arf_smiley_container .popover.top .arrow:after { border-top-color: #000000 !important; }

                .arf_form .arf_smiley_btn {cursor:pointer; display: inline-block; padding:0 3px;}
                .arf_form .arf_smiley_btn .arf_smiley_img{opacity: 0.6; box-shadow:none;}
                .arf_form .arf_smiley_btn .arf_smiley_icon{opacity: 0.6; box-shadow:none; text-align: center; }

                .ar_main_div_<?php echo $form->id; ?> .arf_smiley_btn .arf_smiley_icon{
                    float: left;
                    line-height:normal;
                    padding:0 2px;
                }



            </style>
            <?php
            global $arfsettings, $arfversion;
            if (!isset($arfsettings)) {
                $arfsettings_new = get_option('arf_options');
            } else {
                $arfsettings_new = $arfsettings;
            }
            if (isset($arfsettings_new->arfmainformloadjscss) && $arfsettings_new->arfmainformloadjscss == 1) {
                wp_register_script('arfbootstrap-js', ARFURL . '/bootstrap/js/bootstrap.min.js', array('jquery'), $arfversion);
                wp_print_scripts('arfbootstrap-js');
            }
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function () {
                    jQuery('.arf_smiley_btn').each(function () {
                        var title = jQuery(this).attr('data-title');
                        if (title !== undefined) {
                            jQuery(this).popover({
                                html: true,
                                trigger: 'hover',
                                placement: 'top',
                                content: title,
                                title: '',
                                animation: false
                            });
                        }
                    });
                });
                

            </script>
            <?php
        }
    }

    function arf_add_smiley_field_options() {
        ?>
        <div class="arf_field_option_content_cell arf_full_width_cell" id="arf_smiley_images">
            <label class="arf_field_option_content_cell_label"><?php echo addslashes(esc_html__('Smiley Images', 'ARForms')); ?></label>
            <div class="arf_field_option_content_cell_input">
                <ul id="arf_smiley_images_section_{arf_field_id}" class="arf_smiley_images_main">
                </ul>
            </div>
            <?php
                $wp_upload_dir = wp_upload_dir();
                if (is_ssl()) {
                    $upload_css_url = str_replace("http://", "https://", $wp_upload_dir['baseurl'] . '/arforms/');
                } else {
                    $upload_css_url = $wp_upload_dir['baseurl'] . '/arforms/';
                }
            ?>
            <script type="text/javascript">
                function change_smiley_image() {

                    var upload_css_url = '<?php echo $upload_css_url; ?>';
                    var arf_smiley_img = jQuery('#arf_smiley_image_name').val();

                    var arf_smiley_data = arf_smiley_img.split('_arf_smiley_');
                    var img = arf_smiley_data[1];
                    var image = upload_css_url + img;

                    jQuery('.arf_field_option_model[data-field_id="{arf_field_id}"]').find('#arfsmiley_type_' + arf_smiley_data[0]).val('image');
                    var arfsmiley_type = jQuery('.arf_field_option_model[data-field_id="{arf_field_id}"]').find('#arfsmiley_type_' + arf_smiley_data[0]).val();

                    var msg = "<img alt='' src='"+image+"' style='float: left; margin: 0 10px 0 0; height: 30px; width: 30px;' />";
                    
                    if( arfsmiley_type == 'image' ){
                        jQuery('.arf_field_option_model[data-field_id="{arf_field_id}"]').find('#arfsmiley_image_value_' + arf_smiley_data[0]).html(msg);
                    } else {
                        jQuery('.arf_field_option_model[data-field_id="{arf_field_id}"]').find('#arfsmiley_icon_value_' + arf_smiley_data[0]).html(msg);
                        jQuery('.arf_field_option_model[data-field_id="{arf_field_id}"]').find('#arfsmiley_icon_value_' + arf_smiley_data[0]).attr('id', 'arfsmiley_image_value_' + arf_smiley_data[0]);
                    }
                    jQuery('.arf_field_option_model[data-field_id="{arf_field_id}"]').find('#arfimage_url_' + arf_smiley_data[0]).val(img);
                }
            </script>
        </div>
        <?php
    }

    function arf_add_smiley_field_in_array($fields,$field_icons,$json_data,$positioned_field_icons) {
        global $arfieldhelper;

        $field_opt_arr = $arfieldhelper->arf_getfields_basic_options_section();

        $field_order_arf_smiley = isset($field_opt_arr['arf_smiley']) ? $field_opt_arr['arf_smiley'] : '';

        $wp_upload_dir = wp_upload_dir();

        $upload_dir = $wp_upload_dir['basedir'] . '/arforms';
        $upload_url = $wp_upload_dir['baseurl'] . '/arforms';

        $field_data_array = $json_data;
        $field_data_obj_arf_smiley = $field_data_array->field_data->arf_smiley;

        $arf_field_move_option_icon = "<div class='arf_field_option_icon'><a class='arf_field_option_input'><svg id='moveing' height='20' width='21'><g>".ARF_CUSTOM_MOVING_ICON."</g></svg></a></div>";

        $fields['arf_smiley'] = "<div  class='arf_inner_wrapper_sortable arfmainformfield edit_form_item arffieldbox ui-state-default 1  arf1columns single_column_wrapper' data-id='arf_editor_main_row_{arf_editor_index_row}'><div class='arf_multiiconbox'><div class='arf_field_option_multicolumn' id='arf_multicolumn_wrapper'><input type='hidden' name='multicolumn' />{$field_icons['multicolumn_one']} {$field_icons['multicolumn_two']} {$field_icons['multicolumn_three']} {$field_icons['multicolumn_four']} {$field_icons['multicolumn_five']} {$field_icons['multicolumn_six']}</div>{$field_icons['multicolumn_expand_icon']}</div><div class='sortable_inner_wrapper edit_field_type_arf_smiley' inner_class='arf_1col' id='arfmainfieldid_{arf_field_id}'><div id='arf_field_{arf_field_id}' class='arfformfield control-group arfmainformfield top_container  arfformfield  arf_field_{arf_field_id}'><div class='fieldname-row' style='display : block;'><div class='fieldname'><label class='arf_main_label' id='field_{arf_field_id}'><span class='arfeditorfieldopt_label arf_edit_in_place'><input type='text' class='arf_edit_in_place_input inplace_field' data-ajax='false' data-field-opt-change='true' data-field-opt-key='name' value='Smiley' data-field-id='{arf_field_id}' /></span><span id='require_field_{arf_field_id}'><a href='javascript:arfmakerequiredfieldfunction({arf_field_id},0,1)' class='arfaction_icon arfhelptip arffieldrequiredicon alignleft arfcheckrequiredfield0' id='req_field_{arf_field_id}' title='Click to mark as not compulsory field'></a></span></label></div></div><div class='arf_fieldiconbox'>".$positioned_field_icons[ARF_SMILEY_SLUG]."</div><div class='controls'><div class='arf_smiley_container' id='arf_smiley_container_{arf_field_id}'><input type='radio' class='arf_hide_opacity arf_smiley_input' data-id='{arf_field_id}' style='position: absolute;' name='item_meta[{arf_field_id}]' id='field_{arf_unique_key}_0' value='Terrible'><label id='smiley_{arf_unique_key}-0' class='arf_smiley_btn' data-id='{arf_field_id}' for='field_{arf_unique_key}_0' data-title='Terrible'><img class='arf_smiley_img' src='" . $upload_url . "/arf_smiley_image_4.png' alt='Terrible'></label><input type='radio' class='arf_hide_opacity arf_smiley_input' data-id='{arf_field_id}' style='position: absolute;' name='item_meta[{arf_field_id}]' id='field_{arf_unique_key}_1' value='Could be better'><label id='smiley_{arf_unique_key}-1' class='arf_smiley_btn' data-id='{arf_field_id}' for='field_{arf_unique_key}_1' data-title='Could be better'><img class='arf_smiley_img' src='" . $upload_url . "/arf_smiley_image_2.png' alt='Could be better'></label><input type='radio' class='arf_hide_opacity arf_smiley_input' data-id='{arf_field_id}' style='position: absolute;' name='item_meta[{arf_field_id}]' id='field_{arf_unique_key}_2' value='Just okay'><label id='smiley_{arf_unique_key}-2' class='arf_smiley_btn' data-id='{arf_field_id}' for='field_{arf_unique_key}_2' data-title='Just okay'><img class='arf_smiley_img' src='" . $upload_url . "/arf_smiley_image_1.png' alt='Just okay'></label><input type='radio' class='arf_hide_opacity arf_smiley_input' data-id='{arf_field_id}' style='position: absolute;' name='item_meta[{arf_field_id}]' id='field_{arf_unique_key}_3' value='I like it'><label id='smiley_{arf_unique_key}-3' class='arf_smiley_btn'  data-id='{arf_field_id}' for='field_{arf_unique_key}_3' data-title='I like it'><img class='arf_smiley_img' src='" . $upload_url . "/arf_smiley_image_5.png' alt='I like it'></label><input type='radio' class='arf_hide_opacity arf_smiley_input' data-id='{arf_field_id}' style='position: absolute;' name='item_meta[{arf_field_id}]' id='field_{arf_unique_key}_4' value='Awesome!'><label id='smiley_{arf_unique_key}-4' class='arf_smiley_btn' data-id='{arf_field_id}' for='field_{arf_unique_key}_4' data-title='Awesome!'><img class='arf_smiley_img' src='" . $upload_url . "/arf_smiley_image_3.png' alt='Awesome!'></label></div><div class='arf_field_description' id='field_description_{arf_field_id}'></div><div class='help-block'></div></div><input type='hidden' name='arf_field_data_{arf_field_id}' id='arf_field_data_{arf_field_id}' value='" . htmlspecialchars(json_encode($field_data_obj_arf_smiley)) . "' data-field_options='" . json_encode($field_order_arf_smiley) . "' /><div class='arf_field_option_model arf_field_option_model_cloned'><div class='arf_field_option_model_header'>".esc_html__('Field Options','ARForms')."</div><div class='arf_field_option_model_container'><div class='arf_field_option_content_row'></div></div><div class='arf_field_option_model_footer'><button type='button' class='arf_field_option_close_button' onClick='arf_close_field_option_popup({arf_field_id});'>".esc_html__('Cancel','ARForms')."</button><button type='button' class='arf_field_option_submit_button' data-field_id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button></div></div></div></div></div>";
        return $fields;
    }

    function arf_add_smiley_field_in_array_materialize($fields,$field_icons,$json_data,$positioned_field_icons) {
        global $arfieldhelper;

        $field_opt_arr = $arfieldhelper->arf_getfields_basic_options_section();

        $field_order_arf_smiley = isset($field_opt_arr['arf_smiley']) ? $field_opt_arr['arf_smiley'] : '';

        $wp_upload_dir = wp_upload_dir();

        $upload_dir = $wp_upload_dir['basedir'] . '/arforms';
        $upload_url = $wp_upload_dir['baseurl'] . '/arforms';

        $field_data_array = $json_data;
        $field_data_obj_arf_smiley = $field_data_array->field_data->arf_smiley;

        $fields['arf_smiley'] = "<div  class='arf_inner_wrapper_sortable arfmainformfield edit_form_item arffieldbox ui-state-default 1  arf1columns single_column_wrapper' data-id='arf_editor_main_row_{arf_editor_index_row}'><div class='arf_multiiconbox'><div class='arf_field_option_multicolumn' id='arf_multicolumn_wrapper'><input type='hidden' name='multicolumn' />{$field_icons['multicolumn_one']} {$field_icons['multicolumn_two']} {$field_icons['multicolumn_three']} {$field_icons['multicolumn_four']} {$field_icons['multicolumn_five']} {$field_icons['multicolumn_six']}</div>{$field_icons['multicolumn_expand_icon']}</div><div class='sortable_inner_wrapper edit_field_type_arf_smiley' inner_class='arf_1col' id='arfmainfieldid_{arf_field_id}'><div id='arf_field_{arf_field_id}' class='arfformfield control-group arfmainformfield top_container  arfformfield  arf_field_{arf_field_id}'><div class='fieldname-row' style='display : block;'><div class='fieldname'><label class='arf_main_label' id='field_{arf_field_id}'><span class='arfeditorfieldopt_label arf_edit_in_place'><input type='text' class='arf_edit_in_place_input inplace_field' data-ajax='false' data-field-opt-change='true' data-field-opt-key='name' value='Smiley' data-field-id='{arf_field_id}' /></span><span id='require_field_{arf_field_id}'><a href='javascript:arfmakerequiredfieldfunction({arf_field_id},0,1)' class='arfaction_icon arfhelptip arffieldrequiredicon alignleft arfcheckrequiredfield0' id='req_field_{arf_field_id}' title='Click to mark as not compulsory field'></a></span></label></div></div><div class='arf_fieldiconbox'>".$positioned_field_icons[ARF_SMILEY_SLUG]."</div><div class='controls'><div class='arf_smiley_container' id='arf_smiley_container_{arf_field_id}'><input type='radio' class='arf_hide_opacity arf_smiley_input' data-id='{arf_field_id}' style='position: absolute;' name='item_meta[{arf_field_id}]' id='field_{arf_unique_key}_0' value='Terrible'><label id='smiley_{arf_unique_key}-0' class='arf_smiley_btn'  data-id='{arf_field_id}' for='field_{arf_unique_key}_0' data-title='Terrible'><img class='arf_smiley_img' src='" . $upload_url . "/arf_smiley_image_4.png' alt='Terrible'></label><input type='radio' class='arf_hide_opacity arf_smiley_input' data-id='{arf_field_id}' style='position: absolute;' name='item_meta[{arf_field_id}]' id='field_{arf_unique_key}_1' value='Could be better'><label id='smiley_{arf_unique_key}-1' class='arf_smiley_btn'  data-id='{arf_field_id}' for='field_{arf_unique_key}_1' data-title='Could be better'><img class='arf_smiley_img' src='" . $upload_url . "/arf_smiley_image_2.png' alt='Could be better'></label><input type='radio' class='arf_hide_opacity arf_smiley_input' data-id='{arf_field_id}' style='position: absolute;' name='item_meta[{arf_field_id}]' id='field_{arf_unique_key}_2' value='Just okay'><label id='smiley_{arf_unique_key}-2' class='arf_smiley_btn'  data-id='{arf_field_id}' for='field_{arf_unique_key}_2' data-title='Just okay'><img class='arf_smiley_img' src='" . $upload_url . "/arf_smiley_image_1.png' alt='Just okay'></label><input type='radio' class='arf_hide_opacity arf_smiley_input' data-id='{arf_field_id}' style='position: absolute;' name='item_meta[{arf_field_id}]' id='field_{arf_unique_key}_3' value='I like it'><label id='smiley_{arf_unique_key}-3' class='arf_smiley_btn'  data-id='{arf_field_id}' for='field_{arf_unique_key}_3' data-title='I like it'><img class='arf_smiley_img' src='" . $upload_url . "/arf_smiley_image_5.png' alt='I like it'></label><input type='radio' class='arf_hide_opacity arf_smiley_input' data-id='{arf_field_id}' style='position: absolute;' name='item_meta[{arf_field_id}]' id='field_{arf_unique_key}_4' value='Awesome!'><label id='smiley_{arf_unique_key}-4' class='arf_smiley_btn'  data-id='{arf_field_id}' for='field_{arf_unique_key}_4' data-title='Awesome!'><img class='arf_smiley_img' src='" . $upload_url . "/arf_smiley_image_3.png' alt='Awesome!'></label></div><div class='arf_field_description' id='field_description_{arf_field_id}'></div><div class='help-block'></div></div><input type='hidden' name='arf_field_data_{arf_field_id}' id='arf_field_data_{arf_field_id}' value='" . htmlspecialchars(json_encode($field_data_obj_arf_smiley)) . "' data-field_options='" . json_encode($field_order_arf_smiley) . "' /><div class='arf_field_option_model arf_field_option_model_cloned'><div class='arf_field_option_model_header'>".esc_html__('Field Options','ARForms')."</div><div class='arf_field_option_model_container'><div class='arf_field_option_content_row'></div></div><div class='arf_field_option_model_footer'><button type='button' class='arf_field_option_close_button' onClick='arf_close_field_option_popup({arf_field_id});'>".esc_html__('Cancel','ARForms')."</button><button type='button' class='arf_field_option_submit_button' data-field_id='{arf_field_id}'>".esc_html__('OK','ARForms')."</button></div></div></div></div></div>";
        return $fields;
    }

    function arf_move_smiley_default_images_to_uploads() {
        $wp_upload_dir = wp_upload_dir();

        $destination_url = $wp_upload_dir['baseurl'] . '/arforms';
        $destination_path = $wp_upload_dir['basedir'] . '/arforms';

        $source_path = FORMPATH . '/images/smiley';

        $source_image1 = '1.png';
        $source_image2 = '2.png';
        $source_image3 = '3.png';
        $source_image4 = '4.png';
        $source_image5 = '5.png';

        $dest_image1 = 'arf_smiley_image_1.png';
        $dest_image2 = 'arf_smiley_image_2.png';
        $dest_image3 = 'arf_smiley_image_3.png';
        $dest_image4 = 'arf_smiley_image_4.png';
        $dest_image5 = 'arf_smiley_image_5.png';

        $smiley_images = array(
            $source_image1 => $dest_image1,
            $source_image2 => $dest_image2,
            $source_image3 => $dest_image3,
            $source_image4 => $dest_image4,
            $source_image5 => $dest_image5
        );

        foreach ($smiley_images as $source => $destination) {
            copy($source_path . '/' . $source, $destination_path . '/' . $destination);
        }
    }

    function arf_install_smiley_field($fields){
        array_push($fields,'arf_smiley');
        return $fields;
    }

}
?>