---
title: How To Calculate a Good MySQL Redo Log Size in MySQL 8
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-calculate-a-good-mysql-redo-log-size-in-mysql-8/
  post_id: 26670
source_author:
  name: Mauricio Cacho
  slug: mauricio-cacho
  url: https://www.percona.com/blog/author/mauricio-cacho/
  website: ''
published_at: '2023-03-06T14:21:15'
published_at_gmt: '2023-03-06T14:21:15'
modified_at: '2026-03-26T20:30:06'
modified_at_gmt: '2026-03-26T20:30:06'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:pmm
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- InnoDB
- MySQL
- mysql-and-variants
tag_slugs:
- innodb
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_an_underwater_high_tech_computer_server_a_dolpin_i_9337e5c5-e3c5-41dd-b0b1-e6504186488b.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How To Calculate a Good MySQL Redo Log Size in MySQL 8

Source: [Percona Blog](https://www.percona.com/blog/how-to-calculate-a-good-mysql-redo-log-size-in-mysql-8/)

Auteur source: [Mauricio Cacho](https://www.percona.com/blog/author/mauricio-cacho/)

Publication: 2023-03-06T14:21:15

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL uses Redo Logs internally during crash recovery to correct data written by incomplete transactions. But how do you know what the right Redo Log size is? We will walk through how to figure that out in this blog. We already have a couple of posts related to this topic. “How to calculate a good … Continued

## Structure detectee

- H2: Redo Logs
- H4: MySQL versions before 8.0.30:
- H4: MySQL versions 8.0.30 and later:
- H3: Testing the formula
- H3: Using PMM
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [How To Calculate a Good MySQL Redo Log Size in MySQL 8](https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_an_underwater_high_tech_computer_server_a_dolpin_i_9337e5c5-e3c5-41dd-b0b1-e6504186488b.png)
- content / image: [redo generation rate](https://www.percona.com/wp-content/uploads/2026/03/Redo-Generation.png)
- content / image: [InnoDB Log File Usage](https://www.percona.com/wp-content/uploads/2026/03/InnoDB-Redo-Logs-Usage-1.png)

## Auteur source

Mauricio started working with MySQL back in 2014, and started with DBA tasks a few months later; quickly after that, he became passionate about troubleshooting MySQL issues and optimizing overall performance for databases.
