<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vanella Documentation</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/App.css">
</head>
<body>
    <div class="container">
        <section class="hero">
            <h1>Welcome to Vanella REST API!</h1>
            <p>
                Vanella REST API is a stand-alone api framework built in native php.
            </p>
        </section>
        <section class="content">
            <div class="why-vanella-api">
                <h3>Why Use Vanella REST API?</h3>
                <ul>
                    <li>It is very easy to setup.</li>
                    <li>It is reasonably fast because it only uses native php and has a very small codebase.</li>
                    <li>Has a built-in command line interface called <strong>vanella</strong> to help you get started with your project!</li>
                    <li>
                        You will never read tons of documentation just to get this framework working.
                    </li>
                    <li>
                        Lesser documentation to read means faster learning curve.
                    </li>
                    <li>
                        You never have to re-learn everything. If you know PHP you can get around with this instantly.
                    </li>
                    <li>
                        It has a built-in create, read, update, delete api functionality.
                    </li>
                    <li>
                        It uses oAuth2 for client app authentication.
                    </li>
                    <li>
                        It uses JWT for generating access token.
                    </li>
                    <li>
                        You can create your own custom api call right off the bat using your favorite vanilla php or object oriented style of coding or <strong>via VANELLA command line interface </strong>!
                    </li>
                    <li>
                        You can also add additional libraries via composer depending on your needs.
                    </li>
                </ul>
            </div>
            <div class="system-requirements">
                <h3>System Requirements</h3>
                <ul>
                    <li>Utilizes composer <span class="link"><a href="https://getcomposer.org/download/">https://getcomposer.org/download/</a></span> </li>
                    <li>PHP Version (>= 5.3.0, PHP 7).</li>
                    <li>Primarily uses MySQL for the database (But you can connect to any database at your will).</li>
                </ul>
            </div>
            <div class="installation">
                <h3>How to Install?</h3>
                <ul>                    
                    <li>Download via composer(recommended). If you have composer already installed, run this command on your terminal or cmd.<div class="command">composer create-project afnav/vanella-rest-api</div></li>
                    <li>Or alternatively, clone to github using git.
                    <span class="link"><a href="https://github.com/afnavaja/vanella-rest-api">https://github.com/afnavaja/vanella-rest-api</a></span>
                    </li>
                    <li>Or just download the zip in my github page.</li>
                </ul>
            </div>
            <div class="configuration">
                <h3>Basic Configuration (Follow these steps)</h3>
                <ol>
                    <li>
                        Go to the root of your project directory. Run <div class="command">php vanella</div> on your terminal(linux) or cmd(Windows) to see the list of active commands.
                    </li>
                    <li>
                        Run <div class="command">php vanella create:config</div> and choose <strong>[all]</strong> to create the initial config.
                    </li>
                    <li>
                        Go to your app root directory <span class="directory">/src/config/database.php</span> to change the database credentials.
                    </li>                   
                    <li>
                        In your <span class="directory">/src/config/main.php</span> file there is an <span class="config-element">active_env</span> key, you change it to "development" or "production" if you want. 
                    </li>                    
                </ol>
            </div>
            <div class="cli-commands">
                <h3>Basic CLI Commands (Normal Process Flow)</h3>
                <ol>
                    <li>
                        Run <div class="command">php vanella create:endpointgroup</div> to create your first endpoint group. After that, you can locate your class file in <span class="directory">/src/restful/SomeClassFileForYourEndpointGroup.php</span> See examples below. 

                        <ul>
                            <li><span class="link">http://yourwebsite.com/users/read</span></li>
                            <li><span class="link">http://yourwebsite.com/users/create</span></li>
                            <li><span class="link">http://yourwebsite.com/users/update</span></li>
                            <li><span class="link">http://yourwebsite.com/users/delete</span></li>
                        </ul>
                        <strong>Explanation:</strong>
                        "/users" are the Endpoint Group which is and also should be the name of your class files. In this example, your class file should be named <strong>Users.php</strong> in your <span class="directory">/src/resftul/Users.php</span> directory.<br/>
                       The "/read" or "/create" or "/update" or "/delete" are the endpoints. The endpoints are the name of your class functions. Luckily, the vanella commands are ready to do that for you so you don&apos;t need to worry! ;)
                    </li>
                    <li>
                        All endpoints groups or endpoint class files are located in <span class="directory">/src/restful/</span> directory. 
                    </li>
                    <li>Run <div class="command">php vanella create:endpoint</div> to create an endpoint. The app will create those endpoints for you. See examples below.
                        <ul>
                            <li><span class="link">http://yourwebsite.com/users/yourcustomEndpoint</span></li>
                            <li><span class="link">http://yourwebsite.com/users/anotherCustomEndpoint</span></li>
                        </ul>
                    </li>
                    <li>There are 5 endpoint types that you can choose to generate the code when running create:endpoint which are <span class="config-element">[basic,read,create,update,delete]</span>
                        <ul>
                           <li><strong>Endpoint type: </strong><span class="config-element">basic</span> only creates an empty function which equals to desired endpoint. From here you can run native php code to your hearts content!</li>
                           <li><strong>Endpoint type: </strong><span class="config-element">create</span>  creates a ready made endpoint app-generated code to create records to your specified database table.</li>
                           <li><strong>Endpoint type: </strong><span class="config-element">read</span>  creates a ready made endpoint app-generated code to view your records to your specified database table. And it already has built in pagination as well. See examples below. Supposing you already have records on your "users" database table.
                           <ul>
                                <li><span class="link">http://yourwebsite.com/users/read/page/1</span></li>
                                <li><span class="link">http://yourwebsite.com/users/read/page/2</span></li>
                            </ul>
                            </li>
                            <li><strong>Endpoint type: </strong><span class="config-element">update</span>  creates a ready made endpoint app-generated code to update records to your specified database table.
                            <ul>
                                <li><span class="link">http://yourwebsite.com/users/update/{id}</span></li>
                                <li><span class="link">http://yourwebsite.com/users/update/1</span></li>
                            </ul>
                            </li>
                            <li><strong>Endpoint type: </strong><span class="config-element">delete</span>  creates a ready made endpoint app-generated code to delete records to your specified database table.
                            <ul>
                                <li><span class="link">http://yourwebsite.com/users/delete/{id}</span></li>
                                <li><span class="link">http://yourwebsite.com/users/delete/1</span></li>
                            </ul>
                            </li>
                        </ul>
                    </li>
                    <li><div class="important">Important: The endpoint group class files already extends to Vanella\Handlers\Restful class so you don&apos;t necessarily have to regenerate CRUD on your class files. But there maybe times, that you need to have some minor changes or customization to a certain endpoint so Vanella REST API helps you generate necessary sourcecode for that to get started! Again, just use vanilla php to create that sophisticated API process! :p</div></li>
                </ol>
            </div>
            <div class="cli-commands-with-authentication">
                <h3>Activating Authentication</h3>
                <ol>
                    <li>
                        Run <div class="command">php vanella activate:auth</div> to activate authentication. The app will generate the necessary files for authentication.
                    </li>
                    <li>
                        Run and test the auth using Postman. If you don&apos;t have Postman in your computer, download it here! <span class="link"><a href="https://www.postman.com/downloads/">https://www.postman.com/downloads</a></span>
                    </li>
                    <li>The uses OAuth2.0 for client authentication to generate the JWT access tokens.</li>
                    <li>The initial configuration can be found in <span class="directory">/src/config/authentication.php</span></li>
                    <li>The authenticated apps configuration can be found in <span class="directory">/src/config/authenticatedApps.php</span></li>
                    <li>The JWT configuration can be found in <span class="directory">/src/config/authlist.php</span></li>
                    <li>When you activate the built-in authentication, you need to specify explicitly the endpoint rules of your custom endpoints. So in your endpoint group class, <strong>add this function</strong> to explicitly register your endpoint access rule to the authentication class.
                    <pre styles="line-height: 1rem;">
                        /**
                         * The default config of this Endpoint Group Class
                         *
                         */
                        public function defaultConfig()
                        {                       
                            $this->_registerEndpointToAccessRule('anotherEndpoint', [
                                'isAccessPageViaAccessToken' => true,
                            ]);
                        }

                        /**
                         * This could be another endpoint that you might add along the way
                         *
                         */
                        public function anotherEndpoint()
                        {
                            // Your custom code here
                        }
                    </pre>
                    </li>
                </ol>
            </div>
            <div class="conclusion">
                <h3>Conclusion</h3>
                <p>I didn&apos;t want you to read lots of documentations just to get you started with this rest api framework. I really hated it when you spend a lot of time reading the docs when you just wanted a straightforward answer. That&apos;s basically what you need to do to get started with this rest api framework! If you have questions you can reach me to my email afnavaja@gmail.com!</p>
            </div>
        </section>
    </div>
</body>
</html>
