<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2018
 */

namespace Flancer32\BotSess\Plugin\Session;

/**
 * Don't open sessions for crawlers/bots.
 */
class SessionManager
{
    /** @var \Flancer32\BotSess\Helper\Filter */
    private $hlp;
    /** @var \Flancer32\BotSess\Logger */
    private $logger;

    public function __construct(
        \Flancer32\BotSess\Logger $logger,
        \Flancer32\BotSess\Helper\Filter $hlp
    ) {
        $this->logger = $logger;
        $this->hlp = $hlp;
    }

    public function aroundStart(
        \Magento\Framework\Session\SessionManager $subject,
        \Closure $proceed
    ) {
        $result = $subject;
        if (isset($_SERVER['REMOTE_ADDR'])) {
            /* filter HTTP requests only */
            $isBot = $this->hlp->isBot();
            if (!$isBot) {
                /* proceed with session start */
                $result = $proceed();
            } else {
                $agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'n/a';
                $address = $_SERVER['REMOTE_ADDR'];
                $this->logger->debug("Skip session for agent |$agent| from $address.");
            }
        } else {
            /* process CLI & cron requests */
            $result = $proceed();
        }
        return $result;
    }
}
