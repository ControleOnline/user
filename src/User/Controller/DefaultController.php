<?php

namespace User\Controller;

use Doctrine\ORM\EntityManager;
use User\Model\UserModel;
use Core\Controller\AbstractController;
use Zend\View\Model\ViewModel;
use Core\Helper\Format;

class DefaultController extends AbstractController {

    /**
     * @var EntityManager
     */
    protected $_em;
    protected $_userModel;
    protected $_view;

    public function loginAction() {
        $this->_view = new ViewModel();
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        $username = $this->params()->fromPost('username');
        $password = $this->params()->fromPost('password');
        if ($username && $password) {
            $this->_userModel->login($username, $password);
        }
        if ($this->_userModel->loggedIn()) {
            $this->_view->setVariables(Format::returnData($this->_userModel->getLoggedUser()));
            $this->_view->setTemplate('user/default/profile.phtml');
        } else {
            $this->_view->setTemplate('user/default/login.phtml');
        }
        return $this->_view;
    }

    public function logoutAction() {
        $this->_view = new ViewModel();
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        $this->_userModel->logout();
        $this->_view->setTemplate('user/default/login.phtml');
        return $this->_view;
    }

    public function indexAction() {
        $this->_view = new ViewModel();
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        if ($this->_userModel->loggedIn()) {
            $this->_view->setVariables(Format::returnData($this->_userModel->getLoggedUser()));
            $this->_view->setTemplate('user/default/profile.phtml');          
        } else {
            $this->_view->setTemplate('user/default/login.phtml');
        }
        return $this->_view;
    }

    public function profileAction() {
        $this->_view = new ViewModel();
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        $name = $this->params()->fromPost('name');
        $username = $this->params()->fromPost('username');
        $password = $this->params()->fromPost('password');
        $confirm_password = $this->params()->fromPost('confirm-password');

        if ($username && !$this->_userModel->loggedIn()) {
            $this->_userModel->changeUser($username, $name, $password, $confirm_password);
        }
        if ($this->_userModel->loggedIn()) {
            $this->_view->setVariables(Format::returnData($this->_userModel->getLoggedUser()));
            $this->_view->setTemplate('user/default/profile.phtml');
        } else {
            $this->_view->setTemplate('user/default/create-account.phtml');
        }
        return $this->_view;
    }

    public function forgotPassword() {
        $this->_view = new ViewModel();
        return $this->_view;
    }

    public function forgotUsername() {
        $this->_view = new ViewModel();
        return $this->_view;
    }

    public function createAccountAction() {
        $this->_view = new ViewModel();
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        $name = $this->params()->fromPost('name');
        $username = $this->params()->fromPost('username');
        $password = $this->params()->fromPost('password');
        $confirm_password = $this->params()->fromPost('confirm-password');

        if ($username && !$this->_userModel->loggedIn()) {
            $this->_userModel->createAccount($username, $name, $password, $confirm_password);
        }
        if ($this->_userModel->loggedIn()) {
            $this->_view->setVariables(Format::returnData($this->_userModel->getLoggedUser()));
            $this->_view->setTemplate('user/default/profile.phtml');
        } else {
            $this->_view->setTemplate('user/default/create-account.phtml');
        }
        return $this->_view;
    }

}
