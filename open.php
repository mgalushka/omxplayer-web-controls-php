<?php
header('Cache-Control: no-cache, must-revalidate');
header('Content-Type: application/json; charset=utf-8');

error_reporting(E_ALL);

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
				array_push($directories, realpath($item));
			}
			else
			{
				array_push($files, ($item));
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

// List the directories as browsable navigation
echo "{\n";
echo "\"path\" : \"${path}\"";
if(!empty($directories) || !empty($files)){
	echo ",\n\"files\" : [";
}
foreach( $directories as $directory ){
	echo ($directory != $path) ? "\"dir\" : \"${directory}\"," : "";
}

foreach( $files as $file ){
	// Comment the next line out if you wish see hidden files while browsing
	if(preg_match("/^\./", $file) || $file == $script): continue; endif; // This line will hide all invisible files.
	echo "\"file\" : \"${file}\"," : "";
}
if(!empty($directories) || !empty($files)){
	echo "]\n";
}
echo "}\n";

?>

