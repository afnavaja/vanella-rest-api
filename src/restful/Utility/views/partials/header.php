<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="<?= $baseUrl ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $baseUrl ?>assets/css/utility-admin.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark fixed-top bg-dark flex-md-nowrap p-0 shadow">
  <a class="navbar-brand col-sm-3 col-md-2 mr-0" href="#">
      Utility - Vanella REST
  </a> 
  <ul class="navbar-nav px-3">
    <li class="nav-item text-nowrap">
      <a class="nav-link" href="<?= $baseUrl.$url->segment(1)."/logout_util" ?>">Sign out</a>
    </li>
  </ul>
</nav>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-none d-md-block bg-light sidebar">
            <div class="sidebar-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= $page == 'users' ? 'active':'' ?>" href="<?= $baseUrl.$url->segment(1)."/users_util?limit=".$defaultLimit."&page=1" ?>">                       
                            Users
                        </a>
                    </li>  
                    <li class="nav-item">
                        <a class="nav-link <?= $page == 'authorization' ? 'active':'' ?>" href="<?= $baseUrl.$url->segment(1)."/authorization_util" ?>">                       
                            Authorization
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4"><div class="chartjs-size-monitor" style="position: absolute; left: 0px; top: 0px; right: 0px; bottom: 0px; overflow: hidden; pointer-events: none; visibility: hidden; z-index: -1;">
        <div class="chartjs-size-monitor-expand" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;"><div style="position:absolute;width:1000000px;height:1000000px;left:0;top:0"></div></div>
        <div class="chartjs-size-monitor-shrink" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;"><div style="position:absolute;width:200%;height:200%;left:0; top:0"></div></div></div>
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?= $title ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0"></div>
            </div>
            <div class="col-md-12">