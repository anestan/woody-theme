<?php

/**
 * Woody Theme Process
 * @author Benoit BOUCHAUD
 * @copyright Raccourci Agency 2020
 */

namespace WoodyTheme\Process;

trait WoodyThemeTrait_WoodyProcessQuery
{
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
    public function processWoodyQuery($the_post, $query_form, $paginate = false, $uniqid = 0, $ignore_maxnum = false, $posts_in, $filters)
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
            $operator = "IN";

            // On récupère la relation choisie (ET/OU) entre les termes
            // et on génère un tableau de term_id pour chaque taxonomie
            // Si la valeur est NONE, on passe en relation AND et on change l'operator de IN en NOT IN (correspond a AUCUN DES TERMES)
            if (!empty($query_form['focused_taxonomy_terms_andor']) && $query_form['focused_taxonomy_terms_andor'] == "NONE") {
                $tax_query['custom_tax']['relation'] = "AND";
                $operator = "NOT IN";
            } else {
                $tax_query['custom_tax']['relation'] = (!empty($query_form['focused_taxonomy_terms_andor'])) ? $query_form['focused_taxonomy_terms_andor'] : 'OR';
            }

            // Si la valeur n'est pas un tableau (== int), on pousse cette valeur dans un tableau
            if (is_numeric($query_form['focused_taxonomy_terms'])) {
                $query_form['focused_taxonomy_terms'] = [$query_form['focused_taxonomy_terms']];
            }

            // Pour chaque entrée du tableau focus_taxonomy_terms
            foreach ($query_form['focused_taxonomy_terms'] as $focused_term) {
                // Si l'entrée est un post id (Aucun filtre n'a été utilisé en front)
                $term = get_term($focused_term);
                if (!empty($term) && !is_wp_error($term) && is_object($term)) {
                    $custom_tax[$term->taxonomy][] = $focused_term;
                }

                foreach ($custom_tax as $taxo => $terms) {
                    foreach ($terms as $term) {
                        $tax_query['custom_tax'][] = array(
                            'taxonomy' => $taxo,
                            'terms' => [$term],
                            'field' => 'term_id',
                            'operator' => $operator
                        );
                    }
                }
            }
        } elseif (!empty($query_form['filtered_taxonomy_terms'])) { // Si des filtres de taxonomie ont été utilisés en front

            // On applique le comportement entre TOUS les filtres
            $tax_query['custom_tax']['relation'] = 'AND';

            // Pour chaque séléction de filtre envoyée, on créé une custom_tax
            foreach ($query_form['filtered_taxonomy_terms'] as $filter_key => $term_filter) {

                // On récupère l'index du filtre dans la clé du param GET
                $exploded_key = explode('_', $filter_key);
                $index = $exploded_key[2];

                $tax_query['custom_tax'][$index] = [];

                // On récupère la relation AND/OR choisie dans le backoffice
                $tax_query['custom_tax'][$index] = [
                    'relation' => (!empty($filters['list_filters'][$index]['list_filter_andor'])) ? $filters['list_filters'][$index]['list_filter_andor'] : 'OR'
                ];

                // Si on reçoit le paramètre en tant qu'identifiant (select/radio) => on le pousse dans un tableau
                $term_filter = (!is_array($term_filter)) ? [$term_filter] : $term_filter;

                foreach ($term_filter as $term) {
                    $the_wp_term = get_term($term);
                    $tax_query['custom_tax'][$index][] = array(
                        'taxonomy' => $the_wp_term->taxonomy,
                        'terms' => [$term],
                        'field' => 'term_id',
                        'operator' => 'IN'
                    );
                }
            }
        }

        // On retourne les contenus dont le prix et compris entre 2 valeurs
        if (!empty($query_form['focused_trip_price'])) {
            if (!empty($query_form['focused_trip_price']['min'])) {
                $the_meta_query[] = [
                    'key'        => 'the_price_price',
                    'value'        => $query_form['focused_trip_price']['min'],
                    'type'      => 'NUMERIC',
                    'compare'    => '>='
                ];
            }
            if (!empty($query_form['focused_trip_price']['max'])) {
                $the_meta_query[] = [
                    'key'        => 'the_price_price',
                    'value'        => $query_form['focused_trip_price']['max'],
                    'type'      => 'NUMERIC',
                    'compare'    => '<='
                ];
            }
        }

        // On retourne les contenus dont la durée et comprise entre 2 valeurs
        if (!empty($query_form['focused_trip_duration'])) {
            if (!empty($query_form['focused_trip_duration']['min'])) {
                $the_meta_query[] = [
                    'key'        => 'the_duration_count_days',
                    'value'        => $query_form['focused_trip_duration']['min'],
                    'type'      => 'NUMERIC',
                    'compare'    => '>='
                ];
            }
            if (!empty($query_form['focused_trip_duration']['max'])) {
                $the_meta_query[] = [
                    'key'        => 'the_duration_count_days',
                    'value'        => $query_form['focused_trip_duration']['max'],
                    'type'      => 'NUMERIC',
                    'compare'    => '<='
                ];
            }
        }

        // On trie les contenus en fonction d'un ordre donné
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
                $orderby = 'rand';
                $order = 'ASC';
        }

        // On enregistre le tri aléatoire pour la journée en cours (pagination)
        if ($orderby == 'rand' && $paginate == true) {
            $seed = (!empty($query_form['seed'])) ? $query_form['seed'] : date("dmY");
            $orderby = 'RAND(' . $seed . ')';
        }

        // On créé la wp_query en fonction des choix faits dans le backoffice
        // NB : si aucun choix n'a été fait, on remonte automatiquement tous les contenus de type page
        $the_query = [
            'post_type' => 'page',
            'posts_per_page' => (!empty($query_form['focused_count'])) ? $query_form['focused_count'] : 12,
            'post_status' => 'publish',
            'post__not_in' => array($the_post->ID),
            'order' => $order,
            'orderby' => $orderby,
        ];

        if (!empty($posts_in)) {
            $the_query['post__in'] = $posts_in;
        }

        // Retourne tous les posts correspondant à la query
        if ($ignore_maxnum === true) {
            $the_query['posts_per_page'] = -1;
        }

        // On récupère l'offset de la page
        if ($paginate == true) {
            $the_page_offset = (!empty($_GET[$uniqid])) ? htmlentities(stripslashes($_GET[$uniqid])) : '';
            $the_query['paged'] = (!empty($the_page_offset)) ? $the_page_offset : 1;
        }

        // On ajoute la tax_query
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

        // On passe les arguments dans un filtre
        $the_query = apply_filters('custom_process_woody_query_arguments', $the_query, $query_form);

        // On créé la wp_query avec les paramètres définis
        $query_result = new \WP_Query($the_query);

        // Si on ordonne par geoloc, il faut trier les résultats reçus
        $query_result = apply_filters('custom_process_woody_query', $query_result, $query_form, $the_post);

        return $query_result;
    }
}
