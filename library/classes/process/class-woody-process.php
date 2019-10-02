<?php

namespace WoodyProcess\Process;

use WoodyProcess\Tools\WoodyTheme_WoodyProcessTools;
use WoodyProcess\Compilers\WoodyTheme_WoodyCompilers;

/**
 * Dispatch Woody data processing
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.10.0
 * @author Jeremy Legendre - Benoit Bouchaud
 */


class WoodyTheme_WoodyProcess
{
    protected $tools;
    protected $compilers;

    public function __construct()
    {
        $this->tools = new WoodyTheme_WoodyProcessTools;
        $this->compilers = new WoodyTheme_WoodyCompilers;
    }

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
        $layout['default_marker'] = $context['default_marker'];
        // Traitements spécifique en fonction du type de layout
        switch ($layout['acf_fc_layout']) {
            case 'manual_focus':
            case 'auto_focus':
            case 'auto_focus_sheets':
            case 'focus_trip_components':
            case 'auto_focus_topics':
                $return = $this->compilers->formatFocusesData($layout, $context['post'], $context['woody_components']);
                break;
            case 'geo_map':
                $return = $this->compilers->formatGeomapData($layout, $context['woody_components']);
                break;
            case 'content_list':
                $return = $this->compilers->formatListContent($layout, $context['post'], $context['woody_components']);
                // $return = $this->compilers->formatFullContentList($layout, $context['post'], $context['woody_components']);
                break;
            case 'weather':
                $vars['account'] = $layout['weather_account'];
                $vars['nb_days'] = $layout['weather_count_days'];
                $the_weather = apply_filters('woody_weather', $vars);
                $the_weather['bg_color'] = (!empty($layout['weather_bg_params']['background_color'])) ? $layout['weather_bg_params']['background_color'] : '';
                $the_weather['bg_img'] = $layout['weather_bg_img'];
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $the_weather);
                break;
            case 'call_to_action':
                // TODO: Case à enlever lorsque les "Anciens champs" seront supprimés du backoffice (utile pour les anciens liens de CTA uniquement)
                $layout['modal_id'] = uniqid($layout['acf_fc_layout'] . '_');
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'gallery':
                // Ajout des données Instagram + champs personnaliés dans le contexte des images
                if (!empty($layout['gallery_items'])) {
                    foreach ($layout['gallery_items'] as $key => $media_item) {
                        $layout['gallery_items'][$key]['attachment_more_data'] = $this->tools->getAttachmentMoreData($media_item['ID']);
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
                    foreach ($layout['socialwall_manual'] as $key => $media_item) {
                        // On ajoute une entrée "gallery_items" pour être compatible avec le tpl woody
                        $layout['gallery_items'][] = $media_item;
                        $layout['gallery_items'][$key]['attachment_more_data'] = $this->tools->getAttachmentMoreData($media_item['ID']);
                    }
                } elseif ($layout['socialwall_type'] == 'auto') {
                    // On récupère les images en fonction des termes sélectionnés
                    $layout['gallery_items'] = (!empty($layout['socialwall_auto'])) ? $this->tools->getAttachmentsByTerms('attachment_hashtags', $layout['socialwall_auto']) : '';
                    if (!empty($layout['gallery_items'])) {
                        foreach ($layout['gallery_items'] as $key => $media_item) {
                            $layout['gallery_items'][$key]['attachment_more_data'] = $this->tools->getAttachmentMoreData($media_item['ID']);
                        }
                    }
                }
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'semantic_view':
                $return = $this->compilers->formatSemanticViewData($layout, $context['woody_components']);
                break;
            case 'audio_player':
                $layout['woody_tpl'] = 'blocks-audio-tpl_01';
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'disqus_block':
                $layout['woody_tpl'] = 'blocks-disqus-tpl_01';
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            default:
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
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
            if (!empty($grid['light_section_content'])) {
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

    /**
     *
     * Nom : processWoodyQuery
     * Auteur : Benoit Bouchaud - Jeremy Legendre
     * Return : Le résultat de la wp_query sous forme d'objet
     * @param    the_post - Un objet Timber\Post
     * @param    query_form - Champs de formulaire permettant de monter la query
     * @return   query_result - Un objet
     *
     */
    public function processWoodyQuery($the_post, $query_form, $paginate = false, $uniqid = 0, $ignore_maxnum = false)
    {
        $query_result = new \stdClass();
        $tax_query = [];


        // Création du paramètre tax_query pour la wp_query
        // Référence : https://codex.wordpress.org/Class_Reference/WP_Query
        if (!empty($query_form['focused_content_type'])) {
            $tax_query = [
                'relation' => 'AND',
                'page_type' => array(
                    'taxonomy' => 'page_type',
                    'terms' => $query_form['focused_content_type'],
                    'field' => 'term_id',
                    'operator' => 'IN'
                ),
            ];
        }

        // Si des termes ont été choisi pour filtrer les résultats
        // on créé tableau custom_tax à passer au paramètre tax_query
        $custom_tax = [];
        if (!empty($query_form['focused_taxonomy_terms'])) {

            // On récupère la relation choisie (ET/OU) entre les termes
            // et on génère un tableau de term_id pour chaque taxonomie
            $tax_query['custom_tax']['relation'] = (!empty($query_form['focused_taxonomy_terms_andor'])) ? $query_form['focused_taxonomy_terms_andor'] : 'OR';
            foreach ($query_form['focused_taxonomy_terms'] as $focused_term_key => $focused_term) {
                $term = get_term($focused_term);
                if (!empty($term) && is_object($term)) {
                    $custom_tax[$term->taxonomy][] = $focused_term;
                }
            }
            foreach ($custom_tax as $taxo => $terms) {
                $tax_query['custom_tax'][] = array(
                    'taxonomy' => $taxo,
                    'terms' => $terms,
                    'field' => 'term_id',
                    'operator' => 'IN'
                );
            }
        }

        // Si l'on trouve des filtres dans le formulaire
        if (!empty($query_form['filters_apply'])) {
            foreach ($query_form['filters_apply'] as $filter_key => $filter) {

                // On ajoute des paramètres de taxonomies à la query
                if (strpos($filter_key, 'taxonomy_terms') !== false) {
                    $tax_query[$filter_key] = [];
                    $tax_query[$filter_key]['relation'] = $filter['andor'];
                    if (!is_array($filter['terms'])) {
                        $term = get_term($filter['terms']);
                        if (!empty($term) && is_object($term)) {
                            $filter_tax[$filter_key][$term->taxonomy][] = $filter['terms'];
                        }
                    } else {
                        foreach ($filter['terms'] as $focused_term) {
                            $term = get_term($focused_term);
                            if (!empty($term) && is_object($term)) {
                                $filter_tax[$filter_key][$term->taxonomy][] = $focused_term;
                            }
                        }
                    }

                    if (!empty($filter_tax[$filter_key])) {
                        foreach ($filter_tax[$filter_key] as $taxo => $terms) {
                            $tax_query[$filter_key][] = array(
                                'taxonomy' => $taxo,
                                'terms' => $terms,
                                'field' => 'term_id',
                                'operator' => 'IN'
                            );
                        }
                    }

                    // On ajoute des paramètres de meta_query à la query
                } elseif (strpos($filter_key, 'filter_trip_price') !== false) {
                    $the_meta_query[] = [
                        'key'        => 'the_price_price',
                        'value'        => $filter['min'],
                        'type'      => 'NUMERIC',
                        'compare'    => '>='
                    ];
                    $the_meta_query[] = [
                        'key'        => 'the_price_price',
                        'value'        => $filter['max'],
                        'type'      => 'NUMERIC',
                        'compare'    => '<='
                    ];
                } elseif (strpos($filter_key, 'filter_trip_duration') !== false) {
                    $the_meta_query[] = [
                        'key'        => 'the_duration_count_days',
                        'value'        => $filter['min'],
                        'type'      => 'NUMERIC',
                        'compare'    => '>='
                    ];
                    $the_meta_query[] = [
                        'key'        => 'the_duration_count_days',
                        'value'        => $filter['max'],
                        'type'      => 'NUMERIC',
                        'compare'    => '<='
                    ];
                }
            }
        }

        switch ($query_form['focused_sort']) {
            case 'random':
                $orderby = 'rand';
                $order = 'ASC';
                break;
            case 'created_desc':
                $orderby = 'post_date';
                $order = 'DESC';
                break;
            case 'created_asc':
                $orderby = 'post_date';
                $order = 'ASC';
                break;
            case 'menu_order':
                $orderby = 'menu_order';
                $order = 'ASC';
                break;
            default:
        }

        if ($orderby == 'rand' && $paginate == true) {
            $seed = date("dmY");
            $orderby = 'RAND(' . $seed . ')';
        }

        // On créé la wp_query en fonction des choix faits dans le backoffice
        // NB : si aucun choix n'a été fait, on remonte automatiquement tous les contenus de type page
        $the_query = [
            'post_type' => 'page',
            'posts_per_page' => (!empty($query_form['focused_count'])) ? $query_form['focused_count'] : 16,
            'post_status' => 'publish',
            'post__not_in' => array($the_post->ID),
            'order' => $order,
            'orderby' => $orderby,
        ];

        if ($ignore_maxnum === true) {
            $the_query['posts_per_page'] = -1;
        }

        if ($paginate == true) {
            $explode_uniqid = explode('_', $uniqid);
            $the_page_name = 'section_' . $explode_uniqid[1] . '_' . $explode_uniqid[4];
            $the_page = (!empty($_GET[$the_page_name])) ? htmlentities(stripslashes($_GET[$the_page_name])) : '';
            $the_query['paged'] = (!empty($the_page)) ? $the_page : 1;
        }

        $the_query['tax_query'] = (!empty($tax_query)) ? $tax_query : '';

        // Si Hiérarchie = Enfants directs de la page
        // On passe le post ID dans le paramètre post_parent de la query
        if ($query_form['focused_hierarchy'] == 'child_of') {
            $the_query['post_parent'] = $the_post->ID;
        }

        // Si Hiérarchie = Pages de même niveau
        // On passe le parent_post_ID dans le paramètre post_parent de la query
        if ($query_form['focused_hierarchy'] == 'brother_of') {
            $the_query['post_parent'] = $the_post->post_parent;
        }

        // Si on trouve une metaquery (recherche sur champs ACF)
        // On définit une relation AND par défaut
        if (!empty($the_meta_query)) {
            $the_meta_query_relation = [
                'relation' => 'AND'
            ];
            $the_query['meta_query'] = array_merge($the_meta_query_relation, $the_meta_query);
        }

        // On créé la wp_query avec les paramètres définis
        $query_result = new \WP_Query($the_query);

        return $query_result;
    }
}
