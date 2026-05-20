---
title: Least Privilege for Kubernetes Resources and Percona Operators
source:
  name: Percona Blog
  url: https://www.percona.com/blog/least-privilege-for-kubernetes-resources-and-percona-operators/
  post_id: 26388
source_author:
  name: Sergey Pronin
  slug: sergey-pronin
  url: https://www.percona.com/blog/author/sergey-pronin/
  website: ''
published_at: '2022-12-15T12:58:46'
published_at_gmt: '2022-12-15T12:58:46'
modified_at: '2026-03-26T20:30:25'
modified_at_gmt: '2026-03-26T20:30:25'
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
- Security
category_slugs:
- cloud
- mysql
- percona-software
- security
tags:
- cloud-native
- containers
- Kubernetes
- MySQL
- mysql-and-variants
- operators
- rbac
- security
tag_slugs:
- cloud-native
- containers
- kubernetes
- mysql
- mysql-and-variants
- operators
- rbac
- security
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Least-Privilege-for-Kubernetes-1.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Least Privilege for Kubernetes Resources and Percona Operators

Source: [Percona Blog](https://www.percona.com/blog/least-privilege-for-kubernetes-resources-and-percona-operators/)

Auteur source: [Sergey Pronin](https://www.percona.com/blog/author/sergey-pronin/)

Publication: 2022-12-15T12:58:46

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Operators hide the complexity of the application and Kubernetes. Instead of dealing with Pods, StatefulSets, tons of YAML manifests, and various configuration files, the user talks to Kubernetes API to provision a ready-to-use application. An Operator automatically provisions all the required resources and exposes the application. Though, there is always a risk that the user … Continued

## Structure detectee

- H2: The goal
- H2: Action
- H2: Administrator
- H2: Developer
- H3: Verify
- H3: Apply
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Least Privilege for Kubernetes Resources and Percona Operators](https://www.percona.com/wp-content/uploads/2026/03/Least-Privilege-for-Kubernetes-1.png)
- content / image: [Least Privilege for Kubernetes](https://www.percona.com/wp-content/uploads/2026/03/leastpriv_blog_0-1024x680.png)

## Auteur source

Sergey is a product leader at Percona focusing on delivering robust open-source database and cloud-native solutions. Prior to Percona Sergey led product management and engineering teams in other organizations with a primary focus on products in infrastructure and platforms space.
