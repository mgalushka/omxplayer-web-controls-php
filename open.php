<?php
header('Cache-Control: no-cache, must-revalidate');
header('Content-Type: application/json; charset=utf-8');

error_reporting(0);

//////////////////////////////////////////////////////////////////////////////////
// Explore the files via a web interface.
$script = basename(__FILE__); // the name of this script
$path = !empty($_REQUEST['path']) ? $_REQUEST['path'] : dirname(__FILE__); // the path the script should access

if ($_POST['save'] == 'save') {
	// TODO: somehow save current playing
}

$directories = array();
$files = array();

// Check we are focused on a dir
if (is_dir($path)) {
	chdir($path); // Focus on the dir
	if ($handle = opendir('.')) {
		while (($item = readdir($handle)) !== false) {
			// Loop through current directory and divide files and directorys
			if(is_dir($item)){
				array_push($directories, json_encode(realpath($item)));
			}
			else
			{
				array_push($files, json_encode(realpath($item)));
			}
		}
		closedir($handle); // Close the directory handle
	}
	else {
		echo "{\"error\" : \"Directory handle could not be obtained.\"}";
		exit;
	}
}
else
{
	echo "{\"error\" : \"Path is not a directory.\"}"; 
	exit;
}
asort($directories, SORT_NATURAL);
asort($files, SORT_NATURAL);
// There are now two arrays that contians the contents of the path.
// List the directories as json
echo "{\n";
$escaped_path = json_encode($path);
echo "\"path\" : ${escaped_path}";
if(!empty($directories) || !empty($files)){
	echo ",\n\"content\" : [\n";
}

$i = 0;
$len = count($directories);
foreach( $directories as $directory ){
	if($directory != $path){
		echo "\t{\"dir\" : ${directory}}";
		if ($i < $len - 1){
			echo ",\n";
		}		
	}
	$i++;
}

if(!empty($files) || !empty($files)){
	echo ",\n";
}

$i = 0;
$len = count($files);
foreach( $files as $file ){
	// Comment the next line out if you wish see hidden files while browsing
	// This line will hide all invisible files.
	if(preg_match("/^\./", $file) || $file == $script){
		$i++;
		continue;
	}		
	echo "\t{\"file\" : ${file}}";
	if ($i < $len - 1){
		echo ",\n";
	}
	$i++;
}
if(!empty($directories) || !empty($files)){
	echo "\n\t]\n";
}
echo "}\n";

?>

