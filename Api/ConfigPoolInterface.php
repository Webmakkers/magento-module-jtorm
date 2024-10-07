<?php
/*! (c) jTorm and other contributors | www.jtorm.com/license */

declare(strict_types=1);

namespace Webmakkers\Jtorm\Api;

use Magento\Framework\DataObject;

interface ConfigPoolInterface
{
    public function addConfig(ConfigInterface $config): void;

    /**
     * @return ConfigInterface[]
     */
    public function getConfig(): array;

    public function process($scope, string $id, DataObject $transport): DataObject;
}
