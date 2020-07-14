<div class="row">
    <div class="col-lg-12">
        <form method="GET">
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="search">Search</span>
                </div>                
                <input type="text" name="search" value="<?= isset($_GET['search']) ? $_GET['search'] : ""?>" class="form-control" aria-label="Search" aria-describedby="search">
            </div>
        </form>
        <?php if (isset($_GET['action']) && $_GET['action'] == 'info'): ?>
        <?php include 'forms/users/form_info.php'?>
        <?php else: ?>
            <?= Vanella\Handlers\ViewHelpers::pagination(
                [
                    'id' => 'ID',
                    'role' => 'Role',
                    'email' => 'Email',
                    'username' => 'Username',
                    'first_name' => 'First Name',
                    'last_name' => 'Last name',
                ],
                $users,
                $defaultLimit,
                $totalUsersCount,
                'users_util',
                true
            );?>
        <?php endif;?>
    </div>
</div>