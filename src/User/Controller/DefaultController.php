<?php

namespace User\Controller;

use Doctrine\ORM\EntityManager;
use User\Model\UserModel;
use Core\Helper\Format;
use Core\Model\ErrorModel;
use Core\Helper\ViewRender;

class DefaultController extends \Core\Controller\DefaultController {

    /**
     * @var EntityManager
     */
    protected $_em;
    protected $_userModel;
    protected $_peopleModel;

    public function loginAction() {
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        $username = $this->params()->fromPost('username');
        $password = $this->params()->fromPost('password');
        $login_referrer = $this->params()->fromQuery('login-referrer');

        if ((!$username || !$password) && $this->_userModel->loggedIn()) {
            return $this->redirect()->toUrl($login_referrer ?: $this->_renderer->basePath('/user/profile'));
        } else {
            $this->_userModel->login($username, $password);
            $this->_view->setVariables(Format::returnData($this->_userModel->getLoggedUser()));
            $this->_view->setVariable('login_referrer', $login_referrer);
            $this->_view->setTemplate('user/default/login.phtml');
        }

        return $this->_view;
    }

    public function logoutAction() {
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        $this->_userModel->logout();
        return $this->redirect()->toUrl($this->_renderer->basePath('/user/login'));
    }

    public function indexAction() {
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        if ($this->_userModel->loggedIn()) {
            return $this->redirect()->toUrl($this->_renderer->basePath('/user/profile'));
        } else {
            return $this->redirectToLogin();
        }
    }

    public function redirectToLogin() {
        $url = $this->getRequest()->getUri()->getPath();
        $params = $this->getRequest()->getUri()->getQueryAsArray() ?: array();
        return $this->redirect()->toUrl($this->_renderer->basePath('/user/login?login-referrer=' . $url . rawurlencode('&' . http_build_query($params))));
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
                                    'url' => $user->getPeople()->getImage()->getUrl()
                                )
                    ))) : ErrorModel::addError('User not found'));
        return $this->_view;
    }

    public function profileImageAction() {
        $defaultImgProfile = 'public/assets/img/default/profile.png';
        $userId = $this->params()->fromQuery('id');
        if ($userId) {
            $this->_peopleModel = new UserModel();
            $this->_peopleModel->initialize($this->serviceLocator);
            $this->_peopleModel->setEntity('Entity\\People');
            $people = $this->_peopleModel->getEntity()->find($userId);
        }
        $file = $people && $people->getImage() && is_file($people->getImage()->getPath()) ? $people->getImage()->getPath() : $defaultImgProfile;
        $imageContent = file_get_contents($file);
        $response = $this->getResponse();
        $response->setContent($imageContent);
        $response
                ->getHeaders()
                ->addHeaderLine('Content-Transfer-Encoding', 'binary')
                ->addHeaderLine('Content-Type', exif_imagetype($file));

        return $response;
    }

    public function changePasswordAction() {
        $name = $this->params()->fromPost('name');
        $username = $this->params()->fromPost('username');
        $password = $this->params()->fromPost('password');
        $confirm_password = $this->params()->fromPost('confirm-password');

        if ($username && $this->_userModel->loggedIn()) {
            $this->_userModel->changeUser($username, $name, $password, $confirm_password);
        }
    }

    public function deleteAction() {
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        $delete = $this->_userModel->delete($this->params()->fromPost('id'));
        return $delete ? $this->_view : ErrorModel::addError('Error removing this user!');
    }

    public function deletePhoneAction() {
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        $delete = $this->_userModel->deletePhone($this->params()->fromPost('id'));
        return $delete ? $this->_view : ErrorModel::addError('Error removing this phone!');
    }

    public function deleteEmailAction() {
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        $delete = $this->_userModel->deleteEmail($this->params()->fromPost('id'));
        return $delete ? $this->_view : ErrorModel::addError('Error removing this email!');
    }

    public function profileAction() {
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        if ($this->_userModel->loggedIn()) {
            $this->_view->user = $this->_userModel->getLoggedUser();
            $this->_view->user_people = $this->_userModel->getLoggedUserPeople();
            $this->_view->setTemplate('user/default/profile.phtml');
        } else {
            return $this->redirectToLogin();
        }
        return $this->_view;
    }

    public function forgotPassword() {
        return $this->_view;
    }

    public function forgotUsername() {
        return $this->_view;
    }

    public function addPhoneAction() {
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        $phone = $this->params()->fromPost('phone');
        $ddd = $this->params()->fromPost('ddd');

        if ($this->_userModel->loggedIn() && $ddd && $phone) {
            $new_phone = $this->_userModel->addUserPhone($ddd, $phone);
            $this->_view->setVariables(Format::returnData($new_phone));
        } else {
            ErrorModel::addError('The ddd field is required.');
            ErrorModel::addError('The phone field is required.');
        }
        $this->_view->setTemplate('user/form/add-user-phone.phtml');
        $this->_view->setTerminal(true);
        return $this->_view;
    }

    public function addEmailAction() {
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        $email = $this->params()->fromPost('email');

        if ($this->_userModel->loggedIn() && $email) {
            $new_email = $this->_userModel->addUserEmail($email);
            $this->_view->setVariables(Format::returnData($new_email));
        } else {
            ErrorModel::addError('The email field is required.');
        }
        $this->_view->setTemplate('user/form/add-user-email.phtml');
        $this->_view->setTerminal(true);
        return $this->_view;
    }

    public function addUserAction() {
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);

        $username = $this->params()->fromPost('username');
        $password = $this->params()->fromPost('password');
        $confirm_password = $this->params()->fromPost('confirm-password');

        if ($this->_userModel->loggedIn() && $username && $password && $confirm_password) {
            $new_user = $this->_userModel->addUser($username, $password, $confirm_password);
            $this->_view->setVariables(Format::returnData($new_user));
        }
        $this->_view->setTemplate('user/form/add-user.phtml');
        $this->_view->setTerminal(true);
        return $this->_view;
    }

    public function createAccountAction() {
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        $name = $this->params()->fromPost('name');
        $username = $this->params()->fromPost('username');
        $password = $this->params()->fromPost('password');
        $confirm_password = $this->params()->fromPost('confirm-password');
        $email = $this->params()->fromPost('email');
        if ($username && !$this->_userModel->loggedIn()) {
            $this->_userModel->createAccount($username, $email, $name, $password, $confirm_password);
            $this->_view->setVariables(Format::returnData($this->_userModel->getLoggedUser()));
            $this->_view->setTemplate('user/default/create-account.phtml');
        } elseif ($this->_userModel->loggedIn()) {
            return $this->redirect()->toUrl($this->_renderer->basePath('/user/profile'));
        } else {
            $this->_view->setTemplate('user/default/create-account.phtml');
        }
        return $this->_view;
    }

}
