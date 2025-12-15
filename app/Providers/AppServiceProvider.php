<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();

        $this->app->make(\Illuminate\Contracts\Debug\ExceptionHandler::class)->renderable(function (HttpException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {

                return response()->json([
                    'error' => $e->getMessage(),
                ], $e->getStatusCode(), [], JSON_UNESCAPED_UNICODE);
            }
        });
    }
}
