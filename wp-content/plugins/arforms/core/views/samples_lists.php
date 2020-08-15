<div class="wrap arfforms_page">
    <div class="top_bar" style="margin-bottom: 10px;">
	<span class="h2"> <?php echo addslashes(esc_html__('ARForms Samples','ARForms')); ?></span>
    </div>
	<div id="poststuff" class="">
    	<div id="post-body" >
        	<div class="arf_samples_page_content">
                <div class="arf_samples_page_desc"></div>
                <div class="arf_samples_page_inner_content">
					<?php
						global $arsamplecontroller;
						$sample_lists = $arsamplecontroller->samples_list();
					?>
                </div>
            </div>
        </div>
    </div>
    <div class="arf_modal_overlay">
        <div class="arf_modal_container arf_failed_sample_popup_container">
            <div class="arf_modal_top_belt">
                <span class="arf_modal_title"><?php esc_html_e('Install Failed','ARForms'); ?></span>
                <div class="arf_modal_close_btn arf_failed_sample_popup_container_close"></div>
            </div>
            <div class="arf_sample_popup_content">
                <div class="arf_sample_popup_msg"><?php esc_html_e('Please activate license to install this sample.','ARForms') ?></div>
                <div class="arf_sample_popup_button">
                    <button id="arf_sample_popup_btn_div" type="button" class="arf_sample_popup_btn"><?php esc_html_e('OK','ARForms'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
