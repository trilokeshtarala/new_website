<?php
global $armainhelper, $arformhelper,$arfversion;
?>
<script  type="text/javascript" data-cfasync="false">
    function hasClass(el, className) {
        if (el == null) return false;
        if (el.classList) return el.classList.contains(className)
        else return !!el.className.match(new RegExp('(\\s|^)' + className + '(\\s|$)'))
    }
    function arfopenarfinsertform() {
        jQuery('.arf_modal_overlay,.arm_popup_container').removeClass('arfactive');
        jQuery('.arf_popup_container#arf_insert_popup_modal').parents('.arf_modal_overlay').addClass('arfactive');
        jQuery('.arf_popup_container#arf_insert_popup_modal').addClass('arfactive');
    }
    function changetopposition(myval) {
        var modalheight = jQuery(window).height();
        var top_height = Number(modalheight) / 2;
        if (myval == "fly") {
            jQuery('#arfinsertform').css('top', (top_height - 280) + 'px');
        } else {
            jQuery('#arfinsertform').css('top', (top_height - 280) + 'px');
        }
    }
    function arfinsertform() {
        var form_id = jQuery("#arfaddformid").val();
        if (form_id == "" || form_id == "0") {
            alert("<?php echo addslashes(esc_html__('Please select a form', 'ARForms')); ?>");
            return;
        }
        jQuery('#arfaddtopageloader').show();
        jQuery('#arfaddtopageloader').hide();
        var titile_val = jQuery('#form_title_i').val();
        var title_qs = (titile_val == 'yes') ? " title=true" : "";
        var description_qs = (titile_val == 'yes') ? " description=true" : "";
        var shrt_type = jQuery('input[name="shortcode_type"]:checked').val();
        var link_type = jQuery('#link_type').val();
        var link_position = jQuery('#link_position').val();
        var link_position_fly = jQuery('#link_position_fly').val();
        var modal_height = jQuery('#modal_height').val();
        var modal_width = jQuery('#modal_width').val();
        var open_inactivity = jQuery('#open_inactivity').val();
        var open_scroll = jQuery('#open_scroll').val();
        var open_delay = jQuery('#open_delay').val();
        var overlay = jQuery('#overlay').val();
        var show_close_link = jQuery('input[name="show_close_link"]:checked').val();
        var bgcolor = jQuery('#arf_modal_btn_bg_color').val();
        var txtcolor = jQuery('#arf_modal_btn_txt_color').val();
        var modal_bgcolor = jQuery('#arf_modal_bg_color').val();
        var show_full_screen = jQuery('input[name="show_full_screen"]:checked').val();
        var inact_min = jQuery('#inact_time').val();
        var modaleffect = jQuery('#modal_effect').val();

        if (shrt_type == 'normal')
        {
            var arfgt_shortcode=" [ARForms id=" + form_id + "]";
            if( typeof wp.blocks != 'undefined' ){
                if( typeof window.arf_props != 'undefined' && window.arf_props_selected == '1'){
                    window.arf_props.setAttributes( {'ARFShortcode': arfgt_shortcode});
                    var check_block_content_length = jQuery('#block-'+window.arf_props.clientId).find('.wp-block-arforms-arforms-shortcode').length;
                    if(check_block_content_length>0)
                    {
                        jQuery('#block-'+window.arf_props.clientId).find('.wp-block-arforms-arforms-shortcode').val(arfgt_shortcode);
                    }

                } 
            } else {setTimeout(function () {
                     window.send_to_editor(arfgt_shortcode);
                }, 10);
                
           } 
            
        }
        else if (shrt_type == 'popup') {
            var caption = jQuery('#short_caption').val();
            var arfgt_shortcode='';
            if (link_type == 'sticky') {
               arfgt_shortcode=" [ARForms_popup id=" + form_id + " desc='" + caption + "' type='" + link_type + "' position='" + link_position + "' height='" + modal_height + "' width='" + modal_width + "' bgcolor='" + bgcolor + "' txtcolor='" + txtcolor + "' ]";
            } else if (link_type == 'fly'){
                var button_angle = jQuery('#button_angle').val();
                arfgt_shortcode=" [ARForms_popup id=" + form_id + " desc='" + caption + "' type='" + link_type + "' position='" + link_position_fly + "' height='" + modal_height + "' width='" + modal_width + "' angle='" + button_angle + "' bgcolor='" + bgcolor + "' txtcolor='" + txtcolor + "' ]";
            } else if (link_type == 'onload') {
                arfgt_shortcode=" [ARForms_popup id=" + form_id + " type='" + link_type + "' width='" + modal_width + "' modaleffect='"+modaleffect+"' is_fullscreen='"+show_full_screen+"' overlay='" + overlay + "' is_close_link='" + show_close_link + "' modal_bgcolor='" + modal_bgcolor + "' ]";

            } else if (link_type == 'scroll') {
                arfgt_shortcode=" [ARForms_popup id=" + form_id + " type='" + link_type + "' width='" + modal_width + "' modaleffect='"+modaleffect+"' is_fullscreen='"+show_full_screen+"' on_scroll='" + open_scroll + "' overlay='" + overlay + "' is_close_link='" + show_close_link + "' modal_bgcolor='" + modal_bgcolor + "' ]";

            } else if (link_type == 'button') {
                arfgt_shortcode=" [ARForms_popup id=" + form_id + " desc='" + caption + "' type='" + link_type + "' width='" + modal_width + "' modaleffect='"+modaleffect+"' is_fullscreen='"+show_full_screen+"' overlay='" + overlay + "' is_close_link='" + show_close_link + "' bgcolor='" + bgcolor + "' txtcolor='" + txtcolor + "' modal_bgcolor='" + modal_bgcolor + "']";

            } else if (link_type == 'timer') {
                arfgt_shortcode=" [ARForms_popup id=" + form_id + " on_delay='" + open_delay + "' type='" + link_type + "' width='" + modal_width + "' modaleffect='"+modaleffect+"' is_fullscreen='"+show_full_screen+"' overlay='" + overlay + "' is_close_link='" + show_close_link + "' bgcolor='" + bgcolor + "' txtcolor='" + txtcolor + "' modal_bgcolor='" + modal_bgcolor + "']";

            }else if (link_type == 'on_exit') {
                
                arfgt_shortcode=" [ARForms_popup id=" + form_id + " type='" + link_type + "' width='" + modal_width + "' modaleffect='"+modaleffect+"' is_fullscreen='"+show_full_screen+"' is_close_link='" + show_close_link + "' modal_bgcolor='" + modal_bgcolor + "' ]";

            }else if(link_type == 'on_idle'){

                arfgt_shortcode=" [ARForms_popup id=" + form_id + " type='" + link_type + "' width='" + modal_width + "' modaleffect='"+modaleffect+"' is_fullscreen='"+show_full_screen+"' inactive_min='"+inact_min+"' overlay='" + overlay + "' is_close_link='" + show_close_link + "' bgcolor='" + bgcolor + "' txtcolor='" + txtcolor + "' modal_bgcolor='" + modal_bgcolor + "']";

            }else {
                arfgt_shortcode=" [ARForms_popup id=" + form_id + " desc='" + caption + "' type='" + link_type + "' width='" + modal_width + "' modaleffect='"+modaleffect+"'  is_fullscreen='"+show_full_screen+"' overlay='" + overlay + "' is_close_link='" + show_close_link + "' modal_bgcolor='" + modal_bgcolor + "']";
            }
            if( typeof wp.blocks != 'undefined' ){
                if( typeof window.arf_props != 'undefined' && window.arf_props_selected == '1'){
                    window.arf_props.setAttributes( {'ARFShortcode': arfgt_shortcode});
                    var check_block_content_length = jQuery('#block-'+window.arf_props.clientId).find('.wp-block-arforms-arforms-shortcode').length;
                    if(check_block_content_length>0)
                    {
                        jQuery('#block-'+window.arf_props.clientId).find('.wp-block-arforms-arforms-shortcode').val(arfgt_shortcode);
                    }

                } 
            } else {
                setTimeout(function () {
                    window.send_to_editor(arfgt_shortcode);
                }, 10);
            }    

        }
        jQuery('.arf_popup_container#arf_insert_popup_modal').parents('.arf_modal_overlay').removeClass('arfactive');
        jQuery('.arf_popup_container#arf_insert_popup_modal').removeClass('arfactive');        
    }
    function frm_insert_display() {
        var display_id = jQuery("#frm_add_display_id").val();
        if (display_id == "") {
            alert("<?php echo addslashes(esc_html__('Please select a custom display', 'ARForms')); ?>");
            return;
        }
        var filter_qs = jQuery("#frm_filter_content").is(":checked") ? " filter=1" : "";
        var win = window.dialogArguments || opener || parent || top;
        win.send_to_editor("[display-frm-data id=" + display_id + filter_qs + "]");
    }
    function arfchangepageload(){
        var is_onload = jQuery('input[name="open_type"]:checked').val();
        if (is_onload == 'yes'){
            jQuery('#normal_link_type').hide();
            jQuery('#load_link_type_div').show();
        } else {
            jQuery('#load_link_type_div').hide();
            jQuery('#normal_link_type').show();
        }
    }
    jQuery(document).on('click', '.arfnewmodalclose', function() {
        jQuery('#arfinsertform').hide();
        jQuery('.arfmodal-backdrop').remove();
    });    
    jQuery(document).on('click', '.arfmodal-backdrop', function() {
        jQuery('#arfinsertform').hide();
        jQuery('.arfmodal-backdrop').remove();
    });
    jQuery(document).on('click','#arfinsertform',function(event){
        event.stopPropagation();
    });
    function arf_add_fav_color(color, colpick) {
        if (color === undefined)
            return;
        var colors = arf_get_favourite_color();
        if (jQuery.inArray(color, colors) > -1) {
            return;
        }
        if (jQuery.inArray(color, colors) && color != '') {
            colors.splice(0, 0, color);
        }
        if (colors.length > 6) {
            colors = colors.slice(0, 6);
        }
        if (colors.length) {
            document.cookie = 'arf_fav_color[colors]=' + colors.join(',');
            arf_show_fav_colors(colpick.attr('data-colpick-id'), colpick.attr('data-column'));
        }
    }
    function arf_get_favourite_color() {
        var arf_cookies = getCookie('arf_fav_color[colors]');
        if (typeof arf_cookies == 'undefined' || arf_cookies == null) {
            return [];
        }
        if (arf_cookies.indexOf(";") > -1) {
            arf_cookies = arf_cookies.split(';')[0];
        }
        var arf_fav_colors = [];
        for (var x in arf_cookies) {
            if (arf_cookies != '')
                arf_fav_colors = arf_cookies.split(',');
        }
        return arf_fav_colors;
    }
    function getCookie(cname) {
        var name = cname + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }
    function arfupdate_color(jscolor, id, input_id) {
        var color_code = jQuery(id).val();
        var attr_input_id = jQuery(id).attr('id');
        jQuery('#' + attr_input_id).val(color_code);
        jQuery('*[data-fid='+attr_input_id+']').css('background-color',color_code);    
        jQuery("#arf_color_picker_input").val(color_code.replace('#',''));
    }
    function arf_show_fav_colors(colpick_id, column) {
        var colors = arf_get_favourite_color();
        jQuery('.arf_favourite_color').html('');
        jQuery.each(colors, function (index, item) {
            if (colpick_id == 'arf_modal_bg_color' || colpick_id == 'arf_modal_btn_bg_color' || colpick_id == 'arf_modal_btn_txt_color') {
                jQuery('.arf_favourite_color').append('<div class="arf_fav_color_list arf_fav_color_list_modal" data-column="' + column + '" data-colpick-id="' + colpick_id + '" data-color="' + item + '" style="background-color:' + item + '"></div>');
            } else {
                jQuery('.arf_favourite_color').append('<div class="arf_fav_color_list" data-column="' + column + '" data-colpick-id="' + colpick_id + '" data-color="' + item + '" style="background-color:' + item + '"></div>');
            }
        });
    }
    function arffindUnique(arr) {
        var result = [];
        arr.forEach(function(d) {
            if (result.indexOf(d) === -1) result.push(d);
        });
        return result;
    }
    function arf_initialize_dropdown_keypress(e,keyCode, obj) {
        var dl = jQuery(obj).parents('dl');
        var ul = dl.find('ul');
        var li_hovered = dl.find('li.arf_hovered');
        if (keyCode >= 38 && keyCode <= 41) {
            preventDefault(e);
            preventDefaultForScrollKeys(e);
            if (ul.find("li.arf_hovered").length > 0) {
                var current = ul.find("li.arf_hovered");
                if (keyCode == 38) {
                    if (current.siblings(':visible').addBack().index(current) == 0) {
                        ul.find("li:visible:last").addClass('arf_hovered');
                        ul[0].scrollTop = ul.prop('scrollHeight');
                    } else {
                        ul.find("li.arf_hovered").prevAll('li:visible').first().addClass('arf_hovered');
                        ul.scrollTop(30 * (li_hovered.siblings(':visible').addBack().index(li_hovered) - 3));
                    }
                } else if (keyCode == 40) {
                    if (current.siblings(':visible').addBack().index(current) == ul.find('li:visible').length - 1) {
                        ul.find("li:visible:first").addClass('arf_hovered');
                        ul[0].scrollTop = ul.find('li:visible:first').prop('scrollHeight') - 30;
                    } else {
                        ul.find("li.arf_hovered").nextAll('li:visible').first().addClass('arf_hovered');
                        ul.scrollTop(30 * (li_hovered.siblings(':visible').addBack().index(li_hovered) - 1));
                    }
                }
                current.removeClass('arf_hovered');
            } else {
                ul.find("li:visible:first").addClass('arf_hovered');
                ul.scrollTop(30 * (li_hovered.index() - 3));
            }
        } else if (keyCode == 13 || keyCode == 27) {
            li_hovered.trigger('click');
            (function(ulObj){
                setTimeout(function(){
                    ulObj.hide();
                },100);
            })(ul);
            return false;
        }
    }
    function disableScroll() {
        if (window.addEventListener){ // older FF
            window.addEventListener('wheel  DOMMouseScroll', preventDefault, false);
        }
        document.onkeydown = preventDefaultForScrollKeys;
    }
    function enableScroll() {
        if (window.removeEventListener) {
            window.removeEventListener('wheel', preventDefault, false);
            window.removeEventListener('DOMMouseScroll', preventDefault, false);
        }
        document.onkeydown = null;
    }
    function preventDefault(e) {
        e = e || window.event;
        if (e.preventDefault) e.preventDefault();
        e.returnValue = false;
    }
    function preventDefaultForScrollKeys(e) {
        var keys = {
            37: 1,
            38: 1,
            39: 1,
            40: 1
        };
        if (keys[e.keyCode]) {
            preventDefault(e);
            return false;
        }
    }
    

    jQuery(document).ready(function () {

        jQuery('#arf_modal_bg_color').wpColorPicker();

        jQuery('#arf_modal_btn_bg_color').wpColorPicker();

        jQuery('#arf_modal_btn_txt_color').wpColorPicker();

        
        jQuery(document).on('click', 'div.arf_add_favourite_color', function (e) {
            var $this = jQuery(this);
            var $parent = $this.parent().find('.colpick_hex_field');
            $input = $parent.find('input');
            if ($input.val().match(/^([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/g)) {
                arf_add_fav_color('#' + $input.val(), $this);
            }
            e.preventDefault();
        });
        jQuery(document).on('click', 'div.arf_fav_color_list', function (e) {
            var color = jQuery(this).attr('data-color');
            var colpick_id = jQuery(this).attr('data-colpick-id');
            setTimeout(function () {
                jQuery('div[data-fid="' + colpick_id + '"]').parent().find('input#' + colpick_id).trigger('change');
            }, 100);
            e.preventDefault();
        });
        jQuery(document).on('click', '.select_from_fav_color', function (e) {
            var $this = jQuery(this);
            var color = $this.attr('value');
            jQuery("#arf_color_picker_input").val(color.replace('#',''));
            var id = $this.parent().parent().find('.arf_add_favorite_color_btn').attr('data-value');
            jQuery(".arf_coloroption[data-fid='" + id + "']").css('background-color',color);            
            jQuery("#" + id).val(color).trigger('change');
            var elm = jQuery("#" +id ).parent('.arf_coloroption_sub').find('.arf_coloroption')[0];
        });
        jQuery(document).on('click', '#arf_add_favorite_color_btn', function (e) {
            var $this = jQuery(this);
            var $id = $this.attr('data-value');
            var $color = jQuery('#' + $id).val();
            if ($color === undefined) return;
            var colors = arf_get_favourite_color();
            if (jQuery.inArray($color, colors) > -1) {
                return;
            }
            if (jQuery.inArray($color, colors) && $color != '') {
                colors.splice(0, 0, $color);
            }
            if (colors.length > 7) {
                colors = colors.slice(0, 7);
            }
            if (colors.length) {                
                var current = new Date();
                current.setMonth(current.getMonth() + 1);
                document.cookie = 'arf_fav_color[colors]=' + colors.join(',') + '; expires=' + current.toGMTString();                
            }
            var favorite_colors = getCookie('arf_fav_color[colors]');            
            var object = jQuery('.arf_js_colorpicker').find('.arf_favorite_color_buttons');
            object.html('');
            if (typeof favorite_colors != undefined && favorite_colors != '') {
                var fav_cols = favorite_colors.split(',');
                for (var n in fav_cols) {
                    var color = fav_cols[n];
                    var div = document.createElement('div');
                    div.setAttribute('class', 'select_from_fav_color');
                    div.setAttribute('value', color);
                    div.setAttribute('style', 'background:' + color);
                    if (n < 7) {
                        object.append(div);
                    }
                }
            }
        });
        jQuery(document).on('click', '.arf_selectbox', function (e) {
            $this = jQuery(this);
            jQuery(this).find('dd ul').toggle();
            var col_id = jQuery(this).find('dd ul').attr('data-column');
            if (jQuery(this).find('dd ul').is(":visible")) {
                var id = jQuery(this).find('dd ul').attr('data-id');
                var value = jQuery('#main_' + col_id).find("input#" + id).val();
                if (value != '' && id != '') {
                    jQuery(this).find('dd ul li').each(function () {
                        if (jQuery(this).attr('data-value') == value) {
                            var target = jQuery(this);
                            var target_position = target.position().top;
                            if (Math.floor(target_position) > jQuery(this).parent().height()) {
                                jQuery(this).parent().animate({scrollTop: target.position().top}, 0);
                            }
                        }
                    });
                }
            }
        });
        jQuery(document).on('click', '.arf_selectbox dt', function () {
            var this_parent = jQuery(this).parent();
            if (jQuery(this).parent().find('dd ul').is(":visible") == false) {
                jQuery('dd ul').not(this).hide();
                var ul_h = this_parent.find('dd ul').height();
                var dd_h = this_parent.height();
                var win_h = jQuery(window);
                var offsetTop = this_parent.offset().top - win_h.scrollTop();
                if (win_h.height() - offsetTop - dd_h < ul_h) {
                    this_parent.find('dd ul').addClass('arfdropdownoptiontop');
                } else {
                    this_parent.find('dd ul').removeClass('arfdropdownoptiontop');
                }
                var chk_field_enabled = this_parent.find('dd ul').attr("data-id");
                var isDisabled = jQuery("#" + chk_field_enabled).prop('disabled');
                var isReadonly = jQuery("#" + chk_field_enabled).prop('readonly');
                if (isDisabled || isReadonly) {
                    if (isDisabled) {
                        this_parent.find('dd ul').hide();
                        this_parent.find('dt').addClass("arf_disable_selectbox");
                    } else if (isReadonly) {
                        this_parent.find('dd ul').hide();
                    }
                    return false;
                } else {
                    this_parent.find('dt').removeClass("arf_disable_selectbox");
                }
            } else {
                var chk_field_enabled = this_parent.find('dd ul').attr("data-id");
                var isDisabled = jQuery("#" + chk_field_enabled).prop('disabled');
                if (isDisabled) {
                    this_parent.find('dd ul').hide();
                    return false;
                } else {
                    this_parent.find('dd ul').show();
                }
            }
        });
        jQuery(document).on('keyup', '.arf_selectbox dt input', function () {
            jQuery(this).parent().parent().find('dd ul').scrollTop();
            var value = jQuery(this).val();
            value = value.toLowerCase();
            jQuery(this).parent().parent().find('dd ul').show();
            jQuery(this).parent().parent().find('dd ul li').each(function (x) {
                var text = jQuery(this).attr('data-label').toLowerCase();
                (text.indexOf(value) != -1) ? jQuery(this).show() : jQuery(this).hide();
            });
        });
        jQuery(document).on('click', ".arf_selectbox dd ul li", function (e) {
            jQuery(document).find('.arf_selectbox:active dd ul').hide();
            var text = jQuery(this).html();
            jQuery(this).parent().parent().parent().find('dt span').html(jQuery(this).attr('data-label'));
            jQuery(this).parent().parent().parent().find('dt span').show();
            jQuery(this).parent().parent().parent().find('dt input').val(jQuery(this).data('label'));
            jQuery(this).parent().parent().parent().find('dt input').hide();
            var id = jQuery(this).parent().attr('data-id');
            var value = jQuery(this).attr('data-value');
            var column_id = jQuery(this).parent().attr('data-column');
            if (typeof (column_id) !== 'undefined') {
                jQuery('#main_' + column_id).find('input#' + id).val(value);
                jQuery('#main_' + column_id).find('input#' + id).trigger('change');
            } else {
                jQuery('input#' + id).val(value);
                jQuery('input#' + id).trigger('change');
            }
            jQuery(this).parent().find('li').show();
        });
        jQuery(document).on('keydown', function(e) {
            var keyCode = e.keyCode;
            if (jQuery('.arf_selectbox dd ul:visible').length > 0) {
                arf_initialize_dropdown_keypress(e, e.keyCode, jQuery('.arf_selectbox dd ul:visible'));
            }
        });
        jQuery(window).scroll(function(e){
            if( jQuery('.arf_selectbox dd ul:visible').length > 0 ){
                disableScroll();
            } else {
                enableScroll();
            }
        });
        jQuery(document).bind('click', function (e) {
            var $clicked = jQuery(e.target);
            if (!$clicked.parents().hasClass("arf_selectbox")) {
                jQuery(".arf_selectbox dd ul").hide();
                jQuery('.arf_selectbox dt span').show();
                jQuery('.arf_selectbox dt input').hide();
                jQuery('.arf_autocomplete').each(function () {
                    if (jQuery(this).val() == '') {
                        jQuery(this).val(jQuery(this).parent().find('span').html());
                    }
                });
            }
            jQuery('.arf_selectbox').removeClass('active');
        });   

        jQuery(document).on('click','input[name="onclick_type"]',function(){
            var lin_type = jQuery('input[name="onclick_type"]:checked').val();
            lin_type = (lin_type != '')?lin_type:'link';
            jQuery('#link_type').val(lin_type);
            jQuery('#link_type').trigger('change');
        });

        jQuery('#shortcode_type_popup').click(function () {
            jQuery('#show_link_inner').slideUp();
            jQuery('#show_link_type').slideDown(700);
        });
        jQuery('#shortcode_type_normal').click(function () {
            jQuery('#show_link_inner').slideDown();
            jQuery('#show_link_type').slideUp(700);
        });
        jQuery('#link_type').change(function () {
            var show_link_type = jQuery('#link_type').val();
            var radio_link_type= jQuery('input[name="onclick_type"]:checked').val();
            radio_link_type = (show_link_type == 'onclick')?radio_link_type:'';
            var tid = jQuery('#arf_btn_txtcolor .arf_coloroption.arfhex').attr('data-fid');
            jQuery('#' + tid).val('#ffffff');

            if (show_link_type == 'sticky' || radio_link_type == 'sticky') {
                jQuery('#is_sticky').slideDown();
                jQuery('#is_fly').slideUp();
                jQuery('#button_angle_div').slideUp();
                jQuery('#arfmodalbuttonstyles').slideDown();
                jQuery('#arf_full_screen_modal').slideUp();
                jQuery('#ideal_time').slideUp();
                jQuery('#modal_effect_div').slideUp();
                
                jQuery('#is_scroll').slideUp();
                jQuery('#overlay_div').slideUp();
                jQuery('#is_close_link_div').slideDown();
                jQuery('#modal_height').parent().slideDown();

            } else if (show_link_type == 'fly' || radio_link_type == 'fly') {
                jQuery('#is_fly').slideDown();
                jQuery('#is_sticky').slideUp();
                jQuery('#button_angle_div').slideDown();
                jQuery('#arfmodalbuttonstyles').slideDown();
                jQuery('#arf_full_screen_modal').slideUp();
                jQuery('#ideal_time').slideUp();
                jQuery('#modal_effect_div').slideUp();
                
                jQuery('#is_scroll').slideUp();
                jQuery('#overlay_div').slideUp();
                jQuery('#is_close_link_div').slideDown();
                jQuery('#modal_height').parent().slideUp();

            } else if (show_link_type == 'scroll') {
                jQuery('#is_scroll').slideDown();
                jQuery('#is_close_link_div').slideDown();
                jQuery('#is_sticky').slideUp();
                jQuery('#is_fly').slideUp();
                jQuery('#button_angle_div').slideUp();
                jQuery('#shortcode_caption').slideUp();
                jQuery('#arfmodalbuttonstyles').slideUp();
                jQuery('#arf_full_screen_modal').slideDown();
                jQuery('#ideal_time').slideUp();
                jQuery('#modal_effect_div').slideDown();
                jQuery('#modal_height').parent().slideUp();

            } else if (show_link_type == 'link' || radio_link_type == 'link') {
                jQuery('#overlay_div').slideDown();
                jQuery('#is_close_link_div').slideDown();
                jQuery('#is_sticky').slideUp();
                jQuery('#is_fly').slideUp();
                jQuery('#button_angle_div').slideUp();
                jQuery('#is_scroll').slideUp();
                jQuery('#arfmodalbuttonstyles').slideUp();
                jQuery('#arf_full_screen_modal').slideDown();
                jQuery('#ideal_time').slideUp();
                jQuery('#modal_effect_div').slideDown();
                jQuery('#modal_height').parent().slideUp();

            } else if (show_link_type == 'button' || radio_link_type == 'button') {
                jQuery('#overlay_div').slideDown();
                jQuery('#is_close_link_div').slideDown();
                jQuery('#is_sticky').slideUp();
                jQuery('#is_fly').slideUp();
                jQuery('#button_angle_div').slideUp();
                jQuery('#is_scroll').slideUp();
                jQuery('#arfmodalbuttonstyles').slideDown();
                jQuery('#arf_full_screen_modal').slideDown();
                jQuery('#ideal_time').slideUp();
                jQuery('#modal_effect_div').slideDown();
                
                jQuery('#modal_height').parent().slideUp();

            } else if(show_link_type == 'on_idle'){
                jQuery('#overlay_div').slideDown();
                jQuery('#is_close_link_div').slideDown();
                jQuery('#is_sticky').slideUp();
                jQuery('#is_fly').slideUp();
                jQuery('#button_angle_div').slideUp();
                jQuery('#is_scroll').slideUp();
                jQuery('#arfmodalbuttonstyles').slideUp();
                jQuery('#arf_full_screen_modal').slideDown();
                jQuery('#ideal_time').slideDown();
                jQuery('#modal_effect_div').slideDown();
                jQuery('#modal_height').parent().slideUp();

            }else if(show_link_type == 'on_exit'){
                jQuery('#overlay_div').slideDown();
                jQuery('#is_close_link_div').slideDown();
                jQuery('#is_sticky').slideUp();
                jQuery('#is_fly').slideUp();
                jQuery('#button_angle_div').slideUp();
                jQuery('#is_scroll').slideUp();
                jQuery('#arfmodalbuttonstyles').slideUp();
                jQuery('#arf_full_screen_modal').slideDown();
                jQuery('#ideal_time').slideUp();
                jQuery('#modal_effect_div').slideDown();
                jQuery('#modal_height').parent().slideUp();

            }else {
                jQuery('#is_sticky').slideUp();
                jQuery('#is_fly').slideUp();
                jQuery('#button_angle_div').slideUp();
                jQuery('#arfmodalbuttonstyles').slideUp();
                jQuery('#arf_full_screen_modal').slideDown();
                jQuery('#is_scroll').slideUp();
                jQuery('#is_close_link_div').slideUp();
                jQuery('#modal_height').parent().slideUp();
            }
            if (show_link_type == 'onload' || show_link_type == 'scroll' || show_link_type == 'timer' ||  show_link_type == 'on_idle' ||  show_link_type == 'on_exit') {
                jQuery('#shortcode_caption').slideUp();
                jQuery('#overlay_div').slideDown();
                jQuery('#modal_effect_div').slideDown();
            } else {
                jQuery('#shortcode_caption').slideDown();
            }
            if (show_link_type == 'timer') {
                jQuery('#is_delay').slideDown();
                jQuery('#is_close_link_div').slideDown();
            } else {
                jQuery('#is_delay').slideUp();
            }

            if(show_link_type == 'onclick' || show_link_type == 'button' || show_link_type == 'link' || show_link_type == 'sticky' || show_link_type == 'fly'){
                jQuery('#list_of_onclick').slideDown();
            }else{
                jQuery('#list_of_onclick').slideUp();
            }

        });
        jQuery('#link_position_fly').change(function () {
            var position = jQuery(this).val();
            var color = (position == 'left') ? '#2d6dae' : '#8ccf7a';
            
        });
        jQuery('#link_position').change(function () {
            var position = jQuery(this).val();
            var color = (['left', 'right', 'bottom'].indexOf(position) > -1) ? '#1bbae1' : '#93979d';
            
        });
    });
    function changeflybutton() {
        var angle = jQuery('#button_angle').val();
        angle = angle != '' ? angle : 0;
        jQuery('.arf_fly_btn').css('transform', 'rotate(' + angle + 'deg)');
    }
    function arfchangeflybtn() {
        if (jQuery('#link_position_fly').val() == 'right') {
            jQuery('.arfbtnleft').hide();
            jQuery('.arfbtnright').show();
        } else {
            jQuery('.arfbtnleft').show();
            jQuery('.arfbtnright').hide();
        }
    }
    function arf_close_field_option_popup(){
        jQuery('.arf_popup_container#arf_insert_popup_modal').parents('.arf_modal_overlay').removeClass('arfactive');
        jQuery('.arf_popup_container#arf_insert_popup_modal').removeClass('arfactive');
    }
    function arfreinilizecolorpicker(color_code, id){
        var color = color_code;
        jQuery("#arf_color_picker_input").val(color.replace('#',''));
        jQuery(".arf_coloroption[data-fid='" + id + "']").css('background-color',color);            
        jQuery("#" + id).val(color).trigger('change');
        var elm = jQuery("#" +id ).parent('.arf_coloroption_sub').find('.arf_coloroption')[0];            
        if (typeof __JSPICKER != 'undefined') {
            __JSPICKER = arffindUnique(__JSPICKER);
            jQuery(__JSPICKER).each(function(n) {
                var array = __JSPICKER[n];
                if (typeof array == 'undefined') {
                    return true;
                }
                var target = array.targetElement;
                if (elm == target) {
                    var new_color = color.replace('#', '');
                    __JSPICKER[n].fromString(new_color, undefined);
                }
            });
        }
    }

    jQuery(document).on('click', '.arf_selectbox dt', function(e) {

        if(jQuery(this).hasClass('arf_disable_selectbox')){ return false;}

        if (jQuery(this).find('.arf_autocomplete').length > 0) {
            
            this.getElementsByTagName('input')[0].value = '';
            this.getElementsByTagName('span')[0].style.display = 'none';
            this.getElementsByTagName('input')[0].style.display = '';
            this.getElementsByTagName('input')[0].focus();
        }
        var this_parent = jQuery(this).parent();
        if (jQuery(this).hasClass('arf_disabled_container')) {
            return false;
        }
        if (jQuery(this).parent().find('dd ul').is(":visible") == false) {
            jQuery('dd ul').not(this).hide();
            var ul_h = this_parent.find('dd ul').height();
            var dd_h = this_parent.height();
            var win_h = jQuery(window);
            var offsetTop = this_parent.offset().top - win_h.scrollTop();
           
            if (win_h.height() - offsetTop - dd_h < ul_h) {
                this_parent.find('dd ul').addClass('arfdropdownoptiontop');
            } else {
                this_parent.find('dd ul').removeClass('arfdropdownoptiontop');
            }
           
            var chk_field_enabled = this_parent.find('dd ul').attr("data-id");
            var isDisabled = jQuery("#" + chk_field_enabled).prop('disabled');
            var isReadonly = jQuery("#" + chk_field_enabled).prop('readonly');
            if (isDisabled || isReadonly) {
                if (isDisabled) {
                    this_parent.find('dd ul').hide();
                    this_parent.find('dt').addClass("arf_disable_selectbox");

                } else if (isReadonly) {
                    this_parent.find('dd ul').hide();
                }
                return false;
            } else {
                this_parent.find('dt').removeClass("arf_disable_selectbox");
            }
        } else {
            var chk_field_enabled = this_parent.find('dd ul').attr("data-id");
            var isDisabled = jQuery("#" + chk_field_enabled).prop('disabled');
            if (isDisabled) {
                this_parent.find('dd ul').hide();
                return false;
            } else {
                this_parent.find('dd ul').show();
            }
        }
    });

    jQuery(document).on('keyup', '.arf_selectbox dt input', function() {
        
        jQuery(this).parent().parent().find('dd ul').scrollTop();
        var value = jQuery(this).val();
        value = value.toLowerCase();
        jQuery(this).parent().parent().find('dd ul').show();
        jQuery(this).parent().parent().find('dd ul li').each(function(x) {
            var text = jQuery(this).attr('data-label').toLowerCase();
            (text.indexOf(value) != -1) ? jQuery(this).show(): jQuery(this).hide();
        });
    });

    jQuery(document).bind('click', function(e) {
      
        var $clicked = jQuery(e.target);
        if (!$clicked.parents().hasClass("arf_selectbox") && jQuery('.arf_selectbox dd ul:visible').length > 0) {
            jQuery(".arf_selectbox dd ul").hide();
            jQuery('.arf_selectbox dt span').show();
            jQuery('.arf_selectbox dt input').hide();
            jQuery('.arf_autocomplete').each(function() {

                if (jQuery(this).val() == '') {
                    jQuery(this).val(jQuery(this).parent().find('span').html());
                }
            });
        }
        jQuery('.arf_selectbox').removeClass('active');
    });

    jQuery(document).on('mouseenter', '.arf_selectbox', function() {
        jQuery(this).find('li.arf_hovered').removeClass('arf_hovered');
    });

    jQuery(document).on('keydown', function(e) {
        var keyCode = e.keyCode;
        if (jQuery('.arf_selectbox dd ul:visible').length > 0 ) {
            arf_initialize_dropdown_keypress(e, e.keyCode, jQuery('.arf_selectbox dd ul:visible'));
        }
    });

    jQuery(window).scroll(function(e) {
        if (jQuery('.arf_selectbox dd ul:visible').length > 0) {
            disableScroll();
        } else {
            enableScroll();
        }
    });

    function arf_initialize_dropdown_keypress(e,keyCode, obj) {
        var dl = jQuery(obj).parents('dl');
        var dt = dl.find('dt');
        if(!dt.hasClass('arf_disable_selectbox')){

            var ul = dl.find('ul');
            var li_hovered = dl.find('li.arf_hovered');
            if (keyCode >= 38 && keyCode <= 41) {
                preventDefault(e);
                preventDefaultForScrollKeys(e);
                if (ul.find("li.arf_hovered").length > 0) {
                    var current = ul.find("li.arf_hovered");
                    if (keyCode == 38) {
                        if (current.siblings(':visible').addBack().index(current) == 0) {
                            ul.find("li:visible:last").addClass('arf_hovered');
                            ul[0].scrollTop = ul.prop('scrollHeight');
                        } else {
                            ul.find("li.arf_hovered").prevAll('li:visible').first().addClass('arf_hovered');
                            ul.scrollTop(30 * (li_hovered.siblings(':visible').addBack().index(li_hovered) - 3));
                        }
                    } else if (keyCode == 40) {
                        if (current.siblings(':visible').addBack().index(current) == ul.find('li:visible').length - 1) {
                            ul.find("li:visible:first").addClass('arf_hovered');
                            ul[0].scrollTop = ul.find('li:visible:first').prop('scrollHeight') - 30;
                        } else {
                            ul.find("li.arf_hovered").nextAll('li:visible').first().addClass('arf_hovered');
                            ul.scrollTop(30 * (li_hovered.siblings(':visible').addBack().index(li_hovered) - 1));
                        }
                    }
                    current.removeClass('arf_hovered');
                } else {
                    ul.find("li:visible:first").addClass('arf_hovered');
                    ul.scrollTop(30 * (li_hovered.index() - 3));
                }
            } else if (keyCode == 13 || keyCode == 27) {
                preventDefault(e);
                li_hovered.trigger('click');
                (function(ulObj) {
                    setTimeout(function() {
                        ulObj.hide();
                    }, 100);
                })(ul);
                return false;
            }
        }
    }

</script>

<?php
wp_register_style('arf-fontawesome-css', ARFURL . '/css/font-awesome.min.css', array(), $arfversion);
$armainhelper->load_styles(array('arf-fontawesome-css'));
wp_enqueue_style( 'wp-color-picker' );
wp_enqueue_script( 'wp-color-picker');
?>
<style type="text/css">
    @font-face {
        font-family: 'Asap-Regular';
        src: url('<?php echo ARFURL; ?>/fonts/Asap-Regular.eot');
        src: url('<?php echo ARFURL; ?>/fonts/asap-regular-webfont.woff2') format('woff2'), 
             url('<?php echo ARFURL; ?>/fonts/Asap-Regular.woff') format('woff'), 
             url('<?php echo ARFURL; ?>/fonts/Asap-Regular.ttf') format('truetype'), 
             url('<?php echo ARFURL; ?>/fonts/Asap-Regular.svg#Asap-Regular') format('svg'), 
             url('<?php echo ARFURL; ?>/fonts/Asap-Regular.eot?#iefix') format('embedded-opentype');
        font-weight: normal;
        font-style: normal;
    }
    body.rtl .arf_popup_container_header{
        float: right;
        text-align: right;
    }
    body.rtl .arf_popup_container_footer{
        float: right;
    }
    body.rtl .arf_field_option_close_button{
        left: 1%;
        right: inherit;
    }
    body.rtl .arf_field_option_submit_button {
        left: 16%;
        right: inherit;
    }
    body.rtl .arfinsertform_modal_container{
        float: right;
        text-align: right;
    }
    body.rtl .arf_selectbox dt span{
        float: right !important;
    }
    body.rtl .arf_selectbox dt i{
        float: left;
        left: 15px;
        right: inherit;
    }
    body.rtl .arf_selectbox dd ul{
        float: right;
    }
    body.rtl .arfinsertform_modal_container .arf_radio_wrapper {
        float: right;
        margin-left: 20px;
    }
    body.rtl .arfinsertform_modal_container .arf_custom_radio_div {
        float: right;
    }
    body.rtl .arf_custom_checkbox_wrapper,body.rtl .arf_custom_radio_wrapper {
        float: right;
    }
    body.rtl .arf_custom_checkbox_wrapper input[type="checkbox"],body.rtl .arf_custom_radio_wrapper input[type="radio"]{
        float: right;   
    }
    body.rtl input[type=radio]:checked:before, body.rtl input[type=checkbox]:checked:before{
        float: left;
    }
    body.rtl .arfinsertform_modal_container .arf_custom_radio_div .arf_custom_radio_wrapper + span {
        float: right;
    }
    body.rtl .arf_radio_wrapper span label{
        float: right !important;
        margin-right: inherit !important;   
    }
    body.rtl #show_link_type{
        float: right;
    }
    .arf_modal_overlay.arfactive {
        display: block;
    }
    .arf_modal_overlay {
        float: left;
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(10, 14, 33, 0.6);
        z-index: 99999;
        text-align: center;
        display: none;
    }
    #arf_insert_popup_modal {
        -webkit-box-shadow: 0px 0px 15px 0px rgba(3, 169, 244, 0.15);
        -o-box-shadow: 0px 0px 15px 0px rgba(3, 169, 244, 0.15);
        -moz-box-shadow: 0px 0px 15px 0px rgba(3, 169, 244, 0.15);
        box-shadow: 0px 0px 15px 0px rgba(3, 169, 244, 0.15);
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        -o-border-radius: 3px;
        font-family: Asap-Regular;
    }
    .arf_popup_container.arfactive {
        display: block;
    }
    .arf_popup_container {
        float: none;
        position: fixed;
        top: 15%;
        left: 23.30%;
        background: #fff;
        display: none;
        z-index: 9991;
        border-radius: 3px;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        -o-border-radius: 3px;
        transition: all 0.4s ease 0s;
        -webkit-transition: all 0.4s ease 0s;
        -o-transition: all 0.4s ease 0s;
        -moz-transition: all 0.4s ease 0s;
        box-shadow: 0px 0px 15px 0px rgba(3, 169, 244, 0.15);
        -webkit-box-shadow: 0px 0px 15px 0px rgba(3, 169, 244, 0.15);
        -o-box-shadow: 0px 0px 15px 0px rgba(3, 169, 244, 0.15);
        -moz-box-shadow: 0px 0px 15px 0px rgba(3, 169, 244, 0.15);
        overflow-x: hidden;
        overflow-y: auto;
    }
    .arf_popup_container_header {
        float: left;
        width: 100%;
        height: 43px;
        line-height: 43px;
        padding: 0 15px;
        background: #F0F5FF;
        font-family: Asap-Medium;
        font-size: 18px;
        text-align: left;
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        -o-box-sizing: border-box;
        -webkit-box-sizing: border-box;
        position: relative;
        border-bottom: 1px #dee6fb solid;
        color: #262944;
    }
    
    @media only screen and (min-width: 1224px){
        .arf_insert_popup_modal{
            min-height: 80%;
            max-height: 80%;
        }
        .arfinsertform_modal_container{
            min-height: 80%;
            max-height: 80%;
        }
    }
    @media all and (min-width: 1600px) and (max-width: 1899px){
        .arf_popup_container{
            top: 21.2%;
            left: 27.4%;
        }

    }
    @media all and (min-width:1900px){
        .arf_popup_container{
            top: 24%;
            left: 31.3%;
        }
    }
    .arf_insert_popup_modal {
        float: left;
        text-align: left;
        height: 80%;
        min-height: 457px;
        max-height: 457px;
        width:717px;
        overflow-x: hidden;
        overflow-y: auto;
    }
    .arf_popup_container_footer {
        float: left;
        width: 100%;
        height: 58px;
        border-top: 1px #d5e3ff solid;
        margin-top: 10px;        
        position: absolute;
        overflow: hidden;
        bottom: 0px;
        background: #fff;        
    }
    @font-face {
        font-family: 'Asap-Regular';
        src: url('<?php echo ARFURL; ?>/fonts/Asap-Regular.eot');
        src: url('<?php echo ARFURL; ?>/fonts/asap-regular-webfont.woff2') format('woff2'), url('<?php echo ARFURL; ?>/fonts/Asap-Regular.woff') format('woff'), url('<?php echo ARFURL; ?>/fonts/Asap-Regular.ttf') format('truetype'), url('<?php echo ARFURL; ?>/fonts/Asap-Regular.svg#Asap-Regular') format('svg'), url('<?php echo ARFURL; ?>/fonts/Asap-Regular.eot?#iefix') format('embedded-opentype');
        font-weight: normal;
        font-style: normal;
    }
    @font-face {
        font-family: 'Asap-Medium';
        src: url('<?php echo ARFURL; ?>/fonts/Asap-Medium.eot');
        src: url('<?php echo ARFURL; ?>/fonts/asap-medium-webfont.woff2') format('woff2'), url('<?php echo ARFURL; ?>/fonts/Asap-Medium.woff') format('woff'), url('<?php echo ARFURL; ?>/fonts/Asap-Medium.ttf') format('truetype'), url('<?php echo ARFURL; ?>/fonts/Asap-Medium.svg#Asap-Medium') format('svg'), url('<?php echo ARFURL; ?>/fonts/Asap-Medium.eot?#iefix') format('embedded-opentype');
        font-weight: normal;
        font-style: normal;
    }
    #arfinsertform.arfmodal {
        border-radius:0px;
        -webkit-border-radius:0px;
        -moz-border-radius:0px;
        -o-border-radius:0px;
        text-align:center;
        width:560px;
        height:auto;
        left:35%;
        border:none;
    }
    .arfmodal .btn-group.bootstrap-select 
    {
        text-align:left;
    }
    .arfmodal .btn-group .btn.dropdown-toggle,.arfmodal .btn-group .arfbtn.dropdown-toggle {
        border: 1px solid #CCCCCC;
        background-color:#FFFFFF;
        background-image:none;
        box-shadow:none;
        -webkit-box-shadow:none;
        -moz-box-shadow:none;
        -o-box-shadow:none;
        outline:0 !important;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
        -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
        -moz-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
        -o-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
    }
    .arfmodal .btn-group.open .btn.dropdown-toggle,.arfmodal .btn-group.open .arfbtn.dropdown-toggle {
        border:solid 1px #CCCCCC;
        background-color:#FFFFFF;
        border-bottom-color:transparent;
        box-shadow:none;
        -webkit-box-shadow:none;
        -moz-box-shadow:none;
        -o-box-shadow:none;
        outline:0 !important;
        outline-style:none;
        border-bottom-left-radius:0px;
        border-bottom-right-radius:0px;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
        -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
        -moz-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
        -o-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
    }
    .arfmodal .btn-group.dropup.open .btn.dropdown-toggle, .arfmodal .btn-group.dropup.open .arfbtn.dropdown-toggle {
        border:solid 1px #CCCCCC;
        background-color:#FFFFFF;
        border-top-color:transparent;
        box-shadow:none;
        -webkit-box-shadow:none;
        -moz-box-shadow:none;
        -o-box-shadow:none;
        outline:0 !important;
        outline-style:none;
        border-top-left-radius:0px;
        border-top-right-radius:0px;
        border-bottom-left-radius:6px;
        border-bottom-right-radius:6px;
    }
    .arfmodal .btn-group .arfdropdown-menu {
        margin:0;
    }
    .arfmodal .btn-group.open .arfdropdown-menu {
        border:solid 1px #CCCCCC;
        box-shadow:none;
        -webkit-box-shadow:none;
        -moz-box-shadow:none;
        -o-box-shadow:none;
        border-top:none;
        margin:0;
        margin-top:-1px;
        border-top-left-radius:0px;
        border-top-right-radius:0px;    
    }
    .arfmodal .btn-group.dropup.open .arfdropdown-menu {
        border-top:solid 1px #CCCCCC;
        box-shadow:none;
        -webkit-box-shadow:none;
        -moz-box-shadow:none;
        -o-box-shadow:none;
        border-bottom:none;
        margin:0;
        margin-bottom:-1px;
        border-bottom-left-radius:0px;
        border-bottom-right-radius:0px;
        border-top-left-radius:6px;
        border-top-right-radius:6px;    
    }
    .arfmodal .btn-group.dropup.open .arfdropdown-menu .arfdropdown-menu.inner {
        border-top:none;
    }
    .arfmodal .btn-group.open ul.arfdropdown-menu {
        border:none;
    }

    .arfmodal .arfdropdown-menu > li {
        margin:0px;
    }

    .arfmodal .arfdropdown-menu > li > a {
        padding: 6px 12px;
        text-decoration:none;
    }

    .arfmodal .arfdropdown-menu > li:hover > a {
        background:#1BBAE1;
    }

    .arfmodal .bootstrap-select.btn-group, 
    .arfmodal .bootstrap-select.btn-group[class*="span"] {
        margin-bottom:5px;
    }

    .arfmodal ul, .wrap ol {
        margin:0;
        padding:0;
    }

    .arfmodal form {
        margin:0;
    }   

    .arfmodal label {
        display:inline;
        margin-left:5px;
    }

    .arfnewmodalclose
    {
        font-size: 15px;
        font-weight: bold;
        height: 19px;
        position: absolute;
        right: 3px;
        top:5px;
        width: 19px;
        cursor:pointer;
        color:#D1D6E5;
    } 
    #arfinsertform
    {
        text-align:center;
    }
    .newform_modal_title
    {
        font-size:24px;
        font-family:'Asap-Medium', Arial, Helvetica, Verdana, sans-serif;
        
        color:#d1d6e5;
        margin-top:14px;
    }

    
    .arfmodal .txtmodal1 
    {
        height:36px;
        border:1px solid #cccccc;
        -o-border-radius:3px;
        -moz-border-radius:3px;
        -webkit-border-radius:3px;
        border-radius:3px;
        color:#353942;
        background:#FFFFFF;
        font-family:'Asap-Regular', Arial, Helvetica, Verdana, sans-serif;
        font-size:14px;
        margin:0px;
        letter-spacing:0.8px;
        padding:0px 10px 0 10px;
        width:360px;
        outline:none;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
        -webkit-box-shadow: 0px 0px 1px rgba(0, 0, 0, 0), 0 1px 1px rgba(0, 0, 0, 0.1) inset;
        -moz-box-shadow: 0px 0px 1px rgba(0, 0, 0, 0), 0 1px 1px rgba(0, 0, 0, 0.1) inset;
        -o-box-shadow: 0px 0px 1px rgba(0, 0, 0, 0), 0 1px 1px rgba(0, 0, 0, 0.1) inset;
        -webkit-box-sizing: content-box;
        -o-box-sizing: content-box;
        -moz-box-sizing: content-box;
        box-sizing: content-box;
    }
    .arfmodal .txtmodal1:focus
    {
        
        border:1px solid #1BBAE1;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
        -webkit-box-shadow: 0px 0px 1px rgba(0, 0, 0, 0), 0 1px 1px rgba(0, 0, 0, 0.1) inset;
        -moz-box-shadow: 0px 0px 1px rgba(0, 0, 0, 0), 0 1px 1px rgba(0, 0, 0, 0.1) inset;
        -o-box-shadow: 0px 0px 1px rgba(0, 0, 0, 0), 0 1px 1px rgba(0, 0, 0, 0.1) inset;
        transition:none;
        -webkit-transition:none;
        -moz-transition:none;
        -o-transition:none;
    }
    .newmodal_field_title
    {
        margin:20px 0 10px 0;
        font-family:'Asap-Medium', Arial, Helvetica, Verdana, sans-serif;
        
        font-size:14px;
        color:#353942;
    }
    .arfmodal input[class="rdomodal"] {
        display:none;
    }

    .arfmodal input[class="rdomodal"] + label {
        color:#333333;
        font-size:14px;
        font-family:'Asap-Regular', Arial, Helvetica, Verdana, sans-serif;
    }

    .arfmodal input[class="rdomodal"] + label span {
        display:inline-block;
        width:19px;
        height:19px;
        margin:-1px 4px 0 0;
        vertical-align:middle;
        background:url(<?php echo ARFURL; ?>/images/dark-radio-green.png) -37px top no-repeat;
        cursor:pointer;
    }

    .arfmodal input[class="rdomodal"]:checked + label span
    {
        background:url(<?php echo ARFURL; ?>/images/dark-radio-green.png) -56px top no-repeat;
    }
    .arfmodalfields
    {
        display:table;
        text-align: center;
        margin-top:10px;
        width:100%;
    }
    .arfmodalfields .arfmodalfield_left
    {
        display:table-cell;
        text-align:right;
        width:45%;
        padding-right:20px; 
        font-family:'Asap-Medium', Arial, Helvetica, Verdana, sans-serif;
        font-weight:normal;
        font-size:14px;
        color:#353942;
    }
    .arfmodalfields .arfmodalfield_right
    {
        display:table-cell;
        text-align:left;
    }
    .arfmodal .arf_px
    {
        font-family:'Asap-Regular', Arial, Helvetica, Verdana, sans-serif;
        font-size:12px;
        color:#353942;  
    }

    
    body.rtl .arfnewmodalclose
    {
        right:auto;
        left:3px;
    }
    body.rtl .arfmodalfields .arfmodalfield_left
    {
        text-align:left;
    }
    body.rtl .arfmodalfields .arfmodalfield_right
    {
        text-align:right;
        padding-right:20px; 
    }
    body.rtl .arfmodal .bootstrap-select.btn-group .arfbtn .filter-option
    {
        top:5px;
        right:8px;
        left:auto;
    }

    body.rtl .arfmodal .bootstrap-select.btn-group .arfbtn .caret
    {
        left:8px;
        right:auto;
    }
    body.rtl .arfmodal .btn-group.open .arfdropdown-menu {
        text-align:right;
    }
    .arf_coloroption_sub{
        border: 4px solid #D5E3FF;
        border-radius: 2px;
        -webkit-border-radius: 2px;
        -moz-border-radius: 2px;
        -o-border-radius: 2px;
        cursor: pointer;
        height: 25px;
        width: 55px;
        margin-left:30px;        
    }

    .arf_coloroption{
        cursor: pointer;
        height: 25px;
        width: 55px;
    }

    .arf_coloroption_subarrow_bg{
        background: none repeat scroll 0 0 #dcdfe4;
        height: 8px;
        margin-left: 48px;
        margin-top: -8px;
        text-align: center;
        vertical-align: middle;
        width: 8px;
    }

    .arf_coloroption_subarrow{
        background: <?php echo "url(" . ARFURL . "/images/colpickarrow.png) no-repeat center center"; ?>;
        height: 3px;
        padding-left: 5px;
        padding-top: 6px;
        width: 5px;
    }           
    .main_div_container{
    box-sizing:border-box;
        padding:25px 30px 45px 30px;
        width:100%;
        height:auto;
        max-height:80%;
        min-height:80%;
        float: left;
    }
    .arfinsertform_modal_container{
        float: left;
        text-align: left;
        width:100%;
        height:auto;
        max-height:78%;
        min-height:78%;
        overflow-y:auto;
        overflow-x:hidden;
    }
    .arf_field_option_submit_button{
        float: none;
        width: 120px;
        padding-bottom: 3px;
        height: 33px;
        font-size: 14px;
        border: none;
        border-radius: 85px;
        -webkit-border-radius: 85px;
        -o-border-radius: 85px;
        -moz-border-radius: 85px;
        position: absolute;
        right: 1%;
        top: 48%;
        transform: translateY(-50%);
        -webkit-transform: translateY(-50%);
        -moz-transform: translateY(-50%);
        -o-transform: translateY(-50%);
        outline: none;
        cursor: pointer;
        font-weight: bold;
        right: 16%;
        background: #4786ff;
        color: #ffffff;
    }
    .arf_field_option_close_button{
        float: none;
        width: 100px;
        padding-bottom: 3px;
        height: 33px;
        font-size: 14px;
        border: none;
        border-radius: 85px;
        -webkit-border-radius: 85px;
        -o-border-radius: 85px;
        -moz-border-radius: 85px;
        position: absolute;
        right: 1%;
        top: 48%;
        transform: translateY(-50%);
        -webkit-transform: translateY(-50%);
        -moz-transform: translateY(-50%);
        -o-transform: translateY(-50%);
        outline: none;
        background: #DFECF2;
        cursor: pointer;
        font-weight: bold;
        color: #000000;
    }
    .arfinsertform_modal_container label{
        font-family:Asap-Regular;
        font-size:14px;
        margin-bottom: 10px;
        margin-top: 10px;
        display: block;
    }
    dl.arf_selectbox {
        margin: 0;
        padding: 0;
        height: 30px;
        position: relative;
    }
    .arf_selectbox {
        cursor: pointer;
    }
    .arf_selectbox dt {
        background: #ffffff;
        border: 1px solid #D5E3FF;
        border-radius: 3px;
        color: #000000;
        display: inline-block;
        font-size: 14px;
        height: 30px;
        line-height: 30px;
        overflow: hidden;
        padding: 0 8px;
        width: 100%;
        position: relative;
    }
    .arf_selectbox dt span {
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
        display: inline-block;
        width: 75%;
        font-size: 14px;
        font-family: Asap-Regular;
        color: #4e5462;
    }
    .arf_selectbox dt i {
        float: right;
        font-size: 14px;
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        -webkit-transform: translateY(-50%);
        -moz-transform: translateY(-50%);
        -o-transform: translateY(-50%);
        width: 0;
        right: 15px;
    }
    .arf_selectbox dd ul {
        background: #ffffff;
        border: 1px solid #d5e3ff;
        border-top: 0px;
        display: none;
        -o-border-radius: 0 0 3px 3px;
        -moz-border-radius: 0 0 3px 3px;
        -webkit-border-radius: 0 0 3px 3px;
        border-radius: 0 0 3px 3px;
        float: left;
        margin-top: -7px !important;
        overflow-x: hidden;
        overflow-y: auto;
        padding: 0;
        width: 100%;
        z-index: 99999;
        max-height: 120px;
        position: absolute;
        color: #404852;
    }
    .arf_selectbox dd ul li {
        display: inline-block;
        min-height: 22px;
        height: auto;
        margin: 0px;
        line-height: 22px !important;
        padding: 4px 7px 4px 15px;
        width: 100%;
        z-index: 99999;
        font-size: 14px;
        font-family: Asap-Regular;
    }
    .arf_selectbox dd {
        position: relative;
        margin:0px;
        padding:0px;
    }
    .arf_selectbox dd ul li:hover {
        background: #3f74e7;
        color: #FFFFFF !important;
    }
    .arfmarginb20{
        margin-bottom:20px;
    }
    /*Custom Checkbox css start*/
    .arfinsertform_modal_container .arf_radio_wrapper {
        float: left;
        margin-right: 20px;
        width: auto;
        min-width: 80px;
    }
    .arfinsertform_modal_container .arf_custom_radio_div {
        float: left;
        margin-top: 3px;
        height: 23px;
        width:auto;
        position:relative;
    }
    .arfinsertform_modal_container .arf_custom_radio_div .arf_custom_radio_wrapper + span{
        float:left;
        width:auto;
        margin-left:8px;
        position:relative;
        top:50%;
        transform:translateY(-70%);
        -webkit-transform:translateY(-70%);
        -o-transform:translateY(-70%);
        -moz-transform:translateY(-70%);
    }
    .arf_custom_checkbox_wrapper,
    .arf_custom_radio_wrapper{
        float:left;
        width:18px;
        height:18px;
        position: relative;
    }
    

    .arf_custom_checkbox_wrapper input[type="checkbox"],
    .arf_custom_radio_wrapper input[type="radio"]{
        float:left;
        width:18px;
        height:18px;
        position: absolute;
        opacity: 0;
    }

    .arf_custom_checkbox_wrapper svg path{
        fill:#C0C3CB;
    }

    .arf_custom_checkbox_wrapper input[type="checkbox"]:checked + svg path#arfcheckbox_unchecked,
    .arf_custom_checkbox_wrapper input[type="checkbox"] + svg path#arfcheckbox_checked,
    .arf_custom_radio_wrapper input[type="radio"]:checked + svg path#arfradio,
    .arf_custom_radio_wrapper input[type="radio"] + svg path#arfradio_checked{
        display:none;
    }

    .arf_custom_checkbox_wrapper input[type="checkbox"]:checked + svg path#arfcheckbox_checked,
    .arf_custom_radio_wrapper input[type="radio"]:checked + svg path#arfradio_checked{
        display:block;
    }

    .arf_custom_checkbox_wrapper input[type="checkbox"]:checked + svg path,
    .arf_custom_radio_wrapper input[type="radio"]:checked + svg path{
        fill:#3f74e7;
    }
    .arf_radio_wrapper span label {
        font-family: Asap-Regular;
        font-size: 14px !important;
        color: #4e5462;
        margin:0px;
    }
    /*Custom Checkbox css end*/
    #show_link_type{
        width:100%;
        float:left;
    }
    #normal_link_type,.arffirst_div{
        float:left;
        width:255px;
    }
    .arfsecond_div{
        margin-left:25px;
        display:inline-block;
    }
    .arfminwidth30{
        min-width:40px !important;
    }
    .txtstandardnew{
        border: 1px solid #D5E3FF !important;
        border-radius: 3px;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        -o-border-radius: 3px;
        box-shadow: none !important;
        -webkit-box-shadow: none !important;
        -o-box-shadow: none !important;
        -moz-box-shadow: none !important;
        height: 30px;
        padding-left:11px;
    }
    
    .arf_bg_color{
        float:left;
    }
    .arfbgcolornote{
        display: inline-block;
        float: left;
        height: 40px;
        line-height: 40px;
        font-size: 14px;
        font-style: italic;
        width: 150px;
        color: #3f74e7;
    }
    .arfdiv{
        display: block;
        clear: both;
        float: left;
        margin-bottom: 8px !important;
    }
    .arfheight{
        display: inline-block;
        float: left;
        height: 40px;
        line-height: normal;
        font-size: 14px;
        font-style: italic;
        color: #3f74e7;
        margin-top: 10px;
        padding-left: 5px;
    }
    .arf_js_colorpicker{
            z-index: 100000 !important;
    }
    .arf_color_picker_input_div{
        float: left;
        padding-left: 130px;
        padding-top: 155px;
        width: 100%;
        z-index: 2147483647;
        box-sizing:border-box;
        -moz-box-sizing:border-box;
        -webkit-box-sizing:border-box;
        -o-box-sizing:border-box;
    }
    .color_input_hex_div{
        background-color: #c9c9c9;
        float: left;
        height: 22px !important;
        padding-left: 6px;
        padding-top: 3px;
        text-align: center;
        width: 21px !important;
    }
    .arf_add_favorite_color {
        float: left;
        height: 30px;
        position: absolute;
        width: 100%;
        line-height:30px;
        z-index:9999999999;
    }

    .arf_add_favorite_color_btn {
        cursor: pointer;
        float: right;
        height: 25px;
        line-height: normal;
        position: relative;
        right: 6px;
        top: 2px;
        width: 25px;
        color: #a9a9a9;
    }

    .arf_favorite_color_buttons {
        float: left;
        margin-left: 13px;
        width: auto;
    }

    .select_from_fav_color {
        border: 1px solid;
        float: left;
        height: 20px;
        margin-right: 5px;
        width: 20px;
        cursor:pointer;
    }

    .arf_add_favorite_color_btn i{
        font-size: 23px;
    }

    .arf_add_favorite_color_btn i:hover:before{
        content: "\f08a" !important;
    }
    .arf_color_picker_input,
    .arf_color_picker_input:focus
    {
        border: 1px solid #c9c9c9;
        float: left;
        height: 25px;
        margin: 0;
        width: 70px;
    }
    
    .arf_selectbox dd ul li:hover,
    .arf_hovered {
        background:#3f74e7;
        color:#FFFFFF !important;
    }
    body.rtl .arfdiv{
        float: right;
    }
    body.rtl .arfsecond_div{
        margin-left: unset;
        margin-right:25px;
    }

   .arf_coloroption_sub .wp-picker-container{
        position: relative;
        top: -65px;
    }
    #arf_btn_txtcolor_div .wp-picker-container{
        position: relative;
        top: -68px;
    }
    #arf_btn_bgcolor_div .wp-picker-container{
        position: relative;
        top: -68px;
    }

   .arf_coloroption_sub .button.wp-color-result span{
        display: none;    
    }

    .arf_coloroption_sub .arf_coloroption_subarrow_bg{
        position: relative;
        top: 30px;
    }
    #arf_btn_bgcolor .arf_coloroption_subarrow_bg{
        position: relative;
        top: 15px !important;   
    }
    #arf_btn_txtcolor .arf_coloroption_subarrow_bg{
        position: relative;
        top: 15px !important;   
    }

    .arf_coloroption_sub .wp-picker-container .wp-color-result.button{
        width: 100%;
    }
    .wp-picker-clear{
        display: none !important; 
    }

    .wp-picker-input-wrap{
        margin-top: -15px;
    }
    .wp-picker-holder{
        margin-top: -22px !important;
    }
    .wp-picker-holder .iris-picker{
        z-index: 1;
    }
                            
