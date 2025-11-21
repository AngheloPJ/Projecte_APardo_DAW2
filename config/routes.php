<?php

return [

    // Acceso público
    '/'             => 'MainController@showAllArticles',
    '/home'         => 'MainController@showAllArticles',
    '/login'        => 'LoginController@showLoginForm',
    '/register'     => 'LoginController@showRegisterForm',

    // Acciones
    '/login-submit'    => 'LoginController@login',
    '/logout'          => 'LoginController@logout',
    '/register-submit' => 'LoginController@register',

    // Rutas con parámetros
    '/article/{id}' => 'MainController@showArticle'
];
