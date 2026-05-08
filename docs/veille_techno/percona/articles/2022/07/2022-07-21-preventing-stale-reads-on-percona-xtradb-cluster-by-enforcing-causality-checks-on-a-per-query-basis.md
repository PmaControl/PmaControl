---
title: Preventing Stale Reads on Percona XtraDB Cluster by Enforcing Causality Checks on a Per-Query Basis
source:
  name: Percona Blog
  url: https://www.percona.com/blog/preventing-stale-reads-on-percona-xtradb-cluster-by-enforcing-causality-checks-on-a-per-query-basis/
  post_id: 25839
source_author:
  name: Fernando Laudares Camargos
  slug: fernando-laudares
  url: https://www.percona.com/blog/author/fernando-laudares/
  website: ''
published_at: '2022-07-21T13:23:42'
published_at_gmt: '2022-07-21T13:23:42'
modified_at: '2026-03-26T20:31:32'
modified_at_gmt: '2026-03-26T20:31:32'
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
- MySQL
- mysql-and-variants
- Percona XtraDB Cluster
- pxc
tag_slugs:
- mysql
- mysql-and-variants
- percona-xtradb-cluster
- pxc
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Stale-Reads-on-Percona-XtraDB-Cluster.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Preventing Stale Reads on Percona XtraDB Cluster by Enforcing Causality Checks on a Per-Query Basis

Source: [Percona Blog](https://www.percona.com/blog/preventing-stale-reads-on-percona-xtradb-cluster-by-enforcing-causality-checks-on-a-per-query-basis/)

Auteur source: [Fernando Laudares Camargos](https://www.percona.com/blog/author/fernando-laudares/)

Publication: 2022-07-21T13:23:42

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

When we run a SELECT in a replica server and it returns a different result to the one we would have obtained had we queried the source server instead, due to changes in the dataset that have not yet been replicated or synchronized to the replica, we get what is known as a stale read. … Continued

## Structure detectee

- H2: Evolution of “synchronous” replication on PXC
- H2: The cherry on top

## Images et graphiques reperes

- featured / image: [Preventing Stale Reads on Percona XtraDB Cluster by Enforcing Causality Checks on a Per-Query Basis](https://www.percona.com/wp-content/uploads/2026/03/Stale-Reads-on-Percona-XtraDB-Cluster.png)
- content / image: [Stale Reads on Percona XtraDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/Stale-Reads-on-Percona-XtraDB-Cluster-300x168.png)

## Auteur source

Fernando Laudares Camargos joined Percona in early 2013 after working 8 years for a Canadian company specialized in offering services based in open source technologies. Fernando's work experience includes the architecture, deployment and maintenance of IT infrastructures based on Linux, open source software and a layer of server virtualization. From the basic services such as DHCP & DNS to identity management systems, but also including backup routines, configuration management tools and thin-clients. He's now focusing on the universe of MySQL, MongoDB and PostgreSQL with a particular interest in understanding the intricacies of database systems and contributes regularly to this blog. You can read his other articles here.