</style>
<div class='arf_modal_overlay'>
     <?php 
    $arf_element_show = false;
     if (defined('WPB_VC_VERSION')){
         if (version_compare(WPB_VC_VERSION, '4.6', '>=')) {
             $arf_element_show = true;
         }
     }
    ?>
    <input type="hidden" id="arf_element_trigger_event" value="<?php echo $arf_element_show; ?>" />
    <div class='arf_popup_container arf_insert_popup_modal' id="arf_insert_popup_modal">
        <div class='arf_popup_container_header'><?php echo addslashes(esc_html__('ADD ARFORMS FORM', 'ARForms'));?>        
        </div>
        <div class='arfinsertform_modal_container arf_popup_content_container'>
            <div class="main_div_container">
                <div class="select_form arfmarginb20">
                    <label><?php echo addslashes(esc_html__('Select a form to insert into page', 'ARForms')); ?>&nbsp;<span class="newmodal_required" style="color:#000000; vertical-align:top;">*</span></label>
                    <div class="selectbox">
                        <?php $arformhelper->forms_dropdown_new('arfaddformid', '', 'Select form') ?>
                    </div>
                </div>
                <input type="hidden" id="form_title_i" value="" />
                <div class="select_type arfmarginb20">
                    <label><?php echo addslashes(esc_html__('How you want to include this form into page?', 'ARForms')); ?></label>
                    <div class="radio_selection">
                        <div class="arf_radio_wrapper">
                            <div class="arf_custom_radio_div">
                                <div class="arf_custom_radio_wrapper">
                                    <input type="radio" class="arf_custom_radio" checked="checked" name="shortcode_type" value="normal" id="shortcode_type_normal" />
                                    <svg width="18px" height="18px">
                                        <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                        <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                    </svg>
                                </div>
                                <span>
                                    <label for="shortcode_type_normal" <?php if (is_rtl()) { echo 'style="float:right; margin-right:167px;"';}?>><?php echo addslashes(esc_html__('Internal', 'ARForms')); ?></label>
                                </span>
                            </div>
                        </div>
                        <div class="arf_radio_wrapper">
                            <div class="arf_custom_radio_div">
                                <div class="arf_custom_radio_wrapper">
                                    <input type="radio" class="arf_custom_radio arf_submit_entries" name="shortcode_type" value="popup" id="shortcode_type_popup" />
                                    <svg width="18px" height="18px">
                                        <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                        <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                    </svg>
                                </div>
                                <span>
                                    <label for="shortcode_type_popup" <?php if (is_rtl()) { echo 'style="float:right; margin-right:167px;"';} else { echo 'style="width:170px;"';}?>><?php echo addslashes(esc_html__('Modal (popup) window', 'ARForms')); ?></label>
                                </span>
                            </div>
                        </div>
                    </div>                    
                </div>
                <div id="show_link_type" style="display:none;">
                    <div class="arfdiv" style="margin-top:0px;">
                        <div class="arffirst_div">
                            <div id="normal_link_type">     
                                <label><?php echo addslashes(esc_html__('Modal Trigger Type', 'ARForms')); ?></label>
                                <div>
                                    <div class="dt_dl" style="<?php
                                    if (is_rtl()) {
                                        echo 'text-align:right;';
                                    } else {
                                        echo 'text-align:left;';
                                    }
                                    ?>">
                                    <input type="hidden" name="link_type" id="link_type" onchange="javascript:changetopposition(this.value);" value="link"/>
                                    <dl class="arf_selectbox" data-name="link_type" data-id="link_type" style="width:235px;">
                                        <dt>
                                        <span style="float:left;"><?php echo addslashes(esc_html__('On Click', 'ARForms')); ?></span>
                                        <input value="onclick" style="display:none;" class="" type="text">
                                        <i class="arfa arfa-caret-down arfa-lg"></i>
                                        </dt>
                                        <dd>
                                            <ul style="display:none;width:250px;" data-id="link_type">
                                                
                                                <li class="lblnotetitle arf_selectbox_option" data-value="onclick" data-label="<?php echo addslashes(esc_html__('On Click', 'ARForms')); ?>"><?php echo addslashes(esc_html__('On Click', 'ARForms')); ?></li>

                                                <li class="lblnotetitle arf_selectbox_option" data-value="onload" data-label="<?php echo addslashes(esc_html__('On Page Load', 'ARForms')); ?>"><?php echo addslashes(esc_html__('On Page Load', 'ARForms')); ?></li>
                                                <li class="lblnotetitle arf_selectbox_option" data-value="scroll" data-label="<?php echo addslashes(esc_html__('On Page Scroll', 'ARForms')); ?>"><?php echo addslashes(esc_html__('On Page Scroll', 'ARForms')); ?></li>

                                                <li class="lblnotetitle arf_selectbox_option" data-value="timer" data-label="<?php echo addslashes(esc_html__('On Timer(Scheduled)', 'ARForms')); ?>"><?php echo addslashes(esc_html__('On Timer(Scheduled)', 'ARForms')); ?></li>

                                                 <li class="lblnotetitle arf_selectbox_option" data-value="on_exit" data-label="<?php echo addslashes(esc_html__('On Exit(Exit Intent)', 'ARForms')); ?>"><?php echo addslashes(esc_html__('On Exit(Exit Intent)', 'ARForms')); ?></li>
                                                 <li class="lblnotetitle arf_selectbox_option" data-value="on_idle" data-label="<?php echo addslashes(esc_html__('On Idle', 'ARForms')); ?>"><?php echo addslashes(esc_html__('On Idle', 'ARForms')); ?></li>
                                            </ul>
                                        </dd>
                                    </dl>                            
                                </div>          
                            </div>
                        </div>
                        </div>
                        <div class="arfsecond_div">
                            <div class="" id="shortcode_caption">   
                                <label><?php echo addslashes(esc_html__('Caption :', 'ARForms')); ?></label>
                                <div class="">
                                    <input type="text" name="short_caption" id="short_caption" value="Click here to open Form" class="txtstandardnew" style="width:255px;" />
                                </div>          
                            </div>
                            <div class="" id="is_scroll" style="display:none;">
                                <label><?php echo addslashes(esc_html__('Open popup when user scroll % of page after page load', 'ARForms')); ?></label>                                
                                <div class="">
                                    <input type="text" name="open_scroll" id="open_scroll" value="10" class="txtstandardnew" style="width:65px;" />&nbsp; %
                                    <br>
                                    <span class="arfheight" style="float:none;"><?php echo addslashes(esc_html__('(eg. 100% - end of page)', 'ARForms')); ?></span>
                                </div>          
                            </div>
                            <div class="" id="is_delay" style="display:none;">  
                                <label><?php echo addslashes(esc_html__('Open popup after Time Interval of', 'ARForms')); ?></label>     
                                <div class="">
                                    <input type="text" name="open_delay" id="open_delay" value="0" class="txtstandardnew" style="width:65px;" />
                                    <span class="" style="float:none;margin-left:10px;"><?php echo addslashes(esc_html__('(in seconds)', 'ARForms')); ?></span>
                                </div>          
                            </div>
                        </div>
                    </div>

                    <div class="arfdiv" id="list_of_onclick">
                        <div class="arffirst_div" style="width: 100%">
                            <label><?php echo addslashes(esc_html__('Click Types', 'ARForms')); ?></label>     
                            <div class="radio_selection ">
                               
                               <div class="arf_radio_wrapper">
                                   <div class="arf_custom_radio_div">
                                       <div class="arf_custom_radio_wrapper">
                                           <input type="radio" class="arf_custom_radio" checked="checked" name="onclick_type" value="link" id="onclick_type_link" />
                                           <svg width="18px" height="18px">
                                           <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                           <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                           </svg>
                                       </div>
                                       <span>
                                           <label for="onclick_type_link" <?php if (is_rtl()) { echo 'style="float:right; margin-right:167px;"';}?>><?php echo addslashes(esc_html__('Link', 'ARForms')); ?></label>
                                       </span>
                                   </div>
                               </div>

                               <div class="arf_radio_wrapper">
                                   <div class="arf_custom_radio_div">
                                       <div class="arf_custom_radio_wrapper">
                                           <input type="radio" class="arf_custom_radio arf_submit_entries" name="onclick_type" value="button" id="onclick_type_button" />
                                           <svg width="18px" height="18px">
                                           <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                           <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                           </svg>
                                       </div>
                                       <span>
                                           <label for="onclick_type_button" <?php if (is_rtl()) { echo 'style="float:right; margin-right:167px;"';}?>><?php echo addslashes(esc_html__('Button', 'ARForms')); ?></label>
                                       </span>
                                   </div>
                               </div>

                               <div class="arf_radio_wrapper">
                                   <div class="arf_custom_radio_div">
                                       <div class="arf_custom_radio_wrapper">
                                           <input type="radio" class="arf_custom_radio" name="onclick_type" value="sticky" id="onclick_type_sticky" />
                                           <svg width="18px" height="18px">
                                           <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                           <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                           </svg>
                                       </div>
                                       <span>
                                           <label for="onclick_type_sticky" <?php if (is_rtl()) { echo 'style="float:right; margin-right:167px;"';}?>><?php echo addslashes(esc_html__('Sticky', 'ARForms')); ?></label>
                                       </span>
                                   </div>
                               </div>

                                <div class="arf_radio_wrapper">
                                   <div class="arf_custom_radio_div">
                                       <div class="arf_custom_radio_wrapper">
                                           <input type="radio" class="arf_custom_radio" name="onclick_type" value="fly" id="onclick_type_fly" />
                                           <svg width="18px" height="18px">
                                           <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                           <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                           </svg>
                                       </div>
                                       <span>
                                       <label for="onclick_type_fly" <?php if (is_rtl()) { echo 'style="float:right; margin-right:167px;"';}?>><?php echo addslashes(esc_html__('Fly (Sidebar)', 'ARForms')); ?></label>
                                       </span>
                                   </div>
                               </div>

                           </div>  
                        </div>
                    </div>

                    <div class="arfdiv" id="overlay_div" style="margin-bottom: 20px !important; clear: both;" >
                        <div class="arffirst_div">
                            <label><?php echo addslashes(esc_html__('Background Overlay :', 'ARForms')); ?></label>
                            <div class="dt_dl arf_bg_color" style="<?php
                                if (is_rtl()) {
                                    echo 'text-align:right;';
                                } else {
                                    echo 'text-align:left;';
                                }
                                ?>">
                                <input type="hidden" name="overlay" id="overlay" value="0.6"/>
                                <dl class="arf_selectbox" data-name="overlay" data-id="overlay" style="width:85px;">
                                    <dt>
                                    <span style="float:left;"><?php echo addslashes(esc_html__('60%', 'ARForms')); ?></span>
                                    <input value="0.6" style="display:none;" class="" type="text">
                                    <i class="arfa arfa-caret-down arfa-lg"></i>
                                    </dt>
                                    <dd>
                                        <ul style="display:none;width:100px;" data-id="overlay">
                                            <li class="lblnotetitle arf_selectbox_option" data-value="0" data-label="<?php echo addslashes(esc_html__('0 (None)', 'ARForms')); ?>"><?php echo addslashes(esc_html__('0 (None)', 'ARForms')); ?></li>
                                            <li class="lblnotetitle arf_selectbox_option" data-value="0.1" data-label="<?php echo addslashes(esc_html__('10%', 'ARForms')); ?>"><?php echo addslashes(esc_html__('10%', 'ARForms')); ?></li>
                                            <li class="lblnotetitle arf_selectbox_option" data-value="0.2" data-label="<?php echo addslashes(esc_html__('20%', 'ARForms')); ?>"><?php echo addslashes(esc_html__('20%', 'ARForms')); ?></li>
                                            <li class="lblnotetitle arf_selectbox_option" data-value="0.3" data-label="<?php echo addslashes(esc_html__('30%', 'ARForms')); ?>"><?php echo addslashes(esc_html__('30%', 'ARForms')); ?></li>
                                            <li class="lblnotetitle arf_selectbox_option" data-value="0.4" data-label="<?php echo addslashes(esc_html__('40%', 'ARForms')); ?>"><?php echo addslashes(esc_html__('40%', 'ARForms')); ?></li>
                                            <li class="lblnotetitle arf_selectbox_option" data-value="0.5" data-label="<?php echo addslashes(esc_html__('50%', 'ARForms')); ?>"><?php echo addslashes(esc_html__('50%', 'ARForms')); ?></li>
                                            <li class="lblnotetitle arf_selectbox_option" data-value="0.6" data-label="<?php echo addslashes(esc_html__('60%', 'ARForms')); ?>"><?php echo addslashes(esc_html__('60%', 'ARForms')); ?></li>
                                            <li class="lblnotetitle arf_selectbox_option" data-value="0.7" data-label="<?php echo addslashes(esc_html__('70%', 'ARForms')); ?>"><?php echo addslashes(esc_html__('70%', 'ARForms')); ?></li>
                                            <li class="lblnotetitle arf_selectbox_option" data-value="0.8" data-label="<?php echo addslashes(esc_html__('80%', 'ARForms')); ?>"><?php echo addslashes(esc_html__('80%', 'ARForms')); ?></li>
                                            <li class="lblnotetitle arf_selectbox_option" data-value="0.9" data-label="<?php echo addslashes(esc_html__('90%', 'ARForms')); ?>"><?php echo addslashes(esc_html__('90%', 'ARForms')); ?></li>
                                            <li class="lblnotetitle arf_selectbox_option" data-value="1" data-label="<?php echo addslashes(esc_html__('100%', 'ARForms')); ?>"><?php echo addslashes(esc_html__('100%', 'ARForms')); ?></li>
                                        </ul>
                                    </dd>
                                </dl>  
                            </div>

                            

                            <div style="display: inline-block; float:left;" class="arf_coloroption_sub">   
                                
                                <div class="arf_coloroption_subarrow_bg">
                                    <div class="arf_coloroption_subarrow"></div>
                                </div>
                                <div class="arfbgcolornote" style="margin-top:25px;">(<?php echo addslashes(esc_html__('Background Color', 'ARForms')); ?>)</div>
                                
                                <input type="hidden" name="arf_modal_bg_color" id="arf_modal_bg_color" class="txtmodal1" value="#000000" />
                            </div>
                        </div>
                            
                        <div class="arfsecond_div" id="is_close_link_div">
                            <label><?php echo addslashes(esc_html__('Show Close Button :', 'ARForms')); ?></label>
                            <div class="radio_selection ">
                                <div class="arf_radio_wrapper arfminwidth30">
                                   <div class="arf_custom_radio_div">
                                       <div class="arf_custom_radio_wrapper">
                                           <input type="radio" class="arf_custom_radio" checked="checked" name="show_close_link" value="yes" id="show_close_link_yes" />
                                           <svg width="18px" height="18px">
                                               <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                               <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                           </svg>
                                       </div>
                                       <span>
                                           <label for="show_close_link_yes" <?php if (is_rtl()) { echo 'style="float:right; margin-right:167px;"';}?>><?php echo addslashes(esc_html__('Yes', 'ARForms')); ?></label>
                                       </span>
                                   </div>
                                </div>
                                <div class="arf_radio_wrapper arfminwidth30">
                                   <div class="arf_custom_radio_div">
                                       <div class="arf_custom_radio_wrapper">
                                           <input type="radio" class="arf_custom_radio arf_submit_entries" name="show_close_link" value="no" id="show_close_link_no" />
                                           <svg width="18px" height="18px">
                                               <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                               <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                           </svg>
                                       </div>
                                       <span>
                                           <label for="show_close_link_no" <?php if (is_rtl()) { echo 'style="float:right; margin-right:167px;"';}?>><?php echo addslashes(esc_html__('No', 'ARForms')); ?></label>
                                       </span>
                                   </div>
                                </div>

                           </div>                        
                        </div>
                    </div>
                    <div class="arfdiv">
                        <div class="arffirst_div">
                            <label><?php echo addslashes(esc_html__('Size :', 'ARForms')); ?></label>
                            <div class="height_setting" style="display:none;float: left;">
                                <input type="text" onkeyup="if(jQuery(this).val() == 'auto') {jQuery('span#arf_modal_height_px').hide();}else{ jQuery('span#arf_modal_height_px').show();}" class="txtstandardnew" name="modal_height" id="modal_height" value="auto" style="width:70px;" />&nbsp;<span class="arf_px" id="arf_modal_height_px" style="display: none;"><?php echo addslashes(esc_html__('px', 'ARForms')); ?></span><br/>
                                <div class="arfheight"><?php echo addslashes(esc_html__('Height', 'ARForms')); ?></div>
                            </div>
                            <div class="height_setting" style="display: inline; float: none;margin-left:10px;">
                                <input type="text" class="txtstandardnew" name="modal_width" id="modal_width" value="800" style="width:70px;" />&nbsp;
                                <span class="arf_px"><?php echo addslashes(esc_html__('px', 'ARForms')); ?></span><br/>
                                <div class="arfheight" style="width:122px;line-height:normal;margin-left:10px;"><?php echo addslashes(esc_html__('Width', 'ARForms')); ?> &nbsp; 
                                    <span>(<?php echo addslashes(esc_html__('Form width will be overwritten', 'ARForms')); ?>)</span>
                                </div>
                            </div>
                        </div>
                        <div class="arfsecond_div" id="arfmodalbuttonstyles" style="display:none;">
                            <label><?php echo addslashes(esc_html__('Colors :', 'ARForms')); ?></label>
                                <div style="display:inline">
                                    <div class="height_setting arf_coloroption_sub" id="arf_btn_bgcolor_div" style="display:inline;float:left;" >
                                        <div style="display: inline-block;margin-left: 0px;" id="arf_btn_bgcolor" class="">
                                            
                                            <div class="arf_coloroption_subarrow_bg">
                                                <div class="arf_coloroption_subarrow"></div>
                                            </div>
                                        </div>
                                        <div class="arfheight" style="position:relative;top:8px;"><?php echo addslashes(esc_html__('Button Background', 'ARForms')); ?></div>
                                        <input type="hidden" name="arf_modal_btn_bg_color" id="arf_modal_btn_bg_color" class="txtmodal1" value="#808080" />
                                    </div>
                                    <div class="height_setting arf_coloroption_sub" id="arf_btn_txtcolor_div" style="display:inline;float:left;">
                                        <div style="display: inline-block;margin-left: 0px;" id="arf_btn_txtcolor" class="">
                                            
                                            <div class="arf_coloroption_subarrow_bg">
                                                <div class="arf_coloroption_subarrow"></div>
                                            </div>
                                        </div>
                                        <div class="arfheight" style="position:relative;top:8px;"><?php echo addslashes(esc_html__('Button Text', 'ARForms')); ?></div>
                                        <input type="hidden" name="arf_modal_btn_txt_color" id="arf_modal_btn_txt_color" class="txtmodal1" value="#FFFFFF" />
                                    </div>
                                </div>                                       
                        </div>
                    </div>
                    <div class="arfdiv">
                        <div class="arffirst_div" >
                          <div id="is_sticky" style="display:none;">
                               <label><?php echo addslashes(esc_html__('Link Position?', 'ARForms')); ?></label>
                               <div class="dt_dl arf_bg_color" style="<?php
                                if (is_rtl()) {
                                    echo 'text-align:right;';
                                } else {
                                    echo 'text-align:left;';
                                }
                                ?>">
                            <input type="hidden" name="link_position" id="link_position" value="top"/>
                                <dl class="arf_selectbox" data-name="link_position" data-id="link_position" style="width:235px;">
                                    <dt>
                                    <span style="float:left;"><?php echo addslashes(esc_html__('Top', 'ARForms')); ?></span>
                                    <input value="top" style="display:none;" class="" type="text">
                                    <i class="arfa arfa-caret-down arfa-lg"></i>
                                    </dt>
                                    <dd>
                                        <ul style="display:none;width:250px;" data-id="link_position">
                                            <li class="lblnotetitle arf_selectbox_option" data-value="top" data-label="<?php echo addslashes(esc_html__('Top', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Top', 'ARForms')); ?></li>
                                            <li class="lblnotetitle arf_selectbox_option" data-value="bottom" data-label="<?php echo addslashes(esc_html__('Bottom', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Bottom', 'ARForms')); ?></li>
                                            <li class="lblnotetitle arf_selectbox_option" data-value="left" data-label="<?php echo addslashes(esc_html__('Left', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Left', 'ARForms')); ?></li>
                                            <li class="lblnotetitle arf_selectbox_option" data-value="right" data-label="<?php echo addslashes(esc_html__('Right', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Right', 'ARForms')); ?></li>                                        
                                        </ul>
                                    </dd>
                                </dl>  
                            </div> 
                          </div>                        
                          <div id="is_fly" style="display:none;">
                               <label><?php echo addslashes(esc_html__('Link Position?', 'ARForms')); ?></label>
                               <div class="dt_dl arf_bg_color" style="<?php
                                if (is_rtl()) {
                                    echo 'text-align:right;';
                                } else {
                                    echo 'text-align:left;';
                                }
                                ?>">
                            <input type="hidden" name="link_position_fly" id="link_position_fly" value="left" onchange="arfchangeflybtn();"/>
                                <dl class="arf_selectbox" data-name="link_position_fly" data-id="link_position_fly" style="width:235px;">
                                    <dt>
                                    <span style="float:left;"><?php echo addslashes(esc_html__('Left', 'ARForms')); ?></span>
                                    <input value="top" style="display:none;" class="" type="text">
                                    <i class="arfa arfa-caret-down arfa-lg"></i>
                                    </dt>
                                    <dd>
                                        <ul style="display:none;width:250px;" data-id="link_position_fly">
                                            <li class="lblnotetitle arf_selectbox_option" data-value="left" data-label="<?php echo addslashes(esc_html__('Left', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Left', 'ARForms')); ?></li>
                                            <li class="lblnotetitle arf_selectbox_option" data-value="right" data-label="<?php echo addslashes(esc_html__('Right', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Right', 'ARForms')); ?></li>                                                                        
                                        </ul>
                                    </dd>
                                </dl>  
                            </div> 
                          </div>
                                
                          
                                                
                        </div>
                        <div class="arfsecond_div">
                            <div id="button_angle_div" style="display:none;">
                                <label><?php echo addslashes(esc_html__('Button angle :', 'ARForms')); ?></label>
                                <div class="dt_dl arf_bg_color" style="<?php
                                    if (is_rtl()) {
                                        echo 'text-align:right;';
                                    } else {
                                        echo 'text-align:left;';
                                    }
                                    ?>">
                                <input type="hidden" name="button_angle" id="button_angle" value="0" onchange="changeflybutton();"/>
                                    <dl class="arf_selectbox" data-name="overlay" data-id="button_angle" style="width:85px;">
                                        <dt>
                                        <span style="float:left;"><?php echo addslashes(esc_html__('0', 'ARForms')); ?></span>
                                        <input value="0.6" style="display:none;" class="" type="text">
                                        <i class="arfa arfa-caret-down arfa-lg"></i>
                                        </dt>
                                        <dd>
                                            <ul style="display:none;width:100px;" data-id="button_angle">
                                                <li class="lblnotetitle arf_selectbox_option" data-value="0" data-label="<?php echo addslashes(esc_html__('0', 'ARForms')); ?>"><?php echo addslashes(esc_html__('0', 'ARForms')); ?></li>
                                                <li class="lblnotetitle arf_selectbox_option" data-value="90" data-label="<?php echo addslashes(esc_html__('90', 'ARForms')); ?>"><?php echo addslashes(esc_html__('90', 'ARForms')); ?></li>
                                                <li class="lblnotetitle arf_selectbox_option" data-value="-90" data-label="<?php echo addslashes(esc_html__('-90', 'ARForms')); ?>"><?php echo addslashes(esc_html__('-90', 'ARForms')); ?></li>
                                            </ul>
                                        </dd>
                                    </dl>  
                                </div> 
                            </div>                           
                        </div>
                    </div>

                    <div class="arfdiv">
                        <div class="arffirst_div" id="arf_full_screen_modal">
                            <label><?php echo addslashes(esc_html__('Show Full Screen Popup :', 'ARForms')); ?></label>
                            <div class="radio_selection ">
                               <div class="arf_radio_wrapper arfminwidth30">
                                   <div class="arf_custom_radio_div">
                                       <div class="arf_custom_radio_wrapper">
                                           <input type="radio" class="arf_custom_radio" name="show_full_screen" value="yes" id="show_full_screen_yes" />
                                           <svg width="18px" height="18px">
                                           <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                           <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                           </svg>
                                       </div>
                                       <span>
                                           <label for="show_full_screen_yes" <?php if (is_rtl()) { echo 'style="float:right; margin-right:167px;"';}?>><?php echo addslashes(esc_html__('Yes', 'ARForms')); ?></label>
                                       </span>
                                   </div>
                               </div>
                               <div class="arf_radio_wrapper arfminwidth30">
                                   <div class="arf_custom_radio_div">
                                       <div class="arf_custom_radio_wrapper">
                                           <input type="radio" class="arf_custom_radio arf_submit_entries" checked="checked" name="show_full_screen" value="no" id="show_full_screen_no" />
                                           <svg width="18px" height="18px">
                                           <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
                                           <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
                                           </svg>
                                       </div>
                                       <span>
                                           <label for="show_full_screen_no" <?php if (is_rtl()) { echo 'style="float:right; margin-right:167px;"';}?>><?php echo addslashes(esc_html__('No', 'ARForms')); ?></label>
                                       </span>
                                   </div>
                               </div>
                            </div>                        
                        </div>

                        <div class="arfsecond_div" id="modal_effect_div">
                            <label><?php echo addslashes(esc_html__('Animation Effect', 'ARForms')); ?></label>
                            <div class="dt_dl" id="" style="<?php
                                if (is_rtl()) {
                                    echo 'text-align:right;';
                                } else {
                                    echo 'text-align:left;';
                                }
                                ?>">
                                <input type="hidden" name="modal_effect" id="modal_effect" value="fade_in" onchange=""/>
                                <dl class="arf_selectbox" data-name="overlay" data-id="modal_effect" style="width:135px;">
                                    <dt>
                                    <span style="float:left;"><?php echo addslashes(esc_html__('Fade-in', 'ARForms')); ?></span>
                                    <input value="fade_in" style="display:none;" class="" type="text">
                                    <i class="arfa arfa-caret-down arfa-lg"></i>
                                    </dt>
                                    <dd>
                                        <ul style="display:none;width:151px;" data-id="modal_effect">
                                            <li class="lblnotetitle arf_selectbox_option" data-value="no_animation" data-label="<?php echo addslashes(esc_html__('No Animation','ARForms')); ?>"><?php echo addslashes(esc_html__('No Animation','ARForms')); ?></li>
                                            
                                            <li class="lblnotetitle arf_selectbox_option" data-value="fade_in" data-label="<?php echo addslashes(esc_html__('Fade-in', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Fade-in', 'ARForms')); ?></li>
                                            
                                            <li class="lblnotetitle arf_selectbox_option" data-value="slide_in_top" data-label="<?php echo addslashes(esc_html__('Slide In Top', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Slide In Top', 'ARForms')); ?></li>
                                            
                                            <li class="lblnotetitle arf_selectbox_option" data-value="slide_in_bottom" data-label="<?php echo addslashes(esc_html__('Slide In Bottom', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Slide In Bottom', 'ARForms')); ?></li>
                                            
                                            <li class="lblnotetitle arf_selectbox_option" data-value="slide_in_right" data-label="<?php echo addslashes(esc_html__('Slide In Right', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Slide In Right', 'ARForms')); ?></li>

                                            <li class="lblnotetitle arf_selectbox_option" data-value="slide_in_left" data-label="<?php echo addslashes(esc_html__('Slide In Left', 'ARForms')); ?>"><?php echo addslashes(esc_html__('Slide In Left', 'ARForms')); ?></li>
                                            
                                            <li class="lblnotetitle arf_selectbox_option" data-value="zoom_in" data-label="<?php echo addslashes(esc_html__('Zoom In','ARForms')); ?>"><?php echo addslashes(esc_html__('Zoom In','ARForms')); ?></li>

                                        </ul>
                                    </dd>
                                </dl>  
                            </div>          
                        </div>
                    </div>

                    <div class="arfdiv">
                        <div class="arffirst_div">
                        <div id="ideal_time" style="display:none;">     
                            <label><?php echo addslashes(esc_html__('show after user is inactive for', 'ARForms')); ?></label>     
                            <div class="">
                                <input type="text" name="inact_time" id="inact_time" value="1" class="txtstandardnew" style="width:65px;" />
                                <span class="" style="float:none;margin-left:10px;"><?php echo addslashes(esc_html__(' Minutes', 'ARForms')); ?></span>
                            </div>          
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="arf_popup_container_footer">
            <button type="button" class="arf_field_option_close_button" onclick="arf_close_field_option_popup();"><?php echo addslashes(esc_html__('Cancel', 'ARForms')); ?></button>
            <button type="button" class="arf_field_option_submit_button" id="arfcontinuebtn" onclick="arfinsertform();"><?php echo addslashes(esc_html__('Add to page', 'ARForms')); ?></button>            
        </div>
    </div>
</div>