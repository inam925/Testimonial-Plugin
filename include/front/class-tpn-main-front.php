<?php
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists("Tpn_Class_Main_Front")) {
    class Tpn_Class_Main_Front
    {
        public function __construct()
        {
            add_action('wp_enqueue_scripts', array($this, 'tpn_plugin_enqueue_scripts'));

            add_shortcode('testimonial_plugin', array($this, 'tpn_plugin_testimonial_shortcode'));
            add_shortcode('testimonial_plugin_single_news', array($this, 'tpn_plugin_single_news_shortcode'));

            // AJAX callback to filter posts by category
            add_action('wp_ajax_filter_posts', array($this, 'tpn_wp_filter_posts_callback'));
            add_action('wp_ajax_nopriv_filter_posts', array($this, 'tpn_wp_filter_posts_callback'));

            // AJAX callback to display news item
            add_action('wp_ajax_expand_post', array($this, 'tpn_wp_display_read_more_new_callback'));
            add_action('wp_ajax_nopriv_expand_post', array($this, 'tpn_wp_display_read_more_new_callback'));
        }

        public function tpn_plugin_enqueue_scripts()
        {
            wp_enqueue_style('pluginstyle', plugins_url('../../assets/css/tpn-style.css', __FILE__));

            wp_enqueue_script('filter-script', plugins_url('../../assets/js/tpn-filter-by-category.js', __FILE__), array('jquery'));
            wp_enqueue_script('wp-util');
            wp_localize_script(
                'filter-script',
                'filter_script_object',
                array('ajax_url' => admin_url('admin-ajax.php'))
            );
        }
        public function tpn_wp_include_single_shortcode_function($shortcode)
        {
            if (get_post_type() == 'news') {
                if (is_single()) {
                    $shortcode =  do_shortcode('[testimonial_plugin_single_news]');
                } else {
                }
            }
            return $shortcode;
        }

        public function tpn_plugin_testimonial_shortcode()
        {
?>
            <section>
                <h1 class="main-heading">News</h1>
                <div class="news-body">
                    <div>
                        <div class="categories">
                            <label>Categories</label>
                            <?php
                            $tpn_args = array(
                                'taxonomy' => 'news_category',
                                'hide_empty' => false, // Set to true if you want to hide empty categories
                            );

                            $tpn_categories = get_terms($tpn_args);

                            if (!empty($tpn_categories) && !is_wp_error($tpn_categories)) {
                            ?>
                                <div class="categories-check">
                                    <?php
                                    foreach ($tpn_categories as $category) { ?>
                                        <div>
                                            <input type="checkbox" id="category-<?= $category->term_id ?>" name="categories[]" value="<?= $category->term_id ?>">
                                            <label for="category-<?= $category->term_id; ?>"><?= $category->name ?></label>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                </div>
                                <button id="category-button">Appy filter</button>
                                <button id="display-all">All News</button>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                    <div>
                        <div class="main">
                            <?php
                            $tpn_args = array(
                                'post_type' => 'news',
                                'posts_per_page' => 10 // Display only 10 posts
                            );
                            $tpn_posts = new WP_Query($tpn_args);

                            foreach ($tpn_posts->posts as $post) {
                            ?>
                                <div class="main-news-<?= $post->ID ?>" id="main-news">
                                    <a href="http://localhost/wordpress/single-news/?id=<?= $post->ID ?>">
                                        <small>
                                            <span><?= get_the_post_thumbnail($post->ID, array(100, 100)); ?></span>
                                        </small>
                                    </a>
                                    <small class="title">
                                        <a href="http://localhost/wordpress/single-news/?id=<?= $post->ID ?>" class="news-link">
                                            <strong><?= $post->post_title ?></strong>
                                            <small><?= $post->post_excerpt ?></small>
                                            <small><?= get_the_author_meta('display_name', $post->post_author); ?></small>
                                        </a>
                                        <div class="expanded-content-<?= $post->ID ?>" id="expanded-news">
                                            <a href="http://localhost/wordpress/single-news/?id=<?= $post->ID ?>">
                                                <div id="exp-content" class="expanded-news-<?= $post->ID ?>">
                                                    <!-- Expanded content will be added here -->
                                                </div>
                                            </a>
                                            <button value="<?= $post->ID ?>" class="close-button">Read less</button>
                                        </div>
                                        <button value="<?= $post->ID ?>" class="news-item-button read-more-button-<?= $post->ID ?>">Read more</button>
                                    </small>
                                </div>
                            <?php } ?>
                        </div>
                        <!-- Display page navigation -->
                        <div class="pagination-links">
                            <?php
                            if (isset($tpn_posts->max_num_pages) && $tpn_posts->max_num_pages > 1) {
                            ?>
                                <?php
                                global $tpn_paged;
                                if (!$tpn_paged) {
                                    $tpn_paged = 1;
                                }
                                for ($i = 1; $i <= $tpn_posts->max_num_pages; $i++) {
                                    echo '<a href="' . get_pagenum_link($i) . '" class="' . ($tpn_paged == $i ? 'current' : '') . '">' . $i . '</a>';
                                }
                                ?>
                            <?php } ?>
                        </div>
                    </div>
                    <div>
                        <div class="latest-news">
                            <?php
                            $tpn_args = array(
                                'post_type' => 'news',
                                'posts_per_page' => 5 // Display only 5 posts
                            );
                            $tpn_latest_posts = new WP_Query($tpn_args); ?>
                            <header>
                                <span>Trending Now</span>
                            </header>
                            <?php
                            foreach ($tpn_latest_posts->posts as $post) {
                            ?>
                                <div class="latest">
                                    <span><a href="http://localhost/wordpress/single-news/?id=<?= $post->ID ?>"><?= get_the_post_thumbnail($post->ID, array(60, 60)); ?></a></span>
                                    <span class="trending">
                                        <a href="http://localhost/wordpress/single-news/?id=<?= $post->ID ?>" class="title-link">
                                            <small class="trending-title"><?= $post->post_title ?></small>
                                            <small class="trending-author"><?= get_the_author_meta('display_name', $tpn_latest_posts->post_author); ?></small>
                                        </a>
                                        <small class="expanded-title-<?= $post->ID ?>" id="expanded-title">
                                            <a href="http://localhost/wordpress/single-news/?id=<?= $post->ID ?>">
                                                <small id="exp-title" class="expanded-news-<?= $post->ID ?>">
                                                    <!-- Expanded content will be added here -->
                                                </small>
                                            </a>
                                        </small>
                                        <button value="<?= $post->ID ?>" class="news-title-button read-more-title-button-<?= $post->ID ?>">Read more</button>
                                        <button value="<?= $post->ID ?>" class="close-title-button-<?= $post->ID ?>" id="close-title-button">Read less</button>
                                    </span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php
        }

        public function tpn_plugin_single_news_shortcode()
        {
        ?>
            <section class="single-news">
                <?php
                $single_news_id = $_GET['id'];
                $single_news_post = get_post($single_news_id);

                if ($single_news_post) { ?>
                    <header class="main-heading">
                        <h1>News</h1>
                    </header>
                    <div class="news-body">
                        <div class="news-item-body">
                            <div class="news-item-img">
                                <?= get_the_post_thumbnail($single_news_post->ID, array(700, 500)); ?>
                            </div>
                            <article class="news-item">

                                <strong class="news-item-title"><?= $single_news_post->post_title; ?></strong><br />
                                <em>Last Modified at: <?= get_the_modified_date('Y-m-d H:i:s', $single_news_post->ID); ?></em>
                                <em>Category: <?php the_terms($single_news_post->ID, 'news_category', ' '); ?></em>
                                <em>Author: <?= get_the_author_meta('display_name', $single_news_post->post_author); ?></em>
                                <em>Desgination: <?= get_post_meta($single_news_post->ID, 'author_designation_field', true) ?></em>
                                <div class="news-item-content"><?= $single_news_post->post_content; ?></div>
                            </article>
                            <div class="news-home">
                                <button><a href="http://localhost/wordpress/page/">Back to News</a></button>
                            </div>
                        </div>
                        <div>
                            <div class="latest-news">
                                <?php
                                $tpn_args = array(
                                    'post_type' => 'news',
                                    'posts_per_page' => 5
                                );
                                $tpn_latest_posts = new WP_Query($tpn_args); ?>
                                <header>
                                    <span>Trending Now</span>
                                </header>
                                <?php
                                foreach ($tpn_latest_posts->posts as $post) {
                                ?>
                                    <!-- Display news title -->
                                    <div class="latest">
                                        <span><a href="http://localhost/wordpress/single-news/?id=<?= $post->ID ?>"><?= get_the_post_thumbnail($post->ID, array(60, 60)); ?></a></span>
                                        <span class="trending">
                                            <a href="http://localhost/wordpress/single-news/?id=<?= $post->ID ?>" class="title-link">
                                                <small class="trending-title"><?= $post->post_title ?></small>
                                                <small class="trending-author"><?= get_the_author_meta('display_name', $tpn_latest_posts->post_author); ?></small>
                                            </a>
                                            <small class="expanded-title-<?= $post->ID ?>" id="expanded-title">
                                                <a href="http://localhost/wordpress/single-news/?id=<?= $post->ID ?>">
                                                    <small id="exp-title" class="expanded-news-<?= $post->ID ?>">
                                                        <!-- Expanded content will be added here -->
                                                    </small>
                                                </a>
                                            </small>
                                            <button value="<?= $post->ID ?>" class="news-title-button read-more-title-button-<?= $post->ID ?>">Read more</button>
                                            <button value="<?= $post->ID ?>" class="close-title-button-<?= $post->ID ?>" id="close-title-button">Read less</button>
                                        </span>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                <?php } else { ?>
                    <p>No news found.</p>
                <?php } ?>
            </section>

            <?php
        }

        public function tpn_wp_filter_posts_callback()
        {
            // Retrieve the selected categories and page number from the AJAX request
            $selectedCategories = $_POST['categories']; // An array of selected category IDs
            $paged = $_POST['page'] ? (int)$_POST['page'] : 1;

            $args = array(
                'post_type' => 'news',
                'posts_per_page' => 10,
                'paged' => $paged,
            );

            if (!empty($selectedCategories)) {
                // Convert selected category IDs to integers
                $selectedCategories = array_map('intval', $selectedCategories);

                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'news_category',
                        'field' => 'term_id',
                        'terms' => $selectedCategories,
                    ),
                );
            }

            // Query the posts
            $filteredPosts = new WP_Query($args);

            ob_start();

            if ($filteredPosts->have_posts()) {
                foreach ($filteredPosts->posts as $post) {
                    // Display the post content or any other information you want
            ?>
                    <div class="main-news-<?= $post->ID ?>" id="main-news">
                        <a href="http://localhost/wordpress/single-news/?id=<?= $post->ID ?>">
                            <small>
                                <span><?= get_the_post_thumbnail($post->ID, array(100, 100)); ?></span>
                            </small>
                        </a>
                        <!-- Display news title and excerpt -->
                        <small class="title">
                            <a href="http://localhost/wordpress/single-news/?id=<?= $post->ID ?>" class="news-link">
                                <strong><?= $post->post_title ?></strong>
                                <small><?= $post->post_excerpt ?></small>
                                <small><?= get_the_author_meta('display_name', $post->post_author); ?></small>
                            </a>
                            <div class="expanded-content-<?= $post->ID ?>" id="expanded-news">
                                <a href="http://localhost/wordpress/single-news/?id=<?= $post->ID ?>">
                                    <div id="exp-content" class="expanded-news-<?= $post->ID ?>">
                                        <!-- Expanded content will be added here -->
                                    </div>
                                </a>
                                <button value="<?= $post->ID ?>" class="close-button">Read less</button>
                            </div>
                            <button value="<?= $post->ID ?>" class="news-item-button read-more-button-<?= $post->ID ?>">Read more</button>
                        </small>
                    </div>
<?php
                }
            } else {
                echo 'No posts found.';
            }
            $output = ob_get_clean();
            $response = array(
                'filtered_posts' => $output,
                'max_num_pages' => $filteredPosts->max_num_pages,
            );
            wp_send_json_success($response);
        }

        public function tpn_wp_display_read_more_new_callback()
        {
            // Retrieve the selected category from the AJAX request
            $postId = $_POST['id'];
            // Set up the query arguments to retrieve the post
            $args = array(
                'post_type' => 'news',
                'p' => $postId,
            );
            // Query the post
            $readContent = new WP_Query($args);
            $response = array();

            if ($readContent->have_posts()) {
                $readContent->the_post();
                $response['content'] = get_the_content();
            } else {
                $response['content'] = 'No posts found.';
            }

            // Send the response as JSON
            wp_send_json($response);
            wp_die();
        }
    }
}
if (class_exists("Tpn_Class_Main_Front")) {
    new Tpn_Class_Main_Front();
}
