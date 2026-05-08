---
title: How Container Networking Affects Database Performance
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-container-networking-affects-database-performance/
  post_id: 21849
source_author:
  name: Tyler Duzan
  slug: tyler-duzan
  url: https://www.percona.com/blog/author/tyler-duzan/
  website: ''
published_at: '2020-03-18T20:35:51'
published_at_gmt: '2020-03-18T20:35:51'
modified_at: '2026-03-26T20:16:46'
modified_at_gmt: '2026-03-26T20:16:46'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- ProxySQL
matched_filters:
- category:mysql:83
- search:proxysql
categories:
- Insight for DBAs
- MongoDB
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mongodb
- mysql
- percona-software
tags:
- Kubernetes
- MongoDB
- MySQL
- Percona Software
tag_slugs:
- kubernetes
- mongodb
- mysql
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/How-Container-Networking-Affects-Database-Performance.png
image_count: 5
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How Container Networking Affects Database Performance

Source: [Percona Blog](https://www.percona.com/blog/how-container-networking-affects-database-performance/)

Auteur source: [Tyler Duzan](https://www.percona.com/blog/author/tyler-duzan/)

Publication: 2020-03-18T20:35:51

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona has been investing in building and releasing Operators for Kubernetes to run traditional databases in a cloud-native fashion. The first two Kubernetes operators were for Percona Server for MongoDB and Percona XtraDB Cluster, chosen because they both feature replication systems that can be made to work effectively in a containerized world. One of the … Continued

## Structure detectee

- H2: Benchmark Methodology
- H2: What CNI Plugins We Tested
- H3: Project Calico
- H3: Flannel
- H3: Cilium
- H3: Weave (weave-net)
- H3: Intel SR-IOV and Multus
- H3: Kube-Router
- H2: Results
- H3: Conclusions
- H3: Next Steps

## Images et graphiques reperes

- featured / image: [How Container Networking Affects Database Performance](https://www.percona.com/wp-content/uploads/2026/03/How-Container-Networking-Affects-Database-Performance.png)
- content / image: [How Container Networking Affects Database Performance](https://www.percona.com/wp-content/uploads/2026/03/How-Container-Networking-Affects-Database-Performance-300x168.png)
- content / image: [image4-4-1024x576.png](https://www.percona.com/wp-content/uploads/2026/03/image4-4-1024x576.png)
- content / image: [image3-4-1024x658.png](https://www.percona.com/wp-content/uploads/2026/03/image3-4-1024x658.png)
- content / image: [image2-3-1024x542.png](https://www.percona.com/wp-content/uploads/2026/03/image2-3-1024x542.png)

## Auteur source

Prior to joining Percona as a Product Manager, Tyler spent almost 13 years as an operations and security engineer in a variety of different industries. Tyler is applying his knowledge to solving business problems for Percona customers with inventive product solutions combining technology and services.
