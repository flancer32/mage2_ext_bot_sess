<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2018
 */

namespace Flancer32\BotSess\Helper;

/**
 * Module level helper.
 */
class Mod
{
    /**
     * Thanks to David Farthing
     * (https://magento.stackexchange.com/questions/18276/magento-generating-aprox-20-session-files-per-minute)
     */
    const BOT_REGEX = '/^alexa|^blitz\.io|bot|^browsermob|crawl|^curl|^facebookexternalhit|feed|google web preview|^ia_archiver|^java|jakarta|^load impact|^magespeedtest|monitor|nagios|^pinterest|postrank|slurp|spider|uptime|yandex/i';

    /** @var bool cached isBot result based on HTTP var */
    private $cacheIsBot = null;

    public function __construct()
    {
        /** Analyze HTTP request var to cache isBot result */
        $userAgent = empty($_SERVER['HTTP_USER_AGENT']) ? null : $_SERVER['HTTP_USER_AGENT'];
        $this->cacheIsBot = $this->isBotAgentMatched($userAgent);
    }

    /**
     * Analyze $agent and return 'true' if agent is like a bot. If $userAgent is null then cached result for
     * HTTP request var analyze is used.
     *
     * @param string $userAgent
     * @return bool
     */
    public function isBot($userAgent = null)
    {
        if ($userAgent) {
            $result = $this->isBotAgentMatched($userAgent);
        } else {
            $result = $this->cacheIsBot;
        }
        return $result;
    }

    /**
     * 'true' if $agent empty (false, null) or contains bot signature.
     *
     * @param string|null $agent
     * @return bool
     */
    private function isBotAgentMatched($agent)
    {
        $result = !$agent || preg_match(self::BOT_REGEX, $agent);
        return $result;
    }
}