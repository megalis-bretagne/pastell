<?php

declare(strict_types=1);

class ProofBackendTest extends SQL implements ProofBackend
{
    public function __construct(
        SQLQuery $sqlQuery,
    ) {
        parent::__construct($sqlQuery);
    }

    public function write($id, $content): void
    {
        $sql = "UPDATE journal SET preuve = ? WHERE id_j = ?";
        $this->query($sql, $content, $id);
    }

    public function read($id)
    {
        $sql = "SELECT preuve FROM journal WHERE id_j = ?";
        return $this->query($sql, $id)[0]['preuve'];
    }
}
