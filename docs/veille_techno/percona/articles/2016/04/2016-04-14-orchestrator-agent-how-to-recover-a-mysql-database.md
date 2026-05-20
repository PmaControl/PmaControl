---
title: 'Orchestrator-agent: How to recover a MySQL database'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/orchestrator-agent-how-to-recover-a-mysql-database/
  post_id: 14849
source_author:
  name: Tibor Korocz
  slug: tibor-koroczpercona-com
  url: https://www.percona.com/blog/author/tibor-koroczpercona-com/
  website: ''
published_at: '2016-04-14T00:42:22'
published_at_gmt: '2016-04-14T00:42:22'
modified_at: '2026-05-05T19:28:20'
modified_at_gmt: '2026-05-05T19:28:20'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-xtrabackup
- search:xtrabackup
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- MySQL
- orchestrator
- orchestrator-agent
tag_slugs:
- mysql
- orchestrator
- orchestrator-agent
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/orchestrator-agent.jpg
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Orchestrator-agent: How to recover a MySQL database

Source: [Percona Blog](https://www.percona.com/blog/orchestrator-agent-how-to-recover-a-mysql-database/)

Auteur source: [Tibor Korocz](https://www.percona.com/blog/author/tibor-koroczpercona-com/)

Publication: 2016-04-14T00:42:22

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In our previous post, we showed how Orchestrator can handle complex replication topologies. Today we will discuss how the Orchestrator-agent complements Orchestrator by monitoring our servers, and provides us a snapshot and recovery abilities if there are problems. Please be aware that the following scripts and settings in this post are not production ready (missing … Continued

## Structure detectee

- H2: What is Orchestrator-agent?
- H2: How does it work?
- H2: Orchestrator-agent configuration settings
- H2: Example external scripts
- H2: Job details
- H2: Why do we need Orchestrator-agent?
- H2: Features requests
- H2: Summary

## Images et graphiques reperes

- featured / image: [Orchestrator-agent: How to recover a MySQL database](https://www.percona.com/wp-content/uploads/2026/03/orchestrator-agent.jpg)
- content / image: [orchestrator-agent](https://www.percona.com/wp-content/uploads/2026/03/orchestrator-agent-300x200.jpg)
- content / image: [Screen Shot 2016-03-28 at 12.55.43](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2016-03-28-at-12.55.43-scaled.png)
- content / image: [Screen Shot 2016-03-28 at 16.40.15](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2016-03-28-at-16.40.15-scaled.png)

## Auteur source

Tibi joined Percona in 2015 as a Consultant. Before joining Percona, among many other things, he worked at the world’s largest car hire booking service as a Senior Database Engineer. He enjoys trying and working with the latest technologies and applications which can help or work with MySQL together. In his spare time he likes to spend time with his friends, travel around the world and play ultimate frisbee.
