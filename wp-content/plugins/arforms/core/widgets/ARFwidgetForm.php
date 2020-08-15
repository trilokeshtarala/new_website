<?php

class ARFwidgetForm extends WP_Widget {

    function __construct() {
        $widget_ops = array('description' => addslashes(esc_html__("Display Form of ARForms", 'ARForms')));
        parent::__construct('arforms_widget_form', addslashes(esc_html__('ARForms Form', 'ARForms')), $widget_ops);

        add_action('load-widgets.php', array($this, 'arf_load_wiget_colorpicker'));
    }

    function arf_load_wiget_colorpicker() {
        wp_enqueue_style('iris');
        wp_enqueue_script('iris');
    }

    function form($instance) {


        $instance = wp_parse_args((array) $instance, array('title' => false, 'form' => false, 'widget_type' => 'normal', 'link_type' => 'link', 'link_position' => 'top', 'link_position_fly' => 'top', 'height' => 'auto', 'width' => '800', 'desc' => 'Click here to open Form', 'button_angle' => '0', 'scroll' => '10', 'delay' => '0', 'overlay' => '0.6', 'show_close_link' => 'yes', 'modal_bgcolor' => '#000000'));

        echo "<style type='text/css'>";
        echo ".wp-picker-container, .wp-picker-container:active{ position:relative; top:15px;left:10px; }";
        echo "</style>";
        ?>

        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php echo addslashes(esc_html__('Title', 'ARForms')); ?>:</label>
            <input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr(stripslashes($instance['title'])); ?>" /></p>

        <p><label for="<?php echo $this->get_field_id('form'); ?>"><?php echo addslashes(esc_html__('Form', 'ARForms')); ?>:</label>


            <?php
            global $arformhelper;
            $arformhelper->forms_dropdown_widget($this->get_field_name('form'), $instance['form'], false, $this->get_field_id('form'))
            ?>
        </p>

        <p><label for=""><?php echo addslashes(esc_html__('Form Type', 'ARForms')); ?>:</label>
            <br /><input type="radio" class="rdomodal" <?php checked($instance['widget_type'], 'normal'); ?> name="<?php echo $this->get_field_name('widget_type'); ?>" value="normal" id="<?php echo $this->get_field_id('widget_type'); ?>_type_normal" onchange="arf_change_type('<?php echo $this->get_field_name('widget_type'); ?>', '<?php echo $this->get_field_id('link_type'); ?>', '<?php echo $this->get_field_id('link_position'); ?>', '<?php echo $this->get_field_id('link_position_fly'); ?>', '<?php echo $this->get_field_id('arf_fly_modal_btn_bgcol'); ?>', '<?php echo $this->get_field_id('arf_fly_modal_btn_txtcol'); ?>', '<?php echo $this->get_field_id('button_angle'); ?>');" /><label for="<?php echo $this->get_field_id('widget_type'); ?>_type_normal"><span></span>&nbsp;<?php echo addslashes(esc_html__('Internal', 'ARForms')); ?></label>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type="radio" class="rdomodal" <?php checked($instance['widget_type'], 'popup'); ?> name="<?php echo $this->get_field_name('widget_type'); ?>" value="popup" id="<?php echo $this->get_field_id('widget_type'); ?>_type_popup" onchange="arf_change_type('<?php echo $this->get_field_name('widget_type'); ?>', '<?php echo $this->get_field_id('link_type'); ?>', '<?php echo $this->get_field_id('link_position'); ?>', '<?php echo $this->get_field_id('link_position_fly'); ?>', '<?php echo $this->get_field_id('arf_fly_modal_btn_bgcol'); ?>', '<?php echo $this->get_field_id('arf_fly_modal_btn_txtcol'); ?>', '<?php echo $this->get_field_id('button_angle'); ?>');" /><label for="<?php echo $this->get_field_id('widget_type'); ?>_type_popup"><span></span>&nbsp;<?php echo addslashes(esc_html__('Modal(popup) Window', 'ARForms')); ?></label>
        </p>

        <p id="<?php echo $this->get_field_id('link_type'); ?>_label" <?php if ($instance['widget_type'] != 'popup' || $instance['link_type'] == 'onload' || $instance['link_type'] == 'scroll' || $instance['link_type'] == 'timer' || $instance['link_type'] == 'on_exit' || $instance['link_type'] == 'on_idle') { ?> style="display:none;"<?php } ?> ><label for="<?php echo $this->get_field_id('desc'); ?>"><?php echo addslashes(esc_html__('Label', 'ARForms')); ?></label>
            <input type="text" style="width:220px;" name="<?php echo $this->get_field_name('desc'); ?>" id="<?php echo $this->get_field_id('desc'); ?>" value="<?php echo $instance['desc']; ?>" />
        </p>

        <p id="<?php echo $this->get_field_id('link_type') . '_div'; ?>" <?php if ($instance['widget_type'] != 'popup') { ?>style="display:none;"<?php } ?>><label for="<?php echo $this->get_field_id('link_type'); ?>"><?php echo addslashes(esc_html__('Modal Trigger Type', 'ARForms')); ?>:</label>
            <select onchange="arf_change_link_type('<?php echo $this->get_field_id('link_type'); ?>', '<?php echo $this->get_field_id('link_position'); ?>', '<?php echo $this->get_field_id('link_position_fly'); ?>', '<?php echo $this->get_field_id('button_angle'); ?>', '<?php echo $this->get_field_id('arf_fly_modal_btn_bgcol') ?>', '<?php echo $this->get_field_id('arf_fly_modal_btn_txtcol') ?>');" name="<?php echo $this->get_field_name('link_type'); ?>" id="<?php echo $this->get_field_id('link_type'); ?>" data-width="150px">
                <option value="link" <?php selected($instance['link_type'], 'link'); ?> style="display: none;"><?php echo addslashes(esc_html__('On Click', 'ARForms')); ?></option>
                <option value="button" <?php selected($instance['link_type'], 'button'); ?> style="display: none;"><?php echo addslashes(esc_html__('On Click', 'ARForms')); ?></option>
                <option value="sticky" <?php selected($instance['link_type'], 'sticky'); ?> style="display: none;"><?php echo addslashes(esc_html__('On Click', 'ARForms')); ?></option>
                <option value="fly" <?php selected($instance['link_type'], 'fly'); ?> style="display: none;"><?php echo addslashes(esc_html__('On Click', 'ARForms')); ?></option>

                <option value="onclick" <?php selected($instance['link_type'], 'onclick'); ?>><?php echo addslashes(esc_html__('On Click', 'ARForms')); ?></option>
                <option value="onload" <?php selected($instance['link_type'], 'onload'); ?>><?php echo addslashes(esc_html__('On Page Load', 'ARForms')); ?></option>
                <option value="scroll" <?php selected($instance['link_type'], 'scroll'); ?>><?php echo addslashes(esc_html__('On Page Scroll', 'ARForms')); ?></option>

                <option value="timer" <?php selected($instance['link_type'], 'timer'); ?>><?php echo addslashes(esc_html__('On Timer (Scheduled)', 'ARForms')); ?></option>
                <option value="on_exit" <?php selected($instance['link_type'], 'on_exit'); ?>><?php echo addslashes(esc_html__('On Exit (Exit Intent)', 'ARForms')); ?></option>
                <option value="on_idle" <?php selected($instance['link_type'], 'on_idle'); ?>><?php echo addslashes(esc_html__('On Idle', 'ARForms')); ?></option>
            </select>
        </p>

        <p id="<?php echo $this->get_field_id('link_type') . '_onclick_type'; ?>" <?php echo(!in_array($instance['link_type'],array('link','button','sticky','fly','onclick')) || $instance['widget_type']!='popup')?'style="display:none;"':'';?>>

            <label for=""><?php echo addslashes(esc_html__('Click Types', 'ARForms')); ?>:&nbsp;</label>

            <input type="radio" class="rdomodal" <?php echo(!in_array($instance['link_type'],array('button','sticky','fly')))?'checked':''; ?> name="<?php echo $this->get_field_name('onclick_type'); ?>" value="link" id="<?php echo $this->get_field_id('onclick_type'); ?>_link" onclick="change_onclick('<?php echo $this->get_field_id('onclick_type'); ?>','link','<?php echo $this->get_field_id('link_type'); ?>')" />

            <label for="<?php echo $this->get_field_id('onclick_type'); ?>_link"><span></span><?php echo addslashes(esc_html__('Link', 'ARForms')); ?></label>
            &nbsp;&nbsp;
            <input type="radio" class="rdomodal" <?php checked($instance['link_type'], 'button'); ?> name="<?php echo $this->get_field_name('onclick_type'); ?>" value="button" id="<?php echo $this->get_field_id('onclick_type'); ?>_button" onclick="change_onclick('<?php echo $this->get_field_id('onclick_type'); ?>','button','<?php echo $this->get_field_id('link_type'); ?>')"/>

            <label for="<?php echo $this->get_field_id('onclick_type'); ?>_button"><span></span><?php echo addslashes(esc_html__('Button', 'ARForms')); ?></label>
            &nbsp;&nbsp;
            <input type="radio" class="rdomodal" <?php checked($instance['link_type'], 'sticky'); ?> name="<?php echo $this->get_field_name('onclick_type'); ?>" value="sticky" id="<?php echo $this->get_field_id('onclick_type'); ?>_sticky" onclick="change_onclick('<?php echo $this->get_field_id('onclick_type'); ?>','sticky','<?php echo $this->get_field_id('link_type'); ?>')"/>

            <label for="<?php echo $this->get_field_id('onclick_type'); ?>_sticky"><span></span><?php echo addslashes(esc_html__('Sticky', 'ARForms')); ?></label>
            &nbsp;&nbsp;
            <input type="radio" class="rdomodal" <?php checked($instance['link_type'], 'fly'); ?> name="<?php echo $this->get_field_name('onclick_type'); ?>" value="fly" id="<?php echo $this->get_field_id('onclick_type'); ?>_fly" onclick="change_onclick('<?php echo $this->get_field_id('onclick_type'); ?>','fly','<?php echo $this->get_field_id('link_type'); ?>')"/>
            <label for="<?php echo $this->get_field_id('onclick_type'); ?>_fly"><span></span><?php echo addslashes(esc_html__('Fly', 'ARForms')); ?></label>
        </p>


        <p id="<?php echo $this->get_field_id('link_type') . '_scroll'; ?>"  <?php echo ($instance['widget_type'] == 'popup' and $instance['link_type'] == 'scroll') ? '' : 'style="display:none"'; ?> ><label for="<?php echo $this->get_field_id('scroll'); ?>"><?php echo addslashes(esc_html__('Open popup when user scroll % of page after page load', 'ARForms')); ?></label>
            <input type="text" style="width:77px;" name="<?php echo $this->get_field_name('scroll'); ?>" id="<?php echo $this->get_field_id('scroll'); ?>" value="<?php echo $instance['scroll']; ?>" /> %
            <span style="font-style:italic;">&nbsp;<?php echo addslashes(esc_html__('(eg. 100% - end of page)', 'ARForms')); ?></span>
        </p>

        <p id="<?php echo $this->get_field_id('link_type') . '_delay'; ?>"  <?php echo ($instance['widget_type'] == 'popup' and $instance['link_type'] == 'timer') ? '' : 'style="display:none"'; ?> ><label for="<?php echo $this->get_field_id('delay'); ?>"><?php echo addslashes(esc_html__('Open popup after page load', 'ARForms')); ?></label>
            <input type="text" style="width:77px;" name="<?php echo $this->get_field_name('delay'); ?>" id="<?php echo $this->get_field_id('delay'); ?>" value="<?php echo $instance['delay']; ?>" />
            <span><?php echo addslashes(esc_html__('(in seconds)', 'ARForms')); ?></span>
        </p>

        <p id="<?php echo $this->get_field_id('link_position') . '_div'; ?>" <?php
        if ($instance['widget_type'] == 'popup' and $instance['link_type'] == 'sticky') {
            
        } else {
            ?>style="display:none;"<?php } ?>><label for="<?php echo $this->get_field_id('link_position'); ?>"><?php echo addslashes(esc_html__('Link Position?', 'ARForms')); ?>:</label>
            <select name="<?php echo $this->get_field_name('link_position'); ?>"  id="<?php echo $this->get_field_id('link_position'); ?>" data-width="150px">
                <option value="top" <?php selected($instance['link_position'], 'top'); ?>><?php echo addslashes(esc_html__('Top', 'ARForms')); ?></option>
                <option value="bottom" <?php selected($instance['link_position'], 'bottom'); ?>><?php echo addslashes(esc_html__('Bottom', 'ARForms')); ?></option>
                <option value="left" <?php selected($instance['link_position'], 'left'); ?>><?php echo addslashes(esc_html__('Left', 'ARForms')); ?></option>
                <option value="right" <?php selected($instance['link_position'], 'right'); ?>><?php echo addslashes(esc_html__('Right', 'ARForms')); ?></option>
            </select>
        </p>

        <p id="<?php echo $this->get_field_id('link_position_fly') . '_div'; ?>" <?php
        if ($instance['widget_type'] == 'popup' and $instance['link_type'] == 'fly') {
            
        } else {
            ?>style="display:none;"<?php } ?>><label style="text-align:left;"><?php echo addslashes(esc_html__('Link Position?', 'ARForms')); ?>:</label>
            <select name="<?php echo $this->get_field_name('link_position_fly'); ?>" id="<?php echo $this->get_field_id('link_position'); ?>" data-width="150px" ><label for="<?php echo $this->get_field_id('link_position_fly'); ?>">
                    <option value="left" <?php selected($instance['link_position_fly'], 'left'); ?>><?php echo addslashes(esc_html__('Left', 'ARForms')); ?></option>
                    <option value="right" <?php selected($instance['link_position_fly'], 'right'); ?>><?php echo addslashes(esc_html__('Right', 'ARForms')); ?></option>
            </select>
        </p>

        <div id="<?php echo $this->get_field_id('link_type') . '_overlay'; ?>" <?php
        if ($instance['widget_type'] == 'popup' and $instance['link_type'] != 'fly' and $instance['link_type'] != 'sticky') {
            
        } else {
            ?>style="display:none;" <?php } ?>>
            <div style="width: 100%">
                <label for="<?php echo $this->get_field_id('overlay'); ?>"><?php echo addslashes(esc_html__('Background Overlay', 'ARForms')); ?>:</label>
                <select name="<?php echo $this->get_field_name('overlay'); ?>" class="txtmodal" id="<?php echo $this->get_field_id('overlay'); ?>" style="width:80px;" >
                    <option <?php echo ($instance['overlay'] == '0') ? "selected=selected" : ''; ?> value="0"><?php echo addslashes(esc_html__('0 (None)', 'ARForms')); ?></option>
                    <option <?php echo ($instance['overlay'] == '0.1') ? "selected=selected" : ''; ?> value="0.1" ><?php echo addslashes(esc_html__('10%', 'ARForms')); ?></option>
                    <option <?php echo ($instance['overlay'] == '0.2') ? "selected=selected" : ''; ?> value="0.2" ><?php echo addslashes(esc_html__('20%', 'ARForms')); ?></option>
                    <option <?php echo ($instance['overlay'] == '0.3') ? "selected=selected" : ''; ?> value="0.3" ><?php echo addslashes(esc_html__('30%', 'ARForms')); ?></option>
                    <option <?php echo ($instance['overlay'] == '0.4') ? "selected=selected" : ''; ?> value="0.4" ><?php echo addslashes(esc_html__('40%', 'ARForms')); ?></option>
                    <option <?php echo ($instance['overlay'] == '0.5') ? "selected=selected" : ''; ?> value="0.5" ><?php echo addslashes(esc_html__('50%', 'ARForms')); ?></option>
                    <option <?php echo ($instance['overlay'] == '0.6') ? "selected=selected" : ''; ?> value="0.6" ><?php echo addslashes(esc_html__('60%', 'ARForms')); ?></option>
                    <option <?php echo ($instance['overlay'] == '0.7') ? "selected=selected" : ''; ?> value="0.7" ><?php echo addslashes(esc_html__('70%', 'ARForms')); ?></option>
                    <option <?php echo ($instance['overlay'] == '0.8') ? "selected=selected" : ''; ?> value="0.8" ><?php echo addslashes(esc_html__('80%', 'ARForms')); ?></option>
                    <option <?php echo ($instance['overlay'] == '0.9') ? "selected=selected" : ''; ?> value="0.9" ><?php echo addslashes(esc_html__('90%', 'ARForms')); ?></option>
                    <option <?php echo ($instance['overlay'] == '1') ? "selected=selected" : ''; ?> value="1" ><?php echo addslashes(esc_html__('100%', 'ARForms')); ?></option>
                </select>
            </div>
            <div style="width: 100%; margin-top:15px;">
                <label for="<?php echo $this->get_field_id('overlay'); ?>"><?php echo addslashes(esc_html__('Background Color', 'ARForms')); ?>:</label>
                <span> <input size="7" type="text" name="<?php echo $this->get_field_name('modal_bgcolor'); ?>" class="arf_fly_modal_btn_style" value="<?php echo $instance['modal_bgcolor']; ?>"> </span>
            </div>
        </div>

        <p id="<?php echo $this->get_field_id('link_type') . '_close_link'; ?>" <?php
        if ($instance['widget_type'] == 'popup' and $instance['link_type'] != 'fly' and $instance['link_type'] != 'sticky') {
            
        } else {
            ?>style="display:none;"<?php } ?>>
            <label for=""><?php echo addslashes(esc_html__('Show Close Button', 'ARForms')); ?>:&nbsp;</label>
            <input type="radio" class="rdomodal" <?php checked($instance['show_close_link'], 'yes'); ?> name="<?php echo $this->get_field_name('show_close_link'); ?>" value="yes" id="<?php echo $this->get_field_id('show_close_link'); ?>_yes" />
            <label for="<?php echo $this->get_field_id('show_close_link'); ?>_yes"><span></span><?php echo addslashes(esc_html__('Yes', 'ARForms')); ?></label>
            &nbsp;&nbsp;
            <input type="radio" class="rdomodal" <?php checked($instance['show_close_link'], 'no'); ?> name="<?php echo $this->get_field_name('show_close_link'); ?>" value="no" id="<?php echo $this->get_field_id('show_close_link'); ?>_no"  />
            <label for="<?php echo $this->get_field_id('show_close_link'); ?>_no"><span></span><?php echo addslashes(esc_html__('No', 'ARForms')); ?></label>
        </p>

        <?php
        $arf_fly_sticky_btn_val = ( isset($instance['arf_fly_modal_btn_bgcol']) and ! empty($instance['arf_fly_modal_btn_bgcol']) ) ? $instance['arf_fly_modal_btn_bgcol'] : '#8ccf7a';
        ?>

        <p id="<?php echo $this->get_field_id('arf_fly_modal_btn_bgcol') . '_div'; ?>" <?php
        if ($instance['widget_type'] == 'popup' and ( $instance['link_type'] == 'fly' or $instance['link_type'] == 'sticky' or $instance['link_type'] == 'button')) {
            
        } else {
            ?> style="display:none;" <?php } ?>>
            <label style="text-align:left;"><?php echo addslashes(esc_html__('Button Background Color', 'ARForms')); ?>: </label>
            <input type="text" name="<?php echo $this->get_field_name('arf_fly_modal_btn_bgcol'); ?>" class="arf_fly_modal_btn_style" value="<?php echo $arf_fly_sticky_btn_val; ?>">
        </p>

        <?php
        $arf_fly_sticky_btn_txtval = (isset($instance['arf_fly_modal_btn_txtcol']) and ! empty($instance['arf_fly_modal_btn_txtcol']) ) ? $instance['arf_fly_modal_btn_txtcol'] : '#ffffff';
        ?>

        <p id="<?php echo $this->get_field_id('arf_fly_modal_btn_txtcol') . '_div'; ?>" <?php
        if ($instance['widget_type'] == 'popup' and ( $instance['link_type'] == 'fly' or $instance['link_type'] == 'sticky' or $instance['link_type'] == 'button')) {
            
        } else {
            ?> style="display:none;" <?php } ?>>
            <label style="text-align:left;"><?php echo addslashes(esc_html__('Text Color', 'ARForms')); ?>:</label>
            <input type="text" name="<?php echo $this->get_field_name('arf_fly_modal_btn_txtcol'); ?>" class="arf_fly_modal_btn_style" value="<?php echo $arf_fly_sticky_btn_txtval; ?>">
        </p>

        <p id="<?php echo $this->get_field_id('link_type') . '_height'; ?>" <?php  echo($instance['link_type'] == 'sticky')?'':'style="display:none;"';?>>
            <label style="text-align:left;"><?php echo addslashes(esc_html__('Height :', 'ARForms')); ?></label>&nbsp;&nbsp;<input type="text" onkeyup="if (jQuery(this).val() == 'auto') {
                                jQuery('span#arf_widget_height_px').hide();
                            } else {
                                jQuery('span#arf_widget_height_px').show();
                            }" class="txtmodal" name="<?php echo $this->get_field_name('height'); ?>" id="<?php echo $this->get_field_id('height'); ?>" value="<?php echo $instance['height']; ?>" style="width:70px;" />&nbsp;<span class="arf_px" id="arf_widget_height_px" style="display: none;" ><?php echo addslashes(esc_html__('px', 'ARForms')); ?></span>
        </p> 

