<?php
	//require_once 'config.php';

	header('Content-type: application/json');
	error_reporting(E_ALL);
	
	define('FIFO', getcwd().'/omxplayer_fifo');
	
	$verb = $_SERVER['REQUEST_METHOD'];
	
	if($verb === 'POST'){
		
		
		$body = file_get_contents('php://input');
				
		$request = json_decode($body, true);
		$action = $request['request'];
						
		switch ($action) {
			case 'browse':
				$path = $request['path'];
				$result = browse($path);
			break;
			case 'play':
				$path = $request['path'];
				$result = play($path);
			break;

			case 'stop';
				$result = send('q');
			break;

			case 'pause';
				$result = send('p');
			break;

			case 'volup';
				$result = send('+');
			break;

			case 'voldown';
				$result = send('-');
			break;

			case 'seek-30';
				$result = send(pack('n',0x5b44));
			break;

			case 'seek30';
				$result = send(pack('n',0x5b43));
			break;

			case 'seek-600';
				$result = send(pack('n',0x5b42));
			break;

			case 'seek600';
				$result = send(pack('n',0x5b41));
			break;

			default:
				$error = 'Wrong command: '.$action;
		}
		
		if(!empty($error)){
			echo json_encode(array('error' => $error));
			exit();
		}
		if(!empty($result['error'])){
			echo json_encode(array('error' => $result['error']));
			exit();
		}
		echo json_encode(array('action' => $action, 'result' => $result['result']));
	}
	else{	
		echo json_encode(array('error' => 'Action should be sent with POST verb'));
	}	
	
	function play($file) {
		$out = "running";
		$error = "";
		return array ( 'result' => $out, 'error' => $error );
	}
	
	function send($command) {
		$out = $command." running";
		$error = "";
		return array ( 'result' => $out, 'error' => $error );
	}
	
	function browse($path){
		$result = array("path" => $path);
		$content = array();
		// Check we are focused on a dir
		if (is_dir($path)) {
			chdir($path); // Focus on the dir
			if ($handle = opendir('.')) {
				while (($item = readdir($handle)) !== false) {
					// Loop through current directory and divide files and directorys
					if(is_dir($item)){
						array_push($content, array ("path" => realpath($item), "name" => "", "type" => "DIR"));
					}
					else
					{
						array_push($content, array ("path" => realpath($item), "name" => "", "type" => "FILE"));
					}
				}
				closedir($handle); // Close the directory handle
				$result["content"] = $content;
				return array ( 'result' => $result );
			}			
			else {
				return array ( 'result' => $out, 'error' => $path.' is not a directory' );
			}
		}
		else
		{
			return array ( 'result' => $out, 'error' => $path.' is not a directory' );
		}
	}
	
	function _play($file) {
		$error = '';
		exec('pgrep omxplayer', $pids);  //omxplayer
		if ( empty($pids) ) {
			@unlink (FIFO);
			posix_mkfifo(FIFO, 0777);
			chmod(FIFO, 0777);
			shell_exec ( getcwd().'/omx_php.sh '.escapeshellarg($file).' '.FIFO);
			$out = 'Playing '.basename($file);
		} else {
			$error = 'omxplayer is already runnning';
		}
		return array ( 'result' => $out, 'error' => $error );
	}

	function _send($command) {
		$error = '';
		exec('pgrep omxplayer', $pids);
		if ( !empty($pids) ) {
			if ( is_writable(FIFO) ) {
				if ( $fifo = fopen(FIFO, 'w') ) {
					stream_set_blocking($fifo, false);
					fwrite($fifo, $command);
					fclose($fifo);
					if ($command == 'q') {
						sleep (1);
						@unlink(FIFO);
						$out = 'Stopped';
					}
				}
			}
		} else {
			$error .= 'Not running';
		}
		return array ( 'result' => $out, 'error' => $error );
	}

?>