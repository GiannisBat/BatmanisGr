// ===== MAIN PAGE CONFIG (index.php) =====
// Επιλεγμένες πηγές ειδήσεων για γρήγορη ενημέρωση στην αρχική σελίδα
const newsSources = [
    { url: 'https://www.ertnews.gr/feed', cat: 'ellada' },
    { url: 'https://news.google.com/rss?hl=el&gl=GR&ceid=GR:el', cat: 'ellada' },
    { url: 'https://www.documentonews.gr/feed/', cat: 'ellada' },
    { url: 'https://defencereview.gr/feed/', cat: 'ellada' },
    { url: 'https://www.taxheaven.gr/bibliothiki/soft/xml/soft_new.xml', cat: 'oikonomia' },
    { url: 'https://www.naftemporiki.gr/feed', cat: 'oikonomia' },
    { url: 'https://lionnews.gr/blog/?feed=rss2&cat=0', cat: 'topika' },
    { url: 'https://www.ertnews.gr/news/perifereiakoi-stathmoi/seres/feed/', cat: 'topika' },
    { url: 'https://www.ertsports.gr/feed', cat: 'sport' },
    { url: 'https://www.esos.gr/rss.xml', cat: 'ekpaideusi' },
    { url: 'https://saekser.gr/?feed=rss2&cat=0', cat: 'ekpaideusi' },
    { url: 'https://dipe.ser.sch.gr/feed', cat: 'ekpaideusi' },
    { url: 'http://www.goutouloudi.gr/?feed=rss2&cat=0', cat: 'ekpaideusi' },
    { url: 'https://3dim-nigrit.ser.sch.gr/?feed=rss2&cat=0', cat: 'ekpaideusi' },
    { url: 'https://www.agronews.gr/rss', cat: 'agrotika' },
    { url: 'https://techmaniacs.gr/feed/', cat: 'tech' },
    { url: 'https://www.pcsteps.gr/feed/', cat: 'tech' },	
    { url: 'https://feeds.nbcnews.com/nbcnews/public/news', cat: 'diethni' }
];

initApp();
