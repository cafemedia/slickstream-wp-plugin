<?php

declare(strict_types=1);

namespace Slickstream;

require_once 'Utils.php';

class PageBootData extends OptionsManager
{
    private const PAGE_BOOT_DATA_DEFAULT_TTL = 60 * MINUTE_IN_SECONDS;
    private const URL_TO_PAGE_GROUP_ID_TTL = 12 * HOUR_IN_SECONDS;
    private string $scriptClass;
    private ?string $pageGroupId;
    private ?object $pageBootData;
    private ?string $pageGroupTransientName;
    private string $pageGroupIdTransientName;
    private string $siteCode;
    private string $serverUrlBase;
    private string $urlPath;
    private Utils $utils;

    public function __construct(string $serverUrlBase, string $siteCode, string $scriptClass)
    {
        parent::__construct();
        $this->scriptClass = $scriptClass;
        $this->serverUrlBase = $serverUrlBase;
        $this->siteCode = addslashes(substr($siteCode, 0, 9));
        $this->utils = Utils::getInstance();
        $this->urlPath = $this->getCurrentUrlPath();
        $this->pageGroupIdTransientName = 'slick_page_group_id_' . md5($_SERVER['SERVER_NAME'] . $this->urlPath);
        $this->pageGroupId = $this->getPageGroupId();
        $this->pageGroupTransientName = $this->getPageGroupTransientName();
        $this->pageBootData = $this->getPageBootData();
        // Ensure transient name is always initialized
        if (empty($this->pageGroupIdTransientName)) {
            $this->pageGroupIdTransientName = 'slick_page_group_id_default';
        }
    }



    private function getPageBootDataForDevice(): object
    {
        if (isset($this->pageBootData->v2)) {
            if ($this->utils->isMobile() && isset($this->pageBootData->v2->phone)) {
                return $this->pageBootData->v2->phone;
            }
            return $this->pageBootData->v2->desktop;
        }
        return $this->pageBootData;
    }

