<?php

include_once 'SlickEngagement_LifeCycle.php';
include_once 'SlickEngagement_Widgets.php';

define('PLUGIN_VERSION', '1.4.1');

class SlickEngagement_Plugin extends SlickEngagement_LifeCycle
{
    private $consoleOutput = "";
    const defaultServerUrl = 'https://app.slickstream.com';
    /**
     * @return array of option meta data.
     */
    public function getOptionMetaData()
    {
        return array(
            'SiteCode' => array(__('Site Code', 'slick-engagement')),
            'SlickServerUrl' => array(__('Service URL (optional)', 'slick-engagement')),
        );
    }

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
        return 'Slickstream Engagement';
    }

    protected function getMainPluginFileName()
    {
        return 'slick-engagement.php';
    }

    protected function installDatabaseTables()
    {
    }

    protected function unInstallDatabaseTables()
    {
    }

    public function upgrade()
    {
    }

    public function addActionsAndFilters()
    {
        // Add options administration page
        add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));


        // Add Actions & Filters
        add_action('wp_head', array(&$this, 'addSlickPageHeader'));

        // Adding scripts & styles to all pages
        // Examples:
        //        wp_enqueue_script('jquery');
        //        wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
        //        wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));

        // Register short codes
        add_shortcode('slick-film-strip', array($this, 'doFilmStripShortcode'));
        add_shortcode('slick-game', array($this, 'doGameShortcode'));
        // add_shortcode('slick-next-up', array($this, 'doNextUpShortcode'));
        add_shortcode('slick-grid', array($this, 'doSlickGridShortcode'));
        add_shortcode('slick-story', array($this, 'doSlickStoryShortcode'));
        add_shortcode('slick-story-carousel', array($this, 'doSlickStoryCarouselShortcode'));
        add_shortcode('slick-story-explorer', array($this, 'doSlickStoryExplorerShortcode'));

        // Register AJAX hooks
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

        //If there's a featured image, show it
        if (has_post_thumbnail($post)) {
            $images = wp_get_attachment_image_src(get_post_thumbnail_id($post), 'single-post-thumbnail');
            return $images[0];
        } else {
            $content = $post->post_content;
            $first_img = '';
            $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content, $matches);
            $first_img = $matches[1][0];

            //No featured image, so we get the first image inside the post content
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

    // Fetches the Page Boot Data from the server
    //TODO: if we find that `/page-boot-data` requests are reduced enough to always use origin, we can add headers to avoid hitting CloudFlare
    private function fetchBootData($siteCode) 
    {
        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $page_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $remote = self::defaultServerUrl . '/d/page-boot-data?site=' . $siteCode . '&url=' . rawurlencode($page_url);
        $headers = array('referer' => home_url());
        $response = wp_remote_get($remote , array('timeout' => 2, 'headers' => $headers));
        
        if (is_array($response)) {
            $response_text = wp_remote_retrieve_body($response);
            return json_decode($response_text);
        } else {
            return null;
        }
    }

    private function echoSlickBootJs($boot_data_obj) {
        $boot_data_json = json_encode($boot_data_obj);

        if (false === $boot_data_json) {
            $this->echoSlickstreamComment('Error encoding boot data JSON');
            return;
        }

        $this->echoSlickstreamComment('Page Boot Data:', false);

        echo <<<JSBLOCK
        <script>
        // slickstream page boot data
        window.\$slickBoot = window.\$slickBoot || {};
        window.\$slickBoot.d = ${boot_data_json};
        window.\$slickBoot.s = 'plugin';
        window.\$slickBoot._bd = performance.now();
        </script>\n
        JSBLOCK;

        $this->echoSlickstreamComment('END Page Boot Data', false);
    }

    private function isMobile() {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
          $user_agent = $_SERVER['HTTP_USER_AGENT'];
          $excluded = preg_match('/Tablet|iPad|Playbook|Nook|webOS|Kindle|Android (?!.*Mobile).*Safari/i', $user_agent);
          $mobile = preg_match('/Mobi|iP(hone|od)|Opera Mini/i', $user_agent);
          return $mobile && !$excluded;
        }
        return false;
    }

    private function getBootDataForDevice($boot_data_obj) {
        if (isset($boot_data_obj->v2)) {
            $boot_data_obj_v2 = $boot_data_obj->v2;
            if (isset($boot_data_obj_v2->phone) && $this->isMobile()) {
              return $boot_data_obj_v2->phone;
            }
            return $boot_data_obj_v2->desktop;
        }
        return $boot_data_obj;
    }

    private function echoClsData($boot_data_obj)
    {
        $device_boot_data = $this->getBootDataForDevice($boot_data_obj);

        $filmstrip_config = isset($device_boot_data->filmstrip) ? $device_boot_data->filmstrip : '';
        $dcm_config = isset($device_boot_data->inlineSearch) ? $device_boot_data->inlineSearch : '';

        // from 1.2.5 settings
        $filmstrip_margin = $this->getOption('ReserveFilmstripMargin', '');
        $dcm_margin = $this->getOption('ReserveDCMMargin', '');
        if (!empty($filmstrip_config) && !empty($filmstrip_margin)) {
            $filmstrip_config->marginLegacy = $filmstrip_margin;
        }
        if (!empty($dcm_config) && !empty($dcm_margin)) {
            foreach ($dcm_config as $config) {
              $config->marginLegacy = $dcm_margin;
            }
        }

        if (!empty($filmstrip_config) || !empty($dcm_config)) {
            $filmstrip_str = empty($filmstrip_config) ? '' :  json_encode($filmstrip_config);
            $dcm_str = empty($dcm_config) ? '' :  json_encode($dcm_config);

            $this->echoSlickstreamComment('CLS Insertion:', false);

            echo "<script>\n";
            echo '"use strict";(async(e,t)=>{const n="slickstream";const r=e?JSON.parse(e):null;const i=t?JSON.parse(t):null;if(r||i){const e=async()=>{if(document.body){if(r){o(r.selector,r.position||"after selector","slick-film-strip",r.minHeight||72,r.margin||r.marginLegacy||"10px auto")}if(i){i.forEach((e=>{if(e.selector){o(e.selector,e.position||"after selector","slick-inline-search-panel",e.minHeight||350,e.margin||e.marginLegacy||"50px 15px",e.id)}}))}return}window.requestAnimationFrame(e)};window.requestAnimationFrame(e)}const c=async(e,t)=>{const n=Date.now();while(true){const r=document.querySelector(e);if(r){return r}const i=Date.now();if(i-n>=t){throw new Error("Timeout")}await s(200)}};const s=async e=>new Promise((t=>{setTimeout(t,e)}));const o=async(e,t,r,i,s,o)=>{try{const n=await c(e,5e3);const a=o?document.querySelector(`.${r}[data-config="${o}"]`):document.querySelector(`.${r}`);if(n&&!a){const e=document.createElement("div");e.style.minHeight=i+"px";e.style.margin=s;e.classList.add(r);if(o){e.dataset.config=o}switch(t){case"after selector":n.insertAdjacentElement("afterend",e);break;case"before selector":n.insertAdjacentElement("beforebegin",e);break;case"first child of selector":n.insertAdjacentElement("afterbegin",e);break;case"last child of selector":n.insertAdjacentElement("beforeend",e);break}return e}}catch(t){console.log("plugin","error",n,`Failed to inject ${r} for selector ${e}`)}return false}})' . "\n";
            echo "('" . addslashes($filmstrip_str) . "','" . addslashes($dcm_str) . "');" . "\n";
            echo "\n</script>\n";

            $this->echoSlickstreamComment('END CLS Insertion', false);
        }
    }

    //Returns a name for the "page boot data" transient 
    private function getTransientName()
    {
        DEFINE('PAGE_BOOT_DATA_TRANSIENT_PREFIX', 'slick_page_boot_');
        $normalized_url = $_SERVER['HTTP_HOST'] . explode('?', $_SERVER['REQUEST_URI'])[0];
        return PAGE_BOOT_DATA_TRANSIENT_PREFIX . md5($normalized_url);
    }

    private function echoSlickstreamComment($comment, $echoToConsole = true)
    {
        echo "<!-- [Slickstream] " . $comment . " -->\n";

        if ($echoToConsole) {
            $this->consoleOutput .= $comment . "\n";
        }
    }

    private function echoConsoleOutput()
    {
        if ($this->consoleOutput !== "") 
        {
            echo "<script>console.info(`[SLICKSTREAM]\n" . $this->consoleOutput . "`)</script>\n";
        }
    }

    //TODO: Add functionality to detect if the page was cached (by a CDN or otherwise)
    private function getCurrentTimestampByTimeZone($tz_name)
    {
        $timestamp = time();
        $dt = new DateTime('now', new DateTimeZone($tz_name));
        $dt->setTimestamp($timestamp);
        return $dt->format('n/j/Y, g:i:s A');
    }

    // Delete transient boot data if `&delete-boot=1` is passed in
    private function handleDeleteBootData()
    {
        $delete_transient_param = $this->getQueryParamByName('delete-boot');
        $delete_transient_data = ($delete_transient_param === '1');

        if (!$delete_transient_data) {
            return;
        }

        $this->echoSlickstreamComment("Deleting page boot data from cache");
        $comment = (false === delete_transient($this->getTransientName())) ? 
            "Nothing to do--page not found in cache" :
            "Page boot data deleted successfully";
        $this->echoSlickstreamComment($comment);
    }

    private function getQueryParamByName($param_name)
    {
        $param_val = isset($_GET[$param_name]) ? $_GET[$param_name] : null;
        return $param_val;
    }

    private function echoPageBootData() 
    {
        $siteCode = substr(trim($this->getOption('SiteCode')), 0, 9);

        if (!$siteCode) {
            $this->echoSlickstreamComment("ERROR: Site Code missing from Plugin Settings; Slickstream services are disabled");
            return;
        }

        global $wp;
        
        $transient_name = $this->getTransientName(); //Name for WP Transient Cache API Key

        // If `delete-boot=1` is passed as a query param, delete the stored page boot data
        $this->handleDeleteBootData();

        $no_transient_data = false === ($boot_data_obj = get_transient($transient_name)); //get_transient returns `false` if the key doesn't exist

        // If `slick-boot=1` is passed as a query param, force a re-fetch of the boot data from the server
        // If `slick-boot=0` is passed as a query param, skip fetching boot data from the server
        $slick_boot_param = $this->getQueryParamByName('slick-boot');
        $force_fetch_boot_data = ($slick_boot_param === '1');
        $dont_load_boot_data = ($slick_boot_param === '0');
        $slick_boot_param_not_set = ($slick_boot_param === null);
    
        // Check for existing data in transient cache. If none, then fetch data from server.
        //TODO: store cache hits and cache misses in a transient or option?
        if ($force_fetch_boot_data || ($no_transient_data && $slick_boot_param_not_set)) {
            $this->echoSlickstreamComment("Fetching page boot data from server");
            $boot_data_obj = $this->fetchBootData($siteCode);

            // Put the results in transient storage; expire after 15 minutes
            if ($boot_data_obj) {
                set_transient($transient_name, $boot_data_obj, 15 * MINUTE_IN_SECONDS);
                $this->echoSlickstreamComment("Storing page boot data in transient cache: " . $transient_name);
            } else {
                $this->echoSlickstreamComment("Error Fetching page boot data from server");
                return;
            }
        } else if ($dont_load_boot_data) {
            $this->echoSlickstreamComment("Skipping page boot data and CLS output");
            return;
        } else {
            $this->echoSlickstreamComment("Using cached page boot data: " . $transient_name);
        }

        $this->echoSlickBootJs($boot_data_obj);
        $this->echoClsData($boot_data_obj);
    }

    //Plugin-based A/B Testing JS Logic
    private function getAbTestJs()
    {
        
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
        
        return $jsBlock;
    }

    //TODO: Clean this up / migrate to SR functions
    public function addSlickPageHeader()
    {
        global $post;
        echo "\n";
        $this->echoSlickstreamComment("Page Generated at: " . $this->getCurrentTimeStampByTimeZone('Europe/London') . " GMT");
        $this->consoleOutput .= "Current timestamp: \${(new Date).toLocaleString('en-US', { timeZone: 'GMT' })} GMT\n\n";
        $this->echoPageBootData();

        echo "\n" . '<meta property="slick:wpversion" content="' . PLUGIN_VERSION . '" />' . "\n";
        $siteCode = substr(trim($this->getOption('SiteCode')), 0, 9);

        if ($siteCode) {
            $adThriveAbTest = false;
            $serverUrl = trim($this->getOption('SlickServerUrl', self::defaultServerUrl));
            //NOTE: for WP Plugin-based A/B Tests, the SlickServerUrl option is overloaded, hence the weird usage here
            if (substr($serverUrl, 0, 11) === 'adthrive-ab') {
                $pieces = explode(" ", $serverUrl);
                $serverUrl = self::defaultServerUrl;
                $adThriveAbTest = true;
                $enabledPercent = (count($pieces) > 1) ? intval($pieces[1]) : 100;
            }

            $jsBlock = $this->getAbTestJs();

            $this->echoSlickstreamComment("Bootloader:", false);
            echo "<script>\n";
            echo "'use strict';\n";
            if ($adThriveAbTest) {
                echo $jsBlock;
                echo "if (window.slickAbTestResult(" . $enabledPercent . ")) {\n";
            }
            echo '"use strict";(async(e,t)=>{if(location.search.indexOf("no-slick")>=0){return}let o;const c=()=>performance.now();let a=window.$slickBoot=window.$slickBoot||{};a.rt=e;a._es=c();a.ev="2.0.1";a.l=async(e,t)=>{try{let a=0;if(!o&&"caches"in self){o=await caches.open("slickstream-code")}if(o){let n=await o.match(e);if(!n){a=c();await o.add(e);n=await o.match(e);if(n&&!n.ok){n=undefined;o.delete(e)}}if(n){const e=n.headers.get("x-slickstream-consent");return{t:a,d:t?await n.blob():await n.json(),c:e||"na"}}}}catch(e){console.log(e)}return{}};const n=e=>new Request(e,{cache:"no-store"});if(!a.d||a.d.bestBy<Date.now()){const o=n(`${e}/d/page-boot-data?site=${t}&url=${encodeURIComponent(location.href.split("#")[0])}`);let{t:s,d:i,c:d}=await a.l(o);if(i){if(i.bestBy<Date.now()){i=undefined}else if(s){a._bd=s;a.c=d}}if(!i){a._bd=c();const e=await fetch(o);const t=e.headers.get("x-slickstream-consent");a.c=t||"na";i=await e.json()}if(i){a.d=i;a.s="embed"}}if(a.d){let e=a.d.bootUrl;const{t:t,d:o}=await a.l(n(e),true);if(o){a.bo=e=URL.createObjectURL(o);if(t){a._bf=t}}else{a._bf=c()}const s=document.createElement("script");s.src=e;document.head.appendChild(s)}else{console.log("[Slick] Boot failed")}})' . "\n";
            echo '("' . $serverUrl . '","' . $siteCode . '");' . "\n";
            if ($adThriveAbTest) {
                echo "}\n";
            }
            echo "</script>\n";
            $this->echoSlickstreamComment("END Bootloader", false);
        }

        $this->echoSlickstreamComment("Page Metadata:", false);
        
        //TODO: Move this out into SR functions and cache the output
        $ldJsonElements = array();

        $ldJsonPlugin = (object) [
            '@type' => 'Plugin',
            'version' => PLUGIN_VERSION,
        ];
        
        array_push($ldJsonElements, $ldJsonPlugin);

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
        $this->echoSlickstreamComment("END Page Metadata", false);
        $this->echoConsoleOutput();
    }
}
