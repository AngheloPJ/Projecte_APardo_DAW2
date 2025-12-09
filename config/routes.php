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

// Admin: gestión de usuarios
Route::get('admin/users', 'AdminController@listUsers');                       // listar todos
Route::get('admin/users/edit/{id}', 'AdminController@editForm');              // formulario edición
Route::post('admin/users/edit-submit/{id}', 'AdminController@editSubmit');    // guardar cambios
Route::get('admin/users/delete/{id}', 'AdminController@delete');             // eliminar usuario

// Artículos
Route::get('article/create', 'ArticleController@showForm');  
Route::post('article/create-submit', 'ArticleController@create');

Route::get('article/edit/{id}', 'ArticleController@showForm');  
Route::post('article/edit-submit', 'ArticleController@edit');

Route::get('article/{id}', 'MainController@showArticle');
