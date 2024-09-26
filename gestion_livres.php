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
    <link rel="shortcut icon" href="Image/images.png">
    <link rel="stylesheet" href="styleadmin.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@1,300&display=swap" rel="stylesheet"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playwrite+CU:wght@100..400&display=swap" rel="stylesheet">
</head>
<body>
    <a href="accueil_admin.php"><button class="btn_retour">Retour</button></a>
    <h2 class="titre_admin">Gestion des livres </h2>
    <button id="openModal" class="open-modal-btn">+</button>
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Ajouter un nouveau Llvre</h2>
            <form id="addBookForm" method="POST">
                <label for="titre">Titre:</label>
                <input type="text" id="titre" name="titre" required>
                <label for="auteur">Auteur:</label>
                <input type="text" id="auteur" name="auteur" required>
                <label for="date_publication">Date de Publication:</label>
                <input type="date" id="date_publication" class="date-input" name="date_publication" required>
                <label for="genre">Genre:</label>
                <input type="text" id="genre" name="genre" required>
                <label for="image">Image URL:</label>
                <input type="text" id="image" name="image">
                <label for="statut">Statut:</label>
                <input type="number" id="statut" name="statut" required>
                <button type="submit" name="add_user">Ajouter</button>
            </form>
        </div>
    </div>

    <?php
$servername = "localhost";
$username = "root";
$password_db = "sio2024";
$dbname = "bibliotheque";

$conn = new mysqli($servername, $username, $password_db, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion: " . $conn->connect_error);
}

// Afficher le message de la session
if (isset($_SESSION['message'])) {
    echo $_SESSION['message'];
    // Supprimer le message de la session après l'avoir affiché
    unset($_SESSION['message']);
}

// Traitement de l'ajout de livre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $titre = $_POST['titre'];
    $auteur = $_POST['auteur'];
    $date_publication = $_POST['date_publication'];
    $genre = $_POST['genre'];
    $image = $_POST['image'];
    $statut = $_POST['statut'];

    // Préparer et exécuter la requête SQL pour insérer le nouveau livre
    $stmt = $conn->prepare("INSERT INTO livres (titre, auteur, date_publication, genre, image, statut) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $titre, $auteur, $date_publication, $genre, $image, $statut);

    if ($stmt->execute()) {
        $_SESSION['message'] = "<p style='color: green;'>Livre ajouté avec succès !</p>";
        // Rediriger vers la même page ou vers une autre page après l'ajout
        header("Location: gestion_livres.php");
        exit();
    } else {
        $_SESSION['message'] = "<p style='color: red;'>Erreur lors de l'ajout du livre: " . $stmt->error . "</p>";
    }
}

// Traitement de la suppression de livre
if (isset($_POST['delete']) && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // Requête SQL pour supprimer le livre
    $sql_delete = "DELETE FROM livres WHERE id='$delete_id'";

    if ($conn->query($sql_delete) === TRUE) {
        $_SESSION['message'] = "<p style='color: green;'>Livre supprimé avec succès !</p>";
    } else {
        $_SESSION['message'] = "<p style='color: red;'>Erreur lors de la suppression: " . $conn->error . "</p>";
    }
    $conn->close();

    // Rediriger vers la même page
    header("Location: gestion_livres.php");
    exit();
}

// Traitement de la mise à jour de livre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    if (isset($_POST['id'], $_POST['titre'], $_POST['auteur'], $_POST['date_publication'], $_POST['genre'], $_POST['image'], $_POST['statut'])) {
        $id = $_POST['id'];
        $titre = $_POST['titre'];
        $auteur = $_POST['auteur'];
        $date_publication = $_POST['date_publication'];
        $genre = $_POST['genre'];
        $image = $_POST['image'];
        $statut = $_POST['statut'];

        // Préparer et exécuter la requête SQL pour mettre à jour le livre
        $stmt = $conn->prepare("UPDATE livres SET titre=?, auteur=?, date_publication=?, genre=?, image=?, statut=? WHERE id=?");
        $stmt->bind_param("sssssii", $titre, $auteur, $date_publication, $genre, $image, $statut, $id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "<p style='color: green;'>Livre mis à jour avec succès !</p>";
            header("Location: gestion_livres.php");
            exit();
        } else {
            $_SESSION['message'] = "<p style='color: red;'>Erreur lors de la mise à jour: " . $stmt->error . "</p>";
        }
    }
}

