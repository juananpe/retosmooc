<?

include("session_check.php");

require_once("globals.php");

if (isset($_SESSION['login'])){
	go("home.php");
}else{
	include("db.php");
	if (isset($_POST['login']) && isset($_POST['password'])){
		$credentialsquery = "select first_name as login, id as pass from user where first_name='". $_POST['login']. "' and id='".$_POST['password']. "'";
		$credentials = $conn->query($credentialsquery);
		$user = $credentials->fetch();

		if ($credentials->rowCount() == 1) {
			$_SESSION['login']=$_POST['login'];
			$_SESSION['userid']=$user['pass'];
			if (in_array( $_SESSION['userid'] , $admins))
				$_SESSION['admin']=1;
			go("home.php");
		}else{
			$error = "Usuario o pass incorrectos";
		}
	}
}

?>
<!DOCTYPE html>
<html lang="eus">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="../../favicon.ico">
    <title> <? echo $BOT_NAME ?> control panel - Login </title>
    <!-- Bootstrap core CSS -->
    <link href="dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="signin.css" rel="stylesheet">
  </head>

  <body>
    <div class="container">

      <form class="form-signin"  action="?action=kautotu" method="POST">
        <h2 class="form-signin-heading">Sign in</h2>
        <label for="login" class="sr-only">Login</label>
        <input type="text" id="inputEmail" class="form-control" placeholder="Login" required autofocus name="login">
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" id="inputPassword" class="form-control" placeholder="Password" required name="password">
	<? if ($error!='') { ?><div class="alert alert-danger" role="alert"><label><?=$error?></label></div> <? } ?>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
      </form>

    </div> <!-- /container -->

  </body>
</html>

