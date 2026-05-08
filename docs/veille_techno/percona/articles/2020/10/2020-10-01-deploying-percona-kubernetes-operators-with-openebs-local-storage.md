---
title: Deploying Percona Kubernetes Operators with OpenEBS Local Storage
source:
  name: Percona Blog
  url: https://www.percona.com/blog/deploying-percona-kubernetes-operators-with-openebs-local-storage/
  post_id: 23190
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2020-10-01T18:52:35'
published_at_gmt: '2020-10-01T18:52:35'
modified_at: '2026-03-26T20:16:24'
modified_at_gmt: '2026-03-26T20:16:24'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Hardware and Storage
- MongoDB
- MySQL
- Percona Software
category_slugs:
- hardware-and-storage
- mongodb
- mysql
- percona-software
tags:
- Kuberenetes
- MongoDB
- MySQL
- mysql-and-variants
- Percona Software
- XtraDB Cluster
tag_slugs:
- kuberenetes
- mongodb
- mysql
- mysql-and-variants
- percona-software
- xtradb-cluster
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Deploying-Percona-Kubernetes-Operators-with-OpenEBS-Local-Storage.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Deploying Percona Kubernetes Operators with OpenEBS Local Storage

Source: [Percona Blog](https://www.percona.com/blog/deploying-percona-kubernetes-operators-with-openebs-local-storage/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2020-10-01T18:52:35

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Network volumes in Kubernetes provide great flexibility, but still, nothing beats local volumes from direct-attached storage in the sense of database performance. I want to explore ways to deploy both Percona Kubernetes Operators (Percona Kubernetes Operator for Percona XtraDB Cluster and Percona Kubernetes Operator for Percona Server for MongoDB) using local volumes, both on the … Continued

## Structure detectee

- H3: Simple Ways
- H2: Persistent Volumes
- H2: OpenEBS Local PV Hostpath

## Images et graphiques reperes

- featured / image: [Deploying Percona Kubernetes Operators with OpenEBS Local Storage](https://www.percona.com/wp-content/uploads/2026/03/Deploying-Percona-Kubernetes-Operators-with-OpenEBS-Local-Storage.png)
- content / image: [Deploying Percona Kubernetes Operators with OpenEBS Local Storage](https://www.percona.com/wp-content/uploads/2026/03/Deploying-Percona-Kubernetes-Operators-with-OpenEBS-Local-Storage-300x180.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
