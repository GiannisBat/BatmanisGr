// ===== CORE ENGINE FOR BATMANIS.GR =====
const REFRESH_TIME = 60 * 60 * 1000;
const CACHE_TTL = 5 * 60 * 1000;

// ===== STATE =====
let currentView = 'grid';
let currentCat = typeof INITIAL_CATEGORY !== 'undefined' ? INITIAL_CATEGORY : 'all';
let currentSearch = '';
let bookmarks = JSON.parse(localStorage.getItem('bookmarks') || '[]');
let seenItems = JSON.parse(localStorage.getItem('seenItems') || '{}');
let calDate = new Date();

// ===== DARK MODE =====
function applyTheme() {
    const dark = localStorage.getItem('darkMode') === '1';
    document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
    const toggle = document.getElementById('dark-toggle');
    if (toggle) toggle.textContent = dark ? '☀️' : '🌙';
}
function toggleDark() {
    const dark = localStorage.getItem('darkMode') === '1';
    localStorage.setItem('darkMode', dark ? '0' : '1');
    applyTheme();
}
applyTheme();

// ===== VIEW MODE =====
function setView(v) {
    currentView = v;
    const grid = document.getElementById('main-grid');
    if (grid) grid.classList.toggle('list-view', v === 'list');
    const btnGrid = document.getElementById('btn-grid');
    const btnList = document.getElementById('btn-list');
    if (btnGrid) btnGrid.classList.toggle('active', v === 'grid');
    if (btnList) btnList.classList.toggle('active', v === 'list');
}

// ===== CATEGORY FILTER =====
function filterCat(el) {
    document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    currentCat = el.dataset.cat;
    applyFilters();
}
function applyFilters() {
    document.querySelectorAll('.feed-container[data-cat]').forEach(fc => {
        const cat = fc.dataset.cat;
        const matchCat = currentCat === 'all' || cat === currentCat;
        fc.classList.toggle('hidden', !matchCat);
    });
    applySearchFilter();
}

// ===== SEARCH =====
function doSearch(val) {
    currentSearch = val.toLowerCase();
    applySearchFilter();
}
function clearSearch() {
    const input = document.getElementById('search-input');
    if (input) input.value = '';
    currentSearch = '';
    applySearchFilter();
}
function applySearchFilter() {
    if (!currentSearch) {
        document.querySelectorAll('.feed-container').forEach(fc => {
            if (!fc.classList.contains('hidden')) fc.classList.remove('search-hidden');
            fc.querySelectorAll('.news-item').forEach(ni => ni.style.display = '');
        });
        return;
    }
    document.querySelectorAll('.feed-container').forEach(fc => {
        if (fc.classList.contains('hidden')) return;
        let anyMatch = false;
        fc.querySelectorAll('.news-item').forEach(ni => {
            const title = ni.querySelector('a')?.textContent?.toLowerCase() || '';
            const excerpt = ni.querySelector('.news-excerpt')?.textContent?.toLowerCase() || '';
            const match = title.includes(currentSearch) || excerpt.includes(currentSearch);
            ni.style.display = match ? '' : 'none';
            if (match) anyMatch = true;
        });
        fc.classList.toggle('search-hidden', !anyMatch);
    });
}

// ===== CACHE HELPERS =====
function getCached(key) {
    try {
        const raw = localStorage.getItem('rss_' + key);
        if (!raw) return null;
        const obj = JSON.parse(raw);
        if (Date.now() - obj.ts > CACHE_TTL) return null;
        return obj.data;
    } catch { return null; }
}
function setCache(key, data) {
    try { localStorage.setItem('rss_' + key, JSON.stringify({ ts: Date.now(), data })); } catch {}
}

// ===== FETCH FEEDS ENGINE =====
async function fetchFeeds() {
    if (typeof newsSources === 'undefined' || !Array.isArray(newsSources)) return;

    const grid = document.getElementById('main-grid');
    const btn = document.getElementById('refresh-btn');
    if (btn) {
        btn.classList.add('spinning');
        btn.textContent = ' Φόρτωση...';
    }

    if (grid) {
        grid.innerHTML = '';
        newsSources.forEach((_, i) => {
            grid.innerHTML += `<div id="feed-${i}" class="skeleton-feed">
                <div class="skeleton-title"></div>
                ${[1,2,3].map(() => `<div class="skeleton-item"><div class="skeleton-img"></div><div class="skeleton-lines"><div class="skeleton-line"></div><div class="skeleton-line"></div><div class="skeleton-line"></div></div></div>`).join('')}
            </div>`;
        });
    }

    const BATCH_SIZE = 4;
    for (let i = 0; i < newsSources.length; i += BATCH_SIZE) {
        const batch = newsSources.slice(i, i + BATCH_SIZE).map((src, batchIdx) => {
            const index = i + batchIdx;
            return loadRSS(src.url, src.cat, `feed-${index}`);
        });
        await Promise.allSettled(batch);
        applyFilters();
        if (currentSearch) applySearchFilter();
    }

    updateBookmarkCount();

    const now = new Date();
    const lastUpdateEl = document.getElementById('last-update');
    if (lastUpdateEl) lastUpdateEl.textContent = `Τελευταία ενημέρωση: ${now.toLocaleTimeString('el-GR')}`;
    if (btn) {
        btn.classList.remove('spinning');
        btn.textContent = '↻ Ανανέωση';
    }
}

