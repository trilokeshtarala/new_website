<?php
global $wpdb, $MdlDb,$arformcontroller;
$new_month_year ='';
$new_month = '';
$new_year = '';
$new_day = '';
$new_day_month = '';
$new_day_year = '';
if (isset($_REQUEST['calculate']) && $_REQUEST['calculate'] == 'pre') {
    if (isset($_REQUEST['new_year']) && $_REQUEST['new_year'] != '') {
        $year_diff = date('Y', current_time('timestamp')) - $_REQUEST['new_year'];
        $new_year = $_REQUEST['new_year'];
    } elseif (isset($_REQUEST['new_month']) && $_REQUEST['new_month'] != '') {
        $month_diff = date('m', current_time('timestamp')) - $_REQUEST['new_month'];
        $new_month = $_REQUEST['new_month'];
        $new_month_year = $_REQUEST['new_month_year'];
    } elseif (isset($_REQUEST['new_week']) && $_REQUEST['new_week'] != '') {
        $duedt = explode("-", date('Y-m-d'));
        $week_diff = (int) date('W', mktime(0, 0, 0, $duedt[1], $duedt[2], $duedt[0])) - $_REQUEST['new_week'];
        $new_week = $_REQUEST['new_week'];
        $new_week_year = $_REQUEST['new_week_year'];
    } elseif (isset($_REQUEST['new_day']) && $_REQUEST['new_day'] != '') {
        $new_diff = date('d') - $_REQUEST['new_day'];
        $new_day = $_REQUEST['new_day'];
        $new_day_month = $_REQUEST['new_day_month'];
        $new_day_year = $_REQUEST['new_day_year'];
    }
} elseif (isset($_REQUEST['calculate']) && $_REQUEST['calculate'] == 'next') {
    if (isset($_REQUEST['new_year']) && $_REQUEST['new_year'] != '') {
        $year_diff = date('Y', current_time('timestamp')) + $_REQUEST['new_year'];
        $new_year = $_REQUEST['new_year'];
    } elseif (isset($_REQUEST['new_month']) && $_REQUEST['new_month'] != '') {
        $month_diff = date('m', current_time('timestamp')) + $_REQUEST['new_month'];
        $new_month = $_REQUEST['new_month'];
        $new_month_year = $_REQUEST['new_month_year'];
    } elseif (isset($_REQUEST['new_week']) && $_REQUEST['new_week'] != '') {
        $duedt = explode("-", date('Y-m-d'));
        $week_diff = (int) date('W', mktime(0, 0, 0, $duedt[1], $duedt[2], $duedt[0])) + $_REQUEST['new_week'];
        $new_week = $_REQUEST['new_week'];
        $new_week_year = $_REQUEST['new_week_year'];
    } elseif (isset($_REQUEST['new_day']) && $_REQUEST['new_day'] != '') {
        $new_diff = date('d') + $_REQUEST['new_day'];
        $new_day = $_REQUEST['new_day'];
        $new_day_month = $_REQUEST['new_day_month'];
        $new_day_year = $_REQUEST['new_day_year'];
    }
} else {
    $year_diff = 0;
    $new_year = date('Y', current_time('timestamp'));
    $month_diff = 0;
    $new_month = date('m', current_time('timestamp'));

    $new_month_year = date('Y', current_time('timestamp'));
    $week_diff = 0;
    $duedt = explode("-", date('Y-m-d'));
    $new_week = (int) date('W', mktime(0, 0, 0, $duedt[1], $duedt[2], $duedt[0]));
    $new_week_year = date('Y');
    $new_day = date('d');
    $new_day_month = date('m');
    $new_day_year = date('Y');
}
$graph_type = isset($_REQUEST['graph_type']) ? $_REQUEST['graph_type'] : '';
if($graph_type == 'countries'){

    if ($form == '') { 
        if($type == 'yearly') {
            $allYear = $wpdb->get_results($wpdb->prepare("SELECT COUNT(id) AS total ,country AS country_name, YEAR(created_date) AS Year from " . $MdlDb->entries . " WHERE YEAR(created_date) = %d Group By YEAR(created_date),country",$new_year), 'ARRAY_A');            
            $yearView =  $wpdb->get_results($wpdb->prepare("SELECT COUNT(id) AS total ,country AS country_name, YEAR(added_date) AS YEAR from " . $MdlDb->views . " WHERE YEAR(added_date) = %d Group By YEAR(added_date),country", $new_year), 'ARRAY_A');
            
        } else if($type == 'monthly'){

            $allYear = $wpdb->get_results($wpdb->prepare("SELECT COUNT(id) AS total ,country AS country_name, MAX(MONTH(created_date)) AS MONTH  , YEAR(created_date) AS YEAR FROM " . $MdlDb->entries . " where MONTH(created_date) = %d AND YEAR(created_date) = %d group by MONTH(created_date), country", $new_month, $new_month_year), 'ARRAY_A'); 

            $yearView = $wpdb->get_results($wpdb->prepare("SELECT COUNT(id) AS total ,country AS country_name, MAX(MONTH(added_date)) AS MONTH  , YEAR(added_date) AS YEAR FROM " . $MdlDb->views . " where MONTH(added_date) = %d AND YEAR(added_date) = %d group by MONTH(added_date), country", $new_month, $new_month_year), 'ARRAY_A'); 
        } else if($type == 'daily'){
            $allYear = $wpdb->get_results($wpdb->prepare("SELECT COUNT(id) AS total ,country AS country_name, MAX(DAY(created_date)) AS DAY, MAX(MONTH(created_date)) AS MONTH  , YEAR(created_date) AS YEAR FROM " . $MdlDb->entries . " where DAY(created_date) = %d AND MONTH(created_date) = %d AND YEAR(created_date) = %d group by DAY(created_date), country",$new_day,$new_day_month, $new_day_year), 'ARRAY_A');             
            $yearView = $wpdb->get_results($wpdb->prepare("SELECT COUNT(id) AS total ,country AS country_name, MAX(DAY(added_date)) AS DAY, MAX(MONTH(added_date)) AS MONTH  , YEAR(added_date) AS YEAR FROM " . $MdlDb->views . " where DAY(added_date) = %d AND MONTH(added_date) = %d AND YEAR(added_date) = %d group by DAY(added_date), country",$new_day, $new_day_month, $new_day_year), 'ARRAY_A'); 
        }
    }
    else {
        $from_year = $new_year . '-01-01 00:00:00';
        $end_year = $new_year . '-12-31 23:59:59';
        if($type == 'yearly')
        {
            $allYear = $wpdb->get_results($wpdb->prepare("SELECT COUNT(id) AS total ,country AS country_name, YEAR(created_date) AS Year from " . $MdlDb->entries . " WHERE form_id = %d AND created_date >= %s AND created_date <= %s Group By YEAR(created_date),country", $form, $from_year, $end_year), 'ARRAY_A');            
            $yearView =  $wpdb->get_results($wpdb->prepare("SELECT COUNT(id) AS total ,country AS country_name, YEAR(added_date) AS YEAR from " . $MdlDb->views . " WHERE form_id = %d AND added_date >= %s AND added_date <= %s Group By YEAR(added_date),country", $form, $from_year, $end_year), 'ARRAY_A');            
        } else if($type == 'monthly'){

            $allYear = $wpdb->get_results($wpdb->prepare("SELECT COUNT(id) AS total ,country AS country_name, MAX(MONTH(created_date)) AS MONTH  , YEAR(created_date) AS YEAR FROM " . $MdlDb->entries . " where form_id = %d AND MONTH(created_date) = %d AND YEAR(created_date) = %d group by MONTH(created_date), country",$form, $new_month, $new_month_year), 'ARRAY_A'); 

            $yearView = $wpdb->get_results($wpdb->prepare("SELECT COUNT(id) AS total ,country AS country_name, MAX(MONTH(added_date)) AS MONTH  , YEAR(added_date) AS YEAR FROM " . $MdlDb->views . " where form_id = %d AND MONTH(added_date) = %d AND YEAR(added_date) = %d group by MONTH(added_date), country",$form, $new_month, $new_month_year), 'ARRAY_A');             
            
        } else if($type == 'daily'){
            $allYear = $wpdb->get_results($wpdb->prepare("SELECT COUNT(id) AS total ,country AS country_name, MAX(DAY(created_date)) AS DAY, MAX(MONTH(created_date)) AS MONTH  , YEAR(created_date) AS YEAR FROM " . $MdlDb->entries . " where form_id = %d AND DAY(created_date) = %d AND MONTH(created_date) = %d AND YEAR(created_date) = %d group by DAY(created_date), country",$form,$new_day,$new_day_month, $new_day_year), 'ARRAY_A');             
            $yearView = $wpdb->get_results($wpdb->prepare("SELECT COUNT(id) AS total ,country AS country_name, MAX(DAY(added_date)) AS DAY, MAX(MONTH(added_date)) AS MONTH  , YEAR(added_date) AS YEAR FROM " . $MdlDb->views . " where form_id = %d AND DAY(added_date) = %d AND MONTH(added_date) = %d AND YEAR(added_date) = %d group by DAY(added_date), country",$form,$new_day, $new_day_month, $new_day_year), 'ARRAY_A'); 
        }
    }
    
    $coutry_array= array();
    foreach ($allYear as $key => $value) {
        $coutry_array[$value['country_name']] = $value['total'];
    }

    $coutry_array_view= array();
    foreach ($yearView as $key => $value) {
        $coutry_array_view[$value['country_name']] = $value['total'];
    }
} 
// else {

    $allEntries = 'SELECT COUNT(id) AS total ,country AS country_name, YEAR(created_date) AS YEAR,MAX(MONTH(created_date)) AS MONTH , MAX(DAY(created_date)) AS DAY  FROM ' . $MdlDb->entries . ' group by YEAR(created_date),MONTH(created_date),DAY(created_date),country';

    $allViews = 'SELECT COUNT(id) AS total ,country AS country_name, YEAR(added_date) AS YEAR,MAX(MONTH(added_date)) AS MONTH , MAX(DAY(added_date)) AS DAY  from ' . $MdlDb->views . ' Group By YEAR(added_date),MONTH(added_date),DAY(added_date),country';

    $allYear = $wpdb->get_results('SELECT total, country_name, YEAR, MONTH, DAY FROM ('.$allEntries.' UNION ' . $allViews . ') AS arfentriesviews Group By YEAR,MONTH,DAY,country_name', 'ARRAY_A');
