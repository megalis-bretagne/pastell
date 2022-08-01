<?php

class CPPWrapperConfig
{
    /** @deprecated V3.1.0 - utiliser authentification PISTE */
    public $url;
    /** @deprecated V3.1.0 - utiliser authentification PISTE */
    public $certificat_pem;
    /** @deprecated V3.1.0 - utiliser authentification PISTE */
    public $certificat_prikey_pem;
    /** @deprecated V3.1.0 - utiliser authentification PISTE */
    public $certificat_password;
    /** @deprecated V3.1.0 - utiliser authentification PISTE */
    public $certificate_chain;
    /** @deprecated V3.1.0 - utiliser authentification PISTE*/
    /** @var  bool */
    public $is_raccordement_certificat = false;

    /** @var  string */
    public $url_piste_get_token;
    /** @var  string */
    public $client_id;
    /** @var  string */
    public $client_secret;
    /** @var  string */
    public $url_piste_api;
    /** @var  string */
    public $cpro_account;

    public $proxy;

    public $user_login;
    public $user_password;

    /** @var  string */
    public $user_role;

    public $identifiant_structure_cpp;
    public $service_destinataire;

    /** @var ?bool $fetchDownloadedInvoices */
    public $fetchDownloadedInvoices;
}
