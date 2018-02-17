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
    const BATCH_LIMIT = 1000;
    const DESC = 'Clean crawlers/bots sessions existing in DB.';
    const NAME = 'fl32:botsess:clean';

    /** @var \Magento\Framework\DB\Adapter\AdapterInterface */
    private $conn;
    /** @var \Flancer32\BotSess\Helper\Mod */
    private $hlp;
    /** @var \Magento\Framework\App\ResourceConnection */
    private $resource;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Flancer32\BotSess\Helper\Mod $hlp
    ) {
        parent::__construct(self::NAME);
        /* Symfony related config is performed from parent constructor */
        $this->setDescription(self::DESC);
        $this->resource = $resource;
        $this->conn = $resource->getConnection();
        $this->hlp = $hlp;
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
        /* perform operation */
        $name = self::NAME;
        $output->writeln("Command '$name' is started.");

        /* start session to decode session data */
        session_start();

        /* get sessions count */
        $total = $this->getSessionsCount();
        $output->writeln("Total '$total' sessions is found in DB.");
        $current = 0;
        $limit = self::BATCH_LIMIT;
        $id = null;
        while ($current < $total) {
            /* get all sessions then process it in loop */
            $all = $this->getSessionsBatch($id, $limit);
            foreach ($all as $one) {
                $current++;
                $id = $one['session_id'];
                $data = $one['session_data'];
                $decoded = base64_decode($data);
                try {
                    session_decode($decoded);
                    if (isset($_SESSION['_session_validator_data']['http_user_agent'])) {
                        $agent = $_SESSION['_session_validator_data']['http_user_agent'];
                        $isBot = $this->hlp->isBot($agent);
                        if ($isBot) {
                            /* remove bot session */
                            $output->writeln("Session '$id' belongs to bot ($agent).");
                            $deleted = $this->deleteSession($id);
                            if ($deleted == 1) {
                                $output->writeln("Session '$id' is deleted.");
                            } else {
                                $output->writeln("Cannot delete session '$id'.");
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    $output->writeln($e->getMessage());
                }
            }
        }
        $output->writeln("Command '$name' is executed.");
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