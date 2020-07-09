<?php

namespace Vanella\Validators;

class Unique extends Validator
{
    /**
     * Class construct
     *
     * @param array $args
     *
     * @return void
     */
    public function __construct($args = [])
    {
        $this->db = isset($args['db']) ? $args['db'] : null;
        $this->table = isset($args['table']) ? $args['table'] : null;
        $this->ruleName = 'isUnique';
        parent::__construct($args);
    }

    /**
     * Checks if the value already exists
     *
     * @param string $field
     * @param string $value
     * @param string $customMessage
     *
     * @return array
     */
    public function handle($field, $value, $customMessage = null)
    {
        if (!empty($value)) {
            $result = $this->db
                ->select($this->table)
                ->where($field, $value)
                ->one();
            if (!empty($result)) {
                $message = !is_null($customMessage) ? $customMessage : 'This ' . $value . ' already exists in the database.';
                $this->message = [
                    'rule' => $this->ruleName,
                    'message' => $message,
                ];
            }
        }
    }
}
