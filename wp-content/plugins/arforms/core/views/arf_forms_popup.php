<?php

global $wpdb, $MdlDb;
$form_cols = array();
$actions['bulk_delete'] = addslashes(esc_html__('Delete', 'ARForms'));

$count = 0;

$frm_list = "<li class='lblnotetitle arf_selectbox_option' data-value='0' data-label='".esc_html__('Please select form','ARForms')."'>".esc_html__('Please select form','ARForms')."</li>";
$res = $wpdb->get_results($wpdb->prepare("SELECT id,name,is_template,status FROM ".$MdlDb->forms." WHERE is_template=%d AND status=%s", 0, 'published'));
if(!empty($res)){
	$res_count = count($res);
	foreach ($res as $key => $value) {
		$frm_list .= "<li class='lblnotetitle arf_selectbox_option' data-value='".$value->id."' data-label='".$value->name."'>".$value->name."</li>";	
	}
}

include(VIEWS_PATH . '/arf_popup_controls.php');

$is_display_popup = 0;
$display_popup_posts = 0;
$display_popup_pages = 0;

$values = array();
 
echo str_replace('id="{arf_id}"','id="arf_full_width_loader"',ARF_LOADER_ICON); 

?>

<div class="wrap frm_entries_page arf_popup_frm_list_page">
	<div class="top_bar">
        <span class="h2"><?php echo addslashes(esc_html__('Site-wide Popups', 'ARForms')); ?></span>
        <input type="hidden" name="arfmainformurl" data-id="arfmainformurl" value="<?php echo ARFURL; ?>" />   
    </div>

    <div id="success_message" class="arf_success_message">
        <div class="message_descripiton">
            <div style="float: left; margin-right: 15px;" id="records_suc_message_des"></div>
            <div class="message_svg_icon">
                <svg style="height: 14px;width: 14px;"><path fill-rule="evenodd" clip-rule="evenodd" fill="#FFFFFF" d="M6.075,14.407l-5.852-5.84l1.616-1.613l4.394,4.385L17.181,0.411l1.616,1.613L6.392,14.407H6.075z"></path></svg>
            </div>
        </div>
    </div>

    <div id="error_message" class="arf_error_message">
        <div class="message_descripiton">
            <div style="float: left; margin-right: 15px;" id="records_error_message_des"></div>
            <div class="message_svg_icon">
                <svg style="height: 14px;width: 14px;"><path fill-rule="evenodd" clip-rule="evenodd" fill="#ffffff" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></svg>
            </div>
        </div>
    </div>


    <div id="poststuff" class="metabox-holder">
    	<div id="post-body">
            <div class="inside" style="background-color:#ffffff;">
            	<div class="arf_form_popup_entries_wrapper">            	
            		<div id="arf_form_popup_entries">
            			<form method="get" id="list_popup_form" class="arf_list_popup_form" onsubmit="return apply_bulk_delete_popup();" style="float:left;width:98%;">
		            		<div class="arf_display_form_in_popup_div">
		                        <button class="rounded_button arf_btn_dark_blue" id="arf_display_form_in_popup" type="button" style="width:230px !important;"><svg width="20px" height="20px" style="vertical-align: middle;"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#FFFFFF" d="M16.997,7.32v2h-7v6.969h-2V9.32h-7v-2h7V0.289h2V7.32H16.997z"></path></svg>&nbsp;<?php esc_html_e('Add new Site-wide Popup', 'ARForms'); ?></button>
		                    </div>

		            		<div class="alignleft actions">
		                        <div class="arf_list_bulk_action_wrapper">
		                            <input id="arf_bulk_action_one" name="action1" value="-1" type="hidden">
		                            <dl class="arf_selectbox" data-name="action1" data-id="arf_bulk_action_one">
		                                <dt style="width:105px;"><span><?php echo addslashes(esc_html__('Bulk Actions', 'ARForms')); ?></span>
		                                <svg viewBox="0 0 2000 1000" width="15px" height="15px">
		                                <g fill="#000">
		                                <path d="M1024 320q0 -26 -19 -45t-45 -19h-896q-26 0 -45 19t-19 45t19 45l448 448q19 19 45 19t45 -19l448 -448q19 -19 19 -45z"/>
		                                </g>
		                                </svg>
		                                </dt>
		                                <dd>
		                                    <ul style="display: none;width:121px;" data-id="arf_bulk_action_one">
		                                        <li data-value='-1' data-label='<?php echo addslashes(esc_html__('Bulk Actions', 'ARForms')); ?>'><?php echo addslashes(esc_html__('Bulk Actions', 'ARForms')); ?></li>
		                                        <?php
		                                        foreach ($actions as $name => $title) {
		                                            $class = 'edit' == $name ? ' class="hide-if-no-js" ' : '';
		                                            ?>
		                                            <li <?php echo $class; ?> data-value='<?php echo $name; ?>' data-label='<?Php echo $title; ?>'><?php echo $title; ?></li>
		                                        <?php } ?>
		                                    </ul>
		                                </dd>
		                            </dl>
		                        </div>
		                        <input type="submit" id="doaction1" class="arf_bulk_action_btn rounded_button btn_green" value="<?php echo addslashes(esc_html__('Apply', 'ARForms')); ?>"/>
		                    </div>


		                    <table cellpadding="0" cellspacing="0" border="0" class="display table_grid arf_popup_list_table" id="example">
		                    	<thead>
                                    <tr>
                                        <th class="center box" style="width:50px;">
                                            <div style="display:inline-block; position:relative;">
                                                <div class="arf_custom_checkbox_div arfmarginl15">
                                                    <div class="arf_custom_checkbox_wrapper arfmargin10custom">
                                                        <input id="cb-select-all-popup-1" type="checkbox" class="">
                                                        <svg width="18px" height="18px">
                                                        <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                                        <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                                        </svg>
                                                    </div>
                                                </div>

                                                <label for="cb-select-all-popup-1"  class="cb-select-all"><span class="cb-select-all-checkbox"></span></label>
                                            </div>
                                        </th>
                                        <th style="width:15%;"><?php echo addslashes(esc_html__('Form ID', 'ARForms')); ?></th>
                                        <th style="width:35%;"><?php echo addslashes(esc_html__('Form Title', 'ARForms')); ?></th>
                                        <th style="width:15%;"><?php echo esc_html__('Popup Type', 'ARForms'); ?></th>
                                        <th style="width:15%;"><?php echo esc_html__('Status', 'ARForms'); ?></th>
                                        <th style="width:20%;"><?php echo addslashes(esc_html__('Popup created date', 'ARForms')); ?></th>
                                        
                                        <th class="arf_col_action arf_action_cell"><?php echo addslashes(esc_html__('Action', 'ARForms')); ?></th>
                                    </tr>
                                </thead>

                                <tbody>

                                	<?php
                                        global $arforms_popup;
                                        $popup_data = $arforms_popup->arf_load_popup_list();

                                        if(isset($popup_data['data'])){
                                        	echo $popup_data['data'];
                                        }
                                        
                                    ?>
                                </tbody>
		                    </table>

		                    <div class="clear"></div>

		                    <input type="hidden" name="show_hide_columns" id="show_hide_columns" value="<?php echo addslashes(esc_html__('Show / Hide columns', 'ARForms')); ?>"/>
                            <input type="hidden" name="search_grid" id="search_grid" value="<?php echo addslashes(esc_html__('Search', 'ARForms')); ?>"/>
                            <input type="hidden" name="entries_grid" id="entries_grid" value="<?php echo addslashes(esc_html__('entries', 'ARForms')); ?>"/>
                            <input type="hidden" name="show_grid" id="show_grid" value="<?php echo addslashes(esc_html__('Show', 'ARForms')); ?>"/>
                            <input type="hidden" name="showing_grid" id="showing_grid" value="<?php echo addslashes(esc_html__('Showing', 'ARForms')); ?>"/>
                            <input type="hidden" name="to_grid" id="to_grid" value="<?php echo addslashes(esc_html__('to', 'ARForms')); ?>"/>
                            <input type="hidden" name="of_grid" id="of_grid" value="<?php echo addslashes(esc_html__('of', 'ARForms')); ?>"/>
                            <input type="hidden" name="no_match_record_grid" id="no_match_record_grid" value="<?php echo addslashes(esc_html__('No matching records found', 'ARForms')); ?>"/>
                            <input type="hidden" name="no_record_grid" id="no_record_grid" value="<?php echo addslashes(esc_html__('No data available in table', 'ARForms')); ?>"/>
                            <input type="hidden" name="filter_grid" id="filter_grid" value="<?php echo addslashes(esc_html__('filtered from', 'ARForms')); ?>"/>
                            <input type="hidden" name="totalwd_grid" id="totalwd_grid" value="<?php echo addslashes(esc_html__('total', 'ARForms')); ?>"/>

		                    <div class="alignleft actions">
		                        <div class="arf_list_bulk_action_wrapper">
		                            <input id="arf_bulk_action_two" name="action2" value="-1" type="hidden">
		                            <dl class="arf_selectbox" data-name="action2" data-id="arf_bulk_action_two">
		                                <dt style="width:105px;"><span><?php echo addslashes(esc_html__('Bulk Actions', 'ARForms')); ?></span>
		                                <svg viewBox="0 0 2000 1000" width="15px" height="15px">
		                                <g fill="#000">
		                                <path d="M1024 320q0 -26 -19 -45t-45 -19h-896q-26 0 -45 19t-19 45t19 45l448 448q19 19 45 19t45 -19l448 -448q19 -19 19 -45z"/>
		                                </g>
		                                </svg>
		                                </dt>
		                                <dd>
		                                    <ul style="display: none;width:121px;" data-id="arf_bulk_action_two">
		                                        <li data-value='-1' data-label='<?php echo addslashes(esc_html__('Bulk Actions', 'ARForms')); ?>'><?php echo addslashes(esc_html__('Bulk Actions', 'ARForms')); ?></li>
		                                        <?php
		                                        foreach ($actions as $name => $title) {
		                                            $class = 'edit' == $name ? ' class="hide-if-no-js" ' : '';
		                                            ?>
		                                            <li <?php echo $class; ?> data-value='<?php echo $name; ?>' data-label='<?Php echo $title; ?>'><?php echo $title; ?></li>
		                                        <?php } ?>
		                                    </ul>
		                                </dd>
		                            </dl>
		                        </div>
		                        <input type="submit" id="doaction2" class="arf_bulk_action_btn rounded_button btn_green" value="<?php echo addslashes(esc_html__('Apply', 'ARForms')); ?>"/>
		                    </div>

		                    <div class="footer_grid"></div>
		            	</form>
	            	</div>
            	</div>
            	<div class="arf_modal_overlay">
	                <div id="delete_form_message" class="arfmodal arfdeletemodabox arf_popup_container arfdeletemodalboxnew">
	                    <input type="hidden" value="" id="delete_id" />
	                    <div class="arfdelete_modal_msg delete_confirm_message"><?php echo sprintf(addslashes(esc_html__('Are you sure you want to %s delete this entry?', 'ARForms')),'<br/>'); ?></div>
	                    <div class="arf_delete_modal_row delete_popup_footer">
	                        <input type="hidden" value="false" id="bulk_delete_flag"/>
	                        <button class="rounded_button add_button arf_delete_modal_left arfdelete_color_red" onclick="arf_delete_bulk_popup('true');">&nbsp;<?php echo addslashes(esc_html__('Okay', 'ARForms')); ?></button>&nbsp;&nbsp;<button class="arf_delete_modal_right rounded_button delete_button arfdelete_color_gray" onclick="jQuery('.arf_popup_container,.arf_modal_overlay').removeClass('arfactive');">&nbsp;<?php echo addslashes(esc_html__('Cancel', 'ARForms')); ?></button>
	                    </div>
	                </div>
	            </div>
            </div>
        </div>
    </div>

    
    <form id="arf_popup_form" name="arf_popup_form" onsubmit="return arf_validate_popup_list_data();">
		<div class="arf_modal_overlay">
			<div id="arf_display_form_in_popup_model" class="arf_popup_container arf_popup_container_other_option_model">
				<div class="arf_popup_container_header"><?php echo esc_html__('Add new Site-wide Popup', 'ARForms'); ?>
		            <div class="arfpopupclosebutton arfmodalclosebutton" data-dismiss="arfmodal" data-id="arf_popup_list_button">
		                <svg width="30px" height="30px" viewBox="1 0 20 20"><g id="preview"><path fill-rule="evenodd" clip-rule="evenodd" fill="#262944" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></g></svg>
		            </div>
		        </div>
		        <div class="arf_popup_content_container" id="arf_popup_content_container">
		        	<div class="arf_popup_container_loader arf_edit_popup_container_loader">
		        		<i class="arfa arfa-spinner arfa-spin"></i>
		        	</div>
		            <label class="arf_popup_label_left arftitle_p" for="arf_display_form_in_popup_input">
		                <span><?php esc_html_e('Select a Form to display on page/post','ARForms'); ?>:&nbsp;</span>
		            </label>
		            
		            <div class="dt_dl arf_form_list_dd" style="<?php if (is_rtl()) { echo 'text-align:right;'; } else { echo 'text-align:left;'; } ?>">
	                  	<input type="hidden" name="arf_popup_selected_form" id="arf_popup_selected_form" value=""/>
	                  	<dl class="arf_selectbox" data-name="arf_popup_selected_form" data-id="arf_popup_selected_form" style="width:300px;">
	                      	<dt>
	                        	<span style="float:left;">Please select form</span>
	                        	<input value="top" style="display:none;" class="" type="text">
	                        	<i class="arfa arfa-caret-down arfa-lg"></i>
	                      	</dt>
	                      	<dd>
	                          	<ul style="display:none;width:316px;" data-id="arf_popup_selected_form">
	                              <?php echo $frm_list; ?>
	                          	</ul>
	                      	</dd>
	                  	</dl>  
	              	</div>
	              	<br/>
	              	<br/>
	              	<br/>
	              	
		            <hr class="arf_popup_option_separator" />
		            <div class="arf_popup_display_section" id="arf_popup_display_section" style="float:left;width:100%;">
		            	<div class="arf_popup_section_row" style="margin-bottom:30px;">
		                	<label class="arf_popup_label_left arftitle_p">
		                		<span><?php esc_html_e('Popups on Posts','ARForms'); ?>&nbsp;</span>
		                		<div class="arf_popup_tooltip_main"><img src="<?php echo ARFIMAGESURL ?>/tooltips-icon.png" alt="?" class="arfhelptip" title="<?php echo addslashes(esc_html__('Popup will be displayed on individual Post. (not in listing)', 'ARForms')) ?>" /></div>
		                	</label>
		                	<label class="arf_js_switch_label" style="margin-right:8px;"><?php _e('Off','ARForms'); ?></label>
		                	<span class="arf_js_switch_wrapper">
		                		<input type="checkbox" class="js-switch" name="arf_display_popup_in_posts" id="arf_display_popup_in_posts_input" value="1" <?php checked(1,$display_popup_posts); ?> />
		                		<span class="arf_js_switch"></span>
		                	</span>
		                	<label class="arf_js_switch_label" style="margin-left:8px;"><?php _e('On','ARForms'); ?></label>
		                	<div class="arf_popup_post_inner_container arf_display_popup_inner_container" style="<?php echo ($display_popup_posts == 1)? '' :'display:none;'; ?>">
		                		<?php arf_get_display_popup_option('post',$values); ?>
		                	</div>
		                </div>
		                
		                <div class="arf_post_popup_switch_err_div"></div>
		                <hr class="arf_popup_option_separator" />

		                <div class="arf_popup_section_row" style="margin-bottom:30px;">
		                	<label class="arf_popup_label_left arftitle_p" >
		                		<span><?php esc_html_e('Popups on Pages','ARForms'); ?>&nbsp;</span>
		                		<div class="arf_popup_tooltip_main"><img src="<?php echo ARFIMAGESURL ?>/tooltips-icon.png" alt="?" class="arfhelptip" title="<?php echo addslashes(esc_html__('Popup will be displayed on individual Page. (not in listing)', 'ARForms')) ?>" /></div>
		                	</label>
		                	<label class="arf_js_switch_label" style="margin-right:8px;"><?php _e('Off','ARForms'); ?></label>
		                	<span class="arf_js_switch_wrapper">
		                		<input type="checkbox" class="js-switch" name="arf_display_popup_in_pages" id="arf_display_popup_in_pages_input" value="1" <?php checked(1,$display_popup_pages); ?> />
		                		<span class="arf_js_switch"></span>
		                	</span>
		                	<label class="arf_js_switch_label" style="margin-left:8px;"><?php _e('On','ARForms'); ?></label>
		                	<div class="arf_popup_page_inner_container arf_display_popup_inner_container" style="<?php echo ($display_popup_pages == 1)? '' :'display:none;'; ?>">
		                		<?php arf_get_display_popup_option('page',$values); ?>
		                	</div>
		                </div>

		                <div class="arf_page_popup_switch_err_div"></div>
		            </div>
		            <hr class="arf_popup_option_separator" />
		            <div class="arf_popup_display_container">
		            	<div class="site_wide_popup_form_row">
		            		<div class="site_wide_popup_label site_wide_popup_special_page_title arftitle_p">
		            			<label><?php esc_html_e('Hide popup on following special pages','ARForms'); ?></label>
		            		</div>
		            	</div>
		            	<div class="arf_popup_section_row site_wide_popup_sub_options" style="margin-bottom: 20px;">
							<label class="arf_popup_label_left" >
		                		<span><?php esc_html_e('Archive pages', 'ARForms'); ?></span>
		                	</label>	    
		                	<div class="arf_custom_checkbox_wrapper">
                                <input type="checkbox" id="arf_hide_popup_in_archive_input" name="arf_hide_popup_in_archive" value="1" checked="checked" style="border:none;">
                                <svg width="18px" height="18px">
		                            <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
	                                <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                </svg>
                            </div>
					    </div>
					    <div class="arf_popup_section_row site_wide_popup_sub_options" style="margin-bottom: 20px;">
							<label class="arf_popup_label_left" >
		                		<span><?php esc_html_e('404 page', 'ARForms'); ?></span>
		                	</label>	    
		                	<div class="arf_custom_checkbox_wrapper">
                                <input type="checkbox" id="arf_hide_popup_in_404_input" name="arf_hide_popup_in_404" value="1" checked="checked" style="border:none;">
                                <svg width="18px" height="18px">
		                            <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
	                                <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                </svg>
                            </div>
					    </div>
					    <div class="arf_popup_section_row site_wide_popup_sub_options" style="margin-bottom: 20px;">
							<label class="arf_popup_label_left" >
		                		<span><?php esc_html_e('Front page', 'ARForms'); ?></span>
		                	</label>
		                	<div class="arf_custom_checkbox_wrapper">
                                <input type="checkbox" id="arf_hide_popup_in_front_input" name="arf_hide_popup_in_front" value="1" style="border:none;">
                                <svg width="18px" height="18px">
		                            <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
	                                <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                </svg>
                            </div>
					    </div>
					    <div class="arf_popup_section_row site_wide_popup_sub_options" style="margin-bottom: 20px;">
							<label class="arf_popup_label_left">
		                		<span><?php esc_html_e('Search Result page', 'ARForms'); ?></span>
		                	</label>
		                	<div class="arf_custom_checkbox_wrapper">
                                <input type="checkbox" id="arf_hide_popup_in_search_result_input" name="arf_hide_popup_in_search_result" value="1" checked="checked" style="border:none;">
                                <svg width="18px" height="18px">
		                            <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
	                                <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                </svg>
                            </div>
					    </div>
		            </div>
		            
		        </div>
		        <div class="arf_popup_container_footer">
		        	<div class="arf_popup_save_loader_container">
	        			<div class="arf_imageloader arf_popup_save_loader" id="ajax_form_loader"></div>
	        		</div>
		            <button type="submit" class="arf_popup_close_button btn_green" data-id="arf_list_popup_button" id="arf_list_popup_button"><?php echo esc_html__('Save', 'ARForms'); ?></button>
		        </div>
			</div>
		</div>
	</form>
</div>