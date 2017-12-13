<?php

class CurlWrapperFactory {

    function getInstance(){
        return new CurlWrapper(new CurlFunctions());
    }
}