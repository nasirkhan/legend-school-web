<?php

namespace App;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

trait ApiAuthorizable
{
    /**
     * Default API controller method to permission-action map.
     */
    private array $apiAbilities = [
        'index' => 'view',
        'show' => 'view',
        'store' => 'add',
        'create' => 'add',
        'update' => 'edit',
        'edit' => 'edit',
        'destroy' => 'delete',
        'complete' => 'edit',
        'reopen' => 'edit',
    ];

    /**
     * Perform authorization before controller action runs.
     *
     * @return mixed
     */
    public function callAction($method, $parameters)
    {
        if ($ability = $this->getApiAbility($method)) {
            Gate::authorize($ability);
        }

        return parent::callAction($method, $parameters);
    }

    public function setApiAbilities(array $abilities): void
    {
        $this->apiAbilities = $abilities;
    }

    protected function getApiAbility(string $method): ?string
    {
        $routeName = explode('.', Route::currentRouteName() ?? '');
        $action = Arr::get($this->apiAbilities, $method);

        if (! $action) {
            return null;
        }

        if (count($routeName) < 4 || $routeName[0] !== 'api') {
            return null;
        }

        $resource = $routeName[count($routeName) - 2];

        return "{$action}_{$resource}";
    }
}
