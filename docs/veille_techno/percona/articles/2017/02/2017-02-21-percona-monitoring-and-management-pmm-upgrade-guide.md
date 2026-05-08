---
title: Percona Monitoring and Management (PMM) Upgrade Guide
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-monitoring-and-management-pmm-upgrade-guide/
  post_id: 16390
source_author:
  name: Barrett Chambers
  slug: barrett-chambers
  url: https://www.percona.com/blog/author/barrett-chambers/
  website: ''
published_at: '2017-02-21T22:53:25'
published_at_gmt: '2017-02-21T22:53:25'
modified_at: '2026-03-26T20:21:42'
modified_at_gmt: '2026-03-26T20:21:42'
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
- tag:pmm:2167
categories:
- Cloud
- MongoDB
- Monitoring
- MySQL
- Percona Software
category_slugs:
- cloud
- mongodb
- monitoring
- mysql
- percona-software
tags:
- Metrics
- MongoDB
- MySQL
- Percona Monitoring and Management
- PMM
- Upgrade
tag_slugs:
- metrics
- mongodb
- mysql
- percona-monitoring-and-management
- pmm
- upgrade
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-Monitoring-and-Management-e1502904228646.png
image_count: 8
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona Monitoring and Management (PMM) Upgrade Guide

Source: [Percona Blog](https://www.percona.com/blog/percona-monitoring-and-management-pmm-upgrade-guide/)

Auteur source: [Barrett Chambers](https://www.percona.com/blog/author/barrett-chambers/)

Publication: 2017-02-21T22:53:25

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This blog post is another in the series on the Percona Server for MongoDB 3.4 bundle release. The purpose of this blog post is to demonstrate current best-practices for an in-place Percona Monitoring and Management (PMM) upgrade. Following this method allows you to retain data previously collected by PMM in your MySQL or MongoDB environment, … Continued

## Structure detectee

- H2: Step 1: Housekeeping
- H2: Step 2: PMM Server Upgrade
- H2: Step 3: PMM Client Upgrade
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Percona Monitoring and Management (PMM) Upgrade Guide](https://www.percona.com/wp-content/uploads/2026/03/Percona-Monitoring-and-Management-e1502904228646.png)
- content / image: [Percona Monitoring and Management](https://www.percona.com/wp-content/uploads/2026/03/Percona-Monitoring-and-Management-e1487036422930.png)
- content / image: [docker_ps](https://www.percona.com/wp-content/uploads/2026/03/versioncheck-scaled.png)
- content / image: [docker_stop](https://www.percona.com/wp-content/uploads/2026/03/stop_and_remove.png)
- content / image: [docker_run](https://www.percona.com/wp-content/uploads/2026/03/new_docker_image-scaled.png)
- content / image: [docker_ps](https://www.percona.com/wp-content/uploads/2026/03/install_verify-scaled.png)
- content / image: [apt-get_update](https://www.percona.com/wp-content/uploads/2026/03/apt-get_update.png)
- content / image: [grafana_graph](https://www.percona.com/wp-content/uploads/2026/03/post_install_graph-scaled.png)

## Auteur source

Barrett Chambers is a Senior Solutions Engineer with Percona. Prior to joining the Percona team, he acquired a range of skills as an application support specialist, operational DBA, and engineer for SaaS Operations and Delivery. Barrett has experience with MySQL, Oracle SQL, MS SQL, PostgreSQL, MariaDB, and MongoDB.
