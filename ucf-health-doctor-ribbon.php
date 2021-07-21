<?php
/*
Plugin Name: UCF Health Doctor Ribbon
Plugin URI: https://github.com/schrauger/ucf-health-doctor-ribbon
Description: Block to display a ribbon object with doctor information. Also adds the languages taxonomy and a column to the single-person output.
Version: 0.4
Author: Stephen Schrauger
Author URI: https://www.schrauger.com/
License: GPLv2 or later
*/

namespace ucf_health_doctor_ribbon;

include_once plugin_dir_path( __FILE__ ) . 'acf-pro/block.php';
include_once plugin_dir_path( __FILE__ ) . 'taxonomy/languages.php';

/**
 * Prints out the ribbon
 */
function replacement_print() {
	echo replacement();
}

/**
 * Returns the ribbon html
 * @return string
 */
function replacement() {
	return get_ribbon_content();
}

/**
 * Adds the map and location html to the current page
 * @return string HTML with location map object (which is empty until javascript generates the map on the fly),
 *                as well as the selector list with detailed location information
 */
function get_ribbon_content() {

	// first column

	$doctor = null;
	if ( get_field( 'doctor' ) ) {
		$doctor = get_field( 'doctor' );
		/**
		 * @var \WP_Post $doctor
		 */
		$doctor_name        = $doctor->post_title;
		$doctor_image_array = wp_get_attachment_image_src( get_post_thumbnail_id( $doctor->ID ) ); //@TODO remove hardcode sizing css in div
		$doctor_image_url   = $doctor_image_array[ 0 ];
	} else {
		$doctor_name      = "Choose a Doctor";
		$doctor_image_url = "/wp-content/uploads/2016/10/UCF-Health-logo.jpg";
	}

	$column_1 = "
	<div class='wp-block-column'>

		<figure class='wp-block-image size-large is-resized is-style-rounded'><img src='{$doctor_image_url}' alt='{$doctor_name}' class='wp-image-1592' width='120' height='120'></figure>

		<div class='name'>
			<strong>{$doctor_name}</strong>
		</div>
	</div>
	";


	if ($doctor){
		// check advanced options to see if we should hide any of the columns
		$show_column_2 = true;
		$show_column_3 = true;
		if (get_field('toggle_advanced_options')){
			while (have_rows('advanced_options')){
				the_row();
				$show_column_2 = !(get_sub_field('hide_languages')); // invert the value (if checked, then hide)
				$show_column_3 = !(get_sub_field('hide_buttons'));
			}
		}
	} else {
		// check advanced options to see if we should hide any of the columns
		$show_column_2 = false;
		$show_column_3 = false;
	}

	// second column
	$column_2 = "";
	if ($show_column_2) {

		$doctor_languages_array = wp_get_post_terms( $doctor->ID, 'languages' );

		$language_list = "";
		foreach ( $doctor_languages_array as $language ) {
			/**
			 * @var \WP_Term $language
			 */

			$language_list .= "
		<li>
			{$language->name}
		</li>
		";
		}


		$column_2 = "
		<div class='wp-block-column'>
			<div class='languages'>
				<h5>Languages Spoken</h5>
				<ul>
					{$language_list}
				</ul>
			</div>
		</div>
		";
	}

	// third column
	$column_3 = "";
	if ($show_column_3) {

		//$user_content = get_field('content');
		$user_content = get_field( 'uchf_schedulereview_buttons', $doctor->ID );

		$column_3 = "
		<div class='wp-block-column'>
			<div class='user-content'>
				{$user_content}
			</div>       
		</div>
		";
	}


	$return_html = "
		<div class='alert alert-info doctor-ribbon' role='information'>
			<div class='wp-block-columns'>
				{$column_1}
				{$column_2}
				{$column_3}
			</div>
		</div>
	";

	return $return_html;
}

/**
 * Creates the list item for a specific location. This is shown in a <ul> on the locations page.
 *
 * @param $location_array
 * @param $is_selected boolean If true, marks this tab as active
 *
 * @return string
 */
