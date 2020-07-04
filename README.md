# VANELLA REST API
Vanella REST API is a standalone API framework using native PHP.

  - It is very easy to setup.
  - It is reasonably fast because it only uses native PHP and has a very small codebase.
  - Has a built-in command-line interface called vanella to help you get started with your project!
  - It has a built-in create, read, update, delete API functionality.
  - It uses oAuth2 for client app authentication.
  - It uses JWT for generating an access token.
  - You can create your custom API call right off the bat using your favorite vanilla PHP or object-oriented style of coding or via the VANELLA command-line interface!
  - You can also add additional libraries via the composer, depending on your needs.

### Installation

- Utilizes [composer](https://getcomposer.org/download/).
- PHP Version (>= 5.3.0, safest in PHP 7).
- MySQL

```sh
$ composer create-project afnav/vanella-rest-api
```

### Basic Configuration (Follow these steps)
- Go to the root of your project directory. Run this on your terminal(Linux) or cmd(Windows) to see the list of active commands.
    ```sh
    $ php vanella
    ```
- Run and choose [all] to create the initial config.
    ```sh
    $ php vanella create:config
    ```
- Go to your app root directory /src/config/database.php to change the database credentials.
- In your /src/config/main.php file, there is an active_env key; you change it to "development" or "production" if you want. The default value is "development".



### Basic CLI Commands (Normal Process Flow)
 1. Run this command to create your first endpoint group. 

    ```sh
     $ php vanella create:endpointgroup
    ``` 
 2. After that, you can locate your class file in "/src/restful/SomeClassFileForYourEndpointGroup.php". See examples below.
 - http://yourwebsite.com/users/read
 - http://yourwebsite.com/users/create
 - http://yourwebsite.com/users/update
 - http://yourwebsite.com/users/delete
 
 > The [read, create, update, delete] are built-in endpoints when you extend to the Vanella\Handlers\Restful class. So you don't have to recreate these endpoints.

### Basic CLI Commands (Things you need to know before you continue)
 1. "/users" are the Endpoint Group, which is and also should be the name of your class files. In this example, your class file should be named Users.php in your /src/resftul/Users.php directory.
 2. The "/read" or "/create" or "/update" or "/delete" are the endpoints. The endpoints are the name of your class functions. Luckily, the Vanella commands are ready to do that for you, so you don't need to worry! ;)
 3. All endpoints groups or endpoint class files are in /src/restful/ directory.

### Basic CLI Commands (Creating Endpoints)
 1. Run this command to create an endpoint.
    ```sh
     $ php vanella create:endpoint
    ``` 
 2. The app will create those endpoints for you. See the examples below.
  - http://yourwebsite.com/users/yourcustomEndpoint
  - http://yourwebsite.com/users/anotherCustomEndpoint
 3. There are five endpoint types that you can choose to generate the code when running create:endpoint which is [basic,read,create,update,delete]
 
    | TYPE | DESCRIPTION | ACCEPTS METHOD |
    | ------ | ------ | ------ |
    | basic | Only creates an empty function which equals to the desired endpoint. From here, you can run native PHP code to your heart's content! | N/A |
    | create | Creates a ready-made endpoint app-generated code to create records to your specified database table. | POST |
    | read | Creates a ready-made endpoint app-generated code to view your records to your specified database table. And it already has built-in pagination as well.| GET |
    | update | Creates a ready-made endpoint app-generated code to update records to your specified database table. | POST OR PUT
    | delete | Creates a ready-made endpoint app-generated code to delete records to your specified database table. | DELETE

  - > Important: The endpoint group class files already extends to Vanella\Handlers\Restful class, so you don't necessarily have to generate CRUD[create,read,update,delete] API endpoints on your class files. 
  - >But there may be times that you need to have some minor changes or customization to a specific endpoint, so Vanella REST API helps you generate necessary sourcecode for that to get you started! 
  - > Again, just use your vanilla PHP to create that sophisticated API process! :p

  #### About the endpoint types [read, update, delete]
 - Endpoint type: read creates a ready-made endpoint app-generated code to view your records to your specified database table. And it already has built-in pagination as well. See the examples below. Supposing you already have records on your "users" database table.
    - http://yourwebsite.com/users/read/page/1
    - http://yourwebsite.com/users/read/page/2
 - Endpoint type: update creates a ready-made endpoint app-generated code to update records to your specified database table.
    - http://yourwebsite.com/users/update/{id}
    - http://yourwebsite.com/users/update/1
 - Endpoint type: delete creates a ready-made endpoint app-generated code to delete records to your specified database table.
   - http://yourwebsite.com/users/delete/{id}
   - http://yourwebsite.com/users/delete/1

## Activating Built-in Authentication

 1. Run this command to activate authentication. The app will generate the necessary files for authentication.
    ```sh
     $ php vanella activate:auth
    ``` 
 2. Test the auth using Postman. If you don't have Postman on your computer, download it here! https://www.postman.com/downloads
 3. Primarily uses OAuth2.0 for client authentication to generate the JWT access tokens.
 4. The initial configuration can be found in /src/config/authentication.php
 5. The authenticated apps configuration can be found in /src/config/authenticatedApps.php
 
### Conclusion
If you have questions, you can reach me through my email at afnavaja@gmail.com!