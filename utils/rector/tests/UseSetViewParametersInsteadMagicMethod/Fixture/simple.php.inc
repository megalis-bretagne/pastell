<?php

class TotoControler extends PastellControler
{
    public function indexAction()
    {
        $this->{'title'} = "Hello world !";
    }
}
-----
<?php

class TotoControler extends PastellControler
{
    public function indexAction()
    {
        $this->setViewParameter('title', "Hello world !");
    }
}