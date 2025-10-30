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

namespace D3\OxLogIQ\Processors;

use InvalidArgumentException;
use Monolog\Processor\ProcessorInterface;
use OxidEsales\Eshop\Core\Session;

class SessionIdProcessor implements ProcessorInterface
{
    protected string $sid;

    /**
     * @param \OxidEsales\EshopCommunity\Core\Session $session
     * @param int $length
     */
    public function __construct(protected Session $session, int $length = 7)
    {
        if ($length > 32 || $length < 1) {
            throw new InvalidArgumentException('The session id length must be an integer between 1 and 32');
        }

        $this->sid = $this->getShopSid($length);
    }

    public function __invoke(array $records): array
    {
        $records['extra']['sid'] = $this->sid;

        return $records;
    }

    public function getShopSid(int $length = 32): string
    {
        return substr((string) $this->session->getId(), 0, $length);
    }
}
