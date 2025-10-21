<?php

namespace D3\Shoplogger;

use OxidEsales\EshopCommunity\Internal\Framework\Logger\Configuration\MonologConfiguration as OxidLoggerConfiguration;

class MonologConfiguration extends OxidLoggerConfiguration
{
//    /**
//     * @param string $loggerName
//     * @param string $logFilePath
//     * @param string $logLevel
//     */
//    public function __construct(
//        private $loggerName,
//        private $logFilePath,
//        private $logLevel
//    ) {
//    }

    public function getRemainingFiles(): int
    {
        return 5;
    }

    public function getNotificationMailAddress(): ?string
    {
        return 'alert@ds.data-develop.de';
    }
}