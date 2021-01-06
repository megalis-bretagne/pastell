<?php

class CPPListeStructure extends ActionExecutor
{
    /**
     * @return UTF8Encoder
     */
    public function getUTF8Encoder()
    {
        return $this->objectInstancier->getInstance(UTF8Encoder::class);
    }

    /**
     * @return array|mixed
     * @throws Exception
     */
    public function metier()
    {
        /** @var CPP $cpp */
        $cpp = $this->getMyConnecteur();
        return $this->getUTF8Encoder()->decode(
            json_encode($this->getUTF8Encoder()->encode(
                $cpp->listeStructure()
            ))
        );
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        /** @var CPP $cpp */
        $cpp = $this->getMyConnecteur();
        $result = $this->metier();
        if (! $result) {
            $this->setLastMessage("La connexion cpp a échoué : " . $cpp->getLastError());
            return false;
        }
        $this->setLastMessage("Liste des structures : " . $result);
        return true;
    }
}
