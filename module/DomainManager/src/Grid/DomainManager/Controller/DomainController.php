<?php

namespace Grid\DomainManager\Controller;

use Zork\Stdlib\Message;
use Grid\Core\Controller\AbstractListController;

/**
 * DomainController
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class DomainController extends AbstractListController
{

    /**
     * @var int
     */
    protected $siteId;

    /**
     * @return int
     */
    protected function getSiteId()
    {
        if ( null === $this->siteId )
        {
            $this->siteId = $this->getServiceLocator()
                                 ->get( 'Zork\Db\SiteInfo' )
                                 ->getSiteId();
        }

        return $this->siteId;
    }

    /**
     * Get paginator list
     *
     * @return \Zend\Paginator\Paginator
     */
    protected function getPaginator()
    {
        return $this->getServiceLocator()
                    ->get( 'Grid\MultisitePlatform\Model\Domain\Model' )
                    ->getPaginator( $this->getSiteId() );
    }

    /**
     * Edit a domain
     */
    public function editAction()
    {
        $params     = $this->params();
        $request    = $this->getRequest();
        $locator    = $this->getServiceLocator();
        $model      = $locator->get( 'Grid\MultisitePlatform\Model\Domain\Model' );
        $form       = $locator->get( 'Form' )
                              ->create( 'Grid\DomainManager\Domain' );

        if ( ( $id = $params->fromRoute( 'id' ) ) )
        {
            $domain = $model->find( $id );

            if ( empty( $domain ) )
            {
                $this->getResponse()
                     ->setStatusCode( 404 );

                return;
            }

            if ( $domain->siteId != $this->getSiteId() )
            {
                $this->getResponse()
                     ->setStatusCode( 403 );

                return;
            }

            $actualId = $locator->get( 'Zork\Db\SiteInfo' )
                                ->getDomainId();

            if ( $domain->id == $actualId )
            {
                $this->getResponse()
                     ->setStatusCode( 403 );

                return;
            }
        }
        else
        {
            $domain = $model->create( array(
                'siteId' => $this->getSiteId(),
            ) );
        }

        /* @var $form \Zend\Form\Form */
        $form->setHydrator( $model->getMapper() )
             ->bind( $domain );

        if ( $request->isPost() )
        {
            $form->setData( $request->getPost() );

            if ( $form->isValid() && $domain->save() )
            {
                $this->messenger()
                     ->add( 'domain.form.success',
                            'domain', Message::LEVEL_INFO );

                return $this->redirect()
                            ->toRoute( 'Grid\DomainManager\Domain\List', array(
                                'locale' => (string) $this->locale(),
                            ) );
            }
            else
            {
                $this->messenger()
                     ->add( 'domain.form.failed',
                            'domain', Message::LEVEL_ERROR );
            }
        }

        $form->setCancel(
            $this->url()
                 ->fromRoute( 'Grid\DomainManager\Domain\List', array(
                        'locale' => (string) $this->locale(),
                    ) )
        );

        return array(
            'form'      => $form,
            'domain'    => $domain,
        );
    }

    /**
     * Delete a sub-domain
     */
    public function deleteAction()
    {
        $params     = $this->params();
        $locator    = $this->getServiceLocator();
        $model      = $locator->get( 'Grid\MultisitePlatform\Model\Domain\Model' );
        $domain     = $model->find( $params->fromRoute( 'id' ) );

        if ( empty( $domain ) )
        {
            $this->getResponse()
                 ->setStatusCode( 404 );

            return;
        }

        if ( $domain->siteId != $this->getSiteId() )
        {
            $this->getResponse()
                 ->setStatusCode( 403 );

            return;
        }

        $actualId = $this->getServiceLocator()
                         ->get( 'Zork\Db\SiteInfo' )
                         ->getDomainId();

        if ( $domain->id == $actualId )
        {
            $this->getResponse()
                 ->setStatusCode( 403 );

            return;
        }

        if ( $domain->delete() )
        {
            $this->messenger()
                 ->add( 'domain.form.success',
                        'domain', Message::LEVEL_INFO );
        }
        else
        {
            $this->messenger()
                 ->add( 'domain.form.failed',
                        'domain', Message::LEVEL_ERROR );
        }

        return $this->redirect()
                    ->toRoute( 'Grid\DomainManager\Domain\List', array(
                        'locale' => (string) $this->locale(),
                    ) );
    }

}
