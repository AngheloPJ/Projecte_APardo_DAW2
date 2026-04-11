<?php

use config\Route;

// Iniciales
Route::get('/', 'MainController@showAllArticles');
Route::get('home', 'MainController@showAllArticles');
Route::get('my-articles', 'MainController@showUserArticles');

// Login / registro
Route::get('login', 'LoginController@showLoginForm');
Route::get('register', 'LoginController@showRegisterForm');
Route::get('logout', 'SessionController@logout');

// Recuperar password
Route::get('/forgot-password', 'PasswordResetController@showForgotPasswordForm');
Route::post('/forgot-password', 'PasswordResetController@forgotPassword');
Route::get('/reset-password', 'PasswordResetController@showResetPasswordForm');
Route::post('/reset-password-submit', 'PasswordResetController@resetPassword');

Route::post('login-submit', 'LoginController@login');
Route::post('register-submit', 'LoginController@register');

// Perfil
Route::get('profile/edit', 'UserController@showUserProfile');
Route::post('profile/edit-submit', 'UserController@editSubmit');

// Perfil - Api Key
Route::post('profile/api-key/generate', 'UserController@generateApiKey');

// Admin
Route::get('admin/users', 'UserController@listUsers');                       
Route::get('admin/users/edit/{id}', 'UserController@showEditUser');
Route::post('admin/users/edit-submit/{id}', 'UserController@editUserSubmit');
Route::post('admin/users/delete/{id}', 'UserController@delete');

// Artículos
Route::get('article/create', 'ArticleController@showForm');  
Route::post('article/create-submit', 'ArticleController@create');

Route::get('article/edit/{id}', 'ArticleController@showForm');  
Route::post('article/edit/{id}', 'ArticleController@edit');  
Route::post('article/edit-submit', 'ArticleController@edit');

Route::post('article/delete/{id}', 'ArticleController@delete');

Route::get('article/search', 'MainController@searchArticles');
Route::get('article/search/results', 'MainController@fetchArticles');
Route::get('article/{id}', 'MainController@showArticle');

// OAuth (Discord)
Route::get('oauth/redirect/{provider}', 'OAuthCallbackController@redirect');
Route::get('oauth/discord', 'OAuthCallbackController@discord');

// HybridAuth (GitHub)
Route::get('oauth/hybridauth/redirect/{provider}', 'OAuthCallbackController@hybridRedirect');
Route::get('oauth/hybridauth/callback/{provider}', 'OAuthCallbackController@hybridCallback');

Route::get('oauth/choose-username', 'OAuthCallbackController@chooseUsername');
Route::post('oauth/choose-username-submit', 'OAuthCallbackController@chooseUsernameSubmit');
Route::get('oauth/confirm-link', 'OAuthCallbackController@confirmLink');

// API
Route::post('api/auth/login', 'ApiSessionController@login');
Route::post('api/auth/refresh', 'ApiSessionController@refresh');
Route::post('api/auth/logout', 'ApiSessionController@logout');
Route::get('api/articles', 'ApiController@listArticles');

// Steam API
Route::get('api/steam/news', 'SteamNewsController@listNews');
Route::post('api/steam/news/publish', 'SteamNewsController@publishAsArticle');