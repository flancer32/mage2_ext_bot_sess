<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2018
 */

namespace Flancer32\BotSess\Service\Clean\Files;


class Response
{
    /** @var int total number of session with failures in processing */
    public $failures;
    /** @var int session files that were removed (belongs to bots) */
    public $removed;
    /** @var int session files that can belongs to the customers */
    public $skipped;
    /** @var int total number of session files processed */
    public $total;
}