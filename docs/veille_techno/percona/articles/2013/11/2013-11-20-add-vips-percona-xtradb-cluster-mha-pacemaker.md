---
title: How to add VIPs to Percona XtraDB Cluster or MHA with Pacemaker
source:
  name: Percona Blog
  url: https://www.percona.com/blog/add-vips-percona-xtradb-cluster-mha-pacemaker/
  post_id: 7556
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2013-11-20T18:35:21'
published_at_gmt: '2013-11-20T18:35:21'
modified_at: '2026-05-05T16:51:59'
modified_at_gmt: '2026-05-05T16:51:59'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- mysql_monitor
- mysql_prm
- Pacemaker
- Percona XtraDB Cluster
- VIPs
- Yves Trudeau
tag_slugs:
- mysql_monitor
- mysql_prm
- pacemaker
- percona-xtradb-cluster
- vips
- yves-trudeau
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to add VIPs to Percona XtraDB Cluster or MHA with Pacemaker

Source: [Percona Blog](https://www.percona.com/blog/add-vips-percona-xtradb-cluster-mha-pacemaker/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2013-11-20T18:35:21

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

It is a rather frequent problem to have to manage Virtual IP addresses (VIPs) with a Percona XtraDB Cluster (PXC) or with MySQL master HA (MHA). In order to help solving these problems, I wrote a Pacemaker agent, mysql_monitor that is a simplified version of the mysql_prm agent. The mysql_monitor agent only monitors MySQL and … Continued

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
