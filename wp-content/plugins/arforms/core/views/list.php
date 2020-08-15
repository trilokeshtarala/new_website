<?php
global $arformhelper;
$actions['bulk_delete'] = addslashes(esc_html__('Delete', 'ARForms'));
if (isset($_REQUEST['err']) and $_REQUEST['err'] == 1) $errors[] = addslashes(esc_html__('This form is already deleted.', 'ARForms'));

$width = isset($_COOKIE['width']) ? $_COOKIE['width'] * 0.80 : '';
$width_new = '&width=' . $width;

$default_hide = array (
    '0' => '',
    '1' => 'ID',
    '2' => 'Name',
    '3' => 'Entries',
    '4' => 'Shortcodes',
    '5' => 'Create Date',
    '6' => 'Action',
);

$columns_list = (get_option('arfformcolumnlist') != '' ) ?  maybe_unserialize(get_option('arfformcolumnlist')) : array();
$is_colmn_array = is_array($columns_list);

$exclude = '';

if (count($columns_list) > 0 and $columns_list != '') {
    foreach ($default_hide as $key => $val) {
        foreach ($columns_list as $column) {
            if ($column == $val) {
                $exclude .= $key . ', ';
            }
        }
    }
}
?>

<script type="text/javascript" data-cfasync="false" charset="utf-8">
// <![CDATA[

    jQuery(document).ready(function () {
        var height = jQuery(window).height();
        document.cookie = 'height=' + height;
        var oTable = "";

        var width = jQuery(window).width();
        document.cookie = 'width=' + width;

        function change_title(val) {
            val.title = '<span class="tb_liev_prev"><img style="vertical-align: middle;padding-bottom: 3px;" align="absmiddle" src="<?php echo ARFIMAGESURL; ?>/preview-icon.png">&nbsp;Form Preview</span><div align="right" class="tb_go_back"><button onClick="javascript:CloseWindow();" type="button" class="btn_3"><img style="vertical-align: middle;" src="<?php echo ARFIMAGESURL; ?>/back_icon.png">&nbsp;&nbsp;Back To Editor</button></div>';

            jQuery('#TB_window').html('');
            jQuery('#TB_title').html('');
            jQuery('#TB_ajaxContent').html('');
        }

        jQuery.fn.dataTableExt.oPagination.four_button = {
            "fnInit": function (oSettings, nPaging, fnCallbackDraw) {
                nFirst = document.createElement('span');
                nPrevious = document.createElement('span');
                var nInput = document.createElement('input');
                var nPage = document.createElement('span');
                var nOf = document.createElement('span');
                nOf.className = "paginate_of";
                nInput.className = "current_page_no";
                nPage.className = "paginate_page";
                nInput.type = "text";
                nInput.style.width = "40px";
                nInput.style.height = "26px";
                nInput.style.display = "inline";
                nPaging.appendChild(nPage);

                jQuery(nInput).keyup(function (e) {
                    if (e.which == 38 || e.which == 39) {
                        this.value++;
                    }
                    else if ((e.which == 37 || e.which == 40) && this.value > 1) {
                        this.value--;
                    }

                    if (this.value == "" || this.value.match(/[^0-9]/)) {
                        return;
                    }

                    var iNewStart = oSettings._iDisplayLength * (this.value - 1);
                    if (iNewStart > oSettings.fnRecordsDisplay()) {
                        oSettings._iDisplayStart = (Math.ceil((oSettings.fnRecordsDisplay() - 1) / oSettings._iDisplayLength) - 1) * oSettings._iDisplayLength;
                        fnCallbackDraw(oSettings);
                        return;
                    }

                    oSettings._iDisplayStart = iNewStart;
                    fnCallbackDraw(oSettings);
                });

                nNext = document.createElement('span');
                nLast = document.createElement('span');
                var nFirst = document.createElement('span');
                var nPrevious = document.createElement('span');
                var nPage = document.createElement('span');
                var nOf = document.createElement('span');

                nNext.style.backgroundRepeat = "no-repeat";
                nNext.style.backgroundPosition = "center";
                nNext.title = "Next";

                nLast.style.backgroundRepeat = "no-repeat";
                nLast.style.backgroundPosition = "center";
                nLast.title = "Last";

                nFirst.style.backgroundRepeat = "no-repeat";
                nFirst.style.backgroundPosition = "center";
                nFirst.title = "First";

                nPrevious.style.backgroundRepeat = "no-repeat";
                nPrevious.style.backgroundPosition = "center";
                nPrevious.title = "Previous";

                nFirst.appendChild(document.createTextNode(' '));
                nPrevious.appendChild(document.createTextNode(' '));

                nLast.appendChild(document.createTextNode(' '));
                nNext.appendChild(document.createTextNode(' '));

                nOf.className = "paginate_button nof";

                nPaging.appendChild(nFirst);
                nPaging.appendChild(nPrevious);

                nPaging.appendChild(nInput);
                nPaging.appendChild(nOf);

                nPaging.appendChild(nNext);
                nPaging.appendChild(nLast);

                jQuery(nFirst).click(function () {
                    oSettings.oApi._fnPageChange(oSettings, "first");
                    fnCallbackDraw(oSettings);
                });

                jQuery(nPrevious).click(function () {
                    oSettings.oApi._fnPageChange(oSettings, "previous");
                    fnCallbackDraw(oSettings);
                });

                jQuery(nNext).click(function () {
                    oSettings.oApi._fnPageChange(oSettings, "next");
                    fnCallbackDraw(oSettings);
                });

                jQuery(nLast).click(function () {
                    oSettings.oApi._fnPageChange(oSettings, "last");
                    fnCallbackDraw(oSettings);
                });

                jQuery(nFirst).bind('selectstart', function () {return false;});
                jQuery(nPrevious).bind('selectstart', function () {return false;});
                jQuery('span', nPaging).bind('mousedown', function () {return false;});
                jQuery('span', nPaging).bind('selectstart', function () {return false;});
                jQuery(nNext).bind('selectstart', function () {return false;});
                jQuery(nLast).bind('selectstart', function () {return false;});
            },

            "fnUpdate": function (oSettings, fnCallbackDraw) {
                if (!oSettings.aanFeatures.p) {
                    return;
                }

                var an = oSettings.aanFeatures.p;
                for (var i = 0, iLen = an.length; i < iLen; i++) {
                    var buttons = an[i].getElementsByTagName('span');

                    if (oSettings._iDisplayStart === 0) {
                        buttons[1].className = "paginate_disabled_first arfhelptip";
                        buttons[2].className = "paginate_disabled_previous arfhelptip";
                    }
                    else {
                        buttons[1].className = "paginate_enabled_first arfhelptip";
                        buttons[2].className = "paginate_enabled_previous arfhelptip";
                    }

                    if (oSettings.fnDisplayEnd() == oSettings.fnRecordsDisplay()) {
                        buttons[4].className = "paginate_disabled_next arfhelptip";
                        buttons[5].className = "paginate_disabled_last arfhelptip";
                    }
                    else {
                        buttons[4].className = "paginate_enabled_next arfhelptip";
                        buttons[5].className = "paginate_enabled_last arfhelptip";
                    }

                    if (!oSettings.aanFeatures.p) {
                        return;
                    }

                    var iPages = Math.ceil((oSettings.fnRecordsDisplay()) / oSettings._iDisplayLength);
                    var iCurrentPage = Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength) + 1;

                    if (document.getElementById('of_grid')) {
                        of_grid = document.getElementById('of_grid').value;
                    }
                    else {
                        of_grid = 'of';
                    }

                    var an = oSettings.aanFeatures.p;
                    for (var i = 0, iLen = an.length; i < iLen; i++) {
                        var spans = an[i].getElementsByTagName('span');
                        var inputs = an[i].getElementsByTagName('input');
                        spans[spans.length - 3].innerHTML = " " + of_grid + " " + iPages
                        inputs[0].value = iCurrentPage;
                    }
                }
            }
        };

        

        initDatatTable();

        jQuery("#arf_full_width_loader").hide();

        /*jQuery(document).on("click", ".arfdelete_color_red", function() {
            jQuery(this).parents('tr').remove();
            jQuery(".arf_manage_grid_tbl").dataTable().fnDestroy();
            initDatatTable();
        });*/

        jQuery("#cb-select-all-1").click(function () {
            jQuery('input[name="item-action[]"]').attr('checked', this.checked);
        });

        jQuery(document).on('click','input[name="item-action[]"]',function() {
            if (jQuery('input[name="item-action[]"]').length == jQuery('input[name="item-action[]"]:checked').length) {
                jQuery("#cb-select-all-1").attr("checked", "checked");
            }
            else {
                jQuery("#cb-select-all-1").removeAttr("checked");
            }
        });

        jQuery(document).on('click', '.ColVis_Button:not(.ColVis_MasterButton)', function () {
            var colsArray = jQuery('.ColVis_Button :checkbox').map(function () {
                return [[jQuery(this).parent().next('.ColVis_title').text(), this.checked ? 'visibile' : 'hidden']];
            }).get();
            jQuery.ajax({
                type: "POST",
                url: ajaxurl,
                data: "action=change_show_hide_column&colsArray=" + colsArray,
                success: function (msg) {
                }
            });
        });
        jQuery('.arfhelptip').tipso({
            position: 'top',
            maxWidth: '400',
            useTitle: true,
            background: '#444444',
            color: '#ffffff',
            width: 'auto'
        });
    });
