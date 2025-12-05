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

use Exception;
use Monolog\Logger;
use Monolog\Processor\ProcessorInterface;

class SentryExceptionProcessor implements ProcessorInterface
{
    public function __invoke(array $records): array
    {
        if ($this->checkForException($records)) {
            // find exception in context and define it as Sentry exception
            foreach ($records['context'] as $key => $value) {
                if ($key != 'exception' && $value instanceof Exception) {
                    $records['context']['exception'] = $value;
                    unset($records['context'][$key]);
                }
            }
        }

        return $records;
    }

    /**
     * @param array $records
     *
     * @return bool
     */
    protected function checkForException(array $records): bool
    {
        return isset($records['level']) && $records['level'] >= Logger::ERROR &&   // level at least error
               isset($records['context']) && is_array($records['context']) && empty($records['context']['exception']);
    }
}
