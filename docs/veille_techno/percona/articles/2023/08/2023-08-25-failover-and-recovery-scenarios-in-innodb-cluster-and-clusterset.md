---
title: Failover and Recovery Scenarios in InnoDB Cluster and ClusterSet
source:
  name: Percona Blog
  url: https://www.percona.com/blog/failover-and-recovery-scenarios-in-innodb-cluster-and-clusterset/
  post_id: 27393
source_author:
  name: Anil Joshi
  slug: anil-joshi
  url: https://www.percona.com/blog/author/anil-joshi/
  website: ''
published_at: '2023-08-25T12:21:20'
published_at_gmt: '2023-08-25T12:21:20'
modified_at: '2026-03-26T20:29:09'
modified_at_gmt: '2026-03-26T20:29:09'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- InnoDB
- MySQL
- mysql-and-variants
tag_slugs:
- innodb
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Failover-and-Recovery-Scenarios-in-InnoDB-Cluster.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Failover and Recovery Scenarios in InnoDB Cluster and ClusterSet

Source: [Percona Blog](https://www.percona.com/blog/failover-and-recovery-scenarios-in-innodb-cluster-and-clusterset/)

Auteur source: [Anil Joshi](https://www.percona.com/blog/author/anil-joshi/)

Publication: 2023-08-25T12:21:20

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This blog post will focus on failover and recovery scenarios inside the InnoDB Cluster and ClusterSet environment. To know more about the deployments of these topologies, you can refer to the manuals – InnoDB Cluster and Innodb ClusterSet setup. In the below snippet, we have two clusters (cluster1 and cluster2), which are connected via an … Continued

## Structure detectee

- H2: How failover happens inside a single InnoDB Cluster
- H2: How to rejoin the lost instance again
- H3: How to recover a cluster from a quorum or vote loss
- H2: How to recover a complete cluster from a major outage
- H2: How to perform switchover/failover from one cluster to another in a ClusterSet
- H2: How to change traffic routes with MySQLRouter
- H2: How to rejoin the lost cluster again in the ClusterSet
- H3: Summary

## Images et graphiques reperes

- featured / image: [Failover and Recovery Scenarios in InnoDB Cluster and ClusterSet](https://www.percona.com/wp-content/uploads/2026/03/Failover-and-Recovery-Scenarios-in-InnoDB-Cluster.jpg)

## Auteur source

I am Anil Joshi, and I work for Percona as a support engineer. I've worked with some well-known Open Source database technologies (MySQL/MariaDB, MongoDB, and Redis) for almost ten years. I am keenly interested in learning new databases and writing database content.
