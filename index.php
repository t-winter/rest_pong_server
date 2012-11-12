<?php
/*
/ignores all sent data (e.g. params via GET, POST-Data) except these inside the URI
/returned data is JSON-formatted
*/

if (!session_start()) {exit('unable to start/resume session!');}
$defaultGame=['players'=>['left'=>'', 'right'=>''], 'secretLeft'=>'', 'secretRight'=>'', 'leftMoveCounter'=>0, 'rightMoveCounter'=>0, 'scoreLeft'=>0, 'scoreRight'=>0, 'ball'=>[0=>250.0, 1=>200.0], 'ballDelta'=>[0=>5.0, 1=>0.0], 'paddleLeft'=>0, 'paddleRight'=>0, 'status'=>'login', 'time'=>0, 'lastUpdate'=>0]; //ball[0]=x-pos, ball[1]=y-pos, ballDelta[0]=x-delta per step, ballDelta[1]=y-delta per step
$uri = explode('/', $_SERVER['REQUEST_URI']);
$method = $_SERVER['REQUEST_METHOD'];
$uri_base_index=1; //index of 'game' in $uri
foreach ($uri as $param) {if ($param=='game'){break;} else {$uri_base_index++;}}
if ($uri[$uri_base_index]=='basicConfig') {return_http_code(400);}
$verb='';
if (array_key_exists($uri_base_index+1, $uri)) {$verb=$uri[$uri_base_index+1];}//$uri[$uri_base_index]==key==session_id==id of the game
else {return_http_code(400);}
try {
switch($method) {
case 'GET': if ($verb=='status') {get_status($uri[$uri_base_index]); break;}
	else if ($verb=='config') {get_config($uri[$uri_base_index]); break;}
case 'POST': if ($verb=='start') {post_start($uri[$uri_base_index]); break;}
	else if ($verb=='move') {post_move($uri[$uri_base_index], $uri[$uri_base_index+2], $uri[$uri_base_index+3], $uri[$uri_base_index+4]); break;}
case 'PUT': if ($verb=='player') {put_player($uri[$uri_base_index], $uri[$uri_base_index+2]); break;}
default: return_http_code(404);
}
} catch (Exception $e) {return_http_code(404);}

function basicGameConfig() {
$_SESSION['basicConfig']=['FIELD_WIDTH'=>500, 'FIELD_HEIGHT'=>400, 'BALL_RADIUS'=>5, 'PADDLE_HEIGHT'=>50, 'PADDLE_WIDTH'=>10, 'PADDLE_STEP'=>5, 'NUMBER_OF_PADDLE_MOVES'=>10, 'ACCELERATOR'=>10, 'INITIAL_BALL_SPEED'=>10, 'WAIT_BEFORE_START'=>5, 'SCORE_TO_WIN'=>10];
}

function get_status($key) {
if (!array_key_exists($key, $_SESSION)) {return_http_code(404);}
$tmp=$_SESSION[$key];
unset($tmp['secretLeft'], $tmp['secretRight'], $tmp['leftMoveCounter'], $tmp['rightMoveCounter'], $tmp['time'], $tmp['lastUpdate']);
if ($tmp['status']=='started') {recalculateBallPosition($key);}
echo json_encode($tmp);
}

function get_config($key) {
if (!array_key_exists('basicConfig', $_SESSION)) {basicGameConfig();}
echo json_encode($_SESSION['basicConfig']);
}

function post_start($key) {
if (!array_key_exists($key, $_SESSION)) {return_http_code(404);}
switch ($_SESSION[$key]['status']) {
	case 'login': while ($_SESSION[$key]['status']=='login') {sleep(1);}
	case 'ready': time_sleep_until($_SESSION[$key]['time']+$_SESSION['basicConfig']['WAIT_BEFORE_START']); $_SESSION[$key]['status']='started';
	case 'started': echo 'ok'; startGame($key); return_http_status(200); break;
}}

