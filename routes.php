<?php

/**
 * Grupo dos enpoints iniciados por v1
 */
$app->group('/v1', function() {

    /**
     * Dentro de v1, o recurso /book
     */
    $this->group('/book', function() {
        $this->get('', '\App\v1\Controllers\BookController:listBook');
    });

    /**
     * Dentro de v1, o recurso /auth
     */
    $this->group('/auth', function() {
        $this->post('', \App\v1\Controllers\AuthController::class);
    });
});