        <p id="<?php echo $this->get_field_id('link_type') . '_width'; ?>" <?php if ($instance['widget_type'] != 'popup') { ?>style="display:none;"<?php } ?>>
            <label style="text-align:left;"><?php echo addslashes(esc_html__('Width :', 'ARForms')); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" class="txtmodal" name="<?php echo $this->get_field_name('width'); ?>" id="<?php echo $this->get_field_id('width'); ?>" value="<?php echo $instance['width']; ?>" style="width:70px;" />&nbsp;<?php echo addslashes(esc_html__('px', 'ARForms')); ?>
            <span style="display: inline-block; float:left; width:100%; margin-bottom:10px; font-size: 12px; font-style: italic;"><?php echo addslashes(esc_html__('Form width will be overwritten', 'ARForms')); ?></span>
        </p>

        <p id="<?php echo $this->get_field_id('button_angle') . '_div'; ?>" <?php
        if ($instance['widget_type'] == 'popup' and $instance['link_type'] == 'fly') {
            
        } else {
            ?>style="display:none;"<?php } ?>><label for="<?php echo $this->get_field_id('button_angle'); ?>"><?php echo addslashes(esc_html__('Button Angle', 'ARForms')); ?>:</label>

            <select name="<?php echo $this->get_field_name('button_angle'); ?>" class="txtmodal" id="<?php echo $this->get_field_id('button_angle'); ?>" style="width:70px;" >
                <option value="0" <?php
                if ($instance['button_angle'] == 0) {
                    echo "selected=selected";
                }
                ?> >0</option>
                <option value="90" <?php
                if ($instance['button_angle'] == 90) {
                    echo "selected=selected";
                }
                ?>>90</option>
                <option value="-90" <?php
                if ($instance['button_angle'] == -90) {
                    echo "selected=selected";
                }
                ?>>-90</option>
            </select>
        </p>


