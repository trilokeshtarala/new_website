<?php
global $arf_modal_view_in_menu_class;
$arf_modal_view_in_menu_class = new arf_modal_view_in_menu();

class arf_modal_view_in_menu {

    var $arf_id;
    var $arf_shortcode_type;
    var $arf_type;
    var $arf_desc;
    var $arf_height;
    var $arf_width;
    var $arf_overlay;
    var $arf_on_delay;
    var $arf_is_close_link;
    var $arf_modal_bgcolor;

    function __construct() {

        global $pagenow;
        $this->arf_id = '';
        $this->arf_shortcode_type = 'popup';
        $this->arf_type = 'link';
        $this->arf_desc = '&nbsp;';
        $this->arf_height = 'auto';
        $this->arf_width = '800';
        $this->arf_overlay = '0.6';
        $this->arf_on_delay = '0';
        $this->arf_is_close_link = 'yes';
        $this->arf_modal_bgcolor = '#000000';
        $this->arf_full_screen = 'no';
        $this->modal_effect = 'fade_in';

        add_action( 'admin_head-nav-menus.php', array($this, 'arf_add_nav_menu_metabox'), 10 );
        
        add_filter('wp_nav_menu',array($this,'arf_wp_loaded_walker_menu'),10,2);

        add_action('wp_footer', array($this, 'arf_nav_menu_add_javascript')); 

        if( isset($pagenow) && $pagenow != "" && $pagenow == 'nav-menus.php' ){
            add_action('admin_footer',array($this,'arf_edit_nav_menu'),10);
        }

        add_action('wp_update_nav_menu_item',array($this,'arf_add_nav_menu_meta_box'),10,3);

        add_action('wp_ajax_arf_get_post_meta_for_menu',array($this,'arf_get_post_meta_for_menu'));

    }

    function arf_get_post_meta_for_menu(){
        $response = array();
        if( isset($_REQUEST['ids']) && !empty($_REQUEST['ids']) ){
            $response['error'] = false;
            $response['res'] = array();
            $ids = json_decode(stripslashes_deep($_REQUEST['ids']));
            foreach ($ids as $key => $menu_id) {
                $response['res'][$menu_id] = get_post_meta($menu_id, 'arf_nav_menu_link_full_screen', true);
            }
        } else {
            $response['error'] = true;
        }
        echo json_encode($response);
        die();
    }

    function arf_add_nav_menu_meta_box($menu_id, $menu_item_db_id, $args){


        if (isset($_REQUEST['arf_show_full_screen_popup'])) {
            if (is_array($_REQUEST['arf_show_full_screen_popup'])) {
                $custom_value = isset($_REQUEST['arf_show_full_screen_popup'][$menu_item_db_id]) ? $_REQUEST['arf_show_full_screen_popup'][$menu_item_db_id] : 'no';
                update_post_meta($menu_item_db_id, 'arf_nav_menu_link_full_screen', $custom_value);
            }
        }
    }

