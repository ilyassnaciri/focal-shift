# Focal-Shift V16 — Marketplace premium de matériel photo et vidéo

Focal-Shift est un prototype fonctionnel de marketplace permettant d’acheter, de louer, de vendre ou de mettre en location du matériel photo et vidéo. Le projet met l’accent sur la confiance, la vérification de l’état, la transparence tarifaire et la remise en circulation des équipements.

## Démonstration fonctionnelle

- catalogues distincts pour l’achat et la location ;
- fiches produits, disponibilités, assurance et prix complet ;
- simulateur de vente/location en deux étapes ;
- prix d’annonce encadré dans une fourchette de ±20 % ;
- indicateur circulaire et réduction de 5 % pour les locations vertes ;
- comptes, messagerie, demandes et restitution photographique ;
- Blog avec interviews, tests, guides, FAQ, maillage interne et filtres interactifs ;
- conformité RGPD, sécurité applicative et accessibilité prises en compte dans le POC.

## Version 16

- filtres **Tous**, **Interviews**, **Tests produits** et **Guides** désormais fonctionnels ;
- sept articles éditoriaux dépassant chacun 3 000 caractères ;
- liens contextuels dans le cœur des articles vers d’autres contenus, les catalogues, les produits et le simulateur ;
- état actif visible et utilisable au clavier ;
- nombre de publications actualisé après chaque filtre ;
- documentation GitHub et procédure de déploiement public ajoutées ;
- configuration sensible prévue via variables d’environnement ;
- environnement Docker fourni pour faciliter l’installation.

## Technologies

| Couche | Technologie |
|---|---|
| Interface | HTML5, CSS3, JavaScript natif |
| Serveur | PHP 8.2+ |
| Données | MySQL 8 |
| Accès aux données | PDO et requêtes préparées |
| Déploiement | Apache, WampServer ou Docker |

## Démarrage rapide avec Docker

```bash
git clone https://github.com/ilyassnaciri/focal-shift.git
cd focal-shift
docker compose up --build
```

Ouvrez ensuite `http://localhost:8080`. La procédure complète, notamment pour une mise en ligne publique, se trouve dans [DEPLOYMENT.md](DEPLOYMENT.md).

> GitHub rend le code accessible publiquement, mais GitHub Pages ne peut pas exécuter PHP ou MySQL. Pour obtenir un site public fonctionnel, utilisez un hébergeur compatible PHP/MySQL ou un déploiement Docker.

Application fonctionnelle du Digital Consulting Project MBA ESG. Elle démontre une chaîne complète : interface responsive → JavaScript → PHP → règles métier → MySQL → restitution utilisateur.

## Version 14 — indicateurs produits et restitution démontrable

- indicateurs vert, orange et rouge rétablis sur les cartes et les fiches produits ;
- réduction de 5 % affichée et calculée uniquement pour une location classée verte ;
- compte Claire avec une Blackmagic Pocket Cinema 6K Pro actuellement louée ;
- compte Ilyass avec la location terminée et le bouton de restitution avec photos ;
- boutons d’accès direct aux deux comptes sur la page de connexion en environnement local ;
- migration `upgrade-v14.php` pour ajouter ces exemples sans supprimer les données existantes.

### Démonstration V14

1. Exécuter `upgrade-v14.php` après les migrations précédentes.
2. Ouvrir `login.php`.
3. Choisir **Claire · ses offres** pour montrer l’offre et la demande reçue.
4. Se déconnecter puis choisir **Ilyass · rendre le produit**.
5. Dans **Mes demandes**, cliquer sur **Rendre le produit · ajouter les photos**.

En développement, les comptes de démonstration sont accessibles avec les boutons de connexion rapide. Un mot de passe manuel facultatif peut être défini localement avec `FOCAL_DEMO_PASSWORD` sans être publié.

## Version 13 — prix encadrés, démonstration et maillage éditorial

