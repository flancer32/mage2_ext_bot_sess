<?php

namespace Flancer32\BotSess\Controller\Clean;

/**
 * Clean up old session files without visitors activities.
 */
class Files
    extends \Magento\Framework\App\Action\Action
{
    /** @var \Flancer32\BotSess\Service\Clean\Files */
    private $servClean;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Flancer32\BotSess\Service\Clean\Files $servClean
    ) {
        parent::__construct($context);
        $this->servClean = $servClean;
    }

    public function execute()
    {
        $req = new \Flancer32\BotSess\Service\Clean\Files\Request();
        $resp = $this->servClean->exec($req);

        /** compose result */
        $type = \Magento\Framework\Controller\ResultFactory::TYPE_JSON;
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create($type);
        $resultPage->setData($resp);
        return $resultPage;
    }
}