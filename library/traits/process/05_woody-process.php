<?php

/**
 * Woody Theme Process
 * @author Benoit BOUCHAUD
 * @copyright Raccourci Agency 2020
 */


namespace WoodyTheme\Process;

trait WoodyThemeTrait_WoodyProcess
{
    use WoodyThemeTrait_WoodyProcessTools;
    use WoodyThemeTrait_WoodyProcessCompilers;

    /**
     *
     * Nom : processWoodyLayouts
     * Auteur : Benoit Bouchaud - Jeremy Legendre
     * Return : Dispatch le traitement des données en fonction du layout ACF utilisé
     * @param    layout - La donnée du layout acf sous forme de tableau
     * @param    context - Le contexte global de la page sous forme de tableau
     * @return   return - Code HTML
     *
     */
    public function processWoodyLayouts($layout, $context)
    {
        $return = '';
        $layout['default_marker'] = !empty($context['default_marker']) ? $context['default_marker'] : '';
        // Traitements spécifique en fonction du type de layout
        switch ($layout['acf_fc_layout']) {
            case 'manual_focus':
            case 'auto_focus':
            case 'auto_focus_sheets':
            case 'auto_focus_topics':
            case 'focus_trip_components':
            case 'profile_focus':
                // TODO: les cases auto_focus_topics + auto_focus_sheets + focus_trip_components doivent être ajoutés via le filtre woody_custom_layout depuis leurs addons respectifs
                $return = $this->formatFocusesData($layout, $context['post'], $context['woody_components']);
                break;
            case 'manual_focus_minisheet':
                $return = $this->formatMinisheetData($layout, $context['woody_components']);
                break;
            case 'geo_map':
                $return = $this->formatGeomapData($layout, $context['woody_components']);
                break;
            case 'content_list':
                $return = $this->formatListContent($layout, $context['post'], $context['woody_components']);
                // $return = $this->formatFullContentList($layout, $context['post'], $context['woody_components']);
                break;
            case 'weather':
                // TODO: le case Weather doit être ajouté via le filtre woody_custom_layout depuis le plugin
                $vars['account'] = $layout['weather_account'];
                $vars['nb_days'] = $layout['weather_count_days'];
                $the_weather = apply_filters('woody_weather', $vars);
                $the_weather['bg_color'] = (!empty($layout['weather_bg_params']['background_color'])) ? $layout['weather_bg_params']['background_color'] : '';
                $the_weather['bg_img'] = $layout['weather_bg_img'];
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $the_weather);
                break;
            case 'infolive':
                // TODO: le case Infolive doit être ajouté via le filtre woody_custom_layout depuis le plugin
                $vars['resort'] = $layout['infolive_block_select_resort'];
                $vars['display_custom'] = $layout['infolive_block_switch_display'];
                $vars['display'] = $layout['infolive_block_display'];
                $vars['lists'] = $layout['infolive_list_display'];
                $vars['lists_mode'] = $layout['infolive_list_display_mode'];
                $vars['list_all'] = $layout['infolive_list_display_all_zones'];
                $vars['list_selected_zones'] = $layout['infolive_list_select_zones'];
                $the_infolive = apply_filters('woody_infolive', $vars);
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $the_infolive);
                break;
            case 'gallery':
                // Ajout des données Instagram + champs personnalisés dans le contexte des images
                $layout['gallery_type'] = !empty($layout['gallery_type']) ? $layout['gallery_type'] : "manual";

                switch ($layout['gallery_type']) {
                    case 'auto':
                        $layout['gallery_items'] = $this->getAttachmentsByMultipleTerms($layout["gallery_tags"], $layout['gallery_taxonomy_terms_andor'], $layout['gallery_count']);

                        foreach ($layout['gallery_items'] as $key => $attachment) {
                            $layout['gallery_items'][$key]['attachment_more_data'] = $this->getAttachmentMoreData($layout['gallery_items'][$key]['ID']);
                        }
                    break;
                    case 'manual':
                    default:
                        if (!empty($layout['gallery_items'])) {
                            foreach ($layout['gallery_items'] as $key => $media_item) {
                                $layout['gallery_items'][$key]['attachment_more_data'] = $this->getAttachmentMoreData($media_item['ID']);
                                if (isset($context['print_rdbk']) && !empty($context['print_rdbk'])) {
                                    $layout['gallery_items'][$key]['lazy'] = 'disabled';
                                }
                            }
                        }
                    break;
                }

