# WP_Query Execution Time

Measure the execution time of a specific WP_Query and log it to the browser console.

### How to use

1. Activate the plugin
2. Pass the wp_query_id as an array to the `wpqet_target_query_id` filter
3. Review the console to view the execution time for your queries

**Filter example**

````
/**
 * Target Query ID
 * 
 * @return string
 */
function acme_target_query_id_filter( $ids ) {
    $ids = array( 'your_query_id', 'another_id_here' );

    return $ids;
}
add_filter( 'wpqet_target_query_id', 'acme_target_query_id_filter' );
````