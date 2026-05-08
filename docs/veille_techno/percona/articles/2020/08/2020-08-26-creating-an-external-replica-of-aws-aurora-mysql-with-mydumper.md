---
title: Creating an External Replica of AWS Aurora MySQL with Mydumper
source:
  name: Percona Blog
  url: https://www.percona.com/blog/creating-an-external-replica-of-aws-aurora-mysql-with-mydumper/
  post_id: 22926
source_author:
  name: Ivan Groenewold
  slug: ivan-groenewold
  url: https://www.percona.com/blog/author/ivan-groenewold/
  website: ''
published_at: '2020-08-26T18:31:41'
published_at_gmt: '2020-08-26T18:31:41'
modified_at: '2026-04-27T22:12:37'
modified_at_gmt: '2026-04-27T22:12:37'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:percona-xtrabackup
- search:pmm
- search:xtrabackup
- tag:percona-xtrabackup:330
categories:
- Cloud
- Insight for DBAs
- MySQL
category_slugs:
- cloud
- insight-for-dbas
- mysql
tags:
- Aurora
- mydumper
- MySQL
- mysql-and-variants
- Percona XtraBackup
tag_slugs:
- aurora
- mydumper
- mysql
- mysql-and-variants
- percona-xtrabackup
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL_Dumper.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Creating an External Replica of AWS Aurora MySQL with Mydumper

Source: [Percona Blog](https://www.percona.com/blog/creating-an-external-replica-of-aws-aurora-mysql-with-mydumper/)

Auteur source: [Ivan Groenewold](https://www.percona.com/blog/author/ivan-groenewold/)

Publication: 2020-08-26T18:31:41

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Oftentimes, we need to replicate between Amazon Aurora and an external MySQL server. The idea is to start by taking a point-in-time copy of the dataset. Next, we can configure MySQL replication to roll it forward and keep the data up-to-date. This process is documented by Amazon, however, it relies on the mysqldump method to … Continued

## Structure detectee

- H2: Preparation Steps
- H2: Exporting the Data
- H2: Importing the Data
- H2: Setting Up Replication
- H2: Final Words

## Images et graphiques reperes

- featured / image: [Creating an External Replica of AWS Aurora MySQL with Mydumper](https://www.percona.com/wp-content/uploads/2026/03/MySQL_Dumper.jpg)
- content / image: [MySQL_Dumper-300x169.jpg](https://www.percona.com/wp-content/uploads/2026/03/MySQL_Dumper-300x169.jpg)

## Auteur source

Passionate about technology, Ivan Groenewold is a seasoned professional with extensive experience in database management, cloud infrastructure, and software development. With a focus on optimizing performance and scalability, Ivan excels in designing and implementing solutions for complex systems, particularly in MongoDB and cloud-native environments. Known for problem-solving and a results-driven mindset, Ivan combines technical expertise with a commitment to continuous learning, delivering high-quality solutions for modern enterprise applications.
