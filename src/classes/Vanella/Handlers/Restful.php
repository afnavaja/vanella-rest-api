<?php

namespace Vanella\Handlers;

use Vanella\Core\Controller;
use Vanella\Core\Database;
use Vanella\Core\Url;
use Vanella\Handlers\Authentication;

interface RestfulInterface
{
    public function create();
    public function read();
    public function update();
    public function delete();
}

class Restful extends Authentication implements RestfulInterface
{
    protected $tableName = "";
    protected $id = null;
    protected $defaultDbPrimaryKeyName = 'id';
    protected $defaultDbCreatedAtName = 'created_at';
    protected $defaultDbUpdatedAtName = 'updated_at';
    protected $defaultSuccessMessageRead;
    protected $defaultSuccessMessageCreate;
    protected $defaultSuccessMessageUpdate;
    protected $defaultSuccessMessageDelete;
    protected $childClass;
    protected $pageNumber;
    protected $limit = 10;
    protected $totalPages;
    private $columnPresets = ['limit', 'page', 'total_items', 'total_pages'];

    public function __construct($args = [])
    {
        parent::__construct($args);
        $this->_loadConfig($args);
        date_default_timezone_set($this->restConfig['timezone']);
    }

    /**
     * Takes you to the page of the enpoint list
     *
     * @return void
     */
    public function endpoints()
    {
        $data['config'] = $this->mainConfig;
        $data['endPointList'] = $this->_endpointList();
        $data['endpointGroup'] = $this->endpointGroup;
        Controller::render(__DIR__ . '/views/api.documentation', $data);
    }

    /**
     * This is the default access rule when you
     * extend to Handlers\Restful class.
     *
     * @return void
     */
    public function accessRule()
    {
        $this->_registerEndpointToAccessRule('read', [
            'isAccessPageViaAccessToken' => false,
        ])->_registerEndpointToAccessRule('create', [
            'isAccessPageViaAccessToken' => true,
        ])->_registerEndpointToAccessRule('update', [
            'isAccessPageViaAccessToken' => true,
        ])->_registerEndpointToAccessRule('delete', [
            'isAccessPageViaAccessToken' => true,
        ])->_registerEndpointToAccessRule('endpoints', [
            'isAccessPageViaAccessToken' => false,
        ]);
    }

    /**
     * Performs the read method
     * of the rest api call
     *
     * @return void
     */
    public function read()
    {
        try {

            $result = null;
            $success = false;
            $message = null;
            $response_code = null;

            if ($_SERVER['REQUEST_METHOD'] == 'GET') {
                $db = new Database(
                    $this->dbConfig['db_host'],
                    $this->dbConfig['db_username'],
                    $this->dbConfig['db_password'],
                    $this->dbConfig['db_name']
                );

                $db->select($this->tableName);

                // Just in case the user passes an id
                if ($this->id) {
                    $db->where($this->defaultDbPrimaryKeyName, $this->id);
                }

                // This where only performs equality
                if (!empty($_GET)) {
                    foreach ($_GET as $column => $value) {
                        if (!in_array($column, $this->columnPresets)) {
                            $db->where($column, $value);
                        }
                    }
                }

                // Default limit is 10 and offset is 0
                // Only activate this if there is a pagination
                if ($this->pageNumber) {
                    $offset = ($this->pageNumber * $this->limit) - $this->limit;
                    $db->limit(intval($this->limit))->offset(intval($offset));
                } else {
                    $db->limit($this->limit);
                }

                // Get the results
                $result = $db->all();
                $success = true;
                $message = (isset($this->defaultSuccessMessageRead) ? $this->defaultSuccessMessageRead : $message);
                $response_code = 200; // Ok
            } else {
                $message = 'Only GET methods are allowed';
                $response_code = 405; // Method not allowed
            }

            $this->_displayResponse($result, $success, $message, $response_code, 'GET');

        } catch (\Exception $e) {
            $this->_displayResponse(null, false, $e->getMessage(), 500, 'GET');
        }
    }

