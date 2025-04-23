function addToCart(productId, quantity = 1) {
    $.ajax({
        url: 'add_to_cart.php',
        type: 'POST',
        data: {
            product_id: productId,
            quantity: quantity
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Get product details to show in popup
                $.ajax({
                    url: 'get_product_details.php',
                    type: 'GET',
                    data: {
                        id: productId
                    },
                    dataType: 'json',
                    success: function(productData) {
                        if (productData.success) {
                            // Show popup with product details
                            showCartPopup(productData.name, quantity, productData.price);
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('An error occurred while getting product details');
                    }
                });
            } else if (response.redirect) {
                // User needs to log in
                window.location.href = response.redirect;
            } else {
                // Show error message
                alert(response.message);
            }
        },
        error: function() {
            alert('An error occurred while adding the product to cart');
        }
    });
}

// Add this at the end of the file, before the closing </body> tag
document.addEventListener('DOMContentLoaded', function() {
    // Handle quantity buttons
    const minusBtn = document.querySelector('.quantity-btn.minus');
    const plusBtn = document.querySelector('.quantity-btn.plus');
    const quantityInput = document.querySelector('#quantity');
    
    if (minusBtn && plusBtn && quantityInput) {
        minusBtn.addEventListener('click', function() {
            const currentValue = parseInt(quantityInput.value);
            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
            }
        });
        
        plusBtn.addEventListener('click', function() {
            const currentValue = parseInt(quantityInput.value);
            const maxValue = parseInt(quantityInput.getAttribute('max'));
            if (currentValue < maxValue) {
                quantityInput.value = currentValue + 1;
            }
        });
    }
    
    // Handle form submission
    const addToCartForm = document.querySelector('.add-to-cart-form');
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const productId = this.querySelector('input[name="product_id"]').value;
            const quantity = this.querySelector('input[name="quantity"]').value;
            
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Get product details from the page
                    const productName = document.querySelector('.product-title').textContent;
                    const productPrice = document.querySelector('.product-price').textContent.replace('RM', '');
                    
                    showCartPopup(productName, quantity, productPrice);
                } else if (data.redirect) {
                    // User is not logged in, redirect to login page
                    window.location.href = data.redirect;
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }
});

function showCartPopup(productName, quantity, price) {
    // Create popup elements if they don't exist
    if (!document.getElementById('cart-popup-overlay')) {
        const overlay = document.createElement('div');
        overlay.id = 'cart-popup-overlay';
        overlay.className = 'cart-popup-overlay';
        document.body.appendChild(overlay);
        
        const popup = document.createElement('div');
        popup.id = 'cart-popup';
        popup.className = 'cart-popup';
        popup.innerHTML = `
            <div class="cart-popup-header">
                <h3>Added to Cart</h3>
                <button class="cart-popup-close" onclick="closeCartPopup()">&times;</button>
            </div>
            <div class="cart-popup-content">
                <div class="cart-popup-item">
                    <div class="cart-popup-item-row">
                        <span class="cart-popup-label">Item Name:</span>
                        <span class="cart-popup-value" id="popup-product-name"></span>
                    </div>
                    <div class="cart-popup-item-row">
                        <span class="cart-popup-label">Quantity:</span>
                        <span class="cart-popup-value" id="popup-quantity"></span>
                    </div>
                    <div class="cart-popup-item-row">
                        <span class="cart-popup-label">Price:</span>
                        <span class="cart-popup-value" id="popup-price"></span>
                    </div>
                </div>
            </div>
            <div class="cart-popup-actions">
                <a href="javascript:void(0)" class="cart-popup-btn cart-popup-continue" onclick="closeCartPopup()">Continue Shopping</a>
                <a href="cart.php" class="cart-popup-btn cart-popup-cart">Go to Cart</a>
            </div>
        `;
        document.body.appendChild(popup);
    }
    
    // Update popup content
    document.getElementById('popup-product-name').textContent = productName;
    document.getElementById('popup-quantity').textContent = quantity;
    document.getElementById('popup-price').textContent = 'RM' + parseFloat(price).toFixed(2);
    
    // Show popup
    document.getElementById('cart-popup-overlay').style.display = 'block';
    document.getElementById('cart-popup').style.display = 'block';
}

function closeCartPopup() {
    document.getElementById('cart-popup-overlay').style.display = 'none';
    document.getElementById('cart-popup').style.display = 'none';
}

$(document).ready(function() {
    // Auto update cart when quantity changes
    $('.quantity-input').on('change', function() {
        const cartId = $(this).data('cart-id');
        const quantity = $(this).val();
        
        // Update the cart via AJAX
        updateCart(cartId, quantity);
    });
    
    // Remove item when remove button is clicked
    $('.remove-button').on('click', function() {
        const cartId = $(this).data('cart-id');
        
        // Remove the item via AJAX
        updateCart(cartId, 0);
    });
    
    // Function to update cart
    function updateCart(cartId, quantity) {
        $.ajax({
            url: 'update_cart.php',
            type: 'POST',
            data: {
                cart_id: cartId,
                quantity: quantity
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (quantity <= 0) {
                        // Remove the row if quantity is 0
                        $('input[data-cart-id="' + cartId + '"]').closest('tr').remove();
                    } else {
                        // Update the subtotal for this item
                        const row = $('input[data-cart-id="' + cartId + '"]').closest('tr');
                        const price = row.find('.item-subtotal').data('price');
                        const newSubtotal = price * quantity;
                        row.find('.item-subtotal').text('RM' + newSubtotal.toFixed(2));
                    }
                    
                    // Update the cart summary
                    $('#cart-subtotal').text('RM' + response.subtotal.toFixed(2));
                    $('#shipping-cost').text('RM' + response.shipping.toFixed(2));
                    $('#cart-total').text('RM' + response.total.toFixed(2));
                    $('#item-count').text(response.item_count);
                    
                    // If cart is empty, reload the page to show empty cart message
                    if (response.item_count === 0) {
                        location.reload();
                    }
                } else {
                    alert(response.message || 'An error occurred. Please try again.');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    }
});

