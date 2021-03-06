<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2018
 */

namespace Flancer32\BotSess;


class Logger
    extends \Monolog\Logger
{
    const FILENAME = 'fl32.botsess.log';
    const NAME = 'FL32BOTS';

    public function __construct()
    {
        $handlers = $this->initHandlers();
        $processors = [];
        parent::__construct(static::NAME, $handlers, $processors);
    }

    private function initFormatter()
    {
        $dateFormat = "Y-m-d H:i:s";
        $msgFormat = "%datetime%: %message%\n";
        $result = new \Monolog\Formatter\LineFormatter($msgFormat, $dateFormat);
        return $result;
    }

    private function initHandlers()
    {
        $result = [];
        $path = BP . '/var/log/' . static::FILENAME;
        $handler = new \Monolog\Handler\StreamHandler($path);
        $formatter = $this->initFormatter();
        $handler->setFormatter($formatter);
        $result[] = $handler;
        return $result;
    }
}