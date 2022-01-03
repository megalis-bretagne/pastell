<?php

class LastMessage
{
    public const DEFAULT_SESSION_KEY = 'last_message';

    protected $lastMessage;
    protected $lastPost;
    protected $sessionKey;
    protected $encoding;

    protected $css_class = "";

    public function __construct()
    {
        $this->sessionKey = self::DEFAULT_SESSION_KEY;
        if (isset($_SESSION[$this->sessionKey])) {
            $this->lastMessage = $_SESSION[$this->sessionKey];
            unset($_SESSION[$this->sessionKey]);
            if (isset($_SESSION['last_post'])) {
                $this->lastPost = $_SESSION['last_post'];
                unset($_SESSION['last_post']);
            } else {
                $this->lastPost = false;
            }
        }
        $this->setEncodingInput(ENT_QUOTES);
    }

    public function getLastMessage()
    {
        return $this->lastMessage;
    }

    public function getLastPostData()
    {
        return $this->lastPost;
    }

    public function setCssClass($css_class)
    {
        $this->css_class = $css_class;
    }


    public function getCssClass()
    {
        return $this->css_class;
    }

    public function setLastMessage($message)
    {
        $_SESSION[$this->sessionKey] = $message;
        $_SESSION['last_post'] = $_POST;
        $this->lastMessage = $message;
    }

    public function setEncodingInput($encoding = ENT_QUOTES)
    {
        $this->encoding = $encoding;
    }

    public function getLastInput($inputName)
    {
        if (empty($this->lastPost[$inputName])) {
            return false;
        }
        return htmlentities($this->lastPost[$inputName], $this->encoding);
    }

    public function deleteLastInput()
    {
        unset($_SESSION['last_post']);
    }
}
