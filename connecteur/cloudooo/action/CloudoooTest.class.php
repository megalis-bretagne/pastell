<?php

class CloudoooTest extends ActionExecutor
{
    public function go()
    {
        $cloudooo = $this->getMyConnecteur();
        $cloudooo->convertField($this->getConnecteurProperties(), 'document_test', 'document_test_result');
        $this->setLastMessage("Le document a été converti en PDF");
        return true;
    }
}
