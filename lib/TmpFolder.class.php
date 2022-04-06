<?php

class TmpFolder
{
    /** @throws Exception */
    public function create(): string
    {
        $folder_name = sys_get_temp_dir() . "/pastell_tmp_folder_" . time() . "_" . mt_rand(0, mt_getrandmax());
        if (file_exists($folder_name)) {
            throw new Exception("Impossible de créer un répertoire temporaire : le répertoire existe");
        }
        mkdir($folder_name) ;
        if (! is_dir($folder_name)) {
             throw new Exception("Accès impossible au répertoire temporaire");
        }
        return $folder_name;
    }

    public function delete(string $folder_name)
    {
        if (! is_dir($folder_name)) {
                return;
        }
        foreach (scandir($folder_name) as $object) {
            if (in_array($object, [".",".."])) {
                continue;
            }
            if (is_dir("$folder_name/$object")) {
                $this->delete("$folder_name/$object");
            } else {
                unlink("$folder_name/$object");
            }
        }
        rmdir($folder_name);
    }
}
