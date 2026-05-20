---
title: The use of Iptables ClusterIP target as a load balancer for PXC, PRM, MHA and NDB
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-clusterip-load-balancer-pxc-prm-mha/
  post_id: 7470
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2014-01-10T08:00:12'
published_at_gmt: '2014-01-10T08:00:12'
modified_at: '2026-04-28T21:57:11'
modified_at_gmt: '2026-04-28T21:57:11'
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
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- High Availability
- MySQL
- Percona XtraDB Cluster
tag_slugs:
- high-availability
- mysql
- percona-xtradb-cluster
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# The use of Iptables ClusterIP target as a load balancer for PXC, PRM, MHA and NDB

Source: [Percona Blog](https://www.percona.com/blog/using-clusterip-load-balancer-pxc-prm-mha/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2014-01-10T08:00:12

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Most technologies achieving high-availability for MySQL need a load-balancer to spread the client connections to a valid database host, even the Tungsten special connector can be seen as a sophisticated load-balancer. People often use hardware load balancer or software solution like haproxy. In both cases, in order to avoid having a single point of failure, … Continued

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
