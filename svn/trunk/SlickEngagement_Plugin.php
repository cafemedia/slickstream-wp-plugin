<?php

include_once 'SlickEngagement_LifeCycle.php';
include_once 'SlickEngagement_Widgets.php';

class SlickEngagement_Plugin extends SlickEngagement_LifeCycle
{
  const defaultServerUrl = 'https://app.slickstream.com';
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

    public function getPageBootData() {
      $siteCode = trim($this->getOption('SiteCode'));
      if ($siteCode) {
        global $wp;

        $page_url = "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $remote = self::defaultServerUrl . '/d/page-boot-data?site=' . $siteCode . '&url=' . rawurlencode($page_url);
        $headers = array( 'referer' => home_url() );
        $response = wp_remote_get( $remote , array( 'timeout' => 3, 'headers' => $headers ) );

        if ( is_array($response) ) {
          $response_text = wp_remote_retrieve_body( $response );
          
          if (!empty($response_text)) {
            $boot_data = json_decode($response_text);

            echo "<script>";
            echo "window.\$slickBoot = window.\$slickBoot || {};";
            echo "window.\$slickBoot.d = " . $response_text . ";";
            echo "window.\$slickBoot.s = 'plugin';";
            echo "window.\$slickBoot._bd = performance.now();";
            echo "</script>";

            $filmstrip_config = isset($boot_data->filmstrip) ? $boot_data->filmstrip : '';
            $dcm_config = isset($boot_data->inlineSearch) ? $boot_data->inlineSearch : '';
            if ( !empty($filmstrip_config) || !empty($dcm_config) ) {
              $filmstrip_str = empty($filmstrip_config) ? '' :  json_encode($filmstrip_config);
              $dcm_str = empty($dcm_config) ? '' :  json_encode($dcm_config);
              echo "<script>\n";
              echo "/* Slickstream CLS Insertion */\n";
              echo '"use strict";(async(e,t)=>{const n=e?JSON.parse(e):null;const r=t?JSON.parse(t):null;if(n||r){const e=async()=>{if(document.body){if(n){o(n.selector,n.position||"after selector","slick-film-strip",n.minHeight||72)}if(r){r.forEach((e=>{if(e.selector){o(e.selector,e.position||"after selector","slick-inline-search-panel",e.minHeight||350,e.id)}}))}return}window.requestAnimationFrame(e)};window.requestAnimationFrame(e)}const c=async(e,t)=>{const n=Date.now();while(true){const r=document.querySelector(e);if(r){return r}const c=Date.now();if(c-n>=t){throw new Error("Timeout")}await i(200)}};const i=async e=>new Promise((t=>{setTimeout(t,e)}));const o=async(e,t,n,r,i)=>{try{const o=await c(e,5e3);const s=i?document.querySelector(`.${n}[data-config="${i}"]`):document.querySelector(`.${n}`);if(o&&!s){const e=document.createElement("div");e.style.minHeight=r+"px";e.classList.add(n);if(i){e.dataset.config=i}switch(t){case"after selector":o.insertAdjacentElement("afterend",e);break;case"before selector":o.insertAdjacentElement("beforebegin",e);break;case"first child of selector":o.insertAdjacentElement("afterbegin",e);break;case"last child of selector":o.insertAdjacentElement("beforeend",e);break}return e}}catch(t){console.log("plugin","error",`Failed to inject ${n} for selector ${e}`)}return false}})' . "\n";
              echo "('" . addslashes($filmstrip_str) . "','" . addslashes($dcm_str) . "');" . "\n";
              echo "</script>\n";
            }
          }
        }
      }
    }

