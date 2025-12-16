<?php

/**
 * Copyright (c) D3 Data Development (Inh. Thomas Dartsch)
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * https://www.d3data.de
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author    D3 Data Development - Daniel Seifert <info@shopmodule.com>
 * @link      https://www.oxidmodule.com
 */

declare(strict_types=1);

namespace D3\OxLogIQ\Core;

use D3\OxLogIQ\Interfaces\FallbackHandlerInterface;
use D3\OxLogIQ\Interfaces\FallbackLoggerInterface;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class FallbackLogger implements FallbackLoggerInterface
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(private FallbackHandlerInterface $handler)
    {
    }

    public function get(): LoggerInterface
    {
        return (new Logger('OxLogIQ Fallback'))
            ->pushHandler($this->handler);
    }
}
