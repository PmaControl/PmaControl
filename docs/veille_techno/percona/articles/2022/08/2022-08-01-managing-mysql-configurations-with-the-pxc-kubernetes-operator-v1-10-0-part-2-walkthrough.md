---
title: 'Managing MySQL Configurations with the PXC Kubernetes Operator V1.10.0 Part 2: Walkthrough'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/managing-mysql-configurations-with-the-pxc-kubernetes-operator-v1-10-0-part-2-walkthrough/
  post_id: 43884
source_author:
  name: Chetan Shivashankar
  slug: chetan-shivashankar
  url: https://www.percona.com/blog/author/chetan-shivashankar/
  website: ''
published_at: '2022-08-01T20:07:34'
published_at_gmt: '2022-08-01T20:07:34'
modified_at: '2026-04-20T20:15:54'
modified_at_gmt: '2026-04-20T20:15:54'
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
category_slugs:
- cloud
- mysql
- percona-software
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/04/Managing-MySQL-Configurations-with-the-PXC-Kubernetes-300x157-1.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Managing MySQL Configurations with the PXC Kubernetes Operator V1.10.0 Part 2: Walkthrough

Source: [Percona Blog](https://www.percona.com/blog/managing-mysql-configurations-with-the-pxc-kubernetes-operator-v1-10-0-part-2-walkthrough/)

Auteur source: [Chetan Shivashankar](https://www.percona.com/blog/author/chetan-shivashankar/)

Publication: 2022-08-01T20:07:34

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In part one of this series, we introduced the different ways to manage MySQL configurations. In this post, we will walk through different possibilities and the changes happening while modifying MySQL configurations with the operator. Percona Distribution for MySQL Operator based on Percona XtraDB Cluster (PXC) provides three ways for managing MySQL, but the question … Continued

## Structure detectee

- H2: CASE-1: Modify Percona XtraDB Cluster object
- H2: CASE-2: When MySQL configuration is present in both PXC object and ConfigMap and ConfigMap is modified
- H2: CASE-3: Modifying the ConfigMap cluster1-pxc when there is no MySQL configuration in PXC object

## Images et graphiques reperes

- content / image: [Managing MySQL Configurations with the PXC Kubernetes](https://www.percona.com/wp-content/uploads/2026/04/Managing-MySQL-Configurations-with-the-PXC-Kubernetes-300x157-1.png)

## Auteur source

Chetan is passionate about Kubernetes and well versed with Infrastructure and Devops Philosophy. He is playing a key role on running Database on Kubernetes at Percona.