async function loadRSS(url, cat, containerId) {
    const cacheKey = btoa(url).replace(/[^a-z0-9]/gi, '').substr(0, 40);
    let data = getCached(cacheKey);

    if (!data) {
        try {
            const api_url = `./rss-proxy.php?url=${encodeURIComponent(url)}`;
            const response = await fetch(api_url);
            data = await response.json();
            if (data.status === 'ok') setCache(cacheKey, data);
        } catch {
            const el = document.getElementById(containerId);
            if (el) el.innerHTML = `<div style="color:var(--text3);font-size:0.85em;padding:10px;">⚠️ Αποτυχία φόρτωσης</div>`;
            return;
        }
    }

    if (data && data.status === 'ok') {
        const feedUrl = data.feed.link || data.feed.url || url;
        let newCount = 0;

        data.items.slice(0, 8).forEach(item => {
            const key = item.link;
            if (!seenItems[key]) newCount++;
        });

        let html = `<div class="feed-title">
            <div class="feed-title-left">
                <span class="feed-title-text">${data.feed.title || 'RSS Feed'}</span>
                <span class="feed-badge ${newCount > 0 ? 'has-new' : ''}">${newCount}</span>
            </div>
            <a href="${feedUrl}" target="_blank" class="feed-visit-btn">🔗 Επίσκεψη</a>
        </div>`;

        data.items.slice(0, 8).forEach(item => {
            let thumb = item.thumbnail || (item.enclosure && item.enclosure.link) || '';
            const imgHtml = thumb
                ? `<img src="${thumb}" alt="" loading="lazy" onerror="this.style.display='none'">`
                : `<div style="width:76px;height:76px;background:var(--skeleton);border-radius:8px;flex-shrink:0;"></div>`;

            const date = new Date(item.pubDate).toLocaleString('el-GR', { day:'2-digit', month:'2-digit', hour:'2-digit', minute:'2-digit' });
            let tempDiv = document.createElement("div");
            tempDiv.innerHTML = item.description;
            let words = (tempDiv.textContent || '').split(/\s+/).slice(0, 25).join(" ") + "...";

            const isSaved = bookmarks.some(b => b.link === item.link);
            const isNew = !seenItems[item.link];

            html += `<div class="news-item" ${isNew ? 'style="border-left:3px solid var(--accent);padding-left:11px;"' : ''}>
                ${imgHtml}
                <div class="news-content">
                    <a href="${item.link}" target="_blank" onclick="markSeen('${item.link.replace(/'/g,"\\'")}');this.parentElement.parentElement.style.borderLeft='';">${item.title}</a>
                    <span class="news-date">📅 ${date}</span>
                    <div class="news-excerpt">${words}</div>
                </div>
                <button class="bookmark-btn ${isSaved ? 'saved' : ''}"
                    onclick="toggleBookmark(this, '${item.link.replace(/'/g,"\\'")}', '${item.title.replace(/'/g,"\\'").replace(/"/g,'&quot;')}')"
                    title="${isSaved ? 'Αφαίρεση' : 'Αποθήκευση'}">
                    🔖
                </button>
            </div>`;

            seenItems[item.link] = 1;
        });

        localStorage.setItem('seenItems', JSON.stringify(seenItems));

        const el = document.getElementById(containerId);
        if (el) {
            el.className = 'feed-container';
            el.dataset.cat = cat;
            el.innerHTML = html;
        }
    }
}

// ===== MARK SEEN =====
function markSeen(link) {
    seenItems[link] = 1;
    localStorage.setItem('seenItems', JSON.stringify(seenItems));
}

