<?

# GLOBALS
include("globals.php");


$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
	echo "Errorea MySQLra konektatzerakoan: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
$mysqli->set_charset("utf8");

function get_details($url){
 $ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
// Set so curl_exec returns the result instead of outputting it.
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Get the response and close the channel.
$response = curl_exec($ch);
curl_close($ch);

return $response;
}


function exists($fileId){

	global $mysqli;

	$select = "select * from voicecache where fileId='".$fileId."'";
	$result = $mysqli->query($select);
	return ($result->num_rows > 0);
}

function add_to_cache($fileId, $filePath, $conversation_id, $user_id, $question, $challenge){

	global $mysqli;

	// could exist but not been downloaded
	if (!exists($fileId)){

		$insert =   "insert into voicecache set fileId = '". $fileId . "', filePath='". $filePath."', downloaded_at=NULL, conversation_id='". $conversation_id ."', user_id=" . $user_id . ", question=" . $question . ", challenge=". $challenge;

		if(!$mysqli->query($insert))
		{
			die("Error: " . $insert . "\n" . $mysqli->error);
		}

		// is it the first one? Mark as selected
		$query = "select id from voicecache where user_id=".$user_id . " and question=". $question . " and challenge=". $challenge;

		$result = $mysqli->query($query);
		if ($result->num_rows == 1){
			list($id) = $result->fetch_row();
			$update = "update voicecache set selected=1 where id=". $id;
			$mysqli->query($update);
		}	
	}
}

function download($user, $base, $file_path){

	set_time_limit(0);

	$url =  $base . '/' . $file_path;
	$extension = pathinfo($file_path, PATHINFO_EXTENSION);
	if ($extension != 'oga' && $extension != 'ogg')
		$file_path .= '.oga';
	$filename = dirname(__FILE__) . '/downloads/'. $user . '/'. $file_path;
	$dirname = dirname($filename);

	if (!is_dir($dirname))
	{
	    mkdir($dirname, 0775, true);
	}

	$fp = fopen($filename, 'w+');
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_TIMEOUT, 50);
	// write curl response to file
	curl_setopt($ch, CURLOPT_FILE, $fp); 
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	// get curl response
	$output = curl_exec($ch); 
	curl_close($ch);
	fclose($fp);

	return $output !== FALSE;	
}

function update_download_timestamp($fileId){
	global $mysqli;

	$update = "update voicecache set downloaded_at = NOW() where fileId = '". $fileId ."'";
	if(!$mysqli->query($update))
	{
	    die("Error: " . $update . "\n" . $mysqli->error);
	}

	
}

function get_or_retrieve($fileId, $conversation_id, $user_id, $question, $challenge){
	global $mysqli, $URL_BASE, $API_KEY;


	$audiors = $mysqli->query("select * from voicecache where fileId = '". $fileId ."' and downloaded_at IS NOT NULL");

	if($audiors->num_rows === 0)
	{
		echo "Retrieving $fileId for conversation $conversation_id ( challenge: $challenge question: $question )\n";
		$details = json_decode(get_details("https://api.telegram.org/bot". $API_KEY ."/getFile?file_id=". $fileId));
		$file_path = $details->result->file_path;

		add_to_cache($fileId, $file_path, $conversation_id, $user_id, $question, $challenge);

		$ok = download($user_id, $URL_BASE . $API_KEY , $file_path);
		if ($ok)
			update_download_timestamp($fileId);


	}else{

		$audio = $audiors->fetch_assoc();
		$file_path = $audio['filePath'];

	}
}



for($chal=1; $chal<= $NUM_CHALLENGES; $chal++){
	echo "Preparando URLs para challenge $chal: \n";
	$selection = "SELECT 
			u.username, u.first_name, u.last_name, c.id as c_id, c.user_id, c.chat_id, c.notes, c.created_at, c.updated_at
			FROM conversation c, user u
			where command LIKE 'Reto". $chal ."%'
			and c.user_id = u.id";
			// and status = 'stopped'

	$emaitzak = $mysqli->query($selection);

	// print_r("DEBUG:" . $selection . "\n");

	echo "Num questions:" . $NUM_QUESTIONS ."\n";


	while ($lerro = $emaitzak->fetch_assoc()) {
		echo $lerro['username'] . " ";
		echo $lerro['first_name']." ";
		echo $lerro['last_name'] .  " ";
	//	echo $lerro['user_id'] . " ";
	//	echo $lerro['chat_id'] . " ";
	//	echo $lerro['created_at'] . " ";
		echo $lerro['updated_at']  . "\n";

		$obj = json_decode($lerro['notes']);

		for($ind=1; $ind <= $NUM_QUESTIONS; $ind++){
			if (isset($obj->{"question". $ind})){
				get_or_retrieve($obj->{"question" . $ind}, $lerro['c_id'], $lerro['user_id'], $ind, $chal);
			}
		}
	}
}


