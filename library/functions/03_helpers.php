<?php

use WoodyProcess\Getters\WoodyTheme_WoodyGetters;
use WoodyProcess\Compilers\WoodyTheme_WoodyCompilers;
use WoodyProcess\Tools\WoodyTheme_WoodyProcessTools;

// ***************************************************************************************//
// Get previews - Retournent des tableaux de données compatibles avec les templates Woody //
// ***************************************************************************************//

function getCustomPreview($item, $wrapper = null)
{
    $getter = new WoodyTheme_WoodyGetters;
    return $getter->getCustomPreview($item, $wrapper);
}

function getPagePreview($wrapper, $item, $clickable = true)
{
    $getter = new WoodyTheme_WoodyGetters;
    return $getter->getPagePreview($wrapper, $item, $clickable);
}

function getTouristicSheetPreview($wrapper = null, $item)
{
    $getter = new WoodyTheme_WoodyGetters;
    return $getter->getTouristicSheetPreview($wrapper, $item);
}

function getAutoFocusSheetData($wrapper, $playlist_params = [])
{
    $getter = new WoodyTheme_WoodyGetters;
    return $getter->getAutoFocusSheetData($wrapper, $playlist_params);
}

function getBlockTitles($wrapper)
{
    $tools = new WoodyTheme_WoodyProcessTools;
    return $tools->getBlockTitles($wrapper);
}

function getProfilePreview($wrapper, $post)
{
    $getter = new WoodyTheme_WoodyGetters;
    return $getter->getProfilePreview($wrapper, $post);
}

function woodyComponentGetDisplayOptions($wrapper)
{
    $tools = new WoodyTheme_WoodyProcessTools;
    return $tools->getDisplayOptions($wrapper);
}
