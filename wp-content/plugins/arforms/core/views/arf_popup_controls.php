<?php
  function arf_get_display_popup_option($post_type = 'post',$values ){
    $link_type = isset($values['arf_popup_'.$post_type.'_link_type']) ? $values['arf_popup_'.$post_type.'_link_type'] : 'onclick';


    $link_type_arr = array(
      'onclick' => addslashes(esc_html__('On Click','ARForms')),
      'onload' => addslashes(esc_html__('On Page Load','ARForms')),
      'scroll' => addslashes(esc_html__('On Page Scroll','ARForms')),
      'timer' => addslashes(esc_html__('On Timer(Scheduled)','ARForms')),
      'on_exit' => addslashes(esc_html__('On Exit(Exit Intent)','ARForms')),
      'on_idle' => addslashes(esc_html__('On Idle','ARForms'))
    );

    $caption_text = isset($values['arf_popup_'.$post_type.'_caption']) ? $values['arf_popup_'.$post_type.'_caption'] : 'Click here to open form';
    $click_type = isset($values['arf_popup_'.$post_type.'_click_type']) ? $values['arf_popup_'.$post_type.'_click_type'] : 'sticky';

    $overlay_arr = array(
      "0" => addslashes(esc_html__('0 (None)','ARForms') ),
      "0.1" => addslashes(esc_html__('10%','ARForms') ),
      "0.2" => addslashes(esc_html__('20%','ARForms') ),
      "0.3" => addslashes(esc_html__('30%','ARForms') ),
      "0.4" => addslashes(esc_html__('40%','ARForms') ),
      "0.5" => addslashes(esc_html__('50%','ARForms') ),
      "0.6" => addslashes(esc_html__('60%','ARForms') ),
      "0.7" => addslashes(esc_html__('70%','ARForms') ),
      "0.8" => addslashes(esc_html__('80%','ARForms') ),
      "0.9" => addslashes(esc_html__('90%','ARForms') ),
      "1" => addslashes(esc_html__('100%','ARForms') ),
    );
    $overlay_val = isset($values['arf_popup_'.$post_type.'_overlay']) ? $values['arf_popup_'.$post_type.'_overlay'] : '0.6';

    $overlay_col = isset($values['arf_popup_'.$post_type.'_model_bgcolor']) ? $values['arf_popup_'.$post_type.'_model_bgcolor'] : '#000000';

    $show_close = isset($values['arf_popup_'.$post_type.'_show_close']) ? $values['arf_popup_'.$post_type.'_show_close'] : 'yes';
    $model_height = isset($values['arf_popup_'.$post_type.'_model_height']) ? $values['arf_popup_'.$post_type.'_model_height'] : 'auto';

    $link_position = isset($values['arf_popup_'.$post_type.'_link_position']) ? $values['arf_popup_'.$post_type.'_link_position'] : 'top';

    $link_position_arr = array(
      'top' => addslashes(esc_html__('Top','ARForms')),
      'bottom' => addslashes(esc_html__('Bottom','ARForms')),
      'left' => addslashes(esc_html__('Left','ARForms')),
      'right' => addslashes(esc_html__('Right','ARForms')),
    );

    $link_position_fly = isset($values['arf_popup_'.$post_type.'_link_position_fly']) ? $values['arf_popup_'.$post_type.'_link_position_fly'] : 'left';

    $link_position_fly_arr = array(
      'left' => addslashes(esc_html__('Left','ARForms')),
      'right' => addslashes(esc_html__('Right','ARForms'))
    );

    $button_angle = isset($values['arf_popup_'.$post_type.'_button_angle']) ? $values['arf_popup_'.$post_type.'_button_angle'] : '0';

    $button_angle_arr = array(
      '0' => addslashes(esc_html__('0', 'ARForms')),
      '90' => addslashes(esc_html__('90','ARForms')),
      '-90' => addslashes(esc_html__('-90','ARForms')),
    );

    $show_full_screen = isset($values['arf_popup_'.$post_type.'_show_full_screen']) ? $values['arf_popup_'.$post_type.'_show_full_screen'] : 'no';
    $model_effect = isset($values['arf_popup_'.$post_type.'_model_effect']) ? $values['arf_popup_'.$post_type.'_model_effect'] : 'fade_in';

    $model_effect_arr = array(
      'no_animation' => addslashes(esc_html__('No Animation','ARForms')),
      'fade_in' => addslashes(esc_html__('Fade-in','ARForms')),
      'slide_in_top' => addslashes(esc_html__('Slide In Top','ARForms')),
      'slide_in_bottom' => addslashes(esc_html__('Slide In Bottom','ARForms')),
      'slide_in_right' => addslashes(esc_html__('Slide In Right','ARForms')),
      'slide_in_left' => addslashes(esc_html__('Slide In Left','ARForms')),
      'zoom_in' => addslashes(esc_html__('Zoom In','ARForms'))
    );

    $inactive_time = isset($values['arf_popup_'.$post_type.'_inactive_time']) ? $values['arf_popup_'.$post_type.'_inactive_time'] : '1';
    $open_scroll = isset($values['arf_popup_'.$post_type.'_open_scroll']) ? $values['arf_popup_'.$post_type.'_open_scroll'] : '10';
    $open_delay = isset($values['arf_popup_'.$post_type.'_open_delay']) ? $values['arf_popup_'.$post_type.'_open_delay'] : '0';

    $btn_bgcolor = isset($values['arf_popup_'.$post_type.'_modal_btn_bg_color']) ? $values['arf_popup_'.$post_type.'_modal_btn_bg_color'] : '#808080';
    $btn_txcolor = isset($values['arf_popup_'.$post_type.'_modal_btn_txt_color']) ? $values['arf_popup_'.$post_type.'_modal_btn_txt_color'] : '#FFFFFF';

    $exclude_post_type = isset($values['arf_popup_'.$post_type.'_exclude']) ? $values['arf_popup_'.$post_type.'_exclude'] : array();

    $arguments = array(
    	'posts_per_page' => -1,
      	'sort_order' 	 => 'asc',
      	'sort_column' 	 => 'post_title',
      	'hierarchical' 	 => 1,
      	'exclude' 		 => '',
      	'include' 		 => '',
      	'meta_key' 		 => '',
      	'meta_value' 	 => '',
      	'authors' 		 => '',
      	'child_of' 		 => 0,
      	'parent' 		 => -1,
      	'exclude_tree' 	 => '',
      	'number' 		 => '',
      	'offset' 		 => 0,
      	'post_type' 	 => $post_type,
      	'post_status' 	 => 'publish'
    ); 
    $post_lists = get_posts($arguments);
?>
    <div class="arf_sitewide_popup_inner_container">
    	<div class="site_wide_popup_form_row">
    		<div class="site_wide_popup_label">
    			<label id="sitewide_popup_size_<?php echo $post_type; ?>"><?php esc_html_e('Popup width','ARForms'); ?></label>
    		</div>
    		<div class="site_wide_popup_input">
                <input type="text" class="arf_large_input_box arf_sitewide_popup_<?php echo $post_type; ?>_width" name="<?php echo "options[arf_popup_{$post_type}_model_width]"; ?>" id="modal_width" value="800" style="width:70px;" />&nbsp;<span class="arf_px" id="arf_modal_height_px"><?php esc_html_e('px','ARForms'); ?></span>
    		</div>
    		<div class="site_wide_popup_input_help">
    			<div class="arf_sitewide_popup_width_text">(<?php esc_html_e('Form width will be overwritten','ARForms'); ?>)</div>
    		</div>
    	</div>

    	<div class="site_wide_popup_form_row">
    		<div class="site_wide_popup_label">
    			<label id="sitewide_popup_trigger_type_<?php echo $post_type; ?>"><?php esc_html_e('Modal trigger type','ARForms'); ?></label>
    		</div>
    		<div class="site_wide_popup_input">
    			<div class="dt_dl <?php echo "arf_trigger_type_dd_".$post_type; ?>" style="<?php if (is_rtl()) { echo 'text-align:right;'; } else { echo 'text-align:left;'; } ?>">
                    <input onchange="model_trigger_type('<?php echo $post_type; ?>',this.value,'<?php echo $post_type.'_link_type'; ?>');" type="hidden" name="<?php echo "options[arf_popup_".$post_type."_link_type]" ?>" id="<?php echo $post_type.'_link_type'; ?>" value="<?php echo $link_type; ?>" />
                    <dl class="arf_selectbox" data-name="link_type" data-id="<?php echo $post_type.'_link_type'; ?>" style="width:250px;">
                        <dt>
                        <span style="float:left;"><?php echo $link_type_arr[$link_type]; ?></span>
                        <input value="<?php echo $link_type; ?>" style="display:none;" class="" type="text">
                        <i class="arfa arfa-caret-down arfa-lg"></i>
                        </dt>
                        <dd>
                            <ul style="display:none;width:266px;" data-id="<?php echo $post_type.'_link_type'; ?>">
                                <?php
                                  foreach( $link_type_arr as $link_type => $link_label ){
                                ?>
                                  <li class="lblnotetitle arf_selectbox_option" data-value="<?php echo $link_type; ?>" data-label="<?php echo $link_label; ?>"><?php echo $link_label; ?></li>
                                <?php
                                  }
                                ?>
                            </ul>
                        </dd>
                    </dl>                            
                </div>
    		</div>
    	</div>

    	<div class="site_wide_popup_form_row site_wide_popup_sub_options" id="<?php echo $post_type.'_shortcode_caption'; ?>">
    		<div class="site_wide_popup_label">
    			<label><?php esc_html_e('Caption','ARForms'); ?></label>
    		</div>
    		<div class="site_wide_popup_input">
    			<input type="text" name="<?php echo "options[arf_popup_".$post_type."_caption]" ?>" id="<?php echo $post_type.'_short_caption'; ?>" value="<?php echo $caption_text; ?>" class="arf_large_input_box" style="width:255px;" />
    		</div>
    	</div>

    	<div class="site_wide_popup_form_row site_wide_popup_sub_options" id="<?php echo $post_type.'_is_scroll'; ?>">
    		<div class="site_wide_popup_label">
    			<label><?php echo addslashes(esc_html__('Open popup when user scroll % of page after page load','ARForms') ); ?></label>
    		</div>
    		<div class="site_wide_popup_input">
				<input type="text" name="<?php echo 'options[arf_popup_'.$post_type.'_open_scroll]'; ?>" id="<?php echo $post_type.'_is_scroll_input'; ?>" value="<?php echo $open_scroll; ?>" class="arf_large_input_box" style="width:65px;" />&nbsp; %
				<br/>
				<span class="arfbgcolornote arf_popup_scroll_note"><?php echo addslashes(esc_html__('(eg. 100% - end of page)', 'ARForms')); ?></span>
    		</div>
    	</div>

    	<div class="site_wide_popup_form_row site_wide_popup_sub_options" id="<?php echo $post_type.'_is_delay'; ?>">
    		<div class="site_wide_popup_label">
    			<label><?php echo addslashes(esc_html__('Open popup after Time Interval of','ARForms') ); ?></label>
    		</div>
    		<div class="site_wide_popup_input">
				<input type="text" name="<?php echo "options[arf_popup_".$post_type."_open_delay]"; ?>" id="<?php echo $post_type.'_is_delay_input'; ?>" value="<?php echo $open_delay; ?>" class="arf_large_input_box" style="width:65px;" />
				<span class="arfbgcolornote arf_popup_timer_note"><?php echo addslashes(esc_html__('(in seconds)', 'ARForms')); ?></span>
    		</div>
    	</div>

    	<div class="site_wide_popup_form_row site_wide_popup_sub_options" id="<?php echo $post_type.'_list_of_onclick'; ?>">
    		<div class="site_wide_popup_label">
    			<label><?php echo addslashes(esc_html__('Click Types','ARForms')); ?></label>
    		</div>
    		<div class="site_wide_popup_input">
				<div class="radio_selection">
					<div class="arf_radio_wrapper">
						<div class="arf_custom_radio_div">
							<div class="arf_custom_radio_wrapper">
								<input type="radio" class="arf_custom_radio" name="<?php echo "options[arf_popup_".$post_type."_click_type]"; ?>" <?php checked($click_type,'sticky'); ?> value="sticky" onchange="model_trigger_type('<?php echo $post_type; ?>','onclick');" id="<?php echo $post_type.'_onclick_type_sticky'; ?>" />
								<svg width="18px" height="18px">
									<?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
									<?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
								</svg>
							</div>
							<span style="position: relative;top:-2px;">
								<label for="<?php echo $post_type.'_onclick_type_sticky'; ?>"><?php echo addslashes(esc_html__('Sticky', 'ARForms')); ?></label>
							</span>
						</div>
					</div>
					<div class="arf_radio_wrapper">
						<div class="arf_custom_radio_div">
							<div class="arf_custom_radio_wrapper">
								<input type="radio" class="arf_custom_radio" onchange="model_trigger_type('<?php echo $post_type; ?>','onclick');" name="<?php echo "options[arf_popup_".$post_type."_click_type]"; ?>" <?php checked($click_type,'fly'); ?> value="fly" id="<?php echo $post_type.'_onclick_type_fly'; ?>" />
								<svg width="18px" height="18px">
									<?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
									<?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
								</svg>
							</div>
							<span style="position: relative;top:-2px;">
								<label for="<?php echo $post_type.'_onclick_type_fly'; ?>"><?php echo addslashes(esc_html__('Fly (Sidebar)', 'ARForms')); ?></label>
							</span>
						</div>
					</div>
				</div>
    		</div>
    	</div>

    	<div class="site_wide_popup_form_row site_wide_popup_sub_options" id="<?php echo $post_type.'_modal_height'; ?>">
    		<div class="site_wide_popup_label">
    			<label><?php esc_html_e('Popup height','ARForms'); ?></label>
    		</div>
    		<div class="site_wide_popup_input">
    			<input type="text" class="arf_large_input_box arf_sitewide_popup_height arf_sitewide_popup_<?php echo $post_type; ?>_height" name="<?php echo "options[arf_popup_{$post_type}_model_height]" ?>" id="modal_height" value="auto" style="width:60px;" >&nbsp;<span class="arf_px" id="arf_modal_height_px"><?php esc_html_e('px','ARForms'); ?></span>
    		</div>
    	</div>

    	<div class="site_wide_popup_form_row site_wide_popup_sub_options" id="<?php echo $post_type.'_is_sticky'; ?>">
    		<div class="site_wide_popup_label">
    			<label><?php echo esc_html__('Link Position','ARForms'); ?></label>
    		</div>
    		<div class="site_wide_popup_input">
				<div class="dt_dl arf_bg_color arf_sticky_link_position_dd" style="<?php if (is_rtl()) { echo 'text-align:right;'; } else { echo 'text-align:left;'; } ?>">
					<input type="hidden" name="<?php echo 'options[arf_popup_'.$post_type.'_link_position]'; ?>" id="<?php echo 'arf_popup_'.$post_type.'_link_position'; ?>" value="<?php echo $link_position; ?>"/>
					<dl class="arf_selectbox" data-name="<?php echo 'options[arf_popup_'.$post_type.'_link_position]'; ?>" data-id="<?php echo 'arf_popup_'.$post_type.'_link_position'; ?>" style="width:250px;">
						<dt>
							<span style="float:left;"><?php echo $link_position_arr[$link_position]; ?></span>
							<input value="top" style="display:none;" class="" type="text">
							<i class="arfa arfa-caret-down arfa-lg"></i>
						</dt>
						<dd>
							<ul style="display:none;width:266px;" data-id="<?php echo 'arf_popup_'.$post_type.'_link_position'; ?>">
								<?php foreach( $link_position_arr as $link_position_k => $link_position_v ){ ?>
								<li class="lblnotetitle arf_selectbox_option" data-value="<?php echo $link_position_k; ?>" data-label="<?php echo $link_position_v; ?>"><?php echo $link_position_v; ?></li>
								<?php } ?>
							</ul>
						</dd>
					</dl>  
				</div>
    		</div>
    	</div>

    	<div class="site_wide_popup_form_row site_wide_popup_sub_options" id="<?php echo $post_type.'_is_fly'; ?>">
    		<div class="site_wide_popup_label">
    			<label><?php echo esc_html__('Link Position','ARForms'); ?></label>
    		</div>
    		<div class="site_wide_popup_input">
				<div class="dt_dl arf_bg_color arf_fly_link_position_dd" style="<?php if (is_rtl()) { echo 'text-align:right;'; } else { echo 'text-align:left;'; } ?>">
					<input type="hidden" name="<?php echo 'options[arf_popup_'.$post_type.'_link_position_fly]'; ?>" id="<?php echo 'arf_popup_'.$post_type.'_link_position_fly'; ?>" value="<?php echo $link_position_fly; ?>"/>
					<dl class="arf_selectbox" data-name="<?php echo 'options[arf_popup_'.$post_type.'_link_position_fly]'; ?>" data-id="<?php echo 'arf_popup_'.$post_type.'_link_position_fly'; ?>" style="width:250px;">
						<dt>
							<span style="float:left;"><?php echo $link_position_fly_arr[$link_position_fly]; ?></span>
							<input value="top" style="display:none;" class="" type="text">
							<i class="arfa arfa-caret-down arfa-lg"></i>
						</dt>
						<dd>
							<ul style="display:none;width:266px;" data-id="<?php echo 'arf_popup_'.$post_type.'_link_position_fly'; ?>">
								<?php foreach( $link_position_fly_arr as $fly_link_pos_k => $fly_link_pos_v){ ?>
									<li class="lblnotetitle arf_selectbox_option" data-value="<?php echo $fly_link_pos_k; ?>" data-label="<?php echo $fly_link_pos_v; ?>"><?php echo $fly_link_pos_v; ?></li>
								<?php } ?>
							</ul>
						</dd>
					</dl>
				</div>
    		</div>
    	</div>

    	<div class="site_wide_popup_form_row site_wide_popup_sub_options" id="<?php echo $post_type.'_button_angle_div'; ?>">
    		<div class="site_wide_popup_label">
    			<label><?php esc_html_e('Button Angle','ARForms'); ?></label>
    		</div>
    		<div class="site_wide_popup_input">
				<div class="dt_dl arf_bg_color arf_btn_angle_dd" style="<?php if (is_rtl()) { echo 'text-align:right;'; } else { echo 'text-align:left;'; } ?>">
					<input type="hidden" name="<?php echo 'options[arf_popup_'.$post_type.'_button_angle]'; ?>" id="<?php echo 'arf_popup_'.$post_type.'_button_angle'; ?>" value="<?php echo $button_angle; ?>" />
					<dl class="arf_selectbox" data-name="overlay" data-id="<?php echo 'arf_popup_'.$post_type.'_button_angle'; ?>" style="width:100px;">
						<dt>
							<span style="float:left;"><?php echo $button_angle_arr[$button_angle]; ?></span>
							<input value="0" style="display:none;" class="" type="text">
							<i class="arfa arfa-caret-down arfa-lg"></i>
						</dt>
						<dd>
							<ul style="display:none;width:116px;" data-id="<?php echo 'arf_popup_'.$post_type.'_button_angle'; ?>">
								<?php foreach($button_angle_arr as $btn_angle_k => $btn_angle_v){ ?>
									<li class="lblnotetitle arf_selectbox_option" data-value="<?php echo $btn_angle_k; ?>" data-label="<?php echo $btn_angle_v; ?>"><?php echo $btn_angle_v; ?></li>
								<?php } ?>
							</ul>
						</dd>
					</dl>  
				</div>
    		</div>
    	</div>

    	<div class="site_wide_popup_form_row site_wide_popup_sub_options" id="<?php echo $post_type.'_overlay_div'; ?>">
    		<div class="site_wide_popup_label">
    			<label><?php echo addslashes(esc_html__('Background Overlay','ARForms')); ?></label>
    		</div>
    		<div class="site_wide_popup_input">
    			<div class="dt_dl arf_bg_overlay_dd_container arf_bg_color <?php echo "arf_bg_overlay_dd_".$post_type; ?>" style="<?php if (is_rtl()) { echo 'text-align:right;'; } else { echo 'text-align:left;'; } ?> ">                  
                  	<input type="hidden" name="<?php echo "options[arf_popup_".$post_type."_overlay]"; ?>" id="<?php echo $post_type."_overlay"; ?>" value="<?php echo $overlay_val; ?>" />
                  	<dl class="arf_selectbox" data-name="<?php echo "options[arf_popup_".$post_type."_overlay]"; ?>" data-id="<?php echo $post_type."_overlay"; ?>" style="width:100px;">
                    	<dt>
                      		<span style="float:left;"><?php echo $overlay_arr[$overlay_val]; ?></span>
                      		<input value="0.6" style="display:none;" class="" type="text">
                      		<i class="arfa arfa-caret-down arfa-lg"></i>
                      	</dt>
                      	<dd>
                        	<ul style="display:none;width:116px;" data-id="<?php echo $post_type."_overlay"; ?>">
	                            <?php foreach( $overlay_arr as $overlay_v => $overlay_l ){ ?>
    	                          	<li class="lblnotetitle arf_selectbox_option" data-value="<?php echo $overlay_v; ?>" data-label="<?php echo $overlay_l; ?>"><?php echo $overlay_l; ?></li>
                            	<?php } ?>
                          	</ul>
                      	</dd>
                  	</dl>  
              	</div>              

              	<div style="display: inline-block;" class="arf_bg_overlay_container arf_coloroption_sub">   
                	<div class="arf_coloroption_subarrow_bg arf_custom_color_popup_picker jscolor" data-fid="<?php echo "arf_popup_".$post_type."_model_bgcolor"; ?>" style="background:<?php echo str_replace('##', '#', $overlay_col); ?>;" data-default-color="<?php echo str_replace('##', '#', $overlay_col); ?>" jscolor-hash="true" jscolor-onfinechange="arf_update_color(this,'<?php echo "arf_popup_".$post_type."_model_bgcolor"; ?>')" jscolor-valueelement="<?php echo "arf_popup_".$post_type."_model_bgcolor"; ?>"></div>

                  	<input type="hidden" name="<?php echo "options[arf_popup_".$post_type."_model_bgcolor]"; ?>" id="<?php echo "arf_popup_".$post_type."_model_bgcolor"; ?>" class="txtmodal1 arf_sitewide_popup_color" value="<?php echo $overlay_col; ?>" />
                  	
                  	<div class="arfbgcolornote">(<?php echo addslashes(esc_html__('Background Color', 'ARForms')); ?>)</div>
              	</div>
    		</div>
    	</div>

    	<div class="site_wide_popup_form_row site_wide_popup_sub_options" id="<?php echo $post_type.'_is_close_link_div'; ?>">
    		<div class="site_wide_popup_label">
    			<label><?php echo addslashes(esc_html__('Show Close Button','ARForms')); ?></label>
    		</div>
    		<div class="site_wide_popup_input">
				<div class="radio_selection ">
					<div class="arf_radio_wrapper arfminwidth30">
						<div class="arf_custom_radio_div">
							<div class="arf_custom_radio_wrapper">
								<input type="radio" class="arf_custom_radio" checked="checked" <?php checked($show_close,'yes'); ?> name="<?php echo "options[arf_popup_".$post_type."_show_close]"; ?>" value="yes" id="show_close_link_yes" />
								<svg width="18px" height="18px">
									<?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
									<?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
								</svg>
							</div>
							<span>
								<label for="show_close_link_yes" ><?php echo addslashes(esc_html__('Yes', 'ARForms')); ?></label>
							</span>
						</div>
					</div>
					<div class="arf_radio_wrapper arfminwidth30">
						<div class="arf_custom_radio_div">
							<div class="arf_custom_radio_wrapper">
								<input type="radio" class="arf_custom_radio arf_submit_entries" <?php checked($show_close,'no'); ?> name="<?php echo "options[arf_popup_".$post_type."_show_close]"; ?>" value="no" id="show_close_link_no" />
								<svg width="18px" height="18px">
									<?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
									<?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
								</svg>
							</div>
							<span>
								<label for="show_close_link_no"><?php echo addslashes(esc_html__('No', 'ARForms')); ?></label>
							</span>
						</div>
					</div>
				</div>
    		</div>
    	</div>

    	<div class="site_wide_popup_form_row site_wide_popup_sub_options" id="<?php echo $post_type.'_arfmodalbuttonstyles'; ?>">
    		<div class="site_wide_popup_label">
    			<label><?php echo addslashes(esc_html__('Colors','ARForms')); ?></label>
    		</div>
    		<div class="site_wide_popup_input">
    			<div class="height_setting arf_coloroption_sub" id="arf_btn_bgcolor_div" style="display:inline;" >
					<div style="display: inline-block;margin-left: 0px;" id="arf_btn_bgcolor" class="">
						<div class="arf_coloroption_subarrow_bg arf_custom_color_popup_picker jscolor" data-fid="<?php echo "arf_popup_".$post_type."_modal_btn_bg_color"; ?>" style="background:<?php echo str_replace('##', '#', $btn_bgcolor); ?>;margin:0;" data-default-color="<?php echo str_replace('##', '#', $btn_bgcolor); ?>" jscolor-hash="true" jscolor-onfinechange="arf_update_color(this,'<?php echo "arf_popup_".$post_type."_modal_btn_bg_color"; ?>')" jscolor-valueelement="<?php echo "arf_popup_".$post_type."_modal_btn_bg_color"; ?>"></div>

					</div>
					<input type="hidden" name="<?php echo "options[arf_popup_".$post_type."_modal_btn_bg_color]"; ?>" id="<?php echo "arf_popup_".$post_type."_modal_btn_bg_color"; ?>" class="txtmodal1 arf_sitewide_popup_color" value="<?php echo $btn_bgcolor; ?>" />
					<div class="arfbgcolornote" style="float:none;left:0;position:relative;top:0px;"><?php echo addslashes(esc_html__('Button Background', 'ARForms')); ?></div>
				</div>
				<div class="height_setting arf_coloroption_sub" id="arf_btn_txtcolor_div" style="display:inline;">
					<div style="display: inline-block;margin-left: 0px;" id="arf_btn_txtcolor" class="">
				  		<div class="arf_coloroption_subarrow_bg arf_custom_color_popup_picker jscolor" data-fid="<?php echo "arf_popup_".$post_type."_modal_btn_txt_color"; ?>" style="background:<?php echo str_replace('##', '#', $btn_txcolor); ?>;" data-default-color="<?php echo str_replace('##', '#', $btn_txcolor); ?>" jscolor-hash="true" jscolor-onfinechange="arf_update_color(this,'<?php echo "arf_popup_".$post_type."_modal_btn_txt_color"; ?>')" jscolor-valueelement="<?php echo "arf_popup_".$post_type."_modal_btn_txt_color"; ?>"></div>
					</div>
					<input type="hidden" name="<?php echo "options[arf_popup_".$post_type."_modal_btn_txt_color"; ?>" id="<?php echo "arf_popup_".$post_type."_modal_btn_txt_color"; ?>" class="txtmodal1 arf_sitewide_popup_color" value="<?php echo $btn_txcolor; ?>" />
					<div class="arfbgcolornote arf_popup_btn_text_container"><?php echo addslashes(esc_html__('Button Text', 'ARForms')); ?></div>
				</div>
    		</div>
    	</div>

    	<div class="site_wide_popup_form_row site_wide_popup_sub_options" id="<?php echo $post_type.'_arf_full_screen_modal' ?>">
    		<div class="site_wide_popup_label">
    			<label><?php echo addslashes(esc_html__('Show Full screen popup','ARForms')); ?></label>
    		</div>
    		<div class="site_wide_popup_input">
    			<div class="radio_selection">
	                <div class="arf_radio_wrapper arfminwidth30">
	                    <div class="arf_custom_radio_div">
	                        <div class="arf_custom_radio_wrapper">
	                            <input type="radio" class="arf_custom_radio" <?php checked($show_full_screen,'yes'); ?> name="<?php echo 'options[arf_popup_'.$post_type.'_show_full_screen]'; ?>" value="yes" id="<?php echo 'arf_popup_'.$post_type.'_show_full_screen_yes'; ?>" />
	                            <svg width="18px" height="18px">
	                    	        <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
	                	            <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
	                             </svg>
	                        </div>
	                        <span>
	                            <label for="<?php echo 'arf_popup_'.$post_type.'_show_full_screen_yes'; ?>"><?php echo addslashes(esc_html__('Yes', 'ARForms')); ?></label>
	                        </span>
	                    </div>
	                </div>
	                <div class="arf_radio_wrapper arfminwidth30">
	                    <div class="arf_custom_radio_div">
	                        <div class="arf_custom_radio_wrapper">
	                            <input type="radio" class="arf_custom_radio arf_submit_entries" <?php checked($show_full_screen,'no'); ?> name="<?php echo 'options[arf_popup_'.$post_type.'_show_full_screen]'; ?>" value="no" id="<?php echo 'arf_popup_'.$post_type.'_show_full_screen_no'; ?>" />
	                            <svg width="18px" height="18px">
	                	            <?php echo ARF_CUSTOM_UNCHECKEDRADIO_ICON; ?>
	                    	        <?php echo ARF_CUSTOM_CHECKEDRADIO_ICON; ?>
	                            </svg>
	                        </div>
	                        <span>
	                            <label for="<?php echo 'arf_popup_'.$post_type.'_show_full_screen_no'; ?>" ><?php echo addslashes(esc_html__('No', 'ARForms')); ?></label>
	                        </span>
	                    </div>
	                </div>
	            </div>
    		</div>
    	</div>

    	<div class="site_wide_popup_form_row site_wide_popup_sub_options" id="<?php echo $post_type.'_modal_effect_div' ?>">
    		<div class="site_wide_popup_label">
    			<label><?php echo addslashes(esc_html__('Animation Effect','ARForms')); ?></label>
    		</div>
    		<div class="site_wide_popup_input">
				<div class="dt_dl <?php echo "arf_animation_effect_dd_".$post_type; ?>" id="" style="<?php if (is_rtl()) { echo 'text-align:right;'; } else { echo 'text-align:left;'; } ?>">
					<input type="hidden" name="<?php echo 'options[arf_popup_'.$post_type.'_model_effect]'; ?>" id="<?php echo 'arf_popup_'.$post_type.'_model_effect'; ?>" value="<?php echo $model_effect; ?>" onchange=""/>
					<dl class="arf_selectbox" data-name="<?php echo 'arf_popup_'.$post_type.'_model_effect'; ?>" data-id="<?php echo 'arf_popup_'.$post_type.'_model_effect'; ?>" style="width:151px;">
						<dt>
							<span style="float:left;"><?php echo $model_effect_arr[$model_effect]; ?></span>
							<input value="fade_in" style="display:none;" class="" type="text">
							<i class="arfa arfa-caret-down arfa-lg"></i>
						</dt>
						<dd>
							<ul style="display:none;width:167px;" data-id="<?php echo 'arf_popup_'.$post_type.'_model_effect'; ?>">
								<?php foreach( $model_effect_arr as $model_effect_k => $model_effect_v ){ ?>
									<li class="lblnotetitle arf_selectbox_option" data-value="<?php echo $model_effect_k; ?>" data-label="<?php echo $model_effect_v; ?>"><?php echo $model_effect_v; ?></li>
								<?php } ?>
							</ul>
						</dd>
					</dl>  
				</div>
    		</div>
    	</div>

    	<div class="site_wide_popup_form_row">
    		<div class="site_wide_popup_label">
    			<label><?php echo sprintf(esc_html__('Exclude %s','ARForms'),ucfirst($post_type.'s')); ?> <div class="arf_popup_tooltip_main"><img src="<?php echo ARFIMAGESURL ?>/tooltips-icon.png" alt="?" class="arfhelptip" title="<?php echo sprintf(esc_html__('Popup will not be displayed on those %s you have selected from below list.' , 'ARForms'), $post_type.'s'); ?>" /></div></label>
    		</div>
    		<div class="site_wide_popup_input">
    			<div class="arf_choosen_select_container">
	                <select multiple="multiple" id="<?php echo $post_type.'_chosen_select' ?>" class='arf_exclude_page_post' id='<?php echo $post_type.'_exclude'; ?>'>
	                	<?php foreach( $post_lists as $key => $value){ ?>
	                    	<option style="height:22px;font-size:14px;padding-top:4px;" value="<?php echo $value->ID; ?>" <?php echo (in_array($value->ID,$exclude_post_type)) ? ' selected="selected" ' : ''; ?>><?php echo $value->post_title; ?></option>
	                  	<?php } ?>
	                </select>
	                <span class="arf_exclude_page_post_note"><?php echo sprintf(esc_html__('Hold %s key to select multiple %s','ARForms'),'ctrl',$post_type.'s'); ?></span>
	                <input type="hidden" name="<?php echo 'options[arf_popup_'.$post_type.'_exclude]'; ?>" id="<?php echo $post_type.'_chosen_select_input' ?>" value="<?php echo implode(',', $exclude_post_type); ?>" />
	            </div>
    		</div>
    	</div>
    </div>
<?php
	}
?>