---
title: Using VS Code and Docker to Debug MySQL Crashes
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-vs-code-and-docker-to-debug-mysql-crashes/
  post_id: 29247
source_author:
  name: Roberto De Bem
  slug: roberto-garciadebem
  url: https://www.percona.com/blog/author/roberto-garciadebem/
  website: ''
published_at: '2025-02-26T13:20:41'
published_at_gmt: '2025-02-26T13:20:41'
modified_at: '2026-03-26T20:25:43'
modified_at_gmt: '2026-03-26T20:25:43'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- Debugging
- Docker
- GitHub
- MySQL
- mysql-and-variants
- Source Code
tag_slugs:
- debugging
- docker
- github
- mysql
- mysql-and-variants
- source-code
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/VS-Code-and-Docker-to-Debug-MySQL-Crashes.jpg
image_count: 8
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using VS Code and Docker to Debug MySQL Crashes

Source: [Percona Blog](https://www.percona.com/blog/using-vs-code-and-docker-to-debug-mysql-crashes/)

Auteur source: [Roberto De Bem](https://www.percona.com/blog/author/roberto-garciadebem/)

Publication: 2025-02-26T13:20:41

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Typically, we receive customer tickets regarding crashes or bugs, where we request a core dump to analyze and identify the root cause or understand the unexpected behavior. To read the core dumps, we also request the linked libraries used by the server’s MySQL. However, there’s a more efficient way to achieve our goal: by using … Continued

## Structure detectee

- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Using VS Code and Docker to Debug MySQL Crashes](https://www.percona.com/wp-content/uploads/2026/03/VS-Code-and-Docker-to-Debug-MySQL-Crashes.jpg)
- content / image: [Visual Studio](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-12-28-at-18.35.41.png)
- content / image: [Tunnels/SSH or Dev Containers](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-12-28-at-18.35.48.png)
- content / image: [Screenshot-2024-12-28-at-18.36.44.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-12-28-at-18.36.44.png)
- content / image: [Screenshot-2024-12-28-at-18.40.57-276x300.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-12-28-at-18.40.57-276x300.png)
- content / image: [JSON configuration file](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-12-31-at-11.20.21.png)
- content / image: [examine the core dump](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-12-31-at-11.21.50-scaled.png)
- content / image: [mysql-performance-tuning-1.png](https://www.percona.com/wp-content/uploads/2026/03/mysql-performance-tuning-1.png)
