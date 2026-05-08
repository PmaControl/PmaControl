---
title: An argument for not using mysqldump
source:
  name: Percona Blog
  url: https://www.percona.com/blog/an-argument-for-not-using-mysqldump-in-production/
  post_id: 2497
source_author:
  name: Morgan Tocker
  slug: morgan
  url: https://www.percona.com/blog/author/morgan/
  website: http://www.percona.com/
published_at: '2010-11-08T17:34:38'
published_at_gmt: '2010-11-08T17:34:38'
modified_at: '2026-04-28T21:19:32'
modified_at_gmt: '2026-04-28T21:19:32'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- XtraBackup
matched_filters:
- category:mysql:83
- search:xtrabackup
- tag:xtrabackup:153
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- backup
- Backups
- mysqldump
- Recovery
- Tips
- xtrabackup
tag_slugs:
- backup
- backups
- mysqldump
- recovery
- tips
- xtrabackup
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# An argument for not using mysqldump

Source: [Percona Blog](https://www.percona.com/blog/an-argument-for-not-using-mysqldump-in-production/)

Auteur source: [Morgan Tocker](https://www.percona.com/blog/author/morgan/)

Publication: 2010-11-08T17:34:38

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I have a 5G mysqldump which takes 30 minutes to restore from backup.Â That means that when the database reaches 50G, it should take 30×10=5 hours to restore.Â Right?Â Wrong.

## Auteur source

Morgan is a former Percona employee. He was the Director of Training at Percona. He was formerly a Technical Instructor for MySQL and Sun Microsystems. He has also previously worked in the MySQL Support Team, and provided DRBD support.
