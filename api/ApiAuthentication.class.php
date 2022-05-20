<?php

use Pastell\Service\LoginAttemptLimit;
use Symfony\Component\RateLimiter\Exception\RateLimitExceededException;

class ApiAuthentication
{
    private array $server = [];
    private array $request = [];

    public function __construct(
        private ConnexionControler $connexionControler,
        private SQLQuery $sqlQuery,
        private LoginAttemptLimit $loginAttemptLimit,
        private UtilisateurSQL $utilisateurSQL,
    ) {
    }

    public function setServerInfo(array $server): void
    {
        $this->server = $server;
    }

    public function setRequestInfo(array $request): void
    {
        $this->request = $request;
    }

    /**
     * @return array|bool|mixed
     * @throws UnauthorizedException
     */
    public function getUtilisateurId()
    {
        try {
            $id_u = $this->getUtilisateurIdThrow();
        } catch (RateLimitExceededException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnauthorizedException($e->getMessage());
        }
        return $id_u;
    }

    /**
     * @return array|bool|mixed
     * @throws Exception
     */
    private function getUtilisateurIdThrow()
    {
        $recuperateur = new Recuperateur($this->request);
        $auth = $recuperateur->get("auth");

        $id_u = false;

        if ($auth === 'cas') {
            $id_u = $this->connexionControler->apiExternalConnexion(null, false);
        }

        $certificatConnexion = new CertificatConnexion($this->sqlQuery);
        $utilisateur = new UtilisateurSQL($this->sqlQuery);
        $utilisateurListe = new UtilisateurListe($this->sqlQuery);

        if (!$id_u) {
            $id_u = $certificatConnexion->autoConnect();
        }

        if (! $id_u && ! empty($this->server['PHP_AUTH_USER'])) {
            if (false === $this->loginAttemptLimit->isLoginAttemptAuthorized($this->server['PHP_AUTH_USER'])) {
                throw new RateLimitExceededException($this->loginAttemptLimit->getRateLimit($this->server['PHP_AUTH_USER']));
            }
            $id_u = $utilisateurListe->getUtilisateurByLogin($this->server['PHP_AUTH_USER']);
            if ($utilisateur->verifPassword($id_u, $this->server['PHP_AUTH_PW'])) {
                $this->loginAttemptLimit->resetLoginAttempt($this->server['PHP_AUTH_USER']);
            } else {
                $id_u = false;
            }
            if (! $certificatConnexion->connexionGranted($id_u)) {
                $id_u = false;
            }
        }

        if (! $id_u) {
            throw new Exception("Accès interdit");
        }
        if (! $this->utilisateurSQL->isEnabled($id_u)) {
            throw new UnauthorizedException('Votre compte a été désactivé');
        }
        return $id_u;
    }
}
