<?php

class FournisseurCommandeEnvoiMail extends ActionExecutor
{
    public function go()
    {
        $mailsec = $this->getConnecteur('mailsec');
        $this->getDonneesFormulaire()->setData('has_message', true);
        $this->getDonneesFormulaire()->setData('objet', $this->getDonneesFormulaire()->get('libelle'));
        $this->getDonneesFormulaire()->setData('to', $this->getDonneesFormulaire()->get('mail_fournisseur'));
        $this->getDonneesFormulaire()->setData('message', "Veuillez trouver ci-joint le bon de commande.");
        $commande_file_name = $this->getDonneesFormulaire()->getFileName('commande');
        $this->getDonneesFormulaire()->addFileFromCopy(
            'document_attache',
            $commande_file_name,
            $this->getDonneesFormulaire()->getFilePath('commande')
        );


        $this->documentEmail = $this->objectInstancier->getInstance(DocumentEmail::class);
        $this->documentEmail->add($this->id_d, $this->getDonneesFormulaire()->get('mail_fournisseur'), 'to');

        $mailsec->sendAllMail($this->id_e, $this->id_d);

        $this->addActionOK("Le document a été envoyé au fournisseur");
        return true;
    }
}
