<?php
/*! (c) jTorm and other contributors | www.jtorm.com/license */

declare(strict_types=1);

namespace Webmakkers\Jtorm\Model\Cache;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DataObject;
use Webmakkers\Jtorm\Api\CacheKeyResolverInterface;

class ProductKeyResolver implements CacheKeyResolverInterface
{
    public const string KEY = 'product';

    public function execute(DataObject $block): ?string
    {
        if ($block->getProductId()) {
            return $block->getProductId();
        }

        if ($block->getProduct() instanceof ProductInterface) {
            return $block->getProduct()->getSku();
        }

        return null;
    }
}
