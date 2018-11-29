<?php
/**
 * Template
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Template_Page extends WoodyTheme_TemplateAbstract
{
    protected $twig_tpl;

    public function __construct()
    {
        parent::__construct();
    }

    protected function registerHooks()
    {
    }

    protected function setTwigTpl()
    {
        if (!empty(is_front_page())) {
            $this->twig_tpl = 'front.twig';
        } else {
            $this->twig_tpl = 'page.twig';
        }
    }

    protected function extendContext()
    {
        $this->commonContext();

        if (!empty(is_front_page())) {
            $this->frontpageContext();
        } else {
            $this->pageContext();
        }
    }

    protected function frontpageContext()
    {
        /*********************************************
         * Compilation du Diaporama en page d'accueil
         *********************************************/
        $home_slider = getAcfGroupFields('group_5bb325e8b6b43');
        if (!empty($home_slider['landswpr_slides'])) {
            $this->context['home_slider'] = Timber::compile($this->context['woody_components'][$home_slider['landswpr_woody_tpl']], $home_slider);
        }
    }

    protected function pageContext()
    {
        $this->context['active_social_shares'] = getActiveShares();

        /*********************************************
         * Compilation du visuel et accroche
         *********************************************/
        $page_hero = [];
        $page_hero = getAcfGroupFields('group_5b052bbee40a4');
        if (!empty($page_hero['page_heading_media_type']) && ($page_hero['page_heading_media_type'] == 'movie' && !empty($page_hero['page_heading_movie']) || ($page_hero['page_heading_media_type'] == 'img' && !empty($page_hero['page_heading_img'])))) {
            if (empty($page_teaser['page_teaser_display_title'])) {
                $page_hero['title_as_h1'] = true;
            }

            $page_hero['page_heading_img']['attachment_more_data'] = (!empty($page_hero['page_heading_img'])) ? getAttachmentMoreData($page_hero['page_heading_img']['ID']) : '';
            if (!empty($page_hero['page_heading_social_movie'])) {
                preg_match_all('@src="([^"]+)"@', $page_hero['page_heading_social_movie'], $result);
                $iframe_url = $result[1][0];
                if (strpos($iframe_url, 'youtube') != false) {
                    $yt_params_url = $iframe_url . '?&autoplay=0&rel=0';
                    $page_hero['page_heading_social_movie'] = str_replace($iframe_url, $yt_params_url, $page_hero['page_heading_social_movie']);
                }
            }

            $page_hero['title'] = (!empty($page_hero['title'])) ? str_replace('-', '&#8209', $page_hero['title']) : '';

            $this->context['page_hero'] = Timber::compile($this->context['woody_components'][$page_hero['heading_woody_tpl']], $page_hero);
        }

        /*********************************************
         * Compilation de l'en tête de page
         *********************************************/
        $page_teaser = [];
        $page_teaser = getAcfGroupFields('group_5b2bbb46507bf');
        if (!empty($page_teaser)) {
            $page_teaser['page_teaser_title'] = (!empty($page_teaser['page_teaser_display_title'])) ? str_replace('-', '&#8209', $this->context['post']->post_title) : '';
            $page_teaser['the_classes'] = [];
            $page_teaser['the_classes'][] = (!empty($page_teaser['background_img_opacity'])) ? $page_teaser['background_img_opacity'] : '';
            $page_teaser['the_classes'][] = (!empty($page_teaser['background_color'])) ? $page_teaser['background_color'] : '';
            $page_teaser['the_classes'][] = (!empty($page_teaser['border_color'])) ? $page_teaser['border_color'] : '';
            $page_teaser['the_classes'][] =  (!empty($page_teaser['teaser_margin_bottom'])) ? $page_teaser['teaser_margin_bottom'] : '';
            $page_teaser['the_classes'][] = (!empty($page_teaser['background_img'])) ? 'isRel' : '';
            $page_teaser['classes'] = (!empty($page_teaser['the_classes'])) ? implode(' ', $page_teaser['the_classes']) : '';
            $page_teaser['breadcrumb'] = yoast_breadcrumb('<div class="breadcrumb-wrapper padd-top-sm padd-bottom-sm">', '</div>', false);
            $page_teaser['trip_infos'] = (!empty($this->context['trip_infos'])) ? $this->context['trip_infos'] : '';
            if (!empty($page_teaser['page_teaser_media_type']) && $page_teaser['page_teaser_media_type'] == 'map') {
                $page_teaser['post_coordinates'] = (!empty(getAcfGroupFields('group_5b3635da6529e'))) ? getAcfGroupFields('group_5b3635da6529e') : '';
            }

            $this->context['page_teaser'] = Timber::compile($this->context['woody_components'][$page_teaser['page_teaser_woody_tpl']], $page_teaser);
        }

        /*********************************************
         * Compilation du bloc prix
         *********************************************/
        $trip_infos = getAcfGroupFields('group_5b6c5e6ff381d');
        if (!empty($trip_infos['the_duration']['count_days']) || !empty($trip_infos['the_length']['length']) || !empty($trip_infos['the_price']['price'])) {
            //TODO: Gérer le fichier gps pour affichage s/ carte
            $trip_infos['the_duration']['count_days'] = ($trip_infos['the_duration']['count_days']) ? humanDays($trip_infos['the_duration']['count_days']) : '';
            $trip_infos['the_price']['price'] = (!empty($trip_infos['the_price']['price'])) ? str_replace('.', ',', $trip_infos['the_price']['price']) : '';
            $this->context['trip_infos'] = Timber::compile($this->context['woody_components'][$trip_infos['tripinfos_woody_tpl']], $trip_infos);
        } else {
            $trip_infos = [];
        }
    }

    protected function commonContext()
    {
        $this->context['page_terms'] = implode(' ', getPageTerms($this->context['post']->ID));

        /*********************************************
         * Check type de publication
         *********************************************/
        if ($this->context['page_type'] === 'playlist_tourism') {
            require_once 'parts/inc-touristic-playlist.php';
        }

        /*********************************************
        * Compilation des sections
        *********************************************/
        $this->context['sections'] = [];
        $sections = $this->context['post']->get_field('section');

        if (!empty($sections)) {
            foreach ($sections as $section_id => $section) {
                $the_header = '';
                $the_layout = '';

                if (!empty($section['icon']) || !empty($section['pretitle']) || !empty($section['title']) || !empty($section['subtitle']) || !empty($section['description'])) {
                    $the_header = Timber::compile($this->context['woody_components']['section-section_header-tpl_01'], $section);
                }

                // Pour chaque bloc d'une section, on compile les données dans un template Woody
                // Puis on les compile dans le template de grille Woody selectionné
                $components = [];
                $components['no_padding'] = $section['scope_no_padding'];
                $components['alignment'] = (!empty($section['section_alignment'])) ? $section['section_alignment'] : '';

                if (!empty($section['section_content'])) {
                    foreach ($section['section_content'] as $layout_id => $layout) {
                        $layout['post'] = [
                            'ID' => $this->context['post']->ID,
                            'title' => $this->context['post']->title,
                            'page_type' => $this->context['page_type']
                        ];
                        $layout['uniqid'] = 'section_' . $section_id . '_' . 'section_content_' . $layout_id;
                        $layout['visual_effects'] = (!empty($layout['visual_effects'])) ? formatVisualEffectData($layout['visual_effects']) : '';

                        $components['items'][] = getComponentItem($layout, $this->context);
                    }

                    if (!empty($section['section_woody_tpl'])) {
                        $the_layout = Timber::compile($this->context['woody_components'][$section['section_woody_tpl']], $components);
                    }
                }

                // On récupère les données d'affichage personnalisables
                $display = getDisplayOptions($section);

                // On ajoute les 3 parties compilées d'une section + ses paramètres d'affichage
                // puis on compile le tout dans le template de section Woody
                $the_section = [
                    'header' => $the_header,
                    'layout' => $the_layout,
                    'display' => $display,
                ];

                $this->context['the_sections'][] = Timber::compile($this->context['woody_components']['section-section_full-tpl_01'], $the_section);
            }
        }
    }
}
