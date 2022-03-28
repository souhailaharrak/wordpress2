<?php

namespace GT3\ThemesCore;

use GT3\ThemesCore\Assets\Script;
use GT3\ThemesCore\Assets\Style;
use GT3\ThemesCore\Customizer\Controls\Button;
use GT3\ThemesCore\Customizer\Defaults_Trait;
use GT3\ThemesCore\Customizer\Convert_Trait;
use GT3\ThemesCore\Customizer\Controls\Color;
use GT3\ThemesCore\Customizer\Controls\Sidebar_Generator;
use GT3\ThemesCore\Customizer\Controls\Toggle;
use GT3\ThemesCore\Customizer\Controls\Radio_Image;
use GT3\ThemesCore\Customizer\Elementor;
use WP_Customize_Manager;

use WP_Customize_Background_Position_Control;
use WP_Customize_Code_Editor_Control;
use WP_Customize_Color_Control;
use WP_Customize_Date_Time_Control;
use WP_Customize_Media_Control;
use WP_Customize_Nav_Menu_Auto_Add_Control;
use WP_Customize_Nav_Menu_Control;
use WP_Customize_Nav_Menu_Item_Control;
use WP_Customize_Nav_Menu_Location_Control;
use WP_Customize_Nav_Menu_Locations_Control;
use WP_Customize_Nav_Menu_Name_Control;
use WP_Widget_Area_Customize_Control;
use WP_Widget_Form_Customize_Control;
use WP_Customize_Theme_Control;

use WP_Customize_Control;

class Customizer {
	use Defaults_Trait;
	use Convert_Trait;

	private static $instance = null;

	private static $panels   = array();
	private static $sections = array();
	private static $fields   = array();

	const SIDEBAR_GENERATOR   = Sidebar_Generator::class;
	const Toggle_Control      = Toggle::class;
	const Color_Control       = Color::class;
	const Button_Control      = Button::class;
	const Radio_Image_Control = Radio_Image::class;

	const Background_Position_Control      = WP_Customize_Background_Position_Control::class;
	const Code_Editor_Control              = WP_Customize_Code_Editor_Control::class;
	const Date_Time_Control                = WP_Customize_Date_Time_Control::class;
	const Media_Control                    = WP_Customize_Media_Control::class;
	const Nav_Menu_Auto_Add_Control        = WP_Customize_Nav_Menu_Auto_Add_Control::class;
	const Nav_Menu_Control                 = WP_Customize_Nav_Menu_Control::class;
	const Nav_Menu_Item_Control            = WP_Customize_Nav_Menu_Item_Control::class;
	const Nav_Menu_Location_Control        = WP_Customize_Nav_Menu_Location_Control::class;
	const Nav_Menu_Locations_Control       = WP_Customize_Nav_Menu_Locations_Control::class;
	const Nav_Menu_Name_Control            = WP_Customize_Nav_Menu_Name_Control::class;
	const WP_Widget_Area_Customize_Control = WP_Widget_Area_Customize_Control::class;
	const WP_Widget_Form_Customize_Control = WP_Widget_Form_Customize_Control::class;
	const WP_Customize_Theme_Control       = WP_Customize_Theme_Control::class;

	const Checkbox_Control       = 'checkbox';
	const Radio_Control          = 'radio';
	const Select_Control         = 'select';
	const Textarea_Control       = 'textarea';
	const Dropdown_Pages_Control = 'dropdown-pages';
	const Range_Control          = 'range';
	const Number_Control         = 'number';

	private static $current_panel   = '';
	private static $current_section = '';

	private $conditions = array();

	private static $option_name = '';
	private static $options     = array();
	private static $theme       = '';

