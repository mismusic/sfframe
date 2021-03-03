<?php

namespace frame\core;

use app\exception\RequestException;

class Request
{
    protected $content = [];
    protected $request = [];
    protected $get = [];
    protected $post = [];
    protected $services = [];
    protected $headers = [];
    protected $files = [];
    protected $method;
    public function __construct()
    {
        if ($this->isRequestJson()) {
            $this->content = json_decode(file_get_contents('php://input'), true) ?? [];
        }
        if (! empty($_REQUEST)) {
            $this->request = $_REQUEST;
        }
        if (! empty($_GET)) {
            $this->get = $_GET;
        }
        if (! empty($_POST)) {
            $this->post = $_POST;
        }
        if (! empty($_FILES)) {
            $this->files = $_FILES;
        }
        foreach ($_SERVER as $name => $value) {
            if (stripos($name, 'HTTP_') !== false || strtoupper($name) === 'CONTENT_TYPE') {
                $this->headers[str_replace('http_', '', strtolower($name))] = $value;
            } else {
                $this->services[strtolower($name)] = $value;
            }
        }
        $this->method = $this->getService('request_method') ?? null;
    }
    public function isRequestJson() :bool
    {
        $contentType = $this->getHeader('content_type');
        if ($contentType && stripos($contentType, 'application/json') !== false)
        {
            return true;
        }
        return false;
    }
    public function isResponseJson() :bool
    {
        $contentType = $this->getHeader('accept');
        if ($contentType && stripos($contentType, 'application/json') !== false)
        {
            return true;
        }
        return false;
    }
    public function getMethod()
    {
        return $this->method;
    }
    public function isPost()
    {
        if (strtoupper($this->method) === 'POST')
        {
            return true;
        }
        return false;
    }
    public function isGet()
    {
        if (strtoupper($this->method) === 'GET')
        {
            return true;
        }
        return false;
    }
    public function addParam(string $name, string $value)
    {
        $this->request[$name] = $value;
        return $this;
    }
    public function all()
    {
        return $this->content ?: $this->request;
    }
    public function addGet(string $name, string $value)
    {
        if ($this->isRequestJson()) {
            $this->content[$name] = $value;
        } else {
            $this->get[$name] = $value;
        }
        return $this;
    }
    public function get($name = null, $default = null)
    {
        if (! is_null($name) && ! is_array($name) && ! is_string($name)) {
            throw new RequestException('request get param name type must is string or array or null');
        }
        $content = $this->isGet() ? $this->content : [];
        $data = $this->isRequestJson() ? $content : $this->get;
        if (is_null($name)) {
            return $data;
        }
        if (is_string($name)) {
            return $data[$name] ?? $default;
        }
        $result = [];
        foreach ($name as $item) {
            if (isset($data[$item])) {
                $result[$item] = $data[$item];
            }
        }
        return $result;
    }
    public function addPost(string $name, string $value)
    {
        if ($this->isRequestJson()) {
            $this->content[$name] = $value;
        } else {
            $this->get[$name] = $value;
        }
        return $this;
    }
    public function post($name = null, $default = null)
    {
        if (! is_null($name) && ! is_array($name) && ! is_string($name)) {
            throw new RequestException('request get param name type must is string or array or null');
        }
        $content = ! $this->isGet() ? $this->content : [];
        $data = $this->isRequestJson() ? $content : $this->post;
        if (is_null($name)) {
            return $data;
        }
        if (is_string($name)) {
            return $data[$name] ?? $default;
        }
        $result = [];
        foreach ($name as $item) {
            if (isset($data[$item])) {
                $result[$item] = $data[$item];
            }
        }
        return $result;
    }
    public function file($name = null, $default = null)
    {
        if (! is_null($name) && ! is_array($name) && ! is_string($name)) {
            throw new RequestException('request post param name type must is string or array or null');
        }
        if (is_null($name)) {
            return $this->files;
        }
        if (is_string($name)) {
            return $this->files[$name] ?? $default;
        }
        $result = [];
        foreach ($name as $item) {
            if (isset($this->files[$item])) {
                $result[$item] = $this->files[$item];
            }
        }
        /*
         * array(
         *  [name] => MyFile.jpg
         *  [type] => image/jpeg
         *  [tmp_name] => /tmp/php/php6hst32
         *  [error] => UPLOAD_ERR_OK
         *  [size] => 98174
         * )
         */
        return $result;
    }
    public function getService(string $name = null)
    {
        if (is_null($name)) {
            return $this->services;
        }
        return $this->services[strtolower($name)] ?? null;
    }
    public function addService(string $name, $value)
    {
        $this->services[strtolower($name)] = $value;
        return $this;
    }
    public function getHeader(string $name = null)
    {
        if (is_null($name)) {
            return $this->headers;
        }
        return $this->headers[strtolower($name)] ?? null;
    }
    public function addHeader(string $name, $value)
    {
        $this->headers[strtolower($name)] = $value;
        return $this;
    }
    public function root()
    {
        return $this->getService('document_root') ?? '';
    }
    public function isHttps() :bool
    {
        return $this->getService('https') ? true : false;
    }
    public function serverName()
    {
        return $this->getService('server_name') ?? '';
    }
    public function serverAddr()
    {
        return $this->getService('server_addr') ?? '';
    }
    public function serverSoftware()
    {
        return $this->getService('server_software') ?? '';
    }
    public function remoteName()
    {
        return $this->getService('remote_host') ?? '';
    }
    public function remoteAddr()
    {
        return $this->getService('remote_addr') ?? '';
    }
    public function path()
    {
        return $this->getService('path_info') ?? '';
    }
    public function uri($full = false)
    {
        $uri = $this->getService('request_uri') ?? '/';
        if ($full) {
            return $this->serverName() . $uri;
        }
        return $uri;
    }
    public function queryString()
    {
        $queryString = $this->getService('query_string') ?? '';
        $queryStringArr = explode('&', $queryString);
        $result = [];
        foreach ($queryStringArr as $str)
        {
            list($name, $value) = explode('=', $str);
            if (is_string($value)) {
                $value = urldecode($value);
            }
            $result[$name] = $value;
        }
        return $result;
    }
    public function ip()
    {
        return $this->realIp();
    }
    public function getContent()
    {
        return $this->content;
    }

    protected function realIp()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
            foreach ($matches[0] AS $xip) {
                if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                    $ip = $xip;
                    break;
                }
            }
        } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (isset($_SERVER['HTTP_X_REAL_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        }
        return $ip;
    }
}