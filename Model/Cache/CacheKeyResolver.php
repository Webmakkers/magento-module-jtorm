<?php
/*! (c) jTorm and other contributors | www.jtorm.com/license */

declare(strict_types=1);

namespace Webmakkers\Jtorm\Model\Cache;

use http\Exception\InvalidArgumentException;
use Magento\Framework\DataObject;
use Webmakkers\Jtorm\Api\CacheKeyResolverInterface;
use Webmakkers\Jtorm\Model\Cache\Type\JtormUiEngineCache;

class CacheKeyResolver
{
    /** @var array CacheKeyResolverInterface[] */
    private array $cacheKeyResolver;

    public function __construct(
        array $cacheKeyResolver = []
    ) {
        $this->cacheKeyResolver = $cacheKeyResolver;
    }

    public function execute(
        $storeId,
        string $nameInLayout,
        ?DataObject $block,
        ?string $cacheKey,
        ?string $cacheKeyScope
    ) {
        $cacheKey2 = '';

        if ($block) {
            if ($block->getCacheKey()) {
                $cacheKey2 .= $block->getCacheKey();
            }

            if ($cacheKeyScope) {
                if (!isset($this->cacheKeyResolver[$cacheKeyScope])) {
                    throw new InvalidArgumentException(\sprintf('Cache key resolver %s not found.', $cacheKeyScope));
                }

                if ($cacheKey2) {
                    $cacheKey2 .= '_';
                }

                $cacheKey2 .= $this->cacheKeyResolver[$cacheKeyScope]->execute($block);
            }
        }

        return
            JtormUiEngineCache::TYPE_IDENTIFIER
            . '_' . $storeId
            . '_' . $nameInLayout
            . ($cacheKey ? '_' . $cacheKey : '')
            . ($cacheKey2 ? '_' . $cacheKey2 : '')
            ;
    }
}
