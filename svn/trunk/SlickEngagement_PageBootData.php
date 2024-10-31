<?php 
declare(strict_types=1);
namespace Slickstream;

require_once 'SlickEngagement_Utils.php';

class PageBootData extends OptionsManager {
    private const PAGE_BOOT_DATA_DEFAULT_TTL = 60 * MINUTE_IN_SECONDS;
    private const URL_TO_PAGE_GROUP_MAP_TTL = 12 * HOUR_IN_SECONDS;
    private const DEFAULT_SERVER_URL = 'app.slickstream.com';
    private string $scriptClass;
    private ?string $pageGroupId;
    private ?object $pageBootData;
    private ?string $pageGroupTransientName;
    private string $siteCode;
    private string $serverUrlBase;
    private string $urlPath;
    private Utils $utils;

    public function __construct($serverUrlBase, $siteCode, $scriptClass) {
        parent::__construct();
        $this->scriptClass = $scriptClass;
        $this->serverUrlBase = $serverUrlBase;
        $this->siteCode = $siteCode;
        $this->utils = new Utils();
        $this->urlPath = $this->getCurrentUrlPath();
        $this->pageGroupId = $this->getPageGroupId();
        $this->pageGroupTransientName = $this->getPageGroupTransientName();
        $this->pageBootData = $this->getPageBootData();
    }

    private function echoComment($comment, $echoToConsole = true, $debugOnly = true): void {
        $this->utils->echoComment($comment, $echoToConsole, $debugOnly);
    }

    private function getBootDataForDevice(): object {
        if (isset($this->pageBootData->v2)) {
            if ($this->utils->isMobile() && isset($bootDataObj->v2->phone)) {
              return $this->pageBootData->v2->phone;
            }
            return $this->pageBootData->v2->desktop;
        }
        return $this->pageBootData;
    }

    private function echoClsData(): void {
        $deviceBootData = $this->getBootDataForDevice();
    
        $filmstripConfig = $deviceBootData->filmstrip ?? '';
        $dcmConfig = $deviceBootData->inlineSearch ?? '';
        $ecConfig = $deviceBootData->emailCapture ?? '';
    
        if (!empty($filmstripConfig) || !empty($dcmConfig) || !empty($ecConfig)) {
            $filmstripStr = empty($filmstripConfig) ? '' : json_encode($filmstripConfig);
            $dcmStr = empty($dcmConfig) ? '' : json_encode($dcmConfig);
            $emailCapStr = empty($ecConfig) ? '' : json_encode($ecConfig);
    
            $this->echoComment('CLS Insertion:', false, false);
    
            echo "<script>\n";
            echo "\"use strict\";(async(e,t,n)=>{const o=\"slickstream\";const i=e?JSON.parse(e):null;const r=t?JSON.parse(t):null;const c=n?JSON.parse(n):null;if(i||r||c){const e=async()=>{if(document.body){if(i){m(i.selector,i.position||\"after selector\",\"slick-film-strip\",i.minHeight||72,i.margin||i.marginLegacy||\"10px auto\")}if(r){r.forEach((e=>{if(e.selector){m(e.selector,e.position||\"after selector\",\"slick-inline-search-panel\",e.minHeight||350,e.margin||e.marginLegacy||\"50px 15px\",e.id)}}))}if(c){s(c)}return}window.requestAnimationFrame(e)};window.requestAnimationFrame(e)}const s=async e=>{const t=\"slick-on-page\";try{if(document.querySelector(`.\${t}`)){return}const n=l()?e.minHeightMobile||220:e.minHeight||200;if(e.cssSelector){m(e.cssSelector,\"before selector\",t,n,\"\",undefined)}else{a(e.pLocation||3,t,n)}}catch(e){console.log(\"plugin\",\"error\",o,`Failed to inject \${t}`)}};const a=async(e,t,n)=>{const o=document.createElement(\"div\");o.classList.add(t);o.classList.add(\"cls-inserted\");o.style.minHeight=n+\"px\";const i=document.querySelectorAll(\"article p\");if((i===null||i===void 0?void 0:i.length)>=e){const t=i[e-1];t.insertAdjacentElement(\"afterend\",o);return o}const r=document.querySelectorAll(\"section.wp-block-template-part div.entry-content p\");if((r===null||r===void 0?void 0:r.length)>=e){const t=r[e-1];t.insertAdjacentElement(\"afterend\",o);return o}return null};const l=()=>{const e=navigator.userAgent;const t=/Tablet|iPad|Playbook|Nook|webOS|Kindle|Android (?!.*Mobile).*Safari/i.test(e);const n=/Mobi|iP(hone|od)|Opera Mini/i.test(e);return n&&!t};const d=async(e,t)=>{const n=Date.now();while(true){const o=document.querySelector(e);if(o){return o}const i=Date.now();if(i-n>=t){throw new Error(\"Timeout\")}await u(200)}};const u=async e=>new Promise((t=>{setTimeout(t,e)}));const m=async(e,t,n,i,r,c)=>{try{const o=await d(e,5e3);const s=c?document.querySelector(`.\${n}[data-config=\"\${c}\"]`):document.querySelector(`.\${n}`);if(o&&!s){const e=document.createElement(\"div\");e.style.minHeight=i+\"px\";e.style.margin=r;e.classList.add(n);e.classList.add(\"cls-inserted\");if(c){e.dataset.config=c}switch(t){case\"after selector\":o.insertAdjacentElement(\"afterend\",e);break;case\"before selector\":o.insertAdjacentElement(\"beforebegin\",e);break;case\"first child of selector\":o.insertAdjacentElement(\"afterbegin\",e);break;case\"last child of selector\":o.insertAdjacentElement(\"beforeend\",e);break}return e}}catch(t){console.log(\"plugin\",\"error\",o,`Failed to inject \${n} for selector \${e}`)}return false}})\n";
            echo "('" . addslashes($filmstripStr) . "','" . addslashes($dcmStr) . "','" . addslashes($emailCapStr) . "');" . "\n";
            echo "\n</script>\n";
    
            $this->echoComment('END CLS Insertion', false, false);
        }
    }

