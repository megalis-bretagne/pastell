<?php

class WebdavClientFactory
{
    public function getInstance($settings)
    {
        return new \Sabre\DAV\Client($settings);
    }
}
