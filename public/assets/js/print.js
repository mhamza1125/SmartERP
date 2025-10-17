// ===========================================================================================
// =============================== Print Functionality ===================================
// ===========================================================================================

// Print Purchase Info Modal
function printPModal(modalId) {
    var printContents = document.getElementById(modalId).innerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
}

/**
 * Global print function for information/detail pages
 * Supports printing entire page or specific tabs with header information
 * @param {string} mode - 'page' for entire page, 'tab' for specific tab
 * @param {string} tabId - ID of specific tab to print (only used when mode is 'tab')
 * @param {string} pageTitle - Custom title for the print page
 */
function globalPrint(mode = 'page', tabId = null, pageTitle = null) {
    // Get the main content area (excluding navigation)
    const mainContent = document.querySelector('.main-content');
    const cardBody = document.querySelector('.card-body');
    const cardHeader = document.querySelector('.card-header h4');

    if (!mainContent || !cardBody) {
        console.error('Required elements not found for printing');
        return;
    }

    // Get page title
    const title = pageTitle || (cardHeader ? cardHeader.textContent : document.title);

    // Build print content
    let printContent = '';

    if (mode === 'page') {
        // Print entire page content
        printContent = cardBody.innerHTML;
    } else if (mode === 'tab' && tabId) {
        // Print specific tab with header information
        const headerInfo = cardBody.querySelector('.row:first-child');
        const tabContent = document.getElementById(tabId);

        if (headerInfo && tabContent) {
            printContent = headerInfo.outerHTML + '<hr>' + tabContent.innerHTML;
        } else {
            console.error('Tab content or header not found');
            return;
        }
    }

    // Fetch company data and generate print document
    fetch('/company/data')
        .then(response => response.json())
        .then(company => {
            generatePrintDocument(title, printContent, company);
        })
        .catch(error => {
            console.error('Error fetching company data:', error);
            // Fallback to default company data
            const defaultCompany = {
                name: 'Sajjadson Lab Equipment',
                address: 'Near Sachi Sarkar Darbar, Opposite Qayyum Elahi Surgical, Harrar Sialkot, Pakistan',
                phone: '+92 52 357 3727',
                email: 'info@sajjadsonlab.com',
                website: 'sajjadsonlab.com',
                logo_path: 'assets/print-logo.png',
                footer_text: 'Near Sachi Sarkar Darbar, Opposite Qayyum Elahi Surgical, Harrar Sialkot, Pakistan'
            };
            generatePrintDocument(title, printContent, defaultCompany);
        });
}

/**
 * Generate and display the print document
 * @param {string} title - Document title
 * @param {string} printContent - Content to print
 * @param {object} company - Company data
 */
