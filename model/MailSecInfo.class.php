<?php

class MailSecInfo
{
    public $id_de;
    public $key;
    public $id_d;
    public $email;
    public $type_destinataire;
    public $reponse;

    public $id_e;
    public $denomination_entite;

    public $type_document;

    public $flux_destinataire;

    /** @var DonneesFormulaire */
    public $donneesFormulaire;

    /** @var FieldData[] */
    public $fieldDataList;

    public $flux_reponse;
    public $has_flux_reponse;
    public $has_reponse;
    public $id_d_reponse;

    /** @var DonneesFormulaire */
    public $donneesFormulaireReponse;

    /** @var FieldData[] */
    public $fieldDataListReponse;
}
