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

// Start - Wrong Extension Image / File Name
document.addEventListener("DOMContentLoaded", function() {
    var fileInput = document.getElementById('customFile');
    var fileError = document.getElementById('fileError');
    var fileSuccess = document.getElementById('fileSuccess');
    
    function handleFileInputChange(fileInput, fileError, fileSuccess) {
        fileInput.addEventListener('change', function() {
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
    handleFileInputChange(fileInput, fileError, fileSuccess);
});

document.addEventListener("DOMContentLoaded", function() {
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
    document.addEventListener('change', function(event) {
        if (event.target && event.target.classList.contains('attachment-file')) {
            handleFileInputChange(event);
        }
    });
});
// End - Wrong Extension Image / File Name

// Start - Duplicate Attachment Row
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById('attachmentContainer').addEventListener('click', function(e) {
        var target = e.target;

        if (target.classList.contains('add-attachment')) {
            // Clone the attachment row
            var originalRow = target.closest('.attachment-row');
            var clonedRow = originalRow.cloneNode(true);

            // Clear the input values in the cloned row and adjust visibility of action buttons
            clonedRow.querySelectorAll('input').forEach(function(input) { input.value = ''; });
            clonedRow.querySelector('.add-attachment').style.display = 'none';
            clonedRow.querySelector('.add-attachment-label').style.display = 'none';
            clonedRow.querySelector('.remove-attachment').style.display = 'inline-block';
            clonedRow.querySelector('.remove-attachment-label').style.display = 'inline';
            
            // Append the cloned row
            document.getElementById('attachmentContainer').appendChild(clonedRow);
        } else if (target.classList.contains('remove-attachment')) {
            // Remove the attachment row
            target.closest('.attachment-row').remove();
        }
    });
});
// End - Duplicate Attachment Row

// Start - Purchase Script
$(document).ready(function() {
    if (typeof isPurchasePage !== 'undefined') {
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
                if (isPurchasePage) {
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
            var productId = $('select[name="product_type_id"]').val();
            var quantity = $('input[name="quantity"]').val();
            var price = $('input[name="price"]').val();
            return (productId && quantity && price);
        }

        // Enable/disable add button based on field values
        $('select[name="product_type_id"], input[name="quantity"], input[name="price"]').on('change keyup', function() {
            $('#addBtn').prop('disabled', !checkFields());
        });

        $('#addBtn').on('click', function() {
            var productId = $('select[name="product_type_id"]').val();
            var productName = $('select[name="product_type_id"] option:selected').text();
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

        // Event listener for change in product_type_id
        $('select[name="product_type_id"]').change(function() {
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
        $('select[name="material_id"], input[name="quantity"]').on('change keyup', function() {
            $('#addBtn').prop('disabled', !checkFields());
        });

        $('#addBtn').on('click', function() {
            var materialId = $('select[name="material_id"]').val();
            var materialName = $('select[name="material_id"] option:selected').text();
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
                $('select[name="material_id"]').val('').trigger('change');
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

        // Function to clear the table if product_type_id changes
        function clearTable() {
            $('#items-table tbody').empty();
            updateSrNumbers();
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
    if (event.target.classList.contains('receive-qty')) {
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
    var receiveQuantity = parseInt(receiveQuantityCell.textContent.split('/')[1].trim());
    var returnedQuantity = parseInt(receiveQuantityCell.textContent.split('/')[0].trim()) || 0;
    var availableToReturn = receiveQuantity - returnedQuantity;
    input.addEventListener('input', function() {
        var inputValue = parseInt(this.value.trim()) || 0;
        if (inputValue > availableToReturn) {
            this.value = availableToReturn;
        }
    });
    input.setAttribute('max', availableToReturn);
});
// End - Return Material Script

// Start - Make Qty 0
var makeZeroForm = document.getElementById('makeZero');
if (makeZeroForm) {
    makeZeroForm.addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent default form submission
        document.querySelectorAll('.receive-qty, .return-qty').forEach(input => input.value = input.value.trim() === '' ? '0' : input.value);
        makeZeroForm.submit(); // Submit the form
    });
}
// End - Make Qty 0

// Start - Issue Material Script
$(document).ready(function() {
    if (typeof isIssuePage !== 'undefined') {
        // Function to initialize select2
        function initializeSelect2() {
            $('.select2').select2();
        }

        // Getting Table name
        $('#employee_id').on('change', function() {
            var tableName = $(this).find('option:selected').data('type');
            $('#table_name').val(tableName);
        });

        updateSerialNumbers(); // Update serial numbers after deleting a row

        // Function to update available stock
        function updateAvailableStock(materialId) {
            // Calculate total quantity of the same material present in the table
            var totalQuantityInTable = 0;
            var totalQuantityInTable2 = 0;
            $('#items-table tbody tr').each(function() {
                var rowMaterialId = $(this).find('input[name="material_id[]"]').val();
                var rowMaterialId2 = $(this).find('input[name="hidden_material_id[]"]').val();
                if (rowMaterialId === materialId) {
                    totalQuantityInTable += parseInt($(this).find('input[name="quantity[]"]').val());
                }if (rowMaterialId2 === materialId) {
                    totalQuantityInTable2 += parseInt($(this).find('input[name="hidden_quantity[]"]').val());
                }
            });

            // Find the stock item for the selected material
            var stockItem = stockData.find(item => item.material_id == materialId);
            if (stockItem) {
                var availableStock = stockItem.total_received + stockItem.stockIn - stockItem.total_returned - stockItem.stockOut + totalQuantityInTable2 - totalQuantityInTable;
                $('#available_stock').val(availableStock);
            } else {
                // If no stock item found, set available stock to 0
                $('#available_stock').val(0);
            }
        }

        function updateAvailablePStock(productId, stageId) {
            var totalQuantityInTable = 0;
            var totalQuantityInTable2 = 0;
            $('#items-table tbody tr').each(function() {
                var rowProductId = $(this).find('input[name="product_type_id[]"]').val();
                var rowStageId = $(this).find('input[name="stage_id[]"]').val();
                var rowProductId2 = $(this).find('input[name="hidden_product_type_id[]"]').val();
                var rowStageId2 = $(this).find('input[name="hidden_stage_id[]"]').val();
                if (rowProductId === productId && rowStageId == stageId) {
                    totalQuantityInTable += parseInt($(this).find('input[name="quantity[]"]').val());
                }if (rowProductId2 === productId && rowStageId2 == stageId) {
                    totalQuantityInTable2 += parseInt($(this).find('input[name="hidden_quantity[]"]').val());
                }
            });

            // Find the stock item for the selected material
            var stockItem = pstockData.find(item => item.product_type_id == productId && item.stage_id == stageId);
            if (stockItem) {
                var availableStock = stockItem.stockIn - stockItem.stockOut + totalQuantityInTable2 - totalQuantityInTable;
                $('#available_pstock').val(availableStock);
                $('#available_stock').val(0);
            } else {
                $('#available_pstock').val(0);
            }
        }

        // Manually trigger AJAX request to load products based on preselected order on page load
        loadProductsBasedOnOrder();

        // Function to load products based on preselected order
        function loadProductsBasedOnOrder() {
            var orderId = $('#order_id').val();
            $.ajax({
                url: ajaxPTUrl,
                type: "GET",
                data: { orderId: orderId },
                dataType: "json",
                success: function(response) {
                    $('#product_type_id').empty().append('<option value="" disabled selected>Select Product</option>');
                    response.data.forEach(function(item) {
                        var optionText = item.article_no + ' - Size ' + item.hname;
                        $('#product_type_id').append(new Option(optionText, item.product_type_id));
                    });
                    $('#product_type_id').trigger('change');
                }
            });
        }

        // Event listener for change in order ID
        $('#order_id').on('change', function() {
            if ($('#items-table tbody tr').length > 0) {
                if (!confirm('Changing the order will clear the table. Are you sure you want to proceed?')) {
                    $(this).val($(this).data('previous')).trigger('change.select2');
                    return;
                }
            }
            $('#items-table tbody').empty();
            loadProductsBasedOnOrder();
        });

        // Define stageStockInfo outside of the event listeners
        var stageStockInfo = {};

        // Event listener for change in product type
        $('#product_type_id').on('change', function() {
            var productId = $(this).val();
            $.ajax({
                url: ajaxPMUrl,
                type: "GET",
                data: { productId: productId },
                dataType: "json",
                success: function(response) {
                    var materialSelect = $('#material_id');
                    materialSelect.empty().append('<option value="" disabled selected>Select Material</option>');
                    response.materials.forEach(function(item) {
                        materialSelect.append(new Option(item.name, item.material_id));
                    });
                    materialSelect.trigger('change');

                    // Clear previous data in stageStockInfo
                    stageStockInfo = {};

                    // Populate stageSelect and stageStockInfo
                    var stageSelect = $('#stage_id');
                    stageSelect.empty().append('<option value="" disabled selected>Select Product</option>');
                    response.stockItems.forEach(function(item) {
                        var optionText = item.article_no + ' - Size ' + item.sname + ' - ' + item.stname;
                        stageSelect.append(new Option(optionText, item.stage_id));
                        stageStockInfo[item.stage_id] = {
                            stock: item.stockIn - item.stockOut,
                            stname: item.stname,
                        };
                    });
                },
            });
        });

        // Event listener for change in stage_id dropdown
        $('#stage_id').on('change', function() {
            var selectedStageId = $(this).val();
            if (selectedStageId) {
                var availableStock = stageStockInfo[selectedStageId];
                $('#available_stock').val(availableStock);
            } else {
                $('#available_stock').val('');
            }
        });

        // Event listener for select2:select event on material ID
        $('#material_id').on('select2:select', function(e) {
            var selectedMaterialId = e.params.data.id;
            updateAvailableStock(selectedMaterialId);
        });

        // Event listener for change in stage_id dropdown
        $('#stage_id').on('change', function() {
            var selectedStageId = $(this).val();
            var productId = $('#product_type_id').val();
            updateAvailablePStock(productId, selectedStageId);
        });

        // Event listener for click on add button
        $('#addBtnMaterial').click(function() {
            var productId = $('#product_type_id').val();
            var materialId = $('#material_id').val();
            var materialText = $('#material_id option:selected').text();
            var productName = $('#product_type_id option:selected').text();
            var quantity = parseInt($('input[name="quantityMaterial"]').val());
            var availableStock = parseInt($('#available_stock').val());
            var stageId = $('#stage_id').val() || "0"; 

            if (!materialId || !quantity) return;

            if (quantity > availableStock) {
                alert("Quantity cannot be greater than available stock.");
                return;
            }

            var isDuplicate = false;
            $('#items-table tbody tr').each(function() {
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
        $('#addBtnStage').click(function() {
            var productId = $('#product_type_id').val();
            var materialId = $('#material_id').val()  || "0";
            var productName = $('#product_type_id option:selected').text();
            var quantity = parseInt($('input[name="quantityStage"]').val());
            var availableStock = parseInt($('#available_pstock').val());
            var stageId = $('#stage_id').val() || "0"; 
            var stageName = stageStockInfo[stageId].stname;

            if (!stageId || !quantity) return;

            if (quantity > availableStock) {
                alert("Quantity cannot be greater than available stock.");
                return;
            }

            var isDuplicate = false;
            $('#items-table tbody tr').each(function() {
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

            var updatedStock = availableStock - quantity;
            $('#available_pstock').val(updatedStock);

            var srNo = $('#items-table tbody tr').length + 1;

            var markup = `<tr>
                <td>${srNo}</td>
                <td>${productName}<input type="hidden" name="product_type_id[]" value="${productId}"><input type="hidden" name="stage_id[]" value="${stageId}"></td>
                <td>${stageName}<input type="hidden" name="material_id[]" value="${materialId}"></td>
                <td>${quantity}<input type="hidden" name="quantity[]" value="${quantity}"></td>
                <td><button type="button" class="btn btn-danger deletepRow">X</button></td>
            </tr>`;

            $('#items-table tbody').append(markup);

            $('#stage_id').val(null).trigger('change');
            $('input[name="quantityStage"]').val('');
            $('input[name="available_pstock"]').val('');
            updateSerialNumbers();
        });

        // Function to update serial numbers
        function updateSerialNumbers() {
            $('#items-table tbody tr:not(#hiddentr)').each(function(index) {
                $(this).find('td:first').text(index + 1);
            });
        }

        // Event listener for click on delete button in table row
        $('#items-table').on('click', '.deleteRow, .deletepRow', function() {
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

// Start - Receive Issue Material Script
$(document).ready(function() {
    if (typeof isReceiveIssuePage !== 'undefined') {

        // Initialize select2 for existing select elements
        $('.select2').select2();
        
        // Function to initialize select2
        function initializeSelect2() {
            $('.select2').select2();
        }

        // AJAX call to update product costs based on selected product type
        $('#product_type_id').on('change', function() {
            var productId = $(this).val();
            $.ajax({
                url: ajaxPCUrl,
                type: "GET",
                data: { productId: productId },
                dataType: "json",
                success: function(response) {
                    $('#pcost_id').empty().append('<option disabled>Select Product Cost</option>');
                    response.data.forEach(function(item) {
                        var optionText = item.hname;
                        $('#pcost_id').append(new Option(optionText, item.head_id));
                    });
                    // Re-initialize select2 for the updated product cost select element
                    initializeSelect2();
                },
            });
        });

        // Event listener for change in material select element
        $('#material_id').change(function() {
            var selectedOption = $(this).val();
            if (!selectedOption) {
                return;
            }
            var parts = selectedOption.split('|');
            var materialId = parts[0];
            var productId = parts[1];
            updateAvailableStock(materialId, productId);
        });

        // Function to update available stock
        function updateAvailableStock(materialId, productId) {
            var totalQuantityInTable = 0;
            $('#items-table tbody tr').each(function() {
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

        // Event listener for click on add button in product section
        $('#addBtnProduct').click(function() {
            var productId = $('#product_type_id').val();
            var materialId = '0';
            var productName = $('#product_type_id option:selected').text();
            var stageId = $('#stage_id').val();
            var stageName = $('#stage_id option:selected').text();
            var quantity = parseInt($('input[name="quantityProduct"]').val());

            // Retrieve all selected product costs
            var selectedProductCosts = $('#pcost_id').val();

            // if (!productId || !quantity || !stageId || selectedProductCosts.length === 0) {
            if (!productId || !quantity || !stageId) {
                // alert("Please select a product, its cost(s), specify its quantity, and stage.");
                alert("Please select a product, specify its quantity, and stage.");
                return;
            }

            if (isProductStageCombinationExists(productId, stageId)) {
                alert("This product and stage combination already exists in the table.");
                return;
            }

            var srNo = $('#items-table tbody tr').length + 1;
            
            // var pcostInputs = '';
            // selectedProductCosts.forEach(pcost => {
            //     pcostInputs += `<input type="hidden" name="pcost_id[]" value="${productId}|${pcost}">`;
            // });

            var selectedOptions = $('#pcost_id').val();
            var selectedOptionsText = selectedOptions.map(option => $('#pcost_id option[value="' + option + '"]').text());
            var selectedOptionsString = selectedOptionsText.join(', ');
            var selectedIds = $('#pcost_id').val();
            var idsString = selectedIds.join('|') || 0;
            
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
        $('#addBtnMaterial').click(function() {
            var selectedOption = $('#material_id option:selected').val();
            var selectedValues = selectedOption.split('|');
            var materialId = selectedValues[0];
            var productId = selectedValues[1];
            var materialText = $('#material_id option:selected').text();
            var materialValues = materialText.split('|');
            var materialName = materialValues[0];
            var productName = materialValues[1];
            var quantity = parseInt($('input[name="quantityMaterial"]').val());
            var availableStock = parseInt($('#receiveable_stock').val());
            var stageId = '0';
            var idsString = '0';
            var workLog = 'None';

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
            $('#items-table tbody tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
            });
        }

        // Event listener for click on delete button in table row
        $('#items-table').on('click', '.deleteRow', function() {
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
        function isProductStageCombinationExists(productId, stageId) {
            var exists = false;
            $('#items-table tbody tr').each(function() {
                var rowProductId = $(this).find('input[name="product_type_id[]"]').val();
                var rowStageId = $(this).find('input[name="stage_id[]"]').val();
                if (rowProductId == productId && rowStageId == stageId) {
                    exists = true;
                    return false; // exit loop early
                }
            });
            return exists;
        }

        // Function to check if material with the specified product already exists
        function isMaterialExists(materialId, productId) {
            var exists = false;
            $('#items-table tbody tr').each(function() {
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
$(document).ready(function() {
    if (typeof isProductCostPage !== 'undefined') {
        var tableRowCount = 1;
        updateSrNumbers();
      
        // Event listener for change in product_type_id
        $('select[name="product_type_id"]').change(function() {
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
        $('select[name="head_id"], input[name="amount"]').on('change keyup', function() {
            $('#addBtn').prop('disabled', !checkFields());
        });

        $('#addBtn').click(function() {
            var headId = $('select[name="head_id"]').val();
            var headName = $('select[name="head_id"] option:selected').text();
            var tableId = $('select[name="table_id"]').val();
            var evName = $('select[name="table_id"] option:selected').text();
            var tname = $('select[name="table_id"] option:selected').data('tname');
            var amount = $('input[name="amount"]').val();

            // Check for existing row with table_id = 0 and the same head_id
            if (tableId != 0) {
                var hasGeneralCost = $('#items-table tbody tr').filter(function() {
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
            var isDuplicate = $('#items-table tbody tr').filter(function() {
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
        $(document).on('click', '.deleteRowBtn', function() {
            $(this).closest('tr').remove();
            updateSrNumbers();
        });

        // Function to check if all fields have data
        function checkFields() {
            var productId = $('select[name="product_type_id"]').val();
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
            $('#items-table tbody tr').each(function(index) {
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
            $('#items-table tbody tr').each(function() {
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

        // Function to clear the table if product_type_id changes
        function clearTable() {
            $('#items-table tbody').empty();
            updateSrNumbers();
        }
    }
});
// End - Product Cost Script

// Start - Bank Script
$(document).ready(function() {
    if (typeof isBankPage !== 'undefined') {
        function toggleSections() {
            var selected = $('select[name="bank_holder"]').val();
            // Reset selects when not active
            if (selected != 'employee') {
                $('#employee select').val('').trigger('change');
            }if (selected != 'vendor') {
                $('#vendor select').val('').trigger('change');
            }if (selected != 'customer') {
                $('#customer select').val('').trigger('change');
            }
            // Hide all sections first
            $('#admin, #employee, #vendor, #customer').hide();
            if (selected == 'admin') {
                $('#admin').show();
            } else if (selected == 'employee') {
                $('#employee').show();
            } else if (selected == 'vendor') {
                $('#vendor').show();
            } else if (selected == 'customer') {
                $('#customer').show();
            }
            // Re-initialize Select2 for visible select elements
            $('.select2:visible').select2();
        }

        
        // Edit Bank Detail using Modal
        toggleSections();
        // Run on selection change
        $('select[name="bank_holder"]').change(function() {
            toggleSections();
        });

        // Reinitialize Select2 for Bank Types
        function reinitializeSelect2(modalId) {
            $('#' + modalId + ' select[name="head_id"]').select2();
        }
    
        // Run on page load
        $('.modal').each(function() {
            var modalId = $(this).attr('id');
            reinitializeSelect2(modalId);
        });
    
        // Run after a modal is shown
        $('.modal').on('shown.bs.modal', function() {
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
        $('#payee_id').on('change', function() {
            var table = $('#transaction_to').val();
            var tableId = $(this).val();
            $.ajax({
                url: ajaxBankUrl,
                type: "GET",
                data: {tableId: tableId, table: table},
                dataType: "json",
                success: function(response) {
                    var bankSelect = $('#payee_bank_id');
                    bankSelect.empty().append('<option value="0" selected>Cash Payment</option>');
                    // Populate options dynamically based on the response
                    $.each(response.data, function(index, item) {
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

// Start - Pay Order Payment Script
$(document).ready(function () {
    if (typeof isPayOrderPage !== 'undefined') {
        $('#payee_id').on('change', function() {
            var customerId = $(this).val();
            $.ajax({
                url: ajaxOrderUrl,
                type: "GET",
                data: {customerId: customerId},
                dataType: "json",
                success: function(response) {
                    var orderSelect = $('#order_id');
                    orderSelect.empty().append('<option value="" selected disabled>Select Order</option>');
                    // Populate options dynamically based on the response
                    $.each(response.data, function(index, item) {
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
