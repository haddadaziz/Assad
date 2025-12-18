<?php
require_once 'config.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // nettoyer les données
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Vérifier si l'email existe déjà
    $check = $pdo->prepare("SELECT id_utilisateur FROM utilisateurs WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>Cet email est déjà utilisé.</div>";
    } else {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        // Validation : Si c'est un guide, il n'est pas validé par défaut (est_valide = 0)
        // Si c'est un visiteur, il est validé direct (est_valide = 1)
        $est_valide = ($role === 'guide') ? 0 : 1;

        $sql = "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, est_valide) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$nom, $prenom, $email, $password_hash, $role, $est_valide])) {
            // Redirection vers login avec succès
            header("Location: login.php?success=1");
            exit();
        } else {
            $message = "<div class='bg-red-100 text-red-700 px-4 py-3 rounded'>Erreur technique.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - Zoo Assad</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { theme: { extend: { colors: { 'maroc-red': '#C1272D', 'maroc-green': '#006233' } } } }
    </script>
</head>
<body class="bg-gray-50 font-sans min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-xl shadow-2xl overflow-hidden">
        <div class="bg-maroc-green py-6 text-center">
            <h1 class="text-2xl font-bold text-white font-['Montserrat']">Rejoignez la Meute</h1>
            <p class="text-green-100 text-sm">Créez votre compte Zoo Assad</p>
        </div>

        <div class="p-8">
            <?= $message ?>
            
            <form method="POST" action="">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Prénom</label>
                        <input type="text" name="prenom" required class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-maroc-red">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Nom</label>
                        <input type="text" name="nom" required class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-maroc-red">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                    <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-maroc-red">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Mot de passe</label>
                    <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-maroc-red">
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Je veux être :</label>
                    <select name="role" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-maroc-red">
                        <option value="visiteur">Visiteur (Pour réserver)</option>
                        <option value="guide">Guide (Pour organiser)</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-maroc-red text-white font-bold py-3 rounded hover:bg-red-700 transition duration-300">
                    S'inscrire
                </button>
            </form>
            
            <p class="mt-4 text-center text-sm text-gray-600">
                Déjà un compte ? <a href="login.php" class="text-maroc-green font-bold hover:underline">Se connecter</a>
            </p>
            <p class="mt-2 text-center text-sm">
                <a href="index.php" class="text-gray-400 hover:text-gray-600">Retour à l'accueil</a>
            </p>
        </div>
    </div>
</body>
</html>