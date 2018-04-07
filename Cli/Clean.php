<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2018
 */

namespace Flancer32\BotSess\Cli;

use Flancer32\BotSess\Service\Clean\A\Request as ARequest;
use Flancer32\BotSess\Service\Clean\A\Response as AResponse;

/**
 * Console command to clean up crawlers/bots sessions existing in DB.
 */
class Clean
    extends \Symfony\Component\Console\Command\Command
{

    const DESC = 'Clean crawlers/bots sessions existing in DB.';
    const NAME = 'fl32:botsess:clean';

    /** @var \Flancer32\BotSess\Service\Clean */
    protected $servClean;

    public function __construct(
        \Flancer32\BotSess\Service\Clean $servClean
    ) {
        parent::__construct(self::NAME);
        /* Symfony related config is performed from parent constructor */
        $this->setDescription(self::DESC);
        /* own properties */
        $this->servClean = $servClean;
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        /** define local working data */
        $output->writeln("Command '{$this->getName()}' is started.");

        /** perform operation */
        $req = new ARequest();
        /** @var AResponse $resp */
        $resp = $this->servClean->exec($req);

        /** compose result */
        $agents = $resp->agents;
        asort($agents);
        foreach ($agents as $agentName => $count) {
            $output->writeln("$count: $agentName");
        }
        $output->writeln("Total '{$resp->total}' sessions are found in DB.");
        $output->writeln("'{$resp->skipped}' sessions are not defined as bot's.");
        $output->writeln("'{$resp->removedBots}' sessions are deleted as bot's.");
        $output->writeln("'{$resp->removedInactive}' sessions are deleted as inactive customers.");
        $output->writeln("'{$resp->failures}' sessions have a failures during analyze..");
        $output->writeln("'{$resp->active}' sessions belong to active customers.");
        $output->writeln("'{$resp->admins}' sessions belong admins.");
        $output->writeln("Command '{$this->getName()}' is executed.");
    }

}