<?php

class CurlWrapperFactory
{

    public function getInstance()
    {
        return new CurlWrapper(new CurlFunctions());
    }
}