- simulateur en deux étapes avec exemples premium de 48 à 120 € par jour ;
- prix proposé modifiable dans une fourchette obligatoire de −20 % à +20 %, contrôlée côté interface et côté serveur ;
- accueil réordonné : promesse par situation, catalogues séparés, preuves et chiffres clés avant les avis ;
- cartes de valeur redessinées sans numérotation ;
- séparation stricte entre une annonce à vendre et une annonce à louer ;
- maillage entre articles, FAQ, articles précédent/suivant, produits concernés, catalogues et simulateur ;
- guide de démonstration orale disponible dans `GUIDE_DEMONSTRATION_V14.md`.

### Mise à niveau

Pour une base issue de la V10 ou antérieure, exécutez successivement les scripts de migration manquants jusqu’à `upgrade-v11.php`. Les améliorations V13 de prix et de navigation ne nécessitent pas de nouvelle table.

## Version 10 — contact, compréhension et preuve sociale

- page Contact accessible depuis l’en-tête et le footer, avec validation, consentement et enregistrement MySQL ;
- glossaire accessible depuis le footer et la page Contact ;
- liens vers Instagram, LinkedIn et YouTube dans le footer ;
- chiffres clés animés à leur apparition, avec désactivation automatique si l’utilisateur réduit les animations ;
- portraits fictifs ajoutés aux témoignages, avec alternatives textuelles ;
- migration `upgrade-v10.php` sans suppression des données existantes.

### Mise à niveau depuis la V9

1. Conservez le dossier `uploads` et votre base existante.
2. Remplacez les fichiers du projet par ceux de la V10.
3. Ouvrez `http://localhost/focal-shift/upgrade-v10.php`.
4. Cliquez sur **Installer la V10 sans effacer mes données**.
5. Testez Contact, Glossaire, les compteurs et le footer.

## Version 9 — contenu, confiance et transaction

- accueil enrichi avec chiffres clés, partenaires, avis et succès clients ;
- newsletter fonctionnelle avec email, profil et consentement enregistrés en base ;
- navigation « Journal » renommée « Blog » ;
- Blog enrichi avec interviews, tests produits, guides et quatre articles détaillés ;
- interview principale reliée à la vidéo YouTube fournie ;
- footer complété avec RGPD, données personnelles et mentions légales ;
- parcours d’achat avec prix du produit, livraison et choix du paiement ;
- parcours de location avec dates, calendrier, nombre de jours, livraison et assurance estimée ;
- vendeur capable de bloquer une période précise d’indisponibilité ;
- demandes d’achat et de location enregistrées dans MySQL avec contrôle CSRF et vérification des droits.

### Mise à niveau depuis la V8

1. Conservez le dossier `uploads` et votre base existante.
2. Remplacez les fichiers du projet par ceux de la V9.
3. Ouvrez `http://localhost/focal-shift/upgrade-v9.php`.
4. Cliquez sur **Installer la V9 sans effacer mes données**.
5. Contrôlez l’accueil, le Blog, une demande d’achat, une demande de location et une indisponibilité vendeur.

L’assurance, les moyens de paiement et les logos partenaires illustrent le parcours cible. Leur activation commerciale nécessite des contrats et une validation juridique.

## Version 8 — gestion complète des annonces

- boutons de gestion visibles uniquement par le propriétaire connecté ;
- modification de l’annonce, des prix, de la disponibilité, de la photo et des critères circulaires ;
- statut « Réservé / actuellement loué » avec retrait automatique du catalogue ;
- possibilité de remettre ensuite l’annonce disponible ;
- suppression définitive avec confirmation, contrôle CSRF et vérification du propriétaire ;
- suppression en cascade des photos et conversations liées ;
- tableau « Mes offres » enrichi avec le statut disponible ou réservé/loué.

### Mise à niveau obligatoire depuis la V6

