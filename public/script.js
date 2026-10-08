const court = document.getElementById('court');

const POSITION_LABELS = {
    S: 'Setter',
    MB: 'Middle blocker',
    OT: 'Outside hitter',
    OH: 'Outside hitter',
    RS: 'Right side',
    L: 'Libero'
};

// single floating tooltip reused for every .player/.mini-player-abs hover,
// on every page (create/edit courts, saved-rotation show pages, sequence previews)
let playerTooltipEl = null;

function ensurePlayerTooltip() {
    if (!playerTooltipEl) {
        playerTooltipEl = document.createElement('div');
        playerTooltipEl.className = 'player-tooltip';
        document.body.appendChild(playerTooltipEl);
    }
    return playerTooltipEl;
}

function playerTooltipContent(el) {
    const rawName = (el.dataset.name || '').trim();
    const role = (el.dataset.role || '').trim();
    const label = role ? (POSITION_LABELS[role] || role) : '';

    if (rawName) {
        return { title: rawName, sub: label };
    }

    return { title: label || el.textContent.trim() || 'Player', sub: '' };
}

function showPlayerTooltip(el) {
    const { title, sub } = playerTooltipContent(el);
    const tip = ensurePlayerTooltip();

    tip.textContent = '';
    tip.appendChild(document.createTextNode(title));

    if (sub) {
        const subEl = document.createElement('span');
        subEl.className = 'tooltip-sub';
        subEl.textContent = sub;
        tip.appendChild(subEl);
    }

    const rect = el.getBoundingClientRect();
    tip.style.left = (rect.left + rect.width / 2) + 'px';
    tip.style.top = rect.top + 'px';
    tip.classList.add('visible');
}

function hidePlayerTooltip() {
    if (playerTooltipEl) playerTooltipEl.classList.remove('visible');
}

document.addEventListener('mouseover', e => {
    const el = e.target.closest('.player, .mini-player-abs');
    if (el) showPlayerTooltip(el);
});

document.addEventListener('mouseout', e => {
    const el = e.target.closest('.player, .mini-player-abs');
    if (el) hidePlayerTooltip();
});

document.addEventListener('scroll', hidePlayerTooltip, true);

// shrinks #court to fit its .court-wrap container (desktop: wrap is >=500px so this
// is a no-op scale of 1; phone: wrap is narrower, so the whole court scales down).
// Only runs on pages that actually wrap #court in .court-wrap (create/edit) — show
// pages use a different, non-draggable .court-view technique and don't have this class.
function updateCourtScale() {
    if (!court) return;

    const wrap = court.parentElement;
    if (!wrap || !wrap.classList.contains('court-wrap')) return;

    const scale = wrap.clientWidth / court.offsetWidth;
    court.style.transform = `scale(${scale})`;
}

window.addEventListener('resize', updateCourtScale);
updateCourtScale();

