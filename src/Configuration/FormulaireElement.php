<?php

declare(strict_types=1);

namespace Pastell\Configuration;

enum FormulaireElement: string
{
    case AUTOCOMPLETE = 'autocomplete';
    case CHOICE_ACTION = 'choice-action';
    case COMMENTAIRE = 'commentaire';
    case CONTENT_TYPE = 'content-type';
    case DEFAULT = 'default';
    case DEPEND = 'depend';
    case EDIT_ONLY = 'edit-only';
    case INDEX = 'index';
    case IS_EQUAL = 'is_equal';
    case IS_EQUAL_ERROR = 'is_equal_error';
    case LINK_NAME = 'link_name';
    case MAX_FILE_SIZE = 'max_file_size';
    case MAX_MULTIPLE_FILE_SIZE = 'max_multipe_file_size';
    case MAY_BE_NULL = 'may_be_null';
    case MULTIPLE = 'multiple';
    case NAME = 'name';
    case NO_SHOW = 'no-show';
    case ONCHANGE = 'onchange';
    case PREG_MATCH = 'preg_match';
    case PREG_MATCH_ERROR = 'preg_match_error';
    case PROGRESS_BAR = 'progress_bar';
    case READ_ONLY = 'read-only';
    case READ_ONLY_CONTENT = 'read-only-content';
    case REQUIS = 'requis';
    case SHOW_ROLE = 'show-role';
    case TITLE = 'title';
    case VALUE = 'value';
    case VISIONNEUSE = 'visionneuse';
    case VISIONNEUSE_NO_LINK = 'visionneuse-no-link';
}
