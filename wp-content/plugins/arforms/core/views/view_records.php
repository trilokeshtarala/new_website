<?php
global $armainhelper, $arformhelper, $arrecordhelper, $arrecordcontroller, $arfversion, $arfieldhelper;
$arf_edit_select_array = array();

$tabview = (isset($_GET['tabview']) && !empty($_GET['tabview'])) ? $_GET['tabview'] : '';


$_GET['form'] = isset($_GET['form']) ? $_GET['form'] : -1;

$form_id = $_GET['form'];

function getBrowser($user_agent) {
    $u_agent = $user_agent;
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version = "";


    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
    } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
    } elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
    }


    if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
        $bname = 'Internet Explorer';
        $ub = "MSIE";
    } elseif (preg_match('/Trident/i', $u_agent)) {
        $bname = 'Internet Explorer';
        $ub = "rv";
    } elseif (preg_match('/OPR/i', $u_agent)) {
        $bname = 'Opera';
        $ub = "OPR";
    } elseif (preg_match('/Firefox/i', $u_agent)) {
        $bname = 'Mozilla Firefox';
        $ub = "Firefox";
    } elseif (preg_match('/Edge/i', $u_agent)) {
        $bname = 'Edge';
        $ub = "Edge";
    } elseif (preg_match('/Chrome/i', $u_agent)) {
        $bname = 'Google Chrome';
        $ub = "Chrome";
    } elseif (preg_match('/Safari/i', $u_agent)) {
        $bname = 'Apple Safari';
        $ub = "Safari";
    } elseif (preg_match('/Opera/i', $u_agent)) {
        $bname = 'Opera';
        $ub = "Opera";
    } elseif (preg_match('/Netscape/i', $u_agent)) {
        $bname = 'Netscape';
        $ub = "Netscape";
    }


    $known = array('Version', $ub, 'other');

    $pattern = '#(?<browser>' . join('|', $known) .
            ')[/ |:]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        
    }


    $i = count($matches['browser']);
    if ($i != 1) {

        if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
            $version = $matches['version'][0];
        } else {
            $version = $matches['version'][1];
        }
    } else {
        $version = $matches['version'][0];
    }


    if ($version == null || $version == "") {
        $version = "?";
    }

    return array(
        'userAgent' => $u_agent,
        'name' => $bname,
        'version' => $version,
        'platform' => $platform,
        'pattern' => $pattern
    );
}

$actions = array('bulk_delete' => addslashes(esc_html__('Delete', 'ARForms')));
if( current_user_can('arfchangesettings') ){
    $actions['bulk_csv'] = addslashes(esc_html__('Export to CSV', 'ARForms'));
}
?>
<style type="text/css" title="currentStyle">
    @import "<?php echo ARFURL; ?>/datatables/media/css/demo_page.css";
    @import "<?php echo ARFURL; ?>/datatables/media/css/demo_table_jui.css";
    @import "<?php echo ARFURL; ?>/datatables/media/css/jquery-ui-1.8.4.custom.css";
    @import "<?php echo ARFURL; ?>/datatables/media/css/ColVis.css";
</style>
<style>
    .chart_previous{
        float:right;
        text-decoration:underline;
    }
    .chart_next{
        float:right;
        text-decoration:underline;
    }
    #poststuff #post-body {
        margin-top: 35px !important;
    }

    #post-body {
        background:none;
    }
    .arf_cal_header {
        background-color: #66aaff!important;
        color: #ffffff;
        border-bottom: 1px solid #ffffff!important;
    }
    .arf_cal_month {
        background-color: #66aaff!important;
        color: #ffffff;
        border-bottom: 1px solid #66aaff!important;
    }
    .arf_selectbox[data-name="arfredirecttolist"] ul{
        width:412px !important;
    }
    #form_entries .bootstrap-datetimepicker-widget table td.active,
    #form_entries .bootstrap-datetimepicker-widget table td.active:hover {
    	color: #66aaff; 
    	background-image : url("data:image/svg+xml;utf8,<svg width='35px' xmlns='http://www.w3.org/2000/svg' height='29px'><path fill='rgb(0,126,228)' d='M15.732,27.748c0,0-14.495,0.2-14.71-11.834c0,0,0.087-7.377,7.161-11.82 c0,0,0.733-0.993-1.294-0.259c0,0-1.855,0.431-3.538,2.2c0,0-1.078,0.216-0.388-1.381c0,0,2.416-3.019,8.585-2.76 c0,0,2.372-2.458,7.419-1.293c0,0,0.819,0.517-0.518,0.819c0,0-5.361,0.514-3.753,1.122c0,0,14.021,3.073,14.322,13.943 C29.019,16.484,29.573,27.32,15.732,27.748z M26.991,16.182C26.24,7.404,14.389,3.543,14.389,3.543 c-2.693-0.747-4.285,0.683-4.285,0.683C8.767,4.969,6.583,7.804,6.583,7.804C2.216,13.627,3.612,18.47,3.612,18.47 c2.168,7.635,12.505,7.097,12.505,7.097C27.376,25.418,26.991,16.182,26.991,16.182z'/></svg>") !important;
    }
</style>

<?php
if (isset($form->id) && $form->id == '-1') {
    $form_cols = array();
    $items = array();
}

