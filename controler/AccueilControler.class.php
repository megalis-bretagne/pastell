<?php

class AccueilControler extends PastellControler
{
    private $exception;

    public function setException(Exception $e)
    {
        $this->exception = $e;
    }

    public function getException()
    {
        return $this->exception;
    }

    public function errorAction()
    {
        $this->{'page_title'} = "Ooops";
        $this->{'template_milieu'} = "AccueilError";
        $this->{'the_exception'} = $this->exception;
        $this->renderDefault();
    }

    public function notFoundAction()
    {
        header_wrapper('HTTP/1.1 404 Not Found');
        $this->{'page_title'} = "404 - La page demandée n'a pas été trouvée";
        $this->{'template_milieu'} = "AccueilError";
        $this->{'the_exception'} = $this->exception;
        $this->renderDefault();
    }
}
