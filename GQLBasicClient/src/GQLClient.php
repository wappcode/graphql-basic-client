<?php

namespace GQLBasicClient;

/**
 * GraphQL Basic Client
 * 
 * A simple GraphQL client for making requests to GraphQL endpoints
 */
final class GQLClient
{
    /**
     * Default timeout in seconds
     */
    private const DEFAULT_TIMEOUT = 30;

    /**
     * Default connection timeout in seconds
     */
    private const DEFAULT_CONNECT_TIMEOUT = 10;

    /**
     * @var string GraphQL endpoint URL
     */
    private $url;

    /**
     * @var int Request timeout in seconds
     */
    private $timeout;

    /**
     * @var int Connection timeout in seconds
     */
    private $connectTimeout;

    /**
     * @var bool Whether to verify SSL certificates
     */
    private $verifySSL;

    /**
     * @var bool Enable debug mode
     */
    private $debug;

    /**
     * @var array Additional cURL options
     */
    private $curlOptions;

    /**
     * @param string $url GraphQL endpoint URL
     * @param array $options Configuration options [
     *   'timeout' => int,           // Request timeout in seconds (default: 30)
     *   'connectTimeout' => int,    // Connection timeout in seconds (default: 10)
     *   'verifySSL' => bool,        // Verify SSL certificates (default: true)
     *   'debug' => bool,            // Enable debug mode (default: false)
     *   'curlOptions' => array      // Additional cURL options
     * ]
     * @throws GQLClientException
     */
    public function __construct(string $url, array $options = [])
    {
        $this->validateUrl($url);
        $this->url = $url;
        $this->timeout = $options['timeout'] ?? self::DEFAULT_TIMEOUT;
        $this->connectTimeout = $options['connectTimeout'] ?? self::DEFAULT_CONNECT_TIMEOUT;
        $this->verifySSL = $options['verifySSL'] ?? true;
        $this->debug = $options['debug'] ?? false;
        $this->curlOptions = $options['curlOptions'] ?? [];
    }

    /**
     * Make a GraphQL request
     *
     * @param string $query GraphQL query or mutation
     * @param array|null $variables Query variables
     * @param array|null $headers Additional HTTP headers (e.g., ["Authorization: Bearer token"])
     * @return array Response data
     * @throws GQLClientException
     */
    public function execute(string $query, ?array $variables = null, ?array $headers = null): array
    {
        $this->validateQuery($query);

        $curl = $this->initializeCurl();
        $this->configureCurl($curl, $query, $variables, $headers);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        $errno = curl_errno($curl);

        curl_close($curl);

        if ($response === false) {
            throw new GQLClientException(
                "cURL error: {$error}",
                $errno,
                null,
                ['url' => $this->url, 'errno' => $errno]
            );
        }

        $this->validateHttpResponse($httpCode, $response);

        return $this->decodeResponse($response);
    }

    /**
     * Initialize cURL handle
     *
     * @return resource
     * @throws GQLClientException
     */
    private function initializeCurl()
    {
        $curl = curl_init($this->url);
        
        if ($curl === false) {
            throw new GQLClientException(
                "Failed to initialize cURL",
                0,
                null,
                ['url' => $this->url]
            );
        }

        return $curl;
    }

    /**
     * Configure cURL options
     *
     * @param resource $curl
     * @param string $query
     * @param array|null $variables
     * @param array|null $headers
     * @return void
     * @throws GQLClientException
     */
    private function configureCurl($curl, string $query, ?array $variables, ?array $headers): void
    {
        $data = $this->createGQLQuery($query, $variables);

        $defaultHeaders = ['Content-Type: application/json'];
        $finalHeaders = is_array($headers) ? array_merge($defaultHeaders, $headers) : $defaultHeaders;

        $options = [
            CURLOPT_URL => $this->url,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => $finalHeaders,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_SSL_VERIFYPEER => $this->verifySSL,
            CURLOPT_SSL_VERIFYHOST => $this->verifySSL ? 2 : 0,
        ];

        if ($this->debug) {
            $options[CURLOPT_VERBOSE] = true;
        }

        // Merge with custom cURL options (custom options can override defaults)
        $options = array_replace($options, $this->curlOptions);

        foreach ($options as $option => $value) {
            curl_setopt($curl, $option, $value);
        }
    }

    /**
     * Create GraphQL query payload
     *
     * @param string $query
     * @param array|null $variables
     * @return string JSON encoded query
     * @throws GQLClientException
     */
    private function createGQLQuery(string $query, ?array $variables): string
    {
        $payload = [
            'query' => $query,
            'variables' => $variables ?? [],
        ];

        $json = json_encode($payload);

        if ($json === false) {
            throw new GQLClientException(
                "Failed to encode query to JSON: " . json_last_error_msg(),
                json_last_error()
            );
        }

        return $json;
    }

    /**
     * Decode JSON response
     *
     * @param string $response
     * @return array
     * @throws GQLClientException
     */
    private function decodeResponse(string $response): array
    {
        $result = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new GQLClientException(
                "Failed to decode JSON response: " . json_last_error_msg(),
                json_last_error(),
                null,
                ['response' => substr($response, 0, 500)]
            );
        }

        // Check for GraphQL errors
        if (isset($result['errors']) && is_array($result['errors'])) {
            $errorMessages = array_map(function ($error) {
                return $error['message'] ?? 'Unknown error';
            }, $result['errors']);

            throw new GQLClientException(
                "GraphQL errors: " . implode(', ', $errorMessages),
                0,
                null,
                ['errors' => $result['errors']]
            );
        }

        return $result;
    }

    /**
     * Validate HTTP response
     *
     * @param int $httpCode
     * @param string $response
     * @return void
     * @throws GQLClientException
     */
    private function validateHttpResponse(int $httpCode, string $response): void
    {
        if ($httpCode < 200 || $httpCode >= 300) {
            throw new GQLClientException(
                "HTTP error: {$httpCode}",
                $httpCode,
                null,
                [
                    'httpCode' => $httpCode,
                    'response' => substr($response, 0, 500),
                    'url' => $this->url
                ]
            );
        }
    }

    /**
     * Validate URL
     *
     * @param string $url
     * @return void
     * @throws GQLClientException
     */
    private function validateUrl(string $url): void
    {
        if (empty($url)) {
            throw new GQLClientException("URL cannot be empty");
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new GQLClientException(
                "Invalid URL format: {$url}",
                0,
                null,
                ['url' => $url]
            );
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new GQLClientException(
                "URL must use HTTP or HTTPS protocol",
                0,
                null,
                ['url' => $url, 'scheme' => $scheme]
            );
        }
    }

    /**
     * Validate GraphQL query
     *
     * @param string $query
     * @return void
     * @throws GQLClientException
     */
    private function validateQuery(string $query): void
    {
        if (empty(trim($query))) {
            throw new GQLClientException("GraphQL query cannot be empty");
        }
    }

    /**
     * Get the configured endpoint URL
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }
}
