<?php
/**
 * Polylang
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Polylang
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_filter('pll_is_cache_active', [$this, 'isCacheActive']);
        add_action('after_setup_theme', [$this, 'loadThemeTextdomain']);
    }

    public function isCacheActive()
    {
        return true;
    }

    public function loadThemeTextdomain()
    {
        load_theme_textdomain('woody-theme', get_template_directory() . '/languages');
    }

    private function twigExtractPot()
    {
        // Commande pour créer automatiquement woody-theme.pot
        // A ouvrir ensuite avec PoEdit.app sous Mac
        // cd ~/www/wordpress/current/web/app/themes/woody-theme
        // wp i18n make-pot . languages/woody-theme.pot

        __("M'y rendre", 'woody-theme');
        __("Voir l'itinéraire", 'woody-theme');
        __("Voir la vidéo", 'woody-theme');
        __("Affiner ma recherche", 'woody-theme');
        __("Voir les résultats sur la carte", 'woody-theme');
        __("Voir la carte", 'woody-theme');
    }
}
