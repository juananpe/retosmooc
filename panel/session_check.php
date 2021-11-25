<?

session_start();

$error="";

function go($nora){
$uneko_zerbitzaria  = $_SERVER['HTTP_HOST'];
$uneko_karpeta   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
header("Location: http://$uneko_zerbitzaria$uneko_karpeta/$nora");
exit;
}

if (isset($_GET['action']) && $_GET['action']=='logout'){
	unset($_SESSION['login']);
	unset($_SESSION['userid']);
	unset($_SESSION['admin']);
	session_destroy();
}

