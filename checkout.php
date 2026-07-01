<?php
/**
 * Enhanced Checkout with Payment Integration
 * PHASE 6: Payment Integration
 * TEST MODE - Using Razorpay test credentials
 */

require_once 'init.php';
require_once 'config/Razorpay.php';

$razorpayMode = defined('RAZORPAY_MODE') ? RAZORPAY_MODE : 'unknown';
$razorpayModeLabel = $razorpayMode === 'live' ? 'LIVE' : ($razorpayMode === 'test' ? 'TEST' : 'UNKNOWN');
$razorpayEnabled = in_array($razorpayMode, ['test', 'live'], true);

// Redirect to login if not authenticated
if (!isset($_SESSION['cid']) && !isset($_SESSION['customer_id']) && !isset($_SESSION['customer_logged_in'])) {
    header('Location: auth.php?redirect=' . urlencode('checkout.php'));
    exit;
}

$siteTitle = 'Checkout';
$errorMessage = '';

// Get customer details from session
$customerId = $_SESSION['cid'] ?? $_SESSION['customer_id'] ?? 0;
$customerName = $_SESSION['cname'] ?? $_SESSION['customer_name'] ?? '';
$customerEmail = $_SESSION['cemail'] ?? $_SESSION['customer_email'] ?? '';
$customerPhone = $_SESSION['cphone'] ?? $_SESSION['customer_phone'] ?? '';

// Get cart items
$cartItems = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_map('intval', $_SESSION['cart']);
    $idList = implode(',', $ids);
    $query = mysqli_query($conn, "SELECT Apid, apname, apprice FROM apadd WHERE Apid IN ($idList)");
    
    if ($query) {
        while ($row = mysqli_fetch_assoc($query)) {
            $qty = isset($_SESSION['cart_qty'][$row['Apid']]) ? (int)$_SESSION['cart_qty'][$row['Apid']] : 1;
            $lineTotal = $qty * (float)$row['apprice'];
            $total += $lineTotal;
            
            $cartItems[] = [
                'id' => (int)$row['Apid'],
                'name' => $row['apname'],
                'price' => (float)$row['apprice'],
                'qty' => $qty,
                'line_total' => $lineTotal,
            ];
        }
    }
}

if (empty($cartItems)) {
    $errorMessage = 'Your cart is empty. Add items before placing an order.';
}

include 'templates/header.php';
?>

