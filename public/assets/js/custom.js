// ===========================================================================================
// ======================================== Custom JS ========================================
// ===========================================================================================

"use strict";

// Toaster Message
$(document).ready(function() {
    var successMessage = $('#successMessage').val();
    var errorMessage = $('#errorMessage').val();
    if (successMessage) {
        iziToast.success({
            title: 'Success!',
            message: successMessage,
            position: 'topRight'
        });
    }else if (errorMessage) {
        iziToast.error({
            title: 'Error!',
            message: errorMessage,
            position: 'topRight'
        });
    }
});

// Wrong Extension Image
document.addEventListener("DOMContentLoaded", function() {
    var fileInput = document.getElementById('customFile');
    var fileError = document.getElementById('fileError');
    var fileSuccess = document.getElementById('fileSuccess');

    fileInput.addEventListener('change', function() {
        var files = this.files;
        var errorMessage = '';

        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            var extension = file.name.split('.').pop().toLowerCase();
            var allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];

            if (allowedExtensions.indexOf(extension) === -1) {
                errorMessage = 'Please select JPG, JPEG, PNG, GIF, or SVG files only.';
                break;
            }
        }

        if (errorMessage) {
            fileError.textContent = errorMessage;
            fileError.style.display = 'block';
            fileSuccess.style.display = 'none';
            this.value = '';
        } else {
            fileError.style.display = 'none';
            fileSuccess.style.display = 'block';
        }
    });
});

// Start - Purchase Script
$(document).ready(function() {
    if (typeof isEditPage !== 'undefined') {
        var tableRowCount = 1;
        updateSrNumbers();
        updateGrandTotal();
        // Initially disable the add button
        $('#addBtn').prop('disabled', true);

        // Function to check if all three fields have data
        function checkFields() {
            var materialId = $('select[name="material_id[]"]').val();
            var quantity = $('input[name="quantity"]').val();
            var price = $('input[name="price"]').val();
            return (materialId && quantity && price);
        }

        // Enable/disable add button based on field values
        $('select[name="material_id[]"], input[name="quantity"], input[name="price"]').on('change keyup', function() {
            $('#addBtn').prop('disabled', !checkFields());
        });

        $('#addBtn').on('click', function() {
            var materialId = $('select[name="material_id[]"]').val();
            var materialName = $('select[name="material_id[]"] option:selected').text();
            var quantity = $('input[name="quantity"]').val();
            var price = $('input[name="price"]').val();
            var total = quantity * price;

            var existingMaterial = false;
        $('#items-table tbody tr').each(function(index, row) {
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
            '<td>' + total + '<input type="hidden" name="total[]" value="' + total + '"></td>' +
            '<td><button class="deleteRowBtn btn btn-danger">X</button></td>' +
            '</tr>';
            
            $('#items-table tbody').append(newRow);

            tableRowCount++;
            
            // Disable Btn & Reset input field
            $('#addBtn').prop('disabled', true); 
            $('input[name="quantity"]').val('0');
            $('input[name="price"]').val('0');
            $('select[name="material_id[]"]').val('').trigger('change');
            updateSrNumbers();
            updateGrandTotal();
        }
        });

        // Delete row when delete button is clicked
        $(document).on('click', '.deleteRowBtn', function() {
            $(this).closest('tr').remove();
            updateSrNumbers();
            updateGrandTotal();
        });
        
        // Function to update Sr. numbers
        function updateSrNumbers() {
            $('#items-table tbody tr').each(function(index) {
                if (isEditPage) {
                    $(this).find('td:first').text(index);
                } else {
                    $(this).find('td:first').text(index + 1);
                }
            });
        }

        // Function to calculate and update grand total
        function updateGrandTotal() {
            var grandTotal = 0;
            $('#items-table tbody tr').each(function() {
                var total = parseFloat($(this).find('input[name="total[]"]').val());
                if (!isNaN(total)) {
                    grandTotal += total;
                }
            });
            $('#grandTotal').text('Rupee: ' + grandTotal.toFixed(2));
        }

        $('#submitBtn').on('click', function() {
            // Gather data from table and submit
            var tableData = [];
            $('#items-table tbody tr').each(function(index, row) {
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
// End - Purchase Script

// Start - Order Script
$(document).ready(function() {
    if (typeof isOrderPage !== 'undefined') {
        var tableRowCount = 1;
        updateSrNumbers();
        updateGrandTotal();
        // Initially disable the add button
        $('#addBtn').prop('disabled', true);

        // Function to check if all three fields have data
        function checkFields() {
            var productId = $('select[name="product_type_id[]"]').val();
            var quantity = $('input[name="quantity"]').val();
            var price = $('input[name="price"]').val();
            return (productId && quantity && price);
        }

        // Enable/disable add button based on field values
        $('select[name="product_type_id[]"], input[name="quantity"], input[name="price"]').on('change keyup', function() {
            $('#addBtn').prop('disabled', !checkFields());
        });

        $('#addBtn').on('click', function() {
            var productId = $('select[name="product_type_id[]"]').val();
            var productName = $('select[name="product_type_id[]"] option:selected').text();
            var quantity = $('input[name="quantity"]').val();
            var price = $('input[name="price"]').val();
            var total = quantity * price;

            var existingProduct = false;
        $('#items-table tbody tr').each(function(index, row) {
            var existingProductId = $(row).find('input[name="product_type_id[]"]').val();
            if (existingProductId == productId) {
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
            '<td>' + quantity + '<input type="hidden" name="quantity[]" value="' + quantity + '"></td>' +
            '<td>' + price + '<input type="hidden" name="price[]" value="' + price + '"></td>' +
            '<td>' + total + '<input type="hidden" name="total[]" value="' + total + '"></td>' +
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
        $(document).on('click', '.deleteRowBtn', function() {
            $(this).closest('tr').remove();
            updateSrNumbers();
            updateGrandTotal();
        });
        
        // Function to update Sr. numbers
        function updateSrNumbers() {
            $('#items-table tbody tr').each(function(index) {
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
            $('#items-table tbody tr').each(function() {
                var total = parseFloat($(this).find('input[name="total[]"]').val());
                if (!isNaN(total)) {
                    grandTotal += total;
                }
            });
            $('#grandTotal').text('Rupee: ' + grandTotal.toFixed(2));
        }

        $('#submitBtn').on('click', function() {
            // Gather data from table and submit
            var tableData = [];
            $('#items-table tbody tr').each(function(index, row) {
            var rowData = {
                'product_type_id': $(row).find('input[name="product_type_id[]"]').val(),
                'product_name': $(row).find('input[name="name[]"]').val(),
                'quantity': $(row).find('input[name="quantity[]"]').val(),
                'price': $(row).find('input[name="price[]"]').val(),
                'total': $(row).find('input[name="total[]"]').val()
            };
            tableData.push(rowData);
          });
        });
    }
});
// End - Order Script