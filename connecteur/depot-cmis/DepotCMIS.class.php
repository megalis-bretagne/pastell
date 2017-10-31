<?php

//Docker : https://hub.docker.com/r/gui81/alfresco/

//A tester : http://jeci.fr/blog/2017/0922-en-alfresco-docker-cloud-201707.html

class DepotCMIS extends DepotConnecteur {

    const DEPOT_CMIS_URL = 'depot_cmis_url';
    const DEPOT_CMIS_LOGIN = 'depot_cmis_login';
    const DEPOT_CMIS_PASSWORD = 'depot_cmis_password';
    const DEPOT_CMIS_DIRECTORY='depot_cmis_directory';

    /** @var \Dkd\PhpCmis\Data\FolderInterface */
    private $folder;

    /** @var  \Dkd\PhpCmis\Session */
    private $session;

    public function listDirectory() {
        $result = array();
        foreach($this->getFolder()->getChildren() as $children){
            $result[] = $children->getName();
        }
        return $result;
    }

    public function makeDirectory(string $directory_name) {
        $properties = [
            \Dkd\PhpCmis\PropertyIds::OBJECT_TYPE_ID => 'cmis:folder',
            \Dkd\PhpCmis\PropertyIds::NAME => $directory_name,

        ];
        $this->getFolder()->createFolder($properties);
        return $directory_name;
    }

    public function saveDocument(string $directory_name, string $filename, string $filepath) {
        $properties = [
            \Dkd\PhpCmis\PropertyIds::OBJECT_TYPE_ID => 'cmis:document',
            \Dkd\PhpCmis\PropertyIds::NAME => $filename,
        ];

        $versionningState = new \Dkd\PhpCmis\Enum\VersioningState(\Dkd\PhpCmis\Enum\VersioningState::MAJOR);

        $folder = $this->getFolder();
        if ($directory_name){
            $folder = $this->session->getObjectByPath(
                $this->connecteurConfig->get(self::DEPOT_CMIS_DIRECTORY)."/".$directory_name
            );
        }

        $folder->createDocument(
            $properties,
            \GuzzleHttp\Stream\Stream::factory(fopen($filepath , 'r')),
            $versionningState
        );

        return $directory_name."/".$filename;
    }

    private function itemExists(string $item_name) {
        return array_reduce($this->listDirectory(),
            function($carry,$item) use($item_name){
                $carry = $carry || trim($item,"/") == $item_name;
                return $carry;
            }
        );
    }

    public function directoryExists(string $directory_name) {
        return $this->itemExists($directory_name);
    }

    public function fileExists(string $filename) {
        return $this->itemExists($filename);
    }

    private function getFolder(){
        if ($this->folder){
            return $this->folder;
        }
        $httpInvoker = new \GuzzleHttp\Client();

        $httpInvoker->setDefaultOption(
            'auth',
            array(
                $this->connecteurConfig->get(self::DEPOT_CMIS_LOGIN),
                $this->connecteurConfig->get(self::DEPOT_CMIS_PASSWORD),
            )
        );

        $parameters = [
            \Dkd\PhpCmis\SessionParameter::BINDING_TYPE => \Dkd\PhpCmis\Enum\BindingType::BROWSER,
            \Dkd\PhpCmis\SessionParameter::BROWSER_URL => $this->connecteurConfig->get(self::DEPOT_CMIS_URL),
            \Dkd\PhpCmis\SessionParameter::BROWSER_SUCCINCT => false,
            \Dkd\PhpCmis\SessionParameter::HTTP_INVOKER_OBJECT => $httpInvoker
        ];
        $sessionFactory = new \Dkd\PhpCmis\SessionFactory();


        $repositories = $sessionFactory->getRepositories($parameters);
        $parameters[\Dkd\PhpCmis\SessionParameter::REPOSITORY_ID] = $repositories[0]->getId();

        $this->session = $sessionFactory->createSession($parameters);
        /** @var \Dkd\PhpCmis\Data\FolderInterface $folder */
        $this->folder = $this->session->getObjectByPath($this->connecteurConfig->get(self::DEPOT_CMIS_DIRECTORY));
        return $this->folder;
    }
}