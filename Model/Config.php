<?php
/*! (c) jTorm and other contributors | www.jtorm.com/license */

declare(strict_types=1);

namespace Webmakkers\Jtorm\Model;

use Webmakkers\Jtorm\Api\ConfigInterface;
use Webmakkers\Jtorm\Api\DataProviderInterface;
use Magento\Framework\ObjectManagerInterface;

use function in_array;

class Config implements ConfigInterface
{
    public function __construct(
        private readonly ObjectManagerInterface $objectmanager,
        private array $layoutConfig = [],
        private ?array $scope = null
    ) {}

    public function hasConfig(string $id): bool
    {
        return isset($this->layoutConfig[$id]);
    }

    public function getDataProvider(string $id): ?DataProviderInterface
    {
        if (!$this->hasConfig($id)) {
            return null;
        }

        return $this->objectmanager->create($this->layoutConfig[$id]);
    }

    public function hasScope($scope): bool
    {
        if ($this->scope === null) {
            return true;
        }

        return in_array($scope, $this->scope);
    }
}
