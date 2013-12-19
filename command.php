<?php
	//require_once 'config.php';

	header('Content-type: application/json');
	error_reporting(E_ALL);
	
	define('FIFO', getcwd().'/omxplayer_fifo');
	
	$verb = $_SERVER['REQUEST_METHOD'];
	
	if($verb === 'POST'){
		// internal usage - so don't need to escape	
		
		$body = file_get_contents('php://input');
		
		
		$request = json_decode($body, true);
		$action = $request['request'];
						
		echo json_encode(array('action' => $action, 'result' => 'OK'));
	}
	else{	
		echo json_encode(array('error' => 'Action should be sent with POST verb'));
	}	
	
	function play($file) {
		$err = '';
		exec('pgrep omxplayer', $pids);  //omxplayer
		if ( empty($pids) ) {
			@unlink (FIFO);
			posix_mkfifo(FIFO, 0777);
			chmod(FIFO, 0777);
			shell_exec ( getcwd().'/omx_php.sh '.escapeshellarg($file));
			$out = 'playing '.basename($file);
		} else {
			$err = 'omxplayer is already runnning';
		}
		return array ( 'res' => $out, 'err' => $err );
	}

	function send($command) {
		$err = '';
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
						$out = 'stopped';
					}
				}
			}
		} else {
			$err .= 'not running';
		}
		return array ( 'res' => $out, 'err' => $err );
	}

	
	if(false) {
		$act = $_REQUEST['act'];
		unset($result);

		switch ($act) {

			case 'play':
			$result = play($_REQUEST['arg']);
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
			$err = 'wrong command';
		}
	}

?>
