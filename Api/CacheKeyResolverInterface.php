<?php
/*! (c) jTorm and other contributors | www.jtorm.com/license */

declare(strict_types=1);

namespace Webmakkers\Jtorm\Api;

use Magento\Framework\DataObject;

interface CacheKeyResolverInterface
{
    public function execute(DataObject $block);
}
