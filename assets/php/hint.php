<?php
require_once('functions.php');
require_once('pieces_data.php');
require_once('chess_bot_functions.php');

session_start();
$board = $_SESSION['game_data']['board'];
$move = calculate_move($board, 'bottom', 1000);
echo json_encode($move['move']);
?>