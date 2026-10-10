<?php

namespace App\Http\Middleware;

use App\Enums\VerificationStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ShopOwnerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check() || ! Auth::user()->hasRole('owner')) {
            abort(404, 'Unauthorized');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Use the session-aware method — respects the shop switcher.
        // Falls back to an approved shop if the session has nothing.
        $tenant = $user->currentTenant();

        // Routes that must always be reachable — otherwise we loop.
        $isSetupRoute   = $request->routeIs('owner.business_setup')
            || $request->routeIs('owner.business_create')
            || $request->routeIs('owner.switch_shop');
        $isStatusRoute  = $request->routeIs('owner.business_status');
        $isProfileRoute = $request->routeIs('owner.profile')
            || $request->routeIs('owner.update_profile');

        $allowedWithoutApproval = $isSetupRoute || $isStatusRoute || $isProfileRoute;

        // 1. No tenant yet → force setup
        if (! $tenant) {
            return $allowedWithoutApproval
                ? $next($request)
                : redirect()->route('owner.business_setup');
        }

        // 2. Tenant exists but setup wasn't completed → setup
        if (! $tenant->business_setup_completed) {
            return $allowedWithoutApproval
                ? $next($request)
                : redirect()->route('owner.business_setup');
        }

        // 3. Rejected → status page (so they can see why)
        if ($tenant->verification_status === VerificationStatus::Rejected->value) {
            return $allowedWithoutApproval
                ? $next($request)
                : redirect()->route('owner.business_status');
        }

        // 4. Pending / under review / needs info → status page
        if ($tenant->verification_status !== VerificationStatus::Approved->value) {
            return $allowedWithoutApproval
                ? $next($request)
                : redirect()->route('owner.business_status');
        }

        // 5. Approved but shop is inactive → status page
        if (! $tenant->is_active) {
            return $allowedWithoutApproval
                ? $next($request)
                : redirect()->route('owner.business_status');
        }

        // 6. Everything checks out → let them through
        return $next($request);
    }
}