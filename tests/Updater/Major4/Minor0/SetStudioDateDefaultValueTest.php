<?php

declare(strict_types=1);

namespace Pastell\Tests\Updater\Major4\Minor0;

use Exception;
use Pastell\Configuration\ElementType;
use Pastell\Updater\Major4\Minor0\SetStudioDateDefaultValue;
use PastellTestCase;
use TypeDossierException;
use TypeDossierFormulaireElementProperties;
use TypeDossierLoader;
use TypeDossierService;

class SetStudioDateDefaultValueTest extends PastellTestCase
{
    /**
     * @throws TypeDossierException
     * @throws Exception
     */
    public function testUpdate(): void
    {
        $this->getObjectInstancier()->getInstance(TypeDossierLoader::class)
            ->createTypeDossierDefinitionFile('sae-only');

        /** @var TypeDossierFormulaireElementProperties[] $dateFields */
        $dateFields = $this->getObjectInstancier()->getInstance(TypeDossierService::class)->getFieldWithType(
            1,
            ElementType::DATE->value
        );

        static::assertFalse($dateFields['date']->default_value);

        $this->getObjectInstancier()->getInstance(SetStudioDateDefaultValue::class)->update();
        /** @var TypeDossierFormulaireElementProperties[] $dateFields */
        $dateFields = $this->getObjectInstancier()->getInstance(TypeDossierService::class)->getFieldWithType(
            1,
            ElementType::DATE->value
        );

        static::assertSame('1', $dateFields['date']->default_value);
    }
}
