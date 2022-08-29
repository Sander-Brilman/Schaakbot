<?php
function board_score(array $board, string $team_turn, string $get_score_for = null, bool $full_array = false) {
	/**
	 * Calculate the score for both teams Based upon the current board.
	 * The score is calculated by these points:
	 * -> if the king is under attack
	 * -> checkmate/stalemate
	 * -> pieces that will get hit by a piece with a lower value
	 * -> total value of the pieces
	 * 
	 * the return value will be ($team_1_score - $team_2_score) unless $full_array is true,
     * In that case the full score of both teams will be returned
	 * 
	 * @param array the board array
	 * @param string the team that can move
	 * @param string the team that the score will be calculated for, if null it will choose $team_turn
	 * @param bool give the full score array
	 * 
	 * @return int/array
	*/
	$get_score_for = $get_score_for == null ? $team_turn : $get_score_for;
	$score = [
		'top' 		=> 0,
		'bottom' 	=> 0,
	];
	$movements_count = [
		'top' 		=> 0,
		'bottom' 	=> 0,
	];
    $kings_under_attack = [
        'top'       => false,
        'bottom'    => false,
    ];

	$king_under_attack 		= -50;
	$check_mate_value 		= -999;
	$attacked_by_piece		= -25;
	$opposite_team 			= opposite_team($team_turn);
	$end_game 				= false;

	// 
	// check if game is over
	// 
	foreach ($board['squares'] as $cor => $piece) {
		if ($piece['name'] != '') {
			$movements_count[$piece['team']] += sizeof($piece['movements']);
			$score[$piece['team']] += get_value($piece['name']);
		}
	}

	foreach ($movements_count as $team => $number_of_movements) {
		$king_attacked = is_square_attacked($board, $team, $board[$team.'_king']);
        $kings_under_attack[$team] = $king_attacked;
		if ($king_attacked) {
			$score[$team] += $king_under_attack;
		}

		if ($number_of_movements == 0) {

			if ($king_attacked) {
				$score[$team] += $check_mate_value;
			} else {
				$score = [
					'top' =>    0,
					'bottom' => 0,
				];
			}

			$end_game = true;
			break;
		}
	}

	if ($end_game) {
		$opposite_score_team = opposite_team($get_score_for);
		return $full_array ? $score : $score[$get_score_for] - $score[$opposite_score_team];
	}

	// 
	// pieces under attack by a lower value piece 
	// 
	$highest_loss = 0;
	$pieces_array = get_attackers_defenders($board);
	foreach ($pieces_array[$opposite_team] as $cor_str => $piece) {

		if (sizeof($piece['attackers']) == 0) {
			continue;
		}

		// count the half value as a loss
		if (sizeof($piece['defenders']) == 0) {
			if ($piece['value'] > $highest_loss) {
				$highest_loss = $piece['value'] / 2;
			}
			continue;            
		}

		// count the piece value - attacker value as a loss
		$lowest_attacker = null;
		foreach ($piece['attackers'] as $cor) {
			$attacker_cor_str = cor_string($cor);
			$attacker_value = get_value(get_piece($board, $attacker_cor_str)['name']);
			if ($lowest_attacker === null || $attacker_value < $lowest_attacker) {
				$lowest_attacker = $attacker_value;
			}
		}
		if ($piece['value'] > $lowest_attacker && $piece['value'] - $lowest_attacker > $highest_loss) {
			$highest_loss = $piece['value'] - $lowest_attacker;
			continue;
		}

	}
	$score[$opposite_team] -= $highest_loss;

	$danger_cases = [];
	foreach ($pieces_array[$team_turn] as $cor_str => $piece) {

		if (sizeof($piece['attackers']) == 0) {
			continue;
		}

		if (sizeof($piece['defenders']) == 0) {
			$danger_cases[$cor_str] = get_value($piece['name']);
			continue;
		}

        if (sizeof($piece['defenders']) < sizeof($piece['attackers'])) {
            $danger_cases[$cor_str] = get_value($piece['name']);   
            $score[$team_turn] += $attacked_by_piece;
            continue;
        }
 
		foreach ($piece['attackers'] as $cor) {
			$attacker_cor_str = cor_string($cor);
			$attacker_value = get_value(get_piece($board, $attacker_cor_str)['name']);
	
			if ($attacker_value < $piece['value']) {
				$danger_cases[$cor_str] = get_value($piece['name']);   
				break;
			}
		}
	}

    if ($kings_under_attack[$team_turn]) {
        $score[$team_turn] -= array_shift($danger_cases);
    } else {
        if (sizeof($danger_cases) > 1) {
            $score[$team_turn] += ((sizeof($danger_cases) - 1) * $attacked_by_piece);
        }
    }

	$opposite_score_team = opposite_team($get_score_for);
	return $full_array ? $score : $score[$get_score_for] - $score[$opposite_score_team];
}
?>