if (isset($form->id) and ( $form->id != '-1' || $form->id != '')) {
    
    $form_cols = apply_filters('arfpredisplayformcols', $form_cols, $form->id);
    $items = apply_filters('arfpredisplaycolsitems', $items, $form->id);

    $action_no = 0;

    $default_hide = array(
        '0' => '',
        '1' => 'ID',
    );

    if (count($form_cols) > 0) {

        for ($i = 2; 1 + count($form_cols) >= $i; $i++) {
            $j = $i - 2;
            $default_hide[$i] = $armainhelper->truncate($form_cols[$j]->name, 40);
        }
        $default_hide[$i] = 'Entry Key';
        $default_hide[$i + 1] = 'Entry creation date';
        $default_hide[$i + 2] = 'Browser Name';
        $default_hide[$i + 3] = 'IP Address';
        $default_hide[$i + 4] = 'Country';
        $default_hide[$i + 5] = 'Page URL';
        $default_hide[$i + 6] = 'Referrer URL';
        $default_hide[$i + 7] = 'Action';
        $action_no = $i + 7;
    } else {
        $default_hide['2'] = 'Entry Key';
        $default_hide['3'] = 'Entry creation date';
        $default_hide['4'] = 'Browser Name';
        $default_hide['5'] = 'IP Address';
        $default_hide['6'] = 'Country';
        $default_hide['7'] = 'Page URL';
        $default_hide['8'] = 'Referrer URL';
        $default_hide['9'] = 'Action';
        $action_no = 9;
    }

    global $wpdb, $MdlDb;


    $page_params = "&action=0&arfaction=0&form=";

    $page_params .= ($form) ? $form->id : 0;

    if (!empty($_REQUEST['fid'])) {
        $page_params .= '&fid=' . $_REQUEST['fid'];
    }

    $item_vars = $this->get_sort_vars($params, $where_clause);

    $page_params .= ($page_params_ov) ? $page_params_ov : $item_vars['page_params'];

    if ($form) {
        
    } else {
        $form_cols = array();
        $record_where = $item_vars['where_clause'];
    }

    $columns_list_res = $wpdb->get_results($wpdb->prepare('SELECT columns_list FROM ' . $MdlDb->forms . ' WHERE id = %d', $form->id), ARRAY_A);
    $columns_list_res = $columns_list_res[0];

    $columns_list = (!empty($columns_list_res['columns_list'])) ? maybe_unserialize($columns_list_res['columns_list']) : array();
    $is_colmn_array = is_array($columns_list);

    $exclude = '';

    $exclude_array = array();
    if (count($columns_list) > 0 and $columns_list != '') {

        foreach ($columns_list as $keys => $column) {
            $exclude_no = 0;
            foreach ($default_hide as $key => $val) {

                if ($column == $val) {
                    if ($exclude_array == "") {
                        $exclude_array[] = $key;
                    } else {
                        if (!in_array($key, $exclude_array)) {
                            $exclude_array[] = $key;
                            $exclude_no++;
                        }
                    }
                }
            }
        }
    }


    $ipcolumn = ($action_no - 4);
    $page_url_column = ($action_no - 2);
    $referrer_url_column = ($action_no - 1);

    if ($exclude_array == "" and ! $is_colmn_array) {
        $exclude_array = array($ipcolumn, $page_url_column, $referrer_url_column);
    } else if (is_array($exclude_array) and ! $is_colmn_array) {
        if (!in_array($ipcolumn, $exclude_array)) {
            array_push($exclude_array, $ipcolumn);
        }
        if (!in_array($page_url_column, $exclude_array)) {
            array_push($exclude_array, $page_url_column);
        }
        if (!in_array($referrer_url_column, $exclude_array)) {
            array_push($exclude_array, $referrer_url_column);
        }
    }
} else {

    $action_no = 9;
    $exclude_array = array(5, 7, 8);
}

if (isset($exclude_array) and $exclude_array != "") {
    $exclude = implode(",", $exclude_array);
}

wp_enqueue_script('jquery');
wp_enqueue_script('jquery-ui-core');
wp_enqueue_style('bootstrap-editable-css', ARFURL . '/bootstrap/css/bootstrap-editable.css', array(), $arfversion);
wp_enqueue_script('bootstrap-editable-js', ARFURL . '/bootstrap/js/bootstrap-editable.js', array(), $arfversion);

global $style_settings;

$wp_format_date = get_option('date_format');

if ($wp_format_date == 'F j, Y') {
    $date_format_new = 'MMMM D, YYYY';
    $date_format_new1 = 'MMMM D, YYYY';
    $start_date_new = 'January 01, 1970';
    $end_date_new = 'December 31, 2050';
} else if($wp_format_date == 'Y-m-d'){
    $date_format_new = 'YYYY-MM-DD';
    $date_format_new1 = 'YYYY-MM-DD';
    $start_date_new = '1970-1-1';
    $end_date_new = '2050-12-1';
} else if ($wp_format_date == 'm/d/Y') {
    $date_format_new = 'MM/DD/YYYY';
    $date_format_new1 = 'MM-DD-YYYY';
    $start_date_new = '01/01/1970';
    $end_date_new = '12/31/2050';
} else if ($wp_format_date == 'd/m/Y') {
    $date_format_new = 'DD/MM/YYYY';
    $date_format_new1 = 'DD-MM-YYYY';
    $start_date_new = '01/01/1970';
    $end_date_new = '31/12/2050';
} else if ($wp_format_date == 'Y/m/d') {
    $date_format_new = 'DD/MM/YYYY';
    $date_format_new1 = 'DD-MM-YYYY';
    $start_date_new = '01/01/1970';
    $end_date_new = '31/12/2050';
} else {
    $date_format_new = 'MM/DD/YYYY';
    $date_format_new1 = 'MM-DD-YYYY';
    $start_date_new = '01/01/1970';
    $end_date_new = '12/31/2050';
}


global $arf_entries_action_column_width;
?>

