<?php
/**
 * Menus
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Menus
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_theme_support('menus');
    }

    /**
    *
    * Nom : getMainMenu
    * Auteur : Benoit Bouchaud
    * Return : Retourne les liens du menu principal avec les champs utiles de la page associée
    * @param submenu_depth - Tableau des profondeurs max pour chaque sous menu
    * @param limit - Le nombre maximum d'éléments à remonter
    * @return return - Un tableau
    *
    */
    public static function getMainMenu($limit = 6)
    {
        $return = [];
        $return = self::getMenuLinks(null, 0, $limit);

        if (!empty($return) && is_array($return)) {
            foreach ($return as $key => $value) {
                $return[$key]['submenu'] = self::getSubmenus($value['the_id']);
            }
        }

        return $return;
    }

    public static function getSubmenus($post_id)
    {
        $return = [];

        $fields_groups_wrapper = self::getTheRightOption($post_id);
        if (empty($fields_groups_wrapper) || !is_array($fields_groups_wrapper)) {
            return;
        }

        foreach ($fields_groups_wrapper as $fields_groups) {
            if (empty($fields_groups)) {
                return;
            }
            foreach ($fields_groups as $group_key => $field_group) {
                if (empty($field_group)) {
                    return;
                }
                if (is_array($field_group)) {
                    foreach ($field_group as $field) {
                        if (empty($field)) {
                            return;
                        }
                        if (!is_array($field)) {
                            $return[$group_key]['part_title'] = $field;
                        } else {
                            foreach ($field as $field_data) {
                                $parts[$group_key][] = $field_data['submenu_links_objects'];
                                $return[$group_key]['links'] = self::getMenuLinks($parts[$group_key]);
                            }
                        }
                    }
                } else {
                    $return[$group_key]['links'] = '';
                }
            }
        }

        return $return;
    }

    public static function getTheRightOption($post_id)
    {
        $return = [];

        $return = get_fields('options');

        if (!empty($return) && is_array($return)) {
            foreach ($return as $key => $value) {
                if ($post_id == 17865) {
                    continue;
                }

                if (strpos($key, 'submenu_') === false) {
                    unset($return[$key]);
                }

                if (str_replace('submenu_', '', $key) != $post_id) {
                    unset($return[$key]);
                }
            }
        }

        return $return;
    }

    /**
    *
    * Nom : getMenuLinks
    * Auteur : Benoit Bouchaud
    * Return : Récupère les champs utiles au menu de tous les post enfants du $post_parent
    * @param posts - Un tableau de posts (optionnel)
    * @param post_parent - L'id du post parent
    * @param limit - Le nombre maximum de posts à remonter
    * @return return - Un tableau
    *
    */
    public static function getMenuLinks($posts = [], $post_parent = 0, $limit = -1)
    {
        $return = [];
        if (empty($posts)) {
            $args = array(
                'post_type'        => 'page',
                'post_parent'      => $post_parent,
                'post_status'      => 'publish',
                'order'            => 'ASC',
                'orderby'          => 'menu_order',
                'numberposts'      => $limit
            );
            $posts = get_posts($args);
        }

        if (!empty($posts) && is_array($posts)) {
            foreach ($posts as $key => $post) {
                $return[$key] = [
                    'the_id' => $post->ID,
                    'the_url' => get_permalink($post->ID),
                ];

                $return[$key]['the_fields']['title'] = (!empty(get_field('in_menu_title', $post->ID))) ? get_field('in_menu_title', $post->ID) : $post->post_title;
                $return[$key]['the_fields']['woody_icon'] = (!empty(get_field('in_menu_woody_icon', $post->ID))) ? get_field('in_menu_woody_icon', $post->ID) : '';
                $return[$key]['the_fields']['icon_type'] = 'picto';
                $return[$key]['the_fields']['pretitle'] = (!empty(get_field('in_menu_pretitle', $post->ID))) ? get_field('in_menu_pretitle', $post->ID) : '';
                $return[$key]['the_fields']['subtitle'] = (!empty(get_field('in_menu_subtitle', $post->ID))) ? get_field('in_menu_subtitle', $post->ID) : '';
                $return[$key]['img'] = (!empty(get_field('in_menu_img', $post->ID))) ? get_field('in_menu_img', $post->ID) : get_field('field_5b0e5ddfd4b1b', $post->ID);
            }

            return $return;
        }
    }


    /**
     *
     * Nom : getCompiledSubmenu
     * Auteur : Benoit Bouchaud
     * Return : Récupère les champs utiles au menu de tous les post enfants du $post_parent
     * @param menu_link - Le tableau du lien 0 avec son sous-menu
     * @param menu_display - Un tableau des tpl twigs à appliquer
     * @return return - html
     *
     */
    public static function getCompiledSubmenu($menu_link, $menu_display)
    {
        $return = '';
        $twig_paths = getWoodyTwigPaths();
        if (!empty($menu_link['submenu'])) {
            $the_submenu = [];
            $the_submenu['is_list'] = true;
            $the_submenu['alignment'] = 'align-top';
            $submenu['display'] = $menu_display[$menu_link['the_id']];
            $i = 0;
            foreach ($menu_link['submenu'] as $key => $part) {
                if (!empty($part['links'])) {
                    foreach ($part['links'] as $link_key => $link) {
                        if (!empty($submenu['display']['parts'][$i]['links_tpl'])) {
                            $link_display = $submenu['display']['parts'][$i]['links_tpl'];
                            $part['links'][$link_key] = Timber::compile($twig_paths[$link_display], $link);
                        }
                    }
                }

                $the_part = [];
                $the_part['alignment'] = 'align-top';

                if (!empty($submenu['display']['parts'][$i]['part_tpl'])) {
                    $part_display = $submenu['display']['parts'][$i]['part_tpl'];
                    $the_part['items'] = $part['links'];
                    $the_part['menu_part_title'] = (!empty($part['part_title'])) ? $part['part_title'] : '';
                    $menu_link['submenu'][$key] = Timber::compile($twig_paths[$part_display], $the_part);
                } elseif (!empty($submenu['display']['parts'][$i]['custom_function'])) {
                    $menu_link['submenu'][$key] = $submenu['display']['parts'][$i]['custom_function'];
                }

                $the_submenu['items'][] = $menu_link['submenu'][$key];
                $i++;
            }

            $return = Timber::compile($twig_paths[$submenu['display']['grid_tpl']], $the_submenu);
        }

        return $return;
    }
}