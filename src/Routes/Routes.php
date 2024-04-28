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
    $app->post('/article/add', [ArticleController::class, 'addArticle']); // Article add        
    $app->post('/article/status', [ArticleController::class, 'updateStatus']); // Article update status        
    $app->post('/article/comment', [ArticleController::class, 'addComment']); // Article add comment        
});

$app->group('/course', function () use ($app) {
    $app->get('/course/list', [CourseController::class, 'listCourses']); // Course list           
    $app->get('/course/list/user/{id}', [CourseController::class, 'listByUser']); // Course list by user      
    $app->post('/course/add', [CourseController::class, 'addCourse']); // Course add
    $app->post('/course/edit', [CourseController::class, 'editCourse']); // Course edit
    $app->delete('/course/del/{id}', [CourseController::class, 'delCourse']); // Course del        
});