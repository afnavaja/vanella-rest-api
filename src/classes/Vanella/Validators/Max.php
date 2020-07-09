<?php

namespace Vanella\Validators;

class Max extends Validator
{
    protected $max;

    /**
     * Class construct
     *
     * @param array $args
     *
     * @return void
     */
    public function __construct($args = [])
    {
        $this->max = isset($args['args']['max']) ? $args['args']['max'] : 12;
        $this->ruleName = 'isMax';
        parent::__construct($args);
    }

    /**
     * Checks if the value is alphanumeric
     *
     * @param string $field
     * @param string $value
     * @param string $customMessage
     *
     * @return array
     */
    public function handle($field, $value, $customMessage = null)
    {
        if (strlen($value) > $this->max && !empty($value)) {
            $message = !is_null($customMessage) ? $customMessage : 'This ' . $field . ' field should only be ' . $this->max . ' characters max.';
            $this->message = [
                'rule' => $this->ruleName,
                'message' => $message,
            ];
        }

        return [];
    }
}