        <p id="<?php echo $this->get_field_id('link_type') . '_ideal_time'; ?>" <?php echo($instance['link_type'] != 'on_idle')?'style="display:none;"':''; ?>>

            <label style="text-align:left;"><?php echo addslashes(esc_html__('Show after user is inactive for :', 'ARForms')); ?></label>&nbsp;&nbsp;<input type="text" class="txtmodal" name="<?php echo $this->get_field_name('inact_time'); ?>" id="<?php echo $this->get_field_id('inact_time'); ?>" value="<?php echo isset($instance['inact_time']) ? $instance['inact_time'] : ''; ?>" style="width:35px;" />&nbsp;<span class="arf_px"><?php echo addslashes(esc_html__('minute', 'ARForms')); ?></span>
        </p>


        <div id="<?php echo $this->get_field_id('link_type') . '_modal_effect'; ?>" <?php
        echo($instance['widget_type'] == 'popup' and $instance['link_type'] != 'fly' and $instance['link_type'] != 'sticky')?'':'style="display:none;"';?>>
            <div style="width: 100%">
                <label for="<?php echo $this->get_field_id('modal_effect'); ?>"><?php echo addslashes(esc_html__('Animation Effect', 'ARForms')); ?>:</label>
                <select name="<?php echo $this->get_field_name('modal_effect'); ?>" class="txtmodal" id="<?php echo $this->get_field_id('modal_effect'); ?>">
                    <option  <?php isset($instance['modal_effect']) ? selected($instance['modal_effect'], 'no_animation') : ''; ?> value="no_animation"><?php echo addslashes(esc_html__('No Animation', 'ARForms')); ?></option>
                    <option  <?php isset($instance['modal_effect']) ? selected($instance['modal_effect'], 'fade_in') : ''; ?> value="fade_in"><?php echo addslashes(esc_html__('Fade In', 'ARForms')); ?></option>
                    <option  <?php isset($instance['modal_effect']) ? selected($instance['modal_effect'], 'slide_in_top') : ''; ?> value="slide_in_top"><?php echo addslashes(esc_html__('Slide in Top', 'ARForms')); ?></option>
                    <option  <?php isset($instance['modal_effect']) ? selected($instance['modal_effect'], 'slide_in_bottom') : ''; ?> value="slide_in_bottom"><?php echo addslashes(esc_html__('Slide In Bottom', 'ARForms')); ?></option>
                    <option  <?php isset($instance['modal_effect']) ? selected($instance['modal_effect'], 'slide_in_right') : ''; ?> value="slide_in_right"><?php echo addslashes(esc_html__('Slide In Right', 'ARForms')); ?></option>
                    <option  <?php isset($instance['modal_effect']) ? selected($instance['modal_effect'], 'slide_in_left') : ''; ?> value="slide_in_left"><?php echo addslashes(esc_html__('Slide In Left', 'ARForms')); ?></option>
                    <option  <?php isset($instance['modal_effect']) ? selected($instance['modal_effect'], 'zoom_in') : ''; ?> value="zoom_in"><?php echo addslashes(esc_html__('Zoom In', 'ARForms')); ?></option>
                </select>
            </div>
        </div>

