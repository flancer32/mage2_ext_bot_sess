<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2018
 */

namespace Flancer32\BotSess\Service\Clean\A;


class Response
{
    /** @var int total count of active sessions left in DB */
    public $active = 0;
    /** @var array Frequency for agents used in active sessions [agent => count] */
    public $agents = [];
    /** @var int total count of session with failures in processing */
    public $failures = 0;
    /** @var int session files that were removed (belongs to bots) */
    public $removedBots = 0;
    /** @var int total count of inactive sessions removed from DB */
    public $removedInactive = 0;
    /** @var int session files that can belongs to the users */
    public $skipped = 0;
    /** @var int total count of session files processed */
    public $total = 0;
}