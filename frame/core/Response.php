<?php

namespace frame\core;

use app\exception\ResponseException;

class Response
{
    protected $headers = [
        'Cache-Control' => 'no-cache, max-age=0',
        'Content-Type' => 'text/html; charset=UTF-8',
    ];
    protected $body = '';
    protected $statusCode = 200;
    protected $format = [
        'html' => 'text/html; charset=UTF-8',
        'plain' => 'text/plain; charset=UTF-8',
        'json' => 'application/json; charset=UTF-8',
    ];
    protected $contentType;

    public function body($content)
    {
        if (! is_string($content) && ! is_bool($content) && ! is_numeric($content) && ! is_null($content)) {
            throw new ResponseException('response body param content type must is string or bool or numeric');
        }
        $this->body = $content;
        return $this;
    }
    public function header(string $key, string $value)
    {
        $this->headers[$key] = $value;
        return $this;
    }
    public function headers(array $data)
    {
        $this->headers = array_merge($this->headers, $data);
        return $this;
    }
    public function statusCode(int $statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }
    public function format(string $format)
    {
        if (isset($this->format[$format])) {
            $this->contentType = $this->format[$format];
        }
        return $this;
    }
    public function json(array $data)
    {
        $this->contentType = $this->format['json'];
        $this->body = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return $this;
    }
    public function redirect(string $url)
    {
        $this->header('location', $url);
        return $this;
    }
    public function send()
    {
        $this->dispatch();
        echo $this->body;
    }
    protected function dispatch()
    {
        if (! headers_sent()) {
            http_response_code($this->statusCode);
            foreach ($this->headers as $key => $value)
            {
                header(sprintf('%s: %s', ucfirst(preg_replace_callback("#-[a-z]#", function ($mat) {
                    return strtoupper($mat[0]);
                }, $key)), $value));
            }
            if ($this->contentType) {
                header('Content-Type: ' . $this->contentType);
            }
            header('Date: ' . gmdate('D, d M Y H:i:s T',time() + 8 * 3600));
        }
    }
}