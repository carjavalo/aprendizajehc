<?php

namespace App\Menu\Filters;

use Illuminate\Support\Facades\Auth;
use JeroenNoten\LaravelAdminLte\Menu\Builder;
use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

class RoleFilter implements FilterInterface
{
    /**
     * Transforms a menu item. Add restricted attribute if user doesn't have the required role.
     *
     * @param  array  $item  A menu item
     * @return array  The transformed menu item
     */
    public function transform($item)
    {
        // Check if the item has role restrictions
        if (isset($item['role'])) {
            // Get the current user's role
            $userRole = Auth::check() ? Auth::user()->role : null;
            
            // Convert role to array if it's a string
            $allowedRoles = is_array($item['role']) ? $item['role'] : [$item['role']];
            
            // If user role is not in allowed roles, mark item as restricted
            if (!in_array($userRole, $allowedRoles)) {
                $item['restricted'] = true;
            }
        }
        
        return $item;
    }
}
