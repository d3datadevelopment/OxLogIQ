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

namespace D3\OxLogIQ\Providers\Processors;

use D3\LoggerFactory\LoggerFactory;
use D3\OxLogIQ\Interfaces\ProviderInterface;
use D3\OxLogIQ\Processors\SessionIdProcessor;
use OxidEsales\Eshop\Core\Session;

class SessionIdProcessorProvider implements ProviderInterface
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(protected Session $session)
    {
    }

    /**
     * @codeCoverageIgnore
     */
    public function isActive(): bool
    {
        return true;
    }

    public function provide(LoggerFactory $factory): void
    {
        $factory->addOtherProcessor(
            new SessionIdProcessor($this->session)
        );
    }

    /**
     * @codeCoverageIgnore
     */
    public static function getPriority(): int
    {
        return 300;
    }
}
