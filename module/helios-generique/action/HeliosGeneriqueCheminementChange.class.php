<?php

/**
 * Class HeliosGeneriqueCheminementChange
 *
 * Il s'agit d'un copier/coller de ActesGeneriqueCheminementChange : je pense que la similitude est accidentelle et
 * le factorisation à ce niveau là n'apporterait rien.
 */
class HeliosGeneriqueCheminementChange extends ActionExecutor
{
    public function go()
    {
        $recuperateur = new Recuperateur($_POST);

        $has_information_complementaire = $this->getDonneesFormulaire()->get('has_information_complementaire');
        $info_needed = $this->isInformationComplementaireNeedeed();

        if ($has_information_complementaire == $info_needed) {
            return true;
        }

        $this->getDonneesFormulaire()->setData('has_information_complementaire', $info_needed);

        if ($recuperateur->get('suivant') || $recuperateur->get('precedent') || ! $info_needed) {
            return true;
        }

        $page = $this->getFormulaire()->getTabNumber("Informations complémentaires");
        $this->redirect("/Document/edition?id_d={$this->id_d}&id_e={$this->id_e}&page=$page");
        return true;
    }

    private function isInformationComplementaireNeedeed()
    {
        if ($this->getDonneesFormulaire()->get('envoi_tdt')) {
            return false;
        }

        return (bool) (
            $this->getDonneesFormulaire()->get('envoi_sae') ||
            $this->getDonneesFormulaire()->get('envoi_ged')
        );
    }
}
