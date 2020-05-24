<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed

namespace App\Http\Middleware;

use App\Traits\CreateOrUpdateUserFromBuzzAPI;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RoboJackets\ErrorPages\BadNetwork;
use RoboJackets\ErrorPages\EduroamISSDisabled;
use RoboJackets\ErrorPages\EduroamNonGatech;
use RoboJackets\ErrorPages\UsernameContainsDomain;
use RoboJackets\NetworkCheck;

class CASAuthenticate
{
    use CreateOrUpdateUserFromBuzzAPI;

    /**
     * Auth facade.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * CAS library interface.
     *
     * @var \Subfission\Cas\CasManager
     */
    protected $cas;

    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
        $this->cas = app('cas');
    }

    /**
     * Handle an incoming request.
     *
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function handle(Request $request, Closure $next)
    {
        //Check to ensure the request isn't already authenticated through the API guard
        if (! Auth::guard('api')->check()) {
            // Run the user update only if they don't have an active session
            if ($this->cas->isAuthenticated() && null === $request->user()) {
                $username = strtolower($this->cas->user());

                if (false !== strpos($username, '@')) {
                    foreach (array_keys($_COOKIE) as $key) {
                        setcookie($key, '', time() - 3600);
                    }
                    UsernameContainsDomain::render($username);
                    exit;
                }

                $network = NetworkCheck::detect();
                if (NetworkCheck::EDUROAM_ISS_DISABLED === $network) {
                    EduroamISSDisabled::render();
                    exit;
                }
                if (NetworkCheck::GTOTHER === $network) {
                    BadNetwork::render('GTother', $username, '');
                    exit;
                }
                if (NetworkCheck::GTVISITOR === $network) {
                    BadNetwork::render('GTvisitor', $username, '');
                    exit;
                }
                if (
                    NetworkCheck::EDUROAM_NON_GATECH_V4 === $network
                    || NetworkCheck::EDUROAM_NON_GATECH_V6 === $network
                ) {
                    EduroamNonGatech::render($username, '');
                    exit;
                }

                $user = $this->createOrUpdateUserFromBuzzAPI($username);
                Auth::login($user);
            }

            if ($this->cas->isAuthenticated() && null !== $request->user()) {
                //User is authenticated and already has an existing session
                return $next($request);
            }

            //User is not authenticated and does not have an existing session
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized', 401);
            }
            $this->cas->authenticate();
        }

        //User is authenticated through the API guard (I guess? Moving this into an else() broke sessions)
        return $next($request);
    }
}
