<?php

declare(strict_types=1);

namespace D3\ShopLogger;

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
    private function getFactsConfigFile(): FactsConfigFile
    {
        if (!is_a($this->factsConfigFile, FactsConfigFile::class)) {
            $this->factsConfigFile = new FactsConfigFile();
        }

        return $this->factsConfigFile;
    }

    public function getRemainingLogFiles(): ?int
    {
        return $this->getFactsConfigFile()->getVar('logRemainingFiles') ?
            (int) $this->getFactsConfigFile()->getVar('logRemainingFiles') :
            null;
    }

    public function getNotificationMailAddress(): ?string
    {
        return $this->getFactsConfigFile()->getVar('logNotificationAddress');
    }
}