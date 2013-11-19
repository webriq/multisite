<?php

namespace Grid\MultisiteCentral\Controller;

use Grid\MultisitePlatform\Controller\AbstractAdminController;

/**
 * WelcomeController
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class WelcomeController extends AbstractAdminController
{

    /**
     * Get site-wizard form (first step, with fixed submit: next)
     *
     * @return  \Zork\Form\Form
     */
    protected function getSiteWizardForm()
    {
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

        return $form;
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->paragraphLayout();
        $form    = null;
        $hash    = null;
        $request = $this->getRequest();
        $page    = (int) $request->getPost( 'page', $request->getQuery( 'page' ) );
        $auth    = $this->getServiceLocator()
                        ->get( 'Zend\Authentication\AuthenticationService' );

        if ( ! $auth->hasIdentity() )
        {
            return $this->redirect()
                        ->toUrl( '/' );
        }

        if ( $this->getServiceLocator()
                  ->get( 'Grid\User\Model\Permissions\Model' )
                  ->isAllowed( 'central.site', 'create' ) )
        {
            $form     = $this->getSiteWizardForm();
            $continue = $this->params()
                             ->fromQuery( 'continue' );

            if ( $continue )
            {
                $data = $this->getServiceLocator()
                             ->get( 'Grid\MultisiteCentral\Model\SiteWizardData' );

                if ( $data->has( $continue ) )
                {
                    $hash = $continue;
                }
            }
        }

        return array(
            'form'      => $form,
            'hash'      => $hash,
            'page'      => $page,
            'user'      => $auth->getIdentity(),
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