    private function getPageBootData(): ?object {
        if (!$this->pageGroupId || !$this->siteCode || $this->pageGroupTransientName === null) {
            return null;
        }

        $noTransientPageBootData = (
            false === (
                $pageBootData = get_transient($this->pageGroupTransientName)
            )
        );

        if ($noTransientPageBootData) {
            $pageBootData = $this->fetchPageBootData();
            if (!$pageBootData) {
                $this->echoComment("ERROR: Unable to Fetch Page Boot Data from Server");
                return null;
            }

            if ($pageBootData) {
                // Read the TTL in minutes value from the boot data object; fallback to 60 minute default if not found
                $pageBootDataTtl = $pageBootData->wpPluginTtl ?? self::PAGE_BOOT_DATA_DEFAULT_TTL;
                set_transient($this->pageGroupTransientName, $pageBootData, $pageBootDataTtl);
                $this->echoComment("Stored Page Boot Data in Transient Cache Using Key: $this->pageGroupTransientName for $pageBootDataTtl Seconds.");
            }            
        }

        return $pageBootData;
    }
    
    public function handlePageBootData(): void {
        // If `delete-boot=1` is passed as a query param, delete the stored page boot data
        $this->handleDeletePageBootData();
        
        // If `slick-boot=1` is passed as a query param, force a re-fetch of the boot data from the server
        // If `slick-boot=0` is passed as a query param, skip fetching boot data from the server
        $slickBootParam = $this->utils->getQueryParamByName('slick-boot');
        $forceFetchBootData = ($slickBootParam === '1');
        $dontLoadBootData = ($slickBootParam === '0');

        if ($forceFetchBootData) {
            $this->pageBootData = $this->fetchPageBootData();
        } else if ($dontLoadBootData) {
            $this->echoComment('Skipping Page Group Data and CLS Data Output');
            return;
        }
    
        if ($this->pageBootData) {
            $this->echoSlickBootJs();
            $this->echoClsData();
        } else {
            $this->echoComment('No Page Boot Data Available; Front-end will Fetch it Instead');
        }
    }

