---
title: Deploy Percona Monitoring and Management on Amazon EKS With eksctl and Helm
source:
  name: Percona Blog
  url: https://www.percona.com/blog/deploy-percona-monitoring-and-management-on-amazon-eks-with-eksctl-and-helm/
  post_id: 26750
source_author:
  name: Dev Montiontactic
  slug: mt_admin
  url: https://www.percona.com/blog/author/mt_admin/
  website: ''
published_at: '2023-03-22T12:11:38'
published_at_gmt: '2023-03-22T12:11:38'
modified_at: '2026-04-28T15:06:27'
modified_at_gmt: '2026-04-28T15:06:27'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- category:monitoring:2104
- search:percona-monitoring-and-management
- search:pmm
- tag:percona-monitoring-and-management:2166
categories:
- Cloud
- Monitoring
- Percona Software
category_slugs:
- cloud
- monitoring
- percona-software
tags:
- AWS
- DBaaS
- Kubernetes
- Percona Monitoring and Management
tag_slugs:
- aws
- dbaas
- kubernetes
- percona-monitoring-and-management
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_an_icon_of_an_electronic_cloud_f55d1720-c726-4761-89f2-978718ffcfd9.png
image_count: 5
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Deploy Percona Monitoring and Management on Amazon EKS With eksctl and Helm

Source: [Percona Blog](https://www.percona.com/blog/deploy-percona-monitoring-and-management-on-amazon-eks-with-eksctl-and-helm/)

Auteur source: [Dev Montiontactic](https://www.percona.com/blog/author/mt_admin/)

Publication: 2023-03-22T12:11:38

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

One of the installation methods we support for our database software is through Helm. We have a collection of Helm charts, in this repository, for the following Percona software: Percona Operator for MySQL Percona XtraDB Cluster Percona Operator for MongoDB Percona Server for MongoDB Percona Operator for PostgreSQL Percona … Continued

## Structure detectee

- H2: Requirements
- H2: Create a Kubernetes cluster
- H2: Install PMM with Helm
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Deploy Percona Monitoring and Management on Amazon EKS With eksctl and Helm](https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_an_icon_of_an_electronic_cloud_f55d1720-c726-4761-89f2-978718ffcfd9.png)
- content / image: [MyDBaaS - Kubernetes Cluster Configuration](https://www.percona.com/wp-content/uploads/2026/03/mydbaas.png)
  Caption: Figure 1: MyDBaaS – Kubernetes Cluster Configuration
- content / image: [Instance Type Selector](https://www.percona.com/wp-content/uploads/2026/03/instance-type-selector-1024x605.png)
  Caption: Figure 2: MyDBaaS – Instance Type Selector
- content / image: [eksctl Running](https://percona.community/blog/2022/9/eksctl_running_huc9b9868d7114ba5624169616df63d1a0_337950_1400x0_resize_lanczos_2.png)
  Caption: Figure 3: eksctl Running
- content / image: [PMM running on Kubernetes](https://www.percona.com/wp-content/uploads/2026/03/pmm-kubernetes-1024x784.png)
  Caption: Figure 4: PMM running on Kubernetes
