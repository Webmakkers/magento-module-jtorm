<?php
/*! (c) jTorm and other contributors | www.jtorm.com/license */

declare(strict_types=1);

namespace Webmakkers\Jtorm\Api;

interface SendToUIEngineActionInterface
{
    public function execute(DataProviderInterface $model): string;
}
