<?php

class RecupReponsePrefectureAll extends ActionExecutor
{
    public function go()
    {
        $all_col =  $this->getEntiteSQL()->getAll();

        $envoye = [];
        foreach ($all_col as $infoCollectivite) {
            try {
                $tdT = $this->getConnecteurFactory()->getConnecteurByType(
                    $infoCollectivite['id_e'],
                    'actes-reponse-prefecture',
                    'TdT'
                );
                if (!$tdT) {
                    continue;
                }
                /** @var S2low $tdT */
                $numberOfResponses = $tdT->getListDocumentPrefecture();
                $message = $numberOfResponses > 1 ?
                    "$numberOfResponses réponses de la préfecture ont été récupérées."
                    : "$numberOfResponses réponse de la préfecture a été récupérée.";

                $envoye[] = "{$infoCollectivite['denomination']}  : $message";
            } catch (Exception $e) {
                $envoye[] = "{$infoCollectivite['denomination']}  : " . ($e->getMessage());
                continue;
            }
        }

        $this->setLastMessage("Résultat :<br/>" . implode("<br/>", $envoye));
        return true;
    }
}