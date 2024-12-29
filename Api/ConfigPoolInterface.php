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

    /**
     * (int) Store ID or (string) path from static generation e.g. Magento/luma/en_US/
     *
     * @param int|string $storeId
     * @param string $nameInLayout
     * @param DataObject $transport
     * @return DataObject
     */
    public function process($storeId, string $nameInLayout, DataObject $transport): DataObject;
}
