<?php

use \GT3\ThemesCore\Customizer;


Customizer::add_section(
	'theme_blog', array(
		'title' => esc_html__('Blog', 'ewebot'),
	)
);

Customizer::add_field(
	'blog_title_conditional',
	array(
		'label'         => esc_html__( 'Show Post Title', 'ewebot' ),
		'type'          => Customizer::Toggle_Control,
		'settings_args' => array(
			'sanitize_callback'    => array( Customizer::Toggle_Control, 'sanitize' ),
			'sanitize_js_callback' => array( Customizer::Toggle_Control, 'sanitize' ),
		),
		'conditions'    => array(
			array(
				'field' => 'page_title_conditional',
				'type'  => 'bool',
				'value' => true,
			),
		)
	)
);

Customizer::add_field(
	'related_posts',
	array(
		'label'         => esc_html__( 'Related Posts Section', 'ewebot' ),
		'type'          => Customizer::Toggle_Control,
		'settings_args' => array(
//			'transport' => 'refresh',
			'sanitize_callback'    => array( Customizer::Toggle_Control, 'sanitize' ),
			'sanitize_js_callback' => array( Customizer::Toggle_Control, 'sanitize' ),
		)
	)
);

Customizer::add_field(
	'related_posts_filter', array(
		'type'          => Customizer::Select_Control,
		'label'         => esc_html__('Order Related Posts by', 'ewebot'),
		'choices'       => array(
			'tag' => esc_html__('Tag', 'ewebot'),
			'category' => esc_html__('Category', 'ewebot'),
		),
		'settings_args' => array(
			'transport' => 'refresh',
			'capability' => 'edit_theme_options',
		),
		'conditions'    => array(
			array(
				'field' => 'related_posts',
				'type'  => 'bool',
				'value' => true,
			),
		)
	)
);

Customizer::add_field(
	'author_box',
	array(
		'label'         => esc_html__( 'Author Info Box', 'ewebot' ),
		'type'          => Customizer::Toggle_Control,
		'settings_args' => array(
			'sanitize_callback'    => array( Customizer::Toggle_Control, 'sanitize' ),
			'sanitize_js_callback' => array( Customizer::Toggle_Control, 'sanitize' ),
		)
	)
);

Customizer::add_field(
	'post_comments',
	array(
		'label'         => esc_html__( 'Comments in Single Post', 'ewebot' ),
		'type'          => Customizer::Toggle_Control,
		'settings_args' => array(
			'sanitize_callback'    => array( Customizer::Toggle_Control, 'sanitize' ),
			'sanitize_js_callback' => array( Customizer::Toggle_Control, 'sanitize' ),
		)
	)
);

Customizer::add_field(
	'blog_post_likes',
	array(
		'label'         => esc_html__( 'Likes in Posts', 'ewebot' ),
		'type'          => Customizer::Toggle_Control,
		'settings_args' => array(
			'sanitize_callback'    => array( Customizer::Toggle_Control, 'sanitize' ),
			'sanitize_js_callback' => array( Customizer::Toggle_Control, 'sanitize' ),
		)
	)
);

Customizer::add_field(
	'blog_post_share',
	array(
		'label'         => esc_html__( 'Shares in Posts', 'ewebot' ),
		'type'          => Customizer::Toggle_Control,
		'settings_args' => array(
			'sanitize_callback'    => array( Customizer::Toggle_Control, 'sanitize' ),
			'sanitize_js_callback' => array( Customizer::Toggle_Control, 'sanitize' ),
		)
	)
);

Customizer::add_field(
	'blog_post_listing_content',
	array(
		'label'         => esc_html__( 'Shorten Post Text in Blog Listing', 'ewebot' ),
		'type'          => Customizer::Toggle_Control,
		'settings_args' => array(
			'transport' => 'refresh',
			'sanitize_callback'    => array( Customizer::Toggle_Control, 'sanitize' ),
			'sanitize_js_callback' => array( Customizer::Toggle_Control, 'sanitize' ),
		)
	)
);

Customizer::add_field(
	'post_pingbacks',
	array(
		'label'         => esc_html__( 'Trackbacks and Pingbacks', 'ewebot' ),
		'type'          => Customizer::Toggle_Control,
		'settings_args' => array(
			'transport' => 'refresh',
			'sanitize_callback'    => array( Customizer::Toggle_Control, 'sanitize' ),
			'sanitize_js_callback' => array( Customizer::Toggle_Control, 'sanitize' ),
		)
	)
);
