<?php


// TODO get same every call after first (save first result)
// Create class ?

// Get all Map Keys
// DEV mode : otm key is always 'raccourci'
function getMapKeys($encoded = false)
{
    $map_keys = [];

    $otmkeys = RC_TOURISTIC_MAPS_API_KEY;
    $gmapkeys = RC_GOOGLE_MAPS_API_KEY;
    $ignkeys = RC_IGN_MAPS_API_KEY;

    if (is_array($otmkeys) && !empty($otmkeys)) {
        $map_keys['otmKey'] = (WP_ENV === 'dev') ? 'raccourci' : shuffle($otmkeys)[0]; // override touristic_map key in DEV
    }
    if (is_array($gmapkeys) && !empty($gmapkeys)) {
        $map_keys['gmKey'] = shuffle($gmapkeys)[0];
    }
    if (is_array($ignkeys) && !empty($ignkeys)) {
        $map_keys['ignKey'] = shuffle($ignkeys)[0];
    }

    return $encoded ? wp_json_encode($map_keys) : $map_keys;
}