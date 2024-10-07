<?php
/*! (c) jTorm and other contributors | www.jtorm.com/license */

declare(strict_types=1);

namespace Webmakkers\Jtorm\Api;

interface ConfigInterface
{
    public function hasConfig(string $id): bool;

    public function getDataProvider(string $id): ?DataProviderInterface;

    public function hasScope($scope): bool;
}
