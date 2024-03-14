$(document).ready(function() {
    // Function to initialize select2
    function initializeSelect2() {
        $('.select2').select2();
    }

    updateSerialNumbers(); // Update serial numbers after deleting a row

    // Function to update available stock
    function updateAvailableStock(materialId) {
        // Calculate total quantity of the same material present in the table
        var totalQuantityInTable = 0;
        $('#items-table tbody tr').each(function() {
            var rowMaterialId = $(this).find('input[name="material_id[]"]').val();
            if (rowMaterialId === materialId) {
                totalQuantityInTable += parseInt($(this).find('input[name="quantity[]"]').val());
            }
        });

        // Find the stock item for the selected material
        var stockItem = stockData.find(item => item.material_id == materialId);
        if (stockItem) {
            // Calculate available stock by subtracting total quantity in table from total received
            var availableStock = stockItem.total_received - stockItem.total_returned - totalQuantityInTable;
            $('#available_stock').val(availableStock);
        } else {
            // If no stock item found, set available stock to 0
            $('#available_stock').val(0);
        }
    }

    // Manually trigger AJAX request to load products based on preselected order on page load
    loadProductsBasedOnOrder();

    // Function to load products based on preselected order
    function loadProductsBasedOnOrder() {
        var orderId = $('#order_id').val();

        $.ajax({
            // url: "{{ route('ajaxPT') }}",
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

    // Event listener for change in product type
    $('#product_type_id').on('change', function() {
        var productId = $(this).val();
        $.ajax({
            // url: "{{ route('ajaxPM') }}",
            url: ajaxPMUrl,
            type: "GET",
            data: { productId: productId },
            dataType: "json",
            success: function(response) {
                $('#material_id').empty().append('<option value="" disabled selected>Select Material</option>');
                response.data.forEach(function(item) {
                    $('#material_id').append(new Option(item.name, item.material_id));
                });
                $('#material_id').trigger('change');
            }
        });
    });

    // Event listener for select2:select event on material ID
    $('#material_id').on('select2:select', function(e) {
        var selectedMaterialId = e.params.data.id;
        updateAvailableStock(selectedMaterialId);
    });

    // Event listener for click on add button
    $('#addBtn').click(function() {
        var productId = $('#product_type_id').val();
        var materialId = $('#material_id').val();
        var materialText = $('#material_id option:selected').text();
        var productName = $('#product_type_id option:selected').text();
        var quantity = parseInt($('input[name="quantity"]').val());
        var availableStock = parseInt($('#available_stock').val());

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
            <td>${productName}<input type="hidden" name="product_type_id[]" value="${productId}"></td>
            <td>${materialText}<input type="hidden" name="material_id[]" value="${materialId}"></td>
            <td>${quantity}<input type="hidden" name="quantity[]" value="${quantity}"></td>
            <td><button type="button" class="btn btn-danger deleteRow">X</button></td>
        </tr>`;

        $('#items-table tbody').append(markup);

        $('#material_id').val(null).trigger('change');
        $('input[name="quantity"]').val('');
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
        var currentAvailableStock = parseInt($('#available_stock').val());
        var updatedStock = currentAvailableStock + quantityToRemove;
        $('#available_stock').val(updatedStock);
        $(this).closest('tr').remove();
        updateSerialNumbers(); // Update serial numbers after deleting a row
    });

    // Initialize select2
    initializeSelect2();
});