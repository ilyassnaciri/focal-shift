# Plan de test Focal-Shift

## Tests critiques avant code freeze

| ID | Parcours | Action | Résultat attendu |
|---|---|---|---|
| T01 | Installation | importer `focal_shift.sql` | 6 tables et données de démo |
| T02 | Accueil | ouvrir `/focal-shift/` | aucun message d'erreur, CTA visibles |
| T03 | Catalogue | filtrer Sony + location | uniquement les Sony louables |
| T04 | Catalogue | saisir un prix max | aucun résultat au-dessus du seuil du mode choisi |
| T05 | Fiche | ouvrir un équipement | prix, propriétaire, score et limites visibles |
| T06 | Dépôt | envoyer vide | publication bloquée, erreurs compréhensibles |
| T07 | Dépôt | image >5 Mo ou mauvais type | upload refusé |
| T08 | Dépôt | annonce valide | redirection fiche + présence catalogue |
| T09 | Simulateur exact | Sony / A7 IV | référence exacte et résultats numériques |
| T10 | Simulateur fallback | modèle inconnu d'une catégorie renseignée | moyenne catégorie signalée |
| T11 | Consentement | tout refuser | aucun événement dans `focalDataLayer` |
| T12 | Consentement | autoriser puis utiliser simulateur | événement local `simulator_complete` |
| T13 | Clavier | Tab depuis le haut | lien d'évitement puis navigation logique |
| T14 | Mobile | largeur 375 px | menu replié, aucune coupure horizontale |
| T15 | Sécurité | entrée `<script>` dans description | texte affiché, script non exécuté |

## Go / No-Go démo

**Go** si T01-T10, T13-T15 passent et si une capture vidéo de secours existe.  
**No-Go** si l'import SQL, le dépôt ou le simulateur échoue. Revenir à la dernière archive stable ; ne pas ajouter de fonctionnalité après le code freeze.

