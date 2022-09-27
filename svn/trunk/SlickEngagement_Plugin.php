<?php

include_once 'SlickEngagement_LifeCycle.php';
include_once 'SlickEngagement_Widgets.php';

define("AFTER_HEADER_GENESIS", "After header on posts (for Genesis themes)");
define("BEFORE_CONTENT_GENESIS", "Before content on posts (for Genesis themes)");
define("AFTER_HEADER_GENESIS_TLA", "After header on posts (for Genesis/THA/Thesis themes)");
define("BEFORE_CONTENT_GENESIS_TLA", "Before content on posts (for Genesis/THA/Thesis themes)");
define("CUSTOM", "custom");

define("AFTER_CONTENT", "After content (for supported themes)");
define("BEFORE_FOOTER", "Before footer (for supported themes)");

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
            'SlickServerUrl' => array(__('Service URL (optional)', 'slick-engagement')),
            'ReserveFilmstrip' => array(__('Reserve filmstrip space', 'slick-engagement'), 'None', AFTER_HEADER_GENESIS_TLA, BEFORE_CONTENT_GENESIS_TLA, AFTER_HEADER_GENESIS, BEFORE_CONTENT_GENESIS, CUSTOM),
            'ReserveFilmstripCustom' => array(__('Filmstrip: custom hook', 'slick-engagement')),
            'ReserveFilmstripPriority' => array(__('Filmstrip: priority', 'slick-engagement')),
            'ReserveFilmstripMargin' => array(__('Filmstrip: margin', 'slick-engagement')),
            // 'ReserveContentGrid' => array(__('Reserve content grid space', 'slick-engagement'), 'None', AFTER_CONTENT, BEFORE_FOOTER, CUSTOM),
            // 'ReserveContentGridCustom' => array(__('Content grid: custom hook', 'slick-engagement')),
            // 'ReserveContentGridTitleHtml' => array(__('Content grid: title HTML', 'slick-engagement')),
            // 'ReserveContentGridPriority' => array(__('Content grid: priority', 'slick-engagement')),
            // 'ReserveContentGridHeightWide' => array(__('Content grid: height in px (desktop)', 'slick-engagement')),
            // 'ReserveContentGridHeightNarrow' => array(__('Content grid: height in px (phone)', 'slick-engagement')),
            // 'ReserveContentGridMargin' => array(__('Content grid: margin', 'slick-engagement')),
            'ReserveDCM' => array(__('Reserve DCM space', 'slick-engagement'), 'None', AFTER_CONTENT, BEFORE_FOOTER, CUSTOM),
            'ReserveDCMCustom' => array(__('DCM: custom hook', 'slick-engagement')),
            'ReserveDCMId' => array(__('DCM: id', 'slick-engagement')),
            'ReserveDCMPriority' => array(__('DCM: priority', 'slick-engagement')),
            'ReserveDCMHeightWide' => array(__('DCM: min-height in px (desktop)', 'slick-engagement')),
            'ReserveDCMHeightNarrow' => array(__('DCM: min-height in px (phone)', 'slick-engagement')),
            'ReserveDCMMargin' => array(__('DCM: margin', 'slick-engagement')),
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
        add_shortcode('slick-grid', array($this, 'doSlickGridShortcode'));
        add_shortcode('slick-story', array($this, 'doSlickStoryShortcode'));
        add_shortcode('slick-story-carousel', array($this, 'doSlickStoryCarouselShortcode'));
        add_shortcode('slick-story-explorer', array($this, 'doSlickStoryExplorerShortcode'));