                $layout['display'] = $this->getDisplayOptions($layout);
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'interactive_gallery':
                // Ajout des données Instagram + champs personnalisés dans le contexte des images
                if (!empty($layout['interactive_gallery_items'])) {
                    foreach ($layout['interactive_gallery_items'] as $key => $media_item) {
                        $layout['interactive_gallery_items'][$key]['img_mobile_url'] =  (!empty($layout['interactive_gallery_items'][$key]['interactive_gallery_photo']['sizes'])) ? $layout['interactive_gallery_items'][$key]['interactive_gallery_photo']['sizes']['ratio_4_3_medium'] : '';
                        $layout['interactive_gallery_items'][$key]['interactive_gallery_photo']['attachment_more_data'] = $this->getAttachmentMoreData($media_item['interactive_gallery_photo']['ID']);
                    }
                }
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'links':
                $layout['woody_tpl'] = 'blocks-links-tpl_01';
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'tabs_group':
                $layout['tabs'] = $this->processWoodySubLayouts($layout['tabs'], 'tab_woody_tpl', 'tabs', $context);
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'slides_group':
                $layout['slides'] = $this->processWoodySubLayouts($layout['slides'], 'slide_woody_tpl', 'slides', $context);
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'socialwall':
                $layout['gallery_items'] = [];
                if ($layout['socialwall_type'] == 'manual') {
                    if (!empty($layout['socialwall_manual'])) {
                        foreach ($layout['socialwall_manual'] as $key => $media_item) {
                            // On ajoute une entrée "gallery_items" pour être compatible avec le tpl woody
                            $layout['gallery_items'][] = $media_item;
                            $layout['gallery_items'][$key]['attachment_more_data'] = $this->getAttachmentMoreData($media_item['ID']);
                        }
                    }
                } elseif ($layout['socialwall_type'] == 'auto') {
                    // On récupère les images en fonction des termes sélectionnés
                    $layout['gallery_items'] = (!empty($layout['socialwall_auto'])) ? $this->getAttachmentsByTerms('attachment_hashtags', $layout['socialwall_auto']) : '';
                    if (!empty($layout['gallery_items'])) {
                        foreach ($layout['gallery_items'] as $key => $media_item) {
                            $layout['gallery_items'][$key]['attachment_more_data'] = $this->getAttachmentMoreData($media_item['ID']);
                        }
                    }
                }
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'semantic_view':
                $return = $this->formatSemanticViewData($layout, $context['woody_components']);
                break;
            case 'audio_player':
                $layout['woody_tpl'] = 'blocks-audio-tpl_01';
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'disqus_block':
                // TODO: le case Disqus block doit être ajouté via le filtre woody_custom_layout depuis le plugin
                $layout['woody_tpl'] = 'blocks-disqus-tpl_01';
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'eye_candy_img':
                $layout['woody_tpl'] = 'blocks-eye_candy_img-tpl_01';
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'page_summary':
                if (!empty($layout['summary_bg_params'])) {
                    $layout['display'] = $this->getDisplayOptions($layout['summary_bg_params']);
                }
                $layout['items'] = $this->formatSummaryItems(get_the_ID());
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
            break;
            case 'free_text':
                $layout['text'] = $this->replacePattern($layout['text'], get_the_ID());
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
            break;
            case 'quote':
                $layout['display'] = $this->getDisplayOptions($layout['quote_bg_params']);
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
            break;
            case 'feature':
                $layout['display'] = $this->getDisplayOptions($layout);
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
            break;
            case 'story':
                $layout['display'] = $this->getDisplayOptions($layout['story_bg_params']);
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
            break;
            case 'testimonials':
                $layout = $this->formatTestimonials($layout);
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
            break;
            default:

                // On autorise le traitement des layouts depuis un code externe
                $layout = apply_filters('woody_custom_layout', $layout, $context);

