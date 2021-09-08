<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed

namespace App\Http\Middleware;

use App\Traits\CreateOrUpdateUserFromBuzzAPI;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RoboJackets\AuthStickler;

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
     * @param  Request  $request
     * @param  Closure  $next
     *
     * @return \Illuminate\Http\Response|Closure
     */
    public function handle(Request $request, Closure $next)
    {
        //Check to ensure the request isn't already authenticated through the API guard
        if (! Auth::guard('api')->check()) {
            // Run the user update only if they don't have an active session
            if ($this->cas->isAuthenticated() && null === $request->user()) {
                if ($this->cas->isMasquerading()) {
                    $this->cas->setAttributes(
                        [
                            'gtAccountEntitlement' => [
                                '/gt/central/services/iam/two-factor/duo-user',
                            ],
                            'authn_method' => 'duo-two-factor',
                        ]
                    );
                }

                $username = AuthStickler::check($this->cas);

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
