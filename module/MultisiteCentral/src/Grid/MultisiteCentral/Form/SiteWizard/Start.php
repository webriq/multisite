<?php

namespace Grid\MultisiteCentral\Form\SiteWizard;

use Zork\Form\Form;
use Zork\Form\PrepareElementsAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * Strat step for site-wizard
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class Start extends Form
         implements ServiceLocatorAwareInterface,
                    PrepareElementsAwareInterface
{

    use DomainPostfixTrait;

    /**
     * Prepare additional elements for the form
     *
     * @return void
     */
    public function prepareElements()
    {
        $subdomain = $this->get( 'subdomain' );
        $subdomain->setOptions( array_merge(
            $subdomain->getOptions(),
            array(
                'description'  => array(
                    'label'        => '<span>%s'
                                    . $this->getDomainPostfix()
                                    . '</span>',
                    'translatable' => false,
                    'attributes'   => array(
                        'data-js-type' => 'js.form.preview',
                    ),
                ),
            )
        ) );
    }

}