    // Fetch the page URL path to Page Group ID mappings from the server
    private function fetchUrlToPageGroupMap(): ?object {
        $this->echoComment("Fetching Page Group Map From Server");
        
        if (!$this->siteCode) {
            $this->echoComment("fetchPageBootData Error: Missing Site Code");
            return null;
        }

        $urlToPageGroupMapUrl = "{$this->serverUrlBase}/d/url-page-group?site={$this->siteCode}";
        return $this->utils->fetchRemoteObject($urlToPageGroupMapUrl, 2);
    }
        
    // Fetch the Page Boot Data Object by Site Code and Page Group ID from the server
    private function fetchPageBootData(): ?object {
        $this->echoComment("Fetching Page Boot Data From Server");
        
        if (!$this->siteCode) {
            $this->echoComment("fetchPageBootData Error: Missing Site Code");
            return null;
        }
        if (!$this->pageGroupId) {
            $this->echoComment("fetchPageBootData Error: Missing Page Group ID");
            return null;
        }

        $pageBootDataByGroupUrl = $this->serverUrlBase . "/d/group-page-boot-data?site=$this->siteCode&pageGroupId=" . rawurlencode($this->pageGroupId);
        return $this->utils->fetchRemoteObject($pageBootDataByGroupUrl);
    }

    private function getCurrentUrlPath() {
        $scheme = $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $url = "$scheme://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
        $parsedUrl = parse_url($url);
        return dirname($parsedUrl['path']);
    }

    private function findPageGroupIdByPath($urlToPageGroupMap): ?string {
        foreach ($urlToPageGroupMap as $path => $pageGroupId) {
            if ($path === $this->urlPath) {
                return $pageGroupId;
            }
        }
        return null;
    }

    private function getPageGroupId(): ?string {
        $urlToPageGroupMap = $this->getUrlToPageGroupMap();
        return $urlToPageGroupMap ? $this->findPageGroupIdByPath($urlToPageGroupMap) : null;
    }

    private function getUrlToPageGroupMap(): ?object {
        $pageGroupMapTransientName = 'slick_page_group_map_' . md5($_SERVER['SERVER_NAME']);
        $noTransientPageGroupMapExists = (
            false === (
                $urlToPageGroupMap = get_transient($pageGroupMapTransientName)
            )
        );
        
        if ($noTransientPageGroupMapExists) {
            $urlToPageGroupMap = $this->fetchUrlToPageGroupMap();
            if (!$urlToPageGroupMap) {
                $this->echoComment("Error Fetching Page Group Map From Server");
                return null;
            }

            set_transient($pageGroupMapTransientName, $urlToPageGroupMap, self::URL_TO_PAGE_GROUP_MAP_TTL);
            $this->echoComment("Successfully Cached Page Group Map With Key: $pageGroupMapTransientName.");
        }
        return $urlToPageGroupMap;
    }

    private function echoSlickBootJs(): void {
        $pageBootDataJson = json_encode($this->pageBootData);

        if (null === $pageBootDataJson || json_last_error() !== JSON_ERROR_NONE) {
            $this->echoComment('Error Encoding Page Boot Data JSON');
            return;
        }

        $this->echoComment('Page Boot Data:', false, false);

        echo <<<JSBLOCK
        <script class='$this->scriptClass'>
        (function() {
            "slickstream";
            const win = window;
            win.\$slickBoot = win.\$slickBoot || {};
            win.\$slickBoot.d = $pageBootDataJson;
            win.\$slickBoot.s = 'plugin';
            win.\$slickBoot._bd = performance.now();
        })();
        </script>\n
        JSBLOCK;

        $this->echoComment('END Page Boot Data', false, false);
    }

    private function getPageGroupTransientName(): ?string {
        if (!$this->pageGroupId) {
            return null;
        }
        return 'slick_page_group_' . md5($this->pageGroupId);
    }

    private function handleDeletePageBootData(): void {
        $deleteTransientParam = $this->utils->getQueryParamByName('delete-boot');
        $shouldDeleteTransientData = ($deleteTransientParam === '1');

        if (!$shouldDeleteTransientData) {
            return;
        }
        $this->echoComment("Deleting Page Group Data From Cache With Key: $this->pageGroupTransientName");
        $deleteComment = (false === delete_transient($this->pageGroupTransientName)) ? 
            "Nothing to do--Page Boot Data Not Found in Cache" : "Page Boot Data Deleted Successfully";
        $this->echoComment($deleteComment);
    }
}