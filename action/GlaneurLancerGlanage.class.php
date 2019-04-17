<?php

class GlaneurLancerGlanage extends ActionExecutor {

    /**
     * @return bool
     * @throws Exception
     */
    public function go(){
        /** @var GlaneurLocal $connecteur */
        $connecteur = $this->getMyConnecteur();

        try{
            $connecteur->glaner();
            $this->setLastMessage(implode("<br/>",$connecteur->getLastMessage()));
        } catch (UnrecoverableException $e){
        	$jobQueue = $this->objectInstancier->getInstance(JobQueueSQL::class);

        	$id_job  = $jobQueue->getJobIdForConnecteur($this->id_ce,'go');


        	if ($id_job) {
				$jobQueue->lock($id_job);
			}
			$message = $e->getMessage();
            $this->setLastMessage($message);
            mail(
                ADMIN_EMAIL,
                "[Pastell] Le traitement du glaneur passe à 'NON'",
                "Le glaneur ".SITE_BASE."Connecteur/edition?id_ce=".$this->id_ce." est en erreur."."\n".$message
            );
            return false;
        } catch (Exception $e){
            $this->setLastMessage("Erreur lors de l'importation : ".$e->getMessage()."<br />\n");
            return false;
        }

        return true;
    }

}