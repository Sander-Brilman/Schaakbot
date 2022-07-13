<?php
function create_cor($string)
{
	/**
	 * Transforms a coordinate string to a coordinate array.
     * This makes it easier to calculate movements with
	 * if the value is already an array it will be returned unchanged
	 * 
	 * @param string coordinate string 
	 * 
	 * @return array array with X and Y
	 */
	if (is_string($string)) {
		$explode = explode('-', $string);
		return [
			'x' => (int)$explode[0],
			'y' => (int)$explode[1],
		];
	} else {
		return $string;
	}
}

function cor_string($cor) {
	/**
	 * Coordinate array back to string. 
     * 
	 * @param array coordinate string 
	 * 
	 * @return string array with X and Y
	*/
    return is_array($cor) ? $cor['x'] . '-' . $cor['y'] : $cor;
}

function valid_cor(array $cor)
{
	/**
	 * Check if the coordinate is within the board limits
	 * 
	 * @param array coordinate string 
	 * 
	 * @return bool
	 */
	return ($cor['x'] > 7 || $cor['x'] < 0 || $cor['y'] > 7 || $cor['y'] < 0) ? false : true;
}

function opposite_team(string $team)
{
	/**
	 * return a string of the opposite team
	 * 
	 * @param string the team you want the opposite from
	 * 
	 * @return string
	 */
	return $team == 'top' ? 'bottom' : 'top';
}

function get_piece(array $board, $cor)
{
	/**
	 * Get the piece data from a loaction on the board
	 * 
	 * @param array the board array
	 * @param array the location you want the data from
	 * 
	 * @return array a array containing the piece information
	 */
	return $board['squares'][cor_string($cor)];
}

function place_piece(array &$board, $cor, string $piece_name, string $piece_team, bool $has_moves = true)
{
	/**
	 * Place a piece on the board.
	 * Movements are not included.
	 * 
	 * @param array the board array
	 * @param array coordinates for where to put the piece
	 * @param string king | queen | tower | bishop | horse | pawn
	 * @param string  top | bottom
	 * 
	 * @return null
	 */
	$cor = is_string($cor) ? create_cor($cor) : $cor;
	$board['squares'][cor_string($cor)] = [
		'name' 		=> 	$piece_name,
		'team' 		=> 	$piece_team,
		'has_moved' =>  $has_moves,
	];
	if ($piece_name == 'king') {
		$board[$piece_team.'_king'] = $cor;
	}
}

function remove_piece(array &$board, $cor) {
	/**
	 * Removes a piece from the board.
     * The array keys will still be there but they will be empty.
	 * 
	 * @param array the board array
	 * @param array coordinates array
	 * 
	 * @return null
	 */
	$board['squares'][cor_string($cor)] = ['name' => '', 'team' => '', 'has_moved' => true];
}

function move_piece(array &$board, $old_cor, $new_cor, bool $update_movements = true)
{
	/**
	 * Move a piece from the old coordinates to the new coordinates.
	 * Automatically updates the movements.
	 * 
	 * @param array the board array
	 * @param array/string the current location of the piece
	 * @param array/string the location where the piece needs to go
	 * @param bool if the movements of the pieces are updated.
	 * 
	 * @return void 
	 */
	$piece 				= get_piece($board, $old_cor);
	$old_cor			= create_cor($old_cor);
	$new_cor			= create_cor($new_cor);
	$changed_squares 	= [$old_cor, $new_cor];

	// code for castling
    $castling_check = check_castling($board, $old_cor, $new_cor);
    if ($castling_check !== false) {

        move_piece($board, $castling_check['from'], $castling_check['to'], false);
		$changed_squares[] = $castling_check['from'];
		$changed_squares[] = $castling_check['to'];

    }

	place_piece($board, $new_cor, $piece['name'], $piece['team']);
	remove_piece($board, $old_cor);

    // replace a pawn with a queen
    if ($piece['name'] == 'pawn' && ($new_cor['y'] == 0 || $new_cor['y'] == 7)) {
		place_piece($board, $new_cor, 'queen', $piece['team']);
	}

	if ($piece['name'] == 'king') {
		$board[$piece['team'].'_king'] = $new_cor;
	}

	if ($update_movements) {
		update_movements($board, $changed_squares);
	}

	return true;
}

