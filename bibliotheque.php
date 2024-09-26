<?php
session_start();
if (!isset($_SESSION['email'])) {
    // Rediriger vers la page de connexion si la session n'est pas définie
    header("Location: index.php");
    exit();
}

$host = 'localhost'; 
$dbname = 'bibliotheque';
$username = 'root';  
$password = 'sio2024';   

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connexion échouée : ' . $e->getMessage();
}
// Récupération des valeurs uniques pour les filtres
function getDistinctValues($pdo, $column) {
    $stmt = $pdo->prepare("SELECT DISTINCT $column FROM livres ORDER BY $column");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Récupération des filtres sélectionnés
$auteur = isset($_GET['auteur']) ? $_GET['auteur'] : '';
$genre = isset($_GET['genre']) ? $_GET['genre'] : '';
$date_publication = isset($_GET['date_publication']) ? $_GET['date_publication'] : '';

// Construction de la requête SQL
$query = "SELECT * FROM livres WHERE 1=1";

if ($auteur) {
    $query .= " AND auteur = :auteur";
}

if ($genre) {
    $query .= " AND genre = :genre";
}

if ($date_publication) {
    $query .= " AND date_publication = :date_publication";
}

$query .= " ORDER BY titre"; // Tri par titre par défaut

$stmt = $pdo->prepare($query);

if ($auteur) {
    $stmt->bindParam(':auteur', $auteur);
}

if ($genre) {
    $stmt->bindParam(':genre', $genre);
}

if ($date_publication) {
    $stmt->bindParam(':date_publication', $date_publication);
}

$stmt->execute();
$livres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des options pour les filtres
$auteurs = getDistinctValues($pdo, 'auteur');
$genres = getDistinctValues($pdo, 'genre');
$date_publications = getDistinctValues($pdo, 'date_publication');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Sofia">
    <link rel="stylesheet" type="text/css" href="style_bibliotheque.css" />
    <link rel="shortcut icon" href="Image/images.png">
    <title>Bibliothèque Serrano - Livres</title>
</head>
<body>
    <div class="tete">
        <img href="" class='auchon' src="Image/images.png">
    <form method="GET" action="">
        <label for="auteur">Auteur:</label>
        <select id="auteur" name="auteur">
            <option value="">Tous</option>
            <?php foreach ($auteurs as $option): ?>
                <option value="<?php echo htmlspecialchars($option); ?>" <?php echo $option == $auteur ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($option); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="genre">Genre:</label>
        <select id="genre" name="genre">
            <option value="">Tous</option>
            <?php foreach ($genres as $option): ?>
                <option value="<?php echo htmlspecialchars($option); ?>" <?php echo $option == $genre ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($option); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="date_publication">Date de publication:</label>
        <select id="date_publication" name="date_publication">
            <option value="">Toutes</option>
            <?php foreach ($date_publications as $option): ?>
                <option value="<?php echo htmlspecialchars($option); ?>" <?php echo $option == $date_publication ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($option); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Filtrer</button>
    </form>
    <a href="logout.php"><button class="btn_deconnexion">Déconnexion</button></a>
    <a href="consultation_reservation_utilisateur.php"><button class="btn_consultation">Consulter vos réservations</button></a>
    </div>
    <div class="product-grid">
    <?php if ($livres): ?>
        <?php foreach ($livres as $livre): ?>
            <div class="product">
                <?php if ($livre['image']): ?>
                    <img class ="product-image" src="<?php echo htmlspecialchars($livre['image']); ?>" alt="Image du livre">
                    <div class="product-info">
                        <h2><?php echo htmlspecialchars($livre['titre']); ?></h2>
                        <p>Auteur: <?php echo htmlspecialchars($livre['auteur']); ?></p>
                        <p>Date de publication: <?php echo htmlspecialchars($livre['date_publication']); ?></p>
                        <p>Genre: <?php echo htmlspecialchars($livre['genre']); ?></p>
                    </div>
                    
                    <?php if ($livre['statut'] == 0): ?>
                        <form action="reserver.php" method="POST">
                            <input type="hidden" name="id_livre" value="<?php echo $livre['id']; ?>">
                            <label for="duree">Durée de la réservation (en jours, max 7) :</label>
                            <input type="number" id="duree" name="duree" min="1" max="7" required>
                            <button type="submit" class="reserve-btn">Réserver</button>
                        </form>
                    <?php else: ?>
                        <p>Ce livre est déjà réservé</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucun livre trouvé.</p>
    <?php endif; ?>
</div>

<?php
$host = 'localhost';
$dbname = 'bibliotheque';
$username = 'root';
$password = 'sio2024';

// Définir le fuseau horaire pour s'assurer que les dates sont correctes
date_default_timezone_set('Europe/Paris');

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtenir la date et l'heure actuelles
    $currentDate = date('Y-m-d H:i:s');

    // Sélectionner les livres dont la réservation est expirée
    $query = $pdo->prepare('
        SELECT id_livres FROM reservation WHERE date_fin <= :currentDate
    ');
    $query->bindParam(':currentDate', $currentDate);
    $query->execute();
    $livres_expired = $query->fetchAll(PDO::FETCH_ASSOC);

    // Si des réservations sont expirées, vérifier s'il y a encore d'autres réservations pour ces livres
    if ($livres_expired) {
        foreach ($livres_expired as $livre) {
            $id_livres = $livre['id_livres'];

            // Vérifier s'il existe encore des réservations actives pour ce livre
            $checkQuery = $pdo->prepare('
                SELECT COUNT(*) as count FROM reservation WHERE id_livres = :id_livres AND date_fin > :currentDate
            ');
            $checkQuery->bindParam(':id_livres', $id_livres);
            $checkQuery->bindParam(':currentDate', $currentDate);
            $checkQuery->execute();
            $reservation_active = $checkQuery->fetch(PDO::FETCH_ASSOC);

            // Si aucune autre réservation n'existe pour ce livre, changer le statut à 0 (disponible)
            if ($reservation_active['count'] == 0) {
                $updateQuery = $pdo->prepare('UPDATE livres SET statut = 0 WHERE id = :id_livres');
                $updateQuery->bindParam(':id_livres', $id_livres);
                $updateQuery->execute();
            }
        }
        echo 'Les statuts des livres sans réservation active ont été mis à jour avec succès.';
    } else {
        echo 'Aucune réservation expirée trouvée à ce moment.';
    }

} catch (PDOException $e) {
    // Gestion des erreurs de connexion ou de requête
    echo 'Erreur de connexion ou de requête : ' . $e->getMessage();
}
?>


</body>
<br>
<footer>
    Bibliothèque 2024 © Tous droits réservés ©
</footer>
</html>
