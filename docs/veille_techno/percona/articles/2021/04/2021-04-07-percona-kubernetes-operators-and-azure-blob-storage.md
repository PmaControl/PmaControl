---
title: Percona Kubernetes Operators and Azure Blob Storage
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-kubernetes-operators-and-azure-blob-storage/
  post_id: 24135
source_author:
  name: Sergey Pronin
  slug: sergey-pronin
  url: https://www.percona.com/blog/author/sergey-pronin/
  website: ''
published_at: '2021-04-07T14:57:44'
published_at_gmt: '2021-04-07T14:57:44'
modified_at: '2026-03-26T20:16:11'
modified_at_gmt: '2026-03-26T20:16:11'
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
- MongoDB
- MySQL
- Percona Software
category_slugs:
- cloud
- mongodb
- mysql
- percona-software
tags:
- Azure
- cloud
- containers
- Kubernetes
- Kubernetes Operator
- MongoDB
- MySQL
- mysql-and-variants
- Percona Software
tag_slugs:
- azure
- cloud
- containers
- kubernetes
- kubernetes-operator
- mongodb
- mysql
- mysql-and-variants
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-Kubernetes-Operators-and-Azure-Blob-Storage.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona Kubernetes Operators and Azure Blob Storage

Source: [Percona Blog](https://www.percona.com/blog/percona-kubernetes-operators-and-azure-blob-storage/)

Auteur source: [Sergey Pronin](https://www.percona.com/blog/author/sergey-pronin/)

Publication: 2021-04-07T14:57:44

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona Kubernetes Operators allow users to simplify deployment and management of MongoDB and MySQL databases on Kubernetes. Both operators allow users to store backups on S3-compatible storage and leverage Percona XtraBackup and Percona Backup for MongoDB to deliver backup and restore functionality. Both backup tools do not work with Azure Blob Storage, which is not … Continued

## Structure detectee

- H2: Setup
- H2: Deploy MinIO Gateway
- H2: Deploy PXC
- H2: Take Backups and Restore
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Percona Kubernetes Operators and Azure Blob Storage](https://www.percona.com/wp-content/uploads/2026/03/Percona-Kubernetes-Operators-and-Azure-Blob-Storage.png)
- content / image: [Percona Kubernetes Operators along with MinIO Gateway](https://www.percona.com/wp-content/uploads/2026/03/blog-Page-1-17-1024x418.png)
- content / image: [backup_created.png](https://www.percona.com/wp-content/uploads/2026/03/backup_created.png)

## Auteur source

Sergey is a product leader at Percona focusing on delivering robust open-source database and cloud-native solutions. Prior to Percona Sergey led product management and engineering teams in other organizations with a primary focus on products in infrastructure and platforms space.
