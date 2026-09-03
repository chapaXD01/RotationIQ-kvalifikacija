const players = document.querySelectorAll('.player');
const court = document.getElementById('court');

players.forEach(player => {
    let offsetX = 0;
    let offsetY = 0;

    player.addEventListener('mousedown', e => {
        offsetX = e.offsetX;
        offsetY = e.offsetY;

        function move(e) {
            const rect = court.getBoundingClientRect();

            let x = e.clientX - rect.left - offsetX;
            let y = e.clientY - rect.top - offsetY;

            
            x = Math.max(0, Math.min(x, court.clientWidth - player.clientWidth));
            y = Math.max(0, Math.min(y, court.clientHeight - player.clientHeight));

            player.style.left = x + 'px';
            player.style.top = y + 'px';
        }

        document.addEventListener('mousemove', move);

        document.addEventListener('mouseup', () => {
            document.removeEventListener('mousemove', move);
        }, { once: true });
    });
});

function getPlayers() {
    const data = {};

    document.querySelectorAll('.player').forEach(p => {
        const pos = p.dataset.pos;

        data[pos] = {
            top: parseFloat(p.style.top),
            left: parseFloat(p.style.left),
            el: p
        };
    });

    return data;
}

// check rotation

function checkRotationWithVisuals() {
    const players = getPlayers();
    const svg = document.getElementById("lines");
    svg.innerHTML = "";

    let errors = [];

    
    if (players[1].top < players[2].top) {
        errors.push("Player 1 is in front of Player 2");

        drawHorizontalFaultLine(players[2], "front");
    }

    
    if (players[6].top < players[3].top) {
        errors.push("Player 6 is in front of Player 3");
        drawHorizontalFaultLine(players[3], "front");
    }

    
    if (players[5].top < players[4].top) {
        errors.push("Player 5 is in front of Player 4");
        drawHorizontalFaultLine(players[4], "front");
    }

   
    if (players[6].left > players[1].left) {
        errors.push("Player 6 is right of Player 1");
        drawVerticalFaultLine(players[1], "right");
    }

    if (players[5].left > players[6].left) {
        errors.push("Player 5 is right of Player 6");
        drawVerticalFaultLine(players[6], "right");
    }

    showErrors(errors);
}
// lines for positon error
function drawHorizontalFaultLine(player, side) {
    const svg = document.getElementById("lines");
    const y = side === "front"
        ? player.top - 5
        : player.top + 50;

    const line = document.createElementNS("http://www.w3.org/2000/svg", "line");

    line.setAttribute("x1", 0);
    line.setAttribute("x2", 500);
    line.setAttribute("y1", y);
    line.setAttribute("y2", y);
    line.setAttribute("stroke", "red");
    line.setAttribute("stroke-width", "4");

    svg.appendChild(line);
}
// lines / rotation error lines
function drawVerticalFaultLine(player, side) {
    const svg = document.getElementById("lines");
    const x = side === "right"
        ? player.left + 50
        : player.left - 5;

    const line = document.createElementNS("http://www.w3.org/2000/svg", "line");

    line.setAttribute("y1", 0);
    line.setAttribute("y2", 400);
    line.setAttribute("x1", x);
    line.setAttribute("x2", x);
    line.setAttribute("stroke", "red");
    line.setAttribute("stroke-width", "4");

    svg.appendChild(line);
}

function showErrors(errors) {
    if (errors.length === 0) {
        alert("in rotation");
    } else {
        alert("out of rotation:\n\n" + errors.join("\n"));
    }
}

function getCenter(player) {
    return {
        x: player.left + 22.5,
        y: player.top + 22.5
    };
}

// save rotation