// }
$numYear = $wpdb->num_rows;

$Years = array();
$Months = array();
$Dates = array();
if ($numYear > 0) {
    foreach ($allYear as $newyear) {
        $Years[] = isset($newyear['YEAR']) ? $newyear['YEAR'] : $new_day_year;
        $Months[] = isset($newyear['MONTH']) ? $newyear['MONTH'] : $new_month;
        $Dates[] = isset($newyear['DAY']) ? $newyear['DAY'] : $new_day;
    }
}
$min_year = date("Y");
$max_year = date("Y");
$min_month = date("m");
$max_month = date("m");
$max_date = date("d");
$min_date = date("d");
if ((is_array($Years) && !empty($Years)) || (is_array($Months) && !empty($Months) ) || (is_array($Dates) && !empty($Dates))) {
    $min_year = min($Years);
    $max_year = max($Years);
    $min_month = min($Months);
    $max_month = max($Months);
    $max_date = max($Dates);
    $min_date = min($Dates);
}

if($graph_type =='countries'){
    $country_array = $arformcontroller->arfcode_to_country('','',true);    
    $data = array();
    $i=0;    

    foreach ($country_array as $key => $value) {
        $data[$i]['code'] = $key;
        $data[$i]['coutry_name'] = $value;  
        if(array_key_exists($value, $coutry_array))
        {
            $data[$i]['entries'] = $coutry_array[$value];
            $data[$i]['value'] = $coutry_array[$value];            
        }
        else{
            $data[$i]['entries'] = 0;
            $data[$i]['value'] = 0;
            $data[$i]['views'] = 0;    
        }
        if(array_key_exists($value, $coutry_array_view))
        {
            $data[$i]['views'] = $coutry_array_view[$value];    
        }
        else{
            $data[$i]['views'] = 0;    
        }

        
        $i++;
    }      

    $json_coutry_data = json_encode($data);
    
}
if ($type == "yearly") {
    $from_year = $new_year . '-01-01 00:00:00';
    $end_year = $new_year . '-12-31 23:59:59';

    if ($form == '') {
        $sqlMonth = $wpdb->get_results($wpdb->prepare("SELECT  YEAR(created_date) AS Year, MONTH(created_date) AS Month,COUNT(*) AS num from " . $MdlDb->entries . " WHERE created_date >= %s AND created_date <= %s Group By YEAR(created_date),  MONTH(created_date)", $from_year, $end_year), 'ARRAY_A');
        $totalYear = $wpdb->num_rows;

        $sqlViewMonth = $wpdb->get_results($wpdb->prepare("SELECT YEAR(added_date) AS Year, MONTH(added_date) AS Month,COUNT(*) AS num from " . $MdlDb->views . " WHERE added_date >= %s AND added_date <= %s Group By YEAR(added_date),  MONTH(added_date)", $from_year, $end_year), 'ARRAY_A');
        $totalViewYear = $wpdb->num_rows;
    } else {
        $sqlMonth = $wpdb->get_results($wpdb->prepare("SELECT YEAR(created_date) AS Year, MONTH(created_date) AS Month,COUNT(*) AS num from " . $MdlDb->entries . " WHERE form_id = %d AND created_date >= %s AND created_date <= %s Group By YEAR(created_date),  MONTH(created_date)", $form, $from_year, $end_year), 'ARRAY_A');
        $totalYear = $wpdb->num_rows;

        $sqlViewMonth = $wpdb->get_results($wpdb->prepare("SELECT YEAR(added_date) AS Year, MONTH(added_date) AS Month,COUNT(*) AS num from " . $MdlDb->views . " WHERE form_id = %d AND added_date >= %s AND added_date <= %s Group By YEAR(added_date),  MONTH(added_date)", $form, $from_year, $end_year), 'ARRAY_A');
        $totalViewYear = $wpdb->num_rows;
    }
    $arf_max_year_entry = 0;
    if ($totalYear > 0) {
        foreach ($sqlMonth as $arr_month) {
            $month[$arr_month['Month']] = $arr_month['num'];
        }

        $arf_max_year_entry = 0;
        foreach ($month as $key => $val) {
            $arf_max_year_entry = max($arf_max_year_entry, $val);
        }
        if ($arf_max_year_entry < 5)
            $arf_max_year_entry = $arf_max_year_entry;
    }

    if ($totalViewYear > 0) {
        foreach ($sqlViewMonth as $arr_view_month) {
            $view_month[$arr_view_month['Month']] = $arr_view_month['num'];
        }

        $arf_max_year_view = 0;
        if ($view_month) {
            foreach ($view_month as $key => $val) {
                $arf_max_year_view = max($arf_max_year_view, $val);
            }
        }
        if ($arf_max_year_view < 5)
            $arf_max_year_view = $arf_max_year_view;
    }
    $monthToDisplay = '';
    for ($i = 1; $i <= 12; $i++) {
        if (empty($month[$i])) {
            if ($i == 12)
                $monthToDisplay .= 0;
            else
                $monthToDisplay .= "0,";
        }else {
            if ($i == 12)
                $monthToDisplay .= $month[$i];
            else
                $monthToDisplay .= $month[$i] . ",";
        }
        $viewMonthToDisplay = '';
        if (empty($view_month[$i])) {
            if ($i == 12)
                $viewMonthToDisplay .= 0;
            else
                $viewMonthToDisplay .= "0,";
        }else {
            if ($i == 12)
                $viewMonthToDisplay .= $view_month[$i];
            else
                $viewMonthToDisplay .= $view_month[$i] . ",";
        }
    }
    
    $arf_max_year = 0;
    if ($arf_max_year_entry < 5 && (isset($arf_max_year_view) && $arf_max_year_view < 5))
        $arf_max_year = 5;
    $arf_disable_class_next = '';
    $arf_enable_next = 1;
    if($new_year >= date('Y', current_time('timestamp')))
    {
        $arf_disable_class_next = 'arf_disabled_class_next';
        $arf_enable_next = 0;
    }
    $arf_disable_class_prev = '';
    $arf_enable_prev = 1;
    if($new_year <= $min_year)
    {
        $arf_disable_class_prev = 'arf_disabled_class_prev';
        $arf_enable_prev = 0;
    }
    ?>
    <script type="text/javascript" data-cfasync="false">
        jQuery.noConflict();
                jQuery(document).ready(function($){
                var chart_type = '';
                var graph_type = "<?php echo $graph_type;?>";
                if(graph_type == 'bar'){
                    chart_type ='column';
                } else if(graph_type == 'line'){
                    chart_type ='areaspline';
                }                
                var line1 = [<?php echo $monthToDisplay; ?>];
                var line2 = [<?php echo $viewMonthToDisplay ?>];
                var yearly_data = [];
                var ticks = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                if(graph_type == 'bar' || graph_type == 'line')
                {
                    if(graph_type =='bar')
                    {
                        var arfChart = Highcharts.chart('chart1', {
                        chart: {
                        type: chart_type,
                        },
                        colors:[{
                        linearGradient: { x1: 0, x2: 0, y1: 0, y2: 1 },
                                stops: [
                                        [0, '#a3de63'],
                                        [1, '#ffdc17']
                                ]
                        }, {
                        linearGradient: { x1: 0, x2: 0, y1: 0, y2: 1 },
                                stops: [
                                        [0, '#4eeed6'],
                                        [1, '#53bcf6']
                                ]
                        }],
                        legend: {
                        align:'right',
                                symbolHeight: 13,
                                symbolWidth: 13,
                                symbolRadius: 12,
                                margin: 50,
                                backgroundColor: '#FFFFFF',
                                layout: 'horizontal',
                                itemDistance: 25,
                                symbolMargin: 20,
                                itemStyle: {
                                color: '#4e5462',
                                        fontSize:'18px',
                                        fontWeight:'normal',
                                        fontFamily:'Asap-medium',
                                }
                        },
                        title: {
                        text: ''
                        },
                        subtitle: {
                        text: ''
                        },
                        xAxis: {
                        categories: ticks,
                                crosshair: true
                        },
                        yAxis: {
                        min: 0,
                                title: {
                                text: ''
                                }
                        <?php
                        if (isset($arf_max_year) and $arf_max_year == 5) {
                            echo ',max : 6';
                        }
                        ?>
                        },
                        tooltip: {
                        headerFormat: '<span style="font-size:10px">Month : {point.key}</span><table>',
                                pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                                '<td style="padding:0"><b>{point.y:1f}</b></td></tr>',
                                footerFormat: '</table>',
                                shared: true,
                                useHTML: true
                        },
                        plotOptions: {                            
                        column: {
                        pointPadding: 0,
                                borderWidth: 1,
                                pointWidth: 27
                        }
                        },
                        series: [{
                        name: 'Entries',
                                data: line1

                        }, {
                        name: 'Views',
                                data: line2

                        }]
                    });
                    
                    }
                    else
                    {
                        var arfChart = Highcharts.chart('chart1', {
                            chart: {
                            type: chart_type,
                            },
                            colors:[{
                            linearGradient: { x1: 0, x2: 0, y1: 0, y2: 1 },
                                stops: [
                                        [0, 'rgba(163,222,99,0.5)'],
                                        [1, 'rgba(255,220,23,0.5)']
                                ]
                            }, {
                            linearGradient: { x1: 0, x2: 0, y1: 0, y2: 1 },
                                stops: [
                                        [0, 'rgba(78,238,214,0.5)'],
                                        [1, 'rgba(83,188,246,0.5)']
                                ]
                            }],
                            legend: {
                            align:'right',
                                    symbolHeight: 13,
                                    symbolWidth: 13,
                                    symbolRadius: 12,
                                    margin: 50,
                                    backgroundColor: '#FFFFFF',
                                    layout: 'horizontal',
                                    itemDistance: 25,
                                    symbolMargin: 20,
                                    itemStyle: {
                                    color: '#4e5462',
                                            fontSize:'18px',
                                            fontWeight:'normal',
                                            fontFamily:'Asap-medium',
                                    }
                            },
                            title: {
                            text: ''
                            },
                            subtitle: {
                            text: ''
                            },
                            xAxis: {
                            categories: ticks,
                                    crosshair: true
                            },
                            yAxis: {
                            min: 0,
                                    title: {
                                    text: ''
                                    }
                            <?php
                            if (isset($arf_max_year) and $arf_max_year == 5) {
                                echo ',max : 6';
                            }
                            ?>
                            },
                            tooltip: {
                            headerFormat: '<span style="font-size:10px">Month : {point.key}</span><table>',
                                    pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                                    '<td style="padding:0"><b>{point.y:1f}</b></td></tr>',
                                    footerFormat: '</table>',
                                    shared: true,
                                    useHTML: true
                            },
                            plotOptions: {                            
                                column: {
                                pointPadding: 0,
                                        borderWidth: 1,
                                        pointWidth: 27
                                },
                                areaspline: {
                                    fillOpacity: 0.5
                                }
                            },
                            series: [{
                            name: 'Entries',
                                    data: line1

                            }, {
                            name: 'Views',
                                    data: line2

                            }]
                        });
                        
                    }
                    var normalState = new Object();
                    normalState.stroke_width = 1;
                    normalState.stroke = '#afcaff';
                    normalState.fill = 'rgba(255,255,255,0.9)';
                    normalState.padding = 10;
                    normalState.r = 6;
                    normalState.width = 16;
                    normalState.height = 16;
                    normalState.align = 'center';
                    var hoverState = new Object();
                    hoverState = normalState;
                    var pressedState = new Object();
                    pressedState = normalState;
                    arfChart.renderer.button('', 56, 70, function(){change_graph_pre('<?php echo $type; ?>','<?php echo $arf_enable_prev;?>')}, normalState, hoverState, pressedState).attr({id:'arf_prev_button',class:'<?php echo $arf_disable_class_prev;?>'}).add().toFront();
                    arfChart.renderer.button('', (arfChart.chartWidth - 30), 70, function(){change_graph_next('<?php echo $type; ?>','<?php echo $arf_enable_next;?>')}, normalState, hoverState, pressedState).attr({id:'arf_next_button',class:'<?php echo $arf_disable_class_next;?>'}).add().toFront();
                    jQuery('.highcharts-container').find('#arf_prev_button').find('text').remove();
                    jQuery('.highcharts-container').find('#arf_prev_button').append('<svg version="1.1" xmlns="http://www.w3.org/2000/svg" x="12" y="10"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4786ff" d="M1.221,8.318l8.002,8.002l2.001-2L5.221,8.316l6.003-6.003  l-2.001-2L1.221,8.315l0.001,0.001L1.221,8.318z"/></svg>');
                    jQuery('.highcharts-container').find('#arf_next_button').find('text').remove();
                    jQuery('.highcharts-container').find('#arf_next_button').append('<svg version="1.1" xmlns="http://www.w3.org/2000/svg" x="12" y="10"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4786ff" d="M11.431,8.601l-8.002,8.002l-2.001-2l6.003-6.003L1.428,2.596  l2.001-2l8.002,8.002L11.43,8.599L11.431,8.601z"/></svg>'); 
                    
                }
                else
                {
                    yearly_data = <?php echo isset($json_coutry_data) ? $json_coutry_data : '[]';?>;
                    var arfChart = Highcharts.mapChart('chart1', {

                        tooltip: {
                             formatter: function(){
                                 var s = this.key + '<br/>';
                                 s += 'Entries:' + this.point.entries + '<br/>';
                                 s += 'View:' + this.point.views;
                                 return s;
                             },
                         },
                        chart: {
                            borderWidth: 0
                        },
                        colors:['#dae7ff', '#c7daff', '#b5cff','#a3c2ff', '#91b6ff', '#7eaaff', '#4786ff'],
                        legend: {
                            title: {
                                text: '',
                                style: {
                                    color: (Highcharts.theme && Highcharts.theme.textColor) || 'black'
                                }
                            },
                            align: 'right',
                            verticalAlign: 'bottom',
                            floating: true,
                            layout: 'vertical',
                            valueDecimals: 0,
                            backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || 'rgba(255, 255, 255, 0.85)',
                            symbolRadius: 0,
                            symbolHeight: 14,
                            itemStyle: {
                                color: '#4e5462',
                                fontSize:'14px',
                                fontWeight:'normal',
                                fontFamily:'Asap-medium',
                            }
                        },                        
                        title: {
                            text: ''
                        },
                        subtitle: {
                            text: ''
                        },
                        mapNavigation: {
                            enabled: true
                        },
                        colorAxis: {
                            dataClasses: [{
                                to: 3,
                                color:'#dae7ff'
                            }, {
                                from: 3,
                                to: 10,
                                color:'#c7daff'
                            }, {
                                from: 10,
                                to: 30,
                                color:'#b5cfff'
                            }, {
                                from: 30,
                                to: 100,
                                color:'#a3c2ff'
                            }, {
                                from: 100,
                                to: 300,
                                color:'#91b6ff'
                            }, {
                                from: 300,
                                to: 1000,
                                color:'#7eaaff'
                            }, {
                                from: 1000,
                                color:'#4786ff'
                            }]                  
                        },

                        
                        series: [{
                            data: yearly_data,
                            mapData: Highcharts.maps['custom/world'],
                            joinBy: ['iso-a2', 'code'],
                            animation: true,
                            name: 'Entries',
                            states: {
                                hover: {
                                    color: '#18da9d'
                                }
                            },
                            shadow: false
                        }]
                        
                    }); 
                var normalState = new Object();
                normalState.stroke_width = 1;
                normalState.stroke = '#afcaff';
                normalState.fill = 'rgba(255,255,255,0.9)';
                normalState.padding = 10;
                normalState.r = 6;
                normalState.width = 16;
                normalState.height = 16;
                normalState.align = 'center';
                var hoverState = new Object();
                hoverState = normalState;
                var pressedState = new Object();
                pressedState = normalState;
                arfChart.renderer.button('', 33, 170, function(){change_graph_pre('<?php echo $type; ?>','<?php echo $arf_enable_prev;?>')}, normalState, hoverState, pressedState).attr({id:'arf_prev_button',class:'<?php echo $arf_disable_class_prev;?>'}).add().toFront();
                arfChart.renderer.button('', (arfChart.chartWidth - 30), 170, function(){change_graph_next('<?php echo $type; ?>','<?php echo $arf_enable_next;?>')}, normalState, hoverState, pressedState).attr({id:'arf_next_button',class:'<?php echo $arf_disable_class_next;?>'}).add().toFront();
                jQuery('.highcharts-container').find('#arf_prev_button').find('text').remove();
                jQuery('.highcharts-container').find('#arf_prev_button').append('<svg version="1.1" xmlns="http://www.w3.org/2000/svg" x="12" y="10"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4786ff" d="M1.221,8.318l8.002,8.002l2.001-2L5.221,8.316l6.003-6.003  l-2.001-2L1.221,8.315l0.001,0.001L1.221,8.318z"/></svg>');
                jQuery('.highcharts-container').find('#arf_next_button').find('text').remove();
                jQuery('.highcharts-container').find('#arf_next_button').append('<svg version="1.1" xmlns="http://www.w3.org/2000/svg" x="12" y="10"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4786ff" d="M11.431,8.601l-8.002,8.002l-2.001-2l6.003-6.003L1.428,2.596  l2.001-2l8.002,8.002L11.43,8.599L11.431,8.601z"/></svg>');

                }
                
        });
    </script>
    <?php
}

