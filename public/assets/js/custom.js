// ===========================================================================================
// ======================================== Custom JS ========================================
// ===========================================================================================

"use strict";

// Start - Toaster Message
$(document).ready(function () {
    var successMessage = $('#successMessage').val();
    var errorMessage = $('#errorMessage').val();
    if (successMessage) {
        iziToast.success({
            title: 'Success!',
            message: successMessage,
            position: 'topRight'
        });
    } else if (errorMessage) {
        iziToast.error({
            title: 'Error!',
            message: errorMessage,
            position: 'topRight'
        });
    }
});
// End - Toaster Message

// Start - Confirm Form Submission
function submits() {
    return confirm('Are you sure you want to submit?');
}
// End - Confirm Form Submission

// Start - Shortcut Keys
$(document).ready(function () {
    $(document).keydown(function (event) {
        if (event.altKey && event.key === 's') {
            event.preventDefault(); // Prevent the default action if any
            $('#btn-id').focus();
        }
    });
});
// End - Shortcut Keys

// Start - Remove Header Of Export Table
$(document).ready(function () {
    // Function to initialize DataTable with export buttons and custom header
    function initializeDataTable(tableId) {
        if ($.fn.DataTable.isDataTable(tableId)) {
            $(tableId).DataTable().destroy();
        }

        $(tableId).DataTable({
            dom: 'Bfrtip',
            // lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
            pageLength: 200,
            buttons: [
                {
                    extend: 'copyHtml5',
                    title: '',
                    exportOptions: { title: null, columns: ':not(:contains("Action"))' }
                },
                {
                    extend: 'excelHtml5',
                    title: '',
                    exportOptions: { title: null, columns: ':not(:contains("Action"))' }
                },
                {
                    extend: 'csvHtml5',
                    title: '',
                    exportOptions: { title: null, columns: ':not(:contains("Action"))' }
                },
                {
                    extend: 'pdfHtml5',
                    title: '',
                    exportOptions: { title: null, columns: ':not(:contains("Action"))' }
                },
                {
                    extend: 'print',
                    title: '',
                    customize: function (win) {
                        $(win.document.body).prepend(customHeader);
                    },
                    exportOptions: { title: null, columns: ':not(:contains("Action"))' }
                }
            ]
        });
    }

    // Custom header HTML - will be populated dynamically
    var customHeader = '<center><h1>Loading...</h1></center>';

    // Fetch company data for table printing
    fetch('/company/data')
        .then(response => response.json())
        .then(company => {
            customHeader = `<center><h1>${company.name}</h1><h5>${company.address}</h5><h6>Phone: ${company.phone} || Email: ${company.email} || Web: ${company.website}</h6></center>`;
        })
        .catch(error => {
            console.error('Error fetching company data:', error);
            customHeader = '<center><h1>Sajjadson Lab Equipment</h1><h5>Near Sachi Sarkar Darbar, Opposite Qayyum Elahi Surgical, Harrar Sialkot, Pakistan</h5><h6>Phone no. +92 52 357 3727 || E-mail: info@sajjadsonlab.com || Web: sajjadsonlab.com</h6></center>';
        });

    // Initialize DataTable for table with ID #tableExport
    initializeDataTable('#tableExport');

    // Initialize DataTable for table with ID #tableExport1
    initializeDataTable('#tableExport1');
});
// End - Remove Header Of Export Table

// Start - Stock Table Save Stage
$(document).ready(function () {
    $('#save-stage-all').DataTable({
        "stateSave": true // Enable state saving
    });

    $('#save-stage-receive').DataTable({
        "stateSave": true // Enable state saving
    });

    $('#recordsPerPage').on('change', function () {
        // Retrieve the selected value
        var selectedValue = $(this).val();
        $('#save-stage-all').DataTable().page.len(selectedValue).draw();
        $('#save-stage-receive').DataTable().page.len(selectedValue).draw();
    });
});
// End - Stock Table Save Stage

// Start - Wrong Extension Image / File Name
document.addEventListener("DOMContentLoaded", function () {
    var fileInput = document.getElementById('customFile');
    var fileError = document.getElementById('fileError');
    var fileSuccess = document.getElementById('fileSuccess');

    // Check if fileInput exists before adding event listener
    if (fileInput) {
        handleFileInputChange(fileInput, fileError, fileSuccess);
    }

    function handleFileInputChange(fileInput, fileError, fileSuccess) {
        fileInput.addEventListener('change', function () {
            var files = this.files;
            var errorMessage = '';
            var fileNames = '';

            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                var extension = file.name.split('.').pop().toLowerCase();
                var allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];

                if (allowedExtensions.indexOf(extension) === -1) {
                    errorMessage = 'Please select JPG, JPEG, PNG, GIF, or SVG files only.';
                    break;
                } if (i > 0) {
                    fileNames += ', ';
                }
                fileNames += file.name;
            }
            if (errorMessage) {
                fileError.textContent = errorMessage;
                fileError.style.display = 'block';
                fileSuccess.style.display = 'none';
                this.value = '';
            } else {
                fileError.style.display = 'none';
                fileSuccess.textContent = 'Selected files: ' + fileNames;
                fileSuccess.style.display = 'block';
            }
        });
    }
});

document.addEventListener("DOMContentLoaded", function () {
    function handleFileInputChange(event) {
        var fileInput = event.target;
        var fileSuccess = fileInput.closest('.attachment-row').querySelector('.attachment-success');

        if (fileInput.files.length > 0) {
            fileSuccess.textContent = 'Selected file: ' + fileInput.files[0].name;
            fileSuccess.style.display = 'block';
        } else {
            fileSuccess.textContent = '';
            fileSuccess.style.display = 'none';
        }
    }

    // Attach change event listener to the document and delegate it to .attachment-file inputs
    document.addEventListener('change', function (event) {
        if (event.target && event.target.classList.contains('attachment-file')) {
            handleFileInputChange(event);
        }
    });
});
// End - Wrong Extension Image / File Name

// Start - Duplicate Attachment Row
document.addEventListener("DOMContentLoaded", function () {
    var attachmentContainer = document.getElementById('attachmentContainer');

    // Check if attachmentContainer exists before adding event listener
    if (attachmentContainer) {
        attachmentContainer.addEventListener('click', function (e) {
            var target = e.target;

            if (target.classList.contains('add-attachment')) {
                // Clone the attachment row
                var originalRow = target.closest('.attachment-row');
                var clonedRow = originalRow.cloneNode(true);

                // Clear the input values in the cloned row and adjust visibility of action buttons
                clonedRow.querySelectorAll('input').forEach(function (input) { input.value = ''; });
                clonedRow.querySelector('.add-attachment').style.display = 'none';
                clonedRow.querySelector('.add-attachment-label').style.display = 'none';
                clonedRow.querySelector('.remove-attachment').style.display = 'inline-block';
                clonedRow.querySelector('.remove-attachment-label').style.display = 'inline';

                // Append the cloned row
                attachmentContainer.appendChild(clonedRow);
            } else if (target.classList.contains('remove-attachment')) {
                // Remove the attachment row
                target.closest('.attachment-row').remove();
            }
        });
    }
});
// End - Duplicate Attachment Row

// Start - Reporting Page
$(document).ready(function () {
    if (typeof isReportPage !== 'undefined') {
        $('#employee_id').on('change', function () {
            var tableName = $(this).find('option:selected').data('type');
            $('#table_name').val(tableName);
        })
    }
});
// End - Reporting Page

// Start - Material Process Script
$(document).ready(function () {
    if (typeof isMProcessPage !== 'undefined') {
        var tableRowCount = 1;
        updateSrNumbers();
        updateGrandTotal();
        // Initially disable the add button
        $('#addBtn').prop('disabled', true);

        // Function to update available stock
        function updateAvailableStock(materialId) {
            // Calculate total quantity of the same material present in the table
            var totalQuantityInTable = 0;
            var totalQuantityInTable2 = 0;
            $('#items-table tbody tr').each(function () {
                var rowMaterialId = $(this).find('input[name="amaterial_id[]"]').val();
                var rowMaterialId2 = $(this).find('input[name="bmaterial_id[]"]').val();
                if (rowMaterialId === materialId) {
                    totalQuantityInTable += parseInt($(this).find('input[name="aquantity[]"]').val());
                }
                if (rowMaterialId2 === materialId) {
                    totalQuantityInTable2 += parseInt($(this).find('input[name="bquantity[]"]').val());
                }
            });

            // Find the stock item for the selected material
            var stockItem = stockData.find(item => item.material_id == materialId);
            if (stockItem) {
                var availableStock = stockItem.total_received + stockItem.stockIn - stockItem.total_returned - stockItem.stockOut - totalQuantityInTable;
                $('#available_stock').val(parseFloat(availableStock.toFixed(4)));
                // $('#available_stock').val(availableStock);
            } else {
                // If no stock item found, set available stock to 0
                $('#available_stock').val(0);
            }
        }

        // Function to check if all three fields have data
        function checkFields() {
            var amaterialId = $('select[name="samaterial_id[]"]').val();
            var aquantity = $('input[name="saquantity"]').val();
            var bmaterialId = $('select[name="sbmaterial_id[]"]').val();
            var bquantity = $('input[name="sbquantity"]').val();
            var price = $('input[name="price"]').val();
            return (amaterialId && aquantity && bmaterialId && bquantity && price);
        }

        // Event listener for select2:select event on material ID
        $('#amaterial_id').on('select2:select', function (e) {
            var selectedMaterialId = e.params.data.id;
            updateAvailableStock(selectedMaterialId);
        });

        // Enable/disable add button based on field values
        $('select[name="samaterial_id[]"], input[name="saquantity"], select[name="sbmaterial_id[]"], input[name="sbquantity"], input[name="price"]').on('change keyup', function () {
            $('#addBtn').prop('disabled', !checkFields());
        });

        $('#addBtn').on('click', function () {
            var amaterialId = $('select[name="samaterial_id[]"]').val();
            var afullText = $('select[name="samaterial_id[]"] option:selected').text();
            var aparts = afullText.split('|');
            var amaterialName = aparts[0].trim();
            var amaterialUnit = aparts[1].trim();
            var aquantity = $('input[name="saquantity"]').val();
            var bmaterialId = $('select[name="sbmaterial_id[]"]').val();
            var bfullText = $('select[name="sbmaterial_id[]"] option:selected').text();
            var bparts = bfullText.split('|');
            var bmaterialName = bparts[0].trim();
            var bmaterialUnit = bparts[1].trim();
            var bquantity = $('input[name="sbquantity"]').val();
            var price = $('input[name="price"]').val();
            // var availableStock = parseInt($('#available_stock').val());
            var availableStock = parseFloat($('#available_stock').val()).toFixed(4);
            var total = bquantity * price;

            var existingMaterial = false;
            $('#items-table tbody tr').each(function (index, row) {
                var aexistingMaterialId = $(row).find('input[name="amaterial_id[]"]').val();
                var bexistingMaterialId = $(row).find('input[name="bmaterial_id[]"]').val();
                if (aexistingMaterialId == amaterialId || bexistingMaterialId == bmaterialId) {
                    existingMaterial = true;
                    return false; // Exit the loop
                }
            });

            // if (aquantity > availableStock) {
            if (parseFloat(aquantity) > parseFloat(availableStock)) {
                console.log(aquantity);
                console.log(availableStock);

                alert("Quantity cannot be greater than available stock.");
                return;
            }

            if (existingMaterial) {
                // Material already exists, show an alert or handle the situation
                alert('Material already exists in the table.');
            } else {
                // Material does not exist, add row to table
                var newRow = '<tr>' +
                    '<td>' + tableRowCount + '</td>' +
                    '<td>' + amaterialName + '<input type="hidden" name="amaterial_name[]" value="' + amaterialName + '"><input type="hidden" name="amaterial_id[]" value="' + amaterialId + '"></td>' +
                    '<td>' + bmaterialName + '<input type="hidden" name="bmaterial_name[]" value="' + bmaterialName + '"><input type="hidden" name="bmaterial_id[]" value="' + bmaterialId + '"></td>' +
                    '<td>' + aquantity + ' ' + amaterialUnit + '<input type="hidden" name="aquantity[]" value="' + aquantity + '"></td>' +
                    '<td>' + bquantity + ' ' + bmaterialUnit + '<input type="hidden" name="bquantity[]" value="' + bquantity + '"></td>' +
                    '<td>' + price + '<input type="hidden" name="price[]" value="' + price + '"></td>' +
                    '<td>' + total + '<input type="hidden" name="total[]" value="' + total + '"></td>' +
                    '<td><button class="deleteRowBtn btn btn-danger">X</button></td>' +
                    '</tr>';

                $('#items-table tbody').append(newRow);

                tableRowCount++;

                // Update available stock
                updateAvailableStock(amaterialId);

                // Disable Btn & Reset input field
                $('#addBtn').prop('disabled', true);
                $('input[name="saquantity"]').val('0');
                $('input[name="sbquantity"]').val('0');
                $('input[name="price"]').val('0');
                $('select[name="samaterial_id[]"]').val('').trigger('change');
                $('select[name="sbmaterial_id[]"]').val('').trigger('change');
                updateSrNumbers();
                updateGrandTotal();
            }
        });

        // Delete row when delete button is clicked
        $(document).on('click', '.deleteRowBtn', function () {
            var row = $(this).closest('tr');
            var amaterialId = row.find('input[name="amaterial_id[]"]').val();
            var aquantity = parseInt(row.find('input[name="aquantity[]"]').val());
            row.remove();
            updateSrNumbers();
            updateGrandTotal();
            // Update available stock after removing the row
            updateAvailableStock(amaterialId);
        });

        // Function to update Sr. numbers
        function updateSrNumbers() {
            $('#items-table tbody tr').each(function (index) {
                $(this).find('td:first').text(index + 1);
            });
        }

        // Function to calculate and update grand total
        function updateGrandTotal() {
            var grandTotal = 0;
            $('#items-table tbody tr').each(function () {
                var total = parseFloat($(this).find('input[name="total[]"]').val());
                if (!isNaN(total)) {
                    grandTotal += total;
                }
            });
            $('#grandTotal').text('Rupee: ' + grandTotal.toFixed(2));
        }

        $('#submitBtn').on('click', function () {
            // Gather data from table and submit
            var tableData = [];
            $('#items-table tbody tr').each(function (index, row) {
                var rowData = {
                    'amaterial_id': $(row).find('input[name="amaterial_id[]"]').val(),
                    'amaterial_name': $(row).find('input[name="amaterial_name[]"]').val(),
                    'aquantity': $(row).find('input[name="aquantity[]"]').val(),
                    'bmaterial_id': $(row).find('input[name="bmaterial_id[]"]').val(),
                    'bmaterial_name': $(row).find('input[name="bmaterial_name[]"]').val(),
                    'bquantity': $(row).find('input[name="bquantity[]"]').val(),
                    'price': $(row).find('input[name="price[]"]').val(),
                    'total': $(row).find('input[name="total[]"]').val()
                };
                tableData.push(rowData);
            });
        });
    }
});


