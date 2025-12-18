<?php
session_start();
require_once 'config.php';

$message = "";
if (isset($_GET['success'])) {
    $message = "<div class='bg-green-100 text-green-700 px-4 py-3 rounded mb-4'>Compte créé ! Connectez-vous.</div>";
}

if (isset($_GET['pending'])) {
    $message = "<div class='bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded mb-4'>⏳ Compte créé avec succès !<br><strong>Note :</strong> En tant que Guide, votre compte doit être validé par un administrateur avant de pouvoir vous connecter.</div>";
}

if (isset($_GET['redirect'])) {
    $message = "<div class='bg-yellow-100 text-yellow-800 px-4 py-3 rounded mb-4'>Veuillez vous connecter pour accéder à cette page.</div>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['motpasse_hash'])) {
        if ($user['statut'] == 0) {
            $message = "<div class='bg-yellow-100 text-yellow-800 px-4 py-3 rounded mb-4'>Votre compte est en attente de validation par un administrateur.</div>";
        } else {
            // Remplir la session si login réussi
            $_SESSION['user_id'] = $user['id_utilisateur'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } elseif ($user['role'] === 'guide') {
                header("Location: guide_dashboard.php");
            } else {
                header(header: "Location: visiteur.php");
            }
            exit();
        }
    } else {
        $message = "<div class='bg-red-100 text-red-700 px-4 py-3 rounded mb-4'>Email ou mot de passe incorrect.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Connexion - Zoo Assad</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { theme: { extend: { colors: { 'maroc-red': '#C1272D', 'maroc-green': '#006233' } } } }
    </script>
</head>

<body class="bg-gray-50 font-sans min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-xl shadow-2xl overflow-hidden">
        <div class="bg-maroc-red py-6 text-center">
            <h1 class="text-2xl font-bold text-white font-['Montserrat']">Espace Membre</h1>
            <p class="text-red-100 text-sm">Connectez-vous au Zoo Assad</p>
        </div>

        <div class="p-8">
            <?= $message ?>

            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                    <input type="email" name="email" required
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-maroc-green">
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Mot de passe</label>
                    <input type="password" name="password" required
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-maroc-green">
                    <a href="#" class="text-xs text-gray-500 hover:text-maroc-red float-right mt-1">Mot de passe oublié
                        ?</a>
                </div>

                <button type="submit"
                    class="w-full bg-maroc-green text-white font-bold py-3 rounded hover:bg-green-800 transition duration-300">
                    Se connecter
                </button>
            </form>

            <p class="mt-4 text-center text-sm text-gray-600">
                Pas encore de compte ? <a href="register.php"
                    class="text-maroc-red font-bold hover:underline">S'inscrire</a>
            </p>
            <p class="mt-2 text-center text-sm">
                <a href="index.php" class="text-gray-400 hover:text-gray-600">Retour à l'accueil</a>
            </p>
        </div>
    </div>
</body>

</html>