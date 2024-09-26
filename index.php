<!--Administrateur123!
Nonoutilisateur1!-->
<html>
<head>
    <meta charset="UTF-8" />
    <title>Bibliothèque Serrano</title>
    <link rel="shortcut icon" href="Image/images.png">
    <link rel="stylesheet" href="style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@1,300&display=swap" rel="stylesheet"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playwrite+CU:wght@100..400&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Connexion</h2>
        <form action="" method="post">
            <input type="email" name="email" placeholder="Adresse e-mail" required>
            <div class="password-container">
                <input type="password" name="password" id="password" placeholder="Mot de passe" required>
                <span class="toggle-password" onclick="togglePassword()">
                <i id="eye-icon" class="fa fa-eye"></i>
                </span>
            </div>
            <button type="submit">Se connecter</button>
        </form>
        <div class="create-account">
            <a href="inscription.php">Créez un compte</a>
        </div>
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Vérifiez que les champs email et password existent
            if (isset($_POST['email']) && !empty($_POST['email']) && isset($_POST['password']) && !empty($_POST['password'])) {
                // Paramètres de connexion à la base de données
                $servername = "localhost";
                $username = "root";
                $password_db = "sio2024";
                $dbname = "bibliotheque"; 

                // Connexion à la base de données
                $conn = new mysqli($servername, $username, $password_db, $dbname);

                // Vérifier la connexion
                if ($conn->connect_error) {
                    die("Échec de la connexion: " . $conn->connect_error);
                }

                // Récupérer les données du formulaire
                $email = $_POST['email'];
                $password = $_POST['password'];

                // Préparation de la requête SQL pour vérifier les identifiants
                $sql = "SELECT mot_de_passe FROM utilisateurs WHERE email = ?";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $stmt->store_result();
                    $stmt->bind_result($hashed_password);
                    
                    if ($stmt->num_rows > 0) {
                        // Récupérer le mot de passe haché depuis la base de données
                        $stmt->fetch();
                        
                        $salt = '$2y$10$abcdefghijklmnopqrstuv.';
                        $hashed_password = trim($hashed_password);
                        $password = trim($password);
                        
                        // Vérifier que le mot de passe est correct
                        if (crypt($password, $salt) === $hashed_password) {
                            session_start();
                            $_SESSION['email'] = $email;
                            
                            // Vérifier si l'utilisateur est admin
                            if ($email === "admin@gmail.com") {
                                // Rediriger l'admin vers la page d'accueil admin
                                header("Location: accueil_admin.php");
                            } else {
                                // Rediriger l'utilisateur non admin vers la bibliothèque
                                header("Location: bibliotheque.php");
                            }
                            exit(); 
                        } else {
                            // Mot de passe incorrect
                            $error_message = "Mot de passe incorrect.";
                        }
                    } else {
                        // Adresse email non trouvée
                        $error_message = "Aucun compte trouvé avec cette adresse e-mail.";
                    }
                
                    // Fermeture de la déclaration et de la connexion
                    $stmt->close();
                } else {
                    $error_message = "Erreur lors de la préparation de la requête SQL.";
                }
                
                $conn->close();
            }
        }
                
    

        // Affichage des messages d'erreur
        if (isset($error_message)) {
            echo "<p>$error_message</p>";
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


// Fonction pour désactiver le retour en arrière
function preventBack() {
    window.history.forward(); 
}

// Appel de la fonction lorsqu'on essaie d'aller en arrière
setTimeout("preventBack()", 0);

// Intercepter les actions de retour en arrière du navigateur
window.onunload = function() { null };
</script>