function valid_move(array $board, string $team, $old_cor, $new_cor, array $king_cor = null) {
	/**
	 * Validates if a move can be made without the king being in check.
	 * Used for filtering impossible moves.
	 * 
	 * @param array the board array
	 * @param string the team of the piece
	 * @param array the location where the piece came from
	 * @param array the location where the piece needs to go
	 * @param array the location of the king
	 * 
	 * @return bool
	 */

	move_piece($board, $old_cor, $new_cor, false);

	// find king location if needed
	if ($king_cor == null || $king_cor == '') {
		$king_cor = $board[$team.'_king'];
	} else if ($old_cor == $king_cor) {
		$king_cor = $new_cor;
	}

	return !is_square_attacked($board, $team, $king_cor);
}

function is_square_attacked(array $board, string $team, array $square_cor) {
	/**
	 * Checks if a square is attacked in any possible way by the other team
	 * Used mostly to check if the king is under attack
	 * 
	 * @param array the board array
	 * @param string the team of the piece
	 * @param array the location piece is
	 * 
	 * @return bool 
	 */

	$enemy_team = opposite_team($team);
	$directions_normal = [
		['x' =>  1, 'y' =>  1, 'direction' => 'diagonal'],
		['x' => -1, 'y' => -1, 'direction' => 'diagonal'],
		['x' =>  1, 'y' => -1, 'direction' => 'diagonal'],
		['x' => -1, 'y' =>  1, 'direction' => 'diagonal'],
		['x' =>  0, 'y' =>  1, 'direction' => 'up-down'],
		['x' =>  0, 'y' => -1, 'direction' => 'up-down'],
		['x' =>  1, 'y' =>  0, 'direction' => 'up-down'],
		['x' => -1, 'y' =>  0, 'direction' => 'up-down'],
	];
	$directions_horse = [
		['x' =>  2, 'y' =>  1],
		['x' =>  1, 'y' =>  2],
		['x' => -2, 'y' =>  1],
		['x' =>  1, 'y' => -2],
		['x' => -1, 'y' => -2],
		['x' => -2, 'y' => -1],
		['x' => -1, 'y' =>  2],
		['x' =>  2, 'y' => -1],
	];

	// loop for diagonal, horizontal and vertical
	foreach ($directions_normal as $direction) {
		$cor = $square_cor;
		for ($squares_moved = 0; $squares_moved < 8; $squares_moved++) {

			$cor['x'] += $direction['x'];
			$cor['y'] += $direction['y'];
			if (!valid_cor($cor)) {
				break;
			}

			$piece = get_piece($board, $cor);
			if ($piece['name'] == '') {
				continue;
			} else if ($piece['team'] == $enemy_team) {

				if (
					($piece['name'] == "bishop"	&& $direction['direction']	== 'diagonal') || 
					($piece['name'] == "tower"	&& $direction['direction']	== 'up-down' ) ||
					($piece['name'] == "king"	&& $squares_moved 			== 0		 ) ||
					($piece['name'] == "queen"											 ) ||

					(($piece['name'] == "pawn" 	 && $direction['direction']	== 'diagonal' && $squares_moved == 0) &&
					(($piece['team'] == 'bottom' && $direction['y'] > 0) ||	($piece['team'] == 'top' 	&& $direction['y'] < 0)))
				) 
				{
					return true;
				}
				break;

			}
			break;
		}
	}

	// loop for horses
	foreach ($directions_horse as $direction) {
		$horse_cor = $square_cor;
		$horse_cor['x'] += $direction['x'];
		$horse_cor['y'] += $direction['y'];
		if (!valid_cor($horse_cor)) {
			continue;
		}
		$piece = get_piece($board, $horse_cor);
		if ($piece['name'] == "horse" && $piece['team'] == $enemy_team) {
			return true;
		}
	}

	return false;
}

