<?php

class DatabaseUpdate
{
    private $fileContent;
    private $databaseDefinition = [];
    /** @var DatabaseDiff */
    private $databaseDiff;

    public function __construct($fileContent, SQLQuery $sqlQuery = null)
    {
        $this->fileContent = json_decode($fileContent, true);
        if (!$this->fileContent) {
            $this->fileContent = [];
        }
        $databaseEvent = new DatabaseEventMySQL();
        $this->databaseDiff = new DatabaseDiff($databaseEvent);

        if ($sqlQuery) {
            $databaseDefinition = new DatabaseDefinition($sqlQuery);
            $this->databaseDefinition = $databaseDefinition->getDefinition();
        }
    }

    public function writeDefinition($binaryFilePath, $sqlCommandFilePath)
    {
        file_put_contents($binaryFilePath, $this->getDatabaseDefinition());
        file_put_contents($sqlCommandFilePath, implode("\n", $this->getAllSQLCommand()));
    }

    public function getAllSQLCommand()
    {
        return $this->databaseDiff->getDiff($this->databaseDefinition, []);
    }

    /**
     * @param SQLQuery $sqlQuery
     * @param Closure $function_log
     * @throws Exception
     */
    public function majDatabase(SQLQuery $sqlQuery, Closure $function_log)
    {
        foreach ($this->getDiff() as $sql) {
            $function_log($sql);
            $sqlQuery->query($sql);
        }
    }

    public function getDiff()
    {
        return $this->databaseDiff->getDiff($this->fileContent, $this->databaseDefinition);
    }

    public function getDatabaseDefinition()
    {
        return json_encode($this->databaseDefinition);
    }

    public function setDatabaseDefinition(array $databaseDefinition)
    {
        $this->databaseDefinition = $databaseDefinition;
    }
}
