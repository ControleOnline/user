<?php

namespace User\Controller;

use Doctrine\ORM\EntityManager;
use User\Model\UserModel;
use Core\Controller\AbstractController;
use Zend\View\Model\ViewModel;
use Core\Helper\Format;
use Core\Model\ErrorModel;

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

        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $url = $renderer->basePath('/user/login');

        return $this->redirect()->toUrl($url);
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

    public function getImageProfileAction() {
        $usermame = $this->params()->fromQuery('username');

        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        $user = $this->_userModel->getEntity()->findOneBy(array('username' => $usermame));

        echo json_encode(Format::returnData($user ? array(
                            'user' => array(
                                'name' => ucwords(strtolower($user->getPeople()->getName())),
                                'image' => array(
                                    'url' => $user->getImage()->getUrl()
                                )
                            )) : ErrorModel::addError('User not found')));
        exit;
    }

    public function profileImageAction() {
        $defaultImgProfile = 'public/img/default/profile.png';
        $userId = $this->params()->fromQuery('id');
        if ($userId) {
            $this->_view = new ViewModel();
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
        $this->_view = new ViewModel();
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
        $this->_view = new ViewModel();
        return $this->_view;
    }

    public function forgotUsername() {
        $this->_view = new ViewModel();
        return $this->_view;
    }

    public function userExistsAction() {
        $this->_view = new ViewModel();
        $response = array(
            'valid' => false,
            'message' => 'Post argument "user" is missing.'
        );

        if (isset($_POST['user'])) {
            $user = 'xxx';
            if ($user) {
                // User name is registered on another account
                $response = array('valid' => false, 'message' => 'This user name is already registered.');
            } else {
                // User name is available
                $response = array('valid' => true);
            }
        }
        $this->_view->setVariables($response);
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
