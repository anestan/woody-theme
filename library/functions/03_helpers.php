<?php

use WoodyTheme\Process\WoodyTheme_WoodyProcessGetters;
use WoodyTheme\Process\WoodyTheme_WoodyProcessCompilers;
use WoodyTheme\Process\WoodyTheme_WoodyProcessTools;

// ***************************************************************************************//
// Get previews - Retournent des tableaux de données compatibles avec les templates Woody //
// ***************************************************************************************//

function getCustomPreview($item, $wrapper = null)
{
    $getter = new WoodyTheme_WoodyProcessGetters;
    return $getter->getCustomPreview($item, $wrapper);
}

function getPagePreview($wrapper, $item, $clickable = true)
{
    $getter = new WoodyTheme_WoodyProcessGetters;
    return $getter->getPagePreview($wrapper, $item, $clickable);
}

function getTouristicSheetPreview($wrapper = null, $item)
{
    $getter = new WoodyTheme_WoodyProcessGetters;
    return $getter->getTouristicSheetPreview($wrapper, $item);
}

function getAutoFocusSheetData($wrapper, $playlist_params = [])
{
    $getter = new WoodyTheme_WoodyProcessGetters;
    return $getter->getAutoFocusSheetData($wrapper, $playlist_params);
}

function getFocusBlockTitles($wrapper)
{
    $tools = new WoodyTheme_WoodyProcessTools;
    return $tools->getFocusBlockTitles($wrapper);
}

function getProfilePreview($wrapper, $post)
{
    $getter = new WoodyTheme_WoodyProcessGetters;
    return $getter->getProfilePreview($wrapper, $post);
}