	/** @return self */
	public static function instance(){
		if(is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	public function filter_child_options($options) {
		$parent_options = get_option("theme_mods_".self::$theme);
		if (key_exists(self::$option_name, $parent_options)) {
			if (key_exists(self::$option_name, $options) && is_array($options[self::$option_name])) {
				$parent_options[self::$option_name] = array_merge($parent_options[self::$option_name], $options[self::$option_name]);
			}

			$options[self::$option_name] = $parent_options[self::$option_name];
		}

		return $options;
	}

	public function save_child_options($value, $old_value, $option) {
		if (key_exists(self::$option_name, $value)) {

			$parent_options = get_option("theme_mods_".self::$theme);
			$options = key_exists(self::$option_name, $parent_options) ? $parent_options[self::$option_name] : array();
			$options = array_merge($options, $value[self::$option_name]);
			$parent_options[self::$option_name] = $options;
			update_option("theme_mods_".self::$theme,$parent_options );

			unset($value[self::$option_name]);
		}

		return $value;
	}
	private function __construct(){
		self::$theme = DashBoard::instance()->get_theme();
		self::$option_name = self::$theme.'_options';
		self::$options     = apply_filters('gt3/core/customizer/defaults', $this->defaults);

		add_filter("option_theme_mods_".self::$theme."-child", array($this,'filter_child_options'));
		add_filter( "pre_update_option_theme_mods_".self::$theme."-child", array($this, 'save_child_options'),10,3  );

		$this->get_options();

		add_action('customize_register', array( $this, 'customize_register' ));
		add_action('customize_controls_print_scripts', array( $this, 'print_conditions' ));

		add_action('wp_footer', array( $this, 'wp_footer' ));
		add_action(
			'wp_enqueue_scripts',
			function(){
				Style::enqueue_core_asset('customizer');
			}
		);


	}


	protected function json_decode($value){
		$decoded = wp_unslash($value);
		$error   = false;
		if($decoded !== $value) {
			$_value = json_decode($decoded, true);
			if(!json_last_error()) {
				$value = $_value;
			} else {
				$error = true;
			}
		}
		if($error) {
			$_value = json_decode($value, true);
			if(!json_last_error()) {
				$value = $_value;
			}
		}

		return $value;
	}

	public function get_options($option = false){
		static $loaded = false;
		if(false === $loaded) {
			$loaded  = true;
			$options = get_theme_mod(self::$option_name);

			if (current_user_can('manage_options') && key_exists('gt3-convert-theme-options', $_REQUEST)) {
				$options = $this->convert();
			}

			if(false === $options) {
				$options = $this->convert();
				set_theme_mod(self::$option_name, $options);
			}
			if(!is_array($options)) {
				$options = array();
			}
			self::$options = array_merge(self::$options, $options);
			if(is_customize_preview()) {
				$_customized = $this->json_decode($_REQUEST['customized']);
				$customized  = array();
				foreach($_customized as $key => $value) {
					if(strpos($key, self::$option_name) === 0) {
						$opt     = self::$option_name;
						$matches = preg_match("#{$opt}\[(\w+)\]#i", $key, $match);
						if($matches) {
							$customized[$match[1]] = $value;
						}
					}
				}
				self::$options = array_merge(self::$options, $customized);
			}

			foreach(self::$options as $key => $value) {
				$decoded = $value;
				if(gettype($value) === 'string') {
					$decoded = urldecode($value);
				}
				$error      = false;
				$_new_value = $value;
				if($decoded !== $value) {

					if($this->is_json($decoded)) {
						$_value = json_decode($decoded, true);
						if(json_last_error() === JSON_ERROR_NONE) {
							$_new_value = $_value;
						} else {
							$error = true;
						}
					}
				}
				if($error) {
					if($this->is_json($decoded)) {
						$_value = json_decode($value, true);
						if(json_last_error() === JSON_ERROR_NONE) {
							$_new_value = $_value;
						}
					}
				}

				if($_new_value !== $value) {
					self::$options[$key] = $_new_value;
				}
			}
		}

		if(false !== $option) {
			return $this->get_option($option);
		}

		return self::$options;
	}

	protected function is_json($json){
		$result = json_decode($json);

		return (json_last_error() === JSON_ERROR_NONE);
	}

	public function get_option($option, $subkey = null){
		if(key_exists($option, self::$options)) {
			return self::$options[$option];
		}
		$value = null;
		if ($subkey !== null) {
			$value = Elementor::get_repeater_setting($option, $subkey);
		} else {
			$value = Elementor::instance()->get_setting($option);
		}

		return $value;
	}

	public function get_defaults($option = false){
		if(false !== $option) {
			return $this->get_default($option);
		}

		return self::$options;
	}

	public function get_default($option){
		if(key_exists($option, self::$options)) {
			return self::$options[$option];
		}

		return null;
	}


	public function filter_options($value){
		if(!is_array($value)) {
			return $value;
		}
		$value = array_merge(self::$options, $value);

		return json_encode($value);
	}

	public function set_options($value){
		if(!is_array($value)) {
			return false;
		}

		self::$options = array_merge(self::$options, $value);

		return set_theme_mod(self::$option_name, $value);
	}

	public function customize_register(WP_Customize_Manager $wp_customize){
		$wp_customize->register_control_type(Sidebar_Generator::class);
		$wp_customize->register_control_type(Toggle::class);
		$wp_customize->register_control_type(Color::class);
		$wp_customize->register_control_type(Radio_Image::class);
		$wp_customize->register_control_type(Button::class);

		foreach(self::$panels as $id => $args) {
			$wp_customize->add_panel($id, $args);
		}
		foreach(self::$sections as $id => $args) {
			$wp_customize->add_section($id, $args);
		}
		foreach(self::$fields as $id => $args) {
			$name = $id;
			$this->add_condition($id, $args);
			$wp_customize->add_setting($id, $args['settings_args']);

			$type = $args['type'];
			if(class_exists($type)) {
				unset($args['type']);
				$name = new $type($wp_customize, $id, $args);
				$args = array();
			}
//			if ($name instanceof WP_Customize_Control) {
//				if (property_exists($name, ''))
//			}
			$wp_customize->add_control($name, $args);
		}
	}

	public function add_condition($id, $args){
		if(key_exists('conditions', $args)) {
			foreach($args['conditions'] as $key => &$condition) {
				if(key_exists('field', $condition)) {
					$condition['field'] = self::$option_name.'['.$condition['field'].']';
				}
			}
			$this->conditions[$id] = $args['conditions'];
		}
	}

	public function print_conditions(){
		Script::enqueue_core_asset('customizer');
		Style::enqueue_core_asset('customizer');
		$options = array(
			'conditions'  => $this->conditions,
			'option_name' => self::$option_name,
		);
		wp_localize_script(Script::CORE.'/customizer', 'gt3_customizer', $options);
	}

	/**
	 * @param string         $id              A specific ID for the panel.
	 * @param array          $args            {
	 *                                        Optional. Array of properties for the new Panel object. Default empty array.
	 *
	 * @type int             $priority        Priority of the panel, defining the display order
	 *                                            of panels and sections. Default 160.
	 * @type string          $capability      Capability required for the panel.
	 *                                            Default `edit_theme_options`.
	 * @type string|string[] $theme_supports  Theme features required to support the panel.
	 * @type string          $title           Title of the panel to show in UI.
	 * @type string          $description     Description to show in the UI.
	 * @type string          $type            Type of the panel.
	 * @type callable        $active_callback Active callback.
	 * }
	 */
	public static function add_panel($id, $args, $save = true){
		self::instance();
		self::$panels[$id] = $args;
		if($save) {
			self::$current_panel = $id;
		}

		return $id;
	}

	/**
	 * @param string         $id                 A specific ID of the section.
	 * @param array          $args               {
	 *                                           Optional. Array of properties for the new Section object. Default empty array.
	 *
	 * @type int             $priority           Priority of the section, defining the display order
	 *                                               of panels and sections. Default 160.
	 * @type string          $panel              The panel this section belongs to (if any).
	 *                                               Default empty.
	 * @type string          $capability         Capability required for the section.
	 *                                               Default 'edit_theme_options'
	 * @type string|string[] $theme_supports     Theme features required to support the section.
	 * @type string          $title              Title of the section to show in UI.
	 * @type string          $description        Description to show in the UI.
	 * @type string          $type               Type of the section.
	 * @type callable        $active_callback    Active callback.
	 * @type bool            $description_hidden Hide the description behind a help icon,
	 *                                               instead of inline above the first control.
	 *                                               Default false.
	 * }
	 */
	public static function add_section($id, $args, $save = true){
		self::instance();
		if(!key_exists('panel', $args)) {
			$args['panel'] = self::$current_panel;
		}
		self::$sections[$id] = $args;

		if($save) {
			self::$current_section = $id;
		}

		return $id;
	}

	public static function set_section($id){
		self::instance();
		self::$current_section = $id;

		return $id;
	}

	public static function set_panel($id){
		self::instance();
		self::$current_panel = $id;

		return $id;
	}


	/**
	 * @param string              $id                   Control ID.
	 * @param array               $args                 {
	 *                                                  Optional. Array of properties for the new Control object. Default empty array.
	 *
	 * @type int                  $instance_number      Order in which this instance was created in relation
	 *                                                 to other instances.
	 * @type WP_Customize_Manager $manager              Customizer bootstrap instance.
	 * @type string               $id                   Control ID.
	 * @type array                $settings             All settings tied to the control. If undefined, `$id` will
	 *                                                 be used.
	 * @type string               $setting              The primary setting for the control (if there is one).
	 *                                                 Default 'default'.
	 * @type string               $capability           Capability required to use this control. Normally this is empty
	 *                                                 and the capability is derived from `$settings`.
	 * @type int                  $priority             Order priority to load the control. Default 10.
	 * @type string               $section              Section the control belongs to. Default empty.
	 * @type string               $label                Label for the control. Default empty.
	 * @type string               $description          Description for the control. Default empty.
	 * @type array                $choices              List of choices for 'radio' or 'select' type controls, where
	 *                                                 values are the keys, and labels are the values.
	 *                                                 Default empty array.
	 * @type array                $conditions           Conditions
	 * @type array                $input_attrs          List of custom input attributes for control output, where
	 *                                                 attribute names are the keys and values are the values. Not
	 *                                                 used for 'checkbox', 'radio', 'select', 'textarea', or
	 *                                                 'dropdown-pages' control types. Default empty array.
	 * @type bool                 $allow_addition       Show UI for adding new content, currently only used for the
	 *                                                 dropdown-pages control. Default false.
	 * @type array                $json                 Deprecated. Use WP_Customize_Control::json() instead.
	 * @type string               $type                 Control type. Core controls include 'text', 'checkbox',
	 *                                                 'textarea', 'radio', 'select', and 'dropdown-pages'. Additional
	 *                                                 input types such as 'email', 'url', 'number', 'hidden', and
	 *                                                 'date' are supported implicitly. Default 'text'.
	 * @type callable             $active_callback      Active callback.
	 *
	 * @type array                $settings_args        {
	 *     Optional. Array of properties for the new Setting object. Default empty array.
	 *
	 * @type string               $type                 Type of the setting. Default 'theme_mod'.
	 * @type string               $capability           Capability required for the setting. Default 'edit_theme_options'
	 * @type string|string[]      $theme_supports       Theme features required to support the panel. Default is none.
	 * @type string               $default              Default value for the setting. Default is empty string.
	 * @type string               $transport            Options for rendering the live preview of changes in Customizer.
	 *                                                 Using 'refresh' makes the change visible by reloading the whole preview.
	 *                                                 Using 'postMessage' allows a custom JavaScript to handle live changes.
	 *                                                 Default is 'refresh'.
	 * @type callable             $validate_callback    Server-side validation callback for the setting's value.
	 * @type callable             $sanitize_callback    Callback to filter a Customize setting value in un-slashed form.
	 * @type callable             $sanitize_js_callback Callback to convert a Customize PHP setting value to a value that is
	 *                                                 JSON serializable.
	 * @type bool                 $dirty                Whether or not the setting is initially dirty when created.
	 * }
	 * }
	 */
	public static function add_field($id, $args){
		$self = self::instance();
		$name = self::$option_name.'['.$id.']';
		if(!key_exists('section', $args)) {
			$args['section'] = self::$current_section;
		}
		$args['settings_args'] = array_merge(
			array(
				'type'      => 'theme_mod',
				'transport' => 'postMessage',
				'default'   => $self->get_default($id),
			), key_exists('settings_args', $args) && is_array($args['settings_args']) ? $args['settings_args'] : array(),
		);

		self::$fields[$name] = $args;

		return $name;
	}

	public function wp_footer(){
		if(!is_customize_preview()) {
			return;
		}
		?>
		<div class="gt3-customizer-loading-wrapper">
			<svg viewBox="0 0 100 100" version="1.1" enable-background="new 0 0 100 100"
			     xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
			     x="0px" y="0px" width="100" height="100"
			     xml:space="preserve"
			>
                <rect class="item-1" x="0" y="0" width="50" height="50" />
				<rect class="item-2" x="50" y="0" width="50" height="50" />
				<rect class="item-3" x="0" y="50" width="50" height="50" />
				<rect class="item-4" x="50" y="50" width="50" height="50" />
			</svg>
		</div>
		<?php
	}
}
