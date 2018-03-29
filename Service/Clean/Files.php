<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2018
 */

namespace Flancer32\BotSess\Service\Clean;

use Flancer32\BotSess\Service\Clean\Files\Request as ARequest;
use Flancer32\BotSess\Service\Clean\Files\Response as AResponse;

/**
 * Scan all session files and remove old ones w/o human activity.
 */
class Files
{

    /**
     * @param \Flancer32\BotSess\Service\Clean\Files\Request $request
     * @return \Flancer32\BotSess\Service\Clean\Files\Response
     */
    public function exec($request)
    {
        assert($request instanceof ARequest);

        $result = new AResponse();
        return $result;
    }
}