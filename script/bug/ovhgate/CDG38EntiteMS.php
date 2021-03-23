<?php

class CDG38EntiteMS
{
    public $entite_fille;
    public $login_user;
    public $password_user;
    public $mail_user;
    public $nom_user;
    public $prenom_user;
    public $role_user;
    public $adresse_expe;
    public $reception_mail;

    public function hydrate(array $csv_info) {
        $colonne = $this->getColonne();
        foreach($colonne as $num_colonne => $type_colonne){
            $this->$type_colonne = $csv_info[$num_colonne];
        }
    }

    public function getColonne()
    {
        return [
            0 => 'type',
            'denomination',
            'siren',
            'entite_fille',
            'id_e',
            'id_admin',
            'password_admin',
            'mail_admin',
            'nom_admin',
            'prenom_admin',
            'role',
            'login_user',
            'password_user',
            'mail_user',
            'nom_user',
            'prenom_user',
            'role_user',
            'adresse_expe',
            'reception_mail',
        ];
    }
}
