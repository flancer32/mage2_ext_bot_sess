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

    /**
     * Analyze $agent (or HTTP_USER_AGENT global var) and return 'true' if agent is like a bot.
     *
     * @param string $userAgent
     * @return bool
     */
    public function isBot($userAgent = null)
    {
        if(!$userAgent ) {
            $userAgent = empty($_SERVER['HTTP_USER_AGENT']) ? FALSE : $_SERVER['HTTP_USER_AGENT'];
        }
        $result = !$userAgent || preg_match(self::BOT_REGEX, $userAgent);
        return $result;
    }
}