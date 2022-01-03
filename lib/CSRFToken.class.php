<?php

class CSRFToken
{
    public const TOKEN_NAME =  'csrf_token';

    private $session;

    private $post_parameter;

    public function __construct()
    {
        $this->setPostParameter($_POST);
        if (isset($_SESSION)) {
            $this->setSession($_SESSION);
        }
    }

    public function setSession(array &$session)
    {
        $this->session = & $session;
    }

    public function setPostParameter(array $post_parameter)
    {
        $this->post_parameter = $post_parameter;
    }

    public function displayFormInput()
    {
        ?>
        <input type="hidden" name="<?php echo self::TOKEN_NAME ?>" value="<?php echo $this->getCSRFToken() ?>" />
        <?php
    }

    public function verifToken()
    {
        if (
            empty($this->post_parameter[self::TOKEN_NAME]) ||
            $this->post_parameter[self::TOKEN_NAME] != $this->getCSRFToken()
        ) {
            throw new Exception("Votre session n'était plus valide. Le formulaire doit-être réinitialisé.");
        }
        return true;
    }

    public function deleteToken()
    {
        if (isset($this->session[self::TOKEN_NAME])) {
            unset($this->session[self::TOKEN_NAME]);
        }
    }

    public function getCSRFToken()
    {
        if (empty($this->session[self::TOKEN_NAME])) {
            $this->session[self::TOKEN_NAME] = base64_encode(openssl_random_pseudo_bytes(32));
        }
        return $this->session[self::TOKEN_NAME];
    }
}