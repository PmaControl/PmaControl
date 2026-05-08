---
title: Using Loki and Promtail to Display PostgreSQL Logs From a Kubernetes Cluster in PMM
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-loki-and-promtail-to-display-postgresql-logs-from-a-kubernetes-cluster-in-pmm/
  post_id: 29095
source_author:
  name: Fernando Laudares Camargos
  slug: fernando-laudares
  url: https://www.percona.com/blog/author/fernando-laudares/
  website: ''
published_at: '2024-11-19T14:21:54'
published_at_gmt: '2024-11-19T14:21:54'
modified_at: '2026-04-28T22:55:07'
modified_at_gmt: '2026-04-28T22:55:07'
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
categories:
- Cloud
- Monitoring
- Percona Software
- PostgreSQL
category_slugs:
- cloud
- monitoring
- percona-software
- postgresql
tags:
- k8s
- Kuberenetes
- Operator
- PostgreSQL
tag_slugs:
- k8s
- kuberenetes
- operator
- postgresql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Loki-and-Promtail-to-Display-PostgreSQL-Logs.jpg
image_count: 7
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using Loki and Promtail to Display PostgreSQL Logs From a Kubernetes Cluster in PMM

Source: [Percona Blog](https://www.percona.com/blog/using-loki-and-promtail-to-display-postgresql-logs-from-a-kubernetes-cluster-in-pmm/)

Auteur source: [Fernando Laudares Camargos](https://www.percona.com/blog/author/fernando-laudares/)

Publication: 2024-11-19T14:21:54

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This is a follow-up to my colleagues Nickolay and Phong’s Store and Manage Logs of Percona Operator Pods with PMM and Grafana Loki and Agustin’s Turbocharging Percona Monitoring and Management With Loki’s Log-shipping Functionality blog posts. Here, I focus on making PostgreSQL database logs from a Kubernetes cluster deployed with the Percona Operator for PostgreSQL … Continued

## Structure detectee

- H2: Starting point
- H2: Creating the Loki server
- H2: Using a sidecar container to get the PostgreSQL log files
- H2: Configuring Loki as a data source in PMM
- H2: Checking if we can see the PostgreSQL logs from Loki
- H2: Creating a logs dashboard from scratch
- H2: PostgreSQL Logs dashboard

## Images et graphiques reperes

- featured / image: [Using Loki and Promtail to Display PostgreSQL Logs From a Kubernetes Cluster in PMM](https://www.percona.com/wp-content/uploads/2026/03/Loki-and-Promtail-to-Display-PostgreSQL-Logs.jpg)
- content / image: [PostgreSQL-Extensions-Handbook.png](https://www.percona.com/wp-content/uploads/2026/03/PostgreSQL-Extensions-Handbook.png)
- content / image: [Loki and Promtail to Display PostgreSQL](https://www.percona.com/wp-content/uploads/2026/03/Selection_3025.png)
- content / image: [Selection_3026.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_3026.png)
- content / image: [Selection_3027.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_3027.png)
- content / image: [Selection_3028.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_3028.png)
- content / image: [Selection_4119.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_4119.png)

## Auteur source

Fernando Laudares Camargos joined Percona in early 2013 after working 8 years for a Canadian company specialized in offering services based in open source technologies. Fernando's work experience includes the architecture, deployment and maintenance of IT infrastructures based on Linux, open source software and a layer of server virtualization. From the basic services such as DHCP & DNS to identity management systems, but also including backup routines, configuration management tools and thin-clients. He's now focusing on the universe of MySQL, MongoDB and PostgreSQL with a particular interest in understanding the intricacies of database systems and contributes regularly to this blog. You can read his other articles here.