// Start - Purchase Script
$(document).ready(function () {
    if (typeof isPurchasePage !== 'undefined') {
        var tableRowCount = 1;
        updateSrNumbers();
        updateGrandTotal();
        // Initially disable the add button
        $('#addBtn').prop('disabled', true);

        function fetchMaterialQty() {
            var materialId = $('#material_id').val();
            var orderId = $('#order_id').val();

            if (materialId && orderId) {
                $.ajax({
                    url: ajaxPMQtyUrl,
                    type: "GET",
                    data: { materialId: materialId, orderId: orderId },
                    dataType: "json",
                    success: function (response) {
                        $('#materialQty').val(response.data);
                    },
                    error: function (xhr, status, error) {
                        console.error("An error occurred: " + error);
                    }
                });
            }
        }

        $('#material_id').on('change', fetchMaterialQty);
        $('#order_id').on('change', fetchMaterialQty);

        // Function to check if all three fields have data
        function checkFields() {
            var materialId = $('select[name="smaterial_id[]"]').val();
            var quantity = $('input[name="quantity"]').val();
            var price = $('input[name="price"]').val();
            return (materialId && quantity && price);
        }

        // Enable/disable add button based on field values
        $('select[name="smaterial_id[]"], input[name="quantity"], input[name="price"]').on('change keyup', function () {
            $('#addBtn').prop('disabled', !checkFields());
        });

        $('#addBtn').on('click', function () {
            var materialId = $('select[name="smaterial_id[]"]').val();
            var fullText = $('select[name="smaterial_id[]"] option:selected').text();
            var parts = fullText.split('|');
            var materialName = parts[0].trim();
            var quantity = $('input[name="quantity"]').val();
            var price = $('input[name="price"]').val();
            var total = quantity * price;

            var existingMaterial = false;
            $('#items-table tbody tr').each(function (index, row) {
                var existingMaterialId = $(row).find('input[name="material_id[]"]').val();
                if (existingMaterialId == materialId) {
                    existingMaterial = true;
                    return false; // Exit the loop
                }
            });

            if (existingMaterial) {
                // Material already exists, show an alert or handle the situation
                alert('Material already exists in the table.');
            } else {
                // Material does not exist, add row to table
                var newRow = '<tr>' +
                    '<td>' + tableRowCount + '</td>' +
                    '<td>' + materialName + '<input type="hidden" name="material_name[]" value="' + materialName + '"><input type="hidden" name="material_id[]" value="' + materialId + '"></td>' +
                    '<td>' + quantity + '<input type="hidden" name="quantity[]" value="' + quantity + '"></td>' +
                    '<td>' + price + '<input type="hidden" name="price[]" value="' + price + '"></td>' +
                    '<td>' + total + '<input type="hidden" name="total[]" value="' + total + '"><input type="hidden" name="product_type_id[]" value="0"><input type="hidden" name="product_stage_id[]" value="0"></td>' +
                    '<td><button class="deleteRowBtn btn btn-danger">X</button></td>' +
                    '</tr>';

                $('#items-table tbody').append(newRow);

                tableRowCount++;

                // Disable Btn & Reset input field
                $('#addBtn').prop('disabled', true);
                $('input[name="quantity"]').val('0');
                $('input[name="price"]').val('0');
                $('select[name="smaterial_id[]"]').val('').trigger('change');
                updateSrNumbers();
                updateGrandTotal();
            }
        });

        // Delete row when delete button is clicked
        $(document).on('click', '.deleteRowBtn', function () {
            $(this).closest('tr').remove();
            updateSrNumbers();
            updateGrandTotal();
        });

        // Function to update Sr. numbers
        function updateSrNumbers() {
            $('#items-table tbody tr').each(function (index) {
                $(this).find('td:first').text(index + 1);
            });
        }

        // Function to calculate and update grand total
        function updateGrandTotal() {
            var grandTotal = 0;
            $('#items-table tbody tr').each(function () {
                var total = parseFloat($(this).find('input[name="total[]"]').val());
                if (!isNaN(total)) {
                    grandTotal += total;
                }
            });
            $('#grandTotal').text('Rupee: ' + grandTotal.toFixed(2));
        }

        $('#submitBtn').on('click', function () {
            // Gather data from table and submit
            var tableData = [];
            $('#items-table tbody tr').each(function (index, row) {
                var rowData = {
                    'material_id': $(row).find('input[name="material_id[]"]').val(),
                    'material_name': $(row).find('input[name="material_name[]"]').val(),
                    'quantity': $(row).find('input[name="quantity[]"]').val(),
                    'price': $(row).find('input[name="price[]"]').val(),
                    'total': $(row).find('input[name="total[]"]').val()
                };
                tableData.push(rowData);
            });
        });
    }
});
// Print functionality has been moved to print.js

// Start - Order Script
$(document).ready(function () {
    if (typeof isOrderPage !== 'undefined') {
        var tableRowCount = 1;
        updateSrNumbers();
        updateGrandTotal();
        // Initially disable the add button
        $('#addBtn').prop('disabled', true);

        // AJAX call to update product stages based on selected product type
        $('#product_type_id').on('change', function () {
            var productId = $(this).val();
            $.ajax({
                url: ajaxPSUrl,
                type: "GET",
                data: { productId: productId },
                dataType: "json",
                success: function (response) {
                    $('#stage_id').empty().append('<option disabled>Select Product Stage</option>');
                    response.data.forEach(function (item) {
                        var optionText = item.name;
                        $('#stage_id').append(new Option(optionText, item.head_id));
                    });
                    // Re-initialize select2 for the updated product cost select element
                    initializeSelect2();
                },
            });
        });

        // Function to check if all three fields have data
        function checkFields() {
            var productId = $('select[name="product_type_id"]').val();
            var stageId = $('select[name="stage_id"]').val();
            var quantity = $('input[name="quantity"]').val();
            var price = $('input[name="price"]').val();
            return (productId && stageId && quantity && price > 0);
            // return (productId && stageId && quantity && price);
        }

        function toggleAddButton() {
            $('#addBtn').prop('disabled', !checkFields());
        }

        // Enable/disable add button based on field values
        $('select[name="product_type_id"], select[name="stage_id"], input[name="quantity"], input[name="price"]').on('change keyup', toggleAddButton);


        $('#addBtn').on('click', function () {
            var productId = $('select[name="product_type_id"]').val();
            var productName = $('select[name="product_type_id"] option:selected').text();
            var stageId = $('select[name="stage_id"]').val();
            var stageName = $('select[name="stage_id"] option:selected').text();
            var quantity = $('input[name="quantity"]').val();
            var price = $('input[name="price"]').val();
            var price2 = $('input[name="price2"]').val() || '0';
            var total = quantity * price;
            var existingProduct = false;

            $('#items-table tbody tr').each(function (index, row) {
                var existingProductId = $(row).find('input[name="product_type_id[]"]').val();
                var existingStageId = $(row).find('input[name="product_stage_id[]"]').val();
                if (existingProductId == productId && existingStageId == stageId) {
                    existingProduct = true;
                    return false; // Exit the loop
                }
            });

            if (existingProduct) {
                // Product already exists, show an alert or handle the situation
                alert('Product already exists in the table.');
            } else {
                // Product does not exist, add row to table
                var newRow = '<tr>' +
                    '<td>' + tableRowCount + '</td>' +
                    '<td>' + productName + '<input type="hidden" name="name[]" value="' + productName + '"><input type="hidden" name="product_type_id[]" value="' + productId + '"></td>' +
                    '<td>' + stageName + '<input type="hidden" name="sname[]" value="' + stageName + '"><input type="hidden" name="product_stage_id[]" value="' + stageId + '"></td>' +
                    '<td>' + quantity + '<input type="hidden" name="quantity[]" value="' + quantity + '"></td>' +
                    '<td>' + price + '<input type="hidden" name="price[]" value="' + price + '"></td>' +
                    '<td>' + price2 + '<input type="hidden" name="price2[]" value="' + price2 + '"></td>' +
                    '<td>' + total + '<input type="hidden" name="total[]" value="' + total + '"></td>' +
                    '<td><button class="deleteRowBtn btn btn-danger">X</button></td>' +
                    '</tr>';

                $('#items-table tbody').append(newRow);

                tableRowCount++;

                // Disable Btn & Reset input field
                $('#addBtn').prop('disabled', true);
                $('input[name="quantity"]').val('0');
                $('input[name="price"]').val('0');
                $('input[name="price2"]').val('0');
                $('select[name="product_type_id[]"]').val('').trigger('change');
                updateSrNumbers();
                updateGrandTotal();
            }
        });

        // Delete row when delete button is clicked
        $(document).on('click', '.deleteRowBtn', function () {
            $(this).closest('tr').remove();
            updateSrNumbers();
            updateGrandTotal();
        });

        // Function to update Sr. numbers
        function updateSrNumbers() {
            $('#items-table tbody tr').each(function (index) {
                if (isOrderPage) {
                    $(this).find('td:first').text(index);
                } else {
                    $(this).find('td:first').text(index + 1);
                }
            });
        }

        // Function to calculate and update grand total
        function updateGrandTotal() {
            var grandTotal = 0;
            $('#items-table tbody tr').each(function () {
                var total = parseFloat($(this).find('input[name="total[]"]').val());
                if (!isNaN(total)) {
                    grandTotal += total;
                }
            });
            $('#grandTotal').text('Rupee: ' + grandTotal.toFixed(2));
        }

        $('#submitBtn').on('click', function () {
            // Gather data from table and submit
            var tableData = [];
            $('#items-table tbody tr').each(function (index, row) {
                var rowData = {
                    'product_type_id': $(row).find('input[name="product_type_id[]"]').val(),
                    'product_name': $(row).find('input[name="name[]"]').val(),
                    'quantity': $(row).find('input[name="quantity[]"]').val(),
                    'price': $(row).find('input[name="price[]"]').val(),
                    'price2': $(row).find('input[name="price2[]"]').val(),
                    'total': $(row).find('input[name="total[]"]').val()
                };
                tableData.push(rowData);
            });
        });
    }
});
// End - Order Script

// Start - Product Purchase Script
$(document).ready(function () {
    if (typeof isProductPurchasePage !== 'undefined') {
        var tableRowCount = 1;
        updateSrNumbers();
        updateGrandTotal();
        // Initially disable the add button
        $('#addBtn').prop('disabled', true);

        // AJAX call to update product stages based on selected product type
        $('#product_type_id').on('change', function () {
            var productId = $(this).val();
            $.ajax({
                url: ajaxPSUrl,
                type: "GET",
                data: { productId: productId },
                dataType: "json",
                success: function (response) {
                    $('#stage_id').empty().append('<option disabled>Select Product Stage</option>');
                    response.data.forEach(function (item) {
                        var optionText = item.name;
                        $('#stage_id').append(new Option(optionText, item.head_id));
                    });
                    // Re-initialize select2 for the updated product cost select element
                    initializeSelect2();
                },
            });
        });

        // Function to check if all three fields have data
        function checkFields() {
            var productId = $('select[name="product_type_id"]').val();
            var stageId = $('select[name="stage_id"]').val();
            var quantity = $('input[name="quantity"]').val();
            var price = $('input[name="price"]').val();
            return (productId && stageId && quantity && price > 0);
            // return (productId && stageId && quantity && price);
        }

        function toggleAddButton() {
            $('#addBtn').prop('disabled', !checkFields());
        }

        // Enable/disable add button based on field values
        $('select[name="product_type_id"], select[name="stage_id"], input[name="quantity"], input[name="price"]').on('change keyup', toggleAddButton);


        $('#addBtn').on('click', function () {
            var productId = $('select[name="product_type_id"]').val();
            var productName = $('select[name="product_type_id"] option:selected').text();
            var stageId = $('select[name="stage_id"]').val();
            var stageName = $('select[name="stage_id"] option:selected').text();
            var quantity = $('input[name="quantity"]').val();
            var price = $('input[name="price"]').val();
            var price2 = $('input[name="price2"]').val() || '0';
            var total = quantity * price;
            var existingProduct = false;

            $('#items-table tbody tr').each(function (index, row) {
                var existingProductId = $(row).find('input[name="product_type_id[]"]').val();
                var existingStageId = $(row).find('input[name="product_stage_id[]"]').val();
                if (existingProductId == productId && existingStageId == stageId) {
                    existingProduct = true;
                    return false; // Exit the loop
                }
            });

            if (existingProduct) {
                // Product already exists, show an alert or handle the situation
                alert('Product already exists in the table.');
            } else {
                // Product does not exist, add row to table
                var newRow = '<tr>' +
                    '<td>' + tableRowCount + '</td>' +
                    '<td>' + productName + '<input type="hidden" name="name[]" value="' + productName + '"><input type="hidden" name="product_type_id[]" value="' + productId + '"></td>' +
                    '<td>' + stageName + '<input type="hidden" name="sname[]" value="' + stageName + '"><input type="hidden" name="product_stage_id[]" value="' + stageId + '"></td>' +
                    '<td>' + quantity + '<input type="hidden" name="quantity[]" value="' + quantity + '"></td>' +
                    '<td>' + price + '<input type="hidden" name="price[]" value="' + price + '"></td>' +
                    '<td>' + total + '<input type="hidden" name="total[]" value="' + total + '"><input type="hidden" name="material_id[]" value="0"></td>' +
                    '<td><button class="deleteRowBtn btn btn-danger">X</button></td>' +
                    '</tr>';

                $('#items-table tbody').append(newRow);

                tableRowCount++;

                // Disable Btn & Reset input field
                $('#addBtn').prop('disabled', true);
                $('input[name="quantity"]').val('0');
                $('input[name="price"]').val('0');
                $('select[name="product_type_id[]"]').val('').trigger('change');
                updateSrNumbers();
                updateGrandTotal();
            }
        });

        // Delete row when delete button is clicked
        $(document).on('click', '.deleteRowBtn', function () {
            $(this).closest('tr').remove();
            updateSrNumbers();
            updateGrandTotal();
        });

        // Function to update Sr. numbers
        function updateSrNumbers() {
            $('#items-table tbody tr').each(function (index) {
                if (isProductPurchasePage) {
                    $(this).find('td:first').text(index);
                } else {
                    $(this).find('td:first').text(index + 1);
                }
            });
        }

        // Function to calculate and update grand total
        function updateGrandTotal() {
            var grandTotal = 0;
            $('#items-table tbody tr').each(function () {
                var total = parseFloat($(this).find('input[name="total[]"]').val());
                if (!isNaN(total)) {
                    grandTotal += total;
                }
            });
            $('#grandTotal').text('Rupee: ' + grandTotal.toFixed(2));
        }

        $('#submitBtn').on('click', function () {
            // Gather data from table and submit
            var tableData = [];
            $('#items-table tbody tr').each(function (index, row) {
                var rowData = {
                    'product_type_id': $(row).find('input[name="product_type_id[]"]').val(),
                    'product_name': $(row).find('input[name="name[]"]').val(),
                    'quantity': $(row).find('input[name="quantity[]"]').val(),
                    'price': $(row).find('input[name="price[]"]').val(),
                    'price2': $(row).find('input[name="price2[]"]').val(),
                    'total': $(row).find('input[name="total[]"]').val()
                };
                tableData.push(rowData);
            });
        });
    }
});
// End - Product Purchase Script

