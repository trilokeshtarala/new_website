<?php
namespace ElementorARFELEMENT\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) exit; 


class arf_element_shortcode extends Widget_Base {

	public function get_name() {
		return 'arf-element-shortcode';
	}

	
	public function get_title() {
		return esc_html__( 'ARForms', 'ARForms' ).'<style>
		.arf_element_icon{
			display: inline-block;
		    width: 35px;
		    height: 24px;
		    background-image: url('.ARFIMAGESURL.'/logo_el.svg);
		    background-repeat: no-repeat;
		    background-position: bottom;
		}
		.arf_frm_type_el .elementor-choices-label .elementor-screen-only{
			position: relative;
			top: 0;
		}
		.arf_click_type_el .elementor-choices-label .elementor-screen-only{
			position: relative;
			top: 0;
		}	
		.arf_show_cl_elbtn .elementor-choices-label .elementor-screen-only{
			position: relative;
			top: 0;
		}
		.arf_show_full_screen_popup .elementor-choices-label .elementor-screen-only{
			position: relative;
			top: 0;
		}
		</style>
		';
	}

	
	public function get_icon() {
		//return 'eicon-posts-ticker';
		return 'arf_element_icon';
	}

	
	public function get_categories() {
		return [ 'general' ];
	}

	
	public function get_script_depends() {
		return [ 'elementor-arf-element' ];
	}