    private function echoClsContainerScript(): void
    {
        $deviceBootData = $this->getPageBootDataForDevice();

        $filmstripConfig = $deviceBootData->filmstrip ?? '';
        $dcmConfig = $deviceBootData->inlineSearch ?? '';
        $emailCapConfig = $deviceBootData->emailCapture ?? '';

        if (!empty($filmstripConfig) || !empty($dcmConfig) || !empty($emailCapConfig)) {
            $filmstripStr = empty($filmstripConfig) ? '' : json_encode($filmstripConfig) || '';
            $dcmStr = empty($dcmConfig) ? '' : json_encode($dcmConfig) || '';
            $emailCapStr = empty($emailCapConfig) ? '' : json_encode($emailCapConfig) || '';

            $this->utils->echoComment('CLS Container Insertion:', false, false);

            // NOTE: The source of the minified JavaScript below is: slickstream-client/blob/main/src/plugin/cls-inject.ts
            // This script will insert the filmstrip, DCM, and email container elements into the page to eliminate CLS on those widgets.
            // TODO: This should be pulled in over HTTP and cached in Wordpress, not embedded directly like this.
            echo "\n<script>//cls-inject.ts v2.15.1\n";
            echo "\"use strict\";(async(e,t,n)=>{const o=\"[slickstream]\";const r=\"cls-inject\";const i=e=>{if(!e){return null}try{return JSON.parse(e)}catch(t){console.error(o,r,\"Failed to parse config:\",e,t);return null}};const c=i(e);const s=i(t);const a=i(n);if(!c&&!s&&!a){return}let l=0;const d=50;const u=()=>{if(!document.body){l++;if(l<d){window.requestAnimationFrame(u)}else{console.warn(o,r,\"inject: document.body not found after max retries\")}return}void y()};const f=async(e,t,n)=>{const o=document.createElement(\"div\");o.classList.add(t);o.classList.add(\"cls-inserted\");o.style.minHeight=n+\"px\";const r=[\"article p\",\"section.wp-block-template-part div.entry-content p\"];for(const t of r){const n=document.querySelectorAll(t);if((n===null||n===void 0?void 0:n.length)>=e){const t=n[e-1];t.insertAdjacentElement(\"afterend\",o);return o}}return null};const m=async e=>{const t=\"slick-on-page\";try{if(document.querySelector(`.\${t}`)){return}const n=g()?e.minHeightMobile||220:e.minHeight||200;if(e.cssSelector){await h(e.cssSelector,\"before selector\",t,n,\"\",undefined)}else{await f(e.pLocation||3,t,n)}}catch(e){console.error(o,r,`Failed to inject \${t} container`,e)}};const y=async()=>{if(c){await h(c.selector,c.position||\"after selector\",\"slick-film-strip\",c.minHeight||72,c.margin||c.marginLegacy||\"10px auto\")}if(s){const e=Array.isArray(s)?s:[s];for(const t of e){if(t.selector){await h(t.selector,t.position||\"after selector\",\"slick-inline-search-panel\",t.minHeight||350,t.margin||t.marginLegacy||\"50px 15px\",t.id)}}}if(a){await m(a)}};const g=()=>{const e=navigator.userAgent;const t=/Tablet|iPad|Playbook|Nook|webOS|Kindle|Silk|SM-T|GT-P|SCH-I800|Xoom|Transformer|Tab|Slate|Pixel C|Nexus 7|Nexus 9|Nexus 10|SHIELD Tablet|Lenovo Tab|Mi Pad|Android(?!.*Mobile)/i.test(e);const n=/Mobi|iP(hone|od)|Android.*Mobile|Opera Mini|IEMobile|WPDesktop|BlackBerry|BB10|webOS|Fennec/i.test(e);return n&&!t};const w=async e=>new Promise(t=>{setTimeout(t,e)});const p=async(e,t,n,o,r)=>{const i=document.querySelector(e);if(i){return i}const c=Date.now();if(c-n>=t){console.error(o,r,`Timeout waiting for selector: \${e}`);return null}await w(200);return p(e,t,n,o,r)};const b=async(e,t)=>{const n=Date.now();return p(e,t,n,o,r)};const h=async(e,t,n,i,c,s)=>{try{const a=await b(e,5e3);const l=s?document.querySelector(`.\${n}[data-config=\"\${s}\"]`):document.querySelector(`.\${n}`);if(l){console.warn(o,r,`Container element already exists for \${n} class with selector \${e}`);return null}if(!a){console.warn(o,r,`Target node not found for selector: \${e}`);return null}const d=document.createElement(\"div\");d.style.minHeight=i+\"px\";d.style.margin=c;d.classList.add(n,\"cls-inserted\");if(s){d.dataset.config=s}const u={\"after selector\":\"afterend\",\"before selector\":\"beforebegin\",\"first child of selector\":\"afterbegin\",\"last child of selector\":\"beforeend\"};a.insertAdjacentElement(u[t]||\"afterend\",d);return d}catch(t){console.warn(o,r,`Failed to inject \${n} for selector \${e}`);return null}};window.requestAnimationFrame(u)})";
            echo "\n('" . addslashes($filmstripStr) . "','" .
                addslashes($dcmStr) . "','" .
                addslashes($emailCapStr) . "');" . "\n";
            echo "\n</script>\n";

            $this->utils->echoComment('END CLS Container Script Insertion', false, false);
        }
    }

