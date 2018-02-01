<?php

class CreationPesAllerRecupAuto extends ActionExecutor {

    public function go(){
        /** @var CreationPesAller $connecteur */
        $connecteur = $this->getMyConnecteur();

        try{
            $result = $connecteur->recupAllAuto($this->id_e);
            if ($result){
                $this->setLastMessage(implode("<br/>",$result));
            } else {
                $this->setLastMessage("Aucun fichier trouvé");
            }
        } catch (UnrecoverableException $e){
            $this->setModeAuto(0);
            $message = "Erreur lors de l'importation : ".$e->getMessage()."<br />\n"."La récupération automatique passe à 'non'";
            $this->setLastMessage($message);

            mail(
                ADMIN_EMAIL,
                "[Pastell] La récupération automatique du glaneur passe à 'non'",
                "Le glaneur ".SITE_BASE."Connecteur/edition?id_ce=".$this->id_ce." est en erreur."."\n".$message
            );
            return false;
        } catch (Exception $e){
            $this->setLastMessage("Erreur lors de l'importation : ".$e->getMessage()."<br />\n");
            return false;
        }
        return true;
    }

    private function setModeAuto($mode_auto) {
        $connecteur_properties = $this->getConnecteurProperties();
        $connecteur_properties->setData('connecteur_auto',$mode_auto);
    }

}