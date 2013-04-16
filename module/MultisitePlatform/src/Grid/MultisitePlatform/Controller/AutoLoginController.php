<?php

namespace Grid\MultisitePlatform\Controller;

use Zend\Mvc\Exception\RuntimeException;
use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * AutoLoginController
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class AutoLoginController extends AbstractActionController
{

    /**
     * Auto-login by domain
     *
     * @throws \Zend\Mvc\Exception\RuntimeException
     */
    public function byDomainAction()
    {
        $auth   = new AuthenticationService;
        $domain = $this->params()
                       ->fromRoute( 'domain' );

        if ( ! $auth->hasIdentity() )
        {
            $this->paragraphLayout();

            $this->getResponse()
                 ->setStatusCode( 403 );

            return;
        }

        if ( empty( $domain ) || ! strstr( $domain, '.' ) )
        {
            throw new RuntimeException(
                'Domain is not supplied, or invalid'
            );
        }

        $store = $this->getServiceLocator()
                      ->get( 'Grid\MultisitePlatform\Authentication\AutoLoginToken' );

        return $this->redirect()
                    ->toUrl( 'http://' . $domain .
                             $this->url()
                                  ->fromRoute( 'Grid\User\Authentication\LoginWidth', array(
                                        'locale' => (string) $this->locale(),
                                    ), array(
                                        'query' => array(
                                            'autoLoginToken' => $store->create(
                                                $auth->getIdentity()
                                                     ->email
                                            ),
                                        ),
                                    ) )
                    );
    }

}
