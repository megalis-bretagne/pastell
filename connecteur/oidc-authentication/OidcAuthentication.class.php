<?php

use Jumbojett\OpenIDConnectClient;
use Jumbojett\OpenIDConnectClientException;

class OidcAuthentication extends AuthenticationConnecteur
{
    private $providerUrl;
    private $clientId;
    private $clientSecret;
    private $loginAttribute;
    private $givenNameAttribute;
    private $familyNameAttribute;
    private $emailAttribute;
    private $userCreation;

    /** @var OpenIDConnectClient */
    private $oidc;

    /**
     * @var string
     */
    private $redirectUrl;

    public function __construct(
        private readonly UtilisateurSQL $utilisateurSQL,
        private readonly RoleUtilisateur $roleUtilisateur,
    ) {
    }

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire)
    {
        $this->providerUrl = $donneesFormulaire->get('provider_url');
        $this->clientId = $donneesFormulaire->get('client_id');
        $this->clientSecret = $donneesFormulaire->get('client_secret');
        $this->loginAttribute = $donneesFormulaire->get('login_attribute');
        $this->givenNameAttribute = $donneesFormulaire->get('given_name_attribute');
        $this->familyNameAttribute = $donneesFormulaire->get('family_name_attribute');
        $this->emailAttribute = $donneesFormulaire->get('email_attribute');
        $this->redirectUrl = $donneesFormulaire->get('redirect_url', '');
        $this->userCreation = $donneesFormulaire->get('user_creation');
        $this->oidc = new OpenIDConnectClient(
            $this->providerUrl,
            $this->clientId,
            $this->clientSecret
        );

        if ($donneesFormulaire->get('http_proxy')) {
            $this->oidc->setHttpProxy($donneesFormulaire->get('http_proxy'));
        }
    }

    /**
     * @param $redirectUrl
     * @return mixed
     * @throws UnrecoverableException
     */
    public function authenticate($redirectUrl = false)
    {
        $userInfo = $this->getConnectedUserInfo($redirectUrl);
        if (empty($userInfo[$this->loginAttribute])) {
            throw new UnrecoverableException(sprintf(
                "L'attribut %s utilisé pour le login n'a pas été trouvé " .
                "sur la réponse du serveur d'authentification OpenID connect",
                $this->loginAttribute
            ));
        }
        if (empty($userInfo[$this->loginAttribute])) {
            throw new UnrecoverableException("Le champs login n'a pas été trouvé dans la réponse OpenID Connect");
        }
        if ($this->userCreation) {
            $this->createUserIfNeeded($userInfo);
        }
        return $userInfo[$this->loginAttribute];
    }

    /**
     * @throws OpenIDConnectClientException
     * @throws JsonException
     */
    public function getConnectedUserInfo($redirectUrl = false)
    {
        if ($redirectUrl) {
            $this->oidc->setRedirectURL($redirectUrl);
        }

        $this->oidc->authenticate();
        return json_decode(
            json_encode($this->oidc->requestUserInfo(), JSON_THROW_ON_ERROR),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    /**
     * @throws UnrecoverableException
     * @throws Exception
     */
    private function createUserIfNeeded(array $userInfo): void
    {
        $login = $userInfo[$this->loginAttribute] ;
        if ($this->utilisateurSQL->getIdFromLogin($login) !== false) {
            return;
        }
        $email = $userInfo[$this->emailAttribute] ?? '';
        $id_u = $this->utilisateurSQL->create($login, random_int(1, mt_getrandmax()), $email, '');
        $this->utilisateurSQL->setNomPrenom(
            $id_u,
            $userInfo[$this->familyNameAttribute] ?? '',
            $userInfo[$this->givenNameAttribute] ?? ''
        );
        $this->utilisateurSQL->validMailAuto($id_u);
        $this->roleUtilisateur->addRole($id_u, RoleUtilisateur::AUCUN_DROIT, 0);
    }

    public function logout($redirectUrl = false)
    {
        $this->oidc->signOut($this->oidc->getIdToken(), $redirectUrl ?: SITE_BASE);
    }

    public function getExternalSystemName(): string
    {
        return 'OIDC';
    }

    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }
}
