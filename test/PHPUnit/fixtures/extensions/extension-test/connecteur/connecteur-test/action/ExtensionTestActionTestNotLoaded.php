<?php

final class ExtensionTestActionTestNotLoaded extends \ActionExecutor
{
    public function go()
    {
        $this->setLastMessage('Action not done');
        return true;
    }
}
