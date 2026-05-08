---
title: Using CLONE INSTANCE With DATA DIRECTORY on Replicas With Data
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-clone-instance-with-data-directory-on-replicas-with-data/
  post_id: 27514
source_author:
  name: Sveta Smirnova
  slug: sveta-smirnova
  url: https://www.percona.com/blog/author/sveta-smirnova/
  website: http://www.percona.com/blog/
published_at: '2023-10-17T12:03:02'
published_at_gmt: '2023-10-17T12:03:02'
modified_at: '2026-03-26T20:27:04'
modified_at_gmt: '2026-03-26T20:27:04'
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
- backup
- MySQL
- mysql-and-variants
tag_slugs:
- backup
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/CLONE-INSTANCE-with-DATA-DIRECTORY.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using CLONE INSTANCE With DATA DIRECTORY on Replicas With Data

Source: [Percona Blog](https://www.percona.com/blog/using-clone-instance-with-data-directory-on-replicas-with-data/)

Auteur source: [Sveta Smirnova](https://www.percona.com/blog/author/sveta-smirnova/)

Publication: 2023-10-17T12:03:02

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This blog post discusses using the CLONE INSTANCE command with the safety option DATA DIRECTORY when you do not have enough disk space to store two datasets. In my previous blog post on the CLONE INSTANCE command, The MySQL Clone Plugin Is Not Your Backup, I mentioned that using the option DATA DIRECTORY helps to avoid situations where you need to … Continued

## Structure detectee

- H2: Start from scratch
- H2: Keep your existing MySQL schema
- H2: Cloning the instance
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Using CLONE INSTANCE With DATA DIRECTORY on Replicas With Data](https://www.percona.com/wp-content/uploads/2026/03/CLONE-INSTANCE-with-DATA-DIRECTORY.png)

## Auteur source

Sveta joined Percona in 2015. Her main professional interests are problem solving, working with tricky issues, bugs, finding patterns that can solve typical issues quicker and teaching others how to deal with MySQL issues, bugs and gotchas effectively. Before joining Percona Sveta worked as a Support Engineer in the MySQL Bugs Analysis Support Group in MySQL AB-Sun-Oracle. She is the author of the books "MySQL Troubleshooting" and "MySQL Cookbook, 4th Edition".
