<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class PublicSitePageController extends Controller
{
    public function home(): Response
    {
        return Inertia::render('PublicSite/Home');
    }

    public function about(): Response
    {
        return Inertia::render('PublicSite/About');
    }
}
