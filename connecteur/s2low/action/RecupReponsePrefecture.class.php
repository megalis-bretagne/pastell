<?php

class RecupReponsePrefecture extends ActionExecutor
{
    /**
     * @return bool
     * @throws S2lowException
     * @throws Exception
     */
    public function go()
    {
        /** @var S2low $s2low */
        $s2low = $this->getMyConnecteur();
        $numberOfResponses = $s2low->getListDocumentPrefecture();
        $message = $numberOfResponses > 1 ?
            "$numberOfResponses réponses de la préfecture ont été récupérées."
            : "$numberOfResponses réponse de la préfecture a été récupérée.";
        $this->setLastMessage($message);

        return true;
    }
}
