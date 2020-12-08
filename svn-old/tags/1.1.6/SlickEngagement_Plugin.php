<?php

include_once 'SlickEngagement_LifeCycle.php';
include_once 'SlickEngagement_Widgets.php';

class SlickEngagement_Plugin extends SlickEngagement_LifeCycle
{
/**
 * See: http://plugin.michael-simpson.com/?page_id=31
 * @return array of option meta data.
 */
    public function getOptionMetaData()
    {
//  http://plugin.michael-simpson.com/?page_id=31
        return array(
            'SiteCode' => array(__('Site Code', 'slick-engagement')),
            'SlickServerUrl' => array(__('Support code (support use only)', 'slick-engagement')),
        );
    }

//    protected function getOptionValueI18nString($optionValue) {
    //        $i18nValue = parent::getOptionValueI18nString($optionValue);
    //        return $i18nValue;
    //    }

    protected function initOptions()
    {
        $options = $this->getOptionMetaData();
        if (!empty($options)) {
            foreach ($options as $key => $arr) {
                if (is_array($arr) && count($arr) > 1) {
                    $this->addOption($key, $arr[1]);
                }
            }
        }
    }

    public function getPluginDisplayName()
    {
        return 'Slick Engagement';
    }

    protected function getMainPluginFileName()
    {
        return 'slick-engagement.php';
    }

/**
 * See: http://plugin.michael-simpson.com/?page_id=101
 * Called by install() to create any database tables if needed.
 * Best Practice:
 * (1) Prefix all table names with $wpdb->prefix
 * (2) make table names lower case only
 * @return void
 */
    protected function installDatabaseTables()
    {
//        global $wpdb;
        //        $tableName = $this->prefixTableName('mytable');
        //        $wpdb->query("CREATE TABLE IF NOT EXISTS `$tableName` (
        //            `id` INTEGER NOT NULL");
    }

/**
 * See: http://plugin.michael-simpson.com/?page_id=101
 * Drop plugin-created tables on uninstall.
 * @return void
 */
    protected function unInstallDatabaseTables()
    {
//        global $wpdb;
        //        $tableName = $this->prefixTableName('mytable');
        //        $wpdb->query("DROP TABLE IF EXISTS `$tableName`");
    }

/**
 * Perform actions when upgrading from version X to version Y
 * See: http://plugin.michael-simpson.com/?page_id=35
 * @return void
 */
    public function upgrade()
    {
    }

    public function addActionsAndFilters()
    {
// Add options administration page
        // http://plugin.michael-simpson.com/?page_id=47
        add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));

// Example adding a script & style just for the options administration page
        // http://plugin.michael-simpson.com/?page_id=47
        //        if (strpos($_SERVER['REQUEST_URI'], $this->getSettingsSlug()) !== false) {
        //            wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));
        //            wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
        //        }

// Add Actions & Filters
        // http://plugin.michael-simpson.com/?page_id=37

        add_action('wp_head', array(&$this, 'addSlickPageHeader'));

// Adding scripts & styles to all pages
        // Examples:
        //        wp_enqueue_script('jquery');
        //        wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
        //        wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));

// Register short codes
        // http://plugin.michael-simpson.com/?page_id=39

        add_shortcode('slick-film-strip', array($this, 'doFilmStripShortcode'));
        add_shortcode('slick-game', array($this, 'doGameShortcode'));
        // add_shortcode('slick-next-up', array($this, 'doNextUpShortcode'));
        add_shortcode('slick-story', array($this, 'doSlickStoryShortcode'));

// Register AJAX hooks
        // http://plugin.michael-simpson.com/?page_id=41

// Ensure pages can be configured with categories and tags
        add_action('init', array(&$this, 'add_taxonomies_to_pages'));

        $prefix = is_network_admin() ? 'network_admin_' : '';
        $plugin_file = plugin_basename($this->getPluginDir() . DIRECTORY_SEPARATOR . $this->getMainPluginFileName()); //plugin_basename( $this->getMainPluginFileName() );
        $this->guildLog('Adding filter ' . "{$prefix}plugin_action_links_{$plugin_file}");
        add_filter("{$prefix}plugin_action_links_{$plugin_file}", array(&$this, 'onActionLinks'));
    }

    public function onActionLinks($links)
    {
        $this->guildLog('onActionLinks ' . admin_url('options-general.php?page=SlickEngagement_PluginSettings'));
        $mylinks = array('<a href="' . admin_url('options-general.php?page=SlickEngagement_PluginSettings') . '">Settings</a>');
        return array_merge($links, $mylinks);
    }

    public function doFilmStripShortcode()
    {
        return '<div class="slick-widget slick-film-strip slick-shortcode"></div>';
    }

    public function doGameShortcode()
    {
        return '<div class="slick-widget slick-game-panel slick-shortcode"></div>';
    }

    // public function doNextUpShortcode()
    // {
    //     return '<div class="slick-widget slick-next-up slick-shortcode"></div>';
    // }

    public function doSlickStoryShortcode($attrs, $content, $tag)
    {
        extract(shortcode_atts(array('src' => ''), $attrs));
        $output = '<div class="slick-story-container">';
        $output .= '<script async src="https://stories.slickstream.com/e2/story-embed.js"></script>';
        $output .= '<slick-story-viewer src="' . $src . '"></slick-story-viewer>';
        $output .= '</div>';
        return $output;
    }

    public function add_taxonomies_to_pages()
    {
        register_taxonomy_for_object_type('post_tag', 'page');
        register_taxonomy_for_object_type('category', 'page');
    }

