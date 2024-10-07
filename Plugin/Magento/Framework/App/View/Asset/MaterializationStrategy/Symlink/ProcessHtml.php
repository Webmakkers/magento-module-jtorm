<?php
/*! (c) jTorm and other contributors | www.jtorm.com/license */

declare(strict_types=1);

namespace Webmakkers\Jtorm\Plugin\Magento\Framework\App\View\Asset\MaterializationStrategy\Symlink;

use Magento\Framework\App\View\Asset\MaterializationStrategy\Symlink;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Webmakkers\Jtorm\Api\ConfigPoolInterface;

class ProcessHtml
{
    public function __construct(
        private readonly ConfigPoolInterface $configPool,
        private readonly DataObjectFactory $dataObjectFactory,
        private readonly WriteFactory $writeFactory
    ) {}

    public function beforePublishFile(
        Symlink $instance,
        WriteInterface $sourceDir,
        WriteInterface $targetDir,
                       $sourcePath,
                       $destinationPath
    ) {
        if (!$this->process($sourceDir, $targetDir, $sourcePath, $destinationPath)) {
            return [$sourceDir, $targetDir, $sourcePath, $destinationPath];
        }

        exit(0);// Bugfix showing double output the first visit
    }

    private function process(
        WriteInterface $sourceDir,
        WriteInterface $targetDir,
                       $sourcePath,
                       $destinationPath
    ): bool
    {
        if (\preg_match('/\.html$/', $sourcePath)) {
            $match = [];
            if (\preg_match('#\/([A-Za-z0-9]+\/[A-Za-z0-9]+/[A-Za-z_]+\/)#', $destinationPath, $match)) {
                $html = $sourceDir->readFile($sourceDir->getAbsolutePath() . $sourcePath);
                if (!empty($html)) {
                    $transport = $this->dataObjectFactory->create();
                    $transport->setHtml($html);

                    $id = \preg_replace('#frontend\/([A-Za-z0-9]+\/[A-Za-z0-9]+/[A-Za-z_]+\/)#', '', $destinationPath);
                    $this->configPool->process($match[1], $id, $transport);

                    $write = $this->writeFactory->create($targetDir->getAbsolutePath());
                    $write->writeFile($destinationPath, $transport->getHtml());

                    // Bugfix opening old version on first visit
                    $handle = \fopen($targetDir->getAbsolutePath() . $destinationPath, 'r');
                    if ($handle) {
                        while (($buffer = \fgets($handle, 4096)) !== false) {
                            // phpcs:ignore Magento2.Security.LanguageConstruct.DirectOutput
                            echo $buffer;
                        }
                        if (!\feof($handle)) {
                            throw new \UnexpectedValueException("Unexpected end of file");
                        }
                        \fclose($handle);
                    }

                    return true;
                }
            }
        }

        return false;
    }
}