// Récupérer les livres de la table "livres"
$sql = "SELECT id, titre, auteur, date_publication, genre, image, statut FROM livres";
$result = $conn->query($sql);

// Vérifier s'il y a des livres
if ($result->num_rows > 0) {
    echo '<table border="1" cellpadding="10">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Id</th>';
    echo '<th>Titre</th>';
    echo '<th>Auteur</th>';
    echo '<th>Date de publication</th>';
    echo '<th>Genre</th>';
    echo '<th>URL de l\'image</th>';
    echo '<th>Statut</th>';
    echo '<th>Image</th>';
    echo '<th>Modifier</th>';
    echo '<th>Supprimer</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Afficher chaque livre dans une ligne de tableau
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<form method="POST" action="" style="display: contents;">';  // Formulaire pour modifier le livre
        
        echo '<td><input type="text" name="id" value="' . htmlspecialchars($row["id"]) . '" readonly></td>';
        echo '<td><input type="text" name="titre" value="' . htmlspecialchars($row["titre"]) . '"></td>';
        echo '<td><input type="text" name="auteur" value="' . htmlspecialchars($row["auteur"]) . '"></td>';
        echo '<td><input type="date" name="date_publication" value="' . htmlspecialchars($row["date_publication"]) . '"></td>';
        echo '<td><input type="text" name="genre" value="' . htmlspecialchars($row["genre"]) . '"></td>';
        echo '<td><input type="text" name="image" value="' . htmlspecialchars($row["image"]) . '"></td>';
        echo '<td><input type="number" name="statut" value="' . htmlspecialchars($row["statut"]) . '"></td>';
        echo '<td><img class="img_livre" src="' . htmlspecialchars($row["image"]) . '" alt="' . htmlspecialchars($row["titre"]) . '"></td>';
        echo '<input type="hidden" name="action" value="update">';
        echo '<td><input type="submit" value="Modifier"></td>';
        echo '</form>';
        // Formulaire pour supprimer le livre
        echo '<form method="POST" action="" onsubmit="return confirm(\'Êtes-vous sûr de vouloir supprimer ce livre ?\');" style="display:inline-block;">';
        echo '<input type="hidden" name="delete_id" value="' . htmlspecialchars($row["id"]) . '">';
        echo '<input type="hidden" name="action" value="delete">';
        echo '<td><button type="submit" name="delete"><img src="Image/poubelle.png" alt="Supprimer"></button></td>'; // Icône de poubelle
        echo '</form>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
} else {
    echo "Aucun livre trouvé.";
}

$conn->close();
?>
</body>
</html>
<style>
    body{
        background-color: white;
        padding-bottom: 50px;
        background-image: url("Image/fond.jpg");

    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', (event) => {
    var modal = document.getElementById("myModal");
    var btn = document.getElementById("openModal");
    var span = document.getElementsByClassName("close")[0];

    // Ouvrir le modal lorsque le bouton est cliqué
    btn.onclick = function() {
        modal.style.display = "block";
    }

    // Fermer le modal lorsque l'utilisateur clique sur <span> 
    span.onclick = function() {
        modal.style.display = "none";
    }

    // Fermer le modal lorsque l'utilisateur clique en dehors du modal
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
});

document.querySelectorAll('.date-input').forEach(function(input) {
    var today = new Date().toISOString().split('T')[0];
    input.setAttribute('max', today);
})
</script>
