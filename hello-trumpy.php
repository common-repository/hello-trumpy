<?php

/*
Plugin Name: Hello Trumpy
Plugin URI: https://wordpress.org/plugins/hello-trumpy/
Description: Randomly see a quote from President Donald Trump
Version: 1.2
Author: LJerez
Author URI: http://LeoJerez.com
License: GPLv2+
*/

function get_hello_trumpy_quote() {
    $url = "https://api.whatdoestrumpthink.com/api/v1/quotes/";
    $result = "";
	$transResult = get_transient('trumpyAPI');

	if($transResult === false) {

		// Use WP Http to pull API data
		if ( ! class_exists( 'WP_Http' ) ) {
			include_once( ABSPATH . WPINC . '/class-http.php' );
		}

		$wpObject = new WP_Http();

		$result = $wpObject->get( $url );
		set_transient('trumpyAPI', $result, 60*60*24*7); // Hold transient data for 1 week in seconds

		if ( ! is_wp_error( $transResult ) ) {

			// We want to work with a PHP object form the Json to get the message
			$result = json_decode( $result['body'] );
			$result = $result->messages->non_personalized;

			// Get one Random quote from the list of quotes
			//$result = explode(',', $result);
			$result = $result[array_rand($result, 1)];

			// If the message is long (Trump is long-winded), split into two lines at the nearest word near the middle
			if ( strlen( $result ) >= 120 ) {
				$halfWayPos  = strlen( $result ) / 2;
				$nextWordPos = strpos( $result, " ", $halfWayPos ) + 1;
				$result      = substr_replace( $result, "<br />", $nextWordPos, 0 );
			}
		}

	} else{
		if ( ! is_wp_error( $transResult ) ) {

			// We want to work with a PHP object form the Json to get the message
			$result = json_decode( $transResult['body'] );
			$result = $result->messages->non_personalized;

			// Get one Random quote from the list of quotes
			//$result = explode(',', $result);
			$result = $result[array_rand($result, 1)];

			// If the message is long (Trump is long-winded), split into two lines at the nearest word near the middle
			if ( strlen( $result ) >= 120 ) {
				$halfWayPos  = strlen( $result ) / 2;
				$nextWordPos = strpos( $result, " ", $halfWayPos ) + 1;
				$result      = substr_replace( $result, "<br />", $nextWordPos, 0 );
			}
		}
	}



    // Returns text with quote transformations
    return wptexturize( $result );
}

// Calls API Get function and prints html, positioned/styled separately.
function hello_trumpy() {
    $quote = get_hello_trumpy_quote();
    echo "<p id='trumpy'><em>$quote</em></p>";
}

// Create [hello-trumpy] shortcode
add_shortcode('hello-trumpy', 'hello_trumpy');

// Hook into admin_notices to display the quote.
add_action( 'admin_notices', 'hello_trumpy' );

// Account for right-to-left languages and call different CSS appropriately.

function trumpy_css()
{
    if (is_rtl()) {
        wp_enqueue_style('trumpy-rtl', plugins_url('css/trumpy-rtl.css', __FILE__));
    } else {
        wp_enqueue_style('trumpy', plugins_url('css/trumpy.css', __FILE__));
    }
}

// Hook into admin head to include trumpy css
add_action( 'admin_head', 'trumpy_css' );

?>
