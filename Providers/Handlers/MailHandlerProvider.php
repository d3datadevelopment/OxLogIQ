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

namespace D3\OxLogIQ\Providers\Handlers;

use D3\LoggerFactory\LoggerFactory;
use D3\OxLogIQ\Interfaces\ProviderInterface;
use D3\OxLogIQ\MonologConfiguration;
use Monolog\Logger;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Configuration\MonologConfigurationInterface;

class MailHandlerProvider implements ProviderInterface
{
    /**
     * @param MonologConfiguration         $configuration
     * @codeCoverageIgnore
     */
    public function __construct(
        protected MonologConfigurationInterface $configuration,
        protected Config $shopConfig
    ){
    }

    public function isActive(): bool
    {
        return $this->configuration->useAlertMail();
    }

    public function provide(LoggerFactory $factory): void
    {
        $shop = $this->shopConfig->getActiveShop();
        $to       = (array) $this->configuration->getAlertMailRecipients();
        $subject  = sprintf(
            '%1$s - %2$s',
            $shop->getFieldData('oxname'),
            $this->configuration->getAlertMailSubject()
        );
        $from     = (string) (
            $this->configuration->getAlertMailFrom() ?? $shop->getFieldData('oxinfoemail')
        );
        $logLevel = (int) Logger::toMonologLevel($this->configuration->getAlertMailLevel());
        $factory->addMailHandler($to, $subject, $from, $logLevel)->setBuffering();
    }

    /**
     * @codeCoverageIgnore
     */
    public static function getPriority(): int
    {
        return 200;
    }
}
