<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $config['main']['app_name'] ?> Documentation</title>
    <link rel="stylesheet" href="css/App.css">
</head>
<body>
    <div class="container">
        <section class="hero">
            <h1>Welcome to <?= $config['main']['app_name'] ?>.</h1>
            <p>
                <?= $config['main']['app_name'] ?> API is a stand-alone api framework built in native php.
            </p>
        </section>
        <section class="content">
            <div class="why-vanella-api">
                <h3>Why Use <?= $config['main']['app_name'] ?> API?</h3>
                <ul>
                    <li>It is very easy to setup.</li>
                    <li>It is reasonably fast because it only uses native php and has a very small codebase.</li>
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
                        You can create your own custom api call right off the bat using your favorite vanilla php or object oriented style of coding.
                    </li>
                    <li>
                        You can add additional libraries via composer depending on your needs.
                    </li>
                </ul>
            </div>
            <div class="system-requirements">
                <h3>System Requirements</h3>
                <ul>
                    <li>PHP Version (PHP 5 >= 5.2.0, PHP 7).</li>
                    <li>Primarily uses MySQL for the database.</li>
                    <li>You can connect to any database at your will.</li>
                </ul>
            </div>
            <div class="installation">
                <h3>How to Install?</h3>
                <ul>
                    <li>Download via vanella-cli (recommended).</li>
                    <li>Or download via composer.</li>
                    <li>Or alternatively, clone to github.</li>
                    <li>Or just download the zip in my github page.</li>
                </ul>
            </div>
            <div class="configuration">
                <h3>Basic Configuration</h3>
                <ul>
                    <li>
                        Go to your app root directory and set your entry point to the /public folder.
                    </li>
                    <li>
                        Go to your app root directory config/database.php and change the database credentials.
                    </li>
                    <li>Go to your app root config/main.sample.php and rename it to "main.php" </li>
                    <li>
                        In your config/main.php file there is an "active_env" key, change it to "development" or "production".
                    </li>
                    <li>
                        Go to config/authentication.php and set isAuthActivated = true if you want to turn the built in authentication.
                    </li>
                </ul>
            </div>
            <div class="configuration">
                <h3>Basic CLI Commands</h3>
                <ul>
                    <li>
                        php vanella create:project [Creates a new project in the ./dist/yourAppName directory]
                    </li>
                </ul>
            </div>
        </section>
    </div>
</body>
</html>
