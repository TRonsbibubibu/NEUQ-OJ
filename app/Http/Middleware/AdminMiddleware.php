<?php

namespace NEUQOJ\Http\Middleware;

use Closure;
use NEUQOJ\Services\RoleService;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function handle($request, Closure $next, $id)
    {
        if($this->roleService->hasRole($id,'Administrator'))
            return $next($request);
    }
}