// Start - Product Material Script
$(document).ready(function () {
    if (typeof isPMPage !== 'undefined') {
        var bqtyInput = document.getElementById('bqty');
        var mqtyInput = document.getElementById('mqty');

        bqtyInput.addEventListener('input', function () {
            var bqtyValue = parseFloat(bqtyInput.value);
            if (!isNaN(bqtyValue) && bqtyValue !== 0) {
                var result = 1 / bqtyValue;
                mqtyInput.value = result.toFixed(20);
            } else {
                mqtyInput.value = '';
            }
        });
    }
    if (typeof isPMPageOld !== 'undefined') { // This is Updated No Need of JS
        var tableRowCount = 1;
        updateSrNumbers();

        // Event listener for change in product_type_id
        $('select[name="product_type_id"]').change(function () {
            if ($('#items-table tbody tr').length > 0) {
                if (!confirm('Changing the product type will clear the table. Are you sure you want to proceed?')) {
                    $(this).val($(this).data('previous')).trigger('change.select2');
                    return;
                }
            }
            clearTable();
        });

        // Initially disable the add button
        $('#addBtn').prop('disabled', true);

        // Function to check if both fields have data
        function checkFields() {
            var productId = $('select[name="product_type_id"]').val();
            var materialId = $('select[name="material_id"]').val();
            var quantity = $('input[name="quantity"]').val();
            if (isPMPage) {
                return (materialId && quantity);
            } else {
                return (productId && materialId && quantity);
            }
        }

        // Enable/disable add button based on field values
        $('select[name="material_id"], input[name="quantity"]').on('change keyup', function () {
            $('#addBtn').prop('disabled', !checkFields());
        });

        $('#addBtn').on('click', function () {
            var materialId = $('select[name="material_id"]').val();
            var materialName = $('select[name="material_id"] option:selected').text();
            var quantity = $('input[name="quantity"]').val();

            var existingMaterial = false;
            $('#items-table tbody tr').each(function (index, row) {
                var existingMaterialId = $(row).find('input[name="material_id[]"]').val();
                if (existingMaterialId == materialId) {
                    existingMaterial = true;
                    return false; // Exit the loop
                }
            });

            if (existingMaterial) {
                // Material already exists, show an alert or handle the situation
                alert('Material already exists in the table.');
            } else {
                // Material does not exist, add row to table
                var newRow = '<tr>' +
                    '<td>' + tableRowCount + '</td>' +
                    '<td>' + materialName + '<input type="hidden" name="material_name[]" value="' + materialName + '"><input type="hidden" name="material_id[]" value="' + materialId + '"></td>' +
                    '<td>' + quantity + '<input type="hidden" name="quantity[]" value="' + quantity + '"></td>' +
                    '<td><button class="deleteRowBtn btn btn-danger">X</button></td>' +
                    '</tr>';

                $('#items-table tbody').append(newRow);

                tableRowCount++;

                // Disable Btn & Reset input field
                $('#addBtn').prop('disabled', true);
                $('input[name="quantity"]').val('0');
                $('select[name="material_id"]').val('').trigger('change');
                updateSrNumbers();
            }
        });

        // Delete row when delete button is clicked
        $(document).on('click', '.deleteRowBtn', function () {
            $(this).closest('tr').remove();
            updateSrNumbers();
        });

        // Function to update Sr. numbers
        function updateSrNumbers() {
            $('#items-table tbody tr').each(function (index) {
                if (isPMPage) {
                    $(this).find('td:first').text(index);
                } else {
                    $(this).find('td:first').text(index + 1);
                }
            });
        }

        // Function to clear the table if product_type_id changes
        function clearTable() {
            $('#items-table tbody').empty();
            updateSrNumbers();
        }

        $('#submitBtn').on('click', function () {
            // Gather data from table and submit
            var tableData = [];
            $('#items-table tbody tr').each(function (index, row) {
                var rowData = {
                    'material_id': $(row).find('input[name="material_id[]"]').val(),
                    'material_name': $(row).find('input[name="material_name[]"]').val(),
                    'quantity': $(row).find('input[name="quantity[]"]').val(),
                };
                tableData.push(rowData);
            });
        });
    }
});
// End - Product Material Script

// Start - Receive Material Script
$(document).ready(function () {
    if (typeof isReceivePage !== 'undefined') {
        document.addEventListener('input', function (event) {
            if (event.target.classList.contains('receive-qty')) {
                var row = event.target.closest('tr');
                var received = parseInt(row.querySelector('.received').innerText, 10) || 0;
                var total = parseInt(row.querySelector('.total').innerText, 10) || 0;
                var enteredQuantity = parseInt(event.target.value, 10) || 0;

                // Calculate the remaining quantity
                var remaining = total - received - enteredQuantity;

                // Ensure the entered quantity does not exceed the remaining quantity
                var maxQuantity = total - received;
                event.target.setAttribute('max', maxQuantity);

                // If the entered quantity exceeds the max, adjust it to the max
                if (enteredQuantity > maxQuantity) {
                    event.target.value = maxQuantity;
                    enteredQuantity = maxQuantity;
                    remaining = 0;
                } else if (remaining < 0) {
                    remaining = 0;
                }

                // Update the remaining input value
                var remainingInput = row.querySelector('.remaining');
                remainingInput.value = remaining;

                // Initialize pending_qty with receive-qty
                var pendingQtyInput = row.querySelector('.pending_qty');
                pendingQtyInput.value = enteredQuantity;

                // Reset approved and rejected quantities
                var approvedQtyInput = row.querySelector('.approved_qty');
                var rejectedQtyInput = row.querySelector('.rejected_qty');
                approvedQtyInput.value = 0;
                rejectedQtyInput.value = 0;
            } else if (event.target.classList.contains('approved_qty') || event.target.classList.contains('rejected_qty')) {
                var row = event.target.closest('tr');
                var receiveQty = parseInt(row.querySelector('.receive-qty').value, 10) || 0;
                var approvedQty = parseInt(row.querySelector('.approved_qty').value, 10) || 0;
                var rejectedQty = parseInt(row.querySelector('.rejected_qty').value, 10) || 0;

                // Calculate the total approved and rejected quantity
                var totalHandledQty = approvedQty + rejectedQty;

                // Ensure the sum of approved and rejected does not exceed received quantity
                if (totalHandledQty > receiveQty) {
                    var excessQty = totalHandledQty - receiveQty;
                    if (event.target.classList.contains('approved_qty')) {
                        event.target.value = approvedQty - excessQty;
                        approvedQty -= excessQty;
                    } else {
                        event.target.value = rejectedQty - excessQty;
                        rejectedQty -= excessQty;
                    }
                    totalHandledQty = approvedQty + rejectedQty;
                }

                // Update pending quantity
                var pendingQtyInput = row.querySelector('.pending_qty');
                pendingQtyInput.value = receiveQty - totalHandledQty;
            }
        });
    }
});
// End - Receive Material Script

// Start - Return Material Script
$(document).ready(function () {
    if (typeof isReturnPage !== 'undefined') {
        var returnQuantityInputs = document.querySelectorAll('.return-qty');
        returnQuantityInputs.forEach(function (input) {
            var row = input.closest('tr');
            var receiveQuantityCell = row.querySelector('td:nth-child(5)');
            var receiveQuantity = parseInt(receiveQuantityCell.textContent.split('/')[1].trim());
            var returnedQuantity = parseInt(receiveQuantityCell.textContent.split('/')[0].trim()) || 0;
            var availableToReturn = receiveQuantity - returnedQuantity;

            input.addEventListener('input', function () {
                var inputValue = parseInt(this.value.trim()) || 0;
                if (inputValue > availableToReturn) {
                    this.value = availableToReturn;
                }
            });

            input.setAttribute('max', availableToReturn);
        });

        var ereturnQuantityInputs = document.querySelectorAll('.ereturn-qty');
        ereturnQuantityInputs.forEach(function (input) {
            var row = input.closest('tr');
            var receiveQuantityCell = row.querySelector('td:nth-child(5)');
            var availableToReturn = parseInt(receiveQuantityCell.textContent);

            input.addEventListener('input', function () {
                var inputValue = parseInt(this.value.trim()) || 0;
                if (inputValue > availableToReturn) {
                    this.value = availableToReturn;
                }
            });

            input.setAttribute('max', availableToReturn);
        });
    }
});
// End - Return Material Script

// Start - Delivery Script
$(document).ready(function () {
    if (typeof isDeliveryPage !== 'undefined') {
        // Start - Box Quantity in Delivery
        function updateTotals() {
            let totalQuantity = 0;
            let totalBqty = 0;

            $('.quantity-input').each(function () {
                let quantity = parseFloat($(this).val());
                if (!isNaN(quantity)) {
                    totalQuantity += quantity;
                }
            });

            $('.bqty-input').each(function () {
                let bqty = Math.ceil($(this).val());
                if (!isNaN(bqty)) {
                    totalBqty += bqty;
                }
            });

            $('#totalQuantity').text(totalQuantity.toFixed(2));
            $('#totalBqty').text(totalBqty);
            $('#totalBqty2').text(totalBqty);
            calculateDifference();
        }

        function calculateDifference() {
            var tQty = parseFloat($('#tQty').text()) || 0;
            var totalBqty2 = parseInt($('#totalBqty2').text()) || 0;
            var difference = tQty - totalBqty2;
            $('#remBqty').text(difference.toFixed(2));
        }

        // Box Qty in Delivery
        $(document).on('input', '.quantity-input', function () {
            let quantity = $(this).val();
            let bqty = $(this).data('bqty');
            let calculatedBqty = quantity * bqty;
            // Use ceil to get whole boxes needed (round up for partial boxes)
            calculatedBqty = Math.ceil(calculatedBqty);
            $(this).closest('tr').find('.bqty-input').val(calculatedBqty);
            updateTotals();
        });

        // Optional: Max button functionality
        $(document).on('click', '.maxBtn', function () {
            let quantityInput = $(this).closest('tr').find('.quantity-input');
            let maxStock = parseFloat(quantityInput.attr('max')) || 0;
            let remainingToDeliver = parseFloat(quantityInput.data('remaining')) || 0;
            // Set to the lesser of available stock or remaining to be delivered
            let maxQuantity = Math.min(maxStock, remainingToDeliver);
            quantityInput.val(maxQuantity).trigger('input');
        });

        // Optional: Zero button functionality
        $(document).on('click', '.zeroBtn', function () {
            let quantityInput = $(this).closest('tr').find('.quantity-input');
            quantityInput.val(0).trigger('input');
        });

        // Initial calculation of totals on page load
        updateTotals();

        // Note: bqty validation removed - deliveries now allowed for products without bqty defined
        // The box quantity calculation functionality above remains for products that do have bqty defined

        // End - Box Quantity in Delivery

        // Start - Factory to Container Delivery
        $('#addVBtn').on('click', function () {
            var vehicleNo = $('input[name="svehicle_no"]').val();
            var rowQuantities = [];

            // Iterate through each input field starting with name "sqty"
            $('input[name^="sQty"]').each(function () {
                var qty = $(this).val().trim(); // Trim to remove leading/trailing spaces
                rowQuantities.push(qty === '' ? '0' : qty); // Replace empty value with '0'
            });

            // Calculate total quantity
            var totalQty = rowQuantities.reduce((a, b) => parseInt(a) + parseInt(b), 0);

            // Join row quantities with '|' as the joiner
            var joinedRowQuantities = rowQuantities.join('|');

            // Create new row for the table
            var newRow = '<tr>' +
                '<td>' + ($('#vehicles-table tbody tr').length + 1) + '</td>' +
                '<td>' + vehicleNo + '<input type="hidden" name="vehicle_no[]" value="' + vehicleNo + '"><input type="hidden" name="rowQty[]" value="' + joinedRowQuantities + '"></td>';

            // Add individual row quantities
            for (var i = 0; i < rowQuantities.length; i++) {
                newRow += '<td>' + rowQuantities[i] + '</td>';
            }

            // Add total quantity
            newRow += '<td>' + totalQty + '<input type="hidden" name="totalQty[]" value="' + totalQty + '"></td>' +
                '<td><button class="deleteRowBtn btn btn-danger">X</button></td>' +
                '</tr>';

            // Append the new row to the table
            $('#vehicles-table tbody').append(newRow);

            // Update the grand total
            updateGrandTotal();
            calculateDifference();

            // Reset fields
            $('input[name="svehicle_no"]').val('');
            $('input[name^="sQty"]').val('0');
        });

        // Delete row when delete button is clicked
        $(document).on('click', '.deleteRowBtn', function () {
            $(this).closest('tr').remove();
            updateGrandTotal();
        });

        // Initial Calculation
        updateGrandTotal();

        // Function to update grand total
        function updateGrandTotal() {
            var grandTotal = 0;
            $('#vehicles-table tbody tr').each(function (index, row) {
                grandTotal += parseInt($(row).find('td:last').prev().text());
            });
            $('#tQty').text(grandTotal);
        }

        // End - Factory to Container Delivery


        // Start - Adding Vehicle to Table
        var tableRowCount = 1;
        updateSrNumbers();
        // Initially disable the add button
        $('#addBtn').prop('disabled', true);

        // Function to check if both fields have data [Delivery Container / Vehicle]
        function checkFields() {
            var materialId = $('select[name="smaterial_id[]"]').val();
            var quantity = $('input[name="squantity"]').val();
            return (materialId && quantity);
        }

        // Enable/disable add button based on field values
        $('select[name="smaterial_id[]"], input[name="squantity"]').on('change keyup', function () {
            $('#addBtn').prop('disabled', !checkFields());
        });

        $('#addBtn').on('click', function () {
            var materialId = $('select[name="smaterial_id[]"]').val();
            var materialName = $('select[name="smaterial_id[]"] option:selected').text();
            var quantity = $('input[name="squantity"]').val();

            var existingMaterial = false;
            $('#items-table tbody tr').each(function (index, row) {
                var existingMaterialId = $(row).find('input[name="material_id[]"]').val();
                if (existingMaterialId == materialId) {
                    existingMaterial = true;
                    return false; // Exit the loop
                }
            });

            if (existingMaterial) {
                // Material already exists, show an alert or handle the situation
                alert('Material already exists in the table.');
            } else {
                // Material does not exist, add row to table
                var newRow = '<tr>' +
                    '<td>' + tableRowCount + '</td>' +
                    '<td>' + materialName + '<input type="hidden" name="material_name[]" value="' + materialName + '"><input type="hidden" name="material_id[]" value="' + materialId + '"><input type="hidden" name="product_type_id[]" value="0"><input type="hidden" name="stage_id[]" value="0"></td>' +
                    '<td>' + quantity + '<input type="hidden" name="quantity[]" value="' + quantity + '"></td>' +
                    '<td><button class="deleteRowBtn btn btn-danger">X</button></td>' +
                    '</tr>';
                $('#items-table tbody').append(newRow);

                tableRowCount++;

                // Disable Btn & Reset input field
                $('#addBtn').prop('disabled', true);
                $('input[name="squantity"]').val('0');
                $('select[name="smaterial_id[]"]').val('').trigger('change');
                updateSrNumbers();
            }
        });

        // Delete row when delete button is clicked
        $(document).on('click', '.deleteRowBtn', function () {
            $(this).closest('tr').remove();
            updateSrNumbers();
        });

        // Function to update Sr. numbers
        function updateSrNumbers() {
            $('#items-table tbody tr').each(function (index) {
                $(this).find('td:first').text(index + 1);
            });
        }

        $('#submitBtn').on('click', function () {
            // Gather data from table and submit
            var tableData = [];
            $('#items-table tbody tr').each(function (index, row) {
                var rowData = {
                    'material_id': $(row).find('input[name="material_id[]"]').val(),
                    'material_name': $(row).find('input[name="material_name[]"]').val(),
                    'quantity': $(row).find('input[name="quantity[]"]').val()
                };
                tableData.push(rowData);
            });
        });
        // End - Adding Vehicle to Table


        // Start - Adding Expense to Table
        var expenseRowCount = 1;

        // Function to add a new row to the table
        function addExpenseRow() {
            var expense = $('select[name="spayee_id"] option:selected').text();
            var expenseId = $('select[name="spayee_id"]').val();
            var paymentType = $('select[name="sbank_id"] option:selected').text();
            var paymentTypeId = $('select[name="sbank_id"]').val();
            var amount = $('input[name="sdebit"]').val();
            var detail = $('textarea[name="sremarks[]"]').val();

            // Add row to the table
            var newRow = '<tr>' +
                '<td>' + expenseRowCount + '</td>' +
                '<td>' + expense + '<input type="hidden" name="payee_id[]" value="' + expenseId + '"></td>' +
                '<td>' + paymentType + '<input type="hidden" name="bank_id[]" value="' + paymentTypeId + '"></td>' +
                '<td>' + amount + '<input type="hidden" name="debit[]" value="' + amount + '"></td>' +
                '<td>' + detail + '<input type="hidden" name="remarks[]" value="' + detail + '"></td>' +
                '<td><button class="delete-expense-row btn btn-danger">X</button></td>' +
                '</tr>';

            $('#expense-table tbody').append(newRow);

            // Increment row count
            updateExpenseRowNumbers();
            // expenseRowCount++;

            // Clear input fields
            clearExpenseFields();
        }

        // Event listener for "Add" button
        $('#addExpenseBtn').on('click', function () {
            if (checkExpenseFields()) {
                addExpenseRow();
            } else {
                alert('Please fill in all fields.');
            }
        });

        // Delete row from expense table
        $(document).on('click', '.delete-expense-row', function () {
            $(this).closest('tr').remove();
            updateExpenseRowNumbers();
        });

        // Function to update row numbers
        function updateExpenseRowNumbers() {
            $('#expense-table tbody tr').each(function (index) {
                $(this).find('td:first').text(index + 1);
            });
            expenseRowCount = $('#expense-table tbody tr').length + 1;
        }

        // Function to check if all fields are filled for expense
        function checkExpenseFields() {
            var payeeId = $('select[name="spayee_id"]').val();
            // var bankId = $('select[name="sbank_id"]').val();
            var debit = $('input[name="sdebit"]').val();
            // var remarks = $('textarea[name="sremarks[]"]').val();
            return (payeeId && debit);
        }

        // Function to clear expense input fields
        function clearExpenseFields() {
            $('select[name="spayee_id"]').val('').trigger('change');
            $('select[name="sbank_id"]').val('');
            $('input[name="sdebit"]').val('');
            $('textarea[name="sremarks[]"]').val('');
        }
        // End - Adding Expense to Table


        // Set max quantity based on selected material
        $('#materialSelect').change(function () {
            var available = $(this).find('option:selected').data('available');
            $('#vehicleQty').attr('max', available);
        });

        // Automatically set quantity to max if typed value is greater
        $('#vehicleQty').on('input', function () {
            var max = parseInt($(this).attr('max'), 10);
            var currentVal = parseInt($(this).val(), 10);
            if (currentVal > max) {
                $(this).val(max);
            }
        });

        // Prevent form submission when clicking on the buttons
        $(document).on('click', '.maxBtn', function (event) {
            event.preventDefault(); // Prevent default form submission
            var maxVal = parseInt($(this).closest('tr').find('.quantity-input').attr('max'));
            $(this).closest('tr').find('.quantity-input').val(maxVal);
        });

        $(document).on('click', '.zeroBtn', function (event) {
            event.preventDefault(); // Prevent default form submission
            $(this).closest('tr').find('.quantity-input').val(0);
        });

        // Automatically change input value to max if higher value is typed
        $('.quantity-input').on('input', function () {
            var maxVal = parseInt($(this).attr('max'), 10); // Get the max attribute
            var currentVal = parseInt($(this).val(), 10); // Get current value
            if (currentVal > maxVal) {
                $(this).val(maxVal); // Set value to max if exceeded
            }
        });
    }
});
// End - Delivery Script

