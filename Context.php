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

namespace D3\OxLogiQ;

use OxidEsales\EshopCommunity\Internal\Transition\Utility\Context as OxidContext;
use OxidEsales\Facts\Config\ConfigFile as FactsConfigFile;

class Context extends OxidContext
{
    /**
     * @var FactsConfigFile
     */
    private $factsConfigFile;

    /**
     * @return FactsConfigFile
     */
    protected function getFactsConfigFile(): FactsConfigFile
    {
        if (!is_a($this->factsConfigFile, FactsConfigFile::class)) {
            $this->factsConfigFile = new FactsConfigFile();
        }

        return $this->factsConfigFile;
    }

    public function getRemainingLogFiles(): ?int
    {
        return is_int($this->getFactsConfigFile()->getVar('logRemainingFiles')) ?
            (int) $this->getFactsConfigFile()->getVar('logRemainingFiles') :
            null;
    }

    public function getNotificationMailAddress(): ?string
    {
        return $this->getFactsConfigFile()->getVar('logNotificationAddress');
    }
}