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
            'FilmStripCssPosition' => array(__('FilmStrip widget CSS position', 'slick-engagement'), 'none', 'before selector', 'after selector', 'first child of selector', 'last child of selector'),
            'FilmStripCss' => array(__('FilmStrip CSS selector', 'slick-engagement')),
            'FilmStripToolbar' => array(__('FilmStrip in toolbar', 'slick-engagement'), 'enabled', 'disabled'),
            'ExplorerCssPosition' => array(__('Explorer widget CSS position', 'slick-engagement'), 'none', 'before selector', 'after selector', 'first child of selector', 'last child of selector'),
            'ExplorerCss' => array(__('Explorer CSS selector', 'slick-engagement')),
            'LinkHighlighter' => array(__('Link highlighter', 'slick-engagement'), 'enabled', 'disabled'),
            'ActivationPercent' => array(__('Percent of visitors to be shown widgets (0 - 100)', 'slick-engagement'), '100'),
            'SlickServerUrl' => array(__('Slick server (support use only)', 'slick-engagement'), ''),
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
        add_shortcode('slick-explorer', array($this, 'doExplorerShortcode'));

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

    public function doExplorerShortcode()
    {
        return '<div class="slick-widget slick-explorer slick-shortcode"></div>';
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

    public function addSlickPageHeader()
    {
        $siteCode = $this->getOption('SiteCode');
        if ($siteCode) {
            $serverUrl = $this->getOption('SlickServerUrl', 'https://poweredbyslick.com/e2/embed-nav.js');
            $serverUrl = $serverUrl . '?site=' . $siteCode;
            $slickInfo = '{site:"' . $siteCode . '",';
            $filmStripCssPosition = $this->getOption('FilmStripCssPosition', 'none');
            $filmStripCss = $this->getOption('FilmStripCss', '');
            if ($filmStripCssPosition !== 'none' && !empty($filmStripCss)) {
                $slickInfo = $slickInfo . 'filmStrip: {position:"' . $filmStripCssPosition . '", selector:"' . $filmStripCss . '"},';
            }
            $explorerCssPosition = $this->getOption('ExplorerCssPosition', 'none');
            $explorerCss = $this->getOption('ExplorerCss', '');
            if ($explorerCssPosition !== 'none' && !empty($explorerCss)) {
                $slickInfo = $slickInfo . 'explorer: {position:"' . $explorerCssPosition . '", selector:"' . $explorerCss . '"},';
            }
            if ($this->getOption('FilmStripToolbar', 'enabled') == 'enabled') {
                $slickInfo = $slickInfo . 'filmStripToolbar: {state:"enabled"},';
            }
            if ($this->getOption('LinkHighlighter', 'enabled') == 'enabled') {
                $slickInfo = $slickInfo . 'linkHighlighter: {state:"enabled"},';
            }
            $activationString = $this->getOption('ActivationPercent', '');
            if (!empty($activationString)) {
                $activationPercent = (float) $activationString;
                if ($activationPercent >= 0 && $activationPercent < 100) {
                    $slickInfo = $slickInfo . 'activationRatio:' . strval($activationPercent / 100) . ',';
                }
            }
            $slickInfo = $slickInfo . '}';
            echo "\n" . '<script type="text/javascript">';
            echo "\n" . '   function setSlick(i){window.slick=Object.assign?Object.assign(window.slick||{},i):(window.slick||i)}';
            echo "\n" . '   setSlick(' . $slickInfo . ');';
            echo "\n" . '</script>';
            echo "\n" . '<script async src="' . $serverUrl . '"></script>' . "\n";
        }
    }
}
