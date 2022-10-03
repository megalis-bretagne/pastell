<?php

use Pastell\Service\TokenGenerator;

class AdminControler extends Controler
{
    /** @return UtilisateurCreator */
    private function getUtilisateurCreator()
    {
        return $this->getInstance(UtilisateurCreator::class);
    }

    /** @return RoleDroit */
    private function getRoleDroit()
    {
        return $this->getInstance(RoleDroit::class);
    }

    /** @return RoleSQL */
    private function getRoleSQL()
    {
        return $this->getInstance(RoleSQL::class);
    }

    private function getUtilisateur(): UtilisateurSQL
    {
        return $this->getInstance(UtilisateurSQL::class);
    }

    /** @return RoleUtilisateur */
    private function getRoleUtilisateur()
    {
        return $this->getInstance(RoleUtilisateur::class);
    }

    /** @return EntiteCreator */
    private function getEntiteCreator()
    {
        return $this->getInstance(EntiteCreator::class);
    }

    public function createAdmin($login, $password, $email)
    {
        $this->fixDroit();
        $id_u = $this->getUtilisateurCreator()->create($login, $password, $password, $email);
        if (!$id_u) {
            $this->setLastError($this->getUtilisateurCreator()->getLastError());
            return false;
        }
        //Ajout de l'affectation du nom (reprise du login) pour avoir accès à la fiche de l'utilisateur depuis l'IHM
        $this->getUtilisateur()->setNomPrenom($id_u, $login, "");
        $this->getUtilisateur()->validMailAuto($id_u);
        $this->getUtilisateur()->setColBase($id_u, 0);
        $this->getRoleUtilisateur()->addRole($id_u, "admin", 0);
        return true;
    }

    public function fixDroit()
    {
        $this->getRoleSQL()->edit("admin", "Administrateur");

        foreach ($this->getRoleDroit()->getAllDroit() as $droit) {
            $this->getRoleSQL()->addDroit("admin", $droit);
        }
        $this->getEntiteCreator()->updateAllEntiteAncetre();
    }

    public function createOrUpdateAdmin(string $login, string $email): void
    {
        $pastellLogger = $this->getObjectInstancier()->getInstance(PastellLogger::class);
        $utilisateur = $this->getUtilisateur();
        $utilisateurInfo = $utilisateur->getInfoByLogin($login);
        if (! $utilisateurInfo) {
            $tokenGenerator = $this->getObjectInstancier()->getInstance(TokenGenerator::class);
            $pastellLogger->info("L'utilisateur {$login} n'existe pas.");
            $password = $tokenGenerator->generate();
            $createAdminResult = $this->createAdmin(
                $login,
                $password,
                $email
            );
            $pastellLogger->info("Mot de passe de l'administrateur : " . $password);
            if (! $createAdminResult) {
                $pastellLogger->error(
                    "Erreur lors de la création de l'utilisateur :  " . $this->getLastError()->getLastError()
                );
                return;
            }
            $pastellLogger->info("Création de l'utilisateur {$login} OK");
            return;
        }
        $pastellLogger->info("L'utilisateur {$login} existe déjà");
        $this->getUtilisateur()->setEmail($utilisateurInfo['id_u'], $email);
        $pastellLogger->info("Mise à jour de l'utilisateur $login.");
    }
}
