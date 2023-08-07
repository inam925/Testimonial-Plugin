<?php
/*
 * Plugin Name: Testimonial Plugin
 * Description: Display news with this plugin.
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists("Tpn_Plugin_Testimonial")) {
    class Tpn_Plugin_Testimonial
    {
        public function __construct()
        {
            $this->tpn_initialize_constants();

            add_action('init', array($this, 'tpn_initialize_constants'));
            add_action('init', array($this, 'tpn_initialize_custom_post_type_and_taxonomies'));

            if (is_admin()) {
                include_once(TPN_PLUGIN_PATH . 'include/admin/class-tpn-admin.php');
            }
            include_once(TPN_PLUGIN_PATH . 'include/front/class-tpn-main-front.php');
        }

        public function tpn_initialize_custom_post_type_and_taxonomies()
        {

            $this->tpn_create_post_type_news();
            $this->tpn_create_news_taxonomies();
        }
        public function tpn_initialize_constants()
        {
            if (!defined('TPN_PLUGIN_PATH')) {
                define('TPN_PLUGIN_PATH', plugin_dir_path(__FILE__));
            }
            if (!defined('TPN_PLUGIN_URL')) {
                define('TPN_PLUGIN_URL', plugins_url(__FILE__));
            }
        }

        public function tpn_create_post_type_news()
        {
            $supports = array(
                'title', // post title
                'editor', // post content
                'author', // post author
                'thumbnail', // featured images
                'excerpt', // post excerpt
                'comments', // post comments
            );
            $labels = array(
                'name' => _x('News', 'plural'),
                'singular_name' => _x('News', 'singular'),
                'menu_name' => _x('News', 'admin menu'),
                'name_admin_bar' => _x('News', 'admin bar'),
                'add_new' => _x('Add New', 'add new'),
                'add_new_item' => __('Add new News'),
                'new_item' => __('New News'),
                'edit_item' => __('Edit News'),
                'view_item' => __('View News'),
                'all_items' => __('All News'),
                'search_items' => __('Search News'),
                'not_found' => __('No News found.'),
            );
            $args = array(
                'supports' => $supports,
                'labels' => $labels,
                'public' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'news'),
                'has_archive' => true,
                'hierarchical' => false,
            );
            register_post_type('news', $args);
        }

        public function tpn_create_news_taxonomies()
        {
            register_taxonomy(
                'news_category',
                'news',
                array(
                    'labels' => array(
                        'name' => 'Categories',
                        'add_new_item' => 'Add New Category',
                        'new_item_name' => "Category Type"
                    ),
                    'show_ui' => true,
                    'hierarchical' => true
                )
            );
        }
    }
}
if (class_exists("Tpn_Plugin_Testimonial")) {
    new Tpn_Plugin_Testimonial();
}
