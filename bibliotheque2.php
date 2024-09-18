

<?php
$host = 'localhost'; // Changez si nécessaire
$dbname = 'bibliotheque';
$username = 'root';  // Changez si nécessaire
$password = '';      // Changez si nécessaire

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
    <link rel="stylesheet" type="text/css" href="style.css" />
    <link rel="shortcut icon" href="Ressources/auchon.png">
    <title>Bibliothèque Serrano - Livres</title>
    <style>
        .livre { margin-bottom: 20px; }
        .livre img { max-width: 150px; height: auto; }
    </style>
</head>
<body>
    <div class="tete">
        <img href="" class='auchon' src="images.png">
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
                </div>
                <?php endif; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucun livre trouvé.</p>
    <?php endif; ?>
    </div>
</body>
<br>
<footer>
    Bibliothèque 2024 © Tous droits réservés ©
</footer>
</html>
