---
title: Percona Operator Volume Expansion Without Downtime
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-operator-volume-expansion-without-downtime/
  post_id: 25738
source_author:
  name: Natalia Marukovich
  slug: natalia-marukovich
  url: https://www.percona.com/blog/author/natalia-marukovich/
  website: ''
published_at: '2022-07-06T13:05:03'
published_at_gmt: '2022-07-06T13:05:03'
modified_at: '2026-03-26T20:31:41'
modified_at_gmt: '2026-03-26T20:31:41'
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
- MongoDB
- MySQL
- Percona Software
- PostgreSQL
category_slugs:
- cloud
- mongodb
- mysql
- percona-software
- postgresql
tags:
- cloud
- Kubernetes
- MongoDB
- MySQL
tag_slugs:
- cloud
- kubernetes
- mongodb
- mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-Operator-Volume-Expansion-Without-Downtime.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona Operator Volume Expansion Without Downtime

Source: [Percona Blog](https://www.percona.com/blog/percona-operator-volume-expansion-without-downtime/)

Auteur source: [Natalia Marukovich](https://www.percona.com/blog/author/natalia-marukovich/)

Publication: 2022-07-06T13:05:03

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

There are several ways to manage storage in Percona Kubernetes Operators: Persistent Volume (PV), hostPath, ephemeral storage, etc. Using PVs, which are provisioned by the Operator through Storage Classes and Persistent Volume Claims, is the most popular choice for our users. And one of the most popular questions is how to scale our operator storages … Continued

## Structure detectee

- H2: Scale-up persistent volume claim (PVC) by volume expansion
- H3: Percona Operator for MongoDB/Percona Operator for MySQL
- H4: Resizing a PV claimed by changing custom resource or StatefulSets (Does not work)
- H4: Expansion storage by modifying persistent volume claim (PVC)
- H3: Percona Operator for PostgreSQL (pg operator)
- H2: Resizing persistent volume claim (PVC) by transferring data to new PVC
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Percona Operator Volume Expansion Without Downtime](https://www.percona.com/wp-content/uploads/2026/03/Percona-Operator-Volume-Expansion-Without-Downtime.png)
- content / image: [Percona Operator Volume Expansion Without Downtime](https://www.percona.com/wp-content/uploads/2026/03/Percona-Operator-Volume-Expansion-Without-Downtime-300x157.png)

## Auteur source

Devops/QA engineer at Percona.
