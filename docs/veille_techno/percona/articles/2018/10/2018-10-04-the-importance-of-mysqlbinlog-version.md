---
title: The Importance of mysqlbinlog –version
source:
  name: Percona Blog
  url: https://www.percona.com/blog/the-importance-of-mysqlbinlog-version/
  post_id: 19420
source_author:
  name: Ceri Williams
  slug: ceri-williams
  url: https://www.percona.com/blog/author/ceri-williams/
  website: https://www.percona.com
published_at: '2018-10-04T14:01:31'
published_at_gmt: '2018-10-04T14:01:31'
modified_at: '2026-05-05T19:23:44'
modified_at_gmt: '2026-05-05T19:23:44'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MariaDB
- MySQL
matched_filters:
- category:mariadb:1281
- category:mysql:83
categories:
- Insight for DBAs
- MariaDB
- MySQL
category_slugs:
- insight-for-dbas
- mariadb
- mysql
tags:
- binlog
- binlog encryption
- binlog events
- MySQL version
- package versions
tag_slugs:
- binlog
- binlog-encryption
- binlog-events
- mysql-version
- package-versions
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-binlog-version.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# The Importance of mysqlbinlog –version

Source: [Percona Blog](https://www.percona.com/blog/the-importance-of-mysqlbinlog-version/)

Auteur source: [Ceri Williams](https://www.percona.com/blog/author/ceri-williams/)

Publication: 2018-10-04T14:01:31

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

When deciding on your backup strategy, one of the key components for Point In Time Recovery (PITR) will be the binary logs. Thankfully, the mysqlbinlog command allows you to easily take binary log backups, including those that would otherwise be encrypted on disk using encrypt_binlog=ON.

## Structure detectee

- H2: Warning: option ‘stop-never-slave-server-id’: unsigned value <xxxxxxxx> adjusted to <yyyyy>
- H2: ERROR: Could not find server version: Master reported unrecognized MySQL version ‘xxx’

## Images et graphiques reperes

- featured / image: [The Importance of mysqlbinlog –version](https://www.percona.com/wp-content/uploads/2026/03/MySQL-binlog-version.jpg)
- content / image: [Importance of MySQL binlog version](https://www.percona.com/wp-content/uploads/2026/03/MySQL-binlog-version-300x212.jpg)

## Auteur source

Ceri is a Senior Technical Operations Engineer at Percona. He has previously worked in a variety of industries ranging from telecoms to skin care and online travel, nearly always with a database by his side for more than 10 years. Living in the Welsh Marches area of the UK, Ceri enjoys the rural life and beautiful countryside whenever possible.
