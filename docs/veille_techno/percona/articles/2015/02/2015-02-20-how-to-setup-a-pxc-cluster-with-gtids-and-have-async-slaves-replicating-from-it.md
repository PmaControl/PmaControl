---
title: How to setup a PXC cluster with GTIDs (and have async slaves replicating from it!)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-setup-a-pxc-cluster-with-gtids-and-have-async-slaves-replicating-from-it/
  post_id: 9060
source_author:
  name: Fernando Laudares Camargos
  slug: fernando-laudares
  url: https://www.percona.com/blog/author/fernando-laudares/
  website: ''
published_at: '2015-02-20T08:00:19'
published_at_gmt: '2015-02-20T08:00:19'
modified_at: '2026-04-28T22:16:48'
modified_at_gmt: '2026-04-28T22:16:48'
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
- tag:percona-xtrabackup:330
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- Fernando Laudares
- Global Transaction IDs
- GTIDs
- MySQL
- Percona XtraBackup
- Percona XtraDB Cluster
- Primary
- pxc
tag_slugs:
- fernando-laudares
- global-transaction-ids
- gtids
- mysql
- percona-xtrabackup
- percona-xtradb-cluster
- primary
- pxc
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to setup a PXC cluster with GTIDs (and have async slaves replicating from it!)

Source: [Percona Blog](https://www.percona.com/blog/how-to-setup-a-pxc-cluster-with-gtids-and-have-async-slaves-replicating-from-it/)

Auteur source: [Fernando Laudares Camargos](https://www.percona.com/blog/author/fernando-laudares/)

Publication: 2015-02-20T08:00:19

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This past week was marked by a series of personal findings related to the use of Global Transaction IDs (GTIDs) on Galera-based clusters such as Percona XtraDB Cluster (PXC). The main one being the fact that transactions touching MyISAM tables (and FLUSH PRIVILEGES!) issued on a giving node of the cluster are recorded on a … Continued

## Structure detectee

- H2: Initializing a PXC cluster configured with GTIDs
- H2: OK, that’s done. But how do I attach an async replica to the cluster?
- H2: Nice! What about the caveats you were talking about in the other blog post?
- H2: Ouch! Is there a fix for this?
- H2: Take-home lesson

## Auteur source

Fernando Laudares Camargos joined Percona in early 2013 after working 8 years for a Canadian company specialized in offering services based in open source technologies. Fernando's work experience includes the architecture, deployment and maintenance of IT infrastructures based on Linux, open source software and a layer of server virtualization. From the basic services such as DHCP & DNS to identity management systems, but also including backup routines, configuration management tools and thin-clients. He's now focusing on the universe of MySQL, MongoDB and PostgreSQL with a particular interest in understanding the intricacies of database systems and contributes regularly to this blog. You can read his other articles here.
