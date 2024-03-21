// Copy of Issuance Before Sorting Logic
// Start - Issue Material Script
$(document).ready(function() {
    if (typeof isIssuePage !== 'undefined') {
        // Function to initialize select2
        function initializeSelect2() {
            $('.select2').select2();
        }

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
                <td>${productName}<input type="text" name="product_type_id[]" value="${productId}"><input type="text" name="stage_id[]" value="${stageId}"></td>
                <td>${materialText}<input type="text" name="material_id[]" value="${materialId}"></td>
                <td>${quantity}<input type="text" name="quantity[]" value="${quantity}"></td>
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
                <td>${productName}<input type="text" name="product_type_id[]" value="${productId}"><input type="text" name="stage_id[]" value="${stageId}"></td>
                <td>${stageName}<input type="text" name="material_id[]" value="${materialId}"></td>
                <td>${quantity}<input type="text" name="quantity[]" value="${quantity}"></td>
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