    public function addSlickPageHeader()
    {
        global $post;

        $this->getPageBootData();

        echo "\n";
        echo '<meta property="slick:wpversion" content="1.3.1" />' . "\n";
        $siteCode = trim($this->getOption('SiteCode'));

        if ($siteCode) {
            $adThriveAbTest = false;
            $serverUrl = trim($this->getOption('SlickServerUrl', self::defaultServerUrl));
            if (substr($serverUrl, 0, 11) === 'adthrive-ab') {
                $pieces = explode(" ", $serverUrl);
                $serverUrl = self::defaultServerUrl;
                $adThriveAbTest = true;
                $enabledPercent = (count($pieces) > 1) ? intval($pieces[1]) : 100;
            }

            $jsBlock = <<<JSBLOCK
            window.slickAbTestResult = function(percentEnabled, recalculate = false, testName = 'embed') {
                const win = window;
                const storage = win.localStorage;
                const targetPercentEnabled = parseInt(percentEnabled);
                
                if (isNaN(targetPercentEnabled)) {
                    return new Error("Invalid enabled percentage");
                }

                let enableSlickFeature;
                const abTestStorageKey = `slickab-\${testName}-\${targetPercentEnabled}`;
                const storedOnOffVal = storage.getItem(abTestStorageKey);
                
                const percentKey = `slickAbTestPercent-\${testName}`;
                const storedPercentVal = parseInt(storage.getItem(percentKey));
                
                if (recalculate === true || !storedOnOffVal || storedPercentVal !== targetPercentEnabled) {
                    enableSlickFeature = (Math.random() * 100) <= targetPercentEnabled;
                    storage.setItem(abTestStorageKey, enableSlickFeature);
                    storage.setItem(percentKey, targetPercentEnabled);
                } else {
                    enableSlickFeature = storage.getItem(abTestStorageKey) === 'true';
                }

                const abGroupVal = `slk\${testName}\${targetPercentEnabled}`;
                const featureOnOff = enableSlickFeature ? "on" : "off";
                win.adthrive = win.adthrive || {};
                win.adthrive.cmd = win.adthrive.cmd || [];
                win.adthrive.cmd.push(() => { win.adthrive.config.abGroup.set(abGroupVal, featureOnOff); });

                return enableSlickFeature;
            };
            JSBLOCK;

            echo "<script>\n";
            echo "'use strict';\n";
            if ($adThriveAbTest) {
                echo $jsBlock;
                echo "if (window.slickAbTestResult(" . $enabledPercent . ")) {\n";
            }
            echo "/* Slickstream Engagement Suite Embedder */\n";
            echo '"use strict";(async(e,t)=>{if(location.search.indexOf("no-slick")>=0){return}let o;const c=()=>performance.now();let a=window.$slickBoot=window.$slickBoot||{};a.rt=e;a._es=c();a.ev="2.0.1";a.l=async(e,t)=>{try{let a=0;if(!o&&"caches"in self){o=await caches.open("slickstream-code")}if(o){let i=await o.match(e);if(!i){a=c();await o.add(e);i=await o.match(e);if(i&&!i.ok){i=undefined;o.delete(e)}}if(i){const e=i.headers.get("x-slickstream-consent");return{t:a,d:t?await i.blob():await i.json(),c:e||"na"}}}}catch(e){console.log(e)}return{}};const i=e=>new Request(e,{cache:"no-store"});if(!a.d){const o=i(`${e}/d/page-boot-data?site=${t}&url=${encodeURIComponent(location.href.split("#")[0])}`);let{t:n,d:s,c:l}=await a.l(o);if(s){if(s.bestBy<Date.now()){s=undefined}else if(n){a._bd=n;a.c=l}}if(!s){a._bd=c();const e=await fetch(o);const t=e.headers.get("x-slickstream-consent");a.c=t||"na";s=await e.json()}if(s){a.d=s;a.s="embed"}}if(a.d){let e=a.d.bootUrl;const{t:t,d:o}=await a.l(i(e),true);if(o){a.bo=e=URL.createObjectURL(o);if(t){a._bf=t}}else{a._bf=c()}const n=document.createElement("script");n.src=e;document.head.appendChild(n)}else{console.log("[Slick] Boot failed")}})' . "\n";
            echo '("' . $serverUrl . '","' . $siteCode . '");' . "\n";
            if ($adThriveAbTest) {
                echo "}\n";
            }
            echo "</script>\n";
        }

        $ldJsonElements = array();

        $ldJsonPlugin = (object) [
            '@type' => 'Plugin',
            'version' => '1.3.1',
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
