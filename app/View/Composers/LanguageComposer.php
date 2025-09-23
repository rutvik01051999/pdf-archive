<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\App;

class LanguageComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        // Get current language from request
        $currentLanguage = request()->get('lang', 'en');
        
        // Validate language parameter
        if (!in_array($currentLanguage, ['en', 'hi'])) {
            $currentLanguage = 'en';
        }
        
        // Set Laravel locale for translations
        App::setLocale($currentLanguage);
            
        $view->with('currentLanguage', $currentLanguage);
    }
}
