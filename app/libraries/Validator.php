<?php

class Validator {

    private $method;
    private $key;
    private $jsonArr = array();
    private $value;
    private $values = array();
    private $fileTypes = array(
        'img' => array(
            'jpg', 'jpeg',
            'gif', 'png'
        ),
        'doc' => array(
            'pdf', 'docx', 'doc'
        )
    );
    private $patterns = array(
        'alpha' => "/^[\p{L}\s]+$/",
        'alphaNumeric' => "/^[\w.-]+$/",
        'email' => "/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/",
        'password' => "/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d!$%@#£€*?&]{8,}$/",
        'phoneNo' => "/^(\+44\s?7\d{3}|\(?07\d{3}\)?)\s?\d{3}\s?\d{3}|\(?(01|02)\d{3}\)?\s?\d{6}$/",
        'postCode' => "/^[A-Z]{2}(\d{2}|\d[A-Z]?)\s?\d[A-Z]{2}$/",
        'url' => "/^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:\/?#[\]@!\$&'\(\)\*\+,;=.]+$/",
        'username' => "/^[a-zA-Z0-9]+([_\-]?[a-zA-Z0-9])*$/",
    );
    private $errorMessages = array(
        'alpha' => "This field can only contain letters",
        'alphaNumeric' => "This field can only contain letters & numbers",
        'email' => "Email address invalid",
        'password' => "Password is invalid",
        'phoneNo' => "Phone number is invalid",
        'postCode' => "Post code is invalid",
        'url' => "Url is invalid",
        'username' => "Username is invalid",
    );
    private $errors = array();

    /**
     * Sanitise input array before validation
     *
     * @param string $method
     * @param bool $trim
     * @return void
     */
    public static function sanitizeInput($method, $trim = true) {
        $method = strtoupper($method);
        if($method === 'GET') {
            $_GET = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
            if($trim && !empty($_GET)) {
                $_GET = self::trimInput($_GET);
            }
        } elseif ($method === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            if($trim && !empty($_POST)) {
                $_POST = self::trimInput($_POST);
            }
        }
    }

    /**
     * Sanitize custom $this->jsonArr
     * Serperated into another function as it's not intended to be
     * run statically.
     *
     * @param bool $trim
     * @return void
     */
    public function sanitizeJsonArr($trim = true) {
        if($this->method === 'JSON') {
            $this->jsonArr = filter_var_array($this->jsonArr, FILTER_SANITIZE_STRING);
            if($trim && !empty($this->jsonArr)) {
                $this->jsonArr = self::trimInput($this->jsonArr);
            }
        }
    }

    /**
     * Trims a given array
     *
     * @param assoc $inputArr
     * @return assoc
     */
    public static function trimInput($inputArr) {
        foreach ($inputArr as $key => $value) {
            if(is_string($inputArr)) {
                $inputArr[$key] = trim($inputArr[$value]);
            }
        }
        return $inputArr;
    }

    /**
     * Sets the method for the current validation
     *
     * @param string $method
     * @return this
     */
    public function method($method) {
        $method = strtoupper($method);
        if($method === 'GET' || $method === 'POST' || $method === 'JSON') {
            $this->method = $method;
        }
        return $this;
    }

    public function setJsonInput($jsonData) {
        if(is_array($jsonData)) {
            $this->jsonArr = $jsonData;
        }
        return $this;
    }

    /**
     * Sets the value for the current validation
     *
     * @param string $key
     * @return this
     */
    public function value($key) {
        $this->key = $key;
        if($this->method === 'GET') {
            $this->value = $_GET[$key] ?? null;
            $this->setValue();
        } elseif ($this->method === 'POST') {
            $this->value = $_POST[$key] ?? null;
            $this->setValue();
        } elseif ($this->method === 'JSON') {
            $this->value = $this->jsonArr[$key] ?? null;
            $this->setValue();
        }
        return $this;
    }

    /**
     * Allows the user to insert a non-input key => value
     * pair into the validator.
     *
     * @param string $key
     * @param mixed $value
     * @return this
     */
    public function insertValue($key, $value) {
        $this->key = $key;
        $this->value = $value;
        $this->setValue();
        return $this;
    }

