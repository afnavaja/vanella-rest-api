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
     * @param array $args
     *
     * @return array
     */
    public function handle($args = [])
    {
        if (!empty($args['value']) && in_array($_SERVER['REQUEST_METHOD'], ['POST'])) {
            $result = $this->db
                ->select($this->table)
                ->where($args['field'], $args['value'])
                ->one();
            if (!empty($result)) {
                $message = isset($args['customMessage']) ? $args['customMessage'] : 'This ' . $args['value'] . ' already exists in the database.';
                $this->message = [
                    'rule' => $this->ruleName,
                    'message' => $message,
                ];
            }
        }
    }
}
