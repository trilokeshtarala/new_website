<?php
class arelementcontroller{

    
   function __construct() {

        add_action( 'plugins_loaded', array( $this, 'arf_element_widget' ) );
    
   } 
   function arf_element_widget(){
        if ( ! did_action( 'elementor/loaded' ) ) {
            return;
        }
        require_once(CONTROLLERS_PATH . '/arf_elm_widgets/arf_elementor_element.php');
   }
   
}
?>