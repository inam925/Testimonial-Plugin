<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists("Tpn_Plugin_Class_Admin")) {
    class Tpn_Plugin_Class_Admin
    {
        public function __construct()
        {
            add_action('add_meta_boxes', array($this, 'author_custom_field'));
            add_action('save_post_news', array($this, 'save_author_custom_field'));
        }

        public function author_custom_field()
        {
            add_meta_box(
                'author_designation_field',
                'Author Designation',
                array($this, 'display_author_custom_field'),
                'news',
                'normal',
                'low'
            );
        }

        public function display_author_custom_field($post)
        {
            $author_field_value = get_post_meta($post->ID, 'author_designation_field', true);
?>
            <label for="author_field">Desgination:</label>
            <input type="text" id="author_field" name="author_field" value="<?= $author_field_value ?>">
<?php
        }

        public function save_author_custom_field($post_id)
        {
            if (isset($_POST['author_field'])) {
                $author_field_value = sanitize_text_field($_POST['author_field']);
                update_post_meta($post_id, 'author_designation_field', $author_field_value);
            }
        }
    }
}
if (class_exists("Tpn_Plugin_Class_Admin")) {
    new Tpn_Plugin_Class_Admin();
}
