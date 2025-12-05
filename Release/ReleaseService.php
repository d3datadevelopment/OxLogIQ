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

namespace D3\OxLogIQ\Release;

use DateTimeImmutable;
use OxidEsales\Eshop\Core\Exception\FileException;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;

class ReleaseService implements ReleaseServiceInterface
{
    public function getRelease(): string
    {
        try {
            $path = Registry::getConfig()->getConfigParam('sShopDir') . '../composer.lock';
            $realPath = realpath($path);

            if (!$realPath) {
                throw new FileException(sprintf('composer.lock file not found in path %s', $path));
            }

            return (new DateTimeImmutable())->setTimestamp(filemtime($realPath))->format('Y-m-d_H:i:s');
        } catch (StandardException) {
            return '';
        }
    }
}