function saveRotation() {

    const name = document.getElementById('rotation-name').value;
    const type = document.getElementById('rotationType').value;

    if (!name) {
        alert("Please enter rotation name");
        return;
    }

    const players = [];

    document.querySelectorAll('.player').forEach(player => {

        players.push({
            role: player.dataset.role,
            pos: player.dataset.pos,
            top: parseFloat(player.style.top),
            left: parseFloat(player.style.left)
        });

    });

    const token = document.querySelector('meta[name="csrf-token"]').content;

    const url = type === "attack"
        ? "/attack-rotations"
        : "/defence-rotations";

    fetch(url, {

        method: "POST",

        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": token
        },

        body: JSON.stringify({
            name: name,
            players: players
        })

    })
    .then(response => {

        if (!response.ok) {
            throw new Error("Server error");
        }

        return response.json();
    })
    .then(data => {

        alert("Rotation saved successfully");

        if (type === "attack") {
            window.location.href = "/attack";
        } else {
            window.location.href = "/defence";
        }

    })
    .catch(error => {

        console.error(error);

        alert("Failed to save rotation");
    });
}
// seit ir hardcoded jo šis ir priekš velejbola laukuma pozīcijām kad meģinaju citus veidus viss tika sačakarēts ar laukuma pozīcijām
const zoneCenters = {
    1: { top: 278, left: 395 },
    2: { top: 78,  left: 395 },
    3: { top: 78,  left: 228 },
    4: { top: 78,  left: 61  },
    5: { top: 278, left: 61  },
    6: { top: 278, left: 228 }
};
// volleyball rotation 
function rotateClockwise() {

    const rotationMap = {
        1: 6,
        6: 5,
        5: 4,
        4: 3,
        3: 2,
        2: 1
    };
    const players = document.querySelectorAll('.player');

    players.forEach(player => {

        const currentPos = parseInt(player.dataset.pos);
        const newPos = rotationMap[currentPos];

        player.dataset.pos = newPos;

        player.style.top = zoneCenters[newPos].top + "px";
        player.style.left = zoneCenters[newPos].left + "px";
    });

    handleLiberoSub();

    document.getElementById("lines").innerHTML = "";
}

// Libero swithc
function handleLiberoSub() {
    const players = document.querySelectorAll('.player');
    
    players.forEach(player => {
        const pos = parseInt(player.dataset.pos);
        const role = player.dataset.role;
        
        // parbauda vai role ir vienāds ar MB(midle blocker), un ja speletāja position ir vienāds ar 5 positon vai 6 vai 1, ja ta ir tad izmaina pre L(libero)
        if (role === 'MB' && (pos === 5 || pos === 6 || pos === 1)) {
            player.dataset.role = 'L';
            player.textContent = 'L';
        } 
 
        else if (role === 'L' && (pos === 4 || pos === 3 || pos === 2)) {
            player.dataset.role = 'MB';
            player.textContent = 'MB';
        }
    });
}

// update rotation
function updateRotation(id) {
    const name = document.getElementById('rotation-name').value;
    const type = document.getElementById('rotationType').value;

    const players = [];

    document.querySelectorAll('.player').forEach(player => {
        players.push({
            role: player.dataset.role,
            pos: player.dataset.pos,
            top: parseFloat(player.style.top),
            left: parseFloat(player.style.left)
        });
    });

    const url = type === "attack"
        ? `/attack/${id}`
        : `/defence/${id}`;

    fetch(url, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            name: name,
            players: players
        })
    })
    .then(res => res.json())
    .then(() => {
        alert('Rotation updated');

        if (type === "attack") {
            window.location.href = '/attack';
        } else {
            window.location.href = '/defence';
        }
    });
}


// animated players (kur speletāji iet uz selected location)


