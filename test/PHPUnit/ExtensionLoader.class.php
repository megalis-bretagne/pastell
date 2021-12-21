<?php

class ExtensionLoader
{
    private $extensionSQL;
    private $extensions;

    public function __construct(
        ExtensionSQL $extensionSQL,
        Extensions $extensions
    ) {
        $this->extensionSQL = $extensionSQL;
        $this->extensions = $extensions;
    }

    public function loadExtension(array $extension_path_list)
    {
        $result = array();
        foreach ($extension_path_list as $ext) {
            $this->extensionSQL->edit(0, $ext);
            $result[$ext] = $this->extensionSQL->getLastInsertId();
        }
        $this->extensions->loadConnecteurType();
        return $result;
    }
}
