---
title: 'Percona Operator for MySQL Based on Percona XtraDB Cluster: HAProxy or ProxySQL?'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-kubernetes-operator-for-percona-xtradb-cluster-haproxy-or-proxysql/
  post_id: 23657
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2021-01-11T15:00:09'
published_at_gmt: '2021-01-11T15:00:09'
modified_at: '2026-05-05T21:10:21'
modified_at_gmt: '2026-05-05T21:10:21'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- ProxySQL
matched_filters:
- category:mysql:83
- category:proxysql:2261
- search:percona-monitoring-and-management
- search:pmm
- search:proxysql
categories:
- Cloud
- MySQL
- Percona Software
- ProxySQL
category_slugs:
- cloud
- mysql
- percona-software
- proxysql
tags:
- Kubernetes
- MySQL
- mysql-and-variants
- Operator
- Percona Kubernetes Operator for Percona XtraDB Cluster
- Percona Software
tag_slugs:
- kubernetes
- mysql
- mysql-and-variants
- operator
- percona-kubernetes-operator-for-percona-xtradb-cluster
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-Kubernetes-Operator-HAProxy-or-ProxySQL.png
image_count: 22
graph_or_chart_count: 2
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona Operator for MySQL Based on Percona XtraDB Cluster: HAProxy or ProxySQL?

Source: [Percona Blog](https://www.percona.com/blog/percona-kubernetes-operator-for-percona-xtradb-cluster-haproxy-or-proxysql/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2021-01-11T15:00:09

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona Operator for MySQL based on Percona XtraDB Cluster comes with two different proxies, HAProxy and ProxySQL. While the initial version was based on ProxySQL, in time, Percona opted to set HAProxy as the default Proxy for the operator, without removing ProxySQL. While one of the main points was to guarantee users to have a … Continued

## Structure detectee

- H2: Operator Assumptions
- H2: The Plus in the Game (Read Scaling)
- H2: Global Difference and Comparison
- H2: The Test Environment
- H2: Comparing Performance When Scaling Reads
- H2: Comparing When Using Only One Node
- H3: Conclusions
- H4: References

## Images et graphiques reperes

- featured / image: [Percona Operator for MySQL Based on Percona XtraDB Cluster: HAProxy or ProxySQL?](https://www.percona.com/wp-content/uploads/2026/03/Percona-Kubernetes-Operator-HAProxy-or-ProxySQL.png)
- content / image: [Percona Kubernetes Operator HAProxy or ProxySQL](https://www.percona.com/wp-content/uploads/2026/03/Percona-Kubernetes-Operator-HAProxy-or-ProxySQL-300x168.png)
- content / image: [proxySQL-HAProxy-feature-comparison.png](https://www.percona.com/wp-content/uploads/2026/03/proxySQL-HAProxy-feature-comparison.png)
- content / image: [HAProxy](https://www.percona.com/wp-content/uploads/2026/03/events-3node-1024x429.png)
- content / image: [HAProxy ProxySQL](https://www.percona.com/wp-content/uploads/2026/03/operation-3node-1024x422.png)
- content / image: [HAProxy ProxySQL](https://www.percona.com/wp-content/uploads/2026/03/writes-3node-1024x487.png)
- content / image: [HAProxy ProxySQL read comparison](https://www.percona.com/wp-content/uploads/2026/03/reads-3node.png)
- content / graph_or_chart: [latency HAProxy](https://www.percona.com/wp-content/uploads/2026/03/latency68-3node-HAproxy-w-1024x337.png)
- content / graph_or_chart: [latency ProxySQL](https://www.percona.com/wp-content/uploads/2026/03/latency68-3node-proxy-w-1024x336.png)
- content / image: [latency68-3node-HAproxy-r-1024x335.png](https://www.percona.com/wp-content/uploads/2026/03/latency68-3node-HAproxy-r-1024x335.png)
- content / image: [latency68-3node-proxy-r-1024x335.png](https://www.percona.com/wp-content/uploads/2026/03/latency68-3node-proxy-r-1024x335.png)
- content / image: [latency1024-3node-HAproxy-w-1024x337.png](https://www.percona.com/wp-content/uploads/2026/03/latency1024-3node-HAproxy-w-1024x337.png)
- content / image: [latency1024-3node-proxy-w-1024x337.png](https://www.percona.com/wp-content/uploads/2026/03/latency1024-3node-proxy-w-1024x337.png)
- content / image: [latency1024-3node-HAproxy-r-1024x337.png](https://www.percona.com/wp-content/uploads/2026/03/latency1024-3node-HAproxy-r-1024x337.png)
- content / image: [latency1024-3node-proxy-r-1024x337.png](https://www.percona.com/wp-content/uploads/2026/03/latency1024-3node-proxy-r-1024x337.png)
- content / image: [writes-1node-1024x458.png](https://www.percona.com/wp-content/uploads/2026/03/writes-1node-1024x458.png)
- content / image: [Percona Kubernetes Operator for Percona XtraDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/reads-1node-1024x460.png)
- content / image: [latency68-1node-HAproxy-rw-1024x277.png](https://www.percona.com/wp-content/uploads/2026/03/latency68-1node-HAproxy-rw-1024x277.png)
- content / image: [latency68-1node-proxy-rw-1024x277.png](https://www.percona.com/wp-content/uploads/2026/03/latency68-1node-proxy-rw-1024x277.png)
- content / image: [latency2048-1node-HAproxy-rw-1024x277.png](https://www.percona.com/wp-content/uploads/2026/03/latency2048-1node-HAproxy-rw-1024x277.png)
- content / image: [latency2048-1node-proxy-rw-1024x277.png](https://www.percona.com/wp-content/uploads/2026/03/latency2048-1node-proxy-rw-1024x277.png)
- content / image: [right-tool-296x300.png](https://www.percona.com/wp-content/uploads/2026/03/right-tool-296x300.png)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
