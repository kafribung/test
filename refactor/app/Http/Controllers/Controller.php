<?php

namespace DTApi\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
// Grouping class
use Illuminate\Foundation\Validation\{
    ValidatesRequests,
    AuthorizesRequests,
    AuthorizesResources
};


class Controller extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;
}
