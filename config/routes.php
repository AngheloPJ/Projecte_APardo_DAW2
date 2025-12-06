<?php

use config\Route;

// Páginas públicas
Route::get('/', 'MainController@showAllArticles');
Route::get('home', 'MainController@showAllArticles');
Route::get('my-articles', 'MainController@showUserArticles');

// Login / registro
Route::get('login', 'LoginController@showLoginForm');
Route::get('register', 'LoginController@showRegisterForm');
Route::get('logout', 'SessionController@logout');

Route::post('login-submit', 'LoginController@login');
Route::post('register-submit', 'LoginController@register');

// Perfil
Route::get('profile/edit', 'UserController@showEditForm');
Route::post('profile/edit-submit', 'UserController@editSubmit');
Route::get('profile/{id}', 'UserController@showUserProfile');

// Artículos
Route::get('article/create', 'ArticleController@showCreateForm');
Route::post('article/create-submit', 'ArticleController@create');

Route::get('article/edit/{id}', 'ArticleController@showEditForm');
Route::post('article/edit-submit', 'ArticleController@edit');

Route::get('article/delete/{id}', 'ArticleController@delete');
Route::get('article/{id}', 'MainController@showArticle');
