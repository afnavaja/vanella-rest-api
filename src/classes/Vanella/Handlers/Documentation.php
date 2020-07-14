<?php

namespace Vanella\Handlers;

use Vanella\Core\Controller;

class Documentation
{
    protected $_args;
    public function __construct($args = [])
    {
        $this->_args = $args;
        $this->index();
    }

    public function index()
    {
        $data['config'] = $this->_args['config'];
        
        Controller::render(__DIR__ . '/views/index', $data);
    }
}
