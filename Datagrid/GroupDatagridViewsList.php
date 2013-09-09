<?php

namespace Oro\Bundle\UserBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\Views\View;
use Oro\Bundle\GridBundle\Datagrid\Views\AbstractViewsList;

class GroupDatagridViewsList extends AbstractViewsList
{
    /**
     * Returns an array of available views
     *
     * @return View[]
     */
    protected function getViewsList()
    {
        return array(
            new View('test')
        );
    }
}
