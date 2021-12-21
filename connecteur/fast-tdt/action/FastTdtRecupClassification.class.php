<?php

require_once __DIR__ . '/../lib/FastTdtClassification.php';

class FastTdtRecupClassification extends ActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        /** @var FastTdt $connecteur */
        $connecteur = $this->getMyConnecteur();
        $classification = new FastTdtClassification($connecteur);
        $classificationFile = $classification->getClassificationFile();

        if (!$classificationFile) {
            $this->setLastMessage("Il n'y a actuellement pas de nouvelle classification disponible");
            return true;
        }

        $classificationDate = $classification->getClassificationDate($classificationFile);
        $this->getConnecteurProperties()->addFileFromData(
            "classification_file",
            "classification.xml",
            $classificationFile
        );
        $this->getConnecteurProperties()->setData("classification_date", $classificationDate);

        $this->setLastMessage("La classification a été mise à jour");
        return true;
    }
}
