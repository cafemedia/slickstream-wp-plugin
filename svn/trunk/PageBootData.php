<?php

declare(strict_types=1);

namespace Slickstream;

require_once PLUGIN_DIR_PATH(__FILE__) . 'Utils.php';

class PageBootData extends OptionsManager
{
    private const PAGE_BOOT_DATA_DEFAULT_TTL = 60 * MINUTE_IN_SECONDS;
    private const URL_TO_PAGE_GROUP_ID_TTL = 12 * HOUR_IN_SECONDS;
    private string $scriptClass;
    private ?string $pageGroupId;
    private ?object $pageBootData;
    private ?string $pageGroupTransientName = null;
    private ?string $pageGroupIdTransientName = null;
    private string $siteCode;
    private string $serverUrlBase;
    private string $urlPath;
    private Utils $utils;

    public function __construct(string $serverUrlBase, string $siteCode, string $scriptClass)
    {
        parent::__construct();
        $this->scriptClass = $scriptClass;
        $this->serverUrlBase = $serverUrlBase;
        $this->siteCode = addslashes(substr($siteCode, 0, 10));
        $this->utils = Utils::getInstance();
        $this->urlPath = $this->getCurrentUrlPath();
        $this->pageGroupIdTransientName = $this->getPageGroupIdTransientName();
        $this->pageGroupId = $this->getPageGroupId();
        $this->pageGroupTransientName = $this->getPageGroupTransientName();
        $this->pageBootData = $this->getPageBootData();
    }

    // Same rule as the front-end's boot-loader.ts: phone config if there is one, desktop otherwise
    private function getPageBootDataForDevice(bool $isPhone): object
    {
        if (isset($this->pageBootData->v2)) {
            if ($isPhone && isset($this->pageBootData->v2->phone)) {
                return $this->pageBootData->v2->phone ?? $this->pageBootData;
            }
            return $this->pageBootData->v2->desktop ?? $this->pageBootData;
        }
        return $this->pageBootData;
    }

    // Filmstrip, DCM and email capture configs of one device, JSON-encoded, '' when absent
    private function getClsConfigStrings(object $deviceBootData): array
    {
        return array_map(
            fn($config) => empty($config) ? '' : json_encode($config),
            [
                $deviceBootData->filmstrip ?? '',
                $deviceBootData->inlineSearch ?? '',
                $deviceBootData->emailCapture ?? '',
            ]
        );
    }

