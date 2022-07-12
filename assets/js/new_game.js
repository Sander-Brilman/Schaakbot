const levelInput = document.getElementById('level');
const l = console.log;
function updateLevel() {
	let displayText = '0';
	let number      = Math.round(levelInput.value / 200) * 200;
	switch (number) {
		case 200:
			displayText = '1';
			break;

		case 400:
			displayText = '2';
			break;

		case 600:
			displayText = '3';
			break;

		case 800:
			displayText = '4';
			break;

		case 1000:
			displayText = '5';
			break;
	}
	document.querySelector('label > span').innerHTML = ` - ${displayText}`;
}

levelInput.onchange = function() {
	updateLevel();
}

setInterval(() => {
	updateLevel();
	updateColors();
}, 100);

document.querySelectorAll('.radio-container').forEach(element => {
	element.onclick = function() {
		if (element.querySelector('input').getAttribute('checked') == null) {
			document.querySelectorAll(".radio-container > input").forEach(input => {
				input.removeAttribute('checked');
			});
			toggleColorPicker(false);
			element.querySelector('input').setAttribute('checked', true);
		}
	}
});

document.querySelectorAll('.color-row').forEach(row => {
	row.onclick = function() {
		row.querySelector('input').click();
	}
})

let toggleIcon = document.getElementById('toggle');
let active = false;
toggleIcon.onclick = function() {
	toggleColorPicker();
}

function toggleColorPicker(state = null) {
	if (state == null) {
		active = active ? false : true;
	} else {
		active = state;
	}
	if (active) {
		toggleIcon.style.transform = 'rotate(0deg)';
		document.querySelector('.input-row').style.height = '210px';
		setTimeout(() => {
			document.querySelector('.color-picker').style.visibility = 'visible';
		}, 400)
	} else {
		toggleIcon.style.transform = 'rotate(90deg)';
		document.querySelector('.input-row').style.height = '90px';
		setTimeout(() => {
			document.querySelector('.color-picker').style.visibility = 'hidden';
		}, 75)
	}
}

function updateColors() {
	let shadowInput = document.getElementById('custom_shadow');
	let colorInput = document.getElementById('custom_color');
	let customColorIcon = document.querySelector('.radio-container:last-of-type > i');

	let color = shadowInput.value;
	customColorIcon.style.textShadow = `${color} 0px 0px 4px`;

	color = colorInput.value;
	customColorIcon.style.color = color;
}