<?php

use Symfony\Component\Finder\Finder;

/**
 *
 * Nom : getAcfGroupFields
 * Auteur : Benoit Bouchaud
 * Return : Retourne un tableau avec les valeurs des champs d'un groupe ACF poyr un post donné
 * @param    group_id - La clé du groupe ACF
 * @return   page_teaser_fields - Un tableau de données
 *
 */
 function getAcfGroupFields($group_id)
 {
     global $post;
     if (!empty($post)) {
         $post_id = $post->ID;

         $page_teaser_fields = array();

         $fields = acf_get_fields($group_id);

         if (!empty($fields)) {
             foreach ($fields as $field) {
                 $field_value = false;
                 if (!empty($field['name'])) {
                     $field_value = get_field($field['name'], $post_id);
                 }

                 if ($field_value && !empty($field_value)) {
                     $page_teaser_fields[$field['name']] = $field_value;
                 }
             }
         }

         return $page_teaser_fields;
     }
 }

 /**
 *
 * Nom : getTermsSlugs
 * Auteur : Benoit Bouchaud
 * Return : Retourne un tableau de termes
 * @param    postId - Le post dans lequel on recherche
 * @param    taxonomy - Le slug du vocabulaire dans lequel on recherche
 * @param    implode - Booleén => retourne une chaine de caractères si true
 * @return   slugs - Un tableau de slugs de termes
 *
 */
function getTermsSlugs($postId, $taxonomy, $implode = false)
{
    $return = [];

    $terms = get_the_terms($postId, $taxonomy);
    if (!empty($terms)) {
        foreach ($terms as $term) {
            $return[] = $term->slug;
        }

        if ($implode == true) {
            $return = implode(' ', $return);
        }
    }


    return $return;
}

 /**
 *
 * Nom : humanDays
 * Auteur : Benoit Bouchaud
 * Return : Retourne une chaine de caractères (jours) en fonction d'un nombre donné
 * @param    number - int
 * @return   human_string - Un chaine de caractères
 *
 */

function humanDays($number)
{
    $return = '';

    if ($number % 7 === 0) {
        $week_number = $number / 7;
        if ($week_number > 1) {
            $return = $week_number . ' semaines';
        } else {
            $return = $week_number . ' semaine';
        }
    } else {
        if ($number > 1) {
            $return = $number . ' jours';
        } else {
            $return = $number . ' jour';
        }
    }

    return $return;
}

 /**
 *
 * Nom : getWoodyIcons
 * Auteur : Benoit Bouchaud
 * Return : Un tableau
 * @return   the_icons - La liste de tous les icones du site
 *
 */
function getWoodyIcons()
{
    $return = [];

    //TODO: Récupérer une variable globale en fonction du set d'icones choisis dans le thème pour remplacer '/src/icons/icons_set_01'
    $core_icons = woodyIconsFolder(get_template_directory() . '/src/icons/icons_set_01');
    $site_icons = woodyIconsFolder(get_stylesheet_directory() . '/src/icons');

    $return = array_merge($core_icons, $site_icons);

    return $return;
}

function woodyIconsFolder($folder)
{
    $return = array();

    $icons_finder = new Finder();
    $icons_finder->files()->name('*.svg')->in($folder);
    foreach ($icons_finder as $key => $icon) {
        $icon_name = str_replace('.svg', '', $icon->getRelativePathname());
        $icon_class = 'wicon-' . $icon_name;
        $icon_human_name = str_replace('-', ' ', $icon_name);
        $icon_human_name = substr($icon_human_name, 4);
        $icon_human_name = ucfirst($icon_human_name);
        $return[$icon_class] = $icon_human_name;
    }

    return $return;
}

 /**
 *
 * Nom : getWoodyTwigPaths
 * Auteur : Benoit Bouchaud
 * Return : Un tableau
 * @return   the_icons - La liste de tous les icones du site
 *
 */
function getWoodyTwigPaths()
{
    $woodyTwigsPaths = [];
    $woodyComponents = get_transient('woody_components');
    if (empty($woodyComponents)) {
        $woodyComponents = Woody::getComponents();
        set_transient('woody_components', $woodyComponents);
    }

    $woodyTwigsPaths = Woody::getTwigsPaths($woodyComponents);

    return $woodyTwigsPaths;
}
