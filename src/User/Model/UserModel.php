<?php

namespace User\Model;

use Core\Model\ErrorModel;
use Core\Interfaces\LoginInterface;
use Core\Model\DefaultModel;
use Zend\Session\Container;
use User\Model\ComplexityPasswordmodel;

class UserModel extends DefaultModel implements LoginInterface {

    /**
     * @var \Zend\Session\Container
     */
    protected $_session;
    protected $_password;
    protected $_hash;
    protected $_salt;
    protected static $_user;
    protected static $_user_people;
    protected static $_company;

    const COST = 10;

    public function __construct() {
        $this->_salt = sprintf("$2a$%02d$", self::COST) . strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
        $this->_session = new Container('user');
    }

    public function logout() {
        unset($this->_session->user);
        return $this->_session;
    }

    public function checkUserData($username, $email, $password, $confirm_password) {
        $this->userExists($username);
        $this->emailExists($email);
        $this->comparePasswords($password, $confirm_password);
        return $this->getErrors() ? false : true;
    }

    public function addUserEmail($email) {
        $current_user = $this->getLoggedUser();
        $this->emailExists($email);
        if (!$this->getErrors()) {
            $entity_email = new \Entity\Email();
            $entity_email->setPeople($current_user->getPeople());
            $entity_email->setEmail($email);
            $entity_email->setConfirmed(false);
            $this->_em->persist($entity_email);

            $this->_em->flush();
            $this->_em->clear();
            return array(
                'id' => $entity_email->getId(),
                'email' => $entity_email->getEmail(),
                'confirmed' => $entity_email->getConfirmed()
            );
        }
    }

    public function addUser($username, $password, $confirm_password) {
        $current_user = $this->getLoggedUser();

        if ($this->checkUserData($username, microtime(), $password, $confirm_password)) {
            $entity_user = new \Entity\User();
            $entity_user->setPeople($current_user->getPeople());
            $entity_user->setUsername($username);
            $entity_user->setHash($this->getHash($password));
            $this->_em->persist($entity_user);

            $this->_em->flush();
            $this->_em->clear();
            return array(
                'id' => $entity_user->getId(),
                'username' => $entity_user->getUsername()
            );
        }
    }

    protected function persistData($username, $email, $name, $password) {

        $entity_people = new \Entity\People();
        $entity_people->setName($name);
        $this->_em->persist($entity_people);

        $entity_email = new \Entity\Email();
        $entity_email->setPeople($entity_people);
        $entity_email->setEmail($email);
        $this->_em->persist($entity_email);


        $entity_user = new \Entity\User();
        $entity_user->setPeople($entity_people);
        $entity_user->setUsername($username);
        $entity_user->setHash($this->getHash($password));
        $this->_em->persist($entity_user);


        $entity_employee = new \Entity\PeopleEmployee();
        $entity_employee->setEmployee($entity_people);
        $entity_employee->setCompany($this->getPeopleCompany());

        $this->_em->persist($entity_employee);

        return $entity_user;
    }

    protected function createUser($username, $email, $name, $password, $confirm_password) {

        if (!$this->checkUserData($username, $email, $password, $confirm_password)) {
            return;
        }
        try {
            $entity_people = $this->persistData($username, $email, $name, $password);
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

    public function createAccount($username, $email, $name, $password, $confirm_password) {
        $entity_people = $this->createUser($username, $email, $name, $password, $confirm_password);
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

    public function emailExists($email) {
        $entity_email = $this->_em->getRepository('\Entity\Email');
        $mail = $entity_email->findOneBy(array('email' => $email));
        if ($mail) {
            $this->addError(array('message' => 'Email %1$s in use!', 'values' => array('user' => $email)));
        }
        return $mail;
    }

    public function login($username, $password) {
        $this->logout();
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

    public function getUserCompany() {
        self::$_company = self::$_company ?: $this->getLoggedUser()->getPeople()->getPeopleEmployee()[0]->getCompany();
        return self::$_company;
    }

    public function deleteEmail($id) {
        if (!$this->loggedIn()) {
            ErrorModel::addError('You do not have permission to delete this!');
        } elseif (!$id) {
            ErrorModel::addError('Email id not informed!');
        } elseif (count($this->getLoggedUser()->getPeople()->getEmail()) < 2) {
            ErrorModel::addError('You need at least one registered e-mail. Please add another email before removing this one.');
        } else {
            $entity = $this->_em->getRepository('\Entity\Email')->findOneBy(array(
                'id' => $id,
                'people' => $this->getLoggedUser()->getPeople()
            ));
            if ($entity) {
                $this->_em->remove($entity);
                $this->_em->flush();
                $this->_em->clear();
                return true;
            } else {
                return false;
            }
        }
    }

    public function delete($id) {
        if (!$this->loggedIn()) {
            ErrorModel::addError('You do not have permission to delete this!');
        } elseif (!$id) {
            ErrorModel::addError('User id not informed!');
        } elseif ($id != $this->getLoggedUser()->getId()) {
            $entity = $this->entity->findOneBy(array(
                'id' => $id,
                'people' => $this->getLoggedUser()->getPeople()
            ));
            if ($entity) {
                $this->_em->remove($entity);
                $this->_em->flush();
                $this->_em->clear();
                return true;
            } else {
                return false;
            }
        } else {
            ErrorModel::addError('You can not delete the user you are logged in to!');
            return false;
        }
    }

    public function getLoggedUserPeople() {
        self::$_user_people = self::$_user_people ? self::$_user_people : $this->getLoggedUser() ? $this->entity->findBy(array('people' => $this->getLoggedUser()->getPeople())) : false;
        return self::$_user_people;
    }

    public function getPeopleCompany() {
        return $this->getLoggedUserPeople() ?: $this->_em->getRepository('\Entity\People')->find(1);
    }

    public function getLoggedUser() {
        if ($this->loggedIn()) {
            self::$_user = self::$_user ?: $this->entity->find($this->_session->user->id);
            return self::$_user;
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
