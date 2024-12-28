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
        private \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        private \Magento\Framework\Locale\Resolver $localeResolver
    ) {}

    public function execute(\Webmakkers\Jtorm\Api\DataProviderInterface $dataProvider): string
    {
        $this->curl->addHeader('Accept', 'text/html');
        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->addHeader(\CURLOPT_ENCODING, 'gzip, deflate');
        $this->curl->setOption(\CURLOPT_RETURNTRANSFER, true);

        $data = $this->json->serialize([
            'return_body' => !$dataProvider->isFullPage(),
            'tss' => $dataProvider->getTss(),
            'data' => $dataProvider->toArray(),
            'html' => $dataProvider->getTransport()->getHtml()
        ]);

        $url = $this->getUIEngineUrl() . '/api/compile/' . \preg_replace('/_/', '-', $this->localeResolver->getLocale());
        $this->curl->post($url, $data);

        $this->handleResponse($url, $dataProvider);

        return $this->curl->getBody();
    }

    private function getUIEngineUrl()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_UI_ENGINE_URL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    private function handleResponse(string $url, \Webmakkers\Jtorm\Api\DataProviderInterface $dataProvider)
    {
        if ($this->isDebug()) {
            $this->logger->debug(
                __METHOD__,
                [
                    'url'         => $url,
                    'return_body' => !$dataProvider->isFullPage(),
                    'data'        => $dataProvider->toArray(),
                    'html'        => $dataProvider->getTransport()->getHtml(),
                    'tss'         => $dataProvider->getTss(),
                    'status'      => $this->curl->getStatus(),
                    'headers'     => $this->curl->getHeaders(),
                    'body'        => $this->curl->getBody()
                ]
            );
        }

        if ($this->curl->getStatus() !== 200) {
            $this->logger->error(
                __METHOD__,
                [
                    'url'         => $url,
                    'return_body' => !$dataProvider->isFullPage(),
                    'data'        => $dataProvider->toArray(),
                    'html'        => $dataProvider->getTransport()->getHtml(),
                    'tss'         => $dataProvider->getTss(),
                    'status'      => $this->curl->getStatus(),
                    'headers'     => $this->curl->getHeaders(),
                    'body'        => $this->curl->getBody()
                ]
            );

            $this->curl->doError(
                'jTorm UI Engine request ' . $url . ' resulted in an invalid status code ' . $this->curl->getStatus() . '.'
            );
        }
    }

    private function isDebug(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::XML_PATH_IS_DEBUG, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
