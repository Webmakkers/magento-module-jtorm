<?php
/*! (c) jTorm and other contributors | www.jtorm.com/license */

declare(strict_types=1);

namespace Webmakkers\Jtorm\Plugin\Magento\Framework\Controller\ResultInterface;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Store\Model\StoreManagerInterface;
use Webmakkers\Jtorm\Api\ConfigPoolInterface;

readonly class ProcessPhtmlAfterFpc
{
    public function __construct(
        private ConfigPoolInterface $configPool,
        private DataObjectFactory $dataObjectFactory,
        private RequestInterface $request,
        private StoreManagerInterface $storeManager
    ) {}

    public function afterRenderResult(
        ResultInterface $subject,
        ResultInterface $result,
        ResponseInterface $response
    ) {
        $path = $this->request->getModuleName() .'/'. $this->request->getControllerName() .'/'. $this->request->getActionName();
        if (!empty($this->request->getParams())) {
            foreach ($this->request->getParams() as $key => $value) {
                $path .= '/' . $key . '/' . $value;
            }
        }

        $transport = $this->dataObjectFactory->create();
        $transport->setHtml($response->getBody());
        $this->configPool->process((int) $this->storeManager->getStore()->getId(), $path, $transport);

        $response->setBody($transport->getHtml());

        return $result;
    }
}
