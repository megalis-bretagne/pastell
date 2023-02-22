<?php

class PastellCoreTestFail extends ActionExecutor
{
    public function go()
    {
        throw new Exception("Fail !");
    }
}
