<?php

namespace Pastell\Updater;

interface Version
{
    public function update(): void;
}