    function arf_edit_nav_menu(){?>
    <script data-cfasync="false">
        jQuery(document).ajaxComplete(function (event, xhr, settings) {

            if (settings.data.match(/action=add-menu-item/) !== null && settings.data.match(/action=add-menu-item/).length > -1) {

                arf_add_menu_custom_meta_item();
            }
        });

        jQuery(document).ready(function(){
            arf_add_menu_custom_meta_item();
        });

         function arf_add_menu_custom_meta_item(){
            var menu_item_ids = new Array();
            jQuery('.arm-menu-item-hide-show').remove();

            var arforms_menu_ids = new Array();
            jQuery('[value="arf-form-slug"]').each(function(){
                var name = jQuery(this).attr('name');
                var menu_i = name.replace(/(menu-item\[\-(\d+)\]\[(menu\-item\-object)\])/,'-$2');
                var link = jQuery("[name='menu-item["+menu_i+"][menu-item-object-id]']").val();
                arforms_menu_ids.push(link);
            });


            jQuery('ul#menu-to-edit > li').each(function(){
                var $this = jQuery(this);
                var input = $this.find('.field-url input').val();

                var id = jQuery(this).attr('id');
                var menu_item_id = id.replace('menu-item-','');

                if( typeof input != 'undefined' ){
                    if( input.indexOf('menu-id=') < 0 ){
                        if( arforms_menu_ids.indexOf(input) > -1 ){
                            var updated_input = input + '&menu_id='+menu_item_id;
                            $this.find('.field-url input').val(updated_input);
                        }
                    }
                }

                var obj = jQuery(this).find('.menu-item-settings');
                var menu_item_type = jQuery(this).find('.item-type').text().toLowerCase();
                var controls = create_menu_item_meta_box(menu_item_type,menu_item_id);
                var new_text = controls.replace(/(\[ARF_MENU_ITEM_ID\])/g, menu_item_id);
                var control_html = jQuery.parseHTML(new_text);
                obj.find('.menu-item-actions').before(control_html);
                menu_item_ids.push(menu_item_id);
            });

            if( menu_item_ids.length > 0 ){
                var item_ids = JSON.stringify(menu_item_ids);
                jQuery.ajax({
                    url:'<?php echo admin_url('admin-ajax.php');?>',
                    method:'POST',
                    dataType:'json',
                    data:'action=arf_get_post_meta_for_menu&ids='+item_ids,
                    success:function(response){
                        jQuery.each( response.res, function( key, value ) {
                            if(value == 'yes'){
                                jQuery('#arf_full_scrn_yes-'+key).attr('checked',true);
                            }
                        });
                    }                        
                });
            }   
            
        }

         function create_menu_item_meta_box(menu_item_type,menu_item_id) {
            var meta_box_html = "";
            meta_box_html += '<p class="field-custom arf-menu-item-show-fullscreen">';

            meta_box_html += '<label for="edit-menu-item-custom-[ARF_MENU_ITEM_ID]">';
            meta_box_html += '<b><?php echo addslashes(esc_html__('Show Full Screen Modal', 'ARForms')); ?></b>&nbsp;&nbsp;';
           
            meta_box_html += '<input type="radio" id="arf_full_scrn_yes-[ARF_MENU_ITEM_ID]" class="widefat code" name="arf_show_full_screen_popup[[ARF_MENU_ITEM_ID]]" value="yes">';
            meta_box_html += '<label for="arf_full_scrn_yes-[ARF_MENU_ITEM_ID]"><?php echo addslashes(esc_html__('Yes', 'ARForms')); ?></label>&nbsp;&nbsp;';

            meta_box_html += '<input type="radio" id="arf_full_scrn_no-[ARF_MENU_ITEM_ID]" class="widefat code" name="arf_show_full_screen_popup[[ARF_MENU_ITEM_ID]]" value="no" checked="checked">';
            meta_box_html += '<label for="arf_full_scrn_no-[ARF_MENU_ITEM_ID]"><?php echo addslashes(esc_html__('No', 'ARForms')); ?></label>';

            meta_box_html += '</span>';
            meta_box_html += '</p>';
            return meta_box_html;
        }
    </script>
    <?php 
    }

    function arf_nav_menu_add_javascript() {
	?>
	<script>
	    
        function arf_open_modal_box_in_nav_menu(menu_id,form_id){
	    
	    var nav_menu_link_popup_data_id = jQuery("#arf_nav_menu_link_"+form_id).find('#arf_modal_default').attr('data-link-popup-id');
	       if(nav_menu_link_popup_data_id != ""){
		      jQuery("#arf_nav_menu_link_"+form_id).find(".arform_modal_link_"+form_id+"_"+nav_menu_link_popup_data_id).trigger( "click" );
	       }
        }
	</script>
	
    <?php }
    
