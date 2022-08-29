<?php
/**
 * Functions for debugging.
 */
function start_timer()
{
    return microtime(true);
}

function end_timer($timer_start, $feedback)
{
    echo "millisec passed $feedback -> ".(microtime(true)-$timer_start)*1000;
}

function dump($data) 
{
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
}

function highlight_square($cor)
{
    $cor = cor_string($cor);
    ?>
    <script> 
	$(document).ready(function() {
        $('#<?= $cor ?>').addClass("path");
    })
    </script>

    <?php
}

function from_to($move)
{
    return cor_string($move['from']).' -> '.cor_string($move['to']);
}

function display_board($board, $message = '')
{
    global $icons;
    $color_2 	= $_SESSION['game_data']['color'] == 'white' ? 'rgb(135 91 44)' : 'wheat';
    $color_1 	= $_SESSION['game_data']['color'] == 'white' ? 'wheat' : 'rgb(135 91 44)';
    $css_vars 	= '
    --piece-color: '.$_SESSION['game_data']['color'].';
    --shadow-color: '.$_SESSION['game_data']['shadow'].';
    --square-color-1: '.$color_1.';
    --square-color-2: '.$color_2.';';
    ?>
    <div class="main" style="<?= $css_vars ?>">
        <h3><?= $message ?></h3>
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
    </div>
    <?php
}

function export_board($board)
{
    $board_string = '';
    foreach ($board['squares'] as $cor_str => $piece) {
        $has_moved = $piece['has_moved'] ? 'true' : 'false';
        $board_string .= $cor_str.','.$piece['name'].','.$piece['team'].','.$has_moved.'|';
    }
    return $board_string;
}

define('BR', '<br>');
?>