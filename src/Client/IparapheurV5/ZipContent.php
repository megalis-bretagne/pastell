<?php

declare(strict_types=1);

namespace Pastell\Client\IparapheurV5;

use Pastell\Client\IparapheurV5\Model\Premis;
use Pastell\Client\IparapheurV5\Model\PreservationLevelValue;
use Pastell\Client\IparapheurV5\Model\Type;
use Pastell\Client\IparapheurV5\Model\ZipContentModel;
use SimpleXMLWrapper;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use UnrecoverableException;
use ZipArchive;

class ZipContent
{
    public const PREMIS_FILENAME = 'i_Parapheur_internal_premis.xml';

    /**
     * @throws UnrecoverableException
     */
    public function extract(string $zipPath, string $folderPath): ZipContentModel
    {
        $this->unzipArchive($zipPath, $folderPath);
        $premisFilepath = $this->getPremisFilePath($folderPath);
        if (! file_exists($premisFilepath)) {
            throw new UnrecoverableException("Le fichier PREMIS ne se trouve pas dans l'archive");
        }

        /** @var Premis $premis */
        $premis = $this->getSerializer()->deserialize(
            file_get_contents($premisFilepath),
            Premis::class,
            'xml'
        );

        $zipContentModel = new ZipContentModel();
        $zipContentModel->premisFile = self::PREMIS_FILENAME;

        foreach ($premis->object as $object) {
            if ($object->type === Type::intellectualEntity) {
                $zipContentModel->id = $object->objectIdentifier->objectIdentifierValue;
                $zipContentModel->name = $object->originalName;
            }
            if ($object->type === Type::file) {
                if ($object->preservationLevel->preservationLevelValue === PreservationLevelValue::mainDocument) {
                    $zipContentModel->documentPrincipaux[] = 'Documents principaux/' . $object->originalName;
                }
                if ($object->preservationLevel->preservationLevelValue === PreservationLevelValue::annex) {
                    $zipContentModel->annexe[] = 'Annexes/' . $object->originalName;
                }
            }
        }
        if (! isset($zipContentModel->name)) {
            throw new UnrecoverableException("Impossible de trouver le nom du dossier dans l'archive");
        }
        $zipContentModel->bordereau = $zipContentModel->name . "_bordereau.pdf";
        return $zipContentModel;
    }
    private function getSerializer(): Serializer
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader());

        $metadataAwareNameConverter = new MetadataAwareNameConverter($classMetadataFactory);

        $extractor = new PropertyInfoExtractor([], [
            new PhpDocExtractor(),
            new ReflectionExtractor(),
        ]);
        $normalizers = [
            new ArrayDenormalizer(),
            new DateTimeNormalizer(),
            new BackedEnumNormalizer(),
            new ObjectNormalizer(
                $classMetadataFactory,
                $metadataAwareNameConverter,
                null,
                $extractor
            ),
        ];



        return (new Serializer($normalizers, [new XmlEncoder()]));
    }

    private function getPremisFilePath(string $folderPath): string
    {
        return sprintf('%s/%s', $folderPath, self::PREMIS_FILENAME);
    }

    /**
     * @throws UnrecoverableException
     */
    private function unzipArchive($zip_file, $target_folder): void
    {
        $zip = new ZipArchive();
        $handle = $zip->open($zip_file);
        if (!$handle) {
            throw new UnrecoverableException("Impossible d'ouvrir le fichier zip");
        }
        $zip->extractTo($target_folder);
        $zip->close();
    }
}
