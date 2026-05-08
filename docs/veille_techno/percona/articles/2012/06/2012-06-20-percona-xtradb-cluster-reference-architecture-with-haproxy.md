---
title: Percona XtraDB Cluster reference architecture with HaProxy
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-xtradb-cluster-reference-architecture-with-haproxy/
  post_id: 3638
source_author:
  name: Peter Boros
  slug: peter-boros
  url: https://www.percona.com/blog/author/peter-boros/
  website: ''
published_at: '2012-06-20T19:50:30'
published_at_gmt: '2012-06-20T19:50:30'
modified_at: '2026-05-04T21:46:30'
modified_at_gmt: '2026-05-04T21:46:30'
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
- MySQL
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- pxc
tag_slugs:
- pxc
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/pxc_haproxy_status_example.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona XtraDB Cluster reference architecture with HaProxy

Source: [Percona Blog](https://www.percona.com/blog/percona-xtradb-cluster-reference-architecture-with-haproxy/)

Auteur source: [Peter Boros](https://www.percona.com/blog/author/peter-boros/)

Publication: 2012-06-20T19:50:30

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This post is a step-by-step guide to set up Percona XtraDB Cluster (PXC) in a virtualized test sandbox. I used Amazon EC2 micro instances, but the content here is applicable for any kind of virtualization technology (for example VirtualBox). The goal is to give step by step instructions, so the setup process is understandable and … Continued

## Images et graphiques reperes

- featured / image: [Percona XtraDB Cluster reference architecture with HaProxy](https://www.percona.com/wp-content/uploads/2026/03/pxc_haproxy_status_example.png)
- content / image: [pxc_haproxy_lb_leastconn.png](https://www.percona.com/wp-content/uploads/2026/03/pxc_haproxy_lb_leastconn.png)
- content / image: [pxc_haproxy_lb_active_backup.png](https://www.percona.com/wp-content/uploads/2026/03/pxc_haproxy_lb_active_backup.png)

## Auteur source

Peter is a Principal Architect at Percona's European consulting team, his special interests are performance tuning and automation for large scale systems. Before Percona, he worked at Zuora, Dropbox, and Sun microsystems, also taught MySQL courses for Oracle University. He currently lives in Debrecen, Hungary with his wife and kids.
