@extends('layouts.app')
@section('title', 'Kantor Virtual')
@section('content')
<div class="mb-6">
  <div class="text-xs text-slate-500 mb-1">Dashboard › <span class="text-brand-600 font-semibold">Kantor Virtual</span></div>
  <h1 class="text-3xl font-extrabold tracking-tight">Kantor Virtual</h1>
  <p class="text-sm text-slate-500">Lihat bagaimana tim bekerja: ada yang di meja, rapat, ngopi di pantry, cek gudang, sampai ruang server.</p>
</div>

<div class="grid xl:grid-cols-[minmax(0,1fr)_320px] gap-5">
  {{-- ===== Panel peta ===== --}}
  <div class="card p-3 sm:p-4">
    <div class="flex flex-wrap items-center gap-2 mb-3">
      <button id="btnPause"   class="btn btn-secondary !h-9 !px-3 text-xs" type="button"><span class="material-symbols-outlined">pause</span><span>Jeda</span></button>
      <button id="btnBubble"  class="btn btn-secondary !h-9 !px-3 text-xs" type="button"><span class="material-symbols-outlined">chat_bubble</span><span>Dialog: ON</span></button>
      <button id="btnSpeed"   class="btn btn-secondary !h-9 !px-3 text-xs" type="button"><span class="material-symbols-outlined">speed</span><span>Kecepatan 1×</span></button>
      <button id="btnMeeting" class="btn btn-secondary !h-9 !px-3 text-xs" type="button"><span class="material-symbols-outlined">groups</span><span>Rapat sekarang</span></button>
      <button id="btnShuffle" class="btn btn-ghost !h-9 !px-3 text-xs" type="button"><span class="material-symbols-outlined">shuffle</span><span>Acak ulang</span></button>
      <span id="statusText" class="ml-auto text-xs text-slate-500">Memuat peta…</span>
    </div>
    <div class="relative rounded-xl overflow-hidden border border-slate-200 bg-slate-900">
      <canvas id="office" width="1536" height="1024" class="block w-full h-auto"
              style="aspect-ratio:1536/1024"
              data-map="{{ asset('img/office/map.png') }}"
              data-raka="{{ asset('img/office/raka.png') }}"
              data-naya="{{ asset('img/office/naya.png') }}"
              data-bimo="{{ asset('img/office/bimo.png') }}"
              aria-label="Peta kantor virtual dengan 3 karakter yang berjalan dan bekerja"></canvas>
    </div>
  </div>

  {{-- ===== Panel samping ===== --}}
  <div class="space-y-5">
    <div class="card p-5">
      <h2 class="font-bold text-lg mb-3">Tim hari ini</h2>
      <div id="people" class="space-y-3"></div>
    </div>
    <div class="card p-5">
      <h2 class="font-bold text-lg mb-3">Aktivitas terbaru</h2>
      <ul id="log" class="space-y-2 text-sm"></ul>
    </div>
  </div>
</div>
@endsection

