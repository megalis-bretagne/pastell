<?php

class PastellCoreFluxTestFail extends ActionExecutor
{
    public function go()
    {
        throw new Exception("Raté !");
    }
}
