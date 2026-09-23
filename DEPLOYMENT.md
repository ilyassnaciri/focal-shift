# Déployer Focal-Shift V16

## Important

GitHub héberge et partage le code. **GitHub Pages ne convient pas à ce projet**, car il ne prend pas en charge PHP et MySQL. Le site doit être déployé sur un hébergement PHP/MySQL ou sur une plateforme acceptant Docker.

## Option 1 — Consultation locale avec WampServer

1. Installer WampServer avec PHP 8.2 ou supérieur et MySQL 8.
2. Copier le projet dans `C:\wamp64\www\focal-shift`.
3. Démarrer Apache et MySQL.
4. Ouvrir phpMyAdmin depuis `http://localhost/phpmyadmin/`.
5. Importer `database/focal_shift.sql`.
6. Ouvrir `http://localhost/focal-shift/`.

## Option 2 — Consultation locale avec Docker

Pré-requis : Git et Docker Desktop.

```bash
git clone https://github.com/ilyassnaciri/focal-shift.git
cd focal-shift
docker compose up --build
```

Le site devient accessible sur `http://localhost:8080`.

Pour arrêter les conteneurs :

```bash
docker compose down
```

Pour supprimer aussi la base locale de démonstration :

```bash
docker compose down -v
```

## Option 3 — Site public

Choisir une plateforme compatible avec une image Docker et une base MySQL managée.

1. Créer un compte sur l’hébergeur retenu.
2. Créer un nouveau projet depuis le dépôt GitHub public.
3. Demander à la plateforme de construire le `Dockerfile` situé à la racine.
4. Créer une base MySQL séparée.
5. Importer `database/focal_shift.sql` dans cette base.
6. Définir les variables suivantes dans les paramètres de l’application :

```text
FOCAL_DB_HOST
FOCAL_DB_PORT
FOCAL_DB_NAME
FOCAL_DB_USER
FOCAL_DB_PASS
```

7. Redéployer l’application et ouvrir l’URL HTTPS fournie.

## Vérifications avant partage public

- ne jamais publier de mot de passe réel dans GitHub ;
- remplacer tous les identifiants de démonstration avant une vraie exploitation ;
- désactiver ou supprimer les scripts `upgrade-*.php` et `diagnostic.php` ;
- définir la variable d’environnement `FOCAL_APP_ENV=production` ;
- activer HTTPS ;
- vérifier les permissions du dossier `uploads` ;
- mettre en place des sauvegardes MySQL ;
- ajouter un stockage persistant pour les photos ;
- ne pas activer de paiement ou d’assurance sans prestataire agréé.

## Mise à jour depuis GitHub

Après une modification publiée :

```bash
git pull origin main
docker compose up --build -d
```