    /**
     * Performs the create method
     * of the rest api call
     *
     * @return void
     */
    public function create()
    {
        try {

            $result = null;
            $success = false;
            $message = null;
            $response_code = null;

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $post = $this->_cleanedPostData($_POST);

                if (!$this->id && !empty($post)) { // Always check if the id is there

                    $db = new Database(
                        $this->dbConfig['db_host'],
                        $this->dbConfig['db_username'],
                        $this->dbConfig['db_password'],
                        $this->dbConfig['db_name']
                    );

                    $date = new \DateTime();
                    $_POST = array_merge($_POST, [
                        $this->defaultDbCreatedAtName => $date->format('Y-m-d H:i:s'),
                        $this->defaultDbUpdatedAtName => $date->format('Y-m-d H:i:s'),
                    ]);

                    // Insert the data
                    $result = $db->insert($this->tableName, $post)->execute();
                    $success = true;
                    $message = 'Succesfully added record with an ' . $this->defaultDbPrimaryKeyName . ' of ' . $result . '.';
                    $message = (isset($this->defaultSuccessMessageCreate) ? $this->defaultSuccessMessageCreate : $message);
                    $response_code = 201; // Created
                } else {
                    if (empty($_POST)) {
                        $message = 'No data has been passed.';
                        $response_code = 400; // Bad Request
                    }
                }
            } else {
                $message = 'Only POST methods are allowed';
                $response_code = 405; // Method not allowed
            }

            $this->_displayResponse($result, $success, $message, $response_code, 'POST');

        } catch (\Exception $e) {
            $this->_displayResponse(null, false, $e->getMessage(), 500, 'POST');
        }
    }

    /**
     * Performs an api action update
     *
     * @return void
     */
    public function update()
    {
        try {

            $result = null;
            $success = false;
            $message = null;
            $response_code = null;

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {

                if ($this->id) { // Always check if the id is there
                    $db = new Database(
                        $this->dbConfig['db_host'],
                        $this->dbConfig['db_username'],
                        $this->dbConfig['db_password'],
                        $this->dbConfig['db_name']
                    );

                    $date = new \DateTime();
                    $_POST = array_merge($_POST, [
                        $this->defaultDbUpdatedAtName => $date->format('Y-m-d H:i:s'),
                    ]);

                    // Update the data
                    $db->update($this->tableName, $this->_cleanedPostData($_POST))
                        ->where($this->defaultDbPrimaryKeyName, $this->id)
                        ->execute();

                    $result = ['id' => $this->id];
                    $success = true;
                    $message = 'Succesfully updated record with an ' . $this->defaultDbPrimaryKeyName . ' of ' . $this->id . '.';
                    $message = (isset($this->defaultSuccessMessageUpdate) ? $this->defaultSuccessMessageUpdate : $message);
                    $response_code = 200; // Ok
                } else {
                    $message = 'Missing ' . $this->defaultDbPrimaryKeyName . ' field value.';
                    $response_code = 400; // Bad request
                }

            } else {
                $message = 'Only POST methods are allowed';
                $response_code = 405; // Method not allowed
            }

            $this->_displayResponse($result, $success, $message, $response_code, 'POST');

        } catch (\Exception $e) {
            $this->_displayResponse(null, false, $e->getMessage(), 500, 'GET');
        }
    }

    /**
     * Performs an api action delete
     *
     * @return void
     */
    public function delete()
    {
        try {
            $result = null;
            $success = null;
            $message = null;
            $response_code = null;

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {

                if ($this->id) { // Always check if the id is there
                    $db = new Database(
                        $this->dbConfig['db_host'],
                        $this->dbConfig['db_username'],
                        $this->dbConfig['db_password'],
                        $this->dbConfig['db_name']
                    );

                    // Get the results
                    $db->delete($this->tableName)
                        ->where($this->defaultDbPrimaryKeyName, $this->id)
                        ->execute();

                    $result = ['id' => $this->id];
                    $success = true;
                    $message = 'Succesfully deleted record with an ' . $this->defaultDbPrimaryKeyName . ' of ' . $this->id . '.';
                    $message = (isset($this->defaultSuccessMessageDelete) ? $this->defaultSuccessMessageDelete : $message);
                    $response_code = 200; // Ok
                } else {
                    $message = 'Missing ' . $this->defaultDbPrimaryKeyName . ' field value.';
                    $response_code = 400; // Bad request
                }

            } else {
                $message = 'Only POST methods are allowed';
                $response_code = 405; // Method not allowed
            }

            $this->_displayResponse($result, $success, $message, $response_code, 'POST');

        } catch (\Exception $e) {
            $this->_displayResponse(null, false, $e->getMessage(), 500, 'GET');
        }
    }

    /**
     * Renders the api response
     * for the restful class.
     *
     * @param array $data
     * @param boolean $success
     * @param string $message
     * @param int $responseCode
     * @param string $allowedMethods
     * @param string $allowedOrigin
     *
     * @return void
     */
    protected function _displayResponse($data = [], $success = false, $message = '', $responseCode = null, $allowedMethods = 'GET', $allowedOrigin = '*')
    {
        $response = [
            'success' => $success,
            'message' => $message,
        ];

        $totalItems = intval($this->_totalItemCount());
        $this->totalPages = ceil($totalItems / $this->limit);

        if ($success && $this->pageNumber) {
            $response = array_merge([
                'total_items' => $totalItems,
                'total_pages' => $this->totalPages,
                'page' => $this->pageNumber,
                'limit' => $this->limit,
            ], $response);
        }

        if ($this->isAuthInDebugMode) {
            $response = array_merge(['authStatusResponse' => $this->authStatusResponse], $response);
        }

        $response = isset($data) ? array_merge(['data' => $data], $response) : $response;

        Helpers::renderAsJson($response, $responseCode, $allowedMethods, $allowedOrigin);
    }

    /**
     * Returns the total item of the table
     *
     * @return array
     */
    protected function _totalItemCount()
    {
        $db = new Database(
            $this->dbConfig['db_host'],
            $this->dbConfig['db_username'],
            $this->dbConfig['db_password'],
            $this->dbConfig['db_name']
        );

        $result = $db->select($this->tableName, 'COUNT(' . $this->defaultDbPrimaryKeyName . ') as count')->one();

        return $result['count'];
    }

    /**
     * Only includes those values that do not belong
     * in the restfulhandler column preset
     *
     * @return array
     */
    protected function _cleanedPostData()
    {
        $data = [];
        foreach ($_POST as $key => $value) {
            if (!in_array($key, $this->columnPresets)) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * Load the main configuration
     *
     * @param array $args
     *
     * @return void
     */
    private function _loadConfig($args = [])
    {
        // Register this predefined enpoint
        $this->_registerEndpointToAccessRule('endpoints', [
            'isAccessPageViaAccessToken' => false,
        ]);

        // Set default for read pagination
        $this->id = isset($args['id']) ? $args['id'] : null;
        $this->limit = intval(isset($_GET['limit']) ? $_GET['limit'] : $this->limit);
        $this->pageNumber = isset($args['pageNumber']) ? $args['pageNumber'] : 1;

        // If method is not executed for the endpoints
        if (empty($args['isMethodExecuted'])) {
            $this->endpoints();
        }
    }

    /**
     * Returns the endpoint list of the child class
     *
     * @return array
     */
    protected function _endpointList()
    {
        $data = [];
        $class = new \ReflectionClass($this->childClass);
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

        $ctr = 0;
        foreach ($methods as $value) {
            if (!in_array($value->name, $this->declaredPredefinedMethods)) { // Only include those public
                $data[$ctr]['name'] = $value->name;
                $data[$ctr]['url'] = Url::baseUrl() . strtolower($this->endpointGroup) . '/' . $value->name;
                $ctr++;
            }
        }

        return $data;
    }
}
