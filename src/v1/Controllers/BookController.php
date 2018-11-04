<?php
namespace App\v1\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//use App\Models\Entity\Book;

/**
 * Controller v1 de livros
 */
class BookController{

    /**
     * Container Class
     * @var [object]
     */
    private $container;

    /**
     * Undocumented function
     * @param [object] $container
     */
    public function __construct($container) {
        $this->container = $container;
    }
    
    /**
     * Listagem de Livros
     * @param [type] $request
     * @param [type] $response
     * @param [type] $args
     * @return Response
     */
    public function listBook($request, $response, $args) {
        $books = ['Clube da luta',  'O poder do hábito', 'Triste fim de Policarpo Quaresma'];
        $return = $response->withJson($books, 200)
            ->withHeader('Content-type', 'application/json');
        return $return;        
    }
    
}