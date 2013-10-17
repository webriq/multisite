<?php

namespace Grid\MultisiteCentral\Controller;

use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * WelcomeController
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class WelcomeController extends AbstractActionController
{

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->paragraphLayout();
        $controller = preg_replace( '/Controller$/', '', get_class( $this ) );
        $auth       = $this->getServiceLocator()
                           ->get( 'Zend\Authentication\AuthenticationService' );

        if ( ! $auth->hasIdentity() )
        {
            return $this->redirect()
                        ->toUrl( '/' );
        }

        $parts = array();

        if ( $this->getServiceLocator()
                  ->get( 'Grid\User\Model\Permissions\Model' )
                  ->isAllowed( 'central.site', 'create' ) )
        {
            $parts[] = $this->forward()
                            ->dispatch( $controller, array(
                                'locale' => (string) $this->locale(),
                                'action' => 'site-wizard',
                            ) );

            $continue = $this->params()
                             ->fromQuery( 'continue' );

            if ( $continue )
            {
                $data = $this->getServiceLocator()
                             ->get( 'Grid\MultisiteCentral\Model\SiteWizardData' );

                if ( $data->has( $continue ) )
                {
                    $parts[] = $this->forward()
                                    ->dispatch( $controller, array(
                                        'locale' => (string) $this->locale(),
                                        'action' => 'continue',
                                        'hash'   => $continue,
                                    ) );
                }
            }
        }

        $parts[] = $this->forward()
                        ->dispatch( $controller, array(
                            'locale' => (string) $this->locale(),
                            'action' => 'site-list',
                        ) );

        return array(
            'parts' => $parts,
        );
    }

    /**
     * Site-wizard action
     */
    public function siteWizardAction()
    {
        if ( ! $this->getServiceLocator()
                    ->get( 'Grid\User\Model\Permissions\Model' )
                    ->isAllowed( 'central.site', 'create' ) )
        {
            $this->getResponse()
                 ->setStatusCode( 403 );

            return;
        }

        $form = $this->getServiceLocator()
                     ->get( 'Form' )
                     ->create( 'Grid\MultisiteCentral\SiteWizard\Start' );

        $form->setAttributes( array(
            'target' => '_blank',
            'action' => $this->url()
                             ->fromRoute(
                                 'Grid\MultisiteCentral\SiteWizard\Step',
                                 array( 'locale' => (string) $this->locale() )
                             )
        ) );

        $form->add( array(
            'type'  => 'Zork\Form\Element\Submit',
            'name'  => 'next',
            'options'       => array(
                'text_domain'   => 'default',
            ),
            'attributes'    => array(
                'value'         => 'default.next',
            ),
        ) );

        return array(
            'form' => $form,
        );
    }

    /**
     * Continue (site-creation) action
     */
    public function continueAction()
    {
        return array(
            'hash' => $this->params()
                           ->fromRoute( 'hash' )
        );
    }

    /**
     * Site-list action
     */
    public function siteListAction()
    {
        $request = $this->getRequest();
        $auth    = $this->getServiceLocator()
                        ->get( 'Zend\Authentication\AuthenticationService' );

        $page = (int) $request->getPost(
            'page', $request->getQuery( 'page', 0 )
        );

        if ( ! $auth->hasIdentity() )
        {
            $this->getResponse()
                 ->setStatusCode( 403 );

            return;
        }

        return array(
            'page'      => $page,
            'paginator' => $this->getServiceLocator()
                                ->get( 'Grid\MultisiteCentral\Model\Site\Model' )
                                ->getPaginator( $auth->getIdentity()->id ),
        );
    }

    /**
     * Delete site action (only owned)
     */
    public function deleteSiteAction()
    {
        $params = $this->params();
        $auth   = $this->getServiceLocator()
                       ->get( 'Zend\Authentication\AuthenticationService' );

        if ( ! $auth->hasIdentity() )
        {
            $this->getResponse()
                 ->setStatusCode( 403 );

            return;
        }

        /* @var $model \Grid\MultisiteCentral\Model\Site\Model */
        /* @var $site \Grid\MultisiteCentral\Model\Site\Structure */
        $model = $this->getServiceLocator()
                      ->get( 'Grid\MultisiteCentral\Model\Site\Model' );
        $site  = $model->find( $params->fromRoute( 'id' ) );

        if ( empty( $site ) )
        {
            $this->getResponse()
                 ->setStatusCode( 404 );

            return;
        }

        if ( $site->ownerId != $auth->getIdentity()->id )
        {
            $this->getResponse()
                 ->setStatusCode( 403 );

            return;
        }

        if ( ! $site->delete() )
        {
            throw new \LogicException( 'Site cannot be deleted' );
        }

        return $this->redirect()
                    ->toRoute( 'Grid\MultisiteCentral\Welcome\Index', array(
                        'locale' => (string) $this->locale(),
                    ) );
    }

}
