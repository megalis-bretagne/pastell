<?php

class PastellCoreTestOnChange extends ActionExecutor
{
    public function go()
    {
        $data = $this->getDonneesFormulaire()->get('test_on_change');
        $this->getDonneesFormulaire()->setData('test2', $data);
        return true;
    }
}
