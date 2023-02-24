<?php

class TableCheck extends SQL
{
    public function getTablesMarkedAsCrashed(): array
    {
        $sql = "SHOW TABLE STATUS WHERE comment != ''";
        return $this->query($sql) ?: [];
    }
}
