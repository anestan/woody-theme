<?php
/**
 * Template
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

abstract class WoodyTheme_TemplateAbstract
{
    protected $context = [];

    // Force les classes filles à définir cette méthode
    abstract protected function setTwigTpl();
    abstract protected function extendContext();
    abstract protected function registerHooks();
    abstract protected function getHeaders();

    public function __construct()
    {
        add_filter('timber_compile_data', [$this, 'timberCompileData']);

        $this->registerHooks();
        $this->initContext();
        $this->setTwigTpl();
        $this->extendContext();

        $headers = $this->getHeaders();
        if (!empty($headers)) {
            foreach ($headers as $key => $val) {
                // allow val to be an array of value to set
                if (is_array($val)) {
                    foreach ($val as $key2 => $val2) {
                        header($key . ': ' . $val2, false);
                    }
                } else {
                    header($key . ': ' . $val);
                }
            }
        }
    }

    public function timberCompileData($data)
    {
        $data['globals']['post'] = $this->context['post'];
        $data['globals']['post_title'] = $this->context['post_title'];
        $data['globals']['post_id'] = $this->context['post_id'];
        $data['globals']['page_type'] = $this->context['page_type'];
        $data['globals']['woody_options_pages'] = $this->getWoodyOptionsPagesValues();

        return $data;
    }

    public function getWoodyOptionsPagesValues()
    {
        $return = [];

        $return['favorites_url'] = get_field('favorites_page_url', 'options');
        $return['search_url'] = get_field('es_search_page_url', 'options');
        $return['weather_url'] = get_field('weather_page_url', 'options');

        return $return;
    }

    public function render()
    {
        if (!empty($this->twig_tpl) && !empty($this->context)) {
            $this->context = apply_filters('woody_theme_context', $this->context);
            Timber::render($this->twig_tpl, $this->context);
        }
    }

    private function initContext()
    {
        $this->context = Timber::get_context();
        $this->context['title'] = wp_title(null, false);
        $this->context['current_url'] = get_permalink();
        $this->context['site_key'] = WP_SITE_KEY;

        // Default values
        $this->context['timberpost'] = false;
        $this->context['post'] = false;
        $this->context['post_id'] = false;
        $this->context['post_title'] = false;
        $this->context['page_type'] = false;

        $this->context['enabled_woody_options'] = WOODY_OPTIONS;

        /******************************************************************************
         * Sommes nous dans le cas d'une page miroir ?
         ******************************************************************************/
        $mirror_page = getAcfGroupFields('group_5c6432b3c0c45');
        if (!empty($mirror_page['mirror_page_reference'])) {
            $this->context['post_id'] = $mirror_page['mirror_page_reference'];
            $this->context['post'] = get_post($this->context['post_id']);
            $this->context['post_title'] = get_the_title();
            $this->context['metas'][] = sprintf('<link rel="canonical" href="%s" />', get_permalink($this->context['post_id']));
        } else {
            $this->context['post'] = get_post();
            if (!empty($this->context['post'])) {
                $this->context['post_title'] = $this->context['post']->post_title;
                $this->context['post_id'] = $this->context['post']->ID;
            }
        }

        if (!empty($this->context['post'])) {
            $this->context['timberpost'] = Timber::get_post($this->context['post_id']);
            $this->context['page_type'] = getTermsSlugs($this->context['post_id'], 'page_type', true);
        }

        if (!empty($this->context['page_type'])) {
            $this->context['body_class'] = $this->context['body_class'] . ' woodypage-' . $this->context['page_type'];
        }

        // Define Woody Components
        $this->addWoodyComponents();

        // Define SubWoodyTheme_TemplateParts
        $this->addHeaderFooter();

        // GlobalsVars
        $this->addGlobalsVars();

        // GTM
        $this->addGTM();

        // Added Icons
        $this->addIcons();

        // Add langSwitcher
        $this->addLanguageSwitcher();

        // Add langSwitcher
        $this->addSeasonSwitcher();

        // Add addEsSearchBlock
        $this->addEsSearchBlock();

        // Add addFavoritesBlock
        if (in_array('favorites', $this->context['enabled_woody_options'])) {
            $this->addFavoritesBlock();
        }

        // Set a global dist dir
        $this->context['dist_dir'] = WP_DIST_DIR;
    }

    private function addWoodyComponents()
    {
        $this->context['woody_components'] = getWoodyTwigPaths();
    }

    private function addHeaderFooter()
    {
        // Define SubWoodyTheme_TemplateParts
        if (class_exists('SubWoodyTheme_TemplateParts')) {
            $SubWoodyTheme_TemplateParts = new SubWoodyTheme_TemplateParts($this->context['woody_components']);
            if (!empty($SubWoodyTheme_TemplateParts->mobile_logo)) {
                $this->context['mobile_logo'] = $SubWoodyTheme_TemplateParts->mobile_logo;
            }
            if (!empty($SubWoodyTheme_TemplateParts->website_logo)) {
                $this->context['website_logo'] = $SubWoodyTheme_TemplateParts->website_logo;
            }
            $this->context['page_parts'] = $SubWoodyTheme_TemplateParts->getParts();
        }
    }

    private function addGlobalsVars()
    {
        $globals = [
            'post_id' => $this->context['post_id'],
            'post_title' => $this->context['post_title'],
            'page_type' => $this->context['page_type'],
            'woody_options_pages' => $this->getWoodyOptionsPagesValues()
        ];
        $this->context['globals'] = json_encode($globals);
    }

    private function addGTM()
    {
        $this->context['gtm'] = WOODY_GTM;
    }

    private function addIcons()
    {
        // Icons
        //$icons = ['favicon', '16', '32', '64', '120', '128', '152', '167', '180', '192'];
        $icons = ['16', '32', '64', '120', '128', '152', '167', '180', '192'];
        foreach ($icons as $icon) {
            $icon_ext = ($icon == 'favicon') ? $icon . '.ico' : 'favicon.' . $icon . 'w-' . $icon . 'h.png';
            if (file_exists(WP_CONTENT_DIR . '/dist/' . WP_SITE_KEY . '/favicon/' . $icon_ext)) {
                $this->context['icons'][$icon] = WP_HOME . '/app/dist/' . WP_SITE_KEY . '/favicon/' . $icon_ext;
            }
        }
    }

    private function addSeasonSwitcher()
    {
        // Get polylang languages
        $languages = apply_filters('woody_pll_the_seasons', null);

        if (!empty($languages)) {
            $data = $this->createSwitcher($languages);

            // Set a default template
            $tpl_switcher = apply_filters('season_switcher_tpl', null);
            if (empty($tpl_switcher)) {
                $tpl_switcher = 'woody_widgets-season_switcher-tpl_01';
            }

            $template = $this->context['woody_components'][$tpl_switcher];
            $compile_switcher = Timber::compile($template, $data);

            $this->context['season_switcher'] = $compile_switcher;
            $this->context['season_switcher_mobile'] = $compile_switcher;
        }
    }

    private function addLanguageSwitcher()
    {
        // Get polylang languages
        $languages = apply_filters('woody_pll_the_languages', 'auto');

        if (!empty($languages) && count($languages) != 1) {
            $data = $this->createSwitcher($languages);

            // Set a default template
            $template = $this->context['woody_components']['woody_widgets-lang_switcher-tpl_01'];
            $compile_lang = Timber::compile($template, $data);

            $this->context['lang_switcher'] = $compile_lang;
            $this->context['lang_switcher_mobile'] = $compile_lang;
        }
    }

    private function createSwitcher($languages)
    {
        $data = [];

        // Save the $_GET
        $autoselect_id = !empty($_GET['autoselect_id']) ? 'autoselect_id=' . $_GET['autoselect_id'] : '';
        $page = !empty($_GET['page']) ? 'page=' . $_GET['page'] : '';
        $output_params = !empty($autoselect_id) ? $autoselect_id . '&' : '';
        $output_params .= !empty($page) ? $page . '&' : '';
        $output_params = substr($output_params, 0, -1);
        $output_params = !empty($output_params) ? '?' . $output_params : '';

        if (!empty($languages)) {
            foreach ($languages as $language) {
                if (!empty($language['current_lang'])) {
                    $data['current_lang'] = substr($language['locale'], 0, 2);
                    $data['langs'][$language['slug']]['url'] = $language['url'] . $output_params;
                    $data['langs'][$language['slug']]['name'] = strpos($language['name'], '(') ? substr($language['name'], 0, strpos($language['name'], '(')) : $language['name'];
                    $data['langs'][$language['slug']]['locale'] = substr($language['locale'], 0, 2);
                    $data['langs'][$language['slug']]['no_translation'] = $language['no_translation'];
                    $data['langs'][$language['slug']]['is_current'] = true;
                    $data['langs'][$language['slug']]['season'] = !empty($language['season']) ? $language['season'] : '';
                } else {
                    $data['langs'][$language['slug']]['url'] = $language['url'] . $output_params;
                    $data['langs'][$language['slug']]['name'] = strpos($language['name'], '(') ? substr($language['name'], 0, strpos($language['name'], '(')) : $language['name'];
                    $data['langs'][$language['slug']]['locale'] = substr($language['locale'], 0, 2);
                    $data['langs'][$language['slug']]['no_translation'] = $language['no_translation'];
                    $data['langs'][$language['slug']]['season'] = !empty($language['season']) ? $language['season'] : '';
                }
            }
        }

        // Get potential external languages
        if (class_exists('SubWoodyTheme_Languages')) {
            $SubWoodyTheme_Languages = new SubWoodyTheme_Languages($this->context['woody_components']);
            if (method_exists($SubWoodyTheme_Languages, 'languagesCustomization')) {
                $languages_customization = $SubWoodyTheme_Languages->languagesCustomization();
                // if (!empty($languages_customization['template'])) {
                //     $template = $languages_customization['template'];
                // }
                $data['flags'] = (!empty($languages_customization['flags'])) ? $languages_customization['flags'] : false;
                if (!empty($languages_customization['external_langs'])) {
                    foreach ($languages_customization['external_langs'] as $lang_key => $language) {
                        if (!empty($data['langs'][$lang_key])) {
                            $data['langs'][$lang_key]['url'] = $language['url'];
                            $data['langs'][$lang_key]['name'] = $language['name'];
                            $data['langs'][$lang_key]['locale'] = (!empty($language['locale'])) ? substr($language['locale'], 0, 2) : $lang_key;
                            $data['langs'][$lang_key]['target'] = '_blank';
                        } else if (!is_user_logged_in()) {
                            unset($data['langs'][$lang_key]);
                        }
                    }
                }

                if (!is_user_logged_in() and !empty($languages_customization['hide_langs'])) {
                    foreach ($data['langs'] as $lang_key => $language) {
                        if (in_array($language['locale'], $languages_customization['hide_langs'])) {
                            unset($data['langs'][$lang_key]);
                        }
                    }
                }
            }
        }

        if (!empty($data['langs']) && count($data['langs']) == 1) {
            return;
        }

        return $data;
    }

    private function addEsSearchBlock()
    {
        $search_post_id = apply_filters('woody_get_field_option', 'es_search_page_url');
        if (!empty($search_post_id)) {
            $data = [];
            $data['search_url'] = get_permalink(pll_get_post($search_post_id));

            $suggest = apply_filters('woody_get_field_option', 'es_search_block_suggests');
            if (!empty($suggest) && !empty($suggest['suggest_pages'])) {
                $data['suggest']['title'] = __('Nos suggestions', 'woody-theme');
                foreach ($suggest['suggest_pages'] as $page) {
                    $t_page = pll_get_post($page['suggest_page']);
                    if (!empty($t_page)) {
                        $post = Timber::get_post($t_page);
                        if (!empty($post)) {
                            $data['suggest']['pages'][] = getPagePreview(['display_img' => true], $post);
                        }
                    }
                }
            }

            if (class_exists('SubWoodyTheme_esSearch')) {
                $SubWoodyTheme_esSearch = new SubWoodyTheme_esSearch($this->context['woody_components']);
                if (method_exists($SubWoodyTheme_esSearch, 'esSearchBlockCustomization')) {
                    $esSearchBlockCustomization = $SubWoodyTheme_esSearch->esSearchBlockCustomization();
                    if (!empty($esSearchBlockCustomization['template'])) {
                        $template = $esSearchBlockCustomization['template'];
                    }
                }
            }

            // Set a default template
            $tpl = apply_filters('es_search_block_tpl', null);
            $data['tags'] = $tpl['tags'] ?: '';
            $template = $tpl['template'] ?: $this->context['woody_components']['woody_widgets-es_search_block-tpl_01'];

            $timber_compile = Timber::compile($template, $data);

            $this->context['es_search_block'] = $timber_compile;
            $this->context['es_search_block_mobile'] = $timber_compile;
        }
    }

    private function addFavoritesBlock()
    {
        $favorites_post_id = apply_filters('woody_get_field_option', 'favorites_page_url');
        if (!empty($favorites_post_id)) {
            $data = [];
            $data['favorites_page_url'] = get_permalink(pll_get_post($favorites_post_id));

            // Set a default template
            $tpl = apply_filters('favorites_block', null);
            $template = $tpl['template'] ?: $this->context['woody_components']['woody_widgets-favorites_block-tpl_01'];

            $timber_compile = Timber::compile($template, $data);

            $this->context['favorites_block'] = $timber_compile;
            $this->context['favorites_block_mobile'] = $timber_compile;
        }
    }
}
