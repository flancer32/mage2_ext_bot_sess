<?php

/**
 * Define test app class before bootstrap (PHP is interpreted lang).
 */
class Flancer32TestApp
    implements \Magento\Framework\AppInterface
{
    /** @var \Magento\Framework\App\Console\Response */
    private $response;
    /** @var \Magento\Store\Model\StoreManagerInterface */
    private $storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Console\Response $response
    ) {
        $this->storeManager = $storeManager;
        $this->response = $response;
    }

    /**
     * Ability to handle exceptions that may have occurred during bootstrap and launch
     *
     * Return values:
     * - true: exception has been handled, no additional action is needed
     * - false: exception has not been handled - pass the control to Bootstrap
     *
     * @param \Magento\Framework\App\Bootstrap $bootstrap
     * @param \Exception $exception
     *
     * @return bool
     */
    public function catchException(
        \Magento\Framework\App\Bootstrap $bootstrap,
        \Exception $exception
    ) {
        return false;
    }

    /**
     * Launch application. Prevent application termination on sent response, initialize DB connection.
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function launch()
    {
        $this->response->terminateOnSend(false);
        $this->storeManager->getStores(false, true);
        return $this->response;
    }
}

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
    /** @var  $app \Flancer32TestApp */
    $app = $bootstrap->createApplication(\Flancer32TestApp::class);
    $bootstrap->run($app);
}
