<?php

// File generated from our OpenAPI spec

namespace Stripe;

/**
 * A usage record summary represents an aggregated view of how much usage was accrued for a subscription item within a subscription billing period.
 *
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property null|string $invoice The invoice in which this usage period has been billed for.
 * @property bool $livemode Has the value <code>true</code> if the object exists in live mode or the value <code>false</code> if the object exists in test mode.
 * @property StripeObject $period
 * @property string $subscription_item The ID of the subscription item this summary is describing.
 * @property int $total_usage The total usage within this usage period.
 */
class UsageRecordSummary extends ApiResource
{
    const OBJECT_NAME = 'usage_record_summary';
}
