<?php

class UndeliveredMailTestConnexion extends ActionExecutor
{
    public function go()
    {
        /** @var UndeliveredMail $undeliveredMail */
        $undeliveredMail = $this->getMyConnecteur();

        $result = $undeliveredMail->testConnexion();
        $display = "Information sur le serveur : <br/><table>";
        foreach ($result as $key => $value) {
            $display .= "<tr><th>$key : </th><td>$value</td></tr>";
        }
        $display .= "</table>";
        $this->setLastMessage($display);
        return true;
    }
}
