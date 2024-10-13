<?php
/*! (c) jTorm and other contributors | www.jtorm.com/license */

declare(strict_types=1);

namespace Webmakkers\Jtorm\Model;

use Exception;
use Magento\Framework\DataObject;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;
use Webmakkers\Jtorm\Api\ConfigInterface;
use Webmakkers\Jtorm\Api\ConfigPoolInterface;
use Webmakkers\Jtorm\Api\SendToUIEngineActionInterface;
use Webmakkers\Jtorm\Model\Cache\Type\JtormUiEngineCache;

class ConfigPool implements ConfigPoolInterface
{
    private const string XML_PATH_TTL = 'system/full_page_cache/ttl';

    /** @var ConfigInterface[] */
    private array $config;

    public function __construct(
        private readonly JtormUiEngineCache            $cache,
        private readonly LoggerInterface               $logger,
        private readonly SendToUIEngineActionInterface $sendUIEngineAction,
        private readonly ScopeConfigInterface          $scopeConfig,
        private $key = '',
        array                                          $config = []
    ) {
        $this->config = $config;
    }

    public function addConfig(ConfigInterface $config): void
    {
        $this->config[] = $config;
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    public function process($scope, string $id, DataObject $transport): DataObject
    {
        $html = $this->loadCache($scope, $id);
        if ($html) {
            $transport->setHtml($html);
            return $transport;
        }

        $setCache = false;

        foreach ($this->config as $config) {
            if (!$config->hasScope($scope)) {
                continue;
            }

            if (!$config->hasConfig($id)) {
                continue;
            }

            try {
                $dataProvider = $config
                    ->getDataProvider($id)
                    ->setTransport($transport)
                ;

                $transport->setHtml($this->sendUIEngineAction->execute($dataProvider));

                $setCache = true;
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage() ?? 'Error connecting with jTorm UI Engine');
            }
        }

        if ($setCache) {
            $this->cache->save(
                $transport->getHtml(),
                $this->parseKey($scope, $id),
                [JtormUiEngineCache::CACHE_TAG],
                $dataProvider->getTtl() ?? $this->scopeConfig->getValue(self::XML_PATH_TTL)
            );
        }

        return $transport;
    }

    private function loadCache($scope, string $id): ?string
    {
        foreach ($this->config as $config) {
            if ($config->hasScope($scope) && $config->hasConfig($id)) {
                $cache = $this->cache->load($this->parseKey($scope, $id));
                if ($cache) {
                    return $cache;
                }
            }
        }

        return null;
    }

    private function parseKey($scope, string $id): string
    {
        return
            JtormUiEngineCache::TYPE_IDENTIFIER
            . '_' . \preg_replace('#[_\/]#', '_', \strtolower($scope))
            . '_' . \preg_replace('#[_\/]#', '_', \strtolower($id))
            . ($this->key ? '_' . \preg_replace('#[_\/]#', '_', \strtolower($this->key)) : '')
        ;
    }
}
