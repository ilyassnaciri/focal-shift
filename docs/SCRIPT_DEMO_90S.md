# Script de démonstration - 1 min 30

**0:00-0:12 - Problème**  
« Focal-Shift remet en circulation du matériel photo et vidéo premium. Notre différence : la confiance devient une fonctionnalité du produit. »

**0:12-0:30 - Demande**  
Ouvrir le catalogue, filtrer `Sony` et `Location`.  
« Les cartes proviennent de MySQL ; les filtres déclenchent des requêtes PHP préparées. »

**0:30-0:43 - Confiance**  
Ouvrir le Sony A7 IV.  
« Avant toute action, l’utilisateur voit l’identité vérifiée, la réputation, l’état documenté et notre Circular Score, qui n’est pas un score CO₂. »

**0:43-1:07 - Simulateur**  
Saisir `Boîtiers photo / Sony / A7 IV / 2023 / Très bon état`.  
« Le JavaScript appelle une API PHP. Elle interroge le référentiel SQL, applique des coefficients transparents et journalise la simulation. Le résultat compare la vente nette, la location et le nombre de jours nécessaires pour égaler une vente. »

**1:07-1:25 - Dépôt**  
Cliquer sur la mise en circulation et montrer le formulaire.  
« La validation existe côté navigateur et côté serveur ; la photo est contrôlée, l’insertion est transactionnelle et l’annonce apparaît ensuite dans le catalogue. »

**1:25-1:30 - Conclusion**  
« Ce POC prouve toute la chaîne : UX, data, PHP, SQL, règles métier, sécurité, RGPD et accessibilité. »

## Réponse si le jury demande ce qui n'est pas développé

« Nous avons choisi de prouver le coeur de valeur. Le paiement, le KYC, l’assurance et la réservation sont dans l’architecture V1, mais ne sont pas simulés comme opérationnels dans le POC. »
