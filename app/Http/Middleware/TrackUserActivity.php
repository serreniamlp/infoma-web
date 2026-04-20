<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only track for authenticated users
        if (Auth::check() && !$request->ajax()) {
            $this->logActivity($request);
        }

        return $response;
    }

    protected function logActivity(Request $request)
    {
        try {
            UserActivity::create([
                'user_id' => Auth::id(),
                'action' => $this->getActionName($request),
                'description' => $this->getActionDescription($request),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        } catch (\Exception $e) {
            // Silently fail to not break the application
            \Log::error('Failed to log user activity: ' . $e->getMessage());
        }
    }

    protected function getActionName(Request $request)
    {
        $route = $request->route();

        if (!$route) {
            return 'page_visit';
        }

        $action = $route->getActionMethod();
        $uri = $request->getRequestUri();

        // Map common actions
        $actionMap = [
            'index' => 'view_list',
            'show' => 'view_detail',
            'create' => 'view_create_form',
            'store' => 'create_item',
            'edit' => 'view_edit_form',
            'update' => 'update_item',
            'destroy' => 'delete_item'
        ];

        return $actionMap[$action] ?? $action;
    }

    protected function getActionDescription(Request $request)
    {
        $route = $request->route();
        $uri = $request->getRequestUri();
        $method = $request->method();

        if (!$route) {
            return "Visited {$uri}";
        }

        $action = $route->getActionMethod();
        $routeName = $route->getName() ?? '';

        // Generate meaningful descriptions
        if (str_contains($routeName, 'residence')) {
            switch ($action) {
                case 'index':
                    return 'Viewed residences list';
                case 'show':
                    return 'Viewed residence details';
                case 'create':
                    return 'Opened residence creation form';
                case 'store':
                    return 'Created new residence';
                case 'edit':
                    return 'Opened residence edit form';
                case 'update':
                    return 'Updated residence';
                case 'destroy':
                    return 'Deleted residence';
            }
        }

        if (str_contains($routeName, 'activity')) {
            switch ($action) {
                case 'index':
                    return 'Viewed activities list';
                case 'show':
                    return 'Viewed activity details';
                case 'create':
                    return 'Opened activity creation form';
                case 'store':
                    return 'Created new activity';
                case 'edit':
                    return 'Opened activity edit form';
                case 'update':
                    return 'Updated activity';
                case 'destroy':
                    return 'Deleted activity';
            }
        }

        if (str_contains($routeName, 'booking')) {
            switch ($action) {
                case 'index':
                    return 'Viewed bookings list';
                case 'show':
                    return 'Viewed booking details';
                case 'store':
                    return 'Created new booking';
                case 'update':
                    return 'Updated booking status';
            }
        }

        return "{$method} {$uri}";
    }
}





















