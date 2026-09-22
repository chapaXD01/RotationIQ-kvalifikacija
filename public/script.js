const court = document.getElementById('court');

// attaches drag-to-reposition to every .player inside the court, and tells
// the roster/bench logic (if active) about plain clicks vs. drags
function attachDragHandlers(courtEl) {
    if (!courtEl) return;

    courtEl.querySelectorAll('.player').forEach(player => {
        if (player.dataset.dragBound) return;
        player.dataset.dragBound = '1';

        let offsetX = 0;
        let offsetY = 0;

        player.addEventListener('mousedown', e => {
            offsetX = e.offsetX;
            offsetY = e.offsetY;
            let moved = false;

            function move(e) {
                moved = true;
                const rect = courtEl.getBoundingClientRect();

                let x = e.clientX - rect.left - offsetX;
                let y = e.clientY - rect.top - offsetY;


                x = Math.max(0, Math.min(x, courtEl.clientWidth - player.clientWidth));
                y = Math.max(0, Math.min(y, courtEl.clientHeight - player.clientHeight));

                player.style.left = x + 'px';
                player.style.top = y + 'px';
            }

            document.addEventListener('mousemove', move);

            document.addEventListener('mouseup', () => {
                document.removeEventListener('mousemove', move);

                if (moved) {
                    if (window.rememberCourtPosition) {
                        window.rememberCourtPosition(player.dataset.pos, player.style.top, player.style.left);
                    }
                } else if (window.handleCourtSlotClick) {
                    window.handleCourtSlotClick(player.dataset.pos);
                }
            }, { once: true });
        });
    });
}

attachDragHandlers(court);