// Start - Make Qty 0
var makeZeroForm = document.getElementById('makeZero');
if (makeZeroForm) {
    makeZeroForm.addEventListener('submit', function (event) {
        event.preventDefault(); // Prevent default form submission
        document.querySelectorAll('.receive-qty, .return-qty').forEach(input => input.value = input.value.trim() === '' ? '0' : input.value);
        makeZeroForm.submit(); // Submit the form
    });
}
// End - Make Qty 0

// Start - Issue Material Script
$(document).ready(function () {
    if (typeof isIssuePage !== 'undefined') {
        // Function to initialize select2
        function initializeSelect2() {
            $('.select2').select2();
        }

        // Getting Table name
        $('#employee_id').on('change', function () {
            var tableName = $(this).find('option:selected').data('type');
            $('#table_name').val(tableName);
        });

        updateSerialNumbers(); // Update serial numbers after deleting a row

        // Function to update available stock
        function updateAvailableStock(materialId) {
            // Calculate total quantity of the same material present in the table
            var totalQuantityInTable = 0;
            var totalQuantityInTable2 = 0;
            $('#items-table tbody tr').each(function () {
                var rowMaterialId = $(this).find('input[name="material_id[]"]').val();
                var rowMaterialId2 = $(this).find('input[name="hidden_material_id[]"]').val();
                if (rowMaterialId === materialId) {
                    totalQuantityInTable += parseInt($(this).find('input[name="quantity[]"]').val());
                } if (rowMaterialId2 === materialId) {
                    totalQuantityInTable2 += parseInt($(this).find('input[name="hidden_quantity[]"]').val());
                }
            });

            // Find the stock item for the selected material
            var stockItem = stockData.find(item => item.material_id == materialId);
            if (stockItem) {
                var availableStock = stockItem.total_received + stockItem.stockIn - stockItem.total_returned - stockItem.stockOut + totalQuantityInTable2 - totalQuantityInTable;
                $('#available_stock').val(availableStock);
                // $('#available_stock').val(isFinite(availableStock) ? availableStock : 0);
            } else {
                // If no stock item found, set available stock to 0
                $('#available_stock').val(0);
            }
        }

        // Function to update available product stock
        function updateAvailablePStock() {
            var productId = $('#product_type_id').val();
            var selectedStageIds = $('#stage_id').val();

            if (!selectedStageIds || !productId) {
                $('#available_pstock').val(0);
                return;
            }

            var minAvailableStock = Infinity;

            selectedStageIds.forEach(function (stageId) {
                var totalQuantityInTable = 0;
                var totalQuantityInTable2 = 0;

                $('#items-table tbody tr').each(function () {
                    var rowProductId = $(this).find('input[name="product_type_id[]"]').val();
                    var rowStageId = $(this).find('input[name="stage_id[]"]').val();
                    var rowProductId2 = $(this).find('input[name="hidden_product_type_id[]"]').val();
                    var rowStageId2 = $(this).find('input[name="hidden_stage_id[]"]').val();

                    if (rowProductId === productId && rowStageId === stageId) {
                        totalQuantityInTable += parseInt($(this).find('input[name="quantity[]"]').val());
                    }

                    if (rowProductId2 === productId && rowStageId2 === stageId) {
                        totalQuantityInTable2 += parseInt($(this).find('input[name="hidden_quantity[]"]').val());
                    }
                });

                var stockItem = pstockData.find(item => item.product_type_id == productId && item.stage_id == stageId);

                if (stockItem) {
                    var availableStock = stockItem.stockIn - stockItem.stockOut - totalQuantityInTable + totalQuantityInTable2;
                    minAvailableStock = Math.min(minAvailableStock, availableStock);
                }
            });

            $('#available_pstock').val(isFinite(minAvailableStock) ? minAvailableStock : 0);
        }

        // Function to update available stock
        function updateAvailableGStock(igroupId) {
            // Fetch the stock item from gstockData
            var stockItem = gstockData[igroupId];
            if (stockItem) {
                $('#available_gstock').val(stockItem.max_issuable);
            } else {
                $('#available_gstock').val(0);
            }
        }

        // Manually trigger AJAX request to load products based on preselected order on page load
        if (typeof isIGroupPage === 'undefined') {
            loadProductsBasedOnOrder();
        }

        // Function to load products based on preselected order
        function loadProductsBasedOnOrder() {
            var orderId = $('#order_id').val();

            // If "Default Issuance" is selected (orderId = 0), load all available products with stock
            if (orderId == 0 || orderId === '') {
                loadAllProductsWithStock();
                return;
            }

            $.ajax({
                // Use original route for order-based products
                url: ajaxPTUrl.replace('ajaxPTStock', 'ajaxPT'),
                type: "GET",
                data: { orderId: orderId },
                dataType: "json",
                success: function (response) {
                    $('#product_type_id').empty().append('<option value="" disabled selected>Select Product</option>');
                    response.data.forEach(function (item) {
                        // Handle undefined size names
                        var sizeText = item.hname ? ' - Size ' + item.hname : (item.sname ? ' - Size ' + item.sname : '');
                        var optionText = item.article_no + sizeText;
                        $('#product_type_id').append(new Option(optionText, item.product_type_id));
                    });
                    $('#product_type_id').trigger('change');
                }
            });
        }

        // Function to load all available products for Default Production
        function loadAllProducts() {
            $.ajax({
                url: '/ajax/getAllProducts', // We'll need to create this route
                type: "GET",
                dataType: "json",
                success: function (response) {
                    $('#product_type_id').empty().append('<option value="" disabled selected>Select Product</option>');
                    response.data.forEach(function (item) {
                        // Handle undefined size names
                        var sizeText = item.hname ? ' - Size ' + item.hname : (item.sname ? ' - Size ' + item.sname : '');
                        var optionText = item.article_no + sizeText;
                        $('#product_type_id').append(new Option(optionText, item.product_type_id));
                    });
                    $('#product_type_id').trigger('change');
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('Failed to load all products:', textStatus, errorThrown);
                }
            });
        }

        // Function to load all products (for issuance forms - regardless of stock)
        function loadAllProductsWithStock() {
            $.ajax({
                url: '/ajax/getAllProducts', // Get ALL products regardless of stock
                type: "GET",
                dataType: "json",
                success: function (response) {
                    $('#product_type_id').empty().append('<option value="" disabled selected>Select Product</option>');
                    response.data.forEach(function (item) {
                        // Handle undefined size names
                        var sizeText = item.hname ? ' (Size: ' + item.hname + ')' : '';
                        var optionText = item.article_no + ' - ' + item.name + sizeText;
                        $('#product_type_id').append(new Option(optionText, item.product_type_id));
                    });
                    $('#product_type_id').trigger('change');
                }
            });
        }

        // Function to update issuance groups based on selected order
        function updateIssuanceGroups() {
            var orderId = $('#order_id').val();
            $.ajax({
                url: ajaxIGUrl,
                type: "GET",
                data: { orderId: orderId },
                dataType: "json",
                success: function (response) {
                    var igroupSelect = $('#igroup_id');
                    igroupSelect.empty().append('<option value="" disabled selected>Select Group</option>'); // Corrected here

                    // Check if response data exists and is an array
                    if (response.data && Array.isArray(response.data)) {
                        response.data.forEach(function (item) {
                            var optionText = item.igroup_no;
                            igroupSelect.append(new Option(optionText, item.igroup_id));
                        });

                        // Re-initialize select2 for the updated select element
                        igroupSelect.select2();
                    } else {
                        console.error('Unexpected response format:', response);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('AJAX call failed:', textStatus, errorThrown);
                }
            });
        }

        // Event listener for change in order ID
        $('#order_id').on('change', function () {
            if ($('#items-table tbody tr').length > 0) {
                if (!confirm('Changing the order will clear the table. Are you sure you want to proceed?')) {
                    $(this).val($(this).data('previous')).trigger('change.select2');
                    return;
                }
            }
            $('#items-table tbody').empty();
            // if (typeof isIGroupPage !== 'undefined') {
            // Removed showing Groups based on Orders
            if (typeof isIGroupPage === 'undefined') {
                updateIssuanceGroups();
            }
            // Removed showing Products based on Orders
            if (typeof isIGroupPage === 'undefined') {
                loadProductsBasedOnOrder();
            }
        });

        // Define stageStockInfo outside of the event listeners
        var stageStockInfo = {};

        // Load all products with stock on page load for issuance forms
        if (typeof isIGroupPage === 'undefined' && typeof isIssuePage !== 'undefined') {
            // This is an issuance page, load all products with stock
            loadAllProductsWithStock();
        }

        // Define componentStockInfo for tracking component stock
        var componentStockInfo = {};

        // Event listener for change in product type
        if (typeof isIGroupPage === 'undefined') {
            // Issuance Page
            $('#product_type_id').on('change', function () {
                var productId = $(this).val();
                $.ajax({
                    url: ajaxPMUrl,
                    type: "GET",
                    data: { productId: productId },
                    dataType: "json",
                    success: function (response) {
                        var materialSelect = $('#material_id');
                        materialSelect.empty().append('<option value="" disabled selected>Select Material</option>');
                        response.materials.forEach(function (item) {
                            var optionText = item.material_no + ' - ' + item.name;
                            materialSelect.append(new Option(optionText, item.material_id));
                        });
                        materialSelect.trigger('change');

                        // Clear previous data in stageStockInfo
                        stageStockInfo = {};

                        // Populate stageSelect and stageStockInfo
                        var stageSelect = $('#stage_id');
                        stageSelect.empty();
                        response.stockItems.forEach(function (item) {
                            var optionText = item.article_no + ' - Size ' + item.sname + ' - ' + item.stname;
                            stageSelect.append(new Option(optionText, item.stage_id));
                            stageStockInfo[item.stage_id] = {
                                stock: item.stockIn - item.stockOut,
                                stname: item.stname,
                            };
                        });

                        // Populate product components dropdown
                        componentStockInfo = {};
                        var componentSelect = $('#component_id');
                        componentSelect.empty().append('<option value="" disabled selected>Select Product Component</option>');
                        if (response.productComponents && response.productComponents.length > 0) {
                            response.productComponents.forEach(function (item) {
                                var optionText = item.article_no + ' - ' + item.product_name + ' (' + item.size_name + ') - Req: ' + item.quantity;
                                var option = new Option(optionText, item.component_product_type_id);
                                $(option).data('stock', item.available_stock);
                                $(option).data('article', item.article_no);
                                $(option).data('name', item.product_name);
                                $(option).data('size', item.size_name);
                                $(option).data('required', item.quantity);
                                componentSelect.append(option);
                                componentStockInfo[item.component_product_type_id] = {
                                    stock: item.available_stock,
                                    article: item.article_no,
                                    name: item.product_name,
                                    size: item.size_name,
                                    required: item.quantity
                                };
                            });
                        }
                        componentSelect.trigger('change');
                    },
                });
            });
        } else { // Issuance Group Page
            $('#product_type_id').on('change', function () {
                var productId = $(this).val();
                if (!productId) { return; }
                // Perform both AJAX requests concurrently
                $.when(
                    $.ajax({
                        url: ajaxPMUrl,
                        type: "GET",
                        data: { productId: productId },
                        dataType: "json"
                    }),
                    $.ajax({
                        url: ajaxPSUrl,
                        type: "GET",
                        data: { productId: productId },
                        dataType: "json"
                    })
                ).then(function (pmResponse, psResponse) {
                    // Handle response for product materials
                    var materialSelect = $('#material_id');
                    materialSelect.empty().append('<option value="" disabled selected>Select Material</option>');
                    pmResponse[0].materials.forEach(function (item) {
                        var optionText = item.material_no + ' - ' + item.name;
                        materialSelect.append(new Option(optionText, item.material_id));
                    });
                    materialSelect.trigger('change');

                    // Handle response for product stages
                    var stageSelect = $('#stage_id');
                    stageSelect.empty().append('<option disabled>Select Product Stage</option>');
                    psResponse[0].data.forEach(function (item) {
                        var optionText = item.name;
                        stageSelect.append(new Option(optionText, item.head_id));
                    });

                    // Re-initialize select2 for the updated select element
                    $('#stage_id').select2();
                    $('#material_id').select2();
                });
            });
        }

        // Event listener for change in material to get the following
        // Required Material Qty, Issued Qty, Remaining Qty to Issue Against Order 
        $('#material_id').on('change', function () {
            var materialId = $(this).val();
            var orderId = $('#order_id').val();
            $.ajax({
                url: ajaxMQtyUrl,
                type: "GET",
                data: { materialId: materialId, orderId: orderId },
                dataType: "json",
                success: function (response) {
                    $('#materialQty').val(response.data);
                    var parts = response.data.split('|');
                    var val0 = parts[0].trim();
                    var val1 = parts[1].trim();
                    var val2 = parts[2].trim();
                    $('#materialQty0').val(val0);
                    $('#materialQty1').val(val1);
                    $('#materialQty2').val(val2);
                },
            });
        });

        // Required Material Qty, Issued Qty, Remaining Qty to Issue Against Specific Article 
        $('#material_id').on('change', function () {
            var materialId = $(this).val();
            var orderId = $('#order_id').val();
            var productId = $('#product_type_id').val();
            $.ajax({
                url: ajaxAMQtyUrl,
                type: "GET",
                data: { materialId: materialId, orderId: orderId, productId: productId },
                dataType: "json",
                success: function (response) {
                    $('#articleQty').val(response.data);
                },
            });
        });

        // Required Material Qty, Issued Qty, Remaining Qty to Issue Against Specific Article Type
        $('#material_id').on('change', function () {
            var materialId = $(this).val();
            var orderId = $('#order_id').val();
            var productId = $('#product_type_id').val();
            $.ajax({
                url: ajaxATMQtyUrl,
                type: "GET",
                data: { materialId: materialId, orderId: orderId, productId: productId },
                dataType: "json",
                success: function (response) {
                    $('#articleTQty').val(response.data);
                },
            });
        });

        // Event listener for select2:select event on material ID
        $('#material_id').on('select2:select', function (e) {
            var selectedMaterialId = e.params.data.id;
            if (typeof isIGroupPage === 'undefined') {
                updateAvailableStock(selectedMaterialId);
            }
        });

        // Update available product stock when stage_id changes
        $('#stage_id').on('change', function () {
            if (typeof isIGroupPage === 'undefined') {
                updateAvailablePStock();
            }
        });

        // Event listener for select2:select event on igroup ID
        $('#igroup_id').on('select2:select', function (e) {
            var selectedIGroupId = e.params.data.id;
            updateAvailableGStock(selectedIGroupId);
        });

        // Event listener for click on add button
        $('#addBtnMaterial').click(function () {
            var productId = $('#product_type_id').val();
            var materialId = $('#material_id').val();
            var materialText = $('#material_id option:selected').text();
            var productName = $('#product_type_id option:selected').text();
            var quantity = parseFloat($('input[name="quantityMaterial"]').val());
            // var quantity = parseInt($('input[name="quantityMaterial"]').val());
            var availableStock = parseInt($('#available_stock').val());
            // var stageId = $('#stage_id').val() || "0"; 
            var stageId = "0";

            if (!materialId || !quantity) return;

            if (typeof isIGroupPage === 'undefined') { // Disable Stock Check for IGroup
                if (quantity > availableStock) {
                    alert("Quantity cannot be greater than available stock.");
                    return;
                }
            }

            var isDuplicate = false;
            $('#items-table tbody tr').each(function () {
                var existingProductId = $(this).find('input[name="product_type_id[]"]').val();
                var existingMaterialId = $(this).find('input[name="material_id[]"]').val();
                if (existingProductId === productId && existingMaterialId === materialId) {
                    isDuplicate = true;
                    return false;
                }
            });

            if (isDuplicate) {
                alert("This combination of product and material is already added to the table.");
                return;
            }

            var updatedStock = availableStock - quantity;
            $('#available_stock').val(updatedStock);

            var srNo = $('#items-table tbody tr').length + 1;

            var markup = `<tr>
                <td>${srNo}</td>
                <td>${productName}<input type="hidden" name="product_type_id[]" value="${productId}"><input type="hidden" name="stage_id[]" value="${stageId}"></td>
                <td>${materialText}<input type="hidden" name="material_id[]" value="${materialId}"></td>
                <td>${quantity}<input type="hidden" name="quantity[]" value="${quantity}"></td>
                <td><button type="button" class="btn btn-danger deleteRow">X</button></td>
            </tr>`;

            $('#items-table tbody').append(markup);

            $('#material_id').val(null).trigger('change');
            $('input[name="quantityMaterial"]').val('');
            $('input[name="available_stock"]').val('');
            updateSerialNumbers();
        });

        // Event listener for click on add button
        $('#addBtnStage').click(function () {
            var productId = $('#product_type_id').val();
            var materialId = $('#material_id').val() || "0";
            var productName = $('#product_type_id option:selected').text();
            var quantity = parseFloat($('input[name="quantityStage"]').val());
            // var quantity = parseInt($('input[name="quantityStage"]').val());
            var stageIds = $('#stage_id').val() || [];

            if (!stageIds.length || !quantity) return;

            // Check available stock for each stage
            if (typeof isIGroupPage === 'undefined') { // Disable Stock Check for IGroup 
                for (let i = 0; i < stageIds.length; i++) {
                    var stageId = stageIds[i];
                    var availableStock = parseFloat($('#available_pstock').val());

                    if (isNaN(availableStock) || quantity > availableStock) {
                        alert("Quantity cannot be greater than available stock.");
                        return;
                    }
                }
            }


            if (typeof isIGroupPage === 'undefined') { // Issuance Page
                // Check for duplicates
                for (let i = 0; i < stageIds.length; i++) {
                    var stageId = stageIds[i];
                    var isDuplicate = false;

                    $('#items-table tbody tr').each(function () {
                        var existingProductId = $(this).find('input[name="product_type_id[]"]').val();
                        var existingStageId = $(this).find('input[name="stage_id[]"]').val();

                        if (existingProductId === productId && existingStageId === stageId) {
                            isDuplicate = true;
                            return false; // Exit the loop
                        }
                    });

                    if (isDuplicate) {
                        alert("One or more selected stages are already added to the table.");
                        return;
                    }
                }
            } else { // Issuance Group Page
                var isDuplicate = false;
                var stageId = $('#stage_id').val() || "0";
                $('#items-table tbody tr').each(function () {
                    var existingProductId = $(this).find('input[name="product_type_id[]"]').val();
                    var existingStageId = $(this).find('input[name="stage_id[]"]').val();
                    if (existingProductId === productId && existingStageId === stageId) {
                        isDuplicate = true;
                        return false;
                    }
                });

                if (isDuplicate) {
                    alert("This product stage is already added to the table.");
                    return;
                }
            }

            // Add the product stages to the table
            if (typeof isIGroupPage === 'undefined') { // Issuance Page
                var srNo = $('#items-table tbody tr').length + 1;
                stageIds.forEach(function (stageId) {
                    var stageName = stageStockInfo[stageId].stname;
                    var markup = `<tr>
                        <td>${srNo}</td>
                        <td>${productName}<input type="hidden" name="product_type_id[]" value="${productId}"></td>
                        <td>${stageName}<input type="hidden" name="stage_id[]" value="${stageId}"><input type="hidden" name="material_id[]" value="${materialId}"></td>
                        <td>${quantity}<input type="hidden" name="quantity[]" value="${quantity}"></td>
                        <td><button type="button" class="btn btn-danger deletepRow">X</button></td>
                    </tr>`;
                    $('#items-table tbody').append(markup);
                    srNo++;
                });
            } else { // Issuance Group Page
                var stageId = $('#stage_id').val() || "0";
                var stageName = $('#stage_id option:selected').text();
                var srNo = $('#items-table tbody tr').length + 1;
                var markup = `<tr>
                    <td>${srNo}</td>
                    <td>${productName}<input type="hidden" name="product_type_id[]" value="${productId}"><input type="hidden" name="stage_id[]" value="${stageId}"></td>
                    <td>${stageName}<input type="hidden" name="material_id[]" value="${materialId}"></td>
                    <td>${quantity}<input type="hidden" name="quantity[]" value="${quantity}"></td>
                    <td><button type="button" class="btn btn-danger deletepRow">X</button></td>
                </tr>`;
                $('#items-table tbody').append(markup);
            }

            $('#stage_id').val('').trigger('change');
            $('input[name="quantityStage"]').val('');
            $('input[name="available_pstock"]').val('');
            updateSerialNumbers();
        });

        // Event listener for component selection change
        $('#component_id').on('change', function () {
            var selectedOption = $(this).find('option:selected');
            var stock = selectedOption.data('stock') || componentStockInfo[$(this).val()]?.stock || 0;
            $('#available_component_stock').val(stock);
        });

        // Event listener for add component button click
        $('#addBtnComponent').click(function () {
            var productId = $('#product_type_id').val();
            var componentId = $('#component_id').val();
            var productName = $('#product_type_id option:selected').text();
            var componentText = $('#component_id option:selected').text();
            var quantity = parseFloat($('input[name="quantityComponent"]').val());
            var availableStock = parseFloat($('#available_component_stock').val());

            if (!componentId || !quantity) {
                alert("Please select a component and enter a valid quantity.");
                return;
            }

            if (quantity > availableStock) {
                alert("Quantity cannot be greater than available stock.");
                return;
            }

            var isDuplicate = false;
            $('#items-table tbody tr').each(function () {
                var existingProductId = $(this).find('input[name="product_type_id[]"]').val();
                var existingComponentId = $(this).find('input[name="component_id[]"]').val();
                if (existingProductId === productId && existingComponentId === componentId) {
                    isDuplicate = true;
                    return false;
                }
            });

            if (isDuplicate) {
                alert("This component is already added to the table.");
                return;
            }

            var srNo = $('#items-table tbody tr').length + 1;
            var markup = `<tr>
                <td>${srNo}</td>
                <td>${productName}<input type="hidden" name="product_type_id[]" value="${productId}"><input type="hidden" name="stage_id[]" value="0"></td>
                <td><span class="badge badge-info">Component:</span> ${componentText}<input type="hidden" name="material_id[]" value="0"><input type="hidden" name="component_id[]" value="${componentId}"></td>
                <td>${quantity}<input type="hidden" name="quantity[]" value="${quantity}"></td>
                <td><button type="button" class="btn btn-danger deleteComponentRow">X</button></td>
            </tr>`;

            $('#items-table tbody').append(markup);

            $('#component_id').val(null).trigger('change');
            $('input[name="quantityComponent"]').val('');
            $('#available_component_stock').val('');
            updateSerialNumbers();
        });

        // Event listener for delete component row
        $(document).on('click', '.deleteComponentRow', function () {
            $(this).closest('tr').remove();
            updateSerialNumbers();
        });

        // Event listener for click on add button
        $('#addBtnIGroup').click(function () {
            var igroupId = $('#igroup_id').val();
            var igroupText = $('#igroup_id option:selected').text();
            var quantity = parseInt($('input[name="quantityMaterial"]').val());
            var availableStock = parseInt($('#available_gstock').val());

            if (!igroupId || !quantity) {
                alert("Please select a group and enter a valid quantity.");
                return;
            }

            if (quantity > availableStock) {
                alert("Quantity cannot be greater than available stock.");
                return;
            }

            var isDuplicate = false;
            $('#items-table tbody tr').each(function () {
                var existingIGroupId = $(this).find('input[name="igroup_id[]"]').val();
                if (existingIGroupId == igroupId) {
                    isDuplicate = true;
                    return false;
                }
            });

            if (isDuplicate) {
                alert("This group is already added to the table.");
                return;
            }

            var updatedStock = availableStock - quantity;
            $('#available_gstock').val(updatedStock);

            var srNo = $('#items-table tbody tr').length + 1;

            var markup = `
            <tr>
                <td>${srNo}</td>
                <td>${igroupText}<input type="hidden" name="igroup_id[]" value="${igroupId}"></td>
                <td>${quantity}<input type="hidden" name="quantity[]" value="${quantity}"></td>
                <td><button type="button" class="btn btn-danger deleteRow">X</button></td>
            </tr>
            `;

            $('#items-table tbody').append(markup);

            $('#igroup_id').val(null).trigger('change');
            $('input[name="quantityMaterial"]').val('');
            $('#available_pstock').val('');

            updateSerialNumbers();
        });

        // Event listener for click on add button
        $('#addBtnMM').click(function () {
            var materialId = $('#material_id').val();
            var materialText = $('#material_id option:selected').text();
            var quantity = parseInt($('input[name="quantityMaterial"]').val());
            var availableStock = parseInt($('#available_stock').val());

            if (!materialId || !quantity) {
                alert("Please select a material and enter a valid quantity.");
                return;
            }

            if (quantity > availableStock) {
                alert("Quantity cannot be greater than available stock.");
                return;
            }

            var isDuplicate = false;
            $('#items-table tbody tr').each(function () {
                var existingMaterialId = $(this).find('input[name="material_id[]"]').val();
                if (existingMaterialId == materialId) {
                    isDuplicate = true;
                    return false;
                }
            });

            if (isDuplicate) {
                alert("This material is already added to the table.");
                return;
            }

            var updatedStock = availableStock - quantity;
            $('#available_stock').val(updatedStock);

            var srNo = $('#items-table tbody tr').length + 1;

            var markup = `
              <tr>
                <td>${srNo}</td>
                <td>${materialText}<input type="hidden" name="material_id[]" value="${materialId}"></td>
                <td>${quantity}<input type="hidden" name="quantity[]" value="${quantity}"></td>
                <td><button type="button" class="btn btn-danger deleteRow">X</button></td>
              </tr>
            `;

            $('#items-table tbody').append(markup);

            $('#material_id').val(null).trigger('change');
            $('input[name="quantityMaterial"]').val('');
            $('#available_stock').val('');

            updateSerialNumbers();
        });

        // Function to update serial numbers
        function updateSerialNumbers() {
            $('#items-table tbody tr:not(#hiddentr)').each(function (index) {
                $(this).find('td:first').text(index + 1);
            });
        }

        // Event listener for click on delete button in table row
        $(document).on('click', '.deleteRow, .deletepRow', function () {
            var quantityToRemove = parseInt($(this).closest('tr').find('input[name="quantity[]"]').val());
            var currentAvailableStock = $(this).hasClass('deleteRow') ? parseInt($('#available_stock').val()) : parseInt($('#available_pstock').val());
            var updatedStock = currentAvailableStock + quantityToRemove;
            $(this).hasClass('deleteRow') ? $('#available_stock').val(updatedStock) : $('#available_pstock').val(updatedStock);
            $(this).closest('tr').remove();
            updateSerialNumbers();
        });

        initializeSelect2();
    }
});
// End - Issue Material Script

// Start - PTC (Process Travel Card) Script
$(document).ready(function () {
    if (typeof isPtcPage !== 'undefined') {
        // Initialize select2
        $('.select2').select2();

        // Getting Table name for employee/vendor
        $('#employee_id').on('change select2:select', function () {
            var tableName = $(this).find('option:selected').data('type');
            $('#table_name').val(tableName);
        });

        // Function to update available material stock
        function updateAvailableStock(materialId) {
            var totalQuantityInTable = 0;
            $('#items-table tbody tr').each(function () {
                var rowMaterialId = $(this).find('input[name="material_id[]"]').val();
                if (rowMaterialId == materialId) {
                    totalQuantityInTable += parseFloat($(this).find('input[name="quantity[]"]').val()) || 0;
                }
            });

            var stockItem = stockData.find(item => item.material_id == materialId);
            if (stockItem) {
                var availableStock = parseFloat(stockItem.total_received || 0) + parseFloat(stockItem.stockIn || 0)
                    - parseFloat(stockItem.total_returned || 0) - parseFloat(stockItem.stockOut || 0)
                    - totalQuantityInTable;
                $('#available_stock').val(parseFloat(availableStock.toFixed(4)));
            } else {
                $('#available_stock').val(0);
            }
        }

        // Function to update available product stock for PTC
        function updateAvailablePStock() {
            var selectedStageIds = $('#stage_id').val();

            if (!selectedStageIds || !ptcProductTypeId || ptcProductTypeId == '0') {
                $('#available_pstock').val(0);
                return;
            }

            var minAvailableStock = Infinity;

            selectedStageIds.forEach(function (stageId) {
                var totalQuantityInTable = 0;
                $('#items-table tbody tr').each(function () {
                    var rowProductId = $(this).find('input[name="product_type_id[]"]').val();
                    var rowStageId = $(this).find('input[name="stage_id[]"]').val();
                    if (rowProductId == ptcProductTypeId && rowStageId == stageId) {
                        totalQuantityInTable += parseInt($(this).find('input[name="quantity[]"]').val()) || 0;
                    }
                });

                var stockItem = pstockData.find(item => item.product_type_id == ptcProductTypeId && item.stage_id == stageId);
                if (stockItem) {
                    var availableStock = parseInt(stockItem.stockIn || 0) - parseInt(stockItem.stockOut || 0) - totalQuantityInTable;
                    minAvailableStock = Math.min(minAvailableStock, availableStock);
                } else {
                    minAvailableStock = Math.min(minAvailableStock, 0);
                }
            });

            $('#available_pstock').val(isFinite(minAvailableStock) ? minAvailableStock : 0);
        }

        // Event listener for select2:select event on material ID
        $('#material_id').on('select2:select', function (e) {
            var selectedMaterialId = e.params.data.id;
            updateAvailableStock(selectedMaterialId);
        });

        // Also handle regular change event for material_id
        $('#material_id').on('change', function () {
            var materialId = $(this).val();
            if (materialId) {
                updateAvailableStock(materialId);

                // Material Quantity Tracking for PTC Creation/Issuance Page (addPTC)
                if (typeof isPtcIssuancePage !== 'undefined' && typeof ajaxMQtyUrl !== 'undefined') {
                    var ptcOrderId = typeof orderId !== 'undefined' ? orderId : 0;
                    var ptcProductId = typeof ptcProductTypeId !== 'undefined' ? ptcProductTypeId : 0;

                    // If no order (Default PTC), show N/A since we can't calculate requirements
                    if (!ptcOrderId || ptcOrderId == '0' || ptcOrderId == 0) {
                        $('#materialQty').val('N/A - No Order');
                        $('#articleQty').val('N/A - No Order');
                        $('#articleTQty').val('N/A - No Order');
                        return;
                    }

                    // Required | Issued | To Issue (Complete Order)
                    $.ajax({
                        url: ajaxMQtyUrl,
                        type: "GET",
                        data: { materialId: materialId, orderId: ptcOrderId },
                        dataType: "json",
                        success: function (response) {
                            $('#materialQty').val(response.data);
                        },
                    });
                    // Required | Issued | To Issue (Selected Article)
                    $.ajax({
                        url: ajaxAMQtyUrl,
                        type: "GET",
                        data: { materialId: materialId, orderId: ptcOrderId, productId: ptcProductId },
                        dataType: "json",
                        success: function (response) {
                            $('#articleQty').val(response.data);
                        },
                    });
                    // Required | Issued | To Issue (Selected Article Size)
                    $.ajax({
                        url: ajaxATMQtyUrl,
                        type: "GET",
                        data: { materialId: materialId, orderId: ptcOrderId, productId: ptcProductId },
                        dataType: "json",
                        success: function (response) {
                            $('#articleTQty').val(response.data);
                        },
                    });
                }
            } else {
                $('#available_stock').val('');
            }
        });

        // Update available product stock when stage_id changes
        $('#stage_id').on('change', function () {
            updateAvailablePStock();
        });

        // Function to update serial numbers
        function updateSerialNumbers() {
            $('#items-table tbody tr:not(#hiddentr)').each(function (index) {
                $(this).find('td:first').text(index + 1);
            });
        }

        // Add material to table
        $('#addBtnMaterial').on('click', function () {
            var materialId = $('#material_id').val();
            var materialText = $('#material_id option:selected').text();
            var parts = materialText.split('|');
            var materialName = parts[0] ? parts[0].trim() : '';
            var materialUnit = parts[1] ? parts[1].trim() : '';
            var quantity = parseFloat($('#quantityMaterial').val()) || 0;
            var availableStock = parseFloat($('#available_stock').val()) || 0;

            if (!materialId || quantity <= 0) {
                alert('Please select a material and enter a valid quantity.');
                return;
            }

            if (quantity > availableStock) {
                alert('Quantity cannot exceed available stock.');
                return;
            }

            // Check for duplicate
            var exists = false;
            $('#items-table tbody tr').each(function () {
                if ($(this).find('input[name="material_id[]"]').val() == materialId &&
                    $(this).find('input[name="product_type_id[]"]').val() == '0') {
                    exists = true;
                    return false;
                }
            });

            if (exists) {
                alert('Material already added.');
                return;
            }

            var srNo = $('#items-table tbody tr').length + 1;
            var newRow = '<tr>' +
                '<td>' + srNo + '</td>' +
                '<td>Material<input type="hidden" name="product_type_id[]" value="0"><input type="hidden" name="stage_id[]" value="0"></td>' +
                '<td>' + materialName + '<input type="hidden" name="material_id[]" value="' + materialId + '"></td>' +
                '<td>' + quantity + ' ' + materialUnit + '<input type="hidden" name="quantity[]" value="' + quantity + '"></td>' +
                '<td><button type="button" class="btn btn-danger deleteRow">X</button></td>' +
                '</tr>';

            $('#items-table tbody').append(newRow);

            // Reset fields and update available stock
            $('#material_id').val(null).trigger('change');
            $('#quantityMaterial').val('');
            $('#available_stock').val('');
            updateSerialNumbers();
        });

        // Add product stage to table
        $('#addBtnStage').on('click', function () {
            var stageIds = $('#stage_id').val() || [];
            var quantity = parseInt($('#quantityStage').val()) || 0;

            if (!stageIds.length || quantity <= 0) {
                alert('Please select at least one stage and enter a valid quantity.');
                return;
            }

            var availableStock = parseInt($('#available_pstock').val()) || 0;
            if (quantity > availableStock) {
                alert('Quantity cannot exceed available stock.');
                return;
            }

            // Check for duplicates and add rows
            for (var i = 0; i < stageIds.length; i++) {
                var stageId = stageIds[i];
                var stageName = $('#stage_id option[value="' + stageId + '"]').text();
                var isDuplicate = false;

                $('#items-table tbody tr').each(function () {
                    var existingProductId = $(this).find('input[name="product_type_id[]"]').val();
                    var existingStageId = $(this).find('input[name="stage_id[]"]').val();
                    if (existingProductId == ptcProductTypeId && existingStageId == stageId) {
                        isDuplicate = true;
                        return false;
                    }
                });

                if (isDuplicate) {
                    alert('Stage "' + stageName + '" is already added to the table.');
                    return;
                }

                var srNo = $('#items-table tbody tr').length + 1;
                var newRow = '<tr>' +
                    '<td>' + srNo + '</td>' +
                    '<td>' + ptcProductName + '<input type="hidden" name="product_type_id[]" value="' + ptcProductTypeId + '"><input type="hidden" name="material_id[]" value="0"></td>' +
                    '<td>' + stageName + '<input type="hidden" name="stage_id[]" value="' + stageId + '"></td>' +
                    '<td>' + quantity + '<input type="hidden" name="quantity[]" value="' + quantity + '"></td>' +
                    '<td><button type="button" class="btn btn-danger deletepRow">X</button></td>' +
                    '</tr>';

                $('#items-table tbody').append(newRow);
            }

            // Reset fields and update available stock
            $('#stage_id').val(null).trigger('change');
            $('#quantityStage').val('');
            $('#available_pstock').val('');
            updateSerialNumbers();
        });

        // Delete row - material
        $(document).on('click', '.deleteRow', function () {
            $(this).closest('tr').remove();
            updateSerialNumbers();
            // Update available stock after removing the row
            var materialId = $('#material_id').val();
            if (materialId) {
                updateAvailableStock(materialId);
            }
        });

        // Delete row - product
        $(document).on('click', '.deletepRow', function () {
            $(this).closest('tr').remove();
            updateSerialNumbers();
            updateAvailablePStock();
        });
    }
});
// End - PTC Script

// Start - PTC Move Stage Script (Receive + Issue)
// Also used for separate Issuance and Receiving pages
$(document).ready(function () {
    if (typeof isPtcMoveStage !== 'undefined' || typeof isPtcIssuancePage !== 'undefined' || typeof isPtcReceivingPage !== 'undefined') {

        // Initialize select2
        $('.select2').select2();

        function initializeSelect2() {
            $('.select2').select2();
        }

        // Getting Table name for employee/vendor
        $('#employee_id').on('change select2:select', function () {
            var tableName = $(this).find('option:selected').data('type');
            $('#table_name').val(tableName);
        });

        // ============== RECEIVE SECTION HANDLERS ==============

        // Material selection for receive
        $('#r_material_id').on('change select2:select', function () {
            var selectedOption = $(this).val();
            if (!selectedOption) return;
            var parts = selectedOption.split('|');
            var materialId = parts[0];
            var productId = parts[1];
            updateReceiveableStock(materialId, productId);
        });

        // Calculate receiveable stock
        function updateReceiveableStock(materialId, productId) {
            var totalQuantityInTable = 0;
            $('#receive-table tbody tr').each(function () {
                var rowMaterialId = $(this).find('input[name="r_material_id[]"]').val();
                var rowProductId = $(this).find('input[name="r_product_type_id[]"]').val();
                if (rowMaterialId === materialId && rowProductId === productId) {
                    totalQuantityInTable += parseFloat($(this).find('input[name="r_quantity[]"]').val()) || 0;
                }
            });

            var issueItem = issueItems.find(item => item.material_id && item.material_id.toString() === materialId && item.product_type_id.toString() === productId);
            var rstockItem = rstock.find(item => item.material_id && item.material_id.toString() === materialId && item.product_type_id.toString() === productId);
            var rqty = rstockItem ? parseFloat(rstockItem.rqty) : 0;

            if (issueItem) {
                var availableStock = parseFloat(issueItem.quantity) - totalQuantityInTable - rqty;
                $('#receiveable_stock').val(parseFloat(availableStock.toFixed(4)));
            } else {
                $('#receiveable_stock').val(0);
            }
        }

        // Product type change - load stages and costs
        $('#r_product_type_id').on('change select2:select', function () {
            var productId = $(this).val();
            // Load stages
            $.ajax({
                url: ajaxPSUrl,
                type: "GET",
                data: { productId: productId },
                dataType: "json",
                success: function (response) {
                    $('#r_stage_id').empty().append('<option disabled selected>Select Stage</option>');
                    response.data.forEach(function (item) {
                        $('#r_stage_id').append(new Option(item.name, item.head_id));
                    });
                    // Add Rejected option for product receiving (head_id=105)
                    $('#r_stage_id').append('<option value="105">Rejected</option>');
                    initializeSelect2();
                },
            });
            // Load costs
            $.ajax({
                url: ajaxPCUrl,
                type: "GET",
                data: { productId: productId },
                dataType: "json",
                success: function (response) {
                    $('#r_pcost_id').empty().append('<option disabled>Select Work Log</option>');
                    $('#r_pcost_id').append('<option value="0">None</option>');
                    response.data.forEach(function (item) {
                        $('#r_pcost_id').append(new Option(item.hname, item.head_id));
                    });
                    initializeSelect2();
                },
            });
        });

        // Add material to receive table
        $('#addBtnReceiveMaterial').click(function () {
            var selectedOption = $('#r_material_id option:selected').val();
            if (!selectedOption) return;
            var selectedValues = selectedOption.split('|');
            var materialId = selectedValues[0];
            var productId = selectedValues[1];
            var materialText = $('#r_material_id option:selected').text();
            var materialName = materialText.split('|')[0].trim();
            var selectedId = $('#r_material_id');
            var selectedOpt = selectedId.find('option:selected');
            var previousDisabled = selectedOpt.prevAll('option[disabled]:first').text();
            var previousDisabledValues = previousDisabled.split('|');
            var articleNo = previousDisabledValues[0].replace('========== ', '').trim();
            var articleSize = previousDisabledValues[1] ? previousDisabledValues[1].replace('Size ', '').trim() : '';
            var productName = articleNo + ' - Size ' + articleSize;
            var quantity = parseFloat($('#r_quantityMaterial').val());
            var availableStock = parseFloat($('#receiveable_stock').val());

            if (!materialId || !quantity) {
                alert("Please select a material and enter quantity.");
                return;
            }
            if (quantity > availableStock) {
                alert("Quantity cannot be greater than receiveable stock.");
                return;
            }

            var srNo = $('#receive-table tbody tr').length + 1;
            var markup = '<tr>' +
                '<td>' + srNo + '</td>' +
                '<td>' + productName + '<input type="hidden" name="r_material_id[]" value="' + materialId + '"><input type="hidden" name="r_product_type_id[]" value="' + productId + '"></td>' +
                '<td>' + materialName + '<input type="hidden" name="r_stage_id[]" value="0"></td>' +
                '<td>None<input type="hidden" name="r_work_logs[]" value="0"></td>' +
                '<td>' + quantity + '<input type="hidden" name="r_quantity[]" value="' + quantity + '"></td>' +
                '<td><button type="button" class="btn btn-danger deleteReceiveRow">X</button></td>' +
                '</tr>';

            $('#receive-table tbody').append(markup);
            $('#receiveable_stock').val(availableStock - quantity);
            $('#r_quantityMaterial').val('');
            updateReceiveSerialNumbers();
        });

        // Add product to receive table
        $('#addBtnReceiveProduct').click(function () {
            var productId = $('#r_product_type_id').val();
            var productName = $('#r_product_type_id option:selected').text();
            var stageId = $('#r_stage_id').val();
            var stageName = $('#r_stage_id option:selected').text();
            var quantity = parseInt($('#r_quantityProduct').val());
            var selectedOptions = $('#r_pcost_id').val() || [];
            var selectedOptionsText = selectedOptions.map(function (option) {
                return $('#r_pcost_id option[value="' + option + '"]').text();
            });
            var selectedOptionsString = selectedOptionsText.join(', ') || 'None';
            var idsString = selectedOptions.join('|') || '0';

            if (!productId || !quantity || !stageId) {
                alert("Please select a product, stage, and enter quantity.");
                return;
            }

            var srNo = $('#receive-table tbody tr').length + 1;
            var markup = '<tr>' +
                '<td>' + srNo + '</td>' +
                '<td>' + productName + '<input type="hidden" name="r_product_type_id[]" value="' + productId + '"><input type="hidden" name="r_material_id[]" value="0"></td>' +
                '<td>' + stageName + '<input type="hidden" name="r_stage_id[]" value="' + stageId + '"></td>' +
                '<td>' + selectedOptionsString + '<input type="hidden" name="r_work_logs[]" value="' + idsString + '"></td>' +
                '<td>' + quantity + '<input type="hidden" name="r_quantity[]" value="' + quantity + '"></td>' +
                '<td><button type="button" class="btn btn-danger deleteReceiveRow">X</button></td>' +
                '</tr>';

            $('#receive-table tbody').append(markup);
            $('#r_quantityProduct').val('');
            $('#r_pcost_id').val(null).trigger('change');
            updateReceiveSerialNumbers();
        });

        // Delete receive row
        $(document).on('click', '.deleteReceiveRow', function () {
            $(this).closest('tr').remove();
            updateReceiveSerialNumbers();
        });

        function updateReceiveSerialNumbers() {
            $('#receive-table tbody tr').each(function (index) {
                $(this).find('td:first').text(index + 1);
            });
        }
    }
});
// End - PTC Move Stage Script

// Start - Receive Issue Material Script
$(document).ready(function () {
    if (typeof isReceiveIssuePage !== 'undefined') {

        // Initialize select2 for existing select elements
        $('.select2').select2();

        // Function to initialize select2
        function initializeSelect2() {
            $('.select2').select2();
        }

        // AJAX call to update product costs based on selected product type
        $('#product_type_id').on('change', function () {
            var productId = $(this).val();
            $.ajax({
                url: ajaxPCUrl,
                type: "GET",
                data: { productId: productId },
                dataType: "json",
                success: function (response) {
                    $('#pcost_id').empty().append('<option disabled>Select Product Cost</option>');
                    $('#pcost_id').append('<option value="0">None</option>');
                    response.data.forEach(function (item) {
                        var optionText = item.hname;
                        $('#pcost_id').append(new Option(optionText, item.head_id));
                    });
                    // Re-initialize select2 for the updated product cost select element
                    initializeSelect2();
                },
            });
        });

        // AJAX call to update product stages based on selected product type
        $('#product_type_id').on('change', function () {
            var productId = $(this).val();
            $.ajax({
                url: ajaxPSUrl,
                type: "GET",
                data: { productId: productId },
                dataType: "json",
                success: function (response) {
                    $('#stage_id').empty().append('<option disabled>Select Product Stage</option>');
                    response.data.forEach(function (item) {
                        var optionText = item.name;
                        $('#stage_id').append(new Option(optionText, item.head_id));
                    });
                    $('#stage_id').append('<option value="105">Rejected</option>');
                    // Re-initialize select2 for the updated product cost select element
                    initializeSelect2();
                },
            });
        });

        // Event listener for change in material select element
        $('#material_id').change(function () {
            var selectedOption = $(this).val();
            if (!selectedOption) {
                return;
            }
            var parts = selectedOption.split('|');
            var materialId = parts[0];
            var productId = parts[1];
            updateAvailableStock(materialId, productId);

            // Material Quantity Tracking for PTC Issuance Page
            if (typeof isPtcIssuancePage !== 'undefined' && typeof ajaxMQtyUrl !== 'undefined') {
                var ptcOrderId = typeof orderId !== 'undefined' ? orderId : 0;
                // For PTC pages, use ptcProductTypeId instead of productId from dropdown
                var ptcProductId = typeof ptcProductTypeId !== 'undefined' ? ptcProductTypeId : productId;

                // If no order (Default PTC), show N/A since we can't calculate requirements
                if (!ptcOrderId || ptcOrderId == '0' || ptcOrderId == 0) {
                    $('#materialQty').val('N/A - No Order');
                    $('#articleQty').val('N/A - No Order');
                    $('#articleTQty').val('N/A - No Order');
                    return;
                }

                // Required | Issued | To Issue (Complete Order)
                $.ajax({
                    url: ajaxMQtyUrl,
                    type: "GET",
                    data: { materialId: materialId, orderId: ptcOrderId },
                    dataType: "json",
                    success: function (response) {
                        $('#materialQty').val(response.data);
                    },
                });
                // Required | Issued | To Issue (Selected Article)
                $.ajax({
                    url: ajaxAMQtyUrl,
                    type: "GET",
                    data: { materialId: materialId, orderId: ptcOrderId, productId: ptcProductId },
                    dataType: "json",
                    success: function (response) {
                        $('#articleQty').val(response.data);
                    },
                });
                // Required | Issued | To Issue (Selected Article Size)
                $.ajax({
                    url: ajaxATMQtyUrl,
                    type: "GET",
                    data: { materialId: materialId, orderId: ptcOrderId, productId: ptcProductId },
                    dataType: "json",
                    success: function (response) {
                        $('#articleTQty').val(response.data);
                    },
                });
            }
        });

        // Function to update available stock, It shows only issued Material Qty
        function updateAvailableStockOld(materialId, productId) {
            var totalQuantityInTable = 0;
            $('#items-table tbody tr').each(function () {
                var rowMaterialId = $(this).find('input[name="material_id[]"]').val();
                var rowProductId = $(this).find('input[name="product_type_id[]"]').val();

                // Checking both material ID and product ID to accurately identify the row
                if (rowMaterialId === materialId && rowProductId === productId) {
                    totalQuantityInTable += parseInt($(this).find('input[name="quantity[]"]').val()) || 0;
                }
            });

            // Find the issueItem based on both material_id and product_type_id
            var issueItem = issueItems.find(item => item.material_id.toString() === materialId && item.product_type_id.toString() === productId);
            if (issueItem) {
                var availableStock = issueItem.quantity - totalQuantityInTable;
                $('#receiveable_stock').val(availableStock > 0 ? availableStock : 0);
            } else {
                $('#receiveable_stock').val(0);
            }
        }
        // It Also Calculate material based on Received Items
        function updateAvailableStock(materialId, productId) {
            var totalQuantityInTable = 0;

            // Iterate over table rows to calculate total quantity in the table
            $('#items-table tbody tr').each(function () {
                var rowMaterialId = $(this).find('input[name="material_id[]"]').val();
                var rowProductId = $(this).find('input[name="product_type_id[]"]').val();
                // Checking both material ID and product ID to accurately identify the row
                if (rowMaterialId === materialId && rowProductId === productId) {
                    totalQuantityInTable += parseInt($(this).find('input[name="quantity[]"]').val()) || 0;
                }
            });

            // Find the issueItem based on both material_id and product_type_id
            var issueItem = issueItems.find(item => item.material_id.toString() === materialId && item.product_type_id.toString() === productId);
            // Find the matching record in rstock array
            var rstockItem = rstock.find(item => item.material_id.toString() === materialId && item.product_type_id.toString() === productId);
            // Calculate the rqty if rstockItem is found
            var rqty = rstockItem ? rstockItem.rqty : 0;

            if (issueItem) {
                var availableStock = issueItem.quantity - totalQuantityInTable - rqty;
                // $('#receiveable_stock').val(availableStock > 0 ? availableStock : 0);
                $('#receiveable_stock').val(parseFloat(availableStock.toFixed(4)));
            } else {
                $('#receiveable_stock').val(0);
            }
        }

        // Event listener for click on add button in product section
        $('#addBtnProduct').click(function () {
            var productId = $('#product_type_id').val();
            var materialId = '0';
            var productName = $('#product_type_id option:selected').text();
            var parts = productName.split('|');
            var productName = parts.slice(0, 2).join(' - ');
            var stageId = $('#stage_id').val();
            var stageName = $('#stage_id option:selected').text();
            var quantity = parseInt($('input[name="quantityProduct"]').val());

            // Retrieve all selected product costs
            var selectedOptions = $('#pcost_id').val();
            var selectedOptionsText = selectedOptions.map(option => $('#pcost_id option[value="' + option + '"]').text());
            var selectedOptionsString = selectedOptionsText.join(', ');
            var selectedIds = $('#pcost_id').val();
            var idsString = selectedIds.join('|') || 0;

            // if (!productId || !quantity || !stageId || selectedOptions.length === 0) {
            if (!productId || !quantity || !stageId) {
                // alert("Please select a product, its cost(s), specify its quantity, and stage.");
                alert("Please select a product, specify its quantity, and stage.");
                return;
            }

            // if (isProductStageCombinationExists(productId, stageId)) {
            if (isProductStageCombinationExists(productId, stageId, idsString)) {
                alert("This product and stage combination already exists in the table.");
                return;
            }

            var srNo = $('#items-table tbody tr').length + 1;

            // var pcostInputs = '';
            // selectedOptions.forEach(pcost => {
            //     pcostInputs += `<input type="hidden" name="pcost_id[]" value="${productId}|${pcost}">`;
            // });

            var markup = `<tr>
                <td>${srNo}</td>
                <td>${productName}<input type="hidden" name="product_type_id[]" value="${productId}"><input type="hidden" name="material_id[]" value="${materialId}"></td>
                <td>${stageName}<input type="hidden" name="stage_id[]" value="${stageId}"></td>
                <td>${selectedOptionsString}<input type="hidden" name="work_logs[]" value="${idsString}"></td>
                <td>${quantity}<input type="hidden" name="quantity[]" value="${quantity}"></td>
                <td><button type="button" class="btn btn-danger deleteRow">X</button></td>
            </tr>`;

            $('#items-table tbody').append(markup);

            $('input[name="quantityProduct"]').val('');
            $('#pcost_id').val(null).trigger('change');
            updateSerialNumbers();
        });

        // Event listener for click on add button in material section
        $('#addBtnMaterial').click(function () {
            var selectedOption = $('#material_id option:selected').val();
            var selectedValues = selectedOption.split('|');
            var materialId = selectedValues[0];
            var productId = selectedValues[1];
            var materialText = $('#material_id option:selected').text();
            var materialValues = materialText.split('|');
            var materialName = materialValues[0];
            // var productName = materialValues[1];
            var selectedId = $('#material_id'); // Select element jQuery object
            var selectedOption = selectedId.find('option:selected');
            var previousDisabled = selectedOption.prevAll('option[disabled]:first').text();
            var previousDisabledValues = previousDisabled.split('|');
            var articleNo = previousDisabledValues[0].replace('========== ', '').trim();
            var articleSize = previousDisabledValues[1].replace('Size ', '').trim();
            var productName = `${articleNo} - Size ${articleSize}`;
            var quantity = parseInt($('input[name="quantityMaterial"]').val());
            var availableStock = parseInt($('#receiveable_stock').val());
            var stageId = '0';
            var idsString = '0';
            var workLog = 'None';
            // var materialId = $('#material_id');
            var selectedValue = $('#material_id').val();
            var parts = selectedValue.split('|');
            var materialId = parts[0];

            if (!materialId || !quantity) return;

            if (quantity > availableStock) {
                alert("Quantity cannot be greater than available stock.");
                return;
            }

            if (isMaterialExists(materialId, productId)) {
                alert("This material for the selected product already exists in the table.");
                return;
            }

            var srNo = $('#items-table tbody tr').length + 1;

            var markup = `<tr>
                <td>${srNo}</td>
                <td>${productName}<input type="hidden" name="material_id[]" value="${materialId}"><input type="hidden" name="product_type_id[]" value="${productId}"></td>
                <td>${materialName}<input type="hidden" name="stage_id[]" value="${stageId}"></td>
                <td>${workLog}<input type="hidden" name="work_logs[]" value="${idsString}"></td>
                <td>${quantity}<input type="hidden" name="quantity[]" value="${quantity}"></td>
                <td><button type="button" class="btn btn-danger deleteRow">X</button></td>
            </tr>`;

            $('#items-table tbody').append(markup);

            var updatedStock = availableStock - quantity;
            $('#receiveable_stock').val(updatedStock);

            $('input[name="quantityMaterial"]').val('');
            updateSerialNumbers();
        });

        // Function to update serial numbers
        function updateSerialNumbers() {
            $('#items-table tbody tr').each(function (index) {
                $(this).find('td:first').text(index + 1);
            });
        }

        // Event listener for click on delete button in table row
        $('#items-table').on('click', '.deleteRow', function () {
            var quantityToRemove = parseInt($(this).closest('tr').find('input[name="quantity[]"]').val());
            var currentAvailableStock = parseInt($('#receiveable_stock').val());
            var updatedStock = currentAvailableStock + quantityToRemove;
            $('#receiveable_stock').val(updatedStock);
            $(this).closest('tr').remove();
            updateSerialNumbers();
        });

        // Initialize select2
        initializeSelect2();

        // Function to check if product and stage combination already exists
        function isProductStageCombinationExists(productId, stageId, productCost) {
            var exists = false;
            $('#items-table tbody tr').each(function () {
                var rowProductId = $(this).find('input[name="product_type_id[]"]').val();
                var rowStageId = $(this).find('input[name="stage_id[]"]').val();
                var rowCostId = $(this).find('input[name="work_logs[]"]').val();
                // if (rowProductId == productId && rowStageId == stageId) {
                if (rowProductId == productId && rowStageId == stageId && rowCostId == productCost) {
                    exists = true;
                    return false; // exit loop early
                }
            });
            return exists;
        }

        // Function to check if material with the specified product already exists
        function isMaterialExists(materialId, productId) {
            var exists = false;
            $('#items-table tbody tr').each(function () {
                var rowMaterialId = $(this).find('input[name="material_id[]"]').val();
                var rowProductId = $(this).find('input[name="product_type_id[]"]').val();

                // Check combination of material ID and product ID
                if (rowMaterialId === materialId && rowProductId === productId) {
                    exists = true;
                    return false; // exit loop early
                }
            });
            return exists;
        }
    }
});
// End - Receive Issue Material Script

// Start - Product Cost Script
$(document).ready(function () {
    if (typeof isProductCostPage !== 'undefined') {
        var tableRowCount = 1;
        updateSrNumbers();

        // Event listener for change in product_id
        $('select[name="product_id"]').change(function () {
            if ($('#items-table tbody tr').length > 0) {
                if (!confirm('Changing the product type will clear the table. Are you sure you want to proceed?')) {
                    $(this).val($(this).data('previous')).trigger('change.select2');
                    return;
                }
            }
            clearTable();
        });

        // Initially disable the add button
        $('#addBtn').prop('disabled', true);

        // Enable/disable add button based on field values
        $('select[name="head_id"], input[name="amount"]').on('change keyup', function () {
            $('#addBtn').prop('disabled', !checkFields());
        });

        $('#addBtn').click(function () {
            var headId = $('select[name="head_id"]').val();
            var headName = $('select[name="head_id"] option:selected').text();
            var tableId = $('select[name="table_id"]').val();
            var evName = $('select[name="table_id"] option:selected').text();
            var tname = $('select[name="table_id"] option:selected').data('tname');
            var amount = $('input[name="amount"]').val();

            // Check for existing row with table_id = 0 and the same head_id
            if (tableId != 0) {
                var hasGeneralCost = $('#items-table tbody tr').filter(function () {
                    var rowTableId = $(this).find('input[name="table_id[]"]').val();
                    var rowHeadId = $(this).find('input[name="head_id[]"]').val();
                    return rowTableId == 0 && rowHeadId == headId;
                }).length > 0;

                if (!hasGeneralCost) {
                    alert("Add General cost first.");
                    return;
                }
            }

            // Check for duplicate entry
            var isDuplicate = $('#items-table tbody tr').filter(function () {
                var rowTableId = $(this).find('input[name="table_id[]"]').val();
                var rowHeadId = $(this).find('input[name="head_id[]"]').val();
                var rowTableName = $(this).find('input[name="table_name[]"]').val();
                return rowTableId === tableId && rowHeadId === headId && rowTableName === tname;
            }).length > 0;

            if (isDuplicate) {
                alert("This Costing head is already added.");
                return;
            }

            // Append the new row
            appendRow(headId, headName, amount, tableId, evName, tname);
            // Disable Add button & Reset input field
            $('#addBtn').prop('disabled', true);
            $('input[name="amount"]').val('');
            $('select[name="head_id"]').val('').trigger('change');
            updateSrNumbers();
        });

        // Delete row functionality
        $(document).on('click', '.deleteRowBtn', function () {
            $(this).closest('tr').remove();
            updateSrNumbers();
        });

        // Function to check if all fields have data
        function checkFields() {
            var productId = $('select[name="product_id"]').val();
            var headId = $('select[name="head_id"]').val();
            var amount = $('input[name="amount"]').val();
            if (isProductCostPage) {
                return (headId && amount);
            } else {
                return (productId && headId && amount);
            }
        }

        // Function to update serial numbers
        function updateSrNumbers() {
            $('#items-table tbody tr').each(function (index) {
                $(this).find('td:first').text(index + 1);
            });
        }

        // Function to append a row to the table and then sort
        function appendRow(headId, headName, amount, tableId, evName, tname) {
            var newRow = $(`<tr>
                <td class="sr"></td>
                <td>${evName}<input type="hidden" name="table_id[]" value="${tableId}">
                <input type="hidden" name="table_name[]" value="${tname}"></td>
                <td>${headName}<input type="hidden" name="head_id[]" value="${headId}"></td>
                <td>${amount}<input type="hidden" name="amount[]" value="${amount}"></td>
                <td><button type="button" class="deleteRowBtn btn btn-danger">X</button></td>
            </tr>`);
            // Insert the new row in sorted order based on table_id
            var inserted = false;
            $('#items-table tbody tr').each(function () {
                var currentTableId = parseInt($(this).find('input[name="table_id[]"]').val(), 10);
                if (tableId < currentTableId && !inserted) {
                    newRow.insertBefore($(this));
                    inserted = true;
                    return false; // Break loop
                }
            });
            // If the row is not inserted, append it at the end
            if (!inserted) {
                $('#items-table tbody').append(newRow);
            }
            // Call updateSrNumbers to ensure serial numbers are correct
            updateSrNumbers();
        }

        // Function to clear the table if product_id changes
        function clearTable() {
            $('#items-table tbody').empty();
            updateSrNumbers();
        }
    }
});
// End - Product Cost Script

// Start - Bank Script
$(document).ready(function () {
    if (typeof isBankPage !== 'undefined') {
        function toggleSections() {
            var selected = $('select[name="bank_holder"]').val();
            // Reset selects when not active
            if (selected != 'admin') {
                $('#credit').val(0);
            } if (selected != 'employee') {
                $('#employee select').val('').trigger('change');
            } if (selected != 'vendor') {
                $('#vendor select').val('').trigger('change');
            } if (selected != 'customer') {
                $('#customer select').val('').trigger('change');
            } if (selected != 'contractor') {
                $('#contractor select').val('').trigger('change');
            }
            // Hide all sections first
            $('#admin, #employee, #vendor, #customer, #contractor').hide();
            if (selected == 'admin') {
                $('#admin').show();
            } else if (selected == 'employee') {
                $('#employee').show();
            } else if (selected == 'vendor') {
                $('#vendor').show();
            } else if (selected == 'customer') {
                $('#customer').show();
            } else if (selected == 'contractor') {
                $('#contractor').show();
            }
            // Re-initialize Select2 for visible select elements
            $('.select2:visible').select2();
        }

        // Edit Bank Detail using Modal
        toggleSections();
        // Run on selection change
        $('select[name="bank_holder"]').change(function () {
            toggleSections();
        });

        // Reinitialize Select2 for Bank Types
        function reinitializeSelect2(modalId) {
            $('#' + modalId + ' select[name="head_id"]').select2();
        }

        // Run on page load
        $('.modal').each(function () {
            var modalId = $(this).attr('id');
            reinitializeSelect2(modalId);
        });

        // Run after a modal is shown
        $('.modal').on('shown.bs.modal', function () {
            var modalId = $(this).attr('id');
            reinitializeSelect2(modalId);
        });
    }
});
// End - Bank Script

// Start - Pay Script
$(document).ready(function () {
    // Getting Account No of Employee / Vendor
    if (typeof isPayPage !== 'undefined') {
        $('#payee_id').on('change', function () {
            var table = $('#transaction_to').val();
            var tableId = $(this).val();
            $.ajax({
                url: ajaxBankUrl,
                type: "GET",
                data: { tableId: tableId, table: table },
                dataType: "json",
                success: function (response) {
                    var bankSelect = $('#payee_bank_id');
                    bankSelect.empty().append('<option value="0" selected>Cash Payment</option>');
                    // Populate options dynamically based on the response
                    $.each(response.data, function (index, item) {
                        var optionText = item.hname + ' - ' + item.account_title + ' - ' + item.account;
                        bankSelect.append(new Option(optionText, item.bank_id));
                    });
                    bankSelect.trigger('change');
                },
            });
        });

        $('#payee_id').trigger('change');
    }
});
// End - Pay Script

// Start - Pay Vendor Script
$(document).ready(function () {
    if (typeof isPayVendorPage !== 'undefined') {
        $('#payee_id').on('change', function () {
            var vendorId = $(this).val();
            $.ajax({
                url: ajaxPurchaseUrl,
                type: "GET",
                data: { vendorId: vendorId },
                dataType: "json",
                success: function (response) {
                    var purchaseSelect = $('#order_id');
                    purchaseSelect.empty().append('<option value="" selected disabled>Select Purchase</option>');
                    // Populate options dynamically based on the response
                    $.each(response.data, function (index, item) {
                        var optionText = item.purchase_no + (item.job_no ? ' - ' + item.job_no : ' - Default Purchase');
                        purchaseSelect.append(new Option(optionText, item.purchase_id));
                    });
                    purchaseSelect.trigger('change');
                },
            });
        });

        $('#payee_id').trigger('change');
    }
});
// End - Pay Vendor Script

// Start - Pay Order Payment Script
$(document).ready(function () {
    if (typeof isPayOrderPage !== 'undefined') {
        $('#payee_id').on('change', function () {
            var customerId = $(this).val();
            $.ajax({
                url: ajaxOrderUrl,
                type: "GET",
                data: { customerId: customerId },
                dataType: "json",
                success: function (response) {
                    var orderSelect = $('#order_id');
                    orderSelect.empty().append('<option value="" selected disabled>Select Order</option>');
                    // Populate options dynamically based on the response
                    $.each(response.data, function (index, item) {
                        var optionText = item.order_no + ' | ' + item.job_no;
                        orderSelect.append(new Option(optionText, item.order_id));
                    });
                    orderSelect.trigger('change');
                },
            });
        });
        $('#payee_id').trigger('change');
    }
});
// End - Pay Order Payment Script

// Start - General Voucher Script
$(document).ready(function () {
    if (typeof isGeneralVoucherPage !== 'undefined') {
        function togglePayeeSections() {
            var selected = $('select[name="payee_type"]').val();

            // Reset all payee_id selects when not active
            if (selected != 'vendor') {
                $('#vendor select[name="payee_id"]').val('').trigger('change');
            }
            if (selected != 'contractor') {
                $('#contractor select[name="payee_id"]').val('').trigger('change');
            }
            if (selected != 'employee') {
                $('#employee select[name="payee_id"]').val('').trigger('change');
            }
            if (selected != 'customer') {
                $('#customer select[name="payee_id"]').val('').trigger('change');
            }

            // Hide all payee sections first
            $('#vendor, #contractor, #employee, #customer').hide();

            // Show the selected payee section
            if (selected == 'vendor') {
                $('#vendor').show();
            } else if (selected == 'contractor') {
                $('#contractor').show();
            } else if (selected == 'employee') {
                $('#employee').show();
            } else if (selected == 'customer') {
                $('#customer').show();
            }
        }

        // Initialize on page load
        togglePayeeSections();

        // Run on selection change
        $('select[name="payee_type"]').change(function () {
            togglePayeeSections();
        });

        // Custom form validation for payee_id
        $('form').on('submit', function (e) {
            var payeeType = $('select[name="payee_type"]').val();
            var payeeId = $('select[name="payee_id"]:visible').val();

            // Validate payee type is selected
            if (!payeeType) {
                e.preventDefault();
                alert('Please select a Payee Type');
                return false;
            }

            // Validate payee is selected
            if (!payeeId) {
                e.preventDefault();
                alert('Please select a Payee');
                return false;
            }

            return true;
        });
    }
});
// End - General Voucher Script

// Start - PTC (Process Travel Card) Script
$(document).ready(function () {
    if (typeof ajaxPtcProductsUrl !== 'undefined') {
        var $orderSelect = $('#modal_order_id');
        var $productSelect = $('#modal_product_type_id');
        var $startStageSelect = $('#modal_start_stage_id');
        var $endStageSelect = $('#modal_end_stage_id');
        var $submitBtn = $('#modalSubmitBtn');
        var stagesData = [];

        // Function to initialize select2 within modal
        function initializeSelect2() {
            $('#createPtcModal .select2').select2({
                dropdownParent: $('#createPtcModal')
            });
        }

        // Initialize Select2 when modal is shown
        $('#createPtcModal').on('shown.bs.modal', function () {
            initializeSelect2();
        });

        // Function to validate form and enable/disable submit button
        function validatePtcForm() {
            var productId = $productSelect.val();
            var startStageId = $startStageSelect.val();
            var endStageId = $endStageSelect.val();

            if (productId && startStageId && endStageId) {
                // Validate that end stage comes after or equals start stage
                var startIndex = stagesData.findIndex(s => s.head_id == startStageId);
                var endIndex = stagesData.findIndex(s => s.head_id == endStageId);
                if (startIndex <= endIndex) {
                    $submitBtn.prop('disabled', false);
                    return;
                }
            }
            $submitBtn.prop('disabled', true);
        }

        // Load products when order changes
        $orderSelect.on('change', function () {
            var orderId = $(this).val();
            $productSelect.empty().append('<option value="" disabled selected>Loading...</option>');
            $startStageSelect.empty().append('<option value="" disabled selected>Select Start Stage</option>');
            $endStageSelect.empty().append('<option value="" disabled selected>Select End Stage</option>');
            $submitBtn.prop('disabled', true);
            stagesData = [];

            $.ajax({
                url: ajaxPtcProductsUrl,
                type: "GET",
                data: { order_id: orderId },
                dataType: "json",
                success: function (response) {
                    $productSelect.empty().append('<option value="" disabled selected>Select Product</option>');
                    if (response.data && response.data.length > 0) {
                        response.data.forEach(function (item) {
                            var optionText = item.product_name + ' - ' + item.size_name;
                            $productSelect.append(new Option(optionText, item.product_type_id));
                        });
                    }
                    initializeSelect2();
                },
                error: function () {
                    $productSelect.empty().append('<option value="" disabled selected>Error loading products</option>');
                }
            });
        });

        // Load stages when product changes
        $productSelect.on('change', function () {
            var productTypeId = $(this).val();
            $startStageSelect.empty().append('<option value="" disabled selected>Loading...</option>');
            $endStageSelect.empty().append('<option value="" disabled selected>Loading...</option>');
            $submitBtn.prop('disabled', true);
            stagesData = [];

            $.ajax({
                url: ajaxPtcStagesUrl,
                type: "GET",
                data: { product_type_id: productTypeId },
                dataType: "json",
                success: function (response) {
                    $startStageSelect.empty().append('<option value="" disabled selected>Select Start Stage</option>');
                    $endStageSelect.empty().append('<option value="" disabled selected>Select End Stage</option>');
                    if (response.data && response.data.length > 0) {
                        stagesData = response.data;
                        response.data.forEach(function (item) {
                            $startStageSelect.append(new Option(item.name, item.head_id));
                            $endStageSelect.append(new Option(item.name, item.head_id));
                        });
                        // Auto-select first stage as start and last stage as end
                        if (response.data.length > 0) {
                            $startStageSelect.val(response.data[0].head_id);
                            $endStageSelect.val(response.data[response.data.length - 1].head_id);
                        }
                    }
                    initializeSelect2();
                    validatePtcForm();
                },
                error: function () {
                    $startStageSelect.empty().append('<option value="" disabled selected>Error loading stages</option>');
                    $endStageSelect.empty().append('<option value="" disabled selected>Error loading stages</option>');
                }
            });
        });

        // Validate on stage change
        $startStageSelect.on('change', validatePtcForm);
        $endStageSelect.on('change', validatePtcForm);
    }
});
// End - PTC (Process Travel Card) Script