                // On compile le $layout uniquement si ça n'a pas déjà été fait
                if (is_array($layout)) {
                    $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                } else {
                    $return = $layout;
                }
        }
        return $return;
    }

    /**
     *
     * Nom : processWoodySubLayouts
     * Auteur : Benoit Bouchaud
     * Return : Retourne un DOM html
     * @param    scope - L'élément parent qui contient les grilles
     * @param    gridTplField - Le slug du champ 'Template'
     * @param    uniqIid_prefix - Un préfixe d'id, si besoin de créer un id unique (tabs)
     * @return   scope - Un DOM Html
     *
     */
    public function processWoodySubLayouts($wrapper = [], $gridTplField, $uniqIid_prefix = '', $context)
    {
        $woodyTwigsPaths = getWoodyTwigPaths();
        foreach ($wrapper as $grid_key => $grid) {
            $grid_content = [];
            if (!empty($uniqIid_prefix) && is_numeric($grid_key)) {
                $wrapper[$grid_key]['el_id'] = $uniqIid_prefix . '-' . uniqid();
            }

            // On compile les tpls woody pour chaque bloc ajouté dans l'onglet
            if (!empty($grid['light_section_content']) && is_array($grid['light_section_content'])) {
                foreach ($grid['light_section_content'] as $layout) {
                    $grid_content['items'][] = $this->processWoodyLayouts($layout, $context);
                }

                // On compile le tpl de grille woody choisi avec le DOM de chaque bloc
                $wrapper[$grid_key]['light_section_content'] = \Timber::compile($woodyTwigsPaths[$grid[$gridTplField]], $grid_content);
            }
        }

        if (!empty($uniqIid_prefix)) {
            $wrapper['group_id'] = $uniqIid_prefix . '-' . uniqid();
        }

        return $wrapper;
    }

    public function processWoodySections($sections, $context)
    {
        $return = [];
        if (!empty($sections) && is_array($sections)) {
            foreach ($sections as $section_id => $section) {
                $the_header = '';
                $the_layout = '';

                if (!empty($section['icon']) || !empty($section['pretitle']) || !empty($section['title']) || !empty($section['subtitle']) || !empty($section['description'])) {
                    $the_header = \Timber::compile($context['woody_components']['section-section_header-tpl_01'], $section);
                }

                // Pour chaque bloc d'une section, on compile les données dans un template Woody
                // Puis on les compile dans le template de grille Woody selectionné
                $components = [];
                $components['no_padding'] = $section['scope_no_padding'];
                $components['alignment'] = (!empty($section['section_alignment'])) ? $section['section_alignment'] : '';

                if (!empty($section['section_content'])) {
                    foreach ($section['section_content'] as $layout_id => $layout) {
                        // On définit un uniqid court à utiliser dans les filtres de listes en paramètre GET
                        // Uniqid long : section . $section_id . '_section_content' . $layout_id
                        $layout['uniqid'] = 's' . $section_id . 'sc' . $layout_id;
                        $layout['visual_effects'] = (!empty($layout['visual_effects'])) ? $this->formatVisualEffectData($layout['visual_effects']) : '';
                        $components['items'][] = $this->processWoodyLayouts($layout, $context);
                    }

                    // On retire les items retournés vides par processWoodyLayouts
                    $components['items'] = array_filter($components['items']);

                    if (!empty($section['section_woody_tpl']) && !empty($components['items'])) {
                        $the_layout = \Timber::compile($context['woody_components'][$section['section_woody_tpl']], $components);
                    }
                }

                // On récupère les données d'affichage personnalisables
                $display = $this->getDisplayOptions($section);

                // On ajoute les class personnalisées de section dans la liste des class d'affichage
                if (!empty($display['classes']) && !empty($section['section_class'])) {
                    $display['classes'] .=  ' ' . $section['section_class'];
                }

                // On ajoute les 3 parties compilées d'une section + ses paramètres d'affichage
                // puis on compile le tout dans le template de section Woody
                $the_section = [
                    'header' => $the_header,
                    'layout' => $the_layout,
                    'display' => $display,
                ];
                if (!empty($section['section_banner'])) {
                    foreach ($section['section_banner'] as $banner) {
                        $the_section[$banner] = $this->getSectionBannerFiles($banner);
                    }
                }

                // On récupère l'option "Masquer les sections vides"
                $hide_empty_sections = get_field('hide_empty_sections', 'option');


                if ($section['hide_section']) {
                    $the_section['is_empty'] = true;
                    $return[] = \Timber::compile($context['woody_components']['section-section_full-tpl_01'], $the_section);
                } else {
                    if (!empty($the_section['layout'])) {
                        $return[] = \Timber::compile($context['woody_components']['section-section_full-tpl_01'], $the_section);
                    } else {
                        if (!empty($hide_empty_sections)) {
                            if (is_user_logged_in()) {
                                // Si l'utilisateur est connecté, on compile le twig empty_section
                                $return[] = \Timber::compile('parts/empty_section.twig', $the_section);
                            } else {
                                $the_section['is_empty'] = true;
                                $return[] = \Timber::compile($context['woody_components']['section-section_full-tpl_01'], $the_section);
                            }
                        } else {
                            $return[] = \Timber::compile($context['woody_components']['section-section_full-tpl_01'], $the_section);
                        }
                    }
                }
            }
        }

        return $return;
    }
}
