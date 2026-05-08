---
title: How to reclaim space in InnoDB when innodb_file_per_table is ON
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-reclaim-space-in-innodb-when-innodb_file_per_table-is-on/
  post_id: 7347
source_author:
  name: Nilnandan Joshi
  slug: nilnandan-joshi-2
  url: https://www.percona.com/blog/author/nilnandan-joshi-2/
  website: ''
published_at: '2013-09-25T05:00:27'
published_at_gmt: '2013-09-25T05:00:27'
modified_at: '2026-04-28T21:56:05'
modified_at_gmt: '2026-04-28T21:56:05'
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
- ibdata
- innodb_file_per_table
tag_slugs:
- ibdata
- innodb_file_per_table
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/reclaim-space-in-InnoDB.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to reclaim space in InnoDB when innodb_file_per_table is ON

Source: [Percona Blog](https://www.percona.com/blog/how-to-reclaim-space-in-innodb-when-innodb_file_per_table-is-on/)

Auteur source: [Nilnandan Joshi](https://www.percona.com/blog/author/nilnandan-joshi-2/)

Publication: 2013-09-25T05:00:27

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Editor’s Note: This post was written for MySQL 5.1/5.5 and that the default for innodb_file_per_table was turned ON in 5.6 and the behaviors/process noted below may not be necessary for someone running 5.6/5.7. When innodb_file_per_table is OFF and all data is going to be stored in ibdata files. If you drop some tables and delete … Continued

## Images et graphiques reperes

- featured / image: [How to reclaim space in InnoDB when innodb_file_per_table is ON](https://www.percona.com/wp-content/uploads/2026/03/reclaim-space-in-InnoDB.jpg)

## Auteur source

Nilnandan officially started with Percona as a Support Engineer. Before joining Percona, he has worked as a MySQL Database administrator with different types of service based companies managing high-traffic websites and web applications. Nilnandan has extensive experience in database design and development, database management, client management, security/documentations/training, implementing DRM solutions, automating backups and high availability. Nilnandan is based at Pune (India). In his spare time, he likes to listen Indian classical/semi-classical music, watching tv, playing cricket/badminton and hang out with his family.
