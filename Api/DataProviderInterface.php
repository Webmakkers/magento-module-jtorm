<?php
/*! (c) jTorm and other contributors | www.jtorm.com/license */

declare(strict_types=1);

namespace Webmakkers\Jtorm\Api;

use Magento\Framework\DataObject;

interface DataProviderInterface
{
    public function getBlock(): ?DataObject;

    public function setBlock(DataObject $block): DataProviderInterface;

    public function getTransport(): ?DataObject;

    public function setTransport(DataObject $transport): DataProviderInterface;

    public function getTss(): string;

    /**
     * Required when manipulating the full page through
     * \Webmakkers\Jtorm\Plugin\Magento\Framework\Controller\ResultInterface\ProcessPhtml
     * Needed so that the UI Engine returns the full page instead of only the compiled body
     */
    public function isFullPage(): bool;
}
