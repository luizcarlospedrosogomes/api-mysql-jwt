<?php
namespace App\Models\Entity;
/**
 * @Entity @Table(name="users")
 **/
class User {
   /**
     * @var int
     * @Id @Column(type="integer") 
     * @GeneratedValue
     */
    protected $id;
    /**
     * @var string
     * @Column(type="string") 
     */
    protected $login;
    /**
     * @var string
     * @Column(type="string") 
     */
    protected $password;

    public function getId(){
        return $this->id;
    }   
    
    public function getLogin(){
        return $this->login;
    }

    public function setLogin($login){
        
        $this->login = $login;
        return $this;  
    }

    public function getPassword(){
        return $this->password;
    }

    public function setPassword($password){
        
        $this->password = $password;
        return $this;  
    }

}