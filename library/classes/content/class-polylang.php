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
        //add_filter('option_page_on_front', [$this, 'pageOnFront'], 10, 2);
    }

    public function isCacheActive()
    {
        return true;
    }

    /**
     * Translate frontpage
     */
    // public function pageOnFront($value, $option)
    // {
    //     if (pll_current_language() != pll_default_language()) {
    //         $t_value = pll_get_post($value);
    //         return (!empty($t_value)) ? $t_value : $value;
    //     } else {
    //         return $value;
    //     }
    // }

    public function loadThemeTextdomain()
    {
        load_theme_textdomain('woody-theme', get_template_directory() . '/languages');
    }

    /**
     * Commande pour créer automatiquement woody-theme.pot
     * A ouvrir ensuite avec PoEdit.app sous Mac
     * cd ~/www/wordpress/current/web/app/themes/woody-theme
     * wp i18n make-pot . languages/woody-theme.pot
     */
    private function twigExtractPot()
    {
        // Yoast
        __("Page non trouvée %%sep%% %%sitename%%", 'woody-theme');
        __("Erreur 404 : Page non trouvée", 'woody-theme');

        // Woody blocs
        __("M'y rendre", 'woody-theme');
        __("Ajouter à mes favoris", 'woody-theme');
        __("Voir l'itinéraire", 'woody-theme');
        __("Voir la vidéo", 'woody-theme');
        __("Affiner ma recherche", 'woody-theme');
        __("Voir les résultats sur la carte", 'woody-theme');
        __("résultats", 'woody-theme');
        __("Voir la carte", 'woody-theme');
        __("Partager sur Facebook", 'woody-theme');
        __("Partager sur Twitter", 'woody-theme');
        __("Partager sur Google+", 'woody-theme');
        __("Partager sur Instagram", 'woody-theme');
        __("Partager sur Pinterest", 'woody-theme');
        __("Partager par email", 'woody-theme');
        __("Accès au menu principal", 'woody-theme');
        __("Que recherchez-vous ?", "woody-theme");
        __("Rechercher", 'woody-theme');
        __("Réinitialiser", 'woody-theme');
        __("Choisissez vos dates", 'woody-theme');
        __("adulte(s)", 'woody-theme');
        __("enfant(s)", 'woody-theme');
        __("jours", 'woody-theme');
        __("Pages", 'woody-theme');
        __("Offre touristique", 'woody-theme');
        __("Désolé, aucun contenu touristique ne correspond à votre recherche", 'woody-theme');
        __("Désolé, aucune page ne correspond à votre recherche", 'woody-theme');
    }
}
