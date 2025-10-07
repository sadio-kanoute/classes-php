<?php

class User
{
    private $id;
    public $login;
    public $email;
    public $firstname;
    public $lastname;
    
    private $conn;
    
    public function __construct()
    {
        $this->conn = new mysqli("localhost", "root", "", "classes");
        if ($this->conn->connect_error) {
            die("Erreur de connexion: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8mb4");
        
        $this->id = null;
        $this->login = '';
        $this->email = '';
        $this->firstname = '';
        $this->lastname = '';
    }
    
    public function register($login, $password, $email, $firstname, $lastname)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $this->conn->prepare("INSERT INTO utilisateurs (login, password, email, firstname, lastname) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $login, $hashedPassword, $email, $firstname, $lastname);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->insert_id;
            $this->login = $login;
            $this->email = $email;
            $this->firstname = $firstname;
            $this->lastname = $lastname;
            
            $stmt->close();
            return $this->getAllInfos();
        }
        
        $stmt->close();
        return false;
    }
    
    public function connect($login, $password)
    {
        $stmt = $this->conn->prepare("SELECT id, login, password, email, firstname, lastname FROM utilisateurs WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $this->id = $row['id'];
                $this->login = $row['login'];
                $this->email = $row['email'];
                $this->firstname = $row['firstname'];
                $this->lastname = $row['lastname'];
                
                $stmt->close();
                return true;
            }
        }
        
        $stmt->close();
        return false;
    }
    
    public function disconnect()
    {
        $this->id = null;
        $this->login = '';
        $this->email = '';
        $this->firstname = '';
        $this->lastname = '';
    }
    
    public function delete()
    {
        if ($this->isConnected()) {
            $stmt = $this->conn->prepare("DELETE FROM utilisateurs WHERE id = ?");
            $stmt->bind_param("i", $this->id);
            
            if ($stmt->execute()) {
                $stmt->close();
                $this->disconnect();
                return true;
            }
            
            $stmt->close();
        }
        return false;
    }
    
    public function update($login, $password, $email, $firstname, $lastname)
    {
        if ($this->isConnected()) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $this->conn->prepare("UPDATE utilisateurs SET login = ?, password = ?, email = ?, firstname = ?, lastname = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $login, $hashedPassword, $email, $firstname, $lastname, $this->id);
            
            if ($stmt->execute()) {
                $this->login = $login;
                $this->email = $email;
                $this->firstname = $firstname;
                $this->lastname = $lastname;
                
                $stmt->close();
                return true;
            }
            
            $stmt->close();
        }
        return false;
    }
    
    public function isConnected()
    {
        return $this->id !== null;
    }
    
    public function getAllInfos()
    {
        return [
            'id' => $this->id,
            'login' => $this->login,
            'email' => $this->email,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname
        ];
    }
    
    public function getLogin()
    {
        return $this->login;
    }
    
    public function getEmail()
    {
        return $this->email;
    }
    
    public function getFirstname()
    {
        return $this->firstname;
    }
    
    public function getLastname()
    {
        return $this->lastname;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    // Destructeur pour fermer la connexion
    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// =======================================
// TESTS DE LA CLASSE USER (MySQLi)
// =======================================

echo "<h2>🔧 Tests de la classe User (MySQLi)</h2>";

// Test 1 : Inscription
echo "<h3>Test 1 : Inscription</h3>";
$user1 = new User();
$result = $user1->register("Tom13", "azerty", "thomas@gmail.com", "Thomas", "DUPONT");

if ($result) {
    echo "✅ Inscription réussie !<br>";
    echo "ID: " . $result['id'] . "<br>";
    echo "Login: " . $result['login'] . "<br>";
    echo "Email: " . $result['email'] . "<br>";
    echo "Nom complet: " . $result['firstname'] . " " . $result['lastname'] . "<br>";
} else {
    echo "❌ Erreur lors de l'inscription<br>";
}

echo "<hr>";

// Test 2 : Connexion
echo "<h3>Test 2 : Connexion</h3>";
$user2 = new User();
if ($user2->connect("Tom13", "azerty")) {
    echo "✅ Connexion réussie !<br>";
    echo "Utilisateur connecté: " . $user2->getLogin() . "<br>";
    echo "Email: " . $user2->getEmail() . "<br>";
    echo "Est connecté: " . ($user2->isConnected() ? "Oui" : "Non") . "<br>";
    
    // Test 3 : Mise à jour
    echo "<h3>Test 3 : Mise à jour</h3>";
    if ($user2->update("Tom13_updated", "newpassword", "thomas.updated@gmail.com", "Thomas", "MARTIN")) {
        echo "✅ Mise à jour réussie !<br>";
        echo "Nouveau login: " . $user2->getLogin() . "<br>";
        echo "Nouvel email: " . $user2->getEmail() . "<br>";
    } else {
        echo "❌ Erreur lors de la mise à jour<br>";
    }
    
    echo "<hr>";
    
    // Test 4 : Récupération des infos
    echo "<h3>Test 4 : Toutes les informations</h3>";
    $infos = $user2->getAllInfos();
    echo "📋 Informations complètes:<br>";
    foreach ($infos as $key => $value) {
        echo "- $key: $value<br>";
    }
    
    echo "<hr>";
    
    // Test 5 : Suppression
    echo "<h3>Test 5 : Suppression</h3>";
    if ($user2->delete()) {
        echo "✅ Suppression réussie !<br>";
        echo "Est encore connecté: " . ($user2->isConnected() ? "Oui" : "Non") . "<br>";
    } else {
        echo "❌ Erreur lors de la suppression<br>";
    }
} else {
    echo "❌ Erreur de connexion<br>";
}

echo "<br><strong>🔍 Vérifiez les résultats dans phpMyAdmin > base 'classes' > table 'utilisateurs'</strong>";

?>