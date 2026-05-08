---
title: Location for InnoDB tablespace in MySQL 5.6.6
source:
  name: Percona Blog
  url: https://www.percona.com/blog/location-for-innodb-tablespace-in-mysql-5-6-6/
  post_id: 8203
source_author:
  name: Frederic Descamps
  slug: lefred
  url: https://www.percona.com/blog/author/lefred/
  website: http://www.lefred.be
published_at: '2014-05-28T10:00:16'
published_at_gmt: '2014-05-28T10:00:16'
modified_at: '2026-05-04T22:21:20'
modified_at_gmt: '2026-05-04T22:21:20'
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
category_slugs:
- insight-for-dbas
- mysql
tags:
- Frederic Descamps
- InnoDB tablespace
- MySQL 5.6.6
tag_slugs:
- frederic-descamps
- innodb-tablespace
- mysql-5-6-6
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Location for InnoDB tablespace in MySQL 5.6.6

Source: [Percona Blog](https://www.percona.com/blog/location-for-innodb-tablespace-in-mysql-5-6-6/)

Auteur source: [Frederic Descamps](https://www.percona.com/blog/author/lefred/)

Publication: 2014-05-28T10:00:16

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

There is one new feature in MySQL 5.6 that didn’t get the attention it deserved (at least from me 😉 ) : “DATA DIRECTORY” for InnoDB tables. This is implemented since MySQL 5.6.6 and can be used only at the creation of the table. It’s not possible to change the DATA DIRECTORY with an ALTER … Continued

## Auteur source

Frédéric joined Percona in June 2011, he is an experienced Open Source consultant with expertise in infrastructure projects as well in development tracks and database administration. Frédéric is a believer of devops culture.
