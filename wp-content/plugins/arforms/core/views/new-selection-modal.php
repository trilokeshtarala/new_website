<?php


global $arfform;

$all_templates = $arfform->getAll(array('is_template' => 1), 'name');

$sv[1] = '<div class="arfsubscriptionform arftemplateicondiv"></div>';

$sv[2] = '<div class="arfregistrationform arftemplateicondiv"></div>';

$sv[7] = '<div class="arfjobapplicationform arftemplateicondiv"></div>';

$sv[4] = '<div class="arfsurveyform arftemplateicondiv"></div>';

$sv[6] = '<div class="arfrsvpform arftemplateicondiv"></div>';
$sv[5] = '<div class="arfffedbackform arftemplateicondiv"></div>';

$sv[3] = '<div class="arfcontactform arftemplateicondiv"></div>';

$sv[8] = '<div class="arfdonationform arftemplateicondiv"></div>';

$sv[9] = '<div class="arfrequestaquoteform arftemplateicondiv"></div>';

$sv[10] = '<div class="arfmemberloginform arftemplateicondiv"></div>';

$sv[11] = '<div class="arforderform arftemplateicondiv"></div>';

?>

<div id="new_form_selection_modal">
	<form method="get" name="new" id="new">
        <input type="hidden" name="arfaction" id="arfnewaction" value="new" />
        <input type="hidden" name="page" value="ARForms" />
        
        <input type="hidden" name="id" id="template_list_id" value="" />    
	<div class="newform_modal_title_container">
    	<div class="newform_modal_title"><?php echo addslashes(esc_html__('New Form','ARForms'));?></div>
    </div>
 	
    <div class="newform_modal_fields_start_left">
    	
        <div class="newmodal_field_title"><?php echo addslashes(esc_html__('Form Title','ARForms'));?>&nbsp;<span class="newmodal_required" style="color:#ff0000; vertical-align:top;">*</span></div>
        <div class="newmodal_field"><input name="form_name" id="form_name_new" value="" class="txtmodal1" /><br /><div id="form_name_new_required" class="arferrmessage" style="display:none;"><?php echo addslashes(esc_html__('Please enter form title','ARForms'));?></div></div>
        

	<div class="newmodal_field_title">
		<?php echo addslashes(esc_html__('Form Description','ARForms'));?>
	</div>
    <div class="newmodal_field">
    	<textarea name="form_desc" id="form_desc_new" class="txtmultimodal1" rows="2" ></textarea>
    </div>

    <div class="newmodal_field_title">
		<?php echo addslashes(esc_html__('Select Theme','ARForms'));?>
	</div>
    <div class="newmodal_field">
		<div class="">
		    <input type="hidden" name="templete_style" value="material" id="templete_style">
		    <dl class="arf_selectbox" data-name="arfinpst" data-id="templete_style">
		    	<?php
	                $inputStyle = array(
	                    'standard' => addslashes(esc_html__('Standard Style', 'ARForms')),
	                    'rounded' => addslashes(esc_html__('Rounded Style', 'ARForms')),
	                    'material' => addslashes(esc_html__('Material Style', 'ARForms'))
	                );
	            ?>
		        <dt class="arf_templete_style_dt">
			          <span style="float:left;"><?php echo addslashes(esc_html__('Material Style','ARForms'));?></span>
			          <i class="arfa arfa-caret-down arfa-lg"></i>
		          </dt>
		        <dd>
		            <ul style="display:none;" data-id="templete_style" class="arf_templete_style_ul">
		                <li class="arf_selectbox_option" data-value="standard" data-label="<?php echo addslashes(esc_html__('Standard Style', 'ARForms'));?>"><?php echo addslashes(esc_html__('Standard Style', 'ARForms'));?></li>
		                <li class="arf_selectbox_option" data-value="rounded" data-label="<?php echo addslashes(esc_html__('Rounded Style', 'ARForms'));?>"><?php echo addslashes(esc_html__('Rounded Style', 'ARForms'));?></li>
		                <li class="arf_selectbox_option" data-value="material" data-label="<?php echo addslashes(esc_html__('Material Style', 'ARForms'));?>"><?php echo addslashes(esc_html__('Material Style', 'ARForms'));?></li>
		            </ul>
		        </dd>
		    </dl>
	    </div>
    </div>


    <!-- new form in RTL mode option -->
    <?php if(is_rtl()){ ?>

    <div class="newmodal_field_title">
	    		<?php echo addslashes(esc_html__('Input Direction','ARForms'));?>
	    	</div>
    <div class="newmodal_field">
		<div class="">
	    <input type="hidden" name="arf_rtl_switch_mode" value="" id="arf_load_form_rtl_switch">
	    <dl class="arf_selectbox" data-name="arfinpdir" data-id="arf_load_form_rtl_switch">
	        <dt class="arf_templete_style_dt">
		          <span style="float:left;"><?php echo addslashes(esc_html__('Left to Right','ARForms'));?></span>
		          <i class="arfa arfa-caret-down arfa-lg"></i>
	          </dt>
	        <dd>
	            <ul style="display:none;" data-id="arf_load_form_rtl_switch" class="arf_templete_style_ul">
	                <li class="arf_selectbox_option" data-value="no" data-label="<?php echo addslashes(esc_html__('Left to Right', 'ARForms'));?>"><?php echo addslashes(esc_html__('Left to Right', 'ARForms'));?></li>
	                <li class="arf_selectbox_option" data-value="yes" data-label="<?php echo addslashes(esc_html__('Right to Left', 'ARForms'));?>"><?php echo addslashes(esc_html__('Right to Left', 'ARForms'));?></li>
	            </ul>
	        </dd>
	    </dl>
	    </div>
    </div>
    <?php } ?>
	<!-- end RTL mode Option -->

        <div class="newmodal_field_title" style="margin-top: 20px;"><?php echo addslashes(esc_html__('Please Select Template','ARForms'));?></div>
        <div class="newmodal_field arfdefaulttemplate" style="margin-top: 10px;<?php echo (is_rtl()) ? 'float: right;' : 'float: left;';?>">
        
        <div id="arftemplate_blankform" onclick="arf_selectform('blankform');" class="arf_modalform_box arf_modalblankform_box arfactive" style="margin-bottom:5px;">
		    <div class="arf_formbox_hover"></div>
		    <div class="arfblankformsvg arftemplateicondiv"></div>	    
		    <div class="arf_modalform_boxtitle"><?php echo addslashes(esc_html__('Blank Form','ARForms'));?></div>  
		</div>
		<?php 
		    global $arfdefaulttemplate;
		    if( $arfdefaulttemplate )
		    {
			    $ti = 1;
			    foreach($arfdefaulttemplate as $template_id => $template_name)
			    {?>
	    <div id="arftemplate_<?php echo $template_id ?>" onclick="arf_selectform('<?php echo $template_id ?>','<?php echo $template_name['theme'] ?>','<?php echo $template_name['name'] ?>');" class="arf_modalform_box" <?php if($ti <= 3){ ?>style="margin-bottom:5px;"<?php } ?>>
		<div class="arf_formbox_hover"></div>
		<?php echo $sv[$template_id];?>		
		<div class="arf_modalform_boxtitle"><?php echo $template_name['name'];?></div>  
	    </div>
	    <?php
			$ti++;
			}
		    }?> 
        </div>
        
    </div>
	<div style="clear:both;"></div>
	
	
	<div id="arfcontinuebtn" >
	    <button type="button" class="rounded_button arf_btn_dark_blue" id="submit_new_form" onclick="submit_form_type();" style=""><?php echo addslashes(esc_html__('Continue', 'ARForms')); ?></button>
	    <button type="button" class="rounded_button arfnewmodalclose" style="<?php echo (is_rtl() ) ? 'margin-right:22px;' : 'margin-right:11px;'; ?>background-color:#ECECEC;color:#666666;position:inherit;"><?php echo addslashes(esc_html__('Cancel', 'ARForms')); ?></button> 
	</div>
    </form>
    
    <script type="text/javascript">
    jQuery(document).ready(function(e){
	jQuery('#form_name_new').focus();    
    });
  

    </script>
</div>