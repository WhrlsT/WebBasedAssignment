<?php
require '_base.php';

$_title = 'Frequently Asked Questions - SigmaMart';
include '_head.php';
?>

<div class="container page-container">
    <h1 class="page-title">Frequently Asked Questions</h1>
    
    <div class="faq-container">
        <div class="faq-section">
            <h2>Orders & Shipping</h2>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>How do I track my order?</h3>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>You can track your order by logging into your account and visiting the "Order History" section. There, you'll find tracking information for all your orders. Alternatively, you can use the tracking number provided in your shipping confirmation email.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>What are the shipping options and costs?</h3>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>We offer several shipping options:</p>
                    <ul>
                        <li>Standard Shipping (3-5 business days): RM10</li>
                        <li>Express Shipping (1-2 business days): RM20</li>
                        <li>Free shipping on orders over RM150</li>
                    </ul>
                    <p>International shipping rates vary by destination.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>How long will it take to receive my order?</h3>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>Domestic orders typically arrive within 3-5 business days with standard shipping, or 1-2 business days with express shipping. International orders may take 7-14 business days depending on the destination and customs processing.</p>
                </div>
            </div>
        </div>
        
        <div class="faq-section">
            <h2>Returns & Refunds</h2>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>What is your return policy?</h3>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>We accept returns within 30 days of delivery for most items in their original condition. Some exclusions apply for hygiene reasons. Please contact our customer service team to initiate a return.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>How do I request a refund?</h3>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>To request a refund, please contact our customer service team with your order number. Once your return is received and inspected, we will process your refund. The money will be credited back to your original payment method within 5-7 business days.</p>
                </div>
            </div>
        </div>
        
        <div class="faq-section">
            <h2>Products & Inventory</h2>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>Do you offer pre-orders for upcoming products?</h3>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>Yes, we occasionally offer pre-orders for highly anticipated products. Pre-order items will be clearly marked on our website with an estimated release date. Your card will only be charged when the item ships.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>Are all products authentic?</h3>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>Absolutely! We only sell 100% authentic products sourced directly from manufacturers or authorized distributors. We stand behind the authenticity of every item we sell.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const faqItems = document.querySelectorAll('.faq-question');
    
    faqItems.forEach(item => {
        item.addEventListener('click', function() {
            const parent = this.parentElement;
            const answer = parent.querySelector('.faq-answer');
            const toggle = this.querySelector('.faq-toggle');
            
            // Toggle the answer visibility
            if (answer.style.display === 'block') {
                answer.style.display = 'none';
                toggle.textContent = '+';
            } else {
                answer.style.display = 'block';
                toggle.textContent = '-';
            }
        });
    });
});
</script>

<?php include '_foot.php'; ?>