        <p id="<?php echo $this->get_field_id('link_type') . '_full_screen_modal'; ?>" <?php echo(in_array($instance['link_type'],array('sticky','fly')) && $instance['widget_type'] != 'popup')?'style="display:none;"':''; echo $instance['link_type'].'|'.$instance['widget_type'] ?>>
            <label for=""><?php echo addslashes(esc_html__('Show Full Screen Popup', 'ARForms')); ?>:&nbsp;</label>

            <input type="radio" class="rdomodal" <?php isset($instance['show_full_screen']) ? checked($instance['show_full_screen'], 'yes') : ''; ?> name="<?php echo $this->get_field_name('show_full_screen'); ?>" value="yes" id="<?php echo $this->get_field_id('show_full_screen'); ?>_yes" />
            <label for="<?php echo $this->get_field_id('show_full_screen'); ?>_yes"><span></span><?php echo addslashes(esc_html__('Yes', 'ARForms')); ?></label>
            &nbsp;&nbsp;
            <input type="radio" class="rdomodal" <?php isset($instance['show_full_screen']) ? checked($instance['show_full_screen'], 'no') : ''; ?> name="<?php echo $this->get_field_name('show_full_screen'); ?>" value="no" id="<?php echo $this->get_field_id('show_full_screen'); ?>_no"  />
            <label for="<?php echo $this->get_field_id('show_full_screen'); ?>_no"><span></span><?php echo addslashes(esc_html__('No', 'ARForms')); ?></label>
        </p>


       

