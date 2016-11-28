<?php

namespace User\Model;

use Core\Model\ErrorModel;
use Core\Interfaces\LoginInterface;
use Core\Model\DefaultModel;
use Zend\Session\Container;
use User\Model\ComplexityPasswordmodel;
use Core\Helper\Format;

class UserModel extends DefaultModel implements LoginInterface {

    /**
     * @var \Zend\Session\Container
     */
    protected $_session;
    protected $_password;
    protected $_hash;
    protected $_salt;

    const COST = 10;

    public function __construct() {
        $this->_salt = sprintf("$2a$%02d$", self::COST) . strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
        $this->_session = new Container('user');
    }

    public function logout() {
        unset($this->_session->user);
        return $this->_session;
    }

    public function checkUserData($username, $password, $confirm_password) {
        $this->userExists($username);
        $this->comparePasswords($password, $confirm_password);
        return $this->getErrors() ? false : true;
    }

    protected function persistData($username, $name, $password) {

        $entity_people = new \Entity\People();
        $entity_people->setName($name);
        $this->_em->persist($entity_people);

        $entity_user = new \Entity\User();
        $entity_user->setPeople($entity_people);
        $entity_user->setUsername($username);
        $entity_user->setHash($this->getHash($password));
        $this->_em->persist($entity_user);
    }

    protected function createUser($username, $name, $password, $confirm_password) {

        if (!$this->checkUserData($username, $password, $confirm_password)) {
            return;
        }
        try {
            $entity_people = $this->persistData($username, $name, $password);
            $this->_em->flush();
            $this->_em->clear();
            return $entity_people;
        } catch (Exception $e) {
            ErrorModel::addError(array('code' => $e->getCode(), 'message' => 'Error on create a new user'));
            ErrorModel::addError(array('code' => $e->getCode(), 'message' => $e->getMessage()));
            $this->_em->rollback();
        }
    }

    public function changeUser($username, $name, $password, $confirm_password) {
        
    }

    public function createAccount($username, $name, $password, $confirm_password) {
        $entity_people = $this->createUser($username, $name, $password, $confirm_password);
        $entity_people ? $this->login($username, $password) : false;
        return $entity_people;
    }

    public function userExists($username) {
        $user = $this->entity->findOneBy(array('username' => $username));
        if ($user) {
            $this->addError(array('message' => 'User %1$s already exists!', 'values' => array('user' => $username)));
        }
        return $user;
    }

    public function login($username, $password) {
        $storePassword = $this->getStorePassword($username);
        if ($storePassword && hash_equals($storePassword, crypt($password, $storePassword))) {
            $user = $this->entity->findOneBy(array('username' => $username));
            $this->_session->user = new \stdClass();
            $this->_session->user->id = $user->getId();
            $this->_session->user->username = $user->getUsername();
            return $this->_session;
        } else {
            $this->addError('Login incorrect!');
            return false;
        }
    }

    protected function comparePasswords($password, $confirm_password) {
        $return = true;
        if ($password !== $confirm_password) {
            $this->addError('New password and password verificarion don\'t match');
            $return = false;
        }
        if (!$password || !$confirm_password) {
            $this->addError('Need all fields');
            $return = false;
        }
        return $return;
    }

    public function changePassword($username, $oldPassword, $newPassword, $confirm_password) {
        $this->checkPasswordComplexity($newPassword);
        if (!$this->login($username, $oldPassword)) {
            $this->addError('Incorrect old password');
        }
        $this->comparePasswords($newPassword, $confirm_password);

        if ($this->getErrors()) {
            return false;
        } else {
            $this->setStorePassword($username, $newPassword);
            return true;
        }
    }

    protected function checkPasswordComplexity($password) {
        return ComplexityPasswordmodel::checkPasswordcomplexity($password);
    }

    protected function setStorePassword($username, $password) {
        $entity = $this->entity->findOneBy(array('username' => $username));
        $hash = $this->getHash($password);
        $entity->setHash($hash);
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    protected function getStorePassword($username) {
        $user = $this->entity->findOneBy(array('username' => $username));
        if ($user) {
            return $user->getHash();
        } else {
            self::addError(array('message' => 'Cannot find %1$s', 'values' => array($username)));
        }
    }

    public function loggedIn() {
        return $this->_session->user ? true : false;
    }

    public function getLoggedUser() {
        if ($this->loggedIn()) {
            return $this->entity->find($this->_session->user->id);
        }
    }

    public function getUserSession() {
        return $this->_session->user;
    }

    protected function getHash($password) {
        return crypt($password, $this->_salt);
    }

    protected function addError($error) {
        ErrorModel::addError($error);
        return $this;
    }

    public function getErrors() {
        return ErrorModel::getErrors();
    }

}