<?php

namespace Vanella\Validators;

interface ValidatorInterface
{
    public function handle($field, $value, $customMessage);
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
        $this->handle($this->field, $this->value, $this->customMessage);
    }

    public function handle($field, $value, $customMessage)
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
