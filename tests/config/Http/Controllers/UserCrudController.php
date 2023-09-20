<?php

namespace Backpack\CRUD\Tests\Config\Http\Controllers;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\Tests\config\Models\User;

class UserCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;

    public function setup()
    {
        $this->crud->setModel(User::class);
        $this->crud->setRoute('users');
    }

    public function setupUpdateOperation()
    {
    }

    protected function create()
    {
        return response('create');
    }
}
