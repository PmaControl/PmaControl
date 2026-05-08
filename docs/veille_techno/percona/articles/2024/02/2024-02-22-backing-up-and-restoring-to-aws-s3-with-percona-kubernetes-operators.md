---
title: Backing up and Restoring to AWS S3 With Percona Kubernetes Operators
source:
  name: Percona Blog
  url: https://www.percona.com/blog/backing-up-and-restoring-to-aws-s3-with-percona-kubernetes-operators/
  post_id: 28091
source_author:
  name: Edith Puclla
  slug: edith-puclla
  url: https://www.percona.com/blog/author/edith-puclla/
  website: ''
published_at: '2024-02-22T14:21:56'
published_at_gmt: '2024-02-22T14:21:56'
modified_at: '2026-03-26T20:26:37'
modified_at_gmt: '2026-03-26T20:26:37'
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
- Percona Software
category_slugs:
- cloud
- mysql
- percona-software
tags:
- cloud
- Kubernetes
- MySQL
- mysql-and-variants
- operators
tag_slugs:
- cloud
- kubernetes
- mysql
- mysql-and-variants
- operators
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Backing-Up-and-Restoring-to-AWS-S3-Percona-Kubernetes-Operators.jpg
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Backing up and Restoring to AWS S3 With Percona Kubernetes Operators

Source: [Percona Blog](https://www.percona.com/blog/backing-up-and-restoring-to-aws-s3-with-percona-kubernetes-operators/)

Auteur source: [Edith Puclla](https://www.percona.com/blog/author/edith-puclla/)

Publication: 2024-02-22T14:21:56

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In our last post, we looked into the lifecycle of applications in Kubernetes. We see that Kubernetes doesn’t handle database backups itself. This is where Kubernetes Operators come into action. They add additional functions to Kubernetes, enabling it to set up, configure, and manage complex applications like databases within a Kubernetes environment for the user. … Continued

## Structure detectee

- H2: Prerequisites:
- H2: 1. Connect to the MySQL instance in Percona XtraDB Cluster
- H2: 2. Add sample data to the database
- H2: 3. Set up and make a physical backup
- H2: 4. Restore the database
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Backing up and Restoring to AWS S3 With Percona Kubernetes Operators](https://www.percona.com/wp-content/uploads/2026/03/Backing-Up-and-Restoring-to-AWS-S3-Percona-Kubernetes-Operators.jpg)
- content / image: [s3-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/s3-scaled.png)
- content / image: [Screenshot-2024-01-28-at-16.33.30.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-01-28-at-16.33.30.png)
- content / image: [Screenshot-2024-01-28-at-17.10.37-1024x583.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-01-28-at-17.10.37-1024x583.png)

## Auteur source

Edith Puclla is a Technology Evangelist at Percona Corporation, a CNCF Ambassador, an open source contributor with a background in DevOps, and a Docker and Kubernetes enthusiast.
