<?php
declare(strict_types=1);

$commonSections = [
    [
        'title' => 'Passer du conseil à une décision vérifiable',
        'paragraphs' => [
            'Une décision fiable ne repose pas uniquement sur une marque ou sur une remise affichée. Il faut confronter le prix, la fréquence d’utilisation, l’état réel, les accessoires inclus et le coût total du projet. Le [[simulateur Focal-Shift|simulator.php]] donne une première fourchette, puis les catalogues permettent de comparer des offres réellement disponibles. Cette démarche évite de surdimensionner le matériel et rend le choix compréhensible pour un débutant comme pour un professionnel.',
            'Pour un besoin ponctuel, consultez les [[équipements disponibles à la location|catalogue-location.php]] et vérifiez le calendrier, l’assurance ainsi que les conditions de restitution. Pour un usage récurrent, le [[catalogue des produits à vendre|catalogue-vente.php]] permet d’étudier une acquisition durable. Chaque annonce suit un seul parcours : un produit proposé à la vente n’est pas louable simultanément, ce qui évite les ambiguïtés de prix et de disponibilité.',
        ],
    ],
    [
        'title' => 'Sécuriser le parcours avant et après la transaction',
        'paragraphs' => [
            'Avant de confirmer, relisez la description, contrôlez les photos, identifiez les défauts déclarés et posez vos questions dans la messagerie. Pour une location, l’état initial doit être documenté avec le numéro de série et la liste des accessoires. Au retour, de nouvelles photographies permettent une comparaison factuelle. Cette continuité protège le locataire comme le bailleur et donne davantage de valeur aux profils vérifiés.',
            'La lecture peut se poursuivre avec le [[guide consacré à la location sécurisée|article.php?slug=louer-materiel-audiovisuel-securite]], qui explique le contrôle préalable et la restitution, ou avec la [[checklist avant, pendant et après|article.php?slug=guide-location]], pensée pour être utilisée le jour de la remise. Ces liens ne sont pas ajoutés à part : ils prolongent directement les questions abordées dans l’article et permettent de passer naturellement de l’information à l’action.',
        ],
    ],
];