<div class="row justify-content-center">
    <div class="col-xl-10">
        <div class="text-center mb-4">
            <h2 class="h4 mb-2">Checkout</h2>
            <p class="text-secondary mb-0">Complete your order with shipping and payment details.</p>
            <!-- TEST MODE BADGE -->
            <div class="mt-2">
                <?php if ($razorpayEnabled): ?>
                    <span class="badge bg-<?php echo $razorpayMode === 'live' ? 'success' : 'warning'; ?> text-dark">
                        ⚠️ <?php echo strtoupper($razorpayModeLabel); ?> MODE - Using Razorpay <?php echo strtoupper($razorpayMode === 'live' ? 'Live' : 'Test'); ?> Credentials
                    </span>
                <?php else: ?>
                    <span class="badge bg-danger text-white">⚠️ Razorpay not configured. Please set valid API keys in .env</span>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
            <div class="alert alert-warning">Your cart is empty. <a href="index.php?page=1">Browse products</a> to add items.</div>
        <?php else: ?>
            <div class="row gx-4 gy-4">
                <!-- Left Column: Shipping & Payment -->
                <div class="col-lg-7">
                    <!-- Shipping Information -->
                    <section class="card sidebar-card shadow-sm mb-4">
                        <div class="card-body">
                            <h4 class="h5 mb-4">📦 Shipping Information</h4>
                            <form id="checkoutForm">
                                <div class="mb-3">
                                    <label class="form-label" for="customer_name">Full Name <span class="text-danger">*</span></label>
                                    <input 
                                        id="customer_name" 
                                        name="customer_name" 
                                        type="text" 
                                        class="form-control" 
                                        value="<?php echo htmlspecialchars($customerName); ?>" 
                                        placeholder="Enter your full name"
                                        required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="customer_phone">Contact Number <span class="text-danger">*</span></label>
                                    <input 
                                        id="customer_phone" 
                                        name="customer_phone" 
                                        type="tel" 
                                        class="form-control" 
                                        value="<?php echo htmlspecialchars($customerPhone); ?>" 
                                        placeholder="10-digit mobile number"
                                        pattern="[0-9]{10}"
                                        required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="shipping_address">Shipping Address <span class="text-danger">*</span></label>
                                    <textarea 
                                        id="shipping_address" 
                                        name="shipping_address" 
                                        class="form-control" 
                                        rows="4"
                                        placeholder="Enter complete shipping address"
                                        required></textarea>
                                </div>
                            </form>
                        </div>
                    </section>

                    <!-- Payment Method Selection -->
                    <section class="card sidebar-card shadow-sm">
                        <div class="card-body">
                            <h4 class="h5 mb-4">💳 Payment Method</h4>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input 
                                        class="form-check-input" 
                                        type="radio" 
                                        name="payment_method" 
                                        id="payment_cod" 
                                        value="COD" 
                                        checked>
                                    <label class="form-check-label" for="payment_cod">
                                        <strong>Cash on Delivery (COD)</strong>
                                        <br>
                                        <small class="text-muted">Pay when you receive the order</small>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input 
                                        class="form-check-input" 
                                        type="radio" 
                                        name="payment_method" 
                                        id="payment_razorpay" 
                                        value="Razorpay"
                                        <?php echo $razorpayEnabled ? '' : 'disabled'; ?> >
                                    <label class="form-check-label" for="payment_razorpay">
                                        <strong>Razorpay Secure Payment (<?php echo $razorpayModeLabel; ?> MODE)</strong>
                                        <br>
                                        <small class="text-muted">Card, UPI, Net Banking, Wallet - All in one</small>
                                        <br>
                                        <?php if ($razorpayMode === 'test'): ?>
                                            <small class="badge bg-info">Test Credentials: Use any valid card for testing</small>
                                        <?php elseif ($razorpayMode === 'live'): ?>
                                            <small class="badge bg-success">Live mode enabled. Real payment will be charged.</small>
                                        <?php else: ?>
                                            <small class="badge bg-danger">Razorpay is unavailable until credentials are configured.</small>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>

                            <!-- Test Card Info -->
                            <div id="testCardInfo" class="alert alert-info d-none" role="alert">
                                <?php if ($razorpayMode === 'test'): ?>
                                    <strong>Razorpay Test Credentials:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li><strong>Test card:</strong> 4111 1111 1111 1111</li>
                                        <li><strong>Expiry:</strong> Any future date</li>
                                        <li><strong>CVV:</strong> Any 3 digits</li>
                                        <li><strong>Alternative:</strong> UPI ID <code>test@razorpay</code></li>
                                    </ul>
                                    <hr>
                                    <p class="mb-0"><strong>Note:</strong> If this test card is rejected with an international transaction error, your Razorpay account may not support international cards. Use the UPI test flow or enable international transactions in the Razorpay dashboard.</p>
                                <?php elseif ($razorpayMode === 'live'): ?>
                                    <strong>Razorpay Live Mode:</strong>
                                    <p class="mb-0">This checkout will process a real payment. Ensure your Razorpay live credentials are valid and your account is fully activated.</p>
                                <?php else: ?>
                                    <strong>Razorpay unavailable:</strong>
                                    <p class="mb-0">Razorpay is not configured. Set valid test or live API keys in your .env file to enable this payment method.</p>
                                <?php endif; ?>
                            </div>

                            <button 
                                type="button" 
                                id="placeOrderBtn"
                                class="btn btn-primary btn-lg w-100 mt-4"
                                onclick="handleCheckout()">
                                <span id="btnText">Place Order</span>
                            </button>
                        </div>
                    </section>
                </div>

                <!-- Right Column: Order Summary -->
                <div class="col-lg-5">
                    <section class="card sidebar-card shadow-sm h-fit">
                        <div class="card-body">
                            <h4 class="h5 mb-4">📋 Order Summary</h4>
                            
                            <ul class="list-group mb-3">
                                <?php foreach ($cartItems as $item): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <div><?php echo htmlspecialchars($item['name']); ?></div>
                                            <small class="text-muted">₹<?php echo number_format($item['price'], 2); ?> × <?php echo (int)$item['qty']; ?></small>
                                        </div>
                                        <span class="fw-semibold">₹<?php echo number_format($item['line_total'], 2); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <!-- Pricing Breakdown -->
                            <div class="bg-light p-3 rounded mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal</span>
                                    <span>₹<?php echo number_format($total, 2); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Shipping</span>
                                    <span class="badge bg-success">FREE</span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold h5 mb-0">Total Amount</span>
                                    <span class="h4 mb-0 text-primary">₹<?php echo number_format($total, 2); ?></span>
                                </div>
                            </div>

                            <!-- Customer Info -->
                            <div class="alert alert-info">
                                <small><strong>Logged in as:</strong> <?php echo htmlspecialchars($customerName ?: $customerEmail); ?></small>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Razorpay Test Mode Warning Modal -->