<script type="text/javascript" charset="utf-8" data-cfasync="false">

    __ARF_LOADER_ICON = '<?php echo ARF_LOADER_ICON; ?>';
    function arf_dataTable_pagination() {
        var options = {
            "fnInit": function (oSettings, nPaging, fnCallbackDraw)
            {
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

                    if (e.which == 38 || e.which == 39)
                    {
                        this.value++;
                    }
                    else if ((e.which == 37 || e.which == 40) && this.value > 1)
                    {
                        this.value--;
                    }

                    if (this.value == "" || this.value.match(/[^0-9]/))
                    {

                        return;
                    }

                    var iNewStart = oSettings._iDisplayLength * (this.value - 1);
                    if (iNewStart > oSettings.fnRecordsDisplay())
                    {

                        oSettings._iDisplayStart = (Math.ceil((oSettings.fnRecordsDisplay() - 1) /
                                oSettings._iDisplayLength) - 1) * oSettings._iDisplayLength;
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


                jQuery(nFirst).bind('selectstart', function () {
                    return false;
                });
                jQuery(nPrevious).bind('selectstart', function () {
                    return false;
                });
                jQuery('span', nPaging).bind('mousedown', function () {
                    return false;
                });
                jQuery('span', nPaging).bind('selectstart', function () {
                    return false;
                });
                jQuery(nNext).bind('selectstart', function () {
                    return false;
                });
                jQuery(nLast).bind('selectstart', function () {
                    return false;
                });
            },
            "fnUpdate": function (oSettings, fnCallbackDraw)
            {
                if (!oSettings.aanFeatures.p)
                {
                    return;
                }


                var an = oSettings.aanFeatures.p;
                for (var i = 0, iLen = an.length; i < iLen; i++)
                {
                    var buttons = an[i].getElementsByTagName('span');
                    if (oSettings._iDisplayStart === 0)
                    {

                        buttons[1].className = "paginate_disabled_first arfhelptip";
                        buttons[2].className = "paginate_disabled_previous arfhelptip";
                    }
                    else
                    {

                        buttons[1].className = "paginate_enabled_first arfhelptip";
                        buttons[2].className = "paginate_enabled_previous arfhelptip";
                    }

                    if (oSettings.fnDisplayEnd() == oSettings.fnRecordsDisplay())
                    {
                        buttons[4].className = "paginate_disabled_next arfhelptip";
                        buttons[5].className = "paginate_disabled_last arfhelptip";
                    }
                    else
                    {

                        buttons[4].className = "paginate_enabled_next arfhelptip";
                        buttons[5].className = "paginate_enabled_last arfhelptip";
                    }


                    if (!oSettings.aanFeatures.p)
                    {
                        return;
                    }
                    var iPages = Math.ceil((oSettings.fnRecordsDisplay()) / oSettings._iDisplayLength);

                    var iCurrentPage = Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength) + 1;


                    if (iPages == 0 && iCurrentPage == 1) {
                        iPages = iPages + 1;
                    }

                    if (document.getElementById('of_grid')) {
                        of_grid = document.getElementById('of_grid').value;
                    } else {
                        of_grid = 'of';
                    }
                    var an = oSettings.aanFeatures.p;
                    for (var i = 0, iLen = an.length; i < iLen; i++)
                    {
                        var spans = an[i].getElementsByTagName('span');
                        var inputs = an[i].getElementsByTagName('input');
                        spans[spans.length - 3].innerHTML = " " + of_grid + " " + iPages
                        inputs[0].value = iCurrentPage;
                    }


                }
            }
        };
        return options;
    }
