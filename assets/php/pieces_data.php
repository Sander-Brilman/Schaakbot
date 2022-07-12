<?php
require_once('functions.php');

function pawn_movements(array $board, array $cor)
{
	/** 
	 * calculate movements + the attack squares for the pawn
	 * 
	 * @param array the board array
	 * @param array the location of the piece
	 * 
	 * @return array array with all the movements & attack squares for the pawn
	*/
	$current_cor = $cor;
	$piece = get_piece($board, $cor);
	$team = $piece['team'];
	$opposite_team = opposite_team($team);
	$possible_movements = [
		'movements' => [],
		'attack_squares' => [],
	];

	// 1 step ahead
	$current_cor['y'] += $team == "top" ? 1 : -1;
	if (valid_cor($current_cor)) {

        $square_data = get_piece($board, $current_cor);
        if ($square_data['name'] == '') {

            $possible_movements['movements'][] = $current_cor;

            // 2 steps ahead if it hasent moved yet
            if (!$piece['has_moved']) {
                $current_cor['y'] += $team == "top" ? 1 : -1;
                $square_data = get_piece($board, $current_cor);
                if (valid_cor($current_cor) && $square_data['name'] == "") {
                    $possible_movements['movements'][] = $current_cor;
                }
                $current_cor['y'] += $team == "top" ? -1 : 1;
            }
        }
	}

	// left/right
	$current_cor['x'] += 1;
	if (valid_cor($current_cor)) {
		$square_data = get_piece($board, $current_cor);
		$possible_movements['attack_squares'][] = $current_cor;
		if ($square_data['team'] == $opposite_team) {
			$possible_movements['movements'][] = $current_cor;
		}
	}

	// left/right
	$current_cor['x'] -= 2;
	if (valid_cor($current_cor)) {
		$square_data = get_piece($board, $current_cor);
		$possible_movements['attack_squares'][] = $current_cor;
		if ($square_data['team'] == $opposite_team) {
			$possible_movements['movements'][] = $current_cor;
		}
	}

	return $possible_movements;
}

function horse_movements(array $board, array $cor)
{
	/** 
	 * calculate movements + the attack squares for the horse
	 * 
	 * @param array the board array
	 * @param array the location of the piece
	 * 
	 * @return array array with all the movements & attack squares for the horse
	*/
	$team = get_piece($board, $cor)['team'];
	$opposite_team = opposite_team($team);
	$possible_movements = [
		'movements' => [],
		'attack_squares' => [],
	];
	$directions = [
		[ 2,  1],
		[ 1,  2],
		[-2,  1],
		[ 1, -2],
		[-1, -2],
		[-2, -1],
		[-1,  2],
		[ 2, -1],
	];
	
	foreach ($directions as $direction) {
		$current_cor = $cor;
		$current_cor['x'] += $direction[0];
		$current_cor['y'] += $direction[1];

		if (!valid_cor($current_cor)) {
			continue;
		}

		$piece = get_piece($board, $current_cor);

		if ($piece['name'] == "" || $piece['team'] == $opposite_team) {
			$possible_movements['movements'][] = $current_cor;
    		$possible_movements['attack_squares'][] = $current_cor;
		}
	}
	
	return $possible_movements;
}

function bishop_movements(array $board, array $cor)
{
	/** 
	 * calculate movements + the attack squares for the bishop
	 * 
	 * @param array the board array
	 * @param array the location of the piece
	 * 
	 * @return array array with all the movements & attack squares for the bishop
	*/

	$piece = get_piece($board, $cor);
	$opposite_team = opposite_team($piece['team']);
	$possible_movements = [
		'movements' => [],
		'attack_squares' => [],
	];
	$directions = [
		[ 1,  1],
		[-1, -1],
		[ 1, -1],
		[-1,  1],
	];
	
	foreach ($directions as $direction) {
		$current_cor = $cor;
		for ($i = 0; $i < 7; $i++) {
			$current_cor['y'] += $direction[0];
			$current_cor['x'] += $direction[1];

			if (!valid_cor($current_cor)) {
				break;
			}

			$possible_movements['attack_squares'][] = $current_cor;
			$square_data = get_piece($board, $current_cor);
			if ($square_data['name'] == "" || $square_data['team'] == $opposite_team) {
				$possible_movements['movements'][] = $current_cor;
				if ($square_data['team'] == $opposite_team) {
					break;
				}
			} else {
				break;
			}

		}
	}

	return $possible_movements;
}

function tower_movements(array $board, array $cor)
{
	/** 
	 * calculate movements + the attack squares for the tower
	 * 
	 * @param array the board array
	 * @param array the location of the piece
	 * 
	 * @return array array with all the movements & attack squares for the tower
	*/

	$piece = get_piece($board, $cor);
	$opposite_team = opposite_team($piece['team']);
	$possible_movements = [
		'movements' => [],
		'attack_squares' => [],
	];
	$directions = [
		[ 0,  1],
		[ 1,  0],
		[ 0, -1],
		[-1,  0],
	];
	
	foreach ($directions as $direction) {
		$current_cor = $cor;
		for ($i = 0; $i < 7; $i++) {

			$current_cor['y'] += $direction[0];
			$current_cor['x'] += $direction[1];

			if (!valid_cor($current_cor)) {
				break;
			}

			$possible_movements['attack_squares'][] = $current_cor;
			$square_data = get_piece($board, $current_cor);
			if ($square_data['name'] == "" || $square_data['team'] == $opposite_team) {
				$possible_movements['movements'][] = $current_cor;
				if ($square_data['team'] == $opposite_team) {
					break;
				}
			} else {
				break;
			}

		}
	}
	
	return $possible_movements;
}

