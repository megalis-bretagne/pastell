<?php

//use Pastell\Service\PasswordEntropy;
//use Pastell\Service\TokenGenerator;
use Pastell\Service\Utilisateur\UserCreationService;
use Pastell\Service\Utilisateur\UserUpdateService;
use Pastell\Service\Utilisateur\UtilisateurDeletionService;
use Pastell\Service\Utilisateur\UserTokenService;

class UtilisateurAPIController extends BaseAPIController
{
    public function __construct(
        private readonly UtilisateurSQL $utilisateur,
        private readonly UtilisateurListe $utilisateurListe,
        private readonly UserCreationService $userCreationService,
        private readonly UserUpdateService $userUpdateService,
        private readonly UtilisateurDeletionService $utilisateurDeletionService,
        private readonly UserTokenService $userTokenService,
        private readonly ApiAuthentication $apiAuthentication,
    ) {
    }

    /**
     * @param $id_u
     * @return array|bool|mixed
     * @throws NotFoundException
     */
    private function verifExists($id_u)
    {
        $infoUtilisateur = $this->utilisateur->getInfo($id_u);
        if (!$infoUtilisateur) {
            throw new NotFoundException("L'utilisateur n'existe pas : {id_u=$id_u}");
        }
        return $infoUtilisateur;
    }

    /**
     * @return array
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function get()
    {
        if ($this->getFromQueryArgs(0)) {
            if ($this->getFromQueryArgs(0) === 'token') {
                return $this->getUserToken();
            } else {
                return $this->detail();
            }
        }

        $id_e = $this->getFromRequest('id_e', 0);

        $this->checkDroit($id_e, "utilisateur:lecture");

        $listUtilisateur = $this->utilisateurListe->getAllUtilisateurSimple($id_e);
        $result = [];
        if ($listUtilisateur) {
            // Création d'un nouveau tableau pour ne retourner que les valeurs retenues
            foreach ($listUtilisateur as $id_u => $utilisateur) {
                $result[$id_u] = [
                    'id_u' => (string)$utilisateur['id_u'],
                    'login' => $utilisateur['login'],
                    'email' => $utilisateur['email'],
                    'active' => (bool)$utilisateur['is_enabled']
                ];
            }
        }
        return $result;
    }

    /**
     * @return array
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function detail()
    {
        $id_u = $this->getFromQueryArgs(0);

        $info = $this->getDetailInfoForAPI($id_u);

        return $info;
    }

    /**
     * @param $id_u
     * @return array
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    private function getDetailInfoForAPI($id_u)
    {
        $infoUtilisateur = $this->verifExists($id_u);
        $this->checkDroit($infoUtilisateur['id_e'], "utilisateur:lecture");

        $result = [];
        $result['id_u'] = (string)$infoUtilisateur['id_u'];
        $result['login'] = $infoUtilisateur['login'];
        $result['nom'] = $infoUtilisateur['nom'];
        $result['prenom'] = $infoUtilisateur['prenom'];
        $result['email'] = $infoUtilisateur['email'];
        $result['certificat'] = $infoUtilisateur['certificat'];
        $result['id_e'] = (string)$infoUtilisateur['id_e'];
        $result['active'] = (bool)$infoUtilisateur['is_enabled'];

        return $result;
    }


    /**
     * @throws ForbiddenException
     * @throws ConflictException
     * @throws UnrecoverableException
     * @throws NotFoundException
     */
    public function post(): array
    {
        if ($this->getFromQueryArgs(0) === 'token') {
            return $this->postUserToken();
        }
        $id_e = $this->getFromRequest('id_e', 0);
        $id_u = $this->getFromQueryArgs(0);

        if (
            $id_u !== false
            && $this->verifExists($id_u)
            && $this->checkDroit($this->utilisateur->getInfo($id_u)['id_e'], 'utilisateur:edition')
        ) {
            $action = $this->getFromQueryArgs(1);
            if ($action === 'activate') {
                $this->utilisateur->enable($id_u);
            } elseif ($action === 'deactivate') {
                if ($id_u == $this->getUtilisateurId()) {
                    throw new UnrecoverableException('Vous ne pouvez pas désactiver votre compte utilisateur.');
                }

                $this->utilisateur->disable($id_u);
            } else {
                throw new UnrecoverableException('Cette action n\'existe pas.');
            }
            return $this->detail();
        }

        $this->checkDroit($id_e, 'utilisateur:creation');

        $id_u = $this->userCreationService->create(
            $this->getFromRequest('login'),
            $this->getFromRequest('email'),
            $this->getFromRequest('prenom'),
            $this->getFromRequest('nom'),
            (int)$id_e,
            $this->getFromRequest('password', null),
            $this->getFileUploader()->getFileContent('certificat') ?: null,
        );
        return $this->getDetailInfoForAPI($id_u);
    }

