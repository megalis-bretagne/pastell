<?php

class IParapheurEmptyWSDLCache extends ActionExecutor
{
    /**
     *
     * On ne fait pas dans la dentelle : tous les fichiers WSDL sont supprimés !
     * L'extension SOAP ne permet pas de savoir quel est le fichier a supprimé
     * On aurait pu mettre la fonction sur le connecteur global, mais cela aurait rendu l'utilisation plus complexe
     *
     * @return bool
     */
    public function go()
    {
        $finder = new \Symfony\Component\Finder\Finder();
        $filesystem = new \Symfony\Component\Filesystem\Filesystem();

        $wsdl_cache_dir = ini_get("soap.wsdl_cache_dir");

        $cacheFileIterator = $finder
            ->files()
            ->depth('== 0')
            ->in($wsdl_cache_dir)
            ->name("wsdl-*")
            ->getIterator();

        foreach ($cacheFileIterator as $file) {
            $filesystem->remove($file);
        }

        $this->setLastMessage("Le cache WSDL a été supprimé");
        return true;
    }
}
