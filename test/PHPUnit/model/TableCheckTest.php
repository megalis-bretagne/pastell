<?php

class TableCheckTest extends PastellTestCase
{
    public function testGetTablesMarkedAsCrashed()
    {
        $tableCheck = $this->getObjectInstancier()->getInstance(TableCheck::class);
        $this->assertSame(
            [],
            $tableCheck->getTablesMarkedAsCrashed()
        );
    }
}
