<?php

use Pastell\Service\LoginAttemptLimit;
use Symfony\Component\RateLimiter\Exception\RateLimitExceededException;

class ApiAuthentication
{
    /** @var SQLQuery */
    private $sqlQuery;

    //TODO inverser la dépendance...
    /** @var  ConnexionControler */
    private $connexionControler;

    private $loginAttemptLimit;

    public function __construct(
        ConnexionControler $connexionControler,
        SQLQuery $sqlQuery,
        LoginAttemptLimit $loginAttemptLimit
    ) {
        $this->connexionControler = $connexionControler;
        $this->sqlQuery = $sqlQuery;
        $this->loginAttemptLimit = $loginAttemptLimit;
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
        $recuperateur = new Recuperateur($_REQUEST);
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

        if (! $id_u && ! empty($_SERVER['PHP_AUTH_USER'])) {
            if (false === $this->loginAttemptLimit->isLoginAttemptAuthorized($_SERVER['PHP_AUTH_USER'])) {
                throw new RateLimitExceededException($this->loginAttemptLimit->getRateLimit($_SERVER['PHP_AUTH_USER']));
            }
            $id_u = $utilisateurListe->getUtilisateurByLogin($_SERVER['PHP_AUTH_USER']);
            if ($utilisateur->verifPassword($id_u, $_SERVER['PHP_AUTH_PW'])) {
                $this->loginAttemptLimit->resetLoginAttempt($_SERVER['PHP_AUTH_USER']);
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

        return $id_u;
    }
}