1. Remplacez les fichiers du site par ceux de la V8 en conservant votre dossier `uploads`.
2. Ne réimportez pas le fichier SQL : vos données existantes doivent être conservées.
3. Ouvrez `http://localhost/focal-shift/upgrade-v8.php`.
4. Cliquez sur **Installer la V8 sans effacer mes données**.
5. Ouvrez ensuite `http://localhost/focal-shift/diagnostic.php`.

## Corrections V6 — fluidité et parcours métier

- vidéo locale multi-produits de 23 secondes avec cinq plans fixes et fondus doux, sans zoom ni déplacement ;
- aperçu photographique immédiat dans le simulateur après le choix de la catégorie ;
- remplacement de « remises en circulation » par « locations déjà réalisées », avec aide contextuelle ;
- explication professionnelle de la réparation comme prolongation documentée de la durée de vie ;
- panneau de filtres catalogue limité à la hauteur de l’écran, défilement interne et bouton toujours accessible ;
- passerelle simulateur → publication : la dernière estimation apparaît dans le formulaire et peut remplir les deux prix en un clic ;
- aucun changement de schéma SQL : la V6 peut remplacer la V5/V4 sans réimporter la base.

### Mise à niveau depuis la V4 ou la V5

1. Fermez les onglets Focal-Shift puis sauvegardez `C:\wamp64\www\focal-shift\uploads`.
2. Renommez le dossier actuel en `focal-shift-sauvegarde`.
3. Copiez le dossier V6 sous le nom exact `C:\wamp64\www\focal-shift`.
4. Recopiez uniquement le contenu de l’ancien dossier `uploads` dans le nouveau.
5. Ne réimportez pas `database/focal_shift.sql` : aucune migration de base n’est nécessaire.
6. Ouvrez `http://localhost/focal-shift/diagnostic.php`, puis rechargez le site avec `Ctrl+F5`.

## Finition V5 — accueil cinématographique

- nouvelle scène studio ultra-large conçue pour l’accueil ;
- composition unique et stable avec espace sombre réservé au texte ;
- suppression du montage d’appareils successifs et des micro-zooms ;
- animation limitée à une transition lumineuse lente, sans déplacement des objets ;
- boucle locale silencieuse de 18 secondes, 1280×720 à 30 images/seconde ;
- nouvelle image de couverture cohérente avec la vidéo ;
- versionnement de l’URL média pour éviter que le navigateur conserve l’ancienne vidéo en cache.

## Correction V4 — compte polyvalent

- un seul formulaire de connexion, sans choix acheteur ou vendeur ;
- un seul type de compte donnant accès à l’achat, la location, la vente et la publication ;
- suppression du choix « usage principal » pendant l’inscription ;
- suppression des deux boutons « Compte acheteur » et « Compte vendeur » ;
- après déconnexion, retour direct vers `login.php` ;
- `upgrade-v4.php` transforme les comptes existants en comptes polyvalents sans supprimer leurs données.

## Nouveautés V3

- indicateur circulaire simplifié en trois niveaux, sans note visible sur 100 ;
- impact faible vert avec 5 % de réduction pour le locataire, moyen orange et élevé rouge ;
- libellés, symboles et contrastes renforcés pour ne jamais dépendre uniquement de la couleur ;
- vidéo d’accueil remplacée par un montage local dynamique de 17 secondes ;
- filtres catalogue par état, ville et rayon de 5 à 100 km ;
- portail de connexion unique avec accès rapide aux deux comptes de présentation ;
- Journal professionnalisé avec ressources YouTube et guides de réparation ;
- FAQ entièrement réécrite et scénario complet de soutenance dans `docs/SCENARIO_DEMO_V3.md`.

## Mise à niveau depuis la V2

1. Sauvegarder le dossier actuel et conserver son dossier `uploads`.
2. Copier la V3 sous le nom exact `C:\wamp64\www\focal-shift`.
3. Recopier les anciennes photos dans le nouveau dossier `uploads`.
4. Ne pas réimporter `database/focal_shift.sql`.
5. Ouvrir `http://localhost/focal-shift/upgrade-v3.php` et lancer la migration.
6. Contrôler `http://localhost/focal-shift/diagnostic.php`.

