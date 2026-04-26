<?php
declare(strict_types=1);
namespace Kursach;

final class Permissions
{
    public static function rolePermissions(): array
    {
        return [
            'guest'     => ['products.read'],
            'user'      => ['products.read', 'products.create', 'profile.read', 'tickets.create', 'tickets.read_own', 'comments.create'],
            'support'   => ['products.read', 'profile.read', 'tickets.read', 'tickets.respond', 'tickets.manage'],
            'moderator' => ['products.read', 'products.moderate', 'profile.read'],
            'admin'     => ['admin.all', 'users.list', 'users.block', 'users.roles'],
        ];
    }

    public static function can(AuthContext $auth, string $permission): bool
    {
        $map = self::rolePermissions();
        foreach ($auth->roles as $role) {
            $role = strtolower($role);
            $perms = $map[$role] ?? [];
            if (in_array('admin.all', $perms, true)) return true;
            if (in_array($permission, $perms, true)) return true;
        }
        return false;
    }

    public static function require(AuthContext $auth, string $permission): void
    {
        if (!self::can($auth, $permission)) {
            Response::json([
                'error' => 'Forbidden',
                'permission' => $permission,
                'roles' => $auth->roles
            ], 403);
        }
    }
}
