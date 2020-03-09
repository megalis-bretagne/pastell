<?php

class EntiteAPIController extends BaseAPIController
{
    
    private $entiteSQL;

    private $siren;

    private $entiteCreator;


    public function __construct(
        EntiteSQL $entiteSQL,
        Siren $siren,
        EntiteCreator $entiteCreator
    ) {
        $this->entiteSQL = $entiteSQL;
        $this->siren = $siren;
        $this->entiteCreator = $entiteCreator;
    }

    public function get()
    {
        if ($this->getFromQueryArgs(0)) {
            return $this->getInfo($this->getFromQueryArgs(0));
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
        $resultFille = array();
        $entiteFille = $this->entiteSQL->getFille($id_e);
        if ($entiteFille) {
            //GDON : completer les TU pour passer dans la boucle.
            foreach ($entiteFille as $key => $valeur) {
                $resultFille[$key] = array('id_e' => $valeur['id_e']);
            }
        }

        // Construction du tableau resultat
        $result = array();
        $result['id_e'] = $infoEntite['id_e'];
        $result['denomination'] = $infoEntite['denomination'];
        $result['siren'] = $infoEntite['siren'];
        $result['type'] = $infoEntite['type'];
        $result['entite_mere'] = $infoEntite['entite_mere'];
        $result['entite_fille'] = $resultFille;
        $result['centre_de_gestion'] = $infoEntite['centre_de_gestion'];

        return $result;
    }

    public function post()
    {
        $entite_mere = $this->getFromRequest('entite_mere', 0);
        $type = $this->getFromRequest('type');
        $siren = $this->getFromRequest('siren');
        $denomination = $this->getFromRequest('denomination');
        $centre_de_gestion = $this->getFromRequest('centre_de_gestion', 0);
        $id_e = $this->edition(null, $denomination, $siren, $type, $entite_mere, $centre_de_gestion);
        return $this->getInfo($id_e);
    }


    public function delete()
    {
        $data['id_e'] = $this->getFromQueryArgs(0);
        $data['denomination'] = $this->getFromRequest('denomination');
        $infoEntiteExistante = $this->entiteSQL->getEntiteFromData($data);
        $id_e = $infoEntiteExistante['id_e'];

        $this->checkDroit($id_e, "entite:edition");

        $this->entiteSQL->removeEntite($id_e);

        $result['result'] = self::RESULT_OK;
        return $result;
    }

    public function patch()
    {

        $createEntite = $this->getFromRequest('create');

        if ($createEntite) {
            return $this->post();
        }

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
        $siren = $infoEntiteExistante['siren'];
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
        if (! in_array($type, array(Entite::TYPE_SERVICE,Entite::TYPE_CENTRE_DE_GESTION,Entite::TYPE_COLLECTIVITE))) {
            throw new Exception("Le type d'entité doit être renseigné. Les valeurs possibles sont collectivite, service ou centre_de_gestion.");
        }

        if ($type == Entite::TYPE_SERVICE && ! $entite_mere) {
            throw new Exception("Un service doit être ataché à une entité mère (collectivité, centre de gestion ou service)");
        }

        if ($type != Entite::TYPE_SERVICE) {
            if (! $siren) {
                throw new Exception("Le siren est obligatoire");
            }
            if (! $this->siren->isValid($siren)) {
                throw new Exception("Le siren « $siren » ne semble pas valide");
            }
        }

        $id_e = $this->entiteCreator->edit($id_e, $siren, $nom, $type, $entite_mere, $centre_de_gestion);
        return $id_e;
    }
}
