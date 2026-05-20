---
title: Using pt-table-checksum with Percona XtraDB Cluster
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-pt-table-checksum-with-percona-xtradb-cluster/
  post_id: 3819
source_author:
  name: Daniel Nichter
  slug: daniel
  url: https://www.percona.com/blog/author/daniel/
  website: http://www.percona.com
published_at: '2012-10-15T15:55:28'
published_at_gmt: '2012-10-15T15:55:28'
modified_at: '2026-05-04T21:52:08'
modified_at_gmt: '2026-05-04T21:52:08'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- Percona Toolkit
matched_filters:
- search:percona-toolkit
- tag:percona-toolkit:378
categories:
- Insight for DBAs
- Percona Software
category_slugs:
- insight-for-dbas
- percona-software
tags:
- Percona Toolkit
- pt-table-checksum
tag_slugs:
- percona-toolkit
- pt-table-checksum
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using pt-table-checksum with Percona XtraDB Cluster

Source: [Percona Blog](https://www.percona.com/blog/using-pt-table-checksum-with-percona-xtradb-cluster/)

Auteur source: [Daniel Nichter](https://www.percona.com/blog/author/daniel/)

Publication: 2012-10-15T15:55:28

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

As of Percona Toolkit v2.1.5, pt-table-checksum works correctly with Percona XtraDB Cluster, but it doesn’t work quite like a traditional replication setup because cluster nodes are not like traditional replicas. In this post I demonstrate how to use pt-table-checksum with Percona XtraDB Cluster. First, you’ll need Percona Toolkit v2.1.5 or newer and Percona XtraDB Cluster 5.5.27-23.6 … Continued
