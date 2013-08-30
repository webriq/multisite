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
        'delete' => array(
            'central.site' => 'delete',
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

    /**
     * Delete action (for only platform owner)
     */
    public function deleteAction()
    {
        /* @var $model \Grid\MultisiteCentral\Model\Site\Model */
        /* @var $site \Grid\MultisiteCentral\Model\Site\Structure */
        $params = $this->params();
        $model  = $this->getServiceLocator()
                       ->get( 'Grid\MultisiteCentral\Model\Site\Model' );
        $site   = $model->find( $params->fromRoute( 'id' ) );

        if ( empty( $site ) )
        {
            $this->getResponse()
                 ->setStatusCode( 404 );

            return;
        }

        if ( ! $site->delete() )
        {
            throw new \LogicException( 'Site cannot be deleted' );
        }

        return $this->redirect()
                    ->toRoute( 'Grid\MultisiteCentral\SiteAdmin\List', array(
                        'locale' => (string) $this->locale(),
                    ) );
    }

}
