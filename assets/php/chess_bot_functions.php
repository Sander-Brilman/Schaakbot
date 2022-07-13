<?php
include_once('functions.php');
include_once('debug.php');
function get_attackers_defenders(array $board) {
	/**
	 * Create a array the pieces and their already existing information.
     * All pieces also have a bonus array with locations of their attackers & defenders.
	 * Used for fast filtering of bad moves
     * 
	 * @param array board array
	 * 
	 * @return array the array with pieces
	*/
	$teams = [
		'bottom' => [],
		'top' =>    [],
	];

	foreach ($board['squares'] as $cor_str => $piece) {
		if ($piece['name'] == '') {
			continue;
		}
		$teams[$piece['team']][$cor_str] 				= $piece;
		$teams[$piece['team']][$cor_str]['value']		= get_value($piece['name']);
		$teams[$piece['team']][$cor_str]['attackers'] 	= [];
		$teams[$piece['team']][$cor_str]['defenders'] 	= [];
	}

	foreach ($teams as $team => $pieces_array) {
		foreach ($pieces_array as $cor_str => $piece) {

			$piece_cor = create_cor($cor_str);
			foreach ($piece['attack_squares'] as $cor) {

				$current_square = get_piece($board, $cor);
				if ($current_square['name'] == '') {
					continue;
				}

				$status = $current_square['team'] == $team ? 'defenders' : 'attackers';
				$teams[$current_square['team']][cor_string($cor)][$status][] = $piece_cor;
			}

		}
	}
	
	return $teams;
}


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

	$king_under_attack 		= -50;
	$check_mate_value 		= -999;
	$attacked_by_piece		= -20;
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
			$danger_cases[$cor_str] = true;
			continue;
		}

		foreach ($piece['attackers'] as $cor) {
			$attacker_cor_str = cor_string($cor);
			$attacker_value = get_value(get_piece($board, $attacker_cor_str)['name']);
			
			if ($attacker_value < $piece['value']) {
				$danger_cases[$cor_str] = true;
				break;
			}
		}
	}

	if (sizeof($danger_cases) > 1) {
		$score[$team_turn] += ((sizeof($danger_cases) - 1) * $attacked_by_piece);
	}

	$opposite_score_team = opposite_team($get_score_for);
	return $full_array ? $score : $score[$get_score_for] - $score[$opposite_score_team];
}

function calculate_move(array $board, string $team_turn, int $difficulty = 1000)
{
	/**
	 * The starting place for calculating a movement based upon the $difficulty.
	 * 
	 * @param array the board array
	 * @param string the team that can move
	 * @param int effects the quality of the move calculation (between 0 and 1000)
	 * 
	 * @return array movement array of the recomended move
	*/
	$limit          = 0;
	$choice         = mt_rand(0, 1000);
    $opposite_team  = opposite_team($team_turn);
    $message        = '';

	if ($choice <= $difficulty) {
		$movement = reqursive_calculation($board, $team_turn, 0, $limit);
        $message  = 'best_movement';
	} else {

		$choice = mt_rand(0, 1000);
		if ($choice <= $difficulty) {

			$movement = best_movements($board, $team_turn, 1)[0];
            $board    = $movement['board'];
			$movement = ['move' => $movement['move'], 'status' => ''];
            $message  = 'second_best_movement';

		} else {

            $message         = 'random_movement';
			$total_movements = [];
			foreach ($board['squares'] as $cor_str => $piece) {
				if ($piece['team'] == $team_turn && sizeof($piece['movements']) > 0) {
					$total_movements[$cor_str] = $piece['movements'];
				}
			}
	
			$from	= array_rand($total_movements);
			$to 	= array_rand($total_movements[$from]);

			$to 	= create_cor($total_movements[$from][$to]);;
            $from   = create_cor($from);

            move_piece($board, $from, $to);

			$movement = [
				'move' => [
					'from'	=> $from,
					'to'	=> $to,
				],
				'status' => '',
			];
		}

        $movement['status'] = is_square_attacked($board, $opposite_team, $board[$opposite_team.'_king']) ? 'checkmate' : 'stalemate';
        foreach ($board['squares'] as $cor_str => $piece) {
            if ($piece['team'] == $opposite_team && sizeof($piece['movements']) > 0) {
                $movement['status'] = 'active';
                break;
            }
        }
	}

    $movement['message'] = $message;
	return $movement;
}