function post_move($key, $playername, $secret, $direction) {
if (!array_key_exists($key, $_SESSION)||$_SESSION[$key]['status']!='started') {return_http_code(404);}
if ($_SESSION[$key]['time']!=time()) {$_SESSION[$key]['time']=time(); $_SESSION[$key]['leftMoveCounter']=0; $_SESSION[$key]['rightMoveCounter']=0;}
switch ($playername) {
case $_SESSION[$key]['players']['left']:
	if ($secret!=$_SESSION[$key]['secretLeft']) {echo 'Wrong secret code.'; return_http_code(400);}
	if ($_SESSION[$key]['leftMoveCounter']>=$_SESSION['basicConfig']['NUMBER_OF_PADDLE_MOVES']) {echo 'Too many moves.'; return_http_code(500);}
	switch ($direction) {
		case 'up': $_SESSION[$key]['paddleLeft']-=$_SESSION['basicConfig']['PADDLE_STEP']; break;
		case 'down': $_SESSION[$key]['paddleLeft']+=$_SESSION['basicConfig']['PADDLE_STEP']; break;
	}
	$_SESSION[$key]['leftMoveCounter']++;
	if ($_SESSION[$key]['paddleLeft']+$_SESSION['basicConfig']['PADDLE_HEIGHT']>$_SESSION['basicConfig']['FIELD_HEIGHT']) {$_SESSION[$key]['paddleLeft']=$_SESSION['basicConfig']['FIELD_HEIGHT']-$_SESSION['basicConfig']['PADDLE_HEIGHT'];}
	else if ($_SESSION[$key]['paddleLeft']<0) {$_SESSION[$key]['paddleLeft']=0;}
	echo 'ok';
	recalculateBallPosition($key);
	return_http_code(200);
	break;
case $_SESSION[$key]['players']['right']:
	if ($secret!=$_SESSION[$key]['secretRight']) {echo 'Wrong secret code.'; return_http_code(400);}
	if ($_SESSION[$key]['rightMoveCounter']>=$_SESSION['basicConfig']['NUMBER_OF_PADDLE_MOVES']) {echo 'Too many moves.'; return_http_code(500);}
	switch ($direction) {
		case 'up': $_SESSION[$key]['paddleRight']-=$_SESSION['basicConfig']['PADDLE_STEP']; break;
		case 'down': $_SESSION[$key]['paddleRight']+=$_SESSION['basicConfig']['PADDLE_STEP']; break;
	}
	$_SESSION[$key]['rightMoveCounter']++;
	if ($_SESSION[$key]['paddleRight']+$_SESSION['basicConfig']['PADDLE_HEIGHT']>$_SESSION['basicConfig']['FIELD_HEIGHT']) {$_SESSION[$key]['paddleRight']=$_SESSION['basicConfig']['FIELD_HEIGHT']-$_SESSION['basicConfig']['PADDLE_HEIGHT'];}
	else if ($_SESSION[$key]['paddleRight']<0) {$_SESSION[$key]['paddleRight']=0;}
	echo 'ok';
	recalculateBallPosition($key);
	return_http_code(200);
	break;
default: echo 'Invalid playername.'; return_http_code(400); break;
}}

function put_player($key, $playername) {
if (!$playername) {return_http_code(510);}
$secret='';
$secret_chars='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQERSTUVWXYZ0123456789';
shuffle($secret_chars);
for ($i=0;$i<30;$i++) {$secret+=array_rand($secret_chars);}
if (!array_key_exists($key, $_SESSION)) {
	if (!array_key_exists($_SESSION['basicConfig'])) {basicGameConfig();}
	$_SESSION[$key]['players']['left']=$playername;
	$_SESSION[$key]['secretLeft']=$secret;
	echo $secret;
} else if ($_SESSION[$key]['players']['left']!=$playername&&$_SESSION[$key]['players']['right']=='') {
	$_SESSION[$key]['players']['right']=$playername;
	$_SESSION[$key]['secretRight']=$secret;
	echo $secret;
	$_SESSION[$key]['status']='ready';
	$_SESSION[$key]['time']=time();
} else {
	return_http_code(423);//Only 2 players per game allowed
}}

