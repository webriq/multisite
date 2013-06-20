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
        $auth       = new AuthenticationService;
        $controller = preg_replace( '/Controller$/', '', __CLASS__ );

        if ( ! $auth->hasIdentity() )
        {
         /* $this->getResponse()
                 ->setStatusCode( 403 ); */

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

        $form   = $this->getServiceLocator()
                       ->get( 'Form' )
                       ->create( 'Grid\MultisiteCentral\SiteWizard\Start' );

        $config = $this->getServiceLocator()
                       ->get( 'Config'  )
                            [ 'modules' ]
                            [ 'Grid\User'    ];

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
     * Site-list action
     */
    public function siteListAction()
    {
        $request = $this->getRequest();
        $auth    = new AuthenticationService;
        $page    = (int) $request->getPost(
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

}