// ]]>

function initDatatTable() {

    oTable = jQuery(".arf_manage_grid_tbl").DataTable({
        "oLanguage": {
            "sProcessing": "",
            "sEmptyTable": "<?php echo addslashes(esc_html__('There is no any form found','ARForms')); ?>",
            "sZeroRecords": "<?php echo addslashes(esc_html__('There is no any form found','ARForms')); ?>"
        },
        "sDom": '<"H"lCfr>t<"footer"ip>',
        "sPaginationType": "four_button",
        "bJQueryUI": true,
        "bPaginate": true,
        "bAutoWidth": false,
        "bDestroy":false,
        "bReDraw":true,
        "oColVis": {
            "aiExclude": [0, 6]
        },
        "aoColumnDefs": [
            {"bVisible": false, "aTargets": [<?php if ($exclude != '') echo $exclude; ?>]},
            {"bSortable": false, "aTargets": [0, 6]},
            
        ],
    });
}

</script>

<style type="text/css" title="currentStyle">
    @import "<?php echo ARFURL; ?>/datatables/media/css/demo_table_jui.css";
    @import "<?php echo ARFURL; ?>/datatables/media/css/jquery-ui-1.8.4.custom.css";
    @import "<?php echo ARFURL; ?>/datatables/media/css/ColVis.css";

    .paginate_page a {
        display:none;
    }
    #poststuff #post-body {
        margin-top: 32px;
    }
    .delete_box {
        float:left;
    }

    body {
        padding:0px !important;
        margin:0px !important;
    }

    #poststuff {
        clear:both;
    }

    #poststuff #post-body {
        background:none;
        border:none;
        clear:both;
        margin-top: 0px !important;
    }
    
    .wrap_content  { 
        clear:both;
        margin-top:0px !important;
        margin-left:0px;
        margin-right:0px; 
        padding:25px; 
        background-color:#FFFFFF;
        border:none; 
        border-radius:0px;
        -webkit-border-radius:0px;
        -moz-border-radius:0px;
        -o-border-radius:0px;
    }

    .addnewbutton {
        height:45px;
    }	
