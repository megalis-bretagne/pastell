<?php

use Pastell\Service\PasswordEntropy;
use Pastell\Service\Utilisateur\UtilisateurDeletionService;
use Pastell\Utilities\Certificate;

class UtilisateurAPIController extends BaseAPIController
{
    private $utilisateur;

    private $utilisateurListe;


    private $utilisateurCreator;

    private $roleUtilisateur;

    private $journal;

    private $utilisateurDeletionService;

    private $passwordEntropy;

    public function __construct(
        UtilisateurSQL $utilisateur,
        UtilisateurListe $utilisateurListe,
        UtilisateurCreator $utilisateurCreator,
        RoleUtilisateur $roleUtilisateur,
        Journal $journal,
        UtilisateurDeletionService $utilisateurDeletionService,
        PasswordEntropy $passwordEntropy,
        \Pastell\Service\TokenGenerator $tokenGenerator,
    ) {
        $this->utilisateur = $utilisateur;
        $this->utilisateurListe = $utilisateurListe;
        $this->utilisateurCreator = $utilisateurCreator;
        $this->roleUtilisateur = $roleUtilisateur;
        $this->journal = $journal;
        $this->utilisateurDeletionService = $utilisateurDeletionService;
        $this->passwordEntropy = $passwordEntropy;
        $this->tokenGenerator = $tokenGenerator;
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
            return $this->detail();
        }

        $id_e = $this->getFromRequest('id_e', 0);

        $this->checkDroit($id_e, "utilisateur:lecture");

