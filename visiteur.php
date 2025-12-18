<?php
session_start();
require_once 'config.php';

// 1. SÉCURITÉ : Vérifier si c'est un visiteur connecté
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'visiteur') {
    header("Location: login.php?redirect=visiteur");
    exit();
}

$id_user = $_SESSION['user_id'];
$message = "";

// --- LOGIQUE : RÉSERVER UNE VISITE ---
if (isset($_POST['btn_reserver'])) {
    $id_visite_form = $_POST['id_visite'];
    $nb_places = $_POST['nb_places'];

    $sql_resa = "INSERT INTO reservations (idvisite, idutilisateur, nbpersonnes) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql_resa);
    if ($stmt->execute([$id_visite_form, $id_user, $nb_places])) {
        $message = "<div class='fixed top-24 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-xl z-50 animate-bounce'><i class='fa-solid fa-check-circle mr-2'></i> Réservation confirmée !</div>";
    } else {
        $message = "<div class='fixed top-24 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-xl z-50'><i class='fa-solid fa-triangle-exclamation mr-2'></i> Erreur technique.</div>";
    }
}

// --- LOGIQUE : LAISSER UN COMMENTAIRE ---
if (isset($_POST['btn_commenter'])) {
    $id_visite_comment = $_POST['id_visite_comment'];
    $note = $_POST['note'];
    $texte = htmlspecialchars($_POST['commentaire']);

    $sql_com = "INSERT INTO commentaires (idvisitesguides, idutilisateur, note, texte) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql_com);
    if ($stmt->execute([$id_visite_comment, $id_user, $note, $texte])) {
        $message = "<div class='fixed top-24 right-4 bg-blue-500 text-white px-6 py-3 rounded-lg shadow-xl z-50'><i class='fa-solid fa-comment-dots mr-2'></i> Merci pour votre avis !</div>";
    }
}

// --- RÉCUPÉRATION DES DONNÉES ---

// 1. Visites FUTURES (Pour la section Visites existante)
// On sélectionne les visites actives et futures
$sql_futures = "SELECT * FROM visitesguidees WHERE dateheure > NOW() AND statut = 1 ORDER BY dateheure ASC";
$visites_futures = $pdo->query($sql_futures)->fetchAll();

// 2. Visites PASSÉES (Pour la nouvelle section Historique)
$sql_passees = "SELECT v.*, r.nbpersonnes 
                FROM reservations r
                JOIN visitesguidees v ON r.idvisite = v.id_visite
                WHERE r.idutilisateur = ? AND v.dateheure < NOW()
                ORDER BY v.dateheure DESC";