function related_pieces(array $board, $square_cor)
{
	/**
	 * Gets all pieces that are related to the given coordinates.
	 * Uses almost the same technique as the is_square_attacked function. 
	 * 
	 * @param array the board array
	 * @param array the coordinates it gets the squares from
	 * 
	 * @return array a array with coordinates
	 */
	$squares = [];
	$directions_normal = [
		['x' =>  1, 'y' =>  1, 'direction' => 'diagonal'],
		['x' => -1, 'y' => -1, 'direction' => 'diagonal'],
		['x' =>  1, 'y' => -1, 'direction' => 'diagonal'],
		['x' => -1, 'y' =>  1, 'direction' => 'diagonal'],
		['x' =>  0, 'y' =>  1, 'direction' => 'up-down'],
		['x' =>  0, 'y' => -1, 'direction' => 'up-down'],
		['x' =>  1, 'y' =>  0, 'direction' => 'up-down'],
		['x' => -1, 'y' =>  0, 'direction' => 'up-down'],
	];
	$directions_horse = [
		['x' =>  2, 'y' =>  1],
		['x' =>  1, 'y' =>  2],
		['x' => -2, 'y' =>  1],
		['x' =>  1, 'y' => -2],
		['x' => -1, 'y' => -2],
		['x' => -2, 'y' => -1],
		['x' => -1, 'y' =>  2],
		['x' =>  2, 'y' => -1],
	];

	// loop for diagonal, horizontal and vertical
	foreach ($directions_normal as $direction) {
		$cor = $square_cor;
		for ($squares_moved = 0; $squares_moved < 8; $squares_moved++) {

			$cor['x'] += $direction['x'];
			$cor['y'] += $direction['y'];
			if (!valid_cor($cor)) {
				break;
			}

			$piece = get_piece($board, $cor);
			if ($piece['name'] == '' || $piece['name'] == 'horse') {
				continue;
			}
			
			if (($piece['name'] == "bishop"	&& $direction['direction'] 	== 'diagonal') || 
				($piece['name'] == "tower" 	&& $direction['direction'] 	== 'up-down' ) ||
				($piece['name'] == "king" 	&& $squares_moved 			== 0) ||
				($piece['name'] == "pawn" 	&& $squares_moved 			== 0) ||
				($piece['name'] == "queen") ||

				($piece['name'] == "pawn" 	&& $squares_moved == 1 && !$piece['has_moved'] && $direction['direction'] == 'up-down')) 
			{
				$squares[] = $cor;
			}
			break;
		}
	}

	// loop for horses
	foreach ($directions_horse as $direction) {
		$horse_cor = $square_cor;
		$horse_cor['x'] += $direction['x'];
		$horse_cor['y'] += $direction['y'];
		if (!valid_cor($horse_cor)) {
			continue;
		}

		$piece = get_piece($board, $horse_cor);
		if ($piece['name'] == "horse") {
			$squares[] = $horse_cor;
		}
	}

	return $squares;
}

function update_movements(array &$board, array $changed_squares = [])
{
	/**
	 * Calculates the movements for the pieces.
     * The $changed_squares schould contain a list of all squares that have changed value.
     * Based on that list the function will update the pieces.
	 * 
	 * @param array the board array
	 * @param array a array of the old locations of the pieces before they moved,
	 * if the array empty he will search for pieces with no movements
     * @param bool recalculate all pieces movements
	 * 
	 * @return void
	*/
	$update_pieces = [];
    $teams         = ['top', 'bottom'];

	// reset all pieces if a king is under attack or is no longer under attack
	if (is_square_attacked($board, 'top', $board['top_king']) ||
		is_square_attacked($board, 'bottom', $board['bottom_king']) ||
		$board['in_check']) {

        $board['in_check'] = !$board['in_check'];
		foreach ($board['squares'] as $cor_str => $piece) {
			$update_pieces[$cor_str] = create_cor($cor_str);
		}

	} else if (sizeof($changed_squares) == 0) {
        // select pieces with no movements key set + their related pieces

		foreach ($board['squares'] as $cor_str => $piece) {
			if ($piece['name'] != '' && !isset($piece['movements'])) {
				$cor_array = create_cor($cor_str);
				$related_pieces = related_pieces($board, $cor_array);
				foreach ($related_pieces as $related_cor) {
					$update_pieces[cor_string($related_cor)] = $related_cor;
				}
				$update_pieces[$cor_str] = $cor_array;
			}
		}

	} else {
        // select the pieces related to the changed squares

		foreach ($changed_squares as $cor) {
			$cor = create_cor($cor);
			$piece = get_piece($board, $cor);

			$related_pieces = related_pieces($board, $cor);
			foreach ($related_pieces as $related_cor) {
				$update_pieces[cor_string($related_cor)] = $related_cor;
			}
			
			$update_pieces[cor_string($cor)] = $cor;
		}

	}

    // always select both team kings to prevent unforseen bugs.
    foreach ($teams as $team) {
        $king = $board[$team.'_king'];
        $update_pieces[cor_string($king)] = $king;
    }

    // update the selected pieces
	foreach ($update_pieces as $cor_str => $cor) {
		$movements = get_movements($board, $cor);
		$piece = get_piece($board, $cor);
		
		foreach ($movements['movements'] as $index => $movement_cor) {
			if (!valid_move($board, $piece['team'], $cor, $movement_cor, $board[$piece['team'].'_king'])) {
				unset($movements['movements'][$index]);
			}
		}

		$board['squares'][$cor_str]['attack_squares'] = $movements['attack_squares'];
		$board['squares'][$cor_str]['movements'] = $movements['movements'];
	}
}

