<?php
namespace App\v1\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Firebase\JWT\JWT;
use App\Models\Entity\User as User;

/**
 * Controller de Autenticação
 */
class AuthController {

    /**
     * Container
     * @var object s
     */
   protected $container;
   
   /**
    * Undocumented function
    * @param ContainerInterface $container
    */
   public function __construct($container) {
       $this->container = $container;
   }
   
  
   public function __invoke(Request $request, Response $response, $args) {
    $em = $this->container->get('em');
    $key           = $this->container->get("secretkey");
 
    $dados = json_decode($request->getBody(),true);

    $user         = $em->getRepository(User::class)
                                ->findOneBy(array('login'   => $dados['login']
                                                ,'password' => md5($dados['senha'])
                                )
                            );
                            
    if($user){
        $logger = $this->container->get('autenticacao');
        $logger->info('login_ok', ['id' => $user->getId(),'usuario' => $user->getLogin()]);
        $jwt           = $this->__getToken($user->getId(),$user->getLogin(), $key);
        return $response->withJson(["auth-jwt" => $jwt], 200)
                        ->withHeader('Content-type', 'application/json');   
        
    }else{
        $logger = $this->container->get('autenticacao');
        $logger->info('erro_login', ['usuario' => $dados['login']]);
        return $response->withJson(["erro_login" => "Falha no login"], 401)
                        ->withHeader('Content-type', 'application/json');   
        
    }
   }


   private function __getToken($id, $user, $secret){
		$future = time();
		$token = array(
                'id' => $id,
				'user' => $user, 
				'iat' => time(), 
				'exp' => time()+(60*60*24) 
			
		);
		return JWT::encode($token, $secret, "HS256");
    }
   
}