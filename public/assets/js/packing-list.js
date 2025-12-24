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

        // Update split summary after all groups are created
        updateSplitSummary();
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

        // Update split summary after all groups are loaded
        updateSplitSummary();
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

                // Copy product to target group (allow splitting across groups)
                const productId = draggedElement.dataset.productId;
                const productName = draggedElement.dataset.productName;
                const article = draggedElement.dataset.article;
                const totalPcs = draggedElement.dataset.totalPcs;
                const pcsEachCarton = draggedElement.querySelector('.pcs-each-carton').value;

                const targetGroupIndex = targetGroup.dataset.groupIndex - 1;
                const targetProductsZone = targetGroup.querySelector('.products-zone');

                // Add to target group (creates a new instance of the product)
                addProductToGroup(targetProductsZone, productId, productName, article, targetGroupIndex, totalPcs, pcsEachCarton);

                // Update group headers
                updateGroupHeader(targetGroup);

                // Update split summary
                updateSplitSummary();

                // Note: We do NOT remove from source group - this allows the same product
                // to be split across multiple groups with different quantities
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

        // Generate unique instance ID for tracking split products
        const instanceId = `${productId}_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;

        const itemHtml = `
            <div class="product-item-row" draggable="true"
                 data-product-id="${productId}"
                 data-product-name="${productName}"
                 data-article="${article}"
                 data-total-pcs="${displayTotalPcs}"
                 data-instance-id="${instanceId}">
                <div class="row align-items-center mb-2">
                    <div class="col-md-4">
                        <div class="drag-handle">
                            <i class="fas fa-grip-vertical text-muted mr-2"></i>
                            <strong>${productName}</strong>
                        </div>
                        <small class="text-muted ml-4">Article: ${article}</small>
                        <input type="hidden" name="groups[${groupArrayIndex}][products][${productCount}][product_id]" value="${productId}">
                        <input type="hidden" name="groups[${groupArrayIndex}][products][${productCount}][total_pcs]" value="${displayTotalPcs}">
                        <input type="hidden" name="groups[${groupArrayIndex}][products][${productCount}][instance_id]" value="${instanceId}">
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
        const pcsInput = newItem.querySelector('.pcs-each-carton');

        // Make product draggable
        attachProductDragListeners(newItem);

        // Update summary when pcs value changes
        pcsInput.addEventListener('change', updateSplitSummary);
        pcsInput.addEventListener('input', updateSplitSummary);

        // Remove product button - removes this instance from the group
        removeBtn.addEventListener('click', function () {
            const groupElement = productsZone.closest('.carton-group');
            const remainingProducts = productsZone.querySelectorAll('.product-item-row').length;

            // If this is the only product in the group, remove the entire group
            if (remainingProducts === 1) {
                if (confirm('This will remove the entire carton group. Continue?')) {
                    groupElement.remove();
                    updateSplitSummary();
                }
            } else {
                // Just remove this product instance from the group
                newItem.remove();
                // Update header of source group
                updateGroupHeader(groupElement);
                updateSplitSummary();
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

    // Helper function to get all product instances across all groups
    function getAllProductInstances() {
        const instances = {};
        const groups = document.querySelectorAll('.carton-group');

        groups.forEach((group) => {
            const productsZone = group.querySelector('.products-zone');
            const items = productsZone.querySelectorAll('.product-item-row');

            items.forEach((item) => {
                const productId = item.dataset.productId;
                const instanceId = item.dataset.instanceId;
                const totalPcs = parseInt(item.dataset.totalPcs) || 0;
                const pcsEachCarton = parseInt(item.querySelector('.pcs-each-carton').value) || 0;

                if (!instances[productId]) {
                    instances[productId] = {
                        name: item.dataset.productName,
                        totalDelivered: totalPcs,
                        instances: []
                    };
                }

                instances[productId].instances.push({
                    instanceId: instanceId,
                    pcsEachCarton: pcsEachCarton
                });
            });
        });

        return instances;
    }

    // Update the split summary display
    function updateSplitSummary() {
        const productInstances = getAllProductInstances();
        const summaryDiv = document.getElementById('splitSummary');
        const summaryContent = document.getElementById('splitSummaryContent');

        let hasSplits = false;
        let summaryHtml = '';

        for (const productId in productInstances) {
            const product = productInstances[productId];

            if (product.instances.length > 1) {
                hasSplits = true;
                let totalAllocated = 0;
                let instancesHtml = '';

                product.instances.forEach((instance, index) => {
                    totalAllocated += instance.pcsEachCarton;
                    instancesHtml += `<span class="badge badge-secondary mr-2">Group ${index + 1}: ${instance.pcsEachCarton} pcs</span>`;
                });

                const remaining = product.totalDelivered - totalAllocated;
                const statusClass = remaining < 0 ? 'text-danger' : (remaining === 0 ? 'text-success' : 'text-warning');

                summaryHtml += `
                    <div class="mb-2">
                        <strong>${product.name}</strong><br>
                        <small>Delivered: ${product.totalDelivered} pcs | Allocated: ${totalAllocated} pcs | Remaining: <span class="${statusClass}">${remaining} pcs</span></small><br>
                        ${instancesHtml}
                    </div>
                `;
            }
        }

        if (hasSplits) {
            summaryContent.innerHTML = summaryHtml;
            summaryDiv.style.display = 'block';
        } else {
            summaryDiv.style.display = 'none';
        }
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

            // Validate that pcs_each_carton is filled for all products
            items.forEach((item) => {
                const pcsInput = item.querySelector('.pcs-each-carton');
                if (!pcsInput.value || parseInt(pcsInput.value) < 1) {
                    e.preventDefault();
                    alert(`Carton Group ${index + 1}: Please enter a valid "Pcs Each Carton" value for ${item.dataset.productName}`);
                    hasError = true;
                }
            });
        });

        if (hasError) {
            return false;
        }

        // Validate split products - total across all groups shouldn't exceed delivered quantity
        const productInstances = getAllProductInstances();
        for (const productId in productInstances) {
            const product = productInstances[productId];
            let totalAllocated = 0;

            product.instances.forEach((instance) => {
                totalAllocated += instance.pcsEachCarton;
            });

            if (totalAllocated > product.totalDelivered) {
                e.preventDefault();
                alert(`Product "${product.name}": Total allocated (${totalAllocated}) exceeds delivered quantity (${product.totalDelivered})`);
                hasError = true;
            }
        }

        return !hasError;
    });
});