@push('scripts')
@verbatim
<script>
(() => {
  const canvas = document.getElementById('office'), ctx = canvas.getContext('2d');
  const DEBUG = location.hash === '#debug';           // buka /office#debug untuk lihat titik jalur
  const W = canvas.width, H = canvas.height;

  /* ---------- Sprite: 5 pose per sheet (depan, kiri, belakang, kanan, idle) ---------- */
  const CW = 96, CH = 136, FEET = 131, K = 0.56;       // ukuran sel, garis kaki, skala gambar
  const POSE = { depan: 0, kiri: 1, belakang: 2, kanan: 3, idle: 4 };
  const SEAT_CUT = 95;                                 // baris sprite sampai pinggang (untuk pose duduk)

  /* ---------- Node jalur (koordinat piksel pada map 1536x1024) ---------- */
  const N = {
    // lorong utama
    a0:[252,330], a1:[370,330], a2:[457,330], a3:[546,330], a4:[625,330], a5:[722,330], a6:[745,330], a7:[813,330], a8:[900,330], a9:[1072,330],
    v1b:[370,470], v1d:[370,655], v2b:[625,470], v2d:[625,655], v3b:[900,470], v3d:[900,655], v4d:[1072,655],
    n431:[431,655], r1:[480,655], n549:[549,655], n693:[693,655], n814:[814,655],
    // lobby, santai, dispenser
    r2:[480,805], r3:[622,806], sa:[370,745], sb:[310,745], sc:[276,748], sd:[240,752], w1:[900,740],
    // toilet pria
    t0:[245,372], t1:[222,372], t2:[180,350],
    // ruang manager
    mg0:[252,288], mg1:[258,262], mg2:[258,235], mg3:[95,235],
    // ruang meeting
    mt0:[546,300], mt1:[546,282], mt2:[478,282], mt3:[433,282], mt4:[388,282], mt5:[330,282], mt6:[330,168],
    // ruang HR -> pantry
    hr0:[745,300], hr1:[752,270], hr2:[770,244], p0:[792,236], p1:[822,240], p2:[880,246], p3:[985,244],
    // gudang
    g1:[1130,370], g0:[1095,370], gi:[1072,370], g2:[1200,370], g3:[1200,245], g3b:[1260,245], g4:[1400,245], g45:[1400,330],
    g5:[1400,455], g5b:[1385,455], g6b:[1290,455], g6:[1200,455],
    // ruang server & peralatan
    sva:[1085,668], sv0:[1118,668], sv1:[1136,672], sv2:[1286,672], pr0:[1136,712], pr1:[1150,728], pr2:[1290,728], pr3:[1290,775],
  };
  const CHAINS = [
    ['a0','a1','a2','a3','a4','a5','a6','a7','a8','a9'],
    ['a1','v1b','v1d'], ['a4','v2b','v2d'], ['a8','v3b','v3d'], ['a9','gi'],
    ['v1b','v2b','v3b'],
    ['v1d','n431','r1','n549','v2d','n693','n814','v3d','v4d'],
    ['r1','r2','r3'], ['v1d','sa','sb','sc','sd'], ['v3d','w1'],
    ['a0','t0','t1','t2'], ['a0','mg0','mg1','mg2','mg3'],
    ['a3','mt0','mt1','mt2','mt3','mt4','mt5','mt6'],
    ['a6','hr0','hr1','hr2','p0','p1','p2','p3'],
    ['gi','g0','g1','g2','g3','g3b','g4','g45','g5','g5b','g6b','g6','g2'],
    ['v4d','sva','sv0','sv1','sv2'], ['sv1','pr0','pr1','pr2','pr3'],
  ];
  const ADJ = {};
  const link = (a, b) => { const d = Math.hypot(N[a][0]-N[b][0], N[a][1]-N[b][1]); (ADJ[a] ??= []).push([b,d]); (ADJ[b] ??= []).push([a,d]); };
  CHAINS.forEach(ch => { for (let i = 0; i < ch.length - 1; i++) link(ch[i], ch[i+1]); });

  function shortest(from, to) {                        // Dijkstra sederhana
    if (from === to) return [from];
    const dist = {[from]:0}, prev = {}, todo = new Set(Object.keys(N));
    while (todo.size) {
      let u = null; todo.forEach(k => { if (dist[k] !== undefined && (u === null || dist[k] < dist[u])) u = k; });
      if (u === null || u === to) break;
      todo.delete(u);
      (ADJ[u] || []).forEach(([v, d]) => { if (todo.has(v) && (dist[v] === undefined || dist[u] + d < dist[v])) { dist[v] = dist[u] + d; prev[v] = u; } });
    }
    const out = []; for (let k = to; k; k = prev[k]) out.unshift(k);
    return out[0] === from ? out : [from, to];
  }

  /* ---------- Stasiun (tempat aktivitas) ---------- */
  // [x, y, arah, node, duduk?]  — untuk "duduk", y = garis pinggang
  const S = (group, place, list) => list.map(([x,y,face,node,seat], i) => ({ group, place, x, y, face, node, seat: !!seat, by: null, id: group + i }));
  const STATIONS = [
    ...S('desk', 'Meja kerja', [
      [457,392,'depan','a2',1], [546,392,'depan','a3',1], [722,392,'depan','a5',1], [813,392,'depan','a7',1],
      [430,472,'belakang','v1b',1], [548,472,'belakang','v2b',1], [693,472,'belakang','v2b',1], [814,472,'belakang','v3b',1],
      [431,612,'belakang','n431',1], [549,612,'belakang','n549',1], [693,612,'belakang','n693',1], [814,612,'belakang','n814',1],
    ]),
    ...S('meeting', 'Ruang Meeting', [
      [388,240,'belakang','mt4',1], [433,240,'belakang','mt3',1], [478,240,'belakang','mt2',1],
      [388,160,'depan','mt6',1], [478,160,'depan','mt6',1],
    ]),
    ...S('pantry', 'Pantry', [
      [1000,196,'belakang','p3'], [873,196,'belakang','p2'], [911,224,'kanan','p2',1], [968,224,'kiri','p3',1],
    ]),
    ...S('gudang', 'Gudang', [
      [1260,238,'belakang','g3b'], [1205,235,'kiri','g3'], [1290,458,'belakang','g6b'],
      [1212,470,'kiri','g6'], [1378,500,'kanan','g5b'], [1410,330,'kanan','g45'],
    ]),
    ...S('server', 'Ruang Server', [[1283,600,'kiri','sv2'], [1127,598,'kanan','sv1']]),
    ...S('peralatan', 'Ruang Peralatan', [[1290,775,'kiri','pr3'], [1128,770,'kanan','pr0']]),
    ...S('manager', 'Ruang Manager', [[258,232,'kiri','mg2'], [90,185,'belakang','mg3']]),
    ...S('hr', 'Ruang HR', [[780,198,'belakang','hr1'], [758,225,'kiri','hr1']]),
    ...S('santai', 'Ruang Santai', [[112,742,'depan','sd',1], [152,742,'depan','sd',1], [220,744,'depan','sd',1]]),
    ...S('toilet', 'Toilet Pria', [[180,350,'depan','t2']]),
    ...S('lobby', 'Lobby', [[622,806,'depan','r3'], [560,802,'depan','r3']]),
    ...S('dispenser', 'Area umum', [[905,740,'kanan','w1']]),
  ];
  const byGroup = g => STATIONS.filter(s => s.group === g);

  /* ---------- Karakter ---------- */
  const PEOPLE = [
    { name:'Raka', role:'Frontend Developer', accent:'#60a5fa', img:'raka', speed:78,
      w:{ desk:5, meeting:1.5, pantry:1.5, server:2, manager:.7, santai:1, toilet:.4, hr:.3, dispenser:.5 } },
    { name:'Naya', role:'UI/UX Designer', accent:'#34d399', img:'naya', speed:72,
      w:{ desk:5, meeting:1.5, pantry:1.5, hr:1, manager:1, santai:1.2, toilet:.4, dispenser:.5, lobby:.3 } },
    { name:'Bimo', role:'Office Ops', accent:'#fbbf24', img:'bimo', speed:84,
      w:{ gudang:3, peralatan:2, server:1.5, lobby:1.2, desk:2, pantry:1, meeting:1, dispenser:.8, toilet:.4, santai:.6 } },
  ];
  const SAY = {
    desk: { Raka:['Build-nya lolos! 🎉','Push kecil, dampak besar.','Fix bug checkout dulu.','Refactor dikit ah.'],
            Naya:['Aku rapikan UI dulu ya.','Warna tombolnya pas banget.','Layout dashboard sudah oke 🎨','Cek kontras dark mode.'],
            Bimo:['Rekap stok masuk dulu.','Jadwal kirim sudah rapi.','Cek laporan harian.'] },
    meeting:['Cek agenda dulu ya.','Catat poin penting.','Siapkan bahan presentasi.'],
    pantry:['Ada yang mau ngopi? ☕','Air panasnya sudah siap.','Istirahat sebentar ☕','Camilan masih ada?'],
    gudang:['Stok gudang aman.','Paket baru sudah datang 📦','Hitung ulang stok rak.','Forklift jangan dipinjam dulu!'],
    server:['Server kelihatan sehat.','Uptime aman 👍','Cek backup malam ini.'],
    peralatan:['Aku cek perlengkapan dulu.','Helm dan alat aman.','Perlengkapan lengkap.'],
    manager:['Update progres ke manager.','Ada yang perlu disetujui?','Aku laporan sebentar.'],
    hr:['Ambil berkas cuti dulu.','Rekap absensi minggu ini.','Titip dokumen ya.'],
    santai:['Istirahat sebentar…','Rileks dulu 5 menit.','Kursinya empuk banget 😌'],
    toilet:['Sebentar ya 🚻'],
    lobby:['Ada tamu hari ini?','Cek paket di pintu.','Lobi rapi, aman.'],
    dispenser:['Isi botol dulu 💧','Air dinginnya segar.'],
  };
  const RAPAT = ['Oke, kita mulai rapat.','Progres minggu ini lancar.','Ada kendala di stok?','Siap, aku follow up.','Deal, kita lanjut!','Catat sebagai prioritas.'];
  const PLACE_NAME = Object.fromEntries(STATIONS.map(s => [s.group, s.place]));
  const rand = (a, b) => a + Math.random() * (b - a);
  const pick = a => a[Math.floor(Math.random() * a.length)];
  const DUR = { desk:[10,20], meeting:[8,13], pantry:[6,10], gudang:[7,12], server:[6,10], peralatan:[6,9], manager:[5,8], hr:[5,8], santai:[8,14], toilet:[4,6], lobby:[5,8], dispenser:[3,5] };

  /* ---------- State ---------- */
  let paused = false, bubblesOn = true, speedMul = 1, last = 0, clock = 0;
  let nextMeeting = rand(55, 80), meeting = null;
  const imgs = {}, chars = PEOPLE.map(p => ({ ...p, x:0, y:0, face:'depan', seated:false, moving:false, path:[], st:null, wait:1, bubble:null, phase:Math.random()*6, locked:false, status:'' }));
  const $ = id => document.getElementById(id);

  function takeStation(c, st) { if (c.st) c.st.by = null; c.st = st; st.by = c; }
  function teleport(c, st) { takeStation(c, st); c.x = st.x; c.y = st.y; c.face = st.face; c.seated = st.seat; c.path = []; c.wait = rand(3, 8); setStatus(c, st, false); }

  function chooseStation(c) {
    const opts = [];
    Object.entries(c.w).forEach(([g, w]) => byGroup(g).forEach(s => { if (!s.by && s !== c.st) opts.push([s, w / byGroup(g).length]); }));
    let r = Math.random() * opts.reduce((a, o) => a + o[1], 0);
    for (const [s, w] of opts) { if ((r -= w) <= 0) return s; }
    return opts[0][0];
  }

  function planTo(c, st) {
    const from = c.st ? c.st.node : 'a2', pts = [];
    const add = (x, y) => { const l = pts[pts.length - 1] || { x:c.x, y:c.y }; if (Math.hypot(l.x - x, l.y - y) > 1.5) pts.push({ x, y }); };
    // dari posisi sekarang ke node stasiun lama -> lewat jalur -> ke stasiun baru
    if (c.path.length === 0) add(...N[from]);
    shortest(from, st.node).forEach(k => add(...N[k]));
    add(st.x, st.y);
    takeStation(c, st); c.path = pts; c.seated = false; c.bubble = null; c.wait = 0;
  }

  function setStatus(c, st, log = true) {
    c.status = st.place;
    renderPeople();
    if (log) {
      const li = document.createElement('li');
      li.className = 'flex gap-2 items-start';
      li.innerHTML = `<span class="mt-1.5 w-2 h-2 rounded-full shrink-0" style="background:${c.accent}"></span><span><b>${c.name}</b> di ${st.place}<span class="block text-xs text-slate-500">${new Date().toLocaleTimeString('id-ID')}</span></span>`;
      const ul = $('log'); ul.prepend(li); while (ul.children.length > 7) ul.lastChild.remove();
    }
  }

  function arrive(c) {
    const st = c.st; c.face = st.face; c.seated = st.seat; c.moving = false;
    c.wait = rand(...(DUR[st.group] || [5, 9]));
    if (!meeting || meeting.state === 'gather') {
      const lines = st.group === 'desk' ? SAY.desk[c.name] : SAY[st.group];
      if (bubblesOn && lines) c.bubble = { text:pick(lines), until:clock + rand(2.8, 4.5) };
    }
    setStatus(c, st);
  }

  /* ---------- Rapat tim ---------- */
  function callMeeting() {
    if (meeting) return;
    const seats = byGroup('meeting').slice(0, 3);
    meeting = { state:'gather', t:0, speaker:0 };
    chars.forEach((c, i) => { c.locked = true; planTo(c, seats[i]); });
    $('statusText').textContent = 'Rapat tim dimulai — semua menuju Ruang Meeting';
  }
  function updateMeeting(dt) {
    if (!meeting) return;
    if (meeting.state === 'gather' && chars.every(c => c.path.length === 0)) { meeting.state = 'talk'; meeting.t = 0; meeting.next = 0; }
    if (meeting.state === 'talk') {
      meeting.t += dt;
      if (meeting.t >= meeting.next && bubblesOn) {
        chars.forEach(c => c.bubble = null);
        chars[meeting.speaker % 3].bubble = { text:RAPAT[Math.floor(Math.random() * RAPAT.length)], until:clock + 3.2 };
        meeting.speaker++; meeting.next += 3.4;
      }
      if (meeting.t > 15) { chars.forEach(c => { c.locked = false; c.wait = rand(0.3, 1.5); c.bubble = null; }); meeting = null; nextMeeting = rand(60, 90); $('statusText').textContent = '3 karakter sedang beraktivitas'; }
    }
  }

  /* ---------- Update & render ---------- */
  function update(dt) {
    clock += dt;
    chars.forEach(c => {
      if (c.path.length) {
        const t = c.path[0], dx = t.x - c.x, dy = t.y - c.y, d = Math.hypot(dx, dy), step = c.speed * dt;
        c.moving = true; c.phase += dt * 11; c.seated = false;
        if (d <= step) { c.x = t.x; c.y = t.y; c.path.shift(); if (!c.path.length) arrive(c); }
        else { c.x += dx / d * step; c.y += dy / d * step; c.face = Math.abs(dx) > Math.abs(dy) * 1.1 ? (dx > 0 ? 'kanan' : 'kiri') : (dy > 0 ? 'depan' : 'belakang'); }
      } else {
        c.moving = false;
        if (!c.locked && (c.wait -= dt) <= 0) planTo(c, chooseStation(c));
        if (!c.bubble && bubblesOn && !meeting && !c.locked && Math.random() < dt * 0.05) { const l = c.st.group === 'desk' ? SAY.desk[c.name] : SAY[c.st.group]; if (l) c.bubble = { text:pick(l), until:clock + rand(2.8, 4.2) }; }
      }
      if (c.bubble && clock > c.bubble.until) c.bubble = null;
    });
    updateMeeting(dt);
    if (!meeting && (nextMeeting -= dt) <= 0) callMeeting();
  }

  function drawChar(c) {
    const img = imgs[c.img]; if (!img) return;
    const w = CW * K, h = CH * K, x = Math.round(c.x), y = Math.round(c.y);
    let pose = POSE[c.face], bob = 0;
    if (c.moving) { bob = -Math.abs(Math.sin(c.phase)) * 3; if (c.face === 'depan' && Math.sin(c.phase * 0.5) > 0) pose = POSE.idle; }
    else if (c.face === 'depan' && !c.seated && Math.floor(clock * 1.1) % 2) pose = POSE.idle;       // napas pelan
    let top;
    if (c.seated) {                                                                                  // duduk: tampilkan badan atas saja
      ctx.drawImage(img, pose * CW, 0, CW, SEAT_CUT, x - w / 2, y - SEAT_CUT * K, w, SEAT_CUT * K);
      top = y - (SEAT_CUT - 12) * K;
      if (c.moving === false && c.st && c.st.group === 'desk') { const t = Math.sin(clock * 9 + c.phase) > 0.6 ? 1 : 0; if (t) ctx.drawImage(img, pose * CW, 0, CW, SEAT_CUT, x - w / 2, y - SEAT_CUT * K - 1, w, SEAT_CUT * K); }
    } else {
      ctx.fillStyle = 'rgba(10,20,40,.35)'; ctx.beginPath(); ctx.ellipse(x, y - 1, 15, 5, 0, 0, 7); ctx.fill();
      ctx.drawImage(img, pose * CW, 0, CW, CH, x - w / 2, y - FEET * K + bob, w, h);
      top = y - (FEET - 12) * K + bob;
    }
    // papan nama
    ctx.font = 'bold 12px ui-sans-serif,system-ui'; ctx.textAlign = 'center';
    const lw = ctx.measureText(c.name).width + 14, ty = y + (c.seated ? 6 : 5);
    ctx.fillStyle = 'rgba(16,29,50,.9)'; ctx.fillRect(x - lw / 2, ty, lw, 18);
    ctx.strokeStyle = c.accent; ctx.lineWidth = 1; ctx.strokeRect(x - lw / 2 + .5, ty + .5, lw - 1, 17);
    ctx.fillStyle = '#f2f7ff'; ctx.fillText(c.name, x, ty + 13);
    // dialog
    if (bubblesOn && c.bubble) {
      ctx.font = 'bold 13px ui-sans-serif,system-ui';
      const max = 200, words = c.bubble.text.split(' '), lines = []; let line = '';
      words.forEach(wd => { const t = line ? line + ' ' + wd : wd; if (ctx.measureText(t).width > max - 20) { lines.push(line); line = wd; } else line = t; });
      if (line) lines.push(line);
      const bw = Math.max(...lines.map(t => ctx.measureText(t).width)) + 20, bh = lines.length * 17 + 14;
      let bx = Math.max(6, Math.min(W - bw - 6, x - bw / 2)), by = Math.max(5, top - bh - 12);
      ctx.fillStyle = '#f8fbff'; ctx.strokeStyle = c.accent; ctx.lineWidth = 2;
      ctx.beginPath(); ctx.roundRect(bx, by, bw, bh, 7); ctx.fill(); ctx.stroke();
      ctx.beginPath(); ctx.moveTo(x - 5, by + bh - 1); ctx.lineTo(x + 5, by + bh - 1); ctx.lineTo(x, by + bh + 8); ctx.fill();
      ctx.fillStyle = '#17243a'; lines.forEach((t, i) => ctx.fillText(t, bx + bw / 2, by + 18 + i * 17));
    }
  }

  function drawDebug() {
    ctx.lineWidth = 1; ctx.strokeStyle = 'rgba(255,255,0,.7)'; ctx.fillStyle = 'rgba(255,255,0,.9)'; ctx.font = '10px monospace'; ctx.textAlign = 'left';
    CHAINS.forEach(ch => { ctx.beginPath(); ch.forEach((k, i) => i ? ctx.lineTo(...N[k]) : ctx.moveTo(...N[k])); ctx.stroke(); });
    Object.entries(N).forEach(([k, [x, y]]) => { ctx.fillRect(x - 2, y - 2, 5, 5); ctx.fillText(k, x + 4, y - 3); });
    STATIONS.forEach(s => { ctx.strokeStyle = s.seat ? '#ff6bd6' : '#6bffb0'; ctx.strokeRect(s.x - 6, s.y - 6, 12, 12); ctx.beginPath(); ctx.moveTo(s.x, s.y); ctx.lineTo(...N[s.node]); ctx.stroke(); });
  }

  function render() {
    ctx.clearRect(0, 0, W, H);
    ctx.imageSmoothingEnabled = true; ctx.imageSmoothingQuality = 'high';
    ctx.drawImage(imgs.map, 0, 0, W, H);
    if (DEBUG) drawDebug();
    [...chars].sort((a, b) => a.y - b.y).forEach(drawChar);
  }

  function loop(now) {
    const dt = Math.min(0.05, (now - last) / 1000 || 0); last = now;
    if (!paused) update(dt * speedMul);
    render(); requestAnimationFrame(loop);
  }

  /* ---------- UI ---------- */
  function renderPeople() {
    $('people').innerHTML = chars.map(c => `
      <div class="flex items-center gap-3 rounded-xl border border-slate-100 p-3">
        <span class="w-3 h-3 rounded-md shrink-0" style="background:${c.accent}"></span>
        <div class="min-w-0 flex-1 leading-tight">
          <div class="font-bold text-sm">${c.name} <span class="font-medium text-xs text-slate-500">· ${c.role}</span></div>
          <div class="text-xs text-slate-500 truncate">${c.moving ? 'Berjalan…' : (c.status || '-')}</div>
        </div>
      </div>`).join('');
  }
  const label = (id, icon, text) => { $(id).innerHTML = `<span class="material-symbols-outlined">${icon}</span><span>${text}</span>`; };
  $('btnPause').onclick = () => { paused = !paused; label('btnPause', paused ? 'play_arrow' : 'pause', paused ? 'Lanjut' : 'Jeda'); $('statusText').textContent = paused ? 'Aktivitas dijeda' : '3 karakter sedang beraktivitas'; };
  $('btnBubble').onclick = () => { bubblesOn = !bubblesOn; if (!bubblesOn) chars.forEach(c => c.bubble = null); label('btnBubble', 'chat_bubble', 'Dialog: ' + (bubblesOn ? 'ON' : 'OFF')); };
  $('btnSpeed').onclick = () => { speedMul = speedMul % 3 + 1; label('btnSpeed', 'speed', 'Kecepatan ' + speedMul + '×'); };
  $('btnMeeting').onclick = callMeeting;
  $('btnShuffle').onclick = () => {
    meeting = null; nextMeeting = rand(55, 80); chars.forEach(c => { c.locked = false; c.bubble = null; c.st && (c.st.by = null); c.st = null; });
    chars.forEach(c => teleport(c, chooseStation({ ...c, st:null })));
  };
  setInterval(renderPeople, 1500);

  /* ---------- Muat gambar lalu mulai ---------- */
  const load = src => new Promise((ok, no) => { const i = new Image(); i.onload = () => ok(i); i.onerror = () => no(src); i.src = src; });
  Promise.all([['map', canvas.dataset.map], ...PEOPLE.map(p => [p.img, canvas.dataset[p.img]])].map(([k, s]) => load(s).then(i => imgs[k] = i)))
    .then(() => {
      const start = [byGroup('desk')[0], byGroup('desk')[2], byGroup('gudang')[0]];
      chars.forEach((c, i) => teleport(c, start[i]));
      $('statusText').textContent = '3 karakter sedang beraktivitas';
      requestAnimationFrame(loop);
    })
    .catch(src => { $('statusText').textContent = 'Gagal memuat gambar: ' + src + ' (cek folder public/img/office)'; });

  window.__office = { chars, STATIONS, N, callMeeting, teleport, planTo, render };
})();
</script>
@endverbatim
@endpush
