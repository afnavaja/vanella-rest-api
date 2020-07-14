<?php session_start();

use Vanella\Core\Controller;
use Vanella\Handlers\Authentication;
use Vanella\Handlers\Helpers;

/**
 * This is a built in class for Vanella.
 * This class will serve as an additional utility
 * for you rest api application. You can set the
 * authorization for each users here.
 * 
 * Visit www.yourwebsite.com/utility/login_util to access this endpoint
 */
class Utility extends Authentication
{
    protected $args = [];
    protected $data = [];
    protected $url;
    protected $utilityBasePath;
    protected $mainTitle;
    protected $predefinedMethod;

    /**
     * The class construct
     */
    public function __construct($args = [])
    {
        $this->args = $args;
        $this->url = isset($args['url']) ? $args['url'] : null;
        $this->utilityBasePath = __DIR__ . '/Utility/';
        $this->data['url'] = $this->url;
        $this->data['baseUrl'] = $this->url->baseUrl();
        $this->data['defaultLimit'] = 10;
        $this->data['isLoggedIn'] = $this->isLoggedIn();
        $this->mainTitle = 'Vanella REST Utility';
        $this->predefinedMethod = array_merge($this->declaredPredefinedMethods, [
            'login_util',
            'logout_util',
            'authorization_util',
            'users_util',
            'validation_util',
            'authorization_util_save'
        ]);
        parent::__construct($args);
        $this->isAuthActivated();
    }


    /**
     * Checks if auth is activated
     * 
     * @return void
     */
    private function isAuthActivated()
    {
        if (!isset($this->authConfig['default']['isAuthActivated']) && !$this->authConfig['default']['isAuthActivated']) {
            Helpers::renderAsJson([
                'success' => false,
                'message' => 'You need to activate auth to access this page. Run [php vanella activate:auth]'
            ], 403);
        }
    }

    /**
     * Handles the login page.
     *
     * @return void
     */
    public function login_util()
    {           
        $this->data['title'] = 'Login';
        $this->data['page'] = 'login';

        if (isset($_POST['submit'])) {  
            $username = isset($_POST['username']) ? $_POST['username'] : null;
            $password = isset($_POST['password']) ? $_POST['password'] : null;
            $user = $this->validateUser($username, $password);

            if ($user['success'] && $user['data']['role'] == 'superadmin') {
                $_SESSION['logged_in'] = true;
                $_SESSION['role'] = $user['data']['role'];
                header("Location: " . $this->url->baseUrl() . 'utility/users_util');
            } else {
                $this->data['prompt'] = 'Wrong username or password.';
                session_destroy();
            }
        }

        Controller::render($this->utilityBasePath . 'views/login_util', $this->data);
    }

    /**
     * Validates the user that logs in the utility
     * 
     * @param string $username
     * @param string $password
     * 
     * @return boolean
     */
    private function validateUser($username, $password)
    {
        $db = $this->dbConn()
            ->select($this->mainConfig['defaultTableForUsers'], 'password, role')
            ->where('username', $username)
            ->one();

        if (!empty($db)) {            
            return [
                'success' => password_verify($password, $db['password']) ? true:false,
                'data' => $db
            ];
        } else {
            return [
                'success' => false,
                'data' => []
            ];
        }

    }

    /**
     * Checks if current user is logged in
     *
     * @return boolean
     */
    private function isLoggedIn()
    {
        return isset($_SESSION['logged_in']) ? true : false;
    }

    /**
     * Redirects back to login page
     * if user is not logged in
     *
     * @return boolean
     */
    protected function redirectIfNotLoggedIn()
    {
        if (!$this->isLoggedIn()) {
            $this->logout_util();
        }
    }

    /**
     * Logs the user out
     *
     * @return void
     */
    public function logout_util()
    {
        session_destroy();
        header("Location: " . $this->url->baseUrl() . 'utility/login_util');
    }

    /**
     * Handles the Users utility.
     *
     * @return void
     */
    public function users_util()
    {   
        $this->redirectIfNotLoggedIn();
        $this->data['title'] = 'Users';
        $this->data['page'] = 'users';
        $this->data['users'] = $this->getUsers();
        $this->data['userDetails'] = $this->getUserDetailsView((isset($_GET['id']) ? $_GET['id'] : null));
        $this->data['totalUsersCount'] = $this->totalUsersCount();

        $this->render('users_util');
    }

    /**
     * Handles the Authorization page.
     *
     * @return void
     */
    public function authorization_util()
    {        
        $this->redirectIfNotLoggedIn();
        $this->data['title'] = 'Authorization';
        $this->data['page'] = 'authorization';
        $this->data['endpoints'] = $this->getEndpointsUrl($this->predefinedMethod);
        $this->data['userRoles'] = $this->getUserRoles();
        $filename = '../restful/Authorization/authorization.php';

        if (!file_exists($filename)) {
            mkdir('../restful/Authorization', 0777, true);
            $this->data['promptAuthorizationEmpty'] = 'Please setup the authorization for each roles.';
        } else {
            $this->data['authorizationFile'] = require_once $filename;
        }

        $this->render('authorization_util');
    }

