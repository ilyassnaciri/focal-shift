# Focal-Shift - Dossier technique des tâches 7 à 11

**Version POC : 21 septembre 2026**  
**Responsable : Ilyass NACIRI - Architecture / Data / KPI / support technique**

## Décision de synthèse

Le POC prouve le coeur de valeur sans simuler un produit fini : une marketplace à deux faces, un catalogue SQL, un dépôt d'annonce et un simulateur de cote. Le fil directeur est **Confiance → Valeur → Circularité**.

---

## 7. Architecture fonctionnelle et arborescence

### Objectifs utilisateurs

| Acteur | Besoin | Réponse du POC | Preuve technique |
|---|---|---|---|
| Propriétaire | Connaître la valeur de son matériel | Simulateur vente/location | API PHP + référentiel SQL + calculs |
| Propriétaire | Remettre un équipement en circulation | Dépôt d'annonce | Validation + upload + transaction SQL |
| Acheteur/loueur | Trouver un équipement adapté | Catalogue et filtres | SELECT préparé dynamique |
| Toute partie | Réduire le risque perçu | Fiche produit | profil, état, score, processus cible |

### Arborescence produit

```text
Accueil
├── Catalogue
│   ├── Recherche et filtres
│   └── Fiche équipement
├── Simulateur de cote
│   └── CTA vers dépôt
├── Déposer un équipement
├── Méthodologie
└── Données et cookies
```

### Périmètre

**POC codé :** accueil, catalogue, fiche, dépôt, simulateur, méthodologie, confidentialité.  
**V1 représentée mais non codée :** authentification, KYC, paiement, préautorisation, réservation, avis, messagerie, assurance, incident.  
**Hors périmètre :** logistique opérée par Focal-Shift et calcul carbone certifié.

### Flux critiques

1. **Recherche :** filtres utilisateur → validation PHP → requête préparée → résultats SQL → fiche.
2. **Dépôt :** formulaire → validation JS → validation PHP → contrôle MIME → transaction SQL → publication → catalogue.
3. **Simulation :** caractéristiques → POST `fetch` → référence exacte ou moyenne de catégorie → calcul → journalisation → comparaison.

---

## 8. UX, wireframes, responsive et accessibilité

### Principe UX

La page d'accueil explique en moins de dix secondes les deux faces de la marketplace. Le simulateur sert d'entrée propriétaire ; le catalogue sert d'entrée demande. La confiance apparaît avant l'action et non dans une page légale secondaire.

### Wireframes fonctionnels

#### Accueil

```text
[LOGO]                     [Catalogue] [Simulateur] [Déposer]

SURTITRE
Votre matériel mérite       [Illustration produit]
mieux qu'un placard.         [Valeur] [Score]
[Estimer] [Catalogue]

[Promesse propriétaire] [Promesse demande]
[Parcours confiance en 4 étapes]
[3 équipements SQL]
[Circular Score + limite]
```

#### Catalogue

```text
[Titre + explication SQL]
[Filtres] | [Nombre de résultats]
          | [Carte] [Carte]
          | [Carte] [Carte]
```

#### Dépôt

```text
[Identification]
[Photo + état]
[Vente] [Location]
[Données Circular Score]
[Publier]
```

#### Simulateur

```text
[Catégorie / marque / modèle / année / état] | [Valeur estimée]
[Calculer]                                   | [Vendre] [Louer]
                                             | [10j] [30j] [60j]
                                             | [Seuil + hypothèses]
```

### Responsive

| Largeur | Comportement |
|---|---|
| ≥ 921 px | grilles 2-3 colonnes, filtres et panneau latéral fixes |
| 721-920 px | blocs principaux empilés, filtres sur 2 colonnes |
| ≤ 720 px | navigation repliée, formulaires et cartes sur 1 colonne |

### Accessibilité intégrée

- lien d'évitement ;
- landmarks `header`, `nav`, `main`, `footer` ;
- titres hiérarchisés et labels explicites ;
- contrastes élevés et état de focus visible ;
- navigation clavier ;
- erreurs groupées avec `role="alert"` ;
- résultats du simulateur annoncés avec `aria-live` ;
- alternative textuelle des images ;
- préférence `prefers-reduced-motion` ;
- aucun sens porté seulement par la couleur ;
- cibles tactiles confortables.

L'objectif est WCAG 2.2 niveau AA ; un audit outillé et utilisateur reste nécessaire avant production.

---

## 9. Architecture technique, data et RGPD

### Architecture du POC

```text
Navigateur
HTML sémantique + CSS responsive + JavaScript
        ↓ GET / POST / fetch JSON
Apache + PHP 8.3
contrôleurs simples + règles métier + PDO
        ↓ requêtes préparées
MySQL 8.4
utilisateurs / catégories / références / équipements / photos / simulations
```

### Justification de la stack

| Choix | Justification hackathon | Limite / évolution |
|---|---|---|
| PHP 8.3 natif | exigence du brief, rapide, visible au jury | framework MVC en V1 si équipe élargie |
| MySQL 8.4 | inclus dans WampServer, relations démontrables | réplication/sauvegardes managées en production |
| JS natif | interaction sans chaîne de build | composants industrialisés en V1 |
| CSS personnalisé | identité premium, fonctionnement hors ligne | design system partagé à formaliser |

### Modèle de données

