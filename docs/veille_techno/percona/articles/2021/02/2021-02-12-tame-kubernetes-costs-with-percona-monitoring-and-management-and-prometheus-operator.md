---
title: Tame Kubernetes Costs with Percona Monitoring and Management and Prometheus Operator
source:
  name: Percona Blog
  url: https://www.percona.com/blog/tame-kubernetes-costs-with-percona-monitoring-and-management-and-prometheus-operator/
  post_id: 23947
source_author:
  name: Sergey Pronin
  slug: sergey-pronin
  url: https://www.percona.com/blog/author/sergey-pronin/
  website: ''
published_at: '2021-02-12T17:23:54'
published_at_gmt: '2021-02-12T17:23:54'
modified_at: '2026-04-27T22:23:13'
modified_at_gmt: '2026-04-27T22:23:13'
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
- Open Source
- Percona Software
category_slugs:
- cloud
- monitoring
- open-source
- percona-software
tags:
- cloud
- containers
- DBaaS
- Kubernetes
- Monitoring
- Percona Monitoring and Management
- Percona Software
tag_slugs:
- cloud
- containers
- dbaas
- kubernetes
- monitoring
- percona-monitoring-and-management
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Kubernetes-Costs-Percona-Monitoring-and-Management.png
image_count: 9
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Tame Kubernetes Costs with Percona Monitoring and Management and Prometheus Operator

Source: [Percona Blog](https://www.percona.com/blog/tame-kubernetes-costs-with-percona-monitoring-and-management-and-prometheus-operator/)

Auteur source: [Sergey Pronin](https://www.percona.com/blog/author/sergey-pronin/)

Publication: 2021-02-12T17:23:54

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

More and more companies are adopting Kubernetes, but after some time they see an unexpected growth around cloud costs. Engineering teams did their part in setting up auto-scalers, but the cloud bill is still growing. Today we are going to see how Percona Monitoring and Management (PMM) can help with monitoring Kubernetes and reducing the … Continued

## Structure detectee

- H2: Get the Metrics
- H3: Overview
- H3: PMM-server
- H3: Prometheus Operator
- H3: Check
- H2: Monitor the Costs
- H3: Dashboard #1 – Cluster Overview
- H3: Dashboard #2 – Namespace and Pod
- H4: Summary

## Images et graphiques reperes

- featured / image: [Tame Kubernetes Costs with Percona Monitoring and Management and Prometheus Operator](https://www.percona.com/wp-content/uploads/2026/03/Kubernetes-Costs-Percona-Monitoring-and-Management.png)
- content / image: [Kubernetes Costs Percona Monitoring and Management](https://www.percona.com/wp-content/uploads/2026/03/Kubernetes-Costs-Percona-Monitoring-and-Management-300x168.png)
- content / image: [Prometheus Operator](https://www.percona.com/wp-content/uploads/2026/03/blog-Page-1-1-1024x670.png)
- content / image: [PMM Server UI](https://www.percona.com/wp-content/uploads/2026/03/explore.png)
- content / graph_or_chart: [dashboards in PMM](https://www.percona.com/wp-content/uploads/2026/03/import-dashboard.png)
- content / image: [Cluster Overview](https://www.percona.com/wp-content/uploads/2026/03/k8s_summary-1024x258.png)
- content / image: [CPU/Mem Request/Limit/Capacity section](https://www.percona.com/wp-content/uploads/2026/03/resources_graph-1024x258.png)
- content / image: [Namespace and Pod](https://www.percona.com/wp-content/uploads/2026/03/namespaces-1024x292.png)
- content / image: [Pod CPU Usage](https://www.percona.com/wp-content/uploads/2026/03/pod_utilization-1024x299.png)

## Auteur source

Sergey is a product leader at Percona focusing on delivering robust open-source database and cloud-native solutions. Prior to Percona Sergey led product management and engineering teams in other organizations with a primary focus on products in infrastructure and platforms space.
