<?php

namespace User\Controller;

use User\Model\UserModel;
use Core\Helper\Format;
use Core\Model\ErrorModel;

class DefaultController extends \Core\Controller\DefaultController {

    /**
     * @var \User\Model\UserModel
     */
    protected $_userModel;

    /**
     * @var \User\Model\PeopleModel
     */
    protected $_peopleModel;

    public function loginAction() {
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        $username = $this->params()->fromPost('username');
        $password = $this->params()->fromPost('password');
        $login_referrer = $this->params()->fromQuery('login-referrer');
        if ((!$username || !$password) && $this->_userModel->loggedIn()) {            
            return $this->redirect()->toUrl($login_referrer ?: $this->_renderer->basePath('/user/profile'));
        } elseif ($username && $password) {            
            $this->_userModel->login($username, $password);
            $this->_view->setVariables(Format::returnData($this->_userModel->getLoggedUser()));
        }        
        $this->_view->setVariable('login_referrer', $login_referrer);        
        return $this->_view;
    }

    public function logoutAction() {
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        $this->_userModel->logout();
        return \Core\Helper\View::redirectToLogin($this->_renderer, $this->getResponse(), $this->getRequest(), $this->redirect(), false);
    }

    public function indexAction() {
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        if ($this->_userModel->loggedIn()) {
            return $this->redirect()->toUrl($this->_renderer->basePath('/user/profile'));
        } else {
            return \Core\Helper\View::redirectToLogin($this->_renderer, $this->getResponse(), $this->getRequest(), $this->redirect());
        }
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
            $this->_peopleModel->setEntity('Core\\Entity\\People');
            $people = $this->_peopleModel->getEntity()->find($userId);
        }
        $file = $people && $people->getImage() && is_file($people->getImage()->getPath()) ? $people->getImage()->getPath() : $defaultImgProfile;
        $imageContent = file_get_contents($file);
        $response = $this->getResponse();
        $response->setContent($imageContent);
        $response
                ->getHeaders()
                ->addHeaderLine('Content-Transfer-Encoding', 'binary')
                ->addHeaderLine('Content-Type', exif_imagetype($file) ?: 'image/svg+xml');
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

    public function deleteAdressAction() {
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        $delete = $this->_userModel->deleteAdress($this->params()->fromPost('id'));
        return $delete ? $this->_view : ErrorModel::addError('Error removing this adress!');
    }

    public function deleteDocumentAction() {
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        $delete = $this->_userModel->deleteDocument($this->params()->fromPost('id'));
        return $delete ? $this->_view : ErrorModel::addError('Error removing this document!');
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
        if (!$this->_userModel->loggedIn()) {
            return \Core\Helper\View::redirectToLogin($this->_renderer, $this->getResponse(), $this->getRequest(), $this->redirect());
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

        if ($this->params()->fromPost() && $this->_userModel->loggedIn() && $ddd && $phone) {
            $new_phone = $this->_userModel->addUserPhone($ddd, $phone);
            $this->_view->setVariables(Format::returnData($new_phone));
        } elseif ($this->params()->fromPost()) {
            ErrorModel::addError('The ddd field is required.');
            ErrorModel::addError('The phone field is required.');
        }

        $this->_view->setTerminal(true);
        return $this->_view;
    }

    public function addAdressAction() {
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        $params = $this->params()->fromPost();
        if ($params && $this->_userModel->loggedIn()) {
            $new_adress = $this->_userModel->addUserAdress($params);
            $this->_view->setVariables(Format::returnData(array(
                        'id' => $new_adress->getId(),
                        'street' => $new_adress->getStreet()->getStreet(),
                        'number' => $new_adress->getNumber(),
                        'complement' => $new_adress->getComplement(),
                        'nickname' => $new_adress->getNickname(),
                        'neighborhood' => $new_adress->getStreet()->getNeighborhood()->getNeighborhood(),
                        'cep' => $new_adress->getStreet()->getCep()->getCep(),
                        'city' => $new_adress->getStreet()->getNeighborhood()->getCity()->getCity(),
                        'state' => $new_adress->getStreet()->getNeighborhood()->getCity()->getState()->getState(),
                        'country' => $new_adress->getStreet()->getNeighborhood()->getCity()->getState()->getCountry()->getCountryname()
            )));
        }

        $this->_view->setTerminal(true);
        return $this->_view;
    }

    public function addDocumentAction() {
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        $document = $this->params()->fromPost('document');
        $document_type = $this->params()->fromPost('document-type');

        if ($this->params()->fromPost() && $this->_userModel->loggedIn() && $document && $document_type) {
            $new_document = $this->_userModel->addUserDocument($document, $document_type);
            $this->_view->setVariables(Format::returnData($new_document));
        } elseif ($this->params()->fromPost()) {
            ErrorModel::addError('The document field is required.');
        } else {
            $document_types = $this->_userModel->getDocumentTypes();
            $this->_view->setVariables(array('document_types' => $document_types));
        }

        $this->_view->setTerminal(true);
        return $this->_view;
    }

    public function addEmailAction() {
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);
        $email = $this->params()->fromPost('email');

        if ($this->params()->fromPost() && $this->_userModel->loggedIn() && $email) {
            $new_email = $this->_userModel->addUserEmail($email);
            $this->_view->setVariables(Format::returnData($new_email));
        } elseif ($this->params()->fromPost()) {
            ErrorModel::addError('The email field is required.');
        }

        $this->_view->setTerminal(true);
        return $this->_view;
    }

    public function addUserAction() {
        $this->_userModel = new UserModel();
        $this->_userModel->initialize($this->serviceLocator);

        $username = $this->params()->fromPost('username');
        $password = $this->params()->fromPost('password');
        $confirm_password = $this->params()->fromPost('confirm-password');

        if ($this->params()->fromPost() && $this->_userModel->loggedIn() && $username && $password && $confirm_password) {
            $new_user = $this->_userModel->addUser($username, $password, $confirm_password);
            $this->_view->setVariables(Format::returnData($new_user));
        }

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
        } elseif ($this->_userModel->loggedIn()) {
            return $this->redirect()->toUrl($this->_renderer->basePath('/user/profile'));
        }
        return $this->_view;
    }

}