document.addEventListener('DOMContentLoaded', function() {
    // chek if we are in animation page
    const animateBtn = document.getElementById('animateBtn');
    if (!animateBtn) return;

    const court = document.getElementById('court');
    const players = document.querySelectorAll('#court .player');
    const resetBtn = document.getElementById('resetBtn');
    const clearDestinationsBtn = document.getElementById('clearDestinationsBtn');
    const rotateBtn = document.getElementById('rotateBtn');
    const selectedInfo = document.getElementById('selectedInfo');
    const destinationCount = document.getElementById('destinationCount');

    let selectedPlayer = null;
    let totalDestinations = 0;

    
    players.forEach((player, index) => {
        player.destination = null;
        player.originalPosition = {
            left: player.style.left,
            top: player.style.top
        };
        player.playerIndex = index;

        // player selection
        player.addEventListener('click', function(event) {
            event.stopPropagation();

            if (selectedPlayer) {
                selectedPlayer.classList.remove('selected');
            }

            selectedPlayer = player;
            player.classList.add('selected');

            const role = player.getAttribute('data-role') || 'Player ' + (index + 1);
            selectedInfo.textContent = role;
        });
    });

    // destination selection 
    court.addEventListener('click', function(event) {
        if (!selectedPlayer) {
            alert('Please select a player first!');
            return;
        }

        if (event.target.classList.contains('player')) {
            return;
        }

        const rect = court.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;

        selectedPlayer.destination = { x, y };

        // create or update marker
        if (!selectedPlayer.marker) {
            const marker = document.createElement('div');
            marker.classList.add('destination-marker');
            court.appendChild(marker);
            selectedPlayer.marker = marker;
        }

        selectedPlayer.marker.style.left = (x - 6) + 'px';
        selectedPlayer.marker.style.top = (y - 6) + 'px';

        // update destination count
        let count = 0;
        players.forEach(p => {
            if (p.destination) count++;
        });
        totalDestinations = count;
        destinationCount.textContent = count + ' / ' + players.length;
    });

    // animate all players to destinations
    animateBtn.addEventListener('click', function() {
        let hasDestinations = false;

        players.forEach(player => {
            if (player.destination) {
                hasDestinations = true;
                const boxWidth = player.offsetWidth;
                const boxHeight = player.offsetHeight;

                // center the player on the destination point
                const newLeft = player.destination.x - boxWidth / 2;
                const newTop = player.destination.y - boxHeight / 2;

                player.style.left = newLeft + 'px';
                player.style.top = newTop + 'px';
            }
        });

        if (!hasDestinations) {
            alert('Please set at least one destination!');
        }
    });

    // rotate positions
    rotateBtn.addEventListener('click', function() {
        const rotationMap = {
            1: 6,
            6: 5,
            5: 4,
            4: 3,
            3: 2,
            2: 1
        };

        const courCenters = {
            1: { top: 278, left: 395 },
            2: { top: 78,  left: 395 },
            3: { top: 78,  left: 228 },
            4: { top: 78,  left: 61  },
            5: { top: 278, left: 61  },
            6: { top: 278, left: 228 }
        };

        players.forEach(player => {
            const currentPos = parseInt(player.dataset.pos);
            const newPos = rotationMap[currentPos];

            player.dataset.pos = newPos;

            player.style.top = courCenters[newPos].top + "px";
            player.style.left = courCenters[newPos].left + "px";

            // update original position for reset
            player.originalPosition = {
                left: courCenters[newPos].left + "px",
                top: courCenters[newPos].top + "px"
            };
        });

        // delete destinations and markers after rotation
        players.forEach(player => {
            player.destination = null;
            if (player.marker) {
                player.marker.remove();
                player.marker = null;
            }
        });

        totalDestinations = 0;
        destinationCount.textContent = '0 / ' + players.length;
    });

    // reset all players to original positions
    resetBtn.addEventListener('click', function() {
        players.forEach(player => {
            player.style.left = player.originalPosition.left;
            player.style.top = player.originalPosition.top;
            player.destination = null;

            if (player.marker) {
                player.marker.remove();
                player.marker = null;
            }
        });

        if (selectedPlayer) {
            selectedPlayer.classList.remove('selected');
            selectedPlayer = null;
            selectedInfo.textContent = 'None selected';
        }

        totalDestinations = 0;
        destinationCount.textContent = '0 / ' + players.length;
    });

    // delete all destination markers
    clearDestinationsBtn.addEventListener('click', function() {
        players.forEach(player => {
            player.destination = null;
            if (player.marker) {
                player.marker.remove();
                player.marker = null;
            }
        });

        totalDestinations = 0;
        destinationCount.textContent = '0 / ' + players.length;
    });
});