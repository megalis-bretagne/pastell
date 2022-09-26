<?php

namespace Pastell\Bootstrap;

enum InstallResult
{
    case NothingToDo;
    case InstallOk;
    case InstallFailed;
}
