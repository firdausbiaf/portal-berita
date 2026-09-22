<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * Generate dynamic robots.txt content.
     */
    public function index(): Response
    {
        $sitemapUrl = url('/sitemap.xml');

        $content = "User-agent: *\n"
            ."Disallow: /admin/\n\n"
            ."Sitemap: {$sitemapUrl}\n";

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }
}
