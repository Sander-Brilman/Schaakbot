<?php
if (isset($_POST['new_game'])) {

	$required = [
		'color',
		'level',
		'custom_color',
		'custom_shadow',
	];
	foreach ($required as $name) {
		if (!isset($_POST[$name])) {
			header('Location: nieuw-spel?error=incomplete');
			exit;
		}
	} 

	if ($_POST['color'] == 'custom') {
		$color = $_POST['custom_color'];
		$shadow = $_POST['custom_shadow'];
		$begins = false;
	} else {
		$color = $_POST['color'];
		$shadow = $_POST['color'] == 'black' ? '#ff0000' : '#00c5ff';
		$begins = $color == 'white' ? true : false;
	}
	$computer_users = [
		['icon' => '<i class="fa-duotone fa-user-vneck-hair-long"></i>',	'name' => '*'],
		['icon' => '<i class="fa-duotone fa-user-nurse-hair-long"></i>',    'name' => '*'],
		['icon' => '<i class="fa-duotone fa-user-helmet-safety"></i>', 		'name' => '*'],
		['icon' => '<i class="fa-duotone fa-user-shakespeare"></i>', 		'name' => 'Shakespeare'],
		['icon' => '<i class="fa-duotone fa-user-vneck-hair"></i>',      	'name' => '*'],
		['icon' => '<i class="fa-duotone fa-user-astronaut"></i>',          'name' => '*'],
		['icon' => '<i class="fa-duotone fa-user-graduate"></i>',           'name' => 'Professor W.B'],
		['icon' => '<i class="fa-duotone fa-user-injured"></i>', 			'name' => '*'],
		['icon' => '<i class="fa-duotone fa-user-doctor"></i>',            	'name' => 'Docter *'],
		['icon' => '<i class="fa-duotone fa-user-cowboy"></i>',            	'name' => '*'],
		['icon' => '<i class="fa-duotone fa-user-police"></i>', 			'name' => '*'],
		['icon' => '<i class="fa-duotone fa-user-secret"></i>', 			'name' => 'Incognitor'],
		['icon' => '<i class="fa-duotone fa-user-alien"></i>',            	'name' => '*'],
		['icon' => '<i class="fa-duotone fa-user-crown"></i>',          	'name' => 'Koning *'],
		['icon' => '<i class="fa-duotone fa-user-ninja"></i>', 				'name' => 'Ninja *'],
		['icon' => '<i class="fa-duotone fa-user-pilot"></i>', 				'name' => '*'],
		['icon' => '<i class="fa-duotone fa-user-robot"></i>', 				'name' => 'Wall-E'],
		['icon' => '<i class="fa-duotone fa-user-visor"></i>',              'name' => '*'],
		['icon' => '<i class="fa-duotone fa-user-vneck"></i>',    			'name' => '*'],
		['icon' => '<i class="fa-duotone fa-user-chef"></i>',              	'name' => 'Chef *'],
		['icon' => '<i class="fa-duotone fa-user-tie"></i>', 				'name' => '*'],
		['icon' => '<i class="fa-duotone fa-angel"></i>',					'name' => '*'],
		['icon' => '<i class="fa-solid fa-user"></i>',                     	'name' => '*'],

		// ['icon' => '',													'name' => '*'],

		['icon' => '<i class="fa-duotone fa-alien-8bit"></i>', 				'name' => 'Space-Invader'],
		['icon' => '<i class="fa-duotone fa-axe-battle"></i>',				'name' => 'Viking *'],
		['icon' => '<i class="fa-duotone fa-mushroom"></i>',				'name' => '*'],
		['icon' => '<i class="fa-duotone fa-baguette"></i>', 				'name' => 'Baguette'],
		['icon' => '<i class="fa-duotone fa-skull"></i>', 					'name' => '*'],
		['icon' => '<i class="fa-duotone fa-otter"></i>', 					'name' => '* de Otter'],
		['icon' => '<i class="fa-duotone fa-cat"></i>', 					'name' => 'Minoes'],
	];

	// random bot with random name
	$names				= explode("\n", file_get_contents('assets/voornamen.txt'));
	$random_name		= $names[array_rand($names, 1)];
	$random_bot			= $computer_users[array_rand($computer_users, 1)];
	$random_bot['name'] = str_replace('*',  $random_name, $random_bot['name']);
	$random_bot['name'] = str_replace("\r", '', $random_bot['name']);

	$_SESSION['game_data'] = [
		'level'     	=> (int)$_POST['level'],
		'bot_icon'  	=> $random_bot['icon'],
		'bot_name'  	=> $random_bot['name'],

		'color'     	=> $color,
		'shadow'    	=> $shadow,
		'begins'    	=> $begins,
		'board'     	=> create_board(),

		'status'    	=> 'active',
		'winner'    	=> 'undefined',
		'move_history'	=> [],
	];

	header('location: spelen');
	exit; 
}

function incomplete_form()
{
	?>
	<div class="error">
		<h2>Er is iets mis gegaan..</h2>
		<p>Oepsie.. Het lijkt erop dat er iets mis is gegaan bij het aanmaken van het spel. Probeer het nog een keer.</p>
		<small>Komt dit vaker voor? <a href="https://sanderbrilman.nl/pages/contact.php">Laat het mij weten.</a></small>
	</div>
	<?php
}

function random_color()
{
	return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
}
$shadow = random_color();
$color = random_color();

if (isset($_GET['error'])) {
	switch ($_GET['error']) {
		case 'incomplete':
			incomplete_form();
			break;
	}
}
?>
<div id="menu">
	<header>
		<h1>Nieuw Spel</h1>
	</header>
	<form method="post">
		<div class="input-row">
			<label for="color">Kleur</label>

			<div class="radio-buttons">

				<div class="radio-container">
					<input checked type="radio" name="color" value="white" required >
					<i style="--color: white; --shadow: #00c5ff 0px 0px 4px;" class="fa-solid fa-chess"></i>
					Wit
				</div>
				<hr>

				<div class="radio-container">
					<input type="radio" name="color" value="black" required>
					<i style="--color: black; --shadow: #ff0000 0px 0px 4px;" class="fa-solid fa-chess"></i>
					Zwart
				</div>
				<hr>

				<div class="radio-container">
					<input type="radio" name="color" value="custom" required>
					<i style="--color: <?= $color ?>; --shadow: <?= $shadow ?> 0px 0px 4px;" class="fa-solid fa-chess"></i>
					<span>Kiezen<i id="toggle" class="fa-thin fa-circle-chevron-down"></i></span>
				</div>

			</div>

			<div class="color-picker">
				<div class="color-row">
					<label for="custom_color">Kleur</label>
					<input type="color" name="custom_color" id="custom_color" value="<?= $color ?>">
				</div>

				<div class="color-row">
					<label for="custom_shadow">Schadow kleur</label>
					<input type="color" name="custom_shadow" id="custom_shadow" value="<?= $shadow ?>">
				</div>
			</div>
		</div>

		<div class="input-row">
			<label for="level">Niveau<span> - 5</span></label>
			<input type="range" name="level" min="0" max="1000" value="1000" id="level">
		</div>

		<input type="checkbox" checked hidden name="new_game" value="1">
		<button type="submit">Speel</button>
	</form>
</div>