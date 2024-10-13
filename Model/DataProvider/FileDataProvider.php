<?php
/*! (c) jTorm and other contributors | www.jtorm.com/license */

declare(strict_types=1);

namespace Webmakkers\Jtorm\Model\DataProvider;

class FileDataProvider extends \Webmakkers\Jtorm\Model\AbstractDataProvider
{
    public function __construct(
        private readonly \Magento\Framework\Filesystem\File\ReadFactory $readFactory,
        private readonly \Magento\Framework\Module\Dir\Reader $moduleReader,
        private string $module = 'Webmakkers_JtormTheme',
        private string $scope = 'frontend',
        private string $file = 'example.tss',
        array $data = []
    ) {
        parent::__construct($data);

        if (isset($data['ttl'])) {
            $this->ttl = (int) $data['ttl'];
        }

        if (isset($data['is_full_page'])) {
            $this->isFullPage = (bool) $data['is_full_page'];
        }
    }

    public function getTss(): string
    {
        $read = $this->readFactory->create($this->getTssFile(), \Magento\Framework\Filesystem\DriverPool::FILE);
        return $read->readAll();
    }

    private function getTssFile()
    {
        $viewDir = $this->moduleReader->getModuleDir(
            \Magento\Framework\Module\Dir::MODULE_VIEW_DIR,
            $this->module
        );
        return $viewDir . '/' . $this->scope . '/web/tss/' . $this->file;
    }
}