$date = $new_month_year . '-' . $new_month . '-' . date('m', current_time('timestamp'));

$day_first = date('01', strtotime($date));


$day_last = date('t', strtotime($date));

function makeDayArray($startDate, $endDate) {

    $startDate = strtotime($startDate);
    $endDate = strtotime($endDate);


    $currDate = $startDate;
    $dayArray = array();


    do {
        $dayArray[] = date('Y-m-d', $currDate);
        $currDate = strtotime('+1 day', $currDate);
    } while ($currDate <= $endDate);


    return $dayArray;
}

$day_array = makeDayArray($new_month_year . '-' . $new_month . '-' . $day_first, $new_month_year . '-' . $new_month . '-' . $day_last);

if ($form == '') {
    foreach ($day_array as $day) {
        $day_arr[$day] = $wpdb->get_results($wpdb->prepare('SELECT COUNT(*) AS num FROM ' . $MdlDb->entries . ' WHERE CAST(created_date AS DATE) = %s', $day), 'ARRAY_A');
        $day_arr[$day] = $day_arr[$day][0];

        $day_view_arr[$day] = $wpdb->get_results($wpdb->prepare('SELECT COUNT(*) AS num FROM ' . $MdlDb->views . ' WHERE CAST(added_date AS DATE) = %s', $day), 'ARRAY_A');
        $day_view_arr[$day] = $day_view_arr[$day][0];
    }
} else {
    foreach ($day_array as $day) {
        $day_arr[$day] = $wpdb->get_results($wpdb->prepare('SELECT COUNT(*) AS num FROM ' . $MdlDb->entries . ' WHERE form_id = %d AND CAST(created_date AS DATE) = %s', $form, $day), 'ARRAY_A');
        $day_arr[$day] = $day_arr[$day][0];

        $day_view_arr[$day] = $wpdb->get_results($wpdb->prepare('SELECT COUNT(*) AS num FROM ' . $MdlDb->views . ' WHERE form_id = %d AND CAST(added_date AS DATE) = %s', $form, $day), 'ARRAY_A');
        $day_view_arr[$day] = $day_view_arr[$day][0];
    }
}

