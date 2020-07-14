<?php

namespace Vanella\Handlers;

class Validations
{
    protected $args;
    protected $isValidationActivated;
    protected $db;
    protected $table;
    protected $errorData = [];
    protected $validators;
    protected $refreshToken;

    public function __construct($args = [])
    {
        $this->args = $args;
        $this->isValidationActivated = isset($this->args['isValidationActivated']) && $this->args['isValidationActivated'] ? $this->args['isValidationActivated'] : false;
        $this->db = isset($this->args['db']) && $this->args['db'] ? $this->args['db'] : null;
        $this->table = isset($this->args['table']) && $this->args['table'] ? $this->args['table'] : null;
        $this->refreshToken = isset($this->args['refreshToken']) && $this->args['refreshToken'] ? $this->args['refreshToken'] : null;
        $this->validators = $this->args['validators'];

        if ($this->isValidationActivated) {
            $this->runValidation($this->args);
        }
    }

    /**
     * Loads the specific endpointgroup
     * validation configuration
     *
     * @return array
     */
    protected function _loadValidationConfig()
    {
        try {
            $path = '../restful/Validations/' . $this->args['endpointGroup'];

            if (!file_exists($path)) {
                Helpers::renderAsJson([
                    'success' => false,
                    'error' => 'Looks like you have not set your Validations for this endpoint yet. Please run [php vanella add:validations]',
                ], 500);
            }

            $allFiles = scandir($path); // The path for your endpointgroup validations
            $fields = array_diff($allFiles, array('.', '..'));
            $validationConfig = [];

            if (!empty($fields)) {
                foreach ($fields as $field) {
                    $rulesFullPath = $path . '/' . $field;
                    $key = substr($field, 0, -4); // We will be removing the .php extension
                    $validationConfig[$key] = require_once $rulesFullPath; // We will include what's inside the file
                }
            }

            return $validationConfig;
        } catch (\Exception $e) {
            Helpers::renderAsJson([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run the validation
     *
     * @param boolean $isValidationActivated
     *
     * @return void
     */
    public function runValidation()
    {

        if ($this->isValidationActivated) {

            $requestData = $this->args['requestData'];
            $validationRules = $this->_loadValidationConfig();
            $tableColumns = $this->args['tableColumns'];

            if (empty($requestData)) {
                Helpers::renderAsJson([
                    'success' => false,
                    'message' => 'Empty request data. No values are passed',
                ], 400); // Bad request
            }

            if (empty($validationRules)) {
                Helpers::renderAsJson([
                    'success' => false,
                    'message' => 'No validation rules are specified. Please add at least 1 validation rule.',
                ], 400); // Bad request
            }

            foreach ($validationRules as $field => $rules) {
                // Must exist in the table columns
                if (in_array($field, $tableColumns)) {

                    // Value from the request data
                    $value = isset($requestData[$field]) ? $requestData[$field] : null;

                    // Iterate through the validators
                    foreach ($this->validators as $validatorKey => $validatorClass) {

                        // This group of validators belongs to a key => value pair type
                        // of validator configuration i.e (max => 10, min => 5)
                        if (in_array($validatorKey, ['min', 'max'])) {
                            if (array_key_exists($validatorKey, $rules)) {
                                $errorValue = new $validatorClass([
                                    'class' => $validatorClass,
                                    'field' => $field,
                                    'value' => $value,
                                    'args' => [
                                        $validatorKey => $rules[$validatorKey],
                                    ],
                                ]);
                                $errorMessage = $errorValue->getMessage();
                                if (!empty($errorMessage)) {
                                    $this->errorData[$field][] = $errorMessage;
                                }
                            }
                        } else {
                            foreach ($rules as $rule) {
                                // Run validator if it matches the key
                                if ($rule === $validatorKey) {
                                    // Dynamically load the validator class
                                    $errorValue = new $validatorClass([
                                        'class' => $validatorClass,
                                        'db' => $this->db,
                                        'table' => $this->table,
                                        'field' => $field,
                                        'value' => $value,
                                    ]);

                                    // Run the validations
                                    $this->runSpecifiedValidation($validatorKey, $rules, $errorValue->getMessage(), $field);
                                }
                            }
                        }
                    }
                }
            }

            // Render all errors
            if (!empty($this->errorData)) {
                $data = [
                    'inputErrors' => $this->errorData,
                ];

                if (!is_null($this->refreshToken)) {
                    $data = array_merge($data, $this->refreshToken);
                }

                Helpers::renderAsJson($data, 400);
            }
        }
    }

    /**
     * Runs a specified validation and sets it to the
     * errordata class variable
     *
     * @param string $ruleName
     * @param array $rules
     * @param array $validationResult
     * @param string $field
     *
     * @return void
     */
    private function runSpecifiedValidation($ruleName, $rules, $validationResult, $field)
    {
        if (in_array($ruleName, $rules)) {
            // If there is an error add it to the error
            if (!empty($validationResult)) {
                $this->errorData[$field][] = $validationResult;
            }
        }
    }

}
