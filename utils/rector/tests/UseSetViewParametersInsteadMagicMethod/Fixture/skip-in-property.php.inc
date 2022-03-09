<?php

class TotoControler extends PastellControler
{

    private string $title;

    public function indexAction()
    {
        $this->title = "Hello world !";
    }

}