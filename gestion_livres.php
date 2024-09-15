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
</head>
<body>
    <a href="accueil_admin.php"><button class="btn_retour">Retour</button></a>
    <h2 class="titre_admin">Gestion des livres </h2>
    <button id="openModal" class="open-modal-btn">+</button>
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Ajouter un Nouveau Livre</h2>
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
                <button type="submit">Ajouter</button>
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

    if (isset($_SESSION['message'])) {
        echo $_SESSION['message'];
        // Supprimer le message de la session après l'avoir affiché
        unset($_SESSION['message']);
    }


    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['titre'], $_POST['auteur'], $_POST['date_publication'], $_POST['genre'], $_POST['image'])) {
        $titre = $_POST['titre'];
        $auteur = $_POST['auteur'];
        $date_publication = $_POST['date_publication'];
        $genre = $_POST['genre'];
        $image = $_POST['image'];
    
        // Préparer et exécuter la requête SQL pour insérer le nouveau livre
        $stmt = $conn->prepare("INSERT INTO livres (titre, auteur, date_publication, genre, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $titre, $auteur, $date_publication, $genre, $image);
    
        if ($stmt->execute()) {
            $_SESSION['message'] = "<p style='color: green;'>Livre ajouté avec succès !</p>";
            // Rediriger vers la même page ou vers une autre page après l'ajout
            header("Location: gestion_livres.php"); // Assurez-vous que cette URL correspond à votre page
            exit();
        } else {
            $_SESSION['message'] = "<p style='color: red;'>Erreur lors de l'ajout du livre: " . $stmt->error . "</p>";
        }
    }

    // Si une suppression est demandée
    if (isset($_POST['delete']) && isset($_POST['delete_id'])) {
        $delete_id = $_POST['delete_id'];

        // Requête SQL pour supprimer le livre
        $sql_delete = "DELETE FROM livres WHERE id='$delete_id'";

        if ($conn->query($sql_delete) === TRUE) {
            $_SESSION['message'] ="<p style='color: green;'>Livre supprimé avec succès !</p>";
        } else {
            $_SESSION['message'] ="<p style='color: red;'>Erreur lors de la suppression: " . $conn->error . "</p>";
        }
        $conn->close();

        // Rediriger vers la même page
        header("Location: gestion_livres.php"); // Assurez-vous que cette URL correspond à votre page
        exit();
    }

    // Si le formulaire est soumis, mettre à jour les informations du livre
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['id'], $_POST['titre'], $_POST['auteur'], $_POST['date_publication'], $_POST['genre'], $_POST['image'])) {
        $id = $_POST['id'];
        $titre = $_POST['titre'];
        $auteur = $_POST['auteur'];
        $date_publication = $_POST['date_publication'];
        $genre = $_POST['genre'];
        $image = $_POST['image'];

        $sql_update = "UPDATE livres SET titre='$titre', auteur='$auteur', date_publication='$date_publication', genre='$genre', image='$image' WHERE id='$id'";

        if ($conn->query($sql_update) === TRUE) {
            $_SESSION['message'] = "<p style='color: green; font-size:1vw'>Livre mis à jour avec succès !</p>";
            header("Location: gestion_livres.php"); // Assurez-vous que cette URL correspond à votre page
            exit();
        } else {
            $_SESSION['message'] = "<p style='color: red; font-size:1vw'>Erreur lors de la mise à jour: " . $conn->error . "</p>";
        }
    } 
    }

    // Récupérer les livres de la table "livres"
    $sql = "SELECT id, titre, auteur, date_publication, genre, image FROM livres";
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
        echo '<td><input type="date" class="date-input" name="date_publication" value="' . htmlspecialchars($row["date_publication"]) . '"></td>';
        echo '<td><input type="text" name="genre" value="' . htmlspecialchars($row["genre"]) . '"></td>';
        echo '<td><input type="text" name="image" value="' . htmlspecialchars($row["image"]) . '"></td>';  // URL complète dans le champ de texte
        echo '<td><img class="img_livre" src="' . htmlspecialchars($row["image"]) . '" alt="' . htmlspecialchars($row["titre"]) . '"></td>';
        echo '<td><input type="submit" value="Modifier"></td>';
        echo '</form>';
        // Formulaire pour supprimer le livre
        echo '<form method="POST" action="" onsubmit="return confirm(\'Êtes-vous sûr de vouloir supprimer ce livre ?\');" style="display:inline-block;">';
        echo '<input type="hidden" name="delete_id" value="' . htmlspecialchars($row["id"]) . '">';
        echo '<td><button type="submit" name="delete"><img src="poubelle.png" alt="Supprimer"></button></td>'; // Icône de poubelle
        echo '</form>';
        echo '</td>';
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

    // Fermer le modal lorsque l'utilisateur clique sur <span> (x)
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