<?php

namespace Grid\MultisiteCentral\Controller;

use Grid\Core\Controller\AbstractListController;

/**
 * SiteAdminController
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class SiteAdminController extends AbstractListController
{

    /**
     * @var array
     */
    protected $aclRights = array(
        '' => array(
            'central.site' => 'view',
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
                    ->get( 'Grid\MultisiteCentral\Model\Site\Model' )
                    ->getPaginator();
    }

}