    private function echoClsContainerScript(): void
    {
        // Full-page caches serve this HTML to whichever device asks next, so the configs of both
        // devices are passed and cls-inject picks one in the browser
        $desktopConfigStrs = $this->getClsConfigStrings($this->getPageBootDataForDevice(false));
        $phoneConfigStrs = $this->getClsConfigStrings($this->getPageBootDataForDevice(true));
        $clsConfigStrs = array_merge($desktopConfigStrs, $phoneConfigStrs);

        // Debugging output for CLS container script injection
        $this->utils->echoComment("Desktop Filmstrip, DCM, Email Capture Configs: " . implode(' | ', $desktopConfigStrs), true, true, false);
        $this->utils->echoComment("Phone Filmstrip, DCM, Email Capture Configs: " . implode(' | ', $phoneConfigStrs), true, true, false);

        if (count(array_filter($clsConfigStrs)) > 0) {
            $this->utils->echoComment('CLS Container Script Injection:', false, false, true);

            // NOTE: The source of the minified JavaScript below is: slickstream-client/blob/main/src/plugin/cls-inject.ts
            // It is the client release named in the banner, published as https://c.slickstream.com/app/<version>/cls-inject.js
            // (minus the trailing placeholder call); rebuild from that release, not from an unreleased branch.
            // This script will insert the filmstrip, DCM, and email container elements into the page to eliminate CLS on those widgets.
            // TODO: This should be pulled in over HTTP and cached in Wordpress, not embedded directly like this.
            echo "\n<script>//cls-inject.ts v3.1.12\n";
            echo "\"use strict\";(async(e,t,n,o,r,s)=>{const i=\"[slickstream]\";const c=\"cls-inject\";const a=200;const l=50;const d=6e3;const u={onPageEmailCapture:\"slick-on-page\",dcmInlineSearch:\"slick-inline-search-panel\",filmstrip:\"slick-film-strip\"};let f=0;const m=e=>{if(!e){return null}try{return JSON.parse(e)}catch(t){console.error(i,c,\"Failed to parse config:\",e,t);return null}};const p=()=>{const e=navigator.userAgent;const t=/Mobi|iP(hone|od)|Android.*Mobile|Opera Mini|IEMobile|WPDesktop|BlackBerry|BB10|webOS|Fennec/i.test(e);const n=/Tablet|iPad|Playbook|Nook|webOS|Kindle|Silk|SM-T|GT-P|SCH-I800|Xoom|Transformer|Tab|Slate|Pixel C|Nexus 7|Nexus 9|Nexus 10|SHIELD Tablet|Lenovo Tab|Mi Pad|Android(?!.*Mobile)/i.test(e);return t&&!n};const y=p();const g=m(y?o:e);const w=m(y?r:t);const h=m(y?s:n);if(!g&&!w&&!h){return}const b=()=>{if(!document.body){f++;if(f<l){window.requestAnimationFrame(b)}else{console.warn(i,c,\"inject: document.body not found after max retries\")}return}void A().catch(e=>{console.error(i,c,\"injectAllClsDivs failed\",e)})};const S=async(e,t,n)=>{const o=document.createElement(\"div\");o.classList.add(t);o.classList.add(\"cls-inserted\");o.style.minHeight=`\${n}px`;const r=[\"article p\",\"section.wp-block-template-part div.entry-content p\"];for(const t of r){const n=document.querySelectorAll(t);if(n?.length>=e){const t=n[e-1];t.insertAdjacentElement(\"afterend\",o);return o}}return null};const \$=async e=>{const t=u.onPageEmailCapture;try{if(document.querySelector(`.\${t}`)){console.warn(i,c,`Container element already exists for \${t} class`);return}const n=y?e.minHeightMobile||220:e.minHeight||200;if(e.cssSelector){await T(e.cssSelector,\"before selector\",t,n,\"\",undefined)}else{await S(e.pLocation||3,t,n)}}catch(e){console.error(i,c,`Failed to inject \${t} container`,e)}};const k=async e=>{if(e.selector){await T(e.selector,e.position||\"after selector\",u.filmstrip,e.minHeight||72,e.margin||e.marginLegacy||\"10px auto\")}else{console.warn(i,c,\"Filmstrip config missing selector property\")}};const x=async e=>{const t=Array.isArray(e)?e:[e];for(const e of t){if(e.selector){await T(e.selector,e.position||\"after selector\",u.dcmInlineSearch,e.minHeight||350,e.margin||e.marginLegacy||\"50px 15px\",e.id)}else{console.warn(i,c,\"DCM config is missing selector property:\",e)}}};const A=async()=>{if(g){await k(g)}if(w){await x(w)}if(h){await \$(h)}};const C=async e=>new Promise(t=>{setTimeout(t,e)});const E=async(e,t,n,o,r)=>{const s=document.querySelector(e);if(s){return s}const i=Date.now();if(i-n>=t){console.error(o,r,`Timeout waiting for selector: \${e}`);return null}await C(a);return E(e,t,n,o,r)};const P=async(e,t)=>{const n=Date.now();return E(e,t,n,i,c)};const T=async(e,t,n,o,r,s)=>{try{if(!e||e===\"undefined\"){console.warn(i,c,`Selector is empty or \"undefined\" for \${n} class; nothing to do`);return null}const a=await P(e,d);const l=s?document.querySelector(`.\${n}[data-config=\"\${s}\"]`):document.querySelector(`.\${n}`);if(l){console.warn(i,c,`Container element already exists for \${n} class with selector \${e}`);return null}if(!a){console.warn(i,c,`Target node not found for selector: \${e}`);return null}const u=document.createElement(\"div\");u.style.minHeight=`\${o}px`;u.style.margin=r;u.classList.add(n,\"cls-inserted\");if(s){u.dataset.config=s}const f={\"after selector\":\"afterend\",\"before selector\":\"beforebegin\",\"first child of selector\":\"afterbegin\",\"last child of selector\":\"beforeend\"};a.insertAdjacentElement(f[t]||\"afterend\",u);return u}catch(t){console.error(i,c,`Failed to inject \${n} for selector \${e}`,t);return null}};const F=()=>{window.requestAnimationFrame(b)};F()})";
            echo "\n('" . implode("','", array_map('addslashes', $clsConfigStrs)) . "');" . "\n";
            echo "\n</script>\n";

            $this->utils->echoComment('END CLS Container Script Injection', false, false, true);
        } else {
            $this->utils->echoComment('CLS Script Injection: Filmstrip, DCM, and Email Capture configs all empty on every device; CLS Script not injected');
        }
    }

