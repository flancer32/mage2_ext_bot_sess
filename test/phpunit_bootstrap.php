<?php
/* BP is defined in Magento's ./app/autoload.php */
if (!defined('BP')) {
    include_once(__DIR__ . '/../../../../app/bootstrap.php');
    /**
     * Create test application that initializes DB connection and ends w/o exiting
     *  ($response->terminateOnSend = false).
     */
    $params = $_SERVER;
    /** @var  $bootstrap \Magento\Framework\App\Bootstrap */
    $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);
    /** @var  $app \Flancer32\BotSess\Test\DevApp */
    $app = $bootstrap->createApplication(\Flancer32\BotSess\Test\DevApp::class);
    $bootstrap->run($app);
}