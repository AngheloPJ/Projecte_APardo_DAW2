<?php

return [

    // Acceso público
    '/'                => 'MainController@showAllArticles',
    'home'            => 'MainController@showAllArticles',
    'my-articles'     => 'MainController@showUserArticles',
    'login'           => 'LoginController@showLoginForm',
    'register'        => 'LoginController@showRegisterForm',

    // Acciones
    'login-submit'    => 'LoginController@login',
    'logout'          => 'LoginController@logout',
    'register-submit' => 'LoginController@register',

    // CRUD Articulos
    'article/create' => 'ArticleController@showCreateForm',
    'article/create-submit'=> 'ArticleController@create',
    'article/edit/{id}'    => 'ArticleController@showEditForm',
    'article/edit-submit'  => 'ArticleController@edit',
    'article/delete/{id}'  => 'ArticleController@delete',

    // Rutas con parámetros
    'author/{id}'     => 'MainControler@showUserArticle',
    'article/{id}'    => 'MainController@showArticle'
];
