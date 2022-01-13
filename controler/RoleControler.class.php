<?php

use Pastell\Service\Droit\DroitService;

class RoleControler extends PastellControler
{
    public function _beforeAction()
    {
        parent::_beforeAction();
        $this->{'menu_gauche_template'} = "ConfigurationMenuGauche";
        $this->{'menu_gauche_select'} = "Role/index";
        $this->{'dont_display_breacrumbs'} = true;
    }

    public function indexAction()
    {
        $this->verifDroit(0, "role:lecture");
        $this->{'allRole'} = $this->getRoleSQL()->getAllRole();
        if ($this->hasDroit(0, "role:edition")) {
            $this->{'nouveau_bouton_url'} = array("Ajouter" => "Role/edition");
        }
        $this->{'page_title'} = "Rôles";
        $this->{'template_milieu'} = "RoleIndex";
        $this->renderDefault();
    }

    public function detailAction()
    {
        $this->verifDroit(0, "role:lecture");
        $this->{'role'} = $this->getGetInfo()->get('role');
        $this->{'role_edition'} = $this->hasDroit(0, "role:edition");
        $this->{'role_info'} = $this->getRoleSQL()->getInfo($this->{'role'});

        /** @var RoleDroit $roleDroit */
        $roleDroit = $this->getInstance(RoleDroit::class);

        $all_droit = $roleDroit->getAllDroit();
        $all_droit_sql = $this->getRoleSQL()->getDroit($all_droit, $this->{'role'});
        $this->{'all_droit_utilisateur'} = $this->getObjectInstancier()->getInstance(DroitService::class)->clearRestrictedDroit($all_droit_sql);

        $this->{'page_title'} = "Gestion du rôle {$this->{'role'}} et des droits associés";
        $this->{'template_milieu'} = "RoleDetail";
        $this->renderDefault();
    }

    public function editionAction()
    {
        $this->verifDroit(0, "role:edition");
        $role = $this->getGetInfo()->get('role');

        if ($role) {
            $this->{'page_title'} = "Modification du rôle $role ";
            $this->{'role_info'} = $this->getRoleSQL()->getInfo($role);
        } else {
            $this->{'page_title'} = "Ajout d'un rôle";
            $this->{'role_info'} = array('libelle' => '','role' => '');
        }
        $this->{'template_milieu'} = "RoleEdition";
        $this->renderDefault();
    }

    public function doEditionAction()
    {
        $this->verifDroit(0, "role:edition");
        $role = $this->getPostInfo()->get('role');
        $role = preg_replace("/\s+/", "_", $role);
        $libelle = $this->getPostInfo()->get('libelle');
        $this->getRoleSQL()->edit($role, $libelle);
        $this->redirect("/Role/detail?role=$role");
    }

    public function doDeleteAction()
    {
        $this->verifDroit(0, "role:edition");
        $role = $this->getPostInfo()->get('role');

        if ($this->getRoleUtilisateur()->anybodyHasRole($role)) {
            $this->setLastError("Le rôle $role est attribué à des utilisateurs");
            $this->redirect("/Role/detail?role=$role");
        }

        $this->getRoleSQL()->delete($role);
        $this->setLastMessage("Le rôle $role a été supprimé");
        $this->redirect("/Role/index");
    }

    public function doDetailAction()
    {
        $this->verifDroit(0, "role:edition");
        $role = $this->getPostInfo()->get('role');
        $droit = $this->getPostInfo()->get('droit', array());
        $this->getRoleSQL()->updateDroit($role, $droit);
        $this->setLastMessage("Le rôle $role a été mis à jour");
        $this->redirect("/Role/detail?role=$role");
    }
}
