// ===========================================================================================
// ======================================== Custom JS ========================================
// ===========================================================================================

"use strict";

// Start - Toaster Message
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
// End - Toaster Message

// Start - Wrong Extension Image
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
// End - Wrong Extension Image

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

// Start - Product Material Script
$(document).ready(function() {
    if (typeof isPMPage !== 'undefined') {
        var tableRowCount = 1;
        updateSrNumbers();
        // Initially disable the add button
        $('#addBtn').prop('disabled', true);

        // Function to check if both fields have data
        function checkFields() {
            var materialId = $('select[name="material_id[]"]').val();
            var quantity = $('input[name="quantity"]').val();
            return (materialId && quantity);
        }

        // Enable/disable add button based on field values
        $('select[name="material_id[]"], input[name="quantity"]').on('change keyup', function() {
            $('#addBtn').prop('disabled', !checkFields());
        });

        $('#addBtn').on('click', function() {
            var materialId = $('select[name="material_id[]"]').val();
            var materialName = $('select[name="material_id[]"] option:selected').text();
            var quantity = $('input[name="quantity"]').val();

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
                    '<td><button class="deleteRowBtn btn btn-danger">X</button></td>' +
                    '</tr>';

                $('#items-table tbody').append(newRow);

                tableRowCount++;

                // Disable Btn & Reset input field
                $('#addBtn').prop('disabled', true);
                $('input[name="quantity"]').val('0');
                $('select[name="material_id[]"]').val('').trigger('change');
                updateSrNumbers();
            }
        });

        // Delete row when delete button is clicked
        $(document).on('click', '.deleteRowBtn', function() {
            $(this).closest('tr').remove();
            updateSrNumbers();
        });

        // Function to update Sr. numbers
        function updateSrNumbers() {
            $('#items-table tbody tr').each(function(index) {
                if (isPMPage) {
                    $(this).find('td:first').text(index);
                } else {
                    $(this).find('td:first').text(index + 1);
                }
            });
        }

        $('#submitBtn').on('click', function() {
            // Gather data from table and submit
            var tableData = [];
            $('#items-table tbody tr').each(function(index, row) {
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
document.addEventListener('input', function(event) {
    if (event.target.classList.contains('qty')) {
      var row = event.target.closest('tr');
      var received = parseInt(row.querySelector('.received').innerText, 10) || 0;
      var total = parseInt(row.querySelector('.total').innerText, 10) || 0;
      var enteredQuantity = parseInt(event.target.value, 10) || 0;
      var remaining = total - received - enteredQuantity;

      // Ensure the entered quantity does not exceed the remaining quantity
      var maxQuantity = total - received;
      event.target.setAttribute('max', maxQuantity);

      // Update the remaining input value
      var remainingInput = row.querySelector('.remaining');
      remainingInput.value = remaining >= 0 ? remaining : 0;

      // If the entered quantity exceeds the max, adjust it to the max
      if (enteredQuantity > maxQuantity) {
        event.target.value = maxQuantity;
        remainingInput.value = 0;
      }
    }
});
// End - Receive Material Script

// Start - Return Material Script
var returnQuantityInputs = document.querySelectorAll('.return-qty');
returnQuantityInputs.forEach(function(input) {
    var row = input.closest('tr');
    var receiveQuantityCell = row.querySelector('td:nth-child(5)');
    var receiveQuantity = parseInt(receiveQuantityCell.textContent.trim());
    input.addEventListener('input', function() {
        var inputValue = parseInt(this.value.trim());
        if (inputValue > receiveQuantity) {
            this.value = receiveQuantity;
        }
    });
    input.setAttribute('max', receiveQuantity);
});
// End - Return Material Script

// Start - Make Qty 0
document.getElementById('makeZero').addEventListener('submit', function(event) {
    document.querySelectorAll('.qty, .return-qty, .price').forEach(function(input) {
        if (input.value.trim() === '') {
            input.value = '0';
        }
    });
});
// End - Make Qty 0
