<?php

namespace WPDaddy\Builder\Library;
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

use Elementor\Controls_Manager;
use Elementor\Core\Base\Document;
use Elementor\Core\DocumentTypes\Post;
use Elementor\Modules\Library\Documents\Library_Document;
use Elementor\Plugin;
use Elementor\Utils;
use Elementor\Plugin as Elementor_Plugin;
use WP_REST_Request;
use WP_REST_Server;
use WPDaddy\Builder\Assets;
use WPDaddy\Builder\Elementor;
use WPDaddy\Builder\Settings;

class Menu extends Basic {

	const post_type = 'elementor_library';
	public static $name = 'wpda-menu';

	const REST_NAMESPACE = 'wpda-builder/v2/nav-menu';
	const permission = 'manage_options';


	public function __construct(array $data = []) {
		if ($data) {
			$template = get_post_meta($data['post_id'], '_wp_page_template', true);

			if (empty($template)) {
				$template = 'default';
			}

			$data['settings']['template'] = $template;
		}

		add_action('elementor/editor/before_enqueue_scripts', array($this, 'editor_assets'));
		add_action('elementor/preview/enqueue_scripts', array($this, 'preview_assets'));

		add_action('elementor/frontend/after_enqueue_styles', function() {
			Assets::enqueue_style('wpda-elementor-core-frontend', 'frontend/frontend.css');
		});
		parent::__construct($data);

	}

	public function editor_assets() {
		$elementor = Elementor_Plugin::instance();

		$document = Elementor_Plugin::instance()->documents->get_current();

		if ($document instanceof self) {
			Assets::enqueue_script('wpda-builder/editor', 'admin/library-mega-menu.js');
		}
	}

	public function preview_assets() {
		global $post;

		$_elementor_template_type = get_metadata('post', $post->ID, '_elementor_template_type', true);

		if ($post->post_type === 'elementor_library' && $_elementor_template_type === self::$name) {
			Assets::enqueue_style('wpda-builder/editor', 'admin/library-mega-menu-preview.css');
			Assets::enqueue_script('wpda-builder/editor', 'admin/library-mega-menu-preview.js');
		}
	}


	public static function manage_posts_columns__DISABLED($column) {
		return array(
			'cb'         => '<input type="checkbox" />',
			'title'      => esc_html__('Title', 'wpda-builder'),
			'status'     => esc_html__('Status', 'wpda-builder'),
			'conditions' => esc_html__('Conditions', 'wpda-builder'),
			'date'       => esc_html__('Date', 'wpda-builder'),
		);
	}

	public static function manage_posts_custom_column__DISABLED($column, $post_id) {
		$this_url = $_SERVER['REQUEST_URI'];

		switch ($column) {
			case 'status':
				echo '<span
				class="active-status"
				data-status="'.(!!get_post_meta($post_id, '_wpda-builder-active', true) ? 'true' : 'false').'"
				data-active='.__('Active', 'wpda-builder').'
				data-inactive='.__('Inactive', 'wpda-builder').'>
			</span>';
				break;
			case 'conditions':
				$conditionsArray = array(
					'none'         => __('None', 'wpda-builder'),
					'all'          => __('All Pages', 'wpda-builder'),
					'is_single'    => __('Posts', 'wpda-builder'),
					'is_page'      => __('Pages', 'wpda-builder'),
					'is_singular'  => __('Singular', 'wpda-builder'),
					'is_search'    => __('Search', 'wpda-builder'),
					'is_404'       => __('404', 'wpda-builder'),
					'is_archive'   => __('Archive', 'wpda-builder'),
					'is_home'      => __('Homepage', 'wpda-builder'),
					'is_shop_wpda' => __('Shop', 'wpda-builder'),
				);

				$conditions          = get_post_meta($post_id, '_wpda-builder-conditions', true);
				$defaults_conditions = array(
					array(
						'type'  => 'include',
						'key'   => 'none',
						'value' => [],
					)
				);
				try {
					$conditions = json_decode($conditions, true);
					if (json_last_error() || !is_array($conditions)) {
						$conditions = $defaults_conditions;
					}
				} catch (\Exception $ex) {
					$conditions = $defaults_conditions;
				}

				echo join(
					',', array_map(
						function($cond) use ($conditionsArray) {
							return $conditionsArray[$cond['key']];
						}, $conditions
					)
				);
				break;
			default:
				break;
		}
	}

	public function filter_admin_row_actions($actions) {
		if ($this->is_built_with_elementor() && $this->is_editable_by_current_user()) {
			$actions['edit_with_elementor'] = sprintf(
				'<a href="%1$s">%2$s</a>',
				$this->get_edit_url(),
				__('Edit Menu', 'wpda-builder')
			);

			unset($actions['edit_vc']);
		}

		return $actions;
	}