$articles = [
    'guide-location-materiel-video-2026' => [
        'type' => 'Guide location',
        'image' => 'images/catalog/video.webp',
        'image_alt' => 'Caméra vidéo professionnelle préparée pour un tournage',
        'category_id' => 3,
        'title' => 'Guide 2026 : réussir la location de matériel vidéo pour un tournage',
        'lead' => 'Du choix du kit à la restitution : une méthode concrète pour louer sans mauvaise surprise.',
        'sections' => [
            ['title' => 'Partir du projet, pas de la fiche technique', 'paragraphs' => [
                'Commencez par décrire le tournage : intérieur ou extérieur, durée des plans, taille de l’équipe, mobilité, niveau de lumière et format de livraison. Une caméra très performante n’apporte rien si elle impose des cartes, une alimentation ou un ordinateur incompatibles avec votre organisation. Pour construire un premier ensemble cohérent, le [[guide du matériel vidéo pour débuter|article.php?slug=materiel-video-debutant-2026]] aide à hiérarchiser image, son, stabilité et autonomie.',
                'Une interview demande surtout un son propre, une optique adaptée au recul et une alimentation stable. Un événement privilégie l’autofocus et l’endurance, tandis qu’un clip peut justifier un format plus lourd et davantage de latitude en postproduction. Le [[catalogue vidéo en location|catalogue-location.php?category=3]] sert ensuite à vérifier quels produits correspondent réellement au scénario retenu.',
            ]],
            ['title' => 'Construire un kit complet et compatible', 'paragraphs' => [
                'Le boîtier n’est qu’un élément du système. Vérifiez la monture des optiques, la vitesse des cartes, les connectiques du microphone, la capacité des batteries, le poids supporté par le trépied et la présence des câbles. Prévoyez aussi une solution de sauvegarde et un jeu de consommables. Cette vérification évite qu’un accessoire peu coûteux bloque un équipement premium le jour du tournage.',
                'Si vous hésitez entre plusieurs configurations, l’[[interview d’un créateur indépendant|article.php?slug=interview-createur]] montre pourquoi un kit plus réduit, maîtrisé et testé produit souvent de meilleurs résultats qu’une accumulation de références. Faites un essai d’enregistrement dans le format final, contrôlez quelques fichiers sur ordinateur et chronométrez l’autonomie réelle avant de quitter le point de remise.',
            ], 'list' => ['Boîtier et optique adaptés au cadrage', 'Batteries, chargeur et cartes compatibles', 'Microphone, casque et câbles', 'Trépied ou stabilisateur correctement dimensionné']],
            ['title' => 'Contrôler le prix, les preuves et le retour', 'paragraphs' => [
                'Le prix journalier doit être lu avec la durée, la livraison, l’assurance et les éventuels accessoires. Une offre apparemment moins chère peut devenir plus coûteuse si elle exige plusieurs compléments. Utilisez le [[simulateur de prix|simulator.php]] pour situer la proposition, puis examinez le détail de l’annonce. Sur les équipements premium, un tarif réaliste se situe souvent entre quarante et cent vingt euros par jour selon la catégorie et la valeur.',
                'Avant le départ, photographiez toutes les faces, les écrans, les connectiques, le numéro de série et les accessoires. À la fin, reproduisez les mêmes vues avant l’envoi ou la remise. La [[checklist de restitution|article.php?slug=guide-location]] détaille cette séquence et les réactions à adopter si une différence apparaît. Des preuves partagées valent mieux qu’un souvenir imprécis lorsque plusieurs personnes ont manipulé le kit.',
            ]],
        ],
        'faq' => [['Quand réserver ?','Une semaine est confortable pour un kit courant ; réservez davantage pour une période chargée.'],['Faut-il assurer le matériel ?','Oui pour un équipement de valeur : vérifiez plafonds, franchise et exclusions avant le paiement.'],['Que faire si un accessoire manque ?','Faites corriger la liste avant la remise et conservez les échanges dans la messagerie.']],
    ],
    'louer-materiel-audiovisuel-securite' => [
        'type' => 'Guide propriétaires',
        'image' => 'images/catalog/camera.webp',
        'image_alt' => 'Appareil photo vérifié avant sa mise en location',
        'category_id' => 1,
        'title' => 'Propriétaires : louer son matériel audiovisuel en toute sécurité',
        'lead' => 'Fixer le bon prix, documenter l’état et sélectionner un locataire sans transformer la location en parcours anxiogène.',
        'sections' => [
            ['title' => 'Choisir clairement entre vendre et louer', 'paragraphs' => [
                'La première décision concerne l’usage futur. La vente convient à un appareil dormant, devenu secondaire ou dont la valeur doit être récupérée rapidement. La location correspond à un produit fiable que le propriétaire souhaite conserver tout en amortissant son coût. Sur Focal-Shift, une annonce ne mélange jamais les deux statuts : elle rejoint soit le [[catalogue de location|catalogue-location.php]], soit le [[catalogue de vente|catalogue-vente.php]].',
                'Évaluez le nombre de jours louables, le temps consacré aux remises, l’usure prévisible et la valeur résiduelle. L’article [[réparer avant de remplacer|article.php?slug=reparation-circulaire]] aide aussi à déterminer si une maintenance documentée peut prolonger la vie du produit avant sa publication.',
            ]],
            ['title' => 'Fixer un prix crédible et contrôlé', 'paragraphs' => [
                'Un tarif cohérent dépend de la valeur actuelle, de l’âge, de l’état, des accessoires et de la demande. Le [[simulateur Focal-Shift|simulator.php]] calcule une estimation puis autorise un ajustement dans une limite de vingt pour cent. Cette marge respecte la liberté du propriétaire tout en empêchant un prix manifestement déconnecté du marché. La jauge rend immédiatement visibles le minimum, le maximum et le montant retenu.',
                'Pour un boîtier premium, un prix trop faible fragilise la rentabilité et peut diminuer la perception de qualité. Un prix excessif détourne les locataires vers une autre référence. Expliquez la valeur : état contrôlé, batteries supplémentaires, livraison, support ou accessoires. Le [[test du Nikon Z8|article.php?slug=test-nikon-z8]] fournit un exemple de critères techniques qui peuvent justifier une offre.',
            ]],
            ['title' => 'Vérifier le produit et organiser la restitution', 'paragraphs' => [
                'Avant la publication, le produit destiné à la location passe par une vérification de son état et de son fonctionnement. Le contrôle couvre les commandes, le capteur, les connectiques, l’enregistrement, les accessoires et le numéro de série. Il permet d’attribuer un score explicable et de confirmer le tarif. Les défauts esthétiques ne sont pas cachés : ils sont photographiés pour éviter toute confusion.',
                'À la fin de la période, le locataire photographie l’équipement avant l’envoi ou la restitution. Le propriétaire compare ces éléments avec l’état initial puis clôture la transaction. La [[checklist complète de location|article.php?slug=guide-location]] transforme cette règle en étapes simples. En cas d’incident, les échanges, photographies et dates constituent un dossier beaucoup plus solide qu’une déclaration tardive.',
            ]],
        ],
        'faq' => [['Puis-je vendre et louer le même produit ?','Non, chaque annonce suit un parcours unique afin d’éviter les conflits.'],['Qui fixe le prix ?','Le propriétaire choisit dans la fourchette du simulateur, puis la vérification confirme le tarif.'],['Comment prouver l’état initial ?','Avec des photos datées, le numéro de série, la liste des accessoires et le rapport de contrôle.']],
    ],
    'materiel-video-debutant-2026' => [
        'type' => 'Guide débutant',
        'image' => 'images/catalog/camera.webp',
        'image_alt' => 'Kit vidéo simple composé pour un créateur débutant',
        'category_id' => 1,
        'title' => 'Quel matériel vidéo choisir pour débuter ? Guide complet 2026',
        'lead' => 'Un premier kit équilibré, évolutif et adapté au projet réel plutôt qu’une accumulation d’équipements.',
        'sections' => [
            ['title' => 'Définir ce que vous allez réellement filmer', 'paragraphs' => [
                'Un premier achat doit répondre à un usage identifiable : interview, contenu social, événement, documentaire ou court-métrage. Notez le lieu, le temps d’installation, la nécessité de travailler seul et le canal de diffusion. Cette description réduit immédiatement le nombre d’options. Le [[guide de location pour un tournage|article.php?slug=guide-location-materiel-video-2026]] propose une méthode plus détaillée pour transformer le projet en liste de matériel.',
                'Ne choisissez pas uniquement sur la résolution. L’ergonomie, l’autofocus, la stabilisation, la lisibilité de l’écran et la simplicité du transfert peuvent avoir plus d’impact sur vos résultats. Parcourez les [[appareils disponibles à la location|catalogue-location.php?category=1]] pour tester une configuration avant de l’acheter définitivement.',
            ]],
            ['title' => 'Répartir le budget intelligemment', 'paragraphs' => [
                'Le son médiocre est souvent plus gênant qu’une image légèrement moins détaillée. Réservez donc une part du budget à un microphone, un casque, deux batteries, des cartes adaptées et un trépied stable. Une optique polyvalente et lumineuse peut servir plus longtemps qu’un boîtier remplacé rapidement. Le [[simulateur|simulator.php]] permet de comparer la logique d’une location ponctuelle avec celle d’une acquisition.',
                'Pour découvrir la logique d’un professionnel, lisez [[dans le sac d’un créateur|article.php?slug=interview-createur]]. L’entretien montre comment réduire le nombre d’éléments et apprendre à exploiter chaque pièce du kit. Une configuration simple accélère la préparation, limite les oublis et facilite le diagnostic lorsqu’un résultat ne correspond pas aux attentes.',
            ]],
            ['title' => 'Tester avant d’acheter et faire évoluer le kit', 'paragraphs' => [
                'Louez le matériel sur un projet réel, pas seulement pour quelques minutes. Vérifiez le poids après une heure, la confiance dans l’autofocus, la durée des batteries, le volume des fichiers et le temps nécessaire au montage. Si l’usage devient fréquent et prévisible, consultez les [[appareils proposés à la vente|catalogue-vente.php?category=1]] et comparez le coût d’achat au cumul des locations.',
                'Faites évoluer le kit à partir d’une limite constatée. Achetez une seconde optique si le cadrage vous bloque, une lumière si les intérieurs sont trop sombres ou un enregistreur si le son devient central. Cette progression évite les achats impulsifs. Pour un modèle haut de gamme, le [[test du Nikon Z8|article.php?slug=test-nikon-z8]] illustre les contrôles à réaliser avant de réserver ou d’investir.',
            ]],
        ],
        'faq' => [['Faut-il filmer en 4K ?','La 4K donne une marge de recadrage, mais augmente le stockage et la puissance nécessaires.'],['Quel budget prévoir ?','Commencez par un ensemble cohérent et louez les éléments coûteux avant l’achat.'],['Quelle première optique choisir ?','Une focale polyvalente et lumineuse est souvent plus utile que plusieurs objectifs spécialisés.']],
    ],
    'interview-createur' => [
        'type' => 'Interview',
        'image' => 'images/catalog/video.webp',
        'image_alt' => 'Vidéaste préparant un sac de tournage',
        'category_id' => 3,
        'title' => 'Dans le sac d’un créateur : choisir moins, tourner mieux',
        'lead' => 'Un vidéaste indépendant partage sa méthode pour construire un kit fiable et adapté au terrain.',
        'video' => 'https://www.youtube.com/watch?v=ekKooxaeAS8',
        'sections' => [
            ['title' => 'Un sac construit autour des missions', 'paragraphs' => [
                'Le créateur interrogé ne commence jamais par demander quel est le meilleur boîtier. Il observe la mission, le nombre de personnes, les déplacements, la lumière et le temps disponible. Pour une interview légère, il retient un appareil fiable, une optique polyvalente, un micro, un casque et deux batteries. Cette discipline rejoint le [[guide vidéo pour débuter|article.php?slug=materiel-video-debutant-2026]], qui recommande de construire le kit autour d’un usage réel.',
                'Chaque objet doit justifier son poids. Une seconde caméra est utile si elle sécurise une prise non reproductible ; elle devient superflue si elle ralentit un tournage seul. Les [[caméras proposées à la location|catalogue-location.php?category=3]] permettent justement d’ajouter ponctuellement une fonction sans immobiliser plusieurs milliers d’euros.',
            ]],
            ['title' => 'Tester avant d’investir', 'paragraphs' => [
                'Une fiche technique ne révèle ni la fatigue liée au poids, ni la vitesse des menus, ni la confiance dans l’autofocus. Le vidéaste loue donc un appareil sur une mission contrôlée avant de décider. Il réalise quelques plans représentatifs, transfère les fichiers et reproduit son montage habituel. Le [[guide complet de location|article.php?slug=guide-location-materiel-video-2026]] détaille les vérifications à effectuer avant le départ.',
                'Le coût est analysé sur plusieurs mois. Si trois locations suffisent dans l’année, l’achat n’est pas toujours raisonnable. Si le matériel devient indispensable chaque semaine, le [[catalogue de vente|catalogue-vente.php]] peut offrir une solution plus durable. Le [[simulateur de prix|simulator.php]] constitue alors un point de comparaison, pas une promesse automatique.',
            ]],
            ['title' => 'Préparer la panne avant qu’elle arrive', 'paragraphs' => [
                'Le sac comprend peu de doublons, mais protège les fonctions critiques : deux cartes, deux batteries, un câble de secours et une solution audio minimale. Avant chaque mission, le créateur formate les supports, contrôle les connectiques et enregistre une séquence complète. Cette routine simple évite davantage d’incidents qu’un équipement très coûteux mal préparé.',
                'Au retour, il nettoie, recharge et documente immédiatement le matériel. Pour une location, il photographie l’état avant la restitution et compare les accessoires avec la liste de départ. La [[checklist de remise et de retour|article.php?slug=guide-location]] peut être conservée sur téléphone pour ne rien oublier lorsque la fatigue du tournage réduit l’attention.',
            ]],
        ],
        'faq' => [['Quel objet est toujours dans son sac ?','Un casque fiable, car le contrôle du son ne peut pas attendre le montage.'],['Pourquoi louer ?','Pour tester en situation réelle ou répondre à un besoin rare sans immobiliser le budget.'],['Faut-il prévoir un secours ?','Oui pour les fonctions critiques : alimentation, stockage, câble et captation audio minimale.']],
    ],
    'test-nikon-z8' => [
        'type' => 'Test produit',
        'image' => 'images/catalog/camera.webp',
        'image_alt' => 'Nikon Z8 contrôlé avant une location',
        'category_id' => 1,
        'title' => 'Nikon Z8 : les points à vérifier avant une location',
        'lead' => 'Autofocus, vidéo, autonomie et ergonomie : une grille de lecture orientée usage.',
        'video' => 'https://www.youtube.com/watch?v=Rn5CatbKrkM',
        'sections' => [
            ['title' => 'Un appareil puissant qui doit servir le projet', 'paragraphs' => [
                'Le Nikon Z8 réunit haute définition, autofocus avancé et fonctions vidéo professionnelles. Cette polyvalence ne signifie pas qu’il convient automatiquement à tous les tournages. Pour une interview fixe, un modèle plus simple peut produire le même résultat avec moins de stockage. Le [[guide du premier kit vidéo|article.php?slug=materiel-video-debutant-2026]] aide à vérifier si la sophistication répond à un besoin concret.',
                'Son intérêt apparaît lorsque la résolution, le recadrage, le suivi du sujet ou les formats vidéo deviennent déterminants. Comparez son tarif dans les [[appareils disponibles à la location|catalogue-location.php?category=1]] puis utilisez le [[simulateur|simulator.php]] afin de replacer l’offre dans une fourchette cohérente.',
            ]],
            ['title' => 'Les contrôles indispensables avant la remise', 'paragraphs' => [
                'Inspectez le capteur à petite ouverture, testez les deux logements de cartes, les connectiques, les boutons, la stabilisation et l’autofocus. Vérifiez la version du firmware et réalisez un enregistrement dans le format réellement prévu. Certains modes exigent des supports rapides et produisent des fichiers volumineux ; leur coût doit entrer dans le budget du tournage.',
                'Demandez le nombre de batteries, le chargeur fourni, la cage éventuelle et les câbles. Pour une journée longue, prévoyez une alimentation adaptée. Le [[guide de location vidéo 2026|article.php?slug=guide-location-materiel-video-2026]] complète ce contrôle avec le son, la stabilité, la sauvegarde et l’organisation du retour.',
            ]],
            ['title' => 'Évaluer l’ergonomie et le coût total', 'paragraphs' => [
                'Un essai doit simuler les gestes de la mission : mise au point, changement de réglage, lecture des niveaux, déplacement et transfert des fichiers. Un appareil peut être excellent tout en restant trop lourd ou trop complexe pour une équipe réduite. L’[[interview du créateur|article.php?slug=interview-createur]] explique pourquoi la maîtrise du kit compte davantage que la longueur de sa fiche technique.',
                'Additionnez enfin location, optiques, cartes, livraison et assurance. Si les missions deviennent régulières, consultez les [[produits photo proposés à la vente|catalogue-vente.php?category=1]]. Une acquisition n’est pertinente que si son utilisation, son entretien et sa valeur résiduelle compensent l’investissement initial.',
            ]],
        ],
        'faq' => [['À qui s’adresse le Nikon Z8 ?','Aux projets qui exploitent réellement sa résolution, son autofocus ou ses formats vidéo avancés.'],['Que tester en priorité ?','Le capteur, les cartes, les connectiques, la stabilisation, l’autofocus et l’enregistrement prévu.'],['Combien de batteries prévoir ?','Au moins deux pour un usage court, davantage pour une journée vidéo sans alimentation externe.']],
    ],
    'guide-location' => [
        'type' => 'Guide pratique',
        'image' => 'images/catalog/lens.webp',
        'image_alt' => 'Objectif photographié pour documenter son état',
        'category_id' => 2,
        'title' => 'Location : la checklist avant, pendant et après',
        'lead' => 'Une méthode simple pour sécuriser la remise et le retour du matériel.',
        'sections' => [
            ['title' => 'Avant : confirmer l’offre et documenter l’état', 'paragraphs' => [
                'Vérifiez l’identité, les dates, le prix total, l’assurance, la livraison et la liste des accessoires. Lisez la description jusqu’aux défauts signalés et posez vos questions avant le paiement. Pour choisir la bonne référence, commencez par le [[guide de location vidéo|article.php?slug=guide-location-materiel-video-2026]] ou parcourez directement les [[produits disponibles|catalogue-location.php]].',
                'Photographiez les faces, les écrans, la lentille, les connectiques, le numéro de série et chaque accessoire. Allumez l’appareil et testez les fonctions liées au projet. Une image datée et partagée est plus utile qu’une formule générale comme « bon état ». Le propriétaire et le locataire doivent partir avec la même compréhension du produit.',
            ]],
            ['title' => 'Pendant : protéger, signaler et conserver les preuves', 'paragraphs' => [
                'Transportez le matériel dans une protection adaptée et respectez les limites annoncées. N’attendez pas le retour pour signaler un choc, une panne ou un accessoire absent. Un message envoyé immédiatement permet de chercher une solution et crée une chronologie claire. Pour les équipements sensibles, le [[guide des propriétaires|article.php?slug=louer-materiel-audiovisuel-securite]] explique comment préparer une remise rassurante.',
                'Conservez les emballages, évitez les interventions non autorisées et sauvegardez les fichiers sans modifier la configuration plus que nécessaire. Si une réparation semble utile, ne la lancez pas seul : consultez d’abord l’article [[réparer avant de remplacer|article.php?slug=reparation-circulaire]] et échangez avec le propriétaire.',
            ]],
            ['title' => 'Après : reproduire les photos et clôturer', 'paragraphs' => [
                'Avant l’envoi ou la remise, nettoyez uniquement avec des outils adaptés, replacez les protections et reprenez les mêmes angles qu’au départ. Comptez les accessoires un à un et photographiez le colis si le retour se fait par transporteur. Cette étape doit être réalisée avant que le matériel quitte à nouveau votre contrôle.',
                'Le propriétaire compare l’état, confirme la réception puis clôture la transaction. Une différence doit être décrite précisément, sans accusation immédiate. Pour préparer un prochain besoin, le [[simulateur Focal-Shift|simulator.php]] permet d’estimer le budget et les catalogues distinguent clairement les [[offres de location|catalogue-location.php]] des [[offres de vente|catalogue-vente.php]].',
            ]],
        ],
        'faq' => [['Quelles photos prendre ?','Toutes les faces, les zones fragiles, le numéro de série et les accessoires.'],['Quand signaler un incident ?','Immédiatement dans la messagerie, sans attendre la restitution.'],['Qui contrôle le retour ?','Le propriétaire compare les preuves finales avec l’état initial avant de clôturer.']],
    ],
    'reparation-circulaire' => [
        'type' => 'Économie circulaire',
        'image' => 'images/catalog/accessory.webp',
        'image_alt' => 'Accessoires et outils utilisés pour diagnostiquer un appareil',
        'category_id' => 4,
        'title' => 'Réparer avant de remplacer : décider avec méthode',
        'lead' => 'Coût, disponibilité des pièces et fiabilité guident une décision documentée.',
        'sections' => [
            ['title' => 'Distinguer l’usure, le défaut et la panne', 'paragraphs' => [
                'Une trace esthétique n’a pas le même impact qu’un connecteur instable ou qu’une panne intermittente. Commencez par décrire le symptôme, les conditions d’apparition et les fonctions touchées. Photographiez l’appareil et conservez les messages d’erreur. Pour un produit loué, appliquez d’abord la [[checklist de restitution|article.php?slug=guide-location]] et informez le propriétaire avant toute intervention.',
                'Le diagnostic doit être confié à une personne compétente lorsque l’ouverture présente un risque électrique, mécanique ou optique. Une intervention improvisée peut aggraver le dommage et supprimer une garantie. La documentation de la panne permet au réparateur d’éviter des recherches inutiles et améliore la qualité du devis.',
            ]],
            ['title' => 'Comparer le coût avec la valeur d’usage', 'paragraphs' => [
                'Additionnez le diagnostic, les pièces, la main-d’œuvre, le transport et l’indisponibilité. Comparez ce total à la valeur actuelle, pas au prix payé plusieurs années auparavant. Le [[simulateur|simulator.php]] fournit un ordre de grandeur de marché ; le [[catalogue de vente|catalogue-vente.php]] permet d’observer des alternatives disponibles. Une réparation reste pertinente si elle rend l’équipement fiable pour une durée suffisante.',
                'La décision ne doit pas être uniquement financière. Une réparation réduit les déchets, conserve des accessoires compatibles et évite un nouvel apprentissage. À l’inverse, une pièce introuvable ou une panne répétitive peut rendre le remplacement plus raisonnable. Dans ce cas, le produit défectueux doit être orienté vers une filière adaptée et non présenté comme fonctionnel.',
            ]],
            ['title' => 'Valoriser une réparation documentée', 'paragraphs' => [
                'Conservez la facture, la date, les pièces remplacées et les tests réalisés. Ajoutez ces informations à l’annonce sans promettre plus que le rapport. Un historique transparent peut rassurer davantage qu’un appareil dont on ignore l’entretien. Le [[guide des propriétaires|article.php?slug=louer-materiel-audiovisuel-securite]] explique comment intégrer cette preuve au contrôle précédant une mise en location.',
                'Après la réparation, testez l’équipement sur un usage réel et surveillez les fonctions concernées. Si le produit redevient fiable, choisissez clairement entre la [[mise en location|catalogue-location.php]] et la vente. L’objectif de l’économie circulaire n’est pas de conserver tout objet à n’importe quel prix, mais de prolonger chaque usage lorsque la sécurité, la qualité et la confiance restent démontrables.',
            ]],
        ],
        'faq' => [['Quand demander un diagnostic ?','Dès qu’un défaut fonctionnel est reproductible ou qu’un risque existe.'],['Une réparation augmente-t-elle la valeur ?','Elle peut rassurer si elle est professionnelle, récente, documentée et testée.'],['Quand remplacer ?','Lorsque la réparation est disproportionnée, les pièces indisponibles ou la fiabilité insuffisante.']],
    ],
];

foreach ($articles as &$article) {
    $article['sections'] = array_merge($article['sections'], $commonSections);
}
unset($article);

return $articles;
