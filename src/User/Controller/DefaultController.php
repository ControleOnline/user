<?php

namespace User\Controller;

use User\Model\UserModel;

class DefaultController extends \Core\Controller\DefaultController {

    public function indexAction() {
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        if ($this->_userModel->loggedIn()) {
            return $this->redirect()->toUrl($this->_renderer->basePath('/user/profile'));
        } else {
            return \Core\Helper\View::redirectToLogin($this->_renderer, $this->getResponse(), $this->getRequest(), $this->redirect());
        }
    }

    public function infoAction() {
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        if ($this->_userModel->loggedIn()) {
            return array(
                'id' => $this->_userModel->getLoggedUser()->getPeople()->getId(),
                'name' => $this->_userModel->getLoggedUser()->getPeople()->getName(),
                'email' => $this->_userModel->getLoggedUser()->getPeople()->getEmail()[0]->getEmail(),
                'phone' => count($this->_userModel->getLoggedUser()->getPeople()->getPhone()) > 0 ? ('(' . $this->_userModel->getLoggedUser()->getPeople()->getPhone()[0]->getDdd() . ') ' . \Core\Helper\Format::maskNumber(strlen($this->_userModel->getLoggedUser()->getPeople()->getPhone()[0]->getPhone()) > 8 ? "#####-####" : "####-####", $this->_userModel->getLoggedUser()->getPeople()->getPhone()[0]->getPhone())) : NULL
            );
        } else {
            \Core\Model\ErrorModel::addError('User is not logged in');
        }
    }

}
