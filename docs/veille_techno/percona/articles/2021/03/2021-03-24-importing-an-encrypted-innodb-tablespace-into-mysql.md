---
title: Importing an Encrypted InnoDB Tablespace into MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/importing-an-encrypted-innodb-tablespace-into-mysql/
  post_id: 24072
source_author:
  name: Sri Sakthivel
  slug: sri-sakthivel
  url: https://www.percona.com/blog/author/sri-sakthivel/
  website: ''
published_at: '2021-03-24T13:03:54'
published_at_gmt: '2021-03-24T13:03:54'
modified_at: '2026-04-27T22:25:14'
modified_at_gmt: '2026-04-27T22:25:14'
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
tags:
- insight for DBAs
- MySQL
- mysql-and-variants
tag_slugs:
- insight-for-dbas
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/importing-encrypted-InnoDB-Tablespace-MySQL.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Importing an Encrypted InnoDB Tablespace into MySQL

Source: [Percona Blog](https://www.percona.com/blog/importing-an-encrypted-innodb-tablespace-into-mysql/)

Auteur source: [Sri Sakthivel](https://www.percona.com/blog/author/sri-sakthivel/)

Publication: 2021-03-24T13:03:54

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Transportable tablespaces were introduced in MySQL 5.6. Using this feature, we can directly copy a tablespace to another server and populate the table with data. This is a very useful feature for large tables. The transportable tablespace mechanism is faster than any other method for exporting and importing tables because the files containing the data … Continued

## Structure detectee

- H2: Requirements
- H3: Step 1 (Prepare the table to copy):
- H3: Step 2 (copy .ibd, .cfg, and .cfp files from s1 to s2):
- H3: Step 3 (Unlock table on S1):
- H3: Step 4 (Create the table structure on s2):
- H3: Step 5 (Remove the .ibd file):
- H3: Step 6 (Copy the tablespace to data directory):
- H3: Step 7 (Change ownership to MySQL user):
- H3: Step 8 (Import the tablespace):

## Images et graphiques reperes

- featured / image: [Importing an Encrypted InnoDB Tablespace into MySQL](https://www.percona.com/wp-content/uploads/2026/03/importing-encrypted-InnoDB-Tablespace-MySQL.png)
- content / image: [Importing an Encrypted InnoDB Tablespace into MySQL](https://www.percona.com/wp-content/uploads/2026/03/importing-encrypted-InnoDB-Tablespace-MySQL-300x168.png)

## Auteur source

Oracle certified MySQL DBA. Working on MySQL and related technologies to ensures database performance. Handling multi client projects round the clock. Currently focusing on MySQL Cluster technologies like Galera and Group replication/InnoDB cluster. Active MySQL Blogger.