        <script type="text/javascript" data-cfasync="false">

            function change_onclick(field_id,typename,link_id){
                jQuery('#'+link_id).val(typename);
                jQuery('#'+link_id).trigger('change');
            }

            function arf_change_type(name, id, link_position, link_position_fly, btn_bg_col, btn_txt_col, angle_div) {
                var type_val = jQuery('input[name="' + name + '"]:checked').val();
                var link_type = jQuery('#' + id).val();
                if (type_val == 'popup') {
                    jQuery('#' + id + '_div').show();
                    jQuery('#' + id + '_label').show();
                    jQuery('#' + id + '_height').show();
                    jQuery('#' + id + '_width').show();
                    jQuery('select#' + id).trigger('change');

                    jQuery('#' + id + '_close_link').show();


                    if (link_type == 'scroll') {
                        jQuery('#' + id + '_scroll').show();
                    } else {
                        jQuery('#' + id + '_scroll').hide();
                    }

                    if (link_type == 'onload') {
                        jQuery('#' + id + '_delay').show();
                    } else {
                        jQuery('#' + id + '_delay').hide();
                    }

                    if (link_type == 'onload' || link_type == 'scroll') {
                        jQuery('#' + id + '_label').hide();
                    } else {
                        jQuery('#' + id + '_label').show();
                    }

                    if (link_type == 'sticky' || link_type == 'fly') {
                        jQuery('#' + id + '_overlay').hide();
                        jQuery('#' + id + '_close_link').hide();
                    } else {
                        jQuery('#' + id + '_overlay').show();
                        jQuery('#' + id + '_close_link').show();
                    }

                } else if (type_val == 'normal') {
                    jQuery('#' + id + '_div').hide();
                    jQuery('#' + id + '_label').hide();
                    jQuery('#' + id + '_height').hide();
                    jQuery('#' + id + '_width').hide();


                    jQuery('#' + id + '_scroll').hide();
                    jQuery('#' + id + '_delay').hide();
                    jQuery('#' + id + '_overlay').hide();
                    jQuery('#' + id + '_close_link').hide();

                    jQuery('#' + link_position + '_div').hide();
                    jQuery('#' + link_position_fly + '_div').hide();
                    jQuery('#' + btn_bg_col + '_div').hide();
                    jQuery('#' + btn_txt_col + '_div').hide();
                    jQuery('#' + angle_div + '_div').hide();
                }

            }

