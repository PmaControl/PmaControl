---
title: Kubernetes Scaling Capabilities with Percona XtraDB Cluster
source:
  name: Percona Blog
  url: https://www.percona.com/blog/kubernetes-scaling-capabilities-with-percona-xtradb-cluster/
  post_id: 23466
source_author:
  name: Sergey Pronin
  slug: sergey-pronin
  url: https://www.percona.com/blog/author/sergey-pronin/
  website: ''
published_at: '2020-11-13T16:40:16'
published_at_gmt: '2020-11-13T16:40:16'
modified_at: '2026-04-27T22:17:22'
modified_at_gmt: '2026-04-27T22:17:22'
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
- Percona Software
category_slugs:
- cloud
- insight-for-dbas
- mysql
- percona-software
tags:
- AWS
- cloud
- containers
- cost monitoring
- Kubernetes
- MySQL
- mysql-and-variants
- Percona Software
- scaling
tag_slugs:
- aws
- cloud
- containers
- cost-monitoring
- kubernetes
- mysql
- mysql-and-variants
- percona-software
- scaling
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Kubernetes-Scaling-Capabilities-with-Percona-XtraDB-Cluster.png
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Kubernetes Scaling Capabilities with Percona XtraDB Cluster

Source: [Percona Blog](https://www.percona.com/blog/kubernetes-scaling-capabilities-with-percona-xtradb-cluster/)

Auteur source: [Sergey Pronin](https://www.percona.com/blog/author/sergey-pronin/)

Publication: 2020-11-13T16:40:16

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Our recent survey showed that many organizations saw unexpected growth around cloud and data. Unexpected bills can become a big problem, especially in such uncertain times. This blog post talks about how Kubernetes scaling capabilities work with Percona Kubernetes Operator for Percona XtraDB Cluster (PXC Operator) and can help you to control the bill. Resources … Continued

## Structure detectee

- H2: Resources
- H2: Problem #1: Requested Too Much
- H2: Configure VPA
- H2: Problem #2: Spiky Usage
- H2: Problem #3: My Cluster is Too Big
- H2: Configure CA
- H3: Overprovision the Cluster
- H3: Expanders
- H3: Safety
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Kubernetes Scaling Capabilities with Percona XtraDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/Kubernetes-Scaling-Capabilities-with-Percona-XtraDB-Cluster.png)
- content / image: [Kubernetes Scaling Capabilities with Percona XtraDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/Kubernetes-Scaling-Capabilities-with-Percona-XtraDB-Cluster-300x168.png)
- content / image: [resource allocation in Kubernetes](https://www.percona.com/wp-content/uploads/2026/03/k8s-resources.png)
- content / image: [request resources for containers](https://www.percona.com/wp-content/uploads/2026/03/k8s-vpa-1024x591.png)
- content / image: [Horizontal Pod Autoscaler](https://www.percona.com/wp-content/uploads/2026/03/k8s-hpa-1024x398.png)
- content / image: [Kubernetes cluster is overprovisioned](https://www.percona.com/wp-content/uploads/2026/03/k8s-nodes-1024x247.png)

## Auteur source

Sergey is a product leader at Percona focusing on delivering robust open-source database and cloud-native solutions. Prior to Percona Sergey led product management and engineering teams in other organizations with a primary focus on products in infrastructure and platforms space.