</style>

<?php echo str_replace('id="{arf_id}"','id="arf_full_width_loader"',ARF_LOADER_ICON); ?>

<div class="wrap arfforms_page">
    <div class="top_bar">
        <span class="h2"><?php echo addslashes(esc_html__('Manage Forms', 'ARForms')); ?></span>
    </div>

    <div id="success_message" class="arf_success_message">
        <div class="message_descripiton">
            <div style="<?php echo (is_rtl()) ? 'float: right;' : 'float: left;';?> margin-right: 15px;" id="form_suc_message_des"></div>
            <div class="message_svg_icon">
                <svg style="height: 14px;width: 14px;"><path fill-rule="evenodd" clip-rule="evenodd" fill="#FFFFFF" d="M6.075,14.407l-5.852-5.84l1.616-1.613l4.394,4.385L17.181,0.411l1.616,1.613L6.392,14.407H6.075z"></path></svg>
            </div>
        </div>
    </div>

    <div id="error_message" class="arf_error_message">
        <div class="message_descripiton">
            <div style="<?php echo (is_rtl()) ? 'float: right;' : 'float: left;';?> margin-right: 15px;" id="form_error_message_des"></div>
            <div class="message_svg_icon">
                <svg style="height: 14px;width: 14px;"><path fill-rule="evenodd" clip-rule="evenodd" fill="#ffffff" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></svg>
            </div>
        </div>
    </div>

    <div id="poststuff" class="metabox-holder">
        <div id="post-body">
            <div class="wrap_content">
                <div style="clear:both;"></div>
                <div style="clear:both; margin-top:15px;">
                    <form method="get" id="arfmainformnewlist" class="data_grid_list" onsubmit="return apply_bulk_action_form();">
                        <input type="hidden" name="page" value="<?php echo $_GET['page'] ?>" />
                        <input type="hidden" name="arfaction" value="list" />
                        <div id="arfmainformnewlist">
                            <?php
                                do_action('arfbeforelistingforms');
                                require(VIEWS_PATH . '/shared_errors.php');
                                if (is_rtl()) {
                                    $add_new_form_btn = 'float:left;';
                                } else {
                                    $add_new_form_btn = 'float:right;';
                                }
                            ?>
                            <div style=" <?php echo $add_new_form_btn; ?>; margin-top:21px;">
                                <button class="rounded_button arf_btn_dark_blue" type="button" onclick="location.href = '<?php echo admin_url('admin.php?page=ARForms&arfaction=new&isp=1'); ?>';" style="width:160px !important;"><svg width="20px" height="20px" style="vertical-align: middle;"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#FFFFFF" d="M16.997,7.32v2h-7v6.969h-2V9.32h-7v-2h7V0.289h2V7.32H16.997z"/></svg>&nbsp;<?php echo addslashes(esc_html__('Add New Form', 'ARForms')); ?></button>
                            </div>
                            <?php $two = '1'; ?>
                            <div class="alignleft actions">
                                <div class="arf_list_bulk_action_wrapper">
                                    <input id="arf_bulk_action_one" name="action<?php echo $two; ?>" value="-1" type="hidden">
                                    <dl class="arf_selectbox" data-name="action<?php echo $two; ?>" data-id="arf_bulk_action_one">
                                        <dt style="width:105px;">
                                            <span><?php echo addslashes(esc_html__('Bulk Actions', 'ARForms')); ?></span>
                                            <svg viewBox="0 0 2000 1000" width="15px" height="15px"><g fill="#000"><path d="M1024 320q0 -26 -19 -45t-45 -19h-896q-26 0 -45 19t-19 45t19 45l448 448q19 19 45 19t45 -19l448 -448q19 -19 19 -45z"/></g></svg>
                                        </dt>
                                        <dd>
                                            <ul style="display: none;width:121px;" data-id="arf_bulk_action_one">
                                                <li data-value='-1' data-label='<?php echo addslashes(esc_html__('Bulk Actions', 'ARForms')); ?>'><?php echo addslashes(esc_html__('Bulk Actions', 'ARForms')); ?></li>
                                                <?php
                                                    foreach ($actions as $name => $title) {
                                                        $class = 'edit' == $name ? ' class="hide-if-no-js" ' : '';
                                                ?>
                                                        <li <?php echo $class; ?> data-value='<?php echo $name; ?>' data-label='<?Php echo $title; ?>'><?php echo $title; ?></li>
                                                <?php
                                                    }
                                                ?>
                                            </ul>
                                        </dd>
                                    </dl>
                                </div>
                                <input type="submit" id="doaction<?php echo $two; ?>" class="arf_bulk_action_btn rounded_button btn_green" value="<?php echo addslashes(esc_html__('Apply', 'ARForms')); ?>"/>
                            </div>

                            <table cellpadding="0" cellspacing="0" border="0" class="display table_grid arf_manage_grid_tbl" id="example">
                                <thead>
                                    <tr>
                                        <th class="center box" style="width:50px;">
                                            <div style="display:inline-block; position:relative;">
                                                <div class="arf_custom_checkbox_div arfmarginl20">
                                                    <div class="arf_custom_checkbox_wrapper <?php echo (!is_rtl()) ? 'arfmargin10custom' : '';?>">
                                                        <input id="cb-select-all-1" type="checkbox" class="">
                                                        <svg width="18px" height="18px">
                                                            <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                                            <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <label for="cb-select-all-1" class="cb-select-all"><span></span></label>
                                            </div>
                                        </th>
                                        <th class="id_column" style="width:50px;"><?php echo addslashes(esc_html__('ID', 'ARForms')); ?></th>
                                        <th class="form_title_column"><?php echo addslashes(esc_html__('Form Title', 'ARForms')); ?></th>
                                        <th class="center entry_column" style="width:90px;"><?php echo addslashes(esc_html__('Entries', 'ARForms')); ?></th>
                                        <th class="arf_shortcode_width"><?php echo addslashes(esc_html__('Shortcodes', 'ARForms')); ?></th>
                                        <th style="width:100px;"><?php echo addslashes(esc_html__('Create Date', 'ARForms')); ?></th>
                                        <th class="arf_col_action hide_action_button_row arf_action_cell" style="width:230px;"><?php echo addslashes(esc_html__('Action', 'ARForms')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        global $wpdb, $db_record, $MdlDb, $arformcontroller;
                                        echo $arformcontroller->arf_load_form_grid_data();
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

                            <div class="alignleft actions2">
                                <?php $two = '2'; ?>
                                <div class="arf_list_bulk_action_wrapper">
                                    <input id="arf_bulk_action_two" name="action<?php echo $two; ?>" value="-1" type="hidden">
                                    <dl class="arf_selectbox" data-name="action<?php echo $two; ?>" data-id="arf_bulk_action_two">
                                        <dt style="width:105px;"><span><?php echo addslashes(esc_html__('Bulk Actions', 'ARForms')); ?></span><svg viewBox="0 0 2000 1000" width="15px" height="15px"><g fill="#000"><path d="M1024 320q0 -26 -19 -45t-45 -19h-896q-26 0 -45 19t-19 45t19 45l448 448q19 19 45 19t45 -19l448 -448q19 -19 19 -45z" /></g></svg>
                                        </dt>
                                        <dd>
                                            <ul style="display: none;width:121px;" data-id="arf_bulk_action_two">
                                                <li data-value='-1' data-label='<?php echo addslashes(esc_html__('Bulk Actions', 'ARForms')); ?>'><?php echo addslashes(esc_html__('Bulk Actions', 'ARForms')); ?></li>
                                                <?php
                                                    foreach ($actions as $name => $title) {
                                                        $class = 'edit' == $name ? ' class="hide-if-no-js" ' : '';
                                                ?>
                                                        <li <?php echo $class; ?> data-value='<?php echo $name; ?>' data-label='<?Php echo $title; ?>'><?php echo $title; ?></li>
                                                <?php
                                                    }
                                                ?>
                                            </ul>
                                        </dd>
                                    </dl>
                                </div>
                                <input type="submit" id="doaction<?php echo $two; ?>" class="arf_bulk_action_btn rounded_button btn_green" value="<?php echo addslashes(esc_html__('Apply', 'ARForms')); ?>" />
                            </div>
                        </div>
                        <div class="footer_grid"></div>
                            <?php do_action('arfafterlistingforms'); ?>
                    </form>
                </div>
                <div id="arfupdateformbulkoption_div"></div>
            </div>
            <div class="arf_modal_overlay">
                <div id="delete_form_message" class="arfmodal arfdeletemodabox arf_popup_container arfdeletemodalboxnew">
                    <input type="hidden" value="" id="delete_id" />
                    <div class="arfdelete_modal_msg delete_confirm_message"><?php echo sprintf(addslashes(esc_html__('Are you sure you want to %s delete this entry?', 'ARForms')),'<br/>'); ?></div>
                    <div class="arf_delete_modal_row delete_popup_footer">
                        <input type="hidden" value="false" id="bulk_delete_flag"/>
                        <button class="rounded_button add_button arf_delete_modal_left arfdelete_color_red" onclick="arf_delete_bulk_form('true');">&nbsp;<?php echo addslashes(esc_html__('Okay', 'ARForms')); ?></button>&nbsp;&nbsp;<button class="arf_delete_modal_right rounded_button delete_button arfdelete_color_gray" onclick="jQuery('.arf_popup_container,.arf_modal_overlay').removeClass('arfactive');">&nbsp;<?php echo addslashes(esc_html__('Cancel', 'ARForms')); ?></button>
                    </div>
                </div>
            </div>
            <div class="arf_modal_overlay arf_whole_screen">
                <div id="form_previewmodal" class="arf_popup_container" style="overflow:hidden;">
                    <div class="arf_preview_model_header" style="z-index:1;">
                        <div class="arf_preview_model_header_icons">
                            <div onclick="arfchangedevice('computer');" title="<?php echo addslashes(esc_html__('Computer View', 'ARForms')); ?>" class="arfdevicesbg arfhelptip arf_preview_model_device_icon"><div id="arfcomputer" class="arfdevices arfactive"><svg width="75px" height="60px" viewBox="-16 -14 75 60"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#ffffff" d="M40.561,28.591H24.996v2.996h8.107c0.779,0,1.434,0.28,1.434,1.059  c0,0.779-0.655,0.935-1.434,0.935H9.951c-0.779,0-1.435-0.156-1.435-0.935c0-0.778,0.656-1.059,1.435-1.059h8.045v-2.996H2.452  c-0.779,0-1.435-0.656-1.435-1.435V2.086c0-0.779,0.656-1.434,1.435-1.434h38.109c0.778,0,1.434,0.655,1.434,1.434v25.071  C41.995,27.936,41.339,28.591,40.561,28.591z M22.996,31.587v-2.996h-3v2.996H22.996z M39.995,2.642H3.017v23.895h36.978V2.642z"/></svg></div></div>
                            <div onclick="arfchangedevice('tablet');" title="<?php echo addslashes(esc_html__('Tablet View', 'ARForms')); ?>" class="arfdevicesbg arfhelptip arf_preview_model_device_icon"><div id="arftablet" class="arfdevices"><svg width="40px" height="60px" viewBox="-6 -15 40 60"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#ffffff" d="M23.091,33.642H4.088c-1.657,0-3-1.021-3-2.28V2.816  c0-1.259,1.343-2.28,3-2.28h19.003c1.657,0,3,1.021,3,2.28v28.546C26.091,32.622,24.749,33.642,23.091,33.642z M4.955,31.685h17.262  c1.035,0,1.875-0.638,1.875-1.425v-4.694H3.08v4.694C3.08,31.047,3.92,31.685,4.955,31.685z M24.092,4.002  c0-0.787-0.84-1.425-1.875-1.425H4.955c-1.035,0-1.875,0.638-1.875,1.425v1.563h21.012V4.002z M3.08,7.566v16h21.012v-16H3.08z   M13.618,26.551c1.09,0,1.974,0.896,1.974,2s-0.884,2-1.974,2c-1.09,0-1.974-0.896-1.974-2S12.527,26.551,13.618,26.551zz"/></svg></div></div>
                            <div onclick="arfchangedevice('mobile');" title="<?php echo addslashes(esc_html__('Mobile View', 'ARForms')); ?>" class="arfdevicesbg arfhelptip arf_preview_model_device_icon"><div id="arfmobile" class="arfdevices"><svg width="45px" height="60px" viewBox="-12 -15 45 60"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#ffffff" d="M17.894,33.726H3.452c-1.259,0-2.28-1.021-2.28-2.28V2.899  c0-1.259,1.021-2.28,2.28-2.28h14.442c1.259,0,2.28,1.021,2.28,2.28v28.546C20.174,32.705,19.153,33.726,17.894,33.726z   M18.18,4.086c0-0.787-0.638-1.425-1.425-1.425H4.585c-0.787,0-1.425,0.638-1.425,1.425v26.258c0,0.787,0.638,1.425,1.425,1.425  h12.169c0.787,0,1.425-0.638,1.425-1.425V4.086z M13.787,6.656H7.568c-0.252,0-0.456-0.43-0.456-0.959s0.204-0.959,0.456-0.959  h6.218c0.251,0,0.456,0.429,0.456,0.959S14.038,6.656,13.787,6.656z M10.693,25.635c1.104,0,2,0.896,2,2c0,1.105-0.895,2-2,2  c-1.105,0-2-0.895-2-2C8.693,26.53,9.588,25.635,10.693,25.635z"/></svg></div></div>
                        </div>
                        <div class="arf_popup_header_close_button arf_preview_close" data-dismiss="arfmodal">
                            <svg width="16px" height="16px" viewBox="0 0 12 12"><path fill-rule="evenodd" clip-rule="evenodd" fill="#ffffff" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></svg>
                        </div>
                    </div>
                    <div class="arfmodal-body" style=" overflow:hidden; clear:both;padding:0;">
                        <div class="iframe_loader" align="center"><?php echo ARF_LOADER_ICON; ?></div>
                        <iframe id="arfdevicepreview" name="arf_preview_frame" src="" frameborder="0" height="100%" width="100%"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="clear:both;"></div>
    <?php
        if (is_rtl()) {
            $doc_link_align = 'left';
        }
        else {
            $doc_link_align = 'right';
        }
    ?>
    <div class="documentation_link" style="background:none; background:none; padding-top:40px;" align="<?php echo $doc_link_align; ?>"><a href="<?php echo ARFURL; ?>/documentation/index.html" style="margin-right:10px;" target="_blank"><?php echo addslashes(esc_html__('Documentation', 'ARForms')); ?></a>|<a href="https://helpdesk.arpluginshop.com/submit-a-ticket/" style="margin-left:10px;" target="_blank"><?php echo addslashes(esc_html__('Support', 'ARForms')); ?></a> &nbsp;&nbsp;<img src="<?php echo ARFURL; ?>/images/dot.png" height="10" width="10" onclick="javascript:OpenInNewTab('<?php echo ARFURL; ?>/documentation/assets/sysinfo.php');" /></div>
</div>
<script type="text/javascript" data-cfasync="false">
    function ChangeID(id) {
        document.getElementById('delete_id').value = id;
    }
</script>