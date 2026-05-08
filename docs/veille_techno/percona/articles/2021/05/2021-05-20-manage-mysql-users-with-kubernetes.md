---
title: Manage MySQL Users with Kubernetes
source:
  name: Percona Blog
  url: https://www.percona.com/blog/manage-mysql-users-with-kubernetes/
  post_id: 24378
source_author:
  name: Sergey Pronin
  slug: sergey-pronin
  url: https://www.percona.com/blog/author/sergey-pronin/
  website: ''
published_at: '2021-05-20T20:29:07'
published_at_gmt: '2021-05-20T20:29:07'
modified_at: '2026-04-27T22:27:37'
modified_at_gmt: '2026-04-27T22:27:37'
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
- Open Source
- Percona Software
category_slugs:
- cloud
- mysql
- open-source
- percona-software
tags:
- cloud
- containers
- Kubernetes
- Kubernetes Operator
- MySQL
- mysql-and-variants
- Percona Software
tag_slugs:
- cloud
- containers
- kubernetes
- kubernetes-operator
- mysql
- mysql-and-variants
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Manage-MySQL-Users-with-Kubernetes.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Manage MySQL Users with Kubernetes

Source: [Percona Blog](https://www.percona.com/blog/manage-mysql-users-with-kubernetes/)

Auteur source: [Sergey Pronin](https://www.percona.com/blog/author/sergey-pronin/)

Publication: 2021-05-20T20:29:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Quite a common request that we receive from the community and customers is to provide a way to manage database users with Operators – both MongoDB and MySQL. Even though we see it as an interesting task, our Operators are mainly a tool to simplify the deployment and management of our software on Kubernetes. Our … Continued

## Structure detectee

- H2: Why Manage Users with Operators?
- H2: Action
- H2: Install crossplane
- H2: Install provider-sql
- H2: Almost There
- H2: Do It
- H3: Database Creation
- H3: User Creation
- H2: Keeping the State
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Manage MySQL Users with Kubernetes](https://www.percona.com/wp-content/uploads/2026/03/Manage-MySQL-Users-with-Kubernetes.png)
- content / image: [Manage MySQL Users with Kubernetes](https://www.percona.com/wp-content/uploads/2026/03/Manage-MySQL-Users-with-Kubernetes-300x168.png)
- content / image: [Manage MySQL Users with Kubernetes](https://www.percona.com/wp-content/uploads/2026/03/crossplane-provider-sql-1024x489.png)

## Auteur source

Sergey is a product leader at Percona focusing on delivering robust open-source database and cloud-native solutions. Prior to Percona Sergey led product management and engineering teams in other organizations with a primary focus on products in infrastructure and platforms space.
