<?php

namespace Vanella\Handlers;

use Vanella\Handlers\Authentication;
use Vanella\Handlers\Authorization;

interface RestfulInterface
{
    public function create();
    public function read();
    public function update();
    public function delete();
}

class Restful extends Authentication implements RestfulInterface
{
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
    protected $columnPresets = ['limit', 'page', 'total_items', 'total_pages'];

    public function __construct($args = [])
    {
        parent::__construct($args);

        // Load restful configurations
        $this->_loadConfig($args);

        // Run the onBefore function
        $this->_onBefore();

        date_default_timezone_set($this->restConfig['timezone']);
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

            // If defaultDbUpdatedAtName field is not specified
            if ($this->defaultDbUpdatedAtName) {
                $data = array_merge(
                    $data,
                    [$this->defaultDbUpdatedAtName => $date->format('Y-m-d H:i:s')]
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
            $this->allowAccess(['DELETE']);

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
            Helpers::renderAsJson(array_merge([
                'success' => false,
                'message' => 'Missing ' . $this->defaultDbPrimaryKeyName . ' field value.',
            ], $this->_addRefreshTokenToResponse()), 400); // Bad request
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
            Helpers::renderAsJson(array_merge([
                'success' => false,
                'message' => 'The record with an ' . $this->defaultDbPrimaryKeyName . ' of ' . $this->id . ' does not exists.',
            ], $this->_addRefreshTokenToResponse()), 400);
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

        $response = array_merge($response, $this->_addRefreshTokenToResponse());
        $response = array_merge($response, $this->_addAuthStatusResponse());
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
        $this->tableColumns = $this->_getTableColumns($this->tableName);

        // Register this predefined enpoint
        $this->_registerEndpointToAccessRule('endpoints', [
            'isAccessPageViaAccessToken' => false,
        ]);

        // Set default for read pagination
        $this->id = isset($args['id']) ? $args['id'] : null;
        $this->limit = intval(isset($_GET['limit']) ? $_GET['limit'] : $this->limit);
        $this->pageNumber = isset($args['pageNumber']) ? $args['pageNumber'] : 1;

    }

    /**
     * Run this function before anything else
     *
     * @return void
     */
    protected function _onBefore()
    {
        // Run the authorization
        $this->runAuthorization();

        // Run the validations
        $this->runValidations();
    }

    /**
     * Performs the validation
     *
     * @return void
     */
    protected function runValidations($isValidationActivated = true)
    {
        if ($this->_isPageAccessibleViaAccessToken() && $this->isPostPutPatchServerRequest()) {
            new Validations([
                'db' => $this->dbConn(),
                'isValidationActivated' => $isValidationActivated,
                'endpointGroup' => $this->endpointGroup,
                'endpoint' => $this->endpoint,
                'table' => $this->tableName,
                'tableColumns' => $this->tableColumns,
                'requestData' => $this->request,
                'validators' => $this->validators,
            ]);
        }
    }

    /**
     * Performs the authorization
     *
     * @return void
     */
    protected function runAuthorization()
    {
        if ($this->_isPageAccessibleViaAccessToken()) {

            // Get the decode JWT
            $jwtDecoded = (array) $this->_getJWTDecoded($this->accessToken);
            $username = isset($jwtDecoded['username']) ? $jwtDecoded['username'] : null;
            $password = isset($jwtDecoded['password']) ? $jwtDecoded['password'] : null;
            $this->validateUserCredentials($username, $password);

            $init = [
                'endpointGroup' => $this->endpointGroup,
                'endpoint' => $this->endpoint,
                'mainConfig' => (isset($this->mainConfig) ? $this->mainConfig : []),
                'childClass' => $this->childClass,
                'declaredPredefinedMethods' => $this->declaredPredefinedMethods,
                'accessToken' => $this->accessToken,
                'validatedUser' => $this->validatedUser
            ];

            new Authorization($init);
        }
    }

    /**
     * Get the tablecolumns of this table
     */
    protected function _getTableColumns($tableName)
    {
        $data = [];
        $db = $this->dbConn()->tableColumns($tableName);

        if (!empty($db)) {
            foreach ($db as $items) {
                $data[] = $items['Field'];
            }
        }

        return $data;
    }
}
