# Démonstration complète Focal-Shift V3 — 8 à 10 minutes

## Préparation avant le passage

- WampServer vert et `diagnostic.php` entièrement vert.
- Exécuter une fois `upgrade-v3.php`.
- Ouvrir deux fenêtres : une fenêtre normale pour le vendeur et une fenêtre privée pour l’acheteur.
- Comptes rapides :
  - acheteur : `acheteur.demo@focal-shift.local` ;
  - vendeur : `claire.demo@focal-shift.local` ;
  - utiliser les boutons de connexion rapide en environnement de développement.
- Préparer une photo JPG, PNG ou WebP de moins de 8 Mo pour le test de publication.

## 1. Accueil — 45 secondes

1. Montrer le montage vidéo en mouvement et le titre sur une ligne en grand écran.
2. Présenter les deux entrées : estimer un équipement et explorer le catalogue.
3. Faire défiler jusqu’aux trois niveaux : faible, moyen et élevé.
4. Expliquer que le texte et les symboles `✓`, `≈`, `!` complètent les couleurs pour l’accessibilité.

## 2. Catalogue et carte — 1 minute 30

1. Filtrer par état `Très bon état`.
2. Choisir `Paris`, puis un rayon de `25 km`.
3. Basculer de `Grille` à `Carte`.
4. Montrer qu’un impact faible ouvre droit à 5 % de réduction pour le locataire.
5. Cliquer sur `Autour de moi` seulement si le navigateur autorise la géolocalisation.

## 3. Simulateur — 1 minute 30

Utiliser : `Boîtiers photo`, `Sony`, `A7 IV`, année `2023`, `Très bon état`, 4 remises en circulation, 15 km et réparation cochée.

Présenter dans cet ordre : valeur estimée, vente nette, location par jour, projections, seuil de récupération, puis niveau d’impact. Insister sur le fait qu’aucun nombre sur 100 n’est affiché au client.

## 4. Publication vendeur — 1 minute 30

1. Dans la fenêtre normale, ouvrir `Connexion` et utiliser le bouton de connexion rapide de Claire.
2. Cliquer sur `Publier une offre`.
3. Renseigner un produit, choisir vente/location, Paris et la photo préparée.
4. Valider puis montrer la fiche créée.
5. Retourner au catalogue et rechercher le modèle pour prouver l’écriture MySQL et l’affichage de la photo.

## 5. Messagerie acheteur–vendeur — 2 minutes

1. Dans la fenêtre privée, ouvrir `Connexion` et utiliser le bouton de connexion rapide de l’acheteur.
2. Ouvrir une offre appartenant à Claire et cliquer sur `Contacter le propriétaire`.
3. Envoyer : `Bonjour Claire, le matériel est-il disponible vendredi de 9 h à 18 h avec deux batteries ?`
4. Dans la fenêtre vendeur, ouvrir `Messagerie`, sélectionner la conversation et répondre : `Bonjour, oui. Le kit comprend deux batteries et un état photographique sera réalisé au départ.`
5. Revenir dans la fenêtre acheteur, actualiser la conversation et montrer la réponse.

## 6. Journal, FAQ et conclusion — 1 minute

1. Montrer les liens d’unboxing YouTube et les guides de réparation externes du Journal.
2. Ouvrir la FAQ sur la protection, la caution, les incidents et la réparation prioritaire.
3. Conclure : le parcours relie interface, logique métier, PHP, MySQL, photo, carte et messagerie dans une expérience cohérente.

## Plan de secours

- Si la carte OpenStreetMap ne charge pas : utiliser la carte locale de secours incluse.
- Si la géolocalisation est refusée : choisir manuellement Paris et 25 km.
- Si Internet est indisponible : ne pas ouvrir les liens YouTube ; toutes les fonctions principales restent locales.
- Si une conversation existe déjà : l’ouvrir et envoyer un nouveau message horodaté.
