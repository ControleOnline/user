<?php

namespace User\Model;

use Core\Interfaces\ComplexityPasswordInterface;
use Core\Model\ErrorModel;

class ComplexityPasswordmodel implements ComplexityPasswordInterface {

    CONST min_lenght = 8;
    CONST max_lenght = 20;
    CONST min_numbers = 1;
    CONST min_letter = 1;
    CONST min_caps_letter = 1;
    CONST min_symbol = 1;

    protected static function checkMinLenght($password) {
        if (strlen($password) < self::min_lenght) {
            self::addError('Password too short!');
            return false;
        } else {
            return true;
        }
    }

    protected static function checkMaxLenght($password) {
        if (strlen($password) > self::max_lenght) {
            self::addError('Password too long!');
            return false;
        } else {
            return true;
        }
    }

    /**
     * @todo Ajustar para contar o numero
     */
    protected static function checkMinNumbers($password) {
        if (!preg_match('#[0-9]+#', $password)) {
            self::addError(array('message' => 'Password must include at least %1$s numbers! ', 'values' => array(self::min_numbers)));
            return false;
        } else {
            return true;
        }
    }

    /**
     * @todo Ajustar para contar o numero de letras
     */
    protected static function checkMinLetters($password) {


        if (!preg_match('#[a-z]+#', $password)) {
            self::addError('Password must include at least one letter!');
            return false;
        } else {
            return true;
        }
    }

    /**
     * @todo Ajustar para contar o numero de letras maiúsculas
     */
    protected static function checkMinCapsLetters($password) {
        if (!preg_match('#[A-Z]+#', $password)) {
            self::addError('Password must include at least one CAPS!');
            return false;
        } else {
            return true;
        }
    }

    /**
     * @todo Ajustar para contar o numero de símbulos
     */
    protected static function checkMinSymbol($password) {
        if (!preg_match('#\W+#', $password)) {
            self::addError('Password must include at least one symbol!');
            return false;
        } else {
            return true;
        }
    }

    public static function checkPasswordcomplexity($password) {
        self::checkMaxLenght($password);
        self::checkMinCapsLetters($password);
        self::checkMinLenght($password);
        self::checkMinLetters($password);
        self::checkMinNumbers($password);
        self::checkMinSymbol($password);
        return self::getErrors() ? false : true;
    }

    protected static function addError($error) {
        ErrorModel::addError($error);
    }

    public static function getErrors() {
        return ErrorModel::getErrors();
    }

}
