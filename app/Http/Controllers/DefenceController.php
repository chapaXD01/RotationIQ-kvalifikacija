<?php

namespace App\Http\Controllers;

use App\Models\DefenceRotation;

class DefenceController extends BaseRotationController
{
    protected function getModel(): string
    {
        return DefenceRotation::class;
    }

    protected function getRouteName(): string
    {
        return 'defence';
    }
}