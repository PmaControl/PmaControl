---
title: Expose Databases on Kubernetes with Ingress
source:
  name: Percona Blog
  url: https://www.percona.com/blog/expose-databases-on-kubernetes-with-ingress/
  post_id: 25584
source_author:
  name: Sergey Pronin
  slug: sergey-pronin
  url: https://www.percona.com/blog/author/sergey-pronin/
  website: ''
published_at: '2022-04-18T13:14:37'
published_at_gmt: '2022-04-18T13:14:37'
modified_at: '2026-03-26T20:31:58'
modified_at_gmt: '2026-03-26T20:31:58'
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
- containers
- database management
- Kubernetes
- Kubernetes Operator
- MySQL
- mysql-and-variants
tag_slugs:
- containers
- database-management
- kubernetes
- kubernetes-operator
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Expose-Databases-on-Kubernetes-with-Ingress.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Expose Databases on Kubernetes with Ingress

Source: [Percona Blog](https://www.percona.com/blog/expose-databases-on-kubernetes-with-ingress/)

Auteur source: [Sergey Pronin](https://www.percona.com/blog/author/sergey-pronin/)

Publication: 2022-04-18T13:14:37

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Ingress is a resource that is commonly used to expose HTTP(s) services outside of Kubernetes. To have ingress support, you will need an Ingress Controller, which in a nutshell is a proxy. SREs and DevOps love ingress as it provides developers with a self-service to expose their applications. Developers love it as it is simple … Continued

## Structure detectee

- H2: TCP and UDP with Ingress
- H2: Hands-on
- H2: Deploy Percona XtraDB Clusters (PXC)
- H2: Deploy Ingress
- H2: Check the Connection
- H2: Adding More Clusters
- H2: Limitations and considerations
- H3: Ports per Load Balancer
- H3: Automation

## Images et graphiques reperes

- featured / image: [Expose Databases on Kubernetes with Ingress](https://www.percona.com/wp-content/uploads/2026/03/Expose-Databases-on-Kubernetes-with-Ingress.png)
- content / image: [High-level ingress design](https://www.percona.com/wp-content/uploads/2026/03/blog-ingress-0.png)
- content / image: [TCP and UDP with Ingress](https://www.percona.com/wp-content/uploads/2026/03/blog-ingress-1.png)
- content / image: [blog-ingress-2.png](https://www.percona.com/wp-content/uploads/2026/03/blog-ingress-2.png)

## Auteur source

Sergey is a product leader at Percona focusing on delivering robust open-source database and cloud-native solutions. Prior to Percona Sergey led product management and engineering teams in other organizations with a primary focus on products in infrastructure and platforms space.
