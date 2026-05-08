---
title: Percona Monitoring and Management Setup on Kubernetes with NGINX Ingress for External Databases
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-monitoring-and-management-setup-on-kubernetes-with-nginx-ingress-for-external-databases/
  post_id: 28316
source_author:
  name: Juan Arruti
  slug: juan-arruti
  url: https://www.percona.com/blog/author/juan-arruti/
  website: ''
published_at: '2024-04-10T12:49:04'
published_at_gmt: '2024-04-10T12:49:04'
modified_at: '2026-03-26T20:26:30'
modified_at_gmt: '2026-03-26T20:26:30'
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
- Cloud
- Insight for DBAs
- Monitoring
- MySQL
category_slugs:
- cloud
- insight-for-dbas
- monitoring
- mysql
tags:
- cloud
- Kubernetes
- Monitoring
- Percona Monitoring and Management
tag_slugs:
- cloud
- kubernetes
- monitoring
- percona-monitoring-and-management
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-Monitoring-and-Management-Setup-on-Kubernetes-with-NGINX.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona Monitoring and Management Setup on Kubernetes with NGINX Ingress for External Databases

Source: [Percona Blog](https://www.percona.com/blog/percona-monitoring-and-management-setup-on-kubernetes-with-nginx-ingress-for-external-databases/)

Auteur source: [Juan Arruti](https://www.percona.com/blog/author/juan-arruti/)

Publication: 2024-04-10T12:49:04

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

It’s a common scenario to have a Percona Monitoring and Management (PMM) server running on Kubernetes and also desire to monitor databases that are running outside the Kubernetes cluster. The Ingress NGINX Controller is one of the most popular choices for managing the inbound traffic to K8s. It acts as a reverse proxy and load … Continued

## Structure detectee

- H2: Installing PMM server
- H2: Routing traffic to Kubernetes
- H2: Configuring the PMM client
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Percona Monitoring and Management Setup on Kubernetes with NGINX Ingress for External Databases](https://www.percona.com/wp-content/uploads/2026/03/Percona-Monitoring-and-Management-Setup-on-Kubernetes-with-NGINX.jpg)

## Auteur source

Juan Pablo joined Percona in 2016 as a member of Technical Services Team. Before coming to Percona, he worked as DBA in several companies such as IBM, Turner and Oracle.
