<?php

require_once __DIR__ . '/../../../connecteur-type/Tdt/lib/TdtClassification.php';

class RecupClassification extends ActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        /** @var S2low $connecteur */
        $connecteur = $this->getMyConnecteur();
        $classification = new TdtClassification($connecteur);
        $classificationFile = $classification->getClassificationFile();
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
