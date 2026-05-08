---
title: Change Storage Class on Kubernetes on the Fly
source:
  name: Percona Blog
  url: https://www.percona.com/blog/change-storage-class-on-kubernetes-on-the-fly/
  post_id: 24240
source_author:
  name: Sergey Pronin
  slug: sergey-pronin
  url: https://www.percona.com/blog/author/sergey-pronin/
  website: ''
published_at: '2021-04-20T19:07:24'
published_at_gmt: '2021-04-20T19:07:24'
modified_at: '2026-05-05T22:42:48'
modified_at_gmt: '2026-05-05T22:42:48'
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
tags:
- cloud
- containers
- Kubernetes
- Kubernetes Operator
- MySQL
- mysql-and-variants
- Percona Software
- pxc
tag_slugs:
- cloud
- containers
- kubernetes
- kubernetes-operator
- mysql
- mysql-and-variants
- percona-software
- pxc
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Change-Storage-Class-Kubernetes.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Change Storage Class on Kubernetes on the Fly

Source: [Percona Blog](https://www.percona.com/blog/change-storage-class-on-kubernetes-on-the-fly/)

Auteur source: [Sergey Pronin](https://www.percona.com/blog/author/sergey-pronin/)

Publication: 2021-04-20T19:07:24

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona Kubernetes Operators support various options for storage: Persistent Volume (PV), hostPath, ephemeral storage, etc. In most of the cases, PVs are used, which are provisioned by the Operator through Storage Classes and Persistent Volume Claims. Storage Classes define the underlying volume type that should be used (ex. AWS, EBS, gp2, or io1), file system … Continued

## Structure detectee

- H2: Changing Storage Class on Kubernetes
- H2: Planning
- H2: Execution
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Change Storage Class on Kubernetes on the Fly](https://www.percona.com/wp-content/uploads/2026/03/Change-Storage-Class-Kubernetes.png)
- content / image: [Change Storage Class Kubernetes](https://www.percona.com/wp-content/uploads/2026/03/Change-Storage-Class-Kubernetes-300x168.png)
- content / image: [Change Storage Class on Kubernetes](https://www.percona.com/wp-content/uploads/2026/03/Change-Storage-Class-on-Kubernetes-1024x346.png)

## Auteur source

Sergey is a product leader at Percona focusing on delivering robust open-source database and cloud-native solutions. Prior to Percona Sergey led product management and engineering teams in other organizations with a primary focus on products in infrastructure and platforms space.
