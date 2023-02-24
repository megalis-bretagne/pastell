<?php

class RoleAPIController extends BaseAPIController
{
    public function get()
    {
        $this->checkOneDroit("role:lecture");
        return $this->getRoleUtilisateur()->getAuthorizedRoleToDelegate($this->getUtilisateurId());
    }
}
