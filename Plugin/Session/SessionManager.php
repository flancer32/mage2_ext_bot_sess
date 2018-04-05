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

    public function __construct(
        \Flancer32\BotSess\Helper\Filter $hlp
    ) {
        $this->hlp = $hlp;
    }

    public function aroundStart(
        \Magento\Framework\Session\SessionManager $subject,
        \Closure $proceed
    ) {
        $result = $subject;
        $isBot = $this->hlp->isBot();
        if (!$isBot) {
            /* not bot - proceed with session start */
            $result = $proceed();
        }
        return $result;
    }
}