<?php
// Stripe API configuration
require_once 'lib/stripe_lib/init.php';

// Set your Stripe API keys
$stripeSecretKey = 'sk_test_51RGsMIRtqjLfe2OnOmbz9kDgBQdM9Ci6Y0a2Qg7UZWTcNYB787M6TPiYUEaDajtUmqIJ5dTIdWmXgB41cFxo0V7E00NSTCZT0H';
$stripePublishableKey = 'pk_test_51RGsMIRtqjLfe2OniGSnmWatqr87PbsT0ZK45t3rofZSRry829QK3uyjzQrr1IUa7J7OSgG2tTlqcJqXTLJYVbbu008H3omUd4';

// Set Stripe API key
\Stripe\Stripe::setApiKey($stripeSecretKey);