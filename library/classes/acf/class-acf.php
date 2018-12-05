<?php
/**
 * ACF sync field
 *
 * @link https://www.advancedcustomfields.com/resources/acf-settings
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_ACF
{
    const ACF = "acf-pro/acf.php";

    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('woody_theme_update', [$this,'cleanTransient']);
        add_action('woody_subtheme_update', [$this,'cleanTransient']);
        if (WP_ENV == 'dev') {
            add_filter('woody_acf_save_paths', [$this,'acfJsonSave']);
        }
        add_action('create_term', [$this,'cleanTermsChoicesTransient']);
        add_action('edit_term', [$this,'cleanTermsChoicesTransient']);
        add_action('delete_term', [$this,'cleanTermsChoicesTransient']);
        add_filter('acf/settings/load_json', [$this,'acfJsonLoad']);
        add_filter('acf/load_field/type=radio', [$this, 'woodyTplAcfLoadField']);
        add_filter('acf/load_field/type=select', [$this, 'woodyIconLoadField']);

        add_filter('acf/load_field/name=focused_taxonomy_terms', [$this, 'focusedTaxonomyTermsLoadField']);
        add_filter('acf/load_value/name=focused_taxonomy_terms', [$this, 'termsLoadValue'], 10, 3);

        add_filter('acf/load_field/name=list_el_terms', [$this, 'focusedTaxonomyTermsLoadField']);
        add_filter('acf/load_value/name=list_el_terms', [$this, 'termsLoadValue'], 10, 3);

        add_filter('acf/load_field/name=list_filter_custom_terms', [$this, 'focusedTaxonomyTermsLoadField']);
        add_filter('acf/load_value/name=list_filter_custom_terms', [$this, 'termsLoadValue'], 10, 3);

        add_filter('acf/load_field/name=list_filter_taxonomy', [$this, 'pageTaxonomiesLoadField']);
        add_filter('acf/load_value/name=list_filter_taxonomy', [$this, 'termsLoadValue'], 10, 3);

        add_filter('acf/load_field/name=display_elements', [$this, 'displayElementLoadField'], 10, 3);

        add_filter('acf/fields/google_map/api', [$this, 'acfGoogleMapKey']);
        add_filter('acf/location/rule_types', [$this, 'woodyAcfAddPageTypeLocationRule']);
        add_filter('acf/location/rule_values/page_type_and_children', [$this, 'woodyAcfAddPageTypeChoices']);
        add_filter('acf/location/rule_match/page_type_and_children', [$this, 'woodyAcfPageTypeMatch'], 10, 3);
    }

    /**
     * Register ACF Json Save directory
     */
    public function acfJsonSave($groups)
    {
        $groups['default'] = get_template_directory() . '/acf-json';
        return $groups;
    }

    /**
     * Register ACF Json load directory
     */
    public function acfJsonLoad($paths)
    {
        $paths[] = get_template_directory() . '/acf-json';
        return $paths;
    }

    /**
     * Register Raccourci GoogleMapKey
     */
    public function acfGoogleMapKey($api)
    {
        $keys = [
            'AIzaSyAIWyOS5ifngsd2S35IKbgEXXgiSAnEjsw',
            'AIzaSyBMx446Q--mQj9mzuZhb7BGVDxac6NfFYc',
            'AIzaSyB8Fozhi1FKU8oWYJROw8_FgOCbn3wdrhs',
        ];
        $rand_keys = array_rand($keys, 1);
        $api['key'] = $keys[$rand_keys];
        return $api;
    }

    public function sortWoodyTpls()
    {
        $heroes = [
            'hero_01' => 'blocks-hero-tpl_01',
            'hero_02' => 'blocks-hero-tpl_02',
            'hero_03' => 'blocks-hero-tpl_03'
        ];

        $teasers = [
            'teaser_01' => 'blocks-page_teaser-tpl_01',
            'teaser_02' => 'blocks-page_teaser-tpl_02',
            'teaser_03' => 'blocks-page_teaser-tpl_03',
            'teaser_04' => 'blocks-page_teaser-tpl_04'
        ];

        $sections = [
            'section_01' => 'grids_basic-grid_1_cols-tpl_01',
            'section_02' => 'grids_basic-grid_1_cols-tpl_02',
            'section_03' => 'grids_basic-grid_2_cols-tpl_01',
            'section_04' => 'grids_basic-grid_2_cols-tpl_02',
            'section_05' => 'grids_basic-grid_2_cols-tpl_05',
            'section_06' => 'grids_basic-grid_2_cols-tpl_03',
            'section_07' => 'grids_basic-grid_2_cols-tpl_04',
            'section_08' => 'grids_basic-grid_3_cols-tpl_01',
            'section_09' => 'grids_basic-grid_3_cols-tpl_02',
            'section_10' => 'grids_basic-grid_3_cols-tpl_03',
            'section_11' => 'grids_basic-grid_3_cols-tpl_04',
            'section_12' => 'grids_basic-grid_4_cols-tpl_01',
            'section_13' => 'grids_basic-grid_5_cols-tpl_01',
            'section_14' => 'grids_basic-grid_6_cols-tpl_01',
            'section_15' => 'grids_split-grid_2_cols-tpl_06',
            'section_16' => 'grids_split-grid_2_cols-tpl_05',
            'section_17' => 'grids_split-grid_2_cols-tpl_04',
            'section_18' => 'grids_split-grid_2_cols-tpl_01',
            'section_19' => 'grids_split-grid_2_cols-tpl_03',
            'section_20' => 'grids_split-grid_2_cols-tpl_02'
        ];

        $lists_and_focuses = [
            'lists_and_focuses_01' => 'blocks-focus-tpl_103',
            'lists_and_focuses_02' => 'blocks-focus-tpl_112',
            'lists_and_focuses_03' => 'blocks-focus-tpl_104',
            'lists_and_focuses_04' => 'blocks-focus-tpl_113',
            'lists_and_focuses_05' => 'blocks-focus-tpl_105',
            'lists_and_focuses_06' => 'blocks-focus-tpl_102',
            'lists_and_focuses_07' => 'blocks-focus-tpl_101',
            'lists_and_focuses_08' => 'blocks-focus-tpl_110',
            'lists_and_focuses_09' => 'blocks-focus-tpl_106',
            'lists_and_focuses_10' => 'blocks-focus-tpl_107',
            'lists_and_focuses_11' => 'blocks-focus-tpl_108',
            'lists_and_focuses_12' => 'blocks-focus-tpl_109',
            'lists_and_focuses_13' => 'blocks-focus-tpl_114',
            'lists_and_focuses_14' => 'blocks-focus-tpl_111',
            'lists_and_focuses_15' => 'lists-list_grids-tpl_207',
            'lists_and_focuses_16' => 'lists-list_grids-tpl_202',
            'lists_and_focuses_17' => 'lists-list_grids-tpl_209',
            'lists_and_focuses_18' => 'lists-list_grids-tpl_206',
            'lists_and_focuses_19' => 'lists-list_grids-tpl_208',
            'lists_and_focuses_20' => 'lists-list_grids-tpl_203',
            'lists_and_focuses_21' => 'lists-list_grids-tpl_204',
            'lists_and_focuses_22' => 'lists-list_grids-tpl_201',
            'lists_and_focuses_23' => 'lists-list_grids-tpl_205',
            'lists_and_focuses_24' => 'blocks-focus-tpl_201',
            'lists_and_focuses_25' => 'blocks-focus-tpl_310',
            'lists_and_focuses_26' => 'blocks-focus-tpl_301',
            'lists_and_focuses_27' => 'blocks-focus-tpl_304',
            'lists_and_focuses_28' => 'blocks-focus-tpl_308',
            'lists_and_focuses_29' => 'blocks-focus-tpl_306',
            'lists_and_focuses_30' => 'blocks-focus-tpl_309',
            'lists_and_focuses_31' => 'blocks-focus-tpl_303',
            'lists_and_focuses_32' => 'blocks-focus-tpl_307',
            'lists_and_focuses_33' => 'blocks-focus-tpl_311',
            'lists_and_focuses_34' => 'blocks-focus-tpl_302',
            'lists_and_focuses_35' => 'blocks-focus-tpl_305',
            'lists_and_focuses_36' => 'lists-list_grids-tpl_307',
            'lists_and_focuses_37' => 'lists-list_grids-tpl_302',
            'lists_and_focuses_38' => 'lists-list_grids-tpl_309',
            'lists_and_focuses_39' => 'lists-list_grids-tpl_306',
            'lists_and_focuses_40' => 'lists-list_grids-tpl_308',
            'lists_and_focuses_41' => 'lists-list_grids-tpl_303',
            'lists_and_focuses_42' => 'lists-list_grids-tpl_304',
            'lists_and_focuses_43' => 'lists-list_grids-tpl_301',
            'lists_and_focuses_44' => 'lists-list_grids-tpl_305',
            'lists_and_focuses_45' => 'lists-list_grids-tpl_310',
            'lists_and_focuses_46' => 'blocks-focus-tpl_401',
            'lists_and_focuses_47' => 'blocks-focus-tpl_402',
            'lists_and_focuses_48' => 'blocks-focus-tpl_403',
            'lists_and_focuses_49' => 'blocks-focus-tpl_404',
            'lists_and_focuses_50' => 'blocks-focus-tpl_501',
            'lists_and_focuses_51' => 'blocks-focus-tpl_502',
            'lists_and_focuses_52' => 'blocks-focus-tpl_601',
            'lists_and_focuses_53' => 'blocks-focus-tpl_602',
            'lists_and_focuses_54' => 'blocks-focus-tpl_603',
            'lists_and_focuses_55' => 'blocks-focus-tpl_701',
            'lists_and_focuses_56' => 'blocks-focus-tpl_1001',
            'lists_and_focuses_57' => 'blocks-focus_map-tpl_01',
            'lists_and_focuses_58' => 'lists-list_full-tpl_101',
            'lists_and_focuses_59' => 'lists-list_full-tpl_102',
            'lists_and_focuses_60' => 'lists-list_full-tpl_105',
            'lists_and_focuses_61' => 'lists-list_full-tpl_103',
            'lists_and_focuses_62' => 'lists-list_full-tpl_104',
            'lists_and_focuses_63' => 'lists-list_full-tpl_201',
            'lists_and_focuses_64' => 'lists-list_full-tpl_301'
        ];

        $galleries = [
            'gallery_01' => 'blocks-media_gallery-tpl_102',
            'gallery_02' => 'blocks-media_gallery-tpl_103',
            'gallery_03' => 'blocks-media_gallery-tpl_104',
            'gallery_04' => 'blocks-media_gallery-tpl_101',
            'gallery_05' => 'blocks-media_gallery-tpl_105',
            'gallery_06' => 'blocks-media_gallery-tpl_107',
            'gallery_07' => 'blocks-media_gallery-tpl_108',
            'gallery_08' => 'blocks-media_gallery-tpl_106',
            'gallery_09' => 'blocks-media_gallery-tpl_109',
            'gallery_10' => 'blocks-media_gallery-tpl_202',
            'gallery_11' => 'blocks-media_gallery-tpl_203',
            'gallery_12' => 'blocks-media_gallery-tpl_204',
            'gallery_13' => 'blocks-media_gallery-tpl_201',
            'gallery_14' => 'blocks-media_gallery-tpl_205',
            'gallery_15' => 'blocks-media_gallery-tpl_302',
            'gallery_16' => 'blocks-media_gallery-tpl_303',
            'gallery_17' => 'blocks-media_gallery-tpl_304',
            'gallery_18' => 'blocks-media_gallery-tpl_301',
            'gallery_19' => 'blocks-media_gallery-tpl_305',
            'gallery_20' => 'blocks-media_gallery-tpl_403',
            'gallery_21' => 'blocks-media_gallery-tpl_404',
            'gallery_22' => 'blocks-media_gallery-tpl_401',
            'gallery_23' => 'blocks-media_gallery-tpl_405',
            'gallery_24' => 'blocks-media_gallery-tpl_503',
            'gallery_25' => 'blocks-media_gallery-tpl_504',
            'gallery_26' => 'blocks-media_gallery-tpl_501',
            'gallery_27' => 'blocks-media_gallery-tpl_505',
            'gallery_28' => 'blocks-media_gallery-tpl_603',
            'gallery_29' => 'blocks-media_gallery-tpl_604',
            'gallery_30' => 'blocks-media_gallery-tpl_601',
            'gallery_31' => 'blocks-media_gallery-tpl_605',
            'gallery_32' => 'blocks-media_gallery-tpl_206',
            'gallery_33' => 'blocks-media_gallery-tpl_207',
            'gallery_34' => 'blocks-media_gallery-tpl_306',
            'gallery_35' => 'blocks-media_gallery-tpl_307',
            'gallery_36' => 'blocks-media_gallery-tpl_208',
            'gallery_37' => 'blocks-media_gallery-tpl_209',
            'gallery_38' => 'blocks-media_gallery-tpl_210',
            'gallery_39' => 'blocks-media_gallery-tpl_211',
        ];

        $cta = [
            'cta_01' => 'blocks-call_to_action-tpl_01',
            'cta_02' => 'blocks-call_to_action-tpl_02',
            'cta_03' => 'blocks-call_to_action-tpl_05',
            'cta_04' => 'blocks-call_to_action-tpl_03',
            'cta_05' => 'blocks-call_to_action-tpl_04',
        ];

        $socialwalls = [
            'sw_01' => 'blocks-socialwall-tpl_01',
            'sw_02' => 'blocks-socialwall-tpl_02',
            'sw_03' => 'blocks-novascotia-tpl_01'
        ];

        $return = $teasers + $heroes + $sections + $lists_and_focuses + $galleries + $cta + $socialwalls;

        return $return;
    }

    /**
     * Benoit Bouchaud
     * On ajoute les templates Woody disponibles dans les option du champ radio woody_tpl
     */
    public function woodyTplAcfLoadField($field)
    {
        if (strpos($field['name'], 'woody_tpl') !== false) {
            $field['choices'] = [];

            $woodyComponents = get_transient('woody_components');
            if (empty($woodyComponents)) {
                $woodyComponents = Woody::getComponents();
                set_transient('woody_components', $woodyComponents);
            }

            switch ($field['key']) {
                case 'field_5afd2c9616ecd': // Cas des sections
                    $components = Woody::getTemplatesByAcfGroup($woodyComponents, $field['key']);
                break;
                default:
                if (is_numeric($field['parent'])) {
                    // From 08/31/18, return of $field['parent'] is the acf post id instead of the key
                    $parent_field_as_post = get_post($field['parent']);
                    $components = Woody::getTemplatesByAcfGroup($woodyComponents, $parent_field_as_post->post_name);
                } else {
                    $components = Woody::getTemplatesByAcfGroup($woodyComponents, $field['parent']);
                }

            }

            if (!empty($components)) {
                foreach ($components as $key => $component) {
                    $tpl_name = (!empty($component['name'])) ? $component['name'] : '{Noname :/}';
                    $tpl_desc = (!empty($component['description'])) ? $component['description'] : '{Nodesc :/}';

                    $fitted_for = (!empty($component['items_count'][0]['fitted_for'])) ? $component['items_count'][0]['fitted_for'] : '';
                    $accepts_max = (!empty($component['items_count'][0]['accepts_max'])) ? $component['items_count'][0]['accepts_max'] : '';
                    $count_data = [];

                    if (!empty($fitted_for)) {
                        $count_data[] = 'data-fittedfor="' . $fitted_for . '"';
                    }

                    if (!empty($accepts_max)) {
                        $count_data[] = 'data-acceptsmax="' . $accepts_max . '"';
                    }

                    $count_data = implode(' ', $count_data);

                    $field['choices'][$key] = '<div class="tpl-choice-wrapper" ' . $count_data . '>
                    <img class="img-responsive lazyload" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-src="' . WP_HOME . '/app/dist/' . WP_SITE_KEY . '/img/woody-library/views/' . $component['thumbnails']['small'] . '?version=' . get_option('woody_theme_version') . '" alt="' . $key . '" width="150" height="150" />
                    <h5 class="tpl-title">' . $tpl_name . '</h5>
                    <div class="dashicons dashicons-info toggle-desc"></div>
                    <div class="tpl-desc hidden"><h4 class="tpl-title">' . $tpl_name . '</h4>' . $tpl_desc . '<span class="dashicons dashicons-no close-desc"></span></div>
                    <div class="desc-backdrop hidden"></div>
                    </div>';
                    if ($field['name'] == 'section_woody_tpl' || $field['name'] == 'tab_woody_tpl' || $field['name'] == 'slide_woody_tpl') {
                        foreach ($field['choices'] as $name => $value) {
                            if (strpos($name, 'basic-grid_1_cols-tpl_01') !== false) {
                                $field['default_value'] = $name;
                            }
                        }
                    }
                }

                $woody_tpls_order = get_transient('woody_tpls_order');
                if (empty($woody_tpls_order)) {
                    $woody_tpls_order = array_flip($this->sortWoodyTpls());
                    set_transient('woody_tpls_order', $woody_tpls_order);
                }

                foreach ($woody_tpls_order as $order_key => $value) {
                    if (!array_key_exists($order_key, $field['choices'])) {
                        unset($woody_tpls_order[$order_key]);
                    }
                }

                $field['choices'] = array_merge($woody_tpls_order, $field['choices']);
            }
        }

        return $field;
    }

    /**
     * Benoit Bouchaud
     * On ajoute tous les termes de taxonomie du site dans le sélecteur de termes de la mise en avant automatique
     */
    public function focusedTaxonomyTermsLoadField($field)
    {
        // Reset field's choices + create $terms for future choices
        $choices = [];
        $terms = [];

        $lang = $this->getCurrentLang();
        $choices = get_transient('woody_terms_choices');
        if (empty($choices[$lang])) {

            // Get all site taxonomies and exclude those we don't want to use
            $taxonomies = get_object_taxonomies('page', 'objects');

            // Remove useless taxonomies
            $unset_taxonomies = [
                'page_type',
                'post_translations', // Polylang
                'language', // Polylang
            ];

            foreach ($taxonomies as $taxonomy) {
                // Remove useless taxonomies
                if (in_array($taxonomy->name, $unset_taxonomies)) {
                    continue;
                }

                // Get terms for each taxonomy and push them in $terms
                $tax_terms = get_terms(array(
                    'taxonomy' => $taxonomy->name,
                    'hide_empty' => false,
                ));

                foreach ($tax_terms as $term) {
                    if ($term->name == 'Uncategorized') {
                        continue;
                    }
                    $choices[$lang][$term->term_id] = $taxonomy->label . ' - ' . $term->name;
                }
            }

            // Sort by values
            if (!empty($choices[$lang]) && is_array($choices[$lang])) {
                asort($choices[$lang]);
            }

            set_transient('woody_terms_choices', $choices);
        }

        $field['choices'] = (!empty($choices[$lang])) ? $choices[$lang] : [];
        return $field;
    }

    public function pageTaxonomiesLoadField($field)
    {
        $lang = $this->getCurrentLang();
        $choices = get_transient('woody_page_taxonomies_choices');
        if (empty($choices[$lang])) {
            $taxonomies = get_object_taxonomies('page', 'objects');

            foreach ($taxonomies as $key => $taxonomy) {
                $choices[$lang][$taxonomy->name] = $taxonomy->label;
            }

            set_transient('woody_page_taxonomies_choices', $choices);
        }

        $field['choices'] = (!empty($choices[$lang])) ? $choices[$lang] : [];
        return $field;
    }

    /**
     * Léo POIROUX
     * On traduit les termes lors que la synchronisation d'une page dans les blocs Focus ou Liste de contenus
     */
    public function termsLoadValue($value, $post_id, $field)
    {
        $lang = $this->getCurrentLang();
        if (is_array($value) && function_exists('pll_get_term')) {
            foreach ($value as $key => $term_id) {
                $value[$key] = pll_get_term($term_id, $lang);
            }
        }

        return $value;
    }

    /**
    * Benoit Bouchaud
    * On remplit le select "icones" avec les woody-icons disponibles
    */
    public function woodyIconLoadField($field)
    {
        if (strpos($field['name'], 'woody_icon') !== false) {
            $icons = getWoodyIcons();
            foreach ($icons as $key => $icon) {
                $field['choices'][$key] = '<div class="wicon-select"><span class="wicon-woody-icons ' . $key . '"></span><span>' . $icon . '</span></div>';
            }
        }

        return $field;
    }


    public function woodyAcfAddPageTypeLocationRule($choices)
    {
        $choices['Woody']['page_type_and_children'] = 'Type de publication (et ses enfants)';
        return $choices;
    }

    public function woodyAcfAddPageTypeChoices($choices)
    {
        $page_types = $this->getPageTypeTerms();
        foreach ($page_types as $key => $type) {
            $choices[$type->slug] = $type->name;
        }
        return $choices;
    }

    public function woodyAcfPageTypeMatch($match, $rule, $options)
    {
        $page_types = $this->getPageTypeTerms();
        foreach ($page_types as $term) {
            if ($term->slug == $rule['value']) {
                $current_term = $term;
                break;
            }
        }

        $children_terms_ids = [];
        if (!empty($current_term)) {
            foreach ($page_types as $term) {
                if ($term->parent == $current_term->term_id) {
                    $children_terms_ids[] = $term->term_id;
                }
            }
        }

        $selected_term_ids = [];
        if ($options['ajax'] && !empty($options['post_terms']) && !empty($options['post_terms']['page_type'])) {
            $selected_term_ids = $options['post_terms']['page_type'];
        } elseif (!empty($options['post_id'])) {
            $current_page_type = wp_get_post_terms($options['post_id'], 'page_type');
            if (!empty($current_page_type[0]) && !empty($current_page_type[0]->term_id)) {
                $selected_term_ids[] = $current_page_type[0]->term_id;
            }
        }

        // Toujours vide à la création de page
        if (empty($selected_term_ids)) {
            return false;
        }

        foreach ($selected_term_ids as $term_id) {
            if (in_array($term_id, $children_terms_ids) || (!empty($current_term) && $term_id == $current_term->term_id)) {
                $match = true;
            }
        }

        if ($rule['operator'] == "!=") {
            $match = !$match;
        }

        return $match;
    }

    public function displayElementLoadField($field)
    {
        if ($field['key'] == 'field_5bfeaaf039785') {
            return $field;
        }
        $taxonomies = get_transient('woody_website_pages_taxonomies');
        if (empty($taxonomies)) {
            $taxonomies = get_object_taxonomies('page', 'objects');
            unset($taxonomies['language']);
            unset($taxonomies['page_type']);
            unset($taxonomies['post_translations']);

            set_transient('woody_website_pages_taxonomies', $taxonomies);
        }
        foreach ($taxonomies as $key => $taxonomy) {
            $field['choices']['_' . $taxonomy->name] = (!empty($taxonomy->labels->singular_name)) ? $taxonomy->labels->singular_name . ' principal(e)</small>' : $taxonmy->label .' <small>Tag principal</small>';
        }
        return $field;
    }

    public function getPageTypeTerms()
    {
        $page_types = get_transient('woody_terms_page_type');
        if (false === $page_types) {
            $page_types = get_terms(array('taxonomy' => 'page_type', 'hide_empty' => false, 'hierarchical' => true));
            set_transient('woody_terms_page_type', $page_types);
        }

        return $page_types;
    }

    private function getCurrentLang()
    {
        $active_lang = 'fr';

        // Polylang
        if (function_exists('pll_current_language')) {
            $active_lang = pll_current_language();
        }

        return $active_lang;
    }

    public function cleanTransient()
    {
        delete_transient('woody_terms_page_type');
        delete_transient('woody_tpls_order');
        delete_transient('woody_components');
        delete_transient('woody_icons_folder');
        delete_transient('woody_page_taxonomies_choices');
        delete_transient('woody_terms_choices');
        delete_transient('woody_website_pages_taxonomies');
        flush_rewrite_rules();
    }

    public function cleanTermsChoicesTransient()
    {
        delete_transient('woody_page_taxonomies_choices');
        delete_transient('woody_terms_choices');
    }
}
