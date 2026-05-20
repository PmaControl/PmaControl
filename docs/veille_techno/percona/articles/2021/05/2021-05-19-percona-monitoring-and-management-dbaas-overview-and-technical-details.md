---
title: Percona Monitoring and Management DBaaS Overview and Technical Details
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-monitoring-and-management-dbaas-overview-and-technical-details/
  post_id: 24336
source_author:
  name: Dev Montiontactic
  slug: mt_admin
  url: https://www.percona.com/blog/author/mt_admin/
  website: ''
published_at: '2021-05-19T20:22:19'
published_at_gmt: '2021-05-19T20:22:19'
modified_at: '2026-03-26T20:15:21'
modified_at_gmt: '2026-03-26T20:15:21'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:monitoring:2104
- category:mysql:83
- search:percona-monitoring-and-management
- search:pmm
- tag:percona-monitoring-and-management:2166
categories:
- MongoDB
- Monitoring
- MySQL
- Percona Software
category_slugs:
- mongodb
- monitoring
- mysql
- percona-software
tags:
- cloud
- DBaaS
- Kubernetes Operator
- MongoDB
- mysql-and-variants
- Percona Monitoring and Management
tag_slugs:
- cloud
- dbaas
- kubernetes-operator
- mongodb
- mysql-and-variants
- percona-monitoring-and-management
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-Monitoring-and-Management-DBaaS-Overview.png
image_count: 12
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona Monitoring and Management DBaaS Overview and Technical Details

Source: [Percona Blog](https://www.percona.com/blog/percona-monitoring-and-management-dbaas-overview-and-technical-details/)

Auteur source: [Dev Montiontactic](https://www.percona.com/blog/author/mt_admin/)

Publication: 2021-05-19T20:22:19

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Database-as-a-Service (DBaaS) is a managed database that doesn’t need to be installed and maintained but is instead provided as a service to the user. The Percona Monitoring and Management (PMM) DBaaS component allows users to CRUD (Create, Read, Update, Delete) Percona XtraDB Cluster (PXC) and Percona Server for MongoDB (PSMDB) managed databases in Kubernetes clusters. … Continued

## Structure detectee

- H2: Deploy Playground with minikube
- H2: Configure PMM DBaaS
- H2: Deploy PSMDB with DBaaS
- H3: PMM API
- H2: Create DB and Deep Dive
- H2: Summary
- H3: P.S.

## Images et graphiques reperes

- featured / image: [Percona Monitoring and Management DBaaS Overview and Technical Details](https://www.percona.com/wp-content/uploads/2026/03/Percona-Monitoring-and-Management-DBaaS-Overview.png)
- content / image: [Percona Monitoring and Management DBaaS Overview](https://www.percona.com/wp-content/uploads/2026/03/Percona-Monitoring-and-Management-DBaaS-Overview-300x168.png)
- content / graph_or_chart: [DBaaS Dashboard](https://www.percona.com/wp-content/uploads/2026/03/Screenshot_2021-05-10-DBaaS-Percona-Monitoring-and-Management3-1024x231.png)
- content / image: [PMM Advanced settings](https://www.percona.com/wp-content/uploads/2026/03/Screenshot_2021-05-10-Settings-Percona-Monitoring-and-Management-1024x588.png)
- content / image: [DBaaS Register k8s Cluster](https://www.percona.com/wp-content/uploads/2026/03/Screenshot_2021-05-10-DBaaS-Percona-Monitoring-and-Management4.png)
- content / image: [Cluster with PSMDB](https://www.percona.com/wp-content/uploads/2026/03/Screenshot_2021-05-10-DBaaS-Percona-Monitoring-and-Management2-1024x206.png)
- content / image: [Swagger API](https://www.percona.com/wp-content/uploads/2026/03/Screenshot_2021-05-10-Swagger-UI1-1024x223.png)
- content / image: [PMM Swagger API Example](https://www.percona.com/wp-content/uploads/2026/03/Screenshot_2021-05-10-Swagger-UI-888x1024.png)
- content / image: [Create MongoDB cluster](https://www.percona.com/wp-content/uploads/2026/03/Screenshot_2021-05-10-DBaaS-Percona-Monitoring-and-Management.png)
- content / image: [Advanced settings for cluster creation](https://www.percona.com/wp-content/uploads/2026/03/Screenshot_2021-05-10-DBaaS-Percona-Monitoring-and-Management5.png)
- content / image: [PSMDB Cluster created](https://www.percona.com/wp-content/uploads/2026/03/Screenshot_2021-05-10-DBaaS-Percona-Monitoring-and-Management1-1024x126.png)
- content / image: [PMM PSMDB overview](https://www.percona.com/wp-content/uploads/2026/03/Screenshot_2021-05-10-MongoDB-Instances-Overview-Percona-Monitoring-and-Management-1024x833.png)
