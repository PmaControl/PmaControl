---
title: How to improve InnoDB performance by 55% for write-bound loads
source:
  name: Percona Blog
  url: https://www.percona.com/blog/improve-innodb-performance-write-bound-loads/
  post_id: 8089
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2014-05-23T10:00:44'
published_at_gmt: '2014-05-23T10:00:44'
modified_at: '2026-05-05T16:54:19'
modified_at_gmt: '2026-05-05T16:54:19'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Benchmarks
- Hardware and Storage
- Insight for DBAs
- MySQL
- Percona Live
category_slugs:
- benchmarks
- hardware-and-storage
- insight-for-dbas
- mysql
- percona-live
tags:
- Hardware
- Operating Systems
- Percona Live 2014
- Performance
- SSD
- Storage Engine
- Tips
tag_slugs:
- hardware
- operating-systems
- percona-live-2014
- performance
- ssd
- storage-engine
- tips
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Filesystem_trx_innodb_dw.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to improve InnoDB performance by 55% for write-bound loads

Source: [Percona Blog](https://www.percona.com/blog/improve-innodb-performance-write-bound-loads/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2014-05-23T10:00:44

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Update: do not do this, this has been proven to corrupt data! During April’s Percona Live MySQL Conference and Expo 2014, I attended a talk on MySQL 5.7 performance an scalability given by Dimitri Kravtchuk, the Oracle MySQL benchmark specialist. He mentioned at some point that the InnoDB double write buffer was a real performance … Continued

## Structure detectee

- H3: Update: do not do this, this has been proven to corrupt data!
- H2: Methodology
- H2: Results
- H2: Safety
- H2: Impacts on MyISAM
- H2: Fast SSDs
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [How to improve InnoDB performance by 55% for write-bound loads](https://www.percona.com/wp-content/uploads/2026/03/Filesystem_trx_innodb_dw.png)
- content / image: [TPCC NOTPM evolution over time](https://www.percona.com/wp-content/uploads/2026/03/Notpm_over_time_fs_innodb_dw.png)
  Caption: TPCC NOTPM evolution over time

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
