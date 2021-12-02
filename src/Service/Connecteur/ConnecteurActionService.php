<?php

namespace Pastell\Service\Connecteur;

use ConnecteurActionSQL;
use Exception;
use Pastell\Service\Connecteur\ConnecteurHashService;

class ConnecteurActionService
{
    public const ACTION_MODIFFIE = 'Modifié';
    public const ACTION_AJOUTE = 'Ajouté';
    public const ACTION_ASSOCIE = 'Associé';
    public const ACTION_DISSOCIE = 'Dissocié';

    /**
     * @var ConnecteurActionSQL
     */
    private $connecteurActionSQL;

    /**
     * @var ConnecteurHashService
     */
    private $connecteurHashService;

    public function __construct(
        ConnecteurActionSQL $connecteurActionSQL,
        ConnecteurHashService $connecteurHashService
    ) {
        $this->connecteurActionSQL = $connecteurActionSQL;
        $this->connecteurHashService = $connecteurHashService;
    }

    /**
     * @throws Exception
     */
    public function add(int $id_e, int $id_u, int $id_ce, string $type_dossier, string $action, string $message): ?int
    {
        $hash = in_array($action, [self::ACTION_AJOUTE, self::ACTION_MODIFFIE], true) ?
            $this->connecteurHashService->getHash($id_ce)
            : $this->getLastHash($id_ce);

        return $this->connecteurActionSQL->add(
            $id_e,
            $id_u,
            $id_ce,
            $type_dossier,
            $action,
            $hash,
            $message
        );
    }

    /**
     * @throws Exception
     */
    public function delete(int $id_ce): void
    {
        $this->connecteurActionSQL->delete($id_ce);
    }

    public function getByIdCe(int $id_ce, int $offset = 0, int $limit = ConnecteurActionSQL::DEFAULT_LIMIT): array
    {
        return $this->connecteurActionSQL->getByIdCe($id_ce, $offset, $limit);
    }

    public function countByIdCe(int $id_ce): int
    {
        return $this->connecteurActionSQL->countByIdCe($id_ce);
    }

    public function getLastHash(int $id_ce): string
    {
        return $this->getByIdCe($id_ce, 0, 1)[0]['empreinte_sha256'] ?? '';
    }
}
