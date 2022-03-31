<?php

class DatabaseDefinition
{
    private $sqlQuery;

    public function __construct(SQLQuery $sqlQuery)
    {
        $this->sqlQuery = $sqlQuery;
    }

    public function getDefinition()
    {
        $result = [];
        $tables = $this->sqlQuery->query('SHOW TABLE STATUS');
        foreach ($tables as $table) {
            $tableName = $table['Name'];
            $result[$tableName] =  [
                'Engine' => $table['Engine'],
                'Column' => $this->getColumnDefinition($tableName),
                'Index' =>  $this->getIndexDefinition($tableName),
            ];
        }

        return $result;
    }

    private function getColumnDefinition($tableName)
    {
        $r = [];
        $result = $this->sqlQuery->query("SHOW COLUMNS FROM $tableName");
        foreach ($result as $line) {
            $r[$line['Field']] = $line;
        }
        return $r;
    }

    private function getIndexDefinition($tableName)
    {
        $result = [];
        $r = $this->sqlQuery->query("SHOW INDEX FROM $tableName");
        foreach ($r as $line) {
            if (empty($result[$line['Key_name']])) {
                $result[$line['Key_name']] = [
                'type' => $line['Index_type'],
                                            'col' => [],
                                            'unique' => ! $line['Non_unique']
                ];
            }
            $result[$line['Key_name']]['col'][$line['Seq_in_index'] - 1] = $line['Column_name'];
        }

        return $result;
    }
}
