<?php
function stockBadgeClass(string $status): string
{
    return match ($status) {
        'Hết hàng' => 'bg-danger',
        'Sắp hết' => 'bg-warning text-dark',
        default => 'bg-success',
    };
}