// Team roster / bench / substitution — only active on rotation create/edit
// pages that render a #teamSelect + #benchList (i.e. the manager has a team).
(function initTeamRoster() {
    const teamSelect = document.getElementById('teamSelect');
    const benchList = document.getElementById('benchList');

    if (!teamSelect || !court || !benchList) return;

    const teams = window.ROTATION_TEAMS || [];
    const current = window.ROTATION_CURRENT || null;

    const zoneCenters = {
        1: { top: 278, left: 395 },
        2: { top: 78,  left: 395 },
        3: { top: 78,  left: 228 },
        4: { top: 78,  left: 61  },
        5: { top: 278, left: 61  },
        6: { top: 278, left: 228 }
    };

    const positionLabels = {
        S: 'Setter',
        MB: 'Middle blocker',
        OT: 'Outside hitter',
        RS: 'Right side',
        L: 'Libero'
    };

    let selectedTeam = null;
    let courtSlots = { 1: null, 2: null, 3: null, 4: null, 5: null, 6: null };
    let courtPositions = {};
    let selectedBenchPlayerId = null;

    Object.keys(zoneCenters).forEach(pos => {
        courtPositions[pos] = { ...zoneCenters[pos] };
    });

    teams.forEach(team => {
        const opt = document.createElement('option');
        opt.value = team.id;
        opt.textContent = team.name;
        opt.style.background = '#1e293b';
        teamSelect.appendChild(opt);
    });

    function findTeam(id) {
        return teams.find(t => String(t.id) === String(id)) || null;
    }

    function seatedIds() {
        return Object.values(courtSlots).filter(Boolean).map(p => String(p.id));
    }

    function playerLabel(position, name) {
        if (position) return position;
        return name ? name.trim().charAt(0).toUpperCase() : '?';
    }

    function tooltipFor(name, position) {
        return position ? `${name} — ${positionLabels[position] || position}` : name;
    }

    function renderCourt() {
        court.querySelectorAll('.player, .empty-slot').forEach(el => el.remove());

        Object.keys(zoneCenters).forEach(pos => {
            const occupant = courtSlots[pos];
            const coords = courtPositions[pos] || zoneCenters[pos];

            if (occupant) {
                const el = document.createElement('div');
                el.className = 'player';
                el.dataset.pos = pos;
                el.dataset.role = occupant.position || '';
                el.dataset.userId = occupant.id;
                el.dataset.name = occupant.name;
                el.style.top = coords.top + 'px';
                el.style.left = coords.left + 'px';
                el.textContent = playerLabel(occupant.position, occupant.name);
                el.title = tooltipFor(occupant.name, occupant.position);
                court.appendChild(el);
            } else {
                const el = document.createElement('div');
                el.className = 'empty-slot';
                el.dataset.pos = pos;
                el.style.top = coords.top + 'px';
                el.style.left = coords.left + 'px';
                el.textContent = '+';
                el.title = 'Click to place a player here';
                el.addEventListener('click', () => handleSlotClick(pos));
                court.appendChild(el);
            }
        });

        attachDragHandlers(court);
    }

    function renderBench() {
        benchList.innerHTML = '';

        if (!selectedTeam) {
            benchList.innerHTML = '<p class="bench-empty">Select a team to see your players.</p>';
            return;
        }

        const seated = seatedIds();
        const bench = selectedTeam.players.filter(p => !seated.includes(String(p.id)));

        if (bench.length === 0) {
            benchList.innerHTML = '<p class="bench-empty">Everyone is on the court.</p>';
            return;
        }

        bench.forEach(player => {
            const hasPosition = !!player.position;

            const item = document.createElement('div');
            item.className = 'bench-item'
                + (String(player.id) === String(selectedBenchPlayerId) ? ' selected' : '')
                + (hasPosition ? '' : ' bench-item-disabled');
            item.title = hasPosition
                ? tooltipFor(player.name, player.position)
                : `${player.name} has no court position set — assign one in the team roster before adding them to a rotation.`;

            const name = document.createElement('span');
            name.textContent = player.name;
            item.appendChild(name);

            const badge = document.createElement('span');
            badge.className = 'bench-position' + (hasPosition ? '' : ' bench-position-none');
            badge.textContent = hasPosition ? player.position : 'No position';
            item.appendChild(badge);

            if (hasPosition) {
                item.addEventListener('click', () => {
                    selectedBenchPlayerId = (String(selectedBenchPlayerId) === String(player.id)) ? null : player.id;
                    renderBench();
                });
            }

            benchList.appendChild(item);
        });
    }

    // clicking a bench player then a court slot substitutes them in;
    // clicking an occupied slot with nothing selected benches that player
    function handleSlotClick(pos) {
        if (!selectedTeam) return;

        if (selectedBenchPlayerId) {
            const incoming = selectedTeam.players.find(p => String(p.id) === String(selectedBenchPlayerId));

            if (incoming && incoming.position) {
                courtSlots[pos] = { id: incoming.id, name: incoming.name, position: incoming.position };
            }

            selectedBenchPlayerId = null;
        } else if (courtSlots[pos]) {
            courtSlots[pos] = null;
        }

        renderCourt();
        renderBench();
    }

    window.handleCourtSlotClick = handleSlotClick;
    window.rememberCourtPosition = function (pos, top, left) {
        courtPositions[pos] = { top: parseFloat(top), left: parseFloat(left) };
    };

    // builds the standard base rotation (pos4 RS, pos3 MB, pos2 OT / pos5 OT, pos6 L or MB, pos1 S)
    // instead of just dropping roster players into slots in roster order — a libero can never
    // legally stand front row, so it is only ever placed at pos6 (back row)
    function autoFillFromRoster(players) {
        courtSlots = { 1: null, 2: null, 3: null, 4: null, 5: null, 6: null };

        const byRole = { S: [], OT: [], MB: [], RS: [], L: [] };
        players.forEach(player => {
            if (player.position && byRole[player.position]) {
                byRole[player.position].push(player);
            }
        });

        const setter = byRole.S[0] || null;
        const opposite = byRole.RS[0] || null;
        const outsides = byRole.OT.slice(0, 2);
        const middles = byRole.MB.slice(0, 2);
        const libero = byRole.L[0] || null;

        const place = (pos, player) => {
            if (player) {
                courtSlots[pos] = { id: player.id, name: player.name, position: player.position };
            }
        };

        place('1', setter);
        place('4', opposite);
        place('3', middles[0]);
        place('6', libero || middles[1]);
        place('2', outsides[0]);
        place('5', outsides[1]);
    }

    function selectTeam(teamId, seedPlayers) {
        selectedTeam = findTeam(teamId);
        selectedBenchPlayerId = null;
        courtSlots = { 1: null, 2: null, 3: null, 4: null, 5: null, 6: null };

        if (selectedTeam && seedPlayers && seedPlayers.length) {
            seedPlayers.forEach(sp => {
                if (!sp.pos || !sp.user_id) return;

                const rosterPlayer = selectedTeam.players.find(p => String(p.id) === String(sp.user_id));

                courtSlots[sp.pos] = {
                    id: sp.user_id,
                    name: sp.name || (rosterPlayer ? rosterPlayer.name : sp.role),
                    position: sp.role
                };
                courtPositions[sp.pos] = { top: parseFloat(sp.top), left: parseFloat(sp.left) };
            });
        } else if (selectedTeam) {
            autoFillFromRoster(selectedTeam.players);
        }

        renderCourt();
        renderBench();
    }

    teamSelect.addEventListener('change', () => selectTeam(teamSelect.value));

    if (current && current.team_id && findTeam(current.team_id)) {
        teamSelect.value = current.team_id;
        selectTeam(current.team_id, current.players);
    } else if (teams.length === 1) {
        teamSelect.value = teams[0].id;
        selectTeam(teams[0].id);
    } else {
        renderBench();
    }
})();

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
    if (document.querySelectorAll('.player').length < 6) {
        alert("Fill all 6 court positions before checking the rotation.");
        return;
    }

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
            left: parseFloat(player.style.left),
            user_id: player.dataset.userId || null,
            name: player.dataset.name || null
        });

    });

    const teamSelectEl = document.getElementById('teamSelect');
    const teamId = (teamSelectEl && teamSelectEl.value) ? teamSelectEl.value : null;

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
            players: players,
            team_id: teamId
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
            left: parseFloat(player.style.left),
            user_id: player.dataset.userId || null,
            name: player.dataset.name || null
        });
    });

    const teamSelectEl = document.getElementById('teamSelect');
    const teamId = (teamSelectEl && teamSelectEl.value) ? teamSelectEl.value : null;

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
            players: players,
            team_id: teamId
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