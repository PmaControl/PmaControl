---
title: The MySQL Clone Plugin Is Not Your Backup
source:
  name: Percona Blog
  url: https://www.percona.com/blog/the-mysql-clone-plugin-is-not-your-backup/
  post_id: 27518
source_author:
  name: Sveta Smirnova
  slug: sveta-smirnova
  url: https://www.percona.com/blog/author/sveta-smirnova/
  website: http://www.percona.com/blog/
published_at: '2023-09-26T14:03:53'
published_at_gmt: '2023-09-26T14:03:53'
modified_at: '2026-03-26T20:27:11'
modified_at_gmt: '2026-03-26T20:27:11'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-xtrabackup
- search:xtrabackup
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- backup
- MySQL
- MySQL Backup
- mysql-and-variants
tag_slugs:
- backup
- mysql
- mysql-backup
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/blockchain-technology-background-gradient-blue-1.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# The MySQL Clone Plugin Is Not Your Backup

Source: [Percona Blog](https://www.percona.com/blog/the-mysql-clone-plugin-is-not-your-backup/)

Auteur source: [Sveta Smirnova](https://www.percona.com/blog/author/sveta-smirnova/)

Publication: 2023-09-26T14:03:53

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This blog post discusses the limitations of the MySQL Clone plugin. The MySQL clone plugin significantly simplifies the process of replica provisioning. All you need to do is: Ensure that the source server has binary logs enabled Grant appropriate permissions Execute the CLONE INSTANCE command on the recipient This works extremely easily when you provision a … Continued

## Structure detectee

- H2: Checking prerequisites on the replica
- H2: Wiping the data directory
- H2: Copying data from the source server to the replica
- H2: Finalizing data on the replica
- H3: Recovering from error if data is wiped out
- H3: Recovering from error if you used option DATA DIRECTORY
- H2: Conclusion
- H3: MySQL clone plugin resources

## Images et graphiques reperes

- featured / image: [The MySQL Clone Plugin Is Not Your Backup](https://www.percona.com/wp-content/uploads/2026/03/blockchain-technology-background-gradient-blue-1.jpg)

## Auteur source

Sveta joined Percona in 2015. Her main professional interests are problem solving, working with tricky issues, bugs, finding patterns that can solve typical issues quicker and teaching others how to deal with MySQL issues, bugs and gotchas effectively. Before joining Percona Sveta worked as a Support Engineer in the MySQL Bugs Analysis Support Group in MySQL AB-Sun-Oracle. She is the author of the books "MySQL Troubleshooting" and "MySQL Cookbook, 4th Edition".
