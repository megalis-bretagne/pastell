<?php

declare(strict_types=1);

namespace Pastell\Seda\Message;

use DonneesFormulaire;
use DonneesFormulaireException;
use FileContentType;
use FluxData;
use GenerateurSedaFillFiles;
use Pastell\Service\SimpleTwigRenderer;
use SedaGenerique;
use SimpleXMLWrapperException;
use TmpFolder;
use UnrecoverableException;
use ZipArchive;

class SedaMessageBuilder
{
    private ?string $zipDirectory = null;
    /** @var callable $idGeneratorFunction */
    private $idGeneratorFunction;

    private DonneesFormulaire $donneesFormulaire;
    private FluxData $fluxData;

    public function __construct(
        private readonly TmpFolder $tmpFolder,
    ) {
        $this->setIdGeneratorFunction(fn() => 'id_' . \uuid_create(\UUID_TYPE_RANDOM));
    }

    public function __destruct()
    {
        if ($this->zipDirectory !== null) {
            $this->tmpFolder->delete($this->zipDirectory);
        }
    }

    public function setIdGeneratorFunction(callable $idGeneratorFunction): void
    {
        $this->idGeneratorFunction = $idGeneratorFunction;
    }

    public function getDonneesFormulaire(): DonneesFormulaire
    {
        if (!isset($this->donneesFormulaire)) {
            throw new \RuntimeException('DonneesFormulaire is not set');
        }
        return $this->donneesFormulaire;
    }


    public function setDonneesFormulaire(DonneesFormulaire $donneesFormulaire): self
    {
        $this->donneesFormulaire = $donneesFormulaire;
        return $this;
    }

    public function getFluxData(): FluxData
    {
        if (!isset($this->fluxData)) {
            throw new \RuntimeException('FluxData is not set');
        }
        return $this->fluxData;
    }

    public function setFluxData(FluxData $fluxData): self
    {
        $this->fluxData = $fluxData;
        return $this;
    }

    /**
     * @throws UnrecoverableException
     */
    private function getInputDataElement(array $dataFileContent): array
    {
        $data = [];
        foreach (SedaGenerique::getPastellToSeda() as $pastellId => $elementInfo) {
            if (empty($dataFileContent[$pastellId])) {
                continue;
            }
            $elementIdList = \explode('.', $elementInfo['seda']);
            $theData = &$data;
            foreach ($elementIdList as $i => $element_id) {
                if ($i < \count($elementIdList) - 1) {
                    if (!isset($theData[$element_id])) {
                        $theData[$element_id] = [];
                    }
                    $theData = &$theData[$element_id];
                } else {
                    $theData[$element_id] = $this->getStringWithMetatadaReplacement(
                        $dataFileContent[$pastellId]
                    );
                }
            }
        }
        return $data;
    }

    /**
     * @throws UnrecoverableException
     */
    private function getInputDataKeywords(string $keywordsData): array
    {
        $result = [];
        $keywordsData = $this->getStringWithMetatadaReplacement($keywordsData);
        $keywords = \explode("\n", $keywordsData);
        foreach ($keywords as $keywordLine) {
            $sedaKeywords = [];
            $keywordLine = \trim($keywordLine);
            if (!$keywordLine) {
                continue;
            }
            $keywordProperties = \str_getcsv($keywordLine);
            $sedaKeywords['KeywordContent'] = $keywordProperties[0];
            if (!empty($keywordProperties[1])) {
                $sedaKeywords['KeywordReference'] = $keywordProperties[1];
            }
            if (!empty($keywordProperties[2])) {
                $sedaKeywords['KeywordType'] = $keywordProperties[2];
            }
            $result[] = $sedaKeywords;
        }
        return $result;
    }

    /**
     * @throws DonneesFormulaireException
     * @throws SimpleXMLWrapperException
     * @throws UnrecoverableException
     */
    private function getInputDataFiles(string $dataFromFiles): array
    {
        $sedaGeneriqueFillFiles = new GenerateurSedaFillFiles($dataFromFiles);
        $result = [];
        $result[] = $this->getArchiveUnitDefinition($sedaGeneriqueFillFiles);
        return $result;
    }

