<?php

class ARF_Dashboard_Widget{

    function __construct(){
        add_action('wp_dashboard_setup', array($this, 'arforms_all_dashboard_widgets'));
        add_action('admin_enqueue_scripts', array($this, 'arf_set_graf_js'));

    }

    function arforms_all_dashboard_widgets(){
        wp_add_dashboard_widget('ARForms-recently-analytics', esc_html__('Recent ARForms Entries', 'ARForms'), array($this, 'arforms_dashboard_widgets_recently_analytics_html'));
        wp_add_dashboard_widget('ARForms-add-ons', esc_html__('ARForms Add-Ons', 'ARForms'), array($this, 'arforms_dashboard_widgets_add_ons_list'));

    }

    function arf_set_graf_js(){
        global $arf_jscss_version;
        $screen = get_current_screen();
        if (is_admin() && $screen->id == 'dashboard') {
            wp_register_script('arfhighcharts-script',ARFURL . '/js/highcharts/arfhighcharts.js',array(),$arf_jscss_version);
            wp_enqueue_script('arfhighcharts-script');

            wp_register_style('arf-dashboard-widget-styles', ARFURL.'/css/arforms_dashboard.css',array(),$arf_jscss_version);
            wp_enqueue_style('arf-dashboard-widget-styles');
        }

    }

    function arforms_dashboard_widgets_recently_analytics_html(){

        global $wpdb;

        $current_date   = date('Y-m-d');
        $last_week_date = date('Y-m-d', strtotime('-6 days'));

        $day_array = $this->ArfDashboardMakeDayArray($last_week_date, $current_date);

        foreach ($day_array as $day) {

            $day_arr[$day] = $wpdb->get_results($wpdb->prepare('SELECT COUNT(*) AS num FROM ' . $wpdb->prefix . 'arf_entries WHERE CAST(created_date AS DATE) = %s', $day), 'ARRAY_A');

            $day_arr[$day] = $day_arr[$day][0];

            $day_view_arr[$day] = $wpdb->get_results($wpdb->prepare('SELECT COUNT(*) AS num FROM ' . $wpdb->prefix . 'arf_views WHERE CAST(added_date AS DATE) = %s', $day), 'ARRAY_A');

            $day_view_arr[$day] = $day_view_arr[$day][0];

        }

        $day_var = '[';

        $val_var = '[';

        $day_view_var = '[';

        foreach ($day_arr as $day => $val) {

            $day_var .= "'" . date('d', strtotime($day)) . "-" . date('M', strtotime($day)) . "', ";

            $val_var .= $val['num'] . ', ';

        }

        foreach ($day_view_arr as $dayView => $valView) {

            $day_view_var .= $valView['num'] . ', ';

        }

        $day_var .= ']';

        $val_var .= ']';

        $day_view_var .= ']';

        ?>

        <script type="text/javascript">

            jQuery(document).ready(function ($) {

                var entries = <?php echo $val_var; ?>;

                var views = <?php echo $day_view_var; ?>;

                var ticks_month = <?php echo $day_var; ?>;

                var chart = {
                    type:'areaspline'
                };
                var title ={
                    text:'Recently form entries'
                }
                var xAxis = {
                   categories: ticks_month
                };
                var credits = {
                   enabled: false
                };
                var series = [
                   {
                      name: 'Entries',
                      data: entries
                   },
                   {
                      name: 'Views',
                      data: views
                   }

                ];
                var colors = [{
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
                    }];
                var json = {};
                json.chart = chart;
                json.title = title;
                json.xAxis = xAxis;
                json.credits = credits;
                json.series = series;
                json.colors = colors;
                $('#chart_div').highcharts(json);


            });

        </script>
        <div class="footer" style="text-align: right;">

            <button type="button" onclick="location.href = '<?php echo esc_url(admin_url('admin.php')); ?>?page=ARForms-entries&tabview=analytics';" style="width:210px; border:0px; color:#FFFFFF; height:35px; border-radius:5px;" class="greensavebtn"><?php esc_html_e('View Complete Analytics', 'ARForms');?></button>
        </div>
        <div id="chart_div">
        </div>

        <?php

    }

    function ArfDashboardMakeDayArray($startDate, $endDate){
        $startDate = strtotime($startDate);
        $endDate   = strtotime($endDate);
        $currDate  = $startDate;

        $dayArray = array();
        do {

            $dayArray[] = date('Y-m-d', $currDate);

            $currDate = strtotime('+1 day', $currDate);

        } while ($currDate <= $endDate);

        return $dayArray;
    }

    function arforms_dashboard_widgets_add_ons_list(){

        $dashboard_add_ons_list_url = 'https://www.arformsplugin.com/addonlist/arforms_dashboad_addon_list.php';
        $dashboard_add_ons_list     = wp_remote_get($dashboard_add_ons_list_url, $args = array());

        if (is_wp_error($dashboard_add_ons_list)) {
            printf(esc_html__("There is something error to retrieve the %s add-ons list. Please try again later.", "ARForms"), 'ARForms');
        } else {
            $dashboard_add_ons_list = json_decode($dashboard_add_ons_list['body']);
            $arr__add_ons_list = apply_filters('arf_dashboard_add_more_add_ons', $dashboard_add_ons_list);
            ?>
            <div class="dataTables_wrapper" id="example_wrapper_addons">
                <table cellspacing="0" cellpadding="0" border="0" id="example" class="display dataTable" style="width: 100%; margin-bottom:10px; ">
                    <tbody>
                        <?php

                        if (!empty($arr__add_ons_list)) {

                            $list_tr_class = 'even';
                            $list_tr_class_ext = '';

                            $total_addons = count($arr__add_ons_list);

                            $last_tr_counter = ceil($total_addons / 4);

                            echo '<tr class="' . $list_tr_class . ' arf_frist_addons_icon" >';

                            $list_counter = 1;
                            $row_counter = 1;

                            foreach( $arr__add_ons_list as $key => $add_ons_list ){
                                $add_ons_list_link = $add_ons_list->link;
                                $add_ons_list_img  = $add_ons_list->img;
                                $add_ons_list_name = $add_ons_list->name;
                                //$add_ons_list_label = $add_ons_list->label;
                                $add_on_td_class = '';

                                if( $list_counter % 4 == 0 ){
                                    $add_on_td_class = ' addon_list_no_border ';
                                }
                            ?>
                            <td class="add-ons-icon <?php echo $add_on_td_class; ?>">
                                <a target="_blank" class="add-ons-icon-image" href="<?php echo $add_ons_list_link; ?>" title='<?php echo $add_ons_list_name; ?>'>
                                    <img src="<?php echo $add_ons_list_img; ?>"  alt='<?php echo $add_ons_list_name; ?>'/>
                                </a>
                                <!-- <label class="add-ons-icon-label">< ?php echo $add_ons_list_label; ?></label> -->
                            </td>
                            <?php
                                if( $list_counter % 4 == 0 && $list_counter < $total_addons ){
                                    echo '</tr>';
                                    $list_tr_class = ($list_tr_class == 'even') ? ' odd ' : ' even ';
                                    $row_counter++;
                                    if( $row_counter == $last_tr_counter ){
                                        $list_tr_class_ext = ' addons_last_row ';
                                    }
                                    echo '<tr class="'.$list_tr_class.' '.$list_tr_class_ext.'">';
                                }

                                if( $list_counter == $total_addons ){
                                    echo '</tr>';
                                }
                                $list_counter++;
                            }

                        }

                        ?>

                    </tbody>
                </table>
            </div>

            <?php
        }

    }

}

?>