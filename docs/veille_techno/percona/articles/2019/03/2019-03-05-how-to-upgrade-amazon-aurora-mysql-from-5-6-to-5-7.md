---
title: How to Upgrade Amazon Aurora MySQL from 5.6 to 5.7
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-upgrade-amazon-aurora-mysql-from-5-6-to-5-7/
  post_id: 20055
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2019-03-05T17:31:46'
published_at_gmt: '2019-03-05T17:31:46'
modified_at: '2026-05-05T17:32:25'
modified_at_gmt: '2026-05-05T17:32:25'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Cloud
- MySQL
category_slugs:
- cloud
- mysql
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/aws_binlog_pos.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Upgrade Amazon Aurora MySQL from 5.6 to 5.7

Source: [Percona Blog](https://www.percona.com/blog/how-to-upgrade-amazon-aurora-mysql-from-5-6-to-5-7/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2019-03-05T17:31:46

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Over time, software evolves and it is important to stay up to date if you want to benefit from new features and performance improvements. Database engines follow the exact same logic and providers are always careful to provide an easy upgrade path. With MySQL, the mysql_upgrade tool serves that purpose. A database upgrade process becomes … Continued

## Structure detectee

- H2: Issues with the regular upgrade procedure
- H2: Our original high-level plan
- H2: Backup of the Amazon Aurora MySQL 5.6 cluster
- H2: Restore to an empty Amazon Aurora MySQL 5.7 cluster
- H2: Configure replication
- H2: Test with Amazon Aurora MySQL 5.7
- H2: Switch production to the Amazon Aurora MySQL 5.7 cluster
- H2: Summary
- H3: Co-Author: Jacques Fu, Fattmerchant

## Images et graphiques reperes

- featured / image: [How to Upgrade Amazon Aurora MySQL from 5.6 to 5.7](https://www.percona.com/wp-content/uploads/2026/03/aws_binlog_pos.png)
- content / image: [Replication speed](https://www.percona.com/wp-content/uploads/2026/03/repl_speed.png)
  Caption: Replication speed
- content / image: [Jacques-Fu-Fattmerchant-150x150.jpg](https://www.percona.com/wp-content/uploads/2026/03/Jacques-Fu-Fattmerchant-150x150.jpg)

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
