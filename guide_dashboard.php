<?php
session_start();
require_once 'config.php';

// 1. SÉCURITÉ : Vérifier si c'est bien un GUIDE
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guide') {
    header("Location: login.php?redirect=guide");
    exit();
}

$id_guide = $_SESSION['user_id'];
$message = "";

// --- LOGIQUE PHP : AJOUTER UNE VISITE ---
if (isset($_POST['creer_visite'])) {
    $titre = htmlspecialchars($_POST['titre']);
    $date = $_POST['dateheure'];
    $langue = $_POST['langue'];
    $duree = $_POST['duree'];
    $prix = $_POST['prix'];
    $capacite = $_POST['capacite'];
    
    // On insère la visite liée à CE guide ($id_guide)
    $sql = "INSERT INTO visitesguidees (titre, dateheure, langue, duree, prix, capacite_max, id_guide, est_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$titre, $date, $langue, $duree, $prix, $capacite, $id_guide])) {
        $message = "<div class='bg-green-100 text-green-700 p-3 rounded mb-4'>Visite créée avec succès !</div>";
    } else {
        $message = "<div class='bg-red-100 text-red-700 p-3 rounded mb-4'>Erreur lors de la création.</div>";
    }
}

// Gestion de la section (Navigation)
$section = isset($_GET['section']) ? $_GET['section'] : 'mes_visites';

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Guide - Zoo Assad</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'maroc-red': '#C1272D',
                        'maroc-green': '#006233',
                    },
                    fontFamily: { 'headings': ['Montserrat', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans flex h-screen overflow-hidden">

    <aside class="w-64 bg-maroc-green text-white flex flex-col shadow-2xl">
        <div class="h-20 flex items-center justify-center border-b border-green-800 bg-green-900">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-compass text-yellow-400 text-2xl"></i>
                <span class="font-headings font-bold text-xl tracking-wider">ESPACE GUIDE</span>
            </div>
        </div>

        <div class="p-4 border-b border-green-700 bg-green-800">
            <p class="text-xs text-green-200 uppercase">Bienvenue,</p>
            <p class="font-bold truncate"><?= $_SESSION['nom'] ?></p>
        </div>

        <nav class="flex-1 overflow-y-auto py-6">
            <ul class="space-y-2">
                <li>
                    <a href="?section=mes_visites" class="flex items-center px-6 py-3 hover:bg-green-700 transition <?= $section == 'mes_visites' ? 'bg-green-900 border-l-4 border-yellow-400' : '' ?>">
                        <i class="fa-solid fa-map-location-dot w-6"></i>
                        <span class="font-medium">Mes Visites</span>
                    </a>
                </li>
                <li>
                    <a href="?section=reservations" class="flex items-center px-6 py-3 hover:bg-green-700 transition <?= $section == 'reservations' ? 'bg-green-900 border-l-4 border-yellow-400' : '' ?>">
                        <i class="fa-solid fa-clipboard-list w-6"></i>
                        <span class="font-medium">Réservations</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="p-4 border-t border-green-700">
            <a href="logout.php" class="flex items-center gap-2 text-green-200 hover:text-white transition">
                <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
            </a>
        </div>
    </aside>

    <main class="flex-1 overflow-y-auto p-8">
        
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-headings font-bold text-gray-800">
                <?php 
                    if($section == 'mes_visites') echo "Gestion des Visites";
                    elseif($section == 'reservations') echo "Liste des Réservations";
                ?>
            </h1>
            <a href="index.php" class="text-gray-500 hover:text-maroc-red text-sm flex items-center gap-2">
                <i class="fa-solid fa-home"></i> Retour au site
            </a>
        </header>

        <?= $message ?>

        <?php if ($section == 'mes_visites'): ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="bg-white p-6 rounded-xl shadow-md border-t-4 border-maroc-red h-fit">
                    <h2 class="font-bold text-lg mb-4 text-gray-700"><i class="fa-solid fa-plus-circle mr-2"></i>Créer une visite</h2>
                    <form method="POST" action="">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Titre de la visite</label>
                                <input type="text" name="titre" required placeholder="Ex: Safari Nocturne" class="w-full border p-2 rounded focus:ring-1 focus:ring-maroc-green">
                            </div>
                            
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Date & Heure</label>
                                    <input type="datetime-local" name="dateheure" required class="w-full border p-2 rounded text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Langue</label>
                                    <select name="langue" class="w-full border p-2 rounded">
                                        <option>Français</option>
                                        <option>Arabe</option>
                                        <option>Anglais</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Durée (min)</label>
                                    <input type="number" name="duree" value="90" class="w-full border p-2 rounded">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Prix (DH)</label>
                                    <input type="number" name="prix" value="100" class="w-full border p-2 rounded">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Max Pers.</label>
                                    <input type="number" name="capacite" value="15" class="w-full border p-2 rounded">
                                </div>
                            </div>

                            <button type="submit" name="creer_visite" class="w-full bg-maroc-green hover:bg-green-800 text-white font-bold py-3 rounded transition shadow-md">
                                Publier la visite
                            </button>
                        </div>
                    </form>
                </div>

                <div class="lg:col-span-2 space-y-4">
                    <h2 class="font-bold text-lg text-gray-700">Vos visites programmées</h2>
                    
                    <?php
                    // Récupérer les visites de CE guide
                    $sql_visites = "SELECT * FROM visitesguidees WHERE id_guide = ? ORDER BY dateheure DESC";
                    $stmt_v = $pdo->prepare($sql_visites);
                    $stmt_v->execute([$id_guide]);
                    $mes_visites = $stmt_v->fetchAll();
                    ?>

                    <?php if(empty($mes_visites)): ?>
                        <div class="bg-white p-8 rounded-xl shadow text-center text-gray-500">
                            <i class="fa-regular fa-calendar-xmark text-4xl mb-3"></i>
                            <p>Aucune visite programmée pour l'instant.</p>
                        </div>
                    <?php else: ?>
                        <div class="bg-white rounded-xl shadow overflow-hidden">
                            <table class="w-full text-left">
                                <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                                    <tr>
                                        <th class="p-4">Date</th>
                                        <th class="p-4">Titre</th>
                                        <th class="p-4">Statut</th>
                                        <th class="p-4 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm">
                                    <?php foreach($mes_visites as $v): ?>
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="p-4 text-gray-500">
                                            <?= date('d/m/Y H:i', strtotime($v['dateheure'])) ?>
                                        </td>
                                        <td class="p-4 font-bold text-gray-800"><?= $v['titre'] ?></td>
                                        <td class="p-4">
                                            <?php if($v['est_active']): ?>
                                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-bold">Active</span>
                                            <?php else: ?>
                                                <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs font-bold">Annulée</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4 text-right">
                                            <button class="bg-blue-50 text-blue-600 px-3 py-1 rounded hover:bg-blue-100 text-xs mr-2" title="Gérer le parcours">
                                                <i class="fa-solid fa-route"></i> Parcours
                                            </button>
                                            <a href="#" class="text-red-400 hover:text-red-600" title="Annuler la visite">
                                                <i class="fa-solid fa-ban"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($section == 'reservations'): ?>
            <?php
            // Requête complexe : Récupérer les réservations pour les visites de CE guide
            // JOIN entre reservations, visites et utilisateurs (visiteurs)
            $sql_res = "SELECT r.*, v.titre, v.dateheure, u.nom AS nom_visiteur, u.email 
                        FROM reservations r
                        JOIN visitesguidees v ON r.idvisite = v.id
                        JOIN utilisateurs u ON r.idutilisateur = u.id
                        WHERE v.id_guide = ?
                        ORDER BY r.datereservation DESC";
            $stmt_r = $pdo->prepare($sql_res);
            $stmt_r->execute([$id_guide]);
            $reservations = $stmt_r->fetchAll();
            ?>

            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                        <tr>
                            <th class="p-4">Visiteur</th>
                            <th class="p-4">Visite Réservée</th>
                            <th class="p-4">Date Visite</th>
                            <th class="p-4 text-center">Places</th>
                            <th class="p-4">Contact</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php foreach($reservations as $r): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-4 font-bold"><?= $r['nom_visiteur'] ?></td>
                            <td class="p-4"><?= $r['titre'] ?></td>
                            <td class="p-4 text-gray-500"><?= date('d/m/Y à H:i', strtotime($r['dateheure'])) ?></td>
                            <td class="p-4 text-center">
                                <span class="bg-gray-100 px-2 py-1 rounded font-bold"><?= $r['nbpersonnes'] ?></span>
                            </td>
                            <td class="p-4 text-blue-600 hover:underline">
                                <a href="mailto:<?= $r['email'] ?>"><?= $r['email'] ?></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($reservations)): ?>
                            <tr>
                                <td colspan="5" class="p-8 text-center text-gray-500">Aucune réservation pour le moment.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>

    </main>

</body>
</html>