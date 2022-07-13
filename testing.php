<?php
/**
 * Page for debugging & testing, can be accessed by https://url-of-the-website/test
 */

$board = $_SESSION['game_data']['board'];

$color_2 	= $_SESSION['game_data']['color'] == 'white' ? 'rgb(135 91 44)' : 'wheat';
$color_1 	= $_SESSION['game_data']['color'] == 'white' ? 'wheat' : 'rgb(135 91 44)';
$css_vars 	= '
--piece-color: '.$_SESSION['game_data']['color'].';
--shadow-color: '.$_SESSION['game_data']['shadow'].';
--square-color-1: '.$color_1.';
--square-color-2: '.$color_2.';
';
?>
<div class="main" style="<?= $css_vars ?>">
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
</div>
