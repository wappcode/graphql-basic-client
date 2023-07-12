<?php

namespace GQLBasicClient;

final class GQLClient
{
    protected $url;

    public function __construct($url)
    {
        $this->url = $url;
    }



    public function executeQuery(string $query, ?array $variables = null, $headers = null)
    {
        $curl = curl_init($this->url);
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $data = static::createGQLQuery($query, $variables);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
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
