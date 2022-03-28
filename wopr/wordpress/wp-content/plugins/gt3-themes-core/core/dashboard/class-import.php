<?php

namespace GT3\ThemesCore\Dashboard;

use GT3\ThemesCore\Customizer;
use GT3\ThemesCore\DashBoard;
use GT3\ThemesCore\Registration;
use RevSlider;
use RevSliderSliderImport;
use WP_Error;
use Elementor\Plugin as Elementor_plugin;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

define('IMPORT_DEBUG', true);


final class Import extends \WP_Importer {
	// information to import from JSON file
	protected $version;
	protected $authors     = array();
	protected $posts       = array();
	protected $terms       = array();
	protected $categories  = array();
	protected $tags        = array();
	protected $menu_items  = array();
	protected $attachments = array();
	protected $rev_sliders = array();
	protected $base_url    = '';

	// mappings from old information to new
	protected $processed_authors     = array();
	protected $author_mapping        = array();
	protected $processed_terms       = array();
	protected $processed_posts       = array();
	protected $post_orphans          = array();
	protected $processed_menu_items  = array();
	protected $menu_item_orphans     = array();
	protected $missing_menu_items    = array();
	protected $url_remap             = array();
	protected $featured_images       = array();
	protected $processed_attachments = array();
	protected $fetch_attachments     = true;
	protected $log                   = array();

	protected $import_data = array();
	protected $scheduled   = array();

	const STEP_IDLE        = -1;
	const STEP_TERMS       = 0;
	const STEP_ATTACHMENTS = 1;
	const STEP_POSTS       = 2;
	const STEP_FINISH      = 3;

	private $active        = false;
	private $current_step  = self::STEP_IDLE;
	private $current_index = 0;
	private $global_index  = 0;
	private $max_items     = array( -1, -1, -1, -1 );

	private $save_log          = false;
	private $save_log_external = true;

	const LIMIT_POSTS       = 10;
	const LIMIT_ATTACHMENTS = 5;

	private $cache_status = null;

	/** ----------------------------------------------------- */
	protected $elementor_active         = false;
	protected $elementor_media_controls = array();
	protected $theme                    = null;

	protected $path = '';
	protected $url  = '';

	protected $upload_dir = '';

	private static $nonce_key         = '_gt3_core_import_nonce';
	private        $_cache_option_key = '_gt3_core_import';

	private static $instance = null;
	/** ----------------------------------------------------- */

	/** @return self */
	public static function instance(){
		if(is_null(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct(){
		$this->get_elementor_media_controls_names();
		$this->theme = wp_get_theme()->get_template();

		$this->path = trailingslashit(realpath(get_template_directory().'/core/demo-data/').'/import');
		$this->url  = get_template_directory_uri().'/core/demo-data/import/';

		$upload_dir       = wp_upload_dir();
		$this->upload_dir = trailingslashit($upload_dir['basedir']).'gt3-demo-import/';
		$this->maybe_create_folder($this->upload_dir);

		add_action('rest_api_init', array( $this, 'rest_init' ));
	}

	public function commands(\WP_REST_Request $request){
		$this->load_import_file();
		$this->load_states();

		if(is_wp_error($this->import_data)) {
			return rest_ensure_response(
				array(
					'error' => true,
				)
			);
		}

		$command = $request->get_param('command');
		$filter  = $request->get_param('filter');
		switch($command) {
			case 'start':
				if($this->active) {
					return rest_ensure_response($this->get_current_status());
				}

				$this->clear_log();
				$this->save_scheduled($filter);

				$this->processed_authors     = array();
				$this->author_mapping        = array();
				$this->processed_terms       = array();
				$this->processed_posts       = array();
				$this->post_orphans          = array();
				$this->processed_menu_items  = array();
				$this->menu_item_orphans     = array();
				$this->missing_menu_items    = array();
				$this->url_remap             = array();
				$this->featured_images       = array();
				$this->current_step          = self::STEP_TERMS;
				$this->current_index         = 0;
				$this->global_index          = 0;
				$this->processed_attachments = array();
				$this->active                = true;
				$this->log                   = array();
				$this->save_states();

				return rest_ensure_response(
					array(
						'error' => false,
					)
				);

			case 'stop':
				$this->active = false;
				$this->save_states();

				return rest_ensure_response(
					array(
						'error' => false,
					)
				);
		}

		$type = $request->get_param('type');
		if(is_null($type)) {
			$type = 'json';
		}

		if($type == 'text') {
			echo 'Step: '.$this->current_step.PHP_EOL.
			     'Index: '.$this->current_index.' of '.$this->get_max_index().PHP_EOL.
			     'Logs:'.PHP_EOL.PHP_EOL.
			     implode(PHP_EOL, array_reverse($this->log));
			die;
		}

		return rest_ensure_response($this->get_current_status());
	}

	public function rest_init(){
		register_rest_route(
			'gt3_core/v1/import',
			'commands',
			array(
				array(
					'methods'             => WP_REST_Server::ALLMETHODS,
					'permission_callback' => function(WP_REST_Request $request){
						return current_user_can('administrator');
					},
					'callback'            => array( $this, 'commands' ),
				)
			)
		);

		register_rest_route(
			'gt3_core/v1/import',
			'tick',
			array(
				array(
					'methods'             => WP_REST_Server::ALLMETHODS,
					'permission_callback' => function(WP_REST_Request $request){
						return current_user_can('administrator');
					},
					'callback'            => array( $this, 'rest_tick' ),
				)
			)
		);

		register_rest_route(
			'gt3_core/v1/import',
			'get_settings',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'permission_callback' => function(){
						return current_user_can('administrator');
					},
					'callback'            => array( $this, 'get_settings' ),
				)
			)
		);

		register_rest_route(
			'gt3_core/v1/import',
			'import_homepage',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'permission_callback' => function(){
						return current_user_can('administrator');
					},
					'callback'            => array( $this, 'rest_import_homepage' ),
				)
			)
		);

