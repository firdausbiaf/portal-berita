<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        View::composer('layouts.public', function ($view): void {
            $headerCategories = Category::query()
                ->where('show_in_header', true)
                ->orderByRaw('menu_order IS NULL, menu_order ASC')
                ->orderBy('name')
                ->get();

            $dropdownCategories = Category::query()
                ->where('show_in_header', false)
                ->orderBy('name')
                ->get();

            $footerCategories = Category::query()
                ->orderBy('name')
                ->get();

            $view->with([
                'headerCategories' => $headerCategories,
                'dropdownCategories' => $dropdownCategories,
                'mobileCategories' => $headerCategories->concat($dropdownCategories),
                'footerCategories' => $footerCategories,
                'navCategories' => $footerCategories,
            ]);
        });
    }
}
