<?php

namespace {namespace};

use Light\Crud;

/**
 * Class {name}
 * @package {namespace}
 *
 * @crud-title {name}
 * @crud-manageable true
 * @crud-sortable title
 *
 * @crud-header {"title": "Заголовок", "by": "title", "static": true}
 * @crud-header {"title": "Активность", "type": "bool", "by": "enabled", "static": true}
 *
 * @crud-filter {"type": "search", "by": ["title"]}
 */
class {name} extends Crud
{
}