            function arf_change_link_type(id, link_position, link_position_fly, button_angle, btn_bg_col, btn_txt_col) {
                var link_type = jQuery('#' + id).val();
                if (link_type == 'sticky') {
                    jQuery('#' + link_position_fly + '_div').hide();
                    jQuery('#' + link_position + '_div').show();
                    jQuery('#' + button_angle + '_div').hide();
                    jQuery('#' + btn_bg_col + '_div').show();
                    jQuery('#' + btn_txt_col + '_div').show();
                    jQuery('#' + id + '_label').show();


                    jQuery('#' + id + '_delay').hide();
                    jQuery('#' + id + '_scroll').hide();
                    jQuery('#' + id + '_overlay').hide();
                    jQuery('#' + id + '_close_link').hide();
                    jQuery('#' + id + '_onclick_type').show();
                    jQuery('#' + id + '_ideal_time').hide();
                    jQuery('#' + id + '_full_screen_modal').hide();
                    jQuery('#' + id + '_modal_effect').hide();
                    jQuery('#' + id + '_height').show();

                } else if (link_type == 'fly') {
                    jQuery('#' + link_position + '_div').hide();
                    jQuery('#' + link_position_fly + '_div').show();
                    jQuery('#' + button_angle + '_div').show();
                    jQuery('#' + btn_bg_col + '_div').show();
                    jQuery('#' + btn_txt_col + '_div').show();
                    jQuery('#' + id + '_label').show();


                    jQuery('#' + id + '_delay').hide();
                    jQuery('#' + id + '_scroll').hide();
                    jQuery('#' + id + '_overlay').hide();
                    jQuery('#' + id + '_close_link').hide();
                    jQuery('#' + id + '_onclick_type').show();
                    jQuery('#' + id + '_ideal_time').hide();
                    jQuery('#' + id + '_full_screen_modal').hide();
                    jQuery('#' + id + '_modal_effect').hide();
                    jQuery('#' + id + '_height').hide();

                } else if (link_type == 'scroll') {
                    jQuery('#' + link_position + '_div').hide();
                    jQuery('#' + link_position_fly + '_div').hide();
                    jQuery('#' + button_angle + '_div').hide();
                    jQuery('#' + btn_bg_col + '_div').hide();
                    jQuery('#' + btn_txt_col + '_div').hide();
                    jQuery('#' + id + '_label').hide();
                    jQuery('#' + id + '_full_screen_modal').show();


                    jQuery('#' + id + '_delay').hide();
                    jQuery('#' + id + '_scroll').show();
                    jQuery('#' + id + '_overlay').show();
                    jQuery('#' + id + '_close_link').show();
                    jQuery('#' + id + '_onclick_type').hide();
                    jQuery('#' + id + '_ideal_time').hide();
                    jQuery('#' + id + '_modal_effect').show();
                    jQuery('#' + id + '_height').hide();

                } else if (link_type == 'onload') {

                    jQuery('#' + link_position + '_div').hide();
                    jQuery('#' + link_position_fly + '_div').hide();
                    jQuery('#' + button_angle + '_div').hide();
                    jQuery('#' + btn_bg_col + '_div').hide();
                    jQuery('#' + btn_txt_col + '_div').hide();
                    jQuery('#' + id + '_label').hide();


                    jQuery('#' + id + '_scroll').hide();
                    jQuery('#' + id + '_delay').hide();
                    jQuery('#' + id + '_overlay').show();
                    jQuery('#' + id + '_close_link').show();
                    jQuery('#' + id + '_onclick_type').hide();
                    jQuery('#' + id + '_ideal_time').hide();
                    jQuery('#' + id + '_full_screen_modal').show();
                    jQuery('#' + id + '_modal_effect').show();
                    jQuery('#' + id + '_height').hide();

                } else if (link_type == 'button') {
                    jQuery('#' + link_position + '_div').hide();
                    jQuery('#' + link_position_fly + '_div').hide();
                    jQuery('#' + button_angle + '_div').hide();
                    jQuery('#' + btn_bg_col + '_div').show();
                    jQuery('#' + btn_txt_col + '_div').show();
                    jQuery('#' + id + '_label').show();


                    jQuery('#' + id + '_scroll').hide();
                    jQuery('#' + id + '_delay').hide();
                    jQuery('#' + id + '_overlay').hide();
                    jQuery('#' + id + '_close_link').hide();
                    jQuery('#' + id + '_onclick_type').hide();
                    jQuery('#' + id + '_onclick_type').show();
                    jQuery('#' + id + '_ideal_time').hide();
                    jQuery('#' + id + '_full_screen_modal').show();
                    jQuery('#' + id + '_modal_effect').show();
                    jQuery('#' + id + '_height').hide();

                } else if (link_type == 'timer') {

                    jQuery('#' + link_position + '_div').hide();
                    jQuery('#' + link_position_fly + '_div').hide();
                    jQuery('#' + button_angle + '_div').hide();
                    jQuery('#' + btn_bg_col + '_div').hide();
                    jQuery('#' + btn_txt_col + '_div').hide();
                    jQuery('#' + id + '_label').hide();


                    jQuery('#' + id + '_scroll').hide();
                    jQuery('#' + id + '_delay').show();
                    jQuery('#' + id + '_overlay').show();
                    jQuery('#' + id + '_close_link').show();
                    jQuery('#' + id + '_onclick_type').hide();
                    jQuery('#' + id + '_ideal_time').hide();
                    jQuery('#' + id + '_full_screen_modal').show();
                    jQuery('#' + id + '_modal_effect').show();
                    jQuery('#' + id + '_height').hide();

                }else if (link_type == 'on_exit') {

                    jQuery('#' + link_position + '_div').hide();
                    jQuery('#' + link_position_fly + '_div').hide();
                    jQuery('#' + button_angle + '_div').hide();
                    jQuery('#' + btn_bg_col + '_div').hide();
                    jQuery('#' + btn_txt_col + '_div').hide();
                    jQuery('#' + id + '_label').hide();


                    jQuery('#' + id + '_scroll').hide();
                    jQuery('#' + id + '_delay').hide();
                    jQuery('#' + id + '_overlay').show();
                    jQuery('#' + id + '_close_link').show();
                    jQuery('#' + id + '_onclick_type').hide();
                    jQuery('#' + id + '_ideal_time').hide();
                    jQuery('#' + id + '_full_screen_modal').show();
                    jQuery('#' + id + '_modal_effect').show();
                    jQuery('#' + id + '_height').hide();

                }else if (link_type == 'on_idle') {

                    jQuery('#' + link_position + '_div').hide();
                    jQuery('#' + link_position_fly + '_div').hide();
                    jQuery('#' + button_angle + '_div').hide();
                    jQuery('#' + btn_bg_col + '_div').hide();
                    jQuery('#' + btn_txt_col + '_div').hide();
                    jQuery('#' + id + '_label').hide();


                    jQuery('#' + id + '_scroll').hide();
                    jQuery('#' + id + '_delay').hide();
                    jQuery('#' + id + '_overlay').show();
                    jQuery('#' + id + '_close_link').show();
                    jQuery('#' + id + '_onclick_type').hide();
                    jQuery('#' + id + '_ideal_time').show();
                    jQuery('#' + id + '_full_screen_modal').show();
                    jQuery('#' + id + '_modal_effect').show();
                    jQuery('#' + id + '_height').hide();

                } else if(link_type == 'onclick'){
                    jQuery('#' + id + '_onclick_type').show();
                    jQuery('#' + id + '_ideal_time').hide();

                }else {
                    jQuery('#' + link_position + '_div').hide();
                    jQuery('#' + link_position_fly + '_div').hide();
                    jQuery('#' + button_angle + '_div').hide();
                    jQuery('#' + btn_bg_col + '_div').hide();
                    jQuery('#' + btn_txt_col + '_div').hide();
                    jQuery('#' + id + '_label').hide();


                    jQuery('#' + id + '_scroll').hide();
                    jQuery('#' + id + '_delay').hide();
                    jQuery('#' + id + '_overlay').hide();
                    jQuery('#' + id + '_close_link').hide();
                    jQuery('#' + id + '_onclick_type').hide();
                    jQuery('#' + id + '_ideal_time').hide();
                     jQuery('#' + id + '_full_screen_modal').hide();
                    jQuery('#' + id + '_modal_effect').hide();
                    jQuery('#' + id + '_height').hide();
                }

                if (link_type == 'link' || link_type == 'onclick') {
                    jQuery('#' + id + '_close_link').show();
                    jQuery('#' + id + '_overlay').show();
                    jQuery('#' + id + '_label').show();
                    jQuery('#' + id + '_onclick_type').show();
                    jQuery('#' + id + '_ideal_time').hide();
                    jQuery('#' + id + '_modal_effect').show();
                    jQuery('#' + id + '_full_screen_modal').show();
                    jQuery('#' + id + '_height').hide();

                }
                if (link_type == 'button') {
                    jQuery('#' + id + '_close_link').show();
                    jQuery('#' + id + '_overlay').show();
                    jQuery('#' + id + '_label').show();
                    jQuery('#' + id + '_onclick_type').show();
                    jQuery('#' + id + '_ideal_time').hide();
                    jQuery('#' + id + '_height').hide();
                }

            }

