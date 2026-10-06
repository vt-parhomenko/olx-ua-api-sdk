<?php

declare(strict_types=1);

namespace Parhomenko\Olx;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Parhomenko\Olx\Exceptions\UnknownCountryException;

class OlxFactory
{
    /**
     * @param string|Country $country country code (e.g. "ua") or a Country enum case
     * @param Credentials|array<string, mixed> $credentials credential set; client_id and client_secret are required
     * @param bool $update_token refresh the access token on construction if it has expired
     * @param array<string, mixed> $httpOptions Guzzle client options (e.g. timeout, connect_timeout, handler)
     * @return Api
     * @throws UnknownCountryException for an unsupported country
     * @throws GuzzleException on a token-refresh transport error
     * @throws Exception if required credentials are missing or the token refresh fails
     */
    public static function get(
        string|Country $country,
        Credentials|array $credentials,
        bool $update_token = false,
        array $httpOptions = [],
    ): Api {
        $country = $country instanceof Country
            ? $country
            : (Country::tryFrom($country) ?? throw new UnknownCountryException("Country '$country' is not supported"));

        return new Api($country->baseUri(), $credentials, $update_token, $httpOptions);
    }

}
