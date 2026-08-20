<?php

namespace App\Services;

use App\Models\User;

class TicketAuthorizationService
{
    /**
     * Danh sách tất cả các danh mục nghiệp vụ của Seller Support Ticket.
     */
    public const ALL_CATEGORIES = [
        'account_blocked',
        'withdrawal_issue',
        'product_rejected',
        'commission_fee',
        'other',
    ];

    /**
     * Lấy danh sách các categories mà user được phép xem và xử lý.
     *
     * @return string[]
     */
    public static function getAllowedCategoriesForUser(?User $user): array
    {
        if (! $user) {
            return [];
        }

        return match ($user->role) {
            'super-admin', 'admin' => self::ALL_CATEGORIES,
            'moderator' => ['product_rejected', 'other'],
            'accountant' => ['commission_fee', 'withdrawal_issue', 'other'],
            default => [],
        };
    }

    /**
     * Kiểm tra user hiện tại có thẩm quyền thao tác với danh mục ticket này không.
     */
    public static function canHandleCategory(?User $user, string $category): bool
    {
        $allowed = self::getAllowedCategoriesForUser($user);

        return in_array($category, $allowed, true);
    }
}