## Nouveautés V2

- accueil haut de gamme avec vidéo locale et titre corrigé ;
- catalogue en grille ou carte avec géolocalisation optionnelle ;
- photographies studio locales et photos réellement téléversées pour les nouvelles annonces ;
- Circular Score vert/orange/rouge et réduction sur les frais de service ;
- score ajouté au simulateur ;
- connexion séparée acheteur/locataire et vendeur/bailleur ;
- création de nouveaux comptes dans la table MySQL `users` ;
- espace membre et offres rattachées au vendeur connecté ;
- messagerie testable entre acheteur et propriétaire ;
- journal éditorial avec vidéo et FAQ assurance/réparation ;
- correction du débordement du résultat du simulateur.

## Mise à niveau depuis la V1 sans perte de données

1. Sauvegarder ou renommer l’ancien dossier `C:\wamp64\www\focal-shift`.
2. Copier le dossier V2 sous le nom exact `C:\wamp64\www\focal-shift`.
3. Ne pas réimporter `focal_shift.sql` si la V1 fonctionne déjà.
4. Ouvrir `http://localhost/focal-shift/upgrade-v2.php`.
5. Cliquer sur **Installer la V2 sans effacer mes données**.
6. Ouvrir `http://localhost/focal-shift/diagnostic.php` : 8 tables doivent être détectées.

Les portails de connexion proposent des boutons d’accès aux comptes de démonstration en environnement de développement.

## Ce qui fonctionne réellement

- accueil premium et responsive ;
- catalogue alimenté par MySQL avec vue carte ;
- recherche et filtres via requêtes PHP préparées ;
- fiche produit et marqueurs de confiance ;
- publication d'offre avec photo réelle, validation navigateur + serveur, protection CSRF, contrôle d'image et transaction SQL ;
- calcul du Circular Score interne ;
- simulateur de cote via `fetch()` vers une API PHP ;
- journalisation des simulations dans MySQL ;
- consentement équilibré, sans traceur non essentiel chargé par défaut ;
- inscription, connexion, espace membre et messagerie MySQL ;
- navigation clavier, focus visible, HTML sémantique et réduction des animations.

## Installation neuve dans WampServer

### 1. Copier le projet

Décompressez le dossier `focal-shift`, puis copiez-le ici :

```text
C:\wamp64\www\focal-shift\
```

L'arborescence finale doit notamment contenir :

```text
C:\wamp64\www\focal-shift\index.php
C:\wamp64\www\focal-shift\database\focal_shift.sql
```

### 2. Démarrer WampServer

L'icône doit être verte. Dans votre capture, Apache 2.4.65, PHP 8.3.28 et MySQL 8.4.7 sont actifs : l'environnement est compatible.

Ouvrez :

```text
http://localhost/
```

Puis phpMyAdmin :

```text
http://localhost/phpmyadmin/
```

### 3. Importer la base

1. Dans phpMyAdmin, cliquez sur **Importer**.
2. Choisissez `C:\wamp64\www\focal-shift\database\focal_shift.sql`.
3. Conservez le jeu de caractères UTF-8.
4. Cliquez sur **Importer**.
5. Vérifiez que la base `focal_shift` contient 6 tables.

Le script réinitialise uniquement les tables de démonstration de la base `focal_shift` lorsqu'il est réimporté.

Ouvrez ensuite `http://localhost/focal-shift/upgrade-v2.php` et lancez la mise à niveau pour obtenir les 8 tables de la V2.

### 4. Ouvrir le POC

```text
http://localhost/focal-shift/
```

Avant la première démonstration, ouvrez aussi le diagnostic :

```text
http://localhost/focal-shift/diagnostic.php
```

Tous les contrôles doivent être verts. Cette page est destinée au local et doit être retirée avant un déploiement public.

