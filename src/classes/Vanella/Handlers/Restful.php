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
    protected $tableColumns = [];
    protected $request = [];
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
     * Connect to the database
     */
    protected function dbConn()
    {
        return new Database(
            $this->dbConfig['db_host'],
            $this->dbConfig['db_username'],
            $this->dbConfig['db_password'],
            $this->dbConfig['db_name']
        );
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

            // Returns a json exception for the allowed access methods
            $this->allowAccess('GET');

            $db = $this->dbConn();
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
            $message = 'Successfully retrieved records';
            $message = (isset($this->defaultSuccessMessageRead) ? $this->defaultSuccessMessageRead : $message);
            $response_code = 200; // Ok
            $this->_displayResponse($result, $success, $message, $response_code, $_SERVER['REQUEST_METHOD']);

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

            // Blocks the rest of the execution if request method does not match
            $this->allowAccess('POST');

            // Blocks the rest of the execution if the request is empty
            $this->_checkRequestEmpty();

            // Prepare the data
            $date = new \DateTime();

            //Remove data if it is in column preset
            $data = $this->_cleanedData($this->request, $this->columnPresets, true);

            // If defaultDbCreatedAtName field is not specified
            if ($this->defaultDbCreatedAtName) {
                $data = array_merge(
                    $data,
                    [$this->defaultDbCreatedAtName => $date->format('Y-m-d H:i:s')]
                );
            }

            //Remove data if not in table columns
            $data = $this->_cleanedData($data, $this->tableColumns, false);

            // Insert the data
            $id = $this->dbConn()->insert($this->tableName, $data)->execute();
            $success = true;
            $message = 'Succesfully added record with an ' . $this->defaultDbPrimaryKeyName . ' of ' . $id . '.';
            $message = (isset($this->defaultSuccessMessageCreate) ? $this->defaultSuccessMessageCreate : $message);
            $response_code = 201; // Created

            // Display the response
            $this->_displayResponse(['id' => $id], $success, $message, $response_code, $_SERVER['REQUEST_METHOD']);

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

            // Blocks the rest of the execution if request method does not match
            $this->allowAccess(['PATCH', 'PUT']);

            // Blocks the rest of the execution if the id is not
            // passed in http://yoursite.com/endpointgroup/endpoint/{id}
            $this->_checkIdIsPassed();

            // Blocks the rest of the execution
            // if the record does not exists in the database
            $this->_checkRecordExists();

            // Blocks the rest of the execution if the request body is empty
            $this->_checkRequestEmpty();

            // Updates the record
            $this->_updateById();

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
            // Blocks the rest of the execution if request method does not match
            $this->allowAccess(['PATCH', 'PUT']);

            // Blocks the rest of the execution if the id is not
            // passed in http://yoursite.com/endpointgroup/endpoint/{id}
            $this->_checkIdIsPassed();

            // Blocks the rest of the execution
            // if the record does not exists in the database
            $this->_checkRecordExists();

            // Deletes the record
            $this->_deleteById();

        } catch (\Exception $e) {
            $this->_displayResponse(null, false, $e->getMessage(), 500, 'GET');
        }
    }

    /**
     * Updates by id
     */
    protected function _updateById()
    {
        // Prepare the data to be updated
        $date = new \DateTime();

        //Remove data if it is in column preset
        $data = $this->_cleanedData($this->request, $this->columnPresets, true);

        // If defaultDbUpdatedAtName field is not specified
        if ($this->defaultDbUpdatedAtName) {
            $data = array_merge(
                $data,
                [$this->defaultDbUpdatedAtName => $date->format('Y-m-d H:i:s')]
            );
        }

        //Remove data if not in table columns
        $data = $this->_cleanedData($data, $this->tableColumns, false);

        // Update the data
        $this->dbConn()
            ->update($this->tableName, $data)
            ->where($this->defaultDbPrimaryKeyName, $this->id)
            ->execute();

        // Data to be passed in response
        $result = ['id' => $this->id];
        $defaultMessage = 'Succesfully updated record with an ' . $this->defaultDbPrimaryKeyName . ' of ' . $this->id . '.';
        $message = (isset($this->defaultSuccessMessageUpdate) ? $this->defaultSuccessMessageUpdate : $defaultMessage);

        // Display the response
        $this->_displayResponse(
            $result,
            true, // Success status
            $message, // Message prompt
            200, // Response code
            $_SERVER['REQUEST_METHOD']
        );
    }

    /**
     * Deletes by id
     *
     * @return void
     */
    protected function _deleteById()
    {
        // Delete the record
        $this->dbConn()
            ->delete($this->tableName)
            ->where($this->defaultDbPrimaryKeyName, $this->id)
            ->execute();

        $result = ['id' => $this->id];
        $success = true;
        $message = 'Succesfully deleted record with an ' . $this->defaultDbPrimaryKeyName . ' of ' . $this->id . '.';
        $message = (isset($this->defaultSuccessMessageDelete) ? $this->defaultSuccessMessageDelete : $message);
        $response_code = 200; // Ok

        $this->_displayResponse($result, $success, $message, $response_code, $_SERVER['REQUEST_METHOD']);
    }

    /**
     * Checks if the id is passed and has value
     */
    protected function _checkIdIsPassed()
    {
        // Block the execution if the id is not there
        if (!$this->id) {
            Helpers::renderAsJson([
                'success' => false,
                'message' => 'Missing ' . $this->defaultDbPrimaryKeyName . ' field value.',
            ], 400); // Bad request
        }
    }

    /**
     * Checks if the record exists
     */
    protected function _checkRecordExists()
    {
        $db = $this->dbConn();
        $record = $db->select($this->tableName)
            ->where($this->defaultDbPrimaryKeyName, $this->id)
            ->one();

        // Block the execution if the record does not exist
        if (empty($record)) {
            Helpers::renderAsJson([
                'success' => false,
                'message' => 'The record with an ' . $this->defaultDbPrimaryKeyName . ' of ' . $this->id . ' does not exists.',
            ], 400);
        }
    }

    /**
     * Checks if the request is empty
     */
    protected function _checkRequestEmpty()
    {
        // Block the execution if the request is empty
        if (empty($this->request)) {
            Helpers::renderAsJson([
                'success' => false,
                'message' => 'No data has been passed.',
            ], 400); // Bad request
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
        $db = $this->dbConn();
        $result = $db->select($this->tableName, 'COUNT(' . $this->defaultDbPrimaryKeyName . ') as count')->one();

        return $result['count'];
    }

    /**
     * Only includes/uninclude those values that do not belong
     * in the arrayHayStack
     *
     * @return array
     */
    protected function _cleanedData($data = [], $arrayHayStack = [], $reverse = true)
    {
        $newData = [];
        foreach ($data as $key => $value) {
            if ($reverse) {
                // If not in the list of $arrayHayStack include
                if (!in_array($key, $arrayHayStack)) {
                    $newData[$key] = $value;
                }
            } else {
                // If it is in the list of $arrayHayStack include
                if (in_array($key, $arrayHayStack)) {
                    $newData[$key] = $value;
                }
            }
        }

        return $newData;
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
        // Load request data
        $this->requestData();
        $this->tableColumns = $this->_getTableColumns();

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

    /**
     * Get the tablecolumns of this table
     */
    protected function _getTableColumns()
    {
        $data = [];
        $db = $this->dbConn()->tableColumns($this->tableName);

        if (!empty($db)) {
            foreach ($db as $items) {
                $data[] = $items['Field'];
            }
        }

        return $data;
    }

    /**
     * Get the request data
     */
    protected function requestData()
    {
        parse_str(file_get_contents('php://input'), $this->request);
    }
}
