<?php

namespace Pastell\System;

use ConnecteurFactory;
use DatabaseUpdate;
use DocumentSQL;
use DocumentTypeFactory;
use Extensions;
use FreeSpace;
use Journal;
use Pastell\Service\Crypto;
use SQLQuery;
use TableCheck;
use VerifEnvironnement;

class HealthCheck
{
    /** @var VerifEnvironnement */
    private $verifEnvironnement;
    /** @var FreeSpace */
    private $freeSpace;
    /** @var Journal */
    private $journal;
    /** @var SQLQuery */
    private $sqlQuery;
    /** @var TableCheck */
    private $tableCheck;
    /** @var ConnecteurFactory */
    private $connecteurFactory;
    /** @var DocumentSQL */
    private $documentSQL;
    /** @var DocumentTypeFactory */
    private $documentTypeFactory;
    /** @var Extensions */
    private $extensions;
    /** @var string */
    private $database_file;

    public function __construct(
        VerifEnvironnement $verifEnvironnement,
        FreeSpace $freeSpace,
        Journal $journal,
        SQLQuery $sqlQuery,
        TableCheck $tableCheck,
        ConnecteurFactory $connecteurFactory,
        DocumentSQL $documentSQL,
        DocumentTypeFactory $documentTypeFactory,
        Extensions $extensions,
        string $database_file
    ) {
        $this->verifEnvironnement = $verifEnvironnement;
        $this->freeSpace = $freeSpace;
        $this->journal = $journal;
        $this->sqlQuery = $sqlQuery;
        $this->tableCheck = $tableCheck;
        $this->connecteurFactory = $connecteurFactory;
        $this->documentSQL = $documentSQL;
        $this->documentTypeFactory = $documentTypeFactory;
        $this->extensions = $extensions;
        $this->database_file = $database_file;
    }

    /**
     * @return HealthCheckItem[]
     */
    public function checkPhpExtensions(): array
    {
        $phpExtensions = [];
        foreach ($this->verifEnvironnement->checkExtension() as $extension => $value) {
            $phpExtensions[] = (new HealthCheckItem($extension, $extension))->setSuccess($value);
        }
        return $phpExtensions;
    }

    /**
     * @return HealthCheckItem[]
     */
    public function checkWorkspace(): array
    {
        $spaceUsed = $this->freeSpace->getFreeSpace(WORKSPACE_PATH);
        return [
            (new HealthCheckItem(
                WORKSPACE_PATH . ' accessible en lecture/écriture ?',
                $this->verifEnvironnement->checkWorkspace() ? 'OK' : 'KO'
            ))->setSuccess($this->verifEnvironnement->checkWorkspace()),
            new HealthCheckItem(
                'Taille totale de la partition',
                $spaceUsed['disk_total_space']
            ),
            new HealthCheckItem(
                'Taille des données',
                $spaceUsed['disk_use_space']
            ),
            (new HealthCheckItem(
                "Taux d'occupation",
                $spaceUsed['disk_use_percent']
            ))->setSuccess(!$spaceUsed['disk_use_too_big'])
        ];
    }

    /**
     * @return HealthCheckItem[]
     */
    public function checkJournal(): array
    {
        $firstLineDate = round((time() - strtotime($this->journal->getFirstLineDate())) / 86400);
        return [
            new HealthCheckItem(
                "Nombre d'enregistrements dans la table journal",
                number_format_fr($this->journal->getNbLine())
            ),
            new HealthCheckItem(
                "Nombre d'enregistrements dans la table journal_historique",
                number_format_fr($this->journal->getNbLineHistorique())
            ),
            new HealthCheckItem(
                'Date du premier enregistrement de la table journal',
                $this->journal->getFirstLineDate()
            ),
            new HealthCheckItem("Nombre de mois de conservation du journal", JOURNAL_MAX_AGE_IN_MONTHS),
            (new HealthCheckItem(
                "Age du premier enregistrement de la table journal",
                $firstLineDate . ' jours'
            ))->setSuccess($firstLineDate <= JOURNAL_MAX_AGE_IN_MONTHS * 31),
        ];
    }

    /**
     * @return HealthCheckItem[]
     */
    public function checkRedis(): array
    {
        $status = $this->verifEnvironnement->checkRedis();
        if ($status) {
            $result = 'OK';
        } else {
            $result = 'KO ' . $this->verifEnvironnement->getLastError();
        }
        return [
            (new HealthCheckItem('Statut de redis', $result))->setSuccess($status),
            new HealthCheckItem(
                'Temps de mise en cache (définition des flux, des connecteurs, ...)',
                CACHE_TTL_IN_SECONDS . ' seconde(s)'
            )
        ];
    }

    /**
     * @return HealthCheckItem[]
     */
    public function checkPhpConfiguration(): array
    {
        $expectedData = [
            'memory_limit' => "512M",
            'post_max_size' => "200M",
            'upload_max_filesize' => "200M",
            'max_execution_time' => 600,
            'session.cookie_httponly' => 1,
            'session.cookie_secure' => 1,
            'session.use_only_cookies' => 1
        ];
        $ini = [];

        foreach ($expectedData as $key => $expectedValue) {
            $ini[] = (new HealthCheckItem(
                $key,
                ini_get($key),
                $expectedValue
            ))->setSuccess((int)ini_get($key) >= (int)$expectedValue);
        }

        return $ini;
    }

