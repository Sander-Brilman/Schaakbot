<?php
/**
 * Page for debugging & testing, can be accessed by https://url-of-the-website/test
 */

$board = $_SESSION['game_data']['board'];


move_piece($board, '0-5', '1-6');

// dump(calculate_move($board, 'top'));



// echo export_board($board);

echo '<link rel="stylesheet" href="assets/css/play.css">';

display_board($board);

$timer = start_timer();

dump(calculate_move($board, 'top'));

// $moves = best_movements($board,'top', 9999);

// foreach ($moves as $move) {
//     dump(from_to($move['move']).': '.$move['score']);
// }

end_timer($timer, 'calculating move');

exit;

$s1 = $board;
$s2 = $board;

move_piece($s1, '2-1', '2-3');
move_piece($s2, '0-0', '1-0');

// move_piece($s1, '1-6', '3-4');
display_board($s1, 's1: '.board_score($s1, 'top', 'top'));

echo BR;
echo BR;
echo BR;

// move_piece($s2, '1-6', '3-4');
display_board($s2, 's2: '.board_score($s2, 'top', 'top'));
