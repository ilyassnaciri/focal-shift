# Démonstration fonctionnelle — Focal-Shift V17

## Préparation

Pour une installation neuve, importer `database/focal_shift.sql`. Pour une base provenant de la V16, ouvrir une fois `upgrade-v17.php`.

## Parcours à présenter

1. Se connecter puis lancer le simulateur pour obtenir la fourchette d’un produit.
2. Ouvrir **Publier une offre** et choisir soit **Vendre uniquement**, soit **Louer uniquement**.
3. Compléter la description, les huit caractéristiques techniques et sélectionner une photo JPG, PNG ou WebP.
4. Publier : la fiche s’ouvre immédiatement avec la photo choisie et le badge **Produit non vérifié**.
5. Revenir au catalogue correspondant pour montrer que l’annonce est déjà visible.
6. Rouvrir l’annonce depuis **Mon espace > Mes offres**.
7. Cliquer sur **Faire vérifier ce produit**, choisir le dépôt partenaire ou l’envoi suivi et confirmer.
8. Revenir à la fiche : l’annonce reste visible et l’action devient **Suivre ma demande de vérification**.

## Résultat attendu

- l’offre n’apparaît que dans le catalogue correspondant au choix vente/location ;
- la photo téléchargée est utilisée sur la carte et la fiche ;
- le statut non vérifié est visible sans bloquer la publication ;
- seul le propriétaire accède à la demande de vérification ;
- la fiche technique présente huit rubriques ;
- la demande de contrôle ne retire pas l’annonce du catalogue.
