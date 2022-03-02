<?php

class TotoControler extends PastellControler
{
    public function indexAction()
    {
        $foo = new Gabarit();
        $foo->{'title'} = "Hello world !";
    }
}