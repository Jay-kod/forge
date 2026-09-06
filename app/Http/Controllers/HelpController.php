<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class HelpController extends Controller
{
    /**
     * Display documentation, guides, and support resources.
     */
    public function index(): Response
    {
        return Inertia::render('Help/Index');
    }
}