        $listUtilisateur = $this->utilisateurListe->getAllUtilisateurSimple($id_e);
        $result = [];
        if ($listUtilisateur) {
            // Création d'un nouveau tableau pour ne retourner que les valeurs retenues
            foreach ($listUtilisateur as $id_u => $utilisateur) {
                $result[$id_u] = ['id_u' => $utilisateur['id_u'], 'login' => $utilisateur['login'], 'email' => $utilisateur['email']];
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
        $result['id_u'] = $infoUtilisateur['id_u'];
        $result['login'] = $infoUtilisateur['login'];
        $result['nom'] = $infoUtilisateur['nom'];
        $result['prenom'] = $infoUtilisateur['prenom'];
        $result['email'] = $infoUtilisateur['email'];
        $result['certificat'] = $infoUtilisateur['certificat'];
        $result['id_e'] = $infoUtilisateur['id_e'];

        return $result;
    }


    /**
     * @return array
     * @throws ConflictException
     * @throws Exception
     * @throws ForbiddenException
     */
    public function post()
    {
        $id_e = $this->getFromRequest('id_e', 0);
        $this->checkDroit($id_e, "utilisateur:edition");



        $id_u = $this->editionUtilisateur(
            $id_e,
            null,
            $this->getFromRequest('email'),
            $this->getFromRequest('login'),
            $this->getFromRequest('password') ?: $this->tokenGenerator->generate(),
            $this->getFromRequest('nom'),
            $this->getFromRequest('prenom'),
            $this->getFileUploader()->getFileContent('certificat')
        );
        return $this->getDetailInfoForAPI($id_u);
    }

    /**
     * @param $id_e
     * @param $id_u
     * @param $email
     * @param $login
     * @param $password
     * @param $nom
     * @param $prenom
     * @param $certificat_content
     * @return array|bool|mixed
     * @throws ConflictException
     * @throws Exception
     */
    private function editionUtilisateur($id_e, $id_u, $email, $login, $password, $nom, $prenom, $certificat_content)
    {
        if (! $nom) {
            throw new Exception("Le nom est obligatoire");
        }

        if (! $prenom) {
            throw new Exception("Le prénom est obligatoire");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Votre adresse email ne semble pas valide");
        }


        if ($certificat_content) {
            $certificat = new Certificate($certificat_content);
            if (! $certificat->isValid()) {
                throw new Exception("Le certificat ne semble pas être valide");
            }
        }
        $other_id_u = $this->utilisateur->getIdFromLogin($login);

        if ($id_u && $other_id_u && ($other_id_u != $id_u)) {
            throw new ConflictException("Un utilisateur avec le même login existe déjà.");
        }

        $is_creation = false;

        if (! $id_u) {
            $is_creation = true;
            $id_u = $this->utilisateurCreator->create($login, $password, $password, $email);
            if (! $id_u) {
                throw new Exception($this->utilisateurCreator->getLastError());
            }
        }
        if ($password) {
            if (! $this->passwordEntropy->isPasswordStrongEnough($password)) {
                throw new Exception(
                    "Le mot mot de passe n'est pas assez fort. " .
                    "(trop court ou pas assez de caractères différents)"
                );
            }
            $this->utilisateur->setPassword($id_u, $password);
        }
        $oldInfo = $this->utilisateur->getInfo($id_u);

        if (! empty($certificat)) {
            $this->utilisateur->setCertificat($id_u, $certificat);
        }

        $this->utilisateur->validMailAuto($id_u);
        $this->utilisateur->setNomPrenom($id_u, $nom, $prenom);
        $this->utilisateur->setEmail($id_u, $email);
        $this->utilisateur->setLogin($id_u, $login);
        $this->utilisateur->setColBase($id_u, $id_e);

        $allRole = $this->roleUtilisateur->getRole($id_u);
        if (! $allRole) {
            $this->roleUtilisateur->addRole($id_u, RoleUtilisateur::AUCUN_DROIT, $id_e);
        }

        $newInfo = $this->utilisateur->getInfo($id_u);

        $infoToRetrieve = ['email','login','nom','prenom'];
        $infoChanged = [];
        foreach ($infoToRetrieve as $key) {
            if ($oldInfo[$key] != $newInfo[$key]) {
                $infoChanged[] = "$key : {$oldInfo[$key]} -> {$newInfo[$key]}";
            }
        }
        $infoChanged  = implode("; ", $infoChanged);

        $mode  = $is_creation ? "Création" : "Modification";
        $action = $is_creation ? "Créé" : "Modifié";

        $this->journal->add(
            Journal::MODIFICATION_UTILISATEUR,
            $id_e,
            0,
            $action,
            "$mode de l'utilisateur $login ($id_u) : $infoChanged"
        );

        return $id_u;
    }

    /**
     * @return array
     * @throws ConflictException
     * @throws Exception
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function patch()
    {
        $createUtilisateur = $this->getFromRequest('create');
        if ($createUtilisateur) {
            return $this->post();
        }

        $data = $this->getRequest();
        $data['id_u'] = $this->getFromQueryArgs(0);

        $utilisateur = $this->utilisateur;

        $infoUtilisateurExistant = $this->utilisateur->getUserFromData($data);

        $id_e = $this->getFromRequest('id_e', $infoUtilisateurExistant["id_e"]);

        // Vérification des droits.
        $this->checkDroit($id_e, "utilisateur:edition");


        // Modification de l'utilisateur chargé avec les infos passées par l'API
        foreach ($data as $key => $newValeur) {
            if (array_key_exists($key, $infoUtilisateurExistant)) {
                $infoUtilisateurExistant[$key] = $newValeur;
            }
        }

        $login = $infoUtilisateurExistant['login'];
        $password = $infoUtilisateurExistant['password'];
        $nom = $infoUtilisateurExistant['nom'];
        $prenom = $infoUtilisateurExistant['prenom'];
        $email = $infoUtilisateurExistant['email'];


        $certificat_content = $this->getFileUploader()->getFileContent('certificat');

        // Appel du service métier pour enregistrer la modification de l'utilisateur
        $id_u = $this->editionUtilisateur($id_e, $infoUtilisateurExistant['id_u'], $email, $login, $password, $nom, $prenom, $certificat_content);

        // Si le certificat n'est pas passé, il faut le supprimer de l'utilisateur
        // Faut-il garder ce comportement ou faire des webservices dédiés à la gestion des certificats (au moins la suppression) ?
        if (!$certificat_content && ! $this->getFromRequest('dont_delete_certificate_if_empty', false)) {
            $utilisateur->removeCertificat($infoUtilisateurExistant['id_u']);
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
        $data['id_u'] = $this->getFromQueryArgs(0);
        $data['login'] = $this->getFromRequest('login');

        $infoUtilisateur = $this->utilisateur->getUserFromData($data);

        $this->checkDroit($infoUtilisateur['id_e'], "utilisateur:edition");

        $this->utilisateurDeletionService->delete($infoUtilisateur['id_u']);

        $result['result'] = self::RESULT_OK;
        return $result;
    }
}
