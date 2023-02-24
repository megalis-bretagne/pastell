<?php

class SedaNGValidationTest extends ActionExecutor
{
    public function go()
    {

        /** @var SedaNG $sedaNG */
        $sedaNG = $this->getMyConnecteur();

        $bordereau =  $sedaNG->getBordereauTest();

        try {
            $sedaNG->validateBordereau($bordereau);
        } catch (SchemaNotFoundException $e) {
            throw $e;
        } catch (Exception $e) {
            $message = $e->getMessage() . "<br/><br/>";
            foreach ($sedaNG->getLastValidationError() as $erreur) {
                $message .= $erreur->message . "<br/>";
            }
            //print_r($last_validation_error);
            $this->setLastMessage($message);
            return false;
        }


        $this->setLastMessage("Le bordereau généré est valide");
        return true;
    }
}
