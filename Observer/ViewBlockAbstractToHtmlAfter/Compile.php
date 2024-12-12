<?php
/*! (c) jTorm and other contributors | www.jtorm.com/license */

declare(strict_types=1);

namespace Webmakkers\Jtorm\Observer\ViewBlockAbstractToHtmlAfter;

use Magento\Framework\DataObject;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Store\Model\StoreManagerInterface;
use Webmakkers\Jtorm\Api\ConfigPoolInterface;

readonly class Compile implements ObserverInterface
{
    public function __construct(
        private ConfigPoolInterface $configPool,
        private StoreManagerInterface $storeManager
    ) {}

    public function execute(Observer $observer)
    {
        /** @var DataObject $transport */
        $transport = $observer->getEvent()->getData('transport');

        $html = $transport->getData('html');
        if (empty($html)) {
            return;
        }

        /** @var AbstractBlock $block */
        $block = $observer->getEvent()->getData('block');
        if (empty($block->getNameInLayout())) {
            return;
        }

        $this->configPool->process($this->storeManager->getStore()->getId(), $block->getNameInLayout(), $transport, $block);
    }
}
