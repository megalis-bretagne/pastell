<?php

require_once(__DIR__ . "/lib/smbclient.php");

class ServerMessageBlock extends GEDConnecteur {

    private $mount_point;
    private $login;
    private $password;
    private $directory;

    /** @var  smbclient */
    private $smbclient;

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire) {
        $this->mount_point = $donneesFormulaire->get('mount_point');
        $this->login = $donneesFormulaire->get('user');
        $this->password = $donneesFormulaire->get('password');
        $this->directory = $donneesFormulaire->get('directory');
        $this->smbclient = new smbclient ($this->mount_point, $this->login, $this->password);
    }

    public function getRootFolder(){
        return $this->directory;
    }

    private function checkSmbCommandResult(){
        if ($this->smbclient->get_last_cmd_exit_code() != 0){
            throw new Exception("Erreur SMB : ". implode(",",$this->smbclient->get_last_cmd_stderr()));
        }
    }

    public function listFolder($folder){
        $result = array();
        $folder_list = $this->smbclient->dir($folder);
        $this->checkSmbCommandResult();
        foreach($folder_list as $folder_info){
            $result[] = $folder_info['filename'];
        }
        return $result;
    }

    public function createFolder(
        /** @noinspection PhpUnusedParameterInspection */ $folder_name,
                                                          $title,
        /** @noinspection PhpUnusedParameterInspection */ $description
    ){

        $result = $this->smbclient->mkdir($this->directory."/".$title);
        $this->checkSmbCommandResult();
        return $result;
    }

    public function addDocument(
        $title,
        /** @noinspection PhpUnusedParameterInspection */ $description,
        /** @noinspection PhpUnusedParameterInspection */ $contentType,
        $content,
        $gedFolder
    ){
        $localfile = sys_get_temp_dir()."/$title";
        file_put_contents($localfile,$content);
        $result = $this->smbclient->safe_put($localfile,$gedFolder."/".$title);
        if (! $result){
            throw new Exception("Erreur SMB : ". implode(",",$this->smbclient->get_last_cmd_stderr()));

        }
        unlink($localfile);
        return $result;
    }

    public function getSanitizeFolderName($folder){
        $folder = strtolower($folder);
        $folder = strtr($folder," àáâãäçèéêëìíîïñòóôõöùúûüýÿ","_aaaaaceeeeiiiinooooouuuuyy");
        $folder = preg_replace('/[^\w_]/',"",$folder);
        return $folder;
    }

}