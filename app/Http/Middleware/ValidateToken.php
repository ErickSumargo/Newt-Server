<?php

namespace App\Http\Middleware;

use App\Helpers\Token;
use Closure;
use Request;

class ValidateToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */

    protected $token;

    public function handle($request, Closure $next)
    {
        $this->token = new Token();
        if (!$this->isHeaderValid(Request::header('Authorization'))) {
            $_response = [
                'success' => false
            ];
            return response($_response, 200);
        }
        return $next($request);
    }

    private function isHeaderValid($headers)
    {
        $headers = explode(' ', $headers);
        if (count($headers) < 3) {
            return false;
        }
        $app = $headers[0];
        $user_type = $headers[1];
        $token = $headers[2];

        if ($app != 'Newt' && $app != 'Newt_Pro' && $app != 'Newt_Admin') {
            return false;
        }
        if ($user_type != 'student' && $user_type != 'teacher' && $user_type != 'admin' && $user_type != 'guest') {
            return false;
        }
        return $this->token->validate($user_type, $token);
    }
}