    function arf_wp_loaded_walker_menu($nav_menu,$args){
	    global $maincontroller,$forms_in_menu,$arformcontroller;
        $forms_in_menu = array();
	    
	    $menu = $nav_menu;

            preg_match('/arfaction=(arf_modal_view_menu)/',$nav_menu,$matches);
            
            if( count( $matches ) > 0 ){
                $dom = new DOMDocument;
                if (extension_loaded('mbstring')) {
                    $dom->loadHTML(mb_convert_encoding($nav_menu, 'HTML-ENTITIES', 'UTF-8'));
                } else {
                    $dom->loadHTML(htmlspecialchars_decode(utf8_decode(htmlentities($nav_menu, ENT_COMPAT, 'utf-8', false))));
                }
                $n = new DOMXPath($dom);
                $new_menu = '';

                $anchor_tag = $dom->getElementsByTagName('a');
                foreach( $anchor_tag as $tag ){
                    $href = $tag->getAttribute('href');
                    $echo = "";
                    if( preg_match('/arfaction=(arf_modal_view_menu)/',$href) ){

                        $menu_id = '';
                        /* changes for notice warning need to confirm */
                        if(isset($args->menu->term_id)) {
                            $menu_id = $args->menu->term_id;
                        }
                        
                        if (!is_admin()) {
                            $maincontroller->front_head_js();
                        }
                        $arf_menu_array = array();
                        $arf_menu_elems = explode("&", str_replace('&amp;', '&', $href));
                        if (!empty($arf_menu_elems)) {
                            foreach ($arf_menu_elems as $arf_menu_elem) {
                                if (!empty($arf_menu_elem)) {
                                    $arf_link_pera = explode("=", $arf_menu_elem);
                                    $arf_menu_array[$arf_link_pera[0]] = $arf_link_pera[1];
                                }
                            }
                        }

                        $li_id = $arf_menu_array['menu_id'];
                        

                        if (!empty($arf_menu_array)) {
                            if (array_key_exists('id', $arf_menu_array) && !empty($arf_menu_array['id'])) {
                                
                                $this->arf_id    = $arf_menu_array['id'];
				                $forms_in_menu[] = $this->arf_id;
				
                                if (isset($arf_menu_array['height']) && !empty($arf_menu_array['height'])) {
                                    $this->arf_height = $arf_menu_array['height'];
                                }
                                if (isset($arf_menu_array['width']) && !empty($arf_menu_array['width'])) {
                                    $this->arf_width = $arf_menu_array['width'];
                                }
                                if (isset($arf_menu_array['overlay'])) {
                                    $this->arf_overlay = $arf_menu_array['overlay'];
                                }
                                if (isset($arf_menu_array['is_close_link']) && !empty($arf_menu_array['is_close_link'])) {
                                    $this->arf_is_close_link = $arf_menu_array['is_close_link'];
                                }
                                if (isset($arf_menu_array['modal_bgcolor']) && !empty($arf_menu_array['modal_bgcolor'])) {
                                    $this->arf_modal_bgcolor = $arf_menu_array['modal_bgcolor'];
                                }

                                $this->arf_full_screen = get_post_meta($li_id,'arf_nav_menu_link_full_screen',true);

                                if( isset($arf_menu_array['modal_effect']) && !empty($arf_menu_array['modal_effect'])) {
                                    $this->modal_effect = $arf_menu_array['modal_effect'];
                                }

				                /*Menu id is not set than assign form id*/
                                if($menu_id===''){
                                   $menu_id = $this->arf_id;
                                }
                                $atts['onClick'] = "arf_open_modal_box_in_nav_menu('$menu_id','$this->arf_id');";
                                echo $arformcontroller->arf_get_form_style($this->arf_id,'',$this->arf_type , '','', '', '', $this->arf_modal_bgcolor,$this->arf_overlay ,$this->arf_full_screen) ;
                                echo '<div id="arf_nav_menu_link_'.$this->arf_id.'" style="display:none;">' .
                                do_shortcode('['
                                        . 'ARForms_popup id=' . $this->arf_id 
                                        . ' type="' . $this->arf_type . '"'
                                        . ' is_navigation="true"'
                                        . ' desc="' . $this->arf_desc . '"'
                                        . ' height="' . $this->arf_height . '"'
                                        . ' width="' . $this->arf_width . '"'
                                        . ' on_delay="' . $this->arf_on_delay . '"'
                                        . ' overlay="' . $this->arf_overlay . '"'
                                        . ' is_close_link="' . $this->arf_is_close_link . '"'
                                        . ' modal_bgcolor="' . $this->arf_modal_bgcolor . '"'
                                        . ' is_fullscreen="'.$this->arf_full_screen.'"'
                                        . ' modaleffect="'.$this->modal_effect.'"'
                                        . ']')
                                . '</div>';
                            }
                            $tag->setAttribute('href','javascript:void(0)');
                            $tag->setAttribute('onClick',$atts['onClick']);
                        }

                    }
                  
                    $new_menu = preg_replace('/^<!DOCTYPE.+?>/', '', str_replace( array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), $dom->saveHTML()));
                }
                $nav_menu = $new_menu;
            }
            return $nav_menu;
        }
    
    function arf_add_nav_menu_metabox(){
            add_meta_box( 'arformnav', addslashes(esc_html__( 'ARForms Forms','ARForms' )), array($this, 'arf_from_menu_metabox'), 'nav-menus', 'side', 'default' );
            ?>
            <style type="text/css">
                .arformnav .accordion-section-title.hndle,
                .arformnav.open .accordion-section-title.hndle{
                    background: #4786ff !important;
                    background-color: #4786ff !important;
                    border-top: 1px solid #ffffff !important;
                    color: #ffffff;
                    margin: -6px 0 0;
                    padding-left: 40px;
                    position: relative;
                }
                .arformnav .accordion-section-title.hndle:focus,
                .arformnav .accordion-section-title.hndle:hover
                {
                    background-color: #4786ff;
                    color: white;
                    margin: -6px 0 0;
                    position: relative;
                }
                .arformnav .accordion-section-title.hndle::before{
                    background-image: url(<?php echo ARFIMAGESURL.'/appearance_menu_icon_24X24.png' ?>);
                    background-repeat: no-repeat;
                    height: 25px;
                    width: 25px;
                    content: " ";
                    position: absolute;
                    left: 8px;
                }


                .arformnav .accordion-section-title::after{
                    color: #fff !important;
                }
                #menu-settings-column .arformnav .inside{
                    margin: 0;
                }

                /* RTL CSS */
                body.rtl .arformnav .accordion-section-title.hndle{
                    padding-left: 10px;
                    padding-right: 40px;
                }

                body.rtl .arformnav .accordion-section-title.hndle::before{
                    right: 8px;
                    left: inherit;
                }
            </style>
        <?php }
        
    function arf_from_menu_metabox($object){
            global $nav_menu_selected_id,$wpdb,$MdlDb,$arformcontroller;
            // Create an array of objects that imitate Post objects
            $form_items = array();
             
                $form_list = $wpdb->get_results( $wpdb->prepare("SELECT * FROM ".$MdlDb->forms." WHERE is_template = %d AND (status is NULL OR status = '' OR status = 'published') order by id desc", 0), OBJECT_K );
          

            if(!empty($form_list))
            {
                
            foreach($form_list as $_form){

                    $_flabel = wp_strip_all_tags($_form->name.' (Form ID: '.$_form->id.')');
                    $_flabel = stripslashes_deep($_flabel);
                    $_fid = $_form->id;
                    $r_navigation_link = home_url(). "?arfaction=arf_modal_view_menu&id=";
                    $r_navigation_link .= $_fid;
                    $r_navigation_link .= "&width=800&height=auto&on_delay=0&overlay=0.6&is_close_link=yes&modal_bgcolor=#000000";
                    $form_items[] = (object) array(
                    'ID' => 1,
                    'db_id' => 0,
                    'menu_item_parent' => 0,
                    'object_id' => $r_navigation_link,
                    'post_parent' => 0,
                    'type' => 'custom',
                    'object' => 'arf-form-slug',
                    'type_label' => 'ARForms Plugin',
                    'title' => $_flabel,
                    'url' => $r_navigation_link,
                    'target' => '',
                    'attr_title' => '',
                    'description' => '',
                    'classes' => array(),
                    'xfn' => '',
                ); 
            }
            $db_fields = false;
            // If your links will be hieararchical, adjust the $db_fields array bellow
            if ( false ) {
                $db_fields = array( 'parent' => 'parent', 'id' => 'post_parent' );
            }
            $walker = new Walker_Nav_Menu_Checklist( $db_fields );
            $removed_args = array(
                'action',
                'customlink-tab',
                'edit-menu-item',
                'menu-item',
                'page-tab',
                '_wpnonce',
            );
            ?>
            <div id="login-links" class="loginlinksdiv posttypediv">
                <div><p style='color:red;'><?php echo addslashes(esc_html__("NOTE: This feature will only work with those themes which has support of WordPress' navigation menu core hooks.", 'ARForms'));?></p></div>
                <p><?php echo addslashes(esc_html__("This navigation menu link will open ARForms form in Modal Window.", 'ARForms'));?></p>
                <div id="tabs-panel-login-links-all" class="tabs-panel tabs-panel-view-all tabs-panel-active">
                    <ul id="login-linkschecklist" class="list:login-links categorychecklist form-no-clear">
                        <?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $form_items ), 0, (object) array( 'walker' => $walker ) ); ?>
                    </ul>
                </div>
                <p class="button-controls">
                    <span class="list-controls">
                        <a href="<?php
                            echo esc_url(add_query_arg(
                                array(
                                    'my-plugin-all' => 'all',
                                    'selectall' => 1,
                                ),
                                remove_query_arg( $removed_args )
                            ));
                        ?>#arformnav" class="select-all"><?php echo addslashes(esc_html__( 'Select All','ARForms' )); ?></a>
                    </span>

                    <span class="add-to-menu">
                        <input type="submit"<?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu right" value="<?php echo esc_attr(addslashes(esc_html__( 'Add to Menu','ARForms' ))); ?>" name="add-login-links-menu-item" id="submit-login-links" />
                        <span class="spinner"></span>
                    </span>
                </p>
            </div>
<?php
        }// if completed
        else{
            echo addslashes(esc_html__('No Form Created Yet.','ARForms'));
        }
    }

}
?>