<?php

use MacoBackend\Controllers\ArticleController;
use MacoBackend\Controllers\CourseController;
use MacoBackend\Controllers\HomeController;
use MacoBackend\Controllers\UserController;

// Default route
$app->get('/', [HomeController::class, 'home']);
$app->get('/ping', [HomeController::class, 'ping']);

$app->group('/user', function () use ($app) {
    $app->post('/user/login', [UserController::class, 'login']); // Login   
    $app->post('/user/register', [UserController::class, 'register']); // Register   
    $app->post('/user/recoverpassword', [UserController::class, 'recoverPassword']); // Recover password
    $app->get('/user/list', [UserController::class, 'list']); // User list      
});

$app->group('/article', function () use ($app) {
    $app->get('/article/list', [ArticleController::class, 'listByAdmin']); // Article list  
    $app->get('/article/list/advisor/{id}', [ArticleController::class, 'listByAdvisor']); // Article list      
    $app->post('/article/add', [ArticleController::class, 'add']); // Article add        
});

$app->group('/course', function () use ($app) {
    $app->get('/course/list', [CourseController::class, 'list']); // Course list           
    $app->post('/course/add', [CourseController::class, 'add']); // Course add
    $app->post('/course/edit', [CourseController::class, 'edit']); // Course edit
    $app->delete('/course/del/{id}', [CourseController::class, 'del']); // Course del        
});