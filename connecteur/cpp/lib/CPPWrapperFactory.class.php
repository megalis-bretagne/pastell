<?php

use Monolog\Logger;

class CPPWrapperFactory
{
    /** @var CurlWrapperFactory */
    private $curlWrapperFactory;

    /** @var MemoryCache */
    private $memoryCache;

    /**
     * @var UTF8Encoder
     */
    private $utf8Encoder;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(CurlWrapperFactory $curlWrapperFactory, MemoryCache $memoryCache, UTF8Encoder $utf8Encoder, Logger $logger)
    {
        $this->curlWrapperFactory = $curlWrapperFactory;
        $this->memoryCache = $memoryCache;
        $this->utf8Encoder = $utf8Encoder;
        $this->logger = $logger;
    }

    public function newInstance()
    {
        return new CPPWrapper($this->curlWrapperFactory, $this->memoryCache, $this->utf8Encoder, $this->logger);
    }
}
