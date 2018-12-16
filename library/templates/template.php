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
                }
                else {
                    header($key . ': ' . $val);
                }
            }
        }
    }

    public function render()
    {
        if (!empty($this->twig_tpl) && !empty($this->context)) {
            Timber::render($this->twig_tpl, $this->context);
        }
    }

    private function initContext()
    {
        $this->context = Timber::get_context();
        $this->context['title'] = wp_title(null, false);
        $this->context['current_url'] = get_permalink();

        // Get current Post
        $this->context['post'] = new TimberPost();
        $this->context['page_type'] = getTermsSlugs($this->context['post']->ID, 'page_type', true);

        // Define Woody Components
        $this->addWoodyComponents();

        // Define SubWoodyTheme_TemplateParts
        $this->addHeaderFooter();

        // GTM
        $this->addGTM();

        // Added SiteConfig
        $this->addSiteConfig();

        // Added Icons
        $this->addIcons();

        // Add langSwitcher
        $this->addLanguageSwitcher();

        // Add addEsSearchBlock
        $this->addEsSearchBlock();

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
            if (!empty($SubWoodyTheme_TemplateParts->website_logo)) {
                $this->context['website_logo'] = $SubWoodyTheme_TemplateParts->website_logo;
            }
            $this->context['page_parts'] = $SubWoodyTheme_TemplateParts->getParts();
        }
    }

    private function addGTM()
    {
        $this->context['gtm'] = RC_GTM;
    }

    private function addSiteConfig()
    {
        // Site Config
        $this->context['site_config'] = [];
        $this->context['site_config']['site_key'] = WP_SITE_KEY;
        $credentials = get_option('woody_credentials');
        if (!empty($credentials['public_login']) && !empty($credentials['public_password'])) {
            $this->context['site_config']['login'] = $credentials['public_login'];
            $this->context['site_config']['password'] = $credentials['public_password'];
            $this->context['site_config'] = json_encode($this->context['site_config']);
        }
    }

    private function addIcons()
    {
        // Icons
        $icons = ['favicon', '16', '32', '64', '120', '128', '152', '167', '180', '192'];
        foreach ($icons as $icon) {
            $icon_ext = ($icon == 'favicon') ? $icon . '.ico' : 'favicon.' . $icon . 'w-' . $icon . 'h.png';
            if (file_exists(WP_CONTENT_DIR . '/dist/' . WP_SITE_KEY . '/favicon/' . $icon_ext)) {
                $this->context['icons'][$icon] = WP_HOME . '/app/dist/' . WP_SITE_KEY . '/favicon/' . $icon_ext;
            }
        }
    }

    private function addLanguageSwitcher()
    {
        $data = [];

        if (!function_exists('pll_the_languages')) {
            return;
        }

        // Save the $_GET
        $autoselect_id = !empty($_GET['autoselect_id']) ? 'autoselect_id='.$_GET['autoselect_id'] : '';
        $page = !empty($_GET['page']) ? 'page='.$_GET['page'] : '';
        $output_params = !empty($autoselect_id) ? $autoselect_id.'&' : '';
        $output_params .= !empty($page) ? $page.'&' : '';
        $output_params = substr($output_params, 0, -1);
        $output_params = !empty($output_params) ? '?'.$output_params : '';

        // Get polylang languages
        $languages = pll_the_languages(array(
            'display_names_as'       => 'slug',
            'hide_if_no_translation' => 0,
            'raw'                    => true
        ));

        if (!empty($languages)) {
            foreach ($languages as $language) {
                if (!empty($language['current_lang'])) {
                    $data['current_lang'] = $language['slug'];
                } else {
                    // $data['langs'][$language['slug']]['id'] = $language['id'];
                    $data['langs'][$language['slug']]['url'] = $language['url'] . $output_params;
                    $data['langs'][$language['slug']]['no_translation'] = $language['no_translation'];
                }
            }
        }

        // Get potential external languages
        if (class_exists('SubWoodyTheme_Languages')) {
            $SubWoodyTheme_Languages = new SubWoodyTheme_Languages($this->context['woody_components']);
            if (method_exists($SubWoodyTheme_Languages, 'languagesCustomization')) {
                $languages_customization = $SubWoodyTheme_Languages->languagesCustomization();
                if (!empty($languages_customization['template'])) {
                    $template = $languages_customization['template'];
                }
                $data['flags'] = (!empty($languages_customization['flags'])) ? $languages_customization['flags'] : false;
                if (!empty($languages_customization['external_langs'])) {
                    foreach ($languages_customization['external_langs'] as $lang_key => $language) {
                        $data['langs'][$lang_key]['url'] = $language['url'];
                        $data['langs'][$lang_key]['name'] = $language['name'];
                        $data['langs'][$lang_key]['target'] = '_blank';
                    }
                }
            }
        }

        if (empty($data['langs'])) {
            return;
        }

        // Set a default template
        $template = $this->context['woody_components']['woody_widgets-lang_switcher-tpl_01'];

        $this->context['lang_switcher'] = Timber::compile($template, $data);
    }

    private function addEsSearchBlock(){
        $data = [];

        $data['search_url'] = get_field('es_search_page_url', 'option');
        if(empty($data['search_url'])){
            return;
        }

        $suggest = get_field('es_search_block_suggests', 'option');
        if(!empty($suggest) && !empty($suggest['suggest_pages'])){
            $data['suggest']['title'] = (!empty($suggest['suggest_title'])) ? $suggest['suggest_title'] : '';
            foreach ($suggest['suggest_pages'] as $page) {
                $post = Timber::get_post($page['suggest_page']);
                $data['suggest']['pages'][] = getPagePreview('', $post);
            }
        }

        if (class_exists('SubWoodyTheme_esSearch')) {
            $SubWoodyTheme_esSearch = new SubWoodyTheme_esSearch($this->context['woody_components']);
            if (method_exists($SubWoodyTheme_esSearch, 'esSearchBlockCustomization')) {
                $esSearchBlockCustomization = $SubWoodyTheme_esSearch->esSearchBlockCustomization();
                if (!empty($esSearchBlockCustomization['template'])) {
                    $template = $languages_customization['template'];
                }
            }
        }

        // Set a default template
        $template = $this->context['woody_components']['woody_widgets-es_search_block-tpl_01'];

        $this->context['es_search_block'] = Timber::compile($template, $data);
    }
}