// attaches drag-to-reposition to every .player inside the court, and tells
// the roster/bench logic (if active) about plain clicks vs. drags. Uses Pointer Events
// (not mouse events) so this works identically with a mouse, a finger on a phone, or a
// pen — offsetX/offsetY are already reported in the player's own untransformed space by
// the browser, but clientX/clientY are raw screen pixels, so those get divided by the
// live court scale to land back in the court's 500x400 virtual coordinate space.
function attachDragHandlers(courtEl) {
    if (!courtEl) return;

    courtEl.querySelectorAll('.player').forEach(player => {
        if (player.dataset.dragBound) return;
        player.dataset.dragBound = '1';

        player.addEventListener('pointerdown', e => {
            if (e.pointerType === 'mouse' && e.button !== 0) return;

            const offsetX = e.offsetX;
            const offsetY = e.offsetY;
            let moved = false;
            let rafId = null;
            let pendingX = null;
            let pendingY = null;
            hidePlayerTooltip();
            player.setPointerCapture(e.pointerId);
            // drop the CSS snap-transition while actively dragging — otherwise every
            // move update gets animated instead of tracking the pointer instantly
            player.classList.add('dragging');

            function applyPending() {
                rafId = null;
                player.style.left = pendingX + 'px';
                player.style.top = pendingY + 'px';
            }

            function move(e) {
                moved = true;
                const rect = courtEl.getBoundingClientRect();
                const scale = rect.width / courtEl.offsetWidth;

                let x = (e.clientX - rect.left) / scale - offsetX;
                let y = (e.clientY - rect.top) / scale - offsetY;

                x = Math.max(0, Math.min(x, courtEl.clientWidth - player.clientWidth));
                y = Math.max(0, Math.min(y, courtEl.clientHeight - player.clientHeight));

                pendingX = x;
                pendingY = y;

                // batch style writes to one per animation frame instead of one per
                // move event, which avoids layout thrashing on fast pointer movement
                if (rafId === null) {
                    rafId = requestAnimationFrame(applyPending);
                }
            }

            function up() {
                player.removeEventListener('pointermove', move);
                player.removeEventListener('pointerup', up);
                player.removeEventListener('pointercancel', up);

                if (rafId !== null) {
                    cancelAnimationFrame(rafId);
                    applyPending();
                }

                player.classList.remove('dragging');

                if (moved) {
                    if (window.rememberCourtPosition) {
                        window.rememberCourtPosition(player.dataset.pos, player.style.top, player.style.left);
                    }
                } else if (window.handleCourtSlotClick) {
                    window.handleCourtSlotClick(player.dataset.pos);
                }
            }

            player.addEventListener('pointermove', move);
            player.addEventListener('pointerup', up, { once: true });
            player.addEventListener('pointercancel', up, { once: true });
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
        OH: 'Outside hitter',
        RS: 'Right side',
        L: 'Libero'
    };

    let selectedTeam = null;
    let courtSlots = { 1: null, 2: null, 3: null, 4: null, 5: null, 6: null };
    let courtPositions = {};
    let selectedBenchPlayerId = null;

    // "full sequence" authoring mode — 6 independently editable court layouts for one rotation
    let sequenceMode = false;
    let sequenceStates = { 1: null, 2: null, 3: null, 4: null, 5: null, 6: null };
    let activeSlot = 1;

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
                el.className = 'player' + (String(pos) === '1' ? ' is-serving' : '');
                el.dataset.pos = pos;
                el.dataset.role = occupant.position || '';
                el.dataset.userId = occupant.id;
                el.dataset.name = occupant.name;
                el.style.top = coords.top + 'px';
                el.style.left = coords.left + 'px';
                el.textContent = playerLabel(occupant.position, occupant.name);
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

    // builds the standard base rotation (pos4 RS, pos3 MB, pos2 OH / pos5 OH, pos6 L or MB, pos1 S)
    // instead of just dropping roster players into slots in roster order — a libero can never
    // legally stand front row, so it is only ever placed at pos6 (back row)
    function autoFillFromRoster(players) {
        courtSlots = { 1: null, 2: null, 3: null, 4: null, 5: null, 6: null };

        // reset drag positions to the default zone layout — otherwise a drag made while
        // editing a different sequence slot (or a previous team selection) would "leak" into
        // this fresh auto-filled slot at whichever zone was last dragged
        Object.keys(zoneCenters).forEach(pos => {
            courtPositions[pos] = { ...zoneCenters[pos] };
        });

        const byRole = { S: [], OH: [], MB: [], RS: [], L: [] };
        players.forEach(player => {
            if (player.position && byRole[player.position]) {
                byRole[player.position].push(player);
            }
        });

        const setter = byRole.S[0] || null;
        const opposite = byRole.RS[0] || null;
        const outsides = byRole.OH.slice(0, 2);
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

        // courtPositions persists drag positions across calls (needed so re-rendering the
        // same slot keeps a drag), but that means a drag made on one sequence slot would
        // otherwise "leak" into every other slot that doesn't set its own top/left (a fresh
        // autoFillFromRoster slot, for example) — reset to the default zone layout first so
        // each slot only reflects positions its own data explicitly provides
        Object.keys(zoneCenters).forEach(pos => {
            courtPositions[pos] = { ...zoneCenters[pos] };
        });

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

    // captures whatever is currently on the court into the active slot. Reads the live DOM
    // (via captureCourtPlayers, the same function single-mode save uses) rather than the
    // courtSlots/courtPositions state objects — those only track bench substitutions and
    // drags, but rotateClockwise()/handleLiberoSub() mutate the .player elements directly
    // and never touch courtSlots, so reading courtSlots here would silently drop any
    // "Rotate Clockwise" changes made while editing a slot
    function captureActiveSlotIntoState() {
        sequenceStates[activeSlot] = captureCourtPlayers();
    }

    window.setSequenceMode = function (enabled) {
        sequenceMode = enabled;

        if (enabled) {
            activeSlot = 1;
            sequenceStates = { 1: null, 2: null, 3: null, 4: null, 5: null, 6: null };

            if (selectedTeam) {
                autoFillFromRoster(selectedTeam.players);
                renderCourt();
                renderBench();
            }
        }
    };

    window.isSequenceMode = function () {
        return sequenceMode;
    };

    window.selectSequenceSlot = function (slot) {
        if (!selectedTeam) return;

        captureActiveSlotIntoState();
        activeSlot = slot;

        const seed = sequenceStates[slot];

        if (seed && seed.length) {
            selectTeam(selectedTeam.id, seed);
        } else {
            autoFillFromRoster(selectedTeam.players);
            renderCourt();
            renderBench();
        }
    };

    window.getSequencePayload = function () {
        captureActiveSlotIntoState();

        return [1, 2, 3, 4, 5, 6].map(n => sequenceStates[n] || []);
    };

    // pre-loads a saved sequence-type rotation's 6 states so the edit page opens on slot 1
    // and setSequenceMode/selectSequenceSlot above work with real data right away
    function loadSequenceFromCurrent(teamId, slotsPlayers) {
        selectedTeam = findTeam(teamId);
        sequenceMode = true;
        activeSlot = 1;

        for (let i = 1; i <= 6; i++) {
            sequenceStates[i] = slotsPlayers[i - 1] || [];
        }

        selectTeam(teamId, sequenceStates[1]);
        window.__initialSequenceMode = true;
    }

    if (current && current.team_id && findTeam(current.team_id)) {
        teamSelect.value = current.team_id;

        if (current.type === 'sequence' && Array.isArray(current.players)) {
            loadSequenceFromCurrent(current.team_id, current.players);
        } else {
            selectTeam(current.team_id, current.players);
        }
    } else if (teams.length === 1) {
        teamSelect.value = teams[0].id;
        selectTeam(teams[0].id);
    } else {
        renderBench();
    }
})();

// Rotation Mode (single vs full sequence) — wired to the #rotationMode select + #sequenceTabs
// tab strip that create/edit blade templates render when the user has a manageable team.
function onRotationModeChange() {
    const modeEl = document.getElementById('rotationMode');
    const tabs = document.getElementById('sequenceTabs');
    const teamSelect = document.getElementById('teamSelect');

    if (!modeEl || !tabs) return;

    if (modeEl.value === 'sequence') {
        if (!window.setSequenceMode || !teamSelect || !teamSelect.value) {
            alert('Select a team first to build a full sequence.');
            modeEl.value = 'single';
            return;
        }

        window.setSequenceMode(true);
        tabs.classList.remove('hidden');
        tabs.classList.add('flex');
        onSequenceTabClick(1);
    } else {
        if (window.setSequenceMode) window.setSequenceMode(false);
        tabs.classList.add('hidden');
        tabs.classList.remove('flex');
    }
}

function onSequenceTabClick(n) {
    if (window.selectSequenceSlot) window.selectSequenceSlot(n);

    document.querySelectorAll('.sequence-tab').forEach(btn => {
        const active = parseInt(btn.dataset.slot) === n;
        btn.classList.toggle('bg-blue-600', active);
        btn.classList.toggle('border-blue-400', active);
        btn.classList.toggle('text-white', active);
        btn.classList.toggle('bg-white/5', !active);
        btn.classList.toggle('text-white/70', !active);
    });
}

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

// reads the currently rendered court into the same flat player-array shape the
// backend expects for a "single" rotation (also reused as the base of each
// "sequence" slot's capture — see window.getSequencePayload in the team-roster IIFE)
function captureCourtPlayers() {
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

    return players;
}

function saveRotation() {

    const name = document.getElementById('rotation-name').value;
    const type = document.getElementById('rotationType').value;

    if (!name) {
        alert("Please enter rotation name");
        return;
    }

    const modeEl = document.getElementById('rotationMode');
    const isSequence = !!(modeEl && modeEl.value === 'sequence' && window.isSequenceMode && window.isSequenceMode());

    let players;

    if (isSequence) {
        players = window.getSequencePayload();

        const filledSlots = players.filter(slot => slot.length > 0).length;
        if (filledSlots < 6) {
            alert('Fill all 6 rotations before saving the sequence (currently ' + filledSlots + '/6).');
            return;
        }
    } else {
        players = captureCourtPlayers();
    }

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
            team_id: teamId,
            type: isSequence ? 'sequence' : 'single'
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

    if (document.querySelectorAll('.player').length < 6) {
        alert("Fill all 6 court positions before rotating.");
        return;
    }

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

    const modeEl = document.getElementById('rotationMode');
    const isSequence = !!(modeEl && modeEl.value === 'sequence' && window.isSequenceMode && window.isSequenceMode());

    let players;

    if (isSequence) {
        players = window.getSequencePayload();

        const filledSlots = players.filter(slot => slot.length > 0).length;
        if (filledSlots < 6) {
            alert('Fill all 6 rotations before saving the sequence (currently ' + filledSlots + '/6).');
            return;
        }
    } else {
        players = captureCourtPlayers();
    }

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
            team_id: teamId,
            type: isSequence ? 'sequence' : 'single'
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

        // #court may be visually scaled down to fit a narrow screen (.court-wrap) while
        // staying laid out at its native 500x400px — divide back into that virtual space
        // so the destination marker/animation land where the tap actually was
        const rect = court.getBoundingClientRect();
        const scale = rect.width / court.offsetWidth;
        const x = (event.clientX - rect.left) / scale;
        const y = (event.clientY - rect.top) / scale;

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

// on the edit page, a saved "sequence" rotation pre-loads its 6 states (see
// loadSequenceFromCurrent above) and flags window.__initialSequenceMode — sync the
// Rotation Mode dropdown + tab strip to match once everything has rendered
if (window.__initialSequenceMode) {
    const modeEl = document.getElementById('rotationMode');
    const tabs = document.getElementById('sequenceTabs');

    if (modeEl) modeEl.value = 'sequence';
    if (tabs) {
        tabs.classList.remove('hidden');
        tabs.classList.add('flex');
    }

    // opened via a "click a rotation card to edit it" link from the show page
    // (?slot=N) — jump straight to that slot instead of always slot 1
    const initialSlot = window.ROTATION_INITIAL_SLOT || 1;
    if (typeof onSequenceTabClick === 'function') onSequenceTabClick(initialSlot);
}