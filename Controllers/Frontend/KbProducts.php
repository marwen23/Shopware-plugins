<?php

class Shopware_Controllers_Frontend_KbProducts extends Enlight_Controller_Action
{
    public function indexAction()
    {

        echo 'hi';
    }

    public function importAction()
    {
        $import = new \KbProducts\Core\Import();
    }

}
