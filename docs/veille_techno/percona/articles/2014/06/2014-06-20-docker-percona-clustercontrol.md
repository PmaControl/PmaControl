---
title: How to setup Docker for Percona ClusterControl and add existing Percona XtraDB Cluster
source:
  name: Percona Blog
  url: https://www.percona.com/blog/docker-percona-clustercontrol/
  post_id: 8131
source_author:
  name: Jericho Rivera
  slug: jerichorivera
  url: https://www.percona.com/blog/author/jerichorivera/
  website: ''
published_at: '2014-06-20T15:12:56'
published_at_gmt: '2014-06-20T15:12:56'
modified_at: '2026-04-28T22:05:05'
modified_at_gmt: '2026-04-28T22:05:05'
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
- Docker
- Jericho Rivera
- Percona ClusterControl
- Percona XtraDB Cluster
tag_slugs:
- docker
- jericho-rivera
- percona-clustercontrol
- percona-xtradb-cluster
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/dockercc-2-e1399797921580.png
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to setup Docker for Percona ClusterControl and add existing Percona XtraDB Cluster

Source: [Percona Blog](https://www.percona.com/blog/docker-percona-clustercontrol/)

Auteur source: [Jericho Rivera](https://www.percona.com/blog/author/jerichorivera/)

Publication: 2014-06-20T15:12:56

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In my previous post I showed you how to setup Percona XtraDB Cluster 5.6 on Docker. This time I will show you how to setup Percona ClusterControl and add the existing Percona XtraDB Cluster 5.6 that we’ve managed to setup from the previous post. Let us note the following details about our existing containers: … Continued

## Structure detectee

- H3: Create the Percona ClusterControl UI Docker container
- H3: Setup Percona ClusterControl on the web browser
- H3: Summary

## Images et graphiques reperes

- featured / image: [How to setup Docker for Percona ClusterControl and add existing Percona XtraDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/dockercc-2-e1399797921580.png)
- content / image: [img_53a44a76a9b50.png](https://www.percona.com/wp-content/uploads/2026/03/img_53a44a76a9b50.png)
- content / image: [Percona ClusterControl](https://www.percona.com/wp-content/uploads/2026/03/dockercc-1-e1399798616447.png)
  Caption: Percona ClusterControl Wizard
- content / image: [img_53a44a772f765.png](https://www.percona.com/wp-content/uploads/2026/03/img_53a44a772f765.png)
- content / image: [img_53a44a788ac8e.png](https://www.percona.com/wp-content/uploads/2026/03/img_53a44a788ac8e.png)
- content / image: [Percona ClusterControl](https://www.percona.com/wp-content/uploads/2026/03/dockercc-3-e1399798092177.png)
  Caption: Percona ClusterControl UI on Docker container with Percona XtraDB Clusters 5.6 with each node on docker containers

## Auteur source

Jericho Rivera currently works for Percona as Support Engineer. His interests include linux systems and MySQL database administration.
