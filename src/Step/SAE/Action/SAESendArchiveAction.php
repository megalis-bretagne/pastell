<?php

namespace Pastell\Step\SAE\Action;

use ConnecteurTypeActionExecutor;
use NotFoundException;
use Pastell\Step\SAE\Enum\SAEActionsEnum;
use Pastell\Step\SAE\Enum\SAEFieldsEnum;
use SAEConnecteur;
use UnrecoverableException;

final class SAESendArchiveAction extends ConnecteurTypeActionExecutor
{
    /**
     * @throws UnrecoverableException
     * @throws NotFoundException
     */
    public function go()
    {
        $bordereau = $this->getDonneesFormulaire()->getFileContent(SAEFieldsEnum::SAE_BORDEREAU->value);
        $archivePath = $this->getDonneesFormulaire()->getFilePath(SAEFieldsEnum::SAE_ARCHIVE->value);
        /** @var SAEConnecteur $sae */
        $sae = $this->getConnecteur('SAE');

        try {
            $sae->sendArchive($bordereau, $archivePath);
        } catch (\Exception $exception) {
            $message = $exception->getMessage() . " - L'envoi du bordereau a échoué : " . $sae->getLastError();
            $this->changeAction(SAEActionsEnum::SEND_ARCHIVE_ERROR->value, $message);
            $this->notify(SAEActionsEnum::SEND_ARCHIVE_ERROR->value, $this->type, $message);
            return false;
        }

        $this->addActionOK('Le document a été envoyé au SAE');
        $this->notify($this->action, $this->type, 'Le document a été envoyé au SAE');
        return true;
    }
}
