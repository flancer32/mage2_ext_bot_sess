<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 * Since: 2018
 */

namespace Test\Flancer32\BotSess\Helper;

include_once(__DIR__ . '/../phpunit_bootstrap.php');

class ConfigTest
    extends \PHPUnit\Framework\TestCase
{
    /** @var \Flancer32\BotSess\Helper\Config */
    private $obj;

    protected function setUp()
    {
        /** Get object to test */
        $obm = \Magento\Framework\App\ObjectManager::getInstance();
        $this->obj = $obm->get(\Flancer32\BotSess\Helper\Config::class);
    }


    public function test_getFilter()
    {
        $res = $this->obj->getFilter();
        $this->assertNotNull($res);
    }
}