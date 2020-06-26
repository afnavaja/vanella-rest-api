<?php

use Handlers\Helpers;

$endpoints = null;
if (!empty($endPointList)) {
    foreach ($endPointList as $items) {
        $endpoints .= '<li><a href="' . $items['url'] . '">' . $items['url'] . '</a></li>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation -

    <?=ucwords($endpointGroup)?></title>
    <link rel="stylesheet" href="css/App.css">
</head>
<body>
    <div class="container">
        <h2>API - <?=ucwords($endpointGroup)?></h2>
        <ul><?=$endpoints?></ul>
    </div>
</body>
</html>