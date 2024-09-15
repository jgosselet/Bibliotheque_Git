<html>
<head>
    <meta charset="UTF-8" />
    <title>Bibliothèque Serrano</title>
    <link rel="stylesheet" href="style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@1,300&display=swap" rel="stylesheet"/>

</head>
<body>
    <div class="container">
        <h2>Inscription</h2>
        <form action="inscription.php" method="post">
            <input type="email" name="email" placeholder="Adresse e-mail" required>
            <div class="password-container">
                <input type="password" name="password" id="password" placeholder="Mot de passe" required>
                <span class="toggle-password" onclick="togglePassword()">
                <i id="eye-icon" class="fa fa-eye"></i>
                </span>
            </div>
            <button type="submit">Créer le compte</button>
        </form>
        <div class="create-account">
            <a href="index.php">Se connecter</a>
        </div>
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST['email']) && !empty($_POST['email']) && isset($_POST['password']) && !empty($_POST['password'])) {
                $email = $_POST['email'];
                $password = $_POST['password'];

            if (!validate_password($password)) {
                echo "<p>Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.</p>";
            } else {
            // Paramètres de connexion à la base de données
            $servername = "localhost";
            $username = "root";
            $password_db = "sio2024";
            $dbname = "bibliotheque"; // Remplacez par le nom de votre base de données

            // Connexion à la base de données
            $conn = new mysqli($servername, $username, $password_db, $dbname);

            // Vérifier la connexion
            if ($conn->connect_error) {
                die("Échec de la connexion: " . $conn->connect_error);
            }

                // Hachage du mot de passe pour le sécuriser
                //$hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $salt = '$2y$10$abcdefghijklmnopqrstuv.'; // Exemple de sel pour bcrypt

                // Hacher le mot de passe avec le sel fixe
                $hashed_password = crypt($password, $salt);

                // Préparation de la requête SQL pour insérer les données dans la table utilisateur
                $sql = "INSERT INTO utilisateurs (email, mot_de_passe) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                // Vérifiez si la préparation de la requête a réussi
                if ($stmt === false) {
                    die("Erreur de préparation de la requête: " . $conn->error);
                }

                $stmt->bind_param("ss", $email, $hashed_password);

                // Exécuter la requête et vérifier si elle a réussi
                if ($stmt->execute()) {
                    echo "<p>Compte créé avec succès !</p>";
                } else {
                    echo "<p>Erreur: " . $stmt->error . "</p>";
                }

                // Fermeture de la requête préparée et de la connexion
                $stmt->close();
                $conn->close();
            }
        }else {
            $error_message = "Les champs email ou mot de passe sont manquants.";
        }
    }

        function validate_password($password) {
            // Expression régulière pour valider le mot de passe
            $pattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/';
            return preg_match($pattern, $password);
        }
        ?>
    </div>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />  
</body>
</html>
<script>
    function togglePassword() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
}
</script>