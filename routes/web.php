<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
});

Route::get('/lang/{lang}', function ($lang) {
    session()->put('lang', $lang);
    return redirect()->back();
})->name('lang')->middleware('webLang');
