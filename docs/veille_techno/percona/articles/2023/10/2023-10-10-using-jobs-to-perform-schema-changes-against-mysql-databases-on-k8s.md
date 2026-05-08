---
title: Using Jobs to Perform Schema Changes Against MySQL Databases on K8s
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-jobs-to-perform-schema-changes-against-mysql-databases-on-k8s/
  post_id: 27323
source_author:
  name: Ananias Tsalouchidis
  slug: ananias-tsalouchidis
  url: https://www.percona.com/blog/author/ananias-tsalouchidis/
  website: ''
published_at: '2023-10-10T12:02:54'
published_at_gmt: '2023-10-10T12:02:54'
modified_at: '2026-03-26T20:27:07'
modified_at_gmt: '2026-03-26T20:27:07'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
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
- MySQL
- mysql-and-variants
tag_slugs:
- cloud
- kubernetes
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_the_texture_inside_software_navy_blue_colors_efd306b2-c70a-4d87-8992-04bf3da703fd.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using Jobs to Perform Schema Changes Against MySQL Databases on K8s

Source: [Percona Blog](https://www.percona.com/blog/using-jobs-to-perform-schema-changes-against-mysql-databases-on-k8s/)

Auteur source: [Ananias Tsalouchidis](https://www.percona.com/blog/author/ananias-tsalouchidis/)

Publication: 2023-10-10T12:02:54

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Performing an operation is always challenging when dealing with K8s. When on-prem or DBaaS like RDS or Cloud SQL, it is relatively straightforward to apply a change. You can perform a DIRECT ALTER, use a tool such as pt-osc, or even, for certain cases where async replication is in use, perform changes on replicas and … Continued

## Images et graphiques reperes

- featured / image: [Using Jobs to Perform Schema Changes Against MySQL Databases on K8s](https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_the_texture_inside_software_navy_blue_colors_efd306b2-c70a-4d87-8992-04bf3da703fd.png)

## Auteur source

Ananias is a Principal MySQL DBA who joined Percona on May 2017. He holds a BSc and a MSc in computer science and has a 10+ years working experience as a systems and databases administrator. He loves databases and perl scripting. He has worked for big companies and academic institutions and has also been involved into numerous research programs.
