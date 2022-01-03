<?php

class StandardLigneDePoste extends ChoiceActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {

        $recuperateur = $this->getRecuperateur();

        $ligne_de_poste = $this->getLigneDePoste();

        $data = array('lignePosteReference','lignePosteDenomination','lignePosteQuantite');

        $one_line = array();
        foreach ($data as $id) {
            $one_line[$id] = $recuperateur->get($id);
        }

        $ligne_de_poste[] = $one_line;

        $file_content  = json_encode($ligne_de_poste);

        $this->getDonneesFormulaire()->addFileFromData('fichier_ligne_de_poste', 'fichier_ligne_de_poste.json', $file_content);
        $this->getDonneesFormulaire()->setData('ligne_de_poste', count($ligne_de_poste) . " ligne de poste");
        return true;
    }

    /**
     * @return array|bool|false|mixed|string
     */
    private function getLigneDePoste()
    {
        $ligne_de_poste = $this->getDonneesFormulaire()->getFileContent('fichier_ligne_de_poste');
        if ($ligne_de_poste) {
            $ligne_de_poste = json_decode($ligne_de_poste, true);
        } else {
            $ligne_de_poste = array();
        }
        return $ligne_de_poste;
    }

    public function display()
    {

        $document_info = $this->getDocument()->getInfo($this->id_d);
        $this->{'info'} = $document_info;

        $this->{'ligne_de_poste'} = $this->getLigneDePoste();
        $this->renderPage("Edition des lignes de poste", __DIR__ . "/../template/StandardLigneDePoste.php");
    }


    /**
     * @throws Exception
     */
    public function displayAPI()
    {
        throw new Exception("Les lignes de poste sont accessible via l'élément Pastell fichier_ligne_de_poste");
    }
}