<div class="modal fade" id="testModeWarning" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header <?php echo $razorpayMode === 'live' ? 'bg-danger' : 'bg-warning'; ?>">
                <h5 class="modal-title">
                    <?php echo $razorpayMode === 'live' ? '⚠️ LIVE MODE' : '⚠️ TEST MODE'; ?>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php if ($razorpayMode === 'live'): ?>
                    <p>You are using <strong>Razorpay in LIVE MODE</strong>. A real payment will be charged.</p>
                    <p>Verify the order details carefully before proceeding.</p>
                <?php else: ?>
                    <p>You are using <strong>Razorpay in TEST MODE</strong>. No real money will be charged.</p>
                    <p>Use the test card credentials provided to complete the payment.</p>
                <?php endif; ?>
                <p><strong>This checkout will open Razorpay's secure payment modal.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Proceed with Payment</button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Processing Script -->
<script>
const RAZORPAY_KEY = '<?php echo addslashes(defined('RAZORPAY_KEY_ID') ? RAZORPAY_KEY_ID : (getenv('RAZORPAY_KEY_ID') ?: '')); ?>';
const RAZORPAY_MODE = '<?php echo addslashes($razorpayMode); ?>';
const RAZORPAY_ENABLED = <?php echo $razorpayEnabled ? 'true' : 'false'; ?>;
const TOTAL_AMOUNT = <?php echo $total; ?>;
const CART_ITEMS = <?php echo json_encode($cartItems); ?>;
const RAZORPAY_SCRIPT_URL = 'https://checkout.razorpay.com/v1/checkout.js';

function loadRazorpayScript() {
    return new Promise((resolve, reject) => {
        if (window.Razorpay) {
            return resolve(true);
        }

        let script = document.querySelector('script[src="' + RAZORPAY_SCRIPT_URL + '"]');
        if (script && !window.Razorpay) {
            script.remove();
            script = null;
        }

        if (!script) {
            script = document.createElement('script');
            script.src = RAZORPAY_SCRIPT_URL;
            script.async = true;
            script.crossOrigin = 'anonymous';
            document.head.appendChild(script);
        }

        script.onload = () => {
            if (window.Razorpay) {
                resolve(true);
            } else {
                reject(new Error('Razorpay library loaded but did not initialize.'));
            }
        };

        script.onerror = () => reject(new Error('Failed to load Razorpay checkout library.'));
    });
}

/**
 * Handle checkout process
 */
async function handleCheckout() {
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
    const customerName = document.getElementById('customer_name').value.trim();
    const customerPhone = document.getElementById('customer_phone').value.trim();
    const shippingAddress = document.getElementById('shipping_address').value.trim();

    // Validate form
    if (!customerName || !customerPhone || !shippingAddress) {
        alert('Please fill in all shipping details');
        return;
    }

    if (customerPhone.length !== 10 || !/^\d+$/.test(customerPhone)) {
        alert('Please enter a valid 10-digit phone number');
        return;
    }

    // Show loading state
    showLoading(true);

    try {
        // Show test mode warning for Razorpay
        if (paymentMethod === 'Razorpay') {
            if (RAZORPAY_MODE === 'unknown' || !RAZORPAY_KEY) {
                throw new Error('Razorpay is not configured correctly. Please check your API credentials.');
            }
            const confirmed = await showTestModeWarning();
            if (!confirmed) {
                showLoading(false);
                return;
            }
        }

        // Create order via API
        const response = await fetch('api/payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            },
            body: new URLSearchParams({
                payment_method: paymentMethod,
                shipping_address: shippingAddress,
                cart_items: JSON.stringify(CART_ITEMS.map(item => ({
                    id: item.id,
                    qty: item.qty,
                    price: item.price
                })))
            }),
            credentials: 'same-origin'
        });

        let data;
        const text = await response.text();
        try {
            data = JSON.parse(text);
        } catch (jsonError) {
            throw new Error('Server returned invalid JSON: ' + text);
        }

        if (!response.ok) {
            throw new Error(data.error || 'Order creation failed with status ' + response.status);
        }

        if (!data.success) {
            throw new Error(data.error || 'Failed to create order');
        }

        if (paymentMethod === 'Razorpay') {
            await loadRazorpayScript();
            // Initiate Razorpay payment
            initiateRazorpayPayment(data);
        } else {
            // COD - Redirect to tracking
            window.location.href = `Track.php?order=${data.order_id}`;
        }

    } catch (error) {
        console.error('Checkout error:', error);
        let userMessage = error.message;
        if (/international|not supported|transaction not allowed|gateway rejected/i.test(error.message)) {
            userMessage += '\n\nTip: In Razorpay TEST MODE, try using the UPI test ID test@razorpay or a supported local test card instead.';
        }
        alert('Error: ' + userMessage);
        showLoading(false);
    }
}

