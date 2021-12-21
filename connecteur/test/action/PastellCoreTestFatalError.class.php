<?php

class PastellCoreTestFatalError extends ActionExecutor
{
    public function go()
    {
        trigger_error("Fatal error", E_USER_ERROR);
    }
}