    /**
     * Sets a key => value pair in $this->values
     *
     * @return void
     */
    private function setValue() {
        $this->values[$this->key] = $this->value;
    }

    /**
     * Unsets a key => pair in $this->values
     *
     * @return void
     */
    public function unsetValue($key = null) {
        $key = $key ?? $this->key;
        unset($this->values[$key]);
    }

    /**
     * Gets a value from $this->values
     *
     * @param string $key
     * @return string | null
     */
    public function getValue($key = null) {
        $key = $key ?? $this->key;
        return $this->values[$key] ?? null;
    }

    /**
     * Checks if value is a blank string
     * (For stopping errors if not required)
     *
     * @return void
     */
    private function noEntry() {
        return $this->value === '';
    }

    /**
     * Sanitize individual value
     *
     * @param int $filterDefault
     * @return this
     */
    public function sanitize($filterDefault = FILTER_SANITIZE_STRING) {
        $this->value = filter_var($this->value, $filterDefault, FILTER_NULL_ON_FAILURE);
        $this->values[$this->key] = filter_var($this->values[$this->key], $filterDefault, FILTER_NULL_ON_FAILURE);
        return $this;
    }

    /**
     * Trims a specific value in $this->values
     *
     * @return this
     */
    public function trimValue() {
        $this->values[$this->key] = trim($this->value);
        return $this;
    }

    /**
     * Makes a string lower case
     */
    public function toLower() {
        if(is_string($this->value)) {
            $this->values[$this->key] = strtolower($this->value);
        }
        return $this;
    }

    /**
     * Nulls empty strings in the validated results.
     */
    public function nullEmptyStrings() {
        foreach($this->values as $key => $val) {
            if(is_string($val) && $val === "") {
                $this->values[$key] = null;
            }
        }
    }


    /**
     * Checks to see if the value is not empty or null
     *
     * @return this
     */
    public function isRequired() {
        if($this->value === null || $this->noEntry()) {
            $errMsg = "This field is required";
            $this->setError($errMsg);
        }
        return $this;
    }

    /**
     * Matches a pre-defined regex pattern to the value.
     *
     * @param string $pattern
     * @return void
     */
    public function pattern($pattern) {
        if(array_key_exists($pattern, $this->patterns)) {
            $regex = $this->patterns[$pattern];
            if(!$this->noEntry() && $this->value && !preg_match($regex, $this->value)) {
                $this->setError($this->errorMessages[$pattern]);
            }
        } else {
            trigger_error("Invalid pattern provided", E_USER_ERROR);
        }
        return $this;
    }

    /**
     * Matches a user specified regex pattern to the value
     *
     * @param string $customPattern
     * @return void
     */
    public function customPattern($customPattern) {
        $regex = '/'.$customPattern.'/';
        if(!$this->noEntry() && $this->value && !preg_match($regex, $this->value)) {
            $this->setError("Invalid value supplied");
        }
        return $this;
    }

    /**
     * Checks to see if the provided number is an int
     *
     * @return this
     */
    public function isInt() {
        if(!$this->noEntry() && is_numeric($this->value)) {
            // Make sure the value is converted to an int for further validation
            $this->value = filter_var($this->value, FILTER_VALIDATE_INT);
            $this->setValue();
        } else {
            $errMsg = ucwords($this->key)." is not a valid number";
            $this->setError($errMsg);
        }
        return $this;
    }

    /**
     * Checks to see if the provided number is a float
     *
     * @return this
     */
    public function isFloat() {
        if(!$this->noEntry() && is_numeric($this->value)) {
        // Make sure the value is converted to a float for further validation
            $this->value = filter_var($this->value, FILTER_VALIDATE_FLOAT);
            $this->setValue();
        } else {
            $errMsg = ucwords($this->key)." is not a valid float";
            $this->setError($errMsg);
        }
        return $this;
    }

    /**
     * Checks if a valid date is provided
     *
     * @param string $format
     * @return this
     */
    public function isDate($format='d/m/Y') {
        $date = DateTime::createFromFormat($format, $this->value);
        if(!$this->noEntry() && !$date) {
            $errMsg = "Invalid date provided";
            $this->setError($errMsg);
        }
        return $this;
    }
    