function reqursive_calculation(array $board, string $team_turn, int $current, int $limit)
{
	/**
	 * Calculate the best movement for a certain situation using the score from the board_score function
	 * Could be used to calculate result score of movements reqursively 
	 * 
	 * @param array the board array
	 * @param string the team that can move
	 * @param int the current counter of reqursiveness
	 * @param int the limit the function can reqursive calculate moves
	 * 
	 * @return array array with both the advised move and the score of that move
	*/
	$opposite_team 		= opposite_team($team_turn);
	$best_moves_length	= 5;
	$best_moves 		= best_movements($board, $team_turn, $best_moves_length);
	$return_move		= null;
	
	// 
	// calulate enemy reaction to the moves
	//
	foreach ($best_moves as $move_data) {

		$enemy_move	= best_movements($move_data['board'], $opposite_team, 2);

		if ($enemy_move == 'checkmate') {
			$return_move = ['score' => 999999, 'move' => $move_data['move'], 'status' => 'checkmate'];
			break;
		}
		
		$enemy_move = $enemy_move[0];
		$new_board 	= $enemy_move['board'];
		$move 		= $enemy_move['move'];

		move_piece($new_board, $move['from'], $move['to']);
		$score		= board_score($board, $team_turn, $team_turn);

		/**
		 * code for calculating the end result score of the movements reqursively, currently not in use.
		 * 
		 *	if ($current < $limit) {
		 *		$final_move = reqursive_calculation($new_board, $team_turn, $current + 1, $limit);
		 *	} else {
		 *		$final_move = best_movements($new_board, $team_turn, 1)[0];
		 *	}
		 */

		if ($return_move == null || $return_move['score'] < $score) {
			$return_move = ['score' => $score, 'move' => $move_data['move'], 'status' => 'active'];
		}
	}

	return $return_move;
}

function worst_movements(array $board, string $team)
{
	/**
	 * Calculate the WORST movements for the given team. 
	 * Assuming the given team is the one that can make a move.
	 * Is used to filter out the worst movements in advance to save processing time.
	 * 
	 * @param array the board array
	 * @param string the team to calculate for
	 * 
	 * @return array array with with the worst movements 
	*/
	$pieces_array 		= get_attackers_defenders($board);
	$worst_movements 	= [];

	foreach ($pieces_array[$team] as $cor_str => $piece) {
		foreach ($piece['movements'] as $index => $cor) {

			$movement_cor_str = cor_string($cor);
			$square_name = $board['squares'][$movement_cor_str]['name'];
			$attackers = [];
			$defenders = [];

			// get all the attackers and defernders for the new location
			foreach ($pieces_array as $piece_team => $team_array) {
				foreach ($team_array as $team_cor => $team_piece) {

					if (in_array($cor, $team_piece['attack_squares']) && $cor_str != $team_cor) {
						if ($team == $piece_team) {
							$defenders[$team_cor] = $team_piece['value'];
						} else {
							$attackers[$team_cor] = $team_piece['value'];
						}
					}

				}
			}

			if (sizeof($attackers) == 0) {
				continue;
			}

			if ($square_name == '') {

				$attacked_by_lower = false;
				foreach ($attackers as $value) {
					if ($value < $piece['value']) {
						$attacked_by_lower = true;
						break;
					}
				}

				// if all attackers are higher value and piece is defended
				if (!$attacked_by_lower && sizeof($defenders) > 0) {
					continue;
				}

			} else if (get_value($square_name) >= $piece['value']) {
				continue;
			} else if (sizeof($defenders) >= sizeof($attackers)) {
				continue;
			}

			$worst_movements[$cor_str][$index] = $cor;
		}
	}
	return $worst_movements;
}

function best_movements(array $board, string $team, int $return_array_length)
{
	/**
	 * Calculate the best movements for a team.
	 * Returns an array with the movements sorted by their score
	 * 
	 * @param array the board array
	 * @param string the team to calculate for
	 * 
	 * @param int the length of the return array. 
	 * Every item in the return array contains a board array. Keep this number low to save memory.
	 * 
	 * @return array the array with the moves sorted by score.
	*/
	$calculated_moves	= [];
	$total_movements	= [];
	$return_array       = [];
	$opposite_team		= opposite_team($team);
	$can_move           = false;

	if (is_square_attacked($board, $team, $board[$team.'_king'])) {
		$worst_movements = [];
		$in_check        = true;
	} else {
		$worst_movements = worst_movements($board, $team);
		$in_check        = false;
	}

	// 
	// filter the worst movements 
	// 
	foreach ($board['squares'] as $cor_str => $piece) {
		if ($piece['name'] == '' || $piece['team'] != $team) {
			continue;
		}
		if (isset($worst_movements[$cor_str])) {
			foreach ($worst_movements[$cor_str] as $index => $cor) {
				unset($piece['movements'][$index]);
			}
		}
		$total_movements[$cor_str] = $piece['movements'];
		if (sizeof($piece['movements']) > 0) {
			$can_move = true;
		}
	}

	if (!$can_move) {
		return $in_check ? 'checkmate' : 'stalemate';
	}

	// 
	// order remaining movements by score
	//
	foreach ($total_movements as $cor_str => $movements) {
		foreach ($movements as $cor) {

			$board_copy = $board;
			move_piece($board_copy, $cor_str, $cor);
			$score = board_score($board_copy, $opposite_team, $team);

			$calculated_moves[$score][] = [
				'score' => $score,
				'board' => $board_copy,
				'move'  => ['from' => create_cor($cor_str), 'to' => $cor],
			];
		}
	}


	//
	// return the best of the array in requested length
	// 
	krsort($calculated_moves);
	foreach ($calculated_moves as $score => $array) {
		shuffle($array);
		foreach ($array as $move_data) {
			$return_array[] = $move_data;

			$return_array_length--;
			if ($return_array_length === 0) {
				break;
			}
		}

        if ($return_array_length === 0) {
            break;
        }
	}

	return $return_array;
}
?>