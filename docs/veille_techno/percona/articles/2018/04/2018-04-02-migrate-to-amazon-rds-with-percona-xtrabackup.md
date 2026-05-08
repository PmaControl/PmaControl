---
title: Migrate to Amazon RDS Using Percona Xtrabackup
source:
  name: Percona Blog
  url: https://www.percona.com/blog/migrate-to-amazon-rds-with-percona-xtrabackup/
  post_id: 18356
source_author:
  name: Daniel Kowalewski
  slug: daniel-kowalewski
  url: https://www.percona.com/blog/author/daniel-kowalewski/
  website: https://www.percona.com
published_at: '2018-04-02T23:00:10'
published_at_gmt: '2018-04-02T23:00:10'
modified_at: '2026-05-05T19:09:07'
modified_at_gmt: '2026-05-05T19:09:07'
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
- Cloud
- Insight for DBAs
- MySQL
category_slugs:
- cloud
- insight-for-dbas
- mysql
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/chart-2.png
image_count: 4
graph_or_chart_count: 2
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Migrate to Amazon RDS Using Percona Xtrabackup

Source: [Percona Blog](https://www.percona.com/blog/migrate-to-amazon-rds-with-percona-xtrabackup/)

Auteur source: [Daniel Kowalewski](https://www.percona.com/blog/author/daniel-kowalewski/)

Publication: 2018-04-02T23:00:10

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll look at how to migrate to Amazon RDS using Percona XtraBackup. Until recently, there was only one way to migrate your data from an existing MySQL instance into a new RDS MySQL instance: take and restore a logical backup with mysqldump or mydumper. This can be slow and error-prone. When … Continued

## Structure detectee

- H2: Demonstration – Migrate to Amazon RDS Using Percona Xtrabackup
- H2: Replication
- H2: Time Comparison
- H2: Conclusion
- H2: You May Also Like

## Images et graphiques reperes

- featured / graph_or_chart: [Migrate to Amazon RDS Using Percona Xtrabackup](https://www.percona.com/wp-content/uploads/2026/03/chart-2.png)
- content / image: [choose_s3_bucket-1024x364.png](https://www.percona.com/wp-content/uploads/2026/03/choose_s3_bucket-1024x364.png)
- content / graph_or_chart: [chart-1.png](https://www.percona.com/wp-content/uploads/2026/03/chart-1.png)
- content / image: [Watch the recorded webinar](https://www.percona.com/wp-content/uploads/2026/03/e45a8fcb-2063-42ba-bd82-634e960718ab.png)

## Auteur source

Daniel joined Percona in August of 2015. Previously, he earned a B.S. in Computer Science from the University of Colorado in 2006, and was a DBA there until he joined Percona. In addition to MySQL, Daniel also has experience with Oracle and Microsoft SQL Server, but he much prefers to stay in the MySQL world. Daniel lives near Denver, CO with his wife, three-year-old son, and dog. If you can't reach him, he's probably in the mountains hiking, camping, or trying to get lost.