    private function getPageBootData(): ?object
    {
        if (
            !$this->pageGroupId || !$this->siteCode || !$this->urlPath ||
            !$this->pageGroupIdTransientName || !$this->pageGroupTransientName
        ) {
            $this->utils->echoComment('getPageBootData Error: Missing Required Data; Skipping Page Boot Data. Details:');
            $this->utils->echoComment('pageGroupId: ' . ($this->pageGroupId ?? 'null'));
            $this->utils->echoComment("siteCode: {$this->siteCode}");
            $this->utils->echoComment("urlPath: {$this->urlPath}");
            $this->utils->echoComment('pageGroupIdTransientName: ' . ($this->pageGroupIdTransientName || 'null'));
            $this->utils->echoComment('pageGroupTransientName: ' . ($this->pageGroupTransientName || 'null'));
            return null;
        }

        $noTransientPageBootData = (
            false === (
                $pageBootData = get_transient($this->pageGroupTransientName)
            )
        );

        if ($noTransientPageBootData) {
            $pageBootData = $this->fetchPageBootData();
            if ($pageBootData) {
                $pageBootDataTtl = $pageBootData->wpPluginTtl ?? self::PAGE_BOOT_DATA_DEFAULT_TTL;
                set_transient($this->pageGroupTransientName, $pageBootData, $pageBootDataTtl);
                $this->utils->echoComment("Stored Page Boot Data in Transient Cache Using Key: $this->pageGroupTransientName for $pageBootDataTtl Seconds.");
            } else {
                $this->utils->echoComment("ERROR: Unable to Fetch Page Boot Data from Server");
                return null;
            }
        }
        $this->utils->echoComment("Retrieved Page Boot Data from from Transient Cache for Page Group ID: $this->pageGroupId from Key: $this->pageGroupTransientName");
        return $pageBootData;
    }

    // Fetch the Page Boot Data Object by Site Code and Page Group ID from the server
    private function fetchPageBootData(): ?object
    {
        $this->utils->echoComment("Fetching Page Boot Data From Server");

        if (!$this->siteCode) {
            $this->utils->echoComment("fetchPageBootData Error: Missing Site Code");
            return null;
        }
        if (!$this->pageGroupId) {
            $this->utils->echoComment("fetchPageBootData Error: Missing Page Group ID");
            return null;
        }

        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $pageUrl = $protocol . '://' . $_SERVER['SERVER_NAME'] . $this->urlPath;
        $pageBootDataUrl = $this->serverUrlBase . '/d/page-boot-data?site=' . rawurlencode($this->siteCode) . '&url=' . rawurlencode($pageUrl);
        try {
            $returnVal = $this->utils->fetchRemoteObject($pageBootDataUrl);
        } catch (\Exception $e) {
            $this->utils->echoComment("fetchPageBootData Error (fetching $pageBootDataUrl): " . $e->getMessage(), true, false, true);
            return null;
        }
        return $returnVal;
    }

    public function handlePageBootData(): void
    {
        if (wp_get_environment_type() === 'local' && !$this->utils->isDebugModeEnabled()) {
            $this->utils->echoComment('Local Environment Detected; Skipping Page Boot Data');
            return;
        }

        // If `delete-boot=1` is passed as a query param, delete the stored page boot data (and embed code)
        $pageBootDataDeleted = $this->handleDeletePageBootData();

        // If `slick-boot=1` is passed as a query param, force a re-fetch of the boot data from the server
        // If `slick-boot=0` is passed as a query param, skip fetching boot data from the server
        $slickBootParam = $this->utils->getQueryParamByName('slick-boot');
        $forceFetchPageBootData = ($slickBootParam === '1');
        $dontLoadPageBootData = ($slickBootParam === '0');

        if ($forceFetchPageBootData || $pageBootDataDeleted) {
            $this->pageBootData = $this->fetchPageBootData();
        } elseif ($dontLoadPageBootData) {
            $this->utils->echoComment('Skipping Page Boot Data and CLS Container Output');
            return;
        }

        if ($this->pageBootData) {
            $this->echoSlickBootJs();
            $this->echoClsContainerScript(); // This is dependent on page boot data existing
        } else {
            $this->utils->echoComment('No Page Boot Data Available; Front-end will Fetch it Instead');
        }
    }

    private function getCurrentUrlPath(): string
    {
        $parsedUrl = parse_url('http://' . (string) $_SERVER['HTTP_HOST'] . (string) $_SERVER['REQUEST_URI']);
        $path = '';

        if (isset($parsedUrl['path'])) {
            $path = ($parsedUrl['path'] === '/') ? '/' :
                rtrim($parsedUrl['path'], '/');
        }

        return $path;
    }

