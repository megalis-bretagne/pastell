<?php

class PurgeTypeDocument extends ChoiceActionExecutor
{

    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        $document_type = $this->getRecuperateur()->get('document_type');
        $list_flux = $this->displayAPI();
        if (empty($list_flux[$document_type])) {
            throw new Exception("Ce type de dossier n'existe pas");
        }
        $this->getConnecteurProperties()->setData('document_type', $document_type);
        $this->getConnecteurProperties()->setData('document_type_libelle', $list_flux[$document_type]['nom']);
        return true;
    }

    public function displayAPI()
    {
        return $this->apiGet("/Flux", array());
    }

    public function display()
    {
        $this->document_type = $this->getConnecteurProperties()->get('document_type');
        $list_flux = $this->displayAPI();

        $currentLocale = setlocale(LC_COLLATE, 0);
        setlocale(LC_COLLATE, 'fr_FR.utf8');
        uasort($list_flux, function ($a, $b) {
            return strcoll($a['nom'], $b['nom']);
        });
        setlocale(LC_COLLATE, $currentLocale);

        $this->list_flux = $list_flux;
        $this->renderPage(
            "Choix du type de dossier",
            __DIR__ . "/../template/PurgeTypeDocument.php"
        );
        return true;
    }
}
