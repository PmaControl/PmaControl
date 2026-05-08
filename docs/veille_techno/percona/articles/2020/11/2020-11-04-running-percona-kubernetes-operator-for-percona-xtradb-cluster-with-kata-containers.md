---
title: Running Percona Kubernetes Operator for Percona XtraDB Cluster with Kata Containers
source:
  name: Percona Blog
  url: https://www.percona.com/blog/running-percona-kubernetes-operator-for-percona-xtradb-cluster-with-kata-containers/
  post_id: 23420
source_author:
  name: Sergey Pronin
  slug: sergey-pronin
  url: https://www.percona.com/blog/author/sergey-pronin/
  website: ''
published_at: '2020-11-04T15:03:17'
published_at_gmt: '2020-11-04T15:03:17'
modified_at: '2026-05-05T22:47:52'
modified_at_gmt: '2026-05-05T22:47:52'
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
- Security
category_slugs:
- cloud
- mysql
- percona-software
- security
tags:
- AWS
- cloud
- containers
- Kata
- Kubernetes
- MySQL
- mysql-and-variants
- Percona Software
- runtime
tag_slugs:
- aws
- cloud
- containers
- kata
- kubernetes
- mysql
- mysql-and-variants
- percona-software
- runtime
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-Kubernetes-Operator-for-Percona-XtraDB-Cluster-with-Kata-Containers.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Running Percona Kubernetes Operator for Percona XtraDB Cluster with Kata Containers

Source: [Percona Blog](https://www.percona.com/blog/running-percona-kubernetes-operator-for-percona-xtradb-cluster-with-kata-containers/)

Auteur source: [Sergey Pronin](https://www.percona.com/blog/author/sergey-pronin/)

Publication: 2020-11-04T15:03:17

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Kata containers are containers that use hardware virtualization technologies for workload isolation almost without performance penalties. Top use cases are untrusted workloads and tenant isolation (for example in a shared Kubernetes cluster). This blog post describes how to run Percona Kubernetes Operator for Percona XtraDB Cluster (PXC Operator) using Kata containers. Prepare Your Kubernetes Cluster … Continued

## Structure detectee

- H2: Prepare Your Kubernetes Cluster
- H2: Virtualization Support
- H2: Containerd
- H2: Setting Up Nodes
- H2: Install the Operator
- H4: Conclusions

## Images et graphiques reperes

- featured / image: [Running Percona Kubernetes Operator for Percona XtraDB Cluster with Kata Containers](https://www.percona.com/wp-content/uploads/2026/03/Percona-Kubernetes-Operator-for-Percona-XtraDB-Cluster-with-Kata-Containers.png)
- content / image: [Percona Kubernetes Operator for Percona XtraDB Cluster with Kata Containers](https://www.percona.com/wp-content/uploads/2026/03/Percona-Kubernetes-Operator-for-Percona-XtraDB-Cluster-with-Kata-Containers-300x157.png)
- content / image: [Kubernetes works with Kata](https://www.percona.com/wp-content/uploads/2026/03/kata-shim.png)

## Auteur source

Sergey is a product leader at Percona focusing on delivering robust open-source database and cloud-native solutions. Prior to Percona Sergey led product management and engineering teams in other organizations with a primary focus on products in infrastructure and platforms space.
