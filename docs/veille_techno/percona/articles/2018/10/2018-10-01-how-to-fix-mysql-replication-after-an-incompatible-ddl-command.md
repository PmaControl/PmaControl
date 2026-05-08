---
title: How To Fix MySQL Replication After an Incompatible DDL Command
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-fix-mysql-replication-after-an-incompatible-ddl-command/
  post_id: 19365
source_author:
  name: Jaime Sicam
  slug: jaimesicam
  url: https://www.percona.com/blog/author/jaimesicam/
  website: ''
published_at: '2018-10-01T11:40:37'
published_at_gmt: '2018-10-01T11:40:37'
modified_at: '2026-05-05T19:22:44'
modified_at_gmt: '2026-05-05T19:22:44'
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
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/fix-MySQL-replication-after-incompatible-DDL.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How To Fix MySQL Replication After an Incompatible DDL Command

Source: [Percona Blog](https://www.percona.com/blog/how-to-fix-mysql-replication-after-an-incompatible-ddl-command/)

Auteur source: [Jaime Sicam](https://www.percona.com/blog/author/jaimesicam/)

Publication: 2018-10-01T11:40:37

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL supports replicating to a slave that is one release higher. This allows us to easily upgrade our MySQL setup to a new version, by promoting the slave and pointing the application to it. However, though unsupported, there are times when the MySQL version of slave deployed is one release lower. In this scenario, if … Continued

## Structure detectee

- H3: Fixing non-GTID replication
- H3: GTID replication
- H3: Summary

## Images et graphiques reperes

- featured / image: [How To Fix MySQL Replication After an Incompatible DDL Command](https://www.percona.com/wp-content/uploads/2026/03/fix-MySQL-replication-after-incompatible-DDL.jpg)
- content / image: [fix MySQL replication after incompatible DDL](https://www.percona.com/wp-content/uploads/2026/03/fix-MySQL-replication-after-incompatible-DDL-300x199.jpg)

## Auteur source

Jaime is a Senior Support Engineer at Percona. Prior to joining Percona, Jaime worked as a remote system administrator managing high-traffic websites and consultant for several local companies. He also conducted Linux trainings in several schools. Jaime is based in the Philippines. He enjoys road trips and photography in his spare time.
