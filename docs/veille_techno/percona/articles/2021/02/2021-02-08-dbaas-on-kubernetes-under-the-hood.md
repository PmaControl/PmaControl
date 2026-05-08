---
title: 'DBaaS on Kubernetes: Under the Hood'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/dbaas-on-kubernetes-under-the-hood/
  post_id: 23902
source_author:
  name: Sergey Pronin
  slug: sergey-pronin
  url: https://www.percona.com/blog/author/sergey-pronin/
  website: ''
published_at: '2021-02-08T17:36:12'
published_at_gmt: '2021-02-08T17:36:12'
modified_at: '2026-05-05T16:36:25'
modified_at_gmt: '2026-05-05T16:36:25'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
- ProxySQL
- XtraBackup
matched_filters:
- search:percona-monitoring-and-management
- search:percona-xtrabackup
- search:pmm
- search:proxysql
- search:xtrabackup
categories:
- Cloud
- Insight for DBAs
- Open Source
- Percona Software
category_slugs:
- cloud
- insight-for-dbas
- open-source
- percona-software
tags:
- cloud
- containers
- DBaaS
- Kubernetes
- mysql-and-variants
- Percona Software
tag_slugs:
- cloud
- containers
- dbaas
- kubernetes
- mysql-and-variants
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/DBaaS-on-Kubernetes-1.png
image_count: 10
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# DBaaS on Kubernetes: Under the Hood

Source: [Percona Blog](https://www.percona.com/blog/dbaas-on-kubernetes-under-the-hood/)

Auteur source: [Sergey Pronin](https://www.percona.com/blog/author/sergey-pronin/)

Publication: 2021-02-08T17:36:12

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Running Database-as-a-Service (DBaaS) in the cloud is the norm for users today. It provides a ready-to-use database instance for users in a few seconds, which can be easily scaled and usually comes with a pay-as-you-go model. We at Percona see that more and more cloud vendors or enterprises either want to or are already running … Continued

## Structure detectee

- H2: DBaaS on Kubernetes Offerings
- H2: Topologies
- H2: Kubernetes Cluster per DB
- H2: Shared Kubernetes with Node Isolation
- H2: Fully Shared Kubernetes
- H2: Shared Kubernetes with VM-Like Isolation
- H2: High Availability
- H2: Storage
- H2: StatefulSets
- H2: Persistent Volume Claims
- H2: Local Storage
- H3: EmptyDir
- H3: HostPath
- H3: Pitfall #1 – Node Failure Causes Data Loss
- H3: Pitfall #2 – Data Limitation is Hard
- H2: Network
- H2: Day 2 Operations
- H2: Scaling
- H2: Upgrading
- H2: Backups
- H3: Point-in-time recovery
- H2: Monitoring
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [DBaaS on Kubernetes: Under the Hood](https://www.percona.com/wp-content/uploads/2026/03/DBaaS-on-Kubernetes-1.png)
- content / image: [DBaaS on Kubernetes](https://www.percona.com/wp-content/uploads/2026/03/DBaaS-on-Kubernetes-1-300x157.png)
- content / image: [Kubernetes Cluster per DB](https://www.percona.com/wp-content/uploads/2026/03/Dbaas-1024x557.png)
- content / image: [Shared Kubernetes with Node Isolation](https://www.percona.com/wp-content/uploads/2026/03/Dbaas-Page-1-1024x551.png)
- content / image: [Fully Shared Kubernetes](https://www.percona.com/wp-content/uploads/2026/03/Dbaas-Page-1-1.png)
- content / image: [Shared Kubernetes with VM-Like Isolation](https://www.percona.com/wp-content/uploads/2026/03/Dbaas-Page-1-2.png)
- content / image: [Persistent Volume Claims](https://www.percona.com/wp-content/uploads/2026/03/Dbaas-Page-1-3.png)
- content / image: [Local Storage](https://www.percona.com/wp-content/uploads/2026/03/Dbaas-Page-1-4.png)
- content / image: [Node Failure Causes Data Loss](https://www.percona.com/wp-content/uploads/2026/03/Dbaas-Page-1-5.png)
- content / image: [DBaas Network](https://www.percona.com/wp-content/uploads/2026/03/Dbaas-Page-1-6.png)

## Auteur source

Sergey is a product leader at Percona focusing on delivering robust open-source database and cloud-native solutions. Prior to Percona Sergey led product management and engineering teams in other organizations with a primary focus on products in infrastructure and platforms space.
