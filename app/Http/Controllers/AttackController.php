<?php

namespace App\Http\Controllers;

use App\Models\AttackRotation;

class AttackController extends BaseRotationController
{
    protected function getModel(): string
    {
        return AttackRotation::class;
    }

    protected function getRouteName(): string
    {
        return 'attack';
    }
}