    /**
     * @throws UnrecoverableException
     */
    private function getSedaInfoFromSpecificInfo(array $specificInfo): array
    {
        $sedaArchiveUnits = [];
        if (!empty($specificInfo['Description'])) {
            $sedaArchiveUnits['ContentDescription']['Description'] = $this->getStringWithMetatadaReplacement(
                $specificInfo['Description']
            );
        }
        if (!empty($specificInfo['DescriptionLevel'])) {
            $sedaArchiveUnits['ContentDescription']['DescriptionLevel'] = $this->getStringWithMetatadaReplacement(
                $specificInfo['DescriptionLevel']
            );
        }
        if (!empty($specificInfo['Language'])) {
            $sedaArchiveUnits['ContentDescription']['Language'] = $this->getStringWithMetatadaReplacement(
                $specificInfo['Language']
            );
        }
        if (!empty($specificInfo['CustodialHistory'])) {
            $sedaArchiveUnits['ContentDescription']['CustodialHistory'] = $this->getStringWithMetatadaReplacement(
                $specificInfo['CustodialHistory']
            );
        }
        if (!empty($specificInfo['AccessRestrictionRule_AccessRule'])) {
            $sedaArchiveUnits['AccessRestrictionRule']['AccessRule'] = $this->getStringWithMetatadaReplacement(
                $specificInfo['AccessRestrictionRule_AccessRule']
            );
        }
        if (!empty($specificInfo['AccessRestrictionRule_StartDate'])) {
            $sedaArchiveUnits['AccessRestrictionRule']['StartDate'] = $this->getStringWithMetatadaReplacement(
                $specificInfo['AccessRestrictionRule_StartDate']
            );
        }
        if (
            empty($sedaArchiveUnits['AccessRestrictionRule']['AccessRule']) &&
            empty($sedaArchiveUnits['AccessRestrictionRule']['StartDate'])
        ) {
            unset($sedaArchiveUnits['AccessRestrictionRule']);
        }
        if (!empty($specificInfo['ArchiveUnit_AppraisalRule_FinalAction'])) {
            $sedaArchiveUnits['AppraisalRule']['FinalAction'] = $this->getStringWithMetatadaReplacement(
                $specificInfo['ArchiveUnit_AppraisalRule_FinalAction']
            );
        }
        if (!empty($specificInfo['ArchiveUnit_AppraisalRule_Rule'])) {
            $sedaArchiveUnits['AppraisalRule']['Rule'] = $this->getStringWithMetatadaReplacement(
                $specificInfo['ArchiveUnit_AppraisalRule_Rule']
            );
        }
        if (!empty($specificInfo['ArchiveUnit_AppraisalRule_StartDate'])) {
            $sedaArchiveUnits['AppraisalRule']['StartDate'] = $this->getStringWithMetatadaReplacement(
                $specificInfo['ArchiveUnit_AppraisalRule_StartDate']
            );
        }
        if (
            empty($sedaArchiveUnits['AppraisalRule']['FinalAction']) &&
            empty($sedaArchiveUnits['AppraisalRule']['Rule']) &&
            empty($sedaArchiveUnits['AppraisalRule']['StartDate'])
        ) {
            unset($sedaArchiveUnits['AppraisalRule']);
        }
        if (!empty($specificInfo['Keywords'])) {
            $sedaArchiveUnits['ContentDescription']['Keywords'] = $this->getInputDataKeywords(
                $specificInfo['Keywords']
            );
        }
        return $sedaArchiveUnits;
    }

    /**
     * @throws UnrecoverableException
     */
    private function getSpecificInfo(GenerateurSedaFillFiles $sedaGeneriqueFilleFiles, string $nodeId): array
    {
        $specificInfo = $this->getSpecificInfoDefinition($sedaGeneriqueFilleFiles, $nodeId);
        if (!$specificInfo) {
            return $specificInfo;
        }
        $specificInfo = $this->getSedaInfoFromSpecificInfoWithLocalDescription($specificInfo, '', true);
        return $this->getSedaInfoFromSpecificInfo(
            $specificInfo
        );
    }

    /**
     * @throws UnrecoverableException
     */
    private function getSpecificInfoDefinition(GenerateurSedaFillFiles $sedaGeneriqueFilleFiles, string $nodeId): array
    {
        $sedaArchiveUnits = [];
        if (!$nodeId) {
            return $sedaArchiveUnits;
        }
        return $sedaGeneriqueFilleFiles->getArchiveUnitSpecificInfo($nodeId);
    }

