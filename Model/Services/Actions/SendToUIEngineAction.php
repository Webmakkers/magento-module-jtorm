<?php
/*! (c) jTorm and other contributors | www.jtorm.com/license */

declare(strict_types=1);

namespace Webmakkers\Jtorm\Model\Services\Actions;

readonly class SendToUIEngineAction implements \Webmakkers\Jtorm\Api\SendToUIEngineActionInterface
{
    private const string XML_PATH_IS_DEBUG = 'jtorm/general/is_debug';
    private const string XML_PATH_UI_ENGINE_URL = 'jtorm/general/url';

    public function __construct(
        private \Magento\Framework\HTTP\Client\Curl $curl,
        private \Magento\Framework\Serialize\Serializer\Json $json,
        private \Psr\Log\LoggerInterface $logger,
        private \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {}

    public function execute(\Webmakkers\Jtorm\Api\DataProviderInterface $dataProvider): string
    {
        $this->curl->addHeader('Accept', 'text/html');
        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->addHeader(\CURLOPT_ENCODING, 'gzip, deflate');
        $this->curl->setOption(\CURLOPT_RETURNTRANSFER, true);

        $data = $this->json->serialize([
            'return_body' => !$dataProvider->isFullPage(),
            'data' => $dataProvider->toArray(),
            'html' => $dataProvider->getTransport()->getHtml(),
            'tss' => $dataProvider->getTss()
        ]);

        $this->curl->post($this->getUIEngineUrl() . '/api/compile', $data);

        if ($this->isDebug()) {
            $this->logger->debug(
                __METHOD__,
                [
                    'return_body' => !$dataProvider->isFullPage(),
                    'data'        => $dataProvider->toArray(),
                    'tss'         => $dataProvider->getTss(),
                    'res'         => $this->curl->getBody()
                ]
            );
        }

        return $this->curl->getBody();
    }

    private function isDebug(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::XML_PATH_IS_DEBUG, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    private function getUIEngineUrl()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_UI_ENGINE_URL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
