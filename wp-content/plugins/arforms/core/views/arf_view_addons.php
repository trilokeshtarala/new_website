
<style type="text/css">
    .addon_container{
        position:relative;
        display: inline-block;
        width:290px;
        border:1px solid #dee6fb;
        margin:0 25px 25px 0;
        -webkit-border-radius: 4px;
        -moz-border-radius: 4px;
        -o-border-radius: 4px;
        border-radius: 4px;
        overflow: hidden;
    }
    .addon_container_activated{
        position:absolute;
        position: absolute;
        border-top: 50px solid #03a9f4;
        border-left: 50px solid transparent;
        border-bottom: 50px solid transparent;
        border-right: none;
        top: 0;
        right: 0;
        margin: 0;
    }
    .addon_container_activated::before{
        content: "";
        position: absolute;
        top: -44px;
        left: -19px;
        border: 3px solid #ffffff;
        width: 6px;
        height: 12px;
        transform: rotate(40deg);
        -webkit-transform: rotate(40deg);
        -o-transform: rotate(40deg);
        -moz-transform: rotate(40deg);
        border-left: none;
        border-top: 0;
    }
    .addon_image{
        border-bottom:1px solid #dee6fb;
        margin-bottom: -4px;
    }
    .addon_title a{
        font-family:'Asap-medium';
        font-size:16px;
        text-align:center;
        font-weight:bold;
        color:#353942;
        float:left;
        width:100%;
        margin:20px 0;
        text-decoration:none;
    }
    .addon_description{
        font-family:'Asap-regular';
        font-size:15px;
        text-align:center;
        color:#4e5462;
        background-color: #ffffff;
        height: 145px;
        padding: 10px 10px 0px 10px;
        line-height:20px;
    }
    .addon_button.no_icon{
        position:relative;
    }
    .add_more{
        font-family: 'Asap-regular';
        font-size: 16px;
        text-align: center;
        color: #353942;
        background-color: #ffffff;
        height: 35px;
        margin-top: 0px;
    }
    .add_more a{
        font-family: 'Asap-medium';
        font-size: 16px;
        margin-right: 13px;
        float: right;
        text-decoration: none;
        color: #ff6b23;
    }
    .addon_button{
        border:none;
        border-top:1px solid #dee6fb;
        padding: 6px 0px;
        height:43px;
        background: rgb(246, 253, 255);
        background: -webkit-radial-gradient(rgb(255, 255, 255), rgb(246, 253, 255)100%, rgb(246, 253, 255));
        background: -o-radial-gradient(rgb(255, 255, 255), rgb(246, 253, 255)100%, rgb(246, 253, 255));
        background: -moz-radial-gradient(rgb(255, 255, 255), rgb(246, 253, 255)100%, rgb(246, 253, 255));
        background: radial-gradient(rgb(255, 255, 255), rgb(246, 253, 255)100%, rgb(246, 253, 255));
        text-align: center;
        width:100%;
        cursor:pointer;
        line-height:40px;
        outline:none;
    }
    .addon_button.addon_processing{
        background:#ffffff;
        text-align:center;
    }
    .addon_button.addon_processing .arf_addon_loader{
        left:46% !important;
    }
    .addon_button:hover,
    .addon_button:active,
    .addon_button:focus{
        outline:none;
    }
    .addon_container:hover{
        -webkit-box-shadow: 0px 0px 15px 0px rgba(0, 132, 255, 0.15);
        -o-box-shadow: 0px 0px 15px 0px rgba(0, 132, 255, 0.15);
        -moz-box-shadow:    0px 0px 15px 0px rgba(0, 132, 255, 0.15);
        box-shadow:         0px 0px 15px 0px rgba(0, 132, 255, 0.15);
        cursor:pointer;
    }
    .addon_processing_tick{
        position: absolute;
        color: #23c875;
        width: 100%;
        left: 0;
        padding-left: 27px;
        z-index: 999;
        background: #fff;
        top: 0px;
        text-transform: none;
        font-family: Asap-Medium;
        height:100%;
        display:none;
    }
    .addon_processing_tick::before{
        content:"";
        position:absolute;
        top:25%;
        left:33%;
        transform:translate(-50%,-30%);
        -webkit-transform:translate(-50%,-30%);
        -o-transform:translate(-50%,-30%);
        -moz-transform:translate(-50%,-30%);
        border:3px solid #23c875;
        width:6px;
        height:12px;
        color:#23c875;
        transform: rotate(40deg);
        -webkit-transform: rotate(40deg);
        -o-transform: rotate(40deg);
        -moz-transform: rotate(40deg);
        border-left: none;
        border-top: 0;
    }
    .addon_processing_tick_deactivation{
        position: absolute;
        color: #f05350;
        width: 100%;
        left: 0;
        padding-left: 28px;
        z-index: 999;
        background: #fff;
        top: 0px;
        text-transform: none;
        font-family: Asap-Medium;
        height:100%;
        display:none;
    }
    .addon_processing_tick_deactivation::before{
        content:"";
        position:absolute;
        top:25%;
        left:29.2%;
        transform:translate(-50%,-30%);
        -webkit-transform:translate(-50%,-30%);
        -o-transform:translate(-50%,-30%);
        -moz-transform:translate(-50%,-30%);
        border:3px solid #f05350;
        width:6px;
        height:12px;
        color:#23c875;
        transform: rotate(40deg);
        -webkit-transform: rotate(40deg);
        -o-transform: rotate(40deg);
        -moz-transform: rotate(40deg);
        border-left: none;
        border-top: 0;
    }
    
    .addon_container:hover .addon_button span svg g path{
        fill:#ffffff !important;
    }
    .addon_container:hover .addon_button{
        background: #03a9f4;
    }
    .addon_container:hover .addon_button{
        color:#ffffff;
    }
    .get_it_a{
        display: inline-block;
        margin-top: -17px;
        vertical-align: middle;
        margin-left: 10px;
    }
    .addon_button{
        font-family: 'Asap-medium';
        font-size: 19px;
        text-decoration: none;
        color:#8e9fb2;
        text-transform: uppercase;
    }
    .arf_addon_loader{
        position: absolute;
        width: 30px;
        height: 30px;
        left: 75%;
        top: 58%;
        transform: translateY(-50%);
        -webkit-transform: translateY(-50%);
        -o-transform: translateY(-50%);
        -moz-transform: translateY(-50%);
    }
    .arf_circular {
        -webkit-animation: rotate 2s linear infinite;
        animation: rotate 2s linear infinite;
        height: 100%;
        -webkit-transform-origin: 43% 37%;
        transform-origin: 43% 37%;
        width: 100%;
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        margin: auto;
        display:none;
    }

    .path {
        stroke-dasharray: 1, 200;
        stroke-dashoffset: 0;
        stroke-linecap: round;
        -webkit-animation: dash 1.5s ease-in-out infinite, color_blue 6s ease-in-out infinite;
        animation: dash 1.5s ease-in-out infinite, color_blue 6s ease-in-out infinite;
    }


    .addon_container:hover .path{
        -webkit-animation: dash 1.5s ease-in-out infinite, color 6s ease-in-out infinite;
        animation: dash 1.5s ease-in-out infinite, color 6s ease-in-out infinite;
    }

    @-webkit-keyframes rotate {
        100% {
            -webkit-transform: rotate(360deg);
            transform: rotate(360deg);
        }
    }

    @keyframes rotate {
        100% {
            -webkit-transform: rotate(360deg);
            transform: rotate(360deg);
        }
    }
    @-webkit-keyframes dash {
        0% {
            stroke-dasharray: 1, 200;
            stroke-dashoffset: 0;
        }
        50% {
            stroke-dasharray: 89, 200;
            stroke-dashoffset: -35px;
        }
        100% {
            stroke-dasharray: 89, 200;
            stroke-dashoffset: -124px;
        }
    }
    @keyframes dash {
        0% {
            stroke-dasharray: 1, 200;
            stroke-dashoffset: 0;
        }
        50% {
            stroke-dasharray: 89, 200;
            stroke-dashoffset: -35px;
        }
        100% {
            stroke-dasharray: 89, 200;
            stroke-dashoffset: -124px;
        }
    }
    @-webkit-keyframes color {
        100%,
        0% {
            stroke: #ffffff;
        }
        40% {
            stroke: #ffffff;
        }
        66% {
            stroke: #ffffff;
        }
        80%,
        90% {
            stroke: #ffffff;
        }
    }
    @-webkit-keyframes color_blue {
        100%,
        0%{
            stroke: #4786ff;
        }
        40% {
            stroke: #4786ff;
        }
        66% {
            stroke: #4786ff;
        }
        80%,
        90% {
            stroke: #4786ff;
        }
    }
    @keyframes color {
        100%,
        0% {
            stroke: #ffffff;
        }
        40% {
            stroke: #ffffff;
        }
        66% {
            stroke: #ffffff;
        }
        80%,
        90% {
            stroke: #ffffff;
        }
    }
    @-keyframes color_blue {
        100%,
        0%{
            stroke: #4786ff;
        }
        40% {
            stroke: #4786ff;
        }
        66% {
            stroke: #4786ff;
        }
        80%,
        90% {
            stroke: #4786ff;
        }
    }
