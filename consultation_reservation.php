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
    <h2 class="titre_admin">Consultation des réservations</h2>
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

    // Récupérer les livres de la table "livres"
    $sql = "SELECT * FROM reservation";
    $result = $conn->query($sql);

    // Vérifier s'il y a des livres
    if ($result->num_rows > 0) {
        echo '<table border="1" cellpadding="10">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Id de réservation</th>';
        echo '<th>Email</th>';
        echo '<th>Id du livre</th>';
        echo '<th>Date de fin</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

    // Afficher chaque livre dans une ligne de tableau
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row["id_reservation"]) . '</td>';
        echo '<td>' . htmlspecialchars($row["email"]) . '</td>';
        echo '<td>' . htmlspecialchars($row["id_livres"]) . '</td>';
        echo '<td>' . htmlspecialchars($row["date_fin"]) . '</td>';
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