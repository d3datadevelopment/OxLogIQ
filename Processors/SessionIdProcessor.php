<?php

declare(strict_types=1);

namespace D3\ShopLogger\Processors;

use InvalidArgumentException;
use Monolog\Processor\ProcessorInterface;
use OxidEsales\Eshop\Core\Registry;

class SessionIdProcessor implements ProcessorInterface
{
    private string $sid;

    public function __construct( $length = 7 )
    {
        if ( ! is_int( $length ) || $length > 32 || $length < 1 ) {
            throw new InvalidArgumentException( 'The session id length must be an integer between 1 and 32' );
        }

        $this->sid = $this->getShopSid( $length );
    }

    public function __invoke( array $records ): array
    {
        $records['extra']['sid'] = $this->sid;

        return $records;
    }

    public function getShopSid( int $length = 32 ): string
    {
        return substr(Registry::getSession()->getId(), 0, $length);
    }
}