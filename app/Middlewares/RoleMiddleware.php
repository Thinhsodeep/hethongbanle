<?php

declare(strict_types=1);

require_once ROOT_PATH . '/core/Middleware.php';

final class RoleMiddleware extends Middleware
{
    /** @var list<string> */
    private array $allowedRoles;

    public function __construct(array $allowedRoles)
    {
        $this->allowedRoles = $allowedRoles;
    }

    public function handle(): bool
    {
        $role = $_SESSION['role'] ?? null;
        return is_string($role) && in_array($role, $this->allowedRoles, true);
    }
}
