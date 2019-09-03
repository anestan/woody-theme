<?php

/**
 * Robots
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Robots
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_filter('robots_txt', [$this, 'robotsTxt'], 10, 2);
    }

    public function robotsTxt($output, $public)
    {
        if ('0' != $public) {
            // Add Disallow
            $output = [
                '# Woody Robots Public ' . WP_SITE_KEY . ' (' . WP_ENV . ')',
                '# Generated by Raccourci Agency',
                'User-agent: *',
                'Disallow: /wp/',
                'Disallow: /*.php$',
                'Disallow: /*.twig$',
                'Disallow: /*.inc$',
                'Disallow: /*?*p=',
                'Disallow: /*obf.js',
            ];

            // Add Sitemap
            $output[] = 'Sitemap: ' . str_replace('/wp', '/sitemap.xml', site_url());
        } else {
            $output = [
                '# Woody Robots Private ' . WP_SITE_KEY . ' (' . WP_ENV . ')',
                '# Generated by Raccourci Agency',
                'User-agent: *',
                'Disallow: /',
            ];
        }

        // Added custom filter
        $output = apply_filters('woody_robots_txt', $output, $public);

        // Implode
        $output = implode("\n", $output);

        return $output;
    }
}
