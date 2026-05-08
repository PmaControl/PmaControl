---
title: Illustrating Primary Key models in InnoDB and their impact on disk usage
source:
  name: Percona Blog
  url: https://www.percona.com/blog/illustrating-primary-key-models-in-innodb-and-their-impact-on-disk-usage/
  post_id: 9077
source_author:
  name: Michael Coburn
  slug: michael-coburn
  url: https://www.percona.com/blog/author/michael-coburn/
  website: ''
published_at: '2015-04-03T10:00:32'
published_at_gmt: '2015-04-03T10:00:32'
modified_at: '2026-05-04T22:34:43'
modified_at_gmt: '2026-05-04T22:34:43'
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
- tag:percona-xtrabackup:330
categories:
- MySQL
category_slugs:
- mysql
tags:
- InnoDB
- Michael Coburn
- MySQL
- Percona XtraBackup
- Primary
- primary key
tag_slugs:
- innodb
- michael-coburn
- mysql
- percona-xtrabackup
- primary
- primary-key
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/t3_UUID_B.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Illustrating Primary Key models in InnoDB and their impact on disk usage

Source: [Percona Blog](https://www.percona.com/blog/illustrating-primary-key-models-in-innodb-and-their-impact-on-disk-usage/)

Auteur source: [Michael Coburn](https://www.percona.com/blog/author/michael-coburn/)

Publication: 2015-04-03T10:00:32

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

On a recent engagement I worked with a customer who makes extensive use of UUID() values for their Primary Key and stores it as char(36), and their row count on this example table has grown to over 1 billion rows. The table is INSERT-only (no UPDATEs or DELETEs), and the bulk of their retrieval are PK … Continued

## Images et graphiques reperes

- featured / image: [Illustrating Primary Key models in InnoDB and their impact on disk usage](https://www.percona.com/wp-content/uploads/2026/03/t3_UUID_B.png)
- content / image: [t1_AUTO_INCREMENT](https://www.percona.com/wp-content/uploads/2026/03/t1_AUTO_INCREMENT-300x248.png)
  Caption: Primary Key integer AUTO_INCREMENT
- content / image: [t2_Ordered_UUID](https://www.percona.com/wp-content/uploads/2026/03/t2_Ordered_UUID-300x300.png)
  Caption: Ordered UUID()-based Primary Key
- content / image: [t3_UUID_A](https://www.percona.com/wp-content/uploads/2026/03/t3_UUID_A-186x300.png)
  Caption: UUID() Primary Key

## Auteur source

Michael Coburn works at Percona on the Professional Services team in the role of Principal Architect. Michael joined Percona in 2012 as a Consultant after having worked as a DBA with stock photography websites and email service provider platforms. With a foundation in Systems Administration, Michael previously served as Product Manager responsible for Percona Monitoring and Management (PMM).
