---
title: 'Using Percona Kubernetes Operators With K3s Part 3: Monitoring and PMM'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-percona-kubernetes-operators-with-k3s-part-3-monitoring-and-pmm/
  post_id: 26133
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2022-10-14T11:14:24'
published_at_gmt: '2022-10-14T11:14:24'
modified_at: '2026-03-26T20:30:57'
modified_at_gmt: '2026-03-26T20:30:57'
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
- cloud
- K3s
- Kubernetes
- MySQL
- mysql-and-variants
- Percona Operators
tag_slugs:
- cloud
- k3s
- kubernetes
- mysql
- mysql-and-variants
- percona-operators
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-Kubernetes-Operators-With-K3s-Monitoring-and-Management.png
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using Percona Kubernetes Operators With K3s Part 3: Monitoring and PMM

Source: [Percona Blog](https://www.percona.com/blog/using-percona-kubernetes-operators-with-k3s-part-3-monitoring-and-pmm/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2022-10-14T11:14:24

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

As we have Kubernetes installed in part one (see Using Percona Kubernetes Operators With K3s Part 1: Installation) and have Percona Server for MySQL running (Using Percona Kubernetes Operators With K3s Part 2: Percona Server for MySQL Operator) lets review how we can install monitoring and monitor our running instance. Percona Monitoring and Management installation … Continued

## Structure detectee

- H2: Percona Monitoring and Management installation
- H2: Monitoring the running Percona Operator for MySQL
- H2: Summary

## Images et graphiques reperes

- featured / image: [Using Percona Kubernetes Operators With K3s Part 3: Monitoring and PMM](https://www.percona.com/wp-content/uploads/2026/03/Percona-Kubernetes-Operators-With-K3s-Monitoring-and-Management.png)
- content / image: [Percona Kubernetes Operators With K3s Monitoring and Management](https://www.percona.com/wp-content/uploads/2026/03/Percona-Kubernetes-Operators-With-K3s-Monitoring-and-Management-300x157.png)
- content / image: [Percona Monitoring and Management installation](https://www.percona.com/wp-content/uploads/2026/03/image2-2-5-1024x326.png)
- content / image: [PMM installation kubernetes](https://www.percona.com/wp-content/uploads/2026/03/image3-1-7-1024x394.png)
- content / image: [Percona Kubernetes Operator](https://www.percona.com/wp-content/uploads/2026/03/image1-3-2-1024x398.png)
- content / image: [Percona Operators](https://www.percona.com/wp-content/uploads/2026/03/image4-13-1024x370.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
