---
title: Exploring Kubernetes CPU Resources in View of Percona XtraDB Cluster’s Flow Control
source:
  name: Percona Blog
  url: https://www.percona.com/blog/exploring-kubernetes-cpu-resources-in-view-of-percona-xtradb-clusters-flow-control/
  post_id: 29025
source_author:
  name: Fernando Laudares Camargos
  slug: fernando-laudares
  url: https://www.percona.com/blog/author/fernando-laudares/
  website: ''
published_at: '2024-11-14T14:49:03'
published_at_gmt: '2024-11-14T14:49:03'
modified_at: '2026-03-26T20:25:53'
modified_at_gmt: '2026-03-26T20:25:53'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:pmm
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
- kubernetes operators
- MySQL
- mysql-and-variants
- pxc
tag_slugs:
- kubernetes-operators
- mysql
- mysql-and-variants
- pxc
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Exploring-Kubernetes-CPU-Resources-in-View-of-Percona-XtraDB-Clusters-Flow-Control.jpg
image_count: 7
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Exploring Kubernetes CPU Resources in View of Percona XtraDB Cluster’s Flow Control

Source: [Percona Blog](https://www.percona.com/blog/exploring-kubernetes-cpu-resources-in-view-of-percona-xtradb-clusters-flow-control/)

Auteur source: [Fernando Laudares Camargos](https://www.percona.com/blog/author/fernando-laudares/)

Publication: 2024-11-14T14:49:03

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Even though I used a dedicated Kubernetes cluster to host my test database, I had this belief that by not explicitly allocating (or requesting, in Kubernetes vocabulary) CPU resources to my Percona XtraDB Cluster (PXC) pods or yet making just a small request, Kubernetes could be delaying access to the free CPU cycles available on … Continued

## Structure detectee

- H2: The problem
- H2: A brief explanation of flow control
- H2: Why can’t the other nodes keep up with the writer node?
- H3: What do I cover below?
- H2: Test environment
- H2: Deploying a PXC cluster on GKE using the Percona Operator
- H2: PMM server
- H2: Cluster monitoring
- H2: Sysbench
- H2: K9s view of the pods’ distribution in the cluster
- H2: Running the benchmark
- H2: Results
- H2: Query throughput
- H2: Replication threads
- H2: Deleting the test environment

## Images et graphiques reperes

- featured / image: [Exploring Kubernetes CPU Resources in View of Percona XtraDB Cluster’s Flow Control](https://www.percona.com/wp-content/uploads/2026/03/Exploring-Kubernetes-CPU-Resources-in-View-of-Percona-XtraDB-Clusters-Flow-Control.jpg)
- content / image: [Selection_3592.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_3592.png)
- content / image: [Selection_3606.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_3606.png)
- content / image: [Selection_3752.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_3752.png)
- content / image: [Selection_3640.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_3640.png)
- content / image: [Selection_3643.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_3643.png)
- content / image: [Selection_3644.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_3644.png)

## Auteur source

Fernando Laudares Camargos joined Percona in early 2013 after working 8 years for a Canadian company specialized in offering services based in open source technologies. Fernando's work experience includes the architecture, deployment and maintenance of IT infrastructures based on Linux, open source software and a layer of server virtualization. From the basic services such as DHCP & DNS to identity management systems, but also including backup routines, configuration management tools and thin-clients. He's now focusing on the universe of MySQL, MongoDB and PostgreSQL with a particular interest in understanding the intricacies of database systems and contributes regularly to this blog. You can read his other articles here.
