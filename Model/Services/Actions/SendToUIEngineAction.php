<?php
/*! (c) jTorm and other contributors | www.jtorm.com/license */

declare(strict_types=1);

namespace Webmakkers\Jtorm\Model\Services\Actions;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Webmakkers\Jtorm\Api\DataProviderInterface;
use Webmakkers\Jtorm\Api\SendToUIEngineActionInterface;

readonly class SendToUIEngineAction implements SendToUIEngineActionInterface
{
    private const string XML_PATH_UI_ENGINE_URL = 'jtorm/general/url';

    public function __construct(
        private Curl $curl,
        private Json $json,
        private ScopeConfigInterface $scopeConfig
    ) {}

    public function execute(DataProviderInterface $dataProvider): string
    {
        $this->curl->setTimeout(5);
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

        return $this->curl->getBody();
    }

    private function getUIEngineUrl()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_UI_ENGINE_URL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
