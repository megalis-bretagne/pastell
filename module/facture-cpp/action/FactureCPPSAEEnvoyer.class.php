<?php

require_once PASTELL_PATH . "/connecteur-type/SAE/SAEEnvoyer.class.php";
require_once __DIR__ . "/../../../module/facture-formulaire-pivot/lib/FactureFichierPivot.class.php";
require_once __DIR__ . "/../../../module/facture-cpp/lib/ExtraireDonneesPivot.class.php";

class FactureCPPSAEEnvoyer extends SAEEnvoyer
{

    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        $this->setDataSedaClassName(FluxDataSedaFactureCPP::class);
        try {
            if (!$this->extrairePivot()) {
                return false;
            }
        } catch (Exception $e) {
            $this->setLastMessage('ERREUR : ' . $e->getMessage());
            return false;
        }
        return parent::go();
    }

    /**
     * @return bool
     * @throws NotFoundException
     * @throws Exception
     */
    private function extrairePivot()
    {
        $donneesFormulaire = $this->getDonneesFormulaire();

        $fichier_pivot = $donneesFormulaire->getFilePath('fichier_facture');
        if (!$fichier_pivot) {
            throw new Exception("Le fichier CPPFacturePivot est manquant.");
        }
        /** @var FactureFichierPivot $pivot */
        $pivot = new FactureFichierPivot();
        try {
            $pivot->verifIsFormatPivot($fichier_pivot);
        } catch (Exception $e) {
            throw new Exception("Le fichier CPPFacturePivot est incorrect: " . $e->getMessage());
        }

        //Extraction des donnees pivot
        $donnees_pivot = $this->objectInstancier->getInstance(ExtraireDonneesPivot::class);

        $metadata = $donnees_pivot->getFournisseur($donneesFormulaire);
        $metadata += $donnees_pivot->getDebiteur($donneesFormulaire);
        $metadata += $donnees_pivot->getDonneesFacture($donneesFormulaire);

        $donneesFormulaire->addFileFromData('sae_config', 'donnees_pivot.json', json_encode($metadata));

        return true;
    }
}