            jQuery(document).ready(function () {
                jQuery('.arf_fly_modal_btn_style').iris();
                jQuery('.arf_back_color_style .elementor-control-tag-area').iris();
                jQuery(document).click(function (e) {
                    if (!jQuery(e.target).is(".arf_fly_modal_btn_style, .iris-picker, .iris-picker-inner")) {
                        jQuery('.arf_fly_modal_btn_style').iris('hide');
                    }
                });
                jQuery('.arf_fly_modal_btn_style').click(function (event) {
                    jQuery('.arf_fly_modal_btn_style').iris('hide');
                    jQuery(this).iris('show');
                });


            });

        </script>
        <?php
    }

    function update($new_instance, $old_instance) {
        return $new_instance;
    }

    function widget($args, $instance) {
        global $arfform, $arfversion, $arformcontroller, $arfforms_loaded;
        extract($args);
        ?>
        <style>
            .ar_main_div_<?php echo $instance['form']; ?> .arf_submit_div.left_container { text-align:center !important; clear:both !important; margin-left:auto !important; margin-right:auto !important; }
            .ar_main_div_<?php echo $instance['form']; ?> .arf_submit_div.right_container { text-align:center !important; clear:both !important; margin-left:auto !important; margin-right:auto !important; }
            .ar_main_div_<?php echo $instance['form']; ?> .arf_submit_div.top_container,
            .ar_main_div_<?php echo $instance['form']; ?> .arf_submit_div.none_container { text-align:center !important; clear:both !important; margin-left:auto !important; margin-right:auto !important; }

            .ar_main_div_<?php echo $instance['form']; ?> #brand-div { font-size: 10px; color: #444444; }
            .ar_main_div_<?php echo $instance['form']; ?> #brand-div.left_container { text-align:center !important; margin-left:auto !important; margin-right:auto !important; }
            .ar_main_div_<?php echo $instance['form']; ?> #brand-div.right_container { text-align:center !important; margin-left:auto !important; margin-right:auto !important; }
            .ar_main_div_<?php echo $instance['form']; ?> #brand-div.top_container,
            .ar_main_div_<?php echo $instance['form']; ?> #brand-div.none_container { text-align:center !important; clear:both !important; margin-left:auto !important; margin-right:auto !important; }

            .ar_main_div_<?php echo $instance['form']; ?> #hexagon.left_container { text-align:center !important; margin-left:auto !important; margin-right:auto !important; }
            .ar_main_div_<?php echo $instance['form']; ?> #hexagon.right_container { text-align:center !important; margin-left:auto !important; margin-right:auto !important; }
            .ar_main_div_<?php echo $instance['form']; ?> #hexagon.top_container, 
            .ar_main_div_<?php echo $instance['form']; ?> #hexagon.none_container { text-align:center !important; margin-left:auto !important; margin-right:auto !important; }

            .ar_main_div_<?php echo $instance['form']; ?> .arfsubmitbutton .arf_submit_btn { margin: 10px 0 0 0 !important; } 

        </style>
        <?php
        $form_name='';
        if(isset($instance['form'])){
            $form_name = $arfform->getName($instance['form']);   
        }
        global $wpdb,$MdlDb;
        $form_data='';
        if(isset($instance['form'])){
            $form_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM " .$MdlDb->forms." WHERE id = %d", $instance['form']));
        }
        if ($form_data) {
            $formoptions = maybe_unserialize($form_data->options);
            if (isset($formoptions['display_title_form']) and $formoptions['display_title_form'] == '1') {
                $is_title = true;
                $is_description = true;
            } else {
                $is_title = false;
                $is_description = false;
            }
        }
        $arfforms_loaded[] = $form_data;

        $title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title']);
        echo $before_widget;
        echo '<div class="arf_widget_form">';
        if ($title)
            echo $before_title . stripslashes($title) . $after_title;
        $wp_upload_dir = wp_upload_dir();
        if (is_ssl()) {
            $upload_main_url = str_replace("http://", "https://", $wp_upload_dir['baseurl'] . '/arforms/maincss');
        } else {
            $upload_main_url = $wp_upload_dir['baseurl'] . '/arforms/maincss';
        }
        $is_material = false;
        if( isset($form_data) && $form_data != '' && count($form_data) ){
            $form_css = maybe_unserialize($form_data->form_css);
            if( isset($form_css) && is_array($form_css) && !empty($form_css) ){
                $input_style = $form_css['arfinputstyle'];
                if( $input_style == 'material'){
                    $is_material = true;
                    wp_enqueue_style('arf_materialize_css', ARFURL . '/materialize/materialize.css', array(), $arfversion);
                    wp_enqueue_script('arf_materialize_js', ARFURL . '/materialize/materialize.js', array(), $arfversion);
                }
            }
        }

        global $armainhelper, $arrecordcontroller;
        if( $is_material ){
            if(isset($instance['form'])){
                $fid = $upload_main_url . '/maincss_materialize_'.$instance['form'].'.css';
            }
        } else {
            if(isset($instance['form'])){
                $fid = $upload_main_url . '/maincss_' . $instance['form'] . '.css';
            }
        }
        $arf_data_uniq_id = rand(1, 99999);
        if (empty($arf_data_uniq_id) || $arf_data_uniq_id == '') {
            if(isset($instance['form'])){
                $arf_data_uniq_id = $instance['form'];
            }
        }

        /* arf_dev_flag passed unique id to handle of css 
         * once there was ticket for conflict with yoast seo 
         * moreover also change hangle of arf_front.css (not done yet)
         */
        if(isset($instance['form'])){
            wp_register_style('arfformscss' . $instance['form'] . $arf_data_uniq_id, $fid, array(), $arfversion);
        }
        $func_val='';
        if(isset($instance['form'])){
            $func_val = apply_filters('arf_hide_forms', $arformcontroller->arf_class_to_hide_form($instance['form']), $instance['form']);
        }


        if ($func_val == '') {
            if(isset($instance['form'])){
                $armainhelper->load_styles(array('arfformscss' . $instance['form'] . $arf_data_uniq_id, 'arfdisplaycss', 'arfbootstrap-css'));
            }
        } else {
            if(isset($instance['form'])){
                $armainhelper->load_styles(array('arfformscss' . $instance['form'] . $arf_data_uniq_id));
            }
        }


        if(isset($instance['widget_type'])){
            if ($instance['widget_type'] == 'popup') {
                if ($instance['link_type'] == 'sticky')
                    $arf_position = $instance['link_position'];
                else if ($instance['link_type'] == 'fly')
                    $arf_position = $instance['link_position_fly'];
                else
                    $arf_position = '';

                /* arf_dev_flag $open_inactivity => set this variable */
                $open_inactivity = '';
                $key = '';

                require_once VIEWS_PATH . '/arf_front_form.php';

                $fullscreen = (isset($instance['show_full_screen']) && $instance['show_full_screen'] != '')?$instance['show_full_screen']:''; 

                $contents = ars_get_form_builder_string($instance['form'], $key, false, true, '', $arf_data_uniq_id, $instance['desc'], $instance['link_type'], $instance['height'], $instance['width'], $arf_position, $instance['button_angle'], $instance['arf_fly_modal_btn_bgcol'], $instance['arf_fly_modal_btn_txtcol'], $open_inactivity, $instance['scroll'], $instance['delay'], $instance['overlay'], $instance['show_close_link'], $instance['modal_bgcolor'],$fullscreen,$instance['inact_time'],$instance['modal_effect']);

                $contents = apply_filters('arf_pre_display_arfomrms', $contents, $instance['form'], $key);

                /* arf_dev_flag widget echo css here */
                echo $arformcontroller->arf_get_form_style($instance['form'], $arf_data_uniq_id, $instance['link_type'], $arf_position, $instance['arf_fly_modal_btn_bgcol'], $instance['arf_fly_modal_btn_txtcol'], $instance['button_angle'], $instance['modal_bgcolor'], $instance['overlay'],$fullscreen,$instance['inact_time'],$instance['modal_effect']);
                echo $contents;
            } else {

                $key = '';
                require_once VIEWS_PATH . '/arf_front_form.php';

                $contents = ars_get_form_builder_string($instance['form'], $key, false, false, '', $arf_data_uniq_id);

                $contents = apply_filters('arf_pre_display_arfomrms', $contents, $instance['form'], $key);

                /* arf_dev_flag widget echo css here */
                echo $arformcontroller->arf_get_form_style($instance['form'], $arf_data_uniq_id);
                echo $contents;
            }
        }
        $arfsidebar_width = '';
        echo '</div>';
        echo $after_widget;
    }

}
?>