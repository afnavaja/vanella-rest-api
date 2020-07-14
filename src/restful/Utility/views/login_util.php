<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="<?= $baseUrl ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $baseUrl ?>assets/css/form-signin.css" rel="stylesheet">
</head>
<body>
<form class="form-signin" method="POST">   
    <h1 class="h3 mb-3 font-weight-normal">Vanella REST - UTILITY</h1>
    <?= isset($prompt) ? '<p style="color: red">'.$prompt.'</p>':'' ?>
    <label for="inputUsername" class="sr-only">Username</label>
    <input type="text" id="inputUsername" class="form-control" placeholder="Username" name="username" required autofocus>
    <label for="inputPassword" class="sr-only">Password</label>
    <input type="password" id="inputPassword" class="form-control" placeholder="Password" name="password" required>    
    <button class="btn btn-lg btn-primary btn-block" name="submit" type="submit">Login</button>
    <p class="mt-5 mb-3 text-muted">&copy; 2020</p>
</form>
<script src="<?= $baseUrl ?>assets/js/bootstrap.bundle.min.js" ></script>
</body>
</html>