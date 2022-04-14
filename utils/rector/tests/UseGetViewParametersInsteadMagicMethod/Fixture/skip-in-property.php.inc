<?php

class TotoControler extends PastellControler
{

    private string $title;

    public function indexAction()
    {
        echo $this->title;
    }

}