// ===== BOOKMARKS =====
function toggleBookmark(btn, link, title) {
    const idx = bookmarks.findIndex(b => b.link === link);
    if (idx === -1) {
        bookmarks.push({ link, title });
        btn.classList.add('saved');
        btn.textContent = '🔖';
    } else {
        bookmarks.splice(idx, 1);
        btn.classList.remove('saved');
        btn.textContent = '🔖';
    }
    localStorage.setItem('bookmarks', JSON.stringify(bookmarks));
    updateBookmarkCount();
    renderBookmarks();
}
function updateBookmarkCount() {
    const cnt = document.getElementById('bookmark-count');
    if (!cnt) return;
    if (bookmarks.length > 0) {
        cnt.style.display = 'inline';
        cnt.textContent = bookmarks.length;
    } else {
        cnt.style.display = 'none';
    }
}
function toggleBookmarksPanel() {
    const panel = document.getElementById('bookmarks-panel');
    if (panel) panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
    renderBookmarks();
}
function renderBookmarks() {
    const list = document.getElementById('bookmarks-list');
    const empty = document.getElementById('no-bookmarks');
    if (!list) return;
    if (bookmarks.length === 0) {
        list.innerHTML = '';
        if (empty) empty.style.display = 'block';
        return;
    }
    if (empty) empty.style.display = 'none';
    list.innerHTML = bookmarks.map((b, i) => `
        <li>
            <button class="bookmark-del" onclick="removeBookmark(${i})">✕</button>
            <a href="${b.link}" target="_blank">${b.title}</a>
        </li>
    `).join('');
}
function removeBookmark(i) {
    bookmarks.splice(i, 1);
    localStorage.setItem('bookmarks', JSON.stringify(bookmarks));
    updateBookmarkCount();
    renderBookmarks();
}

// ===== CURRENCY =====
async function fetchCurrency() {
    try {
        const res = await fetch('https://api.exchangerate-api.com/v4/latest/EUR');
        const d = await res.json();
        const rates = d.rates;
        function show(id, chgId, rate, prev) {
            const el = document.getElementById(id);
            if (el) el.textContent = rate ? rate.toFixed(4) : '—';
            if (prev) {
                const diff = ((rate - prev) / prev * 100);
                const cel = document.getElementById(chgId);
                if (cel) {
                    cel.textContent = (diff >= 0 ? '▲ +' : '▼ ') + diff.toFixed(2) + '%';
                    cel.className = 'currency-change ' + (diff >= 0 ? 'up' : 'down');
                }
            }
        }
        show('rate-eurusd', 'chg-eurusd', rates.USD);
        show('rate-eurgbp', 'chg-eurgbp', rates.GBP);
        show('rate-eurchf', 'chg-eurchf', rates.CHF);

        const btcRes = await fetch('https://api.coinbase.com/v2/prices/BTC-USD/spot');
        const btcD = await btcRes.json();
        const btcRate = parseFloat(btcD.data.amount);
        const btcEl = document.getElementById('rate-btcusd');
        if (btcEl) btcEl.textContent = btcRate ? '$' + Math.round(btcRate).toLocaleString('el-GR') : '—';
    } catch {}
}

// ===== CALENDAR & NAMEDAYS =====
const GR_MONTHS = ['Ιανουάριος','Φεβρουάριος','Μάρτιος','Απρίλιος','Μάιος','Ιούνιος','Ιούλιος','Αύγουστος','Σεπτέμβριος','Οκτώβριος','Νοέμβριος','Δεκέμβριος'];
let selectedCalDay = null;

function getOrthodoxEaster(year) {
    const a = year % 4;
    const b = year % 7;
    const c = year % 19;
    const d = (19 * c + 15) % 30;
    const e = (2 * a + 4 * b - d + 34) % 7;
    const month = Math.floor((d + e + 114) / 31);
    const day = ((d + e + 114) % 31) + 1;
    const dt = new Date(year, month - 1, day);
    dt.setDate(dt.getDate() + 13);
    return dt;
}

