<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2018
 */

namespace Flancer32\BotSess\Cli;

/**
 * Console command to clean up crawlers/bots sessions existing in DB.
 */
class Clean
    extends \Symfony\Component\Console\Command\Command
{
    /** Process DB sessions with batches to prevent freeze for other connections */
    const BATCH_LIMIT = 1000;
    const DESC = 'Clean crawlers/bots sessions existing in DB.';
    const NAME = 'fl32:botsess:clean';

    /** @var \Magento\Framework\DB\Adapter\AdapterInterface */
    private $conn;
    /** @var \Flancer32\BotSess\Helper\Filter */
    private $hlpFilter;
    /** @var \Flancer32\BotSess\Helper\Config */
    private $hlpCfg;
    /** @var \Magento\Framework\App\ResourceConnection */
    private $resource;
    /** @var \Flancer32\BotSess\Logger */
    private $logger;
    /** @var \Symfony\Component\Console\Output\OutputInterface set from execute() */
    private $output;
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Flancer32\BotSess\Logger $logger,
        \Flancer32\BotSess\Helper\Config $hlpCfg,
        \Flancer32\BotSess\Helper\Filter $hlpFilter
    ) {
        parent::__construct(self::NAME);
        /* Symfony related config is performed from parent constructor */
        $this->setDescription(self::DESC);
        $this->resource = $resource;
        $this->logger = $logger;
        $this->conn = $resource->getConnection();
        $this->hlpCfg = $hlpCfg;
        $this->hlpFilter = $hlpFilter;
    }

    /**
     * Delete session by ID.
     *
     * @param string $id
     * @return int
     */
    private function deleteSession($id)
    {
        $tbl = $this->resource->getTableName('session');
        $where = 'session_id=' . $this->conn->quote($id);
        $result = $this->conn->delete($tbl, $where);
        return $result;
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        $this->output = $output;
        /* perform operation */
        $name = self::NAME;
        $this->log("Command '$name' is started.");

        /* start session to decode session data */
        session_start();

        /* get sessions count */
        $total = $this->getSessionsCount();
        $current = 0;
        $limit = self::BATCH_LIMIT;
        $id = null;
        $agentSkipped = [];     // not bot agents skipped in cleanup
        $sessionsActive = 0;    // counter for active (or not expired inactive) sessions
        $sessionsInactive = 0;  // counter for removed inactive sessions
        $sessionsBots = 0;      // counter for bot sessions
        $now = time();          // current time
        $deltaMax = $this->hlpCfg->getBotsCleanupDelta();
        while ($current < $total) {
            /* get all sessions then process it in loop */
            $all = $this->getSessionsBatch($id, $limit);
            foreach ($all as $one) {
                $current++;
                $id = $one['session_id'];
                $created = $one['session_expires']; // session creation time (???)
                $data = $one['session_data'];
                $decoded = base64_decode($data);
                try {
                    session_decode($decoded);
                    if (isset($_SESSION['_session_validator_data']['http_user_agent'])) {
                        $agent = $_SESSION['_session_validator_data']['http_user_agent'];
                        $isBot = $this->hlpFilter->isBot($agent);
                        if ($isBot) {
                            /* remove bot session */
                            $this->log("Session '$id' belongs to bot ($agent).");
                            $deleted = $this->deleteSession($id);
                            if ($deleted == 1) {
                                $this->log("Session '$id' is deleted as bot.");
                                $sessionsBots++;
                            } else {
                                $this->logger->error("Cannot delete bot session '$id'.");
                            }
                        } else {
                            /* human session, detect session age */
                            $delta = $now - $created;
                            if ($delta > $deltaMax) {
                                /* session age is over then time delta for inactive customers */
                                if (
                                    isset($_SESSION['catalog']) &&
                                    is_array($_SESSION['catalog']) &&
                                    (count($_SESSION['catalog']) > 0) &&
                                    isset($_SESSION['checkout']) &&
                                    is_array($_SESSION['checkout']) &&
                                    (count($_SESSION['checkout']) > 0)
                                ) {
                                    /* there are not empty catalog & checkout sections in the session */
                                    $sessionsActive++;
                                } else {
                                    /* remove expired inactive session */
                                    $this->log("Session '$id' belongs to inactive customer.");
                                    $deleted = $this->deleteSession($id);
                                    if ($deleted == 1) {
                                        $this->log("Session '$id' is deleted as inactive.");
                                        $sessionsInactive++;
                                    } else {
                                        $this->logger->error("Cannot delete inactive session '$id'.");
                                    }
                                }
                            }
                            if (isset($agentSkipped[$agent])) {
                                $agentSkipped[$agent]++;
                            } else {
                                $agentSkipped[$agent] = 1;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    $this->log($e->getMessage());
                }
            }
        }

        /** compose result */
        arsort($agentSkipped);
        $sessionsSkipped = count($agentSkipped);
        foreach ($agentSkipped as $agentName => $count) {
            $this->log("$count: $agentName");
        }
        $this->log("Total '$total' sessions are found in DB.");
        $this->log("'$sessionsSkipped' sessions are not defined as bot's.");
        $this->log("'$sessionsBots' sessions are deleted as bot's.");
        $this->log("'$sessionsActive' sessions belong to active customers.");
        $this->log("'$sessionsInactive' inactive customers sessions are deleted.");
        $this->log("Command '$name' is executed.");
    }

    /**
     * Get sessions from DB where 'session_id' greater then given $id.
     *
     * @param string $id
     * @param int $limit
     * @return array
     */
    private function getSessionsBatch($id, $limit)
    {
        $tbl = $this->resource->getTableName('session');
        $select = $this->conn->select()->from($tbl);
        if (!is_null($id)) {
            $where = "session_id>" . $this->conn->quote($id);
            $select->where($where);
        }
        $select->limit($limit);
        $result = $this->conn->fetchAll($select);
        return $result;
    }

    /**
     * Aggregator to log messages to console & log file.
     *
     * @param string $msg
     */
    private function log($msg)
    {
        $this->logger->debug($msg);
        $this->output->writeln($msg);
    }
    /**
     * Get total count of the all sessions from DB.
     *
     * @return int
     */
    private function getSessionsCount()
    {
        $tbl = $this->resource->getTableName('session');
        $select = $this->conn->select();
        $select->from($tbl, 'COUNT(session_id)');
        $result = $this->conn->fetchOne($select);
        return $result;
    }
}