$day_var = '[';
$val_var = '[';
$col_var = '[';
$day_view_var = '[';
$series_data = array();

foreach ($day_arr as $day => $val) {
    $day_var .= "'" . date('d', strtotime($day)) . "-" . date('M', strtotime($day)) . "', ";
    $val_var .= $val['num'] . ', ';
    $col_var .= "'#00CCFF', ";
}

foreach ($day_view_arr as $dayView => $valView) {
    $day_view_var .= $valView['num'] . ', ';
}

$day_var .= ']';
$val_var .= ']';
$col_var .= ']';
$day_view_var .= ']';

$max_day = 0;
foreach ($day_arr as $key => $val) {
    $max_day = max($max_day, $val['num']);
}

foreach ($day_view_arr as $key => $val) {
    $max_view_day = max($max_view_day, $val['num']);
}

$max_day_mnth = '';
if ($max_day < 5 && $max_view_day < 5)
    $max_day_mnth = 5;
if( $type == "monthly"){
$max_month_limit = date('Y-m',mktime(0, 0, 0, $new_month,1 , $new_month_year));
$arf_disable_class_next = '';
$arf_enable_next = 1;
if($max_month_limit >= date('Y-m'))
{
    $arf_disable_class_next = 'arf_disabled_class_next';
    $arf_enable_next = 0;
}
$arf_disable_class_prev = '';
$arf_enable_prev = 1;
$month_limit = date('Y-m-d',mktime(0, 0, 0, $new_month, 1, $new_month_year));
$min_month_limit = date('Y-m-d',mktime(0, 0, 0, $min_month, 1, $min_year));
if($min_month_limit >= $month_limit)
{
    $arf_disable_class_prev = 'arf_disabled_class_prev';
    $arf_enable_prev = 0;
}
?>
<script type="text/javascript" data-cfasync="false">
    jQuery.noConflict();
    jQuery(document).ready(function($){
        var buttonOptions = {};
        var chart_type = '';
        var graph_type = "<?php echo $graph_type;?>";
        if(graph_type == 'bar'){
            chart_type ='column';
        } else if(graph_type == 'line'){
            chart_type ='areaspline';
        }
        var s1 = <?php echo $val_var; ?>;
        var s2 = <?php echo $day_view_var; ?>;
        var monthly_data = [];
        var ticks_month = <?php echo $day_var; ?>;
        if(graph_type == 'bar' || graph_type == 'line'){
            if(graph_type == 'bar'){
                var gbarOpt = {
                    chart : {
                        type:chart_type
                    },
                    colors:[
                        {
                            linearGradient: { x1: 0, x2: 0, y1: 0, y2: 1 },
                            stops: [
                                [0, '#a3de63'],
                                [1, '#ffdc17']
                            ]
                        }, 
                        {
                            linearGradient: { x1: 0, x2: 0, y1: 0, y2: 1 },
                            stops: [
                                [0, '#4eeed6'],
                                [1, '#53bcf6']
                            ]
                        }
                    ],
                    legend: {                                
                        align:'right',
                        symbolHeight: 13,
                        symbolWidth: 13,
                        symbolRadius: 12,
                        margin: 50,
                        backgroundColor: '#FFFFFF',
                        layout: 'horizontal',
                        itemDistance: 25,
                        symbolMargin: 20,
                        itemStyle: {
                            color: '#4e5462',
                            fontSize:'18px',
                            fontWeight:'normal',
                            fontFamily:'Asap-medium',
                        }
                    },
                    title: {
                        text: ''
                    },
                    subtitle: {
                        text: ''
                    },
                    xAxis: {
                        categories: <?php echo $day_var; ?>,
                        crosshair: true
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: ''
                        }
                        <?php
                        if ($max_day_mnth == 5) {
                            echo ',max : 6';
                        }
                        ?>
                    },
                    tooltip: {
                        headerFormat: '<span style="font-size:10px">Day : {point.key}</span><table>',
                        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                        '<td style="padding:0"><b>{point.y:1f}</b></td></tr>',
                        footerFormat: '</table>',
                        shared: true,
                        useHTML: true
                    },
                    plotOptions: {
                        column: {
                            pointPadding: 0.2,
                            borderWidth: 1,
                            pointWidth: 10 //width of the column
                        }
                    },
                    series: [
                        {
                            name: 'Entries',
                            data: s1
                        },
                        {
                            name: 'Views',
                            data: s2
                        }
                    ],
                };
                var arfChart = Highcharts.chart('chart2', gbarOpt);    
            } else {
                var opt2 = {
                    chart : {
                        type:chart_type
                    },
                    colors:[
                        {
                            linearGradient: { x1: 0, x2: 0, y1: 0, y2: 1 },
                            stops: [
                                [0, 'rgba(163,222,99,0.5)'],
                                [1, 'rgba(255,220,23,0.5)']
                            ]
                        },
                        {
                            linearGradient: { x1: 0, x2: 0, y1: 0, y2: 1 },
                            stops: [
                                [0, 'rgba(78,238,214,0.5)'],
                                [1, 'rgba(83,188,246,0.5)']
                            ]
                        }
                    ],
                    legend: {
                        align:'right',
                        symbolHeight: 13,
                        symbolWidth: 13,
                        symbolRadius: 12,
                        margin: 50,
                        backgroundColor: '#FFFFFF',
                        layout: 'horizontal',
                        itemDistance: 25,
                        symbolMargin: 20,
                        itemStyle: {
                        color: '#4e5462',
                            fontSize:'18px',
                            fontWeight:'normal',
                            fontFamily:'Asap-medium',
                        }
                    },
                    title: {
                        text: ''
                    },
                    subtitle: {
                        text: ''
                    },
                    xAxis: {
                        categories: <?php echo $day_var; ?>,
                        crosshair: true
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: ''
                        }
                        <?php
                        if ($max_day_mnth == 5) {
                            echo ',max : 6';
                        }
                        ?>
                    },
                    tooltip: {
                        headerFormat: '<span style="font-size:10px">Day : {point.key}</span><table>',
                        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' + '<td style="padding:0"><b>{point.y:1f}</b></td></tr>',
                        footerFormat: '</table>',
                        shared: true,
                        useHTML: true
                    },
                    plotOptions: {
                        column: {
                            pointPadding: 0.2,
                            borderWidth: 1,
                            pointWidth: 10 //width of the column
                        },
                        areaspline: {
                            fillOpacity: 0.5
                        }
                    },
                    series: [
                        {
                            name: 'Entries',
                            data: s1
                        }, {
                            name: 'Views',
                            data: s2
                        }
                    ],
                };
                var arfChart = Highcharts.chart('chart2', opt2);
            }
            var normalState = new Object();
            normalState.stroke_width = 1;
            normalState.stroke = '#afcaff';
            normalState.fill = 'rgba(255,255,255,0.9)';
            normalState.padding = 10;
            normalState.r = 6;
            normalState.width = 16;
            normalState.height = 16;
            normalState.align = 'center';
            var hoverState = new Object();
            hoverState = normalState;
            var pressedState = new Object();
            pressedState = normalState;
            arfChart.renderer.button('', 56, 70, function(){change_graph_pre('<?php echo $type; ?>','<?php echo $arf_enable_prev;?>')}, normalState, hoverState, pressedState).attr({id:'arf_prev_button',class:'<?php echo $arf_disable_class_prev;?>'}).add().toFront();
            arfChart.renderer.button('', (arfChart.chartWidth - 30), 70, function(){change_graph_next('<?php echo $type; ?>','<?php echo $arf_enable_next;?>')}, normalState, hoverState, pressedState).attr({id:'arf_next_button',class:'<?php echo $arf_disable_class_next;?>'}).add().toFront();
            jQuery('.highcharts-container').find('#arf_prev_button').find('text').remove();
            jQuery('.highcharts-container').find('#arf_prev_button').append('<svg version="1.1" xmlns="http://www.w3.org/2000/svg" x="12" y="10"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4786ff" d="M1.221,8.318l8.002,8.002l2.001-2L5.221,8.316l6.003-6.003  l-2.001-2L1.221,8.315l0.001,0.001L1.221,8.318z"/></svg>');
            jQuery('.highcharts-container').find('#arf_next_button').find('text').remove();
            jQuery('.highcharts-container').find('#arf_next_button').append('<svg version="1.1" xmlns="http://www.w3.org/2000/svg" x="12" y="10"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4786ff" d="M11.431,8.601l-8.002,8.002l-2.001-2l6.003-6.003L1.428,2.596  l2.001-2l8.002,8.002L11.43,8.599L11.431,8.601z"/></svg>'); 
        } else if(graph_type == 'countries') {
            var monthly_data = <?php echo isset($json_coutry_data) ? $json_coutry_data : '[]';?>;
            var monthlyOpt = {
                tooltip: {
                     formatter: function(){
                         var s = this.key + '<br/>';
                         s += 'Entries:' + this.point.entries + '<br/>';
                         s += 'View:' + this.point.views;
                         return s;
                     },
                },
                chart: {
                    borderWidth: 0
                },
                colors:['#dae7ff', '#c7daff', '#b5cff','#a3c2ff', '#91b6ff', '#7eaaff', '#4786ff'],
                legend: {
                    title: {
                        text: '',
                        style: {
                            color: (Highcharts.theme && Highcharts.theme.textColor) || 'black'
                        }
                    },
                    align: 'right',
                    verticalAlign: 'bottom',
                    floating: true,
                    layout: 'vertical',
                    valueDecimals: 0,
                    backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || 'rgba(255, 255, 255, 0.85)',
                    symbolRadius: 0,
                    symbolHeight: 14,
                    itemStyle: {
                        color: '#4e5462',
                        fontSize:'14px',
                        fontWeight:'normal',
                        fontFamily:'Asap-medium',
                    }
                },                        
                title: {
                    text: ''
                },
                subtitle: {
                    text: ''
                },
                mapNavigation: {
                    enabled: true
                },
                colorAxis: {
                    dataClasses: [{
                        to: 3,
                        color:'#dae7ff'
                    }, {
                        from: 3,
                        to: 10,
                        color:'#c7daff'
                    }, {
                        from: 10,
                        to: 30,
                        color:'#b5cfff'
                    }, {
                        from: 30,
                        to: 100,
                        color:'#a3c2ff'
                    }, {
                        from: 100,
                        to: 300,
                        color:'#91b6ff'
                    }, {
                        from: 300,
                        to: 1000,
                        color:'#7eaaff'
                    }, {
                        from: 1000,
                        color:'#4786ff'
                    }]                  
                },
                series: [{
                    data: monthly_data,
                    mapData: Highcharts.maps['custom/world'],
                    joinBy: ['iso-a2', 'code'],
                    animation: true,
                    name: 'Entries',
                    states: {
                        hover: {
                            color: '#18da9d'
                        }
                    },
                    shadow: false
                }]
            };
            var arfChart = Highcharts.mapChart('chart2', monthlyOpt); 
            var normalState = new Object();
            normalState.stroke_width = 1;
            normalState.stroke = '#afcaff';
            normalState.fill = 'rgba(255,255,255,0.9)';
            normalState.padding = 10;
            normalState.r = 6;
            normalState.width = 16;
            normalState.height = 16;
            normalState.align = 'center';
            var hoverState = new Object();
            hoverState = normalState;
            var pressedState = new Object();
            pressedState = normalState;
            arfChart.renderer.button('', 33, 170, function(){change_graph_pre('<?php echo $type; ?>','<?php echo $arf_enable_prev;?>')}, normalState, hoverState, pressedState).attr({id:'arf_prev_button',class:'<?php echo $arf_disable_class_prev;?>'}).add().toFront();
            arfChart.renderer.button('', (arfChart.chartWidth - 30), 170, function(){change_graph_next('<?php echo $type; ?>','<?php echo $arf_enable_next;?>')}, normalState, hoverState, pressedState).attr({id:'arf_next_button',class:'<?php echo $arf_disable_class_next;?>'}).add().toFront();
            jQuery('.highcharts-container').find('#arf_prev_button').find('text').remove();
            jQuery('.highcharts-container').find('#arf_prev_button').append('<svg version="1.1" xmlns="http://www.w3.org/2000/svg" x="12" y="10"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4786ff" d="M1.221,8.318l8.002,8.002l2.001-2L5.221,8.316l6.003-6.003  l-2.001-2L1.221,8.315l0.001,0.001L1.221,8.318z"/></svg>');
            jQuery('.highcharts-container').find('#arf_next_button').find('text').remove();
            jQuery('.highcharts-container').find('#arf_next_button').append('<svg version="1.1" xmlns="http://www.w3.org/2000/svg" x="12" y="10"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4786ff" d="M11.431,8.601l-8.002,8.002l-2.001-2l6.003-6.003L1.428,2.596  l2.001-2l8.002,8.002L11.43,8.599L11.431,8.601z"/></svg>'); 
        }
    });