// <![CDATA[
    jQuery(document).ready(function () {

        var data_array_size = <?php echo count($items); ?>;

        if (data_array_size > 0) {
            var paginate = true;
        } else {
            var paginate = false;
        }
        jQuery.fn.dataTableExt.oPagination.four_button = arf_dataTable_pagination();

        <?php
            if (isset($_GET['form']) && $_GET['form'] > 0 && count($items) > 0) {
                ?>
                arf_load_form_entries_grid(paginate);
                <?php
            }
        ?>

        jQuery("#datepicker_from").datetimepicker({
            useCurrent: true,
            format: '<?php echo $date_format_new; ?>',
            locale: '<?php echo (isset($options['locale'])) ? $options['locale'] : ''; ?>',
            minDate: moment('<?php echo $start_date_new; ?>', '<?php echo $date_format_new1; ?>'),
            maxDate: moment('<?php echo $end_date_new; ?>', '<?php echo $date_format_new1; ?>')
        });

        jQuery("#datepicker_to").datetimepicker({
            useCurrent: false,
            format: '<?php echo $date_format_new; ?>',
            locale: '<?php echo (isset($options['locale'])) ? $options['locale'] : ''; ?>',
            minDate: moment('<?php echo $start_date_new; ?>', '<?php echo $date_format_new1; ?>'),
            maxDate: moment('<?php echo $end_date_new; ?>', '<?php echo $date_format_new1; ?>')
        });

        jQuery("#datepicker_from").on("dp.change", function (e) {
            jQuery("#datepicker_to").data("DateTimePicker").minDate(e.date);
        });
        jQuery("#datepicker_to").on("dp.change", function (e) {
            jQuery("#datepicker_from").data("DateTimePicker").maxDate(e.date);
        });

        <?php
            if (!isset($_GET['form']) || ( isset($_GET['form']) && $_GET['form'] < 1) || count($items) <= 0) {
            ?>
                var dataTableOpt = {
                    "oLanguage": {
                        "sProcessing": "",
                        "sEmptyTable": "There is no entry found",
                        "sZeroRecords": "There is no entry found"
                    },
                    "sDom": '<"H"lCfr>t<"footer"ip>',
                    "sPaginationType": "four_button",
                    "bJQueryUI": true,
                    "bPaginate": paginate,
                    "bInfo": paginate,
                    "bAutoWidth": false,
                    "bScrollCollapse": true,
                    "oColVis": {
                        "aiExclude": [0, <?php echo ( isset($action_no) ) ? $action_no : ''; ?>]
                    },
                    "aoColumnDefs": [
                        {"sType": "html", "bVisible": false, "aTargets": [<?php if (isset($exclude) and $exclude != '') echo $exclude; ?>]},
                        {"bSortable": false, "aTargets": [0, <?php echo ( isset($action_no) ) ? $action_no : ''; ?>]}
                    ],
                    "fnDrawCallback": function (oSettings) {
                        jQuery(".arf_loader_icon_wrapper").hide();
                        jQuery('.arfhelptip').tipso('destroy');
                        jQuery('.arfhelptip').tipso({
                            position: 'top',
                            maxWidth: '400',
                            useTitle: true,
                            background: '#444444',
                            color: '#ffffff',
                            width: 'auto'
                        });
                    }
                };

                var oTables = jQuery('#example').dataTable(dataTableOpt);

                new FixedColumns(oTables, {
                    "iLeftColumns": 0,
                    "iLeftWidth": 0,
                    "iRightColumns": 0,
                    "iRightWidth": 0,
                });
            <?php
            }
        ?>

        var wrapper_width = jQuery(".frm_entries_page #example_wrapper").outerWidth();
        var table_head_width = jQuery(".frm_entries_page table#example thead").outerWidth();

        if( table_head_width >= wrapper_width ){
            jQuery(".frm_entries_page table#example").css('display','block');
        } else {
            jQuery(".frm_entries_page table#example").css('display','');
        }
    });

    function arf_load_form_entries_grid(paginate,arftotalrecords){

            var form = jQuery("#arfredirecttolist").val();
            var start_date = jQuery("#datepicker_from").val();
            var end_date = jQuery("#datepicker_to").val();
            var please_select_form = jQuery("#please_select_form").val();
            var action_column_width = jQuery("#action_column_width").val();
            var per_page_rec = (document.querySelector('[name="example_length"]') != null) ? document.querySelector('[name="example_length"]').value : 10;
            var current_page = document.getElementsByClassName('current_page_no')[0] || 1;
            if (form == '' || typeof form == 'undefined') {
                alert(please_select_form);
                return false;
            }

            var oTableOpt = {
                "oLanguage": {
                    "sProcessing": "",
                    "sEmptyTable": "There is no entry found",
                    "sZeroRecords": "There is no entry found"
                },
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": ajaxurl,
                "sServerMethod": "POST",
                "fnServerParams": function (aoData) {
                    aoData.push({'name': 'action', 'value': 'arf_retrieve_form_entry'});
                    aoData.push({'name': 'form', 'value': form});
                    aoData.push({'name': 'start_date', 'value': start_date});
                    aoData.push({'name': 'end_date', 'value': end_date});
                },
                "bRetrieve": false,
                "sDom": '<"H"lCfr>t<"footer"ip>',
                "sPaginationType": "four_button",
                "bJQueryUI": true,
                "bPaginate": paginate,
                "bAutoWidth": false,
                "bInfo": paginate,
                "bScrollCollapse": true,
                "oColVis": {
                    "aiExclude": [0, <?php echo ( isset($action_no) ) ? $action_no : ''; ?>]
                },
                "fixedColumns": true,
                "aaSorting": [[1, 'desc']],
                "aLengthMenu": [10, 25, 50, 100, 200],
                "aoColumnDefs": [
                    { "sType": "html", "bVisible": false, "aTargets": [<?php if (isset($exclude) and $exclude != '') echo $exclude; ?>] }, 
                    { "sClass": "box", "aTargets": [0] }, 
                    { "sClass": "arf_col_action arf_action_cell", "aTargets": [<?php echo ( isset($action_no) ) ? $action_no : ''; ?>] }, 
                    { "bSortable": false, "aTargets": [0, <?php echo ( isset($action_no) ) ? $action_no : ''; ?>] }
                ],
                
                "fnPreDrawCallback": function () {
                    jQuery(".arf_loader_icon_wrapper").show();
                },
                "fnDrawCallback": function (oSettings) {

                    jQuery(".arf_loader_icon_wrapper").hide();
                    jQuery("#cb-select-all-1").removeAttr("checked");
                    if(arftotalrecords != '' && arftotalrecords != undefined){
                        var form_list_item = jQuery('ul[data-id="arfredirecttolist"]').find('li[data-value="'+form+'"]');
                        var label = form_list_item.attr('data-label');
                        var text = form_list_item.html();

                        var pattern = /\(\d+\)/gi;
                        var matches, matches2;
                        if( matches = label.match(pattern) ){
                            var match_len = matches.length;
                            for( var m1 = 0; m1 < match_len; m1++ ){
                                var temp_counter = matches[m1];
                                label = label.replace(temp_counter,'('+arftotalrecords+')');
                            }
                            form_list_item.attr('data-label',label);
                        }
                        if( matches2 = text.match(pattern) ){
                            var match_len = matches2.length;
                            for( var m1 = 0; m1 < match_len; m1++ ){
                                var temp_counter = matches2[m1];
                                text = text.replace(temp_counter,'('+arftotalrecords+')');
                            }
                            form_list_item.html(text);
                            jQuery('dl[data-name="arfredirecttolist"] dt span').html(text);
                        }
                    }

                    var wrapper_width = jQuery(".frm_entries_page #example_wrapper").outerWidth();
                    var table_head_width = jQuery(".frm_entries_page table#example thead").outerWidth();

                    if( table_head_width >= wrapper_width ){
                        jQuery(".frm_entries_page table#example").css('display','block');
                    } else {
                        jQuery(".frm_entries_page table#example").css('display','');
                    }

                    jQuery('.arfhelptip').tipso('destroy');
                    jQuery('.arfhelptip').tipso({
                        position: 'top',
                        maxWidth: '400',
                        useTitle: true,
                        background: '#444444',
                        color: '#ffffff',
                        width: 'auto'
                    });
                }
            };
            var oTables = jQuery('#example').dataTable(oTableOpt);

    }

    jQuery(document).on('click', '.ColVis_Button:not(.ColVis_MasterButton)', function () {
        var colsArray = jQuery('.ColVis_Button :checkbox').map(function () {
            return [[jQuery(this).parent().next('.ColVis_title').text(), this.checked ? 'visibile' : 'hidden']];
        }).get();

        var form = jQuery('#arfredirecttolist').val();

        if (form == '') {
            return false;
        }

        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: "action=managecolumns&colsArray=" + colsArray + "&form=" + form,
            success: function (msg) {
                var wrapper_width = jQuery(".frm_entries_page #example_wrapper").outerWidth();
                var table_head_width = jQuery(".frm_entries_page table#example thead").outerWidth();
                
                if( table_head_width >= wrapper_width ){
                    jQuery(".frm_entries_page table#example").css('display','block');
                } else {
                    jQuery(".frm_entries_page table#example").css('display','');
                }
            }
        });

    });



// ]]>

    jQuery(document).on('click', "#cb-select-all-1", function () {
        jQuery('input[name="item-action[]"]').attr('checked', this.checked);
    });

    jQuery(document).on('click', 'input[name="item-action[]"]', function () {

        if (jQuery('input[name="item-action[]"]').length == jQuery('input[name="item-action[]"]:checked').length) {
            jQuery("#cb-select-all-1").attr("checked", "checked");
        } else {
            jQuery("#cb-select-all-1").removeAttr("checked");
        }

    });

    jQuery(document).ready(function () {

        <?php if ($tabview == 'analytics') { ?>
            show_form_settimgs('analytics', 'form_entries');
        <?php } else { ?>
            show_form_settimgs('form_entries', 'analytics');
        <?php } ?>
    });

    function show_form_settimgs(id1, id2)
    {
        if (id1 == "analytics")
        {
            if (!jQuery.isFunction(change_graph_new)) {
                return;
            }
            change_graph_new("monthly");
        }
        document.getElementById(id1).style.display = 'block';
        document.getElementById(id2).style.display = 'none';
        document.getElementById('arfcurrenttab').value = id1;

        jQuery('.' + id1).addClass('btn_sld').removeClass('tab-unselected');
        jQuery('#' + id1 + '_img').attr('src', '<?php echo ARFIMAGESURL; ?>/' + id1 + '.png');
        jQuery('.' + id2).removeClass('btn_sld').addClass('tab-unselected');
        jQuery('#' + id2 + '_img').attr('src', '<?php echo ARFIMAGESURL; ?>/' + id2 + '_hover.png');
    }