	public static function static_get_edit_url($post_id) {
		$url = add_query_arg(
			[
				'post'   => $post_id,
				'action' => 'elementor',
			],
			admin_url('post.php')
		);

		return $url;
	}

	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['support_wp_page_templates'] = false;
		$properties['admin_tab_group']           = 'library';
		$properties['show_in_library']           = true;
		$properties['register_type']             = false;

		return $properties;
	}

	/**
	 * @access public
	 */
	public function get_name() {
		return self::$name;
	}

	protected static function get_editor_panel_categories() {
		return Utils::array_inject(
			parent::get_editor_panel_categories(),
			'theme-elements',
			[
				'theme-elements-single' => [
					'title'  => __('Single', 'wpda-builder'),
					'active' => false,
				],
			]
		);
	}

	public function get_css_wrapper_selector() {
		return '#wpda-mega-menu-'.$this->get_main_id();
	}

	protected function register_controls() {
		parent::register_controls();

		$this->start_controls_section(
			'wpda_settings',
			[
				'label' => __('WPDaddy Settings', 'wpda-builder'),
				'tab'   => Elementor::TAB_WPDA_SETTINGS,
			]
		);

		$this->add_control(
			'type',
			[
				'label'   => __('Menu Type', 'plugin-domain'),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'boxed'     => [
						'title' => __('Boxed', 'plugin-domain'),
						'icon'  => 'eicon-lightbox',
					],
					'fullwidth' => [
						'title' => __('Full Width', 'plugin-domain'),
						'icon'  => 'eicon-lightbox-expand',
					],
				],
				'default' => 'boxed',
				'toggle'  => false,
			]
		);

		$this->add_control(
			'boxed_position',
			[
				'label'     => __('Menu Alignmant', 'plugin-domain'),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [
						'title' => __('Left', 'plugin-domain'),
						'icon'  => 'eicon-text-align-right',
					],
					'center' => [
						'title' => __('Center', 'plugin-domain'),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => __('Right', 'plugin-domain'),
						'icon'  => 'eicon-text-align-left',
					],
				],
				'boxed'     => 'center',
				'toggle'    => false,
				'condition' => array(
					'type' => 'boxed',
				),
			]
		);

		$this->add_control(
			'min_width',
			array(
				'label'     => __('Min. Width', 'wpda-builder'),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 640,
				'min'       => 300,
				'selectors' => array(
					'{{WRAPPER}}' => '--w: {{VALUE}}px;',
				),
				'condition' => array(
					'type' => 'boxed',
				),
			)
		);

		$this->end_controls_section();

		Post::register_style_controls($this);
	}

	/**
	 * @access public
	 * @static
	 */
	public static function get_title() {
		return __('WPDaddy Mega Menu', 'wpda-builder');
	}

	protected function get_remote_library_config() {
		$config = parent::get_remote_library_config();

		$config['category'] = '';
		$config['type']     = self::$name;

		return $config;
	}

	public function get_initial_config() {
		$config = parent::get_initial_config();

		return $config;
	}

	public static function load_canvas_template($single_template) {
		global $post;
		$_elementor_template_type = get_metadata('post', $post->ID, '_elementor_template_type', true);

		if ($post->post_type === 'elementor_library' && $_elementor_template_type === self::$name) {
			$single_template = __DIR__.'/template.php';
		}

		return $single_template;
	}

	public function get_container_attributes() {
		$attributes = parent::get_container_attributes();

		$attributes['id'] = 'wpda-mega-menu-'.$this->get_main_id();
		$attributes['class'] .= ' sub-menu wpda-menu wpda-mega-menu';
		$attributes['class'] .= ' wpda-builder-page-'.$this->get_main_id();
		$attributes['class'] .= ' wpda-mega-menu-'.$this->get_main_id();
		$attributes['class'] .= ' wpda-builder '.$this->get_name().'-builder';

		$attributes['data-menu-type'] = esc_attr($this->get_settings('type'));

		if ($this->get_settings('type') === 'boxed') {
			$attributes['data-menu-boxed-align'] = esc_attr($this->get_settings('boxed_position'));
		}

		return $attributes;
	}

	public function print_elements_with_wrapper( $elements_data = null ) {
		if ( ! $elements_data ) {
			$elements_data = $this->get_elements_data();
		}

		$is_dom_optimization_active = Plugin::$instance->experiments->is_feature_active( 'e_dom_optimization' );
		?>
		<ul <?php Utils::print_html_attributes( $this->get_container_attributes() ); ?>>
			<?php if ( ! $is_dom_optimization_active ) { ?>
			<div class="elementor-inner">
				<?php } ?>
				<div class="elementor-section-wrap">
					<?php $this->print_elements( $elements_data ); ?>
				</div>
				<?php if ( ! $is_dom_optimization_active ) { ?>
			</div>
		<?php } ?>
		</ul>
		<?php
	}
}


/**
 * var a = $e.routes.getComponent('panel/page-settings/settings')
 * a.manager.editedView.model.get('settings').on('change',() => console.log(1));
 */
