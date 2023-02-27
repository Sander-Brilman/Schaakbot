<?php
function incomplete_form()
{
	?>
	<div class="error">
		<h2>Error</h2>
		<p>Oeps. Het ziet er naar uit dat er iets fout is gegaan met het aanmaken van het spel. Probeer het nog een keer..</p>
		<small>Komt dit vaker voor? <a href="https://sanderbrilman.nl/pages/contact.php">Laat het mij weten</a></small>
	</div>
	<?php
}

function random_color()
{
	return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
}
$shadow = random_color();
$color  = random_color();

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
		<h1>Nieuw spel</h1>
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
					<label for="custom_shadow">Schaduw kleur</label>
					<input type="color" name="custom_shadow" id="custom_shadow" value="<?= $shadow ?>">
				</div>
			</div>
		</div>

		<div class="input-row">
			<label for="level">Moeilijkheid<span> - 5</span></label>
			<input type="range" name="level" min="0" max="1000" value="1000" id="level">
		</div>

		<button name="new_game" value="<?= create_form_id('new_game') ?>" type="submit">Spelen!</button>
	</form>
</div>