| Table | Finalité | Données clés |
|---|---|---|
| `users` | profil propriétaire de démonstration | vérification, note, transactions |
| `categories` | taxonomie + rendement locatif hypothétique | nom, slug, taux |
| `price_reference` | données du simulateur | modèle, prix neuf, source, date |
| `equipment` | catalogue | état, prix, disponibilité, score |
| `equipment_photos` | preuve d'état | URL, type avant/après |
| `simulator_requests` | mesure et amélioration du modèle | entrée, résultat, type de correspondance |

### Sécurité POC

- PDO avec émulation désactivée et requêtes préparées ;
- échappement systématique des sorties HTML ;
- jeton CSRF pour dépôt et simulateur ;
- validation front et serveur ;
- liste blanche MIME JPG/PNG/WebP et limite 5 Mo ;
- noms d'upload aléatoires ;
- transaction SQL pour éviter une annonce sans photo ;
- cookie de session `HttpOnly` et `SameSite=Lax` ;
- aucun secret de production dans le prototype.

### RGPD by design

**Traitement réel du POC :** session technique, préférence de consentement locale, annonces et simulations de démonstration. Aucun pixel, GA ou régie publicitaire chargé.  
**Minimisation :** pas d'inscription, pas de numéro de série public, pas de paiement et consigne de ne saisir aucune donnée réelle.  
**V1 :** registre des traitements, notices par finalité, bases légales, durées documentées, droits, contrats sous-traitants, contrôle d'accès, chiffrement, journalisation et analyse d'impact selon le niveau de risque.

### Plan de taggage minimal conforme

Les événements ne sont envoyés que si la mesure d'audience est autorisée.

| Événement | Déclencheur | Paramètres non personnels | KPI |
|---|---|---|---|
| `hero_simulator` | CTA simulateur | page | taux d'entrée propriétaire |
| `hero_catalogue` | CTA catalogue | page | taux d'entrée demande |
| `simulator_complete` | calcul réussi | type de correspondance | complétion simulateur |
| `equipment_publish` | INSERT réussi | catégorie, modes | conversion simulateur/dépôt |
| `catalogue_filter` | filtres appliqués | catégorie, mode | recherche → fiche |
| `demo_request_equipment` | CTA fiche | catégorie | intention transactionnelle |

---

## 10. POC accueil, catalogue et dépôt

### Critères d'acceptation

| Fonction | Critère | Statut |
|---|---|---|
| Accueil | deux promesses et CTA visibles | implémenté |
| Catalogue | données MySQL et filtres combinables | implémenté |
| Fiche | confiance, prix, propriétaire, score | implémenté |
| Dépôt | champs obligatoires + image | implémenté |
| Dépôt | annonce visible après insertion | implémenté |
| Responsive | téléphone, tablette, bureau | implémenté dans CSS |

### Règle de publication BR1

Une annonce est publiée seulement si catégorie, marque, modèle, année, état, description et photo sont présents, avec vente ou location et le prix associé. Les contrôles sont répliqués côté serveur ; le navigateur ne constitue jamais la seule barrière.

---

## 11. Simulateur de cote et logique PHP/SQL/JS

### Formules

**Coefficient d'âge**

- année 0 : `1,00` ;
- années 1 et 2 : `0,85^âge` ;
- au-delà : `0,85² × 0,92^(âge-2)` ;
- plancher : `0,20`.

**Coefficient d'état :** comme neuf `1,00`, très bon `0,85`, bon `0,70`, usage `0,50`.

```text
valeur_revente = prix_neuf_référence × coefficient_âge × coefficient_état
tarif_location_jour = valeur_revente × taux_catégorie
revenu_net_X_jours = tarif_jour × X × (1 - 15 %)
vente_nette = valeur_revente × (1 - 10 %)
seuil_location = plafond(vente_nette / revenu_net_journalier)
```

Les taux, commissions et références sont des **hypothèses POC**. Le seuil indique combien de jours de location nette sont nécessaires pour égaler une vente nette immédiate ; cette définition évite un comparateur trompeur.

### Fallback BR2

1. recherche exacte `catégorie + marque + modèle` ;
2. sinon moyenne du prix neuf dans la catégorie ;
3. libellé explicite « Moyenne de catégorie » ;
4. sinon erreur contrôlée sans résultat inventé.

### Circular Score

| Critère | Points max | Règle POC |
|---|---:|---|
| Seconde main | 15 | équipement remis en circulation |
| Âge utile | 15 | progression jusqu'à 5 ans |
| Réutilisations | 35 | progression jusqu'à 10 mouvements |
| Réparation | 20 | réparation documentée ; sinon 8 points |
| Proximité | 15 | 15 pts ≤25 km, 8 pts ≤100 km, sinon 3 |

Ce score est un indice produit, jamais un équivalent d'émissions de CO₂.

---

## Preuve de maîtrise à présenter au jury

```text
SELECT préparé → catalogue
INSERT transactionnel → nouvelle annonce
API PHP JSON → simulateur
Calcul métier → estimation + seuil
JavaScript → expérience dynamique
Data → journalisation des simulations
RGPD → aucun traceur avant consentement
Accessibilité → clavier, contrastes, erreurs, aria-live
```

## Risques et mesures

| Risque | POC | Production |
|---|---|---|
| prix imprécis | étiquette hypothèse + source/date | flux de prix, recalibrage, contrôle humain |
| upload malveillant | MIME, taille, nom aléatoire | stockage isolé + scan antivirus |
| injection SQL | requêtes préparées | tests SAST/DAST + moindre privilège |
| données personnelles | aucune donnée réelle demandée | gouvernance complète et droits |
| fausse promesse de protection | fonctions cibles signalées | partenaires et cadre contractuel validés |
| indisponibilité Internet | démonstration locale Wamp | hébergement redondé et supervision |

