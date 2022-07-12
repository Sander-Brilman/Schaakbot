<?php
session_start();
include_once('assets/php/debug.php');
require_once("assets/php/functions.php");
require_once("assets/php/pieces_data.php");
require_once("assets/php/chess_bot_functions.php");

$url_array 	= $_SERVER['REQUEST_URI'];
$url_array 	= str_replace("/phprunning/website/subdomain_projects/chess/newFolders/versions/v3new/", '', $url_array);
$url_array 	= explode('/', $url_array);

foreach ($url_array as $key => $value) {
	$url_array[$key] = explode('?', $value)[0];
}

$include      = 'play.php';
$custom_pages = [
	'spelen'        => 'play.php',
	'nieuw-spel'    => 'new_game.php',
	'testen'        => 'testing.php',
	'test'          => 'testing.php',
];

foreach ($custom_pages as $title => $script) {
	if ($url_array[0] == $title) {
		$include = $script;
		break;
	}
}

// Special cases
if (!isset($_COOKIE['name'])) {
    $include = 'get_name.php';
}

$icons = [
	'tower' 	=> '<i class="fa-solid fa-chess-rook-piece"></i>',
	'horse'	 	=> '<i class="fa-solid fa-chess-knight"></i>',
	'bishop' 	=> '<i class="fa-solid fa-chess-bishop"></i>',
	'queen' 	=> '<i class="fa-solid fa-chess-queen"></i>',
	'king' 		=> '<i class="fa-solid fa-chess-king"></i>',
	'pawn' 		=> '<i class="fa-solid fa-chess-pawn"></i>',
];

?>
<!DOCTYPE html>
<html lang="nl">
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="title" content="Schaken versie v3 | Sander brilman">

		<link rel="stylesheet" href="assets/css/icons.css">
		<link rel="stylesheet" href="assets/css/style.css">
		<?php 
		if (file_exists('assets/css/'.str_replace('.php', '.css', $include))) {
			echo '<link rel="stylesheet" href="assets/css/'.str_replace('.php', '.css', $include).'">';
		}
		?>

		<script src="assets/js/jQuery.js"></script>


		<title>Schaken - v3 | Sander Brilman</title>
	</head>

	<body>
		<?php
		include $include;

        $file = 'assets/js/'.str_replace('.php', '.js', $include);
		if (file_exists($file)) echo '<script src="'.$file.'"></script>';
		?>
	</body>
</html>