<?php

class AFIEntite
{

    public $verifie;
    public $id_e;
    public $siren;
    public $denomination;
    public $login_user;
    public $password_user;
    public $email_user;
    public $helios_generique;
    public $helios_automatique;
    public $actes_automatique;
    public $pdf_generique;
    public $cpp;
    public $doc_a_signer;
    public $helios_retour;
    public $pack_marche;
    public $login_admin;
    public $password_admin;
    public $email_admin;
    public $user_s2low;
    public $type_ip_pdf_generique;
    public $cpp_user;
    public $cpp_pass;
    public $login_parapheur_tech;
    public $password_parapheur_tech;
    public $type_parapheur;

    public function hydrate(array $csv_info) {
        $colonne = $this->getColonne();
        foreach($colonne as $num_colonne => $type_colonne){
            $this->$type_colonne = $csv_info[$num_colonne];
        }
    }

    public function getColonne()
    {
        return [
            0 => 'verifie',
            'id_e',
            'siren',
            'denomination',
            'helios_generique',
            'test_helios_generique',
            'helios_automatique',
            'test_helios_automatique',
            'actes_automatique',
            'test_actes_automatique',
            'pdf_generique',
            'test_pdfgenerique',
            'cpp',
            'test_cpp',
            'doc_a_signer',
            'test_doc_a_signer',
            'helios_retour',
            'test_helios_retour',
            'pack_marche',
            'test_pack_marche',
            'login_user',
            'password_user',
            'email_user',
            'login_admin',
            'password_admin',
            'email_admin',
            's2low_other',
            'user_s2low',
            'password_s2low',
            'certificat_s2low',
            'mdp_certificat_s2low',
            'uri_parapheur',
            'login_parapheur_tech',
            'password_parapheur_tech',
            'signataire_parapheur',
            'signataire_parapheur_mdp',
            'signataire_parapheur_email',
            'type_parapheur',
            's_type1',
            's_type2',
            's_type3',
            's_type4',
            's_type5',
            's_type6',
            's_type7',
            's_type8',
            's_type9',
            's_type10',
            's_type11',
            's_type12',
            'bureau_technique',
            'bureau_signataire',
            'cpp_user',
            'cpp_pass',
            'type_ip_pdf_generique'

        ];
    }
}
