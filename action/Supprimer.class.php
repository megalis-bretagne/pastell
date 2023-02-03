<?php

use Pastell\Service\Document\DocumentDeletionService;

class Supprimer extends ActionExecutor
{
    /**
     * @throws NotFoundException
     */
    public function go()
    {
        $message = $this->objectInstancier->getInstance(DocumentDeletionService::class)->delete($this->id_d);
        $this->setLastMessage($message);
        $this->redirect("/Document/list?id_e={$this->id_e}&type={$this->type}");
        return true;
    }
}
