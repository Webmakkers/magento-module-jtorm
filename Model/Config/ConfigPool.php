<?php
/*! (c) jTorm and other contributors | www.jtorm.com/license */

declare(strict_types=1);

namespace Webmakkers\Jtorm\Model\Config;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Psr\Log\LoggerInterface;
use Webmakkers\Jtorm\Api\ConfigInterface;
use Webmakkers\Jtorm\Api\ConfigPoolInterface;
use Webmakkers\Jtorm\Api\SendToUIEngineActionInterface;
use Webmakkers\Jtorm\Model\Cache\CacheKeyResolver;
use Webmakkers\Jtorm\Model\Cache\Type\JtormUiEngineCache;

class ConfigPool implements ConfigPoolInterface
{
    private const string XML_PATH_TTL = 'system/full_page_cache/ttl';

    /** @var ConfigInterface[] */
    private array $config;

    public function __construct(
        private readonly CacheKeyResolver              $cacheKeyResolver,
        private readonly JtormUiEngineCache            $cache,
        private readonly LoggerInterface               $logger,
        private readonly SendToUIEngineActionInterface $sendUIEngineAction,
        private readonly ScopeConfigInterface          $scopeConfig,
        private readonly ?string                       $cacheKey = null,
        /**
         * cache key scope adds a unique value to the cache key
         * this is needed for example in a product list/grid where the block layout name is the same
         * to achieve a cache per product by adding a sku to the cache key
         */
        private readonly ?string                       $cacheKeyScope = null,
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

    public function process(
        $storeId,
        string $nameInLayout,
        DataObject $transport,
        ?DataObject $block = null
    ): DataObject
    {
        $loadCache = true;
        $setCache = false;

        foreach ($this->config as $config) {
            if (!$config->hasScope($storeId)) {
                continue;
            }

            if (!$config->hasConfig($nameInLayout)) {
                continue;
            }

            if ($loadCache) {
                $html = $this->loadCache($storeId, $nameInLayout, $block);
                if ($html) {
                    $transport->setHtml($html);
                    return $transport;
                }

                $loadCache = false;
            }

            try {
                $dataProvider = $config
                    ->getDataProvider($nameInLayout)
                    ->setTransport($transport)
                ;

                if ($block) {
                    $dataProvider->setBlock($block);
                }

                $html = $this->sendUIEngineAction->execute($dataProvider);
                $transport->setHtml($html);

                $setCache = true;
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage() ?? 'Error connecting with jTorm UI Engine.');
            }
        }

        if ($setCache) {
            $this->cache->save(
                $transport->getHtml(),
                $this->parseKey($storeId, $nameInLayout, $block),
                [JtormUiEngineCache::CACHE_TAG],
                $dataProvider->getTtl() ?? $this->scopeConfig->getValue(self::XML_PATH_TTL)
            );
        }

        return $transport;
    }

    private function loadCache($storeId, string $nameInLayout, ?DataObject $block = null): ?string
    {
        foreach ($this->config as $config) {
            if ($config->hasScope($storeId) && $config->hasConfig($nameInLayout)) {
                $cache = $this->cache->load($this->parseKey($storeId, $nameInLayout, $block));
                if ($cache) {
                    return $cache;
                }
            }
        }

        return null;
    }

    private function parseKey($storeId, string $nameInLayout, ?DataObject $block = null): string
    {
        return $this->cacheKeyResolver->execute(
            $storeId,
            $nameInLayout,
            $block,
            $this->cacheKey,
            $this->cacheKeyScope
        );
    }
}