</script> 
<?php 
    if( $_GET['form'] < 0 ){
        echo str_replace('id="{arf_id}"','id="arf_full_width_loader" style="display:none;" ',ARF_LOADER_ICON);
    } else {
        if( ( isset($_GET['tabview']) && 'analytics'==$_GET['tabview'] ) && $_GET['form'] > 0 ){
            echo str_replace('id="{arf_id}"','id="arf_full_width_loader" style="display:none;" ',ARF_LOADER_ICON);
        } else{
            echo str_replace('id="{arf_id}"','id="arf_full_width_loader" ',ARF_LOADER_ICON);    
        }
        
    }
?>
<div class="wrap frm_entries_page">
    <div class="top_bar">
        <span class="h2"><?php echo addslashes(esc_html__('Form Entries', 'ARForms')); ?></span>
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
                <div class="formsettings1" style="background-color:#ffffff;">
                    <div class="setting_tabrow">
                        <div class="arftab" style="padding-left:0px;">
                            <ul class="arfmainformnavigation" style="height:43px !important; padding-bottom:0px; margin-bottom:0px;">
                                <li class="form_entries btn_sld"> <a href="javascript:show_form_settimgs('form_entries','analytics');"><?php echo addslashes(esc_html__('Entries List', 'ARForms')); ?></a></li>
                                <li class="analytics tab-unselected"> <a href="javascript:show_form_settimgs('analytics','form_entries');"><?php echo addslashes(esc_html__('Analytics', 'ARForms')); ?></a></li>
                            </ul>
                        </div>
                    </div>


                    <input type="hidden" name="action_column_width" id="action_column_width" value="<?php echo isset($arf_entries_action_column_width) ? $arf_entries_action_column_width : '120'; ?>" />
                    <div class="frm_settings_form">

                        <input type="hidden" name="arfcurrenttab" id="arfcurrenttab" value="form_entries" />

                        <input type="hidden" name="arfformentriesurl" id="arfformentriesurl" value="<?php echo esc_url(admin_url('admin.php') . "?page=ARForms-entries"); ?>" />

                        <div id="form_entries">
                            <div class="arf_form_entry_select">
                                <table class="arf_form_entry_select_sub">
                                    <tr>
                                        <th class="arf_form_entry_left" style="float:none;<?php (is_rtl())? 'text-align:right;' : 'text-align:left;';?>"><?php echo addslashes(esc_html__("Select form", 'ARForms')); ?></th>
                                        <th class="arf_form_entry_left"><?php echo addslashes(esc_html__('Select Date', 'ARForms')); ?> (<?php echo addslashes(esc_html__('optional', 'ARForms')); ?>)</th>
                                    </tr>
                                    <tr>
                                        <td><div class="sltstandard" style="float:none; width: 400px !important;<?php echo (is_rtl())? 'margin-left:60px;' : 'margin-right:60px;';?>margin-top:-16px;"><?php $arformhelper->forms_dropdown('arfredirecttolist', $_GET['form'], addslashes(esc_html__('Select Form', 'ARForms')), false, ""); ?></div></td>
                                        <td>
                                            <?php
                                            if (is_rtl()) {
                                                $sel_frm_date_wrap = 'float:right;text-align:right;';
                                                $sel_frm_sel_date = 'float:right;';
                                                $sel_frm_button = 'float:right;';
                                            } else {
                                                $sel_frm_date_wrap = 'float:left;text-align:left;';
                                                $sel_frm_sel_date = 'float:left;';
                                                $sel_frm_button = 'float:left;';
                                            }
                                            ?>
                                            <div style="position:relative; <?php echo $sel_frm_date_wrap; ?>">
                                                <div style="<?php echo $sel_frm_sel_date; ?>"><div class="arfentrytitle" style='margin-left:0;'><?php echo addslashes(esc_html__('From', 'ARForms')); ?></div><input type="text" class="txtmodal1" value="<?php echo (isset($_GET['start_date'])) ? $_GET['start_date'] : ''; ?>" id="datepicker_from" name="datepicker_from" style="width:120px;height:35px;vertical-align:middle; " /></div> <div class="arfentrytitle"><?php echo addslashes(esc_html__('To', 'ARForms')); ?></div>&nbsp;&nbsp;<div style="<?php echo $sel_frm_sel_date; ?>"><input type="text" class="txtmodal1" value="<?php echo (isset($_GET['end_date'])) ? $_GET['end_date'] : ''; ?>" id="datepicker_to" name="datepicker_to" style="vertical-align:middle; width:120px;height:35px;"/></div>
                                                <div style=" <?php echo $sel_frm_button; ?>">
                                                    <div class="arf_form_entry_left">&nbsp;</div>
                                                    <div style="float:left;text-align:left;"><button type="button" class="rounded_button arf_btn_dark_blue" onclick="change_frm_entries();" style="width: 35px !important;height: 35px;"><?php echo addslashes(esc_html__('Go', 'ARForms')); ?></button></div>
                                                </div>
                                                <input type="hidden" name="please_select_form" id="please_select_form" value="<?php echo addslashes(esc_html__('Please select a form', 'ARForms')); ?>" />
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div style="clear:both; height:30px;"></div>

                            <?php do_action('arfbeforelistingentries'); ?>

                            <form method="get" id="list_entry_form" class="arf_list_entries_form" onsubmit="return apply_bulk_action();" style="float:left;width:98%;padding-left: 15px;">

                                <input type="hidden" name="page" value="ARForms-entries" />

                                <input type="hidden" name="form" value="<?php echo ($form) ? $form->id : '-1'; ?>" />

                                <input type="hidden" name="arfaction" value="list" />

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

                                <?php require(VIEWS_PATH . '/shared_errors.php'); ?>

                                <?php $two = '1'; ?>
                                <div class="alignleft actions">
                                    <div class="arf_list_bulk_action_wrapper">
                                        <input id="arf_bulk_action_one" name="action<?php echo $two; ?>" value="-1" type="hidden">
                                        <dl class="arf_selectbox" data-name="action<?php echo $two; ?>" data-id="arf_bulk_action_one">
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
                                    <input type="submit" id="doaction<?php echo $two; ?>" class="arf_bulk_action_btn rounded_button btn_green" value="<?php echo addslashes(esc_html__('Apply', 'ARForms')); ?>"/>
                                </div>

                                <table cellpadding="0" cellspacing="0" border="0" class="display table_grid import_export_entries" id="example">
                                    <thead>
                                        <tr>
                                            <th class="box">
                                                <div style="display:inline-block; position:relative;">
                                                    <div class="arf_custom_checkbox_div arfmarginl15">
                                                        <div class="arf_custom_checkbox_wrapper arfmargin10custom">
                                                            <input id="cb-select-all-1" type="checkbox" class="">
                                                            <svg width="18px" height="18px">
                                                            <?php echo ARF_CUSTOM_UNCHECKED_ICON; ?>
                                                            <?php echo ARF_CUSTOM_CHECKED_ICON; ?>
                                                            </svg>
                                                        </div>
                                                    </div>

                                                    <label for="cb-select-all-1"  class="cb-select-all"><span class="cb-select-all-checkbox"></span></label>
                                                </div>
                                            </th>
                                            <th><?php echo addslashes(esc_html__('ID', 'ARForms')); ?></th>
                                            <?php
                                            if (count($form_cols) > 0) {
                                                foreach ($form_cols as $col) {
                                                    ?>
                                                    <th><?php echo $armainhelper->truncate($col->name, 40) ?></th>
                                                    <?php
                                                }
                                            }
                                            ?>
                                            <th><?php echo esc_html__('Entry Key', 'ARForms'); ?></th>
                                            <th><?php echo addslashes(esc_html__('Entry creation date', 'ARForms')); ?></th>
                                            <th><?php echo addslashes(esc_html__('Browser Name', 'ARForms')); ?></th>
                                            <th><?php echo addslashes(esc_html__('IP Address', 'ARForms')); ?></th>
                                            <th><?php echo addslashes(esc_html__('Country', 'ARForms')); ?></th>
                                            <th><?php echo esc_html__('Page URL', 'ARForms'); ?></th>
                                            <th><?php echo addslashes(esc_html__('Referrer URL', 'ARForms')); ?></th>
                                            <th class="arf_col_action arf_action_cell"><?php echo addslashes(esc_html__('Action', 'ARForms')); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    <script type="text/javascript" data-cfasync="false">
                                        function ChangeID(id)
                                        {
                                            document.getElementById('delete_entry_id').value = id;
                                        }
                                    </script>

                                    </tbody>
                                </table>
                                

                                <?php $two = '2'; ?>
                                <div class="alignleft actions">
                                    <div class="arf_list_bulk_action_wrapper">
                                        <input id="arf_bulk_action_two" name="action<?php echo $two; ?>" value="-1" type="hidden">
                                        <dl class="arf_selectbox" data-name="action<?php echo $two; ?>" data-id="arf_bulk_action_two">
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
                                    <input type="submit" id="doaction<?php echo $two; ?>" class="arf_bulk_action_btn rounded_button btn_green" value="<?php echo addslashes(esc_html__('Apply', 'ARForms')); ?>"/>
                                </div>

                                <div class="footer_grid"></div>
                            </form>

                            <?php do_action('arfafterlistingentries'); ?>

                            <div style="clear:both;"></div>
                            <br /><br />
                        </div>

                        <div id="analytics" style="padding-top: 50px;display:none;">
                            <?php echo str_replace('{arf_id}','arf_graph_changes_loader',ARF_LOADER_ICON); ?>

                            <table border="0" align="middle" class="arftalbespacing">
                                <tr>
                                    <?php $form_id = isset($form_id) ? $form_id : ''; ?>
                                    <td align="left">
                                        <div class="lblnotetitle arfselectformtitle"><?php echo addslashes(esc_html__('Select form', 'ARForms')); ?> :</div>
                                        <div class="sltstandard" id="arfsltstandard_arfredirecttolist3" style="<?php echo (is_rtl()) ? 'float:right;' : 'float:left;';?>width:348px;"><?php $arformhelper->forms_dropdown('arfredirecttolist3', $form_id, addslashes(esc_html__('All Forms', 'ARForms')), false, "change_graph_new('monthly','1')"); ?></div></td>
                                    
                                    <?php
                                    if (is_rtl()) {
                                        $analytic_time_label = 'float:right;';
                                    } else {
                                        $analytic_time_label = 'float:left;';
                                    }
                                    ?>
                                    <td align="left"><div class="sltstandard" style=" <?php echo $analytic_time_label; ?>">
                                            <div style=" <?php echo $analytic_time_label; ?>margin:0px 5px;">

                                                <button id="daily_unselected" onclick="javascript:change_graph_new('daily');" class="btn_sld_daily"><?php echo addslashes(esc_html__('Daily', 'ARForms')); ?></button>
                                                <button id="daily_selected" onclick="javascript:change_graph_new('daily');" class="btn_sld_daily_selected"><?php echo addslashes(esc_html__('Daily', 'ARForms')); ?></button>
                                            </div>

                                            <div style=" <?php echo $analytic_time_label; ?>margin:0px 5px;">

                                                <button id="monthly_unselected" onclick="javascript:change_graph_new('monthly');" class="btn_sld_monthly"><?php echo addslashes(esc_html__('Monthly', 'ARForms')); ?></button>
                                                <button id="monthly_selected" onclick="javascript:change_graph_new('monthly');" class="btn_sld_monthly_selected"><?php echo addslashes(esc_html__('Monthly', 'ARForms')); ?></button>
                                            </div>
                                            <div style=" <?php echo $analytic_time_label; ?>margin:0px 5px;">
                                                <button id="yearly_unselected" onclick="javascript:change_graph_new('yearly');" class="btn_sld_yearly"><?php echo addslashes(esc_html__('Yearly', 'ARForms')); ?></button>
                                                <button id="yearly_selected" onclick="javascript:change_graph_new('yearly');" class="btn_sld_yearly_selected"><?php echo addslashes(esc_html__('Yearly', 'ARForms')); ?></button>
                                            </div>                                            
                                            <input type="hidden" value="monthly" name="arfgraphval" id="arfgraphval" />
                                        </div></td>
                                    <td align="left" style="<?php echo (is_rtl()) ? 'float:left;' : 'float:right;';?>">                                        
                                        <div class="arfgraphtype" id="arfgraphtype_div_bar" onclick="change_graph_type('bar')"> 
                                            <input type="radio" id="arfgraphtype_bar" value="bar" name="arfgraphtype">
                                            <span class="arfgraphtype_span">
                                                <svg width="30px" height="30px">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M22.232,26.339V14.245h4.003v12.094H22.232z M15.237,7.345h4.003v18.994h-4.003
        V7.345z M8.243,0.239h4.003v26.099H8.243V0.239z M1.248,10.159h4.004v16.128H1.248V10.159z"/>
                                                </svg>
                                            </span>
                                            
                                        </div>
                                        <div class="arfgraphtype selected" id="arfgraphtype_div_line" onclick="change_graph_type('line')">
                                            <input type="radio"  value="line" id="arfgraphtype_line" name="arfgraphtype" checked>
                                            <span class="arfgraphtype_span">
                                                <svg width="35px" height="35px">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M26.835,8.673c-0.141,0-0.273-0.028-0.41-0.042l-3.493,8.709
        c0.715,0.639,1.173,1.558,1.173,2.592c0,1.928-1.563,3.49-3.49,3.49s-3.49-1.563-3.49-3.49c0-0.395,0.08-0.768,0.201-1.122
        l-5.351-7.229c-0.41,0.211-0.868,0.342-1.361,0.342c-0.074,0-0.143-0.017-0.215-0.022l-4.211,8.532
        c0.258,0.442,0.417,0.949,0.417,1.498c0,1.652-1.339,2.991-2.991,2.991s-2.991-1.339-2.991-2.991s1.339-2.991,2.991-2.991
        c0.35,0,0.68,0.071,0.992,0.182l3.957-8.021C7.986,10.557,7.621,9.79,7.621,8.933c0-1.652,1.34-2.992,2.992-2.992
        s2.991,1.339,2.991,2.992c0,0.447-0.104,0.868-0.281,1.25l5.142,7.021c0.594-0.469,1.334-0.76,2.149-0.76
        c0.218,0,0.429,0.026,0.636,0.064L24.6,8.01c-1.146-0.737-1.91-2.018-1.91-3.482c0-2.289,1.856-4.145,4.146-4.145
        s4.146,1.856,4.146,4.145C30.98,6.817,29.124,8.673,26.835,8.673z"/>
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="arfgraphtype" id="arfgraphtype_div_countries" onclick="change_graph_type('countries')">
                                            <input type="radio" value="countries" id="arfgraphtype_countries" name="arfgraphtype">
                                            <span class="arfgraphtype_span">
                                                <svg width="30px" height="30px">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M17.155,25.972l0.236-0.858l2.226-1.485l0.584-1.591l1.539-0.714l1.457-2.785
        l-2.254-1.326l-1.166-1.326l-0.69-0.08l-1.377-0.372l-1.189-0.188l-1.039,0.292l-0.637-0.714l-0.638-0.186l0.054-0.954l-0.77,0.025
        l-0.438,0.504l-0.251-1.061l1.006-0.478l1.036,0.478h0.555l0.202-0.822l1.55-1.857l2.15-1.086l1.244,0.159l0.113-0.608
        l-1.543-1.565l-0.584-1.166h-0.85l-0.531-0.311L16.01,5.729l-0.238,1.355l-1.381-0.266L14.287,5.97l1.06-0.24l0.35-1.52
        l1.052,0.433l-0.026,0.646l0.802,0.334l0.535,0.158l0.658-0.344l-0.585-0.688l-1.195-1.166l0.028-0.559l1.221,0.239l1.034,0.927
        l0.318,0.823l0.239,0.771l1.646,1.483l0.422,0.135l0.584-0.933l2.068-0.184l0.388-0.123c1.386,2.062,2.194,4.546,2.194,7.215
        C27.079,19.49,22.844,24.602,17.155,25.972z M17.893,1.534l-0.9,0.601l-0.637,0.704l-2.317,0.217l-0.954-0.157L12.43,3.927
        l-1.908,0.105L9.327,3.688L8.264,4.27L5.96,4.591L4.114,5.125H4.107c2.377-2.88,5.977-4.715,10.003-4.715
        c1.977,0,3.843,0.457,5.521,1.246l-0.465,0.07L17.893,1.534z M12.083,1.806l-0.765,0.243l-0.585-0.243
        c-0.019,0.154-0.85,0.689-0.85,0.689l0.85,0.497l1.768-0.464L12.083,1.806z M15.983,0.97l-1.325,0.729l-0.761,0.474l0.529,0.343
        l1.175-0.118l1.232-0.937L15.983,0.97z M3.653,7.851H5.11l2.163-0.386l1.363,2.214v2.07l1.859,2.521h0.315v-0.89l0.716,1.498
        l2.157,0.483l0.974,0.977l0.866,0.26l-0.866,1.807l0.945,1.751c0,0,0.593,2.014,0.595,2.095c0,0.079-0.595,2.413-0.595,2.413
        l0.135,1.571c-0.535,0.065-1.076,0.112-1.628,0.112c-7.163,0-12.97-5.807-12.97-12.968c0-2.387,0.655-4.615,1.781-6.536
        l0.933-0.216L2.918,7.37L3.653,7.851z"/>
                                                </svg>
                                            </span>
                                        </div>                                      
                                    </td>
                                </tr>
                            </table>
                            <style type="text/css">
                                .jqplot-xaxis { font-weight:bold; }
                                .jqplot-yaxis { font-weight:bold; }
                                .jqplot-highlighter { background-color:#333333; opacity:.70; filter:Alpha(Opacity=70); color:#FFFFFF; }
                                .jqplot-highlighter .tooltip_title {font-weight:bold; color:#FFFFFF; width:50px; font-size:12px; }
                                .jqplot-highlighter .tooltip_title1 {font-weight:bold; color:#FFFFFF; width:60px; font-size:12px; }
                            </style>
                            <div id="chart_div">
                                <div id="daily" style="padding:15px;">
                                    <label class="lbltitle">Daily chart</label><br />

                                    <div id="chart2" style="width:100%;height:300px;margin-top: 30px;margin-left: 6px;" ></div>

                                </div>

                                <div id="monthly" style="padding:15px; display:none;">
                                    <label class="lbltitle">Month chart</label><br />

                                    <div id="chart1" style="width:100%;height:300px;margin-top: 30px;margin-left: 6px;" ></div>

                                </div>

                                <div id="weekly" style="padding:15px; display:none;"} ?>">
                                    <label class="lbltitle">Weekly chart</label><br />

                                    <div id="chart3" style="width:100%;height:300px;margin-top: 30px;margin-left: 6px;" ></div>

                                </div>

                                <div id="yearly" style="padding:15px; display:none;">
                                    <label class="lbltitle">Yearly chart</label><br />

                                    <div id="chart4" style="width:100%;height:300px;margin-top: 30px;margin-left: 6px;" ></div>

                                </div>
                                <span class"lbltitle next_chart">Previous</span> <span class="lbltitle next_chart">Next</span>
                                <br /><br />


                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            if (is_rtl()) {
                $doc_link_align = 'left';
            } else {
                $doc_link_align = 'right';
            }
            ?>
            <div class="documentation_link" style="background:none; background:none;"  align="<?php echo $doc_link_align; ?>"><a href="<?php echo ARFURL; ?>/documentation/index.html" style="margin-right:10px;" target="_blank" class="arlinks"><?php echo addslashes(esc_html__('Documentation', 'ARForms')); ?></a>|<a href="https://helpdesk.arpluginshop.com/submit-a-ticket/" style="margin-left:10px;" target="_blank" class="arlinks"><?php echo addslashes(esc_html__('Support', 'ARForms')); ?></a> &nbsp;&nbsp;<img src="<?php echo ARFURL; ?>/images/dot.png" height="4" width="4" onclick="javascript:OpenInNewTab('<?php echo ARFURL; ?>/documentation/assets/sysinfo.php');" /></div>

        </div>

        <div class="arf_modal_overlay">
            <div id="delete_form_message" style="" class="arfmodal arfdeletemodabox arf_popup_container">
                <div class="arfnewmodalclose" data-dismiss="arfmodal"><img alt='' src="<?php echo ARFIMAGESURL . '/close-button.png'; ?>" align="absmiddle" /></div>
                <input type="hidden" value="" id="delete_entry_id" />
                <div class="arfdelete_modal_title"><img alt='' src="<?php echo ARFIMAGESURL . '/delete-field-icon.png'; ?>" align="absmiddle" style="margin-top:-5px;" />&nbsp;<?php echo addslashes(esc_html__('DELETE ENTRY', 'ARForms')); ?></div>
                <div class="arfdelete_modal_msg"><?php echo addslashes(esc_html__('Are you sure you want to delete this entry?', 'ARForms')); ?></div>
                <div class="arf_delete_modal_row">
                    <div class="arf_delete_modal_left" onclick="arfentryactionfunc('delete', '');"><img alt='' src="<?php echo ARFIMAGESURL . '/okay-icon.png'; ?>" align="absmiddle" style="margin-right:10px;" />&nbsp;<?php echo addslashes(esc_html__('Okay', 'ARForms')); ?></div>
                    <div class="arf_delete_modal_right" id="arf_close_single_entry_modal" data-dismiss="arfmodal"><img alt='' src="<?php echo ARFIMAGESURL . '/cancel-btnicon.png'; ?>" align="absmiddle" style="margin-right:10px;" />&nbsp;<?php echo addslashes(esc_html__('Cancel', 'ARForms')); ?></div>
                </div>
            </div>
        </div>
        <div class='arf_modal_overlay'>
            <div class="arf_entry_popup_container_wrapper">
                <div class='arf_popup_container arf_view_entry_modal arf_popup_container_view_entry_modal'>
                    <div class='arf_popup_container_header'><?php echo esc_html__('View entry', 'ARForms'); ?> <span id="arf_view_entry_modal_form_title"></span>
                        
                        <div class="arf_modal_close_btn arf_entry_model_close"></div> 
                    </div>
                    <div class='arfentry_modal_content arf_popup_content_container'></div>
                    <div class="arf_popup_footer arf_view_entry_modal_footer">
                        <button class="rounded_button arf_btn_dark_blue" id="arf_update_entry_button" style="<?php echo (is_rtl()) ? 'margin-left:7px;' : 'margin-right:7px;';?>" name="arf_update_entry_button"><?php echo addslashes(esc_html__('Update','ARForms')); ?></button>
                        <button class="rounded_button" id="arf_entry_popup_close_btn" style="color:#666666;" name="arf_entry_popup_close_btn"><?php echo addslashes(esc_html__('Cancel','ARForms')); ?></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="arf_modal_overlay">
            <div id="delete_bulk_entry_message" class="arfdeletemodabox arfmodal arf_popup_container arfdeletemodalboxnew">
                <input type="hidden" value="false" id="delete_bulk_entry_flag"/>
                <div class="arfdelete_modal_msg delete_confirm_message"><?php echo addslashes(esc_html__('Are you sure you want to <br/> delete this entries?', 'ARForms')); ?></div>
                <div class="arf_delete_modal_row delete_popup_footer">
                    <button class="rounded_button add_button arf_delete_modal_left arfdelete_color_red" onclick="arf_delete_bulk_entries('true');">&nbsp;<?php echo addslashes(esc_html__('Okay', 'ARForms')); ?></button>&nbsp;&nbsp;<button class="arf_delete_modal_right rounded_button delete_button arfdelete_color_gray arf_bulk_delete_entry_close_btn" data-dismiss="arfmodal">&nbsp;<?php echo addslashes(esc_html__('Cancel', 'ARForms')); ?></button>
                </div>
            </div>
        </div>

    </div>
    <input type="hidden" id="arf_is_edit_entries" value="no" />
    <script type="text/javascript">
        jQuery(document).ready(function () {
            if (!jQuery.isFunction(arf_edit_entries_in_viewmodel)) {
                return;
            }
            arf_edit_entries_in_viewmodel();
        });

    </script>