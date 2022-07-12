<?php

$board = $_SESSION['game_data']['board'];



$timer = start_timer();

// update_movements($board, [], true);

dump($board['top_king']);

// dump(get_piece($board, '4-0'));
// $moves = get_movements($board, create_cor('4-0'));

// foreach ($moves['movements'] as $index => $move) {
//     dump(cor_string($move));
//     dump(valid_move($board, 'top', create_cor('4-0')))
// }

// foreach (get_piece($board, '3-7')['movements'] as $cor) {
//     dump(cor_string($cor));
// }

// echo BR;
// echo BR;
// echo BR;
// echo BR;

// dump('test');


// foreach ($movements['movements'] as $move) {
//     dump(cor_string($move));

// }




end_timer($timer, 'calculating move');
?>
<table id="board" style="width: 500px; height: 500px">
	<?php
	for ($i=0; $i < 8; $i++) {
		echo '<tr>';
			for ($j=0; $j < 8; $j++) {
				$cor = "$j-$i";
				$piece = get_piece($board, $cor);
				$content = $piece['name'] != '' ?'<div style="--piece-color: '.$_SESSION['game_data']['color'].'; --shadow-color: '.$_SESSION['game_data']['shadow'].';" class='.$piece['team'].'>'.$icons[$piece['name']].'</div>' : '';
				echo "<td title='$cor' id='$cor'>$content</td>";
			}
		echo '</tr>';
	}
	?>
</table>
<link rel="stylesheet" href="assets/css/play.css">

