<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2018
 */

namespace Flancer32\BotSess\Helper;

/**
 * Filter for User-Agent HTTP header to get bots.
 *
 * Special thanks to David Farthing
 * (https://magento.stackexchange.com/questions/18276/magento-generating-aprox-20-session-files-per-minute)
 */
class Filter
{
    /** @var bool cached `isBot` result based on HTTP headers (for current request) */
    private $cacheIsBot;

    /** @var string Regex with bots signatures */
    private $regex;

    public function __construct(
        \Flancer32\BotSess\Helper\Config $hlpCfg
    ) {
        $this->regex = $hlpCfg->getFilter();
    }

    /**
     * Analyse HTTP headers and cache the result.
     */
    private function analyzeHttpHeaders()
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
        } elseif (is_null($this->cacheIsBot)) {
            $this->analyzeHttpHeaders();
            $result = $this->cacheIsBot;
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
        $result = !$agent || preg_match($this->regex, $agent);
        return $result;
    }
}
