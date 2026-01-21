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

// Perfil propio
Route::get('profile', 'UserController@showUserProfile');
Route::get('profile/edit', 'UserController@showEditForm');
Route::post('profile/edit-submit', 'UserController@editSubmit');

// Admin: Gestión de usuarios / Listar usuarios
Route::get('admin/users', 'AdminController@listUsers');                       
Route::get('admin/users/edit/{id}', 'AdminController@editForm');
Route::post('admin/users/edit-submit/{id}', 'AdminController@editSubmit');
Route::get('admin/users/delete/{id}', 'AdminController@delete');

// Artículos
Route::get('article/create', 'ArticleController@showForm');  
Route::post('article/create-submit', 'ArticleController@create');

Route::get('article/edit/{id}', 'ArticleController@showForm');  
Route::post('article/edit-submit', 'ArticleController@edit');

Route::get('article/delete/{id}', 'ArticleController@delete');
Route::post('article/delete/{id}', 'ArticleController@delete');

Route::get('article/{id}', 'MainController@showArticle');
