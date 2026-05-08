---
title: Exploring MySQL on Kubernetes With Minikube
source:
  name: Percona Blog
  url: https://www.percona.com/blog/exploring-mysql-on-kubernetes-with-minkube/
  post_id: 25821
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2022-07-14T12:29:43'
published_at_gmt: '2022-07-14T12:29:43'
modified_at: '2026-03-26T20:31:36'
modified_at_gmt: '2026-03-26T20:31:36'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- ProxySQL
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:percona-xtrabackup
- search:pmm
- search:proxysql
- search:xtrabackup
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
- cloud
- Kubernetes
- Minikube
- MySQL
- mysql-and-variants
tag_slugs:
- cloud
- kubernetes
- minikube
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Exploring-MySQL-on-Kubernetes-with-Minkube.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Exploring MySQL on Kubernetes With Minikube

Source: [Percona Blog](https://www.percona.com/blog/exploring-mysql-on-kubernetes-with-minkube/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2022-07-14T12:29:43

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, I will show how to install the MySQL-compatible Percona XtraDB Cluster (PXC) Operator on Minikube as well as perform some basic actions. I am by no means a Kubernetes expert and this blog post is the result of my explorations preparing for a local MySQL Meetup, so if you have … Continued

## Structure detectee

- H2: Enabling Metrics Server in Minikube
- H2: Getting basic MySQL up and running on Kubernetes
- H2: Percona Operator for MySQL Custom Resource Manifest explained
- H2: Accessing the MySQL server you provisioned
- H2: Running Sysbench on MySQL on Kubernetes
- H2: Let’s make MySQL on Kubernetes highly available!
- H2: Setting up resource limits for your MySQL on Kubernetes
- H2: Pausing and resuming MySQL on Kubernetes
- H2: MySQL on Kubernetes backup (and restore)
- H2: Monitoring your deployment with Percona Monitoring and Management (PMM)
- H2: Configuring MySQL on Kubernetes
- H2: Accessing MySQL logs
- H2: Deleting the MySQL deployment
- H2: Protecting PVCs from deletion
- H2: Summary

## Images et graphiques reperes

- featured / image: [Exploring MySQL on Kubernetes With Minikube](https://www.percona.com/wp-content/uploads/2026/03/Exploring-MySQL-on-Kubernetes-with-Minkube.png)
- content / image: [Exploring MySQL on Kubernetes with Minkube](https://www.percona.com/wp-content/uploads/2026/03/Exploring-MySQL-on-Kubernetes-with-Minkube-300x157.png)
- content / image: [API Keys configuration section](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2022-07-13-at-7.42.54-AM-1024x263.png)
- content / image: [Percona Monitoring and Management](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2022-07-13-at-7.44.39-AM-1024x525.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
