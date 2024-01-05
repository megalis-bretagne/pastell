<?php

declare(strict_types=1);

use Symfony\Component\Serializer\Exception\ExceptionInterface;

class DeskNameAction extends DictionnaryChoice
{
    public function getElementId(): string
    {
        return 'desk_id';
    }

    public function getElementName(): string
    {
        return 'desk_name';
    }

    public function getTitle(): string
    {
        return 'Sélectionner un bureau';
    }

    /**
     * @throws ExceptionInterface
     * @throws \Http\Client\Exception
     * @throws Exception
     */
    public function displayAPI(): array
    {
        /** @var RecupFinParapheur $recupFinParapheur */
        $recupFinParapheur = $this->getMyConnecteur();
        try {
            return $recupFinParapheur->getAllDesks();
        } catch (Exception) {
            throw new \RuntimeException('Erreur dans la récupération des bureaux');
        }
    }
}
