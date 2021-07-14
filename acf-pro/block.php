<?php
/**
 * Created by IntelliJ IDEA.
 * User: stephen
 * Date: 2021-06-22
 * Time: 4:30 PM
 */

/**
 * Class ucf_health_doctor_ribbon_acf_pro_admin_fields
 * Creates an admin page to let the site admin define site-wide options for embedded google maps. Namely, the api js
 * key.
 */

namespace ucf_health_doctor_ribbon\acf_pro\block;

// create a block, then add ACF fields for options for the block
add_action( 'acf/init', __NAMESPACE__ . '\\create_block' );
add_action( 'acf/init', __NAMESPACE__ . '\\create_fields' );

const block_slug = 'ucf-health-doctor-ribbon';

function create_block() {
	if ( function_exists( 'acf_register_block' ) ) {
		acf_register_block(
			array(
				'name'            => block_slug,
				'title'           => __( 'UCF Health Doctor Ribbon' ),
				'description'     => __( 'Ribbon with info about a specific doctor.' ),
				'render_callback' => 'ucf_health_doctor_ribbon\\replacement_print',
				'category'        => 'embed',
				'icon'            => 'id',
				'keywords'        => array(
					'ucf',
					'college',
					'people',
					'directory',
					'profile',
					'person'
				),
				//'enqueue_assets'  => 'ucf_health_doctor_ribbon\\enqueue_files',
			)
		);
	}
}

function create_fields() {

	if ( function_exists( 'acf_add_local_field_group' ) ) {
		acf_add_local_field_group(
			array(
				'key'                   => 'group_60ec940598c5f',
				'title'                 => 'Doctor ribbon',
				'fields'                => array(
					array(
						'key'               => 'field_60ec940ed402d',
						'label'             => 'Doctor',
						'name'              => 'doctor',
						'type'              => 'post_object',
						'instructions'      => '',
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'post_type'         => array(
							0 => 'person',
						),
						'taxonomy'          => '',
						'allow_null'        => 0,
						'multiple'          => 0,
						'return_format'     => 'object',
						'ui'                => 1,
					),
					array(
						'key' => 'field_60ecaa5ab7305',
						'label' => 'Buttons',
						'name' => 'content',
						'type' => 'wysiwyg',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'tabs' => 'all',
						'toolbar' => 'full',
						'media_upload' => 1,
						'delay' => 0,
					),
					array(
						'key'               => 'field_60ec942ed402e',
						'label'             => 'Toggle Advanced Options',
						'name'              => 'toggle_advanced_options',
						'type'              => 'true_false',
						'instructions'      => '',
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'message'           => '',
						'default_value'     => 0,
						'ui'                => 1,
						'ui_on_text'        => '',
						'ui_off_text'       => '',
					),
					array(
						'key'               => 'field_60ec944dd402f',
						'label'             => 'Advanced Options',
						'name'              => 'advanced_options',
						'type'              => 'group',
						'instructions'      => '',
						'required'          => 0,
						'conditional_logic' => array(
							array(
								array(
									'field'    => 'field_60ec942ed402e',
									'operator' => '==',
									'value'    => '1',
								),
							),
						),
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'layout'            => 'block',
						'sub_fields'        => array(
							array(
								'key'               => 'field_60ec9471d4030',
								'label'             => 'Hide languages',
								'name'              => 'hide_languages',
								'type'              => 'true_false',
								'instructions'      => 'Activate to HIDE the language column',
								'required'          => 0,
								'conditional_logic' => 0,
								'wrapper'           => array(
									'width' => '',
									'class' => '',
									'id'    => '',
								),
								'message'           => '',
								'default_value'     => 0,
								'ui'                => 1,
								'ui_on_text'        => 'Hidden',
								'ui_off_text'       => 'Visible',
							),
							array(
								'key'               => 'field_60ec94d1d4031',
								'label'             => 'Hide buttons',
								'name'              => 'hide_buttons',
								'type'              => 'true_false',
								'instructions'      => 'Activate to HIDE the button column',
								'required'          => 0,
								'conditional_logic' => 0,
								'wrapper'           => array(
									'width' => '',
									'class' => '',
									'id'    => '',
								),
								'message'           => '',
								'default_value'     => 0,
								'ui'                => 1,
								'ui_on_text'        => 'Hidden',
								'ui_off_text'       => 'Visible',
							),
						),
					),
				),
				'location'              => array(
					array(
						array(
							'param'    => 'block',
							'operator' => '==',
							'value'    => 'acf/' . block_slug,

						),
					),
				),
				'menu_order'            => 0,
				'position'              => 'normal',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen'        => '',
				'active'                => true,
				'description'           => '',
			)
		);

	}
}