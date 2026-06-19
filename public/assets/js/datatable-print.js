/*!
 * SmartERP — DataTable Professional Print
 * =========================================
 * Standardises the DataTables "Print" button output with a consistent,
 * enterprise-grade layout that matches the system's Blade print design tokens.
 *
 * Usage: included globally via index.blade.php (before custom.js).
 * API:   window.dtPrintCustomize(win, title)
 *
 * Automatically pre-fetches company data on page load so the customize
 * callback can run synchronously when the user clicks Print.
 */
(function (window) {
    'use strict';

    /* ── Design tokens (must match print/layout.blade.php) ──────────── */
    var ACCENT   = '#1f3a5f';
    var INK      = '#1a1d21';
    var MUTED    = '#6b707a';
    var HAIRLINE = '#e6e9ee';
    var ROW_ALT  = '#fafbfc';
    var SANS     = '"Arial","Helvetica Neue",Helvetica,sans-serif';
    var MONO     = '"Courier New",Courier,monospace';

    /* ── Company data cache ──────────────────────────────────────────── */
    var _co = null;

    /* Start fetching immediately on script load.
       By the time the user finds and clicks the Print button, the
       ~100 ms local fetch will have resolved. */
    fetch('/company/data')
        .then(function (r) { return r.json(); })
        .then(function (d) { _co = d; })
        .catch(function () { /* leave null; fallback used at print time */ });

    function getCompany() {
        return _co || {
            name:        'SmartERP',
            address:     '',
            phone:       '',
            email:       '',
            website:     '',
            logo_path:   'assets/print-logo.png',
            footer_text: ''
        };
    }

    /* ── Print-window CSS ────────────────────────────────────────────── */
    function buildStyles() {
        return [
            /* 1. Reset */
            '*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }',

            /* 2. Page — zero margin; body padding carries the gutters */
            '@page { size: A4; margin: 0; }',

            /* 3. Body */
            'body {',
            '  font-family: ' + SANS + ';',
            '  font-size: 11px;',
            '  line-height: 1.45;',
            '  color: ' + INK + ';',
            '  background: #fff;',
            '  -webkit-print-color-adjust: exact;',
            '  print-color-adjust: exact;',
            /* Push body content below the fixed header (logo ≈ 48px + 5mm top + 3mm margin + 3px rule ≈ 28 mm)
               and above the fixed footer (two lines at 9px × 1.6 + 4mm padding ≈ 14 mm) */
            '  padding: 30mm 14mm 14mm;',
            '}',

            /* 4. Fixed header — logo centred, accent rule beneath */
            '.dtp-header {',
            '  position: fixed;',
            '  top: 0; left: 0; right: 0;',
            '  background: #fff;',
            '  text-align: center;',
            '  padding: 5mm 14mm 0;',
            '  z-index: 1000;',
            '}',
            '.dtp-header__logo {',
            '  height: 48px;',
            '  width: auto;',
            '  object-fit: contain;',
            '  display: block;',
            '  margin: 0 auto 3mm;',
            '}',
            '.dtp-header__rule {',
            '  height: 3px;',
            '  background: ' + ACCENT + ';',
            '}',

            /* 5. Title row — report name left, timestamp right */
            '.dtp-title-row {',
            '  display: flex;',
            '  justify-content: space-between;',
            '  align-items: baseline;',
            '  margin-bottom: 10px;',
            '  padding-bottom: 6px;',
            '  border-bottom: 2px solid ' + ACCENT + ';',
            '}',
            '.dtp-title {',
            '  font-size: 15px;',
            '  font-weight: 700;',
            '  color: ' + ACCENT + ';',
            '  text-transform: uppercase;',
            '  letter-spacing: 0.05em;',
            '}',
            '.dtp-meta {',
            '  font-family: ' + MONO + ';',
            '  font-size: 10px;',
            '  color: ' + MUTED + ';',
            '}',

            /* 6. Table — full design-token styling, overrides DataTables defaults */
            'table {',
            '  width: 100%;',
            '  border-collapse: collapse !important;',
            '  margin-bottom: 14px;',
            '  font-size: 11px;',
            '}',
            'thead tr { background: ' + ACCENT + ' !important; }',
            'thead th {',
            '  color: #fff !important;',
            '  font-family: ' + MONO + ' !important;',
            '  font-size: 8.5px !important;',
            '  text-transform: uppercase !important;',
            '  letter-spacing: 0.10em !important;',
            '  padding: 6px 8px !important;',
            '  text-align: left !important;',
            '  font-weight: 700 !important;',
            '  white-space: nowrap;',
            '  border: none !important;',
            '}',
            'tbody tr { border-bottom: 1px solid ' + HAIRLINE + ' !important; }',
            'tbody tr:nth-child(even) { background: ' + ROW_ALT + ' !important; }',
            'tbody td {',
            '  padding: 5px 8px !important;',
            '  vertical-align: middle;',
            '  color: ' + INK + ';',
            '  border: none !important;',
            '  border-bottom: 1px solid ' + HAIRLINE + ' !important;',
            '}',
            'tfoot tr {',
            '  border-top: 2px solid ' + HAIRLINE + ' !important;',
            '  background: ' + ROW_ALT + ' !important;',
            '}',
            'tfoot td {',
            '  padding: 5px 8px !important;',
            '  font-weight: 600;',
            '  border: none !important;',
            '}',

            /* 7. Fixed footer — accent bar, centred two-line text */
            '.dtp-footer {',
            '  position: fixed;',
            '  bottom: 0; left: 0; right: 0;',
            '  background: ' + ACCENT + ';',
            '  text-align: center;',
            '  padding: 2mm 14mm;',
            '  z-index: 1000;',
            '}',
            '.dtp-footer__line {',
            '  font-size: 9px;',
            '  color: #fff;',
            '  font-weight: 700;',
            '  line-height: 1.6;',
            '  display: block;',
            '}',
            '.dtp-footer__line.mono {',
            '  font-family: ' + MONO + ';',
            '}',

            /* 8. Page-break helpers */
            'tr { page-break-inside: avoid; }',

            /* 9. Print media — ensure color accuracy */
            '@media print {',
            '  body {',
            '    -webkit-print-color-adjust: exact !important;',
            '    print-color-adjust: exact !important;',
            '  }',
            '}'
        ].join('\n');
    }

    /* ── Zero-pad helper ─────────────────────────────────────────────── */
    function pad2(n) { return n < 10 ? '0' + n : '' + n; }

    /* ── Date/time string ────────────────────────────────────────────── */
    function nowString() {
        var d = new Date();
        var months = ['Jan','Feb','Mar','Apr','May','Jun',
                      'Jul','Aug','Sep','Oct','Nov','Dec'];
        return pad2(d.getDate()) + ' ' + months[d.getMonth()] + ' ' + d.getFullYear()
             + '  ' + pad2(d.getHours()) + ':' + pad2(d.getMinutes());
    }

    /* ── Core: apply the professional layout to the DataTables window ── */
    function applyLayout(win, title) {
        var co  = getCompany();
        var doc = win.document;

        /* Remove DataTables own <style> blocks — we replace them entirely */
        [].slice.call(doc.querySelectorAll('style')).forEach(function (s) {
            s.parentNode.removeChild(s);
        });

        /* Inject design-system styles */
        var styleEl = doc.createElement('style');
        styleEl.textContent = buildStyles();
        doc.head.appendChild(styleEl);

        /* Set window title */
        doc.title = title || 'Report';

        /* Remove the empty <h1> DataTables writes */
        var h1 = doc.querySelector('h1');
        if (h1) h1.parentNode.removeChild(h1);

        /* Build header: centred logo + accent rule */
        var logoSrc = window.location.origin + '/assets/print-logo.png';
        var logoHtml = '<img class="dtp-header__logo" src="' + logoSrc + '" alt="Logo">';
        var headerHtml =
            '<div class="dtp-header">' +
                logoHtml +
                '<div class="dtp-header__rule"></div>' +
            '</div>';

        /* Build title row: report title + timestamp */
        var titleHtml =
            '<div class="dtp-title-row">' +
                '<span class="dtp-title">' + (title || 'Report') + '</span>' +
                '<span class="dtp-meta">' + nowString() + '</span>' +
            '</div>';

        /* Build footer: two centred lines matching the Blade footer layout.
           If footer_text is set, render it as-is (it may already contain line breaks).
           Otherwise build line 1 from address and line 2 from phone/email/website. */
        var footerHtml = '<div class="dtp-footer">';

        if (co.footer_text && co.footer_text.trim()) {
            var ftLines = co.footer_text
                .replace(/<br\s*\/?>/gi, '\n')
                .replace(/<[^>]+>/g, '')
                .split('\n');
            for (var i = 0; i < ftLines.length; i++) {
                var line = ftLines[i].trim();
                if (line) {
                    footerHtml += '<span class="dtp-footer__line">' + line + '</span>';
                }
            }
        } else {
            var addrParts = [co.address, co.city, co.country]
                .filter(function (p) { return p && p.trim(); });
            var addrLine = addrParts.join(', ');

            var contactParts = [co.phone, co.email, co.website]
                .filter(function (p) { return p && p.trim(); });
            var contactLine = contactParts.join('  |  ');

            if (addrLine) {
                footerHtml += '<span class="dtp-footer__line">' + addrLine + '</span>';
            }
            if (contactLine) {
                footerHtml += '<span class="dtp-footer__line mono">' + contactLine + '</span>';
            }
        }

        footerHtml += '</div>';

        /* Inject header + title row before existing body content */
        var topWrap = doc.createElement('div');
        topWrap.innerHTML = headerHtml + titleHtml;
        doc.body.insertBefore(topWrap, doc.body.firstChild);

        /* Inject footer after existing body content */
        var botWrap = doc.createElement('div');
        botWrap.innerHTML = footerHtml;
        doc.body.appendChild(botWrap);
    }

    /* ── Public API ──────────────────────────────────────────────────── */
    /**
     * Called from the DataTables print button `customize` callback.
     *
     * @param {Window} win   - The DataTables-created print window
     * @param {string} title - Report title to display (e.g. "Order List")
     */
    window.dtPrintCustomize = function (win, title) {
        applyLayout(win, title);
    };

})(window);
