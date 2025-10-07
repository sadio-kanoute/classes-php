<?php

require_once 'config-pdo.php';

class Userpdo
{
    private $id;
    public $login;
    public $email;
    public $firstname;
    public $lastname;
    
    private $conn;
    
    public function __construct()
    {
        $this->conn = getPDOConnection();
        
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
        
        if ($stmt->execute([$login, $hashedPassword, $email, $firstname, $lastname])) {
            $this->id = $this->conn->lastInsertId();
            $this->login = $login;
            $this->email = $email;
            $this->firstname = $firstname;
            $this->lastname = $lastname;
            
            return $this->getAllInfos();
        }
        
        return false;
    }
    
    public function connect($login, $password)
    {
        $stmt = $this->conn->prepare("SELECT id, login, password, email, firstname, lastname FROM utilisateurs WHERE login = ?");
        $stmt->execute([$login]);
        
        if ($row = $stmt->fetch()) {
            if (password_verify($password, $row['password'])) {
                $this->id = $row['id'];
                $this->login = $row['login'];
                $this->email = $row['email'];
                $this->firstname = $row['firstname'];
                $this->lastname = $row['lastname'];
                
                return true;
            }
        }
        
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
            
            if ($stmt->execute([$this->id])) {
                $this->disconnect();
                return true;
            }
        }
        return false;
    }
    
    public function update($login, $password, $email, $firstname, $lastname)
    {
        if ($this->isConnected()) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $this->conn->prepare("UPDATE utilisateurs SET login = ?, password = ?, email = ?, firstname = ?, lastname = ? WHERE id = ?");
            
            if ($stmt->execute([$login, $hashedPassword, $email, $firstname, $lastname, $this->id])) {
                $this->login = $login;
                $this->email = $email;
                $this->firstname = $firstname;
                $this->lastname = $lastname;
                
                return true;
            }
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
    
    // Destructeur pour fermer la connexion (optionnel avec PDO)
    public function __destruct()
    {
        $this->conn = null;
    }
}

// =======================================
// TESTS DE LA CLASSE USERPDO (PDO)
// =======================================

echo "<h2>🔧 Tests de la classe Userpdo (PDO)</h2>";

// Test 1 : Inscription
echo "<h3>Test 1 : Inscription</h3>";
$userPdo1 = new Userpdo();
$result = $userPdo1->register("Marie25", "motdepasse", "marie@example.com", "Marie", "BERNARD");

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
$userPdo2 = new Userpdo();
if ($userPdo2->connect("Marie25", "motdepasse")) {
    echo "✅ Connexion réussie !<br>";
    echo "Utilisateur connecté: " . $userPdo2->getLogin() . "<br>";
    echo "Email: " . $userPdo2->getEmail() . "<br>";
    echo "Est connecté: " . ($userPdo2->isConnected() ? "Oui" : "Non") . "<br>";
    
    // Test 3 : Mise à jour
    echo "<h3>Test 3 : Mise à jour</h3>";
    if ($userPdo2->update("Marie25_PDO", "newpass123", "marie.pdo@example.com", "Marie", "LEFEBVRE")) {
        echo "✅ Mise à jour réussie !<br>";
        echo "Nouveau login: " . $userPdo2->getLogin() . "<br>";
        echo "Nouvel email: " . $userPdo2->getEmail() . "<br>";
    } else {
        echo "❌ Erreur lors de la mise à jour<br>";
    }
    
    echo "<hr>";
    
    // Test 4 : Getters individuels
    echo "<h3>Test 4 : Getters individuels</h3>";
    echo "🔸 Login: " . $userPdo2->getLogin() . "<br>";
    echo "🔸 Email: " . $userPdo2->getEmail() . "<br>";
    echo "🔸 Prénom: " . $userPdo2->getFirstname() . "<br>";
    echo "🔸 Nom: " . $userPdo2->getLastname() . "<br>";
    echo "🔸 ID: " . $userPdo2->getId() . "<br>";
    
    echo "<hr>";
    
    // Test 5 : Déconnexion
    echo "<h3>Test 5 : Déconnexion</h3>";
    $userPdo2->disconnect();
    echo "Après déconnexion - Est connecté: " . ($userPdo2->isConnected() ? "Oui" : "Non") . "<br>";
    
    // Test 6 : Reconnexion pour suppression
    echo "<h3>Test 6 : Reconnexion et suppression</h3>";
    if ($userPdo2->connect("Marie25_PDO", "newpass123")) {
        echo "✅ Reconnexion réussie !<br>";
        if ($userPdo2->delete()) {
            echo "✅ Suppression réussie !<br>";
            echo "Est encore connecté: " . ($userPdo2->isConnected() ? "Oui" : "Non") . "<br>";
        } else {
            echo "❌ Erreur lors de la suppression<br>";
        }
    }
} else {
    echo "❌ Erreur de connexion<br>";
}

echo "<br><strong>🔍 Vérifiez les résultats dans phpMyAdmin > base 'classes' > table 'utilisateurs'</strong>";

?>