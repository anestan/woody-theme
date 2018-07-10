<?php
/**
 * The page template file
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 * @package HawwwaiTheme
 * @since HawwwaiTheme 1.0.0
 */

$context = Timber::get_context();

// Creating Timber object to access twig keys
$context['post'] = new TimberPost();

$context['woody_components'] = Woody::getTwigsPaths();

// rcd(get_class_methods(TimberPost), true);

/** ****************************
 * Compilation du visuel et accroche
 **************************** **/
$page_heading = [];
$page_heading = get_acf_group_fields(33);
if (!empty($page_heading)) {
    $context['page_heading'] = Timber::compile($context['woody_components'][$page_heading['woody_tpl']], $page_heading);
}

/** ****************************
 * Compilation de l'en tête de page
 **************************** **/
$page_teaser = [];
$page_teaser = get_acf_group_fields(725);
// rcd($page_heading, true);




$page_type_term = wp_get_post_terms($context['post']->ID, 'page_type');
$page_type = $page_type_term[0]->slug;

if ($page_type === 'playlist_tourism') {
    /** ************************
    * Appel apirender pour récupérer le html de la playlist
    ************************ **/
    $playlist_conf_id = get_field('field_5b338ff331b17');
    $context['playlist_template'] = apply_filters('wp_hawwwai_sit_playlist_render', $playlist_conf_id, 'fr');
} else {
    /** ************************
    * Compilation des sections
    ************************ **/

    $context['sections'] = [];
    $sections = $context['post']->get_field('section');

    if (!empty($sections)) {
        // Foreach section, fill vars to display in the woody's components
        foreach ($sections as $key => $section) {

    // On compile les données du header de section
            $the_header = Timber::compile($context['woody_components']['section-section_header-tpl_1'], $section);

            // On compile les données du footer de section
            $the_footer = Timber::compile($context['woody_components']['section-section_footer-tpl_1'], $section);

            // Pour chaque bloc d'une section, on compile les données dans un template Woody
            // Puis on les compile dans le template de grille Woody selectionné
            $components = [];

            if (!empty($section['section_content'])) {
                foreach ($section['section_content'] as $key => $layout) {
                    if ($layout['acf_fc_layout'] == 'manual_focus') {
                        $the_items = getManualFocus_data($layout['content_selection']);
                        $components['items'][] = Timber::compile($context['woody_components'][$layout['woody_tpl']], $the_items);
                    } elseif ($layout['acf_fc_layout'] == 'auto_focus') {
                        $the_items = getAutoFocus_data($context['post'], $layout);
                        $components['items'][] = Timber::compile($context['woody_components'][$layout['woody_tpl']], $the_items);
                    } elseif ($layout['acf_fc_layout'] == 'playlist_bloc') {
                        // rcd($layout);
                        $playlist_conf_id = $layout['playlist_conf_id'];
                        $components['items'][] = apply_filters('wp_hawwwai_sit_playlist_render', $playlist_conf_id, 'fr');
                    } else {
                        $components['items'][] = Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                    }
                }

                if (!empty($section['woody_tpl'])) {
                    $the_layout = Timber::compile($context['woody_components'][$section['woody_tpl']], $components);
                }
            }

            // On récupère les données d'affichage personnalisables
            $display = getDisplayOptions($section);

            // On ajoute les 3 parties compilées d'une section + ses paramètres d'affichage
            // puis on compile le tout dans le template de section Woody
            $the_section = [
        'header' => $the_header,
        'footer' => $the_footer,
        'layout' => $the_layout,
        'display' => $display,
    ];

            $context['the_sections'][] = Timber::compile($context['woody_components']['section-section_full-tpl_1'], $the_section);
        }
    }
}

// Render the $context in page.twig
Timber::render('page.twig', $context);
