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

namespace D3\OxLogIQ\Handlers;

use D3\OxLogIQ\Interfaces\FallbackHandlerInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use OxidEsales\Facts\Config\ConfigFile;
use Symfony\Component\Filesystem\Path;

class FallbackHandler extends StreamHandler implements FallbackHandlerInterface
{
    public function __construct()
    {
        parent::__construct(
            Path::join((new ConfigFile())->getVar('sShopDir'), 'log', 'oxideshop.log'),
            Logger::ERROR
        );

        $this->setFormatter(new LineFormatter());
    }
}
