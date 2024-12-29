<?php
/*! (c) jTorm and other contributors | www.jtorm.com/license */

declare(strict_types=1);

namespace Webmakkers\Jtorm\Api;

interface ConfigInterface
{
    public function hasConfig(string $nameInLayout): bool;

    public function getDataProvider(string $nameInLayout): ?DataProviderInterface;

    /**
     * Store ID or path from static generation e.g. Magento/luma/en_US/
     *
     * @param int|string $storeId
     * @return bool
     */
    public function hasScope($storeId): bool;
}