    /**
     * @throws ForbiddenException
     * @throws ConflictException
     * @throws UnrecoverableException
     * @throws NotFoundException
     */
    public function patch(): array
    {
        $createUtilisateur = $this->getFromRequest('create');
        if ($createUtilisateur) {
            return $this->post();
        }

        $data = $this->getRequest();
        $data['id_u'] = $this->getFromQueryArgs(0);

        $infoUtilisateurExistant = $this->utilisateur->getUserFromData($data);

        $id_e = $this->getFromRequest('id_e', $infoUtilisateurExistant['id_e']);

        // Vérification des droits.
        $this->checkDroit($id_e, 'utilisateur:edition');

        // Modification de l'utilisateur chargé avec les infos passées par l'API
        foreach ($data as $key => $newValeur) {
            if (array_key_exists($key, $infoUtilisateurExistant)) {
                $infoUtilisateurExistant[$key] = $newValeur;
            }
        }

        $login = $infoUtilisateurExistant['login'];
        $password = $this->getFromRequest('password', null);
        $nom = $infoUtilisateurExistant['nom'];
        $prenom = $infoUtilisateurExistant['prenom'];
        $email = $infoUtilisateurExistant['email'];

        $certificat_content = $this->getFileUploader()->getFileContent('certificat');

        $id_u = $this->userUpdateService->update(
            $infoUtilisateurExistant['id_u'],
            $login,
            $email,
            $prenom,
            $nom,
            (int)$id_e,
            $password,
            $certificat_content ?: null
        );

        // Si le certificat n'est pas passé, il faut le supprimer de l'utilisateur
        // Faut-il garder ce comportement ou faire des webservices dédiés à la gestion des certificats (au moins la suppression) ?
        if (!$certificat_content && ! $this->getFromRequest('dont_delete_certificate_if_empty', false)) {
            $this->utilisateur->removeCertificat($infoUtilisateurExistant['id_u']);
        }

        $result = $this->getDetailInfoForAPI($id_u);
        $result['result'] = self::RESULT_OK;
        return $result;
    }

    /**
     * @return mixed
     * @throws Exception
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function delete()
    {
        if ($this->getFromQueryArgs(0) === 'token') {
            return $this->deleteUserToken();
        }

        $data['id_u'] = $this->getFromQueryArgs(0);
        $data['login'] = $this->getFromRequest('login');

        $infoUtilisateur = $this->utilisateur->getUserFromData($data);

        $this->checkDroit($infoUtilisateur['id_e'], "utilisateur:edition");

        $this->utilisateurDeletionService->delete($infoUtilisateur['id_u']);

        $result['result'] = self::RESULT_OK;
        return $result;
    }

    /**
     * @throws UnauthorizedException
     */
    private function getUserToken()
    {
        $id_u = $this->apiAuthentication->getUtilisateurId();
        return $this->userTokenService->getTokens($id_u);
    }

    /**
     * @throws UnrecoverableException
     * @throws UnauthorizedException
     */
    private function postUserToken(): array
    {
        $id_u = $this->apiAuthentication->getUtilisateurId();
        $name = $this->getFromRequest('nom') ?: null;
        $expiration = $this->getFromRequest('expiration') ?: null;

        if ($name === null) {
            throw new Exception("Le nom du token est obligatoire");
        }

        if ($expiration !== null) {
            $date = DateTime::createFromFormat('Y-m-d', $expiration);
            if (!$date || ($date->format('Y-m-d') !== $expiration)) {
                throw new Exception("La date d'expiration est fausse, format attendu : 2020-03-31");
            }
        }

        $token = $this->userTokenService->createToken($id_u, $name, $expiration);
        return [$token];
    }

    private function deleteUserToken()
    {
        $id_u = $this->apiAuthentication->getUtilisateurId();
        $tokenId = $this->getFromQueryArgs(1);
        $user = $this->userTokenService->getUser($tokenId);
        if ($user !== $id_u) {
            throw new Exception('Impossible de supprimer ce jeton');
        }
        $this->userTokenService->deleteToken($tokenId);
        return ["Le token $tokenId a été supprimé"];
    }
}
