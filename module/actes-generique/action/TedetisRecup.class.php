<?php

class TedetisRecup extends ActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        /** @var TdtRetriever $tdtRetriever */
        $tdtRetriever = $this->objectInstancier->getInstance(TdtRetriever::class);
        $result = $tdtRetriever->retrieve($this->type, $this->id_e, $this->id_d, $this->id_u);
        $this->setLastMessage($tdtRetriever->getLastMessage());
        return $result;
    }
}
