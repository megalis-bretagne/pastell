<?php

declare(strict_types=1);

namespace Pastell\Bootstrap;

class CreateOrUpdateAdmin implements InstallableBootstrap
{
    public function __construct(
        private readonly string $pastell_admin_login,
        private readonly string $pastell_admin_email,
        private readonly \AdminControler $adminControler,
    ) {
    }

    public function install(): InstallResult
    {
        $this->adminControler->createOrUpdateAdmin($this->pastell_admin_login, $this->pastell_admin_email);
        return InstallResult::InstallOk;
    }

    public function getName(): string
    {
        return "Adminitrateur initial";
    }
}
