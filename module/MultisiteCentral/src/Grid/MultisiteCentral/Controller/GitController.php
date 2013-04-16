<?php

namespace Grid\MultisiteCentral\Controller;

use Grid\Core\Controller\AbstractListController;

/**
 * GitController
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class GitController extends AbstractListController
{

    /**
     * @var array
     */
    protected $aclRights = array(
        '' => array(
            'sysadmin.git' => 'view',
        ),
    );

    /**
     * Get paginator list
     *
     * @return \Zend\Paginator\Paginator
     */
    protected function getPaginator()
    {
        return $this->getServiceLocator()
                    ->get( 'Grid\MultisiteCentral\Model\Git\Model' )
                    ->getPaginator();
    }

}
