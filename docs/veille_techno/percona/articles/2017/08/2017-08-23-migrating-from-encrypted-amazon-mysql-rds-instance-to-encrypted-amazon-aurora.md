---
title: Migrating Data from an Encrypted Amazon MySQL RDS Instance to an Encrypted Amazon Aurora Instance
source:
  name: Percona Blog
  url: https://www.percona.com/blog/migrating-from-encrypted-amazon-mysql-rds-instance-to-encrypted-amazon-aurora/
  post_id: 17051
source_author:
  name: Sveta Smirnova
  slug: sveta-smirnova
  url: https://www.percona.com/blog/author/sveta-smirnova/
  website: http://www.percona.com/blog/
published_at: '2017-08-23T19:51:00'
published_at_gmt: '2017-08-23T19:51:00'
modified_at: '2026-03-20T21:27:08'
modified_at_gmt: '2026-03-20T21:27:08'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Cloud
- MySQL
category_slugs:
- cloud
- mysql
tags:
- Amazon Aurora
- Amazon RDS
- Amazon’s AWS
- encryption
- migration
- Replication
tag_slugs:
- amazon-aurora
- amazon-rds
- amazons-aws
- encryption
- migration
- replication
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Migrating-Data.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Migrating Data from an Encrypted Amazon MySQL RDS Instance to an Encrypted Amazon Aurora Instance

Source: [Percona Blog](https://www.percona.com/blog/migrating-from-encrypted-amazon-mysql-rds-instance-to-encrypted-amazon-aurora/)

Auteur source: [Sveta Smirnova](https://www.percona.com/blog/author/sveta-smirnova/)

Publication: 2017-08-23T19:51:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll discuss migrating data from encrypted Amazon MySQL RDS to encrypted Amazon Aurora. One of my customers wanted to migrate from an encrypted MySQL RDS instance to an encrypted Aurora instance. They have a pretty large database, therefore using mysqldump or a similar tool was not suitable for them. They also wanted to setup … Continued

## Images et graphiques reperes

- featured / image: [Migrating Data from an Encrypted Amazon MySQL RDS Instance to an Encrypted Amazon Aurora Instance](https://www.percona.com/wp-content/uploads/2026/03/Migrating-Data.png)
- content / image: [Aurora-RR.png](https://www.percona.com/wp-content/uploads/2026/03/Aurora-RR.png)
- content / image: [No-migration.png](https://www.percona.com/wp-content/uploads/2026/03/No-migration.png)

## Auteur source

Sveta joined Percona in 2015. Her main professional interests are problem solving, working with tricky issues, bugs, finding patterns that can solve typical issues quicker and teaching others how to deal with MySQL issues, bugs and gotchas effectively. Before joining Percona Sveta worked as a Support Engineer in the MySQL Bugs Analysis Support Group in MySQL AB-Sun-Oracle. She is the author of the books "MySQL Troubleshooting" and "MySQL Cookbook, 4th Edition".
