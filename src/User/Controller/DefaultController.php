<?php

namespace User\Controller;

use Doctrine\ORM\EntityManager;
use User\Model\UserModel;
use Core\Helper\Format;
use Core\Model\ErrorModel;

class DefaultController extends \Core\Controller\DefaultController {

    /**
     * @var EntityManager
     */
    protected $_em;
    protected $_userModel;

    public function loginAction() {
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
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        $this->_userModel->logout();

        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $url = $renderer->basePath('/user/login');

        return $this->redirect()->toUrl($url);
    }

    public function indexAction() {
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

    public function userInUseAction() {
        $usermame = $this->params()->fromQuery('username');
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        $user = $this->_userModel->getEntity()->findOneBy(array('username' => $usermame));
        $this->_view->setVariables(array('data' => false));
        $user ? ErrorModel::addError('User in use') : false;
        return $this->_view;
    }

    public function getImageProfileAction() {
        $usermame = $this->params()->fromQuery('username');
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        $user = $this->_userModel->getEntity()->findOneBy(array('username' => $usermame));
        $this->_view->setVariables($user ? Format::returnData(array(
                            'user' => array(
                                'name' => ucwords(strtolower($user->getPeople()->getName())),
                                'image' => array(
                                    'url' => $user->getImage()->getUrl()
                                )
                    ))) : ErrorModel::addError('User not found'));
        return $this->_view;
    }

    public function profileImageAction() {
        $defaultImgProfile = 'public/assets/img/default/profile.png';
        $userId = $this->params()->fromQuery('id');
        if ($userId) {
            $this->_userModel = new UserModel();
            $this->_userModel->initialize($this->serviceLocator);
            $user = $this->_userModel->getEntity()->find($userId);
        }
        $imageContent = file_get_contents($user && $user->getImage() && is_file($user->getImage()->getPath()) ? $user->getImage()->getPath() : $defaultImgProfile);
        $response = $this->getResponse();
        $response->setContent($imageContent);
        $response
                ->getHeaders()
                ->addHeaderLine('Content-Transfer-Encoding', 'binary')
                ->addHeaderLine('Content-Type', 'image/png');

        return $response;
    }

    public function profileAction() {
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        $name = $this->params()->fromPost('name');
        $username = $this->params()->fromPost('username');
        $password = $this->params()->fromPost('password');
        $confirm_password = $this->params()->fromPost('confirm-password');

        if ($username && $this->_userModel->loggedIn()) {
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
        return $this->_view;
    }

    public function forgotUsername() {
        return $this->_view;
    }

    public function createAccountAction() {
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
