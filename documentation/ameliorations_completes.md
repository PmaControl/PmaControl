# Liste Complete des Ameliorations de PmaControl

- Genere le: 2026-03-12 01:42:25
- Objectif: backlog d'amelioration transverse, couvrant architecture, securite, tests, DX, performance et gouvernance

## 1. Architecture et code

1. Decouper les gros controleurs (`Cleaner`, `Schema`, `Aspirateur`, `Backup`, `Dot3`) en services orientés domaine.
2. Sortir la logique metier des actions web vers des classes applicatives testables.
3. Introduire des DTO pour les entrees API, formulaires et jobs.
4. Remplacer les acces directs aux superglobales par une couche request explicite.
5. Formaliser les contrats de sortie HTML/JSON/fichier.
6. Unifier le style de code et les conventions de nommage.
7. Supprimer le code mort, les commentaires historiques et les chemins de debug temporaires.
8. Centraliser la construction d'URLs et de routes.
9. Introduire des exceptions metier dediees par domaine.
10. Documenter explicitement les dependances entre controleurs et bibliotheques.

## 2. Base de donnees et acces SQL

11. Remplacer toute interpolation SQL par requetes preparees.
12. Introduire un repository layer pour les entites critiques.
13. Normaliser les transactions dans les parcours d'ecriture.
14. Ajouter des contraintes BD manquantes la ou le metier les suppose deja.
15. Distinguer clairement referentiel, time series, caches et journaux.
16. Rationaliser les tables `ts_*` et documenter leur retention.
17. Ajouter des index verifies par usage sur les tables de topologie, backup et monitoring.
18. Mettre en place une strategie de migrations coherentement versionnee.
19. Mesurer et limiter la croissance des tables system-versioned.
20. Mieux separer les ecritures techniques des donnees de gouvernance.

## 3. Securite

21. Ajouter une protection CSRF globale.
22. Renforcer la validation des entrees route, GET et POST.
23. Centraliser les checks d'autorisation metier.
24. Isoler les appels shell et interdire la concatenation brute.
25. Basculer les secrets vers un vault ou un stockage externe.
26. Reduire les privileges des comptes MySQL utilises par l'application.
27. Segmenter les cles SSH par cas d'usage.
28. Journaliser toutes les operations sensibles avec acteur, cible et resultat.
29. Filtrer les logs pour eviter la fuite de secrets et chemins sensibles.
30. Revoir l'exposition des endpoints de telechargement et d'import.

## 4. Testabilite et qualite

31. Ajouter des tests unitaires sur les services extraits des gros controleurs.
32. Ajouter des tests d'integration sur API, backup, restore et topologie.
33. Mettre en place des fixtures SQL minimales et reutilisables.
34. Ajouter une base de tests dediee ou un environnement ephemeral.
35. Introduire un pipeline CI pour lint, phpunit et verification doc.
36. Ajouter une analyse statique PHPStan/Psalm graduellement.
37. Couvrir les parcours d'erreur et d'autorisation.
38. Tester explicitement les workflows CLI.
39. Ajouter des snapshots de graphes et de documents generes.
40. Mettre en place une strategie de non-regression sur les exports.

## 5. Performance et exploitation

41. Identifier les requetes les plus couteuses des ecrans lourds.
42. Mettre en cache les vues purement analytiques.
43. Deplacer les taches longues hors requete web.
44. Industrialiser la gestion des fichiers pivots, locks et md5.
45. Documenter les timeouts reseau par integration.
46. Ajouter des garde-fous de concurrence sur workers et cleaner.
47. Rendre observable la file de jobs et les echecs techniques.
48. Ajouter des budgets de retention sur logs, archives et historiques.
49. Segreguer les volumes filesystem temporaires et definitifs.
50. Prevoir une politique de reprise sur incident pour backup et collecte.

## 6. UX operateur et documentation

51. Uniformiser les messages d'erreur et de succes.
52. Ajouter une doc fonctionnelle par domaine, pas seulement par classe.
53. Expliquer dans l'UI les impacts des operations destructives.
54. Ajouter un guide operateur pour backup, cleaner, schema, cluster.
55. Generer un catalogue de routes/action par controleur.
56. Ajouter un glossaire metier des objets `mysql_*`, `ts_*`, `dot3_*`, `backup_*`.
57. Documenter les prerequis systeme exacts par module.
58. Ajouter des exemples JSON/cURL pour toutes les integrations.
59. Produire une matrice ACL lisible par role.
60. Documenter les parcours d'onboarding et de support.

## 7. Modernisation technique

61. Introduire progressivement des types retour PHP natifs.
62. Eliminer les `var` et proprietes dynamiques legacy.
63. Ajouter `strict_types=1` la ou c'est viable.
64. Remplacer les utilitaires statiques les plus couplants par des services injectes.
65. Isoler la couche Glial legacy derriere des adapters.
66. Reduire le nombre de points d'entree qui ecrivent en base depuis le web.
67. Factoriser les wrappers HTTP/SSH/SQL.
68. Ajouter une strategy de compatibilite PHP documentee.
69. Standardiser le packaging des scripts CLI.
70. Simplifier la relation entre docs generees et docs hand-written.

## 8. Ordre recommande de chantier

### Lot 1

- SQL, CSRF, secret management, shell hardening

### Lot 2

- extraction services sur `Api`, `Server`, `Client`, `Environment`, `Tag`, `Alias`, `StorageArea`

### Lot 3

- refonte par domaine des blocs lourds `Backup`, `Cleaner`, `Aspirateur`, `Schema`, `Dot3`

### Lot 4

- CI/CD, static analysis, test data management, observabilite

## 9. Conclusion

La liste d'ameliorations est volontairement exhaustive et structurante. Le projet n'a pas besoin d'une simple couche cosmetique; il a besoin d'une mise a niveau par domaines, en commencant par la securite et les gros noeuds de complexite, puis en consolidant la testabilite et la gouvernance documentaire.
