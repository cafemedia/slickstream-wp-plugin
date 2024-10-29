<?php

class SlickEngagement_Utils {
    private $consoleOutput;

    public function __construct() {
        $this->consoleOutput = '';
    }

    public function echoComment($comment, $echoToConsole = true, $debugOnly = true): void {
        if ($debugOnly === true && !$this->isDebugModeEnabled()) {
            return;
        }

        echo "<!-- [slickstream] " . strip_tags($comment) . " -->\n";
        if ($echoToConsole) {
            $this->consoleOutput .= "$comment\n";
        }
    }

    public function isDebugModeEnabled(): bool {
        return $this->getQueryParamByName('slickdebug') === '1';
    }

    public function getQueryParamByName(string $paramName): ?string {
        return filter_input(INPUT_GET, $paramName, FILTER_SANITIZE_STRING);
    }

    public function fetchRemoteObject(string $remoteUrl, int $timeout = 1): ?object {
        $headers = ['referer' => home_url()];
        $this->echoComment("Fetching from URL: $remoteUrl");
        $this->echoComment('Headers: ' . json_encode($headers));
        $response = wp_remote_get($remoteUrl, [
            'timeout' => $timeout,
            'headers' => $headers,
        ]);

        $responseCode = wp_remote_retrieve_response_code($response);
        if (is_wp_error($response) || $responseCode !== 200) {
            $errorMsg = is_wp_error($response) ? (string) $response : 'Server-side Error';
            $this->echoComment("Error Fetching Data from $remoteUrl; Response code: $responseCode; Error: $errorMsg");
            return null;
        }

        return json_decode(wp_remote_retrieve_body($response));
    }

    //This logic matches the logic on the back-end to determine if the device is mobile
    public function isMobile(): bool {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgentStr = $_SERVER['HTTP_USER_AGENT'];
            $excluded = preg_match('/Tablet|iPad|Playbook|Nook|webOS|Kindle|Android (?!.*Mobile).*Safari/i', $userAgentStr);
            $mobile = preg_match('/Mobi|iP(hone|od)|Opera Mini/i', $userAgentStr);
            return $mobile && !$excluded;
        }
        return false;
    }

    public function removeSemicolons($value): string {
        return str_replace(';', ' ', $value);
    }
}