function create_board() {
	/**
	 * Create a new board with all pieces and movements set default.
	 * 
	 * @return array a new board array
	*/
	$debug = false;

	$board = [
		'squares' => [],
		'in_check'  => false,
	];
	$teams = [
		'top' => ['pawn' => '1', 'other' => '0'],
		'bottom' => ['pawn' => '6', 'other' => '7'],
	];
	$pieces_placement = [
		'tower' => [0,7],
		'horse' => [1,6],
		'bishop' => [2,5],
		'king' => 3,
		'queen' => 4,
	];

	for ($i = 0; $i < 8; $i++) {
		for ($j = 0; $j < 8; $j++) {
			$cor_str = "$i-$j";
			place_piece($board, $cor_str, '', '', true);
		}
	}

	if ($debug) {

        // custom start senario for debugging purposes

	} else {
		foreach ($teams as $team => $y_array) {
			// pawns
			for ($i = 0; $i < 8; $i++) {
				place_piece($board, "$i-". $y_array['pawn'], 'pawn', $team, false);
			}

			// other pieces
			foreach ($pieces_placement as $name => $cor_x) {
				if (is_array($cor_x)) {
					place_piece($board, $cor_x[0].'-'.$y_array['other'], $name, $team, false);
					place_piece($board, $cor_x[1].'-'.$y_array['other'], $name, $team, false);
				} else {
					place_piece($board, $cor_x.'-'.$y_array['other'], $name, $team, false);
				}
			}
		}
	}

	foreach ($board['squares'] as $cor_str => $piece) {
		if ($piece['name'] == '') {
			continue;
		}

		$cor        = create_cor($cor_str);
		$movements  = get_movements($board, $cor);

		$board['squares'][$cor_str]['movements']      = $movements['movements'];
		$board['squares'][$cor_str]['attack_squares'] = $movements['attack_squares'];
	}
	return $board;
}

function check_castling(array $board, array $from, array $to)
{
    /**
	 * Checks if a move is a castling move.
     * Is used for checking if a extra castling move is nessesary
	 * 
     * @param array the board array
     * @param array coordinate array
     * @param array coordinate array
     * 
     * returns the old and the new location of the tower involved, uses from-to format.
     * returns false is there is no castling happening
	 * @return array/false
	*/
    $piece = get_piece($board, $from);
	if ($piece['name'] != 'king' || abs($from['x'] - $to['x']) != 2) {
        return false;
    }

    return [
        'from' => create_cor($from['x'] > $to['x'] ? '0-'.$from['y'] : '7-'.$from['y']),
        'to'   => create_cor($from['x'] > $to['x'] ? ($from['x'] - 1).'-'.$from['y'] : ($from['x'] + 1).'-'.$from['y']),
    ];
}

function generate_piece_html(string $name, string $team)
{
    /**
	 * Generates the html for a piece.
     * Is used for regenerating the html for the frond-end when loading the page or for the undo animations.
	 * 
     * @param string the name of the piece: pawn | horse | bishop | tower | queen | king
     * @param string the team of the piece
     * 
	 * @return string
	*/
    $icons = [
        'tower' 	=> '<i class="fa-solid fa-chess-rook-piece"></i>',
        'horse'	 	=> '<i class="fa-solid fa-chess-knight"></i>',
        'bishop' 	=> '<i class="fa-solid fa-chess-bishop"></i>',
        'queen' 	=> '<i class="fa-solid fa-chess-queen"></i>',
        'king' 		=> '<i class="fa-solid fa-chess-king"></i>',
        'pawn' 		=> '<i class="fa-solid fa-chess-pawn"></i>',
    ];

    return $name == '' ? '' : '<div data-piece="'.$name.'" class="'.$team.'">'.$icons[$name].'</div>';
}
?>