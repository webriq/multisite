<?php

namespace Grid\MultisitePlatform\Controller;

use Zend\Mvc\Exception\RuntimeException;
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
        $locator    = $this->getServiceLocator();
        $siteInfo   = $locator->get( 'Zork\Db\SiteInfo' );
        $auth       = $locator->get( 'Zend\Authentication\AuthenticationService' );
        $domain     = $this->params()
                           ->fromRoute( 'domain' );

        if ( empty( $domain ) || ! strstr( $domain, '.' ) )
        {
            throw new RuntimeException(
                'Domain is not supplied, or invalid'
            );
        }

        $path = '/';

        if ( $auth->hasIdentity() )
        {
            $store = $this->getServiceLocator()
                          ->get( 'Grid\MultisitePlatform\Authentication\AutoLoginToken' );

            $path = '/' . ltrim(
                $this->url()
                     ->fromRoute(
                         'Grid\User\Authentication\LoginWidth',
                         array(
                             'locale' => (string) $this->locale(),
                         ),
                         array(
                             'query' => array(
                                 'autoLoginToken' => $store->create(
                                     $auth->getIdentity()
                                          ->email
                                 ),
                             ),
                         )
                     ),
                '/'
            );
        }

        $port   = $siteInfo->getPort();
        $scheme = $siteInfo->getScheme() ?: 'http';

        if ( $port )
        {
            $path = ':' . $port . $path;
        }

        return $this->redirect()
                    ->toUrl( $scheme . '://' . $domain . $path );
    }

}
