<?php
/*! (c) jTorm and other contributors | www.jtorm.com/license */

declare(strict_types=1);

namespace Webmakkers\Jtorm\Api;

interface ConfigInterface
{
    public function hasConfig(string $nameInLayout): bool;

    public function getDataProvider(string $nameInLayout): ?DataProviderInterface;

    public function hasScope(int $storeId): bool;
}
