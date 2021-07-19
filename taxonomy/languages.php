<?php

namespace ucf_health_doctor_ribbon\taxonomy\languages;

add_action('init', __NAMESPACE__ . '\\register_language', 100); // priority must be later/higher than the ucf-people-cpt plugin in order to add and extend the people post type

/**
 * Registers the 'languages' taxonomy and assigns it to the 'person' post type.
 * If languages is already defined, does nothing.
 * Languages should ideally be defined via the theme, but it is utilized in this plugin as well,
 * so we define it ourselves if needed.
 */
function register_language() {
	if (!taxonomy_exists('languages')) {
		register_taxonomy(
			'languages', // name/slug of taxonomy
			null, // don't set custom taxonomies for custom post types; link them later with register_taxonomy_for_object_type()
			array(
				'labels'            => array(
					'name'          => __( 'Languages' ),
					'singular_name' => __( 'Language' )
				),
				'hierarchical'      => true,
				'query_var'         => false, // don't create subpages based on the taxonomy slugs
				'rewrite'           => false, // same as above
				'public'            => false,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_nav_menus' => true,
			)
		);

		if ( post_type_exists( 'person' ) ) {
			// link our custom taxonomies and custom post types
			// Better safe than sorry when registering custom taxonomies for custom post types:
			// http://codex.wordpress.org/Function_Reference/register_taxonomy#Usage
			register_taxonomy_for_object_type( 'languages', 'person' );
		}
	}

}
