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

namespace D3\OxLogIQ\Providers;

use D3\LoggerFactory\LoggerFactory;
use D3\OxLogIQ\Interfaces\ProviderInterface;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;

class IntrospectionProcessorProvider implements ProviderInterface
{
    public function register(LoggerFactory $factory): void
    {
        $factory->addOtherProcessor(
            new IntrospectionProcessor(Logger::ERROR, [
                'Internal\\Framework\\Logger\\',
            ])
        );
    }

    /**
     * @codeCoverageIgnore
     */
    public static function getPriority(): int
    {
        return 100;
    }
}
