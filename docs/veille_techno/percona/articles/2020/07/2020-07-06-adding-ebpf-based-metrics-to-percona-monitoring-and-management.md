---
title: Adding eBPF-Based Metrics to Percona Monitoring and Management
source:
  name: Percona Blog
  url: https://www.percona.com/blog/adding-ebpf-based-metrics-to-percona-monitoring-and-management/
  post_id: 22632
source_author:
  name: Sergey Kuzmichev
  slug: sergey-kuzmichev
  url: https://www.percona.com/blog/author/sergey-kuzmichev/
  website: https://www.percona.com
published_at: '2020-07-06T16:11:13'
published_at_gmt: '2020-07-06T16:11:13'
modified_at: '2026-04-27T21:40:00'
modified_at_gmt: '2026-04-27T21:40:00'
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
- Insight for DBAs
- Monitoring
- Percona Software
category_slugs:
- insight-for-dbas
- monitoring
- percona-software
tags:
- insight for DBAs
- Monitoring
- Percona Monitoring and Management
- Percona Software
tag_slugs:
- insight-for-dbas
- monitoring
- percona-monitoring-and-management
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/ebpf-percona-monitoring-and-management.png
image_count: 5
graph_or_chart_count: 3
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Adding eBPF-Based Metrics to Percona Monitoring and Management

Source: [Percona Blog](https://www.percona.com/blog/adding-ebpf-based-metrics-to-percona-monitoring-and-management/)

Auteur source: [Sergey Kuzmichev](https://www.percona.com/blog/author/sergey-kuzmichev/)

Publication: 2020-07-06T16:11:13

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I wanted to start this post with the words “eBPF is the hot new thing”, but I think it’s already too late to write that. Running eBPF programs in production is becoming less of a peculiarity and more of a normal way to operate. Famously, Facebook runs about 40 BPF programs on each server. There … Continued

## Structure detectee

- H2: Technologies
- H2: Setting up the Environment
- H2: Setting up Grafana Dashboard
- H2: Testing to See We Got The Right Data
- H3: Summary

## Images et graphiques reperes

- featured / image: [Adding eBPF-Based Metrics to Percona Monitoring and Management](https://www.percona.com/wp-content/uploads/2026/03/ebpf-percona-monitoring-and-management.png)
- content / image: [ebpf percona monitoring and management](https://www.percona.com/wp-content/uploads/2026/03/ebpf-percona-monitoring-and-management-300x168.png)
- content / graph_or_chart: [prometheus dashboard showing newly-added ebpf metrics](https://www.percona.com/wp-content/uploads/2026/03/pasted-image-0-2-1024x509.png)
- content / graph_or_chart: [Grafana Dashboard](https://www.percona.com/wp-content/uploads/2026/03/screencapture-192-168-70-110-graph-d-eaDORvzMz-ebpf-exporter-panels-2020-06-25-14_26_05-1024x1006.png)
- content / graph_or_chart: [eBPF dashboard](https://www.percona.com/wp-content/uploads/2026/03/screencapture-192-168-70-110-graph-d-eaDORvzMz-ebpf-exporter-panels-2020-06-25-15_49_11-800x1024.png)

## Auteur source

Sergey is a support engineer in Percona. Interested in all things databases, he's currently working mainly with MySQL and PostgreSQL. He started his career working as an Oracle DBA, later moving to a DevOps engineer role supporting Java-based trading platform running on PostgreSQL. After being a jack of all trades for a while, he's now focusing on what he enjoys most: open source databases, systems performance, and reliability.
