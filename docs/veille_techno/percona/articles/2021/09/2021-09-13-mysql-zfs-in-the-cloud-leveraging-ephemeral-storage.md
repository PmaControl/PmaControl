---
title: MySQL/ZFS in the Cloud, Leveraging Ephemeral Storage
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-zfs-in-the-cloud-leveraging-ephemeral-storage/
  post_id: 24801
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2021-09-13T13:51:05'
published_at_gmt: '2021-09-13T13:51:05'
modified_at: '2026-05-04T21:13:18'
modified_at_gmt: '2026-05-04T21:13:18'
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
- Insight for DBAs
- MySQL
category_slugs:
- cloud
- insight-for-dbas
- mysql
tags:
- cloud
- MySQL
- mysql-and-variants
tag_slugs:
- cloud
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-ZFS-Ephemeral-Cloud-Storage.png
image_count: 5
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL/ZFS in the Cloud, Leveraging Ephemeral Storage

Source: [Percona Blog](https://www.percona.com/blog/mysql-zfs-in-the-cloud-leveraging-ephemeral-storage/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2021-09-13T13:51:05

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Here’s a second post focusing on the performance of MySQL on ZFS in cloud environments. In the first post, MySQL/ZFS Performance Update, we compared the performances of ZFS and ext4. This time we’ll look at the benefits of using ephemeral storage devices. These devices, called ephemeral in AWS, local in Google cloud, and temporary in … Continued

## Structure detectee

- H2: What is the ZFS L2ARC?
- H2: Configuration for the L2ARC
- H2: L2ARC Impacts on TPCC Results
- H2: Comparison with bcache
- H2: How to Recreate L2ARC if Missing
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [MySQL/ZFS in the Cloud, Leveraging Ephemeral Storage](https://www.percona.com/wp-content/uploads/2026/03/MySQL-ZFS-Ephemeral-Cloud-Storage.png)
- content / image: [MySQL/ZFS in the cloud](https://www.percona.com/wp-content/uploads/2026/03/MySQL-ZFS-Ephemeral-Cloud-Storage-300x168.png)
- content / image: [TPCC Transation Rate ZFS](https://www.percona.com/wp-content/uploads/2026/03/tpcc_ZFS_on_ephemeral.png)
  Caption: TPCC results using ZFS on an ephemeral device
- content / image: [TPCC performance on ZFS with a L2ARC](https://www.percona.com/wp-content/uploads/2026/03/tpcc_ZFS_with_L2ARC.png)
  Caption: TPCC performance on ZFS with a L2ARC
- content / image: [Comparison of the TPCC transaction rate between bcache and L2ARC](https://www.percona.com/wp-content/uploads/2026/03/tpcc_l2arc_bcache.png)
  Caption: Comparison of the TPCC transaction rate between bcache and L2ARC

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
