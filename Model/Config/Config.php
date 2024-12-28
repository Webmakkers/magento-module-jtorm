<?php
/*! (c) jTorm and other contributors | www.jtorm.com/license */

declare(strict_types=1);

namespace Webmakkers\Jtorm\Model\Config;

use Magento\Framework\ObjectManagerInterface;
use Webmakkers\Jtorm\Api\ConfigInterface;
use Webmakkers\Jtorm\Api\DataProviderInterface;

class Config implements ConfigInterface
{
    public function __construct(
        private readonly ObjectManagerInterface $objectmanager,
        private array $layoutConfig = [],
        private ?array $scope = null
    ) {}

    public function hasConfig(string $nameInLayout): bool
    {
        return isset($this->layoutConfig[$nameInLayout]);
    }

    public function getDataProvider(string $nameInLayout): ?DataProviderInterface
    {
        if (!$this->hasConfig($nameInLayout)) {
            return null;
        }

        return $this->objectmanager->create($this->layoutConfig[$nameInLayout]);
    }

    public function hasScope(int $storeId): bool
    {
        if ($this->scope === null) {
            return true;
        }

        return \in_array($storeId, $this->scope);
    }
}