function startGame($key) {
$_SESSION[$key]['ball']=[$_SESSION['basicConfig'][FIELD_WIDTH]/2, $_SESSION['basicConfig'][FIELD_HEIGHT]/2];
$_SESSION[$key]['ballDelta']=[(-1*rand(0,1))*$_SESSION['basicConfig']['INITIAL_BALL_SPEED'], rand(-$_SESSION['basicConfig']['INITIAL_BALL_SPEED']*100,$_SESSION['basicConfig']['INITIAL_BALL_SPEED']*100)/100.0];
$_SESSION[$key]['lastUpdate']=microtime(true);
}

function recalculateBallPosition($key) {
$newTime=microtime(true);
$timeDelta=$newTime-$_SESSION[$key]['lastUpdate'];
$_SESSION[$key]['ball'][0]=$_SESSION[$key]['ballDelta'][0]*$timeDelta;
$_SESSION[$key]['ball'][1]=$_SESSION[$key]['ballDelta'][1]*$timeDelta;
if ($_SESSION[$key]['ballDelta'][1]<0||$_SESSION[$key]['ballDelta'][1]>$_SESSION['basicConfig']['FIELD_HEIGHT']) $_SESSION[$key]['ballDelta'][1]*=-1;
if ($_SESSION[$key]['ball'][0]<$_SESSION['basicConfig']['PADDLE_WIDTH']+$_SESSION['basicConfig']['BALL_RADIUS']) {
	if ($_SESSION[$key]['ball'][1]>=$_SESSION[$key]['paddleLeft']||$_SESSION[$key]['ball'][1]<=$_SESSION[$key]['paddleLeft']+$_SESSION['basicConfig']['PADDLE_HEIGHT']) {
		$_SESSION[$key]['ballDelta'][0]*=-1*(100/$_SESSION['basicConfig']['ACCELERATOR']);
		$_SESSION[$key]['ballDelta'][1]*=(100/$_SESSION['basicConfig']['ACCELERATOR']);
	} else {
		$_SESSION[$key]['scoreRight']+=1;
		startGame($key);
		checkWinner($key);
	}
} else if ($_SESSION[$key]['ball'][0]>$_SESSION['basicConfig']['FIELD_WIDTH']-($_SESSION['basicConfig']['PADDLE_WIDTH']+$_SESSION['basicConfig']['BALL_RADIUS'])) {
	if ($_SESSION[$key]['ball'][1]>=$_SESSION[$key]['paddleRight']||$_SESSION[$key]['ball'][1]<=$_SESSION[$key]['paddleRight']+$_SESSION['basicConfig']['PADDLE_HEIGHT']) {
		$_SESSION[$key]['ballDelta'][0]*=-1*(100/$_SESSION['basicConfig']['ACCELERATOR']);
		$_SESSION[$key]['ballDelta'][1]*=(100/$_SESSION['basicConfig']['ACCELERATOR']);
	} else {
		$_SESSION[$key]['scoreLeft']+=1;
		startGame($key);
		checkWinner($key);
	}
}
}

function checkWinner($key) {
if ($_SESSION[$key]['scoreLeft']>=$_SESSION['basicConfig']['SCORE_TO_WIN']) {
	echo $_SESSION[$key]['playerLeft'].' wins!';
	unset($_SESSION[$key]);
	exit ();
} else if ($_SESSION[$key]['scoreRight']>=$_SESSION['basicConfig']['SCORE_TO_WIN']) {
	echo $_SESSION[$key]['playerRight'].' wins!';
	unset($_SESSION[$key]);
	exit ();
}
}

function return_http_code($code) {
echo $code;
http_response_code($code);
exit();
}
?>