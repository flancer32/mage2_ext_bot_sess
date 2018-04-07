<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Flancer32\BotSess\Cron;

use Flancer32\BotSess\Service\Clean\A\Request as ARequest;

/**
 * Clean up bots sessions and inactive users sessions by cron.
 */
class Clean
{
    /** @var \Flancer32\BotSess\Service\Clean */
    protected $servClean;

    public function __construct(
        \Flancer32\BotSess\Service\Clean $servClean
    ) {
        $this->servClean = $servClean;
    }

    public function execute()
    {
        $req = new ARequest();
        $this->servClean->exec($req);
    }
}