    /**
     * Checks to see if date provided is after current date
     *
     * @return this
     */
    public function isFutureDate($format='d/m/Y') {
        $date = DateTime::createFromFormat($format, $this->value);
        $today = new DateTime('now');
        if(!$this->noEntry() && $date < $today) {
            $errMsg = "Date can't be previous to today";
            $this->setError($errMsg);
        }
        return $this;
    }

    /**
     * Checks to see if value or char count is below a provided number.
     *
     * @param int $minVal
     * @return this
     */
    public function min($minVal, $errMsg = null) {
        $value = (is_string($this->value)) ? strlen($this->value) : $this->value;
        if($value < $minVal) {
            $errMsg = $errMsg ?? ((is_string($this->value)) ? "Character count lower than $minVal" : "Value lower than $minVal");
            $this->setError($errMsg);
        }
        return $this;
    }

    /**
     * Checks to see if value or char count is below a provided number.
     *
     * @param int $minVal
     * @return this
     */
    public function max($maxVal, $errMsg = null) {
        $value = (is_string($this->value)) ? strlen($this->value) : $this->value;
        if($value > $maxVal) {
            $errMsg = $errMsg ?? ((is_string($this->value)) ? "Character count higher than $maxVal" : "Value higher than $maxVal");
            $this->setError($errMsg);
        }
        return $this;
    }

    /**
     * Checks to see if a supplied value matches the input value
     *
     * @param mixed $match
     * @return this
     */
    public function match($match, $errVal=null, $errMsg = null) {
        if($this->value !== $match) {
            $errMsg = $errMsg ?? (($errVal) ? ucwords($errVal)."s do not match" : ucwords($this->key)."s do not match");
            $this->setError($errMsg);
        }
        return $this;
    }

    /**
     * Checks to see if a supplied values match the input value
     *
     * @param array $matches
     * @return this
     */
    public function multiMatch($matches, $errVal=null, $errMsg = null) {
        $err = true;
        foreach ($matches as $match) {
            if($this->value === $match) {
                $err = false;
                break;
            }
        }
        if($err) {
            $errMsg = $errMsg ?? (($errVal) ? ucwords($errVal)."s do not match" : ucwords($this->key)."s do not match");
            $this->setError($errMsg);
        }
        return $this;
    }

    /**
     * Checks if a valid filetype is being used
     *
     * @param string $fileType
     * @return this
     */
    public function fileType($fileType) {
        $ext = self::getExtension($this->value);
        if(!$this->noEntry() && !in_array($ext, $this->fileTypes[$fileType])) {
            $errMsg = "Invalid file type";
            $this->setError($errMsg);
        }
        return $this;
    }

    /**
     * Checks if a valid filetype is being used
     *
     * @param string $match
     * @return this
     */
    public function matchExtensions($match) {
        $ext = self::getExtension($this->value);
        if(!$this->noEntry() && $ext !== $match) {
            $errMsg = "Invalid file type";
            $this->setError($errMsg);
        }
        return $this;
    }

    /**
     * Returns the extension of a given file
     *
     * @param string $filename
     * @return string | null
     */
    public static function getExtension($filename) {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        return $ext;
    }

    /**
     * Checks to see if inputs are valid or not.
     *
     * @return boolean
     */
    public function isValid() {
        return (count($this->errors) === 0);
    }

    /**
     * Sets an error message in the error array
     * Also removes value from $this->values
     *
     * @param string $key
     * @param string $errMsg
     * @return void
     */
    private function setError($errMsg) {
        $errKey = $this->key.'Err';
        if(!array_key_exists($errKey, $this->errors)) {
            $this->errors[$errKey] = $errMsg;
        }
        $this->unsetValue();
    }

    /**
     * Returns the error array
     *
     * @return assoc
     */
    public function getValidInputs() {
        return $this->values;
    }

    /**
     * Returns the error array
     *
     * @return assoc
     */
    public function getErrors() {
        return $this->errors;
    }
}
