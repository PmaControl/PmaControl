---
title: Getting Started with ProxySQL in Kubernetes
source:
  name: Percona Blog
  url: https://www.percona.com/blog/getting-started-with-proxysql-in-kubernetes/
  post_id: 24920
source_author:
  name: Ivan Groenewold
  slug: ivan-groenewold
  url: https://www.percona.com/blog/author/ivan-groenewold/
  website: ''
published_at: '2021-10-07T12:21:17'
published_at_gmt: '2021-10-07T12:21:17'
modified_at: '2026-05-04T21:13:44'
modified_at_gmt: '2026-05-04T21:13:44'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- ProxySQL
matched_filters:
- category:mysql:83
- category:proxysql:2261
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
- Percona Kubernetes Operators
- ProxySQL
tag_slugs:
- kubernetes
- mysql
- mysql-and-variants
- percona-kubernetes-operators
- proxysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Getting-Started-with-ProxySQL-in-Kubernetes.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Getting Started with ProxySQL in Kubernetes

Source: [Percona Blog](https://www.percona.com/blog/getting-started-with-proxysql-in-kubernetes/)

Auteur source: [Ivan Groenewold](https://www.percona.com/blog/author/ivan-groenewold/)

Publication: 2021-10-07T12:21:17

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

There are plenty of ways to run ProxySQL in Kubernetes (K8S). For example, we can deploy sidecar containers on the application pods, or run a dedicated ProxySQL service with its own pods. We are going to discuss the latter approach, which is more likely to be used when dealing with a large number of application … Continued

## Structure detectee

- H2: Creating a Cluster
- H2: Dedicated Service Using a StatefulSet
- H2: Pod Name Resolution
- H2: Connecting to the Service
- H2: Cleanup Steps
- H3: Final Words

## Images et graphiques reperes

- featured / image: [Getting Started with ProxySQL in Kubernetes](https://www.percona.com/wp-content/uploads/2026/03/Getting-Started-with-ProxySQL-in-Kubernetes.png)
- content / image: [Getting Started with ProxySQL in Kubernetes](https://www.percona.com/wp-content/uploads/2026/03/Getting-Started-with-ProxySQL-in-Kubernetes-300x157.png)

## Auteur source

Passionate about technology, Ivan Groenewold is a seasoned professional with extensive experience in database management, cloud infrastructure, and software development. With a focus on optimizing performance and scalability, Ivan excels in designing and implementing solutions for complex systems, particularly in MongoDB and cloud-native environments. Known for problem-solving and a results-driven mindset, Ivan combines technical expertise with a commitment to continuous learning, delivering high-quality solutions for modern enterprise applications.