    // Fetch a single page URL path to Page Group ID from the server by site code and URL path
    private function fetchPageGroupId(): ?string
    {
        $this->utils->echoComment("Fetching Page Group ID From Server");

        if (!$this->siteCode) {
            $this->utils->echoComment("fetchPageBootData Error: Missing Site Code");
            return null;
        }

        $urlPathToPageGroupIdUrl = "{$this->serverUrlBase}/d/url-page-group?site={$this->siteCode}&url={$this->urlPath}";
        return $this->utils->fetchRemoteObject($urlPathToPageGroupIdUrl, 1, 'text');
    }

    private function getPageGroupId(): ?string
    {
        $noTransientPageGroupIdExists = (
            false === (
                $pageGroupId = get_transient($this->pageGroupIdTransientName)
            )
        );

        if ($noTransientPageGroupIdExists) {
            $pageGroupId = $this->fetchPageGroupId();
            if ($pageGroupId) {
                set_transient($this->pageGroupIdTransientName, $pageGroupId, self::URL_TO_PAGE_GROUP_ID_TTL);
                $this->utils->echoComment("Successfully Cached Page Group ID With Key: $this->pageGroupIdTransientName for URL Path: $this->urlPath");
            } else {
                $this->utils->echoComment("Failed to Fetch Page Group ID for URL Path: $this->urlPath");
                return null;
            }
        }
        $this->utils->echoComment("Retrieved Page Group ID: '{$pageGroupId}' from Transient Cache from Key: $this->pageGroupIdTransientName");
        return $pageGroupId;
    }

    private function echoSlickBootJs(): void
    {
        $pageBootDataJson = json_encode($this->pageBootData);

        if (null === $pageBootDataJson || json_last_error() !== JSON_ERROR_NONE) {
            $this->utils->echoComment('Error Encoding Page Boot Data JSON');
            return;
        }

        $this->utils->echoComment('Page Boot Data:', false, false);
        echo <<<JSBLOCK
        <script class='$this->scriptClass'>
        (function() {
            "slickstream";
            const win = window;
            win.\$slickBoot = win.\$slickBoot || {};
            win.\$slickBoot.d = $pageBootDataJson;
            win.\$slickBoot.rt = '$this->serverUrlBase';
            win.\$slickBoot.s = 'plugin';
            win.\$slickBoot._bd = performance.now();
        })();
        </script>\n
        JSBLOCK;
        $this->utils->echoComment('END Page Boot Data', false, false);
    }

    private function getPageGroupTransientName(): ?string
    {
        if (!$this->pageGroupId) {
            return null;
        }
        return 'slick_page_group_' . md5($_SERVER['SERVER_NAME'] . $this->pageGroupId);
    }

    private function handleDeletePageBootData(): bool
    {
        $deleteTransientParam = $this->utils->getQueryParamByName('delete-boot');
        $shouldDeleteTransientData = ($deleteTransientParam === '1');

        if (!$shouldDeleteTransientData) {
            return false;
        }

        $this->utils->echoComment("Deleting Page Boot Data From Cache With Key: $this->pageGroupTransientName", true, true, false);
        $deleteComment = (false === delete_transient($this->pageGroupTransientName)) ?
            "Nothing to do--Page Boot Data Not Found in Cache" : "Page Boot Data Transient Deleted Successfully";
        $this->utils->echoComment($deleteComment);

        $this->utils->echoComment("Deleting Page Group ID From Cache With Key: $this->pageGroupIdTransientName", true, true, false);
        $deleteComment = (false === delete_transient($this->pageGroupIdTransientName)) ?
            "Nothing to do--Page Group ID Not Found in Cache" : "Page Group ID Transient Deleted Successfully";
        $this->utils->echoComment($deleteComment);

        $this->utils->echoComment("Deleting Embed Code From Cache With Key: slickstream_embed_code", true, true, false);
        $deleteComment = (false === delete_transient('slickstream_embed_code')) ?
            "Nothing to do--Embed Code Not Found in Cache" : "Embed Code Transient Deleted Successfully";
        $this->utils->echoComment($deleteComment);

        $this->pageBootData = null;
        return true;
    }
}