    private function getPageBootData(): ?object
    {
        if (empty($this->pageGroupId) || empty($this->siteCode) || $this->urlPath === '') {
            $this->utils->echoComment('getPageBootData Error: Missing Required Data; Skipping Page Boot Data. Details:');
            $this->utils->echoComment('pageGroupId: ' . ($this->pageGroupId ?? 'null'));
            $this->utils->echoComment("siteCode: {$this->siteCode}");
            $this->utils->echoComment("urlPath: {$this->urlPath}");
            $this->utils->echoComment('pageGroupIdTransientName: ' . ($this->pageGroupIdTransientName ?? 'null'));
            $this->utils->echoComment('pageGroupTransientName: ' . ($this->pageGroupTransientName ?? 'null'));
            return null;
        }

        $transientKey = $this->getPageGroupTransientName();

        if (empty($transientKey)) {
            $this->utils->echoComment("getPageBootData Error: pageGroupTransientName is null or empty; cannot fetch transient.");
        }

        $noTransientPageBootData = (
            false === (
                $pageBootData = get_transient($transientKey)
            )
        );

        // Fetch from server if we can't find page boot data in the transient cache
        if ($noTransientPageBootData) {
            $pageBootData = $this->fetchPageBootData();
            if ($pageBootData) {
                $pageBootDataTtl = $pageBootData->wpPluginTtl ?? self::PAGE_BOOT_DATA_DEFAULT_TTL;
                set_transient($transientKey, $pageBootData, $pageBootDataTtl);
                $this->utils->echoComment("Stored Page Boot Data in Transient Cache Using Key: $transientKey for $pageBootDataTtl Seconds.");
            } else {
                $this->utils->echoComment("ERROR: Unable to Fetch Page Boot Data from Server");
                return null;
            }
        }

        $this->utils->echoComment("Retrieved Page Boot Data from from Transient Cache for Page Group ID: $this->pageGroupId from Key: $transientKey", true, true, false);
        // $this->utils->echoComment('Page Boot Data: ' . json_encode($pageBootData), true, true, false);
        return $pageBootData;
    }

    // Fetch the Page Boot Data Object by Site Code and Page Group ID from the server
    private function fetchPageBootData(): ?object
    {
        $this->utils->echoComment("Fetching Page Boot Data From Server", true, true, false);

        if (!$this->siteCode) {
            $this->utils->echoComment("fetchPageBootData Error: Missing Site Code");
            return null;
        }
        if (!$this->pageGroupId) {
            $this->utils->echoComment("fetchPageBootData Error: Missing Page Group ID");
            return null;
        }

        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $pageUrl = $protocol . '://' . $serverName . $this->urlPath;
        $pageBootDataUrl = $this->serverUrlBase . '/d/page-boot-data?site=' . rawurlencode($this->siteCode) . '&url=' . rawurlencode($pageUrl);
        try {
            $returnVal = $this->utils->fetchRemoteObject($pageBootDataUrl, 2, 'json');
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
        $httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $parsedUrl = parse_url('http://' . (string) $httpHost . (string) $requestUri);
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
        return $this->utils->fetchRemoteObject($urlPathToPageGroupIdUrl, 2, 'text');
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
        $this->utils->echoComment("Retrieved Page Group ID: '{$pageGroupId}' from Transient Cache from Key: $this->pageGroupIdTransientName", true, true, false);
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

    // Returns the Page Group Transient Name
    private function getPageGroupTransientName(): ?string
    {
        if (!empty($this->pageGroupTransientName)) {
            return $this->pageGroupTransientName;
        }

        if (empty($this->pageGroupId)) {
            return null;
        }

        $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $this->pageGroupTransientName = 'slick_page_group_' . md5("{$serverName}{$this->pageGroupId}");
        return $this->pageGroupTransientName;
    }

    // Returns the Page Group ID Transient Name
    private function getPageGroupIdTransientName(): string
    {
        $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
        return 'slick_page_group_id_' . md5("{$serverName}{$this->urlPath}");
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
        $this->utils->echoComment($deleteComment, true, true, false);

        $this->utils->echoComment("Deleting Page Group ID From Cache With Key: $this->pageGroupIdTransientName", true, true, false);
        $deleteComment = (false === delete_transient($this->pageGroupIdTransientName)) ?
            "Nothing to do--Page Group ID Not Found in Cache" : "Page Group ID Transient Deleted Successfully";
        $this->utils->echoComment($deleteComment, true, true, false);

        $this->utils->echoComment("Deleting Embed Code From Cache With Key: slickstream_embed_code", true, true, false);
        $deleteComment = (false === delete_transient('slickstream_embed_code')) ?
            "Nothing to do--Embed Code Not Found in Cache" : "Embed Code Transient Deleted Successfully";
        $this->utils->echoComment($deleteComment, true, true, false);

        $this->pageBootData = null;
        return true;
    }
}
