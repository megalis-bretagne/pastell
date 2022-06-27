<?php

declare(strict_types=1);

namespace Pastell\Configuration;

enum ElementType: string
{
    case TEXT = 'text';
    case PASSWORD = 'password';
    case SELECT = 'select';
    case FILE = 'file';
    case CHECKBOX = 'checkbox';
    case EXTERNAL_DATA = 'externalData';
    case TEXTAREA = 'textarea';
    case DATE = 'date';
    case LINK = 'link';
    case URL = 'url';
}
