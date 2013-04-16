<?php

namespace Grid\MultisiteCentral\Model\Paragraph\Structure;

use Zend\Authentication\AuthenticationService;
use Grid\Paragraph\Model\Paragraph\Structure\AbstractLeaf;

/**
 * Content
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class SiteWizard extends AbstractLeaf
{

    /**
     * Paragraph type
     *
     * @var string
     */
    protected static $type = 'siteWizard';

    /**
     * Paragraph-render view-open
     *
     * @var string
     */
    protected static $viewOpen = 'grid/paragraph/render/siteWizard';

    /**
     * Is site-wizard allowed
     *
     * @return bool
     */
    public function getAllowed()
    {
        $auth   = new AuthenticationService;
        $config = $this->getServiceLocator()
                       ->get( 'Config'  )
                            [ 'modules' ]
                            [ 'Grid\User'    ];

        return $auth->hasIdentity() || ! empty(
            $config['features']['registrationEnabled']
        );
    }

    /**
     * Get site-wizard start form
     *
     * @return \Zend\Form\Form
     */
    public function getForm( $action )
    {
        $form   = $this->getServiceLocator()
                       ->get( 'Form' )
                       ->create( 'Grid\MultisiteCentral\SiteWizard\Start' );

        $form->setAttributes( array(
            'target' => '_blank',
            'action' => $action,
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

}
