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

function dump($data) {
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


define('BR', '<br>');

?>