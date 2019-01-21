<?php

namespace App\Helpers\Http;

class HttpCurl
{

    private $ch;
    public $http_code;
    private $http_url;
    public $api_url;
    public $timeout = 100;
    public $connecttimeout = 30;
    public $ssl_verifypeer = false;
    public $format = '';
    public $decodeFormat = 'json';
    public $http_info = array();
    public $http_header = array();
    private $contentType;
    private $postFields;
    private static $paramsOnUrlMethod = array('GET', 'DELETE');
    private static $supportExtension = array('json', 'xml');
    private $file = null;
    private static $userAgent = 'Sudaizhijia RESTClient';
    private static $nativeClient;

    public function __construct()
    {
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_USERAGENT, self::$userAgent);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($this->ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, $this->ssl_verifypeer);
        curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
        curl_setopt($this->ch, CURLOPT_HEADER, FALSE);
    }

    public static function i()
    {
        if (!(self::$nativeClient instanceof HttpCurl))
        {
            self::$nativeClient = new HttpCurl();
        }
        return self::$nativeClient;
    }

    public function call($url, $method, $postFields = null, $username = null, $password = null, $contentType = null)
    {
        if (strrpos($url, 'https://') !== 0 && strrpos($url, 'http://') !== 0 && !empty($this->format))
        {
            $url = "{$this->api_url}{$url}.{$this->format}";
        }
        $this->http_url = $url;
        $this->contentType = $contentType;
        $this->postFields = $postFields;
        $url = in_array($method, self::$paramsOnUrlMethod) ? $this->to_url() : $this->get_http_url();

        is_object($this->ch) or $this->__construct();
        switch ($method)
        {
            case 'POST': curl_setopt($this->ch, CURLOPT_POST, TRUE);
                if ($this->postFields != null)
                {
                    curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->postFields);
                }
                break;
            case 'DELETE':
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'PUT':
                curl_setopt($this->ch, CURLOPT_PUT, TRUE);
                if ($this->postFields != null)
                {
                    $this->file = tmpFile();
                    fwrite($this->file, $this->postFields);
                    fseek($this->file, 0);
                    curl_setopt($this->ch, CURLOPT_INFILE, $this->file);
                    curl_setopt($this->ch, CURLOPT_INFILESIZE, strlen($this->postFields));
                }
                break;
        }

        $this->setAuthorizeInfo($username, $password);

        $this->contentType != null && curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('Content-type:' . $this->contentType));
        curl_setopt($this->ch, CURLOPT_URL, $url);
        $response = curl_exec($this->ch);
        $this->http_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $this->http_info = array_merge($this->http_info, curl_getinfo($this->ch));
        $this->close();

        return $response;
    }

    public function POST($url, $params = null, $username = null, $password = null, $contentType = null)
    {
        $response = $this->call($url, 'POST', $params, $username, $password, $contentType);
        return $this->parseResponse($response);
    }

    public function PUT($url, $params = null, $username = null, $password = null, $contentType = null)
    {
        $response = $this->call($url, 'PUT', $params, $username, $password, $contentType);
        return $this->parseResponse($response);
    }

    public function GET($url, $params = null, $username = null, $password = null)
    {
        $response = $this->call($url, 'GET', $params, $username, $password);
        return $this->parseResponse($response);
    }

    public function DELETE($url, $params = null, $username = null, $password = null)
    {
        $response = $this->call($url, 'DELETE', $params, $username, $password);
        return $this->parseResponse($response);
    }

    public function parseResponse($resp, $ext = '')
    {
        return $resp;
    }

    public static function xml_decode($data, $toArray = false)
    {
        $data = simplexml_load_string($data);
        return $data;
    }

    public static function objectToArray($obj)
    {
        
    }

    public function get_http_url()
    {
        $parts = parse_url($this->http_url);
        $port = @$parts['port'];
        $scheme = $parts['scheme'];
        $host = $parts['host'];
        $path = @$parts['path'];
        $port or $port = ($scheme == 'https') ? '443' : '80';
        if (($scheme == 'https' && $port != '443') || ($scheme == 'http' && $port != '80'))
        {
            $host = "$host:$port";
        }
        return "$scheme://$host$path";
    }

    public function to_url()
    {
        $post_data = $this->to_postdata();
        $out = $this->get_http_url();
        if ($post_data)
        {
            $out .= '?' . $post_data;
        }
        return $out;
    }

    public function to_postdata()
    {
        return http_build_query($this->postFields);
    }

    public function setNotFollow()
    {
        curl_setopt($this->ch, CURLOPT_AUTOREFERER, FALSE);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, FALSE);
        return $this;
    }

    public function close()
    {
        curl_close($this->ch);
        if ($this->file != null)
        {
            fclose($this->file);
        }
    }

    public function setURL($url)
    {
        $this->url = $url;
    }

    public function setFormat($format = null)
    {
        if ($format == null)
            return false;
        $this->format = $format;
        return true;
    }

    public function setDecodeFormat($format = null)
    {
        if ($format == null)
            return false;
        $this->decodeFormat = $format;
        return true;
    }

    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    public function setAuthorizeInfo($username, $password)
    {
        if ($username != null)
        {
            curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($this->ch, CURLOPT_USERPWD, "{$username}:{$password}");
        }
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function setParameters($params)
    {
        $this->postFields = $params;
    }

    public function getHeader($ch, $header)
    {
        $i = strpos($header, ':');
        if (!empty($i))
        {
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
            $value = trim(substr($header, $i + 2));
            $this->http_header[$key] = $value;
        }
        return strlen($header);
    }

}

?>