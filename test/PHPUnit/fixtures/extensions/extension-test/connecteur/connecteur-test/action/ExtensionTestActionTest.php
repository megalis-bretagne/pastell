<?php

final class ExtensionTestActionTest extends \ActionExecutor
{
    public function go()
    {
        $this->setLastMessage('Action done');
        return true;
    }
}
