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
    <a href="accueil_admin.php"><button class="btn_retour">Retour</button></a>
    <h2 class="titre_admin">Gestion des utilisateurs </h2>
    <button id="openModal" class="open-modal-btn">+</button>
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Ajouter un Nouvel Utilisateur</h2>
            <form id="addUserForm" method="POST">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
                <label for="password">Mot de passe:</label>
                <input type="password" id="password" name="password" required>
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

    if (isset($_SESSION['message'])) {
        echo $_SESSION['message'];
        unset($_SESSION['message']);
    }

    // Ajouter un utilisateur
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
            $email = $_POST['email'];
            $password = $_POST['password'];

            if (!validate_password($password)) {
                echo "<p style='color: red;'>Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.</p>";
            }else{
            // Hachage du mot de passe pour le sécuriser
            //$hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $salt = '$2y$10$abcdefghijklmnopqrstuv.'; // Exemple de sel pour bcrypt

            // Hacher le mot de passe avec le sel fixe
            $hashed_password = crypt($password, $salt);

            // Préparer et exécuter la requête SQL pour insérer le nouvel utilisateur
            $stmt = $conn->prepare("INSERT INTO utilisateurs (email, mot_de_passe) VALUES (?, ?)");
            $stmt->bind_param("ss", $email, $hashed_password);

            if ($stmt->execute()) {
                $_SESSION['message'] = "<p style='color: green;'>Utilisateur ajouté avec succès !</p>";
                header("Location: gestion_utilisateurs.php");
                exit();
        } else {
            $_SESSION['message'] = "<p style='color: red;'>Erreur lors de l'ajout de l'utilisateur: " . $stmt->error . "</p>";
        }
    }
}

    // Mettre à jour l'utilisateur
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
        $original_email = $_POST['original_email'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        $sql_update = "UPDATE utilisateurs SET email=?, mot_de_passe=? WHERE email=?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("sss", $email, $password, $original_email);

        if ($stmt->execute()) {
            $_SESSION['message'] = "<p style='color: green;'>Utilisateur mis à jour avec succès !</p>";
            header("Location: gestion_utilisateurs.php");
            exit();
        } else {
            $_SESSION['message'] = "<p style='color: red;'>Erreur lors de la mise à jour: " . $conn->error . "</p>";
        }
    }

    // Supprimer un utilisateur
    if (isset($_POST['delete']) && isset($_POST['delete_email'])) {
        $delete_email = $_POST['delete_email'];

        $sql_delete = "DELETE FROM utilisateurs WHERE email=?";
        $stmt = $conn->prepare($sql_delete);
        $stmt->bind_param("s", $delete_email);

        if ($stmt->execute()) {
            $_SESSION['message'] = "<p style='color: green;'>Utilisateur supprimé avec succès !</p>";
            header("Location: gestion_utilisateurs.php");
            exit();
        } else {
            $_SESSION['message'] = "<p style='color: red;'>Erreur lors de la suppression: " . $conn->error . "</p>";
        }
    }

    // Récupérer les utilisateurs de la table "utilisateurs"
    $sql = "SELECT email, mot_de_passe FROM utilisateurs";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo '<table border="1" cellpadding="10">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Email</th>';
        echo '<th>Mot de passe</th>';
        echo '<th>Modifier</th>';
        echo '<th>Supprimer</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<form method="POST" action="" style="display: contents;">';  
            echo '<td><input type="email" name="email" value="' . htmlspecialchars($row["email"]) . '" required></td>';
            echo '<td><input type="text" name="password" value="' . htmlspecialchars($row["mot_de_passe"]) . '" required></td>';
            echo '<input type="hidden" name="original_email" value="' . htmlspecialchars($row["email"]) . '">';
            echo '<td><input type="submit" name="update_user" value="Modifier"></td>';
            echo '</form>';

            echo '<form method="POST" action="" onsubmit="return confirm(\'Êtes-vous sûr de vouloir supprimer cet utilisateur ?\');" style="display:inline-block;">';
            echo '<input type="hidden" name="delete_email" value="' . htmlspecialchars($row["email"]) . '">';
            echo '<td><button type="submit" name="delete"><img src="Image/poubelle.png" alt="Supprimer"></button></td>';
            echo '</form>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    } else {
        echo "Aucun utilisateur trouvé.";
    }

    $conn->close();

    function validate_password($password) {
        // Expression régulière pour valider le mot de passe
        $pattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/';
        return preg_match($pattern, $password);
    }
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

</script>