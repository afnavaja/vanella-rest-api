<?php

namespace Vanella\Validators;

interface ValidatorInterface
{
    public function handle($args = []);
}

class Validator implements ValidatorInterface
{
    protected $field;
    protected $value;
    protected $customMessage;
    protected $message;
    protected $db;
    protected $table;
    protected $ruleName;

    public function __construct($args = [])
    {
        $this->field = isset($args['field']) ? $args['field'] : null;
        $this->value = isset($args['value']) ? $args['value'] : null;
        $this->customMessage = isset($args['customMessage']) ? $args['customMessage'] : null;
        $this->handle($args);
    }

    /**
     * Handles the validation
     *
     * @param array $args
     *
     * @return array
     */
    public function handle($args = [])
    {

    }

    /**
     * Returns the error message
     */
    public function getMessage()
    {
        return $this->message;
    }
}
