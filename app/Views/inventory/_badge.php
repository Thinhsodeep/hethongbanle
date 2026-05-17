<?php
function stockBadgeClass(string $status): string
{
    return match ($status) {
        'Hết hàng' => 'stripe-badge stripe-badge-danger',
        'Sắp hết' => 'stripe-badge stripe-badge-warning',
        default => 'stripe-badge stripe-badge-success',
    };
}
