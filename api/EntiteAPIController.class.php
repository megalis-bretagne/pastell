<?php

use Pastell\Service\Entite\EntiteDeletionService;

class EntiteAPIController extends BaseAPIController
{
    private $entiteSQL;

    private $siren;

    private $entiteCreator;

    /**
     * @var EntiteDeletionService
     */
    private $entiteDeletionService;

    public function __construct(
        EntiteSQL $entiteSQL,
        Siren $siren,
        EntiteCreator $entiteCreator,
        EntiteDeletionService $entiteDeletionService
    ) {
        $this->entiteSQL = $entiteSQL;
        $this->siren = $siren;
        $this->entiteCreator = $entiteCreator;
        $this->entiteDeletionService = $entiteDeletionService;
    }

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function get()
    {
        if ($this->getFromQueryArgs(0)) {
            return $this->getInfo($this->getFromQueryArgs(0));
        }
        $data['is_active'] = $this->getFromRequest('is_active', null);
        if ($data['is_active'] !== null) {
            return $this->entiteSQL->getEntiteFromData($data);
        }
        return $this->getRoleUtilisateur()->getAllEntiteWithFille($this->getUtilisateurId(), 'entite:lecture');
    }

    private function getInfo($id_e)
    {
        $infoEntite = $this->entiteSQL->getInfo($id_e);

        if (!$infoEntite) {
            throw new NotFoundException("L'entité $id_e n'a pas été trouvée");
        }
        $this->checkDroit($id_e, "entite:lecture");

        // Chargement des entités filles
        $resultFille = [];
        $entiteFille = $this->entiteSQL->getFille($id_e);
        if ($entiteFille) {
            //GDON : completer les TU pour passer dans la boucle.
            foreach ($entiteFille as $key => $valeur) {
                $resultFille[$key] = ['id_e' => $valeur['id_e']];
            }
        }

        // Construction du tableau resultat
        $result = [];
        $result['id_e'] = $infoEntite['id_e'];
        $result['denomination'] = $infoEntite['denomination'];
        $result['siren'] = $infoEntite['siren'];
        $result['type'] = $infoEntite['type'];
        $result['entite_mere'] = $infoEntite['entite_mere'];
        $result['entite_fille'] = $resultFille;
        $result['centre_de_gestion'] = $infoEntite['centre_de_gestion'];
        $result['is_active'] = (bool)$infoEntite['is_active'];

        return $result;
    }

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function post()
    {
        $id_e = $this->getFromQueryArgs(0);
        if ($id_e !== false) {
            $action = $this->getFromQueryArgs(1);
            if ($action == 'activate') {
                $this->entiteSQL->setActive($id_e, 1);
            } elseif ($action == 'desactivate') {
                $this->entiteSQL->setActive($id_e, 0);
            } else {
                throw new Exception('Cette action n\'existe pas.');
            }
            return $this->get();
        }
        $entite_mere = $this->getFromRequest('entite_mere', 0);
        $type = $this->getFromRequest('type');
        $siren = $this->getFromRequest('siren', "");
        $denomination = $this->getFromRequest('denomination');
        $centre_de_gestion = $this->getFromRequest('centre_de_gestion', 0);
        $id_e = $this->edition(null, $denomination, $siren, $type, $entite_mere, $centre_de_gestion);
        return $this->getInfo($id_e);
    }


    /**
     * @return mixed
     * @throws ForbiddenException
     * @throws Exception
     */
    public function delete()
    {
        $data['id_e'] = $this->getFromQueryArgs(0);
        $data['denomination'] = $this->getFromRequest('denomination');
        $infoEntiteExistante = $this->entiteSQL->getEntiteFromData($data);
        $id_e = $infoEntiteExistante['id_e'];

        $this->checkDroit($id_e, "entite:edition");

        $this->entiteDeletionService->delete($id_e);

        $result['result'] = self::RESULT_OK;
        return $result;
    }

    public function patch()
    {
        $data = $this->getRequest();
        $data['id_e'] = $this->getFromQueryArgs(0);

        $infoEntiteExistante = $this->entiteSQL->getEntiteFromData($data);

        $id_e = $infoEntiteExistante['id_e'];


        // Sauvegarde des valeurs. Si elles ne sont pas présentes dans $data, il faut les conserver.
        $entite_mere = $infoEntiteExistante['entite_mere'];
        $centre_de_gestion = $infoEntiteExistante['centre_de_gestion'];

        // Modification de l'entité chargée avec les infos passées par l'API
        foreach ($data as $key => $newValeur) {
            if (array_key_exists($key, $infoEntiteExistante)) {
                $infoEntiteExistante[$key] = $newValeur;
            }
        }

        $type = $infoEntiteExistante['type'];
        $siren = $infoEntiteExistante['siren'] ?? "";
        $denomination = $infoEntiteExistante['denomination'];
        if ($infoEntiteExistante['entite_mere']) {
            $entite_mere = $infoEntiteExistante['entite_mere'];
        }
        if ($infoEntiteExistante['centre_de_gestion']) {
            $centre_de_gestion = $infoEntiteExistante['centre_de_gestion'];
        }

        $this->edition($id_e, $denomination, $siren, $type, $entite_mere, $centre_de_gestion);

        $result = $this->getInfo($id_e);
        $result['result'] = self::RESULT_OK;
        return $result;
    }

    private function edition($id_e, $nom, $siren, $type, $entite_mere, $centre_de_gestion)
    {
        $this->checkDroit($entite_mere, "entite:edition");

        if ($id_e) {
            $this->checkDroit($id_e, "entite:edition");
        }

        if (!$nom) {
            throw new Exception("Le nom (denomination) est obligatoire");
        }
        if (! $type) {
            $type = EntiteSQL::TYPE_COLLECTIVITE;
        }
        if (! in_array($type, [EntiteSQL::TYPE_CENTRE_DE_GESTION,EntiteSQL::TYPE_COLLECTIVITE])) {
            throw new Exception("Le type d'entité doit être renseigné. Les valeurs possibles sont collectivite ou centre_de_gestion.");
        }

        if ($siren !== '' && ! $this->siren->isValid($siren)) {
            throw new Exception("Le siren « $siren » ne semble pas valide");
        }

        $id_e = $this->entiteCreator->edit($id_e, $siren, $nom, $type, $entite_mere, $centre_de_gestion);
        return $id_e;
    }
}