    /**
     * Gets all the endpoint URL in you rest api
     * 
     * @return array
     */
    private function getEndpointsUrl($predefinedMethods)
    {
        $endpointUrl = [];
        $endpoints = Helpers::allEndpoints($predefinedMethods);
        if (!empty($endpoints)) {
            foreach ($endpoints as $item) {
                if(!in_array($item['endpointgroup'], ['Auth'])) {
                    $endpointUrl[] = $item['endpointUrl'];
                }
            }
        }

        return $endpointUrl;
    }

    /**
     * Handles the Authorization page save.
     *
     * @return void
     */
    public function authorization_util_save()
    {        
        $this->redirectIfNotLoggedIn();
        $filename = '../restful/Authorization/authorization.php';

        if (isset($_POST['submit'])) {
            $data = $_POST;
            unset($data['submit']);
            $content = null;
            if (!empty($data)) {
                $content .= '
<?php';
                $content .= '

return [';
                foreach ($data as $key => $value) {
                    $content .= '
    "' . $key . '" => [';
                    foreach ($value as $item) {
                        $content .= '
        "' . $item . '"';
                        if ($key != $this->_lastKey($value)) {
                            $content .= ',';
                        }
                    }
                    $content .= "
    ]";
                    if ($key != $this->_lastKey($data)) {
                        $content .= ',';
                    }
                }

                $content .= '
];';

                // Save contents to the config file
                file_put_contents($filename, $content);
                header("Location: " . $this->url->baseUrl() . 'utility/authorization_util');
            }
        }
    }

    /**
     * Renders the view
     *
     * @param string $page
     * @param array $data
     *
     * @return void
     */
    protected function render($page)
    {
        $this->data['title'] .= ' | ' . $this->mainTitle;

        Controller::render($this->utilityBasePath . 'views/partials/header', $this->data);
        Controller::render($this->utilityBasePath . 'views/' . $page, $this->data);
        Controller::render($this->utilityBasePath . 'views/partials/footer', $this->data);
    }

    /**
     * Gets the user role in the database
     *
     * @return array
     */
    protected function getUserRoles()
    {
        $db = $this->dbConn()->tableColumns($this->mainConfig['defaultTableForUsers']);
        $roles = null;

        foreach ($db as $item) {
            if ($item['Field'] == 'role') {
                $roles = $item['Type'];
            }
        }

        $roles = substr($roles, 5, -1);
        $roles = str_replace("'", '', $roles);
        $roles = explode(',', $roles);
        return $roles;
    }

    /**
     * Gets the users
     *
     * @return array
     */
    protected function getUsers()
    {
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : $this->data['defaultLimit'];
        $page = isset($_GET['page']) ? $_GET['page'] : null;
        $search = isset($_GET['search']) ? $_GET['search'] : null;
        $columns = $this->getUserTableColumns();
        unset($columns['date_created']);
        unset($columns['date_updated']);

        $db = $this->dbConn()
            ->select($this->mainConfig['defaultTableForUsers']);

        if (!empty($search)) {
            foreach ($columns as $key => $column) {
                if ($key == 0) {
                    $db->where($column, '%' . $search . '%', 'LIKE');
                } else {
                    $db->orWhere($column, '%' . $search . '%', 'LIKE');
                }
            }
        }

        $db->orderBy('id');

        if (!empty($page)) {
            $offset = (intval($page) * $limit) - $limit;
            $db->limit(intval($limit))->offset(intval($offset));
        } else {
            $db->limit($limit);
        }

        return $db->all();
    }

    /**
     * Gets the user details
     *
     * @param int $id
     *
     * @return array
     */
    protected function getUserDetails($id = null)
    {
        return $this->dbConn()->select($this->mainConfig['defaultTableForUsers'])->where('id', $id)->one();
    }

    /**
     * Gets the user details view
     *
     * @param int $id
     *
     * @return string
     */
    protected function getUserDetailsView($id = null)
    {
        $result = $this->getUserDetails($id);
        $data = null;

        if (!empty($result)) {
            $data = '<ul class="list-group">';
            foreach ($result as $key => $value) {
                $data .= '<li class="list-group-item">[<strong>' . $key . '] </strong>' . $value . '</li>';
            }

            $data .= '</ul>';
        }

        return $data;
    }

    /**
     * Get the tablecolumns of this table
     */
    protected function getUserTableColumns()
    {
        $data = [];
        $db = $this->dbConn()->tableColumns($this->mainConfig['defaultTableForUsers']);

        if (!empty($db)) {
            foreach ($db as $items) {
                $data[] = $items['Field'];
            }
        }

        return $data;
    }

    /**
     * Returns the total item of the table
     *
     * @return array
     */
    protected function totalUsersCount()
    {
        $db = $this->dbConn();
        $result = $db->select($this->mainConfig['defaultTableForUsers'], 'COUNT(id) as count')->one();

        return $result['count'];
    }

    /**
     * Returns the last key of an array
     *
     * @param array $data
     * @return string
     */
    private function _lastKey($data = array())
    {
        $data1 = array_keys($data);
        $data2 = array_pop($data1);
        return $data2;
    }
}