function generatePrintDocument(title, printContent, company) {
    // Create print window
    const printWindow = window.open('', '_blank', 'width=800,height=600');

    // Write print document
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>${title}</title>
            <style>
                /* Print Styles */
                @media print {
                    @page {
                        margin: 1in;
                        size: A4;
                    }
                }

                body {
                    font-family: Arial, sans-serif;
                    font-size: 12px;
                    line-height: 1.4;
                    color: #000;
                    background: white;
                    margin: 0;
                    padding: 20px;
                    padding-bottom: 80px; /* Space for footer */
                }

                h1, h2, h3, h4, h5, h6 {
                    color: #000;
                    margin-bottom: 10px;
                }

                .page-title {
                    text-align: center;
                    font-size: 18px;
                    font-weight: bold;
                    margin-bottom: 20px;
                    border-bottom: 2px solid #000;
                    padding-bottom: 10px;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                    font-size: 11px;
                }

                table th,
                table td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                    vertical-align: top;
                }

                table th {
                    background-color: #f5f5f5;
                    font-weight: bold;
                }

                table tfoot th {
                    background-color: #e9e9e9;
                    font-weight: bold;
                }

                .table-sm th,
                .table-sm td {
                    padding: 4px;
                }

                .badge {
                    display: inline-block;
                    padding: 2px 6px;
                    font-size: 10px;
                    font-weight: bold;
                    border-radius: 3px;
                    color: white;
                }

                .badge-success { background-color: #28a745; }
                .badge-warning { background-color: #ffc107; color: #000; }
                .badge-danger { background-color: #dc3545; }
                .badge-info { background-color: #17a2b8; }

                .row {
                    display: block;
                    margin-bottom: 15px;
                }

                .col-md-7, .col-md-5, .col-md-12 {
                    display: block;
                    width: 100%;
                    margin-bottom: 10px;
                }

                /* Hide elements that shouldn't be printed */
                .btn, .button, .nav-tabs, .card-header-action,
                .dropdown, .modal, .tooltip, .popover,
                .pbtn, .no-print {
                    display: none !important;
                }

                /* Tab content styling */
                .tab-content {
                    display: block !important;
                }

                .tab-pane {
                    display: block !important;
                    opacity: 1 !important;
                }

                hr {
                    border: none;
                    border-top: 1px solid #ccc;
                    margin: 20px 0;
                }

                /* Company header styling */
                .print-header {
                    text-align: center;
                    margin-bottom: 30px;
                    border-bottom: 2px solid #000;
                    padding-bottom: 15px;
                }

                .print-header img {
                    max-height: 80px;
                    margin-bottom: 10px;
                }

                .print-header h1 {
                    margin: 0;
                    font-size: 24px;
                    font-weight: bold;
                }

                .print-header p {
                    margin: 5px 0;
                    font-size: 12px;
                }

                /* Footer styling */
                .print-footer {
                    position: fixed;
                    bottom: 20px;
                    left: 0;
                    right: 0;
                    text-align: center;
                    font-size: 12px;
                    border-top: 1px solid #000;
                    padding-top: 10px;
                    background: white;
                }
            </style>
        </head>
        <body>
            <div class="print-header">
                <img src="${window.location.origin}/${company.logo_path}" alt="Company Logo">
                <h1>${company.name}</h1>
                <p>${company.address}</p>
                <p>Phone: ${company.phone} || Email: ${company.email} || Web: ${company.website}</p>
            </div>
            <div class="page-title">${title}</div>
            <div class="print-content">
                ${printContent}
            </div>
            <div class="print-footer">
                <p>${company.footer_text}</p>
                <p>Phone: ${company.phone} || Email: ${company.email} || Web: ${company.website}</p>
            </div>
            <script>
                window.onload = function() {
                    window.print();
                    window.onafterprint = function() {
                        window.close();
                    };
                };
            </script>
        </body>
        </html>
    `);

    printWindow.document.close();
}

/**
 * Print entire information page
 * @param {string} pageTitle - Custom title for the print page
 */
function printPage(pageTitle = null) {
    globalPrint('page', null, pageTitle);
}

/**
 * Print specific tab content with header
 * @param {string} tabId - ID of the tab to print
 * @param {string} tabTitle - Title for the tab being printed
 */
function printTab(tabId, tabTitle = null) {
    const tabElement = document.getElementById(tabId);
    if (!tabElement) {
        console.error('Tab with ID "' + tabId + '" not found');
        return;
    }

    // Get tab title from nav link if not provided
    if (!tabTitle) {
        const navLink = document.querySelector(`[href="#${tabId}"]`);
        tabTitle = navLink ? navLink.textContent.trim() : 'Tab Content';
    }

    globalPrint('tab', tabId, tabTitle);
}

/**
 * Generic invoice print function with dynamic company header
 * @param {string} content - HTML content to print
 * @param {string} title - Document title
 */
function printInvoice(content, title) {
    console.log('printInvoice() called with title:', title);
    console.log('Opening print window...');

    var printWindow = window.open('', '_blank');

    if (!printWindow) {
        alert('Pop-up blocked! Please allow pop-ups for this site to generate invoices.');
        return;
    }

    // Fetch company data and generate invoice
    fetch('/company/data')
        .then(response => response.json())
        .then(company => {
            generateInvoiceDocument(printWindow, content, title, company);
        })
        .catch(error => {
            console.error('Error fetching company data:', error);
            // Fallback to default company data
            const defaultCompany = {
                name: 'Sajjadson Lab Equipment',
                address: 'Near Sachi Sarkar Darbar, Opposite Qayyum Elahi Surgical, Harrar Sialkot, Pakistan',
                phone: '+92 52 357 3727',
                email: 'info@sajjadsonlab.com',
                website: 'sajjadsonlab.com',
                logo_path: 'assets/print-logo.png',
                footer_text: 'Near Sachi Sarkar Darbar, Opposite Qayyum Elahi Surgical, Harrar Sialkot, Pakistan'
            };
            generateInvoiceDocument(printWindow, content, title, defaultCompany);
        });
}

/**
 * Generate invoice document with company header
 * @param {Window} printWindow - Print window object
 * @param {string} content - HTML content
 * @param {string} title - Document title
 * @param {object} company - Company data
 */
function generateInvoiceDocument(printWindow, content, title, company) {
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>${title}</title>
            <style>
                @media print {
                    @page { margin: 0.5in; size: A4; }
                }
                body { font-family: Arial, sans-serif; font-size: 12px; margin: 0; padding: 20px; }
                .invoice-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 15px; }
                .invoice-header img { max-height: 80px; margin-bottom: 10px; }
                .invoice-header h1 { margin: 0; font-size: 24px; font-weight: bold; }
                .invoice-header p { margin: 5px 0; font-size: 12px; }
                .invoice-title { text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                table th, table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                table th { background-color: #f5f5f5; font-weight: bold; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                .no-print { display: none !important; }
            </style>
        </head>
        <body>
            <div class="invoice-header">
                <img src="${window.location.origin}/${company.logo_path}" alt="Company Logo">
                <h1>${company.name}</h1>
                <p>${company.address}</p>
                <p>Phone: ${company.phone} || Email: ${company.email} || Web: ${company.website}</p>
            </div>
            <div class="invoice-title">${title}</div>
            <div class="invoice-content">
                ${content}
            </div>
            <script>
                window.onload = function() {
                    window.print();
                    window.onafterprint = function() {
                        window.close();
                    };
                };
            </script>
        </body>
        </html>
    `);

    printWindow.document.close();
}

// End - Print Functionality
