<?php

class TotoControler extends PastellControler
{
    public function indexAction()
    {
        $documentSQL = $this->{'DocumentSQL'};
    }
}
-----
<?php

class TotoControler extends PastellControler
{
    public function indexAction()
    {
        $documentSQL = $this->getViewParameterOrObject('DocumentSQL');
    }
}