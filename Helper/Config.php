<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Flancer32\BotSess\Helper;

/**
 * Helper to get store configuration parameters related to the module.
 *
 * (see ./src/etc/adminhtml/system.xml)
 */
class Config
{

    const DEF_BOT_CLEANUP_DELTA = 3600;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    private $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Max lifetime for bots sessions (in sec.) before cleanup.
     *
     * @return bool
     */
    public function getBotsCleanupDelta()
    {
        $result = $this->scopeConfig->getValue('web/session/bots_cleanup_delta');
        $result = filter_var($result, FILTER_VALIDATE_INT);
        if ($result <= 0) {
            $result = self::DEF_BOT_CLEANUP_DELTA;
        }
        return $result;
    }

}