function queen_movements(array $board, array $cor)
{
	/** 
	 * calculate movements + the attack squares for the queen
	 * uses the tower and bishop calculations
	 * 
	 * @param array the board array
	 * @param array the location of the piece
	 * 
	 * @return array array with all the movements & attack squares for the queen
	*/
	$bishop_movements = bishop_movements($board, $cor);
	$tower_movements  = tower_movements ($board, $cor);
	$total_movements = [];
	
	foreach ($bishop_movements as $key => $value) {
		$total_movements[$key] = array_merge($tower_movements[$key], $bishop_movements[$key]);
	}

	return $total_movements;
}

function king_movements(array $board, array $cor){
	/** 
	 * calculate movements + the attack squares for the king
	 * 
	 * @param array the board array
	 * @param array the location of the piece
	 * 
	 * @return array array with all the movements & attack squares for the king
	*/
	$towers_x               = [0,7];
	$piece                  = get_piece($board, $cor);
	$opposite_team          = opposite_team($piece['team']);
	$possible_movements     = [
		'movements' =>      [],
		'attack_squares' => [],
	];
	$directions             = [
		[ 1,  1],
		[-1, -1],
		[ 0,  1],
		[ 1, -1],
		[ 1,  0],
		[-1,  1],
		[ 0, -1],
		[-1,  0],
	];
	
	foreach ($directions as $direction) {
		$new_cor = $cor;
		$new_cor['x'] += $direction[0];
		$new_cor['y'] += $direction[1];

		if (!valid_cor($new_cor)) {
			continue;
		}

		$data = get_piece($board, $new_cor);
		$possible_movements['attack_squares'][] = $new_cor;
		if ($data['name'] == "" || $data['team'] == $opposite_team) {
			$possible_movements['movements'][] = $new_cor;
		}
	}
	
	if ($piece['has_moved'] || is_square_attacked($board, $piece['team'], $cor)) {
		return $possible_movements;
	}


	// special movements
	foreach ($towers_x as $tower_x) {
		$tower_cor = $cor;
		$tower_cor['x'] = $tower_x;
		$tower = get_piece($board, $tower_cor);

		if ($tower['name'] == 'tower' && !$tower['has_moved'] && valid_castling($board, $cor, $tower_cor)) {
			$new_cor = $cor;
			$new_cor['x'] += $tower_x == 0 ? -2 : 2;

			$possible_movements['movements'][] = $new_cor;
		}
	}

	return $possible_movements;
}

function get_movements(array $board, array $cor)
{
	/**
	 * get the movements for a piece
	 * 
	 * @param array the board array
	 * @param array the location of the piece
	 * 
	 * @return array array with all the movements & attack squares 
	*/

	$piece = get_piece($board, $cor);
	switch ($piece['name']) {
		case 'king':
			return king_movements($board, $cor);
			break;

		case 'queen':
			return queen_movements($board, $cor);
			break;

		case 'tower':
			return tower_movements($board, $cor);
			break;

		case 'bishop':
			return bishop_movements($board, $cor);
			break;

		case 'horse':
			return horse_movements($board, $cor);
			break;

		case 'pawn':
			return pawn_movements($board, $cor);
			break;
	}

	return [
		'movements' => [],
		'attack_squares' => [],
	];


}

function get_value($name)
{
	/**
	 * get the value for a piece
	 * 
	 * @param string the name of the piece
	 * 
	 * @return array array with all the movements & attack squares 
	*/
	switch ($name) {
		case 'king':
			return 0;
			break;
		case 'queen':
			return 950;
			break;
		case 'tower':
			return 500;
			break;
		case 'bishop':
			return 300;
			break;
		case 'horse':
			return 300;
			break;
		case 'pawn':
			return 100;
			break;
		default:
			return 0;
			break;
	}
}

function valid_castling(array $board, array $king_cor, array $tower_cor) {
	/**
	 * Check if castling movement is possible.
     * Used for the king movements calculation
	 * 
	 * @param array the board array
	 * @param array the location of the king
	 * @param array the location of the tower you want to castle with
	 * 
	 * @return bool
	*/
	$y = $king_cor['y'];
	$king = get_piece($board, $king_cor);

	$end = $king_cor['x'] > $tower_cor['x'] ? $king_cor['x'] : $tower_cor['x'];
	$start = $king_cor['x'] > $tower_cor['x'] ? $tower_cor['x'] : $king_cor['x'];

	for ($x = $start + 1; $x < $end; $x++) {
		$square_cor = "$x-$y";
		$square_data = get_piece($board, $square_cor);
		if ($square_data['name'] != "" || is_square_attacked($board, $king['team'], create_cor($square_cor))) {
			return false;
		}
	}
	return true;
}

?>