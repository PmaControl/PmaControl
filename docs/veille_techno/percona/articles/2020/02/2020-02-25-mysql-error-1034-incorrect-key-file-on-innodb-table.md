---
title: 'MySQL ERROR 1034: Incorrect Key File on InnoDB Table'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-error-1034-incorrect-key-file-on-innodb-table/
  post_id: 21649
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2020-02-25T13:45:54'
published_at_gmt: '2020-02-25T13:45:54'
modified_at: '2026-05-05T17:56:59'
modified_at_gmt: '2026-05-05T17:56:59'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
- Storage Engine
category_slugs:
- mysql
- storage-engine
tags:
- InnoDB
- MySQL
- Storage Engine
tag_slugs:
- innodb
- mysql
- storage-engine
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-ERROR-1034.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL ERROR 1034: Incorrect Key File on InnoDB Table

Source: [Percona Blog](https://www.percona.com/blog/mysql-error-1034-incorrect-key-file-on-innodb-table/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2020-02-25T13:45:54

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Sometimes, you may experience “ERROR 1034: Incorrect key file” while running the ALTER TABLE or CREATE INDEX command: Shell mysql> alter table ontime add key(FlightDate); ERROR 1034 (HY000): Incorrect key file for table 'ontime'; try to repair it 1 2 mysql > alter table ontime add key ( FlightDate ) ; ERROR 1034 ( HY000 ) : Incorrect key file for table 'ontime' ; try to repair it As the error message mentions key file, it is reasonable to assume we’re dealing with the MyISAM storage engine (the legacy storage engine which used to have such a thing), but no, we can clearly see … Continued

## Structure detectee

- H3: Summary:

## Images et graphiques reperes

- featured / image: [MySQL ERROR 1034: Incorrect Key File on InnoDB Table](https://www.percona.com/wp-content/uploads/2026/03/MySQL-ERROR-1034.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
