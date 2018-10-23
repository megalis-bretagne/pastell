<?php

abstract class CommonExtractionAction extends ActionExecutor {

    const ACTION_NAME_ASYNCRHONE = 'extraction-prepare';
    const ACTION_NAME_SYNCRHONE = 'extraction';
    const ACTION_NAME_ERROR = 'extraction-error';

    /**
     * @throws Exception
     */
    public function go(){

        $tmpFolder = new TmpFolder();

        $tmp_folder = $tmpFolder->create();

        try {
            $this->goThrow($tmp_folder);
        } catch (Exception $e){
            $this->changeAction(self::ACTION_NAME_ERROR,$e->getMessage());
            $this->notify(self::ACTION_NAME_ERROR,$this->type,$e->getMessage());
            $tmpFolder->delete($tmp_folder);
            throw $e;
        }
        $tmpFolder->delete($tmp_folder);

        $message = "Extraction terminée";
        $this->addActionOK($message);
        $this->notify(self::ACTION_NAME_SYNCRHONE,$this->type,$message);
        return true;
    }

    abstract public function goThrow(string $tmp_folder);


}