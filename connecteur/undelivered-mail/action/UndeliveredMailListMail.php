<?php

class UndeliveredMailListMail extends ActionExecutor
{
    public function go()
    {
        /** @var UndeliveredMail $undeliveredMail */
        $undeliveredMail = $this->getMyConnecteur();

        $result = $undeliveredMail->listMail();
        $display = "Information sur le serveur : " .
            " <br/><table><tr><th>UID</th><th>from</th><th>sujet</th><th>En-tête Pastell</th><th>id_de</th></tr>";
        foreach ($result as $mail) {
            $display .=
                "<tr>" .
                "<td>{$mail['uid']}</td>" .
                "<td>{$mail['from']}</td>" .
                "<td>{$mail['subject']}</td>" .
                "<td>{$mail['pastell_header']}</td>" .
                "<td>{$mail['id_de']}</td>" .
                "</tr>";
        }
        $display .= "</table>";
        $this->setLastMessage($display);
        return true;
    }
}
