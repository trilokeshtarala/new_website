if( typeof window.arf_vc_clicked == 'undefined' ){
    window.arf_vc_clicked = false;
}
function hasClass(el, className) {
    if (el == null) return false;
    if (el.classList) return el.classList.contains(className)
    else return !!el.className.match(new RegExp('(\\s|^)' + className + '(\\s|$)'))
}
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

jQuery(document).ready(function () {
        
        jQuery('#arf_vc_modal_bg_color_input').wpColorPicker();

        jQuery('#arf_vc_modal_btn_bg_color_input').wpColorPicker();

        jQuery('#arf_vc_modal_btn_txt_color_input').wpColorPicker();

});
     
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
        if (arf_cookies != '') arf_fav_colors = arf_cookies.split(',');
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
        }
        else {
            jQuery('.arf_favourite_color').append('<div class="arf_fav_color_list" data-column="' + column + '" data-colpick-id="' + colpick_id + '" data-color="' + item + '" style="background-color:' + item + '"></div>');
        }
    });
}



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

        jQuery('[data-fid="' + colpick_id + '"]').colpickSetColor(color);
        setTimeout(function () {
            jQuery('div[data-fid="' + colpick_id + '"]').parent().find('input#' + colpick_id).trigger('change');
        }, 100);

        e.preventDefault();
    });
    jQuery(document).on('click', '.select_from_fav_color', function(e) {
        var $this = jQuery(this);
        var color = $this.attr('value');
        jQuery("#arf_color_picker_input").val(color.replace('#',''));
        var id = $this.parent().parent().find('.arf_add_favorite_color_btn').attr('data-value');
        jQuery(".arf_coloroption[data-fid='" + id + "']").css('background-color',color);
        
        jQuery("#" + id).val(color).trigger('change');
        var elm = jQuery("#" +id ).parent('.arf_coloroption_sub').find('.arf_coloroption')[0];            

      
    });
        jQuery(document).on('click', '#arf_add_favorite_color_btn', function(e) {
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
            if (favorite_colors !== undefined && favorite_colors !== '') {
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
	
     
    jQuery('.ARForms_Popup_Shortode_arfield').each(function () {
        var fild_value = jQuery(this).val();
        var fild_name = jQuery(this).attr('id');


        if (fild_name == 'id') {
            jQuery('#arfaddformid_vc_popup option[value="' + fild_value + '"]').prop('selected', true);
            jQuery('input#Arf_param_id').val(fild_value);
        }

        if (fild_name == 'shortcode_type') {
            if (fild_value == 'normal') {
                jQuery('#shortcode_type_normal_vc').attr('checked', true);
                jQuery('#show_link_inner').slideDown();
                jQuery('#show_link_type_vc').slideUp(700);
                jQuery("#arf_shortcode_type").val(fild_value);
            }
            if (fild_value == 'popup') {
                jQuery('#shortcode_type_popup_vc').attr('checked', true);
                jQuery('#show_link_inner').slideUp();
                jQuery('#show_link_type_vc').slideDown(700);
                jQuery("#arf_shortcode_type").val(fild_value);
            }
        }

        if (fild_name == 'type') {
            jQuery('#link_type_vc option[value="' + fild_value + '"]').prop('selected', true);
            arf_set_link_type_data(fild_value);
        }

        if (fild_name == 'position') {
            jQuery('select#link_position_vc').find('option').each(function () {
                if (jQuery(this).attr('value') == fild_value)
                    jQuery(this).attr('selected', true);
                else
                    jQuery(this).attr('selected', false);
            });
        }

        if (fild_name == 'desc') {
            jQuery("input#short_caption").val(fild_value);
        }
        if (fild_name == 'width') {
            jQuery("input#modal_width").val(fild_value);
        }
        if (fild_name == 'height') {
            jQuery("input#modal_height").val(fild_value);
            if(fild_value == 'auto'){
                jQuery('span#arf_vc_height_px').hide();
            }else{
                jQuery('span#arf_vc_height_px').show();
            }
            
        }


        if (fild_name == 'angle') {
            jQuery('#button_angle option[value="' + fild_value + '"]').prop('selected', true);
        }

        if (fild_name == 'bgcolor' || fild_name == 'txtcolor') {
           
        }

        if (fild_name == 'bgcolor') {
            jQuery('.arf_coloroption[data-fid="arf_vc_modal_btn_bg_color"]').css('background', fild_value);
            jQuery("input#arf_vc_modal_btn_bg_color_input").val(fild_value);
        }

        if (fild_name == 'txtcolor') {
            jQuery('.arf_coloroption[data-fid="arf_vc_modal_btn_txt_color"]').css('background', fild_value);
            jQuery("input#arf_vc_modal_btn_txt_color_input").val(fild_value);
        }

        if (fild_name == 'on_inactivity') {
            jQuery("input#open_inactivity").val(fild_value);
        }

        if (fild_name == 'on_scroll') {
            jQuery("input#open_scroll").val(fild_value);
        }

        if (fild_name == 'on_delay') {
            jQuery("input#open_delay").val(fild_value);
        }
        if (fild_name == 'overlay') {
            jQuery('#overlay option[value="' + fild_value + '"]').prop('selected', true);
        }

        if (fild_name == 'is_close_link') {
            if (fild_value == 'yes') {
                jQuery('#show_close_link_yes_vc').attr('checked', true);
            }
            if (fild_value == 'no') {
                jQuery('#show_close_link_no_vc').attr('checked', true);
            }
        }

        if (fild_name == 'modal_bgcolor') {
            jQuery('.arf_coloroption[data-fid="arf_vc_modal_bg_color"]').css('background', fild_value);
            jQuery("input#arf_vc_modal_bg_color_input").val(fild_value);
        }

        if (fild_name == 'inactive_min') {
            jQuery("input#inact_time").val(fild_value);
        }

        if (fild_name == 'is_fullscreen') {
            if (fild_value == 'yes') {
                jQuery('#show_full_screen_yes').attr('checked', true);
            }
            if (fild_value == 'no') {
                jQuery('#show_full_screen_no').attr('checked', true);
            }
        }
        
        if (fild_name == 'modaleffect') {
            jQuery("input#modal_effect").val(fild_value);
        }

    });

    jQuery(document).on('click','.vc_general[data-vc-ui-element="button-save"]',function(e){
        if( typeof window.arf_vc_clicked == 'undefined' || window.arf_vc_clicked == false ){
            window.arf_vc_clicked = true;
            //var form_id = jQuery("#arfaddformid_vc_popup").val() || 0;
            var form_id = document.getElementById('arfaddformid_vc_popup').value;
            if( typeof form_id == 'undefined' ){
                form_id = '';
            }
            if (form_id == '' || form_id == '0' ) {
                alert(__BLANK_FORM_MSG);
                return false;
            }
        } else {
            window.arf_vc_clicked = false;
        }
    });


    jQuery('#shortcode_type_popup_vc').click(function () {
        jQuery('#show_link_inner').slideUp();
        jQuery('#show_link_type_vc').slideDown(700);
        jQuery("#arf_shortcode_type").val(jQuery(this).val());
    });
    jQuery('#shortcode_type_normal_vc').click(function () {
        jQuery('#show_link_inner').slideDown();
        jQuery('#show_link_type_vc').slideUp(700);
        jQuery("#arf_shortcode_type").val(jQuery(this).val());
    });


    jQuery('#link_type_vc').change(function () {
        var show_link_type = jQuery('#link_type_vc').val();
        arf_set_link_type_data(show_link_type);
    });

    jQuery('#arfaddformid_vc').change(function () {
        var arformid = jQuery(this).val();
        if (arformid) {
            jQuery(".wpb_vc_param_value").val(arformid);
        }
    });

    jQuery('#arfaddformid_vc_popup').change(function () {
        var arformid = jQuery(this).val();
        if (arformid) {
            jQuery("#Arf_param_id").val(arformid);
        }
    });

     
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

            jQuery('[data-fid="' + colpick_id + '"]').colpickSetColor(color);
            setTimeout(function () {
                jQuery('div[data-fid="' + colpick_id + '"]').parent().find('input#' + colpick_id).trigger('change');
            }, 100);

            e.preventDefault();
        });
        jQuery(document).on('click', '.select_from_fav_color', function (e) {
            var $this = jQuery(this);
            var color = $this.attr('value');

            var id = $this.parent().parent().find('.arf_add_favorite_color_btn').attr('data-value');

            jQuery(".arf_custom_color_popup_picker[data-fid='" + id + "']").css('background-color', color);
            jQuery("#" + id).val(color).trigger('change');
            var elm = jQuery("#" + id)[0];

      
        });
        jQuery(document).on('click', '#arf_add_favorite_color_btn', function (e) {
            var $this = jQuery(this);
            var $id = $this.attr('data-value');

            var $color = jQuery("#"+$id).attr('value');
            var $color2 = jQuery("#"+$id).text();
            if( $color.toLowerCase() != $color2.toLowerCase() ){
                $color = $color2;
            }
            if ($color === undefined)
                return;
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
            } else if (colors.length == 0) {
                var current = new Date();
                current.setMonth(current.getMonth() + 1);
                document.cookie = 'arf_fav_color[colors]=' + $color + '; expires=' + current.toGMTString();
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
    
    jQuery(document).on('click','input[name="onclick_type"]',function(){
        var lin_type = jQuery('input[name="onclick_type"]:checked').val();
        lin_type = (lin_type != '')?lin_type:'link';
        jQuery('#link_type_vc').val(lin_type);
        jQuery('#link_type_vc').trigger('change');
    });

    jQuery(document).on('click','input[name="_is_fullscreen"]',function(){
        var fulls = jQuery('input[name="_is_fullscreen"]:checked').val();
        jQuery('#is_fullscreen_id').val(fulls);
    });


function changeflybutton()
{
    var angle = jQuery('#button_angle').val();
    angle = angle != '' ? angle : 0;
    jQuery('.arf_fly_btn').css('transform', 'rotate(' + angle + 'deg)');
}
function arfchangeflybtn()
{
    if (jQuery('#link_position_fly').val() == 'right') {
        jQuery('.arfbtnleft').hide();
        jQuery('.arfbtnright').show();
    } else {
        jQuery('.arfbtnleft').show();
        jQuery('.arfbtnright').hide();
    }
}




function changetopposition(myval) {
    var modalheight = jQuery(window).height();
    var top_height = Number(modalheight) / 2;

    if (myval == "fly")
        jQuery('#arfinsertform').css('top', (top_height - 230) + 'px');
    else
        jQuery('#arfinsertform').css('top', (top_height - 180) + 'px');
}


function arf_set_link_type_data(show_link_type) {

    var radio_link_type= jQuery('input[name="onclick_type"]:checked').val();
        radio_link_type = (show_link_type == 'onclick')?show_link_type = radio_link_type:'';

    var tid = jQuery('.arfmodal_vcfields #arf_btn_txtcolor .arf_coloroption').attr('data-fid');
    jQuery('#' + tid).val('#ffffff');

    var link_sticky_html = '';
    var link_fly_html = '';

    var top_label = (typeof __LINK_POSITION_TOP !== undefined) ? __LINK_POSITION_TOP : 'Top';
    var bottom_label = (typeof __LINK_POSITION_BOTTOM !== undefined) ? __LINK_POSITION_BOTTOM : 'Bottom';
    var left_label = (typeof __LINK_POSITION_LEFT !== undefined) ? __LINK_POSITION_LEFT : 'Left';
    var right_label = (typeof __LINK_POSITION_RIGHT !== undefined) ? __LINK_POSITION_RIGHT : 'Right';

    link_sticky_html += '<li class="lblnotetitle arf_selectbox_option" data-value="top" data-label="Top">Top</li>';
    link_sticky_html += '<li class="lblnotetitle arf_selectbox_option" data-value="bottom" data-label="Bottom">Bottom</li>';
    link_sticky_html += '<li class="lblnotetitle arf_selectbox_option" data-value="left" data-label="Left">Left</li>';
    link_sticky_html += '<li class="lblnotetitle arf_selectbox_option" data-value="right" data-label="Right">Right</li>';

    link_fly_html += '<li class="lblnotetitle arf_selectbox_option" data-value="left" data-label="Left">Left</li>';
    link_fly_html += '<li class="lblnotetitle arf_selectbox_option" data-value="right" data-label="Right">Right</li>';

    if (show_link_type == 'sticky')
    {
        jQuery('#link_position_vc').next('dl').children('dd').children('ul').html(link_sticky_html);
	    jQuery('#link_position_vc').next('dl').children('dt').children('span').html('Top');
	    jQuery('#link_position_vc').val('top');
        jQuery('#button_angle_div_vc').slideUp();
        jQuery('#is_scroll_vc').slideUp();
        jQuery('#overlay_div_vc').slideUp();
        jQuery('#is_close_link_div_vc').slideUp();
        jQuery('modal_effect_div').slideUp();
        jQuery('#arf_full_screen_modal').slideUp();
        jQuery(".arfmodal_vcfields #arf_btn_bgcolor .arf_coloroption").css('background', '#93979d');
        var fid = jQuery('.arfmodal_vcfields #arf_btn_bgcolor .arf_coloroption').attr('data-fid');
        jQuery('#' + fid).val('#93979d');
        arfreinilizecolorpicker('#93979d',fid);
        jQuery('.arfmodal_vcfields#arfmodalbuttonstyles').slideDown();
        jQuery('#is_sticky_vc').slideDown();
        jQuery('#list_of_onclick_vc').slideDown();
        jQuery('#modal_height').parent().slideDown();
        jQuery('#show_link_type_vc #ideal_time').slideUp();
    }
    else if (show_link_type == 'fly')
    {
        jQuery('#is_sticky_vc').slideDown();
    	jQuery('#link_position_vc').next('dl').children('dd').children('ul').html(link_fly_html);
    	jQuery('#link_position_vc').next('dl').children('dt').children('span').html('Left');
    	jQuery('#link_position_vc').val('left');
        jQuery('#button_angle_div_vc').slideDown();
        jQuery('.arfmodal_vcfields#arfmodalbuttonstyles').slideDown();
        jQuery(".arfmodal_vcfields #arf_btn_bgcolor .arf_coloroption").css('background', '#2d6dae');
        var fid = jQuery('.arfmodal_vcfields #arf_btn_bgcolor .arf_coloroption').attr('data-fid');
        jQuery('#' + fid).val('#2d6dae');
        arfreinilizecolorpicker('#2d6dae',fid);
        jQuery('#is_scroll_vc').slideUp();
        jQuery('#overlay_div_vc').slideUp();
        jQuery('#is_close_link_div_vc').slideUp();
        jQuery('#list_of_onclick_vc').slideDown();
        jQuery('modal_effect_div').slideUp();
        jQuery('#arf_full_screen_modal').slideUp();
        jQuery('#modal_height').parent().slideUp();
        jQuery('#show_link_type_vc #ideal_time').slideUp();
    } else if (show_link_type == 'scroll') {
        jQuery('#is_sticky_vc').slideUp();
        jQuery('#is_fly_vc').slideUp();
        jQuery('#button_angle_div_vc').slideUp();
        jQuery('.arfmodal_vcfields#arfmodalbuttonstyles').slideUp();
        jQuery('#is_scroll_vc').slideDown();
        jQuery('#overlay_div_vc').slideDown();
        jQuery('#is_close_link_div_vc').slideDown();
        jQuery('#shortcode_caption_vc').slideUp();
        jQuery('#list_of_onclick_vc').slideUp();
        jQuery('modal_effect_div').slideDown();
        jQuery('#arf_full_screen_modal').slideDown();
        jQuery('#modal_height').parent().slideUp();
        jQuery('#show_link_type_vc #ideal_time').slideUp();

    } else if (show_link_type == 'link') {
        jQuery('#is_sticky_vc').slideUp();
        jQuery('#is_fly_vc').slideUp();
        jQuery('#button_angle_div_vc').slideUp();
        jQuery('.arfmodal_vcfields#arfmodalbuttonstyles').slideUp();
        jQuery('#is_scroll_vc').slideUp();
        jQuery('#modal_height').parent().slideUp();
        jQuery('#shortcode_caption_vc').slideDown();
        jQuery('#overlay_div_vc').slideDown();
        jQuery('#is_close_link_div_vc').slideDown();
        jQuery('#list_of_onclick_vc').slideDown();
        jQuery('modal_effect_div').slideDown();
        jQuery('#arf_full_screen_modal').slideDown();
        jQuery('#show_link_type_vc #ideal_time').slideUp();

    } else if (show_link_type == 'button') {
        jQuery('#is_sticky_vc').slideUp();
        jQuery('#is_fly_vc').slideUp();
        jQuery('#button_angle_div_vc').slideUp();
        jQuery('.arfmodal_vcfields#arfmodalbuttonstyles').slideDown();
        jQuery(".arfmodal_vcfields #arf_btn_bgcolor .arf_coloroption").css('background', '#808080');
        var fid = jQuery('.arfmodal_vcfields #arf_btn_bgcolor .arf_coloroption').attr('data-fid');
        jQuery('#' + fid).val('#808080');
        arfreinilizecolorpicker('#808080',fid);
        jQuery('#is_scroll_vc').slideUp();
        jQuery('#overlay_div_vc').slideDown();
        jQuery('#is_close_link_div_vc').slideDown();
        jQuery('#list_of_onclick_vc').slideDown();
        jQuery('modal_effect_div').slideDown();
        jQuery('#arf_full_screen_modal').slideDown();
        jQuery('#modal_height').parent().slideUp();
        jQuery('#show_link_type_vc #ideal_time').slideUp();

    } else if(show_link_type == 'timer'){
        jQuery('#is_delay_vc').slideDown();
        jQuery('#overlay_div_vc').slideDown();
        jQuery('#is_close_link_div_vc').slideDown();
        jQuery('#shortcode_caption_vc').slideUp();
        jQuery('#list_of_onclick_vc').slideUp();
        jQuery('#is_scroll_vc').slideUp();
        jQuery('#button_angle_div_vc').slideUp();
        jQuery('.arfmodal_vcfields#arfmodalbuttonstyles').slideUp();
        jQuery('modal_effect_div').slideDown();
        jQuery('#arf_full_screen_modal').slideDown();
        jQuery('#modal_height').parent().slideUp();
        jQuery('#show_link_type_vc #ideal_time').slideUp();

    } else if(show_link_type == "on_exit"){
        jQuery('#is_delay_vc').slideUp();
        jQuery('#list_of_onclick_vc').slideUp();
        jQuery('#shortcode_caption_vc').slideUp(); 
        jQuery('#is_scroll_vc').slideUp();
        jQuery('#button_angle_div_vc').slideUp();
        jQuery('.arfmodal_vcfields#arfmodalbuttonstyles').slideUp();
        jQuery('#modal_height').parent().slideUp();
        jQuery('#overlay_div_vc').slideDown();
        jQuery('#is_close_link_div_vc').slideDown();
        jQuery('modal_effect_div').slideDown();
        jQuery('#arf_full_screen_modal').slideDown();
        jQuery('#show_link_type_vc #ideal_time').slideUp();

    } else if(show_link_type == "on_idle"){
        jQuery('#is_delay_vc').slideUp();
        jQuery('#overlay_div_vc').slideDown();
        jQuery('#is_close_link_div_vc').slideDown();
        jQuery('#list_of_onclick_vc').slideUp();
        jQuery('#shortcode_caption_vc').slideUp(); 
        jQuery('#is_scroll_vc').slideUp();
        jQuery('#button_angle_div_vc').slideUp();
        jQuery('.arfmodal_vcfields#arfmodalbuttonstyles').slideUp();
        jQuery('modal_effect_div').slideDown();
        jQuery('#arf_full_screen_modal').slideDown();
        jQuery('#is_sticky_vc').slideUp();
        jQuery('#modal_height').parent().slideUp();
        jQuery('#show_link_type_vc #ideal_time').slideDown();

    }else if (show_link_type == 'onload') {
        jQuery('#is_delay_vc').slideUp();
        jQuery('#shortcode_caption_vc').slideUp();
        jQuery('#list_of_onclick_vc').slideUp();
        jQuery('#modal_height').parent().slideUp();
        jQuery('#is_sticky_vc').slideUp();
        jQuery('.arfmodal_vcfields#arfmodalbuttonstyles').slideUp();
        jQuery('#overlay_div_vc').slideDown();
        jQuery('#is_close_link_div_vc').slideDown();
        jQuery('modal_effect_div').slideDown();
        jQuery('#arf_full_screen_modal').slideDown();
        jQuery('#show_link_type_vc #ideal_time').slideUp();
    }else {
        jQuery('#is_sticky_vc').slideUp();
        jQuery('#button_angle_div_vc').slideUp();
        jQuery('.arfmodal_vcfields#arfmodalbuttonstyles').slideUp();
        jQuery('#is_scroll_vc').slideUp();
        jQuery('#overlay_div_vc').slideUp();
        jQuery('#is_close_link_div_vc').slideUp();
        jQuery('#list_of_onclick_vc').slideDown();
        jQuery('#shortcode_caption_vc').slideDown();
        jQuery('#modal_height').parent().slideUp();
        jQuery('#show_link_type_vc #ideal_time').slideUp();
    }

}

function showarfpopupfieldlist()
{
    var fild_value = jQuery('input[name="shortcode_type"]:checked').val();
    var fild_name = 'shortcode_type';

    if (fild_name == 'id') {
        jQuery('#arfaddformid_vc_popup option[value="' + fild_value + '"]').prop('selected', true);
        jQuery('input#Arf_param_id').val(fild_value);
    }

    if (fild_name == 'shortcode_type') {
        if (fild_value == 'normal') {
            jQuery('#shortcode_type_normal_vc').attr('checked', true);
            jQuery('#show_link_inner').slideDown();
            jQuery('#show_link_type_vc').slideUp();
            jQuery("#arf_shortcode_type").val(fild_value);
        }
        if (fild_value == 'popup') {
            jQuery('#shortcode_type_popup_vc').attr('checked', true);
            jQuery('#show_link_inner').slideUp();
            jQuery('#show_link_type_vc').slideDown();
            jQuery("#arf_shortcode_type").val(fild_value);

        }
    }

}

function set_arfaddformid_vc_popup(id)
{
    if (id) {
        jQuery("#Arf_param_id").val(id);
    }
}

jQuery('#link_position_fly').change(function () {
    var position = jQuery(this).val();

    var color = (position == 'left') ? '#2d6dae' : '#8ccf7a';

    jQuery(".arfmodal_vcfields #arf_btn_bgcolor .arf_coloroption").css('background', color);
    var fid = jQuery('.arfmodal_vcfields #arf_btn_bgcolor .arf_coloroption').attr('data-fid');
    jQuery('#' + fid).val(color);
    arfreinilizecolorpicker(color,fid);


});



jQuery('#link_position_vc').change(function () {
    var position = jQuery(this).val();
    var color = (['left', 'right', 'bottom'].indexOf(position) > -1) ? '#1bbae1' : '#93979d';

    jQuery(".arfmodal_vcfields #arf_btn_bgcolor .arf_coloroptions").css('background', color);
    var fid = jQuery('.arfmodal_vcfields #arf_btn_bgcolor .arf_coloroption').attr('data-fid');
    jQuery('#' + fid).val(color);
    arfreinilizecolorpicker(color,fid);
});

function is_close_link_change() {
    var fild_value = jQuery('input[name="is_close_link_vc"]:checked').val();
    if (fild_value) {
        jQuery("input#is_close_link_value").val(fild_value);
    }
}

function arfreinilizecolorpicker(color_code, id){
    var color = color_code;
    jQuery("#arf_color_picker_input").val(color.replace('#',''));
    jQuery(".arf_coloroption[data-fid='" + id + "']").css('background-color',color);            
    jQuery("#" + id).val(color).trigger('change');
    var elm = jQuery("#" +id ).parent('.arf_coloroption_sub').find('.arf_coloroption')[0];            
   
   
}