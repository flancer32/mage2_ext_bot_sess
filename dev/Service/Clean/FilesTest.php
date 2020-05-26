<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Test\Flancer32\BotSess\Service\Clean;

include_once(__DIR__ . '/../../phpunit_bootstrap.php');

class FilesTest
    extends \PHPUnit\Framework\TestCase
{
    public function test_exec()
    {
        /** Get object to test */
        $obm = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Flancer32\BotSess\Service\Clean\Files $service */
        $service = $obm->get(\Flancer32\BotSess\Service\Clean\Files::class);

        /** Run object methods */
        $request = new \Flancer32\BotSess\Service\Clean\Files\Request();
        $response = $service->exec($request);

        $this->assertNotNull($response);
    }
}