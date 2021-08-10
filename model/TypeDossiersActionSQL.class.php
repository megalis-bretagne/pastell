<?php

class TypeDossiersActionSQL extends SQL
{

    public function add(int $id_u, int $id_t, string $action, string $message, string $empreinte_sha256): int
    {
        $now = date(Date::DATE_ISO);

        $sql = "INSERT INTO type_dossier_action(id_u, id_t, action, message, date, empreinte_sha256) VALUES (?,?,?,?,?,?)";
        $this->query($sql, $id_u, $id_t, $action, $message, $now, $empreinte_sha256);
        return $this->lastInsertId();
    }

    public function delete(int $id_t): void
    {
        $sql = "DELETE FROM type_dossier_action WHERE id_t=?";
        $this->query($sql, $id_t);
    }
}
