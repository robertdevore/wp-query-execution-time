<?php

/**
 * The plugin bootstrap file
 *
 * @link              https://www.robertdevore.com
 * @since             1.0.0
 * @package           WP_Query_Execution_Time
 *
 * @wordpress-plugin
 *
 * Plugin Name: WP_Query Execution Time
 * Description: Measure the execution time of a specific WP_Query and log it to the browser console.
 * Plugin URI:  https://www.robertdevore.com/wp-query-execution-time-wordpress-plugin/
 * Version:     1.0.0
 * Author:      Robert DeVore
 * Author URI:  https://www.robertdevore.com
 * License:     GPL-3.0+
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: wpqet
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Current plugin version.
 */
define( 'WPQET_VERSION', '1.0.0' );

// Create an array to store executed queries.
$executed_queries = array();

// Hook the function to run before and after the specific WP_Query
add_action( 'pre_get_posts', 'wpqet_start_custom_wp_query_timer' );
add_action( 'loop_end', 'wpqet_end_custom_wp_query_timer' );

/**
 * Start the timer before specific WP_Query instances.
 *
 * This function initializes a timer before the execution of specific WordPress queries
 * identified by their query IDs. It supports both single query IDs and arrays of query IDs
 * provided through the 'wpqet_target_query_id' filter.
 *
 * @param WP_Query $query The current WP_Query instance to check and potentially start the timer for.
 */
function wpqet_start_custom_wp_query_timer( $query ) {
    global $executed_queries; // Define the variable as global.

    // Get the target query ID(s) from the filter.
    $target_query_ids = apply_filters( 'wpqet_target_query_id', array( '' ) );

    // Ensure $target_query_ids is an array.
    if ( ! is_array( $target_query_ids ) ) {
        $target_query_ids = array( $target_query_ids );
    }

    // Check if this is one of the specific WP_Queries and if it hasn't been executed already.
    $query_id = $query->get( 'wp_query_id' );

    // Check if this is the main query or if it hasn't been executed already.
    if ( $query->is_main_query() && ! in_array( 'main_query', $executed_queries ) ) {
        return;
    }

    if ( in_array( $query_id, $target_query_ids ) ) {
        // Create a unique key for the executed query using the query ID and a counter.
        $executed_key = $query_id . '_' . count( $executed_queries );

        // Check if this query has already been executed by looking for the key.
        if ( ! in_array( $executed_key, $executed_queries ) ) {
            global $start_time;
            $start_time = microtime( true );
            $executed_queries[] = $executed_key;
        }
    }
}

/**
 * End the timer and log execution time for specific WP_Query instances.
 *
 * This function finalizes the timer after the execution of specific WordPress queries
 * and logs the execution time to the browser console for each target query ID.
 *
 * @global float $start_time The start time recorded by the 'wpqet_start_custom_wp_query_timer' function.
 */
function wpqet_end_custom_wp_query_timer() {
    global $executed_queries; // Define the variable as global.
    global $start_time;

    if ( isset( $start_time ) ) {
        // Calculate execution time.
        $end_time       = microtime( true );
        $execution_time = $end_time - $start_time;

        // Log execution time to the browser console for each target query ID.
        $target_query_ids = apply_filters( 'wpqet_target_query_id', array( '' ) );

        if ( is_array( $target_query_ids ) ) {
            foreach ( $target_query_ids as $query_id ) {
                // Log the execution time for each unique instance of the query ID.
                foreach ( $executed_queries as $executed_key ) {
                    if ( strpos( $executed_key, $query_id ) === 0 ) {
                        echo '<script>console.log( "WP_Query Execution Time for ' . $query_id . ': ' . number_format( $execution_time, 4 ) . ' seconds" );</script>';
                        break; // Only log once for each query ID.
                    }
                }
            }
        } else {
            // Log the execution time for the main query.
            foreach ( $executed_queries as $executed_key ) {
                if ( strpos( $executed_key, $target_query_ids ) === 0 ) {
                    echo '<script>console.log( "WP_Query Execution Time: ' . number_format( $execution_time, 4 ) . ' seconds" );</script>';
                    break; // Only log once for the main query.
                }
            }
        }

        // Reset the start time for the next query.
        $start_time = null;
    }
}