function selector_panel_list_tab( $location_array, $is_selected = false ) {
	$location           = json_decode( json_encode( $location_array ) );
	$is_selected_string = $is_selected ? 'true' : 'false'; // convert boolean to string for js
	$is_active_string   = $is_selected ? 'active' : ''; // convert boolean to string for js
	$tab                = "
		<li class='nav-item'>
			<a 
			class='nav-link {$is_active_string}' 
			id='tab-{$location->slug}-tab' 
			data-toggle='tab' 
			href='#tab-{$location->slug}-content' 
			role='tab' 
			aria-controls='tab-{$location->slug}-content' 
			aria-selected='{$is_selected_string}'
			data-location='{$location->slug}'
			>
				{$location->name}
			</a>
		</li>
	";

	//$tab .= var_export($location_array, true);

	return $tab;
	//return "<li class='locations {$location->slug}' data-location='{$location->slug}'><div class='location location-{$i}'></div><a href='#'>{$location->name}</a></li>";

}

/**
 * Creates the list item for a specific location. This is shown in a <ul> on the locations page.
 *
 * @param $location_array
 * @param $is_selected boolean If true, marks this tab as active
 *
 * @return string
 */
function selector_panel_list_info( $location_array, $is_selected = false ) {
	$location = json_decode( json_encode( $location_array ) );

	$address = "";
	if ( $location->address ) {
		$address .= "			
			<strong>Address:</strong><br />
			<p>" . nl2br( $location->address ) . "</p>
			<a 
			href='" . get_directions( $location ) . "' 
			class='green map location' 
			target='_blank'
			>
				Google Maps
			</a>
			<a 
			href='" . get_directions_apple( $location ) . "' 
			class='green map nomarker location ' 
			target='_blank'
			>
				Apple iOS Maps
			</a>
			";
		if ( $location->written_directions_pdf_file ) {
			$address .= "
			<a 
			href='{$location->written_directions_pdf_file}' 
			class='green map nomarker location ' 
			target='_blank'
			>
				PDF Directions
			</a>
			";
		}
	}


	$phone = "";
	if ( $location->phone_number ) {
		$phone .= "
			<strong>Phone:</strong><br />
			<p>" . nl2br( stripslashes( $location->phone_number ) ) . "</p>
			";
	}
	if ( $location->fax_number ) {
		$phone .= "
			<strong>Fax:</strong><br />
			<p>" . nl2br( $location->fax_number ) . "</p>
			";
	}

	$hours = "";
	if ( $location->hours_of_operation ) {
		$hours .= "
			<strong>Hours:</strong></br>
			<p>" . nl2br( $location->hours_of_operation ) . "</p>
			<p class='notice' >If you have a medical emergency, call 911.</p >
			";
	}

	$extra_classes = "";
	if ( $is_selected ) {
		$extra_classes .= " show active ";
	}


	$tab_content = "";
	$tab_content .= "
		<div 
		class='tab-pane fade {$extra_classes}' 
		id='tab-{$location->slug}-content' 
		role='tabpanel' 
		aria-labelledby='tab-{$location->slug}-tab'
		>
			<div 
			id='tab-{$location->slug}-pininfo' 
			class='tab-{$location->slug}-pininfo info' 
			data-location='{$location->slug}'
			>
				<ul class=''>
					<div class='third'>
						<h2>" . nl2br( $location->name ) . "</h2>
						{$address}
					</div>
					<div class='third'>
						{$phone}
					</div>
					<div class='third'>
						{$hours}
					</div>
				</ul>
			</div>
		</div>
	";

	return $tab_content;
}

/**
 * Returns a url to google maps with the destination filled out.
 *
 * @param $location Object with address, latitude, and longitude members.
 *
 * @return string href to google maps
 */
function get_directions( $location ) {
	return directions_base_url . urlencode( str_replace( "\n", ', ', $location->address ) ) // change newlines into comma+space so google maps can process it properly
	       . '/@' . $location->latitude . ',' . $location->longitude . ',17z/';
	//  https://www.google.com/maps/dir//6850+Lake+Nona+Blvd,+Orlando,+FL+32827/@28.3676791,-81.2850738,17z/
}

function get_directions_apple( $location ) {
	return directions_apple_base_url
	       . '&ll=' . $location->latitude . ',' . $location->longitude
	       . '&sll=' . $location->latitude . ',' . $location->longitude
	       . '&daddr=' . $location->latitude . ',' . $location->longitude;
	//  https://www.google.com/maps/dir//6850+Lake+Nona+Blvd,+Orlando,+FL+32827/@28.3676791,-81.2850738,17z/
}


?>
