<?

# GLOBALS

include("db.php");
$NUM_QUESTIONS = 3;
$BOT_NAME = "dawebot";

function printHeader(){
	global $BOT_NAME;

	echo "<!doctype html>
		<html>
		<head>
		<meta charset='utf-8' />
	    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='icon' href='/favicon.ico'>
    <title> ". $BOT_NAME  ." - RETOS DE VOZ </title>
    <!-- Bootstrap core CSS -->
    <link href='dist/css/bootstrap.min.css' rel='stylesheet'>
    <!-- Custom styles for this template -->
    <link href='signin.css' rel='stylesheet'>
			</head><body role='document'>
";
}



function printFooter(){
	echo "</body></html>";
}


function prepare_audios($obj, $user, $c_id){

	global $API_KEY, $URL_BASE, $conn, $NUM_QUESTIONS;

	$output = '';

	for($ind=1; $ind <= $NUM_QUESTIONS; $ind++){

		$file_id = $obj->{'question' . $ind};
		// error_log("Retrieving: " . $file_id . " for " . $c_id ."\n", 3, "/tmp/error.log");

		$audiors = $conn->query("select * from voicecache where fileId = '". $file_id ."'");

		if($audiors->rowCount() === 0)
		{
			return 'No results';
		}else{

			$audio = $audiors->fetch(PDO::FETCH_ASSOC);
			$audiourl = 'downloads/' . $user . '/' .  $audio['filePath'] ;

			$output .= "\n Q" . $ind .": <audio controls><source src='". $audiourl ."' type='audio/ogg'>Tu navegador no es compatible. Descarga la <a href='". $audiourl ."'> nota de audio</a></audio><br>\n";
		}
	}
	return $output;
}


printHeader();

	$emaitzak = $conn->query("SELECT 
			u.username, u.first_name, u.last_name, c.user_id, c.id as c_id, c.notes, c.created_at, c.updated_at
			FROM conversation c, user u
			where command = 'Reto' 
			and status = 'stopped'
			and c.user_id = u.id");

	echo "<table class='table table-striped'>
		<thead><tr><th>username</th>
		<th>first_name</th>
		<th>last_name</th>
		<th>user_id</th>
		<th>conv_id</th>
		<th>notes</th>
		<th>created_at</th>
		<th>updated_at</th>
		</tr></thead><tbody>";
	while ($lerro = $emaitzak->fetch(PDO::FETCH_ASSOC)) {
		$obj = json_decode($lerro['notes']);
		echo "<tr>";
		echo "<td>" . $lerro['username'] . "</td>";
		echo "<td>" . $lerro['first_name'] . "</td>";
		echo "<td>" . $lerro['last_name'] . "</td>";
		echo "<td>" . $lerro['user_id'] . "</td>";
		echo "<td>" . $lerro['c_id'] . "</td>";
		echo "<td>" . prepare_audios($obj, $lerro['user_id'], $lerro['c_id']) . "</td>";
		echo "<td>" . $lerro['created_at'] . "</td>";
		echo "<td>" . $lerro['updated_at'] . "</td>";
		echo "</tr>\n";
	}
	echo "</tbody></table>";

	printFooter();

