// Packing List Drag and Drop Functionality
document.addEventListener('DOMContentLoaded', function () {
    let groupCounter = 0;
    let draggedElement = null;
    let draggedFromGroup = null; // Track which group the product is being dragged from

    // Check if we're on edit page and load existing data
    if (typeof existingCartons !== 'undefined' && existingCartons.length > 0) {
        loadExistingCartons();
    } else if (typeof deliveryProducts !== 'undefined' && deliveryProducts.length > 0) {
        // Create page: auto-create one group per product
        autoCreateGroupsForProducts();
    }

    // Add Carton Group Button (for manual addition if needed)
    const addGroupBtn = document.getElementById('addCartonGroup');
    if (addGroupBtn) {
        addGroupBtn.addEventListener('click', function () {
            addCartonGroup();
        });
    }

    function autoCreateGroupsForProducts() {
        deliveryProducts.forEach((product, index) => {
            groupCounter++;
            const container = document.getElementById('cartonGroupsContainer');

            // Debug: Log product data
            console.log('Product:', product.name, 'Quantity:', product.quantity);

            const groupHtml = `
                <div class="carton-group" data-group-index="${groupCounter}">
                    <div class="carton-group-header">
                        <span class="group-title">Carton Group ${groupCounter} - ${product.name}</span>
                        <button type="button" class="btn btn-sm btn-danger remove-group">
                            <i class="fas fa-times"></i> Remove
                        </button>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label>Carton From</label>
                            <input type="number" class="form-control carton-from" name="groups[${groupCounter - 1}][carton_from]" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label>Carton To</label>
                            <input type="number" class="form-control carton-to" name="groups[${groupCounter - 1}][carton_to]" min="1" required>
                        </div>
                    </div>
                    <div class="products-zone" data-group="${groupCounter}">
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', groupHtml);
            const newGroup = container.lastElementChild;
            attachGroupEventListeners(newGroup);

            // Add product to this group
            const productsZone = newGroup.querySelector('.products-zone');
            addProductToGroup(productsZone, product.product_id, product.name, product.article_no, groupCounter - 1, product.quantity);
        });
    }

    function loadExistingCartons() {
        existingCartons.forEach((carton, index) => {
            groupCounter++;
            const container = document.getElementById('cartonGroupsContainer');

            // Build product names for header
            let productNames = '';
            if (carton.items && carton.items.length > 0) {
                productNames = carton.items.map(item => item.name).join(', ');
            }

            const groupHtml = `
                <div class="carton-group" data-group-index="${groupCounter}">
                    <div class="carton-group-header">
                        <span class="group-title">Carton Group ${groupCounter}${productNames ? ' - ' + productNames : ''}</span>
                        <button type="button" class="btn btn-sm btn-danger remove-group">
                            <i class="fas fa-times"></i> Remove
                        </button>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label>Carton From</label>
                            <input type="number" class="form-control carton-from" name="groups[${groupCounter - 1}][carton_from]" min="1" value="${carton.carton_from}" required>
                        </div>
                        <div class="col-md-6">
                            <label>Carton To</label>
                            <input type="number" class="form-control carton-to" name="groups[${groupCounter - 1}][carton_to]" min="1" value="${carton.carton_to}" required>
                        </div>
                    </div>
                    <div class="products-zone" data-group="${groupCounter}">
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', groupHtml);
            const newGroup = container.lastElementChild;
            attachGroupEventListeners(newGroup);

            // Add existing products to this carton
            if (carton.items && carton.items.length > 0) {
                const productsZone = newGroup.querySelector('.products-zone');
                carton.items.forEach((item, itemIndex) => {
                    addProductToGroup(productsZone, item.product_id, item.name, item.article_no, groupCounter - 1, item.total_pcs, item.pcs_each_carton);
                });
            }
        });
    }

    function addCartonGroup(productId, productName, article, totalPcs) {
        groupCounter++;
        const container = document.getElementById('cartonGroupsContainer');

        const groupHtml = `
            <div class="carton-group" data-group-index="${groupCounter}">
                <div class="carton-group-header">
                    <span class="group-title">Carton Group ${groupCounter}${productName ? ' - ' + productName : ''}</span>
                    <button type="button" class="btn btn-sm btn-danger remove-group">
                        <i class="fas fa-times"></i> Remove
                    </button>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6">
                        <label>Carton From</label>
                        <input type="number" class="form-control carton-from" name="groups[${groupCounter - 1}][carton_from]" min="1" required>
                    </div>
                    <div class="col-md-6">
                        <label>Carton To</label>
                        <input type="number" class="form-control carton-to" name="groups[${groupCounter - 1}][carton_to]" min="1" required>
                    </div>
                </div>
                <div class="products-zone" data-group="${groupCounter}">
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', groupHtml);

        // Attach event listeners to the new group
        const newGroup = container.lastElementChild;
        attachGroupEventListeners(newGroup);

        // If product info provided, add it to the group
        if (productId && productName) {
            const productsZone = newGroup.querySelector('.products-zone');
            addProductToGroup(productsZone, productId, productName, article, groupCounter - 1, totalPcs);
        }

        return newGroup;
    }

    function attachGroupEventListeners(groupElement) {
        // Remove group button
        const removeBtn = groupElement.querySelector('.remove-group');
        removeBtn.addEventListener('click', function () {
            if (confirm('Are you sure you want to remove this carton group?')) {
                groupElement.remove();
            }
        });

        // Products zone - allow dropping products from other groups
        const productsZone = groupElement.querySelector('.products-zone');

        productsZone.addEventListener('dragover', function (e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });

        productsZone.addEventListener('dragleave', function (e) {
            this.classList.remove('drag-over');
        });

        productsZone.addEventListener('drop', function (e) {
            e.preventDefault();
            this.classList.remove('drag-over');

            if (draggedElement && draggedFromGroup) {
                const targetGroup = this.closest('.carton-group');
                const sourceGroup = draggedFromGroup;

                // Don't do anything if dropped in same group
                if (targetGroup === sourceGroup) {
                    return;
                }

                // Move product to target group
                const productId = draggedElement.dataset.productId;
                const productName = draggedElement.dataset.productName;
                const article = draggedElement.dataset.article;
                const totalPcs = draggedElement.dataset.totalPcs;
                const pcsEachCarton = draggedElement.querySelector('.pcs-each-carton').value;

                const targetGroupIndex = targetGroup.dataset.groupIndex - 1;
                const targetProductsZone = targetGroup.querySelector('.products-zone');

                // Add to target group
                addProductToGroup(targetProductsZone, productId, productName, article, targetGroupIndex, totalPcs, pcsEachCarton);

                // Remove from source group
                draggedElement.remove();

                // Update group headers
                updateGroupHeader(targetGroup);
                updateGroupHeader(sourceGroup);

                // If source group is now empty, remove it
                const sourceProductsZone = sourceGroup.querySelector('.products-zone');
                if (sourceProductsZone.querySelectorAll('.product-item-row').length === 0) {
                    sourceGroup.remove();
                }
            }
        });
    }

    function updateGroupHeader(groupElement) {
        const productsZone = groupElement.querySelector('.products-zone');
        const productItems = productsZone.querySelectorAll('.product-item-row');
        const groupTitle = groupElement.querySelector('.group-title');
        const groupNumber = groupElement.dataset.groupIndex;

        if (productItems.length > 0) {
            const productNames = Array.from(productItems).map(item => item.dataset.productName).join(', ');
            groupTitle.textContent = `Carton Group ${groupNumber} - ${productNames}`;
        } else {
            groupTitle.textContent = `Carton Group ${groupNumber}`;
        }
    }

    function addProductToGroup(productsZone, productId, productName, article, groupArrayIndex, totalPcs, pcsEachCarton) {
        // Count existing products in this zone
        const productCount = productsZone.querySelectorAll('.product-item-row').length;

        // If pcsEachCarton not provided, use empty string
        const pcsValue = pcsEachCarton || '';

        // Ensure totalPcs has a value (fallback to 0 if undefined/null)
        const displayTotalPcs = totalPcs || 0;

        const itemHtml = `
            <div class="product-item-row" draggable="true"
                 data-product-id="${productId}"
                 data-product-name="${productName}"
                 data-article="${article}"
                 data-total-pcs="${displayTotalPcs}">
                <div class="row align-items-center mb-2">
                    <div class="col-md-4">
                        <div class="drag-handle">
                            <i class="fas fa-grip-vertical text-muted mr-2"></i>
                            <strong>${productName}</strong>
                        </div>
                        <small class="text-muted ml-4">Article: ${article}</small>
                        <input type="hidden" name="groups[${groupArrayIndex}][products][${productCount}][product_id]" value="${productId}">
                        <input type="hidden" name="groups[${groupArrayIndex}][products][${productCount}][total_pcs]" value="${displayTotalPcs}">
                    </div>
                    <div class="col-md-3">
                        <label class="mb-0">Pcs Each Carton</label>
                        <input type="number" class="form-control form-control-sm pcs-each-carton"
                               name="groups[${groupArrayIndex}][products][${productCount}][pcs_each_carton]"
                               min="1" value="${pcsValue}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="mb-0">Total Pcs (Delivered)</label>
                        <div class="total-pcs-display font-weight-bold text-dark">${displayTotalPcs}</div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-sm btn-danger remove-product" title="Remove from group">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        productsZone.insertAdjacentHTML('beforeend', itemHtml);

        const newItem = productsZone.lastElementChild;
        const removeBtn = newItem.querySelector('.remove-product');

        // Make product draggable
        attachProductDragListeners(newItem);

        // Remove product button - creates new group with this product
        removeBtn.addEventListener('click', function () {
            const groupElement = productsZone.closest('.carton-group');
            const remainingProducts = productsZone.querySelectorAll('.product-item-row').length;

            // If this is the only product in the group, just delete the group
            if (remainingProducts === 1) {
                if (confirm('This will remove the entire carton group. Continue?')) {
                    groupElement.remove();
                }
            } else {
                // Create new group with this product (split functionality)
                const pcsEachValue = newItem.querySelector('.pcs-each-carton').value;
                const newGroup = addCartonGroup(productId, productName, article, totalPcs);

                // Set the pcs each carton value if it was filled
                if (pcsEachValue) {
                    const newPcsInput = newGroup.querySelector('.pcs-each-carton');
                    if (newPcsInput) {
                        newPcsInput.value = pcsEachValue;
                    }
                }

                // Remove from current group
                newItem.remove();

                // Update header of source group
                updateGroupHeader(groupElement);
            }
        });
    }

    function attachProductDragListeners(productElement) {
        productElement.addEventListener('dragstart', function (e) {
            draggedElement = this;
            draggedFromGroup = this.closest('.carton-group');
            this.classList.add('dragging');
        });

        productElement.addEventListener('dragend', function (e) {
            this.classList.remove('dragging');
            draggedElement = null;
            draggedFromGroup = null;
        });
    }

    // Form validation before submit
    document.getElementById('packingListForm').addEventListener('submit', function (e) {
        const groups = document.querySelectorAll('.carton-group');

        if (groups.length === 0) {
            e.preventDefault();
            alert('Please add at least one carton group!');
            return false;
        }

        let hasError = false;
        groups.forEach((group, index) => {
            const productsZone = group.querySelector('.products-zone');
            const items = productsZone.querySelectorAll('.product-item-row');

            if (items.length === 0) {
                e.preventDefault();
                alert(`Carton Group ${index + 1} has no products. Please add products or remove the group.`);
                hasError = true;
                return false;
            }
        });

        return !hasError;
    });
});

