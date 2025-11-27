<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Print Document')</title>
    <link rel="stylesheet" href="{{ asset('assets/css/print.css') }}">
    <style>
        @page {
            size: A4;
            margin: 20mm 15mm 25mm 15mm; /* top, right, bottom, left - extra bottom for footer */
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            color: #000;
            background: white;
        }

        /* Print Header - Fixed at top */
        .print-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 80px;
            background: white;
            border-bottom: 2px solid #333;
            padding: 10px 15mm;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .company-logo {
            height: 60px;
            width: auto;
        }
        
        .company-info {
            text-align: center;
            flex-grow: 1;
            margin: 0 20px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .company-tagline {
            font-size: 14px;
            color: #666;
            font-style: italic;
        }
        
        .company-reg {
            font-size: 10px;
            color: #888;
            margin-top: 3px;
        }
        
        /* Print Footer - Fixed at bottom */
        .print-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: white;
            border-top: 1px solid #333;
            padding: 8px 15mm;
            z-index: 1000;
            font-size: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .footer-left {
            text-align: left;
        }
        
        .footer-center {
            text-align: center;
            flex-grow: 1;
            margin: 0 20px;
        }
        
        .footer-right {
            text-align: right;
        }
        
        /* Main content area */
        .print-content {
            margin-top: 100px; /* Space for header */
            margin-bottom: 80px; /* Space for footer */
            padding: 0 15mm;
        }
        
        /* Document title */
        .document-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
        }
        
        /* Standard table styling */
        .print-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .print-table th,
        .print-table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        
        .print-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }
        
        .print-table .text-right {
            text-align: right;
        }
        
        .print-table .text-center {
            text-align: center;
        }
        
        /* Info sections */
        .info-section {
            margin-bottom: 20px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        
        .info-label {
            font-weight: bold;
            width: 150px;
            flex-shrink: 0;
        }
        
        .info-value {
            flex-grow: 1;
        }
        
        /* Totals section */
        .totals-section {
            margin-top: 20px;
            border-top: 2px solid #333;
            padding-top: 10px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .grand-total {
            font-size: 14px;
            border-top: 1px solid #333;
            padding-top: 5px;
        }
        
        /* Amount in words */
        .amount-words {
            margin-top: 15px;
            padding: 10px;
            border: 1px solid #333;
            background-color: #f9f9f9;
        }
        
        .amount-words-label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        /* Signatures */
        .signatures {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            text-align: center;
            width: 30%;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            height: 40px;
            margin-bottom: 5px;
        }
        
        /* Print-specific styles */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-before: always;
            }

            .avoid-break {
                page-break-inside: avoid;
            }

            /* Prevent header/footer overlap on page breaks */
            .print-header {
                position: static;
                margin-bottom: 20px;
            }

            .print-footer {
                position: static;
                margin-top: 20px;
                page-break-after: always;
            }

            .print-content {
                margin-top: 0;
                margin-bottom: 0;
            }

            /* Prevent table rows from breaking awkwardly */
            .print-table tbody tr {
                page-break-inside: avoid;
            }

            /* Prevent info sections from breaking */
            .info-section {
                page-break-inside: avoid;
            }
        }
        
        /* Screen-only styles */
        @media screen {
            .print-header {
                position: relative;
            }
            
            .print-footer {
                position: relative;
            }
            
            .print-content {
                margin-top: 20px;
                margin-bottom: 20px;
            }
            
            .print-button {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 2000;
                padding: 12px 24px;
                background: #007bff;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 14px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            }
            
            .print-button:hover {
                background: #0056b3;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Print button (visible only on screen) -->
    <button onclick="window.print()" class="print-button no-print">
        <i class="fas fa-print"></i> Print Document
    </button>

    <!-- Print Header -->
    {{-- <div class="print-header">
        @include('print.header')
    </div> --}}

    <!-- Main Content -->
    <div class="print-content">
        @yield('content')
    </div>

    <!-- Print Footer -->
    {{-- <div class="print-footer">
        @include('print.footer')
    </div> --}}

    @stack('scripts')

    <script>
        // Auto-generate PDF filename based on document title and current date
        document.addEventListener('DOMContentLoaded', function() {
            // Get the document title
            const title = document.title;

            // Generate filename with current date
            const currentDate = new Date().toISOString().split('T')[0]; // YYYY-MM-DD format

            // Clean the title for filename (remove special characters)
            const cleanTitle = title.replace(/[^a-zA-Z0-9\s\-_]/g, '').replace(/\s+/g, '_');

            // Set the filename for when user saves/downloads the PDF
            const filename = `${cleanTitle}_${currentDate}.pdf`;

            // Update document title to include the filename hint
            document.title = filename.replace('.pdf', '');

            // Add a data attribute to body for potential use by print handlers
            document.body.setAttribute('data-print-filename', filename);
        });

        // Enhanced print function with filename
        window.addEventListener('beforeprint', function() {
            // Additional print preparation if needed
            console.log('Preparing to print:', document.body.getAttribute('data-print-filename'));
        });

        window.addEventListener('afterprint', function() {
            // Post-print cleanup if needed
            console.log('Print completed');
        });
    </script>
</body>
</html>