// Register AJAX hooks
        // http://plugin.michael-simpson.com/?page_id=41

        // Ensure pages can be configured with categories and tags
        add_action('init', array(&$this, 'add_taxonomies_to_pages'));

        $prefix = is_network_admin() ? 'network_admin_' : '';
        $plugin_file = plugin_basename($this->getPluginDir() . DIRECTORY_SEPARATOR . $this->getMainPluginFileName()); //plugin_basename( $this->getMainPluginFileName() );
        // $this->guildLog('Adding filter ' . "{$prefix}plugin_action_links_{$plugin_file}");
        add_filter("{$prefix}plugin_action_links_{$plugin_file}", array(&$this, 'onActionLinks'));

        $reserveFilmstripSpace = $this->getOption('ReserveFilmstrip', 'None');
        if ($reserveFilmstripSpace !== 'None') {
            $reserveFilmstripPriority = intval($this->getOption('ReserveFilmstripPriority', '15'));
            $reserveFilmstripCustom = $this->getOption('ReserveFilmstripCustom', '');
            if ($reserveFilmstripSpace === AFTER_HEADER_GENESIS_TLA || $reserveFilmstripSpace === AFTER_HEADER_GENESIS) {
                // $this->guildLog('Adding after-header filmstrip injection');
                add_action('genesis_after_header', array(&$this, 'np_slickstream_space_genesis'), $reserveFilmstripPriority);
                add_action('tha_header_after', array(&$this, 'np_slickstream_space_genesis'), $reserveFilmstripPriority);
                add_action('tha_after_header', array(&$this, 'np_slickstream_space_genesis'), $reserveFilmstripPriority);
                add_action('thesis_hook_after_header', array(&$this, 'np_slickstream_space_genesis'), $reserveFilmstripPriority);
                add_action('kadence_after_header', array(&$this, 'np_slickstream_space_genesis'), $reserveFilmstripPriority);
                add_action('astra_header_after', array(&$this, 'np_slickstream_space_genesis'), $reserveFilmstripPriority);
            } else if ($reserveFilmstripSpace === BEFORE_CONTENT_GENESIS_TLA || $reserveFilmstripSpace === BEFORE_CONTENT_GENESIS) {
                // $this->guildLog('Adding before-content filmstrip injection');
                add_action('genesis_before_content', array(&$this, 'np_slickstream_space_genesis'), $reserveFilmstripPriority);
                add_action('tha_content_before', array(&$this, 'np_slickstream_space_genesis'), $reserveFilmstripPriority);
                add_action('tha_before_content', array(&$this, 'np_slickstream_space_genesis'), $reserveFilmstripPriority);
                add_action('thesis_hook_before_content', array(&$this, 'np_slickstream_space_genesis'), $reserveFilmstripPriority);
                add_action('kadence_before_content', array(&$this, 'np_slickstream_space_genesis'), $reserveFilmstripPriority);
                add_action('astra_content_before', array(&$this, 'np_slickstream_space_genesis'), $reserveFilmstripPriority);
            } else if ($reserveFilmstripSpace === CUSTOM && !empty($reserveFilmstripCustom)) {
                add_action($reserveFilmstripCustom, array(&$this, 'np_slickstream_space_genesis'), $reserveFilmstripPriority);
            }
        }

        $reserveDCM = $this->getOption('ReserveDCM', 'None');
        if ($reserveDCM !== 'None') {
            $reserveDCMPriority = intval($this->getOption('ReserveDCMPriority', '15'));
            $reserveDCMCustom = $this->getOption('ReserveDCMCustom', '');
            if ($reserveDCM === AFTER_CONTENT) {
                add_action('genesis_after_content', array(&$this, 'np_slickstream_dcm_reserve'), $reserveDCMPriority);
                add_action('tha_content_after', array(&$this, 'np_slickstream_dcm_reserve'), $reserveDCMPriority);
                add_action('tha_after_content', array(&$this, 'np_slickstream_dcm_reserve'), $reserveDCMPriority);
                add_action('thesis_hook_after_content', array(&$this, 'np_slickstream_dcm_reserve'), $reserveDCMPriority);
                add_action('kadence_after_content', array(&$this, 'np_slickstream_dcm_reserve'), $reserveDCMPriority);
                add_action('astra_content_after', array(&$this, 'np_slickstream_dcm_reserve'), $reserveDCMPriority);
            } else if ($reserveDCM === BEFORE_FOOTER) {
                add_action('genesis_before_footer', array(&$this, 'np_slickstream_dcm_reserve'), $reserveDCMPriority);
                add_action('tha_footer_before', array(&$this, 'np_slickstream_dcm_reserve'), $reserveDCMPriority);
                add_action('tha_before_footer', array(&$this, 'np_slickstream_dcm_reserve'), $reserveDCMPriority);
                add_action('thesis_hook_before_footer', array(&$this, 'np_slickstream_dcm_reserve'), $reserveDCMPriority);
                add_action('kadence_before_footer', array(&$this, 'np_slickstream_dcm_reserve'), $reserveDCMPriority);
                add_action('astra_footer_before', array(&$this, 'np_slickstream_dcm_reserve'), $reserveDCMPriority);
            } else if ($reserveDCM === CUSTOM && !empty($reserveDCMCustom)) {
                add_action($reserveDCMCustom, array(&$this, 'np_slickstream_dcm_reserve'), $reserveDCMPriority);
            }
        }

        add_filter('rocket_delay_js_exclusions', array(&$this, 'np_wp_rocket__exclude_from_delay_js'));
    }

    // Exclude scripts from JS delay.
    public function np_wp_rocket__exclude_from_delay_js($excluded_strings = array())
    {
        // MUST ESCAPE PERIODS AND PARENTHESES!
        $excluded_strings[] = "slickstream";
        return $excluded_strings;
    }

    public function onActionLinks($links)
    {
        // $this->guildLog('onActionLinks ' . admin_url('options-general.php?page=SlickEngagement_PluginSettings'));
        $mylinks = array('<a href="' . admin_url('options-general.php?page=SlickEngagement_PluginSettings') . '">Settings</a>');
        return array_merge($links, $mylinks);
    }

    public function np_slickstream_space_genesis()
    {
        if (is_singular('post')) {
            // $this->guildLog('Injecting filmstrip');
            $reserveFilmstripMargin = $this->getOption('ReserveFilmstripMargin', '');
            if (empty($reserveFilmstripMargin)) {
                $reserveFilmstripMargin = '10px auto';
            }
            echo '<div style="min-height:72px;margin:' . $reserveFilmstripMargin . '" class="slick-film-strip"></div>';
        }
    }

    public function np_slickstream_dcm_reserve()
    {
        if (is_singular('post')) {
            $dcmId = $this->getOption('ReserveDCMId', '_default');
            $dcmHeightWide = $this->getOption('ReserveDCMHeightWide', '428');
            $dcmHeightNarrow = $this->getOption('ReserveDCMHeightNarrow', '334');
            $reserveDCMMargin = $this->getOption('ReserveDCMMargin', '50px 15px');
            echo "\n" . '<style>.slick-inline-search-panel { margin: ' . $reserveDCMMargin . '; min-height: ' . $dcmHeightWide . 'px; } @media (max-width: 600px) { .slick-inline-search-panel { min-height: ' . $dcmHeightNarrow . 'px; } } </style>' . "\n";
            echo '<div class="slick-inline-search-panel" data-config="' . $dcmId . '"></div>' . "\n";
        }
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

    public function doSlickGridShortcode($attrs, $content, $tag)
    {
        extract(shortcode_atts(array('id' => ''), $attrs));
        if (isset($id)) {
            return '<div class="slick-content-grid" data-config="' . trim($id) . '"></div>' . "\n";
        } else {
            return '<div class="slick-content-grid"></div>' . "\n";
        }
    }

    public function doSlickStoryCarouselShortcode()
    {
        return '<style>.slick-story-carousel {min-height: 324px;} @media (max-width: 600px) {.slick-story-carousel {min-height: 224px;}}</style>' . "\n" . '<div class="slick-widget slick-story-carousel slick-shortcode"></div>';
    }

    public function doSlickStoryExplorerShortcode()
    {
        return '<div class="slick-widget slick-story-explorer slick-shortcode"></div>';
    }

    public function doSlickStoryShortcode($attrs, $content, $tag)
    {
        extract(shortcode_atts(array('src' => ''), $attrs));
        // We want to support different styles of short-code arguments:
        //   old-style: https://:channelid.stories.slickstream.com/d/story/:channelid/:storyid
        //   story page URL: https://stories.slickstream.com/:channelid/story/:storyid
        //   new-style: :channelid/:storyid
        $oldStyleRegex = '/^https\:\/\/([^\/]+)\/d\/story\/([^\/]+)\/([^\/]+)$/i';
        $revisedStyleRegex = '/^https\:\/\/([^\/]+)\/([^\/]+)\/d\/story\/([^\/]+)$/i';
        $storyPageRegex = '/^https\:\/\/([^\/]+)\/([^\/]+)\/story\/([^\/]+)$/i';
        $newStyleRegex = '/^([^\/]+)\/([^\/]+)$/i';
        $domain = "stories.slickstream.com";
        $slickServerUrl = $this->getOption('SlickServerUrl', 'https://app.slickstream.com');
        if (preg_match('/\-staging\.slickstream/', $slickServerUrl)) {
            $domain = "stories-staging.slickstream.com";
        }
        $channelid = "nochannel";
        $storyid = "";
        $webStoryUrl = "";
        if (preg_match_all($oldStyleRegex, $src, $matches)) {
            $domain = $matches[1][0];
            $channelid = $matches[2][0];
            $storyid = $matches[3][0];
            $webStoryUrl = $this->getSlickstreamWebStoryUrl($domain, $channelid, $storyid);
        } else if (preg_match_all($revisedStyleRegex, $src, $matches)) {
            $domain = $matches[1][0];
            $channelid = $matches[2][0];
            $storyid = $matches[3][0];
            $webStoryUrl = $this->getSlickstreamWebStoryUrl($domain, $channelid, $storyid);
        } else if (preg_match_all($storyPageRegex, $src, $matches)) {
            $domain = $matches[1][0];
            $channelid = $matches[2][0];
            $storyid = $matches[3][0];
            $webStoryUrl = $this->getSlickstreamWebStoryUrl($domain, $channelid, $storyid);
        } else if (preg_match_all($newStyleRegex, $src, $matches)) {
            $channelid = $matches[1][0];
            $storyid = $matches[2][0];
            $webStoryUrl = $this->getSlickstreamWebStoryUrl($domain, $channelid, $storyid);
        } else {
            $webStoryUrl = $src;
        }
        $output = '';
        if (!empty($webStoryUrl)) {
            if (empty($storyId)) {
                $storyId = $this->getStoryIdFromUrl($webStoryUrl);
            }
            $output .= '<slick-webstory-player id="story-' . $storyId . '">' . "\n";
            $output .= '  <a href="' . $webStoryUrl . '"></a>' . "\n";
            $output .= '</slick-webstory-player>' . "\n";
        }
        return $output;
    }

    public function getStoryIdFromUrl($url)
    {
        if (strpos($url, 'slickstream.com') !== false && strpos($url, '/d/webstory') !== false) {
            $parts = explode('/', $url);
            if (count($parts) > 1) {
                if (!empty($parts[count($parts) - 1])) {
                    return $parts[count($parts) - 1];
                }
            }
        }
        return substr(hash('md5', $url), 0, 5);
    }

    public function getSlickstreamWebStoryUrl($domain, $channelId, $storyId)
    {
        return 'https://' . $domain . '/' . $channelId . '/d/webstory/' . $storyId;
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
        echo '<meta property="slick:wpversion" content="1.2.5" />' . "\n";
        $siteCode = trim($this->getOption('SiteCode'));
        if ($siteCode) {
            $adThriveAbTest = false;
            $adThriveAbFraction = 0.9;
            $serverUrl = trim($this->getOption('SlickServerUrl', 'https://app.slickstream.com'));
            if (substr($serverUrl, 0, 11) === 'adthrive-ab') {
                $pieces = explode(" ", $serverUrl);
                $serverUrl = 'https://app.slickstream.com';
                $adThriveAbTest = true;
                if (count($pieces) > 1) {
                    $fractionValue = intval($pieces[1]);
                    if ($fractionValue > 0 && $fractionValue < 100) {
                        $adThriveAbFraction = $fractionValue / 100;
                    }
                }
            }
            echo '<style>' . "\n";
            echo '  .category-slickstream-exclusive { visibility: hidden; }' . "\n";
            echo '</style>' . "\n";
            echo '<script>' . "\n";
            echo '"use strict";' . "\n";
            if ($adThriveAbTest) {
                echo '(() => {' . "\n";
                echo '  window.adthrive = window.adthrive || {};' . "\n";
                echo '  window.adthrive.cmd = window.adthrive.cmd || [];' . "\n";
                echo '  let slickParams = new URLSearchParams(document.location.search.substring(1));' . "\n";
                echo '  let slickAbParam = slickParams.get("abEnabled");' . "\n";
                echo '  if (slickAbParam && ["on","off"].indexOf(slickAbParam) >= 0) {' . "\n";
                echo '    window.adthrive_AB_enabled = slickAbParam;' . "\n";
                echo '    if (window.localStorage) { window.localStorage.setItem("adthrive_AB_enabled", window.adthrive_AB_enabled); }' . "\n";
                echo '  } else {' . "\n";
                echo '    window.adthrive_AB_enabled = (window.localStorage ? window.localStorage.getItem("adthrive_AB_enabled") : undefined);' . "\n";
                echo '    if (!window.adthrive_AB_enabled) {' . "\n";
                echo '      window.adthrive_AB_enabled = Math.random() < ' . $adThriveAbFraction . ' ? "on" : "off";' . "\n";
                echo '      if (window.localStorage) { window.localStorage.setItem("adthrive_AB_enabled", window.adthrive_AB_enabled); }' . "\n";
                echo '    }' . "\n";
                echo '  }' . "\n";
                echo '  window.adthrive.cmd.push(function() {' . "\n";
                echo '    window.adthrive.config.abGroup.set("slkstm", window.adthrive_AB_enabled);' . "\n";
                echo '  });' . "\n";
                echo '})();' . "\n";
                echo 'if (window.adthrive_AB_enabled === "on") {' . "\n";
            }
            echo '/* Slickstream Engagement Suite Embedder */' . "\n";
            echo '"use strict";(async(e,t)=>{if(location.search.indexOf("no-slick")>=0){return}let o;const a=()=>(performance||Date).now();const i=window.$slickBoot={rt:e,_es:a(),ev:"2.0.0",l:async(e,t)=>{try{let i=0;if(!o&&"caches"in self){o=await caches.open("slickstream-code")}if(o){let n=await o.match(e);if(!n){i=a();await o.add(e);n=await o.match(e);if(n&&!n.ok){n=undefined;o.delete(e)}}if(n){return{t:i,d:t?await n.blob():await n.json()}}}}catch(e){console.log(e)}return{}}};const n=e=>new Request(e,{cache:"no-store"});const c=n(`${e}/d/page-boot-data?${innerWidth<=600?"mobile&":""}site=${t}&url=${encodeURIComponent(location.href.split("#")[0])}`);let{t:s,d:l}=await i.l(c);if(l){if(l.bestBy<Date.now()){l=undefined}else if(s){i._bd=s}}if(!l){i._bd=a();l=await(await fetch(c)).json()}if(l){i.d=l;let e=l.bootUrl;const{t:t,d:o}=await i.l(n(e),true);if(o){i.bo=e=URL.createObjectURL(o);if(t){i._bf=t}}else{i._bf=a()}const c=document.createElement("script");c.src=e;document.head.appendChild(c)}else{console.log("[Slick] Boot failed")}})' . "\n";
            echo '("' . $serverUrl . '","' . $siteCode . '");' . "\n";
            if ($adThriveAbTest) {
                echo '}' . "\n";
            }
            echo '</script>' . "\n";
        }
        $ldJsonElements = array();

        $ldJsonPlugin = (object) [
            '@type' => 'Plugin',
            'version' => '1.2.5',
        ];
        array_push($ldJsonElements, $ldJsonPlugin);

        // $currentUser = wp_get_current_user();
        // if (!empty($currentUser->user_email)) {
        //     echo '<meta property="slick:wpuser" content="' . $currentUser->user_email . '" />' . "\n";
        //     $ldJsonUser = (object) [
        //         '@type' => 'User',
        //         'email' => $currentUser->user_email,
        //     ];
        //     array_push($ldJsonElements, $ldJsonUser);
        // }

        $ldJsonSite = (object) [
            '@type' => 'Site',
            'name' => get_bloginfo('name'),
            'url' => get_bloginfo('url'),
            'description' => get_bloginfo('description'),
            'atomUrl' => get_bloginfo('atom_url'),
            'rtl' => is_rtl(),
        ];
        array_push($ldJsonElements, $ldJsonSite);

        if (!empty($post)) {
            $pageType = 'post';
            if (is_front_page() || is_home()) {
                $pageType = 'home';
            } else if (is_category()) {
                $pageType = 'category';
            } else if (is_tag()) {
                $pageType = 'tag';
            } else if (is_singular('post')) {
                $pageType = 'post';
            } else if (is_singular('page')) {
                $pageType = 'page';
            } else {
                $pageType = 'other';
            }
            $ldJsonPost = (object) [
                '@type' => 'WebPage',
                '@id' => $post->ID,
                'isFront' => is_front_page(),
                'isHome' => is_home(),
                'isCategory' => is_category(),
                'isTag' => is_tag(),
                'isSingular' => is_singular(),
                'date' => get_the_time('c'),
                'modified' => get_the_modified_time('c'),
                'title' => $post->post_title,
                'pageType' => $pageType,
                'postType' => $post->post_type,
            ];
            echo '<meta property="slick:wppostid" content="' . $post->ID . '" />' . "\n";
            if (has_post_thumbnail($post)) {
                $images = wp_get_attachment_image_src(get_post_thumbnail_id($post), 'single-post-thumbnail');
                if (!empty($images)) {
                    echo '<meta property="slick:featured_image" content="' . $images[0] . '" />' . "\n";
                    $ldJsonPost->featured_image = $images[0];
                }
            }
            $authorName = get_the_author_meta('display_name');
            if (!empty($authorName)) {
                $ldJsonPost->author = $authorName;
            }
            if (is_category()) {
                echo '<meta property="slick:group" content="category" />' . "\n";
                $term = get_queried_object();
                if (isset($term->slug)) {
                    echo '<meta property="slick:category" content="' . $term->slug . ':' . $term->name . '" />' . "\n";
                    $ldJsonCategory = (object) [
                        '@id' => $term->term_id,
                        'slug' => $term->slug,
                        'name' => $term->name,
                    ];
                    $ldJsonPost->category = $ldJsonCategory;
                }
            } else if (is_tag()) {
                echo '<meta property="slick:group" content="tag" />' . "\n";
                $term = get_queried_object();
                if (isset($term->slug)) {
                    echo '<meta property="slick:tag" content="' . $term->slug . ':' . $term->name . '" />' . "\n";
                    $ldJsonTag = (object) [
                        '@id' => $term->term_id,
                        'slug' => $term->slug,
                        'name' => $term->name,
                    ];
                    $ldJsonPost->tag = $ldJsonTag;
                }
            } else if (is_singular(['post', 'page'])) {
                if (is_singular('post')) {
                    echo '<meta property="slick:group" content="post" />' . "\n";
                }
                $categories = get_the_category();
                if (!empty($categories)) {
                    $ldJsonCategoryElements = array();
                    foreach ($categories as $category) {
                        if (isset($category->slug) && $category->slug !== 'uncategorized') {
                            echo '<meta property="slick:category" content="' . $category->slug . ':' . $this->removeSemicolons($category->name);
                            $used = [$category->cat_ID];
                            $count = 0;
                            $parentCatId = $category->category_parent;
                            $ldJsonParents = array();
                            while ($parentCatId && $count < 8 && !in_array($parentCatId, $used)) {
                                $parentCat = get_category($parentCatId);
                                if (isset($parentCat->slug) && $parentCat->slug !== 'uncategorized') {
                                    echo ';' . $parentCat->slug . ':' . $this->removeSemicolons($parentCat->name);
                                    $parentCatId = $parentCat->cat_ID;
                                    $ldJsonParent = (object) [
                                        '@type' => 'CategoryParent',
                                        '@id' => $parentCat->cat_ID,
                                        'slug' => $parentCat->slug,
                                        'name' => $this->removeSemicolons($parentCat->name),
                                    ];
                                    array_push($ldJsonParents, $ldJsonParent);
                                } else {
                                    break;
                                }
                                array_push($used, $parentCatId);
                                $count = $count + 1;
                            }
                            echo '" />' . "\n";
                            $ldJsonCategoryElement = (object) [
                                '@id' => $category->cat_ID,
                                'slug' => $category->slug,
                                'name' => $this->removeSemicolons($category->name),
                                'parents' => $ldJsonParents,
                            ];
                            array_push($ldJsonCategoryElements, $ldJsonCategoryElement);
                        }
                    }
                    if (!empty($ldJsonCategoryElements)) {
                        $ldJsonPost->categories = $ldJsonCategoryElements;
                    }
                }

                $tags = get_the_tags();
                if (!empty($tags)) {
                    $ldJsonTags = array();
                    foreach ($tags as $tag) {
                        if (isset($tag->name)) {
                            array_push($ldJsonTags, $tag->name);
                        }
                    }
                    if (!empty($ldJsonTags)) {
                        $ldJsonPost->tags = $ldJsonTags;
                    }
                }

                $ldJsonTaxonomies = array();
                $taxonomies = get_object_taxonomies($post, 'objects');
                if (!empty($taxonomies)) {
                    foreach ($taxonomies as $taxonomy) {
                        if (empty($taxonomy->_builtin) && $taxonomy->public) {
                            $taxTerms = array();
                            $terms = get_the_terms($post, $taxonomy->name);
                            if (!empty($terms)) {
                                foreach ($terms as $term) {
                                    $termObject = (object) [
                                        '@id' => $term->term_id,
                                        'name' => $term->name,
                                        'slug' => $term->slug,
                                    ];
                                    array_push($taxTerms, $termObject);
                                }
                                $ldJsonTaxElement = (object) [
                                    'name' => $taxonomy->name,
                                    'label' => $taxonomy->label,
                                    'description' => $taxonomy->description,
                                    'terms' => $taxTerms,
                                ];
                                array_push($ldJsonTaxonomies, $ldJsonTaxElement);
                            }
                        }
                    }
                }
                if (class_exists('WPRM_Recipe_Manager')) {
                    // $postTypeArgs = array('public' => true, '_builtin' => false);
                    // $ldJsonPost->postTypes = get_post_types($postTypeArgs, 'names', 'and');
                    $recipes = WPRM_Recipe_Manager::get_recipe_ids_from_post($post->ID);
                    // $ldJsonPost->recipes = $recipes;
                    if (!empty($recipes)) {
                        // $recipe = WPRM_Recipe_Manager::get_recipe($recipes[0]);
                        // $ldJsonPost->recipe = $recipe;
                        // $ldJsonPost->recipeId = $recipes[0];
                        $recipeTaxonomies = get_object_taxonomies('wprm_recipe', 'objects');
                        // $ldJsonPost->recipeTaxonomies = $recipeTaxonomies;
                        if (!empty($recipeTaxonomies)) {
                            foreach ($recipeTaxonomies as $taxonomy) {
                                if (empty($taxonomy->_builtin) && $taxonomy->public) {
                                    $taxTerms = array();
                                    $terms = get_the_terms($recipes[0], $taxonomy->name);
                                    // $lsJsonPost->taxTerms = $terms;
                                    if (!empty($terms)) {
                                        foreach ($terms as $term) {
                                            $termObject = (object) [
                                                '@id' => $term->term_id,
                                                'name' => $term->name,
                                                'slug' => $term->slug,
                                            ];
                                            array_push($taxTerms, $termObject);
                                        }
                                        $ldJsonTaxElement = (object) [
                                            'name' => $taxonomy->name,
                                            'label' => $taxonomy->label,
                                            'description' => $taxonomy->description,
                                            'terms' => $taxTerms,
                                        ];
                                        array_push($ldJsonTaxonomies, $ldJsonTaxElement);
                                    }
                                }
                            }
                        }
                    }
                }
                $ldJsonPost->taxonomies = $ldJsonTaxonomies;
            }
            array_push($ldJsonElements, $ldJsonPost);
        }
        $ldJson = (object) [
            '@context' => 'https://slickstream.com',
            '@graph' => $ldJsonElements,
        ];
        echo '<script type="application/x-slickstream+json">' . json_encode($ldJson, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }
}
