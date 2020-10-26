<?php

namespace FDT\DataLoader;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;

class DataLoaderAuthServiceProvider extends AuthServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [];

    /**
     * Register the application's policies.
     *
     * @return void
     */
    public function registerPolicies()
    {
        foreach ($this->policies as $key => $value) {
            Gate::policy($key, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->registerPolicies();

        Gate::define('dataloader-admin', function ($user) {
            $region = $user->region ? $user->region : session('region');

            if (!empty($region)) {
                $emails = config('dataloader.admin.' . $region);
            } else {
                $emails = config('dataloader.admin');
            }
            return in_array($user->email, explode(';', $emails));
        });

        Gate::define('file-request', function ($user) {
            return true;
        });
    }
}