function getGreekHolidays(year) {
    const holidays = {};
    function add(d, m, name) {
        const key = `${year}-${String(m).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
        holidays[key] = name;
    }
    add(1, 1, 'Πρωτοχρονιά (Επίσημη Αργία)');
    add(6, 1, 'Θεοφάνεια (Επίσημη Αργία)');
    add(25, 3, '25η Μαρτίου - Ευαγγελισμός (Εθνική Αργία)');
    add(1, 5, 'Εργατική Πρωτομαγιά (Επίσημη Αργία)');
    add(15, 8, 'Κοίμηση της Θεοτόκου (Επίσημη Αργία)');
    add(28, 10, '28η Οκτωβρίου - Ημέρα του Όχι (Εθνική Αργία)');
    add(25, 12, 'Χριστούγεννα (Επίσημη Αργία)');
    add(26, 12, 'Σύναξη της Θεοτόκου (2η ημέρα Χριστουγέννων)');

    const easter = getOrthodoxEaster(year);
    function addOffset(daysOffset, name) {
        const dt = new Date(easter);
        dt.setDate(dt.getDate() + daysOffset);
        const key = `${dt.getFullYear()}-${String(dt.getMonth()+1).padStart(2,'0')}-${String(dt.getDate()).padStart(2,'0')}`;
        holidays[key] = name;
    }
    addOffset(-48, 'Καθαρά Δευτέρα (Επίσημη Αργία)');
    addOffset(-2, 'Μεγάλη Παρασκευή (Επίσημη Αργία)');
    addOffset(-1, 'Μεγάλο Σάββατο');
    addOffset(0, 'Κυριακή του Πάσχα (Άγιο Πάσχα)');
    addOffset(1, 'Δευτέρα του Πάσχα (Επίσημη Αργία)');
    addOffset(50, 'Αγίου Πνεύματος (Επίσημη Αργία)');

    return holidays;
}

const GREEK_NAMEDAYS = {
    "01-01": "Βασίλειος, Βασιλική, Βάσω, Βασίλης",
    "01-02": "Σεραφείμ, Σιλβέστρος",
    "01-03": "Γενεβιέβη",
    "01-04": "Αναστασία, Θεόκτιστος",
    "01-06": "Φώτιος, Φωτεινή, Φάνης, Φανή, Ιορδάνης, Ιορδάνα, Ουρανία, Θεοφάνης",
    "01-07": "Ιωάννης, Ιωάννα, Γιάννης, Γιαννούλα, Πρόδρομος",
    "01-08": "Δόμνα, Παρθένα",
    "01-10": "Γρηγόριος, Νικάνωρ",
    "01-11": "Θεοδόσιος",
    "01-12": "Τατιανή",
    "01-14": "Νίνα",
    "01-17": "Αντώνιος, Αντωνία, Αντώνης, Τώνια",
    "01-18": "Αθανάσιος, Αθανασία, Θανάσης, Νάσος, Κύριλλος",
    "01-19": "Μακάριος, Ευγενία",
    "01-20": "Ευθύμιος, Ευθυμία, Θέμης",
    "01-21": "Μάξιμος, Νεόφυτος",
    "01-22": "Τιμόθεος, Αναστάσιος",
    "01-24": "Ξένη, Ξενοφών",
    "01-25": "Γρηγόριος, Γρηγορία, Μαργαρίτα",
    "01-26": "Ξενοφών, Μαρία",
    "01-30": "Τριών Ιεραρχών (Αρχοντής, Αρχοντία)",
    "02-01": "Τρύφων, Τρύφωνας",
    "02-02": "Υπαπαντή του Χριστού, Μαρία, Δέσποινα",
    "02-03": "Συμεών, Συμεωνία, Σίμος",
    "02-05": "Αγάθη, Αγαθή",
    "02-06": "Φώτιος, Φωτεινή",
    "02-10": "Χαράλαμπος, Χαραλαμπία, Μπάμπης, Χαρά",
    "02-11": "Βλάσιος, Βλάσης",
    "02-13": "Πρίσκιλλα, Ακύλας",
    "02-14": "Βαλεντίνος, Βαλεντίνη",
    "02-17": "Θεόδωρος, Θεοδώρα",
    "02-21": "Ευσταθία",
    "03-01": "Ευδοκία, Ευδοξία",
    "03-09": "Τεσσαράκοντα Μαρτύρων (Σαράντης, Σαραντούλα)",
    "03-17": "Αλέξιος, Αλεξία",
    "03-19": "Χρύσανθος, Χρυσάνθη",
    "03-25": "Ευάγγελος, Ευαγγελία, Βαγγέλης, Βαγγελιώ, Άγγελος, Αγγελική",
    "05-01": "Ιερεμίας, Ταμάρα",
    "05-02": "Αθανάσιος (Ανακομιδή λειψάνων)",
    "05-03": "Μαύρα, Τιμόθεος",
    "05-04": "Πελαγία",
    "05-05": "Ειρήνη, Ρένα, Ειρηναίος",
    "05-08": "Ιωάννης Θεολόγος, Θεολόγος",
    "05-09": "Χριστόφορος, Χριστοφορία",
    "05-10": "Σίμων, Σιμώνη",
    "05-11": "Μεθόδιος, Ολυμπία",
    "05-12": "Επιφάνιος",
    "05-13": "Γλυκερία",
    "05-14": "Ισίδωρος",
    "05-15": "Αχίλλιος, Αχινέας",
    "05-21": "Κωνσταντίνος, Κωνσταντίνα, Ελένη, Κώστας, Ντίνος, Έλενα",
    "05-29": "Θεοδοσία",
    "06-08": "Καλλιόπη, Πόπη",
    "06-11": "Βαρνάβας, Βαρθολομαίος",
    "06-24": "Ιωάννης Βαπτιστής (Γενέθλιον)",
    "06-29": "Πέτρος, Παύλος, Πετρούλα, Πωλίνα",
    "06-30": "Απόστολοι (Σύναξη Δώδεκα Αποστόλων)",
    "07-01": "Κοσμάς, Δαμιανός, Ανάργυρος, Αναργυρούλα",
    "07-03": "Υάκινθος",
    "07-07": "Κυριακή, Κική, Κορίνα",
    "07-08": "Προκόπιος, Προκοπία",
    "07-11": "Ευφημία",
    "07-17": "Μαρίνα, Μαρινέλλα",
    "07-20": "Ηλίας, Ηλιάνα, Λίας",
    "07-22": "Μαρία Μαγδαληνή, Μενέλαος",
    "07-25": "Άννα (Κοίμηση Αγίας Άννας)",
    "07-26": "Παρασκευή, Εύη, Βούλα, Παρασκευάς",
    "07-27": "Παντελεήμων, Παντελής, Παντελίτσα",
    "07-28": "Ειρήνη Χρυσοβαλάντου, Βαλάντης, Βαλάντω",
    "08-06": "Σωτήριος, Σωτηρία, Σωτήρης, Σώτος",
    "08-15": "Μαρία, Μάριος, Παναγιώτης, Παναγιώτα, Δέσποινα, Πάνος, Γιώτα",
    "08-16": "Σταμάτης, Σταματία, Σταματίνα, Απόστολος",
    "08-18": "Αρσένιος, Αρσινόη",
    "08-20": "Ηλιόδωρος, Σαμουήλ",
    "08-23": "Παναγία (Εννιάμερα Θεοτόκου)",
    "08-24": "Κοσμάς ο Αιτωλός",
    "08-25": "Τίτος, Τίτα",
    "08-26": "Αδριανός, Ναταλία",
    "08-27": "Φανούριος, Φανουρία",
    "08-30": "Αλέξανδρος, Αλεξάνδρα, Αλέξης, Αλεξία",
    "08-31": "Ζώνης, Αγαθόνικος",
    "09-01": "Συμεών, Αθηνά, Αγγελική, Ασπασία, Ακριβή, Ευανθία, Καλλιρόη, Κλειώ, Μαργαρίτα, Μαριάνθη, Πηνελόπη",
    "09-05": "Ζαχαρίας, Ελισάβετ",
    "09-08": "Δέσποινα, Μαρία (Γενέσιον Θεοτόκου)",
    "09-09": "Ιωακείμ, Άννα",
    "09-14": "Σταύρος, Σταυρούλα, Σταυριανός",
    "09-15": "Νικήτας, Νικήτη",
    "09-17": "Σοφία, Πίστη, Ελπίδα, Αγάπη, Σόνια",
    "09-20": "Ευστάθιος, Ευσταθία, Στάθης",
    "09-24": "Θέκλα, Μυρσίνη, Μυρτώ",
    "09-29": "Κυριάκος, Κυριακή",
    "10-03": "Διονύσιος Αρεοπαγίτης, Διονύσης, Διονυσία",
    "10-06": "Θωμάς, Θωμαΐς",
    "10-07": "Σέργιος, Βάκχος",
    "10-18": "Λουκάς, Λουκία",
    "10-20": "Αρτέμιος, Άρτεμις, Γεράσιμος, Δήμητρα",
    "10-26": "Δημήτριος, Δημητρία, Δημήτρης, Δήμητρα, Μίμης",
    "11-01": "Κοσμάς, Δαμιανός, Ανάργυρος, Δαυίδ",
    "11-08": "Μιχαήλ, Γαβριήλ, Άγγελος, Αγγελική, Μιχάλης, Ταξιάρχης, Σταμάτης",
    "11-09": "Νεκτάριος, Νεκταρία",
    "11-11": "Μηνάς, Βίκτωρ, Βικτωρία",
    "11-14": "Φίλιππος, Φιλιππία",
    "11-21": "Μαρία, Δέσποινα, Μαίρη (Εισόδια της Θεοτόκου)",
    "11-25": "Αικατερίνη, Κατερίνα, Κατίνα, Μερκούριος",
    "11-26": "Στυλιανός, Στυλιανή, Στέλιος, Στέλλα",
    "11-30": "Ανδρέας, Ανδριανή, Ανδριάνα",
    "12-04": "Βαρβάρα, Σάββας",
    "12-05": "Σάββας, Σαββούλα",
    "12-06": "Νικόλαος, Νικολέτα, Νίκος, Νικολής",
    "12-09": "Άννα (Σύλληψη Αγίας Άννης)",
    "12-12": "Σπυρίδων, Σπυριδούλα, Σπύρος",
    "12-15": "Ελευθέριος, Ελευθερία, Λευτέρης",
    "12-17": "Διονύσιος, Δανιήλ",
    "12-20": "Ιγνάτιος",
    "12-24": "Ευγενία, Ευγένιος",
    "12-25": "Χρήστος, Χριστίνα, Χρύσα, Χρυσάνθη, Εμμανουήλ, Μανώλης",
    "12-26": "Εμμανουήλ, Μανώλης (2η ημέρα Χριστουγέννων)",
    "12-27": "Στέφανος, Στεφανία, Στέφος"
};

function getNamedayForDate(y, m, d) {
    const mmdd = `${String(m+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
    let nameday = GREEK_NAMEDAYS[mmdd] || '';

    const easter = getOrthodoxEaster(y);
    const easterMon = new Date(easter); easterMon.setDate(easterMon.getDate() + 1);
    const stGeorgeStandard = new Date(y, 3, 23);

    if (m === 3 && d === 23) {
        if (easter > stGeorgeStandard) {
            nameday = '';
        } else {
            nameday = "Γεώργιος, Γεωργία, Γιώργος, Γιωργία";
        }
    }
    if (m === easterMon.getMonth() && d === easterMon.getDate() && easter > stGeorgeStandard) {
        nameday = "Γεώργιος, Γεωργία, Γιώργος, Γιωργία (Μετάθεση Αγ. Γεωργίου)";
    }

    return nameday;
}

function renderCalendar() {
    const y = calDate.getFullYear();
    const m = calDate.getMonth();
    const today = new Date();
    const calTitleEl = document.getElementById('cal-title');
    if (calTitleEl) calTitleEl.textContent = GR_MONTHS[m] + ' ' + y;

    const firstDay = new Date(y, m, 1).getDay();
    const daysInMonth = new Date(y, m + 1, 0).getDate();
    const daysInPrev = new Date(y, m, 0).getDate();

    const holidays = getGreekHolidays(y);

    let html = '';
    for (let i = firstDay - 1; i >= 0; i--) {
        html += `<div class="cal-day other-month">${daysInPrev - i}</div>`;
    }
    for (let d = 1; d <= daysInMonth; d++) {
        const isToday = d === today.getDate() && m === today.getMonth() && y === today.getFullYear();
        const dateKey = `${y}-${String(m+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
        const isHoliday = !!holidays[dateKey];
        const nameday = getNamedayForDate(y, m, d);
        const hasEvent = isHoliday || !!nameday;
        const isSelected = selectedCalDay === dateKey;

        const classes = [
            'cal-day',
            isToday ? 'today' : '',
            isHoliday ? 'holiday' : '',
            hasEvent ? 'has-event' : '',
            isSelected ? 'selected' : ''
        ].filter(Boolean).join(' ');

        html += `<div class="${classes}" onclick="selectDay(${d})">${d}</div>`;
    }
    const total = firstDay + daysInMonth;
    const remaining = total % 7 === 0 ? 0 : 7 - (total % 7);
    for (let d = 1; d <= remaining; d++) {
        html += `<div class="cal-day other-month">${d}</div>`;
    }
    const daysEl = document.getElementById('cal-days');
    if (daysEl) daysEl.innerHTML = html;
}

function calNav(dir) {
    calDate.setMonth(calDate.getMonth() + dir);
    renderCalendar();
}

function selectDay(d) {
    const y = calDate.getFullYear();
    const m = calDate.getMonth();
    selectedCalDay = `${y}-${String(m+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
    
    renderCalendar();

    const panel = document.getElementById('cal-details-panel');
    const dateTitle = document.getElementById('cal-detail-date');
    const holidayItem = document.getElementById('cal-detail-holiday');
    const holidayTitle = document.getElementById('cal-holiday-title');
    const namedayItem = document.getElementById('cal-detail-nameday');
    const namedayText = document.getElementById('cal-nameday-text');

    if (!panel) return;
    
    const holidays = getGreekHolidays(y);
    const holiday = holidays[selectedCalDay];
    const nameday = getNamedayForDate(y, m, d);

    panel.style.display = 'block';
    dateTitle.textContent = `${d} ${GR_MONTHS[m]} ${y}`;

    if (holiday) {
        holidayItem.style.display = 'flex';
        holidayTitle.textContent = holiday;
    } else {
        holidayItem.style.display = 'none';
    }

    if (nameday) {
        namedayItem.style.display = 'flex';
        namedayText.textContent = nameday;
    } else {
        namedayItem.style.display = 'none';
    }
}

// ===== STOCKS WIDGET =====
const STOCKS = [
    { symbol: '^ATH',    flag: '🇬🇷', label: 'ΧΑΑ',        name: 'Γεν. Δείκτης Αθηνών' },
    { symbol: '^spx',    flag: '🇺🇸', label: 'S&P 500',    name: 'Standard & Poor\'s 500' },
    { symbol: '^dji',    flag: '🇺🇸', label: 'Dow Jones',  name: 'Dow Jones Industrial' },
    { symbol: '^ndq',    flag: '🇺🇸', label: 'Nasdaq',     name: 'Nasdaq Composite' },
    { symbol: '^ukx',    flag: '🇬🇧', label: 'FTSE 100',   name: 'London Stock Exchange' },
    { symbol: '^dax',    flag: '🇩🇪', label: 'DAX',        name: 'Frankfurt Stock Exchange' },
    { symbol: '^nkx',    flag: '🇯🇵', label: 'Nikkei 225', name: 'Tokyo Stock Exchange' },
];

async function fetchStocks() {
    const container = document.getElementById('stocks-grid');
    if (!container) return;
    container.innerHTML = '<div class="stocks-grid">' +
        STOCKS.map(() => '<div class="stock-skeleton"></div>').join('') +
        '</div>';

    try {
        const res = await fetch('./stocks-proxy.php');
        const data = await res.json();

        let html = '<div class="stocks-grid">';
        STOCKS.forEach(stock => {
            const q = data[stock.symbol];
            if (!q) {
                html += `<div class="stock-card">
                    <div class="stock-left">
                        <span class="stock-flag">${stock.flag}</span>
                        <div><div class="stock-label">${stock.label}</div>
                        <div class="stock-name">${stock.name}</div></div>
                    </div>
                    <div class="stock-right"><div class="stock-price">—</div></div>
                </div>`;
                return;
            }
            const price    = q.price    != null ? q.price.toLocaleString('el-GR', {minimumFractionDigits:2, maximumFractionDigits:2}) : '—';
            const change   = q.change   != null ? (q.change >= 0 ? '+' : '') + q.change.toFixed(2) : '';
            const changePct = q.changePct != null ? (q.changePct >= 0 ? '+' : '') + q.changePct.toFixed(2) + '%' : '';
            const dir = q.changePct > 0 ? 'up' : q.changePct < 0 ? 'down' : 'flat';
            const arrow = dir === 'up' ? '▲' : dir === 'down' ? '▼' : '—';

            html += `<div class="stock-card">
                <div class="stock-left">
                    <span class="stock-flag">${stock.flag}</span>
                    <div>
                        <div class="stock-label">${stock.label}</div>
                        <div class="stock-name">${stock.name}</div>
                    </div>
                </div>
                <div class="stock-right">
                    <div class="stock-price">${price}</div>
                    <div class="stock-change ${dir}">${arrow} ${change} (${changePct})</div>
                </div>
            </div>`;
        });
        html += '</div>';
        container.innerHTML = html;

    } catch(e) {
        container.innerHTML = '<div style="color:var(--text3);font-size:0.82em;padding:8px;">⚠️ Αποτυχία φόρτωσης</div>';
    }
}

// ===== CUSTOM ASSETS WIDGET =====
const CUSTOM_ASSETS = [
    { key: 'VUAA',     flag: '🇪🇺', label: 'VUAA',     name: 'Vanguard S&P 500 ETF' },
    { key: 'O.US',     flag: '🇺🇸', label: 'O.US',     name: 'Realty Income Corp' },
    { key: 'DGRW.EU',  flag: '🇪🇺', label: 'DGRW.EU', name: 'WisdomTree US Quality Div' },
    { key: 'PPC.GR',   flag: '🇬🇷', label: 'PPC.GR',   name: 'ΔΕΗ S.A.' },
    { key: 'MYTIL.GR', flag: '🇬🇷', label: 'MYTIL.GR', name: 'Metlen (Μυτιληναίος)' },
    { key: 'BTC',      flag: '₿',  label: 'BTC',      name: 'Bitcoin' },
    { key: 'ETH',      flag: 'Ξ',  label: 'ETH',      name: 'Ethereum' },
];

async function fetchCustomAssets() {
    const container = document.getElementById('custom-assets-grid');
    if (!container) return;
    container.innerHTML = '<div class="stocks-grid">' +
        CUSTOM_ASSETS.map(() => '<div class="stock-skeleton"></div>').join('') +
        '</div>';

    try {
        const res = await fetch('./assets-proxy.php');
        const data = await res.json();

        let html = '<div class="stocks-grid">';
        CUSTOM_ASSETS.forEach(asset => {
            const q = data[asset.key];
            if (!q) {
                html += `<div class="stock-card">
                    <div class="stock-left">
                        <span class="stock-flag">${asset.flag}</span>
                        <div><div class="stock-label">${asset.label}</div>
                        <div class="stock-name">${asset.name}</div></div>
                    </div>
                    <div class="stock-right"><div class="stock-price">—</div></div>
                </div>`;
                return;
            }
            const sign = q.currencySign || (q.currency === 'EUR' ? '€' : '$');
            const price = q.price != null ? sign + q.price.toLocaleString('el-GR', {minimumFractionDigits:2, maximumFractionDigits:2}) : '—';
            const change = q.change != null ? (q.change >= 0 ? '+' : '') + q.change.toFixed(2) : '';
            const changePct = q.changePct != null ? (q.changePct >= 0 ? '+' : '') + q.changePct.toFixed(2) + '%' : '';
            const dir = q.changePct > 0 ? 'up' : q.changePct < 0 ? 'down' : 'flat';
            const arrow = dir === 'up' ? '▲' : dir === 'down' ? '▼' : '—';

            html += `<div class="stock-card">
                <div class="stock-left">
                    <span class="stock-flag">${asset.flag}</span>
                    <div>
                        <div class="stock-label">${asset.label}</div>
                        <div class="stock-name">${asset.name}</div>
                    </div>
                </div>
                <div class="stock-right">
                    <div class="stock-price">${price}</div>
                    <div class="stock-change ${dir}">${arrow} ${change} (${changePct})</div>
                </div>
            </div>`;
        });
        html += '</div>';
        container.innerHTML = html;

    } catch(e) {
        container.innerHTML = '<div style="color:var(--text3);font-size:0.82em;padding:8px;">⚠️ Αποτυχία φόρτωσης</div>';
    }
}

// ===== SALARY CALCULATOR =====
function calcMisthos() {
    const currentYear = new Date().getFullYear();
    const miktaEl    = document.getElementById('calc_mikta');
    const efkaEl     = document.getElementById('calc_efka');
    const paidiaEl   = document.getElementById('calc_paidia');
    const birthEl    = document.getElementById('calc_birth');

    if (!miktaEl) return;

    const mikta    = parseFloat(miktaEl.value)  || 0;
    const efka_pct = parseFloat(efkaEl.value)   || 0;
    const paidia   = parseInt(paidiaEl.value)   || 0;
    const birth    = parseInt(birthEl.value)    || 2000;

    const eisf_ergaz  = mikta * efka_pct / 100;
    const age         = currentYear - birth;
    const forologiteo = (mikta - eisf_ergaz) * 14;

    let syntel1 = (age <= 25 || paidia >= 4) ? 0 : 9;
    const forol1 = Math.min(forologiteo, 10000);
    const foros1 = forol1 * syntel1 / 100;

    let syntel2;
    if      (age <= 25)    syntel2 = 0;
    else if (age <= 30)    syntel2 = 9;
    else if (paidia === 0) syntel2 = 20;
    else if (paidia === 1) syntel2 = 18;
    else if (paidia === 2) syntel2 = 16;
    else if (paidia === 3) syntel2 = 9;
    else                   syntel2 = 0;
    const forol2 = forologiteo > 10000 ? Math.min(forologiteo - 10000, 10000) : 0;
    const foros2 = forol2 * syntel2 / 100;

    let syntel3;
    if      (paidia === 0) syntel3 = 26;
    else if (paidia === 1) syntel3 = 24;
    else if (paidia === 2) syntel3 = 22;
    else if (paidia === 3) syntel3 = 20;
    else if (paidia === 4) syntel3 = 18;
    else if (paidia === 5) syntel3 = 16;
    else                   syntel3 = 14;
    const forol3 = forologiteo > 20000 ? Math.min(forologiteo - 20000, 10000) : 0;
    const foros3 = forol3 * syntel3 / 100;

    const forol4 = forologiteo > 30000 ? Math.min(forologiteo - 30000, 10000) : 0;
    const foros4 = forol4 * 34 / 100;

    const forol5 = forologiteo > 40000 ? Math.min(forologiteo - 40000, 20000) : 0;
    const foros5 = forol5 * 39 / 100;

    const forol6 = forologiteo > 60000 ? forologiteo - 60000 : 0;
    const foros6 = forol6 * 44 / 100;

    const meioseis = [777, 900, 1120, 1340, 1560, 1780];
    const meiosi_1 = meioseis[Math.min(paidia, 5)];
    const meiosi_2 = forologiteo > 12000 ? ((forologiteo - 12000) / 1000) * -20 : 0;
    const meiosi_tel = meiosi_1 + meiosi_2;

    const totalForos = foros1 + foros2 + foros3 + foros4 + foros5 + foros6;
    const fmy_year   = Math.max(0, totalForos - meiosi_tel);
    const fmy        = fmy_year / 14;
    const katharες   = mikta - eisf_ergaz - fmy;

    function fmt(n) {
        return n.toLocaleString('el-GR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
    }
    const fmyEl = document.getElementById('calc_fmy');
    const katharesEl = document.getElementById('calc_katharες');
    if (fmyEl) fmyEl.textContent = fmt(fmy);
    if (katharesEl) katharesEl.textContent = fmt(katharες);
}

// ===== INITIALIZE COMMON SIDEBAR WIDGETS =====
function initCommonWidgets() {
    updateBookmarkCount();
    renderBookmarks();
    fetchCurrency();
    setInterval(fetchCurrency, 5 * 60 * 1000);
    renderCalendar();
    selectDay(new Date().getDate());
    fetchStocks();
    setInterval(fetchStocks, 5 * 60 * 1000);
    fetchCustomAssets();
    setInterval(fetchCustomAssets, 5 * 60 * 1000);
}

// ===== ENTRY POINT =====
function initApp() {
    initCommonWidgets();
    fetchFeeds();
    setInterval(fetchFeeds, REFRESH_TIME);
}
