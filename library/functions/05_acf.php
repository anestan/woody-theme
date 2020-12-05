<?php

function acf_copy_metadata($from_post_id = 0, $to_post_id = 0)
{
    // Duplicate all post meta just in one SQL queries
    global $wpdb;
    $wpdb->query("INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) SELECT $to_post_id, meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = $from_post_id");
}
