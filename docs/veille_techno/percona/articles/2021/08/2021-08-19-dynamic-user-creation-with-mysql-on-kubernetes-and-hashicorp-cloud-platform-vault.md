---
title: Dynamic User Creation with MySQL on Kubernetes and Hashicorp Cloud Platform Vault
source:
  name: Percona Blog
  url: https://www.percona.com/blog/dynamic-user-creation-with-mysql-on-kubernetes-and-hashicorp-cloud-platform-vault/
  post_id: 24752
source_author:
  name: Sergey Pronin
  slug: sergey-pronin
  url: https://www.percona.com/blog/author/sergey-pronin/
  website: ''
published_at: '2021-08-19T15:07:54'
published_at_gmt: '2021-08-19T15:07:54'
modified_at: '2026-04-28T14:58:46'
modified_at_gmt: '2026-04-28T14:58:46'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- ProxySQL
matched_filters:
- category:mysql:83
- search:proxysql
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
- Kubernetes
- Kubernetes Operator
- MySQL
- mysql-and-variants
- Percona Software
tag_slugs:
- cloud
- kubernetes
- kubernetes-operator
- mysql
- mysql-and-variants
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Kubernetes-Hashicorp-Cloud.png
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Dynamic User Creation with MySQL on Kubernetes and Hashicorp Cloud Platform Vault

Source: [Percona Blog](https://www.percona.com/blog/dynamic-user-creation-with-mysql-on-kubernetes-and-hashicorp-cloud-platform-vault/)

Auteur source: [Sergey Pronin](https://www.percona.com/blog/author/sergey-pronin/)

Publication: 2021-08-19T15:07:54

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

You may have already seen this document which describes the integration between HashiCorp Vault and Percona Distribution for MySQL Operator to enable data-at-rest encryption for self-managed Vault deployments. In April 2021, HashiCorp announced a fully managed offering, HashiCorp Cloud Platform Vault (HCP Vault), that simplifies deployment and management of the Vault. With that in mind, … Continued

## Structure detectee

- H2: Goal
- H2: Before You Begin
- H3: Prerequisites
- H3: Networking
- H2: Set it All Up
- H3: MySQL
- H3: Create the User and the Database
- H2: Hashicorp Cloud Platform Vault
- H3: Vault CLI
- H2: Connecting the Dots
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Dynamic User Creation with MySQL on Kubernetes and Hashicorp Cloud Platform Vault](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Kubernetes-Hashicorp-Cloud.png)
- content / image: [MySQL cluster deployed in Kubernetes with dynamic credentials through Hashicorp Vault](https://www.percona.com/wp-content/uploads/2026/03/blog-hcp-0.png)
- content / image: [blog-hcp-1-1024x395.png](https://www.percona.com/wp-content/uploads/2026/03/blog-hcp-1-1024x395.png)
- content / image: [vault cluster](https://www.percona.com/wp-content/uploads/2026/03/blog-hcp-2-1024x227.png)
- content / image: [configure cluster](https://www.percona.com/wp-content/uploads/2026/03/blog-hcp-3.png)
- content / image: [blog-hcp-4.png](https://www.percona.com/wp-content/uploads/2026/03/blog-hcp-4.png)

## Auteur source

Sergey is a product leader at Percona focusing on delivering robust open-source database and cloud-native solutions. Prior to Percona Sergey led product management and engineering teams in other organizations with a primary focus on products in infrastructure and platforms space.
