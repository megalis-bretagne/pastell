<?php

class Controler
{
    private $objectInstancier;
    private $viewParameter;

    private $dont_redirect = false;

    private $server_info;
    /** @var  Recuperateur */
    private $getInfo;
    /** @var  Recuperateur */
    private $postInfo;

    public function __construct(ObjectInstancier $objectInstancier)
    {
        $this->objectInstancier = $objectInstancier;
        $this->viewParameter = [];
        $this->setGetInfo(new Recuperateur([]));
        $this->setPostInfo(new Recuperateur([]));
    }


    public function _beforeAction()
    {
    }

    public function setServerInfo(array $server_info)
    {
        $this->server_info = $server_info;
    }

    public function getServerInfo($key)
    {
        return $this->getFormArray($this->server_info, $key);
    }

    public function setGetInfo(Recuperateur $getInfo)
    {
        $this->getInfo = $getInfo;
    }

    public function getGetInfo()
    {
        return $this->getInfo;
    }

    public function setPostInfo(Recuperateur $postInfo)
    {
        $this->postInfo = $postInfo;
    }

    public function getPostInfo()
    {
        return $this->postInfo;
    }

    public function getPostOrGetInfo()
    {
        if ($this->getServerInfo('REQUEST_METHOD') == 'POST') {
            return $this->getPostInfo();
        } else {
            return $this->getGetInfo();
        }
    }

    private function getFormArray($array, $key)
    {
        if (empty($array[$key])) {
            return false;
        }
        return $array[$key];
    }


    public function setDontRedirect($dont_redirect)
    {
        $this->dont_redirect = $dont_redirect;
    }

    public function isDontRedirect()
    {
        return $this->dont_redirect;
    }


    /**
     * @return LastMessage
     */
    public function getLastMessage()
    {
        return $this->getObjectInstancier()->getInstance(LastMessage::class);
    }

    /**
     * @return LastError
     */
    public function getLastError()
    {
        return $this->getObjectInstancier()->getInstance(LastError::class);
    }

    public function setLastError($message)
    {
        /** @var LastError $lastError */
        $lastError = $this->getObjectInstancier()->getInstance(LastError::class);
        $lastError->setLastError($message);
    }

    public function setLastMessage($message)
    {
        /** @var LastMessage $lastMessage */
        $lastMessage = $this->getObjectInstancier()->getInstance(LastMessage::class);
        $lastMessage->setLastMessage($message);
    }

    /**
     * @deprecated Use getInstance() or getViewParameterByKey() instead
     * @param $key
     * @return mixed|object|ObjectInstancier|null
     */
    public function getViewParameterOrObject($key)
    {
        if ($this->isViewParameter($key)) {
            return $this->viewParameter[$key];
        }
        return $this->objectInstancier->getInstance($key);
    }

    public function getObjectInstancier()
    {
        return $this->objectInstancier;
    }

    public function getInstance($class_name)
    {
        return $this->getObjectInstancier()->getInstance($class_name);
    }

    /**
     * @param $key
     * @return mixed
     * @throws UnrecoverableException
     */
    public function getViewParameterByKey($key): mixed
    {
        if (! $this->isViewParameter($key)) {
            throw new UnrecoverableException("Impossible de récupérer la valeur du paramètre $key");
        }
        return $this->viewParameter[$key];
    }

    public function setViewParameter($key, $value)
    {
        $this->viewParameter[$key] = $value;
    }

    public function setAllViewParameter(array $viewParameter)
    {
        $this->viewParameter = $viewParameter;
    }

    public function getViewParameter()
    {
        return $this->viewParameter;
    }

    public function isViewParameter($key)
    {
        return isset($this->viewParameter[$key]);
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function redirect(string $to = ''): never
    {
        $url = rtrim(SITE_BASE, "/") . "/" . ltrim($to, "/");
        $this->doRedirect($url);
    }

    public function absoluteRedirect($url)
    {
        $this->doRedirect($url);
    }

    /**
     * @throws LastMessageException
     * @throws LastErrorException
     */
    private function doRedirect(string $url): never
    {
        if ($this->isDontRedirect()) {
            $error = $this->getLastError()->getLastError();
            $this->getLastError()->setLastMessage(false);
            if ($error) {
                throw new LastErrorException("Redirection vers $url : $error");
            } else {
                $message = $this->getLastMessage()->getLastMessage();
                $this->getLastMessage()->setLastMessage(false);
                throw new LastMessageException("Redirection vers $url: $message");
            }
        }
        header_wrapper("Location: $url");
        exit_wrapper();
    }

    /**
     * @return Gabarit
     */
    public function getGabarit()
    {
        return $this->getInstance(Gabarit::class);
    }

    public function renderDefault()
    {
        $this->getGabarit()->setParameters($this->getViewParameter());
        $this->getGabarit()->render("Page");
    }

    public function render($template)
    {
        $this->getGabarit()->setParameters($this->getViewParameter());
        $this->getGabarit()->render($template);
    }
}