    /**
     * @return HealthCheckItem[]
     */
    public function checkExpectedElements(): array
    {
        if (function_exists('curl_version')) {
            $curlVersion = curl_version()['ssl_version'];
        } else {
            $curlVersion = "La fonction curl_version() n'existe pas !";
        }

        $array = [
            'PHP est en version 7.2' => [
                '#^7\.2#',
                $this->verifEnvironnement->checkPHP()['environnement_value']
            ],
            'OpenSSL est en version 1 ou plus ' => [
                "#^OpenSSL 1\.#",
                shell_exec(OPENSSL_PATH . ' version')
            ],
            'Curl est compilé avec OpenSSL' => [
                '#OpenSSL#',
                $curlVersion
            ],
            'La base de données est accédée en UTF-8' => [
                "#^utf8$#",
                $this->sqlQuery->getClientEncoding()
            ]
        ];

        $elements = [];
        foreach ($array as $key => $value) {
            $elements[] = (new HealthCheckItem($key, $value[1], $value[0]))
                ->setSuccess((bool)preg_match($value[0], $value[1]));
        }

        $elements[] = (new HealthCheckItem(
            'Libsodium est en version >=' . Crypto::LIBSODIUM_MINIMUM_VERSION_EXPECTED,
            SODIUM_LIBRARY_VERSION,
            ">= " . Crypto::LIBSODIUM_MINIMUM_VERSION_EXPECTED
        ))->setSuccess(version_compare(
            SODIUM_LIBRARY_VERSION,
            Crypto::LIBSODIUM_MINIMUM_VERSION_EXPECTED,
            '>='
        ));

        return $elements;
    }

    /**
     * @return HealthCheckItem[]
     */
    public function checkCommands(): array
    {
        $commands = [];
        foreach ($this->verifEnvironnement->checkCommande(['dot', 'xmlstarlet']) as $command => $path) {
            $commands[] = (new HealthCheckItem(
                $command,
                $path ?: "La commande n'est pas disponible"
            ))->setSuccess((bool)$path);
        }
        return $commands;
    }

    /**
     * @return HealthCheckItem[]
     */
    public function getConstants(): array
    {
        return [
            new HealthCheckItem('OPENSSL_PATH', OPENSSL_PATH),
            new HealthCheckItem('WORKSPACE_PATH', WORKSPACE_PATH),
        ];
    }

    public function checkDatabaseSchema(): HealthCheckItem
    {
        $databaseUpdate = new DatabaseUpdate(file_get_contents($this->database_file), $this->sqlQuery);
        $databaseSqlCommand = $databaseUpdate->getDiff();
        $databaseSchemaResult = "Le schéma de la base est conforme au schéma attendu par le code.";

        if ($databaseSqlCommand) {
            $databaseSchemaResult = "Le schéma de la base n'est pas conforme au schéma attendu par le code !" .
                implode(',', $databaseSqlCommand);
        }
        return (new HealthCheckItem('Schéma de la base de données', $databaseSchemaResult))
            ->setSuccess(!(bool)$databaseSqlCommand);
    }

    public function checkDatabaseEncoding(): HealthCheckItem
    {
        $tablesCollection = $this->sqlQuery->getTablesCollation();
        $databaseEncoding = "L'encodage de la base est conforme à l'encodage attendu.";
        $success = true;
        $details = [];
        if (count($tablesCollection) > 1) {
            $databaseEncoding = "Les tables n'utilisent pas toutes le même encodage !";
            $success = false;
            foreach ($tablesCollection as $encoding => $tableList) {
                $details[] = new HealthCheckItem($encoding, implode(', ', $tableList));
            }
        } elseif (array_keys($tablesCollection)[0] !== SQLQuery::PREFERRED_TABLE_COLLATION) {
            $databaseEncoding = sprintf(
                "L'encodage trouvé %s ne correspond pas à l'encodage prévu %s",
                array_keys($tablesCollection)[0],
                SQLQuery::PREFERRED_TABLE_COLLATION
            );
            $success = false;
        }

        return (new HealthCheckItem('Encodage de la base de données', $databaseEncoding))
            ->setSuccess($success)
            ->setDetails($details);
    }

    public function checkCrashedTables(): HealthCheckItem
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

    public function checkMissingConnectors(): HealthCheckItem
    {
        $missingConnectors = array_keys($this->connecteurFactory->getManquant());
        $result = empty($missingConnectors) ? 'aucun' : implode(', ', $missingConnectors);
        return (new HealthCheckItem('Connecteur(s) manquant(s)', $result))
            ->setSuccess(empty($missingConnectors));
    }

    public function checkMissingModules(): HealthCheckItem
    {
        $missingModules = $this->getMissingModules();
        $result = empty($missingModules) ? 'aucun' : implode(', ', $missingModules);
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