    /**
     * @throws DonneesFormulaireException
     * @throws UnrecoverableException
     */
    private function getArchiveUnitDefinition(
        GenerateurSedaFillFiles $sedaGeneriqueFillFiles,
        string $parentId = '',
    ): array {
        $sedaArchiveUnits = [];
        $sedaArchiveUnits['Id'] = ($this->idGeneratorFunction)();
        if ($parentId) {
            $sedaArchiveUnits['Title'] = $this->getStringWithMetatadaReplacement(
                $sedaGeneriqueFillFiles->getDescription($parentId)
            );
        }

        $sedaArchiveUnits = \array_merge(
            $sedaArchiveUnits,
            $this->getSpecificInfo($sedaGeneriqueFillFiles, $parentId)
        );

        foreach ($sedaGeneriqueFillFiles->getFiles($parentId) as $files) {
            $field = $this->getStringWithMetatadaReplacement((string)$files['field_expression']);
            if (\preg_match('/#ZIP#/', $field)) {
                $archiveFromZip = $this->getArchiveUnitFromZip(
                    (string)$files['description'],
                    $field,
                    0,
                    $this->getSpecificInfoDefinition($sedaGeneriqueFillFiles, $parentId),
                    (!empty($files['do_not_put_mime_type']))
                );
                $sedaArchiveUnits['ArchiveUnits'] = \array_merge(
                    $sedaArchiveUnits['ArchiveUnits'] ?? [],
                    $archiveFromZip['ArchiveUnits'] ?? []
                );
                $sedaArchiveUnits['Files'] = \array_merge(
                    $sedaArchiveUnits['Files'] ?? [],
                    $archiveFromZip['Files'] ?? []
                );
                continue;
            }

            if (!\is_array($this->getDonneesFormulaire()->get($field))) {
                continue;
            }
            foreach ($this->getDonneesFormulaire()->get($field) as $filenum => $filename) {
                $file_unit = [];
                $file_unit['Filename'] = $filename;
                $file_unit['MessageDigest'] = $this->getDonneesFormulaire()->getFileDigest($field, $filenum);
                $file_unit['Size'] = (string)$this->getDonneesFormulaire()->getFileSize($field, $filenum);
                if (empty($files['do_not_put_mime_type'])) {
                    $file_unit['MimeType'] = $this->getDonneesFormulaire()->getContentType($field, $filenum);
                }
                $description = (string)$files['description'];
                $description = \preg_replace('/#FILE_NUM#/', (string)$filenum, $description);
                $file_unit['Title'] = $this->getStringWithMetatadaReplacement($description);
                $sedaArchiveUnits['Files'][($this->idGeneratorFunction)()] = $file_unit;
                $this->getFluxData()->setFileList(
                    $files['field_expression'],
                    $filename,
                    $this->getDonneesFormulaire()->getFilePath($field, $filenum)
                );
            }
        }
        foreach ($sedaGeneriqueFillFiles->getArchiveUnit($parentId) as $archiveUnit) {
            if ((string)$archiveUnit['field_expression']) {
                $field_expression_result = $this->getStringWithMetatadaReplacement(
                    (string)$archiveUnit['field_expression']
                );
                if (!$field_expression_result) {
                    continue;
                }
            }

            $sedaArchiveUnits['ArchiveUnits'][($this->idGeneratorFunction)()] = $this->getArchiveUnitDefinition(
                $sedaGeneriqueFillFiles,
                (string)$archiveUnit['id']
            );
        }
        return $sedaArchiveUnits;
    }

    /**
     * @throws UnrecoverableException
     * @throws SimpleXMLWrapperException
     * @throws DonneesFormulaireException
     */
    public function getInputData(string $dataFromBordereau, string $dataFromFiles): array
    {
        $dataFileContent = \json_decode($dataFromBordereau, true);
        if (!$dataFileContent) {
            $dataFileContent = [];
        }
        $data = $this->getInputDataElement($dataFileContent);
        $data['Keywords'] = $this->getInputDataKeywords($dataFileContent['keywords'] ?? '');
        $inputDataFiles = $this->getInputDataFiles($dataFromFiles);
        $data['ArchiveUnits'] = $inputDataFiles[0]['ArchiveUnits'] ?? [];
        $data['Files'] = $inputDataFiles[0]['Files'] ?? [];

        if (!empty($dataFileContent['archiveunits_title'])) {
            $data['Description'] = $this->getStringWithMetatadaReplacement($dataFileContent['archiveunits_title']);
        }

        $appraisailRuleFinalAction = [
            '1.0' => [
                'Conserver' => 'conserver',
                'Détruire' => 'detruire',
            ],
            '2.1' => [
                'Conserver' => 'Keep',
                'Détruire' => 'Destroy',
            ],
        ];
        if (!empty($data['AppraisalRule']['FinalAction'])) {
            $data['AppraisalRule']['FinalAction'] = $appraisailRuleFinalAction[$data['version'] ?? '2.1'][$data['AppraisalRule']['FinalAction']];
        }
        if (empty($data['StartDate'])) {
            unset($data['StartDate']);
        }
        if (empty($data['EndDate'])) {
            unset($data['EndDate']);
        }
        return $data;
    }

