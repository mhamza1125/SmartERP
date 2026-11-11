/*!
 * SmartERP — DataTable Print / Export
 * ====================================
 * Layout for the DataTables "Print" button, matching the Blade print views:
 *   header (repeated on every page): logo left, report title middle, phone / email / web right
 *   no footer
 * plus the cell cleaning shared by every export button (copy / excel / csv / pdf / print).
 *
 * Included globally via index.blade.php (before custom.js). Company logo, contacts and the
 * base URL come from window.SmartERP (set in index.blade.php), so it works when the app runs
 * under a sub-folder such as http://host:8080/ERP/public.
 *
 * API:
 *   window.dtPrintCustomize(win, title)   DataTables print `customize` callback
 *   window.dtExportCell(data, row, column, node)   exportOptions.format.body
 */
(function (window, $) {
    'use strict';

    var ACCENT   = '#1f3a5f';
    var INK      = '#1a1d21';
    var MUTED    = '#6b707a';
    var HAIRLINE = '#dfe3e8';
    var ROW_ALT  = '#f6f8fa';
    var SANS     = '"Arial","Helvetica Neue",Helvetica,sans-serif';
    var MONO     = '"Courier New",Courier,monospace';

    // Header text that marks a numeric column (right-aligned), and words that rule it out
    var NUMERIC_HEAD = /\b(qty|quantity|price|rate|total|amount|debit|credit|balance|stock|weight|pcs|boxes|cartons|average|avg|ordered|received|returned|remaining|wages|cost|value|salary|paid|due)\b/i;
    var NOT_NUMERIC_HEAD = /\b(no|name|date|from|to|by|type|description|method|reference|details|head|stage|remarks|reason|code|status|detail)\b/i;

    function config() {
        return window.SmartERP || {};
    }

    function escapeHtml(text) {
        return String(text == null ? '' : text)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function nowString() {
        var d = new Date();
        var pad = function (n) { return n < 10 ? '0' + n : '' + n; };
        return pad(d.getDate()) + '-' + pad(d.getMonth() + 1) + '-' + d.getFullYear() + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    /* ── Cell text for every export ───────────────────────────────────────
       Interactive markup in a cell (status dropdowns, menus, forms, hidden inputs)
       is not data: keep only what the cell shows. A cell can also give its own
       export text with data-export="...". */
    window.dtExportCell = function (data, row, column, node) {
        if (!node || !node.cloneNode) {
            return typeof data === 'string' ? $('<div>').html(data).text().replace(/\s+/g, ' ').trim() : data;
        }
        if (node.hasAttribute && node.hasAttribute('data-export')) {
            return node.getAttribute('data-export');
        }

        var el = node.cloneNode(true);
        var $el = $(el);
        $el.find('[data-export]').each(function () {
            $(this).replaceWith(document.createTextNode(this.getAttribute('data-export')));
        });
        $el.find('.dropdown-menu, .no-export, .d-print-none, .modal, script, style, input[type=hidden], template').remove();
        $el.find('select').each(function () {
            $(this).replaceWith(document.createTextNode($(this).find('option:selected').text()));
        });
        $el.find('input').each(function () {
            $(this).replaceWith(document.createTextNode(this.value || ''));
        });
        $el.find('br').replaceWith(' ');
        // Keep block elements apart ("Name" + "Location" -> "Name Location", not "NameLocation")
        $el.find('div, p, li, sub, sup, small, span.badge').each(function () {
            $(this).before(' ').after(' ');
        });

        return $el.text().replace(/\s+/g, ' ').trim();
    };

    /* ── Print window styles ─────────────────────────────────────────── */
    function buildStyles(landscape) {
        return [
            '*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }',
            '@page { size: A4 ' + (landscape ? 'landscape' : 'portrait') + '; margin: 8mm 10mm 10mm; }',
            'html, body { background: #fff; }',
            'body { font-family: ' + SANS + '; font-size: 10.5px; line-height: 1.4; color: ' + INK + ';',
            '  -webkit-print-color-adjust: exact; print-color-adjust: exact; }',

            /* Frame: its thead (the page header) repeats on every printed page */
            '.dtp-frame { width: 100%; border-collapse: collapse; }',
            '.dtp-frame > thead > tr > td, .dtp-frame > tbody > tr > td { padding: 0; border: none; }',

            /* Header: logo | title | contacts */
            '.dtp-header { display: grid; grid-template-columns: minmax(0, 46fr) minmax(0, 30fr) minmax(0, 24fr); align-items: center; gap: 12px;',
            '  padding-bottom: 6px; border-bottom: 3px solid ' + ACCENT + '; margin-bottom: 10px; }',
            '.dtp-header__logo { height: 64px; max-width: 100%; width: auto; object-fit: contain; display: block; }',
            '.dtp-header__title { text-align: center; min-width: 0; }',
            '.dtp-header__title-main { font-size: 13px; font-weight: 700; color: ' + ACCENT + '; text-transform: uppercase; letter-spacing: 0.05em;',
            '  line-height: 1.2; overflow-wrap: anywhere; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; overflow: hidden; }',
            '.dtp-header__title-sub { font-family: ' + MONO + '; font-size: 9.5px; color: ' + MUTED + '; margin-top: 2px; }',
            '.dtp-header__contacts { justify-self: end; text-align: right; font-size: 9px; line-height: 1.5; min-width: 0; overflow-wrap: anywhere; }',
            '.dtp-header__label { font-family: ' + MONO + '; font-size: 7.5px; text-transform: uppercase; letter-spacing: 0.1em; color: ' + MUTED + '; margin-right: 3px; }',

            /* Data table. Column titles are dark text on a light band so they print
               even when the browser's "Background graphics" option is off. */
            'table.dataTable, table.dtp-data { width: 100% !important; border-collapse: collapse !important; font-size: 10.5px; margin: 0 !important; }',
            'table.dtp-data thead { display: table-header-group; }',
            'table.dtp-data thead th { background: #e9eef4 !important; color: ' + ACCENT + ' !important; font-size: 10.5px; font-weight: 700;',
            '  text-transform: uppercase; letter-spacing: 0.03em; text-align: left; padding: 6px 7px !important; white-space: nowrap;',
            '  border: none !important; border-top: 2px solid ' + ACCENT + ' !important; border-bottom: 2px solid ' + ACCENT + ' !important; }',
            /* Columns size to their content (widths DataTables set on screen are dropped) */
            'table.dtp-data th, table.dtp-data td { width: auto !important; }',
            'table.dtp-data tbody td { padding: 4px 7px !important; vertical-align: top; border: none !important;',
            '  border-bottom: 1px solid ' + HAIRLINE + ' !important; word-break: break-word; }',
            'table.dtp-data tbody tr:nth-child(even) td { background: ' + ROW_ALT + ' !important; }',
            'table.dtp-data tfoot th, table.dtp-data tfoot td { padding: 5px 7px !important; font-weight: 700; border-top: 2px solid ' + ACCENT + ' !important; }',
            'table.dtp-data .num { text-align: right !important; font-family: ' + MONO + '; white-space: nowrap; }',
            'table.dtp-data .sr { text-align: center !important; width: 1%; white-space: nowrap; }',
            'table.dtp-data tr { page-break-inside: avoid; }',

            '.dtp-count { margin-top: 6px; font-size: 9px; color: ' + MUTED + '; text-align: right; }'
        ].join('\n');
    }

    function headerHtml(title) {
        var cfg = config();
        var contacts = cfg.contacts || {};
        var contactRows = [['Phone', contacts.phone], ['Email', contacts.email], ['Web', contacts.website]]
            .filter(function (item) { return item[1] && String(item[1]).trim() && String(item[1]).trim() !== '.'; })
            .map(function (item) {
                return '<div><span class="dtp-header__label">' + item[0] + '</span>' + escapeHtml(item[1]) + '</div>';
            }).join('');
        var logo = cfg.logo || (window.appUrl ? window.appUrl('assets/print-logo.png') : 'assets/print-logo.png');

        return '<div class="dtp-header">' +
            '<div><img class="dtp-header__logo" src="' + escapeHtml(logo) + '" alt="Logo"></div>' +
            '<div class="dtp-header__title">' +
                '<div class="dtp-header__title-main">' + escapeHtml(title || 'Report') + '</div>' +
                '<div class="dtp-header__title-sub">Printed ' + nowString() + '</div>' +
            '</div>' +
            '<div class="dtp-header__contacts">' + contactRows + '</div>' +
        '</div>';
    }

    /* Right-align numeric columns (by their title) in head, body and foot; centre Sr. */
    function alignColumns(table) {
        var headRow = table.tHead && table.tHead.rows[0];
        if (!headRow) return;
        var rows = [].slice.call(table.querySelectorAll('tbody tr, tfoot tr'));

        [].forEach.call(headRow.cells, function (th, index) {
            var text = th.textContent.replace(/\s+/g, ' ').trim();
            var cls = /^(sr\.?|#|s\.? ?no\.?)$/i.test(text) ? 'sr'
                : (NUMERIC_HEAD.test(text) && !NOT_NUMERIC_HEAD.test(text) ? 'num' : null);
            if (!cls) return;
            th.classList.add(cls);
            rows.forEach(function (row) {
                if (row.cells[index]) row.cells[index].classList.add(cls);
            });
        });
    }

    function applyLayout(win, title) {
        var doc = win.document;

        // Replace DataTables' own styles and title
        [].slice.call(doc.querySelectorAll('style, link[rel=stylesheet]')).forEach(function (s) { s.parentNode.removeChild(s); });
        var h1 = doc.querySelector('h1');
        if (h1) h1.parentNode.removeChild(h1);
        doc.title = title || 'Report';

        var table = doc.querySelector('table');
        if (!table) return;
        table.classList.add('dtp-data');
        // Many listings repeat the column titles in <tfoot>: print a footer only when it has totals
        var titles = function (section) { return section ? section.textContent.replace(/\s+/g, ' ').trim().toLowerCase() : ''; };
        if (table.tFoot && (titles(table.tFoot) === titles(table.tHead) || titles(table.tFoot) === '')) {
            table.removeChild(table.tFoot);
        }
        alignColumns(table);

        var columns = table.tHead && table.tHead.rows[0] ? table.tHead.rows[0].cells.length : 0;
        var styleEl = doc.createElement('style');
        styleEl.textContent = buildStyles(columns > 7);
        doc.head.appendChild(styleEl);

        // Frame table: page header in its thead (repeats per page), the data table in its body
        var frame = doc.createElement('table');
        frame.className = 'dtp-frame';
        frame.innerHTML = '<thead><tr><td>' + headerHtml(title) + '</td></tr></thead><tbody><tr><td></td></tr></tbody>';
        table.parentNode.insertBefore(frame, table);
        var cell = frame.tBodies[0].rows[0].cells[0];
        cell.appendChild(table);

        var count = table.tBodies[0] ? table.tBodies[0].rows.length : 0;
        var countEl = doc.createElement('div');
        countEl.className = 'dtp-count';
        countEl.textContent = count + ' record' + (count === 1 ? '' : 's');
        cell.appendChild(countEl);
    }

    window.dtPrintCustomize = function (win, title) {
        applyLayout(win, title);
    };

})(window, jQuery);
