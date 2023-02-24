<?php

abstract class CommonExtractionAction extends ActionExecutor
{
    public const ACTION_NAME_ASYNCHRONE = 'extraction-prepare';
    public const ACTION_NAME_SYNCHRONE = 'extraction';
    public const ACTION_NAME_ERROR = 'extraction-error';

    /**
     * @throws Exception
     */
    public function go()
    {
        $tmpFolder = new TmpFolder();

        $tmp_folder = $tmpFolder->create();

        try {
            $this->extract($tmp_folder);
        } catch (Exception $e) {
            $this->changeAction(self::ACTION_NAME_ERROR, $e->getMessage());
            $this->notify(self::ACTION_NAME_ERROR, $this->type, $e->getMessage());
            $tmpFolder->delete($tmp_folder);
            throw $e;
        }
        $tmpFolder->delete($tmp_folder);

        $message = "Extraction terminée";
        $this->addActionOK($message);
        $this->notify(self::ACTION_NAME_SYNCHRONE, $this->type, $message);

        return true;
    }

    abstract public function extract(string $tmp_folder);
}
