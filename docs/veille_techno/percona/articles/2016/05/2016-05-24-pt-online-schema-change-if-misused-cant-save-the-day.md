---
title: pt-online-schema-change (if misused) can’t save the day
source:
  name: Percona Blog
  url: https://www.percona.com/blog/pt-online-schema-change-if-misused-cant-save-the-day/
  post_id: 15166
source_author:
  name: Przemysław Malkowski
  slug: przemek-malkowski
  url: https://www.percona.com/blog/author/przemek-malkowski/
  website: ''
published_at: '2016-05-24T18:27:23'
published_at_gmt: '2016-05-24T18:27:23'
modified_at: '2026-05-05T19:39:35'
modified_at_gmt: '2026-05-05T19:39:35'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- MySQL
- pt-online-schema-change
- Replication
tag_slugs:
- mysql
- pt-online-schema-change
- replication
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-replication-e1480466617340.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# pt-online-schema-change (if misused) can’t save the day

Source: [Percona Blog](https://www.percona.com/blog/pt-online-schema-change-if-misused-cant-save-the-day/)

Auteur source: [Przemysław Malkowski](https://www.percona.com/blog/author/przemek-malkowski/)

Publication: 2016-05-24T18:27:23

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post we’ll discuss pt-online-schema-change, and how to correctly use it. Always use pt-osc? Altering large tables can be still a problematic DBA task, even now after we’ve improved Online DDL features in MySQL 5.6 and 5.7. Some ALTER types are still not online, or sometimes just too expensive to execute on busy … Continued

## Structure detectee

- H2: Always use pt-osc?
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [pt-online-schema-change (if misused) can’t save the day](https://www.percona.com/wp-content/uploads/2026/03/MySQL-replication-e1480466617340.jpg)
- content / image: [MySQL-replication-2-300x225.jpg](https://www.percona.com/wp-content/uploads/2026/03/MySQL-replication-2-300x225.jpg)

## Auteur source

Przemek joined Support Team at Percona in August 2012. Before that he spent over five years working for Wikia.com (Quantcast Top 50) as System Administrator where he was a key person responsible for seamless building up MySQL powered database infrastructure. Besides MySQL he worked on maintaining all other parts of LAMP stack, with main focus on automation, monitoring and backups.
