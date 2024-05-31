<?php
  ob_start();
  require_once('includes/load.php');
  if($session->isUserLoggedIn(true)) { redirect('home.php', false);}
?>
<?php include_once('layouts/header.php'); ?>
<div class="login-page" style="height:auto;">
    <div class="text-center">
<img src="./logo.jpeg" alt="user-image" class="logo pull-left" style="width:300px; margin-top: 10px !important;" />

<br>
<br>
<br>

       <h1>Manage Assets</h1>
       <p>Sign In Now! </p>
     </div>
     <?php echo display_msg($msg); ?>
      <form method="post" action="auth.php" class="clearfix">
        <div class="form-group">
              <label for="username" class="control-label">Username</label>
              <input type="name" class="form-control" name="username" placeholder="Username">
        </div>
        <div class="form-group">
            <label for="Password" class="control-label">Password</label>
            <input type="password" name= "password" class="form-control" placeholder="password">
        </div>
        <div class="form-group">
                <button type="submit" class="btn btn-info  pull-right">Login</button>
        </div>
        <br>
<br>
    </form>
</div>
<?php include_once('layouts/footer.php'); ?>
