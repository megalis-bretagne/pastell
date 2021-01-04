<?php

class SignatureLocale extends ChoiceActionExecutor
{

    public function go()
    {
        $recuperateur = new Recuperateur($_POST);
        $signature = $recuperateur->get("signature_1");
        if (! $signature) {
            throw new Exception("Aucune signature n'a été trouvée");
        }
        $signature = base64_decode($signature);
        if (! $signature) {
            throw new Exception("La signature n'est pas au bon format");
        }

        $actes = $this->getDonneesFormulaire();
        $actes->setData('signature_link', "La signature a été recupérée");
        $actes->addFileFromData('signature', "signature.pk7", $signature);

        $this->getActionCreator()->addAction($this->id_e, $this->id_u, 'recu-iparapheur', "La signature a été récupérée depuis l'applet de signature");
        $this->notify('recu-iparapheur', $this->type, "La signature a été récupérée depuis l'applet de signature");

        $this->setLastMessage("La signature a été correctement récupérée");
        $this->redirect("/Document/detail?id_e=" . $this->id_e . "&id_d=" . $this->id_d . "&page=" . $this->page);
    }

    public function displayAPI()
    {
        throw new Exception("Cette fonctionnalité n'est pas disponible via l'API.");
    }

    public function display()
    {

        $this->libersignConnecteur = $this->getConnecteur('signature');

        if ($this->getDonneesFormulaire()->fieldExists("arrete")) {
            $acte_file_path = $this->getDonneesFormulaire()->getFilePath("arrete");
        } elseif ($this->getDonneesFormulaire()->fieldExists("document")) {
            //FIXME WTF ! => C'est vraiement pas beau !
            // Document à faire signer CDG85
            $acte_file_path = $this->getDonneesFormulaire()->getFilePath("document");
        } else {
            throw new Exception("arrete ou document non présent");
        }

        $sha1 = sha1_file($acte_file_path);

        $this->{'tab_included_files'} = array(array('id' => $this->id_d,  'sha1' => $sha1));

        $document_info = $this->getDocument()->getInfo($this->id_d);
        $this->{'info'} = $document_info;

        $type_name = $this->getDocumentTypeFactory()->getFluxDocumentType($this->type)->getName();
        $this->renderPage("Signature de l'acte - " .  $document_info['titre'] . " (" . $type_name . ")", __DIR__ . "/../template/SignatureLocale.php");
        return true;
    }
}
