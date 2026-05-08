---
title: Comparing S3 Streaming Tools with Percona XtraBackup
source:
  name: Percona Blog
  url: https://www.percona.com/blog/comparing-s3-streaming-tools-with-percona-xtrabackup/
  post_id: 21256
source_author:
  name: Mykola Marzhan
  slug: mykola-marzhan
  url: https://www.percona.com/blog/author/mykola-marzhan/
  website: ''
published_at: '2019-11-26T16:07:33'
published_at_gmt: '2019-11-26T16:07:33'
modified_at: '2026-05-05T20:53:22'
modified_at_gmt: '2026-05-05T20:53:22'
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
- Benchmarks
- Cloud
- Hardware and Storage
- MySQL
category_slugs:
- benchmarks
- cloud
- hardware-and-storage
- mysql
tags:
- cloud
- MySQL
tag_slugs:
- cloud
- mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Comparing-S3-Streaming-Tools-Xtrabackup.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Comparing S3 Streaming Tools with Percona XtraBackup

Source: [Percona Blog](https://www.percona.com/blog/comparing-s3-streaming-tools-with-percona-xtrabackup/)

Auteur source: [Mykola Marzhan](https://www.percona.com/blog/author/mykola-marzhan/)

Publication: 2019-11-26T16:07:33

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Making backups over the network can be done in two ways: either save on disk and transfer or just transfer without saving. Both ways have their strong and weak points. The second way, particularly, is highly dependent on the upload speed, which would either reduce or increase the backup time. Other factors that influence it … Continued

## Structure detectee

- H2: AWS (Same Region)
- H2: AWS (From US to EU)
- H2: Google Cloud (From US to EU)
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Comparing S3 Streaming Tools with Percona XtraBackup](https://www.percona.com/wp-content/uploads/2026/03/Comparing-S3-Streaming-Tools-Xtrabackup.png)
- content / image: [t1-1-1024x556.png](https://www.percona.com/wp-content/uploads/2026/03/t1-1-1024x556.png)
- content / image: [t2-1024x556.png](https://www.percona.com/wp-content/uploads/2026/03/t2-1024x556.png)
- content / image: [gcs-multi-region-1024x339.png](https://www.percona.com/wp-content/uploads/2026/03/gcs-multi-region-1024x339.png)
