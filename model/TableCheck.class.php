<?php

class TableCheck extends SQL
{

    public function getTablesMarkedAsCrashed(): array
    {
        $tables_list = [];
        $sql = "SHOW TABLES";
        $col = $this->queryOneCol($sql);
        $all_table = implode(',', $col);

        $sql = "CHECK table $all_table";
        $all_error = $this->query($sql);
        foreach ($all_error as $error) {
            if ($error['Msg_type'] == 'error') {
                $tables_list[$error['Table']] = true ;
            }
        }
        return array_keys($tables_list);
    }
}
