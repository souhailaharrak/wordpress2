<?php

require_once get_template_directory() . '/core/tgm/class-tgm-plugin-activation.php';

add_action('tgmpa_register', 'gt3_register_required_plugins');
function gt3_register_required_plugins()
{

    /**
     * Array of plugin arrays. Required keys are name and slug.
     * If the source is NOT from the .org repo, then source is also required.
     */
    $path =  get_template_directory();
    $plugins = array(
        array(
            'name'               => esc_html__('GT3 Themes Core', 'ewebot' ), // The plugin name.
            'slug'               => 'gt3-themes-core', // The plugin slug (typically the folder name).
            'source'             => $path. '/core/tgm/plugins/gt3-themes-core.zip', // The plugin source.
            'required'           => true, // If false, the plugin is only 'recommended' instead of required.
            'version'            => '1.6.7', // E.g. 1.0.0. If set, the active plugin must be this version or higher.
            'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
            'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
        ),
        array(
            'name'              => esc_html__('Elementor Page Builder', 'ewebot' ),
            'slug'              => 'elementor',
            'required'          => false,
        ),
        array(
            'name'              => esc_html__('Contact Form 7', 'ewebot' ),
            'slug'              => 'contact-form-7',
            'required'          => false,
        ),
        array(
            'name'              => esc_html__('MC4WP: Mailchimp for WordPress', 'ewebot' ),
            'slug'              => 'mailchimp-for-wp',
            'required'          => false,
        ),
        array(
            'name'         => esc_html__('WooCommerce', 'ewebot' ),
            'slug'         => 'woocommerce',
            'required'       => false,
        ),
        array(
            'name'               => esc_html__('Revolution Slider', 'ewebot' ), // The plugin name.
            'slug'               => 'revslider', // The plugin slug (typically the folder name).
            'source'             => $path. '/core/tgm/plugins/revslider.zip', // The plugin source
            'required'           => true, // If false, the plugin is only 'recommended' instead of required.
            'version'            => '6.5.16', // E.g. 1.0.0. If set, the active plugin must be this version or higher.
            'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
            'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
        ),
        array(
            'name'               => esc_html__('GT3 Ultimate Charts for Elementor Page Builder', 'ewebot' ),
            'slug'               => 'gt3-elementor-unlimited-charts',
            'source'             => $path. '/core/tgm/plugins/gt3-elementor-unlimited-charts.zip',
            'required'           => false,
            'version'            => '1.0.3',
            'force_activation'   => false,
            'force_deactivation' => false,
        ),
	    array(
		    'name'               => esc_html__('WP Daddy Builder Pro', 'ewebot' ), // The plugin name.
		    'slug'               => 'wpda-builder-pro', // The plugin slug (typically the folder name).
		    'source'             => $path. '/core/tgm/plugins/wpda-builder-pro.zip', // The plugin source
		    'required'           => true, // If false, the plugin is only 'recommended' instead of required.
		    'version'            => '1.2.6', // E.g. 1.0.0. If set, the active plugin must be this version or higher.
		    'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
		    'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
	    ),
	    array(
		    'name'         => esc_html__('Photo Gallery by GT3 – Video Gallery & Gutenberg Block Gallery', 'ewebot' ),
		    'slug'         => 'gt3-photo-video-gallery',
		    'required'       => false,
	    ),
	    array(
		    'name'               => esc_html__('GT3 Photo & Video Gallery - Pro', 'ewebot' ), // The plugin name.
		    'slug'               => 'gt3-photo-video-gallery-pro', // The plugin slug (typically the folder name).
		    'source'             => $path. '/core/tgm/plugins/gt3-photo-video-gallery-pro.zip', // The plugin source.
		    'required'           => false, // If false, the plugin is only 'recommended' instead of required.
		    'version'            => '1.7.1.1', // E.g. 1.0.0. If set, the active plugin must be this version or higher.
		    'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
		    'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
	    ),
    );

	/* Modern Shop */
	if (gt3_option('modern_shop') == '1' || true === gt3_option('modern_shop')) {
		$modern_shop_plugins = array(
			array(
				'name'         => esc_html__('YITH WooCommerce Wishlist', 'ewebot' ),
				'slug'         => 'yith-woocommerce-wishlist',
				'required'       => false,
			),
			array(
				'name'         => esc_html__('YITH WooCommerce Quick View', 'ewebot' ),
				'slug'         => 'yith-woocommerce-quick-view',
				'required'       => false,
			),
			array(
				'name'         => esc_html__('Advanced AJAX Product Filters', 'ewebot' ),
				'slug'         => 'woocommerce-ajax-filters',
				'required'       => false,
			),
			array(
				'name'         => esc_html__('Variation Swatches for WooCommerce', 'ewebot' ),
				'slug'         => 'woo-variation-swatches',
				'required'       => false,
			),

		);
		$plugins = array_merge($plugins, $modern_shop_plugins);
	}
	/* Modern Shop End */

    $plugins_autoupdate = get_option( 'gt3_plugins' );
    foreach ($plugins as $plugin => $plugin_array) {
        $slug = $plugin_array['slug'];
        if (!empty($plugins_autoupdate) && is_array($plugins_autoupdate) && array_key_exists($slug,$plugins_autoupdate)) {
            if (!empty($plugins_autoupdate[$slug]['version']) && version_compare( $plugin_array['version'], $plugins_autoupdate[$slug]['version'], '<')) {
                $plugins[$plugin]['version'] = $plugins_autoupdate[$slug]['version'];
                $plugins[$plugin]['source'] = $plugins_autoupdate[$slug]['source'];
            }
        }
    }

    /**
     * Array of configuration settings. Amend each line as needed.
     * If you want the default strings to be available under your own theme domain,
     * leave the strings uncommented.
     * Some of the strings are added into a sprintf, so see the comments at the
     * end of each line for what each argument will be.
     */
    $config = array(
        'default_path' => '',                       // Default absolute path to pre-packaged plugins.
        'menu'         => 'tgmpa-install-plugins',  // Menu slug.
        'has_notices'  => true,                     // Show admin notices or not.
        'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
        'dismiss_msg'  => '',                       // If 'dismissable' is false, this message will be output at top of nag.
        'is_automatic' => true,                     // Automatically activate plugins after installation or not.
        'message'      => '',                       // Message to output right before the plugins table.
        'strings'      => array(
            'page_title'                      => esc_html__( 'Install Required Plugins', 'ewebot' ),
            'menu_title'                      => esc_html__( 'Install Plugins', 'ewebot' ),
            'installing'                      => esc_html__( 'Installing Plugin: %s', 'ewebot' ), // %s = plugin name.
            'oops'                            => esc_html__( 'Something went wrong with the plugin API.', 'ewebot' ),
            'notice_can_install_required'     => esc_html__( 'This theme requires the following plugins: %1$s.', 'ewebot' ), // %1$s = plugin name(s).
            'notice_can_install_recommended'  => esc_html__( 'This theme recommends the following plugins: %1$s.', 'ewebot' ), // %1$s = plugin name(s).
            'notice_cannot_install'           => esc_html__( 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.', 'ewebot' ), // %1$s = plugin name(s).
            'notice_can_activate_required'    => esc_html__( 'The following required plugins are currently inactive: %1$s.', 'ewebot' ), // %1$s = plugin name(s).
            'notice_can_activate_recommended' => esc_html__( 'The following recommended plugins are currently inactive: %1$s.', 'ewebot' ), // %1$s = plugin name(s).
            'notice_cannot_activate'          => esc_html__( 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.', 'ewebot' ), // %1$s = plugin name(s).
            'notice_ask_to_update'            => esc_html__( 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.', 'ewebot' ), // %1$s = plugin name(s).
            'notice_cannot_update'            => esc_html__( 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.', 'ewebot' ), // %1$s = plugin name(s).
            'install_link'                    => esc_html__( 'Begin installing plugins', 'ewebot' ),
            'activate_link'                   => esc_html__( 'Begin activating plugins', 'ewebot' ),
            'return'                          => esc_html__( 'Return to Required Plugins Installer', 'ewebot' ),
            'plugin_activated'                => esc_html__( 'Plugin activated successfully.', 'ewebot' ),
            'complete'                        => esc_html__( 'All plugins installed and activated successfully. %s', 'ewebot' ), // %s = dashboard link.
            'nag_type'                        => 'updated' // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
        )
    );

    tgmpa( $plugins, $config );

}
