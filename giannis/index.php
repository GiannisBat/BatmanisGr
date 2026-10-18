<?php $currentCategory = $currentCategory ?? 'all'; ?>
<!DOCTYPE html>
<html lang="el" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giannis - batmanis.gr</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- ===== HEADER ===== -->
<header class="site-header">
    <div class="site-logo">batmanis<span>.gr</span></div>
    <div class="header-controls">
        <div class="view-toggle">
            <button class="view-btn active" id="btn-grid" title="Grid προβολή" onclick="setView('grid')">⊞</button>
            <button class="view-btn" id="btn-list" title="List προβολή" onclick="setView('list')">☰</button>
        </div>
        <button class="btn-icon" onclick="toggleDark()" id="dark-toggle" title="Dark/Light mode">🌙</button>
        <button class="btn-icon" id="bookmarks-toggle" onclick="toggleBookmarksPanel()" title="Αποθηκευμένα">🔖 <span id="bookmark-count" style="display:none;background:var(--badge-bg);color:#fff;border-radius:10px;padding:1px 6px;font-size:0.75em;margin-left:3px;"></span></button>
    </div>
</header>

<!-- ===== SEARCH + FILTERS ===== -->
<div class="search-bar-wrap">
    <div class="search-input-wrap">
        <span class="icon">🔍</span>
        <input type="text" id="search-input" placeholder="Αναζήτηση σε όλα τα άρθρα..." oninput="doSearch(this.value)">
        <button id="search-clear" onclick="clearSearch()">✕</button>
    </div>
    <div class="filter-chips" id="filter-chips">
        <a href="index.php" class="chip <?= $currentCategory === 'all' ? 'active' : '' ?>" data-cat="all">📰 Όλα</a>
        <a href="ellada.php" class="chip <?= $currentCategory === 'ellada' ? 'active' : '' ?>" data-cat="ellada">🇬🇷 Ελλάδα</a>
        <a href="oikonomia.php" class="chip <?= $currentCategory === 'oikonomia' ? 'active' : '' ?>" data-cat="oikonomia">💼 Οικονομία</a>
        <a href="topika.php" class="chip <?= $currentCategory === 'topika' ? 'active' : '' ?>" data-cat="topika">📍 Τοπικά Σερρών</a>
        <a href="tech.php" class="chip <?= $currentCategory === 'tech' ? 'active' : '' ?>" data-cat="tech">💻 Τεχνολογία</a>
        <a href="sport.php" class="chip <?= $currentCategory === 'sport' ? 'active' : '' ?>" data-cat="sport">⚽ Αθλητικά</a>
        <a href="ekpaideusi.php" class="chip <?= $currentCategory === 'ekpaideusi' ? 'active' : '' ?>" data-cat="ekpaideusi">🎓 Εκπαίδευση</a>
        <a href="agrotika.php" class="chip <?= $currentCategory === 'agrotika' ? 'active' : '' ?>" data-cat="agrotika">🌾 Αγροτικά</a>
        <a href="diethni.php" class="chip <?= $currentCategory === 'diethni' ? 'active' : '' ?>" data-cat="diethni">🌍 Διεθνή</a>
    </div>
</div>

