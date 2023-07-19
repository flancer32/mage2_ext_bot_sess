<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2018
 */

namespace Flancer32\BotSess\Command;

use Flancer32\BotSess\Service\Clean\A\Request as ARequest;
use Flancer32\BotSess\Service\Clean\A\Response as AResponse;
use Flancer32\BotSess\Service\Clean as CleanService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console command to clean up crawlers/bots sessions existing in DB.
 */
class Clean extends Command
{
    const DESC = 'Clean crawlers/bots sessions existing in DB.';
    private const NAME = 'fl32:botsess:clean';

    /** @var CleanService */
    protected CleanService $servClean;

    public function __construct(
        CleanService $servClean
    ) {
        parent::__construct();
        $this->servClean = $servClean;
    }

    protected function configure(): void
    {
        $this->setName(self::NAME);
        $this->setDescription(self::DESC);

        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $exitCode = 0;

        /** define local working data */
        $output->writeln("Command '{$this->getName()}' is started.");

        try {
            /** perform operation */
            $req = new ARequest();
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
            $output->writeln("'{$resp->removedInactive}' sessions are deleted as inactive users.");
            $output->writeln("'{$resp->failures}' sessions have a failures during analyze.");
            $output->writeln("'{$resp->active}' sessions are belong to active users.");
            $output->writeln("Command '{$this->getName()}' is executed.");
        } catch (LocalizedException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            $exitCode = 1;
        }

        return $exitCode;
    }

}
