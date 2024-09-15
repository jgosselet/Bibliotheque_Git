<?php
session_start();
if (!isset($_SESSION['email'])) {
    // Rediriger vers la page de connexion si la session n'est pas définie
    header("Location: index.php");
    exit();
}
?>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Bibliothèque Serrano</title>
    <link rel="stylesheet" href="styleadmin.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@1,300&display=swap" rel="stylesheet"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playwrite+CU:wght@100..400&display=swap" rel="stylesheet">
</head>
<body>
    <a href="logout.php"><button class="btn_deconnexion">Déconnexion</button></a>
    <h2 id="titre_admin">Accueil admin</h2>
    <div class="container-button">
        <a href="gestion_livres.php"><button>Gérer les livres</button></a>
        <a href="gestion_utilisateurs.php"><button>Gérer les utilisateurs</button></a>
        <a href="gestion_livres.php"><button>Gérer les livres</button></a>
    </div>
</body>
</html>
<style>
    body{
        justify-content: center;
        background-image: url(fonddessin.jpg);
    }
</style>