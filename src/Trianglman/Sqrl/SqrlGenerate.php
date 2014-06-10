<?php
/*
 * The MIT License (MIT)
 * 
 * Copyright (c) 2013 John Judy
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace Trianglman\Sqrl;

use Endroid\QrCode\QrCode;

/**
 * Generates a SQRL QR image, URL and nonce.
 *
 * @author johnj
 */
class SqrlGenerate extends SqrlConfigurable implements SqrlGenerateInterface
{
    /**
     * @var SqrlStore
     */
    protected $store = null;

    protected $secure = false;

    protected $domain = '';

    protected $authPath = '';

    protected $qrHeight = 300;

    protected $qrPad = 10;

    protected $salt = 'asWB^<O]3>H*`a`h_b$XX6r*^6WkNV!;hAgL,X}:#mag"pq)lpUFuj^d5R3i?;X';

    protected $nonce = '';

    protected $requestorIP = 0;
    
    public function setNonce($nonce,$action = SqrlRequestHandlerInterface::INITIAL_REQUEST, $key = '')
    {
        $this->nonce = $nonce;
        if (!is_null($this->store)) {
            $this->store->storeNut($this->nonce, $this->requestorIP, $action, $key);
        }
    }

    public function getNonce($action = SqrlRequestHandlerInterface::INITIAL_REQUEST, $key = '')
    {
        if (empty($this->nonce)) {
            $this->generateNonce($action, $key);
        }

        return $this->nonce;
    }

    public function getUrl()
    {
        return $this->buildUrl();
    }

    public function configure($filePath)
    {
        $decoded = $this->loadConfigFromJSON($filePath);

        if (!empty($decoded->secure)) {
            $this->setSecure($decoded->secure > 0);
        }
        if (!empty($decoded->key_domain)) {
            $this->setKeyDomain($decoded->key_domain);
        }
        if (!empty($decoded->authentication_path)) {
            $this->setAuthenticationPath($decoded->authentication_path);
        }
        if (!empty($decoded->height)) {
            $this->setHeight($decoded->height);
        }
        if (!empty($decoded->padding)) {
            $this->setPadding($decoded->padding);
        }
        if (!empty($decoded->nonce_salt)) {
            $this->setSalt($decoded->nonce_salt);
        }
    }

    public function setStorage(SqrlStoreInterface $storage)
    {
        $this->store = $storage;
    }

    public function render($outputFile)
    {
        $qrCode = new QrCode();
        $qrCode->setText($this->getUrl());
        $qrCode->setSize($this->qrHeight);
        $qrCode->setPadding($this->qrPad);
        $qrCode->render($outputFile);
    }

    public function setHeight($height)
    {
        if (is_numeric($height)) {
            $this->qrHeight = $height;
        }
    }

    public function setPadding($pad)
    {
        if (is_numeric($pad)) {
            $this->qrPad = $pad;
        }
    }

    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    /**
     * Sets the IP of the user who requested the SQRL image
     *
     * @param string $ip
     *
     * @throws \InvalidArgumentException
     * @return void
     */
    public function setRequestorIp($ip)
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            throw new \InvalidArgumentException('Not a valid IPv4');
        }
        $this->requestorIP = ip2long($ip);
    }

    /**
     * Generates a random, one time use key to be used in the sqrl validation
     *
     * The implementation of this may get more complicated depending on the
     * requirements detailed in any reference implementation. Users wanting to
     * make this library more (or less) secure should override this function
     * to strengthen (or weaken) the randomness of the generation.
     *
     * @param int $action [Optional] The type of action this nonce is being generated for
     *
     * @see SqrlRequestHandler
     *
     * @param string $key [Optional] The public key associated with the nonce
     *
     * @return string
     */
    protected function generateNonce($action = SqrlRequestHandlerInterface::INITIAL_REQUEST, $key = '')
    {
        $this->nonce = hash_hmac('sha256', uniqid('', true), $this->salt);
        if (!is_null($this->store)) {
            $this->store->storeNut($this->nonce, $this->requestorIP, $action, $key);
        }

        return $this->nonce;
    }

    /**
     * Generates the URL to display in the QR code
     *
     * Separated this out to break out the logic that determines how to append
     * to the URL. This can be extended to add extra SQRL validation to add
     * requests for user information if that is determined to be valid in the
     * standard.
     *
     * @return string
     */
    protected function buildUrl()
    {
        $url = ($this->secure ? 's' : '').'qrl://'.$this->domain.(strpos(
                $this->domain,
                '/'
            ) !== false ? '|' : '/').$this->authPath;
        $currentPathParts = parse_url($url);
        if (!empty($currentPathParts['query'])) {
            $pathAppend = '&nut=';
        } else {
            $pathAppend = '?nut=';
        }

        return $url.$pathAppend.$this->getNonce();
    }

    public function setAuthenticationPath($path)
    {
        $this->authPath = $path;
    }

    public function setKeyDomain($domain)
    {
        $this->domain = $domain;
    }

    public function setSecure($sec)
    {
        $this->secure = (bool) $sec;
    }
}