	protected function _register_controls() {
		global $arfform, $armainhelper;
		$where = apply_filters('arfformsdropdowm', "is_template=0 AND (status is NULL OR status = '' OR status = 'published')",'arf_select');
		$forms = $arfform->getAll($where, ' ORDER BY name');
		$arf_forms=array();
        $arf_forms['Please select a valid form']='Please select form';
		if($forms){
			foreach ($forms as $form) {
					$arf_forms['id='.$form->id]=$armainhelper->truncate($form->name, 33);
			}	
		}
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'ARForms Shortcode', 'ARForms' ),
			]
		);

		$this->add_control(
			'title',
			[
				'label' => esc_html__( 'Title', 'ARForms' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
			]
		);
		$this->add_control(
			'arf_select',
			[
				'label' => esc_html__( 'Forms :', 'ARForms'),
				'type' => Controls_Manager::SELECT,
				'default' => 'Please select a valid form',
				'options' => $arf_forms,
				'label_block' => true,
				
			]
		);
		$this->add_control(
			'arf_frm_type',
			[
				'label' => esc_html__( 'Form Type :', 'ARForms'),
				'type' => Controls_Manager::CHOOSE,
				'default' => 'ARForms',
				'options' => [
					'ARForms' => [
						'title' => esc_html__( 'Internal', 'ARForms' ),
					],
					'ARForms_popup' => [
						'title' => esc_html__( 'Modal (popup) Window', 'ARForms' ),
					],
				],
				'label_block' => true,
				'classes'=>'arf_frm_type_el',
				
			]
		);
		$this->add_control(
			'arf_popup_label',
			[
				'label' => esc_html__( 'Label :', 'ARForms' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' =>'Click here to open Form',
				'condition'=>['arf_frm_type' => 'ARForms_popup','arf_model_trigger_type'=>'onclick'],
			]
		);
		$this->add_control(
			'arf_model_trigger_type',
			[
				'label' => esc_html__( 'Modal Trigger Type :','ARForms'),
				'type' => Controls_Manager::SELECT,
				'default' => 'onclick',
				'options' => [
							"onclick" =>"On Click",
                            "onload"  =>"On Page Load",
                            "scroll"  =>"On Page Scroll",
                            "timer"   =>"On Timer(Scheduled)",
                            "on_exit" =>"On Exit(Exit Intent)",
                            "on_idle" =>"On Idle",
                        ],
				'label_block' => true,
				'condition'=>['arf_frm_type' => 'ARForms_popup'],
				
			]
		);
		$this->add_control(
			'arf_click_type',
			[
				'label' => esc_html__( 'Click Types :', 'ARForms'),
				'type' => Controls_Manager::CHOOSE,
				'default' => 'link',
				'options' => [
					'link' => [
						'title' => esc_html__( 'Link', 'ARForms' ),
					],
					'button' => [
						'title' => esc_html__( 'Button', 'ARForms' ),
					],
					'sticky' => [
						'title' => esc_html__( 'Sticky', 'ARForms' ),
					],
					'fly' => [
						'title' => esc_html__( 'Fly', 'ARForms' ),
					],
				],
				'label_block' => true,
				'condition'=>['arf_frm_type' => 'ARForms_popup','arf_model_trigger_type'=>'onclick'],
				'classes'=>'arf_click_type_el',
				
			]
		);
		$this->add_control(
			'arf_link_position',
			[
				'label' => esc_html__( 'Link Position ?', 'ARForms'),
				'type' => Controls_Manager::CHOOSE,
				'default' => 'top',
				'options' => [
					'top' => [
						'title' => esc_html__( 'Top', 'ARForms' ),
					],
					'bottom' => [
						'title' => esc_html__( 'Bottom', 'ARForms' ),
					],
					'left' => [
						'title' => esc_html__( 'Left', 'ARForms' ),
					],
					'right' => [
						'title' => esc_html__( 'Right', 'ARForms' ),
					],
				],
				'label_block' => true,
				'condition'=>['arf_frm_type' => 'ARForms_popup','arf_click_type'=>'sticky'],
				'classes'=>'arf_click_type_el',
				
			]
		);
		$this->add_control(
			'arf_fly_link_position',
			[
				'label' => esc_html__( 'Link Position ?', 'ARForms'),
				'type' => Controls_Manager::CHOOSE,
				'default' => 'left',
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'ARForms' ),
					],
					'right' => [
						'title' => esc_html__( 'Right', 'ARForms' ),
					],
				],
				'label_block' => true,
				'condition'=>['arf_frm_type' => 'ARForms_popup','arf_click_type'=>'fly'],
				'classes'=>'arf_click_type_el',
				
			]
		);
		$this->add_control(
			'arf_popup_on_scoll_position',
			[
				'label' => esc_html__( 'Open popup when user scroll % of page after page load :', 'ARForms' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' =>'10',
				'condition'=>['arf_frm_type' => 'ARForms_popup','arf_model_trigger_type'=>'scroll'],
				'description'=>'%  (eg. 100% - end of page)',
			]
		);
		$this->add_control(
			'arf_popup_after_page_load',
			[
				'label' => esc_html__( 'Open popup after page load (in seconds) :', 'ARForms' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' =>'0',
				'condition'=>['arf_frm_type' => 'ARForms_popup','arf_model_trigger_type'=>'timer'],
				
			]
		);
		$this->add_control(
			'arf_back_overlay',
			[
				'label' => esc_html__( 'Background Overlay :','ARForms'),
				'type' => Controls_Manager::SELECT,
				'default' => '0.6',
				'options' => [
							"0"		=>"0 (None)",
                            "0.1" 	=>"10%",
                            "0.2"  	=>"20%",
                            "0.3"   =>"30%",
                            "0.4" 	=>"40%",
                            "0.5" 	=>"50%",
                            "0.6" 	=>"60%",
                            "0.7" 	=>"70%",
                            "0.8" 	=>"80%",
                            "0.9" 	=>"90%",
                            "1" 	=>"100%",

                        ],
				'label_block' => true,
				'condition'=>['arf_frm_type' => 'ARForms_popup','arf_model_trigger_type'=>['onload','scroll','timer','on_exit','on_idle']],
				
			]
		);
		$this->add_control(
			'arf_back_color',
			[
				'label' => esc_html__( 'Background Color :', 'ARForms' ),
				'type' => Controls_Manager::COLOR,
				'label_block' => true,
				'default' =>'#000000',
				'condition'=>['arf_frm_type' => 'ARForms_popup','arf_model_trigger_type'=>['onload','scroll','timer','on_exit','on_idle']],
				'classes'=>'arf_back_color_style',
			]
		);
		$this->add_control(
			'arf_click_back_overlay',
			[
				'label' => esc_html__( 'Background Overlay :','ARForms'),
				'type' => Controls_Manager::SELECT,
				'default' => '0.6',
				'options' => [
							"0"		=>"0 (None)",
                            "0.1" 	=>"10%",
                            "0.2"  	=>"20%",
                            "0.3"   =>"30%",
                            "0.4" 	=>"40%",
                            "0.5" 	=>"50%",
                            "0.6" 	=>"60%",
                            "0.7" 	=>"70%",
                            "0.8" 	=>"80%",
                            "0.9" 	=>"90%",
                            "1" 	=>"100%",

                        ],
				'label_block' => true,
				'condition'=>['arf_frm_type' => 'ARForms_popup','arf_click_type'=>['link','button']],
				
			]
		);
		$this->add_control(
			'arf_click_back_color',
			[
				'label' => esc_html__( 'Background Color :', 'ARForms' ),
				'type' => Controls_Manager::COLOR,
				'label_block' => true,
				'default' =>'#000000',
				'condition'=>['arf_frm_type' => 'ARForms_popup','arf_click_type'=>['link','button']],
				'classes'=>'arf_back_color_style',
			]
		);
		$this->add_control(
			'arf_btn_back_color',
			[
				'label' => esc_html__( 'Button Background Color :', 'ARForms' ),
				'type' => Controls_Manager::COLOR,
				'label_block' => true,
				'default' =>'#8ccf7a',
				'condition'=>['arf_frm_type' => 'ARForms_popup','arf_model_trigger_type'=>'onclick','arf_click_type'=>['button','sticky','fly']],
				'classes'=>'arf_back_color_style',
			]
		);
		$this->add_control(
			'arf_btn_text_color',
			[
				'label' => esc_html__( 'Text Color :', 'ARForms' ),
				'type' => Controls_Manager::COLOR,
				'label_block' => true,
				'default' =>'#ffffff',
				'condition'=>['arf_frm_type' => 'ARForms_popup','arf_model_trigger_type'=>'onclick','arf_click_type'=>['button','sticky','fly']],
				'classes'=>'arf_back_color_style',
			]
		);
		$this->add_control(
			'arf_show_cl_btn',
			[
				'label' => esc_html__('Show Close Button :','ARForms'),
				'type' => Controls_Manager::CHOOSE,
				'default' =>'yes',
				'options' => [
					'yes' => [
						'title' => esc_html__( 'Yes', 'ARForms' ),
					],
					'no' => [
						'title' => esc_html__( 'No', 'ARForms' ),
					],
				],
				'condition'=>['arf_frm_type' => 'ARForms_popup','arf_model_trigger_type'=>['onload','scroll','timer','on_exit','on_idle']],
				'classes'=>'arf_show_cl_elbtn',
				
			]
		);
		$this->add_control(
			'arf_click_show_cl_btn',
			[
				'label' => esc_html__('Show Close Button :','ARForms'),
				'type' => Controls_Manager::CHOOSE,
				'default' =>'yes',
				'options' => [
					'yes' => [
						'title' => esc_html__( 'Yes', 'ARForms' ),
					],
					'no' => [
						'title' => esc_html__( 'No', 'ARForms' ),
					],
				],
				'condition'=>['arf_frm_type' => 'ARForms_popup','arf_click_type'=>['link','button']],
				'classes'=>'arf_show_cl_elbtn',
				
			]
		);
		$this->add_control(
			'arf_height',
			[
				'label' => esc_html__( 'Height :', 'ARForms' ),
				'type' => Controls_Manager::TEXT,
				'default' =>'auto',
				'condition'=>['arf_frm_type' => 'ARForms_popup','arf_click_type'=>'sticky'],
			]
		);
		$this->add_control(
			'arf_width',
			[
				'label' => esc_html__( 'Width :', 'ARForms' ),
				'type' => Controls_Manager::TEXT,
				'default' =>'800',
				'description'=>'Form width will be overwritten',
				'condition'=>['arf_frm_type' => 'ARForms_popup'],
			]
		);
		$this->add_control(
			'arf_btn_angle',
			[
				'label' => esc_html__( 'Button Angle :','ARForms'),
				'type' => Controls_Manager::SELECT,
				'default' => '0',
				'options' => [
							"0"=>"0",
							"90"=>"90",
							"-90"=>"-90",
			            ],
				'label_block' => false,
				'condition'=>['arf_frm_type' => 'ARForms_popup','arf_click_type'=>'fly'],
				
			]
		);
		$this->add_control(
			'arf_popup_after_user_inactive',
			[
				'label' => esc_html__( 'Show after user is inactive for :', 'ARForms' ),
				'type' => Controls_Manager::TEXT,
				'default' => '1',
				'label_block' => true,
				'condition'=>['arf_frm_type' => 'ARForms_popup','arf_model_trigger_type'=>'on_idle'],
				'description'=>'minute',
				
			]
		);
		$this->add_control(
			'arf_animation_effect',
			[
				'label' => esc_html__( 'Animation Effect :','ARForms'),
				'type' => Controls_Manager::SELECT,
				'default' => 'no_animation',
				'options' => [
							"no_animation"=>"No Animation",
							"fade_in"=>"Fade In",
							"slide_in_top"=>"Slide in Top",
							"slide_in_bottom"=>"Slide In Bottom",
							"slide_in_right"=>"Slide In Right",
							"slide_in_left"=>"Slide In Left",
							"zoom_in"=>"Zoom In",

                        ],
				'label_block' => true,
				'condition'=>['arf_frm_type' => 'ARForms_popup','arf_model_trigger_type'=>['onload','scroll','timer','on_exit','on_idle']],
				
			]
		);
		$this->add_control(
			'arf_show_full_screen_popup',
			[
				'label' => esc_html__('Show Full Screen Popup :','ARForms'),
				'type' => Controls_Manager::CHOOSE,
				'default' =>'no',
				'options' => [
					'yes' => [
						'title' => esc_html__( 'Yes', 'ARForms' ),
					],
					'no' => [
						'title' => esc_html__( 'No', 'ARForms' ),
					],
				],
				'condition'=>['arf_frm_type' => 'ARForms_popup','arf_model_trigger_type'=>['onload','scroll','timer','on_exit','on_idle']],
				'classes'=>'arf_show_full_screen_popup',
				
			]
		);
		$this->add_control(
			'arf_click_animation_effect',
			[
				'label' => esc_html__( 'Animation Effect :','ARForms'),
				'type' => Controls_Manager::SELECT,
				'default' => 'no_animation',
				'options' => [
							"no_animation"=>"No Animation",
							"fade_in"=>"Fade In",
							"slide_in_top"=>"Slide in Top",
							"slide_in_bottom"=>"Slide In Bottom",
							"slide_in_right"=>"Slide In Right",
							"slide_in_left"=>"Slide In Left",
							"zoom_in"=>"Zoom In",

                        ],
				'label_block' => true,
				'condition'=>['arf_frm_type' => 'ARForms_popup','arf_model_trigger_type'=>'onclick','arf_click_type'=>['link','button']],
				
			]
		);
		$this->add_control(
			'arf_click_show_full_screen_popup',
			[
				'label' => esc_html__('Show Full Screen Popup :','ARForms'),
				'type' => Controls_Manager::CHOOSE,
				'default' =>'no',
				'options' => [
					'yes' => [
						'title' => esc_html__( 'Yes', 'ARForms' ),
					],
					'no' => [
						'title' => esc_html__( 'No', 'ARForms' ),
					],
				],
				'condition'=>['arf_frm_type' => 'ARForms_popup','arf_model_trigger_type'=>'onclick','arf_click_type'=>['link','button']],
				'classes'=>'arf_show_full_screen_popup',
				
			]
		);
		$this->end_controls_section();

	}

	
	protected function render() {
		$settings = $this->get_settings_for_display();

		echo '<h5 class="title">';
		echo $settings['title'];
		echo '</h5>';
		echo '<div class="arf_select">';
			$arf_shortcode='';
			if(isset($settings['arf_select']) && $settings['arf_select']=="Please select a valid form"){
				echo $settings['arf_select'];	
			}else if(isset($settings['arf_frm_type']) && $settings['arf_frm_type']=="ARForms_popup"){
				if (isset($settings['arf_model_trigger_type']) && $settings['arf_model_trigger_type'] == 'onclick' && isset($settings['arf_click_type']) && $settings['arf_click_type'] == 'sticky') {
	               echo '[ARForms_popup '.$settings['arf_select'].' desc="'.$settings['arf_popup_label'].'" type="'.$settings['arf_click_type'].'" position="'.$settings['arf_link_position'].'" height="'.$settings['arf_height'].'" width="'.$settings['arf_width'].'" bgcolor="'.$settings['arf_btn_back_color'].'" txtcolor="'.$settings['arf_btn_text_color'].'"]';
	            } 
	            if (isset($settings['arf_model_trigger_type']) && $settings['arf_model_trigger_type'] == 'onclick' && isset($settings['arf_click_type']) && $settings['arf_click_type'] == 'fly'){
	                echo '[ARForms_popup '.$settings['arf_select'].' desc="'.$settings['arf_popup_label'].'" type="'.$settings['arf_click_type'].'" position="'.$settings['arf_fly_link_position'].'" width="'.$settings['arf_width'].'" angle="'.$settings['arf_btn_angle'].'" bgcolor="'.$settings['arf_btn_back_color'].'" txtcolor="'.$settings['arf_btn_text_color'].'"]';
	            }
	            if (isset($settings['arf_model_trigger_type']) && $settings['arf_model_trigger_type'] == 'onclick' && isset($settings['arf_click_type']) && $settings['arf_click_type'] == 'button') {
	                echo '[ARForms_popup '.$settings['arf_select'].' desc="'.$settings['arf_popup_label'].'" type="'.$settings['arf_click_type'].'" width="'.$settings['arf_width'].'" modaleffect="'.$settings['arf_click_animation_effect'].'" is_fullscreen="'.$settings['arf_click_show_full_screen_popup'].'" overlay="'.$settings['arf_click_back_overlay'].'" is_close_link="'.$settings['arf_click_show_cl_btn'].'" bgcolor="'.$settings['arf_btn_back_color'].'" txtcolor="'.$settings['arf_btn_text_color'].'" modal_bgcolor="'.$settings['arf_click_back_color'].'"]';
	            } 
	            if (isset($settings['arf_model_trigger_type']) && $settings['arf_model_trigger_type'] == 'onload') {
	                echo '[ARForms_popup '.$settings['arf_select'].' type="'.$settings['arf_model_trigger_type'].'" width="'.$settings['arf_width'].'" modaleffect="'.$settings['arf_animation_effect'].'" is_fullscreen="'.$settings['arf_show_full_screen_popup'].'" overlay="'.$settings['arf_back_overlay'].'" is_close_link="'.$settings['arf_show_cl_btn'].'" modal_bgcolor="'.$settings['arf_back_color'].'" ]';

	            }
	            if (isset($settings['arf_model_trigger_type']) && $settings['arf_model_trigger_type'] == 'scroll') {
	                echo '[ARForms_popup '.$settings['arf_select'].' type="'.$settings['arf_model_trigger_type'].'" width="'.$settings['arf_width'].'" modaleffect="'.$settings['arf_animation_effect'].'" is_fullscreen="'.$settings['arf_show_full_screen_popup'].'" on_scroll="'.$settings['arf_popup_on_scoll_position'].'" overlay="'.$settings['arf_back_overlay'].'" is_close_link="'.$settings['arf_show_cl_btn'].'" modal_bgcolor="'.$settings['arf_back_color'].'"]';

	            } 
	            if (isset($settings['arf_model_trigger_type']) && $settings['arf_model_trigger_type'] == 'timer') {
	                echo '[ARForms_popup '.$settings['arf_select'].' on_delay="'.$settings['arf_popup_after_page_load'].'" type="'.$settings['arf_model_trigger_type'].'" width="'.$settings['arf_width'].'" modaleffect="'.$settings['arf_animation_effect'].'" is_fullscreen="'.$settings['arf_show_full_screen_popup'].'" overlay="'.$settings['arf_back_overlay'].'" is_close_link="'.$settings['arf_show_cl_btn'].'" modal_bgcolor="'.$settings['arf_back_color'].'"]';

	            }
	            if (isset($settings['arf_model_trigger_type']) && $settings['arf_model_trigger_type'] == 'on_exit') {
	                
	                echo '[ARForms_popup '.$settings['arf_select'].' type="'.$settings['arf_model_trigger_type'].'" width="'.$settings['arf_width'].'" modaleffect="'.$settings['arf_animation_effect'].'" is_fullscreen="'.$settings['arf_show_full_screen_popup'].'" is_close_link="'.$settings['arf_show_cl_btn'].'" modal_bgcolor="'.$settings['arf_back_color'].'"]';

	            }
	            if(isset($settings['arf_model_trigger_type']) && $settings['arf_model_trigger_type'] == 'on_idle'){

	               echo '[ARForms_popup '.$settings['arf_select'].' type="'.$settings['arf_model_trigger_type'].'" width="'.$settings['arf_width'].'" modaleffect="'.$settings['arf_animation_effect'].'" is_fullscreen="'.$settings['arf_show_full_screen_popup'].'" inactive_min="'.$settings['arf_popup_after_user_inactive'].'" overlay="'.$settings['arf_back_overlay'].'" is_close_link="'.$settings['arf_show_cl_btn'].'" modal_bgcolor="'.$settings['arf_back_color'].'"]';

	            }
	            if(isset($settings['arf_model_trigger_type']) && $settings['arf_model_trigger_type'] == 'onclick' && isset($settings['arf_click_type']) && $settings['arf_click_type']=='link'){
					echo '[ARForms_popup '.$settings['arf_select'].' desc="'.$settings['arf_popup_label'].'" type="'.$settings['arf_click_type'].'" width="'.$settings['arf_width'].'" modaleffect="'.$settings['arf_click_animation_effect'].'"  is_fullscreen="'.$settings['arf_click_show_full_screen_popup'].'" overlay="'.$settings['arf_click_back_overlay'].'" is_close_link="'.$settings['arf_click_show_cl_btn'].'" modal_bgcolor="'.$settings['arf_click_back_color'].'"]';
				}		
			}else{
				echo '[ARForms '.$settings['arf_select'].']';
			}
		echo '</div>';
		
	}

	
	protected function _content_template() {
		?>
		<h5 class="title">
			{{{ settings.title }}}
		</h5>
		<div class="arf_select">
			<# if ( settings.arf_select=='Please select a valid form' ) { #>
					{{{ settings.arf_select }}}
			<# }else if(settings.arf_frm_type=='ARForms_popup'){ #>
					<# if (settings.arf_model_trigger_type=='onclick' && settings.arf_click_type=='sticky') { #>
					   [ARForms_popup {{{ settings.arf_select}}} desc="{{{ settings.arf_popup_label}}}" type="{{{ settings.arf_click_type}}}" position="{{{ settings.arf_link_position}}}" height="{{{ settings.arf_height}}}" width="{{{ settings.arf_width}}}" bgcolor="{{{ settings.arf_btn_back_color}}}" txtcolor="{{{ settings.arf_btn_text_color}}}"]
					<# } #>
					<# if(settings.arf_model_trigger_type=='onclick' && settings.arf_click_type=='fly'){ #>
					    [ARForms_popup {{{ settings.arf_select}}} desc="{{{ settings.arf_popup_label}}}" type="{{{ settings.arf_click_type}}}" position="{{{ settings.arf_fly_link_position}}}" height="{{{ settings.arf_height}}}" width="{{{ settings.arf_width}}}" angle="{{{settings.arf_btn_angle}}}" bgcolor="{{{ settings.arf_btn_back_color}}}" txtcolor="{{{ settings.arf_btn_text_color}}}"]
					<# } #>
					<# if(settings.arf_model_trigger_type=='onclick' && settings.arf_click_type=='button'){ #>
					    [ARForms_popup {{{ settings.arf_select}}} desc="{{{ settings.arf_popup_label}}}" type="{{{ settings.arf_click_type}}}" width="{{{ settings.arf_width}}}" modaleffect="{{{ settings.arf_click_animation_effect}}}" is_fullscreen="{{{ settings.arf_click_show_full_screen_popup}}}" overlay="{{{ settings.arf_back_overlay}}}" is_close_link="{{{ settings.arf_click_show_cl_btn}}}" bgcolor="{{{ settings.arf_btn_back_color}}}" txtcolor="{{{ settings.arf_btn_text_color}}}" modal_bgcolor="{{{ settings.arf_back_color}}}"]
					<# } #>
					<# if(settings.arf_model_trigger_type=='onload'){ #>    
					    [ARForms_popup {{{ settings.arf_select}}} type="{{{ settings.arf_model_trigger_type}}}" width="{{{ settings.arf_width}}}" modaleffect="{{{ settings.arf_animation_effect}}}" is_fullscreen="{{{ settings.arf_show_full_screen_popup}}}" overlay="{{{ settings.arf_back_overlay}}}" is_close_link="{{{ settings.arf_show_cl_btn}}}" modal_bgcolor="{{{ settings.arf_back_color}}}" ]
					<# } #>
					<# if(settings.arf_model_trigger_type=='scroll'){ #>    
					    [ARForms_popup {{{ settings.arf_select}}} type="{{{ settings.arf_model_trigger_type}}}" width="{{{ settings.arf_width}}}" modaleffect="{{{ settings.arf_animation_effect}}}" is_fullscreen="{{{ settings.arf_show_full_screen_popup}}}" on_scroll="{{{ settings.arf_popup_on_scoll_position}}}" overlay="{{{ settings.arf_back_overlay}}}" is_close_link="{{{ settings.arf_show_cl_btn}}}" modal_bgcolor="{{{ settings.arf_back_color}}}"]
					<# } #>
					<# if(settings.arf_model_trigger_type=='timer'){ #>    
					    [ARForms_popup {{{ settings.arf_select}}} on_delay="{{{settings.arf_popup_after_page_load}}}" type="{{{ settings.arf_model_trigger_type}}}" width="{{{ settings.arf_width}}}" modaleffect="{{{ settings.arf_animation_effect}}}" is_fullscreen="{{{ settings.arf_show_full_screen_popup}}}" overlay="{{{ settings.arf_back_overlay}}}" is_close_link="{{{ settings.arf_show_cl_btn}}}"  modal_bgcolor="{{{ settings.arf_back_color}}}"]
					<# } #>
					<# if(settings.arf_model_trigger_type=='on_exit'){ #>    
					    [ARForms_popup {{{ settings.arf_select}}} type="{{{ settings.arf_model_trigger_type}}}" width="{{{ settings.arf_width}}}" modaleffect="{{{ settings.arf_animation_effect}}}" is_fullscreen="{{{ settings.arf_show_full_screen_popup}}}" is_close_link="{{{ settings.arf_show_cl_btn}}}" modal_bgcolor="{{{ settings.arf_back_color}}}"]
					<# } #>
					<# if(settings.arf_model_trigger_type=='on_idle'){ #>    
					    [ARForms_popup {{{ settings.arf_select}}} type="{{{ settings.arf_model_trigger_type}}}" width="{{{ settings.arf_width}}}" modaleffect="{{{ settings.arf_animation_effect}}}" is_fullscreen="{{{ settings.arf_show_full_screen_popup}}}" inactive_min="{{{settings.arf_popup_after_user_inactive}}}" overlay="{{{ settings.arf_back_overlay}}}" is_close_link="{{{ settings.arf_show_cl_btn}}}"  modal_bgcolor="{{{ settings.arf_back_color}}}"]
					<# } #>
					<# if(settings.arf_model_trigger_type=='onclick' && settings.arf_click_type=='link'){ #>
						[ARForms_popup {{{ settings.arf_select}}} desc="{{{ settings.arf_popup_label}}}" type="{{{ settings.arf_click_type}}}" width="{{{ settings.arf_width}}}" modaleffect="{{{ settings.arf_click_animation_effect}}}"  is_fullscreen="{{{ settings.arf_click_show_full_screen_popup}}}" overlay="{{{ settings.arf_back_overlay}}}" is_close_link="{{{ settings.arf_click_show_cl_btn}}}" modal_bgcolor="{{{ settings.arf_back_color}}}"]
					<# } #>
			<# }else{ #>
					[ARForms {{{ settings.arf_select }}}]
			<# } #>
		</div>
		<?php
	}
}