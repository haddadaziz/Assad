<?php
require_once 'config.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = htmlspecialchars($_POST['nom']);
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    $check = $pdo->prepare("SELECT id_utilisateur FROM utilisateurs WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>Cet email est déjà utilisé.</div>";
    } else {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $statut = ($role === 'guide') ? 0 : 1;

        $sql = "INSERT INTO utilisateurs (nom, email, motpasse_hash, role, statut) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$nom, $email, $password_hash, $role, $statut])) {
            if ($role === 'guide') {
                header("Location: login.php?status=pending_registration");
            } else {
                header("Location: login.php?status=success_registration");
            }
            exit();
        } else {
            $message = "<div class='bg-red-100 text-red-700 px-4 py-3 rounded'>Erreur.</div>";
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
    <script src="script.js" defer></script>
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
                <div class="mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Nom</label>
                        <input type="text" name="nom" required
                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-maroc-red">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                    <input type="email" name="email" required
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-maroc-red">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Mot de passe</label>
                    <input type="password" name="password" required
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-maroc-red">
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Je veux être :</label>
                    <select name="role"
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-maroc-red">
                        <option value="visiteur">Visiteur (Pour réserver)</option>
                        <option value="guide">Guide (Pour organiser)</option>
                    </select>
                </div>

                <button type="submit"
                    class="w-full bg-maroc-red text-white font-bold py-3 rounded hover:bg-red-700 transition duration-300">
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
    <!-- Notifications d'erreur ou de succes -->
    <div class="notification success hidden fixed top-6 left-1/2 transform -translate-x-1/2 z-[999] bg-green-500 text-white px-10 py-4 rounded-full shadow-2xl border-4 border-white/30 text-lg font-bold text-center min-w-[350px] shadow-green-500/50"
        id="success_notification"></div>
    <div class="notification error hidden fixed top-6 left-1/2 transform -translate-x-1/2 z-[999] bg-red-500 text-white px-10 py-4 rounded-full shadow-2xl border-4 border-white/30 text-lg font-bold text-center min-w-[350px] shadow-green-500/50"
        id="error_notification"></div>
</body>

</html>