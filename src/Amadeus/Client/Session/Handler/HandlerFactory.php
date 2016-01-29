<?php
/**
 * amadeus-ws-client
 *
 * Copyright 2015 Amadeus Benelux NV
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package Amadeus
 * @license https://opensource.org/licenses/Apache-2.0 Apache 2.0
 */

namespace Amadeus\Client\Session\Handler;

use Amadeus\Client;
use Amadeus\Client\Params\SessionHandlerParams;
use Amadeus\Client\Install\SomewhatRandomGenerator;

/**
 * HandlerFactory generates the correct Session Handler based on the incoming params.
 *
 * @package Amadeus\Client\Session\Handler
 * @author Dieter Devlieghere <dieter.devlieghere@benelux.amadeus.com>
 */
class HandlerFactory
{
    /**
     * @param SessionHandlerParams $handlerParams
     * @return HandlerInterface
     * @throws \InvalidArgumentException when the parameters to create the handler do not make sense.
     */
    public static function createHandler($handlerParams)
    {
        $theHandler = null;

        $handlerParams = self::loadNonceBase($handlerParams);

        switch ($handlerParams->soapHeaderVersion) {
            case Client::HEADER_V4:
                $theHandler = new SoapHeader4($handlerParams);
                break;
            case Client::HEADER_V2:
            case Client::HEADER_V1:
            default:
                //TODO implement Client::HEADER_V2 & Client::HEADER_V1
                throw new \InvalidArgumentException(
                    'No Session Handler found for soapHeaderVersion ' . $handlerParams->soapHeaderVersion
                );
        }

        return $theHandler;
    }

    /**
     * Get the NONCE base to be used when generating somewhat random nonce strings
     *
     * From the NONCE base file, or fallback is a new somewhat random string each instantiation.
     *
     * @param SessionHandlerParams $handlerParams
     * @return SessionHandlerParams
     */
    protected static function loadNonceBase($handlerParams)
    {
        $fullPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
        $fullPath .= DIRECTORY_SEPARATOR . "conf" . DIRECTORY_SEPARATOR . "noncebase.txt";

        if (!file_exists($fullPath) || !is_readable($fullPath)) {
            //FALLBACK: USE A NEW NONCE BASE EACH TIME
            $handlerParams->authParams->nonceBase = SomewhatRandomGenerator::generateSomewhatRandomString();
        } else {
            if (!isset($handlerParams->authParams->nonceBase) || empty($handlerParams->authParams->nonceBase)) {
                $handlerParams->authParams->nonceBase = file_get_contents($fullPath);
            }
        }

        return $handlerParams;
    }
}
