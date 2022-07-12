function checkMate(winningColor) {
    const translation = {
        white: "Wit",
        black: "Zwart",
    }
    showEndWindow(translation[winningColor] + " heeft gewonnen!");
}

function tie() {
    showEndWindow("Gelijkspel!");
}

function showEndWindow(Title) {
    document.querySelectorAll("td").forEach(td => {
        td.setAttribute("class", "");
        td.onclick = function() { };
    });

    let endWindow = document.createElement("div");
    endWindow.setAttribute("id", "gameOver");
    endWindow.innerHTML = `
    <h1>${Title}</h1>
    <h3>Het spel heeft ${numberOfMoves} zetten geduurt.</h3>
    <div id="menu">
        <a href="index.html">Speel nog een keer.</a>
        <button>
            Speel nog een keer met dezelfde instellingen.
        </button>
    </div>  
    `;
    document.body.appendChild(endWindow);
}

