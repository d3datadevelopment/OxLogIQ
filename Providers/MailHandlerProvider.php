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
use D3\OxLogIQ\MonologConfiguration;
use Monolog\Logger;
use OxidEsales\Eshop\Application\Model\Shop;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Configuration\MonologConfigurationInterface;

class MailHandlerProvider implements ProviderInterface
{
    /**
     * @param MonologConfiguration         $configuration
     */
    public function __construct(protected MonologConfigurationInterface $configuration)
    {
    }

    public function register(LoggerFactory $factory): void
    {
        if ($this->configuration->useAlertMail()) {
            /** @var Shop $shop */
            $shop = Registry::getConfig()->getActiveShop();
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
    }
}