/* determine whether post has a featured image, if not, find the first image inside the post content, $size passes the thumbnail size, $url determines whether to return a URL or a full image tag*/
/* adapted from http://www.amberweinberg.com/wordpress-find-featured-image-or-first-image-in-post-find-dimensions-id-by-url/ */

    public function getPostImage($post)
    {
        ob_start();
        ob_end_clean();

/*If there's a featured image, show it*/

        if (has_post_thumbnail($post)) {
            $images = wp_get_attachment_image_src(get_post_thumbnail_id($post), 'single-post-thumbnail');
            return $images[0];
        } else {
            $content = $post->post_content;
            $first_img = '';
            $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content, $matches);
            $first_img = $matches[1][0];

            /*No featured image, so we get the first image inside the post content*/

            if ($first_img) {
                return $first_img;
            } else {
                return null;
            }
        }
    }

    public function removeSemicolons($value)
    {
        return str_replace(';', ' ', $value);
    }

    public function addSlickPageHeader()
    {
        global $post;
        echo "\n";
        echo '<meta property="slick:wpversion" content="1.1.6" />' . "\n";
        $siteCode = trim($this->getOption('SiteCode'));
        if ($siteCode) {
            $serverUrl = trim($this->getOption('SlickServerUrl', 'https://app.slickstream.com'));
            echo '<script>' . "\n";
            echo '/* Slickstream Engagement Suite Embedder */' . "\n";
            echo '"use strict";((e,t,c)=>{const i=window;i.slickSnippetVersion="1.18.0";i.slickSnippetTime=(performance||Date).now();i.slickEmbedRoot=e;i.slickSiteCode=c;let a;const n=async e=>{if(!a&&"caches"in self){try{a=await caches.open("slickstream1")}catch(e){console.log(e)}}let t;if(a){try{const c=new Request(e);t=await a.match(c);if(!t){await a.add(c);t=await a.match(c);if(t&&!t.ok){t=undefined;void a.delete(c)}}}catch(e){console.warn("Slick: ",e)}}const c=document.createElement("script");if(t){c.type="application/javascript";c.appendChild(document.createTextNode(await t.text()))}else{c.src=e}(document.head||document.body).appendChild(c);return c};n(new URL(`${t}?site=${c}`,e).href)})' . "\n";
            echo '(' . "\n";
            echo '  "' . $serverUrl . '",' . "\n";
            echo '  "' . $serverUrl . '/e3/embed.js",' . "\n";
            echo '  "' . $siteCode . '",' . "\n";
            echo ');' . "\n";
            echo '</script>' . "\n";
        }
        if (is_category()) {
            echo '<meta property="slick:group" content="category" />' . "\n";
            $term = get_queried_object();
            if (isset($term->slug)) {
                echo '<meta property="slick:category" content="' . $term->slug . ':' . $term->name . '" />' . "\n";
            }
        } else if (is_tag()) {
            echo '<meta property="slick:group" content="tag" />' . "\n";
            $term = get_queried_object();
            if (isset($term->slug)) {
                echo '<meta property="slick:tag" content="' . $term->slug . ':' . $term->name . '" />' . "\n";
            }
        } else {
            if (is_singular('post')) {
                echo '<meta property="slick:group" content="post" />' . "\n";
            }
            $categories = get_the_category(get_query_var('cat'), false);
            foreach ($categories as $category) {
                if (isset($category->slug) && $category->slug !== 'uncategorized') {
                    echo '<meta property="slick:category" content="' . $category->slug . ':' . $this->removeSemicolons($category->name);
                    $used = [$category->cat_ID];
                    $count = 0;
                    $parentCatId = $category->category_parent;
                    while ($parentCatId && $count < 8 && !in_array($parentCatId, $used)) {
                        $parentCat = get_category($parentCatId);
                        if (isset($parentCat->slug) && $parentCat->slug !== 'uncategorized') {
                            echo ';' . $parentCat->slug . ':' . $this->removeSemicolons($parentCat->name);
                            $parentCatId = $parentCat->cat_ID;
                        } else {
                            break;
                        }
                        array_push($used, $parentCatId);
                        $count = $count + 1;
                    }
                    echo '" />' . "\n";
                }
            }
        }
        $currentUser = wp_get_current_user();
        if (!empty($currentUser->user_email)) {
            echo '<meta property="slick:wpuser" content="' . $currentUser->user_email . '" />' . "\n";
        }
        if (!empty($post)) {
            echo '<meta property="slick:wppostid" content="' . $post->ID . '" />' . "\n";
            if (has_post_thumbnail($post)) {
                $images = wp_get_attachment_image_src(get_post_thumbnail_id($post), 'single-post-thumbnail');
                if (!empty($images)) {
                    echo '<meta property="slick:featured_image" content="' . $images[0] . '" />' . "\n";
                }
            }
        }
    }
}
