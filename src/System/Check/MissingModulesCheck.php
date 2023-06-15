<?php

namespace Pastell\System\Check;

use DocumentSQL;
use DocumentTypeFactory;
use Extensions;
use Pastell\System\CheckInterface;
use Pastell\System\HealthCheckItem;

class MissingModulesCheck implements CheckInterface
{
    /**
     * @var DocumentSQL
     */
    private $documentSQL;
    /**
     * @var DocumentTypeFactory
     */
    private $documentTypeFactory;
    /**
     * @var Extensions
     */
    private $extensions;

    public function __construct(
        DocumentSQL $documentSQL,
        DocumentTypeFactory $documentTypeFactory,
        Extensions $extensions
    ) {
        $this->documentSQL = $documentSQL;
        $this->documentTypeFactory = $documentTypeFactory;
        $this->extensions = $extensions;
    }

    public function check(): array
    {
        return [$this->checkMissingModules()];
    }

    private function checkMissingModules(): HealthCheckItem
    {
        $missingModules = $this->getMissingModules();
        $result = empty($missingModules) ? 'Aucun' : implode(', ', $missingModules);
        return (new HealthCheckItem('Type(s) de dossier manquant(s)', $result))
            ->setSuccess(empty($missingModules));
    }

    private function getMissingModules(): array
    {
        $result = [];
        $document_type_list = $this->documentSQL->getAllType();
        $module_list = $this->documentTypeFactory->clearRestrictedFlux($this->extensions->getAllModule());
        foreach ($document_type_list as $document_type) {
            if (empty($module_list[$document_type])) {
                $result[] = $document_type;
            }
        }
        return $result;
    }
}
