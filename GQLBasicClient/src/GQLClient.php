<?php

namespace GQLBasicClient;

final class GQLClient
{
    protected $url;

    public function __construct($url)
    {
        $this->url = $url;
    }



    /**
     * Make a Graphql request
     *
     * @param string $query
     * @param array|null $variables
     * @param array|null $headers  array of strings, example ["Authorization: Bearer jwt"] 
     * @return void
     */
    public function execute(string $query, ?array $variables = null, array $headers = null)
    {
        $curl = curl_init($this->url);
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $data = static::createGQLQuery($query, $variables);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $defaultHeaders = ['Content-Type:application/json'];
        $headerFinal = is_array($headers) ? array_merge($defaultHeaders, $headers) : $defaultHeaders;

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headerFinal);
        $resp = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($resp, true);
        return $result;
    }

    protected function createGQLQuery($query, $variables)
    {
        $result = [
            "variables" => $variables ?? [],
            "query" => $query
        ];
        return json_encode($result);
    }
}
