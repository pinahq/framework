<?php

namespace Pina\Http;

class JsonAPI
{

    protected $endpoint = '';
    protected $headers = [];
    protected $info = false;
    protected $response = null;

    public function __construct(string $endpoint, array $headers = [])
    {
        $this->endpoint = $endpoint;
        $this->headers = $headers;
    }

    public function get($api)
    {
        return $this->request('get', $api);
    }

    public function post($api, $packet = null)
    {
        return $this->request('post', $api, $packet);
    }

    public function request($method, $api, $packet = null)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->resolveResource($api));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($this->makeHeaders($packet), $this->headers));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST, true);
        }
        if (!is_null($packet)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->encodePacket($packet));
        }

        $this->response = curl_exec($ch);
        $result = json_decode($this->response, true);

        $this->info = curl_getinfo($ch);
        curl_close($ch);

        return $this->processResponse($result);
    }

    public function getInfo()
    {
        return $this->info;
    }

    public function getLastResponse()
    {
        return $this->response;
    }

    public function isSuccess()
    {
        return ($this->info['http_code'] ?? '') == 200;
    }

    protected function makeHeaders($packet)
    {
        return [
            'Content-Type: application/json',
            'Accept: application/json',
        ];
    }

    protected function encodePacket($packet)
    {
        return json_encode($packet, JSON_UNESCAPED_UNICODE);
    }

    protected function resolveResource($api)
    {
        return rtrim($this->endpoint, '/') . '/' . ltrim($api, '/');
    }

    protected function processResponse($response)
    {
        return $response;
    }

}