    /**
     * @throws UnrecoverableException
     */
    private function getStringWithMetatadaReplacement(string $expression): string
    {
        $simpleTwigRenderer = new SimpleTwigRenderer();
        return $simpleTwigRenderer->render(
            $expression,
            $this->getDonneesFormulaire()
        );
    }

    /**
     * @throws UnrecoverableException
     * @throws \Exception
     */
    private function getArchiveUnitFromZip(
        string $description,
        string $field_expression,
        int $filenum = 0,
        array $specific_info = [],
        bool $do_not_put_mime_type = false
    ): array {
        $field = \preg_replace('/#ZIP#/', '', $field_expression);

        $zipFilePath = $this->getDonneesFormulaire()->getFilePath($field, $filenum);
        if (!$zipFilePath) {
            return [];
        }

        $this->zipDirectory = $this->tmpFolder->create();

        $zip = new ZipArchive();
        $handle = $zip->open($zipFilePath);
        if (!$handle) {
            throw new UnrecoverableException("Impossible d'ouvrir le fichier zip");
        }
        $zip->extractTo($this->zipDirectory);
        $zip->close();
        return $this->getArchiveUnitFromFolder(
            $description,
            $this->zipDirectory,
            $field,
            $this->zipDirectory,
            $specific_info,
            $do_not_put_mime_type
        );
    }

    /**
     * @throws UnrecoverableException
     */
    private function getArchiveUnitFromFolder(
        string $description,
        string $folder,
        string $field,
        string $rootFolder,
        array $specificInfo,
        bool $doNotPutMimeType = false
    ): array {
        $localDescription = $this->getLocalDescription(
            $description,
            $this->getRelativePath($rootFolder, $folder),
            true
        );

        $result['Id'] = ($this->idGeneratorFunction)();
        $result['Title'] = $this->getStringWithMetatadaReplacement($localDescription);

        $result = \array_merge(
            $result,
            $this->getSedaInfoFromSpecificInfo(
                $this->getSedaInfoFromSpecificInfoWithLocalDescription(
                    $specificInfo,
                    $this->getRelativePath($rootFolder, $folder),
                    true
                )
            )
        );

        $directoryContent = \array_diff(\scandir($folder), $this->excludeFileList());

        foreach ($directoryContent as $file_or_folder) {
            $filepath = $folder . '/' . $file_or_folder;
            if (\is_dir($filepath)) {
                $result['ArchiveUnits'][($this->idGeneratorFunction)()] = $this->getArchiveUnitFromFolder(
                    $description,
                    $filepath,
                    $field,
                    $rootFolder,
                    $specificInfo,
                    $doNotPutMimeType
                );
            } elseif (\is_file($filepath)) {
                $relativePath = $this->getRelativePath($rootFolder, $filepath);
                $fileUnit = [];
                $fileUnit['Filename'] = $relativePath;
                $fileUnit['MessageDigest'] = \hash_file('sha256', $filepath);
                $fileUnit['Size'] = \filesize($filepath);
                $fileContentType = new FileContentType();
                if (!$doNotPutMimeType) {
                    $fileUnit['MimeType'] = $fileContentType->getContentType($filepath);
                }

                $localDescription = $this->getLocalDescription($description, $relativePath, false);
                $fileUnit['Title'] = $this->getStringWithMetatadaReplacement($localDescription);
                $result['Files'][($this->idGeneratorFunction)()] = $fileUnit;
                $this->getFluxData()->setFileList(
                    $field,
                    $relativePath,
                    $filepath
                );
            }
        }
        return $result;
    }

    private function getSedaInfoFromSpecificInfoWithLocalDescription(
        array $specificInfo,
        string $filepath,
        bool $isDirectory
    ): array {
        $result = [];
        foreach ($specificInfo as $id => $expression) {
            $result[$id] = $this->getLocalDescription($expression, $filepath, $isDirectory);
        }
        return $result;
    }

    private function getRelativePath(string $rootFolder, string $localFolder): string
    {
        $relativePath = \preg_replace("#$rootFolder#", '', $localFolder);
        return \ltrim($relativePath, '/');
    }

    private function getLocalDescription(string $description, string $filepath, bool $isDirectory): string
    {
        $localDescription = \preg_replace('/#FILEPATH#/', $filepath, $description);
        $localDescription = \preg_replace('/#FILENAME#/', \basename($filepath), $localDescription);
        $localDescription = \preg_replace('/#IS_DIR#/', $isDirectory ? 'true' : 'false', $localDescription);
        return \preg_replace('/#IS_FILE#/', $isDirectory ? 'false' : 'true', $localDescription);
    }

    private function excludeFileList(): array
    {
        return ['.', '..', '__MACOSX', '.DS_Store', '.gitkeep'];
    }
}
