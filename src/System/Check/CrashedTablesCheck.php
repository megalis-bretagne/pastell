<?php

namespace Pastell\System\Check;

use Pastell\System\CheckInterface;
use Pastell\System\HealthCheckItem;
use TableCheck;

class CrashedTablesCheck implements CheckInterface
{
    /**
     * @var TableCheck
     */
    private $tableCheck;

    public function __construct(TableCheck $tableCheck)
    {
        $this->tableCheck = $tableCheck;
    }

    public function check(): array
    {
        return [$this->checkCrashedTables()];
    }

    private function checkCrashedTables(): HealthCheckItem
    {
        $crashedTable = $this->tableCheck->getTablesMarkedAsCrashed();
        $crashedTableResult = 'Aucune';
        $success = true;
        $details = [];
        if (!empty($crashedTable)) {
            $crashedTableResult = '';
            $success = false;
            foreach ($crashedTable as $table) {
                $details[] = new HealthCheckItem($table['Name'], $table['Comment']);
            }
        }

        return (new HealthCheckItem('Table(s) crashée(s)', $crashedTableResult))
            ->setSuccess($success)
            ->setDetails($details);
    }
}
