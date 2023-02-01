<?php

declare(strict_types=1);

namespace Pastell\Tests\Service\Entite;

use EntiteSQL;
use Journal;
use Pastell\Service\Entite\EntityCreationService;
use Pastell\Validator\EntityValidator;
use PastellTestCase;
use UnrecoverableException;

class EntityCreationServiceTest extends PastellTestCase
{
    private function entityCreationService(): EntityCreationService
    {
        return new EntityCreationService(
            $this->getObjectInstancier()->getInstance(EntiteSQL::class),
            $this->getObjectInstancier()->getInstance(Journal::class),
            $this->getObjectInstancier()->getInstance(EntityValidator::class),
        );
    }

    /**
     * @throws UnrecoverableException
     */
    public function testCreate(): void
    {
        $entityName = 'My entity';
        $entityId = $this->entityCreationService()->create($entityName, '');

        $entity = $this->getObjectInstancier()->getInstance(EntiteSQL::class)->getInfo($entityId);

        static::assertSame($entityName, $entity['denomination']);
    }
}