</style>

<?php

global $arsettingcontroller;

if($arf_addons == ''){

    echo "<div class='error_message' style='margin-top:100px; padding:20px;'>" . addslashes(esc_html__("Add-On listing is currently unavailable. Please try again later.", "ARForms")) . "</div>";

} else {

    $arf_addons = maybe_unserialize(base64_decode($arf_addons));

    $plugins = get_plugins();
    $installed_plugins = array();
    foreach ($plugins as $key => $plugin) {
        $is_active = is_plugin_active($key);
        $installed_plugin = array("plugin" => $key, "name" => $plugin["Name"], "is_active" => $is_active);

        $installed_plugin["activation_url"] = $is_active ? "" : wp_nonce_url("plugins.php?action=activate&plugin={$key}", "activate-plugin_{$key}");
        $installed_plugin["deactivation_url"] = !$is_active ? "" : wp_nonce_url("plugins.php?action=deactivate&plugin={$key}", "deactivate-plugin_{$key}");

        $installed_plugins[] = $installed_plugin;
    }

    if (is_array($arf_addons) && count($arf_addons) > 0) {

        foreach ($arf_addons as $arf_addon) {

            $is_active_addon = is_plugin_active($arf_addon['plugin_installer']);

            ?>

            <div class="addon_container">

            <?php
                if ($is_active_addon == 1) {
                    echo "<div class='addon_container_activated'></div>";
                }
            ?>

                <div class="addon_image">
                    <a href="<?php echo $arf_addon['detail_url']; ?>" target="_blank"><img src="<?php echo $arf_addon['image']; ?>" width="290" height="119" /></a>
                </div>

                <div class="addon_title">
                    <a href="<?php echo $arf_addon['detail_url']; ?>" target="_blank"><?php echo $arf_addon['full_name']; ?></a></div>

                <div class="addon_description"><?php echo $arf_addon['description']; ?></div>

                <div class="add_more">
                    <a href="<?php echo $arf_addon['detail_url']; ?>" class="addon_readmore" target="_blank"><?php echo addslashes(esc_html__('Read More...', 'ARForms')); ?></a>
                </div>

                <?php echo $arsettingcontroller->CheckpluginStatus($installed_plugins, $arf_addon['plugin_installer'], 'plugin', $arf_addon['short_name'], $arf_addon['plugin_type'], $arf_addon['install_url']); ?>

            </div>

            <?php
        }
    }


}

$_SESSION['arforms_addon'] = $arf_addons;

?>

<div id="error_message" class="arf_error_message">
    <div class="message_descripiton">
        <div id="arf_plugin_install_error" style="float: left; margin-right: 15px;" id=""><?php echo addslashes(esc_html__('File is not proper.', 'ARForms')); ?></div>
        <div class="message_svg_icon">
            <svg style="height: 14px;width: 14px;"><path fill-rule="evenodd" clip-rule="evenodd" fill="#ffffff" d="M10.702,10.909L6.453,6.66l-4.249,4.249L1.143,9.848l4.249-4.249L1.154,1.361l1.062-1.061l4.237,4.237l4.238-4.237l1.061,1.061L7.513,5.599l4.249,4.249L10.702,10.909z"></path></svg>
        </div>
    </div>
</div>