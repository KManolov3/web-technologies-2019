<?php
class User{
 
    private $conn;
    private $table_name = "users";
 
    public $email;
    public $fname;
    public $lname;
    public $password;
    public $role;
 

    public function __construct($db){
        $this->conn = $db;
    }

    function read(){
    $query = "SELECT
                *
              FROM " . 
              $this->table_name;
 
    $stmt = $this->conn->prepare($query);
 
    $stmt->execute();
 
    return $stmt;
    }

    function create(){
 
        $query = "INSERT INTO
                    " . $this->table_name . "
                SET
                    email=:email, fname=:fname, lname=:lname, password=:password, role=:role";
     
        $stmt = $this->conn->prepare($query);

        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->fname=htmlspecialchars(strip_tags($this->fname));
        $this->lname=htmlspecialchars(strip_tags($this->lname));
        $this->password=htmlspecialchars(strip_tags($this->password));
        $this->role=htmlspecialchars(strip_tags($this->role));
     
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":fname", $this->fname);
        $stmt->bindParam(":lname", $this->lname);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":role", $this->role);
     
        if($stmt->execute()){
            return true;
        }
     
        return false;
    }

    function delete(){
        $this->email = htmlspecialchars(strip_tags($this->email));

        $query = "SELECT * FROM " . $this->table_name . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if(empty($row))
            return false;
        $this->fname = $row['fname'];
        $this->lname = $row['lname'];
        $this->role = $row['role'];

        $query = "DELETE FROM " . $this->table_name . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);

        if($stmt->execute()){
            return true;
        }
     
        return false;
     
    }


}

?>