La configuration par défaut correspond à WampServer : MySQL sur `127.0.0.1:3306`, utilisateur `root`, mot de passe vide. Si votre mot de passe est différent, modifiez seulement `includes/config.php` ou définissez les variables `FOCAL_DB_HOST`, `FOCAL_DB_PORT`, `FOCAL_DB_NAME`, `FOCAL_DB_USER`, `FOCAL_DB_PASS`.

### 5. Vérifier les droits d'upload

Sous Windows/WampServer, le dossier `uploads` est normalement inscriptible. Si le dépôt d'annonce échoue, vérifiez que PHP peut écrire dans :

```text
C:\wamp64\www\focal-shift\uploads\
```

Vérifiez aussi dans `php.ini` :

```ini
file_uploads = On
upload_max_filesize = 8M
post_max_size = 10M
```

Redémarrez les services WampServer après toute modification de `php.ini`.

## Parcours de test en 5 minutes

1. Ouvrir l'accueil ; vérifier les deux CTA sans défilement.
2. Aller dans **Catalogue** ; tester **Grille**, **Carte** puis **Autour de moi**.
3. Ouvrir le Sony A7 IV ; expliquer les marqueurs de confiance.
4. Ouvrir **Simulateur** ; saisir `Boîtiers photo`, `Sony`, `A7 IV`, `2023`, `Très bon état`.
5. Vérifier l'estimation, le tarif/jour, les projections et le seuil.
6. Ouvrir le portail vendeur démo puis cliquer sur **Publier une offre**.
7. Ajouter une annonce avec une image JPG/PNG/WebP ; choisir vente ou location.
8. Vérifier que la fiche créée s'affiche puis qu'elle est visible dans le catalogue.
9. Ouvrir le portail acheteur démo et envoyer un message au propriétaire.

## Limites assumées du POC

Ne sont pas implémentés : KYC réel, encaissement bancaire réel, souscription d’assurance auprès d’un assureur, caution débitée, avis vérifiés et gestion d'incident complète. L’inscription, la messagerie, les demandes d’achat/location, le calcul des montants et le blocage des disponibilités sont fonctionnels dans le POC. Les moyens de paiement et l’assurance restent des simulations sans transaction financière ni contrat réel.

Les prix du référentiel sont des **hypothèses de démonstration**, clairement étiquetées dans la base et l'interface. Le Circular Score n'est pas un score CO₂.

## Structure

```text
focal-shift/
├── index.php                 Accueil
├── catalogue.php             Recherche et filtres SQL
├── product.php               Fiche produit / confiance
├── transaction.php           Achat/location, devis et paiement simulé
├── availability.php          Indisponibilités déclarées par le vendeur
├── deposit.php               Dépôt et INSERT SQL
├── simulator.php             Interface du simulateur
├── login.php / register.php  Comptes MySQL
├── account.php               Espace membre
├── messages.php              Messagerie liée aux offres
├── blog.php / article.php    Blog, interviews, tests et articles
├── faq.php / legal.php       Réassurance et mentions légales
├── newsletter.php            Inscription newsletter en base
├── upgrade-v9.php            Migration V9 sans perte de données
├── methodology.php           Hypothèses et limites
├── privacy.php               RGPD du POC et cible V1
├── api/simulator.php         Calcul JSON côté serveur
├── includes/                 Configuration, PDO, composants
├── assets/css/style.css      Design system responsive
├── assets/js/                Interactions et simulateur
├── assets/images/            Photographies studio originales
├── database/focal_shift.sql  Schéma et données de démonstration
├── docs/                     Architectures, wireframes, tests, oral
└── uploads/                  Photos déposées
```

## Sécurité et passage en production

Le POC applique déjà des requêtes préparées, l'échappement des sorties, la validation serveur, CSRF et le contrôle MIME. Avant production : secrets hors code, HTTPS obligatoire, stockage objet pour les images, scan antivirus, authentification robuste, contrôle d'accès, limitation de débit, logs centralisés, sauvegardes, analyse d'impact RGPD si nécessaire et contrats avec les partenaires de paiement/KYC/assurance.