$stmt_hist = $pdo->prepare($sql_passees);
$stmt_hist->execute([$id_user]);
$historique = $stmt_hist->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Visiteur - Zoo ASSAD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'maroc-red': '#C1272D',
                        'maroc-green': '#006233',
                        'sand-light': '#Fdfbf7',
                    },
                    fontFamily: {
                        'headings': ['Montserrat', 'sans-serif'],
                        'body': ['Open Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23006233' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>
<body class="font-body bg-sand-light text-gray-800 flex flex-col min-h-screen">

    <?= $message ?>

    <nav class="bg-maroc-red text-white fixed w-full z-50 shadow-lg transition-all duration-300" id="navbar">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="flex items-center gap-3 group">
                <i class="fa-solid fa-paw text-yellow-400 text-2xl group-hover:rotate-12 transition"></i>
                <div class="flex flex-col">
                    <span class="font-headings font-bold text-2xl tracking-wider">ZOO ASSAD</span>
                    <span class="text-xs text-yellow-300 font-semibold tracking-widest">ESPACE MEMBRE</span>
                </div>
            </a>

            <div class="hidden md:flex items-center space-x-8 font-semibold text-sm uppercase tracking-wide">
                <a href="#accueil" class="hover:text-yellow-300 transition">Accueil</a>
                <a href="#asaad" class="hover:text-yellow-300 transition">Asaad</a>
                <a href="#animaux" class="hover:text-yellow-300 transition">Nos Animaux</a>
                <a href="#visites" class="hover:text-yellow-300 transition">Réserver</a>
                <a href="#historique" class="hover:text-yellow-300 transition">Historique</a>
            </div>

            <div class="hidden md:flex items-center space-x-4">
                <span class="text-yellow-200 font-medium text-sm">Bonjour, <?= htmlspecialchars($_SESSION['nom']) ?></span>
                <a href="logout.php" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-full font-bold text-sm transition border border-white/50">
                    <i class="fa-solid fa-right-from-bracket mr-2"></i>Déconnexion
                </a>
            </div>

            <button class="md:hidden text-2xl focus:outline-none">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </nav>

    <header id="accueil" class="relative h-screen flex items-center justify-center overflow-hidden">
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1546182990-dffeafbe841d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80" alt="Lion Atlas" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-r from-maroc-red/80 to-black/50"></div>
        </div>

        <div class="container mx-auto px-6 relative z-10 grid md:grid-cols-2 gap-12 items-center pt-20">
            <div class="text-white space-y-6">
                <div class="inline-flex items-center gap-2 bg-yellow-500 text-maroc-red font-bold px-4 py-1 rounded-full text-sm mb-2 shadow-lg animate-pulse">
                    <i class="fa-solid fa-user-check"></i> MEMBRE CONNECTÉ
                </div>
                <h1 class="font-headings text-5xl md:text-7xl font-extrabold leading-tight drop-shadow-lg">
                    Bienvenue sur<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-yellow-200">votre espace</span>
                </h1>
                <p class="text-lg md:text-xl text-gray-100 max-w-lg leading-relaxed">
                    Ravi de vous revoir. Réservez vos prochaines aventures ou partagez vos souvenirs avec nous.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 pt-4">
                    <a href="#visites" class="bg-maroc-green hover:bg-green-800 text-white text-center px-8 py-4 rounded-lg font-bold text-lg shadow-lg transition transform hover:-translate-y-1">
                        Réserver une visite
                    </a>
                </div>
            </div>
        </div>
    </header>

    <section id="asaad" class="py-20 bg-pattern">
        <div class="container mx-auto px-6">
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden border-t-4 border-maroc-red flex flex-col md:flex-row">
                <div class="md:w-1/2 relative h-96 md:h-auto">
                    <img src="https://images.unsplash.com/photo-1614027164847-1b28cfe1df60?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Lion Majestic" class="w-full h-full object-cover">
                    <div class="absolute bottom-0 left-0 bg-maroc-green text-white px-6 py-2 rounded-tr-xl font-bold z-10">
                        #DimaMaghrib
                    </div>
                </div>
                <div class="md:w-1/2 p-10 md:p-16 flex flex-col justify-center space-y-6">
                    <h2 class="font-headings text-4xl font-bold text-maroc-red">Asaad : Lion de l'Atlas</h2>
                    <h3 class="text-xl font-semibold text-maroc-green">Symbole de la CAN 2025</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Le Lion de l'Atlas est plus qu'un animal, c'est l'emblème de notre nation. Asaad vous accueille pour vous faire découvrir les secrets de son espèce.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section id="animaux" class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <span class="text-maroc-red font-bold tracking-widest uppercase text-sm">Faune Africaine</span>
                <h2 class="font-headings text-4xl font-extrabold text-gray-900 mt-2">Nos Pensionnaires</h2>
                <div class="w-24 h-1 bg-maroc-green mx-auto mt-4 rounded-full"></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
                <div class="group bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
                    <div class="h-64 overflow-hidden relative">
                        <img src="https://images.unsplash.com/photo-1547721064-da6cfb341d50?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Lion" class="w-full h-full object-cover">
                    </div>
                    <div class="p-6">
                        <h3 class="font-headings font-bold text-xl text-gray-800 mb-1">Lion de l'Atlas</h3>
                        <p class="text-xs font-bold text-maroc-green uppercase tracking-wide">Montagnes</p>
                    </div>
                </div>
                </div>
            <div class="text-center">
                <a href="animaux.php" class="inline-flex items-center justify-center gap-3 bg-white border-2 border-gray-900 text-gray-900 hover:bg-gray-900 hover:text-white font-bold py-4 px-10 rounded-full transition duration-300 text-lg shadow-lg group">
                    <i class="fa-solid fa-paw group-hover:rotate-12 transition"></i> Explorer la collection
                </a>
            </div>
        </div>
    </section>

    <section id="visites" class="py-20 bg-gray-50 relative">
        <div class="absolute top-0 left-0 w-full h-20 bg-gradient-to-b from-white to-gray-50"></div>
        
        <div class="container mx-auto px-6 relative z-10">
            <div class="text-center mb-10">
                <span class="text-maroc-red font-bold tracking-widest uppercase text-sm">Agenda CAN 2025</span>
                <h2 class="font-headings text-4xl font-extrabold text-gray-900 mt-2">Réserver une visite</h2>
            </div>

            <div class="max-w-4xl mx-auto bg-white p-2 rounded-full shadow-xl border border-gray-200 mb-16 transform -translate-y-4">
                <div class="flex flex-col md:flex-row items-center p-2 gap-2">
                    <div class="flex-grow w-full md:w-auto flex items-center px-4">
                        <i class="fa-solid fa-magnifying-glass text-gray-400 mr-3"></i>
                        <input type="text" placeholder="Rechercher une visite..." class="w-full bg-transparent border-none focus:ring-0 text-gray-700">
                    </div>
                    <button class="bg-maroc-red text-white font-bold py-3 px-8 rounded-full">Rechercher</button>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <?php if(empty($visites_futures)): ?>
                    <div class="col-span-3 text-center text-gray-500 italic py-10">
                        Aucune visite disponible pour le moment.
                    </div>
                <?php else: ?>
                    <?php foreach($visites_futures as $v): ?>
                        <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden flex flex-col h-full hover:shadow-xl transition duration-300 group">
                            <div class="relative h-48 overflow-hidden bg-gray-200">
                                <img src="https://images.unsplash.com/photo-1596716766417-76813296c00e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Visite" class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
                                <div class="absolute top-4 right-4 bg-maroc-green text-white font-bold px-3 py-1 rounded-md text-sm shadow-sm">
                                    <?= $v['prix'] ?> MAD
                                </div>
                            </div>
                            
                            <div class="p-6 flex flex-col flex-grow">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="text-maroc-red font-bold text-xs uppercase tracking-wide">Visite Guidée</span>
                                    <span class="text-gray-400 text-xs"><i class="fa-solid fa-calendar mr-1"></i> <?= date('d/m/Y H:i', strtotime($v['dateheure'])) ?></span>
                                </div>
                                <h3 class="font-headings font-bold text-xl mb-2 text-gray-800"><?= $v['titre'] ?></h3>
                                <div class="text-gray-500 text-sm mb-4 flex-grow">
                                    <p><i class="fa-regular fa-clock mr-2"></i>Durée : <?= $v['duree'] ?> min</p>
                                    <p><i class="fa-solid fa-language mr-2"></i>Langue : <?= $v['langue'] ?></p>
                                </div>
                                
                                <div class="border-t border-gray-100 pt-4 mt-auto">
                                    <div class="flex items-center justify-between text-sm text-gray-600 mb-4">
                                        <span class="flex items-center gap-2"><i class="fa-solid fa-user-group text-maroc-green"></i> <?= $v['capacite_max'] ?> places max</span>
                                    </div>
                                    
                                    <form method="POST" action="" class="flex gap-2">
                                        <input type="hidden" name="id_visite" value="<?= $v['id'] ?>">
                                        <input type="number" name="nb_places" value="1" min="1" max="10" class="w-16 border border-gray-300 rounded text-center font-bold" title="Nombre de personnes">
                                        <button type="submit" name="btn_reserver" class="flex-grow bg-gray-900 text-white py-3 rounded-lg font-bold hover:bg-maroc-red transition shadow-lg">
                                            Réserver
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section id="historique" class="py-20 bg-white border-t border-gray-200">
        <div class="container mx-auto px-6">
            <div class="text-center mb-10">
                <span class="text-gray-500 font-bold tracking-widest uppercase text-sm">Vos souvenirs</span>
                <h2 class="font-headings text-3xl font-extrabold text-gray-900 mt-2">Mon Historique & Avis</h2>
            </div>

            <?php if(empty($historique)): ?>
                <div class="bg-gray-50 p-8 rounded-xl border border-gray-200 text-center max-w-2xl mx-auto">
                    <i class="fa-solid fa-clock-rotate-left text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">Vous n'avez pas encore effectué de visite terminée.</p>
                </div>
            <?php else: ?>
                <div class="space-y-6 max-w-4xl mx-auto">
                    <?php foreach($historique as $h): ?>
                        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 flex flex-col md:flex-row gap-6 hover:shadow-xl transition">
                            <div class="md:w-1/3 border-r border-gray-100 pr-6">
                                <h3 class="font-headings font-bold text-xl text-gray-800 mb-2"><?= $h['titre'] ?></h3>
                                <p class="text-sm text-gray-500 mb-2">
                                    <i class="fa-solid fa-calendar-check text-green-600 mr-2"></i>
                                    Le <?= date('d/m/Y', strtotime($h['dateheure'])) ?>
                                </p>
                                <span class="inline-block bg-green-100 text-green-800 text-xs px-3 py-1 rounded-full font-bold">
                                    <?= $h['nbpersonnes'] ?> place(s) réservée(s)
                                </span>
                            </div>

                            <div class="md:w-2/3">
                                <h4 class="font-bold text-sm text-gray-400 uppercase mb-3">Laisser un avis</h4>
                                <form method="POST" action="">
                                    <input type="hidden" name="id_visite_comment" value="<?= $h['id'] ?>">
                                    
                                    <div class="flex gap-4 mb-4">
                                        <label class="cursor-pointer text-sm font-bold flex items-center bg-gray-50 px-3 py-2 rounded border hover:border-yellow-400"><input type="radio" name="note" value="5" class="mr-2" checked>⭐⭐⭐⭐⭐</label>
                                        <label class="cursor-pointer text-sm font-bold flex items-center bg-gray-50 px-3 py-2 rounded border hover:border-yellow-400"><input type="radio" name="note" value="4" class="mr-2">⭐⭐⭐⭐</label>
                                        <label class="cursor-pointer text-sm font-bold flex items-center bg-gray-50 px-3 py-2 rounded border hover:border-yellow-400"><input type="radio" name="note" value="3" class="mr-2">⭐⭐⭐</label>
                                    </div>

                                    <div class="flex gap-2">
                                        <input type="text" name="commentaire" class="flex-grow border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-maroc-green focus:outline-none" placeholder="C'était comment ? (Optionnel)">
                                        <button type="submit" name="btn_commenter" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-6 rounded-lg text-sm transition shadow-md">
                                            Envoyer
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <footer class="bg-gray-900 text-white pt-16 pb-8 border-t-8 border-maroc-red mt-auto">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-3 gap-12 mb-12">
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-paw text-yellow-400 text-2xl"></i>
                        <span class="font-headings font-bold text-2xl">ZOO ASSAD</span>
                    </div>
                    <p class="text-gray-400 text-sm">Le zoo officiel de la Coupe d'Afrique des Nations 2025.</p>
                </div>
                <div>
                    <h4 class="font-bold text-lg mb-6 text-yellow-400">Liens Rapides</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="index.php" class="hover:text-white">Accueil</a></li>
                        <li><a href="#visites" class="hover:text-white">Réserver</a></li>
                        <li><a href="#historique" class="hover:text-white">Mon Historique</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-lg mb-6 text-yellow-400">Contact</h4>
                    <p class="text-gray-400 text-sm"><i class="fa-solid fa-envelope mr-2"></i> contact@zoo-assad.ma</p>
                    <p class="text-gray-400 text-sm"><i class="fa-solid fa-phone mr-2"></i> +212 5 22 00 00 00</p>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 text-center text-gray-500 text-xs">
                <p>&copy; 2025 Zoo Assad - Projet CAN 2025.</p>
            </div>
        </div>
    </footer>

</body>
</html>