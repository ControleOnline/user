<?php

namespace User\Model;

use Core\Interfaces\LoginInterface;
use Core\Model\DefaultModel;
use Zend\Session\Container;
use User\Model\ComplexityPasswordmodel;
use Core\Model\AdressModel;

class UserModel extends DefaultModel implements LoginInterface {

    /**
     * @var \Zend\Session\Container
     */
    protected $_session;

    /**
     * @var \Core\Model\AdressModel
     */
    protected $_adressModel;
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

    public function initialize(\Zend\ServiceManager\ServiceManager $serviceLocator) {
        $this->_adressModel = new AdressModel();
        $this->_adressModel->initialize($serviceLocator);
        parent::initialize($serviceLocator);
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

    /**
     * @return \Core\Entity\Adress
     */
    public function addUserAdress(array $params) {
        if ($this->_adressModel->checkAdressData($params)) {
            try {
                $entity_adress = $this->_adressModel->addPeopleAdress($this->getLoggedUserPeople(), $params);
                $this->_em->flush();
                $this->_em->clear();
            } catch (Exception $e) {
                $this->addError(array('code' => $e->getCode(), 'message' => 'Error on create a new adress'));
                $this->addError(array('code' => $e->getCode(), 'message' => $e->getMessage()));
                $this->_em->rollback();
            }
            return $entity_adress;
        }
    }

    public function addUserPhone($ddd, $phone) {
        $current_user = $this->getLoggedUser();
        if (!$this->getErrors()) {
            $entity_phone = new \Core\Entity\Phone();
            $entity_phone->setPeople($current_user->getPeople());
            $entity_phone->setDdd($ddd);
            $entity_phone->setPhone($phone);
            $entity_phone->setConfirmed(false);
            $this->_em->persist($entity_phone);
            $this->_em->flush();
            $this->_em->clear();
            return array(
                'id' => $entity_phone->getId(),
                'ddd' => $entity_phone->getDdd(),
                'phone' => $entity_phone->getPhone(),
                'confirmed' => $entity_phone->getConfirmed()
            );
        }
    }

    public function addUserEmail($email) {
        $current_user = $this->getLoggedUser();
        $this->emailExists($email);
        if (!$this->getErrors()) {
            $entity_email = new \Core\Entity\Email();
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

    public function getDocumentTypeExists($document_type) {
        $entity = $this->_em->getRepository('\Core\Entity\DocumentType');
        $doc = $entity->findOneBy(array(
            'documentType' => $document_type,
            'peopleType' => 'F'
        ));
        if (!$doc) {
            $this->addError('This type of document does not exist');
        }

        return $doc;
    }

    public function addUserDocument($document, $document_type) {
        $current_user = $this->getLoggedUser();
        $documentType = $this->getDocumentTypeExists($document_type);
        $this->documentExists($document, $documentType);
        if (!$this->getErrors()) {
            $entity = new \Core\Entity\Document();
            $entity->setPeople($current_user->getPeople());
            $entity->setDocument($document);
            $entity->setDocumentType($documentType);
            $this->_em->persist($entity);

            $this->_em->flush();
            $this->_em->clear();
            return array(
                'id' => $entity->getId(),
                'document' => $entity->getDocument(),
                'document_type' => $entity->getDocumentType()->getDocumentType(),
                'image' => $entity->getImage() ? $entity->getImage()->getUrl() : null
            );
        }
    }

    public function addUser($username, $password, $confirm_password) {
        $current_user = $this->getLoggedUser();

        if ($this->checkUserData($username, microtime(), $password, $confirm_password)) {
            $entity_user = new \Core\Entity\User();
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

        $entity_people = new \Core\Entity\People();
        $entity_people->setName($name);
        $entity_people->setPeopleType('F');
        $entity_people->setAlias('');
        $this->_em->persist($entity_people);

        $entity_email = new \Core\Entity\Email();
        $entity_email->setPeople($entity_people);
        $entity_email->setEmail($email);
        $this->_em->persist($entity_email);

        $entity_user = new \Core\Entity\User();
        $entity_user->setPeople($entity_people);
        $entity_user->setUsername($username);
        $entity_user->setHash($this->getHash($password));
        $this->_em->persist($entity_user);

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
            $this->addError(array('code' => $e->getCode(), 'message' => 'Error on create a new user'));
            $this->addError(array('code' => $e->getCode(), 'message' => $e->getMessage()));
            $this->_em->rollback();
        }
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

    public function getDocumentTypes() {
        $entity = $this->_em->getRepository('\Core\Entity\DocumentType');
        return $entity->findBy(array('peopleType' => 'F'), array('documentType' => 'ASC'), 100);
    }

    public function documentExists($document, $document_type) {
        $entity = $this->_em->getRepository('\Core\Entity\Document');
        $doc = $entity->findOneBy(array('document' => $document));

        $documentType = $entity->findOneBy(array(
            'documentType' => $document_type,
            'people' => $this->getLoggedUser()->getPeople()
        ));

        if ($documentType) {
            $this->addError(array('message' => 'Document type (%1$s) already added!', 'values' => array('docType' => $document_type->getDocumentType())));
        }
        if ($doc) {
            $this->addError(array('message' => 'Document %1$s in use!', 'values' => array('doc' => $document)));
        }
        return $doc;
    }

    public function emailExists($email) {
        $entity_email = $this->_em->getRepository('\Core\Entity\Email');
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
        self::$_company = self::$_company ?: $this->getLoggedUser() && $this->getLoggedUser()->getPeople() && count($this->getLoggedUser()->getPeople()->getPeopleEmployee()) > 0 ? $this->getLoggedUser()->getPeople()->getPeopleEmployee()[0]->getCompany() : null;
        return self::$_company;
    }

    public function deletePhone($id) {
        if (!$this->loggedIn()) {
            $this->addError('You do not have permission to delete this!');
        } elseif (!$id) {
            $this->addError('Phone id not informed!');
        } elseif (count($this->getLoggedUser()->getPeople()->getPhone()) < 2) {
            $this->addError('You need at least one phone. Please add another phone before removing this one.');
        } else {
            $entity = $this->_em->getRepository('\Core\Entity\Phone')->findOneBy(array(
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

    public function deleteAdress($id) {
        if (!$this->loggedIn()) {
            $this->addError('You do not have permission to delete this!');
        } elseif (!$id) {
            $this->addError('Adress id not informed!');
        } elseif (count($this->getLoggedUser()->getPeople()->getAdress()) < 2) {
            $this->addError('You need at least one adress. Please add another adress before removing this one.');
        } else {
            $entity = $this->_em->getRepository('\Core\Entity\Adress')->findOneBy(array(
                'id' => $id,
                'people' => $this->getLoggedUser()->getPeople()
            ));
            if ($entity) {
                $entity->setPeople(null);
                $this->_em->persist($entity);
                $this->_em->flush();
                $this->_em->clear();
                return true;
            } else {
                return false;
            }
        }
    }

    public function deleteDocument($id) {
        if (!$this->loggedIn()) {
            $this->addError('You do not have permission to delete this!');
        } elseif (!$id) {
            $this->addError('Document id not informed!');
        } else {
            $entity = $this->_em->getRepository('\Core\Entity\Document')->findOneBy(array(
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

    public function deleteEmail($id) {
        if (!$this->loggedIn()) {
            $this->addError('You do not have permission to delete this!');
        } elseif (!$id) {
            $this->addError('Email id not informed!');
        } elseif (count($this->getLoggedUser()->getPeople()->getEmail()) < 2) {
            $this->addError('You need at least one registered e-mail. Please add another email before removing this one.');
        } else {
            $entity = $this->_em->getRepository('\Core\Entity\Email')->findOneBy(array(
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
            $this->addError('You do not have permission to delete this!');
        } elseif (!$id) {
            $this->addError('User id not informed!');
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
            $this->addError('You can not delete the user you are logged in to!');
            return false;
        }
    }

    /**
     * @return \Core\Entity\People
     */
    public function getLoggedUserPeople() {
        if ($this->loggedIn()) {
            self::$_user_people = self::$_user_people ? self::$_user_people : $this->getLoggedUser() && get_class($this->getLoggedUser()) == 'Core\Entity\User' ? $this->getLoggedUser()->getPeople() : false;
            return self::$_user_people;
        }
    }

    /**
     * @return \Core\Entity\User
     */
    public function getLoggedUser() {
        if ($this->loggedIn()) {
            self::$_user = self::$_user ?: $this->entity->find($this->_session->user->id);
        } else {
            self::$_user = false;
        }
        return self::$_user;
    }

    public function getUserSession() {
        return $this->_session->user;
    }

    protected function getHash($password) {
        return crypt($password, $this->_salt);
    }

}