/**
 * Initiate Razorpay payment
 */
function initiateRazorpayPayment(orderData) {
    const options = {
        key: orderData.razorpay_key || RAZORPAY_KEY,
        amount: orderData.amount * 100, // Amount in paise
        currency: 'INR',
        order_id: orderData.razorpay_order_id,
        
        handler: function(response) {
            verifyPayment(
                response.razorpay_payment_id,
                response.razorpay_order_id,
                response.razorpay_signature,
                orderData.order_id
            );
        },
        
        prefill: {
            name: orderData.customer_name,
            email: orderData.customer_email,
            contact: orderData.customer_phone
        },
        
        notes: {
            order_id: orderData.order_id
        },
        
        theme: {
            color: '#2563eb'
        },
        
        modal: {
            ondismiss: function() {
                showLoading(false);
                alert('Payment cancelled. Your order has been created with pending payment status.');
                window.location.href = `payment_cancelled.php?order=${orderData.order_id}`;
            }
        }
    };

    const rzp = new Razorpay(options);
    rzp.open();
}

/**
 * Verify payment after Razorpay redirect
 */
async function verifyPayment(paymentId, orderId, signature, ourOrderId) {
    try {
        const response = await fetch('api/payment.php?action=verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                razorpay_payment_id: paymentId,
                razorpay_order_id: orderId,
                razorpay_signature: signature,
                order_id: ourOrderId
            })
        });

        const data = await response.json();

        if (data.success) {
            // Redirect to tracking
            window.location.href = `Track.php?order=${ourOrderId}`;
        } else {
            throw new Error(data.error || 'Payment verification failed');
        }
    } catch (error) {
        console.error('Verification error:', error);
        alert('Payment verification failed: ' + error.message);
        showLoading(false);
    }
}

/**
 * Show test mode warning
 */
function showTestModeWarning() {
    return new Promise((resolve) => {
        if (window.jQuery && typeof window.jQuery === 'function') {
            const $modal = window.jQuery('#testModeWarning');
            const modal = $modal.modal({ show: false });
            const proceedBtn = $modal.find('.modal-footer .btn-primary');
            const cancelBtn = $modal.find('.modal-footer .btn-secondary');

            const cleanup = () => {
                proceedBtn.off('click', handleProceed);
                cancelBtn.off('click', handleCancel);
                $modal.off('hidden.bs.modal', handleDismiss);
            };

            const handleProceed = () => {
                $modal.modal('hide');
                resolve(true);
                cleanup();
            };

            const handleCancel = () => {
                $modal.modal('hide');
                resolve(false);
                cleanup();
            };

            const handleDismiss = () => {
                resolve(false);
                cleanup();
            };

            proceedBtn.on('click', handleProceed);
            cancelBtn.on('click', handleCancel);
            $modal.on('hidden.bs.modal', handleDismiss);
            $modal.modal('show');
            return;
        }

        const confirmText = RAZORPAY_MODE === 'live'
            ? 'You are using Razorpay in LIVE MODE. A real payment will be charged. Proceed with payment?'
            : RAZORPAY_MODE === 'test'
                ? 'You are using Razorpay in TEST MODE. No real money will be charged. Proceed with payment?'
                : 'Razorpay mode is not configured correctly. Proceed with payment?';
        const confirmed = window.confirm(confirmText);
        resolve(confirmed);
    });
}

/**
 * Show/hide loading state
 */
function showLoading(isLoading) {
    const btn = document.getElementById('placeOrderBtn');
    const btnText = document.getElementById('btnText');
    
    if (isLoading) {
        btn.disabled = true;
        btnText.textContent = 'Processing...';
    } else {
        btn.disabled = false;
        btnText.textContent = 'Place Order';
    }
}

/**
 * Toggle test card info visibility
 */
document.getElementById('payment_razorpay').addEventListener('change', function() {
    document.getElementById('testCardInfo').classList.toggle('d-none', !this.checked);
});

// Show test card info on page load if Razorpay was selected
window.addEventListener('load', function() {
    if (document.getElementById('payment_razorpay').checked) {
        document.getElementById('testCardInfo').classList.remove('d-none');
    }
});
</script>

<?php include 'templates/footer.php'; ?>
