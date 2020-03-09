<?php

class TypeDossierImportExport
{

    public const ID_TYPE_DOSSIER = 'id_type_dossier';
    public const RAW_DATA = 'raw_data';
    public const TIMESTAMP = 'timestamp';
    public const PASTELL_VERSION = 'pastell-version';

    private $typeDossierService;
    private $typeDossierSQL;
    private $manifestFactory;
    private $time_function;

    public function __construct(
        TypeDossierService $typeDossierService,
        TypeDossierSQL $typeDossierSQL,
        ManifestFactory $manifestFactory
    ) {
        $this->typeDossierService = $typeDossierService;
        $this->typeDossierSQL = $typeDossierSQL;
        $this->manifestFactory = $manifestFactory;
        $this->setTimeFunction(function () {
            return time();
        });
    }

    /**
     * @param callable $time_function
     */
    public function setTimeFunction(callable $time_function)
    {
        $this->time_function = $time_function;
    }

    /**
     * @param int $id_t
     * @return string
     * @throws UnrecoverableException
     */
    public function export(int $id_t): string
    {
        $raw_data = $this->typeDossierService->getRawData($id_t);

        $type_dossier_info = $this->typeDossierSQL->getInfo($id_t);

        $result[self::ID_TYPE_DOSSIER] = $type_dossier_info[self::ID_TYPE_DOSSIER];

        $result[self::PASTELL_VERSION] = $this->manifestFactory->getPastellManifest()->getVersion();
        $t_function = $this->time_function;
        $result[self::TIMESTAMP] = $t_function();
        $result[self::RAW_DATA] = $raw_data;

        return json_encode($result);
    }


    /**
     * @param string $filepath
     * @return array
     * @throws TypeDossierException
     * @throws UnrecoverableException
     */
    public function importFromFilePath(string $filepath): array
    {

        return $this->import(file_get_contents($filepath));
    }

    /**
     * @param $file_content
     * @return array
     * @throws TypeDossierException
     * @throws UnrecoverableException
     */
    public function import($file_content): array
    {
        if (! $file_content) {
            throw new UnrecoverableException("Aucun fichier n'a été présenté ou le fichier est vide");
        }

        $json_content = json_decode($file_content, true);
        if (! $json_content) {
            throw new UnrecoverableException("Le fichier présenté ne contient pas de json");
        }

        if (empty($json_content[self::RAW_DATA]) || empty($json_content[self::ID_TYPE_DOSSIER])) {
            throw new UnrecoverableException("Le fichier présenté ne semble pas contenir de données utilisables");
        }

        $typeDossier = $this->typeDossierService->getTypeDossierFromArray($json_content[self::RAW_DATA]);

        $id_type_dossier = $json_content[self::ID_TYPE_DOSSIER];

        $this->typeDossierService->checkTypeDossierId($id_type_dossier);

        $id_t = $this->typeDossierSQL->getByIdTypeDossier($id_type_dossier);
        $orig_id_type_dossier = $id_type_dossier;
        if ($id_t) {
            $i = 1;
            do {
                $id_type_dossier = $orig_id_type_dossier . "_" . $i++;
            } while ($this->typeDossierSQL->getByIdTypeDossier($id_type_dossier));
        }

        $typeDossier->id_type_dossier = $id_type_dossier;
        $id_t = $this->typeDossierSQL->edit(0, $typeDossier);

        try {
            $this->typeDossierService->save($id_t, $typeDossier);
        } catch (Exception $e) {
            throw new UnrecoverableException("Impossible de créer de type de dossier : " . $e->getMessage());
        }

        return [
            'id_t' => $id_t,
            self::ID_TYPE_DOSSIER => $id_type_dossier,
            'orig_id_type_dossier' => $orig_id_type_dossier,
            'timestamp' => $json_content[self::TIMESTAMP]
        ];
    }
}
