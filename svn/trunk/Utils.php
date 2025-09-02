<?php

declare(strict_types=1);

namespace Slickstream;

class Utils
{
    /** @var Utils|null */
    private static ?Utils $instance = null;

    public function __clone()
    {
    }

    public function __wakeup()
    {
    }

    /**
     * @return Utils
     */
    public static function getInstance(): Utils
    {
        if (self::$instance === null) {
            self::$instance = new Utils();
        }
        return self::$instance;
    }

    /**
     * @param string $comment
     * @param bool $isBrowserConsoleComment
     * @param bool $debugOnly
     * @param bool $isHtmlComment
     */
    public function echoComment(
        string $comment,
        bool $isBrowserConsoleComment = true,
        bool $debugOnly = true,
        bool $isHtmlComment = true
    ): void {
        if ($debugOnly === true && !$this->isDebugModeEnabled()) {
            return;
        }

        if ($isHtmlComment) {
            echo "<!-- [slickstream] " . strip_tags($comment) . " -->\n";
        }

        if ($isBrowserConsoleComment) {
            $this->echoConsoleOutput($comment);
        }
    }

    /**
     * @param string $output
     */
    public function echoConsoleOutput(string $output): void
    {
        if ($output !== '') {
            $safeOutput = addslashes(strip_tags($output));
            echo "\t\t<script>console.info(`[slickstream] $safeOutput`);</script>\n";
        }
    }

    public function isDebugModeEnabled(): bool
    {
        return $this->getQueryParamByName('slickdebug') === '1';
    }

    /**
     * @param string $paramName
     * @return string|null
     */
    public function getQueryParamByName(string $paramName): ?string
    {
        $result = filter_input(INPUT_GET, $paramName, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        return $result !== null ? (string)$result : null;
    }

    /**
     * @param string $remoteUrl
     * @param int $timeout
     * @param string $type
     * @return mixed|null
     */
    public function fetchRemoteObject(string $remoteUrl, int $timeout = 1, string $type = 'json')
    {
        if (!in_array($type, ['json', 'text'])) {
            throw new \InvalidArgumentException("The type parameter must be either 'json' or 'text'.");
        }
        $headers = ['referer' => home_url()];
        $this->echoComment("Fetching from URL: $remoteUrl", true, true, false);
        $this->echoComment('Headers: ' . json_encode($headers), true, true, false);
        $response = wp_remote_get($remoteUrl, [
            'timeout' => $timeout,
            'headers' => $headers,
        ]);
        $responseCode = wp_remote_retrieve_response_code($response);
        if (is_wp_error($response) || $responseCode !== 200) {
            $errorMsg = is_wp_error($response) ? $response->get_error_message() : 'Server-side Error';
            $this->echoComment("Error Fetching Data from $remoteUrl; Response code: " . (string)$responseCode . "; Error: " . (string)$errorMsg);
            return null;
        }
        return $type === 'text' ?
            wp_remote_retrieve_body($response) :
            json_decode(wp_remote_retrieve_body($response));
    }

    /**
     * @param string $remoteUrl
     * @param int $timeout
     * @return object|null
     */
    public function fetchRemote(string $remoteUrl, int $timeout = 1): ?object
    {
        $errObj = (object)[
            'status' => 'error',
            'body' => null
        ];

        if (empty($remoteUrl)) {
            return $errObj;
        }

        $response = wp_remote_get($remoteUrl, [
            'timeout' => $timeout,
            'headers' => ['referer' => home_url()],
        ]);

        $responseCode = wp_remote_retrieve_response_code($response);

        if ($responseCode === 200 && !is_wp_error($response)) {
            return (object)[
                'status' => 'success',
                'body' => wp_remote_retrieve_body($response)
            ];
        } else {
            return $errObj;
        }
    }

    // This logic matches the logic on the back-end to determine if the device is mobile
    public function isMobile(): bool
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgentStr = $_SERVER['HTTP_USER_AGENT'];
            $excluded = preg_match('/Tablet|iPad|Playbook|Nook|webOS|Kindle|Android (?!.*Mobile).*Safari/i', $userAgentStr);
            $mobile = preg_match('/Mobi|iP(hone|od)|Opera Mini/i', $userAgentStr);
            return $mobile && !$excluded;
        }
        return false;
    }

    /**
     * @param string $value
     * @return string
     */
    public function removeSemicolons(string $value): string
    {
        return str_replace(';', ' ', $value);
    }
}
