<?php
/*! (c) jTorm and other contributors | www.jtorm.com/license */

declare(strict_types=1);

namespace Webmakkers\Jtorm\Model;

use Magento\Framework\DataObject;
use Webmakkers\Jtorm\Api\DataProviderInterface;

abstract class AbstractDataProvider extends DataObject implements DataProviderInterface
{
    protected const string KEY_BLOCK = 'block';
    protected const string KEY_TRANSPORT = 'transport';

    protected bool $isFullPage = false;
    protected ?int $ttl = null;

    public function getBlock(): ?DataObject
    {
        return $this->getData(self::KEY_BLOCK);
    }

    public function setBlock(DataObject $block): DataProviderInterface
    {
        return $this->setData(self::KEY_BLOCK, $block);
    }

    public function getTransport(): ?DataObject
    {
        return $this->getData(self::KEY_TRANSPORT);
    }

    public function setTransport(DataObject $transport): DataProviderInterface
    {
        return $this->setData(self::KEY_TRANSPORT, $transport);
    }

    abstract public function getTss(): string;

    public function getTtl(): ?int
    {
        return $this->ttl;
    }

    public function isFullPage(): bool
    {
        return $this->isFullPage;
    }

    public function toArray(array $keys = [])
    {
        $result = parent::toArray($keys);
        unset($result[self::KEY_TRANSPORT]);
        return $result;
    }
}