		register_rest_route(
			'gt3_core/v1/import',
			'logs',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'permission_callback' => function(){
						return current_user_can('administrator');
					},
					'callback'            => array( $this, 'rest_logs' ),
				)
			)
		);
	}

	protected function disable_cache(){
		$this->cache_status = wp_suspend_cache_addition();
		wp_suspend_cache_addition(true);
	}

	private function maybe_create_folder($folder){
		if(false === stream_resolve_include_path($folder) || !is_dir($folder)) {
			@mkdir($folder);
		}
	}

	public function get_settings(){
		$this->load_import_file();

		$woo_import_url = '';
		if(class_exists('WooCommerce')) {
			$woo_import_url = wp_normalize_path(stream_resolve_include_path($this->path.'gt3-wc-products.csv'));
			if($woo_import_url) {
				$params         = array(
					'post_type'       => 'product',
					'page'            => 'product_importer',
					'step'            => 'mapping',
					'file'            => $woo_import_url,
					'delimiter'       => ',',
					'update_existing' => 0,
					'_wpnonce'        => wp_create_nonce('woocommerce-csv-importer'), // wp_nonce_url() escapes & to &amp; breaking redirects.
				);
				$woo_import_url = add_query_arg($params, admin_url('edit.php'));
			}
		}

		return rest_ensure_response(
			array_merge(
				array(
					'headerLogo'     => get_template_directory_uri().'/core/admin/img/logo_options.png',
					'import_data'    => $this->import_data,
					'importUrl'      => $this->url,
					'system'         => DashBoard::instance()->get_system_info(),
					'themeVersion'   => Registration::instance()->get_theme_version(),
					'_import_nonce'  => wp_create_nonce(self::$nonce_key),
					'woo_import_url' => $woo_import_url,
				)
			)
		);
	}

	public function rest_logs(){
		$file = $this->upload_dir.'log.txt';
		$file = realpath($file);

		$log = 'File not exist';

		if($file && file_exists($file)) {
			$log = file_get_contents($file);

			if(empty($log)) {
				$log = 'Log empty';
			}
		}

		return rest_ensure_response(
			array(
				'log' => $log,
			)
		);
	}

	public function rest_tick(){
		ignore_user_abort(true);
		set_time_limit(0);

		ob_start();

		$this->load_scheduled_file();
		$this->load_states();

		if(is_wp_error($this->scheduled)) {
			$this->log('missing import data');

			return rest_ensure_response(
				array(
					'error'   => true,
					'respond' => 'missing import data',
				)
			);
		}

		if(!$this->active) {
			$this->log('process stopped');

			return rest_ensure_response(
				array(
					'error'   => true,
					'respond' => 'process stopped',
				)
			);
		}

		require_once ABSPATH.'wp-admin/includes/post.php';
		require_once ABSPATH.'wp-admin/includes/comment.php';
		require_once ABSPATH.'wp-admin/includes/taxonomy.php';
		require_once ABSPATH.'wp-admin/includes/image.php';

		wp_suspend_cache_invalidation(true);

		switch($this->current_step) {
			case self::STEP_TERMS:
				$this->import_theme_options();
				$this->process_terms();

				$this->current_step  = self::STEP_ATTACHMENTS;
				$this->current_index = 0;
				break;

			case self::STEP_ATTACHMENTS:
				$this->process_attachments();

				if($this->current_index >= count($this->attachments)) {
					$this->current_step  = self::STEP_POSTS;
					$this->current_index = 0;
				}
				break;

			case self::STEP_POSTS:

				$this->process_posts();

				if($this->current_index >= count($this->posts)) {
					$this->current_step  = self::STEP_FINISH;
					$this->current_index = $this->get_max_index(self::STEP_FINISH);
				}
				break;

			case self::STEP_FINISH:
				$this->process_menus();

				$this->backfill_parents();
				$this->backfill_attachment_urls();
				$this->remap_featured_images();

				$this->import_widgets();
				$this->import_settings();
				$this->process_rev_sliders();

				wp_cache_flush();
				foreach(get_taxonomies() as $tax) {
					delete_option("{$tax}_children");
					_get_term_hierarchy($tax);
				}

				wp_defer_term_counting(false);
				wp_defer_comment_counting(false);

				do_action('gt3/core/import/finish');

				$this->active = false;
				break;
		}
		wp_suspend_cache_invalidation(false);

		$this->save_states();

		return rest_ensure_response(
			array(
				'error' => false,
				'state' => $this->get_current_status(),
			)
		);
	}

	protected function get_meta_key($key, $post){
		$meta = array();
		if(is_array($post)) {
			$meta = key_exists('postmeta', $post) ? $post['postmeta'] : $post;
		}
		if(is_array($meta)) {
			foreach($meta as $_meta) {
				$_meta = array_merge(
					array(
						'meta_key'   => null,
						'meta_value' => null
					), is_array($_meta) ? $_meta : array()
				);

				if($_meta['meta_key'] === $key) {
					return $_meta['meta_value'];
				}
			}
		}

		return null;
	}

	public function rest_import_homepage(WP_REST_Request $request){
		$this->load_import_file();

		require_once(ABSPATH.'wp-admin/includes/post.php');
		require_once(ABSPATH.'wp-admin/includes/comment.php');
		require_once ABSPATH.'wp-admin/includes/taxonomy.php';
		require_once ABSPATH.'wp-admin/includes/image.php';

		$post = $request->get_param('post');
		$post = $this->load_file('posts', $post);
		if(is_wp_error($post)) {
			return rest_ensure_response(
				array(
					'error'   => true,
					'respond' => $post->get_error_message(),
				)
			);
		}
		$media = key_exists('media', $post) && is_array($post['media']) ? $post['media'] : array();

		$_header = $this->get_meta_key('_wpda-builder-header', $post);
		$_footer = $this->get_meta_key('_wpda-builder-footer', $post);

		if($_header) {
			$_header = $this->load_file('posts', $_header);
			if(!is_wp_error($_header)) {
				$media = key_exists('media', $_header) && is_array($_header['media']) ? array_merge($media, $_header['media']) : $media;
			} else {
				$_header = null;
			}
		}

		if($_footer) {
			$_footer = $this->load_file('posts', $_footer);
			if(!is_wp_error($_footer)) {
				$media = key_exists('media', $_footer) && is_array($_footer['media']) ? array_merge($media, $_footer['media']) : $media;
			} else {
				$_footer = null;
			}
		}

		$media = array_unique($media);

		foreach($media as $post_id) {
			$_media = $this->load_file('posts', $post_id);
			if(is_wp_error($_media)) {
				continue;
			}

			$this->process_post($_media);
		}

		if($_footer) {
			$this->process_post($_footer);
		}
		if($_header) {
			$this->process_post($_header);
		}

		$this->process_post($post);

		$this->backfill_parents();
		$this->backfill_attachment_urls();
		$this->remap_featured_images();

		return rest_ensure_response(
			array(
				'error'   => false,
				'respond' => 'Imported',
			)
		);
	}

	public function get_current_status(){
		return array(
			'step'         => $this->current_step,
			'index'        => $this->current_index,
			'global_index' => $this->global_index,
			'max'          => $this->get_max_index(),
			'all'          => $this->max_items[self::STEP_FINISH],
			'active'       => $this->active
		);
	}

	protected function count_max_steps(){
		$terms       = count($this->terms);
		$attachments = count($this->attachments);
		$posts       = count($this->posts);
		$menus       = count($this->menu_items);

		$this->max_items = array(
			self::STEP_TERMS       => $terms,
			self::STEP_ATTACHMENTS => $attachments,
			self::STEP_POSTS       => $posts+$menus,
			self::STEP_FINISH      => $terms+$attachments+$posts+$menus
		);
	}

	protected function get_max_index($step = null){
		if(is_null($step)) {
			$step = $this->current_step;
		}

		return (key_exists($step, $this->max_items) && $this->max_items[$step] > -1) ? $this->max_items[$step] : 0;
	}

	protected function load_states(){
		$file = $this->get_state_file();
		if(!file_exists($file)) {
			return;
		}

		$fp = fopen($file, 'r');
		if($fp) {
			$state = '';
			while(!feof($fp)) {
				$state .= fread($fp, 1024);
			}
			fclose($fp);
			$state = json_decode($state, true);
			if(json_last_error()) {
				$state = array();
			}
		}
		if(!is_array($state)) {
			$state = array();
		}

		$this->processed_authors     = $this->get_state('processed_authors', $state);
		$this->author_mapping        = $this->get_state('author_mapping', $state);
		$this->processed_terms       = $this->get_state('processed_terms', $state);
		$this->processed_posts       = $this->get_state('processed_posts', $state);
		$this->post_orphans          = $this->get_state('post_orphans', $state);
		$this->processed_menu_items  = $this->get_state('processed_menu_items', $state);
		$this->menu_item_orphans     = $this->get_state('menu_item_orphans', $state);
		$this->missing_menu_items    = $this->get_state('missing_menu_items', $state);
		$this->url_remap             = $this->get_state('url_remap', $state);
		$this->featured_images       = $this->get_state('featured_images', $state);
		$this->current_step          = $this->get_state('current_step', $state, self::STEP_IDLE);
		$this->current_index         = $this->get_state('current_index', $state, 0);
		$this->global_index          = $this->get_state('global_index', $state, 0);
		$this->processed_attachments = $this->get_state('processed_attachments', $state);
		$this->active                = $this->get_state('active', $state, false);
		$this->log                   = $this->get_state('log', $state);
	}

	protected function get_state_file(){
		return trailingslashit($this->upload_dir).'import-'.$this->theme.'-state.json';
	}

	protected function save_states(){
		$state = array(
			'current_step'          => $this->current_step,
			'current_index'         => $this->current_index,
			'global_index'          => $this->global_index,
			'processed_attachments' => $this->processed_attachments,
			'active'                => $this->active,

			'processed_authors'    => $this->processed_authors,
			'author_mapping'       => $this->author_mapping,
			'processed_terms'      => $this->processed_terms,
			'processed_posts'      => $this->processed_posts,
			'post_orphans'         => $this->post_orphans,
			'processed_menu_items' => $this->processed_menu_items,
			'menu_item_orphans'    => $this->menu_item_orphans,
			'missing_menu_items'   => $this->missing_menu_items,
			'url_remap'            => $this->url_remap,
			'featured_images'      => $this->featured_images,
			'log'                  => $this->log,
		);
		if(!$this->save_log) {
			unset($state['log']);
		}

		$file = $this->get_state_file();
		$fp   = fopen($file, 'w+');
		if($fp) {
			$state   = json_encode($state);
			$len     = strlen($state);
			$written = $fwrite = 0;

			if($len > 0) {
				while($len > $written && $fwrite !== false) {
					$line   = substr($state, $written, 256);
					$fwrite = fwrite($fp, $line);
					fflush($fp);
					if(false !== $fwrite) {
						$written += $fwrite;
					}
				}
			}

			fflush($fp);
			fclose($fp);
		}
	}

	protected function get_state($state_name, $state, $default = array()){
		$val = $default;
		if(key_exists($state_name, $state)) {
			$val = $state[$state_name];
		} else if(property_exists($this, $state_name)) {
			$val = $this->{$state_name};
		}

		return $val;
	}

	protected function clear_log(){
		if($this->save_log_external) {
			$file = $this->upload_dir.'log.txt';

			if(file_exists($file)) {
				@unlink($file);
			}
		}
	}

	protected function log($msg){
		if($this->save_log_external) {
			$file = $this->upload_dir.'log.txt';
			$fp   = fopen($file, 'a+');
			if($fp) {
				fwrite($fp, json_encode($msg).PHP_EOL);
				fflush($fp);
				fclose($fp);
			}
		}

		$this->log[] = json_encode($msg);
	}

	protected function process_rev_sliders(){
		if(class_exists('RevSlider')) {
			require_once RS_PLUGIN_PATH.'/admin/includes/template.class.php';
			require_once RS_PLUGIN_PATH.'/includes/cssparser.class.php';
			require_once RS_PLUGIN_PATH.'/admin/includes/plugin-update.class.php';
			require_once RS_PLUGIN_PATH.'/admin/includes/import.class.php';

			$i = new RevSliderSliderImport();

			foreach($this->rev_sliders as $import_slider) {
				$file = stream_resolve_include_path($this->path.'rev_sliders/'.$import_slider.'.zip');
				if($file) {
					$i->import_slider(true, $file);
				}
			}
		}
	}

	protected function get_elementor_media_controls_names(){
		$this->elementor_active = class_exists('Elementor\Plugin');

		if(!$this->elementor_active) {
			return;
		}

		$this->elementor_media_controls = array_unique(
			apply_filters(
				'gt3/export/elementor/media_controls',
				array(
					\Elementor\Controls_Manager::MEDIA,
					\Elementor\Controls_Manager::GALLERY,
				)
			)
		);
	}

	/**
	 * Create new terms based on import information
	 *
	 * Doesn't create a term its slug already exists
	 */
	function process_terms(){
		$this->terms = apply_filters('wp_import_terms', $this->terms);

		if(empty($this->terms)) {
			return;
		}

		foreach($this->terms as $term) {
			$this->current_index++;
			$this->global_index++;
			$this->save_states();

			if(!is_array($term)) {
				$term = array();
			}

			$term = array_merge(
				array(
					'id'   => 0,
					'type' => 'error',
				), $term
			);

			$this->process_term($term);
		}
	}

	public function process_term($term){
		$term_id   = $term['id'];
		$term_slug = $term['type'];

		$term = $this->load_file('taxonomy', $term_id);
		if(is_wp_error($term)) {
			$this->log($term_id);
			$this->log($term->get_error_message());

			return;
		}

		// if the term already exists in the correct taxonomy leave it alone
		$term_id = term_exists($term['term_slug'], $term['term_taxonomy']);
		if($term_id) {
			if(is_array($term_id)) {
				$term_id = $term_id['term_id'];
			}
			if(isset($term['term_id'])) {
				$this->processed_terms[intval($term['term_id'])] = (int) $term_id;
			}

			return;
		}

		if(empty($term['term_parent'])) {
			$parent = 0;
		} else {
			$parent = term_exists($term['term_parent'], $term['term_taxonomy']);
			if(is_array($parent)) {
				$parent = $parent['term_id'];
			}
		}
		$term        = wp_slash($term);
		$description = isset($term['term_description']) ? $term['term_description'] : '';
		$termarr     = array( 'slug' => $term['term_slug'], 'description' => $description, 'parent' => intval($parent) );

		$id = wp_insert_term($term['term_name'], $term['term_taxonomy'], $termarr);
		if(!is_wp_error($id)) {
			if(isset($term['term_id'])) {
				$this->processed_terms[intval($term['term_id'])] = $id['term_id'];
			}
		} else {
			$this->log($term_id);
			$this->log(sprintf(__('Failed to import %s %s', 'wordpress-importer'), esc_html($term['term_taxonomy']), esc_html($term['term_name'])).' '.$id->get_error_message());

			return;
		}

		$this->process_termmeta($term, $id['term_id']);
	}

	/**
	 * Add metadata to imported term.
	 *
	 * @param array $term    Term data from WXR import.
	 * @param int   $term_id ID of the newly created term.
	 *
	 * @since 0.6.2
	 *
	 */
	protected function process_termmeta($term, $term_id){
		if(!isset($term['term_meta'])) {
			$term['term_meta'] = array();
		}

		/**
		 * Filters the metadata attached to an imported term.
		 *
		 * @param array $termmeta Array of term meta.
		 * @param int   $term_id  ID of the newly created term.
		 * @param array $term     Term data from the WXR import.
		 *
		 * @since 0.6.2
		 *
		 */
		$term['term_meta'] = apply_filters('wp_import_term_meta', $term['term_meta'], $term_id, $term);

		if(empty($term['term_meta'])) {
			return;
		}

		foreach($term['term_meta'] as $meta) {
			/**
			 * Filters the meta key for an imported piece of term meta.
			 *
			 * @param string $meta_key Meta key.
			 * @param int    $term_id  ID of the newly created term.
			 * @param array  $term     Term data from the WXR import.
			 *
			 * @since 0.6.2
			 *
			 */
			$key = apply_filters('import_term_meta_key', $meta['meta_key'], $term_id, $term);
			if(!$key) {
				continue;
			}

			// Export gets meta straight from the DB so could have a serialized string
			$value = maybe_unserialize($meta['meta_value']);

			//add_term_meta( $term_id, $key, $value );
			add_term_meta($term_id, wp_slash($key), $value);

			/**
			 * Fires after term meta is imported.
			 *
			 * @param int    $term_id ID of the newly created term.
			 * @param string $key     Meta key.
			 * @param mixed  $value   Meta value.
			 *
			 * @since 0.6.2
			 *
			 */
			do_action('import_term_meta', $term_id, $key, $value);
		}
	}

	function process_post($post){
		$post = apply_filters('wp_import_post_data_raw', $post);

		if(!post_type_exists($post['post_type'])) {
			$this->log(
				sprintf(
					__('Failed to import &#8220;%s&#8221;: Invalid post type %s', 'wordpress-importer'),
					esc_html($post['post_title']), esc_html($post['post_type'])
				)
			);
			$this->log($post);
			do_action('wp_import_post_exists', $post);

			return __LINE__;
		}

		if(isset($this->processed_posts[$post['post_id']]) && !empty($post['post_id'])) {
			return __LINE__;
		}

		if($post['status'] == 'auto-draft') {
			return __LINE__;
		}

		if('nav_menu_item' == $post['post_type']) {
			$this->process_menu_item($post);

			return __LINE__;
		}

		$empty_title = null;
		if(empty($post['post_title'])) {
			$empty_title        = $post['post_title'];
			$post['post_title'] = 'attachment-'.$post['post_id'];
		}

		$post_type_object = get_post_type_object($post['post_type']);

		$post_exists = post_exists($post['post_title'], '', $post['post_date']);

		/**
		 * Filter ID of the existing post corresponding to post currently importing.
		 *
		 * Return 0 to force the post to be imported. Filter the ID to be something else
		 * to override which existing post is mapped to the imported post.
		 *
		 * @param int   $post_exists Post ID, or 0 if post did not exist.
		 * @param array $post        The post array to be inserted.
		 *
		 * @see   post_exists()
		 * @since 0.6.2
		 *
		 */
		$post_exists = apply_filters('wp_import_existing_post', $post_exists, $post);

		if($post_exists && get_post_type($post_exists) == $post['post_type']) {
			$this->log(sprintf(__('%s &#8220;%s&#8221; already exists.', 'wordpress-importer'), $post_type_object->labels->singular_name, esc_html($post['post_title'])));
			$comment_post_ID                                 = $post_id = $post_exists;
			$this->processed_posts[intval($post['post_id'])] = intval($post_exists);
		} else {
			$post_parent = (int) $post['post_parent'];
			if($post_parent) {
				// if we already know the parent, map it to the new local ID
				if(isset($this->processed_posts[$post_parent])) {
					$post_parent = $this->processed_posts[$post_parent];
					// otherwise record the parent for later
				} else {
					$this->post_orphans[intval($post['post_id'])] = $post_parent;
					$post_parent                                  = 0;
				}
			}

			// map the post author
			$author = sanitize_user($post['post_author'], true);
			if(isset($this->author_mapping[$author])) {
				$author = $this->author_mapping[$author];
			} else {
				$author = (int) get_current_user_id();
			}

			$postdata = array(
				'import_id'      => $post['post_id'],
				'post_author'    => $author,
				'post_date'      => $post['post_date'],
				'post_date_gmt'  => $post['post_date_gmt'],
				'post_content'   => $post['post_content'],
				'post_excerpt'   => $post['post_excerpt'],
				'post_title'     => $post['post_title'],
				'post_status'    => $post['status'],
				'post_name'      => $post['post_name'],
				'comment_status' => $post['comment_status'],
				'ping_status'    => $post['ping_status'],
				'guid'           => $post['guid'],
				'post_parent'    => $post_parent,
				'menu_order'     => $post['menu_order'],
				'post_type'      => $post['post_type'],
				'post_password'  => $post['post_password']
			);

			$original_post_ID = $post['post_id'];
			$postdata         = apply_filters('wp_import_post_data_processed', $postdata, $post);

			$postdata = wp_slash($postdata);

			if('attachment' == $postdata['post_type']) {
				$remote_url = !empty($post['attachment_url']) ? $post['attachment_url'] : $post['guid'];

				// try to use _wp_attached file for upload folder placement to ensure the same location as the export site
				// e.g. location is 2003/05/image.jpg but the attachment post_date is 2010/09, see media_handle_upload()
				$postdata['upload_date'] = $post['post_date'];
				if(isset($post['postmeta'])) {
					foreach($post['postmeta'] as $meta) {
						if($meta['meta_key'] == '_wp_attached_file') {
							if(preg_match('%^[0-9]{4}/[0-9]{2}%', $meta['meta_value'], $matches)) {
								$postdata['upload_date'] = $matches[0];
							}
							break;
						}
					}
				}

				$comment_post_ID = $post_id = $this->process_attachment($postdata, $remote_url);

				$this->save_attachment_id($post_id, $postdata);
			} else {
				$comment_post_ID = $post_id = wp_insert_post($postdata, true);
				do_action('wp_import_insert_post', $post_id, $original_post_ID, $postdata, $post);
				$post = $this->fix_import_post($post, $postdata);
			}

			if(is_wp_error($post_id)) {
				$this->log(
					sprintf(
						__('Failed to import %s &#8220;%s&#8221; %s', 'wordpress-importer'),
						$post_type_object->labels->singular_name, esc_html($post['post_title']), __LINE__
					).' '.$post_id->get_error_message()
				);

				return __LINE__;
			}

			if($post['is_sticky'] == 1) {
				stick_post($post_id);
			}
		}

		// map pre-import ID to local ID
		$this->processed_posts[intval($post['post_id'])] = (int) $post_id;

		if(!is_null($empty_title)) {
			wp_update_post(
				array(
					'ID'         => $post_id,
					'post_title' => $empty_title,
				)
			);
		}

		if(!isset($post['terms'])) {
			$post['terms'] = array();
		}

		$post['terms'] = apply_filters('wp_import_post_terms', $post['terms'], $post_id, $post);

		// add categories, tags and other terms
		if(!empty($post['terms'])) {
			$terms_to_set = array();
			foreach($post['terms'] as $term) {
				if(!is_array($term)) {
					$term = $this->load_file('taxonomy', $term);
				}
				// back compat with WXR 1.0 map 'tag' to 'post_tag'
				$taxonomy    = $term['term_taxonomy'];
				$term_exists = term_exists($term['term_slug'], $taxonomy);
				$term_id     = is_array($term_exists) ? $term_exists['term_id'] : $term_exists;
				if(!$term_id) {
					$t = wp_insert_term($term['term_name'], $taxonomy, array( 'slug' => $term['term_slug'] ));
					if(!is_wp_error($t)) {
						$term_id = $t['term_id'];
						do_action('wp_import_insert_term', $t, $term, $post_id, $post);
					} else {
						$this->log(sprintf(__('Failed to import %s %s', 'wordpress-importer'), esc_html($taxonomy), esc_html($term['term_name'])).' '.$t->get_error_message());

						do_action('wp_import_insert_term_failed', $t, $term, $post_id, $post);
						continue;
					}
				}
				$terms_to_set[$taxonomy][] = intval($term_id);
			}

			foreach($terms_to_set as $tax => $ids) {
				$tt_ids = wp_set_post_terms($post_id, $ids, $tax);
				do_action('wp_import_set_post_terms', $tt_ids, $ids, $tax, $post_id, $post);
			}
			unset($post['terms'], $terms_to_set);
		}

		if(!isset($post['comments'])) {
			$post['comments'] = array();
		}

		$post['comments'] = apply_filters('wp_import_post_comments', $post['comments'], $post_id, $post);

		// add/update comments
		if(!empty($post['comments'])) {
			$num_comments      = 0;
			$inserted_comments = array();
			foreach($post['comments'] as $comment) {
				$comment_id                                       = $comment['comment_id'];
				$newcomments[$comment_id]['comment_post_ID']      = $comment_post_ID;
				$newcomments[$comment_id]['comment_author']       = $comment['comment_author'];
				$newcomments[$comment_id]['comment_author_email'] = $comment['comment_author_email'];
				$newcomments[$comment_id]['comment_author_IP']    = $comment['comment_author_IP'];
				$newcomments[$comment_id]['comment_author_url']   = $comment['comment_author_url'];
				$newcomments[$comment_id]['comment_date']         = $comment['comment_date'];
				$newcomments[$comment_id]['comment_date_gmt']     = $comment['comment_date_gmt'];
				$newcomments[$comment_id]['comment_content']      = $comment['comment_content'];
				$newcomments[$comment_id]['comment_approved']     = $comment['comment_approved'];
				$newcomments[$comment_id]['comment_type']         = $comment['comment_type'];
				$newcomments[$comment_id]['comment_parent']       = $comment['comment_parent'];
				$newcomments[$comment_id]['commentmeta']          = isset($comment['commentmeta']) ? $comment['commentmeta'] : array();
				if(isset($this->processed_authors[$comment['comment_user_id']])) {
					$newcomments[$comment_id]['user_id'] = $this->processed_authors[$comment['comment_user_id']];
				}
			}
			ksort($newcomments);

			foreach($newcomments as $key => $comment) {
				// if this is a new post we can skip the comment_exists() check
				if(!$post_exists || !comment_exists($comment['comment_author'], $comment['comment_date'])) {
					if(isset($inserted_comments[$comment['comment_parent']])) {
						$comment['comment_parent'] = $inserted_comments[$comment['comment_parent']];
					}
					$comment                 = wp_slash($comment);
					$comment                 = wp_filter_comment($comment);
					$inserted_comments[$key] = wp_insert_comment($comment);
					do_action('wp_import_insert_comment', $inserted_comments[$key], $comment, $comment_post_ID, $post);

					foreach($comment['commentmeta'] as $meta) {
						$value = maybe_unserialize($meta['meta_value']);
						//add_comment_meta( $inserted_comments[$key], $meta['key'], $value );
						add_comment_meta($inserted_comments[$key], wp_slash($meta['meta_key']), wp_slash($value));
					}

					$num_comments++;
				}
			}
			unset($newcomments, $inserted_comments, $post['comments']);
		}

		if(!isset($post['postmeta'])) {
			$post['postmeta'] = array();
		}

		$post['postmeta'] = apply_filters('wp_import_post_meta', $post['postmeta'], $post_id, $post);

		// add/update post meta
		if(!empty($post['postmeta'])) {
			foreach($post['postmeta'] as $meta) {
				if($meta['meta_key'] === '_elementor_css') {
					continue;
				}
				$key   = apply_filters('import_post_meta_key', $meta['meta_key'], $post_id, $post);
				$value = false;

				if('_edit_last' == $key) {
					if(isset($this->processed_authors[intval($meta['meta_value'])])) {
						$value = $this->processed_authors[intval($meta['meta_value'])];
					} else {
						$key = false;
					}
				}

				if($key) {
					// export gets meta straight from the DB so could have a serialized string
					if(!$value) {
						$value = maybe_unserialize($meta['meta_value']);
					}

					//add_post_meta( $post_id, $key, $value );
					add_post_meta($post_id, wp_slash($key), wp_slash($value));
					do_action('import_post_meta', $post_id, $key, $value);

					// if the post has a featured image, take note of this in case of remap
					if('_thumbnail_id' == $key) {
						$this->featured_images[$post_id] = (int) $value;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Create new posts based on import information
	 *
	 * Posts marked as having a parent which doesn't exist will become top level items.
	 * Doesn't create a new post if: the post type doesn't exist, the given post ID
	 * is already noted as imported or a post with the same title and date already exists.
	 * Note that new/updated terms, comments and meta are imported for the last of the above.
	 */
	function process_posts(){
		$posts = array_slice($this->posts, $this->current_index, self::LIMIT_POSTS, true);

		foreach($posts as $post) {
			$this->current_index++;
			$this->global_index++;
			$this->save_states();

			if(!is_array($post)) {
				$post = array();
			}

			$post = array_merge(
				array(
					'id'   => 0,
					'type' => 'error',
				), $post
			);

			$post_id   = $post['id'];
			$post_type = $post['type'];

			$post = $this->load_file('posts', $post_id);
			if(is_wp_error($post)) {
				$this->log($post_id);
				$this->log($post->get_error_message());
				continue;
			}

			$this->process_post($post);
		}
	}

	public function process_attachments(){

		$attachments = array_slice($this->attachments, $this->current_index, self::LIMIT_ATTACHMENTS, true);

		foreach($attachments as $post_id => $post) {
			$this->current_index++;
			$this->global_index++;
			$this->save_states();

			if(!is_array($post)) {
				$post = array();
			}

			$post = array_merge(
				array(
					'id'   => 0,
					'type' => 'error',
				), $post
			);

			$post_id   = $post['id'];
			$post_type = $post['type'];

			$post = $this->load_file('posts', $post_id);
			if(is_wp_error($post)) {
				$this->log($post_id);
				$this->log($post->get_error_message());
				continue;
			}

			$this->process_post($post);
		}
	}

	public function process_menus(){
		$this->log($this->menu_items);
		foreach($this->menu_items as $post) {
			$this->current_index++;
			$this->global_index++;
			$this->save_states();

			if(!is_array($post)) {
				$post = array();
			}

			$post = array_merge(
				array(
					'id'   => 0,
					'type' => 'error',
				), $post
			);

			$post_id   = $post['id'];
			$post_type = $post['type'];

			$post = $this->load_file('posts', $post_id);
			if(is_wp_error($post)) {
				$this->log($post_id);
				$this->log($post->get_error_message());
				continue;
			}

			$this->process_post($post);
		}
	}

	/**
	 * Attempt to create a new menu item from import data
	 *
	 * Fails for draft, orphaned menu items and those without an associated nav_menu
	 * or an invalid nav_menu term. If the post type or term object which the menu item
	 * represents doesn't exist then the menu item will not be imported (waits until the
	 * end of the import to retry again before discarding).
	 *
	 * @param array $item Menu item details from WXR file
	 */
	function process_menu_item($item){
		$this->log("Process  menu ${item['post_id']}");
		// skip draft, orphaned menu items
		if('draft' == $item['status']) {
			return;
		}

		$menu_slug = false;
		if(isset($item['terms'])) {
			// loop through terms, assume first nav_menu term is correct menu
			foreach($item['terms'] as $term) {
				if(!is_array($term)) {
					$term = $this->load_file('taxonomy', $term);
				}
				if('nav_menu' == $term['term_taxonomy']) {
					$menu_slug = $term['term_slug'];
					break;
				}
			}
		}

		// no nav_menu term associated with this menu item
		if(!$menu_slug) {
			$this->log(__('Menu item skipped due to missing menu slug', 'wordpress-importer'));

			return;
		}

		$menu_id = term_exists($menu_slug, 'nav_menu');
		if(!$menu_id) {
			$this->log(sprintf(__('Menu item skipped due to invalid menu slug: %s', 'wordpress-importer'), esc_html($menu_slug)));

			return;
		} else {
			$menu_id = is_array($menu_id) ? $menu_id['term_id'] : $menu_id;
		}

		$meta = array();
		foreach($item['postmeta'] as $_meta) {
			$meta[$_meta['meta_key']] = $_meta['meta_value'];
		}

		if('taxonomy' == $meta['_menu_item_type'] && isset($this->processed_terms[intval($meta['_menu_item_object_id'])])) {
			$meta['_menu_item_object_id'] = $this->processed_terms[intval($meta['_menu_item_object_id'])];
		} else if('post_type' == $meta['_menu_item_type'] && isset($this->processed_posts[intval($meta['_menu_item_object_id'])])) {
			$meta['_menu_item_object_id'] = $this->processed_posts[intval($meta['_menu_item_object_id'])];
		} else if('custom' != $meta['_menu_item_type']) {
			// associated object is missing or not imported yet, we'll retry later
			$this->missing_menu_items[] = $item;
			$this->log(__('Menu missed', 'wordpress-importer'));
			$this->log($item);

			return;
		}

		if(isset($this->processed_menu_items[intval($meta['_menu_item_menu_item_parent'])])) {
			$meta['_menu_item_menu_item_parent'] = $this->processed_menu_items[intval($meta['_menu_item_menu_item_parent'])];
		} else if($meta['_menu_item_menu_item_parent']) {
			$this->menu_item_orphans[intval($item['post_id'])] = (int) $meta['_menu_item_menu_item_parent'];
			$meta['_menu_item_menu_item_parent']               = 0;
		}

		// wp_update_nav_menu_item expects CSS classes as a space separated string
		$meta['_menu_item_classes'] = maybe_unserialize($meta['_menu_item_classes']);
		if(is_array($meta['_menu_item_classes'])) {
			$meta['_menu_item_classes'] = implode(' ', $meta['_menu_item_classes']);
		}

		$args = array(
			'menu-item-object-id'   => $meta['_menu_item_object_id'],
			'menu-item-object'      => $meta['_menu_item_object'],
			'menu-item-parent-id'   => $meta['_menu_item_menu_item_parent'],
			'menu-item-position'    => intval($item['menu_order']),
			'menu-item-type'        => $meta['_menu_item_type'],
			'menu-item-title'       => $item['post_title'],
			'menu-item-url'         => $meta['_menu_item_url'],
			'menu-item-description' => $item['post_content'],
			'menu-item-attr-title'  => $item['post_excerpt'],
			'menu-item-target'      => $meta['_menu_item_target'],
			'menu-item-classes'     => $meta['_menu_item_classes'],
			'menu-item-xfn'         => $meta['_menu_item_xfn'],
			'menu-item-status'      => $item['status']
		);

		$id = wp_update_nav_menu_item($menu_id, 0, $args);

		if(is_wp_error($id)) {
			$this->log('Menu id '.$menu_id);
			$this->log($id->get_error_message());
		}
		if($id && !is_wp_error($id)) {
			$this->processed_menu_items[intval($item['post_id'])] = (int) $id;

			if(!empty($meta)) {
				$post_id = $id;
				foreach($meta as $_meta_key => $_meta_value) {
					if(in_array(
						$_meta_key, array(
							'_menu_item_object_id',
							'_menu_item_object',
							'_menu_item_menu_item_parent',
							'menu_order',
							'_menu_item_type',
							'post_title',
							'_menu_item_url',
							'post_content',
							'post_excerpt',
							'_menu_item_target',
							'_menu_item_classes',
							'_menu_item_xfn',
							'status',
						)
					)) {
						continue;
					}
					$key   = apply_filters('import_post_meta_key', $_meta_key, $post_id, $item);
					$value = false;

					if('_edit_last' == $key) {
						if(isset($this->processed_authors[intval($_meta_value)])) {
							$value = $this->processed_authors[intval($_meta_value)];
						} else {
							$key = false;
						}
					}

					if($key) {
						// export gets meta straight from the DB so could have a serialized string
						if(!$value) {
							$value = maybe_unserialize($_meta_value);
						}

						//add_post_meta( $post_id, $key, $value );
						add_post_meta($post_id, wp_slash($key), wp_slash($value));
						do_action('import_post_meta', $post_id, $key, $value);
					}
				}
			}
		}


	}

	/**
	 * If fetching attachments is enabled then attempt to create a new attachment
	 *
	 * @param array  $post Attachment post details from WXR
	 * @param string $url  URL to fetch attachment from
	 *
	 * @return int|WP_Error Post ID on success, WP_Error otherwise
	 */
	function process_attachment($post, $url){
		if(!$this->fetch_attachments) {
			return new WP_Error(
				'attachment_processing_error',
				__('Fetching attachments is not enabled', 'wordpress-importer')
			);
		}

		// if the URL is absolute, but does not contain address, then upload it assuming base_site_url
		if(preg_match('|^/[\w\W]+$|', $url)) {
			$url = rtrim($this->base_url, '/').$url;
		}

		$upload = $this->fetch_remote_file($url, $post);
		if(is_wp_error($upload)) {
			$this->log($post['ID']);
			$this->log($upload->get_error_message());

			return $upload;
		}

		if($info = wp_check_filetype($upload['file'])) {
			$post['post_mime_type'] = $info['type'];
		} else {
			return new WP_Error('attachment_processing_error', __('Invalid file type', 'wordpress-importer'));
		}

		$post['guid'] = $upload['url'];

		// as per wp-admin/includes/upload.php
		$post_id = wp_insert_attachment($post, $upload['file']);
		wp_update_attachment_metadata($post_id, wp_generate_attachment_metadata($post_id, $upload['file']));

		// remap resized image URLs, works by stripping the extension and remapping the URL stub.
		if(preg_match('!^image/!', $info['type'])) {
			$parts = pathinfo($url);
			$name  = basename($parts['basename'], ".{$parts['extension']}"); // PATHINFO_FILENAME in PHP 5.2

			$parts_new = pathinfo($upload['url']);
			$name_new  = basename($parts_new['basename'], ".{$parts_new['extension']}");

			$this->url_remap[$parts['dirname'].'/'.$name] = $parts_new['dirname'].'/'.$name_new;
		}

		return $post_id;
	}

	/**
	 * Attempt to download a remote file attachment
	 *
	 * @param string $url  URL of item to fetch
	 * @param array  $post Attachment details
	 *
	 * @return array|WP_Error Local file location details on success, WP_Error otherwise
	 */
	function fetch_remote_file($url, $post){
		// extract the file name and extension from the url
		$file_name = basename($url);

		// get placeholder file in the upload dir with a unique, sanitized filename
		$upload = wp_upload_bits($file_name, 0, '', $post['upload_date']);
		if($upload['error']) {
			return new WP_Error('upload_dir_error', $upload['error']);
		}

		// fetch the remote url and write it to the placeholder file
		$remote_response = wp_safe_remote_get(
			$url, array(
				'timeout'  => 300,
				'stream'   => true,
				'filename' => $upload['file'],
			)
		);

		$headers = wp_remote_retrieve_headers($remote_response);

		// request failed
		if(!$headers) {
			@unlink($upload['file']);

			return new WP_Error('import_file_error', __('Remote server did not respond', 'wordpress-importer'));
		}

		$remote_response_code = wp_remote_retrieve_response_code($remote_response);

		// make sure the fetch was successful
		if($remote_response_code != '200') {
			@unlink($upload['file']);

			return new WP_Error('import_file_error', sprintf(__('Remote server returned error response %1$d %2$s', 'wordpress-importer'), esc_html($remote_response_code), get_status_header_desc($remote_response_code)));
		}

		$filesize = filesize($upload['file']);

		if(isset($headers['content-length']) && $filesize != $headers['content-length']) {
			@unlink($upload['file']);

			return new WP_Error('import_file_error', __('Remote file is incorrect size', 'wordpress-importer'));
		}

		if(0 == $filesize) {
			@unlink($upload['file']);

			return new WP_Error('import_file_error', __('Zero size file downloaded', 'wordpress-importer'));
		}

		$max_size = (int) $this->max_attachment_size();
		if(!empty($max_size) && $filesize > $max_size) {
			@unlink($upload['file']);

			return new WP_Error('import_file_error', sprintf(__('Remote file is too large, limit is %s', 'wordpress-importer'), size_format($max_size)));
		}

		// keep track of the old and new urls so we can substitute them later
		$this->url_remap[$url]          = $upload['url'];
		$this->url_remap[$post['guid']] = $upload['url']; // r13735, really needed?
		// keep track of the destination if the remote url is redirected somewhere else
		if(isset($headers['x-final-location']) && $headers['x-final-location'] != $url) {
			$this->url_remap[$headers['x-final-location']] = $upload['url'];
		}

		return $upload;
	}

	/**
	 * Attempt to associate posts and menu items with previously missing parents
	 *
	 * An imported post's parent may not have been imported when it was first created
	 * so try again. Similarly for child menu items and menu items which were missing
	 * the object (e.g. post) they represent in the menu
	 */
	function backfill_parents(){
		global $wpdb;

		// find parents for post orphans
		foreach($this->post_orphans as $child_id => $parent_id) {
			$local_child_id = $local_parent_id = false;
			if(isset($this->processed_posts[$child_id])) {
				$local_child_id = $this->processed_posts[$child_id];
			}
			if(isset($this->processed_posts[$parent_id])) {
				$local_parent_id = $this->processed_posts[$parent_id];
			}

			if($local_child_id && $local_parent_id) {
				$wpdb->update($wpdb->posts, array( 'post_parent' => $local_parent_id ), array( 'ID' => $local_child_id ), '%d', '%d');
				clean_post_cache($local_child_id);
			}
		}

		// all other posts/terms are imported, retry menu items with missing associated object
		$missing_menu_items = $this->missing_menu_items;
		foreach($missing_menu_items as $item) {
			$this->process_menu_item($item);
		}

		// find parents for menu item orphans
		foreach($this->menu_item_orphans as $child_id => $parent_id) {
			$local_child_id = $local_parent_id = 0;
			if(isset($this->processed_menu_items[$child_id])) {
				$local_child_id = $this->processed_menu_items[$child_id];
			}
			if(isset($this->processed_menu_items[$parent_id])) {
				$local_parent_id = $this->processed_menu_items[$parent_id];
			}

			if($local_child_id && $local_parent_id) {
				update_post_meta($local_child_id, '_menu_item_menu_item_parent', (int) $local_parent_id);
			}
		}
	}

	/**
	 * Use stored mapping information to update old attachment URLs
	 */
	function backfill_attachment_urls(){
		global $wpdb;
		// make sure we do the longest urls first, in case one is a substring of another
		uksort($this->url_remap, array( &$this, 'cmpr_strlen' ));

		foreach($this->url_remap as $from_url => $to_url) {
			// remap urls in post_content
			$wpdb->query($wpdb->prepare("UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, %s, %s)", $from_url, $to_url));
			// remap enclosure urls
			$result = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, %s, %s) WHERE meta_key='enclosure'", $from_url, $to_url));
		}
	}

	/**
	 * Update _thumbnail_id meta to new, imported attachment IDs
	 */
	function remap_featured_images(){
		// cycle through posts that have a featured image
		foreach($this->featured_images as $post_id => $value) {
			if(isset($this->processed_posts[$value])) {
				$new_id = $this->processed_posts[$value];
				// only update if there's a difference
				if($new_id != $value) {
					update_post_meta($post_id, '_thumbnail_id', $new_id);
				}
			}
		}
	}

	/**
	 * Parse a JSON file
	 *
	 * @param string $file Path to WXR file for parsing
	 *
	 * @return array|WP_Error Information gathered from the WXR file
	 */
	function parse($file){
		if(!is_file($file) || !is_readable($file)) {
			return new WP_Error('File not found. '.$file);
		}

		$data = '';
		$fp   = fopen($file, 'r');
		while(!feof($fp)) {
			$data .= fread($fp, 1024);
		}
		fclose($fp);

		try {
			$_data = json_decode($data, true);

			if(!json_last_error()) {
				$data = $_data;
			} else {
				$data = new WP_Error('File broken. '.$file);
			}

		} catch(\Exception $exception) {
			$data = new WP_Error('File broken. '.$exception->getMessage());
		}

		return $data;
	}

	/**
	 * Decide if the given meta key maps to information we will want to import
	 *
	 * @param string $key The meta key to check
	 *
	 * @return string|bool The key if we do want to import, false if not
	 */
	function is_valid_meta_key($key){
		// skip attachment metadata since we'll regenerate it from scratch
		// skip _edit_lock as not relevant for import
		if(in_array($key, array( '_wp_attached_file', '_wp_attachment_metadata', '_edit_lock' ))) {
			return false;
		}

		return $key;
	}

	/**
	 * Decide whether or not the importer should attempt to download attachment files.
	 * Default is true, can be filtered via import_allow_fetch_attachments. The choice
	 * made at the import options screen must also be true, false here hides that checkbox.
	 *
	 * @return bool True if downloading attachments is allowed
	 */
	function allow_fetch_attachments(){
		return apply_filters('import_allow_fetch_attachments', true);
	}

	/**
	 * Decide what the maximum file size for downloaded attachments is.
	 * Default is 0 (unlimited), can be filtered via import_attachment_size_limit
	 *
	 * @return int Maximum attachment file size to import
	 */
	function max_attachment_size(){
		return apply_filters('import_attachment_size_limit', 0);
	}

	/**
	 * Added to http_request_timeout filter to force timeout at 60 seconds during import
	 *
	 * @return int 60
	 */
	function bump_request_timeout($val){
		return 60;
	}

	// return the difference in length between two strings
	function cmpr_strlen($a, $b){
		return strlen($b)-strlen($a);
	}


	protected function load_file($folder, $file){
		if(!empty($folder)) {
			$folder .= '/';
		}
		$filename = $this->path.$folder.$file.'.json';

		return $this->parse($filename);
	}

	/**
	 * @return array Filtered data
	 */
	protected function filter_data(&$array, $args = array( 'filter' => array(), 'remove' => false, 'field' => 'type' )){
		if(!is_array($args)) {
			$args = array( 'filter' => $args );
		}
		if(!is_array($args['filter'])) {
			$args['filter'] = array( $args['filter'] );
		}

		$args   = array_merge(array( 'filter' => array(), 'remove' => false, 'field' => 'type' ), $args);
		$field  = $args['field'];
		$remove = $args['remove'];
		$filter = $args['filter'];

		return array_filter(
			$array, function(&$item, $key) use (&$array, $filter, $remove, $field){
			if(key_exists($field, $item) && in_array($item[$field], $filter)) {
				if($remove) {
					unset($array[$key]);
				}

				return true;
			}

			return false;
		}, ARRAY_FILTER_USE_BOTH
		);

	}

	protected function add_post_to_schedule($post){
		if(!is_array($post)) {
			$post = $this->filter_data($this->import_data['posts'], array( 'filter' => $post, 'field' => 'id' ));
			if(count($post)) {
				$post = array_shift($post);
			} else {
				return;
			}
		}
		$_post = array_merge(
			array(
				'id'     => false,
				'title'  => "",
				'type'   => "none",
				'media'  => [],
				'terms'  => [],
				'header' => null,
				'footer' => null,
			), $post
		);
		if(!key_exists($_post['id'], $this->scheduled['posts']) && !!$_post['id']) {
			$terms = $this->filter_data($this->import_data['terms'], array( 'filter' => $_post['terms'], 'field' => 'id' ));
			array_map(array( $this, 'add_term_to_schedule' ), $terms);
			$attachments = $this->filter_data($this->import_data['attachments'], array( 'filter' => $_post['media'], 'field' => 'id' ));
			array_map(array( $this, 'add_attachment_to_schedule' ), $attachments);

			if($_post['header']) {
				$this->add_post_to_schedule($_post['header']);
			}
			if($_post['footer']) {
				$this->add_post_to_schedule($_post['footer']);
			}
			$this->scheduled['posts'][$_post['id']] = array_intersect_key($_post, array( 'id' => true, 'type' => true ));
		}
	}

	protected function add_term_to_schedule($term){
		if(!key_exists($term['id'], $this->scheduled['terms'])) {
			$this->scheduled['terms'][$term['id']] = $term;
		}
	}

	protected function add_attachment_to_schedule($attachment){
		if(!key_exists($attachment['id'], $this->scheduled['attachments'])) {
			$this->scheduled['attachments'][$attachment['id']] = $attachment;
		}
	}

	protected function save_scheduled($filter = array( 'all' => true )){
		if(!is_array($filter)) {
			$filter = array( 'all' => true );
		}

		$this->scheduled = array(
			'posts'       => array(),
			'terms'       => array(),
			'attachments' => array(),
			'categories' => array(),
			'tags' => array(),
			'menu_items'  => array(),
			'base_url'    => $this->import_data['base_url'],
			'rev_sliders' => array(),
		);


		$posts = array();

		if(isset($filter['all']) && $filter['all']) {
			$this->scheduled = $this->import_data;
		} else {
			if(count($filter)) {
				foreach($filter as $key => $value) {
					$isAll = (is_array($value) && count($value) === 1 && $value[0] === -1) || $value === true;
					switch($key) {
						case 'homepages':
							if($isAll) {
								$value = array_column($this->import_data['homepages'], 'id');
							}
							$posts = array_merge(
								$posts,
								$this->filter_data($this->import_data['posts'], array( 'filter' => $value, 'field' => 'id' ))
							);
							break;
						case 'post':
							$value = ($isAll ?
								$this->filter_data($this->import_data['posts'], 'post') :
								$this->filter_data($this->import_data['posts'], array( 'filter' => $value, 'field' => 'id' )));
							$posts = array_merge($posts, $value);
							break;
						case 'page':
							$value = ($isAll ?
								$this->filter_data($this->import_data['posts'], 'page') :
								$this->filter_data($this->import_data['posts'], array( 'filter' => $value, 'field' => 'id' )));
							$posts = array_merge($posts, $value);

							break;
						case 'team':
							$value = ($isAll ?
								$this->filter_data($this->import_data['posts'], 'team') :
								$this->filter_data($this->import_data['posts'], array( 'filter' => $value, 'field' => 'id' )));

							$posts = array_merge($posts, $value);
							break;
						case 'portfolio':
							$value = ($isAll ?
								$this->filter_data($this->import_data['posts'], 'portfolio') :
								$this->filter_data($this->import_data['posts'], array( 'filter' => $value, 'field' => 'id' )));
							$posts = array_merge($posts, $value);
							break;
						case 'rev_sliders':
							if($value && key_exists('rev_sliders', $this->import_data)) {
								$this->scheduled['rev_sliders'] = $this->import_data['rev_sliders'];
							}
							break;
						case 'headers_footers':
							if($value) {
								$_posts = array();
								if(key_exists('headers', $this->import_data)) {
									$_posts = array_merge($_posts, $this->import_data['headers']);
								}
								if(key_exists('footers', $this->import_data)) {
									$_posts = array_merge($_posts, $this->import_data['footers']);
								}

								$posts = array_merge($posts, $this->filter_data($this->import_data['posts'], array( 'filter' => $_posts, 'field' => 'id' )));
							}
							break;
						case 'cf7':
							if($value) {
								$value = $this->filter_data($this->import_data['posts'], 'wpcf7_contact_form');

								$posts = array_merge($posts, $value);
							}
							break;

						case 'tabs':
							if($value && key_exists('tabs', $this->import_data)) {
								$posts = array_merge($posts, $this->filter_data($this->import_data['posts'], array( 'filter' => $this->import_data['tabs'], 'field' => 'id' )));
							}
							break;
					}
				}
			}

			array_map(array( $this, 'add_post_to_schedule' ), $posts);
		}

		$file = $this->upload_dir.'scheduled.json';
		$fp   = fopen($file, 'w+');
		if($fp) {
			$state   = json_encode($this->scheduled);
			$len     = strlen($state);
			$written = $fwrite = 0;

			if($len > 0) {
				while($len > $written && $fwrite !== false) {
					$line   = substr($state, $written, 256);
					$fwrite = fwrite($fp, $line);
					fflush($fp);
					if(false !== $fwrite) {
						$written += $fwrite;
					}
				}
			}

			fflush($fp);
			fclose($fp);
		}
	}

	public function load_scheduled_file(){
		$file            = $this->upload_dir.'scheduled.json';
		$this->scheduled = $this->parse($file);

		$def = array(
			'categories' => array(),
			'tags'       => array(),
			'terms'      => array(),
			'posts'      => array(),
		);

		if(is_wp_error($this->scheduled)) {
			$this->log('Error loading import file.');

			return $def;
		}

		$this->scheduled = array_merge($def, $this->scheduled);

		$this->posts       = $this->scheduled['posts'];
		$this->terms       = $this->scheduled['terms'];
		$this->categories  = $this->scheduled['categories'];
		$this->tags        = $this->scheduled['tags'];
		$this->base_url    = esc_url($this->scheduled['base_url']);
		$this->attachments = $this->scheduled['attachments'];
		$this->menu_items  = $this->scheduled['menu_items'];
		$this->rev_sliders = $this->scheduled['rev_sliders'];

		$this->count_max_steps();
	}

	public function load_import_file(){
		$file              = $this->path.'import.json';
		$this->import_data = $this->parse($file);
	}

	public function import_theme_options(){
		$options = $this->load_file('', 'theme_options');
		$options = $this->fix_import_theme_options($options);
		update_option($this->theme, $options);

		/** Elementor */

		$customizer = $this->load_file('', 'customizer');
		if(!is_wp_error($customizer)) {
			Customizer::instance()->set_options($customizer);
		}

		$customizer = $this->load_file('', 'customizer_elementor');
		if(!is_wp_error($customizer)) {
			Customizer\Elementor::instance()->set_setting($customizer);
			Customizer\Elementor::instance()->save_settings();
		} else if(class_exists('Elementor\Plugin')) {
			$doc  = \Elementor\Plugin::instance()->documents;
			$kit  = \Elementor\Plugin::instance()->kits_manager;
			$data = array(
				'editor_post_id' => $kit->get_active_id(),
				'post_status'    => "publish",
				'status'         => "publish",
				'elements'       => array(),
				'settings'       => array(
					'container_width' => array(
						'size'  => 1190,
						'sizes' => [],
						'unit'  => "px",
					),
					'post_status'     => 'publish'
				)

			);

			$doc->ajax_save($data);
		}

	}

	public function available_widgets(){

		global $wp_registered_widget_controls;

		$widget_controls = $wp_registered_widget_controls;

		$available_widgets = array();

		foreach($widget_controls as $widget) {
			if(!empty($widget['id_base']) && !isset($available_widgets[$widget['id_base']])) { // no dupes
				$available_widgets[$widget['id_base']]['id_base'] = $widget['id_base'];
				$available_widgets[$widget['id_base']]['name']    = $widget['name'];
			}
		}

		return apply_filters('radium_theme_import_widget_available_widgets', $available_widgets);

	}

	public function import_widgets(){
		$data = $this->load_file('', 'widgets');

		global $wp_registered_sidebars;

		// Have valid data?
		// If no data or could not decode
		if(empty($data) || !is_array($data)) {
			return;
		}

		// Hook before import
		$data = apply_filters('radium_theme_import_widget_data', $data);

		// Get all available widgets site supports
		$available_widgets = $this->available_widgets();

		// Get all existing widget instances
		$widget_instances = array();
		foreach($available_widgets as $widget_data) {
			$widget_instances[$widget_data['id_base']] = get_option('widget_'.$widget_data['id_base']);
		}

		// Begin results
		$results = array();

		// Loop import data's sidebars
		foreach($data as $sidebar_id => $widgets) {


			// Skip inactive widgets
			// (should not be in export file)
			if('wp_inactive_widgets' == $sidebar_id) {
				continue;
			}

			// Check if sidebar is available on this site
			// Otherwise add widgets to inactive, and say so
			if(isset($wp_registered_sidebars[$sidebar_id])) {
				$sidebar_available    = true;
				$use_sidebar_id       = $sidebar_id;
				$sidebar_message_type = 'success';
				$sidebar_message      = '';
			} else {
				$sidebar_available    = false;
				$use_sidebar_id       = 'wp_inactive_widgets'; // add to inactive if sidebar does not exist in theme
				$sidebar_message_type = 'error';
				$sidebar_message      = __('Sidebar does not exist in theme (using Inactive)', 'radium');
			}

			// Result for sidebar
			$results[$sidebar_id]['name']         = !empty($wp_registered_sidebars[$sidebar_id]['name']) ? $wp_registered_sidebars[$sidebar_id]['name'] : $sidebar_id;
			$results[$sidebar_id]['message_type'] = $sidebar_message_type;
			$results[$sidebar_id]['message']      = $sidebar_message;
			$results[$sidebar_id]['widgets']      = array();

			// Loop widgets
			foreach($widgets as $widget_instance_id => $widget) {

				$fail = false;

				// Get id_base (remove -# from end) and instance ID number
				$id_base            = preg_replace('/-[0-9]+$/', '', $widget_instance_id);
				$instance_id_number = str_replace($id_base.'-', '', $widget_instance_id);

				// Does site support this widget?
				if(!$fail && !isset($available_widgets[$id_base])) {
					$fail                = true;
					$widget_message_type = 'error';
					$widget_message      = __('Site does not support widget', 'radium'); // explain why widget not imported
				}

				// Filter to modify settings before import
				// Do before identical check because changes may make it identical to end result (such as URL replacements)
				$widget = $this->fix_import_widget($widget, $id_base);

				// Does widget with identical settings already exist in same sidebar?
				if(!$fail && isset($widget_instances[$id_base])) {

					// Get existing widgets in this sidebar
					$sidebars_widgets = get_option('sidebars_widgets');
					$sidebar_widgets  = isset($sidebars_widgets[$use_sidebar_id]) ? $sidebars_widgets[$use_sidebar_id] : array(); // check Inactive if that's where will go

					// Loop widgets with ID base
					$single_widget_instances = !empty($widget_instances[$id_base]) ? $widget_instances[$id_base] : array();
					foreach($single_widget_instances as $check_id => $check_widget) {

						// Is widget in same sidebar and has identical settings?
						if(in_array("$id_base-$check_id", $sidebar_widgets) && (array) $widget == $check_widget) {

							$fail                = true;
							$widget_message_type = 'warning';
							$widget_message      = __('Widget already exists', 'radium'); // explain why widget not imported

							break;

						}

					}

				}

				// No failure
				if(!$fail) {

					// Add widget instance
					$single_widget_instances = get_option('widget_'.$id_base); // all instances for that widget ID base, get fresh every time
					$single_widget_instances = !empty($single_widget_instances) ? $single_widget_instances : array( '_multiwidget' => 1 ); // start fresh if have to

					$single_widget_instances[] = $widget; // add it

					// Get the key it was given
					end($single_widget_instances);
					$new_instance_id_number = key($single_widget_instances);

					// If key is 0, make it 1
					// When 0, an issue can occur where adding a widget causes data from other widget to load, and the widget doesn't stick (reload wipes it)
					if('0' === strval($new_instance_id_number)) {
						$new_instance_id_number                           = 1;
						$single_widget_instances[$new_instance_id_number] = $single_widget_instances[0];
						unset($single_widget_instances[0]);
					}

					// Move _multiwidget to end of array for uniformity
					if(isset($single_widget_instances['_multiwidget'])) {
						$multiwidget = $single_widget_instances['_multiwidget'];
						unset($single_widget_instances['_multiwidget']);
						$single_widget_instances['_multiwidget'] = $multiwidget;
					}

					// Update option with new widget
					update_option('widget_'.$id_base, $single_widget_instances);

					// Assign widget instance to sidebar
					$sidebars_widgets = get_option('sidebars_widgets'); // which sidebars have which widgets, get fresh every time
					if(!$sidebars_widgets) {
						$sidebars_widgets = array();
					}

					$new_instance_id                     = $id_base.'-'.$new_instance_id_number; // use ID number from new widget instance
					$sidebars_widgets[$use_sidebar_id][] = $new_instance_id; // add new instance to sidebar
					update_option('sidebars_widgets', $sidebars_widgets); // save the amended data

					// Success message
					if($sidebar_available) {
						$widget_message_type = 'success';
						$widget_message      = __('Imported', 'radium');
					} else {
						$widget_message_type = 'warning';
						$widget_message      = __('Imported to Inactive', 'radium');
					}
				}

				// Result for widget instance
				$results[$sidebar_id]['widgets'][$widget_instance_id]['name']         = isset($available_widgets[$id_base]['name']) ? $available_widgets[$id_base]['name'] : $id_base; // widget name or ID if name not available (not supported by site)
				$results[$sidebar_id]['widgets'][$widget_instance_id]['title']        = !empty($widget['title']) ? $widget['title'] : __('No Title', 'radium'); // show "No Title" if widget instance is untitled
				$results[$sidebar_id]['widgets'][$widget_instance_id]['message_type'] = $widget_message_type;
				$results[$sidebar_id]['widgets'][$widget_instance_id]['message']      = $widget_message;
			}
		}

		// Return results
		return apply_filters('radium_theme_import_widget_results', $results);

	}

	public function import_settings(){
		if(key_exists('home_page', $this->scheduled)) {
			$this->log('Import home page. '.$this->scheduled['home_page'].' -- '.$this->get_real_page_id($this->scheduled['home_page']));

			update_option('page_on_front', $this->get_real_page_id($this->scheduled['home_page']));
			update_option('show_on_front', 'page');
		}

	}

	public function get_real_page_id($page_id){
		return $page_id;
	}

	public function save_attachment_id($post_id, $postmeta){
		$this->set_real_id($post_id, $postmeta['import_id']);
	}

	public function fix_import_post($post, $postdata){
		return $this->fix_elementor_page($post, $postdata);
	}

	public function fix_import_widget($widget, $widget_name){
		if(in_array($widget_name, array( 'media_image' ))) {
			if(is_object($widget) && property_exists($widget, 'attachment_id')) {
				$widget->attachment_id = $this->get_local_id($widget->attachment_id);
				$widget->url           = wp_get_attachment_image_url($widget->attachment_id, 'full');
			}
		}

		return $widget;
	}

	public function fix_import_theme_options($data){
		if(is_array($data) && count($data)) {
			foreach($data as &$value) {
				if(is_array($value)) {
					if(
						key_exists('url', $value)
						&& key_exists('id', $value)
						&& key_exists('thumbnail', $value)
					) {
						$value['id']        = $this->get_local_id($value['id']);
						$value['url']       = wp_get_attachment_image_url($value['id'], 'full');
						$value['thumbnail'] = wp_get_attachment_image_url($value['id'], 'thumbnail');
					} else if(key_exists('background-image', $value)
					          && key_exists('media', $value)
					) {
						$value['media']['id']        = $this->get_local_id($value['media']['id']);
						$value['media']['thumbnail'] = wp_get_attachment_image_url($value['media']['id'], 'thumbnail');
						$value['background-image']   = wp_get_attachment_image_url($value['media']['id'], 'full');
					}
				}
			}
		}

		return $data;
	}

	protected function set_real_id($local, $real){
		$this->processed_attachments[$real] = $local;
	}

	protected function get_local_id($real){
		return key_exists($real, $this->processed_attachments) ? $this->processed_attachments[$real] : $real;
	}

	protected function fix_elementor_page($post, $postdata = array()){
		if(class_exists('Elementor\Plugin')) {
			if(key_exists('postmeta', $post)) {
				foreach($post['postmeta'] as &$post_meta) {
					if(is_array($post_meta) && $post_meta['meta_key'] == '_elementor_data') {
						$meta = json_decode($post_meta['meta_value'], true);
						if(json_last_error()) {
							$meta = array();
						}

						if(is_array($meta) && count($meta)) {
							foreach($meta as &$level_0) {
								$this->fix_elementor_page_images($level_0);
							}
						}
						$post_meta['meta_value'] = wp_json_encode($meta);
					}

					if($post_meta['meta_key'] === '_elementor_css') {
						unset($post_meta);
					}
				}
			}
		}

		return $post;
	}

	protected function fix_elementor_page_images(&$item){
		if(key_exists('elType', $item) && !in_array($item['elType'], array( 'section', 'column' ))) {
			$this->replace_image_id($item);
		}
		if(key_exists('elements', $item) && is_array($item['elements']) && count($item['elements'])) {
			foreach($item['elements'] as &$element) {
				$this->fix_elementor_page_images($element);
			}
		}
	}

	protected function replace_image_id(&$item){
		$widget_controls = $this->get_widget_controls($item['widgetType']);
		if(is_array($widget_controls) && count($widget_controls)) {
			foreach($item['settings'] as $control => &$control_settings) {
				if(in_array($control, $widget_controls)) {
					if(key_exists('id', $control_settings)) {
						$_new_id = $this->get_local_id($control_settings['id']);
						if($_new_id !== $control_settings['id']) {
							$control_settings['id']  = $_new_id;
							$control_settings['url'] = wp_get_attachment_image_url($_new_id, 'full');
						}
					} else {
						foreach($control_settings as &$control_setting) {
							$_new_id = $this->get_local_id($control_setting['id']);
							if($_new_id !== $control_setting['id']) {
								$control_setting['id']  = $_new_id;
								$control_setting['url'] = wp_get_attachment_image_url($_new_id, 'full');
							}
						}
					}
				}
			}
		}
	}

	protected function get_widget_controls($widget = false){
		static $widgets = null;

		if(false === $widget) {
			return $widgets;
		}

		if(null !== $widgets && key_exists($widget, $widgets)) {
			return $widgets[$widget];
		}
		$manager  = Elementor_Plugin::instance()->widgets_manager;
		$_widget  = $manager->get_widget_types($widget);
		$controls = is_null($_widget) ? array() : $_widget->get_controls();

		$controls = array_filter(
			$controls, function($control){
			return (key_exists('type', $control) && in_array($control['type'], $this->elementor_media_controls));
		}
		);

		$widgets[$widget] = array_keys($controls);

		return $widgets[$widget];
	}

	public function process_post_type__DISABLED($post_type){
		foreach($this->posts[$post_type] as $post_id => $post) {
			$this->current_index++;
			$this->global_index++;
			$this->save_states();

			$this->process_post($post);
		}
	}

	protected function get_log_file__DISABLED(){
		return trailingslashit($this->upload_dir).wp_create_nonce(self::$nonce_key).'.json';
	}

	/**
	 * Create new post tags based on import information
	 *
	 * Doesn't create a tag if its slug already exists
	 */

	function process_tags__DISABLED(){
		$this->tags = apply_filters('wp_import_tags', $this->tags);
		$this->log('process_tags');

		if(empty($this->tags)) {
			return;
		}

		foreach($this->tags as $tag_id => $tag) {
			$this->current_index++;
			$this->global_index++;
			$this->save_states();

			if(is_string($tag)) {
				$tag = $this->load_file('taxonomy', $tag_id);
				if(is_wp_error($tag)) {
					$this->log($tag_id);
					$this->log($tag->get_error_message());

					continue;
				}
			}

			// if the tag already exists leave it alone
			$term_id = term_exists($tag['term_slug'], 'post_tag');
			if($term_id) {
				if(is_array($term_id)) {
					$term_id = $term_id['term_id'];
				}
				if(isset($tag['term_id'])) {
					$this->processed_terms[intval($tag['term_id'])] = (int) $term_id;
				}
				continue;
			}

			$tag      = wp_slash($tag);
			$tag_desc = isset($tag['term_description']) ? $tag['term_description'] : '';
			$tagarr   = array( 'slug' => $tag['term_slug'], 'description' => $tag_desc );

			$id = wp_insert_term($tag['term_name'], 'post_tag', $tagarr);
			if(!is_wp_error($id)) {
				if(isset($tag['term_id'])) {
					$this->processed_terms[intval($tag['term_id'])] = $id['term_id'];
				}
			} else {
				$this->log($tag_id);
				$this->log(sprintf(__('Failed to import post tag %s', 'wordpress-importer'), esc_html($tag['term_name'])).' '.$id->get_error_message());
				continue;
			}

			$this->process_termmeta($tag, $id['term_id']);
		}

		unset($this->tags);
	}

	/**
	 * Create new categories based on import information
	 *
	 * Doesn't create a new category if its slug already exists
	 */
	function process_categories__DISABLED(){
		$this->categories = apply_filters('wp_import_categories', $this->categories);

		if(empty($this->categories)) {
			return;
		}

		foreach($this->categories as $cat_id => $cat) {
			$this->current_index++;
			$this->global_index++;
			$this->save_states();

			if(is_string($cat)) {
				$cat = $this->load_file('taxonomy', $cat_id);
				if(is_wp_error($cat)) {
					$this->log('Error cat: '.$cat_id);
					$this->log($cat->get_error_message());
					continue;
				}
			}

			// if the category already exists leave it alone
			$term_id = term_exists($cat['term_slug'], 'category');
			if($term_id) {
				if(is_array($term_id)) {
					$term_id = $term_id['term_id'];
				}
				if(isset($cat['term_id'])) {
					$this->processed_terms[intval($cat['term_id'])] = (int) $term_id;
				}
				continue;
			}

			$category_parent      = empty($cat['term_parent']) ? 0 : category_exists($cat['term_parent']);
			$category_description = isset($cat['term_description']) ? $cat['term_description'] : '';
			$catarr               = array(
				'category_nicename'    => $cat['term_slug'],
				'category_parent'      => $category_parent,
				'cat_name'             => $cat['term_name'],
				'category_description' => $category_description
			);
			$catarr               = wp_slash($catarr);

			$id = wp_insert_category($catarr);
			if(!is_wp_error($id)) {
				if(isset($cat['term_id'])) {
					$this->processed_terms[intval($cat['term_id'])] = $id;
				}
			} else {
				$this->log('Error cat: '.$term_id);
				$this->log(sprintf(__('Failed to import category %s', 'wordpress-importer'), esc_html($cat['term_slug'])).' '.$id->get_error_message());
				continue;
			}

			$this->process_termmeta($cat, $id['term_id']);
		}

		unset($this->categories);
	}

	protected function process_woo__DISABLED(){
		return;
		global $wpdb;

		if(!defined('WC_ABSPATH')) {
			return;
		}

		include_once WC_ABSPATH.'includes/admin/importers/class-wc-product-csv-importer-controller.php';
		include_once WC_ABSPATH.'includes/import/class-wc-product-csv-importer.php';

		$file   = $this->path.'wc-import.csv';
		$params = array(
			'delimiter'       => ',', // PHPCS: input var ok.
			'start_pos'       => 0, // PHPCS: input var ok.
			'mapping'         => array(), // PHPCS: input var ok.
			'update_existing' => false, // PHPCS: input var ok.
			'lines'           => apply_filters('woocommerce_product_import_batch_size', 30),
			'parse'           => true,
		);

		// Log failures.
		if(0 !== $params['start_pos']) {
			$error_log = array_filter((array) get_user_option('product_import_error_log'));
		} else {
			$error_log = array();
		}

		$importer  = \WC_Product_CSV_Importer_Controller::get_importer($file, $params);
		$maps      = $importer->get_mapped_keys();
		$maps_raw  = $importer->get_raw_keys();
		$results   = $importer->import();
		$error_log = array_merge($error_log, $results['failed'], $results['skipped']);

		update_user_option(get_current_user_id(), 'product_import_error_log', $error_log);

		$max  = 30;
		$curr = 0;
		while((float) $importer->get_percent_complete() < (float) 100 && $max > $curr) {
			echo $importer->get_percent_complete().'%';
			echo '<br/>';
			sleep(1);
			$curr++;
		}

//		var_dump($results, $importer->import());

		return;

		// @codingStandardsIgnoreStart.
		$wpdb->delete($wpdb->postmeta, array( 'meta_key' => '_original_id' ));
		$wpdb->delete(
			$wpdb->posts, array(
				'post_type'   => 'product',
				'post_status' => 'importing',
			)
		);
		$wpdb->delete(
			$wpdb->posts, array(
				'post_type'   => 'product_variation',
				'post_status' => 'importing',
			)
		);
		// @codingStandardsIgnoreEnd.

		// Clean up orphaned data.
		$wpdb->query(
			"
				DELETE {$wpdb->posts}.* FROM {$wpdb->posts}
				LEFT JOIN {$wpdb->posts} wp ON wp.ID = {$wpdb->posts}.post_parent
				WHERE wp.ID IS NULL AND {$wpdb->posts}.post_type = 'product_variation'
			"
		);
		$wpdb->query(
			"
				DELETE {$wpdb->postmeta}.* FROM {$wpdb->postmeta}
				LEFT JOIN {$wpdb->posts} wp ON wp.ID = {$wpdb->postmeta}.post_id
				WHERE wp.ID IS NULL
			"
		);
		// @codingStandardsIgnoreStart.
		$wpdb->query(
			"
				DELETE tr.* FROM {$wpdb->term_relationships} tr
				LEFT JOIN {$wpdb->posts} wp ON wp.ID = tr.object_id
				LEFT JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				WHERE wp.ID IS NULL
				AND tt.taxonomy IN ( '".implode("','", array_map('esc_sql', get_object_taxonomies('product')))."' )
			"
		);
		// @codingStandardsIgnoreEnd.
	}

	protected function respondOK__DISABLED($msg = null){
		ignore_user_abort(true);
		set_time_limit(0);

		ob_start();
		if(null !== $msg) {
			echo json_encode($msg);
		}
		header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
		header('Content-Encoding: none');
		header('Content-Length: '.ob_get_length());
		header('Connection: close');

		while(ob_get_level()) {
			ob_end_flush();
		}
		ob_flush();
		flush();
	}

	public function rest_status__DISABLED(){
		$this->load_import_file();
		$this->load_scheduled_file();
		$this->load_states();

		return rest_ensure_response($this->get_current_status());
	}

	/**
	 * The main controller for the actual import stage.
	 *
	 * @param string $file Path to the WXR file for importing
	 */
	public function import__DISABLED(){
		if(is_wp_error($this->import_data)) {
			return;
		}

		return;
		ob_implicit_flush(1);
		add_filter('import_post_meta_key', array( $this, 'is_valid_meta_key' ));
		add_filter('http_request_timeout', array( &$this, 'bump_request_timeout' ));

		$this->log('Import start');
		wp_suspend_cache_invalidation(true);

		$this->process_categories();
		$this->process_tags();
		$this->process_terms();
		$this->process_attachments();
		$this->process_posts();
		$this->process_menus();
//		$this->process_woo();
		wp_suspend_cache_invalidation(false);

		// update incorrect/missing information in the DB
		$this->backfill_parents();
		$this->backfill_attachment_urls();
		$this->remap_featured_images();

		$this->import_theme_options();
		$this->import_widgets();
		$this->import_settings();


//		$this->import_end();
	}
}
