<?php
session_start();
$host = 'localhost'; 
$dbname = 'bibliotheque';
$username = 'root';  
$password = 'sio2024'; 

date_default_timezone_set('Europe/Paris');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo '<script>alert("Connexion échouée : ' . $e->getMessage() . '");</script>';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_livres = $_POST['id_livre'];
    $duree = isset($_POST['duree']) ? (int)$_POST['duree'] : 0;
    $email = $_SESSION['email'];
    
    // Vérification de l'email dans la table utilisateurs
    $queryEmail = $pdo->prepare('SELECT email FROM utilisateurs WHERE email = :email');
    $queryEmail->bindParam(':email', $email);
    $queryEmail->execute();
    $user = $queryEmail->fetch();
    
    if (!$user) {
        echo '<script>alert("L\'email fourni ne correspond à aucun utilisateur enregistré."); window.location.href="bibliotheque.php";</script>';
        exit();
    }
    
    // Vérifier si le livre est disponible (statut == 0)
    $query = $pdo->prepare('SELECT statut FROM livres WHERE id = :id_livres');
    $query->bindParam(':id_livres', $id_livres);
    $query->execute();
    $livre = $query->fetch();

    if ($livre && $livre['statut'] == 0) {
        // Changer le statut du livre à 1 (réservé)
        $updateQuery = $pdo->prepare('UPDATE livres SET statut = 1 WHERE id = :id_livres');
        $updateQuery->bindParam(':id_livres', $id_livres);
        $updateQuery->execute();
        
        // Calculer la date de fin (duree jours après la réservation)
        $date_debut = date('Y-m-d H:i:s');
        $date_fin = date('Y-m-d H:i:s', strtotime("+$duree days"));
        
        // Insérer la réservation dans la table reservation
        $insertQuery = $pdo->prepare('INSERT INTO reservation (email, id_livres, date_debut, date_fin) VALUES (:email, :id_livres, :date_debut, :date_fin)');
        $insertQuery->bindParam(':email', $email);
        $insertQuery->bindParam(':id_livres', $id_livres);
        $insertQuery->bindParam(':date_debut', $date_debut);
        $insertQuery->bindParam(':date_fin', $date_fin);
        $insertQuery->execute();
        
        // Afficher une pop-up de succès et redirection vers bibliotheque.php
        echo '<script>alert("Le livre a été réservé avec succès jusqu\'à ' . $date_fin . '"); window.location.href="bibliotheque.php";</script>';
    } else {
        // Afficher une pop-up si le livre n'est pas disponible, et redirection vers bibliotheque.php
        echo '<script>alert("Ce livre n\'est plus disponible."); window.location.href="bibliotheque.php";</script>';
    }
} else {
    echo '<script>alert("Méthode non autorisée."); window.location.href="bibliotheque.php";</script>';
}
?>
