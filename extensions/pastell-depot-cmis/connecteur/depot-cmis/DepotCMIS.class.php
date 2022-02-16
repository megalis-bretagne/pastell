<?php

//Docker : https://hub.docker.com/r/gui81/alfresco/

//A tester : http://jeci.fr/blog/2017/0922-en-alfresco-docker-cloud-201707.html

//composer update

use Dkd\PhpCmis\Data\FolderInterface;
use Dkd\PhpCmis\Enum\BindingType;
use Dkd\PhpCmis\Enum\VersioningState;
use Dkd\PhpCmis\OperationContext;
use Dkd\PhpCmis\PropertyIds;
use Dkd\PhpCmis\Session;
use Dkd\PhpCmis\SessionFactory;
use Dkd\PhpCmis\SessionParameter;
use GuzzleHttp\Client;
use GuzzleHttp\Stream\Stream;

class DepotCMIS extends DepotConnecteur
{
    public const DEPOT_CMIS_URL = 'depot_cmis_url';
    public const DEPOT_CMIS_LOGIN = 'depot_cmis_login';
    public const DEPOT_CMIS_PASSWORD = 'depot_cmis_password';
    public const DEPOT_CMIS_DIRECTORY = 'depot_cmis_directory';

    /** @var FolderInterface */
    private $folder;

    /** @var  Session */
    private $session;

    public function listDirectory()
    {
        $result = array();
        foreach ($this->getFolder()->getChildren() as $children) {
            $result[] = $children->getName();
        }
        return $result;
    }

    public function makeDirectory(string $directory_name)
    {
        $properties = [
            PropertyIds::OBJECT_TYPE_ID => 'cmis:folder',
            PropertyIds::NAME => $directory_name,

        ];
        $this->getFolder()->createFolder($properties);
        return $directory_name;
    }

    public function saveDocument(string $directory_name, string $filename, string $filepath)
    {
        $fileContentType = new FileContentType();
        $properties = [
            PropertyIds::OBJECT_TYPE_ID => 'cmis:document',
            PropertyIds::NAME => $filename,
            PropertyIds::CONTENT_STREAM_MIME_TYPE => $fileContentType->getContentType($filepath),
        ];

        $versionningState = new VersioningState(VersioningState::MAJOR);

        $folder = $this->getFolder();
        if ($directory_name) {
            $folder = $this->session->getObjectByPath(
                $this->connecteurConfig->get(self::DEPOT_CMIS_DIRECTORY) . "/" . $directory_name
            );
        }

        $document = $folder->createDocument(
            $properties,
            Stream::factory(fopen($filepath, 'r')),
            $versionningState,
            [],
            [],
            [],
            new OperationContext()
        );

        $this->addGedDocumentId($filename, $document->getId());

        return $directory_name . "/" . $filename;
    }

    private function itemExists(string $item_name)
    {
        return array_reduce(
            $this->listDirectory(),
            function ($carry, $item) use ($item_name) {
                $carry = $carry || basename($item) == $item_name;
                return $carry;
            }
        );
    }

    public function directoryExists(string $directory_name)
    {
        return $this->itemExists($directory_name);
    }

    public function fileExists(string $filename)
    {
        return $this->itemExists($filename);
    }

    private function getFolder()
    {

        if ($this->folder) {
            return $this->folder;
        }

        $httpInvoker = new Client();

        $httpInvoker->setDefaultOption(
            'auth',
            array(
                $this->connecteurConfig->get(self::DEPOT_CMIS_LOGIN),
                $this->connecteurConfig->get(self::DEPOT_CMIS_PASSWORD),
            )
        );

        $parameters = [
            SessionParameter::BINDING_TYPE => BindingType::BROWSER,
            SessionParameter::BROWSER_URL => $this->connecteurConfig->get(self::DEPOT_CMIS_URL),
            SessionParameter::BROWSER_SUCCINCT => false,
            SessionParameter::HTTP_INVOKER_OBJECT => $httpInvoker
        ];
        $sessionFactory = new SessionFactory();

        $repositories = $sessionFactory->getRepositories($parameters);
        $parameters[SessionParameter::REPOSITORY_ID] = $repositories[0]->getId();
        $this->session = $sessionFactory->createSession($parameters);
        /** @var FolderInterface $folder */
        $this->folder = $this->session->getObjectByPath($this->connecteurConfig->get(self::DEPOT_CMIS_DIRECTORY));
        return $this->folder;
    }

    /*
     * Only used for testing
     */
    public function getClient(): Client
    {
        return new Client();
    }
}
