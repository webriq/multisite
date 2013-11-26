<?php

namespace Grid\MultisitePlatform\Controller;

class AdminController extends AbstractAdminController
{

    /**
     * Default action if none provided
     *
     * @return  \Zend\Http\Response
     */
    public function indexAction()
    {
        return $this->redirect()
                    ->toRoute( 'Grid\MultisitePlatform\Admin\NotAllowed', array(
                        'locale' => (string) $this->locale(),
                    ) );
    }

    /**
     * Not allowed action
     *
     * Redirected here, if the requested operation is not allowed.
     *
     * @return  array
     */
    public function notAllowedAction()
    {
        return array();
    }

}
