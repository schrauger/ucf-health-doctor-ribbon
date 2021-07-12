<?php
/*
Plugin Name: UCF Health Doctor Ribbon
Plugin URI: https://github.com/schrauger/ucf-health-doctor-ribbon
Description: Block to display a ribbon object with doctor information
Version: 0.1
Author: Stephen Schrauger
Author URI: https://www.schrauger.com/
License: GPLv2 or later
*/

namespace ucf_health_doctor_ribbon;

include_once plugin_dir_path( __FILE__ ) . 'acf-pro/block.php';
include_once plugin_dir_path( __FILE__ ) . 'taxonomy/languages.php';

function replacement_print() {
	echo replacement();
}

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

	if ( get_field( 'doctor' ) ) {
		$doctor = get_field( 'doctor' );
		/**
		 * @var \WP_Post $doctor
		 */
		$doctor_name        = $doctor->post_title;
		$doctor_image_array = wp_get_attachment_image_src( get_post_thumbnail_id( $doctor->ID ) ); //@TODO remove hardcode sizing css in div
		$doctor_image_url   = $doctor_image_array[ 0 ];
	} else {
		$doctor_name      = "Choose a doctor";
		$doctor_image_url = "/wp-content/uploads/2016/10/UCF-Health-logo.jpg";
	}

	$column_1 = "
	<div class='row'>
		<div class='col'>
			<div 
			class='image'
			style='background-image: url(\"{$doctor_image_url}\"); width: 100px; height: 100px;'
			>
			</div>
			<div
			class='name'
			>
				{$doctor_name}
			</div>
		</div>
	</div>
	";


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
		<div class='row'>
			<div class='col'>
				<div class='languages'>
					<ul>
						{$language_list}
					</ul>
				</div>
				
			</div>
		</div>
		";
	}

	// third column
	$column_3 = "";
	if ($show_column_3) {

		$user_content = get_field('content');

		$column_3 = "
		<div class='row'>
			<div class='col'>
				<div class='user-content'>
					{$user_content}
				</div>
			</div>
		           
		</div>
		";

	}
	$return_html = "
		<div class='container'>
			{$column_1}
			{$column_2}
			{$column_3}
		</div>
	";

	return $return_html;
}

function oldstuff() {
	/*
	* Visible list of locations.
	*/
	$selector_panel_tabs = '';
	$selector_panel_info = '';


	// Get all the pins for the map
	$pins       = array();
	$i          = 0;
	$show_first = true; // set to false to hide all details by default. true to show the first one.
	while ( have_rows( 'pin_locations' ) ) {
		the_row();

		$pin_info                  = array();
		$pin_info[ 'name' ]        = get_sub_field( 'name' );
		$pin_info[ 'description' ] = get_sub_field( 'description' );
		//$pin_info[ 'phone_number' ]             = get_sub_field( 'phone_numbers' ); // @TODO this is a repeater
		$pin_info[ 'hours_of_operation' ] = get_sub_field( 'hours_of_operation' );
		//$pin_info[ 'coordinates' ]              = get_sub_field( 'coordinates' ); // @TODO this is a group
		$pin_info[ 'address' ]                  = get_sub_field( 'address' );
		$pin_info[ 'url' ]                      = get_sub_field( 'url' );
		$pin_info[ 'written_directs_pdf_file' ] = get_sub_field( 'written_directs_pdf_file' );
		//$pin_info[''] = get_sub_field('');

		while ( have_rows( 'phone_number' ) ) {
			the_row();
			$type                                = get_sub_field( 'type' );
			$number                              = get_sub_field( 'number' );
			$pin_info[ 'phone_number' ][ $type ] = $number;
		}

		// coordinates are in a group, which also needs to be looped even though it isn't a repeater
		while ( have_rows( 'coordinates' ) ) {
			the_row();
			$pin_info[ 'latitude' ]  = get_sub_field( 'latitude' );
			$pin_info[ 'longitude' ] = get_sub_field( 'longitude' );

		}


		$pin_info[ 'slug' ] = 'ucfh-' . md5( json_encode( $pin_info ) );
		// use md5 to create a unique id that only changes when the pin data changes - for caching and unique id in html
		// note: ids MUST start with a letter, so prefix the md5 to prevent erros

		$pins[ $pin_info[ 'slug' ] ] = $pin_info;

		// 4. Create an always-visible list entry (outside of the google map interface)

		if ( $i === 0 && $show_first ) {
			$show_current = true;
		} else {
			$show_current = false;
		}

		$selector_panel_tabs .= selector_panel_list_tab( $pin_info, $show_current );
		$selector_panel_info .= selector_panel_list_info( $pin_info, $show_current );

		$i ++;
	}

	$unique_id_all_data = 'ucfh-' . md5( json_encode( $pins ) );
	// generate another unique id for the parent object. this way, a page with multiple blocks won't interfere with one another.
	// note: ids MUST start with a letter, so prefix the md5 to prevent erros

	if ( get_field( 'panel_visible' ) ) {
		$selector_panel = "
			<div class='info selector-panel locations' >
				<ul class='nav nav-tabs' id='{$unique_id_all_data}-tabs' role='tablist' >
					{$selector_panel_tabs}
				</ul>
				<div class='tab-content' id='{$unique_id_all_data}-content'>
					{$selector_panel_info}
				</div>
			</div>
		";

	} else {
		$selector_panel = '';
	}

	// All location data is in the array. Output it.
	$json_object = '<input type="hidden" name="' . html_input_name_locations . '" data-locations=' . "'" . json_encode( $pins ) . "'" . ' />';

	if ( get_field( 'map_visible' ) ) {
		$map = "<section><div class='ucf-health-locationsmap'  ></div></section>";
	} else {
		$map = '';
	}

	return "<div class='locations-output' id='{$unique_id_all_data}' >{$map}{$json_object}{$selector_panel}</div>";
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