<!-- ===== PAGE LAYOUT ===== -->
<div class="page-wrap">

    <!-- MAIN NEWS COLUMN -->
    <main class="main-col">
        <div class="status-bar">
            <span class="last-update" id="last-update">Φόρτωση ειδήσεων...</span>
            <button class="refresh-btn" id="refresh-btn" onclick="fetchFeeds()">↻ Ανανέωση</button>
        </div>
        <div id="main-grid" class="news-grid"></div>
    </main>

    <!-- SIDEBAR -->
    <aside class="side-col" id="side-col">

        <!-- BOOKMARKS PANEL (hidden by default) -->
        <div class="side-section" id="bookmarks-panel" style="display:none;">
            <div class="side-title">🔖 Αποθηκευμένα άρθρα</div>
            <ul class="bookmarks-list" id="bookmarks-list"></ul>
            <p class="no-bookmarks" id="no-bookmarks" style="display:none;">Δεν έχεις αποθηκεύσει άρθρα ακόμα.</p>
        </div>

        <!-- WINDY MAP -->
        <div class="side-section">
            <div class="side-title">🌬 Χάρτης Καιρού</div>
            <div class="iframe-wrap">
                <iframe width="100%" height="350" src="https://embed.windy.com/embed.html?type=map&location=coordinates&metricRain=mm&metricTemp=°C&metricWind=km/h&zoom=7&overlay=rain&product=ecmwf&level=surface&lat=40.221&lon=22.643" frameborder="0"></iframe>
            </div>
        </div>

        <!-- WEATHER -->
        <div class="side-section">
            <div class="side-title">🌤 Καιρός</div>
            <div class="iframe-wrap">
                <iframe scrolling="no" frameborder="0" allowtransparency="true" src="https://gr.k24.net/widgets/weather_w7.aspx?p=60" style="width:100%;height:150px;"></iframe>
            </div>
            <div class="iframe-wrap">
                <iframe scrolling="no" frameborder="0" allowtransparency="true" src="https://gr.k24.net/widgets/weather_w7.aspx?p=3132" style="width:100%;height:150px;"></iframe>
            </div>
            <div class="iframe-wrap">
                <iframe scrolling="no" frameborder="0" allowtransparency="true" src="https://gr.k24.net/widgets/weather_w7.aspx?p=3466" style="width:100%;height:150px;"></iframe>
            </div>
        </div>

        <!-- MINI CALENDAR -->
        <div class="side-section">
            <div class="side-title">📅 Ημερολόγιο &amp; Αργίες</div>
            <div class="mini-cal">
                <div class="cal-header">
                    <button class="cal-nav-btn" onclick="calNav(-1)">‹</button>
                    <span class="cal-title" id="cal-title"></span>
                    <button class="cal-nav-btn" onclick="calNav(1)">›</button>
                </div>
                <div class="cal-days-header">
                    <span>Κυ</span><span>Δε</span><span>Τρ</span><span>Τε</span><span>Πε</span><span>Πα</span><span>Σα</span>
                </div>
                <div class="cal-days" id="cal-days"></div>
                <div class="cal-details-panel" id="cal-details-panel" style="display:none;">
                    <div class="cal-detail-date" id="cal-detail-date"></div>
                    <div class="cal-detail-item holiday" id="cal-detail-holiday" style="display:none;">
                        <span class="icon">🏛️</span>
                        <div><strong id="cal-holiday-title"></strong></div>
                    </div>
                    <div class="cal-detail-item nameday" id="cal-detail-nameday" style="display:none;">
                        <span class="icon">🎉</span>
                        <div><strong>Γιορτάζουν:</strong> <span id="cal-nameday-text"></span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SALARY CALCULATOR -->
        <div class="side-section">
            <div class="side-title">🧮 Υπολογισμός Αποδοχών</div>
            <table class="calc-table">
                <tr>
                    <td>Μικτές αποδοχές</td>
                    <td><input class="calc-input" type="number" id="calc_mikta" value="920.00"></td>
                </tr>
                <tr>
                    <td>ΕΦΚΑ εργαζόμενου (%)</td>
                    <td><input class="calc-input" type="text" id="calc_efka" value="13.37"></td>
                </tr>
                <tr>
                    <td>Αρ. παιδιών</td>
                    <td><input class="calc-input" type="number" id="calc_paidia" value="0"></td>
                </tr>
                <tr>
                    <td>Έτος γέννησης</td>
                    <td><input class="calc-input" type="number" id="calc_birth" value="2000"></td>
                </tr>
                <tr>
                    <td>ΦΜΥ ανά μισθό</td>
                    <td class="calc-result" id="calc_fmy">0,00 €</td>
                </tr>
                <tr>
                    <td>Καθαρές αποδοχές</td>
                    <td class="calc-result" id="calc_katharες">0,00 €</td>
                </tr>
            </table>
            <button class="calc-submit" onclick="calcMisthos()">Υπολογισμός →</button>
        </div>		



        <!-- RADIO -->
        <div class="side-section">
            <div class="side-title">📻 Ραδιόφωνο</div>
            <div class="iframe-wrap">
                <iframe src="https://tunein.com/embed/player/s234853/" style="width:100%;height:100px;" scrolling="no" frameborder="no"></iframe>
            </div>
            <div class="iframe-wrap">
                <iframe src="https://tunein.com/embed/player/s49386/" style="width:100%; height:100px;" scrolling="no" frameborder="no"></iframe>
            </div>
            <div class="iframe-wrap">
                <iframe src="https://tunein.com/embed/player/s126371/" style="width:100%;height:100px;" scrolling="no" frameborder="no"></iframe>
            </div>
        </div>

        <!-- FRONT PAGES -->
        <div class="side-section">
            <div class="side-title">📰 Πρωτοσέλιδα</div>
            <div class="iframe-wrap">
                <iframe src="https://www.frontpages.gr/ticker.php?category=7&c=F2F2F2&w=400&h=600&t=1&e=0" width="400" height="600" scrolling="no" frameborder="0"></iframe>
            </div>
        </div>

        <!-- LINKS -->
        <div class="side-section">
            <div class="side-title">🔗 Σύνδεσμοι</div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <a href="https://payroll-team.gr/" target="_blank"><img src="./logo200x200.png" style="width:100%;max-width:200px;border-radius:8px;"></a>
                <a href="https://www.goutouloudi.gr/" target="_blank"><img src="./Goutouloudi-logo1200x200.png" style="width:100%;border-radius:8px;"></a>
                <a href="https://3dim-nigrit.ser.sch.gr/" target="_blank"><img src="./3dsn-logo.png" style="width:100%;border-radius:8px;"></a>
            </div>
        </div>

        <div>
            <iframe width="400" height="225" src="https://www.meteology.gr/cam/anostavros/anostavros.png?t="></iframe>

        <!-- CURRENCY -->
        <div class="side-section">
            <div class="side-title">💱 Ισοτιμίες</div>
            <div class="currency-grid" id="currency-grid">
                <div class="currency-card"><div class="currency-pair">EUR/USD</div><div class="currency-rate" id="rate-eurusd">—</div><div class="currency-change" id="chg-eurusd"></div></div>
                <div class="currency-card"><div class="currency-pair">EUR/GBP</div><div class="currency-rate" id="rate-eurgbp">—</div><div class="currency-change" id="chg-eurgbp"></div></div>
                <div class="currency-card"><div class="currency-pair">EUR/CHF</div><div class="currency-rate" id="rate-eurchf">—</div><div class="currency-change" id="chg-eurchf"></div></div>
                <div class="currency-card"><div class="currency-pair">BTC/USD</div><div class="currency-rate" id="rate-btcusd">—</div><div class="currency-change" id="chg-btcusd"></div></div>
            </div>
        </div>

        <!-- ΧΡΗΜΑΤΙΣΤΗΡΙΑ -->
        <div class="side-section">
            <div class="side-title">📈 Χρηματιστήρια</div>
            <div id="stocks-grid"></div>
            <div style="font-size:0.7em;color:var(--text3);margin-top:6px;">
                ⏱ Καθυστέρηση ~15 λεπτών
            </div>
        </div>

        <!-- ΜΕΤΟΧΕΣ, ETF & CRYPTO -->
        <div class="side-section">
            <div class="side-title">📊 Μετοχές, ETF &amp; Crypto</div>
            <div id="custom-assets-grid"></div>
            <div style="font-size:0.7em;color:var(--text3);margin-top:6px;">
                ⏱ Καθυστέρηση ~15 λεπτών
            </div>
        </div>


        <!-- TV PROGRAM -->
        <div class="side-section">
            <div class="side-title">📺 Πρόγραμμα Τηλεόρασης</div>
            <div class="iframe-wrap">
                <iframe id="widget_frame" src="//programmatileorasis.gr/widget_horizontal_show.php?width=4" marginwidth=0 marginheight=0 frameborder=0 border=0 style="border:0;margin:0;padding:0;overflow:hidden;width:100%;height:439px;"></iframe>
            </div>
        </div>

        <!-- SPORTS -->
        <div class="side-section">
            <div class="side-title">⚽ Αποτελέσματα</div>
            <div class="iframe-wrap">
                <iframe src="https://ticker.agones.gr/widget_vscroll_show.php?width=120%25&height=400&duration=5000" width="120%" height="400" frameborder="0" style="border:0;"></iframe>
            </div>
        </div>

        <!-- EORTOLOGIO -->
        <div class="side-section">
            <div class="side-title">🎉 Εορτολόγιο</div>
            <div class="iframe-wrap">
                <iframe src="https://www.eortologio.net/widget.php?color=555555&color2=feca54&bgcolor=f8f9db&width=100%25&height=400" width="100%" height="400" scrolling="no" frameborder="0"></iframe>
            </div>
        </div>

    </aside>
</div>

<footer>
    batmanis.gr &copy; <?= date('Y') ?> — Προσωπική Αρχική Σελίδα
</footer>

<script>const INITIAL_CATEGORY = <?= json_encode($currentCategory) ?>;</script>
<script src="app-core.js"></script>
<script src="<?= htmlspecialchars($currentJsScript ?? 'main.js') ?>"></script>
</body>
</html>