</script>
<?php
}
if ($type == "daily") {
    $fdate = $new_day_year . "-" . $new_day_month . "-" . $new_day . " 00:00:00";
    $ldate = $new_day_year . "-" . $new_day_month . "-" . $new_day . " 23:59:59";

    if ($form == '') {
        $getDailyRecords = $wpdb->get_results($wpdb->prepare("SELECT Hour(created_date) AS hour,count(id) AS record FROM " . $MdlDb->entries . " where created_date >= %s and created_date <= %s  GROUP BY Day(created_date), Hour(created_date) ORDER BY Day(created_date), Hour(created_date)", $fdate, $ldate), 'ARRAY_A');
        $totalDailyRecord = $wpdb->num_rows;

        $getDailyViewRecords = $wpdb->get_results($wpdb->prepare("SELECT Hour(added_date) AS hour,count(id) AS record FROM " . $MdlDb->views . " where added_date >= %s and added_date <= %s  GROUP BY Day(added_date), Hour(added_date) ORDER BY Day(added_date), Hour(added_date)", $fdate, $ldate), 'ARRAY_A');
        $totalDailyViewRecord = $wpdb->num_rows;
    } else {
        $getDailyRecords = $wpdb->get_results($wpdb->prepare("SELECT Hour(created_date) AS hour,count(id) AS record FROM " . $MdlDb->entries . " where created_date >= %s and created_date <= %s AND form_id = %d  GROUP BY Day(created_date), Hour(created_date) ORDER BY Day(created_date), Hour(created_date)", $fdate, $ldate, $form), 'ARRAY_A');
        $totalDailyRecord = $wpdb->num_rows;        


        $getDailyViewRecords = $wpdb->get_results($wpdb->prepare("SELECT Hour(added_date) AS hour,count(id) AS record FROM " . $MdlDb->views . " where added_date >= %s and added_date <= %s AND form_id = %d GROUP BY Day(added_date), Hour(added_date) ORDER BY Day(added_date), Hour(added_date)", $fdate, $ldate, $form), 'ARRAY_A');
        $totalDailyViewRecord = $wpdb->num_rows;        
    }
    $max_day = 0;
    $max_day_d = 0;
    $max_day_tick = '';
    $new_arr = array();
    $newViewArr = array();
    if ($totalDailyViewRecord > 0) {
        foreach ($getDailyRecords as $dailyRecord) {
            $hour[] = $dailyRecord['hour'];
            $record[] = $dailyRecord['record'];
            $new_arr[$dailyRecord['hour']] = $dailyRecord['record'];
        }

        foreach ($getDailyViewRecords as $dailyViewRecord) {
            $newViewArr[$dailyViewRecord['hour']] = $dailyViewRecord['record'];
        }
    }
    $new_array = array();
    $newViewArray = array();
    for ($z = 0; $z <= 23; $z+=2) {
        if ((isset($new_arr[$z]) and $new_arr[$z] != "") || (isset($new_arr[$z + 1]) and $new_arr[$z + 1] != "")) {
            $new_array[$z] = ( isset($new_arr[$z]) ? $new_arr[$z] : '' ) + ( isset($new_arr[$z + 1]) ? $new_arr[$z + 1] : '' );
        }
        if ((isset($newViewArr[$z]) and $newViewArr[$z] != "") || (isset($newViewArr[$z + 1]) and $newViewArr[$z + 1] != "")) {
            $newViewArray[$z] = ( isset($newViewArr[$z]) ? $newViewArr[$z] : '' ) + ( isset($newViewArr[$z + 1]) ? $newViewArr[$z + 1] : '' );
        }
    }

    for ($i = 1; $i <= 24; $i++) {
        if (array_key_exists($i - 1, $new_array)) {
            $record[$i] = $new_array[$i - 1];
        } else {
            $record[$i] = 0;
        }
        $dailyline1 = '';
        if ($i == 24)
            $dailyline1 .= $record[$i];
        else
            $dailyline1 .= $record[$i] . ',';


        if (array_key_exists($i - 1, $newViewArray))
            $viewRecord[$i] = $newViewArray[$i - 1];
        else
            $viewRecord[$i] = 0;

        $dailyline2 = '';
        if ($i == 24)
            $dailyline2 .= $viewRecord[$i];
        else
            $dailyline2 .= $viewRecord[$i] . ',';

        $max_day = max($max_day, $record[$i]);
        $max_view_day = max($max_view_day, $viewRecord[$i]);
    }
    if ($max_day < 5 && $max_view_day <= 5)
        $max_day_tick = 5;

    $max_date_limit = date('Y-m-d',mktime(0, 0, 0, $new_day_month, $new_day, $new_day_year));    
    $arf_disable_class_next = '';
    $arf_enable_next = 1;
    if($max_date_limit >= date('Y-m-d'))
    {
        $arf_disable_class_next = 'arf_disabled_class_next';
        $arf_enable_next = 0;
    }
    $arf_disable_class_prev = '';
    $arf_enable_prev = 1;
    $date_limit = date('Y-m-d',mktime(0, 0, 0, $new_day_month, $new_day, $new_day_year));
    $min_date_limit = date('Y-m-d',mktime(0, 0, 0, $min_month, $min_date, $min_year));
    if($min_date_limit >= $date_limit)
    {
        $arf_disable_class_prev = 'arf_disabled_class_prev';
        $arf_enable_prev = 0;
    }
    ?>
    <script type="text/javascript" data-cfasync="false">
        jQuery.noConflict();
        jQuery(document).ready(function($){
            var chart_type = '';
            var graph_type = "<?php echo $graph_type;?>";
            if(graph_type == 'bar'){
                chart_type ='column';
            } else if(graph_type == 'line'){
                chart_type ='areaspline';
            }
            var s1 = [<?php echo $dailyline1; ?>];
            var s2 = [<?php echo $dailyline2; ?>];
            var daily_data = [];
            var ticks_daily = ['00:00', '01:00', '02:00', '03:00', '04:00', '05:00', '06:00', '07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00', '23:00'];
            if(graph_type == 'bar' || graph_type == 'line'){
                if(graph_type == 'bar'){ 
                    var opt1 = {
                        chart: {
                            type: chart_type
                        },
                        legend: {
                            align:'right',
                            symbolHeight: 13,
                            symbolWidth: 13,
                            symbolRadius: 12,
                            margin: 50,
                            backgroundColor: '#FFFFFF',
                            layout: 'horizontal',
                            itemDistance: 25,
                            symbolMargin: 20,
                            itemStyle: {
                            color: '#4e5462',
                                fontSize:'18px',
                                fontWeight:'normal',
                                fontFamily:'Asap-medium',
                            }
                        },
                        title: {
                            text: ''
                        },
                        subtitle: {
                            text: ''
                        },
                        xAxis: {
                            categories: ticks_daily,
                            crosshair: true
                        },
                        yAxis: {
                            min: 0,
                            title: {
                                text: ''
                            }
                            <?php
                            if ($max_day_tick == 5) {
                                echo ',max : 6';
                            }
                            ?>
                        },
                        tooltip: {
                            headerFormat: '<span style="font-size:10px">Hour : {point.key}</span><table>',
                            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' + '<td style="padding:0"><b>{point.y:1f}</b></td></tr>',
                            footerFormat: '</table>',
                            shared: true,
                            useHTML: true
                        },
                        plotOptions: {
                            column: {
                                pointPadding: 0.2,
                                borderWidth: 1,
                                pointWidth: 25
                            }
                        },
                        series: [
                            {
                                name: 'Entries',
                                data: s1
                            },
                            {
                                name: 'Views',
                                data: s2
                            }
                        ],
                        colors:[
                            {
                                linearGradient: { x1: 0, x2: 0, y1: 0, y2: 1 },
                                stops: [
                                    [0, '#a3de63'],
                                    [1, '#ffdc17']
                                ]
                            },
                            {
                                linearGradient: { x1: 0, x2: 0, y1: 0, y2: 1 },
                                stops: [
                                    [0, '#4eeed6'],
                                    [1, '#53bcf6']
                                ]
                            }
                        ],
                    };
                    var arfChart = Highcharts.chart('chart4', opt1);
                } else {
                    var opt2 = {
                        chart: {
                            type: chart_type
                        },
                        legend: {
                            align:'right',
                            symbolHeight: 13,
                            symbolWidth: 13,
                            symbolRadius: 12,
                            margin: 50,
                            backgroundColor: '#FFFFFF',
                            layout: 'horizontal',
                            itemDistance: 25,
                            symbolMargin: 20,
                            itemStyle: {
                            color: '#4e5462',
                                fontSize:'18px',
                                fontWeight:'normal',
                                fontFamily:'Asap-medium',
                            }
                        },
                        title: {
                            text: ''
                        },
                        subtitle: {
                            text: ''
                        },
                        xAxis: {
                            categories: ticks_daily,
                            crosshair: true
                        },
                        yAxis: {
                            min: 0,
                            title: {
                                text: ''
                            }
                            <?php
                            if ($max_day_tick == 5) {
                                echo ',max : 6';
                            }
                            ?>
                        },
                        tooltip: {
                            headerFormat: '<span style="font-size:10px">Hour : {point.key}</span><table>',
                            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' + '<td style="padding:0"><b>{point.y:1f}</b></td></tr>',
                            footerFormat: '</table>',
                            shared: true,
                            useHTML: true
                        },
                        plotOptions: {
                            column: {
                                pointPadding: 0.2,
                                borderWidth: 1,
                                pointWidth: 25
                            },
                            areaspline: {
                                fillOpacity: 0.5
                            }
                        },
                        series: [
                            {
                                name: 'Entries',
                                data: s1
                            }, {
                                name: 'Views',
                                data: s2
                            }
                        ],
                        colors:[
                            {
                                linearGradient: { x1: 0, x2: 0, y1: 0, y2: 1 },
                                stops: [
                                    [0, 'rgba(163,222,99,0.5)'],
                                    [1, 'rgba(255,220,23,0.5)']
                                ]
                            },
                            {
                                linearGradient: { x1: 0, x2: 0, y1: 0, y2: 1 },
                                stops: [
                                    [0, 'rgba(78,238,214,0.5)'],
                                    [1, 'rgba(83,188,246,0.5)']
                                ]
                            }
                        ],
                    };
                    var arfChart = Highcharts.chart('chart4',opt2);
                }
                var normalState = new Object();
                normalState.stroke_width = 1;
                normalState.stroke = '#afcaff';
                normalState.fill = 'rgba(255,255,255,0.9)';
                normalState.padding = 10;
                normalState.r = 6;
                normalState.width = 16;
                normalState.height = 16;
                normalState.align = 'center';
                var hoverState = new Object();
                hoverState = normalState;
                var pressedState = new Object();
                pressedState = normalState;
                arfChart.renderer.button('', 56, 70, function(){change_graph_pre('<?php echo $type; ?>','<?php echo $arf_enable_prev;?>')}, normalState, hoverState, pressedState).attr({id:'arf_prev_button',class:'<?php echo $arf_disable_class_prev;?>'}).add().toFront();
                arfChart.renderer.button('', (arfChart.chartWidth - 30), 70, function(){change_graph_next('<?php echo $type; ?>','<?php echo $arf_enable_next;?>')}, normalState, hoverState, pressedState).attr({id:'arf_next_button',class:'<?php echo $arf_disable_class_next;?>'}).add().toFront();
                jQuery('.highcharts-container').find('#arf_prev_button').find('text').remove();
                jQuery('.highcharts-container').find('#arf_prev_button').append('<svg version="1.1" xmlns="http://www.w3.org/2000/svg" x="12" y="10"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4786ff" d="M1.221,8.318l8.002,8.002l2.001-2L5.221,8.316l6.003-6.003  l-2.001-2L1.221,8.315l0.001,0.001L1.221,8.318z"/></svg>');
                jQuery('.highcharts-container').find('#arf_next_button').find('text').remove();
                jQuery('.highcharts-container').find('#arf_next_button').append('<svg version="1.1" xmlns="http://www.w3.org/2000/svg" x="12" y="10"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4786ff" d="M11.431,8.601l-8.002,8.002l-2.001-2l6.003-6.003L1.428,2.596  l2.001-2l8.002,8.002L11.43,8.599L11.431,8.601z"/></svg>');
                
            } else {
                daily_data = <?php echo isset($json_coutry_data) ? $json_coutry_data : '[]';?>;
                var opt3 = {
                    tooltip: {
                         formatter: function(){
                             var s = this.key + '<br/>';
                             s += 'Entries:' + this.point.entries + '<br/>';
                             s += 'View:' + this.point.views;
                             return s;
                         },
                    },
                    chart: {
                        borderWidth: 0
                    },
                    colors:['#dae7ff', '#c7daff', '#b5cff','#a3c2ff', '#91b6ff', '#7eaaff', '#4786ff'],
                    legend: {
                        title: {
                            text: '',
                            style: {
                                color: (Highcharts.theme && Highcharts.theme.textColor) || 'black'
                            }
                        },
                        align: 'right',
                        verticalAlign: 'bottom',
                        floating: true,
                        layout: 'vertical',
                        valueDecimals: 0,
                        backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || 'rgba(255, 255, 255, 0.85)',
                        symbolRadius: 0,
                        symbolHeight: 14,
                        itemStyle: {
                            color: '#4e5462',
                            fontSize:'14px',
                            fontWeight:'normal',
                            fontFamily:'Asap-medium',
                        }
                    },                        
                    title: {
                        text: ''
                    },
                    subtitle: {
                        text: ''
                    },
                    mapNavigation: {
                        enabled: true
                    },
                    colorAxis: {
                        dataClasses: [{
                            to: 3,
                            color:'#dae7ff'
                        }, {
                            from: 3,
                            to: 10,
                            color:'#c7daff'
                        }, {
                            from: 10,
                            to: 30,
                            color:'#b5cfff'
                        }, {
                            from: 30,
                            to: 100,
                            color:'#a3c2ff'
                        }, {
                            from: 100,
                            to: 300,
                            color:'#91b6ff'
                        }, {
                            from: 300,
                            to: 1000,
                            color:'#7eaaff'
                        }, {
                            from: 1000,
                            color:'#4786ff'
                        }]                    
                    },

                    
                    series: [{
                        data: daily_data,
                        mapData: Highcharts.maps['custom/world'],
                        joinBy: ['iso-a2', 'code'],
                        animation: true,
                        name: 'Entries',
                        states: {
                            hover: {
                                color: '#18da9d'
                            }
                        },
                        shadow: false
                    }]
                    
                };
                var arfChart = Highcharts.mapChart('chart4', opt3);
                var normalState = new Object();
                normalState.stroke_width = 1;
                normalState.stroke = '#afcaff';
                normalState.fill = 'rgba(255,255,255,0.9)';
                normalState.padding = 10;
                normalState.r = 6;
                normalState.width = 16;
                normalState.height = 16;
                normalState.align = 'center';
                var hoverState = new Object();
                hoverState = normalState;
                var pressedState = new Object();
                pressedState = normalState;
                arfChart.renderer.button('', 33, 170, function(){change_graph_pre('<?php echo $type; ?>','<?php echo $arf_enable_prev;?>')}, normalState, hoverState, pressedState).attr({id:'arf_prev_button',class:'<?php echo $arf_disable_class_prev;?>'}).add().toFront();
                arfChart.renderer.button('', (arfChart.chartWidth - 30), 170, function(){change_graph_next('<?php echo $type; ?>','<?php echo $arf_enable_next;?>')}, normalState, hoverState, pressedState).attr({id:'arf_next_button',class:'<?php echo $arf_disable_class_next;?>'}).add().toFront();
                jQuery('.highcharts-container').find('#arf_prev_button').find('text').remove();
                jQuery('.highcharts-container').find('#arf_prev_button').append('<svg version="1.1" xmlns="http://www.w3.org/2000/svg" x="12" y="10"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4786ff" d="M1.221,8.318l8.002,8.002l2.001-2L5.221,8.316l6.003-6.003  l-2.001-2L1.221,8.315l0.001,0.001L1.221,8.318z"/></svg>');
                jQuery('.highcharts-container').find('#arf_next_button').find('text').remove();
                jQuery('.highcharts-container').find('#arf_next_button').append('<svg version="1.1" xmlns="http://www.w3.org/2000/svg" x="12" y="10"><path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#4786ff" d="M11.431,8.601l-8.002,8.002l-2.001-2l6.003-6.003L1.428,2.596  l2.001-2l8.002,8.002L11.43,8.599L11.431,8.601z"/></svg>');
            }
        });
    </script>
<?php } ?>
<div id="chart_div">
    <div id="daily" style="padding:15px;<?php echo ($type == 'daily') ? 'display:block;' : 'display:none'; ?>">
        <div class="arlinks link_align">
            <?php
            $date_limit = date('Y-m-d', mktime(0, 0, 0, (int)$new_day_month, (int)$new_day, (int)$new_day_year));
            $min_date_limit = date('Y-m-d', mktime(0, 0, 0, $min_month, $min_date, $min_year));
            ?>
        </div>
        <div class="arfchart_display_title">
            <label class="arfcharttitle"><?php echo date(get_option('date_format'), strtotime($new_day . '-' . $new_day_month . '-' . $new_day_year)); ?></label>
        </div>
        <div id="chart4" style="width:100%;<?php echo ($graph_type == 'countries') ? 'height:400px;' : 'height:300px;';?>" ></div>
        
        <input type="hidden" value="<?php echo $new_day; ?>" name="current_day" id="current_day" />
        <input type="hidden" value="<?php echo $new_day_month; ?>" name="current_day_month" id="current_day_month" />
        <input type="hidden" value="<?php echo $new_day_year; ?>" name="current_day_year" id="current_day_year" />
    </div>

    <div id="monthly" style="padding:15px; <?php echo ($type == 'monthly') ? 'display:block;' : 'display:none'; ?>">
             <?php
             $monthName = date("F", mktime(0, 0, 0, (int)$new_month, 10));
             ?>
        <div class="arlinks link_align">
            <?php
            $month_limit = date('Y-m-d', mktime(0, 0, 0, (int)$new_month, 1, (int)$new_month_year));
            $min_month_limit = date('Y-m-d', mktime(0, 0, 0, $min_month, 1, $min_year));
            ?>

        </div>
        <div class="arfchart_display_title">
            <label class="arfcharttitle"><?php echo $monthName . "-" . $new_month_year; ?></label>
        </div>
        <div id="chart2" style="width:100%;<?php echo ($graph_type == 'countries') ? 'height:400px;' : 'height:300px;';?>"" ></div>
        <input type="hidden" value="<?php echo $new_month; ?>" name="current_month" id="current_month" />
        <input type="hidden" value="<?php echo $new_month_year; ?>" name="current_month_year" id="current_month_year" />
    </div>

    <div id="yearly" style="padding:15px; <?php echo ($type == 'yearly') ? 'display:block;' : 'display:none'; ?>">

        <div class="arlinks link_align">
        </div>
        <div class="arfchart_display_title">
            <label class="arfcharttitle"><?php echo $new_year; ?></label>
        </div>
        <div id="chart1" style="width:100%;<?php echo ($graph_type == 'countries') ? 'height:400px;' : 'height:300px;';?>" ></div>
        <input type="hidden" value="<?php echo $new_year; ?>" name="current_year" id="current_year" />
    </div>